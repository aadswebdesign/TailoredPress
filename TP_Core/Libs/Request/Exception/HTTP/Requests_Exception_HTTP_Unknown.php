<?php
/**
 * Exception for unknown status responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
use TP_Core\Libs\Request\Requests_Response;
if(ABSPATH){

}else die;
class Requests_Exception_HTTP_Unknown extends Requests_Exception_HTTP {
	protected $_code = 0;
	protected $_reason = 'Unknown';
	public function __construct($reason = null, $data = null) {
		if ($data instanceof Requests_Response)
			$this->_code = $data->status_code;
		parent::__construct($reason, $data);
	}
}