<?php
/**
 * Class: Settings
 *
 * Settings class file of the extension.
 *
 * @package mwp-al-ext
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

		$wp_time_format = get_option( 'time_format' );
		if ( stripos( $wp_time_format, 'A' ) !== false ) {
			$date_time_format .= '.$$$&\n\b\s\p;A';
		} else {
			$date_time_format .= '.$$$';
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
		$activity_log = \WSAL\MainWPExtension\Activity_Log::get_instance();
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
		return isset( $_GET['mwpal-site-id'] ) ? (int) sanitize_text_field( $_GET['mwpal-site-id'] ) : 0; // Site ID.
		// @codingStandardsIgnoreEnd
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
			'date'       => '1',
			'username'   => '1',
			'source_ip'  => '1',
			'message'    => '1',
		);

		// Get selected columns.
		$selected = $this->get_columns_selected();

		if ( ! empty( $selected ) ) {
			$columns = array(
				'site'       => '0',
				'alert_code' => '0',
				'type'       => '0',
				'date'       => '0',
				'username'   => '0',
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
		$activity_log = \WSAL\MainWPExtension\Activity_Log::get_instance();

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

				foreach ( $results as $site_id => $site_obj ) {
					if ( empty( $site_obj ) ) {
						continue;
					} elseif ( true === $site_obj->wsal_installed ) {
						$child_sites[ $site_id ] = $site_obj;
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
		if ( empty( $site_ids ) || ! is_array( $site_ids ) ) {
			return;
		}

		// Get WSAL child sites.
		$wsal_sites = $this->get_wsal_child_sites();
		$new_sites  = array();

		foreach ( $site_ids as $id ) {
			if ( isset( $wsal_sites[ $id ] ) ) {
				$new_sites[ $id ] = $wsal_sites[ $id ];
			} else {
				$new_sites[ $id ] = new \stdClass();
			}
		}
		$this->update_option( 'wsal-child-sites', $new_sites );
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
}
