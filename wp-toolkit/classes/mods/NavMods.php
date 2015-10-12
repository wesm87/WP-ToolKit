<?php
/**
 * Contains the NavMods class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Mods;
use WPTK\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans up the HTML that's output for each nav menu.
 */
class NavMods extends ModsBase {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_filter( 'wp_nav_menu_args', 'nav_menu_args' );
		$this->add_filter( 'nav_menu_item_id', '__return_null' );
		$this->add_filter( 'nav_menu_css_class', 'nav_menu_css_class', 10, 2 );
		$this->add_filter( 'nav_menu_link_attributes', 'nav_menu_link_attributes', 10, 2 );
	}

	/**
	 * Removes unnecessary classes and replaces all the "current-menu-*" and
	 * "current-page-*" classes with a single "is-active" class.
	 *
	 * @param array  $classes An array containing the default nav menu item classes.
	 * @param object $item    An object containing the nav menu item data.
	 * @return array
	 */
	public function nav_menu_css_class( $classes, $item ) {
		$classes = preg_replace( '/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'is-active', $classes );
		$classes = preg_replace( '/^((menu|page)[-_\w+]+)+/', '', $classes );
		$classes = preg_replace( '/^active/', 'is-active', $classes );
		$classes[] = 'menu-item';
		$classes = array_unique( $classes );

		return array_filter( $classes, function( $element ) {
			$element = trim( $element );
			return ( ! empty( $element ) );
		});
	}

	/**
	 * Replaces the default menu link class and adds a role to each link.
	 *
	 * @param array  $atts An array containing the menu link HTML attributes.
	 * @param object $item An object containing the menu link data.
	 * @return array
	 */
	public function nav_menu_link_attributes( $atts, $item ) {
		$atts['class'] = 'menu-link';
		$atts['role'] = 'menuitem';

		return $atts;
	}

	/**
	 * Cleans up wp_nav_menu_args. Remove the menu container and removes the
	 * id attribute on nav menu items.
	 *
	 * @param array $args The default nav menu arguments.
	 * @return array
	 */
	function nav_menu_args( $args = '' ) {
		$nav_menu_args = [];
		$nav_menu_args['container'] = false;
		if ( ! $args['items_wrap'] ) {
			$nav_menu_args['items_wrap'] = '<ul class="menu">%3$s</ul>';
		}
		return array_merge( $args, $nav_menu_args );
	}
}
