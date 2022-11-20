<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-8-2022
 * Time: 20:17
 */
namespace TP_Core\Libs\Editor;
use TP_Admin\Traits\AdminMedia\_adm_media_01;
use TP_Admin\Traits\AdminMedia\_adm_media_02;
use TP_Admin\Traits\AdminMedia\_adm_media_03;
use TP_Admin\Traits\AdminMedia\_adm_media_04;
use TP_Admin\Traits\AdminMedia\_adm_media_05;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Block\_blocks_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_07;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Misc\tp_link_styles;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
use TP_Core\Traits\User\_user_05;

if(ABSPATH){
    class Editor_base{
        use _filter_01, _action_01, _option_01,_option_02,_formats_02,_formats_04, _formats_08,_formats_10;
        use _methods_01, _methods_05,_methods_06, _methods_07, _methods_08,tp_link_styles,tp_script;
        use _methods_10, _methods_12,_methods_16, _methods_17, _blocks_01, _I10n_01, _link_template_01;
        use _post_template_01, _general_template_08, _post_01, _post_03,_capability_01,_user_02,_user_03,_user_05;
        use _init_error,_adm_media_01,_adm_media_02,_adm_media_03,_adm_media_04,_adm_media_05,_pluggable_01;
        use _I10n_04;
        //much more to do here but not the direction I wanna go! todo for later!
        public static $mce_locale;
        protected static $_mce_settings = [];
        protected static $_qt_settings  = [];
        protected static $_plugins  = [];
        protected static $_qt_buttons = [];
        protected static $_ext_plugins;
        protected static $_baseurl;
        protected static $_first_init;
        protected static $_this_tinymce = false;
        protected static $_this_quicktags = false;
        protected static $_has_tinymce = false;
        protected static $_has_quicktags = false;
        protected static $_has_medialib = false;
        protected static $_editor_buttons_css = true;
        protected static $_drag_drop_upload = false;
        protected static $_editor_translation;
        protected static $_tinymce_scripts_printed = false;
        protected static $_link_dialog_printed  = false;
        protected $_tinymce_version;//todo
        protected $_file;
        protected $_size;
        protected $_mime_type;
        protected $_output_mime_type;
        protected $_default_mime_type = 'image/jpeg';
        protected $_quality           = false;
        protected static $_html;
    }
}else{die;}