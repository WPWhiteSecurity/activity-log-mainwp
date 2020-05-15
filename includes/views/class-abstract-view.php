<?php
/**
 * Abstract Class: View
 *
 * Abstract view class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Views;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract view class of the extension.
 */
abstract class Abstract_View {

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
