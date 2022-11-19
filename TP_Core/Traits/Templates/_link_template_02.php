<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _link_template_02 {
        use _init_rewrite;
        use _init_error;
        /**
         * @description Retrieves the permalink for the year archives.
         * @param $year
         * @return mixed
         */
        protected function _get_year_link( $year ){
            $tp_rewrite = $this->_init_rewrite();
            if ( ! $year ) $year = $this->_current_time( 'Y' );
            $yearlink = $tp_rewrite->get_year_permanent_structure();
            if ( ! empty( $yearlink ) ) {
                $yearlink = str_replace( '%year%', $year, $yearlink );
                $yearlink = $this->_home_url( $this->_user_trailingslashit( $yearlink, 'year' ) );
            } else $yearlink = $this->_home_url( '?m=' . $year );
            return $this->_apply_filters( 'year_link', $yearlink, $year );
        }//542 from link-template
        /**
         * @description Retrieves the permalink for the month archives with year.
         * @param $year
         * @param $month
         * @return mixed
         */
        protected function _get_month_link( $year, $month ){
            $tp_rewrite = $this->_init_rewrite();
            if ( ! $year ) $year = $this->_current_time( 'Y' );
            if ( ! $month ) $month = $this->_current_time( 'm' );
            $monthlink = $tp_rewrite->get_month_permanent_structure();
            if ( ! empty( $monthlink ) ) {
                $monthlink = str_replace(array('%year%', '%monthnum%'), array($year, $this->_zero_ise((int)$month, 2)), $monthlink);
                $monthlink = $this->_home_url( $this->_user_trailingslashit( $monthlink, 'month' ) );
            } else $monthlink = $this->_home_url( '?m=' . $year . $this->_zero_ise( $month, 2 ) );
            return $this->_apply_filters( 'month_link', $monthlink, $year, $month );
        }//577 from link-template
        /**
         * @description Retrieves the permalink for the day archives with year and month.
         * @param $year
         * @param $month
         * @param $day
         * @return mixed
         */
        protected function _get_day_link( $year, $month, $day ){
            $tp_rewrite = $this->_init_rewrite();
            if ( ! $year ) $year = $this->_current_time( 'Y' );
            if ( ! $month ) $month = $this->_current_time( 'm' );
            if ( ! $day ) $day = $this->_current_time( 'j' );
            $daylink = $tp_rewrite->get_day_permanent_structure();
            if ( ! empty( $daylink ) ) {
                $daylink = str_replace(array('%year%', '%monthnum%', '%day%'), array($year, $this->_zero_ise((int)$month, 2), $this->_zero_ise((int)$day, 2)), $daylink);
                $daylink = $this->_home_url( $this->_user_trailingslashit( $daylink, 'day' ) );
            } else $daylink = $this->_home_url( '?m=' . $year . $this->_zero_ise( $month, 2 ) . $this->_zero_ise( $day, 2 ) );
            return $this->_apply_filters( 'day_link', $daylink, $year, $month, $day );
        }//618 from link-template
        /**
         * @description Displays the permalink for the feed type.
         * @param $anchor
         * @param string $feed
         */
        protected function _the_feed_link( $anchor, $feed = '' ):void{
            $link = "<a href='{$this->_esc_url( $this->_get_feed_link( $feed ) )}'>$anchor</a>";
            echo $this->_apply_filters( 'the_feed_link', $link, $feed );
        }//662 from link-template
        /**
         * @description Retrieves the permalink for the feed type.
         * @param string $feed
         * @return mixed
         */
        protected function _get_feed_link($feed = ''){
            $tp_rewrite = $this->_init_rewrite();
            $permalink = $tp_rewrite->get_feed_permanent_structure();
            if ( $permalink ) {
                if ( false !== strpos( $feed, 'comments_' ) ) {
                    $feed      = str_replace( 'comments_', '', $feed );
                    $permalink = $tp_rewrite->get_comment_feed_permanent_structure();
                }
                if ( $this->_get_default_feed() === $feed ) {$feed = '';}
                $permalink = str_replace( '%feed%', $feed, $permalink );
                $permalink = preg_replace( '#/+#', '/', "/$permalink" );
                $output    = $this->_home_url( $this->_user_trailingslashit( $permalink, 'feed' ) );
            } else {
                if ( empty( $feed ) ) { $feed = $this->_get_default_feed();}
                if ( false !== strpos( $feed, 'comments_' ) ) { $feed = str_replace( 'comments_', 'comments-', $feed );}
                $output = $this->_home_url( "?feed={$feed}" );
            }
            return $this->_apply_filters( 'feed_link', $output, $feed );

        }//688 from link-template
        /**
         * @description Retrieves the permalink for the post comments feed.
         * @param int $post_id
         * @param string $feed
         * @return string
         */
        protected function _get_post_comments_feed_link( $post_id = 0,$feed = '' ):string{
            $post_id = (int)$this->_abs_int( $post_id );
            if ( ! $post_id ) $post_id = (int)$this->_get_the_ID();
            if ( empty( $feed ) ) $feed = $this->_get_default_feed();
            $post = $this->_get_post( $post_id );
            if ( ! $post instanceof TP_Post ) return '';
            $unattached = 'attachment' === $post->post_type && 0 === (int) $post->post_parent;
            if ( $this->_get_option( 'permalink_structure' ) ) {
                if ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_on_front' ) === $post_id )
                    $url = $this->_get_page_link( $post_id );
                else $url = $this->_get_permalink( $post_id );
                if ( $unattached ) {
                    $url = $this->_home_url( '/feed/' );
                    if ( $this->_get_default_feed() !== $feed ) $url .= "$feed/";
                    $url = $this->_add_query_arg( 'attachment_id', $post_id, $url );
                } else {
                    $url = $this->_trailingslashit( $url ) . 'feed';
                    if ( $this->_get_default_feed() !== $feed ) $url .= "/$feed";
                    $url = $this->_user_trailingslashit( $url, 'single_feed' );
                }
            } else if ( $unattached ) $url = $this->_add_query_arg(['feed' => $feed,'attachment_id' => $post_id,],$this->_home_url( '/' ));
            elseif ( 'page' === $post->post_type ) $url = $this->_add_query_arg(['feed' => $feed,'page_id' => $post_id,],$this->_home_url( '/' ));
            else $url = $this->_add_query_arg(['feed' => $feed,'p' => $post_id,], $this->_home_url( '/' ));
            return $this->_apply_filters( 'post_comments_feed_link', $url );
        }//740 from link-template
        /**
         * @description Displays the comment feed link for a post.
         * @param string $link_text
         * @param string $post_id
         * @param string $feed
         */
        protected function _post_comments_feed_link( $link_text = '', $post_id = '', $feed = '' ):void{
            $url = $this->_get_post_comments_feed_link( $post_id, $feed );
            if ( empty( $link_text ) ) $link_text = $this->__( 'Comments Feed' );
            $link = "<a href='{$this->_esc_url(  $url )}'>$link_text</a>";
            echo $this->_apply_filters( 'post_comments_feed_link_html', $link, $post_id, $feed );
        }//832 from link-template
        /**
         * @description Retrieves the feed link for a given author.
         * @param $author_id
         * @param $feed
         * @return string
         */
        protected function _get_author_feed_link( $author_id , $feed = ''):string{
            $author_id = (int) $author_id;
            $permalink_structure = $this->_get_option( 'permalink_structure' );
            if ( empty( $feed ) ) $feed = $this->_get_default_feed();
            if ( ! $permalink_structure ) $link = $this->_home_url( "?feed=$feed&amp;author=" . $author_id );
            else {
                $link = $this->_get_author_posts_url( $author_id );
                if ( $this->_get_default_feed() === $feed ) $feed_link = 'feed';
                else $feed_link = "feed/$feed";
                $link = $this->_trailingslashit( $link ) . $this->_user_trailingslashit( $feed_link, 'feed' );
            }
            $link = $this->_apply_filters( 'author_feed_link', $link, $feed );
            return $link;
        }//865 from link-template
        /**
         * @description Retrieves the feed link for a category.
         * @param $cat
         * @param string $feed
         * @return string
         */
        protected function _get_category_feed_link( $cat,$feed ='' ):string{
            return $this->_get_term_feed_link( $cat, 'category', $feed );
        }//912 from link-template
        /**
         * @description Retrieves the feed link for a term.
         * @param $term
         * @param string $taxonomy
         * @param string $feed
         * @return bool|string
         */
        protected function _get_term_feed_link( $term, $taxonomy = '', $feed = '' ){
            if ( ! is_object( $term ) ) $term = (int) $term;
            $term = $this->_get_term( $term, $taxonomy );
            if ( empty( $term ) || $this->_init_error( $term ) ) return false;
            $taxonomy = $term->taxonomy;
            if ( empty( $feed ) ) $feed = $this->_get_default_feed();
            $permalink_structure = $this->_get_option( 'permalink_structure' );
            if ( ! $permalink_structure ) {
                if ( 'category' === $taxonomy )
                    $link = $this->_home_url( "?feed=$feed&amp;cat=$term->term_id" );
                 elseif ( 'post_tag' === $taxonomy )
                    $link = $this->_home_url( "?feed=$feed&amp;tag=$term->slug" );
                 else {
                    $t    = $this->_get_taxonomy( $taxonomy );
                    $link = $this->_home_url( "?feed=$feed&amp;$t->query_var=$term->slug" );
                }
            } else {
                $link = $this->_get_term_link( $term, $term->taxonomy );
                if ( $this->_get_default_feed() === $feed ) $feed_link = 'feed';
                else $feed_link = "feed/$feed";
                $link = $this->_trailingslashit( $link ) . $this->_user_trailingslashit( $feed_link, 'feed' );
            }
            if ( 'category' === $taxonomy ) $link = $this->_apply_filters( 'category_feed_link', $link, $feed );
            elseif ( 'post_tag' === $taxonomy ) $link = $this->_apply_filters( 'tag_feed_link', $link, $feed );
            else $link = $this->_apply_filters( 'taxonomy_feed_link', $link, $feed, $taxonomy );
            return $link;
        }//930 from link-template
    }
}else die;