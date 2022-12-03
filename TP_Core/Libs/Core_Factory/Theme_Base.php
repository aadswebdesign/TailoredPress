<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-3-2022
 * Time: 19:57
 */
namespace TP_Core\Libs\Core_Factory;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Templates\_block_utils_template_02;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Cache\_cache_02;
use TP_Core\Traits\Misc\_error_protection;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Methods\_methods_14;
use TP_Core\Traits\K_Ses\_k_ses_01;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Multisite\Blog\_ms_blog_02;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Theme\_theme_02;
use TP_Core\Traits\Theme\_theme_07;
if(ABSPATH){
    class Theme_Base {
        use _block_utils_template_02, _cache_01,_cache_02;
        use _error_protection, _filter_01,_methods_14,_methods_20,_load_04;
        use _formats_01, _formats_02, _formats_04, _formats_07, _formats_08;
        use _init_error, _k_ses_01, _ms_blog_02, _option_01;
        use _I10n_01,_I10n_02,_I10n_03,_I10n_04;
        use _post_01, _post_02, _post_03;
        use _theme_01,_theme_02, _theme_07;
        protected static $_cache_expiration = 1800;
        protected static $_default_themes = [
            'classic'         => 'TailoredPress Classic',
            'default'         => 'TailoredPress Default',
            //todo sort this out!
        ];
        protected static $_file_headers = [
            'Name'        => 'Theme Name',
            'ThemeURI'    => 'Theme URI',
            'Description' => 'Description',
            'Author'      => 'Author',
            'AuthorURI'   => 'Author URI',
            'Version'     => 'Version',
            'Template'    => 'Template',
            'Status'      => 'Status',
            'Tags'        => 'Tags',
            'TextDomain'  => 'Text Domain',
            'DomainPath'  => 'Domain Path',
            'RequiresTP'  => 'Requires at least',
            'RequiresPHP' => 'Requires PHP',
        ];
        protected static $_keys = [
            'Name','Version','Status','Title','Author','Author Name','Author URI',
            'Description','Template','Stylesheet','Template Files','Stylesheet Files','Template Dir',
            'Stylesheet Dir','Screenshot','Tags','Theme Root','Theme Root URI','Parent Theme',
        ];
        protected static $_persistently_cache;
        protected static $_properties = [
            'name','title','version','parent_theme','template_dir','stylesheet_dir',
            'template','stylesheet','screenshot','description','author','tags',
            'theme_root','theme_root_uri',
        ];
        protected static $_tag_map = [
            'fixed-width'    => 'fixed-layout',
            'flexible-width' => 'fluid-layout',
        ];
        protected $_cache_hash;
        protected $_errors;
        protected $_headers = [];
        protected $_headers_sanitized;
        protected $_name_translated;
        protected $_parent;
        protected $_set_parent;
        protected $_stylesheet;
        protected $_template;
        protected $_textdomain_loaded;
        protected $_theme;
        protected $_theme_root;
        protected $_theme_root_uri;

        //global likes
        protected $_tp_theme_directories;




        public $update = false;

    }
}else die;