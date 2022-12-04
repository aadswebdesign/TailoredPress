<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-7-2022
 * Time: 10:01
 */
namespace TP_Admin\Traits\AdminInits;
use TP_Admin\Libs\Adm_Screen;
use TP_Admin\Traits\AdminConstructs\_adm_construct_screen;

if(ABSPATH){
    trait _adm_init_screen{
        use _adm_construct_screen;
        /**
         * @param string $hook_name
         * @return Adm_Screen
         */
        protected function _init_get_screen($hook_name = ''):Adm_Screen{
            if(!$this->tp_screen instanceof Adm_Screen){
                $this->tp_screen = Adm_Screen::get_screen($hook_name);
            }
            return  $this->tp_screen;
        }
        protected function _init_set_screen($hook_name = ''):Adm_Screen{
            if(!$this->tp_screen instanceof Adm_Screen){
                $this->tp_screen = Adm_Screen::get_screen($hook_name);
            }
            return $this->tp_screen->set_screen();
        }

    }
}else die;