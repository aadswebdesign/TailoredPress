<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\Inits\_init_custom;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _theme_09 {
        use _init_custom,_init_db;
        /**
         * @description Prints a script to check whether or not the Customizer is supported,
         * @description . and apply either the no-customize-support or customize-support class to the body.
         */
        protected function _tp_get_customize_support_script():string{
            $admin_origin = parse_url( $this->_admin_url() );
            $home_origin  = parse_url( $this->_home_url() );
            $cross_domain = ( strtolower( $admin_origin['host'] ) !== strtolower( $home_origin['host'] ) );
            $type_script = static function()use($cross_domain){
                ob_start();
                ?>
                <!--suppress JSUnusedAssignment -->
                <script id='customize_support_script'>
                    (function(){
                        let request, b = document.body, c = 'className', cs = 'customize-support', rcs = new RegExp('(^|\\s+)(no-)?'+cs+'(\\s+|$)');
                        <?php if ( $cross_domain ){ ?>
                        request = (function(){ var xhr = new XMLHttpRequest(); return ('withCredentials' in xhr); })();
                        <?php }else{?>
                        request = true;
                        <?php } ?>
                            b[c] = b[c].replace( rcs, ' ' );
                            // The customizer requires postMessage and CORS (if the site is cross domain).
                            //noinspection JSUnresolvedVariable
                            b[c] += ( window.postMessage && request ? ' ' : ' no-' ) + cs;
                    }());
                </script>
                <?php
                return ob_get_clean();
            };
            return (string)$type_script();
        }//3654
        protected function _tp_customize_support_script():void{
            echo $this->_tp_get_customize_support_script();
        }
        /**
         * @description Whether the site is being previewed in the Customizer.
         * @return bool
         */
        protected function _is_customize_preview():bool{
            return $this->_init_customize_manager()->is_preview();
        }//3687
        /**
         * @description Makes sure that auto-draft posts get their post_date bumped or status
         * @description . changed to draft to prevent premature garbage-collection.
         * @param $new_status
         * @param $old_status
         * @param $post
         */
        protected function _tp_keep_alive_customize_changeset_dependent_auto_drafts( $new_status, $old_status, $post ):void{
            $this->tpdb = $this->_init_db();
            if ($old_status === null && ('customize_changeset' !== $post->post_type || 'publish' === $new_status) ) {
                return;
            }
            $data = json_decode( $post->post_content, true );
            if ( empty( $data['nav_menus_created_posts']['value'] ) ) {
                return;
            }
            if ( 'trash' === $new_status ) {
                foreach ( $data['nav_menus_created_posts']['value'] as $post_id ) {
                    if ( ! empty( $post_id ) && 'draft' === $this->_get_post_status( $post_id ) ) {
                        $this->_tp_trash_post( $post_id );
                    }
                }
                return;
            }
            $post_args = [];
            if ( 'auto-draft' === $new_status ) {$post_args['post_date'] = $post->post_date;// Note tp_delete_auto_drafts() only looks at this date.
            }else{$post_args['post_status'] = 'draft';}
            foreach ( $data['nav_menus_created_posts']['value'] as $post_id ) {
                if ( empty( $post_id ) || 'auto-draft' !== $this->_get_post_status( $post_id ) ) {
                    continue;
                }
                $this->tpdb->update($this->tpdb->posts, $post_args,['ID' => $post_id]);
                $this->_clean_post_cache( $post_id );
            }
        }//3722
        /**
         * @description Creates the initial theme features when the 'setup_theme' action is fired.
         */
        protected function _create_initial_theme_features():void{
            $this->_register_theme_feature('align-wide',[]);
            $this->_register_theme_feature('automatic-feed-links',[]);
            $this->_register_theme_feature('custom-background',[]);
            $this->_register_theme_feature('custom-header',[]);
            $this->_register_theme_feature('custom-logo',[]);
            $this->_register_theme_feature('customize-selective-refresh-widgets',[]);
            $this->_register_theme_feature('dark-editor-style',[]);
            $this->_register_theme_feature('disable-custom-colors',[]);
            $this->_register_theme_feature('disable-custom-font-sizes',[]);
            $this->_register_theme_feature('disable-custom-gradients',[]);
            $this->_register_theme_feature('editor-color-palette',[]);
            $this->_register_theme_feature('editor-font-sizes',[]);
            $this->_register_theme_feature('editor-gradient-presets',[]);
            $this->_register_theme_feature('editor-styles',[]);
            //html5 skipped
            $this->_register_theme_feature('post-formats',[]);
            $this->_register_theme_feature('post-thumbnails',[]);
            $this->_register_theme_feature('responsive-embeds',[]);
            $this->_register_theme_feature('title-tag',[]);
            $this->_register_theme_feature('tp-block-styles',[]);
            //$this->_register_theme_feature('',[]);
        }//3793 //todo data
        /**
         * @description Returns whether the current theme is a block-based theme or not.
         * @return bool
         */
        protected function _tp_is_block_theme():bool{
            $_get_theme = $this->_tp_get_theme();
            $get_theme = null;
            if($_get_theme instanceof TP_Theme ){
                $get_theme = $_get_theme;
            }
            return $get_theme->is_block_theme();
        }//4164
        /**
         * @description Adds default theme supports for block themes when the 'setup_theme' action fires.
         */
        protected function _add_default_theme_supports():void{
            if(!$this->_tp_is_block_theme()){return;}
            $this->_add_theme_support( 'post-thumbnails' );
            $this->_add_theme_support( 'responsive-embeds' );
            $this->_add_theme_support( 'editor-styles' );
            $this->_add_theme_support( 'automatic-feed-links' );
            $this->_add_filter( 'should_load_separate_core_block_assets', '__return_true' );
        }//4176
    }
}else die;