<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 20:04
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Templates\Components\print_media_view;
use TP_Core\Traits\Templates\Components\underscore_audio_view;
use TP_Core\Traits\Templates\Components\underscore_video_view;
if(ABSPATH){
    trait _media_template_01 {
        /**
         * @description  Output the markup for a audio,
         * @description . tag to be used in an Underscore template when data.model is passed.
         */
        public function tp_underscore_audio_template():void{
            echo new underscore_audio_view(); //todo let see of this is working?
        }//16 from media-template
        /**
         * @description Output the markup for a video tag,
         * @description . to be used in an Underscore template when data.model is passed.
         */
        public function tp_underscore_video_template():void{
            echo new underscore_video_view(); //todo let see of this is working?
        }//56 from media-template
        /**
         * @description Prints the templates used in the media manager.
         */
        public function tp_print_media_templates():void{
            echo new print_media_view();//todo
        }//156 from media-template
    }
}else die;