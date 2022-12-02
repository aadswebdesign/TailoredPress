<?php 
/**
 * @file: wpc_autoloader.php
 * @author: Aad Pouw 
 * @description: enabling namespacing site wide
 * @note: 
 */

final class tp_autoload{
	public function __construct(){
		$this->__autoload_class();
	}
	private function __autoload_class():void{
		spl_autoload_register(static function($_class){
			$_file = str_replace('\\','/',$_class).'.php';
			$_resolvedFile = stream_resolve_include_path($_file);
			if(file_exists($_resolvedFile)){
                /** @noinspection PhpIncludeInspection */
                require_once $_resolvedFile;
				return $_class;
			}
		});
	}
}
