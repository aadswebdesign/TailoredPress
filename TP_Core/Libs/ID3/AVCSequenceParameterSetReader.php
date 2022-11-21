<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 22:21
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class AVCSequenceParameterSetReader{
        public $sps;
        public $start = 0;
        public $currentBytes = 0;
        public $currentBits = 0;
        public $width;
        public $height;
        public function __construct($sps) {
            $this->sps = $sps;
        }//780
        public function readData():void {
            $this->skipBits(8);
            $this->skipBits(8);
            $profile = $this->getBits(8);
            if ($profile > 0) {
                $this->skipBits(8);
                //$level_idc = $this->getBits(8); not used here anywhere
                $this->expGolombUe();
                $this->expGolombUe();
                $picOrderType = $this->expGolombUe();
                if ($picOrderType === 0) {
                    $this->expGolombUe();
                } elseif ($picOrderType === 1) {
                    $this->skipBits(1);
                    $this->expGolombSe();
                    $this->expGolombSe();
                    $num_ref_frames_in_pic_order_cnt_cycle = $this->expGolombUe();
                    for ($i = 0; $i < $num_ref_frames_in_pic_order_cnt_cycle; $i++) {
                        $this->expGolombSe();
                    }
                }
                $this->expGolombUe();
                $this->skipBits(1);
                $pic_width_in_mbs_minus1 = $this->expGolombUe();
                $pic_height_in_map_units_minus1 = $this->expGolombUe();
                $frame_mbs_only_flag = $this->getBits(1);
                if ($frame_mbs_only_flag === 0) {
                    $this->skipBits(1);
                }
                $this->skipBits(1);
                $frame_cropping_flag = $this->getBits(1);
                $frame_crop_left_offset   = 0;
                $frame_crop_right_offset  = 0;
                $frame_crop_top_offset    = 0;
                $frame_crop_bottom_offset = 0;
                if ($frame_cropping_flag) {
                    $frame_crop_left_offset   = $this->expGolombUe();
                    $frame_crop_right_offset  = $this->expGolombUe();
                    $frame_crop_top_offset    = $this->expGolombUe();
                    $frame_crop_bottom_offset = $this->expGolombUe();
                }
                $this->skipBits(1);
                $this->width  = (($pic_width_in_mbs_minus1 + 1) * 16) - ($frame_crop_left_offset * 2) - ($frame_crop_right_offset * 2);
                $this->height = ((2 - $frame_mbs_only_flag) * ($pic_height_in_map_units_minus1 + 1) * 16) - ($frame_crop_top_offset * 2) - ($frame_crop_bottom_offset * 2);
            }
        }//784
        public function skipBits($bits):void {
            $newBits = $this->currentBits + $bits;
            $this->currentBytes += (int)floor($newBits / 8);
            $this->currentBits = $newBits % 8;
        }//839
        public function getBit():int {
            $result = (getid3_lib::BigEndian2Int($this->sps[$this->currentBytes]) >> (7 - $this->currentBits)) & 0x01;
            $this->skipBits(1);
            return $result;
        }//848
        public function getBits($bits):int {
            $result = 0;
            for ($i = 0; $i < $bits; $i++) {
                $result = ($result << 1) + $this->getBit();
            }
            return $result;
        }//859
        public function expGolombUe():int {
            $significantBits = 0;
            $bit = $this->getBit();
            while ($bit === 0) {
                $significantBits++;
                $bit = $this->getBit();
                if ($significantBits > 31) {
                    return 0;
                }
            }
            return (1 << $significantBits) + $this->getBits($significantBits) - 1;
        }//870
        public function expGolombSe():int {
            $result = $this->expGolombUe();
            if (($result & 0x01) === 0) {
                return -($result >> 1);
            }
            return ($result + 1) >> 1;
        }//888
        public function getWidth():int{
            return $this->width;
        }//900
        public function getHeight():int{
            return $this->height;
        }//907
    }
}else{die;}