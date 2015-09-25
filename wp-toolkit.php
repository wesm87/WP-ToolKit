<?php
/**
 * Plugin Name: WP ToolKit
 * Description: Core functions, classes, scripts, styles, actions, filters, shortcodes, options pages, etc. Contains any functionality that isn't tied to the active theme and should persist between theme changes.
 * Author: Wes Moberly
 * Version: 0.1
 *
 * @package WP-ToolKit
 */

namespace WPTK\Core;
use WPTK\Mods;
use WPTK\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Change default log file location.
ini_set( 'error_log', CJ_APP_DIR . '/logs/debug.log' );

/*
 * If a custom default theme isn't defined, register
 * the default theme folder path as a fallback.
 */
if ( ! defined( 'WP_DEFAULT_THEME' ) ) {
	register_theme_directory( ABSPATH . 'wp-content/themes' );
}

// Include the Autoloader class file.
require_once( __DIR__ . '/classes/core/Autoloader.php' );

// Instantiate the required classes.
new Autoloader();
new WP_ToolKit();
