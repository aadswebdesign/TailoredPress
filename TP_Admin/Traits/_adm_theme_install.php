<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-10-2022
 * Time: 20:14
 */
namespace TP_Admin\Traits;
use TP_Admin\Libs\AdmLists\TP_Themes_List_Block;
use TP_Admin\Libs\AdmLists\TP_Theme_Install_List_Block;
if(ABSPATH){
    trait _adm_theme_install{
        protected $_themes_allowedtags = [
            'a' =>['href' => [],'title' => [],'target' => [],],'abbr' => ['title' => []],
            'acronym' => ['title' => []],'code' => [],'pre' => [],'em' => [],'strong' => [],
            'div' => [],'p' => [],'ul' => [],'ol' => [],'li' => [],'h1' => [],'h2' => [],
            'h3' => [],'h4' => [],'h5' => [],'h6' => [],'img' =>['src' => [],'class' => [],'alt' => [],],
        ];
        protected $_theme_field_defaults =['description' => true,'sections' => false,'tested' => true,'requires' => true,'rating' => true,
            'downloaded' => true,'download_link' => true,'last_updated' => true,'homepage' => true,'tags' => true,'num_ratings' => true,
        ];
        protected function _getTpListBlock(){
            return $this->tp_list_block;
        }
        protected function _get_install_theme_search_form($type_selector = true ):string{
            $output='';
            $type = isset( $_REQUEST['type'] ) ? $this->_tp_unslash( $_REQUEST['type'] ) : 'term';
            $term =  isset( $_REQUEST['s'] ) ? $this->_tp_unslash( $_REQUEST['s'] ) : '';
            if ( ! $type_selector ) {
                $output .= "<p class='install-help'>{$this->__('Search for themes by keyword.')}</p>";
            }
            $output .= "<form class='search-themes' method='get'><ul>";
            $output .= "<li><input name='tab' type='hidden' value='search'/></li>";
            if ($type_selector ){
                $search_option = "<option value='term' {$this->_get_selected( 'term', $type )} >{$this->__('Keyword')}</option>";
                $search_option .= "<option value='author' {$this->_get_selected( 'author', $type )} >{$this->__('Author')}</option>";
                $search_option .= "<option value='tag' {$this->_get_selected( 'tag', $type )} >{$this->_x('Tag', 'Theme Installer')}</option>";
                $output .= "<li>";
                $output .= "<dt><label class='screen-reader-text' for='type_selector'>{$this->__('Type of search')}</label></dt>";
                $output .= "<dd><select name='type' id='type_selector'>$search_option</select></dd>";
                $output .= "</li><li>";
                $output .= "<dt><label class='screen-reader-text' for='s'>";
                switch ( $type ) {
                    case 'term':
                        $output .= $this->__( 'Search by keyword' );
                        break;
                    case 'author':
                        $output .= $this->__( 'Search by author' );
                        break;
                    case 'tag':
                        $output .= $this->__( 'Search by tag' );
                        break;
                }
                $output .= "</label></dt></li>";
            }else{
                $output .= "<li>";
                $output .= "<dt><label class='screen-reader-text' for='s'>{$this->__('Search by keyword')}</label></dt>";
                $output .= "</li>";
            }
            $output .= "<li>";
            $output .= "<dd><input name='s' id='s' type='search' value='{$this->_esc_attr($term )}' size='30' autofocus /></dd>";
            $output .= "</li><li>";
            $output .= "<dd>{$this->_get_submit_button( $this->__( 'Search' ), '', 'search', false )}</dd>";
            $output .= "</li>";
            $output .= "</ul></form>";
            return $output;
        }//91
        protected function _get_install_themes_dashboard():string{
            $feature_list = $this->_get_theme_feature_list();
            $output= $this->_get_install_theme_search_form( false );
            $output .= "<h4>{$this->__('Feature Filter')}</h4>";
            $output .= "<p class='install-help'>{$this->__('Find a theme based on specific features.')}</p>";
            $output .= "<form method='get'><ul>";
            $output .= "<li><input name='tab' type='hidden' value='search'/></li>";
            $output .= "<li><div class='feature-filter'>";
            foreach ( (array) $feature_list as $feature_name => $features ){
                $feature_name = $this->_esc_html( $feature_name );
                $output .= "<div class='feature-name'>$feature_name</div>";
                $output .= "<ol class='feature-group'>";
                foreach ( $features as $feature => $sub_feature_name ){
                    $feature_name = $this->_esc_html( $sub_feature_name );
                    $feature      = $this->_esc_attr( $feature );
                    $output .= "<li>";
                    $output .= "<dd><input name='features[]' id='feature_id_{$feature}' type='checkbox' value='$feature' size='' /></dd>";
                    $output .= "<dt><label for='feature_id_{$feature}'>{$feature_name}</label></dt>";
                    $output .= "</li>";
                }
                $output .= "</ol>";
            }
            $output .= "</div></li><li>";
            $output .= "<dd>{$this->_get_submit_button( $this->__( 'Find Themes' ), '', 'search')}</dd>";
            $output .= "</li></ul></form>";
            return $output;
        }//136
        protected function _get_install_themes_upload():string{
            $output = "<p class='install-help'>{$this->__('If you have a theme in a .zip format, you may install or update it by uploading it here.')}</p>";
            $output .= "<form method='post' class='tp-upload-form' action='{$this->_self_admin_url( 'update.php?action=upload-theme' )}' enctype='multipart/form-data'><ul>";
            $output .= "<li>{$this->_tp_get_nonce_field( 'theme-upload' )}</li><li>";
            $output .= "<dt><label for='theme_zip' class='screen-reader-text'>{$this->__('Theme zip file')}</label></dt>";
            $output .= "<dd><input name='theme_zip' id='theme_zip' type='file' accept='.zip' /></dd></li><li>";
            $output .= "<dd>{$this->_get_submit_button($this->__('Install Now'),'', 'install-theme-submit', false)}</dd>";
            $output .= "</li></ul></form>";
            return $output;
        }//180 $this->tp_list_table
        protected function _get_display_themes():string{
            if ($this->tp_list_block instanceof TP_Themes_List_Block && ! isset( $this->tp_list_block ) ) {
                $this->tp_list_block = $this->_init_theme_list_block_install();
            }
            ob_start();
            $this->tp_list_block->prepare_items();
            $output  = ob_get_clean();
            $output .= $this->tp_list_block->get_display();
            return $output;
        }//218
        protected function _get_install_theme_information():string{
            $theme = $this->_themes_api('theme_information',['slug' => $this->_tp_unslash( $_REQUEST['theme'])]);
            if($this->_init_error( $theme)){ $this->_tp_die( $theme );}
            $output  = $this->_get_iframe_header( $this->__( 'Theme Installation' ) );
            if ($this->tp_list_block instanceof TP_Theme_Install_List_Block &&  ! isset( $this->tp_list_block ) ) {
                $this->tp_list_block = $this->_init_theme_list_block_install();
            }
            $output .= $this->tp_list_block->get_theme_installer_single( $theme );
            $output .= $this->_get_iframe_footer();
            return $output;
        }
    }
}else{die;}