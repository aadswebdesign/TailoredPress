<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-6-2022
 * Time: 09:00
 */
namespace TP_Admin\Traits\AdminTemplates;
use TP_Admin\Libs\Adm_Screen;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_template_02{
        use _init_db;
        /**
         * @description Print out option HTML elements for the page templates drop-down.
         * @param string $default
         * @param string $post_type
         * @return string
         */
        protected function _get_page_template_dropdown( $default = '', $post_type = 'page' ):string{
            $templates = $this->_get_page_templates( null, $post_type );
            ksort( $templates );
            $output  = "";
            foreach ( array_keys( $templates ) as $template ) {
                $selected = $this->_get_selected( $default, $templates[ $template ] );
                $output .= "\n\t<option value='{$this->_esc_attr( $templates[ $template ] )}' $selected>{$this->_esc_html( $template )}</option>";
            }
            return $output;
        }//876
        /**
         * @description Print out option HTML elements for the page parents drop-down.
         * @param int $default
         * @param int $parent
         * @param int $level
         * @param null $post
         * @return bool|string
         */
        protected function _get_parent_dropdown( $default = 0, $parent = 0, $level = 0, $post = null ):string{
            $this->tpdb = $this->_init_db();
            $post  = $this->_get_post( $post );
            $items = $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT . " ID, post_parent, post_title FROM $this->tpdb->posts WHERE post_parent = %d AND post_type = 'page' ORDER BY menu_order", $parent ) );
            $output  = "";
            if ( $items ) {
                foreach ( $items as $item ) {
                    if ( $post && $post->ID && (int) $item->ID === $post->ID ){ continue;}
                    $pad      = str_repeat( '&nbsp;', $level * 3 );
                    $selected = $this->_get_selected( $default, $item->ID);
                    $output .= "\n\t<option class='level-$level' value='$item->ID' $selected>$pad{$this->_esc_html($item->post_title)}</option>";
                    $output .= $this->_get_parent_dropdown( $default, $item->ID, $level + 1 );
                }
            }else{
                return false;
            }
            return $output;
        }//901
        /**
         * @description Print out option HTML elements for role selectors.
         * @param string $selected
         * @return string
         */
        protected function _tp_get_dropdown_roles( $selected = '' ):string{
            $output  = "";
            $editable_roles = array_reverse((array) $this->_get_editable_roles() );
            foreach ($editable_roles as $role => $details ){
                $name = null;//todo testing
                if ( $selected === $role ) {
                    $name = (string)$this->_translate_user_role( $details['name'] );
                    $output .= "\n\t<option selected='selected' value='{$this->_esc_attr( $role )}'>$name</option>";
                }else{ $output .= "\n\t<option value='{$this->_esc_attr( $role )}'>$name</option>";}
            }
            return $output;
        }//932
        /**
         * @description Outputs the form used by the importers to accept the data to be imported
         * @param $action
         * @return string
         */
        protected function _tp_get_import_upload_form( $action ):string{
            $bytes      = $this->_apply_filters( 'import_upload_size_limit', $this->_tp_max_upload_size() );
            $size       = $this->_size_format( $bytes );
            $upload_dir = $this->_tp_upload_dir();
            $output  = "";
            if ( ! empty( $upload_dir['error'] ) ){
                $output .= "<div class='error'><p>{$this->__('Before you can upload your import file, you will need to fix the following error(s):')}</p>";
                $output .= "<p><strong>{$upload_dir['error']}</strong></p></div>";
            }else{
                $output .= "<form enctype='multipart/form-data' method='post' action='{$this->_esc_url($this->_tp_nonce_url( $action, 'import-upload' ))}' id='import_upload_form' class='tp-upload-form'><ul><li><dt>";
                $output .= sprintf("<label for='upload'>%s</label>(%s)",$this->__('Choose a file from your computer:'),sprintf($this->__('Maximum size: %s'), $size));
                $output .= "</dt><dd><input name='import' id='upload' type='file' size='25'/></dd>";
                $output .= "</li><li>";
                $output .= "<input name='action' type='hidden' value='save'/>";
                $output .= "<input name='max_file_size' type='hidden' value='$bytes'/>";
                $output .= "</li><li>";
                $output .= $this->_get_submit_button( $this->__( 'Upload file and import' ), 'primary' );
                $output .= "</li></ul></form>";
            }
            return $output;
        }//957
        /**
         * @description Adds a meta box to one or more screens.
         * @param $id
         * @param $title
         * @param $callback
         * @param null $screen
         * @param string $context
         * @param string $priority
         * @param null $callback_args
         * @return mixed
         */
        protected function _get_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args =null ){
            if ( empty( $screen ) ) { $screen = $this->_get_current_screen();
            } elseif ( is_string( $screen ) ) { $screen = $this->_convert_to_screen( $screen );
            } elseif ( is_array( $screen ) ) {
                foreach ( $screen as $single_screen ) {
                    $this->_get_meta_box( $id, $title, $callback, $single_screen, $context, $priority, $callback_args );
                }
            }
            //if (!isset( $screen->id ) ) { return false;}//todo  add !
            $page = $screen->id;
            if ( ! isset( $this->tp_meta_boxes ) ) { $this->tp_meta_boxes = [];}
            if ( ! isset( $this->tp_meta_boxes[ $page ] ) ) { $this->tp_meta_boxes[ $page ] = [];}
            if ( ! isset( $this->tp_meta_boxes[ $page ][ $context ] ) ) { $this->tp_meta_boxes[ $page ][ $context ] =[];}
            foreach ( array_keys( $this->tp_meta_boxes[ $page ] ) as $a_context ) {
                foreach ( array( 'high', 'core', 'default', 'low' ) as $a_priority ) {
                    if ( ! isset( $this->tp_meta_boxes[ $page ][ $a_context ][ $a_priority ][ $id ] ) ) {continue;}
                    // If a core box was previously removed, don't add.
                    if ( ( 'core' === $priority || 'sorted' === $priority )
                        && false === $this->tp_meta_boxes[ $page ][ $a_context ][ $a_priority ][ $id ]
                    ) { return; }
                    if ( 'core' === $priority ) {
                        if ( 'default' === $a_priority ) {
                            $this->tp_meta_boxes[ $page ][ $a_context ]['core'][ $id ] = $this->tp_meta_boxes[ $page ][ $a_context ]['default'][ $id ];
                            unset( $this->tp_meta_boxes[ $page ][ $a_context ]['default'][ $id ] );
                        }
                        return;
                    }
                    if ( empty( $priority ) ) {
                        $priority = $a_priority;
                    } elseif ( 'sorted' === $priority ) {
                        $title         = $this->tp_meta_boxes[ $page ][ $a_context ][ $a_priority ][ $id ]['title'];
                        $callback      = $this->tp_meta_boxes[ $page ][ $a_context ][ $a_priority ][ $id ]['callback'];
                        $callback_args = $this->tp_meta_boxes[ $page ][ $a_context ][ $a_priority ][ $id ]['args'];
                    }
                    if ( $priority !== $a_priority || $context !== $a_context ) {
                        unset( $this->tp_meta_boxes[ $page ][ $a_context ][ $a_priority ][ $id ] );
                    }
                }
            }
            if ( empty( $priority ) ) { $priority = 'low';}
            if ( ! isset( $this->tp_meta_boxes[ $page ][ $context ][ $priority ] ) ) {
                $this->tp_meta_boxes[ $page ][ $context ][ $priority ] = [];
            }
            $_cb_args = null;
            if($callback_args === null){
                $_cb_args = $callback_args;
            }elseif((array)$callback_args){
                $_cb_args = implode('', $callback_args);
            }

            $this->tp_meta_boxes[ $page ][ $context ][ $priority ][ $id ] = ['id' => $id,'title' => $title, 'callback' => $callback,'args' => $_cb_args,];

            $_meta_boxes = $this->tp_meta_boxes[ $page ][ $context ][ $priority ][ $id ];
            $output  = $_meta_boxes['id'];
            $output .= $_meta_boxes['title'];
            $output .= $_meta_boxes['callback'];
            return $output;
            //return $this->tp_meta_boxes[ $page ][ $context ][ $priority ][ $id ]['callback'];
        }
        /**
         * @description Adds a meta box to one or more screens.
         * @param $id
         * @param $title
         * @param $callback
         * @param null $screen
         * @param string $context
         * @param string $priority
         * @param null $callback_args
         */
        protected function _add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ):void{
            $this->_get_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
        }//1029
        //protected function _do_block_editor_incompatible_meta_box( $object, $box ){return '';}//1137 not needed
        /**
         * @description Meta-Box template function.
         * @param $screen
         * @param $context
         * @param $object
         * @return string
         */
        protected function _get_meta_boxes( $screen, $context, $object ):string{
            static $already_sorted = false;
            if ( empty( $screen ) ) { $screen = $this->_get_current_screen();
            } elseif ( is_string( $screen ) ) { $screen = $this->_convert_to_screen( $screen ); }
            $page = $screen->id;
            $hidden = $this->_get_hidden_meta_boxes( $screen );
            $sorted = $this->_get_user_option( "meta-box-order_$page" );
            $output  = sprintf("<div id='%s_sortables' class='meta-box-sortables'>",$this->_esc_attr( $context ));//todo adding </div> somewhere
            if ( ! $already_sorted && $sorted ) {
                foreach ( $sorted as $box_context => $ids ) {
                    foreach ( explode( ',', $ids ) as $id ) {
                        if ( $id && 'dashboard_browser_nag' !== $id ) {
                            ob_start();
                            $this->_add_meta_box( $id, null, null, $screen, $box_context, 'sorted');
                            $output .= ob_get_clean();
                        }
                    }
                }
            }
            $already_sorted = true;
            if ( isset( $this->tp_meta_boxes[ $page ][ $context ] ) ) {
                foreach (['high', 'sorted', 'core', 'default', 'low'] as $priority ) {
                    if ( isset( $this->tp_meta_boxes[ $page ][ $context ][ $priority ] ) ) {
                        foreach ( (array) $this->tp_meta_boxes[ $page ][ $context ][ $priority ] as $box ) {
                            if ( false === $box || ! $box['title']){ continue;}
                            $block_compatible = true;
                            if ( is_array( $box['args'] ) ) {
                                if (isset( $box['args']['__back_compat_meta_box'] ) && $box['args']['__back_compat_meta_box'] && $screen instanceof Adm_Screen && $screen->is_block_editor() ) {
                                    continue;
                                }
                                if ( isset( $box['args']['__block_editor_compatible_meta_box'] ) ) {
                                    $block_compatible = (bool) $box['args']['__block_editor_compatible_meta_box'];
                                    unset( $box['args']['__block_editor_compatible_meta_box'] );
                                }
                                if ( ! $block_compatible && $screen->is_block_editor() ) {
                                    $box['old_callback'] = $box['callback'];
                                    $box['callback']     = 'do_block_editor_incompatible_meta_box';
                                }
                                if ( isset( $box['args']['__back_compat_meta_box'] ) ) {
                                    $block_compatible = $block_compatible || (bool) $box['args']['__back_compat_meta_box'];
                                    unset( $box['args']['__back_compat_meta_box'] );
                                }
                            }
                            $hidden_class = ( ! $screen->is_block_editor() && in_array( $box['id'], $hidden, true ) ) ? ' hide-if-js' : '';
                            $_classes_setup ="item-{$this->_postbox_classes( $box['id'], $page )} $hidden_class";
                            $_dashicons_setup = '';
                            if ( 'dashboard_php_nag' === $box['id'] ) { //todo swap dashicons for my own svg creations
                                $_dashicons_setup .= "<span class='dashicons dashicons-warning' aria-hidden='true'></span>";
                                $_dashicons_setup .= "<span class='screen-reader-text'>{$this->__('Warning:')}</span>";
                            }
                            $output .= "<div id='{$box['id']}' class='postbox $_classes_setup'>\n";
                            $output .= "<header class='postbox-header'>";
                            $output .= "<h2 class='handle'>$_dashicons_setup{$box['title']}</h2>\n";
                            if ( 'dashboard_browser_nag' !== $box['id'] ) {
                                $widget_title = $box['title']; //might not use this?
                                if ( is_array( $box['args'] ) && isset( $box['args']['__widget_basename'] ) ) {
                                    $widget_title = $box['args']['__widget_basename'];
                                    // Do not pass this parameter to the user callback function.
                                    unset( $box['args']['__widget_basename'] );
                                }
                                $button_content_higher  = "<span class='screen-reader-text'>{$this->__('Move up')}</span>";
                                $button_content_higher .= "<span class='order-higher-indicator' aria-hidden='true'></span>";
                                $button_content_lower  = "<span class='screen-reader-text'>{$this->__('Move down')}</span>";
                                $button_content_lower .= "<span class='order-lower-indicator' aria-hidden='true'></span>";
                                $_toggle_panel = sprintf($this->__('Toggle panel: %s'),$widget_title);
                                $button_content_toggle = "<span class='screen-reader-text'>$_toggle_panel</span>";
                                $button_content_toggle .= "<span class='toggle-indicator' aria-hidden='true'></span>";
                                $_move_box_up = sprintf($this->__('Move %s box up'),$widget_title);
                                $_move_box_down = sprintf($this->__('Move %s box down'),$widget_title);
                                $output .= "<div class='handle-actions hide-if-no-js'><ul><li>";
                                $output .= "<dd><button class='handle-order-higher' type='button' aria-disabled='false' aria-describedby='{$box['id']}_handle_order_higher_description'>$button_content_higher</button></dd>";
                                $output .= "</li><li>";
                                $output .= "<span id='{$box['id']}_handle_order_higher_description' class='hidden'>$_move_box_up</span>";
                                $output .= "</li><li>";
                                $output .= "<dd><button class='handle-order-lower' type='button' aria-disabled='false' aria-describedby='{$box['id']}_handle_order_lower_description'>$button_content_lower</button></dd>";
                                $output .= "</li><li>";
                                $output .= "<span id='{$box['id']}_handle_order_lower_description' class='hidden'>$_move_box_down</span>";
                                $output .= "</li><li>";
                                $output .= "<dd><button class='handlediv' type='button' aria-expanded='true'>$button_content_toggle</button></dd>";
                                $output .= "</li></ul></div>";
                            }
                            $output .= "</header>";//postbox-header
                            $output .= "<div class='inside'>\n";
                            if ( TP_DEBUG && ! isset( $_GET['meta-box-loader'] ) && ! $block_compatible && 'edit' === $screen->parent_base && ! $screen->is_block_editor() ) {
                                //left empty because Tailored Press doesn't use plugins
                            }
                            ob_start();
                            call_user_func( $box['callback'], $object, $box );
                            $output .= ob_get_clean();
                            $output .= "</div>\n</div>\n";//postbox
                        }

                    }
                }
            }
            $output .= "</div>\n";
            return $output;
        }//1253
        /**
         * @description Removes a meta box from one or more screens.
         * @param $id
         * @param $screen
         * @param $context
         */
        protected function _remove_meta_box( $id, $screen, $context ):void{
            if ( empty( $screen ) ) { $screen = $this->_get_current_screen();
            } elseif ( is_string( $screen ) ) { $screen = $this->_convert_to_screen( $screen );
            } elseif ( is_array( $screen ) ) {
                foreach ( $screen as $single_screen ) { $this->_remove_meta_box( $id, $single_screen, $context );}
            }
            if ( ! isset( $screen->id ) ) { return;}
            $page = $screen->id;
            if ( ! isset( $this->tp_meta_boxes ) ) {$this->tp_meta_boxes = [];}
            if ( ! isset( $this->tp_meta_boxes[ $page ] ) ) {$this->tp_meta_boxes[ $page ] = [];}
            if ( ! isset( $this->tp_meta_boxes[ $page ][ $context ] ) ) { $this->tp_meta_boxes[ $page ][ $context ] = [];}
            foreach ( array( 'high', 'core', 'default', 'low' ) as $priority ) {
                $this->tp_meta_boxes[ $page ][ $context ][ $priority ][ $id ] = false;
            }
        }//1427
        /**
         * @description Meta Box Accordion Template Function.
         * @param $screen
         * @param $context
         * @param $object
         * @return string
         */
        protected function _get_accordion_sections( $screen, $context, $object ):string{
            if ( empty( $screen ) ) { $screen = $this->_get_current_screen();
            } elseif ( is_string( $screen ) ) { $screen = $this->_convert_to_screen( $screen ); }
            $page = $screen->id;
            $hidden = $this->_get_hidden_meta_boxes( $screen );
            //static $i = 0;
            $first_open = false;
            ob_start();
            $this->tp_enqueue_script( 'accordion' );
            $output  = ob_get_clean();
            $output .= "<div id='side_sortables' class='accordion-container'><ul class='outer-border'>";
            if ( isset( $this->tp_meta_boxes[ $page ][ $context ] ) ) {
                foreach (['high', 'core', 'default', 'low']as $priority ) {
                    if ( isset( $this->tp_meta_boxes[ $page ][ $context ][ $priority ] ) ) {
                        foreach ( $this->tp_meta_boxes[ $page ][ $context ][ $priority ] as $box ) {
                            if(false === $box || ! $box['title']){ continue;}
                            //$i++;
                            $hidden_class = in_array( $box['id'], $hidden, true ) ? 'hide-if-js ' : '';
                            $open_class = '';
                            if ( ! $first_open && empty( $hidden_class ) ) {
                                $first_open = true;
                                $open_class = 'open ';
                            }
                            $_class_setup = "$hidden_class.$open_class item-{$this->_esc_attr($box['id'])}";
                            $_h3_span = "{$this->_esc_attr($box['title'])}<span class='screen-reader-text'>{$this->__('Press return or enter to open this section.')}</span>";
                            $output .= "<li id='{$this->_esc_attr($box['id'])}' class='control-section accordion-section $_class_setup'>";
                            $output .= "<h3 class='accordion-section-title handle' tabindex='0'>$_h3_span</h3>";
                            $output .= "<div class='accordion-section-content {$this->_postbox_classes( $box['id'], $page )}'>";
                            $output .= "<div class='inside'>";
                            $output .= call_user_func( $box['callback'], $object, $box );
                            $output .= "</div></div></li>\n";
                        }
                    }
                }
            }
            $output .= "</ul></div>";
            //$output .= $i; //might not be needed?
            return $output;
        }//1477
        /**
         * @description Add a new section to a settings page.
         * @param $id
         * @param $title
         * @param $callback
         * @param $page
         */
        protected function _add_settings_section( $id, $title, $callback, $page ):void{
            $this->tp_settings_sections[ $page ][ $id ] = ['id' => $id, 'title' => $title,'callback' => $callback,];
        }//1560
    }
}else die;