<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-6-2022
 * Time: 04:22
 */
namespace TP_Admin\Traits\AdminMedia;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _adm_media_02{
        use _init_error;
        /**
         * @param string $editor_id
         * @return string
         * @description Adds the media button to the editor
         */
        protected function _get_media_buttons( $editor_id = 'content' ):string{
            static $instance = 0;
            $instance++;
            $post = $this->_get_post();
            if(! $post && ! empty( $GLOBALS['post_ID'])){ $post = $GLOBALS['post_ID'];}
            $this->_tp_enqueue_media(['post' => $post]);
            $img = "<span class='tp-media-buttons-icon'></span> ";
            $id_attribute = 1 === $instance ? " id='insert_media_button'" : '';
            return sprintf("<button type='button' %s class='button insert-media add_media' data-editor='%s'>%s</button>",
                $id_attribute,$this->_esc_attr( $editor_id ),$img.$this->__('Add Media'));
        }//623
        protected function _get_upload_iframe_src( $type = null, $post_id = null, $tab = null ){
            if ( empty( $post_id ) ) { $post_id = $this->tp_post_id; }
            $upload_iframe_src = $this->_add_query_arg( 'post_id', (int) $post_id, $this->_admin_url( 'media_upload.php' ) );
            if ( $type && 'media' !== $type ) { $upload_iframe_src = $this->_add_query_arg( 'type', $type, $upload_iframe_src );}
            if ( ! empty($tab)){ $upload_iframe_src = $this->_add_query_arg( 'tab', $tab, $upload_iframe_src );}
            $upload_iframe_src = $this->_apply_filters( "{$type}_upload_iframe_src", $upload_iframe_src );
            return $this->_add_query_arg( 'TP_iframe', true, $upload_iframe_src );
        }//674
        /**
         * @description Handles form submissions for the legacy media uploader.
         * @return null|string
         */
        protected function _media_upload_form_handler():?string{
            $this->_check_admin_referer( 'media_form' );
            $errors = null;
            if ( isset( $_POST['send'] ) ) {
                $keys    = array_keys( $_POST['send'] );
                $send_id = (int) reset( $keys );
            }
            if (!empty($_POST['attachments'])){
                foreach ( $_POST['attachments'] as $attachment_id => $attachment ) {
                    $post  = $this->_get_post( $attachment_id, ARRAY_A );
                    $_post = $post;
                    if (!$this->_current_user_can( 'edit_post', $attachment_id )){ continue;}
                    if ( isset( $attachment['post_content'])){ $post['post_content'] = $attachment['post_content'];}
                    if ( isset( $attachment['post_title'] ) ) { $post['post_title'] = $attachment['post_title']; }
                    if ( isset( $attachment['post_excerpt'] ) ) { $post['post_excerpt'] = $attachment['post_excerpt']; }
                    if ( isset( $attachment['menu_order'] ) ) { $post['menu_order'] = $attachment['menu_order']; }
                    if (isset($send_id, $attachment['post_parent']) && $attachment_id === $send_id) { $post['post_parent'] = $attachment['post_parent'];}
                    $post = $this->_apply_filters( 'attachment_fields_to_save', $post, $attachment );
                    if ( isset( $attachment['image_alt'] ) ) {
                        $image_alt = $this->_tp_unslash( $attachment['image_alt'] );
                        if ( $this->_get_post_meta( $attachment_id, '_tp_attachment_image_alt', true ) !== $image_alt ) {
                            $image_alt = $this->_tp_strip_all_tags( $image_alt, true );
                            $this->_update_post_meta( $attachment_id, '_tp_attachment_image_alt', $this->_tp_slash( $image_alt ) );
                        }
                    }
                    if ( isset( $post['errors'] ) ) {
                        $errors[ $attachment_id ] = $post['errors'];
                        unset( $post['errors'] );
                    }
                    if ( $post !== $_post ) { $this->_tp_update_post( $post );}
                    foreach ( $this->_get_attachment_taxonomies( $post ) as $t ) {
                        if(isset( $attachment[$t])){ $this->_tp_set_object_terms( $attachment_id, array_map( 'trim', preg_split( '/,+/', $attachment[ $t ] ) ), $t, false );}
                    }
                }
            }
            if ( isset( $_POST['insert-gallery'] ) || isset( $_POST['update-gallery'] ) ) {
                ob_start();
                ?>
                <script id='gallery'>console.log('gallery','todo')</script>
                <?php
                return ob_get_clean(); //instead of exit
            }
            if ( isset( $send_id ) ) {
                $attachment = $this->_tp_unslash( $_POST['attachments'][ $send_id ] );
                $html       = $attachment['post_title'] ?? '';
                if ( ! empty( $attachment['url'] ) ) {
                    $rel = '';
                    if ( strpos( $attachment['url'], 'attachment_id' ) || $this->_get_attachment_link( $send_id ) === $attachment['url'] ) {
                        $rel = " rel='attachment tp-att-{$this->_esc_attr( $send_id )}'";
                    }
                    $html = "<a href='{$attachment['url']}' $rel>$html</a>";
                }
                $html = $this->_apply_filters( 'media_send_to_editor', $html, $send_id, $attachment );
                return $this->_media_send_to_editor( $html );
            }
            return $errors;
        }//718
        /**
         * @description Handles the process of uploading media.
         * @return null|string
         */
        protected function _tp_media_upload_handler():?string{
            $errors = [];
            $id     = 0;
            $html = null;
            $class= null;
            if ( isset( $_POST['html-upload'] ) && ! empty( $_FILES ) ) {
                $this->_check_admin_referer( 'media-form' );
                $id = $this->_media_handle_upload( 'async-upload', $_REQUEST['post_id'] );
                unset( $_FILES );
                if ( $this->_init_error( $id ) ) {
                    $errors['upload_error'] = $id;
                    $id                     = false;
                }
            }
            if ( ! empty( $_POST['insert_only_button'] ) ) {
                $src = $_POST['src'];
                if (!empty( $src ) && ! strpos( $src, '://')){$src = "http://$src";}
                if ( isset( $_POST['media_type'] ) && 'image' !== $_POST['media_type'] ) {
                    $title = $this->_esc_html( $this->_tp_unslash( $_POST['title'] ) );
                    if ( empty( $title ) ) {$title = $this->_esc_html( $this->_tp_basename( $src ) );}
                    if ( $title && $src ) { $html = "<a href='{$this->_esc_url( $src )}'>$title</a>";}
                    $type = 'file';
                    $ext  = preg_replace( '/^.+?\.([^.]+)$/', '$1', $src );
                    if ( $ext ) {
                        $ext_type = $this->_tp_ext2type( $ext );
                        if ( 'audio' === $ext_type || 'video' === $ext_type ) {$type = $ext_type;}
                    }
                    $html = $this->_apply_filters( "{$type}_send_to_editor_url", $html, $this->_esc_url_raw( $src ), $title );
                } else {
                    $align = '';
                    $alt   = $this->_esc_attr( $this->_tp_unslash( $_POST['alt'] ) );
                    if ( isset( $_POST['align'] ) ) {
                        $align = $this->_esc_attr( $this->_tp_unslash( $_POST['align'] ) );
                        $class = " class='align-{$align}'";
                    }
                    if(!empty($src)){ $html = "<img src='{$this->_esc_url( $src )}' alt='$alt' $class />";}
                    $html = $this->_apply_filters( 'image_send_to_editor_url', $html, $this->_esc_url_raw( $src ), $alt, $align );
                }
                return $this->_media_send_to_editor( $html );
            }
            if (isset( $_POST['save'] )) {
                $errors['upload_notice'] = $this->__( 'Saved.' );
                $this->tp_enqueue_script( 'admin-gallery' );
                return $this->_tp_iframe( 'media_upload_gallery_form', $errors );
            }
            if (! empty( $_POST )) {
                $return = $this->_media_upload_form_handler();
                if(is_string( $return)){return $return; }
                if(is_array($return)){$errors = $return;}
            }
            if ( isset( $_GET['tab'] ) && 'type_url' === $_GET['tab'] ) {
                $type = 'image';
                if ( isset( $_GET['type'] ) && in_array( $_GET['type'],['video','audio','file'], true ) ) {
                    $type = $_GET['type'];}
                return $this->_tp_iframe( 'media_upload_type_url_form', $type, $errors, $id );
            }
            return $this->_tp_iframe( 'media_upload_type_form', 'image', $errors, $id );
        }//850
        /**
         * @description Downloads an image from the specified URL,
         * @description . saves it as an attachment, and optionally attaches it to a post.
         * @param $file
         * @param int $post_id
         * @param null $desc
         * @param string $return
         * @return null|string|TP_Error
         */
        protected function _media_sideload_image( $file, $post_id = 0, $desc = null, $return = 'html' ){
            $html = null;
            if ( ! empty( $file ) ) {
                $allowed_extensions = array( 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp' );
                $allowed_extensions = $this->_apply_filters( 'image_sideload_extensions', $allowed_extensions, $file );
                $allowed_extensions = array_map( 'preg_quote', $allowed_extensions );
                preg_match( '/[^\?]+\.(' . implode( '|', $allowed_extensions ) . ')\b/i', $file, $matches );
                if ( ! $matches ) {  return new TP_Error( 'image_sideload_failed', $this->__( 'Invalid image URL.' ) );}
                $file_array = [];
                $file_array['name'] = $this->_tp_basename( $matches[0] );
                $file_array['tmp_name'] = $this->_download_url( $file );
                if ( $this->_init_error( $file_array['tmp_name'] ) ){  return $file_array['tmp_name'];}
                $id = $this->_media_handle_sideload( $file_array, $post_id, $desc );
                if ( $this->_init_error( $id ) ) {
                    @unlink( $file_array['tmp_name'] );
                    return $id;
                }
                $this->_add_post_meta( $id, '_source_url', $file );
                if ( 'id' === $return ) { return $id;}
                $src = $this->_tp_get_attachment_url( $id );
            }
            if ( ! empty( $src ) ) {
                if ( 'src' === $return ) { return $src;}
                $alt  = isset( $desc ) ? $this->_esc_attr( $desc ) : '';
                $html = "<img src='$src' alt='$alt' />";
                return $html;
            }
            return new TP_Error( 'image_sideload_failed' );
        }//991
        /**
         * @description Retrieves the legacy media uploader form in an iframe.
         * @return null|string
         */
        protected function _media_upload_gallery():?string{
            $errors = [];
            if ( ! empty( $_POST ) ) {
                $return = $this->_media_upload_form_handler();
                if ( is_string( $return ) ) { return $return;}
                if(is_array($return)){$errors = $return;}
            }
            $this->tp_enqueue_script( 'admin-gallery' );
            return $this->_tp_iframe( 'media_upload_gallery_form', $errors );
        }//1075
        /**
         * @description Retrieves the legacy media library form in an iframe.
         * @return null|string
         */
        protected function _media_upload_library():?string{
            $errors = [];
            if ( ! empty( $_POST ) ) {
                $return = $this->_media_upload_form_handler();
                if ( is_string( $return )){ return $return;}
                if ( is_array( $return )){$errors = $return;}
            }
            return $this->_tp_iframe( 'media_upload_library_form', $errors );
        }//1101
        /**
         * @description Retrieve HTML for the image alignment radio buttons,
         * @description . with the specified one checked.
         * @param $post
         * @param string $checked
         * @return string
         */
        protected function _image_align_input_fields( $post, $checked = '' ):string{
            if(empty($checked)){$checked = $this->_get_user_setting('align','none');}
            $alignments = ['none' => $this->__('None'),'left' => $this->__('Left'),
                'center' => $this->__('Center'),'right' => $this->__('Right'),];
            if(!array_key_exists((string)$checked,$alignments)){ $checked ='none';}
            $output = [];
            foreach ( $alignments as $name => $label ) {
                $name  = $this->_esc_attr( $name );
                $_checked = ( $checked === $name ? " checked='checked'" : '' );
                $output[] .= "<dd><input name='attachments[{$post->ID}][align]' id='image_align_{$name}_{$post->ID}' type='radio' value='$name' $_checked /></dd>";
                $output[] .= "<dt><label for='image_align_{$name}_{$post->ID}' class='align image-align-{$name}-label'>$label</label></dt>";
            }
            return implode( "\n", $output );
        }//1127
        /**
         * @description Retrieve HTML for the size radio buttons with the specified one checked.
         * @param $post
         * @param string $check
         * @return array
         */
        protected function _image_size_input_fields( $post, $check = '' ):array{
            $size_names = $this->_apply_filters('image_size_names_choose',
                ['thumbnail' => $this->__( 'Thumbnail' ),'medium' => $this->__( 'Medium' ),
                    'large' => $this->__( 'Large' ), 'full' => $this->__( 'Full Size' ),]
            );
            if(empty($check)){ $check = $this->_get_user_setting('img_size','medium');}
            $out = [];
            foreach ( $size_names as $size => $label ) {
                $downsize = $this->_image_downsize( $post->ID, $size );
                $checked  = '';
                $enabled = ( $downsize[3] || 'full' === $size );
                $css_id  = "image_size_{$size}_{$post->ID}";
                if ( $size === $check ) {
                    if($enabled){ $checked = " checked='checked'";}
                    else { $check = '';}
                } elseif ( ! $check && $enabled && 'thumbnail' !== $size ) {
                    /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                    $check   = $size;
                    $checked = " checked='checked'";
                }
                $html = "<div class='image-size-item'><ul><li>";
                $html .= "<dd><input type='radio' {$this->_get_disabled( $enabled, false )} name='attachments[$post->ID][image-size]' id='{$css_id}' value='{$size}' $checked /></dd>";
                $html .= "<dt><label for='{$css_id}'>$label</label></dt></li>";
                if ( $enabled ) {
                    $dimensions = sprintf( '(%d&nbsp;&times;&nbsp;%d)', $downsize[1], $downsize[2] );
                    $html .= "<li><dt><label for='{$css_id}' class='help'>$dimensions</label></dt></li>";
                }
                $html .= '</ul></div>';
                $out[] = $html;
            }
            return ['label' => $this->__( 'Size' ),'input' => 'html','html' => implode( "\n",$out ),];
        }//1165
        /**
         * @description Retrieve HTML for the Link URL buttons with the default link type as specified.
         * @param $post
         * @param string $url_type
         * @return string
         */
        protected function _image_link_input_fields( $post, $url_type = '' ):string{
            $file = $this->_tp_get_attachment_url( $post->ID );
            $link = $this->_get_attachment_link( $post->ID );
            if(empty($url_type)){$url_type = $this->_get_user_setting('url_button','post');}
            $url = '';
            if('file' === $url_type){$url = $file;}
            elseif('post' === $url_type){$url = $link;}
            $output  = "<ul><li>";
            $output .= "<dd><input type='text' class='text url-field' name='attachments[$post->ID][url]' value='{$this->_esc_attr( $url )}' /></dd>";
            $output .= "</li><li>";
            $output .= "<dd><button type='button' class='button url-none' data-link-url=''>{$this->__( 'None' )}</button></dd>";
            $output .= "</li><li>";
            $output .= "<dd><button type='button' class='button url-file' data-link-url='{$this->_esc_attr( $file )}'>{$this->__( 'File URL' )}</button>";
            $output .= "</li><li>";
            $output .= "<dd><button type='button' class='button url-post' data-link-url='{$this->_esc_attr( $link )}'>{$this->__( 'Attachment Post URL' )}</button></dd>";
            $output .= "</li></ul>";
            return $output;
        }//1243
    }
}else die;