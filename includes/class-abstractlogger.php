<?php
/**
 * Class: Abstract Logger
 *
 * Abstract logger class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Loggers;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class used in the Logger.
 *
 * @package mwp-al-ext
 */
abstract class AbstractLogger {

	/**
	 * Instance of Activity_Log.
	 *
	 * @var Activity_Log
	 */
	protected $activity_log;

	/**
	 * Method: Constructor.
	 *
	 * @param Activity_Log $activity_log - Instance of Activity_Log.
	 */
	public function __construct( Activity_Log $activity_log ) {
		$this->activity_log = $activity_log;
	}

	/**
	 * Log alert abstract.
	 *
	 * @param integer $type                - Alert code.
	 * @param array   $data                - Metadata.
	 * @param integer $date (Optional)     - Created on.
	 * @param integer $siteid (Optional)   - Site id.
	 * @param bool    $migrated (Optional) - Is migrated.
	 */
	abstract public function log( $type, $data = array(), $date = null, $siteid = null, $migrated = false );
}
