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
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _adm_media_04{
        use _init_error,_init_db,_init_queries,_init_locale;
        /**
         * @description Outputs the legacy media upload header.
         * @return string
         */
        protected function _get_media_upload_header():string{
            $post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
            ob_start();
            ?>
            <script id="script_media_upload_header">const post_id ='<?php echo $post_id ?>';   console.log('media_upload_header todo', post_id);</script>
            <?php
            $output_header  = ob_get_clean();
            if ( empty( $_GET['chromeless'] ) ){
                $output_header .= "<div id='media-upload-header'>";
                $output_header .= $this->_get_media_upload_tabs();
                $output_header .= "</div>";
            }
            return $output_header;
        }//2070
        /**
         * @description Outputs the legacy media upload form.
         * @param null $errors
         * @return string
         */
        protected function _get_media_upload_form( $errors = null ):string{
            $output  = "";
            if ( !$this->_device_can_upload()){
                $output = "<p>";
                $output .= sprintf($this->__("The web browser on your device cannot be used to upload files. You may be able to use the <a href='%s'>native app for your device</a> instead."),'https://aadswebdesign.nl/');
                $output .= "</p>";
                return $output;
            }
            $upload_action_url = $this->_admin_url( 'async_upload.php' );
            $post_id  = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
            $_type = $this->tp_type ?? '';
            $_tab = $this->tp_tab ?? '';
            $post_params = ['post_id'  => $post_id,'_tp_nonce' => $this->_tp_create_nonce( 'media_form' ),
                'type' => $_type,'tab' => $_tab,'short' => '1',];
            $post_params = $this->_apply_filters( 'upload_post_params', $post_params );
            $max_upload_size = $this->_tp_max_upload_size();
            if ( ! $max_upload_size ) {
                $max_upload_size = 0;
            }
            $plupload_init = ['browse_button' => 'plupload-browse-button','container' => 'plupload-upload-ui',
                'drop_element' => 'drag-drop-area','file_data_name' => 'async-upload','url' => $upload_action_url,
                'filters' => ['max_file_size' => $max_upload_size . 'b'], 'multipart_params' => $post_params,];
            if ( $this->_tp_is_mobile() && strpos( $_SERVER['HTTP_USER_AGENT'], 'OS 7_' ) !== false && strpos( $_SERVER['HTTP_USER_AGENT'], 'like Mac OS X' ) !== false ){
                $plupload_init['multi_selection'] = false;
            }
            if ( ! $this->_tp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
                $plupload_init['webp_upload_error'] = true;
            }
            $plupload_init = $this->_apply_filters( 'plupload_init', $plupload_init );
            $output .= "<div class='media-upload-notice'>";
            if(isset($errors['upload_notice'])){ $output .= $errors['upload_notice'];}
            $output .= "</div><div class='media-upload-error'>";
            $_errors = $errors['upload_error'];
            $tp_errors = null;
            if($_errors instanceof TP_Error){$tp_errors = $_errors;}
            if(isset($tp_errors)  && $this->_init_error($tp_errors) ){ $output .= $tp_errors->get_error_message();}
            $output .= "</div>";
            if ( $this->_is_multisite() && ! $this->_is_upload_space_available() ){
                $output = $this->_do_action( 'upload_ui_over_quota' );
                return $output; //todo will see
            }
            $output .= $this->_do_action( 'pre-upload-ui' );
            ob_start();
            ?>
            <style>
                * { padding: 0; margin: 0;list-style-type: none;}
            </style>
            <script>
                <?php
                    $large_size_h = $this->_abs_int( $this->_get_option( 'large_size_h' ) );
                    if ( ! $large_size_h ) { $large_size_h = 1024;}
                    $large_size_w = $this->_abs_int( $this->_get_option( 'large_size_w' ) );
                if ( ! $large_size_w ) { $large_size_w = 1024;}
                ?>
                const resize_height = '<?php echo $large_size_h; ?>';
                const resize_width = '<?php echo $large_size_w; ?>';
                const tp_uploader_init = '<?php echo $this->_tp_json_encode( $plupload_init ); ?>';
                console.log('resize_height:', resize_height);
                console.log('resize_width:', resize_width);
                console.log('tp_uploader_init:', tp_uploader_init);
            </script>
            <?php
            $output .= ob_get_clean();
            $output .= "<div id='plupload_upload_ui' class='hide-if-no-js'>";
            $output .= $this->_do_action( 'pre-plupload-upload-ui' );
            $output .= "<div class='drag-drop-area'><div class='drag-drop-inside'>";
            $output .= "<p class='drag-drop-info'>{$this->__('Drop files to upload')}</p>";
            $output .= "<p>{$this->_x('or', 'Uploader: Drop files here - or - Select Files')}</p>";
            $output .= "<dd class='drag-drop-buttons'><input class='button' id='plupload_browse_button' type='button' value='{$this->_esc_attr('Select Files')}'/></dd>";
            $output .= "</div></div>";
            $output .= $this->_do_action( 'post-plupload-upload-ui' );
            $output .= "</div>";
            $output .= "<div id='html_upload_ui' class='hide-if-js'><ul><li>";
            $output .= $this->_do_action( 'pre-html-upload-ui' );
            $output .= "</li><li>";
            $output .= "<dt><label class='screen-reader-text' for='async_upload'>{$this->__('Upload')}</label></dt>";
            $output .= "<dd><input name='async_upload' id='async_upload' type='file'/></dd>";
            $output .= "</li><li>";
            $output .= "<dd>{$this->_get_submit_button( $this->__( 'Upload' ), 'primary', 'html-upload', false )}</dd>";
            $upload_cancel = "try{top.tb_remove();}catch(e){}; return false;";
            $output .= "<dd><a href='#' onclick=' \' . $upload_cancel . \' '>{$this->__('Cancel')}</a></dd>";
            $output .= "</li><li>";
            $output .= $this->_do_action( 'post-html-upload-ui' );
            $output .= "</li></ul></div>";
            $output .= "<p class='max-upload-size'>";
            $output .= sprintf($this->__('Maximum upload file size is: %s.'), $this->_esc_html( $this->_size_format( $max_upload_size ) ));
            $output .= "</p>";
            $output .= $this->_do_action( 'post-upload-ui' );
            return $output;
        }//2094
        /**
         * @description Outputs the legacy media upload form for a given media type.
         * @param string $type
         * @param null $errors
         * @param null $id
         * @return string
         */
        protected function _get_media_upload_type_form( $type = 'file', $errors = null, $id = null ):string{
            $output  = $this->_get_media_upload_header();
            $post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
            $form_action_url = $this->_admin_url( "media_upload.php?type=$type&tab=type&post_id=$post_id" );
            $form_action_url = $this->_apply_filters( 'media_upload_form_url', $form_action_url, $type );
            $form_class      = 'media-upload-form type-form validate';
            if ( $this->_get_user_setting( 'uploader' ) ) {$form_class .= ' html-uploader';}
            $output .= "<form class='$form_class' id='{$type}_upload' action='{$this->_esc_url($form_action_url)}' enctype='multipart/form-data' method='post'><ul><li><dd>";
            $output .= $this->_get_submit_button( '', 'hidden', 'save', false )."</dd>";
            $output .= "<input name='post_id' id='post_id' type='hidden' value='{(int) $post_id;}'/>";
            $output .= "</li><li>";
            $output .= "<h3 class='media-title'>{$this->__('Add media files from your computer.')}</h3>";
            $output .= "</li><li>";
            $output .= $this->_get_media_upload_form( $errors);
            $output .= "</li>";
            ob_start();
            ?>
            <script id='media_pre_loaded'>
                (function(){
                    const pre_loaded = document.querySelector('.media-item.pre-loaded');
                    console.log('pre_loaded:', pre_loaded);
                })();
            </script>
            <?php
            $output .= ob_get_clean();
            $output .= "<li><div id='media_items'>";
            if ( $id && $id instanceof TP_Error) {
                $error_output = null;
                if ( ! $this->_init_error( $id ) ) {
                    $error_output .= $this->_get_media_items( $id, $errors );
                }else{
                    $error_output .= "<div id='media_upload_error'>{$this->_esc_html($id->get_error_message())}</div>";
                }
                return $error_output;
            }
            $output .= "</div></li><li>";
            $output .= "<dd class='save-button ml-submit'>";
            $output .= $this->_get_submit_button( $this->__( 'Save all changes' ), '', 'save', false );//todo setting up this method
            $output .= "</dd></li></ul></form>";
            return $output;
        }//2318
        /**
         * @description Outputs the legacy media upload form for external media.
         * @param null $type
         * @param null $errors
         * @param null $id
         * @return string
         */
        protected function _get_media_upload_type_url_form( $type = null, $errors = null, $id = null ):string{
            if ( null === $type ) {$type = 'image';}
            $output  = $this->_get_media_upload_header();
            $post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
            $form_action_url = $this->_admin_url( "media_upload.php?type=$type&tab=type&post_id=$post_id" );
            $form_action_url = $this->_apply_filters( 'media_upload_form_url', $form_action_url, $type );
            $form_class      = 'media-upload-form type-form validate';
            if ( $this->_get_user_setting( 'uploader' ) ) {$form_class .= ' html-uploader';}
            $output .= "<form enctype='multipart/form-data' class='$form_class' id='$type' method='post' action='{$this->_esc_url( $form_action_url )}'><ul><li>";
            $output .= "<input name='post_id' id='post_id' type='hidden' value='{(int) $post_id;}'/>";
            $output .= "</li><li>";
            $output .= "<h3 class='media-title'>{$this->__('Insert media from another website.')}</h3>";
            $output .= "</li>";
            ob_start();
            ?>
            <script id='media_up_loaded'>console.log('media_up_loaded','todo')</script>
            <?php
            $output .= $errors.$id;//todo give this a place!
            $output .= ob_get_clean();
            $output .= "<li><div id='media_items'>";
            $output .= $this->_apply_filters( 'type_url_form_media', $this->_tp_get_media_insert_url_form( $type ) );
            $output .= "</div></li></ul></form>";
            return $output;
        }//2392
        /**
         * @description Adds gallery form to upload iframe
         * @param $errors
         * @return string
         */
        protected function _get_media_upload_gallery_form( $errors ):string{
            $this->tp_redirect_tab = 'gallery';
            $post_id         = (int) $_REQUEST['post_id'];
            $form_action_url = $this->_admin_url( "media_upload.php?type=$this->tp_type&tab=gallery&post_id=$post_id" );
            $form_action_url = $this->_apply_filters( 'media_upload_form_url', $form_action_url, $this->tp_type );
            $form_class      = 'media-upload-form validate';
            if ( $this->_get_user_setting( 'uploader' ) ) {$form_class .= ' html-uploader';}
            $output  = $this->_get_media_upload_header();
            ob_start();
            ?>
            <script id='media_gallery'>console.log('media_gallery:', 'todo')</script>
            <?php
            $output .= ob_get_clean();
            $output .= "<div id='sort_buttons' class='hide-if-no-js'><ul><li>";
            $output .= "<h5>{$this->__('All Tabs:')}</h5>";
            $output .= "<dd><a herf='#' id='show_all' >{$this->__('Show')}</a></dd>";
            $output .= "<dd><a herf='#' id='hide_all' style='display:none;'>{$this->__('Hide')}</a></dd>";
            $output .= "</li><li>";
            $output .= "<h5>{$this->__('Sort Order:')}</h5>";
            $output .= "<dd><a herf='#' id='asc' >{$this->__('Ascending')}</a></dd>";
            $output .= "<dd><a herf='#' id='desc' >{$this->__('Descending')}</a></dd>";
            $output .= "<dd><a herf='#' id='clear' >{$this->_x('Clear', 'verb')}</a></dd>";
            $output .= "</li></ul></div>";
            $output .= "<form enctype='multipart/form-data' class='$form_class' id='gallery_form' action={$this->_esc_url( $form_action_url )}>";
            $output .= "<header class='form-header'><ul><li>";
            $output .= $this->_tp_get_nonce_field( 'media_form' );
            $output .= "</li><h5 class='head'>{$this->__('Media')}</h5><li>";
            $output .= "</li><h5 class='head order'>{$this->__('Order')}</h5><li>";
            $output .= "</li><h5 class='head actions'>{$this->__('Actions')}</h5><li>";
            $output .= "</li></ul></header>";
            $output .= "<div class='content-container'>";
            $output .= "<div class='sub-one media-items'><ul><li>";
            $output .= $this->_add_filter( 'attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2 );
            $output .= "</li><li>";
            $output .= $this->_get_media_items( $post_id, $errors );
            $output .= "</li></ul></div>";
            $output .= "<div class='sub-two'><ul><li><dd class='save-button ml-submit'>";
            $output .= $this->_get_submit_button( $this->__( 'Save all changes' ), 'save_button', 'save', false,['id'=> 'save_all','style' => 'display: none;',] );//todo setting up this method
            $output .= "</dd></li><li>";
            $output .= "<input name='post_id' id='post_id' type='hidden' value='{(int) $post_id}'/>";
            $output .= "<input name='type' type='hidden' value='{$this->_esc_attr($GLOBALS['type'])}'/>";
            $output .= "<input name='tab' type='hidden' value='{$this->_esc_attr($GLOBALS['tab'] )}' />";
            $output .= "</li></ul></div>";
            $output .= "<div id='gallery-settings' class='sub-three'><ul><li>";//todo place this  style='display:none' back
            $output .= "<h5 class='title'>{$this->__('Gallery Settings')}</h5></li><li>";
            $output .= "<dt class='label'><label><span class=''>{$this->__('Link thumbnails to:')}</span></label></dt></li><li>";
            $output .= "</li><li>";
            $output .= "<dd><input name='link_to' id='link_to_file' type='radio' value='file'/></dd>";
            $output .= "<dt><label for='link_to_file' class='radio'>{$this->__('Image File')}</label></dt>";
            $output .= "</li><li>";
            $output .= "<dd><input name='link_to' id='link_to_post' type='radio' value='post' checked/></dd>";
            $output .= "<dt><label for='link_to_post' class='radio'>{$this->__('Attachment Page')}</label></dt>";
            $output .= "</li><li>";
            $output .= "<dt><label><span class=''>{$this->__('Order images by:')}</span></label></dt>";
            $output .= "<dd><select name='orderby' id='orderby'>";
            $output .= "<option value='menu_order' selected>{$this->__('Menu order')}</option>";
            $output .= "<option value='title'>{$this->__('Title')}</option>";
            $output .= "<option value='post_date'>{$this->__('Date/Time')}</option>";
            $output .= "<option value='rand'>{$this->__('Random')}</option>";
            $output .= "</select></dd></li><li>";
            $output .= "<dt><label><span class=''>{$this->__('Order:')}</span></label></dt>";
            $output .= "</li><li>";
            $output .= "<dd><input name='order' id='order_asc' checked type='radio' value='asc'/></dd>";
            $output .= "<dt><label for='order_asc' class='radio'>{$this->__('Ascending')}</label></dt>";
            $output .= "</li><li>";
            $output .= "<dd><input name='order' id='order_desc' type='radio' value='desc'/></dd>";
            $output .= "<dt><label for='order_desc' class='radio'>{$this->__('Descending')}</label></dt>";
            $output .= "</li><li>";
            $output .= "<dt><label><span class=''>{$this->__('Gallery columns:')}</span></label></dt>";
            $output .= "<dd><select name='columns' id='columns'>";
            $output .= "<option value='1'>1</option>";
            $output .= "<option value='2'>2</option>";
            $output .= "<option value='3' selected>3</option>";
            $output .= "<option value='4'>4</option>";
            $output .= "<option value='5'>5</option>";
            $output .= "<option value='6'>6</option>";
            $output .= "<option value='7'>7</option>";
            $output .= "<option value='8'>8</option>";
            $output .= "<option value='9'>9</option>";
            $output .= "</select></li><li>";
            $tp_gallery_update = " onmousedown='tpgallery.update();'";
            $output .= "<dd><input name='insert_gallery' id='insert_gallery' class='button' type='button' $tp_gallery_update value='{$this->_esc_attr('Insert gallery')}'/></dd>";
            $output .= "</li><li>";
            $output .= "<dd><input name='update_gallery' id='update_gallery' class='button' type='button' $tp_gallery_update value='{$this->_esc_attr('Update gallery settings')}'/></dd>";
            $output .= "</li></ul></div></div></form>";
            return $output;
        }//2541
        /**
         * @description Outputs the legacy media upload form for the media library.
         * @param $errors
         * @return string
         */
        protected function _get_media_upload_library_form( $errors ):string{
            $tp_query = $this->_init_query();
            $tpdb = $this->_init_db();
            $tp_locale = $this->_init_locale();
            $num_posts = null; //todo
            $post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
            $form_action_url = $this->_admin_url( "media_upload.php?type=$this->tp_type&tab=library&post_id=$post_id" );
            $form_action_url = $this->_apply_filters( 'media_upload_form_url', $form_action_url, $this->tp_type );
            $form_class      = 'media-upload-form validate';
            $post_mime_types = null;
            if ( $this->_get_user_setting( 'uploader' ) ) {$form_class .= ' html-uploader';}
            $q = $_GET;
            $q['posts_per_page'] = 10;
            $q['paged'] = isset( $q['paged'] ) ? (int) $q['paged'] : 0;
            if ($q['paged'] < 1 ){$q['paged'] = 1;}
            $q['offset'] = ( $q['paged'] - 1 ) * 10;
            if ( $q['offset'] < 1 ) { $q['offset'] = 0; }
            @list($post_mime_types, $avail_post_mime_types) = $this->_tp_edit_attachments_query( $q );
            $output  = $this->_get_media_upload_header();
            $output .= "<form class='form-get' id='filter' method='get'><div class='content-container'><ul><li>";
            $output .= "<input name='type' type='hidden' value='{$this->_esc_attr($this->tp_type)}'/>";
            $output .= "<input name='tab' type='hidden' value='{$this->_esc_attr($this->tp_tab)}'/>";
            $output .= "<input name='post_id' type='hidden' value='{$this->_esc_attr($post_id)}'/>";
            $mime_type = isset( $_GET['post_mime_type'] ) ? $this->_esc_attr( $_GET['post_mime_type'] ) : '';
            $context = isset( $_GET['context'] ) ? $this->_esc_attr( $_GET['context'] ) : '';
            $output .= "<input name='post_mime_type' type='hidden' value='$mime_type'/>";
            $output .= "<input name='context' type='hidden' value='$context'/>";
            $output .= "</li><li id='media_search' class='search-box'>";
            $output .= "<dt><label for='media_search_input' class='screen-reader-text'>{$this->__('Search Media')}:</label></dt>";
            $output .= "<dd><input name='s' id='media_search_input' type='search' value='{$this->_get_search_query()}'/></dd>";
            $output .= "</li><li>";
            $output .= "<dd>{$this->_get_submit_button($this->__('Search Media'),'','',false)}</dd>";
            $type_links = [];
            $_num_posts = (array) $this->_tp_count_attachments() ?: [];
            $matches    = $this->_tp_match_mime_types( array_keys((array) $post_mime_types ), array_keys( $_num_posts ) );
            foreach ( $matches as $_type => $real_s ) {
                foreach ( $real_s as $real ) {
                    if ( isset($num_posts[$_type])){ $num_posts[ $_type ] += $_num_posts[ $real ];}
                    else { $num_posts[ $_type ] = $_num_posts[$real];}
                }
            }
            if ( empty( $_GET['post_mime_type'] ) && ! empty( $num_posts[ $this->tp_type ] ) ) {
                $_GET['post_mime_type'] = $this->tp_type;
                @list($post_mime_types, $avail_post_mime_types) = $this->_tp_edit_attachments_query();
            }
            if ( empty( $_GET['post_mime_type'] ) || 'all' === $_GET['post_mime_type']){ $class = ' class="current"';}
            else { $class = ''; }
            $_query_arg1 = $this->_add_query_arg(['post_mime_type' => 'all', 'paged' => false,'m' => false,]);
            $type_links[] = "<li><a href='{$this->_esc_url($_query_arg1)}' $class>{$this->__('All Types')}</a>";
            $type_links[] .= "";
            $type_links[] .= "";
            $_query_arg2 = $this->_add_query_arg(['post_mime_type' => $mime_type,'paged'=> false,]);
            foreach ((array) $post_mime_types as $mime_type => $label ){
                $class = '';
                if ( ! $this->_tp_match_mime_types( $mime_type, $avail_post_mime_types ) ) { continue;}
                if ( isset( $_GET['post_mime_type'] ) && $this->_tp_match_mime_types( $mime_type, $_GET['post_mime_type'] ) ) {
                    $class = " class='current'";}
                $print_mime_types = sprintf($this->_translate_nooped_plural( $label[2], $num_posts[ $mime_type ] ),
                    "<span id='{$mime_type}_counter'>{$this->_number_format_i18n( $num_posts[ $mime_type ] )}</span>");
                $type_links[] = "<li><a href='{$this->_esc_url($_query_arg2)}' $class>$print_mime_types</a>";
            }
            $output .= implode(' | </li>', $this->_apply_filters( 'media_upload_mime_type_links', $type_links )) . '</li>';
            unset( $type_links );
            $output .= "</li></ul></div>";
            $page_links = $this->_paginate_links(['base' => $this->_add_query_arg( 'paged', '%#%' ),
                'format' => '','prev_text' => $this->__( '&laquo;' ),'next_text' => $this->__( '&raquo;' ),
                'total' => ceil( $tp_query->found_posts / 10 ),'current' => $q['paged'],]);
            $arc_query = TP_SELECT ." DISTINCT YEAR(post_date) AS y_year, MONTH(post_date) AS m_month FROM $tpdb->posts WHERE post_type = 'attachment' ORDER BY post_date DESC";
            $arc_result = $tpdb->get_results( $arc_query );
            $month_count    = count( $arc_result );
            $selected_month = $_GET['m'] ?? '';
            $output .= "<div class='table-nav'><div class='block left'><ul><li>";
            if ( $month_count && ! ( 1 === $month_count && 0 === $arc_result[0]->m_month ) ) {
                $output .= "<select name='m'>";
                $output .= "<option {$this->_get_selected( $selected_month, 0 )} value=''>{$this->__('All dates')}</option>";
                foreach ( $arc_result as $arc_row ) {
                    if ( 0 === $arc_row->y_year ){ continue;}
                    $arc_row->m_month = $this->_zero_ise( $arc_row->m_month, 2 );
                    if ( $arc_row->y_year . $arc_row->m_month === $selected_month ) {
                        $default = ' selected';
                    } else { $default = '';}
                    $output .= "<option $default value='{$this->_esc_attr( $arc_row->y_year . $arc_row->m_month)}'>{$this->_esc_html( $tp_locale->get_month( $arc_row->m_month ))}{$arc_row->y_year}</option>\n";
                }
                $output .= "</select>";
            }
            $output .= "";
            $output .= "</li><li>";
            $output .= "";
            $output .= "";
            $output .= "</li></ul></div><div class='block right'><ul><li>";
            if ( $page_links ) {
                $output .= "<div class='table-nav-pages'>$page_links</div>";
                $output .= "</li><li>";
            }
            $output .= "</li><li>";
            $output .= $this->_get_submit_button( $this->__( 'Filter &#187;' ), '', 'post-query-submit', false );
            $output .= "</li></ul></div></div></form>";
            $output .= "<form enctype='multipart/form-data' method='post' action='{$this->_esc_url($form_action_url)}' id='library_form' class='$form_class'><ul><li>";
            $output .= $this->_tp_get_nonce_field( 'media_form' );
            $output .= "</li>";
            ob_start();
            ?>
            <script id='library_form_script'>console.log('library_form_script:','Also here, all but no jQuery here!')</script>
            <?php
            $output .= ob_get_clean();
            $output .= "<li><div id='media_items'>";
            $output .= $this->_add_filter( 'attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2 );
            $output .= $this->_get_media_items( null, $errors );
            $output .= "</div></li><li>";
            $output .= "<dd class='ml-submit'>{$this->_get_submit_button( $this->__( 'Save all changes' ), 'save_button', 'save', false )}</dd>";
            $output .= "</li><li>";
            $output .= "<input name='post_id' id='post_id' type='hidden' value='{(int) $post_id}'/>";
            $output .= "</li></ul></form>";
            return $output;
        }//2704
        /**
         * @description Creates the form for external url
         * @param string $default_view
         * @return string
         */
        protected function _tp_get_media_insert_url_form( $default_view = 'image' ):string{
            if ( ! $this->_apply_filters( 'disable_captions', '' ) ) {
                $caption = "<dt class='label'><label for='caption'>{$this->__('Image Caption')}</label></dt>";
                $caption .= "<dd class='field'><textarea name='caption' id='caption'></textarea></dd>";
                $caption .= "</li><li>";
            } else {$caption = '';}
            $default_align = $this->_get_option( 'image_default_align' );
            if( empty( $default_align )){ $default_align = 'none';}
            if ( 'image' === $default_view ) {
                $view        = 'image-only';
                $ul_class = '';
            } else {
                $view        = 'not-image';
                $ul_class = $view;
            }
            ob_start();
            $output = ob_get_clean();
            ?>
            <style>
                * { padding: 0; margin: 0;list-style-type: none;}
            </style>
            <?php
            $span_required = "<span class='required'>*</span>";
            $_onblur_img_data = " onblur='addExtImage.getImageData()'";
            $_onclick_img_align = " onclick='addExtImage.align=\'align\'+this.value'";
            $checked_none = ( 'none' === $default_align ? ' checked="checked"' : '' );
            $checked_left = ( 'left' === $default_align ? ' checked="checked"' : '' );
            $checked_center = ( 'center' === $default_align ? ' checked="checked"' : '' );
            $checked_right = ( 'right' === $default_align ? ' checked="checked"' : '' );
            $output .= "<header class='content-header'><ul><li class='media-types'>";
            $output .= "<dd><input name='media_type' id='image_only' type='radio' value='image' {$this->_get_checked( 'image_only', $view )}/></dd>";
            $output .= "<dt><label for='image_only' class=''>{$this->__( 'Image')}</label></dt>";
            $output .= "</li><li class='media-types'>";
            $output .= "<dd><input name='media_type' id='not_image' type='radio' value='generic'  {$this->_get_checked( 'not_image', $view )}/></dd>";
            $output .= "<dt><label for='not_image' class=''>{$this->__('Audio, Video, or Other File')}</label></dt>";
            $output .= "</li><li>";
            $output .= "<small class='media-types media-types-required-info'>";
            $output .= sprintf($this->__('Required fields are marked %s'),$span_required);
            $output .= "</small></li><li>";
            $output .= "</li></ul></header>";
            $output .= "<div class='content-container'><ul class='describe $ul_class'><li>";//2946
            $output .= "<dt class='label'><label for='src'>{$this->__('URL')}$span_required</label></dt>";
            $output .= "<dd class='field'><input name='src' id='src' type='text' value='' required $_onblur_img_data /></dd>";
            $output .= "</li><li>";
            $output .= "<dt class='label'><label for='title'>{$this->__('Title')}$span_required</label></dt>";
            $output .= "<dd class='field'><input name='title' id='title' type='text' value='' required/></dd>";
            $output .= "</li><li class='not-image'><small class='help'>{$this->__('Link text, e.g. &#8220;Ransom Demands (PDF)&#8221;')}</small></li><li class='image-only'>";
            $output .= "<dt class='label'><label for='alt'>{$this->__('Alternative Text')}</label></dt>";
            $output .= "<dd class='field'><input name='alt' id='alt' type='text' value='' required/></dd>";
            $output .= "</li><li class='image-only'><small class='help'>{$this->__('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;')}</small></li><li>";
            $output .= $caption;
            $output .= "<dt class='label'><label for='align'>{$this->__('Alignment')}</label></dt>";
            $output .= "</li><li class='image-only'>";
            $output .= "<dd class='field'><input name='align' id='align_none' type='radio' value='none' $_onclick_img_align $checked_none/></dd>";
            $output .= "<dt class='label'><label for='align_none' class='align image-align-none-label'>{$this->__('None')}</label></dt>";
            $output .= "</li><li class='image-only'>";
            $output .= "<dd class='field'><input name='align' id='align_left' type='radio' value='left' $_onclick_img_align $checked_left/></dd>";
            $output .= "<dt class='label'><label for='align_left' class='align image-align-left-label'>{$this->__('Left')}</label></dt>";
            $output .= "</li><li class='image-only'>";
            $output .= "<dd class='field'><input name='align' id='align_center' type='radio' value='center' $_onclick_img_align $checked_center/></dd>";
            $output .= "<dt class='label'><label for='align_center' class='align image-align-center-label'>{$this->__('Center')}</label></dt>";
            $output .= "</li><li class='image-only'>";
            $output .= "<dd class='field'><input name='align' id='align_right' type='radio' value='right' $_onclick_img_align $checked_right/></dd>";
            $output .= "<dt class='label'><label for='align_right' class='align image-align-right-label'>{$this->__('Right')}</label></dt>";
            $output .= "</li><li class='image-only'>";
            $output .= "<dt><label for='url'>{$this->__('Link Image To:')}</label></dt>";
            $output .= "<dd class='field'><input name='url' id='url' type='text' value=''/></dd>";
            $output .= "</li><li class='image-only'>";
            $_onclick_none = " onclick='document.forms[0].url.value=null'";
            $_onclick_link_to_img = " onclick='document.forms[0].url.value=document.forms[0].src.value'";
            $output .= "<dd><button type='button' class='button' $_onclick_none >{$this->__('none')}</button></dd>";
            $output .= "<dd><button type='button' class='button' $_onclick_link_to_img>{$this->__('Link to image')}</button></dd>";
            $output .= "</li><li><small class='help'>{$this->__('Enter a link URL or click above for presets.')}</small>";
            $output .= "</li><li class='image-only'>";
            $_onclick_insert = " onclick='addExtImage.insert()'";
            $_insert_into = "Insert";
            $output .= "<dd><input style='color:#bbb;' id='go_button' class='button' type='button' $_onclick_insert value='{$this->_esc_attr("$_insert_into into Post")}'/></dd>";
            $output .= "</li><li class='image-only last'>";
            $output .= $this->_get_submit_button( $this->__("$_insert_into into Post"), '', 'insert_only_button', false );
            $output .= "</li></ul></div>";
            return $output;
        }//2912
        /**
         * @description Displays the multi-file uploader message.
         * @return string
         */
        protected function _get_media_upload_flash_bypass():string{
            $browser_uploader = $this->_admin_url( 'media_new.php?browser-uploader' );
            $post = $this->_get_post();
            if ( $post ) { $browser_uploader .= '&amp;post_id=' . (int) $post->ID;
            } elseif ( ! empty( $GLOBALS['post_ID'] ) ) {
                $browser_uploader .= '&amp;post_id=' . (int) $GLOBALS['post_ID'];
            }
            $output  = "<p class='upload-flash-bypass'>";
            $output .= sprintf($this->__("You are using the multi-file uploader. Problems? Try the <a href='%1\$s' %2\$s>browser uploader</a> instead."),$browser_uploader,"target='_blank'");
            $output .= "</p>";
            return $output;
        }//3018
        /**
         * @description Displays the browser's built-in uploader message.
         * @return string
         */
        protected function _get_media_upload_html_bypass():string{
            $output  = "<p class='upload-flash-bypass'>";
            $output .= $this->__("You are using the browser&#8217;s built-in file uploader. The TailoredPress uploader includes multiple file selection and drag and drop capability. <a href='#'>Switch to the multi-file uploader</a>.");
            $output .= "</p>";
            return $output;
        }//3047
        protected function _media_upload_text_after():void{}//3060 nothing todo
        /**
         * @description Used to display a "After a file has been uploaded..." help message.
         * @return string
         */
        protected function _get_media_upload_max_image_resize():string{
            $checked = $this->_get_user_setting( 'upload_resize' ) ? "checked='true'" : '';
            $a       = '';
            $end     = '';
            if ( $this->_current_user_can( 'manage_options' ) ) {
                $a   = "<a href='{$this->_esc_url($this->_admin_url( 'options_media.php' ))}' target='_blank'></a>";
                $end = '</a>';
            }
            $output  = "<ul class='hide-if-no-js'><li><dt><label for='image_resize'>";
            $output .= sprintf($this->__("Scale images to match the large size selected in %1\$simage options%2\$s (%3\$d &times; %4\$d)."), $a, $end, (int) $this->_get_option('large_size_w', '1024'), (int) $this->_get_option( 'large_size_h', '1024' ));
            $output .= "</label></dt><dd><input name='image_resize' id='image_resize' type='checkbox' value='true' $checked/></dd>";
            $output .= "</li></ul>";
            return $output;
        }//3067
    }
}else die;