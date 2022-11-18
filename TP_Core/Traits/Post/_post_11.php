<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _post_11{
        use _init_db;
        /**
         * @description Retrieve a thumbnail for an attachment.
         * @param int $post_id
         * @return bool
         */
        protected function _tp_get_attachment_thumb_file( $post_id = 0 ):bool{
            $post_id = (int) $post_id;
            $post    = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $imagedata = $this->_tp_get_attachment_metadata( $post->ID );
            if ( ! is_array( $imagedata ) ) return false;
            $file = $this->_get_attached_file( $post->ID );
            if ( ! empty( $imagedata['thumb'] ) ) {
                $thumbfile = str_replace( $this->_tp_basename( $file ), $imagedata['thumb'], $file );
                if ( file_exists( $thumbfile ) ) return $this->_apply_filters( 'tp_get_attachment_thumb_file', $thumbfile, $post->ID );
            }
            return false;
        }//6722
        /**
         * @description Retrieve URL for an attachment thumbnail.
         * @param int $post_id
         * @return bool
         */
        protected function _tp_get_attachment_thumb_url( $post_id = 0 ):bool{
            $post_id = (int) $post_id;
            $post    = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $url = $this->_tp_get_attachment_url( $post->ID );
            if ( ! $url ) return false;
            $sized = $this->_image_downsize( $post_id, 'thumbnail' );
            if ( $sized ) return $sized[0];
            $thumb = $this->_tp_get_attachment_thumb_file( $post->ID );
            if ( ! $thumb ) return false;
            $url = str_replace( $this->_tp_basename( $url ), $this->_tp_basename( $thumb ), $url );
            return $this->_apply_filters( 'tp_get_attachment_thumb_url', $url, $post->ID );
        }//6762
        /**
         * @description Verifies an attachment is of a given type.
         * @param $type
         * @param null $post
         * @return bool
         */
        protected function _tp_attachment_is( $type, $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $file = $this->_get_attached_file( $post->ID );
            if ( ! $file ) return false;
            if ( 0 === strpos( $post->post_mime_type, $type . '/' ) ) return true;
            $check = $this->_tp_check_file_type( $file );
            if ( empty( $check['ext'] ) ) return false;
            $ext = $check['ext'];
            if ( 'import' !== $post->post_mime_type ) return $type === $ext;
            switch ( $type ) {
                case 'image':
                    $image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'webp' );
                    return in_array( $ext, $image_exts, true );
                case 'audio':
                    return in_array( $ext, $this->_tp_get_audio_extensions(), true );
                case 'video':
                    return in_array( $ext, $this->_tp_get_video_extensions(), true );
                default:
                    return $type === $ext;
            }
        }//6807
        /**
         * @description Determines whether an attachment is an image.
         * @param null $post
         * @return bool
         */
        protected function _tp_attachment_is_image( $post = null ):bool{
            return $this->_tp_attachment_is( 'image', $post );
        }//6866
        /**
         * @description Retrieve the icon for a MIME type or attachment.
         * @param int $mime
         * @return mixed
         */
        protected function _tp_mime_type_icon( $mime = 0 ){
            $icon = null;
            if ( ! is_numeric( $mime ) )
                $icon = $this->_tp_cache_get( "mime_type_icon_$mime" );
            $post_id = 0;
            if ( empty( $icon ) ) {
                $post_mimes = array();
                if ( is_numeric( $mime ) ) {
                    $mime = (int) $mime;
                    $post = $this->_get_post( $mime );
                    if ( $post ) {
                        $post_id = (int) $post->ID;
                        $file    = $this->_get_attached_file( $post_id );
                        $ext     = preg_replace( '/^.+?\.([^.]+)$/', '$1', $file );
                        if ( ! empty( $ext ) ) {
                            $post_mimes[] = $ext;
                            $ext_type     = $this->_tp_ext2type( $ext );
                            if ( $ext_type ) $post_mimes[] = $ext_type;
                        }
                        $mime = $post->post_mime_type;
                    } else $mime = 0;
                } else $post_mimes[] = $mime;
                $icon_files = $this->_tp_cache_get( 'icon_files' );
                if ( ! is_array( $icon_files ) ) {
                    $icon_url = TP_THEME_IMAGES ?: TP_CORE_IMAGES;
                    $icon_dir = $this->_apply_filters( 'icon_dir', $icon_url);
                    $icon_dir_uri = $this->_apply_filters( 'icon_dir_uri', $this->_includes_url( '/Media/Images' ) );
                    $dirs       = $this->_apply_filters( 'icon_dirs', array( $icon_dir => $icon_dir_uri ) );
                    $icon_files = array();
                    while ( $dirs ) {
                        $keys = array_keys( $dirs );
                        $dir  = array_shift( $keys );
                        $uri  = array_shift( $dirs );
                        $dh   = opendir( $dir );
                        if ( $dh ) {
                            while ( false !== $file = readdir( $dh ) ) {
                                $file = $this->_tp_basename( $file );
                                if (strpos($file, '.') === 0) continue;
                                $ext = strtolower( substr( $file, -4 ) );//todo added webp
                                if ( ! in_array( $ext, array( '.png', '.gif', '.jpg', '.webp'), true ) ) {
                                    if ( is_dir( "$dir/$file" ) ) $dirs[ "$dir/$file" ] = "$uri/$file";
                                    continue;
                                }
                                $icon_files[ "$dir/$file" ] = "$uri/$file";
                            }
                            closedir( $dh );
                        }
                    }
                    $this->_tp_cache_add( 'icon_files', $icon_files, 'default', 600 );
                }
                $types = [];
                // Icon wp_basename - extension = MIME wildcard.
                foreach ( $icon_files as $file => $uri )
                    $types[ preg_replace( '/^([^.]*).*$/', '$1', $this->_tp_basename( $file ) ) ] =& $icon_files[ $file ];
                if ( ! empty( $mime ) ) {
                    $post_mimes[] = substr( $mime, 0, strpos( $mime, '/' ) );
                    $post_mimes[] = substr( $mime, strpos( $mime, '/' ) + 1 );
                    $post_mimes[] = str_replace( '/', '_', $mime );
                }
                $matches = $this->_tp_match_mime_types( array_keys( $types ), $post_mimes );
                $matches['default'] = array( 'default' );
                foreach ( $matches as $match => $wilds ) {
                    foreach ( $wilds as $wild ) {
                        if ( ! isset( $types[ $wild ] ) ) continue;
                        $icon = $types[ $wild ];
                        if ( ! is_numeric( $mime ) ) $this->_tp_cache_add( "mime_type_icon_$mime", $icon );
                        break 2;
                    }
                }
            }
            return $this->_apply_filters( 'tp_mime_type_icon', $icon, $mime, $post_id );
        }//6878
        /**
         * @description Check for changed slugs for published post objects and save the old slug.
         * @param $post_id
         * @param $post
         * @param $post_before
         */
        protected function _tp_check_for_changed_slugs( $post_id, $post, $post_before ):void{
            if ( $post->post_name === $post_before->post_name ) return;
            if ( ! ( 'publish' === $post->post_status || ( 'attachment' === $this->_get_post_type( $post ) && 'inherit' === $post->post_status ) ) || $this->_is_post_type_hierarchical( $post->post_type ) )
                return;
            $old_slugs = (array) $this->_get_post_meta( $post_id, '_tp_old_slug' );
            if ( ! empty( $post_before->post_name ) && ! in_array( $post_before->post_name, $old_slugs, true ) )
                $this->_add_post_meta( $post_id, '_tp_old_slug', $post_before->post_name );
            if ( in_array( $post->post_name, $old_slugs, true ) )
                $this->_delete_post_meta( $post_id, '_tp_old_slug', $post->post_name );
        }//7027
        /**
         * @description Check for changed dates for published post objects and save the old date.
         * @param $post_id
         * @param $post
         * @param $post_before
         */
        protected function _tp_check_for_changed_dates( $post_id, $post, $post_before ):void{
            $previous_date = gmdate( 'Y-m-d', strtotime( $post_before->post_date ) );
            $new_date      = gmdate( 'Y-m-d', strtotime( $post->post_date ) );
            if ( $new_date === $previous_date ) return;
            if ( ! ( 'publish' === $post->post_status || ( 'attachment' === $this->_get_post_type( $post ) && 'inherit' === $post->post_status ) ) || $this->_is_post_type_hierarchical( $post->post_type ) )
                return;
            $old_dates = (array) $this->_get_post_meta( $post_id, '_tp_old_date' );
            if ( ! empty( $previous_date ) && ! in_array( $previous_date, $old_dates, true ) )
                $this->_add_post_meta( $post_id, '_tp_old_date', $previous_date );
            if ( in_array( $new_date, $old_dates, true ) )
                $this->_delete_post_meta( $post_id, '_tp_old_date', $new_date );
        }//7070
        /**
         * @description Retrieve the private post SQL based on capability.
         * @param $post_type
         * @return string
         */
        protected function _get_private_posts_cap_sql( $post_type ):string{
            return $this->_get_posts_by_author_sql( $post_type, false );
        }//7111
        /**
         * @description Retrieve the post SQL based on capability, author, and type.
         * @param $post_type
         * @param bool $full
         * @param null $post_author
         * @param bool $public_only
         * @return string
         */
        protected function _get_posts_by_author_sql( $post_type, $full = true, $post_author = null, $public_only = false ):string{
            $this->tpdb = $this->_init_db();
            if ( is_array( $post_type ) ) $post_types = $post_type;
            else $post_types = array( $post_type );
            $post_type_clauses = array();
            foreach ( $post_types as $sub_post_type ) {
                $post_type_obj = $this->_get_post_type_object( $sub_post_type );
                if ( ! $post_type_obj ) continue;
                $cap = $this->_apply_filters_deprecated( 'pub_priv_sql_capability', array( '' ), '3.2.0' );
                if ( ! $cap ) $cap = $this->_current_user_can( $post_type_obj->cap->read_private_posts );
                $post_status_sql = "post_status = 'publish'";
                if ( false === $public_only ) {
                    if ( $cap ) $post_status_sql .= " OR post_status = 'private'";
                    elseif ( $this->_is_user_logged_in() ) {
                        $id = $this->_get_current_user_id();
                        if ( null === $post_author || ! $full ) {
                            $post_status_sql .= " OR post_status = 'private' AND post_author = $id";
                        } elseif ( $id === (int) $post_author ) $post_status_sql .= " OR post_status = 'private'";
                    }
                }
                $post_type_clauses[] = "( post_type = '" . $post_type . "' AND ( $post_status_sql ) )";
            }
            if ( empty( $post_type_clauses ) ) return $full ? 'WHERE 1 = 0' : '1 = 0';
            $sql = '( ' . implode( ' OR ', $post_type_clauses ) . ' )';
            if ( null !== $post_author )
                $sql .= $this->tpdb->prepare( ' AND post_author = %d', $post_author );
            if ( $full )  $sql = 'WHERE ' . $sql;
            return $sql;
        }//7132
        /**
         * @description Retrieves the most recent time that a post on the site was published.
         * @param string $timezone
         * @param string $post_type
         * @return mixed
         */
        protected function _get_last_postdate( $timezone = 'server', $post_type = 'any' ){
            $lastpostdate = $this->_get_last_post_time( $timezone, 'date', $post_type );
            return $this->_apply_filters( 'get_lastpostdate', $lastpostdate, $timezone, $post_type );
        }//7217
        /**
         * @description Get the most recent time that a post on the site was modified.
         * @param string $timezone
         * @param string $post_type
         * @return mixed
         */
        protected function _get_last_post_modified( $timezone = 'server', $post_type = 'any' ){
            $lastpostmodified = $this->_apply_filters( 'pre_get_lastpostmodified', false, $timezone, $post_type );
            if ( false !== $lastpostmodified ) return $lastpostmodified;
            $lastpostmodified = $this->_get_last_post_time( $timezone, 'modified', $post_type );
            $lastpostdate     = $this->_get_last_postdate( $timezone, $post_type );
            if ( $lastpostdate > $lastpostmodified ) $lastpostmodified = $lastpostdate;
            return $this->_apply_filters( 'get_lastpostmodified', $lastpostmodified, $timezone, $post_type );
        }//7251
    }
}else die;