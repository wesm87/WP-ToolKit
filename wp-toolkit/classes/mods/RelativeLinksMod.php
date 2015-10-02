<?php
/**
 * Contains the RelativeLinksMod class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Mods;
use WPTK\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Makes all links relative.
 */
class RelativeLinksMod extends ModsBase {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( $this->enable_relative_urls() ) {
			$link_filters = [
				'bloginfo_url',
				'the_permalink',
				'wp_list_pages',
				'wp_list_categories',
				'wp_get_attachment_url',
				'the_content_more_link',
				'the_tags',
				'get_pagenum_link',
				'get_comment_link',
				'month_link',
				'day_link',
				'year_link',
				'term_link',
				'the_author_posts_link',
				'script_loader_src',
				'style_loader_src',
			];

			Utils\log( $link_filters );

			$this->add_filters( $link_filters, 'make_link_relative' );
		}
	}

	/**
	 * Check whether we should rewrite the links on the current page.
	 */
	protected function enable_relative_urls() {
		// @codingStandardsIgnoreStart
		$request_uri = sanitize_key( $_SERVER['REQUEST_URI'] );
		// @codingStandardsIgnoreEnd

		$is_sitemap_url = preg_match( '/sitemap(_index)?\.xml/', $request_uri );
		$is_register_or_login_url = in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ] );

		return ! ( is_admin() || $is_sitemap_url || $is_register_or_login_url );
	}

	/**
	 * Takes the existing URL passed from each filter function, converts it
	 * to a relative URL, and returns it.
	 *
	 * @param string $input The URL to make relative.
	 * @return string
	 */
	public function make_link_relative( $input ) {
		// Make sure the host and path are set.
		$url = parse_url( $input );
		if ( ! isset( $url['host'] ) || ! isset( $url['path'] ) ) {
			return $input;
		}

		// Make sure the scheme for the current URL matches the one used
		// on the home page.
		$site_url = parse_url( network_site_url() );
		if ( ! isset( $url['scheme'] ) ) {
			$url['scheme'] = $site_url['scheme'];
		}

		// Make sure the hosts, schemes, and ports (if set) for both URLs match.
		$hosts_match = ( $site_url['host'] === $url['host'] );
		$schemes_match = ( $site_url['scheme'] === $url['scheme'] );
		$ports_exist = ( isset( $site_url['port'] ) && isset( $url['port'] ) );
		$ports_match = ( $ports_exist ) ? ( $site_url['port'] === $url['port'] ) : true;

		// If so, make the URL relative and return it.
		if ( $hosts_match && $schemes_match && $ports_match ) {
			return wp_make_link_relative( $input );
		}

		// If not, return the original URL.
		return $input;
	}
}
