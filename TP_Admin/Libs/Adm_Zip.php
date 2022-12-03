<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-9-2022
 * Time: 18:09
 */
namespace TP_Admin\Libs;
use TP_Admin\Traits\_adm_zipper;
if(ABSPATH){
    class Adm_Zip extends Zip_Base {
        use _adm_zipper;
        private $__v_sort_flag;
        public function __construct($p_zipname){
            parent::__construct();
            if (!function_exists('gzopen')){
                die('Abort '.basename(__FILE__).' : Missing zlib extensions');
            }
            $this->zip_name = $p_zipname;
            //$this->zip_fd = 0 ?: fopen($this->zip_name,'');
            $this->zip_fd = 0;
            $this->magic_quotes_status = -1;
            return null;
        }//215
        public function createZip($p_filelist):string{
            $v_result = 1;
            $this->__privErrorReset();
            $v_options                            = array();
            $v_options[PCLZIP_OPT_NO_COMPRESSION] = false;
            $v_size = func_num_args();
            if ($v_size > 1) {
                $v_arg_list = func_get_args();
                array_shift($v_arg_list);
                $v_size--;
                if (!$v_result && (is_int($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                    $v_result = $this->__privParseOptions($v_arg_list, $v_size, $v_options, array(
                        PCLZIP_OPT_REMOVE_PATH => 'optional',
                        PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                        PCLZIP_OPT_ADD_PATH => 'optional',
                        PCLZIP_CB_PRE_ADD => 'optional',
                        PCLZIP_CB_POST_ADD => 'optional',
                        PCLZIP_OPT_NO_COMPRESSION => 'optional',
                        PCLZIP_OPT_COMMENT => 'optional',
                        PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                        PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                        PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                    ));
                    if ($v_result !== 1) {
                        return 0;
                    }
                } else {
                    $v_options[PCLZIP_OPT_ADD_PATH] = $v_arg_list[0];
                    if ($v_size === 2) {
                        $v_options[PCLZIP_OPT_REMOVE_PATH] = $v_arg_list[1];
                    } elseif ($v_size > 2) {
                        (new self($v_arg_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");
                        return 0;
                    }
                }
            }
            $this->__privOptionDefaultThreshold($v_options);
            $v_string_list    = [];
            $v_att_list       = [];
            $v_filedescr_list = [];
            $p_result_list    = [];
            if (is_array($p_filelist)) {
                if (isset($p_filelist[0]) && is_array($p_filelist[0])) {
                    $v_att_list = $p_filelist;
                } else {$v_string_list = $p_filelist;}
            } elseif (is_string($p_filelist)) {
                $v_string_list = explode(PCLZIP_SEPARATOR, $p_filelist);
            } else {
                (new self($p_filelist))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_filelist");
                return 0;
            }
            if (count($v_string_list) !== 0) {
                foreach ($v_string_list as $v_string) {
                    if ($v_string !== '') {
                        $v_att_list[][PCLZIP_ATT_FILE_NAME] = $v_string;
                    } else {}
                }
            }
            $v_supported_attributes = array(
                PCLZIP_ATT_FILE_NAME => 'mandatory',
                PCLZIP_ATT_FILE_NEW_SHORT_NAME => 'optional',
                PCLZIP_ATT_FILE_NEW_FULL_NAME => 'optional',
                PCLZIP_ATT_FILE_MTIME => 'optional',
                PCLZIP_ATT_FILE_CONTENT => 'optional',
                PCLZIP_ATT_FILE_COMMENT => 'optional'
            );
            foreach ($v_att_list as $v_entry) {
                $v_result = $this->__privFileDescrParseAtt($v_entry, $v_filedescr_list[], $v_supported_attributes);
                if ($v_result !== 1) {return 0;}
            }
            $v_result = $this->__privFileDescrExpand($v_filedescr_list, $v_options);
            if ($v_result !== 1) { return 0;}
            $v_result = $this->__privCreate($v_filedescr_list, $p_result_list, $v_options);
            if ($v_result !== 1) { return 0;}
            return $p_result_list;
        }//270 public access
        protected function _add($p_filelist):string{
            $v_result = 1;
            $this->__privErrorReset();
            $v_options                            = [];
            $v_options[PCLZIP_OPT_NO_COMPRESSION] = false;
            $v_size = func_num_args();
            if ($v_size > 1) {
                $v_arg_list = func_get_args();
                array_shift($v_arg_list);
                $v_size--;
                if (!$v_result && (is_int($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                    $v_result = $this->__privParseOptions($v_arg_list, $v_size, $v_options, array(
                        PCLZIP_OPT_REMOVE_PATH => 'optional',
                        PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                        PCLZIP_OPT_ADD_PATH => 'optional',
                        PCLZIP_CB_PRE_ADD => 'optional',
                        PCLZIP_CB_POST_ADD => 'optional',
                        PCLZIP_OPT_NO_COMPRESSION => 'optional',
                        PCLZIP_OPT_COMMENT => 'optional',
                        PCLZIP_OPT_ADD_COMMENT => 'optional',
                        PCLZIP_OPT_PREPEND_COMMENT => 'optional',
                        PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                        PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                        PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                    ));
                    if ($v_result !== 1) {return 0;}
                } else {
                    $v_options[PCLZIP_OPT_ADD_PATH] = $v_add_path = $v_arg_list[0];
                    if ($v_size === 2) {
                        $v_options[PCLZIP_OPT_REMOVE_PATH] = $v_arg_list[1];
                    } elseif ($v_size > 2) {
                        (new self($v_add_path))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");
                        return 0;
                    }
                }
            }
            $this->__privOptionDefaultThreshold($v_options);
            $v_string_list    = [];
            $v_att_list       = [];
            $v_filedescr_list = [];
            $p_result_list    = [];
            if (is_array($p_filelist)) {
                if (isset($p_filelist[0]) && is_array($p_filelist[0])) {
                    $v_att_list = $p_filelist;
                } else {$v_string_list = $p_filelist;}
            } elseif (is_string($p_filelist)) {
                $v_string_list = explode(PCLZIP_SEPARATOR, $p_filelist);
            } else {
                (new self($p_filelist))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type '" . gettype($p_filelist) . "' for p_filelist");
                return 0;
            }
            if (count($v_string_list) !== 0) {
                foreach ($v_string_list as $v_string) {
                    $v_att_list[][PCLZIP_ATT_FILE_NAME] = $v_string;
                }
            }
            $v_supported_attributes = array(
                PCLZIP_ATT_FILE_NAME => 'mandatory',
                PCLZIP_ATT_FILE_NEW_SHORT_NAME => 'optional',
                PCLZIP_ATT_FILE_NEW_FULL_NAME => 'optional',
                PCLZIP_ATT_FILE_MTIME => 'optional',
                PCLZIP_ATT_FILE_CONTENT => 'optional',
                PCLZIP_ATT_FILE_COMMENT => 'optional'
            );
            foreach ($v_att_list as $v_entry) {
                $v_result = $this->__privFileDescrParseAtt($v_entry, $v_filedescr_list[], $v_supported_attributes);
                if ($v_result !== 1) {return 0;}
            }
            $v_result = $this->__privFileDescrExpand($v_filedescr_list, $v_options);
            if ($v_result !== 1) {return 0;}
            $v_result = $this->__privAdd($v_filedescr_list, $p_result_list, $v_options);
            if ($v_result !== 1) {return 0;}
            return $p_result_list;
        }//445
        public function listZipContent():int{
            $v_result = 1;
            $this->__privErrorReset();
            if (!$this->__privCheckFormat()) { return (0);}
            $p_list = [];
            if (!$v_result &&($v_result = $this->__privList($p_list)) !== 1) {
                unset($p_list);
                return (0);
            }
            return (int)$p_list;
        }//627 public access
        public function extractZip():string{
            $v_result = 1;
            $this->__privErrorReset();
            if (!$this->__privCheckFormat()) { return (0);}
            $v_options         = array();
            $v_path            = '';
            $v_remove_path     = "";
            $v_remove_all_path = false;
            $v_size = func_num_args();
            $v_options[PCLZIP_OPT_EXTRACT_AS_STRING] = false;
            if ($v_size > 0) {
                $v_arg_list = func_get_args();
                if (!$v_result && (is_int($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                    $v_result = $this->__privParseOptions($v_arg_list, $v_size, $v_options, array(
                        PCLZIP_OPT_PATH => 'optional',
                        PCLZIP_OPT_REMOVE_PATH => 'optional',
                        PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                        PCLZIP_OPT_ADD_PATH => 'optional',
                        PCLZIP_CB_PRE_EXTRACT => 'optional',
                        PCLZIP_CB_POST_EXTRACT => 'optional',
                        PCLZIP_OPT_SET_CHMOD => 'optional',
                        PCLZIP_OPT_BY_NAME => 'optional',
                        PCLZIP_OPT_BY_EREG => 'optional',
                        PCLZIP_OPT_BY_PREG => 'optional',
                        PCLZIP_OPT_BY_INDEX => 'optional',
                        PCLZIP_OPT_EXTRACT_AS_STRING => 'optional',
                        PCLZIP_OPT_EXTRACT_IN_OUTPUT => 'optional',
                        PCLZIP_OPT_REPLACE_NEWER => 'optional',
                        PCLZIP_OPT_STOP_ON_ERROR => 'optional',
                        PCLZIP_OPT_EXTRACT_DIR_RESTRICTION => 'optional',
                        PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                        PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                        PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                    ));
                    if ($v_result !== 1) { return 0;}
                    if (isset($v_options[PCLZIP_OPT_PATH])) {$v_path = $v_options[PCLZIP_OPT_PATH];}
                    if (isset($v_options[PCLZIP_OPT_REMOVE_PATH])) { $v_remove_path = $v_options[PCLZIP_OPT_REMOVE_PATH];}
                    if (isset($v_options[PCLZIP_OPT_REMOVE_ALL_PATH])) {$v_remove_all_path = $v_options[PCLZIP_OPT_REMOVE_ALL_PATH];}
                    if (isset($v_options[PCLZIP_OPT_ADD_PATH])) {
                        if (($v_path !== '') && (substr($v_path, -1) !== '/')) { $v_path .= '/';}
                        $v_path .= $v_options[PCLZIP_OPT_ADD_PATH];
                    }
                } else {
                    $v_path = $v_arg_list[0];
                    if ($v_size === 2) {
                        $v_remove_path = $v_arg_list[1];
                    } elseif ($v_size > 2) {
                        (new self($v_arg_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");
                        return 0;
                    }
                }
            }
            $this->__privOptionDefaultThreshold($v_options);
            $p_list   = [];
            $v_result = $this->__privExtractByRule($p_list, $v_path, $v_remove_path, $v_remove_all_path, $v_options);
            if ($v_result < 1) {
                unset($p_list);
                return (0);
            }
            return $p_list;
        }//684 public access
        public function extract_by_index($p_index):int{
            $v_result = 1;
            $this->__privErrorReset();
            if (!$this->__privCheckFormat()) { return (0);}
            $v_options         = [];
            $v_path            = '';
            $v_remove_path     = "";
            $v_remove_all_path = false;
            $v_size = func_num_args();
            $v_options[PCLZIP_OPT_EXTRACT_AS_STRING] = false;
            if ($v_size > 1) {
                $v_arg_list = func_get_args();
                array_shift($v_arg_list);
                $v_size--;
                if (!$v_result && (is_int($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
                    $v_result = $this->__privParseOptions($v_arg_list, $v_size, $v_options, array(
                        PCLZIP_OPT_PATH => 'optional',
                        PCLZIP_OPT_REMOVE_PATH => 'optional',
                        PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                        PCLZIP_OPT_EXTRACT_AS_STRING => 'optional',
                        PCLZIP_OPT_ADD_PATH => 'optional',
                        PCLZIP_CB_PRE_EXTRACT => 'optional',
                        PCLZIP_CB_POST_EXTRACT => 'optional',
                        PCLZIP_OPT_SET_CHMOD => 'optional',
                        PCLZIP_OPT_REPLACE_NEWER => 'optional',
                        PCLZIP_OPT_STOP_ON_ERROR => 'optional',
                        PCLZIP_OPT_EXTRACT_DIR_RESTRICTION => 'optional',
                        PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                        PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                        PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                    ));
                    if ($v_result !== 1) { return 0;}
                    if (isset($v_options[PCLZIP_OPT_PATH])) {
                        $v_path = $v_options[PCLZIP_OPT_PATH];}
                    if (isset($v_options[PCLZIP_OPT_REMOVE_PATH])) {
                        $v_remove_path = $v_options[PCLZIP_OPT_REMOVE_PATH];}
                    if (isset($v_options[PCLZIP_OPT_REMOVE_ALL_PATH])) {
                        $v_remove_all_path = $v_options[PCLZIP_OPT_REMOVE_ALL_PATH];}
                    if (isset($v_options[PCLZIP_OPT_ADD_PATH])) {
                        if (($v_path !== '') && (substr($v_path, -1) !== '/')) { $v_path .= '/';}
                        $v_path .= $v_options[PCLZIP_OPT_ADD_PATH];}
                    if (!isset($v_options[PCLZIP_OPT_EXTRACT_AS_STRING])) {
                        $v_options[PCLZIP_OPT_EXTRACT_AS_STRING] = false;
                    } else {}
                } else {
                    $v_path = $v_arg_list[0];
                    if ($v_size === 2) { $v_remove_path = $v_arg_list[1];
                    } elseif ($v_size > 2) {
                        (new self($v_arg_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");
                        return 0;
                    }
                }
            }
            $v_arg_trick     = array(
                PCLZIP_OPT_BY_INDEX,
                $p_index
            );
            $v_options_trick = array();
            $v_result        = $this->__privParseOptions($v_arg_trick, count($v_arg_trick), $v_options_trick, array(
                PCLZIP_OPT_BY_INDEX => 'optional'
            ));
            if ($v_result !== 1) { return 0;}
            $v_options[PCLZIP_OPT_BY_INDEX] = $v_options_trick[PCLZIP_OPT_BY_INDEX];
            $this->__privOptionDefaultThreshold($v_options);
            if (($v_result = $this->__privExtractByRule($p_list, $v_path, $v_remove_path, $v_remove_all_path, $v_options)) < 1) {
                return (0);}
            return $p_list;
        }//839
        public function delete():string{
            $v_result = 1;
            $this->__privErrorReset();
            if (!$this->__privCheckFormat()) { return (0);}
            $v_options = [];
            $v_size = func_num_args();
            if (!$v_result && $v_size > 0) {
                $v_arg_list = func_get_args();
                $v_result = $this->__privParseOptions($v_arg_list, $v_size, $v_options, array(
                    PCLZIP_OPT_BY_NAME => 'optional',
                    PCLZIP_OPT_BY_EREG => 'optional',
                    PCLZIP_OPT_BY_PREG => 'optional',
                    PCLZIP_OPT_BY_INDEX => 'optional'
                ));
                if ($v_result !== 1) { return 0;}
            }
            $this->__privDisableMagicQuotes();
            $v_list = array();
            if (($v_result = $this->__privDeleteByRule($v_list, $v_options)) !== 1) {
                $this->__privSwapBackMagicQuotes();
                unset($v_list);
                return (0);
            }
            $this->__privSwapBackMagicQuotes();
            return $v_list;
        }//989
        public function delete_by_index($p_index):int{
            return $this->delete(PCLZIP_OPT_BY_INDEX, $p_index);
        }//1050
        public function zipProperties():string{
            $this->__privErrorReset();
            $this->__privDisableMagicQuotes();
            if (!$this->__privCheckFormat()) {
                $this->__privSwapBackMagicQuotes();
                return (0);
            }
            $v_prop            = [];
            $v_prop['comment'] = '';
            $v_prop['nb']      = 0;
            $v_prop['status']  = 'not_exist';
            if (@is_file($this->zip_name)) {
                if (($this->zip_fd = @fopen($this->zip_name, 'rb')) === 0) {
                    $this->__privSwapBackMagicQuotes();
                    (new self($this->zip_name))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \'' . $this->zip_name . '\' in binary read mode');
                    return 0;
                }
                $v_central_dir = [];
                if (($v_prop = $this->__privReadEndCentralDir($v_central_dir)) !== 1) {
                    $this->__privSwapBackMagicQuotes();
                    return 0;
                }
                $this->__privCloseFd();
                $v_prop['comment'] = $v_central_dir['comment'];
                $v_prop['nb']      = $v_central_dir['entries'];
                $v_prop['status']  = 'ok';
            }
            $this->__privSwapBackMagicQuotes();
            return $v_prop;
        }//1074 public access
        public function duplicate($p_archive):string{
            $v_result = 1;
            $this->__privErrorReset();
            if (!$v_result && (is_object($p_archive)) && (get_class($p_archive) === 'pclzip')) {
                $v_result = $this->__privDuplicate($p_archive->zipname);
            } elseif (is_string($p_archive)) {
                if (!is_file($p_archive)) {
                    (new self($p_archive))->__privErrorLog(PCLZIP_ERR_MISSING_FILE, "No file with filename '" . $p_archive . "'");
                    $v_result = PCLZIP_ERR_MISSING_FILE;
                } else { $v_result = $this->__privDuplicate($p_archive);}
            } else {
                (new self($p_archive))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_archive_to_add");
                $v_result = PCLZIP_ERR_INVALID_PARAMETER;
            }
            return $v_result;
        }//1146
        public function merge($p_archive_to_add):string{
            $v_result = 1;
            $this->__privErrorReset();
            if (!$this->__privCheckFormat()) { return (0);}
            if ((is_object($p_archive_to_add)) && (get_class($p_archive_to_add) === 'pclzip')) {
                $v_result = $this->__privMerge($p_archive_to_add);
            } elseif (is_string($p_archive_to_add)) {
                $v_object_archive = new self($p_archive_to_add);
                $v_result = $this->__privMerge($v_object_archive);
            } else {
                (new self($v_result))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_archive_to_add");
                $v_result = PCLZIP_ERR_INVALID_PARAMETER;
            }
            return $v_result;
        }//1199
        public function error_code():string{
            if (PCLZIP_ERROR_EXTERNAL === 1 && method_exists('_PclErrorCode','')) {
                /** @noinspection PhpUndefinedMethodInspection */ //todo
                return ($this->_PclErrorCode());
            }
            return ($this->error_code);
        }//1243
        public function error_name($p_with_code=false){
            $v_name = array(
                PCLZIP_ERR_NO_ERROR => 'PCLZIP_ERR_NO_ERROR',
                PCLZIP_ERR_WRITE_OPEN_FAIL => 'PCLZIP_ERR_WRITE_OPEN_FAIL',
                PCLZIP_ERR_READ_OPEN_FAIL => 'PCLZIP_ERR_READ_OPEN_FAIL',
                PCLZIP_ERR_INVALID_PARAMETER => 'PCLZIP_ERR_INVALID_PARAMETER',
                PCLZIP_ERR_MISSING_FILE => 'PCLZIP_ERR_MISSING_FILE',
                PCLZIP_ERR_FILENAME_TOO_LONG => 'PCLZIP_ERR_FILENAME_TOO_LONG',
                PCLZIP_ERR_INVALID_ZIP => 'PCLZIP_ERR_INVALID_ZIP',
                PCLZIP_ERR_BAD_EXTRACTED_FILE => 'PCLZIP_ERR_BAD_EXTRACTED_FILE',
                PCLZIP_ERR_DIR_CREATE_FAIL => 'PCLZIP_ERR_DIR_CREATE_FAIL',
                PCLZIP_ERR_BAD_EXTENSION => 'PCLZIP_ERR_BAD_EXTENSION',
                PCLZIP_ERR_BAD_FORMAT => 'PCLZIP_ERR_BAD_FORMAT',
                PCLZIP_ERR_DELETE_FILE_FAIL => 'PCLZIP_ERR_DELETE_FILE_FAIL',
                PCLZIP_ERR_RENAME_FILE_FAIL => 'PCLZIP_ERR_RENAME_FILE_FAIL',
                PCLZIP_ERR_BAD_CHECKSUM => 'PCLZIP_ERR_BAD_CHECKSUM',
                PCLZIP_ERR_INVALID_ARCHIVE_ZIP => 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP',
                PCLZIP_ERR_MISSING_OPTION_VALUE => 'PCLZIP_ERR_MISSING_OPTION_VALUE',
                PCLZIP_ERR_INVALID_OPTION_VALUE => 'PCLZIP_ERR_INVALID_OPTION_VALUE',
                PCLZIP_ERR_UNSUPPORTED_COMPRESSION => 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION',
                PCLZIP_ERR_UNSUPPORTED_ENCRYPTION => 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION',
                PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE => 'PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE',
                PCLZIP_ERR_DIRECTORY_RESTRICTION => 'PCLZIP_ERR_DIRECTORY_RESTRICTION'
            );
            $v_value = $v_name[$this->error_code] ?? 'NoName';
            if ($p_with_code) {return ($v_value . "({$this->error_code})");}
            return ($v_value);
        }//1258
        public function error_info($p_full=false){
            if (PCLZIP_ERROR_EXTERNAL === 1 && method_exists('_PclErrorString','')) {
                /** @noinspection PhpUndefinedMethodInspection */ //todo
                return ($this->_PclErrorString());
            }
            if ($p_full) { return ($this->error_name(true) . " : " . $this->error_string);}
            return ($this->error_string . " [code {$this->error_code}]");
        }//1303
        private function __privCheckFormat($p_level=null):bool{
            clearstatcache();
            $this->__privErrorReset();
            if (!is_file($this->zip_name)) {
                (new self($p_level))->__privErrorLog(PCLZIP_ERR_MISSING_FILE, "Missing archive file: '{$this->zip_name}'");
                return (false);
            }
            if (!is_readable($this->zip_name)) {
                (new self($p_level))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to read archive '{$this->zip_name}'");
                return (false);
            }
            return true;
        }//1337
        private function __privParseOptions(&$p_options_list, $p_size, &$v_result_list,array $v_requested_options=false){
            $v_result = 1;
            $i = 0;
            while ($i < $p_size) {
                if (!isset($v_requested_options[$p_options_list[$i]])) {
                    (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid optional parameter: '{ $p_options_list[$i]}' for this method.");
                    return (new self($p_options_list))->error_code();
                }
                switch ($p_options_list[$i]) {
                    case PCLZIP_OPT_PATH:
                    case PCLZIP_OPT_REMOVE_PATH:
                    case PCLZIP_OPT_ADD_PATH:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            (new self($p_options_list))->error_code();
                        }
                        $v_result_list[$p_options_list[$i]] = $this->_PclZipUtilTranslateWinPath($p_options_list[$i + 1], false);
                        $i++;
                        break;
                    case PCLZIP_OPT_TEMP_FILE_THRESHOLD:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_OFF])) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}' can not be used with option 'PCLZIP_OPT_TEMP_FILE_OFF'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_value = $p_options_list[$i + 1];
                        if ((!is_int($v_value)) || ($v_value < 0)) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Integer expected for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_result_list[$p_options_list[$i]] = $v_value * 1048576;
                        $i++;
                        break;
                    case PCLZIP_OPT_TEMP_FILE_ON:
                        if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_OFF])) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}' can not be used with option 'PCLZIP_OPT_TEMP_FILE_OFF'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_result_list[$p_options_list[$i]] = true;
                        break;
                    case PCLZIP_OPT_TEMP_FILE_OFF:
                        if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_ON])) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}' can not be used with option 'PCLZIP_OPT_TEMP_FILE_ON'.");
                            return (new self($p_options_list))->error_code();
                        }
                        if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_THRESHOLD])) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}' can not be used with option 'PCLZIP_OPT_TEMP_FILE_THRESHOLD'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_result_list[$p_options_list[$i]] = true;
                        break;
                    case PCLZIP_OPT_EXTRACT_DIR_RESTRICTION:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        if (is_string($p_options_list[$i + 1]) && ($p_options_list[$i + 1] !== '')) {
                            $v_result_list[$p_options_list[$i]] = $this->_PclZipUtilTranslateWinPath($p_options_list[$i + 1], false);
                            $i++;
                        } else {}
                        break;
                    case PCLZIP_OPT_BY_NAME:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        if (is_string($p_options_list[$i + 1])) {
                            $v_result_list[$p_options_list[$i]][0] = $p_options_list[$i + 1];
                        } elseif (is_array($p_options_list[$i + 1])) {
                            $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                        } else {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Wrong parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $i++;
                        break;
                    case PCLZIP_OPT_BY_PREG:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        if (is_string($p_options_list[$i + 1])) {
                            $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                        } else {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Wrong parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $i++;
                        break;
                    case PCLZIP_OPT_COMMENT:
                    case PCLZIP_OPT_ADD_COMMENT:
                    case PCLZIP_OPT_PREPEND_COMMENT:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        if (is_string($p_options_list[$i + 1])) {
                            $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                        } else {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Wrong parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $i++;
                        break;
                    case PCLZIP_OPT_BY_INDEX:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_work_list = array();
                        if (is_string($p_options_list[$i + 1])) {
                            $p_options_list[$i + 1] = str_replace(' ', '', $p_options_list[$i + 1]);
                            $v_work_list = explode(",", $p_options_list[$i + 1]);
                        } elseif (is_int($p_options_list[$i + 1])) {
                            $v_work_list[0] = $p_options_list[$i + 1] . '-' . $p_options_list[$i + 1];
                        } elseif (is_array($p_options_list[$i + 1])) {
                            $v_work_list = $p_options_list[$i + 1];
                        } else {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Value must be integer, string or array for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $this->__v_sort_flag  = false;
                        $v_sort_value = 0;
                        for ($j = 0, $jMax = count($v_work_list); $j < $jMax; $j++) {
                            $v_item_list      = explode("-", $v_work_list[$j]);
                            $v_size_item_list = count($v_item_list);
                            if ($v_size_item_list === 1) {
                                $v_result_list[$p_options_list[$i]][$j]['start'] = $v_item_list[0];
                                $v_result_list[$p_options_list[$i]][$j]['end']   = $v_item_list[0];
                            } elseif ($v_size_item_list === 2) {
                                $v_result_list[$p_options_list[$i]][$j]['start'] = $v_item_list[0];
                                $v_result_list[$p_options_list[$i]][$j]['end']   = $v_item_list[1];
                            } else {
                                (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Too many values in index range for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                                return (new self($p_options_list))->error_code();
                            }
                            if ($v_result_list[$p_options_list[$i]][$j]['start'] < $v_sort_value) {
                                $this->__v_sort_flag = true;
                                (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Invalid order of index range for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                                return (new self($p_options_list))->error_code();
                            }
                            $v_sort_value = $v_result_list[$p_options_list[$i]][$j]['start'];
                        }
                        if ($this->__v_sort_flag === true) {
                            // TBC : To Be Completed
                            return $this->__v_sort_flag;
                        }
                        $i++;
                        break;
                    case PCLZIP_OPT_REMOVE_ALL_PATH:
                    case PCLZIP_OPT_EXTRACT_AS_STRING:
                    case PCLZIP_OPT_NO_COMPRESSION:
                    case PCLZIP_OPT_EXTRACT_IN_OUTPUT:
                    case PCLZIP_OPT_REPLACE_NEWER:
                    case PCLZIP_OPT_STOP_ON_ERROR:
                        $v_result_list[$p_options_list[$i]] = true;
                        break;
                    case PCLZIP_OPT_SET_CHMOD:
                        if (($i + 1) >= $p_size) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_result_list[$p_options_list[$i]] = $p_options_list[$i + 1];
                        $i++;
                        break;
                    case PCLZIP_CB_PRE_EXTRACT:
                    case PCLZIP_CB_POST_EXTRACT:
                    case PCLZIP_CB_PRE_ADD:
                    case PCLZIP_CB_POST_ADD:
                        /* for futur use
                        case PCLZIP_CB_PRE_DELETE :
                        case PCLZIP_CB_POST_DELETE :
                        case PCLZIP_CB_PRE_LIST :
                        case PCLZIP_CB_POST_LIST :
                        */
                        // ----- Check the number of parameters
                        if (($i + 1) >= $p_size) {
                            // ----- Error log
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_function_name = $p_options_list[$i + 1];
                        if (!function_exists($v_function_name)) {
                            (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Function '" . $v_function_name . "()' is not an existing function for option: '{$this->_PclZipUtilOptionText($p_options_list[$i])}'.");
                            return (new self($p_options_list))->error_code();
                        }
                        $v_result_list[$p_options_list[$i]] = $v_function_name;
                        $i++;
                        break;
                    default:
                        (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Unknown parameter: '{$p_options_list[$i]}'.");
                        return (new self($p_options_list))->error_code();
                }
                $i++;
            }//end while
            if ($v_requested_options !== false) {//or ||
                for (reset($v_requested_options); $key = key($v_requested_options); $key = next($v_requested_options)) {
                    if (($v_requested_options[$key] === 'mandatory') && !isset($v_result_list[$key])) {
                        (new self($p_options_list))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Missing mandatory parameter: '{$this->_PclZipUtilOptionText($key)}({$key})'.");
                        return (new self($p_options_list))->error_code();
                    }
                }
            }
            if (!isset($v_result_list[PCLZIP_OPT_TEMP_FILE_THRESHOLD])){} //return false ?
            return $v_result;
        }//1430
        private function __privOptionDefaultThreshold(&$p_options):int{
            $v_result = 1;
            if (isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD]) || isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF])) {
                return $v_result;
            }
            $v_memory_limit = ini_get('memory_limit');
            $v_memory_limit = trim($v_memory_limit);
            $last           = strtolower(substr($v_memory_limit, -1));
            $v_memory_limit = preg_replace('/\s*[KkMmGg]$/', '', $v_memory_limit);
            if ($last === 'g'){$v_memory_limit *= 1073741824;}
            if ($last === 'm'){$v_memory_limit *= 1048576;}
            if ($last === 'k'){$v_memory_limit *= 1024;}
            $p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] = floor($v_memory_limit * PCLZIP_TEMPORARY_FILE_RATIO);
            if ($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] < 1048576) { unset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD]);}
            return $v_result;
        }//1778
        private function __privFileDescrParseAtt(&$p_file_list, &$p_filedescr,array $v_requested_options=false){//, $v_options not used anywhere
            $v_result = 1;
            foreach ($p_file_list as $v_key => $v_value) {
                if (!isset($v_requested_options[$v_key])) {
                    (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid file attribute: '{$v_key}' for this file.");
                    return (new self($v_key))->error_code();
                }
                switch ($v_key) {
                    case PCLZIP_ATT_FILE_NAME:
                        if (!is_string($v_value)) {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type: " . gettype($v_value) . ". String expected for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        $p_filedescr['filename'] = $this->_PclZipUtilPathReduction($v_value);
                        if ($p_filedescr['filename'] === '') {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid empty filename for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        break;
                    case PCLZIP_ATT_FILE_NEW_SHORT_NAME:
                        if (!is_string($v_value)) {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type: " . gettype($v_value) . ". String expected for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        $p_filedescr['new_short_name'] = $this->_PclZipUtilPathReduction($v_value);
                        if ($p_filedescr['new_short_name'] === '') {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid empty short filename for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        break;
                    case PCLZIP_ATT_FILE_NEW_FULL_NAME:
                        if (!is_string($v_value)) {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type: " . gettype($v_value) . ". String expected for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        $p_filedescr['new_full_name'] = $this->_PclZipUtilPathReduction($v_value);
                        if ($p_filedescr['new_full_name'] === '') {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid empty full filename for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        break;
                    case PCLZIP_ATT_FILE_COMMENT:
                        if (!is_string($v_value)) {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type: " . gettype($v_value) . ". String expected for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        $p_filedescr['comment'] = $v_value;
                        break;
                    case PCLZIP_ATT_FILE_MTIME:
                        if (!is_int($v_value)) {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type: " . gettype($v_value) . ". Integer expected for attribute: '{$this->_PclZipUtilOptionText($v_key)}'.");
                            return (new self($v_key))->error_code();
                        }
                        $p_filedescr['mtime'] = $v_value;
                        break;
                    case PCLZIP_ATT_FILE_CONTENT:
                        $p_filedescr['content'] = $v_value;
                        break;
                    default:
                        (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Unknown parameter: '{$v_key}'");
                        return (new self($v_key))->error_code();
                }
                if ($v_requested_options !== false) {//or ||
                    for (reset($v_requested_options); $key = key($v_requested_options); $key = next($v_requested_options)) {
                        if (($v_requested_options[$key] === 'mandatory') && !isset($p_file_list[$key])) {
                            (new self($v_key))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Missing mandatory parameter: '{$this->_PclZipUtilOptionText($key)}({$key})'.");
                            return (new self($v_key))->error_code();
                        }
                    }
                }
            }// end foreach
            return $v_result;
        }//1824
        private function __privFileDescrExpand(&$p_filedescr_list, &$p_options):int{
            $v_result = 1;
            $v_result_list = array();
            $v_descr = null;
            foreach ($p_filedescr_list as $iValue) {
                $v_descr = $iValue;
                $v_descr['filename'] = $this->_PclZipUtilTranslateWinPath($v_descr['filename'], false);
                $v_descr['filename'] = $this->_PclZipUtilPathReduction($v_descr['filename']);
                if (file_exists($v_descr['filename'])) {
                    if (@is_file($v_descr['filename'])) {$v_descr['type'] = 'file';}
                    elseif (@is_dir($v_descr['filename'])) {$v_descr['type'] = 'folder';}
                    elseif (@is_link($v_descr['filename'])) { continue;}
                    else {continue;}
                } elseif (isset($v_descr['content'])){$v_descr['type'] = 'virtual_file';}
                else {
                    (new self($iValue))->__privErrorLog(PCLZIP_ERR_MISSING_FILE, "File: '{$v_descr['filename']}' does not exist.");
                    return (new self($iValue))->error_code();
                }
                $this->__privCalculateStoredFilename($v_descr, $p_options);
                $v_result_list[] = $v_descr;
                if ($v_descr['type'] === 'folder') {
                    $v_dirlist_descr = [];
                    $v_dirlist_nb    = 0;
                    if ($v_folder_handler = @opendir($v_descr['filename'])) {
                        while (($v_item_handler = @readdir($v_folder_handler)) !== false) {
                            if (($v_item_handler === '.') || ($v_item_handler === '..')) { continue;}
                            $v_dirlist_descr[$v_dirlist_nb]['filename'] = $v_descr['filename'] . '/' . $v_item_handler;
                            if (($v_descr['stored_filename'] !== $v_descr['filename']) && (!isset($p_options[PCLZIP_OPT_REMOVE_ALL_PATH]))) {
                                if ($v_descr['stored_filename'] !== '') {
                                    $v_dirlist_descr[$v_dirlist_nb]['new_full_name'] = $v_descr['stored_filename'] . '/' . $v_item_handler;
                                } else {$v_dirlist_descr[$v_dirlist_nb]['new_full_name'] = $v_item_handler;}
                            }
                            $v_dirlist_nb++;
                        }
                        @closedir($v_folder_handler);
                    } else { /*TBC : unable to open folder in read mode*/ }
                    if ($v_dirlist_nb !== 0) {
                        if (($v_result = $this->__privFileDescrExpand($v_dirlist_descr, $p_options)) !== 1) {
                            return $v_result;}
                        $v_result_list = $this->_PclZipUtilArrayMerge($v_result_list, $v_dirlist_descr);
                    } else {}
                    unset($v_dirlist_descr);
                }
            }
            $p_filedescr_list = $v_result_list;
            return $v_result;
        }//1961
        private function __privCreate($p_filedescr_list, &$p_result_list, &$p_options):void{
            $v_result      = 1;
            //$v_list_detail = []; not used
            $this->__privDisableMagicQuotes();
            if (!$v_result && ($v_result = $this->__privOpenFd('wb')) !== 1) {
                return $v_result;
            }
            $v_result = $this->__privAddList($p_filedescr_list, $p_result_list, $p_options);
            $this->__privCloseFd();
            $this->__privSwapBackMagicQuotes();
            return $v_result;
        }//2077
        private function __privAdd($p_filedescr_list, &$p_result_list, &$p_options){
            $v_result      = 1;
            //$v_list_detail = [];
            if ((!$v_result && (!is_file($this->zip_name))) || (filesize($this->zip_name) === 0)) {
                $v_result = $this->__privCreate($p_filedescr_list, $p_result_list, $p_options);
                return $v_result;
            }
            $this->__privDisableMagicQuotes();
            if (($v_result = $this->__privOpenFd('rb')) !== 1) {
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            $v_central_dir = [];
            if (($v_result = $this->__privReadEndCentralDir($v_central_dir)) !== 1) {
                $this->__privCloseFd();
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb'); //todo let see?
            rewind($this->zip_fd);
            $v_zip_temp_name = PCLZIP_TEMPORARY_DIR . uniqid('pclzip-', true) . '.tmp';
            if (($v_zip_temp_fd = fopen($v_zip_temp_name, 'wb')) === 0) {
                $this->__privCloseFd();
                $this->__privSwapBackMagicQuotes();
                (new self($v_zip_temp_name))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open temporary file: '{$v_zip_temp_name}' in binary write mode.");
                return (new self($v_zip_temp_name))->error_code();
            }
            $v_size = $v_central_dir['offset'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = fread($this->zip_fd, $v_read_size);
                @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $v_swap        = $this->zip_fd;
            $this->zip_fd  = $v_zip_temp_fd;
            $v_zip_temp_fd = $v_swap;
            $v_header_list = [];
            if (($v_result = $this->__privAddFileList($p_filedescr_list, $v_header_list, $p_options)) !== 1) {
                fclose($v_zip_temp_fd);
                $this->__privCloseFd();
                unlink($v_zip_temp_name);
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            $v_offset = @ftell($this->zip_fd);
            $v_size = $v_central_dir['size'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @fread($v_zip_temp_fd, $v_read_size);
                fwrite($this->zip_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            for ($i = 0, $v_count = 0, $iMax = count($v_header_list); $i < $iMax; $i++) {
                if ($v_header_list[$i]['status'] === 'ok') {
                    if (($v_result = $this->__privWriteCentralFileHeader($v_header_list[$i])) !== 1) {
                        fclose($v_zip_temp_fd);
                        $this->__privCloseFd();
                        unlink($v_zip_temp_name);
                        $this->__privSwapBackMagicQuotes();
                        return;
                    }
                    $v_count++;
                }
                $this->__privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
            }
            $v_comment = $p_options[PCLZIP_OPT_COMMENT] ?? $v_central_dir['comment'];
            if (isset($p_options[PCLZIP_OPT_ADD_COMMENT])) {
                $v_comment .= $p_options[PCLZIP_OPT_ADD_COMMENT];
            }
            if (isset($p_options[PCLZIP_OPT_PREPEND_COMMENT])) {
                $v_comment = $p_options[PCLZIP_OPT_PREPEND_COMMENT] . $v_comment;
            }
            // ----- Calculate the size of the central header
            $v_size = @ftell($this->zip_fd) - $v_offset;
            if (($v_result = $this->__privWriteCentralHeader($v_count + $v_central_dir['entries'], $v_size, $v_offset, $v_comment)) !== 1) {
                // ----- Reset the file list
                unset($v_header_list);
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            $v_swap        = $this->zip_fd;
            $this->zip_fd  = $v_zip_temp_fd;
            $v_zip_temp_fd = $v_swap;
            $this->__privCloseFd();
            @fclose($v_zip_temp_fd);
            $this->__privSwapBackMagicQuotes();
            @unlink($this->zip_name);
            $this->_PclZipUtilRename($v_zip_temp_name, $this->zip_name);
            return $v_result;
        }//2111
        private function __privOpenFd($p_mode){
            $v_result = 1;
            if ($this->zip_fd !== 0) {
                (new self($p_mode))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Zip file: '{$this->zip_name}'  already open.");
                return (new self($p_mode))->error_code();
            }
            if (($this->zip_fd = @fopen($this->zip_name, $p_mode)) === 0) {
                (new self($p_mode))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open archive: '{$this->zip_name}' in '{$p_mode}' mode. ");
                return (new self($p_mode))->error_code();
            }
            return $v_result;
        }//2282
        private function __privCloseFd():int{
            $v_result = 1;
            if ($this->zip_fd = 0 ?: fopen($this->zip_name,'wb')) { fclose($this->zip_fd);}
            $this->zip_fd = 0;
            return $v_result;
        }//2314
        private function __privAddList($p_filedescr_list, &$p_result_list, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_header_list = [];
            if (!$v_result && ($v_result = $this->__privAddFileList($p_filedescr_list, $v_header_list, $p_options)) !== 1) {
                return $v_result;
            }
            $v_offset = ftell($this->zip_fd);
            for ($i = 0, $v_count = 0, $iMax = count($v_header_list); $i < $iMax; $i++) {
                if ($v_header_list[$i]['status'] === 'ok') {
                    if (($v_result = $this->__privWriteCentralFileHeader($v_header_list[$i])) !== 1){return;}
                    $v_count++;
                }
                $this->__privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
            }
            $v_comment = $p_options[PCLZIP_OPT_COMMENT] ?? '';
            $v_size = ftell($this->zip_fd) - $v_offset;
            if (($v_result = $this->__privWriteCentralHeader($v_count, $v_size, $v_offset, $v_comment)) !== 1) {
                unset($v_header_list);
                return $v_result;
            }
            return $v_result;
        }//2342
        private function __privAddFileList($p_filedescr_list, &$p_result_list, &$p_options){
            $v_result = 1;
            $v_header = [];
            $v_nb = count($p_result_list);
            for ($j = 0; ($j < count($p_filedescr_list)) && ($v_result === 1); $j++) {
                $p_filedescr_list[$j]['filename'] = $this->_PclZipUtilTranslateWinPath($p_filedescr_list[$j]['filename'], false);
                if ($p_filedescr_list[$j]['filename'] === ""){ continue; }
                if (($p_filedescr_list[$j]['type'] !== 'virtual_file') && (!file_exists($p_filedescr_list[$j]['filename']))) {
                    (new self($p_filedescr_list))->__privErrorLog(PCLZIP_ERR_MISSING_FILE, "File: '{$p_filedescr_list[$j]['filename']}' does not exist.");
                    return  (new self($p_filedescr_list))->error_code();
                }
                if (($p_filedescr_list[$j]['type'] === 'file') || ($p_filedescr_list[$j]['type'] === 'virtual_file') || (($p_filedescr_list[$j]['type'] === 'folder') && (!isset($p_options[PCLZIP_OPT_REMOVE_ALL_PATH]) || !$p_options[PCLZIP_OPT_REMOVE_ALL_PATH]))) {
                    $v_result = $this->__privAddFile($p_filedescr_list[$j], $v_header, $p_options);
                    if ($v_result !== 1) {return $v_result;}
                    $p_result_list[$v_nb++] = $v_header;
                }
            }
            return $v_result;
        }//2403
        private function __privAddFile($p_filedescr, &$p_header, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $p_filename = $p_filedescr['filename'];
            if ($p_filename === "") {
                (new self($p_filename))->__privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid file list parameter (invalid or empty list)");
                return (new self($p_filename))->error_code();
            }
            clearstatcache();
            $p_header['version']           = 20;
            $p_header['version_extracted'] = 10;
            $p_header['flag']              = 0;
            $p_header['compression']       = 0;
            $p_header['crc']               = 0;
            $p_header['compressed_size']   = 0;
            $p_header['filename_len']      = strlen($p_filename);
            $p_header['extra_len']         = 0;
            $p_header['disk']              = 0;
            $p_header['internal']          = 0;
            $p_header['offset']            = 0;
            $p_header['filename']          = $p_filename;
            $p_header['stored_filename']   = $p_filedescr['stored_filename'];
            $p_header['extra']             = '';
            $p_header['status']            = 'ok';
            $p_header['index']             = -1;
            if ($p_filedescr['type'] === 'file') {
                $p_header['external'] = 0x00000000;
                $p_header['size']     = filesize($p_filename);
            } elseif ($p_filedescr['type'] === 'folder') {
                $p_header['external'] = 0x00000010;
                $p_header['mtime']    = filemtime($p_filename);
                $p_header['size']     = filesize($p_filename);
            } elseif ($p_filedescr['type'] === 'virtual_file') {
                $p_header['external'] = 0x00000000;
                $p_header['size']     = strlen($p_filedescr['content']);
            }
            if (isset($p_filedescr['mtime'])) {$p_header['mtime'] = $p_filedescr['mtime'];}
            elseif ($p_filedescr['type'] === 'virtual_file') {$p_header['mtime'] = time();}
            else { $p_header['mtime'] = filemtime($p_filename);}
            if (isset($p_filedescr['comment'])) {
                $p_header['comment_len'] = strlen($p_filedescr['comment']);
                $p_header['comment']     = $p_filedescr['comment'];
            } else {
                $p_header['comment_len'] = 0;
                $p_header['comment']     = '';
            }
            if (isset($p_options[PCLZIP_CB_PRE_ADD])) {
                $v_local_header = array();
                $this->__privConvertHeader2FileInfo($p_header, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_PRE_ADD](PCLZIP_CB_PRE_ADD, $v_local_header);
                if ($v_result === 0) {
                    $p_header['status'] = "skipped";
                    $v_result           = 1;
                }
                if ($p_header['stored_filename'] !== $v_local_header['stored_filename']) {
                    $p_header['stored_filename'] = $this->_PclZipUtilPathReduction($v_local_header['stored_filename']);
                }
            }
            if ($p_header['stored_filename'] === "") {$p_header['status'] = "filtered";}
            if (strlen($p_header['stored_filename']) > 0xFF) { $p_header['status'] = 'filename_too_long';}
            $v_file = null;
            if ($p_header['status'] === 'ok') {
                if ($p_filedescr['type'] === 'file') {
                    if ((!isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF])) && (isset($p_options[PCLZIP_OPT_TEMP_FILE_ON]) || (isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD]) && ($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] <= $p_header['size'])))) {
                        $v_result = $this->__privAddFileUsingTempFile($p_filedescr, $p_header);//, &$p_options not used in this method
                        if ($v_result < PCLZIP_ERR_NO_ERROR) {
                            return $v_result;
                        }
                    } else {
                        if (($v_file = @fopen($p_filename, "rb")) === 0) {
                            (new self($v_file))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open file '$p_filename' in binary read mode");
                            return (new self($v_file))->error_code();
                        }
                        $v_content = @fread($v_file, $p_header['size']);
                        fclose($v_file);
                        $p_header['crc'] = @crc32($v_content);
                        if ($p_options[PCLZIP_OPT_NO_COMPRESSION]) {
                            $p_header['compressed_size'] = $p_header['size'];
                            $p_header['compression']     = 0;
                        } else {
                            $v_content = @gzdeflate($v_content);
                            $p_header['compressed_size'] = strlen($v_content);
                            $p_header['compression']     = 8;
                        }
                        if (($v_result = $this->__privWriteFileHeader($p_header)) !== 1) {
                            fclose($v_file);
                            return $v_result;
                        }
                        @fwrite($this->zip_fd, $v_content, $p_header['compressed_size']);
                    }
                } elseif ($p_filedescr['type'] === 'virtual_file') {
                    $v_content = $p_filedescr['content'];
                    $p_header['crc'] = @crc32($v_content);
                    if ($p_options[PCLZIP_OPT_NO_COMPRESSION]) {
                        $p_header['compressed_size'] = $p_header['size'];
                        $p_header['compression']     = 0;
                    } else {
                        $v_content = @gzdeflate($v_content);
                        $p_header['compressed_size'] = strlen($v_content);
                        $p_header['compression']     = 8;
                    }
                    if (($v_result = $this->__privWriteFileHeader($p_header)) !== 1) {
                        fclose($v_file);
                        return $v_result;
                    }
                    fwrite($this->zip_fd, $v_content, $p_header['compressed_size']);

                    // ----- Look for a directory
                } elseif ($p_filedescr['type'] === 'folder') {
                    if (substr($p_header['stored_filename'], -1) !== '/') {
                        $p_header['stored_filename'] .= '/';
                    }
                    $p_header['size']     = 0;
                    $p_header['external'] = 0x00000010; // Value for a folder : to be checked
                    if (($v_result = $this->__privWriteFileHeader($p_header)) !== 1) {
                        return $v_result;
                    }
                }
            }
            if (isset($p_options[PCLZIP_CB_POST_ADD])) {
                $v_local_header = array();
                $this->__privConvertHeader2FileInfo($p_header, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_POST_ADD](PCLZIP_CB_POST_ADD, $v_local_header);
                if ($v_result === 0) {$v_result = 1;}
            }
            return $v_result;
        }//2457
        private function __privAddFileUsingTempFile($p_filedescr, &$p_header){ //, &$p_options not used anywhere
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            //$v_result = PCLZIP_ERR_NO_ERROR;
            $p_filename = $p_filedescr['filename'];
            if (($v_file = @fopen($p_filename, "rb")) === 0) {
                (new self($p_filename))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open file: '{$p_filename}' in binary read mode.");
                return (new self($p_filename))->error_code();
            }
            $v_gzip_temp_name = PCLZIP_TEMPORARY_DIR . uniqid('pclzip-', true) . '.gz';
            if (($v_file_compressed = gzopen($v_gzip_temp_name, "wb")) === 0) {
                fclose($v_file);
                (new self($v_gzip_temp_name))->__privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL, 'Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary write mode');
                return (new self($v_gzip_temp_name))->error_code();
            }
            $v_size = filesize($p_filename);
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @fread($v_file, $v_read_size);
                @gzputs($v_file_compressed, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            fclose($v_file);
            gzclose($v_file_compressed);
            if (filesize($v_gzip_temp_name) < 18) {
                (new self($v_gzip_temp_name))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'gzip temporary file \'' . $v_gzip_temp_name . '\' has invalid filesize - should be minimum 18 bytes');
                return (new self($v_gzip_temp_name))->error_code();
            }
            if (($v_file_compressed = @fopen($v_gzip_temp_name, "rb")) === 0) {
                (new self($v_gzip_temp_name))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary read mode');
                return (new self($v_gzip_temp_name))->error_code();
            }
            $v_binary_data = @fread($v_file_compressed, 10);
            $v_data_header = unpack('a1id1/a1id2/a1cm/a1flag/Vmtime/a1xfl/a1os', $v_binary_data);
            $v_data_header['os'] = bin2hex($v_data_header['os']);
            @fseek($v_file_compressed, filesize($v_gzip_temp_name) - 8);
            $v_binary_data = @fread($v_file_compressed, 8);
            $v_data_footer = unpack('Vcrc/Vcompressed_size', $v_binary_data);
            $p_header['compression']     = ord($v_data_header['cm']);
            $p_header['crc']             = $v_data_footer['crc'];
            $p_header['compressed_size'] = filesize($v_gzip_temp_name) - 18;
            fclose($v_file_compressed);
            if (($v_result = $this->__privWriteFileHeader($p_header)) !== 1) { return $v_result;}
            if (($v_file_compressed = @fopen($v_gzip_temp_name, "rb")) === 0) {
                (new self($v_gzip_temp_name))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary read mode');
                return (new self($v_gzip_temp_name))->error_code();
            }
            fseek($v_file_compressed, 10);
            $v_size = $p_header['compressed_size'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @fread($v_file_compressed, $v_read_size);
                fwrite($this->zip_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            fclose($v_file_compressed);
            unlink($v_gzip_temp_name);
            return $v_result;
        }//2716
        private function __privCalculateStoredFilename(&$p_filedescr, &$p_options):int{
            $v_result = 1;
            $p_filename = $p_filedescr['filename'];
            $p_add_dir = $p_options[PCLZIP_OPT_ADD_PATH] ?? '';
            $p_remove_dir = $p_options[PCLZIP_OPT_REMOVE_PATH] ?? '';
            $p_remove_all_dir = $p_options[PCLZIP_OPT_REMOVE_ALL_PATH] ?? 0;
            if (isset($p_filedescr['new_full_name'])) {
                $v_stored_filename = $this->_PclZipUtilTranslateWinPath($p_filedescr['new_full_name']);
            } else {
                if (isset($p_filedescr['new_short_name'])) {
                    $v_path_info = pathinfo($p_filename);
                    $v_dir       = '';
                    if ($v_path_info['dirname'] !== '') {
                        $v_dir = $v_path_info['dirname'] . '/';
                    }
                    $v_stored_filename = $v_dir . $p_filedescr['new_short_name'];
                } else {$v_stored_filename = $p_filename;}
                if ($p_remove_all_dir) {
                    $v_stored_filename = basename($p_filename);
                } elseif ($p_remove_dir !== "") {
                    if (substr($p_remove_dir, -1) !== '/') {$p_remove_dir .= "/";}
                    if ((strpos($p_filename, "./") === 0) || (strpos($p_remove_dir, "./") === 0)) {
                        if ((strpos($p_filename, "./") === 0) && (strpos($p_remove_dir, "./") !== 0)) {
                            $p_remove_dir = "./" . $p_remove_dir;}
                        if ((strpos($p_filename, "./") !== 0) && (strpos($p_remove_dir, "./") === 0)) {
                            $p_remove_dir = substr($p_remove_dir, 2);}
                    }
                    $v_compare = $this->_PclZipUtilPathInclusion($p_remove_dir, $v_stored_filename);
                    if ($v_compare > 0) {
                        if ($v_compare === 2) {
                            $v_stored_filename = "";
                        } else {$v_stored_filename = substr($v_stored_filename, strlen($p_remove_dir)); }
                    }
                }
                $v_stored_filename = $this->_PclZipUtilTranslateWinPath($v_stored_filename);
                if ($p_add_dir !== "") {
                    if (substr($p_add_dir, -1) === "/") {
                        $v_stored_filename = $p_add_dir . $v_stored_filename;
                    } else {$v_stored_filename = $p_add_dir . "/" . $v_stored_filename;}
                }
            }
            $v_stored_filename              = $this->_PclZipUtilPathReduction($v_stored_filename);
            $p_filedescr['stored_filename'] = $v_stored_filename;
            return $v_result;
        }//2830
        private function __privWriteFileHeader(&$p_header):int{
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $p_header['offset'] = ftell($this->zip_fd);
            $v_date  = getdate($p_header['mtime']);
            $v_mtime = ($v_date['hours'] << 11) + ($v_date['minutes'] << 5) + $v_date['seconds'] / 2;
            $v_mdate = (($v_date['year'] - 1980) << 9) + ($v_date['mon'] << 5) + $v_date['mday'];
            $v_binary_data = pack("VvvvvvVVVvv", 0x04034b50, $p_header['version_extracted'], $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'], $p_header['compressed_size'], $p_header['size'], strlen($p_header['stored_filename']), $p_header['extra_len']);
            fwrite($this->zip_fd, $v_binary_data, 30);
            if ($p_header['stored_filename'] !== '') {
                fwrite($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));}
            if ($p_header['extra_len'] !== 0) {fwrite($this->zip_fd, $p_header['extra'], $p_header['extra_len']);}
            return $v_result;
        }//2932
        private function __privWriteCentralFileHeader(&$p_header):int{
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_date  = getdate($p_header['mtime']);
            $v_mtime = ($v_date['hours'] << 11) + ($v_date['minutes'] << 5) + $v_date['seconds'] / 2;
            $v_mdate = (($v_date['year'] - 1980) << 9) + ($v_date['mon'] << 5) + $v_date['mday'];
            $v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50, $p_header['version'], $p_header['version_extracted'], $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'], $p_header['compressed_size'], $p_header['size'], strlen($p_header['stored_filename']), $p_header['extra_len'], $p_header['comment_len'], $p_header['disk'], $p_header['internal'], $p_header['external'], $p_header['offset']);
            fwrite($this->zip_fd, $v_binary_data, 46);
            if ($p_header['stored_filename'] !== '') {
                fwrite($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));}
            if ($p_header['extra_len'] !== 0) {fwrite($this->zip_fd, $p_header['extra'], $p_header['extra_len']);}
            if ($p_header['comment_len'] !== 0) {fwrite($this->zip_fd, $p_header['comment'], $p_header['comment_len']);}
            return $v_result;
        }//2969
        private function __privWriteCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment):int{
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, $p_nb_entries, $p_nb_entries, $p_size, $p_offset, strlen($p_comment));
            fwrite($this->zip_fd, $v_binary_data, 22);
            if ($p_comment !== '') {fwrite($this->zip_fd, $p_comment, strlen($p_comment));}
            return $v_result;
        }//3010
        private function __privList(&$p_list){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $this->__privDisableMagicQuotes();
            if (($this->zip_fd = @fopen($this->zip_name, 'rb')) === 0) {
                $this->__privSwapBackMagicQuotes();
                (new self($p_list))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \'' . $this->zip_name . '\' in binary read mode');
                return (new self($p_list))->error_code();
            }
            $v_central_dir = [];
            if (!$v_result && ($v_result = $this->__privReadEndCentralDir($v_central_dir)) !== 1) {
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            @rewind($this->zip_fd);
            if (@fseek($this->zip_fd, $v_central_dir['offset'])) {
                $this->__privSwapBackMagicQuotes();
                (new self($p_list))->__privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
                return (new self($p_list))->error_code();
            }
            for ($i = 0; $i < $v_central_dir['entries']; $i++) {
                if (($v_result = $this->__privReadCentralFileHeader($v_header)) !== 1) {
                    $this->__privSwapBackMagicQuotes();
                    return $v_result;
                }
                $v_header['index'] = $i;
                $this->__privConvertHeader2FileInfo($v_header, $p_list[$i]);
                unset($v_header);
            }
            $this->__privCloseFd();
            $this->__privSwapBackMagicQuotes();
            return $v_result;
        }//3036
        private function __privConvertHeader2FileInfo($p_header, &$p_info):int{
            $v_result = 1;
            $v_temp_path               = $this->_PclZipUtilPathReduction($p_header['filename']);
            $p_info['filename']        = $v_temp_path;
            $v_temp_path               = $this->_PclZipUtilPathReduction($p_header['stored_filename']);
            $p_info['stored_filename'] = $v_temp_path;
            $p_info['size']            = $p_header['size'];
            $p_info['compressed_size'] = $p_header['compressed_size'];
            $p_info['mtime']           = $p_header['mtime'];
            $p_info['comment']         = $p_header['comment'];
            $p_info['folder']          = (($p_header['external'] & 0x00000010) === 0x00000010);
            $p_info['index']           = $p_header['index'];
            $p_info['status']          = $p_header['status'];
            $p_info['crc']             = $p_header['crc'];
            return $v_result;
        }//3120
        private function __privExtractByRule(&$p_file_list, $p_path, $p_remove_path, $p_remove_all_path, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $this->__privDisableMagicQuotes();
            if (($p_path === "") || ((strpos($p_path, "/") !== 0) && (strpos($p_path, "../") !== 0) && (substr($p_path, 1, 2) !== ":/"))) {
                $p_path = "./" . $p_path;
            }
            if (($p_path !== "./") && ($p_path !== "/")) {
                while (substr($p_path, -1) === "/") {
                    $p_path = substr($p_path, 0, -1);
                }
            }
            if (($p_remove_path !== "") && (substr($p_remove_path, -1) !== '/')) {
                $p_remove_path .= '/';
            }
            $p_remove_path_size = strlen($p_remove_path);
            if (!$v_result && ($v_result = $this->__privOpenFd('rb')) !== 1) {
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            $v_central_dir = [];
            if (($v_result = $this->__privReadEndCentralDir($v_central_dir)) !== 1) {
                $this->__privCloseFd();
                $this->__privSwapBackMagicQuotes();
                return $v_result;
            }
            $v_pos_entry = $v_central_dir['offset'];
            $j_start = 0;
            for ($i = 0, $v_nb_extracted = 0; $i < $v_central_dir['entries']; $i++) {
                rewind($this->zip_fd);
                if (fseek($this->zip_fd, $v_pos_entry)) {
                    $this->__privCloseFd();
                    $this->__privSwapBackMagicQuotes();
                    (new self($v_pos_entry))->__privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
                    return (new self($v_pos_entry))->error_code();
                }
                $v_header = [];
                if (($v_result = $this->__privReadCentralFileHeader($v_header)) !== 1) {
                    $this->__privCloseFd();
                    $this->__privSwapBackMagicQuotes();
                    return $v_result;
                }
                $v_header['index'] = $i;
                $v_pos_entry = ftell($this->zip_fd);
                $v_extract = false;
                if ((isset($p_options[PCLZIP_OPT_BY_NAME])) && ($p_options[PCLZIP_OPT_BY_NAME] !== 0)) {
                    for ($j = 0; ($j < count($p_options[PCLZIP_OPT_BY_NAME])) && (!$v_extract); $j++) {
                        if (substr($p_options[PCLZIP_OPT_BY_NAME][$j], -1) === "/") {
                            if ((strpos($v_header['stored_filename'], $p_options[PCLZIP_OPT_BY_NAME][$j]) === 0) && (strlen($v_header['stored_filename']) > strlen($p_options[PCLZIP_OPT_BY_NAME][$j]))) {
                                $v_extract = true;}
                        } elseif ($v_header['stored_filename'] === $p_options[PCLZIP_OPT_BY_NAME][$j]) {
                            $v_extract = true;}
                    }
                } elseif ((isset($p_options[PCLZIP_OPT_BY_PREG])) && ($p_options[PCLZIP_OPT_BY_PREG] !== "")) {
                    if (preg_match($p_options[PCLZIP_OPT_BY_PREG], $v_header['stored_filename'])) {
                        $v_extract = true;}
                } elseif ((isset($p_options[PCLZIP_OPT_BY_INDEX])) && ($p_options[PCLZIP_OPT_BY_INDEX] !== 0)) {
                    for ($j = $j_start; ($j < count($p_options[PCLZIP_OPT_BY_INDEX])) && (!$v_extract); $j++) {
                        if (($i >= $p_options[PCLZIP_OPT_BY_INDEX][$j]['start']) && ($i <= $p_options[PCLZIP_OPT_BY_INDEX][$j]['end'])) {
                            $v_extract = true;}
                        if ($i >= $p_options[PCLZIP_OPT_BY_INDEX][$j]['end']) {$j_start = $j + 1; }
                        if ($p_options[PCLZIP_OPT_BY_INDEX][$j]['start'] > $i) {break;}
                    }
                } else {$v_extract = true;}
                if (($v_extract) && (($v_header['compression'] !== 8) && ($v_header['compression'] !== 0))) {
                    $v_header['status'] = 'unsupported_compression';
                    if ((isset($p_options[PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                        $this->__privSwapBackMagicQuotes();
                        (new self($v_header))->__privErrorLog(PCLZIP_ERR_UNSUPPORTED_COMPRESSION, "Filename '" . $v_header['stored_filename'] . "' is " . "compressed by an unsupported compression " . "method (" . $v_header['compression'] . ") ");
                        return (new self($v_header))->error_code();
                    }
                }
                if (($v_extract) && (($v_header['flag'] & 1) === 1)) {
                    $v_header['status'] = 'unsupported_encryption';
                    if ((isset($p_options[PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                        $this->__privSwapBackMagicQuotes();
                        (new self($v_header))->__privErrorLog(PCLZIP_ERR_UNSUPPORTED_ENCRYPTION, "Unsupported encryption for " . " filename '" . $v_header['stored_filename'] . "'");
                        return(new self($v_header))->error_code();
                    }
                }
                if (($v_extract) && ($v_header['status'] !== 'ok')) {
                    $v_result = $this->__privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++]);
                    if ($v_result !== 1) {
                        $this->__privCloseFd();
                        $this->__privSwapBackMagicQuotes();
                        return $v_result;
                    }
                    $v_extract = false;
                }
                if ($v_extract) {
                    @rewind($this->zip_fd);
                    if (@fseek($this->zip_fd, $v_header['offset'])) {
                        $this->__privCloseFd();
                        $this->__privSwapBackMagicQuotes();
                        (new self($v_header))->__privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
                        return (new self($v_header))->error_code();
                    }
                    if ($p_options[PCLZIP_OPT_EXTRACT_AS_STRING]) {
                        $v_string = '';
                        $v_result1 = $this->__privExtractFileAsString($v_header, $v_string, $p_options);
                        if ($v_result1 < 1) {
                            $this->__privCloseFd();
                            $this->__privSwapBackMagicQuotes();
                            return $v_result1;
                        }
                        if (($v_result = $this->__privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted])) !== 1) {
                            $this->__privCloseFd();
                            $this->__privSwapBackMagicQuotes();
                            return $v_result;
                        }
                        $p_file_list[$v_nb_extracted]['content'] = $v_string;
                        $v_nb_extracted++;
                        if ($v_result1 === 2) {break;}
                    } elseif ((isset($p_options[PCLZIP_OPT_EXTRACT_IN_OUTPUT])) && ($p_options[PCLZIP_OPT_EXTRACT_IN_OUTPUT])) {
                        $v_result1 = $this->__privExtractFileInOutput($v_header, $p_options);
                        if ($v_result1 < 1) {
                            $this->__privCloseFd();
                            $this->__privSwapBackMagicQuotes();
                            return $v_result1;
                        }
                        if (($v_result = $this->__privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) !== 1) {
                            $this->__privCloseFd();
                            $this->__privSwapBackMagicQuotes();
                            return $v_result;
                        }
                        if ($v_result1 === 2){break;}
                    } else {
                        $v_result1 = $this->__privExtractFile($v_header, $p_path, $p_remove_path_size, $p_remove_all_path, $p_options);
                        if ($v_result1 < 1) {
                            $this->__privCloseFd();
                            $this->__privSwapBackMagicQuotes();
                            return $v_result1;
                        }
                        if (($v_result = $this->__privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) !== 1) {
                            $this->__privCloseFd();
                            $this->__privSwapBackMagicQuotes();
                            return $v_result;
                        }
                        if ($v_result1 === 2) { break;}
                    }
                }
            }

            $this->__privCloseFd();
            $this->__privSwapBackMagicQuotes();
            return $v_result;
        }//3159
        private function __privExtractFile(&$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            if (!$v_result && ($v_result = $this->__privReadFileHeader($v_header)) !== 1) {
                return $v_result;
            }
            if ($this->__privCheckFileHeaders($v_header, $p_entry) !== 1) {
                //fixme
            }
            if ($p_remove_all_path === true) {
                if (($p_entry['external'] & 0x00000010) === 0x00000010) {
                    $p_entry['status'] = "filtered";
                    return $v_result;
                }
                $p_entry['filename'] = basename($p_entry['filename']);
            } elseif ($p_remove_path !== "") {
                if ($this->_PclZipUtilPathInclusion($p_remove_path, $p_entry['filename']) === 2) {
                    $p_entry['status'] = "filtered";
                    return $v_result;
                }
                $p_remove_path_size = strlen($p_remove_path);
                if (strpos($p_entry['filename'], $p_remove_path) === 0) {
                    $p_entry['filename'] = substr($p_entry['filename'], $p_remove_path_size);
                }
            }
            if ($p_path !== '') {$p_entry['filename'] = $p_path . "/" . $p_entry['filename'];}
            if (isset($p_options[PCLZIP_OPT_EXTRACT_DIR_RESTRICTION])) {
                $v_inclusion = $this->_PclZipUtilPathInclusion($p_options[PCLZIP_OPT_EXTRACT_DIR_RESTRICTION], $p_entry['filename']);
                if ($v_inclusion === 0) {
                    (new self($p_entry))->__privErrorLog(PCLZIP_ERR_DIRECTORY_RESTRICTION, "Filename '" . $p_entry['filename'] . "' is " . "outside PCLZIP_OPT_EXTRACT_DIR_RESTRICTION");
                    return (new self($p_entry))->error_code();
                }
            }
            if (isset($p_options[PCLZIP_CB_PRE_EXTRACT])) {
                $v_local_header = [];
                $this->__privConvertHeader2FileInfo($p_entry, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_PRE_EXTRACT](PCLZIP_CB_PRE_EXTRACT, $v_local_header);
                if ($v_result === 0) {
                    $p_entry['status'] = "skipped";
                    $v_result = 1;
                }
                if ($v_result === 2) {
                    $p_entry['status'] = "aborted";
                    $v_result          = PCLZIP_ERR_USER_ABORTED;
                }
                $p_entry['filename'] = $v_local_header['filename'];
            }
            if ($p_entry['status'] === 'ok') {
               if (file_exists($p_entry['filename'])) {
                    if (is_dir($p_entry['filename'])) {
                        $p_entry['status'] = "already_a_directory";
                        if ((isset($p_options[PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                            (new self($p_entry))->__privErrorLog(PCLZIP_ERR_ALREADY_A_DIRECTORY, "Filename '" . $p_entry['filename'] . "' is " . "already used by an existing directory");
                            return (new self($p_entry))->error_code();
                        }
                    } elseif (!is_writable($p_entry['filename'])) {
                        $p_entry['status'] = "write_protected";
                        if ((isset($p_options[PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                            (new self($p_entry))->__privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL, "Filename '" . $p_entry['filename'] . "' exists " . "and is write protected");
                            return (new self($p_entry))->error_code();
                        }
                    } elseif (filemtime($p_entry['filename']) > $p_entry['mtime']) {
                        if ((isset($p_options[PCLZIP_OPT_REPLACE_NEWER])) && ($p_options[PCLZIP_OPT_REPLACE_NEWER] === true)) {
                        } else {
                            $p_entry['status'] = "newer_exist";
                            if ((isset($p_options[PCLZIP_OPT_STOP_ON_ERROR])) && ($p_options[PCLZIP_OPT_STOP_ON_ERROR] === true)) {
                                (new self($p_entry))->__privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL, "Newer version of '" . $p_entry['filename'] . "' exists " . "and option PCLZIP_OPT_REPLACE_NEWER is not selected");
                                return (new self($p_entry))->error_code();
                            }
                        }
                    } else {}
                } else {
                    if ((($p_entry['external'] & 0x00000010) === 0x00000010) || (substr($p_entry['filename'], -1) === '/')) {
                        $v_dir_to_check = $p_entry['filename'];
                    } elseif (strpos($p_entry['filename'], "/") === false) {
                        $v_dir_to_check = "";
                    } else {$v_dir_to_check = dirname($p_entry['filename']);}
                    if (($v_result = $this->__privDirCheck($v_dir_to_check, (($p_entry['external'] & 0x00000010) === 0x00000010))) !== 1) {
                        $p_entry['status'] = "path_creation_fail";
                        $v_result = 1;
                    }
                }
            }
            if (($p_entry['status'] === 'ok') && !(($p_entry['external'] & 0x00000010) === 0x00000010)) {
                if ($p_entry['compression'] === 0) {
                    if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) === 0) {
                        $p_entry['status'] = "write_error";
                        return $v_result;
                    }
                    $v_size = $p_entry['compressed_size'];
                    while ($v_size !== 0) {
                        $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                        $v_buffer    = fread($this->zip_fd, $v_read_size);
                        fwrite($v_dest_file, $v_buffer, $v_read_size);
                        $v_size -= $v_read_size;
                    }
                    fclose($v_dest_file);
                    touch($p_entry['filename'], $p_entry['mtime']);
                } else {
                    if (($p_entry['flag'] & 1) === 1) {
                        (new self($p_entry))->__privErrorLog(PCLZIP_ERR_UNSUPPORTED_ENCRYPTION, 'File \'' . $p_entry['filename'] . '\' is encrypted. Encrypted files are not supported.');
                        return (new self($p_entry))->error_code();
                    }
                    if ((!isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF])) && (isset($p_options[PCLZIP_OPT_TEMP_FILE_ON]) || (isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD]) && ($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] <= $p_entry['size'])))) {
                        $v_result = $this->__privExtractFileUsingTempFile($p_entry, $p_options);
                        if ($v_result < PCLZIP_ERR_NO_ERROR) { return $v_result;}
                    } else {
                        $v_buffer = fread($this->zip_fd, $p_entry['compressed_size']);
                        $v_file_content = @gzinflate($v_buffer);
                        unset($v_buffer);
                        if ($v_file_content === false) {
                            $p_entry['status'] = "error";
                            return $v_result;
                        }
                        if (($v_dest_file = fopen($p_entry['filename'], 'wb')) === 0) {
                            $p_entry['status'] = "write_error";
                            return $v_result;
                        }
                        fwrite($v_dest_file, $v_file_content, $p_entry['size']);
                        unset($v_file_content);
                        fclose($v_dest_file);
                    }
                    touch($p_entry['filename'], $p_entry['mtime']);
                }
                if (isset($p_options[PCLZIP_OPT_SET_CHMOD])) {
                    chmod($p_entry['filename'], $p_options[PCLZIP_OPT_SET_CHMOD]);
                }
            }
            if ($p_entry['status'] === "aborted") {$p_entry['status'] = "skipped";}
            elseif (isset($p_options[PCLZIP_CB_POST_EXTRACT])) {
                $v_local_header = [];
                $this->__privConvertHeader2FileInfo($p_entry, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_POST_EXTRACT](PCLZIP_CB_POST_EXTRACT, $v_local_header);
                if ($v_result === 2) {$v_result = PCLZIP_ERR_USER_ABORTED;}
            }
            return $v_result;
        }//3468
        private function __privExtractFileUsingTempFile(&$p_entry, &$v_file){//options swapped for file
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_gzip_temp_name = PCLZIP_TEMPORARY_DIR . uniqid('pclzip-',true) . '.gz';
            if (($v_dest_file = @fopen($v_gzip_temp_name, "wb")) === 0) {
                fclose($v_file);
                (new self($v_gzip_temp_name))->__privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL, 'Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary write mode');
                return (new self($v_gzip_temp_name))->error_code();
            }
            $v_binary_data = pack('va1a1Va1a1', 0x8b1f, chr($p_entry['compression']), chr(0x00), time(), chr(0x00), chr(3));
            @fwrite($v_dest_file, $v_binary_data, 10);
            $v_size = $p_entry['compressed_size'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @fread($this->zip_fd, $v_read_size);
                @fwrite($v_dest_file, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $v_binary_data = pack('VV', $p_entry['crc'], $p_entry['size']);
            @fwrite($v_dest_file, $v_binary_data, 8);
            @fclose($v_dest_file);
            if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) === 0) {
                $p_entry['status'] = "write_error";
                return $v_result;
            }
            if (($v_src_file = @gzopen($v_gzip_temp_name, 'rb')) === 0) {
                @fclose($v_dest_file);
                $p_entry['status'] = "read_error";
                (new self($v_gzip_temp_name))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \'' . $v_gzip_temp_name . '\' in binary read mode');
                return  (new self($v_gzip_temp_name))->error_code();
            }
            $v_size = $p_entry['size'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @gzread($v_src_file, $v_read_size);
                fwrite($v_dest_file, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            fclose($v_dest_file);
            gzclose($v_src_file);
            unlink($v_gzip_temp_name);
            return $v_result;
        }//3779
        private function __privExtractFileInOutput(&$p_entry, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            if (!$v_result && ($v_result = $this->__privReadFileHeader($v_header)) !== 1) {
                return $v_result;
            }
            if ($this->__privCheckFileHeaders($v_header, $p_entry) !== 1) {
                // fixme
            }
            if (isset($p_options[PCLZIP_CB_PRE_EXTRACT])) {
                $v_local_header = [];
                $this->__privConvertHeader2FileInfo($p_entry, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_PRE_EXTRACT](PCLZIP_CB_PRE_EXTRACT, $v_local_header);
                if ($v_result === 0) {
                    $p_entry['status'] = "skipped";
                    $v_result          = 1;
                }
                if ($v_result === 2) {
                    $p_entry['status'] = "aborted";
                    $v_result          = PCLZIP_ERR_USER_ABORTED;
                }
                $p_entry['filename'] = $v_local_header['filename'];
            }
            if (($p_entry['status'] === 'ok') && !(($p_entry['external'] & 0x00000010) === 0x00000010)) {
                if ($p_entry['compressed_size'] === $p_entry['size']) {
                    $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);
                    echo $v_buffer;
                    unset($v_buffer);
                } else {
                    $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);
                    $v_file_content = gzinflate($v_buffer);
                    unset($v_buffer);
                    echo $v_file_content;
                    unset($v_file_content);
                }
            }
            if ($p_entry['status'] === "aborted") { $p_entry['status'] = "skipped";
            } elseif (isset($p_options[PCLZIP_CB_POST_EXTRACT])) {
                $v_local_header = [];
                $this->__privConvertHeader2FileInfo($p_entry, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_POST_EXTRACT](PCLZIP_CB_POST_EXTRACT, $v_local_header);
                if ($v_result === 2) {$v_result = PCLZIP_ERR_USER_ABORTED;}
            }
            return $v_result;
        }//3855
        private function __privExtractFileAsString(&$p_entry, &$p_string, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_header = array();
            if (!$v_result && ($v_result = $this->__privReadFileHeader($v_header)) !== 1) { return $v_result;}
            if ($this->__privCheckFileHeaders($v_header, $p_entry) !== 1) {
                // fixme
            }
            if (isset($p_options[PCLZIP_CB_PRE_EXTRACT])) {
                $v_local_header = [];
                $this->__privConvertHeader2FileInfo($p_entry, $v_local_header);
                $v_result = $p_options[PCLZIP_CB_PRE_EXTRACT](PCLZIP_CB_PRE_EXTRACT, $v_local_header);
                if ($v_result === 0) {
                    $p_entry['status'] = "skipped";
                    $v_result          = 1;
                }
                if ($v_result === 2) {
                    $p_entry['status'] = "aborted";
                    $v_result = PCLZIP_ERR_USER_ABORTED;
                }
                $p_entry['filename'] = $v_local_header['filename'];
            }
            if ($p_entry['status'] === 'ok') {
                if (!(($p_entry['external'] & 0x00000010) === 0x00000010)) {
                    if ($p_entry['compression'] === 0) {
                        $p_string = @fread($this->zip_fd, $p_entry['compressed_size']);
                    } else {
                        $v_data = @fread($this->zip_fd, $p_entry['compressed_size']);
                        if (($p_string = @gzinflate($v_data)) === false) {/*fixme*/ }
                    }
                } else {
                    // fixme : error : can not extract a folder in a string
                }
            }
            if ($p_entry['status'] === "aborted"){ $p_entry['status'] = "skipped";}
            elseif (isset($p_options[PCLZIP_CB_POST_EXTRACT])) {
                $v_local_header = [];
                $this->__privConvertHeader2FileInfo($p_entry, $v_local_header);
                $v_local_header['content'] = $p_string;
                $p_string                  = '';
                $v_result = $p_options[PCLZIP_CB_POST_EXTRACT](PCLZIP_CB_POST_EXTRACT, $v_local_header);
                $p_string = $v_local_header['content'];
                unset($v_local_header['content']);
                if ($v_result === 2) { $v_result = PCLZIP_ERR_USER_ABORTED;}
            }
            return $v_result;
        }//3964
        private function __privReadFileHeader(&$p_header):int{
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_binary_data = @fread($this->zip_fd, 4);
            $v_data        = unpack('Vid', $v_binary_data);
            if ($v_data['id'] !== 0x04034b50) {
                (new self($v_data))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Invalid archive structure');
                return (new self($v_data))->error_code();
            }
            $v_binary_data = fread($this->zip_fd, 26);
            if (strlen($v_binary_data) !== 26) {
                $p_header['filename'] = "";
                $p_header['status']   = "invalid_header";
                (new self($v_binary_data))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Invalid block size : " . strlen($v_binary_data));
                return (new self($v_binary_data))->error_code();
            }
            $v_data = unpack('vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $v_binary_data);
            $p_header['filename'] = fread($this->zip_fd, $v_data['filename_len']);
            if ($v_data['extra_len'] !== 0) {
                $p_header['extra'] = fread($this->zip_fd, $v_data['extra_len']);
            } else {$p_header['extra'] = '';}
            $p_header['version_extracted'] = $v_data['version'];
            $p_header['compression']       = $v_data['compression'];
            $p_header['size']              = $v_data['size'];
            $p_header['compressed_size']   = $v_data['compressed_size'];
            $p_header['crc']               = $v_data['crc'];
            $p_header['flag']              = $v_data['flag'];
            $p_header['filename_len']      = $v_data['filename_len'];
            $p_header['mdate'] = $v_data['mdate'];
            $p_header['mtime'] = $v_data['mtime'];
            if ($p_header['mdate'] && $p_header['mtime']) {
                $v_hour    = ($p_header['mtime'] & 0xF800) >> 11;
                $v_minute  = ($p_header['mtime'] & 0x07E0) >> 5;
                $v_second = ($p_header['mtime'] & 0x001F) * 2;
                $v_year  = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
                $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
                $v_day   = $p_header['mdate'] & 0x001F;
                $p_header['mtime'] = @mktime($v_hour, $v_minute, $v_second, $v_month, $v_day, $v_year);
            } else { $p_header['mtime'] = time(); }
            $p_header['stored_filename'] = $p_header['filename'];
            $p_header['status'] = "ok";
            return $v_result;
        }//4081
        private function __privReadCentralFileHeader(&$p_header):int{
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_binary_data = @fread($this->zip_fd, 4);
            $v_data        = unpack('Vid', $v_binary_data);
            if ($v_data['id'] !== 0x02014b50) {
                (new self($v_data))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Invalid archive structure');
                return (new self($v_data))->error_code();
            }
            $v_binary_data = fread($this->zip_fd, 42);
            if (strlen($v_binary_data) !== 42) {
                $p_header['filename'] = "";
                $p_header['status']   = "invalid_header";
                (new self($v_binary_data))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Invalid block size : " . strlen($v_binary_data));
                return (new self($v_binary_data))->error_code();
            }
            $p_header = unpack('vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $v_binary_data);
            if ($p_header['filename_len'] !== 0) {
                $p_header['filename'] = fread($this->zip_fd, $p_header['filename_len']);
            } else {$p_header['filename'] = '';}
            if ($p_header['extra_len'] !== 0) {
                $p_header['extra'] = fread($this->zip_fd, $p_header['extra_len']);
            } else {$p_header['extra'] = '';}
            if ($p_header['comment_len'] !== 0) {
                $p_header['comment'] = fread($this->zip_fd, $p_header['comment_len']);
            } else { $p_header['comment'] = '';}
            if (1) {
                $v_hour    = ($p_header['mtime'] & 0xF800) >> 11;
                $v_minute  = ($p_header['mtime'] & 0x07E0) >> 5;
                $v_second = ($p_header['mtime'] & 0x001F) * 2;
                $v_year  = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
                $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
                $v_day   = $p_header['mdate'] & 0x001F;
                $p_header['mtime'] = @mktime($v_hour, $v_minute, $v_second, $v_month, $v_day, $v_year);
            } else {$p_header['mtime'] = time();}
            $p_header['stored_filename'] = $p_header['filename'];
            $p_header['status'] = 'ok';
            if (substr($p_header['filename'], -1) === '/') {$p_header['external'] = 0x00000010;}
            return $v_result;
        }//4178
        private function __privCheckFileHeaders(&$p_local_header, &$p_central_header):int{
            $v_result = 1;
            //fixme
            if ($p_local_header['filename'] !== $p_central_header['filename']) {
            }
            if ($p_local_header['version_extracted'] !== $p_central_header['version_extracted']) {
            }
            if ($p_local_header['flag'] !== $p_central_header['flag']) {
            }
            if ($p_local_header['compression'] !== $p_central_header['compression']) {
            }
            if ($p_local_header['mtime'] !== $p_central_header['mtime']) {
            }
            if ($p_local_header['filename_len'] !== $p_central_header['filename_len']) {
            }
            if (($p_local_header['flag'] & 8) === 8) {
                $p_local_header['size']            = $p_central_header['size'];
                $p_local_header['compressed_size'] = $p_central_header['compressed_size'];
                $p_local_header['crc']             = $p_central_header['crc'];
            }
            return $v_result;
        }//4283
        private function __privReadEndCentralDir(&$p_central_dir){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = 1;
            $v_pos = null;
            $v_size = filesize($this->zip_name);
            @fseek($this->zip_fd, $v_size);
            if (@ftell($this->zip_fd) !== $v_size) {
                (new self($v_size))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Unable to go to the end of the archive \'' . $this->zip_name . '\'');
                return (new self($v_size))->error_code();
            }
            $v_found = 0;
            if ($v_size > 26) {
                @fseek($this->zip_fd, $v_size - 22);
                if (($v_pos = @ftell($this->zip_fd)) !== ($v_size - 22)) {
                    (new self($v_size))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Unable to seek back to the middle of the archive \'' . $this->zip_name . '\'');
                    return (new self($v_size))->error_code();
                }
                $v_binary_data = @fread($this->zip_fd, 4);
                $v_data        = @unpack('Vid', $v_binary_data);
                if ($v_data['id'] === 0x06054b50) {$v_found = 1;}
                $v_pos = ftell($this->zip_fd);
            }
            if (!$v_found) {
                $v_maximum_size = 65557; // 0xFFFF + 22;
                if ($v_maximum_size > $v_size) {
                    $v_maximum_size = $v_size;
                }
                @fseek($this->zip_fd, $v_size - $v_maximum_size);
                if (@ftell($this->zip_fd) !== ($v_size - $v_maximum_size)) {
                    (new self($v_size))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Unable to seek back to the middle of the archive \'' . $this->zip_name . '\'');
                    return (new self($v_size))->error_code();
                }
                $v_pos   = ftell($this->zip_fd);
                $v_bytes = 0x00000000;
                while ($v_pos < $v_size) {
                    $v_byte = @fread($this->zip_fd, 1);
                    $v_bytes = (($v_bytes & 0xFFFFFF) << 8) | ord($v_byte);
                    if ($v_bytes === 0x504b0506) {
                        $v_pos++;
                        break;
                    }
                    $v_pos++;
                }
                if ($v_pos === $v_size) {
                    (new self($v_size))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Unable to find End of Central Dir Record signature");
                    return  (new self($v_size))->error_code();
                }
            }
            $v_binary_data = fread($this->zip_fd, 18);
            if (strlen($v_binary_data) !== 18) {
                (new self($v_binary_data))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Invalid End of Central Dir Record size : " . strlen($v_binary_data));
                return  (new self($v_binary_data))->error_code();
            }
            $v_data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $v_binary_data);
            if ((($v_pos + $v_data['comment_size'] + 18) !== $v_size) && 0) {
                (new self($v_pos))->__privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'The central dir is not at the end of the archive.' . ' Some trailing bytes exists after the archive.');
                return (new self($v_pos))->error_code();
            }
            if ($v_data['comment_size'] !== 0) {
                $p_central_dir['comment'] = fread($this->zip_fd, $v_data['comment_size']);
            } else {$p_central_dir['comment'] = '';}
            $p_central_dir['entries']      = $v_data['entries'];
            $p_central_dir['disk_entries'] = $v_data['disk_entries'];
            $p_central_dir['offset']       = $v_data['offset'];
            $p_central_dir['size']         = $v_data['size'];
            $p_central_dir['disk']         = $v_data['disk'];
            $p_central_dir['disk_start']   = $v_data['disk_start'];
            return $v_result;
        }//4320
        private function __privDeleteByRule(&$p_result_list, &$p_options){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result      = 1;//todo to checkup
            //$v_list_detail = [];
            if (!$v_result && ($v_result = $this->__privOpenFd('rb')) !== 1) {
                return $v_result;
            }
            $v_central_dir = array();
            if (($v_result = $this->__privReadEndCentralDir($v_central_dir)) !== 1) {
                $this->__privCloseFd();
                return $v_result;
            }
            @rewind($this->zip_fd);
            $v_pos_entry = $v_central_dir['offset'];
            @rewind($this->zip_fd);
            if (@fseek($this->zip_fd, $v_pos_entry)) {
                $this->__privCloseFd();
                (new self($v_pos_entry))->__privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
                return (new self($v_pos_entry))->error_code();
            }
            $v_header_list = [];
            $j_start       = 0;
            for ($i = 0, $v_nb_extracted = 0; $i < $v_central_dir['entries']; $i++) {
                $v_header_list[$v_nb_extracted] = array();
                if (($v_result = $this->__privReadCentralFileHeader($v_header_list[$v_nb_extracted])) !== 1) {
                    $this->__privCloseFd();
                    return $v_result;
                }
                $v_header_list[$v_nb_extracted]['index'] = $i;
                $v_found = false;
                if ((isset($p_options[PCLZIP_OPT_BY_NAME])) && ($p_options[PCLZIP_OPT_BY_NAME] !== 0)) {
                    for ($j = 0; ($j < count($p_options[PCLZIP_OPT_BY_NAME])) && (!$v_found); $j++) {
                        if (substr($p_options[PCLZIP_OPT_BY_NAME][$j], -1) === "/") {
                            if ((strpos($v_header_list[$v_nb_extracted]['stored_filename'], $p_options[PCLZIP_OPT_BY_NAME][$j]) === 0) && (strlen($v_header_list[$v_nb_extracted]['stored_filename']) > strlen($p_options[PCLZIP_OPT_BY_NAME][$j]))) {
                                $v_found = true;
                            } elseif ((($v_header_list[$v_nb_extracted]['external'] & 0x00000010) === 0x00000010) /* Indicates a folder */ && ($v_header_list[$v_nb_extracted]['stored_filename'] . '/' === $p_options[PCLZIP_OPT_BY_NAME][$j])) {
                                $v_found = true;}
                        } elseif ($v_header_list[$v_nb_extracted]['stored_filename'] === $p_options[PCLZIP_OPT_BY_NAME][$j]) {
                            $v_found = true;}
                    }
                } elseif ((isset($p_options[PCLZIP_OPT_BY_PREG])) && ($p_options[PCLZIP_OPT_BY_PREG] !== "")) {
                    if (preg_match($p_options[PCLZIP_OPT_BY_PREG], $v_header_list[$v_nb_extracted]['stored_filename'])) {
                        $v_found = true;}
                } elseif ((isset($p_options[PCLZIP_OPT_BY_INDEX])) && ($p_options[PCLZIP_OPT_BY_INDEX] !== 0)) {
                    for ($j = $j_start; ($j < count($p_options[PCLZIP_OPT_BY_INDEX])) && (!$v_found); $j++) {
                        if (($i >= $p_options[PCLZIP_OPT_BY_INDEX][$j]['start']) && ($i <= $p_options[PCLZIP_OPT_BY_INDEX][$j]['end'])) {
                            $v_found = true;}
                        if ($i >= $p_options[PCLZIP_OPT_BY_INDEX][$j]['end']) {
                            $j_start = $j + 1;}
                        if ($p_options[PCLZIP_OPT_BY_INDEX][$j]['start'] > $i) {
                            break;}
                    }
                } else {$v_found = true;}
                if ($v_found) { unset($v_header_list[$v_nb_extracted]);
                } else {$v_nb_extracted++;}
            }
            if ($v_nb_extracted > 0) {
                $v_zip_temp_name = PCLZIP_TEMPORARY_DIR . uniqid('pclzip-',true) . '.tmp';
                $v_temp_zip = new self($v_zip_temp_name);
                $v_temp_zip->zip_fd = 0 ?: fopen($v_temp_zip->zip_name,'wb');
                if (($v_result = $v_temp_zip->__privOpenFd('wb')) !== 1) {
                    $this->__privCloseFd();
                    return $v_result;
                }
                for ($i = 0, $iMax = count($v_header_list); $i < $iMax; $i++) {
                    @rewind($this->zip_fd);
                    if (@fseek($this->zip_fd, $v_header_list[$i]['offset'])) {
                        $this->__privCloseFd();
                        $v_temp_zip->__privCloseFd();
                        @unlink($v_zip_temp_name);
                        (new self($v_header_list))->__privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');
                        return (new self($v_header_list))->error_code();
                    }
                    $v_local_header = array();
                    if (($v_result = $this->__privReadFileHeader($v_local_header)) !== 1) {
                        $this->__privCloseFd();
                        $v_temp_zip->__privCloseFd();
                        @unlink($v_zip_temp_name);
                        return $v_result;
                    }
                    if ($this->__privCheckFileHeaders($v_local_header, $v_header_list[$i]) !== 1) {
                        // fixme
                    }
                    unset($v_local_header);
                    if (($v_result = $v_temp_zip->__privWriteFileHeader($v_header_list[$i])) !== 1) {
                        $this->__privCloseFd();
                        $v_temp_zip->__privCloseFd();
                        @unlink($v_zip_temp_name);
                        return $v_result;
                    }
                    if (($v_result = $this->_PclZipUtilCopyBlock($this->zip_fd, $v_temp_zip->zip_fd, $v_header_list[$i]['compressed_size'])) !== 1) {
                        $this->__privCloseFd();
                        $v_temp_zip->__privCloseFd();
                        @unlink($v_zip_temp_name);
                        return $v_result;
                    }
                }
                $v_offset = @ftell($v_temp_zip->zip_fd);
                for ($i = 0, $iMax = count($v_header_list); $i < $iMax; $i++) {
                    if (($v_result = $v_temp_zip->__privWriteCentralFileHeader($v_header_list[$i])) !== 1) {
                        $v_temp_zip->__privCloseFd();
                        $this->__privCloseFd();
                        @unlink($v_zip_temp_name);
                        return $v_result;
                    }
                    $v_temp_zip->__privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
                }
                $v_comment = $p_options[PCLZIP_OPT_COMMENT] ?? '';
                $v_size = @ftell($v_temp_zip->zip_fd) - $v_offset;
                if (($v_result = $v_temp_zip->__privWriteCentralHeader(count($v_header_list), $v_size, $v_offset, $v_comment)) !== 1) {
                    unset($v_header_list);
                    $v_temp_zip->__privCloseFd();
                    $this->__privCloseFd();
                    @unlink($v_zip_temp_name);
                    return $v_result;
                }
                $v_temp_zip->__privCloseFd();
                $this->__privCloseFd();
                @unlink($this->zip_name);
                $this->_PclZipUtilRename($v_zip_temp_name, $this->zip_name);
                unset($v_temp_zip);
            } elseif ($v_central_dir['entries'] !== 0) {
                $this->__privCloseFd();
                if (($v_result = $this->__privOpenFd('wb')) !== 1) {
                    return $v_result;}
                if (($v_result = $this->__privWriteCentralHeader(0, 0, 0, '')) !== 1) {
                    return $v_result;}
                $this->__privCloseFd();
            }
            return $v_result;
        }//4469
        private function __privDirCheck($p_dir, $p_is_dir=false){
            $v_result = 1;
            if (($p_is_dir) && (substr($p_dir, -1) === '/')) {
                $p_dir = substr($p_dir, 0, -1);}
            if (($p_dir === "") || (is_dir($p_dir))) { return 1; }
            $p_parent_dir = dirname($p_dir);
            if (($p_parent_dir !== $p_dir) && ($p_parent_dir !== "") && ($v_result = $this->__privDirCheck($p_parent_dir)) !== 1) {
                return $v_result;}
            if (!mkdir($p_dir, 0777) && !is_dir($p_dir)) {
                (new self($p_dir))->__privErrorLog(PCLZIP_ERR_DIR_CREATE_FAIL, "Unable to create directory '$p_dir'");
                return (new self($p_dir))->error_code();
            }
            return $v_result;
        }//4759
        private function __privMerge(self $p_archive_to_add){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $p_archive_to_add->zip_fd = 0 ?: fopen($p_archive_to_add->zip_name,'wb');
            $v_result = null;
            if (!is_file($p_archive_to_add->zip_name)) {
                $v_result = 1;
                return $v_result;
            }
            if (!is_file($this->zip_name)) {
                $v_result = $this->__privDuplicate($p_archive_to_add->zip_name);
                return $v_result;
            }
            if (($v_result = $this->__privOpenFd('rb')) !== 1) {
                return $v_result;
            }
            $v_central_dir = array();
            if (($v_result = $this->__privReadEndCentralDir($v_central_dir)) !== 1) {
                $this->__privCloseFd();
                return $v_result;
            }
            @rewind($this->zip_fd);
            if (($v_result = $p_archive_to_add->__privOpenFd('rb')) !== 1) {
                $this->__privCloseFd();
                return $v_result;
            }
            $v_central_dir_to_add = [];
            if (($v_result = $p_archive_to_add->__privReadEndCentralDir($v_central_dir_to_add)) !== 1) {
                $this->__privCloseFd();
                $p_archive_to_add->__privCloseFd();
                return $v_result;
            }
            @rewind($p_archive_to_add->zip_fd);
            $v_zip_temp_name = PCLZIP_TEMPORARY_DIR . uniqid('pclzip-',true) . '.tmp';
            if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) === 0) {
                $this->__privCloseFd();
                $p_archive_to_add->__privCloseFd();
                (new self($v_zip_temp_name))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open temporary file: '{$v_zip_temp_name}' in binary write mode.");
                return (new self($v_zip_temp_name))->error_code();
            }
            $v_size = $v_central_dir['offset'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = fread($this->zip_fd, $v_read_size);
                @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $v_size = $v_central_dir_to_add['offset'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = fread($p_archive_to_add->zip_fd, $v_read_size);
                @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $v_offset = @ftell($v_zip_temp_fd);
            $v_size = $v_central_dir['size'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @fread($this->zip_fd, $v_read_size);
                @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $v_size = $v_central_dir_to_add['size'];
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = @fread($p_archive_to_add->zip_fd, $v_read_size);
                @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $v_comment = $v_central_dir['comment'] . ' ' . $v_central_dir_to_add['comment'];
            $v_size = @ftell($v_zip_temp_fd) - $v_offset;
            $v_swap        = $this->zip_fd;
            $this->zip_fd  = $v_zip_temp_fd;
            $v_zip_temp_fd = $v_swap;
            if (($v_result = $this->__privWriteCentralHeader($v_central_dir['entries'] + $v_central_dir_to_add['entries'], $v_size, $v_offset, $v_comment)) !== 1) {
                $this->__privCloseFd();
                $p_archive_to_add->__privCloseFd();
                @fclose($v_zip_temp_fd);
                $this->zip_fd = null;
                unset($v_header_list);
                return $v_result;
            }
            $v_swap        = $this->zip_fd;
            $this->zip_fd  = $v_zip_temp_fd;
            $v_zip_temp_fd = $v_swap;
            $this->__privCloseFd();
            $p_archive_to_add->__privCloseFd();
            @fclose($v_zip_temp_fd);
            @unlink($this->zip_name);
            $this->_PclZipUtilRename($v_zip_temp_name, $this->zip_name);
            return $v_result;
        }//4807
        private function __privDuplicate($p_archive_filename){
            $this->zip_fd = 0 ?: fopen($this->zip_name,'wb');
            $v_result = null;
            if (!is_file($p_archive_filename)) {
                $v_result = 1;
                return $v_result;
            }
            if (($v_result = $this->__privOpenFd('wb')) !== 1) { return $v_result;}
             if (($v_zip_temp_fd = @fopen($p_archive_filename, 'rb')) === 0) {
                $this->__privCloseFd();
                 (new self($p_archive_filename))->__privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open archive file: '{$p_archive_filename}'  in binary write mode. ");
                return (new self($p_archive_filename))->error_code();
            }
            $v_size = filesize($p_archive_filename);
            while ($v_size !== 0) {
                $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
                $v_buffer    = fread($v_zip_temp_fd, $v_read_size);
                @fwrite($this->zip_fd, $v_buffer, $v_read_size);
                $v_size -= $v_read_size;
            }
            $this->__privCloseFd();
            @fclose($v_zip_temp_fd);
            return $v_result;
        }//4981
        private function __privErrorLog($p_error_code=0, $p_error_string=''): void{
            if (PCLZIP_ERROR_EXTERNAL === 1) {
                throw new \RuntimeException($p_error_code, $p_error_string);
            }
            (new static('p_zipname'))->error_code = $p_error_code;
            (new static('p_zipname'))->error_string = $p_error_string;
        }//5037
        private function __privErrorReset():void{
            if (PCLZIP_ERROR_EXTERNAL === 1){throw new \RuntimeException('External error');}
            $this->error_code = 0;
            $this->error_string = '';
        }//5053
        private function __privDisableMagicQuotes():int{
            $v_result=1;
            if ($this->magic_quotes_status !== -1) { return $v_result;}
            return $v_result;
        }//5070
        private function __privSwapBackMagicQuotes():int{
            $v_result=1;
            if ($this->magic_quotes_status !== -1) {return $v_result;}
            return $v_result;
        }//5103
    }
}else{die;}