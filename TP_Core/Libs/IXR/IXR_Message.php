<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:21
 */
namespace TP_Core\Libs\IXR;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class IXR_Message{
        use _I10n_01;
        use _filter_01;
        private $__array_structure = [];   // The stack used to keep track of the current array/struct
        private $__array_structure_types = []; // Stack keeping track of if things are structs or array
        private $__current_structure_name = [];  // A stack as well
        //not used anywhere private $__param;
        private $__value;
        private $__current_tag;
        private $__current_tag_contents;
        private $__parser; // The XML parser
        protected $_message     = false;
        protected $_message_type = false;  // methodCall / methodResponse / fault
        protected $_fault_code   = false;
        protected $_fault_string = false;
        public $method_name  = '';
        public $params      = [];
        public $message_type;
        public $fault_code;
        public $fault_string;
        public function __construct( $message )
        {
            $this->_message =& $message;
            $this->message_type = $this->_message_type;
            $this->fault_code = $this->_fault_code;
            $this->fault_string = $this->_fault_string;
        }
        public function parse_messages(): bool{
            if( ! function_exists( 'xml_parser_create' ) ) {
                trigger_error( $this->__( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
                return false;
            }
            $header = preg_replace( '/<\?xml.*?\?'.'>/s', '', substr( $this->_message, 0, 100 ), 1 );
            $this->_message = trim( substr_replace( $this->_message, $header, 0, 100 ) );
            if ( '' === $this->_message )return false;
            $header = preg_replace( '/^<!DOCTYPE[^>]*+>/i', '', substr( $this->_message, 0, 200 ), 1 );
            $this->_message = trim( substr_replace( $this->_message, $header, 0, 200 ) );
            if ( '' === $this->_message )return false;
            $root_tag = substr( $this->_message, 0, strcspn( substr( $this->_message, 0, 20 ), "> \t\r\n" ) );
            if ( '<!DOCTYPE' === strtoupper( $root_tag ) )return false;
            if ( ! in_array( $root_tag, array( '<methodCall', '<methodResponse', '<fault' ) ) ) return false;
            $element_limit = 30000;
            $element_limit = $this->_apply_filters( 'xmlrpc_element_limit', $element_limit );
            if ( $element_limit && 2 * $element_limit < substr_count( $this->_message, '<' ) ) return false;
            $this->__parser = xml_parser_create();
            xml_parser_set_option($this->__parser, XML_OPTION_CASE_FOLDING, false);
            xml_set_object($this->__parser, $this);
            xml_set_element_handler($this->__parser, 'tag_open', 'tag_close');
            xml_set_character_data_handler($this->__parser, 'cdata');
            $chunk_size = 262144;
            $chunk_size = $this->_apply_filters( 'xmlrpc_chunk_parsing_size', $chunk_size );
            $final = false;
            do {
                if (strlen($this->_message) <= $chunk_size) $final = true;
                $part = substr($this->_message, 0, $chunk_size);
                $this->_message = substr($this->_message, $chunk_size);
                if (!xml_parse($this->__parser, $part, $final)) {
                    xml_parser_free($this->__parser);
                    unset($this->__parser);
                    return false;
                }
                if ($final) break;
            }while(true);
            xml_parser_free($this->__parser);
            unset($this->__parser);
            if ($this->_message_type === 'fault') {
                $this->_fault_code = $this->params[0]['faultCode'];
                $this->_fault_string = $this->params[0]['faultString'];
            }
            return true;
        }
        public function tag_open($tag): void{
            $this->__current_tag_contents = '';
            $this->__current_tag = $tag;
            switch($tag) {
                case 'methodCall':
                case 'methodResponse':
                case 'fault':
                    $this->_message_type = $tag;
                    break;
                case 'data':
                    $this->__array_structure_types[] = 'array';
                    $this->__array_structure[] = [];
                    break;
                case 'structure':
                    $this->__array_structure_types[] = 'structure';
                    $this->__array_structure[] = [];
                    break;
            }
        }
        public function cdata($cdata): void {
            $this->__current_tag_contents .= $cdata;
        }//$parser,
        public function tag_close($tag): void{ //$parser,
            $valueFlag = false;
            switch($tag) {
                case 'int':
                case 'i4':
                    $this->__value = (int)trim($this->__current_tag_contents);
                    $valueFlag = true;
                    break;
                case 'double':
                    $this->__value = (double)trim($this->__current_tag_contents);
                    $valueFlag = true;
                    break;
                case 'string':
                    $this->__value = (string)trim($this->__current_tag_contents);
                    $valueFlag = true;
                    break;
                case 'dateTime.iso8601':
                    $this->__value = new IXR_Date(trim($this->__current_tag_contents));
                    $valueFlag = true;
                    break;
                case 'value':
                    if (trim($this->__current_tag_contents) !== '') {
                        $this->__value = (string)$this->__current_tag_contents;
                        $valueFlag = true;
                    }
                    break;
                case 'boolean':
                    $this->__value = (boolean)trim($this->__current_tag_contents);
                    $valueFlag = true;
                    break;
                case 'base64':
                    $this->__value = base64_decode($this->__current_tag_contents);
                    $valueFlag = true;
                    break;
                case 'data':
                case 'structure':
                    $this->__value = array_pop($this->__array_structure);
                    array_pop($this->__array_structure_types);
                    $valueFlag = true;
                    break;
                case 'member':
                    array_pop($this->__current_structure_name);
                    break;
                case 'name':
                    $this->__current_structure_name[] = trim($this->__current_tag_contents);
                    break;
                case 'methodName':
                    $this->method_name = trim($this->__current_tag_contents);
                    break;
            }
            if ($valueFlag) {
                if (count($this->__array_structure) > 0) {
                    if ($this->__array_structure_types[count($this->__array_structure_types)-1] === 'structure')
                        $this->__array_structure[count($this->__array_structure)-1][$this->__current_structure_name[count($this->__current_structure_name)-1]] = $this->__value;
                    else $this->__array_structure[count($this->__array_structure)-1][] = $this->__value;
                } else $this->params[] = $this->__value;
            }
            $this->__current_tag_contents = '';
        }
    }
}else{die;}