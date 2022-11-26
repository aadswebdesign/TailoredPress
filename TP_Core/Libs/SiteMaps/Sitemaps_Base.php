<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-8-2022
 * Time: 10:39
 */
namespace TP_Core\Libs\SiteMaps;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
if(ABSPATH){
    class Sitemaps_Base{
        use _init_rewrite, _link_template_09,_I10n_01;
        use _filter_01,_formats_07,_formats_08;
    }
}else{die;}