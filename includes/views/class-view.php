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
class View extends Abstract_View {

	/**
	 * Render View.
	 */
	public function render_view() {
		$this->render_page();
	}

	/**
	 * Render Header.
	 */
	public function header() {
		// The "mainwp-pageheader-extensions" action is used to render the tabs on the Extensions screen.
		// It's used together with mainwp-pagefooter-extensions and mainwp-getextensions.
		do_action( 'mainwp-pageheader-extensions', __FILE__ );
	}

	/**
	 * Render Content.
	 */
	public function content() {
		if ( $this->activity_log->is_child_enabled() ) {
			echo 'Hello World';
		} else {
			?>
			<div class="mainwp_info-box-yellow">
				<?php esc_html_e( 'The Extension has to be enabled to change the settings.', 'mwp-al-ext' ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Render Footer.
	 */
	public function footer() {
		do_action( 'mainwp-pagefooter-extensions', __FILE__ );
	}
}
