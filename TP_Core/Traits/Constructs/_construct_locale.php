<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-4-2022
 * Time: 14:06
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_locale{
        public $noop_translations;
		public $tp_I10n;
        public $tp_I10n_unloaded;
        public $tp_locale;
        public $tp_local_package;
        public $tp_locale_switcher;
        public $tp_current_day;
        public $tp_previous_day;
        public $tp_previous_weekday;
        public $tp_month;
        public $tp_month_number;
        public $tp_year;
        protected function _construct_locale():void{
            $this->noop_translations;
            $this->tp_I10n;
            $this->tp_I10n_unloaded;
            $this->tp_locale;
            $this->tp_local_package;
            $this->tp_locale_switcher;
            $this->tp_current_day;
            $this->tp_previous_day;
            $this->tp_previous_weekday;
            $this->tp_month;
            $this->tp_month_number;
            $this->tp_year;
        }

    }
}else die;