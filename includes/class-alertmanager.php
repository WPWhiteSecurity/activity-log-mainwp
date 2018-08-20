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
	 * @throws Exception - Error if alert is already registered.
	 */
	public function Register( $info ) {
		if ( 1 === func_num_args() ) {
			// Handle single item.
			list($type, $code, $catg, $subcatg, $desc, $mesg) = $info;
			if ( isset( $this->alerts[ $type ] ) ) {
				throw new Exception( "Alert $type already registered with Alert Manager." );
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
	 * Method: Returns an array of loaded loggers.
	 *
	 * @return AbstractLogger[]
	 */
	public function GetLoggers() {
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
	public function GetCategorizedAlerts( $sorted = true ) {
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
				$loggers = $this->GetLoggers();

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
}
