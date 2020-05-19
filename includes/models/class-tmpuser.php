<?php
/**
 * Class: TmpUser Model Class
 *
 * Model used for the Temporary WP_users table.
 *
 * @package mwp-al-ext
 */

namespace WSAL\MainWPExtension\Models;

use \WSAL\MainWPExtension\Models\ActiveRecord as ActiveRecord;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Model tmp_users
 *
 * Model used for the Temporary WP_users table.
 */
class TmpUser extends ActiveRecord {

	/**
	 * User ID.
	 *
	 * @var integer
	 */
	public $id = 0;

	/**
	 * Username.
	 *
	 * @var string
	 */
	public $user_login = '';

	/**
	 * Model Name.
	 *
	 * @var string
	 */
	protected $adapterName = 'TmpUser';
}
