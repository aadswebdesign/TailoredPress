<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 16:15
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_IRI;
if(ABSPATH){
    trait _encoding_01{
        public function sp_time_hms($seconds):string{
            $time = '';
            $hours = floor($seconds / 3600);
            $remainder = $seconds % 3600;
            if ($hours > 0) $time .= $hours.':';
            $minutes = floor($remainder / 60);
            $seconds = $remainder % 60;
            if ($minutes < 10 && $hours > 0)
                $minutes = '0' . $minutes;
            if ($seconds < 10)
                $seconds = '0' . $seconds;
            $time .= $minutes.':';
            $time .= $seconds;
            return $time;
        }//51 from SimplePie_Misc
        public function sp_absolutize_url($relative, $base){
            $iri_pie = new SimplePie_IRI($base);
            $iri = $this->iri_absolutize($iri_pie, $relative);
            if ($iri === false) return false;
            return $iri_pie->get_uri();
        }//79 from SimplePie_Misc
        public function sp_get_element($real_name, $string):array{
            $return = [];
            $name = preg_quote($real_name, '/');
            if (preg_match_all("/<($name)" . SP_PCRE_HTML_ATTRIBUTE . "(>(.*)<\/$name>|(\/)?>)/siU", $string, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)){
                for ($i = 0, $total_matches = count($matches); $i < $total_matches; $i++){
                    $return[$i]['tag'] = $real_name;
                    $return[$i]['full'] = $matches[$i][0][0];
                    $return[$i]['offset'] = $matches[$i][0][1];
                    if (strlen($matches[$i][3][0]) <= 2) $return[$i]['self_closing'] = true;
                    else{
                        $return[$i]['self_closing'] = false;
                        $return[$i]['content'] = $matches[$i][4][0];
                    }
                    $return[$i]['atts'] = array();
                    if (isset($matches[$i][2][0]) && preg_match_all('/[\x09\x0A\x0B\x0C\x0D\x20]+([^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3D\x3E]*)(?:[\x09\x0A\x0B\x0C\x0D\x20]*=[\x09\x0A\x0B\x0C\x0D\x20]*(?:"([^"]*)"|\'([^\']*)\'|([^\x09\x0A\x0B\x0C\x0D\x20\x22\x27\x3E][^\x09\x0A\x0B\x0C\x0D\x20\x3E]*)?))?/', ' ' . $matches[$i][2][0] . ' ', $atts, PREG_SET_ORDER)){
                        foreach ($atts as $j => $jValue) {
                            if (count($jValue) === 2) $atts[$j][2] = $atts[$j][1];
                            $return[$i]['atts'][strtolower($jValue[1])]['data'] = $this->sp_entities_decode(end($atts[$j]));
                        }
                    }
                }
            }
            return $return;
        }//97 from SimplePie_Misc
        public function sp_element_implode($element):string{
            $full = "<$element[tag]";
            foreach ($element['atts'] as $key => $value){
                $key = strtolower($key);
                $full .= " $key=\"" . htmlspecialchars($value['data'], ENT_COMPAT, 'UTF-8') . '"';
            }
            if ($element['self_closing']) $full .= ' />';
            else $full .= ">$element[content]</$element[tag]>";
            return $full;
        }//134 from SimplePie_Misc
        public function sp_error($message, $level, $file, $line){
            if ((ini_get('error_reporting') & $level) > 0){
                switch ($level){
                    case E_USER_ERROR:
                        $note = 'PHP Error';
                        break;
                    case E_USER_WARNING:
                        $note = 'PHP Warning';
                        break;
                    case E_USER_NOTICE:
                        $note = 'PHP Notice';
                        break;
                    default:
                        $note = 'Unknown Error';
                        break;
                }
                $log_error = true;
                if (!function_exists('error_log')) $log_error = false;
                $log_file = @ini_get('error_log');
                if (!empty($log_file) && ('syslog' !== $log_file) && !@is_writable($log_file))
                    $log_error = false;
                if ($log_error) /** @noinspection ForgottenDebugOutputInspection *///todo
                    @error_log("$note: $message in $file on line $line", 0);
            }
            return $message;
        }//153 from SimplePie_Misc
        public function sp_fix_protocol($url, $https = 1){
            $url = $this->sp_normalize_url($url);
            $parsed = $this->sp_parse_url($url);
            if ($parsed['scheme'] !== '' && $parsed['scheme'] !== 'http' && $parsed['scheme'] !== 'https')
                return $this->sp_fix_protocol($this->sp_compress_parse_url('http', $parsed['authority'], $parsed['path'], $parsed['query'], $parsed['fragment']), $https);
            if ($parsed['scheme'] === '' && $parsed['authority'] === '' && !file_exists($url))
                return $this->sp_fix_protocol($this->sp_compress_parse_url('http', $parsed['path'], '', $parsed['query'], $parsed['fragment']), $https);
            if ($https === 2 && $parsed['scheme'] !== '') return "feed:$url";
            elseif ($https === 3 && strtolower($parsed['scheme']) === 'http')
                return substr_replace($url, 'podcast', 0, 4);
            elseif ($https === 4 && strtolower($parsed['scheme']) === 'http')
                return substr_replace($url, 'itpc', 0, 4);
            return $url;
        }//194 from SimplePie_Misc
        public function sp_array_merge_recursive($array1, $array2){
            foreach ($array2 as $key => $value){
                if (is_array($value)) $array1[$key] = $this->sp_array_merge_recursive($array1[$key], $value);
                else $array1[$key] = $value;
            }
            return $array1;
        }//224 from SimplePie_Misc
        public function sp_parse_url($url):array{
            $iri = new SimplePie_IRI($url);
            $i_scheme ='scheme';
            $i_authority = 'authority';
            $i_path = 'path';
            $i_query = 'query';
            $i_fragment = 'fragment';
            return array(
                'scheme' => (string) $iri->$i_scheme,
                'authority' => (string) $iri->$i_authority,
                'path' => (string) $iri->$i_path,
                'query' => (string) $iri->$i_query,
                'fragment' => (string) $iri->$i_fragment
            );
        }//241 from SimplePie_Misc
        public function sp_compress_parse_url($scheme = '', $authority = '', $path = '', $query = '', $fragment = ''):string{
            $iri = new SimplePie_IRI('');
            $i_scheme ='scheme';
            $i_authority = 'authority';
            $i_path = 'path';
            $i_query = 'query';
            $i_fragment = 'fragment';
            $iri->$i_scheme = $scheme;
            $iri->$i_authority = $authority;
            $iri->$i_path = $path;
            $iri->$i_query = $query;
            $iri->$i_fragment = $fragment;
            return $iri->get_uri();
        }//253 from SimplePie_Misc
        public function sp_normalize_url($url):string{
            $iri = new SimplePie_IRI($url);
            if($iri instanceof SimplePie_IRI){
                return $iri->get_uri();
            }
        }//264 from SimplePie_Misc
    }
}else die;