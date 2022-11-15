### TP_Core/Traits/Cron

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _cron_01.php: 	
	* _tp_schedule_single_event( $timestamp, $hook, $args = [], $tp_error = false ) 
	* _tp_schedule_event( $timestamp, $recurrence, $hook, $args = [], $tp_error = false ) 
	* _tp_reschedule_event( $timestamp, $recurrence, $hook, $args = [], $tp_error = false ) 
	* _tp_un_schedule_event( $timestamp, $hook, $args = [], $tp_error = false ) 
	* _tp_clear_scheduled_hook( $hook, $args = [], $tp_error = false ) 
	* _tp_un_schedule_hook( $hook, $tp_error = false ) 
	* _tp_get_scheduled_event( $hook, $args = array(), $timestamp = null ) 
	* _tp_next_scheduled( $hook, $args = [] ): bool 
	* _spawn_cron( $gmt_time = 0 ): bool 

- _cron_02.php: 	
	* _tp_cron() 
	* _construct_cron() 
	* _tp_get_schedules() 
	* _tp_get_schedule( $hook, $args = [] ):array 
	* _tp_get_ready_cron_jobs():array 
	* _get_cron_array() 
	* _set_cron_array( $cron, $tp_error = false ):TP_Error 
	* _upgrade_cron_array( $cron ):array 
