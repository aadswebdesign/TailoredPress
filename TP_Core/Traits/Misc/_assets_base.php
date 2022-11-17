<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-5-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    trait _assets_base{
        use _action_01;
        use _I10n_01;
        use _methods_12;
        protected function _tp_scripts_maybe_doing_it_wrong( $function, $handle = '' ):void{
            //todo more to come
            if($this->_did_action('init') || $this->_did_action('tp_enqueue_scripts'))
                return;
            $message = sprintf(
            /* translators: 1: tp_enqueue_scripts, 2: admin_enqueue_scripts, 3: login_enqueue_scripts */
                $this->__( 'Scripts and styles should not be registered or enqueued until the %1$s, %2$s.' ),//, or %3$s hooks
                "<code>tp_enqueue_scripts</code>todo admin_enqueue_scripts</code>","<code>todo login_enqueue_scripts</code>");
            if($handle) /* translators: %s: Name of the script or stylesheet. */
                $message .= ' ' . sprintf($this->__( 'This notice was triggered by the %s handle.' ),"<code>{$handle}</code>");
            $this->_doing_it_wrong($function,$message,'0.0.1');
        }
    }
}else die;