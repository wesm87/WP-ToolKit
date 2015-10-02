<?php
/**
 * Contains the ThemeBase class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Theme;
use WPTK\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base class that all theme-related classes should extend from.
 */
class ThemeBase extends Core\BaseClass {
	/**
	 * The ID of the TypeKit font kit you want to use. Leave blank to disable.
	 *
	 * @var string $typekit_id
	 */
	protected static $typekit_id;

	/**
	 * Controls whether TypeKit will be loaded asynchronously.
	 *
	 * @var string $typekit_async
	 */
	protected static $typekit_async = true;

	/**
	 * Controls where the TypeKit embed script will be output.
	 * Can be either "header" or "footer".
	 *
	 * @var string $typekit_location
	 */
	protected static $typekit_location = 'header';

	/**
	 * If set to true, all pages will redirect to the login page if the
	 * curent user is not logged in.
	 *
	 * @var bool $require_user_login
	 */
	protected static $require_user_login = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		if ( $this->is_admin() ) {
			return;
		}

		$this->add_action( 'after_setup_theme', 'on_theme_loaded' );

		$this->check_for_typekit();

		// Require user login if option is set to true.
		if ( static::$require_user_login ) {
			$this->add_action( 'send_headers', 'require_user_login' );
			$this->add_filter( 'login_redirect', 'login_redirect', 10, 3 );
		}
	}

	/**
	 * Called after the active theme has been loaded.
	 */
	public function on_theme_loaded() {

	}

	/**
	 * Called when the "init" action fires.
	 */
	public function on_wp_init() {

	}

	/**
	 * Checks if the current user is logged in and, if not, redirects
	 * to the login page.
	 */
	public function require_user_login() {
		if ( ! is_user_logged_in() ) {
			Utils\log( 'User is not logged in' );
			$login_page_url = wp_login_url( get_permalink() );
			wp_redirect( $login_page_url );
		}
	}

	/**
	 * Redirect user to the front page after successful login.
	 *
	 * @param string $redirect_to The default redirect URL.
	 * @param string $request	 The URL the user is coming from.
	 * @param object $user		The current user's data.
	 * @return string
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		return home_url();
	}

	/**
	 * Returns the URL to the assets folder. If a path is specified,
	 * it appends the path to the URL before returning it.
	 *
	 * @param string $path An optional path to append to the assets folder URL.
	 * @return string
	 */
	public function get_assets_url( $path = '' ) {
		$assets_url = get_template_directory_uri() . '/_assets';
		if ( $path ) {
			$assets_url .= '/' . $path;
		}

		return wp_make_link_relative( $assets_url );
	}

	/**
	 * Add any data you want to be accessible within any Timber context here
	 *
	 * @param array $context The main Timber context.
	 */
	public function timber_context_add_data( $context ) {
		return $context;
	}

	/**
	 * Outputs the TypeKit embed code if enabled.
	 */
	public function output_typekit_embed_code() {
		// Get Typekit ID.
		$id = static::$typekit_id;

		// Get async setting as a string (e.g. 'true' or 'false').
		$async = static::$typekit_async ? 'true' : 'false';

		// Only output the code if we have a kit ID to use.
		if ( ! $id ) {
			return;
		}

		// Get the TypeKit URL.
		$script_src = $this->get_typekit_url();

		// @codingStandardsIgnoreStart
		?>
		<script src="<?php echo $script_src; ?>"></script>
		<script>try{Typekit.load({async: <?php echo $async; ?>});}catch(e){}</script>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Checks if TypeKit is enabled and schedules the embed code output if so.
	 */
	public function check_for_typekit() {
		// Output the embed code in the appropriate location.
		$location = static::$typekit_location;
		if ( ( 'header' === $location && $action = 'wp_head' )
		  || ( 'footer' === $location && $action = 'wp_footer' )
		) {
			// Make sure we only output the embed code once.
			if ( 0 === did_action( 'enable_typekit' ) ) {
				do_action( 'enable_typekit' );
				$this->add_action( $action, 'output_typekit_embed_code', 1 );
			}
		}
	}

	/**
	 * Checks if a TypeKit ID has been set and, if so, returns the TypeKit
	 * script embed URL with the kid ID included.
	 */
	protected function get_typekit_url() {
		$id = static::$typekit_id;
		if ( $id ) {
			return "https://use.typekit.net/{$id}.js";
		}
	}
}
