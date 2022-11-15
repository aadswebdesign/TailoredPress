<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_images;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Editor\TP_Image_Editor_GD;
use TP_Core\Libs\Editor\TP_Image_Editor_Imagick;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _media_07 {
        use _init_error;
        use _init_images;
        use _init_assets;
        use _init_db;
        use _init_locale;
        /**
         * @description Returns a TP_Image_Editor instance and loads file into it.
         * @param $path
         * @param array $args
         * @return bool|TP_Error
         */
        protected function _tp_get_image_editor( $path, $args = [] ){
            $args['path'] = $path;
            if ( ! isset( $args['mime_type'] ) ) {
                $file_info = $this->_tp_check_file_type( $args['path'] );
                if ( isset( $file_info ) && $file_info['type'] ) $args['mime_type'] = $file_info['type'];
            }
            $implementation = $this->_tp_image_editor_choose( $args );
            if ( $implementation ) {
                $editor = new $implementation( $path );
                $loaded = null;
                if($editor  instanceof TP_Image_Editor_GD ){
                    $loaded = $editor->load();
                }
                if ( $this->_init_error( $loaded ) ) return $loaded;
                return (bool)$editor;
            }
            return new TP_Error( 'image_no_editor', $this->__( 'No editor could be selected.' ) );
        }//3742
        /**
         * @description Tests whether there is an editor that supports a given mime type or methods.
         * @param array $args
         * @return mixed
         */
        protected function _tp_image_editor_supports( $args = [] ){
            return $this->_tp_image_editor_choose( $args );
        }//3780
        /**
         * @description Tests which editors are capable of supporting the request.
         * @param array $args
         * @return mixed
         */
        protected function _tp_image_editor_choose( $args = [] ){
            $editor_imagick = new TP_Image_Editor_Imagick($args);
            $editor_gd = new TP_Image_Editor_GD($args);
            $implementations = $this->_apply_filters( '_tp_image_editor', [$editor_imagick,$editor_gd] );
            foreach ( $implementations as $implementation ) {
                if ( ! call_user_func([$implementation, 'test'], $args ) ) continue;
                if ( isset( $args['mime_type'] ) && ! call_user_func( [$implementation, 'supports_mime_type'], $args['mime_type'] ))
                    continue;
                if ( isset( $args['methods'] ) && array_diff( $args['methods'], get_class_methods( $implementation ) ) )
                    continue;
                return $implementation;
            }
            return false;
        }//3794 todo
        /**
         * @description Prints default upload arguments.
         */
        protected function _tp_plupload_default_settings():void{
            $tp_scripts = $this->_init_scripts();
            $data = $tp_scripts->get_data( 'tp-plupload', 'data' );
            if ( $data && false !== strpos( $data, '_tp_plupload_settings' ) )
                return;
            $max_upload_size    = $this->_tp_max_upload_size();
            $allowed_extensions = array_keys( $this->_get_allowed_mime_types() );
            $extensions         = [];
            foreach ( $allowed_extensions as $extension )
                $extensions = $this->_tp_array_merge($extensions, explode( '|', $extension ));
            $defaults = array(
                'file_data_name' => 'async-upload',
                'url'=> $this->_admin_url( 'async-upload.php', 'relative' ),//todo
                'filters'=> array(
                    'max_file_size' => $max_upload_size . 'b',
                    'mime_types'    => array( array( 'extensions' => implode( ',', $extensions ) ) ),
                ),
            );
            if ( $this->_tp_is_mobile() && strpos( $_SERVER['HTTP_USER_AGENT'], 'OS 7_' ) !== false &&
                strpos( $_SERVER['HTTP_USER_AGENT'], 'like Mac OS X' ) !== false ) {
                $defaults['multi_selection'] = false;
            }
            if ( ! $this->_tp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) )
                $defaults['webp_upload_error'] = true;
            $defaults = $this->_apply_filters( 'plupload_default_settings', $defaults );
            $params = array(
                'action' => 'upload-attachment',
            );
            $params = $this->_apply_filters( 'plupload_default_params', $params );
            $params['_tpnonce'] = $this->_tp_create_nonce( 'media-form' );
            $defaults['multipart_params'] = $params;
            $settings = array(
                'defaults'      => $defaults,
                'browser'       => array(
                    'mobile'    => $this->_tp_is_mobile(),
                    'supported' => $this->_device_can_upload(),
                ),
                'limitExceeded' => $this->_is_multisite() && ! $this->_is_upload_space_available(),
            );
            $script = 'const _tp_plupload_settings = ' . $this->_tp_json_encode( $settings ) . ';';
           if ( $data ) $script = "$data\n$script";
            $tp_scripts->add_data( 'tp-plupload', 'data', $script );
        }//3838 todo
        /**
         * @description Prepares an attachment post object for JS, where it is expected to be JSON-encoded and fit into an Attachment model.
         * @param $attachment
         * @return bool
         */
        protected function _tp_prepare_attachment_for_js( $attachment ):bool{
            $attachment = $this->_get_post( $attachment );
            if ( ! $attachment ) return false;
            if ( 'attachment' !== $attachment->post_type )
                return false;
            $meta = $this->_tp_get_attachment_metadata( $attachment->ID );
            if ( false !== strpos( $attachment->post_mime_type, '/' ) )
                @list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
            else @list( $type, $subtype ) = array( $attachment->post_mime_type, '' );
            $attachment_url = $this->_tp_get_attachment_url( $attachment->ID );
            $base_url       = str_replace( $this->_tp_basename( $attachment_url ), '', $attachment_url );
            $response = ['id' => $attachment->ID,'title' => $attachment->post_title,
                'filename' => $this->_tp_basename( $this->_get_attached_file( $attachment->ID ) ),
                'url' => $attachment_url,'link' => $this->_get_attachment_link( $attachment->ID ),
                'alt' => $this->_get_post_meta( $attachment->ID, '_tp_attachment_image_alt', true ),
                'author' => $attachment->post_author,'description' => $attachment->post_content,
                'caption' => $attachment->post_excerpt,'name' => $attachment->post_name,
                'status' => $attachment->post_status,'uploadedTo' => $attachment->post_parent,
                'date' => strtotime( $attachment->post_date_gmt ) * 1000,'modified' => strtotime( $attachment->post_modified_gmt ) * 1000,
                'menuOrder' => $attachment->menu_order,'mime' => $attachment->post_mime_type,
                'type' => $type,'subtype' => $subtype,'icon' => $this->_tp_mime_type_icon( $attachment->ID ),
                'dateFormatted' => $this->_mysql2date( $this->__( 'F j, Y' ), $attachment->post_date ),
                'nonces' => ['update' => false,'delete' => false,'edit' => false,],'editLink' => false,'meta' => false,];
            $_author = new TP_User($attachment->post_author);
            $author = null;
            if($_author  instanceof TP_Post ){
                $author = $_author;
            }//todo
            if ($author  instanceof \stdClass && $author->exists() ) {
                $author_name            = $author->display_name ?: $author->nickname;
                $response['authorName'] = html_entity_decode( $author_name, ENT_QUOTES, $this->_get_bloginfo( 'charset' ) );
                $response['authorLink'] = $this->_get_edit_user_link( $author->ID );
            } else $response['authorName'] = $this->__( '(no author)' );
            if ( $attachment->post_parent ) {
                $post_parent = $this->_get_post( $attachment->post_parent );
                if ( $post_parent ) {
                    $response['uploadedToTitle'] = $post_parent->post_title ?: $this->__( '(no title)' );
                    $response['uploadedToLink']  = $this->_get_edit_post_link( $attachment->post_parent, 'raw' );
                }
            }
            $attached_file = $this->_get_attached_file( $attachment->ID );
            if ( isset( $meta['filesize'] ) ) $bytes = $meta['filesize'];
            elseif ( file_exists( $attached_file ) ) $bytes = filesize( $attached_file );
            else $bytes = '';
            if ( $bytes ) {
                $response['filesizeInBytes']       = $bytes;
                $response['filesizeHumanReadable'] = $this->_size_format( $bytes );
            }
            $context             = $this->_get_post_meta( $attachment->ID, '_tp_attachment_context', true );
            $response['context'] = ( $context ) ?: '';
            if ( $this->_current_user_can( 'edit_post', $attachment->ID ) ) {
                $response['nonces']['update'] = $this->_tp_create_nonce( 'update-post_' . $attachment->ID );
                $response['nonces']['edit']   = $this->_tp_create_nonce( 'image_editor_' . $attachment->ID );
                $response['editLink']         = $this->_get_edit_post_link( $attachment->ID, 'raw' );
            }
            if ( $this->_current_user_can( 'delete_post', $attachment->ID ) )
                $response['nonces']['delete'] = $this->_tp_create_nonce( 'delete-post_' . $attachment->ID );
            if ( $meta && ( 'image' === $type || ! empty( $meta['sizes'] ) ) ) {
                $possible_sizes = $this->_apply_filters(
                    'image_size_names_choose',
                    ['thumbnail' => $this->__( 'Thumbnail' ), 'medium' => $this->__( 'Medium' ),'large' => $this->__( 'Large' ), 'full' => $this->__( 'Full Size' ),]
                );
                unset( $possible_sizes['full'] );
                foreach ( $possible_sizes as $size => $label ) {
                    $downsize = $this->_apply_filters( 'image_downsize', false, $attachment->ID, $size );
                    if ( $downsize ) {
                        if ( empty( $downsize[3] ) ) continue;
                        $sizes[ $size ] = ['height' => $downsize[2], 'width' => $downsize[1],'url' => $downsize[0],
                            'orientation' => $downsize[2] > $downsize[1] ? 'portrait' : 'landscape',];
                    } elseif ( isset( $meta['sizes'][ $size ] ) ) {
                        $size_meta = $meta['sizes'][ $size ];
                        @list( $width, $height ) = $this->_image_constrain_size_for_editor( $size_meta['width'], $size_meta['height'], $size, 'edit' );
                        $sizes[ $size ] = ['height' => $height,'width' => $width,'url' => $base_url . $size_meta['file'],
                            'orientation' => $height > $width ? 'portrait' : 'landscape',];
                    }
                }
                $sizes = [];
                if ( 'image' === $type ) {
                    if ( ! empty( $meta['original_image'] ) ) {
                        $response['originalImageURL']  = $this->_tp_get_original_image_url( $attachment->ID );
                        $response['originalImageName'] = $this->_tp_basename( $this->_tp_get_original_image_path( $attachment->ID ) );
                    }
                    $sizes['full'] = array( 'url' => $attachment_url );
                    if ( isset( $meta['height'], $meta['width'] ) ) {
                        $sizes['full']['height']      = $meta['height'];
                        $sizes['full']['width']       = $meta['width'];
                        $sizes['full']['orientation'] = $meta['height'] > $meta['width'] ? 'portrait' : 'landscape';
                    }
                    $response = array_merge( $response, $sizes['full'] );
                }elseif ( $meta['sizes']['full']['file'] ) {
                    $sizes['full'] = array(
                        'url'         => $base_url . $meta['sizes']['full']['file'],
                        'height'      => $meta['sizes']['full']['height'],
                        'width'       => $meta['sizes']['full']['width'],
                        'orientation' => $meta['sizes']['full']['height'] > $meta['sizes']['full']['width'] ? 'portrait' : 'landscape',
                    );
                }
                $response = array_merge( $response, array( 'sizes' => $sizes ) );
            }
            if ( $meta && 'video' === $type ) {
                if ( isset( $meta['width'] ) ) $response['width'] = (int) $meta['width'];
                if ( isset( $meta['height'] ) ) $response['height'] = (int) $meta['height'];
            }
            if ( $meta && ( 'audio' === $type || 'video' === $type ) ) {
                if ( isset( $meta['length_formatted'] ) ) {
                    $response['fileLength']              = $meta['length_formatted'];
                    $response['fileLengthHumanReadable'] = $this->_human_readable_duration( $meta['length_formatted'] );
                }
                $response['meta'] = array();
                foreach ( $this->_tp_get_attachment_id_3_keys( $attachment, 'js' ) as $key => $label ) {
                    $response['meta'][ $key ] = false;
                    if ( ! empty( $meta[ $key ] ) ) $response['meta'][ $key ] = $meta[ $key ];
                }
                $id = $this->_get_post_thumbnail_id( $attachment->ID );
                if ( ! empty( $id ) ) {
                    @list( $src, $width, $height ) = $this->_tp_get_attachment_image_src( $id, 'full' );
                    $response['image']            = compact( 'src', 'width', 'height' );
                    @list( $src, $width, $height ) = $this->_tp_get_attachment_image_src( $id, 'thumbnail' );
                    $response['thumb']            = compact( 'src', 'width', 'height' );
                } else {
                    $src               = $this->_tp_mime_type_icon( $attachment->ID );
                    $width             = 48;
                    $height            = 64;
                    $response['image'] = compact( 'src', 'width', 'height' );
                    $response['thumb'] = compact( 'src', 'width', 'height' );
                }
            }
            if ( function_exists( 'get_compat_media_markup' ) ) {
                $response['compat'] = get_compat_media_markup( $attachment->ID, array( 'in_modal' => true ) );
            }
            if ( function_exists( 'get_media_states' ) ) {
                $media_states = get_media_states( $attachment );
                if ( ! empty( $media_states ) ) $response['mediaStates'] = implode( ', ', $media_states );
            }
            return $this->_apply_filters( 'wp_prepare_attachment_for_js', $response, $attachment, $meta );
        }//3975
        /**
         * @description Enqueues all scripts, styles, settings, and templates necessary to use all media JS APIs.
         * @param \array[] ...$args
         * @return bool
         */
        protected function _tp_enqueue_media(array ...$args):bool{
            if ( $this->_did_action( 'tp_enqueue_media' ) ) return false;
            $tpdb = $this->_init_db();
            $tp_locale = $this->_init_locale();
            $content_width = $this->tp_content_width;
            $defaults = ['post' => null,];
            $args     = $this->_tp_parse_args( $args, $defaults );
            $tabs = ['type' => '','type_url' => '','gallery' => '','library' => '',];
            $tabs = $this->_apply_filters( 'media_upload_tabs', $tabs );
            unset( $tabs['type'], $tabs['type_url'], $tabs['gallery'], $tabs['library'] );
            $props = [
                'link'  => $this->_get_option( 'image_default_link_type' ), // DB default is 'file'.
                'align' => $this->_get_option( 'image_default_align' ),     // Empty default.
                'size'  => $this->_get_option( 'image_default_size' ),      // Empty default.
            ];
            $exts      = array_merge( $this->_tp_get_audio_extensions(), $this->_tp_get_video_extensions() );
            $mimes     = $this->_get_allowed_mime_types();
            $ext_mimes = array();
            foreach ( $exts as $ext ) {
                foreach ( $mimes as $ext_preg => $mime_match ) {
                    if ( preg_match( '#' . $ext . '#i', $ext_preg ) ) {
                        $ext_mimes[ $ext ] = $mime_match;
                        break;
                    }
                }
            }
            $show_audio_playlist = $this->_apply_filters( 'media_library_show_audio_playlist', true );
            if ( null === $show_audio_playlist )
                $show_audio_playlist = $tpdb->get_var( TP_SELECT . " ID FROM $tpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'audio%' LIMIT 1 ");
            $show_video_playlist = $this->_apply_filters( 'media_library_show_video_playlist', true );
            if ( null === $show_video_playlist )
                $show_video_playlist = $tpdb->get_var(TP_SELECT . "	ID FROM $tpdb->posts WHERE post_type = 'attachment'	AND post_mime_type LIKE 'video%' LIMIT 1 ");
            $months = $this->_apply_filters( 'media_library_months_with_files', null );
            if ( ! is_array( $months ) )
                $months = $tpdb->get_results( $tpdb->prepare(TP_SELECT . " DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month FROM $tpdb->posts	WHERE post_type = %s ORDER BY post_date DESC ", 'attachment'));
            /* translators: 1: Month, 2: Year. */
            foreach ( $months as $month_year )
                $month_year->text = sprintf( $this->__( '%1$s %2$d' ), $tp_locale->get_month( $month_year->month ), $month_year->year);
            $infinite_scrolling = $this->_apply_filters( 'media_library_infinite_scrolling', false );
            $settings = [
                'tabs'              => $tabs,
                'tabUrl'            => $this->_add_query_arg( ['chromeless' => true], $this->_admin_url( 'media-upload.php' ) ),
                'mimeTypes'         => $this->_tp_list_pluck( $this->_get_post_mime_types(), 0 ),
                'captions'          => ! $this->_apply_filters( 'disable_captions', '' ),
                'nonce'             => ['sendToEditor' => $this->_tp_create_nonce( 'media-send-to-editor' ),],
                'post'              => ['id' => 0,],
                'defaultProps'      => $props,
                'attachmentCounts'  => ['audio' => ( $show_audio_playlist ) ? 1 : 0, 'video' => ( $show_video_playlist ) ? 1 : 0,],
                'oEmbedProxyUrl'    => $this->_rest_url( 'oembed/1.0/proxy' ),
                'embedExts'         => $exts,
                'embedMimes'        => $ext_mimes,
                'contentWidth'      => $content_width,
                'months'            => $months,
                'mediaTrash'        => __MEDIA_TRASH ? 1 : 0,
                'infiniteScrolling' => ( $infinite_scrolling ) ? 1 : 0,
            ];
            $post = null;
            if ( isset( $args['post'] ) ) {
                $post             = $this->_get_post( $args['post'] );
                $settings['post'] = array(
                    'id'    => $post->ID,
                    'nonce' => $this->_tp_create_nonce( 'update-post_' . $post->ID ),
                );
                $thumbnail_support = $this->_current_theme_supports( 'post-thumbnails', $post->post_type ) && $this->_post_type_supports( $post->post_type, 'thumbnail' );
                if ( ! $thumbnail_support && 'attachment' === $post->post_type && $post->post_mime_type ) {
                    if ( $this->_tp_attachment_is( 'audio', $post ) )
                        $thumbnail_support = $this->_post_type_supports( 'attachment:audio', 'thumbnail' ) || $this->_current_theme_supports( 'post-thumbnails', 'attachment:audio' );
                    elseif ( $this->_tp_attachment_is( 'video', $post ) )
                        $thumbnail_support = $this->_post_type_supports( 'attachment:video', 'thumbnail' ) || $this->_current_theme_supports( 'post-thumbnails', 'attachment:video' );
                }
                if ( $thumbnail_support ) {
                    $featured_image_id                   = $this->_get_post_meta( $post->ID, '_thumbnail_id', true );
                    $settings['post']['featuredImageId'] = $featured_image_id ?: -1;
                }
            }
            if ( $post ) $post_type_object = $this->_get_post_type_object( $post->post_type );
            else $post_type_object = $this->_get_post_type_object( 'post' );
            $strings = [
                /* Generic. */'mediaFrameDefaultTitle' => $this->__( 'Media' ),'url' => $this->__( 'URL' ),'addMedia' => $this->__( 'Add media' ),'search' => $this->__( 'Search' ),
                'select' => $this->__( 'Select' ),'cancel' => $this->__( 'Cancel' ),'update' => $this->__( 'Update' ),'replace' => $this->__( 'Replace' ),'remove' => $this->__( 'Remove' ),
                'back' => $this->__( 'Back' ),'selected' => $this->__( '%d selected' ),'dragInfo' => $this->__( 'Drag and drop to reorder media files.' ),/* Upload. */'uploadFilesTitle' => $this->__( 'Upload files' ),
                'uploadImagesTitle' => $this->__( 'Upload images' ),/* Library. */'mediaLibraryTitle' => $this->__( 'Media Library' ), 'insertMediaTitle' => $this->__( 'Add media' ),'createNewGallery' => $this->__( 'Create a new gallery' ),
                'createNewPlaylist' => $this->__( 'Create a new playlist' ),'createNewVideoPlaylist' => $this->__( 'Create a new video playlist' ),'returnToLibrary' => $this->__( '&#8592; Go to library' ),'allMediaItems' => $this->__( 'All media items' ),
                'allDates' => $this->__( 'All dates' ),'noItemsFound' => $this->__( 'No items found.' ),'insertIntoPost' => $post_type_object->labels->insert_into_item,
                'unattached' => $this->_x( 'Unattached', 'media items' ),'mine' => $this->_x( 'Mine', 'media items' ),'trash' => $this->_x( 'Trash', 'noun' ),'uploadedToThisPost' => $post_type_object->labels->uploaded_to_this_item,
                'warnDelete' => $this->__( "You are about to permanently delete this item from your site.\nThis action cannot be undone.\n 'Cancel' to stop, 'OK' to delete." ),
                'warnBulkDelete' => $this->__( "You are about to permanently delete these items from your site.\nThis action cannot be undone.\n 'Cancel' to stop, 'OK' to delete." ),
                'warnBulkTrash' => $this->__( "You are about to trash these items.\n  'Cancel' to stop, 'OK' to delete." ),'bulkSelect' => $this->__( 'Bulk select' ),
                'trashSelected' => $this->__( 'Move to Trash' ), 'restoreSelected' => $this->__( 'Restore from Trash' ), 'deletePermanently' => $this->__( 'Delete permanently' ),'apply' => $this->__( 'Apply' ),
                'filterByDate' => $this->__( 'Filter by date' ),'filterByType' => $this->__( 'Filter by type' ),'searchLabel' => $this->__( 'Search' ),
                'mediaFound' => $this->__( 'Number of media items found: %d' ),'noMedia' => $this->__( 'No media items found.' ),'noMediaTryNewSearch' => $this->__( 'No media items found. Try a different search.' ),
                /* Library Details. */'attachmentDetails' => $this->__( 'Attachment details' ),/* From URL. */'insertFromUrlTitle' => $this->__( 'Insert from URL' ),/* Featured Images. */'setFeaturedImageTitle' => $post_type_object->labels->featured_image,
                'setFeaturedImage' => $post_type_object->labels->set_featured_image,/* Gallery. */'createGalleryTitle' => $this->__( 'Create gallery' ),'editGalleryTitle' => $this->__( 'Edit gallery' ),
                'cancelGalleryTitle' => $this->__( '&#8592; Cancel gallery' ),'insertGallery' => $this->__( 'Insert gallery' ), 'updateGallery' => $this->__( 'Update gallery' ),'addToGallery' => $this->__( 'Add to gallery' ),
                'addToGalleryTitle' => $this->__( 'Add to gallery' ),'reverseOrder' => $this->__( 'Reverse order' ), /* Edit Image. */'imageDetailsTitle' => $this->__( 'Image details' ),
                'imageReplaceTitle' => $this->__( 'Replace image' ),'imageDetailsCancel' => $this->__( 'Cancel edit' ),'editImage' => $this->__( 'Edit image' ),/* Crop Image. */'chooseImage' => $this->__( 'Choose image' ),
                'selectAndCrop' => $this->__( 'Select and crop' ),'skipCropping' => $this->__( 'Skip cropping' ),'cropImage' => $this->__( 'Crop image' ),'cropYourImage' => $this->__( 'Crop your image' ),
                'cropping' => $this->__( 'Cropping&hellip;' ), /* translators: 1: Suggested width number, 2: Suggested height number. */
                'suggestedDimensions' => $this->__( 'Suggested image dimensions: %1$s by %2$s pixels.' ),
                'cropError' => $this->__( 'There has been an error cropping your image.' ),/* Edit Audio. */'audioDetailsTitle' => $this->__( 'Audio details' ),
                'audioReplaceTitle' => $this->__( 'Replace audio' ),'audioAddSourceTitle' => $this->__( 'Add audio source' ),'audioDetailsCancel' => $this->__( 'Cancel edit' ),
                /* Edit Video. */'videoDetailsTitle' => $this->__( 'Video details' ), 'videoReplaceTitle' => $this->__( 'Replace video' ),'videoAddSourceTitle' => $this->__( 'Add video source' ),'videoDetailsCancel' => $this->__( 'Cancel edit' ),
                'videoSelectPosterImageTitle' => $this->__( 'Select poster image' ),'videoAddTrackTitle' => $this->__( 'Add subtitles' ),/* Playlist. */'playlistDragInfo' => $this->__( 'Drag and drop to reorder tracks.' ),
                'createPlaylistTitle' => $this->__( 'Create audio playlist' ),'editPlaylistTitle' => $this->__( 'Edit audio playlist' ),'cancelPlaylistTitle' => $this->__( '&#8592; Cancel audio playlist' ),
                'insertPlaylist' => $this->__( 'Insert audio playlist' ), 'updatePlaylist' => $this->__( 'Update audio playlist' ),'addToPlaylist' => $this->__( 'Add to audio playlist' ),
                'addToPlaylistTitle' => $this->__( 'Add to Audio Playlist' ), /* Video Playlist. */'videoPlaylistDragInfo' => $this->__( 'Drag and drop to reorder videos.' ),
                'createVideoPlaylistTitle' => $this->__( 'Create video playlist' ),'editVideoPlaylistTitle' => $this->__( 'Edit video playlist' ),'cancelVideoPlaylistTitle' => $this->__( '&#8592; Cancel video playlist' ),
                'insertVideoPlaylist' => $this->__( 'Insert video playlist' ),'updateVideoPlaylist' => $this->__( 'Update video playlist' ),'addToVideoPlaylist' => $this->__( 'Add to video playlist' ),
                'addToVideoPlaylistTitle' => $this->__( 'Add to video Playlist' ), /* Headings. */'filterAttachments' => $this->__( 'Filter media' ),'attachmentsList' => $this->__( 'Media list' ),];
            $settings = $this->_apply_filters( 'media_view_settings', $settings, $post );
            $strings = $this->_apply_filters( 'media_view_strings', $strings, $post );
            $strings['settings'] = $settings;
            $this->tp_enqueue_script( 'media-editor' );
            $this->tp_localize_script( 'media-views', '_tpMediaViewsL10n', $strings );

            $this->tp_enqueue_script( 'media-audiovideo' );
            $this->tp_enqueue_style( 'media-views' );
            if ( $this->_is_admin() ) {
                $this->tp_enqueue_script( 'mce-view' );
                $this->tp_enqueue_script( 'image-edit' );
            }
            $this->tp_enqueue_style( 'imgareaselect' );
            $this->_tp_plupload_default_settings();
            //todo make publics for this
            $this->_add_action( 'admin_footer',[$this,'__tp_print_media_templates']);
            $this->_add_action( 'tp_footer', [$this,'__tp_print_media_templates'] );
            $this->_add_action( 'customize_controls_print_footer_scripts', [$this,'__tp_print_media_templates'] );
            $this->_do_action( 'tp_enqueue_media' );
            return true;
        }//4231
        /**
         * @description Retrieves media attached to the passed post.
         * @param $type
         * @param object $post
         * @return array
         */
        protected function _get_attached_media( $type,object $post):array{
            $post = $this->_get_post($post );
            if ( ! $post ) return [];
            $args = [ 'post_parent' => $post->ID,'post_type' => 'attachment','post_mime_type' => $type,
                'posts_per_page' => -1,'orderby' => 'menu_order','order' => 'ASC',];
            $args = $this->_apply_filters( 'get_attached_media_args', $args, $type, $post );
            $children = $this->_get_children( $args );
            return (array) $this->_apply_filters( 'get_attached_media', $children, $type, $post );
        }//4630
        /**
         * @description Check the content HTML for a audio, video, object, embed, or iframe tags.
         * @param $content
         * @param null $types
         * @return array
         */
        protected function _get_media_embedded_in_content( $content, $types = null ):array{
            $html = array();
            $allowed_media_types = $this->_apply_filters( 'media_embedded_in_content_allowed_types', array( 'audio', 'video', 'object', 'embed', 'iframe' ) );
            if ( ! empty( $types ) ) {
                if ( ! is_array( $types ) ) $types = array( $types );
                $allowed_media_types = array_intersect( $allowed_media_types, $types );
            }
            $tags = implode( '|', $allowed_media_types );
            if ( preg_match_all( '#<(?P<tag>' . $tags . ')[^<]*?(?:>[\s\S]*?<\/(?P=tag)>|\s*\/>)#', $content, $matches ) ) {
                foreach ( $matches[0] as $match ) $html[] = $match;
            }
            return $html;
        }//4680
        /**
         * @description Retrieves galleries from the passed post's content.
         * @param $post
         * @param bool $html
         * @return array
         */
        protected function _get_post_galleries( $post, $html = true ):array{
            $post = $this->_get_post( $post );
            if ( ! $post ) return [];
            if ( ! $this->_has_shortcode( $post->post_content, 'gallery' ) && ! $this->_has_block( 'gallery', $post->post_content ) )
                return [];
            $galleries = [];
            if ( preg_match_all( '/' . $this->_get_shortcode_regex() . '/s', $post->post_content, $matches, PREG_SET_ORDER ) ) {
                foreach ( $matches as $shortcode ) {
                    if ( 'gallery' === $shortcode[2] ) {
                        $srcs = [];
                        $shortcode_attrs = $this->_shortcode_parse_atts( $shortcode[3] );
                        if ( ! is_array( $shortcode_attrs ) ) $shortcode_attrs = [];
                        if ( ! isset( $shortcode_attrs['id'] ) ) $shortcode[3] .= " id='{(int) $post->ID}'";
                        $gallery = $this->_do_shortcode_tag( $shortcode );
                        if ( $html ) $galleries[] = $gallery;
                        else {
                            preg_match_all( '#src=([\'"])(.+?)\1#is', $gallery, $src, PREG_SET_ORDER );
                            if ( ! empty( $src ) ) {
                                foreach ( $src as $s ) $srcs[] = $s[2];
                            }
                            $galleries[] = array_merge(
                                $shortcode_attrs,
                                ['src' => array_values( array_unique( $srcs ) ),]
                            );
                        }
                    }
                }
            }
            if ( $this->_has_block( 'gallery', $post->post_content ) ) {
                $post_blocks = $this->_parse_blocks( $post->post_content );
                while ( $block = array_shift( $post_blocks ) ) {
                    $has_inner_blocks = ! empty( $block['innerBlocks'] );
                    if ( ! $block['blockName'] ) continue;
                    if ( 'core/gallery' !== $block['blockName'] ) {
                        if ( $has_inner_blocks ) array_push( $post_blocks, ...$block['innerBlocks'] );
                        continue;
                    }
                    // New Gallery block format as HTML.
                    if ( $has_inner_blocks && $html ) {
                        $block_html  = $this->_tp_list_pluck( $block['innerBlocks'], 'innerHTML' );
                        $galleries[] = '<figure>' . implode( ' ', $block_html ) . '</figure>';
                        continue;
                    }
                    $srcs = [];
                    if ( $has_inner_blocks ) {
                        $attrs = $this->_tp_list_pluck( $block['innerBlocks'], 'attrs' );
                        $ids   = $this->_tp_list_pluck( $attrs, 'id' );
                        foreach ( $ids as $id ) {
                            $url = $this->_tp_get_attachment_url( $id );
                            if ( is_string( $url ) && ! in_array( $url, $srcs, true ) ) $srcs[] = $url;
                        }
                        $galleries[] = ['ids' => implode( ',', $ids ), 'src' => $srcs,];
                        continue;
                    }
                    if ( $html ) {
                        $galleries[] = $block['innerHTML'];
                        continue;
                    }
                    $ids = ! empty( $block['attrs']['ids'] ) ? $block['attrs']['ids'] : array();
                    if ( ! empty( $ids ) ) {
                        foreach ( $ids as $id ) {
                            $url = $this->_tp_get_attachment_url( $id );
                            if ( is_string( $url ) && ! in_array( $url, $srcs, true ) ) $srcs[] = $url;
                        }
                        $galleries[] = ['ids' => implode( ',', $ids ), 'src' => $srcs,];
                        continue;
                    }
                    preg_match_all( '#src=([\'"])(.+?)\1#is', $block['innerHTML'], $found_srcs, PREG_SET_ORDER );
                    if ( ! empty( $found_srcs[0] ) ) {
                        foreach ( $found_srcs as $src ) {
                            if ( isset( $src[2] ) && ! in_array( $src[2], $srcs, true ) )  $srcs[] = $src[2];
                        }
                    }
                    $galleries[] = array( 'src' => $srcs );
                }
            }
            return $this->_apply_filters( 'get_post_galleries', $galleries, $post );
        }//4722
        /**
         * @description Check a specified post's content for gallery and, if present, return the first
         * @param int $post
         * @param bool $html
         * @return mixed
         */
        protected function _get_post_gallery( $post = 0, $html = true ){
            $galleries = $this->_get_post_galleries( $post, $html );
            $gallery   = reset( $galleries );
            return $this->_apply_filters( 'get_post_gallery', $gallery, $post, $galleries );
        }//4883
     }
}else die;