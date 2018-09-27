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
// use \WSAL\MainWPExtension\Event_Ref as Event_Ref;

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
		add_action( 'admin_init', array( $this, 'handle_auditlog_form_submission' ) );
		add_action( 'wp_ajax_set_per_page_events', array( $this, 'set_per_page_events' ) );
		add_action( 'wp_ajax_metadata_inspector', array( $this, 'metadata_inspector' ) );
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
		wp_register_script(
			'mwpal-view-script',
			trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/index.js',
			array( 'jquery' ),
			filemtime( trailingslashit( MWPAL_BASE_DIR ) . 'assets/js/dist/index.js' ),
			false
		);

		// JS data.
		$script_data = array(
			'ajaxURL'     => admin_url( 'admin-ajax.php' ),
			'scriptNonce' => wp_create_nonce( 'mwp-activitylog-nonce' ),
		);
		wp_localize_script( 'mwpal-view-script', 'scriptData', $script_data );
		wp_enqueue_script( 'mwpal-view-script' );
	}

	/**
	 * Handle Audit Log Form Submission.
	 */
	public function handle_auditlog_form_submission() {
		// Global WP page now variable.
		global $pagenow;

		// Only run the function on audit log custom page.
		// @codingStandardsIgnoreStart
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Current page.
		// @codingStandardsIgnoreEnd

		if ( 'admin.php' !== $pagenow ) {
			return;
		} elseif ( 'Extensions-Mainwp-Activity-Log-Extension' !== $page ) { // Page is admin.php, now check auditlog page.
			return; // Return if the current page is not auditlog's.
		}

		// Verify nonce for security.
		if ( isset( $_GET['_wpnonce'] ) ) {
			check_admin_referer( 'bulk-activity-logs' );
		}

		// Site id.
		$site_id = isset( $_GET['mwpal-site-id'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['mwpal-site-id'] ) ) : false;

		if ( ! empty( $wpnonce ) ) {
			// Remove args array.
			$remove_args = array(
				'_wp_http_referer',
				'_wpnonce',
			);

			if ( empty( $site_id ) ) {
				$remove_args[] = 'mwpal-site-id';
			}
			wp_safe_redirect( remove_query_arg( $remove_args ) );
			exit();
		}
	}

	/**
	 * Get WSAL Child Sites.
	 *
	 * @return array
	 */
	private function get_wsal_child_sites() {
		// Check if the WSAL child sites option exists.
		$child_sites = $this->activity_log->settings->get_option( 'wsal-child-sites' );

		if ( empty( $child_sites ) && ! empty( $this->mainwp_sites ) ) {
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
		$site_id = $this->activity_log->settings->get_view_site_id();

		// @codingStandardsIgnoreStart
		$mwp_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Admin WSAL Page.
		// @codingStandardsIgnoreEnd

		if ( $this->activity_log->is_child_enabled() ) {
			$this->get_list_view()->prepare_items();
			?>
			<form id="audit-log-viewer" method="get">
				<div id="audit-log-viewer-content">
					<input type="hidden" name="page" value="<?php echo esc_attr( $mwp_page ); ?>" />
					<input type="hidden" id="mwpal-site-id" name="mwpal-site-id" value="<?php echo esc_attr( $site_id ); ?>" />
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
		// Check if the WSAL child sites option exists.
		$child_sites = $this->get_wsal_child_sites();

		if ( ! empty( $child_sites ) && is_array( $child_sites ) ) {
			$sites_data = array();

			foreach ( $child_sites as $site_id => $child_site ) {
				// Get events count from native events DB.
				$occ_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();
				$occ_query->addCondition( 'site_id = %s ', $site_id ); // Set site id.
				$occ_count = (int) $occ_query->getAdapter()->Count( $occ_query );

				// If events are already present in the DB of a site, then no need to query from child site.
				if ( 0 !== $occ_count ) {
					continue;
				}

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

	/**
	 * Set Per Page Events
	 */
	public function set_per_page_events() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Access denied.', 'mwp-al-ext' ) );
		}

		// @codingStandardsIgnoreStart
		$nonce           = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
		$per_page_events = isset( $_POST['count'] ) ? sanitize_text_field( wp_unslash( $_POST['count'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'mwp-activitylog-nonce' ) ) {
			if ( empty( $per_page_events ) ) {
				die( esc_html__( 'Count parameter expected.', 'mwp-al-ext' ) );
			}
			$this->activity_log->settings->set_view_per_page( (int) $per_page_events );
			die();
		}
		die( esc_html__( 'Nonce verification failed.', 'mwp-al-ext' ) );
	}

	/**
	 * Events Metadata Viewer
	 */
	public function metadata_inspector() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Access denied.', 'mwp-al-ext' ) );
		}

		// @codingStandardsIgnoreStart
		$nonce         = isset( $_GET['mwp_meta_nonc'] ) ? sanitize_text_field( wp_unslash( $_GET['mwp_meta_nonc'] ) ) : false;
		$occurrence_id = isset( $_GET['occurrence_id'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['occurrence_id'] ) ) : false;
		// @codingStandardsIgnoreEnd

		if ( empty( $occurrence_id ) ) {
			die( esc_html__( 'Occurrence ID parameter expected.', 'mwp-al-ext' ) );
		}

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'mwp-meta-display-' . $occurrence_id ) ) {
			$occurrence = new \WSAL\MainWPExtension\Models\Occurrence();
			$occurrence->Load( 'id = %d', array( $occurrence_id ) );
			$event_meta = $occurrence->GetMetaArray();
			unset( $event_meta['ReportText'] );

			// Set Event_Ref class scripts and styles.
			\Event_Ref::config( 'stylePath', esc_url( trailingslashit( MWPAL_BASE_URL ) ) . 'assets/css/dist/wsal-ref.css' );
			\Event_Ref::config( 'scriptPath', esc_url( trailingslashit( MWPAL_BASE_URL ) ) . 'assets/js/dist/wsal-ref.js' );

			echo '<!DOCTYPE html><html><head>';
			echo '<style type="text/css">';
			echo 'html, body { margin: 0; padding: 0; }';
			echo '</style>';
			echo '</head><body>';
			\wsal_r( $event_meta );
			echo '</body></html>';
			die;
		}
		die( esc_html__( 'Nonce verification failed.', 'mwp-al-ext' ) );
	}
}
