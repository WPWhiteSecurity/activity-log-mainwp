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
	 * Extension Tabs.
	 *
	 * @var array
	 */
	private $mwpal_extension_tabs = array();

	/**
	 * Current Tab.
	 *
	 * @var string
	 */
	private $current_tab = '';

	/**
	 * Constructor.
	 *
	 * @param Activity_Log $activity_log – Instance of Activity_Log.
	 */
	public function __construct( Activity_Log $activity_log ) {
		parent::__construct( $activity_log );
		add_filter( 'mainwp_left_menu_sub', array( $this, 'mwp_left_menu_sub' ), 10, 1 );
		add_filter( 'mainwp_subleft_menu_sub', array( $this, 'mwp_sub_menu_dropdown' ), 10, 1 );
		add_action( 'mainwp-pageheader-extensions', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'mainwp-pagefooter-extensions', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_auditlog_form_submission' ) );
		add_action( 'wp_ajax_set_per_page_events', array( $this, 'set_per_page_events' ) );
		add_action( 'wp_ajax_metadata_inspector', array( $this, 'metadata_inspector' ) );

		// Extension view URL.
		$extension_url = add_query_arg( 'page', MWPAL_EXTENSION_NAME, admin_url( 'admin.php' ) );

		// Tab links.
		$mwpal_extension_tabs = array(
			'activity-log' => array(
				'name'   => __( 'Activity Log', 'mwp-al-ext' ),
				'link'   => add_query_arg( 'tab', 'activity-log', $extension_url ),
				'render' => array( $this, 'tab_activity_log' ),
				'save'   => array( $this, 'tab_activity_log_save' ),
			),
			'settings'     => array(
				'name'   => __( 'Extension Settings', 'mwp-al-ext' ),
				'link'   => add_query_arg( 'tab', 'settings', $extension_url ),
				'render' => array( $this, 'tab_settings' ),
				'save'   => array( $this, 'tab_settings_save' ),
			),
		);

		/**
		 * Filter: `mwpal_extension_tabs`
		 *
		 * This filter is used to filter the tabs of WSAL settings page.
		 *
		 * Setting tabs structure:
		 *     $mwpal_extension_tabs['unique-tab-id'] = array(
		 *         'name'   => Name of the tab,
		 *         'link'   => Link of the tab,
		 *         'render' => This function is used to render HTML elements in the tab,
		 *         'name'   => This function is used to save the related setting of the tab,
		 *     );
		 *
		 * @param array $mwpal_extension_tabs – Array of extension tabs.
		 */
		$this->mwpal_extension_tabs = apply_filters( 'mwpal_extension_tabs', $mwpal_extension_tabs );

		// Get the current tab.
		$current_tab       = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$this->current_tab = empty( $current_tab ) ? 'activity-log' : $current_tab;
	}

	/**
	 * Filter MainWP Dashboard Menu
	 *
	 * Modify MainWP Dashboard menu to include activity log's menu.
	 *
	 * @param array $mwp_sub_menu – MainWP Sub-Menu.
	 * @return array
	 */
	public function mwp_left_menu_sub( $mwp_sub_menu ) {
		$activity_log_key = false;
		$extensions_menu  = isset( $mwp_sub_menu['Extensions'] ) ? $mwp_sub_menu['Extensions'] : false;

		if ( $extensions_menu ) {
			foreach ( $extensions_menu as $key => $submenu ) {
				if ( MWPAL_EXTENSION_NAME === $submenu[1] ) {
					$activity_log_key = $key;
					break;
				}
			}

			$sub_menu_before = array_slice( $mwp_sub_menu['mainwp_tab'], 0, 2 );
			$sub_menu_after  = array_splice( $mwp_sub_menu['mainwp_tab'], 2 );
			$activity_log    = $mwp_sub_menu['Extensions'][ $activity_log_key ];
			$activity_log[3] = '<i class="fa fa-globe"></i>';

			$mwp_sub_menu['mainwp_tab'][] = $activity_log;
			$mwp_sub_menu['mainwp_tab']   = array_merge( $mwp_sub_menu['mainwp_tab'], $sub_menu_after );
			unset( $mwp_sub_menu['Extensions'][ $activity_log_key ] );
		}
		return $mwp_sub_menu;
	}

	/**
	 * Filter MainWP Dropdown Menus
	 *
	 * Modify mainwp dropdown menu to include activity log's
	 * dropdown menu.
	 *
	 * @param array $mwp_dropdown_menu – Dropdown menus of MainWP.
	 * @return array
	 */
	public function mwp_sub_menu_dropdown( $mwp_dropdown_menu ) {
		$mwp_dropdown_menu[ MWPAL_EXTENSION_NAME ] = array(
			array(
				'Extension Settings',
				'admin.php?page=' . MWPAL_EXTENSION_NAME . '&tab=settings',
			),
		);
		return $mwp_dropdown_menu;
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
		} elseif ( MWPAL_EXTENSION_NAME !== $page ) {
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
		} elseif ( MWPAL_EXTENSION_NAME !== $page ) {
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
		} elseif ( MWPAL_EXTENSION_NAME !== $page ) { // Page is admin.php, now check auditlog page.
			return; // Return if the current page is not auditlog's.
		}

		// Verify nonce for security.
		if ( isset( $_GET['_wpnonce'] ) ) {
			check_admin_referer( 'bulk-activity-logs' );

			// Site id.
			$site_id = isset( $_GET['mwpal-site-id'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['mwpal-site-id'] ) ) : false;

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

		if ( $this->activity_log->is_child_enabled() ) {
			$this->get_list_view()->prepare_items();
			?>
			<nav class="mainwp-subnav-tabs">
				<?php foreach ( $this->mwpal_extension_tabs as $tab_id => $tab ) : ?>
					<?php if ( empty( $this->current_tab ) ) : ?>
						<a href="<?php echo esc_url( $tab['link'] ); ?>" class="mainwp_action <?php echo ( 'activity-log' === $tab_id ) ? 'mainwp_action_down' : false; ?>">
							<?php echo esc_html( $tab['name'] ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $tab['link'] ); ?>" class="mainwp_action <?php echo ( $tab_id === $this->current_tab ) ? 'mainwp_action_down' : false; ?>">
							<?php echo esc_html( $tab['name'] ); ?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</nav>
			<!-- Extension Sub-tabs -->

			<div class="mwpal-content-wrapper">
				<?php
				if ( ! empty( $this->current_tab ) && ! empty( $this->mwpal_extension_tabs[ $this->current_tab ]['render'] ) ) {
					call_user_func( $this->mwpal_extension_tabs[ $this->current_tab ]['render'] );
				} else {
					call_user_func( $this->mwpal_extension_tabs['activity-log']['render'] );
				}
				?>
			</div>
			<!-- Content Wrapper -->
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
	 * Tab: `Activity Log`
	 */
	public function tab_activity_log() {
		// @codingStandardsIgnoreStart
		$mwp_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Admin WSAL Page.
		// @codingStandardsIgnoreEnd
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
	}

	/**
	 * Tab: `Settings`
	 */
	public function tab_settings() {
		echo 'Hello, World!';
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
