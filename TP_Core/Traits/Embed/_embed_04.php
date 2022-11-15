<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 10:19
 */
namespace TP_Core\Traits\Embed;
use TP_Core\Traits\Inits\_init_embed;
use TP_Core\Libs\Embed\TP_ObjEmbed;
if(ABSPATH){
    trait _embed_04{
        use _init_embed;
        /**
         * @description Prints the JavaScript in the embed iframe header.
         */
        public function print_embed_scripts():void{
            $this->tp_print_inline_script_tag(
                file_get_contents( TP_LIBS_ASSETS . '/js/tp_embed_template_' . $this->_tp_scripts_get_suffix() . '.js' )
            );
        }//1079
        /**
         * @description Prepare the obj_embed HTML to be displayed in an RSS feed.
         * @param $content
         * @return mixed
         */
        protected function _obj_embed_filter_feed_content( $content ){
            $style= " style='position: absolute; clip: rect(1px, 1px, 1px, 1px);'";
            $iframe_atts = " class='tp-embedded-content' sandbox='allow-scripts' security='restricted'";
            return str_replace( "<iframe $iframe_atts $style", "<iframe $iframe_atts", $content );
        }//1094
        /**
         * @description Prints the necessary markup for the embed comments button.
         * @return bool|string
         */
        protected function _get_embed_comments_button(){
            if($this->_is_404() || ! ( $this->_get_comments_number() || $this->_comments_open())) return false;
            $class['screen_reader'] = " class='screen-reader-text'";
            $html = "<div class='tp-embed-comments'>";
            $html .= "<a href='{$this->_get_comments_link()}' target='_top'>";
            $html .= "<i class='todo'></i>";
            ob_start();
            printf($this->_n("%s <span{$class['screen_reader']}>Comment</span>","%s <span{$class['screen_reader']}>Comments</span>", $this->_get_comments_number()),
                $this->_number_format_i18n( $this->_get_comments_number() )   );
            $html .= ob_get_clean();
            $html .= "</a>";
            $html .= "</div>";
            return $html;
        }//1103
        public function print_embed_comments_button():void{
            echo $this->_get_embed_comments_button();
        }
        /**
         * @description Prints the necessary markup for the embed sharing button.
         * @return bool|string
         */
        protected function _get_embed_sharing_button(){
            if($this->_is_404()) return false;
            $html = "<div class='tp-embed-share'>";
            $html .= "<button type='button' class='tp-embed-share-dialog-open' aria-label='{$this->_esc_attr_e( 'Open sharing dialog' )}'>";
            $html .= "<i class='todo'></i>";
            $html .= "</button>";
            $html .= "</div>";
            return $html;
        }//1132
        public function print_embed_sharing_button():void{
            echo $this->_get_embed_sharing_button();
        }
        /**
         * @description Prints the necessary markup for the embed sharing dialog.
         * @return string
         */
        protected function _get_embed_sharing_dialog():string{
            if($this->_is_404()) return false;
            $btn_setup['html'] = " type='button' role='tab' aria-controls='tp-embed-share-tab-html' aria-selected='false' tabindex='-1'";
            $btn_setup['tp'] = " type='button' role='tab' aria-controls='tp-embed-share-tab-tailoredpress' aria-selected='true' tabindex='0'";
            $html  = "<div class='tp-embed-share-dialog hidden' role='dialog' aria-label='{$this->_esc_attr_e( 'Sharing options' )}'>";
            $html .= "<div class='dialog-content'>";
            $html .= "<div class='dialog-text'>";
            $html .= "<ul class='tp-embed-share-tabs' role='tablist'>";
            $html .= "<li class='tp-embed-share-tab-btn btn-tailoredpress' role='presentation'>";
            $html .= "<button{$btn_setup['tp']}>{$this->_esc_html_e( 'TailoredPress Embed' )}</button>";
            $html .= "</li>";
            $html .= "<li class='tp-embed-share-tab-btn btn-html' role='presentation'>";
            $html .= "<button{$btn_setup['html']}>{$this->_esc_html_e( 'HTML Embed' )}</button>";
            $html .= "</li>";
            $html .= "</ul>";
            $html .= "<div class='tp-embed-share-tab tab-tailoredpress' role='tabpanel' aria-hidden='false'>";
            $html .= "<input type='text' class='tp-embed share-input' aria-describedby='tp-embed-share-description-tailoredpress' tabindex='0' readonly/>";
            $html .= "<p class='share-description'>";
            $html .= $this->__('Copy and paste this URL into your TailoredPress site to embed');
            $html .= "</p>";
            $html .= "</div>";
            $html .= "<div class='tp-embed-share-tab tab-html' role='tabpanel' aria-hidden='true'>";
            $html .= "<textarea class='tp-embed share-embed' aria-describedby='tp-embed-share-description-html' tabindex='0'>";
            $html .= $this->_esc_textarea($this->_get_post_embed_html( 600, 400 ));
            $html .= "</textarea>";
            $html .= "<p class='share-description'>";
            $html .= $this->__('Copy and paste this URL into your site to embed');
            $html .= "</p>";
            $html .= "</div></div>";
            $html .= "<button type='button' class='tp-embed share-dialog-close' aria-label='{$this->_esc_attr('Close sharing dialog')}'>";
            $html .= "<i class='to-do'></i>";
            $html .= "</button>";
            $html .= "</div></div>";
            return $html;
        }//1150
        public function print_embed_sharing_dialog():void{
            echo $this->_get_embed_sharing_dialog();
        }
        /**
         * @description Prints the necessary markup for the site title in an embed template.
         * @return mixed
         */
        protected function _get_the_embed_site_title(){
            $site_title = sprintf(
                "<a href='%s' target='_top'><img src='%s' srcset='%s 2x' width='32' height='32' alt='' class='tp-embed-site-icon' /><span>%s</span></a>",
                $this->_esc_url( $this->_get_home_url() ),
                $this->_esc_url( $this->_get_site_icon_url( 32, $this->_includes_url( 'images/w-logo-blue.png' )) ),
                $this->_esc_url( $this->_get_site_icon_url( 64, $this->_includes_url( 'images/w-logo-blue.png' )) ),
                $this->_esc_html( $this->_get_bloginfo( 'name' ) )
            );
            $site_title = "<div class='tp-embed-site-title'>$site_title</div>";
            return $this->_apply_filters( 'embed_site_title_html', $site_title );
        }//1195
        public function the_embed_site_title():void{
            echo $this->_get_the_embed_site_title();
        }
        /**
         * @description Filters the oEmbed result before any HTTP requests are made.
         * @param $result
         * @param $url
         * @param $args
         * @return mixed
         */
        protected function _tp_filter_pre_obj_embed_result( $result, $url, $args ){
            $data = $this->_get_obj_embed_response_data_for_url( $url, $args );
            if ( $data ) {
                $_obj_embed = $this->_tp_obj_embed_get_object();
                $obj_embed = null;
                if($_obj_embed instanceof TP_ObjEmbed){
                    $obj_embed = $_obj_embed;
                }
                return $obj_embed->data2html( $data, $url );
            }
            return $result;
        }//1230
    }
}else die;