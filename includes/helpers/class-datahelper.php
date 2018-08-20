<?php
/**
 * Class: Data Helper.
 *
 * Helper class used for encode/decode json data.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class used for encode/decode json data.
 *
 * @package mwp-al-ext
 */
class DataHelper {

	/**
	 * A wrapper for JSON encoding that fixes potential issues.
	 *
	 * @param mixed $data - The data to encode.
	 * @return string JSON string.
	 */
	public static function JsonEncode( $data ) {
		return @json_encode( $data );
	}

	/**
	 * A wrapper for JSON encoding that fixes potential issues.
	 *
	 * @param string $data - The JSON string to decode.
	 * @return mixed Decoded data.
	 */
	public static function JsonDecode( $data ) {
		return @json_decode( $data );
	}
}
