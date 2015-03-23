<?php
/*
Plugin Name: RY Post Expiration
Plugin URI: http://www.wwpteach.com/ry-post-expiration
Description: Hide article by the expiration time
Version: 1.0.0
Author: Richer Yang
Author URI: http://www.wwpteach.com/
Text Domain: ry-post-expiration
Domain Path: /languages
*/

function_exists('plugin_dir_url') OR exit('No direct script access allowed');

define('RY_PE_VERSION', '1.0.0');
define('RY_PE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_PE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_PE_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once(RY_PE_PLUGIN_DIR . 'class.ry-pe.php');

register_activation_hook( __FILE__, array('RY_PE', 'plugin_activation'));
register_deactivation_hook( __FILE__, array('RY_PE', 'plugin_deactivation'));

add_action('init', array('RY_PE', 'init'));
