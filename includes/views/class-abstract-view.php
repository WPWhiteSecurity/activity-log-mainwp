<?php
/**
 * Abstract Class: View
 *
 * Abstract view class file of the extension.
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
 * Abstract view class of the extension.
 */
abstract class Abstract_View {

	/**
	 * Instance of main plugin class.
	 *
	 * @var \WSAL\MainWPExtension\Activity_Log
	 */
	public $activity_log;

	/**
	 * Constructor.
	 *
	 * @param Activity_Log $activity_log – Instance of main class.
	 */
	public function __construct( Activity_Log $activity_log ) {
		$this->activity_log = $activity_log;
	}

	/**
	 * Render Header.
	 */
	abstract public function header();

	/**
	 * Render Content.
	 */
	abstract public function content();

	/**
	 * Render Footer.
	 */
	abstract public function footer();

	/**
	 * Render Extension Page.
	 */
	public function render_page() {
		$this->header();
		$this->content();
		$this->footer();
	}
}
