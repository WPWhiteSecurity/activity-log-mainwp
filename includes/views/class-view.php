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
	 * MainWP Child Sites.
	 *
	 * @var array
	 */
	private $mwp_child_sites = array();

	/**
	 * WSAL Enabled Child Sites.
	 *
	 * @var array
	 */
	private $wsal_child_sites = array();

	/**
	 * Get WSAL Child Sites.
	 *
	 * @return array
	 */
	private function get_wsal_child_sites() {
		// Check if the WSAL child sites option exists.
		$child_sites = $this->activity_log->settings->get_option( 'wsal-child-sites' );

		if ( false === $child_sites && ! empty( $this->mainwp_sites ) ) {
			foreach ( $this->mainwp_sites as $site ) {
				$post_data = array(
					'action' => 'check_wsal',
				);

				$results[ $site['id'] ] = apply_filters(
					'mainwp_fetchurlauthed',
					$this->activity_log->get_child_file(),
					$this->activity_log->get_child_key(),
					$site['id'],
					'extra_excution',
					$post_data
				);
			}

			if ( ! empty( $results ) && is_array( $results ) ) {
				$child_sites = array();

				foreach ( $results as $site_id => $site_obj ) {
					if ( empty( $site_obj ) ) {
						continue;
					} elseif ( true === $site_obj->wsal_installed ) {
						$child_sites[ $site_id ] = $site_obj;
					}
				}
				$this->activity_log->settings->update_option( 'wsal-child-sites', $child_sites );
			}
		}

		return $child_sites;
	}

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
		$this->mainwp_sites     = apply_filters( 'mainwp-getsites', $this->activity_log->get_child_file(), $this->activity_log->get_child_key(), null );
		$this->wsal_child_sites = $this->get_wsal_child_sites();

		// foreach ( $websites as $single_site ) {
		// 	$information[] = apply_filters( 'mainwp_fetchurlauthed', $this->activity_log->get_child_file(), $this->activity_log->get_child_key(), $single_site['id'], 'extra_excution', array() );
		// }

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
