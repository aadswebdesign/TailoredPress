<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_locale;
if(ABSPATH){
    trait _general_template_04 {
        use _init_locale;
        use _init_db;
        /**
         * @description Display or retrieve page title for tag post archive.
         * @param string $prefix
         * @return string
         */
        protected function _single_tag_title( $prefix = ''):string{
            return $this->_get_single_term_title( $prefix);
        }//1559 from general-template
        /**
         * @description Display or retrieve page title for taxonomy term archive.
         * @param string $prefix
         * @return bool|string
         */
        protected function _get_single_term_title($prefix = ''){
            $term = $this->_get_queried_object();
            if ( ! $term ) return false;
            if ( $this->_is_category() )
                $term_name = $this->_apply_filters( 'single_cat_title', $term->name );
            elseif ( $this->_is_tag() )
                $term_name = $this->_apply_filters( 'single_tag_title', $term->name );
            elseif ( $this->_is_tax() )
                $term_name = $this->_apply_filters( 'single_term_title', $term->name );
            else return false;
            if ( empty( $term_name ) ) return false;
            return $prefix . $term_name;
        }//1576 from general-template
        protected function _single_term_title($prefix = ''):void{
            echo $this->_get_single_term_title($prefix);
        }
        /**
         * @description Display or retrieve page title for post archive based on date.
         * @param string $prefix
         * @return bool|string
         */
        protected function _get_single_month_title( $prefix = ''){
            $tp_locale = $this->_init_locale();
            $my_year = null;
            $m        = $this->_get_query_var( 'm' );
            $year     = $this->_get_query_var( 'year' );
            $monthnum = $this->_get_query_var( 'monthnum' );
            if ( ! empty( $monthnum ) && ! empty( $year ) ) {
                $my_year  = $year;
                $my_month = $tp_locale->get_month( $monthnum );
            } elseif ( ! empty( $m ) ) {
                $my_year  = substr( $m, 0, 4 );
                $my_month = $tp_locale->get_month( substr( $m, 4, 2 ) );
            }
            if ( empty( $my_month ) ) return false;
            return $prefix . $my_month . $prefix . $my_year;
        }//1641 from general-template
        protected function _single_month_title( $prefix = ''):void{
            echo $this->_get_single_month_title( $prefix);
        }
        /**
         *  @description Display the archive title based on the queried object.
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_the_assembled_archive_title( $before = '', $after = '' ):string{
            $title = $this->_get_the_archive_title();
            if (empty( $title ) ) return false;
            return $before . $title . $after;
        }//1678 from general-template
        protected function _print_the_assembled_archive_title( $before = '', $after = '' ):void{
            echo $this->_get_the_assembled_archive_title( $before, $after);
        }
        /**
         * @description Retrieve the archive title based on the queried object.
         * @return mixed
         */
        protected function _get_the_archive_title(){
            $title  = $this->__( 'Archives' );
            $prefix = '';
            if ( $this->_is_category() ) {
                $title  = $this->_single_cat_title( '');
                $prefix = $this->_x( 'Category:', 'category archive title prefix' );
            } elseif ( $this->_is_tag() ) {
                $title  = $this->_single_tag_title( '');
                $prefix = $this->_x( 'Tag:', 'tag archive title prefix' );
            } elseif ( $this->_is_author() ) {
                $title  = $this->_get_the_author();
                $prefix = $this->_x( 'Author:', 'author archive title prefix' );
            } elseif ( $this->_is_year() ) {
                $title  = $this->_get_the_date( $this->_x( 'Y', 'yearly archives date format' ) );
                $prefix = $this->_x( 'Year:', 'date archive title prefix' );
            } elseif ( $this->_is_month() ) {
                $title  = $this->_get_the_date( $this->_x( 'F Y', 'monthly archives date format' ) );
                $prefix = $this->_x( 'Month:', 'date archive title prefix' );
            } elseif ( $this->_is_day() ) {
                $title  = $this->_get_the_date( $this->_x( 'F j, Y', 'daily archives date format' ) );
                $prefix = $this->_x( 'Day:', 'date archive title prefix' );
            } elseif ( $this->_is_tax( 'post_format' ) ) {
                if ( $this->_is_tax( 'post_format', 'post-format-aside' ) )
                    $title = $this->_x( 'Asides', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-gallery' ) )
                    $title = $this->_x( 'Galleries', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-image' ) )
                    $title = $this->_x( 'Images', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-video' ) )
                    $title = $this->_x( 'Videos', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-quote' ) )
                    $title = $this->_x( 'Quotes', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-link' ) )
                    $title = $this->_x( 'Links', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-status' ) )
                    $title = $this->_x( 'Statuses', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-audio' ) )
                    $title = $this->_x( 'Audio', 'post format archive title' );
                elseif ( $this->_is_tax( 'post_format', 'post-format-chat' ) )
                    $title = $this->_x( 'Chats', 'post format archive title' );
            } elseif ( $this->_is_post_type_archive() ) {
                $title  = $this->_get_post_type_archive_title('');
                $prefix = $this->_x( 'Archives:', 'post type archive title prefix' );
            } elseif ( $this->_is_tax() ) {
                $queried_object = $this->_get_queried_object();
                if ( $queried_object ) {
                    $tax    = $this->_get_taxonomy( $queried_object->taxonomy );
                    $title  = $this->_get_single_term_title( '');
                    $prefix = sprintf(
                    /* translators: %s: Taxonomy singular name. */
                        $this->_x( '%s:', 'taxonomy term archive title prefix' ),
                        $tax->labels->singular_name
                    );
                }
            }
            $original_title = $title;
            $prefix = $this->_apply_filters( 'get_the_archive_title_prefix', $prefix );
            if ( $prefix ) {
                $title = sprintf(
                    $this->_x( '%1$s %2$s', 'archive title' ),
                    $prefix,
                    "<span>{$title}</span>"
                );
            }
            return $this->_apply_filters( 'get_the_archive_title', $title, $original_title, $prefix );
        }//1694 from general-template
        /**
         * @description  Display category, tag, term, or author description.
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_the_assembled_archive_description( $before = '', $after = '' ):string{
            $description = $this->_get_the_archive_description();
            if ( !$description ) return false;
            return $before . $description . $after;
        }//1794 from general-template
        protected function _print_the_assembled_archive_description( $before = '', $after = '' ):void{
            echo $this->_get_the_assembled_archive_description( $before, $after);
        }
        /**
         * @description Retrieves the description for an author, post type, or term archive.
         * @return mixed
         */
        protected function _get_the_archive_description(){
            if ( $this->_is_author() )
                $description = $this->_get_the_author_meta( 'description' );
            elseif ( $this->_is_post_type_archive() )
                $description = $this->_get_the_post_type_description();
            else  $description = $this->_term_description();
            return $this->_apply_filters( 'get_the_archive_description', $description );
        }//1812 from general-template
        /**
         * @description Retrieves the description for a post type archive.
         * @return mixed
         */
        protected function _get_the_post_type_description(){
            $post_type = $this->_get_query_var( 'post_type' );
            if ( is_array( $post_type ) )
                $post_type = reset( $post_type );
            $post_type_obj = $this->_get_post_type_object( $post_type );
            if ( isset( $post_type_obj->description ) ) $description = $post_type_obj->description;
            else  $description = '';
            return $this->_apply_filters( 'get_the_post_type_description', $description, $post_type_obj );
        }//1838 from general-template
        /**
         * @description Retrieve archive link content based on predefined or custom code.
         * @param $url
         * @param $text
         * @param string $format
         * @param string $before
         * @param string $after
         * @param bool $selected
         * @return mixed
         */
        protected function _get_archives_link( $url, $text, $format = 'html', $before = '', $after = '', $selected = false ){
            $text         = $this->_tp_texturize( $text );
            $url          = $this->_esc_url( $url );
            $aria_current = $selected ? ' aria-current="page"' : '';
            if ( 'link' === $format )
                $link_html = "\t<link rel='archives' title='" . $this->_esc_attr( $text ) . "' href='$url' />\n";
            elseif ( 'option' === $format ) {
                $selected_attr = $selected ? " selected='selected'" : '';
                $link_html     = "\t<option value='$url' $selected_attr >$before $text $after</option>\n";
            } elseif ( 'html' === $format )
                $link_html = "\t<li>$before<a href='$url' $aria_current >$text</a>$after</li>\n";
            else $link_html = "\t$before<a href='$url' $aria_current >$text</a>$after\n";
            return $this->_apply_filters( 'get_archives_link', $link_html, $url, $text, $format, $before, $after, $selected );
        }//1900 from general-template
        /**
         * @description Display archive links based on type and format.
         * @param string $args
         * @return bool|string
         */
        protected function _tp_get_archives( $args = '' ){
            $tpdb = $this->_init_db();
            $tp_locale = $this->_init_locale();
            $defaults = ['type' => 'monthly','limit' => '','format' => 'html','before' => '',
                'after' => '','show_post_count' => false,'order' => 'DESC','post_type' => 'post',
                'year' => $this->_get_query_var( 'year' ),'monthnum' => $this->_get_query_var( 'monthnum' ),
                'day' => $this->_get_query_var( 'day' ),'w' => $this->_get_query_var( 'w' ),];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $post_type_object = $this->_get_post_type_object( $parsed_args['post_type'] );
            if ( ! $this->_is_post_type_viewable( $post_type_object ) )
                return false;
            $parsed_args['post_type'] = $post_type_object->name;
            if ( '' === $parsed_args['type'] ) $parsed_args['type'] = 'monthly';
            if ( ! empty( $parsed_args['limit'] ) ) {
                $parsed_args['limit'] = $this->_abs_int( $parsed_args['limit'] );
                $parsed_args['limit'] = ' LIMIT ' . $parsed_args['limit'];
            }
            $order = strtoupper( $parsed_args['order'] );
            if ( 'ASC' !== $order )  $order = 'DESC';
            $archive_week_separator = '&#8211;';
            $sql_where = $tpdb->prepare( "WHERE post_type = %s AND post_status = 'publish'", $parsed_args['post_type'] );
            $where = $this->_apply_filters( 'get_archives_where', $sql_where, $parsed_args );
            $join = $this->_apply_filters( 'get_archives_join', '', $parsed_args );
            $output = '';
            $last_changed = $this->_tp_cache_get_last_changed( 'posts' );
            $limit = $parsed_args['limit'];
            if ( 'monthly' === $parsed_args['type'] ) {
                $query   = TP_SELECT . " YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $tpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
                $key     = md5( $query );
                $key     = "tp_get_archives:$key:$last_changed";
                $results = $this->_tp_cache_get( $key, 'posts' );
                if ( ! $results ) {
                    $results = $tpdb->get_results( $query );
                    $this->_tp_cache_set( $key, $results, 'posts' );
                }
                if ( $results ) {
                    $after = $parsed_args['after'];
                    foreach ( (array) $results as $result ) {
                        $url = $this->_get_month_link( $result->year, $result->month );
                        if ( 'post' !== $parsed_args['post_type'] )
                            $url = $this->_add_query_arg( 'post_type', $parsed_args['post_type'], $url );
                        $text = sprintf( $this->__( '%1$s %2$d' ), $tp_locale->get_month( $result->month ), $result->year );
                        if ( $parsed_args['show_post_count'] )
                            $parsed_args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                        $selected = $this->_is_archive() && (string) $parsed_args['year'] === $result->year && (string) $parsed_args['monthnum'] === $result->month;
                        $output  .= $this->_get_archives_link( $url, $text, $parsed_args['format'], $parsed_args['before'], $parsed_args['after'], $selected );
                    }
                }
            } elseif ( 'yearly' === $parsed_args['type'] ) {
                $query   = TP_SELECT . " YEAR(post_date) AS `year`, count(ID) as posts FROM $tpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date $order $limit";
                $key     = md5( $query );
                $key     = "tp_get_archives:$key:$last_changed";
                $results = $this->_tp_cache_get( $key, 'posts' );
                if ( ! $results ) {
                    $results = $tpdb->get_results( $query );
                    $this->_tp_cache_set( $key, $results, 'posts' );
                }
                if ( $results ) {
                    $after = $parsed_args['after'];
                    foreach ( (array) $results as $result ) {
                        $url = $this->_get_year_link( $result->year );
                        if ( 'post' !== $parsed_args['post_type'] )
                            $url = $this->_add_query_arg( 'post_type', $parsed_args['post_type'], $url );
                        $text = sprintf( '%d', $result->year );
                        if ( $parsed_args['show_post_count'] ) {
                            $parsed_args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                        }
                        $selected = $this->_is_archive() && (string) $parsed_args['year'] === $result->year;
                        $output  .= $this->_get_archives_link( $url, $text, $parsed_args['format'], $parsed_args['before'], $parsed_args['after'], $selected );
                    }
                }
            } elseif ( 'daily' === $parsed_args['type'] ) {
                $query   = TP_SELECT . " YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $tpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
                $key     = md5( $query );
                $key     = "wp_get_archives:$key:$last_changed";
                $results = $this->_tp_cache_get( $key, 'posts' );
                if ( ! $results ) {
                    $results = $tpdb->get_results( $query );
                    $this->_tp_cache_set( $key, $results, 'posts' );
                }
                if ( $results ) {
                    $after = $parsed_args['after'];
                    foreach ( (array) $results as $result ) {
                        $url = $this->_get_day_link( $result->year, $result->month, $result->dayofmonth );
                        if ( 'post' !== $parsed_args['post_type'] ) {
                            $url = $this->_add_query_arg( 'post_type', $parsed_args['post_type'], $url );
                        }
                        $date = sprintf( '%1$d-%2$02d-%3$02d 00:00:00', $result->year, $result->month, $result->dayofmonth );
                        $text = $this->_mysql2date( $this->_get_option( 'date_format' ), $date );
                        if ( $parsed_args['show_post_count'] ) {
                            $parsed_args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                        }
                        $selected = $this->_is_archive() && (string) $parsed_args['year'] === $result->year && (string) $parsed_args['monthnum'] === $result->month && (string) $parsed_args['day'] === $result->dayofmonth;
                        $output  .= $this->_get_archives_link( $url, $text, $parsed_args['format'], $parsed_args['before'], $parsed_args['after'], $selected );
                    }
                }
            } elseif ( 'weekly' === $parsed_args['type'] ) {
                $week    = $this->_tp_mysql_week( '`post_date`' );
                $query   = TP_SELECT . " DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$tpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` $order $limit";
                $key     = md5( $query );
                $key     = "tp_get_archives:$key:$last_changed";
                $results = $this->_tp_cache_get( $key, 'posts' );
                if ( ! $results ) {
                    $results = $tpdb->get_results( $query );
                    $this->_tp_cache_set( $key, $results, 'posts' );
                }
                $arc_w_last = '';
                if ( $results ) {
                    $after = $parsed_args['after'];
                    foreach ( (array) $results as $result ) {
                        if ( $result->week !== $arc_w_last ) {
                            $arc_year       = $result->yr;
                            $arc_w_last     = $result->week;
                            $arc_week       = $this->_get_week_start_end( $result->yyyymmdd, $this->_get_option( 'start_of_week' ) );
                            $arc_week_start = $this->_date_i18n( $this->_get_option( 'date_format' ), $arc_week['start'] );
                            $arc_week_end   = $this->_date_i18n( $this->_get_option( 'date_format' ), $arc_week['end'] );
                            $url            = $this->_add_query_arg(
                                array(
                                    'm' => $arc_year,
                                    'w' => $result->week,
                                ),
                                $this->_home_url( '/' )
                            );
                            if ( 'post' !== $parsed_args['post_type'] ) {
                                $url = $this->_add_query_arg( 'post_type', $parsed_args['post_type'], $url );
                            }
                            $text = $arc_week_start . $archive_week_separator . $arc_week_end;
                            if ( $parsed_args['show_post_count'] ) {
                                $parsed_args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                            }
                            $selected = $this->_is_archive() && (string) $parsed_args['year'] === $result->yr && (string) $parsed_args['w'] === $result->week;
                            $output  .= $this->_get_archives_link( $url, $text, $parsed_args['format'], $parsed_args['before'], $parsed_args['after'], $selected );
                        }
                    }
                }
            } elseif ( ( 'post_by_post' === $parsed_args['type'] ) || ( 'alpha' === $parsed_args['type'] ) ) {
                $orderby = ( 'alpha' === $parsed_args['type'] ) ? 'post_title ASC ' : 'post_date DESC, ID DESC ';
                $query   = TP_SELECT . " * FROM $tpdb->posts $join $where ORDER BY $orderby $limit";
                $key     = md5( $query );
                $key     = "tp_get_archives:$key:$last_changed";
                $results = $this->_tp_cache_get( $key, 'posts' );
                if ( ! $results ) {
                    $results = $tpdb->get_results( $query );
                    $this->_tp_cache_set( $key, $results, 'posts' );
                }
                if ( $results ) {
                    foreach ( (array) $results as $result ) {
                        if ( '0000-00-00 00:00:00' !== $result->post_date ) {
                            $url = $this->_get_permalink( $result );
                            if ( $result->post_title )
                                $text = strip_tags( $this->_apply_filters( 'the_title', $result->post_title, $result->ID ) );
                            else $text = $result->ID;
                            $selected = $this->_get_the_ID() === $result->ID;
                            $output  .= $this->_get_archives_link( $url, $text, $parsed_args['format'], $parsed_args['before'], $parsed_args['after'], $selected );
                        }
                    }
                }
            }
            if(!$output) return false;
            return $output;
        }//1974 from general-template
        protected function _tp_print_archives( $args = '' ):void{
            echo $this->_tp_get_archives( $args);
        }
    }
}else die;