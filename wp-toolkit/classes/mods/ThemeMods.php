<?php
/**
 * Contains the ThemeMods class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Mods;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains any theme-related custom mods.
 */
class ThemeMods extends ModsBase {
	/**
	 * Can be 'google-cdn', 'jquery-cdn', or 'local'.
	 *
	 * @var string $jquery_source
	 */
	public static $jquery_source = 'google-cdn';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->head_cleanup();
		$this->add_action( 'wp_enqueue_scripts', 'move_js_to_footer' );
		$this->add_action( 'wp_enqueue_scripts', 'update_jquery_source', 100 );
		$this->add_action( 'wp_head', 'jquery_local_fallback' );
	}

	/**
	 * Removes a lot of unnecessary code from the <head> section.
	 */
	public function head_cleanup() {
		// RSS links.
		$this->remove_action( 'wp_head', 'feed_links_extra', 3 );
		$this->remove_action( 'wp_head', 'rsd_link' );

		// Manifest link.
		$this->remove_action( 'wp_head', 'wlwmanifest_link' );

		// Prev / next post links.
		$this->remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

		// Generator tag.
		$this->remove_action( 'wp_head', 'wp_generator' );
		$this->add_filter( 'the_generator', '__return_false' );

		// Shortlink.
		$this->remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );

		// Emoji scripts & styles.
		$this->remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		$this->remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		$this->remove_action( 'wp_print_styles', 'print_emoji_styles' );
		$this->remove_action( 'admin_print_styles', 'print_emoji_styles' );
		$this->remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		$this->remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		$this->remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Inline gallery styles.
		$this->add_filter( 'use_default_gallery_style', '__return_false' );
	}

	/**
	 * Moves all scripts to wp_footer.
	 */
	public function move_js_to_footer() {
		$this->remove_action( 'wp_head', 'wp_print_scripts' );
		$this->remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
		$this->remove_action( 'wp_head', 'wp_enqueue_scripts', 1 );
	}

	/**
	 * Sets the jQuery source URL to either the Google or jQuery CDN
	 * and adds a fallback to load the local version if the CDN fails.
	 */
	public function update_jquery_source() {
		$jquery_url = $this->get_jquery_url();
		if ( ! empty( $jquery_url ) ) {
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', $jquery_url, [], null, true );
			$this->add_filter( 'script_loader_src', 'jquery_local_fallback', 10, 2 );
		}
	}

	/**
	 * Gets the URL for the current version of jQuery.
	 */
	protected function get_jquery_url() {
		$version = $GLOBALS['wp_scripts']->registered['jquery']->ver;
		$script_debug = ( defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false );
		$ext = ( $script_debug ? '.min' : '' ) . '.js';
		$source = static::$jquery_source;

		switch ( $source ) {
			case 'google-cdn' :
				return 'https://ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery' . $ext;
			break;
			case 'jquery-cdn' :
				return 'https://code.jquery.com/jquery-' . $version . $ext;
			break;
		}
	}

	/**
	 * Outputs a script that checks for the global jQuery object and, if it
	 * doesn't exist, outputs a script to load jQuery from a fallback source.
	 *
	 * @param string $source The current source URL.
	 * @param string $handle The handle for the current script.
	 */
	function jquery_local_fallback( $source, $handle = null ) {
		// Make sure we're modifying the jQuery source URL.
		if ( 'jquery' === $handle ) {
			// Make sure we only output the fallback script once.
			if ( 0 === did_action( 'output_jquery_local_fallback' ) ) {
				do_action( 'output_jquery_local_fallback' );
				$fallback_source = apply_filters( 'script_loader_src', includes_url( '/js/jquery/jquery.js' ), 'jquery-fallback' );
				echo '<script>window.jQuery || document.write(\'<script src="' . $fallback_source .'"><\/script>\')</script>' . "\n";
			}
		}

		return $source;
	}
}
