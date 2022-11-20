<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-8-2022
 * Time: 08:25
 */
namespace TP_Core\Libs\Embed;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Embed\_embed_01;
use TP_Core\Traits\Embed\_embed_02;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Inits\_init_embed;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Inits\_init_shortcode_tags;
use TP_Core\Traits\K_Ses\_k_ses_05;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Options\_option_03;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Post\_post_07;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\RestApi\_rest_api_01;
use TP_Core\Traits\RestApi\_rest_api_04;
use TP_Core\Traits\ShortCode\_short_code_01;
use TP_Core\Traits\ShortCode\_short_code_02;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Templates\_link_template_09;
if(ABSPATH){
    class Embed_Base{
        use _filter_01, _action_01, _short_code_01;
        use _cache_01,_methods_03,_methods_04, _methods_10, _k_ses_05, _formats_01;
        use _formats_07, _formats_08, _formats_11, _post_01, _post_02, _I10n_01;
        use _post_03, _post_05, _post_07, _embed_01,_embed_02,_init_error,_capability_01;
        use _http_01, _http_02,_short_code_02,_link_template_09,_rewrite;
        use _init_embed,_init_shortcode_tags, _init_assets,_option_01,_option_02,_option_03;
        use _rest_api_01,_rest_api_04;
        public $handlers = array();
        public $post_ID;
        public $use_cache      = true;
        public $link_if_unknown = true;
        public $last_attr     = [];
        public $last_url      = '';
        public $return_false_on_fail = false;
        public $providers = [];
        public static $early_providers = [];

    }
}else{die;}