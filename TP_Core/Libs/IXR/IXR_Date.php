<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:32
 */
namespace TP_Core\Libs\IXR;
if(ABSPATH){
    class IXR_Date{
        protected $_year;
        protected $_month;
        protected $_day;
        protected $_hour;
        protected $_minute;
        protected $_second;
        protected $_timezone;
        public function __construct( $time ){
            if (is_numeric($time)) $this->_parse_timestamp($time);
            else $this->_parse_iso($time);
        }
        protected function _parse_timestamp($timestamp): void{
            $this->_year = gmdate('Y', $timestamp);
            $this->_month = gmdate('m', $timestamp);
            $this->_day = gmdate('d', $timestamp);
            $this->_hour = gmdate('H', $timestamp);
            $this->_minute = gmdate('i', $timestamp);
            $this->_second = gmdate('s', $timestamp);
            $this->_timezone = '';
        }
        protected function _parse_iso($iso): void{
            $this->_year = substr($iso, 0, 4);
            $this->_month = substr($iso, 4, 2);
            $this->_day = substr($iso, 6, 2);
            $this->_hour = substr($iso, 9, 2);
            $this->_minute = substr($iso, 12, 2);
            $this->_second = substr($iso, 15, 2);
            $this->_timezone = substr($iso, 17);
        }
        public function getIso(): string{
            return $this->_year.$this->_month.$this->_day.'T'.$this->_hour.':'.$this->_minute.':'.$this->_second.$this->_timezone;
        }
        public function getXml(): string{
            return "<dateTime.iso8601>{$this->getIso()}</dateTime.iso8601>";
        }
        public function getTimestamp(){
            return mktime($this->_hour, $this->_minute, $this->_second, $this->_month, $this->_day, $this->_year);
        }
    }
}else{die;}