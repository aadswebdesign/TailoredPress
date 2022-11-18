<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:02
 */
namespace TP_Core\Traits\Pluggables;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\Diff\TextDiff;
use TP_Core\Libs\Diff\TP_TextDiff_Renderer_Table;
if(ABSPATH){
    trait _pluggable_05 {
        use _init_db;
        /**
         * @description Generates a random password drawn from the defined set of characters.
         * @param int $length
         * @param bool $special_chars
         * @param bool $extra_special_chars
         * @return mixed
         */
        protected function _tp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ){
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            if ( $special_chars ) $chars .= '!@#$%^&*()';
            if ( $extra_special_chars ) $chars .= '-_ []{}<>~`+=,.;:/?|';
            $password = '';
            for ( $i = 0; $i < $length; $i++ ) $password .= $chars[$this->_tp_rand(0, strlen($chars) - 1)];
            return $this->_apply_filters( 'random_password', $password, $length, $special_chars, $extra_special_chars );
        }//2498
        /**
         * @description Generates a random number.
         * @param int $min
         * @param int $max
         * @return number
         */
        protected function _tp_rand( $min = 0, $max = 0 ){
            $max_random_number = 3000000000 === 2147483647 ? (float) '4294967295' : 4294967295; // 4294967295 = 0xffffffff
            $min = (int) $min;
            $max = (int) $max;
            static $use_random_int_functionality = true;
            if ( $use_random_int_functionality ) {
                try {
                    $_max = ( 0 !== $max ) ? $max : $max_random_number;
                    // wp_rand() can accept arguments in either order, PHP cannot.
                    $_max = max( $min, $_max );
                    $_min = min( $min, $_max );
                    $val  = random_int( $_min, $_max );
                    if ( false !== $val )  return $this->_abs_int( $val );
                    else $use_random_int_functionality = false;
                } catch ( \Error $e ) {
                    $use_random_int_functionality = false;
                } catch ( \Exception $e ) {
                    $use_random_int_functionality = false;
                }
            }
            if ( strlen( $this->tp_rand_value ) < 8 ) {
                if ( defined( 'TP_SETUP_CONFIG' ) ) static $seed = '';
                else $seed = $this->_get_transient( 'random_seed' );
                $this->tp_rand_value  = md5( uniqid( microtime() . mt_rand(), true ) . $seed );
                $this->tp_rand_value .= sha1( $this->tp_rand_value );
                $this->tp_rand_value .= sha1( $this->tp_rand_value . $seed );
                $seed       = md5( $seed . $this->tp_rand_value );
                if ( ! defined( 'TP_SETUP_CONFIG' ) && ! defined( 'TP_INSTALLING' ) ) {
                    $this->_set_transient( 'random_seed', $seed );
                }
            }
            $value = substr( $this->tp_rand_value, 0, 8 );
            $rnd_value = substr( $this->tp_rand_value, 8 );//todo wasn't used anywhere, added it to the if statement
            $value = abs( hexdec( $value ) );
            if ( 0 !== $max ) $value = $min + ( $max - $min + 1 ) * $rnd_value / ( $max_random_number + 1 );
            return abs( (int) $value );
        }//2540
        /**
         * @description  Updates the user's password with a new encrypted one.
         * @param $password
         * @param $user_id
         */
        protected function _tp_set_password( $password, $user_id ):void{
            $this->tpdb = $this->_init_db();
            $hash = $this->_tp_hash_password( $password );
            $this->tpdb->update($this->tpdb->users,['user_pass' => $hash,'user_activation_key' => '',],['ID' => $user_id]);
            $this->_clean_user_cache( $user_id );
        }//2624
        /**
         * @description Retrieve the avatar `<img>` tag for a user, email address, MD5 hash, comment, or post.
         * @param $id_or_email
         * @param int $size
         * @param string $default
         * @param string $alt
         * @param array ...$args
         * @return mixed
         */
        protected function _get_avatar( $id_or_email, $size = 96, $default = '', $alt = '', ...$args){
            $defaults = ['size' => 96,'height' => null,'width' => null,
                'default' => $this->_get_option( 'avatar_default', 'mystery' ),
                'force_default' => false,'rating' => $this->_get_option( 'avatar_rating' ),
                'scheme' => null,'alt' => '','class' => null,'force_display' => false,
                'loading' => null,'extra_attr' => '',];
            if ( $this->_tp_lazy_loading_enabled( 'img', 'get_avatar' ) )
                $defaults['loading'] = $this->_tp_get_loading_attr_default( 'get_avatar' );
            $args['size']    = (int) $size;
            $args['default'] = $default;
            $args['alt']     = $alt;
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( empty( $args['height'] ) ) $args['height'] = $args['size'];
            if ( empty( $args['width'] ) ) $args['width'] = $args['size'];
            if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) $id_or_email = $this->_get_comment( $id_or_email );
            $avatar = $this->_apply_filters( 'pre_get_avatar', null, $id_or_email, $args );
            if ( ! is_null( $avatar ) )
                return $this->_apply_filters( 'get_avatar', $avatar, $id_or_email, $args['size'], $args['default'], $args['alt'], $args );
            if ( ! $args['force_display'] && ! $this->_get_option( 'show_avatars' ) ) return false;
            $url2x = $this->_get_avatar_url( $id_or_email, array_merge( $args, array( 'size' => $args['size'] * 2 ) ) );
            $args = $this->_get_avatar_data( $id_or_email, $args );
            $url = $args['url'];
            if ( ! $url || $this->_init_error( $url ) ) return false;
            $class = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );
            if ( ! $args['found_avatar'] || $args['force_default'] ) $class[] = 'avatar-default';
            if ( $args['class'] ) {
                if ( is_array( $args['class'] ) ) $class = array_merge( $class, $args['class'] );
                else $class[] = $args['class'];
            }
            $extra_attr = $args['extra_attr'];
            $loading    = $args['loading'];
            if ( in_array( $loading, array( 'lazy', 'eager' ), true ) && ! preg_match( '/\loading\s*=/', $extra_attr ) ) {
                if ( ! empty( $extra_attr ) ) $extra_attr .= ' ';
                $extra_attr .= "loading='{$loading}'";
            }
            $avatar = sprintf(
                "<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
                $this->_esc_attr( $args['alt'] ),
                $this->_esc_url( $url ),
                $this->_esc_url( $url2x ) . ' 2x',
                $this->_esc_attr( implode( ' ', $class ) ),
                (int) $args['height'],
                (int) $args['width'],
                $extra_attr
            );
            return $this->_apply_filters( 'get_avatar', $avatar, $id_or_email, $args['size'], $args['default'], $args['alt'], $args );
        }//2678
        /**
         * @descriptionDisplays a human readable HTML representation of the difference between two strings.
         * @param $left_string
         * @param $right_string
         * @param null $args
         * @return string
         */
	    protected function _tp_text_diff( $left_string, $right_string, $args = null ):string{
            $defaults = ['title' => '','title_left' => '','title_right' => '','show_split_view' => true,];
            $args     = $this->_tp_parse_args( $args, $defaults );
            $left_string  = $this->_normalize_whitespace( $left_string );
            $right_string = $this->_normalize_whitespace( $right_string );
            $left_lines  = explode( "\n", $left_string );
            $right_lines = explode( "\n", $right_string );
            $text_diff   = new TextDiff( $left_lines, $right_lines );
            $renderer    = new TP_TextDiff_Renderer_Table( $args );
            $diff        = $renderer->render( $text_diff );
            if ( ! $diff ) return '';
            $is_split_view       = ! empty( $args['show_split_view'] );
            $is_split_view_class = $is_split_view ? ' is-split-view' : '';
            $r = "<table class='diff$is_split_view_class'>\n";
            if ( $args['title'] ) $r .= "<caption class='diff-title'>$args[title]</caption>\n";
            if ( $args['title_left'] || $args['title_right'] ) $r .= '<thead>';
            if ( $args['title_left'] || $args['title_right'] ) {
                $th_or_td_left  = empty( $args['title_left'] ) ? 'td' : 'th';
                $th_or_td_right = empty( $args['title_right'] ) ? 'td' : 'th';
                $r .= "<tr class='diff-sub-title'>\n";
                $r .= "\t<$th_or_td_left>$args[title_left]</$th_or_td_left>\n";
                if ( $is_split_view ) $r .= "\t<$th_or_td_right>$args[title_right]</$th_or_td_right>\n";
                $r .= "</tr>\n";
            }
            if ( $args['title_left'] || $args['title_right'] ) $r .= "</thead>\n";
            $r .= "<tbody>\n$diff\n</tbody>\n";
            $r .= '</table>';
            return $r;
        }//2840
    }
}else die;