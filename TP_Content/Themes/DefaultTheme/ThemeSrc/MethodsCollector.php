<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-9-2022
 * Time: 15:41
 */
namespace TP_Content\Themes\DefaultTheme\ThemeSrc;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\K_Ses\_k_ses_03;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Query\_query_01;
use TP_Core\Traits\Templates\_general_template_01;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;
if(ABSPATH){
    class MethodsCollector{
        use _formats_02,_formats_07,_formats_08,_formats_06,_link_template_09,_link_template_10,_filter_01,_option_01,_load_03,_load_04;
        use _k_ses_03,_I10n_01,_I10n_03,_I10n_04,_I10n_05,_general_template_01,_general_template_08,_query_01;
        use _methods_21;

    }
}else{die;}