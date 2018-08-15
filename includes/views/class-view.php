<?php
/**
 * Class: View
 *
 * View class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Views;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * View class of the extension.
 */
class View extends Abstract_View {

	/**
	 * Render Header.
	 */
	public function header() {
		// The "mainwp-pageheader-extensions" action is used to render the tabs on the Extensions screen.
		// It's used together with mainwp-pagefooter-extensions and mainwp-getextensions.
		do_action( 'mainwp-pageheader-extensions', $this->activity_log->get_child_file() );
	}

	/**
	 * Render Content.
	 */
	public function content() {
		// Fetch all child-sites.
		// $websites = apply_filters( 'mainwp-getsites', $this->activity_log->get_child_file(), $this->activity_log->get_child_key(), null );

		// foreach ( $websites as $single_site ) {
		// 	$information[] = apply_filters( 'mainwp_fetchurlauthed', $this->activity_log->get_child_file(), $this->activity_log->get_child_key(), $single_site['id'], 'extra_excution', array() );
		// }

		// $this->activity_log->log( $information );

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
		do_action( 'mainwp-pagefooter-extensions', $this->activity_log->get_child_file() );
	}
}
