<?php
/**
 * Plugin Name: Disable User Login
 * Plugin URI:  http://wordpress.org/plugins/disable-user-login
 * Description: Provides the ability to disable user accounts and prevent them from logging in.
 * Version:     1.3.2
 *
 * Author:      Saint Systems
 * Author URI:  https://www.saintsystems.com
 * Text Domain: disable-user-login
 * Domain Path: languages
 *
 * Copyright: © 2019 Saint Systems
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Full path to the main plugin file
 * @define "SS_DISABLE_USER_LOGIN_FILE" "./disable-user-login.php"
 */
define( 'SS_DISABLE_USER_LOGIN_FILE', __FILE__ );

/**
 * The main plugin class (SS_Disable_User_Login_Plugin)
 */
require_once( 'includes/class-ss-disable-user-login-plugin.php' );

function SSDUL() {
	return SS_Disable_User_Login_Plugin::get_instance();
}

// Get WooCommerce Mailchimp Running.
add_action( 'plugins_loaded', 'SSDUL', 11 );
