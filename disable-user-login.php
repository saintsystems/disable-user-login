<?php
/**
 * Plugin Name: Disable User Login
 * Plugin URI:  http://wordpress.org/plugins/disable-user-login
 * Description: Provides the ability to disable user accounts and prevent them from logging in.
 * x-release-please-start-version
 * Version:     2.0.0
 * x-release-please-end
 * Requires at least: 6.2
 * Requires PHP: 7.4
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
 * Check PHP and WordPress version requirements.
 *
 * WordPress 5.2+ checks Requires at least / Requires PHP headers on activation,
 * but this runtime guard covers manual file copies and downgrades.
 */
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Disable User Login requires PHP 7.4 or higher. Please upgrade PHP.', 'disable-user-login' )
		);
	} );
	return;
}

if ( version_compare( $GLOBALS['wp_version'], '6.2', '<' ) ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Disable User Login requires WordPress 6.2 or higher. Please update WordPress.', 'disable-user-login' )
		);
	} );
	return;
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

// Get Disable User Login Running.
add_action( 'plugins_loaded', 'SSDUL', 11 );
