<?php
/**
 * Class: Alert Manager
 *
 * Alert manager class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;
use \WSAL\MainWPExtension\Alert as Alert;
use \WSAL\MainWPExtension\Loggers\AbstractLogger as AbstractLogger;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alert Manager Class
 *
 * Alert manager class of the extension.
 */
final class AlertManager {

	/**
	 * Activity Log Instance.
	 *
	 * @var \WSAL\MainWPExtension\Activity_Log
	 */
	protected $activity_log;

	/**
	 * Extension Alerts.
	 *
	 * @var array
	 */
	protected $alerts = array();

	/**
	 * Extension Loggers.
	 *
	 * @var array
	 */
	protected $loggers = array();

	/**
	 * Triggered Alerts
	 *
	 * Contains an array of alerts that have been triggered for this request.
	 *
	 * @var array
	 */
	protected $triggered_types = array();

	/**
	 * Constructor: AlertManager instance.
	 *
	 * @param Activity_Log $activity_log – Activity log instance.
	 */
	public function __construct( Activity_Log $activity_log ) {
		$this->activity_log = $activity_log;

		foreach ( glob( MWPAL_BASE_DIR . 'includes/loggers/*.php' ) as $file ) {
			$this->add_from_file( $file );
		}
	}

	/**
	 * Add new logger from file inside autoloader path.
	 *
	 * @param string $file Path to file.
	 */
	public function add_from_file( $file ) {
		$this->add_from_class( $this->get_class_name( $file ) );
	}

	/**
	 * Add new logger given class name.
	 *
	 * @param string $class Class name.
	 */
	public function add_from_class( $class ) {
		$this->add_instance( new $class( $this->activity_log ) );
	}

	/**
	 * Add newly created logger to list.
	 *
	 * @param AbstractLogger[] $logger - The new logger.
	 */
	public function add_instance( AbstractLogger $logger ) {
		$this->loggers[] = $logger;
	}

	/**
	 * Get class name for logger classes.
	 *
	 * @param string $file – File name.
	 * @return string
	 */
	private function get_class_name( $file ) {
		if ( empty( $file ) ) {
			return false;
		}

		// Replace file path, `class-`, and `.php` in the file string.
		$file = str_replace( array( MWPAL_BASE_DIR . 'includes/loggers/', 'class-', '.php' ), '', $file );
		$file = str_replace( 'logger', 'Logger', $file );
		$file = ucfirst( $file );
		$file = '\WSAL\MainWPExtension\Loggers\\' . $file;
		return $file;
	}

	/**
	 * Register a whole group of items.
	 *
	 * @param array $groups - An array with group name as the index and an array of group items as the value.
	 * Item values is an array of [type, code, description, message] respectively.
	 */
	public function RegisterGroup( $groups ) {
		foreach ( $groups as $name => $group ) {
			foreach ( $group as $subname => $subgroup ) {
				foreach ( $subgroup as $item ) {
					list($type, $code, $desc, $mesg) = $item;
					$this->Register( array( $type, $code, $name, $subname, $desc, $mesg ) );
				}
			}
		}
	}

	/**
	 * Register an alert type.
	 *
	 * @param array $info - Array of [type, code, category, description, message] respectively.
	 * @throws \Exception - Error if alert is already registered.
	 */
	public function Register( $info ) {
		if ( 1 === func_num_args() ) {
			// Handle single item.
			list($type, $code, $catg, $subcatg, $desc, $mesg) = $info;
			if ( isset( $this->alerts[ $type ] ) ) {
				add_action( 'admin_notices', array( $this, 'duplicate_event_notice' ) );
				/* Translators: Event ID */
				throw new \Exception( sprintf( esc_html__( 'Event %s already registered with Activity Log MainWP Extension.', 'mwp-al-ext' ), $type ) );
			}
			$this->alerts[ $type ] = new Alert( $type, $code, $catg, $subcatg, $desc, $mesg );
		} else {
			// Handle multiple items.
			foreach ( func_get_args() as $arg ) {
				$this->Register( $arg );
			}
		}
	}

	/**
	 * Duplicate Event Notice
	 *
	 * WP admin notice for duplicate event.
	 */
	public function duplicate_event_notice() {
		$class   = 'notice notice-error';
		$message = __( 'You have custom events that are using the same ID or IDs which are already registered in the plugin, so they have been disabled.', 'mwp-al-ext' );
		printf(
			/* Translators: 1.CSS classes, 2. Notice, 3. Contact us link */
			'<div class="%1$s"><p>%2$s %3$s ' . esc_html__( '%4$s to help you solve this issue.', 'mwp-al-ext' ) . '</p></div>',
			esc_attr( $class ),
			'<span style="color:#dc3232; font-weight:bold;">' . esc_html__( 'ERROR:', 'mwp-al-ext' ) . '</span>',
			esc_html( $message ),
			'<a href="https://www.wpsecurityauditlog.com/contact" target="_blank">' . esc_html__( 'Contact us', 'mwp-al-ext' ) . '</a>'
		);
	}

	/**
	 * Method: Returns an array of loaded loggers.
	 *
	 * @return AbstractLogger[]
	 */
	public function get_loggers() {
		return $this->loggers;
	}

	/**
	 * Return alert given alert type.
	 *
	 * @param integer $type    - Alert type.
	 * @param mixed   $default - Returned if alert is not found.
	 * @return \WSAL\MainWPExtension\Alert
	 */
	public function GetAlert( $type, $default = null ) {
		foreach ( $this->alerts as $alert ) {
			if ( $alert->type == $type ) {
				return $alert;
			}
		}
		return $default;
	}

	/**
	 * Returns all supported alerts.
	 *
	 * @return \WSAL\MainWPExtension\Alert[]
	 */
	public function GetAlerts() {
		return $this->alerts;
	}

	/**
	 * Method: Returns array of alerts by category.
	 *
	 * @param string $category - Alerts category.
	 * @return \WSAL\MainWPExtension\Alert[]
	 */
	public function get_alerts_by_category( $category ) {
		// Categorized alerts array.
		$alerts = array();
		foreach ( $this->alerts as $alert ) {
			if ( $category === $alert->catg ) {
				$alerts[ $alert->type ] = $alert;
			}
		}
		return $alerts;
	}

	/**
	 * Method: Returns array of alerts by sub-category.
	 *
	 * @param string $sub_category - Alerts sub-category.
	 * @return \WSAL\MainWPExtension\Alert[]
	 */
	public function get_alerts_by_sub_category( $sub_category ) {
		// Sub-categorized alerts array.
		$alerts = array();
		foreach ( $this->alerts as $alert ) {
			if ( $sub_category === $alert->subcatg ) {
				$alerts[ $alert->type ] = $alert;
			}
		}
		return $alerts;
	}

	/**
	 * Returns all supported alerts.
	 *
	 * @param bool $sorted – Sort the alerts array or not.
	 * @return array
	 */
	public function get_categorized_alerts( $sorted = true ) {
		$result = array();
		foreach ( $this->alerts as $alert ) {
			if ( ! isset( $result[ $alert->catg ] ) ) {
				$result[ $alert->catg ] = array();
			}
			if ( ! isset( $result[ $alert->catg ][ $alert->subcatg ] ) ) {
				$result[ $alert->catg ][ $alert->subcatg ] = array();
			}
			$result[ $alert->catg ][ $alert->subcatg ][] = $alert;
		}

		if ( $sorted ) {
			ksort( $result );
		}
		return $result;
	}

	/**
	 * Log events in the database.
	 *
	 * @param array   $events  – Activity Log Events.
	 * @param integer $site_id – Site ID according to MainWP.
	 * @return void
	 */
	public function log_events( $events, $site_id ) {
		if ( empty( $events ) ) {
			return;
		}

		if ( is_array( $events ) ) {
			foreach ( $events as $event_id => $event ) {
				// Get loggers.
				$loggers = $this->get_loggers();

				// Get meta data of event.
				$meta_data = $event->meta_data;
				$user_data = isset( $meta_data['UserData'] ) ? $meta_data['UserData'] : false;

				// Username.
				if ( isset( $user_data->username ) ) {
					$meta_data['Username'] = $user_data->username;
				}

				// Log the events in DB.
				foreach ( $loggers as $logger ) {
					$logger->log( $event->alert_id, $meta_data, $event->created_on, $site_id );
				}
			}
		}
	}

	/**
	 * Trigger an event.
	 *
	 * @param integer $type - Event type.
	 * @param array   $data - Event data.
	 */
	public function trigger( $type, $data = array() ) {
		// Get username.
		if ( ! isset( $data['Username'] ) || empty( $data['Username'] ) ) {
			$data['Username'] = wp_get_current_user()->user_login;
		}

		// Get current user roles.
		$roles = $this->activity_log->settings->get_current_user_roles();
		if ( empty( $roles ) && ! empty( $data['CurrentUserRoles'] ) ) {
			$roles = $data['CurrentUserRoles'];
		}

		// Trigger event.
		$this->commit_event( $type, $data, null );
	}

	/**
	 * Method: Commit an alert now.
	 *
	 * @param int   $type  - Alert type.
	 * @param array $data  - Data of the alert.
	 * @param array $cond  - Condition for the alert.
	 * @param bool  $retry - Retry.
	 * @internal
	 *
	 * @throws Exception - Error if alert is not registered.
	 */
	protected function commit_event( $type, $data, $cond, $retry = true ) {
		if ( ! $cond || ! ! call_user_func( $cond, $this ) ) {
			if ( isset( $this->alerts[ $type ] ) ) {
				// Ok, convert alert to a log entry.
				$this->triggered_types[] = $type;
				$this->log( $type, $data );
			} elseif ( $retry ) {
				// This is the last attempt at loading alerts from default file.
				$this->activity_log->load_events();
				return $this->commit_event( $type, $data, $cond, false );
			} else {
				// In general this shouldn't happen, but it could, so we handle it here.
				/* translators: Event ID */
				throw new Exception( sprintf( esc_html__( 'Event with code %d has not be registered.', 'mwp-al-ext' ), $type ) );
			}
		}
	}

	/**
	 * Log Alert
	 *
	 * Converts an Alert into a Log entry (by invoking loggers).
	 * You should not call this method directly.
	 *
	 * @param integer $type - Alert type.
	 * @param array   $data - Misc alert data.
	 */
	protected function log( $type, $data = array() ) {
		// Client IP.
		if ( ! isset( $data['ClientIP'] ) ) {
			$client_ip = $this->activity_log->settings->get_main_client_ip();
			if ( ! empty( $client_ip ) ) {
				$data['ClientIP'] = $client_ip;
			}
		}

		// User agent.
		if ( ! isset( $data['UserAgent'] ) ) {
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				$data['UserAgent'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			}
		}

		// Username.
		if ( ! isset( $data['Username'] ) && ! isset( $data['CurrentUserID'] ) ) {
			if ( function_exists( 'get_current_user_id' ) ) {
				$data['CurrentUserID'] = get_current_user_id();
			}
		}

		// Current user roles.
		if ( ! isset( $data['CurrentUserRoles'] ) && function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			$current_user_roles = $this->activity_log->settings->get_current_user_roles();
			if ( ! empty( $current_user_roles ) ) {
				$data['CurrentUserRoles'] = $current_user_roles;
			}
		}

		// Log event.
		foreach ( $this->loggers as $logger ) {
			$logger->log( $type, $data, null, 0 );
		}
	}
}
