<?php
/**
 * Sensor Class: MainWP
 *
 * MainWP sensor class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Sensors;

use \WSAL\MainWPExtension as MWPAL_Extension;

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
	 * Current User Object.
	 *
	 * @var WP_User
	 */
	private $current_user = null;

	/**
	 * Hook Events
	 *
	 * Listening to events using hooks.
	 */
	public function hook_events() {
		add_action( 'clear_auth_cookie', array( $this, 'get_current_user' ), 10 );
		add_action( 'wp_login', array( $this, 'event_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'event_logout' ) );
		add_action( 'mainwp_added_new_site', array( $this, 'site_added' ), 10, 1 ); // Site added.
		add_action( 'mainwp_delete_site', array( $this, 'site_removed' ), 10, 1 ); // Site removed.
		add_action( 'mainwp_update_site', array( $this, 'site_edited' ), 10, 1 ); // Site edited.
		add_action( 'mainwp_site_synced', array( $this, 'site_synced' ), 10, 1 ); // Site synced.
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

		// Check if Advanced Uptime Monitor Extension is active.
		if ( is_plugin_active( 'advanced-uptime-monitor-extension/advanced-uptime-monitor-extension.php' ) ) {
			add_action( 'mainwp_aum_monitor_created', array( $this, 'aum_monitor_created' ), 10, 1 );
			add_action( 'mainwp_aum_monitor_deleted', array( $this, 'aum_monitor_deleted' ), 10, 1 );
			add_action( 'mainwp_aum_monitor_started', array( $this, 'aum_monitor_started' ), 10, 1 );
			add_action( 'mainwp_aum_monitor_paused', array( $this, 'aum_monitor_paused' ), 10, 1 );
			add_action( 'mainwp_aum_auto_add_sites', array( $this, 'aum_monitor_auto_add' ), 10, 1 );
		}
	}

	/**
	 * Triggered when a user accesses the admin area.
	 */
	public function event_admin_init() {
		$this->old_plugins = get_plugins();
	}

	/**
	 * Sets current user.
	 */
	public function get_current_user() {
		$this->current_user = wp_get_current_user();
	}

	/**
	 * Event Login.
	 *
	 * @param string  $user_login - WP username.
	 * @param WP_User $user       - WP_User object.
	 */
	public function event_login( $user_login, $user ) {
		if ( empty( $user ) ) {
			$user = get_user_by( 'login', $user_login );
		}

		$user_roles = MWPAL_Extension\mwpal_extension()->settings->get_current_user_roles( $user->roles );

		if ( MWPAL_Extension\mwpal_extension()->settings->is_login_super_admin( $user_login ) ) {
			$user_roles[] = 'superadmin';
		}

		MWPAL_Extension\mwpal_extension()->alerts->trigger(
			1000,
			array(
				'mainwp_dash'      => true,
				'Username'         => $user_login,
				'CurrentUserRoles' => $user_roles,
			)
		);
	}

	/**
	 * Event Logout.
	 */
	public function event_logout() {
		if ( 0 !== $this->current_user->ID ) {
			MWPAL_Extension\mwpal_extension()->alerts->Trigger(
				1001,
				array(
					'mainwp_dash'      => true,
					'CurrentUserID'    => $this->current_user->ID,
					'CurrentUserRoles' => MWPAL_Extension\mwpal_extension()->settings->get_current_user_roles( $this->current_user->roles ),
				)
			);
		}
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

		$new_site = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_site_by_id( $new_site_id );
		if ( $new_site != null ) {
			MWPAL_Extension\mwpal_extension()->alerts->trigger(
				7700,
				array(
					'friendly_name' => $new_site['name'],
					'site_url'      => $new_site['url'],
					'site_id'       => $new_site['id'],
					'mainwp_dash'   => true,
				)
			);
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
		if ( empty( $website ) ) {
			return;
		}

		if ( isset( $website->name ) ) {
			MWPAL_Extension\mwpal_extension()->alerts->trigger(
				7701,
				array(
					'friendly_name' => $website->name,
					'site_url'      => $website->url,
					'site_id'       => $website->id,
					'mainwp_dash'   => true,
				)
			);
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
		$mwp_sites = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_sites();

		// Search for the site data.
		$key = array_search( $site_id, array_column( $mwp_sites, 'id' ), false );

		if ( false !== $key && isset( $mwp_sites[ $key ] ) ) {
			MWPAL_Extension\mwpal_extension()->alerts->trigger(
				7702,
				array(
					'friendly_name' => $mwp_sites[ $key ]['name'],
					'site_url'      => $mwp_sites[ $key ]['url'],
					'site_id'       => $mwp_sites[ $key ]['id'],
					'mainwp_dash'   => true,
				)
			);
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
			MWPAL_Extension\mwpal_extension()->alerts->trigger(
				7703,
				array(
					'friendly_name' => $website->name,
					'site_url'      => $website->url,
					'site_id'       => $website->id,
					'mainwp_dash'   => true,
				)
			);
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

		if ( MWPAL_Extension\mwpal_extension()->settings->is_events_global_sync() ) {
			MWPAL_Extension\mwpal_extension()->alerts->retrieve_events_manually();
		}

		// Trigger global sync event.
		MWPAL_Extension\mwpal_extension()->alerts->trigger( 7704, array( 'mainwp_dash' => true ) );
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
			MWPAL_Extension\mwpal_extension()->alerts->trigger(
				7709,
				array(
					'mainwp_dash' => true,
					'extension'   => $slug,
					'action'      => $action,
					'option'      => 'Added' === $action ? 'to' : 'from',
					'EventType'   => $action,
				)
			);
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
	private function extension_log_event( $event, $extension ) {
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
			MWPAL_Extension\mwpal_extension()->alerts->trigger( $event, $event_data );
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
				$this->log_error(
					'Expected exactly one new plugin but found ' . count( $plugin ),
					array(
						'NewPlugin'  => $plugin,
						'OldPlugins' => $this->old_plugins,
						'NewPlugins' => $wp_plugins,
					)
				);
				return;
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

	/**
	 * Advanced Uptime Monitor Created.
	 *
	 * @param array $monitor_site – Monitor Site data array.
	 */
	public function aum_monitor_created( $monitor_site ) {
		// Get monitor site url.
		$site_url = isset( $monitor_site['url_address'] ) ? trailingslashit( $monitor_site['url_address'] ) : ( isset( $monitor_site['url_friendly_name'] ) ? trailingslashit( $monitor_site['url_friendly_name'] ) : false );

		// Report event.
		$this->report_aum_monitor_event( 7750, $site_url );
	}

	/**
	 * Advanced Uptime Monitor Deleted.
	 *
	 * @param object $monitor_site – Monitor site object.
	 */
	public function aum_monitor_deleted( $monitor_site ) {
		// Get monitor site url.
		$site_url = isset( $monitor_site->url_address ) ? trailingslashit( $monitor_site->url_address ) : ( isset( $monitor_site->url_friendly_name ) ? trailingslashit( $monitor_site->url_friendly_name ) : false );

		// Report event.
		$this->report_aum_monitor_event( 7751, $site_url );
	}

	/**
	 * Advanced Uptime Monitor Started.
	 *
	 * @param object $monitor_site – Monitor site object.
	 */
	public function aum_monitor_started( $monitor_site ) {
		// Get monitor site url.
		$site_url = isset( $monitor_site->url_address ) ? trailingslashit( $monitor_site->url_address ) : ( isset( $monitor_site->url_friendly_name ) ? trailingslashit( $monitor_site->url_friendly_name ) : false );

		// Report event.
		$this->report_aum_monitor_event( 7752, $site_url );
	}

	/**
	 * Advanced Uptime Monitor Paused.
	 *
	 * @param object $monitor_site – Monitor site object.
	 */
	public function aum_monitor_paused( $monitor_site ) {
		// Get monitor site url.
		$site_url = isset( $monitor_site->url_address ) ? trailingslashit( $monitor_site->url_address ) : ( isset( $monitor_site->url_friendly_name ) ? trailingslashit( $monitor_site->url_friendly_name ) : false );

		// Report event.
		$this->report_aum_monitor_event( 7753, $site_url );
	}

	/**
	 * Report Advanced Uptime Monitor Event.
	 *
	 * @param integer $event_id – Event ID.
	 * @param string  $site_url – Site URL.
	 */
	public function report_aum_monitor_event( $event_id, $site_url ) {
		if ( ! empty( $event_id ) && ! empty( $site_url ) ) {
			// Search for site in MainWP sites.
			$site = MWPAL_Extension\mwpal_extension()->settings->get_mwp_site_by( $site_url, 'url' );

			// If site is found then report it as MainWP child site.
			if ( false !== $site ) {
				MWPAL_Extension\mwpal_extension()->alerts->trigger(
					$event_id,
					array(
						'friendly_name' => $site['name'],
						'site_url'      => $site['url'],
						'site_id'       => $site['id'],
						'mainwp_dash'   => true,
					)
				);
			} else {
				// Else report as other site.
				MWPAL_Extension\mwpal_extension()->alerts->trigger(
					$event_id,
					array(
						'friendly_name' => $site_url,
						'site_url'      => $site_url,
						'mainwp_dash'   => true,
					)
				);
			}
		}
	}

	/**
	 * Report Advanced Uptime Monitor Auto Add Sites.
	 */
	public function aum_monitor_auto_add() {
		MWPAL_Extension\mwpal_extension()->alerts->trigger( 7754, array( 'mainwp_dash' => true ) );
	}
}
