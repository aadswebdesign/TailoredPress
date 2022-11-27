<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 21:15
 */
namespace TP_Admin\Traits;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _adm_bookmark{
        use _init_db;
        /**
         * @description Add a link to using values provided in $_POST.
         * @return string
         */
        protected function _add_link():string{
            return $this->_edit_link();
        }//16
        /**
         * @description Updates or inserts a link using values provided in $_POST.
         * @param int $link_id
         * @return string
         */
        protected function _edit_link( $link_id = 0 ):string{
            if ( ! $this->_current_user_can( 'manage_links' ) ) {
                $die_msg = static function(){
                    $msg = "<h1>{(new self)-> __( 'You need a higher level of permission.' )}</h1>";
                    $msg .= "<p>{(new self)->__( 'Sorry, you are not allowed to edit the links for this site.' )}</p>";
                    echo $msg;
                };
                $this->_tp_die($die_msg,403);
            }
            $_POST['link_url']   = $this->_esc_html( $_POST['link_url'] );
            $_POST['link_url']   = $this->_esc_url( $_POST['link_url'] );
            $_POST['link_name']  = $this->_esc_html( $_POST['link_name'] );
            $_POST['link_image'] = $this->_esc_html( $_POST['link_image'] );
            $_POST['link_rss']   = $this->_esc_url( $_POST['link_rss'] );
            if ( ! isset( $_POST['link_visible'] ) || 'N' !== $_POST['link_visible'] ) {
                $_POST['link_visible'] = 'Y';}
            if ( ! empty( $link_id ) ) {
                $_POST['link_id'] = $link_id;
                return $this->_tp_update_link( $_POST );
            }
            return $this->_tp_insert_link( $_POST );
        }//28
        /**
         * @description Retrieves the default link for editing.
         * @return \stdClass
         */
        protected function _get_default_link_to_edit():\stdClass{
            $link = new \stdClass;
            if ( isset( $_GET['linkurl'] ) ) {
                $link->link_url = $this->_esc_url( $this->_tp_unslash( $_GET['linkurl'] ) );
            } else {$link->link_url = '';}
            if ( isset( $_GET['name'] ) ) {
                $link->link_name = $this->_esc_attr( $this->_tp_unslash( $_GET['name'] ) );
            } else {$link->link_name = '';}
            $link->link_visible = 'Y';
            return $link;
        }//61
        /**
         * @description Deletes a specified link from the database.
         * @param $link_id
         * @return bool
         */
        protected function _tp_delete_link( $link_id ):bool{
            $this->tpdb = $this->_init_db();
            $this->_do_action( 'delete_link', $link_id );
            $this->_tp_delete_object_term_relationships( $link_id, 'link_category' );
            $this->tpdb->delete( $this->tpdb->links, array( 'link_id' => $link_id ) );
            $this->_do_action( 'deleted_link', $link_id );
            $this->_clean_bookmark_cache( $link_id );
            return true;
        }//90
        /**
         * @description Retrieves the link category IDs associated with the link specified.
         * @param int $link_id
         * @return array
         */
        protected function _tp_get_link_cats( $link_id = 0 ):array{
            $cats = $this->_tp_get_object_terms( $link_id, 'link_category',['fields' => 'ids']);
            return array_unique( $cats );
        }//128
        /**
         * @description Retrieves link data based on its ID.
         * @param $link
         * @return mixed
         */
        protected function _get_link_to_edit( $link ){
            return $this->_get_bookmark( $link, OBJECT, 'edit' );
        }//140
        /**
         * @description Inserts a link into the database, or updates an existing link.
         * @param $link_data
         * @param bool $tp_error
         * @return int
         */
        protected function _tp_insert_link( $link_data, $tp_error = false ):int{
            $this->tpdb = $this->_init_db();
            $defaults = ['link_id' => 0,'link_name' => '','link_url' => '','link_rating' => 0,];
            $parsed_args = $this->_tp_parse_args( $link_data, $defaults );
            $parsed_args = $this->_tp_unslash( $this->_sanitize_bookmark( $parsed_args, 'db' ) );
            $link_id   = $parsed_args['link_id'];
            $link_name = $parsed_args['link_name'];
            $link_url  = $parsed_args['link_url'];
            $update = false;
            if ( ! empty( $link_id )){$update = true;}
            if ( '' === trim( $link_name ) ) {
                if ( '' !== trim( $link_url ) ){$link_name = $link_url;}
                else {return 0;}
            }
            if ( '' === trim( $link_url)){return 0;}
            $link_rating      = ( ! empty( $parsed_args['link_rating'] ) ) ? $parsed_args['link_rating'] : 0;
            $link_image       = ( ! empty( $parsed_args['link_image'] ) ) ? $parsed_args['link_image'] : '';
            $link_target      = ( ! empty( $parsed_args['link_target'] ) ) ? $parsed_args['link_target'] : '';
            $link_visible     = ( ! empty( $parsed_args['link_visible'] ) ) ? $parsed_args['link_visible'] : 'Y';
            $link_owner       = ( ! empty( $parsed_args['link_owner'] ) ) ? $parsed_args['link_owner'] : $this->_get_current_user_id();
            $link_notes       = ( ! empty( $parsed_args['link_notes'] ) ) ? $parsed_args['link_notes'] : '';
            $link_description = ( ! empty( $parsed_args['link_description'] ) ) ? $parsed_args['link_description'] : '';
            $link_rss         = ( ! empty( $parsed_args['link_rss'] ) ) ? $parsed_args['link_rss'] : '';
            $link_rel         = ( ! empty( $parsed_args['link_rel'] ) ) ? $parsed_args['link_rel'] : '';
            $link_category    = ( ! empty( $parsed_args['link_category'] ) ) ? $parsed_args['link_category'] : array();
            if ( ! is_array( $link_category ) || 0 === count( $link_category ) ) {
                $link_category = array( $this->_get_option( 'default_link_category' ) );
            }
            if ( $update ) {
                if ( false === $this->tpdb->update( $this->tpdb->links, compact( 'link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_rel', 'link_notes', 'link_rss' ), compact( 'link_id' ) ) ) {
                    if ( $tp_error ) {
                        return (string)new TP_Error( 'db_update_error', $this->__( 'Could not update link in the database.' ), $this->tpdb->last_error );
                    }
                    return 0;
                }
            } else {
                if ( false === $this->tpdb->insert( $this->tpdb->links, compact( 'link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_rel', 'link_notes', 'link_rss' ) ) ) {
                    if ( $tp_error ) {
                        return (string)new TP_Error( 'db_insert_error', $this->__( 'Could not insert link into the database.' ), $this->tpdb->last_error );
                    }
                    return 0;
                }
                $link_id = (int) $this->tpdb->insert_id;
            }
            $this->_tp_set_link_cats( $link_id, $link_category );
            if ( $update ) {$this->_do_action( 'edit_link', $link_id );}
            else {$this->_do_action( 'add_link', $link_id );}
            $this->_clean_bookmark_cache( $link_id );
            return $link_id;
        }//176
        /**
         * @description Update link with the specified link categories.
         * @param int $link_id
         * @param array ...$link_categories
         */
        protected function _tp_set_link_cats( $link_id = 0, ...$link_categories):void{
            if ( ! is_array( $link_categories ) || 0 === count( $link_categories ) ) {
                $link_categories = array( $this->_get_option( 'default_link_category' ) );
            }
            $link_categories = array_map( 'intval', $link_categories );
            $link_categories = array_unique( $link_categories );
            $this->_tp_set_object_terms( $link_id, $link_categories, 'link_category' );
            $this->_clean_bookmark_cache( $link_id );
        }//279
        /**
         * @description Updates a link in the database.
         * @param $link_data
         * @return int
         */
        protected function _tp_update_link( $link_data ):int{
            $link_id = (int) $link_data['link_id'];
            $link = $this->_get_bookmark( $link_id, ARRAY_A );
            $link = $this->_tp_slash( $link );
            if ( isset( $link_data['link_category'] ) && is_array( $link_data['link_category'] )
                && count( $link_data['link_category'] ) > 0){
                $link_cats = $link_data['link_category'];}
            else {$link_cats = $link['link_category'];}
            $link_data                  = array_merge( $link, $link_data );
            $link_data['link_category'] = $link_cats;
            return $this->_tp_insert_link( $link_data );
        }//301
        /**
         * @description Outputs the 'disabled' message for the TailoredPress Link Manager.
         */
        private function __tp_link_manager_disabled_message():void{
            if ( ! in_array( $this->pagenow, array( 'link-manager.php', 'link-add.php', 'link.php' ), true ) ) {
                return;}
            $this->_add_filter( 'pre_option_link_manager_enabled', '__return_true', 100 );
            $really_can_manage_links = $this->_current_user_can( 'manage_links' );
            $this->_remove_filter( 'pre_option_link_manager_enabled', '__return_true', 100 );
            if ( $really_can_manage_links ) {
                //todo
            }
            $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit the links for this site.' ) );
        }//333
        public function tp_link_manager_disabled_message():void{
            $this->__tp_link_manager_disabled_message();
        }
    }
}else{die;}