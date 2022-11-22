<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 04:47
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\StreamInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UploadedFileInterface;
if(ABSPATH){
    class UploadedFile implements UploadedFileInterface{
        public const ERRORS = [
            UPLOAD_ERR_OK,
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION,
        ];
        private $__clientFilename;
        private $__clientMediaType;
        private $__error;
        private $__file;
        private $__moved = false;
        private $__size;
        private $__stream;
        public function __construct($streamOrFile,?int $size,int $errorStatus,string $clientFilename = null,string $clientMediaType = null){
            $this->__setError($errorStatus);
            $this->__size = $size;
            $this->__clientFilename = $clientFilename;
            $this->__clientMediaType = $clientMediaType;
            if ($this->__isOk())
                $this->__setStreamOrFile($streamOrFile);
        }
        public function isMoved(): bool{
            return $this->__moved;
        }
        public function getStream(): StreamInterface{
            $this->__validateActive();
            $file = $this->__file;
            $_open_stream =new LazyOpenStream($file, 'r+');
            $open_stream = '';
            if ($_open_stream instanceof StreamInterface){
                $open_stream = $_open_stream;
            }
            return $open_stream;
        }
        public function moveTo($targetPath): void{
            $this->__validateActive();
            if (false === $this->__isStringNotEmpty($targetPath))
                throw new \InvalidArgumentException(
                    'Invalid path provided for move operation; must be a non-empty string'
                );
            if ($this->__file)
                $this->__moved = PHP_SAPI === 'cli'
                    ? rename($this->__file, $targetPath)
                    : move_uploaded_file($this->__file, $targetPath);
            else {
                $_open_stream = new LazyOpenStream($targetPath, 'w');
                $open_stream = '';
                if ($_open_stream instanceof StreamInterface){
                    $open_stream = $_open_stream;
                }
                Utils::copyToStream($this->getStream(),$open_stream);
                $this->__moved = true;
            }
            if (false === $this->__moved)
                throw new \RuntimeException(
                    sprintf('Uploaded file could not be moved to %s', $targetPath)
                );
            return null;
        }
        public function getSize(): ?int{
            return $this->__size;
        }
        public function getError(): int{
            return $this->__error;
        }
        public function getClientFilename(): ?string{
            return $this->__clientFilename;
        }
        public function getClientMediaType(): ?string{
            return $this->__clientMediaType;
        }
        private function __setStreamOrFile($streamOrFile): void{
            if (is_string($streamOrFile)) $this->__file = $streamOrFile;
            elseif (is_resource($streamOrFile))
                $this->__stream = new Stream($streamOrFile);
            elseif ($streamOrFile instanceof StreamInterface)
                $this->__stream = $streamOrFile;
            else {
                throw new \InvalidArgumentException(
                    'Invalid stream or file provided for UploadedFile'
                );
            }
            return null;
        }
        private function __setError(int $error): void{
            if (false === in_array($error, self::ERRORS, true))
                throw new \InvalidArgumentException(
                    'Invalid error status for UploadedFile'
                );
            $this->__error = $error;
            return null;
        }
        private function __isStringNotEmpty($param): bool{
            return is_string($param) && false === empty($param);
        }
        private function __isOk(): bool {
            return $this->__error === UPLOAD_ERR_OK;
        }
        private function __validateActive(): void{
            if (false === $this->__isOk())
                throw new \RuntimeException('Cannot retrieve stream due to upload error');
            if ($this->isMoved())
                throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
            return null;
        }
    }
}else die;