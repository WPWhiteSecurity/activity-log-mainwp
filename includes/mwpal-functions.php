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
 * Create a new file in the uploads directory of the extension.
 *
 * @param string $filename  - File name.
 * @param string $content   - Contents of the file.
 * @param bool   $override  - (Optional) True if overriding file contents.
 * @return bool
 */
function write_to_extension_upload( $filename, $content, $override = false ) {
	global $wp_filesystem;
	WP_Filesystem();

	$filepath = MWPAL_UPLOADS_DIR . $filename;
	$dir_path = dirname( $filepath );
	$result   = false;

	if ( ! is_dir( $dir_path ) ) {
		wp_mkdir_p( $dir_path );
	}

	if ( ! $wp_filesystem->exists( $filepath ) || $override ) {
		$result = $wp_filesystem->put_contents( $filepath, $content );
	} else {
		$existing_content = $wp_filesystem->get_contents( $filepath );
		$result           = $wp_filesystem->put_contents( $filepath, $existing_content . $content );
	}

	return $result;
}

/**
 * Returns the MWPAL's uploaded file contents.
 *
 * @param string $filename - Filename.
 * @return mixed
 */
function get_upload_file_contents( $filename ) {
	global $wp_filesystem;
	WP_Filesystem();

	$filepath = MWPAL_UPLOADS_DIR . $filename;
	$contents = false;

	if ( $wp_filesystem->exists( $filepath ) ) {
		$contents = $wp_filesystem->get_contents( $filepath );
	}

	return $contents;
}

/**
 * Create an index.php file, if none exists, in order to
 * avoid directory listing in the specified directory.
 *
 * @param string $dir_path - Directory Path.
 * @return bool
 */
function create_index_file( $dir_path ) {
	return write_to_extension_upload( trailingslashit( $dir_path ) . 'index.php', '<?php // Silence is golden' );
}

/**
 * Create an .htaccess file, if none exists, in order to
 * block access to directory listing in the specified directory.
 *
 * @param string $dir_path - Directory Path.
 * @return bool
 */
function create_htaccess_file( $dir_path ) {
	return write_to_extension_upload( trailingslashit( $dir_path ) . '.htaccess', 'Deny from all' );
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
 * Get child site users.
 *
 * @return array
 */
function get_child_site_users() {
	return mwpal_extension()->settings->get_option( 'wsal-child-users', array() );
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
