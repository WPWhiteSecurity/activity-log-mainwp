<?php

namespace WSAL\MainWPExtension;

class Enforce_Settings_Update_Process extends Enforce_Settings_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wpws_mainwp_enforce_update';

	protected function check_item( $item ) {
		return array_key_exists( 'settings', $item );
	}

	protected function get_subaction() {
		return 'update';
	}

	protected function change_api_data( $data, $item ) {
		$data['settings'] = $item['settings'];
		return $data;
	}

}