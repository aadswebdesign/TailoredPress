<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _taxonomy_07 {
        use _init_db, _init_error, _init_rewrite;
        /**
         * @description In order to avoid the _wp_batch_split_terms() job being accidentally removed,
         * @description . check that it's still scheduled while we haven't finished splitting terms.
         */
        protected function _tp_check_for_scheduled_split_terms():void{
            if ( ! $this->_get_option( 'finished_splitting_shared_terms' ) && ! $this->_tp_next_scheduled( 'tp_split_shared_term_batch' ) )
                $this->_tp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'tp_split_shared_term_batch' );
        }//4354
        /**
         * @description Check default categories when a term gets split to see if any of them need to be updated.
         * @param $term_id
         * @param $new_term_id
         * @param $taxonomy
         */
        protected function _tp_check_split_default_terms( $term_id, $new_term_id, $taxonomy ):void{
            if ( 'category' !== $taxonomy ) return;
            foreach ( array( 'default_category', 'default_link_category', 'default_email_category' ) as $option ) {
                if ( (int) $this->_get_option( $option, -1 ) === $term_id )
                    $this->_update_option( $option, $new_term_id );
            }
        }//4371
        /**
         * @description Check menu items when a term gets split to see if any of them need to be updated.
         * @param $term_id
         * @param $new_term_id
         * @param $taxonomy
         */
        protected function _tp_check_split_terms_in_menus( $term_id, $new_term_id, $taxonomy ):void{
            $this->tpdb = $this->_init_db();
            $post_ids = $this->tpdb->get_col(
                $this->tpdb->prepare(TP_SELECT . " m1.post_id FROM {$this->tpdb->post_meta} AS m1 INNER JOIN {$this->tpdb->post_meta} AS m2 ON ( m2.post_id = m1.post_id ) INNER JOIN {$this->tpdb->post_meta} AS m3 ON ( m3.post_id = m1.post_id )	WHERE ( m1.meta_key = '_menu_item_type' AND m1.meta_value = 'taxonomy' ) AND ( m2.meta_key = '_menu_item_object' AND m2.meta_value = %s ) AND ( m3.meta_key = '_menu_item_object_id' AND m3.meta_value = %d )", $taxonomy,$term_id));
            if ( $post_ids ) {
                foreach ( $post_ids as $post_id )
                    $this->_update_post_meta( $post_id, '_menu_item_object_id', $new_term_id, $term_id );
            }
        }//4396
        /**
         * @description If the term being split is a nav_menu, change associations.
         * @param $term_id
         * @param $new_term_id
         * @param $taxonomy
         */
        protected function _tp_check_split_nav_menu_terms( $term_id, $new_term_id, $taxonomy ):void{
            if ( 'nav_menu' !== $taxonomy ) return;
            $locations = $this->_get_nav_menu_locations();
            foreach ( $locations as $location => $menu_id ) {
                if ( $term_id === $menu_id ) $locations[ $location ] = $new_term_id;
            }
            $this->_set_theme_mod( 'nav_menu_locations', $locations );
        }//4430
        /**
         * @description Get data about terms that previously shared a single term_id, but have since been split.
         * @param $old_term_id
         * @return array
         */
        protected function _tp_get_split_term( $old_term_id ):array{ //not used , $taxonomy
            $split_terms = $this->_get_option( '_split_terms', array() );
            $terms = [];
            if ( isset( $split_terms[ $old_term_id ] ) ) $terms = $split_terms[ $old_term_id ];
            return $terms;
        }//4475
        /**
         * @description Determine whether a term is shared between multiple taxonomies.
         * @param $term_id
         * @return bool
         */
        protected function _tp_term_is_shared( $term_id ):bool{
            $this->tpdb = $this->_init_db();
            if ( $this->_get_option( 'finished_splitting_shared_terms' ) ) return false;
            $tt_count = $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " COUNT(*) FROM $this->tpdb->term_taxonomy WHERE term_id = %d", $term_id ) );
            return $tt_count > 1;
        }//4498
        /**
         * @description Generate a permalink for a taxonomy term archive.
         * @param $term
         * @param string $taxonomy
         * @return TP_Error
         */
        protected function _get_term_link( $term, $taxonomy = '' ):TP_Error{
            $tp_rewrite = $this->_init_rewrite();
            if ( ! is_object( $term ) ) {
                if ( is_int( $term ) )  $term = $this->_get_term( $term, $taxonomy );
                else $term = $this->_get_term_by( 'slug', $term, $taxonomy );
            }
            if ( ! is_object( $term ) )
                $term = new TP_Error( 'invalid_term', $this->__( 'Empty Term.' ) );
            if ( $this->_init_error( $term ) ) return $term;
            $taxonomy = $term->taxonomy;
            $term_link = $tp_rewrite->get_extra_permanent_structure( $taxonomy );
            $term_link = $this->_apply_filters( 'pre_term_link', $term_link, $term );
            $slug = $term->slug;
            $t    = $this->_get_taxonomy( $taxonomy );
            if ( empty( $term_link ) ) {
                if ( 'category' === $taxonomy ) $term_link = '?cat=' . $term->term_id;
                elseif ( $t->query_var ) $term_link = "?$t->query_var=$slug";
                else $term_link = "?taxonomy=$taxonomy&term=$slug";
                $term_link = $this->_home_url( $term_link );
            } else {
                if ( ! empty( $t->rewrite['hierarchical'] ) ) {
                    $hierarchical_slugs = array();
                    $ancestors          = $this->_get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
                    foreach ( (array) $ancestors as $ancestor ) {
                        $ancestor_term        = $this->_get_term( $ancestor, $taxonomy );
                        $hierarchical_slugs[] = $ancestor_term->slug;
                    }
                    $hierarchical_slugs   = array_reverse( $hierarchical_slugs );
                    $hierarchical_slugs[] = $slug;
                    $term_link             = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $term_link );
                } else $term_link = str_replace( "%$taxonomy%", $slug, $term_link );
                $term_link = $this->_home_url( $this->_user_trailingslashit( $term_link, 'category' ) );
            }
            if ( 'category' === $taxonomy )
                $term_link = $this->_apply_filters( 'category_link', $term_link, $term->term_id );
            return $this->_apply_filters( 'term_link', $term_link, $term, $taxonomy );
        }//4521
        /**
         * @description Display the taxonomies of a post with available options.
         * @param \array[] ...$args
         */
        protected function _the_taxonomies(array ...$args):void{
            $defaults = ['post' => 0,'before' => '','sep' => ' ','after' => '',];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            echo $parsed_args['before'] . implode( $parsed_args['sep'], $this->_get_the_taxonomies( $parsed_args['post'], $parsed_args ) ) . $parsed_args['after'];
        }//4643
        /**
         * @description Retrieve all taxonomies associated with a post.
         * @param mixed $post
         * @param array $args
         * @return array
         */
        protected function _get_the_taxonomies( $post = 0, ...$args):array{
            $post = $this->_get_post( $post );
            $args = $this->_tp_parse_args( $args,['template' => $this->__( '%s: %l.' ), 'term_template' => "<a href='%1\$s'>%2\$s</a>", ]);
            $taxonomies = [];
            if ( ! $post ) return $taxonomies;
            foreach ( $this->_get_object_taxonomies( $post ) as $taxonomy ) {
                $t = (array) $this->_get_taxonomy( $taxonomy );
                if ( empty( $t['label'] ) ) $t['label'] = $taxonomy;
                if ( empty( $t['args'] ) ) $t['args'] = [];
                if ( empty( $t['template'] ) ) $t['template'] = $args['template'];
                if ( empty( $t['term_template'] ) ) $t['term_template'] = $args['term_template'];
                $terms = $this->_get_object_term_cache( $post->ID, $taxonomy );
                if ( false === $terms ) $terms = $this->_tp_get_object_terms( $post->ID, $taxonomy, $t['args'] );
                $links = [];
                foreach ( $terms as $term )
                    $links[] = $this->_tp_sprintf( $t['term_template'], $this->_esc_attr( $this->_get_term_link( $term ) ), $term->name );
                if ( $links ) $taxonomies[ $taxonomy ] = $this->_tp_sprintf( $t['template'], $t['label'], $links, $terms );
            }
            return $taxonomies;
        }//4675
        /**
         * @description Retrieve all taxonomy names for the given post.
         * @param mixed $post
         * @return mixed
         */
        protected function _get_post_taxonomies( $post = 0 ){
            $post = $this->_get_post( $post );
            return $this->_get_object_taxonomies( $post );
        }//4732
    }
}else die;