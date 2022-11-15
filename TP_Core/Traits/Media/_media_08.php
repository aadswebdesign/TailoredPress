<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
use TP_Admin\Traits\AdminImage\_adm_image_01;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _media_08 {
        use _init_db;
        use _init_queries;
        //from admin
        use _adm_image_01;
        /**
         * @description Retrieve the image sources from galleries from a post's content, if present
         * @param int $post
         * @return mixed
         */
        protected function _get_post_galleries_images( $post = 0 ){
            $galleries = $this->_get_post_galleries( $post, false );
            return $this->_tp_list_pluck( $galleries, 'src' );
        }//4910
        /**
         * @description Checks a post's content for galleries and return the image srcs for the first found gallery
         * @param int $post
         * @return array
         */
        protected function _get_post_gallery_images( $post = 0 ):array{
            $gallery = $this->_get_post_gallery( $post, false );
            return empty( $gallery['src'] ) ? [] : $gallery['src'];
        }//4925
        /**
         * @description Maybe attempts to generate attachment metadata, if missing.
         * @param $attachment
         */
        protected function _tp_maybe_generate_attachment_metadata( $attachment ):void{
            if ( empty( $attachment ) || empty( $attachment->ID ) ) return;
            $attachment_id = (int) $attachment->ID;
            $file          = $this->_get_attached_file( $attachment_id );
            $meta          = $this->_tp_get_attachment_metadata( $attachment_id );
            if ( empty( $meta ) && file_exists( $file ) ) {
                $_meta = $this->_get_post_meta( $attachment_id );
                $_lock = 'tp_generating_att_' . $attachment_id;
                if ( ! array_key_exists( '_tp_attachment_metadata', $_meta ) && ! $this->_get_transient( $_lock ) ) {
                    $this->_set_transient( $_lock, $file );
                    $this->_tp_update_attachment_metadata( $attachment_id, $this->_tp_generate_attachment_metadata( $attachment_id, $file ) );
                    $this->_delete_transient( $_lock );
                }
            }
        }//4937
        /**
         * @description Tries to convert an attachment URL into a post ID.
         * @param $url
         * @return int
         */
        protected function _attachment_url_to_post_id( $url ):int{
            $tpdb = $this->_init_db();
            $dir  = $this->_tp_get_upload_dir();
            $path = $url;
            $site_url   = parse_url( $dir['url'] );
            $image_path = parse_url( $path );
            if ( isset( $image_path['scheme'] ) && ( $image_path['scheme'] !== $site_url['scheme'] ) )
                $path = str_replace( $image_path['scheme'], $site_url['scheme'], $path );
            if ( 0 === strpos( $path, $dir['baseurl'] . '/' ) )
                $path = substr( $path, strlen( $dir['baseurl'] . '/' ) );
            $sql = $tpdb->prepare( TP_SELECT . " post_id, meta_value FROM $tpdb->post_meta WHERE meta_key = '_tp_attached_file' AND meta_value = %s", $path );
            $results = $tpdb->get_results( $sql );
            $post_id = null;
            if ( $results ) {
                $post_id = reset( $results )->post_id;
                if ( count( $results ) > 1 ) {
                    foreach ( $results as $result ) {
                        if ( $path === $result->meta_value ) {
                            $post_id = $result->post_id;
                            break;
                        }
                    }
                }
            }
            return (int) $this->_apply_filters( 'attachment_url_to_postid', $post_id, $url );
        }//4968
        /**
         * @description Returns the URLs for CSS files used in an iframe-sandbox'd TinyMCE media view.
         * @return array
         */
        protected function _tp_view_media_sandbox_styles():array{
            $version        = 'ver=' . $this->_get_bloginfo( 'version' );
            $media_element   = $this->_includes_url( "path/to/media_element/media_element_player-legacy.min.css?$version" );//todo
            $tp_media_element = $this->_includes_url( "path/to/media_element/tp-media_element.css?$version" );//todo
            return array( $media_element, $tp_media_element );
        }//5026
        /**
         * @description Registers the personal data exporter for media.
         * @param $exporters
         * @return mixed
         */
        protected function _tp_register_media_personal_data_exporter( $exporters ){
            $exporters['tailoredpress-media'] = ['exporter_friendly_name' => $this->__( 'TailoredPress Media' ),
                'callback' => [$this,'tp_media_personal_data_exporter'],];
            return $exporters;
        }//5040
        /**
         * @description Finds and exports attachments associated with an email address.
         * @param $email_address
         * @param int $page
         * @return array
         */
        protected function _get_media_personal_data_exporter( $email_address, $page = 1 ):array{
            $number = 50;
            $page   = (int) $page;
            $data_to_export = [];
            $user = $this->_get_user_by( 'email', $email_address );
            if ( false === $user ) return ['data' => $data_to_export,'done' => true,];
            $post_query = $this->_init_query(['author'=> $user->ID,'posts_per_page' => $number,'paged'=> $page,
                'post_type'=> 'attachment','post_status'=> 'any','orderby' => 'ID','order' => 'ASC',]);
            foreach ( (array) $post_query->posts as $post ) {
                $attachment_url = $this->_tp_get_attachment_url( $post->ID );
                if ( $attachment_url ) {
                    $post_data_to_export = [['name' => $this->__( 'URL' ),'value' => $attachment_url,],];
                    $data_to_export[] = ['group_id' => 'media',
                        'group_label' => $this->__( 'Media' ),'group_description' => $this->__( 'User&#8217;s media data.' ),
                        'item_id' => "post-{$post->ID}",'data' => $post_data_to_export,];
                }
            }
            $done = $post_query->max_num_pages <= $page;
            return ['data' => $data_to_export,'done' => $done,];
        }//5058
        public function tp_media_personal_data_exporter( $email_address, $page = 1 ):array{
            return $this->_get_media_personal_data_exporter( $email_address, $page);
        }
        /**
         * @description Add additional default image sub-sizes.
         */
        public function tp_add_additional_image_sizes():void{
            $this->_add_image_size( '1536x1536', 1536, 1536 );
            $this->_add_image_size( '2048x2048', 2048, 2048 );
        }//5127
        /**
         * @description Callback to enable showing of the user error when uploading .heic images.
         * @param $upload_settings
         * @return mixed
         */
        protected function _tp_show_heic_upload_error( $upload_settings ){
            $plupload_settings['heic_upload_error'] = true;
            return $plupload_settings[$upload_settings];
        }//5142
        /**
         * @description Allows PHP's getimagesize() to be debuggable when necessary.
         * @param $filename
         * @param array|null $image_info
         * @return array|bool
         */
        protected function _tp_get_image_size( $filename, array &$image_info = null ){
            if ( defined( 'TP_DEBUG' ) && TP_DEBUG && ! defined( 'TP_RUN_CORE_TESTS' )) {
                if ( 2 === func_num_args() ) $info = getimagesize( $filename, $image_info );
                else $info = getimagesize( $filename );
            }else if ( 2 === func_num_args() ) $info = @getimagesize( $filename, $image_info );
            else $info = @getimagesize( $filename );
            if ( false !== $info ) return $info;
            if ( 'image/webp' === $this->_tp_get_image_mime( $filename ) ) {
                $webp_info = $this->_tp_get_webp_info( $filename );
                $width     = $webp_info['width'];
                $height    = $webp_info['height'];
                if ( $width && $height )
                    return [$width, $height, IMAGE_TYPE_WEBP,
                        sprintf("width='%d' height='%d'",$width,$height),
                        'mime' => 'image/webp',];
            }
            return false;
        }//5157
     }
}else die;