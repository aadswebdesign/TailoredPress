<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 13:31
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    abstract class getid3_handler extends getid3_handler_base{
        protected $getid3;
        protected $data_string_flag     = false;
        protected $data_string          = '';
        protected $data_string_position = 0;
        protected $data_string_length   = 0;
        private $dependency_to;
        public function __construct(getID3 $getid3, $call_module=null) {
            parent::__construct();
            $this->getid3 = $getid3;
            if ($call_module) {
                $this->dependency_to = str_replace('getid3_', '', $call_module);
            }
        }
        abstract public function Analyze();
        public function AnalyzeString($string):void {
            // Enter string mode
            $this->setStringMode($string);
            // Save info
            $saved_avdataoffset = $this->getid3->info['avdataoffset'];
            $saved_avdataend    = $this->getid3->info['avdataend'];
            $saved_filesize     = ($this->getid3->info['filesize'] ?? null);
            // Reset some info
            $this->getid3->info['avdataoffset'] = 0;
            $this->getid3->info['avdataend']    = $this->getid3->info['filesize'] = $this->data_string_length;
            // Analyze
            $this->Analyze();
            // Restore some info
            $this->getid3->info['avdataoffset'] = $saved_avdataoffset;
            $this->getid3->info['avdataend']    = $saved_avdataend;
            $this->getid3->info['filesize']     = $saved_filesize;
            // Exit string mode
            $this->data_string_flag = false;
        }
        /**
         * @param string $string
         */
        public function setStringMode($string):void {
            $this->data_string_flag   = true;
            $this->data_string        = $string;
            $this->data_string_length = strlen($string);
        }
        /**
         * @return int|bool
         */
        protected function ftell():bool {
            if ($this->data_string_flag) {
                return $this->data_string_position;
            }
            return ftell($this->getid3->fp);
        }
        protected function fread($bytes):string {
            if ($this->data_string_flag) {
                $this->data_string_position += $bytes;
                return substr($this->data_string, $this->data_string_position - $bytes, $bytes);
            }
            $pos = $this->ftell() + $bytes;
            if (!getid3_lib::intValueSupported($pos)) {
                throw new getid3_exception('cannot fread('.$bytes.' from '.$this->ftell().') because beyond PHP filesystem limit', 10);
            }
            $contents = '';
            do {
                //if (($this->getid3->memory_limit > 0) && ($bytes > $this->getid3->memory_limit)) {
                if (($this->getid3->memory_limit > 0) && (($bytes / $this->getid3->memory_limit) > 0.99)) { // enable a more-fuzzy match to prevent close misses generating errors like "PHP Fatal error: Allowed memory size of 33554432 bytes exhausted (tried to allocate 33554464 bytes)"
                    throw new getid3_exception('cannot fread('.$bytes.' from '.$this->ftell().') that is more than available PHP memory ('.$this->getid3->memory_limit.')', 10);
                }
                $part = fread($this->getid3->fp, $bytes);
                $partLength  = strlen($part);
                $bytes      -= $partLength;
                $contents   .= $part;
            } while (($bytes > 0) && ($partLength > 0));
            return $contents;
        }
        protected function fseek($bytes, $whence=SEEK_SET):int {
            if ($this->data_string_flag) {
                switch ($whence) {
                    case SEEK_SET:
                        $this->data_string_position = $bytes;
                        break;

                    case SEEK_CUR:
                        $this->data_string_position += $bytes;
                        break;

                    case SEEK_END:
                        $this->data_string_position = $this->data_string_length + $bytes;
                        break;
                }
                return 0; // fseek returns 0 on success
            }

            $pos = $bytes;
            if ($whence === SEEK_CUR) {
                $pos = $this->ftell() + $bytes;
            } elseif ($whence === SEEK_END) {
                $pos = $this->getid3->info['filesize'] + $bytes;
            }
            if (!getid3_lib::intValueSupported($pos)) {
                throw new getid3_exception('cannot fseek('.$pos.') because beyond PHP filesystem limit', 10);
            }

            // https://github.com/JamesHeinrich/getID3/issues/327
            $result = fseek($this->getid3->fp, $bytes, $whence);
            if ($result !== 0) { // fseek returns 0 on success
                throw new getid3_exception('cannot fseek('.$pos.'). resource/stream does not appear to support seeking', 10);
            }
            return $result;
        }
       protected function fgets():string {
            $buffer   = ''; // final string we will return
            $prevchar = ''; // save previously-read character for end-of-line checking
            if ($this->data_string_flag) {
                while (true) {
                    $thischar = $this->data_string[$this->data_string_position++];
                    if (($prevchar === "\r") && ($thischar !== "\n")) {
                        // read one byte too many, back up
                        $this->data_string_position--;
                        break;
                    }
                    $buffer .= $thischar;
                    if ($thischar === "\n") {
                        break;
                    }
                    if ($this->data_string_position >= $this->data_string_length) {
                        // EOF
                        break;
                    }
                    $prevchar = $thischar;
                }

            } else {
                while (true) {
                    $thischar = fgetc($this->getid3->fp);
                    if (($prevchar === "\r") && ($thischar !== "\n")) {
                        fseek($this->getid3->fp, -1, SEEK_CUR);
                        break;
                    }
                    $buffer .= $thischar;
                    if ($thischar === "\n") {
                        break;
                    }
                    if (feof($this->getid3->fp)) {
                        break;
                    }
                    $prevchar = $thischar;
                }
            }
            return $buffer;
        }
        /**
         * @return bool
         */
        protected function feof():bool {
            if ($this->data_string_flag) {
                return $this->data_string_position >= $this->data_string_length;
            }
            return feof($this->getid3->fp);
        }
        /**
         * @param string $module
         *
         * @return bool
         */
        final protected function isDependencyFor($module):bool {
            return $this->dependency_to === $module;
        }
        /**
         * @param string $text
         *
         * @return bool
         */
        protected function error($text):bool {
            $this->getid3->info['error'][] = $text;
            return false;
        }
        /**
         * @param string $text
         *
         * @return bool
         */
        protected function warning($text):bool {
            return $this->getid3->warning($text);
        }

        /**
         * @param string $text
         */
        protected function notice($text):void {
            // does nothing for now
        }
        /**
         * @param $name
         * @param $offset
         * @param $length
         * @param null $image_mime
         * @return null|string
         */
        public function saveAttachment($name, $offset, $length, $image_mime=null):string {
            $fp_dest = null;
            $dest = null;
            try {
                // do not extract at all
                if ($this->getid3->option_save_attachments === getID3::ATTACHMENTS_NONE) {
                    $attachment = null; // do not set any
                    // extract to return array
                } elseif ($this->getid3->option_save_attachments === getID3::ATTACHMENTS_INLINE) {
                    $this->fseek($offset);
                    $attachment = $this->fread($length); // get whole data in one pass, till it is anyway stored in memory
                    if ($attachment === false || strlen($attachment) !== $length) {
                        throw new \RuntimeException('failed to read attachment data');
                    }
                } else {
                    $dir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->getid3->option_save_attachments), DIRECTORY_SEPARATOR);
                    if (!is_dir($dir) || !getID3::is_writable($dir)) { // check supplied directory
                        throw new \RuntimeException('supplied path ('.$dir.') does not exist, or is not writable');
                    }
                    $dest = $dir.DIRECTORY_SEPARATOR.$name.($image_mime ? '.'.getid3_lib::ImageExtFromMime($image_mime) : '');
                    if (($fp_dest = fopen($dest, 'wb')) === false) {
                        throw new \RuntimeException('failed to create file '.$dest);
                    }
                    // copy data
                    $this->fseek($offset);
                    $buffersize = ($this->data_string_flag ? $length : $this->getid3->fread_buffer_size());
                    $bytesleft = $length;
                    while ($bytesleft > 0) {
                        if (($buffer = $this->fread(min($buffersize, $bytesleft))) === false || ($byteswritten = fwrite($fp_dest, $buffer)) === false || ($byteswritten === 0)) {
                            throw new \RuntimeException($buffer === false ? 'not enough data to read' : 'failed to write to destination file, may be not enough disk space');
                        }
                        $bytesleft -= $byteswritten;
                    }
                    fclose($fp_dest);
                    $attachment = $dest;
                }
            } catch (\Exception $e) {
                // close and remove dest file if created
                if (isset($fp_dest) && is_resource($fp_dest)) {
                    fclose($fp_dest);
                }
                if (isset($dest) && file_exists($dest)) {
                    unlink($dest);
                }
                $attachment = null;
                $this->warning('Failed to extract attachment '.$name.': '.$e->getMessage());
            }
            // seek to the end of attachment
            $this->fseek($offset + $length);
            return $attachment;
        }
    }
}else{die;}
