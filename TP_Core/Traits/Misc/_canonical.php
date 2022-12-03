<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 18:24
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Libs\TP_Term;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _canonical{
        use _init_db,_init_rewrite,_init_queries,_init_core,_init_error;
        public $tp_sitemaps; //todo move this out
        /**
         * @description Redirects incoming links to the proper URL based on the site url.
         * @param null $requested_url
         * @param bool $do_redirect
         * @return bool|void
         */
        protected function _redirect_canonical( $requested_url = null, $do_redirect = true ){
            $this->tpdb = $this->_init_db();
            $this->tp_core = $this->_init_core();
            $this->tp_rewrite = $this->_init_rewrite();
            $this->tp_query = $this->_init_query();
            if ( isset( $_SERVER['REQUEST_METHOD'] ) && ! in_array( strtoupper( $_SERVER['REQUEST_METHOD'] ),['GET', 'HEAD'], true ) ) {
                return;
            }
            if ( $this->_is_preview() && $this->_get_query_var( 'p' ) && 'publish' === $this->_get_post_status( $this->_get_query_var( 'p' ) ) ) {
                if ( ! isset( $_GET['preview_id'], $_GET['preview_nonce'] ) || ! $this->_tp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . (int) $_GET['preview_id'] )){
                    $this->tp_query->is_preview = false;
                }
            }
            if ( $this->_is_admin() || $this->_is_search() || $this->_is_preview() || $this->_is_trackback() || $this->_is_favicon() || ( $this->tp_is_IIS && ! $this->_iis7_supports_permalinks())){
                return;
            }
            if ( ! $requested_url && isset( $_SERVER['HTTP_HOST'] ) ) {
                // Build the URL in the address bar.
                $requested_url  = $this->_is_ssl() ? 'https://' : 'http://';
                $requested_url .= $_SERVER['HTTP_HOST'];
                $requested_url .= $_SERVER['REQUEST_URI'];
            }
            $original = parse_url( $requested_url );
            if ( false === $original ){return;}
            $redirect     = $original;
            $redirect_url = false;
            $redirect_obj = false;
            // Notice fixing.
            if ( ! isset( $redirect['path'])){$redirect['path'] = '';}
            if ( ! isset( $redirect['query'])){$redirect['query'] = '';}
            $redirect['path'] = preg_replace( '|(%C2%A0)+$|i', '', $redirect['path'] );
            if ( $this->_get_query_var('preview')){$redirect['query'] = $this->_remove_query_arg( 'preview', $redirect['query'] );}
            $post_id = $this->_get_query_var( 'p' );
            if ($post_id && $this->_is_feed()) {
                $redirect_url = $this->_get_post_comments_feed_link( $post_id, $this->_get_query_var( 'feed' ) );
                $redirect_obj = $this->_get_post( $post_id );
                if ( $redirect_url ) {
                    $redirect['query'] = $this->__remove_qs_args_if_not_in_url($redirect['query'],['p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type', 'feed' ],$redirect_url);
                    $redirect['path'] = parse_url( $redirect_url, PHP_URL_PATH );
                }
            }
            if ($this->tp_query->post_count < 1 && $post_id &&  $this->_is_singular() ) {
                $vars = $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT ." post_type, post_parent FROM $this->tpdb->posts WHERE ID = %d", $post_id ) );
                if ( ! empty( $vars[0] ) ) {
                    $vars = $vars[0];
                    if ( 'revision' === $vars->post_type && $vars->post_parent > 0 ){$post_id = $vars->post_parent;}
                    $redirect_url = $this->_get_permalink( $post_id );
                    $redirect_obj = $this->_get_post( $post_id );
                    if ( $redirect_url ) {
                        $redirect['query'] = $this->__remove_qs_args_if_not_in_url( $redirect['query'], ['p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type'], $redirect_url);
                    }
                }
            }
            if ( $this->_is_404() ) {
                // Redirect ?page_id, ?p=, ?attachment_id= to their respective URLs.
                $post_id = max( $this->_get_query_var( 'p' ), $this->_get_query_var( 'page_id' ), $this->_get_query_var( 'attachment_id' ) );
                $redirect_post = $post_id ? $this->_get_post( $post_id ) : false;
                if ( $redirect_post ) {
                    $post_type_obj = $this->_get_post_type_object( $redirect_post->post_type );
                    if ( $post_type_obj && $post_type_obj->public && 'auto-draft' !== $redirect_post->post_status ) {
                        $redirect_url = $this->_get_permalink( $redirect_post );
                        $redirect_obj = $this->_get_post( $redirect_post );
                        $redirect['query'] = $this->__remove_qs_args_if_not_in_url(
                            $redirect['query'], ['p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type'], $redirect_url);
                    }
                }
                $year  = $this->_get_query_var( 'year' );
                $month = $this->_get_query_var( 'monthnum' );
                $day   = $this->_get_query_var( 'day' );
                if ( $year && $month && $day ) {
                    $date = sprintf( '%04d-%02d-%02d', $year, $month, $day );
                    if ( ! $this->_tp_check_date( $month, $day, $year, $date ) ) {
                        $redirect_url = $this->_get_month_link( $year, $month );
                        $redirect['query'] = $this->__remove_qs_args_if_not_in_url($redirect['query'],['year', 'monthnum', 'day'],$redirect_url);
                    }
                } elseif ( $year && $month && $month > 12 ) {
                    $redirect_url = $this->_get_year_link( $year );
                    $redirect['query'] = $this->__remove_qs_args_if_not_in_url($redirect['query'],['year', 'monthnum'], $redirect_url);
                }
                // Strip off non-existing <!--nextpage--> links from single posts or pages.
                if ( $this->_get_query_var( 'page' ) ) {
                    $post_id = 0;
                    if ( $this->tp_query->queried_object instanceof TP_Post ) {
                        $post_id = $this->tp_query->queried_object->ID;
                    } elseif ( $this->tp_query->post ) {
                        $post_id = $this->tp_query->post->ID;
                    }
                    if ( $post_id ) {
                        $redirect_url = $this->_get_permalink( $post_id );
                        $redirect_obj = $this->_get_post( $post_id );
                        $redirect['path']  = rtrim( $redirect['path'], (int) $this->_get_query_var( 'page' ) . '/' );
                        $redirect['query'] = $this->_remove_query_arg( 'page', $redirect['query'] );
                    }
                }
                if ( ! $redirect_url ) {
                    $redirect_url = $this->_redirect_guess_404_permalink();
                    if ( $redirect_url ) {
                        $redirect['query'] = $this->__remove_qs_args_if_not_in_url(
                            $redirect['query'], ['page', 'feed', 'p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type'],$redirect_url);
                    }
                }
            } elseif ( is_object( $this->tp_rewrite ) && $this->tp_rewrite->using_permalinks() ) {
                if (! $redirect_url && $this->_is_attachment() && ! array_diff( array_keys( $this->tp_core->query_vars ),['attachment', 'attachment_id'])){
                    if ( ! empty( $_GET['attachment_id'] ) ) {
                        $redirect_url = $this->_get_attachment_link( $this->_get_query_var( 'attachment_id' ) );
                        $redirect_obj = $this->_get_post( $this->_get_query_var( 'attachment_id' ) );
                        if ( $redirect_url ) {$redirect['query'] = $this->_remove_query_arg( 'attachment_id', $redirect['query'] );}
                    } else {
                        $redirect_url = $this->_get_attachment_link();
                        $redirect_obj = $this->_get_post();
                    }
                }elseif (! empty( $_GET['p'] ) && ! $redirect_url &&  $this->_is_single()) {
                    $redirect_url = $this->_get_permalink( $this->_get_query_var( 'p' ) );
                    $redirect_obj = $this->_get_post( $this->_get_query_var( 'p' ) );
                    if ( $redirect_url ) {
                        $redirect['query'] = $this->_remove_query_arg( array( 'p', 'post_type' ), $redirect['query'] );
                    }
                } elseif ( ! empty( $_GET['name'] ) && ! $redirect_url && $this->_is_single() ) {
                    $redirect_url = $this->_get_permalink( $this->tp_query->get_queried_object_id() );
                    $redirect_obj = $this->_get_post( $this->tp_query->get_queried_object_id() );
                    if ( $redirect_url ) { $redirect['query'] = $this->_remove_query_arg( 'name', $redirect['query'] );}
                }elseif (! empty( $_GET['page_id'] ) && ! $redirect_url && $this->_is_page() ) {
                    $redirect_url = $this->_get_permalink( $this->_get_query_var( 'page_id' ) );
                    $redirect_obj = $this->_get_post( $this->_get_query_var( 'page_id' ) );

                    if ( $redirect_url ) {
                        $redirect['query'] = $this->_remove_query_arg( 'page_id', $redirect['query'] );
                    }
                } elseif (! $redirect_url &&  $this->_is_page() && ! $this->_is_feed() && 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_queried_object_id() === (int) $this->_get_option( 'page_on_front' )
                ) { $redirect_url = $this->_home_url( '/' );
                } elseif ( ! empty( $_GET['page_id'] ) && ! $redirect_url && $this->_is_home()
                    && 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_query_var( 'page_id' ) === (int) $this->_get_option( 'page_for_posts' )
                ) {
                    $redirect_url = $this->_get_permalink( $this->_get_option( 'page_for_posts' ) );
                    $redirect_obj = $this->_get_post( $this->_get_option( 'page_for_posts' ) );
                    if ( $redirect_url ) {$redirect['query'] = $this->_remove_query_arg( 'page_id', $redirect['query'] );}
                }elseif ( ! empty( $_GET['m'] ) && ( $this->_is_year() || $this->_is_month() || $this->_is_day() ) ) {
                    $m = $this->_get_query_var( 'm' );
                    switch ( strlen( $m ) ) {
                        case 4: // Yearly.
                            $redirect_url = $this->_get_year_link( $m );
                            break;
                        case 6: // Monthly.
                            $redirect_url = $this->_get_month_link( substr( $m, 0, 4 ), substr( $m, 4, 2 ) );
                            break;
                        case 8: // Daily.
                            $redirect_url = $this->_get_day_link( substr( $m, 0, 4 ), substr( $m, 4, 2 ), substr( $m, 6, 2 ) );
                            break;
                    }
                    if ( $redirect_url ) {
                        $redirect['query'] = $this->_remove_query_arg( 'm', $redirect['query'] );
                    }
                    // Now moving on to non ?m=X year/month/day links.
                }elseif ( $this->_is_date() ) {
                    $year  = $this->_get_query_var( 'year' );
                    $month = $this->_get_query_var( 'monthnum' );
                    $day   = $this->_get_query_var( 'day' );
                    if ( $month && $year && ! empty( $_GET['day'] ) && $this->_is_day() ) {
                        $redirect_url = $this->_get_day_link( $year, $month, $day );
                        if ( $redirect_url ) {
                            $redirect['query'] = $this->_remove_query_arg( array( 'year', 'monthnum', 'day' ), $redirect['query'] );
                        }
                    } elseif ($year && ! empty( $_GET['monthnum'] ) && $this->_is_month() ) {
                        $redirect_url = $this->_get_month_link( $year, $month );
                        if ( $redirect_url ) {
                            $redirect['query'] = $this->_remove_query_arg( array( 'year', 'monthnum' ), $redirect['query'] );
                        }
                    } elseif (! empty( $_GET['year'] ) && $this->_is_year()) {
                        $redirect_url = $this->_get_year_link( $year );
                        if ( $redirect_url ) {$redirect['query'] = $this->_remove_query_arg( 'year', $redirect['query'] );}
                    }
                }elseif ( ! empty( $_GET['author'] ) && $this->_is_author() && preg_match( '|^\d+$|', $_GET['author'] ) ) {
                    $author = $this->_get_user_data( $this->_get_query_var( 'author' ) );
                    if ( false !== $author
                        && $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->posts WHERE $this->tpdb->posts.post_author = %d AND $this->tpdb->posts.post_status = 'publish' LIMIT 1", $author->ID ) )
                    ) {
                        $redirect_url = $this->_get_author_posts_url( $author->ID, $author->user_nicename );
                        $redirect_obj = $author;
                        if ( $redirect_url ) {
                            $redirect['query'] = $this->_remove_query_arg( 'author', $redirect['query'] );
                        }
                    }
                }elseif ( $this->_is_category() || $this->_is_tag() || $this->_is_tax() ) { // Terms (tags/categories).
                    $term_count = 0;
                    foreach ( $this->tp_query->tax_query->queried_terms as $tax_query ) {
                        $term_count += count( $tax_query['terms'] );
                    }
                    $_obj = $this->tp_query->get_queried_object();
                    $obj = null;
                    if($_obj instanceof TP_Term ){ $obj = $_obj;}
                    if ( $term_count <= 1 && ! empty( $obj->term_id ) ) {
                        $tax_url = $this->_get_term_link( (int) $obj->term_id, $obj->taxonomy );
                        if (!empty($redirect['query']) && $tax_url && !$this->_init_error($tax_url)) {
                            // Strip taxonomy query vars off the URL.
                            $qv_remove = array( 'term', 'taxonomy' );
                            if ( $this->_is_category() ) {
                                $qv_remove[] = 'category_name';
                                $qv_remove[] = 'cat';
                            } elseif ( $this->_is_tag() ) {
                                $qv_remove[] = 'tag';
                                $qv_remove[] = 'tag_id';
                            } else {
                                // Custom taxonomies will have a custom query var, remove those too.
                                $tax_obj = $this->_get_taxonomy( $obj->taxonomy );
                                if ( false !== $tax_obj->query_var ) {
                                    $qv_remove[] = $tax_obj->query_var;
                                }
                            }
                            $rewrite_vars = array_diff( array_keys( $this->tp_query->query_main ), array_keys( $_GET ) );
                            // Check to see if all the query vars are coming from the rewrite, none are set via $_GET.
                            if ( ! array_diff( $rewrite_vars, array_keys( $_GET ) ) ) {
                                // Remove all of the per-tax query vars.
                                $redirect['query'] = $this->_remove_query_arg( $qv_remove, $redirect['query'] );
                                // Create the destination URL for this taxonomy.
                                $tax_url = parse_url( $tax_url );
                                if ( ! empty( $tax_url['query'] ) ) {
                                    // Taxonomy accessible via ?taxonomy=...&term=... or any custom query var.
                                    parse_str( $tax_url['query'], $query_vars );
                                    $redirect['query'] = $this->_add_query_arg( $query_vars, $redirect['query'] );
                                } else {
                                    // Taxonomy is accessible via a "pretty URL".
                                    $redirect['path'] = $tax_url['path'];
                                }
                            } else {
                                // Some query vars are set via $_GET. Unset those from $_GET that exist via the rewrite.
                                foreach ( $qv_remove as $_qv ) {
                                    if ( isset( $rewrite_vars[ $_qv ] ) ) {
                                        $redirect['query'] = $this->_remove_query_arg( $_qv, $redirect['query'] );
                                    }
                                }
                            }
                        }
                    }
                }elseif ( $this->_is_single() && strpos( $this->tp_rewrite->permalink_structure, '%category%' ) !== false ) {
                    $category_name = $this->_get_query_var( 'category_name' );
                    if ( $category_name ) {
                        $category = $this->_get_category_by_path( $category_name );
                        if ( ! $category || $this->_init_error( $category )
                            || ! $this->_has_term( $category->term_id, 'category', $this->tp_query->get_queried_object_id() )
                        ) {
                            $redirect_url = $this->_get_permalink( $this->tp_query->get_queried_object_id() );
                            $redirect_obj = $this->_get_post( $this->tp_query->get_queried_object_id() );
                        }
                    }
                }
                // Post paging.
                if ( $this->_is_singular() && $this->_get_query_var( 'page' ) ) {
                    $page = $this->_get_query_var( 'page' );
                    if ( ! $redirect_url ) {
                        $redirect_url = $this->_get_permalink( $this->_get_queried_object_id() );
                        $redirect_obj = $this->_get_post( $this->_get_queried_object_id() );
                    }
                    if ( $page > 1 ) {
                        $redirect_url = $this->_trailingslashit( $redirect_url );
                        if ( $this->_is_front_page() ) {
                            $redirect_url .= $this->_user_trailingslashit( "$this->_tp_rewrite->pagination_base/$page", 'paged' );
                        } else {
                            $redirect_url .= $this->_user_trailingslashit( $page, 'single_paged' );
                        }
                    }
                    $redirect['query'] = $this->_remove_query_arg( 'page', $redirect['query'] );
                }
                if ( $this->_get_query_var( 'sitemap' ) ) {
                    $redirect_url      = $this->_get_sitemap_url( $this->_get_query_var( 'sitemap' ), $this->_get_query_var( 'sitemap-subtype' ), $this->_get_query_var( 'paged' ) );
                    $redirect['query'] = $this->_remove_query_arg( array( 'sitemap', 'sitemap-subtype', 'paged' ), $redirect['query'] );
                }elseif ( $this->_get_query_var( 'paged' ) || $this->_is_feed() || $this->_get_query_var( 'cpage' ) ) {
                    $paged = $this->_get_query_var( 'paged' );
                    $feed  = $this->_get_query_var( 'feed' );
                    $cpage = $this->_get_query_var( 'cpage' );
                    while ( preg_match( "#/$this->tp_rewrite->pagination_base/?\d+?(/+)?$#", $redirect['path'] )
                        || preg_match( '#/(comments/?)?(feed|rss2?|rdf|atom)(/+)?$#', $redirect['path'] )
                        || preg_match( "#/{$this->tp_rewrite->comments_pagination_base}-\d+(/+)?$#", $redirect['path'] )
                    ) {
                        // Strip off any existing paging.
                        $redirect['path'] = preg_replace( "#/$this->tp_rewrite->pagination_base/?\d+?(/+)?$#", '/', $redirect['path'] );
                        // Strip off feed endings.
                        $redirect['path'] = preg_replace( '#/(comments/?)?(feed|rss2?|rdf|atom)(/+|$)#', '/', $redirect['path'] );
                        // Strip off any existing comment paging.
                        $redirect['path'] = preg_replace( "#/{$this->tp_rewrite->comments_pagination_base}-\d+?(/+)?$#", '/', $redirect['path'] );
                    }
                    $addl_path    = '';
                    $default_feed = $this->_get_default_feed();

                    if ( $this->_is_feed() && in_array( $feed, $this->tp_rewrite->feeds, true ) ) {
                        $addl_path = ! empty( $addl_path ) ? $this->_trailingslashit( $addl_path ) : '';
                        if ( ! $this->_is_singular() && $this->_get_query_var( 'with_comments' ) ) {
                            $addl_path .= 'comments/';
                        }
                        if ( ( 'rss' === $default_feed && 'feed' === $feed ) || 'rss' === $feed ) {
                            $format = ( 'rss2' === $default_feed ) ? '' : 'rss2';
                        } else {
                            $format = ( $default_feed === $feed || 'feed' === $feed ) ? '' : $feed;
                        }
                        $addl_path .= $this->_user_trailingslashit( 'feed/' . $format, 'feed' );
                        $redirect['query'] = $this->_remove_query_arg( 'feed', $redirect['query'] );
                    }elseif ('old' === $feed && $this->_is_feed()) {
                        $old_feed_files = array(
                            'tp-atom.php'         => 'atom',
                            'tp-commentsrss2.php' => 'comments_rss2',
                            'tp-feed.php'         => $default_feed,
                            'tp-rdf.php'          => 'rdf',
                            'tp-rss.php'          => 'rss2',
                            'tp-rss2.php'         => 'rss2',
                        );//todo sort this out
                        if ( isset( $old_feed_files[ basename( $redirect['path'] ) ] ) ) {
                            $redirect_url = $this->_get_feed_link( $old_feed_files[ basename( $redirect['path'] ) ] );
                            $this->_tp_redirect( $redirect_url, 301 );
                            die();
                        }
                    }
                    if ( $paged > 0 ) {
                        $redirect['query'] = $this->_remove_query_arg( 'paged', $redirect['query'] );
                        if ( ! $this->_is_feed() ) {
                            if ( ! $this->_is_single() ) {
                                $addl_path = ! empty( $addl_path ) ? $this->_trailingslashit( $addl_path ) : '';
                                if ( $paged > 1 ) {
                                    $addl_path .= $this->_user_trailingslashit( "$this->tp_rewrite->pagination_base/$paged", 'paged' );
                                }
                            }
                        } elseif ( $paged > 1 ) {
                            $redirect['query'] = $this->_add_query_arg( 'paged', $paged, $redirect['query'] );
                        }
                    }
                    $default_comments_page = $this->_get_option( 'default_comments_page' );
                    if ((('newest' !== $default_comments_page && $cpage > 1)||('newest' === $default_comments_page && $cpage > 0)) && $this->_get_option( 'page_comments' )) {
                        $addl_path  = ( ! empty( $addl_path ) ? $this->_trailingslashit( $addl_path ) : '' );
                        $addl_path .= $this->_user_trailingslashit( $this->tp_rewrite->comments_pagination_base . '-' . $cpage, 'commentpaged' );
                        $redirect['query'] = $this->_remove_query_arg( 'cpage', $redirect['query'] );
                    }
                    $redirect['path'] = preg_replace( '|/' . preg_quote( $this->tp_rewrite->index, '|' ) . '/?$|', '/', $redirect['path'] );
                    $redirect['path'] = $this->_user_trailingslashit( $redirect['path'] );
                    if ( ! empty( $addl_path ) && $this->tp_rewrite->using_index_permalinks() && strpos( $redirect['path'], '/' . $this->tp_rewrite->index . '/' ) === false) {
                        $redirect['path'] = $this->_trailingslashit( $redirect['path'] ) . $this->tp_rewrite->index . '/';
                    }
                    if ( ! empty( $addl_path ) ) { $redirect['path'] = $this->_trailingslashit( $redirect['path'] ) . $addl_path; }
                    $redirect_url = $redirect['scheme'] . '://' . $redirect['host'] . $redirect['path'];
                }
                if ( 'tp-register.php' === basename( $redirect['path'] ) ) {
                    if ( $this->_is_multisite() ) {
                        /** This filter is documented in wp-login.php */
                        $redirect_url = $this->_apply_filters( 'tp_signup_location', $this->_network_site_url( 'tp-signup.php' ) );
                    } else {
                        $redirect_url = $this->_tp_registration_url();
                    }
                    $this->_tp_redirect( $redirect_url, 301 );
                    die();
                }
            }
            $redirect['query'] = preg_replace( '#^\??&*?#', '', $redirect['query'] );
            if ( $redirect_url && ! empty( $redirect['query'] ) ) {
                parse_str( $redirect['query'], $_parsed_query );
                $redirect = parse_url( $redirect_url );
                if ( ! empty( $_parsed_query['name'] ) && ! empty( $redirect['query'] ) ) {
                    parse_str( $redirect['query'], $_parsed_redirect_query );
                    if ( empty( $_parsed_redirect_query['name'] ) ) {
                        unset( $_parsed_query['name'] );
                    }
                }
                $_parsed_query = array_combine(
                    $this->_raw_url_encode_deep( array_keys( $_parsed_query ) ),
                    $this->_raw_url_encode_deep( array_values( $_parsed_query ) )
                );
                $redirect_url = $this->_add_query_arg( $_parsed_query, $redirect_url );
            }
            if ( $redirect_url ) {
                $redirect = parse_url( $redirect_url );
            }
            $user_home = parse_url( $this->_home_url() );
            if ( ! empty( $user_home['host'] ) ) {$redirect['host'] = $user_home['host'];}
            if ( empty( $user_home['path'] ) ) { $user_home['path'] = '/';}
            // Handle ports.
            if ( ! empty( $user_home['port'] ) ) { $redirect['port'] = $user_home['port'];}
            else {unset( $redirect['port'] );}
            // Trailing /index.php.
            $redirect['path'] = preg_replace( '|/' . preg_quote( $this->tp_rewrite->index, '|' ) . '/*?$|', '/', $redirect['path'] );
            $punctuation_pattern = implode('|',array_map('preg_quote',[' ','%20','!','%21','"','%22',"'",'%27','(','%28',
                ')','%29',',','%2C','.','%2E',';', '%3B','{','%7B','}','%7D','%E2%80%9C', '%E2%80%9D',]));
               //01 Space. //02 Exclamation mark.//03 Double quote.//04 Single quote.//05 Opening bracket.
               //06 Closing bracket.//07 Comma.//08 Period. //09 Semicolon.//10 Opening curly bracket.
               //11 Closing curly bracket.//12 Opening curly quote.//13 Closing curly quote.
            $redirect['path'] = preg_replace( "#($punctuation_pattern)+$#", '', $redirect['path'] );
            if ( ! empty( $redirect['query'] ) ) {
                // Remove trailing spaces and end punctuation from certain terminating query string args.
                $redirect['query'] = preg_replace( "#((^|&)(p|page_id|cat|tag)=[^&]*?)($punctuation_pattern)+$#", '$1', $redirect['query'] );
                // Clean up empty query strings.
                $redirect['query'] = trim( preg_replace( '#(^|&)(p|page_id|cat|tag)=?(&|$)#', '&', $redirect['query'] ), '&' );
                // Redirect obsolete feeds.
                $redirect['query'] = preg_replace( '#(^|&)feed=rss(&|$)#', '$1feed=rss2$2', $redirect['query'] );
                // Remove redundant leading ampersands.
                $redirect['query'] = preg_replace( '#^\??&*?#', '', $redirect['query'] );
            }
            // Strip /index.php/ when we're not using PATHINFO permalinks.
            if ( ! $this->tp_rewrite->using_index_permalinks() ) {
                $redirect['path'] = str_replace( '/' . $this->tp_rewrite->index . '/', '/', $redirect['path'] );
            }
            // Trailing slashes.
            if ( is_object( $this->tp_rewrite ) && $this->tp_rewrite->using_permalinks()
                && ! $this->_is_404() && ( ! $this->_is_front_page() || ($this->_is_front_page() && $this->_get_query_var('paged') > 1))
            ) {
                $user_ts_type = '';
                if ( $this->_get_query_var( 'paged' ) > 0 ) {
                    $user_ts_type = 'paged';
                } else {
                    foreach ( array( 'single', 'category', 'page', 'day', 'month', 'year', 'home' ) as $type ) {
                        $func = 'is_' . $type;
                        if ($func()) {
                            $user_ts_type = $type;
                            break;
                        }
                    }
                }
                $redirect['path'] = $this->_user_trailingslashit( $redirect['path'], $user_ts_type );
            } elseif ( $this->_is_front_page() ) {
                $redirect['path'] = $this->_trailingslashit( $redirect['path'] );
            }
            if ( $this->_is_robots()
                || ! empty( $this->_get_query_var( 'sitemap' ) ) || ! empty( $this->_get_query_var( 'sitemap-stylesheet' ) )
            ) {
                $redirect['path'] = $this->_untrailingslashit( $redirect['path'] );
            }
            // Strip multiple slashes out of the URL.
            if ( strpos( $redirect['path'], '//' ) > -1 ) {
                $redirect['path'] = preg_replace( '|/+|', '/', $redirect['path'] );
            }
            // Always trailing slash the Front Page URL.
            if ( $this->_trailingslashit( $redirect['path'] ) === $this->_trailingslashit( $user_home['path'] ) ) {
                $redirect['path'] = $this->_trailingslashit( $redirect['path'] );
            }
            $original_host_low = strtolower( $original['host'] );
            $redirect_host_low = strtolower( $redirect['host'] );
            if ( $original_host_low === $redirect_host_low
                || ( 'www.' . $original_host_low !== $redirect_host_low
                    && 'www.' . $redirect_host_low !== $original_host_low )
            ) {
                $redirect['host'] = $original['host'];
            }
            $compare_original = array( $original['host'], $original['path'] );
            if ( ! empty( $original['port'] ) ) {
                $compare_original[] = $original['port'];
            }
            if ( ! empty( $original['query'] ) ) {
                $compare_original[] = $original['query'];
            }
            $compare_redirect = array( $redirect['host'], $redirect['path'] );
            if ( ! empty( $redirect['port'] ) ) {
                $compare_redirect[] = $redirect['port'];
            }
            if ( ! empty( $redirect['query'] ) ) {
                $compare_redirect[] = $redirect['query'];
            }
            if ( $compare_original !== $compare_redirect ) {
                $redirect_url = $redirect['scheme'] . '://' . $redirect['host'];
                if ( ! empty( $redirect['port'] ) ) {
                    $redirect_url .= ':' . $redirect['port'];
                }
                $redirect_url .= $redirect['path'];
                if ( ! empty( $redirect['query'] ) ) {
                    $redirect_url .= '?' . $redirect['query'];
                }
            }
            if ( ! $redirect_url || $redirect_url === $requested_url ) {
                return;
            }
            if ( false !== strpos( $requested_url, '%' ) ) {
                if ( ! function_exists( 'lowercase_octets' ) ) {
                    function lowercase_octets( $matches ) {
                        return strtolower( $matches[0] );
                    }
                }
                $requested_url = preg_replace_callback( '/[a-fA-F0-9](...)/', 'lowercase_octets', $requested_url );
            }
            if ( $redirect_obj instanceof TP_Post ) {
                $post_status_obj = $this->_get_post_status_object( $this->_get_post_status( $redirect_obj ) );
                if (
                    ! (
                        $post_status_obj->private &&
                        $this->_current_user_can( 'read_post', $redirect_obj->ID )
                    ) &&
                    // For other posts, only redirect if publicly viewable.
                    ! $this->_is_post_publicly_viewable( $redirect_obj )
                ) {
                    $redirect_obj = false;
                    $redirect_url = false;
                }
            }
            $redirect_url = $this->_apply_filters( 'redirect_canonical', $redirect_url,$redirect_obj, $requested_url );

            // Yes, again -- in case the filter aborted the request.
            if ( ! $redirect_url || $this->_strip_fragment_from_url( $redirect_url ) === $this->_strip_fragment_from_url( $requested_url ) ) {
                return;
            }
            if ( $do_redirect ) {
                // Protect against chained redirects.
                if ( ! $this->_redirect_canonical( $redirect_url, false ) ) {
                    $this->_tp_redirect( $redirect_url, 301 );
                    exit;
                }
                return;
            }
            return $redirect_url;
        }//42
        /**
         * @description Removes arguments from a query string if they are not present in a URL
         * @param $query_string
         * @param array $args_to_check
         * @param $url
         * @return mixed
         */
        private function __remove_qs_args_if_not_in_url( $query_string, array $args_to_check, $url ){
            $parsed_url = parse_url( $url );
            if ( ! empty( $parsed_url['query'] ) ) {
                parse_str( $parsed_url['query'], $parsed_query );
                foreach ( $args_to_check as $qv ) {
                    if ( ! isset( $parsed_query[ $qv ] ) ) {
                        $query_string = $this->_remove_query_arg( $qv, $query_string );
                    }
                }
            } else {
                $query_string = $this->_remove_query_arg( $args_to_check, $query_string );
            }
            return $query_string;
        }//823
        /**
         * @description Strips the #fragment from a URL, if one is present.
         * @param $url
         * @return string
         */
        protected function _strip_fragment_from_url( $url ):string{
            $parsed_url = parse_url( $url );
            if ( ! empty( $parsed_url['host'] ) ) {
                // This mirrors code in redirect_canonical(). It does not handle every case.
                $url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
                if ( ! empty( $parsed_url['port'] ) ) {
                    $url .= ':' . $parsed_url['port'];
                }
                if ( ! empty( $parsed_url['path'] ) ) {
                    $url .= $parsed_url['path'];
                }
                if ( ! empty( $parsed_url['query'] ) ) {
                    $url .= '?' . $parsed_url['query'];
                }
            }
            return $url;
        }//849
        /**
         * @description Attempts to guess the correct URL for a 404 request based on query vars.
         * @return bool|string
         */
        protected function _redirect_guess_404_permalink(){
            $this->tpdb = $this->_init_db();
            if ( false === $this->_apply_filters( 'do_redirect_guess_404_permalink', true ) ) {
                return false;
            }
            $pre = $this->_apply_filters( 'pre_redirect_guess_404_permalink', null );
            if ( null !== $pre ) {
                return $pre;
            }
            if ( $this->_get_query_var( 'name' ) ) {
                $strict_guess = $this->_apply_filters( 'strict_redirect_guess_404_permalink', false );
                if ( $strict_guess ) {
                    $where = $this->tpdb->prepare( 'post_name = %s', $this->_get_query_var( 'name' ) );
                } else {
                    $where = $this->tpdb->prepare( 'post_name LIKE %s', $this->tpdb->esc_like( $this->_get_query_var( 'name' ) ) . '%' );
                }
                // If any of post_type, year, monthnum, or day are set, use them to refine the query.
                if ( $this->_get_query_var( 'post_type' ) ) {
                    if ( is_array( $this->_get_query_var( 'post_type' ) ) ) {
                        $where .= " AND post_type IN ('" . implode( "', '", $this->_esc_sql( $this->_get_query_var( 'post_type' ) ) ) . "')";
                    } else {
                        $where .= $this->tpdb->prepare( ' AND post_type = %s', $this->_get_query_var( 'post_type' ) );
                    }
                } else {
                    $where .= " AND post_type IN ('" . implode( "', '", $this->_get_post_types( array( 'public' => true ) ) ) . "')";
                }
                if ( $this->_get_query_var( 'year' ) ) {
                    $where .= $this->tpdb->prepare( ' AND YEAR(post_date) = %d', $this->_get_query_var( 'year' ) );
                }
                if ( $this->_get_query_var( 'monthnum' ) ) {
                    $where .= $this->tpdb->prepare( ' AND MONTH(post_date) = %d', $this->_get_query_var( 'monthnum' ) );
                }
                if ( $this->_get_query_var( 'day' ) ) {
                    $where .= $this->tpdb->prepare( ' AND DAYOFMONTH(post_date) = %d', $this->_get_query_var( 'day' ) );
                }
                $post_id = $this->tpdb->get_var( TP_SELECT . " ID FROM $this->tpdb->posts WHERE $where AND post_status = 'publish'" );
                if ( ! $post_id ) {
                    return false;
                }
                if ($this->_get_query_var( 'feed' )) {
                    return $this->_get_post_comments_feed_link( $post_id, $this->_get_query_var( 'feed' ) );
                }
                if ($this->_get_query_var( 'page' ) > 1) {
                    return $this->_trailingslashit( $this->_get_permalink( $post_id ) ) . $this->_user_trailingslashit( $this->_get_query_var( 'page' ), 'single_paged' );
                }
                return $this->_get_permalink( $post_id );
            }
            return false;
        }//880
        /**
         * @description Redirects a variety of shorthand URLs to the admin.
         */
        protected function _tp_redirect_admin_locations():void{
            $this->tp_rewrite = $this->_init_rewrite();
            if ( ! ( $this->_is_404() && $this->tp_rewrite->using_permalinks() ) ) {
                return;
            }
            $admins = array(
                $this->_home_url( 'tp-admin', 'relative' ),
                $this->_home_url( 'dashboard', 'relative' ),
                $this->_home_url( 'admin', 'relative' ),
                $this->_site_url( 'dashboard', 'relative' ),
                $this->_site_url( 'admin', 'relative' ),
            );

            if ( in_array( $this->_untrailingslashit( $_SERVER['REQUEST_URI'] ), $admins, true ) ) {
                $this->_tp_redirect( $this->_admin_url() );
                exit;
            }

            $logins = array(
                $this->_home_url( 'tp-login.php', 'relative' ),
                $this->_home_url( 'login', 'relative' ),
                $this->_site_url( 'login', 'relative' ),
            );

            if ( in_array( $this->_untrailingslashit( $_SERVER['REQUEST_URI'] ), $logins, true ) ) {
                $this->_tp_redirect( $this->_tp_login_url() );
                exit;
            }
        }//983
    }
}else{die;}