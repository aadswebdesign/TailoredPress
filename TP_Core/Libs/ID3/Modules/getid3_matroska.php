<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 23:24
 */
namespace TP_Core\Libs\ID3\Modules;
use TP_Core\Libs\ID3\getID3;
use TP_Core\Libs\ID3\getid3_lib;
use TP_Core\Libs\ID3\getid3_handler;
use TP_Core\Libs\ID3\getid3_exception;
if(ABSPATH){
    class getid3_matroska extends getid3_handler{
        public $hide_clusters    = true;
        public $parse_whole_file = false;
        private $EBMLbuffer        = '';
        private $EBMLbuffer_offset = 0;
        private $EBMLbuffer_length = 0;
        private $current_offset    = 0;
        private $unuseful_elements = array(EBML_ID_CRC32, EBML_ID_VOID);
        public function Analyze():bool{
            $info = &$this->getid3->info;
            // parse container
            try {
                $this->parseEBML($info);
            } catch (\Exception $e) {
                $this->error('EBML parser: '.$e->getMessage());
            }
            // calculate playtime
            if (isset($info['matroska']['info']) && is_array($info['matroska']['info'])) {
                foreach ($info['matroska']['info'] as $key => $infoarray) {
                    if (isset($infoarray['Duration'])) {
                        // TimecodeScale is how many nanoseconds each Duration unit is
                        $info['playtime_seconds'] = $infoarray['Duration'] * (($infoarray['TimecodeScale'] ?? 1000000) / 1000000000);
                        break;
                    }
                }
            }
            // extract tags
            if (isset($info['matroska']['tags']) && is_array($info['matroska']['tags'])) {
                foreach ($info['matroska']['tags'] as $key => $infoarray) {
                    $this->ExtractCommentsSimpleTag($infoarray);
                }
            }
            // process tracks
            if (isset($info['matroska']['tracks']['tracks']) && is_array($info['matroska']['tracks']['tracks'])) {
                foreach ($info['matroska']['tracks']['tracks'] as $key => $trackarray) {
                    $track_info = array();
                    $track_info['dataformat'] = self::CodecIDtoCommonName($trackarray['CodecID']);
                    $track_info['default'] = ($trackarray['FlagDefault'] ?? true);
                    if (isset($trackarray['Name'])) { $track_info['name'] = $trackarray['Name']; }
                    switch ($trackarray['TrackType']) {
                        case 1: // Video
                            $track_info['resolution_x'] = $trackarray['PixelWidth'];
                            $track_info['resolution_y'] = $trackarray['PixelHeight'];
                            $track_info['display_unit'] = self::displayUnit($trackarray['DisplayUnit'] ?? 0);
                            $track_info['display_x']    = ($trackarray['DisplayWidth'] ?? $trackarray['PixelWidth']);
                            $track_info['display_y']    = ($trackarray['DisplayHeight'] ?? $trackarray['PixelHeight']);
                            if (isset($trackarray['PixelCropBottom'])) { $track_info['crop_bottom'] = $trackarray['PixelCropBottom']; }
                            if (isset($trackarray['PixelCropTop']))    { $track_info['crop_top']    = $trackarray['PixelCropTop']; }
                            if (isset($trackarray['PixelCropLeft']))   { $track_info['crop_left']   = $trackarray['PixelCropLeft']; }
                            if (isset($trackarray['PixelCropRight']))  { $track_info['crop_right']  = $trackarray['PixelCropRight']; }
                            if (isset($trackarray['DefaultDuration'])) { $track_info['frame_rate']  = round(1000000000 / $trackarray['DefaultDuration'], 3); }
                            if (isset($trackarray['CodecName']))       { $track_info['codec']       = $trackarray['CodecName']; }
                            if ($trackarray['CodecID'] === 'V_MS/VFW/FOURCC') {
                                $parsed = getid3_riff::ParseBITMAPINFOHEADER($trackarray['CodecPrivate']);
                                $track_info['codec'] = getid3_riff::fourccLookup($parsed['fourcc']);
                                $info['matroska']['track_codec_parsed'][$trackarray['TrackNumber']] = $parsed;
                            }
                            $info['video']['streams'][$trackarray['TrackUID']] = $track_info;
                            break;
                        case 2: // Audio
                            $track_info['sample_rate'] = ($trackarray['SamplingFrequency'] ?? 8000.0);
                            $track_info['channels']    = ($trackarray['Channels'] ?? 1);
                            $track_info['language']    = ($trackarray['Language'] ?? 'eng');
                            if (isset($trackarray['BitDepth']))  { $track_info['bits_per_sample'] = $trackarray['BitDepth']; }
                            if (isset($trackarray['CodecName'])) { $track_info['codec']           = $trackarray['CodecName']; }
                            switch ($trackarray['CodecID']) {
                                case 'A_PCM/INT/LIT':
                                case 'A_PCM/INT/BIG':
                                    $track_info['bitrate'] = $track_info['sample_rate'] * $track_info['channels'] * $trackarray['BitDepth'];
                                    break;
                                case 'A_AC3':
                                case 'A_EAC3':
                                case 'A_DTS':
                                case 'A_MPEG/L3':
                                case 'A_MPEG/L2':
                                case 'A_FLAC':
                                    if ($track_info['dataformat'] === 'eac3') {
                                        $module_dataformat = ($track_info['dataformat'] === 'mp2' ? 'mp3' : ('ac3'));
                                    } else {
                                        $module_dataformat = ($track_info['dataformat'] === 'mp2' ? 'mp3' : ($track_info['dataformat']));
                                    }
                                    if (!isset($info['matroska']['track_data_offsets'][$trackarray['TrackNumber']])) {
                                        $this->warning('Unable to parse audio data ['.basename(__FILE__).':'.__LINE__.'] because $info[matroska][track_data_offsets]['.$trackarray['TrackNumber'].'] not set');
                                        break;
                                    }
                                    // create temp instance
                                    $getid3_temp = new getID3();
                                    if ($track_info['dataformat'] !== 'flac') {
                                        $getid3_temp->openfile($this->getid3->filename, $this->getid3->info['filesize'], $this->getid3->fp);
                                    }
                                    $getid3_temp->info['avdataoffset'] = $info['matroska']['track_data_offsets'][$trackarray['TrackNumber']]['offset'];
                                    if ($track_info['dataformat'][0] === 'm' || $track_info['dataformat'] === 'flac') {
                                        $getid3_temp->info['avdataend'] = $info['matroska']['track_data_offsets'][$trackarray['TrackNumber']]['offset'] + $info['matroska']['track_data_offsets'][$trackarray['TrackNumber']]['length'];
                                    }
                                    // analyze
                                    $class = 'getid3_'.$module_dataformat;
                                    $header_data_key = $track_info['dataformat'][0] === 'm' ? 'mpeg' : $track_info['dataformat'];
                                    $getid3_audio = $getid3_temp->load_module($class);
                                    if ($track_info['dataformat'] === 'flac') {
                                       $getid3_audio->AnalyzeString($trackarray['CodecPrivate']);//todo
                                    }
                                    else {
                                        $getid3_audio->Analyze();
                                    }
                                    if (!empty($getid3_temp->info[$header_data_key])) {
                                        $info['matroska']['track_codec_parsed'][$trackarray['TrackNumber']] = $getid3_temp->info[$header_data_key];
                                        if (isset($getid3_temp->info['audio']) && is_array($getid3_temp->info['audio'])) {
                                            foreach ($getid3_temp->info['audio'] as $sub_key => $value) {
                                                $track_info[$sub_key] = $value;
                                            }
                                        }
                                    }
                                    else {
                                        $this->warning('Unable to parse audio data ['.basename(__FILE__).':'.__LINE__.'] because '.$class.'::Analyze() failed at offset '.$getid3_temp->info['avdataoffset']);
                                    }
                                    // copy errors and warnings
                                    if (!empty($getid3_temp->info['error'])) {
                                        foreach ($getid3_temp->info['error'] as $newerror) {
                                            $this->warning($class.'() says: ['.$newerror.']');
                                        }
                                    }
                                    if (!empty($getid3_temp->info['warning'])) {
                                        foreach ($getid3_temp->info['warning'] as $newerror) {
                                            $this->warning($class.'() says: ['.$newerror.']');
                                        }
                                    }
                                    unset($getid3_temp, $getid3_audio);
                                    break;
                                case 'A_AAC':
                                case 'A_AAC/MPEG2/LC':
                                case 'A_AAC/MPEG2/LC/SBR':
                                case 'A_AAC/MPEG4/LC':
                                case 'A_AAC/MPEG4/LC/SBR':
                                    $this->warning($trackarray['CodecID'].' audio data contains no header, audio/video bitrates can\'t be calculated');
                                    break;
                                case 'A_VORBIS':
                                    if (!isset($trackarray['CodecPrivate'])) {
                                        $this->warning('Unable to parse audio data ['.basename(__FILE__).':'.__LINE__.'] because CodecPrivate data not set');
                                        break;
                                    }
                                    $vorbis_offset = strpos($trackarray['CodecPrivate'], 'vorbis', 1);
                                    if ($vorbis_offset === false) {
                                        $this->warning('Unable to parse audio data ['.basename(__FILE__).':'.__LINE__.'] because CodecPrivate data does not contain "vorbis" keyword');
                                        break;
                                    }
                                    --$vorbis_offset;
                                    $getid3_temp = new getID3();
                                    $getid3_ogg = new getid3_ogg($getid3_temp);
                                    $oggpageinfo['page_seqno'] = 0;
                                    $getid3_ogg->ParseVorbisPageHeader($trackarray['CodecPrivate'], $vorbis_offset, $oggpageinfo);
                                    if (!empty($getid3_temp->info['ogg'])) {
                                        $info['matroska']['track_codec_parsed'][$trackarray['TrackNumber']] = $getid3_temp->info['ogg'];
                                        if (isset($getid3_temp->info['audio']) && is_array($getid3_temp->info['audio'])) {
                                            foreach ($getid3_temp->info['audio'] as $sub_key => $value) {
                                                $track_info[$sub_key] = $value;
                                            }
                                        }
                                    }
                                    if (!empty($getid3_temp->info['error'])) {
                                        foreach ($getid3_temp->info['error'] as $newerror) {
                                            $this->warning('getid3_ogg() says: ['.$newerror.']');
                                        }
                                    }
                                    if (!empty($getid3_temp->info['warning'])) {
                                        foreach ($getid3_temp->info['warning'] as $newerror) {
                                            $this->warning('getid3_ogg() says: ['.$newerror.']');
                                        }
                                    }
                                    if (!empty($getid3_temp->info['ogg']['bitrate_nominal'])) {
                                        $track_info['bitrate'] = $getid3_temp->info['ogg']['bitrate_nominal'];
                                    }
                                    unset($getid3_temp, $getid3_ogg, $oggpageinfo, $vorbis_offset);
                                    break;
                                case 'A_MS/ACM':
                                    $parsed = getid3_riff::parseWAVEFORMATex($trackarray['CodecPrivate']);
                                    foreach ($parsed as $sub_key => $value) {
                                        if ($sub_key !== 'raw') {
                                            $track_info[$sub_key] = $value;
                                        }
                                    }
                                    $info['matroska']['track_codec_parsed'][$trackarray['TrackNumber']] = $parsed;
                                    break;
                                default:
                                    $this->warning('Unhandled audio type "'.($trackarray['CodecID'] ?? '').'"');
                                    break;
                            }
                            $info['audio']['streams'][$trackarray['TrackUID']] = $track_info;
                            break;
                    }
                }
                if (!empty($info['video']['streams'])) {
                    $info['video'] = self::getDefaultStreamInfo($info['video']['streams']);
                }
                if (!empty($info['audio']['streams'])) {
                    $info['audio'] = self::getDefaultStreamInfo($info['audio']['streams']);
                }
            }
            if (isset($info['matroska']['attachments']) && $this->getid3->option_save_attachments !== getID3::ATTACHMENTS_NONE) {
                foreach ($info['matroska']['attachments'] as $i => $entry) {
                    /** @noinspection NotOptimalIfConditionsInspection */
                    if (strpos($entry['FileMimeType'], 'image/') === 0 && !empty($entry['FileData'])) {
                        $info['matroska']['comments']['picture'][] = array('data' => $entry['FileData'], 'image_mime' => $entry['FileMimeType'], 'filename' => $entry['FileName']);
                    }
                }
            }
            if (!empty($info['video']['streams'])) {
                $info['mime_type'] = ($info['matroska']['doctype'] === 'webm' ? 'video/webm' : 'video/x-matroska');
            } elseif (!empty($info['audio']['streams'])) {
                $info['mime_type'] = ($info['matroska']['doctype'] === 'webm' ? 'audio/webm' : 'audio/x-matroska');
            } elseif (isset($info['mime_type'])) {
                unset($info['mime_type']);
            }
            if (!empty($info['matroska']['tags'])) {
                $_STATISTICS_byTrackUID = array();
                foreach ($info['matroska']['tags'] as $key1 => $value1) {
                    if (!empty($value1['Targets']['TagTrackUID'][0]) && !empty($value1['SimpleTag'])) {
                        foreach ($value1['SimpleTag'] as $key2 => $value2) {
                            if (!empty($value2['TagName']) && isset($value2['TagString'])) {
                                $_STATISTICS_byTrackUID[$value1['Targets']['TagTrackUID'][0]][$value2['TagName']] = $value2['TagString'];
                            }
                        }
                    }
                }
                foreach (array('audio','video') as $avtype) {
                    if (!empty($info[$avtype]['streams'])) {
                        foreach ($info[$avtype]['streams'] as $trackUID => $trackdata) {
                            if (!isset($trackdata['bitrate']) && !empty($_STATISTICS_byTrackUID[$trackUID]['BPS'])) {
                                $info[$avtype]['streams'][$trackUID]['bitrate'] = (int) $_STATISTICS_byTrackUID[$trackUID]['BPS'];
                                @$info[$avtype]['bitrate'] += $info[$avtype]['streams'][$trackUID]['bitrate'];
                            }
                        }
                    }
                }
            }
            return true;
        }

        /**
         * @param array $info
         */
        private function parseEBML(&$info):void {
            // http://www.matroska.org/technical/specs/index.html#EBMLBasics
            $this->current_offset = $info['avdataoffset'];
            while ($this->getEBMLelement($top_element, $info['avdataend'])) {
                switch ($top_element['id']) {
                    case EBML_ID_EBML:
                        $info['matroska']['header']['offset'] = $top_element['offset'];
                        $info['matroska']['header']['length'] = $top_element['length'];
                        while ($this->getEBMLelement($element_data, $top_element['end'], true)) {
                            switch ($element_data['id']) {
                                case EBML_ID_EBMLVERSION:
                                case EBML_ID_EBMLREADVERSION:
                                case EBML_ID_EBMLMAXIDLENGTH:
                                case EBML_ID_EBMLMAXSIZELENGTH:
                                case EBML_ID_DOCTYPEVERSION:
                                case EBML_ID_DOCTYPEREADVERSION:
                                    $element_data['data'] = getid3_lib::BigEndian2Int($element_data['data']);
                                    break;
                                case EBML_ID_DOCTYPE:
                                    $element_data['data'] = getid3_lib::trimNullByte($element_data['data']);
                                    $info['matroska']['doctype'] = $element_data['data'];
                                    $info['fileformat'] = $element_data['data'];
                                    break;
                                default:
                                    $this->unhandledElement('header', __LINE__, $element_data);
                                    break;
                            }
                            unset($element_data['offset'], $element_data['end']);
                            $info['matroska']['header']['elements'][] = $element_data;
                        }
                        break;
                    case EBML_ID_SEGMENT:
                        $info['matroska']['segment'][0]['offset'] = $top_element['offset'];
                        $info['matroska']['segment'][0]['length'] = $top_element['length'];
                        while ($this->getEBMLelement($element_data, $top_element['end'])) {
                            if ($element_data['id'] !== EBML_ID_CLUSTER || !$this->hide_clusters) { // collect clusters only if required
                                $info['matroska']['segments'][] = $element_data;
                            }
                            switch ($element_data['id']) {
                                case EBML_ID_SEEKHEAD: // Contains the position of other level 1 elements.
                                    while ($this->getEBMLelement($seek_entry, $element_data['end'])) {
                                        if ($seek_entry['id'] === EBML_ID_SEEK) { // Contains a single seek entry to an EBML element
                                            /** @noinspection LoopWhichDoesNotLoopInspection */
                                            while ($this->getEBMLelement($sub_seek_entry, $seek_entry['end'], true)) {
                                                switch ($sub_seek_entry['id']) {
                                                    case EBML_ID_SEEKID:
                                                        $seek_entry['target_id'] = self::EBML2Int($sub_seek_entry['data']);
                                                        $seek_entry['target_name'] = self::EBMLidName($seek_entry['target_id']);
                                                        break;
                                                    case EBML_ID_SEEKPOSITION:
                                                        $seek_entry['target_offset'] = $element_data['offset'] + getid3_lib::BigEndian2Int($sub_seek_entry['data']);
                                                        break;
                                                    default:
                                                        $this->unhandledElement('seekhead.seek', __LINE__, $sub_seek_entry);
                                                }
                                                break;
                                            }
                                            if (!isset($seek_entry['target_id'])) {
                                                $this->warning('seek_entry[target_id] unexpectedly not set at ' . $seek_entry['offset']);
                                                break;
                                            }
                                            if (($seek_entry['target_id'] !== EBML_ID_CLUSTER) || !$this->hide_clusters) { // collect clusters only if required
                                                $info['matroska']['seek'][] = $seek_entry;
                                            }
                                        } else {
                                            $this->unhandledElement('seekhead', __LINE__, $seek_entry);
                                        }
                                    }
                                    break;
                                case EBML_ID_TRACKS: // A top-level block of information with many tracks described.
                                    $info['matroska']['tracks'] = $element_data;
                                    while ($this->getEBMLelement($track_entry, $element_data['end'])) {
                                        if ($track_entry['id'] === EBML_ID_TRACKENTRY) { //subelements: Describes a track with all elements.
                                            while ($this->getEBMLelement($subelement, $track_entry['end'], array(EBML_ID_VIDEO, EBML_ID_AUDIO, EBML_ID_CONTENTENCODINGS, EBML_ID_CODECPRIVATE))) {
                                                switch ($subelement['id']) {
                                                    case EBML_ID_TRACKUID:
                                                        $track_entry[$subelement['id_name']] = getid3_lib::PrintHexBytes($subelement['data'], true, false);
                                                        break;
                                                    case EBML_ID_TRACKNUMBER:
                                                    case EBML_ID_TRACKTYPE:
                                                    case EBML_ID_MINCACHE:
                                                    case EBML_ID_MAXCACHE:
                                                    case EBML_ID_MAXBLOCKADDITIONID:
                                                    case EBML_ID_DEFAULTDURATION: // nanoseconds per frame
                                                        $track_entry[$subelement['id_name']] = getid3_lib::BigEndian2Int($subelement['data']);
                                                        break;
                                                    case EBML_ID_TRACKTIMECODESCALE:
                                                        $track_entry[$subelement['id_name']] = getid3_lib::BigEndian2Float($subelement['data']);
                                                        break;
                                                    case EBML_ID_CODECID:
                                                    case EBML_ID_LANGUAGE:
                                                    case EBML_ID_NAME:
                                                    case EBML_ID_CODECNAME:
                                                        $track_entry[$subelement['id_name']] = getid3_lib::trimNullByte($subelement['data']);
                                                        break;
                                                    case EBML_ID_CODECPRIVATE:
                                                        $track_entry[$subelement['id_name']] = $this->readEBMLelementData($subelement['length'], true);
                                                        break;
                                                    case EBML_ID_FLAGENABLED:
                                                    case EBML_ID_FLAGDEFAULT:
                                                    case EBML_ID_FLAGFORCED:
                                                    case EBML_ID_FLAGLACING:
                                                    case EBML_ID_CODECDECODEALL:
                                                        $track_entry[$subelement['id_name']] = (bool)getid3_lib::BigEndian2Int($subelement['data']);
                                                        break;
                                                    case EBML_ID_VIDEO:
                                                        while ($this->getEBMLelement($sub_subelement, $subelement['end'], true)) {
                                                            switch ($sub_subelement['id']) {
                                                                case EBML_ID_PIXELWIDTH:
                                                                case EBML_ID_PIXELHEIGHT:
                                                                case EBML_ID_PIXELCROPBOTTOM:
                                                                case EBML_ID_PIXELCROPTOP:
                                                                case EBML_ID_PIXELCROPLEFT:
                                                                case EBML_ID_PIXELCROPRIGHT:
                                                                case EBML_ID_DISPLAYWIDTH:
                                                                case EBML_ID_DISPLAYHEIGHT:
                                                                case EBML_ID_DISPLAYUNIT:
                                                                case EBML_ID_ASPECTRATIOTYPE:
                                                                case EBML_ID_STEREOMODE:
                                                                case EBML_ID_OLDSTEREOMODE:
                                                                    $track_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_FLAGINTERLACED:
                                                                    $track_entry[$sub_subelement['id_name']] = (bool)getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_GAMMAVALUE:
                                                                    $track_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Float($sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_COLOURSPACE:
                                                                    $track_entry[$sub_subelement['id_name']] = getid3_lib::trimNullByte($sub_subelement['data']);
                                                                    break;
                                                                default:
                                                                    $this->unhandledElement('track.video', __LINE__, $sub_subelement);
                                                                    break;
                                                            }
                                                        }
                                                        break;
                                                    case EBML_ID_AUDIO:
                                                        while ($this->getEBMLelement($sub_subelement, $subelement['end'], true)) {
                                                            switch ($sub_subelement['id']) {
                                                                case EBML_ID_CHANNELS:
                                                                case EBML_ID_BITDEPTH:
                                                                    $track_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_SAMPLINGFREQUENCY:
                                                                case EBML_ID_OUTPUTSAMPLINGFREQUENCY:
                                                                    $track_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Float($sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_CHANNELPOSITIONS:
                                                                    $track_entry[$sub_subelement['id_name']] = getid3_lib::trimNullByte($sub_subelement['data']);
                                                                    break;
                                                                default:
                                                                    $this->unhandledElement('track.audio', __LINE__, $sub_subelement);
                                                                    break;
                                                            }
                                                        }
                                                        break;
                                                    case EBML_ID_CONTENTENCODINGS:
                                                        while ($this->getEBMLelement($sub_subelement, $subelement['end'])) {
                                                            if ($sub_subelement['id'] === EBML_ID_CONTENTENCODING) {
                                                                while ($this->getEBMLelement($sub_sub_subelement, $sub_subelement['end'], array(EBML_ID_CONTENTCOMPRESSION, EBML_ID_CONTENTENCRYPTION))) {
                                                                    switch ($sub_sub_subelement['id']) {
                                                                        case EBML_ID_CONTENTENCODINGORDER:
                                                                        case EBML_ID_CONTENTENCODINGSCOPE:
                                                                        case EBML_ID_CONTENTENCODINGTYPE:
                                                                            $track_entry[$sub_subelement['id_name']][$sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_subelement['data']);
                                                                            break;
                                                                        case EBML_ID_CONTENTCOMPRESSION:
                                                                            while ($this->getEBMLelement($sub_sub_sub_subelement, $sub_sub_subelement['end'], true)) {
                                                                                switch ($sub_sub_sub_subelement['id']) {
                                                                                    case EBML_ID_CONTENTCOMPALGO:
                                                                                        $track_entry[$sub_subelement['id_name']][$sub_sub_subelement['id_name']][$sub_sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_sub_subelement['data']);
                                                                                        break;
                                                                                    case EBML_ID_CONTENTCOMPSETTINGS:
                                                                                        $track_entry[$sub_subelement['id_name']][$sub_sub_subelement['id_name']][$sub_sub_sub_subelement['id_name']] = $sub_sub_sub_subelement['data'];
                                                                                        break;
                                                                                    default:
                                                                                        $this->unhandledElement('track.contentencodings.contentencoding.contentcompression', __LINE__, $sub_sub_sub_subelement);
                                                                                        break;
                                                                                }
                                                                            }
                                                                            break;
                                                                        case EBML_ID_CONTENTENCRYPTION:
                                                                            while ($this->getEBMLelement($sub_sub_sub_subelement, $sub_sub_subelement['end'], true)) {
                                                                                switch ($sub_sub_sub_subelement['id']) {
                                                                                    case EBML_ID_CONTENTENCALGO:
                                                                                    case EBML_ID_CONTENTSIGALGO:
                                                                                    case EBML_ID_CONTENTSIGHASHALGO:
                                                                                        $track_entry[$sub_subelement['id_name']][$sub_sub_subelement['id_name']][$sub_sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_sub_subelement['data']);
                                                                                        break;
                                                                                    case EBML_ID_CONTENTENCKEYID:
                                                                                    case EBML_ID_CONTENTSIGNATURE:
                                                                                    case EBML_ID_CONTENTSIGKEYID:
                                                                                        $track_entry[$sub_subelement['id_name']][$sub_sub_subelement['id_name']][$sub_sub_sub_subelement['id_name']] = $sub_sub_sub_subelement['data'];
                                                                                        break;
                                                                                    default:
                                                                                        $this->unhandledElement('track.contentencodings.contentencoding.contentcompression', __LINE__, $sub_sub_sub_subelement);
                                                                                        break;
                                                                                }
                                                                            }
                                                                            break;
                                                                        default:
                                                                            $this->unhandledElement('track.contentencodings.contentencoding', __LINE__, $sub_sub_subelement);
                                                                            break;
                                                                    }
                                                                }
                                                            } else {
                                                                $this->unhandledElement('track.contentencodings', __LINE__, $sub_subelement);
                                                            }
                                                        }
                                                        break;
                                                    default:
                                                        $this->unhandledElement('track', __LINE__, $subelement);
                                                        break;
                                                }
                                            }
                                            $info['matroska']['tracks']['tracks'][] = $track_entry;
                                        } else {
                                            $this->unhandledElement('tracks', __LINE__, $track_entry);
                                        }
                                    }
                                    break;
                                case EBML_ID_INFO: // Contains miscellaneous general information and statistics on the file.
                                    $info_entry = [];
                                    while ($this->getEBMLelement($subelement, $element_data['end'], true)) {
                                        switch ($subelement['id']) {
                                            case EBML_ID_TIMECODESCALE:
                                                $info_entry[$subelement['id_name']] = getid3_lib::BigEndian2Int($subelement['data']);
                                                break;
                                            case EBML_ID_DURATION:
                                                $info_entry[$subelement['id_name']] = getid3_lib::BigEndian2Float($subelement['data']);
                                                break;
                                            case EBML_ID_DATEUTC:
                                                $info_entry[$subelement['id_name']]         = getid3_lib::BigEndian2Int($subelement['data']);
                                                $info_entry[$subelement['id_name'].'_unix'] = self::EBMLdate2unix($info_entry[$subelement['id_name']]);
                                                break;
                                            case EBML_ID_SEGMENTUID:
                                            case EBML_ID_PREVUID:
                                            case EBML_ID_NEXTUID:
                                                $info_entry[$subelement['id_name']] = getid3_lib::trimNullByte($subelement['data']);
                                                break;
                                            case EBML_ID_SEGMENTFAMILY:
                                                $info_entry[$subelement['id_name']][] = getid3_lib::trimNullByte($subelement['data']);
                                                break;
                                            case EBML_ID_SEGMENTFILENAME:
                                            case EBML_ID_PREVFILENAME:
                                            case EBML_ID_NEXTFILENAME:
                                            case EBML_ID_TITLE:
                                            case EBML_ID_MUXINGAPP:
                                            case EBML_ID_WRITINGAPP:
                                                $info_entry[$subelement['id_name']] = getid3_lib::trimNullByte($subelement['data']);
                                                $info['matroska']['comments'][strtolower($subelement['id_name'])][] = $info_entry[$subelement['id_name']];
                                                break;
                                            case EBML_ID_CHAPTERTRANSLATE:
                                                $chaptertranslate_entry = array();
                                                while ($this->getEBMLelement($sub_subelement, $subelement['end'], true)) {
                                                    switch ($sub_subelement['id']) {
                                                        case EBML_ID_CHAPTERTRANSLATEEDITIONUID:
                                                            $chaptertranslate_entry[$sub_subelement['id_name']][] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                            break;
                                                        case EBML_ID_CHAPTERTRANSLATECODEC:
                                                            $chaptertranslate_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                            break;
                                                        case EBML_ID_CHAPTERTRANSLATEID:
                                                            $chaptertranslate_entry[$sub_subelement['id_name']] = getid3_lib::trimNullByte($sub_subelement['data']);
                                                            break;
                                                        default:
                                                            $this->unhandledElement('info.chaptertranslate', __LINE__, $sub_subelement);
                                                            break;
                                                    }
                                                }
                                                $info_entry[$subelement['id_name']] = $chaptertranslate_entry;
                                                break;
                                            default:
                                                $this->unhandledElement('info', __LINE__, $subelement);
                                                break;
                                        }
                                    }
                                    $info['matroska']['info'][] = $info_entry;
                                    break;
                                case EBML_ID_CUES: // A top-level element to speed seeking access. All entries are local to the segment. Should be mandatory for non "live" streams.
                                    if ($this->hide_clusters) { // do not parse cues if hide clusters is "ON" till they point to clusters anyway
                                        $this->current_offset = $element_data['end'];
                                        break;
                                    }
                                    $cues_entry = array();
                                    while ($this->getEBMLelement($subelement, $element_data['end'])) {
                                        if ($subelement['id'] === EBML_ID_CUEPOINT) {
                                            $cuepoint_entry = array();
                                            while ($this->getEBMLelement($sub_subelement, $subelement['end'], array(EBML_ID_CUETRACKPOSITIONS))) {
                                                switch ($sub_subelement['id']) {
                                                    case EBML_ID_CUETRACKPOSITIONS:
                                                        $cuetrackpositions_entry = array();
                                                        while ($this->getEBMLelement($sub_sub_subelement, $sub_subelement['end'], true)) {
                                                            switch ($sub_sub_subelement['id']) {
                                                                case EBML_ID_CUETRACK:
                                                                case EBML_ID_CUECLUSTERPOSITION:
                                                                case EBML_ID_CUEBLOCKNUMBER:
                                                                case EBML_ID_CUECODECSTATE:
                                                                    $cuetrackpositions_entry[$sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_subelement['data']);
                                                                    break;
                                                                default:
                                                                    $this->unhandledElement('cues.cuepoint.cuetrackpositions', __LINE__, $sub_sub_subelement);
                                                                    break;
                                                            }
                                                        }
                                                        $cuepoint_entry[$sub_subelement['id_name']][] = $cuetrackpositions_entry;
                                                        break;
                                                    case EBML_ID_CUETIME:
                                                        $cuepoint_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                        break;
                                                    default:
                                                        $this->unhandledElement('cues.cuepoint', __LINE__, $sub_subelement);
                                                        break;
                                                }
                                            }
                                            $cues_entry[] = $cuepoint_entry;
                                        } else {
                                            $this->unhandledElement('cues', __LINE__, $subelement);
                                        }
                                    }
                                    $info['matroska']['cues'] = $cues_entry;
                                    break;
                                case EBML_ID_TAGS: // Element containing elements specific to Tracks/Chapters.
                                    $tags_entry = [];
                                    while ($this->getEBMLelement($subelement, $element_data['end'], false)) {
                                        if ($subelement['id'] === EBML_ID_TAG) {
                                            $tag_entry = array();
                                            while ($this->getEBMLelement($sub_subelement, $subelement['end'], false)) {
                                                switch ($sub_subelement['id']) {
                                                    case EBML_ID_TARGETS:
                                                        $targets_entry = [];
                                                        while ($this->getEBMLelement($sub_sub_subelement, $sub_subelement['end'], true)) {
                                                            switch ($sub_sub_subelement['id']) {
                                                                case EBML_ID_TARGETTYPEVALUE:
                                                                    $targets_entry[$sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_subelement['data']);
                                                                    $targets_entry[strtolower($sub_sub_subelement['id_name']) . '_long'] = self::TargetTypeValue($targets_entry[$sub_sub_subelement['id_name']]);
                                                                    break;
                                                                case EBML_ID_TARGETTYPE:
                                                                    $targets_entry[$sub_sub_subelement['id_name']] = $sub_sub_subelement['data'];
                                                                    break;
                                                                case EBML_ID_TAGTRACKUID:
                                                                case EBML_ID_TAGEDITIONUID:
                                                                case EBML_ID_TAGCHAPTERUID:
                                                                case EBML_ID_TAGATTACHMENTUID:
                                                                    $targets_entry[$sub_sub_subelement['id_name']][] = getid3_lib::PrintHexBytes($sub_sub_subelement['data'], true, false);
                                                                    break;
                                                                default:
                                                                    $this->unhandledElement('tags.tag.targets', __LINE__, $sub_sub_subelement);
                                                                    break;
                                                            }
                                                        }
                                                        $tag_entry[$sub_subelement['id_name']] = $targets_entry;
                                                        break;
                                                    case EBML_ID_SIMPLETAG:
                                                        $tag_entry[$sub_subelement['id_name']][] = $this->HandleEMBLSimpleTag($sub_subelement['end']);
                                                        break;
                                                    default:
                                                        $this->unhandledElement('tags.tag', __LINE__, $sub_subelement);
                                                        break;
                                                }
                                            }
                                            $tags_entry[] = $tag_entry;
                                        } else {
                                            $this->unhandledElement('tags', __LINE__, $subelement);
                                        }
                                    }
                                    $info['matroska']['tags'] = $tags_entry;
                                    break;
                                case EBML_ID_ATTACHMENTS: // Contain attached files.
                                    while ($this->getEBMLelement($subelement, $element_data['end'])) {
                                        if ($subelement['id'] === EBML_ID_ATTACHEDFILE) {
                                            $attachedfile_entry = array();
                                            while ($this->getEBMLelement($sub_subelement, $subelement['end'], array(EBML_ID_FILEDATA))) {
                                                switch ($sub_subelement['id']) {
                                                    case EBML_ID_FILEDESCRIPTION:
                                                    case EBML_ID_FILENAME:
                                                    case EBML_ID_FILEMIMETYPE:
                                                        $attachedfile_entry[$sub_subelement['id_name']] = $sub_subelement['data'];
                                                        break;
                                                    case EBML_ID_FILEDATA:
                                                        $attachedfile_entry['data_offset'] = $this->current_offset;
                                                        $attachedfile_entry['data_length'] = $sub_subelement['length'];
                                                        $attachedfile_entry[$sub_subelement['id_name']] = $this->saveAttachment(
                                                            $attachedfile_entry['FileName'],
                                                            $attachedfile_entry['data_offset'],
                                                            $attachedfile_entry['data_length']);
                                                        $this->current_offset = $sub_subelement['end'];
                                                        break;
                                                    case EBML_ID_FILEUID:
                                                        $attachedfile_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                        break;
                                                    default:
                                                        $this->unhandledElement('attachments.attachedfile', __LINE__, $sub_subelement);
                                                        break;
                                                }
                                            }
                                            $info['matroska']['attachments'][] = $attachedfile_entry;
                                        } else {
                                            $this->unhandledElement('attachments', __LINE__, $subelement);
                                        }
                                    }
                                    break;
                                case EBML_ID_CHAPTERS:
                                    while ($this->getEBMLelement($subelement, $element_data['end'])) {
                                        if ($subelement['id'] === EBML_ID_EDITIONENTRY) {
                                            $editionentry_entry = array();
                                            while ($this->getEBMLelement($sub_subelement, $subelement['end'], array(EBML_ID_CHAPTERATOM))) {
                                                switch ($sub_subelement['id']) {
                                                    case EBML_ID_EDITIONUID:
                                                        $editionentry_entry[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                        break;
                                                    case EBML_ID_EDITIONFLAGHIDDEN:
                                                    case EBML_ID_EDITIONFLAGDEFAULT:
                                                    case EBML_ID_EDITIONFLAGORDERED:
                                                        $editionentry_entry[$sub_subelement['id_name']] = (bool)getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                        break;
                                                    case EBML_ID_CHAPTERATOM:
                                                        $chapteratom_entry = array();
                                                        while ($this->getEBMLelement($sub_sub_subelement, $sub_subelement['end'], array(EBML_ID_CHAPTERTRACK, EBML_ID_CHAPTERDISPLAY))) {
                                                            switch ($sub_sub_subelement['id']) {
                                                                case EBML_ID_CHAPTERSEGMENTUID:
                                                                case EBML_ID_CHAPTERSEGMENTEDITIONUID:
                                                                    $chapteratom_entry[$sub_sub_subelement['id_name']] = $sub_sub_subelement['data'];
                                                                    break;
                                                                case EBML_ID_CHAPTERFLAGENABLED:
                                                                case EBML_ID_CHAPTERFLAGHIDDEN:
                                                                    $chapteratom_entry[$sub_sub_subelement['id_name']] = (bool)getid3_lib::BigEndian2Int($sub_sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_CHAPTERUID:
                                                                case EBML_ID_CHAPTERTIMESTART:
                                                                case EBML_ID_CHAPTERTIMEEND:
                                                                    $chapteratom_entry[$sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_subelement['data']);
                                                                    break;
                                                                case EBML_ID_CHAPTERTRACK:
                                                                    $chaptertrack_entry = array();
                                                                    while ($this->getEBMLelement($sub_sub_sub_subelement, $sub_sub_subelement['end'], true)) {
                                                                        if ($sub_sub_sub_subelement['id'] === EBML_ID_CHAPTERTRACKNUMBER) {
                                                                            $chaptertrack_entry[$sub_sub_sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_sub_sub_subelement['data']);
                                                                        } else {
                                                                            $this->unhandledElement('chapters.editionentry.chapteratom.chaptertrack', __LINE__, $sub_sub_sub_subelement);
                                                                        }
                                                                    }
                                                                    $chapteratom_entry[$sub_sub_subelement['id_name']][] = $chaptertrack_entry;
                                                                    break;
                                                                case EBML_ID_CHAPTERDISPLAY:
                                                                    $chapterdisplay_entry = array();
                                                                    while ($this->getEBMLelement($sub_sub_sub_subelement, $sub_sub_subelement['end'], true)) {
                                                                        switch ($sub_sub_sub_subelement['id']) {
                                                                            case EBML_ID_CHAPSTRING:
                                                                            case EBML_ID_CHAPLANGUAGE:
                                                                            case EBML_ID_CHAPCOUNTRY:
                                                                                $chapterdisplay_entry[$sub_sub_sub_subelement['id_name']] = $sub_sub_sub_subelement['data'];
                                                                                break;
                                                                            default:
                                                                                $this->unhandledElement('chapters.editionentry.chapteratom.chapterdisplay', __LINE__, $sub_sub_sub_subelement);
                                                                                break;
                                                                        }
                                                                    }
                                                                    $chapteratom_entry[$sub_sub_subelement['id_name']][] = $chapterdisplay_entry;
                                                                    break;
                                                                default:
                                                                    $this->unhandledElement('chapters.editionentry.chapteratom', __LINE__, $sub_sub_subelement);
                                                                    break;
                                                            }
                                                        }
                                                        $editionentry_entry[$sub_subelement['id_name']][] = $chapteratom_entry;
                                                        break;
                                                    default:
                                                        $this->unhandledElement('chapters.editionentry', __LINE__, $sub_subelement);
                                                        break;
                                                }
                                            }
                                            $info['matroska']['chapters'][] = $editionentry_entry;
                                        } else {
                                            $this->unhandledElement('chapters', __LINE__, $subelement);
                                        }
                                    }
                                    break;

                                case EBML_ID_CLUSTER: // The lower level element containing the (monolithic) Block structure.
                                    $cluster_entry = array();
                                    while ($this->getEBMLelement($subelement, $element_data['end'], array(EBML_ID_CLUSTERSILENTTRACKS, EBML_ID_CLUSTERBLOCKGROUP, EBML_ID_CLUSTERSIMPLEBLOCK))) {
                                        switch ($subelement['id']) {
                                            case EBML_ID_CLUSTERTIMECODE:
                                            case EBML_ID_CLUSTERPOSITION:
                                            case EBML_ID_CLUSTERPREVSIZE:
                                                $cluster_entry[$subelement['id_name']] = getid3_lib::BigEndian2Int($subelement['data']);
                                                break;
                                            case EBML_ID_CLUSTERSILENTTRACKS:
                                                $cluster_silent_tracks = array();
                                                while ($this->getEBMLelement($sub_subelement, $subelement['end'], true)) {
                                                    if ($sub_subelement['id'] === EBML_ID_CLUSTERSILENTTRACKNUMBER) {
                                                        $cluster_silent_tracks[] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                    } else {
                                                        $this->unhandledElement('cluster.silenttracks', __LINE__, $sub_subelement);
                                                    }
                                                }
                                                $cluster_entry[$subelement['id_name']][] = $cluster_silent_tracks;
                                                break;
                                            case EBML_ID_CLUSTERBLOCKGROUP:
                                                $cluster_block_group = array('offset' => $this->current_offset);

                                                while ($this->getEBMLelement($sub_subelement, $subelement['end'], array(EBML_ID_CLUSTERBLOCK))) {
                                                    switch ($sub_subelement['id']) {
                                                        case EBML_ID_CLUSTERBLOCK:
                                                            $cluster_block_group[$sub_subelement['id_name']] = $this->HandleEMBLClusterBlock($sub_subelement, EBML_ID_CLUSTERBLOCK, $info);
                                                            break;
                                                        case EBML_ID_CLUSTERREFERENCEPRIORITY: // unsigned-int
                                                        case EBML_ID_CLUSTERBLOCKDURATION:     // unsigned-int
                                                            $cluster_block_group[$sub_subelement['id_name']] = getid3_lib::BigEndian2Int($sub_subelement['data']);
                                                            break;
                                                        case EBML_ID_CLUSTERREFERENCEBLOCK:    // signed-int
                                                            $cluster_block_group[$sub_subelement['id_name']][] = getid3_lib::BigEndian2Int($sub_subelement['data'], false, true);
                                                            break;
                                                        case EBML_ID_CLUSTERCODECSTATE:
                                                            $cluster_block_group[$sub_subelement['id_name']] = getid3_lib::trimNullByte($sub_subelement['data']);
                                                            break;
                                                        default:
                                                            $this->unhandledElement('clusters.blockgroup', __LINE__, $sub_subelement);
                                                            break;
                                                    }
                                                }
                                                $cluster_entry[$subelement['id_name']][] = $cluster_block_group;
                                                break;
                                            case EBML_ID_CLUSTERSIMPLEBLOCK:
                                                $cluster_entry[$subelement['id_name']][] = $this->HandleEMBLClusterBlock($subelement, EBML_ID_CLUSTERSIMPLEBLOCK, $info);
                                                break;
                                            default:
                                                $this->unhandledElement('cluster', __LINE__, $subelement);
                                                break;
                                        }
                                        $this->current_offset = $subelement['end'];
                                    }
                                    if (!$this->hide_clusters) {
                                        $info['matroska']['cluster'][] = $cluster_entry;
                                    }
                                    //todo from here check to see if all the data we need exists already, if so, break out of the loop
                                    if (isset($info['matroska']['info'], $info['matroska']['tracks']['tracks']) && !$this->parse_whole_file && is_array($info['matroska']['info']) && is_array($info['matroska']['tracks']['tracks']) && count($info['matroska']['track_data_offsets']) === count($info['matroska']['tracks']['tracks'])) {
                                        return;
                                    }
                                    break;
                                default:
                                    $this->unhandledElement('segment', __LINE__, $element_data);
                                    break;
                            }
                        }
                        break;
                    default:
                        $this->unhandledElement('root', __LINE__, $top_element);
                        break;
                }
            }
        }//545
        private function EnsureBufferHasEnoughData($min_data=1024):bool {
            if (($this->current_offset - $this->EBMLbuffer_offset) >= ($this->EBMLbuffer_length - $min_data)) {
                $read_bytes = max($min_data, $this->getid3->fread_buffer_size());
                try {
                    $this->fseek($this->current_offset);
                    $this->EBMLbuffer_offset = $this->current_offset;
                    $this->EBMLbuffer        = $this->fread($read_bytes);
                    $this->EBMLbuffer_length = strlen($this->EBMLbuffer);
                } catch (getid3_exception $e) {
                    $this->warning('EBML parser: '.$e->getMessage());
                    return false;
                }
                if ($this->EBMLbuffer_length === 0 && $this->feof()) {
                    return $this->error('EBML parser: ran out of file at offset '.$this->current_offset);
                }
            }
            return true;
        }//1284
        /**
         * @return int|float|false
         */
        private function readEBMLint() {
            $actual_offset = $this->current_offset - $this->EBMLbuffer_offset;
            $first_byte_int = ord($this->EBMLbuffer[$actual_offset]);
            if       (0x80 & $first_byte_int) {
                $length = 1;
            } elseif (0x40 & $first_byte_int) {
                $length = 2;
            } elseif (0x20 & $first_byte_int) {
                $length = 3;
            } elseif (0x10 & $first_byte_int) {
                $length = 4;
            } elseif (0x08 & $first_byte_int) {
                $length = 5;
            } elseif (0x04 & $first_byte_int) {
                $length = 6;
            } elseif (0x02 & $first_byte_int) {
                $length = 7;
            } elseif (0x01 & $first_byte_int) {
                $length = 8;
            } else {
                throw new \RuntimeException('invalid EBML integer (leading 0x00) at '.$this->current_offset);
            }
            // read
            $int_value = self::EBML2Int(substr($this->EBMLbuffer, $actual_offset, $length));
            $this->current_offset += $length;
            return $int_value;
        }//1308
        /**
         * @param int  $length
         * @param bool $check_buffer
         * @return string|false
         */
        private function readEBMLelementData($length, $check_buffer=false) {
            if ($check_buffer && !$this->EnsureBufferHasEnoughData($length)) {
                return false;
            }
            $data = substr($this->EBMLbuffer, $this->current_offset - $this->EBMLbuffer_offset, $length);
            $this->current_offset += $length;
            return $data;
        }//1346
        /**
         * @param array      $element
         * @param int        $parent_end
         * @param array|bool $get_data
         *
         * @return bool
         */
        private function getEBMLelement(&$element, $parent_end, $get_data=false):bool {
            if ($this->current_offset >= $parent_end) {
                return false;
            }
            if (!$this->EnsureBufferHasEnoughData()) {
                $this->current_offset = PHP_INT_MAX; // do not exit parser right now, allow to finish current loop to gather maximum information
                return false;
            }
            $element = [];
            $element['offset'] = $this->current_offset;
            $element['id'] = $this->readEBMLint();
            $element['id_name'] = self::EBMLidName($element['id']);
            $element['length'] = $this->readEBMLint();
            $element['end'] = $this->current_offset + $element['length'];
            $dont_parse = (in_array($element['id'], $this->unuseful_elements, true) || $element['id_name'] === dechex($element['id']));
            if (($get_data === true || (is_array($get_data) && !$dont_parse && !in_array($element['id'], $get_data, true)))) {
                $element['data'] = $this->readEBMLelementData($element['length'], $element);
            }
            return true;
        }//1362
        private function unhandledElement($type, $line, $element):void {
            // warn only about unknown and missed elements, not about unuseful
            if (!in_array($element['id'], $this->unuseful_elements, true)) {
                $this->warning('Unhandled '.$type.' element ['.basename(__FILE__).':'.$line.'] ('.$element['id'].'::'.$element['id_name'].' ['.$element['length'].' bytes]) at '.$element['offset']);
            }
            if (!isset($element['data'])) {
                $this->current_offset = $element['end'];
            }
        }//1403
        /**
         * @param array $SimpleTagArray
         * @return bool
         */
        private function ExtractCommentsSimpleTag($SimpleTagArray):bool {
            if (!empty($SimpleTagArray['SimpleTag'])) {
                foreach ($SimpleTagArray['SimpleTag'] as $SimpleTagKey => $SimpleTagData) {
                    if (!empty($SimpleTagData['TagName']) && !empty($SimpleTagData['TagString'])) {
                        $this->getid3->info['matroska']['comments'][strtolower($SimpleTagData['TagName'])][] = $SimpleTagData['TagString'];
                    }
                    if (!empty($SimpleTagData['SimpleTag'])) {
                        $this->ExtractCommentsSimpleTag($SimpleTagData);
                    }
                }
            }
            return true;
        }//1420
        private function HandleEMBLSimpleTag($parent_end):array {
            $simpletag_entry = array();
            while ($this->getEBMLelement($element, $parent_end, array(EBML_ID_SIMPLETAG))) {
                switch ($element['id']) {
                    case EBML_ID_TAGNAME:
                    case EBML_ID_TAGLANGUAGE:
                    case EBML_ID_TAGSTRING:
                    case EBML_ID_TAGBINARY:
                        $simpletag_entry[$element['id_name']] = $element['data'];
                        break;
                    case EBML_ID_SIMPLETAG:
                        $simpletag_entry[$element['id_name']][] = $this->HandleEMBLSimpleTag($element['end']);
                        break;
                    case EBML_ID_TAGDEFAULT:
                        $simpletag_entry[$element['id_name']] = (bool)getid3_lib::BigEndian2Int($element['data']);
                        break;
                    default:
                        $this->unhandledElement('tag.simpletag', __LINE__, $element);
                        break;
                }
            }
            return $simpletag_entry;
        }//1440
        /**
         * @param array $element
         * @param int   $block_type
         * @param array $info
         *
         * @return array
         */
        private function HandleEMBLClusterBlock($element, $block_type, &$info):array {
            $block_data = array();
            $block_data['tracknumber'] = $this->readEBMLint();
            $block_data['timecode']    = getid3_lib::BigEndian2Int($this->readEBMLelementData(2), false, true);
            $block_data['flags_raw']   = getid3_lib::BigEndian2Int($this->readEBMLelementData(1));
            if ($block_type === EBML_ID_CLUSTERSIMPLEBLOCK) {
                $block_data['flags']['keyframe']  = (($block_data['flags_raw'] & 0x80) >> 7);
            }
            else {
                //$block_data['flags']['reserved1'] = (($block_data['flags_raw'] & 0xF0) >> 4);
            }
            $block_data['flags']['invisible'] = (bool)(($block_data['flags_raw'] & 0x08) >> 3);
            $block_data['flags']['lacing']    =       (($block_data['flags_raw'] & 0x06) >> 1);  // 00=no lacing; 01=Xiph lacing; 11=EBML lacing; 10=fixed-size lacing
            if ($block_type === EBML_ID_CLUSTERSIMPLEBLOCK) {
                $block_data['flags']['discardable'] = (($block_data['flags_raw'] & 0x01));
            }
            else {
                //$block_data['flags']['reserved2'] = (($block_data['flags_raw'] & 0x01) >> 0);
            }
            $block_data['flags']['lacing_type'] = self::BlockLacingType($block_data['flags']['lacing']);
            if ($block_data['flags']['lacing'] > 0) {
                $block_data['lace_frames'] = getid3_lib::BigEndian2Int($this->readEBMLelementData(1)) + 1; // Number of frames in the lace-1 (uint8)
                if ($block_data['flags']['lacing'] !== 0x02) {
                    for ($i = 1; $i < $block_data['lace_frames']; $i ++) { // Lace-coded size of each frame of the lace, except for the last one (multiple uint8). *This is not used with Fixed-size lacing as it is calculated automatically from (total size of lace) / (number of frames in lace).
                        if ($block_data['flags']['lacing'] === 0x03) { // EBML lacing
                            $block_data['lace_frames_size'][$i] = $this->readEBMLint(); // TODO: read size correctly, calc size for the last frame. For now offsets are deteminded OK with readEBMLint() and that's the most important thing.
                        }
                        else { // Xiph lacing
                            $block_data['lace_frames_size'][$i] = 0;
                            do {
                                $size = getid3_lib::BigEndian2Int($this->readEBMLelementData(1));
                                $block_data['lace_frames_size'][$i] += $size;
                            }
                            while ($size === 255);
                        }
                    }
                    if ($block_data['flags']['lacing'] === 0x01) { // calc size of the last frame only for Xiph lacing, till EBML sizes are now anyway determined incorrectly
                        $block_data['lace_frames_size'][] = $element['end'] - $this->current_offset - array_sum($block_data['lace_frames_size']);
                    }
                }
            }
            if (!isset($info['matroska']['track_data_offsets'][$block_data['tracknumber']])) {
                $info['matroska']['track_data_offsets'][$block_data['tracknumber']]['offset'] = $this->current_offset;
                $info['matroska']['track_data_offsets'][$block_data['tracknumber']]['length'] = $element['end'] - $this->current_offset;
            }
            $this->current_offset = $element['end'];
            return $block_data;
        }//1477
        /**
         * @param string $EBMLstring
         * @return int|float|false
         */
        private static function EBML2Int($EBMLstring) {
            $first_byte_int = ord($EBMLstring[0]);
            if (0x80 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x7F);
            } elseif (0x40 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x3F);
            } elseif (0x20 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x1F);
            } elseif (0x10 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x0F);
            } elseif (0x08 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x07);
            } elseif (0x04 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x03);
            } elseif (0x02 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x01);
            } elseif (0x01 & $first_byte_int) {
                $EBMLstring[0] = chr($first_byte_int & 0x00);
            }
            return getid3_lib::BigEndian2Int($EBMLstring);
        }//1545
        /**
         * @param int $EBMLdatestamp
         * @return float
         */
        private static function EBMLdate2unix($EBMLdatestamp):float {
            return round(($EBMLdatestamp / 1000000000) + 978307200);
        }//1592
        /**
         * @param int $target_type
         * @return string|int
         */
        public static function TargetTypeValue($target_type) {
            // http://www.matroska.org/technical/specs/tagging/index.html
            static $TargetTypeValue = array();
            if (empty($TargetTypeValue)) {
                $TargetTypeValue[10] = 'A: ~ V:shot';                                           // the lowest hierarchy found in music or movies
                $TargetTypeValue[20] = 'A:subtrack/part/movement ~ V:scene';                    // corresponds to parts of a track for audio (like a movement)
                $TargetTypeValue[30] = 'A:track/song ~ V:chapter';                              // the common parts of an album or a movie
                $TargetTypeValue[40] = 'A:part/session ~ V:part/session';                       // when an album or episode has different logical parts
                $TargetTypeValue[50] = 'A:album/opera/concert ~ V:movie/episode/concert';       // the most common grouping level of music and video (equals to an episode for TV series)
                $TargetTypeValue[60] = 'A:edition/issue/volume/opus ~ V:season/sequel/volume';  // a list of lower levels grouped together
                $TargetTypeValue[70] = 'A:collection ~ V:collection';                           // the high hierarchy consisting of many different lower items
            }
            return ($TargetTypeValue[$target_type] ?? $target_type);
        }//1603
        /**
         * @param int $lacingtype
         * @return string|int
         */
        public static function BlockLacingType($lacingtype) {
            // http://matroska.org/technical/specs/index.html#block_structure
            static $BlockLacingType = array();
            if (empty($BlockLacingType)) {
                $BlockLacingType[0x00] = 'no lacing';
                $BlockLacingType[0x01] = 'Xiph lacing';
                $BlockLacingType[0x02] = 'fixed-size lacing';
                $BlockLacingType[0x03] = 'EBML lacing';
            }
            return ($BlockLacingType[$lacingtype] ?? $lacingtype);
        }//1623
        /**
         * @param string $codecid
         * @return string
         */
        public static function CodecIDtoCommonName($codecid):string {
            // http://www.matroska.org/technical/specs/codecid/index.html
            static $CodecIDlist = array();
            if (empty($CodecIDlist)) {
                $CodecIDlist['A_AAC']            = 'aac';
                $CodecIDlist['A_AAC/MPEG2/LC']   = 'aac';
                $CodecIDlist['A_AC3']            = 'ac3';
                $CodecIDlist['A_EAC3']           = 'eac3';
                $CodecIDlist['A_DTS']            = 'dts';
                $CodecIDlist['A_FLAC']           = 'flac';
                $CodecIDlist['A_MPEG/L1']        = 'mp1';
                $CodecIDlist['A_MPEG/L2']        = 'mp2';
                $CodecIDlist['A_MPEG/L3']        = 'mp3';
                $CodecIDlist['A_PCM/INT/LIT']    = 'pcm';       // PCM Integer Little Endian
                $CodecIDlist['A_PCM/INT/BIG']    = 'pcm';       // PCM Integer Big Endian
                $CodecIDlist['A_QUICKTIME/QDMC'] = 'quicktime'; // Quicktime: QDesign Music
                $CodecIDlist['A_QUICKTIME/QDM2'] = 'quicktime'; // Quicktime: QDesign Music v2
                $CodecIDlist['A_VORBIS']         = 'vorbis';
                $CodecIDlist['V_MPEG1']          = 'mpeg';
                $CodecIDlist['V_THEORA']         = 'theora';
                $CodecIDlist['V_REAL/RV40']      = 'real';
                $CodecIDlist['V_REAL/RV10']      = 'real';
                $CodecIDlist['V_REAL/RV20']      = 'real';
                $CodecIDlist['V_REAL/RV30']      = 'real';
                $CodecIDlist['V_QUICKTIME']      = 'quicktime'; // Quicktime
                $CodecIDlist['V_MPEG4/ISO/AP']   = 'mpeg4';
                $CodecIDlist['V_MPEG4/ISO/ASP']  = 'mpeg4';
                $CodecIDlist['V_MPEG4/ISO/AVC']  = 'h264';
                $CodecIDlist['V_MPEG4/ISO/SP']   = 'mpeg4';
                $CodecIDlist['V_VP8']            = 'vp8';
                $CodecIDlist['V_MS/VFW/FOURCC']  = 'vcm'; // Microsoft (TM) Video Codec Manager (VCM)
                $CodecIDlist['A_MS/ACM']         = 'acm'; // Microsoft (TM) Audio Codec Manager (ACM)
            }
            return ($CodecIDlist[$codecid] ?? $codecid);
        }//1640
        /**
         * @param int $value
         *
         * @return string
         */
        private static function EBMLidName($value):string {
            static $EBMLidList = [];
            if (empty($EBMLidList)) {
                $EBMLidList[EBML_ID_ASPECTRATIOTYPE]            = 'AspectRatioType';
                $EBMLidList[EBML_ID_ATTACHEDFILE]               = 'AttachedFile';
                $EBMLidList[EBML_ID_ATTACHMENTLINK]             = 'AttachmentLink';
                $EBMLidList[EBML_ID_ATTACHMENTS]                = 'Attachments';
                $EBMLidList[EBML_ID_AUDIO]                      = 'Audio';
                $EBMLidList[EBML_ID_BITDEPTH]                   = 'BitDepth';
                $EBMLidList[EBML_ID_CHANNELPOSITIONS]           = 'ChannelPositions';
                $EBMLidList[EBML_ID_CHANNELS]                   = 'Channels';
                $EBMLidList[EBML_ID_CHAPCOUNTRY]                = 'ChapCountry';
                $EBMLidList[EBML_ID_CHAPLANGUAGE]               = 'ChapLanguage';
                $EBMLidList[EBML_ID_CHAPPROCESS]                = 'ChapProcess';
                $EBMLidList[EBML_ID_CHAPPROCESSCODECID]         = 'ChapProcessCodecID';
                $EBMLidList[EBML_ID_CHAPPROCESSCOMMAND]         = 'ChapProcessCommand';
                $EBMLidList[EBML_ID_CHAPPROCESSDATA]            = 'ChapProcessData';
                $EBMLidList[EBML_ID_CHAPPROCESSPRIVATE]         = 'ChapProcessPrivate';
                $EBMLidList[EBML_ID_CHAPPROCESSTIME]            = 'ChapProcessTime';
                $EBMLidList[EBML_ID_CHAPSTRING]                 = 'ChapString';
                $EBMLidList[EBML_ID_CHAPTERATOM]                = 'ChapterAtom';
                $EBMLidList[EBML_ID_CHAPTERDISPLAY]             = 'ChapterDisplay';
                $EBMLidList[EBML_ID_CHAPTERFLAGENABLED]         = 'ChapterFlagEnabled';
                $EBMLidList[EBML_ID_CHAPTERFLAGHIDDEN]          = 'ChapterFlagHidden';
                $EBMLidList[EBML_ID_CHAPTERPHYSICALEQUIV]       = 'ChapterPhysicalEquiv';
                $EBMLidList[EBML_ID_CHAPTERS]                   = 'Chapters';
                $EBMLidList[EBML_ID_CHAPTERSEGMENTEDITIONUID]   = 'ChapterSegmentEditionUID';
                $EBMLidList[EBML_ID_CHAPTERSEGMENTUID]          = 'ChapterSegmentUID';
                $EBMLidList[EBML_ID_CHAPTERTIMEEND]             = 'ChapterTimeEnd';
                $EBMLidList[EBML_ID_CHAPTERTIMESTART]           = 'ChapterTimeStart';
                $EBMLidList[EBML_ID_CHAPTERTRACK]               = 'ChapterTrack';
                $EBMLidList[EBML_ID_CHAPTERTRACKNUMBER]         = 'ChapterTrackNumber';
                $EBMLidList[EBML_ID_CHAPTERTRANSLATE]           = 'ChapterTranslate';
                $EBMLidList[EBML_ID_CHAPTERTRANSLATECODEC]      = 'ChapterTranslateCodec';
                $EBMLidList[EBML_ID_CHAPTERTRANSLATEEDITIONUID] = 'ChapterTranslateEditionUID';
                $EBMLidList[EBML_ID_CHAPTERTRANSLATEID]         = 'ChapterTranslateID';
                $EBMLidList[EBML_ID_CHAPTERUID]                 = 'ChapterUID';
                $EBMLidList[EBML_ID_CLUSTER]                    = 'Cluster';
                $EBMLidList[EBML_ID_CLUSTERBLOCK]               = 'ClusterBlock';
                $EBMLidList[EBML_ID_CLUSTERBLOCKADDID]          = 'ClusterBlockAddID';
                $EBMLidList[EBML_ID_CLUSTERBLOCKADDITIONAL]     = 'ClusterBlockAdditional';
                $EBMLidList[EBML_ID_CLUSTERBLOCKADDITIONID]     = 'ClusterBlockAdditionID';
                $EBMLidList[EBML_ID_CLUSTERBLOCKADDITIONS]      = 'ClusterBlockAdditions';
                $EBMLidList[EBML_ID_CLUSTERBLOCKDURATION]       = 'ClusterBlockDuration';
                $EBMLidList[EBML_ID_CLUSTERBLOCKGROUP]          = 'ClusterBlockGroup';
                $EBMLidList[EBML_ID_CLUSTERBLOCKMORE]           = 'ClusterBlockMore';
                $EBMLidList[EBML_ID_CLUSTERBLOCKVIRTUAL]        = 'ClusterBlockVirtual';
                $EBMLidList[EBML_ID_CLUSTERCODECSTATE]          = 'ClusterCodecState';
                $EBMLidList[EBML_ID_CLUSTERDELAY]               = 'ClusterDelay';
                $EBMLidList[EBML_ID_CLUSTERDURATION]            = 'ClusterDuration';
                $EBMLidList[EBML_ID_CLUSTERENCRYPTEDBLOCK]      = 'ClusterEncryptedBlock';
                $EBMLidList[EBML_ID_CLUSTERFRAMENUMBER]         = 'ClusterFrameNumber';
                $EBMLidList[EBML_ID_CLUSTERLACENUMBER]          = 'ClusterLaceNumber';
                $EBMLidList[EBML_ID_CLUSTERPOSITION]            = 'ClusterPosition';
                $EBMLidList[EBML_ID_CLUSTERPREVSIZE]            = 'ClusterPrevSize';
                $EBMLidList[EBML_ID_CLUSTERREFERENCEBLOCK]      = 'ClusterReferenceBlock';
                $EBMLidList[EBML_ID_CLUSTERREFERENCEPRIORITY]   = 'ClusterReferencePriority';
                $EBMLidList[EBML_ID_CLUSTERREFERENCEVIRTUAL]    = 'ClusterReferenceVirtual';
                $EBMLidList[EBML_ID_CLUSTERSILENTTRACKNUMBER]   = 'ClusterSilentTrackNumber';
                $EBMLidList[EBML_ID_CLUSTERSILENTTRACKS]        = 'ClusterSilentTracks';
                $EBMLidList[EBML_ID_CLUSTERSIMPLEBLOCK]         = 'ClusterSimpleBlock';
                $EBMLidList[EBML_ID_CLUSTERTIMECODE]            = 'ClusterTimecode';
                $EBMLidList[EBML_ID_CLUSTERTIMESLICE]           = 'ClusterTimeSlice';
                $EBMLidList[EBML_ID_CODECDECODEALL]             = 'CodecDecodeAll';
                $EBMLidList[EBML_ID_CODECDOWNLOADURL]           = 'CodecDownloadURL';
                $EBMLidList[EBML_ID_CODECID]                    = 'CodecID';
                $EBMLidList[EBML_ID_CODECINFOURL]               = 'CodecInfoURL';
                $EBMLidList[EBML_ID_CODECNAME]                  = 'CodecName';
                $EBMLidList[EBML_ID_CODECPRIVATE]               = 'CodecPrivate';
                $EBMLidList[EBML_ID_CODECSETTINGS]              = 'CodecSettings';
                $EBMLidList[EBML_ID_COLOURSPACE]                = 'ColourSpace';
                $EBMLidList[EBML_ID_CONTENTCOMPALGO]            = 'ContentCompAlgo';
                $EBMLidList[EBML_ID_CONTENTCOMPRESSION]         = 'ContentCompression';
                $EBMLidList[EBML_ID_CONTENTCOMPSETTINGS]        = 'ContentCompSettings';
                $EBMLidList[EBML_ID_CONTENTENCALGO]             = 'ContentEncAlgo';
                $EBMLidList[EBML_ID_CONTENTENCKEYID]            = 'ContentEncKeyID';
                $EBMLidList[EBML_ID_CONTENTENCODING]            = 'ContentEncoding';
                $EBMLidList[EBML_ID_CONTENTENCODINGORDER]       = 'ContentEncodingOrder';
                $EBMLidList[EBML_ID_CONTENTENCODINGS]           = 'ContentEncodings';
                $EBMLidList[EBML_ID_CONTENTENCODINGSCOPE]       = 'ContentEncodingScope';
                $EBMLidList[EBML_ID_CONTENTENCODINGTYPE]        = 'ContentEncodingType';
                $EBMLidList[EBML_ID_CONTENTENCRYPTION]          = 'ContentEncryption';
                $EBMLidList[EBML_ID_CONTENTSIGALGO]             = 'ContentSigAlgo';
                $EBMLidList[EBML_ID_CONTENTSIGHASHALGO]         = 'ContentSigHashAlgo';
                $EBMLidList[EBML_ID_CONTENTSIGKEYID]            = 'ContentSigKeyID';
                $EBMLidList[EBML_ID_CONTENTSIGNATURE]           = 'ContentSignature';
                $EBMLidList[EBML_ID_CRC32]                      = 'CRC32';
                $EBMLidList[EBML_ID_CUEBLOCKNUMBER]             = 'CueBlockNumber';
                $EBMLidList[EBML_ID_CUECLUSTERPOSITION]         = 'CueClusterPosition';
                $EBMLidList[EBML_ID_CUECODECSTATE]              = 'CueCodecState';
                $EBMLidList[EBML_ID_CUEPOINT]                   = 'CuePoint';
                $EBMLidList[EBML_ID_CUEREFCLUSTER]              = 'CueRefCluster';
                $EBMLidList[EBML_ID_CUEREFCODECSTATE]           = 'CueRefCodecState';
                $EBMLidList[EBML_ID_CUEREFERENCE]               = 'CueReference';
                $EBMLidList[EBML_ID_CUEREFNUMBER]               = 'CueRefNumber';
                $EBMLidList[EBML_ID_CUEREFTIME]                 = 'CueRefTime';
                $EBMLidList[EBML_ID_CUES]                       = 'Cues';
                $EBMLidList[EBML_ID_CUETIME]                    = 'CueTime';
                $EBMLidList[EBML_ID_CUETRACK]                   = 'CueTrack';
                $EBMLidList[EBML_ID_CUETRACKPOSITIONS]          = 'CueTrackPositions';
                $EBMLidList[EBML_ID_DATEUTC]                    = 'DateUTC';
                $EBMLidList[EBML_ID_DEFAULTDURATION]            = 'DefaultDuration';
                $EBMLidList[EBML_ID_DISPLAYHEIGHT]              = 'DisplayHeight';
                $EBMLidList[EBML_ID_DISPLAYUNIT]                = 'DisplayUnit';
                $EBMLidList[EBML_ID_DISPLAYWIDTH]               = 'DisplayWidth';
                $EBMLidList[EBML_ID_DOCTYPE]                    = 'DocType';
                $EBMLidList[EBML_ID_DOCTYPEREADVERSION]         = 'DocTypeReadVersion';
                $EBMLidList[EBML_ID_DOCTYPEVERSION]             = 'DocTypeVersion';
                $EBMLidList[EBML_ID_DURATION]                   = 'Duration';
                $EBMLidList[EBML_ID_EBML]                       = 'EBML';
                $EBMLidList[EBML_ID_EBMLMAXIDLENGTH]            = 'EBMLMaxIDLength';
                $EBMLidList[EBML_ID_EBMLMAXSIZELENGTH]          = 'EBMLMaxSizeLength';
                $EBMLidList[EBML_ID_EBMLREADVERSION]            = 'EBMLReadVersion';
                $EBMLidList[EBML_ID_EBMLVERSION]                = 'EBMLVersion';
                $EBMLidList[EBML_ID_EDITIONENTRY]               = 'EditionEntry';
                $EBMLidList[EBML_ID_EDITIONFLAGDEFAULT]         = 'EditionFlagDefault';
                $EBMLidList[EBML_ID_EDITIONFLAGHIDDEN]          = 'EditionFlagHidden';
                $EBMLidList[EBML_ID_EDITIONFLAGORDERED]         = 'EditionFlagOrdered';
                $EBMLidList[EBML_ID_EDITIONUID]                 = 'EditionUID';
                $EBMLidList[EBML_ID_FILEDATA]                   = 'FileData';
                $EBMLidList[EBML_ID_FILEDESCRIPTION]            = 'FileDescription';
                $EBMLidList[EBML_ID_FILEMIMETYPE]               = 'FileMimeType';
                $EBMLidList[EBML_ID_FILENAME]                   = 'FileName';
                $EBMLidList[EBML_ID_FILEREFERRAL]               = 'FileReferral';
                $EBMLidList[EBML_ID_FILEUID]                    = 'FileUID';
                $EBMLidList[EBML_ID_FLAGDEFAULT]                = 'FlagDefault';
                $EBMLidList[EBML_ID_FLAGENABLED]                = 'FlagEnabled';
                $EBMLidList[EBML_ID_FLAGFORCED]                 = 'FlagForced';
                $EBMLidList[EBML_ID_FLAGINTERLACED]             = 'FlagInterlaced';
                $EBMLidList[EBML_ID_FLAGLACING]                 = 'FlagLacing';
                $EBMLidList[EBML_ID_GAMMAVALUE]                 = 'GammaValue';
                $EBMLidList[EBML_ID_INFO]                       = 'Info';
                $EBMLidList[EBML_ID_LANGUAGE]                   = 'Language';
                $EBMLidList[EBML_ID_MAXBLOCKADDITIONID]         = 'MaxBlockAdditionID';
                $EBMLidList[EBML_ID_MAXCACHE]                   = 'MaxCache';
                $EBMLidList[EBML_ID_MINCACHE]                   = 'MinCache';
                $EBMLidList[EBML_ID_MUXINGAPP]                  = 'MuxingApp';
                $EBMLidList[EBML_ID_NAME]                       = 'Name';
                $EBMLidList[EBML_ID_NEXTFILENAME]               = 'NextFilename';
                $EBMLidList[EBML_ID_NEXTUID]                    = 'NextUID';
                $EBMLidList[EBML_ID_OUTPUTSAMPLINGFREQUENCY]    = 'OutputSamplingFrequency';
                $EBMLidList[EBML_ID_PIXELCROPBOTTOM]            = 'PixelCropBottom';
                $EBMLidList[EBML_ID_PIXELCROPLEFT]              = 'PixelCropLeft';
                $EBMLidList[EBML_ID_PIXELCROPRIGHT]             = 'PixelCropRight';
                $EBMLidList[EBML_ID_PIXELCROPTOP]               = 'PixelCropTop';
                $EBMLidList[EBML_ID_PIXELHEIGHT]                = 'PixelHeight';
                $EBMLidList[EBML_ID_PIXELWIDTH]                 = 'PixelWidth';
                $EBMLidList[EBML_ID_PREVFILENAME]               = 'PrevFilename';
                $EBMLidList[EBML_ID_PREVUID]                    = 'PrevUID';
                $EBMLidList[EBML_ID_SAMPLINGFREQUENCY]          = 'SamplingFrequency';
                $EBMLidList[EBML_ID_SEEK]                       = 'Seek';
                $EBMLidList[EBML_ID_SEEKHEAD]                   = 'SeekHead';
                $EBMLidList[EBML_ID_SEEKID]                     = 'SeekID';
                $EBMLidList[EBML_ID_SEEKPOSITION]               = 'SeekPosition';
                $EBMLidList[EBML_ID_SEGMENT]                    = 'Segment';
                $EBMLidList[EBML_ID_SEGMENTFAMILY]              = 'SegmentFamily';
                $EBMLidList[EBML_ID_SEGMENTFILENAME]            = 'SegmentFilename';
                $EBMLidList[EBML_ID_SEGMENTUID]                 = 'SegmentUID';
                $EBMLidList[EBML_ID_SIMPLETAG]                  = 'SimpleTag';
                $EBMLidList[EBML_ID_CLUSTERSLICES]              = 'ClusterSlices';
                $EBMLidList[EBML_ID_STEREOMODE]                 = 'StereoMode';
                $EBMLidList[EBML_ID_OLDSTEREOMODE]              = 'OldStereoMode';
                $EBMLidList[EBML_ID_TAG]                        = 'Tag';
                $EBMLidList[EBML_ID_TAGATTACHMENTUID]           = 'TagAttachmentUID';
                $EBMLidList[EBML_ID_TAGBINARY]                  = 'TagBinary';
                $EBMLidList[EBML_ID_TAGCHAPTERUID]              = 'TagChapterUID';
                $EBMLidList[EBML_ID_TAGDEFAULT]                 = 'TagDefault';
                $EBMLidList[EBML_ID_TAGEDITIONUID]              = 'TagEditionUID';
                $EBMLidList[EBML_ID_TAGLANGUAGE]                = 'TagLanguage';
                $EBMLidList[EBML_ID_TAGNAME]                    = 'TagName';
                $EBMLidList[EBML_ID_TAGTRACKUID]                = 'TagTrackUID';
                $EBMLidList[EBML_ID_TAGS]                       = 'Tags';
                $EBMLidList[EBML_ID_TAGSTRING]                  = 'TagString';
                $EBMLidList[EBML_ID_TARGETS]                    = 'Targets';
                $EBMLidList[EBML_ID_TARGETTYPE]                 = 'TargetType';
                $EBMLidList[EBML_ID_TARGETTYPEVALUE]            = 'TargetTypeValue';
                $EBMLidList[EBML_ID_TIMECODESCALE]              = 'TimecodeScale';
                $EBMLidList[EBML_ID_TITLE]                      = 'Title';
                $EBMLidList[EBML_ID_TRACKENTRY]                 = 'TrackEntry';
                $EBMLidList[EBML_ID_TRACKNUMBER]                = 'TrackNumber';
                $EBMLidList[EBML_ID_TRACKOFFSET]                = 'TrackOffset';
                $EBMLidList[EBML_ID_TRACKOVERLAY]               = 'TrackOverlay';
                $EBMLidList[EBML_ID_TRACKS]                     = 'Tracks';
                $EBMLidList[EBML_ID_TRACKTIMECODESCALE]         = 'TrackTimecodeScale';
                $EBMLidList[EBML_ID_TRACKTRANSLATE]             = 'TrackTranslate';
                $EBMLidList[EBML_ID_TRACKTRANSLATECODEC]        = 'TrackTranslateCodec';
                $EBMLidList[EBML_ID_TRACKTRANSLATEEDITIONUID]   = 'TrackTranslateEditionUID';
                $EBMLidList[EBML_ID_TRACKTRANSLATETRACKID]      = 'TrackTranslateTrackID';
                $EBMLidList[EBML_ID_TRACKTYPE]                  = 'TrackType';
                $EBMLidList[EBML_ID_TRACKUID]                   = 'TrackUID';
                $EBMLidList[EBML_ID_VIDEO]                      = 'Video';
                $EBMLidList[EBML_ID_VOID]                       = 'Void';
                $EBMLidList[EBML_ID_WRITINGAPP]                 = 'WritingApp';
            }
            return ($EBMLidList[$value] ?? dechex($value));
        }//1681
        public static function displayUnit($value):string {
            // http://www.matroska.org/technical/specs/index.html#DisplayUnit
            static $units = array(
                0 => 'pixels',
                1 => 'centimeters',
                2 => 'inches',
                3 => 'Display Aspect Ratio');
            return ($units[$value] ?? 'unknown');
        }//1884
        private static function getDefaultStreamInfo($streams):array{
            $stream = array();
            foreach (array_reverse($streams) as $stream) {
                if ($stream['default']) {
                    break;
                }
            }
            $unset = array('default', 'name');
            foreach ($unset as $u) {
                if (isset($stream[$u])) {
                    unset($stream[$u]);
                }
            }
            $info = $stream;
            $info['streams'] = $streams;
            return $info;
        }//1900
    }
}else{die;}