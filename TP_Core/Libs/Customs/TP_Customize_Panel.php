<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 20:20
 */
namespace TP_Core\Libs\Customs;
if(ABSPATH){
    class TP_Customize_Panel extends Customize_Base{
        public $priority = 160;
        public $capability = 'edit_theme_options';
        public $theme_supports = '';
        public $title = '';
        public $description = '';
        public $auto_expand_sole_section = false;
        public $sections;
        public $type = 'default';
        public $active_callback = '';
        public function __construct( $manager, $id, array ...$args){
            $keys = array_keys( get_object_vars( $this ) );
            foreach ( $keys as $key ) {
                if ( isset( $args[ $key ] ) ) $this->$key = $args[ $key ];
            }
            $this->manager = $manager;
            $this->id      = $id;
            if ( empty( $this->active_callback ) )
                $this->active_callback = array( $this, 'active_callback' );
            ++self::$_instance_count;
            $this->instance_number = self::$_instance_count;
            $this->sections = []; // Users cannot customize the $sections array.
        }//156
        final public function active(){
            $panel  = $this;
            $active = call_user_func( $this->active_callback, $this );
            $active = $this->_apply_filters( 'customize_panel_active', $active, $panel );
            return $active;
        }//182
        public function active_callback():bool {
            return true;
        }//211
        public function json():array{
            $array                          = $this->_tp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type' ) );
            $array['title']                 = html_entity_decode( $this->title, ENT_QUOTES, $this->_get_bloginfo( 'charset' ) );
            $array['content']               = $this->get_content();
            $array['active']                = $this->active();
            $array['instanceNumber']        = $this->instance_number;
            $array['autoExpandSoleSection'] = $this->auto_expand_sole_section;
            return $array;
        }//220
        public function check_capabilities():bool{
            if ( $this->capability && ! $this->_current_user_can( $this->capability ) ) return false;
            if ( $this->theme_supports && ! $this->_current_theme_supports( ... (array) $this->theme_supports ) )
                return false;
            return true;
        }//239
        final public function get_content() {
            ob_start();
            $this->maybe_render();
            return trim( ob_get_clean() );
        }//258
        final public function maybe_render():void{
            if ( ! $this->check_capabilities() ) return;
            $this->_do_action( 'customize_render_panel', $this );
            $this->_do_action( "customize_render_panel_{$this->id}" );
            $this->_render();
        }//269
        protected function _render():void{}//303
        protected function _render_content():void{}//312
        public function get_print_template(){
            ob_start();
            ?>
            <script type='text/html' id='template_customize_panel_<?php echo $this->_esc_attr( $this->type ); ?>_content'>
                <?php $this->_content_template(); ?>
            </script>
            <script type='text/html' id='template_customize_panel_<?php echo $this->_esc_attr( $this->type ); ?>'>
                <?php $this->_render_template(); ?>
            </script>
            <?php
            return ob_get_clean();
        }//324
        public function print_template():void{
            echo $this->get_print_template();
        }//324
        protected function _get_render_template(){
            ob_start();
            ?>
            <li id='accordion_panel_{{ data.id }}' class='accordion-section control-section control-panel control-panel-{{ data.type }}'>
                <h3 class='accordion-section-title' tabindex='0'>
                    {{ data.title }}
                    <span class='screen-reader-text'><?php $this->_e( 'Press return or enter to open this panel' ); ?></span>
                </h3>
                <ul class='accordion-sub-container control-panel-content'></ul>
            </li>
            <?php
            return ob_get_clean();
        }//345
        protected function _render_template():void{
            echo $this->_get_render_template();
        }//345
        protected function _get_content_template():string{
            $expand = '';
            $data_description = 'data.description';
            if ( !$data_description  ) $expand='cannot-expand';
            $html = "<li class='panel-meta customize-info accordion-section {$expand}'>";
            $html .= "<button class='customize-panel-back' tabindex='-1'><span class='screen-reader-text'>{$this->__('Back')}</span></button>";
            $html .= "<div class='accordion-section-title'><span class='preview-notice'>";
            ob_start();
            printf($this->__('You are customizing %s'),"<strong class='panel-title'>{{ data.title }}</strong>");
            $html .= ob_get_clean();
            $html .= "</span>";
            ob_start();
            ?>
                <# if ( data.description ) { #>
                    <button type='button' class='customize-help-toggle dashicons dashicons-editor-help' aria-expanded='false'><span class='screen-reader-text'><?php $this->_e( 'Help' ); ?></span></button>
                <# } #>
            <?php
            $html .= ob_get_clean();
            $html .= "</div>";
            ob_start();
            ?>
                <# if ( data.description ) { #>
                    <div class='description customize-panel-description'>
                        {{{ data.description }}}
                    </div>
                <# } #>
            <?php
            $html .= ob_get_clean();
            $html .= "<div class='customize-control-notifications-container'></div>";
            $html .= "</li>";
            return $html;
        }//367
        protected function _content_template():void{
            echo $this->_get_content_template();
        }//367
    }
}else die;