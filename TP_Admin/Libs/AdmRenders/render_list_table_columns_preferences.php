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
    class render_list_table_columns_preferences{
        use _formats_10;
        use _adm_screen;
        protected $_html;
        protected $_args;
        public function __construct($args){
            $this->_args['columns'] = $args['columns'];
            $this->_args['legend'] = $args['legend'];
            $this->_args['hidden'] = $args['hidden'];
        }
        private function __to_string(): string
        {
            $this->_html = "<fieldset class='metabox-prefers'><legend>{$this->_args['legend']}</legend>";
            $special = ['_title', 'cb', 'comment', 'media', 'name', 'title', 'username', 'blogname'];
            foreach ( $this->_args['columns'] as $column => $title ) {
                if ( in_array( $column, $special, true ) ) continue;
                if ( empty( $title ) ) continue;
                $title = $this->_tp_strip_all_tags( $title );
                $id = "{$column}_hide";
                $this->_html .= "<label>";
                $this->_html .= "<input class='hide-column-tog' name='$id' value='$column' {$this->_get_checked( ! in_array( $column, $this->_args['hidden'], true ), true) }/>";
                $this->_html .= "$title</label>\n";
            }
            $this->_html .= "</fieldset>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;