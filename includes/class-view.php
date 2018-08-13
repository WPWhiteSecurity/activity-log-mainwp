<?php
/**
 * Class: View
 *
 * View class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Views;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * View class of the extension.
 */
class View {

	/**
	 * Constructor.
	 *
	 * @param Activity_Log $activity_log – Instance of main class.
	 */
	public function __construct( Activity_Log $activity_log ) {
	}
}
