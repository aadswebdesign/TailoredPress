<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 08:43
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
if(ABSPATH){
    final class TP_Block_Editor_Context{
        public $be_post;
        public function __construct( array $settings = array() ) {
            if ( isset( $settings['post'] ) ) $this->be_post = $settings['post'];
        }
    }
}else die;