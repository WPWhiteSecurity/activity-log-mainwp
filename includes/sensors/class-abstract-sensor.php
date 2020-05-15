<?php
/**
 * Abstract Class: Sensor
 *
 * Abstract sensor class file of the extension.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Sensors;

use \WSAL\MainWPExtension as MWPAL_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract sensor class of the extension.
 */
abstract class Abstract_Sensor {

	/**
	 * Method: Hook events related to sensor.
	 */
	abstract public function hook_events();

	/**
	 * Method: Log the message for sensor.
	 *
	 * @param int    $type    - Type of alert.
	 * @param string $message - Alert message.
	 * @param mix    $args    - Message arguments.
	 */
	protected function log( $type, $message, $args ) {
		MWPAL_Extension\mwpal_extension()->alerts->trigger(
			$type,
			array(
				'Message' => $message,
				'Context' => $args,
				'Trace'   => debug_backtrace(),
			)
		);
	}

	/**
	 * Method: Log error message for sensor.
	 *
	 * @param string $message - Alert message.
	 * @param mix    $args    - Message arguments.
	 */
	protected function log_error( $message, $args ) {
		$this->log( 0001, $message, $args );
	}

	/**
	 * Method: Log warning message for sensor.
	 *
	 * @param string $message - Alert message.
	 * @param mix    $args    - Message arguments.
	 */
	protected function log_warn( $message, $args ) {
		$this->log( 0002, $message, $args );
	}

	/**
	 * Method: Log info message for sensor.
	 *
	 * @param string $message - Alert message.
	 * @param mix    $args    - Message arguments.
	 */
	protected function log_info( $message, $args ) {
		$this->log( 0003, $message, $args );
	}
}
