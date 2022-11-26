<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-4-2022
 * Time: 20:57
 */
namespace TP_Core\Libs\SimplePie\Depedencies\MicroFormats;
if(ABSPATH){
    trait _parser_2{
        private function __parseUriToComponents($uri):array{
            $result = ['scheme' => null,'authority' => null,'path' => null,'query' => null,'fragment' => null];
            $u = @parse_url($uri);
            if(array_key_exists('scheme', $u)) $result['scheme'] = $u['scheme'];
            if(array_key_exists('host', $u)) {
                if(array_key_exists('user', $u)) $result['authority'] = $u['user'];
                if(array_key_exists('pass', $u)) $result['authority'] .= ':' . $u['pass'];
                if(array_key_exists('user', $u) || array_key_exists('pass', $u)) $result['authority'] .= '@';
                $result['authority'] .= $u['host'];
                if(array_key_exists('port', $u)) $result['authority'] .= ':' . $u['port'];
            }
            if(array_key_exists('path', $u)) $result['path'] = $u['path'];
            if(array_key_exists('query', $u)) $result['query'] = $u['query'];
            if(array_key_exists('fragment', $u)) $result['fragment'] = $u['fragment'];
            return $result;
        }//2107
        private function __resolveUrl($baseURI, $reference_URI):string{
            $target = ['scheme' => null,'authority' => null,'path' => null,'query' => null,'fragment' => null];
            $base = $this->__parseUriToComponents($baseURI);
            if($base['path'] === null) $base['path'] = '/';
            $reference = $this->__parseUriToComponents($reference_URI);
            if($reference['scheme']) {
                $target['scheme'] = $reference['scheme'];
                $target['authority'] = $reference['authority'];
                $target['path'] = $this->__removeDotSegments($reference['path']);
                $target['query'] = $reference['query'];
            }else{
                if($reference['authority']) {
                    $target['authority'] = $reference['authority'];
                    $target['path'] = $this->__removeDotSegments($reference['path']);
                    $target['query'] = $reference['query'];
                }else{
                    if($reference['path'] === '') {
                        $target['path'] = $base['path'];
                        if($reference['query']) $target['query'] = $reference['query'];
                        else $target['query'] = $base['query'];
                    }else{
                        if(strpos($reference['path'], '/') === 0)
                            $target['path'] = $this->__removeDotSegments($reference['path']);
                        else {
                            $target['path'] = $this->__mergePaths($base, $reference);
                            $target['path'] = $this->__removeDotSegments($target['path']);
                        }
                        $target['query'] = $reference['query'];
                    }
                    $target['authority'] = $base['authority'];
                }
                $target['scheme'] = $base['scheme'];
            }
            $target['fragment'] = $reference['fragment'];
            $result = '';
            if($target['scheme']) $result .= $target['scheme'] . ':';
            if($target['authority']) $result .= '//' . $target['authority'];
            $result .= $target['path'];
            if($target['query']) $result .= '?' . $target['query'];
            if($target['fragment']) $result .= '#' . $target['fragment'];
            elseif($reference_URI === '#') $result .= '#';
            return $result;
        }//2145
        private function __mergePaths($base, $reference):string{
            if($base['authority'] && $base['path'] === null) $merged = '/' . $reference['path'];
            else if(($pos=strrpos($base['path'], '/')) !== false) $merged = substr($base['path'], 0, $pos + 1) . $reference['path'];
            else $merged = $base['path'];
            return $merged;
        }//2229
        private function __removeLeadingDotSlash(&$input):void{
            if(strpos($input, '../') === 0) $input = substr($input, 3);
            elseif(strpos($input, './') === 0) $input = substr($input, 2);
        }//2253
        private function __removeLeadingSlashDot(&$input):void{
            if(strpos($input, '/./') === 0) $input = '/' . substr($input, 3);
            else $input = '/' . substr($input, 2);
        }//2262
        private function __removeOneDirLevel(&$input, &$output):void{
            if(strpos($input, '/../') === 0) $input = '/' . substr($input, 4);
            else $input = '/' . substr($input, 3);
            $output = substr($output, 0, strrpos($output, '/'));
        }//2271
        private function __removeLoneDotDot(&$input):void{
            if($input === '.') $input = substr($input, 1);
            else $input = substr($input, 2);
        }//2281
        private function __moveOneSegmentFromInput(&$input, &$output):void{
            if(strpos($input, '/') !== 0) $pos = strpos($input, '/');
            else $pos = strpos($input, '/', 1);
            if($pos === false) {
                $output .= $input;
                $input = '';
            } else {
                $output .= substr($input, 0, $pos);
                $input = substr($input, $pos);
            }
        }//2290
        private function __removeDotSegments($path):string{
            $input = $path;
            $output = '';
            $step = 0;
            while($input) {
                /** @noinspection OnlyWritesOnParameterInspection */
                $step++;
                if(strpos($input, '../') === 0 || strpos($input, './') === 0)
                    $this->__removeLeadingDotSlash($input);
                elseif($input === '/.' || strpos($input, '/./') === 0)
                    $this->__removeLeadingSlashDot($input);
                elseif($input === '/..' || strpos($input, '/../') === 0)
                    $this->__removeOneDirLevel($input, $output);
                elseif($input === '.' || $input === '..') $this->__removeLoneDotDot($input);
                else $this->__moveOneSegmentFromInput($input, $output);
            }
            return $output;
        }//2307
    }
}else die;