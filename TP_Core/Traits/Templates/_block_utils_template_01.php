<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 19:25
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
if(ABSPATH){
    trait _block_utils_template_01 {
        /**
         * @description For backward compatibility reasons,
         * @param null $theme_stylesheet
         * @return array
         */
        protected function _get_block_theme_folders( $theme_stylesheet = null ):array{
            $theme_name = null === $theme_stylesheet ?? $this->_get_stylesheet();
            $root_dir   = $this->_get_theme_root( $theme_name );
            $theme_dir  = "$root_dir/$theme_name";
            if ( file_exists( $theme_dir . '/block-templates' ) || file_exists( $theme_dir . '/block-template-parts' ) )
                return ['tp_template' => 'block-templates', 'tp_template_part' => 'block-template-parts',];
            return ['tp_template' => 'templates','tp_template_part' => 'parts',];
        }//39
        /**
         * @description Returns a filtered list of allowed area values for template parts.
         * @return mixed
         */
        protected function _get_allowed_block_template_part_areas(){
            $default_area_definitions = [
                ['area' => '',//todo TP_TEMPLATE_PART_AREA_UNCATEGORIZED
                    'label' => $this->__( 'General' ),
                    'description' => $this->__(
                        'General templates often perform a specific role like displaying post content, and are not tied to any particular area.'
                    ),'icon' => 'layout','area_tag' => 'div',],
                ['area' => '',//todo TP_TEMPLATE_PART_AREA_HEADER
                    'label' => $this->__( 'Header' ),
                    'description' => $this->__(
                        'The Header template defines a page area that typically contains a title, logo, and main navigation.'
                    ), 'icon' => 'header','area_tag' => 'header',
                ],
                ['area' => '',//todo TP_TEMPLATE_PART_AREA_FOOTER
                    'label' => $this->__( 'Footer' ),
                    'description' => $this->__(
                        'The Footer template defines a page area that typically contains site credits, social links, or any other combination of blocks.'
                    ), 'icon' => 'footer', 'area_tag' => 'footer',],
            ];
            return $this->_apply_filters( 'default_tp_template_part_areas', $default_area_definitions );
        }//64 from block-template-utils
        /**
         * @description Returns a filtered list of default template types,
         * containing their localized titles and descriptions.
         * @return mixed
         */
        protected function _get_default_block_template_types(){
            $default_template_types = [
                'index' =>['title' => $this->_x( 'Index', 'Template name' ),'description' => $this->__( 'Displays posts.' ),],
                'home' =>['title' => $this->_x( 'Home', 'Template name' ),'description' => $this->__( 'Displays as the site\'s home page, or as the Posts page when a static home page isn\'t set.' ),],
                'front-page' =>['title' => $this->_x( 'Front Page', 'Template name' ),'description' => $this->__( 'Displays as the site\'s home page.' ),],
                'singular' =>['title' => $this->_x( 'Singular', 'Template name' ),'description' => $this->__( 'Displays a single post or page.' ),],
                'single' =>['title' => $this->_x( 'Single Post', 'Template name' ),'description' => $this->__( 'Displays a single post.' ),],
                'page' =>['title' => $this->_x( 'Page', 'Template name' ),'description' => $this->__( 'Displays a single page.' ),],
                'archive' =>['title' => $this->_x( 'Archive', 'Template name' ),'description' => $this->__( 'Displays post categories, tags, and other archives.' ),],
                'author' =>['title' => $this->_x( 'Author', 'Template name' ),'description' => $this->__( 'Displays latest posts written by a single author.' ),],
                'category' =>['title' => $this->_x( 'Category', 'Template name' ),'description' => $this->__( 'Displays latest posts in single post category.' ),],
                'taxonomy' =>['title' => $this->_x( 'Taxonomy', 'Template name' ),'description' => $this->__( 'Displays latest posts from a single post taxonomy.' ),],
                'date' =>['title' => $this->_x( 'Date', 'Template name' ),'description' => $this->__( 'Displays posts from a specific date.' ),],
                'tag' =>['title' => $this->_x( 'Tag', 'Template name' ),'description' => $this->__( 'Displays latest posts with single post tag.' ),],
                'attachment' =>['title' => $this->__( 'Media' ),'description' => $this->__( 'Displays individual media items or attachments.' ),],
                'search' =>['title' => $this->_x( 'Search', 'Template name' ),'description' => $this->__( 'Template used to display search results.' ),],
                'privacy-policy' =>['title' => $this->__( 'Privacy Policy' ),'description' => $this->__( 'Displays the privacy policy page.' ),],
                '404' =>['title' => $this->_x( '404', 'Template name' ),'description' => $this->__( 'Displays when no content is found.' ),],
            ];
            return $this->_apply_filters( 'default_template_types', $default_template_types );
        }//114 from block-template-utils
        /**
         * @description Checks whether the input 'area' is a supported value.
         * @param $type
         * @return string
         */
        protected function _filter_block_template_part_area( $type ):string{
            $allowed_areas = array_map(
                static function ( $item ) {
                    return $item['area'];
                },
                $this->_get_allowed_block_template_part_areas()
            );
            if ( in_array( $type, $allowed_areas, true ) ) return $type;
            $warning_message = sprintf( $this->__( '"%1$s" is not a supported tp_template_part area value and has been added as "%2$s".' ), $type, '' );//todo TP_TEMPLATE_PART_AREA_UNCATEGORIZED
            trigger_error( $warning_message, E_USER_NOTICE );
            return '';//todo TP_TEMPLATE_PART_AREA_UNCATEGORIZED
        }//203 from block-template-utils
        /**
         * @description Finds all nested template part file paths in a theme's directory.
         * @param $base_directory
         * @return array
         */
        protected function _get_block_templates_paths( $base_directory ):array{
            $path_list = [];
            if ( file_exists( $base_directory ) ) {
                $nested_files      = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base_directory ) );
                $nested_html_files = new \RegexIterator( $nested_files, '/^.+\.html$/i', \RecursiveRegexIterator::GET_MATCH );
                foreach ( $nested_html_files as $path => $file ) $path_list[] = $path;
            }
            return $path_list;
        }//233 from block-template-utils
        /**
         * @description Retrieves the template file from the theme for a given slug.
         * @param $template_type
         * @param $slug
         * @return array|null|string
         */
        protected function _get_block_template_file( $template_type, $slug ){
            if ( 'tp_template' !== $template_type && 'tp_template_part' !== $template_type ) return null;
            $themes = [$this->_get_stylesheet() => $this->_get_stylesheet_directory(), $this->_get_template()   => $this->_get_template_directory(),];
            foreach ( $themes as $theme_slug => $theme_dir ) {
                $template_base_paths = $this->_get_block_theme_folders( $theme_slug );
                $file_path           = $theme_dir . '/' . $template_base_paths[ $template_type ] . '/' . $slug . '.html';
                if ( file_exists( $file_path ) ) {
                    $new_template_item = ['slug' => $slug,'path' => $file_path,'theme' => $theme_slug,'type' => $template_type,];
                    if ( 'tp_template_part' === $template_type ) return $this->_add_block_template_part_area_info( $new_template_item );
                    if ( 'tp_template' === $template_type ) return $this->_add_block_template_info( $new_template_item );
                    return $new_template_item;
                }
            }
            return null;
        }//256 from block-template-utils
        /**
         * @description Retrieves the template files from the theme.
         * @param $template_type
         * @return array|null
         */
        protected function _get_block_templates_files( $template_type ):?array{
            if ( 'tp_template' !== $template_type && 'tp_template_part' !== $template_type ) return null;
            $themes = [$this->_get_stylesheet() => $this->_get_stylesheet_directory(), $this->_get_template() => $this->_get_template_directory(),];
            $template_files = [];
            foreach ( $themes as $theme_slug => $theme_dir ) {
                $template_base_paths  = $this->_get_block_theme_folders( $theme_slug );
                $theme_template_files = $this->_get_block_templates_paths( $theme_dir . '/' . $template_base_paths[ $template_type ] );
                foreach ( $theme_template_files as $template_file ) {
                    $template_base_path = $template_base_paths[ $template_type ];
                    $template_slug      = substr(
                        $template_file,
                        // Starting position of slug.
                        strpos( $template_file, $template_base_path . DIRECTORY_SEPARATOR ) + 1 + strlen( $template_base_path ),
                        // Subtract ending '.html'.
                        -5
                    );
                    $new_template_item = ['slug' => $template_slug, 'path' => $template_file, 'theme' => $theme_slug, 'type' => $template_type,];
                    if ( 'tp_template_part' === $template_type ) $template_files[] = $this->_add_block_template_part_area_info( $new_template_item );
                    if ( 'tp_template' === $template_type ) $template_files[] = $this->_add_block_template_info( $new_template_item );
                }
            }
            return $template_files;
        }//301 from block-template-utils
        /**
         * @description Attempts to add custom template information to the template item.
         * @param $template_item
         * @return mixed
         */
        protected function _add_block_template_info( $template_item ){
            if ( ! TP_Theme_JSON_Resolver::theme_has_support() ) return $template_item;
            $theme_data = TP_Theme_JSON_Resolver::get_theme_data()->get_custom_templates();
            if ( isset( $theme_data[ $template_item['slug'] ] ) ) {
                $template_item['title'] = $theme_data[ $template_item['slug'] ]['title'];
                $template_item['postTypes'] = $theme_data[ $template_item['slug'] ]['postTypes'];
            }
            return $template_item;
        }//352 from block-template-utils
        /**
         * @description Attempts to add the template part's area information to the input template.
         * @param $template_info
         * @return mixed
         */
        protected function _add_block_template_part_area_info( $template_info ){
            if ( TP_Theme_JSON_Resolver::theme_has_support() )
                $theme_data = TP_Theme_JSON_Resolver::get_theme_data()->get_template_parts();
            if ( isset( $theme_data[ $template_info['slug'] ]['area'] ) ) {
                $template_info['title'] = $theme_data[ $template_info['slug'] ]['title'];
                $template_info['area']  = $this->_filter_block_template_part_area( $theme_data[ $template_info['slug'] ]['area'] );
            } else $template_info['area'] = '';//todo TP_TEMPLATE_PART_AREA_UNCATEGORIZED
            return $template_info;
        }//376 from block-template-utils
        /**
         * @description Returns an array containing the references of the passed blocks and their inner blocks.
         * @param $blocks
         * @return array
         */
        protected function _flatten_blocks( &$blocks ):array{
            $all_blocks = [];
            $queue      = [];
            foreach ( $blocks as &$block ) $queue[] = &$block;
            unset($block);
            while ( count( $queue ) > 0 ) {
                $block = &$queue[0];
                array_shift( $queue );
                $all_blocks[] = &$block;
                if ( ! empty( $block['innerBlocks'] ) )  foreach ( $block['innerBlocks'] as &$inner_block ) $queue[] = &$inner_block;
            }
            return $all_blocks;
        }//402 from block-template-utils
    }
}else die;