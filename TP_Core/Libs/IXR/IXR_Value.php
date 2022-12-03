<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 13:44
 */
namespace TP_Core\Libs\IXR;
if(ABSPATH){
    class IXR_Value{
        protected $_data;
        protected $_type;
        public function __construct( $data, $type = false ){
            $this->_data = $data;
            if (!$type) $type = (bool) $this->calculateType();
            $this->_type = $type;
            if ($this->_type === 'structure') {
                foreach ($this->_data as $key => $value)
                    $this->_data[$key] = new IXR_Value($value);
            }
            if ($this->_type === 'array') {
                for ($i = 0, $j = count($this->_data); $i < $j; $i++)
                    $this->_data[$i] = new IXR_Value($this->_data[$i]);
            }
        }
        public function calculateType(): ?string{
            if ($this->_data === true || $this->_data === false) return 'boolean';
            if (is_int($this->_data)) return 'int';
            if (is_float($this->_data)) return 'double';
            if (is_object($this->_data) && is_a($this->_data, 'IXR_Date')) return 'date';
            if (is_object($this->_data) && is_a($this->_data, 'IXR_Base64')) return 'base64';
            if (is_object($this->_data)) {
                $this->_data = get_object_vars($this->_data);
                return 'structure';
            }
            if (!is_array($this->_data)) return 'string';
            if ($this->__is_structure($this->_data))
                return 'structure';
            else return 'array';
        }
        public function getXml(){
            switch ($this->_type) {
                case 'boolean':
                    return "<boolean>(($this->_data) ? '1' : '0')</boolean>";
                    break;
                case 'int':
                    return "<int>$this->_data</int>";
                    break;
                case 'double':
                    return "<double>$this->_data</double>";
                    break;
                case 'string':
                    return "<string>htmlspecialchars($this->_data)</string>";
                    break;
                case 'array':
                    $return = "<array><data value=''>\n";
                    foreach ($this->_data as $item) {
                        $return .= '  <value>'.$item->$this->getXml()."</value>\n";
                    }
                    $return .= "</data></array>";
                    return $return;
                    break;
                case 'structure':
                    $return = '<struct>'."\n";
                    foreach ($this->_data as $name => $value) {
                        $name = htmlspecialchars($name);
                        $return .= "  <member><name>$name</name><value>";
                        $return .= $value->$this->getXml()."</value></member>\n";
                    }
                    $return .= '</struct>';
                    return $return;
                    break;
                case 'date':
                case 'base64':
                    return $this->_data->$this->getXml();
                    break;
            }
            return false;
        }
        private function __is_structure($array):bool {
            $expected = 0;
            foreach ($array as $key => $value) {
                if ((string)$key !== (string)$expected) return true;
                $expected++;
            }
            return false;
        }













    }
}else{die;}