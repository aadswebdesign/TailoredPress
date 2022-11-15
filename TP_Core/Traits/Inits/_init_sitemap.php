<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 22:18
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\SiteMaps\TP_Sitemaps;
use TP_Core\Traits\Actions\_action_01;
if(ABSPATH){
    trait _init_sitemap{
        use _action_01;
        protected $_tp_sitemaps;
        protected function _init_sitemap():TP_Sitemaps{
            if(!($this->_tp_sitemaps instanceof TP_Sitemaps)){
                $sitemap = new TP_Sitemaps();
                $this->_tp_sitemaps = $sitemap->init();
                $this->_do_action( 'tp_sitemaps_init', $this->_tp_sitemaps );
            }
            return $this->_tp_sitemaps;
        }
    }
}else die;