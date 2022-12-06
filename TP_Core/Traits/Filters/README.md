### TP_Core/Traits/Filters

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _all_hooks_helper.php: 	
	* _tp_call_all_hook( $args ): void 

- _filter_01.php: 	
	* _add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1  ):bool
	* _apply_filters( $hook_name, $value, ...$args ) => mixed
	* _apply_filters_ref_array( $hook_name, $args ) => mixed
	* _has_filter( $hook_name, $callback = false) => mixed
	* _remove_filter( $hook_name, $callback, $priority = 10 ): bool 
	* _remove_all_filters( $hook_name, $priority = false): bool 
	* _current_filter() 
	* _doing_filter( $hook_name = null ): bool 
	* _tp_filter_build_unique_id( $hook_name, $callback, $priority): ?string 
	* _apply_filters_deprecated( $hook_name, $args, $version, $replacement = '', $message = '' ) => mixed 
