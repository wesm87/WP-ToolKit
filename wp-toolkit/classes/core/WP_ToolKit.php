<?php
/**
 * Contains the WP_Toolkit class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Core;
use WPTK\Utils;
use WPTK\Mods;
use WPTK\Plugin;
use WPTK\Theme;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main WP_ToolKit class.
 */
class WP_ToolKit extends Plugin\PluginBase {
	/**
	 * Constructor.
	 **/
	public function __construct() {
		$mods = [
			'wptk-site-mods',
			'wptk-admin-mods',
			'wptk-theme-mods',
			'wptk-relative-links',
			'wptk-nav-cleanup',
		];
		$this->enable_mods( $mods );
		Utils\log( $mods );

		$this->add_action( 'wp_loaded', 'load_enabled_mods' );
	}

	/**
	 * Called when the "plugins_loaded" action is fired.
	 */
	public function on_plugin_loaded() {
		// Action hook callbacks.
		$this->add_action( 'init', 'init_post_types' );
		$this->add_action( 'init', 'init_taxonomies' );
		$this->add_action( 'widgets_init', 'init_widgets' );
	}

	/**
	 * Check if any of our custom mods are enabled and, if so, create a new
	 * instance of each one's corresponding class
	 */
	public function load_enabled_mods() {
		if ( is_admin() ) {
			if ( $this->is_mod_enabled( 'wptk-admin-mods' ) ) {
				Utils\log( 'Admin mods enabled' );
				new Mods\AdminMods();
			}
		} else {
			if ( $this->is_mod_enabled( 'wptk-theme-mods' ) ) {
				Utils\log( 'Theme mods enabled' );
				new Mods\ThemeMods();
			}
		}

		if ( $this->is_mod_enabled( 'wptk-core-mods' ) ) {
			Utils\log( 'Core mods enabled' );
			new Mods\CoreMods();
		}

		if ( $this->is_mod_enabled( 'wptk-site-mods' ) ) {
			Utils\log( 'Site mods enabled' );
			new Mods\SiteMods();
		}

		if ( $this->is_mod_enabled( 'wptk-relative-links' ) ) {
			Utils\log( 'Relative links mod enabled' );
			new Mods\RelativeLinksMod();
		}

		if ( $this->is_mod_enabled( 'wptk-nav-cleanup' ) ) {
			Utils\log( 'Nav mods enabled' );
			new Mods\NavMods();
		}
	}

	/**
	 * Registers custom post types
	 */
	public function init_post_types() {
		/*
		$labels = Utils\fill_labels( 'Custom Post Type' );
		$args = array(
			'labels'  => $labels,
			'public'  => true,
			'show_ui' => true,
		);
		register_post_type( 'wptk_custom_post_type', $args );
		*/
	}

	/**
	 * Registers custom taxonomies
	 */
	public function init_taxonomies() {
		/*
		$post_types = array(
			'post',
		);
		$labels = Utils\fill_labels( 'Custom Tax' );
		$args = array(
			'labels' 				=> $labels,
			'public' 				=> true,
			'show_ui' 				=> true,
			'hierarchical' 			=> true,
			'exclude_from_search' 	=> true,
			'show_in_nav_menus' 	=> true,
			'show_in_quick_edit' 	=> true,
			'show_admin_column' 	=> true,
		);
		register_taxonomy( 'wptk_custom_tax', $post_types, $args );
		*/
	}

	/**
	 * Registers custom widget(s)
	 */
	public function init_widgets() {

	}
}
