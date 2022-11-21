<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 13:42
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class getID3Base{
        public $encoding        = 'UTF-8';
        public $encoding_id3v1  = 'ISO-8859-1';
        public $encoding_id3v1_autodetect  = false;
        public $option_tag_id3v1         = true;
        public $option_tag_id3v2         = true;
        public $option_tag_lyrics3       = true;
        public $option_tag_apetag        = true;
        public $option_tags_process      = true;
        public $option_tags_html         = true;
        public $option_extra_info = true;
        public $option_save_attachments  = true;
        public $option_md5_data = false;
        public $option_md5_data_source   = false;
        public $option_sha1_data = false;
        public $option_max_2gb_check;
        public $option_fread_buffer_size = 32768;
        // module-specific options
        public $options_archive_rar_use_php_rar_extension = true;
        public $options_archive_gzip_parse_contents = false;
        public $options_audio_midi_scanwholefile = true;
        public $options_audio_mp3_allow_bruteforce = false;
        public $options_audio_mp3_mp3_valid_check_frames = 50;
        public $options_audio_wavpack_quick_parsing = false;
        public $options_audiovideo_flv_max_frames = 100000;
        public $options_audiovideo_matroska_hide_clusters    = true;
        public $options_audiovideo_matroska_parse_whole_file = false;
        public $options_audiovideo_quicktime_ReturnAtomData  = false;
        public $options_audiovideo_quicktime_ParseAllPossibleAtoms = false;
        public $options_audiovideo_swf_ReturnAllTagData = false;
        public $options_graphic_bmp_ExtractPalette = false;
        public $options_graphic_bmp_ExtractData    = false;
        public $options_graphic_png_max_data_bytes = 10000000;
        public $options_misc_pdf_returnXREF = false;
        public $options_misc_torrent_max_torrent_filesize = 1048576;
        public $filename;
        public $fp;
        public $info;
        public $tempdir = GETID3_TEMP_DIR;
        public $memory_limit = 0;
        protected $_startup_error   = '';
        protected $_startup_warning = '';
        public const VERSION           = '1.9.21-202109171300';
        public const FREAD_BUFFER_SIZE = 32768;
        public const ATTACHMENTS_NONE   = false;
        public const ATTACHMENTS_INLINE = true;
        public function __construct(){
            if (!defined('GETID3_OS_ISWINDOWS')) {
                define('GETID3_OS_ISWINDOWS', (stripos(PHP_OS, 'WIN') === 0));
            }
            if (!defined('GETID3_INCLUDEPATH')) {
                /** @deprecated  */
                define('GETID3_INCLUDEPATH', __DIR__ .DIRECTORY_SEPARATOR);
            }
            if(!defined('GETID3_NAMESPACE_PATH')){
                define('GETID3_NAMESPACE_PATH',TP_NS_CORE_LIBS. 'ID3\\Modules\\');
            }
            if (!defined('ENT_SUBSTITUTE')) {
                define('ENT_SUBSTITUTE', (defined('ENT_IGNORE') ? ENT_IGNORE : 8));
            }
            $temp_dir = ini_get('upload_tmp_dir');
            if ($temp_dir && (!is_dir($temp_dir) || !is_readable($temp_dir))) {
                $temp_dir = '';
            }
            if (!$temp_dir && function_exists('sys_get_temp_dir')) { // sys_get_temp_dir added in PHP v5.2.1
                $temp_dir = sys_get_temp_dir();
            }
            $temp_dir = @realpath($temp_dir); // see https://github.com/JamesHeinrich/getID3/pull/10
            $open_basedir = ini_get('open_basedir');
            if ($open_basedir) {
                $temp_dir     = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $temp_dir);
                $open_basedir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $open_basedir);
                if (substr($temp_dir, -1, 1) !== DIRECTORY_SEPARATOR) {
                    $temp_dir .= DIRECTORY_SEPARATOR;
                }
                $found_valid_tempdir = false;
                $open_basedirs = explode(PATH_SEPARATOR, $open_basedir);
                foreach ($open_basedirs as $basedir) {
                    if (substr($basedir, -1, 1) !== DIRECTORY_SEPARATOR) {
                        $basedir .= DIRECTORY_SEPARATOR;
                    }
                    if (strpos($temp_dir, $basedir) === 0) {
                        $found_valid_tempdir = true;
                        break;
                    }
                }
                if (!$found_valid_tempdir) {
                    $temp_dir = '';
                }
                unset($open_basedirs, $found_valid_tempdir, $basedir);
            }
            if (!$temp_dir) {
                $temp_dir = '*';
            }
            if (!defined('GETID3_TEMP_DIR')) {
                define('GETID3_TEMP_DIR', $temp_dir);
            }
            unset($open_basedir, $temp_dir);
        }
    }
}else{die;}
