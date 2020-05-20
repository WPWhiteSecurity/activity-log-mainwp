<?php
/**
 * Class: Settings
 *
 * Settings class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 *
 * Settings class of the extension.
 */
class Settings {

	/**
	 * Get Plugin Option.
	 *
	 * @param string  $option  – Option name.
	 * @param boolean $default – Default value.
	 * @return mixed
	 */
	public function get_option( $option, $default = false ) {
		return get_option( MWPAL_OPT_PREFIX . $option, $default );
	}

	/**
	 * Update Plugin Option.
	 *
	 * @param string $option – Option name.
	 * @param mixed  $value  – Option value.
	 * @return boolean
	 */
	public function update_option( $option, $value = false ) {
		return update_option( MWPAL_OPT_PREFIX . $option, $value );
	}

	/**
	 * Delete Plugin Option.
	 *
	 * @param string $option – Option name.
	 * @return boolean
	 */
	public function delete_option( $option ) {
		return delete_option( MWPAL_OPT_PREFIX . $option );
	}

	/**
	 * Checks if extension is activated or not.
	 *
	 * @param string $default - Default value to return if option doesn't exist.
	 * @return string
	 */
	public function is_extension_activated( $default = 'no' ) {
		return $this->get_option( 'activity-extension-activated', $default );
	}

	/**
	 * Updates extension activated option.
	 *
	 * @param string $value - Value of the option.
	 * @return boolean
	 */
	public function set_extension_activated( $value ) {
		return $this->update_option( 'activity-extension-activated', $value );
	}

	/**
	 * Datetime used in the Alerts.
	 *
	 * @param bool $line_break – Line break.
	 */
	public function get_date_time_format( $line_break = true ) {
		if ( $line_break ) {
			$date_time_format = $this->get_date_format() . '<\b\r>' . $this->get_time_format();
		} else {
			$date_time_format = $this->get_date_format() . ' ' . $this->get_time_format();
		}

		$wp_time_format = get_option( 'time_format' ); // WP Time format.

		// Check if the time format does not have seconds.
		if ( stripos( $wp_time_format, 's' ) === false ) {
			if ( stripos( $wp_time_format, '.v' ) !== false ) {
				$date_time_format = str_replace( '.v', '', $date_time_format );
			}
			$date_time_format .= ':s'; // Add seconds to time format.
			$date_time_format .= '.$$$'; // Add milliseconds to time format.
		} else {
			// Check if the time format does have milliseconds.
			if ( stripos( $wp_time_format, '.v' ) !== false ) {
				$date_time_format = str_replace( '.v', '.$$$', $date_time_format );
			} else {
				$date_time_format .= '.$$$';
			}
		}

		if ( stripos( $wp_time_format, 'A' ) !== false ) {
			$date_time_format .= '&\n\b\s\p;A';
		}
		return $date_time_format;
	}

	/**
	 * Date Format from WordPress General Settings.
	 */
	public function get_date_format() {
		$wp_date_format = get_option( 'date_format' );
		$search         = array( 'F', 'M', 'n', 'j', ' ', '/', 'y', 'S', ',', 'l', 'D' );
		$replace        = array( 'm', 'm', 'm', 'd', '-', '-', 'Y', '', '', '', '' );
		$date_format    = str_replace( $search, $replace, $wp_date_format );
		return $date_format;
	}

	/**
	 * Time Format from WordPress General Settings.
	 */
	public function get_time_format() {
		$wp_time_format = get_option( 'time_format' );
		$search         = array( 'a', 'A', 'T', ' ' );
		$replace        = array( '', '', '', '' );
		$time_format    = str_replace( $search, $replace, $wp_time_format );
		return $time_format;
	}

	/**
	 * Get MainWP Child Sites.
	 *
	 * @return array
	 */
	public function get_mwp_child_sites() {
		$activity_log = \WSAL\MainWPExtension\mwpal_extension();
		return apply_filters( 'mainwp-getsites', $activity_log->get_child_file(), $activity_log->get_child_key(), null );
	}

	/**
	 * Set Alert Views Per Page.
	 *
	 * @param int $newvalue – New value.
	 */
	public function set_view_per_page( $newvalue ) {
		$perpage = max( $newvalue, 1 );
		$this->update_option( 'items-per-page', $perpage );
	}

	/**
	 * Get Alert Views Per Page.
	 *
	 * @return int
	 */
	public function get_view_per_page() {
		return (int) $this->get_option( 'items-per-page', 10 );
	}

	/**
	 * Return Site ID.
	 *
	 * @return integer
	 */
	public function get_view_site_id() {
		// @codingStandardsIgnoreStart
		$site_id = isset( $_REQUEST['mwpal-site-id'] ) ? sanitize_text_field( $_REQUEST['mwpal-site-id'] ) : 0; // Site ID.
		// @codingStandardsIgnoreEnd

		if ( 'dashboard' !== $site_id ) {
			return (int) $site_id;
		}
		return $site_id;
	}

	/**
	 * Method: Get number of hours since last logged alert.
	 *
	 * @return mixed – False if $created_on is empty | Number of hours otherwise.
	 *
	 * @param float $created_on – Timestamp of last logged alert.
	 */
	public function get_hours_since_last_alert( $created_on ) {
		// If $created_on is empty, then return.
		if ( empty( $created_on ) ) {
			return false;
		}

		// Last alert date.
		$created_date = new \DateTime( date( 'Y-m-d H:i:s', $created_on ) );

		// Current date.
		$current_date = new \DateTime( 'NOW' );

		// Calculate time difference.
		$time_diff = $current_date->diff( $created_date );
		$diff_days = $time_diff->d; // Difference in number of days.
		$diff_hrs  = $time_diff->h; // Difference in number of hours.
		$total_hrs = ( $diff_days * 24 ) + $diff_hrs; // Total number of hours.

		// Return difference in hours.
		return $total_hrs;
	}

	/**
	 * Return audit log columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'site'       => '1',
			'alert_code' => '1',
			'type'       => '1',
			'info'       => '1',
			'date'       => '1',
			'username'   => '1',
			'event_type' => '1',
			'object'     => '1',
			'source_ip'  => '1',
			'message'    => '1',
		);

		// Get selected columns.
		$selected = $this->get_columns_selected();

		if ( ! empty( $selected ) ) {
			$columns  = array(
				'site'       => '0',
				'alert_code' => '0',
				'type'       => '0',
				'info'       => '0',
				'date'       => '0',
				'username'   => '0',
				'event_type' => '0',
				'object'     => '0',
				'source_ip'  => '0',
				'message'    => '0',
			);
			$selected = (array) json_decode( $selected );
			$columns  = array_merge( $columns, $selected );
		}
		return $columns;
	}

	/**
	 * Get Selected Columns.
	 *
	 * @return string
	 */
	public function get_columns_selected() {
		return $this->get_option( 'columns' );
	}

	/**
	 * Set Columns.
	 *
	 * @param array $columns – Columns.
	 */
	public function set_columns( $columns ) {
		$this->update_option( 'columns', wp_json_encode( $columns ) );
	}

	/**
	 * Get WSAL Child Sites.
	 *
	 * @return array
	 */
	public function get_wsal_child_sites() {
		// Check if the WSAL child sites option exists.
		$child_sites = $this->get_option( 'wsal-child-sites' );

		// Get MainWP Child sites.
		$mwp_sites    = $this->get_mwp_child_sites();
		$activity_log = \WSAL\MainWPExtension\mwpal_extension();

		if ( empty( $child_sites ) && ! empty( $mwp_sites ) ) {
			foreach ( $mwp_sites as $site ) {
				$post_data = array( 'action' => 'check_wsal' );

				// Call to child sites to check if WSAL is installed on them or not.
				$results[ $site['id'] ] = apply_filters(
					'mainwp_fetchurlauthed',
					$activity_log->get_child_file(),
					$activity_log->get_child_key(),
					$site['id'],
					'extra_excution',
					$post_data
				);
			}

			if ( ! empty( $results ) && is_array( $results ) ) {
				$child_sites = array();

				foreach ( $results as $site_id => $site_array ) {
					if ( empty( $site_array ) || ! is_array( $site_array ) ) {
						continue;
					} elseif ( is_array( $site_array ) && isset( $site_array['wsal_installed'] ) && true === $site_array['wsal_installed'] ) {
						$child_sites[ $site_id ] = $site_array;
					}
				}
				$this->update_option( 'wsal-child-sites', $child_sites );
			}
		}
		return $child_sites;
	}

	/**
	 * Set WSAL Child Sites.
	 *
	 * @param array $site_ids – Array of Site ids.
	 * @return void
	 */
	public function set_wsal_child_sites( $site_ids ) {
		$wsal_sites     = $this->get_wsal_child_sites(); // Get WSAL child sites.
		$disabled_sites = $this->get_option( 'disabled-wsal-sites', array() ); // Get already disabled sites.
		$new_sites      = array();

		// Set new WSAL sites.
		if ( ! empty( $site_ids ) && is_array( $site_ids ) ) {
			foreach ( $site_ids as $id ) {
				if ( isset( $wsal_sites[ $id ] ) ) {
					$new_sites[ $id ] = $wsal_sites[ $id ];
					unset( $wsal_sites[ $id ] );
				} elseif ( isset( $disabled_sites[ $id ] ) ) {
					$new_sites[ $id ] = $disabled_sites[ $id ];
					unset( $disabled_sites[ $id ] );
				} else {
					$new_sites[ $id ] = new \stdClass();
				}
			}
		}

		// Remove events of removed sites from the DB.
		if ( ! empty( $wsal_sites ) && is_array( $wsal_sites ) ) {
			foreach ( $wsal_sites as $site_id => $site ) {
				// Delete events by site id.
				$delete_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();
				$delete_query->addCondition( 'site_id = %s ', $site_id );
				$delete_query->getAdapter()->Delete( $delete_query );
				$disabled_sites[ $site_id ] = $site;
			}
		}

		$this->update_option( 'wsal-child-sites', $new_sites );
		$this->update_option( 'disabled-wsal-sites', $disabled_sites );
	}

	/**
	 * Get Timezone.
	 *
	 * @return string
	 */
	public function get_timezone() {
		return $this->get_option( 'timezone', 'wp' );
	}

	/**
	 * Set Timezone.
	 *
	 * @param string $newvalue – New value.
	 */
	public function set_timezone( $newvalue ) {
		$this->update_option( 'timezone', $newvalue );
	}

	/**
	 * Get Username Type.
	 *
	 * @return string
	 */
	public function get_type_username() {
		return $this->get_option( 'type_username', 'display_name' );
	}

	/**
	 * Set Username Type.
	 *
	 * @param string $newvalue – New value.
	 */
	public function set_type_username( $newvalue ) {
		$this->update_option( 'type_username', $newvalue );
	}

	/**
	 * Get number of child site events.
	 *
	 * @return integer
	 */
	public function get_child_site_events() {
		return $this->get_option( 'child_site_events', 100 );
	}

	/**
	 * Set number of child site events.
	 *
	 * @param integer $newvalue – New value.
	 */
	public function set_child_site_events( $newvalue ) {
		$this->update_option( 'child_site_events', $newvalue );
	}

	/**
	 * Get Events Frequency.
	 *
	 * @return integer
	 */
	public function get_events_frequency() {
		return $this->get_option( 'events_frequency', 3 );
	}

	/**
	 * Set Events Frequency.
	 *
	 * @param integer $newvalue – New value.
	 */
	public function set_events_frequency( $newvalue ) {
		$this->update_option( 'events_frequency', $newvalue );
	}

	/**
	 * Get Current User Roles.
	 *
	 * @param array $base_roles – Base roles.
	 * @return array
	 */
	public function get_current_user_roles( $base_roles = null ) {
		if ( null === $base_roles ) {
			$base_roles = wp_get_current_user()->roles;
		}
		if ( function_exists( 'is_super_admin' ) && is_super_admin() ) {
			$base_roles[] = 'superadmin';
		}
		return $base_roles;
	}

	/**
	 * Check if the user is super admin.
	 *
	 * @param string $username – Username.
	 * @return boolean
	 */
	public function is_login_super_admin( $username ) {
		$user_id = username_exists( $username );
		if ( function_exists( 'is_super_admin' ) && is_super_admin( $user_id ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get Server IP.
	 *
	 * @return string
	 */
	public function get_server_ip() {
		$result = null;
		if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
			$result = $this->normalize_ip( sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) );
			if ( ! $this->validate_ip( $result ) ) {
				$result = 'Error ' . self::ERROR_CODE_INVALID_IP . ': Invalid IP Address';
			}
		}
		return $result;
	}

	/**
	 * Get Client IP.
	 *
	 * @return string
	 */
	public function get_main_client_ip() {
		$result = null;
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$result = $this->normalize_ip( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
			if ( ! $this->validate_ip( $result ) ) {
				$result = 'Error ' . self::ERROR_CODE_INVALID_IP . ': Invalid IP Address';
			}
		}
		return $result;
	}

	/**
	 * Normalize IP Address.
	 *
	 * @param string $ip – IP Address.
	 * @return string
	 */
	protected function normalize_ip( $ip ) {
		$ip = trim( $ip );
		if ( strpos( $ip, ':' ) !== false && substr_count( $ip, '.' ) == 3 && strpos( $ip, '[' ) === false ) {
			// IPv4 with a port (eg: 11.22.33.44:80).
			$ip = explode( ':', $ip );
			$ip = $ip[0];
		} else {
			// IPv6 with a port (eg: [::1]:80).
			$ip = explode( ']', $ip );
			$ip = ltrim( $ip[0], '[' );
		}
		return $ip;
	}

	/**
	 * Validate IP Address.
	 *
	 * @param string $ip – IP Address.
	 * @return string
	 */
	protected function validate_ip( $ip ) {
		$opts        = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
		$filtered_ip = filter_var( $ip, FILTER_VALIDATE_IP, $opts );
		if ( ! $filtered_ip || empty( $filtered_ip ) ) {
			// Regex IPV4.
			if ( preg_match( '/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $ip ) ) {
				return $ip;
			} // Regex IPV6.
			elseif ( preg_match( '/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/', $ip ) ) {
				return $ip;
			}
			return false;
		} else {
			return $filtered_ip;
		}
	}

	/**
	 * Search & Return MainWP site.
	 *
	 * @param string $column – Column name.
	 * @param string $value  – Column value.
	 * @return mixed
	 */
	public function get_mwp_site_by( $column = 'id', $value ) {
		// Get MainWP sites.
		$mwp_sites = $this->get_mwp_child_sites();

		// Search by column name.
		$key = array_search( $value, array_column( $mwp_sites, $column ), true );
		if ( false !== $key ) {
			return $mwp_sites[ $key ];
		}
		return false;
	}

	/**
	 * Get last checked timestamp by site id.
	 *
	 * @since 1.0.1
	 *
	 * @param integer $site_id - Site id.
	 * @return mixed
	 */
	public function get_last_checked_by_siteid( $site_id = 0 ) {
		if ( $site_id ) {
			return $this->get_option( 'mwpal_last_checked_site_' . $site_id );
		}
		return false;
	}

	/**
	 * Set last checked timestamp by site id.
	 *
	 * @since 1.0.1
	 *
	 * @param integer $site_id      - Site id.
	 * @param string  $last_checked - Last checked timestamp.
	 */
	public function set_last_checked_by_siteid( $site_id = 0, $last_checked ) {
		if ( $site_id ) {
			$this->update_option( 'mwpal_last_checked_site_' . $site_id, $last_checked );
		}
	}

	/**
	 * Return Events Navigation Type.
	 *
	 * @since 1.1
	 *
	 * @return string
	 */
	public function get_events_type_nav() {
		return $this->get_option( 'events-nav-type', 'infinite-scroll' );
	}

	/**
	 * Sets Events Navigation Type.
	 *
	 * @since 1.1
	 *
	 * @param string $nav_type - Navigation type.
	 */
	public function set_events_type_nav( $nav_type ) {
		$this->update_option( 'events-nav-type', $nav_type );
	}

	/**
	 * Returns true if infinite scroll, otherwise false.
	 *
	 * @since 1.1
	 *
	 * @return boolean
	 */
	public function is_infinite_scroll() {
		return 'infinite-scroll' === $this->get_events_type_nav();
	}

	/**
	 * Sets Events Global Sync Option.
	 *
	 * @since 1.1
	 *
	 * @param bool $enabled - True if enabled, false otherwise.
	 */
	public function set_events_global_sync( $enabled ) {
		$this->update_option( 'events-global-sync', $enabled );
	}

	/**
	 * Returns true if events global sync is set, otherwise false.
	 *
	 * @since 1.1
	 *
	 * @return boolean
	 */
	public function is_events_global_sync() {
		return $this->get_option( 'events-global-sync' );
	}

	/**
	 * Checks if current admin page is extension's page or not.
	 *
	 * @since 1.1
	 *
	 * @return boolean
	 */
	public function is_current_extension_page() {
		global $pagenow;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // phpcs:ignore

		if ( 'admin.php' === $pagenow && MWPAL_EXTENSION_NAME === $page ) {
			return true;
		}

		return false;
	}

	/**
	 * Meta data formater.
	 *
	 * @param string  $name   - Name of the data.
	 * @param mixed   $value  - Value of the data.
	 * @param integer $occ_id - Event occurrence id.
	 * @return string
	 */
	public function meta_formatter( $name, $value, $occ_id ) {
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
					$site_id = $this->get_view_site_id(); // Site id for multisite.
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
				if ( $occ_id ) {
					$url_args = array(
						'action'     => 'AjaxInspector',
						'occurrence' => $occ_id,
						'TB_iframe'  => 'true',
						'width'      => 600,
						'height'     => 550,
					);
					$url      = add_query_arg( $url_args, admin_url( 'admin-ajax.php' ) );
					return ' View the changes in <a class="thickbox"  title="' . __( 'Alert Data Inspector', 'mwp-al-ext' ) . '"'
					. ' href="' . $url . '">data inspector.</a>';
				} else {
					return;
				}

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

			default:
				return '<strong>' . esc_html( $value ) . '</strong>';
		}
	}

	/**
	 * Set disabled events.
	 *
	 * @param array $types - IDs events to disable.
	 */
	public function set_disabled_events( $types ) {
		$disabled = array_unique( array_map( 'intval', $types ) );
		$this->update_option( 'disabled-events', implode( ',', $disabled ) );
	}

	/**
	 * Return IDs of disabled events.
	 *
	 * @return array
	 */
	public function get_disabled_events() {
		$disabled = $this->get_option( 'disabled-events', false );
		$disabled = ! $disabled ? array() : explode( ',', $disabled );
		$disabled = array_map( 'intval', $disabled );
		return $disabled;
	}

	/**
	 * Set events pruning.
	 *
	 * @param string $state - Enabled or disabled state.
	 */
	public function set_events_pruning( $state ) {
		$this->update_option( 'events-pruning', $state );
	}

	/**
	 * Checks if events pruning is enabled or disabled.
	 *
	 * @return boolean
	 */
	public function is_events_pruning() {
		$pruning = $this->get_option( 'events-pruning', 'disabled' );
		return 'enabled' === $pruning ? true : false;
	}

	/**
	 * Set events pruning date.
	 *
	 * @param string $date - Date.
	 * @param string $unit - Time unit.
	 */
	public function set_pruning_date( $date, $unit ) {
		$date_obj       = new \stdClass();
		$date_obj->date = $date;
		$date_obj->unit = $unit;
		$this->update_option( 'pruning-date', $date_obj );
	}

	/**
	 * Get events pruning date.
	 *
	 * @return stdClass|bool
	 */
	public function get_pruning_date() {
		$default       = new \stdClass();
		$default->date = false;
		$default->unit = false;
		return $this->get_option( 'pruning-date', $default );
	}
}
