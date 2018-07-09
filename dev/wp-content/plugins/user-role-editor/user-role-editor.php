<?php
/*
Plugin Name: User Role Editor
Plugin URI: https://www.role-editor.com
Description: Change/add/delete WordPress user roles and capabilities.
Version: 4.20.1
Author: Vladimir Garagulya
Author URI: https://www.role-editor.com
Text Domain: ure
Domain Path: /lang/
*/

/*
Copyright 2010-2015  Vladimir Garagulya  (email: support@role-editor.com)
*/

if (!function_exists('get_option')) {
  header('HTTP/1.0 403 Forbidden');
  die;  // Silence is golden, direct call is prohibited
}

if (defined('URE_PLUGIN_URL')) {
   wp_die('It seems that other version of User Role Editor is active. Please deactivate it before use this version');
}

define('URE_VERSION', '4.20.1');
define('URE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('URE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('URE_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('URE_PLUGIN_FILE', basename(__FILE__));
define('URE_PLUGIN_FULL_PATH', __FILE__);

if (!class_exists('Garvs_WP_Lib')) {
    require_once(URE_PLUGIN_DIR.'includes/class-garvs-wp-lib.php');
}
require_once(URE_PLUGIN_DIR.'includes/class-ure-lib.php');

// check PHP version
$ure_required_php_version = '5.2.4';
$exit_msg = sprintf( 'User Role Editor requires PHP %s or newer.', $ure_required_php_version ) . 
                         '<a href="http://wordpress.org/about/requirements/"> ' . 'Please update!' . '</a>';
Ure_Lib::check_version( PHP_VERSION, $ure_required_php_version, $exit_msg, __FILE__ );

// check WP version
$ure_required_wp_version = '4.0';
$exit_msg = sprintf( 'User Role Editor requires WordPress %s or newer.', $ure_required_wp_version ) . 
                        '<a href="http://codex.wordpress.org/Upgrading_WordPress"> ' . 'Please update!' . '</a>';
Ure_Lib::check_version(get_bloginfo('version'), $ure_required_wp_version, $exit_msg, __FILE__ );

require_once(URE_PLUGIN_DIR .'includes/define-constants.php');
require_once(URE_PLUGIN_DIR .'includes/misc-support-stuff.php');
require_once(URE_PLUGIN_DIR .'includes/class-role-additional-options.php');
require_once(URE_PLUGIN_DIR .'includes/class-assign-role.php');
require_once(URE_PLUGIN_DIR .'includes/class-user-other-roles.php');
require_once(URE_PLUGIN_DIR .'includes/class-protect-admin.php');
require_once(URE_PLUGIN_DIR .'includes/class-ajax-processor.php');
require_once(URE_PLUGIN_DIR .'includes/class-ure-screen-help.php');
require_once(URE_PLUGIN_DIR .'includes/class-known-js-css-compatibility-issues.php');
require_once(URE_PLUGIN_DIR .'includes/class-user-role-editor.php');


$ure_lib = new Ure_Lib('user_role_editor');
$GLOBALS['user_role_editor'] = new User_Role_Editor($ure_lib);
