<?php
/**
 * The base plugin class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Plugin;
use WPTK\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base class that all plugin classes should extend from.
 */
class PluginBase extends Core\BaseClass {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_action( 'plugins_loaded', 'on_plugin_loaded' );
	}

	/**
	 * Called after all plugins have been loaded. Override this function
	 * in your plugin classes and use in place of a constructor.
	 */
	public function on_plugin_loaded() {

	}
}
