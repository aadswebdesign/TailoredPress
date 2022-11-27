<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 12:21
 */
namespace TP_Admin\Traits\AdminTheme;
use TP_Admin\Traits\AdminInits\_adm_init_files;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\Theme\_theme_01;

if(ABSPATH){
    trait _adm_theme_01{
        use _theme_01;
        use _adm_init_files;
        /**
         * @description Remove a theme
         * @param $stylesheet
         * @param string $redirect
         * @return string
         */
        protected function _get_delete_theme( $stylesheet, $redirect = '' ):string{
            $this->adm_header_args = [
                'parent_file' => 'testpanel.php',
                //'get_admin_index_head' => [$this,'get_options_index_stuff'],
                //'index_title' => 'TailoredPress',
            ];
            $adm_header = $this->get_adm_component_class('Adm_Header',$this->adm_header_args);
            $adm_footer =  $this->get_adm_component_class('Adm_Footer');
            $output  = "";
            //if ( empty( $stylesheet ) ) { return false;}
            if ( empty( $redirect ) ) {
                $redirect = $this->_tp_nonce_url( 'themes.php?action=delete&stylesheet=' . urlencode( $stylesheet ), 'delete-theme_' . $stylesheet );
            }
            $data = $credentials = $this->_get_request_filesystem_credentials( $redirect );
            if ((false === $credentials) && !empty($data)) {
                $output .= $adm_header;
                $output .= $data;
                $output .= $adm_footer;
            }
            if ( ! $this->_tp_get_filesystem( $credentials ) ) {
                $data = $this->_get_request_filesystem_credentials( $redirect, '', true );
                if (!empty($data) && !$this->_tp_get_filesystem($credentials)) {
                    $output .= $adm_header;
                    $output .= $data;
                    $output .= $adm_footer;
                }
            }
            if ( ! is_object( $this->tp_file_system ) ) {
                ob_start();
                new TP_Error( 'fs_unavailable', $this->__( 'Could not access filesystem.' ) );
                $output .= ob_get_clean();
            }
            if (null !== $this->tp_file_system->errors && $this->_init_error( $this->tp_file_system->errors ) && $this->tp_file_system->errors->has_errors() ) {
                ob_start();
                new TP_Error( 'fs_error', $this->__( 'Filesystem error.' ), $this->tp_file_system->errors );
                $output .= ob_get_clean();
            }
            $themes_dir = $this->_init_files()->tp_themes_dir();
            if ( empty( $themes_dir ) ) {
                ob_start();
                new TP_Error( 'fs_no_themes_dir', $this->__( 'Unable to locate TailoredPress theme directory.' ) );
                $output .= ob_get_clean();
            }
            $output .= $this->_get_action( 'delete_theme', $stylesheet );
            $themes_dir = $this->_trailingslashit( $themes_dir );
            $theme_dir  = $this->_trailingslashit( $themes_dir . $stylesheet );
            $deleted    = $this->_init_files()->delete( $theme_dir, true );
            $output .=  $this->_get_action( 'delete_theme', $stylesheet,$deleted );
            if ( ! $deleted ) {//todo needs testing later
                ob_start();
                new TP_Error('could_not_remove_theme',sprintf( $this->__( 'Could not fully remove the theme %s.' ), $stylesheet ));
                $output .= ob_get_clean();
            }
            $theme_translations = $this->_tp_get_installed_translations( 'themes' );
            if ( ! empty( $theme_translations[ $stylesheet ] ) ) {
                $translations = $theme_translations[ $stylesheet ];
                foreach ( $translations as $translation => $data ) {
                    $this->_init_files()->delete( TP_THEMES_LANG . $stylesheet . '-' . $translation . '.po' );
                    $this->_init_files()->delete( TP_THEMES_LANG . $stylesheet . '-' . $translation . '.mo' );
                    $json_translation_files = glob( TP_THEMES_LANG . $stylesheet . '-' . $translation . '-*.json' );
                    if ( $json_translation_files ) {
                        array_map( array( $this->_init_files(), 'delete' ), $json_translation_files );
                    }
                }
            }
            if ( $this->_is_multisite() ) {
                TP_Theme::network_disable_theme( $stylesheet );
            }
            $output .= "";
            return $output;
        }//21 todo testing
        /**
         * @description Gets the page templates available in this theme.
         * @param null $post
         * @param string $post_type
         * @return string
         */
        protected function _get_page_templates( $post = null, $post_type = 'page' ):string{
            return array_flip( $this->_tp_get_theme()->get_page_templates( $post, $post_type ) );
        }//144
        /**
         * @description Tidies a filename for url display by the theme file editor.
         * @param $fullpath
         * @param $containingfolder
         * @return string
         */
        protected function _get_template_edit_filename( $fullpath, $containingfolder ):string{
            return str_replace( dirname($containingfolder, 2), '', $fullpath );
        }//158
        /**
         * @description Retrieve the update link if there is a theme update available.
         * @param $theme
         * @return string
         */
        protected function _get_theme_update_available( $theme ):string{
            static $themes_update = null;
            if ( ! $this->_current_user_can( 'update_themes' ) ) {  return false;}
            if ( ! isset( $themes_update ) ) {
                $themes_update = $this->_get_site_transient( 'update_themes' );
            }
            if (!($theme instanceof TP_Theme )){ return false;}
            $stylesheet = $theme->get_stylesheet();
            $html = '';
            if ( isset( $themes_update->response[ $stylesheet ] ) ) {
                $update      = $themes_update->response[ $stylesheet ];
                $theme_name  = $theme->display( 'Name' );
                $details_url = $this->_add_query_arg(['TB_iframe' => 'true','width' => 1024,'height' => 800,], $update['url']);
                $update_url  = $this->_tp_nonce_url( $this->_admin_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $stylesheet ) ), 'upgrade-theme_' . $stylesheet );
                if ( ! $this->_is_multisite() ) {
                    if ( ! $this->_current_user_can( 'update_themes' ) ) {
                        $html = sprintf("<p><strong>{$this->__( "There is a new version of %1\$s available. <a href='%2\$s' %3\$s>View version %4\$s details</a>" )}</strong></p>",
                            $theme_name,$this->_esc_url( $details_url ),sprintf(" class='thickbox open-plugin-details-modal' aria-label='%s'",
                                $this->_esc_attr( sprintf( $this->__( 'View %1$s version %2$s details' ), $theme_name, $update['new_version'] ) )));
                    } elseif ( empty( $update['package'] ) ) {
                        $html = sprintf(
                        /* translators: 1: Theme name, 2: Theme details URL, 3: Additional link attributes, 4: Version number. */
                            '<p><strong>' . $this->__( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>. <em>Automatic update is unavailable for this theme.</em>' ) . '</strong></p>',
                            $theme_name,
                            $this->_esc_url( $details_url ),
                            sprintf(
                                'class="thickbox open-plugin-details-modal" aria-label="%s"',
                                /* translators: 1: Theme name, 2: Version number. */
                                $this->_esc_attr( sprintf( $this->__( 'View %1$s version %2$s details' ), $theme_name, $update['new_version'] ) )
                            ),
                            $update['new_version']
                        );
                    } else {
                        $html = sprintf(
                        /* translators: 1: Theme name, 2: Theme details URL, 3: Additional link attributes, 4: Version number, 5: Update URL, 6: Additional link attributes. */
                            '<p><strong>' . $this->__( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ) . '</strong></p>',
                            $theme_name,
                            $this->_esc_url( $details_url ),
                            sprintf(
                                'class="thickbox open-plugin-details-modal" aria-label="%s"',
                                /* translators: 1: Theme name, 2: Version number. */
                                $this->_esc_attr( sprintf( $this->__( 'View %1$s version %2$s details' ), $theme_name, $update['new_version'] ) )
                            ),
                            $update['new_version'],
                            $update_url,
                            sprintf(
                                'aria-label="%s" id="update-theme" data-slug="%s"',
                                /* translators: %s: Theme name. */
                                $this->_esc_attr( sprintf( $this->_x( 'Update %s now', 'theme' ), $theme_name ) ),
                                $stylesheet
                            )
                        );
                    }
                }
            }

            return $html;
        }//187
        //@description Retrieve list of TailoredPress theme features (aka theme tags).
        protected function _get_theme_feature_list( $api = true ):string{return '';}//306
        //@description Retrieves theme installer pages from the WordPress.org Themes API.
        protected function _themes_api( $action, $args = array() ):string{return '';}//486
        //@description Prepare themes for JavaScript.
        protected function _tp_prepare_themes_for_js( $themes = null ):array{return '';}//643
        //@description Print JS templates for the theme-browsing UI in the Customizer.
        protected function _customize_themes_print_templates():string{return '';}//813
        //@description Determines whether a theme is technically active but was paused while* loading.
        protected function _is_theme_paused( $theme ):string{return '';}//1086
    }
}else die;