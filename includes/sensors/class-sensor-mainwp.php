<?php
/**
 * Sensor Class: MainWP
 *
 * MainWP sensor class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Sensors;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MainWP Sensor Class.
 */
class Sensor_MainWP {

	/**
	 * Instance of main plugin class.
	 *
	 * @var \WSAL\MainWPExtension\Activity_Log
	 */
	public $activity_log;

	/**
	 * Constructor.
	 *
	 * @param Activity_Log $activity_log – Instance of main class.
	 */
	public function __construct( Activity_Log $activity_log ) {
		$this->activity_log = $activity_log;
	}

	/**
	 * Hook Events
	 *
	 * Listening to events using hooks.
	 */
	public function hook_events() {
		add_action( 'mainwp_added_new_site', array( $this, 'site_added' ), 10, 1 );
		add_action( 'mainwp_delete_site', array( $this, 'site_removed' ), 10, 1 );
		add_action( 'mainwp_update_site', array( $this, 'site_edited' ), 10, 1 );
		add_action( 'mainwp-site-synced', array( $this, 'site_synced' ), 10, 1 );
	}

	/**
	 * MainWP Site Added
	 *
	 * Site added to MainWP dashboard.
	 *
	 * @param int $new_site_id – New site id.
	 */
	public function site_added( $new_site_id ) {
		if ( empty( $new_site_id ) ) {
			return;
		}

		// Get MainWP child sites.
		$mwp_sites = $this->activity_log->settings->get_mwp_child_sites();

		// Search for the site data.
		$key = array_search( $new_site_id, array_column( $mwp_sites, 'id' ), false );

		if ( false !== $key && isset( $mwp_sites[ $key ] ) ) {
			$this->activity_log->alerts->trigger( 7700, array(
				'friendly_name' => $mwp_sites[ $key ]['name'],
				'site_url'      => $mwp_sites[ $key ]['url'],
				'site_id'       => $mwp_sites[ $key ]['id'],
				'mainwp_dash'   => true,
			) );
		}
	}

	/**
	 * MainWP Site Removed
	 *
	 * Site removed from MainWP dashboard.
	 *
	 * @param stdClass $website – Removed website.
	 */
	public function site_removed( $website ) {
		// Return if empty.
		if ( empty( $website ) ) {
			return;
		}

		if ( isset( $website->name ) ) {
			$this->activity_log->alerts->trigger( 7701, array(
				'friendly_name' => $website->name,
				'site_url'      => $website->url,
				'site_id'       => $website->id,
				'mainwp_dash'   => true,
			) );
		}
	}

	/**
	 * MainWP Site Edited
	 *
	 * Site edited from MainWP dashboard.
	 *
	 * @param int $site_id – Site id.
	 */
	public function site_edited( $site_id ) {
		if ( empty( $site_id ) ) {
			return;
		}

		// Get MainWP child sites.
		$mwp_sites = $this->activity_log->settings->get_mwp_child_sites();

		// Search for the site data.
		$key = array_search( $site_id, array_column( $mwp_sites, 'id' ), false );

		if ( false !== $key && isset( $mwp_sites[ $key ] ) ) {
			$this->activity_log->alerts->trigger( 7702, array(
				'friendly_name' => $mwp_sites[ $key ]['name'],
				'site_url'      => $mwp_sites[ $key ]['url'],
				'site_id'       => $mwp_sites[ $key ]['id'],
				'mainwp_dash'   => true,
			) );
		}
	}

	/**
	 * MainWP Site Synced
	 *
	 * Site synced from MainWP dashboard.
	 *
	 * @param stdClass $website – Removed website.
	 */
	public function site_synced( $website ) {
		// Return if empty.
		if ( empty( $website ) ) {
			return;
		}

		if ( isset( $website->name ) ) {
			$this->activity_log->alerts->trigger( 7703, array(
				'friendly_name' => $website->name,
				'site_url'      => $website->url,
				'site_id'       => $website->id,
				'mainwp_dash'   => true,
			) );
		}
	}
}
