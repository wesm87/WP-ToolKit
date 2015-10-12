<?php
use WPTK\Utils;

class TestUtilFunctions extends WP_UnitTestCase {

	function test_log() {
		// Make sure we don't log any messages since PHPUnit considers sees
		// them as errors and immediately stops the tests.
		$result = Utils\log( 'No logging during unit tests' );
		$this->assertFalse( $result );
	}

	/**
	 * @TODO: Add tests for disable_mod(), disable_mods(), is_mod_disabled()
	 */
	function test_mods() {
		$mod = 'wp_unit_test_util_fn_mod';
		$mod_list = array(
			'wp_unit_test_util_fn_mod_1',
			'wp_unit_test_util_fn_mod_2',
		);

		// Make sure our non-existent mod returns false.
		$result = Utils\is_mod_enabled( $mod );
		$this->assertFalse( $result, '`WPTK\\Utils\\is_mod_enabled` should return false for custom mods that have not been enabled yet.' );

		// Enable the mod and check again.
		Utils\enable_mod( $mod );
		$result = Utils\is_mod_enabled( $mod );
		$this->assertTrue( $result, '`WPTK\\Utils\\is_mod_enabled` should return true for custom mods that have been enabled.' );

		// Try an array of mods.
		Utils\enable_mods( $mod_list );
		$result = Utils\is_mod_enabled( $mod_list[0] );
		$this->assertTrue( $result, 'Every mod in an array should be enabled when passed to `WPTK\\Utils\\enable_mods`.' );
		$result = Utils\is_mod_enabled( $mod_list[1] );
		$this->assertTrue( $result, 'Every mod in an array should be enabled when passed to `WPTK\\Utils\\enable_mods`.' );
	}

	function test_enqueue_scripts_and_styles() {
		// Make sure we don't try to enqueue anything that isn't a CSS or JS file.
		$result = Utils\enqueue_script_or_style( 'not-css-or-js', 'path/to.file', null, null, null, null );
		$this->assertFalse( $result, 'Only CSS or JS files should be enqueued' );
	}

	/**
	 * @todo: Make sure the .min is removed from the returned URL when
	 * `SCRIPT_DEBUG` is enabled.
	 */
	function test_get_asset_location() {
		// Relative URL.
		$url = '//path/to/style.min.css';
		$result = Utils\get_asset_url( $url, 'css' );
		$this->assertEquals( $url, $result );

		// HTTP URL
		$url = 'http://path/to/style.min.css';
		$result = Utils\get_asset_url( $url, 'css' );
		$this->assertEquals( $url, $result );

		// HTTPS URL
		$url = 'https://path/to/style.min.css';
		$result = Utils\get_asset_url( $url, 'css' );
		$this->assertEquals( $url, $result );

		// Absolute path
		$path = '/path/to/style.min.css';
		$result = Utils\get_asset_url( $path, 'css' );
		$this->assertContains( $path, $result );

		// Relative path
		$path = 'path/to/style.min.css';
		$result = Utils\get_asset_url( $path, 'css' );
		$this->assertContains( $path, $result );
	}
}
