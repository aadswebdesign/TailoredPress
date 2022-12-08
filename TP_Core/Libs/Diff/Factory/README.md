### TP_Core/Libs/Diff/Factory

**Note:** For what it is now and subject to change. 

**Files/ClassMethods and Vars:**  
- _text_diff_op.php: 	
	- protected $_original, $_final 
	- &reverse():bool 
	- _new_original():int 
	- _new_final():int 
- _text_diff_op_add.php: 	
	- __construct( $lines )
	- &reverse():bool 
- _text_diff_op_change.php: 	
	- __construct( $orig, $final ) 
	- &reverse():bool 
- _text_diff_op_copy.php: 	
	- __construct($original, $final = false) 
	- &reverse():bool  
- _text_diff_op_delete.php: 	
	- __construct( $lines ) 
	- &reverse():bool 
