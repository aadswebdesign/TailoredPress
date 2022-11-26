<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 23:34
 */
namespace TP_Admin\Traits\AdminImage;
use TP_Core\Libs\Editor\TP_Image_Editor;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_image_edit{
        use _init_error;
        /**
         * @description Loads the TP image-editing interface.
         * @param $post_id
         * @param bool $msg
         * @return string
         */
        protected function _tp_get_image_editor( $post_id, $msg = false ):string{
            $nonce     = $this->_tp_create_nonce( "image_editor-$post_id" );
            $meta      = $this->_tp_get_attachment_metadata( $post_id );
            $thumb     = $this->_image_get_intermediate_size( $post_id, 'thumbnail' );
            $sub_sizes = isset( $meta['sizes'] ) && is_array( $meta['sizes'] );
            $note      = '';
            $big = null;
            if ( isset( $meta['width'], $meta['height'] ) ) {
                $big = max( $meta['width'], $meta['height'] );
            } else {
                die( $this->__( '<small>Image data does not exist. Please re-upload the image.</small>' ) );
            }
            $sizer = $big > 400 ? 400 / $big : 1;
            $backup_sizes = $this->_get_post_meta( $post_id, '_tp_attachment_backup_sizes', true );
            $can_restore  = false;
            if ( ! empty( $backup_sizes ) && isset( $backup_sizes['full-orig'], $meta['file'] ) ) {
                $can_restore = $this->_tp_basename( $meta['file'] ) !== $backup_sizes['full-orig']['file'];
            }
            if ( $msg ) {
                if (! isset( $msg->error ) ) {
                    $note = "<div class='notice notice-error' tabindex='-1' role='alert'><p>$msg->error</p></div>";
                } elseif (isset( $msg->msg ) ) {
                    $note = "<div class='notice notice-success' tabindex='-1' role='alert'><p>$msg->msg</p></div>";
                }
            }
            $output  = "<div id='img_edit_panel_{$post_id}' class=''>";
            $output .= "<section class='img-edit-panel section-one'>{$note}";
            $output .= "<div class='img-edit-menu'><ul>";
            $_onclick_1 = " onclick='imageEdit.handleCropToolClick({$post_id}, &#34;$nonce&#34;)'";
            $output .= "<li>";
            $output .= "<button type='button' $_onclick_1 class='img-edit-crop button disabled'>{$this->_esc_html('Crop')}</button>";
            $_edit_supports = ['mime_type' => $this->_get_post_mime_type( $post_id ),'methods' => ['rotate'],];
            if ($this->_tp_image_editor_supports($_edit_supports)){
                $output .= "</li>";
                $note_no_rotate = '';
                $_onclick_left_1 = " onclick='imageEdit.rotate(90,{$post_id}, &#34;$nonce&#34;)'";
                $_onclick_right_1 = " onclick='imageEdit.rotate(-90,{$post_id}, &#34;$nonce&#34;)'";
                $output .= "<li>";
                $output .= "<button type='button' $_onclick_left_1 class='img-edit-rotate-left button' >{$this->_esc_html('Rotate left')}</button>";
                $output .= "</li><li>";
                $output .= "<button type='button' $_onclick_right_1 class='img-edit-rotate-right button'>{$this->_esc_html('Rotate right')}</button>";
            }else{
                $note_no_rotate = "<li><small class='note-no-rotate'><em>{$this->__('Image rotation is not supported by your web host.')}</em></small>";
                $output .= "</li><li class='disabled'>";
                $output .= "<button type='button' class='img-edit-rotate-left button disabled' disabled></button>";
                $output .= "</li><li class='disabled'>";
                $output .= "<button type='button' class='img-edit-rotate-right button disabled' disabled></button>";
            }
            $output .= "</li>";
            $_onclick_flip_vert = " onclick='imageEdit.flip(1,{$post_id}, &#34;$nonce&#34;)'";
            $_onclick_flip_hor = " onclick='imageEdit.flip(2,{$post_id}, &#34;$nonce&#34;)'";
            $_onclick_undo = " onclick='imageEdit.undo({$post_id}, &#34;{$nonce}&#34;,this)'";
            $_onclick_redo = " onclick='imageEdit.redo({$post_id}, &#34;{$nonce}&#34;,this)'";
            $output .= "<li>";
            $output .= "<button type='button' $_onclick_flip_vert class='img-edit-flip-vert button'>{$this->_esc_html('Flip vertical')}</button>";
            $output .= "</li><li>";
            $output .= "<button type='button' $_onclick_flip_hor class='img-edit-flip-hor button'>{$this->_esc_html('Flip horizontal')}</button>";
            $output .= "</li><li class='disabled' disabled>";
            $output .= "<button type='button' $_onclick_undo id='img_undo_{$post_id}' class='img-edit-undo button disabled' disabled>{$this->_esc_html('Undo')}</button>";
            $output .= "</li><li class='disabled' disabled>";
            $output .= "<button type='button' $_onclick_redo id='img_redo_{$post_id}' class='img-edit-redo button disabled' disabled>{$this->_esc_html('Redo')}</button>";
            $output .= $note_no_rotate;
            $output .= "</li><li>";
            $output .= "<input id='img_edit_sizer_{$post_id}' type='hidden' value='$sizer'/>";
            $output .= "<input id='img_edit_history_{$post_id}' type='hidden' value=''/>";
            $output .= "<input id='img_edit_undone_{$post_id}' type='hidden' value='0'/>";
            $output .= "<input id='img_edit_selection_{$post_id}' type='hidden' value='{$this->_esc_attr('')}'/>";
            $_meta_height = $meta['height'] ?? 0;
            $_meta_width = $meta['width'] ?? 0;
            $output .= "<input id='img_edit_x_{$post_id}' type='hidden' value='$_meta_width'/>";
            $output .= "<input id='img_edit_y_{$post_id}' type='hidden' value='$_meta_height'/>";
            $output .= "</li><li id='img_edit_crop_{$post_id}' class='img-edit-crop-wrap'>";
            $_img_rand = random_int( 1, 99999 );
            $_onload_img = " onload='imageEdit.imgLoaded(&#34;{$post_id}&#34;)'";
            $_img_src = " src='{$this->_esc_url($this->_admin_url('admin_async.php', 'relative'))}?action=img-edit-preview&amp;_async_nonce={$nonce}&amp;postid={$post_id}&amp;rand={$_img_rand}'";
            $output .= "<img id='img_preview_{$post_id}' $_onload_img $_img_src alt=''/>";
            $output .= "</li>";
            $_onclick_cancel = " onclick='imageEdit.close({$post_id})'";
            $_onclick_save = " onclick='imageEdit.save({$post_id}, &#34;{$nonce}&#34;)'";
            $output .= "<li class='img-edit-submit'>";
            $output .= "<dd><input $_onclick_cancel type='button' class='img-edit-cancel-btn button' value='{$this->_esc_attr('Cancel')}'/></dd>";
            $output .= "<dd><input $_onclick_save type='button' class='img-edit-submit-btn button button-primary' value='{$this->_esc_attr('Save')}'/></dd>";
            $output .= "</li></ul></div>";
            $output .= "</section><section class='img-edit-settings section-two'>";
            $output .= "<div class='img-edit-group'><ul><li>";
            $output .= "<h2 class=''>{$this->__('Scale Image')}</h2>";
            $output .= "</li><li>";
            $_onclick_help = " onclick='imageEdit.toggleHelp(this)'";
            $output .= "<button type='button' $_onclick_help class='dashicons dashicons-editor-help img-edit-help-toggle' aria-expanded='false'><span class='screen-reader-text'>{$this->__('Scale Image Help')}</span></button>";
            $output .= "<div class='img-edit-help'><p class=''>{$this->__('You can proportionally scale the original image. For best results, scaling should be done before you crop, flip, or rotate. Images can only be scaled down, not up.')}</p></div>";
            if ( isset( $meta['width'], $meta['height'] ) ){
                $output .= "<p>";
                $output .= sprintf($this->__('Original dimensions %s'),"<span class='img-edit-original-dimensions'>{$this->__($meta['width'])} &times; {$meta['height']}</span>");
                $output .= "</p>";
            }
            $output .= "</li><li class='img-edit-submit'>";
            $output .= "<fieldset class='img-edit-scale'><legend>{$this->__('New dimensions:')}</legend><ul><li>";
            $output .= "<dt><label class='screen-reader-text' for='img_edit_scale_width_{$post_id}'>{$this->__('scale width')}</label></dt>";
            $_input_scale_width = " onkeyup='imageEdit.scaleChanged($post_id, 1, this)' onblur='imageEdit.scaleChanged($post_id, 1, this)'";
            $_input_scale_height = " onkeyup='imageEdit.scaleChanged($post_id, 0, this)' onblur='imageEdit.scaleChanged($post_id, 0, this)'";
            $output .= "<dd><input id='img_edit_scale_width_{$post_id}' type='text' $_input_scale_width value='{$_meta_width}'/></dd>";
            $output .= "</li><li>";
            $output .= "<span class='img-edit-separator' aria-hidden='true'>&times;</span>";
            $output .= "</li><li>";
            $output .= "<dt><label class='screen-reader-text' for='img_edit_scale_height_{$post_id}'>{$this->__('scale height')}</label></dt>";
            $output .= "<dd><input id='img_edit_scale_height_{$post_id}' type='text' $_input_scale_height value='{$_meta_height}'/></dd>";
            $output .= "</li><li>";
            $output .= "<span id='img_edit_scale_warn_{$post_id}' class='img-edit-scale-warn'>!</span>";
            $output .= "</li><li class='img-edit-scale-button-wrapper'>";
            $_onclick_action_scale = " onclick='imageEdit.action({$post_id}, &#34;{$nonce}&#34;, scale)'";
            $_onclick_action_restore =  " onclick='imageEdit.action({$post_id}, &#34;{$nonce}&#34;, restore)'";
            $output .= "<dd><input class='button button-primary' id='img_edit_scale_button' type='button' $_onclick_action_scale value='{$this->_esc_attr('Scale')}'/></dd>";
            $output .= "</li></ul></fieldset></li><li></li></ul></div>";
            if ($can_restore ) {
                $_overwrite = null;
                if ( ! defined( 'IMAGE_EDIT_OVERWRITE' ) || ! IMAGE_EDIT_OVERWRITE ) {
                    $_overwrite = "<em>{$this->__(' Previously edited copies of the image will not be deleted.')}</em>";
                }
                $output .= "<div class='img-edit-group'><ul><li>";
                $output .= "<h2><button type='button' $_onclick_help class='button-link' aria-expanded='false'>{$this->__('Restore original image')}<span class='dashicons dashicons-arrow-down imgedit-help-toggle'></span></button></h2>";
                $output .= "</li><li class='img-edit-help img-edit-restore'>";
                $output .= "<p>{$this->__('Discard any changes and restore the original image.')}$_overwrite</p>";
                $output .= "</li><li class='img-edit-submit'>";
                $output .= "<dd><input type='button' class='button button-primary' $_onclick_action_restore value='{$this->_esc_attr('Restore image')}'/></dd>$can_restore";
                $output .= "</li></ul></div>";
            }
            $output .= "<div class='img-edit-group'><ul><li>";
            $output .= "<h2>{$this->__('Image Crop')}</h2>";
            $output .= "</li><li>";
            $output .= "<button type='button' class='dashicons dashicons-editor-help img-edit-help-toggle' $_onclick_help aria-expanded='false'><span class='screen-reader-text'>{$this->_esc_html('Image Crop Help')}</span></button>";
            $output .= "</li><li class='img-edit-help img-edit-restore'>";
            $output .= "<p>{$this->__('To crop the image, click on it and drag to make your selection.')}</p>";
            $output .= "<p><strong>{$this->__('Crop Aspect Ratio')}</strong>{$this->__('The aspect ratio is the relationship between the width and height. You can preserve the aspect ratio by holding down the shift key while resizing your selection. Use the input box to specify the aspect ratio, e.g. 1:1 (square), 4:3, 16:9, etc.')}</p>";
            $output .= "<p><strong>{$this->__('Crop Selection')}</strong>{$this->__('Once you have made your selection, you can adjust it by entering the size in pixels. The minimum selection size is the thumbnail size as set in the Media settings.')}</p>";
            $output .= "</li><li>";
            $output .= "<fieldset class='img-edit-crop-ratio'><legend>{$this->__('Aspect ratio:')}</legend><ul><li>";
            $_input_crop_width = " onkeyup='imageEdit.setRatioSelection($post_id, 0, this)' onblur='imageEdit.setRatioSelection($post_id, 0, this)'";
            $_input_crop_height = " onkeyup='imageEdit.setRatioSelection($post_id, 1, this)' onblur='imageEdit.setRatioSelection($post_id, 1, this)'";
            $output .= "<dt><label for='img_edit_crop_width_{$post_id}' class='screen-reader-text'>{$this->__('crop ratio width')}</label></dt>";
            $output .= "<dd><input id='img_edit_crop_width_{$post_id}' type='text' $_input_crop_width /></dd>";
            $output .= "</li><li>";
            $output .= "<span class='img-edit-separator' aria-hidden='true'>&times;</span>";
            $output .= "</li><li>";
            $output .= "<dt><label for='img_edit_crop_height_{$post_id}' class='screen-reader-text'>{$this->__('crop ratio height')}</label></dt>";
            $output .= "<dd><input id='img_edit_crop_height_{$post_id}' type='text' $_input_crop_height /></dd>";
            $output .= "</li></ul></fieldset></li>";
            $_input_sel_width = " onkeyup='imageEdit.setNumSelection($post_id, this)' onblur='imageEdit.setNumSelection($post_id, this)'";
            $_input_sel_height = " onkeyup='imageEdit.setNumSelection($post_id, this)' onblur='imageEdit.setNumSelection($post_id, this)'";
            $output .= "<li><fieldset id='img_edit_crop_sel_{$post_id}' class='img-edit-crop-sel'><legend>{$this->__('Selection:')}</legend><ul><li>";
            $output .= "<dt><label for='img_edit_sel_width_{$post_id}' class='screen-reader-text'>{$this->__('selection width')}</label></dt>";
            $output .= "<dd><input id='img_edit_sel_width_{$post_id}' type='text' $_input_sel_width /></dd>";
            $output .= "</li><li>";
            $output .= "<span class='img-edit-separator' aria-hidden='true'>&times;</span>";
            $output .= "</li><li>";
            $output .= "<dt><label for='img_edit_sel_height_{$post_id}' class='screen-reader-text'>{$this->__('selection height')}</label></dt>";
            $output .= "<dd><input id='img_edit_sel_height_{$post_id}' type='text' $_input_sel_height /></dd>";
            $output .= "</li></ul></fieldset>";
            $output .= "</li></ul></div>";
            if ( $thumb && $sub_sizes ) {
                $thumb_img = $this->_tp_constrain_dimensions( $thumb['width'], $thumb['height'], 160, 120 );
                $output .= "<div class='img-edit-group img-edit-apply-to'><ul><li>";
                $output .= "<h2>{$this->__('Thumbnail Settings')}</h2>";
                $output .= "</li><li class='img-edit-help'>";
                $output .= "<button type='button' class='dashicons dashicons-editor-help img-edit-help-toggle' aria-expanded='false' $_onclick_help ><span class='screen-reader-text'>{$this->_esc_html('Thumbnail Settings Help')}</span></button>";
                $output .= "<p>{$this->__('You can edit the image while preserving the thumbnail. For example, you may wish to have a square thumbnail that displays just a section of the image.')}</p>";
                $output .= "</li><li><figure class='img-edit-thumbnail-preview'>";
                $output .= "<img class='img-edit-size-preview' src='{$thumb['url']}' width='{$thumb_img[0]}' height='{$thumb_img[1]}' alt='' draggable='false'/>";
                $output .= "<figcaption class='img-edit-thumbnail-preview-caption'>{$this->__('Current thumbnail')}</figcaption>";
                $output .= "</figure></li><li id='img_edit_save_target_{$post_id}' class='img-edit-save-target'>";
                $output .= "<fieldset><legend>{$this->__('Apply changes to:')}</legend><ul><li class='img-edit-label'>";
                $output .= "<dd><input name='img_edit_target_{$post_id}' id='img_edit_target_all' type='radio' value='all' checked/></dd>";
                $output .= "<dt><label for='img_edit_target_all' class=''>{$this->__('All image sizes')}</label></dt>";
                $output .= "</li><li class='img-edit-label'>";
                $output .= "<dd><input name='img_edit_target_{$post_id}' id='img_edit_target_thumbnail' type='radio' value='thumbnail'/></dd>";
                $output .= "<dt><label for='img_edit_target_thumbnail' class=''>{$this->__('Thumbnail')}</label></dt>";
                $output .= "</li><li class='img-edit-label'>";
                $output .= "<dd><input name='img_edit_target_{$post_id}' id='img_edit_target_no_thumb' type='radio' value='no_thumb'/></dd>";
                $output .= "<dt><label for='img_edit_target_no_thumb' class=''>{$this->__('All sizes except thumbnail')}</label></dt>";
                $output .= "</li></ul></fieldset>";
                $output .= "</li></ul></div>";
            }
            $output .= "<div id='img_edit_wait_{$post_id}' class='img-edit-wait'><ul><li>";
            $output .= "<p id='img_edit_leaving_{$post_id}' class='hidden'>{$this->__('There are unsaved changes that will be lost. \'OK\' to continue, \'Cancel\' to return to the Image Editor.')}</p>";
            $output .= "</li></ul></div>";
            $output .= "</section></div>";
            return $output;
        }//18
        protected function _tp_image_editor( $post_id, $msg = false ):void{
            $this->_tp_get_image_editor( $post_id, $msg);
        }//18
        /**
         * @description Streams image in TP_Image_Editor to browser.
         * @param $image
         * @param $mime_type
         * @param $attachment_id
         * @return bool
         */
        protected function _tp_stream_image( $image, $mime_type, $attachment_id ):bool{
            $image = $this->_apply_filters( 'image_editor_save_pre', $image, $attachment_id );
            $_img = null;
            if($image instanceof TP_Image_Editor){
                $_img = $image;
            }
            if ( $this->_init_error( $_img->stream( $mime_type ) ) ) {
                return false;
            }
            return true;
        }//267
        /**
         * @description Saves image to file.
         * @param $filename
         * @param $image
         * @param $mime_type
         * @param $post_id
         * @return mixed
         */
        protected function _tp_save_image_file( $filename, $image, $mime_type, $post_id ){
            $image = $this->_apply_filters( 'image_editor_save_pre', $image, $post_id );
            $_img = null;
            if ( $image instanceof TP_Image_Editor ) {
                $_img = $image;
            }
            $saved = $this->_apply_filters( 'tp_save_image_editor_file', null, $filename, $image, $mime_type, $post_id );
            return $saved ?? $_img->save($filename, $mime_type);
        }//333
        /**
         * @description Image preview ratio. Internal use only.
         * @param $w
         * @param $h
         * @return float|int
         */
        private function __image_get_preview_ratio( $w, $h ){
            $max = max( $w, $h );
            return $max > 400 ? ( 400 / $max ) : 1;
        }//422
        /**
         * @description Crops an image resource. Internal use only.
         * @param $img
         * @param $x
         * @param $y
         * @param $w
         * @param $h
         * @return mixed
         */
        private function __crop_image_resource( $img, $x, $y, $w, $h ){
            $dst = $this->_tp_image_create_true_color( $w, $h );
            if ($this->_is_gd_image($dst) && imagecopy($dst, $img, 0, 0, $x, $y, $w, $h)) {
                imagedestroy( $img );
                $img = $dst;
            }
            return $img;
        }//502
        /**
         * @description Performs group of changes on Editor specified.
         * @param $image
         * @param $changes
         * @return mixed
         */
        protected function _image_edit_apply_changes( $image, $changes ){
            if ( ! is_array( $changes )){return $image;}
            foreach ( $changes as $key => $obj ) {
                if ( isset( $obj->r ) ) {
                    $obj->type  = 'rotate';
                    $obj->angle = $obj->r;
                    unset( $obj->r );
                } elseif ( isset( $obj->f ) ) {
                    $obj->type = 'flip';
                    $obj->axis = $obj->f;
                    unset( $obj->f );
                } elseif ( isset( $obj->c ) ) {
                    $obj->type = 'crop';
                    $obj->sel  = $obj->c;
                    unset( $obj->c );
                }
                $changes[ $key ] = $obj;
            }
            if ( count( $changes ) > 1 ) {
                $filtered = array( $changes[0] );
                for ( $i = 0, $j = 1, $c = count( $changes ); $j < $c; $j++ ) {
                    $combined = false;
                    if ( $filtered[ $i ]->type === $changes[ $j ]->type ) {
                        switch ( $filtered[ $i ]->type ) {
                            case 'rotate':
                                $filtered[ $i ]->angle += $changes[ $j ]->angle;
                                $combined               = true;
                                break;
                            case 'flip':
                                $filtered[ $i ]->axis ^= $changes[ $j ]->axis;
                                $combined              = true;
                                break;
                        }
                    }
                    if ( ! $combined ) {
                        $filtered[ ++$i ] = $changes[ $j ];
                    }
                }
                $changes = $filtered;
                unset( $filtered );
            }
            if ( $image instanceof TP_Image_Editor ) {
                $image = $this->_apply_filters( 'tp_image_editor_before_change', $image, $changes );
            }
            foreach ( $changes as $operation ) {
                switch ( $operation->type ) {
                    case 'rotate':
                        if ((0 !== $operation->angle) && $image instanceof TP_Image_Editor) {
                            $image->rotate( $operation->angle );
                        }
                        break;
                    case 'flip':
                        if ((0 !== $operation->axis) && $image instanceof TP_Image_Editor) {
                            $image->flip( ( $operation->axis & 1 ) !== 0, ( $operation->axis & 2 ) !== 0 );
                        }
                        break;
                    case 'crop':
                        $sel = $operation->sel;
                        if ( $image instanceof TP_Image_Editor ) {
                            $size = $image->get_size();
                            $w    = $size['width'];
                            $h    = $size['height'];
                            $scale = 1 / $this->__image_get_preview_ratio( $w, $h ); // Discard preview scaling.
                            $image->crop( $sel->x * $scale, $sel->y * $scale, $sel->w * $scale, $sel->h * $scale );
                        } else {
                            $scale = 1 / $this->__image_get_preview_ratio( imagesx( $image ), imagesy( $image ) ); // Discard preview scaling.
                            $image = $this->__crop_image_resource( $image, $sel->x * $scale, $sel->y * $scale, $sel->w * $scale, $sel->h * $scale );
                        }
                        break;
                }
            }
            return $image;
        }//524
        /**
         * @description Streams image in post to browser, along with enqueued changes in `$_REQUEST['history']`.
         * @param $post_id
         * @return bool
         */
        protected function _stream_preview_image( $post_id ):bool{
            $post = $this->_get_post( $post_id );
            $this->_tp_raise_memory_limit( 'admin' );
            $img = $this->_tp_get_image_editor( $this->_load_image_to_edit_path( $post_id ) );
            if ( $this->_init_error( $img ) ) { return false;}
            $changes = ! empty( $_REQUEST['history'] ) ? json_decode($this->_tp_unslash($_REQUEST['history']), false) : null;
            if ( $changes ) { $img = $this->_image_edit_apply_changes( $img, $changes );}
            // Scale the image.
            $size = $img->get_size();
            $w    = $size['width'];
            $h    = $size['height'];
            $ratio = $this->__image_get_preview_ratio( $w, $h );
            $w2    = max( 1, $w * $ratio );
            $h2    = max( 1, $h * $ratio );
            if ( $this->_init_error( $img->resize( $w2, $h2 ) ) ) { return false;}
            return $this->_tp_stream_image( $img, $post->post_mime_type, $post_id );
        }//654
        /**
         * @description Restores the metadata for a given attachment.
         * @param $post_id
         * @return \stdClass
         */
        protected function _tp_restore_image( $post_id ):\stdClass{
            $meta             = $this->_tp_get_attachment_metadata( $post_id );
            $file             = $this->_get_attached_file( $post_id );
            $backup_sizes     = $this->_get_post_meta( $post_id, '_tp_attachment_backup_sizes', true );
            $old_backup_sizes = $backup_sizes;
            $restored         = false;
            $msg              = new \stdClass;
            if ( ! is_array( $backup_sizes ) ) {
                $msg->error = $this->__( 'Cannot load image metadata.' );
                return $msg;
            }
            $parts         = pathinfo( $file );
            $suffix        = time() . random_int( 100, 999 );
            $default_sizes = $this->_get_intermediate_image_sizes();
            if ( isset( $backup_sizes['full-orig'] ) && is_array( $backup_sizes['full-orig'] ) ) {
                $data = $backup_sizes['full-orig'];
                if ( $parts['basename'] !== $data['file'] ) {
                    if ( defined( 'IMAGE_EDIT_OVERWRITE' ) && IMAGE_EDIT_OVERWRITE ) {
                        if ( preg_match( '/-e\d{13}\./', $parts['basename'] ) ) {
                            $this->_tp_delete_file( $file );
                        }
                    } elseif ( isset( $meta['width'], $meta['height'] ) ) {
                        $backup_sizes[ "full-$suffix" ] = ['width' => $meta['width'],'height' => $meta['height'],'file' => $parts['basename'],];
                    }
                }
                $restored_file = $this->_path_join( $parts['dirname'], $data['file'] );
                $restored      = $this->_update_attached_file( $post_id, $restored_file );
                $meta['file']   = $this->_tp_relative_upload_path( $restored_file );
                $meta['width']  = $data['width'];
                $meta['height'] = $data['height'];
            }
            foreach ( $default_sizes as $default_size ) {
                if ( isset( $backup_sizes[ "$default_size-orig" ] ) ) {
                    $data = $backup_sizes[ "$default_size-orig" ];
                    if ( isset( $meta['sizes'][ $default_size ] ) && $meta['sizes'][ $default_size ]['file'] !== $data['file'] ) {
                        if ( defined( 'IMAGE_EDIT_OVERWRITE' ) && IMAGE_EDIT_OVERWRITE ) {
                            if ( preg_match( '/-e\d{13}-/', $meta['sizes'][ $default_size ]['file'] ) ) {
                                $delete_file = $this->_path_join( $parts['dirname'], $meta['sizes'][ $default_size ]['file'] );
                                $this->_tp_delete_file( $delete_file );
                            }
                        } else { $backup_sizes[ "$default_size-{$suffix}" ] = $meta['sizes'][ $default_size ];}
                    }
                    $meta['sizes'][ $default_size ] = $data;
                } else { unset( $meta['sizes'][ $default_size ] );}
            }
            if ( ! $this->_tp_update_attachment_metadata( $post_id, $meta ) ||
                ( $old_backup_sizes !== $backup_sizes && ! $this->_update_post_meta( $post_id, '_tp_attachment_backup_sizes', $backup_sizes ) ) ) {
                $msg->error = $this->__( 'Cannot save image metadata.' );
                return $msg;
            }
            if ( ! $restored ) {$msg->error = $this->__( 'Image metadata is inconsistent.' );
            } else {$msg->msg = $this->__( 'Image restored successfully.' );}
            return $msg;
        }//694
        /**
         * @description Saves image to post, along with enqueued changes
         * @param $post_id
         * @return \stdClass
         */
        protected function _tp_save_image( $post_id ):\stdClass{
            $_tp_additional_image_sizes = $this->_tp_get_additional_image_sizes();
            $return  = new \stdClass;
            $success = false;
            $delete  = false;
            $scaled  = false;
            $nocrop  = false;
            $post    = $this->_get_post( $post_id );
            $_img = $this->_tp_get_image_editor( $this->_load_image_to_edit_path( $post_id, 'full' ) );
            $img = null;
            if($_img instanceof TP_Image_Editor){ $img = $_img;}
            if ( $this->_init_error( $img ) ) {
                $return->error = $this->_esc_js( $this->__( 'Unable to create new image.' ) );
                return $return;
            }
            $fwidth  = ! empty( $_REQUEST['fwidth'] ) ? (int) $_REQUEST['fwidth'] : 0;
            $fheight = ! empty( $_REQUEST['fheight'] ) ? (int) $_REQUEST['fheight'] : 0;
            $target  = ! empty( $_REQUEST['target'] ) ? preg_replace( '/[^a-z0-9_-]+/i', '', $_REQUEST['target'] ) : '';
            $scale   = ! empty( $_REQUEST['do'] ) && 'scale' === $_REQUEST['do'];
            if ( $scale && $fwidth > 0 && $fheight > 0 ) {
                $size = $img->get_size();
                $sX   = $size['width'];
                $sY   = $size['height'];
                $diff = round( $sX / $sY, 2 ) - round( $fwidth / $fheight, 2 );
                // Scale the full size image.
                if (-0.1 < $diff && $diff < 0.1 && $img->resize($fwidth, $fheight)) {
                    $scaled = true;
                }
                if ( ! $scaled ) {
                    $return->error = $this->_esc_js( $this->__( 'Error while saving the scaled image. Please reload the page and try again.' ) );
                    return $return;
                }
            } elseif ( ! empty( $_REQUEST['history'] ) ) {
                $changes = json_decode($this->_tp_unslash($_REQUEST['history']), false);
                if ( $changes ) {
                    $img = $this->_image_edit_apply_changes( $img, $changes );
                }
            } else {
                $return->error = $this->_esc_js( $this->__( 'Nothing to save, the image has not changed.' ) );
                return $return;
            }
            $meta         = $this->_tp_get_attachment_metadata( $post_id );
            $backup_sizes = $this->_get_post_meta( $post->ID, '_tp_attachment_backup_sizes', true );
            if ( ! is_array( $meta ) ) {
                $return->error = $this->_esc_js( $this->__( 'Image data does not exist. Please re-upload the image.' ) );
                return $return;
            }
            if ( ! is_array( $backup_sizes ) ) { $backup_sizes = [];}
            $path = $this->_get_attached_file( $post_id );
            $basename = pathinfo( $path, PATHINFO_BASENAME );
            $dirname  = pathinfo( $path, PATHINFO_DIRNAME );
            $ext      = pathinfo( $path, PATHINFO_EXTENSION );
            $filename = pathinfo( $path, PATHINFO_FILENAME );
            $suffix   = time() . random_int( 100, 999 );
            $new_path = null;
            if ( defined( 'IMAGE_EDIT_OVERWRITE' ) && IMAGE_EDIT_OVERWRITE &&
                isset( $backup_sizes['full-orig'] ) && $backup_sizes['full-orig']['file'] !== $basename ) {
                if ( 'thumbnail' === $target ) { $new_path = "{$dirname}/{$filename}-temp.{$ext}";
                } else {$new_path = $path;}
            } else {
                while ( true ) {
                    $filename     = preg_replace( '/-e(\d+)$/', '', $filename );
                    $filename    .= "-e{$suffix}";
                    $new_filename = "{$filename}.{$ext}";
                    $new_path     = "{$dirname}/$new_filename";
                    if ( file_exists( $new_path ) ) {
                        $suffix++;
                    } else { break;}
                }
            }
            if ( ! $this->_tp_save_image_file( $new_path, $img, $post->post_mime_type, $post_id ) ) {
                $return->error = $this->_esc_js( $this->__( 'Unable to save the image.' ) );
                return $return;
            }
            if ( 'nothumb' === $target || 'all' === $target || 'full' === $target || $scaled ) {
                $tag = false;
                if ( isset( $backup_sizes['full-orig'] ) ) {
                    if ( ( ! defined( 'IMAGE_EDIT_OVERWRITE' ) || ! IMAGE_EDIT_OVERWRITE ) && $backup_sizes['full-orig']['file'] !== $basename ) {
                        $tag = "full-$suffix";
                    }
                } else { $tag = 'full-orig'; }
                if ( $tag ) {
                    $backup_sizes[ $tag ] = ['width' => $meta['width'], 'height' => $meta['height'],'file' => $basename,];
                }
                $success = ( $path === $new_path ) || $this->_update_attached_file( $post_id, $new_path );
                $meta['file'] = $this->_tp_relative_upload_path( $new_path );
                $size           = $img->get_size();
                $meta['width']  = $size['width'];
                $meta['height'] = $size['height'];
                if ( $success && ( 'nothumb' === $target || 'all' === $target ) ) {
                    $sizes = $this->_get_intermediate_image_sizes();
                    if ( 'nothumb' === $target ) { $sizes = array_diff( $sizes, array( 'thumbnail' ) );}
                }
                $return->fw = $meta['width'];
                $return->fh = $meta['height'];
            } elseif ( 'thumbnail' === $target ) {
                $sizes   = array( 'thumbnail' );
                $success = true;
                $delete  = true;
                $nocrop  = true;
            }
            if ( defined( 'IMAGE_EDIT_OVERWRITE' ) && IMAGE_EDIT_OVERWRITE && ! empty( $meta['sizes'] ) ) {
                foreach ( $meta['sizes'] as $size ) {
                    if ( ! empty( $size['file'] ) && preg_match( '/-e\d{13}-/', $size['file'] ) ) {
                        $delete_file = $this->_path_join( $dirname, $size['file'] );
                        $this->_tp_delete_file( $delete_file );
                    }
                }
            }
            if ( isset( $sizes ) ) {
                $_sizes = array();
                foreach ( $sizes as $size ) {
                    $tag = false;
                    if ( isset( $meta['sizes'][ $size ] ) ) {
                        if ( isset( $backup_sizes[ "$size-orig" ] ) ) {
                            if ( ( ! defined( 'IMAGE_EDIT_OVERWRITE' ) || ! IMAGE_EDIT_OVERWRITE ) && $backup_sizes[ "$size-orig" ]['file'] !== $meta['sizes'][ $size ]['file'] ) {
                                $tag = "$size-$suffix";
                            }
                        } else { $tag = "$size-orig";}
                        if ( $tag ) { $backup_sizes[ $tag ] = $meta['sizes'][ $size ];}
                    }
                    if ( isset( $_tp_additional_image_sizes[ $size ] ) ) {
                        $width  = (int) $_tp_additional_image_sizes[ $size ]['width'];
                        $height = (int) $_tp_additional_image_sizes[ $size ]['height'];
                        $crop   = ( $nocrop ) ? false : $_tp_additional_image_sizes[ $size ]['crop'];
                    } else {
                        $height = $this->_get_option( "{$size}_size_h" );
                        $width  = $this->_get_option( "{$size}_size_w" );
                        $crop   = ( $nocrop ) ? false : $this->_get_option( "{$size}_crop" );
                    }
                    $_sizes[ $size ] = ['width' => $width,'height' => $height,'crop' => $crop,];
                }
                $meta['sizes'] = array_merge( $meta['sizes'], $img->multi_resize( $_sizes ) );
            }
            unset( $img );
            if ( $success ) {
                $this->_tp_update_attachment_metadata( $post_id, $meta );
                $this->_update_post_meta( $post_id, '_tp_attachment_backup_sizes', $backup_sizes );
                if ( 'thumbnail' === $target || 'all' === $target || 'full' === $target ) {
                    // Check if it's an image edit from attachment edit screen.
                    if ( ! empty( $_REQUEST['context'] ) && 'edit-attachment' === $_REQUEST['context'] ) {
                        $thumb_url         = $this->_tp_get_attachment_image_src( $post_id, array( 900, 600 ), true );
                        $return->thumbnail = $thumb_url[0];
                    } else {
                        $file_url = $this->_tp_get_attachment_url( $post_id );
                        if ( ! empty( $meta['sizes']['thumbnail'] ) ) {
                            $thumb             = $meta['sizes']['thumbnail'];
                            $return->thumbnail = $this->_path_join( dirname( $file_url ), $thumb['file'] );
                        } else { $return->thumbnail = "$file_url?w=128&h=128";}
                    }
                }
            } else { $delete = true;}
            if ( $delete ) { $this->_tp_delete_file( $new_path );}
            $return->msg = $this->_esc_js( $this->__( 'Image saved' ) );
            return $return;
        }//785
    }
}else die;