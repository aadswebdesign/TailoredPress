<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-6-2022
 * Time: 04:22
 */
namespace TP_Admin\Traits\AdminMedia;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\ID3\getID3;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_media_05{
        use _init_db;
        /**
         * @description Displays the out of storage quota message in Multisite.
         * @return string
         */
        protected function _get_multisite_over_quota_message():string{
            $output  = "<ul><li>";
            $output .= sprintf($this->__("Sorry, you have used your space allocation of %s. Please delete some files to upload more files."),$this->_size_format( $this->_get_space_allowed() * MB_IN_BYTES ));
            $output .= "</li></ul>";
            return $output;
        }//3094
        protected function _multisite_over_quota_message():void{
            echo $this->_get_multisite_over_quota_message();
        }//3094
        /**
         * @description Displays the image and editor in the post editor
         * @param $post
         * @return string
         */
        protected function _get_edit_form_image_editor( $post ):string{
            $open = isset( $_GET['image_editor'] );
            $quicktags_settings = ['buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'];
            $editor_args = ['textarea_name' => 'content','textarea_rows' => 5,'media_buttons' => false,
                'tinymce' => false,'quicktags' => $quicktags_settings,];
            $thumb_url     = false;
            $attachment_id = (int) $post->ID;
            if ( $attachment_id ) {
                $thumb_url = $this->_tp_get_attachment_image_src( $attachment_id, array( 900, 450 ), true );
            }
            $alt_text = $this->_get_post_meta( $post->ID, '_tp_attachment_image_alt', true );
            $att_url = $this->_tp_get_attachment_url( $post->ID );
            $output  = "<section class='edit-form'>";
            if ( $this->_tp_attachment_is_image( $post->ID ) ){
                $image_edit_button = '';
                if ( $this->_tp_image_editor_supports( array( 'mime_type' => $post->post_mime_type ) ) ) {
                    $nonce             = $this->_tp_create_nonce( "image_editor-$post->ID" );
                    $_onclick_open = "onclick='imageEdit.open( $post->ID, $nonce )'";
                    $image_edit_button = "<input type='button' id='img_edit_open-btn_{$post->ID}' $_onclick_open class='button' value='{$this->_esc_attr__( 'Edit Image' )}' /> <span class='spinner'></span>";
                }
                $open_style     = '';
                $not_open_style = '';
                if ( $open ) { $open_style = " style='display:block'";}
                else {$not_open_style = " style='display:none'";}
                $output .= "<div id='img_edit_response_{$attachment_id}' class='img-edit-response'></div>";
                $output .= "<div id='media_head_{$attachment_id}' class='tp-attachment-image' $open_style><ul><li>";
                $output .= "<p id='thumbnail_head_{$attachment_id}'><img class='thumbnail' src='{$this->_set_url_scheme( $thumb_url[0] )}' style='max-width:100%' alt=''/></p>";
                $output .= "</li><li><dd>{$image_edit_button}</dd></li><li>";
                $output .= "<div id='image_editor_{$attachment_id}' class='image-editor' $not_open_style>";
                if ( $open ) {
                    $output .= $this->_tp_get_image_editor( $attachment_id );
                }
                $output .= "</div></li></ul></div>";//media_head
            }elseif ( $attachment_id && $this->_tp_attachment_is( 'audio', $post ) ){
                $output .= "<div class='block audio-post'><ul><li>";
                ob_start();
                    $this->_tp_maybe_generate_attachment_metadata( $post );
                $output .= ob_get_clean();
                $output .= "</li><li>";
                $output .=  $this->tp_audio_shortcode(['src' => $att_url]);
                $output .= "</li></ul></div>";
            }elseif ( $attachment_id && $this->_tp_attachment_is( 'video', $post ) ){
                $meta = $this->_tp_get_attachment_metadata( $attachment_id );
                $w    = ! empty( $meta['width'] ) ? min( $meta['width'], 640 ) : 0;
                $h    = ! empty( $meta['height'] ) ? $meta['height'] : 0;
                if ( $h && $w < $meta['width'] ) {
                    $h = round( ( $meta['height'] * $w ) / $meta['width'] );
                }
                $attr = array( 'src' => $att_url );
                if ( ! empty( $w ) && ! empty( $h ) ) {
                    $attr['width']  = $w;
                    $attr['height'] = $h;
                }
                $thumb_id = $this->_get_post_thumbnail_id( $attachment_id );
                if ( ! empty( $thumb_id ) ) { $attr['poster'] = $this->_tp_get_attachment_url( $thumb_id );}
                $output .= "<div class='block video-post'><ul><li>";
                ob_start();
                $this->_tp_maybe_generate_attachment_metadata( $post );
                $output .= ob_get_clean();
                $output .= "</li><li>";
                $output .= $this->tp_video_shortcode( $attr );
                $output .= "</li></ul></div>";
            }elseif ( isset( $thumb_url[0] ) ){
                $output .= "<div id='media_head_{$attachment_id}' class='block tp-attachment-image'><ul><li>";
                $output .= "<p id='thumbnail_head_{$attachment_id}'><img class='thumbnail' src='{$this->_set_url_scheme( $thumb_url[0] )}' style='max-width:100%;' alt='' /></p>";
                $output .= "</li></ul></div>";
            }else{
                $output .= "<div class='block else'><ul><li>";
                $output .= $this->_do_action( 'tp_edit_form_attachment_display', $post );
                $output .= "</li></ul></div>";
            }
            $output .= "<div class='tp-attachment-details edit-form-section'><ul>";
            if (strpos($post->post_mime_type, 'image') === 0){
                $output .= "<li class='attachment-alt-text'><dt class='label'><label for='attachment_alt'><strong>{$this->__('Alternative Text')}</strong></label></dt>";
                $output .= "<dd class=''><input name='_tp_attachment_image_alt' id='attachment_alt' class='wide-fat' type='text' aria-describedby='alt-text-description' value='{$this->_esc_attr($alt_text)}'/></dd>";
                $output .= "</li><li>";
                $output .= "<p id='alt_text_description' class='attachment-alt-text-description'>{$this->__('')}</p>";
                $output .= sprintf($this->__("<a href='%1\$s' %2\$s>Learn how to describe the purpose of the image%3\$s</a>. Leave empty if the image is purely decorative."),
                    $this->_esc_url('https://www.w3.org/WAI/tutorials/images/decision-tree'), " target='_blank'"," rel='noopener'",
                    sprintf("<span class='screen-reader-text'> %s</span>",$this->__('(opens in a new tab)')));
                $output .= "</p></li><li>";
            }
            $output .= "<li>";
            $output .= "<dt class='label'><label for='attachment_caption'><strong>{$this->__('Caption')}</strong></label></dt>";
            $output .= "<dd class=''><textarea name='' id='attachment_caption'>{$post->post_excerpt}</textarea></dd>";
            $output .= "</li><li>";
            $output .= "<dt class='label'><label for='attachment_content' class='attachment-content-description'><strong>{$this->__('Description')}</strong>";
            if ( preg_match( '#^(audio|video)/#', $post->post_mime_type ) ){
                $output .= $this->__('Displayed on attachment pages.');
            }
            $output .= "</label></dt></li><li>";
            $output .= $this->_tp_get_editor( $this->_format_to_edit( $post->post_content ), 'attachment_content', $editor_args );
            $output .= "</li><li>";
            //$output .= $this->_get_compat_media_markup( $post->ID );
            $output .= "</li><li>";
            $output .= "<input id='image_edit_context' type='hidden' value='edit_attachment'/>\n";
            $output .= "</li></ul></div>";
            $output .= "</section>";
            return $output;
        }//3109
        protected function _edit_form_image_editor( $post ):void{
            echo $this->_get_edit_form_image_editor( $post );
        }//3109
        /**
         * @description Displays non-editable attachment metadata in the publish meta box.
         * @return string
         */
        protected function _get_attachment_submitbox_metadata():string{
            $post          = $this->_get_post();
            $attachment_id = $post->ID;
            $file     = $this->_get_attached_file( $attachment_id );
            $filename = $this->_esc_html( $this->_tp_basename( $file ) );
            $media_dims = '';
            $meta       = $this->_tp_get_attachment_metadata( $attachment_id );
            if ( isset( $meta['width'], $meta['height'] ) ) {
                $media_dims .= "<span id='media_dims_{$attachment_id}'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";
            }
            $media_dims = $this->_apply_filters( 'media_meta', $media_dims, $post );
            $att_url = $this->_tp_get_attachment_url( $attachment_id );
            $author = new TP_User( $post->post_author );
            $uploaded_by_name = $this->__( '(no author)' );
            $uploaded_by_link = '';
            if ( $author->exists() ) {
                $uploaded_by_name = $author->display_name ?: $author->nickname;
                $uploaded_by_link = $this->_get_edit_user_link( $author->ID );
            }
            $uploaded_by = $this->__('Uploaded by: ');
            $uploaded_to = $this->__('Uploaded to: ');
            $output  = "<section class='submitbox-metadata'>";
            $output .= "<div class='misc-pub-section misc-pub-uploaded-by'><ul><li>";
            if ($uploaded_by_link ) {
                $output .= "$uploaded_by<a href='$uploaded_by_link'><strong>$uploaded_by_name</strong></a>";
            }else{
                $output .= "$uploaded_by<strong>$uploaded_by_name</strong>";
            }
            $output .= "</li></ul></div>";
            if ( $post->post_parent ) {
                $post_parent = $this->_get_post( $post->post_parent );
                if ( $post_parent ) {
                    $uploaded_to_title = $post_parent->post_title ?: $this->__( '(no title)' );
                    $uploaded_to_link  = $this->_get_edit_post_link( $post->post_parent, 'raw' );
                    $output .= "<div class='misc-pub-section misc-pub-uploaded-to'><ul><li>";
                    if ( $uploaded_to_link ){
                        $output .= "$uploaded_to<a href='$uploaded_to_link'><strong>$uploaded_to_title</strong></a>";
                    }else{ $output .= "$uploaded_to<strong>$uploaded_to_title</strong>";}
                    $output .= "</li></ul></div>";
                }
            }
            $output .= "<div class='misc-pub-section misc-pub-attachment'><ul><li>";
            $output .= "<dt class='label'><label for='attachment_url'>{$this->__('')}</label></dt>";
            $output .= "<dd class='input'><input name='attachment_url' id='attachment_url' class='wide-fat url-field' type='text' value='{$this->_esc_attr($att_url)}' readonly/></dd>";
            $output .= "</li><li>";
            $output .= "<dd class='copy-to-clipboard-container'><button type='button' class='button copy-attachment-url edit-media' data-clipboard-target='#attachment_url'>{$this->__('Copy URL to clipboard')}</button></dd>";
            $output .= "<dt class='success hidden' aria-hidden='true'><small>{$this->__('Copied!')}</small></dt>";
            $output .= "</li></ul></div>";
            $output .= "<div class='misc-pub-section misc-pub-filename'><ul><li>";
            $output .= "{$this->__('File name:')}<strong>$filename</strong>";
            $output .= "</li></ul></div>";
            $output .= "<div class='misc-pub-section misc-pub-filetype'><ul><li>";
            $output .= "{$this->__('File type:')}<strong>";
            if ( preg_match( '/^.*?\.(\w+)$/', $this->_get_attached_file( $post->ID ), $matches ) ){
                $output .= $this->_esc_url(strtoupper( $matches[1]));
                @list( $mime_type ) = explode( '/', $post->post_mime_type );
                if ('image' !== $mime_type && !empty($meta['mime_type']) && "$mime_type/" . strtolower($matches[1]) !== $meta['mime_type']) {
                    $output .= "({$meta['mime_type']})";
                }
            }else{ $output .= strtoupper( str_replace( 'image/', '', $post->post_mime_type ) );}
            $output .= "</strong></li></ul></div>";
            $file_size = false;
            if ( isset( $meta['filesize'] ) ) { $file_size = $meta['filesize'];
            } elseif ( file_exists( $file ) ) { $file_size = filesize( $file );}
            if ( ! empty( $file_size ) ){
                $output .= "<div class='misc-pub-section misc-pub-filesize'><ul><li>";
                $output .= "{$this->__('File size:')}<strong>{$this->_size_format( $file_size )}</strong>";
                $output .= "</li></ul></div>";
            }
            if ( preg_match( '#^(audio|video)/#', $post->post_mime_type ) ) {
                $fields = ['length_formatted' => $this->__( 'Length:' ),'bitrate' => $this->__( 'Bitrate:' ),];
                $fields = $this->_apply_filters( 'media_submitbox_misc_sections', $fields, $post );
                foreach ( $fields as $key => $label ){
                    if(empty($meta[$key])){continue;}
                    $output .= "<div class='misc-pub-section misc-pub-mime-meta misc-pub-{$this->_sanitize_html_class( $key )}'><ul><li>";
                    $output .= "<dt class='label'>$label</dt>";
                    $output .= "</li><li><strong>";
                    if ($key === 'bitrate') {
                        $output .= round($meta['bitrate'] / 1000) . 'kb/s';
                        if (!empty($meta['bitrate_mode'])) {$output .= strtoupper($this->_esc_html($meta['bitrate_mode']));}
                    } else {$output .= $this->_esc_html($meta[$key]);}
                    $output .= "</strong></li></ul></div>";
                }
                $fields = ['data_format' => $this->__( 'Audio Format:' ),'codec' => $this->__( 'Audio Codec:' ),];
                $audio_fields = $this->_apply_filters( 'audio_submitbox_misc_sections', $fields, $post );
                foreach ( $audio_fields as $key => $label ){
                    if (empty($meta['audio'][$key])){continue;}
                    $output .= "<div class='misc-pub-section misc-pub-audio misc-pub-{$this->_sanitize_html_class( $key )}'><ul><li>";
                    $output .= "<dt>$label</dt>";
                    $output .= "</li><li><strong>{$this->_esc_html($meta['audio'][ $key ])}</strong>";
                    $output .= "</li></ul></div>";
                }
            }
            if ( $media_dims ){
                $output .= "<div class='misc-pub-section misc-pub-dimensions'><ul><li>";
                $output .= "{$this->__('Dimensions:')}<strong>$media_dims</strong>";
                $output .= "</li></ul></div>";
            }
            if(!empty($meta['original_image'])){
                $output .= "<div class='misc-pub-section misc-pub-original-image'><ul><li>";
                $output .= "{$this->__('Original image:')}<a href='{$this->_esc_url($this->_tp_get_original_image_url( $attachment_id ))}'>";
                $output .= "{$this->_esc_html($this->_tp_basename( $this->_tp_get_original_image_path( $attachment_id ) ))}</a></li></ul></div>";
            }
            $output .= "</section>";
            return $output;
        }//3287
        protected function _attachment_submitbox_metadata():void{
            echo $this->_get_attachment_submitbox_metadata();
        }//3287
        /**
         * @description Parse ID3v2, ID3v1, and getID3 comments to extract usable data
         * @param $metadata
         * @param $data
         */
        protected function _tp_add_id3_tag_data( &$metadata, $data ):void{
            foreach ( array( 'id3v2', 'id3v1' ) as $version ) {
                if ( ! empty( $data[ $version ]['comments'] ) ) {
                    foreach ( $data[ $version ]['comments'] as $key => $list ) {
                        if ( 'length' !== $key && ! empty( $list ) ) {
                            $metadata[ $key ] = $this->_tp_kses_post( reset( $list ) );
                            if ( 'terms_of_use' === $key && 0 === strpos( $metadata[ $key ], 'yright notice.' ) ) {
                                $metadata[ $key ] = 'Cop' . $metadata[ $key ];
                            }
                        }
                    }
                    break;
                }
            }
            if ( ! empty( $data['id3v2']['APIC'] ) ) {
                $image = reset( $data['id3v2']['APIC'] );
                if ( ! empty( $image['data'] ) ) {
                    $metadata['image'] = ['data' => $image['data'],'mime' => $image['image_mime'],
                        'width' => $image['image_width'],'height' => $image['image_height'],];
                }
            } elseif ( ! empty( $data['comments']['picture'] ) ) {
                $image = reset( $data['comments']['picture'] );
                if ( ! empty( $image['data'] ) ) {
                    $metadata['image'] = ['data' => $image['data'], 'mime' => $image['image_mime'],];
                }
            }
        }//3501
        /**
         * @description Retrieve metadata from a video file's ID3 tags
         * @param $file
         * @return string
         */
        protected function _tp_read_video_metadata( $file ):string{
            if ( ! file_exists( $file ) ) {
                return false;
            }
            $metadata = [];
            if ( ! defined( 'GETID3_TEMP_DIR' ) ) {
                define( 'GETID3_TEMP_DIR', $this->_get_temp_dir() );
            }
            $id3 = new getID3();
            $id3->options_audiovideo_quicktime_ReturnAtomData = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
            $data = $id3->analyze( $file );
            if ( isset( $data['video']['lossless'] ) ) {
                $metadata['lossless'] = $data['video']['lossless'];
            }
            if ( ! empty( $data['video']['bitrate'] ) ) {
                $metadata['bitrate'] = (int) $data['video']['bitrate'];
            }
            if ( ! empty( $data['video']['bitrate_mode'] ) ) {
                $metadata['bitrate_mode'] = $data['video']['bitrate_mode'];
            }
            if ( ! empty( $data['filesize'] ) ) {
                $metadata['filesize'] = (int) $data['filesize'];
            }
            if ( ! empty( $data['mime_type'] ) ) {
                $metadata['mime_type'] = $data['mime_type'];
            }
            if ( ! empty( $data['playtime_seconds'] ) ) {
                $metadata['length'] = (int) round( $data['playtime_seconds'] );
            }
            if ( ! empty( $data['playtime_string'] ) ) {
                $metadata['length_formatted'] = $data['playtime_string'];
            }
            if ( ! empty( $data['video']['resolution_x'] ) ) {
                $metadata['width'] = (int) $data['video']['resolution_x'];
            }
            if ( ! empty( $data['video']['resolution_y'] ) ) {
                $metadata['height'] = (int) $data['video']['resolution_y'];
            }
            if ( ! empty( $data['fileformat'] ) ) {
                $metadata['fileformat'] = $data['fileformat'];
            }
            if ( ! empty( $data['video']['dataformat'] ) ) {
                $metadata['dataformat'] = $data['video']['dataformat'];
            }
            if ( ! empty( $data['video']['encoder'] ) ) {
                $metadata['encoder'] = $data['video']['encoder'];
            }
            if ( ! empty( $data['video']['codec'] ) ) {
                $metadata['codec'] = $data['video']['codec'];
            }
            if ( ! empty( $data['audio'] ) ) {
                unset( $data['audio']['streams'] );
                $metadata['audio'] = $data['audio'];
            }
            if ( empty( $metadata['created_timestamp'] ) ) {
                $created_timestamp = $this->_tp_get_media_creation_timestamp( $data );
                if ( false !== $created_timestamp ) {
                    $metadata['created_timestamp'] = $created_timestamp;
                }
            }
            $this->_tp_add_id3_tag_data( $metadata, $data );
            $file_format = $metadata['fileformat'] ?? null;
            return $this->_apply_filters( 'tp_read_video_metadata', $metadata, $file, $file_format, $data );
        }//3546
        /**
         * @description Retrieve metadata from an audio file's ID3 tags.
         * @param $file
         * @return string
         */
        protected function _tp_read_audio_metadata( $file ):string{
            if ( ! file_exists( $file ) ) {
                return false;
            }
            $metadata = [];
            if ( ! defined( 'GETID3_TEMP_DIR' ) ) {
                define( 'GETID3_TEMP_DIR', $this->_get_temp_dir() );
            }
            $id3 = new getID3();
            $id3->options_audiovideo_quicktime_ReturnAtomData = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
            $data = $id3->analyze( $file );
            if ( ! empty( $data['audio'] ) ) {
                unset( $data['audio']['streams'] );
                $metadata = $data['audio'];
            }
            if ( ! empty( $data['fileformat'] ) ) {
                $metadata['fileformat'] = $data['fileformat'];
            }
            if ( ! empty( $data['filesize'] ) ) {
                $metadata['filesize'] = (int) $data['filesize'];
            }
            if ( ! empty( $data['mime_type'] ) ) {
                $metadata['mime_type'] = $data['mime_type'];
            }
            if ( ! empty( $data['playtime_seconds'] ) ) {
                $metadata['length'] = (int) round( $data['playtime_seconds'] );
            }
            if ( ! empty( $data['playtime_string'] ) ) {
                $metadata['length_formatted'] = $data['playtime_string'];
            }
            if ( empty( $metadata['created_timestamp'] ) ) {
                $created_timestamp = $this->_tp_get_media_creation_timestamp( $data );
                if ( false !== $created_timestamp ) {
                    $metadata['created_timestamp'] = $created_timestamp;
                }
            }
            $this->_tp_add_id3_tag_data( $metadata, $data );
            return $metadata;
        }//3660
        /**
         * @description Parse creation date from media metadata.
         * @param $metadata
         * @return bool
         */
        protected function _tp_get_media_creation_timestamp( $metadata ):bool{
            $creation_date = false;
            if ( empty( $metadata['fileformat'] ) ) {
                return $creation_date;
            }
            switch ( $metadata['fileformat'] ) {
                case 'asf':
                    if ( isset( $metadata['asf']['file_properties_object']['creation_date_unix'] ) ) {
                        $creation_date = (int) $metadata['asf']['file_properties_object']['creation_date_unix'];
                    }
                    break;
                case 'matroska':
                case 'webm':
                    if ( isset( $metadata['matroska']['comments']['creation_time'][0] ) ) {
                        $creation_date = strtotime( $metadata['matroska']['comments']['creation_time'][0] );
                    } elseif ( isset( $metadata['matroska']['info'][0]['DateUTC_unix'] ) ) {
                        $creation_date = (int) $metadata['matroska']['info'][0]['DateUTC_unix'];
                    }
                    break;
                case 'quicktime':
                case 'mp4':
                    if ( isset( $metadata['quicktime']['moov']['subatoms'][0]['creation_time_unix'] ) ) {
                        $creation_date = (int) $metadata['quicktime']['moov']['subatoms'][0]['creation_time_unix'];
                    }
                    break;
            }
            return $creation_date;
        }//3733
        /**
         * @description Encapsulates the logic for Attach/Detach actions.
         * @param $parent_id
         * @param string $action
         */
        protected function _tp_media_attach_action( $parent_id, $action = 'attach' ):void{
            $this->tpdb = $this->_init_db();
            if ( ! $parent_id ) {return; }
            if ( ! $this->_current_user_can( 'edit_post', $parent_id ) ) {
                $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            }
            $ids = [];
            foreach ( (array) $_REQUEST['media'] as $attachment_id ) {
                $attachment_id = (int) $attachment_id;
                if ( ! $this->_current_user_can( 'edit_post', $attachment_id ) ) {
                    continue;
                }
                $ids[] = $attachment_id;
            }
            if ( ! empty( $ids ) ) {
                $ids_string = implode( ',', $ids );
                if ( 'attach' === $action ) {
                    $result = $this->tpdb->query( $this->tpdb->prepare( TP_UPDATE ." $this->tpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID IN ( $ids_string )", $parent_id ) );
                } else {
                    $result = $this->tpdb->query( TP_UPDATE ." $this->tpdb->posts SET post_parent = 0 WHERE post_type = 'attachment' AND ID IN ( $ids_string )" );
                }
            }
            if ( isset( $result ) ) {
                foreach ($ids as $attachment_id) {
                    $this->_do_action('tp_media_attach_action', $action, $attachment_id, $parent_id);
                    $this->_clean_attachment_cache($attachment_id);
                }
                $location = 'upload.php';
                $referer = $this->_tp_get_referer();
                if ($referer && false !== strpos($referer, 'upload.php')) {
                    $location = $this->_remove_query_arg(array('attached', 'detach'), $referer);
                }
                $key = 'attach' === $action ? 'attached' : 'detach';
                $location = $this->_add_query_arg(array($key => $result), $location);
                $this->_tp_redirect($location);
                exit;
            }
        }//3778
    }
}else die;