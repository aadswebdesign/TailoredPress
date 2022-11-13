<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-6-2022
 * Time: 06:46
 */
namespace TP_Core\Traits\AdminBar;
use TP_Admin\Libs\Adm_Screen;
use TP_Core\Libs\Recovery\TP_Recovery_Mode;
use TP_Core\Libs\TP_Admin_Bar;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_adminbar;
use TP_Core\Traits\Inits\_init_custom;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;

if(ABSPATH){
    trait _admin_bar_02{
        use _init_adminbar,_init_custom,_init_error,_init_queries;
        /**
         * @description Provides a shortlink.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_shortlink_menu(TP_Admin_Bar $tp_admin_bar ):void{
            $short = $this->_tp_get_short_link( 0, 'query' );
            $id    = 'get-shortlink';
            if ( empty( $short)){ return;}
            $html = "<dd><input name='' class='shortlink-input' readonly type='text' value='{$this->_esc_attr($short)}' /></dd>";
            $tp_admin_bar->add_node(['id' => $id,'title' => $this->__('Shortlink'),'href' => $short,'meta' => ['html' => $html],] );
        }//719
        /**
         * @description Provides an edit link for posts and terms.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_edit_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if ( $this->_is_admin() ) {
                $_current_screen = $this->_get_current_screen();
                $current_screen = null;
                if($_current_screen instanceof Adm_Screen ){ $current_screen = $_current_screen;}
                $post = $this->_get_post();
                $post_type_object = null;
                if ( 'post' === $current_screen->base ) {
                    $post_type_object = $this->_get_post_type_object( $post->post_type );
                } elseif ( 'edit' === $current_screen->base ) {
                    $post_type_object = $this->_get_post_type_object( $current_screen->post_type );
                } elseif ( 'edit-comments' === $current_screen->base && $this->tp_post_id ) {
                    $post = $this->_get_post( $this->tp_post_id );
                    if ( $post ) {  $post_type_object = $this->_get_post_type_object( $post->post_type ); }
                }
                if ( ( 'post' === $current_screen->base || 'edit-comments' === $current_screen->base )
                    && 'add' !== $current_screen->action && ($post_type_object) && ($post_type_object->public )
                    && ( $post_type_object->show_in_admin_bar ) && $this->_current_user_can( 'read_post', $post->ID ) ) {
                    if ( 'draft' === $post->post_status ) {
                        $preview_link = $this->_get_preview_post_link( $post );
                        $tp_admin_bar->add_node(['id' => 'preview','title' => $post_type_object->labels->view_item,
                            'href'  => $this->_esc_url( $preview_link ),'meta'  => ['target' => 'tp-preview-' . $post->ID],]);
                    } else {
                        $tp_admin_bar->add_node(['id' => 'view','title' => $post_type_object->labels->view_item, 'href'  => $this->_get_permalink( $post->ID ),]);
                    }
                } elseif ( 'edit' === $current_screen->base && ( $post_type_object ) && ( $post_type_object->public )
                    && ( $post_type_object->show_in_admin_bar ) && ( $this->_get_post_type_archive_link( $post_type_object->name ) )
                    && ! ( 'post' === $post_type_object->name && 'posts' === $this->_get_option( 'show_on_front' ) ) ) {
                    $tp_admin_bar->add_node(['id' => 'archive','title' => $post_type_object->labels->view_items,'href' => $this->_get_post_type_archive_link( $current_screen->post_type ),]);
                } elseif ( 'term' === $current_screen->base && isset( $this->tp_tag ) && is_object( $this->tp_tag ) && ! $this->_init_error( $this->tp_tag ) ) {
                    $tax = $this->_get_taxonomy( $this->tp_tag->taxonomy );
                    if ( $this->_is_taxonomy_viewable( $tax ) ) {
                        $tp_admin_bar->add_node(['id' => 'view','title' => $tax->labels->view_item,'href' => $this->_get_term_link( $this->tp_tag ),]);
                    }
                } elseif ( 'user-edit' === $current_screen->base && isset( $this->tp_user_id ) ) {
                    $user_object = $this->_get_user_data( $this->tp_user_id );
                    $view_link   = $this->_get_author_posts_url( $user_object->ID );
                    if ($view_link && $user_object instanceof TP_User && $user_object->exists()) {
                        $tp_admin_bar->add_node(['id' => 'view','title' => $this->__( 'View User' ),'href' => $view_link,]);
                    }
                }
            } else {
                $current_object = $this->getTpTheQuery()->get_queried_object();
                if ( empty( $current_object)){ return;}
                if ( ! empty( $current_object->post_type ) ) {
                    $post_type_object = $this->_get_post_type_object( $current_object->post_type );
                    $edit_post_link   = $this->_get_edit_post_link( $current_object->ID );
                    if ( $post_type_object && $edit_post_link && $post_type_object->show_in_admin_bar
                        && $this->_current_user_can( 'edit_post', $current_object->ID ) ) {
                        $tp_admin_bar->add_node(['id'=> 'edit','title' => $post_type_object->labels->edit_item,'href'=> $edit_post_link,]);
                    }
                } elseif ( ! empty( $current_object->taxonomy ) ) {
                    $tax            = $this->_get_taxonomy( $current_object->taxonomy );
                    $edit_term_link = $this->_get_edit_term_link( $current_object->term_id, $current_object->taxonomy );
                    if ( $tax && $edit_term_link && $this->_current_user_can( 'edit_term', $current_object->term_id ) ) {
                        $tp_admin_bar->add_node(['id'=> 'edit','title' => $tax->labels->edit_item,'href'=> $edit_term_link,]);
                    }
                } elseif ( is_a( $current_object, 'WP_User' ) && $this->_current_user_can( 'edit_user', $current_object->ID ) ) {
                    $edit_user_link = $this->_get_edit_user_link( $current_object->ID );
                    if ( $edit_user_link ) {
                        $tp_admin_bar->add_node(['id'=> 'edit','title' => $this->__( 'Edit User' ),'href'=> $edit_user_link,]);
                    }
                }
            }
        }//753
        /**
         * @description Adds "Add New" menu.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_new_content_menu(TP_Admin_Bar $tp_admin_bar ):void{
            $actions = [];
            $cpts = (array) $this->_get_post_types( array( 'show_in_admin_bar' => true ), 'objects' );
            if ( isset( $cpts['post'] ) && $this->_current_user_can( $cpts['post']->cap->create_posts ) ) {
                $actions['post$this->_new.php'] = array( $cpts['post']->labels->name_admin_bar, 'new-post' );}
            if ( isset( $cpts['attachment'] ) && $this->_current_user_can( 'upload_files' ) ) {
                $actions['media_new.php'] = array( $cpts['attachment']->labels->name_admin_bar, 'new-media' );}
            if ( $this->_current_user_can( 'manage_links' ) ) {
                $actions['link_add.php'] = array( $this->_x( 'Link', 'add new from admin bar' ), 'new-link' );}
            if ( isset( $cpts['page'] ) && $this->_current_user_can( $cpts['page']->cap->create_posts ) ) {
                $actions['post_new.php?post_type=page'] = array( $cpts['page']->labels->name_admin_bar, 'new-page' );}
            unset( $cpts['post'], $cpts['page'], $cpts['attachment'] );
            foreach ( $cpts as $cpt ) {
                if ( ! $this->_current_user_can( $cpt->cap->create_posts )){ continue;}
                $key = 'post_new.php?post_type=' . $cpt->name;
                $actions[ $key ] = array( $cpt->labels->name_admin_bar, 'new-' . $cpt->name );
            }
            if(isset($actions['post_new.php?post_type=content'])){ $actions['post_new.php?post_type=content'][1] = 'add-new-content';}
            if ( $this->_current_user_can( 'create_users' ) || ( $this->_is_multisite() && $this->_current_user_can( 'promote_users' ) ) ) {
                $actions['user_new.php'] = array( $this->_x( 'User', 'add new from admin bar' ), 'new-user' );}
            if ( ! $actions ) { return; }
            $title = "<span class='ab-icon' aria-hidden='true'></span>";
            $title .= "<span class='ab-label'>{$this->_x( 'New', 'admin bar menu group label' )}</span>";
            $tp_admin_bar->add_node(['id' => 'new_content','title' => $title,'href' => $this->_admin_url( current( array_keys( $actions ) ) ),]);
            foreach ( $actions as $link => $action ) {
                @list( $title, $id ) = $action;
                $tp_admin_bar->add_node(['parent' => 'new_content','id' => $id,'title' => $title,'href' => $this->_admin_url( $link ),]);
            }
        }//890
        /**
         * @description Adds edit comments link with awaiting moderation count bubble.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_comments_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if (!$this->_current_user_can( 'edit_posts')){ return;}
            $awaiting_mod  = $this->_tp_count_comments();
            $awaiting_mod  = $awaiting_mod->moderated;
            $awaiting_text = sprintf($this->_n( '%s Comment in moderation', '%s Comments in moderation', $awaiting_mod ),
                $this->_number_format_i18n( $awaiting_mod ));
            $icon   = "<span class='ab-icon' aria-hidden='true'></span>";
            $title  = "<span class='ab-label awaiting-mod pending-count count-{$awaiting_mod}' aria-hidden='true'></span>";
            $title .= "<span class='screen-reader-text comments-in-moderation-text'>{$awaiting_text}</span>";
            $tp_admin_bar->add_node(['id' => 'comments','title' => $icon . $title,'href' => $this->_admin_url( 'edit_comments.php' ),]);
        }//966
        /**
         * @description Adds edit comments link with awaiting moderation count bubble.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_appearance_menu(TP_Admin_Bar $tp_admin_bar ):void{
            $tp_admin_bar->add_group(['parent' => 'site_name','id' => 'appearance',]);
            if ( $this->_current_user_can( 'switch_themes' ) ) {
                $tp_admin_bar->add_node(['parent' => 'appearance','id' => 'themes',
                    'title' => $this->__( 'Themes' ),'href' => $this->_admin_url( 'themes.php' ),]);
            }
            if ( ! $this->_current_user_can( 'edit_theme_options' ) ) { return;}
            //no widgets
            if ( $this->_current_theme_supports( 'custom-background' ) ) {
                $tp_admin_bar->add_node(['parent' => 'appearance','id' => 'background','title' => $this->__( 'Background' ),
                    'href' => $this->_admin_url( 'themes.php?page=custom-background' ),'meta' => ['class' => 'hide-if-customize',],]);
            }
            if ( $this->_current_theme_supports( 'custom-header' ) ) {
                $tp_admin_bar->add_node(['parent' => 'appearance','id' => 'header','title' => $this->__( 'Header' ),
                    'href' => $this->_admin_url( 'themes.php?page=custom-header' ),'meta' => ['class' => 'hide-if-customize',],]);
            }
        }//999
        /**
         * @description Provides an update link if theme/core updates are available.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_updates_menu(TP_Admin_Bar $tp_admin_bar ):void{
            $update_data = $this->_tp_get_update_data();
            if ( ! $update_data['counts']['total'] ) {return;}
            $updates_text = sprintf($this->_n( '%s update available', '%s updates available', $update_data['counts']['total'] ),
                $this->_number_format_i18n( $update_data['counts']['total'] ));
            $icon   = "<span class='ab-icon' aria-hidden='true'></span>";
            $title  = "<span class='ab-label' aria-hidden='true'>{$this->_number_format_i18n( $update_data['counts']['total'] )}</span>";
            $title .= "<span class='screen-reader-text updates-available-text'>{$updates_text}</span>";
            $tp_admin_bar->add_node(['id' => 'updates','title' => $icon . $title,'href'  => $this->_network_admin_url( 'update_core.php' ),]);
        }//1081
        /**
         * @description Adds search form.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_search_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if ( $this->_is_admin()){return;}
            $form  = "<form id='adminbar_search' method='get' action='{$this->_esc_url( $this->_home_url( '/' ) )}'><ul><li>";
            $form .= "<dd><input name='s' id='adminbar_search' class='adminbar-input' type='text' value='' maxlength='150'/></dd>";
            $form .= "<dt><label for='adminbar_search' class='screen-reader-text'>{$this->__('Search')}</label></dt>";
            $form .= "</li><li>";
            $form .= "<dd><input class='adminbar-button' type='submit' value='{$this->__('Search')}'/></dd>";
            $form .= "</li></ul></form>";
            $tp_admin_bar->add_node(['parent' => 'top-secondary','id' => 'search','title' => $form,
                'meta' => ['class' => 'admin-bar-search','tabindex' => -1,],]);
        }//1115
        /**
         * @description Adds a link to exit recovery mode when Recovery Mode is active.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_recovery_mode_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if (!$this->_tp_is_recovery_mode()){ return;}
            $url = $this->_tp_login_url();
            $url = $this->_add_query_arg( 'action', TP_Recovery_Mode::EXIT_ACTION, $url );
            $url = $this->_tp_nonce_url( $url, TP_Recovery_Mode::EXIT_ACTION );
            $tp_admin_bar->add_node(['parent' => 'top-secondary','id' => 'recovery-mode',
                'title' => $this->__( 'Exit Recovery Mode' ),'href' => $url,]);
        }//1146
        /**
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_add_secondary_groups(TP_Admin_Bar $tp_admin_bar ):void{
            $tp_admin_bar->add_group(['id' => 'top-secondary','meta' => ['class' => 'ab-top-secondary',],]);
            $tp_admin_bar->add_group([ 'parent' => 'tp_logo','id' => 'tp_logo_external','meta' => ['class' => 'ab-sub-secondary',],]);
        }//1172
        /**
         * @description Prints style and scripts for the admin bar.
         * @return string
         */
        protected function _tp_get_admin_bar_header():string{
            return "<style media='print'>#tp_adminbar{ display:none;}</style>";
        }
        protected function _tp_admin_bar_header():void{
            echo $this->_tp_get_admin_bar_header();
        }//1198
    }
}else die;