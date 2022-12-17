<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-12-2022
 * Time: 07:44
 */
namespace TP_Libs;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Comment\_comment_01;
use TP_Core\Traits\Comment\_comment_02;
use TP_Core\Traits\Comment\_comment_03;
use TP_Core\Traits\Comment\_comment_04;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_09;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Load\_load_01;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_03;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
if(ABSPATH){
    class TP_Trackback{
        use _comment_01, _comment_02, _comment_03, _comment_04, _comment_template_04, Constants,_formats_04;
        use _formats_09, _formats_10, _formats_11, _I10n_01,_I10n_02,_I10n_03,_I10n_04,_init_db,_init_error;
        use _init_post, _link_template_01, _load_01, _load_04, _methods_01,_methods_04, _methods_21, _option_01;
        use _pluggable_01,_pluggable_03, _post_01, _query_03, _user_02, _user_03;
        private $__tb_db, $__tb_blog_name, $__tb_charset, $__tb_dupe, $__tb_excerpt, $__tb_request_array;
        private $__tb_id, $__tb_url, $__tb_title, $__tb_post;
        //temporary
        public $tp_current_env;
        protected $_args;
        public function __construct($args = null){
            $this->_args = $args;
            $this->__tb_post = $this->_init_post();
            $this->__tb_db = $this->_init_db();
            $this->_tp_core_constants();
            $this->_db_constants();
            $this->_initial_constants();
            $this->_version_constants();
        }
        private function __trackback_response( $error = 0, $error_message = '' ):string{
            //header("Content-Type:text/xml; charset=''");//'Content-Type: text/xml; charset='. //todo
            $output  = "";
            if( $error ) {
                $output .= "<?xml version='1.0' encoding='utf-8'?>\n";
                $output .= "<response>\n";
                $output .= "<error>1</error><message>$error_message</message>\n";
                $output .= "</response>";
            }else{
                $output .= "<?xml version='1.0' encoding='utf-8'?>\n";
                $output .= "<response>\n";
                $output .= "<error>0</error>\n";
                $output .= "</response>";
            }
            return $output;
        }
        private function __trackback_output():string{
            $output = "";
            $this->_tp_set_current_user( 0 );
            $this->__tb_request_array = 'HTTP_POST_VARS';
            if ( ! isset( $_GET['tb_id'] ) || ! $_GET['tb_id'] ) {
                $tp_id = explode( '/', $_SERVER['REQUEST_URI'] );
                $this->__tb_id = (int) $tp_id[ count( $tp_id ) - 1 ];
            }
            $this->__tb_url  = $_POST['url'] ?? 'sample_url';
            $this->__tb_charset = $_POST['charset'] ?? '';
            $this->__tb_title     = isset( $_POST['title'] ) ? $this->_tp_unslash( $_POST['title'] ) : 'sample_title';
            $this->__tb_excerpt   = isset( $_POST['excerpt'] ) ? $this->_tp_unslash( $_POST['excerpt'] ) : 'sample_excerpt';
            $this->__tb_blog_name = isset( $_POST['blog_name'] ) ? $this->_tp_unslash( $_POST['blog_name'] ) : 'sample_blog_name';
            if ( $this->__tb_charset ) {
                $this->__tb_charset = str_replace( array( ',', ' ' ), '', strtoupper( trim( $this->__tb_charset ) ) );
            } else { $this->__tb_charset = 'ASCII, UTF-8, ISO-8859-1, JIS, EUC-JP, SJIS';}
            if ( false !== strpos( $this->__tb_charset, 'UTF-7' ) ) { die;}
            $this->__tb_title     = $this->_mb_convert_encoding( $this->__tb_title, $this->_get_option( 'blog_charset' ), $this->__tb_charset );
            $this->__tb_excerpt   = $this->_mb_convert_encoding( $this->__tb_excerpt, $this->_get_option( 'blog_charset' ), $this->__tb_charset );
            $this->__tb_blog_name = $this->_mb_convert_encoding( $this->__tb_blog_name, $this->_get_option( 'blog_charset' ), $this->__tb_charset );
            $this->__tb_title     = $this->_tp_slash( $this->__tb_title );
            $this->__tb_excerpt   = $this->_tp_slash( $this->__tb_excerpt );
            $this->__tb_blog_name = $this->_tp_slash( $this->__tb_blog_name );
            if ( $this->_is_single() || $this->_is_page() ) { $this->__tb_id = $this->__tb_post[0]->ID;}
            if ( ! isset( $this->__tb_id ) || ! (int) $this->__tb_id ) {
                $output .= "<dd>{$this->__trackback_response( 1, $this->__( ': I really need an ID for this to work.' ) )}</dd>";
            }
            if (empty( $this->__tb_title ) && empty( $this->__tb_url ) && empty( $this->__tb_blog_name ) ) {
                // If it doesn't look like a trackback at all.
                $this->_tp_redirect( $this->_get_permalink( $this->__tb_id ) );
                exit;
            }
            if ( ! empty( $this->__tb_url ) && ! empty( $this->__tb_title ) ) {
                $output .= $this->_get_action( 'pre_trackback_post', $this->__tb_id, $this->__tb_url, $this->__tb_charset, $this->__tb_title, $this->__tb_excerpt, $this->__tb_blog_name );
                //$output .= header( 'Content-Type: text/xml; charset=' . $this->_get_option( 'blog_charset' ) );//todo
                if (! $this->_pings_open( $this->__tb_id ) ) {
                    $output = "<dd>{$this->__trackback_response( 1, $this->__( ': Sorry, trackbacks are closed for this item.' ) )}</dd>";
                }
                $this->__tb_title   = $this->_tp_html_excerpt( $this->__tb_title, 250, '&#8230;' );
                $this->__tb_excerpt = $this->_tp_html_excerpt( $this->__tb_excerpt, 252, '&#8230;' );
                $comment_post_ID      = (int) $this->__tb_id;
                $comment_author       = $this->__tb_blog_name;
                $comment_author_email = '';
                $comment_author_url   = $this->__tb_url;
                $comment_content      = "<strong>$this->__tb_title</strong>\n\$this->__excerpt";
                $comment_type         = 'trackback';//$this->__tb_db
                if(null !== $this->__tb_db->comments){
                    $this->__tb_dupe .= $this->__tb_db->get_results($this->__tb_db->prepare(TP_SELECT . " * FROM $this->__tb_db->comments WHERE comment_post_ID = %d AND comment_author_url = %s", $comment_post_ID, $comment_author_url ));
                }
                if ($this->__tb_dupe ) {
                    $output .= "<dd>{$this->__trackback_response( 1, $this->__( ': We already have a ping from that URL for this post.' ) )}</dd>";
                }
                $commentdata = compact( 'comment_post_ID','comment_author','comment_author_email','comment_author_url','comment_content','comment_type' );
                $result = $this->_tp_new_comment( $commentdata );
                if ($result instanceof TP_Error && $this->_init_error( $result ) ) {
                    $output .= "<dd>{$this->__trackback_response( 1, $result->get_error_message())}</dd>";
                }
                $trackback_id = $this->__tb_db->insert_id;
                $output .= $this->_get_action( 'trackback_post', $trackback_id );
                $output .= $this->__trackback_response( 0 );
            }
            $output .= "</br>__trackback_output";
            return $output;
        }
        public function __toString(){
            return $this->__trackback_output();
        }
    }
}else{die;}