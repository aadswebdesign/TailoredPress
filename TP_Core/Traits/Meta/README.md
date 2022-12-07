### TP_Core/Traits/Meta

**Note:** For what it is now and subject to change. 

**Files/Methods:** 
- _meta_01.php: 	
	- _add_metadata( $meta_type, $object_id, $meta_key, $meta_value, $unique = false ) -> bool|int
	- _update_metadata( $meta_type, $object_id, $meta_key, $meta_value, $prev_value = '' ):bool  
	- _delete_metadata( $meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false ):bool  
	- _get_metadata( $meta_type, $object_id, $meta_key = '', $single = false ):string  
	- _get_metadata_raw( $meta_type, $object_id, $meta_key = '', $single = false ) -> mixed  
	- _get_metadata_default( $meta_type, $object_id, $meta_key, $single = false ) -> array|string  
	- _metadata_exists( $meta_type, $object_id, $meta_key ):bool 
	- _get_metadata_by_mid( $meta_type, $meta_id ) -> mixed   
	- _update_metadata_by_mid( $meta_type, $meta_id, $meta_value, $meta_key = false ):bool 
	- _delete_metadata_by_mid( $meta_type, $meta_id ):bool 
- _meta_02.php: 	
	- _update_meta_cache( $meta_type, $object_ids ) -> array|bool 
	- _tp_metadata_lazy_loader(): TP_Metadata_Lazyloader 
	- _get_meta_sql( $meta_query, $type, $primary_table, $primary_id_column, $context = null ) -> mixed 
	- _get_meta_table( $type ):bool 
	- _is_protected_meta( $meta_key, $meta_type = '' ) -> mixed 
	- _sanitize_meta( $meta_key, $meta_value, $object_type, $object_subtype = '' ) -> mixed  
	- _register_meta( $object_type, $meta_key, ...$args):bool  
	- _filter_default_metadata( $value, $object_id, $meta_key, $single, $meta_type ):array  
	- _registered_meta_key_exists( $object_type, $meta_key, $object_subtype = '' ):bool  
	- _unregister_meta_key( $object_type, $meta_key, $object_subtype = '' ):bool  
- _meta_03.php: 	
	- _get_registered_meta_keys( $object_type, $object_subtype = '' ):array 
	- _get_registered_metadata( $object_type, $object_id, $meta_key = '' ) -> mixed  
	- _tp_register_meta_args_allowed_list( $default_args, ...$args ):array  
	- _get_object_subtype( $object_type, $object_id ) -> mixed  
