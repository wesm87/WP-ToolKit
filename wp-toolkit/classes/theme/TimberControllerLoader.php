<?php
/**
 * Contains the TimberControllerLoader class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Theme;
use WPTK\Core;
use WPTK\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Overrides the default WordPress template loader behavior to redirect
 * standard page templates to Timber controllers (but only if the Timber
 * plugin is installed and the corresponding controller file exists)
 */
class TimberControllerLoader extends ThemeBase {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->add_action( 'template_include', 'maybe_load_controller', 99 );
	}

	/**
	 * Get a list of potential controller files from the current URL
	 * using a method loosely based on the WordPress template heirarchy.
	 * Load the controller if one is found, otherwise fall back on the
	 * core template loader.
	 *
	 * @param string $template The full path to the template file that WordPress intends to use for the current page.
	 */
	public function maybe_load_controller( $template ) {
		// Do nothing if the Timber plugin is not installed and active.
		if ( ! class_exists( 'Timber' ) ) {
			return $template;
		}

		// See if the current page has a corresponding controller.
		$controller = $this->locate_controller();

		// If not, abort mission now.
		if ( empty( $controller ) ) {
			// Log the template file being loaded.
			$rel_path = str_replace( get_theme_root(), '', $template );
			Utils\log( "Loaded WordPress template at: {$rel_path}" );

			// Return the template file.
			return $template;
		}

		// Log the controller file being loaded.
		$rel_path = str_replace( get_theme_root(), '', $controller );
		Utils\log( "Loaded Timber controller at: {$rel_path}" );

		return $controller;
	}

	/**
	 * Create a list of controllers based on various Conditional Tags
	 */
	protected function locate_controller() {
		// Set the default controller names.
		$controller_names = array( 'base' );

		// Check tags in order of least specific to most specific.
		switch ( true ) {
			// Archive pages.
			case is_archive() :
				// Post archive.
				array_unshift( $controller_names, 'archive', 'archives' );
			case is_date() :
				// Date-based archive.
				array_unshift( $controller_names, 'date' );
			break;
			case is_author() :
				// Author posts.
				array_unshift( $controller_names, 'author' );
			break;
			case is_tax() :
				// Taxonomy terms.
				array_unshift( $controller_names, 'term', 'taxonomy' );
			case is_category() :
				// Categories.
				array_unshift( $controller_names, 'category' );
			break;
			case is_tag() :
				// Tags.
				array_unshift( $controller_names, 'tag' );
			break;
			case is_post_type_archive() :
				// Post type archive.
				array_unshift( $controller_names, 'single' );
			break;

			// Singular pages, posts, and post types.
			case is_singular() :
				array_unshift( $controller_names, 'singular' );

				if ( is_single() ) {
					// Single post.
					array_unshift( $controller_names, 'single' );
				} else if ( is_page() ) {
					// Single page.
					array_unshift( $controller_names, 'page' );

					// Check if this is a page using a custom page template.
					$page_template_slug = get_page_template_slug();
					if ( $page_template_slug ) {
						// Get just the basename of the template file.
						$base = basename( $page_template_slug, '.php' );

						// Add the basename to the controller list.
						array_unshift( $controller_names, $base );
					}

					// Static front page.
					if ( is_front_page() ) {
						array_unshift( $controller_names, 'front-page' );
					}
				}
			break;

			// Blog posts page.
			case is_home() :
				array_unshift( $controller_names, 'home', 'news', 'blog' );
			break;

			// Search page.
			case is_search() :
				array_unshift( $controller_names, 'search' );
			break;

			// 404 page.
			case is_404() :
				array_unshift( $controller_names, '404' );
			break;
		}

		/**
		 * Now that that monumental task is over with, lets loop
		 * through the controller names to prepend the base paths
		 * and append a .php extension to each controller name.
		 */
		$controller_paths = array();
		foreach ( $controller_names as $name ) {
			array_push(
				$controller_paths,
				"controllers/_core/{$name}.php",
				"controllers/{$name}.php"
			);
		}

		// Log the controller name list.
		Utils\log( $controller_names );

		/**
		 * Look through the entire array of controller file paths.
		 * If any of the files exists, return the first one found.
		 * If none of theme exist, return an empty string.
		 */
		return locate_template( $controller_paths );
	}
}
