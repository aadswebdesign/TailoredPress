<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 17:58
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Assets\Templates\template_canvas;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Traits\Inits\_init_embed;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Libs\Block\TP_Block_Template;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    trait _block_template_01 {
        use _init_embed;
        use _init_queries;
        /**
         * @description Adds necessary filters to use 'tp_template' posts instead of theme template files.
         */
        protected function _add_template_loader_filters():void{
            if ( ! $this->_current_theme_supports( 'block-templates' ) ) return;
            $template_types = array_keys( $this->_get_default_block_template_types() );
            foreach ( $template_types as $template_type ) {
                if ( 'embed' === $template_type ) continue;
                $this->_add_filter( str_replace( '-', '', $template_type ) . '_template', 'locate_block_template', 20, 3 );
            }
            if ( isset( $_GET['_tp-find-template'] ) )
                $this->_add_filter( 'pre_get_posts', '_resolve_template_for_new_post' );
        }//14 from block-template
        /**
         * @description Find a block template with equal or higher specificity than a given PHP template file.
         * @param $template_class
         * @param $type
         * @param \array[] ...$templates
         * @return string|template_canvas
         */
        protected function _locate_block_template( $template_class, $type, ...$templates ){
            if ( ! $this->_current_theme_supports( 'block-templates' ) ) return $template_class;
            $block_template_class = TP_NS_CORE_BLOCKS_STORE.$template_class;//todo
            if ( $template_class ) {
                $index =  array_search( $block_template_class, $templates, true );
                $templates = array_slice( $templates, 0, $index + 1 );
            }
            $block_template = $this->_resolve_block_template( $type, $templates, $block_template_class );
            if (($block_template instanceof TP_Block_Template) && $block_template ) {
                if ( empty( $block_template->content ) && $this->_is_user_logged_in() )
                    $this->tp_current_template_content = sprintf( $this->__( 'Empty template: %s' ),$block_template->title);/* translators: %s: Template title */
                elseif ( ! empty( $block_template->content ) ) $this->tp_current_template_content = $block_template->content;
                if ( isset( $_GET['_tp_find_template'] ) ) $this->_tp_send_json_success( $block_template );
            }else {
                if ( $template_class ) return $template_class;
                if ( 'index' === $type ) {
                    if ( isset( $_GET['_tp_find_template'] ) ) $this->_tp_send_json_error( array( 'message' => $this->__( 'No matching template found.' ) ) );
                } else return ''; // So that the template loader keeps looking for templates.
            }
            $this->_add_action( 'tp_head', [$this,'__block_template_viewport_meta_tag'], 0 );
            $this->_remove_action( 'tp_head', [$this,'__tp_render_title_tag'], 1 );
            $this->_add_action( 'tp_head', [$this,'__block_template_render_title_tag'], 1 );
            return new template_canvas();
        }//48 from block-template
        /**
         * @description Return the correct 'wp_template' to render for the request template type.
         * @param $template_type
         * @param $template_hierarchy
         * @param $fallback_template
         * @return mixed
         */
        protected function _resolve_block_template( $template_type, $template_hierarchy, $fallback_template ){
            if ( ! $template_type )  return null;
            if ( empty( $template_hierarchy ) ) $template_hierarchy = [$template_type];
            $slugs = array_map('_strip_template_file_suffix', $template_hierarchy);
            $get_theme = $this->_tp_get_theme();
            if($get_theme instanceof TP_Theme){}//todo1
            $query = ['theme' => $get_theme->get_stylesheet(),'slug__in' => $slugs,];
            $templates = $this->_get_block_templates( $query );
            $slug_priorities = array_flip( $slugs );
            usort(
                $templates,
                static function ( $template_a, $template_b ) use ( $slug_priorities ) {
                    return $slug_priorities[ $template_a->slug ] - $slug_priorities[ $template_b->slug ];
                }
            );
            $theme_base_path = $this->_get_stylesheet_directory() . DIRECTORY_SEPARATOR;
            $parent_theme_base_path = $this->_get_template_directory() . DIRECTORY_SEPARATOR;
            if ( strpos( $fallback_template, $theme_base_path ) === 0 && strpos( $fallback_template, $parent_theme_base_path ) === false ) {
                $fallback_template_slug = substr($fallback_template,strpos( $fallback_template, $theme_base_path ) + strlen( $theme_base_path ), -4);
                if ( count( $templates ) && $fallback_template_slug === $templates[0]->slug && 'theme' === $templates[0]->source){
                    $template_file = $this->_get_block_template_file( 'tp_template', $fallback_template_slug );
                    if ( $template_file && $this->_get_template() === $template_file['theme'] ) array_shift( $templates );
                }
            }
            return count( $templates ) ? $templates[0] : null;
        }//132 from block-template
         /**
         * @description Displays title tag with content, regardless of whether theme has title-tag support.
         */
        protected function _block_template_render_title_tag():void{
            echo "<title>{$this->_tp_get_document_title()}</title>\n";
        }//212 from block-template
        /**
         * @description Returns the markup for the current template.
         * @return null|string
         */
        protected function _get_the_block_template_html():string{
            $this->tp_embed = $this->_init_embed();
            if ( ! $this->tp_current_template_content ) {
                if ( $this->_is_user_logged_in() ) {
                    return "<h1>{$this->_esc_html__( 'No matching template found' )}</h1>";
                }
                return null;
            }
            $content = $this->tp_embed->run_shortcode( $this->tp_current_template_content );
            $content = $this->tp_embed->auto_embed( $content );
            $content = $this->_do_blocks( $content );
            $content = $this->_tp_texturize( $content );
            $content = $this->_convert_smilies( $content );
            $content = $this->_shortcode_un_autop( $content );
            $content = $this->_tp_filter_content_tags( $content );
            $content = $this->_do_shortcode( $content );
            $content = str_replace( ']]>', ']]&gt;', $content );
            return "<div class='tp-site-blocks'>$content</div>";
        }//227 from block-template
        /**
         * @description Renders a 'viewport' meta tag.
         */
        public function block_template_viewport_meta_tag():void{
            echo "<meta name='viewport' content='width=device-width, initial-scale=1' />\n";
        }//261 from block-template
        /**
         * @description Strips .php or .html suffix from template file names.
         * @param $template_file
         * @return mixed
         */
        protected function _strip_template_file_suffix( $template_file ){
            return preg_replace( '/\.(php|html)$/', '', $template_file );
        }//274 from block-template
        /**
         * @description Removes post details from block context when rendering a block template.
         * @param $context
         * @return mixed
         */
        protected function _block_template_render_without_post_block_context( $context ){
            if ( isset( $context['postType'] ) && 'tp_template' === $context['postType'] ) {
                unset( $context['postId'], $context['postType'] );
            }
            return $context;
        }//288 from block-template
        /**
         * @description Sets the current TP_Query to return auto-draft posts.
         * @param $tp_query
         */
        protected function _resolve_template_for_new_post( $tp_query ):void{
            $this->tp_query = $this->_init_query($tp_query);
            $this->_remove_filter( 'pre_get_posts', '_resolve_template_for_new_post' );
            $page_id = $this->tp_query->query_main['page_id'] ?? null;
            $p = $this->tp_query->query['p'] ?? null;
            $post_id = $page_id ?: $p;
            $post    = $this->_get_post( $post_id );
            if(($this->tp_query instanceof TP_Query) && $post && 'auto-draft' === $post->post_status && $this->_current_user_can('edit_post', $post->ID)) {
                $this->tp_query->set( 'post_status', 'auto-draft' );
            }
        }//314 from block-template
        /**
         * @description Returns the correct template for the site's home page.
         * @return array|null
         */
        protected function _resolve_home_block_template():array{
            $show_on_front = $this->_get_option( 'show_on_front' );
            $front_page_id = $this->_get_option( 'page_on_front' );
            if ( 'page' === $show_on_front && $front_page_id )
                return ['postType' => 'page','postId' => $front_page_id,];
            $hierarchy = array( 'front-page', 'home', 'index' );
            $template  = $this->_resolve_block_template( 'home', $hierarchy, '' );
            if ( ! $template ) return null;
            return ['postType' => 'tp_template','postId' => $template->id,];
        }//343
    }
}else die;