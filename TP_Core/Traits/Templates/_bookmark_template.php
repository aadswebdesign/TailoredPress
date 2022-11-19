<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 18:14
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _bookmark_template {
        /**
         * @description The formatted output of a list of bookmarks.
         * @param $bookmarks
         * @param array ...$args
         * @return string
         */
        protected function _walk_bookmarks( $bookmarks, ...$args):string{
            $defaults = [
                'show_updated' => 0,'show_description' => 0,'show_images' => 1,'show_name' => 0,'before' => '<li>',
                'after' => '</li>','between' => "\n",'show_rating' => 0,'link_before' => '','link_after' => '',
            ];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $output = '';
            foreach ( (array) $bookmarks as $bookmark ) {
                if ( ! isset( $bookmark->recently_updated ) )
                    $bookmark->recently_updated = false;
                $output .= $parsed_args['before'];
                if ( $parsed_args['show_updated'] && $bookmark->recently_updated )
                    $output .= '<em>';
                $the_link = '#';
                if ( ! empty( $bookmark->link_url ) )
                    $the_link = $this->_esc_url( $bookmark->link_url );
                $desc  = $this->_esc_attr( $this->_sanitize_bookmark_field( 'link_description', $bookmark->link_description, $bookmark->link_id, 'display' ) );
                $name  = $this->_esc_attr( $this->_sanitize_bookmark_field( 'link_name', $bookmark->link_name, $bookmark->link_id, 'display' ) );
                $title = $desc;
                if ($parsed_args['show_updated'] && strpos($bookmark->link_updated_f, '00') !== 0) {
                    $title .= ' (';
                    $title .= sprintf($this->__( 'Last updated: %s' ), gmdate($this->_get_option( 'links_updated_date_format' ),$bookmark->link_updated_f + ( $this->_get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )));
                    $title .= ')';
                }
                $alt = " alt='$name ({$parsed_args['show_description']}? $title : )'";
                if ( '' !== $title ) $title =" title='$title'";
                $rel = $bookmark->link_rel;
                $target = $bookmark->link_target;
                if ( '' !== $target ) {
                    if ( is_string( $rel ) && '' !== $rel ) {
                        if ( ! $this->_tp_str_contains( $rel, 'noopener' ) )
                            $rel = trim( $rel ) . ' noopener';
                    } else  $rel = 'noopener';
                    $target = " target='$target'";
                }
                if ( '' !== $rel ) $rel=" rel='{$this->_esc_attr( $rel )}'";
                $output .= "<a href='$the_link' $rel $title $target>";
                $output .= $parsed_args['link_before'];
                if ( null !== $bookmark->link_image && $parsed_args['show_images'] ) {
                    if ( strpos( $bookmark->link_image, 'http' ) === 0 )
                        $output .= "<img src='$bookmark->link_image' $alt $title />";
                    // If it's a relative path.
                    else $output .= "<img src='{$this->_get_option( 'siteurl')}$bookmark->link_image\' $alt $title />";
                    if ( $parsed_args['show_name'] ) $output .= " $name";
                }else $output .= $name;
                $output .= $parsed_args['link_after'];
                $output .= '</a>';
                if ( $parsed_args['show_updated'] && $bookmark->recently_updated )
                    $output .= '</em>';
                if ( $parsed_args['show_description'] && '' !== $desc )
                    $output .= $parsed_args['between'] . $desc;
                if ( $parsed_args['show_rating'] ) {
                    $output .= $parsed_args['between'] . $this->_sanitize_bookmark_field(
                            'link_rating',
                            $bookmark->link_rating,
                            $bookmark->link_id,
                            'display'
                        );
                }
                $output .= $parsed_args['after'] . "\n";
            }
            return $output;
        }//51 from bookmark-template
        /**
         * @description Retrieve all of the bookmarks.
         * @param string $args
         * @return string
         */
        protected function _tp_get_list_bookmarks( $args = '' ):string{
            $defaults = [
                'orderby' => 'name','order' => 'ASC','limit' => -1,'category' => '','exclude_category' => '',
                'category_name' => '','hide_invisible' => 1,'show_updated' => 0,'categorize' => 1,
                'title_li' => $this->__( 'Bookmarks' ),'title_before' => '<h2>','title_after' => '</h2>',
                'category_orderby' => 'name','category_order' => 'ASC','class' => 'link-cat',
                'category_before' => "<li id='%id' class='%class'>",'category_after' => '</li>',
            ];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $output = '';
            $cats = '';
            if ( ! is_array( $parsed_args['class'] ) )
                $parsed_args['class'] = explode( ' ', $parsed_args['class'] );
            $parsed_args['class'] = array_map( 'sanitize_html_class', $parsed_args['class'] );
            $parsed_args['class'] = trim( implode( ' ', $parsed_args['class'] ) );
            if ( $parsed_args['categorize'] ) {
                $cats = $this->_get_terms([
                    'taxonomy'=> 'link_category',
                    'name__like'=> $parsed_args['category_name'],
                    'include' => $parsed_args['category'],
                    'exclude' => $parsed_args['exclude_category'],
                    'orderby' => $parsed_args['category_orderby'],
                    'order' => $parsed_args['category_order'],
                    'hierarchical' => 0,
                ], '');//todo
                if ( empty($cats)) $parsed_args['categorize'] = false;
            }
            if ( $parsed_args['categorize'] ) {
                foreach ( (array) $cats as $cat ) {
                    $params = array_merge( $parsed_args, array( 'category' => $cat->term_id ) );
                    $bookmarks = $this->_get_bookmarks( $params );
                    if ( empty( $bookmarks ) ) continue;
                    $output .= str_replace(
                        ['%id', '%class'],
                        ["link_cat_$cat->term_id", $parsed_args['class']],
                        $parsed_args['category_before']
                    );
                    $cat_name = $this->_apply_filters( 'link_category', $cat->name );
                    $output .= $parsed_args['title_before'];
                    $output .= $cat_name;
                    $output .= $parsed_args['title_after'];
                    $output .= "\n\t<ul class='choco blog-roll'>\n";//'xoxo' renamed to 'choco'
                    $output .= $this->_walk_bookmarks( $bookmarks, $parsed_args );
                    $output .= "\n\t</ul>\n";
                    $output .= $parsed_args['category_after'] . "\n";
                }
            }else{
                $bookmarks = $this->_get_bookmarks( $parsed_args );
                if ( ! empty( $bookmarks ) ) {
                    if ( ! empty( $parsed_args['title_li'])){
                        $output .= str_replace(
                            [ '%id', '%class'],
                            ['link_cat_' . $parsed_args['category'], $parsed_args['class']],
                            $parsed_args['category_before']
                        );
                        $output .= $parsed_args['title_before'];
                        $output .= $parsed_args['title_li'];
                        $output .= $parsed_args['title_after'];
                        $output .= "\n\t<ul class='choco blog-roll'>\n";
                        $output .= $this->_walk_bookmarks( $bookmarks, $parsed_args );
                        $output .= "\n\t</ul>\n";
                        $output .= $parsed_args['category_after'] . "\n";
                    }else $output .= $this->_walk_bookmarks( $bookmarks, $parsed_args );
                }
            }
            return $this->_apply_filters( 'tp_list_bookmarks', $output );
        }//219 from bookmark-template
        protected function _tp_list_bookmarks( $args = '' ):void{
            echo $this->_tp_get_list_bookmarks( $args);
        }
    }
}else die;