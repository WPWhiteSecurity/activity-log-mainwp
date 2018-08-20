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

// require_once ABSPATH . 'wp-admin/includes/plugin.php';

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
	 * Extension List View.
	 *
	 * @var object
	 */
	private $list_view = null;

	/**
	 * Constructor.
	 *
	 * @param Activity_Log $activity_log – Instance of Activity_Log.
	 */
	public function __construct( Activity_Log $activity_log ) {
		parent::__construct( $activity_log );

		add_action( 'mainwp-pageheader-extensions', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'mainwp-pagefooter-extensions', array( $this, 'enqueue_scripts' ), 10 );
	}

	/**
	 * Enqueue Styles in Head.
	 */
	public function enqueue_styles() {
		// Confirm extension page.
		global $pagenow;

		// @codingStandardsIgnoreStart
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( 'admin.php' !== $pagenow ) {
			return;
		} elseif ( 'Extensions-Mainwp-Activity-Log-Extension' !== $page ) {
			return;
		}

		// View styles.
		wp_enqueue_style(
			'mwpal-view-styles',
			trailingslashit( MWPAL_BASE_URL ) . 'assets/css/dist/styles.build.css',
			array(),
			filemtime( trailingslashit( MWPAL_BASE_DIR ) . 'assets/css/dist/styles.build.css' )
		);
	}

	/**
	 * Enqueue Scripts in Footer.
	 */
	public function enqueue_scripts() {
		// Confirm extension page.
		global $pagenow;

		// @codingStandardsIgnoreStart
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( 'admin.php' !== $pagenow ) {
			return;
		} elseif ( 'Extensions-Mainwp-Activity-Log-Extension' !== $page ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'mwpal-view-script',
			trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/index.js',
			array( 'jquery' ),
			filemtime( trailingslashit( MWPAL_BASE_DIR ) . 'assets/js/dist/index.js' ),
			true
		);
	}

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
			$this->get_list_view()->prepare_items();
			?>
			<form id="audit-log-viewer" method="get">
				<div id="audit-log-viewer-content">
					<input type="hidden" id="mwpal-site-id" name="mwpal-site-id" value="0" />
					<?php
					/**
					 * Action: `mwpal_auditlog_after_view`
					 *
					 * Do action before the view renders.
					 *
					 * @param ActivityLogListView $this->list_view – Events list view.
					 */
					do_action( 'mwpal_auditlog_before_view', $this->get_list_view() );

					// Display events table.
					$this->get_list_view()->display();

					/**
					 * Action: `mwpal_auditlog_after_view`
					 *
					 * Do action after the view has been rendered.
					 *
					 * @param ActivityLogListView $this->list_view – Events list view.
					 */
					do_action( 'mwpal_auditlog_after_view', $this->get_list_view() );
					?>
				</div>
			</form>
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

	/**
	 * Get Extension's List Table Instance.
	 *
	 * @return AuditLogListView
	 */
	private function get_list_view() {
		if ( is_null( $this->list_view ) ) {
			$this->list_view = new AuditLogListView( $this->activity_log );
		}
		return $this->list_view;
	}
}
