<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Setting;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\Theme\_theme_06;
if(ABSPATH){
    class TP_Customize_Custom_CSS_Setting extends TP_Customize_Setting{
        use _theme_06;
        public $type = 'custom_css';
        public $transport = 'postMessage';
        public $capability = 'edit_css';
        public $stylesheet = '';
        public function __construct( $manager, $id, $args = array() ) {
            parent::__construct( $manager, $id, $args );
            if ( 'custom_css' !== $this->_id_data['base'] ) {
                throw new \RuntimeException( 'Expected custom_css id_base.' );
            }
            if ( 1 !== count( $this->_id_data['keys'] ) || empty( $this->_id_data['keys'][0] ) ) {
                throw new \RuntimeException( 'Expected single stylesheet key.' );
            }
            $this->stylesheet = $this->_id_data['keys'][0];
        }//65
        public function preview():bool {
            if ( $this->_is_previewed ){return false;}
            $this->_is_previewed = true;
            $this->_add_filter( 'tp_get_custom_css',[$this, 'filter_previewed_tp_get_custom_css'], 9, 2 );
            return true;
        }//83
        public function filter_previewed_tp_get_custom_css( $css, $stylesheet ) {
            if ( $stylesheet === $this->stylesheet ) {
                $customized_value = $this->post_value( null );
                if(!is_null( $customized_value )){$css = $customized_value;}
            }
            return $css;
        }
        public function value() {
            if ( $this->_is_previewed ) {
                $post_value = $this->post_value( null );
                if(null !== $post_value){ return $post_value;}
            }
            $id_base = $this->_id_data['base'];
            $value   = '';
            $post    = $this->_tp_get_custom_css_post( $this->stylesheet );
            if ($post){$value = $post->post_content;}
            if (empty($value)){$value = $this->default;}
            $value = $this->_apply_filters( "customize_value_{$id_base}", $value, $this );
            return $value;
        }//124
        public function validate( $value ) {
            $css = $value;
            $validity = new TP_Error();
            if ( preg_match( '#</?\w+#', $css ) ) {
                $validity->add( 'illegal_markup', $this->__('Markup is not allowed in CSS.'));
            }
            if(!$validity->has_errors()){$validity = parent::validate( $css );}
            return $validity;
        }//160
        public function update( $value ) {
            $css = $value;
            if(empty($css)){ $css = '';}
            $r = $this->_tp_update_custom_css_post($css,['stylesheet' => $this->stylesheet,]);
            if ( $r instanceof TP_Error ) { return false;}
            $post_id = $r->ID;
            if ($this->manager instanceof TP_Theme && $this->manager->get_stylesheet() === $this->stylesheet ) {
                $this->_set_theme_mod( 'custom_css_post_id', $post_id );
            }
            return $post_id;
        }
    }
}else die;