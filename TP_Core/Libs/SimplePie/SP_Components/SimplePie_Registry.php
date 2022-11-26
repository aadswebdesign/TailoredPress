<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-3-2022
 * Time: 04:26
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Registry{
        use _sp_vars;
        public function __construct()
        {
            $this->_sp_default = [
                'Cache' => 'SimplePie_Cache',
                'Locator' => 'SimplePie_Locator',
                'Parser' => 'SimplePie_Parser',
                'File' => 'SimplePie_File',
                'Sanitize' => 'SimplePie_Sanitize',
                'Item' => 'SimplePie_Item',
                'Author' => 'SimplePie_Author',
                'Category' => 'SimplePie_Category',
                'Enclosure' => 'SimplePie_Enclosure',
                'Caption' => 'SimplePie_Caption',
                'Copyright' => 'SimplePie_Copyright',
                'Credit' => 'SimplePie_Credit',
                'Rating' => 'SimplePie_Rating',
                'Restriction' => 'SimplePie_Restriction',
                'Content_Type_Sniffer' => 'SimplePie_Content_Type_Sniffer',
                'Source' => 'SimplePie_Source',
                //'Misc' => 'SimplePie_Misc',
                'XML_Declaration_Parser' => 'SimplePie_XML_Declaration_Parser',
                'Parse_Date' => 'SimplePie_Parse_Date',
            ];
        }
        public function register($type, $class, $legacy = false):bool {
            if (!@is_subclass_of($class, $this->_sp_default[$type])) return false;
            $this->_sp_classes[$type] = $class;
            if ($legacy) $this->_sp_legacy[] = $class;
            return true;
        }
        public function get_class($type){
            if (!empty($this->_sp_classes[$type])) return $this->_sp_classes[$type];
            if (!empty($this->_sp_default[$type])) return $this->_sp_default[$type];
            return null;
        }
        public function &create($type, $parameters = array()){
            $class = $this->get_class($type);
            if ($type === 'locator' && in_array($class, $this->_sp_legacy, true)) {
                $replacement = array($this->get_class('file'), $parameters[3], $this->get_class('content_type_sniffer'));
                array_splice($parameters, 3, 1, $replacement);
            }
            if (!method_exists($class, '__construct')) $instance = new $class;
            else {
                $reflector = new \ReflectionClass($class);
                $instance = $reflector->newInstanceArgs($parameters);
            }
            if (method_exists($instance, 'set_registry'))
                $instance->set_registry($this);
            return $instance;
        }
        public function &call($type, $method, $parameters = []){
            $class = $this->get_class($type);
            // For backwards compatibility with old non-static
            // Cache::create() methods in PHP < 8.0.
            // No longer supported as of PHP 8.0.
            if ($type === 'Cache' && $method === 'get_handler' && in_array($class, $this->_sp_legacy, true)) {
                $result = @call_user_func_array(array($class, 'create'), $parameters);
                return $result;
            }
            $result = call_user_func_array(array($class, $method), $parameters);
            return $result;
        }
    }
}else die;