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

$plugin_dir = __DIR__ . '/wp-toolkit';

// If WP_ENV isn't already set, see if we're currently running a unit test.
// If not, set it to development.
if ( ! defined( 'WP_ENV' ) ) {
	$env = defined( 'WP_TESTS_TABLE_PREFIX' ) ? 'unit-test' : 'development';
	define( 'WP_ENV', $env );
}

// Change default log file location.
ini_set( 'error_log', WP_CONTENT_DIR . '/logs/debug.log' );

// If a custom default theme isn't defined, register
// the default theme folder path as a fallback.
if ( ! defined( 'WP_DEFAULT_THEME' ) ) {
	register_theme_directory( ABSPATH . 'wp-content/themes' );
}

// Include the Autoloader class file.
require_once( $plugin_dir . '/classes/core/Autoloader.php' );

// Include namespaced functions.
require_once( $plugin_dir . '/functions.php' );

// Instantiate the required classes.
Autoloader::$base_path = $plugin_dir;
new Autoloader();
new WP_ToolKit();
