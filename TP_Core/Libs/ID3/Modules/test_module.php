<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-10-2022
 * Time: 22:51
 */

namespace TP_Core\Libs\ID3\Modules;


class test_module
{
    protected $_args;
    protected $_html;
    public function __construct(...$args){
        $this->_args = $args;
    }
    private function __to_string():string{
        $this->_html = "test_module";
        $this->_html .= "";
        return (string) $this->_html;
    }
    public function __toString(){
        return $this->__to_string();
    }

}