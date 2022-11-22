<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 21:51
 */
namespace TP_Core\Libs\PHP\Libs\HTTP_Message;
if(ABSPATH){
    interface UploadedFileFactoryInterface{
        public function createUploadedFile( StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface;
    }
}else die;