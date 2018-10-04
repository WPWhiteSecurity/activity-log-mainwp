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
		add_action( 'mainwp_extensions_top_header_after_tab', array( $this, 'activitylog_settings_tab' ), 10, 1 );
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
	 * Add Activity Log Settings Tab.
	 *
	 * @param string $current_page – Path of the extension.
	 */
	public function activitylog_settings_tab( $current_page ) {
		$activity_log = basename( $current_page, '.php' );

		if ( 'mainwp-activity-log-extension' !== $activity_log ) {
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

		if ( 'settings' === $this->current_tab ) {
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

		wp_enqueue_script( 'jquery' );

		if ( 'settings' === $this->current_tab ) {
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
			'ajaxURL'     => admin_url( 'admin-ajax.php' ),
			'scriptNonce' => wp_create_nonce( 'mwp-activitylog-nonce' ),
			'currentTab'  => $this->current_tab,
			'selectSites' => __( 'Select Child Site(s)', 'mwp-al-ext' ),
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
		} elseif ( isset( $_POST['_wpnonce'] ) && isset( $_POST['submit'] ) && 'settings' === $this->current_tab ) {
			// Verify nonce for security.
			check_admin_referer( 'mwpal-settings-nonce' );

			// Get form options.
			$timezone          = isset( $_POST['timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['timezone'] ) ) : false;
			$type_username     = isset( $_POST['type_username'] ) ? sanitize_text_field( wp_unslash( $_POST['type_username'] ) ) : false;
			$child_site_events = isset( $_POST['child-site-events'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['child-site-events'] ) ) : false;
			$events_frequency  = isset( $_POST['events-frequency'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['events-frequency'] ) ) : false;
			// @codingStandardsIgnoreStart
			$columns          = isset( $_POST['columns'] ) ? array_map( 'sanitize_text_field', $_POST['columns'] ) : false;
			$wsal_child_sites = isset( $_POST['mwpal-wsal-child-sites'] ) ? array_map( 'sanitize_text_field', $_POST['mwpal-wsal-child-sites'] ) : false;
			// @codingStandardsIgnoreEnd

			$this->activity_log->settings->set_timezone( $timezone );
			$this->activity_log->settings->set_type_username( $type_username );
			$this->activity_log->settings->set_child_site_events( $child_site_events );
			$this->activity_log->settings->set_events_frequency( $events_frequency );
			$this->activity_log->settings->set_columns( $columns );
			$this->activity_log->settings->set_wsal_child_sites( $wsal_child_sites );
		}
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
		$this->mwp_child_sites  = $this->activity_log->settings->get_mwp_child_sites(); // Get MainWP child sites.
		$this->wsal_child_sites = $this->activity_log->settings->get_wsal_child_sites(); // Get child sites with WSAL installed.
		$this->query_child_site_events(); // Query events from child sites with WSAL.
		$site_id = $this->activity_log->settings->get_view_site_id();

		if ( $this->activity_log->is_child_enabled() ) {
			$this->get_list_view()->prepare_items();
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
										<th scope="row"><label for="utc"><?php esc_html_e( 'Events Timestamp', 'wp-security-audit-log' ); ?></label></th>
										<td>
											<fieldset>
												<?php $timezone = $this->activity_log->settings->get_timezone(); ?>
												<label for="utc">
													<input type="radio" name="timezone" id="utc" style="margin-top: -2px;" <?php checked( $timezone, 'utc' ); ?> value="utc">
													<?php esc_html_e( 'UTC', 'wp-security-audit-log' ); ?>
												</label>
												<br/>
												<label for="timezone">
													<input type="radio" name="timezone" id="timezone" style="margin-top: -2px;" <?php checked( $timezone, 'wp' ); ?> value="wp">
													<?php esc_html_e( 'Timezone configured on this WordPress website', 'wp-security-audit-log' ); ?>
												</label>
											</fieldset>
										</td>
									</tr>
									<!-- Alerts Timestamp -->

									<tr>
										<th scope="row"><label for="column_username"><?php esc_html_e( 'User Information in Audit Log', 'wp-security-audit-log' ); ?></label></th>
										<td>
											<fieldset>
												<?php $type_username = $this->activity_log->settings->get_type_username(); ?>
												<label for="column_username">
													<input type="radio" name="type_username" id="column_username" style="margin-top: -2px;" <?php checked( $type_username, 'username' ); ?> value="username">
													<span><?php esc_html_e( 'WordPress Username', 'wp-security-audit-log' ); ?></span>
												</label>
												<br/>
												<label for="columns_first_last_name">
													<input type="radio" name="type_username" id="columns_first_last_name" style="margin-top: -2px;" <?php checked( $type_username, 'first_last_name' ); ?> value="first_last_name">
													<span><?php esc_html_e( 'First Name & Last Name', 'wp-security-audit-log' ); ?></span>
												</label>
												<br/>
												<label for="columns_display_name">
													<input type="radio" name="type_username" id="columns_display_name" style="margin-top: -2px;" <?php checked( $type_username, 'display_name' ); ?> value="display_name">
													<span><?php esc_html_e( 'Configured Public Display Name', 'wp-security-audit-log' ); ?></span>
												</label>
											</fieldset>
										</td>
									</tr>
									<!-- Select type of name -->

									<tr>
										<th><label for="columns"><?php esc_html_e( 'Activity Log Columns Selection', 'wp-security-audit-log' ); ?></label></th>
										<td>
											<fieldset>
												<?php $columns = $this->activity_log->settings->get_columns(); ?>
												<?php foreach ( $columns as $key => $value ) { ?>
													<label for="columns">
														<input type="checkbox" name="columns[<?php echo esc_attr( $key ); ?>]" id="<?php echo esc_attr( $key ); ?>" class="sel-columns" style="margin-top: -2px;"
															<?php checked( $value, '1' ); ?> value="1">
														<?php if ( 'alert_code' === $key ) : ?>
															<span><?php esc_html_e( 'Event ID', 'wp-security-audit-log' ); ?></span>
														<?php elseif ( 'type' === $key ) : ?>
															<span><?php esc_html_e( 'Severity', 'wp-security-audit-log' ); ?></span>
														<?php elseif ( 'date' === $key ) : ?>
															<span><?php esc_html_e( 'Date & Time', 'wp-security-audit-log' ); ?></span>
														<?php elseif ( 'username' === $key ) : ?>
															<span><?php esc_html_e( 'User', 'wp-security-audit-log' ); ?></span>
														<?php elseif ( 'source_ip' === $key ) : ?>
															<span><?php esc_html_e( 'Source IP Address', 'wp-security-audit-log' ); ?></span>
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
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'Extension Settings', 'mwp-al-ext' ); ?></span></h2>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="child-site-events"><?php esc_html_e( 'Events to Retrieve from Child Sites', 'wp-security-audit-log' ); ?></label></th>
										<td>
											<fieldset>
												<?php $child_site_events = $this->activity_log->settings->get_child_site_events(); ?>
												<input type="number" id="child-site-events" name="child-site-events" value="<?php echo esc_attr( $child_site_events ); ?>" />
											</fieldset>
										</td>
									</tr>

									<tr>
										<th scope="row"><label for="events-frequency"><?php esc_html_e( 'Events Frequency', 'wp-security-audit-log' ); ?></label></th>
										<td>
											<fieldset>
												<?php $events_frequency = $this->activity_log->settings->get_events_frequency(); ?>
												<input type="number" id="events-frequency" name="events-frequency" value="<?php echo esc_attr( $events_frequency ); ?>" />
												<?php esc_html_e( 'hours', 'mwp-al-ext' ); ?>
											</fieldset>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<!-- Extension Settings -->

					<div id="mwpal-setting-contentbox-3" class="postbox">
						<h2 class="hndle ui-sortable-handle"><span><i class="fa fa-cog"></i> <?php esc_html_e( 'Child Sites Settings', 'mwp-al-ext' ); ?></span></h2>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="child-site-events"><?php esc_html_e( 'Active WSAL Child Sites', 'wp-security-audit-log' ); ?></label></th>
										<td>
											<select name="mwpal-wsal-child-sites[]" id="mwpal-wsal-child-sites" multiple="multiple">
												<?php foreach ( $this->mwp_child_sites as $site ) : ?>
													<option value="<?php echo esc_attr( $site['id'] ); ?>" <?php echo isset( $this->wsal_child_sites[ $site['id'] ] ) ? 'selected' : false; ?>>
														<?php echo esc_html( $site['name'] ); ?>
													</option>
												<?php endforeach; ?>
											</select>
											<br />
											<br />
											<input type="button" class="button-primary" id="mwpal-wsal-sites-refresh" value="<?php esc_html_e( 'Refresh Child Sites', 'mwp-al-ext' ); ?>" />
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
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
		do_action( 'mainwp-pagefooter-extensions', $this->activity_log->get_child_file() );
	}

	/**
	 * Query events from all the child sites.
	 *
	 * @return void
	 */
	private function query_child_site_events() {
		// Check if the WSAL child sites option exists.
		$child_sites = $this->activity_log->settings->get_wsal_child_sites();

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
					'events_count' => $this->activity_log->settings->get_child_site_events(),
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
