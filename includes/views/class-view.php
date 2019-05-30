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
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'mainwp_left_menu_sub', array( $this, 'mwp_left_menu_sub' ), 10, 1 );
		add_filter( 'mainwp_subleft_menu_sub', array( $this, 'mwp_sub_menu_dropdown' ), 10, 1 );
		add_action( 'mainwp_extensions_top_header_after_tab', array( $this, 'activitylog_settings_tab' ), 10, 1 );
		add_action( 'mainwp-pageheader-extensions', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'mainwp-pagefooter-extensions', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_auditlog_form_submission' ) );
		add_action( 'wp_ajax_set_per_page_events', array( $this, 'set_per_page_events' ) );
		add_action( 'wp_ajax_metadata_inspector', array( $this, 'metadata_inspector' ) );
		add_action( 'wp_ajax_refresh_child_sites', array( $this, 'refresh_child_sites' ) );
		add_action( 'wp_ajax_update_active_wsal_sites', array( $this, 'update_active_wsal_sites' ) );
		add_action( 'wp_ajax_retrieve_events_manually', array( $this, 'retrieve_events_manually' ) );

		if ( MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll() ) {
			add_action( 'wp_ajax_mwpal_infinite_scroll_events', array( $this, 'infinite_scroll_events' ) );
		}

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
		$mwp_dropdown_menu[ MWPAL_EXTENSION_NAME ] = array(
			array(
				__( 'Extension Settings', 'mwp-al-ext' ),
				'admin.php?page=' . MWPAL_EXTENSION_NAME . '&tab=settings',
				'',
			),
		);
		return $mwp_dropdown_menu;
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

		$settings_url_args = array(
			'page' => MWPAL_EXTENSION_NAME,
			'tab'  => 'settings',
		);
		$settings_tab_url  = add_query_arg( $settings_url_args, admin_url( 'admin.php' ) );
		?>
		<a class="nav-tab pos-nav-tab echo <?php echo ( 'settings' === $this->current_tab ) ? 'nav-tab-active' : false; ?>" href="<?php echo esc_url( $settings_tab_url ); ?>">
			<?php esc_html_e( 'Extension Settings', 'mwp-al-ext' ); ?>
		</a>
		<?php
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
		} elseif ( MWPAL_EXTENSION_NAME !== $page ) {
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

		wp_register_script(
			'mwpal-view-script',
			trailingslashit( MWPAL_BASE_URL ) . 'assets/js/dist/index.js',
			array( 'jquery' ),
			filemtime( trailingslashit( MWPAL_BASE_DIR ) . 'assets/js/dist/index.js' ),
			false
		);

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
			'infiniteScroll' => MWPAL_Extension\mwpal_extension()->settings->is_infinite_scroll(),
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

		if ( isset( $_GET['_wpnonce'] ) && 'activity-log' === $this->current_tab ) {
			// Verify nonce for security.
			check_admin_referer( 'bulk-activity-logs' );

			// Site id.
			$site_id = isset( $_GET['mwpal-site-id'] ) ? sanitize_text_field( wp_unslash( $_GET['mwpal-site-id'] ) ) : false;

			// Check for dashboard.
			if ( 'dashboard' !== $site_id ) {
				$site_id = (int) $site_id;
			}

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
		} elseif ( isset( $_POST['_wpnonce'] ) && isset( $_POST['submit'] ) && 'settings' === $this->current_tab ) {
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

			// Set options.
			MWPAL_Extension\mwpal_extension()->settings->set_events_type_nav( $events_nav_type );
			MWPAL_Extension\mwpal_extension()->settings->set_timezone( $timezone );
			MWPAL_Extension\mwpal_extension()->settings->set_type_username( $type_username );
			MWPAL_Extension\mwpal_extension()->settings->set_child_site_events( $child_site_events );
			MWPAL_Extension\mwpal_extension()->settings->set_events_frequency( $events_frequency );
			MWPAL_Extension\mwpal_extension()->settings->set_events_global_sync( $events_global_sync );
			MWPAL_Extension\mwpal_extension()->settings->set_columns( $columns );
			MWPAL_Extension\mwpal_extension()->settings->set_wsal_child_sites( ! empty( $wsal_child_sites ) ? explode( ',', $wsal_child_sites ) : false );
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

		if ( MWPAL_Extension\mwpal_extension()->is_child_enabled() ) {
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
		$this->get_list_view()->prepare_items();
		$site_id = MWPAL_Extension\mwpal_extension()->settings->get_view_site_id();
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
		// @codingStandardsIgnoreStart
		$mwp_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // Admin WSAL Page.
		// @codingStandardsIgnoreEnd
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

					<div id="mwpal-setting-contentbox-3" class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'List of Child Sites in the Activity Log for MainWP', 'mwp-al-ext' ); ?></span></h2>
						<div class="mainwp-postbox-actions-top"><p class="description"><?php esc_html_e( 'Use the below settings to add or remove child sites\' activity logs from the central activity log in the MainWP dashboard. The column on the left is a list of MainWP child sites that have the WP Security Audit Log plugin installed but their logs are not shown in the MainWP dashboard.', 'mwp-al-ext' ); ?></p></div>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<td>
											<div class="mwpal-wcs-container">
												<div id="mwpal-wcs">
													<p><?php esc_html_e( 'Child sites with WP Security Audit Log installed but not in the MainWP Activity Log', 'mwp-al-ext' ); ?></p>
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

				foreach ( $sites_data as $site_id => $site_events ) {
					// If $site_events is array, then MainWP failed to fetch logs from the child site.
					if ( ! empty( $site_events ) && is_array( $site_events ) ) {
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
					} elseif ( empty( $site_events ) || ! isset( $site_events->events ) ) {
						continue;
					}
					MWPAL_Extension\mwpal_extension()->alerts->log_events( $site_events->events, $site_id );
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
		// Set page arguments.
		if ( ! $this->page_args ) {
			$this->page_args = new \stdClass();

			// @codingStandardsIgnoreStart
			$this->page_args->page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : false;
			$this->page_args->site_id = MWPAL_Extension\mwpal_extension()->settings->get_view_site_id();

			// Order arguments.
			$this->page_args->order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : false;
			$this->page_args->order    = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : false;
			// @codingStandardsIgnoreEnd
		}

		if ( is_null( $this->list_view ) ) {
			$this->list_view = new AuditLogListView( MWPAL_Extension\mwpal_extension(), $this->page_args );
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
			$mwp_child_sites  = MWPAL_Extension\mwpal_extension()->settings->get_mwp_child_sites(); // Get MainWP child sites.
			$wsal_child_sites = MWPAL_Extension\mwpal_extension()->settings->get_option( 'wsal-child-sites', array() ); // Get activity log sites.
			$disabled_sites   = MWPAL_Extension\mwpal_extension()->settings->get_option( 'disabled-wsal-sites', array() ); // Get disabled WSAL sites.
			$wsal_site_ids    = array_merge( array_keys( $wsal_child_sites ), array_keys( $disabled_sites ) ); // Merge arrays active & disabled WSAL child sites.
			$mwp_site_ids     = array_column( $mwp_child_sites, 'id' ); // Get MainWP child site ids.
			$diff             = array_diff( $mwp_site_ids, $wsal_site_ids ); // Compute the difference.

			if ( ! empty( $diff ) ) {
				foreach ( $diff as $index => $site_id ) {
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

					// Check if WSAL is installed on the child site.
					if ( true === $response->wsal_installed ) {
						$disabled_sites[ $site_id ] = $response;
					}
				}

				// Update disabled sites.
				MWPAL_Extension\mwpal_extension()->settings->update_option( 'disabled-wsal-sites', $disabled_sites );
			}
			die();
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
						unset( $active_sites[ $key ] );
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
						$active_sites[] = $site;
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
	 *
	 * @since 3.3.1.1
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
}
