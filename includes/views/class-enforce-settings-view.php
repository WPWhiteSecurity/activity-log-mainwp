<?php
/**
 * Enforce settings view.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Views;

use WSAL\MainWPExtension as MWPAL_Extension;
use function WSAL\MainWPExtension\mwpal_extension;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enforce settings view class.
 */
class Enforce_Settings_View {

	public static $tab_id = 'enforce-settings';

	/**
	 * Allowed HTML tags for the login page notification text.
	 *
	 * @var \array[][]
	 */
	private $allowed_tags = array(
		'a' => array(
			'href'   => array(),
			'title'  => array(),
			'target' => array(),
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_filter( 'mwpal_extension_tabs', array( $this, 'add_tab' ), 10, 2 );
		// For MainWP v4 or later.
		add_filter( 'mwpal_page_navigation', array( $this, 'add_tab_v4plus' ), 10, 1 );

		add_action( 'mainwp_pageheader_extensions', array( $this, 'enqueue_styles' ), 20 );
		add_action( 'mainwp_pagefooter_extensions', array( $this, 'enqueue_scripts' ), 20 );

		if ( \version_compare( MWPAL_Extension\get_mainwp_version(), '4.0-beta', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
		} else {
			add_action( 'mainwp_after_header', array( $this, 'display_admin_notices' ) );
		}
	}

	/**
	 * Add tab to the extension tabs.
	 *
	 * @param array $extension_tabs - Extension tabs array.
	 * @param array $extension_url -  URL of the extension.
	 *
	 * @return array
	 */
	public function add_tab( $extension_tabs, $extension_url ) {
		$position                = array_search( 'settings', $extension_tabs );
		$result                  = array_splice( $extension_tabs, 0, $position - 1 );
		$result[ self::$tab_id ] = array(
			'name'   => __( 'Child Sites Activity Log Settings', 'mwp-al-ext' ),
			'link'   => add_query_arg( 'tab', self::$tab_id, $extension_url ),
			'render' => array( $this, 'render' ),
			'save'   => array( $this, 'save' ),
		);
		$result                  += array_splice( $extension_tabs, $position - 1 );

		return $result;
	}

	/**
	 * Add tab to the extension tabs for MainWP v4 or later.
	 *
	 * @param array $mwpal_tabs - Extension tabs array.
	 *
	 * @return array
	 */
	public function add_tab_v4plus( $mwpal_tabs ) {
		$extension_url = add_query_arg( 'page', MWPAL_EXTENSION_NAME, admin_url( 'admin.php' ) );

		$position = count( $mwpal_tabs ) - 1;
		$result   = array_splice( $mwpal_tabs, 0, $position );
		$result[] = array(
			'title'  => __( 'Child Sites Activity Log Settings', 'mwp-al-ext' ),
			'href'   => add_query_arg( 'tab', self::$tab_id, $extension_url ),
			'active' => self::$tab_id === MWPAL_Extension\mwpal_extension()->extension_view->get_current_tab(),
		);

		$result = array_merge( $result, array_splice( $mwpal_tabs, $position ) );

		return $result;
	}

	/**
	 * Renders the content of the tab.
	 */
	public function render() {

		$settings         = MWPAL_Extension\mwpal_extension()->settings;
		$mwp_sites        = $settings->get_mwp_child_sites();
		$wsal_child_sites = $settings->get_wsal_child_sites();

		$enforce_settings_on_subsites = $settings->get_enforce_settings_on_subsites();
		$sites_with_enforced_settings = [];
		if ( 'some' === $enforce_settings_on_subsites ) {
			$sites_with_enforced_settings = $settings->get_sites_with_enforced_settings();
		}

		$enforced_settings    = $settings->get_enforced_child_sites_settings();
		$pruning_date_enabled = array_key_exists( 'pruning_enabled', $enforced_settings ) ? $enforced_settings['pruning_enabled'] : 'no';
		$pruning_date         = 12;
		$pruning_date_unit    = 'months';
		if ( 'yes' === $pruning_date_enabled ) {
			$pruning_date      = array_key_exists( 'pruning_date', $enforced_settings ) ? $enforced_settings['pruning_date'] : 12;
			$pruning_date_unit = array_key_exists( 'pruning_unit', $enforced_settings ) ? $enforced_settings['pruning_unit'] : 'months';
		}

		$selected_events = ( array_key_exists( 'disabled_events', $enforced_settings ) && ! empty( $enforced_settings['disabled_events'] ) ) ? array_map( 'intval', explode( ',', $enforced_settings['disabled_events'] ) ) : [];

		$incognito_mode_enabled = array_key_exists( 'incognito_mode_enabled', $enforced_settings ) ? $enforced_settings['incognito_mode_enabled'] : 'no';

		$login_page_notification_enabled = array_key_exists( 'login_notification_enabled', $enforced_settings ) ? $enforced_settings['login_notification_enabled'] : 'no';

		// login page notification text
		$default_login_text           = __( 'For security and auditing purposes, a record of all of your logged-in actions and changes within the WordPress dashboard will be recorded in an activity log with the <a href="https://wpactivitylog.com/?utm_source=plugin&utm_medium=referral&utm_campaign=WSAL&utm_content=settings+pages" target="_blank">WP Activity Log plugin</a>. The audit log also includes the IP address where you accessed this site from.', 'mwp-al-text' );
		$login_page_notification_text = array_key_exists( 'login_notification_text', $enforced_settings ) ? $enforced_settings['login_notification_text'] : $default_login_text;

		?>
        <h2><?php esc_html_e( 'Enforce settings on child sites', 'mwp-al-ext' ); ?></h2>
        <p><?php esc_html_e( 'Use the below setting to specify on which of this child sites you’d like to enforce the configured activity log settings.', 'mwp-al-ext' ); ?></p>

        <form method="post" id="mwpal-child-sites-settings">
			<?php wp_nonce_field( 'mwpal-enforce-child-sites-settings-nonce' ); ?>
            <table class="form-table wsal-tab">
                <tbody>
                <tr>
                    <th>
                        <label for="enforce_settings_on_subsites"><?php esc_html_e( 'Enforce these settings on', 'mwp-al-ext' ); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label for="enforce_settings_disabled">
                                <input type="radio" name="enforce_settings_on_subsites" id="enforce_settings_disabled"
                                       style="margin-top: -2px;" <?php checked( $enforce_settings_on_subsites, 'none' ); ?>
                                       value="none">
                                <span><?php esc_html_e( 'Do not enforce any settings', 'mwp-al-ext' ); ?></span>
                            </label>
                            <br/>
                            <label for="enforce_settings_all">
                                <input type="radio" name="enforce_settings_on_subsites" id="enforce_settings_all"
                                       style="margin-top: -2px;" <?php checked( $enforce_settings_on_subsites, 'all' ); ?>
                                       value="all">
                                <span><?php esc_html_e( 'All child sites connected to the central activity log', 'mwp-al-ext' ); ?></span>
                            </label>
                            <br/>
                            <label for="enforce_settings_some">
                                <input type="radio" name="enforce_settings_on_subsites" id="enforce_settings_some"
                                       style="margin-top: -2px;" <?php checked( $enforce_settings_on_subsites, 'some' ); ?>
                                       value="some">
                                <span><?php esc_html_e( 'Only the following child sites', 'mwp-al-ext' ); ?></span>
                            </label>
							<?php
							$sites_with_enforced_settings_mapped = [];
							foreach ( $sites_with_enforced_settings as $site_id ) {
								if ( array_key_exists( $site_id, $wsal_child_sites ) ) {
									$sites_with_enforced_settings_mapped[ $site_id ] = $wsal_child_sites[ $site_id ];
								}
							}

							View::render_sites_selection_ui(
								$mwp_sites,
								esc_html__( 'Child sites which have their activity log in the central MainWP activity logs', 'mwp-al-ext' ),
								$wsal_child_sites,
								esc_html__( 'Child sites which have enforced the configured activity log settings.', 'mwp-al-ext' ),
								$sites_with_enforced_settings_mapped,
								true,
								'',
								false,
								true,
								( 'some' !== $enforce_settings_on_subsites )
							);
							?>
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Activity log retention -->
            <div id="mwpal-setting-contentbox-3" class="postbox">
                <h2 class="hndle ui-sortable-handle">
                    <span><i class="fa fa-cog"></i> <?php esc_html_e( 'For how long do you want to keep the activity log events (Retention settings) ?', 'mwp-al-ext' ); ?></span>
                </h2>
                <div class="mainwp-postbox-actions-top">
                    <p class="description"><?php esc_html_e( 'The plugin uses an efficient way to store the activity log data in the WordPress database, though the more data you keep the more disk space will be required. ', 'mwp-al-ext' ); ?></p>
                </div>
                <div class="inside">
                    <table class="form-table wsal-tab">
                        <tbody>
                        <tr>
                            <th>
                                <label for="delete1"><?php esc_html_e( 'Activity log retention', 'mwp-al-ext' ); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="delete0">
                                        <input type="radio" id="delete0" name="pruning-enabled"
                                               value="no" <?php checked( $pruning_date_enabled, 'no' ); ?> />
										<?php echo esc_html__( 'Keep all data', 'mwp-al-ext' ); ?>
                                    </label>
                                </fieldset>

                                <fieldset>
                                    <label for="delete1">
                                        <input type="radio" id="delete1" name="pruning-enabled"
                                               value="yes" <?php checked( $pruning_date_enabled, 'yes' ); ?> />
										<?php esc_html_e( 'Delete events older than', 'mwp-al-ext' ); ?>
                                    </label>
                                    <input type="text" id="pruning-date" name="pruning-date"
                                           value="<?php echo esc_attr( $pruning_date ); ?>"
                                           onfocus="jQuery('#delete1').attr('checked', true);"
                                    />
                                    <select name="pruning-unit">
                                        <option value="months" <?php selected( $pruning_date_unit, 'months' ); ?>><?php esc_html_e( 'Months', 'mwp-al-ext' ); ?></option>
                                        <option value="years" <?php selected( $pruning_date_unit, 'years' ); ?>><?php esc_html_e( 'Years', 'mwp-al-ext' ); ?></option>
                                    </select>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div><!-- /.inside -->
            </div><!-- /.postbox -->
            <!-- END: Activity log retention -->

            <!-- Disabled events -->
            <div id="mwpal-setting-contentbox-3" class="postbox">
                <h2 class="hndle ui-sortable-handle"><span><i
                                class="fa fa-cog"></i> <?php esc_html_e( 'Disable events', 'mwp-al-ext' ); ?></span>
                </h2>
                <div class="mainwp-postbox-actions-top">
                    <p class="description">
						<?php
						printf(
						/* translators: %s is replaced with the hyperlink to the list of activity IDs */
							esc_html__( 'Specify the ID of the events you want to disable in the child sites’ activity logs. When an ID is disabled, the plugin won’t keep a record of that particular change in the activity log. Refer to the %s to find out what change every event ID represents.', 'mwp-al-ext' ),
							'<a href="https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/?utm_source=plugin&utm_medium=referral&utm_campaign=WSAL&utm_content=settings+pages" target="_blank">' . esc_html__( 'list of activity log event IDs', 'mwp-al-ext' ) . '</a>'
						); ?>
                    </p>
                </div>
                <div class="inside">
                    <table class="form-table wsal-tab">
                        <tbody>
                        <tr>
                            <th>
                                <label for="disabled-events"><?php esc_html_e( 'Disable the following event IDs:', 'mwp-al-ext' ); ?></label>
                            </th>
                            <td>
                                <input type="input" name="disabled-events" class="js-mwpal-disabled-events"
                                       value="<?php echo esc_attr( implode( ',', $selected_events ) ); ?>"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div><!-- /.inside -->
            </div><!-- /.postbox -->
            <!-- END: Disabled events -->

            <!-- Hide Plugin in Plugins Page -->
            <div id="mwpal-setting-contentbox-3" class="postbox">
                <h2 class="hndle ui-sortable-handle">
                    <span><i class="fa fa-cog"></i> <?php esc_html_e( 'Do you want to hide the plugin from the list of installed plugins?', 'mwp-al-ext' ); ?></span>
                </h2>
                <div class="mainwp-postbox-actions-top">
                    <p class="description"><?php esc_html_e( 'By default all installed plugins are listed in the plugins page. Set this option to Yes remove WP Activity Log from the list of installed plugins for users who are unable to access the WP Activity Log settings.', 'mwp-al-ext' ); ?></p>
                </div>
                <div class="inside">
                    <table class="form-table wsal-tab">
                        <tbody>
                        <tr>
                            <th>
                                <label for="incognito_yes"><?php esc_html_e( 'Hide Plugin in Plugins Page', 'mwp-al-ext' ); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="incognito_yes">
                                        <input type="radio" name="incognito-enabled" value="yes"
                                               id="incognito_yes" <?php checked( $incognito_mode_enabled, 'yes' ); ?> />
										<?php esc_html_e( 'Yes, hide the plugin from the list of installed plugins', 'mwp-al-ext' ); ?>
                                    </label>
                                    <br/>
                                    <label for="incognito_no">
                                        <input type="radio" name="incognito-enabled" value="no"
                                               id="incognito_no" <?php checked( $incognito_mode_enabled, 'no' ); ?> />
										<?php esc_html_e( 'No, do not hide the plugin', 'mwp-al-ext' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div><!-- /.inside -->
            </div><!-- /.postbox -->
            <!-- END: Hide Plugin in Plugins Page -->

            <!-- Login Page Notification -->
            <div id="mwpal-setting-contentbox-3" class="postbox">
                <h2 class="hndle ui-sortable-handle">
                    <span><i class="fa fa-cog"></i> <?php esc_html_e( 'Add user notification on the WordPress login page', 'mwp-al-ext' ); ?></span>
                </h2>
                <div class="mainwp-postbox-actions-top">
                    <p class="description"><?php esc_html_e( 'Many compliance regulations (such as the GDPR) require website administrators to tell the users of their website that all the changes they do when logged in are being logged.', 'mwp-al-ext' ); ?></p>
                </div>
                <div class="inside">
                    <table class="form-table wsal-tab">
                        <tbody>

                        <tr>
                            <th>
                                <label for="login_page_notification"><?php esc_html_e( 'Login Page Notification', 'mwp-al-text' ); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="wsal_lpn_yes">
                                        <input type="radio" name="login-page-notification"
                                               id="wsal_lpn_yes" <?php checked( $login_page_notification_enabled, 'yes' ); ?>
                                               value="yes"/>
										<?php esc_html_e( 'Yes', 'mwp-al-text' ); ?>
                                    </label>
                                    <br/>
                                    <textarea name="login-page-notification-text" id="login_page_notification_text"
                                              cols="60"
                                              rows="6" <?php echo disabled( $login_page_notification_enabled, 'no' ) ?>><?php echo wp_kses( $login_page_notification_text, $this->allowed_tags ); ?></textarea>
                                    <br/>
                                    <p class="description">
										<?php echo wp_kses( __( '<strong>Note: </strong>', 'mwp-al-text' ), $this->allowed_tags ) . esc_html__( 'The only HTML code allowed in the login page notification is for links ( < a href >< /a > ).', 'mwp-al-text' ); ?>
                                    </p>
                                    <br/>
                                    <label for="wsal_lpn_no">
                                        <input type="radio" name="login-page-notification"
                                               id="wsal_lpn_no" <?php checked( $login_page_notification_enabled, 'no' ); ?>
                                               value="no"/>
										<?php esc_html_e( 'No', 'mwp-al-text' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div><!-- /.inside -->
            </div><!-- /.postbox -->
            <!-- END: Login Page Notification -->

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary button button-hero"
                       value="<?php esc_attr_e( 'Save Settings', 'mwp-al-ext' ); ?>">
            </p>
        </form><!-- /#mwpal-child-sites-settings -->

		<?php
	}

	/**
	 * Save function for the tab.
	 */
	public function save() {
		if ( isset( $_POST['_wpnonce'] ) && isset( $_POST['submit'] ) ) {

			//  security check
			check_admin_referer( 'mwpal-enforce-child-sites-settings-nonce' );

			//  gather settings array
			$settings_to_enforce = [];
			$pruning_enabled     = filter_input( INPUT_POST, 'pruning-enabled', FILTER_SANITIZE_STRING );
			if ( 'yes' === $pruning_enabled ) {
				$settings_to_enforce['pruning_enabled'] = 'yes';
				$settings_to_enforce['pruning_date']    = filter_input( INPUT_POST, 'pruning-date', FILTER_SANITIZE_NUMBER_INT );
				$settings_to_enforce['pruning_unit']    = filter_input( INPUT_POST, 'pruning-unit', FILTER_SANITIZE_STRING );
			} else {
				$settings_to_enforce['pruning_enabled'] = 'no';
			}

			//  process disabled event IDs
			$disabled_events_ids       = [];
			$disabled_events_ids_value = filter_input( INPUT_POST, 'disabled-events', FILTER_SANITIZE_STRING );
			if ( ! empty( $disabled_events_ids_value ) ) {
				//  filter out invalid data
				$disabled_events_ids = array_map( 'intval', explode( ',', $disabled_events_ids_value ) );
			}
			$settings_to_enforce['disabled_events'] = implode( ',', $disabled_events_ids );

			//  load the previous values to figure out what sites (and if) to notify
			$settings                           = MWPAL_Extension\mwpal_extension()->settings;
			$previous_where_to_enforce_settings = $settings->get_enforce_settings_on_subsites();
			$previously_enforced_settings       = $settings->get_enforced_child_sites_settings();
			$previous_list_of_subsites          = $settings->get_sites_with_enforced_settings();

			//  incognito setting
			$incognito_enabled                             = filter_input( INPUT_POST, 'incognito-enabled', FILTER_SANITIZE_STRING );
			$incognito_enabled                             = ( 'yes' === $incognito_enabled ) ? 'yes' : 'no';
			$settings_to_enforce['incognito_mode_enabled'] = $incognito_enabled;

			//  login page notification
			$login_page_notification_enabled = filter_input( INPUT_POST, 'login-page-notification', FILTER_SANITIZE_STRING );
			if ( $login_page_notification_enabled === 'yes' ) {
				$settings_to_enforce['login_notification_enabled'] = 'yes';
				$settings_to_enforce['login_notification_text']    = wp_kses( trim( $_POST['login-page-notification-text'] ), $this->allowed_tags );
			} else {
				$settings_to_enforce['login_notification_enabled'] = 'no';
			}

			//  save settings to be enforced
			$settings->set_enforced_child_sites_settings( $settings_to_enforce );

			//  trigger event 7715
			mwpal_extension()->alerts->trigger( 7715 );

			//  what sites should have enforced settings?
			$where_to_enforce_settings = filter_input( INPUT_POST, 'enforce_settings_on_subsites', FILTER_SANITIZE_STRING );
			$settings->set_enforce_settings_on_subsites( $where_to_enforce_settings );

			if ( $previous_where_to_enforce_settings != $where_to_enforce_settings ) {
				//  fire event 7713
				mwpal_extension()->alerts->trigger(
					7713,
					array(
						'old_status' => $previous_where_to_enforce_settings,
						'new_status' => $where_to_enforce_settings,
					)
				);
			}

			//  assume no child sites selection - this will clear the previous site selection that might be set (passing
			//  an empty array will result in deleting the option from the database)
			$child_sites_ids = [];
			if ( 'some' === $where_to_enforce_settings ) {
				$child_sites_to_enforce = filter_input( INPUT_POST, 'mwpal-wsal-child-sites', FILTER_SANITIZE_STRING );
				if ( is_string( $child_sites_to_enforce ) && ! empty( $child_sites_to_enforce ) ) {
					$child_sites_ids = array_map( 'intval', explode( ',', $child_sites_to_enforce ) );
				}
			}

			//  save the child sites selection
			$settings->set_sites_with_enforced_settings( $child_sites_ids );

			//  send notifications to the child sites if necessary
			$this->maybe_notify_child_sites( $previous_where_to_enforce_settings, $where_to_enforce_settings, $previously_enforced_settings, $settings_to_enforce, $previous_list_of_subsites, $child_sites_ids );
		}
	}

	private function maybe_notify_child_sites( $previous_where_to_enforce_settings, $where_to_enforce_settings, $previously_enforced_settings, $settings_to_enforce, $previous_list_of_subsites, $child_sites_to_enforce ) {

		if ( $previous_where_to_enforce_settings == $where_to_enforce_settings && 'none' === $where_to_enforce_settings ) {
			//  no notifications necessary
			return;
		}

		$settings = MWPAL_Extension\mwpal_extension()->settings;

		//  check if the settings actually changed
		$enforced_settings_changed = ( ! empty( array_diff( $previously_enforced_settings, $settings_to_enforce ) ) );

		$sites_to_update = [];
		$sites_to_remove = [];
		$all_wsal_sites  = array_keys( $settings->get_wsal_child_sites() );
		switch ( $previous_where_to_enforce_settings ) {
			case 'all':
				if ( 'none' == $where_to_enforce_settings ) {
					//  tell all sites to delete enforced settings
					$sites_to_remove = $all_wsal_sites;
				} else if ( 'some' == $where_to_enforce_settings ) {
					$sites_to_remove = array_diff( $all_wsal_sites, $child_sites_to_enforce );

					//  log event 7714: removed
					$this->trigger_site_addition_or_removal( $sites_to_remove, false );

					if ( $enforced_settings_changed ) {
						$sites_to_update = $child_sites_to_enforce;
					}
				} else if ( 'all' == $where_to_enforce_settings && $enforced_settings_changed ) {
					$sites_to_update = $all_wsal_sites;
				}
				break;
			case 'none':
				if ( 'all' == $where_to_enforce_settings ) {
					//  all sites need to receive the enforced settings
					$sites_to_update = $all_wsal_sites;
				} else if ( 'some' == $where_to_enforce_settings ) {
					$sites_to_update = $child_sites_to_enforce;

					//  log event 7714: added
					$this->trigger_site_addition_or_removal( $sites_to_remove, true );
				}
				break;

			case 'some':

				//  check if the list of sites changed
				if ( 'all' === $where_to_enforce_settings ) {
					$child_sites_to_enforce = $all_wsal_sites;
				}

				//  sites removed
				$sites_removed = array_diff( $previous_list_of_subsites, $child_sites_to_enforce );
				if ( ! empty( $sites_removed ) ) {
					$sites_to_remove = $sites_removed;
					//  log event 7714: removed
					$this->trigger_site_addition_or_removal( $sites_removed, false );
				}

				//  sites added
				$sites_added = array_diff( $child_sites_to_enforce, $previous_list_of_subsites );
				if ( ! empty( $sites_added ) ) {
					$sites_to_update = $sites_added;
					//  log event 7714: added
					$this->trigger_site_addition_or_removal( $sites_removed, true );
				}

				if ( $enforced_settings_changed ) {
					//  if the settings changed we notify all sites in the list, not just the added ones
					$sites_to_update = $child_sites_to_enforce;
				}
				break;

			default:
				//  nothing
		}

		if ( ! empty( $sites_to_remove ) || ! empty( $sites_to_update ) ) {
			//  log event 7716:started
			MWPAL_Extension\mwpal_extension()->alerts->trigger( 7716 );
		}

		if ( ! empty( $sites_to_remove ) ) {
			$removal_process = new MWPAL_Extension\Enforce_Settings_Removal_Process();
			foreach ( $sites_to_remove as $site_id ) {
				$removal_process->push_to_queue( [
					'site_id' => $site_id
				] );
			}
			$removal_process->save()->dispatch();
		}

		if ( ! empty( $sites_to_update ) ) {
			$update_process = new MWPAL_Extension\Enforce_Settings_Update_Process();
			foreach ( $sites_to_update as $site_id ) {
				$update_process->push_to_queue( [
					'site_id'  => $site_id,
					'settings' => $settings_to_enforce
				] );
			}
			$update_process->save()->dispatch();
		}
	}

	protected function trigger_site_addition_or_removal( $site_ids, $is_addition ) {
		if ( is_array( $site_ids ) && ! empty( $site_ids ) ) {
			foreach ( $site_ids as $site_id ) {
				$site = mwpal_extension()->settings->get_mwp_child_site_by_id( $site_id );
				if ( $site != null ) {
					mwpal_extension()->alerts->trigger(
						7714,
						array(
							'friendly_name' => $site['name'],
							'site_url'      => $site['url'],
							'EventType'     => $is_addition ? 'added' : 'removed'
						)
					);
				}
			}
		}
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_styles() {
		//  not needed
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		// Extension page check.
		if ( ! MWPAL_Extension\mwpal_extension()->settings->is_current_extension_page() ) {
			return;
		}

		//  current view page check
		if ( self::$tab_id !== MWPAL_Extension\mwpal_extension()->extension_view->get_current_tab() ) {
			return;
		}

		$script_handle        = 'mwpal-enforced-settings-script';
		$relative_script_path = 'assets/js/dist/build.enforced-settings.js';
		wp_register_script(
			$script_handle,
			trailingslashit( MWPAL_BASE_URL ) . $relative_script_path,
			array( 'jquery', 'mwpal-select2-js' ),
			filemtime( trailingslashit( MWPAL_BASE_DIR ) . $relative_script_path ),
			false
		);

		wp_localize_script( $script_handle, 'mwpal_enforced_settings', array(
			'events'       => wp_json_encode( MWPAL_Extension\Helpers\DataHelper::get_events_for_select2() ),
			'selectEvents' => __( 'Select event code(s)', 'mwp-al-ext' )
		) );
		wp_enqueue_script( $script_handle );
	}


	/**
	 * Display admin notices (if any).
	 */
	public function display_admin_notices() {
		if ( ! MWPAL_Extension\mwpal_extension()->settings->is_current_extension_page() ) {
			return;
		}

		//  current view page check
		if ( self::$tab_id !== MWPAL_Extension\mwpal_extension()->extension_view->get_current_tab() ) {
			return;
		}

		//  @todo show admin notice when the setting are saved
	}
}

new Enforce_Settings_View();
