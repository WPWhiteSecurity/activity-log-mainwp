<?php
/**
 * Class: Setup Wizard
 *
 * Setup wizard class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Views;

use \WSAL\MainWPExtension\Activity_Log as Activity_Log;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class: Extension Setup Wizard.
 *
 * Extension setup wizard class which manages the functionality
 * related to setup.
 */
final class Setup_Wizard {

	/**
	 * Activity Log Instance.
	 *
	 * @var \WSAL\MainWPExtension\Activity_Log
	 */
	protected $activity_log;

	/**
	 * Wizard Steps
	 *
	 * @var array
	 */
	private $wizard_steps;

	/**
	 * Current Step
	 *
	 * @var string
	 */
	private $current_step;

	/**
	 * Method: Constructor.
	 *
	 * @param Activity_Log $activity_log – Instance of main plugin.
	 */
	public function __construct( Activity_Log $activity_log ) {
		$this->activity_log = $activity_log;

		add_action( 'admin_menu', array( $this, 'admin_menus' ), 10 );
		add_action( 'admin_init', array( $this, 'setup_page' ), 10 );
	}

	/**
	 * Add setup admin page.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'activity-log-mainwp-setup', '' );
	}

	/**
	 * Setup Page Start.
	 */
	public function setup_page() {
		// Get page argument from $_GET array.
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		if ( empty( $page ) || 'activity-log-mainwp-setup' !== $page ) {
			return;
		}

		/**
		 * Wizard Steps.
		 */
		$wizard_steps = array(
			'welcome'     => array(
				'name'    => __( 'Welcome', 'mwp-al-ext' ),
				'content' => array( $this, 'step_welcome' ),
			),
			'wsal_sites' => array(
				'name'    => __( 'Child Sites', 'mwp-al-ext' ),
				'content' => array( $this, 'step_wsal_sites' ),
				'save'    => array( $this, 'step_wsal_sites_save' ),
			),
		);

		/**
		 * Filter: `Wizard Default Steps`
		 *
		 * Extension filter hook to filter wizard steps before they are displayed.
		 *
		 * @param array $wizard_steps – Wizard Steps.
		 */
		$this->wizard_steps = apply_filters( 'mwpal_wizard_default_steps', $wizard_steps );

		// Set current step.
		$current_step       = filter_input( INPUT_GET, 'current-step', FILTER_SANITIZE_STRING );
		$this->current_step = ! empty( $current_step ) ? $current_step : current( array_keys( $this->wizard_steps ) );

		/**
		 * Enqueue Styles.
		 */
		wp_enqueue_style(
			'mwpal-wizard-css',
			trailingslashit( MWPAL_BASE_URL ) . 'assets/css/dist/mwpal-setup-wizard.build.css',
			array( 'dashicons', 'install', 'forms' ),
			filemtime( trailingslashit( MWPAL_BASE_DIR ) . 'assets/css/dist/mwpal-setup-wizard.build.css' )
		);

		/**
		 * Save Wizard Settings.
		 */
		$save_step = filter_input( INPUT_POST, 'save_step', FILTER_SANITIZE_STRING );
		if ( ! empty( $save_step ) && ! empty( $this->wizard_steps[ $this->current_step ]['save'] ) ) {
			call_user_func( $this->wizard_steps[ $this->current_step ]['save'] );
		}

		ob_start();
		$this->setup_page_header();
		$this->setup_page_steps();
		$this->setup_page_content();
		$this->setup_page_footer();
		exit;
	}

	/**
	 * Setup Page Header.
	 */
	private function setup_page_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'Activity Log MainWP &rsaquo; Setup Wizard', 'mwp-al-ext' ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wsal-setup wp-core-ui">
			<h1 id="wsal-logo"><a href="https://wpsecurityauditlog.com/" target="_blank"><img src="<?php echo esc_url( trailingslashit( MWPAL_BASE_URL ) ); ?>assets/img/wsal-logo-full.png" alt="WP Security Audit Log" /></a></h1>
		<?php
	}

	/**
	 * Setup Page Footer.
	 */
	private function setup_page_footer() {
		?>
			</body>
		</html>
		<?php
	}

	/**
	 * Setup Page Steps.
	 */
	private function setup_page_steps() {
		?>
		<ul class="steps">
			<?php
			foreach ( $this->wizard_steps as $key => $step ) :
				if ( $key === $this->current_step ) :
					?>
					<li class="is-active"><?php echo esc_html( $step['name'] ); ?></li>
				<?php else : ?>
					<li><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				endif;
			endforeach;
			?>
		</ul>
		<?php
	}

	/**
	 * Get Next Step URL.
	 *
	 * @return string
	 */
	private function get_next_step() {
		// Get current step.
		$current_step = $this->current_step;

		// Array of step keys.
		$keys = array_keys( $this->wizard_steps );
		if ( end( $keys ) === $current_step ) { // If last step is active then return WP Admin URL.
			return admin_url();
		}

		// Search for step index in step keys.
		$step_index = array_search( $current_step, $keys, true );
		if ( false === $step_index ) { // If index is not found then return empty string.
			return '';
		}

		// Return next step.
		return add_query_arg( 'current-step', $keys[ $step_index + 1 ] );
	}

	/**
	 * Setup Page Content.
	 */
	private function setup_page_content() {
		?>
		<div class="mwpal-setup-content">
			<?php
			if ( ! empty( $this->wizard_steps[ $this->current_step ]['content'] ) ) {
				call_user_func( $this->wizard_steps[ $this->current_step ]['content'] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Step View: `Welcome`
	 */
	private function step_welcome() {
		?>
		<p><?php esc_html_e( 'Thank you for installing the Activity Log for MainWP Extension.', 'mwp-al-ext' ); ?></p>
		<p><?php esc_html_e( 'This extension allows you to see the activity logs of all the child websites which have WP Security Audit Log installed from the MainWP dashboard.', 'mwp-al-ext' ); ?></p>
		<div class="mwpal-setup-actions">
			<a class="button button-primary"
				href="<?php echo esc_url( $this->get_next_step() ); ?>">
				<?php esc_html_e( 'Next', 'mwp-al-ext' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Step View: `WSAL Sites`
	 */
	private function step_wsal_sites() {
		$mwp_sites  = $this->activity_log->settings->get_mwp_child_sites();
		$wsal_sites = $this->activity_log->settings->get_wsal_child_sites();

		if ( ! empty( $wsal_sites ) ) :
			?>
			<form method="post" class="mwpal-setup-form">
				<?php wp_nonce_field( 'mwpal-step-wsal-sites' ); ?>
				<p><?php esc_html_e( 'These are the child sites which have the WP Security Audit Log plugin installed. The activity log extension will automatically retrieve the logs from them to show them in the MainWP dashboard.', 'mwp-al-ext' ); ?></p>
				<p><?php esc_html_e( 'Deselect those which you do not want to retrieve logs from:', 'mwp-al-ext' ); ?></p>
				<fieldset>
					<div class="sites-container">
						<?php
						foreach ( $wsal_sites as $site_id => $site ) :
							// Search for the site data.
							$key = array_search( $site_id, array_column( $mwp_sites, 'id' ), false );
							if ( false !== $key && isset( $mwp_sites[ $key ] ) ) :
								$selected_site = $mwp_sites[ $key ];
								?>
								<label for="<?php echo esc_attr( $selected_site['id'] ); ?>">
									<input type="checkbox" name="mwpal-wsal-sites[]" id="<?php echo esc_attr( $selected_site['id'] ); ?>" value="<?php echo esc_attr( $selected_site['id'] ); ?>" checked /> <?php echo esc_html( $selected_site['name'] ); ?>
								</label>
								<br>
							<?php
							endif;
						endforeach;
						?>
					</div>
				</fieldset>
				<fieldset>
					<div class="fetch-logs-container checkbox-toggle">
						<input type="checkbox" name="fetch-logs-now" value="1" id="fetch-logs-toggle" checked>
						<label for="fetch-logs-toggle"><?php esc_html_e( 'Fetch logs from the child sites immediately?', 'mwp-al-ext' ); ?></label>
					</div>
				</fieldset>
				<p class="description"><?php /* translators: %s: Note */ echo sprintf( esc_html__( '%s You can add or remove child websites at a later stage from the extensions settings.', 'mwp-al-ext' ), '<strong>' . esc_html__( 'Note:', 'mwp-al-ext' ) . '</strong>' ); ?></p>
				<div class="mwpal-setup-actions">
					<button class="button button-primary" type="submit" name="save_step" value="<?php esc_attr_e( 'Next', 'wp-security-audit-log' ); ?>">
						<?php esc_html_e( 'Finish', 'wp-security-audit-log' ); ?>
					</button>
				</div>
			</form>
			<?php
		else :
			?>
			<p><?php /* Translators: %s: Getting started guide hyperlink */ echo sprintf( esc_html__( 'It seems that the WP Security Audit Log plugin is not installed on any of the child sites. Please exit this wizard, install the WP Security Audit Log plugin on the child sites and then add the sites from the Extensions settings. Refer to the %s for more information.', 'mwp-al-ext' ), '<a href="https://www.wpsecurityauditlog.com/support-documentation/gettting-started-activity-log-mainwp-extension/" target="_blank">' . esc_html__( 'Getting Started Guide', 'mwp-al-ext' ) . '</a>' ); ?></p>
			<div class="mwpal-setup-actions">
				<a class="button button-primary"
					href="<?php echo esc_url( $this->get_next_step() ); ?>">
					<?php esc_html_e( 'Exit Wizard', 'mwp-al-ext' ); ?>
				</a>
			</div>
			<?php
		endif;
	}

	/**
	 * Step Save: `WSAL Sites`
	 */
	private function step_wsal_sites_save() {
		// Verify nonce.
		check_admin_referer( 'mwpal-step-wsal-sites' );

		// Get selected sites.
		$selected_sites = isset( $_POST['mwpal-wsal-sites'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['mwpal-wsal-sites'] ) ) : false;

		// Save the sites.
		$this->activity_log->settings->set_wsal_child_sites( ! empty( $selected_sites ) ? $selected_sites : false );
		$this->activity_log->settings->update_option( 'setup-complete', true );
		// Fetch the initial logs from the sites.
		if ( filter_input( INPUT_POST, 'fetch-logs-now', FILTER_VALIDATE_BOOLEAN ) && ! empty( $selected_sites ) ) {
			$this->fetch_initial_logs( $selected_sites );
		}
		wp_safe_redirect( esc_url_raw( $this->get_next_step() ) );
		exit();
	}

	/**
	 * Get initial set of logs from sites right after adding them during wizard.
	 *
	 * @method fetch_initial_logs
	 * @since  1.2.0
	 * @param  array $sites array of site ids that were just added.
	 */
	public function fetch_initial_logs( $sites ) {
		// get events from site.
		foreach ( $sites as $site ) {
			$sites_data[ $site ] = $this->activity_log->alerts->fetch_site_events( $site );
		}
		// Extension is ready after retrieving.
		$this->activity_log->alerts->Trigger(
			7712,
			array(
				'mainwp_dash' => true,
				'Username'    => 'System',
				'ClientIP'    => $this->activity_log->settings->get_server_ip(),
			)
		);
		// Set child site events.
		if ( ! empty( $sites_data ) && is_array( $sites_data ) ) {
			$this->activity_log->alerts->set_site_events( $sites_data );
		}
	}
}
