<?php

namespace WSAL\MainWPExtension;

class Enforce_Settings_Removal_Process extends Enforce_Settings_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'wpws_mainwp_enforce_remove';

	protected function get_subaction() {
		return 'remove';
	}

}