<?php
/**
 * Class: View
 *
 * View class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Views;

use \WSAL\MainWPExtension as MWPAL_Extension;

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
	 * Audit Log View Arguments.
	 *
	 * @since 1.1
	 *
	 * @var stdClass
	 */
	private $page_args;

	/**
	 * Stores the value of the last view the user requested.
	 *
	 * @since 1.4.0
	 *
	 * @var string
	 */
	public $user_last_view = '';

	const MWPAL_REFRESH_KEY = 'mwpal_site_refresh_in_progress';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'setup_extension_tabs' ), 10 );
		add_filter( 'mainwp-getsubpages-sites', array( $this, 'managesites_subpage' ), 10, 1 );
		add_filter( 'mainwp_left_menu_sub', array( $this, 'mwp_left_menu_sub' ), 10, 1 );
		add_filter( 'mainwp_subleft_menu_sub', array( $this, 'mwp_sub_menu_dropdown' ), 10, 1 );
		add_filter( 'mainwp_main_menu', array( $this, 'mwpal_main_menu' ), 10, 1 );
		add_filter( 'mainwp_main_menu_submenu', array( $this, 'mwpal_main_menu_submenu' ), 10, 1 );
		add_action( 'mainwp-pageheader-extensions', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'mainwp-pagefooter-extensions', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'mainwp-pageheader-sites', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'mainwp-pagefooter-sites', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_auditlog_form_submission' ), 20 );
		add_action( 'wp_ajax_set_per_page_events', array( $this, 'set_per_page_events' ) );
		add_action( 'wp_ajax_metadata_inspector', array( $this, 'metadata_inspector' ) );
		add_action( 'wp_ajax_refresh_child_sites', array( $this, 'refresh_child_sites' ) );
		add_action( 'wp_ajax_update_active_wsal_sites', array( $this, 'update_active_wsal_sites' ) );
		add_action( 'wp_ajax_retrieve_events_manually', array( $this, 'retrieve_events_manually' ) );
		add_action( 'wp_ajax_mwpal_advert_dismissed', array( $this, 'mwpal_advert_dismissed' ) );
		add_action( 'wp_ajax_mwpal_user_notice_dismissed', array( $this, 'user_notice_dismissed' ) );
		add_action( 'wp_ajax_mwpal_purge_logs', array( $this, 'purge_logs' ) );
		add_action( 'admin_footer', array( $this, 'mwpal_extensions_print_scripts' ) );
		if ( MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll() ) {
			add_action( 'wp_ajax_mwpal_infinite_scroll_events', array( $this, 'infinite_scroll_events' ) );
		}

		if ( MWPAL_Extension\mwpal_extension()->is_mainwp_active() ) {
			if ( \version_compare( \MainWP_System::$version, '4.0-beta', '<' ) ) {
				add_action( 'mainwp_extensions_top_header_after_tab', array( $this, 'activitylog_settings_tab' ), 10, 1 );
				add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
			} else {
				add_filter( 'mainwp_page_navigation', array( $this, 'mwpal_extension_tabs' ), 10, 1 );
			}
		}

		// Setup the users last view by getting the value from user meta.
		$last_view            = get_user_meta( get_current_user_id(), 'almwp-selected-main-view', true );
		$this->user_last_view = ( in_array( $last_view, $this->supported_view_types(), true ) ) ? $last_view : 'list';
	}

	/**
	 * AJAX function for purging activity logs in the MainWP instance.
	 *
	 * @method purge_logs
	 * @since  1.3.0
	 */
	public function purge_logs() {
		// Check nonce and user permissions, bail early with no updates.
		check_ajax_referer( 'mwp-activitylog-nonce', 'mwp_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => 'failed',
				)
			);
		}
		$db = new \WSAL\MainWPExtension\Connector\MySQLDB();
		$db->purge_activity();
		wp_send_json_success(
			array(
				'message' => 'success',
			)
		);
	}

	/**
	 * Setup extension tabs.
	 */
	public function setup_extension_tabs() {
		global $_mainwp_menu_active_slugs;
		$_mainwp_menu_active_slugs[ MWPAL_EXTENSION_NAME ] = MWPAL_EXTENSION_NAME;

		// Extension view URL.
		$extension_url = add_query_arg( 'page', MWPAL_EXTENSION_NAME, admin_url( 'admin.php' ) );

		// Tab links.
		$mwpal_extension_tabs = array(
			'activity-log' => array(
				'name'   => __( 'Activity Log', 'mwp-al-ext' ),
				'link'   => $extension_url,
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
		 * `mwpal_extension_tabs`
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
		 * @param array  $mwpal_extension_tabs - Array of extension tabs.
		 * @param string $extension_url        - URL of the extension.
		 */
		$this->mwpal_extension_tabs = apply_filters( 'mwpal_extension_tabs', $mwpal_extension_tabs, $extension_url );

		// Get the current tab.
		$current_tab       = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$this->current_tab = empty( $current_tab ) ? 'activity-log' : $current_tab;
	}

	/**
	 * Returns current tab of the extension.
	 *
	 * @return string
	 */
	public function get_current_tab() {
		return $this->current_tab;
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

			// Set the menu name.
			$mwp_sub_menu['Extensions'][ $activity_log_key ][0] = __( 'Activity Log', 'mwp-al-ext' );

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
		$mwp_dropdown_menu[ MWPAL_EXTENSION_NAME ] = apply_filters(
			'mwpal_left_submenu_dropdown',
			array(
				array(
					__( 'Extension Settings', 'mwp-al-ext' ),
					$this->mwpal_extension_tabs['settings']['link'],
					'',
				),
			)
		);

		return $mwp_dropdown_menu;
	}

	/**
	 * Extension left menu for MainWP v4 or later.
	 *
	 * @param array $mwpal_left_menu - Left menu array.
	 * @return array
	 */
	public function mwpal_main_menu( $mwpal_left_menu ) {
		$sub_menu_before = array_slice( $mwpal_left_menu['mainwp_tab'], 0, 2 );
		$sub_menu_after  = array_splice( $mwpal_left_menu['mainwp_tab'], 2 );

		$activity_log   = array();
		$activity_log[] = __( 'Activity Log', 'mwp-al-ext' );
		$activity_log[] = MWPAL_EXTENSION_NAME;
		$activity_log[] = $this->mwpal_extension_tabs['activity-log']['link'];

		$mwpal_left_menu['mainwp_tab'][] = $activity_log;
		$mwpal_left_menu['mainwp_tab']   = array_merge( $mwpal_left_menu['mainwp_tab'], $sub_menu_after );

		return $mwpal_left_menu;
	}

	/**
	 * Extension sub left menu for MainWP v4 or later.
	 *
	 * @param array $mwpal_sub_left_menu - Left menu array.
	 * @return array
	 */
	public function mwpal_main_menu_submenu( $mwpal_sub_left_menu ) {
		$mwpal_sub_left_menu[ MWPAL_EXTENSION_NAME ] = apply_filters(
			'mwpal_main_menu_submenu',
			array(
				array(
					__( 'Extension Settings', 'mwp-al-ext' ),
					$this->mwpal_extension_tabs['settings']['link'],
					'manage_options',
				),
			)
		);

		return $mwpal_sub_left_menu;
	}

	/**
	 * Add Activity Log Settings Tab.
	 *
	 * @param string $current_page – Path of the extension.
	 */
	public function activitylog_settings_tab( $current_page ) {
		$activity_log = basename( $current_page, '.php' );

		if ( 'activity-log-mainwp' !== $activity_log ) {
			return;
		}

		$extension_tabs = $this->mwpal_extension_tabs;
		unset( $extension_tabs['activity-log'] ); // Due to the fact the activity log tab will already be added to the extension.

		foreach ( $extension_tabs as $tab_id => $tab ) :
			?>
			<a class="nav-tab pos-nav-tab echo<?php echo ( $tab_id === $this->current_tab ) ? ' nav-tab-active' : false; ?>" href="<?php echo esc_url( $tab['link'] ); ?>"><?php echo esc_html( $tab['name'] ); ?></a>
			<?php
		endforeach;
	}

	/**
	 * Print admin styles for MainWP versions earlier than 4.0.
	 */
	public function admin_print_styles() {
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
		?>
		<style>th#data, td.data.column-data { width: 16px; }</style>
		<?php
	}

	/**
	 * Add extension tabs to extension page.
	 *
	 * @param array $page_tabs - Array of page tabs.
	 * @return array
	 */
	public function mwpal_extension_tabs( $page_tabs ) {
		global $pagenow;

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // phpcs:ignore

		if ( 'admin.php' !== $pagenow ) {
			return $page_tabs;
		} elseif ( MWPAL_EXTENSION_NAME !== $page ) {
			return $page_tabs;
		}

		$page_tabs[1]['active'] = 'activity-log' === $this->current_tab;

		$extension_tabs = apply_filters(
			'mwpal_page_navigation',
			array(
				array(
					'title'  => __( 'Extension Settings', 'mwp-al-ext' ),
					'href'   => $this->mwpal_extension_tabs['settings']['link'],
					'active' => 'settings' === $this->current_tab,
				),
			)
		);

		foreach ( $extension_tabs as $tab ) {
			$page_tabs[] = $tab;
		}

		return $page_tabs;
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
		} elseif ( MWPAL_EXTENSION_NAME !== $page && 'ManageSitesActivityLog' !== $page ) {
			return;
		}

		if ( 'activity-log' === $this->current_tab ) {
			// Select2 styles.
			wp_enqueue_style(
				'mwpal-select2-css',
				trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/select2/select2.css',
				array(),
				'3.5.1'
			);
			wp_enqueue_style(
				'mwpal-select2-bootstrap-css',
				trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/select2/select2-bootstrap.css',
				array(),
				'3.5.1'
			);
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
		} elseif ( MWPAL_EXTENSION_NAME !== $page && 'ManageSitesActivityLog' !== $page ) {
			return;
		}

		// Enqueue jQuery.
		wp_enqueue_script( 'jquery' );

		if ( 'activity-log' === $this->current_tab ) {
			// Select2 script.
			wp_enqueue_script(
				'mwpal-select2-js',
				trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/select2/select2.min.js',
				array( 'jquery' ),
				'3.5.1',
				true
			);
		}

		if ( in_array( $this->current_tab, array( 'activity-log', 'settings' ), true ) ) {
			wp_register_script(
				'mwpal-view-script',
				trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/index.js',
				array( 'jquery' ),
				filemtime( trailingslashit( MWPAL_BASE_DIR ) . 'assets/js/dist/index.js' ),
				false
			);
		}

		// JS data.
		$script_data = array(
			'ajaxURL'        => admin_url( 'admin-ajax.php' ),
			'scriptNonce'    => wp_create_nonce( 'mwp-activitylog-nonce' ),
			'currentTab'     => $this->current_tab,
			'selectSites'    => __( 'Select Child Site(s)', 'mwp-al-ext' ),
			'refreshing'     => __( 'Refreshing Child Sites...', 'mwp-al-ext' ),
			'retrieving'     => __( 'Retrieving Logs...', 'mwp-al-ext' ),
			'page'           => isset( $this->page_args->page ) ? $this->page_args->page : false,
			'siteId'         => isset( $this->page_args->site_id ) ? $this->page_args->site_id : false,
			'orderBy'        => isset( $this->page_args->order_by ) ? $this->page_args->order_by : false,
			'order'          => isset( $this->page_args->order ) ? $this->page_args->order : false,
			'getEvents'      => isset( $this->page_args->get_events ) ? $this->page_args->get_events : false,
			'searchTerm'     => isset( $this->page_args->search_term ) ? $this->page_args->search_term : false,
			'searchFilters'  => isset( $this->page_args->search_filters ) ? $this->page_args->search_filters : false,
			'infiniteScroll' => MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll(),
			'userView'       => ( in_array( $this->user_last_view, $this->supported_view_types(), true ) ) ? $this->user_last_view : 'list',
		);
		wp_localize_script( 'mwpal-view-script', 'scriptData', $script_data );
		wp_enqueue_script( 'mwpal-view-script' );

		if ( 'activity-log' !== $this->current_tab ) {
			?>
			<script type="text/javascript">
				var currentTab = '<?php echo esc_html( $this->current_tab ); ?>';

				if ( 'activity-log' !== currentTab ) {
					var tabItems = document.getElementById( 'mainwp-tabs' );
					if( null !== tabItems && tabItems.length ) {
						tabItems.children[1].classList.remove( 'nav-tab-active' );
					}
				}
			</script>
			<?php
		}
	}

	/**
	 * Handle Audit Log Form Submission.
	 */
	public function handle_auditlog_form_submission() {
		if ( ! MWPAL_Extension\mwpal_extension()->settings->is_current_extension_page() ) {
			return;
		}

		if ( $this->current_tab && ! empty( $this->mwpal_extension_tabs[ $this->current_tab ]['save'] ) ) {
			call_user_func( $this->mwpal_extension_tabs[ $this->current_tab ]['save'] );
		}
	}

	/**
	 * Activity log form submit handler.
	 */
	public function tab_activity_log_save() {
		if ( isset( $_GET['_wpnonce'] ) ) {
			// Verify nonce for security.
			check_admin_referer( 'bulk-activity-logs' );

			// Site id.
			$site_id = isset( $_GET['mwpal-site-id'] ) ? sanitize_text_field( wp_unslash( $_GET['mwpal-site-id'] ) ) : false;

			// Check for dashboard.
			if ( '0' === $site_id ) {
				$site_id = false;
			} elseif ( 'dashboard' !== $site_id ) {
				$site_id = (int) $site_id;
			}

			$this->get_list_view();

			// Remove args array.
			$remove_args   = array( '_wp_http_referer', '_wpnonce' );
			$remove_args[] = ! $site_id ? 'mwpal-site-id' : false;
			$remove_args[] = ! $this->page_args->search_term ? 's' : false;
			$remove_args[] = ( ! is_int( $site_id ) && $this->page_args->get_events ) ? 'get-events' : false;

			$redirect_url = remove_query_arg( $remove_args );

			if ( is_int( $site_id ) && ( $this->page_args->search_term || $this->page_args->search_filters ) ) {
				$redirect_url = add_query_arg( 'get-events', 'live', $redirect_url );
			}

			wp_safe_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Settings form submit handler.
	 */
	public function tab_settings_save() {
		if ( isset( $_POST['_wpnonce'] ) && isset( $_POST['submit'] ) ) {
			// Verify nonce for security.
			check_admin_referer( 'mwpal-settings-nonce' );

			// Get form options.
			$events_nav_type    = isset( $_POST['events-nav-type'] ) ? sanitize_text_field( wp_unslash( $_POST['events-nav-type'] ) ) : false;
			$timezone           = isset( $_POST['timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['timezone'] ) ) : false;
			$type_username      = isset( $_POST['type_username'] ) ? sanitize_text_field( wp_unslash( $_POST['type_username'] ) ) : false;
			$child_site_events  = isset( $_POST['child-site-events'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['child-site-events'] ) ) : false;
			$events_frequency   = isset( $_POST['events-frequency'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['events-frequency'] ) ) : false;
			$events_global_sync = isset( $_POST['global-sync-events'] );
			$columns            = isset( $_POST['columns'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['columns'] ) ) : false;
			$wsal_child_sites   = isset( $_POST['mwpal-wsal-child-sites'] ) ? sanitize_text_field( wp_unslash( $_POST['mwpal-wsal-child-sites'] ) ) : false;
			$events_pruning     = isset( $_POST['events-pruning'] ) ? sanitize_text_field( wp_unslash( $_POST['events-pruning'] ) ) : false;
			$pruning_date       = ( isset( $_POST['events-pruning-date'] ) && 'enabled' === $events_pruning ) ? sanitize_text_field( wp_unslash( $_POST['events-pruning-date'] ) ) : false;
			$pruning_unit       = ( isset( $_POST['events-pruning-unit'] ) && 'enabled' === $events_pruning ) ? sanitize_text_field( wp_unslash( $_POST['events-pruning-unit'] ) ) : false;

			// Get enabled events.
			$enabled    = isset( $_POST['mwpal-event'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mwpal-event'] ) ) : array();
			$enabled    = array_map( 'intval', $enabled );
			$disabled   = array();
			$mwp_events = MWPAL_Extension\mwpal_extension()->alerts->get_alerts_by_sub_category( __( 'MainWP', 'mwp-al-ext' ) );

			foreach ( $mwp_events as $event ) {
				if ( ! in_array( $event->type, $enabled, true ) ) {
					$disabled[] = $event->type;
				}
			}

			// Set options.
			MWPAL_Extension\mwpal_extension()->settings->set_events_type_nav( $events_nav_type );
			MWPAL_Extension\mwpal_extension()->settings->set_timezone( $timezone );
			MWPAL_Extension\mwpal_extension()->settings->set_type_username( $type_username );
			MWPAL_Extension\mwpal_extension()->settings->set_child_site_events( $child_site_events );
			MWPAL_Extension\mwpal_extension()->settings->set_events_frequency( $events_frequency );
			MWPAL_Extension\mwpal_extension()->settings->set_events_global_sync( $events_global_sync );
			MWPAL_Extension\mwpal_extension()->settings->set_columns( $columns );
			MWPAL_Extension\mwpal_extension()->settings->set_wsal_child_sites( ! empty( $wsal_child_sites ) ? explode( ',', $wsal_child_sites ) : false );
			MWPAL_Extension\mwpal_extension()->settings->set_disabled_events( $disabled );
			MWPAL_Extension\mwpal_extension()->settings->set_events_pruning( $events_pruning );
			MWPAL_Extension\mwpal_extension()->settings->set_pruning_date( $pruning_date, $pruning_unit );
		}
	}

	/**
	 * Render Header.
	 */
	public function header() {
		// The "mainwp-pageheader-extensions" action is used to render the tabs on the Extensions screen.
		// It's used together with mainwp-pagefooter-extensions and mainwp-getextensions.
		do_action( 'mainwp-pageheader-extensions', MWPAL_Extension\mwpal_extension()->get_child_file() );
	}

	/**
	 * Render Content.
	 */
	public function content() {
		// Fetch all child-sites.
		$this->mwp_child_sites  = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_sites(); // Get MainWP child sites.
		$this->wsal_child_sites = MWPAL_Extension\mwpal_extension()->settings->get_wsal_child_sites(); // Get child sites with WSAL installed.

		if ( MWPAL_Extension\mwpal_extension()->is_child_enabled() ) :
			?>
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
		<?php else : ?>
			<div class="mainwp_info-box-yellow">
				<?php esc_html_e( 'The Extension has to be enabled to change the settings.', 'mwp-al-ext' ); ?>
			</div>
			<?php
			endif;
	}

	/**
	 * Tab: `Activity Log`
	 */
	public function tab_activity_log() {
		$this->get_list_view()->prepare_items();
		$site_id    = MWPAL_Extension\mwpal_extension()->settings->get_view_site_id();
		$mwp_page   = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // phpcs:ignore
		$get_events = isset( $_GET['get-events'] ) ? sanitize_text_field( wp_unslash( $_GET['get-events'] ) ) : false; // phpcs:ignore
		?>
		<form id="audit-log-viewer" method="get">
			<div id="audit-log-viewer-content">
				<input type="hidden" name="page" value="<?php echo esc_attr( $mwp_page ); ?>" />
				<input type="hidden" id="mwpal-site-id" name="mwpal-site-id" value="<?php echo esc_attr( $site_id ); ?>" />
				<?php if ( $get_events ) : ?>
					<input type="hidden" name="get-events" value="<?php echo esc_attr( $get_events ); ?>" />
					<?php
				endif;

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
		// @codingStandardsIgnoreStart
		$mwp_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Admin WSAL Page.
		// @codingStandardsIgnoreEnd

		$mwp_events = MWPAL_Extension\mwpal_extension()->alerts->get_alerts_by_sub_category( __( 'MainWP', 'mwp-al-ext' ) );
		$disabled   = MWPAL_Extension\mwpal_extension()->settings->get_disabled_events();
		?>
		<div class="metabox-holder columns-1">
			<form method="post" id="mwpal-settings">
				<input type="hidden" name="page" value="<?php echo esc_attr( $mwp_page ); ?>" />
				<?php wp_nonce_field( 'mwpal-settings-nonce' ); ?>
				<div class="meta-box-sortables ui-sortable">
					<div id="mwpal-setting-contentbox-1" class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'Activity Log Settings', 'mwp-al-ext' ); ?></span></h2>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="infinite-scroll"><?php esc_html_e( 'Event Viewer View Type', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $nav_type = MWPAL_Extension\mwpal_extension()->settings->get_events_type_nav(); ?>
												<label for="infinite-scroll">
													<input type="radio" name="events-nav-type" id="infinite-scroll" style="margin-top: -2px;" <?php checked( $nav_type, 'infinite-scroll' ); ?> value="infinite-scroll">
													<?php esc_html_e( 'Infinite Scroll', 'mwp-al-ext' ); ?>
												</label>
												<br/>
												<label for="pagination">
													<input type="radio" name="events-nav-type" id="pagination" style="margin-top: -2px;" <?php checked( $nav_type, 'pagination' ); ?> value="pagination">
													<?php esc_html_e( 'Pagination', 'mwp-al-ext' ); ?>
												</label>
											</fieldset>
										</td>
									</tr>
									<!-- Event Viewer View Type -->

									<tr>
										<th scope="row"><label for="utc"><?php esc_html_e( 'Events Timestamp', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $timezone = MWPAL_Extension\mwpal_extension()->settings->get_timezone(); ?>
												<label for="utc">
													<input type="radio" name="timezone" id="utc" style="margin-top: -2px;" <?php checked( $timezone, 'utc' ); ?> value="utc">
													<?php esc_html_e( 'UTC', 'mwp-al-ext' ); ?>
												</label>
												<br/>
												<label for="timezone">
													<input type="radio" name="timezone" id="timezone" style="margin-top: -2px;" <?php checked( $timezone, 'wp' ); ?> value="wp">
													<?php esc_html_e( 'Timezone configured on this WordPress website', 'mwp-al-ext' ); ?>
												</label>
											</fieldset>
										</td>
									</tr>
									<!-- Alerts Timestamp -->

									<tr>
										<th scope="row"><label for="column_username"><?php esc_html_e( 'Display this user information in activity log', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $type_username = MWPAL_Extension\mwpal_extension()->settings->get_type_username(); ?>
												<label for="column_username">
													<input type="radio" name="type_username" id="column_username" style="margin-top: -2px;" <?php checked( $type_username, 'username' ); ?> value="username">
													<span><?php esc_html_e( 'WordPress Username', 'mwp-al-ext' ); ?></span>
												</label>
												<br/>
												<label for="columns_first_last_name">
													<input type="radio" name="type_username" id="columns_first_last_name" style="margin-top: -2px;" <?php checked( $type_username, 'first_last_name' ); ?> value="first_last_name">
													<span><?php esc_html_e( 'First Name & Last Name', 'mwp-al-ext' ); ?></span>
												</label>
												<br/>
												<label for="columns_display_name">
													<input type="radio" name="type_username" id="columns_display_name" style="margin-top: -2px;" <?php checked( $type_username, 'display_name' ); ?> value="display_name">
													<span><?php esc_html_e( 'Configured Public Display Name', 'mwp-al-ext' ); ?></span>
												</label>
											</fieldset>
										</td>
									</tr>
									<!-- Select type of name -->

									<tr>
										<th><label for="columns"><?php esc_html_e( 'Activity Log Columns Selection', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $columns = MWPAL_Extension\mwpal_extension()->settings->get_columns(); ?>
												<?php foreach ( $columns as $key => $value ) { ?>
													<label for="columns">
														<input type="checkbox" name="columns[<?php echo esc_attr( $key ); ?>]" id="<?php echo esc_attr( $key ); ?>" class="sel-columns" style="margin-top: -2px;"
															<?php checked( $value, '1' ); ?> value="1">
														<?php if ( 'alert_code' === $key ) : ?>
															<span><?php esc_html_e( 'Event ID', 'mwp-al-ext' ); ?></span>
														<?php elseif ( 'type' === $key ) : ?>
															<span><?php esc_html_e( 'Severity', 'mwp-al-ext' ); ?></span>
														<?php elseif ( 'date' === $key ) : ?>
															<span><?php esc_html_e( 'Date & Time', 'mwp-al-ext' ); ?></span>
														<?php elseif ( 'username' === $key ) : ?>
															<span><?php esc_html_e( 'User', 'mwp-al-ext' ); ?></span>
														<?php elseif ( 'source_ip' === $key ) : ?>
															<span><?php esc_html_e( 'Source IP Address', 'mwp-al-ext' ); ?></span>
														<?php elseif ( 'info' === $key ) : ?>
															<span><?php esc_html_e( 'Info (used in Grid view mode only)', 'mwp-al-ext' ); ?></span>
														<?php else : ?>
															<span><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></span>
														<?php endif; ?>
													</label>
													<br/>
												<?php } ?>
											</fieldset>
										</td>
									</tr>
									<!-- Audit Log Columns Selection -->
								</tbody>
							</table>
						</div>
					</div>
					<!-- Activity Log Settings -->

					<div id="mwpal-setting-contentbox-2" class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'MainWP Network Activity Logs', 'mwp-al-ext' ); ?></span></h2>
						<div class="mainwp-postbox-actions-top"><p class="description"><?php esc_html_e( 'Use the below settings to disable / re-enable activity log events that are specific to the MainWP network and to also configure the pruning of such events.', 'mwp-al-ext' ); ?></p></div>
						<div class="inside">
							<h3><?php esc_html_e( 'Enable / Disable MainWP Network Activity Log Events', 'mwp-al-ext' ); ?></h3>
							<table class="wp-list-table widefat" id="mwpal-toggle-events-table">
								<thead>
									<tr>
										<th width="48"><input type="checkbox" id="mwpal-toggle-allchecked" <?php checked( ! $disabled ); ?>></td>
										<th width="80"><?php esc_html_e( 'Code', 'mwp-al-ext' ); ?></td>
										<th width="100"><?php esc_html_e( 'Severity', 'mwp-al-ext' ); ?></td>
										<th><?php esc_html_e( 'Description', 'mwp-al-ext' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $mwp_events as $event ) : ?>
										<tr>
											<th><input type="checkbox" name="mwpal-event[]" class="sel-columns" style="margin-top: -2px;" value="<?php echo esc_attr( $event->type ); ?>" <?php echo ! in_array( $event->type, $disabled, true ) ? 'checked' : false; ?>></th>
											<td><?php echo esc_html( $event->type ); ?></td>
											<td>
												<?php
												$severity_obj = MWPAL_Extension\mwpal_extension()->constants->GetConstantBy( 'value', $event->code );

												if ( 'E_CRITICAL' === $severity_obj->name ) {
													esc_html_e( 'Critical', 'mwp-al-ext' );
												} elseif ( 'E_WARNING' === $severity_obj->name ) {
													esc_html_e( 'Warning', 'mwp-al-ext' );
												} elseif ( 'E_NOTICE' === $severity_obj->name ) {
													esc_html_e( 'Notification', 'mwp-al-ext' );
												} else {
													esc_html_e( 'Notification', 'mwp-al-ext' );
												}
												?>
											</td>
											<td><?php echo esc_html( $event->desc ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- MainWP Network Activity Logs -->

					<div id="mwpal-setting-contentbox-3" class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'Activity Log Retrieval Settings', 'mwp-al-ext' ); ?></span></h2>
						<div class="mainwp-postbox-actions-top"><p class="description"><?php esc_html_e( 'The Activity Log for MainWP extension retrieves events directly from the child sites\' activity logs. Use the below settings to specify how many events the extension should retrieve and store from a child site, and how often it should do it.', 'mwp-al-ext' ); ?></p></div>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="child-site-events"><?php esc_html_e( 'Number of Events to Retrieve from Child Sites', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $child_site_events = MWPAL_Extension\mwpal_extension()->settings->get_child_site_events(); ?>
												<input type="number" id="child-site-events" name="child-site-events" value="<?php echo esc_attr( $child_site_events ); ?>" />
											</fieldset>
										</td>
									</tr>

									<tr>
										<th scope="row"><label for="events-frequency"><?php esc_html_e( 'Events Retrieval Frequency', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $events_frequency = MWPAL_Extension\mwpal_extension()->settings->get_events_frequency(); ?>
												<input type="number" id="events-frequency" name="events-frequency" value="<?php echo esc_attr( $events_frequency ); ?>" />
												<?php esc_html_e( 'hours', 'mwp-al-ext' ); ?>
											</fieldset>
										</td>
									</tr>

									<tr>
										<th scope="row"><label for="global-sync-events"><?php esc_html_e( 'Sync Events', 'mwp-al-ext' ); ?></label></th>
										<td>
											<fieldset>
												<?php $events_global_sync = MWPAL_Extension\mwpal_extension()->settings->is_events_global_sync(); ?>
												<input type="checkbox" id="global-sync-events" name="global-sync-events" value="1" <?php checked( $events_global_sync ); ?> />
												<?php esc_html_e( 'Retrieve activity logs from child sites when I sync data with child sites.', 'mwp-al-ext' ); ?>
											</fieldset>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!-- Activity Log Retrieval Settings -->

					<div class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'MainWP database activity logs management', 'mwp-al-ext' ); ?></span></h2>
						<div class="mainwp-postbox-actions-top"><p class="description"><?php esc_html_e( 'Use the settings below to manage the activity log data stored in the MainWP dashboard site database. Note that these settings do not apply to the activity logs of the child sites.', 'mwp-al-ext' ); ?></p></div>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th><label for="events-pruning"><?php esc_html_e( 'MainWP network activity logs retention policy', 'mwp-al-ext' ); ?></label></th>
									<td>
										<fieldset>
											<?php
											$events_pruning = MWPAL_Extension\mwpal_extension()->settings->is_events_pruning();
											$pruning_date   = MWPAL_Extension\mwpal_extension()->settings->get_pruning_date();
											?>
											<label for="pruning-enabled">
												<input type="radio" name="events-pruning" id="pruning-enabled" value="enabled" style="margin-top:-2px" <?php checked( $events_pruning ); ?>>
												<span>
													<?php esc_html_e( 'Delete events older than:', 'mwp-al-ext' ); ?>
													<input type="number" name="events-pruning-date" value="<?php echo esc_html( $pruning_date->date ); ?>">
													<select name="events-pruning-unit" style="margin-top: -2px;">
														<option value="months" <?php selected( $pruning_date->unit, 'months' ); ?>><?php esc_html_e( 'Months', 'mwp-al-ext' ); ?></option>
														<option value="years" <?php selected( $pruning_date->unit, 'years' ); ?>><?php esc_html_e( 'Years', 'mwp-al-ext' ); ?></option>
													</select>
												</span>
											</label>
											<br>
											<label for="pruning-disabled">
												<input type="radio" name="events-pruning" id="pruning-disabled" value="disabled" style="margin-top:-2px" <?php checked( $events_pruning, false ); ?>>
												<span><?php esc_html_e( 'Do not delete any events.', 'mwp-al-ext' ); ?></span>
											</label>
										</fieldset>
									</td>
								</tr>
								<tr>
									<th><label for="purge-trigger"><?php esc_html_e( 'Delete the activity log data stored in the MainWP database', 'mwp-al-ext' ); ?></label></th>
									<td>
										<fieldset>
											<label for="pruning-enabled">
												<input type="button" class="button-primary" name="events-pruning-now" id="purge-trigger" value="<?php esc_html_e( 'Delete activity log', 'mwp-al-ext' ); ?>">
											</label>
										</fieldset>
									</td>
								</tr>

								<div id="log-purged-popup" class="ui modal">
								  <div class="content">
								    <p><?php esc_html_e( 'Activity log data has been purged.', 'mwp-al-ext' ); ?></p>
								  </div>
								  <div class="actions">
								    <div class="ui button close-log-purged-popup"><?php esc_html_e( 'OK', 'mwp-al-ext' ); ?></div>
								  </div>
								</div>

							</table>
						</div>
					</div>

					<div id="mwpal-setting-contentbox-3" class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'List of Child Sites in the Activity Log for MainWP', 'mwp-al-ext' ); ?></span></h2>
						<div class="mainwp-postbox-actions-top"><p class="description"><?php esc_html_e( 'Use the below settings to add or remove child sites\' activity logs from the central activity log in the MainWP dashboard. The column on the left is a list of MainWP child sites that have the WP Activity Log plugin installed but their logs are not shown in the MainWP dashboard.', 'mwp-al-ext' ); ?></p></div>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<td>
											<div class="mwpal-wcs-container">
												<div id="mwpal-wcs">
													<p><?php esc_html_e( 'Child sites with WP Activity Log installed but not in the MainWP Activity Log', 'mwp-al-ext' ); ?></p>
													<div class="sites-container">
														<?php
														$disabled_sites = MWPAL_Extension\mwpal_extension()->settings->get_option( 'disabled-wsal-sites', array() );
														foreach ( $this->mwp_child_sites as $site ) :
															if ( isset( $disabled_sites[ $site['id'] ] ) ) :
																?>
																<span>
																	<input id="mwpal-wcs-site-<?php echo esc_attr( $site['id'] ); ?>" name="mwpal-wcs[]" value="<?php echo esc_attr( $site['id'] ); ?>" type="checkbox">
																	<label for="mwpal-wcs-site-<?php echo esc_attr( $site['id'] ); ?>"><?php echo esc_html( $site['name'] ); ?></label>
																</span>
																<?php
															endif;
														endforeach;
														?>
													</div>
												</div>
												<div id="mwpal-wcs-btns">
													<a href="javascript:;" class="button-primary" id="mwpal-wcs-add-btn"><?php esc_html_e( 'Add to Activity Log', 'mwp-al-ext' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
													<br>
													<a href="javascript:;" class="button-secondary" id="mwpal-wcs-remove-btn"><span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Remove', 'mwp-al-ext' ); ?></a>
												</div>
												<div id="mwpal-wcs-al">
													<p><?php esc_html_e( 'Child sites which have their activity log in the central MainWP activity logs', 'mwp-al-ext' ); ?></p>
													<div class="sites-container">
														<?php
														$selected_sites = array();
														foreach ( $this->mwp_child_sites as $site ) :
															if ( isset( $this->wsal_child_sites[ $site['id'] ] ) ) :
																$selected_sites[] = $site['id'];
																?>
																<span>
																	<input id="mwpal-wcs-al-site-<?php echo esc_attr( $site['id'] ); ?>" name="mwpal-wcs-al[]" value="<?php echo esc_attr( $site['id'] ); ?>" type="checkbox">
																	<label for="mwpal-wcs-al-site-<?php echo esc_attr( $site['id'] ); ?>"><?php echo esc_html( $site['name'] ); ?></label>
																</span>
																<?php
															endif;
														endforeach;
														$selected_sites = is_array( $selected_sites ) ? implode( ',', $selected_sites ) : false;
														?>
													</div>
													<input type="hidden" id="mwpal-wsal-child-sites" name="mwpal-wsal-child-sites" value="<?php echo esc_attr( $selected_sites ); ?>">
												</div>
											</div>
											<input type="button" class="button-primary" id="mwpal-wsal-sites-refresh" value="<?php esc_html_e( 'Refresh list of child sites', 'mwp-al-ext' ); ?>" />
											<div id="mwpal-wcs-refresh-message"  style="display:none;"  class="notice notice-info">
												<p><?php esc_html_e( 'Updating sites in the background. This can take a while, please do not navigate away from this page.', 'mwp-al-ext' ); ?> <span class="spinner is-active"></span></p>
												<?php
												printf(
													'<p>%1$s<span class="last-message-time">%2$s</span>%3$s</p>',
													esc_html( 'Last message recieved from backend at: ', 'mw-al-ext' ),
													esc_html( 'Just starting...', 'mw-al-ext' ),
													'<span class="spinner is-visible" style="float: none; margin:0 10px 0;"></span>'
												);
												?>
											</div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!-- List of Child Sites in the Activity Log for MainWP -->
				</div>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-primary button button-hero" value="<?php esc_attr_e( 'Save Settings', 'mwp-al-ext' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render Footer.
	 */
	public function footer() {
		do_action( 'mainwp-pagefooter-extensions', MWPAL_Extension\mwpal_extension()->get_child_file() );
	}

	/**
	 * Query events from all the child sites.
	 *
	 * @return void
	 */
	private function query_child_site_events() {
		// Check if the WSAL child sites option exists.
		$child_sites = MWPAL_Extension\mwpal_extension()->settings->get_wsal_child_sites();
		$server_ip   = MWPAL_Extension\mwpal_extension()->settings->get_server_ip(); // Get server IP.

		if ( ! empty( $child_sites ) && is_array( $child_sites ) ) {
			$sites_data        = array();
			$logged_retrieving = false; // Event 7711.
			$logged_ready      = false; // Event 7712.

			foreach ( $child_sites as $site_id => $child_site ) {
				// Get events count from native events DB.
				$occ_query = new \WSAL\MainWPExtension\Models\OccurrenceQuery();
				$occ_query->addCondition( 'site_id = %s ', $site_id ); // Set site id.
				$occ_count = (int) $occ_query->getAdapter()->Count( $occ_query );

				// If events are already present in the DB of a site, then no need to query from child site.
				if ( 0 !== $occ_count ) {
					continue;
				}

				if ( ! $logged_retrieving ) {
					// Extension has started retrieving.
					MWPAL_Extension\mwpal_extension()->alerts->trigger(
						7711,
						array(
							'mainwp_dash' => true,
							'Username'    => 'System',
							'ClientIP'    => ! empty( $server_ip ) ? $server_ip : false,
						)
					);
					$logged_retrieving = true;
				}

				// Post data for child sites.
				$post_data = array(
					'action'       => 'get_events',
					'events_count' => MWPAL_Extension\mwpal_extension()->settings->get_child_site_events(),
				);

				// Call to child sites to fetch WSAL events.
				$sites_data[ $site_id ] = apply_filters(
					'mainwp_fetchurlauthed',
					MWPAL_Extension\mwpal_extension()->get_child_file(),
					MWPAL_Extension\mwpal_extension()->get_child_key(),
					$site_id,
					'extra_excution',
					$post_data
				);

				if ( ! $logged_ready && isset( $sites_data[ $site_id ]->events ) ) {
					// Extension is ready after retrieving.
					MWPAL_Extension\mwpal_extension()->alerts->trigger(
						7712,
						array(
							'mainwp_dash' => true,
							'Username'    => 'System',
							'ClientIP'    => ! empty( $server_ip ) ? $server_ip : false,
						)
					);
					$logged_ready = true;
				}
			}

			if ( ! empty( $sites_data ) && is_array( $sites_data ) ) {
				// Get MainWP child sites.
				$mwp_sites = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_sites();

				foreach ( $sites_data as $site_id => $site_data ) {
					// If $site_data is array, then MainWP failed to fetch logs from the child site.
					if ( ! empty( $site_data ) && is_array( $site_data ) ) {
						// Search for the site data.
						$key = array_search( $site_id, array_column( $mwp_sites, 'id' ), false );

						if ( false !== $key && isset( $mwp_sites[ $key ] ) ) {
							// Extension is unable to retrieve events.
							MWPAL_Extension\mwpal_extension()->alerts->trigger(
								7710,
								array(
									'friendly_name' => $mwp_sites[ $key ]['name'],
									'site_url'      => $mwp_sites[ $key ]['url'],
									'site_id'       => $mwp_sites[ $key ]['id'],
									'mainwp_dash'   => true,
									'Username'      => 'System',
									'ClientIP'      => ! empty( $server_ip ) ? $server_ip : false,
								)
							);
						}
					} elseif ( empty( $site_data ) || ! isset( $site_data->events ) ) {
						continue;
					}

					MWPAL_Extension\mwpal_extension()->alerts->log_events( $site_data->events, $site_id );
					\WSAL\MainWPExtension\save_child_site_users( $site_id, $site_data->users );
				}
			}
		}
	}

	/**
	 * Get Extension's List Table Instance.
	 *
	 * @return AuditLogListView
	 */
	public function get_list_view() {
		// Set page arguments.
		if ( ! $this->page_args ) {
			$this->page_args = new \stdClass();

			// @codingStandardsIgnoreStart
			$this->page_args->page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : false;
			$this->page_args->site_id = MWPAL_Extension\mwpal_extension()->settings->get_view_site_id();

			// Order arguments.
			$this->page_args->order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : false;
			$this->page_args->order    = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : false;

			// Search arguments.
			$this->page_args->get_events     = ! empty( $_REQUEST['get-events'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['get-events'] ) ) : false;
			$this->page_args->search_term    = ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : false;
			$this->page_args->search_filters = ( isset( $_REQUEST['filters'] ) && is_array( $_REQUEST['filters'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['filters'] ) ) : false;
			// @codingStandardsIgnoreEnd
		}

		if ( is_null( $this->list_view ) ) {
			// Setup the view class name. This has been validated before this
			// point and can only be 'list' or 'grid'.
			$view_type = $this->detect_view_type();

			// if the requested view didn't match the view users last viewed
			// then update their preference.
			if ( $view_type !== $this->user_last_view ) {
				$view_type = ( in_array( $view_type, $this->supported_view_types(), true ) ) ? $view_type : 'list';
				update_user_meta( get_current_user_id(), 'almwp-selected-main-view', ( in_array( $view_type, $this->supported_view_types(), true ) ) ? $view_type : 'list' );
				$this->user_last_view = $view_type;
			}

			$view_class = "\WSAL\MainWPExtension\Views\AuditLog{$view_type}View";
			/**
			 * List view class name filter.
			 *
			 * @since 1.1
			 *
			 * @param string $view_class - List view class name.
			 */
			$view_class = apply_filters( 'mwpal_auditlog_list_view_class', $view_class );

			// Initialize the list view.
			$this->list_view = new $view_class( $this->page_args );
		}

		return $this->list_view;
	}

	/**
	 * Helper to store the views that are supported for the plugins lists.
	 *
	 * @method supported_view_types
	 * @since  1.4.0
	 * @return array
	 */
	public function supported_view_types() {
		return array(
			'list',
			'grid',
		);
	}

	/**
	 * Helper to get the current user selected view.
	 *
	 * @method detect_view_type
	 * @since  1.4.0
	 * @return string
	 */
	public function detect_view_type() {
		// First check if there is a GET/POST request for a specific view.
		if ( defined( 'DOING_AJAX' ) ) {
			$requested_view = ( isset( $_POST['view'] ) ) ? wp_unslash( filter_input( INPUT_POST, 'view', FILTER_SANITIZE_STRING ) ) : '';
		} else {
			$requested_view = ( isset( $_GET['view'] ) ) ? wp_unslash( filter_input( INPUT_GET, 'view', FILTER_SANITIZE_STRING ) ) : '';
		}

		// When there is no GET/POST view requested use the user value.
		if ( empty( $requested_view ) ) {
			$requested_view = $this->user_last_view;
		}

		// return the requested view. This is 'list' by default.
		return ( in_array( $requested_view, $this->supported_view_types(), true ) ) ? $requested_view : 'list';
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
			MWPAL_Extension\mwpal_extension()->settings->set_view_per_page( (int) $per_page_events );
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
			\WSAL\MainWPExtension\Event_Ref::config( 'stylePath', trailingslashit( MWPAL_BASE_DIR ) . 'assets/css/dist/wsal-ref.css' );
			\WSAL\MainWPExtension\Event_Ref::config( 'scriptPath', trailingslashit( MWPAL_BASE_DIR ) . 'assets/js/dist/wsal-ref.js' );

			echo '<!DOCTYPE html><html><head>';
			echo '<style type="text/css">';
			echo 'html, body { margin: 0; padding: 0; }';
			echo '</style>';
			echo '</head><body>';
			\WSAL\MainWPExtension\mwpal_r( $event_meta );
			echo '</body></html>';
			die;
		}
		die( esc_html__( 'Nonce verification failed.', 'mwp-al-ext' ) );
	}

	/**
	 * Refresh WSAL Child Sites
	 */
	public function refresh_child_sites() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Access denied.', 'mwp-al-ext' ) );
		}

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mwp-activitylog-nonce' ) ) {

			// get a passed run ID or get a new one.
			$run_id = ( isset( $_POST['mwpal_run_id'] ) ) ? filter_var( wp_unslash( $_POST['mwpal_run_id'] ), FILTER_SANITIZE_STRING ) : uniqid();
			$forced = ( isset( $_POST['mwpal_forced'] ) ) ? filter_var( wp_unslash( $_POST['mwpal_forced'] ), FILTER_VALIDATE_BOOLEAN ) : false;

			// Clear list of disabled sites.
			MWPAL_Extension\mwpal_extension()->settings->update_option( 'disabled-wsal-sites', '' );

			/*
			 * Check transient to see if we are in the middle of a run.
			 */
			$running_flag = get_transient( self::MWPAL_REFRESH_KEY );
			if ( false !== $running_flag && is_array( $running_flag ) ) {
				// verify this id matches the one we got passed.
				if ( isset( $running_flag['run_id'] ) && $running_flag['run_id'] !== $run_id ) {
					// didn't match id. Error if this is not 'forced'.
					if ( ! $forced ) {
						$error = new \WP_Error(
							'run_in_progress',
							esc_html__( 'There is a run in progress and the ID does not match the previously stored run ID: ', 'mwp-al-ext' ) . $running_flag['run_id'],
							$running_flag
						);
						wp_send_json_error( $error );
					}
				}
			} else {
				// since we don't have a workable array start a fresh one.
				$running_flag = array(
					'run_id'         => $run_id,
					'site_ids'       => array(),
					'disabled_sites' => array(),
				);
			}

			/*
			 * Get a list of site IDs that we will start working with.
			 */
			if ( ! empty( $running_flag['site_ids'] ) ) {
				$next_batch = array_slice( $running_flag['site_ids'], 0, 5 );
			} else {
				$mwp_child_sites  = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_sites(); // Get MainWP child sites.
				$wsal_child_sites = MWPAL_Extension\mwpal_extension()->settings->get_option( 'wsal-child-sites', array() ); // Get activity log sites.
				$disabled_sites   = (array) MWPAL_Extension\mwpal_extension()->settings->get_option( 'disabled-wsal-sites', array() ); // Get disabled WSAL sites.
				$wsal_site_ids    = array_merge( array_keys( $wsal_child_sites ), array_keys( $disabled_sites ) ); // Merge arrays active & disabled WSAL child sites.
				$mwp_site_ids     = array_column( $mwp_child_sites, 'id' ); // Get MainWP child site ids.
				$diff             = array_diff( $mwp_site_ids, $wsal_site_ids ); // Compute the difference.

				$running_flag['site_ids']       = $diff;
				$running_flag['disabled_sites'] = $disabled_sites;
				$next_batch                     = array_slice( $diff, 0, 5 );
			}

			if ( ! empty( $next_batch ) ) {
				foreach ( $next_batch as $index => $site_id ) {
					// Post data for child site.
					$post_data = array( 'action' => 'check_wsal' );

					// Call to child sites to check if WSAL is installed on them or not.
					$response = apply_filters(
						'mainwp_fetchurlauthed',
						MWPAL_Extension\mwpal_extension()->get_child_file(),
						MWPAL_Extension\mwpal_extension()->get_child_key(),
						$site_id,
						'extra_excution',
						$post_data
					);

					if ( is_array( $response ) && isset( $response['error'] ) ) {
						// Some error occurred. This might be connectivity
						// problem or it could be sites added/removed from
						// MainWP. Skip this itteration early.
						MWPAL_Extension\mwpal_extension()->log( esc_html__( 'Error when refreshing child sites: ', 'mwp-al-ext' ) . $response['error'] );
						continue;
					} elseif ( is_array( $response ) && isset( $response['wsal_installed'] ) ) {
						// wsal is installed, for back compat reasons cast the
						// array to an object before storing.
						$response = (object) $response;
					}

					// Cast reponse to an araay to avoid incomplete object PHP error.
					$response = (array) $response;

					// Check if WSAL is installed on the child site.
					if ( isset( $response['wsal_installed'] ) && true === $response['wsal_installed'] ) {
						$disabled_sites[ $site_id ]                 = $response;
						$running_flag['disabled_sites'][ $site_id ] = $response;
					}
				}
			}
			// Update disabled sites.
			MWPAL_Extension\mwpal_extension()->settings->update_option( 'disabled-wsal-sites', ( isset( $running_flag['disabled_sites'] ) ) ? $running_flag['disabled_sites'] : array() );
			$running_flag['site_ids'] = array_diff( $running_flag['site_ids'], $next_batch );

			// Send a response message. The JS frontend should know how to deal
			// with the reply.
			if ( ! empty( $running_flag['site_ids'] ) ) {
				// cache the current progress in a transient.
				set_transient( self::MWPAL_REFRESH_KEY, $running_flag, HOUR_IN_SECONDS );
			} else {
				// set the flag as complete to pass back and delete the cache.
				$running_flag['complete'] = true;
				delete_transient( self::MWPAL_REFRESH_KEY );
			}
			wp_send_json_success( $running_flag );
		}
		die( esc_html__( 'Nonce verification failed.', 'mwp-al-ext' ) );
	}

	/**
	 * Update Active WSAL Sites.
	 */
	public function update_active_wsal_sites() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access denied.', 'mwp-al-ext' ),
				)
			);
			exit();
		}

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mwp-activitylog-nonce' ) ) {
			// Get $_POST data.
			$transfer_action = isset( $_POST['transferAction'] ) ? sanitize_text_field( wp_unslash( $_POST['transferAction'] ) ) : false;
			$active_sites    = isset( $_POST['activeSites'] ) ? sanitize_text_field( wp_unslash( $_POST['activeSites'] ) ) : false;
			$active_sites    = ! empty( $active_sites ) ? explode( ',', $active_sites ) : array();
			$request_sites   = isset( $_POST['requestSites'] ) ? sanitize_text_field( wp_unslash( $_POST['requestSites'] ) ) : false;
			$request_sites   = explode( ',', $request_sites );

			if ( 'remove-sites' === $transfer_action && ! empty( $request_sites ) ) {
				foreach ( $request_sites as $site ) {
					$key = array_search( $site, $active_sites, true );
					if ( false !== $key ) {
						// get wsal status from the remote site.
						$site_status = $this->check_remote_wsal_status( (int) $site );
						if ( ! isset( $site_status->error ) ) {
							unset( $active_sites[ $key ] );
							// remove from the active sites list.
							MWPAL_Extension\mwpal_extension()->settings->set_wsal_child_sites( $active_sites );
						}
					}
				}

				echo wp_json_encode(
					array(
						'success'     => true,
						'activeSites' => implode( ',', $active_sites ),
					)
				);
			} elseif ( 'add-sites' === $transfer_action && ! empty( $request_sites ) ) {
				foreach ( $request_sites as $site ) {
					$key = array_search( $site, $active_sites, true );
					if ( false === $key ) {
						$site_status = (array) $this->check_remote_wsal_status( (int) $site );
						if ( ! isset( $site_status['error'] ) && ( isset( $site_status['wsal_installed'] ) && $site_status['wsal_installed'] ) ) {
							if ( ! in_array( $site, $active_sites, true ) ) {
								$active_sites[] = $site;
							}
							MWPAL_Extension\mwpal_extension()->settings->set_wsal_child_sites( $active_sites );
						}
					}
				}

				echo wp_json_encode(
					array(
						'success'     => true,
						'activeSites' => implode( ',', $active_sites ),
					)
				);
			} else {
				echo wp_json_encode(
					array(
						'success' => false,
						'message' => esc_html__( 'Invalid action.', 'mwp-al-ext' ),
					)
				);
			}
		} else {
			echo wp_json_encode(
				array(
					'success' => false,
					'message' => esc_html__( 'Access denied.', 'mwp-al-ext' ),
				)
			);
		}
		exit();
	}

	/**
	 * Retrieve Events Manually.
	 *
	 * To retrieve fresh logs, just delete the events of
	 * the site and refresh the page.
	 */
	public function retrieve_events_manually() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Access denied.', 'mwp-al-ext' ) );
		}

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mwp-activitylog-nonce' ) ) {
			MWPAL_Extension\mwpal_extension()->alerts->retrieve_events_manually();
			die();
		}
		die( esc_html__( 'Nonce verification failed.', 'mwp-al-ext' ) );
	}

	/**
	 * Infinite Scroll Events AJAX Hanlder.
	 */
	public function infinite_scroll_events() {
		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			die( esc_html__( 'Access Denied', 'mwp-al-ext' ) );
		}

		// Verify nonce.
		if ( isset( $_POST['mwpal_viewer_security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mwpal_viewer_security'] ) ), 'mwp-activitylog-nonce' ) ) {
			// Get $_POST arguments.
			$paged = isset( $_POST['page_number'] ) ? sanitize_text_field( wp_unslash( $_POST['page_number'] ) ) : 0;

			// Query events.
			$events_query = $this->get_list_view()->query_events( $paged );

			if ( ! empty( $events_query['items'] ) ) {
				foreach ( $events_query['items'] as $event ) {
					$this->get_list_view()->single_row( $event );
				}
			}
			exit();
		} else {
			die( esc_html__( 'Nonce verification failed.', 'mwp-al-ext' ) );
		}
	}

	/**
	 * Add submenu on manage sites
	 * @param  array $subPage
	 * @return array
	 */
	public function managesites_subpage( $subPage ) {
		$subPage[] = array(
			'title' => __( 'Activity Logs', 'mwp-al-ext' ),
			'slug' => 'ActivityLog',
			'sitetab' => true,
			'menu_hidden' => true,
			'callback' => array( $this, 'managesites_activity_logs' )
		);
		return $subPage;
	}

	/**
	 * Managesites show activity logs
	 * @return empty
	 */
	public function managesites_activity_logs() {

		/**
		 * Remove child sites filter.
		 */
		add_filter( 'pre_option_' . MWPAL_OPT_PREFIX . 'wsal-child-sites', function() {
			return array();
		} );

		// Get current site ID.
		$_REQUEST['mwpal-site-id'] = isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : 0;

		// Prepare items
		$this->get_list_view()->prepare_items();

		/**
		 * Do action before the view renders.
		 */
		do_action( 'mainwp-pageheader-sites', 'ActivityLog' );

		// Display events table.
		$this->get_list_view()->display();

		/**
		 * Do action before the view renders.
		 */
		do_action( 'mainwp-pagefooter-sites', 'ActivityLog' );

		return;
	}

	/**
	 * Enqueue script for extensions page.
	 */
	public function mwpal_extensions_print_scripts() {
		// Only run the function on mainWP Extensions page.
		// @codingStandardsIgnoreStart
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Current page.
		// @codingStandardsIgnoreEnd

		if ( 'Extensions' !== $page ) {
			return;
		}

		// if this is the premium plugin then bail early to stop the button for
		// 'Upgrade to Premium' showing on the extensiton.
		if ( function_exists( 'almainwp_fs' ) && almainwp_fs()->is__premium_only() ) {
			return;
		}
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				var mwpalExtension = $( 'div[extension-slug$="/activity-log-mainwp.php"]' );
				mwpalExtension.append( '<div class="ui middle aligned extra content"><a href="<?php echo esc_url( 'https://wpactivitylog.com/extensions/mainwp-activity-log/pricing/?utm_source=plugin&utm_medium=referral&utm_campaign=AL4MWP&utm_content=extensions+upgrade' ); ?>" target="_blank" class="ui mini right floated button"><?php echo __( 'Upgrade to Premium', 'mwp-al-ext' ); ?></a></div>' );
			} );
		</script>
		<?php
	}

	/**
	 * Dismissed Upgrade advert
	 * @return json
	 */
	public function mwpal_advert_dismissed() {
		// Verify mwp nonce
		check_ajax_referer( 'mwp-activitylog-nonce', 'mwp_nonce' );

		$notice_type = filter_input( INPUT_POST, 'mwpal_notice_type', FILTER_SANITIZE_STRING );
		if ( null !== $notice_type && false !== $notice_type ) {
			$dissmissed_notice = set_transient( $notice_type, true, MONTH_IN_SECONDS );
		} else {
			// Set advert transient
			$dissmissed_notice = set_transient( 'mwpal-is-advert-dismissed', true, MONTH_IN_SECONDS );
		}

		// Send ajax response
		wp_send_json(
			array(
				'status' => $dissmissed_notice
			)
		);
		die();
	}

	/**
	 * Dismiss a user level notice. Stores a user meta value with the data.
	 *
	 * @since  1.4.0
	 * @return void
	 */
	public function user_notice_dismissed() {
		// Verify mwp nonce.
		check_ajax_referer( 'search-script-nonce', 'nonce' );

		$dissmissed_notice = false;
		$notice_type       = filter_input( INPUT_POST, 'notice', FILTER_SANITIZE_STRING );
		if ( null !== $notice_type && false !== $notice_type && in_array( $notice_type, $this->get_allowed_notices(), true ) ) {
			$dissmissed_notice = update_user_meta( get_current_user_id(), "mwpal-is-notice-dismissed-{$notice_type}", true );
		}

		// Send ajax response.
		wp_send_json(
			array(
				'status' => $dissmissed_notice,
			)
		);
		die();
	}

	/**
	 * Gets a list of allowed notice types.
	 *
	 * @method get_allowed_notices
	 * @since  1.4.0
	 * @return array
	 */
	public function get_allowed_notices() {
		return (array) apply_filters(
			'mwp_allowed_notices',
			array(
				'search-filters-changed',
			)
		);
	}

	/**
	 * Makes an exteral call to check if WSAL is installed.
	 *
	 * @method check_wsal_status
	 * @since  1.2
	 * @param  integer $site_id a site ID to try fetch status from.
	 * @return bool|stdClass
	 */
	private function check_remote_wsal_status( $site_id = 0 ) {

		// Fail early if there is no id to work with.
		if ( 0 === $site_id || ! is_int( $site_id ) ) {
			return false;
		}

		// action we are checking.
		$post_data = array( 'action' => 'check_wsal' );

		// Call to child site to check if WSAL is installed or not.
		// NOTE: cast to an object for back compat before possible storing.
		$response = (object) apply_filters(
			'mainwp_fetchurlauthed',
			MWPAL_Extension\mwpal_extension()->get_child_file(),
			MWPAL_Extension\mwpal_extension()->get_child_key(),
			$site_id,
			'extra_excution',
			$post_data
		);
		return $response;
	}
}
