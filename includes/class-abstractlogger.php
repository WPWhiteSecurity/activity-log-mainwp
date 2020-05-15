<?php
/**
 * Class: Abstract Logger
 *
 * Abstract logger class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Loggers;

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
