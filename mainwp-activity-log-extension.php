<?php
/**
 * Plugin Name: MainWP Activity Log Extension
 * Plugin URI: http://www.wpsecurityauditlog.com/
 * Description: An add-on for MainWP to be able to view the activity logs of all child sites from the central MainWP dashboard.
 * Author: WP White Security
 * Version: 0.1.0
 * Text Domain: mwp-al-ext
 * Author URI: http://www.wpsecurityauditlog.com/
 * License: GPL2
 *
 * @package mwp-al-ext
 */

/*
	MainWP Activity Log Extension
	Copyright(c) 2018  Robert Abela  (email : robert@wpwhitesecurity.com)

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

// use \WSAL\MainWPExtension\Views\View as SingleView;

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
	public $version = '0.1.0';

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
		// Define plugin constants.
		$this->define_constants();

		// Include autoloader.
		require_once MWPAL_BASE_DIR . 'includes/vendors/autoload.php';
		\AaronHolbrook\Autoload\autoload( MWPAL_BASE_DIR . 'includes' );

		// Initiate the view.
		$this->extension_view = new \WSAL\MainWPExtension\Views\View( $this );
		// $this->log( \WSAL\MainWPExtension\View::my_function() );

		register_activation_hook( __FILE__, array( $this, 'activate_extension' ) );

		// Set child file.
		$this->child_file = __FILE__;
		add_filter( 'mainwp-getextensions', array( &$this, 'get_this_extension' ) );

		// This filter will return true if the main plugin is activated.
		$this->mainwp_main_activated = apply_filters( 'mainwp-activated-check', false );

		if ( false !== $this->mainwp_main_activated ) {
			$this->activate_this_plugin();
		} else {
			// Because sometimes our main plugin is activated after the extension plugin is activated we also have a second step,
			// listening to the 'mainwp-activated' action. This action is triggered by MainWP after initialisation.
			add_action( 'mainwp-activated', array( &$this, 'activate_this_plugin' ) );
		}
		add_action( 'admin_init', array( &$this, 'redirect_to_extensions' ) );
		add_action( 'admin_notices', array( &$this, 'mainwp_error_notice' ) );
	}

	/**
	 * Save option that extension has been activated.
	 */
	public function activate_extension() {
		update_option( MWPAL_OPT_PREFIX . 'activity-extension-activated', 'yes' );
	}

	/**
	 * Checks if extension is activated or not.
	 *
	 * @param string $default - Default value to return if option doesn't exist.
	 * @return string
	 */
	public function is_extension_activated( $default = 'no' ) {
		return get_option( MWPAL_OPT_PREFIX . 'activity-extension-activated', $default );
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
			define( 'MWPAL_EXTENSION_NAME', 'mainwp-activity-log-extension' );
		}

		// Plugin Min PHP Version.
		if ( ! defined( 'MWPAL_MIN_PHP_VERSION' ) ) {
			define( 'MWPAL_MIN_PHP_VERSION', '5.4.0' );
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
	public function redirect_to_extensions() {
		if ( 'yes' === $this->is_extension_activated() ) {
			delete_option( MWPAL_OPT_PREFIX . 'activity-extension-activated' );
			wp_safe_redirect( add_query_arg( 'page', 'Extensions', admin_url( 'admin.php' ) ) );
			return;
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
			'api'      => MWPAL_EXTENSION_NAME,
			'mainwp'   => false,
			'callback' => array( &$this, 'display_extension' ),
		);
		return $plugins;
	}

	/**
	 * Extension Display on MainWP Dashboard.
	 */
	public function display_extension() {
		$this->extension_view->render_view();
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
			echo '<div class="error"><p>MainWP Hello World! Extension ' . esc_html__( 'requires ', 'mwp-al-ext' ) . '<a href="http://mainwp.com/" target="_blank">MainWP</a>' . esc_html__( ' Plugin to be activated in order to work. Please install and activate', 'mwp-al-ext' ) . '<a href="http://mainwp.com/" target="_blank">MainWP</a> ' . esc_html__( 'first.', 'mwp-al-ext' ) . '</p></div>';
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
}

/**
 * Return the one and only instance of this plugin.
 *
 * @return \WSAL\MainWPExtension\Activity_Log
 */
function mwpal_extension_load() {
	return \WSAL\MainWPExtension\Activity_Log::get_instance();
}
mwpal_extension_load();
