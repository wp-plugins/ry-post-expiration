<?php
defined('RY_PE_VERSION') OR exit('No direct script access allowed');

class RY_PE {
	public static $textdomain = 'ry-post-expiration';
	private static $prefix_word = 'RY_PE_';
	private static $initiated = FALSE;

	public static function init() {
		if( !self::$initiated ) {
			self::init_hooks();
		}
	}

	private static function init_hooks() {
		self::$initiated = true;

		require_once(RY_PE_PLUGIN_DIR . 'class.ry-pe.update.php');

		RY_PE_update::update();

		add_action('parse_query', array('RY_PE', 'parse_query'));

		if( is_admin() ) {
			require_once(RY_PE_PLUGIN_DIR . 'class.ry-pe.admin.php');
			RY_PE_admin::init();
		}
	}

	public static function parse_query($object) {
		if( !is_admin() ) {
			if ( isset($object->query_vars['post_type']) ) {
				$post_type = $object->query_vars['post_type'];
			} else {
				if( $object->is_search ) {
					$post_type = 'any';
				} elseif( $object->is_tax ) {
					$post_type = 'any';
				} else {
					$post_type = 'post';
				}
			}
			if( !is_array($post_type) ) {
				$post_type = array($post_type);
			}
			$post_type = array_filter($post_type);
			if( in_array('any', $post_type) ||  count(array_intersect($post_type, RY_PE::get_option('support_post_type', array()))) > 0 ) {
				if( !isset($object->query_vars['meta_query']) ) {
					$object->query_vars['meta_query'] = array();
				}
				$object->query_vars['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key' => self::$prefix_word . 'expiration_time',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key' => self::$prefix_word . 'expiration_time',
						'value' => date('Y-m-d h:s:i'),
						'compare' => '>=',
						'type' => 'DATETIME'
					)
				);
			}
		}
	}

	public static function get_option($option, $default = FALSE) {
		return get_option(self::$prefix_word . $option, $default);
	}

	public static function update_option($option, $value) {
		return update_option(self::$prefix_word . $option, $value);
	}

	public static function add_post_meta($post_id, $meta_key, $meta_value, $unique = FALSE) {
		return add_post_meta($post_id, self::$prefix_word . $meta_key, $meta_value, $unique);
	}

	public static function delete_post_meta($post_id, $meta_key, $meta_value = '') {
		return delete_post_meta($post_id, self::$prefix_word . $meta_key, $meta_value);
	}

	public static function get_post_meta($post_id, $meta_key = '', $single = FALSE) {
		return get_post_meta($post_id, self::$prefix_word . $meta_key, $single);
	}

	public static function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_post_meta( $post_id, self::$prefix_word . $meta_key, $meta_value, $prev_value);
	}

	public static function plugin_activation() {
	}

	public static function plugin_deactivation( ) {
	}
}
