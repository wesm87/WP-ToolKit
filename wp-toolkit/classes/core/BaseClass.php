<?php
/**
 * Contains the BaseClass class.
 *
 * @package WP-ToolKit
 */

namespace WPTK\Core;
use WPTK\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains a base class for plugin and theme classes to extend from. Also
 * contains a set of useful functions shared across theme and plugin files.
 */
class BaseClass {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_action( 'init', 'on_wp_init' );
		$this->add_action( 'wp_loaded', 'on_wp_loaded' );
		$this->add_action( 'wp_enqueue_scripts', 'add_scripts_and_styles' );
	}

	/**
	 * If an object method is called that doesn't exist, check the current
	 * and global scope and call the first function that is found.
	 * If nothing is found, log the function name and return false.
	 *
	 * @param string     $name The name of the function that was called.
	 * @param array|null $arguments An array containing the arguments that were passed to the function.
	 */
	public function __call( $name, $arguments = null ) {
		$function = $this->get_function( $name );

		if ( is_wp_error( $function ) ) {
			log( "Function {$name} not found inside class or within current or global namespaces." );
			return false;
		}

		call_user_func_array( $function, $arguments );
	}

	/**
	 * Called when `init` action is fired
	 */
	public function on_wp_init() {

	}

	/**
	 * Called once WordPress is fully loaded
	 */
	public function on_wp_loaded() {

	}

	/**
	 * Add your scripts and styles for your plugin or theme here
	 */
	public function add_scripts_and_styles() {

	}

	/**
	 * Enables a custom site / theme / plugin mod.
	 *
	 * @param string $mod The name of the mod you want to enable.
	 * @uses Utils\enable_mod
	 */
	public function enable_mod( $mod ) {
		return Utils\enable_mod( $mod );
	}

	/**
	 * A shortcut function that takes an array of mod names and enables each
	 * one individually.
	 *
	 * @param array $mods An array of mods you want to enable.
	 * @uses enable_mods
	 * @uses Utils\enable_mods
	 */
	public function enable_mods( $mods ) {
		return Utils\enable_mods( $mods );
	}

	/**
	 * Returns true if the specified mod is enabled, or fale if not.
	 *
	 * @param string $mod The name of the mod you want to check.
	 */
	public function is_mod_enabled( $mod ) {
		return Utils\is_mod_enabled( $mod );
	}

	/**
	 * Checks to see if the function object that was passed exists and is
	 * callable, and if not, tries to locate the function in another scope.
	 * Checks the current class first, then the local namespace, then the
	 * global namespace.
	 *
	 * @param string|array|Closure $function_arg The function object to check against.
	 */
	public function get_function( $function_arg ) {
		$is_fn_name	= ( \is_string( $function_arg ) );
		$is_fn_array   = ( \is_array( $function_arg ) );
		$is_closure	= ( $function_arg instanceof \Closure );

		if ( $is_fn_name ) {
			/*
			 * Check for function first inside this class
			 * If not found, check the local namespace
			 * If not found, check the global namespace
			 */
			$function_name = $is_fn_name;
			$function_list = array(
				array( $this, $function_arg ),
				$function_arg,
				"\{$function_arg}",
			);
		} else if ( $is_fn_array || $is_closure ) {
			/*
			 * Check if valid ( Class, method ) or ( $instance, method )
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
		$wp_errors = new WP_Error();
		$wp_errors->add( 'function_not_found', $error_message );
		log( $wp_errors );

		return $wp_errors;
	}

	/**
	 * Helper function to add or remove an action or filter callback.
	 *
	 * @param string $action        Can be "add" or "remove".
	 * @param string $hook_type     Can be "action" or "filter".
	 * @param string $hook_name     The name of the target action or filter.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The priority you want to use for the callback function.
	 * @param int    $accepted_args The number of arguments that your callback function accepts.
	 */
	public function add_remove_hook( $action, $hook_type, $hook_name, $callback, $priority, $accepted_args ) {
		$callback = $this->get_function( $callback );

		if ( is_wp_error( $callback ) ) {
			return $callback;
		}

		$hook_fn = '';
		$errors = array();
		if ( 'add' !== $action && 'remove' !== $action ) {
			$errors[] = [
				'invalid_action',
				"add_remove_hook :: {$action} is not a valid action, should be either 'add' or 'remove'",
			];
		}
		if ( 'action' !== $hook_type && 'filter' !== $hook_type ) {
			$errors[] = [
				'invalid_hook_type',
				"add_remove_hook :: {$hook_type} is not a valid hook type, should be either 'action' or 'filter'",
			];
		}
		if ( ! empty( $errors ) ) {
			$wp_errors = new WP_Error();
			foreach ( $errors as $error_data ) {
				list( $code, $message ) = $error_data;
				$wp_errors->add( $code, $message );
				log( "{$code} :: {$message}" );
			}
			return $wp_errors;
		}

		$hook_fn = "\\{$action}_{$hook_type}";
		if ( is_wp_error( $hook_fn ) ) {
			return $hook_fn;
		}

		$args = [
			$hook_name,
			$callback,
			$priority,
			$accepted_args,
		];

		call_user_func_array( $hook_fn, $args );
	}

	/**
	 * Helper function to add or remove multiple actions or filter callbacks.
	 *
	 * @param string $action        Can be "add" or "remove".
	 * @param string $hook_type     Can be "action" or "filter".
	 * @param string $hook_names    An array containing names of the target actions or filters.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_remove_hooks( $action, $hook_type, $hook_names, $callback, $priority, $accepted_args ) {
		foreach ( (array) $hook_names as $hook_name ) {
			$this->add_remove_hook( $action, $hook_type, $hook_name, $callback, $priority, $accepted_args );
		}
	}

	/**
	 * Helper function to add an action or filter callback.
	 *
	 * @param string $hook_type     Can be "action" or "filter".
	 * @param string $hook_name     The name of the target action or filter.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_hook( $hook_type, $hook_name, $callback, $priority, $accepted_args ) {
		$this->add_remove_hook( 'add', $hook_type, $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to add multiple action or filter callbacks.
	 *
	 * @param string $hook_type     Can be "action" or "filter".
	 * @param string $hook_names    An array containing the names of the target actions or filters.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_hooks( $hook_type, $hook_names, $callback, $priority, $accepted_args ) {
		foreach ( (array) $hook_names as $hook_name ) {
			$this->add_remove_hook( 'add', $hook_type, $hook_name, $callback, $priority, $accepted_args );
		}
	}

	/**
	 * Helper function to remove an action or filter callback.
	 *
	 * @param string $hook_type     Can be "action" or "filter".
	 * @param string $hook_name     The name of the target action or filter.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function remove_hook( $hook_type, $hook_name, $callback, $priority, $accepted_args ) {
		$this->add_remove_hook( 'remove', $hook_type, $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to remove multiple action or filter callbacks.
	 *
	 * @param string $hook_type     Can be "action" or "filter".
	 * @param string $hook_names    An array containing the names of the target actions or filters.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function remove_hooks( $hook_type, $hook_names, $callback, $priority, $accepted_args ) {
		foreach ( (array) $hook_names as $hook_name ) {
			$this->add_remove_hook( 'remove', $hook_type, $hook_name, $callback, $priority, $accepted_args );
		}
	}

	/**
	 * Helper function to add an action callback.
	 *
	 * @param string $action        The name of the target action.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_action( $action, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add_hook( 'action', $action, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to add multiple action callbacks.
	 *
	 * @param string $actions       An array containing the names of the target actions.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_actions( $actions, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add_hooks( 'action', $actions, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to remove an action callback.
	 *
	 * @param string $action_name   The name of the target action.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function remove_action( $action_name, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->remove_hook( 'action', $action_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to remove multiple action callbacks.
	 *
	 * @param string $action_names  An array containing the names of the target actions.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function remove_actions( $action_names, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->remove_hooks( 'action', $action_names, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to add a filter callback.
	 *
	 * @param string $filter        The name of the target filter.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_filter( $filter, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add_hook( 'filter', $filter, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to add multiple filter callbacks.
	 *
	 * @param string $filters       An array containing the names of the target actions.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function add_filters( $filters, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add_hooks( 'filter', $filters, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to remove a filter callback.
	 *
	 * @param string $filter        The name of the target filter.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function remove_filter( $filter, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->remove_hook( 'filter', $filter, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper function to remove multiple filter callbacks.
	 *
	 * @param string $filters       An array containing the names of the target actions.
	 * @param mixed  $callback      A valid callback function.
	 * @param int    $priority      The callback priority.
	 * @param int    $accepted_args The number of arguments your callback accepts.
	 */
	public function remove_filters( $filters, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->remove_hook( 'filter', $filters, $callback, $priority, $accepted_args );
	}
}
