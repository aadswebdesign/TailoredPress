<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-8-2022
 * Time: 13:25
 */
namespace TP_Core\BaseLibs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Multisite\Blog\_ms_blog_01;
use TP_Core\Traits\Multisite\Blog\_ms_blog_02;
use TP_Core\Traits\Multisite\Methods\_ms_methods_02;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_03;
use TP_Core\Traits\Templates\_general_template_01;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;

if(ABSPATH){
    class _base_libs{
        use _init_error,_init_cache,_init_queries,_init_user;
        use _action_01;
        use _methods_03,_methods_04,_methods_08;
        use _ms_methods_02,_ms_blog_01,_ms_blog_02;
        use _I10n_01;
        use _load_04;
        use _pluggable_01,_pluggable_03;
        use _general_template_01,_general_template_02,_link_template_09,_link_template_10;
        use _formats_07,_formats_11;
        public function __construct(){
        }
    }
}else{die;}