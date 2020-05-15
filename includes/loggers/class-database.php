<?php
/**
 * Class: Logger
 *
 * Logger class for wsal.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Loggers;

use \WSAL\MainWPExtension as MWPAL_Extension;
use \WSAL\MainWPExtension\Loggers\AbstractLogger as AbstractLogger;
use \WSAL\MainWPExtension\Models\Occurrence as Occurrence;
use \WSAL\MainWPExtension\Models\OccurrenceQuery as OccurrenceQuery;

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
	 * Constructor.
	 */
	public function __construct() {
		MWPAL_Extension\mwpal_extension()->add_cleanup_hook( array( $this, 'cleanup' ) );
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
	 * Clean up alerts by date.
	 */
	public function cleanup() {
		$mwpal_extension = MWPAL_Extension\mwpal_extension();
		$now             = current_time( 'timestamp' );
		$is_pruning      = $mwpal_extension->settings->is_events_pruning();
		$pruning_date    = $mwpal_extension->settings->get_pruning_date();
		$pruning_date    = $pruning_date->date . $pruning_date->unit;

		if ( ! $is_pruning ) {
			return;
		}

		// Calculate max timestamp.
		$max_timestamp = $now - ( strtotime( $pruning_date ) - $now );

		$query = new OccurrenceQuery();
		$query->addOrderBy( 'created_on', false );
		$query->addCondition( 'created_on <= %s', intval( $max_timestamp ) );
		$query->addCondition( 'site_id = %s ', '0' );
		$query->getAdapter()->Delete( $query );
	}
}
