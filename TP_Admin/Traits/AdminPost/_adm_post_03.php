<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-5-2022
 * Time: 10:08
 */
namespace TP_Admin\Traits\AdminPost;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_post_03{
        use _init_error;
        /**
         * @description Returns a sample permalink based on the post name.
         * @param $id
         * @param null $title
         * @param null $name
         * @return array
         */
        protected function _get_sample_permalink( $id, $title = null, $name = null ):array{
            $post = $this->_get_post( $id );
            if ( ! $post ) { return array( '', '' );}
            $ptype = $this->_get_post_type_object( $post->post_type );
            $original_status = $post->post_status;
            $original_date   = $post->post_date;
            $original_name   = $post->post_name;
            if ( in_array( $post->post_status, array( 'draft', 'pending', 'future' ), true ) ) {
                $post->post_status = 'publish';
                $post->post_name   = $this->_sanitize_title( $post->post_name ?: $post->post_title, $post->ID );
            }
            if ( ! is_null( $name ) ) { $post->post_name = $this->_sanitize_title( $name ?: $title, $post->ID );}
            $post->post_name = $this->_tp_unique_post_slug( $post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent );
            $post->filter = 'sample';
            $permalink = $this->_get_permalink( $post, true );
            $permalink = str_replace( "%$post->post_type%", '%pagename%', $permalink );
            if ( $ptype->hierarchical ) {
                $uri = $this->_get_page_uri( $post );
                if ( $uri ) {
                    $uri = $this->_untrailingslashit( $uri );
                    $uri = strrev( strstr( strrev( $uri ), '/' ) );
                    $uri = $this->_untrailingslashit( $uri );
                }
                $uri = $this->_apply_filters( 'editable_slug', $uri, $post );
                if ( ! empty( $uri ) ) {  $uri .= '/';}
                $permalink = str_replace( '%pagename%', "{$uri}%pagename%", $permalink );
            }
            $permalink         = array( $permalink, $this->_apply_filters( 'editable_slug', $post->post_name, $post ) );
            $post->post_status = $original_status;
            $post->post_date   = $original_date;
            $post->post_name   = $original_name;
            unset( $post->filter );
            /**
             * @param TP_Post $post    Post object.
             */
            return $this->_apply_filters( 'get_sample_permalink', $permalink, $post->ID, $title, $name, $post );
        }//1383
        /**
         * @description Returns the HTML of the sample permalink slug editor.
         * @param $id
         * @param null $new_title
         * @param null $new_slug
         * @return string
         */
        protected function _get_sample_permalink_html( $id, $new_title = null, $new_slug = null ):string{
            $output  = "";
            $post = $this->_get_post( $id );
            if ( ! $post ) { return '';}
            @list($permalink, $post_name) = $this->_get_sample_permalink( $post->ID, $new_title, $new_slug );
            $view_link      = false;
            $preview_target = '';
            if ( $this->_current_user_can( 'read_post', $post->ID ) ) {
                if ( 'draft' === $post->post_status || empty( $post->post_name ) ) {
                    $view_link      = $this->_get_preview_post_link( $post );
                    $preview_target = " target='tp-preview-{$post->ID}'";
                } else if ( 'publish' === $post->post_status || 'attachment' === $post->post_type ) {
                    $view_link = $this->_get_permalink( $post );
                } else {
                    $view_link = str_replace( array( '%pagename%', '%postname%' ), $post->post_name, $permalink );
                }
            }
            if ( false === strpos( $permalink, '%postname%' ) && false === strpos( $permalink, '%pagename%' ) ) {
                $output .= "<strong>{$this->__('Permalink:')}</strong>\n";
                if ( false !== $view_link ) {
                    $display_link = urldecode( $view_link );
                    $output .= "<a href='{$this->_esc_url( $view_link )}' id='sample_permalink' $preview_target>{$this->_esc_html( $display_link )}</a>\n";
                }else{$output .= "<span id='sample_permalink'>$permalink</span>\n";}
                if ( ! $this->_get_option( 'permalink_structure' ) && $this->_current_user_can( 'manage_options' ) && ! ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_on_front' ) === $id )) {
                    $output .= "<span id='change_permalinks'><a href='options_permalink.php' class='button button-small' target='_blank'>{$this->__( 'Change Permalinks' )}</a></span>\n";
                }
            }else{
                if ( mb_strlen( $post_name ) > 34 ) {
                    $post_name_abridged = mb_substr( $post_name, 0, 16 ) . '&hellip;' . mb_substr( $post_name, -16 );
                } else { $post_name_abridged = $post_name;}
                $post_name_html = "<span id='editable_post_name'>{$this->_esc_html( $post_name_abridged )}</span>";
                $display_link   = str_replace( array( '%pagename%', '%postname%' ), $post_name_html, $this->_esc_html( urldecode( $permalink ) ) );
                $output .= "<strong>{$this->__('Permalink:')}</strong>\n";
                $output .= "<span id='sample_permalink'><a href='{$this->_esc_url( $view_link )}' $preview_target>$display_link</a></span>\n";
                $output .= '&lrm;';
                $output .= "<dd id='edit_slug_buttons'><button class='edit-slug button button-small hide-if-no-js' id='' type='button' aria-label='{$this->__( 'Edit permalink' )}'>{$this->__( 'Edit' )}</button></dd>\n";
                $output .= "<dt id='editable_post_name_full'>{$this->_esc_html( $post_name )}</dt>\n";
            }
            return $this->_apply_filters( 'get_sample_permalink_html', $output, $post->ID, $new_title, $new_slug, $post );
        }//1469
        /**
         * @description Returns HTML for the post thumbnail meta box.
         * @param null $thumbnail_id
         * @param null $post
         * @return mixed
         */
        protected function _tp_post_thumbnail_html( $thumbnail_id = null, $post = null ){
            $_tp_additional_image_sizes = $this->_tp_get_additional_image_sizes();
            $output  = "";
            $post = $this->_get_post( $post );
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            $set_thumbnail_link = "<p class='hide-if-no-js'><a href='%s' id='set-post-thumbnail' %s class='thickbox'>%s</a></p>";
            $upload_iframe_src  = $this->_get_upload_iframe_src( 'image', $post->ID );
            $output .= sprintf($set_thumbnail_link,$this->_esc_url( $upload_iframe_src ),'',$this->_esc_html( $post_type_object->labels->set_featured_image ));
            if ( $thumbnail_id && $this->_get_post( $thumbnail_id ) ) {
                $size = isset( $_tp_additional_image_sizes['post-thumbnail'] ) ? 'post-thumbnail' : array( 266, 266 );
                $size = $this->_apply_filters( 'admin_post_thumbnail_size', $size, $thumbnail_id, $post );
                $thumbnail_html = $this->_tp_get_attachment_image( $thumbnail_id, $size );
                if ( ! empty( $thumbnail_html ) ){
                    $output .= sprintf($set_thumbnail_link,$this->_esc_url( $upload_iframe_src )," aria-describedby='set-post-thumbnail-desc'",$thumbnail_html);
                    $output .= "<p class='hide-if-no-js howto' id='set_post_thumbnail_desc'>{$this->__('Click the image to edit or update')}</p>";
                    $output .= "<p class='hide-if-no-js'><a href='#' id='remove_post_thumbnail'>{$this->_esc_html( $post_type_object->labels->remove_featured_image )}</a></p>";
                }
            }
            $output .= "<input name='_thumbnail_id' id='_thumbnail_id' type='hidden' value='{$this->_esc_attr( $thumbnail_id ?: '-1')}'/>";
            return $this->_apply_filters( 'admin_post_thumbnail_html', $output, $post->ID, $thumbnail_id );
        }//1555
        /**
         * @description Determines whether the post is currently being edited by another user.
         * @param $post_id
         * @return bool
         */
        protected function _tp_check_post_lock( $post_id ):bool{
            $post = $this->_get_post( $post_id );
            if (!$post){ return false;}
            $lock = $this->_get_post_meta( $post->ID, '_edit_lock', true );
            if (!$lock ){ return false;}
            $lock = explode( ':', $lock );
            $time = $lock[0];
            $user = isset( $lock[1] ) ?: $this->_get_post_meta( $post->ID, '_edit_last', true );
            if ( ! $this->_get_user_data( $user)){ return false;}
            $time_window = $this->_apply_filters( 'tp_check_post_lock_window', 150 );
            if ( $time && $time > time() - $time_window && $this->_get_current_user_id() !== $user ) { return $user;}
            return false;
        }//1628
        /**
         * @description Marks the post as currently being edited by the current user.
         * @param $post_id
         * @return array|bool
         */
        protected function _tp_set_post_lock( $post_id ){
            $post = $this->_get_post( $post_id );
            if ( ! $post ) { return false;}
            $user_id = $this->_get_current_user_id();
            if ( 0 === $user_id ) { return false;}
            $now  = time();
            $lock = "$now:$user_id";
            $this->_update_post_meta( $post->ID, '_edit_lock', $lock );
            return array( $now, $user_id );
        }//1666
        /**
         * @description Outputs the HTML for the notice to say that someone else is editing or has taken over editing of this post.
         * @return bool|string
         */
        protected function _get_admin_notice_post_locked(){
            $post = $this->_get_post();
            if ( ! $post ) {return false;}
            $user    = null;
            $user_id = $this->_tp_check_post_lock( $post->ID );
            if ( $user_id ) { $user = $this->_get_user_data( $user_id ); }
            if ( $user ) {
                if ( ! $this->_apply_filters( 'show_post_locked_dialog', true, $post, $user ) ) { return false;}
                $locked = true;
            } else {$locked = false;}
            $sendback = $this->_tp_get_referer();
            if ( $locked && $sendback && false === strpos( $sendback, 'post.php' ) && false === strpos( $sendback, 'post-new.php' ) ) {
                $sendback_text = $this->__( 'Go back' );
            } else {
                $sendback = $this->_admin_url( 'edit.php' );
                if ( 'post' !== $post->post_type ) { $sendback = $this->_add_query_arg( 'post_type', $post->post_type, $sendback );}
                $sendback_text = $this->_get_post_type_object( $post->post_type )->labels->all_items;
            }
            $hidden = $locked ? '' : ' hidden';
            $output  = "<div id='post_lock_dialog' class='notification-dialog-wrap $hidden'>";
            $output .= "<div class='notification-dialog-background'></div>";
            $output .= "<div class='notification-dialog'>";
            if ( $locked ) {
                $query_args = [];
                if ( $this->_get_post_type_object( $post->post_type )->public ) {
                    if ( 'publish' === $post->post_status || $user->ID !== $post->post_author ) {
                        $nonce                       = $this->_tp_create_nonce( 'post_preview_' . $post->ID );
                        $query_args['preview_id']    = $post->ID;
                        $query_args['preview_nonce'] = $nonce;
                    }
                }
                $preview_link = $this->_get_preview_post_link( $post->ID, $query_args );
                $override = $this->_apply_filters( 'override_post_lock', true, $post, $user );
                $tab_last = $override ? '' : ' tp-tab-last';
                $output .= "<div class='post-locked-message'>";
                $output .= "<div class='post-locked-avatar'>{$this->_get_avatar( $user->ID, 64 )}</div>";
                $output .= "<p class='currently-editing tp-tab-first' tabindex='0'>";
                if ( $override ) {
                    $output .= sprintf($this->__('%s is currently editing this post. Do you want to take over?'),$this->_esc_html($user->display_name));
                }else{ $output .= sprintf($this->__('%s is currently editing this post.'),$this->_esc_html($user->display_name));}
                $output .= "</p>";
                $output .= $this->_do_action( 'post_locked_dialog', $post, $user );
                $output .= "<p><a href='{$this->_esc_url($sendback)}' class='button'>$sendback_text</a>";
                if ( $preview_link ){
                    $output .= "<a href='{$this->_esc_url($preview_link)}' class='button $tab_last'>{$this->__('Preview')}</a>";
                }
                if ( $override ){
                    $_override_url = $this->_add_query_arg( 'get-post-lock', '1', $this->_tp_nonce_url( $this->_get_edit_post_link( $post->ID, 'url' ), 'lock-post_' . $post->ID ) );
                    $output .= "<a href='{$this->_esc_url($_override_url)}' class='button button-primary tp-tab-last'>{$this->__('Take Over')}</a>";
                }
                $output .= "</p></div>";
            }else{
                $output .= "<div class='post-taken-over'>";
                $output .= "<div class='post-locked-avatar'></div>";
                $output .= "<ul><li class='tp-tab-first' tabindex='0'>";
                $output .= "<dt><span class='currently-editing'></span></dt>";
                $output .= "<dt><span class='locked-saving hidden'><img src='{$this->_esc_url($this->_admin_url( 'images/spinner-2x.gif'))}' width='16' height='16' alt=''/>{$this->__('Saving revision&hellip;')}</span></dt>";
                $output .= "<dt><span class='locked-saving hidden'>{$this->__('Your latest changes were saved as a revision.')}</span></dt>";
                $output .= "</li><li>";
                $output .= $this->_do_action( 'post_lock_lost_dialog', $post );
                $output .= "</li><li>";
                $output .= "<dd><a href='{$this->_esc_url($sendback)}' class='button button-primary tp-tab-last'>$sendback_text</a></dd>";
                $output .= "</ul></div>";
            }
            $output .= "</div></div>";//notification-dialog //post_lock_dialog
            return $output;
        }//1690
        protected function _admin_notice_post_locked():void{
            echo $this->_get_admin_notice_post_locked();
        }//1690
        /**
         * @description Creates autosave data for the specified post from `$_POST` data.
         * @param $post_data
         * @return int
         */
        protected function _tp_create_post_autosave( $post_data ):int{
            if ( is_numeric( $post_data ) ) {
                $post_id   = $post_data;
                $post_data = $_POST;
            } else { $post_id = (int) $post_data['post_ID'];}
            $post_data = $this->_tp_translate_postdata( true, $post_data );
            if ( $this->_init_error( $post_data ) ) { return $post_data;}
            $post_data = $this->_tp_get_allowed_postdata( $post_data );
            $post_author = $this->_get_current_user_id();
            $old_autosave = $this->_tp_get_post_autosave( $post_id, $post_author );
            if ( $old_autosave ) {
                $new_autosave                = $this->_tp_post_revision_data( $post_data, true );
                $new_autosave['ID']          = $old_autosave->ID;
                $new_autosave['post_author'] = $post_author;
                $post = $this->_get_post( $post_id );
                $autosave_is_different = false;
                foreach ( array_intersect( array_keys( $new_autosave ), array_keys( $this->_tp_post_revision_fields( $post ) ) ) as $field ) {
                    if ( $this->_normalize_whitespace( $new_autosave[ $field ] ) !== $this->_normalize_whitespace( $post->$field ) ) {
                        $autosave_is_different = true;
                        break;
                    }
                }
                if ( ! $autosave_is_different ) {
                    $this->_tp_delete_post_revision( $old_autosave->ID );
                    return 0;
                }
                $this->_do_action( 'tp_creating_autosave', $new_autosave );
                return $this->_tp_update_post( $new_autosave );
            }
            $post_data = $this->_tp_unslash( $post_data );
            return $this->_tp_put_post_revision( $post_data, true );
        }//1856
        /**
         * @description Saves a draft or manually autosaves for the purpose of showing a post preview.
         * @return mixed
         */
        protected function _post_preview(){
            $post_ID     = (int) $_POST['post_ID'];
            $_POST['ID'] = $post_ID;
            $post = $this->_get_post( $post_ID );
            if ( ! $post ) { $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit this post.' ) );}
            if ( ! $this->_current_user_can( 'edit_post', $post->ID ) ) { $this->_tp_die( $this->__( 'Sorry, you are not allowed to edit this post.' ) ); }
            $is_autosave = false;
            if ( ( 'draft' === $post->post_status || 'auto-draft' === $post->post_status ) &&! $this->_tp_check_post_lock( $post->ID ) && $this->_get_current_user_id() === $post->post_author
            ) { $saved_post_id = $this->_edit_post();}
            else {
                $is_autosave = true;
                if ( isset( $_POST['post_status'] ) && 'auto-draft' === $_POST['post_status'] ) { $_POST['post_status'] = 'draft';}
                $saved_post_id = $this->_tp_create_post_autosave( $post->ID );
            }
            if ($saved_post_id instanceof TP_Error && $this->_init_error( $saved_post_id ) ) {
                $this->_tp_die( $saved_post_id->get_error_message() );
            }
            $query_args = [];
            if ( $is_autosave && $saved_post_id ) {
                $query_args['preview_id']    = $post->ID;
                $query_args['preview_nonce'] = $this->_tp_create_nonce( 'post_preview_' . $post->ID );
                if ( isset( $_POST['post_format'] ) ) {
                    $query_args['post_format'] = empty( $_POST['post_format'] ) ? 'standard' : $this->_sanitize_key( $_POST['post_format'] );
                }
                if ( isset( $_POST['_thumbnail_id'] ) ) {
                    $query_args['_thumbnail_id'] = ( (int) $_POST['_thumbnail_id'] <= 0 ) ? '-1' : (int) $_POST['_thumbnail_id'];
                }
            }
            return $this->_get_preview_post_link( $post, $query_args );
        }//1921
        /**
         * @description Saves a post submitted with XHR.
         * @param $post_data
         * @return int|TP_Error
         */
        protected function _tp_autosave( $post_data ){
            $post_id              = (int) $post_data['post_id'];
            $post_data['ID']      = $post_id;
            $post_data['post_ID'] = $post_id;
            if ( false === $this->_tp_verify_nonce( $post_data['_tp_nonce'], 'update-post_' . $post_id ) ) {
                return new TP_Error( 'invalid_nonce', $this->__( 'Error while saving.' ) );
            }
            $post = $this->_get_post( $post_id );
            if ( ! $this->_current_user_can( 'edit_post', $post->ID ) ) {
                return new TP_Error( 'edit_posts', $this->__( 'Sorry, you are not allowed to edit this item.' ) );
            }
            if ( 'auto-draft' === $post->post_status ) { $post_data['post_status'] = 'draft';}
            if ( 'page' !== $post_data['post_type'] && ! empty( $post_data['catslist'] ) ) {
                $post_data['post_category'] = explode( ',', $post_data['catslist'] );}
            if (( 'auto-draft' === $post->post_status || 'draft' === $post->post_status ) && ! $this->_tp_check_post_lock( $post->ID )  &&  $this->_get_current_user_id() === $post->post_author
            ) { return $this->_edit_post( $this->_tp_slash( $post_data ) );}
            return $this->_tp_create_post_autosave( $this->_tp_slash( $post_data ) );
        }//1984
        /**
         * @description Redirects to previous page.
         * @param string $post_id
         */
        protected function _redirect_post( $post_id = '' ):void{
            if ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) {
                $status = $this->_get_post_status( $post_id );
                if ( isset( $_POST['publish'] ) ) {
                    switch ( $status ) {
                        case 'pending':
                            $message = 8;
                            break;
                        case 'future':
                            $message = 9;
                            break;
                        default:
                            $message = 6;
                    }
                } else { $message = 'draft' === $status ? 10 : 1; }
                $location = $this->_add_query_arg( 'message', $message, $this->_get_edit_post_link( $post_id, 'url' ) );
            } elseif ( isset( $_POST['add_meta'] ) && $_POST['add_meta'] ) {
                $location = $this->_add_query_arg( 'message', 2, $this->_tp_get_referer() );
                $location = explode( '#', $location );
                $location = $location[0] . '#postcustom';
            } elseif ( isset( $_POST['delete_meta'] ) && $_POST['delete_meta'] ) {
                $location = $this->_add_query_arg( 'message', 3, $this->_tp_get_referer() );
                $location = explode( '#', $location );
                $location = $location[0] . '#postcustom';
            } else { $location = $this->_add_query_arg( 'message', 4, $this->_get_edit_post_link( $post_id, 'url' ) );}
            $this->_tp_redirect( $this->_apply_filters( 'redirect_post_location', $location, $post_id ) );
            exit;
        }//2031
    }
}else die;