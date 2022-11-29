<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 10:05
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmRenders;
use TP_Admin\Traits\_adm_screen;
use TP_Core\Traits\Formats\_formats_10;
if(ABSPATH){
    class render_list_preferences{
        use _formats_10;
        use _adm_screen;
        protected $_args;
        public function __construct($args){
            $this->_args['columns'] = $args['columns'];
            $this->_args['legend'] = $args['legend'];
            $this->_args['hidden'] = $args['hidden'];
        }
        private function __to_string(): string
        {
            $output = "<fieldset class='metabox-prefers'><legend>{$this->_args['legend']}</legend><ul>";
            $special = ['_title', 'cb', 'comment', 'media', 'name', 'title', 'username', 'blogname'];
            foreach ( $this->_args['columns'] as $column => $title ) {
                if ( in_array( $column, $special, true ) ) continue;
                if ( empty( $title ) ) continue;
                $title = $this->_tp_strip_all_tags( $title );
                $id = "{$column}_hide";
                $output .= "<li>";
                $output .= <dt><label for='$id'>$title</label></dt>
                $output .= <dd><input class='hide-column-tog' name='$id' value='$column' type='checkbox' {$this->_get_checked( ! in_array( $column, $this->_args['hidden'], true ), true) }/></dd>
                $output .= "</li>\n";
            }
            $output .= "</ul></fieldset>";
            return (string) $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;