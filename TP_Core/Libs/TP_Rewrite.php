<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 10:02
 */
namespace TP_Core\Libs;
if(ABSPATH){
    class TP_Rewrite extends Rewrite_Base {
        //temporary
        public $tp_filter;
        /**
         * @description Determines whether permalinks are being used.
         * @return bool
         */
        public function using_permalinks(): bool{
            return ! empty( $this->permalink_structure );
        }//352
        /**
         * @description Determines whether permalinks are being used and rewrite module is not enabled.
         * @return bool|int
         */
        public function using_index_permalinks(){
            if ( empty( $this->permalink_structure ) ) return false;
            return preg_match( '#^/*' . $this->index . '#', $this->permalink_structure );
        }//368
        /**
         * @description Determines whether permalinks are being used and rewrite module is enabled.
         * @return bool
         */
        public function using_mod_rewrite_permalinks(): bool{
            return $this->using_permalinks() && ! $this->using_index_permalinks();
        }//383
        /**
         * @description Indexes for matches for usage in preg_*() functions.
         * @param $number
         * @return string
         */
        public function preg_index( $number ): string{
            $match_prefix = '$';
            $match_suffix = '';
            if ( ! empty( $this->matches ) ) {
                $match_prefix = '$' . $this->matches . '[';
                $match_suffix = ']';
            }
            return "$match_prefix$number$match_suffix";
        }//402
        /**
         * @description Retrieves all page and attachments for pages URIs.
         * @return array
         */
        public function page_uri_index(): array{
            $tpdb = $this->_init_db();
            $pages = $tpdb->get_results( TP_SELECT . " ID, post_name, post_parent FROM ". $tpdb->posts ." WHERE post_type = 'page' AND post_status != 'auto-draft'" );
            $posts = $this->_get_page_hierarchy( $pages );
            if ( ! $posts ) return [[],[]];
            $posts = array_reverse( $posts, true );
            $page_uris            = [];
            $page_attachment_uris = [];
            foreach ( $posts as $id => $post ) {
                $uri         = $this->_get_page_uri( $id );
                $attachments = $tpdb->get_results( $tpdb->prepare( TP_SELECT . " ID, post_name, post_parent FROM " . $tpdb->posts ." WHERE post_type = 'attachment' AND post_parent = %d", $id ) );
                if ( ! empty( $attachments ) ) {
                    foreach ( $attachments as $attachment ) {
                        $attach_uri                          = $this->_get_page_uri( $attachment->ID );
                        $page_attachment_uris[ $attach_uri ] = $attachment->ID;
                    }
                }
                $page_uris[ $uri ] = $id;
            }
            return array( $page_uris, $page_attachment_uris );
        }//426
        /**
         * @description Retrieves all of the rewrite rules for pages.
         * @return array
         */
        public function page_rewrite_rules(): array{
            $this->_add_rewrite_tag( '%page_name%', '(.?.+?)', 'page_name=' );
            return $this->generate_rewrite_rules( $this->get_page_permanent_structure(), EP_PAGES, true, true, false, false );
        }//468
        /**
         * @description Retrieves date permalink structure, with year, month, and day.
         * @return bool|string
         */
        private function __get_date_permanent_structure(){
            if ( isset( $this->date_structure ) ) return $this->date_structure;
            if ( empty( $this->permalink_structure ) ) {
                $this->date_structure = '';
                return false;
            }
            $date_orders = [ '%year%/%monthnum%/%day%', '%day%/%monthnum%/%year%', '%monthnum%/%day%/%year%'];
            $this->date_structure = '';
            $date_order          = '';
            foreach ( $date_orders as $order ) {
                if ( false !== strpos( $this->permalink_structure, $order ) ) {
                    $date_order = $order;
                    break;
                }
            }
            if ( empty( $date_order ) ) $date_order = '%year%/%monthnum%/%day%';
            $front = $this->front;
            preg_match_all( '/%.+?%/', $this->permalink_structure, $tokens );
            $tok_index = 1;
            foreach ( (array) $tokens[0] as $token ) {
                if ( '%post_id%' === $token && ( $tok_index <= 3 ) ) {
                    $front .= 'date/';
                    break;
                }
                $tok_index++;
            }
            $this->date_structure = $front . $date_order;
            return $this->date_structure;
        }//494
        /**
         * @description Retrieves the year permalink structure without month and day.
         * @return bool|mixed|string
         */
        public function get_year_permanent_structure(){
            $structure = $this->__get_date_permanent_structure();
            if ( empty( $structure ) ) return false;
            $structure = str_replace( '%monthnum%', '', $structure );
            $structure .= str_replace( '%day%', '', $structure );
            $structure = preg_replace( '#/+#', '/', $structure );
            return $structure;
        }//551
        /**
         * @description Retrieves the month permalink structure without day and with year.
         * @return bool|mixed|string
         */
        public function get_month_permanent_structure(){
            $structure = $this->__get_date_permanent_structure();
            if ( empty( $structure ) ) return false;
            $structure = str_replace( '%day%', '', $structure );
            $structure = preg_replace( '#/+#', '/', $structure );
            return $structure;
        }//575
        /**
         * @description Retrieves the day permalink structure with month and year.
         * @return bool|string
         */
        public function get_day_permanent_structure(){
            return $this->__get_date_permanent_structure();
        }//597
        /**
         * @description Retrieves the permalink structure for categories.
         */
        public function get_category_permanent_structure(){
            return $this->get_extra_permanent_structure( 'category' );
        }//613
        /**
         * @description Retrieve the permalink structure for tags.
         */
        public function get_tag_permanent_structure() {
            return $this->get_extra_permanent_structure( 'post_tag' );
        }//629
        /**
         * @description Retrieves an extra permalink structure by name.
         * @param $name
         * @return bool
         */
        public function get_extra_permanent_structure( $name ): bool{
            if ( empty( $this->permalink_structure ) ) return false;
            if ( isset( $this->extra_permanent_structures[ $name ] ) )
                return $this->extra_permanent_structures[ $name ]['structure'];
            return false;
        }//641
        /**
         * @description Retrieves the author permalink structure.
         * @return bool|string
         */
        public function get_author_permanent_structure(){
            if ( isset( $this->author_structure ) )
                return $this->author_structure;
            if ( empty( $this->permalink_structure ) ) {
                $this->author_structure = '';
                return false;
            }
            $this->author_structure = $this->front . $this->author_base . '/%author%';
            return $this->author_structure;
        }//664
        /**
         * @description Retrieves the search permalink structure.
         * @return bool|string
         */
        public function get_search_permanent_structure(){
            if ( isset( $this->search_structure)) return $this->search_structure;
            if ( empty( $this->permalink_structure ) ) {
                $this->search_structure = '';
                return false;
            }
            $this->search_structure = $this->root . $this->search_base . '/%search%';
            return $this->search_structure;
        }//690
        /**
         * @description Retrieves the page permalink structure.
         * @return bool|string
         */
        public function get_page_permanent_structure(){
            if ( isset( $this->page_structure)) return $this->page_structure;
            if ( empty( $this->permalink_structure ) ) {
                $this->page_structure = '';
                return false;
            }
            $this->page_structure = $this->root . '%page_name%';
            return $this->page_structure;
        }//716
        /**
         * @description Retrieves the feed permalink structure.
         * @return bool|string
         */
        public function get_feed_permanent_structure(){
            if ( isset( $this->feed_structure ) ) return $this->feed_structure;
            if ( empty( $this->permalink_structure ) ) {
                $this->feed_structure = '';
                return false;
            }
            $this->feed_structure = $this->root . $this->feed_base . '/%feed%';
            return $this->feed_structure;
        }//742
        /**
         * @description Retrieves the comment feed permalink structure.
         * @return bool|string
         */
        public function get_comment_feed_permanent_structure(){
            if ( isset( $this->comment_feed_structure ) )
                return $this->comment_feed_structure;
            if ( empty( $this->permalink_structure ) ) {
                $this->comment_feed_structure = '';
                return false;
            }
            $this->comment_feed_structure = $this->root . $this->comments_base . '/' . $this->feed_base . '/%feed%';
            return $this->comment_feed_structure;
        }//768
        /**
         * @description Adds or updates existing rewrite tags (e.g. %post_name%).
         * @param $tag
         * @param $regex
         * @param $query
         */
        public function add_rewrite_tag( $tag, $regex, $query ): void{
            $position = array_search( $tag, $this->rewrite_code, true );
            if ( false !== $position && null !== $position ) {
                $this->rewrite_replace[ $position ] = $regex;
                $this->query_replace[ $position ]   = $query;
            } else {
                $this->rewrite_code[]    = $tag;
                $this->rewrite_replace[] = $regex;
                $this->query_replace[]   = $query;
            }
        }//799
        /**
         * @description Removes an existing rewrite tag.
         * @param $tag
         */
        public function remove_rewrite_tag( $tag ): void{
            $position = array_search( $tag, $this->rewrite_code, true );
            if ( false !== $position && null !== $position ) {
                unset( $this->rewrite_code[ $position ],$this->rewrite_replace[ $position ], $this->query_replace[ $position ] );
            }
        }//823
        /**
         * @description Generates rewrite rules from a permalink structure.
         * @param $permalink_structure
         * @param $ep_mask
         * @param bool $paged
         * @param bool $feed
         * @param $for_comments
         * @param $walk_dirs
         * @param bool $endpoints
         * @return array
         */
        public function generate_rewrite_rules( $permalink_structure, $ep_mask = EP_NONE, $paged = true, $feed = true, $for_comments = false, $walk_dirs = true, $endpoints = true ): array{
            $feed_regex2 = '';
            $rewrite = null;
            $rewrite_item = [];
            $sub1 = null;
            $sub1_tb = null;
            $sub1_feed = null;
            $sub1_feed_2 = null;
            $sub1_comment = null;
            $sub1_embed = null;
            $sub2 = null;
            $sub2_tb = null;
            $sub2_feed = null;
            $sub2_feed_2 = null;
            $sub2_comment = null;
            $sub2_embed = null;
            $sub_query = null;
            $sub_tb_query = null;
            $sub_feed_query = null;
            $sub_commend_query = null;
            $sub_embed_query = null;
            $trackback_match = null;
            $trackback_query = null;
            foreach ( (array) $this->feeds as $feed_name ) $feed_regex2 .= $feed_name . '|';
            $feed_regex2 = '(' . trim( $feed_regex2, '|' ) . ')/?$';
            $feed_regex = $this->feed_base . '/' . $feed_regex2;
            $trackback_regex = 'trackback/?$';
            $page_regex      = $this->pagination_base . '/?([0-9]{1,})/?$';
            $comment_regex   = $this->comments_pagination_base . '-([0-9]{1,})/?$';
            $embed_regex     = 'embed/?$';
            if ( $endpoints ) {
                $ep_query_append = [];
                foreach ( (array) $this->endpoints as $endpoint ) {
                    // Match everything after the endpoint name, but allow for nothing to appear there.
                    $ep_match = $endpoint[1] . '(/(.*))?/?$';
                    $ep_query = '&' . $endpoint[2] . '=';
                    $ep_query_append[ $ep_match ] = [$endpoint[0], $ep_query];
                }
            }
            $front = substr( $permalink_structure, 0, strpos( $permalink_structure, '%' ) );
            preg_match_all( '/%.+?%/', $permalink_structure, $tokens );
            $num_tokens = count( $tokens[0] );
            $index          = $this->index; //todo Probably not'index.php'.
            $feed_index      = $index;
            $trackback_index = $index;
            $embed_index     = $index;
            $queries = [];
            for ( $i = 0; $i < $num_tokens; ++$i ) {
                if ( 0 < $i ) $queries[ $i ] = $queries[ $i - 1 ] . '&';
                else $queries[ $i ] = '';
                $query_token    = str_replace( $this->rewrite_code, $this->query_replace, $tokens[0][ $i ] ) . $this->preg_index( $i + 1 );
                $queries[ $i ] .= $query_token;
            }
            $structure = $permalink_structure;
            if ( '/' !== $front ) $structure = str_replace( $front, '', $structure );
            $structure = trim( $structure, '/' );
            $dirs      = $walk_dirs ? explode( '/', $structure ) : [$structure];
            $front = ltrim($front, '/');
            $post_rewrite = [];
            $front_structure       = $front;
            foreach ($dirs as $jValue) {
                $front_structure .= $jValue . '/'; // Accumulate. see comment near explode('/', $structure) above.
                $front_structure  = ltrim( $front_structure, '/' );
                $match = str_replace( $this->rewrite_code, $this->rewrite_replace, $front_structure );
                $num_tokens = preg_match_all( '/%.+?%/', $front_structure, $tokens );
                $query = ( ! empty( $num_tokens ) && isset( $queries[ $num_tokens - 1 ] ) ) ? $queries[ $num_tokens - 1 ] : '';
                switch ($jValue) {
                    case '%year%':
                        $ep_mask_specific = EP_YEAR;
                        break;
                    case '%monthnum%':
                        $ep_mask_specific = EP_MONTH;
                        break;
                    case '%day%':
                        $ep_mask_specific = EP_DAY;
                        break;
                    default:
                        $ep_mask_specific = EP_NONE;
                }
                $page_match = $match . $page_regex;
                $page_query = $index . '?' . $query . '&paged=' . $this->preg_index( $num_tokens + 1 );
                $comment_match = $match . $comment_regex;
                $comment_query = $index . '?' . $query . '&cpage=' . $this->preg_index( $num_tokens + 1 );
                $root_comment_match = '';
                $root_comment_query = '';
                if ( $this->_get_option( 'page_on_front' ) ) {
                    // Create query for Root /comment-page-xx.
                    $root_comment_match = $match . $comment_regex;
                    $root_comment_query = $index . '?' . $query . '&page_id=' . $this->_get_option( 'page_on_front' ) . '&cpage=' . $this->preg_index( $num_tokens + 1 );
                }
                $feed_match = $match . $feed_regex;
                $feed_query = $feed_index . '?' . $query . '&feed=' . $this->preg_index( $num_tokens + 1 );
                $feed_match2 = $match . $feed_regex2;
                $feed_query2 = $feed_index . '?' . $query . '&feed=' . $this->preg_index( $num_tokens + 1 );
                $embed_match = $match . $embed_regex;
                $embed_query = $embed_index . '?' . $query . '&embed=true';
                if ( $for_comments ) {
                    $feed_query  .= '&with_comments=1';
                    $feed_query2 .= '&with_comments=1';
                }
                $rewrite = [];
                if ( $feed ) {
                    $rewrite = [
                        $feed_match  => $feed_query,
                        $feed_match2 => $feed_query2,
                        $embed_match => $embed_query,
                    ];
                }
                if ( $paged )
                    $rewrite_item[1] = array( $rewrite, [$page_match => $page_query] );
                if ( EP_PAGES & $ep_mask || EP_PERMALINK & $ep_mask ) {
                    $rewrite_item[2] = array( $rewrite, array( $comment_match => $comment_query ) );
                } elseif ( EP_ROOT & $ep_mask && $this->_get_option( 'page_on_front' ) ) {
                    $rewrite_item[3] = array( $rewrite, [$root_comment_match => $root_comment_query] );
                }
                $ep_query_append =[];//todo ?
                if ( $endpoints ) {
                    foreach ($ep_query_append as $regex => $ep ) {
                        if ( $ep[0] & $ep_mask || $ep[0] & $ep_mask_specific )
                            $rewrite[ $match . $regex ] = $index . '?' . $query . $ep[1] . $this->preg_index( $num_tokens + 2 );
                    }
                }
                if ( $num_tokens ) {
                    $post = false;
                    $page = false;
                    if ( strpos( $front_structure, '%post_name%' ) !== false || strpos( $front_structure, '%post_id%' ) !== false || strpos( $front_structure, '%page_name%' ) !== false
                        || ( strpos( $front_structure, '%year%' ) !== false && strpos( $front_structure, '%monthnum%' ) !== false && strpos( $front_structure, '%day%' ) !== false && strpos( $front_structure, '%hour%' ) !== false && strpos( $front_structure, '%minute%' ) !== false && strpos( $front_structure, '%second%' ) !== false )
                    ) {
                        $post = true;
                        if ( strpos( $front_structure, '%page_name%' ) !== false )
                            $page = true;
                    }
                    if ( ! $post ) {
                        foreach ( $this->_get_post_types( ['_builtin' => false] ) as $post_type_item ) {
                            if ( strpos( $front_structure, "%$post_type_item%" ) !== false ) {
                                $post = true;
                                $page = $this->_is_post_type_hierarchical( $post_type_item );
                                break;
                            }
                        }
                    }
                    if ( $post ) {  // Create query and regex for trackback.
                        $trackback_match = $match . $trackback_regex;
                        $trackback_query = $trackback_index . '?' . $query . '&tb=1';
                        // Create query and regex for embeds.
                        $embed_match = $match . $embed_regex;
                        $embed_query = $embed_index . '?' . $query . '&embed=true';
                        $match = rtrim( $match, '/' );// Trim slashes from the end of the regex for this dir.
                        $sub_match_base = str_replace( ['(', ')'], '', $match ); // Get rid of brackets.
                        $sub1 = $sub_match_base . '/([^/]+)/'; // Add a rule for at attachments, which take the form of <permalink>/some-text.
                        $sub1_tb = $sub1 . $trackback_regex; // Add trackback regex <permalink>/trackback/...
                        $sub1_feed = $sub1 . $feed_regex; // And <permalink>/feed/(atom|...)
                        $sub1_feed_2 = $sub1 . $feed_regex2;// And <permalink>/(feed|atom...)
                        $sub1_comment = $sub1 . $comment_regex;// And <permalink>/comment-page-xx
                        $sub1_embed = $sub1 . $embed_regex;// And <permalink>/embed/...
                        $sub2 = $sub_match_base . '/attachment/([^/]+)/';
                        $sub2_tb = $sub2 . $trackback_regex; // And add track_backs <permalink>/attachment/trackback.
                        $sub2_feed = $sub2 . $feed_regex; // Feeds, <permalink>/attachment/feed/(atom|...)
                        $sub2_feed_2 = $sub2 . $feed_regex2; // And feeds again on to this <permalink>/attachment/(feed|atom...)
                        $sub2_comment = $sub2 . $comment_regex; // And <permalink>/comment-page-xx
                        $sub2_embed = $sub2 . $embed_regex; // And <permalink>/embed/...
                        $sub_query         = $index . '?attachment=' . $this->preg_index( 1 );
                        $sub_tb_query      = $sub_query . '&tb=1';
                        $sub_feed_query    = $sub_query . '&feed=' . $this->preg_index( 2 );
                        $sub_commend_query = $sub_query . '&cpage=' . $this->preg_index( 2 );
                        $sub_embed_query   = $sub_query . '&embed=true';
                        if ( ! empty( $endpoints ) ) {
                            foreach ($ep_query_append as $regex => $ep ) {
                                if ( $ep[0] & EP_ATTACHMENT ) {
                                    $rewrite[ $sub1 . $regex ] = $sub_query . $ep[1] . $this->preg_index( 3 );
                                    $rewrite[ $sub2 . $regex ] = $sub_query . $ep[1] . $this->preg_index( 3 );
                                }
                            }
                        }
                        $sub1 .= '?$';
                        $sub2 .= '?$';
                        $match .= '(?:/([0-9]+))?/?$';
                        $query = $index . '?' . $query . '&page=' . $this->preg_index( $num_tokens + 1 );
                    }else{
                        $match .= '?$';
                        $query  = $index . '?' . $query;
                    }
                    $rewrite_item[4] = array( $rewrite, [$match => $query] );
                    if ( $post ) {
                        $rewrite_item[5] = [$trackback_match => $trackback_query];
                        $rewrite_item[6] = [$embed_match => $embed_query ];
                        if ( ! $page ) {
                            $rewrite_item[7] = [
                                $sub1         => $sub_query,
                                $sub1_tb      => $sub_tb_query ,
                                $sub1_feed    => $sub_feed_query ,
                                $sub1_feed_2  => $sub_feed_query ,
                                $sub1_comment => $sub_commend_query,
                                $sub1_embed   => $sub_embed_query,
                            ];
                        }
                        $rewrite_item[8] = array_merge([
                            $sub2         => $sub_query,
                            $sub2_tb      => $sub_tb_query,
                            $sub2_feed    => $sub_feed_query,
                            $sub2_feed_2  => $sub_feed_query,
                            $sub2_comment => $sub_commend_query,
                            $sub2_embed   => $sub_embed_query,
                        ], $rewrite );
                    }
                }
            }
            return array_merge($rewrite_item,$rewrite,$post_rewrite);
        }//873
        /**
         * @description Generates rewrite rules with permalink structure and walking directory only.
         * @param $permalink_structure
         * @param bool $walk_dirs
         * @return array
         */
        public function generate_rewrite_rule( $permalink_structure, $walk_dirs = false ): array{
            return $this->generate_rewrite_rules( $permalink_structure, EP_NONE, false, false, false, $walk_dirs );
        }//1249
        /**
         * @description Constructs rewrite matches and queries from permalink structure.
         * @return array|mixed
         */
        public function rewrite_rules(){
            $rewrite = [];
            if ( empty( $this->permalink_structure ) ) return $rewrite;
            $home_path = parse_url( $this->_home_url() ); //todo
            $robots_rewrite = ( empty( $home_path['path'] ) || '/' === $home_path['path'] ) ? array( 'robots\.txt$' => $this->index . '?robots=1' ) : array();
            $registration_pages = [];
            if ( $this->_is_multisite() && $this->_is_main_site() ) {
                $registration_pages['.*tp-signup.php$']   = $this->index . '?signup=true'; //todo
                $registration_pages['.*tp-activate.php$'] = $this->index . '?activate=true'; //todo
            }
            $post_rewrite = $this->generate_rewrite_rules( $this->permalink_structure, EP_PERMALINK );
            $post_rewrite = $this->_apply_filters( 'post_rewrite_rules', $post_rewrite );
            $date_rewrite = $this->generate_rewrite_rules( $this->__get_date_permanent_structure(), EP_DATE );
            $date_rewrite = $this->_apply_filters( 'date_rewrite_rules', $date_rewrite );
            $root_rewrite = $this->generate_rewrite_rules( $this->root . '/', EP_ROOT );
            $root_rewrite = $this->_apply_filters( 'root_rewrite_rules', $root_rewrite );
            $comments_rewrite = $this->generate_rewrite_rules( $this->root . $this->comments_base, EP_COMMENTS, false, true, true, false );
            $comments_rewrite = $this->_apply_filters( 'comments_rewrite_rules', $comments_rewrite );
            $search_structure = $this->get_search_permanent_structure();
            $search_rewrite   = $this->generate_rewrite_rules( $search_structure, EP_SEARCH );
            $search_rewrite = $this->_apply_filters( 'search_rewrite_rules', $search_rewrite );
            $author_rewrite = $this->generate_rewrite_rules( $this->get_author_permanent_structure(), EP_AUTHORS );
            $author_rewrite = $this->_apply_filters( 'author_rewrite_rules', $author_rewrite );
            $page_rewrite = $this->page_rewrite_rules();
            $page_rewrite = $this->_apply_filters( 'page_rewrite_rules', $page_rewrite );
            $rules = null;
            foreach ( $this->extra_permanent_structures as $permanent_structure_name => $structure_value ) {
                if ( is_array( $structure_value ) ) {
                    if ( count( $structure_value ) === 2 )
                        $rules = $this->generate_rewrite_rules( $structure_value[0], $structure_value[1] );
                    else $rules = $this->generate_rewrite_rules( $structure_value['structure'], $structure_value['ep_mask'], $structure_value['paged'], $structure_value['feed'], $structure_value['for_comments'], $structure_value['walk_dirs'], $structure_value['endpoints'] );
                } else $rules = $this->generate_rewrite_rules( $structure_value );
                $rules = $this->_apply_filters( "{$permanent_structure_name}_rewrite_rules", $rules );
            }
            $this->extra_rules_top = array_merge( $this->extra_rules_top, $rules );
            if ( $this->use_verbose_page_rules )
                $this->rules = array_merge( $this->extra_rules_top, $robots_rewrite, $registration_pages, $root_rewrite, $comments_rewrite, $search_rewrite, $author_rewrite, $date_rewrite, $page_rewrite, $post_rewrite, $this->extra_rules );
            else $this->rules = array_merge( $this->extra_rules_top, $robots_rewrite, $registration_pages, $root_rewrite, $comments_rewrite, $search_rewrite, $author_rewrite, $date_rewrite, $post_rewrite, $page_rewrite, $this->extra_rules );
            $this->_do_action_ref_array( 'generate_rewrite_rules', array( &$this ) );
            $this->rules = $this->_apply_filters( 'rewrite_rules_array', $this->rules );
            return $this->rules;
        }//1270
        /**
         * @description Retrieves the rewrite rules.
         * @return mixed
         */
        public function tp_rewrite_rules(){
            $this->rules = $this->_get_option( 'rewrite_rules' );
            if ( empty( $this->rules ) ) {
                $this->matches = 'matches';
                $this->rewrite_rules();
                if ( ! $this->_did_action( 'tp_loaded' ) ) {
                    $this->_add_action( 'tp_loaded', array( $this, 'flush_rules' ) );
                    return $this->rules;
                }
                $this->_update_option( 'rewrite_rules', $this->rules );
            }
            return $this->rules;
        }//1484
        /**
         * @description Retrieves mod_rewrite-formatted rewrite rules to write to .htaccess.
         * @return string
         */
        public function mod_rewrite_rules(): string{
            if ( ! $this->using_permalinks() ) return '';
            $site_root = parse_url( $this->_site_url() );
            if ( isset( $site_root['path'] ) )
                $site_root = $this->_trailingslashit( $site_root['path'] );
            $home_root = parse_url( $this->_home_url() );
            if ( isset( $home_root['path'] ) )
                $home_root = $this->_trailingslashit( $home_root['path'] );
            else $home_root = '/';
            $rules  = "<IfModule mod_rewrite.c>\n";
            $rules .= "RewriteEngine On\n";
            $rules .= "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n";
            $rules .= "RewriteBase $home_root\n";
            $rules .= "RewriteRule ^index\.php$ - [L]\n";
            foreach ( (array) $this->non_tp_rules as $match => $query ) {
                // Apache 1.3 does not support the reluctant (non-greedy) modifier.
                $match = str_replace( '.+?', '.+', $match );
                $rules .= 'RewriteRule ^' . $match . ' ' . $home_root . $query . " [QSA,L]\n";
            }
            if ( $this->use_verbose_rules ) {
                $this->matches = '';
                $rewrite       = $this->rewrite_rules();
                $num_rules     = count( $rewrite );
                $rules        .= "RewriteCond %{REQUEST_FILENAME} -f [OR]\n" .
                    "RewriteCond %{REQUEST_FILENAME} -d\n" .
                    "RewriteRule ^.*$ - [S=$num_rules]\n";

                foreach ( (array) $rewrite as $match => $query ) {
                    // Apache 1.3 does not support the reluctant (non-greedy) modifier.
                    $match = str_replace( '.+?', '.+', $match );
                    if ( strpos( $query, $this->index ) !== false )
                        $rules .= 'RewriteRule ^' . $match . ' ' . $home_root . $query . " [QSA,L]\n";
                    else $rules .= 'RewriteRule ^' . $match . ' ' . $site_root . $query . " [QSA,L]\n";
                }
            }else {
                $rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n" .
                    "RewriteCond %{REQUEST_FILENAME} !-d\n" .
                    "RewriteRule . {$home_root}{$this->index} [L]\n";
            }
            $rules .= "</IfModule>\n";
            $rules = $this->_apply_filters( 'mod_rewrite_rules', $rules );
            return $rules; //todo let see

        }//1512
        /**
         * @description Retrieves IIS7 URL Rewrite formatted rewrite rules to write to web.config file.
         * @param bool $add_parent_tags
         * @return mixed|string
         */
        public function iis7_url_rewrite_rules( $add_parent_tags = false ){
            $rules = '';
            if ( ! $this->using_permalinks() ) return '';
            if ( $add_parent_tags ) {
                $rules .= '<configuration><system.webServer>';
                $rules .= '<rewrite><rules>';
            }
            $rules .= '<rule name="TailoredPress: ' . $this->_esc_attr( $this->_home_url() ) . '" patternSyntax="Wildcard">';
            $rules .= '<match url="*" />';
            $rules .= '<conditions>';
            $rules .= '<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />';
            $rules .= '<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />';
            $rules .= '</conditions>';
            $rules .= '<action type="Rewrite" url="index.php" />';
            $rules .= '</rule>';
            if ( $add_parent_tags ) {
                $rules .= '</rules></rewrite>';
                $rules .= '</system.webServer></configuration>';
            }
            return $this->_apply_filters( 'iis7_url_rewrite_rules', $rules );
        }//1603
        /**
         * @description  Adds a rewrite rule that transforms a URL structure to a set of query vars.
         * @param $regex
         * @param $query
         * @param string $after
         */
        public function add_rule( $regex, $query, $after = 'bottom' ): void{
            if ( is_array( $query ) ) {
                $external = false;
                $query    = $this->_add_query_arg( $query, 'index.php' );
            } else {
                $index = false === strpos( $query, '?' ) ? strlen( $query ) : strpos( $query, '?' );
                $front = substr( $query, 0, $index );
                $external = $front !== $this->index;
            }
            if ( $external )
                $this->add_external_rule( $regex, $query );
            else if ( 'bottom' === $after )
                $this->extra_rules = array_merge( $this->extra_rules, array( $regex => $query ) );
            else
                $this->extra_rules_top = array_merge( $this->extra_rules_top, array( $regex => $query ) );
        }//1657
        /**
         * @description Adds a rewrite rule that doesn't correspond to index.php.
         * @param $regex
         * @param $query
         */
        public function add_external_rule( $regex, $query ): void{
            $this->non_tp_rules[ $regex ] = $query;
        }//1690
        /**
         * @description  Adds an endpoint, like /trackback/.
         * @param $name
         * @param $places
         * @param bool $query_var
         */
        public function add_endpoint( $name, $places, $query_var = true ): void{
            $this->endpoints[] = array( $places, $name, $query_var );
            $tp_core = $this->_init_core();
                if ( $query_var ) $tp_core->add_query_var( $query_var );
        }//1725
        /**
         * @description Adds a new permalink structure.
         * @param $name
         * @param $structure
         * @param array $args
         */
        public function add_permanent_structure( $name, $structure, $args = array()): void{
            if ( func_num_args() === 4 )
                $args['ep_mask'] = func_get_arg( 3 );
            $defaults = ['with_front' => true,'ep_mask' => EP_NONE,'paged' => true,'feed' => true,'for_comments' => false,'walk_dirs' => true,'endpoints' => true,];
            $args     = array_intersect_key( $args, $defaults );
            $args     = $this->_tp_parse_args( $args, $defaults );
            if ( $args['with_front'] )
                $structure = $this->front . $structure;
            else $structure = $this->root . $structure;
            $args['structure'] = $structure;
            $this->extra_permanent_structures[ $name ] = $args;
        }//1791
        /**
         * @description Removes a permalink structure.
         * @param $name
         */
        public function remove_permanent_structures( $name ): void{
            unset( $this->extra_permanent_structures[ $name ] );
        }//1829
        /**
         * @description Removes rewrite rules and then recreate rewrite rules.
         * @param bool $hard
         */
        public function flush_rules( $hard = true ): void{
            static $do_hard_later = null;
            $do_hard = null;
            if ( ! $this->_did_action( 'tp_loaded' ) ) {
                $this->_add_action( 'tp_loaded', array( $this, 'flush_rules' ) );
                $do_hard_later = ( isset( $do_hard_later ) ) ? $do_hard_later || $hard : $hard;
                return;
            }
            if ( $do_hard_later !== null ) {
                $hard = false;
                $do_hard = $do_hard_later;
                unset( $do_hard );
            }
            $this->_update_option( 'rewrite_rules', '' );
            $this->tp_rewrite_rules();
            if ( ! $hard || ! $this->_apply_filters( 'flush_rewrite_rules_hard', true ) ) return;
            //todo, might become $this?
            if ( function_exists( '__save_mod_rewrite_rules' ) )
                $this->_save_mod_rewrite_rules();
            if ( function_exists( '__iis7_save_url_rewrite_rules' ) )
                $this->_iis7_save_url_rewrite_rules();
        }//1843
        /**
         * @description Sets up the object's properties.
         */
        public function init(): void{
            $this->extra_rules         = [];
            $this->non_tp_rules        = [];
            $this->endpoints           = [];
            $this->permalink_structure = $this->_get_option( 'permalink_structure' );
            $this->front               = substr( $this->permalink_structure, 0, strpos( $this->permalink_structure, '%' ) );
            $this->root                = '';
            if ( $this->using_index_permalinks() )
                $this->root = $this->index . '/';
            unset( $this->author_structure , $this->date_structure, $this->page_structure , $this->search_structure , $this->feed_structure,$this->comment_feed_structure );
            //$this->use_trailing_slashes = ( '/' === $this->permalink_structure[strlen($this->permalink_structure) - 1]);
            /** @noinspection SubStrUsedAsArrayAccessInspection */
            $this->use_trailing_slashes =  ( '/' === substr( $this->permalink_structure, -1, 1 ) );
            if ( preg_match( '/^[^%]*%(?:post_name|category|tag|author)%/', $this->permalink_structure ) )
                $this->use_verbose_page_rules = true;
            else $this->use_verbose_page_rules = false;
        }//1890
        /**
         * @description  Sets the main permalink structure for the site.
         * @param $permalink_structure
         */
        public function set_permalink_structure( $permalink_structure ): void{
            if ( $permalink_structure !== $this->permalink_structure ) {
                $old_permalink_structure = $this->permalink_structure;
                $this->_update_option( 'permalink_structure', $permalink_structure );
                $this->init();
                $this->_do_action( 'permalink_structure_changed', $old_permalink_structure, $permalink_structure );
            }
        }//1933
        /**
         * @description Sets the category base for the category permalink.
         * @param $category_base
         */
        public function set_category_base( $category_base ): void{
            if ( $this->_get_option( 'category_base' ) !== $category_base ) {
                $this->_update_option( 'category_base', $category_base );
                $this->init();
            }
        }//1963
        /**
         * @description Sets the tag base for the tag permalink.
         * @param $tag_base
         */
        public function set_tag_base( $tag_base ): void{
            if ( $this->_get_option( 'tag_base' ) !== $tag_base ) {
                $this->_update_option( 'tag_base', $tag_base );
                $this->init();
            }
        }//1981
        /**
         * TP_Rewrite constructor.
         * @description Constructor - Calls init(), which runs setup.
         */
        public function __construct(){
            $this->_tpdb = $this->_init_db();
            $this->_tp_query = $this->_init_query();
            $this->init();
        }//1993
    }
}else die;