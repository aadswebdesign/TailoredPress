<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-12-2022
 * Time: 17:15
 */
namespace TP_Libs;
if(ABSPATH){
    trait Constants{
        protected function _tp_content_constants():void{
            //dirs
            if(!defined('TP_CONTENT_DIR')) define('TP_CONTENT_DIR', ABSPATH .'TP_Content/');
            if(!defined('TP_CONTENT_ASSETS')) define('TP_CONTENT_ASSETS', TP_CONTENT_DIR .'Assets/');
            if(!defined('TP_CONTENT_THEMES')) define('TP_CONTENT_THEMES', TP_CONTENT_DIR . 'Themes/' );
            if(!defined('TP_CONTENT_LIBS')) define('TP_CONTENT_LIBS', TP_CONTENT_THEMES . 'TP_Library/' );
            if(!defined('TP_MU_CONTENT_LIBS')) define('TP_MU_CONTENT_LIBS', TP_CONTENT_LIBS . 'Multisite/' );
            if(!defined('TP_CONTENT_LANG')) define('TP_CONTENT_LANG', TP_CONTENT_ASSETS. '/Languages');
            if (!defined( 'TP_TEMPLATE_PATH' ) ) define( 'TP_TEMPLATE_PATH', $this->_get_template_directory() );
            if (!defined( 'TP_STYLESHEET_PATH' ) ) define( 'TP_STYLESHEET_PATH', $this->_get_stylesheet_directory() );
            //namespaces
            if (!defined( 'TP_NS' ) ) define('TP_NS','\\');
            if (!defined( 'TP_NS_CONTENT' ) ) define('TP_NS_CONTENT','TP_Content\\');
            if (!defined( 'TP_NS_THEMES' ) ) define('TP_NS_THEMES',TP_NS_CONTENT.'Themes\\');
            if (!defined( 'TP_NS_LIBRARY' ) ) define('TP_NS_LIBRARY',TP_NS_THEMES.'TP_Library\\');
            if (!defined( 'TP_NS_MODULES' ) ) define('TP_NS_MODULES',TP_NS_LIBRARY.'Modules\\');
            if (!defined( 'TP_NS_POST_TYPE' ) ) define('TP_NS_POST_TYPE',TP_NS_LIBRARY.'PostTypes\\');
            if (!defined( 'TP_NS_TEMPLATE' ) ) define('TP_NS_TEMPLATE',TP_NS_LIBRARY.'Templates\\');
            //todo need Theme related
            if (!defined( 'TP_NS_THEME_TEMPLATE' ) ) define('TP_NS_THEME_TEMPLATE','\\ThemeSrc\\Templates\\');
            if (!defined( 'TP_NS_THEME_POST_TYPE' ) ) define('TP_NS_THEME_POST_TYPE','\\ThemeSrc\\PostTypes\\');

            //if(!defined( 'TP_NS_STYLESHEET_PATH' ) ) define('TP_NS_CONTENT',TP_NS_CONTENT.'\\');
            //themes
            if (!defined('TP_USE_THEMES' )) define('TP_USE_THEMES', '');//todo
            if (!defined('TP_DEFAULT_THEME' ) ) define('TP_DEFAULT_THEME',TP_CONTENT_THEMES.'DefaultTheme/');
            if (!defined('TP_THEME_ASSETS' ) ) define('TP_THEME_ASSETS',TP_DEFAULT_THEME.'ThemeAssets/');
            if (!defined('TP_THEME_MEDIA' ) ) define('TP_THEME_MEDIA',TP_THEME_ASSETS.'Media/');
            if (!defined('TP_THEME_IMAGES' ) ) define('TP_THEME_IMAGES',TP_THEME_MEDIA.'Images/');
            if (!defined('TP_THEMES_LANG')) define('TP_THEMES_LANG',TP_CONTENT_THEMES.'/Languages');
            //url
            if (!defined('TP_CONTENT_URL' ) ) define('TP_CONTENT_URL',$this->_get_option('siteurl').'/TP_Content');
            if (!defined('TP_THEMES_URL' ) ) define('TP_THEMES_URL',TP_CONTENT_URL .'/Themes');
            if (!defined('TP_LIBS_URL' ) ) define('TP_LIBS_URL',TP_THEMES_URL .'/TP_Library');
            if (!defined('TP_MU_LIBS_URL' ) ) define('TP_MU_LIBS_URL',TP_LIBS_URL .'/Multisite');
        }
        protected function _tp_core_constants():void{
            //dirs
            if(!defined('TP_CONTENT_DIR')) define('TP_CONTENT_DIR', ABSPATH .'TP_Content/');
            if(!defined('TP_CONTENT_ASSETS')) define('TP_CONTENT_ASSETS', TP_CONTENT_DIR .'Assets/');
            if(!defined('TP_CORE') ) define('TP_CORE', ABSPATH . 'TP_Core/');
            if(!defined('TP_CORE_ASSETS')) define('TP_CORE_ASSETS', TP_CONTENT_ASSETS);
            if(!defined('TP_CORE_MEDIA')) define('TP_CORE_MEDIA', TP_CORE_ASSETS .'Media/');
            if(!defined('TP_CORE_IMAGES')) define('TP_CORE_IMAGES', TP_CORE_MEDIA .'Images/');
            if(!defined('TP_CORE_LANG')) define('TP_CORE_LANG', TP_CORE_ASSETS. '/Languages');
            //namespaces
            if (!defined('TP_NS_CORE')) define('TP_NS_CORE','TP_Core\\');
            if (!defined('TP_NS_CORE_LIBS')) define('TP_NS_CORE_LIBS',TP_NS_CORE .'Libs\\');
            if (!defined('TP_NS_CORE_TEMPLATES')) define('TP_NS_CORE_TEMPLATES',TP_NS_CORE .'Templates\\');
            if (!defined('TP_NS_CORE_BLOCKS_STORE')) define('TP_NS_CORE_BLOCKS_STORE',TP_NS_CORE_TEMPLATES .'BlocksStore\\');
            if (!defined('TP_NS_CORE_THEME_TEMPLATES')) define('TP_NS_CORE_THEME_TEMPLATES',TP_NS_CORE_TEMPLATES .'ThemeTemplates\\');
            if (!defined('TP_NS_CORE_UNCATEGORIZED')) define('TP_NS_CORE_UNCATEGORIZED',TP_NS_CORE_TEMPLATES .'Uncategorized\\');
        }
        protected function _http_constants(): void{
            if (!defined('TP_POST')) define('TP_POST', 'POST');
            if (!defined('TP_PATCH')) define('TP_PATCH', 'PATCH');
            if (!defined('TP_PUT')) define('TP_PUT', 'PUT');
            if (!defined('TP_GET')) define('TP_GET', 'GET');
            if (!defined('TP_EDITABLE')) define('TP_EDITABLE','POST PUT PATCH');
            if (!defined('TP_ALL_METHODS')) define('TP_ALL_METHODS','GET, POST, PUT, PATCH, DELETE');
            if (!defined('TP_HEAD')) define('TP_HEAD', 'HEAD');
            if (!defined('TP_OPTIONS')) define('TP_OPTIONS', 'OPTIONS');
            if (!defined('TP_TRACE')) define('TP_TRACE', 'TRACE');
            if (!defined('TP_BUFFER_SIZE')) define('TP_BUFFER_SIZE', 1160);
            if (!defined('OK')) define('OK' , 200);
            if (!defined('CREATED')) define('CREATED' , 201);
            if (!defined('MULTI_STATUS')) define('MULTI_STATUS' , 207);
            if (!defined('BAD_REQUEST')) define('BAD_REQUEST' , 400);
            if (!defined('UNAUTHORIZED')) define('UNAUTHORIZED' , 401);
            if (!defined('FORBIDDEN')) define('FORBIDDEN' , 403);
            if (!defined('NOT_FOUND')) define('NOT_FOUND' , 404);
            if (!defined('METHOD_NOT_ALLOWED')) define('METHOD_NOT_ALLOWED' , 405);
            if (!defined('CONFLICT')) define('CONFLICT' , 409);
            if (!defined('GONE')) define('GONE' , 410);
            if (!defined('PRECONDITION_FAILED')) define('PRECONDITION_FAILED' , 412);
            if (!defined('SERVER_PORT_SSL')) define('SERVER_PORT_SSL', 443);
            if (!defined('INTERNAL_SERVER_ERROR')) define('INTERNAL_SERVER_ERROR' , 500);
            if (!defined('NOT_IMPLEMENTED')) define('NOT_IMPLEMENTED' , 501);
            if (!defined('SERVICE_UNAVAILABLE')) define('SERVICE_UNAVAILABLE' , 503);
        }
        protected function _db_constants(): void{
            if (!defined('TP_CREATE_TABLE')) define('TP_CREATE_TABLE', "CREATE_TABLE");
            if (!defined('TP_DELETE')) define('TP_DELETE', "DELETE");
            if (!defined('TP_INSERT')) define('TP_INSERT', "INSERT");
            if (!defined('TP_SELECT')) define('TP_SELECT', "SELECT");
            if (!defined('TP_UPDATE')) define('TP_UPDATE', "UPDATE");
            if (!defined('TP_DROP_TABLE')) define('TP_DROP_TABLE', "DROP TABLE");
            if (!defined('ARRAY_A')) define('ARRAY_A', 'ARRAY_A');
            if (!defined('ARRAY_N')) define('ARRAY_N', 'ARRAY_N');
            if (!defined('EZSQL_VERSION')) define('EZSQL_VERSION', 'TP0.01');
            if (!defined('OBJECT')) define('OBJECT', 'OBJECT');
            if (!defined('OBJECT_K')) define('OBJECT_K', 'OBJECT_K');
        }
        protected function _initial_constants(): void{ //todo set this right
            if (!defined('KB_IN_BYTES')) define( 'KB_IN_BYTES', 1024 );
            if (!defined('MB_IN_BYTES')) define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
            if (!defined('GB_IN_BYTES')) define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
            if (!defined('TB_IN_BYTES')) define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );
            if (!defined('TP_START_TIMESTAMP')) define( 'TP_START_TIMESTAMP', microtime( true ) );
            $current_limit     = ini_get( 'memory_limit' );
            $current_limit_int = $this->_tp_convert_hr_to_bytes( $current_limit );
            if (!defined( 'TP_MEMORY_LIMIT')) {
                if(false === $this->_tp_is_ini_value_changeable('memory_limit')) define( 'TP_MEMORY_LIMIT', $current_limit );
                elseif ($this->_is_multisite()) define( 'TP_MEMORY_LIMIT', '64M' );
                else define( 'TP_MEMORY_LIMIT', '40M' );
            }
            if (!defined('TP_MAX_MEMORY_LIMIT')){
                if(false === $this->_tp_is_ini_value_changeable('memory_limit')) define( 'TP_MAX_MEMORY_LIMIT', $current_limit );
                elseif ( -1 === $current_limit_int || $current_limit_int > 268435456 /* = 256M */ ) define( 'TP_MAX_MEMORY_LIMIT', $current_limit );
                else define( 'TP_MAX_MEMORY_LIMIT', '256M' );
            }
            $tp_limit_int = $this->_tp_convert_hr_to_bytes( TP_MEMORY_LIMIT );
            if ( -1 !== $current_limit_int && ( -1 === $tp_limit_int || $tp_limit_int > $current_limit_int ) ) ini_set( 'memory_limit', TP_MEMORY_LIMIT );
            if(!isset( $this->tp_blog_id ) ) $this->tp_blog_id = 1;
            if (!defined('TP_DEBUG')){
                if ( 'development' === $this->_tp_get_environment_type()) define( 'TP_DEBUG', true );
                else define( 'TP_DEBUG', false );
            }
            if (!defined('TP_DEBUG_DISPLAY'))define('TP_DEBUG_DISPLAY', true );
            if (!defined('TP_DEBUG_LOG')) define( 'TP_DEBUG_LOG', false );
            if (!defined('TP_CACHE')) define( 'TP_CACHE',false );
            if (!defined('TP_FEATURE_BETTER_PASSWORDS')) define('TP_FEATURE_BETTER_PASSWORDS', true );
            if (!defined('MINUTE_IN_SECONDS')) define('MINUTE_IN_SECONDS', 60 );
            if (!defined('HOUR_IN_SECONDS')) define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
            if (!defined('DAY_IN_SECONDS')) define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
            if (!defined('WEEK_IN_SECONDS')) define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
            if (!defined('MONTH_IN_SECONDS')) define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
            if (!defined('YEAR_IN_SECONDS')) define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
            if (!defined('TP_SCRIPT_DEBUG')){
                if (!empty( $this->tp_version )) $develop_src = false !== strpos( $this->tp_version, '_src' );
                else $develop_src = false;
                define('TP_SCRIPT_DEBUG',$develop_src ); //todo $develop_src
            }
            if (!defined('KSES_CUSTOM_TAGS')) define('KSES_CUSTOM_TAGS', false );
        }
        protected function _functional_constants(): void{
            if (!defined('AUTOSAVE_INTERVAL')) {define('AUTOSAVE_INTERVAL', MINUTE_IN_SECONDS );}
            if (!defined('EMPTY_TRASH_DAYS')) { define('EMPTY_TRASH_DAYS', 30 ); }
            if (!defined('TP_POST_REVISIONS')) define('TP_POST_REVISIONS', true );
        }
        protected function _ftp_constants(): void{
            if(!defined('CRLF')) define('CRLF',"\r\n");
            if(!defined("FTP_ASCII")) define("FTP_ASCII", 0);
            if(!defined("FTP_AUTO_ASCII")) define("FTP_AUTO_ASCII", -1);
            if(!defined("FTP_BINARY")) define("FTP_BINARY", 1);
            if(!defined('FTP_FORCE')) define('FTP_FORCE', true);
            if(!defined('FTP_OS_Mac')) define('FTP_OS_Mac', 'm');
            if(!defined('FTP_OS_Unix')) define('FTP_OS_Unix', 'u');
            if(!defined('FTP_OS_Windows')) define('FTP_OS_Windows', 'w');
        }
        protected function _media_constants(): void{
            // IMAGE_TYPE_WEBP constant is only defined in PHP 7.1 or later.
            if (!defined('IMAGE_TYPE_WEBP')) define( 'IMAGE_TYPE_WEBP', 18 );
            // IMG_WEBP constant is only defined in PHP 7.0.10 or later.
            if (!defined('IMG_WEBP')) define( 'IMG_WEBP', IMAGE_TYPE_WEBP );
            if (!defined('__MEDIA_TRASH')) define( '__MEDIA_TRASH', false );
        }
        protected function _various_constants(): void{
            if (!defined('TP_XML')) define('TP_XML','<?xml ');
            if (!defined('TP_XML_END')) define('TP_XML_END','?>');
            if (!defined('TP_CDATA')) define('TP_CDATA',' <![CDATA[');
            if (!defined('TP_CDATA_END')) define('TP_CDATA_END',']] ');
            if (!defined('W3_ATOM')) define('W3_ATOM','http://www.w3.org/2005/Atom');
            if (!defined('W3_RDF')) define('W3_RDF','https://www.w3.org/1999/02/22-rdf-syntax-ns#');
            if (!defined('W3_XHTML')) define('W3_XHTML','https://www.w3.org/1999/xhtml');
            if (!defined('W3_MATHML')) define('W3_MATHML','https://www.w3.org/1998/Math/MathML');
            if (!defined('W3_SVG')) define('W3_SVG','https://www.w3.org/2000/svg');
            if (!defined('W3_XLINK')) define('W3_XLINK','https://www.w3.org/1999/xlink');
            if (!defined('W3_XML_NS')) define('W3_XML_NS','https://www.w3.org/XML/1998/namespace');
            if (!defined('W3_XMLNS')) define('W3_XMLNS','https://www.w3.org/2000/xmlns/');
            if (!defined('PURL')) define('PURL','https://purl.org/rss/1.0');
            if (!defined('PURL_DC')) define('PURL_DC','https://purl.org/dc/elements/1.1/');
            if (!defined('PURL_SY')) define('PURL_SY','https://purl.org/rss/1.0/modules/syndication/');
            if (!defined('PURL_THR')) define('PURL_THR','https://purl.org/syndication/thread/1.0');
            if (!defined('PURL_CONTENT')) define('PURL_CONTENT','https://purl.org/rss/1.0/modules/content/');
            if (!defined('PURL_SLASH')) define('PURL_SLASH','https://purl.org/rss/1.0/modules/slash/');
            if (!defined('WEB_NS_MVCB')) define('WEB_NS_MVCB','https://webns.net/mvcb/');
            if (!defined('WFW_COMMENT_API')) define('WFW_COMMENT_API','https://wellformedweb.org/CommentAPI/');
        }
        protected function _version_constants(): void{
            if (!defined('TP_VERSION')) define('TP_VERSION', '5.9.4'); //todo
            if (!defined('TP_PHP_VERSION')) define('TP_PHP_VERSION', '7.0');
            if (!defined('TP_DB_VERSION')) define('TP_DB_VERSION','51917');
            if (!defined('TINYMCE_VERSION')) define('TINYMCE_VERSION','49110-20201110');
            if (!defined('TP_REQUEST_VERSION')) define('TP_REQUEST_VERSION', '5.9.0');//todo
            if (!defined('TP_REQUIRED_PHP_VERSION')) define('TP_REQUIRED_PHP_VERSION','7.0');
            if (!defined('TP_REQUIRED_MYSQL_VERSION')) define('TP_REQUIRED_MYSQL_VERSION','7.0');
        }
        protected function _rewrite_constants(): void{
            if (!defined('EP_NONE')) define('EP_NONE', 0 );
            if (!defined('EP_PERMALINK')) define('EP_PERMALINK', 1 );
            if (!defined('EP_ATTACHMENT')) define('EP_ATTACHMENT', 2 );
            if (!defined('EP_DATE')) define('EP_DATE', 4 );
            if (!defined('EP_YEAR')) define('EP_YEAR', 8 );
            if (!defined('EP_MONTH')) define('EP_MONTH', 16 );
            if (!defined('EP_DAY')) define('EP_DAY', 32 );
            if (!defined('EP_ROOT')) define('EP_ROOT', 64 );
            if (!defined('EP_COMMENTS')) define('EP_COMMENTS', 128 );
            if (!defined('EP_SEARCH')) define('EP_SEARCH', 256);
            if (!defined('EP_CATEGORIES')) define('EP_CATEGORIES', 512 );
            if (!defined('EP_TAGS')) define('EP_TAGS', 1024 );
            if (!defined('EP_AUTHORS')) define('EP_AUTHORS', 2048 );
            if (!defined('EP_PAGES')) define('EP_PAGES', 4096);
            if (!defined('EP_ALL_ARCHIVES')) define('EP_ALL_ARCHIVES', EP_DATE | EP_YEAR | EP_MONTH | EP_DAY | EP_CATEGORIES | EP_TAGS | EP_AUTHORS );
            if (!defined('EP_ALL')) define('EP_ALL', EP_PERMALINK | EP_ATTACHMENT | EP_ROOT | EP_COMMENTS | EP_SEARCH | EP_PAGES | EP_ALL_ARCHIVES );
        }
    }
}else{die;}