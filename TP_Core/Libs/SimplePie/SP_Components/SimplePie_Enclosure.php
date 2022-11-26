<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 11:12
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Depedencies\idna_convert;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_01;
if(ABSPATH){
    class SimplePie_Enclosure{
        use _sp_vars;
        use _encoding_01;
        public function __construct($link = null, $type = null, $length = null, $javascript = null, $bitrate = null, $captions = null, $categories = null, $channels = null, $copyright = null, $credits = null, $description = null, $duration = null, $expression = null, $framerate = null, $hashes = null, $height = null, $keywords = null, $lang = null, $medium = null, $player = null, $ratings = null, $restrictions = null, $samplingrate = null, $thumbnails = null, $title = null, $width = null){
            $this->sp_bitrate = $bitrate;
            $this->sp_captions = $captions;
            $this->sp_categories = $categories;
            $this->sp_channels = $channels;
            $this->sp_copyright = $copyright;
            $this->sp_credits = $credits;
            $this->sp_description = $description;
            $this->sp_duration = $duration;
            $this->sp_expression = $expression;
            $this->sp_framerate = $framerate;
            $this->sp_hashes = $hashes;
            $this->sp_height = $height;
            $this->sp_keywords = $keywords;
            $this->sp_lang = $lang;
            $this->sp_length = $length;
            $this->sp_link = $link;
            $this->sp_medium = $medium;
            $this->sp_player = $player;
            $this->sp_ratings = $ratings;
            $this->sp_restrictions = $restrictions;
            $this->sp_samplingrate = $samplingrate;
            $this->sp_thumbnails = $thumbnails;
            $this->sp_title = $title;
            $this->sp_type = $type;
            $this->sp_width = $width;
            if (class_exists('TP_Managers\SimplePie_Manager\Depedencies\idna_convert')){
                $idn = new idna_convert();
                $parsed = $this->sp_parse_url($link);
                $this->sp_link = $this->sp_compress_parse_url($parsed['scheme'], $idn->encode($parsed['authority']), $parsed['path'], $parsed['query'], $parsed['fragment']);
            }
            $this->sp_handler = $this->get_handler(); // Needs to load last
        }
        public function __toString(){
            // There is no $this->data here
            return (string)md5(serialize($this));
        }
        public function get_bitrate(){
            if ($this->sp_bitrate !== null) return $this->sp_bitrate;
            return null;
        }
        public function get_caption($key = 0){
            $captions = $this->get_captions();
            if (isset($captions[$key])) return $captions[$key];
            return null;
        }
        public function get_captions(){
            if ($this->sp_captions !== null) return $this->sp_captions;
            return null;
        }
        public function get_category($key = 0){
            $categories = $this->get_categories();
            if (isset($categories[$key])) return $categories[$key];
            return null;
        }
        public function get_categories(){
            if ($this->sp_categories !== null) return $this->sp_categories;
            return null;
        }
        public function get_channels() {
            if ($this->sp_channels !== null) return $this->sp_channels;
            return null;
        }
        public function get_copyright(){
            if ($this->sp_copyright !== null) return $this->sp_copyright;
            return null;
        }
        public function get_credit($key = 0){
            $credits = $this->get_credits();
            if (isset($credits[$key])) return $credits[$key];
            return null;
        }
        public function get_credits(){
            if ($this->sp_credits !== null) return $this->sp_credits;
            return null;
        }
        public function get_description(){
            if ($this->sp_description !== null) return $this->sp_description;
            return null;
        }
        public function get_duration($convert = false){
            if ($this->sp_duration !== null){
                if ($convert){
                    return $this->sp_time_hms($this->sp_duration);
                }
                return $this->sp_duration;
            }
            return null;
        }
        public function get_expression():string{
            if ($this->sp_expression !== null) return $this->sp_expression;
            return 'full';
        }
        public function get_extension(){
            if ($this->sp_link !== null){
                $url = $this->sp_parse_url($this->sp_link);
                if ($url['path'] !== '') return pathinfo($url['path'], PATHINFO_EXTENSION);
            }
            return null;
        }
        public function get_framerate(){
            if ($this->sp_framerate !== null) return $this->sp_framerate;
            return null;
        }
        public function get_handler(){
            return $this->get_real_type(true);
        }
        public function get_hash($key = 0){
            $hashes = $this->get_hashes();
            if (isset($hashes[$key])) return $hashes[$key];
            return null;
        }
        public function get_hashes(){
            if ($this->sp_hashes !== null) return $this->sp_hashes;
            return null;
        }
        public function get_height(){
            if ($this->sp_height !== null) return $this->sp_height;
            return null;
        }
        public function get_language(){
            if ($this->sp_lang !== null) return $this->sp_lang;
            return null;
        }
        public function get_keyword($key = 0) {
            $keywords = $this->get_keywords();
            if (isset($keywords[$key])) return $keywords[$key];
            return null;
        }
        public function get_keywords(){
            if ($this->sp_keywords !== null) return $this->sp_keywords;
            return null;
        }
        public function get_length(){
            if ($this->sp_length !== null) return $this->sp_length;
            return null;
        }
        public function get_link(){
            if ($this->sp_link !== null) return urldecode($this->sp_link);
            return null;
        }
        public function get_medium(){
            if ($this->sp_medium !== null) return $this->sp_medium;
            return null;
        }
        public function get_player(){
            if ($this->sp_player !== null) return $this->sp_player;
            return null;
        }
        public function get_rating($key = 0){
            $ratings = $this->get_ratings();
            if (isset($ratings[$key])) return $ratings[$key];
            return null;
        }
        public function get_ratings(){
            if ($this->sp_ratings !== null) return $this->sp_ratings;
            return null;
        }
        public function get_restriction($key = 0){
            $restrictions = $this->get_restrictions();
            if (isset($restrictions[$key]))
                return $restrictions[$key];
            return null;
        }
        public function get_restrictions(){
            if ($this->sp_restrictions !== null) return $this->sp_restrictions;
            return null;
        }
        public function get_sampling_rate(){
            if ($this->sp_samplingrate !== null) return $this->sp_samplingrate;
            return null;
        }
        public function get_size(){
            $length = $this->get_length();
            if ($length !== null) return round($length/1048576, 2);
            return null;
        }
        public function get_thumbnail($key = 0){
            $thumbnails = $this->get_thumbnails();
            if (isset($thumbnails[$key]))
                return $thumbnails[$key];
            return null;
        }
        public function get_thumbnails(){
            if ($this->sp_thumbnails !== null) return $this->sp_thumbnails;
            return null;
        }
        public function get_title(){
            if ($this->sp_title !== null) return $this->sp_title;
            return null;
        }
        public function get_type() {
            if ($this->sp_type !== null) return $this->sp_type;
            return null;
        }
        public function get_width(){
            if ($this->sp_width !== null) return $this->sp_width;
            return null;
        }
        public function native_embed(...$options):string{
            return $this->embed($options, true);
        }
        public function embed($native = false, ...$options):string{
            // Set up defaults
            $audio = '';
            $video = '';
            $alt = '';
            $alt_class = '';
            $loop = 'false';
            $width = 'auto';
            $height = 'auto';
            $bgcolor = '#ffffff';
            $media_player = '';
            $wide_screen = false;
            $handler = $this->get_handler();
            $type = $this->get_real_type();
            // Process options and reassign values as necessary
            if (is_array($options)) extract($options, []);
            else {
                $options = explode(',', $options);
                foreach($options as $option){
                    $opt = explode(':', $option, 2);
                    if (isset($opt[0], $opt[1])){
                        $opt[0] = trim($opt[0]);
                        $opt[1] = trim($opt[1]);
                        switch ($opt[0]){
                            case 'audio':
                                $audio = $opt[1];
                                break;
                            case 'video':
                                $video = $opt[1];
                                break;
                            case 'alt':
                                $alt = $opt[1];
                                break;
                            case 'alt_class':
                                $alt_class = $opt[1];
                                break;
                            case 'loop':
                                $loop = $opt[1];
                                break;
                            case 'width':
                                $width = $opt[1];
                                break;
                            case 'height':
                                $height = $opt[1];
                                break;
                            case 'bgcolor':
                                $bgcolor = $opt[1];
                                break;
                            case 'media_player':
                                $media_player = $opt[1];
                                break;
                            case 'wide_screen':
                                $wide_screen = $opt[1];
                                break;
                        }
                    }
                }
            }
            $mime = explode('/', $type, 2);
            $mime = $mime[0];
            // Process values for 'auto'
            if ($width === 'auto'){
                if ($mime === 'video'){
                    if ($height === 'auto') $width = 480;
                    elseif ($wide_screen) $width = round(((int)($height)/9)*16);
                    else $width = round(((int)($height)/3)*4);
                } else $width = '100%';
            }
            if ($height === 'auto'){
                if ($mime === 'audio') $height = 0;
                elseif ($mime === 'video'){
                    if ($width === 'auto'){
                        if ($wide_screen) $height = 270;
                        else $height = 360;
                    }
                    elseif ($wide_screen) $height = round(((int)($width)/16)*9);
                    else $height = round(((int)($width)/4)*3);
                }else $height = 376;
            }elseif ($mime === 'audio') $height = 0;
            // Set proper $this->sp_placeholder value
            $embed = '';
            if ($mime === 'audio') $this->sp_placeholder = $audio;
            elseif ($mime === 'video') $this->sp_placeholder = $video;
            // QuickTime 7 file types.  Need to test with QuickTime 6.
            elseif ($handler === 'quicktime' || ($handler === 'mp3' && $media_player === '')){
                $cursor = 'cursor:hand;cursor:pointer;';
                $height += 16;
                if ($native){
                    if ($this->sp_placeholder !== '')
                        $embed .= "<embed type='{$type}' style='{$cursor}' href='{$this->get_link()}' src='{$this->sp_placeholder}' width='{$width}' height='{$height}' autoplay='false' target='myself' controller='false' loop='{$loop}' scale='aspect' bgcolor='{$bgcolor}' pluginspage='http://apple.com/quicktime/download/'></embed>";
                    else $embed .= "<embed type='{$type}' style='{$cursor}' src='{$this->get_link()}' width='{$width}' height='{$height}' autoplay='false' target='myself' controller='true' loop='{$loop}' scale='aspect' bgcolor='{$bgcolor}' pluginspage='http://apple.com/quicktime/download/'></embed>";
                }
                else $embed .= "<script type='text/javascript'>embed_quicktime('$type', '$bgcolor', '$width', '$height', '" . $this->get_link() . "', '$this->sp_placeholder', '$loop');</script>";
            }
            // Windows Media
            elseif ($handler === 'wmedia'){
                $height += 45;
                if ($native) $embed .= "<embed type='application/x-mplayer2' src='{$this->get_link()}' autosize='1' width='{$width}' height='{$height}' showcontrols='1' showstatusbar='0' showdisplay='0' autostart='0'></embed>";
                else $embed .= "<script type='text/javascript'>embed_wmedia('$width', '$height', '" . $this->get_link() . "');</script>";
            } else $embed .= "<a href='{$this->get_link()}' class='{$alt_class}' >{$alt}</a>";
            return $embed;
        }
        public function get_real_type($find_handler = false) {
            // Mime-types by handler.
            $types_fmedia = array('video/flv', 'video/x-flv','flv-application/octet-stream'); // Flash Media Player
            $types_quicktime = array('audio/3gpp', 'audio/3gpp2', 'audio/aac', 'audio/x-aac', 'audio/aiff', 'audio/x-aiff', 'audio/mid', 'audio/midi', 'audio/x-midi', 'audio/mp4', 'audio/m4a', 'audio/x-m4a', 'audio/wav', 'audio/x-wav', 'video/3gpp', 'video/3gpp2', 'video/m4v', 'video/x-m4v', 'video/mp4', 'video/mpeg', 'video/x-mpeg', 'video/quicktime', 'video/sd-video'); // QuickTime
            $types_wmedia = array('application/asx', 'application/x-mplayer2', 'audio/x-ms-wma', 'audio/x-ms-wax', 'video/x-ms-asf-plugin', 'video/x-ms-asf', 'video/x-ms-wm', 'video/x-ms-wmv', 'video/x-ms-wvx'); // Windows Media
            $types_mp3 = array('audio/mp3', 'audio/x-mp3', 'audio/mpeg', 'audio/x-mpeg'); // MP3
            if ($this->get_type() !== null) $type = strtolower($this->sp_type);
            else $type = null;
            if (!in_array($type, array_merge($types_fmedia, $types_quicktime, $types_wmedia, $types_mp3),true)){
                $extension = $this->get_extension();
                if ($extension === null) return null;
                switch (strtolower($extension)){
                    // Audio mime-types
                    case 'aac':
                    case 'adts':
                        $type = 'audio/acc';
                        break;
                    case 'aif':
                    case 'aifc':
                    case 'aiff':
                    case 'cdda':
                        $type = 'audio/aiff';
                        break;
                    case 'bwf':
                        $type = 'audio/wav';
                        break;
                    case 'kar':
                    case 'mid':
                    case 'midi':
                    case 'smf':
                        $type = 'audio/midi';
                        break;
                    case 'm4a':
                        $type = 'audio/x-m4a';
                        break;
                    case 'mp3':
                    case 'swa':
                        $type = 'audio/mp3';
                        break;
                    case 'wav':
                        $type = 'audio/wav';
                        break;
                    case 'wax':
                        $type = 'audio/x-ms-wax';
                        break;
                    case 'wma':
                        $type = 'audio/x-ms-wma';
                        break;
                    // Video mime-types
                    case '3gp':
                    case '3gpp':
                        $type = 'video/3gpp';
                        break;
                    case '3g2':
                    case '3gp2':
                        $type = 'video/3gpp2';
                        break;
                    case 'asf':
                        $type = 'video/x-ms-asf';
                        break;
                    case 'flv':
                        $type = 'video/x-flv';
                        break;
                    case 'm1a':
                    case 'm1s':
                    case 'm1v':
                    case 'm15':
                    case 'm75':
                    case 'mp2':
                    case 'mpa':
                    case 'mpeg':
                    case 'mpg':
                    case 'mpm':
                    case 'mpv':
                        $type = 'video/mpeg';
                        break;
                    case 'm4v':
                        $type = 'video/x-m4v';
                        break;
                    case 'mov':
                    case 'qt':
                        $type = 'video/quicktime';
                        break;
                    case 'mp4':
                    case 'mpg4':
                        $type = 'video/mp4';
                        break;
                    case 'sdv':
                        $type = 'video/sd-video';
                        break;
                    case 'wm':
                        $type = 'video/x-ms-wm';
                        break;
                    case 'wmv':
                        $type = 'video/x-ms-wmv';
                        break;
                    case 'wvx':
                        $type = 'video/x-ms-wvx';
                        break;
                }
            }
            if ($find_handler) {
                if (in_array($type, $types_fmedia,true)) return 'fmedia';
                elseif (in_array($type, $types_quicktime,true)) return 'quicktime';
                elseif (in_array($type, $types_wmedia,true)) return 'wmedia';
                elseif (in_array($type, $types_mp3,true)) return 'mp3';
                return null;
            }
            return $type;
        }
    }
}else die;