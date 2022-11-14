<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _comment_08{
        use _init_error;
        use _init_db;
        /**
         * @description Updates the comment type for a batch of comments.
         */
        protected function _tp_batch_update_comment_type():void{
            $tpdb = $this->_init_db();
            $lock_name = 'update_comment_type.lock';
            $lock_result = $tpdb->query( $tpdb->prepare( TP_INSERT ." IGNORE INTO `$tpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */", $lock_name, time() ) );
            if ( ! $lock_result ) {
                $lock_result = $this->_get_option( $lock_name );
                if ( ! $lock_result || ( $lock_result > ( time() - HOUR_IN_SECONDS ) ) ) {
                    $this->_tp_schedule_single_event( time() + ( 5 * MINUTE_IN_SECONDS ), 'tp_update_comment_type_batch' );
                    return;
                }
            }
            $this->_update_option( $lock_name, time() );
            $empty_comment_type = $tpdb->get_var(TP_SELECT . " comment_ID FROM $tpdb->comments WHERE comment_type = '' LIMIT 1");
            if ( ! $empty_comment_type ) {
                $this->_update_option( 'finished_updating_comment_type', true );
                $this->_delete_option( $lock_name );
                return;
            }
            $this->_tp_schedule_single_event( time() + ( 2 * MINUTE_IN_SECONDS ), 'tp_update_comment_type_batch' );
            $comment_batch_size = (int) $this->_apply_filters( 'tp_update_comment_type_batch_size', 100 );
            $comment_ids = $tpdb->get_col(
                $tpdb->prepare(TP_SELECT . " comment_ID FROM {$tpdb->comments} WHERE comment_type = '' ORDER BY comment_ID DESC LIMIT %d", $comment_batch_size ));
            if ( $comment_ids ) {
                $comment_id_list = implode( ',', $comment_ids );
                $tpdb->query(TP_UPDATE . " {$tpdb->comments} SET comment_type = 'comment' WHERE comment_type = '' AND comment_ID IN ({$comment_id_list})");
                $this->_clean_comment_cache( $comment_ids );
            }
            $this->_delete_option( $lock_name );
        }//3824
        /**
         * @description In order to avoid the _tp_batch_update_comment_type() job being accidentally removed,
         * @description . check that it's still scheduled while we haven't finished updating comment types.
         */
        protected function _tp_check_for_scheduled_update_comment_type():void{
            if ( ! $this->_get_option( 'finished_updating_comment_type' ) && ! $this->_tp_next_scheduled( 'tp_update_comment_type_batch' ) )
                $this->_tp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'tp_update_comment_type_batch' );
        }//3908
    }
}else die;