<?php
/**
 * Class: Logger
 *
 * Logger class for wsal.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Loggers;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;
use \WSAL\MainWPExtension\Loggers\AbstractLogger as AbstractLogger;
use \WSAL\MainWPExtension\Models\Occurrence as Occurrence;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loggers Class.
 *
 * This class store the logs in the Database and adds the promo
 * alerts, there is also the function to clean up alerts.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */
class Database extends AbstractLogger {

	/**
	 * Method: Constructor.
	 *
	 * @param Activity_Log $activity_log - Instance of Activity_Log.
	 */
	public function __construct( Activity_Log $activity_log ) {
		parent::__construct( $activity_log );
		// $activity_log->AddCleanupHook( array( $this, 'CleanUp' ) );
	}

	/**
	 * Log an event.
	 *
	 * @param integer $type - Alert code.
	 * @param array   $data - Metadata.
	 * @param integer $date (Optional) - created_on.
	 * @param integer $siteid (Optional) - site_id.
	 * @param bool    $migrated (Optional) - is_migrated.
	 */
	public function log( $type, $data = array(), $date = null, $siteid = null, $migrated = false ) {
		// Is this a php alert, and if so, are we logging such alerts?
		if ( $type < 0010 ) {
			return;
		}

		// Create new occurrence.
		$occ              = new Occurrence();
		$occ->is_migrated = $migrated;
		$occ->created_on  = is_null( $date ) ? microtime( true ) : $date;
		$occ->alert_id    = $type;
		$occ->site_id     = ! is_null( $siteid ) ? $siteid : ( function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 0 );

		// Save the alert occurrence.
		$occ->Save();

		// Set up meta data of the alert.
		$occ->SetMeta( $data );
	}

	/**
	 * Clean Up alerts by date OR by max number.
	 */
	public function CleanUp() {}
}
