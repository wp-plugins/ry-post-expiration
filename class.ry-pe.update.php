<?php
defined('RY_PE_VERSION') OR exit('No direct script access allowed');

class RY_PE_update {
	public static function update() {
		$now_version = RY_PE::get_option('version');

		if( $now_version === FALSE ) {
			$now_version = '0.0.0';
		}
		if( $now_version == RY_PE_VERSION ) {
			return;
		}
		if( version_compare($now_version, '1.0.0', '<' ) ) {
			self::update_1_0_0();
			RY_PE::update_option('version', '1.0.0');
		}
	}

	private static function update_1_0_0() {
	}
}
