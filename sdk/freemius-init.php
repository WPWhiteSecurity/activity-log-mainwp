<?php
/**
 * Initialize Freemius.
 *
 * @package mwp-al-ext
 */

if ( ! function_exists( 'almainwp_fs' ) ) {

	/**
	 * Create a helper function for easy SDK access.
	 *
	 * @return Freemius
	 */
	function almainwp_fs() {
		global $almainwp_fs;

		if ( ! isset( $almainwp_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$almainwp_fs = fs_dynamic_init(
				array(
					'id'             => '3484',
					'slug'           => 'activity-log-mainwp',
					'type'           => 'plugin',
					'public_key'     => 'pk_0f8d051081d0a65688cdfe339a638',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => false,
					'menu'           => array(
						'support' => false,
					),
					'anonymous_mode' => true,
				)
			);
		}

		return $almainwp_fs;
	}

	// Init Freemius.
	almainwp_fs();

	// Signal that SDK was initiated.
	do_action( 'almainwp_fs_loaded' );
}
