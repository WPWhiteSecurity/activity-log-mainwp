<?php
/**
 * Class: Audit Log List View
 *
 * Audit Log List View class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Views;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;

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
final class AuditLogListView extends \WP_List_Table {

	/**
	 * Instance of Activity Log.
	 *
	 * @var Activity_Log
	 */
	protected $activity_log;

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
	 * Method: Constructor.
	 *
	 * @param object $activity_log - Instance of Activity_Log.
	 */
	public function __construct( $activity_log ) {
		$this->activity_log = $activity_log;

		// Set GMT offset.
		$this->gmt_offset_sec = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

		// Get date time format.
		$this->datetime_format = $this->activity_log->settings->get_date_time_format();

		// Get MainWP child sites.
		$this->mwp_child_sites = $this->activity_log->settings->get_mwp_child_sites();

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
	 * Empty View.
	 */
	public function no_items() {
		esc_html_e( 'No events so far.', 'mwp-al-ext' );
	}

	/**
	 * Table navigation.
	 *
	 * @param string $which - Position of the nav.
	 */
	public function extra_tablenav( $which ) {
		// If the position is not top then render.
		if ( 'top' !== $which ) :
			// Items-per-page widget.
			$per_page = $this->activity_log->settings->get_view_per_page();
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

		if ( count( $this->mwp_child_sites ) > 1 ) :
			$current_site = $this->activity_log->settings->get_view_site_id();
			?>
			<div class="mwp-ssa mwp-ssa-<?php echo esc_attr( $which ); ?>">
				<select class="mwp-ssas">
					<option value="0"><?php esc_html_e( 'All Sites', 'mwp-al-ext' ); ?></option>
					<?php foreach ( $this->mwp_child_sites as $site ) { ?>
						<option value="<?php echo esc_attr( $site['id'] ); ?>"
							<?php echo ( $current_site === (int) $site['id'] ) ? 'selected="selected"' : false; ?>>
							<?php echo esc_html( $site['name'] ) . ' (' . esc_html( $site['url'] ) . ')'; ?>
						</option>
					<?php } ?>
				</select>
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
		// Get date time format.
		$datetime_format = $this->datetime_format;

		// Get MainWP & WSAL child sites.
		$mwp_child_sites = $this->mwp_child_sites;

		switch ( $column_name ) {
			case 'site':
				$site_id    = (string) $item->site_id;
				$site_index = array_search( $site_id, array_column( $mwp_child_sites, 'id' ), true );

				$html = '';
				if ( isset( $mwp_child_sites[ $site_index ] ) ) {
					$html  = '<a href="' . esc_url( $mwp_child_sites[ $site_index ]['url'] ) . '" target="_blank">';
					$html .= esc_html( $mwp_child_sites[ $site_index ]['name'] );
					$html .= '</a>';
				}
				return $html;

			case 'type':
				$code = $this->activity_log->alerts->GetAlert( $item->alert_id );
				return '<span class="log-disable">' . str_pad( $item->alert_id, 4, '0', STR_PAD_LEFT ) . ' </span>';

			case 'code':
				$code  = $this->activity_log->alerts->GetAlert( $item->alert_id );
				$code  = $code ? $code->code : 0;
				$const = (object) array(
					'name'        => 'E_UNKNOWN',
					'value'       => 0,
					'description' => __( 'Unknown error code.', 'mwp-al-ext' ),
				);
				$const = $this->activity_log->constants->GetConstantBy( 'value', $code, $const );
				if ( 'E_CRITICAL' === $const->name ) {
					$const->name = __( 'Critical', 'mwp-al-ext' );
				} elseif ( 'E_WARNING' === $const->name ) {
					$const->name = __( 'Warning', 'mwp-al-ext' );
				} elseif ( 'E_NOTICE' === $const->name ) {
					$const->name = __( 'Notification', 'mwp-al-ext' );
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
				// Get username.
				$username = $item->GetUsername();

				// Check if the usernames exists & matches pre-defined cases.
				if ( 'Plugin' === $username ) {
					$image = '<img src="' . trailingslashit( MWPAL_BASE_URL ) . 'assets/img/plugin-logo.png" class="avatar avatar-32 photo" width="32" height="32" alt=""/>';
					$uhtml = '<i>' . __( 'Plugin', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'Plugins' === $username ) {
					$image = '<img src="' . trailingslashit( MWPAL_BASE_URL ) . 'assets/img/wordpress-logo-32.png" class="avatar avatar-32 photo" width="32" height="32" alt=""/>';
					$uhtml = '<i>' . __( 'Plugins', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'Website Visitor' === $username ) {
					$image = '<img src="' . trailingslashit( MWPAL_BASE_URL ) . 'assets/img/wordpress-logo-32.png" class="avatar avatar-32 photo" width="32" height="32" alt=""/>';
					$uhtml = '<i>' . __( 'Website Visitor', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'System' === $username ) {
					$image = '<img src="' . trailingslashit( MWPAL_BASE_URL ) . 'assets/img/wordpress-logo-32.png" class="avatar avatar-32 photo" width="32" height="32" alt=""/>';
					$uhtml = '<i>' . __( 'System', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} else {
					// Get user data.
					$user_data    = $item->get_user_data();
					$image        = get_avatar( $user_data->user_email, 32 ); // Avatar.
					$display_name = $user_data->display_name;

					$site_id    = (string) $item->site_id;
					$site_index = array_search( $site_id, array_column( $mwp_child_sites, 'id' ), true );
					$site_url   = '#';

					if ( isset( $mwp_child_sites[ $site_index ] ) ) {
						$site_url = $mwp_child_sites[ $site_index ]['url'];
					}

					// User html.
					$uhtml = '<a href="' . esc_url( add_query_arg( 'user_id', $user_data->user_id, $site_url . '/wp-admin/user-edit.php' ) ) . '" target="_blank">' . esc_html( $display_name ) . '</a>';

					$roles = $item->GetUserRoles();
					if ( is_array( $roles ) && count( $roles ) ) {
						$roles = esc_html( ucwords( implode( ', ', $roles ) ) );
					} elseif ( is_string( $roles ) && '' != $roles ) {
						$roles = esc_html( ucwords( str_replace( array( '"', '[', ']' ), ' ', $roles ) ) );
					} else {
						$roles = '<i>' . __( 'Unknown', 'mwp-al-ext' ) . '</i>';
					}
				}
				return $image . $uhtml . '<br/>' . $roles;

			case 'scip':
				$scip = $item->GetSourceIP();
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
				return '<div id="Event' . $item->id . '">' . $item->GetMessage( array( $this, 'meta_formatter' ) ) . '</div>';

			case 'data':
				$url     = admin_url( 'admin-ajax.php' ) . '?action=AjaxInspector&amp;occurrence=' . $item->id;
				$tooltip = esc_attr__( 'View all details of this change', 'mwp-al-ext' );
				return '<a class="more-info thickbox" data-tooltip="' . $tooltip . '" title="' . __( 'Alert Data Inspector', 'mwp-al-ext' ) . '"'
					. ' href="' . $url . '&amp;TB_iframe=true&amp;width=600&amp;height=550">&hellip;</a>';

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
			'site' => __( 'Site', 'mwp-al-ext' ),
			'type' => __( 'Event ID', 'mwp-al-ext' ),
			'code' => __( 'Severity', 'mwp-al-ext' ),
			'crtd' => __( 'Date', 'mwp-al-ext' ),
			'user' => __( 'User', 'mwp-al-ext' ),
			'scip' => __( 'Source IP', 'mwp-al-ext' ),
			'mesg' => __( 'Message', 'mwp-al-ext' ),
			'data' => '',
		);
		return $cols;
	}

	/**
	 * Method: Get Sortable Columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'read' => array( 'is_read', false ),
			'type' => array( 'alert_id', false ),
			'crtd' => array( 'created_on', true ),
			'user' => array( 'user', true ),
			'scip' => array( 'scip', false ),
		);
	}

	/**
	 * Method: Prepare items.
	 */
	public function prepare_items() {
		// Per page views.
		$per_page = $this->activity_log->settings->get_view_per_page();

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Query for events.
		$events_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();

		// Get site id for specific site events.
		$bid = (int) $this->activity_log->settings->get_view_site_id();
		if ( $bid ) {
			$events_query->addCondition( 'site_id = %s ', $bid );
		}

		/**
		 * Filter: `mwpal_auditlog_query`
		 *
		 * This filter can be used to modify the query for events.
		 * It is helpful while performing search operations on the
		 * audit log events.
		 *
		 * @param \WSAL\MainWPExtension\Models\OccurrenceQuery $events_query – Occurrrence query instance.
		 */
		$events_query = apply_filters( 'mwpal_auditlog_query', $events_query );

		// @codingStandardsIgnoreStart
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : false;
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( empty( $orderby ) ) {
			$events_query->addOrderBy( 'created_on', true );
		} else {
			$is_descending = true;
			if ( ! empty( $order ) && 'asc' === $order ) {
				$is_descending = false;
			}

			// TO DO: Allow order by meta values.
			if ( 'scip' === $orderby ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'ClientIP' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "ClientIP" THEN meta.value END', $is_descending );
			} elseif ( 'user' === $orderby ) {
				$events_query->addMetaJoin(); // Since LEFT JOIN clause causes the result values to duplicate.
				$events_query->addCondition( 'meta.name = %s', 'CurrentUserID' ); // A where condition is added to make sure that we're only requesting the relevant meta data rows from metadata table.
				$events_query->addOrderBy( 'CASE WHEN meta.name = "CurrentUserID" THEN meta.value END', $is_descending );
			} else {
				$tmp = new \WSAL\MainWPExtension\Models\Occurrence();
				// Making sure the field exists to order by.
				if ( isset( $tmp->{$orderby} ) ) {
					// TODO: We used to use a custom comparator ... is it safe to let MySQL do the ordering now?.
					$events_query->addOrderBy( $orderby, $is_descending );

				} else {
					$events_query->addOrderBy( 'created_on', true );
				}
			}
		}

		$total_items = $events_query->getAdapter()->Count( $events_query );
		$events_query->setOffset( ( $this->get_pagenum() - 1 ) * $per_page );
		$events_query->setLimit( $per_page );

		$this->items = $events_query->getAdapter()->Execute( $events_query );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
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
					$site_id = $this->activity_log->settings->get_view_site_id(); // Site id for multisite.
					return '<a href="javascript:;" onclick="download_404_log( this )" data-log-file="' . esc_attr( $value ) . '" data-site-id="' . esc_attr( $site_id ) . '" data-nonce-404="' . esc_attr( wp_create_nonce( 'wsal-download-404-log-' . $value ) ) . '" title="' . esc_html__( 'Download the log file', 'mwp-al-ext' ) . '">' . esc_html__( 'Download the log file', 'mwp-al-ext' ) . '</a>';
				} else {
					return 'Click <a href="' . esc_url( admin_url( 'admin.php?page=wsal-togglealerts#tab-system-activity' ) ) . '">here</a> to log such requests to file';
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

			default:
				return '<strong>' . esc_html( $value ) . '</strong>';
		}
	}
}
