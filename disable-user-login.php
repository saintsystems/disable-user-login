<?php
/**
 * Plugin Name: Disable User Login
 * Plugin URI:  http://wordpress.org/plugins/disable-user-login
 * Description: Provides the ability to disable user accounts and prevent them from logging in.
 * Version:     1.0.0
 * Author:      Saint Systems
 * Author URI:  https://www.saintsystems.com
 * Text Domain: disable-user-login
 * License:     GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author    Saint Systems
 * @version   1.0.0
 * @package   Disable User Login
 * @license   GPL3
 * @author    Saint Systems
 * @link      http://www.saintsystems.com
 * @copyright Copyright 2019, Saint Systems, LLC
 *
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
