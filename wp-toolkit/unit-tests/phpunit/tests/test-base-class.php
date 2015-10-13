<?php

function wptk_dummy_fn() {
	return 'wptk_dummy_fn';
}

class TestBaseClass extends WP_UnitTestCase {

	private $plugin;

	function setUp() {
		parent::setUp();
		$this->plugin = new WPTK\Core\BaseClass();
	}

	function dummy_fn() {
		return 'dummy_fn';
	}

	function test_wp_env() {
		// Make sure WP_ENV is defined and is set to "unit-test".
		$this->assertTrue( defined( 'WP_ENV' ), '`WP_ENV` is not defined.' );
		$this->assertEquals( 'unit-test', WP_ENV, '`WP_ENV` should be set to `unit-test` when running plugin unit tests.' );
	}

	function test_autoloader() {
		// Make sure our Autoloader class exists.
		$this->assertTrue( class_exists( 'WPTK\\Core\\Autoloader' ), 'The `Autoloader` class does not exist.' );

		// ...and that the base path includes the wp-toolkit plugin folder.
		$this->assertStringEndsWith( 'plugins/wp-toolkit', WPTK\Core\Autoloader::$base_path, '`Autoloader::$base_path` must be set properly before initializing the plugin.' );

		$autoloader = new WPTK\Core\Autoloader();

		// Make sure the Autoloader doesn't load classes that aren't in the
		// plugin namespace (in which case it returns null).
		$result = $autoloader->load_class( 'NotThisPlugin\\Some\\Other\\Class' );
		$this->assertEquals( null, $result, 'The `Autoloader` class should return `null` for classes that are outside of the plugin namespace.' );

		// Make sure it loads classes that exist.
		$result = $autoloader->load_class( 'WPTK\\Core\\BaseClass' );
		$this->assertTrue( $result, 'The `Autoloader` class should return `true` for classes that exist inside the plugin namespace.' );

		// ...and not ones that don't exist.
		$result = $autoloader->load_class( 'WPTK\\Nothing\\NoClassHere' );
		$this->assertFalse( $result, 'The `Autoloader` class should return `false` for classes that do not exist inside the plugin namespace.' );
	}

	function test_get_function() {
		// Make sure we can find functions in BaseClass and its sub-classes.
		$fn = $this->plugin->get_function( 'on_wp_init' );
		$this->assertNotInstanceOf( 'WP_Error', $fn, '`BaseClass#on_wp_init` should exist but it wasn\'t found.' );

		// Make sure we can find functions in specific class instances.
		$fn = $this->plugin->get_function( 'dummy_fn', $this );
		$this->assertNotInstanceOf( 'WP_Error', $fn, '`TestBaseClass#dummy_fn` should exist but it wasn\'t found.' );

		// Make sure we can find functions in the current namespace.
		$fn = $this->plugin->get_function( 'wptk_dummy_fn', $this );
		$this->assertNotInstanceOf( 'WP_Error', $fn, 'The `wptk_dummy_fn` function should exist in `test-base-class.php` but it wasn\'t found.' );

		// Make sure we're getting a WP_Error object for non-existent functions.
		$fn = $this->plugin->get_function( 'some_ridiculous_function_name_that_hopefully_does_not_exist' );
		$this->assertInstanceOf( 'WP_Error', $fn, '`BaseClass#get_function` should return a `WP_Error` object for functions that don\'t exist.' );

		// Make sure the __call() magic method works for functions that exist.
		$result = $this->plugin->wptk_dummy_fn();
		$this->assertEquals( 'wptk_dummy_fn', $result, '`BaseClass#__call` should return the result of the function that was called when that function exists.' );

		// ...and returns false for functions that don't exist.
		$result = $this->plugin->some_ridiculous_function_name_that_hopefully_does_not_exist();
		$this->assertFalse( $result, '`BaseClass#__call` should return false when the function called does not exist.' );
	}

	/**
	 * @todo: Add tests for disable_mod(), disable_mods(), is_mod_disabled()
	 */
	function test_mods() {
		$mod = 'wp_unit_test_base_mod';
		$mod_list = array(
			'wp_unit_test_base_mod_1',
			'wp_unit_test_base_mod_2',
		);

		// Make sure our non-existent mod returns false.
		$result = $this->plugin->is_mod_enabled( $mod );
		$this->assertFalse( $result, '`BaseClass#is_mod_enabled` should return false for custom mods that have not been enabled yet.' );

		// Enable the mod and check again.
		$this->plugin->enable_mod( $mod );
		$result = $this->plugin->is_mod_enabled( $mod );
		$this->assertTrue( $result, '`BaseClass#is_mod_enabled` should return true for custom mods that have been enabled.' );

		// Try an array of mods.
		$this->plugin->enable_mods( $mod_list );
		$result = $this->plugin->is_mod_enabled( $mod_list[0] );
		$this->assertTrue( $result, 'Every mod in an array should be enabled when passed to `BaseClass#enable_mods`.' );
		$result = $this->plugin->is_mod_enabled( $mod_list[1] );
		$this->assertTrue( $result, 'Every mod in an array should be enabled when passed to `BaseClass#enable_mods`.' );
	}
}
