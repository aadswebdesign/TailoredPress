<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 19:13
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface UploadedFileInterface{
        public function getStream();
        public function moveTo($targetPath);
        public function getSize();
        public function getError();
        public function getClientFilename();
        public function getClientMediaType();
    }
}else die;