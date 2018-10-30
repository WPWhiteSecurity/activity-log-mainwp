<?php
/**
 * Class: Abstract Connector.
 *
 * Abstract class used as a class loader.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Connector;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-wpdbcustom.php';

/**
 * Adapter Classes loader class.
 *
 * Abstract class used as a class loader.
 *
 * @package mwp-al-ext
 */
abstract class AbstractConnector {

	/**
	 * Connection Variable.
	 *
	 * @var null
	 */
	protected $connection = null;

	/**
	 * Adapter Base Path.
	 *
	 * @var null
	 */
	protected $adapters_base_path = null;

	/**
	 * Adapter Directory Name.
	 *
	 * @var null
	 */
	protected $adapters_dir_name = null;

	/**
	 * Method: Constructor.
	 *
	 * @param  string $adapters_dir_name - Adapter directory name.
	 */
	public function __construct( $adapters_dir_name = null ) {
		$this->adapters_base_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'adapters' . DIRECTORY_SEPARATOR;

		if ( ! empty( $adapters_dir_name ) ) {
			$this->adapters_dir_name = $adapters_dir_name;
			require_once $this->getAdaptersDirectory() . DIRECTORY_SEPARATOR . 'class-ActiveRecordAdapter.php';
			require_once $this->getAdaptersDirectory() . DIRECTORY_SEPARATOR . 'class-MetaAdapter.php';
			require_once $this->getAdaptersDirectory() . DIRECTORY_SEPARATOR . 'class-OccurrenceAdapter.php';
			require_once $this->getAdaptersDirectory() . DIRECTORY_SEPARATOR . 'class-QueryAdapter.php';
		}
	}

	/**
	 * Method: Get adapters directory.
	 */
	public function getAdaptersDirectory() {
		if ( ! empty( $this->adapters_base_path ) && ! empty( $this->adapters_dir_name ) ) {
			return $this->adapters_base_path . $this->adapters_dir_name;
		} else {
			return false;
		}
	}
}
