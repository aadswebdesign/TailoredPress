### TP_Core/Traits

**Note:** For what it is now and subject to change. Also one of my habbits is to give classes/methods a name that speaks for itself.

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods
- _action_01.php: 	
	* _add_action(hook_name, $callback, $priority),ra
	* _has_action(hook_name, $callback),v
	* _do_action($hook_name, $arg),v
	* _get_action($hook_name, $arg),ra
	* _do_action_ref_array($hook_name, $args),v
	* _remove_action($hook_name, $callback, $priority),
	* _remove_all_actions($hook_name, $priority),
	* _current_action(),
	* _doing_action($hook_name),rs
	* _did_action($hook_name), ri




**Abbreviations**
- return array: ra
- return bool: rb
- return int: ri
- return mixed: rm
- return string: rs
- void: v