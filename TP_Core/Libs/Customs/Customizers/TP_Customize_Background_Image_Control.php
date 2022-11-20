<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
if(ABSPATH){
    class TP_Customize_Background_Image_Control extends TP_Customize_Image_Control {
        public $type = 'background';
        public function __construct( $manager ) {
            parent::__construct($manager,'background_image', ['label'=> $this->__('Background Image'),'section' => 'background_image',]);
        }
        public function enqueue():void{}//44
    }
}else die;