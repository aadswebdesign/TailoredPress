<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 13:47
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class getID3 extends getID3Base {
        public $test;
        public function __construct() {
            parent::__construct();
            $required_php_version = '7.0.0';
            if (version_compare(PHP_VERSION, $required_php_version, '<')) {
                $this->_startup_error .= 'getID3() requires PHP v'.$required_php_version.' or higher - you are running v'.PHP_VERSION."\n";
                return;
            }
            $memoryLimit = ini_get('memory_limit');
            if (preg_match('#(\d+) ?M#i', $memoryLimit, $matches)) {
                $memoryLimit = $matches[1] * 1048576;
            } elseif (preg_match('#(\d+) ?G#i', $memoryLimit, $matches)) { // The 'G' modifier is available since PHP 5.1.0
                $memoryLimit = $matches[1] * 1073741824;
            }
            $this->memory_limit = $memoryLimit;
            if ($this->memory_limit <= 0) {
            } elseif ($this->memory_limit <= 4194304) {
                $this->_startup_error .= 'PHP has less than 4MB available memory and will very likely run out. Increase memory_limit in php.ini'."\n";
            } elseif ($this->memory_limit <= 12582912) {
                $this->_startup_warning .= 'PHP has less than 12MB available memory and might run out if all modules are loaded. Increase memory_limit in php.ini'."\n";
            }
            /** @noinspection DeprecatedIniOptionsInspection */ //todo
            if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
                $this->warning('WARNING: Safe mode is on, shorten support disabled, md5data/sha1data for ogg vorbis disabled, ogg vorbos/flac tag writing disabled.');
            }
            if (($mbstring_func_overload = (int) ini_get('mbstring.func_overload')) && ($mbstring_func_overload & 0x02)) {
                $this->_startup_error .= 'WARNING: php.ini contains "mbstring.func_overload = '.ini_get('mbstring.func_overload').'", getID3 cannot run with this setting (bitmask 2 (string functions) cannot be set). Recommended to disable entirely.'."\n";
            }
            if (PHP_VERSION_ID < 70400) {
                if (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()) {
                    $this->_startup_error .= 'magic_quotes_runtime must be disabled before running getID3(). Surround getid3 block by set_magic_quotes_runtime(0) and set_magic_quotes_runtime(1).'."\n";
                }
                if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                    $this->_startup_error .= 'magic_quotes_gpc must be disabled before running getID3(). Surround getid3 block by set_magic_quotes_gpc(0) and set_magic_quotes_gpc(1).'."\n";
                }
            }
            // Load support library todo
            if ($this->option_max_2gb_check === null) {
                $this->option_max_2gb_check = (PHP_INT_MAX <= 2147483647);
            }
            if (GETID3_OS_ISWINDOWS && !defined('GETID3_HELPERAPPSDIR')) {

                $helperappsdir = GETID3_INCLUDEPATH.'..'.DIRECTORY_SEPARATOR.'helperapps'; // must not have any space in this path

                if (!is_dir($helperappsdir)) {
                    $this->_startup_warning .= '"'.$helperappsdir.'" cannot be defined as GETID3_HELPERAPPSDIR because it does not exist'."\n";
                } elseif (strpos(realpath($helperappsdir), ' ') !== false) {
                    $DirPieces = explode(DIRECTORY_SEPARATOR, realpath($helperappsdir));
                    $path_so_far = array();
                    foreach ($DirPieces as $key => $value) {
                        if (strpos($value, ' ') !== false) {
                            if (!empty($path_so_far)) {
                                $commandline = 'dir /x '.escapeshellarg(implode(DIRECTORY_SEPARATOR, $path_so_far));
                                $dir_listing = shell_exec($commandline);
                                $lines = explode("\n", $dir_listing);
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (preg_match('#^([0-9/]{10}) +([0-9:]{4,5}( [AP]M)?) +(<DIR>|[0-9,]+) +([^ ]{0,11}) +(.+)$#', $line, $matches)) {
                                        @list($filesize, $shortname, $filename) = $matches;
                                        if ((strtoupper($filesize) === __DIR__) && (strtolower($filename) === strtolower($value))) {
                                            $value = $shortname;
                                        }
                                    }
                                }
                            } else {
                                $this->_startup_warning .= 'GETID3_HELPERAPPSDIR must not have any spaces in it - use 8dot3 naming convention if neccesary. You can run "dir /x" from the commandline to see the correct 8.3-style names.'."\n";
                            }
                        }
                        $path_so_far[] = $value;
                    }
                    $helperappsdir = implode(DIRECTORY_SEPARATOR, $path_so_far);
                }
                define('GETID3_HELPERAPPSDIR', $helperappsdir.DIRECTORY_SEPARATOR);
            }
            if (!empty($this->startup_error)) {
                echo $this->startup_error;
                throw new getid3_exception($this->startup_error);
            }
        }
        public function version():string{
            return self::VERSION;
        }//516
        public function fread_buffer_size():int {
            return $this->option_fread_buffer_size;
        }//523
        public function setOption($optArray):bool {
            if (!is_array($optArray) || empty($optArray)) {
                return false;
            }
            foreach ($optArray as $opt => $val) {
                if (isset($this->$opt) === false) {
                    continue;
                }
                $this->$opt = $val;
            }
            return true;
        }
        public function openfile($filename, $filesize=null, $fp=null):bool {
            try {
                if (!empty($this->startup_error)) {
                    throw new getid3_exception($this->startup_error);
                }
                if (!empty($this->startup_warning)) {
                    foreach (explode("\n", $this->startup_warning) as $startup_warning) {
                        $this->warning($startup_warning);
                    }
                }
                $this->filename = $filename;
                $this->info = array();
                $this->info['GETID3_VERSION']   = $this->version();
                $this->info['php_memory_limit'] = (($this->memory_limit > 0) ? $this->memory_limit : false);
                // remote files not supported
                if (preg_match('#^(ht|f)tp://#', $filename)) {
                    throw new getid3_exception('Remote files are not supported - please copy the file locally first');
                }
                $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
                //if (is_readable($filename) && is_file($filename) && ($this->fp = fopen($filename, 'rb'))) { // see https://www.getid3.org/phpBB3/viewtopic.php?t=1720
                if (($fp !== null) && ((get_resource_type($fp) === 'file') || (get_resource_type($fp) === 'stream'))) {
                    $this->fp = $fp;
                } elseif (is_file($filename) && ($this->fp = fopen($filename, 'rb')) && (is_readable($filename) || file_exists($filename))) {
                    // great
                } else {
                    $errormessagelist = array();
                    if (!is_readable($filename)) {
                        $errormessagelist[] = '!is_readable';
                    }
                    if (!is_file($filename)) {
                        $errormessagelist[] = '!is_file';
                    }
                    if (!file_exists($filename)) {
                        $errormessagelist[] = '!file_exists';
                    }
                    if (empty($errormessagelist)) {
                        $errormessagelist[] = 'fopen failed';
                    }
                    throw new getid3_exception('Could not open "'.$filename.'" ('.implode('; ', $errormessagelist).')');
                }
                $this->info['filesize'] = (!is_null($filesize) ? $filesize : filesize($filename));
                $filename = str_replace('\\', '/', $filename);
                $this->info['filepath']     = str_replace('\\', '/', realpath(dirname($filename)));
                $this->info['filename']     = getid3_lib::mb_basename($filename);
                $this->info['filenamepath'] = $this->info['filepath'].'/'.$this->info['filename'];
                $this->info['avdataoffset']        = 0;
                $this->info['avdataend']           = $this->info['filesize'];
                $this->info['fileformat']          = '';                // filled in later
                $this->info['audio']['dataformat'] = '';                // filled in later, unset if not used
                $this->info['video']['dataformat'] = '';                // filled in later, unset if not used
                $this->info['tags']                = [];           // filled in later, unset if not used
                $this->info['error']               = [];           // filled in later, unset if not used
                $this->info['warning']             = [];           // filled in later, unset if not used
                $this->info['comments']            = [];           // filled in later, unset if not used
                $this->info['encoding']            = $this->encoding;   // required by id3v2 and iso modules - can be unset at the end if desired
                if ($this->option_max_2gb_check) {
                    $fseek = fseek($this->fp, 0, SEEK_END);
                    if (($fseek < 0) ||($this->info['filesize'] < 0) || (($this->info['filesize'] !== 0) && (ftell($this->fp) === 0)) || (ftell($this->fp) < 0)) {
                        $real_filesize = getid3_lib::getFileSizeSyscall($this->info['filenamepath']);
                        if ($real_filesize === false) {
                            unset($this->info['filesize']);
                            fclose($this->fp);
                            throw new getid3_exception('Unable to determine actual filesize. File is most likely larger than '.round(PHP_INT_MAX / 1073741824).'GB and is not supported by PHP.');
                        }
                        if (getid3_lib::intValueSupported($real_filesize)) {
                            unset($this->info['filesize']);
                            fclose($this->fp);
                            throw new getid3_exception('PHP seems to think the file is larger than '.round(PHP_INT_MAX / 1073741824).'GB, but filesystem reports it as '.number_format($real_filesize / 1073741824, 3).'GB, please report to info@getid3.org');
                        }
                        $this->info['filesize'] = $real_filesize;
                        $this->warning('File is larger than '.round(PHP_INT_MAX / 1073741824).'GB (filesystem reports it as '.number_format($real_filesize / 1073741824, 3).'GB) and is not properly supported by PHP.');
                    }
                }
                return true;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            return false;
        }//554
        public function analyze($filename, $filesize=null, $original_filename='', $fp=null) {
            try {
                if (!$this->openfile($filename, $filesize, $fp)) {
                    return $this->info;
                }
                foreach (array('id3v2'=>'id3v2', 'id3v1'=>'id3v1', 'apetag'=>'ape', 'lyrics3'=>'lyrics3') as $tag_name => $tag_key) {
                    $option_tag = 'option_tag_'.$tag_name;
                    if ($this->$option_tag) {
                        $this->load_module('tag_'.$tag_name);
                        try {
                            $tag_class = 'getid3_'.$tag_name;
                            $tag = $this->load_module($tag_class);
                            $tag->Analyze();
                        }
                        catch (getid3_exception $e) {
                            throw $e;
                        }
                    }
                }
                if (isset($this->info['id3v2']['tag_offset_start'])) {
                    $this->info['avdataoffset'] = max($this->info['avdataoffset'], $this->info['id3v2']['tag_offset_end']);
                }
                foreach (array('id3v1'=>'id3v1', 'apetag'=>'ape', 'lyrics3'=>'lyrics3') as $tag_name => $tag_key) {
                    if (isset($this->info[$tag_key]['tag_offset_start'])) {
                        $this->info['avdataend'] = min($this->info['avdataend'], $this->info[$tag_key]['tag_offset_start']);
                    }
                }
                if (!$this->option_tag_id3v2) {
                    fseek($this->fp, 0);
                    $header = fread($this->fp, 10);
                    if ((strpos($header, 'ID3') === 0) && (strlen($header) === 10)) {
                        $this->info['id3v2']['header']        = true;
                        $this->info['id3v2']['majorversion']  = ord($header[3]);
                        $this->info['id3v2']['minorversion']  = ord($header[4]);
                        $this->info['avdataoffset']          += getid3_lib::BigEndian2Int(substr($header, 6, 4), 1) + 10; // length of ID3v2 tag in 10-byte header doesn't include 10-byte header length
                    }
                }
                fseek($this->fp, $this->info['avdataoffset']);
                $formattest = fread($this->fp, 32774);
                $determined_format = $this->GetFileFormat($formattest, ($original_filename ?: $filename));
                if (!$determined_format) {
                    fclose($this->fp);
                    return $this->error('unable to determine file format');
                }
                if (isset($determined_format['fail_id3']) && (in_array('id3v1', $this->info['tags'],true) || in_array('id3v2', $this->info['tags'],true))) {
                    if ($determined_format['fail_id3'] === 'ERROR') {
                        fclose($this->fp);
                        return $this->error('ID3 tags not allowed on this file type.');
                    }
                    if ($determined_format['fail_id3'] === 'WARNING') {
                        $this->warning('ID3 tags not allowed on this file type.');
                    }
                }
                if (isset($determined_format['fail_ape']) && in_array('ape', $this->info['tags'],true)) {
                    if ($determined_format['fail_ape'] === 'ERROR') {
                        fclose($this->fp);
                        return $this->error('APE tags not allowed on this file type.');
                    }
                    if ($determined_format['fail_ape'] === 'WARNING') {
                        $this->warning('APE tags not allowed on this file type.');
                    }
                }
                $this->info['mime_type'] = $determined_format['mime_type'];
                if (!file_exists(GETID3_NAMESPACE_PATH.$determined_format['namespace_path'])) {//include
                    fclose($this->fp);
                    return $this->error('Format not supported, module "'.$determined_format['namespace_path'].'" was removed.');
                }
                if (!empty($determined_format['iconv_req']) && !function_exists('mb_convert_encoding') && !function_exists('iconv') && !in_array($this->encoding, array('ISO-8859-1', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'UTF-16'))) {
                    $errormessage = 'mb_convert_encoding() or iconv() support is required for this module ('.$determined_format['include'].') for encodings other than ISO-8859-1, UTF-8, UTF-16LE, UTF16-BE, UTF-16. ';
                    if (GETID3_OS_ISWINDOWS) {
                        $errormessage .= 'PHP does not have mb_convert_encoding() or iconv() support. Please enable php_mbstring.dll / php_iconv.dll in php.ini, and copy php_mbstring.dll / iconv.dll from c:/php/dlls to c:/windows/system32';
                    } else {
                        $errormessage .= 'PHP is not compiled with mb_convert_encoding() or iconv() support. Please recompile with the --enable-mbstring / --with-iconv switch';
                    }
                    return $this->error($errormessage);
                }
                // include module //todo
                $class_name = 'getid3_'.$determined_format['module'];
                if (!class_exists($class_name)) {
                    return $this->error('Format not supported, module "'.$determined_format['include'].'" is corrupt.');
                }
                //$class = new $class_name($this);
                $class = $this->load_module($this);
                foreach (get_object_vars($this) as $getid3_object_vars_key => $getid3_object_vars_value) {
                    if (preg_match('#^options_([^_]+)_([^_]+)_(.+)$#i', $getid3_object_vars_key, $matches)) {
                        @list($GOVgroup, $GOVmodule, $GOVsetting) = $matches;
                        $GOVgroup = (($GOVgroup === 'audiovideo') ? 'audio-video' : $GOVgroup); // variable names can only contain 0-9a-z_ so standardize here
                        if (($GOVgroup === $determined_format['group']) && ($GOVmodule === $determined_format['module'])) {
                            $class->$GOVsetting = $getid3_object_vars_value;
                        }
                    }
                }
                $class->Analyze();
                unset($class);
                fclose($this->fp);
                if ($this->option_tags_process) {
                    $this->HandleAllTags();
                }
                if ($this->option_extra_info) {
                    $this->ChannelsBitratePlaytimeCalculations();
                    $this->CalculateCompressionRatioVideo();
                    $this->CalculateCompressionRatioAudio();
                    $this->CalculateReplayGain();
                    $this->ProcessAudioStreams();
                }
                if ($this->option_md5_data) {
                    if (!$this->option_md5_data_source || empty($this->info['md5_data_source'])) {
                        $this->getHashdata('md5');
                    }
                }
                if ($this->option_sha1_data) {
                    $this->getHashdata('sha1');
                }
                $this->__clean_up();
            } catch (\Exception $e) {
                $this->error('Caught exception: '.$e->getMessage());
            }
            return $this->info;
        }//665
        public function error($message) {
            $this->__clean_up();
            if (!isset($this->info['error'])) {
                $this->info['error'] = array();
            }
            $this->info['error'][] = $message;
            return $this->info;
        }//834
        public function warning($message):bool {
            $this->info['warning'][] = $message;
            return true;
        }//851
        private function __clean_up():bool {
            $AVpossibleEmptyKeys = array('dataformat', 'bits_per_sample', 'encoder_options', 'streams', 'bitrate');
            foreach ($AVpossibleEmptyKeys as $dummy => $key) {
                if (empty($this->info['audio'][$key]) && isset($this->info['audio'][$key])) {
                    unset($this->info['audio'][$key]);
                }
                if (empty($this->info['video'][$key]) && isset($this->info['video'][$key])) {
                    unset($this->info['video'][$key]);
                }
            }
            if (!empty($this->info)) {
                foreach ($this->info as $key => $value) {
                    if (empty($this->info[$key]) && ($this->info[$key] !== 0) && ($this->info[$key] !== '0')) {
                        unset($this->info[$key]);
                    }
                }
            }
            if (empty($this->info['fileformat'])) {
                if (isset($this->info['avdataoffset'])) {
                    unset($this->info['avdataoffset']);
                }
                if (isset($this->info['avdataend'])) {
                    unset($this->info['avdataend']);
                }
            }
            if (!empty($this->info['error'])) {
                $this->info['error'] = array_values(array_unique($this->info['error']));
            }
            if (!empty($this->info['warning'])) {
                $this->info['warning'] = array_values(array_unique($this->info['warning']));
            }
            unset($this->info['php_memory_limit']);
            return true;
        }//860
        public function GetFileFormatArray():array{
            static $format_info = [];
            if (empty($format_info)) {
                $format_info = [
                    // AC-3audio - Dolby AC-3 / Dolby Digital
                    'ac3' => ['pattern' => '^\\x0B\\x77','group' => 'audio','module' => 'ac3','mime_type' => 'audio/ac3',],
                    // AAC  - audio       - Advanced Audio Coding (AAC) - ADIF format
                    'adif' => ['pattern' => '^ADIF','group' => 'audio','module' => 'aac', 'mime_type' => 'audio/aac','fail_ape' => 'WARNING',],
                    // AAC  - audio       - Advanced Audio Coding (AAC) - ADTS format (very similar to MP3)
                    'adts' => ['pattern' => '^\\xFF[\\xF0-\\xF1\\xF8-\\xF9]','group' => 'audio',
                        'module' => 'aac','mime_type' => 'audio/aac','fail_ape' => 'WARNING',],
                    // AU   - audio       - NeXT/Sun Audio (AU)
                    'au' => ['pattern' => '^\\.snd','group' => 'audio','module' => 'au','mime_type' => 'audio/basic',],
                    // AMR  - audio       - Adaptive Multi Rate
                    'amr'  => ['pattern' => '^\\x23\\x21AMR\\x0A','group' => 'audio','module' => 'amr','mime_type' => 'audio/amr',],
                    // AVR  - audio       - Audio Visual Research
                    'avr'  => ['pattern' => '^2BIT','group' => 'audio','module' => 'avr',
                        'mime_type' => 'application/octet-stream',],
                    // BONK - audio       - Bonk v0.9+
                    'bonk' => ['pattern' => '^\\x00(BONK|INFO|META| ID3)','group' => 'audio',
                        'module' => 'bonk','mime_type' => 'audio/xmms-bonk',],
                    // DSF  - audio       - Direct Stream Digital (DSD) Storage Facility files (DSF) - https://en.wikipedia.org/wiki/Direct_Stream_Digital
                    'dsf' => ['pattern' => '^DSD ','group' => 'audio','module' => 'dsf','mime_type' => 'audio/dsd',],
                    // DSS  - audio       - Digital Speech Standard
                    'dss'  => ['pattern' => '^[\\x02-\\x08]ds[s2]','group' => 'audio','module' => 'dss','mime_type' => 'application/octet-stream',],
                    // DSDIFF - audio     - Direct Stream Digital Interchange File Format
                    'dsdiff' => ['pattern' => '^FRM8','group' => 'audio','module' => 'dsdiff','mime_type' => 'audio/dsd',],
                    // DTS  - audio       - Dolby Theatre System
                    'dts' => ['pattern' => '^\\x7F\\xFE\\x80\\x01','group' => 'audio','module' => 'dts','mime_type' => 'audio/dts',],
                    // FLAC - audio       - Free Lossless Audio Codec
                    'flac' => ['pattern' => '^fLaC','group' => 'audio','module' => 'flac','mime_type' => 'audio/flac',],
                    // LA   - audio       - Lossless Audio (LA)
                    'la'   => ['pattern' => '^LA0[2-4]','group' => 'audio','module' => 'la','mime_type' => 'application/octet-stream',],
                    // LPAC - audio       - Lossless Predictive Audio Compression (LPAC)
                    'lpac' => ['pattern' => '^LPAC','group' => 'audio','module' => 'lpac','mime_type' => 'application/octet-stream',],
                    // MIDI - audio       - MIDI (Musical Instrument Digital Interface)
                    'midi' => ['pattern' => '^MThd','group' => 'audio','module' => 'midi','mime_type' => 'audio/midi',],
                    // MAC  - audio       - Monkey's Audio Compressor
                    'mac'  => ['pattern' => '^MAC ','group' => 'audio','module' => 'monkey','mime_type' => 'audio/x-monkeys-audio',],
                    // MOD  - audio       - MODule (Impulse Tracker)
                    'it'   => ['pattern' => '^IMPM','group' => 'audio','module' => 'mod','mime_type' => 'audio/it',],
                    // MOD  - audio       - MODule (eXtended Module, various sub-formats)
                    'xm' => ['pattern' => '^Extended Module','group' => 'audio','module' => 'mod','mime_type' => 'audio/xm',],
                    // MOD  - audio       - MODule (ScreamTracker)
                    's3m' => ['pattern' => '^.{44}SCRM','group' => 'audio','module' => 'mod','mime_type' => 'audio/s3m',],
                    // MPC  - audio       - Musepack / MPEGplus
                    'mpc' => ['pattern' => '^(MPCK|MP\\+|[\\x00\\x01\\x10\\x11\\x40\\x41\\x50\\x51\\x80\\x81\\x90\\x91\\xC0\\xC1\\xD0\\xD1][\\x20-\\x37][\\x00\\x20\\x40\\x60\\x80\\xA0\\xC0\\xE0])',
                        'group' => 'audio','module' => 'mpc','mime_type' => 'audio/x-musepack',],
                    // MP3  - audio       - MPEG-audio Layer 3 (very similar to AAC-ADTS)
                    'mp3' => ['pattern' => '^\\xFF[\\xE2-\\xE7\\xF2-\\xF7\\xFA-\\xFF][\\x00-\\x0B\\x10-\\x1B\\x20-\\x2B\\x30-\\x3B\\x40-\\x4B\\x50-\\x5B\\x60-\\x6B\\x70-\\x7B\\x80-\\x8B\\x90-\\x9B\\xA0-\\xAB\\xB0-\\xBB\\xC0-\\xCB\\xD0-\\xDB\\xE0-\\xEB\\xF0-\\xFB]',
                        'group' => 'audio','module' => 'mp3','mime_type' => 'audio/mpeg',],
                    // OFR  - audio       - OptimFROG
                    'ofr'  => ['pattern' => '^(\\*RIFF|OFR)','group' => 'audio','module' => 'optimfrog','mime_type' => 'application/octet-stream',],
                    // RKAU - audio       - RKive AUdio compressor
                    'rkau' => ['pattern' => '^RKA','group' => 'audio','module' => 'rkau','mime_type' => 'application/octet-stream',],
                    // SHN  - audio       - Shorten
                    'shn' => ['pattern' => '^ajkg','group' => 'audio','module' => 'shorten','mime_type' => 'audio/xmms-shn','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // TAK  - audio       - Tom's lossless Audio Kompressor
                    'tak' => ['pattern' => '^tBaK','group' => 'audio','module' => 'tak','mime_type' => 'application/octet-stream',],
                    // TTA  - audio       - TTA Lossless Audio Compressor (http://tta.corecodec.org)
                    'tta' => ['pattern' => '^TTA','group' => 'audio','module' => 'tta','mime_type' => 'application/octet-stream',],
                    // VOC  - audio       - Creative Voice (VOC)
                    'voc' => ['pattern' => '^Creative Voice File','group' => 'audio','module' => 'voc','mime_type' => 'audio/voc',],
                    // VQF  - audio       - transform-domain weighted interleave Vector Quantization Format (VQF)
                    'vqf' => ['pattern' => '^TWIN','group' => 'audio','module' => 'vqf','mime_type' => 'application/octet-stream',],
                    // WV  - audio        - WavPack (v4.0+)
                    'wv' => ['pattern' => '^wvpk','group' => 'audio','module' => 'wavpack','mime_type' => 'application/octet-stream',],
                    // Audio-Video formats
                    // ASF  - audio/video - Advanced Streaming Format, Windows Media Video, Windows Media Audio
                    'asf' => ['pattern' => '^\\x30\\x26\\xB2\\x75\\x8E\\x66\\xCF\\x11\\xA6\\xD9\\x00\\xAA\\x00\\x62\\xCE\\x6C',
                        'group' => 'audio-video','module' => 'asf','mime_type' => 'video/x-ms-asf','iconv_req' => false,],
                    // BINK - audio/video - Bink / Smacker
                    'bink' => ['pattern' => '^(BIK|SMK)','group' => 'audio-video','module' => 'bink','mime_type' => 'application/octet-stream',],
                    // FLV  - audio/video - FLash Video
                    'flv' => ['pattern' => '^FLV[\\x01]','group' => 'audio-video','module' => 'flv','mime_type' => 'video/x-flv',],
                    // IVF - audio/video - IVF
                    'ivf' => ['pattern' => '^DKIF','group' => 'audio-video','module' => 'ivf','mime_type' => 'video/x-ivf',],
                    // MKAV - audio/video - Mastroka
                    'matroska' => ['pattern' => '^\\x1A\\x45\\xDF\\xA3','group' => 'audio-video','module' => 'matroska','mime_type' => 'video/x-matroska', ],
                    // MPEG - audio/video - MPEG (Moving Pictures Experts Group)
                    'mpeg' => ['pattern' => '^\\x00\\x00\\x01[\\xB3\\xBA]','group' => 'audio-video',
                        'module' => 'mpeg','mime_type' => 'video/mpeg',],
                    // NSV  - audio/video - Nullsoft Streaming Video (NSV)
                    'nsv'  => ['pattern' => '^NSV[sf]','group' => 'audio-video','module' => 'nsv','mime_type' => 'application/octet-stream',],
                    // Ogg  - audio/video - Ogg (Ogg-Vorbis, Ogg-FLAC, Speex, Ogg-Theora(*), Ogg-Tarkin(*))
                    'ogg' => ['pattern' => '^OggS','group' => 'audio','module' => 'ogg',
                        'mime_type' => 'application/ogg','fail_id3' => 'WARNING','fail_ape' => 'WARNING',],
                    // QT   - audio/video - Quicktime
                    'quicktime' => ['pattern' => '^.{4}(cmov|free|ftyp|mdat|moov|pnot|skip|wide)',
                        'group' => 'audio-video','module' => 'quicktime','mime_type' => 'video/quicktime',],
                    // RIFF - audio/video - Resource Interchange File Format (RIFF) / WAV / AVI / CD-audio / SDSS = renamed variant used by SmartSound QuickTracks (www.smartsound.com) / FORM = Audio Interchange File Format (AIFF)
                    'riff' => ['pattern' => '^(RIFF|SDSS|FORM)','group' => 'audio-video','module' => 'riff','mime_type' => 'audio/wav','fail_ape' => 'WARNING',],
                    // Real - audio/video - RealAudio, RealVideo
                    'real' => ['pattern' => '^\\.(RMF|ra)','group' => 'audio-video','module' => 'real','mime_type' => 'audio/x-realaudio',],
                    // SWF - audio/video - ShockWave Flash
                    'swf' => ['pattern' => '^(F|C)WS','group' => 'audio-video','module' => 'swf','mime_type' => 'application/x-shockwave-flash',],
                    // TS - audio/video - MPEG-2 Transport Stream
                    'ts' => ['pattern' => '^(\\x47.{187}){10,}','group' => 'audio-video','module' => 'ts','mime_type' => 'video/MP2T',],
                    // WTV - audio/video - Windows Recorded TV Show
                    'wtv' => ['pattern' => '^\\xB7\\xD8\\x00\\x20\\x37\\x49\\xDA\\x11\\xA6\\x4E\\x00\\x07\\xE9\\x5E\\xAD\\x8D',
                        'group' => 'audio-video','module' => 'wtv','mime_type' => 'video/x-ms-wtv',],
                    // Still-Image formats
                    // BMP  - still image - Bitmap (Windows, OS/2; uncompressed, RLE8, RLE4)
                    'bmp' => ['pattern' => '^BM','group' => 'graphic','module' => 'bmp','mime_type' => 'image/bmp','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // GIF  - still image - Graphics Interchange Format
                    'gif' => ['pattern' => '^GIF','group' => 'graphic','module' => 'gif','mime_type' => 'image/gif','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // JPEG - still image - Joint Photographic Experts Group (JPEG)
                    'jpg' => ['pattern' => '^\\xFF\\xD8\\xFF','group' => 'graphic','module' => 'jpg',
                        'mime_type' => 'image/jpeg','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // PCD  - still image - Kodak Photo CD
                    'pcd'  => ['pattern' => '^.{2048}PCD_IPI\\x00','group' => 'graphic','module' => 'pcd',
                        'mime_type' => 'image/x-photo-cd','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // PNG  - still image - Portable Network Graphics (PNG)
                    'png' => ['pattern' => '^\\x89\\x50\\x4E\\x47\\x0D\\x0A\\x1A\\x0A',
                        'group' => 'graphic','module' => 'png','mime_type' => 'image/png','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // SVG  - still image - Scalable Vector Graphics (SVG)
                    'svg'  => ['pattern' => '(<!DOCTYPE svg PUBLIC |xmlns="http://www\\.w3\\.org/2000/svg")',
                        'group' => 'graphic','module' => 'svg','mime_type' => 'image/svg+xml','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // TIFF - still image - Tagged Information File Format (TIFF)
                    'tiff' => ['pattern' => '^(II\\x2A\\x00|MM\\x00\\x2A)','group' => 'graphic',
                        'module' => 'tiff','mime_type' => 'image/tiff','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // EFAX - still image - eFax (TIFF derivative)
                    'efax' => ['pattern' => '^\\xDC\\xFE','group' => 'graphic','module' => 'efax','mime_type' => 'image/efax','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // Data formats
                    // ISO  - data        - International Standards Organization (ISO) CD-ROM Image
                    'iso'  => ['pattern' => '^.{32769}CD001','group' => 'misc','module' => 'iso','mime_type' => 'application/octet-stream',
                        'fail_id3' => 'ERROR','fail_ape' => 'ERROR','iconv_req' => false,],
                    // HPK  - data        - HPK compressed data
                    'hpk' => ['pattern' => '^BPUL','group' => 'archive','module' => 'hpk',
                        'mime_type' => 'application/octet-stream','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // RAR  - data        - RAR compressed data
                    'rar'  => ['pattern' => '^Rar\\!','group' => 'archive','module' => 'rar',
                        'mime_type' => 'application/vnd.rar','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // SZIP - audio/data  - SZIP compressed data
                    'szip' => ['pattern' => '^SZ\\x0A\\x04','group' => 'archive','module' => 'szip','mime_type' => 'application/octet-stream','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // TAR  - data        - TAR compressed data
                    'tar'  => ['pattern' => '^.{100}[0-9\\x20]{7}\\x00[0-9\\x20]{7}\\x00[0-9\\x20]{7}\\x00[0-9\\x20\\x00]{12}[0-9\\x20\\x00]{12}',
                        'group' => 'archive','module' => 'tar','mime_type' => 'application/x-tar','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // GZIP  - data        - GZIP compressed data
                    'gz'  => ['pattern' => '^\\x1F\\x8B\\x08','group' => 'archive','module' => 'gzip',
                        'mime_type' => 'application/gzip','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // ZIP  - data         - ZIP compressed data
                    'zip'  => ['pattern' => '^PK\\x03\\x04','group' => 'archive','module' => 'zip',
                        'mime_type' => 'application/zip','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // XZ   - data         - XZ compressed data
                    'xz'  => ['pattern' => '^\\xFD7zXZ\\x00','group' => 'archive','module' => 'xz','mime_type' => 'application/x-xz','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // Misc other formats
                    // PAR2 - data        - Parity Volume Set Specification 2.0
                    'par2' => ['pattern' => '^PAR2\\x00PKT','group' => 'misc','module' => 'par2',
                        'mime_type' => 'application/octet-stream','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // PDF  - data        - Portable Document Format
                    'pdf'  => ['pattern' => '^\\x25PDF','group' => 'misc','module' => 'pdf','mime_type' => 'application/pdf','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // MSOFFICE  - data   - ZIP compressed data
                    'msoffice' => ['pattern' => '^\\xD0\\xCF\\x11\\xE0\\xA1\\xB1\\x1A\\xE1', // D0CF11E == DOCFILE == Microsoft Office Document
                        'group' => 'misc','module' => 'msoffice','mime_type' => 'application/octet-stream','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    // TORRENT             - .torrent
                    'torrent' => ['pattern' => '^(d8\\:announce|d7\\:comment)','group' => 'misc',
                        'module' => 'torrent','mime_type' => 'application/x-bittorrent','fail_id3' => 'ERROR','fail_ape' => 'ERROR',],
                    'cue' => ['pattern' => '', 'group' => 'misc','module' => 'cue','mime_type' => 'application/octet-stream',],
                ];
            }
            return $format_info;
        }//911
        public function GetFileFormat(&$filedata, $filename='') {
            foreach ($this->GetFileFormatArray() as $format_name => $info) {
                if (!empty($info['pattern']) && preg_match('#'.$info['pattern'].'#s', $filedata)) {
                    $info['include'] = 'module.'.$info['group'].'.'.$info['module'].'.php';
                    return $info;
                }
            }
            if (preg_match('#\\.mp[123a]$#i', $filename)) {
                $GetFileFormatArray = $this->GetFileFormatArray();
                $info = $GetFileFormatArray['mp3'];
                $info['include'] = 'module.'.$info['group'].'.'.$info['module'].'.php';
                return $info;
            }
            if (preg_match('#\\.cue$#i', $filename) && preg_match('#FILE "[^"]+" (BINARY|MOTOROLA|AIFF|WAVE|MP3)#', $filedata)) {
                $GetFileFormatArray = $this->GetFileFormatArray();
                $info = $GetFileFormatArray['cue'];
                $info['include']   = 'module.'.$info['group'].'.'.$info['module'].'.php';
                return $info;
            }
            return false;
        }//1530
        public function CharConvert(&$array, $encoding):void {
            if ($encoding === $this->encoding) {
                return;
            }
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $this->CharConvert($array[$key], $encoding);
                }
                elseif (is_string($value)) {
                    $array[$key] = trim(getid3_lib::iconv_fallback($encoding, $this->encoding, $value));
                }
            }
        }//1573
        public function HandleAllTags():bool{
            static $tags;
            if (empty($tags)) {
                $tags = [
                    'asf'       => array('asf'           , 'UTF-16LE'),
                    'midi'      => array('midi'          , 'ISO-8859-1'),
                    'nsv'       => array('nsv'           , 'ISO-8859-1'),
                    'ogg'       => array('vorbiscomment' , 'UTF-8'),
                    'png'       => array('png'           , 'UTF-8'),
                    'tiff'      => array('tiff'          , 'ISO-8859-1'),
                    'quicktime' => array('quicktime'     , 'UTF-8'),
                    'real'      => array('real'          , 'ISO-8859-1'),
                    'vqf'       => array('vqf'           , 'ISO-8859-1'),
                    'zip'       => array('zip'           , 'ISO-8859-1'),
                    'riff'      => array('riff'          , 'ISO-8859-1'),
                    'lyrics3'   => array('lyrics3'       , 'ISO-8859-1'),
                    'id3v1'     => array('id3v1'         , $this->encoding_id3v1),
                    'id3v2'     => array('id3v2'         , 'UTF-8'), // not according to the specs (every frame can have a different encoding), but getID3() force-converts all encodings to UTF-8
                    'ape'       => array('ape'           , 'UTF-8'),
                    'cue'       => array('cue'           , 'ISO-8859-1'),
                    'matroska'  => array('matroska'      , 'UTF-8'),
                    'flac'      => array('vorbiscomment' , 'UTF-8'),
                    'divxtag'   => array('divx'          , 'ISO-8859-1'),
                    'iptc'      => array('iptc'          , 'ISO-8859-1'),
                    'dsdiff'    => array('dsdiff'        , 'ISO-8859-1'),
                ];
            }
            foreach ($tags as $comment_name => $tagname_encoding_array) {
                @list($tag_name, $encoding) = $tagname_encoding_array;
                if (isset($this->info[$comment_name]) && !isset($this->info[$comment_name]['encoding'])) {
                    $this->info[$comment_name]['encoding'] = $encoding;
                }
                if (!empty($this->info[$comment_name]['comments'])) {
                    foreach ($this->info[$comment_name]['comments'] as $tag_key => $valuearray) {
                        foreach ($valuearray as $key => $value) {
                            if (is_string($value)) {
                                $value = trim($value, " \r\n\t"); // do not trim nulls from $value!! Unicode characters will get mangled if trailing nulls are removed!
                            }
                            if (isset($value) && $value !== "") {
                                if (!is_numeric($key)) {
                                    $this->info['tags'][trim($tag_name)][trim($tag_key)][$key] = $value;
                                } else {
                                    $this->info['tags'][trim($tag_name)][trim($tag_key)][]     = $value;
                                }
                            }
                        }
                        if ($tag_key === 'picture') {
                            unset($this->info[$comment_name]['comments'][$tag_key]);
                        }
                    }
                    if (!isset($this->info['tags'][$tag_name])) {
                        continue;
                    }
                    $this->CharConvert($this->info['tags'][$tag_name], $this->info[$comment_name]['encoding']);           // only copy gets converted!
                    if ($this->option_tags_html) {
                        foreach ($this->info['tags'][$tag_name] as $tag_key => $valuearray) {
                            if ($tag_key === 'picture') {
                                continue;
                            }
                            $this->info['tags_html'][$tag_name][$tag_key] = getid3_lib::recursiveMultiByteCharString2HTML($valuearray, $this->info[$comment_name]['encoding']);
                        }
                    }
                }
            }
            // pictures can take up a lot of space, and we don't need multiple copies of them; let there be a single copy in [comments][picture], and not elsewhere
            if (!empty($this->info['tags'])) {
                $unset_keys = array('tags', 'tags_html');
                foreach ($this->info['tags'] as $tagtype => $tagarray) {
                    foreach ($tagarray as $tagname => $tagdata) {
                        if ($tagname === 'picture') {
                            /** @noinspection SuspiciousLoopInspection */
                            foreach ((array)$tagdata as $key => $tagarray) {
                                $this->info['comments']['picture'][] = $tagarray;
                                if (isset($tagarray['data'],$tagarray['image_mime'])) {
                                    if (isset($this->info['tags'][$tagtype][$tagname][$key])) {
                                        unset($this->info['tags'][$tagtype][$tagname][$key]);
                                    }
                                    if (isset($this->info['tags_html'][$tagtype][$tagname][$key])) {
                                        unset($this->info['tags_html'][$tagtype][$tagname][$key]);
                                    }
                                }
                            }
                        }
                    }
                    foreach ($unset_keys as $unset_key) {
                        // remove possible empty keys from (e.g. [tags][id3v2][picture])
                        if (empty($this->info[$unset_key][$tagtype]['picture'])) {
                            unset($this->info[$unset_key][$tagtype]['picture']);
                        }
                        if (empty($this->info[$unset_key][$tagtype])) {
                            unset($this->info[$unset_key][$tagtype]);
                        }
                        if (empty($this->info[$unset_key])) {
                            unset($this->info[$unset_key]);
                        }
                    }
                    // remove duplicate copy of picture data from (e.g. [id3v2][comments][picture])
                    if (isset($this->info[$tagtype]['comments']['picture'])) {
                        unset($this->info[$tagtype]['comments']['picture']);
                    }
                    if (empty($this->info[$tagtype]['comments'])) {
                        unset($this->info[$tagtype]['comments']);
                    }
                    if (empty($this->info[$tagtype])) {
                        unset($this->info[$tagtype]);
                    }
                }
            }
            return true;
        }//1598
        public function CopyTagsToComments(&$ThisFileInfo) {
            return getid3_lib::CopyTagsToComments($ThisFileInfo, $this->option_tags_html);
        }//1733
        public function getHashdata($algorithm):bool{
            switch ($algorithm) {
                case 'md5':
                case 'sha1':
                    break;
                default:
                    return $this->error('bad algorithm "'.$algorithm.'" in getHashdata()');
            }
            if (!empty($this->info['fileformat']) && !empty($this->info['dataformat']) && ($this->info['fileformat'] === 'ogg') && ($this->info['audio']['dataformat'] === 'vorbis')) {
                // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
                /** @noinspection DeprecatedIniOptionsInspection */
                if (preg_match('#(1|ON)#i', ini_get('safe_mode'))) {
                    $this->warning('Failed making system call to vorbiscomment.exe - '.$algorithm.'_data is incorrect - error returned: PHP running in Safe Mode (backtick operator not available)');
                    $this->info[$algorithm.'_data'] = false;
                } else {
                    $old_abort = ignore_user_abort(true);
                    $empty = tempnam(GETID3_TEMP_DIR, 'getID3');
                    touch($empty);
                    $temp = tempnam(GETID3_TEMP_DIR, 'getID3');
                    $file = $this->info['filenamepath'];
                    if (GETID3_OS_ISWINDOWS) {
                        if (file_exists(GETID3_HELPERAPPSDIR.'vorbiscomment.exe')) {
                            $commandline = '"'.GETID3_HELPERAPPSDIR.'vorbiscomment.exe" -w -c "'.$empty.'" "'.$file.'" "'.$temp.'"';
                            $VorbisCommentError = shell_exec($commandline);
                        } else {
                            $VorbisCommentError = 'vorbiscomment.exe not found in '.GETID3_HELPERAPPSDIR;
                        }
                    } else {
                        $commandline = 'vorbiscomment -w -c '.escapeshellarg($empty).' '.escapeshellarg($file).' '.escapeshellarg($temp).' 2>&1';
                        $VorbisCommentError = shell_exec($commandline);
                    }
                    if (!empty($VorbisCommentError)) {
                        $this->warning('Failed making system call to vorbiscomment(.exe) - '.$algorithm.'_data will be incorrect. If vorbiscomment is unavailable, please download from http://www.vorbis.com/download.psp and put in the getID3() directory. Error returned: '.$VorbisCommentError);
                        $this->info[$algorithm.'_data'] = false;
                    } else {
                        switch ($algorithm) {
                            case 'md5':
                                $this->info[$algorithm.'_data'] = md5_file($temp);
                                break;
                            case 'sha1':
                                $this->info[$algorithm.'_data'] = sha1_file($temp);
                                break;
                        }
                    }
                    unlink($empty);
                    unlink($temp);
                    ignore_user_abort($old_abort);
                }
            } else if (!empty($this->info['avdataoffset']) || (isset($this->info['avdataend']) && ($this->info['avdataend'] < $this->info['filesize']))) {
                $this->info[$algorithm.'_data'] = getid3_lib::hash_data($this->info['filenamepath'], $this->info['avdataoffset'], $this->info['avdataend'], $algorithm);
            } else {
                switch ($algorithm) {
                    case 'md5':
                        $this->info[$algorithm.'_data'] = md5_file($this->info['filenamepath']);
                        break;
                    case 'sha1':
                        $this->info[$algorithm.'_data'] = sha1_file($this->info['filenamepath']);
                        break;
                }
            }
            return true;
        }//1742
        public function ChannelsBitratePlaytimeCalculations():void {
            // set channelmode on audio
            if (!empty($this->info['audio']['channelmode']) || !isset($this->info['audio']['channels'])) {
               // ignore
            } elseif ($this->info['audio']['channels'] === 1) {
                $this->info['audio']['channelmode'] = 'mono';
            } elseif ($this->info['audio']['channels'] === 2) {
                $this->info['audio']['channelmode'] = 'stereo';
            }
            $CombinedBitrate  = 0;
            $CombinedBitrate += ($this->info['audio']['bitrate'] ?? 0);
            $CombinedBitrate += ($this->info['video']['bitrate'] ?? 0);
            if (($CombinedBitrate > 0) && empty($this->info['bitrate'])) {
                $this->info['bitrate'] = $CombinedBitrate;
            }
            if (isset($this->info['video']['dataformat'], $this->info['audio']['bitrate'], $this->info['playtime_seconds'], $this->info['avdataend'], $this->info['avdataoffset']) && $this->info['video']['dataformat'] && (!isset($this->info['video']['bitrate']) || ($this->info['video']['bitrate'] === 0)) && ($this->info['audio']['bitrate'] > 0) && ($this->info['audio']['bitrate'] === $this->info['bitrate']) && ($this->info['playtime_seconds'] > 0)) {
               $this->info['bitrate'] = round((($this->info['avdataend'] - $this->info['avdataoffset']) * 8) / $this->info['playtime_seconds']);
               $this->info['video']['bitrate'] = $this->info['bitrate'] - $this->info['audio']['bitrate'];
            }
            if ((!isset($this->info['playtime_seconds']) || ($this->info['playtime_seconds'] <= 0)) && !empty($this->info['bitrate'])) {
                $this->info['playtime_seconds'] = (($this->info['avdataend'] - $this->info['avdataoffset']) * 8) / $this->info['bitrate'];
            }
            if (!isset($this->info['bitrate']) && !empty($this->info['playtime_seconds'])) {
                $this->info['bitrate'] = (($this->info['avdataend'] - $this->info['avdataoffset']) * 8) / $this->info['playtime_seconds'];
            }
            if (isset($this->info['bitrate']) && empty($this->info['audio']['bitrate']) && empty($this->info['video']['bitrate'])) {
                if (isset($this->info['audio']['dataformat']) && empty($this->info['video']['resolution_x'])) {
                     $this->info['audio']['bitrate'] = $this->info['bitrate'];
                } elseif (isset($this->info['video']['resolution_x']) && empty($this->info['audio']['dataformat'])) {
                    $this->info['video']['bitrate'] = $this->info['bitrate'];
                }
            }
            if (!empty($this->info['playtime_seconds']) && empty($this->info['playtime_string'])) {
                $this->info['playtime_string'] = getid3_lib::PlaytimeString($this->info['playtime_seconds']);
            }
        }//1864
        public function CalculateCompressionRatioVideo():bool {
            if (empty($this->info['video'])) {
                return false;
            }
            if (empty($this->info['video']['resolution_x']) || empty($this->info['video']['resolution_y'])) {
                return false;
            }
            if (empty($this->info['video']['bits_per_sample'])) {
                return false;
            }
            switch ($this->info['video']['dataformat']) {
                case 'bmp':
                case 'gif':
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'tiff':
                    $FrameRate = 1;
                    /** @noinspection PhpUnusedLocalVariableInspection */
                    $PlaytimeSeconds = 1;
                    $BitrateCompressed = $this->info['filesize'] * 8;
                    break;
                default:
                    if (!empty($this->info['video']['frame_rate'])) {
                        $FrameRate = $this->info['video']['frame_rate'];
                    } else {
                        return false;
                    }
                    if (!empty($this->info['playtime_seconds'])) {
                        /** @noinspection PhpUnusedLocalVariableInspection */
                        $PlaytimeSeconds = $this->info['playtime_seconds'];
                    } else {
                        return false;
                    }
                    if (!empty($this->info['video']['bitrate'])) {
                        $BitrateCompressed = $this->info['video']['bitrate'];
                    } else {
                        return false;
                    }
                    break;
            }
            $BitrateUncompressed = $this->info['video']['resolution_x'] * $this->info['video']['resolution_y'] * $this->info['video']['bits_per_sample'] * $FrameRate;
            $this->info['video']['compression_ratio'] = $BitrateCompressed / $BitrateUncompressed;
            return true;
        }//1931
        public function CalculateCompressionRatioAudio():bool {
            if (empty($this->info['audio']['bitrate']) || empty($this->info['audio']['channels']) || empty($this->info['audio']['sample_rate']) || !is_numeric($this->info['audio']['sample_rate'])) {
                return false;
            }
            $this->info['audio']['compression_ratio'] = $this->info['audio']['bitrate'] / ($this->info['audio']['channels'] * $this->info['audio']['sample_rate'] * (!empty($this->info['audio']['bits_per_sample']) ? $this->info['audio']['bits_per_sample'] : 16));
            if (!empty($this->info['audio']['streams'])) {
                foreach ($this->info['audio']['streams'] as $streamnumber => $streamdata) {
                    if (!empty($streamdata['bitrate']) && !empty($streamdata['channels']) && !empty($streamdata['sample_rate'])) {
                        $this->info['audio']['streams'][$streamnumber]['compression_ratio'] = $streamdata['bitrate'] / ($streamdata['channels'] * $streamdata['sample_rate'] * (!empty($streamdata['bits_per_sample']) ? $streamdata['bits_per_sample'] : 16));
                    }
                }
            }
            return true;
        }//1981
        public function CalculateReplayGain():bool {
            if (isset($this->info['replay_gain'])) {
                if (!isset($this->info['replay_gain']['reference_volume'])) {
                    $this->info['replay_gain']['reference_volume'] = 89.0;
                }
                if (isset($this->info['replay_gain']['track']['adjustment'])) {
                    $this->info['replay_gain']['track']['volume'] = $this->info['replay_gain']['reference_volume'] - $this->info['replay_gain']['track']['adjustment'];
                }
                if (isset($this->info['replay_gain']['album']['adjustment'])) {
                    $this->info['replay_gain']['album']['volume'] = $this->info['replay_gain']['reference_volume'] - $this->info['replay_gain']['album']['adjustment'];
                }

                if (isset($this->info['replay_gain']['track']['peak'])) {
                    $this->info['replay_gain']['track']['max_noclip_gain'] = 0 - getid3_lib::RGADamplitude2dB($this->info['replay_gain']['track']['peak']);
                }
                if (isset($this->info['replay_gain']['album']['peak'])) {
                    $this->info['replay_gain']['album']['max_noclip_gain'] = 0 - getid3_lib::RGADamplitude2dB($this->info['replay_gain']['album']['peak']);
                }
            }
            return true;
        }//2000
        public function ProcessAudioStreams():bool {
            if (!empty($this->info['audio']['bitrate']) || !empty($this->info['audio']['channels']) || !empty($this->info['audio']['sample_rate'])) {
                if (!isset($this->info['audio']['streams'])) {
                    foreach ($this->info['audio'] as $key => $value) {
                        if ($key !== 'streams') {
                            $this->info['audio']['streams'][0][$key] = $value;
                        }
                    }
                }
            }
            return true;
        }//2025
        public function getid3_tempnam():string {
            return tempnam($this->tempdir, 'gI3');
        }//2041
        /**
         * @param $module
         * @param array ...$args
         * @return object
         */
        public function load_module($module, ...$args):object{
            $located = '';
            $module_name = null;
            if(isset($module)){
                $module_name = GETID3_NAMESPACE_PATH.$module;
            }
            if(class_exists($module_name)){
                $located = new $module_name($args);
            }
            return (object)$located;
        }
        public static function is_writable ($filename) {
            $ret = is_writable($filename);
            if (!$ret) {
                $perms = fileperms($filename);
                $ret = ($perms & 0x0080) || ($perms & 0x0010) || ($perms & 0x0002);
            }
            return $ret;
        }//2066
    }
}else{die;}