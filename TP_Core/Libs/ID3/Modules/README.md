### TP_Core/Libs/ID3/Modules

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- getid3_ac3.php: extends getid3_handler	
	* private $AC3header, $BSIoffset; 
	* public const SYNC_WORD_THREE
	* public function Analyze():bool 
	* readHeaderBSI($length):int 
	* sampleRateCodeLookup($fscod) static
	* sampleRateCodeLookup2($fscod2) static
	* serviceTypeLookup($bsmod, $acmod) static
	* audioCodingModeLookup($acmod) static
	* centerMixLevelLookup($cmixlev) static
	* surroundMixLevelLookup($surmixlev) static
	* dolbySurroundModeLookup($dsurmod) static 
	* channelsEnabledLookup($acmod, $lfeon):array static 
	* heavyCompression($compre) static 
	* roomTypeLookup($roomtyp) static 
	* frameSizeLookup($frmsizecod, $fscod) static 
	* bitrateLookup($frmsizecod) static 
	* blocksPerSyncFrame($numblkscod) 

- getid3_apetag.php: extends getid3_handler	
	* public $inline_attachments, $overrideendoffset; 
	* Analyze():bool 
	* parseAPEheaderFooter($APEheaderFooterData) 
	* parseAPEtagFlags($rawflagint):array 
	* APEcontentTypeFlagLookup($contenttypeid) 
	* APEtagItemIsUTF8Lookup($itemkey):bool 

- getid3_asf.php: extends getid3_handler	
	* __construct(getID3 $getid3) 
	* Analyze():bool 
	* codecListObjectTypeLookup($CodecListType):string static 
	* KnownGUIDs():array static 
	* GUIDname($GUIDstring) static
	* ASFIndexObjectIndexTypeLookup($id) static
	* GUIDtoBytestring($GUIDstring) static
	* BytestringToGUID($Bytestring) static
	* FILETIMEtoUNIXtime($FILETIME, $round=true) static
	* WMpictureTypeLookup($WMpictureType) static
	* HeaderExtensionObjectDataParse(&$asf_header_extension_object_data, &$unhandled_sections): array 
	* metadataLibraryObjectDataTypeLookup($id) static 
	* ASF_WMpicture(&$data):string 
	* TrimConvert($string) static
	* TrimTerm($string) static

- getid3_dts.php: extends getid3_handler	
	* public const SYNC_WORD_TWO 
	* private $readBinDataOffset 
	* public static $syncwords 
	* Analyze():bool 
	* readBinData($bin, $length):int private
	* bitrateLookup($index) static
	* sampleRateLookup($index) static 
	* bitPerSampleLookup($index) static 
	* numChannelsLookup($index) static 
	* channelArrangementLookup($index):string static
	* dialogNormalization($index, $version) static

- getid3_flac.php: extends getid3_handler 	
	* public const SYNC_WORD 
	* Analyze():bool 
	* parseMETAdata():bool 
	* parseSTREAMINFOdata($BlockData):array private static 
	* parseSTREAMINFO($BlockData):bool private 
	* parseAPPLICATION($BlockData):bool private 
	* parseSEEKTABLE($BlockData):bool private 
	* parseVORBIS_COMMENT($BlockData):bool private 
	* parseCUESHEET($BlockData):bool private 
	* parsePICTURE():bool 
	* metaBlockTypeLookup($blocktype):string static 
	* applicationIDLookup($applicationid):string static 
	* pictureTypeLookup($type_id):string static 

- getid3_flv.php: extends getid3_handler 	
	* public $flv_framecount 
	* Analyze():bool 
	* audioFormatLookup($id) static 
	* audioRateLookup($id) static 
	* audioBitDepthLookup($id) static 
	* videoCodecLookup($id) static 

- getid3_id3v1.php: extends getid3_handler 	
	* Analyze():bool 
	* cutfield($str):string static 
	* ArrayOfGenres($allowSCMPXextended=false):string static 
	* LookupGenreName($genreid, $allowSCMPXextended=true) static 
	* LookupGenreID($genre, $allowSCMPXextended=false) static 
	* StandardiseID3v1GenreName($OriginalGenre) static 
	* GenerateID3v1Tag($title, $artist, $album, $year, $genreid, $comment, $track=''):string static 

- getid3_id3v2.php:  extends getid3_handler	
	* public $StartingOffset 
	* Analyze():bool 
	* ParseID3v2GenreString($genrestring):array 
	* ParseID3v2Frame(&$parsedFrame):bool 
	* DeUnsynchronise($data):string 
	* LookupExtendedHeaderRestrictionsTagSizeLimits($index):string 
	* LookupExtendedHeaderRestrictionsTextEncodings($index):string 
	* LookupExtendedHeaderRestrictionsTextFieldSize($index):string 
	* LookupExtendedHeaderRestrictionsImageEncoding($index):string 
	* LookupExtendedHeaderRestrictionsImageSizeSize($index):string 
	* LookupCurrencyUnits($currencyid):string 
	* LookupCurrencyCountry($currencyid):string 
	* LanguageLookup($languagecode, $casesensitive=false):string static
	* ETCOEventLookup($index):string static 
	* SYTLContentTypeLookup($index):string static 
	* APICPictureTypeLookup($index, $returnarray=false):string static 
	* COMRReceivedAsLookup($index):string static 
	* RVA2ChannelTypeLookup($index):string static 
	* FrameNameLongLookup($framename):string static  
	* FrameNameShortLookup($framename):string static  
	* TextEncodingTerminatorLookup($encoding):string static   
	* TextEncodingNameLookup($encoding):string static  
	* RemoveStringTerminator($string, $terminator):string static  
	* MakeUTF16emptyStringEmpty($string):string static  
	* IsValidID3v2FrameName($framename, $id3v2majorversion) static  
	* IsANumber($numberstring, $allowdecimal=false, $allownegative=false):bool static  
	* IsValidDateStampString($datestamp):bool static  
	* ID3v2HeaderLength($majorversion):int static  
	* ID3v22iTunesBrokenFrameName($frame_name) static  

- getid3_lyrics3.php:  extends getid3_handler	
	* Analyze():bool 
	* getLyrics3Data($endoffset, $version, $length):bool 
	* Lyrics3Timestamp2Seconds($rawtimestamp) 
	* Lyrics3LyricsTimestampParse(&$Lyrics3data):bool 
	* IntString2Bool($char):bool 

- getid3_matroska.php:  extends getid3_handler	
	* public $hide_clusters, $parse_whole_file 
	* private $EBMLbuffer, $EBMLbuffer_offset, $EBMLbuffer_length, $current_offset, $unuseful_elements 
	* Analyze():bool 
	* parseEBML(&$info):void private 
	* EnsureBufferHasEnoughData($min_data=1024):bool private 
	* readEBMLint() private 
	* readEBMLelementData($length, $check_buffer=false) private 
	* getEBMLelement(&$element, $parent_end, $get_data=false):bool private 
	* unhandledElement($type, $line, $element):void private 
	* ExtractCommentsSimpleTag($SimpleTagArray):bool private 
	* HandleEMBLSimpleTag($parent_end):array private
	* HandleEMBLClusterBlock($element, $block_type, &$info):array private 
	* EBML2Int($EBMLstring) private static 
	* EBMLdate2unix($EBMLdatestamp):float private static
	* TargetTypeValue($target_type) static 
	* BlockLacingType($lacingtype) static 
	* CodecIDtoCommonName($codecid):string static 
	* EBMLidName($value):string private static 
	* static function displayUnit($value):string static 
	* getDefaultStreamInfo($streams):array private static 

- getid3_mp3.php: extends getid3_handler 	
	* public $allow_bruteforce, $mp3_valid_check_frames 
	* Analyze():bool 
	* GuessEncoderOptions():string 
	* decodeMPEGaudioHeader($offset, &$info, $recursivesearch=true, $ScanAsCBR=false, $FastMPEGheaderScan=false):bool 
	* RecursiveFrameScanning(&$offset, &$nextframetestoffset, $ScanAsCBR):bool 
	* FreeFormatFrameLength($offset, $deepscan=false) 
	* getOnlyMPEGaudioInfoBruteForce():bool 
	* getOnlyMPEGaudioInfo($avdataoffset, $BitrateHistogram=false):bool 
	* MPEGaudioVersionArray():array static
	* MPEGaudioLayerArray():array static 
	* MPEGaudioBitrateArray():array static 
	* MPEGaudioFrequencyArray():array static 
	* MPEGaudioChannelModeArray():array static 
	* MPEGaudioModeExtensionArray():array static 
	* MPEGaudioEmphasisArray():array static 
	* MPEGaudioHeaderBytesValid($head4, $allowBitrate15=false):bool static 
	* MPEGaudioHeaderValid($rawarray, $echoerrors=false, $allowBitrate15=false):bool static 
	* MPEGaudioHeaderDecode($Header4Bytes) static 
	* MPEGaudioFrameLength(&$bitrate, &$version, &$layer, $padding, &$samplerate) static 
	* ClosestStandardMP3Bitrate($bit_rate) static 
	* XingVBRidOffset($version, $channelmode):int static 
	* LAMEvbrMethodLookup($VBRmethodID):string static 
	* LAMEmiscStereoModeLookup($StereoModeID):string static 
	* LAMEmiscSourceSampleFrequencyLookup($SourceSampleFrequencyID):string static 
	* LAMEsurroundInfoLookup($SurroundInfoID):string static
	* LAMEpresetUsedLookup($LAMEtag):string static

- getid3_ogg.php: extends getid3_handler 	
	* Analyze():bool 
	* ParseVorbisPageHeader(&$filedata, &$filedataoffset, &$oggpageinfo):bool 
	* ParseOpusPageHeader(&$filedata, &$filedataoffset,&$oggpageinfo):bool 
	* ParseOggPageHeader() 
	* ParseVorbisComments():bool 
	* SpeexBandModeLookup($mode) static 
	* OggPageSegmentLength($OggInfoArray, $SegmentNumber=1):int static 
	* get_quality_from_nominal_bitrate($nominal_bitrate):float static 
	* TheoraColorSpace($colorspace_id):?string static 
	* TheoraPixelFormat($pixelformat_id):?string 

- getid3_quicktime.php: extends getid3_handler 	
	* public $ReturnAtomData,$ParseAllPossibleAtoms 
	* Analyze():bool 
	* QuicktimeParseAtom($atomname, $atomsize, $atom_data, $baseoffset, &$atomHierarchy, $ParseAllPossibleAtoms) 
	* quicktime_read_mp4_descr_length($data, &$offset):int 
	* QuicktimeLanguageLookup($languageid):string 
	* QuicktimeVideoCodecLookup($codecid):string 
	* QuicktimeAudioCodecLookup($codecid) 
	* QuicktimeDCOMLookup($compressionid):string  
	* QuicktimeColorNameLookup($colordepthid):string 
	* QuicktimeSTIKLookup($stik):string 
	* QuicktimeIODSaudioProfileName($audio_profile_id):string 
	* QuicktimeIODSvideoProfileName($video_profile_id):string 
	* QuicktimeContentRatingLookup($rtng):string 
	* QuicktimeStoreAccountTypeLookup($akid):string 
	* QuicktimeStoreFrontCodeLookup($sfid):string 
	* CopyToAppropriateCommentsSection($keyname, $data, $boxname=''):bool 
	* LociString($lstring, &$count):string 
	* NoNullString($nullterminatedstring):string 
	* Pascal2String($pascalstring):string 
	* MaybePascal2String($pascalstring):string 
	* search_tag_by_key($info, $tag, $history, &$result):void 
	* search_tag_by_pair($info, $k, $v, $history, &$result):void 
	* quicktime_time_to_sample_table($info):bool 
	* quicktime_bookmark_time_scale($info):int 

- getid3_riff.php: extends getid3_handler 	
	* protected $container 
	* Analyze():bool 
	* ParseRIFFAMV($startoffset, $maxoffset) 
	* ParseRIFF($startoffset, $maxoffset) 
	* ParseRIFFdata(&$RIFFdata) :bool 
	* parseComments(&$RIFFinfoArray, &$CommentsTargetArray):bool static 
	* parseWAVEFORMATex($WaveFormatExData):array static 
	* parseWavPackHeader($WavPackChunkData):bool 
	* ParseBITMAPINFOHEADER($BITMAPINFOHEADER, $littleEndian=true):array static 
	* ParseDIVXTAG($DIVXTAG, $raw=false):array static 
	* waveSNDMtagLookup($tagshortname):string static
	* wFormatTagLookup($wFormatTag):string static 
	* fourccLookup($fourcc):string static 
	* EitherEndian2Int($byteword, $signed=false) private 

- test_module.php: 	
	* protected $_args, $_html; 
	* __construct(...$args) 
	* __to_string():string 
	* __toString() 
