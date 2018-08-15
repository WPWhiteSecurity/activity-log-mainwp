<?php
/**
 * Class: Settings
 *
 * Settings class file of the extension.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 *
 * Settings class of the extension.
 */
class Settings {

	/**
	 * Get Plugin Option.
	 *
	 * @param string  $option  – Option name.
	 * @param boolean $default – Default value.
	 * @return mixed
	 */
	public function get_option( $option, $default = false ) {
		return get_option( MWPAL_OPT_PREFIX . $option, $default );
	}

	/**
	 * Update Plugin Option.
	 *
	 * @param string $option – Option name.
	 * @param mixed  $value  – Option value.
	 * @return boolean
	 */
	public function update_option( $option, $value = false ) {
		return update_option( MWPAL_OPT_PREFIX . $option, $value );
	}

	/**
	 * Delete Plugin Option.
	 *
	 * @param string $option – Option name.
	 * @return boolean
	 */
	public function delete_option( $option ) {
		return delete_option( MWPAL_OPT_PREFIX . $option );
	}

	/**
	 * Checks if extension is activated or not.
	 *
	 * @param string $default - Default value to return if option doesn't exist.
	 * @return string
	 */
	public function is_extension_activated( $default = 'no' ) {
		return $this->get_option( 'activity-extension-activated', $default );
	}

	/**
	 * Updates extension activated option.
	 *
	 * @param string $value - Value of the option.
	 * @return boolean
	 */
	public function set_extension_activated( $value ) {
		return $this->update_option( 'activity-extension-activated', $value );
	}
}
