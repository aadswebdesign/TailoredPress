<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-2-2022
 * Time: 10:38
 */
declare(strict_types=1);
namespace TP_Core\Traits\Filters;
use TP_Core\Libs\ TP_Hook;
if(ABSPATH){
    trait _all_hooks_helper {
        protected $_tp_filter;
        protected function _tp_call_all_hook( $args ): void{
            global $tp_filter;
            if($tp_filter instanceof TP_Hook){
                $tp_filter['all']->do_all_hook( $args );
            }
        }//906
    }
}else die;