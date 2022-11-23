<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-8-2022
 * Time: 08:42
 */
namespace TP_Core\Libs\Recovery;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Misc\_error_protection;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Options\_option_03;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Cron\_cron_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Pluggables\_pluggable_03;
use TP_Core\Traits\Pluggables\_pluggable_04;
use TP_Core\Traits\Pluggables\_pluggable_05;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_general_template_01;
use TP_Core\Traits\Templates\_general_template_02;
if(ABSPATH){
    class Recovery_Base {
        use _init_error, _error_protection;
        use _filter_01, _action_01, _methods_03, _methods_05,_methods_06, _methods_08;
        use _I10n_01,_I10n_02,_I10n_04, _load_03, _load_04, _link_template_09, _cron_01;
        use _option_01,_option_02,_option_03,_theme_01;
        use _formats_02, _formats_06, _formats_07, _formats_10;
        use _pluggable_01,_pluggable_02, _pluggable_03, _pluggable_04,_pluggable_05;
        use _general_template_01, _general_template_02;
        public const EXIT_ACTION = 'exit_recovery_mode';
        protected $_cookie_service;
        protected $_key_service;
        protected $_link_service;
        protected $_email_service;
        protected $_is_initialized = false;
        protected $_is_active = false;
        protected $_session_id = '';
        protected $_tp_version;
    }
}else die;