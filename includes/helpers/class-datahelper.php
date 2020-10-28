<?php
/**
 * Class: Data Helper.
 *
 * Helper class used for:
 * - JSON data encode/decode
 * - common data loading for select boxes etc.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Helpers;

// Exit if accessed directly.
use WSAL\MainWPExtension as MWPAL_Extension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class used for:
 * - JSON data encode/decode
 * - common data loading for select boxes etc.
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

	/**
	 * Return categorized events of WSAL.
	 *
	 * @return array
	 */
	public static function get_categorized_events() {
		$cg_alerts = MWPAL_Extension\mwpal_extension()->alerts->get_categorized_alerts();
		$events    = array();

		foreach ( $cg_alerts as $cname => $group ) {
			foreach ( $group as $subname => $entries ) {
				if ( __( 'Pages', 'mwp-al-ext' ) === $subname || __( 'Custom Post Types', 'mwp-al-ext' ) === $subname ) {
					continue;
				}

				$events[ $subname ] = $entries;
			}
		}

		return $events;
	}

	/**
	 * Returns events for select2.
	 *
	 * @return array
	 */
	public static function get_events_for_select2() {
		$events       = MWPAL_Extension\Helpers\DataHelper::get_categorized_events();
		$event_groups = array();

		foreach ( $events as $cname => $entries ) {
			$group           = new \stdClass();
			$group->text     = $cname;
			$group->children = array();

			foreach ( $entries as $arr_obj ) {
				$option       = new \stdClass();
				$option->id   = $arr_obj->type;
				$option->text = $option->id . ' (' . $arr_obj->desc . ')';
				array_push( $group->children, $option );
			}

			array_push( $event_groups, $group );
		}

		return $event_groups;
	}
}
