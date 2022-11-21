<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-8-2022
 * Time: 22:33
 */
namespace TP_Core\Libs\JSON;
use TP_Core\Traits\Block\_block_duotone_01;
use TP_Core\Traits\Block\_blocks_editor;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Cache\_cache_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Inits\_init_theme;
use TP_Core\Traits\K_Ses\_k_ses_05;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_07;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Theme\_theme_07;
if(ABSPATH){
    class JSON_Base{
        use _block_duotone_01,_blocks_editor;
        use _cache_01, _cache_02;
        use _formats_03, _formats_08;
        use _init_theme;
        use _k_ses_05,_I10n_04;
        use _methods_10,_methods_11, _methods_12,_methods_20;
        use _post_01, _post_07, _theme_01, _theme_07;
        public const ROOT_BLOCK_SELECTOR = 'body';
        public const VALID_ORIGINS = ['default','theme','custom',];
        public const PRESETS_METADATA = [
            [
                'path' => ['color', 'palette'],'override' => ['color', 'defaultPalette'],
                'use_default_names' => false,'value_key' => 'color','css_vars' => '--tp--preset--color--$slug',
                'classes' => [
                    '.has-$slug-color' => 'color','.has-$slug-background-color' => 'background-color','.has-$slug-border-color' => 'border-color',
                ],
                'properties'=> ['color', 'background-color', 'border-color'],
            ],[
                'path' => ['color', 'gradients'],'override' => ['color', 'defaultGradients'],'use_default_names' => false,
                'value_key' => 'gradient','css_vars' => '--tp--preset--gradient--$slug','properties' => ['background'],
                'classes' => ['.has-$slug-gradient-background' => 'background'],
            ],[
                'path' => ['color', 'duo-tone'],'override' => true,'use_default_names' => false,'value_func' => 'tp_get_duo-tone_filter_property',
                'css_vars' => '--tp--preset--duo-tone--$slug','classes' => [],'properties' => ['filter'],
            ],[
                'path' => ['typography', 'fontFamilies'],
                'override' => true, 'use_default_names' => false, 'value_key' => 'fontFamily',
                'css_vars' => '--tp--preset--font-family--$slug','properties' => ['font-family'],
                'classes' => ['.has-$slug-font-family' => 'font-family'],
            ]
        ];
        public const PROPERTIES_METADATA = [
            'background' => ['color','gradient'],
            'background-color' => ['color','background'],
            'border-radius' => ['border','radius'],
            'border-top-left-radius' => ['border','radius','topLeft'],
            'border-top-right-radius' => ['border','radius','topRight'],
            'border-bottom-left-radius' => ['border','radius','bottomLeft'],
            'border-bottom-right-radius' => ['border','radius','bottomRight'],
            'border-color' => ['border','color'],
            'border-width' => ['border','width'],
            'border-style' => ['border','style'],
            'color' => ['color','text'],
            'font-family' => ['typography','fontFamily'],
            'font-size' => ['typography','fontSize'],
            'font-style' => ['typography','fontStyle'],
            'font-weight' => ['typography','fontWeight'],
            'letter-spacing' => ['typography','letterSpacing'],
            'line-height' => ['typography','lineHeight'],
            'margin' => ['spacing','margin'],
            'margin-top' => ['spacing','margin','top'],
            'margin-right' => ['spacing','margin','right'],
            'margin-bottom' => ['spacing','margin','bottom'],
            'margin-left' => ['spacing','margin','left'],
            'padding' => ['spacing','padding'],
            'padding-top' => ['spacing','padding','top'],
            'padding-right' => ['spacing','padding','right'],
            'padding-bottom' => ['spacing','padding','bottom'],
            'padding-left' => ['spacing','padding','left'],
            '--wp--style--block-gap' => ['spacing','blockGap'],
            'text-decoration' => ['typography','textDecoration'],
            'text-transform' => ['typography','textTransform'],
            'filter' => ['filter','duo-tone'],
        ];
        public const PROTECTED_PROPERTIES = ['spacing.blockGap' => array( 'spacing', 'blockGap' ),];
        public const VALID_TOP_LEVEL_KEYS = ['customTemplates','settings','styles','templateParts','version',];
        public const VALID_SETTINGS = [
            'appearanceTools' => null,
            'border' => ['color' => null,'radius' => null,'style' => null,'width' => null,],
            'color' => [
                'background' => null,'custom' => null,'customDuoTone' => null,'customGradient' => null,'defaultGradients' => null,
                'defaultPalette' => null,'duoTone' => null,'gradients' => null,'link' => null,'palette' => null,'text' => null,],
            'custom'=> null,
            'layout' => ['contentSize' => null,'wideSize' => null,],
            'spacing' => ['blockGap' => null,'margin' => null,'padding' => null,'units' => null,],
            'typography' => [
                'customFontSize' => null,'dropCap' => null,'fontFamilies' => null,'fontSizes' => null,'fontStyle' => null,
                'fontWeight' => null,'letterSpacing' => null,'lineHeight' => null,'textDecoration' => null,'textTransform' => null,],
        ];
        public const VALID_STYLES = [
            'border' => ['color' => null,'radius' => null,'style' => null,'width' => null,],
            'color' => ['background' => null,'gradient' => null,'text' => null,],
            'filter' => ['duoTone' => null,],
            'spacing'    => ['margin' => null,'padding' => null,'blockGap' => 'top',],
            'typography' => [
                'fontFamily' => null,'fontSize' => null,'fontStyle' => null,'fontWeight' => null,
                'letterSpacing' => null,'lineHeight' => null,'textDecoration' => null,'textTransform' => null,
            ],
        ];
        public const ELEMENTS = ['link' => 'a','h1' => 'h1','h2' => 'h2','h3' => 'h3','h4' => 'h4','h5' => 'h5','h6' => 'h6',];
        public const LATEST_SCHEMA = 2;
        protected $_theme_json;
        protected static $_blocks_metadata;
        protected static $_core;
        protected static $_theme;
        protected static $_theme_has_support;
        protected static $_user;
        protected static $_user_custom_post_type_id;
        protected static $_i18n_schema;

    }
}else{die;}