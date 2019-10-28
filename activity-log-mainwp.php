<?php
/**
 * Plugin Name: Activity Log for MainWP
 * Plugin URI: https://www.wpsecurityauditlog.com/activity-log-mainwp-extension/
 * Description: This extension for MainWP enables you to view the activity logs of all child sites in one central location, the MainWP dashboard.
 * Author: WP White Security
 * Version: 1.2
 * Text Domain: mwp-al-ext
 * Author URI: http://www.wpwhitesecurity.com/
 * License: GPL2
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

/*
	Activity Log for MainWP
	Copyright(c) 2019  WP White Security  (email : info@wpwhitesecurity.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace WSAL\MainWPExtension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MainWP Activity Log Extension
 *
 * Entry class for activity log extension.
 */
class Activity_Log {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.2';

	/**
	 * Single Static Instance of the plugin.
	 *
	 * @var Activity_Log
	 */
	public static $instance = null;

	/**
	 * Is MainWP Activated?
	 *
	 * @var boolean
	 */
	protected $mainwp_main_activated = false;

	/**
	 * Is MainWP Child plugin enabled?
	 *
	 * @var boolean
	 */
	protected $child_enabled = false;

	/**
	 * Child Key.
	 *
	 * @var boolean
	 */
	protected $child_key = false;

	/**
	 * Child File.
	 *
	 * @var string
	 */
	protected $child_file;

	/**
	 * Extension View.
	 *
	 * @var \WSAL\MainWPExtension\Views\View
	 */
	public $extension_view;

	/**
	 * Extension Settings.
	 *
	 * @var \WSAL\MainWPExtension\Settings
	 */
	public $settings;

	/**
	 * Alerts Manager.
	 *
	 * @var \WSAL\MainWPExtension\AlertManager
	 */
	public $alerts;

	/**
	 * Constants Manager.
	 *
	 * @var \WSAL\MainWPExtension\ConstantManager
	 */
	public $constants;

	/**
	 * MainWP Sensor.
	 *
	 * @var \WSAL\MainWPExtension\Sensors\Sensor_MainWP
	 */
	public $sensor_mainwp;

	/**
	 * Clean up hooks.
	 *
	 * @since 1.0.4
	 *
	 * @var array
	 */
	public $cleanup_hooks = array();

	/**
	 * Returns the singular instance of the plugin.
	 *
	 * @return Activity_Log
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->child_file = __FILE__; // Set child file.
		$this->define_constants(); // Define plugin constants.
		$this->includes(); // Include files.
		$this->init_hooks(); // Initialize hooks.
	}

	/**
	 * Include Files.
	 *
	 * @since 1.1
	 */
	public function includes() {
		require_once MWPAL_BASE_DIR . 'includes/helpers/class-datahelper.php';
		require_once MWPAL_BASE_DIR . 'includes/models/class-activerecord.php';
		require_once MWPAL_BASE_DIR . 'includes/models/class-query.php';
		require_once MWPAL_BASE_DIR . 'includes/models/class-occurrencequery.php';
		require_once MWPAL_BASE_DIR . 'includes/vendors/autoload.php';

		// Autoload files.
		\WSAL\MainWPExtension\Autoload\mwpal_autoload( MWPAL_BASE_DIR . 'includes' );
	}

	/**
	 * Initialize Plugin Hooks.
	 *
	 * @since 1.1
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'mwpal_init' ) ); // Start listening to events.
		register_activation_hook( __FILE__, array( $this, 'install_extension' ) ); // Installation routine.
		add_action( 'mwp_events_cleanup', array( $this, 'events_cleanup' ) ); // Schedule hook for refreshing events.
		add_filter( 'mainwp-getextensions', array( &$this, 'get_this_extension' ) );
		add_action( 'admin_init', array( &$this, 'redirect_on_activate' ) );
		add_filter( 'plugin_action_links_' . MWPAL_BASE_NAME, array( $this, 'add_plugin_page_links' ), 20, 1 );
		add_action( 'plugins_loaded', array( $this, 'load_mwpal_extension' ) );
		add_action( 'admin_notices', array( &$this, 'mainwp_error_notice' ) );

		// This filter will return true if the main plugin is activated.
		$this->mainwp_main_activated = apply_filters( 'mainwp-activated-check', false );

		if ( false !== $this->mainwp_main_activated ) {
			$this->activate_this_plugin();
		} else {
			// Because sometimes our main plugin is activated after the extension plugin is activated we also have a second step,
			// listening to the 'mainwp-activated' action. This action is triggered by MainWP after initialisation.
			add_action( 'mainwp-activated', array( &$this, 'activate_this_plugin' ) );
		}

		// Initialize freemius.
		$this->init_freemius();
	}

	/**
	 * Initialize Freemius and its hooks.
	 */
	public function init_freemius() {
		if ( is_admin() && file_exists( trailingslashit( MWPAL_BASE_DIR ) . 'sdk/freemius-init.php' ) ) {
			require_once trailingslashit( MWPAL_BASE_DIR ) . 'sdk/freemius-init.php';
		}
	}

	/**
	 * Start listening to events.
	 */
	public function mwpal_init() {
		// Initalize the classes.
		$this->settings       = new \WSAL\MainWPExtension\Settings();
		$this->constants      = new \WSAL\MainWPExtension\ConstantManager( $this );
		$this->alerts         = new \WSAL\MainWPExtension\AlertManager();
		$this->sensor_mainwp  = new \WSAL\MainWPExtension\Sensors\Sensor_MainWP( $this );
		$this->extension_view = new \WSAL\MainWPExtension\Views\View();

		if ( false === $this->settings->get_option( 'setup-complete' ) ) {
			new \WSAL\MainWPExtension\Views\Setup_Wizard( $this );
		}

		// Hook extension events.
		$this->sensor_mainwp->hook_events();
	}

	/**
	 * Load extension on `plugins_loaded` action.
	 */
	public function load_mwpal_extension() {
		do_action( 'mwpal_init', $this );
	}

	/**
	 * DB connection.
	 *
	 * @param mixed $config - DB configuration.
	 * @param bool  $reset  - True if reset.
	 * @return \WSAL\MainWPExtension\Connector\ConnectorInterface
	 */
	public static function get_connector( $config = null, $reset = false ) {
		return \WSAL\MainWPExtension\Connector\ConnectorFactory::getConnector( $config, $reset );
	}

	/**
	 * Save option that extension has been activated.
	 */
	public function install_extension() {
		if ( ! is_plugin_active( 'mainwp/mainwp.php' ) ) {
			?>
			<html>
				<head>
					<style>
						.warn-icon-tri{top:5px;left:5px;position:absolute;border-left:16px solid #FFF;border-right:16px solid #FFF;border-bottom:28px solid #C33;height:3px;width:4px}.warn-icon-chr{top:8px;left:18px;position:absolute;color:#FFF;font:26px Georgia}.warn-icon-cir{top:2px;left:0;position:absolute;overflow:hidden;border:6px solid #FFF;border-radius:32px;width:34px;height:34px}.warn-wrap{position:relative;color:#A00;font:14px Arial;padding:6px 48px}.warn-wrap a,.warn-wrap a:hover{color:#F56}
					</style>
				</head>
				<body>
					<div class="warn-wrap">
						<div class="warn-icon-tri"></div><div class="warn-icon-chr">!</div><div class="warn-icon-cir"></div>
						<?php
						echo sprintf(
							/* Translators: %s: Getting started guide hyperlink. */
							esc_html__( 'This extension should be installed on the MainWP dashboard site. On the child sites please install the WP Security Audit Log plugin. Refer to the %s for more information.', 'mwp-al-ext' ),
							'<a href="https://www.wpsecurityauditlog.com/support-documentation/gettting-started-activity-log-mainwp-extension/" target="_blank">' . esc_html__( 'Getting Started Guide', 'mwp-al-ext' ) . '</a>'
						);
						?>
					</div>
				</body>
			</html>
			<?php
			die( 1 );
		}

		if ( empty( $this->settings ) ) {
			$this->settings = new \WSAL\MainWPExtension\Settings();
		}

		// Ensure that the system is installed and schema is correct.
		self::get_connector()->installAll();

		// Option to redirect to extensions page.
		$this->settings->set_extension_activated( 'yes' );

		$new_version = $this->mwp_current_plugin_version();
		$old_version = $this->mwp_old_plugin_version();

		// If compare old version and new version
		if ( $old_version !== $new_version ) {
			mwpal_extension()->settings->update_option( 'version', $new_version );
			delete_transient( 'mwpal-is-advert-dismissed' );
		}

		// Install refresh hook (remove older one if it exists).
		wp_clear_scheduled_hook( 'mwp_events_cleanup' );
		wp_schedule_event( current_time( 'timestamp' ) + 600, 'hourly', 'mwp_events_cleanup' );
	}

	/**
	 * The current plugin version (according to plugin file metadata).
	 *
	 * @return string
	 */
	public function mwp_current_plugin_version() {
		$version = get_plugin_data( __FILE__, false, false );
		return isset( $version['Version'] ) ? $version['Version'] : '0.0.0';
	}

	/**
	 * The plugin version as stored in DB (will be the old version during an update/install).
	 *
	 * @return string
	 */
	public function mwp_old_plugin_version() {
		return mwpal_extension()->settings->get_option( 'version', '0.0.0' );
	}
	
	/**
	 * Define constants.
	 */
	public function define_constants() {
		// Plugin version.
		if ( ! defined( 'MWPAL_VERSION' ) ) {
			define( 'MWPAL_VERSION', $this->version );
		}

		// Plugin Name.
		if ( ! defined( 'MWPAL_BASE_NAME' ) ) {
			define( 'MWPAL_BASE_NAME', plugin_basename( __FILE__ ) );
		}

		// Plugin Directory URL.
		if ( ! defined( 'MWPAL_BASE_URL' ) ) {
			define( 'MWPAL_BASE_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Directory Path.
		if ( ! defined( 'MWPAL_BASE_DIR' ) ) {
			define( 'MWPAL_BASE_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Extension Name.
		if ( ! defined( 'MWPAL_EXTENSION_NAME' ) ) {
			$filename = str_replace( trailingslashit( WP_PLUGIN_DIR ), '', MWPAL_BASE_DIR );
			$filename = untrailingslashit( $filename );
			$filename = str_replace( '-', ' ', $filename );
			$filename = ucwords( $filename );
			$filename = str_replace( ' ', '-', $filename );
			define( 'MWPAL_EXTENSION_NAME', 'Extensions-' . $filename );
		}

		// Plugin Min PHP Version.
		if ( ! defined( 'MWPAL_MIN_PHP_VERSION' ) ) {
			define( 'MWPAL_MIN_PHP_VERSION', '5.5.0' );
		}

		// Plugin Options Prefix.
		if ( ! defined( 'MWPAL_OPT_PREFIX' ) ) {
			define( 'MWPAL_OPT_PREFIX', 'mwpal-' );
		}
	}

	/**
	 * Redirect to MainWP Extensions Page.
	 *
	 * @return void
	 */
	public function redirect_on_activate() {
		$redirect_url = false;
		if ( 'yes' === $this->settings->is_extension_activated() ) {
			$this->settings->delete_option( 'activity-extension-activated' );

			if ( ! $this->settings->get_option( 'setup-complete' ) ) {
				$redirect_url = add_query_arg( 'page', 'activity-log-mainwp-setup', admin_url( 'index.php' ) );
			} else {
				$redirect_url = add_query_arg( 'page', MWPAL_EXTENSION_NAME, admin_url( 'admin.php' ) );
			}
		}

		if ( $redirect_url ) {
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Add extension to MainWP.
	 *
	 * @param array $plugins – Array of plugins.
	 * @return array
	 */
	public function get_this_extension( $plugins ) {
		$plugins[] = array(
			'plugin'   => __FILE__,
			'api'      => basename( __FILE__, '.php' ),
			'mainwp'   => false,
			'callback' => array( &$this, 'display_extension' ),
			'icon'     => trailingslashit( MWPAL_BASE_URL ) . 'assets/img/activity-log-mainwp-500x500.jpg',
		);
		return $plugins;
	}

	/**
	 * Extension Display on MainWP Dashboard.
	 */
	public function display_extension() {
		$this->extension_view->render_page();
	}

	/**
	 * The function "activate_this_plugin" is called when the main is initialized.
	 */
	public function activate_this_plugin() {
		// Checking if the MainWP plugin is enabled. This filter will return true if the main plugin is activated.
		$this->mainwp_main_activated = apply_filters( 'mainwp-activated-check', $this->mainwp_main_activated );

		// The 'mainwp-extension-enabled-check' hook. If the plugin is not enabled this will return false,
		// if the plugin is enabled, an array will be returned containing a key.
		// This key is used for some data requests to our main.
		$this->child_enabled = apply_filters( 'mainwp-extension-enabled-check', __FILE__ );
		$this->child_key     = $this->child_enabled['key'];
	}

	/**
	 * MainWP Plugin Error Notice.
	 */
	public function mainwp_error_notice() {
		global $current_screen;
		if ( 'plugins' === $current_screen->parent_base && false === $this->mainwp_main_activated ) {
			/* Translators: MainWP website hyperlink */
			echo '<div class="error"><p>' . sprintf( esc_html__( 'Activity Log for MainWP Extension requires %1$s plugin to be activated in order to work. Please install and activate %2$s first.', 'mwp-al-ext' ), '<a href="https://mainwp.com/" target="_blank">MainWP</a>', '<a href="https://mainwp.com/" target="_blank">MainWP</a>' ) . '</p></div>';
		}
	}

	/**
	 * Check if extension is enabled.
	 *
	 * @return mix
	 */
	public function is_child_enabled() {
		return $this->child_enabled;
	}

	/**
	 * Get Child Key.
	 *
	 * @return string
	 */
	public function get_child_key() {
		return $this->child_key;
	}

	/**
	 * Get Child File.
	 *
	 * @return string
	 */
	public function get_child_file() {
		return $this->child_file;
	}

	/**
	 * Load events from external file: `default-events.php`.
	 */
	public function load_events() {
		require_once 'default-events.php';
	}

	/**
	 * Error Logger
	 *
	 * Logs given input into debug.log file in debug mode.
	 *
	 * @param mix $message - Error message.
	 */
	public function log( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

	/**
	 * Clean Up Events
	 *
	 * Clean up events of a site if the latest event is more
	 * than three hours late.
	 */
	public function events_cleanup() {
		// Get MainWP sites.
		$child_sites = $this->settings->get_wsal_child_sites();
		$server_ip   = $this->settings->get_server_ip(); // Get server IP.

		if ( ! empty( $child_sites ) && is_array( $child_sites ) ) {
			$sites_data         = array();
			$trigger_retrieving = true; // Event 7711.
			$trigger_ready      = true; // Event 7712.

			foreach ( $child_sites as $site_id => $site ) {
				$event    = $this->get_latest_event_by_siteid( $site_id );
				$hrs_diff = 0;

				if ( $event ) {
					$hrs_diff = $this->settings->get_hours_since_last_alert( $event->created_on );
				}

				// If the hours difference is less than the selected frequency then skip this site.
				if ( 0 !== $hrs_diff && $hrs_diff < $this->settings->get_events_frequency() ) {
					continue;
				}

				// Get latest event from child site.
				$live_event = $this->get_live_event_by_siteid( $site_id );

				// If the latest event on the dashboard matches the timestamp of the latest event on child site, then skip.
				if ( $live_event && $event && $event->created_on === $live_event->created_on ) {
					continue;
				}

				// Delete events by site id.
				$this->alerts->delete_site_events( $site_id );

				// Fetch events by site id.
				$sites_data[ $site_id ] = $this->alerts->fetch_site_events( $site_id, $trigger_retrieving );

				// Set $trigger_retrieving to false to avoid logging 7711 multiple times.
				$trigger_retrieving = false;

				if ( $trigger_ready && isset( $sites_data[ $site_id ]->events ) ) {
					// Extension is ready after retrieving.
					$this->alerts->trigger(
						7712,
						array(
							'mainwp_dash' => true,
							'Username'    => 'System',
							'ClientIP'    => ! empty( $server_ip ) ? $server_ip : false,
						)
					);
					$trigger_ready = false;
				}
			}

			// Set child site events.
			$this->alerts->set_site_events( $sites_data );
		}

		foreach ( $this->cleanup_hooks as $hook ) {
			call_user_func( $hook );
		}
	}

	/**
	 * Get the latest event by site id.
	 *
	 * @param integer $site_id — Site ID.
	 * @return array
	 */
	private function get_latest_event_by_siteid( $site_id = 0 ) {
		// Return if site id is empty.
		if ( empty( $site_id ) ) {
			return false;
		}

		// Query for latest event.
		$event_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();
		$event_query->addCondition( 'site_id = %s ', $site_id ); // Set site id.
		$event_query->addOrderBy( 'created_on', true );
		$event_query->setLimit( 1 );
		$event = $event_query->getAdapter()->Execute( $event_query );

		if ( isset( $event[0] ) ) {
			// Event is found.
			return $event[0];
		} else {
			// Check the last checked timestamp against this site id.
			$last_checked = $this->settings->get_last_checked_by_siteid( $site_id );

			if ( ! $last_checked ) {
				$next_update = time() + ( $this->settings->get_events_frequency() * 60 * 60 ) + 1;
				$this->settings->set_last_checked_by_siteid( $site_id, $next_update );
			} else {
				$last_event             = new \stdClass();
				$last_event->created_on = $last_checked;
				return $last_event;
			}
		}
		return false;
	}

	/**
	 * Get live event by site id (from child site).
	 *
	 * @param integer $site_id — Site ID.
	 * @return stdClass
	 */
	private function get_live_event_by_siteid( $site_id = 0 ) {
		// Return if site id is empty.
		if ( empty( $site_id ) ) {
			return false;
		}

		// Post data for child sites.
		$post_data = array(
			'action' => 'latest_event',
		);

		// Call to child sites to fetch WSAL events.
		$latest_event = apply_filters(
			'mainwp_fetchurlauthed',
			$this->get_child_file(),
			$this->get_child_key(),
			$site_id,
			'extra_excution',
			$post_data
		);
		return $latest_event;
	}

	/**
	 * Add Plugin Shortcut Links.
	 *
	 * @since 1.0.3
	 *
	 * @param array $old_links - Old links.
	 * @return array
	 */
	public function add_plugin_page_links( $old_links ) {
		// Extension view URL.
		$extension_url = add_query_arg( 'page', MWPAL_EXTENSION_NAME, admin_url( 'admin.php' ) );

		// New plugin links.
		$new_links = array(
			'mwpal-view'     => '<a href="' . add_query_arg( 'tab', 'activity-log', $extension_url ) . '">' . __( 'View Activity Log', 'wp-security-audit-log' ) . '</a>',
			'mwpal-settings' => '<a href="' . add_query_arg( 'tab', 'settings', $extension_url ) . '">' . __( 'Settings', 'wp-security-audit-log' ) . '</a>',
			'deactivate'     => $old_links['deactivate'],
		);
		return $new_links;
	}

	/**
	 * Add callback to be called when a cleanup operation is required.
	 *
	 * @param callable $hook - Hook name.
	 */
	public function add_cleanup_hook( $hook ) {
		$this->cleanup_hooks[] = $hook;
	}

	/**
	 * Checks if MainWP dashboard plugin is active or not.
	 *
	 * @return boolean|string
	 */
	public function is_mainwp_active() {
		return $this->mainwp_main_activated;
	}
}

/**
 * Return the one and only instance of this plugin.
 *
 * @return \WSAL\MainWPExtension\Activity_Log
 */
function mwpal_extension() {
	return \WSAL\MainWPExtension\Activity_Log::get_instance();
}

// Initiate the plugin.
$mwpal_extension = mwpal_extension();

// Include events for extension.
$mwpal_extension->load_events();
