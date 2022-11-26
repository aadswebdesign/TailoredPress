<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-4-2022
 * Time: 17:41
 */
namespace TP_Core\Libs;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    class TP_Core extends Core_Base{
        public function add_query_var($qv): void{
            if (!in_array($qv, $this->public_query_vars, true))
                $this->public_query_vars[] = $qv;
        }//92
        public function remove_query_var($name): void{
            $this->public_query_vars = array_diff($this->public_query_vars, array($name));
        }//105
        public function set_query_var($key, $value): void{
            $this->query_vars[$key] = $value;
        }//117
        public function parse_request(array ...$extra_query_vars): bool{
            $tp_rewrite = $this->_init_rewrite();
            $query = null;
            $matches = null;
            if (!$this->_apply_filters('do_parse_request', true, $this, $extra_query_vars))
                return false;
            $this->query_vars = [];
            $post_type_query_vars = [];
            if (is_array($extra_query_vars)) $this->extra_query_vars = &$extra_query_vars;
            elseif (!empty($extra_query_vars)) parse_str($extra_query_vars, $this->extra_query_vars);
            $rewrite = $tp_rewrite->tp_rewrite_rules(); //15
            if (!empty($rewrite)) {
                $error               = '404';
                $this->did_permalink = true;
                $pathinfo = $_SERVER['PATH_INFO'] ?? '';
                @list($pathinfo) = explode('?', $pathinfo);
                $pathinfo = str_replace('%', '%25', $pathinfo);
                @list($req_uri) = explode('?', $_SERVER['REQUEST_URI']);
                $self = $_SERVER['PHP_SELF'];
                $home_path = parse_url($this->_home_url(), PHP_URL_PATH);
                $home_path_regex = '';
                if (is_string($home_path) && '' !== $home_path) {
                    $home_path = trim($home_path, '/');
                    $home_path_regex = sprintf('|^%s|i', preg_quote($home_path, '|'));
                }
                $req_uri = str_replace($pathinfo, '', $req_uri);
                $req_uri = trim($req_uri, '/');
                $pathinfo = trim($pathinfo, '/');
                $self = trim($self, '/');
                if (!empty($home_path_regex)) {
                    $req_uri = preg_replace($home_path_regex, '', $req_uri);
                    $req_uri = trim($req_uri, '/');
                    $pathinfo = preg_replace($home_path_regex, '', $pathinfo);
                    $pathinfo = trim($pathinfo, '/');
                    $self = preg_replace($home_path_regex, '', $self);
                    $self = trim($self, '/');
                }
                if (!empty($pathinfo) && !preg_match('|^.*' . $tp_rewrite->index . '$|', $pathinfo))
                    $requested_path = $pathinfo;
                else {
                    if ($req_uri === $tp_rewrite->index) $req_uri = '';
                    $requested_path = $req_uri;
                }
                $requested_file = $req_uri;
                $this->request = $requested_path;
                $request_match = $requested_path;
                if (empty($request_match) && isset($rewrite['$'])) {
                    $this->matched_rule = '$';
                    $query = $rewrite['$'];
                    $matches = [''];
                }else{
                    foreach ((array)$rewrite as $match => $query) {
                        // If the requested file is the anchor of the match, prepend it to the path info.
                        if (!empty($requested_file) && $requested_file !== $requested_path && strpos($match, $requested_file) === 0)
                            $request_match = $requested_file . '/' . $requested_path;
                        if (preg_match("#^$match#", $request_match, $matches) ||
                            preg_match("#^$match#", urldecode($request_match), $matches)
                        ) {
                            if ($tp_rewrite->use_verbose_page_rules && preg_match('/page_name=\$matches\[(\d+)\]/', $query, $varmatch)) {
                                $page = $this->_get_page_by_path($matches[$varmatch[1]]);
                                $page_status = null;
                                if($page instanceof TP_Post ){
                                    $page_status = $page->post_status;
                                }
                                if (!$page) continue;
                                $post_status_obj = $this->_get_post_status_object($page_status);
                                if (( $post_status_obj instanceof \stdClass ) && !$post_status_obj->public && !$post_status_obj->protected
                                    && !$post_status_obj->private && $post_status_obj->exclude_from_search
                                ) {
                                    continue;
                                }
                            }
                            $this->matched_rule = $match;
                            break;
                        }
                    }
                }
                if ( isset( $this->matched_rule ) ) {
                    $query = preg_replace( '!^.+\?!', '', $query );
                    $query = addslashes(TP_MatchesMapRegex::apply($query, $matches));
                    $this->matched_query = $query;
                    parse_str($query, $perma_query_vars);
                    if ('404' === $error) {
                        unset($error, $_GET['error']);
                    }
                }
                if (empty($requested_path) || $requested_file === $self || strpos($_SERVER['PHP_SELF'], 'tp_admin/') !== false) {
                    unset($error, $_GET['error']);
                    if (isset($perma_query_vars) && strpos($_SERVER['PHP_SELF'], 'tp_admin/') !== false)
                        unset($perma_query_vars);
                    $this->did_permalink = false;
                }
            }
            $this->public_query_vars = $this->_apply_filters('query_vars', $this->public_query_vars);
            foreach ($this->_get_post_types(array(), 'objects') as $post_type => $t) {
                if ($t->query_var && $this->_is_post_type_viewable($t))
                    $post_type_query_vars[$t->query_var] = $post_type;
            }
            foreach ($this->public_query_vars as $tp_var) {
                if (isset($this->extra_query_vars[$tp_var]))
                    $this->query_vars[$tp_var] = $this->extra_query_vars[$tp_var];
                elseif (isset($_GET[$tp_var],$_POST[$tp_var]) && $_GET[$tp_var] !== $_POST[$tp_var])
                    $this->_tp_die($this->__('A variable mismatch has been detected.'), $this->__('Sorry, you are not allowed to view this item.'), 400);
                elseif (isset($_POST[$tp_var]))
                    $this->query_vars[$tp_var] = $_POST[$tp_var];
                elseif (isset($_GET[$tp_var]))
                    $this->query_vars[$tp_var] = $_GET[$tp_var];
                elseif (isset($perma_query_vars[$tp_var]))
                    $this->query_vars[$tp_var] = $perma_query_vars[$tp_var];
                if (!empty($this->query_vars[$tp_var])) {
                    if (!is_array($this->query_vars[$tp_var]))
                        $this->query_vars[$tp_var] = (string)$this->query_vars[$tp_var];
                    else {
                        foreach ($this->query_vars[$tp_var] as $vkey => $v) {
                            if (is_scalar($v)) $this->query_vars[$tp_var][$vkey] = (string)$v;
                        }
                    }
                    if (isset($post_type_query_vars[$tp_var])) {
                        $this->query_vars['post_type'] = $post_type_query_vars[$tp_var];
                        $this->query_vars['name'] = $this->query_vars[$tp_var];
                    }
                }
            }
            foreach ($this->_get_taxonomies([], 'objects') as $taxonomy => $t) {
                if ($t->query_var && isset($this->query_vars[$t->query_var])) {
                    $this->query_vars[$t->query_var] = str_replace(' ', '+', $this->query_vars[$t->query_var]);
                }
            }
            if (!$this->_is_admin()) {
                foreach ($this->_get_taxonomies(array('publicly_queryable' => false), 'objects') as $taxonomy => $t) {
                    if (isset($this->query_vars['taxonomy']) && $taxonomy === $this->query_vars['taxonomy']) {
                        unset($this->query_vars['taxonomy'], $this->query_vars['term']);
                    }
                }
            }
            if (isset($this->query_vars['post_type'])) {
                $queryable_post_types = $this->_get_post_types(array('publicly_queryable' => true));
                if (!is_array($this->query_vars['post_type'])) {
                    if (!in_array($this->query_vars['post_type'], $queryable_post_types, true))
                        unset($this->query_vars['post_type']);
                } else $this->query_vars['post_type'] = array_intersect($this->query_vars['post_type'], $queryable_post_types);
            }
            $this->query_vars = $this->_tp_resolve_numeric_slug_conflicts($this->query_vars);
            foreach ((array)$this->_private_query_vars as $var) {
                if (isset($this->extra_query_vars[$var]))
                    $this->query_vars[$var] = $this->extra_query_vars[$var];
            }
            if (isset($error)) $this->query_vars['error'] = $error;
            $this->query_vars = $this->_apply_filters('request', $this->query_vars);
            $this->_do_action_ref_array('parse_request', array(&$this));
            return true;
        }//133
        public function send_headers(): void{
            $headers       = [];
            $status        = null;
            $exit_required = false;
            $date_format   = 'D, d M Y H:i:s';
            if ( $this->_is_user_logged_in() )
                $headers = array_merge( $headers, $this->_tp_get_nocache_headers() );
            elseif ( ! empty( $_GET['unapproved'] ) && ! empty( $_GET['moderation-hash'] ) ) {
                $expires = 10 * MINUTE_IN_SECONDS;
                $headers['Expires']       = gmdate( $date_format, time() + $expires );
                $headers['Cache-Control'] = sprintf('max-age=%d, must-revalidate', $expires);
            }
            if ( ! empty( $this->query_vars['error'] ) ) {
                $status = (int) $this->query_vars['error'];
                if ( 404 === $status ) {
                    if (!$this->_is_user_logged_in()) $headers = array_merge($headers, $this->_tp_get_nocache_headers());
                    $headers['Content-Type'] = $this->_get_option('html_type') . '; charset=' . $this->_get_option('blog_charset');
                }elseif ( in_array( $status, [403, 500, 502, 503], true ) ) $exit_required = true;
            }elseif ( empty( $this->query_vars['feed'] ) )
                $headers['Content-Type'] = $this->_get_option( 'html_type' ) . '; charset=' . $this->_get_option( 'blog_charset' );
            else{
                $type = $this->query_vars['feed'];
                if ( 'feed' === $this->query_vars['feed'] ) $type = $this->_get_default_feed();
                $headers['Content-Type'] = $this->_feed_content_type( $type ) . '; charset=' . $this->_get_option( 'blog_charset' );
                $q_attachment = $this->query_vars['attachment'];
                $q_attachment_id = $this->query_vars['attachment_id'];
                $q_name = $this->query_vars['name'];
                $q_p = $this->query_vars['p'];
                $q_page_id = $this->query_vars['page_id'];
                $q_page_name = $this->query_vars['page_name'];
                $with_comments = $this->query_vars['with_comments'];
                $without_comments = $this->query_vars['without_comments'];
                if(! empty($with_comments )|| empty($without_comments )|| ((false !== strpos($this->query_vars['feed'], 'comments-')) && (! empty( $q_p )||! empty($q_name)||! empty($q_name)||! empty($q_page_id)||! empty($q_page_name)||! empty($q_attachment)||! empty($q_attachment_id)))){
                    $tp_last_modified_post    = $this->_mysql2date( $date_format, $this->_get_last_post_modified( 'GMT' ), false );
                    $tp_last_modified_comment = $this->_mysql2date( $date_format, $this->_get_last_comment_modified( 'GMT' ), false );
                    if ( strtotime( $tp_last_modified_post ) > strtotime( $tp_last_modified_comment ) )
                        $tp_last_modified = $tp_last_modified_post;
                    else $tp_last_modified = $tp_last_modified_comment;
                }else {$tp_last_modified = $this->_mysql2date( $date_format, $this->_get_last_post_modified( 'GMT' ), false );}
                if ( ! $tp_last_modified ) $tp_last_modified = gmdate( $date_format );
                $tp_last_modified .= ' GMT';
                $tp_etag                  = '"' . md5( $tp_last_modified ) . '"';
                $headers['Last-Modified'] = $tp_last_modified;
                $headers['ETag']          = $tp_etag;
                if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) )
                    $client_etag = $this->_tp_unslash( $_SERVER['HTTP_IF_NONE_MATCH'] );
                else $client_etag = false;
                $client_last_modified = empty( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? '' : trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
                $client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;
                $tp_modified_timestamp = strtotime( $tp_last_modified );
                if ( ( $client_last_modified && $client_etag ) ?
                    ( ( $client_modified_timestamp >= $tp_modified_timestamp ) && ( $client_etag === $tp_etag ) ) :
                    ( ( $client_modified_timestamp >= $tp_modified_timestamp ) || ( $client_etag === $tp_etag ) ) ) {
                    $status        = 304;
                    $exit_required = true;
                }
            }
            $headers = $this->_apply_filters( 'tp_headers', $headers, $this );
            if ( $status !== null) $this->_status_header( $status );
            if ( isset( $headers['Last-Modified'] ) && false === $headers['Last-Modified'] ) {
                unset( $headers['Last-Modified'] );
                if ( ! headers_sent() ) header_remove( 'Last-Modified' );
            }
            if ( ! headers_sent() ) {
                foreach ( (array) $headers as $name => $field_value )
                    header( "{$name}: {$field_value}" );
            }
            if ( $exit_required ) exit;
            $this->_do_action_ref_array( 'send_headers', array( &$this ) );
        }
        public function build_query_string(): void{
            $this->query_string = '';
            foreach ( (array) array_keys( $this->query_vars ) as $tp_var ) {
                if ( '' !== $this->query_vars[ $tp_var ] ) {
                    $this->query_string .= ( $this->query_string === 1 ) ? '' : '&';
                    if ( ! is_scalar( $this->query_vars[ $tp_var ] ) ) continue;
                    $this->query_string .= $tp_var . '=' . rawurlencode( $this->query_vars[ $tp_var ] );
                }
            }
        }
        public function init(): void{
            $this->_tp_get_user_current();
        }
        public function query_posts(): void{
            $tp_query = $this->getTpTheQuery();
            $this->build_query_string();
            $tp_query->query_main( $this->query_vars );
        }
        public function handle_404(): void{
            $tp_query = $this->_init_query();
            if ( false !== $this->_apply_filters( 'pre_handle_404', false, $tp_query ) )
                return;
            if ( $this->_is_404() ) return;
            $set_404 = true;
            if ( $this->_is_admin() || $this->_is_robots() || $this->_is_favicon() ) {
                $set_404 = false;
            }elseif ( $tp_query->posts ) {
                $content_found = true;
                if ( $this->_is_singular() ) {
                    $post = $tp_query->post ?? null;
                    if ( $post && $this->_pings_open( $post ) && ! headers_sent() )
                        header( 'X-Pingback: ' . $this->_get_bloginfo( 'pingback_url', 'display' ) );
                    $next = '<!--nextpage-->';
                    if ( $post && ! empty( $this->query_vars['page'] ) ) {
                        if (($post instanceof TP_Post ) && false !== strpos( $post->post_content, $next ) ) {
                            $page          = trim( $this->query_vars['page'], '/' );
                            $content_found = (int) $page <= ( substr_count( $post->post_content, $next ) + 1 );
                        } else $content_found = false;
                    }
                }
                if ( $tp_query->is_posts_page && ! empty( $this->query_vars['page'] ) ) $content_found = false;
                if ( $content_found ) $set_404 = false;
            }elseif ( ! $this->_is_paged() ) {
                $author = $this->_get_query_var( 'author' );
                if (is_numeric( $author ) && $author > 0 && $this->_is_author() && $this->_is_user_member_of_blog( $author )) {
                    $set_404 = false;}
            }
            if ( $set_404 ) {
                $tp_query->set_404();
                $this->_status_header( 404 );
                $this->_nocache_headers();
            } else  $this->_status_header( 200 );
        }
        public function main(array ...$query_args): void{
            $this->init();
            $this->parse_request( $query_args );
            $this->send_headers();
            $this->query_posts();
            $this->handle_404();
            $this->_do_action_ref_array( 'tp', array( &$this ) );
        }
    }
}else {die;}