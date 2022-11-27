<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-4-2022
 * Time: 11:32
 */
namespace TP_Admin\Traits\AdminRewrite;
use TP_Admin\Traits\AdminFile\_adm_file_01;
use TP_Core\Traits\Constructs\_construct_assets;
use TP_Core\Traits\Constructs\_construct_browsers;
use TP_Core\Traits\Constructs\_construct_core;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Templates\_link_template_10;

if(ABSPATH){
    trait _adm_rewrite_01{
        use _adm_file_01;
        use _construct_assets;
        use _construct_browsers;
        use _construct_core;
        use _formats_10;
        use _init_rewrite;
        use _link_template_10;
        use _methods_03;
        use _methods_06;
        use _methods_12,_methods_13;
        use _option_01;
        /**
         * @description Returns whether the server is running Apache with the mod_rewrite module loaded.
         * @return mixed
         */
        protected function _get_mod_rewrite(){
            $got_rewrite = $this->_apache_mod_loaded( 'mod_rewrite', true );
            return $this->_apply_filters( 'got_rewrite', $got_rewrite );
        }//16 adm/misc
        /**
         * @description Returns whether the server supports URL rewriting.
         * @return mixed
         */
        protected function _get_url_rewrite(){
            $got_url_rewrite = ( $this->_get_mod_rewrite() || $this->tp_is_nginx || $this->_iis7_supports_permalinks() );
            return $this->_apply_filters( 'got_url_rewrite', $got_url_rewrite );
        }//45 adm/misc
        /**
         * @description Extracts strings from between the BEGIN and END markers in the .htaccess file.
         * @param $filename
         * @param $marker
         * @return array
         */
        protected function _extract_from_markers( $filename, $marker ):array{
            $result = [];
            if(!file_exists( $filename)){return $result;}
            $marker_data = explode( "\n", implode( '', file( $filename ) ) );
            $state = false;
            foreach ( $marker_data as $marker_line ) {
                if(false !== strpos($marker_line,'# END '.$marker)){ $state = false;}
                if ( $state ) {
                    if (strpos($marker_line, '#') === 0) { continue;}
                    $result[] = $marker_line;
                }
                if ( false !== strpos($marker_line,'# BEGIN ' . $marker)){$state = true;}
            }
            return $result;
        }//67 adm/misc
        /**
         * @description Inserts an array of strings into a file (.htaccess),
         * @description . placing it between BEGIN and END markers.
         * @param $filename
         * @param $marker
         * @param $insertion
         * @return bool
         */
        protected function _insert_with_markers( $filename, $marker, $insertion ):bool{
            if ( ! file_exists( $filename ) ) {
                if ( ! is_writable( dirname( $filename ))){return false;}
                if ( ! touch( $filename ) ) { return false;}
                $perms = fileperms( $filename );
                if ( $perms ) {chmod( $filename, $perms | 0644 );}
            } elseif ( ! is_writable( $filename )){return false;}
            if ( ! is_array( $insertion ) ) { $insertion = explode( "\n", $insertion );}
            $switched_locale = $this->_switch_to_locale( $this->_get_locale() );
            $instructions = sprintf( $this->__('The directives (lines) between "BEGIN %1$s" and "END %1$s" are dynamically generated, and should only be modified via TailoredPress filters. Any changes to the directives between these markers will be overwritten.'
            ),$marker);/* translators: 1: Marker. */
            $instructions = explode( "\n", $instructions );
            foreach ($instructions as $line => $text){ $instructions[ $line ] = '# ' . $text;}
            $instructions = $this->_apply_filters( 'insert_with_markers_inline_instructions', $instructions, $marker );
            if($switched_locale){ $this->_restore_previous_locale();}
            $insertion = array_merge( $instructions, $insertion );
            $start_marker = "# BEGIN {$marker}";
            $end_marker   = "# END {$marker}";
            $fp = fopen( $filename, 'rb+' );
            if ( ! $fp ) { return false;}
            flock( $fp, LOCK_EX );
            $lines = [];
            while ( ! feof( $fp ) ) {$lines[] = rtrim( fgets( $fp ), "\r\n" );}
            $pre_lines        = [];
            $post_lines       = [];
            $existing_lines   = [];
            $found_marker     = false;
            $found_end_marker = false;
            foreach ( $lines as $line ) {
                if (! $found_marker && false !== strpos( $line, $start_marker )) {
                    $found_marker = true;
                    continue;
                }
                if (! $found_end_marker && false !== strpos( $line, $end_marker )) {
                    $found_end_marker = true;
                    continue;
                }
                if ( ! $found_marker ) {$pre_lines[] = $line;}
                elseif ( $found_marker && $found_end_marker ){$post_lines[] = $line;}
                else {$existing_lines[] = $line;}
            }
            if ( $existing_lines === $insertion ) {
                flock( $fp, LOCK_UN );
                fclose( $fp );
                return true;
            }
            // Generate the new file data.
            $new_file_data = implode("\n",array_merge($pre_lines,[$start_marker],$insertion,[$end_marker],$post_lines));
            fseek( $fp, 0 );
            $bytes = fwrite( $fp, $new_file_data );
            if ( $bytes ) { ftruncate( $fp, ftell( $fp ) );}
            fflush( $fp );
            flock( $fp, LOCK_UN );
            fclose( $fp );
            return (bool) $bytes;
        }//109 adm/misc
        /**
         * @description Updates the htaccess file with the current rules if it is writable.
         * @return bool|void
         */
        protected function _save_mod_rewrite_rules(){
            if($this->_is_multisite()){return;}
            $this->tp_rewrite = $this->_init_rewrite();
            $home_path     = $this->_get_home_path();
            $htaccess_file = $home_path . '.htaccess';
            if ((is_writable($htaccess_file) || (!file_exists($htaccess_file) && $this->_get_mod_rewrite() && is_writable($home_path) && $this->tp_rewrite->using_mod_rewrite_permalinks()))) {
                $rules = explode( "\n", $this->tp_rewrite->mod_rewrite_rules() );
                return $this->_insert_with_markers( $htaccess_file, 'TailoredPress', $rules );
            }
            return false;
        }//249 adm/misc
        /**
         * @description Updates the IIS web.config file with the current rules if it is writable.
         * @return bool|void
         */
        protected function _iis7_save_url_rewrite_rules(){
            if($this->_is_multisite()){return;}
            $this->tp_rewrite = $this->_init_rewrite();
            $home_path     = $this->_get_home_path();
            $web_config_file = $home_path . 'web.config';
            if (  $this->_iis7_supports_permalinks() && ( ( ! file_exists( $web_config_file ) &&  $this->_win_is_writable( $home_path ) && $this->tp_rewrite->using_mod_rewrite_permalinks() ) ||  $this->_win_is_writable( $web_config_file ) ) ) {
                $rule = $this->tp_rewrite->iis7_url_rewrite_rules( false );
                if ( ! empty( $rule ) ) { return  $this->_iis7_add_rewrite_rule( $web_config_file, $rule );}
                return  $this->_iis7_delete_rewrite_rule( $web_config_file );
            }
            return false;
        }//286 adm/misc
        /**
         * @description Update the "recently-edited" file for the theme file editor.
         * @param $file
         */
        protected function _update_recently_edited( $file ):void{
            $old_files = (array) $this->_get_option( 'recently_edited' );
            if ( $old_files ) {
                $old_files   = array_reverse( $old_files );
                $old_files[] = $file;
                $old_files   = array_reverse( $old_files );
                $old_files   = array_unique( $old_files );
                if ( 5 < count( $old_files )){array_pop( $old_files );}
            } else {$old_files[] = $file;}
            $this->_update_option( 'recently_edited', $old_files );
        }//318 adm/misc
        /**
         * @description Makes a tree structure for the theme file editor's file list.
         * @param $allowed_files
         * @return array
         */
        protected function _tp_make_theme_file_tree( $allowed_files ):array{
            $tree_list = [];
            foreach ( $allowed_files as $file_name => $absolute_filename ) {
                $list     = explode( '/', $file_name );
                $last_dir = &$tree_list;
                foreach ( $list as $dir ) {$last_dir =& $last_dir[ $dir ];}
                $last_dir = $file_name;
            }
            return $tree_list;
        }//343 adm/misc
        /**
         * @description Outputs the formatted file list for the theme file editor.
         * @param $tree
         * @param int $level
         * @param int $size
         * @param int $index
         */
        protected function _tp_print_theme_file_tree( $tree, $level = 2, $size = 1, $index = 1 ):void{
            if ( is_array( $tree ) ) {
                $index = 0;
                $size  = count( $tree );
                foreach ( $tree as $label => $theme_file ){
                    $index++;
                    if ( ! is_array( $theme_file ) ) {
                        $this->_tp_print_theme_file_tree( $theme_file, $level, $index, $size );
                        continue;
                    }
                    $file_tree = static function()use($theme_file,$level,$size,$index,$label){
                        $html = "<li role='treeitem' aria-expanded='true' tabindex='-1'";
                        $html .= " aria-level='{(new self)->_esc_attr( $level )}'";
                        $html .= " aria-setsize ='{(new self)->_esc_attr( $size )}'";
                        $html .= " aria-posinset='{(new self)->_esc_attr( $index )}'>";
                        $html .= "<span class='fold-label'>{(new self)->_esc_html( $label )}";
                        $html .= "<span class='screen-reader-text'>{(new self)->__('folder')}</span>";
                        $html .= "<span aria-hidden='true' class='icon'></span></span>";
                        $html .= "<ul role='group' class='tree-folder'>{(new self)->_tp_print_theme_file_tree( $theme_file, $level + 1, $index, $size )}</ul>";
                        $html .= "</li>";
                        return $html;
                    };
                    echo (string)$file_tree;
                }
            }else{
                $filename = $tree;
                $url      = $this->_add_query_arg(['file' => rawurlencode( $tree ),
                    'theme' => rawurlencode( $this->tp_stylesheet ),],
                    $this->_self_admin_url( 'theme_editor.php' )
                );
                $relative_file = $this->tp_relative_file;
                $filename_tree = static function()use($filename,$relative_file,$url,$level,$size,$index){
                    $class_file = (new self)->_esc_attr( $relative_file === $filename ? ' current-file' : '' );
                    $tab_index = (new self)->_esc_attr( $relative_file === $filename ? '0' : '-1' );
                    $file_description = (new self)->_esc_html( (new self)->_get_file_description( $filename ) );
                    if ( $file_description !== $filename && (new self)->_tp_basename( $filename ) !== $file_description ) {
                        $file_description .="<br /><span class='non-essential'>({(new self)->_esc_html( $filename )})</span>";
                    }
                    $html = "<li class='{$class_file}'>";
                    $html .= "<a role='treeitem' tabindex='{$tab_index}' href='{(new self)->_esc_url( $url )}' ";
                    $html .= " aria-level='{(new self)->_esc_attr( $level )}'";
                    $html .= " aria-setsize ='{(new self)->_esc_attr( $size )}'";
                    $html .= " aria-posinset='{(new self)->_esc_attr( $index )}'>";
                    if ( $relative_file === $filename ) {
                        $html .= "<span class='notice notice-info'>{$file_description}</span>";
                    }else{$html .= $file_description;}
                    $html .= "</a></li>";
                    return $html;
                };
                echo (string)$filename_tree;
            }
        }//371 adm/misc
        //not using @description Makes a tree structure for the plugin file editor's file list.
        //protected function _tp_make_plugin_file_tree( $plugin_editable_files ){return '';}//436 adm/misc
    }
}else die;