<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:22
 */
namespace TP_Core\Traits\Feed;
if(ABSPATH){
    trait _feed_03 {
        /**
         * @description Display the current comment content for use in the feeds.
         */
        protected function _get_comment_text_rss(){
            $comment_text = $this->_get_comment_text();
            $comment_text = $this->_apply_filters( 'comment_text_rss', $comment_text );
            return $comment_text;
        }//354
        public function comment_text_rss():void{
            echo $this->_get_comment_text_rss();
        }
        /**
         * @description Retrieve all of the post categories, formatted for use in feeds.
         * @param null $type
         * @return mixed
         */
        protected function _get_the_category_rss( $type = null ){
            if ( empty( $type )) $type = $this->_get_default_feed();
            $categories = $this->_get_the_category();
            $tags       = $this->_get_the_tags();
            $the_list   = '';
            $cat_names  = [];
            $filter = 'rss';
            if ( 'atom' === $type ) $filter = 'raw';
            if ( ! empty( $categories ) ) {
                foreach ( (array) $categories as $category )
                    $cat_names[] = $this->_sanitize_term_field( 'name', $category->name, $category->term_id, 'category', $filter );
            }
            if ( ! empty( $tags ) ) {
                foreach ( (array) $tags as $tag )
                    $cat_names[] = $this->_sanitize_term_field( 'name', $tag->name, $tag->term_id, 'post_tag', $filter );
            }
            $cat_names = array_unique( $cat_names );
            $bracket['right'] = ']';
            $percent = ['one' => '%1$s', 'two' => '%2$s'];
            foreach ( $cat_names as $cat_name ) {
                if ( 'rdf' === $type )
                    $the_list .= "\t\t<dc:subject><![CDATA[$cat_name]{$bracket['right']}></dc:subject>\n";
                elseif ( 'atom' === $type )
                    $the_list .= sprintf( "<category scheme='{$percent['one']}' term='{$percent['two']}'/>", $this->_esc_attr( $this->_get_bloginfo_rss( 'url' ) ), $this->_esc_attr( $cat_name ) );
                else $the_list .= "\t\t<category><![CDATA[" . html_entity_decode( $cat_name, ENT_COMPAT, $this->_get_option( 'blog_charset' ) ) . "]{$bracket['right']}></category>\n";
            }
            return $this->_apply_filters( 'the_category_rss', $the_list, $type );
        }//379
        /**
         * @description Display the post categories in the feed.
         * @param null $type
         */
        public function the_category_rss( $type = null ):void{
            echo $this->_get_the_category_rss( $type);
        }//438
        /**
         * @deprecated as this build will focus on html5 only
         * @description Display the HTML type based on the blog setting.
         */
        public function html_type_rss():void{
            $type = $this->_get_bloginfo( 'html_type' );
            if ( strpos( $type, 'xhtml' ) !== false ) $type = 'xhtml';
            else $type = 'html';
            echo $type;
        }//449
        /**
         * @description Display the rss enclosure for the current post.
         * @return bool|string
         */
        protected function _get_rss_enclosure(){
            if ( $this->_post_password_required() ) return false;
            $return='';
            foreach ( (array) $this->_get_post_custom() as $key => $val ) {
                if ( 'enclosure' === $key ) {
                    foreach ( (array) $val as $enc ) {
                        $enclosure = explode( "\n", $enc );
                        $t    = preg_split( '/[ \t]/', trim( $enclosure[2] ) );
                        $type = $t[0];
                        $_elem = "<enclosure url='{$this->_esc_url( trim( $enclosure[0] ) )}' length='{$this->_abs_int( trim( $enclosure[1] ) )}' type='{$this->_esc_attr( $type )}' />\n";
                        $return = $this->_apply_filters( 'rss_enclosure', $_elem );
                    }
                }
            }
            return $return;
        }//473
        public function rss_enclosure():void{
            echo $this->_get_rss_enclosure();
        }
        /**
         * @description Display the atom enclosure for the current post.
         * @return bool|string
         */
        protected function _get_atom_enclosure(){
            if ( $this->_post_password_required() ) return false;
            $return = '';
            foreach ( (array) $this->_get_post_custom() as $key => $val ) {
                if ( 'enclosure' === $key ) {
                    foreach ( (array) $val as $enc ) {
                        $enclosure = explode( "\n", $enc );
                        $url    = '';
                        $type   = '';
                        $length = 0;
                        $mimes = $this->_get_allowed_mime_types();
                        if ( isset( $enclosure[0] ) && is_string( $enclosure[0] ) ) $url = trim( $enclosure[0] );
                        for ( $i = 1; $i <= 2; $i++ ) {
                            if ( isset( $enclosure[ $i ] ) ) {
                                if ( is_numeric( $enclosure[ $i ] ) ) $length = trim( $enclosure[ $i ] );
                                elseif ( in_array( $enclosure[ $i ], $mimes,true )) $type = trim( $enclosure[ $i ] );
                            }
                        }
                        $html_link_tag = sprintf(
                            "<link href='%s' rel='enclosure' length='%d' type='%s' />\n",
                            $this->_esc_url( $url ),$this->_esc_attr( $length ), $this->_esc_attr( $type )
                        );
                        $return = $this->_apply_filters( 'atom_enclosure', $html_link_tag );
                    }
                }
            }
            return $return;
        }//513
        public function atom_enclosure():void{
            echo $this->_get_atom_enclosure();
        }
        /**
         * @description Determine the type of a string of data with the data formatted.
         * @param $data
         * @return array
         */
        protected function _prep_atom_text_construct( $data ):?array{
            if ( strpos( $data, '<' ) === false && strpos( $data, '&' ) === false )
                return array( 'text', $data );
            if ( ! function_exists( 'xml_parser_create' ) ) {
                trigger_error( $this->__( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
                return array( 'html', "<![CDATA[$data]]>" );
            }
            $parser = xml_parser_create();
            xml_parse( $parser, '<div>' . $data . '</div>', true );
            $code = xml_get_error_code( $parser );
            xml_parser_free( $parser );
            unset( $parser );
            if ( ! $code ) {
                if ( strpos( $data, '<' ) === false )
                    return array( 'text', $data );
                else {
                    $data = "<div xmlns='http://www.w3.org/1999/xhtml'>$data</div>";
                    return array( 'xhtml', $data );
                }
            }
            if ( strpos( $data, ']]>' ) === false )
                return array( 'html', "<![CDATA[$data]]>" );
            else return array( 'html', htmlspecialchars( $data ) );
        }//582
    }
}else die;