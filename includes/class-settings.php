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
}
