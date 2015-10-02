<?php
/**
 * Contains the SiteMods class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Mods;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains any site-related custom mods.
 */
class SiteMods extends ModsBase {
	/**
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Disable search indexing if not on production server
		 */
		if ( WP_ENV !== 'production' && ! \is_admin() ) {
			\add_action( 'pre_option_blog_public', '__return_zero' );
		}

		$this->add_action( 'template_redirect', 'search_redirect' );
		$this->add_filter( 'wpseo_json_ld_search_url', 'search_rewrite' );
	}

	/**
	 * Clean up search URLs - "?s=search%20terms" becomes "/search/search+terms"
	 */
	public function search_redirect() {
		global $wp_rewrite;
		if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->get_search_permastruct() ) {
			return;
		}

		// @codingStandardsIgnoreStart
		$request_uri = sanitize_key( $_SERVER['REQUEST_URI'] );
		// @codingStandardsIgnoreEnd

		// Check whether the search URL has already been rewritten.
		$search_base = $wp_rewrite->search_base;
		$is_new_search_url = ( 0 === strcmp( $request_uri, $search_base ) );

		if ( is_search() && ! is_admin() && ! $is_new_search_url ) {
			wp_redirect( get_search_link() );
			exit();
		}
	}

	/**
	 * Updates the search URL used by WP SEO.
	 *
	 * @param string $url The default search URL.
	 */
	public function search_rewrite( $url ) {
		return \str_replace( '/?s=', '/search/', $url );
	}
}
