<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-6-2022
 * Time: 09:00
 */
namespace TP_Admin\Traits\AdminTemplates;
use TP_Core\Libs\Walkers\TP_Walker;
use TP_Core\Libs\Walkers\TP_Walker_Category_Checklist;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_locale;
if(ABSPATH){
    trait _adm_template_01{
        use _init_locale;
        use _init_db;
        /**
         * @description Output an unordered list of checkbox input elements labeled with category names.
         * @param int $post_id
         * @param \array[] ...$cat_args
         */
        protected function _tp_category_checklist( $post_id = 0, ...$cat_args ):void{
            $this->_tp_terms_checklist($post_id,['taxonomy' => 'category',
                'descendants_and_self' => $cat_args['descendants_and_self'] ?: 0,
                'selected_cats' => $cat_args['selected_cats'] ?: false,
                'popular_cats' => $cat_args['popular_cats'] ?: false,
                'walker' => $cat_args['walker'] ?: null,
                'checked_on_top' => $cat_args['checked_on_top'] ?: true,
            ]);
        }//40
        /**
         * @description Output an unordered list of checkbox input elements labelled with term names.
         * @param int $post_id
         * @param array ...$terms_args
         * @return string
         */
        protected function _tp_get_terms_checklist( $post_id = 0, ...$terms_args):string{
            $defaults = ['descendants_and_self' => 0,'selected_cats' => false,
                'popular_cats' => false,'walker' => null,'taxonomy' => 'category',
                'checked_on_top' => true];
            $params = $this->_apply_filters( 'tp_terms_checklist_args', $terms_args, $post_id );
            $parsed_args = $this->_tp_parse_args( $params, $defaults );
            if ( empty( $parsed_args['walker'] ) || ! ( $parsed_args['walker'] instanceof TP_Walker ) ) {
                $walker = new TP_Walker_Category_Checklist();
            } else {$walker = $parsed_args['walker'];}
            $taxonomy             = $parsed_args['taxonomy'];
            $descendants_and_self = (int) $parsed_args['descendants_and_self'];
            $args = array( 'taxonomy' => $taxonomy );
            $tax              = $this->_get_taxonomy( $taxonomy );
            $args['disabled'] = ! $this->_current_user_can( $tax->cap->assign_terms );
            $args['list_only'] = ! empty( $parsed_args['list_only'] );
            if ( is_array( $parsed_args['selected_cats'] ) ) {
                $args['selected_cats'] = array_map( 'intval', $parsed_args['selected_cats'] );
            } elseif ( $post_id ) {
                $args['selected_cats'] = $this->_tp_get_object_terms( $post_id, $taxonomy, array_merge( $args, array( 'fields' => 'ids' ) ) );
            } else {$args['selected_cats'] = [];}
            if ( is_array( $parsed_args['popular_cats'] ) ) {
                $args['popular_cats'] = array_map( 'intval', $parsed_args['popular_cats'] );
            } else {
                $args['popular_cats'] = $this->_get_terms( //todo
                    ['taxonomy' => $taxonomy, 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false,]);
            }
            if ( $descendants_and_self ) {
                $categories = (array) $this->_get_terms(
                    ['taxonomy' => $taxonomy, 'child_of' => $descendants_and_self,'hierarchical' => 0,'hide_empty' => 0,]);
                $self       = $this->_get_term( $descendants_and_self, $taxonomy );
                array_unshift( $categories, $self );
            } else {  $categories = (array) $this->_get_terms(['taxonomy' => $taxonomy,'get' => 'all',]);}
            $output = '';
            if ( $parsed_args['checked_ontop'] ) {
                // Post-process $categories rather than adding an exclude to the get_terms() query
                // to keep the query the same across all posts (for any query cache).
                $checked_categories = array();
                $keys               = array_keys( $categories );
                foreach ( $keys as $k ) {
                    if ( in_array( $categories[ $k ]->term_id, $args['selected_cats'], true ) ) {
                        $checked_categories[] = $categories[ $k ];
                        unset( $categories[ $k ] );
                    }
                }
                $output .= $walker->walk( $checked_categories, 0, $args );
            }
            $output .= $walker->walk( $categories, 0, $args );
            return $output;
        }//81
        /**
         * @param $taxonomy
         * @param int $number
         * @return array|string
         */
        protected function _tp_get_popular_terms_checklist( $taxonomy, $number = 10){ //not used , $default = 0
            $post = $this->_get_post();
            if ( $post && $post->ID ) {
                $checked_terms = $this->_tp_get_object_terms( $post->ID, $taxonomy,['fields' => 'ids']);
            } else { $checked_terms = [];}
            $terms = $this->_get_terms(['taxonomy' => $taxonomy, 'orderby' => 'count','order' => 'DESC', 'number' => $number, 'hierarchical' => false,]);
            $tax = $this->_get_taxonomy( $taxonomy );
            $popular_ids = [];
            $cat = 0;
            foreach ((array)$terms as $term){
                $popular_ids[] = $term->term_id;
                $id      = "popular-$taxonomy-$term->term_id";
                $checked = in_array( $term->term_id, $checked_terms, true ) ? 'checked="checked"' : '';
                ++$cat;
                $term_setup = static function() use($id,$term,$checked,$tax,$cat){
                    $html = "<li id='$id' data-category_id='{$id}_{$cat}' class='popular-category'>";
                    $html .= "<dt><label class='select-item'>{(new self)->_esc_html( (new self)->_apply_filters( 'the_category', $term->name, '', '' ) )}</label></dt>";
                    $other_input_values  = $checked;
                    $other_input_values .= (new self)->_get_disabled( ! (new self)->_current_user_can( $tax->cap->assign_terms ) );
                    $html .= "<dd><input id='in_{$id}' value='{(int)$term->term_id}' type='checkbox' $other_input_values /></dd>";
                    $html .= "</li>";
                    return $html;
                };
                /** @noinspection UnnecessaryCastingInspection */
                $popular_ids .=(string)$term_setup;
            }
            return $popular_ids;
        }//209
        /**
         * @description Outputs a link category checklist element.
         * @param int $link_id
         * @return string|void
         */
        protected function _tp_get_link_category_checklist( $link_id = 0 ){
            $default = 1;
            $checked_categories = [];
            if ( $link_id ) {
                $checked_categories = $this->_tp_get_link_cats( $link_id );
                if (!count($checked_categories)){ $checked_categories[] = $default;}
            } else {$checked_categories[] = $default;}
            $categories = $this->_get_terms(['taxonomy'=> 'link_category','orderby'=> 'name','hide_empty' => 0,]);
            if (empty($categories)){ return;}
            $category_list = '';
            foreach ( $categories as $category ) {
                $cat_id = $category->term_id;
                $name    = $this->_esc_html( $this->_apply_filters( 'the_category', $category->name, '', '' ) );
                $checked = in_array( $cat_id, $checked_categories, true ) ? ' checked="checked"' : '';
                $category_list .="<li id='link_category_{$cat_id}'>";
                $category_list .="<dt><label for='in_link_category_{$cat_id}' class='select-item'>$name</label></dt>";
                $category_list .="<dd><input value=',{$cat_id},' type='checkbox' name='link_category[]' id='in_link_category_{$cat_id}' $checked /></dd>";
                $category_list .="</li>";

            }
            return $category_list;
        }//263
        /**
         * @description Adds hidden fields with the data for use in the inline editor for posts and pages.
         * @param $post
         * @return string
         */
        protected function _get_inline_data( $post ):string{
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            if (!$this->_current_user_can( 'edit_post', $post->ID)){return false;}
            $title = $this->_esc_textarea( trim( $post->post_title ) );
            $html  = "<div id='inline_{$post->ID}' class='hidden'>";
            $html .= "<div class='post title'><h6>$title</h6></div>";
            $html .= "<div class='post name'>{$this->_apply_filters( 'editable_slug', $post->post_name, $post )}</div>";
            $html .= "<div class='post author'>{$post->post_author}</div>";
            $html .= "<div class='status comment'>{$this->_esc_html( $post->comment_status )}</div>";
            $html .= "<div class='status ping'>{$this->_esc_html( $post->ping_status )}</div>";
            $html .= "<div class='status'>{$this->_esc_html( $post->post_status )}</div>";
            $html .= "<div class='date jj'>{$this->_mysql2date( 'd', $post->post_date, false )}</div>";
            $html .= "<div class='date mm'>{$this->_mysql2date( 'm', $post->post_date, false )}</div>";
            $html .= "<div class='date aa'>{$this->_mysql2date( 'Y', $post->post_date, false )}</div>";
            $html .= "<div class='date hh'>{$this->_mysql2date( 'H', $post->post_date, false )}</div>";
            $html .= "<div class='date mn'>{$this->_mysql2date( 'i', $post->post_date, false )}</div>";
            $html .= "<div class='date ss'>{$this->_mysql2date( 's', $post->post_date, false )}</div>";
            $html .= "<div class='post password'>{$this->_esc_html( $post->post_password )}</div>";
            if ( $post_type_object->hierarchical ) {
                $html .= "<div class='post parent'>{$post->post_parent}</div>";
            }
            $page_template = ( $post->page_template ? $this->_esc_html( $post->page_template ) : 'default' );
            $html .= "<div class='page template'>$page_template</div>";
            if ( $this->_post_type_supports( $post->post_type, 'page-attributes' ) ) {
                $html .= "<div class='menu order'>{$post->menu_order}</div>";
            }
            $taxonomy_names = $this->_get_object_taxonomies( $post->post_type );
            foreach ( $taxonomy_names as $taxonomy_name ) {
                $taxonomy = $this->_get_taxonomy( $taxonomy_name );
                if ( $taxonomy->hierarchical && $taxonomy->show_ui ) {
                    $terms = $this->_get_object_term_cache( $post->ID, $taxonomy_name );
                    if ( false === $terms ) {
                        $terms = $this->_tp_get_object_terms( $post->ID, $taxonomy_name );
                        $this->_tp_cache_add( $post->ID, $this->_tp_list_pluck( $terms, 'term_id' ), $taxonomy_name . '_relationships' );
                    }
                    $term_ids = empty( $terms ) ? array() : $this->_tp_list_pluck( $terms, 'term_id' );
                    $term_value = implode( ',', $term_ids );
                    $html .= "<div id='{$taxonomy_name}_{$post->ID}' class='post category'>$term_value</div>";
                }elseif ( $taxonomy->show_ui ){
                    $terms_to_edit = $this->_get_terms( $post->ID, $taxonomy_name );//? _to_edit
                    if ( ! is_string( $terms_to_edit ) ) {
                        $terms_to_edit = '';
                    }
                    $html .= "<div id='{$taxonomy_name}_{$post->ID}' class='tags input'>{$this->_esc_html( str_replace( ',', ', ', $terms_to_edit ) )}</div>";
                }
            }
            if ( ! $post_type_object->hierarchical ) {
                $is_sticky = ( $this->_is_sticky( $post->ID ) ? 'sticky' : '' );
                $html .= "<div class='sticky'>$is_sticky</div>";
            }
            if ( $this->_post_type_supports( $post->post_type, 'post-formats' ) ) {
                $html .= "<div class='post format'>{$this->_esc_html( $this->_get_post_format( $post->ID ) )}</div>";
            }
            $html .= $this->_do_action( 'add_inline_data', $post, $post_type_object );
            $html .= "</div>";
            return $html;
        }//307

        /**
         * @description Outputs the in-line comment reply-to form in the Comments list table.
         * @param int $position
         * @param bool $checkbox
         * @param string $mode
         * @return string
         */
        protected function _tp_get_comment_reply( $position = 1, $checkbox = false, $mode = 'single'):string{
            $output = '';
            $content = $this->_apply_filters('tp_comment_reply','',['position' => $position,'checkbox' => $checkbox,'mode' => $mode,]);
            if (! empty( $content ) ) {
                return '';
            }
            if ( ! $this->tp_list_block ) {
                if ( 'single' === $mode ) {
                    $this->tp_list_block = $this->_get_list_block( 'TP_Post_Comments_List_Block' );
                } else {
                    $this->tp_list_block = $this->_get_list_block( 'TP_Comments_List_Block' );
                }
            }
            $quicktags_settings = ['buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'];
            $_editor = $this->_tp_get_editor('','reply_content',['media_buttons' => false,'tinymce' => false,'quicktags'=> $quicktags_settings,]);
            $_btn_setup  = "<span id='add_btn' style=''>{$this->__('Add Comment')}</span>";
            $_btn_setup .= "<span id='save_btn' style=''>{$this->__('Update Comment')}</span>";
            $_btn_setup .= "<span id='reply_btn' style=''>{$this->__('Submit Reply')}</span>";
            $_checkbox = $checkbox ? 1 : 0;
            $output .= "<form method='get'><div id='comment_reply' style=''><div id='reply_row' style=''><fieldset class='comment-reply'><legend>";
            $output .= "<span id='edit_legend' class='hidden'>{$this->__('Edit Comment')}</span><span id='reply_head' class='hidden'>{$this->__('Reply to Comment')}</span>";
            $output .= "<span id='add_head' class='hidden'>{$this->__('Add new Comment')}</span></legend><div id='reply_container'><ul><li>";
            $output .= "<dt><label for='reply_content' class='screen-reader-text'>{$this->__('Comment:')}</label></dt>";
            $output .= "<dd>$_editor</dd></li></ul></div><div id='edit_head' style=''><ul><li class='inside'>";
            $output .= "<dt><label for='author_name'>{$this->__('Name')}</label></dt>";
            $output .= "<dd><input name='new_comment_author' id='author_name' type='text' value='' size='50'/></dd>";
            $output .= "</li><li class='inside'><dt><label for='author_email'>{$this->__('Email')}</label></dt>";
            $output .= "<dd><input name='new_comment_author_email' id='author_email' type='email' value='' size='50'/></dd>";
            $output .= "</li><li class='inside'><dt><label for='author_url'>{$this->__('URL')}</label></dt>";
            $output .= "<dd><input name='new_comment_author_url' id='author_url' type='url' value='' size='103'/></dd>";
            $output .= "</li></ul></div><div id='reply_submit' class='submit'><ul><li class='reply-submit-buttons'>";
            $output .= "<dd><button class='save button button-primary' type='button'>$_btn_setup</button></dd>";
            $output .= "<dd><button class='cancel button' type='button'>{$this->__('Cancel')}</button></dd>";
            $output .= "<span class='waiting spinner'></span></li><li>";
            $output .= "<input name='action' id='action' type='hidden' value=''/>";
            $output .= "<input name='comment_ID' id='comment_ID' type='hidden' value=''/>";
            $output .= "<input name='comment_post_ID' id='comment_post_ID' type='hidden' value=''/>";
            $output .= "<input name='status' id='status' type='hidden' value=''/>";
            $output .= "<input name='position' id='position' type='hidden' value='$position'/>";
            $output .= "<input name='checkbox' id='checkbox' type='hidden' value='$_checkbox'/>";
            $output .= "<input name='mode' id='mode' type='hidden' value='{$this->_esc_attr($mode)}'/>";
            $output .= $this->_tp_get_nonce_field( 'reply_to-comment', '_async_nonce_reply_to_comment', false );
            if ($this->_current_user_can( 'unfiltered_html' ) ) {
                $output .= $this->_tp_get_nonce_field( 'unfiltered-html-comment', '_tp_unfiltered_html_comment', false );
            }
            $output .= "</li></ul></div></fieldset></div></div></form>";
            return $output;
        }//404
        /**
         * @description Output 'undo move to Trash' text for comments
         * @return string
         */
        protected function _tp_get_comment_trash_notice():string{
            $output  = "<div id='trash_undo_holder' class='hidden'>";
            $output .= "<div class='trash-undo-inside'>";
            $output .= sprintf($this->__('Comment by %s moved to the Trash.'), '<strong></strong>');
            $output .= "<span class='undo untrash'><a href='#'>{$this->__('Undo')}</a></span>";
            $output .= "</div></div>";
            $output .= "<div id='spam_undo_holder' class='hidden'>";
            $output .= "<div class='trash-undo-inside'>";
            $output .= sprintf($this->__('Comment by %s marked as spam.'), '<strong></strong>');
            $output .= "<span class='undo unspam'><a href='#'>{$this->__('Undo')}</a></span>";
            $output .= "</div></div>";
            return $output;
        }//534
        /**
         * @description Outputs a post's public meta data in the Custom Fields meta box.
         * @param $meta
         * @return string
         */
        protected function _get_list_meta( $meta ):string{
            $output  = "";
            if ( ! $meta ) {
                $output .= "<div id='list_block' class='meta-list-block' style='display:block'>";//todo must become none
                $output .= "<header><ul>";
                $output .= "<li class='list-name title'>{$this->_x('Name', 'meta name')}</li>";
                $output .= "<li class='list-value title'>{$this->__('Value')}</li>";
                $output .= "</ul></header>";
                $output .= "<div id='the_list' class='data-block' data-tp-lists='list:meta'><ul>";
                $output .= "<li class='list-name data'></li>";
                $output .= "<li class='list-value data'></li>";
                $output .= "</ul></div>";
                $output .= "</div>";
            }
            $count = 0;
            $output .= "<div id='list_block' class='meta-list-block'>";
            $output .= "<header><ul>";
            $output .= "<li class='list-name title'>{$this->_x('Name', 'meta name')}</li>";
            $output .= "<li class='list-value title'>{$this->__('Value')}</li>";
            $output .= "</ul></header>";
            $output .= "<div id='the_list' class='data-block' data-tp-lists='list:meta'><ul>";
            foreach ((array) $meta as $entry ) {
                $output .= $this->_list_meta_block( $entry, $count );
            }
            $output .= "</ul></div>";
            $output .= "</div>";
            return $output;
        }//564
        /**
         * @description Outputs a single row of public meta data in the Custom Fields meta box.
         * @param $entry
         * @param int $count
         * @return string
         */
        protected function _list_meta_block( $entry, &$count ):string{
            static $update_nonce = '';
            if ( $this->_is_protected_meta( $entry['meta_key'], 'post')){ return '';}
            if ( ! $update_nonce ) { $update_nonce = $this->_tp_create_nonce( 'add_meta' );}
            $output  = "";
            ++ $count;
            if ( $this->_is_serialized( $entry['meta_value'] ) ) {
                if ( $this->_is_serialized_string( $entry['meta_value'] ) ) {
                    $entry['meta_value'] = $this->_maybe_unserialize( $entry['meta_value'] );
                } else {
                    --$count;
                    return '';
                }
            }
            $entry['meta_key']   = $this->_esc_attr( $entry['meta_key'] );
            $entry['meta_value'] = $this->_esc_textarea( $entry['meta_value'] ); // Using a <textarea />.
            $entry['meta_id']    = (int) $entry['meta_id'];
            $delete_nonce = $this->_tp_create_nonce( 'delete-meta_' . $entry['meta_id'] );
            $output .= "\n\t<li id='meta_{$entry['meta_id']}'><ul>";
            $output .= "\n\t\t<li>";
            $output .= "<dt><label class='screen-reader-text' for='meta_{$entry['meta_id']}_key'>{$this->__('Key')}</label></dt>";
            $output .= "<dd><input name='meta[{$entry['meta_id']}][key]' id='meta_{$entry['meta_id']}_key' type='text' value='{$entry['meta_key']}' size='20'/></dd>";
            $output .= "\n\t\t</li><li class='submit'>";
            $output .= $this->_get_submit_button( $this->__( 'Delete' ), 'delete_meta small', "delete_meta[{$entry['meta_id']}]", false, array( 'data-tp-lists' => "delete:the-list:meta_{$entry['meta_id']}::_async_nonce=$delete_nonce" ) );
            $output .= "\n\t\t</li><li class='submit'>";
            $output .= $this->_get_submit_button( $this->__( 'Update' ), 'update_meta small', "meta_{$entry['meta_id']}_submit", false, array( 'data-tp-lists' => "add:the-list:meta-{$entry['meta_id']}::_async_nonce_add_meta=$update_nonce" ) );
            $output .= "\n\t\t</li><li>";
            $output .= $this->_tp_get_nonce_field( 'change_meta', '_async_nonce', false );
            $output .= "\n\t\t</li><li>";
            $output .= "<dt><label class='screen-reader-text' for='meta_{$entry['meta_id']}_value'>{$this->__('Value')}</label></dt>";
            $output .= "<dd><textarea name='meta[{$entry['meta_id']}][value]' id='meta_{$entry['meta_id']}_value' rows='2' cols='30'>{$entry['meta_value']}</textarea></dd>";
            $output .= "</li>";
            $output .= "</ul></li>\n";
            return $output;
        }//610 _list_meta_row
        /**
         * @description Prints the form in the Custom Fields meta box.
         * @param null $post
         * @return string
         */
        protected function _get_meta_form( $post = null ):string{
            $this->tpdb = $this->_init_db();
            $post = $this->_get_post( $post );
            $keys = $this->_apply_filters( 'postmeta_form_keys', null, $post );
            if ( null === $keys ) {//todo for when I've the db up and running
                //$limit = $this->_apply_filters( 'postmeta_form_limit', 30 );
                //$keys = $this->tpdb->get_col($this->tpdb->prepare(TP_SELECT  . "DISTINCT meta_key
				//FROM $this->tpdb->post_meta WHERE meta_key NOT BETWEEN '_' AND '_z' HAVING meta_key NOT LIKE %s ORDER BY meta_key LIMIT %d",$this->tpdb->esc_like( '_' ) . '%',$limit));
            }
            if ( $keys ) {
                natcasesort( $keys );
                $meta_key_input_id = 'meta_key_select';
            } else { $meta_key_input_id = 'meta_key_input';}
            $output  = "<div class='custom-field container'><ul id='new_meta'><li>";
            $output .= "<h5 class=''><strong>{$this->__('Add New Custom Field:')}</strong></h5>";
            $output .= "</li><li>";
            $output .= "<dt><label for='{$meta_key_input_id}'>{$this->_x('Name:', 'meta name')}</label></dt>";
            //$output .= "<dd><input name='' id='' type='' value='{$this->_esc_attr('')}'/></dd>";
            $output .= "</li><li>";
            $output .= "<dt><label for='meta_value'>{$this->__('Value:')}</label></dt>";
            //$output .= "<dd><input name='' id='' type='' value='{$this->_esc_attr('')}'/></dd>";

            if ( $keys ) {
                $output .= "</li><li>";
                $output .= "<dd><select id='meta_key_select' name='meta_key_select'>";
                $output .= "<option value='#NONE#'>{$this->__('&mdash; Select &mdash;')}</option>";
                foreach ((array) $keys as $key ) {
                    if ( $this->_is_protected_meta( $key, 'post' ) || ! $this->_current_user_can( 'add_post_meta', $post->ID, $key ) ) {
                        continue;}
                    $output .= "\n<option value='{$this->_esc_url($key)}'>{$this->_esc_html( $key )}</option>";
                }
                $output .= "</select></dd>";
                $output .= "</li><li>";
                $output .= "<dd><input name='meta_key_input' id='meta_key_input' class='hide-if-js' type='text' value=''/></dd>";
                $output .= "</li><li>";
                $_post_custom_stuff = '#post_custom_stuff';
                $output .= "<a href='$_post_custom_stuff' class='hide-if-no-js' onclick=''></a>";//todo no jQuery
                $output .= "<span id='enter_new'>{$this->__('Enter New')}</span>";
                $output .= "<span id='cancel_new' class='hidden'>{$this->__('Cancel')}</span>";
            }else{
                $output .= "</li><li>";
                $output .= "<dd><input name='meta_key_input' id='meta_key_input' type='text' value=''/></dd>";
            }
            $output .= "</li><li>";
            $output .= "<dd><textarea id='meta_value' name='meta_value' rows='2' cols='25'></textarea></dd>";
            $output .= "</li><li>";
            $output .= $this->_get_submit_button($this->__('Add Custom Field'),'','add_meta',false,['id'=> 'new_meta_submit','data-tp-lists' => 'add:the-list:new_meta',]);
            $output .= "</li><li>";
            $output .= $this->_tp_get_nonce_field( 'add-meta', '_async_nonce-add-meta', false );
            $output .= "</li></ul></div>";
            return $output;
        }//665
        /**
         * @description Print out HTML form date elements for editing post or comment publish date.
         * @param int|bool $edit
         * @param int $for_post
         * @param int $tab_index
         * @param int $multi
         * @return string
         */
        protected function _get_touch_time( $edit = 1, $for_post = 1, $tab_index = 0, $multi = 0 ):string{
            $this->tp_locale = $this->_init_locale();
            $post = $this->_get_post();
            if ( $for_post ) {
                $edit = ! ( in_array( $post->post_status, array( 'draft', 'pending' ), true ) && ( ! $post->post_date_gmt || '0000-00-00 00:00:00' === $post->post_date_gmt ) );
            }
            $tab_index_attribute = '';
            if ( (int) $tab_index > 0 ) { $tab_index_attribute = " tabindex='$tab_index'"; }
            $post_date = ( $for_post ) ? $post->post_date : $this->_get_comment()->comment_date;
            $jj        = ( $edit ) ? $this->_mysql2date( 'd', $post_date, false ) : $this->_current_time( 'd' );
            $mm        = ( $edit ) ? $this->_mysql2date( 'm', $post_date, false ) : $this->_current_time( 'm' );
            $aa        = ( $edit ) ? $this->_mysql2date( 'Y', $post_date, false ) : $this->_current_time( 'Y' );
            $hh        = ( $edit ) ? $this->_mysql2date( 'H', $post_date, false ) : $this->_current_time( 'H' );
            $mn        = ( $edit ) ? $this->_mysql2date( 'i', $post_date, false ) : $this->_current_time( 'i' );
            $ss        = ( $edit ) ? $this->_mysql2date( 's', $post_date, false ) : $this->_current_time( 's' );
            $cur_jj = $this->_current_time( 'd' );
            $cur_mm = $this->_current_time( 'm' );
            $cur_aa = $this->_current_time( 'Y' );
            $cur_hh = $this->_current_time( 'H' );
            $cur_mn = $this->_current_time( 'i' );
            $_multi = ( $multi ? '' : "id='mm' " );
            $month  = "<li><dt><label><span class='screen-reader-text'>{$this->__('Month.')}</span></label></dt>\n";
            $month .= "<dd><select class='form-required' $_multi name='mm' $tab_index_attribute>\n";
            for ($i = 1; $i < 13; ++$i) {
                $monthnum  = $this->_zero_ise( $i, 2 );
                $month_text = $this->tp_locale->get_month_abbrev( $this->tp_locale->get_month( $i ) );
                $month .= "\t\t\t<option value='$monthnum' data-text='$month_text' {$this->_get_selected( $monthnum, $mm)}>";
                $month .= sprintf($this->__('%1$s-%2$s'), $monthnum, $month_text);
                $month .= "</option>\n";
            }
            $month .= "</select></dd></li>";
            $day_id = ( $multi ? '' : "id='jj' " );
            $day_for = ( $multi ? '' : "for='jj' " );
            $day    = "<li><dt><label class='screen-reader-text' $day_for>{$this->__('Day')}</label></dt>";
            $day    .= "<dd><input name='jj' $day_id type='text' value='$jj' class='form-required' $tab_index_attribute maxlength='2' autocomplete='off'/></dd></li>";
            $year_id = ( $multi ? '' : "id='aa' " );
            $year_for = ( $multi ? '' : "for='aa' " );
            $year    = "<li><dt><label class='screen-reader-text' $year_for>{$this->__('Year')}</label></dt>";
            $year    .= "<dd><input name='aa' $year_id type='text' value='$aa' class='form-required' $tab_index_attribute maxlength='4' autocomplete='off'/></dd></li>";
            $hour_id = ( $multi ? '' : "id='hh' " );
            $hour_for = ( $multi ? '' : "for='hh' " );
            $hour   = "<li><dt><label class='screen-reader-text' $hour_for>{$this->__('Hour')}</label></dt>";
            $hour    .= "<dd><input name='hh' $hour_id type='text' value='$hh' class='form-required' $tab_index_attribute maxlength='2' autocomplete='off'/></dd></li>";
            $minute_id = ( $multi ? '' : "id='mn' " );
            $minute_for = ( $multi ? '' : "for='mn' " );
            $minute    = "<li><dt><label class='screen-reader-text' $minute_for>{$this->__('Minute')}</label></dt>";
            $minute    .= "<dd><input name='mn' $minute_id type='text' value='$mn' class='form-required' $tab_index_attribute maxlength='2' autocomplete='off'/></dd></li>";
            $output = "<div class='timestamp-wrap'><ul>";
            $output .= sprintf($this->__('%1$s %2$s, %3$s at %4$s:%5$s'), $month, $day, $year, $hour, $minute);
            $output .= "<li><input id='ss' name='ss' type='hidden' value='$ss'/>";
            $output .= "</li></ul></div>";
            if ( $multi ) {return false;}
            $output .= "\n\n";
            $map = ['mm' => [$mm, $cur_mm] ,'jj' => [$jj, $cur_jj] , 'aa' => [$aa, $cur_aa] , 'hh' => [$hh, $cur_hh] ,'mn' => [$mn, $cur_mn] ,];
            foreach ( $map as $time_unit => $value ) {
                @list( $unit, $curr ) = $value;
                $output .= "<input id='hidden_{$time_unit}' name='hidden_' type='hidden' value='$unit'/>\n";
                $cur_time_unit = 'cur_' . $time_unit;
                $output .= "<input id='$cur_time_unit' name='$cur_time_unit' type='hidden' value='$curr'/>\n";
            }
            $output .= "<ul><li>";
            $_edit_timestamp = "#edit_timestamp";
            $output .= "<dd><a href='$_edit_timestamp' class='save-timestamp hide-if-no-js button'>{$this->__('OK')}</a></dd>";
            $output .= "<dd><a href='$_edit_timestamp' class='cancel-timestamp hide-if-no-js button-cancel'>{$this->__('Cancel')}</a></dd>";
            $output .= "</li></ul>";
            return $output;
        }//786
    }
}else die;