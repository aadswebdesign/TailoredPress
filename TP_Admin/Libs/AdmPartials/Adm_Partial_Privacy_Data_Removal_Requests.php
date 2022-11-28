<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Privacy_Data_Removal_Requests extends Adm_Partial_Privacy_Matters  {
        protected $_request_type = 'remove_personal_data';
        protected $_post_type = 'user_request';
        public function get_block_email( $item ):string{
            $row_actions = [];
            $status      = $item->status;
            $request_id  = $item->ID;
            if ( 'request-confirmed' !== $status ){
                $erasers       = $this->_apply_filters( 'tp_privacy_personal_data_erasers',[]);
                $erasers_count = count( $erasers );
                $nonce         = $this->_tp_create_nonce( 'tp-privacy-erase-personal-data-' . $request_id );
                $remove_data_markup  = "<span class='remove-personal-data force-remove-data' data-erasers_count='{$this->_esc_attr($erasers_count)}' data-erasers_id='{$this->_esc_attr($request_id)}' data-nonce='{$this->_esc_attr($nonce)}'>";
                $remove_data_markup .= "<span class='data-idle'><button class='button-link remove-personal-data-handle' type='button'>{$this->__('Force erase personal data.')}</button></span>";
                $remove_data_markup .= "<span class='data-processing hidden'>{$this->__('Erasing data...')}<span class='erasure-progress'></span></span>";
                $remove_data_markup .= "<span class='data-success hidden'>{$this->__('Erasure completed.')}</span>";
                $remove_data_markup .= "<span class='data-failed hidden'>{$this->__('Force erase sure has failed.')}<button class='button-link remove-personal-data-handle' type='button'>{$this->__('Retry.')}</button></span>";
                $remove_data_markup .= "</span>";
                $row_actions['remove-data'] = $remove_data_markup;
            }
            $complete_request_markup = null;
            if ( 'request-completed' !== $status ) {
                $completed_request_url = $this->_esc_url($this->_tp_nonce_url($this->_add_query_arg(['action' => 'complete','request_id' => [$request_id],],$this->_admin_url( 'erase-personal-data.php' )),'bulk-privacy_requests'));
                $completed_request_attr = $this->_esc_attr(sprintf($this->__('Mark export request for &#8220;%s&#8221; as completed.'),$item->email));
                $complete_request_markup  = "<span>";
                $complete_request_markup .= sprintf("<a href='%s' class='complete-request' aria-label='%s'>%s</a>",$completed_request_url,$completed_request_attr,$this->__('Complete request'));
                $complete_request_markup .= "</span>";
            }
            if($complete_request_markup !== null){
                $row_actions['complete-request'] = $complete_request_markup;
            }
            $mailto_url = $this->_esc_url('mailto:'. $item->email);
            return sprintf("<a href='%1\$s'>%2\$s</a> %3\$s",$mailto_url, $item->email,$this->_get_actions( $row_actions ));
        }//46
        public function get_column_next_steps( $item ):string{
            $status = $item->status;
            $output  = "";
            switch ( $status ) {
                case 'request-pending':
                    $output .= "<small>{$this->__('Waiting for confirmation.')}</small>";
                    break;
                case 'request-confirmed':
                    $erasers       = $this->_apply_filters( 'tp_privacy_personal_data_erasers', array() );
                    $erasers_count = count( $erasers );
                    $request_id    = $item->ID;
                    $nonce         = $this->_tp_create_nonce( 'tp-privacy-erase-personal-data-' . $request_id );
                    $output .= "<div class='remove-personal-data' data-force_erase ='1' data-erase_count='{$this->_esc_attr($erasers_count)}' data-request_id='{$this->_esc_attr($request_id)}' data-nonce='{$this->_esc_attr($nonce)}' >";
                    $output .= "<span class='data-idle'><button class='button-link remove-personal-data-handle' type='button'>{$this->__('Erase personal data.')}</button></span>";
                    $output .= "<span class='data-processing hidden'>{$this->__('Erasing data...')}<span class='erasure-progress'></span></span>";
                    $output .= "<span class='data-success message hidden'>{$this->__('Erasure completed.')}</span>";
                    $output .= "<span class='data-failed hidden'>{$this->__('Data erasure has failed.')}<button class='button-link remove-personal-data-handle' type='button'>{$this->__('Retry.')}</button></span>";
                    $output .= "</div>";
                    break;
                case 'request-failed':
                    $output .= "<button id='privacy_action_email_retry[{$item->ID}]' name='privacy_action_email_retry[{$item->ID}]' class='button-link' type='submit'>{$this->__('Retry')}</button>";
                    break;
                case 'request-completed':
                    $remove_url = $this->_esc_url($this->_tp_nonce_url($this->_add_query_arg(['action' => 'delete','request_id' => [$item->ID]],$this->_admin_url( 'erase-personal-data.php')),'bulk-privacy_requests'));
                    $output .= "<a href='$remove_url'>{$this->__('Remove request')}</a>";
                    break;
            }
            return $output;
        }//117
    }
}else{die;}