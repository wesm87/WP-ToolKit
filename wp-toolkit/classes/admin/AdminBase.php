<?php
/**
 * Contains the AdminBase class
 *
 * @package WP-ToolKit
 */

namespace WPTK\Admin;
use WPTK\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base class that any admin-related classes should extend from
 */
class AdminBase extends Core\BaseClass {
	/**
	 * An array of whitelisted MIME types to check when uploading files
	 * e.g. [ 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/gif', ]
	 *
	 * @var array $upload_mime_types
	 */
	protected static $upload_mime_types = [];

	/**
	 * An array of options page titles and/or config arrays
	 *
	 * @var array $options_pages
	 */
	protected static $options_pages = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		parent::__construct();

		Utils\log( 'AdminBase init' );

		$this->add_action( 'admin_init', 'on_admin_init' );

		// Add custom scripts and styles for admin pages.
		$this->add_action( 'admin_enqueue_scripts', 'admin_scripts_and_styles' );

		// Add ACF options pages.
		$this->add_action( 'wp_loaded', 'add_options_pages' );

		// Configure upload MIME types.
		if ( ! empty( self::$upload_mime_types ) ) {
			$this->add_filter( 'upload_mimes', 'filter_upload_mime_types' );
		}
	}

	/**
	 * Called when the `admin_init` action fires
	 */
	public function on_admin_init() {

	}

	/**
	 * Add custom scripts and styles for admin pages
	 */
	public function admin_scripts_and_styles() {

	}

	/**
	 * Add or remove allowed upload mime types. Controls which file types
	 * will be accepted or rejected by the media uploader.
	 */
	public function filter_upload_mime_types() {

	}

	/**
	 * Registers custom ACF options pages for your plugin or theme.
	 */
	public function add_acf_options_pages() {
		if ( ! empty( self::$options_pages ) ) {
			foreach ( self::$options_pages as $page ) {
				$this->add_options_page( $page );
			}
		}
	}

	/**
	 * Registers a custom ACF options page for your plugin or theme.
	 *
	 * @param mixed $page A string for the page title, or an array of settings. If left blank, default settings will be used.
	 */
	public function add_acf_options_page( $page ) {
		if ( function_exists( 'acf_add_options_page' ) ) {
			return acf_add_options_page( $page );
		} else {
			Utils\log( 'acf_add_options_page() not found' );
		}
	}
}
