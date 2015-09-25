<?php
/**
 * Contains the ModsBase class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Mods;
use WPTK\Core;
use WPTK\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base class that other mod classes should extend from.
 */
class ModsBase extends Core\BaseClass {
	/**
	 * Constructor.
	 */
	public function __construct() {
		Utils\log( 'ModsBase class initialized' );
	}
}
