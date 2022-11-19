<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Comment;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _link_template_11 {
        use _init_error;
        /**
         * @description Injects rel=shortlink into the head if a shortlink is defined for the current page.
         */
        protected function _tp_shortlink_tp_head():void{
            $shortlink = $this->_tp_get_short_link( 0, 'query' );
            if ( empty( $shortlink ) ) return;
            echo "<link rel='shortlink' href='{$this->_esc_url( $shortlink )}' />\n";
        }//4062 from link-template
        /**
         * @description Sends a Link: rel=shortlink header if a shortlink is defined for the current page.
         */
        protected function _tp_shortlink_header():void{
            if ( headers_sent() ) return;
            $shortlink = $this->_tp_get_short_link( 0, 'query' );
            if ( empty( $shortlink ) ) return;
            header( "Link:<$shortlink>; rel=shortlink", false );
        }//4079 from link-template
        /**
         * @description Displays the shortlink for a post.
         * @param string $text
         * @param string|bool $title
         * @param string $before
         * @param string $after
         * @return null|string
         */
        protected function _get_the_shortlink( $text = '', $title = '', $before = '', $after = '' ):?string{
            $post = $this->_get_post();
            $return = null;
            if ( empty( $text ) ) $text = $this->__( 'This is the short link.' );
            if ( empty( $title ) )  $title = $this->_get_the_title_attribute();
            $shortlink = $this->_tp_get_short_link( $post->ID );
            if ( ! empty( $shortlink ) ) {
                $link = "<a rel='shortlink' href='{$this->_esc_url($shortlink)}' title='$title'>$text</a>";
                $link = $this->_apply_filters( 'the_shortlink', $link, $shortlink, $text, $title );
                $return =  $before.$link.$after;
            }
            return $return;
        }//added
        protected function _the_shortlink( $text = '', $title = '', $before = '', $after = '' ):void{
            echo $this->_get_the_shortlink( $text, $title, $before, $after);
        }//4107 from link-template
        /**
         * @description Retrieves the avatar URL.
         * @param $id_or_email
         * @param null $args
         * @return mixed
         */
        protected function _get_avatar_url( $id_or_email, $args = null ){
            $args = $this->_get_avatar_data( $id_or_email, $args );
            return $args['url'];
        }//4166 from link-template
        /**
         * @description Check if this comment type allows avatars to be retrieved.
         * @param $comment_type
         * @return bool
         */
        protected function _is_avatar_comment_type( $comment_type ):bool{
            $allowed_comment_types = $this->_apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
            return in_array( $comment_type, (array) $allowed_comment_types, true );
        }//4180 from link-template
        /**
         * @description Retrieves default data about the avatar.
         * @param $id_or_email
         * @param null $args
         * @return mixed
         */
        protected function _get_avatar_data( $id_or_email, $args = null ){
            $args = $this->_tp_parse_args($args,['size' => 96,'height' => null,'width' => null,
                    'default' => $this->_get_option( 'avatar_default', 'mystery' ),
                    'force_default' => false,'rating' => $this->_get_option( 'avatar_rating' ),
                    'scheme' => null,'processed_args' => null,'extra_attr' => '',]);
            if ( is_numeric( $args['size'] ) ) {
                $args['size'] = $this->_abs_int( $args['size'] );
                if ( ! $args['size'] ) $args['size'] = 96;
            } else $args['size'] = 96;
            if ( is_numeric( $args['height'] ) ) {
                $args['height'] = $this->_abs_int( $args['height'] );
                if ( ! $args['height'] ) $args['height'] = $args['size'];
            } else $args['height'] = $args['size'];
            if ( is_numeric( $args['width'] ) ) {
                $args['width'] = $this->_abs_int( $args['width'] );
                if ( ! $args['width'] ) $args['width'] = $args['size'];
            } else $args['width'] = $args['size'];
            if ( empty( $args['default'] ) )
                $args['default'] = $this->_get_option( 'avatar_default', 'mystery' );
            switch ( $args['default'] ) {
                case 'mm':
                case 'mystery':
                case 'mysteryman':
                    $args['default'] = 'mm';
                    break;
                case 'gravatar_default':
                    $args['default'] = false;
                    break;
            }
            $args['force_default'] = (bool) $args['force_default'];
            $args['rating'] = strtolower( $args['rating'] );
            $args['found_avatar'] = false;
            $args = $this->_apply_filters( 'pre_get_avatar_data', $args, $id_or_email );
            if ( isset( $args['url'] ) ) return $this->_apply_filters( 'get_avatar_data', $args, $id_or_email );
            $email_hash = '';
            $user       = false;
            $email      = false;
            if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) )
                $id_or_email = $this->_get_comment( $id_or_email );
            if ( is_numeric( $id_or_email ) )
                $user = $this->_get_user_by( 'id', $this->_abs_int( $id_or_email ) );
            elseif ( is_string( $id_or_email ) ) {
                if ( strpos( $id_or_email, '@md5.gravatar.com' ) ) @list( $email_hash ) = explode( '@', $id_or_email );
                else $email = $id_or_email;
            } elseif ( $id_or_email instanceof TP_User ) $user = $id_or_email;
            elseif ( $id_or_email instanceof TP_Post )
                $user = $this->_get_user_by( 'id', (int) $id_or_email->post_author );
            elseif ( $id_or_email instanceof TP_Comment ) {
                if ( ! $this->_is_avatar_comment_type( $this->_get_comment_type( $id_or_email ) ) ) {
                    $args['url'] = false;
                    return $this->_apply_filters( 'get_avatar_data', $args, $id_or_email );
                }
                if ( ! empty( $id_or_email->user_id ) )
                    $user = $this->_get_user_by( 'id', (int) $id_or_email->user_id );
                if (! empty( $id_or_email->comment_author_email ) && ( ! $user || $this->_init_error( $user ) ))
                    $email = $id_or_email->comment_author_email;
            }
            if ( ! $email_hash ) {
                if ( $user ) $email = $user->user_email;
                if ( $email ) $email_hash = md5( strtolower( trim( $email ) ) );
            }
            if ( $email_hash ) {
                $args['found_avatar'] = true;
                $gravatar_server      = hexdec( $email_hash[0] ) % 3;
            } else $gravatar_server = random_int( 0, 2 );
            $url_args = ['s' => $args['size'],'d' => $args['default'],'f' => $args['force_default'] ? 'y' : false,'r' => $args['rating'],];
            if ( $this->_is_ssl() ) $url = 'https://secure.gravatar.com/avatar/' . $email_hash;
            else $url = sprintf( 'http://%d.gravatar.com/avatar/%s', $gravatar_server, $email_hash );
            $url = $this->_add_query_arg(
                $this->_raw_url_encode_deep( array_filter( $url_args ) ),
                $this->_set_url_scheme( $url, $args['scheme'] )
            );
            $args['url'] = $this->_apply_filters( 'get_avatar_url', $url, $id_or_email, $args );
            return $this->_apply_filters( 'get_avatar_data', $args, $id_or_email );
        }//4230 from link-template
        /**
         * @description Retrieves the URL of a file in the theme.
         * @param string $file
         * @return mixed
         */
        protected function _get_theme_file_uri( $file = '' ){
            $file = ltrim( $file, '/' );
            if ( empty( $file ) ) $url = $this->_get_stylesheet_directory_uri();
            elseif ( file_exists( $this->_get_stylesheet_directory() . '/' . $file ) )
                $url = $this->_get_stylesheet_directory_uri() . '/' . $file;
            else $url = $this->_get_template_directory_uri() . '/' . $file;
            return $this->_apply_filters( 'theme_file_uri', $url, $file );
        }//4424 from link-template
        /**
         * @description Retrieves the URL of a file in the parent theme.
         * @param string $file
         * @return mixed
         */
        protected function _get_parent_theme_file_uri( $file = '' ){
            $file = ltrim( $file, '/' );
            if ( empty( $file ) ) $url = $this->_get_template_directory_uri();
            else $url = $this->_get_template_directory_uri() . '/' . $file;
            return $this->_apply_filters( 'parent_theme_file_uri', $url, $file );
        }//4454 from link-template
        /**
         * @description Retrieves the path of a file in the theme.
         * @param string $file
         * @return mixed
         */
        protected function _get_theme_file_path( $file = '' ){
            $file = ltrim( $file, '/' );
            if ( empty( $file ) ) $path = $this->_get_stylesheet_directory();
            elseif ( file_exists( $this->_get_stylesheet_directory() . '/' . $file ) )
                $path = $this->_get_stylesheet_directory() . '/' . $file;
            else  $path = $this->_get_template_directory() . '/' . $file;
            return $this->_apply_filters( 'theme_file_path', $path, $file );
        }//4485 from link-template
        /**
         * @description Retrieves the path of a file in the parent theme.
         * @param string $file
         * @return mixed
         */
        protected function _get_parent_theme_file_path( $file = '' ){
            $file = ltrim( $file, '/' );
            if ( empty( $file ) ) $path = $this->_get_template_directory();
            else $path = $this->_get_template_directory() . '/' . $file;
            return $this->_apply_filters( 'parent_theme_file_path', $path, $file );
        }//4515 from link-template
    }
}else die;