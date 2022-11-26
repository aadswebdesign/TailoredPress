<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 16:15
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Parse_Date;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_IRI;
use TP_Core\Traits\Methods\_methods_20;

if(ABSPATH){
    trait _encoding_04{
        use _methods_20;
        protected $_sp_prepare;
        protected $_sp_quote;
        public $sp_object;
        //todo or this works?
        public function sp_output_javascript():string{
            $js = '';
            if (function_exists('ob_gzhandler'))
                ob_start('ob_gzhandler');
                header('Content-type: text/javascript; charset: UTF-8');
                header('Cache-Control: must-revalidate');
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT'); // 7 days
            ?>
            <!--suppress JSUnusedLocalSymbols -->
            <script>
            function embed_quicktime(type, bgcolor, width, height, link, placeholder, loop) {
                let cursor_style= 'cursor:hand; cursor:pointer;';
                if (placeholder != '') {
                    document.writeln('<embed type="'+type+'" style="' + cursor_style +'" href="'+link+'" src="'+placeholder+'" width="'+width+'" height="'+height+'" autoplay="false" target="myself" controller="false" loop="'+loop+'" scale="aspect" bgcolor="'+bgcolor+'" pluginspage="http://www.apple.com/quicktime/download/"></embed>');
                }
                else {
                    document.writeln('<embed type="'+type+'" style="' + cursor_style +'" src="'+link+'" width="'+width+'" height="'+height+'" autoplay="false" target="myself" controller="true" loop="'+loop+'" scale="aspect" bgcolor="'+bgcolor+'" pluginspage="http://www.apple.com/quicktime/download/"></embed>');
                }
            }
            <!--suppress JSUnusedLocalSymbols -->
            function embed_flash(bgcolor, width, height, link, loop, type) {
                document.writeln('<embed src="'+link+'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="'+type+'" quality="high" width="'+width+'" height="'+height+'" bgcolor="'+bgcolor+'" loop="'+loop+'"></embed>');
            }
            <!--suppress JSUnusedLocalSymbols -->
            function embed_flv(width, height, link, loop, player) {
                document.writeln('<embed src="'+player+'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="'+width+'" height="'+height+'" wmode="transparent" flashvars="file='+link+'&autostart=false&repeat='+loop+'&showdigits=true&showfsbutton=false"></embed>');
            }
            <!--suppress JSUnusedLocalSymbols -->
            function embed_wmedia(width, height, link) {
                document.writeln('<embed type="application/x-mplayer2" src="'+link+'" autosize="1" width="'+width+'" height="'+height+'" showcontrols="1" showstatusbar="0" showdisplay="0" autostart="0"></embed>');
            }
            </script>
        <?php
            $js .= ob_get_clean();
            return $js;
        }//2143 from SimplePie_Misc
        public function sp_get_build():int{
            $root = __DIR__;
            if (file_exists($root . '/.git/index'))
                return filemtime($root . '/.git/index');
            elseif (file_exists($root . '/SimplePie')){
                $time = 0;
                foreach (glob($root . '/SimplePie/*.php') as $file){
                    if (($micro_time = filemtime($file)) > $time)
                        $time = $micro_time;
                }
                return $time;
            }elseif (file_exists(__DIR__ . '/SimplePie_Core.php'))
                return filemtime(__DIR__ . '/SimplePie_Core.php');//todo
            return filemtime(__FILE__);
        }//2182 from SimplePie_Misc
        public function sp_debug(&$sp):string{
            $info = 'SimplePie ' . SP_VERSION . ' Build ' . SP_BUILD . "\n";
            $info .= 'PHP ' . PHP_VERSION . "\n";
            if ($sp->this->__sp_error() !== null)
                $info .= 'Error occurred: ' . $sp->this->__sp_error() . "\n";
            else $info .= "No error found.\n";
            $info .= "Extensions:\n";
            $extensions = array('pcre', 'curl', 'zlib', 'mbstring', 'iconv', 'xmlreader', 'xml');
            foreach ($extensions as $ext){
                if (extension_loaded($ext)){
                    $info .= "    $ext loaded\n";
                    switch ($ext){
                        case 'pcre':
                            $info .= '      Version ' . PCRE_VERSION . "\n";
                            break;
                        case 'curl':
                            $version = curl_version();
                            $info .= '      Version ' . $version['version'] . "\n";
                            break;
                        case 'mbstring':
                            $info .= '      Overloading: ' . mb_get_info('func_overload') . "\n";
                            break;
                        case 'iconv':
                            $info .= '      Version ' . ICONV_VERSION . "\n";
                            break;
                        case 'xml':
                            $info .= '      Version ' . LIBXML_DOTTED_VERSION . "\n";
                            break;
                    }
                }
                else $info .= "    $ext not loaded\n";
            }
            return $info;
        }//2212 from SimplePie_Misc
        public function sp_silence_errors($num, $str):void{/* No-op */ }//2259 from SimplePie_Misc
        public function sp_url_remove_credentials($url){
            return preg_replace('#^(https?://)[^/:@]+:[^/:@]+@#i', '$1', $url);
        }//2269 from SimplePie_Misc
        public function get_date_object($date):int{
            if (!$this->sp_object)
                $this->sp_object = new SimplePie_Parse_Date;
            return $this->sp_object->parse($date);
        }// from SimplePie_Parse_Date
        public function iri_absolutize($base, $relative) {
            if (!($relative instanceof SimplePie_IRI)) $relative = new SimplePie_IRI($relative);
            if (!$relative->is_valid()) return false;
            elseif ($relative->_sp_scheme !== null) return clone $relative;
            else{
                if (!($base instanceof SimplePie_IRI))
                    $base = new SimplePie_IRI($base);
                if ($base->_sp_scheme !== null && $base->is_valid()){
                    if ($relative->get_iri() !== ''){
                        if ($relative->_sp_i_user_info !== null || $relative->_sp_i_host !== null || $relative->_sp_port !== null){
                            $target = clone $relative;
                            $target->_sp_scheme = $base->_sp_scheme;
                        }else{
                            $target = new SimplePie_IRI;
                            $target->_sp_scheme = $base->_sp_scheme;
                            $target->_sp_i_user_info = $base->_sp_i_user_info;
                            $target->_sp_i_host = $base->_sp_i_host;
                            $target->_sp_port = $base->_sp_port;
                            if ($relative->_sp_i_path !== '') {
                                if ($relative->_sp_i_path[0] === '/') $target->_sp_i_path = $relative->_sp_i_path;
                                elseif (($base->_sp_i_user_info !== null || $base->_sp_i_host !== null || $base->_sp_port !== null) && $base->_sp_i_path === '')
                                    $target->_sp_i_path = '/' . $relative->_sp_i_path;
                                elseif (($last_segment = strrpos($base->_sp_i_path, '/')) !== false)
                                    $target->_sp_i_path = substr($base->_sp_i_path, 0, $last_segment + 1) . $relative->_sp_i_path;
                                else $target->_sp_i_path = $relative->_sp_i_path;
                                $target->_sp_i_path = $target->remove_dot_segments($target->_sp_i_path);
                                $target->_sp_i_query = $relative->_sp_i_query;
                            }else{
                                $target->_sp_i_path = $base->_sp_i_path;
                                if ($relative->_sp_i_query !== null) $target->_sp_i_query = $relative->_sp_i_query;
                                elseif ($base->_sp_i_query !== null) $target->_sp_i_query = $base->_sp_i_query;
                            }
                            $target->_sp_i_fragment = $relative->_sp_i_fragment;
                        }
                    }
                    else{
                        $target = clone $base;
                        $target->_sp_i_fragment = null;
                    }
                    $target->scheme_normalization();
                    return $target;
                }
                return false;
            }
        }
        public function sp_sort_items($a, $b):int{
            $a_date = $a->this->sp_get_date('U');
            $b_date = $b->this->sp_get_date('U');
            if ($a_date && $b_date) return $a_date > $b_date ? -1 : 1;
            // Sort items without dates to the top.
            if ($a_date) return 1;
            if ($b_date)  return -1;
            return 0;
        }//3255
        public function sp_merge_items($urls, $start = 0, $end = 0, $limit = 0):array{
            if (is_array($urls) && count($urls) > 0){
                $items = array();
                foreach ($urls as $arg){
                    if ($arg instanceof SimplePie) $items = $this->_tp_array_merge($items, $arg->get_items(0, $limit));
                    else trigger_error('Arguments must be SimplePie objects', E_USER_WARNING);
                }
                usort($items, array(get_class($urls[0]), 'sort_items'));
                if ($end === 0) return array_slice($items, $start);
                return array_slice($items, $start, $end);
            }
            trigger_error('Cannot merge zero SimplePie objects', E_USER_WARNING);
            return [];
        }//3255 from SimplePie
        /** @noinspection PhpUnusedPrivateMethodInspection */
        /**
         * @param $file
         * @param $hub
         * @param $self
         */
        private function __store_links(&$file, $hub, $self):void {
            if (isset($file->sp_headers['link']['hub']) ||
                (isset($file->sp_headers['link']) &&
                    preg_match('/rel=hub/', $file->sp_headers['link'])))
                return;
            if ($hub){
                if (isset($file->sp_headers['link'])){
                    if ($file->sp_headers['link'] !== '') $file->sp_headers['link'] = ', ';
                }else $file->sp_headers['link'] = '';
                $file->sp_headers['link'] .= '<'.$hub.'>; rel=hub';
                if ($self) $file->sp_headers['link'] .= ', <'.$self.'>; rel=self';

            }
        }//5525 from SimplePie
        public function sp_prepare(\PDO $pdo, $value): \PDOStatement{
            if($pdo)
                $this->_sp_prepare = $pdo->prepare($value);
            return $this->_sp_prepare;
        }
        public function sp_quote(\PDO $pdo, $value):string{
            if($pdo)
                $this->_sp_quote = $pdo->quote($value);
            return $this->_sp_quote;
        }
    }
}else die;