<?php
/**
 * Namespaced Utility Functions.
 *
 * @package WP-ToolKit
 * @todo Add documentation for functions that don't have any.
 */

namespace WPTK\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function log( $message, $level = E_USER_NOTICE ) {
	// Make sure debug mode is enabled and we're not in a unit test.
	if ( false === WP_DEBUG || 'unit-test' === WP_ENV ) {
		return false;
	}

	$backtrace = debug_backtrace();
	$caller_function = $backtrace[1]['function'];
	$error_message_pre_wrap = '::' . $caller_function . ':: [ "';
	$error_message_post_wrap = '" ]';

	if ( is_wp_error( $message ) ) {
		$errors = $message->get_error_messages();
		foreach ( $errors as $error ) {
			trigger_error( $error_message_pre_wrap . $error . $error_message_post_wrap, $level );
		}
	} else {
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}
		trigger_error( $error_message_pre_wrap . $message . $error_message_post_wrap, $level );
	}
}

function get_config_file_contents( $file_path ) {
	if ( ! file_exists( $file ) ) {
		log( "No config file exists at: {$file_path}" );
		return false;
	}
	$file_contents = file_get_contents( $file_path );
	return json_decode( $file_contents, true );
}

/**
 * Enables a custom site / theme / plugin mod.
 *
 * @param string $mod The name of the mod you want to enable.
 */
function enable_mod( $mod ) {
	return add_theme_support( $mod );
}

/**
 * A shortcut function that takes an array of mod names and enables each
 * one individually.
 *
 * @param array $mods An array of mods you want to enable.
 */
function enable_mods( $mods ) {
	foreach ( (array) $mods as $mod ) {
		add_theme_support( $mod );
	}
}

/**
 * Returns true if the specified mod is enabled, or fale if not.
 *
 * @param string $mod The name of the mod you want to check.
 */
function is_mod_enabled( $mod ) {
	return current_theme_supports( $mod );
}

/**
 * Checks to see if the function object that was passed exists and is callable
 * and if not, tries to locate the function in another scope. Looks in $object
 * first, then the local namespace, then the global namespace.
 *
 * @param string|array|Closure $function_arg The function object to check against.
 * @param mixed                $object       An object or class instance to check for our function before we look anywhere else.
 */
function get_function( $function_arg, $object = null ) {
	if ( ! $object || empty( $object ) ) {
		return false;
	}

	$is_fn_name	= ( \is_string( $function_arg ) );
	$is_fn_array   = ( \is_array( $function_arg ) );
	$is_closure	= ( $function_arg instanceof \Closure );

	if ( $is_fn_name ) {
		/*
		 * Check the specified object first if one was passed.
		 * If not found or not passed, check the current class.
		 * If not found, check the local namespace
		 * If not found, check the global namespace
		 */
		$function_name = $is_fn_name;
		$function_list = array(
			array( $object, $function_arg ),
			$function_arg,
			"\{$function_arg}",
		);
		if ( is_object( $object ) ) {
			array_unshift( $function_list, array( $object, $function_arg ) );
		}
	} else if ( $is_fn_array || $is_closure ) {
		/*
		 * Check if valid ( Class, method ) or ( $object, method )
		 * array was passed, or if a closure was passed
		 */
		$function_name = ( $is_fn_array ) ? $function_arg[0] : 'closure';
		$function_list = array(
			$function_arg,
		);

		if ( $is_closure ) {
			$function_arg = '{closure}';
		}
	}

	if ( ! empty( $function_list ) ) {
		foreach ( $function_list as $function ) {
			if ( is_callable( $function ) ) {
				$fn_exists = (
					is_array( $function ) && method_exists( $function[0], $function[1] )
				 || is_string( $function ) &&\function_exists( $function )
				);
				if ( $fn_exists ) {
					return $function;
				}
			}
		}
	}

	/*
	 * If we haven't found a valid function, log an error message
	 * and return it in a WP_Error() object
	 */

	$error_message = "Could not find function with name \"{$function_arg}\". Ensure that the function exists either in the global scope or inside the main theme class, and double-check your code for spelling errors";
	$wp_errors = new \WP_Error();
	$wp_errors->add( 'function_not_found', $error_message );
	log( $wp_errors );

	return $wp_errors;
}

function enqueue_script_or_style( $asset_type, $asset_name, $asset_path, $deps, $version, $final_arg ) {
	if ( 'script' !== $asset_type && 'style' !== $asset_type ) {
		// Error handling goes here.
		return false;
	}

	$file_type = ( 'script' === $asset_type ) ? 'js' : 'css';
	$asset_url = get_asset_url( $asset_path, $file_type );

	if ( $asset_url ) {
		$version = ( SCRIPT_DEBUG ) ? time() : $version;
		if ( 'script' === $asset_type ) {
			wp_enqueue_script( $asset_name, $asset_url, $deps, $version, $final_arg );
		} else {
			wp_enqueue_style( $asset_name, $asset_url, $deps, $version, $final_arg );
		}
	}
}

function enqueue_script( $script_name, $script_path, $deps = array(), $version = null, $in_footer = true ) {
	enqueue_script_or_style( 'script', $script_name, $script_path, $deps, $version, $in_footer );
}

function enqueue_style( $style_name, $style_path, $deps = array(), $version = null, $media = 'all' ) {
	enqueue_script_or_style( 'style', $style_name, $style_path, $deps, $version, $media );
}

function register_nav_menu( $name, $description ) {
	register_nav_menu(
		$name,
		translate( $description, __NAMESPACE__ )
	);
}

function register_sidebar( $args ) {
	if ( ! is_string( $args ) && ! is_array( $args ) ) {
		return;
	}

	if ( is_string( $args ) ) {
		parse_str( $args, $args );
	}

	$translate_list = array(
		'name',
		'description',
	);
	foreach ( $args as $key => $value ) {
		if ( in_array( $key, $translate_list ) && is_string( $value ) ) {
			$args[ $key ] = translate( $value, __NAMESPACE__ );
		}
	}

	register_sidebar( $args );
}

function get_asset_url( $asset_location, $asset_type ) {
	// Return asset location if it appears to be a URL.
	if ( 'http' === substr( $asset_location, 0, 4 ) || '//' === substr( $asset_location, 0, 2 ) ) {
		return $asset_location;
	}

	// For now this function assumes that the asset is
	// located somewhere inside the parent theme folder
	// TODO: Add support for child theme assets.
	$asset_url_base = get_template_directory_uri();

	// Check whether asset path is absolute or relative.
	$is_absolute_path = ( '/' === substr( $asset_location, 0, 1 ) );

	// If the path is relative we use the asset's build folder as the root path
	// e.g. CSS is /assets/css/build, JS is /assets/js/build, etc.
	if ( ! $is_absolute_path ) {
		$asset_url_base .= "/_assets/{$asset_type}/build/";
	}

	if ( SCRIPT_DEBUG ) {
		$asset_location = str_replace( ".min.{$asset_type}", ".{$asset_type}", $asset_location );
	}

	// Assemble the full asset URL and return it.
	// TODO: Check if asset file exists, if not return null or WP_Error.
	$asset_url = $asset_url_base . $asset_location;

	return $asset_url;
}

function fill_post_type_labels( $labels ) {
	return fill_labels( $labels );
}

function fill_taxonomy_labels( $labels ) {
	return fill_labels( $labels );
}

function fill_labels( $labels ) {
	if ( ! is_array( $labels ) && ! is_string( $labels ) ) {
		return null;
	}
	if ( is_string( $labels ) ) {
		$singular_name 	= $labels;
		$name			= $singular_name . 's';
		$labels = array(
			'name' => $name,
			'singular_name' => $singular_name,
		);
	}

	$name = '';
	$name_lower = '';
	$singular_name = '';
	$singular_name_lower = '';

	if ( ! isset( $labels['name'] ) && isset( $labels['singular_name'] ) ) {
		$labels['name'] = $labels['singular_name'] . 's';
	}

	if ( isset( $labels['name'] ) ) {
		$name = $labels['name'];
		$name_lower = strtolower( $name );

		if ( ! isset( $labels['search_items'] ) ) {
			$labels['search_items'] = "Search {$name}";
		}
		if ( ! isset( $labels['popular_items'] ) ) {
			$labels['popular_items'] = "Popular {$name}";
		}
		if ( ! isset( $labels['all_items'] ) ) {
			$labels['all_items'] = "All {$name}";
		}
		if ( ! isset( $labels['not_found'] ) ) {
			$labels['not_found'] = "No {$name_lower} found";
		}
		if ( ! isset( $labels['not_found_in_trash'] ) ) {
			$labels['not_found_in_trash'] = "No {$name_lower} found in trash";
		}
		if ( ! isset( $labels['menu_name'] ) ) {
			$labels['menu_name'] = $name;
		}
		if ( ! isset( $labels['separate_items_with_commas'] ) ) {
			$labels['separate_items_with_commas'] = "Separate {$name_lower} with commas";
		}
		if ( ! isset( $labels['add_or_remove_items'] ) ) {
			$labels['add_or_remove_items'] = "Add or remove {$name_lower}";
		}
		if ( ! isset( $labels['choose_from_most_used'] ) ) {
			$labels['choose_from_most_used'] = "Choose from most used {$name_lower}";
		}
	}

	if ( isset( $labels['singular_name'] ) ) {
		$singular_name = $labels['singular_name'];
		$singular_name_lower = strtolower( $singular_name );

		if ( ! isset( $labels['add_new'] ) ) {
			$labels['add_new'] = 'Add New';
		}
		if ( ! isset( $labels['add_new_item'] ) ) {
			$labels['add_new_item'] = "Add New {$singular_name}";
		}
		if ( ! isset( $labels['edit_item'] ) ) {
			$labels['edit_item'] = "Edit {$singular_name}";
		}
		if ( ! isset( $labels['update_item'] ) ) {
			$labels['update_item'] = "Update {$singular_name}";
		}
		if ( ! isset( $labels['new_item'] ) ) {
			$labels['new_item'] = "New {$singular_name}";
		}
		if ( ! isset( $labels['new_item_name'] ) ) {
			$labels['new_item_name'] = $labels['new_item'];
		}
		if ( ! isset( $labels['view_item'] ) ) {
			$labels['view_item'] = "View {$singular_name}";
		}
		if ( ! isset( $labels['parent_item'] ) ) {
			$labels['parent_item'] = "Parent {$singular_name}";
		}
		if ( ! isset( $labels['parent_item_colon'] ) ) {
			$labels['parent_item_colon'] = $labels['parent_item'] . ':';
		}
	}

	return $labels;
}

/**
 * Returns the current URL
 *
 * @return	string
 */
function get_current_url() {
	return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Echos the current URL
 *
 * @return	void
 */
function current_url() {
	echo get_current_url();
}

/**
 * Returns the current request URI without the query string
 *
 * @return	string
 */
function get_current_url_slug() {
	return strtok( $_SERVER['REQUEST_URI'], '?' );
}

/**
 * Echos the current request URI without the query string
 *
 * @return	void
 */
function current_url_slug() {
	echo get_current_url_slug();
}
