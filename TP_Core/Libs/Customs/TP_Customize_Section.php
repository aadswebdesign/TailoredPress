<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 20:20
 */
namespace TP_Core\Libs\Customs;
if(ABSPATH){
    class TP_Customize_Section extends Customize_Base{
        public $priority = 160;
        public $panel = '';
        public $capability = 'edit_theme_options';
        public $theme_supports = '';
        public $title = '';
        public $description = '';
        public $controls;
        public $type = 'default';
        public $active_callback = '';
        public $description_hidden = false;
        public function __construct( $manager, $id, array ...$args){}//172
        final public function active():bool{
            $section = $this;
            $active  = call_user_func( $this->active_callback, $this );
            $active = $this->_apply_filters( 'customize_section_active', $active, $section );
            return $active;
        }//198
        public function active_callback():bool {
            return true;
        }//225
        public function json():void{}//236
        final public function check_capabilities():void{}//261
        final public function get_content(){
            ob_start();
            $this->maybe_render();
            return trim( ob_get_clean() );
        }//280
        final public function maybe_render() :void{
            if ( ! $this->check_capabilities() ) return;
            $this->_do_action( 'customize_render_section', $this );
            $this->_do_action( "customize_render_section_{$this->id}" );
            $this->_render();
        }//291
        protected function _render():void{}//324
        public function get_print_template(){
            ob_start();
            ?>
            <script type='text/html' id='template_customize_section_<?php echo $this->_esc_attr( $this->type ); ?>'>
                <?php $this->_render_template(); ?>
            </script>
            <?php
            return ob_get_clean();
        }//336
        public function print_template():void{
            echo $this->get_print_template();
        }//336
        protected function _get_render_template(){
            ob_start();
            ?>
            <li id='accordion_section_{{ data.id }}' class='accordion-section control-section control-section-{{ data.type }}'>
                <h3 class='accordion-section-title' tabindex='0'>
                    {{ data.title }}
                    <span class='screen-reader-text'><?php $this->_e( 'Press return or enter to open this section' ); ?></span>
                </h3>
                <ul class='accordion-section-content'>
                    <li class='customize-section-description-container section-meta <# if ( data.description_hidden ) { #>customize-info<# } #>'>
                        <div class='customize-section-title'>
                            <button class='customize-section-back' tabindex='-1'>
                                <span class='screen-reader-text'><?php $this->_e( 'Back' ); ?></span>
                            </button>
                            <h3>
							<span class='customize-action'>
								{{{ data.customizeAction }}}
							</span>
                                {{ data.title }}
                            </h3>
                            <# if ( data.description && data.description_hidden ) { #>
                                <button type='button' class='customize-help-toggle dashicons dashicons-editor-help' aria-expanded='false'><span class='screen-reader-text'><?php $this->_e( 'Help' ); ?></span></button>
                                <div class='description customize-section-description'>
                                    {{{ data.description }}}
                                </div>
                                <# } #>

                                    <div class='customize-control-notifications-container'></div>
                        </div>

                        <# if ( data.description && ! data.description_hidden ) { #>
                            <div class='description customize-section-description'>
                                {{{ data.description }}}
                            </div>
                            <# } #>
                    </li>
                </ul>
            </li>
            <?php
            return ob_get_clean();
        }//354
        protected function _render_template():void{
            echo $this->_get_render_template();
        }//354
    }
}else die;