<?php
/**
 * Class: Audit Log List View
 *
 * Audit Log List View class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Views;

use \WSAL\MainWPExtension as MWPAL_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * Audit Log List View
 *
 * Log view class which extends WP List Table class.
 */
class AuditLogListView extends \WP_List_Table {

	/**
	 * GMT Offset
	 *
	 * @var int
	 */
	protected $gmt_offset_sec = 0;

	/**
	 * Datetime Format
	 *
	 * @var string
	 */
	protected $datetime_format;

	/**
	 * MainWP Child Sites
	 *
	 * @var array
	 */
	protected $mwp_child_sites;

	/**
	 * Events Query Arguments.
	 *
	 * @since 1.1
	 *
	 * @var stdClass
	 */
	protected $query_args;

	/**
	 * Events Meta.
	 *
	 * @since 1.1
	 *
	 * @var array
	 */
	protected $item_meta = array();

	/**
	 * Constructor.
	 *
	 * @param stdClass $query_args - Events query arguments.
	 */
	public function __construct( $query_args ) {
		$this->query_args = $query_args;
		$timezone         = MWPAL_Extension\mwpal_extension()->settings->get_timezone(); // Set GMT offset.

		if ( 'utc' === $timezone ) {
			$this->gmt_offset_sec = date( 'Z' );
		} else {
			$this->gmt_offset_sec = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		}

		// Get MainWP child sites.
		$this->mwp_child_sites = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_sites();

		parent::__construct(
			array(
				'singular' => 'activity-log',
				'plural'   => 'activity-logs',
				'ajax'     => true,
				'screen'   => 'interval-list',
			)
		);
	}

	/**
	 * Provides access to private query args property.
	 *
	 * @return stdClass
	 */
	public function get_query_args() {
		return $this->query_args;
	}

	/**
	 * Adds some classes to the table.
	 *
	 * @method get_table_classes
	 * @since  1.4.0
	 * @return array
	 */
	protected function get_table_classes() {
		$table_classes = array( 'widefat', 'fixed', 'striped', $this->_args['plural'], 'almwp-table', 'almwp-table-list' );
		return $table_classes;
	}

	/**
	 * Empty View.
	 */
	public function no_items() {
		esc_html_e( 'No events so far.', 'mwp-al-ext' );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @param string $which – Nav position.
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php
			$this->extra_tablenav( $which );

			/**
			 * Display search filters.
			 *
			 * @since 1.1
			 *
			 * @param string $which - Display position of tablenav i.e. top or bottom.
			 */
			do_action( 'mwpal_search_filters', $which );

			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
		// only display notices if this is the top side of the table.
		if ( 'top' === $which ) {
			if ( ! get_transient( 'mwpal-is-advert-dismissed' ) && ! ( function_exists( 'almainwp_fs' ) && almainwp_fs()->is__premium_only() ) ) : ?>
			<div class="notice notice-success mwpal-notice">
				<div class="content">
					<div class=mwpal-notice-left>
					<div class="notice-message">
						<div class="notice-message-img">
							<img src="<?php echo MWPAL_BASE_URL ?>assets/img/mwp-al-ext-150x150.jpg">
						</div>
						<div class="notice-message-desc">
							<p><strong><?php esc_html_e( 'Upgrade to premium to add search & filters and reports.', 'mwp-al-ext' ); ?></strong></p>
							<p><?php esc_html_e( 'Use the search to also search in the child sites\'activity log and the filters to fine-tune the results.', 'mwp-al-ext' ); ?></p>
							<p><?php esc_html_e( 'Generate and schedule automated weekly and monthly reports from the child site\'s activity logs.', 'mwp-al-ext' ); ?></p>
						</div>
					</div>
					</div>
					<div class="mwpal-notice-right">
						<div class="upgrade-btn">
							<a target="_blank" href="<?php echo esc_url( 'https://www.wpsecurityauditlog.com/activity-log-mainwp-extension/pricing/?utm_source=plugin&utm_medium=referral&utm_campaign=AL4MWP&utm_content=banner+upgrade+now' ); ?>" class="ui button green"><?php esc_html_e( 'Upgrade Now', 'mwp-al-ext' ); ?></a>
							<a target="_blank" href="<?php echo esc_url( 'https://www.wpsecurityauditlog.com/activity-log-mainwp-extension/premium-benefits/?utm_source=plugin&utm_medium=referral&utm_campaign=AL4MWP&utm_content=banner+tell+me+more' ); ?>" class="ui button"><?php esc_html_e( 'Tell Me More', 'mwp-al-ext' ); ?></a>
						</div>
						<div class="close-btn">
							<a href="javascript:;"><?php esc_html_e( 'Close', 'mwp-al-ext' ); ?></a>
						</div>
					</div>
				</div>
			</div>
			<?php endif;
			$incompatible_sites = get_option( 'mwpal-incompatible-wsal-version', array() );
			if ( ! empty( $incompatible_sites ) && ! get_transient( 'mwpal-hide-incompatible-wsal-version-notice' ) ) {
				?>
				<div class="notice notice-error mwpal-notice">
					<div class="content">
						<div class=mwpal-notice-left>
							<div class="notice-message">
								<div class="notice-message-img">
									<img src="<?php echo MWPAL_BASE_URL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static string ?>assets/img/mwp-al-ext-150x150.jpg">
								</div>
								<div class="notice-message-desc">
									<p><strong><?php esc_html_e( 'The following sites are running a version of WP Security Audit Log plugin that is not supported, so the logs were not retrieved. Please upgrade the plugins to version 4.', 'mwp-al-ext' ); ?></strong></p>
									<p><i>
										<?php
										echo esc_html( implode( ', ', $incompatible_sites ) );
										?>
									</i></p>
								</div>
							</div>
						</div>
						<div class="mwpal-notice-right">
							<div class="close-btn">
								<a data-notice="mwpal-hide-incompatible-wsal-version-notice" href="javascript:;"><?php esc_html_e( 'Close', 'mwp-al-ext' ); ?></a>
							</div>
						</div>
					</div>
				</div>
				<?php
			};
		}
	}

	/**
	 * Table navigation.
	 *
	 * @param string $which - Position of the nav.
	 */
	public function extra_tablenav( $which ) {
		// If the position is not top then render.
		if ( 'top' !== $which && ! MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll() ) :
			// Items-per-page widget.
			$per_page = MWPAL_Extension\mwpal_extension()->settings->get_view_per_page();
			$items    = array( 5, 10, 15, 30, 50 );
			if ( ! in_array( $per_page, $items, true ) ) {
				$items[] = $per_page;
			}
			?>
			<div class="mwp-ipp mwp-ipp-<?php echo esc_attr( $which ); ?>">
				<?php esc_html_e( 'Show ', 'mwp-al-ext' ); ?>
				<select class="mwp-ipps">
					<?php foreach ( $items as $item ) { ?>
						<option
							value="<?php echo is_string( $item ) ? '' : esc_attr( $item ); ?>"
							<?php echo ( $item === $per_page ) ? 'selected="selected"' : false; ?>
							>
							<?php echo esc_html( $item ); ?>
						</option>
					<?php } ?>
				</select>
				<?php esc_html_e( ' Items', 'mwp-al-ext' ); ?>
			</div>
			<?php
		endif;

		if ( 'top' !== $which && MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll() ) :
			?>
			<div id="mwpal-auditlog-end"><p><?php esc_html_e( '— End of Activity Log —', 'mwp-al-ext' ); ?></p></div>
			<div id="mwpal-event-loader"><div class="mwpal-lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
			<?php
		endif;

		if ( 'top' === $which ) :
			// Get child sites with WSAL installed.
			$wsal_child_sites = MWPAL_Extension\mwpal_extension()->settings->get_wsal_child_sites();
			if ( count( $wsal_child_sites ) > 0 ) :
				$current_site = MWPAL_Extension\mwpal_extension()->settings->get_view_site_id();
				?>
				<div class="mwp-ssa mwp-ssa-<?php echo esc_attr( $which ); ?>">
					<select class="mwp-ssas">
						<option value="0"><?php esc_html_e( 'All Sites', 'mwp-al-ext' ); ?></option>
						<option value="dashboard" <?php selected( $current_site, 'dashboard' ); ?>><?php esc_html_e( 'MainWP Dashboard', 'mwp-al-ext' ); ?></option>
						<?php
						if ( is_array( $wsal_child_sites ) ) {
							foreach ( $wsal_child_sites as $site_id => $site_data ) {
								$key = array_search( $site_id, array_column( $this->mwp_child_sites, 'id' ), false );
								if ( false !== $key ) {
									?>
									<option value="<?php echo esc_attr( $this->mwp_child_sites[ $key ]['id'] ); ?>"
										<?php selected( (int) $this->mwp_child_sites[ $key ]['id'], $current_site ); ?>>
										<?php echo esc_html( $this->mwp_child_sites[ $key ]['name'] ) . ' (' . esc_html( $this->mwp_child_sites[ $key ]['url'] ) . ')'; ?>
									</option>
									<?php
								}
							}
						}
						?>
					</select>
					<input type="button" class="almwp-button" id="mwpal-wsal-manual-retrieve" value="<?php esc_html_e( 'Retrieve Activity Logs Now', 'mwp-al-ext' ); ?>" />
				</div>
				<?php
			endif;
			$user_selected_view = 'list';
			?>
			<div class="display-type-buttons">
				<span class="almwp-button dashicons-before dashicons-list-view almwp-list-view-toggle <?php echo ( $this instanceof AuditLogListView ) ? esc_attr( 'disabled' ) : ''; ?>"><?php esc_html_e( 'List View', 'wp-security-audit-log' ); ?></span>
				<a href="<?php echo esc_url( add_query_arg( 'view', 'grid' ) ); ?>" class="almwp-button dashicons-before dashicons-grid-view almwp-grid-view-toggle <?php echo ( $this instanceof AuditLogGridView ) ? esc_attr( 'disabled' ) : ''; ?>"><?php esc_html_e( 'Grid View', 'wp-security-audit-log' ); ?></a>
			</div>
			<?php
		endif;
	}

	/**
	 * Method: Get checkbox column.
	 *
	 * @param object $item - Item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" value="' . $item->id . '" name="' . esc_attr( $this->_args['singular'] ) . '[]" />';
	}

	/**
	 * Method: Get default column values.
	 *
	 * @param object $item        - Column item.
	 * @param string $column_name - Name of the column.
	 */
	public function column_default( $item, $column_name ) {
		$datetime_format = MWPAL_Extension\mwpal_extension()->settings->get_date_time_format(); // Get date time format.
		$type_username   = MWPAL_Extension\mwpal_extension()->settings->get_type_username(); // Get username type to display.
		$mwp_child_sites = $this->mwp_child_sites; // Get MainWP child sites.

		if ( ! isset( $this->item_meta[ $item->getId() ] ) ) {
			$this->item_meta[ $item->getId() ] = $item->GetMetaArray();
		}

		switch ( $column_name ) {
			case 'site':
				$site_id    = (string) $item->site_id;
				$site_index = array_search( $site_id, array_column( $mwp_child_sites, 'id' ), true );

				$html = '';
				if ( false !== $site_index && isset( $mwp_child_sites[ $site_index ] ) ) {
					$html  = '<a href="' . esc_url( $mwp_child_sites[ $site_index ]['url'] ) . '" target="_blank">';
					$html .= esc_html( $mwp_child_sites[ $site_index ]['name'] );
					$html .= '</a>';
				} else {
					$html = __( 'MainWP Dashboard', 'mwp-al-ext' );
				}
				return $html;

			case 'type':
				$code = MWPAL_Extension\mwpal_extension()->alerts->GetAlert( $item->alert_id );
				return '<span class="log-disable">' . str_pad( $item->alert_id, 4, '0', STR_PAD_LEFT ) . ' </span>';

			case 'code':
				$code  = MWPAL_Extension\mwpal_extension()->alerts->GetAlert( $item->alert_id );
				$code  = $code ? $code->code : 0;
				$const = (object) array(
					'name'        => 'E_UNKNOWN',
					'value'       => 0,
					'description' => __( 'Unknown error code.', 'mwp-al-ext' ),
				);
				$const = MWPAL_Extension\mwpal_extension()->constants->GetConstantBy( 'value', $code, $const );
				if ( 'E_CRITICAL' === $const->name ) {
					$const->name = __( 'Critical', 'mwp-al-ext' );
				} elseif ( 'E_WARNING' === $const->name ) {
					$const->name = __( 'Warning', 'mwp-al-ext' );
				} elseif ( 'E_NOTICE' === $const->name ) {
					$const->name = __( 'Notification', 'mwp-al-ext' );
				} elseif ( 'WSAL_CRITICAL' === $const->name ) {
					$const->name = __( 'Critical', 'mwp-al-ext' );
				} elseif ( 'WSAL_HIGH' === $const->name ) {
					$const->name = __( 'High', 'mwp-al-ext' );
				} elseif ( 'WSAL_MEDIUM' === $const->name ) {
					$const->name = __( 'Medium', 'mwp-al-ext' );
				} elseif ( 'WSAL_LOW' === $const->name ) {
					$const->name = __( 'Low', 'mwp-al-ext' );
				} elseif ( 'WSAL_INFORMATIONAL' === $const->name ) {
					$const->name = __( 'Info', 'mwp-al-ext' );
				}
				return '<a class="tooltip" href="#" data-tooltip="' . esc_html( $const->name ) . '"><span class="log-type log-type-' . $const->value . '"></span></a>';

			case 'crtd':
				return $item->created_on ? (
					str_replace(
						'$$$',
						substr( number_format( fmod( $item->created_on + $this->gmt_offset_sec, 1 ), 3 ), 2 ),
						date( $datetime_format, $item->created_on + $this->gmt_offset_sec )
					)
				) : '<i>' . __( 'Unknown', 'mwp-al-ext' ) . '</i>';

			case 'user':
				$username  = $item->GetUsername( $this->item_meta[ $item->getId() ] ); // Get username.
				$user_data = $item->get_user_data( $this->item_meta[ $item->getId() ] ); // Get user data.

				if ( empty( $user_data ) ) {
					$user_data = get_user_by( 'login', $username );
				}

				// Check if the usernames exists & matches pre-defined cases.
				if ( 'Plugin' === $username ) {
					$image = '<img src="' . trailingslashit( MWPAL_BASE_URL ) . 'assets/img/wsal-logo.png" width="32" alt="WSAL Logo"/>';
					$uhtml = '<i>' . __( 'Plugin', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'Plugins' === $username ) {
					$image = '<span class="dashicons dashicons-wordpress wsal-system-icon"></span>';
					$uhtml = '<i>' . __( 'Plugins', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'Website Visitor' === $username ) {
					$image = '<span class="dashicons dashicons-wordpress wsal-system-icon"></span>';
					$uhtml = '<i>' . __( 'Website Visitor', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'System' === $username ) {
					$image = '<span class="dashicons dashicons-wordpress wsal-system-icon"></span>';
					$uhtml = '<i>' . __( 'System', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( $user_data && 'System' !== $user_data->username ) {
					$image = get_avatar( $user_data->user_email, 32 ); // Avatar.

					// Checks for display name.
					if ( 'display_name' === $type_username && ! empty( $user_data->display_name ) ) {
						$display_name = $user_data->display_name;
					} elseif (
						'first_last_name' === $type_username
						&& ( ! empty( $user_data->first_name ) || ! empty( $user_data->last_name ) )
					) {
						$display_name = $user_data->first_name . ' ' . $user_data->last_name;
					} else {
						$display_name = $user_data->username;
					}

					if ( $this->query_args->site_id && 'live' === $this->query_args->get_events ) {
						$site_id = (string) $this->query_args->site_id;
					} else {
						$site_id = (string) $item->site_id;
					}

					$site_index = array_search( $site_id, array_column( $mwp_child_sites, 'id' ), true );
					$site_url   = '#';

					if ( false !== $site_index && isset( $mwp_child_sites[ $site_index ] ) ) {
						$site_url = $mwp_child_sites[ $site_index ]['url'];
						$user_url = add_query_arg( 'user_id', $user_data->user_id, trailingslashit( $site_url ) . 'wp-admin/user-edit.php' );
					} else {
						$user_url = add_query_arg( 'user_id', $user_data->ID, admin_url( 'user-edit.php' ) );
					}

					// User html.
					$uhtml = '<a href="' . esc_url( $user_url ) . '" target="_blank">' . esc_html( $display_name ) . '</a>';

					$roles = $item->GetUserRoles( $this->item_meta[ $item->getId() ] );
					if ( is_array( $roles ) && count( $roles ) ) {
						$roles = esc_html( ucwords( implode( ', ', $roles ) ) );
					} elseif ( is_string( $roles ) && '' != $roles ) {
						$roles = esc_html( ucwords( str_replace( array( '"', '[', ']' ), ' ', $roles ) ) );
					} else {
						$roles = '<i>' . __( 'Unknown', 'mwp-al-ext' ) . '</i>';
					}
				} else {
					$image = '<span class="dashicons dashicons-wordpress wsal-system-icon"></span>';
					$uhtml = '<i>' . __( 'System', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				}
				return $image . $uhtml . '<br/>' . $roles;

			case 'scip':
				$scip = $item->GetSourceIP( $this->item_meta[ $item->getId() ] );
				if ( is_string( $scip ) ) {
					$scip = str_replace( array( '"', '[', ']' ), '', $scip );
				}

				$oips = array(); // $item->GetOtherIPs();

				// If there's no IP...
				if ( is_null( $scip ) || '' == $scip ) {
					return '<i>unknown</i>';
				}

				// If there's only one IP...
				$link = 'https://whatismyipaddress.com/ip/' . $scip . '?utm_source=plugin&utm_medium=referral&utm_campaign=WPSAL';
				if ( class_exists( 'WSAL_SearchExtension' ) ) {
					$tooltip = esc_attr__( 'Show me all activity originating from this IP Address', 'mwp-al-ext' );

					if ( count( $oips ) < 2 ) {
						return "<a class='search-ip' data-tooltip='$tooltip' data-ip='$scip' target='_blank' href='$link'>" . esc_html( $scip ) . '</a>';
					}
				} else {
					if ( count( $oips ) < 2 ) {
						return "<a target='_blank' href='$link'>" . esc_html( $scip ) . '</a>';
					}
				}

				// If there are many IPs...
				if ( class_exists( 'WSAL_SearchExtension' ) ) {
					$tooltip = esc_attr__( 'Show me all activity originating from this IP Address', 'mwp-al-ext' );

					$html = "<a class='search-ip' data-tooltip='$tooltip' data-ip='$scip' target='_blank' href='https://whatismyipaddress.com/ip/$scip'>" . esc_html( $scip ) . '</a> <a href="javascript:;" onclick="jQuery(this).hide().next().show();">(more&hellip;)</a><div style="display: none;">';
					foreach ( $oips as $ip ) {
						if ( $scip != $ip ) {
							$html .= '<div>' . $ip . '</div>';
						}
					}
					$html .= '</div>';
					return $html;
				} else {
					$html = "<a target='_blank' href='https://whatismyipaddress.com/ip/$scip'>" . esc_html( $scip ) . '</a> <a href="javascript:;" onclick="jQuery(this).hide().next().show();">(more&hellip;)</a><div style="display: none;">';
					foreach ( $oips as $ip ) {
						if ( $scip != $ip ) {
							$html .= '<div>' . $ip . '</div>';
						}
					}
					$html .= '</div>';
					return $html;
				}

			case 'mesg':
				return '<div id="Event' . $item->id . '">' . $item->GetMessage( array( $this, 'meta_formatter' ), $this->item_meta[ $item->getId() ] ) . '</div>';

			case 'data':
				$url_args = array(
					'action'        => 'metadata_inspector',
					'occurrence_id' => $item->id,
					'mwp_meta_nonc' => wp_create_nonce( 'mwp-meta-display-' . $item->id ),
					'TB_iframe'     => 'true',
					'width'         => '600',
					'height'        => '550',
				);

				$url     = add_query_arg( $url_args, admin_url( 'admin-ajax.php' ) );
				$tooltip = esc_attr__( 'View all details of this change', 'mwp-al-ext' );
				return '<a class="more-info thickbox" data-tooltip="' . $tooltip . '" title="' . __( 'Alert Data Inspector', 'mwp-al-ext' ) . '"'
					. ' href="' . $url . '">&hellip;</a>';

			case 'object':
				return isset( $this->item_meta[ $item->getId() ]['Object'] ) ? MWPAL_Extension\Activity_Log::get_instance()->alerts->get_display_object_text( $this->item_meta[ $item->getId() ]['Object'] ) : '';

			case 'event_type':
				return isset( $this->item_meta[ $item->getId() ]['EventType'] ) ? MWPAL_Extension\Activity_Log::get_instance()->alerts->get_display_event_type_text( $this->item_meta[ $item->getId() ]['EventType'] ) : '';

			default:
				/* translators: Column Name */
				return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : sprintf( esc_html__( 'Column "%s" not found', 'mwp-al-ext' ), $column_name );
		}
	}

	/**
	 * Method: Get View Columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		// Audit log columns.
		$cols = array(
			'site'       => __( 'Site', 'mwp-al-ext' ),
			'type'       => __( 'Event ID', 'mwp-al-ext' ),
			'code'       => __( 'Severity', 'mwp-al-ext' ),
			'crtd'       => __( 'Date', 'mwp-al-ext' ),
			'user'       => __( 'User', 'mwp-al-ext' ),
			'scip'       => __( 'Source IP', 'mwp-al-ext' ),
			'object'     => __( 'Object', 'mwp-al-ext' ),
			'event_type' => __( 'Event Type', 'mwp-al-ext' ),
			'mesg'       => __( 'Message', 'mwp-al-ext' ),
			'data'       => '',
		);

		// Get selected columns.
		$selected = MWPAL_Extension\mwpal_extension()->settings->get_columns_selected();

		// If selected columns are not empty, then unset default columns.
		if ( ! empty( $selected ) ) {
			unset( $cols );
			$selected = (array) json_decode( $selected );
			foreach ( $selected as $key => $value ) {
				switch ( $key ) {
					case 'site':
						$cols['site'] = __( 'Site', 'mwp-al-ext' );
						break;
					case 'alert_code':
						$cols['type'] = __( 'Event ID', 'mwp-al-ext' );
						break;
					case 'type':
						$cols['code'] = __( 'Severity', 'mwp-al-ext' );
						break;
					case 'date':
						$cols['crtd'] = __( 'Date', 'mwp-al-ext' );
						break;
					case 'username':
						$cols['user'] = __( 'User', 'mwp-al-ext' );
						break;
					case 'source_ip':
						$cols['scip'] = __( 'Source IP', 'mwp-al-ext' );
						break;
					case 'message':
						$cols['mesg'] = __( 'Message', 'mwp-al-ext' );
						break;
					case 'event_type':
						$cols['event_type'] = __( 'Event Type', 'mwp-al-ext' );
						break;
					case 'object':
						$cols['object'] = __( 'Object', 'mwp-al-ext' );
						break;
				}
			}

			$cols['data'] = '';
		}

		if ( isset( $cols['site'] ) && $this->query_args->site_id ) {
			unset( $cols['site'] );
		}

		return $cols;
	}

	/**
	 * Method: Get Sortable Columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'read'       => array( 'is_read', false ),
			'type'       => array( 'alert_id', false ),
			'crtd'       => array( 'created_on', true ),
			'user'       => array( 'user', true ),
			'scip'       => array( 'scip', false ),
			'event_type' => array( 'event_type', true ),
			'object'     => array( 'object', true ),
		);
	}

	/**
	 * Method: Prepare items.
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$query_events = $this->query_events();
		$this->items  = isset( $query_events['items'] ) ? $query_events['items'] : false;
		$total_items  = isset( $query_events['total_items'] ) ? $query_events['total_items'] : false;
		$per_page     = isset( $query_events['per_page'] ) ? $query_events['per_page'] : false;

		if ( ! MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll() ) {
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
		}
	}

	/**
	 * Query Events from WSAL DB.
	 *
	 * @since 1.1
	 *
	 * @param integer $paged - Page number.
	 * @return array
	 */
	public function query_events( $paged = 0 ) {
		// Query for events.
		$events_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();

		// Get site id for specific site events.
		$bid = $this->query_args->site_id;
		if ( $bid && 'dashboard' !== $bid ) {
			$events_query->addCondition( 'site_id = %s ', $bid );
		} elseif ( 'dashboard' === $bid ) {
			$events_query->addCondition( 'site_id = %s ', '0' );
		}

		/**
		 * Filter: `mwpal_auditlog_query`
		 *
		 * This filter can be used to modify the query for events.
		 * It is helpful while performing search operations on the
		 * audit log events.
		 *
		 * @param \WSAL\MainWPExtension\Models\OccurrenceQuery $events_query - Occurrence query instance.
		 */
		$events_query = apply_filters( 'mwpal_auditlog_query', $events_query );

		if ( ! MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll() ) {
			$total_items = $events_query->getAdapter()->Count( $events_query );
			$per_page    = MWPAL_Extension\mwpal_extension()->settings->get_view_per_page();
			$offset      = ( $this->get_pagenum() - 1 ) * $per_page;
		} else {
			$total_items = false;
			$per_page    = 25; // Manually set per page events for infinite scroll.
			$offset      = ( max( 1, $paged ) - 1 ) * $per_page;
		}

		// Set query order arguments.
		$order_by = isset( $this->query_args->order_by ) ? $this->query_args->order_by : false;
		$order    = isset( $this->query_args->order ) ? $this->query_args->order : false;

		if ( ! $order_by ) {
			$events_query->addOrderBy( 'created_on', true );
		} else {
			$is_descending = true;
			if ( ! empty( $order ) && 'asc' === $order ) {
				$is_descending = false;
			}

			// TO DO: Allow order by meta values.
			if ( 'scip' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'ClientIP' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "ClientIP" THEN meta.value END', $is_descending );
			} elseif ( 'user' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'CurrentUserID' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "CurrentUserID" THEN meta.value END', $is_descending );
			} elseif ( 'event_type' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'EventType' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "EventType" THEN meta.value END', $is_descending );
			} elseif ( 'object' === $order_by ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'Object' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "Object" THEN meta.value END', $is_descending );
			} else {
				$tmp = new \WSAL\MainWPExtension\Models\Occurrence();
				// Making sure the field exists to order by.
				if ( isset( $tmp->{$order_by} ) ) {
					// TODO: We used to use a custom comparator ... is it safe to let MySQL do the ordering now?.
					$events_query->addOrderBy( $order_by, $is_descending );

				} else {
					$events_query->addOrderBy( 'created_on', true );
				}
			}
		}

		$events_query->setOffset( $offset );  // Set query offset.
		$events_query->setLimit( $per_page ); // Set number of events per page.
		return array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'items'       => $events_query->getAdapter()->Execute( $events_query ),
		);
	}

	/**
	 * Method: Meta data formater.
	 *
	 * @param string $name - Name of the data.
	 * @param mix    $value - Value of the data.
	 * @return string
	 */
	public function meta_formatter( $name, $value ) {
		switch ( true ) {
			case '%Message%' == $name:
				return esc_html( $value );

			case '%PromoMessage%' == $name:
				return '<p class="promo-alert">' . $value . '</p>';

			case '%PromoLink%' == $name:
			case '%CommentLink%' == $name:
			case '%CommentMsg%' == $name:
				return $value;

			case '%MetaLink%' == $name:
				if ( ! empty( $value ) ) {
					return "<a href=\"#\" data-disable-custom-nonce='" . wp_create_nonce( 'disable-custom-nonce' . $value ) . "' onclick=\"WsalDisableCustom(this, '" . $value . "');\"> Exclude Custom Field from the Monitoring</a>";
				} else {
					return '';
				}

			case '%RevisionLink%' === $name:
				$check_value = (string) $value;
				if ( 'NULL' !== $check_value ) {
					return ' Click <a target="_blank" href="' . esc_url( $value ) . '">here</a> to see the content changes.';
				} else {
					return false;
				}

			case '%EditorLinkPost%' == $name:
				return ' View the <a target="_blank" href="' . esc_url( $value ) . '">post</a>';

			case '%EditorLinkPage%' == $name:
				return ' View the <a target="_blank" href="' . esc_url( $value ) . '">page</a>';

			case '%CategoryLink%' == $name:
				return ' View the <a target="_blank" href="' . esc_url( $value ) . '">category</a>';

			case '%TagLink%' == $name:
				return ' View the <a target="_blank" href="' . esc_url( $value ) . '">tag</a>';

			case '%EditorLinkForum%' == $name:
				return ' View the <a target="_blank" href="' . esc_url( $value ) . '">forum</a>';

			case '%EditorLinkTopic%' == $name:
				return ' View the <a target="_blank" href="' . esc_url( $value ) . '">topic</a>';

			case in_array( $name, array( '%MetaValue%', '%MetaValueOld%', '%MetaValueNew%' ) ):
				return '<strong>' . (
					strlen( $value ) > 50 ? ( esc_html( substr( $value, 0, 50 ) ) . '&hellip;' ) : esc_html( $value )
				) . '</strong>';

			case '%ClientIP%' == $name:
				if ( is_string( $value ) ) {
					return '<strong>' . str_replace( array( '"', '[', ']' ), '', $value ) . '</strong>';
				} else {
					return '<i>unknown</i>';
				}

			case '%LinkFile%' === $name:
				if ( 'NULL' != $value ) {
					$site_id = MWPAL_Extension\mwpal_extension()->settings->get_view_site_id(); // Site id for multisite.
					return '<a href="javascript:;" onclick="download_404_log( this )" data-log-file="' . esc_attr( $value ) . '" data-site-id="' . esc_attr( $site_id ) . '" data-nonce-404="' . esc_attr( wp_create_nonce( 'wsal-download-404-log-' . $value ) ) . '" title="' . esc_html__( 'Download the log file', 'mwp-al-ext' ) . '">' . esc_html__( 'Download the log file', 'mwp-al-ext' ) . '</a>';
				} else {
					return 'Click <a href="' . esc_url( add_query_arg( 'page', 'wsal-togglealerts', admin_url( 'admin.php' ) ) ) . '">here</a> to log such requests to file';
				}

			case '%URL%' === $name:
				return ' or <a href="javascript:;" data-exclude-url="' . esc_url( $value ) . '" data-exclude-url-nonce="' . wp_create_nonce( 'wsal-exclude-url-' . $value ) . '" onclick="wsal_exclude_url( this )">exclude this URL</a> from being reported.';

			case '%LogFileLink%' === $name: // Failed login file link.
				return '';

			case '%Attempts%' === $name: // Failed login attempts.
				$check_value = (int) $value;
				if ( 0 === $check_value ) {
					return '';
				} else {
					return $value;
				}

			case '%LogFileText%' === $name: // Failed login file text.
				return '<a href="javascript:;" onclick="download_failed_login_log( this )" data-download-nonce="' . esc_attr( wp_create_nonce( 'wsal-download-failed-logins' ) ) . '" title="' . esc_html__( 'Download the log file.', 'mwp-al-ext' ) . '">' . esc_html__( 'Download the log file.', 'mwp-al-ext' ) . '</a>';

			case strncmp( $value, 'http://', 7 ) === 0:
			case strncmp( $value, 'https://', 7 ) === 0:
				return '<a href="' . esc_html( $value ) . '" title="' . esc_html( $value ) . '" target="_blank">' . esc_html( $value ) . '</a>';

			case '%PostStatus%' === $name:
				if ( ! empty( $value ) && 'publish' === $value ) {
					return '<strong>' . esc_html__( 'published', 'mwp-al-ext' ) . '</strong>';
				} else {
					return '<strong>' . esc_html( $value ) . '</strong>';
				}

			case '%multisite_text%' === $name:
				if ( $this->is_multisite() && $value ) {
					$site_info = get_blog_details( $value, true );
					if ( $site_info ) {
						return ' on site <a href="' . esc_url( $site_info->siteurl ) . '">' . esc_html( $site_info->blogname ) . '</a>';
					}
					return;
				}
				return;

			case '%ReportText%' === $name:
				return;

			case '%ChangeText%' === $name:
				$url = admin_url( 'admin-ajax.php' ) . '?action=AjaxInspector&amp;occurrence=' . $this->current_alert_id;
				return ' View the changes in <a class="thickbox"  title="' . __( 'Alert Data Inspector', 'mwp-al-ext' ) . '"'
				. ' href="' . $url . '&amp;TB_iframe=true&amp;width=600&amp;height=550">data inspector.</a>';

			case '%ScanError%' === $name:
				if ( 'NULL' === $value ) {
					return false;
				}
				/* translators: Mailto link for support. */
				return ' with errors. ' . sprintf( __( 'Contact us on %s for assistance', 'mwp-al-ext' ), '<a href="mailto:support@wpsecurityauditlog.com" target="_blank">support@wpsecurityauditlog.com</a>' );

			case '%TableNames%' === $name:
				$value = str_replace( ',', ', ', $value );
				return '<strong>' . esc_html( $value ) . '</strong>';

			case '%FileSettings%' === $name:
				$file_settings_args = array(
					'page' => 'wsal-settings',
					'tab'  => 'file-changes',
				);
				$file_settings      = add_query_arg( $file_settings_args, admin_url( 'admin.php' ) );
				return '<a href="' . esc_url( $file_settings ) . '">' . esc_html__( 'plugin settings', 'mwp-al-ext' ) . '</a>';

			case '%ContactSupport%' === $name:
				return '<a href="https://www.wpsecurityauditlog.com/contact/" target="_blank">' . esc_html__( 'contact our support', 'mwp-al-ext' ) . '</a>';

			case '%LineBreak%' === $name:
				return '<br>';

			default:
				return '<strong>' . esc_html( $value ) . '</strong>';
		}
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text     - The 'submit' button label.
	 * @param string $input_id - ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />'; // phpcs:ignore
		}

		if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore
			echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />'; // phpcs:ignore
		}
		?>
		<div class="mwpal-search-box">
			<div class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search events', 'mwp-al-ext' ); ?>" />
				<?php submit_button( $text, '', '', false, array( 'id' => 'almwp-search-submit' ) ); ?>
				<input type="button" id="mwpal-clear-search" class="almwp-button" value="<?php esc_attr_e( 'Clear Search Results', 'mwp-al-ext' ); ?>" disabled>
			</div>
			<div id="mwpal-search-list" class="mwpal-search-filters-list no-filters"></div>
		</div>
		<?php
	}
}
