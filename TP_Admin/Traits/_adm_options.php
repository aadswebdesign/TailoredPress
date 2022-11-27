<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-10-2022
 * Time: 08:24
 */
namespace TP_Admin\Traits;
if(ABSPATH){
    trait _adm_options{

        public function get_options_index_stuff():string{
            ob_start();
            ?>
            <script id='index_stuff_js'>console.log('TODO:','index_stuff_js, (added and or I\'m going to need that or not???)');</script>
            <?php
            return ob_get_clean();
        }



        /**
         * @description  Output JavaScript to toggle display of additional settings if avatars are disabled.
         * @return string
         */
        public function get_options_discussion_add_js():string{
            ob_start();
            ?>
            <script id='discussion_add_js'>console.log('TODO:','discussion_add_js');</script>
            <?php
            return ob_get_clean();
        }//15
        /**
         * @description Display JavaScript on the page.
         * @return string
         */
        public function get_options_general_add_js():string{
            ob_start();
            ?>
            <script id='general_add_js'>console.log('TODO:','general_add_js');</script>
            <?php
            return ob_get_clean();
        }//36
        /**
         * @description  Display JavaScript on the page.
         * @return string
         */
        public function get_options_reading_add_js():string{
            ob_start();
            ?>
            <script id='reading_add_js'>console.log('TODO:','reading_add_js');</script>
            <?php
            return ob_get_clean();
        }//109
        /**
         * @description Render the site charset setting.
         * @return string
         */
        public function get_options_reading_blog_charset():string{
            $output  = "<ul><li>";
            $output .= "<dd><input name='blog_charset' class='regular-text' type='text' value='{$this->_esc_attr($this->_get_option( 'blog_charset' ))}'/></dd>";
            $output .= "<dt><p>{$this->__('TODO')}</p></dt>";
            $output .= "</li></ul>";
            return $output;
        }//131
    }
}else{die;}

