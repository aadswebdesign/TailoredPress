<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 00:28
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Editor\TP_Image_Editor;
use TP_Core\Libs\RestApi\Fields\TP_REST_Meta_Fields;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Attachments_Controller extends TP_REST_Posts_Controller{
        protected $_allow_batch = false;
        public function register_routes():void{
            parent::register_routes();
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<id>[\d]+)/post-process',
                [
                    'methods' => TP_POST,'callback' => [$this, 'post_process_item'],
                    'permission_callback' => [$this, 'post_process_item_permissions_check'],
                    'args' => [
                        'id' => ['description' => $this->__( 'Unique identifier for the attachment.' ),'type' => 'integer',],
                        'action' => ['type' => 'string','enum' => array( 'create-image-subsizes' ), 'required' => true,
                        ],
                    ],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<id>[\d]+)/edit',
                ['methods' => TP_POST,
                    'callback' => array( $this, 'edit_media_item' ),
                    'permission_callback' => [ $this, 'edit_media_item_permissions_check'],
                    'args' => $this->get_edit_media_item_args(),]
            );
        }//34 //todo shrink
        protected function _prepare_items_query( $prepared_args = array(), $request = null ):string{
            $query_args = parent::_prepare_items_query( $prepared_args, $request );
            if ( empty( $query_args['post_status'] ) ) $query_args['post_status'] = 'inherit';
            $media_types = $this->_get_media_types();
            if ( ! empty( $request['media_type'] ) && isset( $media_types[ $request['media_type'] ] ) )
                $query_args['post_mime_type'] = $media_types[ $request['media_type'] ];
            if ( ! empty( $request['mime_type'] ) ) {
                $parts = explode( '/', $request['mime_type'] );
                if ( isset( $media_types[ $parts[0] ] ) && in_array( $request['mime_type'], $media_types[ $parts[0] ], true ) )
                    $query_args['post_mime_type'] = $request['mime_type'];
            }
            if ( isset( $query_args['s'] ) ) $this->_add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );
            return $query_args;
        }//78
        public function create_item_permissions_check( $request ):string{
            $ret = parent::create_item_permissions_check( $request );
            if ( ! $ret ||  $this->_init_error( $ret ) ) return $ret;
            if ( ! $this->_current_user_can( 'upload_files' ) )
                return new TP_Error('rest_cannot_create',
                    $this->__( 'Sorry, you are not allowed to upload media on this site.' ),
                    ['status' => BAD_REQUEST ]);
            if ( ! empty( $request['post'] ) && ! $this->_current_user_can( 'edit_post', (int) $request['post'] ) )
                return new TP_Error('rest_cannot_edit',
                    $this->__( 'Sorry, you are not allowed to upload media to this post.' ),
                    ['status' => $this->_rest_authorization_required_code()] );
            return true;
        }//114
        public function create_item(TP_REST_Request $request ):string{
            if ( ! empty( $request['post'] ) && in_array( $this->_get_post_type( $request['post'] ), array( 'revision', 'attachment' ), true ) )
                return new TP_Error('rest_invalid_param', $this->__( 'Invalid parent type.' ), array( 'status' => BAD_REQUEST ));
            $insert = $this->_insert_attachment( $request );
            if ( $this->_init_error( $insert ) ) return $insert;
            $schema = $this->get_item_schema();
            $attachment_id = $insert['attachment_id'];
            $file          = $insert['file'];
            if ( isset( $request['alt_text'] ) )
                $this->_update_post_meta( $attachment_id, '_tp_attachment_image_alt', $this->_sanitize_text_field( $request['alt_text'] ) );
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                if($this->_meta instanceof TP_REST_Meta_Fields ){}
                $meta_update = $this->_meta->update_value( $request['meta'], $attachment_id );
                if ( $this->_init_error( $meta_update ) )  return $meta_update;
            }
            $attachment    = $this->_get_post( $attachment_id );
            $fields_update = $this->_update_additional_fields_for_object( $attachment, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_attachment', $attachment, $request, true );
            $this->_tp_after_insert_post( $attachment, false, null );
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST )  header( 'X-TP-Upload-Attachment-ID: ' . $attachment_id );
            $this->_tp_update_attachment_metadata( $attachment_id, $this->_tp_generate_attachment_metadata( $attachment_id, $file ) );
            $response = $this->prepare_item_for_response( $attachment, $request );
            $response = $this->_rest_ensure_response( $response );
            $response->set_status( 201 );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $attachment_id ) ) );
            return $response;
        }//149
        protected function _insert_attachment(TP_REST_Request $request ){
            $files   = $request->get_file_params();
            $headers = $request->get_headers();
            if ( ! empty( $files ) ) $file = $this->_upload_from_file( $files, $headers );
            else $file = $this->_upload_from_data( $request->get_body(), $headers );
            if ( $this->_init_error( $file ) ) return $file;
            $file_name       = $this->_tp_basename( $file['file'] );
            $name_parts = pathinfo( $file_name );
            $name       = trim( substr( $file_name, 0, -( 1 + strlen( $name_parts['extension'] ) ) ) );
            $url  = $file['url'];
            $type = $file['type'];
            $file = $file['file'];
            $image_meta = $this->_tp_read_image_metadata( $file );
            if ( ! empty( $image_meta ) ) {
                if ( empty( $request['title'] ) && trim( $image_meta['title'] ) && ! is_numeric( $this->_sanitize_title( $image_meta['title'] ) ) )
                    $request['title'] = $image_meta['title'];
                if ( empty( $request['caption'] ) && trim( $image_meta['caption'] ) ) $request['caption'] = $image_meta['caption'];
            }
            $attachment = $this->_prepare_item_for_database( $request );
            $attachment->post_mime_type = $type;
            $attachment->guid           = $url;
            if ( empty( $attachment->post_title ) )
                $attachment->post_title = preg_replace( '/\.[^.]+$/', '', $this->_tp_basename( $file ) );
            $id = $this->_tp_insert_attachment( $this->_tp_slash( (array) $attachment ), $file, 0, true, false );
            if ( $this->_init_error( $id ) ) {
                if ( 'db_update_error' === $id->get_error_code() ) $id->add_data( array( 'status' => INTERNAL_SERVER_ERROR ) );
                else  $id->add_data( array( 'status' => BAD_REQUEST ) );
                return $id;
            }
            $attachment = $this->_get_post( $id );
            $this->_do_action( 'rest_insert_attachment', $attachment, $request, true );
            return ['attachment_id' => $id, 'file' => $file, 'filename' => $name,];
        }//234
        public function update_item(TP_REST_Request $request ):string{
            if ( ! empty( $request['post'] ) && in_array( $this->_get_post_type( $request['post'] ), array( 'revision', 'attachment' ), true ) )
                return new TP_Error('rest_invalid_param', $this->__( 'Invalid parent type.' ),['status' => BAD_REQUEST] );
            $attachment_before = $this->_get_post( $request['id'] );
            $response          = parent::update_item( $request );
            if ( $this->_init_error( $response ) ) return $response;
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if($_response  instanceof TP_REST_Response ){
                $response = $_response;
            }
            $data     = $response->get_data();
            if ( isset( $request['alt_text'] ) )
                $this->_update_post_meta( $data['id'], '_tp_attachment_image_alt', $request['alt_text'] );
            $attachment = $this->_get_post( $request['id'] );
            $fields_update = $this->_update_additional_fields_for_object( $attachment, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_attachment', $attachment, $request, false );
            $this->_tp_after_insert_post( $attachment, true, $attachment_before );
            $response = $this->prepare_item_for_response( $attachment, $request );
            $response = $this->_rest_ensure_response( $response );
            return $response;
        }//323
        public function post_process_item( $request ){
            if ($request['action'] === 'create-image-subsizes') {
                $this->_tp_update_image_subsizes($request['id']);
            }
            $request['context'] = 'edit';
            return $this->prepare_item_for_response( $this->_get_post( $request['id'] ), $request );
        }//375
        public function post_process_item_permissions_check( $request ){
            return $this->update_item_permissions_check( $request );
        }//369
        public function edit_media_item_permissions_check( $request ){
            if ( ! $this->_current_user_can( 'upload_files' ) )
                return new TP_Error('rest_cannot_edit_image',$this->__( 'Sorry, you are not allowed to upload media on this site.' ), ['status' => $this->_rest_authorization_required_code()] );
            return $this->update_item_permissions_check( $request );
        }//408
        public function edit_media_item( $request ){
            $attachment_id = $request['id'];
            $image_file = $this->_tp_get_original_image_path( $attachment_id );
            $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
            if ( ! $image_meta || ! $image_file || ! $this->_tp_image_file_matches_image_meta( $request['src'], $image_meta, $attachment_id ))
                return new TP_Error('rest_unknown_attachment', $this->__( 'Unable to get meta information for file.' ), ['status' => NOT_FOUND]);
            $supported_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
            $mime_type       = $this->_get_post_mime_type( $attachment_id );
            if ( ! in_array( $mime_type, $supported_types, true ) )
                return new TP_Error('rest_cannot_edit_file_type',$this->__( 'This type of file cannot be edited.' ),['status' => BAD_REQUEST]);
            if ( isset( $request['modifiers'] ) ) $modifiers = $request['modifiers'];
            else {
                $modifiers = [];
                if ( ! empty( $request['rotation'] ) ) $modifiers[] = ['type' => 'rotate','args' => ['angle' => $request['rotation'],],];
                if ( isset( $request['x'], $request['y'], $request['width'], $request['height'] ) )
                    $modifiers[] = ['type' => 'crop','args' => ['left' => $request['x'],'top' => $request['y'],'width' => $request['width'], 'height' => $request['height'],],];
                if ( 0 === count( $modifiers ) )
                    return new TP_Error('rest_image_not_edited', $this->__( 'The image was not edited. Edit the image before applying the changes.' ), ['status' => BAD_REQUEST ]);
            }
            $image_file_to_edit = $image_file;
            if ( ! file_exists( $image_file_to_edit ) )
                $image_file_to_edit = $this->_load_image_to_edit_path( $attachment_id );
            $_image_editor = $this->_tp_get_image_editor( $image_file_to_edit );
            $image_editor = null;
            if( $_image_editor instanceof TP_Image_Editor ){
                $image_editor = $_image_editor;
            }
            if ( $this->_init_error( $image_editor ) )
                return new TP_Error('rest_unknown_image_file_type', $this->__( 'Unable to edit this image.' ), ['status' => INTERNAL_SERVER_ERROR]);
            foreach ( $modifiers as $modifier ) {
                $args = $modifier['args'];
                switch ( $modifier['type'] ) {
                    case 'rotate':
                        $rotate = 0 - $args['angle'];
                        if ( 0 !== $rotate ) {
                            $result = $image_editor->rotate( $rotate );
                            if ( $this->_init_error( $result ) )
                                return new TP_Error('rest_image_rotation_failed',$this->__( 'Unable to rotate this image.' ), ['status' => INTERNAL_SERVER_ERROR]);
                        }
                        break;
                    case 'crop':
                        $size = $image_editor->get_size();
                        $crop_x = round( ( $size['width'] * $args['left'] ) / 100.0 );
                        $crop_y = round( ( $size['height'] * $args['top'] ) / 100.0 );
                        $width  = round( ( $size['width'] * $args['width'] ) / 100.0 );
                        $height = round( ( $size['height'] * $args['height'] ) / 100.0 );
                        if ( $size['width'] !== $width && $size['height'] !== $height ) {
                            $result = $image_editor->crop( $crop_x, $crop_y, $width, $height );
                            if ( $this->_init_error( $result ) )
                                return new TP_Error('rest_image_crop_failed',$this->__( 'Unable to crop this image.' ), ['status' => INTERNAL_SERVER_ERROR]);
                        }
                        break;
                }
            }
            $image_ext  = pathinfo( $image_file, PATHINFO_EXTENSION );
            $image_name = $this->_tp_basename( $image_file, ".{$image_ext}" );
            if ( preg_match( '/-edited(-\d+)?$/', $image_name ) )
                $image_name = preg_replace( '/-edited(-\d+)?$/', '-edited', $image_name );
            else $image_name .= '-edited';
            $filename = "{$image_name}.{$image_ext}";
            $uploads = $this->_tp_upload_dir();
            $filename = $this->_tp_unique_filename( $uploads['path'], $filename );
            $saved = $image_editor->save( $uploads['path'] . "/$filename" );
            if ( $this->_init_error( $saved ) ) return $saved;
            $new_attachment_post = ['post_mime_type' => $saved['mime-type'],'guid' => $uploads['url'] . "/$filename",'post_title' => $image_name, 'post_content' => '',];
            $attachment_post = $this->_get_post( $attachment_id );
            if ( $attachment_post && $attachment_post instanceof \stdClass ) { //todo
                $new_attachment_post['post_content'] = $attachment_post->post_content;
                $new_attachment_post['post_excerpt'] = $attachment_post->post_excerpt;
                $new_attachment_post['post_title']   = $attachment_post->post_title;
            }
            $new_attachment_id = $this->_tp_insert_attachment( $this->_tp_slash( $new_attachment_post ), $saved['path'], 0, true );
            if ( $this->_init_error( $new_attachment_id ) ) {
                if ( 'db_update_error' === $new_attachment_id->get_error_code() )
                    $new_attachment_id->add_data( array( 'status' => INTERNAL_SERVER_ERROR ) );
                else  $new_attachment_id->add_data( array( 'status' => BAD_REQUEST ) );
                return $new_attachment_id;
            }
            $image_alt = $this->_get_post_meta( $attachment_id, '_tp_attachment_image_alt', true );
            if ( ! empty( $image_alt ) )  $this->_update_post_meta( $new_attachment_id, '_tp_attachment_image_alt', $this->_tp_slash( $image_alt ) );
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) header( 'X-TP-Upload-Attachment-ID: ' . $new_attachment_id );
            $new_image_meta = $this->_tp_generate_attachment_metadata( $new_attachment_id, $saved['path'] );
            if ( isset( $image_meta['image_meta'] ,$new_image_meta['image_meta']) && is_array( $new_image_meta['image_meta'] ) ) {
                foreach ( (array) $image_meta['image_meta'] as $key => $value ) {
                    if ( empty( $new_image_meta['image_meta'][ $key ] ) && ! empty( $value ) ){
                        //$new_image_meta['image_meta'][ $key ] = $value; todo
                    }

                }
            }
            $new_img_meta = $new_image_meta['image_meta']['orientation'];
            if ( ! empty($new_img_meta) ) $new_img_meta = 1;
            $new_img_meta['parent_image'] = ['attachment_id' => $attachment_id, 'file' => $this->_tp_relative_upload_path( $image_file ),];
            $new_img_meta = $this->_apply_filters( 'tp_edited_image_metadata', $new_img_meta, $new_attachment_id, $attachment_id );
            $this->_tp_update_attachment_metadata( $new_attachment_id, $new_img_meta );
            $_response = $this->prepare_item_for_response( $this->_get_post( $new_attachment_id ), $request );
            $response = null;
            if($_response  instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( 201 );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%s', $this->_namespace, $this->_rest_base, $new_attachment_id ) ) );
            return $response;
        }//428
        protected function _prepare_item_for_database( $request ):string{
            $prepared_attachment = parent::_prepare_item_for_database( $request );
            if ( isset( $request['caption'] ) ) {
                if ( is_string( $request['caption'] ) ) $prepared_attachment->post_excerpt = $request['caption'];
                elseif ( isset( $request['caption']['raw'] ) ) $prepared_attachment->post_excerpt = $request['caption']['raw'];
            }
            if ( isset( $request['description'] ) ) {
                if ( is_string( $request['description'] ) )  $prepared_attachment->post_content = $request['description'];
                elseif ( isset( $request['description']['raw'] ) ) $prepared_attachment->post_content = $request['description']['raw'];
            }
            if ( isset( $request['post'] ) ) $prepared_attachment->post_parent = (int) $request['post'];
            return $prepared_attachment;
        }//686
        public function prepare_item_for_response( $item, $request ):string{
            $post     = $item;
            $response = parent::prepare_item_for_response( $post, $request );
            if($response  instanceof TP_REST_Response ){}
            $fields   = $this->get_fields_for_response( $request );
            $data     = $response->get_data();
            if ( in_array( 'description', $fields, true ) )
                $data['description'] = ['raw'=> $post->post_content, 'rendered' => $this->_apply_filters( 'the_content', $post->post_content ),];
            if ( in_array( 'caption', $fields, true ) ) {
                $caption = $this->_apply_filters( 'get_the_excerpt', $post->post_excerpt, $post );
                $caption = $this->_apply_filters( 'the_excerpt', $caption );
                $data['caption'] = ['raw' => $post->post_excerpt, 'rendered' => $caption,];
            }
            if ( in_array( 'alt_text', $fields, true ) ) $data['alt_text'] = $this->_get_post_meta( $post->ID, '_wp_attachment_image_alt', true );
            if ( in_array( 'media_type', $fields, true ) ) $data['media_type'] = $this->_tp_attachment_is_image( $post->ID ) ? 'image' : 'file';
            if ( in_array( 'mime_type', $fields, true ) ) $data['mime_type'] = $post->post_mime_type;
            if ( in_array( 'media_details', $fields, true ) ) {
                $data['media_details'] = $this->_tp_get_attachment_metadata( $post->ID );
                if ( empty( $data['media_details'] ) ) $data['media_details'] = new \stdClass;
                elseif ( ! empty( $data['media_details']['sizes'] ) ) {
                    foreach ( $data['media_details']['sizes'] as $size => &$size_data ) {
                        if ( isset( $size_data['mime-type'] ) ) {
                            $size_data['mime_type'] = $size_data['mime-type'];
                            unset( $size_data['mime-type'] );
                        }
                        $image_src = $this->_tp_get_attachment_image_src( $post->ID, $size );
                        if ( ! $image_src ) continue;
                        $size_data['source_url'] = $image_src[0];
                    }
                    unset($size_data);
                    $full_src = $this->_tp_get_attachment_image_src( $post->ID, 'full' );
                    if ( ! empty( $full_src ) )
                        $data['media_details']['sizes']['full'] = ['file' => $this->_tp_basename( $full_src[0] ),'width' => $full_src[1],
                            'height' => $full_src[2],'mime_type' => $post->post_mime_type,'source_url' => $full_src[0],];
                } else $data['media_details']['sizes'] = new \stdClass;
            }
            if ( in_array( 'post', $fields, true ) ) $data['post'] = ! empty( $post->post_parent ) ? (int) $post->post_parent : null;
            if ( in_array( 'source_url', $fields, true ) )  $data['source_url'] = $this->_tp_get_attachment_url( $post->ID );
            if ( in_array( 'missing_image_sizes', $fields, true ) )
                $data['missing_image_sizes'] = array_keys( $this->_tp_get_missing_image_subsizes( $post->ID ) );
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data = $this->filter_response_by_context( $data, $context );
            $links = $response->get_links();
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if($_response  instanceof TP_REST_Response ){
                $response = $_response;
            }
            foreach ( $links as $rel => $rel_links ) {
                foreach ( $rel_links as $link ) $response->add_link( $rel, $link['href'], $link['attributes'] );
            }
            return $this->_apply_filters( 'rest_prepare_attachment', $response, $post, $request );
        }//724
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = parent::get_item_schema();
            $schema['properties']['alt_text'] = array(
                'description' => $this->__( 'Alternative text to display when attachment is not displayed.' ),
                'type'        => 'string',
                'context'     => array( 'view', 'edit', 'embed' ),
                'arg_options' => array(
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            );
            $schema['properties']['caption'] = array(
                'description' => $this->__( 'The attachment caption.' ),
                'type'        => 'object',
                'context'     => array( 'view', 'edit', 'embed' ),
                'arg_options' => array(
                    'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
                    'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
                ),
                'properties'  => array(
                    'raw'      => array(
                        'description' => $this->__( 'Caption for the attachment, as it exists in the database.' ),
                        'type'        => 'string',
                        'context'     => array( 'edit' ),
                    ),
                    'rendered' => array(
                        'description' => $this->__( 'HTML caption for the attachment, transformed for display.' ),
                        'type'        => 'string',
                        'context'     => array( 'view', 'edit', 'embed' ),
                        'readonly'    => true,
                    ),
                ),
            );
            $schema['properties']['description'] = array(
                'description' => $this->__( 'The attachment description.' ),
                'type'        => 'object',
                'context'     => array( 'view', 'edit' ),
                'arg_options' => array(
                    'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
                    'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
                ),
                'properties'  => array(
                    'raw'      => array(
                        'description' => $this->__( 'Description for the attachment, as it exists in the database.' ),
                        'type'        => 'string',
                        'context'     => array( 'edit' ),
                    ),
                    'rendered' => array(
                        'description' => $this->__( 'HTML description for the attachment, transformed for display.' ),
                        'type'        => 'string',
                        'context'     => array( 'view', 'edit' ),
                        'readonly'    => true,
                    ),
                ),
            );
            $schema['properties']['media_type'] = array(
                'description' => $this->__( 'Attachment type.' ),
                'type'        => 'string',
                'enum'        => array( 'image', 'file' ),
                'context'     => array( 'view', 'edit', 'embed' ),
                'readonly'    => true,
            );
            $schema['properties']['mime_type'] = array(
                'description' => $this->__( 'The attachment MIME type.' ),
                'type'        => 'string',
                'context'     => array( 'view', 'edit', 'embed' ),
                'readonly'    => true,
            );
            $schema['properties']['media_details'] = array(
                'description' => $this->__( 'Details about the media file, specific to its type.' ),
                'type'        => 'object',
                'context'     => array( 'view', 'edit', 'embed' ),
                'readonly'    => true,
            );
            $schema['properties']['post'] = array(
                'description' => $this->__( 'The ID for the associated post of the attachment.' ),
                'type'        => 'integer',
                'context'     => array( 'view', 'edit' ),
            );
            $schema['properties']['source_url'] = array(
                'description' => $this->__( 'URL to the original attachment file.' ),
                'type'        => 'string',
                'format'      => 'uri',
                'context'     => array( 'view', 'edit', 'embed' ),
                'readonly'    => true,
            );
            $schema['properties']['missing_image_sizes'] = array(
                'description' => $this->__( 'List of the missing image sizes of the attachment.' ),
                'type'        => 'array',
                'items'       => array( 'type' => 'string' ),
                'context'     => array( 'edit' ),
                'readonly'    => true,
            );
            unset( $schema['properties']['password'] );
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//853 //todo shrink
        protected function _upload_from_data( $data, $headers ){
            if ( empty( $data ) ) return new TP_Error('rest_upload_no_data',$this->__( 'No data supplied.' ),['status' => BAD_REQUEST ]);
            if ( empty( $headers['content_type'] ) )
                return new TP_Error('rest_upload_no_content_type',$this->__( 'No Content-Type supplied.' ), ['status' => BAD_REQUEST ] );
            if ( empty( $headers['content_disposition'] ) )
                return new TP_Error('rest_upload_no_content_disposition', $this->__( 'No Content-Disposition supplied.' ), ['status' => BAD_REQUEST ]);
            $filename = self::get_filename_from_disposition( $headers['content_disposition'] );
            if ( empty( $filename ) )
                return new TP_Error('rest_upload_invalid_disposition',
                    $this->__( 'Invalid Content-Disposition supplied. Content-Disposition needs to be formatted as `attachment; filename="image.png"` or similar.' ),
                    ['status' => BAD_REQUEST ]);
            if ( ! empty( $headers['content_md5'] ) ) {
                $content_md5 = array_shift( $headers['content_md5'] );
                $expected = trim( $content_md5 );
                $actual = md5( $data );
                if ( $expected !== $actual ) return new TP_Error('rest_upload_hash_mismatch', $this->__( 'Content hash did not match expected.' ),['status' => PRECONDITION_FAILED]);
            }
            $type = array_shift( $headers['content_type'] );
            $tmpfname =  $this->_tp_temp_name( $filename );
            $fp = fopen( $tmpfname, 'wb+' );
            if ( ! $fp )  return new TP_Error('rest_upload_file_error', $this->__( 'Could not open file handle.' ), ['status' => INTERNAL_SERVER_ERROR]);
            fwrite( $fp, $data );
            fclose( $fp );
            $file_data = ['error' => null, 'tmp_name' => $tmpfname,'name' => $filename, 'type' => $type,];
            $size_check = $this->_check_upload_size( $file_data );
            if ( $this->_init_error( $size_check ) )  return $size_check;
            $overrides = ['test_form' => false,];
            $sideloaded = $this->_tp_handle_sideload( $file_data, $overrides );
            if ( isset( $sideloaded['error'] ) ) {
                @unlink( $tmpfname );
                return new TP_Error( 'rest_upload_sideload_error', $sideloaded['error'], ['status' => INTERNAL_SERVER_ERROR]);
            }
            return $sideloaded;
        }//975
        public static function get_filename_from_disposition( $disposition_header ){
            $filename = null;
            foreach ( $disposition_header as $value ) {
                $value = trim( $value );
                if ( strpos( $value, ';' ) === false ) continue;
                @list( $type, $attr_parts ) = explode( ';', $value, 2 );

                $attr_parts = explode( ';', $attr_parts.$type );
                $attributes = [];
                foreach ( $attr_parts as $part ) {
                    if ( strpos( $part, '=' ) === false ) continue;
                    @list( $key, $value ) = explode( '=', $part, 2 );
                    $attributes[ trim( $key ) ] = trim( $value );
                }
                if ( empty( $attributes['filename'] ) ) continue;
                $filename = trim( $attributes['filename'] );
                if ( $filename[0] === '"' && $filename[strlen($filename) - 1] === '"' )
                    $filename = substr( $filename, 1, -1 );
            }
            return $filename;
        }//1107
        public function get_collection_params():array{
            $params                            = parent::get_collection_params();
            $params['status']['default']       = 'inherit';
            $params['status']['items']['enum'] = array( 'inherit', 'private', 'trash' );
            $media_types                       = $this->_get_media_types();
            $params['media_type'] = ['default' => null,
                'description' => $this->__( 'Limit result set to attachments of a particular media type.' ),
                'type' => 'string','enum' => array_keys( $media_types ),];
            $params['mime_type'] = ['default' => null,
                'description' => $this->__( 'Limit result set to attachments of a particular MIME type.' ),
                'type' => 'string',];
            return $params;
        }//1155
        protected function _upload_from_file( $files, $headers ){
            if ( empty( $files ) )
                return new TP_Error('rest_upload_no_data', $this->__( 'No data supplied.' ), [ 'status' => BAD_REQUEST] );
            if ( ! empty( $headers['content_md5'] ) ) {
                $content_md5 = array_shift( $headers['content_md5'] );
                $expected    = trim( $content_md5 );
                $actual      = md5_file( $files['file']['tmp_name'] );
                if ( $expected !== $actual )
                    return new TP_Error('rest_upload_hash_mismatch', $this->__( 'Content hash did not match expected.' ),[ 'status' => PRECONDITION_FAILED ] );
            }
            $overrides = ['test_form' => false,];
            if ( defined( 'DIR_TEST_DATA' ) && DIR_TEST_DATA ) $overrides['action'] = 'tp_handle_mock_upload';
            $size_check = $this->_check_upload_size( $files['file'] );
            if ( $this->_init_error( $size_check ) )  return $size_check;
            $file =  $this->_tp_handle_upload( $files['file'], $overrides);
            if ( isset( $file['error'] ) )
                return new TP_Error('rest_upload_unknown_error', $file['error'],['status' => INTERNAL_SERVER_ERROR ]);
            return $file;
        }//1186
        protected function _get_media_types(): array{
            $media_types = [];
            foreach ( $this->_get_allowed_mime_types() as $mime_type ) {
                $parts = explode( '/', $mime_type );
                if ( ! isset( $media_types[ $parts[0] ] ) )  $media_types[ $parts[0] ] = [];
                $media_types[ $parts[0] ][] = $mime_type;
            }
            return $media_types;
        }//1250
        protected function _check_upload_size( $file ){
            if ( ! $this->_is_multisite() ) return true;
            if ( $this->_get_site_option( 'upload_space_check_disabled' ) ) return true;
            $space_left = $this->_get_upload_space_available();
            $file_size = filesize( $file['tmp_name'] );
            if ( $space_left < $file_size ) {
                return new TP_Error('rest_upload_limited_space',
                    sprintf( $this->__( 'Not enough space to upload. %s KB needed.' ), number_format( ( $file_size - $space_left ) / KB_IN_BYTES ) ),
                    ['status' => 400]
                );
            }
            if ( $file_size > ( KB_IN_BYTES * $this->_get_site_option( 'fileupload_maxk', 1500 ) ) )
                return new TP_Error('rest_upload_file_too_big',/* translators: %s: Maximum allowed file size in kilobytes. */
                    sprintf( $this->__( 'This file is too big. Files must be less than %s KB in size.' ), $this->_get_site_option( 'fileupload_maxk', 1500 ) ),
                    ['status' => BAD_REQUEST]);
            if ( $this->_upload_is_user_over_quota( false ) )
                return new TP_Error('rest_upload_user_quota_exceeded',
                    $this->__( 'You have used your space quota. Please delete files before uploading.' ),
                    ['status' => BAD_REQUEST] );
            return true;
        }//1276
        protected function get_edit_media_item_args(): array{
            return array(
                'src'       => array(
                    'description' => $this->__( 'URL to the edited image file.' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'required'    => true,
                ),
                'modifiers' => array(
                    'description' => $this->__( 'Array of image edits.' ),
                    'type'        => 'array',
                    'minItems'    => 1,
                    'items'       => array(
                        'description' => $this->__( 'Image edit.' ),
                        'type'        => 'object',
                        'required'    => array(
                            'type',
                            'args',
                        ),
                        'oneOf'       => array(
                            array(
                                'title'      => $this->__( 'Rotation' ),
                                'properties' => array(
                                    'type' => array(
                                        'description' => $this->__( 'Rotation type.' ),
                                        'type'        => 'string',
                                        'enum'        => array( 'rotate' ),
                                    ),
                                    'args' => array(
                                        'description' => $this->__( 'Rotation arguments.' ),
                                        'type'        => 'object',
                                        'required'    => array(
                                            'angle',
                                        ),
                                        'properties'  => array(
                                            'angle' => array(
                                                'description' => $this->__( 'Angle to rotate clockwise in degrees.' ),
                                                'type'        => 'number',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            array(
                                'title'      => $this->__( 'Crop' ),
                                'properties' => array(
                                    'type' => array(
                                        'description' => $this->__( 'Crop type.' ),
                                        'type'        => 'string',
                                        'enum'        => array( 'crop' ),
                                    ),
                                    'args' => array(
                                        'description' => $this->__( 'Crop arguments.' ),
                                        'type'        => 'object',
                                        'required'    => array(
                                            'left',
                                            'top',
                                            'width',
                                            'height',
                                        ),
                                        'properties'  => array(
                                            'left'   => array(
                                                'description' => $this->__( 'Horizontal position from the left to begin the crop as a percentage of the image width.' ),
                                                'type'        => 'number',
                                            ),
                                            'top'    => array(
                                                'description' => $this->__( 'Vertical position from the top to begin the crop as a percentage of the image height.' ),
                                                'type'        => 'number',
                                            ),
                                            'width'  => array(
                                                'description' => $this->__( 'Width of the crop as a percentage of the image width.' ),
                                                'type'        => 'number',
                                            ),
                                            'height' => array(
                                                'description' => $this->__( 'Height of the crop as a percentage of the image height.' ),
                                                'type'        => 'number',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'rotation'  => array(
                    'description'      => $this->__( 'The amount to rotate the image clockwise in degrees. DEPRECATED: Use `modifiers` instead.' ),
                    'type'             => 'integer',
                    'minimum'          => 0,
                    'exclusiveMinimum' => true,
                    'maximum'          => 360,
                    'exclusiveMaximum' => true,
                ),
                'x'         => array(
                    'description' => $this->__( 'As a percentage of the image, the x position to start the crop from. DEPRECATED: Use `modifiers` instead.' ),
                    'type'        => 'number',
                    'minimum'     => 0,
                    'maximum'     => 100,
                ),
                'y'         => array(
                    'description' => $this->__( 'As a percentage of the image, the y position to start the crop from. DEPRECATED: Use `modifiers` instead.' ),
                    'type'        => 'number',
                    'minimum'     => 0,
                    'maximum'     => 100,
                ),
                'width'     => array(
                    'description' => $this->__( 'As a percentage of the image, the width to crop the image to. DEPRECATED: Use `modifiers` instead.' ),
                    'type'        => 'number',
                    'minimum'     => 0,
                    'maximum'     => 100,
                ),
                'height'    => array(
                    'description' => $this->__( 'As a percentage of the image, the height to crop the image to. DEPRECATED: Use `modifiers` instead.' ),
                    'type'        => 'number',
                    'minimum'     => 0,
                    'maximum'     => 100,
                ),
            );
        }//1328 //todo shrink
    }
}else die;