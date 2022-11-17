### TP_Core/Traits/Compat

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _cache_compat.php: 	
	* _tp_cache_compat_add_multiple( array $data, $group = '', $expire = 0 ) -> mixed 
	* _tp_cache_compat_set_multiple( array $data, $group = '', $expire = 0 ):array 
	* _cache_compat_get_multiple( $keys, $group = '', $force = false ):array 
	* _tp_cache_compat_delete_multiple( array $keys, $group = '' ):array 
	* _tp_cache_compat_flush_runtime():bool 
	*  

- _compat_01.php: 	
	* _tp_can_use_pcre_u( $set = null ) 
	* _mb_substr( $str, $start, $length = null, $encoding = null ):string 
	* _get_mb_substr( $str, $start, $length = null, $encoding = null ):string 
	* _mb_str_len( $str, $encoding = null ):int 
	* _get_mb_str_len( $str, $encoding = null ):int 
	* _hash_hmac( $al_go, $data, $key, $raw_output = false ) bool|string 
	* _get_hash_hmac( $al_go, $data, $key, $raw_output = false ) bool|string 
	* _hash_equals( $a, $b ):bool 
	* _is_countable( $var ):bool 
	* _is_iterable( $var ):bool
	* _array_key_first( array $arr ) -> int|string
