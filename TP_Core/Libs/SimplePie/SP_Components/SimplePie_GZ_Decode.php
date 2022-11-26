<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-3-2022
 * Time: 19:37
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_GZ_Decode{
        use _sp_vars;
        public $M_TIME;
        public $E_F;
        public $O_S;
        public $Sub_field_ID_1;
        public $Sub_field_ID_2;
        public function __set($name, $value){
            trigger_error("Cannot write property $name", E_USER_ERROR);
        }
        public function __construct($data){
            $this->__sp_compressed_data = $data;
            $this->__sp_compressed_size = strlen($data);
        }
        public function parse():bool{
            $len = null;
            if ($this->__sp_compressed_size >= $this->__sp_min_compressed_size){
                // Check ID1, ID2, and CM
                if (strpos($this->__sp_compressed_data, "\x1F\x8B\x08") !== 0)
                    return false;
                // Get the FLG (FLaGs)
                $this->__sp_flags = ord($this->__sp_compressed_data[3]);
                // FLG bits above (1 << 4) are reserved
                if ($this->__sp_flags > 0x1F) return false;
                // Advance the pointer after the above
                $this->__sp_position += 4;
                // M_TIME
                $m_time = substr($this->__sp_compressed_data, $this->__sp_position, 4);
                // Reverse the string if we're on a big-endian arch because l is the only signed long and is machine endianness
                if (current(unpack('S', "\x00\x01")) === 1) $m_time = strrev($m_time);
                $this->M_TIME = current(unpack('l', $m_time));
                $this->__sp_position += 4;
                // Get the XFL (eXtra FLags)
                $this->E_F = ord($this->__sp_compressed_data[$this->__sp_position++]);
                // Get the OS (Operating System)
                $this->O_S = ord($this->__sp_compressed_data[$this->__sp_position++]);
                // Parse the FEXTRA
                if ($this->__sp_flags & 4){
                    $this->Sub_field_ID_1 = $this->__sp_compressed_data[$this->__sp_position++];
                    $this->Sub_field_ID_2 = $this->__sp_compressed_data[$this->__sp_position++];
                    // SI2 set to zero is reserved for future use
                    if ($this->Sub_field_ID_2 === "\x00") return false;
                    // Get the length of the extra field
                    $len = current(unpack('v', substr($this->__sp_compressed_data, $this->__sp_position, 2)));
                    $this->__sp_position += 2;
                    // Check the length of the string is still valid
                    $this->__sp_min_compressed_size += $len + 4;
                    if ($this->__sp_compressed_size >= $this->__sp_min_compressed_size){
                        // Set the extra field to the given data
                        $this->sp_extra_field = substr($this->__sp_compressed_data, $this->__sp_position, $len);
                        $this->__sp_position += $len;
                    }
                    else return false;
                }
                if ($this->__sp_flags & 8){
                    // Get the length of the filename
                    $len = strcspn($this->__sp_compressed_data, "\x00", $this->__sp_position);
                    // Check the length of the string is still valid
                    $this->__sp_min_compressed_size += $len + 1;
                    if ($this->__sp_compressed_size >= $this->__sp_min_compressed_size){
                        // Set the original filename to the given string
                        $this->sp_filename = substr($this->__sp_compressed_data, $this->__sp_position, $len);
                        $this->__sp_position += $len + 1;
                    } else return false;
                }
                if ($this->__sp_flags & 16){
                    // Get the length of the comment
                    $len = strcspn($this->__sp_compressed_data, "\x00", $this->__sp_position);
                    // Check the length of the string is still valid
                    $this->__sp_min_compressed_size += $len + 1;
                    if ($this->__sp_compressed_size >= $this->__sp_min_compressed_size){
                        // Set the original comment to the given string
                        $this->sp_comment = substr($this->__sp_compressed_data, $this->__sp_position, $len);
                        $this->__sp_position += $len + 1;
                    }else return false;
                }
                // Parse the FHCRC
                if ($this->__sp_flags & 2){
                    // Check the length of the string is still valid
                    $this->__sp_min_compressed_size += $len + 2;
                    if ($this->__sp_compressed_size >= $this->__sp_min_compressed_size){
                        // Read the CRC
                        $crc = current(unpack('v', substr($this->__sp_compressed_data, $this->__sp_position, 2)));
                        // Check the CRC matches
                        if ((crc32(substr($this->__sp_compressed_data, 0, $this->__sp_position)) & 0xFFFF) === $crc)
                            $this->__sp_position += 2;
                        else return false;
                    }
                    else return false;
                }
                // Decompress the actual data
                if (($this->sp_data = gzinflate(substr($this->__sp_compressed_data, $this->__sp_position, -8))) === false)
                    return false;
                $this->__sp_position = $this->__sp_compressed_size - 8;
                // Check CRC of data left out
                // Check I_SIZE of data
                $i_size = current(unpack('V', substr($this->__sp_compressed_data, $this->__sp_position, 4)));
                $this->__sp_position += 4;
                if (sprintf('%u', strlen($this->sp_data) & 0xFFFFFFFF) !== sprintf('%u', $i_size))
                    return false;
                return true;
            }
            return false;
        }
    }
}else die;