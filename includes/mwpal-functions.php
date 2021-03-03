<?php
/**
 * MWPAL Functions.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Save child site users.
 *
 * @param integer $site_id - Site id.
 * @param array   $users   - Array of site users.
 */
function save_child_site_users( $site_id, $users ) {
	// Get stored site users.
	$child_site_users = mwpal_extension()->settings->get_option( 'wsal-child-users', array() );

	// Set the users.
	$child_site_users[ $site_id ] = $users;

	// Save them.
	mwpal_extension()->settings->update_option( 'wsal-child-users', $child_site_users );
}
/**
 * Returns the version number of MainWP plugin.
 *
 * @return mixed
 */
function get_mainwp_version() {
	if ( class_exists( '\MainWP_System' ) ) {
		return \MainWP_System::$version;
	}

	$mainwp_info = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . 'mainwp/mainwp.php' );

	if ( ! empty( $mainwp_info ) && isset( $mainwp_info['Version'] ) ) {
		return $mainwp_info['Version'];
	}

	return false;
}
