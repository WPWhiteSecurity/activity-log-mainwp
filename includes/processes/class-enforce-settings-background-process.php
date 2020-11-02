<?php

namespace WSAL\MainWPExtension;

use MainWP\Dashboard\MainWP_DB;

abstract class Enforce_Settings_Background_Process extends \WP_Background_Process {

	/**
	 * Task
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		if ( ! array_key_exists( 'site_id', $item ) ) {
			return false;
		}

		$child_site_id = intval( $item['site_id'] );
		if ( $child_site_id <= 0 ) {
			return false;
		}

		if ( ! $this->check_item( $item ) ) {
			return false;
		}

		//  check plugin compatibility
		$compatibility = $this->check_wsal_plugin_compatibility( $child_site_id );
		if ( is_wp_error( $compatibility ) ) {
			$this->trigger_error_event( $child_site_id, $compatibility->get_error_message() );
			return false;
		}

		// Post data for child sites.
		$post_data = $this->change_api_data( array(
			'action'    => 'enforce_settings',
			'subaction' => $this->get_subaction()
		), $item );

		//  call to child sites to add/update the enforces settings
		$plugin = mwpal_extension();

		$result = apply_filters(
			'mainwp_fetchurlauthed',
			$plugin->get_child_file(),
			$plugin->get_child_key(),
			$child_site_id,
			'extra_excution',
			$post_data
		);

		//  check result and maybe trigger 7717 with error message
		if ( array_key_exists( 'success', $result ) && 'yes' === $result['success'] ) {
			//  all good
			return false;
		}

		//  there might be an error message returned
		if ( array_key_exists( 'success', $result ) && 'no' === $result['success'] ) {
			$error_message = __( 'No error message returned from the child site.', 'mwp-al-ext' );
			if ( array_key_exists( 'message', $result ) ) {
				$error_message = $result['message'];
			}
			$this->trigger_error_event( $child_site_id, $error_message );
		}

		//  non-standard reply from the child site, this could be some sort of communication error or an older version of the plugin
		return false;
	}

	protected function check_item( $item ) {
		//  item valid by default
		return true;
	}

	/**
	 * @param int $child_site_id MainWP child site ID.
	 *
	 * @return bool|\WP_Error
	 */
	protected function check_wsal_plugin_compatibility( $child_site_id ) {

		//  check if the child site supports the setting enforcing (minimum version of WSAL 4.1.5)
		if ( ! class_exists( 'MainWP\Dashboard\MainWP_DB' ) ) {
			//  MainWP class not available for some reason
			return new \WP_Error( 'mainwp_wsal_version_check', 'MainWP plugin not loaded.' );
		}

		$website = MainWP_DB::instance()->get_website_by_id( $child_site_id );
		if ( isset( $website->sync_errors ) && ! empty( $website->sync_errors ) ) {
			return new \WP_Error( 'mainwp_wsal_version_check', $website->sync_errors );
		}

		$allPlugins = json_decode( $website->plugins, true );
		if ( ! is_array( $allPlugins ) || empty( $allPlugins ) ) {
			return new \WP_Error( 'mainwp_wsal_version_check', 'No plugins found on the child site.' );
		}

		$wsal_found = false;
		foreach ( $allPlugins as $plugin_info ) {
			if ( 'wp-security-audit-log.php' == basename( $plugin_info['slug'] ) ) {
				$wsal_found = true;
				break;
			}
		}

		if ( ! $wsal_found) {
			return new \WP_Error( 'mainwp_wsal_version_check', 'WP Activity Log plugin was not found on the child site.' );
		}

		$error_type                = '';
		$compatible_wsal_available = false;
		foreach ( $allPlugins as $plugin_info ) {
			//  there might be the free as well as premium version of WSL plugin installed so we need to continue in the
			//  loop instead of breaking out below
			if ( 'wp-security-audit-log.php' != basename( $plugin_info['slug'] ) ) {
				continue;
			}

			//  WSAL is found on the child site
			if ( $plugin_info['active'] != 1 ) {
				//  ...but it is not active
				$error_type = 'not_active';
				continue;
			}

			//  WSAL check version
			if ( version_compare( $plugin_info['version'], '4.1.5' ) < 0 ) {
				//  WSAL plugin is an older version that does not support the enforced settings
				$error_type = 'not_compat';
				continue;
			}

			$compatible_wsal_available = true;
		}

		if ( ! $compatible_wsal_available ) {

			//  determine log error message
			$error_message = 'Unknown error occurred when checking compatibility of WP Activity Log plugin on the child site.';
			switch ( $error_type ) {
				case 'not_active':
					$error_message = 'WP Activity Log plugin on the child site is not active.';
					break;
				case 'not_compat':
					$error_message = 'Version of WP Activity Log plugin on the child site is not 4.1.5 or above.';
					break;
			}

			return new \WP_Error( 'mainwp_wsal_version_check', $error_message );
		}

		return true;
	}

	protected function trigger_error_event( $child_site_id, $error_message ) {
		$site = mwpal_extension()->settings->get_mwp_child_site_by_id( $child_site_id );
		if ( $site != null ) {
			mwpal_extension()->alerts->trigger(
				7717,
				array(
					'friendly_name' => $site['name'],
					'site_url'      => $site['url'],
					'message'       => $error_message,
				)
			);
		}
	}

	/**
	 * @param mixed[] $data
	 * @param mixed[] $item
	 *
	 * @return mixed[]
	 */
	protected function change_api_data( $data, $item ) {
		return $data;
	}

	/**
	 * @return string
	 */
	abstract protected function get_subaction();

	/**
	 * Log an event upon completion.
	 */
	protected function complete() {
		parent::complete();

		//  log event 7716:stopped
		$plugin = mwpal_extension();
		$plugin->alerts->trigger( 7716, [
			'EventType' => 'stopped'
		] );

	}
}
