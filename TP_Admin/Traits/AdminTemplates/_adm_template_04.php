<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-6-2022
 * Time: 09:00
 */
namespace TP_Admin\Traits\AdminTemplates;
use TP_Admin\Libs\Adm_Screen;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\Methods\_methods_21;

if(ABSPATH){
    trait _adm_template_04{
        use _init_locale;
        use _methods_21;
        /**
         * @param string $title
         * @return string
         */
        protected function _get_iframe_header( $title = ''):string{
            $this->_show_admin_bar( false );
            $this->tp_locale = $this->_init_locale();
            $admin_body_class = preg_replace( '/[^a-z0-9_-]+/i', '-', $this->tp_hook_suffix );
            $current_screen = $this->_get_current_screen();
            ob_start();
            header("Content-Type:'{$this->_get_option( 'html_type' )}'; charset='{$this->_get_option( 'blog_charset' )}'");
            $output  = ob_get_clean();
            $output .= $this->_tp_get_admin_html_begin();
            $_title = "{$this->_get_bloginfo( 'name' )}&rsaquo;{$title}&#8212;{$this->__('TailoredPress')}";
            $output .= "<title>$_title</title>";
            ob_start();
            $this->tp_enqueue_style( 'colors' );
            ?>
            <!--suppress JSUnusedLocalSymbols -->
            <script id='iframe_header'>
                const async_url = '<?php echo $this->_esc_js( $this->_admin_url( 'admin_async.php', 'relative' ) ); ?>';
                const pagenow = '<?php echo $this->_esc_js( $current_screen->id ); ?>';
                const typenow = '<?php echo $this->_esc_js( $current_screen->post_type ); ?>';
                const admin_page = '<?php echo $this->_esc_js( $admin_body_class ); ?>';
                const thousandsSeparator = '<?php echo $this->_esc_js( $this->tp_locale->number_format['thousands_sep'] ); ?>';
                const decimalPoint = '<?php echo  $this->_esc_js( $this->tp_locale->number_format['decimal_point'] ); ?>';
                const isRtl = '<?php echo (int) $this->_is_rtl(); ?>';
            </script>
            <?php
            $output .= ob_get_clean();
            $output .= $this->_do_action( 'admin_enqueue_scripts', $this->tp_hook_suffix );
            $output .= $this->_do_action( "admin_print_styles-{$this->tp_hook_suffix}" );
            $output .= $this->_do_action( 'admin_print_styles' );
            $output .= $this->_do_action( 'admin_print_scripts' );
            $output .= $this->_do_action( "admin_head-{$this->tp_hook_suffix}" );
            $output .= $this->_do_action( 'admin_head' );
            $admin_body_class .= ' locale-' . $this->_sanitize_html_class( strtolower( str_replace( '_', '-', $this->_get_user_locale() ) ) );
            if ( $this->_is_rtl() ) { $admin_body_class .= ' rtl';}
            $admin_body_id = isset( $GLOBALS['body_id'] ) ? " id='" . $GLOBALS['body_id'] . "'" : '';
            $admin_body_classes = $this->_apply_filters( 'admin_body_class', '' );
            $admin_body_classes = ltrim( $admin_body_classes . ' ' . $admin_body_class );
            $output .= "</head><body $admin_body_id class='tp-admin no-js iframe $admin_body_classes'>";
            ob_start();
            ?>
            <script id='iframe_body'>(function(){
                    const class_name = document.body.className;
                    document.body.className = class_name.replace(/no-js/, 'js');
            })();</script>
            <?php
            $output .= ob_get_clean();
            return $output;
        }//2019
        protected function _iframe_header( $title = ''):void{
            echo $this->_get_iframe_header( $title);
        }//2019
        protected function _get_iframe_footer():string{
            $output  = "<div class='hidden'>";
            $output .= $this->_do_action( 'admin_footer', $this->tp_hook_suffix );
            $output .= $this->_do_action( "admin_print_footer_scripts_{$this->tp_hook_suffix}" );
            $output .= $this->_do_action( 'admin_print_footer_scripts' );
            $output .= "</div>";
            ob_start();
            ?>
            <script id='iframe_footer'>console.log('post_states', 'todo');//if(typeof tpOnload==='function')tpOnload();</script>
            <?php
            $output .= ob_get_clean();
            $output .= "</body></html>";
            return $output;
        }//2106
        protected function _iframe_footer():void{
            echo $this->_get_iframe_footer();
        }//2106
        /**
         * @description Retrieves an array of post states from a post.
         * @param $post
         * @return string
         */
        protected function _post_states( $post ):string{
            $post_states = [];
            $post_status = $_REQUEST['post_status'] ?? '';
            if ( ! empty( $post->post_password ) ) {
                $post_states['protected'] = $this->_x( 'Password protected', 'post status' );
            }
            if ( 'private' === $post->post_status && 'private' !== $post_status ) {
                $post_states['private'] = $this->_x( 'Private', 'post status' );
            }
            if ( 'draft' === $post->post_status ) {
                if ( $this->_get_post_meta( $post->ID, '_customize_changeset_uuid', true ) ) {
                    $post_states[] = $this->__( 'Customization Draft' );
                } elseif ( 'draft' !== $post_status ) {
                    $post_states['draft'] = $this->_x( 'Draft', 'post status' );
                }
            } elseif ( 'trash' === $post->post_status && $this->_get_post_meta( $post->ID, '_customize_changeset_uuid', true ) ) {
                $post_states[] = $this->_x( 'Customization Draft', 'post status' );
            }
            if ( 'pending' === $post->post_status && 'pending' !== $post_status ) {
                $post_states['pending'] = $this->_x( 'Pending', 'post status' );
            }
            if ( $this->_is_sticky( $post->ID ) ) {
                $post_states['sticky'] = $this->_x( 'Sticky', 'post status' );
            }
            if ( 'future' === $post->post_status ) {
                $post_states['scheduled'] = $this->_x( 'Scheduled', 'post status' );
            }
            if ( 'page' === $this->_get_option( 'show_on_front' ) ) {
                if ( (int) $this->_get_option( 'page_on_front' ) === $post->ID ) {
                    $post_states['page_on_front'] = $this->_x( 'Front Page', 'page label' );
                }
                if ( (int) $this->_get_option( 'page_for_posts' ) === $post->ID ) {
                    $post_states['page_for_posts'] = $this->_x( 'Posts Page', 'page label' );
                }
            }
            if ( (int) $this->_get_option( 'tp_page_for_privacy_policy' ) === $post->ID ) {
                $post_states['page_for_privacy_policy'] = $this->_x( 'Privacy Policy Page', 'page label' );
            }
            /**
             * @param TP_Post  $post        The current post object.
             */
            return $this->_apply_filters( 'display_post_states', $post_states, $post );
        }//2177
        /**
         * @description Outputs the attachment media states as HTML.
         * @param $post
         * @return string
         */
        protected function _get_printed_media_states( $post):string{
            $media_states        = $this->_get_media_states( $post );
            $media_states_string = '';
            if ( ! empty( $media_states ) ) {
                $state_count = count( $media_states );
                $i = 0;
                $media_states_string .= ' &mdash; ';
                foreach ((array) $media_states as $state ) {
                    ++$i;
                    $sep = ( $i < $state_count ) ? ', ' : '';
                    $media_states_string .= "<span class='post-state'>$state$sep</span>";
                }
            }
            return $media_states_string;
        }//2255
        protected function _print_media_states( $post):void{}//2255
        /**
         * @description Retrieves an array of media states from an attachment.
         * @param $post
         * @return array
         */
        protected function _get_media_states( $post ):array{
            static $header_images;
            $media_states = array();
            $stylesheet   = $this->_get_option( 'stylesheet' );
            if ( $this->_current_theme_supports( 'custom-header' ) ) {
                $meta_header = $this->_get_post_meta( $post->ID, '_tp_attachment_is_custom_header', true );
                if ( $this->_is_random_header_image() ) {
                    if ( ! isset( $header_images ) ) {
                        $header_images = $this->_tp_list_pluck( $this->_get_uploaded_header_images(), 'attachment_id' );
                    }
                    if ( $meta_header === $stylesheet && in_array( $post->ID, $header_images, true ) ) {
                        $media_states[] = $this->__( 'Header Image' );
                    }
                } else {
                    $header_image = $this->_get_header_image();
                    if ( ! empty( $meta_header ) && $meta_header === $stylesheet && $this->_tp_get_attachment_url( $post->ID ) !== $header_image ) {
                        $media_states[] = $this->__( 'Header Image' );
                    }
                    if ( $header_image && $this->_tp_get_attachment_url( $post->ID ) === $header_image ) {
                        $media_states[] = $this->__( 'Current Header Image' );
                    }
                }
                if ( $this->_get_theme_support( 'custom-header', 'video' ) && $this->_has_header_video() ) {
                    $mods = $this->_get_theme_mods();
                    if ( isset( $mods['header_video'] ) && $post->ID === $mods['header_video'] ) {
                        $media_states[] = $this->__( 'Current Header Video' );
                    }
                }
            }
            if ( $this->_current_theme_supports( 'custom-background' ) ) {
                $meta_background = $this->_get_post_meta( $post->ID, '_tp_attachment_is_custom_background', true );
                if ( ! empty( $meta_background ) && $meta_background === $stylesheet ) {
                    $media_states[] = $this->__( 'Background Image' );
                    $background_image = $this->_get_background_image();
                    if ( $background_image && $this->_tp_get_attachment_url( $post->ID ) === $background_image ) {
                        $media_states[] = $this->__( 'Current Background Image' );
                    }
                }
            }
            if ( (int)$this->_get_option( 'site_icon' ) === $post->ID ) {
                $media_states[] = $this->__( 'Site Icon' );
            }
            if ( (int) $this->_get_theme_mod( 'custom_logo' ) === $post->ID ) {
                $media_states[] = $this->__( 'Logo' );
            }
            /**
             * @param TP_Post  $post         The current attachment object.
             */
            return $this->_apply_filters( 'display_media_states', $media_states, $post );
        }//2290
        /**
         * @description Test support for compressing JavaScript from PHP
         * @return string
         */
        protected function _get_compression_test():string{
            ob_start();
            ?>
            <script id='compression_test'>
                const compressionNonce = "<?php echo $this->_tp_json_encode( $this->_tp_create_nonce( 'update_can_compress_scripts' ) ); ?>";
                let  r, h;
                const testCompression ={
                    get:test =>{
                        const x = new XMLHttpRequest();
                        if (x) {
                            x.onreadystatechange = ()=>{

                                if ( x.readyState == 4 ) {
                                    r = x.responseText.substr(0, 18);
                                    h = x.getResponseHeader('Content-Encoding');
                                    testCompression.check(r, h, test);
                                }
                            };
                            x.open('GET', async_url + '?action=tp_compression_test&test='+test+'&_async_nonce='+compressionNonce+'&'+(new Date()).getTime(), true);
                            x.send('');
                        }
                    },
                    check : (r, h, test)=>{
                        if ( ! r && ! test ) this.get(1);
                        if ( 1 == test ) {
                            if ( h && ( h.match(/deflate/i) || h.match(/gzip/i) ) ) this.get('no');
                            else this.get(2);
                            return;
                        }
                        if ( 2 == test ) {
                            if ( '"tpCompressionTest' === r ) this.get('yes');
                            else this.get('no');
                        }
                    }
                };
                console.log('test_compression:',testCompression.check);
                testCompression.check();
            </script>
            <?php
            return ob_get_clean();
        }//2373
        protected function _compression_test():void{
            echo $this->_get_compression_test();
        }//2373
        /**
         * @param string $text
         * @param string|array $type
         * @param string $name
         * @param bool $wrap
         * @param null|array $other_attributes
         * @return string
         */
        protected function _get_submit_button( $text = '', $type = 'primary large', $name = 'submit', $wrap = true, $other_attributes = null ):string{
            if ( ! is_array( $type ) ) {
                $type = explode( ' ', $type );
            }
            $button_shorthand = ['primary','small','large'];
            $classes = ['button'];
            foreach ( $type as $t ) {
                if ( 'secondary' === $t || 'button-secondary' === $t ) {
                    continue;
                }
                $classes[] = in_array( $t, $button_shorthand, true ) ? 'button-' . $t : $t;
            }
            $class = implode( ' ', array_unique( array_filter( $classes ) ) );
            $text = $text ?: $this->__( 'Save Changes' );
            // Default the id attribute to $name unless an id was specifically provided in $other_attributes.
            $id = $name;
            if ( is_array( $other_attributes ) && isset( $other_attributes['id'] ) ) {
                $id = $other_attributes['id'];
                unset( $other_attributes['id'] );
            }
            $attributes = '';
            if ( is_array( $other_attributes ) ) {
                foreach ( $other_attributes as $attribute => $value ) {
                    $attributes .= "$attribute= {$this->_esc_attr( $value )}";
                }
            } elseif ( ! empty( $other_attributes ) ) { // Attributes provided as a string.
                $attributes = $other_attributes;
            }
            $name_attr = $name ? " name='{$this->_esc_attr($name)}'" : '';
            $id_attr = $id ? " id='{$this->_esc_attr($id)}'" : '';
            $output  = "<input type='submit' $name_attr $id_attr class='{$this->_esc_attr($class)}' value='{$this->_esc_attr($text)}' $attributes/>";
            if ( $wrap ) {
                $output = "<dd class='submit'>$output</dd>";
            }
            return $output;
        }//2474
        protected function _submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ):void{
            echo $this->_get_submit_button( $text, $type, $name, $wrap, $other_attributes);
        }//2449
        protected function _tp_get_admin_html_begin():string{
            $admin_html_class = ( $this->_is_admin_bar_showing() ) ? 'tp-toolbar' : '';
            $_adm_xml_ns = $this->add_asset( 'admin_xml_ns', '' );//todo: method will follow!
            $output_begin  = "<!DOCTYPE html><html class='$admin_html_class' {$this->_get_language_attributes()} $_adm_xml_ns>";
            $output_begin .= "<head>";
            if($this->_bloginfo( 'html_type' ) !== null && $this->_get_option( 'blog_charset' ) !== null){
                $output_begin .= "<meta http-equiv='Content-Type' content='{$this->_bloginfo( 'html_type' )}' charset='{$this->_get_option( 'blog_charset' )}' />";
            }
            return $output_begin;
        }//2528
        protected function _tp_admin_html_begin():void{
            echo $this->_tp_get_admin_html_begin();
        }//2528
        /**
         * @description Convert a screen string to a screen object
         * @param $hook_name
         * @return Adm_Screen
         */
        protected function _convert_to_screen( $hook_name ):Adm_Screen{
            return Adm_Screen::get_screen( $hook_name );
        }//2564
    }
}else die;