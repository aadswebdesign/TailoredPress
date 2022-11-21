<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-10-2022
 * Time: 09:46
 */
namespace TP_Core\Libs\ID3\Modules;
use TP_Core\Libs\ID3\getID3;
use TP_Core\Libs\ID3\getid3_lib;
use TP_Core\Libs\ID3\getid3_handler;
if(ABSPATH){
    class getid3_id3v2 extends getid3_handler{
        public $StartingOffset = 0;
        public function Analyze():bool {
            $info = &$this->getid3->info;
           $info['id3v2']['header'] = true;
            $thisfile_id3v2                  = &$info['id3v2'];
            $thisfile_id3v2['flags']         =  array();
            $thisfile_id3v2_flags            = &$thisfile_id3v2['flags'];
            $this->fseek($this->StartingOffset);
            $header = $this->fread(10);
            if (strpos($header, 'ID3') === 0 &&  strlen($header) === 10) {
                $thisfile_id3v2['majorversion'] = ord($header[3]);
                $thisfile_id3v2['minorversion'] = ord($header[4]);
                // shortcut
                $id3v2_majorversion = &$thisfile_id3v2['majorversion'];
            } else {
                unset($info['id3v2']);
                return false;
            }
            if ($id3v2_majorversion > 4) { // this script probably won't correctly parse ID3v2.5.x and above (if it ever exists)
                $this->error('this script only parses up to ID3v2.4.x - this tag is ID3v2.'.$id3v2_majorversion.'.'.$thisfile_id3v2['minorversion']);
                return false;
            }
            $id3_flags = ord($header[5]);
            switch ($id3v2_majorversion) {
                case 2:
                    // %ab000000 in v2.2
                    $thisfile_id3v2_flags['unsynch']     = (bool) ($id3_flags & 0x80); // a - Unsynchronisation
                    $thisfile_id3v2_flags['compression'] = (bool) ($id3_flags & 0x40); // b - Compression
                    break;
                case 3:
                    // %abc00000 in v2.3
                    $thisfile_id3v2_flags['unsynch']     = (bool) ($id3_flags & 0x80); // a - Unsynchronisation
                    $thisfile_id3v2_flags['exthead']     = (bool) ($id3_flags & 0x40); // b - Extended header
                    $thisfile_id3v2_flags['experim']     = (bool) ($id3_flags & 0x20); // c - Experimental indicator
                    break;
                case 4:
                    // %abcd0000 in v2.4
                    $thisfile_id3v2_flags['unsynch']     = (bool) ($id3_flags & 0x80); // a - Unsynchronisation
                    $thisfile_id3v2_flags['exthead']     = (bool) ($id3_flags & 0x40); // b - Extended header
                    $thisfile_id3v2_flags['experim']     = (bool) ($id3_flags & 0x20); // c - Experimental indicator
                    $thisfile_id3v2_flags['isfooter']    = (bool) ($id3_flags & 0x10); // d - Footer present
                    break;
            }
            $thisfile_id3v2['headerlength'] = getid3_lib::BigEndian2Int(substr($header, 6, 4), 1) + 10; // length of ID3v2 tag in 10-byte header doesn't include 10-byte header length
            $thisfile_id3v2['tag_offset_start'] = $this->StartingOffset;
            $thisfile_id3v2['tag_offset_end']   = $thisfile_id3v2['tag_offset_start'] + $thisfile_id3v2['headerlength'];
            $thisfile_id3v2['encoding'] = 'UTF-8';
            $sizeofframes = $thisfile_id3v2['headerlength'] - 10; // not including 10-byte initial header
            if (!empty($thisfile_id3v2['exthead']['length'])) {
                $sizeofframes -= ($thisfile_id3v2['exthead']['length'] + 4);
            }
            if (!empty($thisfile_id3v2_flags['isfooter'])) {
                $sizeofframes -= 10; // footer takes last 10 bytes of ID3v2 header, after frame data, before audio
            }
            if ($sizeofframes > 0) {
                $framedata = $this->fread($sizeofframes); // read all frames from file into $framedata variable
                //    if entire frame data is unsynched, de-unsynch it now (ID3v2.3.x)
                if (!empty($thisfile_id3v2_flags['unsynch']) && ($id3v2_majorversion <= 3)) {
                    $framedata = $this->DeUnsynchronise($framedata);
                }
                $framedataoffset = 10; // how many bytes into the stream - start from after the 10-byte header
                if (!empty($thisfile_id3v2_flags['exthead'])) {
                    $extended_header_offset = 0;
                    if ($id3v2_majorversion === 3) {
                        $thisfile_id3v2['exthead']['length'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4), 0);
                        $extended_header_offset += 4;
                        $thisfile_id3v2['exthead']['flag_bytes'] = 2;
                        $thisfile_id3v2['exthead']['flag_raw'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, $thisfile_id3v2['exthead']['flag_bytes']));
                        $extended_header_offset += $thisfile_id3v2['exthead']['flag_bytes'];
                        $thisfile_id3v2['exthead']['flags']['crc'] = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x8000);
                        $thisfile_id3v2['exthead']['padding_size'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4));
                        $extended_header_offset += 4;
                        if ($thisfile_id3v2['exthead']['flags']['crc']) {
                            $thisfile_id3v2['exthead']['flag_data']['crc'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4));
                            $extended_header_offset += 4;
                        }
                        $extended_header_offset += $thisfile_id3v2['exthead']['padding_size'];
                    } elseif ($id3v2_majorversion === 4) {
                        $thisfile_id3v2['exthead']['length'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, 4), true);
                        $extended_header_offset += 4;
                        $thisfile_id3v2['exthead']['flag_bytes'] = getid3_lib::BigEndian2Int($framedata[$extended_header_offset]); // should always be 1
                        ++$extended_header_offset;
                        $thisfile_id3v2['exthead']['flag_raw'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, $thisfile_id3v2['exthead']['flag_bytes']));
                        $extended_header_offset += $thisfile_id3v2['exthead']['flag_bytes'];
                        $thisfile_id3v2['exthead']['flags']['update']       = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x40);
                        $thisfile_id3v2['exthead']['flags']['crc']          = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x20);
                        $thisfile_id3v2['exthead']['flags']['restrictions'] = (bool) ($thisfile_id3v2['exthead']['flag_raw'] & 0x10);
                        if ($thisfile_id3v2['exthead']['flags']['update']) {
                            getid3_lib::BigEndian2Int($framedata[$extended_header_offset]); // should be 0
                            ++$extended_header_offset;
                        }
                        if ($thisfile_id3v2['exthead']['flags']['crc']) {
                            $ext_header_chunk_length = getid3_lib::BigEndian2Int($framedata[$extended_header_offset]); // should be 5
                            ++$extended_header_offset;
                            $thisfile_id3v2['exthead']['flag_data']['crc'] = getid3_lib::BigEndian2Int(substr($framedata, $extended_header_offset, $ext_header_chunk_length), true, false);
                            $extended_header_offset += $ext_header_chunk_length;
                        }
                        if ($thisfile_id3v2['exthead']['flags']['restrictions']) {
                            $ext_header_chunk_length = getid3_lib::BigEndian2Int($framedata[$extended_header_offset]); // should be 1
                            ++$extended_header_offset;
                            // %ppqrrstt
                            $restrictions_raw = $ext_header_chunk_length;
                            ++$extended_header_offset;
                            $thisfile_id3v2['exthead']['flags']['restrictions']['tagsize']  = ($restrictions_raw & 0xC0) >> 6; // p - Tag size restrictions
                            $thisfile_id3v2['exthead']['flags']['restrictions']['textenc']  = ($restrictions_raw & 0x20) >> 5; // q - Text encoding restrictions
                            $thisfile_id3v2['exthead']['flags']['restrictions']['textsize'] = ($restrictions_raw & 0x18) >> 3; // r - Text fields size restrictions
                            $thisfile_id3v2['exthead']['flags']['restrictions']['imgenc']   = ($restrictions_raw & 0x04) >> 2; // s - Image encoding restrictions
                            $thisfile_id3v2['exthead']['flags']['restrictions']['imgsize']  = ($restrictions_raw & 0x03) >> 0; // t - Image size restrictions

                            $thisfile_id3v2['exthead']['flags']['restrictions_text']['tagsize']  = $this->LookupExtendedHeaderRestrictionsTagSizeLimits($thisfile_id3v2['exthead']['flags']['restrictions']['tagsize']);
                            $thisfile_id3v2['exthead']['flags']['restrictions_text']['textenc']  = $this->LookupExtendedHeaderRestrictionsTextEncodings($thisfile_id3v2['exthead']['flags']['restrictions']['textenc']);
                            $thisfile_id3v2['exthead']['flags']['restrictions_text']['textsize'] = $this->LookupExtendedHeaderRestrictionsTextFieldSize($thisfile_id3v2['exthead']['flags']['restrictions']['textsize']);
                            $thisfile_id3v2['exthead']['flags']['restrictions_text']['imgenc']   = $this->LookupExtendedHeaderRestrictionsImageEncoding($thisfile_id3v2['exthead']['flags']['restrictions']['imgenc']);
                            $thisfile_id3v2['exthead']['flags']['restrictions_text']['imgsize']  = $this->LookupExtendedHeaderRestrictionsImageSizeSize($thisfile_id3v2['exthead']['flags']['restrictions']['imgsize']);
                        }
                        if ($thisfile_id3v2['exthead']['length'] !== $extended_header_offset) {
                            $this->warning('ID3v2.4 extended header length mismatch (expecting '. (int)$thisfile_id3v2['exthead']['length'] .', found '. (int)$extended_header_offset .')');
                        }
                    }
                    $framedataoffset += $extended_header_offset;
                    $framedata = substr($framedata, $extended_header_offset);
                } // end extended header
                while (isset($framedata) && ($framedata !== '')) { // cycle through until no more frame data is left to parse
                    if (strlen($framedata) <= self::ID3v2HeaderLength($id3v2_majorversion)) {
                        // insufficient room left in ID3v2 header for actual data - must be padding
                        $thisfile_id3v2['padding']['start']  = $framedataoffset;
                        $thisfile_id3v2['padding']['length'] = strlen($framedata);
                        $thisfile_id3v2['padding']['valid']  = true;
                        for ($i = 0; $i < $thisfile_id3v2['padding']['length']; $i++) {
                            if ($framedata[$i] !== "\x00") {
                                $thisfile_id3v2['padding']['valid'] = false;
                                $thisfile_id3v2['padding']['errorpos'] = $thisfile_id3v2['padding']['start'] + $i;
                                $this->warning('Invalid ID3v2 padding found at offset '.$thisfile_id3v2['padding']['errorpos'].' (the remaining '.($thisfile_id3v2['padding']['length'] - $i).' bytes are considered invalid)');
                                break;
                            }
                        }
                        break; // skip rest of ID3v2 header
                    }
                    $frame_header = null;
                    $frame_name   = null;
                    $frame_size   = null;
                    $frame_flags  = null;
                    if ($id3v2_majorversion === 2) {
                        $frame_header = substr($framedata, 0, 6); // take next 6 bytes for header
                        $framedata    = substr($framedata, 6);    // and leave the rest in $framedata
                        $frame_name   = substr($frame_header, 0, 3);
                        $frame_size   = getid3_lib::BigEndian2Int(substr($frame_header, 3, 3), 0);
                        $frame_flags  = 0; // not used for anything in ID3v2.2, just set to avoid E_NOTICEs

                    } elseif ($id3v2_majorversion > 2) {
                        $frame_header = substr($framedata, 0, 10); // take next 10 bytes for header
                        $framedata    = substr($framedata, 10);    // and leave the rest in $framedata

                        $frame_name = substr($frame_header, 0, 4);
                        if ($id3v2_majorversion === 3) {
                            $frame_size = getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 0); // 32-bit integer
                        } else { // ID3v2.4+
                            $frame_size = getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 1); // 32-bit synchsafe integer (28-bit value)
                        }
                        if ($frame_size < (strlen($framedata) + 4)) {
                            $nextFrameID = substr($framedata, $frame_size, 4);
                            if (self::IsValidID3v2FrameName($nextFrameID, $id3v2_majorversion)) {
                                // next frame is OK
                            } elseif (($frame_name === "\x00".'MP3') || ($frame_name === "\x00\x00".'MP') || ($frame_name === ' MP3') || ($frame_name === 'MP3e')) {
                                // MP3ext known broken frames - "ok" for the purposes of this test
                            } elseif (($id3v2_majorversion === 4) && (self::IsValidID3v2FrameName(substr($framedata, getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 0), 4), 3))) {
                                $this->warning('ID3v2 tag written as ID3v2.4, but with non-synchsafe integers (ID3v2.3 style). Older versions of (Helium2; iTunes) are known culprits of this. Tag has been parsed as ID3v2.3');
                                $id3v2_majorversion = 3;
                                $frame_size = getid3_lib::BigEndian2Int(substr($frame_header, 4, 4), 0); // 32-bit integer
                            }
                        }
                        $frame_flags = getid3_lib::BigEndian2Int(substr($frame_header, 8, 2));
                    }

                    if ((($id3v2_majorversion === 2) && ($frame_name === "\x00\x00\x00")) || ($frame_name === "\x00\x00\x00\x00")) {
                        $thisfile_id3v2['padding']['start']  = $framedataoffset;
                        $thisfile_id3v2['padding']['length'] = strlen($frame_header) + strlen($framedata);
                        $thisfile_id3v2['padding']['valid']  = true;

                        $len = strlen($framedata);
                        for ($i = 0; $i < $len; $i++) {
                            if ($framedata[$i] !== "\x00") {
                                $thisfile_id3v2['padding']['valid'] = false;
                                $thisfile_id3v2['padding']['errorpos'] = $thisfile_id3v2['padding']['start'] + $i;
                                $this->warning('Invalid ID3v2 padding found at offset '.$thisfile_id3v2['padding']['errorpos'].' (the remaining '.($thisfile_id3v2['padding']['length'] - $i).' bytes are considered invalid)');
                                break;
                            }
                        }
                        break; // skip rest of ID3v2 header
                    }

                    if ($iTunesBrokenFrameNameFixed = self::ID3v22iTunesBrokenFrameName($frame_name)) {
                        $this->warning('error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: IsValidID3v2FrameName("'.str_replace("\x00", ' ', $frame_name).'", '.$id3v2_majorversion.'))). [Note: this particular error has been known to happen with tags edited by iTunes (versions "X v2.0.3", "v3.0.1", "v7.0.0.70" are known-guilty, probably others too)]. Translated frame name from "'.str_replace("\x00", ' ', $frame_name).'" to "'.$iTunesBrokenFrameNameFixed.'" for parsing.');
                        $frame_name = $iTunesBrokenFrameNameFixed;
                    }
                    if (($frame_size <= strlen($framedata)) && (self::IsValidID3v2FrameName($frame_name, $id3v2_majorversion))) {
                        $parsedFrame                    = array();
                        $parsedFrame['frame_name']      = $frame_name;
                        $parsedFrame['frame_flags_raw'] = $frame_flags;
                        $parsedFrame['data']            = substr($framedata, 0, $frame_size);
                        $parsedFrame['datalength']      = getid3_lib::CastAsInt($frame_size);
                        $parsedFrame['dataoffset']      = $framedataoffset;

                        $this->ParseID3v2Frame($parsedFrame);
                        $thisfile_id3v2[$frame_name][] = $parsedFrame;
                        $framedata = substr($framedata, $frame_size);
                    } else { // invalid frame length or FrameID
                        if ($frame_size <= strlen($framedata)) {
                            if (self::IsValidID3v2FrameName(substr($framedata, $frame_size, 4), $id3v2_majorversion)) {
                                // next frame is valid, just skip the current frame
                                $framedata = substr($framedata, $frame_size);
                                $this->warning('Next ID3v2 frame is valid, skipping current frame.');
                            } else {
                                $framedata = null;
                                $this->error('Next ID3v2 frame is also invalid, aborting processing.');
                            }
                        } elseif ($frame_size === strlen($framedata)) {
                            $this->warning('This was the last ID3v2 frame.');
                        } else {
                            $framedata = null;
                            $this->warning('Invalid ID3v2 frame size, aborting.');
                        }
                        if (!self::IsValidID3v2FrameName($frame_name, $id3v2_majorversion)) {
                            switch ($frame_name) {
                                case "\x00\x00".'MP':
                                case "\x00".'MP3':
                                case ' MP3':
                                case 'MP3e':
                                case "\x00".'MP':
                                case ' MP':
                                case 'MP3':
                                    $this->warning('error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: !IsValidID3v2FrameName("'.str_replace("\x00", ' ', $frame_name).'", '.$id3v2_majorversion.'))). [Note: this particular error has been known to happen with tags edited by "MP3ext (www.mutschler.de/mp3ext/)"]');
                                    break;
                                default:
                                    $this->warning('error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: !IsValidID3v2FrameName("'.str_replace("\x00", ' ', $frame_name).'", '.$id3v2_majorversion.'))).');
                                    break;
                            }
                        } elseif (!isset($framedata) || ($frame_size > strlen($framedata))) {
                            $this->error('error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag). (ERROR: $frame_size ('.$frame_size.') > strlen($framedata) ('.(isset($framedata) ? strlen($framedata) : 'null').')).');
                        } else {
                            $this->error('error parsing "'.$frame_name.'" ('.$framedataoffset.' bytes into the ID3v2.'.$id3v2_majorversion.' tag).');
                        }
                    }
                    $framedataoffset += ($frame_size + self::ID3v2HeaderLength($id3v2_majorversion));
                }
            }
            if (isset($thisfile_id3v2_flags['isfooter']) && $thisfile_id3v2_flags['isfooter']) {
                $footer = $this->fread(10);
                if (strpos($footer, '3DI') === 0) {
                    $thisfile_id3v2['footer'] = true;
                    $thisfile_id3v2['majorversion_footer'] = ord($footer[3]);
                    $thisfile_id3v2['minorversion_footer'] = ord($footer[4]);
                }
                if ($thisfile_id3v2['majorversion_footer'] <= 4) {
                    $id3_flags = ord($footer[5]);
                    $thisfile_id3v2_flags['unsynch_footer']  = (bool) ($id3_flags & 0x80);
                    $thisfile_id3v2_flags['extfoot_footer']  = (bool) ($id3_flags & 0x40);
                    $thisfile_id3v2_flags['experim_footer']  = (bool) ($id3_flags & 0x20);
                    $thisfile_id3v2_flags['isfooter_footer'] = (bool) ($id3_flags & 0x10);
                    $thisfile_id3v2['footerlength'] = getid3_lib::BigEndian2Int(substr($footer, 6, 4), 1);
                }
            } // end footer
            if (isset($thisfile_id3v2['comments']['genre'])) {
                $genres = array();
                foreach ($thisfile_id3v2['comments']['genre'] as $key => $value) {
                    foreach ($this->ParseID3v2GenreString($value) as $genre) {
                        $genres[] = $genre;
                    }
                }
                $thisfile_id3v2['comments']['genre'] = array_unique($genres);
                unset($key, $value, $genres, $genre);
            }
            if (isset($thisfile_id3v2['comments']['track_number'])) {
                foreach ($thisfile_id3v2['comments']['track_number'] as $key => $value) {
                    if (strpos($value, '/') !== false) {
                        @list($thisfile_id3v2['comments']['track_number'][$key], $thisfile_id3v2['comments']['totaltracks'][$key]) = explode('/', $thisfile_id3v2['comments']['track_number'][$key]);
                    }
                }
            }
            if (!isset($thisfile_id3v2['comments']['year']) && !empty($thisfile_id3v2['comments']['recording_time'][0]) && preg_match('#^(\d{4})#', trim($thisfile_id3v2['comments']['recording_time'][0]), $matches)) {
                $thisfile_id3v2['comments']['year'] = array($matches[1]);
            }
            if (!empty($thisfile_id3v2['TXXX'])) {
                // MediaMonkey does this, maybe others: write a blank RGAD frame, but put replay-gain adjustment values in TXXX frames
                foreach ($thisfile_id3v2['TXXX'] as $txxx_array) {
                    switch ($txxx_array['description']) {
                        case 'replaygain_track_gain':
                            if (empty($info['replay_gain']['track']['adjustment']) && !empty($txxx_array['data'])) {
                                $info['replay_gain']['track']['adjustment'] = (float)trim(str_replace('dB', '', $txxx_array['data']));
                            }
                            break;
                        case 'replaygain_track_peak':
                            if (empty($info['replay_gain']['track']['peak']) && !empty($txxx_array['data'])) {
                                $info['replay_gain']['track']['peak'] = (float)$txxx_array['data'];
                            }
                            break;
                        case 'replaygain_album_gain':
                            if (empty($info['replay_gain']['album']['adjustment']) && !empty($txxx_array['data'])) {
                                $info['replay_gain']['album']['adjustment'] = (float)trim(str_replace('dB', '', $txxx_array['data']));
                            }
                            break;
                    }
                }
            }
            $info['avdataoffset'] = $thisfile_id3v2['headerlength'];
            if (isset($thisfile_id3v2['footer'])) {
                $info['avdataoffset'] += 10;
            }
            return true;
        }
        public function ParseID3v2GenreString($genrestring):array {
            $clean_genres = array();
            if (($this->getid3->info['id3v2']['majorversion'] === 3) && !preg_match('#[\x00]#', $genrestring)) {
                if (strpos($genrestring, '/') !== false) {
                    $LegitimateSlashedGenreList = array(  // https://github.com/JamesHeinrich/getID3/issues/223
                        'Pop/Funk',    // ID3v1 genre #62 - https://en.wikipedia.org/wiki/ID3#standard
                        'Cut-up/DJ',   // Discogs - https://www.discogs.com/style/cut-up/dj
                        'RnB/Swing',   // Discogs - https://www.discogs.com/style/rnb/swing
                        'Funk / Soul', // Discogs (note spaces) - https://www.discogs.com/genre/funk+%2F+soul
                    );
                    $genrestring = str_replace('/', "\x00", $genrestring);
                    foreach ($LegitimateSlashedGenreList as $SlashedGenre) {
                        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                        $genrestring = str_ireplace(str_replace('/', "\x00", $SlashedGenre), $SlashedGenre, $genrestring);
                    }
                }
                if (strpos($genrestring, ';') !== false) {
                    $genrestring = str_replace(';', "\x00", $genrestring);
                }
            }
            if (strpos($genrestring, "\x00") === false) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $genrestring = preg_replace('#\((\d{1,3})\)#', '$1'."\x00", $genrestring);
            }
            $genre_elements = explode("\x00", $genrestring);
            foreach ($genre_elements as $element) {
                $element = trim($element);
                if ($element) {
                    if (preg_match('#^\d{1,3}$#', $element)) {
                        $clean_genres[] = getid3_id3v1::LookupGenreName($element);
                    } else {
                        $clean_genres[] = str_replace('((', '(', $element);
                    }
                }
            }
            return $clean_genres;
        }
        public function ParseID3v2Frame(&$parsedFrame):bool {
            $info = &$this->getid3->info;
            $id3v2_majorversion = $info['id3v2']['majorversion'];
            $parsedFrame['framenamelong']  = self::FrameNameLongLookup($parsedFrame['frame_name']);
            if (empty($parsedFrame['framenamelong'])) {
                unset($parsedFrame['framenamelong']);
            }
            $parsedFrame['framenameshort'] = self::FrameNameShortLookup($parsedFrame['frame_name']);
            if (empty($parsedFrame['framenameshort'])) {
                unset($parsedFrame['framenameshort']);
            }
            if ($id3v2_majorversion >= 3) { // frame flags are not part of the ID3v2.2 standard
                if ($id3v2_majorversion === 3) {
                    //    Frame Header Flags
                    //    %abc00000 %ijk00000
                    $parsedFrame['flags']['TagAlterPreservation']  = (bool) ($parsedFrame['frame_flags_raw'] & 0x8000); // a - Tag alter preservation
                    $parsedFrame['flags']['FileAlterPreservation'] = (bool) ($parsedFrame['frame_flags_raw'] & 0x4000); // b - File alter preservation
                    $parsedFrame['flags']['ReadOnly']              = (bool) ($parsedFrame['frame_flags_raw'] & 0x2000); // c - Read only
                    $parsedFrame['flags']['compression']           = (bool) ($parsedFrame['frame_flags_raw'] & 0x0080); // i - Compression
                    $parsedFrame['flags']['Encryption']            = (bool) ($parsedFrame['frame_flags_raw'] & 0x0040); // j - Encryption
                    $parsedFrame['flags']['GroupingIdentity']      = (bool) ($parsedFrame['frame_flags_raw'] & 0x0020); // k - Grouping identity
                } elseif ($id3v2_majorversion === 4) {
                    //    Frame Header Flags
                    //    %0abc0000 %0h00kmnp
                    $parsedFrame['flags']['TagAlterPreservation']  = (bool) ($parsedFrame['frame_flags_raw'] & 0x4000); // a - Tag alter preservation
                    $parsedFrame['flags']['FileAlterPreservation'] = (bool) ($parsedFrame['frame_flags_raw'] & 0x2000); // b - File alter preservation
                    $parsedFrame['flags']['ReadOnly']              = (bool) ($parsedFrame['frame_flags_raw'] & 0x1000); // c - Read only
                    $parsedFrame['flags']['GroupingIdentity']      = (bool) ($parsedFrame['frame_flags_raw'] & 0x0040); // h - Grouping identity
                    $parsedFrame['flags']['compression']           = (bool) ($parsedFrame['frame_flags_raw'] & 0x0008); // k - Compression
                    $parsedFrame['flags']['Encryption']            = (bool) ($parsedFrame['frame_flags_raw'] & 0x0004); // m - Encryption
                    $parsedFrame['flags']['Unsynchronisation']     = (bool) ($parsedFrame['frame_flags_raw'] & 0x0002); // n - Unsynchronisation
                    $parsedFrame['flags']['DataLengthIndicator']   = (bool) ($parsedFrame['frame_flags_raw'] & 0x0001); // p - Data length indicator
                    if ($parsedFrame['flags']['Unsynchronisation']) {
                        $parsedFrame['data'] = $this->DeUnsynchronise($parsedFrame['data']);
                    }
                    if ($parsedFrame['flags']['DataLengthIndicator']) {
                        $parsedFrame['data_length_indicator'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 0, 4), 1);
                        $parsedFrame['data']                  =                           substr($parsedFrame['data'], 4);
                    }
                }
                if ($parsedFrame['flags']['compression']) {
                    $parsedFrame['decompressed_size'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 0, 4));
                    if (!function_exists('gzuncompress')) {
                        $this->warning('gzuncompress() support required to decompress ID3v2 frame "'.$parsedFrame['frame_name'].'"');
                    } else if ($decompresseddata = @gzuncompress(substr($parsedFrame['data'], 4))) {
                        $parsedFrame['data'] = $decompresseddata;
                        unset($decompresseddata);
                    } else {
                        $this->warning('gzuncompress() failed on compressed contents of ID3v2 frame "'.$parsedFrame['frame_name'].'"');
                    }
                }
            }
            if (!empty($parsedFrame['flags']['DataLengthIndicator']) && $parsedFrame['data_length_indicator'] !== strlen($parsedFrame['data'])) {
                $this->warning('ID3v2 frame "'.$parsedFrame['frame_name'].'" should be '.$parsedFrame['data_length_indicator'].' bytes long according to DataLengthIndicator, but found '.strlen($parsedFrame['data']).' bytes of data');
            }
            if (isset($parsedFrame['datalength']) && ($parsedFrame['datalength'] === 0)) {
                $warning = 'Frame "'.$parsedFrame['frame_name'].'" at offset '.$parsedFrame['dataoffset'].' has no data portion';
                if ($parsedFrame['frame_name'] === 'WCOM') {
                    $warning .= ' (this is known to happen with files tagged by RioPort)';
                } else {}
                $this->warning($warning);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'UFID')) || // 4.1   UFID Unique file identifier
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'UFI'))) {  // 4.1   UFI  Unique file identifier
                $exploded = explode("\x00", $parsedFrame['data'], 2);
                $parsedFrame['ownerid'] = ($exploded[0] ?? '');
                $parsedFrame['data']    = ($exploded[1] ?? '');
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'TXXX')) || // 4.2.2 TXXX User defined text information frame
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'TXX'))) {    // 4.2.2 TXX  User defined text information frame
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                    $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                }
                $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                $parsedFrame['encodingid']  = $frame_textencoding;
                $parsedFrame['encoding']    = self::TextEncodingNameLookup($frame_textencoding);
                $parsedFrame['description'] = trim(getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['description']));
                $parsedFrame['data'] = substr($parsedFrame['data'], $frame_terminatorpos + strlen($frame_textencoding_terminator));
                $parsedFrame['data'] = self::RemoveStringTerminator($parsedFrame['data'], $frame_textencoding_terminator);
                if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                    $commentkey = ($parsedFrame['description'] ?: (count($info['id3v2']['comments'][$parsedFrame['framenameshort']]) ?? 0));
                    if (!isset($info['id3v2']['comments'][$parsedFrame['framenameshort']]) || !array_key_exists($commentkey, $info['id3v2']['comments'][$parsedFrame['framenameshort']])) {
                        $info['id3v2']['comments'][$parsedFrame['framenameshort']][$commentkey] = trim(getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']));
                    } else {
                        $info['id3v2']['comments'][$parsedFrame['framenameshort']][]            = trim(getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']));
                    }
                }
            } elseif ($parsedFrame['frame_name'][0] === 'T') { // 4.2. T??[?] Text information frame
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                }
                $parsedFrame['data'] = (string) substr($parsedFrame['data'], $frame_offset);
                $parsedFrame['data'] = self::RemoveStringTerminator($parsedFrame['data'], self::TextEncodingTerminatorLookup($frame_textencoding));
                $parsedFrame['encodingid'] = $frame_textencoding;
                $parsedFrame['encoding']   = self::TextEncodingNameLookup($frame_textencoding);
                if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                    switch ($parsedFrame['encoding']) {
                        case 'UTF-16':
                        case 'UTF-16BE':
                        case 'UTF-16LE':
                            $wordsize = 2;
                            break;
                        case 'ISO-8859-1':
                        case 'UTF-8':
                        default:
                            $wordsize = 1;
                            break;
                    }
                    $Txxx_elements = array();
                    $Txxx_elements_start_offset = 0;
                    for ($i = 0, $iMax = strlen($parsedFrame['data']); $i < $iMax; $i += $wordsize) {
                        if (substr($parsedFrame['data'], $i, $wordsize) === str_repeat("\x00", $wordsize)) {
                            $Txxx_elements[] = substr($parsedFrame['data'], $Txxx_elements_start_offset, $i - $Txxx_elements_start_offset);
                            $Txxx_elements_start_offset = $i + $wordsize;
                        }
                    }
                    $Txxx_elements[] = substr($parsedFrame['data'], $Txxx_elements_start_offset, $i - $Txxx_elements_start_offset);
                    foreach ($Txxx_elements as $Txxx_element) {
                        $string = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $Txxx_element);
                        if (!empty($string)) {
                            $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $string;
                        }
                    }
                    unset($string, $wordsize, $i, $Txxx_elements, $Txxx_elements_start_offset);
                }
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'WXXX')) || // 4.3.2 WXXX User defined URL link frame
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'WXX'))) {    // 4.3.2 WXX  User defined URL link frame
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                    $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                }
                $parsedFrame['encodingid']  = $frame_textencoding;
                $parsedFrame['encoding']    = self::TextEncodingNameLookup($frame_textencoding);
                $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);           // according to the frame text encoding
                $parsedFrame['url']         = substr($parsedFrame['data'], $frame_terminatorpos + strlen($frame_textencoding_terminator)); // always ISO-8859-1
                $parsedFrame['description'] = self::RemoveStringTerminator($parsedFrame['description'], $frame_textencoding_terminator);
                $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                if (!empty($parsedFrame['framenameshort']) && $parsedFrame['url']) {
                    $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback('ISO-8859-1', $info['id3v2']['encoding'], $parsedFrame['url']);
                }
                unset($parsedFrame['data']);
            } elseif ($parsedFrame['frame_name'][0] === 'W') { // 4.3. W??? URL link frames
                $parsedFrame['url'] = trim($parsedFrame['data']); // always ISO-8859-1
                if (!empty($parsedFrame['framenameshort']) && $parsedFrame['url']) {
                    $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback('ISO-8859-1', $info['id3v2']['encoding'], $parsedFrame['url']);
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion === 3) && ($parsedFrame['frame_name'] === 'IPLS')) || // 4.4  IPLS Involved people list (ID3v2.3 only)
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'IPL'))) {     // 4.4  IPL  Involved people list (ID3v2.2 only)
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                }
                $parsedFrame['encodingid'] = $frame_textencoding;
                $parsedFrame['encoding']   = self::TextEncodingNameLookup($parsedFrame['encodingid']);
                $parsedFrame['data_raw']   = (string) substr($parsedFrame['data'], $frame_offset);
                $IPLS_parts = array();
                if (strpos($parsedFrame['data_raw'], "\x00") !== false) {
                    $IPLS_parts_unsorted = array();
                    if (((strlen($parsedFrame['data_raw']) % 2) === 0) && ((strpos($parsedFrame['data_raw'], "\xFF\xFE") === 0) || (strpos($parsedFrame['data_raw'], "\xFE\xFF") === 0))) {
                        // UTF-16, be careful looking for null bytes since most 2-byte characters may contain one; you need to find twin null bytes, and on even padding
                        $thisILPS  = '';
                        for ($i = 0, $iMax = strlen($parsedFrame['data_raw']); $i < $iMax; $i += 2) {
                            $twobytes = substr($parsedFrame['data_raw'], $i, 2);
                            if ($twobytes === "\x00\x00") {
                                $IPLS_parts_unsorted[] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $thisILPS);
                                $thisILPS  = '';
                            } else {
                                $thisILPS .= $twobytes;
                            }
                        }
                        if (strlen($thisILPS) > 2) { // 2-byte BOM
                            $IPLS_parts_unsorted[] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $thisILPS);
                        }
                    } else {
                        // ISO-8859-1 or UTF-8 or other single-byte-null character set
                        $IPLS_parts_unsorted = explode("\x00", $parsedFrame['data_raw']);
                    }
                    if (count($IPLS_parts_unsorted) === 1) {
                        // just a list of names, e.g. "Dino Baptiste, Jimmy Copley, John Gordon, Bernie Marsden, Sharon Watson"
                        foreach ($IPLS_parts_unsorted as $key => $value) {
                            $IPLS_parts_sorted = preg_split('#[;,\\r\\n\\t]#', $value);
                            $position = '';
                            foreach ($IPLS_parts_sorted as $person) {
                                $IPLS_parts[] = array('position'=>$position, 'person'=>$person);
                            }
                        }
                    } elseif ((count($IPLS_parts_unsorted) % 2) === 0) {
                        $position = '';
                        $person   = null;
                        foreach ($IPLS_parts_unsorted as $key => $value) {
                            if (($key % 2) === 0) {
                                $position = $value;
                            } else {
                                $person   = $value;
                                $IPLS_parts[] = array('position'=>$position, 'person'=>$person);
                                $position = '';
                                //$person   = '';
                            }
                        }
                    } else {
                        foreach ($IPLS_parts_unsorted as $key => $value) {
                            $IPLS_parts[] = array($value);
                        }
                    }
                } else {
                    $IPLS_parts = preg_split('#[;,\\r\\n\\t]#', $parsedFrame['data_raw']);
                }
                $parsedFrame['data'] = $IPLS_parts;
                if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                    $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $parsedFrame['data'];
                }
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'MCDI')) || // 4.4   MCDI Music CD identifier
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'MCI'))) {     // 4.5   MCI  Music CD identifier
                if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                    $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = $parsedFrame['data'];
                }
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'ETCO')) || // 4.5   ETCO Event timing codes
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'ETC'))) {     // 4.6   ETC  Event timing codes
                $frame_offset = 0;
                $parsedFrame['timestampformat'] = ord($parsedFrame['data'][$frame_offset++]);
                while ($frame_offset < strlen($parsedFrame['data'])) {
                    $parsedFrame['typeid']    = $parsedFrame['data'][$frame_offset++];
                    $parsedFrame['type']      = self::ETCOEventLookup($parsedFrame['typeid']);
                    $parsedFrame['timestamp'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                    $frame_offset += 4;
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'MLLT')) || // 4.6   MLLT MPEG location lookup table
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'MLL'))) {     // 4.7   MLL MPEG location lookup table
                $frame_offset = 0;
                $parsedFrame['framesbetweenreferences'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 0, 2));
                $parsedFrame['bytesbetweenreferences']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 2, 3));
                $parsedFrame['msbetweenreferences']     = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], 5, 3));
                $parsedFrame['bitsforbytesdeviation']   = getid3_lib::BigEndian2Int($parsedFrame['data'][8]);
                $parsedFrame['bitsformsdeviation']      = getid3_lib::BigEndian2Int($parsedFrame['data'][9]);
                $parsedFrame['data'] = substr($parsedFrame['data'], 10);
                $deviationbitstream = '';
                while ($frame_offset < strlen($parsedFrame['data'])) {
                    $deviationbitstream .= getid3_lib::BigEndian2Bin($parsedFrame['data'][$frame_offset++]);
                }
                $reference_counter = 0;
                while ($deviationbitstream !== '') {
                    $parsedFrame[$reference_counter]['bytedeviation'] = bindec(substr($deviationbitstream, 0, $parsedFrame['bitsforbytesdeviation']));
                    $parsedFrame[$reference_counter]['msdeviation']   = bindec(substr($deviationbitstream, $parsedFrame['bitsforbytesdeviation'], $parsedFrame['bitsformsdeviation']));
                    $deviationbitstream = substr($deviationbitstream, $parsedFrame['bitsforbytesdeviation'] + $parsedFrame['bitsformsdeviation']);
                    $reference_counter++;
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'SYTC')) || // 4.7   SYTC Synchronised tempo codes
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'STC'))) {  // 4.8   STC  Synchronised tempo codes
                $frame_offset = 0;
                $parsedFrame['timestampformat'] = ord($parsedFrame['data'][$frame_offset++]);
                $timestamp_counter = 0;
                while ($frame_offset < strlen($parsedFrame['data'])) {
                    $parsedFrame[$timestamp_counter]['tempo'] = ord($parsedFrame['data'][$frame_offset++]);
                    if ($parsedFrame[$timestamp_counter]['tempo'] === 255) {
                        $parsedFrame[$timestamp_counter]['tempo'] += ord($parsedFrame['data'][$frame_offset++]);
                    }
                    $parsedFrame[$timestamp_counter]['timestamp'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                    $frame_offset += 4;
                    $timestamp_counter++;
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'USLT')) || // 4.8   USLT Unsynchronised lyric/text transcription
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'ULT'))) {    // 4.9   ULT  Unsynchronised lyric/text transcription
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                if (strlen($parsedFrame['data']) >= (4 + strlen($frame_textencoding_terminator))) {  // shouldn't be an issue but badly-written files have been spotted in the wild with not only no contents but also missing the required language field, see https://github.com/JamesHeinrich/getID3/issues/315
                    $frame_language = substr($parsedFrame['data'], $frame_offset, 3);
                    $frame_offset += 3;
                    $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                    if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                        $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                    }
                    $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                    $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                    $parsedFrame['data'] = substr($parsedFrame['data'], $frame_terminatorpos + strlen($frame_textencoding_terminator));
                    $parsedFrame['data'] = self::RemoveStringTerminator($parsedFrame['data'], $frame_textencoding_terminator);
                    $parsedFrame['encodingid']   = $frame_textencoding;
                    $parsedFrame['encoding']     = self::TextEncodingNameLookup($frame_textencoding);
                    $parsedFrame['language']     = $frame_language;
                    $parsedFrame['languagename'] = self::LanguageLookup($frame_language, false);
                    if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                        $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
                    }
                } else {
                    $this->warning('Invalid data in frame "'.$parsedFrame['frame_name'].'" at offset '.$parsedFrame['dataoffset']);
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'SYLT')) || // 4.9   SYLT Synchronised lyric/text
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'SLT'))) {     // 4.10  SLT  Synchronised lyric/text
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                $frame_language = substr($parsedFrame['data'], $frame_offset, 3);
                $frame_offset += 3;
                $parsedFrame['timestampformat'] = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['contenttypeid']   = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['contenttype']     = self::SYTLContentTypeLookup($parsedFrame['contenttypeid']);
                $parsedFrame['encodingid']      = $frame_textencoding;
                $parsedFrame['encoding']        = self::TextEncodingNameLookup($frame_textencoding);
                $parsedFrame['language']        = $frame_language;
                $parsedFrame['languagename']    = self::LanguageLookup($frame_language, false);
                $timestampindex = 0;
                $frame_remainingdata = substr($parsedFrame['data'], $frame_offset);
                while ($frame_remainingdata !== '') {
                    $frame_offset = 0;
                    $frame_terminatorpos = strpos($frame_remainingdata, $frame_textencoding_terminator);
                    if ($frame_terminatorpos === false) {
                        $frame_remainingdata = '';
                    } else {
                        if (ord($frame_remainingdata[$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                            $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                        }
                        $parsedFrame['lyrics'][$timestampindex]['data'] = substr($frame_remainingdata, $frame_offset, $frame_terminatorpos - $frame_offset);
                        $frame_remainingdata = substr($frame_remainingdata, $frame_terminatorpos + strlen($frame_textencoding_terminator));
                        if (($timestampindex === 0) && (ord($frame_remainingdata[0]) !== 0)) {
                            // timestamp probably omitted for first data item
                        } else {
                            $parsedFrame['lyrics'][$timestampindex]['timestamp'] = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 0, 4));
                            $frame_remainingdata = substr($frame_remainingdata, 4);
                        }
                        $timestampindex++;
                    }
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'COMM')) || // 4.10  COMM Comments
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'COM'))) {     // 4.11  COM  Comments
                if (strlen($parsedFrame['data']) < 5) {
                    $this->warning('Invalid data (too short) for "'.$parsedFrame['frame_name'].'" frame at offset '.$parsedFrame['dataoffset']);
                } else {
                    $frame_offset = 0;
                    $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                    $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                    if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                        $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                        $frame_textencoding_terminator = "\x00";
                    }
                    $frame_language = substr($parsedFrame['data'], $frame_offset, 3);
                    $frame_offset += 3;
                    $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                    if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                        $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                    }
                    $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                    $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                    $frame_text = (string) substr($parsedFrame['data'], $frame_terminatorpos + strlen($frame_textencoding_terminator));
                    $frame_text = self::RemoveStringTerminator($frame_text, $frame_textencoding_terminator);
                    $parsedFrame['encodingid']   = $frame_textencoding;
                    $parsedFrame['encoding']     = self::TextEncodingNameLookup($frame_textencoding);
                    $parsedFrame['language']     = $frame_language;
                    $parsedFrame['languagename'] = self::LanguageLookup($frame_language, false);
                    $parsedFrame['data']         = $frame_text;
                    if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                        $_comment_key_nested = (!empty($info['id3v2']['comments'][$parsedFrame['framenameshort']]) ? count($info['id3v2']['comments'][$parsedFrame['framenameshort']]) : 0);
                        $commentkey = ($parsedFrame['description'] ?: $_comment_key_nested);
                        if (!isset($info['id3v2']['comments'][$parsedFrame['framenameshort']]) || !array_key_exists($commentkey, $info['id3v2']['comments'][$parsedFrame['framenameshort']])) {
                            $info['id3v2']['comments'][$parsedFrame['framenameshort']][$commentkey] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
                        } else {
                            $info['id3v2']['comments'][$parsedFrame['framenameshort']][]            = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
                        }
                    }
                }
            } elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] === 'RVA2')) { // 4.11  RVA2 Relative volume adjustment (2) (ID3v2.4+ only)
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00");
                $frame_idstring = substr($parsedFrame['data'], 0, $frame_terminatorpos);
                if (ord($frame_idstring) === 0) {
                    $frame_idstring = '';
                }
                $frame_remainingdata = substr($parsedFrame['data'], $frame_terminatorpos + strlen("\x00"));
                $parsedFrame['description'] = $frame_idstring;
                $RVA2channelcounter = 0;
                while (strlen($frame_remainingdata) >= 5) {
                    $frame_offset = 0;
                    $frame_channeltypeid = ord($frame_remainingdata[$frame_offset++]);
                    $parsedFrame[$RVA2channelcounter]['channeltypeid']  = $frame_channeltypeid;
                    $parsedFrame[$RVA2channelcounter]['channeltype']    = self::RVA2ChannelTypeLookup($frame_channeltypeid);
                    $parsedFrame[$RVA2channelcounter]['volumeadjust']   = getid3_lib::BigEndian2Int(substr($frame_remainingdata, $frame_offset, 2), false, true); // 16-bit signed
                    $frame_offset += 2;
                    $parsedFrame[$RVA2channelcounter]['bitspeakvolume'] = ord($frame_remainingdata[$frame_offset++]);
                    if (($parsedFrame[$RVA2channelcounter]['bitspeakvolume'] < 1) || ($parsedFrame[$RVA2channelcounter]['bitspeakvolume'] > 4)) {
                        $this->warning('ID3v2::RVA2 frame['.$RVA2channelcounter.'] contains invalid '.$parsedFrame[$RVA2channelcounter]['bitspeakvolume'].'-byte bits-representing-peak value');
                        break;
                    }
                    $frame_bytespeakvolume = ceil($parsedFrame[$RVA2channelcounter]['bitspeakvolume'] / 8);
                    $parsedFrame[$RVA2channelcounter]['peakvolume']     = getid3_lib::BigEndian2Int(substr($frame_remainingdata, $frame_offset, $frame_bytespeakvolume));
                    $frame_remainingdata = substr($frame_remainingdata, $frame_offset + $frame_bytespeakvolume);
                    $RVA2channelcounter++;
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion === 3) && ($parsedFrame['frame_name'] === 'RVAD')) || // 4.12  RVAD Relative volume adjustment (ID3v2.3 only)
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'RVA'))) {  // 4.12  RVA  Relative volume adjustment (ID3v2.2 only)
                $frame_offset = 0;
                $frame_incrdecrflags = getid3_lib::BigEndian2Bin($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['incdec']['right'] = (bool) substr($frame_incrdecrflags, 6, 1);
                $parsedFrame['incdec']['left']  = (bool) substr($frame_incrdecrflags, 7, 1);
                $parsedFrame['bitsvolume'] = ord($parsedFrame['data'][$frame_offset++]);
                $frame_bytesvolume = ceil($parsedFrame['bitsvolume'] / 8);
                $parsedFrame['volumechange']['right'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                if ($parsedFrame['incdec']['right'] === false) {
                    $parsedFrame['volumechange']['right'] *= -1;
                }
                $frame_offset += $frame_bytesvolume;
                $parsedFrame['volumechange']['left'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                if ($parsedFrame['incdec']['left'] === false) {
                    $parsedFrame['volumechange']['left'] *= -1;
                }
                $frame_offset += $frame_bytesvolume;
                $parsedFrame['peakvolume']['right'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                $frame_offset += $frame_bytesvolume;
                $parsedFrame['peakvolume']['left']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                $frame_offset += $frame_bytesvolume;
                if ($id3v2_majorversion === 3) {
                    $parsedFrame['data'] = substr($parsedFrame['data'], $frame_offset);
                    if ($parsedFrame['data'] !== '') {
                        $parsedFrame['incdec']['rightrear'] = (bool) substr($frame_incrdecrflags, 4, 1);
                        $parsedFrame['incdec']['leftrear']  = (bool) substr($frame_incrdecrflags, 5, 1);
                        $parsedFrame['volumechange']['rightrear'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        if ($parsedFrame['incdec']['rightrear'] === false) {
                            $parsedFrame['volumechange']['rightrear'] *= -1;
                        }
                        $frame_offset += $frame_bytesvolume;
                        $parsedFrame['volumechange']['leftrear'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        if ($parsedFrame['incdec']['leftrear'] === false) {
                            $parsedFrame['volumechange']['leftrear'] *= -1;
                        }
                        $frame_offset += $frame_bytesvolume;
                        $parsedFrame['peakvolume']['rightrear'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        $frame_offset += $frame_bytesvolume;
                        $parsedFrame['peakvolume']['leftrear']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        $frame_offset += $frame_bytesvolume;
                    }
                    $parsedFrame['data'] = substr($parsedFrame['data'], $frame_offset);
                    if ($parsedFrame['data'] !== '') {
                        $parsedFrame['incdec']['center'] = (bool) substr($frame_incrdecrflags, 3, 1);
                        $parsedFrame['volumechange']['center'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        if ($parsedFrame['incdec']['center'] === false) {
                            $parsedFrame['volumechange']['center'] *= -1;
                        }
                        $frame_offset += $frame_bytesvolume;
                        $parsedFrame['peakvolume']['center'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        $frame_offset += $frame_bytesvolume;
                    }
                    $parsedFrame['data'] = substr($parsedFrame['data'], $frame_offset);
                    if ($parsedFrame['data'] !== '') {
                        $parsedFrame['incdec']['bass'] = (bool) substr($frame_incrdecrflags, 2, 1);
                        $parsedFrame['volumechange']['bass'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        if ($parsedFrame['incdec']['bass'] === false) {
                            $parsedFrame['volumechange']['bass'] *= -1;
                        }
                        $frame_offset += $frame_bytesvolume;
                        $parsedFrame['peakvolume']['bass'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesvolume));
                        /** @noinspection PhpUnusedLocalVariableInspection */
                        $frame_offset += $frame_bytesvolume;
                    }
                }
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] === 'EQU2')) { // 4.12  EQU2 Equalisation (2) (ID3v2.4+ only)
                $frame_offset = 0;
                $frame_interpolationmethod = ord($parsedFrame['data'][$frame_offset++]);
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_idstring = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_idstring) === 0) {
                    $frame_idstring = '';
                }
                $parsedFrame['description'] = $frame_idstring;
                $frame_remainingdata = substr($parsedFrame['data'], $frame_terminatorpos + strlen("\x00"));
                while ($frame_remainingdata !== '') {
                    $frame_frequency = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 0, 2)) / 2;
                    $parsedFrame['data'][$frame_frequency] = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 2, 2), false, true);
                    $frame_remainingdata = substr($frame_remainingdata, 4);
                }
                $parsedFrame['interpolationmethod'] = $frame_interpolationmethod;
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion === 3) && ($parsedFrame['frame_name'] === 'EQUA')) || // 4.12  EQUA Equalisation (ID3v2.3 only)
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'EQU'))) {     // 4.13  EQU  Equalisation (ID3v2.2 only)
                $frame_offset = 0;
                $parsedFrame['adjustmentbits'] = $parsedFrame['data'][$frame_offset++];
                $frame_adjustmentbytes = ceil($parsedFrame['adjustmentbits'] / 8);
                $frame_remainingdata = (string) substr($parsedFrame['data'], $frame_offset);
                while ($frame_remainingdata !== '') {
                    $frame_frequencystr = getid3_lib::BigEndian2Bin(substr($frame_remainingdata, 0, 2));
                    $frame_incdec    = (bool) substr($frame_frequencystr, 0, 1);
                    $frame_frequency = bindec(substr($frame_frequencystr, 1, 15));
                    $parsedFrame[$frame_frequency]['incdec'] = $frame_incdec;
                    $parsedFrame[$frame_frequency]['adjustment'] = getid3_lib::BigEndian2Int(substr($frame_remainingdata, 2, $frame_adjustmentbytes));
                    if ($parsedFrame[$frame_frequency]['incdec'] === false) {
                        $parsedFrame[$frame_frequency]['adjustment'] *= -1;
                    }
                    $frame_remainingdata = substr($frame_remainingdata, 2 + $frame_adjustmentbytes);
                }
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'RVRB')) || // 4.13  RVRB Reverb
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'REV'))) {     // 4.14  REV  Reverb
                $frame_offset = 0;
                $parsedFrame['left']  = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                $frame_offset += 2;
                $parsedFrame['right'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                $frame_offset += 2;
                $parsedFrame['bouncesL']      = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['bouncesR']      = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['feedbackLL']    = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['feedbackLR']    = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['feedbackRR']    = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['feedbackRL']    = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['premixLR']      = ord($parsedFrame['data'][$frame_offset++]);
                /** @noinspection PhpUnusedLocalVariableInspection */
                $parsedFrame['premixRL']      = ord($parsedFrame['data'][$frame_offset++]);
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'APIC')) || // 4.14  APIC Attached picture
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'PIC'))) {     // 4.15  PIC  Attached picture
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                $frame_imagetype = null;
                $frame_mimetype = null;
                if ($id3v2_majorversion === 2 && strlen($parsedFrame['data']) > $frame_offset) {
                    $frame_imagetype = substr($parsedFrame['data'], $frame_offset, 3);
                    if (strtolower($frame_imagetype) === 'ima') {
                        // complete hack for mp3Rage (www.chaoticsoftware.com) that puts ID3v2.3-formatted
                        // MIME type instead of 3-char ID3v2.2-format image type  (thanks xbhoffØpacbell*net)
                        $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                        $frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                        if (ord($frame_mimetype) === 0) {
                            $frame_mimetype = '';
                        }
                        $frame_imagetype = strtoupper(str_replace('image/', '', strtolower($frame_mimetype)));
                        if ($frame_imagetype === 'JPEG') {
                            $frame_imagetype = 'JPG';
                        }
                        $frame_offset = $frame_terminatorpos + strlen("\x00");
                    } else {
                        $frame_offset += 3;
                    }
                }
                if ($id3v2_majorversion > 2 && strlen($parsedFrame['data']) > $frame_offset) {
                    $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                    $frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                    if (ord($frame_mimetype) === 0) {
                        $frame_mimetype = '';
                    }
                    $frame_offset = $frame_terminatorpos + strlen("\x00");
                }
                $frame_picturetype = ord($parsedFrame['data'][$frame_offset++]);
                if ($frame_offset >= $parsedFrame['datalength']) {
                    $this->warning('data portion of APIC frame is missing at offset '.($parsedFrame['dataoffset'] + 8 + $frame_offset));
                } else {
                    $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                    if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                        $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                    }
                    $parsedFrame['description']   = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                    $parsedFrame['description']   = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                    $parsedFrame['encodingid']    = $frame_textencoding;
                    $parsedFrame['encoding']      = self::TextEncodingNameLookup($frame_textencoding);
                    if ($id3v2_majorversion === 2) {
                        $parsedFrame['imagetype'] = $frame_imagetype ?? null;
                    } else {
                        $parsedFrame['mime']      = $frame_mimetype ?? null;
                    }
                    $parsedFrame['picturetypeid'] = $frame_picturetype;
                    $parsedFrame['picturetype']   = self::APICPictureTypeLookup($frame_picturetype);
                    $parsedFrame['data']          = substr($parsedFrame['data'], $frame_terminatorpos + strlen($frame_textencoding_terminator));
                    $parsedFrame['datalength']    = strlen($parsedFrame['data']);
                    $parsedFrame['image_mime']    = '';
                    $imageinfo = array();
                    if (($imagechunkcheck = getid3_lib::GetDataImageSize($parsedFrame['data'], $imageinfo)) && ($imagechunkcheck[2] >= 1) && ($imagechunkcheck[2] <= 3)) {
                        $parsedFrame['image_mime']       = image_type_to_mime_type($imagechunkcheck[2]);
                        if ($imagechunkcheck[0]) {
                            $parsedFrame['image_width']  = $imagechunkcheck[0];
                        }
                        if ($imagechunkcheck[1]) {
                            $parsedFrame['image_height'] = $imagechunkcheck[1];
                        }
                    }
                    do {
                        if ($this->getid3->option_save_attachments === false) {
                            // skip entirely
                            unset($parsedFrame['data']);
                            break;
                        }
                        $dir = '';
                        if ($this->getid3->option_save_attachments === true) {
                        } elseif (is_string($this->getid3->option_save_attachments)) {
                            $dir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->getid3->option_save_attachments), DIRECTORY_SEPARATOR);
                            if (!is_dir($dir) || !getID3::is_writable($dir)) {
                                // cannot write, skip
                                $this->warning('attachment at '.$frame_offset.' cannot be saved to "'.$dir.'" (not writable)');
                                unset($parsedFrame['data']);
                                break;
                            }
                        }
                        // if we get this far, must be OK
                        if (is_string($this->getid3->option_save_attachments)) {
                            $destination_filename = $dir.DIRECTORY_SEPARATOR.md5($info['filenamepath']).'_'.$frame_offset;
                            if (!file_exists($destination_filename) || getID3::is_writable($destination_filename)) {
                                file_put_contents($destination_filename, $parsedFrame['data']);
                            } else {
                                $this->warning('attachment at '.$frame_offset.' cannot be saved to "'.$destination_filename.'" (not writable)');
                            }
                            $parsedFrame['data_filename'] = $destination_filename;
                            unset($parsedFrame['data']);
                        } else if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                            if (!isset($info['id3v2']['comments']['picture'])) {
                                $info['id3v2']['comments']['picture'] = array();
                            }
                            $comments_picture_data = array();
                            foreach (array('data', 'image_mime', 'image_width', 'image_height', 'imagetype', 'picturetype', 'description', 'datalength') as $picture_key) {
                                if (isset($parsedFrame[$picture_key])) {
                                    $comments_picture_data[$picture_key] = $parsedFrame[$picture_key];
                                }
                            }
                            $info['id3v2']['comments']['picture'][] = $comments_picture_data;
                            unset($comments_picture_data);
                        }
                    } while (false);
                }
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'GEOB')) || // 4.15  GEOB General encapsulated object
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'GEO'))) {     // 4.16  GEO  General encapsulated object
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_mimetype) === 0) {
                    $frame_mimetype = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");

                $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                    $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                }
                $frame_filename = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_filename) === 0) {
                    $frame_filename = '';
                }
                $frame_offset = $frame_terminatorpos + strlen($frame_textencoding_terminator);
                $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                    $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                }
                $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                $frame_offset = $frame_terminatorpos + strlen($frame_textencoding_terminator);
                $parsedFrame['objectdata']  = (string) substr($parsedFrame['data'], $frame_offset);
                $parsedFrame['encodingid']  = $frame_textencoding;
                $parsedFrame['encoding']    = self::TextEncodingNameLookup($frame_textencoding);
                $parsedFrame['mime']        = $frame_mimetype;
                $parsedFrame['filename']    = $frame_filename;
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'PCNT')) || // 4.16  PCNT Play counter
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'CNT'))) {     // 4.17  CNT  Play counter
                $parsedFrame['data']          = getid3_lib::BigEndian2Int($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'POPM')) || // 4.17  POPM Popularimeter
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'POP'))) {    // 4.18  POP  Popularimeter
                $frame_offset = 0;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_emailaddress = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_emailaddress) === 0) {
                    $frame_emailaddress = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $frame_rating = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['counter'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset));
                $parsedFrame['email']   = $frame_emailaddress;
                $parsedFrame['rating']  = $frame_rating;
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'RBUF')) || // 4.18  RBUF Recommended buffer size
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'BUF'))) {     // 4.19  BUF  Recommended buffer size
                $frame_offset = 0;
                $parsedFrame['buffersize'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 3));
                $frame_offset += 3;
                $frame_embeddedinfoflags = getid3_lib::BigEndian2Bin($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['flags']['embededinfo'] = (bool) substr($frame_embeddedinfoflags, 7, 1);
                $parsedFrame['nexttagoffset'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'CRM')) { // 4.20  Encrypted meta frame (ID3v2.2 only)
                $frame_offset = 0;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['ownerid']     = $frame_ownerid;
                $parsedFrame['data']        = (string) substr($parsedFrame['data'], $frame_offset);
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'AENC')) || // 4.19  AENC Audio encryption
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'CRA'))) {     // 4.21  CRA  Audio encryption
                $frame_offset = 0;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_ownerid) === 0) {
                    $frame_ownerid = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['ownerid'] = $frame_ownerid;
                $parsedFrame['previewstart'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                $frame_offset += 2;
                $parsedFrame['previewlength'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                $frame_offset += 2;
                $parsedFrame['encryptioninfo'] = (string) substr($parsedFrame['data'], $frame_offset);
                unset($parsedFrame['data']);
            } elseif ((($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'LINK')) || // 4.20  LINK Linked information
                (($id3v2_majorversion === 2) && ($parsedFrame['frame_name'] === 'LNK'))) {    // 4.22  LNK  Linked information
                $frame_offset = 0;
                if ($id3v2_majorversion === 2) {
                    $parsedFrame['frameid'] = substr($parsedFrame['data'], $frame_offset, 3);
                    $frame_offset += 3;
                } else {
                    $parsedFrame['frameid'] = substr($parsedFrame['data'], $frame_offset, 4);
                    $frame_offset += 4;
                }
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_url = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_url) === 0) {
                    $frame_url = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['url'] = $frame_url;
                $parsedFrame['additionaldata'] = (string) substr($parsedFrame['data'], $frame_offset);
                if (!empty($parsedFrame['framenameshort']) && $parsedFrame['url']) {
                    $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback_iso88591_utf8($parsedFrame['url']);
                }
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'POSS')) { // 4.21  POSS Position synchronisation frame (ID3v2.3+ only)
                $frame_offset = 0;
                $parsedFrame['timestampformat'] = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['position']        = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset));
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'USER')) { // 4.22  USER Terms of use (ID3v2.3+ only)
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                }
                $frame_language = substr($parsedFrame['data'], $frame_offset, 3);
                $frame_offset += 3;
                $parsedFrame['language']     = $frame_language;
                $parsedFrame['languagename'] = self::LanguageLookup($frame_language, false);
                $parsedFrame['encodingid']   = $frame_textencoding;
                $parsedFrame['encoding']     = self::TextEncodingNameLookup($frame_textencoding);
                $parsedFrame['data'] = (string) substr($parsedFrame['data'], $frame_offset);
                $parsedFrame['data'] = self::RemoveStringTerminator($parsedFrame['data'], self::TextEncodingTerminatorLookup($frame_textencoding));
                if (!empty($parsedFrame['framenameshort']) && !empty($parsedFrame['data'])) {
                    $info['id3v2']['comments'][$parsedFrame['framenameshort']][] = getid3_lib::iconv_fallback($parsedFrame['encoding'], $info['id3v2']['encoding'], $parsedFrame['data']);
                }
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'OWNE')) { // 4.23  OWNE Ownership frame (ID3v2.3+ only)
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                }
                $parsedFrame['encodingid'] = $frame_textencoding;
                $parsedFrame['encoding']   = self::TextEncodingNameLookup($frame_textencoding);
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_pricepaid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['pricepaid']['currencyid'] = substr($frame_pricepaid, 0, 3);
                $parsedFrame['pricepaid']['currency']   = $this->LookupCurrencyUnits($parsedFrame['pricepaid']['currencyid']);
                $parsedFrame['pricepaid']['value']      = substr($frame_pricepaid, 3);
                $parsedFrame['purchasedate'] = substr($parsedFrame['data'], $frame_offset, 8);
                if (self::IsValidDateStampString($parsedFrame['purchasedate'])) {
                    $parsedFrame['purchasedateunix'] = mktime (0, 0, 0, substr($parsedFrame['purchasedate'], 4, 2), substr($parsedFrame['purchasedate'], 6, 2), substr($parsedFrame['purchasedate'], 0, 4));
                }
                $frame_offset += 8;
                $parsedFrame['seller'] = (string) substr($parsedFrame['data'], $frame_offset);
                $parsedFrame['seller'] = self::RemoveStringTerminator($parsedFrame['seller'], self::TextEncodingTerminatorLookup($frame_textencoding));
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'COMR')) { // 4.24  COMR Commercial frame (ID3v2.3+ only)
                $frame_offset = 0;
                $frame_textencoding = ord($parsedFrame['data'][$frame_offset++]);
                $frame_textencoding_terminator = self::TextEncodingTerminatorLookup($frame_textencoding);
                if ((($id3v2_majorversion <= 3) && ($frame_textencoding > 1)) || (($id3v2_majorversion === 4) && ($frame_textencoding > 3))) {
                    $this->warning('Invalid text encoding byte ('.$frame_textencoding.') in frame "'.$parsedFrame['frame_name'].'" - defaulting to ISO-8859-1 encoding');
                    $frame_textencoding_terminator = "\x00";
                }
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_pricestring = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $frame_rawpricearray = explode('/', $frame_pricestring);
                foreach ($frame_rawpricearray as $key => $val) {
                    $frame_currencyid = substr($val, 0, 3);
                    $parsedFrame['price'][$frame_currencyid]['currency'] = $this->LookupCurrencyUnits($frame_currencyid);
                    $parsedFrame['price'][$frame_currencyid]['value']    = substr($val, 3);
                }
                $frame_datestring = substr($parsedFrame['data'], $frame_offset, 8);
                $frame_offset += 8;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_contacturl = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $frame_receivedasid = ord($parsedFrame['data'][$frame_offset++]);
                $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                    $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                }
                $frame_sellername = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_sellername) === 0) {
                    $frame_sellername = '';
                }
                $frame_offset = $frame_terminatorpos + strlen($frame_textencoding_terminator);
                $frame_terminatorpos = strpos($parsedFrame['data'], $frame_textencoding_terminator, $frame_offset);
                if (ord($parsedFrame['data'][$frame_terminatorpos + strlen($frame_textencoding_terminator)]) === 0) {
                    $frame_terminatorpos++; // strpos() fooled because 2nd byte of Unicode chars are often 0x00
                }
                $parsedFrame['description'] = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $parsedFrame['description'] = self::MakeUTF16emptyStringEmpty($parsedFrame['description']);
                $frame_offset = $frame_terminatorpos + strlen($frame_textencoding_terminator);
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_mimetype = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $frame_sellerlogo = substr($parsedFrame['data'], $frame_offset);
                $parsedFrame['encodingid']        = $frame_textencoding;
                $parsedFrame['encoding']          = self::TextEncodingNameLookup($frame_textencoding);
                $parsedFrame['pricevaliduntil']   = $frame_datestring;
                $parsedFrame['contacturl']        = $frame_contacturl;
                $parsedFrame['receivedasid']      = $frame_receivedasid;
                $parsedFrame['receivedas']        = self::COMRReceivedAsLookup($frame_receivedasid);
                $parsedFrame['sellername']        = $frame_sellername;
                $parsedFrame['mime']              = $frame_mimetype;
                $parsedFrame['logo']              = $frame_sellerlogo;
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'ENCR')) { // 4.25  ENCR Encryption method registration (ID3v2.3+ only)
                $frame_offset = 0;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_ownerid) === 0) {
                    $frame_ownerid = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['ownerid']      = $frame_ownerid;
                $parsedFrame['methodsymbol'] = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['data']         = (string) substr($parsedFrame['data'], $frame_offset);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'GRID')) { // 4.26  GRID Group identification registration (ID3v2.3+ only)
                $frame_offset = 0;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_ownerid) === 0) {
                    $frame_ownerid = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['ownerid']       = $frame_ownerid;
                $parsedFrame['groupsymbol']   = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['data']          = (string) substr($parsedFrame['data'], $frame_offset);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'PRIV')) { // 4.27  PRIV Private frame (ID3v2.3+ only)
                $frame_offset = 0;
                $frame_terminatorpos = strpos($parsedFrame['data'], "\x00", $frame_offset);
                $frame_ownerid = substr($parsedFrame['data'], $frame_offset, $frame_terminatorpos - $frame_offset);
                if (ord($frame_ownerid) === 0) {
                    $frame_ownerid = '';
                }
                $frame_offset = $frame_terminatorpos + strlen("\x00");
                $parsedFrame['ownerid'] = $frame_ownerid;
                $parsedFrame['data']    = (string) substr($parsedFrame['data'], $frame_offset);
            } elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] === 'SIGN')) { // 4.28  SIGN Signature frame (ID3v2.4+ only)
                $frame_offset = 0;
                $parsedFrame['groupsymbol'] = ord($parsedFrame['data'][$frame_offset++]);
                $parsedFrame['data']        = (string) substr($parsedFrame['data'], $frame_offset);
            } elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] === 'SEEK')) { // 4.29  SEEK Seek frame (ID3v2.4+ only)
                $frame_offset = 0;
                $parsedFrame['data']          = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
            } elseif (($id3v2_majorversion >= 4) && ($parsedFrame['frame_name'] === 'ASPI')) { // 4.30  ASPI Audio seek point index (ID3v2.4+ only)
                $frame_offset = 0;
                $parsedFrame['datastart'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                $frame_offset += 4;
                $parsedFrame['indexeddatalength'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                $frame_offset += 4;
                $parsedFrame['indexpoints'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                $frame_offset += 2;
                $parsedFrame['bitsperpoint'] = ord($parsedFrame['data'][$frame_offset++]);
                $frame_bytesperpoint = ceil($parsedFrame['bitsperpoint'] / 8);
                for ($i = 0; $i < $parsedFrame['indexpoints']; $i++) {
                    $parsedFrame['indexes'][$i] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, $frame_bytesperpoint));
                    $frame_offset += $frame_bytesperpoint;
                }
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'RGAD')) { // Replay Gain Adjustment
                $frame_offset = 0;
                $parsedFrame['peakamplitude'] = getid3_lib::BigEndian2Float(substr($parsedFrame['data'], $frame_offset, 4));
                $frame_offset += 4;
                foreach (array('track','album') as $rgad_entry_type) {
                    $rg_adjustment_word = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                    $frame_offset += 2;
                    $parsedFrame['raw'][$rgad_entry_type]['name']       = ($rg_adjustment_word & 0xE000) >> 13;
                    $parsedFrame['raw'][$rgad_entry_type]['originator'] = ($rg_adjustment_word & 0x1C00) >> 10;
                    $parsedFrame['raw'][$rgad_entry_type]['signbit']    = ($rg_adjustment_word & 0x0200) >>  9;
                    $parsedFrame['raw'][$rgad_entry_type]['adjustment'] = ($rg_adjustment_word & 0x0100);
                }
                $parsedFrame['track']['name']       = getid3_lib::RGADnameLookup($parsedFrame['raw']['track']['name']);
                $parsedFrame['track']['originator'] = getid3_lib::RGADoriginatorLookup($parsedFrame['raw']['track']['originator']);
                $parsedFrame['track']['adjustment'] = getid3_lib::RGADadjustmentLookup($parsedFrame['raw']['track']['adjustment'], $parsedFrame['raw']['track']['signbit']);
                $parsedFrame['album']['name']       = getid3_lib::RGADnameLookup($parsedFrame['raw']['album']['name']);
                $parsedFrame['album']['originator'] = getid3_lib::RGADoriginatorLookup($parsedFrame['raw']['album']['originator']);
                $parsedFrame['album']['adjustment'] = getid3_lib::RGADadjustmentLookup($parsedFrame['raw']['album']['adjustment'], $parsedFrame['raw']['album']['signbit']);
                $info['replay_gain']['track']['peak']       = $parsedFrame['peakamplitude'];
                $info['replay_gain']['track']['originator'] = $parsedFrame['track']['originator'];
                $info['replay_gain']['track']['adjustment'] = $parsedFrame['track']['adjustment'];
                $info['replay_gain']['album']['originator'] = $parsedFrame['album']['originator'];
                $info['replay_gain']['album']['adjustment'] = $parsedFrame['album']['adjustment'];
                unset($parsedFrame['data']);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'CHAP')) { // CHAP Chapters frame (ID3v2.3+ only)
                $frame_offset = 0;
                @list($parsedFrame['element_id']) = explode("\x00", $parsedFrame['data'], 2);
                $frame_offset += strlen($parsedFrame['element_id']."\x00");
                $parsedFrame['time_begin'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                $frame_offset += 4;
                $parsedFrame['time_end']   = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                $frame_offset += 4;
                if (substr($parsedFrame['data'], $frame_offset, 4) !== "\xFF\xFF\xFF\xFF") {
                    // "If these bytes are all set to 0xFF then the value should be ignored and the start time value should be utilized."
                    $parsedFrame['offset_begin'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                }
                $frame_offset += 4;
                if (substr($parsedFrame['data'], $frame_offset, 4) !== "\xFF\xFF\xFF\xFF") {
                    // "If these bytes are all set to 0xFF then the value should be ignored and the start time value should be utilized."
                    $parsedFrame['offset_end']   = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                }
                $frame_offset += 4;
                if ($frame_offset < strlen($parsedFrame['data'])) {
                    $parsedFrame['subframes'] = array();
                    while ($frame_offset < strlen($parsedFrame['data'])) {
                        // <Optional embedded sub-frames>
                        $subframe = array();
                        $subframe['name']      =                           substr($parsedFrame['data'], $frame_offset, 4);
                        $frame_offset += 4;
                        $subframe['size']      = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                        $frame_offset += 4;
                        $subframe['flags_raw'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                        $frame_offset += 2;
                        if ($subframe['size'] > (strlen($parsedFrame['data']) - $frame_offset)) {
                            $this->warning('CHAP subframe "'.$subframe['name'].'" at frame offset '.$frame_offset.' claims to be "'.$subframe['size'].'" bytes, which is more than the available data ('.(strlen($parsedFrame['data']) - $frame_offset).' bytes)');
                            break;
                        }
                        $subframe_rawdata = substr($parsedFrame['data'], $frame_offset, $subframe['size']);
                        $frame_offset += $subframe['size'];
                        $subframe['encodingid'] = ord($subframe_rawdata[0]);
                        $subframe['text']       =     substr($subframe_rawdata, 1);
                        $subframe['encoding']   = self::TextEncodingNameLookup($subframe['encodingid']);
                        $encoding_converted_text = trim(getid3_lib::iconv_fallback($subframe['encoding'], $info['encoding'], $subframe['text']));
                        switch (substr($encoding_converted_text, 0, 2)) {
                            case "\xFF\xFE":
                            case "\xFE\xFF":
                                switch (strtoupper($info['id3v2']['encoding'])) {
                                    case 'ISO-8859-1':
                                    case 'UTF-8':
                                        $encoding_converted_text = substr($encoding_converted_text, 2);
                                        // remove unwanted byte-order-marks
                                        break;
                                    default:
                                        // ignore
                                        break;
                                }
                                break;
                            default:
                                // do not remove BOM
                                break;
                        }
                        switch ($subframe['name']) {
                            case 'TIT2':
                                $parsedFrame['chapter_name']        = $encoding_converted_text;
                                $parsedFrame['subframes'][] = $subframe;
                                break;
                            case 'TIT3':
                                $parsedFrame['chapter_description'] = $encoding_converted_text;
                                $parsedFrame['subframes'][] = $subframe;
                                break;
                            case 'WXXX':
                                @list($subframe['chapter_url_description'], $subframe['chapter_url']) = explode("\x00", $encoding_converted_text, 2);
                                $parsedFrame['chapter_url'][$subframe['chapter_url_description']] = $subframe['chapter_url'];
                                $parsedFrame['subframes'][] = $subframe;
                                break;
                            case 'APIC':
                                if (preg_match('#^([^\\x00]+)*\\x00(.)([^\\x00]+)*\\x00(.+)$#s', $subframe['text'], $matches)) {
                                    @list($dummy, $subframe_apic_mime, $subframe_apic_picturetype, $subframe_apic_description, $subframe_apic_picturedata) = $matches;
                                    $subframe['image_mime']   = trim(getid3_lib::iconv_fallback($subframe['encoding'], $info['encoding'], $subframe_apic_mime));
                                    $subframe['picture_type'] = self::APICPictureTypeLookup($subframe_apic_picturetype);
                                    $subframe['description']  = trim(getid3_lib::iconv_fallback($subframe['encoding'], $info['encoding'], $subframe_apic_description));
                                    if (strlen(self::TextEncodingTerminatorLookup($subframe['encoding'])) === 2) {
                                        // the null terminator between "description" and "picture data" could be either 1 byte (ISO-8859-1, UTF-8) or two bytes (UTF-16)
                                        // the above regex assumes one byte, if it's actually two then strip the second one here
                                        $subframe_apic_picturedata = substr($subframe_apic_picturedata, 1);
                                    }
                                    $subframe['data'] = $subframe_apic_picturedata;
                                    unset($dummy, $subframe_apic_mime, $subframe_apic_picturetype, $subframe_apic_description, $subframe_apic_picturedata,$subframe['text'], $parsedFrame['text']);
                                    $parsedFrame['subframes'][] = $subframe;
                                    $parsedFrame['picture_present'] = true;
                                } else {
                                    $this->warning('ID3v2.CHAP subframe #'.(count($parsedFrame['subframes']) + 1).' "'.$subframe['name'].'" not in expected format');
                                }
                                break;
                            default:
                                $this->warning('ID3v2.CHAP subframe "'.$subframe['name'].'" not handled (supported: TIT2, TIT3, WXXX, APIC)');
                                break;
                        }
                    }
                    unset($subframe_rawdata, $subframe, $encoding_converted_text,$parsedFrame['data']); // debatable whether this this be here, without it the returned structure may contain a large amount of duplicate data if chapters contain APIC
                }
                $id3v2_chapter_entry = array();
                foreach (array('id', 'time_begin', 'time_end', 'offset_begin', 'offset_end', 'chapter_name', 'chapter_description', 'chapter_url', 'picture_present') as $id3v2_chapter_key) {
                    if (isset($parsedFrame[$id3v2_chapter_key])) {
                        $id3v2_chapter_entry[$id3v2_chapter_key] = $parsedFrame[$id3v2_chapter_key];
                    }
                }
                if (!isset($info['id3v2']['chapters'])) {
                    $info['id3v2']['chapters'] = array();
                }
                $info['id3v2']['chapters'][] = $id3v2_chapter_entry;
                unset($id3v2_chapter_entry, $id3v2_chapter_key);
            } elseif (($id3v2_majorversion >= 3) && ($parsedFrame['frame_name'] === 'CTOC')) { // CTOC Chapters Table Of Contents frame (ID3v2.3+ only)
                $frame_offset = 0;
                @list($parsedFrame['element_id']) = explode("\x00", $parsedFrame['data'], 2);
                $frame_offset += strlen($parsedFrame['element_id']."\x00");
                $ctoc_flags_raw = ord($parsedFrame['data'][$frame_offset]);
                ++$frame_offset;
                $parsedFrame['entry_count'] = ord($parsedFrame['data'][$frame_offset]);
                ++$frame_offset;
                $terminator_position = null;
                for ($i = 0; $i < $parsedFrame['entry_count']; $i++) {
                    $terminator_position = strpos($parsedFrame['data'], "\x00", $frame_offset);
                    $parsedFrame['child_element_ids'][$i] = substr($parsedFrame['data'], $frame_offset, $terminator_position - $frame_offset);
                    $frame_offset = $terminator_position + 1;
                }
                $parsedFrame['ctoc_flags']['ordered']   = (bool) ($ctoc_flags_raw & 0x01);
                $parsedFrame['ctoc_flags']['top_level'] = (bool) ($ctoc_flags_raw & 0x03);
                unset($ctoc_flags_raw, $terminator_position);
                if ($frame_offset < strlen($parsedFrame['data'])) {
                    $parsedFrame['subframes'] = array();
                    while ($frame_offset < strlen($parsedFrame['data'])) {
                        // <Optional embedded sub-frames>
                        $subframe = array();
                        $subframe['name']      =                           substr($parsedFrame['data'], $frame_offset, 4);
                        $frame_offset += 4;
                        $subframe['size']      = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 4));
                        $frame_offset += 4;
                        $subframe['flags_raw'] = getid3_lib::BigEndian2Int(substr($parsedFrame['data'], $frame_offset, 2));
                        $frame_offset += 2;
                        if ($subframe['size'] > (strlen($parsedFrame['data']) - $frame_offset)) {
                            $this->warning('CTOS subframe "'.$subframe['name'].'" at frame offset '.$frame_offset.' claims to be "'.$subframe['size'].'" bytes, which is more than the available data ('.(strlen($parsedFrame['data']) - $frame_offset).' bytes)');
                            break;
                        }
                        $subframe_rawdata = substr($parsedFrame['data'], $frame_offset, $subframe['size']);
                        $frame_offset += $subframe['size'];
                        $subframe['encodingid'] = ord($subframe_rawdata[0]);
                        $subframe['text']       =     substr($subframe_rawdata, 1);
                        $subframe['encoding']   = self::TextEncodingNameLookup($subframe['encodingid']);
                        $encoding_converted_text = trim(getid3_lib::iconv_fallback($subframe['encoding'], $info['encoding'], $subframe['text']));
                        switch (substr($encoding_converted_text, 0, 2)) {
                            case "\xFF\xFE":
                            case "\xFE\xFF":
                                switch (strtoupper($info['id3v2']['encoding'])) {
                                    case 'ISO-8859-1':
                                    case 'UTF-8':
                                        $encoding_converted_text = substr($encoding_converted_text, 2);
                                        // remove unwanted byte-order-marks
                                        break;
                                    default:
                                        // ignore
                                        break;
                                }
                                break;
                            default:
                                // do not remove BOM
                                break;
                        }
                        if (($subframe['name'] === 'TIT2') || ($subframe['name'] === 'TIT3')) {
                            if ($subframe['name'] === 'TIT2') {
                                $parsedFrame['toc_name']        = $encoding_converted_text;
                            } elseif ($subframe['name'] === 'TIT3') {
                                $parsedFrame['toc_description'] = $encoding_converted_text;
                            }
                            $parsedFrame['subframes'][] = $subframe;
                        } else {
                            $this->warning('ID3v2.CTOC subframe "'.$subframe['name'].'" not handled (only TIT2 and TIT3)');
                        }
                    }
                    unset($subframe_rawdata, $subframe, $encoding_converted_text);
                }
            }
            return true;
        }
        public function DeUnsynchronise($data):string {
            return str_replace("\xFF\x00", "\xFF", $data);
        }
        public function LookupExtendedHeaderRestrictionsTagSizeLimits($index):string {
            static $LookupExtendedHeaderRestrictionsTagSizeLimits = array(
                0x00 => 'No more than 128 frames and 1 MB total tag size',
                0x01 => 'No more than 64 frames and 128 KB total tag size',
                0x02 => 'No more than 32 frames and 40 KB total tag size',
                0x03 => 'No more than 32 frames and 4 KB total tag size',
            );
            return ($LookupExtendedHeaderRestrictionsTagSizeLimits[$index] ?? '');
        }
        public function LookupExtendedHeaderRestrictionsTextEncodings($index):string {
            static $LookupExtendedHeaderRestrictionsTextEncodings = array(
                0x00 => 'No restrictions',
                0x01 => 'Strings are only encoded with ISO-8859-1 or UTF-8',
            );
            return ($LookupExtendedHeaderRestrictionsTextEncodings[$index] ?? '');
        }
        public function LookupExtendedHeaderRestrictionsTextFieldSize($index):string {
            static $LookupExtendedHeaderRestrictionsTextFieldSize = array(
                0x00 => 'No restrictions',
                0x01 => 'No string is longer than 1024 characters',
                0x02 => 'No string is longer than 128 characters',
                0x03 => 'No string is longer than 30 characters',
            );
            return ($LookupExtendedHeaderRestrictionsTextFieldSize[$index] ?? '');
        }
        public function LookupExtendedHeaderRestrictionsImageEncoding($index):string {
            static $LookupExtendedHeaderRestrictionsImageEncoding = array(
                0x00 => 'No restrictions',
                0x01 => 'Images are encoded only with PNG or JPEG',
            );
            return ($LookupExtendedHeaderRestrictionsImageEncoding[$index] ?? '');
        }
        public function LookupExtendedHeaderRestrictionsImageSizeSize($index):string {
            static $LookupExtendedHeaderRestrictionsImageSizeSize = array(
                0x00 => 'No restrictions',
                0x01 => 'All images are 256x256 pixels or smaller',
                0x02 => 'All images are 64x64 pixels or smaller',
                0x03 => 'All images are exactly 64x64 pixels, unless required otherwise',
            );
            return ($LookupExtendedHeaderRestrictionsImageSizeSize[$index] ?? '');
        }
        public function LookupCurrencyUnits($currencyid):string {
            $begin = __LINE__;
            return getid3_lib::EmbeddedLookup($currencyid, $begin, __LINE__, __FILE__, 'id3v2-currency-units');
        }
        public function LookupCurrencyCountry($currencyid):string {
            $begin = __LINE__;
            return getid3_lib::EmbeddedLookup($currencyid, $begin, __LINE__, __FILE__, 'id3v2-currency-country');
        }//2489
        public static function LanguageLookup($languagecode, $casesensitive=false):string {
            if (!$casesensitive) {
                $languagecode = strtolower($languagecode);
            }
            $begin = __LINE__;
            return getid3_lib::EmbeddedLookup($languagecode, $begin, __LINE__, __FILE__, 'id3v2-languagecode');
        }//2691
        public static function ETCOEventLookup($index):string {
            if (($index >= 0x17) && ($index <= 0xDF)) {
                return 'reserved for future use';
            }
            if (($index >= 0xE0) && ($index <= 0xEF)) {
                return 'not predefined synch 0-F';
            }
            if (($index >= 0xF0) && ($index <= 0xFC)) {
                return 'reserved for future use';
            }
            static $EventLookup = [0x00 => 'padding (has no meaning)',0x01 => 'end of initial silence',0x02 => 'intro start',
                0x03 => 'main part start',0x04 => 'outro start',0x05 => 'outro end',0x06 => 'verse start',0x07 => 'refrain start',0x08 => 'interlude start',
                0x09 => 'theme start',0x0A => 'variation start',0x0B => 'key change',0x0C => 'time change',0x0D => 'momentary unwanted noise (Snap, Crackle & Pop)',
                0x0E => 'sustained noise',0x0F => 'sustained noise end',0x10 => 'intro end',0x11 => 'main part end',0x12 => 'verse end',0x13 => 'refrain end',
                0x14 => 'theme end',0x15 => 'profanity',0x16 => 'profanity end',0xFD => 'audio end (start of silence)',
                0xFE => 'audio file ends',0xFF => 'one more byte of events follows'];
            return ($EventLookup[$index] ?? '');
        }//3151
        public static function SYTLContentTypeLookup($index):string {
            static $SYTLContentTypeLookup = [0x00 => 'other',0x01 => 'lyrics',0x02 => 'text transcription',0x03 => 'movement/part name',
                0x04 => 'events',0x05 => 'chord',0x06 => 'trivia/\'pop up\' information', 0x07 => 'URLs to webpages',0x08 => 'URLs to images'];
            return ($SYTLContentTypeLookup[$index] ?? '');
        }
        public static function APICPictureTypeLookup($index, $returnarray=false):string {
            static $APICPictureTypeLookup = [0x00 => 'Other', 0x01 => '32x32 pixels \'file icon\' (PNG only)',0x02 => 'Other file icon',
                0x03 => 'Cover (front)', 0x04 => 'Cover (back)',0x05 => 'Leaflet page', 0x06 => 'Media (e.g. label side of CD)',0x07 => 'Lead artist/lead performer/soloist',
                0x08 => 'Artist/performer',0x09 => 'Conductor',0x0A => 'Band/Orchestra',0x0B => 'Composer',0x0C => 'Lyricist/text writer',
                0x0D => 'Recording Location',0x0E => 'During recording',0x0F => 'During performance',0x10 => 'Movie/video screen capture',
                0x11 => 'A bright coloured fish', 0x12 => 'Illustration',0x13 => 'Band/artist logotype',0x14 => 'Publisher/Studio logotype'];
            if ($returnarray) {
                return $APICPictureTypeLookup;
            }
            return ($APICPictureTypeLookup[$index] ?? '');
        }
        public static function COMRReceivedAsLookup($index):string {
            static $COMRReceivedAsLookup = [0x00 => 'Other',0x01 => 'Standard CD album with other songs',0x02 => 'Compressed audio on CD', 0x03 => 'File over the Internet',
                0x04 => 'Stream over the Internet',0x05 => 'As note sheets', 0x06 => 'As note sheets in a book with other sheets',
                0x07 => 'Music on other media', 0x08 => 'Non-musical merchandise'];
            return ($COMRReceivedAsLookup[$index] ?? '');
        }
        public static function RVA2ChannelTypeLookup($index):string {
            static $RVA2ChannelTypeLookup = [0x00 => 'Other', 0x01 => 'Master volume',0x02 => 'Front right',0x03 => 'Front left',
                0x04 => 'Back right',0x05 => 'Back left',0x06 => 'Front centre', 0x07 => 'Back centre', 0x08 => 'Subwoofer'];
            return ($RVA2ChannelTypeLookup[$index] ?? '');
        }
        public static function FrameNameLongLookup($framename):string {
            $begin = __LINE__;
            return getid3_lib::EmbeddedLookup($framename, $begin, __LINE__, __FILE__, 'id3v2-framename_long');
        }
        public static function FrameNameShortLookup($framename):string {
            $begin = __LINE__;
            return getid3_lib::EmbeddedLookup($framename, $begin, __LINE__, __FILE__, 'id3v2-framename_short');
        }
        public static function TextEncodingTerminatorLookup($encoding):string {
            // http://www.id3.org/id3v2.4.0-structure.txt
            // Frames that allow different types of text encoding contains a text encoding description byte. Possible encodings:
            static $TextEncodingTerminatorLookup = array(
                0   => "\x00",     // $00  ISO-8859-1. Terminated with $00.
                1   => "\x00\x00", // $01  UTF-16 encoded Unicode with BOM. All strings in the same frame SHALL have the same byteorder. Terminated with $00 00.
                2   => "\x00\x00", // $02  UTF-16BE encoded Unicode without BOM. Terminated with $00 00.
                3   => "\x00",     // $03  UTF-8 encoded Unicode. Terminated with $00.
                255 => "\x00\x00"
            );
            return ($TextEncodingTerminatorLookup[$encoding] ?? "\x00");
        }
        public static function TextEncodingNameLookup($encoding):string {
            // http://www.id3.org/id3v2.4.0-structure.txt
            // Frames that allow different types of text encoding contains a text encoding description byte. Possible encodings:
            static $TextEncodingNameLookup = array(
                0   => 'ISO-8859-1', // $00  ISO-8859-1. Terminated with $00.
                1   => 'UTF-16',     // $01  UTF-16 encoded Unicode with BOM. All strings in the same frame SHALL have the same byteorder. Terminated with $00 00.
                2   => 'UTF-16BE',   // $02  UTF-16BE encoded Unicode without BOM. Terminated with $00 00.
                3   => 'UTF-8',      // $03  UTF-8 encoded Unicode. Terminated with $00.
                255 => 'UTF-16BE'
            );
            return ($TextEncodingNameLookup[$encoding] ?? 'ISO-8859-1');
        }
        public static function RemoveStringTerminator($string, $terminator):string {
            if (substr($string, -strlen($terminator), strlen($terminator)) === $terminator) {
                $string = substr($string, 0, -strlen($terminator));
            }
            return $string;
        }
        public static function MakeUTF16emptyStringEmpty($string):string {
            if (in_array($string, array("\x00", "\x00\x00", "\xFF\xFE", "\xFE\xFF"))) {
                // if string only contains a BOM or terminator then make it actually an empty string
                $string = '';
            }
            return $string;
        }
        public static function IsValidID3v2FrameName($framename, $id3v2majorversion) {
            switch ($id3v2majorversion) {
                case 2:
                    return preg_match('#[A-Z][A-Z0-9]{2}#', $framename);
                case 3:
                case 4:
                    return preg_match('#[A-Z][A-Z0-9]{3}#', $framename);
            }
            return false;
        }
        public static function IsANumber($numberstring, $allowdecimal=false, $allownegative=false):bool {
            for ($i = 0, $iMax = strlen($numberstring); $i < $iMax; $i++) {
                if ((chr($numberstring[$i]) < chr('0')) || (chr($numberstring[$i]) > chr('9'))) {
                    if (($numberstring[$i] === '.') && $allowdecimal) {
                        // allowed
                    } elseif (($numberstring[$i] === '-') && $allownegative && ($i === 0)) {
                        // allowed
                    } else {
                        return false;
                    }
                }
            }
            return true;
        }
        public static function IsValidDateStampString($datestamp):bool {
            if (strlen($datestamp) !== 8) {
                return false;
            }
            if (!self::IsANumber($datestamp, false)) {
                return false;
            }
            $year  = substr($datestamp, 0, 4);
            $month = substr($datestamp, 4, 2);
            $day   = substr($datestamp, 6, 2);
            if (($year === 0) || ($month === 0) || ($day === 0)) {
                return false;
            }
            if ($month > 12) {
                return false;
            }
            if ($day > 31) {
                return false;
            }
            if (($day > 30) && (($month === 4) || ($month === 6) || ($month === 9) || ($month === 11))) {
                return false;
            }
            if (($day > 29) && ($month === 2)) {
                return false;
            }
            return true;
        }
        public static function ID3v2HeaderLength($majorversion):int {
            return (($majorversion === 2) ? 6 : 10);
        }
        public static function ID3v22iTunesBrokenFrameName($frame_name) {
            static $ID3v22_iTunes_BrokenFrames = array(
                'BUF' => 'RBUF', // Recommended buffer size
                'CNT' => 'PCNT', // Play counter
                'COM' => 'COMM', // Comments
                'CRA' => 'AENC', // Audio encryption
                'EQU' => 'EQUA', // Equalisation
                'ETC' => 'ETCO', // Event timing codes
                'GEO' => 'GEOB', // General encapsulated object
                'IPL' => 'IPLS', // Involved people list
                'LNK' => 'LINK', // Linked information
                'MCI' => 'MCDI', // Music CD identifier
                'MLL' => 'MLLT', // MPEG location lookup table
                'PIC' => 'APIC', // Attached picture
                'POP' => 'POPM', // Popularimeter
                'REV' => 'RVRB', // Reverb
                'RVA' => 'RVAD', // Relative volume adjustment
                'SLT' => 'SYLT', // Synchronised lyric/text
                'STC' => 'SYTC', // Synchronised tempo codes
                'TAL' => 'TALB', // Album/Movie/Show title
                'TBP' => 'TBPM', // BPM (beats per minute)
                'TCM' => 'TCOM', // Composer
                'TCO' => 'TCON', // Content type
                'TCP' => 'TCMP', // Part of a compilation
                'TCR' => 'TCOP', // Copyright message
                'TDA' => 'TDAT', // Date
                'TDY' => 'TDLY', // Playlist delay
                'TEN' => 'TENC', // Encoded by
                'TFT' => 'TFLT', // File type
                'TIM' => 'TIME', // Time
                'TKE' => 'TKEY', // Initial key
                'TLA' => 'TLAN', // Language(s)
                'TLE' => 'TLEN', // Length
                'TMT' => 'TMED', // Media type
                'TOA' => 'TOPE', // Original artist(s)/performer(s)
                'TOF' => 'TOFN', // Original filename
                'TOL' => 'TOLY', // Original lyricist(s)/text writer(s)
                'TOR' => 'TORY', // Original release year
                'TOT' => 'TOAL', // Original album/movie/show title
                'TP1' => 'TPE1', // Lead performer(s)/Soloist(s)
                'TP2' => 'TPE2', // Band/orchestra/accompaniment
                'TP3' => 'TPE3', // Conductor/performer refinement
                'TP4' => 'TPE4', // Interpreted, remixed, or otherwise modified by
                'TPA' => 'TPOS', // Part of a set
                'TPB' => 'TPUB', // Publisher
                'TRC' => 'TSRC', // ISRC (international standard recording code)
                'TRD' => 'TRDA', // Recording dates
                'TRK' => 'TRCK', // Track number/Position in set
                'TS2' => 'TSO2', // Album-Artist sort order
                'TSA' => 'TSOA', // Album sort order
                'TSC' => 'TSOC', // Composer sort order
                'TSI' => 'TSIZ', // Size
                'TSP' => 'TSOP', // Performer sort order
                'TSS' => 'TSSE', // Software/Hardware and settings used for encoding
                'TST' => 'TSOT', // Title sort order
                'TT1' => 'TIT1', // Content group description
                'TT2' => 'TIT2', // Title/songname/content description
                'TT3' => 'TIT3', // Subtitle/Description refinement
                'TXT' => 'TEXT', // Lyricist/Text writer
                'TXX' => 'TXXX', // User defined text information frame
                'TYE' => 'TYER', // Year
                'UFI' => 'UFID', // Unique file identifier
                'ULT' => 'USLT', // Unsynchronised lyric/text transcription
                'WAF' => 'WOAF', // Official audio file webpage
                'WAR' => 'WOAR', // Official artist/performer webpage
                'WAS' => 'WOAS', // Official audio source webpage
                'WCM' => 'WCOM', // Commercial information
                'WCP' => 'WCOP', // Copyright/Legal information
                'WPB' => 'WPUB', // Publishers official webpage
                'WXX' => 'WXXX', // User defined URL link frame
            );
            if (strlen($frame_name) === 4) {
                if (($frame_name[3] === ' ') || ($frame_name[3] === "\x00")) {
                    if (isset($ID3v22_iTunes_BrokenFrames[substr($frame_name, 0, 3)])) {
                        return $ID3v22_iTunes_BrokenFrames[substr($frame_name, 0, 3)];
                    }
                }
            }
            return false;
        }
    }
}else{die;}