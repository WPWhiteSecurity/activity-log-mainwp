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

				// Call to child sites to check if WSAL is installed on them or not.
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
		$this->wsal_child_sites = $this->get_wsal_child_sites(); // Check child sites with WSAL.
		$this->query_child_site_events();

		if ( $this->activity_log->is_child_enabled() ) {
			?>
			<nav id="wsal-tabs" class="nav-tab-wrapper">
				<a href="<?php echo esc_url( '#' ); ?>" class="nav-tab nav-tab-active">
					<?php echo esc_html( 'Activity Log' ); ?>
				</a>
				<a href="<?php echo esc_url( '#' ); ?>" class="nav-tab">
					<?php echo esc_html( 'Settings' ); ?>
				</a>
			</nav>
			<?php
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

	/**
	 * Query events from all the child sites.
	 *
	 * @return void
	 */
	private function query_child_site_events() {
		// Get events count from native events DB.
		$occurrence = new \WSAL\MainWPExtension\Models\Occurrence();
		$occ_count  = (int) $occurrence->Count();

		// Check if the WSAL child sites option exists.
		$child_sites = $this->activity_log->settings->get_option( 'wsal-child-sites' );

		if ( ! empty( $child_sites ) && is_array( $child_sites ) && 0 === $occ_count ) {
			$sites_data = array();

			foreach ( $child_sites as $site_id => $child_site ) {
				// Post data for child sites.
				$post_data = array(
					'action'       => 'get_events',
					'events_count' => 100,
				);

				// Call to child sites to fetch WSAL events.
				$sites_data[ $site_id ] = apply_filters(
					'mainwp_fetchurlauthed',
					$this->activity_log->get_child_file(),
					$this->activity_log->get_child_key(),
					$site_id,
					'extra_excution',
					$post_data
				);
			}

			if ( ! empty( $sites_data ) && is_array( $sites_data ) ) {
				foreach ( $sites_data as $site_id => $site_events ) {
					if ( ! isset( $site_events->events ) ) {
						continue;
					}
					$this->activity_log->alerts->log_events( $site_events->events, $site_id );
				}
			}
		}
	}
}
