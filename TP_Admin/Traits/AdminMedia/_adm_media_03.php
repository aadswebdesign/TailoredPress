<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-6-2022
 * Time: 04:22
 */
namespace TP_Admin\Traits\AdminMedia;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _adm_media_03{
        /**
         * @description Output a textarea element for inputting an attachment caption.
         * @param $edit_post
         * @return string
         */
        protected function _tp_caption_input_textarea($edit_post):string {
            $name = "attachments_[{$edit_post->ID}]_[post_excerpt]";
            return "<dd><textarea name='$name' id='$name'>{$edit_post->post_excerpt}</textarea></dd>";
        }//1276
        /**
         * @description Retrieves the image attachment fields to edit form fields.
         * @param $form_fields
         * @param $post
         * @return mixed
         */
        protected function _image_attachment_fields_to_edit($form_fields, $post){
            return $form_fields[$post->ID]; //this way, otherwise '$post' makes no sense here?
        }//1292
        /**
         * @description Retrieves the single non-image attachment fields to edit form fields.
         * @param $form_fields
         * @param $post
         * @return mixed
         */
        protected function _media_single_attachment_fields_to_edit($form_fields, $post){
            unset($form_fields[$post->ID]['url'], $form_fields[$post->ID]['align'], $form_fields[$post->ID]['image-size']);
            return $form_fields; //this way, otherwise '$post' makes no sense here?
        }//1305
        /**
         * @description Retrieves the post non-image attachment fields to edit form fields.
         * @param $form_fields
         * @param $post
         * @return mixed
         */
        protected function _media_post_single_attachment_fields_to_edit($form_fields, $post){
            unset($form_fields[$post->ID]['image_url']);//this way, otherwise '$post' makes no sense here?
            return $form_fields;
        }//1319
        /**
         * @description Filters input from media_upload_form_handler() and assigns a default
         * @description . post_title from the file name if none supplied.
         * @param $post
         * @param $attachment
         * @return mixed
         */
        protected function _image_attachment_fields_to_save($post, $attachment){
            if ($attachment && (strpos($post['post_mime_type'], 'image') === 0) && trim($post['post_title']) === '') {
                $attachment_url = $post['attachment_url'] ?? $post['guid'];
                $post['post_title'] = preg_replace('/\.\w+$/', '', $this->_tp_basename($attachment_url));
                $post['errors']['post_title']['errors'][] = $this->__('Empty Title filled from filename.');
            }
            return $post;
        }//1337
        /**
         * @description Retrieves the media element HTML to send to the editor.
         * @param $html
         * @param $attachment_id
         * @param $attachment
         * @return mixed
         */
        protected function _image_media_send_to_editor($html, $attachment_id, $attachment){
            $post = $this->_get_post($attachment_id);
            if (strpos($post->post_mime_type, 'image') === 0) {
                $url = $attachment['url'];
                $align = !empty($attachment['align']) ? $attachment['align'] : 'none';
                $size = !empty($attachment['image-size']) ? $attachment['image-size'] : 'medium';
                $alt = !empty($attachment['image_alt']) ? $attachment['image_alt'] : '';
                $rel = (strpos($url, 'attachment_id') || $this->_get_attachment_link($attachment_id) === $url);
                return $this->_get_image_send_to_editor($attachment_id, $attachment['post_excerpt'], $attachment['post_title'], $align, $url, $rel, $size, $alt);
            }
            return $html;
        }//1359
        /**
         * @description Retrieves the attachment fields to edit form fields.
         * @param $post
         * @param null $errors
         * @return array
         */
        protected function _get_attachment_fields_to_edit($post, $errors = null):array{
            if (is_int($post)) {
                $post = $this->_get_post($post);
            }
            if (is_array($post)) {
                $post = new TP_Post((object)$post);
            }
            $image_url = $this->_tp_get_attachment_url($post->ID);
            $edit_post = $this->_sanitize_post($post, 'edit');
            $form_fields = [
                'post_title' => ['label' => $this->__('Title'), 'value' => $edit_post->post_title,],
                'image_alt' => [],
                'post_excerpt' => ['label' => $this->__('Caption'), 'input' => 'html',
                    'html' => $this->_tp_caption_input_textarea($edit_post),],
                'post_content' => ['label' => $this->__('Description'),
                    'value' => $edit_post->post_content, 'input' => 'textarea',],
                'url' => ['label' => $this->__('Link URL'), 'input' => 'html',
                    'html' => $this->_image_link_input_fields($post, $this->_get_option('image_default_link_type')),
                    'helps' => $this->__('Enter a link URL or click above for presets.'),],
                'menu_order' => ['label' => $this->__('Order'), 'value' => $edit_post->menu_order,],
                'image_url' => ['label' => $this->__('File URL'), 'input' => 'html',
                    'html' => "<input type='text' class='text url-field' readonly='readonly' name='attachments[$post->ID][url]' value='{$this->_esc_attr( $image_url )}' /><br />",
                    'value' => $this->_tp_get_attachment_url($post->ID),
                    'helps' => $this->__('Location of the uploaded file.'),],
            ];
            foreach ($this->_get_attachment_taxonomies($post) as $taxonomy) {
                $t = (array)$this->_get_taxonomy($taxonomy);
                if (!$t['public'] || !$t['show_ui']) {
                    continue;
                }
                if (empty($t['label'])) {
                    $t['label'] = $taxonomy;
                }
                if (empty($t['args'])) {
                    $t['args'] = [];
                }
                $terms = $this->_get_object_term_cache($post->ID, $taxonomy);
                if (false === $terms) {
                    $terms = $this->_tp_get_object_terms($post->ID, $taxonomy, $t['args']);
                }
                $values = [];
                foreach ($terms as $term) {
                    $values[] = $term->slug;
                }
                $t['value'] = implode(', ', $values);
                $form_fields[$taxonomy] = $t;
            }
            $form_fields = array_merge_recursive($form_fields, (array)$errors);
            if (strpos($post->post_mime_type, 'image') === 0) {
                $alt = $this->_get_post_meta($post->ID, '_tp_attachment_image_alt', true);
                if (empty($alt)) {
                    $alt = '';
                }
                $form_fields['post_title']['required'] = true;
                $form_fields['image_alt'] = ['value' => $alt, 'label' => $this->__('Alternative Text'),
                    'helps' => $this->__('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;'),];
                $form_fields['align'] = ['label' => $this->__('Alignment'), 'input' => 'html',
                    'html' => $this->_image_align_input_fields($post, $this->_get_option('image_default_align')),];
                $form_fields['image-size'] = $this->_image_size_input_fields($post, $this->_get_option('image_default_size', 'medium'));
            } else {
                unset($form_fields['image_alt']);
            }
            /** @param TP_Post $post */
            $form_fields = $this->_apply_filters('attachment_fields_to_edit', $form_fields, $post);
            return $form_fields;
        }//1384
        /**
         * @description Retrieve HTML for media items of post gallery.
         * @param $post_id
         * @param $errors
         * @return string
         */
        protected function _get_media_items($post_id, $errors):string{
            $attachments = [];
            if ($post_id) {
                $post = $this->_get_post($post_id);
                if ($post && 'attachment' === $post->post_type) {
                    $attachments = [$post->ID => $post];
                } else {
                    $attachments = $this->_get_children(['post_parent' => $post_id, 'post_type' => 'attachment',
                        'orderby' => 'menu_order ASC, ID', 'order' => 'DESC',]);
                }
            } else if (is_array($this->tp_the_query->posts)) {
                foreach ($this->tp_the_query->posts as $attachment) {
                    $attachments[$attachment->ID] = $attachment;
                }
            }//instead of $GLOBALS['wp_the_query']
            $output = "";
            foreach ($attachments as $id => $attachment) {
                if ('trash' === $attachment->post_status) {
                    continue;
                }
                $item = $this->_get_media_item($id, ['errors' => $errors[$id] ?? null]);
                if ($item) {
                    $output .= "\n<div id='media-item-$id' class='media-item child-of-$attachment->post_parent preloaded'><div class='progress hidden'><div class='bar'></div></div>";
                    $output .= "<div id='media-upload-error-$id' class='hidden'></div><div class='filename hidden'></div>$item\n</div>";
                }
            }
            return $output;
        }//1528
        /**
         * @description Retrieve HTML form for modifying the image attachment.
         * @param $attachment_id
         * @param null $args
         * @return string
         */
        protected function _get_media_item($attachment_id, $args = null):string{
            $thumb_url = false;
            $attachment_id = (int)$attachment_id;
            if ($attachment_id) {
                $thumb_url = $this->_tp_get_attachment_image_src($attachment_id, 'thumbnail', true);
                if ($thumb_url) {
                    $thumb_url = $thumb_url[0];
                }
            }
            $post = $this->_get_post($attachment_id);
            $current_post_id = !empty($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
            $default_args = ['send' => $current_post_id ? $this->_post_type_supports($this->_get_post_type($current_post_id), 'editor') : true,
                'errors' => null, 'delete' => true, 'toggle' => true, 'show_title' => true,];
            $parsed_args = $this->_tp_parse_args($args, $default_args);
            $parsed_args = $this->_apply_filters('get_media_item_args', $parsed_args);
            $toggle_on = $this->__('Show');
            $toggle_off = $this->__('Hide');
            $file = $this->_get_attached_file($post->ID);
            $filename = $this->_esc_html($this->_tp_basename($file));
            $title = $this->_esc_attr($post->post_title);
            $post_mime_types = $this->_get_post_mime_types();
            $keys = array_keys($this->_tp_match_mime_types(array_keys($post_mime_types), $post->post_mime_type));
            $type = reset($keys);
            $type_html = "<input type='hidden' id='type_of_$attachment_id' value='{$this->_esc_attr( $type )}' />";
            $form_fields = $this->_get_attachment_fields_to_edit($post, $parsed_args['errors']);
            if ($parsed_args['toggle']) {
                $class = empty($parsed_args['errors']) ? 'start_closed' : 'start_open';
                $toggle_links = "<a class='toggle describe-toggle-on' href='#'>$toggle_on</a>";
                $toggle_links .= "<a class='toggle describe-toggle-off' href='#'>$toggle_off</a>";
            } else {
                $class = '';
                $toggle_links = '';
            }
            $display_title = (!empty($title)) ? $title : $filename; // $title shouldn't ever be empty, but just in case.
            $display_title = $parsed_args['show_title'] ? "<div class='filename new'><span class='title'>{$this->_tp_html_excerpt( $display_title, 60, '&hellip;' )}</span></div>" : '';
            $gallery = ((isset($_REQUEST['tab']) && 'gallery' === $_REQUEST['tab']) || (isset($this->tp_redirect_tab) && 'gallery' === $this->tp_redirect_tab));
            $order = '';
            foreach ($form_fields as $key => $val) {
                if ('menu_order' === $key) {
                    if ($gallery) {
                        $order = "<div class='menu_order'> <input class='menu_order_input' type='text' id='attachments[$attachment_id][menu_order]' name='attachments[$attachment_id][menu_order]' value='" . $this->_esc_attr($val['value']) . "' /></div>";
                    } else {
                        $order = "<input type='hidden' name='attachments[$attachment_id][menu_order]' value='" . $this->_esc_attr($val['value']) . "' />";
                    }
                    unset($form_fields['menu_order']);
                    break;
                }
            }
            $media_dims = '';
            $meta = $this->_tp_get_attachment_metadata($post->ID);
            if (isset($meta['width'], $meta['height'])) {
                $media_dims .= "<span id='media-dims-$post->ID'>{$meta['width']}&nbsp;&times;&nbsp;{$meta['height']}</span> ";
            }
            /** @param TP_Post $post */
            $media_dims = $this->_apply_filters('media_meta', $media_dims, $post);
            $image_edit_button = '';
            if ($this->_tp_attachment_is_image($post->ID) && $this->_tp_image_editor_supports(array('mime_type' => $post->post_mime_type))) {
                $nonce = $this->_tp_create_nonce("image_editor-$post->ID");
                $_onclick4 = " onclick='imageEdit.open( {$post->ID}, {$nonce} )'";
                $image_edit_button = "<input type='button' id='img-edit-open-btn-$post->ID' $_onclick4 class='button' value='{$this->_esc_attr__( 'Edit Image' )}'/> <span class='spinner'></span>";
            }
            $attachment_url = $this->_get_permalink($attachment_id);
            $output = $type_html . $toggle_links . $order . $display_title;
            ob_start(); //todo integrate this into a external style css
            ?>
            <style>
                * {
                    padding: 0;
                    margin: 0;
                    list-style-type: none;
                }

                .relative {
                    position: relative;
                    top: 0;
                    right: 0;
                    bottom;
                    0;
                    left: 0;
                }

                .tp-flex {
                    display: flex;
                    flex-wrap: wrap;
                }

                .container {
                    width: 90%;
                    top: 1.0em;
                    left: 5%;
                    background: #feffe9
                }

                .container header {
                    flex-wrap: nowrap;
                    width: 100%;
                }

                .container header li {
                    padding: 0.2em;
                }

                header .block {
                    border: 1px solid lightgray;
                }

                header .block.left {
                    width: 40%;
                }

                header .block.right {
                    width: 60%;
                }

                div.content-container {
                    width: 100%
                }

                div.content-container ul li {
                    width: 100%;
                }

                div.content-item {
                    width: inherit;
                    border: 1px solid lightgray;
                }
            </style>
            <?php
            $output .= ob_get_clean();
            $output .= "<div class='tp-flex container relative slidetoggle describe $class'>";
            $output .= "<header id='media_head_$post->ID' class='tp-flex  media-item-info relative'>";
            $output .= "<div class='block left relative'><ul><li class='a1-b1' id='thumbnail_head_{$post->ID}'>";
            $output .= "<dd><a href='$attachment_url' target='_blank'><img class='thumb-nail' src='$thumb_url' alt='' /></a></dd>";
            $output .= "<dd>$image_edit_button</dd>";
            $output .= "</li></ul></div><div class='block right relative'><ul><li>";
            $output .= "<p><strong>{$this->__( 'File name: ' )}</strong> $filename</p>";
            $output .= "</li><li>";
            $output .= "<p><strong>{$this->__( 'File type: ' )}</strong> {$post->post_mime_type}</p>";
            $output .= "</li><li>";
            $output .= "<p><strong>{$this->__( 'Upload date: ' )}</strong> {$this->_mysql2date( $this->__( 'F j, Y' ), $post->post_date )}</p>";
            if (empty($media_dims)) {
                $output .= "</li><li><p><strong>{$this->__( 'Dimensions:' )}</strong> $media_dims</p>\n";
            }
            $output .= "</li></ul></div>\n";
            $output .= "</header><div class='content-container relative'><ul class='tp-flex'><li>";
            $output .= "<div class='content-item img-edit-response' id='img_edit_response_{$post->ID}'>cell one a</div></li>\n";
            $output .= "<li><div style='display:block;' class='content-item image-editor' id='image_editor_{$post->ID}'>cell two a</div></li>\n";
            $output .= "<li><div class='content-item'>cell three a</div></li>\n";
            $output .= "<li><div class='content-item'><p class='media-types media-types-required-info'>";
            $output .= sprintf($this->__('Required fields are marked %s'), "<span class='required'>*</span>");
            $output .= "</p></div></li>\n";
            $defaults = ['input' => 'text', 'required' => false, 'value' => '', 'extra_rows' => [],];
            if ($parsed_args['send']) {
                $parsed_args['send'] = $this->_get_submit_button($this->__(TP_INSERT . ' into Post'), '', "send[$attachment_id]", false);
            }
            $delete = empty($parsed_args['delete']) ? '' : $parsed_args['delete'];
            if ($delete && $this->_current_user_can('delete_post', $attachment_id)) {
                if (!EMPTY_TRASH_DAYS) {
                    $delete = "<a href='{$this->_tp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete_post_' . $attachment_id )}' id='del[$attachment_id]' class='delete-permanently'>{$this->__( 'Delete Permanently' )}</a>";
                } elseif (!__MEDIA_TRASH) {
                    $_onclick1 = "document.getElementById('del_attachment_$attachment_id').style.display='block';return false;";
                    $_onclick2 = "this.parentNode.style.display='none';return false;";
                    $delete = "<a href='#' class='del-link' onclick='" . $_onclick1 . "'>{$this->__( 'Delete' )}</a>";
                    $delete .= "<div id='del_attachment_$attachment_id' class='del-attachment' style='display:none;'><p>";
                    $delete .= sprintf($this->__('You are about to delete %s.') . "<strong>$filename</strong>");
                    $delete .= "</p><a href='{$this->_tp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-post_' . $attachment_id )}' id='del[$attachment_id]' class='button'>{$this->__( 'Continue' )}</a>";
                    $delete .= "<a href='#' onclick='" . $_onclick2 . "' class='button'>{$this->__( 'Cancel' )}</a></div>";
                } else {
                    $delete = "<a href='{$this->_tp_nonce_url( "post.php?action=trash&amp;post=$attachment_id", 'trash_post_' . $attachment_id )}' id='del[$attachment_id]' class='delete'>{$this->__( 'Move to Trash' )}</a>";
                    $delete .= "<a href='{$this->_tp_nonce_url( "post.php?action=untrash&amp;post=$attachment_id", 'untrash_post_' . $attachment_id )}' id='undo[$attachment_id]' class='undo hidden'>{$this->__( 'Undo' )}</a>";
                }
            } else {
                $delete = '';
            }
            $thumbnail = '';
            $calling_post_id = 0;
            if (isset($_GET['post_id'])) {
                $calling_post_id = $this->_abs_int($_GET['post_id']);
            } elseif (isset($_POST) && count($_POST)) {
                $calling_post_id = $post->post_parent;
            }
            if ('image' === $type && $calling_post_id && $this->_get_post_thumbnail_id($calling_post_id) !== $attachment_id
                && $this->_current_theme_supports('post-thumbnails', $this->_get_post_type($calling_post_id))
                && $this->_post_type_supports($this->_get_post_type($calling_post_id), 'thumbnail')
            ) {
                $calling_post = $this->_get_post($calling_post_id);
                $calling_post_type_object = $this->_get_post_type_object($calling_post->post_type);
                $async_nonce = $this->_tp_create_nonce("set_post_thumbnail-$calling_post_id");
                $_onclick3 = "TPSetAsThumbnail(\"$attachment_id\", \"$async_nonce\");return false;";
                $thumbnail = "<a class='tp-post-thumbnail' id='tp_post_thumbnail_{$attachment_id}' href='#' onclick='" . $_onclick3 . "'>{$this->_esc_html( $calling_post_type_object->labels->use_featured_image )}</a>";
            }
            if (($parsed_args['send'] || $thumbnail || $delete) && !isset($form_fields['buttons'])) {
                $form_fields['buttons'] = ['t_row' => "\t\t<li><div class='content-item'></div></li><li><div class='content-item save-send'>{$parsed_args['send']} $thumbnail $delete</div></li>\n"];
            }
            $hidden_fields = [];
            foreach ($form_fields as $id => $field) {
                if ('_' === $id[0]) {
                    continue;
                }
                if (!empty($field['t_row'])) {
                    $output .= $field['t_row'];
                    continue;
                }
                $field = $this->_tp_array_merge($defaults, $field);
                $name = "attachments[$attachment_id][$id]";
                if ('hidden' === $field['input']) {
                    $hidden_fields[$name] = $field['value'];
                    continue;
                }
                $required = $field['required'] ? "<span class='' required>*</span>" : '';
                $required_attr = $field['required'] ? ' required' : '';
                $class = $id;
                $class .= $field['required'] ? ' form-required' : '';
                $output .= "\t\t<li class='$class'><dt class='content-item label'><label for='$name'><span class='flex-left'>{$field['label']}{$required}</span></label></dt></li>\n\t\t\t";
                $output .= "<li><div class='content-item field'>";
                if (!empty($field[$field['input']])) {
                    $output .= $field[$field['input']];
                } elseif ('textarea' === $field['input']) {
                    if ('post_content' === $id && $this->_user_can_rich_edit()) {
                        $field['value'] = htmlspecialchars($field['value'], ENT_QUOTES);
                    }
                    $output .= "<textarea id='$name' name='$name' {$required_attr}>{$field['value']}</textarea>";
                } else {
                    $output .= "<input name='$name' id='$name' type='text' class='text' value='{$this->_esc_attr($field['value'])}' $required_attr />";
                }
                if (!empty($field['helps'])) {
                    $_help = implode("</p>\n<p class='help'>", array_unique((array)$field['helps']));
                    $output .= "<p class='help'>$_help</p>";
                }
                $output .= "</div>\n\t\t</li>\n";// end field
                $extra_rows = [];
                if (!empty($field['errors'])) {
                    foreach (array_unique((array)$field['errors']) as $error) {
                        $extra_rows['error'][] = $error;
                    }
                }
                if (!empty($field['extra_rows'])) {
                    foreach ($field['extra_rows'] as $class => $rows) {
                        foreach ((array)$rows as $html) {
                            $extra_rows[$class][] = $html;
                        }
                    }
                }
                foreach ($extra_rows as $class => $rows) {
                    foreach ($rows as $html) {
                        $output .= "\t\t<li><div class='content-item'></div><li></li><div class='content-item $class'>$html</div></li> \n";
                    }
                }
            }
            if (!empty($form_fields['_final'])) {
                $output .= "\t\t<li><div class='content-item'>{$form_fields['_final']}</div></li>\n";
            }
            $output .= "</ul>\t</div>\n";
            $output .= "<div class='content-container extended relative'><ul class='tp-flex'>";
            foreach ($hidden_fields as $name => $value) {
                $output .= "<li><input name='$name' id='$name' type='hidden' value='{$this->_esc_attr($value)}' /></li>\n";
            }
            if ($post->post_parent < 1 && isset($_REQUEST['post_id'])) {
                $parent = (int)$_REQUEST['post_id'];
                $parent_name = "attachments[$attachment_id][post_parent]";
                $output .= "\t<li><input name='$parent_name' id='$parent_name' type='hidden' value='$parent'/></li>\n";
            }
            $output .= "</ul>\t</div>\n";
            $output .= "\t</div>\n";//tp-flex container
            return $output;
        }//1581
        /**
         * @param $attachment_id
         * @param null $args
         * @return array
         */
        protected function _get_compat_media_markup($attachment_id, $args = null):array{
            $post = $this->_get_post($attachment_id);
            $default_args = ['errors' => null, 'in_modal' => false,];
            $user_can_edit = $this->_current_user_can('edit_post', $attachment_id);
            $args = $this->_tp_parse_args($args, $default_args);
            $args = $this->_apply_filters('get_media_item_args', $args);
            $form_fields = [];
            if ($args['in_modal']) {
                foreach ($this->_get_attachment_taxonomies($post) as $taxonomy) {
                    $t = (array)$this->_get_taxonomy($taxonomy);
                    if (!$t['public'] || !$t['show_ui']) {
                        continue;
                    }
                    if (empty($t['label'])) {
                        $t['label'] = $taxonomy ?: 'test_label';//temp
                    }
                    if (empty($t['args'])) {
                        $t['args'] = [];
                    }
                    $terms = $this->_get_object_term_cache($post->ID, $taxonomy);
                    if (false === $terms) {
                        $terms = $this->_tp_get_object_terms($post->ID, $taxonomy, $t['args']);
                    }
                    $values = [];
                    foreach ($terms as $term) {
                        $values[] = $term->slug;
                    }
                    $t['value'] = implode(', ', $values);
                    $t['taxonomy'] = true;
                    $form_fields[$taxonomy] = $t;
                }
            }
            $form_fields = array_merge_recursive($form_fields, (array)$args['errors']);
            $form_fields = $this->_apply_filters('attachment_fields_to_edit', $form_fields, $post);
            unset($form_fields['image-size'], $form_fields['align'], $form_fields['image_alt'], $form_fields['post_title'],
                $form_fields['post_excerpt'], $form_fields['post_content'], $form_fields['url'], $form_fields['menu_order'], $form_fields['image_url']);
            $media_meta = $this->_apply_filters('media_meta', '', $post);
            $defaults = ['input' => 'text', 'required' => false, 'value' => '',
                'extra_rows' => [], 'show_in_edit' => true, 'show_in_modal' => true,];
            //$hidden_fields = []; never used
            $output = "";
            ob_start();
            ?>
            <style>
                * {
                    padding: 0;
                    margin: 0;
                    list-style-type: none;
                }

                .relative {
                    position: relative;
                    top: 0;
                    right: 0;
                    bottom;
                    0;
                    left: 0;
                }

                .tp-flex {
                    display: flex;
                    flex-wrap: wrap;
                }

                .content-item{
                    background: #fffaf1;
                }
            </style>
            <?php
            $output .= ob_get_clean();
            foreach ( $form_fields as $id => $field ) {
                if ( '_' === $id[0]){continue;}
                $name    = "attachments[$attachment_id][$id]";
                $id_attr = "attachments_{$attachment_id}_{$id}";
                if ( ! empty( $field['li'] ) ) {
                    $output .= $field['li'];
                    continue;
                }
                $field = $this->_tp_array_merge( $defaults, $field );
                if ( ( ! $field['show_in_edit'] && ! $args['in_modal'] ) || ( ! $field['show_in_modal'] && $args['in_modal'] ) ) {
                    continue;
                }
                //if ( 'hidden' === $field['input'] ) {
                    //$hidden_fields[ $name ] = $field['value'];
                    //continue;
                //}never used
                $readonly      = ! $user_can_edit && ! empty( $field['taxonomy'] ) ? " readonly='readonly' " : '';
                $required      = $field['required'] ? "<span class='required'>*</span>" : '';
                $required_attr = $field['required'] ? ' required' : '';
                $class         = 'compat-field-' . $id;
                $class        .= $field['required'] ? ' form-required' : '';
                $output .= "\t\t<li class='content-item $class'>";
                $output .= "\t\t\t<dt class='label'><label for='$id_attr'><span class='flex-left'>{$field['label']}</span>$required</label></dt>";
                $output .= "\n\t\t\t<dd class='field'>";
                if ( ! empty( $field[ $field['input'] ] ) ) {
                    $output .= $field[ $field['input'] ];
                }elseif ( 'textarea' === $field['input'] ) {
                    if ( 'post_content' === $id && $this->_user_can_rich_edit() ){
                        $field['value'] = htmlspecialchars( $field['value'], ENT_QUOTES );
                    }
                    $output .= "<textarea id='$id_attr' name='$name' {$required_attr}>{$field['value']}</textarea>";
                }else{
                    $output .= "<input type='text' class='text' id='$id_attr' name='$name' value='{$this->_esc_attr( $field['value'] )}' $readonly{$required_attr} />";
                }
                if(! empty($field['helps'])){
                    $_help = implode( "</p>\n<p class='help'>", array_unique( (array) $field['helps'] ) );
                    $output .= "<p class='help'>$_help</p>";
                }
                $output .= "</dd>\n\t\t</li>\n";
                $extra_rows = [];
                if ( ! empty( $field['errors'] ) ) {
                    foreach ( array_unique( (array) $field['errors'] ) as $error ) {$extra_rows['error'][] = $error;}
                }
                if ( ! empty( $field['extra_rows'] ) ) {
                    foreach ( $field['extra_rows'] as $class => $rows ) {
                        foreach ( (array) $rows as $html ) {$extra_rows[ $class ][] = $html;}
                    }
                }
                foreach ( $extra_rows as $class => $rows ) {
                    foreach ( $rows as $html ) {
                        $output .= "\t\t<li>$html</li>\n";
                    }
                }
            }
            if (empty(!$form_fields['_final'] ) ) {
                $output .= "\t\t<li class='final'><dd >{$form_fields['_final']}</dd></li>\n";
            }
            $end_output = '';
            if ( $output ) {
                $end_output .= "<ul class='compat-attachment-fields'><li><p class='media-types media-types-required-info'>";
                $end_output .= sprintf($this->__('Required fields are marked %s'),"<span class='required'>*</span>");
                $end_output .= "</p></li>";
                $end_output .= "$output</ul>";
            }
            return ['item' => $end_output,'meta' => $media_meta];
        }
    }
}else die;