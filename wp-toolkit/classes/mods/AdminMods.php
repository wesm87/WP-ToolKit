<?php
/**
 * Contains the ModsBase class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Mods;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains admin-related mods.
 */
class AdminMods extends ModsBase {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->maybe_hide_mu_plugins();
		$this->add_action( 'admin_init', 'remove_dashboard_widgets' );
	}

	/**
	 * Hides the "Must-Use" plugins section on production servers.
	 */
	public function maybe_hide_mu_plugins() {
		if ( WP_ENV === 'production' ) {
			$this->add_filter( 'show_advanced_plugins', '__return_false', 0, 2 );
		}
	}

	/**
	 * Removes unnecessary dashboard widgets.
	 *
	 * @link http://www.deluxeblogtips.com/2011/01/remove-dashboard-widgets-in-wordpress.html
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
	}
}
