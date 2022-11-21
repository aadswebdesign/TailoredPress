<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 22:00
 */
namespace TP_Core\Libs\ID3\Modules;
use TP_Core\Libs\ID3\getid3_lib;
use TP_Core\Libs\ID3\getid3_handler;
use TP_Core\Libs\ID3\AMFReader;
use TP_Core\Libs\ID3\AVCSequenceParameterSetReader;
use TP_Core\Libs\ID3\AMFStream;
if(ABSPATH){
    class getid3_flv extends getid3_handler{
        public $flv_framecount = [];
        public function Analyze():bool {
            $info = &$this->getid3->info;
            $this->fseek($info['avdataoffset']);
            //$FLVdataLength = $info['avdataend'] - $info['avdataoffset'];
            $FLVheader = $this->fread(5);
            $info['fileformat'] = 'flv';
            $info['flv']['header']['signature'] = substr($FLVheader, 0, 3);
            $info['flv']['header']['version']   = getid3_lib::BigEndian2Int($FLVheader[3]);
            $TypeFlags                          = getid3_lib::BigEndian2Int($FLVheader[4]);
            if ($info['flv']['header']['signature'] !== self::MAGIC) {
                $this->error('Expecting "'.getid3_lib::PrintHexBytes(self::MAGIC).'" at offset '.$info['avdataoffset'].', found "'.getid3_lib::PrintHexBytes($info['flv']['header']['signature']).'"');
                unset($info['flv'], $info['fileformat']);
                return false;
            }
            $info['flv']['header']['hasAudio'] = (bool) ($TypeFlags & 0x04);
            $info['flv']['header']['hasVideo'] = (bool) ($TypeFlags & 0x01);
            $FrameSizeDataLength = getid3_lib::BigEndian2Int($this->fread(4));
            $FLVheaderFrameLength = 9;
            if ($FrameSizeDataLength > $FLVheaderFrameLength) {
                $this->fseek($FrameSizeDataLength - $FLVheaderFrameLength, SEEK_CUR);
            }
            $Duration = 0;
            $found_video = false;
            $found_audio = false;
            $found_meta  = false;
            $found_valid_meta_playtime = false;
            $tagParseCount = 0;
            $info['flv']['framecount'] = array('total'=>0, 'audio'=>0, 'video'=>0);
            $this->flv_framecount = &$info['flv']['framecount'];
            while ((($this->ftell() + 16) < $info['avdataend']) && (($tagParseCount++ <= $this->max_frames) || !$found_valid_meta_playtime))  {
                $ThisTagHeader = $this->fread(16);
                //$PreviousTagLength = getid3_lib::BigEndian2Int(substr($ThisTagHeader,  0, 4));
                $TagType           = getid3_lib::BigEndian2Int($ThisTagHeader[4]);
                $DataLength        = getid3_lib::BigEndian2Int(substr($ThisTagHeader,  5, 3));
                $Timestamp         = getid3_lib::BigEndian2Int(substr($ThisTagHeader,  8, 3));
                $LastHeaderByte    = getid3_lib::BigEndian2Int($ThisTagHeader[15]);
                $NextOffset = $this->ftell() - 1 + $DataLength;
                if ($Timestamp > $Duration) {
                    $Duration = $Timestamp;
                }
                $this->flv_framecount['total']++;
                switch ($TagType) {
                    case GETID3_FLV_TAG_AUDIO:
                        $this->flv_framecount['audio']++;
                        if (!$found_audio) {
                            $found_audio = true;
                            $info['flv']['audio']['audioFormat'] = ($LastHeaderByte >> 4) & 0x0F;
                            $info['flv']['audio']['audioRate'] = ($LastHeaderByte >> 2) & 0x03;
                            $info['flv']['audio']['audioSampleSize'] = ($LastHeaderByte >> 1) & 0x01;
                            $info['flv']['audio']['audioType'] =  $LastHeaderByte       & 0x01;
                        }
                        break;
                    case GETID3_FLV_TAG_VIDEO:
                        $this->flv_framecount['video']++;
                        if (!$found_video) {
                            $found_video = true;
                            $info['flv']['video']['videoCodec'] = $LastHeaderByte & 0x07;
                            $FLVvideoHeader = $this->fread(11);
                            $PictureSizeEnc = array();
                            if ($info['flv']['video']['videoCodec'] === GETID3_FLV_VIDEO_H264) {
                                $AVCPacketType = getid3_lib::BigEndian2Int($FLVvideoHeader[0]);
                                if ($AVCPacketType === H264_AVC_SEQUENCE_HEADER) {
                                    //todo
                                    //$configurationVersion       = getid3_lib::BigEndian2Int($FLVvideoHeader[4]);
                                    //$AVCProfileIndication       = getid3_lib::BigEndian2Int($FLVvideoHeader[5]);
                                    //$profile_compatibility      = getid3_lib::BigEndian2Int($FLVvideoHeader[6]);
                                    //$lengthSizeMinusOne         = getid3_lib::BigEndian2Int($FLVvideoHeader[7]);
                                    $numOfSequenceParameterSets = getid3_lib::BigEndian2Int($FLVvideoHeader[8]);
                                    if (($numOfSequenceParameterSets & 0x1F) !== 0) {
                                        $spsSize = getid3_lib::LittleEndian2Int(substr($FLVvideoHeader, 9, 2));
                                        $sps = $this->fread($spsSize);
                                        if (strlen($sps) === $spsSize) {	//	make sure that whole SequenceParameterSet was red
                                            $spsReader = new AVCSequenceParameterSetReader($sps);
                                            $spsReader->readData();
                                            $info['video']['resolution_x'] = $spsReader->getWidth();
                                            $info['video']['resolution_y'] = $spsReader->getHeight();
                                        }
                                    }
                                }
                            } elseif ($info['flv']['video']['videoCodec'] === GETID3_FLV_VIDEO_H263) {
                                $PictureSizeType = (getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 3, 2))) >> 7;
                                $PictureSizeType &= 0x0007;
                                $info['flv']['header']['videoSizeType'] = $PictureSizeType;
                                switch ($PictureSizeType) {
                                    case 0:
                                        $PictureSizeEnc['x'] = getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 4, 2)) >> 7;
                                        $PictureSizeEnc['y'] = getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 5, 2)) >> 7;
                                        $info['video']['resolution_x'] = $PictureSizeEnc['x'] & 0xFF;
                                        $info['video']['resolution_y'] = $PictureSizeEnc['y'] & 0xFF;
                                        break;
                                    case 1:
                                        $PictureSizeEnc['x'] = getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 4, 3)) >> 7;
                                        $PictureSizeEnc['y'] = getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 6, 3)) >> 7;
                                        $info['video']['resolution_x'] = $PictureSizeEnc['x'] & 0xFFFF;
                                        $info['video']['resolution_y'] = $PictureSizeEnc['y'] & 0xFFFF;
                                        break;

                                    case 2:
                                        $info['video']['resolution_x'] = 352;
                                        $info['video']['resolution_y'] = 288;
                                        break;
                                    case 3:
                                        $info['video']['resolution_x'] = 176;
                                        $info['video']['resolution_y'] = 144;
                                        break;
                                    case 4:
                                        $info['video']['resolution_x'] = 128;
                                        $info['video']['resolution_y'] = 96;
                                        break;
                                    case 5:
                                        $info['video']['resolution_x'] = 320;
                                        $info['video']['resolution_y'] = 240;
                                        break;
                                    case 6:
                                        $info['video']['resolution_x'] = 160;
                                        $info['video']['resolution_y'] = 120;
                                        break;
                                    default:
                                        $info['video']['resolution_x'] = 0;
                                        $info['video']['resolution_y'] = 0;
                                        break;
                                }
                            } elseif ($info['flv']['video']['videoCodec'] ===  GETID3_FLV_VIDEO_VP6FLV_ALPHA) {
                                /* contributed by schouwerwouØgmail*com */
                                if (!isset($info['video']['resolution_x'])) { // only when meta data isn't set
                                    $PictureSizeEnc['x'] = getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 6, 2));
                                    $PictureSizeEnc['y'] = getid3_lib::BigEndian2Int(substr($FLVvideoHeader, 7, 2));
                                    $info['video']['resolution_x'] = ($PictureSizeEnc['x'] & 0xFF) << 3;
                                    $info['video']['resolution_y'] = ($PictureSizeEnc['y'] & 0xFF) << 3;
                                }
                            }
                            if (!empty($info['video']['resolution_x']) && !empty($info['video']['resolution_y'])) {
                                $info['video']['pixel_aspect_ratio'] = $info['video']['resolution_x'] / $info['video']['resolution_y'];
                            }
                        }
                        break;
                    case GETID3_FLV_TAG_META:
                        if (!$found_meta) {
                            $found_meta = true;
                            $this->fseek(-1, SEEK_CUR);
                            $datachunk = $this->fread($DataLength);
                            $AMFstream = new AMFStream($datachunk);
                            $reader = new AMFReader($AMFstream);
                            $eventName = $reader->readData();
                            $info['flv']['meta'][$eventName] = $reader->readData();
                            unset($reader);
                            $copykeys = array('framerate'=>'frame_rate', 'width'=>'resolution_x', 'height'=>'resolution_y', 'audiodatarate'=>'bitrate', 'videodatarate'=>'bitrate');
                            foreach ($copykeys as $sourcekey => $destkey) {
                                if (isset($info['flv']['meta']['onMetaData'][$sourcekey])) {
                                    switch ($sourcekey) {
                                        case 'width':
                                        case 'height':
                                            $info['video'][$destkey] = (int)round($info['flv']['meta']['onMetaData'][$sourcekey]);
                                            break;
                                        case 'audiodatarate':
                                            $info['audio'][$destkey] = getid3_lib::CastAsInt(round($info['flv']['meta']['onMetaData'][$sourcekey] * 1000));
                                            break;
                                        case 'videodatarate':
                                        case 'frame_rate':
                                        default:
                                            $info['video'][$destkey] = $info['flv']['meta']['onMetaData'][$sourcekey];
                                            break;
                                    }
                                }
                            }
                            if (!empty($info['flv']['meta']['onMetaData']['duration'])) {
                                $found_valid_meta_playtime = true;
                            }
                        }
                        break;
                    default:
                        // noop
                        break;
                }
                $this->fseek($NextOffset);
            }
            $info['playtime_seconds'] = $Duration / 1000;
            if ($info['playtime_seconds'] > 0) {
                $info['bitrate'] = (($info['avdataend'] - $info['avdataoffset']) * 8) / $info['playtime_seconds'];
            }
            if ($info['flv']['header']['hasAudio']) {
                $info['audio']['codec']           =   self::audioFormatLookup($info['flv']['audio']['audioFormat']);
                $info['audio']['sample_rate']     =     self::audioRateLookup($info['flv']['audio']['audioRate']);
                $info['audio']['bits_per_sample'] = self::audioBitDepthLookup($info['flv']['audio']['audioSampleSize']);
                $info['audio']['channels']   =  $info['flv']['audio']['audioType'] + 1; // 0=mono,1=stereo
                $info['audio']['lossless']   = ($info['flv']['audio']['audioFormat'] ? false : true); // 0=uncompressed
                $info['audio']['dataformat'] = 'flv';
            }
            if (!empty($info['flv']['header']['hasVideo'])) {
                $info['video']['codec']      = self::videoCodecLookup($info['flv']['video']['videoCodec']);
                $info['video']['dataformat'] = 'flv';
                $info['video']['lossless']   = false;
            }
            if (!empty($info['flv']['meta']['onMetaData']['duration'])) {
                $info['playtime_seconds'] = $info['flv']['meta']['onMetaData']['duration'];
                $info['bitrate'] = (($info['avdataend'] - $info['avdataoffset']) * 8) / $info['playtime_seconds'];
            }
            if (isset($info['flv']['meta']['onMetaData']['audiocodecid'])) {
                $info['audio']['codec'] = self::audioFormatLookup($info['flv']['meta']['onMetaData']['audiocodecid']);
            }
            if (isset($info['flv']['meta']['onMetaData']['videocodecid'])) {
                $info['video']['codec'] = self::videoCodecLookup($info['flv']['meta']['onMetaData']['videocodecid']);
            }
            return true;
        }//96
        /**
         * @param int $id
         *
         * @return string|false
         */
        public static function audioFormatLookup($id) {
            static $lookup = array(
                0  => 'Linear PCM, platform endian',
                1  => 'ADPCM',
                2  => 'mp3',
                3  => 'Linear PCM, little endian',
                4  => 'Nellymoser 16kHz mono',
                5  => 'Nellymoser 8kHz mono',
                6  => 'Nellymoser',
                7  => 'G.711A-law logarithmic PCM',
                8  => 'G.711 mu-law logarithmic PCM',
                9  => 'reserved',
                10 => 'AAC',
                11 => 'Speex',
                12 => false, // unknown?
                13 => false, // unknown?
                14 => 'mp3 8kHz',
                15 => 'Device-specific sound',
            );
            return ($lookup[$id] ?? false);
        }//355
        public static function audioRateLookup($id) {
            static $lookup = array(
                0 =>  5500,
                1 => 11025,
                2 => 22050,
                3 => 44100,
            );
            return ($lookup[$id] ?? false);
        }//382
        /**
         * @param int $id
         *
         * @return int|false
         */
        public static function audioBitDepthLookup($id) {
            static $lookup = array(
                0 =>  8,
                1 => 16,
            );
            return ($lookup[$id] ?? false);
        }//397
        /**
         * @param int $id
         *
         * @return string|false
         */
        public static function videoCodecLookup($id) {
            static $lookup = array(
                GETID3_FLV_VIDEO_H263         => 'Sorenson H.263',
                GETID3_FLV_VIDEO_SCREEN       => 'Screen video',
                GETID3_FLV_VIDEO_VP6FLV       => 'On2 VP6',
                GETID3_FLV_VIDEO_VP6FLV_ALPHA => 'On2 VP6 with alpha channel',
                GETID3_FLV_VIDEO_SCREENV2     => 'Screen video v2',
                GETID3_FLV_VIDEO_H264         => 'Sorenson H.264',
            );
            return ($lookup[$id] ?? false);
        }//410
    }
}else{die;}