<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 21:51
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Libs\Walkers\TP_Walker;
use TP_Core\Libs\Walkers\TP_Walker_Category;
use TP_Core\Libs\Walkers\TP_Walker_CategoryDropdown;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _category_template_02 {
        use _init_error;
        /**
         * @description Displays a tag cloud.
         * @param array ...$args
         * @return mixed
         */
        protected function _tp_get_tag_cloud( ...$args) {
            $defaults = [
                'smallest' => 8,'largest' => 22,'unit' => 'pt','number' => 45,'format' => 'flat',
                'separator' => "\n",'orderby' => 'name','order' => 'ASC','exclude' => '','include' => '',
                'link' => 'view','taxonomy' => 'post_tag','post_type' => '','show_count' => 0,
            ];
            $args = $this->_tp_parse_args( $args, $defaults );
            $tags = $this->_get_terms(array_merge($args,['orderby' => 'count','order'=> 'DESC',]));
            if ( empty( $tags ) || $this->_init_error( $tags ) ) return null;
            foreach ( $tags as $key => $tag ) {
                if ( 'edit' === $args['link'] )
                    $link = $this->_get_edit_term_link( $tag, $tag->taxonomy, $args['post_type'] );
                else $link = $this->_get_term_link( $tag, $tag->taxonomy );
                if ( $this->_init_error( $link ) ) return null;
                $tags[ $key ]->link = $link;
                $tags[ $key ]->id = $tag->term_id;
            }
            $return = $this->_tp_generate_tag_cloud( $tags, $args );
            $return = $this->_apply_filters( 'tp_tag_cloud', $return, $args );
            if ( 'array' === $args['format'] ) return $return;
        }//707 from category-template
        protected function _tp_tag_cloud( ...$args):void{
            echo $this->_tp_get_tag_cloud($args);
        }
        /**
         * @description Default topic count scaling for tag links.
         * @param $count
         * @return float
         */
        protected function _default_topic_count_scale( $count ):float{
            return round( log10( $count + 1 ) * 100 );
        }//786 from category-template
        /**
         * @description Generates a tag cloud (heat_map) from provided data.
         * @param $tags
         * @param array ...$args
         * @return array|string
         */
        protected function _tp_generate_tag_cloud( $tags, ...$args){
            $defaults = [
                'smallest' => 8,'largest' => 22,'unit' => 'pt','number' => 0,'format' => 'flat','separator' => "\n",
                'orderby' => 'name','order' => 'ASC','topic_count_text' => null,'topic_count_text_callback' => null,
                'topic_count_scale_callback' => 'default_topic_count_scale','filter' => 1,'show_count' => 0,
            ];
            $args = $this->_tp_parse_args( $args, $defaults );
            $return = ( 'array' === $args['format'] ) ? array() : '';
            if ( empty( $tags ) )  return $return;
            if ( isset( $args['topic_count_text'] ) ) {
                $translate_nooped_plural = $args['topic_count_text'];
            } elseif ( ! empty( $args['topic_count_text_callback'] ) ) {
                if ( 'default_topic_count_text' === $args['topic_count_text_callback'] )
                    $translate_nooped_plural = $this->_n_noop( '%s item', '%s items' );
                else $translate_nooped_plural = false;
            } elseif ( isset( $args['single_text'], $args['multiple_text'] ) )
                $translate_nooped_plural = $this->_n_noop( $args['single_text'], $args['multiple_text'] );
            else $translate_nooped_plural = $this->_n_noop( '%s item', '%s items' );
            $tags_sorted = $this->_apply_filters( 'tag_cloud_sort', $tags, $args );
            if ( empty( $tags_sorted ) ) return $return;
            if ( $tags_sorted !== $tags ) {
                $tags = $tags_sorted;
                unset( $tags_sorted );
            }else if ( 'RAND' === $args['order'] ) {
                shuffle( $tags );
            } else {
                if ( 'name' === $args['orderby'] )
                    uasort( $tags, '_tp_object_name_sort_cb' );
                else uasort( $tags, '_tp_object_count_sort_cb' );
                if ( 'DESC' === $args['order'] )
                    $tags = array_reverse( $tags, true );
            }
            if ( $args['number'] > 0 )
                $tags = array_slice( $tags, 0, $args['number'] );
            $counts = [];
            $real_counts = []; // For the alt tag.
            foreach ( (array) $tags as $key => $tag ) {
                $real_counts[ $key ] = $tag->count;
                $counts[ $key ] = call_user_func( $args['topic_count_scale_callback'], $tag->count );
            }
            $min_count = min( $counts );
            $spread    = max( $counts ) - $min_count;
            if ( $spread <= 0 ) $spread = 1;
            $font_spread = $args['largest'] - $args['smallest'];
            if ( $font_spread < 0 ) $font_spread = 1;
            $font_step = $font_spread / $spread;
            $aria_label = false;
            if ( $args['show_count'] || 0 !== $font_spread )
                $aria_label = true;
            $tags_data = array();
            foreach ( $tags as $key => $tag ) {
                $tag_id = $tag->id ?? $key;
                $count      = $counts[ $key ];
                $real_count = $real_counts[ $key ];
                if ( $translate_nooped_plural )
                    $formatted_count = sprintf( $this->_translate_nooped_plural( $translate_nooped_plural, $real_count ), $this->_number_format_i18n( $real_count ) );
                else $formatted_count = call_user_func( $args['topic_count_text_callback'], $real_count, $tag, $args );
                $tags_data[] = [
                    'id' => $tag_id,'url' => $tag->link,
                    'role' => ( '#' !== $tag->link ) ? '' : ' role="button"','name' => $tag->name,
                    'formatted_count' => $formatted_count,'slug' => $tag->slug,'real_count' => $real_count,
                    'class' => 'tag-cloud-link tag-link-' . $tag_id,
                    'font_size' => $args['smallest'] + ( $count - $min_count ) * $font_step,
                    'aria_label' => $aria_label ? sprintf( ' aria-label="%1$s (%2$s)"', $this->_esc_attr( $tag->name ), $this->_esc_attr( $formatted_count ) ) : '',
                    'show_count' => $args['show_count'] ? "<span class='tag-link-count'>($real_count)</span>" : '',
                ];
            }
            $tags_data = $this->_apply_filters( 'tp_generate_tag_cloud_data', $tags_data );
            $a = [];
            foreach ( $tags_data as $inner_key => $tag_data ) {
                $class = $tag_data['class'] . ' tag-link-position-' . ( $inner_key + 1 );
                $a[]   = sprintf(
                    '<!--suppress CssUnitlessNumber -->
                    <a href="%1$s" %2$s class="%3$s" style="font-size: %4$s;" %5$s>%6$s%7$s</a>',
                    $this->_esc_url( $tag_data['url'] ),
                    $tag_data['role'],
                    $this->_esc_attr( $class ),
                    $this->_esc_attr( str_replace( ',', '.', $tag_data['font_size'] ) . $args['unit'] ),
                    $tag_data['aria_label'],
                    $this->_esc_html( $tag_data['name'] ),
                    $tag_data['show_count']
                );
            }
            switch ( $args['format'] ) {
                case 'array':
                    $return =& $a;
                    break;
                case 'list':
                    $return  = "<ul class='tp-tag-cloud' role='list'>\n\t<li>";
                    $return .= implode( "</li>\n\t<li>", $a );
                    $return .= "</li>\n</ul>\n";
                    break;
                default:
                    $return = implode( $args['separator'], $a );
                    break;
            }
            if ( $args['filter'] )
                return $this->_apply_filters( 'tp_generate_tag_cloud', $return, $tags, $args );
            else return $return;
        }//836 from category-template
        /**
         * @description Serves as a callback for comparing objects based on name.
         * @param $a
         * @param $b
         * @return int
         */
        protected function _tp_object_name_sort_cb( $a, $b ):int{
            return strnatcasecmp( $a->name, $b->name );
        }//1064 from category-template
        /**
         * @description Serves as a callback for comparing objects based on count.
         * @param $a
         * @param $b
         * @return bool
         */
        protected function _tp_object_count_sort_cb( $a, $b ):bool{
            return ( $a->count > $b->count );
        }//1080 from category-template
        /**
         * @description Retrieves HTML list content for category list.
         * @param array ...$args
         * @return string
         */
        protected function _walk_category_tree( ...$args ):string{
            if ( empty( $args[2]['walker'] ) || ! ( $args[2]['walker'] instanceof TP_Walker ) )
                $walker = new TP_Walker_Category;
            else $walker = $args[2]['walker'];
            return $walker->walk( ...$args );
        }//1101 from category-template
        /**
         * @description Retrieves HTML dropdown (select) content for category list.
         * @param array ...$args
         * @return string
         */
        protected function _walk_category_dropdown_tree( ...$args ):string{
            if ( empty( $args[2]['walker'] ) || ! ( $args[2]['walker'] instanceof TP_Walker ) )
                $walker = new TP_Walker_CategoryDropdown;
            else $walker = $args[2]['walker'];
            return $walker->walk( ...$args );
        }//1127 from category-template
        /**
         * @description Retrieves the link to the tag.
         * @param $tag
         * @return mixed
         */
        protected function _get_tag_link( $tag ){
            return $this->_get_category_link( $tag );
        }//1150 from category-template
        /**
         * @description Retrieves the tags for a post.
         * @param int $post_id
         * @return mixed
         */
        protected function _get_the_tags( $post_id = 0 ){
            $terms = $this->_get_the_terms( $post_id, 'post_tag' );
            return $this->_apply_filters( 'get_the_tags', $terms );
        }//1167 from category-template
        /**
         * @description Retrieves the tags for a post formatted as a string.
         * @param string $before
         * @param string $sep
         * @param string $after
         * @param int $post_id
         * @return mixed
         */
        protected function _get_the_tag_list( $before = '', $sep = '', $after = '', $post_id = 0 ){
            $tag_list = $this->_get_the_term_list( $post_id, 'post_tag', $before, $sep, $after );
            return $this->_apply_filters( 'the_tags', $tag_list, $before, $sep, $after, $post_id );
        }//1191 from category-template
    }
}else die;