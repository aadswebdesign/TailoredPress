<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-9-2022
 * Time: 09:46
 */
namespace TP_Admin\Traits;
if(ABSPATH){
    trait _adm_privacy_tools{
        //@description Resend an existing request and return the result.
        protected function _tp_privacy_resend_request( $request_id ):bool{return '';}//18
        //@description Marks a request as completed by the admin and logs the current timestamp.
        protected function _tp_privacy_completed_request( $request_id ):string{return '';}//46
        //@description Handle list table actions.
        protected function _tp_personal_data_handle_actions():void{}//73
        //@description Cleans up failed and expired requests before displaying the list table.
        protected function _tp_personal_data_cleanup_requests():void{}//195
        //@description Generate a single group for the personal data export report.
        protected function _tp_privacy_generate_personal_data_export_group_html( $group_data, $group_id = '', $groups_count = 1 ):string{return '';}//252
        //@description Generate the personal data export file.
        protected function _tp_privacy_generate_personal_data_export_file( $request_id ):void{}//310
        //@description Send an email to the user with a link to the personal data export file
        protected function _tp_privacy_send_personal_data_export_email( $request_id ):string{return '';}//588
        //@description Intercept personal data exporter page Ajax responses in order to assemble the personal data export file.
        protected function _tp_privacy_process_personal_data_export_page( $response, $exporter_index, $email_address, $page, $request_id, $send_as_email, $exporter_key ):void{}//770
        //@description Mark erasure requests as completed after processing is finished.
        protected function _tp_privacy_process_personal_data_erasure_page( $response, $eraser_index, $email_address, $page, $request_id ):string{return '';}//916
    }
}else{die;}