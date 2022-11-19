<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 12-3-2022
 * Time: 14:04
 */
namespace TP_Core\Traits;
if(ABSPATH){
    trait _constants  {
        //if(! defined('')) define();

        protected function _error_reporting():void{ //todo lookup load
            error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
        }
        protected function _admin_constants():void{
            //dirs
            if(! defined('TP_ADMIN_DIR')) define('TP_ADMIN_DIR', ABSPATH .'/TP_Admin');
            if(! defined('TP_ADMIN_ASSETS')) define('TP_ADMIN_ASSETS', TP_ADMIN_DIR .'/Assets');
            if(! defined('TP_ADMIN_LANG')) define('TP_ADMIN_LANG', TP_ADMIN_ASSETS. '/Languages');
            //namespaces
            if(! defined('TP_NS_ADMIN')) define('TP_NS_ADMIN', 'TP_Admin\\');
            if(! defined('TP_NS_ADMIN_LIBS')) define('TP_NS_ADMIN_LIBS', TP_NS_ADMIN .'Libs\\');
            if(! defined('TP_NS_ADMIN_FILESYSTEM')) define('TP_NS_ADMIN_FILESYSTEM',TP_NS_ADMIN_LIBS . 'AdmFilesystem\\' );
            if(! defined('TP_NS_ADMIN_COMPONENTS')) define('TP_NS_ADMIN_COMPONENTS', TP_NS_ADMIN_LIBS .'AdmComponents\\');
            if(! defined('TP_NS_ADMIN_MODULES')) define('TP_NS_ADMIN_MODULES', TP_NS_ADMIN_LIBS .'AdmModules\\');
            if(! defined('TP_NS_ADMIN_MENU_PAGES')) define('TP_NS_ADMIN_MENU_PAGES', TP_NS_ADMIN_LIBS .'AdmMenusPages\\');
            if(! defined('TP_NS_ADMIN_TB_LIST')) define('TP_NS_ADMIN_TB_LIST', TP_NS_ADMIN_LIBS .'Lists\\');
            //AdmMenus
        }
        protected function _content_constants():void{
            //dirs
            if(! defined('TP_CONTENT_DIR')) define('TP_CONTENT_DIR', ABSPATH .'TP_Content/');
            if(! defined('TP_CONTENT_ASSETS')) define('TP_CONTENT_ASSETS', TP_CONTENT_DIR .'Assets/');
            if(! defined('TP_CONTENT_THEMES')) define('TP_CONTENT_THEMES', TP_CONTENT_DIR . 'Themes/' );
            if(! defined('TP_CONTENT_LIBS')) define('TP_CONTENT_LIBS', TP_CONTENT_THEMES . 'TP_Library/' );
            if(! defined('TP_MU_CONTENT_LIBS')) define('TP_MU_CONTENT_LIBS', TP_CONTENT_LIBS . 'Multisite/' );
            if(! defined('TP_CONTENT_LANG')) define('TP_CONTENT_LANG', TP_CONTENT_ASSETS. '/Languages');
            if (! defined( 'TEMPLATE_PATH' ) ) define( 'TEMPLATE_PATH', $this->_get_template_directory() );
            if (! defined( 'STYLESHEET_PATH' ) ) define( 'STYLESHEET_PATH', $this->_get_stylesheet_directory() );
            //namespaces
            if (! defined( 'TP_NS' ) ) define('TP_NS','\\');
            if (! defined( 'TP_NS_CONTENT' ) ) define('TP_NS_CONTENT','TP_Content\\');
            if (! defined( 'TP_NS_THEMES' ) ) define('TP_NS_THEMES',TP_NS_CONTENT.'Themes\\');
            if (! defined( 'TP_NS_LIBRARY' ) ) define('TP_NS_LIBRARY',TP_NS_THEMES.'TP_Library\\');
            if (! defined( 'TP_NS_MODULES' ) ) define('TP_NS_MODULES',TP_NS_LIBRARY.'Modules\\');
            if (! defined( 'TP_NS_TEMPLATE_PATH' ) ) define('TP_NS_TEMPLATE_PATH','\\ThemeSrc\\Templates\\');
            //if(! defined( 'TP_NS_STYLESHEET_PATH' ) ) define('TP_NS_CONTENT',TP_NS_CONTENT.'\\');
            //themes
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
        protected function _core_constants():void{
            //dirs
            if(!defined('TP_CORE') ) define('TP_CORE', ABSPATH . 'TP_Core/');
            if(!defined('TP_CORE_ASSETS')) define('TP_CORE_ASSETS', TP_CORE .'Assets/');
            if(!defined('TP_CORE_MEDIA')) define('TP_CORE_MEDIA', TP_CORE_ASSETS .'Media/');
            if(!defined('TP_CORE_IMAGES')) define('TP_CORE_IMAGES', TP_CORE_MEDIA .'Images/');
            if(!defined('TP_CORE_LANG')) define('TP_CORE_LANG', TP_CORE_ASSETS. '/Languages');
            //namespaces
            if ( ! defined( 'TP_NS_CORE' ) ) define('TP_NS_CORE','TP_Core\\');
            if ( ! defined( 'TP_NS_CORE_LIBS' ) ) define('TP_NS_CORE_LIBS',TP_NS_CORE .'Libs\\');
            if ( ! defined( 'TP_NS_CORE_TEMPLATES' ) ) define('TP_NS_CORE_TEMPLATES',TP_NS_CORE .'Templates\\');
            if ( ! defined( 'TP_NS_CORE_BLOCKS_STORE' ) ) define('TP_NS_CORE_BLOCKS_STORE',TP_NS_CORE_TEMPLATES .'BlocksStore\\');
            if ( ! defined( 'TP_NS_CORE_THEME_TEMPLATES' ) ) define('TP_NS_CORE_THEME_TEMPLATES',TP_NS_CORE_TEMPLATES .'ThemeTemplates\\');
            if ( ! defined( 'TP_NS_CORE_UNCATEGORIZED' ) ) define('TP_NS_CORE_UNCATEGORIZED',TP_NS_CORE_TEMPLATES .'Uncategorized\\');

        }
        protected function _http_constants(): void{
            define('TP_POST', 'POST');
            define('TP_PATCH', 'PATCH');
            define('TP_PUT', 'PUT');
            define('TP_GET', 'GET');
            define('TP_EDITABLE','POST PUT PATCH');
            define('TP_ALL_METHODS','GET, POST, PUT, PATCH, DELETE');
            define('TP_HEAD', 'HEAD');
            define('TP_OPTIONS', 'OPTIONS');
            define('TP_TRACE', 'TRACE');
            define('TP_BUFFER_SIZE', 1160);
            define('TP_REQUEST_VERSION', '5.9.0');//todo
            define('OK' , 200);
            define('CREATED' , 201);
            define('MULTI_STATUS' , 207);
            define('BAD_REQUEST' , 400);
            define('UNAUTHORIZED' , 401);
            define('FORBIDDEN' , 403);
            define('NOT_FOUND' , 404);
            define('METHOD_NOT_ALLOWED' , 405);
            define('CONFLICT' , 409);
            define('GONE' , 410);
            define('PRECONDITION_FAILED' , 412);
            define('SERVER_PORT_SSL', 443);
            define('INTERNAL_SERVER_ERROR' , 500);
            define('NOT_IMPLEMENTED' , 501);
            define('SERVICE_UNAVAILABLE' , 503);

        }
        protected function _db_constants(): void{
            define('TP_CREATE_TABLE',"CREATE_TABLE");
            define('TP_DELETE' , "DELETE");
            define('TP_INSERT' , "INSERT");
            define('TP_SELECT' , "SELECT");
            define('TP_UPDATE' , "UPDATE");
            define('TP_DROP_TABLE' , "DROP TABLE");
            if(!defined('ARRAY_A')) define('ARRAY_A','ARRAY_A');
            if(!defined('ARRAY_N')) define('ARRAY_N','ARRAY_N');
            if(!defined('EZSQL_VERSION')) define('EZSQL_VERSION','TP0.01');
            if(!defined('OBJECT')) define('OBJECT','OBJECT');
            if(!defined('OBJECT_K')) define('OBJECT_K','OBJECT_K');

        }
        protected function _initial_constants(): void{ //todo set this right
            define( 'KB_IN_BYTES', 1024 );
            define( 'MB_IN_BYTES', 1024 * KB_IN_BYTES );
            define( 'GB_IN_BYTES', 1024 * MB_IN_BYTES );
            define( 'TB_IN_BYTES', 1024 * GB_IN_BYTES );
            if ( ! defined( 'TP_START_TIMESTAMP' ) ) define( 'TP_START_TIMESTAMP', microtime( true ) );
            $current_limit     = ini_get( 'memory_limit' );
            $current_limit_int = $this->_tp_convert_hr_to_bytes( $current_limit );
            if ( ! defined( 'TP_MEMORY_LIMIT' ) ) {
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
            //if(!isset( $this->tp_blog_id ) ) $this->tp_blog_id = 1;
            if (!defined('TP_DEBUG')){
                if ( 'development' === $this->_tp_get_environment_type()) define( 'TP_DEBUG', true );
                else define( 'TP_DEBUG', false );
            }
            if (!defined('TP_DEBUG_DISPLAY'))define('TP_DEBUG_DISPLAY', true );
            if (!defined('TP_DEBUG_LOG')) define( 'TP_DEBUG_LOG', false );
            if (!defined('TP_CACHE')) define( 'TP_CACHE',false );
            define( 'TP_FEATURE_BETTER_PASSWORDS', true );
            define( 'MINUTE_IN_SECONDS', 60 );
            define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
            define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
            define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
            define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
            define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
            if (!defined('TP_SCRIPT_DEBUG')){
                if (!empty( $this->tp_version )) $develop_src = false !== strpos( $this->tp_version, '-src' );
                else $develop_src = false;
                define( 'TP_SCRIPT_DEBUG',$develop_src ); //todo $develop_src
            }
            if ( ! defined( 'KSES_CUSTOM_TAGS' ) ) define( 'KSES_CUSTOM_TAGS', false );
        }
        protected function _cookie_constants(): void{
            if ( ! defined( 'COOKIE_HASH' ) ) {
                $siteurl = $this->_get_site_option( 'siteurl' );
                if ( $siteurl ) define( 'COOKIE_HASH', md5( $siteurl ) );
                else define( 'COOKIE_HASH', '' );
            }
            if ( ! defined( 'USER_COOKIE' ) ) define( 'USER_COOKIE', 'tailored_press_user_' . COOKIE_HASH );
            if ( ! defined( 'PASS_COOKIE' ) ) define( 'PASS_COOKIE', 'tailored_press_pass_' . COOKIE_HASH );
            if ( ! defined( 'AUTH_COOKIE' ) ) define( 'AUTH_COOKIE', 'tailored_press_' . COOKIE_HASH );
            if ( ! defined( 'SECURE_AUTH_COOKIE' ) ) define( 'SECURE_AUTH_COOKIE', 'tailored_press_sec_' . COOKIE_HASH );
            if ( ! defined( 'LOGGED_IN_COOKIE' ) ) define( 'LOGGED_IN_COOKIE', 'tailored_press_logged_in_' . COOKIE_HASH );
            if ( ! defined( 'TEST_COOKIE' ) ) define( 'TEST_COOKIE', 'tailored_press_test_cookie');
            if ( ! defined( 'COOKIE_PATH' ) ) define( 'COOKIE_PATH', preg_replace( '|https?://[^/]+|i', '', $this->_get_option( 'home' ) . '/' )  );
            if ( ! defined( 'SITE_COOKIE_PATH' ) ) define( 'SITE_COOKIE_PATH',  preg_replace( '|https?://[^/]+|i', '', $this->_get_option( 'siteurl' ) . '/' ));
            if ( ! defined( 'ADMIN_COOKIE_PATH' ) ) define( 'ADMIN_COOKIE_PATH', SITE_COOKIE_PATH.'TP_Admin' );
            if ( ! defined( 'COOKIE_DOMAIN' ) ) define( 'COOKIE_DOMAIN', false );
            if ( ! defined( 'RECOVERY_MODE_COOKIE' ) ) define( 'RECOVERY_MODE_COOKIE', 'tailored_press_rec_' . COOKIE_HASH );
            //define( 'TP_DB_NAME', 'awd_4g7zT_main' );
            //define( 'TP_DB_USER', 'root' );
            //define( 'TP_DB_PASSWORD', 'w2v43e72' );
            //define( 'TP_DB_HOST', 'localhost' );
            //define( 'TP_DB_CHARSET', 'utf8mb4' );
            //define( 'TP_DB_COLLATE', '' );
        }
        protected function _ssl_constants(): void{

        }
        protected function _functional_constants(): void{
            if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {define( 'AUTOSAVE_INTERVAL', MINUTE_IN_SECONDS );}
            if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) { define( 'EMPTY_TRASH_DAYS', 30 ); }
            if ( ! defined( 'TP_POST_REVISIONS' ) ) define( 'TP_POST_REVISIONS', true );
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
            if ( ! defined( 'IMAGE_TYPE_WEBP' ) )
                define( 'IMAGE_TYPE_WEBP', 18 );
            // IMG_WEBP constant is only defined in PHP 7.0.10 or later.
            if ( ! defined( 'IMG_WEBP' ) )
                define( 'IMG_WEBP', IMAGE_TYPE_WEBP );
            if (!defined('__MEDIA_TRASH')) define( '__MEDIA_TRASH', false );
        }
        protected function _namespace_constants(): void{
            define('TP_XML','<?xml ');
            define('TP_XML_END','?>');
            define('TP_CDATA',' <![CDATA[');
            define('TP_CDATA_END',']] ');
            //define('TP_','');
            define('W3_ATOM','http://www.w3.org/2005/Atom');
            define('W3_RDF','https://www.w3.org/1999/02/22-rdf-syntax-ns#');
            define('W3_XHTML','https://www.w3.org/1999/xhtml');
            define('W3_MATHML','https://www.w3.org/1998/Math/MathML');
            define('W3_SVG','https://www.w3.org/2000/svg');
            define('W3_XLINK','https://www.w3.org/1999/xlink');
            define('W3_XML_NS','https://www.w3.org/XML/1998/namespace');
            define('W3_XMLNS','https://www.w3.org/2000/xmlns/');
            define('PURL','https://purl.org/rss/1.0');
            define('PURL_DC','https://purl.org/dc/elements/1.1/');
            define('PURL_SY','https://purl.org/rss/1.0/modules/syndication/');
            define('PURL_THR','https://purl.org/syndication/thread/1.0');
            define('PURL_CONTENT','https://purl.org/rss/1.0/modules/content/');
            define('PURL_SLASH','https://purl.org/rss/1.0/modules/slash/');
            define('WEB_NS_MVCB','https://webns.net/mvcb/');
            define('WFW_COMMENT_API','https://wellformedweb.org/CommentAPI/');
            /** @deprecated */
            define('TP_NAMESPACE_XHTML','https://www.w3.org/1999/xhtml');
            /** @deprecated */
            define('TP_NAMESPACE_MATHML','https://www.w3.org/1998/Math/MathML');
            /** @deprecated */
            define('TP_NAMESPACE_SVG','https://www.w3.org/2000/svg');
            /** @deprecated */
            define('TP_NAMESPACE_XLINK','https://www.w3.org/1999/xlink');
            /** @deprecated */
            define('TP_NAMESPACE_XML','https://www.w3.org/XML/1998/namespace');
            /** @deprecated */
            define('TP_NAMESPACE_XMLNS','https://www.w3.org/2000/xmlns/');

        }
        protected function _version_constants(): void{
            define('TP_VERSION', '5.9.4');
            define('TP_PHP_VERSION', '7.0');
            define('TP_DB_VERSION','51917');
            define('TINYMCE_VERSION','49110-20201110');
            define('TP_REQUIRED_PHP_VERSION','7.0');
            define('TP_REQUIRED_MYSQL_VERSION','7.0');
        }
        protected function _rewrite_constants(): void{
            define('EP_NONE', 0 );
            define('EP_PERMALINK', 1 );
            define('EP_ATTACHMENT', 2 );
            define('EP_DATE', 4 );
            define('EP_YEAR', 8 );
            define('EP_MONTH', 16 );
            define('EP_DAY', 32 );
            define('EP_ROOT', 64 );
            define('EP_COMMENTS', 128 );
            define('EP_SEARCH', 256);
            define('EP_CATEGORIES', 512 );
            define('EP_TAGS', 1024 );
            define('EP_AUTHORS', 2048 );
            define('EP_PAGES', 4096);
            define('EP_ALL_ARCHIVES', EP_DATE | EP_YEAR | EP_MONTH | EP_DAY | EP_CATEGORIES | EP_TAGS | EP_AUTHORS );
            define('EP_ALL', EP_PERMALINK | EP_ATTACHMENT | EP_ROOT | EP_COMMENTS | EP_SEARCH | EP_PAGES | EP_ALL_ARCHIVES );

        }
    }
}else die;