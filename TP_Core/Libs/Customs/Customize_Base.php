<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-7-2022
 * Time: 04:51
 */
namespace TP_Core\Libs\Customs;
use TP_Core\Traits\Inits\_init_custom;
use TP_Core\Traits\Inits\_init_pages;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\AssetsLoaders\_assets_loader_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_04;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\TemplateClasses\_nav_menu_template;
use TP_Core\Traits\Theme\_theme_03;
use TP_Core\Traits\Theme\_theme_07;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Theme\_theme_08;

if(ABSPATH){
    class Customize_Base{
        use _action_01, _action_01, _capability_01, _filter_01;
        use _formats_08, _formats_11,_methods_09, _methods_11, _general_template_02;
        use _nav_menu_template, _option_01, _pluggable_04, _theme_03,_theme_08;
        use _theme_07, _load_04, _init_error, tp_script,_assets_loader_01;
        use _init_pages,_init_custom;
        protected static $_instance_count = 0;
        public $instance_number;
        public $manager;
        public $id;


    }
}else die;