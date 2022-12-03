<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-6-2022
 * Time: 09:00
 */
namespace TP_Admin\Traits\AdminTemplates;
if(ABSPATH){
    trait _adm_template_05{
        /**
         * @description Output the HTML for restoring the post data from DOM storage
         * @return string
         */
        protected function _get_local_storage_notice():string{
            $output  = "<div id='local_storage_notice' class='hidden notice is-dismissible'>";
            $output .= "<p class='local-restore'>";
            $output .= $this->__('The backup of this post in your browser is different from the version below.');
            $output .= "<button type='button' class='button restore-backup'>{$this->__('Restore the backup.')}</button>";
            $output .= "</p><p class='help'>{$this->__('This will replace the current editor content with the last backup version. You can use undo and redo in the editor to get the old content back or to return to the restored version.')}</p>";
            $output .= "</div>";
            return $output;
        }//2592
        protected function _local_storage_notice():void{
            echo $this->_get_local_storage_notice();
        }//2592
        /**
         * @description Output a HTML element with a star rating for a given rating.
         * @param array|null $args
         * @return string
         */
        protected function _tp_get_star_rating($args = null):string{
            $defaults = ['rating' => 0,'type'=> 'rating','number' => 0,];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $rating = (float) str_replace( ',', '.', $parsed_args['rating'] );
            if ( 'percent' === $parsed_args['type'] ) {
                $rating = round( $rating / 10, 0 ) / 2;
            }
            $full_stars  = floor( $rating );
            $half_stars  = ceil( $rating - $full_stars );
            $empty_stars = 5 - $full_stars - $half_stars;
            if ( $parsed_args['number'] ) {
                /* translators: 1: The rating, 2: The number of ratings. */
                $format = $this->_n( '%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $parsed_args['number'] );
                $title  = sprintf( $format, $this->_number_format_i18n( $rating, 1 ), $this->_number_format_i18n( $parsed_args['number'] ) );
            } else {
                /* translators: %s: The rating. */
                $title = sprintf( $this->__( '%s rating' ), $this->_number_format_i18n( $rating, 1 ) );
            }
            $output  = "<div class='star-rating'>";
            $output .= "<span class='screen-reader-text'>$title</span>";
            $output .= str_repeat("<div class='star star-full' aria-hidden='true'></div>",$full_stars);
            $output .= str_repeat("<div class='star star-half' aria-hidden='true'></div>",$half_stars);
            $output .= str_repeat("<div class='star star-empty' aria-hidden='true'></div>",$empty_stars);
            $output .= "</div>";
            return $output;
        }//2629
        /**
         * @param array|null $args
         */
        protected function _tp_star_rating($args = null):void{
            echo $this->_tp_get_star_rating($args);
        }//2629
        /**
         * @description Outputs a notice when editing the page for posts (internal use only).
         * @return string
         */
        protected function _tp_get_posts_page_notice():string{
            return sprintf("<div class='notice notice-warning inline'><p>%s</p></div>",$this->__('You are currently editing the page that shows your latest posts.'));
        }//2680
        protected function _tp_posts_page_notice():void{
            echo $this->_tp_get_posts_page_notice();
        }//2680
        /**
         * @description Outputs a notice when editing the page,
         * @description  . for posts in the block editor (internal use only).
         * @return string
         */
        protected function _tp_get_block_editor_posts_page_notice():string{
            return $this->tp_add_inline_script('tp-notices',sprintf("tp.data.dispatch('core/notices').createWarningNotice('%s', { isDismissible: false } )",
                $this->__('You are currently editing the page that shows your latest posts.')),'after');
        }//2693
        protected function _tp_block_editor_posts_page_notice():void{
            echo $this->_tp_get_block_editor_posts_page_notice();
        }//2693
        //@description Internal helper function to find the plugin from a meta box callback.
        //protected function _get_plugin_from_callback( $callback ){return '';}//1198 might not use this or in an other form
    }
}else die;