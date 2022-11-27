<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-9-2022
 * Time: 20:23
 */
namespace TP_Admin\Traits;
if(ABSPATH){
    trait _adm_zipper{
        protected function _PclZipUtilPathReduction($p_dir){
            $v_result = "";
            if ($p_dir !== "") {
                $v_list = explode("/", $p_dir);
                $v_skip = 0;
                for ($i = count($v_list) - 1; $i >= 0; $i--) {
                    if ($v_list[$i] === ".") {
                    } elseif ($v_list[$i] === "..") { $v_skip++;}
                    elseif ($v_list[$i] === "") {
                        if ($i === 0) {
                            $v_result = "/" . $v_result;
                            if ($v_skip > 0) {
                                $v_result = $p_dir;
                                $v_skip   = 0;
                            }
                        }elseif ($i === (count($v_list) - 1)){ $v_result = $v_list[$i];}
                        else {}
                    } else if ($v_skip > 0) {$v_skip--;}
                    else {$v_result = $v_list[$i] . ($i !== (count($v_list) - 1) ? "/" . $v_result : "");}
                }
                if ($v_skip > 0) {
                    while ($v_skip > 0) {
                        $v_result = '../' . $v_result;
                        $v_skip--;
                    }
                }
            }
            return $v_result;
        }//5434
        protected function _PclZipUtilPathInclusion($p_dir, $p_path):int{
            $v_result = 1;
            if (($p_dir === '.') || ((strlen($p_dir) >= 2) && (strpos($p_dir, './') === 0))) {
                $p_dir = $this->_PclZipUtilTranslateWinPath(getcwd(), false) . '/' . substr($p_dir, 1);
            }
            if (($p_path === '.') || ((strlen($p_path) >= 2) && (strpos($p_path, './') === 0))) {
                $p_path = $this->_PclZipUtilTranslateWinPath(getcwd(), false) . '/' . substr($p_path, 1);
            }
            $v_list_dir       = explode("/", $p_dir);
            $v_list_dir_size  = count($v_list_dir);
            $v_list_path      = explode("/", $p_path);
            $v_list_path_size = count($v_list_path);
            $i = 0;
            $j = 0;
            while (($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result)) {
                if ($v_list_dir[$i] === '') {
                    $i++;
                    continue;
                }
                if ($v_list_path[$j] === '') {
                    $j++;
                    continue;
                }
              if (($v_list_dir[$i] !== $v_list_path[$j]) && ($v_list_dir[$i] !== '') && ($v_list_path[$j] !== '')) {
                    $v_result = 0;}
                $i++;
                $j++;
            }
            if ($v_result) {
                while (($j < $v_list_path_size) && ($v_list_path[$j] === '')) {$j++;}
                while (($i < $v_list_dir_size) && ($v_list_dir[$i] === '')){$i++;}
                if (($i >= $v_list_dir_size) && ($j >= $v_list_path_size)) { $v_result = 2;}
                elseif ($i < $v_list_dir_size) {$v_result = 0;}
            }
            return $v_result;
        }//5515
        protected function _PclZipUtilCopyBlock($p_src, $p_dest, $p_size, $p_mode=0):int{
            $v_result = 1;
            if ($p_mode === 0) {
                while ($p_size !== 0) {
                    $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
                    $v_buffer    = @fread($p_src, $v_read_size);
                    @fwrite($p_dest, $v_buffer, $v_read_size);
                    $p_size -= $v_read_size;
                }
            } elseif ($p_mode === 1) {
                while ($p_size !== 0) {
                    $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
                    $v_buffer    = @gzread($p_src, $v_read_size);
                    @fwrite($p_dest, $v_buffer, $v_read_size);
                    $p_size -= $v_read_size;
                }
            } elseif ($p_mode === 2) {
                while ($p_size !== 0) {
                    $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
                    $v_buffer    = @fread($p_src, $v_read_size);
                    @gzwrite($p_dest, $v_buffer, $v_read_size);
                    $p_size -= $v_read_size;
                }
            } elseif ($p_mode === 3) {
                while ($p_size !== 0) {
                    $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
                    $v_buffer    = @gzread($p_src, $v_read_size);
                    @gzwrite($p_dest, $v_buffer, $v_read_size);
                    $p_size -= $v_read_size;
                }
            }
            return $v_result;
        }//5592
        protected function _PclZipUtilRename($p_src, $p_dest):int{
            $v_result = 1;
            if (!@rename($p_src, $p_dest)) {
                if (!@copy($p_src, $p_dest)) {$v_result = 0;}
                elseif (!@unlink($p_src)) {$v_result = 0;}
            }
            return $v_result;
        }//5654
        protected function _PclZipUtilOptionText($p_option){
            $v_list = get_defined_constants();
            for (reset($v_list); $v_key = key($v_list); next($v_list)) {
                $v_prefix = substr($v_key, 0, 10);
                if ((($v_prefix === 'PCLZIP_OPT') || ($v_prefix === 'PCLZIP_CB_') || ($v_prefix === 'PCLZIP_ATT')) && ($v_list[$v_key] === $p_option)) {
                    return $v_key;}
            }
            return 'Unknown';
        }//5684
        protected function _PclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter=true){
            if (stripos(php_uname(), 'windows') !== false) {
                if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) !== false)) {
                    $p_path = substr($p_path, $v_position + 1);
                }
                if ((strpos($p_path, '\\') > 0) || ($p_path[0] === '\\')) {
                    $p_path = str_replace('\\', '/', $p_path);
                }
            }
            return $p_path;
        }//5715
    }
}else{die;}