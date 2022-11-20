<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Control;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Media\_media_07;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Post\_post_11;
if(ABSPATH){
    class TP_Customize_Media_Control extends TP_Customize_Control {
        use _methods_10,_media_07,_post_11,_formats_10;
        public $type = 'media';
        public $mime_type = '';
        public $button_labels = [];
        public function __construct( $manager, $id, $args = array() ) {
            parent::__construct( $manager, $id, $args );
            $this->button_labels = $this->_tp_parse_args( $this->button_labels, $this->get_default_button_labels() );
        }
        public function enqueue():void {
            $this->_tp_enqueue_media();
        }
        public function to_json():void {
            parent::to_json();
            $this->json['label'] = html_entity_decode( $this->label, ENT_QUOTES, $this->_get_bloginfo( 'charset' ) );
            $this->json['mime_type']= $this->mime_type;
            $this->json['button_labels'] = $this->button_labels;
            $this->json['canUpload']= $this->_current_user_can( 'upload_files' );
            $value = $this->value();
            if ( is_object( $this->setting ) ) {
                if ( $this->setting->default ) {
                    // Fake an attachment model - needs all fields used by template.
                    // Note that the default value must be a URL, NOT an attachment ID.
                    $ext  = substr( $this->setting->default, -3 );
                    $type = in_array( $ext, array( 'jpg', 'png', 'gif', 'bmp', 'webp' ), true ) ? 'image' : 'document';
                    $default_attachment = array(
                        'id'    => 1,
                        'url'   => $this->setting->default,
                        'type'  => $type,
                        'icon'  => $this->_tp_mime_type_icon( $type ),
                        'title' => $this->_tp_basename( $this->setting->default ),
                    );
                    if ( 'image' === $type ) {
                        $default_attachment['sizes'] = array(
                            'full' => array( 'url' => $this->setting->default ),
                        );
                    }
                    $this->json['defaultAttachment'] = $default_attachment;
                }
                if ( $value && $this->setting->default && $value === $this->setting->default ) {
                    // Set the default as the attachment.
                    $this->json['attachment'] = $this->json['defaultAttachment'];
                } elseif ( $value ) {
                    $this->json['attachment'] = $this->_tp_prepare_attachment_for_js( $value );
                }
            }
        }
        public function render_content():void {}
        protected function _get_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//138
        public function get_default_button_labels():string{
            //todo
        }//218
    }
}else die;