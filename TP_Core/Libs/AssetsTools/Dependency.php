<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-5-2022
 * Time: 08:18
 */
declare(strict_types=1);
namespace TP_Core\Libs\AssetsTools;
if(ABSPATH){
    class Dependency{
        public $data = [];
        public $extra = [];
        public $handle;
        public function __construct( ...$args ) {
            @list($this->handle,$this->data) = $args;
            if(! is_array($this->data['deps'])) $this->data['deps'] = [];
        }
        public function add_data( $name, $data ): bool{
            if (!is_scalar($name)) return false;
            $this->extra[$name] = $data;
            return true;
        }
        public function set_translations( $domain, $path = null ): bool{
            if (!is_string($domain)) return false;
            $this->data['textdomain'] = $domain;
            $this->data['translations_path'] = $path;
            return true;
        }
    }
}else die;