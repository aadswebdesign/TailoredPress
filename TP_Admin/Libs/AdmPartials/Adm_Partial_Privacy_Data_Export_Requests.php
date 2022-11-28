<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Privacy_Data_Export_Requests extends Adm_Partial_Privacy_Matters  {
        protected $_request_type = 'export_personal_data';
        protected $_post_type = 'user_request';
        public function get_column_email( $item ):string{
            $exporters       = $this->_apply_filters( 'tp_privacy_personal_data_exporters', []);
            $exporters_count = count( $exporters );
            $status          = $item->status;
            $request_id      = $item->ID;
            $nonce           = $this->_tp_create_nonce( 'tp-privacy-export-personal-data-' . $request_id );
            $download_data_markup  = "<dt><p class='export-personal-data' data-exporters_count='{$this->_esc_attr($exporters_count)}' data-request_id='{$this->_esc_attr($request_id)}' data-nonce='{$this->_esc_attr($nonce)}'>";
            $download_data_markup .= "<span class='data-idle'><button class='button-link export-personal-data-handle' type='button'>{$this->__('Download personal data.')}</button></span>";
            $download_data_markup .= "<span class='data-processing hidden'>{$this->__('Downloading data...')}<span class='export-progress'></span></span>";
            $download_data_markup .= "<span class='data-success hidden'><button class='button-link export-personal-data-handle' type='button'>{$this->__('Download personal data again.')}</button></span>";
            $download_data_markup .= "<span class='data-failed hidden'>{$this->__('Download failed.')}<button class='button-link' type='button'>{$this->__('Retry.')}</button></span>";
            $download_data_markup .= "</p></dt>";
            $row_actions['download-data'] = $download_data_markup;
            $complete_request_markup = null;
            if ( 'request-completed' !== $status ) {
                $completed_request_url = $this->_esc_url($this->_tp_nonce_url($this->_add_query_arg(['action' => 'complete','request_id' => [$request_id],],$this->_admin_url( 'export_personal_data.php' )),'bulk-privacy_requests'));
                $completed_request_attr = $this->_esc_attr(sprintf($this->__('Mark export request for &#8220;%s&#8221; as completed.'),$item->email));
                $complete_request_markup = sprintf("<dd><a href='%s' class='complete-request' aria-label='%s'>%s</a></dd>",$completed_request_url,$completed_request_attr,$this->__('Complete request'));
            }
            if( $complete_request_markup !== null){ $row_actions['complete-request'] = $complete_request_markup;}
            $mailto_url = $this->_esc_url('mailto:'. $item->email);
            return sprintf("<dd><a href='%1\$s'>%2\$s</a></dd> %3\$s",$mailto_url, $item->email,$this->_get_actions( $row_actions ));
        }//46
        public function get_block_next_steps( $item ):string{
            $status = $item->status;
            $output  = "";
            switch ( $status ) {
                case 'request-pending':
                    $output .= "<dt><small>{$this->__('Waiting for confirmation.')}</small></dt>";
                    break;
                case 'request-confirmed':
                    $exporters       = $this->_apply_filters( 'tp_privacy_personal_data_exporters', array() );
                    $exporters_count = count( $exporters );
                    $request_id      = $item->ID;
                    $nonce           = $this->_tp_create_nonce( 'tp-privacy-export-personal-data-' . $request_id );
                    $output .= "<dt><p class='export-personal-data' data-send_as_email='1' data-exporters_count='{$this->_esc_attr($exporters_count)}' data-request_id='{$this->_esc_attr($request_id)}' data-nonce='{$this->_esc_attr($nonce)}' >";
                    $output .= "<span class='data-idle'><button class='button-link export-personal-data-handle' type='button'>{$this->__('Send export link.')}</button></span>";
                    $output .= "<span class='data-processing hidden'>{$this->__('Sending email...')}<span class='export-progress'></span></span>";
                    $output .= "<span class='data-success message hidden'>{$this->__('Email sent.')}</span>";
                    $output .= "<span class='data-failed hidden'>{$this->__('Email could not be sent.')}<button class='button-link export-personal-data-handle' type='button'>{$this->__('Retry.')}</button></span>";
                    $output .= "</p></dt>";
                    break;
                case 'request-failed':
                    $output .= "<dd><button id='privacy_action_email_retry[{$item->ID}]' name='privacy_action_email_retry[{$item->ID}]' class='button-link' type='submit'>{$this->__('Retry')}</button><dd>";
                    break;
                case 'request-completed':
                    $remove_url = $this->_esc_url($this->_tp_nonce_url($this->_add_query_arg(['action' => 'delete','request_id' => [$item->ID]],$this->_admin_url( 'export-personal-data.php')),'bulk-privacy_requests'));
                    $output .= "<dd><a href='$remove_url'>{$this->__('Remove request')}</a></dd>";
                    break;
            }
            return "<li class='wrapper next-steps'>$output</li><!-- wrapper next-steps -->";
        }//111
    }
}else{die;}