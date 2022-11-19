<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 16:15
 */
namespace TP_Core\Traits\Templates;
//use TP_Managers\Core_Manager\TP_Comment;
//use TP_Managers\Query_Manager\TP_Comment_Query;
//use TP_Managers\Query_Manager\TP_Query;
use TP_Core\Libs\TP_Comment;
use TP_Core\Templates\TP_Comments_Template;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _comment_template_04 {
        use _init_db;
        use _init_queries;
        use _init_comment;
        use _init_post;
        /**
         * @description Displays the comment time of the current comment.
         * @param string $format
         */
        public function comment_time( $format = '' ):void{
            echo $this->_get_comment_time( $format );
        }//1074 from comment-template
        /**
         * @description Retrieves the comment type of the current comment.
         * @param int $comment_ID
         * @return mixed
         */
        protected function _get_comment_type( $comment_ID = 0 ){
            $comment = $this->_get_comment( $comment_ID );
            if ( '' === $comment->comment_type )
                $comment->comment_type = 'comment';
            return $this->_apply_filters( 'get_comment_type', $comment->comment_type, $comment->comment_ID, $comment );
        }//1088 from comment-template
        /**
         * @description Displays the comment type of the current comment.
         * @param bool $comment_txt
         * @param bool $trackback_txt
         * @param bool $pingback_txt
         */
        protected function _comment_type( $comment_txt = false, $trackback_txt = false, $pingback_txt = false ):void{
            if ( false === $comment_txt ) $comment_txt = $this->_x( 'Comment', 'noun' );
            if ( false === $trackback_txt )  $trackback_txt = $this->__( 'Trackback' );
            if ( false === $pingback_txt ) $pingback_txt = $this->__( 'Pingback' );
            $type = $this->_get_comment_type();
            switch ( $type ) {
                case 'trackback':
                    echo $trackback_txt;
                    break;
                case 'pingback':
                    echo $pingback_txt;
                    break;
                default:
                    echo $comment_txt;
            }
        }//1117 from comment-template
        /**
         * @description Retrieves the current post's trackback URL.
         * @return mixed
         */
        protected function _get_trackback_url(){
            if ( $this->_get_option( 'permalink_structure' ) )
                $tb_url = $this->_trailingslashit( $this->_get_permalink() ) . $this->_user_trailingslashit( 'trackback', 'single_trackback' );
            else $tb_url = $this->_get_option( 'siteurl' ) . '/tp-trackback.php?p=' . $this->_get_the_ID();
            return $this->_apply_filters( 'trackback_url', $tb_url );
        }//1151 from comment-template
        /**
         * @description Displays the current post's trackback URL.
         */
        public function trackback_url():void{
            echo $this->_get_trackback_url();
        }//1177 from comment-template
        /**
         * @description Generates and displays the RDF for the trackback information of current post.
         */
        protected function _trackback_rdf():void{
            if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && false !== stripos( $_SERVER['HTTP_USER_AGENT'], 'W3C_Validator' ) )
                return;
            $tp_texturize = str_replace( '--', '&#x2d;&#x2d;', $this->_tp_texturize( strip_tags( $this->_get_the_title() ) ));
            $echo_rdf = "<rdf:RDF xmlns:rdf='https://www.w3.org/1999/02/22-rdf-syntax-ns#'";
            $echo_rdf .= " xmlns:dc='https://purl.org/dc/elements/1.1/'";
            $echo_rdf .= " xmlns:trackback='http://madskills.com/public/xml/rss/module/trackback/'>";
            $echo_rdf .= "<rdf:Description rdf:about='{$this->_the_permalink()}'\n";
            $echo_rdf .= " dc:identifier='{$this->_the_permalink()}'\n";
            $echo_rdf .= " dc:title='$tp_texturize'\n";
            $echo_rdf .= " trackback:ping='{$this->_get_trackback_url()}'/>\n";
            $echo_rdf .= "</rdf:RDF>";
            echo $echo_rdf;
        }//1206 from comment-template
        /**
         * @description Determines whether the current post is open for comments.
         * @param null $post_id
         * @return mixed
         */
        protected function _comments_open($post_id = null){
            $_post = $this->_get_post( $post_id );
            $post_id = $_post ? $_post->ID : 0;
            $open    = ( $_post && ( 'open' === $_post->comment_status ) );
            return $this->_apply_filters( 'comments_open', $open, $post_id );
        }//1241 from comment-template
        /**
         * @description Determines whether the current post is open for pings.
         * @param null $post_id
         * @return mixed
         */
        protected function _pings_open($post_id = null){
            $_post = $this->_get_post( $post_id );
            $post_id = $_post ? $_post->ID : 0;
            $open    = ( $_post && ( 'open' === $_post->ping_status ) );
            return $this->_apply_filters( 'pings_open', $open, $post_id );
        }//1271 from comment-template
        /**
         * @description Displays form token for unfiltered comments.
         */
        protected function _tp_comment_form_unfiltered_html_nonce():void{
            $post    = $this->_get_post();
            $post_id = $post ? $post->ID : 0;
            if ( $this->_current_user_can( 'unfiltered_html' ) ) {
                $this->_tp_nonce_field( 'unfiltered-html-comment_' . $post_id, '_tp_unfiltered_html_comment_disabled', false );
                echo "<script>(function(){if(window===window.parent){document.getElementById('_tp_unfiltered_html_comment_disabled').name='_tp_unfiltered_html_comment';}})();</script>\n";
            }
        }//1303 from comment-template
        /**
         * @description Loads the comment template specified in $file.
         * @param bool $separate_comments
         * @param $args
         * @param array||null $comments_args
         * @return string
         */
        protected function _get_comments_template($separate_comments = false, $args, $comments_args = null):string{
            $name = $comments_args['name'];
            $theme_name = $comments_args['theme_name'];
            $class_name = $comments_args['class_name'];
            $this->tp_query = $this->_init_query();
            $this->tp_post = $this->_init_post();
            $this->tpdb = $this->_init_db();
            $this->tp_comment = $this->_init_comment();
            if (empty( $this->tp_post ) || ! ( $this->_is_single() || $this->_is_page() || $this->tp_with_comments )) {
                return false;
            }
            $args['require_email'] = $this->_get_option( 'require_name_email' );
            $commenter = $this->_tp_get_current_commenter();
            $args['comment_author'] = $commenter['comment_author'];
            $args['comment_author_email'] = $commenter['comment_author_email'];
            $args['comment_author_url'] = $this->_esc_url( $commenter['comment_author_url'] );
            $comment_args = ['orderby'=> 'comment_date_gmt','order' => 'ASC','status' => 'approve',
                'post_id' => $this->tp_post->ID,'no_found_rows' => false,'update_comment_meta_cache' => false,];
            if ( $this->_get_option( 'thread_comments')){ $comment_args['hierarchical'] = 'threaded';}
            else {$comment_args['hierarchical'] = false;}
            if ( $this->_is_user_logged_in() ) {$comment_args['include_unapproved'] = [$this->_get_current_user_id()];}
            else {$unapproved_email = $this->_tp_get_unapproved_comment_author_email();
                if ( $unapproved_email ) {$comment_args['include_unapproved'] = [$unapproved_email];}
            }
            $per_page = 0;
            if ( $this->_get_option( 'page_comments' ) ) {
                $per_page = (int) $this->_get_query_var( 'comments_per_page' );
                if ( 0 === $per_page ) {
                    $per_page = (int) $this->_get_option( 'comments_per_page' );
                }
            }
            $comment_args['number'] = $per_page;
            $page                   = (int) $this->_get_query_var( 'cpage' );
            if ( $page ) {
                $comment_args['offset'] = ( $page - 1 ) * $per_page;
            } elseif ( 'oldest' === $this->_get_option( 'default_comments_page' ) ) {
                $comment_args['offset'] = 0;
            }else{
                $top_level_query = $this->_init_comment_query();
                $top_level_args  = ['count' => true,'orderby' => false,'post_id' => $this->tp_post->ID,'status' => 'approve',];
                if ( $comment_args['hierarchical']){$top_level_args['parent'] = 0;}
                if ( isset( $comment_args['include_unapproved'] ) ) {
                    $top_level_args['include_unapproved'] = $comment_args['include_unapproved'];
                }
                $top_level_args = $this->_apply_filters( 'comments_template_top_level_query_args', $top_level_args );
                $top_level_count = $top_level_query->query_comment( $top_level_args );
                $comment_args['offset'] = ( ceil( $top_level_count / $per_page ) - 1 ) * $per_page;
            }
            $comment_args = $this->_apply_filters( 'comments_template_query_args', $comment_args );
            $comment_query = $this->_init_comment_query( $comment_args );
            $_comments     = $comment_query->comments;
            if ( $comment_args['hierarchical'] ) {
                $comments_flat = [];
                foreach ( $_comments as $_comment ) {
                    $comments_flat[]  = $_comment;
                    if($_comment  instanceof TP_Comment ){
                        $comment_children = $_comment->get_children(
                            ['format' => 'flat','status' => $comment_args['status'],'orderby' => $comment_args['orderby'],]);
                        foreach ( $comment_children as $comment_child ){$comments_flat[] = $comment_child;}
                    }
                }
            } else {$comments_flat = $_comments;}
            $this->tp_query->comments = $this->_apply_filters( 'comments_array', $comments_flat, $this->tp_post->ID );
            $comments                        = &$this->tp_query->comments;
            $this->tp_query->comment_count         = count( $this->tp_query->comments );
            $this->tp_query->max_num_comment_pages = $comment_query->max_num_pages;
            if ( $separate_comments ) {
                $this->tp_query->comments_by_type = $this->_separate_comments( $comments );
                $args['comments_by_type'] = &$this->tp_query->comments_by_type;
            } else {$this->tp_query->comments_by_type = [];}
            $this->tp_overridden_cpage = false;
            if ($this->tp_query->max_num_comment_pages > 1 && '' === $this->_get_query_var( 'cpage' )) {
                $this->_set_query_var( 'cpage', 'newest' === $this->_get_option( 'default_comments_page' ) ? $this->_get_comment_pages_count() : 1 );
                $this->tp_overridden_cpage = true;
            }
            if ( ! defined( 'COMMENTS_TEMPLATE' ) ) { define( 'COMMENTS_TEMPLATE', true );}
            $theme_template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null ){
                $theme_template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name,$args);
            }else{ $theme_template = new TP_Comments_Template($args);}
            return $this->_apply_filters( 'comments_template', $theme_template );
        }//1345 todo testing
        protected function _print_comments_template($separate_comments = false, $args, $comments_args = null):void{
            echo $this->_get_comments_template($separate_comments, $args, $comments_args);
        }
    }
}else die;