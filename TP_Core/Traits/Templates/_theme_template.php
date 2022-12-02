<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-3-2022
 * Time: 02:43
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
//use TP_Core\Traits\Inits\_init_template;
use TP_Core\Libs\TP_Theme;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
if(ABSPATH){
    trait _theme_template {
        use _init_error;
        //use _init_template;
        /**
         * @description Sets a custom slug when creating auto-draft template parts.
         * @param $post_id
         */
        protected function _tp_set_unique_slug_on_create_template_part( $post_id ):void{
            $post = $this->_get_post( $post_id );
            if ( 'auto-draft' !== $post->post_status ) return;
            if ( ! $post->post_name )
                $this->_tp_update_post(['ID' => $post_id,'post_name' => 'custom_slug_' . random_bytes(5),]);
            $terms = $this->_get_the_terms( $post_id, 'tp_theme' );
            if ( ! is_array( $terms ) || ! count( $terms ) ) {
                $_get_theme = $this->_tp_get_theme();
                if($_get_theme  instanceof TP_Theme ){
                    $this->_tp_set_post_terms( $post_id, $_get_theme->get_stylesheet(), 'tp_theme' );
                }
            }
        }//13 from theme-template
        /**
         * @description Generates a unique slug for templates.
         * @param $override_slug
         * @param $slug
         * @param $post_ID
         * @param $post_type
         * @return string
         */
        protected function _tp_filter_tp_template_unique_post_slug( $override_slug, $slug, $post_ID, $post_type ):string{ //not used , $post_status
            if ( 'tp_template' !== $post_type && 'tp_template_part' !== $post_type )
                return $override_slug;
            if ( ! $override_slug ) $override_slug = $slug;
            $_get_theme = $this->_tp_get_theme();
            $theme = null;
            if($_get_theme  instanceof TP_Theme ){
                $theme = $_get_theme->get_stylesheet();
            }
            $terms = $this->_get_the_terms( $post_ID, 'tp_theme' );
            if ( $terms && ! $this->_init_error( $terms ) )
                $theme = $terms[0]->name;
            $check_query_args = ['post_name__in' => [ $override_slug],
                'post_type' => $post_type,'posts_per_page' => 1,'no_found_rows' => true,'post__not_in' => [$post_ID],
                'tax_query' => [['taxonomy' => 'tp_theme','field' => 'name','terms' => $theme,],],];
            $check_query      = new TP_Query( $check_query_args );
            $posts            = $check_query->posts;
            if ( count( $posts ) > 0 ) {
                $suffix = 2;
                do {
                    $query_args                  = $check_query_args;
                    $alt_post_name               = $this->_truncate_post_slug( $override_slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
                    $query_args['post_name__in'] = array( $alt_post_name );
                    $query                       = new TP_Query( $query_args );
                    $suffix++;
                } while ( count( $query->posts ) > 0 );
                $override_slug = $alt_post_name;
            }
            return $override_slug;
        }//47 from theme-template
        /**
         * @description Prints the skip-link script & styles.
         * @return string
         */
        private function __block_template_skip_link_script():string{
            $html = "<script>";
            $html .= "todo";
            $html .= "</script>";
            return $html;
        }
        private function __block_template_skip_link_style():string{
            $html = "<style id='skip_link_styles'>";
            $html .= "todo";
            $html .= "</style>";
            return $html;
        }
        protected function _get_the_block_template_skip_link():string{
            if ( ! $this->_current_theme_supports( 'block-templates' ) ) return false;
            if ( ! $this->tp_current_template_content ) return false;
            $output  = $this->__block_template_skip_link_style();
            $output .= $this->__block_template_skip_link_script();
            return $output;
        }//111 from theme-template
        protected function _the_block_template_skip_link():void{
            echo $this->_get_the_block_template_skip_link();
        }
        /**
         * @description Enables the block templates (editor mode) for themes with theme.json by default.
         */
        protected function _tp_enable_block_templates():void{
            if ( $this->_tp_is_block_theme() || TP_Theme_JSON_Resolver::theme_has_support() )
                $this->_add_theme_support( 'block-templates' );
        }//213 from theme-template
    }
}else die;