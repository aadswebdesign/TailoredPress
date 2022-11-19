<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 21:22
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _author_template_02 {
        use _init_rewrite, _init_db;
        /**
         * @description Retrieves an HTML link to the author page of the current post's author.
         * @return string
         */
        protected function _get_the_author_posts_link():string{
            if ( ! is_object( $this->tp_author_data ) ) return '';
            $link = sprintf("<a href='%1\$s' title='%2\$s' rel='author'>%3\$s</a>",
                $this->_esc_url( $this->_get_author_posts_url( $this->tp_author_data->ID, $this->tp_author_data->user_nicename ) ),
                /* translators: %s: Author's display name. */
                $this->_esc_attr( sprintf( $this->__( 'Posts by %s' ), $this->_get_the_author() ) ),
                $this->_get_the_author()
            );
            return $this->_apply_filters( 'the_author_posts_link', $link );
        }//294 from author-template
        /**
         * @description Displays an HTML link to the author page of the current post's author.
         */
        protected function _the_author_posts_link():void{
            echo $this->_get_the_author_posts_link();
        }//326 from author-template
        /**
         * @description Retrieve the URL to the author page for the user with the ID provided.
         * @param $author_id
         * @param string $author_nice_name
         * @return bool|mixed|string
         */
        protected function _get_author_posts_url( $author_id, $author_nice_name = '' ){
            $this->tp_rewrite = $this->_init_rewrite();
            $auth_ID = (int) $author_id;
            $link    = $this->tp_rewrite->get_author_permanent_structure();

            if ( empty( $link ) ) {
                $file = $this->_home_url( '/' );
                $link = $file . '?author=' . $auth_ID;
            } else {
                if ( '' === $author_nice_name ) {
                    $user = $this->_get_user_data( $author_id );
                    if ( ! empty( $user->user_nicename ) ) {
                        $author_nice_name = $user->user_nicename;
                    }
                }
                $link = str_replace( '%author%', $author_nice_name, $link );
                $link = $this->_home_url( $this->_user_trailingslashit( $link ) );
            }
            $link = $this->_apply_filters( 'author_link', $link, $author_id, $author_nice_name );
            return $link;
        }//344 from author-template
        /**
         * @description List all the authors of the site, with several options available.
         * @param array ...$args
         * @return string
         */
        protected function _get_list_authors( ...$args):string{
            $this->tpdb = $this->_init_db();
            $defaults = ['orderby' => 'name','order' => 'ASC','number' => '','option_count' => false,
                'exclude_admin' => true,'show_full_name' => false,'hide_empty' => true,'feed' => '',
                'feed_image' => '','feed_type' => '','echo' => true,'style' => 'list',
                'html' => true,'exclude' => '','include' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            $return = '';
            $query_args           = $this->_tp_array_slice_assoc( $args, array( 'orderby', 'order', 'number', 'exclude', 'include' ) );
            $query_args['fields'] = 'ids';
            $authors              = $this->_get_users( $query_args );
            $author_count = array();
            foreach ( (array) $this->tpdb->get_results( TP_SELECT . " DISTINCT post_author, COUNT(ID) AS count FROM $this->tpdb->posts WHERE " . $this->_get_private_posts_cap_sql( 'post' ) . ' GROUP BY post_author' ) as $row )
                $author_count[ $row->post_author ] = $row->count;
            foreach ( $authors as $author_id ) {
                $posts = $author_count[ $author_id ] ?? 0;
                if ( ! $posts && $args['hide_empty'] ) continue;
                $author = $this->_get_user_data( $author_id );
                if ( $args['exclude_admin'] && 'admin' === $author->display_name ) continue;
                if ( $args['show_full_name'] && $author->first_name && $author->last_name )
                    $name = "$author->first_name $author->last_name";
                else $name = $author->display_name;
                if ( ! $args['html'] ) {
                    $return .= $name . ', ';
                    continue;
                }
                if ( 'list' === $args['style'] ) $return .= '<li>';
                $link = sprintf(
                    "<a href='%1\$s' title='%2\$s'>%3\$s</a>",
                    $this->_esc_url( $this->_get_author_posts_url( $author->ID, $author->user_nicename ) ),
                    $this->_esc_attr( sprintf( $this->__( 'Posts by %s' ), $author->display_name ) ),
                    $name
                );
                if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
                    $link .= ' ';
                    if ( empty( $args['feed_image'] ) ) $link .= '(';
                    $link .= "<a href='{$this->_get_author_feed_link( $author->ID, $args['feed_type'] )}'";
                    $alt = '';
                    if ( ! empty( $args['feed'] ) ) {
                        $alt  = " alt='{$this->_esc_attr( $args['feed'] )}'";
                        $name = $args['feed'];
                    }
                    $link .= '>';
                    if ( ! empty( $args['feed_image'] ) )
                        $link .= "<img src='{$this->_esc_url( $args['feed_image'] )}' style='border: none;' $alt />";
                    else $link .= $name;
                    $link .= '</a>';
                    if ( empty( $args['feed_image'] ) ) $link .= ')';
                }
                if ( $args['option_count'] ) {
                    $link .= ' (' . $posts . ')';
                }
                $return .= $link;
                $return .= ( 'list' === $args['style'] ) ? '</li>' : ', ';
            }
            $return = rtrim( $return, ', ' );
            return $return;
        }//413 from author-template
        protected function _the_list_authors( ...$args):void{
            echo $this->_get_list_authors($args);
        }
        /**
         * @description Determines whether this site has more than one author.
         * @return mixed
         */
        protected function _is_multi_author(){
            $this->tpdb = $this->_init_db();
            $is_multi_author = $this->_get_transient( 'is_multi_author' );
            if ( false === $is_multi_author ) {
                $rows            = $this->tpdb->get_col( TP_SELECT . " DISTINCT post_author FROM $this->tpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 2" );
                $is_multi_author = 1 < count( $rows ) ? 1 : 0;
                $this->_set_transient( 'is_multi_author', $is_multi_author );
            }
            return $this->_apply_filters( 'is_multi_author', (bool) $is_multi_author );

        }//544 from author-template
        /**
         * @description Helper function to clear the cache for number of authors.
         */
        protected function _clear_multi_author_cache():void{
            $this->_delete_transient( 'is_multi_author' );
        }//570 from author-template
    }
}else die;