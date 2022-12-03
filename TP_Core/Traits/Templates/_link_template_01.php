<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 15:46
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _link_template_01 {
        use _init_rewrite;
        use _init_error;
        /**
         * @description Displays the permalink for the current post.
         * @param int $post
         */
        protected function _the_permalink( $post = 0 ):void{
            echo $this->_esc_url( $this->_apply_filters( 'the_permalink', $this->_get_permalink( $post ), $post ) );
        }//17 from link-template
        /**
         * @description Retrieves a trailing-slashed string if the site is set for adding trailing slashes.
         * @param $string
         * @param string $type_of_url
         * @return mixed
         */
        protected function _user_trailingslashit($string, $type_of_url = ''  ){
            $tp_rewrite = $this->_init_rewrite();
            if ( $tp_rewrite->use_trailing_slashes ) $string = $this->_trailingslashit( $string );
            else $string = $this->_untrailingslashit( $string );
            return $this->_apply_filters( 'user_trailingslashit', $string, $type_of_url );
        }//47 from link-template
        /**
         * @description Displays the permalink anchor for the current post.
         * @param string $mode
         * @return array
         */
        protected function _get_permalink_anchor( $mode = 'id' ):array{
            $post = $this->_get_post();
            $atts = [];
            switch ( strtolower( $mode ) ) {
                case 'title':
                    $title = $this->_sanitize_title( $post->post_title ) . '-' . $post->ID;
                    $atts['title'] = "<a id='$title'></a>";
                    break;
                case 'id':
                default:
                    $atts['id']= "<a id='post_{$post->ID}'></a>";
                    break;
            }
            return $atts;
        }//78 from link-template
        protected function _permalink_anchor( $mode = 'id' ):void{
            echo $this->_get_permalink_anchor($mode);
        }
        /**
         * @description Determine whether post should always use a plain permalink structure.
         * @param null $post
         * @param null $sample
         * @return bool
         */
        protected function _tp_force_plain_post_permalink( $post = null, $sample = null ):bool{
            if ( null === $sample && is_object( $post ) && isset( $post->filter ) && 'sample' === $post->filter)
                $sample = true;
            else {
                $post   = $this->_get_post( $post );
                $sample = $sample ?? false;
            }
            if ( ! $post ) return true;
            $post_status_obj = $this->_get_post_status_object( $this->_get_post_status( $post ) );
            $post_type_obj   = $this->_get_post_type_object( $this->_get_post_type( $post ) );
            if ( ! $post_status_obj || ! $post_type_obj ) return true;
            if (( $post_status_obj->protected && $sample ) || $this->_is_post_status_viewable( $post_status_obj ) ||( $post_status_obj->private && $this->_current_user_can( 'read_post', $post->ID )))
                return false;
            return true;
        }//103 from link-template
        /**
         * @description Retrieves the full permalink for the current post or post ID.
         * @param int $post
         * @param bool $leave_name
         * @return string
         */
        protected function _get_the_permalink( $post = 0, $leave_name = false ):string{
            return $this->_get_permalink( $post, $leave_name );
        }//157 from link-template
        /**
         * @description Retrieves the full permalink for the current post or post ID.
         * @param string|object|int $post
         * @param bool $leave_name
         * @return bool|void
         */
        protected function _get_permalink( $post= 0,$leave_name = false ){
            $rewrite_code = ['%year%','%monthnum%','%day%',
                '%hour%','%minute%','%second%',$leave_name ? '' : '%postname%',
                '%post_id%','%category%','%author%',$leave_name ? '' : '%pagename%',];
            if ( is_object( $post ) && isset( $post->filter ) && 'sample' === $post->filter )
                $sample = true;
            else {
                $post   = $this->_get_post( $post );
                $sample = false;
            }
            if ( empty( $post->ID ) ) return false;
            if ( 'page' === $post->post_type )
                return $this->_get_page_link( $post, $leave_name, $sample );
            elseif ( 'attachment' === $post->post_type )
                return $this->_get_attachment_link( $post, $leave_name );
            elseif ( in_array( $post->post_type, $this->_get_post_types(['_builtin' => false]), true ) )
                return $this->_get_post_permalink( $post, $leave_name, $sample );
            $permalink = $this->_get_option( 'permalink_structure' );
            $permalink = $this->_apply_filters( 'pre_post_link', $permalink, $post, $leave_name );
            if ( $permalink && ! $this->_tp_force_plain_post_permalink( $post )) {
                $category = '';
                if ( strpos( $permalink, '%category%' ) !== false ) {
                    $cats = $this->_get_the_category( $post->ID );
                    if ( $cats ) {
                        $cats = $this->_tp_list_sort($cats,['term_id' => 'ASC',]);
                        $category_object = $this->_apply_filters( 'post_link_category', $cats[0], $cats, $post );
                        $category_object = $this->_get_term( $category_object, 'category' );
                        $category        = $category_object->slug;
                        if ( $category_object->parent )
                            $category = $this->_get_category_parents( $category_object->parent, false, '/', true ) . $category;
                    }
                    if ( empty( $category ) ) {
                        $default_category = $this->_get_term( $this->_get_option( 'default_category' ), 'category' );
                        if ( $default_category && ! $this->_init_error( $default_category ) )
                            $category = $default_category->slug;
                    }
                }
                $author = '';
                if ( strpos( $permalink, '%author%' ) !== false ) {
                    $author_data = $this->_get_user_data( $post->post_author );
                    $author     = $author_data->user_nicename;
                }
                $date = explode( ' ', str_replace( ['-', ':'], ' ', $post->post_date ) );
                $rewrite_replace = [$date[0], $date[1], $date[2], $date[3], $date[4],$date[5],
                    $post->post_name,$post->ID,$category,$author,$post->post_name,];
                $permalink = $this->_home_url( str_replace( $rewrite_code, $rewrite_replace, $permalink ) );
                $permalink = $this->_user_trailingslashit( $permalink, 'single' );
            } else $permalink = $this->_home_url( '?p=' . $post->ID );
            return $this->_apply_filters( 'post_link', $permalink, $post, $leave_name );
        }//170 from link-template
        /**
         * @description Retrieves the permalink for a post of a custom post type.
         * @param int $id
         * @param bool $leave_name
         * @param bool $sample
         * @return mixed
         */
        protected function _get_post_permalink( $id = 0, $leave_name = false, $sample = false ){
            $tp_rewrite = $this->_init_rewrite();
            $post = $this->_get_post( $id );
            if ( $this->_init_error( $post ) ) return $post;
            $post_link = $tp_rewrite->get_extra_permanent_structure( $post->post_type );
            $slug = $post->post_name;
            $force_plain_link = $this->_tp_force_plain_post_permalink( $post );
            $post_type = $this->_get_post_type_object( $post->post_type );
            if ( $post_type->hierarchical ) $slug = $this->_get_page_uri( $post );
            if ( ! empty( $post_link ) && ( ! $force_plain_link || $sample ) ) {
                if ( ! $leave_name ) $post_link = str_replace( "%$post->post_type%", $slug, $post_link );
                $post_link = $this->_home_url( $this->_user_trailingslashit( $post_link ) );
            } else {
                if ( $post_type->query_var && ( isset( $post->post_status ) && ! $force_plain_link ) )
                    $post_link = $this->_add_query_arg( $post_type->query_var, $slug, '' );
                else $post_link = $this->_add_query_arg(['post_type' => $post->post_type, 'p' => $post->ID,],'');
                $post_link = $this->_home_url( $post_link );
            }
            return $this->_apply_filters( 'post_type_link', $post_link, $post, $leave_name, $sample );
        }//319 from link-template
        /**
         * @description Retrieves the permalink for the current page or page ID.
         * @param string|bool $post
         * @param bool $leave_name
         * @param bool $sample
         * @return mixed
         */
        protected function _get_page_link( $post = false, $leave_name = false, $sample = false ){
            $post = (string)$this->_get_post( $post );
            $_post = null;
            if($post instanceof TP_Post ){ $_post = $post;}
            if ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_on_front' ) === $_post->ID )
                $link = $this->_home_url( '/' );
            else $link = $this->_get_page_link( $_post, $leave_name, $sample );
            return $this->_apply_filters( 'page_link', $link, $_post->ID, $sample );
        }//386 from link-template
        /**
         * @description Retrieves the page permalink.
         * @param string|bool $post
         * @param bool $leave_name
         * @param bool $sample
         * @return mixed
         */
        protected function _get_protected_page_link( $post = false, $leave_name = false, $sample = false ){
            $tp_rewrite = $this->_init_rewrite();
            $post = (string)$this->_get_post( $post );
            $_post = null;
            if($post instanceof TP_Post ){ $_post = $post;}
            $force_plain_link = $this->_tp_force_plain_post_permalink( $_post );
            $link = $tp_rewrite->get_page_permanent_structure();
            if ( ! empty( $link ) && ( ( isset( $_post->post_status ) && ! $force_plain_link ) || $sample ) ) {
                if ( ! $leave_name ) $link = str_replace( '%pagename%', $this->_get_page_uri( $_post ), $link );
                $link = $this->_home_url( $link );
                $link = $this->_user_trailingslashit( $link, 'page' );
            } else $link = $this->_home_url( '?page_id=' . $_post->ID );
            return $this->_apply_filters( '_get_page_link', $link, $_post->ID );
        }//423 from link-template
        /**
         * @description Retrieves the permalink for an attachment.
         * @param null $post
         * @param bool $leave_name
         * @return mixed
         */
        protected function _get_attachment_link( $post = null, $leave_name = false ){
            $tp_rewrite = $this->_init_rewrite();
            $link = false;
            $post             = $this->_get_post( $post );
            $force_plain_link = $this->_tp_force_plain_post_permalink( $post );
            $parent_id        = $post->post_parent;
            $parent           = $parent_id ? $this->_get_post( $parent_id ) : false;
            $parent_valid     = true; // Default for no parent.
            if ( $parent_id && ($post->post_parent === $post->ID || ! $parent || ! $this->_is_post_type_viewable( $this->_get_post_type( $parent ) )))
                $parent_valid = false;
            if ( $force_plain_link || ! $parent_valid ) $link = false;
            elseif ($parent && $tp_rewrite->using_permalinks()) {
                if ( 'page' === $parent->post_type ) $parentlink = $this->_get_page_link( $post->post_parent );
                else $parentlink = $this->_get_permalink( $post->post_parent );
                if ( is_numeric( $post->post_name ) || false !== strpos( $this->_get_option( 'permalink_structure' ), '%category%' ) )
                    $name = 'attachment/' . $post->post_name;
                else $name = $post->post_name;
                if ( strpos( $parentlink, '?' ) === false )
                    $link = $this->_user_trailingslashit( $this->_trailingslashit( $parentlink ) . '%postname%' );
                if ( ! $leave_name ) $link = str_replace( '%postname%', $name, $link );
            } elseif (! $leave_name && $tp_rewrite->using_permalinks())
                $link = $this->_home_url( $this->_user_trailingslashit( $post->post_name ) );
            if ( ! $link ) $link = $this->_home_url( '/?attachment_id=' . $post->ID );
            return $this->_apply_filters( 'attachment_link', $link, $post->ID );
        }//467 from link-template
    }
}else die;