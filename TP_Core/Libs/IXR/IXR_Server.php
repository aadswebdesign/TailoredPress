<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 13:52
 */
namespace TP_Core\Libs\IXR;
if(ABSPATH){
    class IXR_Server{
        public $data;
        public $callbacks = [];
        public $message;
        public $capabilities;
        public function __construct( $callbacks = false, $data = false, $wait = false ){
            $this->setCapabilities();
            if ($callbacks)  $this->callbacks = $callbacks;
            $this->setCallbacks();
            if (!$wait) $this->serve($data);
        }
        public function serve($data = null, $data_status = false): void{
            if (!$data) {
                if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] !== 'POST')) {
                    if ( function_exists( 'status_header' ) ) {
                        status_header( METHOD_NOT_ALLOWED ); // WP #20986
                        header( 'Allow: POST' );
                    }
                    header('Content-Type: text/plain'); // merged from WP #9093
                    die('XML-RPC server accepts POST requests only.');
                }
                if($data_status === true){$data = file_get_contents('php://input');}

            }
            $this->message = new IXR_Message($data);
            if (!$this->message->parse_messages()) $this->error(-32700, 'parse error. not well formed');
            if ($this->message->message_type !== 'methodCall')
                $this->error(-32600, 'server error. invalid xml-rpc. not conforming to spec. Request must be a methodCall');
            $result = $this->call($this->message->method_name, $this->message->params);
            if (is_a($result, 'IXR_Error')) $this->error($result);
            $r = new IXR_Value($result);
            $resultxml = $r->getXml();
            $xml = <<<EOD
<methodResponse>
  <params>
    <param>
      <value>
      $resultxml
      </value>
    </param>
  </params>
</methodResponse>
EOD;
            $this->output($xml);
        }//38
        public function call($methodname, $args){
            if (!$this->hasMethod($methodname))
                return new IXR_Error(-32601, 'server error. requested method '.$methodname.' does not exist.');
            $method = $this->callbacks[$methodname];
            if (count($args) === 1) $args = $args[0];
            if (is_string($method) && strpos($method, 'this:') === 0) {
                $method = substr($method, 5);
                if (!method_exists($this, $method))
                    return new IXR_Error(-32601, 'server error. requested class method "'.$method.'" does not exist.');
                $result = $this->$method($args);
            } else {
                if (is_array($method)) {
                    if (!is_callable(array($method[0], $method[1])))
                        return new IXR_Error(-32601, 'server error. requested object method "'.$method[1].'" does not exist.');
                } else if (!function_exists($method))
                    return new IXR_Error(-32601, 'server error. requested function "'.$method.'" does not exist.');
                $result = $method($args);
            }
            return $result;
        }//87
        public function error($error, $message = false): void{
            if ($message && !is_object($error))
                $error = new IXR_Error($error, $message);
            $this->output($error->getXml());
        }//126
        public function output($xml): void{
            $charset = function_exists('get_option') ? get_option('blog_charset') : '';
            if ($charset) $xml = '<?xml version="1.0" encoding="'.$charset.'"?>'."\n".$xml;
            else $xml = '<?xml version="1.0"?>'."\n".$xml;
            $length = strlen($xml);
            header('Connection: close');
            if ($charset) header('Content-Type: text/xml; charset='.$charset);
            else header('Content-Type: text/xml');
            header('Date: '.gmdate('r'));
            if(0 !== $length)
                echo $xml;
            exit;
        }//136
        public function hasMethod($method): bool{
            return array_key_exists($method, $this->callbacks);
        }//154
        public function setCapabilities(): void{
            $this->capabilities = array(
                'xmlrpc' => array(
                    'specUrl' => 'http://www.xmlrpc.com/spec',
                    'specVersion' => 1
                ),
                'faults_interop' => array(
                    'specUrl' => 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php',
                    'specVersion' => 20010516
                ),
                'system.multicall' => array(
                    'specUrl' => 'http://www.xmlrpc.com/discuss/msgReader$1208',
                    'specVersion' => 1
                ),
            );
        }//159
        public function getCapabilities($args){
            return $this->capabilities[$args];
        }//178
        public function setCallbacks(): void{
            $this->callbacks['system.getCapabilities'] = 'this:getCapabilities';
            $this->callbacks['system.listMethods'] = 'this:listMethods';
            $this->callbacks['system.multicall'] = 'this:multiCall';
        }//183
        public function listMethods($args): array{
            return array_reverse(array_keys($this->callbacks[$args]));
        }
        public function multiCall(array $methodcalls): array{
            $return = [];
            foreach ($methodcalls as $call) {
                $method = $call['methodName'];
                $params = $call['params'];
                if ($method === 'system.multicall') {
                    $result = new IXR_Error(-32600, 'Recursive calls to system.multicall are forbidden');
                } else $result = $this->call($method, $params);
                if (is_a($result, 'IXR_Error')) {
                    $return[] = array(
                        'faultCode' => $result->code,
                        'faultString' => $result->message
                    );
                } else  $return[] = array($result);
            }
            return $return;
        }//197
    }
}else{die;}