<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 04:24
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Libs\Walkers\TP_Walker_Page;
use TP_Core\Libs\Walkers\TP_Walker_PageDropdown;
if(ABSPATH){
    trait _post_template_03 {
        use _init_queries;
        /**
         * @description Display a list of post custom fields.
         * @return null|string
         */
        protected function _get_the_meta():?string{
            $return = null;
            $keys = $this->_get_post_custom_keys();
            if ( $keys ) {
                $li_html = '';
                foreach ( (array) $keys as $key ) {
                    $keyt = trim( $key );
                    if ( $this->_is_protected_meta( $keyt, 'post' ) ) continue;
                    $values = array_map( 'trim', $this->_get_post_custom_values( $key ) );
                    $value  = implode( ', ', $values );
                    $html = sprintf(
                        "<li><span class='post-meta-key'>%s</span> %s</li>\n",
                        sprintf( $this->_x( '%s:', 'Post custom field name' ), $key ),
                        $value
                    );
                    $li_html .= $this->_apply_filters( 'the_meta_key', $html, $key, $value );
                }
                if ( $li_html ) $return = "<ul class='post-meta'>\n{$li_html}</ul>\n";
            }
            return $return;
        }//added
        protected function _the_meta():void{
            echo $this->_get_the_meta();
        }//1093 from post-template
        /**
         * @description Retrieve or display a list of pages as a dropdown (select list).
         * @param \array[] ...$args
         * @return mixed
         */
        protected function _tp_get_dropdown_pages(array ...$args){
            $defaults = ['depth' => 0,'child_of' => 0,'selected' => 0,'name' => 'page_id','id' => '',
                'class' => '','show_option_none' => '','show_option_no_change' => '','option_none_value' => '','value_field' => 'ID',];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $pages  = $this->_get_pages( $parsed_args );
            $output = '';
            if ( ! empty( $pages ) ) {
                $class = '';
                if ( ! empty( $parsed_args['class'] ) ) $class = " class='{$this->_esc_attr( $parsed_args['class'] )}'";
                $output = "<select name='{$this->_esc_attr( $parsed_args['name'] )}' $class id='{$this->_esc_attr( $parsed_args['id'] )}'>\n";
                if ( $parsed_args['show_option_no_change'] )
                    $output .= "\t<option value='-1'>{$parsed_args['show_option_no_change']}</option>\n";
                if ( $parsed_args['show_option_none'] )
                    $output .= "\t<option value='{$this->_esc_attr( $parsed_args['option_none_value'] )}'>{$parsed_args['show_option_none']}</option>\n";
                $output .= $this->_walk_page_dropdown_tree( $pages, $parsed_args['depth'], $parsed_args );
                $output .= "</select>\n";
            }
            $html = $this->_apply_filters( 'tp_dropdown_pages', $output, $parsed_args, $pages );
            return $html;
        }//added
        protected function _tp_dropdown_pages(array ...$args):void{
            echo $this->_tp_get_dropdown_pages($args);
        }//1165 from post-template
        /**
         * @description Retrieve or display a list of pages (or hierarchical post type items) in list (li) format.
         * @param \array[] ...$args
         * @return mixed
         */
        protected function _tp_get_list_pages(array ...$args){
            $tp_query = $this->_init_query();
            $defaults = ['depth' => 0,'show_date' => '','date_format' => $this->_get_option( 'date_format' ),
                'child_of' => 0,'exclude' => '','title_li' => $this->__( 'Pages' ),'authors' => '','sort_column' => 'menu_order, post_title',
                'link_before' => '','link_after' => '','item_spacing' => 'preserve','walker' => '',];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            if ( ! in_array( $parsed_args['item_spacing'], array( 'preserve', 'discard' ), true ) )
                $parsed_args['item_spacing'] = $defaults['item_spacing'];
            $output       = '';
            $current_page = 0;
            $parsed_args['exclude'] = preg_replace( '/[^0-9,]/', '', $parsed_args['exclude'] );
            $parsed_args['hierarchical'] = 0;
            $pages = $this->_get_pages( $parsed_args );
            if ( ! empty( $pages ) ) {
                if ( $parsed_args['title_li'] )
                    $output .= "<li class='page-nav'>{$parsed_args['title_li']}<ul>";
                if ($tp_query->is_posts_page || $this->_is_page() || $this->_is_attachment())
                    $current_page = $this->_get_queried_object_id();
                elseif ( $this->_is_singular() ) {
                    $queried_object = $this->_get_queried_object();
                    if ( $this->_is_post_type_hierarchical( $queried_object->post_type ) )
                        $current_page = $queried_object->ID;
                }
                $output .= $this->_walk_page_tree( $pages, $parsed_args['depth'], $current_page, $parsed_args );
                if ( $parsed_args['title_li'] ) $output .= '</ul></li>';
            }

            return $this->_apply_filters( 'tp_list_pages', $output, $parsed_args, $pages );
        }//added
        protected function _tp_list_pages(array ...$args):void{
            echo $this->_tp_get_list_pages($args);
        }//1266 from post-template
        /**
         * @description Displays or retrieves a list of pages with an optional home link.
         * @param \array[] ...$args
         * @return string
         */
        protected function _tp_get_page_menu(array ...$args):string{
            $defaults = ['sort_column' => 'menu_order, post_title','menu_id' => '','menu_class' => 'menu',
                'container' => 'div','link_before' => '','link_after' => '','before' => '<ul>','after' => '</ul>',
                'item_spacing' => 'discard','walker' => '',];
            $args     = $this->_tp_parse_args( $args, $defaults );
            if ( ! in_array( $args['item_spacing'], array( 'preserve', 'discard' ), true ) )
                $args['item_spacing'] = $defaults['item_spacing'];
            if ( 'preserve' === $args['item_spacing'] ) {
                $t = "\t";
                $n = "\n";
            } else {
                $t = '';
                $n = '';
            }
            $args = $this->_apply_filters( 'tp_page_menu_args', $args );
            $menu = '';
            $list_args = $args;
            if ( ! empty( $args['show_home'] ) ) {
                if ( true === $args['show_home'] || '1' === $args['show_home'] || 1 === $args['show_home'] )
                    $text = $this->__( 'Home' );
                else $text = $args['show_home'];
                $class = '';
                if ( $this->_is_front_page() && ! $this->_is_paged() ) $class = " class='current_page_item'";
                $menu .= "<li $class ><a href='{$this->_home_url( '/' )}'>{$args['link_before']}$text{$args['link_after']}</a></li>";
                if ( 'page' === $this->_get_option( 'show_on_front' ) ) {
                    if ( ! empty( $list_args['exclude'] ) ) $list_args['exclude'] .= ',';
                    else $list_args['exclude'] = '';
                    $list_args['exclude'] .= $this->_get_option( 'page_on_front' );
                }
            }
            $list_args['echo']     = false;
            $list_args['title_li'] = '';
            $menu                 .= $this->_tp_get_list_pages( $list_args );
            $container = $this->_sanitize_text_field( $args['container'] );
            if ( empty( $container ) ) $container = 'div';
            if ( $menu ) {
                if ( isset( $args['fallback_cb'] ) &&
                    'tp_page_menu' === $args['fallback_cb'] &&
                    'ul' !== $container ) {
                    $args['before'] = "<ul>{$n}{$t}";
                    $args['after']  = '</ul>';
                }
                $menu = $args['before'] . $menu . $args['after'];
            }
            $attrs = '';
            if ( ! empty( $args['menu_id'] ) )
                $attrs .= " id='{$this->_esc_attr( $args['menu_id'] )}'";
            if ( ! empty( $args['menu_class'] ) )
                $attrs .= " class='{$this->_esc_attr( $args['menu_class'] )}'";
            $menu = "<{$container}{$attrs}>" . $menu . "</{$container}>{$n}";
            $menu = $this->_apply_filters( 'tp_page_menu', $menu, $args );
            return $menu;
        }//added
        protected function _tp_page_menu(array ...$args):void{
            echo $this->_tp_get_page_menu($args);
        }//1388 from post-template
        /**
         * @description Retrieve HTML list content for page list.
         * @param $pages
         * @param $depth
         * @param $current_page
         * @param $r
         * @return string
         */
        protected function _walk_page_tree( $pages, $depth, $current_page, $r ):string{
            if ( empty( $r['walker'] ) ) $walker = new TP_Walker_Page;
            else $walker = $r['walker'];
            foreach ( (array) $pages as $page ) {
                if ( $page->post_parent ) $r['pages_with_children'][ $page->post_parent ] = true;
            }
            return $walker->walk( $pages, $depth, $r, $current_page );
        }//1527 from post-template
        /**
         * @description Retrieve HTML dropdown (select) content for page list.
         * @param array ...$args
         * @return string
         */
        protected function _walk_page_dropdown_tree( ...$args ):string{
            if ( empty( $args[2]['walker'] ) ) $walker = new TP_Walker_PageDropdown;
            else $walker = $args[2]['walker'];
            return $walker->walk( ...$args );
        }//1559 from post-template
        /**
         * @description Display an attachment page link using an image or icon.
         * @param int $id
         * @param bool $full_size
         * @param bool $permalink
         * @return string
         */
        protected function _get_the_attachment_link( $id = 0, $full_size = false, $permalink = false ):string{
            $output  = "";
            if ( $full_size ) $output .= $this->_tp_get_attachment_link( $id, 'full', $permalink );
            else  $output .= $this->_tp_get_attachment_link( $id, 'thumbnail', $permalink );
            return $output;
        }//1586 from post-template
        protected function _the_attachment_link( $id = 0, $full_size = false, $permalink = false ):void{
            echo $this->_get_the_attachment_link( $id, $full_size, $permalink);
        }
        /**
         * @description Retrieve an attachment page link using an image or icon, if possible.
         * @param int $id
         * @param string $size
         * @param bool $permalink
         * @param bool $icon
         * @param bool $text
         * @param string $attr
         * @return mixed
         */
        protected function _tp_get_attachment_link( $id = 0, $size = 'thumbnail', $permalink = false, $icon = false, $text = false, $attr = '' ){
            $_post = $this->_get_post( $id );
            if ( empty( $_post ) || ( 'attachment' !== $_post->post_type ) || ! $this->_tp_get_attachment_url( $_post->ID ) )
                return $this->__( 'Missing Attachment' );
            $url = $this->_tp_get_attachment_url( $_post->ID );
            if ( $permalink ) $url = $this->_get_attachment_link( $_post->ID );
            if ( $text ) $link_text = $text;
            elseif ( $size && 'none' !== $size )
                $link_text = $this->_tp_get_attachment_image( $_post->ID, $size, $icon, $attr );
            else $link_text = '';
            if ( '' === trim( $link_text ) ) $link_text = $_post->post_title;
            if ( '' === trim( $link_text ) )
                $link_text = $this->_esc_html( pathinfo( $this->_get_attached_file( $_post->ID ), PATHINFO_FILENAME ) );
            return $this->_apply_filters( 'tp_get_attachment_link', "<a href='{$this->_esc_url( $url )}'>$link_text</a>", $id, $size, $permalink, $icon, $text, $attr );
        }//1614 from post-template
        /**
         * @description Wrap attachment in paragraph tag before content.
         * @param $content
         * @return string
         */
        protected function _prepend_attachment( $content ):string{
            $post = $this->_get_post();
            if ( empty( $post->post_type ) || 'attachment' !== $post->post_type ) return $content;
            if ( $this->_tp_attachment_is( 'video', $post ) ) {
                $meta = $this->_tp_get_attachment_metadata( $this->_get_the_ID() );
                $atts = array( 'src' => $this->_tp_get_attachment_url() );
                if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                    $atts['width']  = (int) $meta['width'];
                    $atts['height'] = (int) $meta['height'];
                }
                if ( $this->_has_post_thumbnail() ) $atts['poster'] = $this->_tp_get_attachment_url( $this->_get_post_thumbnail_id() );
                $p = $this->_get_video_shortcode( $atts );
            } elseif ( $this->_tp_attachment_is( 'audio', $post ) ) {
                $p = $this->_get_audio_shortcode( array( 'src' => $this->_tp_get_attachment_url() ) );
            } else {
                $p = "<p class='attachment'>";
                $p .= $this->_tp_get_attachment_link( 0, 'medium', false );
                $p .= '</p>';
            }
            $p = $this->_apply_filters( 'prepend_attachment', $p );
            return "$p\n$content";
        }//1668 from post-template
        /**
         * @description Retrieve protected post password form content.
         * @param string|int $post
         * @return mixed
         */
        protected function _get_the_password_form($post = 0 ){
            $post   = (string) $this->_get_post( $post );
            $label  = 'pwbox-' . ( empty( $post->ID ) ? mt_rand() : $post->ID );
            $output = "<form action='{$this->_esc_url( $this->_site_url( 'tp-login.php?action=postpass', 'login_post' ) )}' class='post-password-form' method='post'>";
            $output .= "<p>{$this->__( 'This content is password protected. To view it please enter your password below:' )}</p>";
            $output .= "<dt><label for='$label'>{$this->__( 'Password:' )}</label></dt>";
            $output .= "<dd><input type='password' name='post_password' id='$label' size='20'/></dd>";
            $output .= "<dd><input type='submit' name='Submit' value='{$this->_esc_attr_x( 'Enter', 'post password form' )}'/>></dd>";
            $output .= "</form>";
            return $this->_apply_filters( 'the_password_form', $output, $post );
        }//1721 from post-template
    }
}else die;