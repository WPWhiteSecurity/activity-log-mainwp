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

/**
 * Audit Log List View
 *
 * Log view class which extends WP List Table class.
 */
class AuditLogGridView extends AuditLogView {

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
							<img src="<?php echo MWPAL_BASE_URL ?>assets/img/activity-log-mainwp-150x150.jpg">
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
							<a target="_blank" href="<?php echo esc_url( 'https://wpactivitylog.com/extensions/mainwp-activity-log/pricing/?utm_source=plugin&utm_medium=referral&utm_campaign=AL4MWP&utm_content=banner+upgrade+now' ); ?>" class="ui button green"><?php esc_html_e( 'Upgrade Now', 'mwp-al-ext' ); ?></a>
							<a target="_blank" href="<?php echo esc_url( 'https://wpactivitylog.com/extensions/mainwp-activity-log/premium-benefits/?utm_source=plugin&utm_medium=referral&utm_campaign=AL4MWP&utm_content=banner+tell+me+more' ); ?>" class="ui button"><?php esc_html_e( 'Tell Me More', 'mwp-al-ext' ); ?></a>
						</div>
						<div class="close-btn">
							<a href="javascript:;"><?php esc_html_e( 'Close', 'mwp-al-ext' ); ?></a>
						</div>
					</div>
				</div>
			</div>
			<?php endif;
		}
	}

	/**
	 * Method: Get default column values.
	 *
	 * @param object $item - Column item.
	 * @param string $column_name - Name of the column.
	 *
	 * @return false|string|void
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

			case 'info':
				$date_format       = MWPAL_Extension\mwpal_extension()->settings->get_date_format();
				$show_microseconds = MWPAL_Extension\mwpal_extension()->settings->get_time_format();
				if ( 'no' === $show_microseconds ) {
					// remove the microseconds placeholder from format string.
					$datetime_format = str_replace( '.$$$', '', $datetime_format );
				}
				$eventdate = $item->created_on ? (
						str_replace(
							'$$$',
							substr( number_format( fmod( $item->created_on + $this->_gmt_offset_sec, 1 ), 3 ), 2 ),
							date( $date_format, $item->created_on + $this->_gmt_offset_sec )
						)
					) : '<i>' . __( 'Unknown', 'mwp-al-ext' ) . '</i>';
				$eventtime = $item->created_on ? (
						str_replace(
							'$$$',
							substr( number_format( fmod( $item->created_on + $this->_gmt_offset_sec, 1 ), 3 ), 2 ),
							date( get_option( 'time_format' ), $item->created_on + $this->_gmt_offset_sec )
						)
					) : '<i>' . __( 'Unknown', 'mwp-al-ext' ) . '</i>';

				$username  = $item->GetUsername( $this->item_meta[ $item->getId() ] ); // Get username.
				$user_data = $item->get_user_data( $this->item_meta[ $item->getId() ] ); // Get user data.

				if ( empty( $user_data ) ) {
					$user_data = get_user_by( 'login', $username );
					if ( isset( $user_data->data ) && ! empty( $user_data->data ) ) {
						$user_data = json_decode( wp_json_encode( $user_data->data ), true );
					}
				}

				// Check if the usernames exists & matches pre-defined cases.
				if ( 'Plugin' === $username ) {
					$uhtml = '<i>' . __( 'Plugin', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'Plugins' === $username ) {
					$uhtml = '<i>' . __( 'Plugins', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'Website Visitor' === $username ) {
					$uhtml = '<i>' . __( 'Website Visitor', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( 'System' === $username ) {
					$uhtml = '<i>' . __( 'System', 'mwp-al-ext' ) . '</i>';
					$roles = '';
				} elseif ( $user_data && 'System' !== $username ) {
					// Checks for display name.
					$user_meta = ( isset( $user_data['ID'] ) ) ? get_user_meta( $user_data['ID'] ) : get_user_meta( $user_data['user_id'] ) ;
					if ( 'display_name' === $type_username && ! empty( $user_data['display_name'] ) ) {
						$display_name = $user_data['display_name'];
					} elseif (
						'first_last_name' === $type_username
						&& ( ! empty( $user_data['first_name'] ) || ! empty( $user_data['last_name'] ) )
					) {
						$display_name = $user_data['first_name'] . ' ' . $user_data['last_name'];
					} elseif (
						'first_last_name' === $type_username
						&& ( ! empty( $user_meta['first_name'][0] ) || ! empty( $user_meta['last_name'][0] ) )
					) {
						$display_name = $user_meta['first_name'][0] . ' ' . $user_meta['last_name'][0];
					} else {
						$display_name = $username;
					}

					if ( $this->query_args->site_id && 'live' === $this->query_args->get_events ) {
						$site_id = (string) $this->query_args->site_id;
					} else {
						$site_id = (string) $item->site_id;
					}

					$site_index = array_search( $site_id, array_column( $mwp_child_sites, 'id' ), true );
					if ( false !== $site_index && isset( $mwp_child_sites[ $site_index ] ) ) {
						$site_url = $mwp_child_sites[ $site_index ]['url'];
						$user_url = add_query_arg( 'user_id', $user_data['user_id'], trailingslashit( $site_url ) . 'wp-admin/user-edit.php' );
					} else {
						$user_url = add_query_arg( 'user_id', $user_data['ID'], admin_url( 'user-edit.php' ) );
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
				$row_user_data = $uhtml . '<br/>' . $roles;

				/**
				 * WSAL Filter: `wsal_auditlog_row_user_data`
				 *
				 * Filters user data before displaying on the audit log.
				 *
				 * @since 3.3.1
				 *
				 * @param string  $row_user_data          - User data to display in audit log row.
				 * @param integer $this->current_alert_id - Event database ID.
				 */
				$eventuser = apply_filters( 'wsal_auditlog_row_user_data', $row_user_data, $this->current_alert_id );



				$scip = $item->GetSourceIP( $this->item_meta[ $item->getId() ] );
				if ( is_string( $scip ) ) {
					$scip = str_replace( array( '"', '[', ']' ), '', $scip );
				}

				$oips = array();

				// If there's no IP...
				if ( is_null( $scip ) || '' == $scip ) {
					return '<i>unknown</i>';
				}

				// If there's only one IP...
				$link = 'https://whatismyipaddress.com/ip/' . $scip . '?utm_source=plugin&utm_medium=referral&utm_campaign=WPSAL';
				if ( class_exists( 'WSAL_SearchExtension' ) ) {
					$tooltip = esc_attr__( 'Show me all activity originating from this IP Address', 'mwp-al-ext' );

					if ( count( $oips ) < 2 ) {
						$oips_html = "<a class='search-ip' data-tooltip='$tooltip' data-ip='$scip' target='_blank' href='$link'>" . esc_html( $scip ) . '</a>';
					}
				} else {
					if ( count( $oips ) < 2 ) {
						$oips_html = "<a target='_blank' href='$link'>" . esc_html( $scip ) . '</a>';
					}
				}

				// If there are many IPs...
				if ( class_exists( 'WSAL_SearchExtension' ) ) {
					$tooltip = esc_attr__( 'Show me all activity originating from this IP Address', 'mwp-al-ext' );

					$ip_html = "<a class='search-ip' data-tooltip='$tooltip' data-ip='$scip' target='_blank' href='https://whatismyipaddress.com/ip/$scip'>" . esc_html( $scip ) . '</a> <a href="javascript:;" onclick="jQuery(this).hide().next().show();">(more&hellip;)</a><div style="display: none;">';
					foreach ( $oips as $ip ) {
						if ( $scip != $ip ) {
							$ip_html .= '<div>' . $ip . '</div>';
						}
					}
					$ip_html .= '</div>';
				} else {
					$ip_html = "<a target='_blank' href='https://whatismyipaddress.com/ip/$scip'>" . esc_html( $scip ) . '</a> <a href="javascript:;" onclick="jQuery(this).hide().next().show();">(more&hellip;)</a><div style="display: none;">';
					foreach ( $oips as $ip ) {
						if ( $scip != $ip ) {
							$ip_html .= '<div>' . $ip . '</div>';
						}
					}
					$ip_html .= '</div>';
				}



				$eventobj = isset( $this->item_meta[ $item->getId() ]['Object'] ) ? MWPAL_Extension\Activity_Log::get_instance()->alerts->get_display_object_text( $this->item_meta[ $item->getId() ]['Object'] ) : '';

				$eventtypeobj = isset( $this->item_meta[ $item->getId() ]['EventType'] ) ? MWPAL_Extension\Activity_Log::get_instance()->alerts->get_display_event_type_text( $this->item_meta[ $item->getId() ]['EventType'] ) : '';

				ob_start();
				?>
				<table>
					<tr>
						<td class="wsal-grid-text-header"><?php esc_html_e( 'Date:' ); ?></td>
						<td class="wsal-grid-text-data"><?php echo $eventdate; ?></td>
					</tr>
					<tr>
						<td class="wsal-grid-text-header"><?php esc_html_e( 'Time:' ); ?></td>
						<td class="wsal-grid-text-data"><?php echo $eventtime; ?></td>
					</tr>
					<tr>
						<td class="wsal-grid-text-header"><?php esc_html_e( 'User:' ); ?></td>
						<td class="wsal-grid-text-data"><?php echo $eventuser; ?></td>
					</tr>
					<tr>
						<td class="wsal-grid-text-header"><?php esc_html_e( 'IP:' ); ?></td>
						<td class="wsal-grid-text-data"><?php echo ( isset( $oips_html ) && ! empty( $oips_html ) ) ? $oips_html : $ip_html ?></td>
					</tr>
					<tr>
						<td class="wsal-grid-text-header"><?php esc_html_e( 'Object:' ); ?></td>
						<td class="wsal-grid-text-data"><?php echo $eventobj; ?></td>
					</tr>
					<tr>
						<td class="wsal-grid-text-header"><?php esc_html_e( 'Event Type:' ); ?></td>
						<td class="wsal-grid-text-data"><?php echo $eventtypeobj; ?></td>
					</tr>
				</table>
				<?php
				return ob_get_clean();

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
			'site' => __( 'Site', 'mwp-al-ext' ),
			'type' => __( 'Event ID', 'mwp-al-ext' ),
			'code' => __( 'Severity', 'mwp-al-ext' ),
			'info' => __( 'Info', 'mwp-al-ext' ),
			'mesg' => __( 'Message', 'mwp-al-ext' ),
			'data' => '',
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
					case 'info':
						$cols['info'] = __( 'Info', 'mwp-al-ext' );
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
			'read' => array( 'is_read', false ),
			'type' => array( 'alert_id', false ),
			'info' => array( 'created_on', true ),
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
				return ' with errors. ' . sprintf( __( 'Contact us on %s for assistance', 'mwp-al-ext' ), '<a href="mailto:support@wpactivitylog.com" target="_blank">support@wpactivitylog.com</a>' );

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
				return '<a href="https://www.wpactivitylog.com/contact/" target="_blank">' . esc_html__( 'contact our support', 'mwp-al-ext' ) . '</a>';

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
				<input type="button" id="mwpal-clear-search" class="almwp-button" value="<?php esc_attr_e( 'Clear Search Results', 'mwp-al-ext' ); ?>">
			</div>
			<div id="mwpal-search-list" class="mwpal-search-filters-list no-filters"></div>
		</div>
		<?php
	}

	protected function get_view_type() {
		return 'grid';
	}
}
