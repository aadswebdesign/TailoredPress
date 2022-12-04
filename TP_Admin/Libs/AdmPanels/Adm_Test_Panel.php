<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-10-2022
 * Time: 22:25
 */
namespace TP_Admin\Libs\AdmPanels;
//use TP_Admin\Admins;
use TP_Admin\AdminSettings;

if(ABSPATH){
    class Adm_Test_Panel extends AdminSettings  {
        protected $_args;
        protected $_tests;
        public function __construct($args = null){
            parent::__construct();
            $this->_args = $args;
            $this->adm_header_args = [
                'parent_file' => 'testpanel.php',
                'get_admin_index_head' => [$this,'get_options_index_stuff'],
                'panel_title' => '',
                'index_title' => 'TailoredPress',
            ];
            $this->adm_footer_args = [
                'parent_file' => 'testpanel.php'
            ];
            $this->adm_header = $this->get_adm_component_class('Adm_Header',$this->adm_header_args);
            $this->adm_footer = $this->get_adm_component_class('Adm_Footer',$this->adm_footer_args);
        }

        /**
         * @return mixed
         */
        public function test_case(){
            return 'test_case';
        }
        private function __to_string():string{
            $output  = "";
            $output .= $this->adm_header;
            $output .= "<p>Adm_Test_Panel, temporary file to be used for testing!</p>";
            $output .= "</br>";
            ob_start();
            //phpinfo();
            //var_dump('<br>TP_NS_CONTENT: ',TP_NS_CONTENT);
            //var_dump('<br><br>TP_CONTENT_ASSETS: ',TP_CONTENT_ASSETS);
            //var_dump('<br><br>TP_CONTENT_LANG: ',TP_CONTENT_LANG);

            $output .= ob_get_clean();
            $output .= "</br></br>";
            $output .= "";
            $output .= "</br>";
            $output .= "</br>";
            $output .= $this->adm_footer;
            //$output .= $this->_get_delete_theme('');
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

