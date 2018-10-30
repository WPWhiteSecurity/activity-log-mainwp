<?php
/**
 * Query Interface.
 *
 * Interface used by the Query.
 *
 * @package mwp-al-ext
 * @since 1.0.0
 */

namespace WSAL\MainWPExtension\Adapters;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface used by the Query.
 *
 * @package mwp-al-ext
 */
interface QueryInterface {

	/**
	 * Execute query and return data as $ar_cls objects.
	 *
	 * @param object $query - Query object.
	 */
	public function Execute( $query );

	/**
	 * Count query.
	 *
	 * @param object $query - Query object.
	 */
	public function Count( $query );

	/**
	 * Query for deleting records.
	 *
	 * @param object $query - Query object.
	 */
	public function Delete( $query );
}
