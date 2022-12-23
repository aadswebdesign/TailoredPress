<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 15:46
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _template_02 {
        protected function _get_front_page_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);
        }//413 uses __get_query_template
        protected function _get_privacy_policy_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);
        }//431
        protected function _get_page_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);
        }//465 todo
        protected function _get_search_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);
        }//510 todo
        protected function _get_single_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);
        }//545 todo
        protected function _get_embed_template($theme_name = null,$args = null):string{
            //$post_formats; standard (default), aside, gallery, link, image, quote, status, video, audio and chat.
            //seven post types; posts, pages, attachments, revisions, navigation menus, custom CSS, and changesets
            return $this->_get_query_template('Embed',$theme_name,$args);
        }//594
        protected function _get_singular_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);
        }//624
        protected function _get_attachment_template($theme_name = null,$args = null):string {
            return $this->_get_query_template('',$theme_name,$args);
        }//657 todo
        protected function _locate_template_classes($template_classes = null,$template_args = null, $args = null, $load = false):string{
            $located = '';
            $theme_templates = [];
            $theme_name = $template_args['theme_name'] ?? TP_NS_DEFAULT_THEME;
            foreach ( (array) $template_classes as $template_class ) {
                if ( ! $template_class ) {
                    continue;
                }
                $theme_template = TP_NS_CONTENT.$theme_name.TP_NS_THEME_TEMPLATE.$template_class;
                if(class_exists($theme_template)){
                    $located = new $theme_template($args);
                    break;
                }

            }
            if ( $load && '' !== $located )
                $this->_load_template( $located, $args );
            return $located;





        }//697
        protected function _load_template($_template_file, $require_once = true, array ...$args){}//749


        //protected function _locate_theme($theme_class):?string{}//added
    }
}else die;