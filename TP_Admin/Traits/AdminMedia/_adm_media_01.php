<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-6-2022
 * Time: 04:22
 */
namespace TP_Admin\Traits\AdminMedia;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_media_01{
        use _init_db;
        /**
         * @description Defines the default media upload tabs
         * @return mixed
         */
        protected function _media_upload_tabs(){
            $_default_tabs = ['type' => $this->__( 'From Computer' ),'type_url' => $this->__( 'From URL' ),
                'gallery' => $this->__( 'Gallery' ), 'library' => $this->__( 'Media Library' ),];
            return $this->_apply_filters( 'media_upload_tabs', $_default_tabs );// Handler action suffix => tab text.
        }//16
        /**
         * @description Adds the gallery tab back to the tabs array if post has image attachments
         * @param $tabs
         * @return mixed
         */
        protected function _update_gallery_tab( $tabs ){
            $this->tpdb = $this->_init_db();
            if ( ! isset( $_REQUEST['post_id'] ) ) {
                unset( $tabs['gallery'] );
                return $tabs;
            }
            $post_id = (int) $_REQUEST['post_id'];
            if ( $post_id ) {
                $attachments = (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT ." count(*) FROM $this->tpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_parent = %d", $post_id ) );
            }
            if ( empty( $attachments ) ) {
                unset( $tabs['gallery'] );
                return $tabs;
            }
            $tabs['gallery'] = sprintf( $this->__( 'Gallery (%s)' ), "<span id='attachments-count'>$attachments</span>" );
            return $tabs;
        }//44
        /**
         * @description Outputs the legacy media upload tabs UI.
         * @return string
         */
        protected function _get_media_upload_tabs():string{
            $tabs    = $this->_media_upload_tabs();
            $default = 'type';
            if ( isset( $this->tp_redirect_tab ) && array_key_exists( $this->tp_redirect_tab, $tabs ) ) {
                $current = $this->tp_redirect_tab;
            } elseif ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ) {
                $current = $_GET['tab'];
            } else {
                $current = $this->_apply_filters( 'media_upload_default_tab', $default );
            }
            $output  = "";
            if ( ! empty( $tabs ) ) {
                $output .= "<ul class=''>\n";
                foreach ( $tabs as $callback => $text ) {
                    $class = '';
                    if ( $current === $callback ) { $class = " class='current'";}
                    $href = $this->_add_query_arg(['tab' => $callback,'s' => false,'paged' => false,'post_mime_type' => false, 'm' => false,]);
                    $link = "<a href='{$this->_esc_url( $href )}' $class>$text</a>";
                    $callback_id = $this->_esc_attr("tab_{$callback}");
                    $output .= "\t<li id='$callback_id'>$link</li>\n";
                }
                $output .= "</ul>\n";
            }
            return $output;
        }//76
        protected function _print_media_upload_tabs():void{
            echo $this->_get_media_upload_tabs();
        }
        /**
         * @param $id
         * @param $caption
         * @param $title
         * @param $align
         * @param string $url
         * @param bool|string $rel
         * @param string $size
         * @param string $alt
         * @return string
         */
        protected function _get_image_send_to_editor( $id, $caption, $title, $align, $url = '', $rel = false, $size = 'medium', $alt = '' ):string{
            $html = $this->_get_image_tag( $id, $alt, '', $align, $size );
            if ( $rel ) {
                if ( is_string( $rel ) ) { $rel = " rel='{$this->_esc_attr( $rel )}'";}
                else {
                    $int_id = (int) $id;
                    $rel = " rel='attachment tp-att-{$int_id}'";
                }
            } else {$rel = '';}
            if ( $url ) {
                $html  = "<a href='{$this->_esc_attr( $url )}' $rel>$html</a>";
            }
            $html = $this->_apply_filters( 'image_send_to_editor', $html, $id, $caption, $title, $align, $url, $size, $alt, $rel );

            return $html;
        }//133
        /**
         * @description Adds image shortcode with caption to editor.
         * @param $html
         * @param $id
         * @param $caption
         * @param $align
         * @param $url
         * @param $size
         * @param string $alt
         * @return string
         */
        protected function _image_add_caption( $html, $id, $caption, $align, $url, $size, $alt = '' ):string{
            $caption = $this->_apply_filters( 'image_add_caption_text', $caption, $id );
            if ( empty( $caption ) || $this->_apply_filters( 'disable_captions', '' )){ return $html;}
            $id = ( 0 < (int) $id ) ? 'attachment_' . $id : '';
            if ( ! preg_match( '/width=["\'](\d+)/', $html, $matches )){ return $html;}
            $width = $matches[1];
            $caption = str_replace( array( "\r\n", "\r" ), "\n", $caption );
            $caption = preg_replace_callback( '/<[a-zA-Z0-9]+(?: [^<>]+>)*/', '_cleanup_image_add_caption', $caption );
            $caption = preg_replace( '/[ \n\t]*\n[ \t]*/', '<br />', $caption );
            $html = preg_replace( '/(class=["\'][^\'"]*)align(none|left|right|center)\s?/', '$1', $html );
            if ( empty( $align ) ) { $align = 'none'; }
            $short_code = "[caption id='$id' align='$align' width='$width' alt='$alt' size='$size' src='$url']{$html}{$caption}[/caption]";
            return $this->_apply_filters( 'image_add_caption_shortcode', $short_code, $html);
        }//188
        /**
         * @description Private preg_replace callback used in image_add_caption().
         * @param $matches
         * @return mixed
         */
        protected function _cleanup_image_add_caption( $matches ){
            return preg_replace( '/[\r\n\t]+/', ' ', $matches[0] );
        }//258
        /**
         * @description Adds image HTML to editor.
         * @param $html
         * @return string
         */
        protected function _get_media_send_to_editor( $html ):string{
            ob_start();
            ?>
            <script id='media_send_to_editor'>
                //noinspection JSUnresolvedVariable
                let win = window.dialogArguments || opener || parent || top;
                //noinspection JSUnresolvedFunction
                win.send_to_editor( <?php echo $this->_tp_json_encode( $html ); ?> );
            </script>
            <?php
            return ob_get_clean();
        }//270
        protected function _media_send_to_editor( $html ):void{
            echo $this->_get_media_send_to_editor( $html );
        }
        /**
         * @description Saves a file submitted from a POST request and create an attachment post for it.
         * @param $file_id
         * @param $post_id
         * @param array $post_data
         * @param array $overrides
         * @return string
         */
        protected function _media_handle_upload( $file_id, $post_id, $overrides = ['test_form' => false],...$post_data):string{
            $time = $this->_current_time( 'mysql' );
            $post = $this->_get_post( $post_id );
            if ($post && 'page' !== $post->post_type && substr($post->post_date, 0, 4) > 0) {
                $time = $post->post_date;}
            $file = $this->_tp_handle_upload( $_FILES[ $file_id ], $overrides, $time );
            if ( isset( $file['error'] ) ) { return new TP_Error( 'upload_error', $file['error'] );}
            $name = $_FILES[ $file_id ]['name'];
            $ext  = pathinfo( $name, PATHINFO_EXTENSION );
            $name = $this->_tp_basename( $name, ".$ext" );
            $url     = $file['url'];
            $type    = $file['type'];
            $file    = $file['file'];
            $title   = $this->_sanitize_text_field( $name );
            $content = '';
            $excerpt = '';
            if (0 === strpos($type, "audio")) {
                $meta = $this->_tp_read_audio_metadata( $file );
                if ( ! empty( $meta['title'] ) ) {$title = $meta['title'];}
                if ( ! empty( $title ) ) {
                    if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
                        $content .= sprintf( $this->__("'%1\$s' from %2\$s by %3\$s."), $title, $meta['album'], $meta['artist'] );
                    } elseif ( ! empty( $meta['album'] ) ) {
                        $content .= sprintf( $this->__("'%1\$s' from %2\$s." ), $title, $meta['album'] );
                    } elseif ( ! empty( $meta['artist'] ) ) {
                        $content .= sprintf($this->__("'%1\$s' by %2\$s."), $title, $meta['artist']);
                    } else { $content .= sprintf($this->__("'%s'."), $title);}
                } elseif ( ! empty( $meta['album'] ) ) {
                    if ( ! empty( $meta['artist'] ) ) {
                        $content .= sprintf( $this->__( '%1$s by %2$s.' ), $meta['album'], $meta['artist'] );
                    } else { $content .= $meta['album'] . '.';}
                } elseif ( ! empty( $meta['artist'] ) ) { $content .= $meta['artist'] . '.';}
                if ( ! empty( $meta['year'] ) ) { $content .= ' ' . sprintf( $this->__( 'Released: %d.' ), $meta['year'] ); }
                if ( ! empty( $meta['track_number'] ) ) {
                    $track_number = explode( '/', $meta['track_number'] );
                    if ( isset( $track_number[1] ) ) {
                         $content .= ' ' . sprintf( $this->__( 'Track %1$s of %2$s.' ), $this->_number_format_i18n( $track_number[0] ), $this->_number_format_i18n( $track_number[1] ) );
                    } else {$content .= ' ' . sprintf( $this->__( 'Track %s.' ), $this->_number_format_i18n( $track_number[0] ) ); }
                }
                if ( ! empty( $meta['genre'] ) ) { $content .= ' ' . sprintf( $this->__( 'Genre: %s.' ), $meta['genre'] );}
            } elseif ( 0 === strpos( $type, 'image/' ) ) {
                $image_meta = $this->_tp_read_image_metadata( $file );
                if ( $image_meta ) {
                    if ( trim( $image_meta['title'] ) && ! is_numeric( $this->_sanitize_title( $image_meta['title'] ) ) ) {
                        $title = $image_meta['title'];}
                    if ( trim( $image_meta['caption'])){ $excerpt = $image_meta['caption'];}
                }
            }
             $attachment = array_merge(['post_mime_type' => $type,'guid' => $url, 'post_parent' => $post_id,
                 'post_title' => $title,'post_content' => $content,'post_excerpt' => $excerpt,], $post_data);
            unset( $attachment['ID'] );
            $attachment_id = $this->_tp_insert_attachment( $attachment, $file, $post_id, true );
            if ( ! $this->_init_error( $attachment_id ) ) {
                if ( ! headers_sent() ) {  header( 'X-WP-Upload-Attachment-ID: ' . $attachment_id );}
                $this->_tp_update_attachment_metadata( $attachment_id, $this->_tp_generate_attachment_metadata( $attachment_id, $file ) );
            }
            return $attachment_id;
        }//292
        /**
         * @description Handles a side-loaded file in the same way as an uploaded file is handled by media_handle_upload().
         * @param $file_array
         * @param int $post_id
         * @param null $desc
         * @param array $post_data
         * @return string
         */
        protected function _media_handle_sideload( $file_array, $post_id = 0, $desc = null,array $post_data):string{
            $overrides = ['test_form' => false];
            if ( isset( $post_data['post_date'] ) && substr( $post_data['post_date'], 0, 4 ) > 0 ) {
                $time = $post_data['post_date'];
            } else {
                $post = $this->_get_post( $post_id );
                if ( $post && substr( $post->post_date, 0, 4 ) > 0 ){ $time = $post->post_date;}
                else {$time = $this->_current_time( 'mysql' );}
            }
            $file = $this->_tp_handle_sideload( $file_array, $overrides, $time );
            if ( isset( $file['error'])){ return new TP_Error( 'upload_error', $file['error'] );}
            $url     = $file['url'];
            $type    = $file['type'];
            $file    = $file['file'];
            $title   = preg_replace( '/\.[^.]+$/', '', $this->_tp_basename( $file ) );
            $content = '';
            $image_meta = $this->_tp_read_image_metadata( $file );
            if ( $image_meta ) {
                if ( trim( $image_meta['title'] ) && ! is_numeric( $this->_sanitize_title( $image_meta['title'] ) ) ) {
                    $title = $image_meta['title'];}
                if (trim( $image_meta['caption'] )){ $content = $image_meta['caption'];}
            }
            if(isset( $desc )){ $title = $desc;}
            $attachment = array_merge(['post_mime_type' => $type,'guid' => $url,'post_parent' => $post_id, 'post_title' => $title,'post_content' => $content,],$post_data);
            unset( $attachment['ID'] );
            $attachment_id = $this->_tp_insert_attachment( $attachment, $file, $post_id, true );
            if ( ! $this->_init_error( $attachment_id ) ) {
                $this->_tp_update_attachment_metadata( $attachment_id, $this->_tp_generate_attachment_metadata( $attachment_id, $file ) );
            }
            return $attachment_id;
        }//439
        /**
         * @description Outputs the iframe to display the media upload page.
         * @param $content_func
         * @param array ...$args
         * @return string
         */
        protected function _tp_get_iframe( $content_func, ...$args ):string{
            //$body_id_attr = '';
            if ( isset( $GLOBALS['body_id'] ) ) { $body_id_attr = " id='{$GLOBALS['body_id']}'";}
            else{ $body_id_attr = " id='temporary_id'";}
            $output  = "<head>"; //$this->_tp_get_admin_html_begin();
            $output .= "<title>{$this->_bloginfo( 'name' )}&rsaquo;{$this->__('Uploads')}&#8212;{$this->__('TailoredPress')}</title>";
            $this->tp_enqueue_style( 'colors' );
            if (
                ( is_array( $content_func ) && ! empty( $content_func[1] ) && 0 === strpos( (string) $content_func[1], 'media' ) ) ||
                ( ! is_array( $content_func ) && 0 === strpos( $content_func, 'media' ) )
            ) { $this->tp_enqueue_style( 'deprecated-media' );}
            ob_start();
            ?>
            <script id='tp_iframe_1'>console.log('todo,(tp_iframe_1) all but no jquery here!');</script>
            <?php
            $output .= ob_get_clean();
            $output .= $this->_do_action( 'admin_print_styles-media-upload-popup' ); //todo admin enqueue styles and scripts
            $output .= $this->_do_action( 'admin_print_styles' );
            $output .= $this->_do_action( 'admin_head-media-upload-popup' );
            $output .= $this->_do_action( 'admin_head' );
            if ( is_string( $content_func ) ){
                $output .= $this->_do_action( "admin_head_{$content_func}" );
            }
            $output .= "</head><body $body_id_attr class='tp-core-ui no-js'>";
            ob_start();
            ?>
            <script id='tp_iframe_2'>console.log('todo,(tp_iframe_2) all but no jquery here!');</script>
            <?php
            $output .= ob_get_clean();
            $output .= $this->_tp_call_user_func_array($content_func, $args);
            ob_start();
            $this->_do_action( 'admin_print_footer_scripts' );
            ?>
            <script id='tp_iframe_3'>console.log('todo,(tp_iframe_3) all but no jquery here!');</script>
            <?php
            $output .= ob_get_clean();
            $output .= "</body>";
            return $output;
        }//519
        protected function _tp_iframe( $content_func, ...$args ):void{}//519
    }
}else die;