<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-9-2022
 * Time: 22:44
 */
namespace TP_Admin\Traits;
use TP_Admin\Libs\Adm_Screen;
use TP_Admin\Libs\AdmLists\_TP_List_Block_Compat;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Admin\Traits\AdminInits\_init_list_blocks;
use TP_Core\Traits\Methods\_methods_21;
if(ABSPATH){
    trait _adm_list_block{
        use _init_list_blocks;
        use _adm_template_04, _methods_21;
        /**
         * @param null $class
         * @param array ...$args
         * @return mixed
         */
        protected function _get_list_block($class = null,...$args){
            if ($class !== null){
                if ( isset( $args['screen'] ) ) {
                    $args['screen'] = $this->_convert_to_screen( $args['screen'] );
                } elseif ( isset( $GLOBALS['hook_suffix'] ) ) {
                    $args['screen'] = $this->_get_current_screen();
                } else {
                    $args['screen'] = null;
                }
                return $this->_tp_load_class('list_classes',TP_NS_ADMIN_TB_LIST,$class,$args );
            }
            return false;
        }//22 heavily customized!
        protected function _get_register_column_headers( $screen, $columns ):string{
            return new _TP_List_Block_Compat($screen, $columns);
        }//79
        protected function _get_the_column_headers( $screen, $with_id = true ):string{
            return $this->_init_block_compat($screen)->get_column_headers($with_id);
        }//91
    }
}else{die;}