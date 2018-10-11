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
class Sensor_MainWP extends Abstract_Sensor {

	/**
	 * List of Plugins.
	 *
	 * @var array
	 */
	protected $old_plugins = array();

	/**
	 * Hook Events
	 *
	 * Listening to events using hooks.
	 */
	public function hook_events() {
		add_action( 'mainwp_added_new_site', array( $this, 'site_added' ), 10, 1 ); // Site added.
		add_action( 'mainwp_delete_site', array( $this, 'site_removed' ), 10, 1 ); // Site removed.
		add_action( 'mainwp_update_site', array( $this, 'site_edited' ), 10, 1 ); // Site edited.
		add_action( 'mainwp-site-synced', array( $this, 'site_synced' ), 10, 1 ); // Site synced.
		add_action( 'mainwp_synced_all_sites', array( $this, 'synced_all_sites' ) ); // All sites synced.
		add_action( 'mainwp_added_extension_menu', array( $this, 'added_extension_menu' ), 10, 1 ); // Extension added to MainWP menu.
		add_action( 'mainwp_removed_extension_menu', array( $this, 'removed_extension_menu' ), 10, 1 ); // Extension removed from MainWP menu.
		add_action( 'activated_plugin', array( $this, 'mwp_extension_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( $this, 'mwp_extension_deactivated' ), 10, 2 );

		$has_permission = ( current_user_can( 'install_plugins' ) || current_user_can( 'delete_plugins' ) || current_user_can( 'update_plugins' ) );
		if ( $has_permission ) { // Check user permissions.
			add_action( 'admin_init', array( $this, 'event_admin_init' ) );
			add_action( 'shutdown', array( $this, 'admin_shutdown' ), 10 );
		}
	}

	/**
	 * Triggered when a user accesses the admin area.
	 */
	public function event_admin_init() {
		$this->old_plugins = get_plugins();
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

		// @codingStandardsIgnoreStart
		$is_global_sync = isset( $_POST['isGlobalSync'] ) ? sanitize_text_field( wp_unslash( $_POST['isGlobalSync'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( 'true' === $is_global_sync ) { // Check if not global sync.
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

	/**
	 * MainWP Sites Synced
	 *
	 * Log event when MainWP sites are synced altogether.
	 */
	public function synced_all_sites() {
		// @codingStandardsIgnoreStart
		$is_global_sync = isset( $_POST['isGlobalSync'] ) ? sanitize_text_field( wp_unslash( $_POST['isGlobalSync'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( 'true' !== $is_global_sync ) { // Check if global sync is false.
			return;
		}

		// Trigger global sync event.
		$this->activity_log->alerts->trigger( 7704, array( 'mainwp_dash' => true ) );
	}

	/**
	 * MainWP Extension Added
	 *
	 * MainWP extension added to menu.
	 *
	 * @param string $slug – Extension slug.
	 */
	public function added_extension_menu( $slug ) {
		$this->extension_menu_edited( $slug, 'Added' );
	}

	/**
	 * MainWP Extension Removed
	 *
	 * MainWP extension removed from menu.
	 *
	 * @param string $slug – Extension slug.
	 */
	public function removed_extension_menu( $slug ) {
		$this->extension_menu_edited( $slug, 'Removed' );
	}

	/**
	 * MainWP Menu Edited
	 *
	 * Extension added/removed from MainWP menu.
	 *
	 * @param string $slug   – Slug of the extension.
	 * @param string $action – Added or Removed action.
	 */
	public function extension_menu_edited( $slug, $action ) {
		// Check if the slug is not empty and it is active.
		if ( ! empty( $slug ) && \is_plugin_active( $slug ) ) {
			$this->activity_log->alerts->trigger( 7709, array(
				'mainwp_dash' => true,
				'extension'   => $slug,
				'action'      => $action,
				'option'      => 'Added' === $action ? 'to' : 'from',
			) );
		}
	}

	/**
	 * MainWP Extension Activated
	 *
	 * @param string $extension – Extension file path.
	 */
	public function mwp_extension_activated( $extension ) {
		$this->extension_log_event( 7706, $extension );
	}

	/**
	 * MainWP Extension Deactivated
	 *
	 * @param string $extension – Extension file path.
	 */
	public function mwp_extension_deactivated( $extension ) {
		$this->extension_log_event( 7707, $extension );
	}

	/**
	 * Add Extension Event
	 *
	 * @param string $event     – Event ID.
	 * @param string $extension – Name of extension.
	 */
	private function extension_log_event( $event = 0, $extension ) {
		$extension_dir = explode( '/', $extension );
		$extension_dir = isset( $extension_dir[0] ) ? $extension_dir[0] : false;

		if ( ! $extension_dir ) {
			return;
		}

		// Get MainWP extensions data.
		$mwp_extensions = \MainWP_Extensions_View::getAvailableExtensions();
		$extension_ids  = array_keys( $mwp_extensions );
		if ( ! in_array( $extension_dir, $extension_ids, true ) ) {
			return;
		}

		if ( $event ) {
			// Event data.
			$event_data = array();

			if ( 7708 === $event ) {
				// Get extension data.
				$plugin_file = trailingslashit( WP_PLUGIN_DIR ) . $extension;
				$event_data  = array(
					'mainwp_dash'    => true,
					'extension_name' => isset( $mwp_extensions[ $extension_dir ]['title'] ) ? $mwp_extensions[ $extension_dir ]['title'] : false,
					'PluginFile'     => $plugin_file,
					'PluginData'     => (object) array(
						'Name' => isset( $mwp_extensions[ $extension_dir ]['title'] ) ? $mwp_extensions[ $extension_dir ]['title'] : false,
					),
				);
			} else {
				// Get extension data.
				$plugin_file = trailingslashit( WP_PLUGIN_DIR ) . $extension;
				$plugin_data = get_plugin_data( $plugin_file );
				$event_data  = array(
					'mainwp_dash'    => true,
					'extension_name' => isset( $mwp_extensions[ $extension_dir ]['title'] ) ? $mwp_extensions[ $extension_dir ]['title'] : false,
					'Plugin'         => (object) array(
						'Name'            => $plugin_data['Name'],
						'PluginURI'       => $plugin_data['PluginURI'],
						'Version'         => $plugin_data['Version'],
						'Author'          => $plugin_data['Author'],
						'Network'         => $plugin_data['Network'] ? 'True' : 'False',
						'plugin_dir_path' => $plugin_file,
					),
				);
			}

			// Log the event.
			$this->activity_log->alerts->trigger( $event, $event_data );
		}
	}

	/**
	 * Log Extension Install/Uninstall Events.
	 */
	public function admin_shutdown() {
		// Get action from $_GET array.
		// @codingStandardsIgnoreStart
		$action      = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : false;
		$plugin      = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : false;
		$checked     = isset( $_POST['checked'] ) ? array_map( 'sanitize_text_field', $_POST['checked'] )  : false;
		$script_name = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : false;
		// @codingStandardsIgnoreEnd

		$actype = '';
		if ( ! empty( $script_name ) ) {
			$actype = basename( $script_name, '.php' );
		}
		$is_plugins = 'plugins' === $actype;

		// Install plugin.
		if ( in_array( $action, array( 'install-plugin', 'upload-plugin' ), true ) && current_user_can( 'install_plugins' ) ) {
			$wp_plugins = get_plugins();
			$plugin     = array_values( array_diff( array_keys( $wp_plugins ), array_keys( $this->old_plugins ) ) );
			if ( count( $plugin ) !== 1 ) {
				return $this->log_error(
					'Expected exactly one new plugin but found ' . count( $plugin ),
					array(
						'NewPlugin'  => $plugin,
						'OldPlugins' => $this->old_plugins,
						'NewPlugins' => $wp_plugins,
					)
				);
			}
			$this->extension_log_event( 7705, $plugin[0] );
		}

		// Uninstall plugin.
		if ( in_array( $action, array( 'delete-selected', 'delete-plugin' ), true ) && current_user_can( 'delete_plugins' ) ) {
			if ( 'delete-plugin' === $action && ! empty( $plugin ) ) {
				$this->extension_log_event( 7708, $plugin );
			} elseif ( $is_plugins && 'delete-selected' === $action && ! empty( $checked ) && is_array( $checked ) ) {
				foreach ( $checked as $plugin ) {
					$this->extension_log_event( 7708, $plugin );
				}
			}
		}
	}
}
