<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 19:25
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Theme;
use TP_Core\Libs\Block\TP_Block_Template;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    trait _block_utils_template_02 {
        use _init_error;
        /**
         * @description Parses wp_template content and injects the current theme's stylesheet as a theme attribute into each tp_template_part
         * @param $template_content
         * @return string
         */
        protected function _inject_theme_attribute_in_block_template_content( $template_content ):string{
            $_get_theme = $this->_tp_get_theme();
            $get_theme = null;
            if($_get_theme  instanceof  TP_Theme){
                $get_theme = $_get_theme;
            }
            $has_updated_content = false;
            $new_content         = '';
            $template_blocks     = $this->_parse_blocks( $template_content );
            $blocks = $this->_flatten_blocks( $template_blocks );
            foreach ( $blocks as &$block ) {
                if ('core/template-part' === $block['blockName'] && ! isset( $block['attrs']['theme'] )) {
                    $block['attrs']['theme'] = $get_theme->get_stylesheet();
                    $has_updated_content     = true;
                }
            }
            unset($block);
            if ( $has_updated_content ) {
                foreach ( $template_blocks as &$block ) $new_content .= $this->_serialize_block( $block );
                return $new_content;
            }
            return $template_content;
        }//435 from block-template-utils
        /**
         * @param $template_content
         * @return string
         */
        protected function _remove_theme_attribute_in_block_template_content( $template_content ):string{
            $has_updated_content = false;
            $new_content         = '';
            $template_blocks     = $this->_parse_blocks( $template_content );
            $blocks = $this->_flatten_blocks( $template_blocks );
            foreach ( $blocks as $key => $block ) {
                if ( 'core/template-part' === $block['blockName'] && isset( $block['attrs']['theme'] ) ) {
                    unset( $blocks[ $key ]['attrs']['theme'] );
                    $has_updated_content = true;
                }
            }
            if ( ! $has_updated_content ) return $template_content;
            foreach ( $template_blocks as $block ) $new_content .= $this->_serialize_block( $block );
            return $new_content;
        }//471 from block-template-utils
        /**
         * @description Build a unified template object based on a theme file.
         * @param $template_file
         * @param $template_type
         * @return TP_Block_Template
         */
        protected function _build_block_template_result_from_file( $template_file, $template_type ):TP_Block_Template{
            $_get_theme = $this->_tp_get_theme();
            $get_theme = null;
            if($_get_theme  instanceof  TP_Theme){
                $get_theme = $_get_theme;
            }
            $default_template_types = $this->_get_default_block_template_types();
            $template_content       = file_get_contents( $template_file['path'] );
            $theme                  = $get_theme->get_stylesheet();
            $template                 = new TP_Block_Template();
            $template->id             = $theme . '//' . $template_file['slug'];
            $template->theme          = $theme;
            $template->content        = $this->_inject_theme_attribute_in_block_template_content( $template_content );
            $template->slug           = $template_file['slug'];
            $template->source         = 'theme';
            $template->type           = $template_type;
            $template->title          = ! empty( $template_file['title'] ) ? $template_file['title'] : $template_file['slug'];
            $template->status         = 'publish';
            $template->has_theme_file = true;
            $template->is_custom      = true;
            if ( 'tp_template' === $template_type && isset( $default_template_types[ $template_file['slug'] ] ) ) {
                $template->description = $default_template_types[ $template_file['slug'] ]['description'];
                $template->title       = $default_template_types[ $template_file['slug'] ]['title'];
                $template->is_custom   = false;
            }
            if ( 'tp_template' === $template_type && isset( $template_file['postTypes'] ) ) $template->post_types = $template_file['postTypes'];
            if ( 'tp_template_part' === $template_type && isset( $template_file['area'] ) ) $template->area = $template_file['area'];
            return $template;
        }//506 from block-template-utils
        /**
         * @description Build a unified template object based a post Object.
         * @param $post
         * @return TP_Block_Template | TP_Error
         */
        protected function _build_block_template_result_from_post( $post ){
            $_get_theme = $this->_tp_get_theme();
            $get_theme = null;
            if($_get_theme  instanceof  TP_Theme){
                $get_theme = $_get_theme;
            }
            $default_template_types = $this->_get_default_block_template_types();
            $terms                  = $this->_get_the_terms( $post, 'tp_theme' );
            if ( $this->_init_error( $terms ) ) return $terms;
            if ( ! $terms ) return new TP_Error( 'template_missing_theme', $this->__( 'No theme is defined for this template.' ) );
            $theme          = $terms[0]->name;
            $has_theme_file = $get_theme->get_stylesheet() === $theme &&
                null !== $this->_get_block_template_file( $post->post_type, $post->post_name );
            $origin = $this->_get_post_meta( $post->ID, 'origin', true );
            $template                 = new TP_Block_Template();
            $template->tp_id          = $post->ID;
            $template->id             = $theme . '//' . $post->post_name;
            $template->theme          = $theme;
            $template->content        = $post->post_content;
            $template->slug           = $post->post_name;
            $template->source         = 'custom';
            $template->origin         = ! empty( $origin ) ? $origin : null;
            $template->type           = $post->post_type;
            $template->description    = $post->post_excerpt;
            $template->title          = $post->post_title;
            $template->status         = $post->post_status;
            $template->has_theme_file = $has_theme_file;
            $template->is_custom      = true;
            $template->author         = $post->post_author;
            if ( 'tp_template' === $post->post_type && isset( $default_template_types[ $template->slug ] ) )
                $template->is_custom = false;
            if ( 'tp_template_part' === $post->post_type ) {
                $type_terms = $this->_get_the_terms( $post, 'tp_template_part_area' );
                if (false !== $type_terms && ! $this->_init_error( $type_terms ))
                    $template->area = $type_terms[0]->name;
            }
            return $template;
        }//550 from block-template-utils
        /**
         * @description Retrieves a list of unified template objects based on a query.
         * @param array $query
         * @param string $template_type
         * @return mixed
         */
        protected function _get_block_templates( $query = [], $template_type = 'tp_template' ){
            $_get_theme = $this->_tp_get_theme();
            $get_theme = null;
            if($_get_theme  instanceof  TP_Theme){
                $get_theme = $_get_theme;
            }
            $templates = $this->_apply_filters( 'pre_get_block_templates', null, $query, $template_type );
            if ( ! is_null( $templates ) ) return $templates;
            $post_type     = $query['post_type'] ?? '';
            $tp_query_args = ['post_status' => ['auto-draft', 'draft', 'publish'],
                'post_type' => $template_type,'posts_per_page' => -1,'no_found_rows' => true,
                'tax_query' => [['taxonomy' => 'tp_theme','field' => 'name','terms' => $get_theme->get_stylesheet(),],],
            ];
            if ( 'tp_template_part' === $template_type && isset( $query['area'] ) ) {
                $tp_query_args['tax_query'][] = ['taxonomy' => 'tp_template_part_area','field' => 'name','terms' => $query['area'],];
                $tp_query_args['tax_query']['relation'] = 'AND';
            }
            if ( isset( $query['slug__in'] ) ) $tp_query_args['post_name__in'] = $query['slug__in'];
            if ( isset( $query['tp_id'] ) ) $tp_query_args['p'] = $query['tp_id'];
            else $tp_query_args['post_status'] = 'publish';
            $template_query = new TP_Query( $tp_query_args );
            $query_result   = [];
            foreach ( $template_query->posts as $post ) {
                $template = $this->_build_block_template_result_from_post( $post );
                if ( $this->_init_error( $template ) ) continue;
                if ( $post_type && ! $template->is_custom ) continue;
                $query_result[] = $template;
            }
            if ( ! isset( $query['tp_id'] ) ) {
                $template_files = $this->_get_block_templates_files( $template_type );
                foreach ( $template_files as $template_file ) {
                    $template = $this->_build_block_template_result_from_file( $template_file, $template_type );
                    if ( $post_type && ! $template->is_custom ) continue;
                    if ( $post_type && isset( $template->post_types ) && ! in_array( $post_type, $template->post_types, true ))
                        continue;
                    $is_not_custom   = !in_array( $get_theme->get_stylesheet() . '//' . $template_file['slug'], array_column( $query_result, 'id' ), true);
                    $fits_slug_query = ! isset( $query['slug__in'] ) || in_array( $template_file['slug'], $query['slug__in'], true );
                    $fits_area_query = ! isset( $query['area'] ) || $template_file['area'] === $query['area'];
                    $should_include  = $is_not_custom && $fits_slug_query && $fits_area_query;
                    if ( $should_include ) $query_result[] = $template;
                }
            }
            return $this->_apply_filters( 'get_block_templates', $query_result, $query, $template_type );
        }//615 from block-template-utils
        /**
         * @description Retrieves a single unified template object using its id.
         * @param $id
         * @param string $template_type
         * @return mixed
         */
        protected function _get_block_template( $id, $template_type = 'tp_template' ){
            $block_template = $this->_apply_filters( 'pre_get_block_template', null, $id, $template_type );
            if ( ! is_null( $block_template ) ) return $block_template;
            $parts = explode( '//', $id, 2 );
            if ( count( $parts ) < 2 ) return null;
            @list( $theme, $slug ) = $parts;
            $tp_query_args = ['post_name__in' => [$slug],
                'post_type' => $template_type, 'post_status' => ['auto-draft', 'draft', 'publish', 'trash'],
                'posts_per_page' => 1, 'no_found_rows' => true,
                'tax_query' => [['taxonomy' => 'tp_theme','field' => 'name','terms' => $theme,],],];
            $template_query = new TP_Query( $tp_query_args );
            $posts = $template_query->posts;
            if ( count( $posts ) > 0 ) {
                $template = $this->_build_block_template_result_from_post( $posts[0] );
                if ( ! $this->_init_error( $template ) ) return $template;
            }
            $block_template = $this->_get_block_file_template( $id, $template_type );
            return $this->_apply_filters( 'get_block_template', $block_template, $id, $template_type );
        }//750 from block-template-utils
        /**
         * @description Retrieves a single unified template object using its id.
         * @param $id
         * @param string $template_type
         * @return TP_Block_Template
         */
        protected function _get_block_file_template( $id, $template_type = 'tp_template' ):TP_Block_Template{
            $_get_theme = $this->_tp_get_theme();
            $get_theme = null;
            if($_get_theme  instanceof  TP_Theme){
                $get_theme = $_get_theme;
            }
            $block_template = $this->_apply_filters( 'pre_get_block_file_template', null, $id, $template_type );
            if ( ! is_null( $block_template ) ) return $block_template;
            $parts = explode( '//', $id, 2 );
            if ( count( $parts ) < 2 )
                return $this->_apply_filters( 'get_block_file_template', null, $id, $template_type );
            @list( $theme, $slug ) = $parts;
            if ( $get_theme->get_stylesheet() !== $theme )
                return $this->_apply_filters( 'get_block_file_template', null, $id, $template_type );
            $template_file = $this->_get_block_template_file( $template_type, $slug );
            if ( null === $template_file )
                return $this->_apply_filters( 'get_block_file_template', null, $id, $template_type );
            $block_template = $this->_build_block_template_result_from_file( $template_file, $template_type );
            return $this->_apply_filters( 'get_block_file_template', $block_template, $id, $template_type );
        }//822 from block-template-utils
        /**
         * @description Print a template-part.
         * @param $args
         * @param array|null $block_template_args
         * @return string
         */
        protected function _get_block_template_part($args, $block_template_args = null ):string{
            $name = $block_template_args['name'];
            $theme_name = $block_template_args['theme_name'];
            $class_name = $block_template_args['class_name'];
            $class_suffix =  $block_template_args['class_suffix'];
            $class_Default = $class_suffix ?: '_Base';
            $template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null|| $class_suffix !== null ){
                $template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name.$class_suffix,$args);
            }else{
                $template = $this->_tp_load_class('default_block_parts',TP_NS_CORE_TEMPLATES,'TP_Block_Template_Part'.$class_Default,$args);
            }
            $template_part = $this->_get_block_template($template, $name );
            if ( ! $template_part || empty( $template_part->content ) ) return false;
            return $this->_do_blocks( $template_part->content );//todo 'do_blocks' might not be needed here?
        }//879 from block-template-utils todo testing
        protected function _block_template_part($args, $block_template_args = null ):void{
            echo $this->_get_block_template_part($args, $block_template_args);
        }
        /**
         * @description Print the header template-part.
         */
        protected function _block_header_area():void{
            $this->_block_template_part( 'header' );
        }//892 from block-template-utils might not be needed
        /**
         * @description Print the footer template-part.
         */
        protected function _block_footer_area():void{
           $this->_block_template_part( 'footer' );
        }//901 from block-template-utils might not be needed
        /**
         * @description Creates an export of the current templates and
         * @description . template parts from the site editor at the specified path in a ZIP file.
         * @return string|TP_Error
         */
        protected function _tp_generate_block_templates_export_file(){
            if ( ! class_exists( 'ZipArchive' ) )
                return new TP_Error( 'missing_zip_package', $this->__( 'Zip Export not supported.' ) );
            $obscura  = $this->_tp_generate_password( 12, false, false );
            $filename = $this->_get_temp_dir() . 'edit-site-export-' . $obscura . '.zip';
            $zip = new \ZipArchive();
            if ( true !== $zip->open( $filename, \ZipArchive::CREATE ) )
                return new TP_Error( 'unable_to_create_zip', $this->__( 'Unable to open export file (archive) for writing.' ) );
            $zip->addEmptyDir( 'theme' );
            $zip->addEmptyDir( 'theme/templates' );
            $zip->addEmptyDir( 'theme/parts' );
            $templates = $this->_get_block_templates();
            foreach ( $templates as $template ) {
                $template->content = $this->_remove_theme_attribute_in_block_template_content( $template->content );
                $zip->addFromString('theme/templates/' . $template->slug . '.html',$template->content);
            }
            $template_parts = $this->_get_block_templates( array(), 'tp_template_part' );
            foreach ( $template_parts as $template_part )
                $zip->addFromString('theme/parts/' . $template_part->slug . '.html',$template_part->content);
            $zip->close();
            return $filename;
        }//914 from block-template-utils
    }
}else die;