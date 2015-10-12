<?php
/**
 * Contains the Autoloader class.
 *
 * @package WP-ToolKit
 **/

namespace WPTK\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatically loads includes class files when that class is instantiated,
 * referenced, extended from, etc.
 */
class Autoloader {
	/**
	 * The base path to look for the class files in.
	 *
	 * @var $base_path;
	 **/
	public static $base_path = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		spl_autoload_register( [ $this, 'load_class' ] );
	}

	/**
	 * Locates the class file on autoload.
	 *
	 * @param string $ns_and_class The name of the class that's being loaded. If a namespace is defined it's included.
	 */
	public function load_class( $ns_and_class ) {
		$ds = DIRECTORY_SEPARATOR;
		$base_path = ( ! empty( static::$base_path ) ? static::$base_path : __DIR__ );

		// Split the namespace up into chunks.
		$parts = explode( '\\', $ns_and_class );

		// Get the class name and remove it from the $parts array.
		$class_name = array_pop( $parts );

		// Get the namespace root and remove it from the $parts array.
		$root_ns = array_shift( $parts );

		// Stop if the namespace root doesn't match the plugin namespace.
		if ( 'WPTK' !== $root_ns ) {
			return null;
		}

		// Convert the namespace parts to lower-case.
		$namespace_parts = array_map( 'strtolower', $parts );

		// Prepend the base path and the class folder.
		array_unshift( $namespace_parts, $base_path, 'classes' );

		// Append the class name.
		array_push( $namespace_parts, $class_name );

		// Re-assamble the namespace parts into a file path.
		$class_file = implode( $ds, $namespace_parts ) . '.php';

		// Include the class file if it exists.
		if ( file_exists( $class_file ) ) {
			require_once( $class_file );
			return true;
		}

		return false;
	}
}
