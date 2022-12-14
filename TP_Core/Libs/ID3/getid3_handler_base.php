<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 22:00
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class getid3_handler_base{
        public $max_frames = 100000;
        public const MAGIC = 'FLV';
        public function __construct(){
            define('GETID3_FLV_TAG_AUDIO',          8);
            define('GETID3_FLV_TAG_VIDEO',          9);
            define('GETID3_FLV_TAG_META',          18);
            define('GETID3_FLV_VIDEO_H263',         2);
            define('GETID3_FLV_VIDEO_SCREEN',       3);
            define('GETID3_FLV_VIDEO_VP6FLV',       4);
            define('GETID3_FLV_VIDEO_VP6FLV_ALPHA', 5);
            define('GETID3_FLV_VIDEO_SCREENV2',     6);
            define('GETID3_FLV_VIDEO_H264',         7);
            define('H264_AVC_SEQUENCE_HEADER',          0);
            define('H264_PROFILE_BASELINE',            66);
            define('H264_PROFILE_MAIN',                77);
            define('H264_PROFILE_EXTENDED',            88);
            define('H264_PROFILE_HIGH',               100);
            define('H264_PROFILE_HIGH10',             110);
            define('H264_PROFILE_HIGH422',            122);
            define('H264_PROFILE_HIGH444',            144);
            define('H264_PROFILE_HIGH444_PREDICTIVE', 244);
            define('EBML_ID_CHAPTERS',                  0x0043A770); // [10][43][A7][70] -- A system to define basic menus and partition data. For more detailed information, look at the Chapters Explanation.
            define('EBML_ID_SEEKHEAD',                  0x014D9B74); // [11][4D][9B][74] -- Contains the position of other level 1 elements.
            define('EBML_ID_TAGS',                      0x0254C367); // [12][54][C3][67] -- Element containing elements specific to Tracks/Chapters. A list of valid tags can be found <http://www.matroska.org/technical/specs/tagging/index.html>.
            define('EBML_ID_INFO',                      0x0549A966); // [15][49][A9][66] -- Contains miscellaneous general information and statistics on the file.
            define('EBML_ID_TRACKS',                    0x0654AE6B); // [16][54][AE][6B] -- A top-level block of information with many tracks described.
            define('EBML_ID_SEGMENT',                   0x08538067); // [18][53][80][67] -- This element contains all other top-level (level 1) elements. Typically a Matroska file is composed of 1 segment.
            define('EBML_ID_ATTACHMENTS',               0x0941A469); // [19][41][A4][69] -- Contain attached files.
            define('EBML_ID_EBML',                      0x0A45DFA3); // [1A][45][DF][A3] -- Set the EBML characteristics of the data to follow. Each EBML document has to start with this.
            define('EBML_ID_CUES',                      0x0C53BB6B); // [1C][53][BB][6B] -- A top-level element to speed seeking access. All entries are local to the segment.
            define('EBML_ID_CLUSTER',                   0x0F43B675); // [1F][43][B6][75] -- The lower level element containing the (monolithic) Block structure.
            define('EBML_ID_LANGUAGE',                    0x02B59C); //     [22][B5][9C] -- Specifies the language of the track in the Matroska languages form.
            define('EBML_ID_TRACKTIMECODESCALE',          0x03314F); //     [23][31][4F] -- The scale to apply on this track to work at normal speed in relation with other tracks (mostly used to adjust video speed when the audio length differs).
            define('EBML_ID_DEFAULTDURATION',             0x03E383); //     [23][E3][83] -- Number of nanoseconds (i.e. not scaled) per frame.
            define('EBML_ID_CODECNAME',                   0x058688); //     [25][86][88] -- A human-readable string specifying the codec.
            define('EBML_ID_CODECDOWNLOADURL',            0x06B240); //     [26][B2][40] -- A URL to download about the codec used.
            define('EBML_ID_TIMECODESCALE',               0x0AD7B1); //     [2A][D7][B1] -- Timecode scale in nanoseconds (1.000.000 means all timecodes in the segment are expressed in milliseconds).
            define('EBML_ID_COLOURSPACE',                 0x0EB524); //     [2E][B5][24] -- Same value as in AVI (32 bits).
            define('EBML_ID_GAMMAVALUE',                  0x0FB523); //     [2F][B5][23] -- Gamma Value.
            define('EBML_ID_CODECSETTINGS',               0x1A9697); //     [3A][96][97] -- A string describing the encoding setting used.
            define('EBML_ID_CODECINFOURL',                0x1B4040); //     [3B][40][40] -- A URL to find information about the codec used.
            define('EBML_ID_PREVFILENAME',                0x1C83AB); //     [3C][83][AB] -- An escaped filename corresponding to the previous segment.
            define('EBML_ID_PREVUID',                     0x1CB923); //     [3C][B9][23] -- A unique ID to identify the previous chained segment (128 bits).
            define('EBML_ID_NEXTFILENAME',                0x1E83BB); //     [3E][83][BB] -- An escaped filename corresponding to the next segment.
            define('EBML_ID_NEXTUID',                     0x1EB923); //     [3E][B9][23] -- A unique ID to identify the next chained segment (128 bits).
            define('EBML_ID_CONTENTCOMPALGO',               0x0254); //         [42][54] -- The compression algorithm used. Algorithms that have been specified so far are:
            define('EBML_ID_CONTENTCOMPSETTINGS',           0x0255); //         [42][55] -- Settings that might be needed by the decompressor. For Header Stripping (ContentCompAlgo=3), the bytes that were removed from the beggining of each frames of the track.
            define('EBML_ID_DOCTYPE',                       0x0282); //         [42][82] -- A string that describes the type of document that follows this EBML header ('matroska' in our case).
            define('EBML_ID_DOCTYPEREADVERSION',            0x0285); //         [42][85] -- The minimum DocType version an interpreter has to support to read this file.
            define('EBML_ID_EBMLVERSION',                   0x0286); //         [42][86] -- The version of EBML parser used to create the file.
            define('EBML_ID_DOCTYPEVERSION',                0x0287); //         [42][87] -- The version of DocType interpreter used to create the file.
            define('EBML_ID_EBMLMAXIDLENGTH',               0x02F2); //         [42][F2] -- The maximum length of the IDs you'll find in this file (4 or less in Matroska).
            define('EBML_ID_EBMLMAXSIZELENGTH',             0x02F3); //         [42][F3] -- The maximum length of the sizes you'll find in this file (8 or less in Matroska). This does not override the element size indicated at the beginning of an element. Elements that have an indicated size which is larger than what is allowed by EBMLMaxSizeLength shall be considered invalid.
            define('EBML_ID_EBMLREADVERSION',               0x02F7); //         [42][F7] -- The minimum EBML version a parser has to support to read this file.
            define('EBML_ID_CHAPLANGUAGE',                  0x037C); //         [43][7C] -- The languages corresponding to the string, in the bibliographic ISO-639-2 form.
            define('EBML_ID_CHAPCOUNTRY',                   0x037E); //         [43][7E] -- The countries corresponding to the string, same 2 octets as in Internet domains.
            define('EBML_ID_SEGMENTFAMILY',                 0x0444); //         [44][44] -- A randomly generated unique ID that all segments related to each other must use (128 bits).
            define('EBML_ID_DATEUTC',                       0x0461); //         [44][61] -- Date of the origin of timecode (value 0), i.e. production date.
            define('EBML_ID_TAGLANGUAGE',                   0x047A); //         [44][7A] -- Specifies the language of the tag specified, in the Matroska languages form.
            define('EBML_ID_TAGDEFAULT',                    0x0484); //         [44][84] -- Indication to know if this is the default/original language to use for the given tag.
            define('EBML_ID_TAGBINARY',                     0x0485); //         [44][85] -- The values of the Tag if it is binary. Note that this cannot be used in the same SimpleTag as TagString.
            define('EBML_ID_TAGSTRING',                     0x0487); //         [44][87] -- The value of the Tag.
            define('EBML_ID_DURATION',                      0x0489); //         [44][89] -- Duration of the segment (based on TimecodeScale).
            define('EBML_ID_CHAPPROCESSPRIVATE',            0x050D); //         [45][0D] -- Some optional data attached to the ChapProcessCodecID information. For ChapProcessCodecID = 1, it is the "DVD level" equivalent.
            define('EBML_ID_CHAPTERFLAGENABLED',            0x0598); //         [45][98] -- Specify wether the chapter is enabled. It can be enabled/disabled by a Control Track. When disabled, the movie should skip all the content between the TimeStart and TimeEnd of this chapter.
            define('EBML_ID_TAGNAME',                       0x05A3); //         [45][A3] -- The name of the Tag that is going to be stored.
            define('EBML_ID_EDITIONENTRY',                  0x05B9); //         [45][B9] -- Contains all information about a segment edition.
            define('EBML_ID_EDITIONUID',                    0x05BC); //         [45][BC] -- A unique ID to identify the edition. It's useful for tagging an edition.
            define('EBML_ID_EDITIONFLAGHIDDEN',             0x05BD); //         [45][BD] -- If an edition is hidden (1), it should not be available to the user interface (but still to Control Tracks).
            define('EBML_ID_EDITIONFLAGDEFAULT',            0x05DB); //         [45][DB] -- If a flag is set (1) the edition should be used as the default one.
            define('EBML_ID_EDITIONFLAGORDERED',            0x05DD); //         [45][DD] -- Specify if the chapters can be defined multiple times and the order to play them is enforced.
            define('EBML_ID_FILEDATA',                      0x065C); //         [46][5C] -- The data of the file.
            define('EBML_ID_FILEMIMETYPE',                  0x0660); //         [46][60] -- MIME type of the file.
            define('EBML_ID_FILENAME',                      0x066E); //         [46][6E] -- Filename of the attached file.
            define('EBML_ID_FILEREFERRAL',                  0x0675); //         [46][75] -- A binary value that a track/codec can refer to when the attachment is needed.
            define('EBML_ID_FILEDESCRIPTION',               0x067E); //         [46][7E] -- A human-friendly name for the attached file.
            define('EBML_ID_FILEUID',                       0x06AE); //         [46][AE] -- Unique ID representing the file, as random as possible.
            define('EBML_ID_CONTENTENCALGO',                0x07E1); //         [47][E1] -- The encryption algorithm used. The value '0' means that the contents have not been encrypted but only signed. Predefined values:
            define('EBML_ID_CONTENTENCKEYID',               0x07E2); //         [47][E2] -- For public key algorithms this is the ID of the public key the data was encrypted with.
            define('EBML_ID_CONTENTSIGNATURE',              0x07E3); //         [47][E3] -- A cryptographic signature of the contents.
            define('EBML_ID_CONTENTSIGKEYID',               0x07E4); //         [47][E4] -- This is the ID of the private key the data was signed with.
            define('EBML_ID_CONTENTSIGALGO',                0x07E5); //         [47][E5] -- The algorithm used for the signature. A value of '0' means that the contents have not been signed but only encrypted. Predefined values:
            define('EBML_ID_CONTENTSIGHASHALGO',            0x07E6); //         [47][E6] -- The hash algorithm used for the signature. A value of '0' means that the contents have not been signed but only encrypted. Predefined values:
            define('EBML_ID_MUXINGAPP',                     0x0D80); //         [4D][80] -- Muxing application or library ("libmatroska-0.4.3").
            define('EBML_ID_SEEK',                          0x0DBB); //         [4D][BB] -- Contains a single seek entry to an EBML element.
            define('EBML_ID_CONTENTENCODINGORDER',          0x1031); //         [50][31] -- Tells when this modification was used during encoding/muxing starting with 0 and counting upwards. The decoder/demuxer has to start with the highest order number it finds and work its way down. This value has to be unique over all ContentEncodingOrder elements in the segment.
            define('EBML_ID_CONTENTENCODINGSCOPE',          0x1032); //         [50][32] -- A bit field that describes which elements have been modified in this way. Values (big endian) can be OR'ed. Possible values:
            define('EBML_ID_CONTENTENCODINGTYPE',           0x1033); //         [50][33] -- A value describing what kind of transformation has been done. Possible values:
            define('EBML_ID_CONTENTCOMPRESSION',            0x1034); //         [50][34] -- Settings describing the compression used. Must be present if the value of ContentEncodingType is 0 and absent otherwise. Each block must be decompressable even if no previous block is available in order not to prevent seeking.
            define('EBML_ID_CONTENTENCRYPTION',             0x1035); //         [50][35] -- Settings describing the encryption used. Must be present if the value of ContentEncodingType is 1 and absent otherwise.
            define('EBML_ID_CUEREFNUMBER',                  0x135F); //         [53][5F] -- Number of the referenced Block of Track X in the specified Cluster.
            define('EBML_ID_NAME',                          0x136E); //         [53][6E] -- A human-readable track name.
            define('EBML_ID_CUEBLOCKNUMBER',                0x1378); //         [53][78] -- Number of the Block in the specified Cluster.
            define('EBML_ID_TRACKOFFSET',                   0x137F); //         [53][7F] -- A value to add to the Block's Timecode. This can be used to adjust the playback offset of a track.
            define('EBML_ID_SEEKID',                        0x13AB); //         [53][AB] -- The binary ID corresponding to the element name.
            define('EBML_ID_SEEKPOSITION',                  0x13AC); //         [53][AC] -- The position of the element in the segment in octets (0 = first level 1 element).
            define('EBML_ID_STEREOMODE',                    0x13B8); //         [53][B8] -- Stereo-3D video mode.
            define('EBML_ID_OLDSTEREOMODE',                 0x13B9); //         [53][B9] -- Bogus StereoMode value used in old versions of libmatroska. DO NOT USE. (0: mono, 1: right eye, 2: left eye, 3: both eyes).
            define('EBML_ID_PIXELCROPBOTTOM',               0x14AA); //         [54][AA] -- The number of video pixels to remove at the bottom of the image (for HDTV content).
            define('EBML_ID_DISPLAYWIDTH',                  0x14B0); //         [54][B0] -- Width of the video frames to display.
            define('EBML_ID_DISPLAYUNIT',                   0x14B2); //         [54][B2] -- Type of the unit for DisplayWidth/Height (0: pixels, 1: centimeters, 2: inches).
            define('EBML_ID_ASPECTRATIOTYPE',               0x14B3); //         [54][B3] -- Specify the possible modifications to the aspect ratio (0: free resizing, 1: keep aspect ratio, 2: fixed).
            define('EBML_ID_DISPLAYHEIGHT',                 0x14BA); //         [54][BA] -- Height of the video frames to display.
            define('EBML_ID_PIXELCROPTOP',                  0x14BB); //         [54][BB] -- The number of video pixels to remove at the top of the image.
            define('EBML_ID_PIXELCROPLEFT',                 0x14CC); //         [54][CC] -- The number of video pixels to remove on the left of the image.
            define('EBML_ID_PIXELCROPRIGHT',                0x14DD); //         [54][DD] -- The number of video pixels to remove on the right of the image.
            define('EBML_ID_FLAGFORCED',                    0x15AA); //         [55][AA] -- Set if that track MUST be used during playback. There can be many forced track for a kind (audio, video or subs), the player should select the one which language matches the user preference or the default + forced track. Overlay MAY happen between a forced and non-forced track of the same kind.
            define('EBML_ID_MAXBLOCKADDITIONID',            0x15EE); //         [55][EE] -- The maximum value of BlockAddID. A value 0 means there is no BlockAdditions for this track.
            define('EBML_ID_WRITINGAPP',                    0x1741); //         [57][41] -- Writing application ("mkvmerge-0.3.3").
            define('EBML_ID_CLUSTERSILENTTRACKS',           0x1854); //         [58][54] -- The list of tracks that are not used in that part of the stream. It is useful when using overlay tracks on seeking. Then you should decide what track to use.
            define('EBML_ID_CLUSTERSILENTTRACKNUMBER',      0x18D7); //         [58][D7] -- One of the track number that are not used from now on in the stream. It could change later if not specified as silent in a further Cluster.
            define('EBML_ID_ATTACHEDFILE',                  0x21A7); //         [61][A7] -- An attached file.
            define('EBML_ID_CONTENTENCODING',               0x2240); //         [62][40] -- Settings for one content encoding like compression or encryption.
            define('EBML_ID_BITDEPTH',                      0x2264); //         [62][64] -- Bits per sample, mostly used for PCM.
            define('EBML_ID_CODECPRIVATE',                  0x23A2); //         [63][A2] -- Private data only known to the codec.
            define('EBML_ID_TARGETS',                       0x23C0); //         [63][C0] -- Contain all UIDs where the specified meta data apply. It is void to describe everything in the segment.
            define('EBML_ID_CHAPTERPHYSICALEQUIV',          0x23C3); //         [63][C3] -- Specify the physical equivalent of this ChapterAtom like "DVD" (60) or "SIDE" (50), see complete list of values.
            define('EBML_ID_TAGCHAPTERUID',                 0x23C4); //         [63][C4] -- A unique ID to identify the Chapter(s) the tags belong to. If the value is 0 at this level, the tags apply to all chapters in the Segment.
            define('EBML_ID_TAGTRACKUID',                   0x23C5); //         [63][C5] -- A unique ID to identify the Track(s) the tags belong to. If the value is 0 at this level, the tags apply to all tracks in the Segment.
            define('EBML_ID_TAGATTACHMENTUID',              0x23C6); //         [63][C6] -- A unique ID to identify the Attachment(s) the tags belong to. If the value is 0 at this level, the tags apply to all the attachments in the Segment.
            define('EBML_ID_TAGEDITIONUID',                 0x23C9); //         [63][C9] -- A unique ID to identify the EditionEntry(s) the tags belong to. If the value is 0 at this level, the tags apply to all editions in the Segment.
            define('EBML_ID_TARGETTYPE',                    0x23CA); //         [63][CA] -- An informational string that can be used to display the logical level of the target like "ALBUM", "TRACK", "MOVIE", "CHAPTER", etc (see TargetType).
            define('EBML_ID_TRACKTRANSLATE',                0x2624); //         [66][24] -- The track identification for the given Chapter Codec.
            define('EBML_ID_TRACKTRANSLATETRACKID',         0x26A5); //         [66][A5] -- The binary value used to represent this track in the chapter codec data. The format depends on the ChapProcessCodecID used.
            define('EBML_ID_TRACKTRANSLATECODEC',           0x26BF); //         [66][BF] -- The chapter codec using this ID (0: Matroska Script, 1: DVD-menu).
            define('EBML_ID_TRACKTRANSLATEEDITIONUID',      0x26FC); //         [66][FC] -- Specify an edition UID on which this translation applies. When not specified, it means for all editions found in the segment.
            define('EBML_ID_SIMPLETAG',                     0x27C8); //         [67][C8] -- Contains general information about the target.
            define('EBML_ID_TARGETTYPEVALUE',               0x28CA); //         [68][CA] -- A number to indicate the logical level of the target (see TargetType).
            define('EBML_ID_CHAPPROCESSCOMMAND',            0x2911); //         [69][11] -- Contains all the commands associated to the Atom.
            define('EBML_ID_CHAPPROCESSTIME',               0x2922); //         [69][22] -- Defines when the process command should be handled (0: during the whole chapter, 1: before starting playback, 2: after playback of the chapter).
            define('EBML_ID_CHAPTERTRANSLATE',              0x2924); //         [69][24] -- A tuple of corresponding ID used by chapter codecs to represent this segment.
            define('EBML_ID_CHAPPROCESSDATA',               0x2933); //         [69][33] -- Contains the command information. The data should be interpreted depending on the ChapProcessCodecID value. For ChapProcessCodecID = 1, the data correspond to the binary DVD cell pre/post commands.
            define('EBML_ID_CHAPPROCESS',                   0x2944); //         [69][44] -- Contains all the commands associated to the Atom.
            define('EBML_ID_CHAPPROCESSCODECID',            0x2955); //         [69][55] -- Contains the type of the codec used for the processing. A value of 0 means native Matroska processing (to be defined), a value of 1 means the DVD command set is used. More codec IDs can be added later.
            define('EBML_ID_CHAPTERTRANSLATEID',            0x29A5); //         [69][A5] -- The binary value used to represent this segment in the chapter codec data. The format depends on the ChapProcessCodecID used.
            define('EBML_ID_CHAPTERTRANSLATECODEC',         0x29BF); //         [69][BF] -- The chapter codec using this ID (0: Matroska Script, 1: DVD-menu).
            define('EBML_ID_CHAPTERTRANSLATEEDITIONUID',    0x29FC); //         [69][FC] -- Specify an edition UID on which this correspondance applies. When not specified, it means for all editions found in the segment.
            define('EBML_ID_CONTENTENCODINGS',              0x2D80); //         [6D][80] -- Settings for several content encoding mechanisms like compression or encryption.
            define('EBML_ID_MINCACHE',                      0x2DE7); //         [6D][E7] -- The minimum number of frames a player should be able to cache during playback. If set to 0, the reference pseudo-cache system is not used.
            define('EBML_ID_MAXCACHE',                      0x2DF8); //         [6D][F8] -- The maximum cache size required to store referenced frames in and the current frame. 0 means no cache is needed.
            define('EBML_ID_CHAPTERSEGMENTUID',             0x2E67); //         [6E][67] -- A segment to play in place of this chapter. Edition ChapterSegmentEditionUID should be used for this segment, otherwise no edition is used.
            define('EBML_ID_CHAPTERSEGMENTEDITIONUID',      0x2EBC); //         [6E][BC] -- The edition to play from the segment linked in ChapterSegmentUID.
            define('EBML_ID_TRACKOVERLAY',                  0x2FAB); //         [6F][AB] -- Specify that this track is an overlay track for the Track specified (in the u-integer). That means when this track has a gap (see SilentTracks) the overlay track should be used instead. The order of multiple TrackOverlay matters, the first one is the one that should be used. If not found it should be the second, etc.
            define('EBML_ID_TAG',                           0x3373); //         [73][73] -- Element containing elements specific to Tracks/Chapters.
            define('EBML_ID_SEGMENTFILENAME',               0x3384); //         [73][84] -- A filename corresponding to this segment.
            define('EBML_ID_SEGMENTUID',                    0x33A4); //         [73][A4] -- A randomly generated unique ID to identify the current segment between many others (128 bits).
            define('EBML_ID_CHAPTERUID',                    0x33C4); //         [73][C4] -- A unique ID to identify the Chapter.
            define('EBML_ID_TRACKUID',                      0x33C5); //         [73][C5] -- A unique ID to identify the Track. This should be kept the same when making a direct stream copy of the Track to another file.
            define('EBML_ID_ATTACHMENTLINK',                0x3446); //         [74][46] -- The UID of an attachment that is used by this codec.
            define('EBML_ID_CLUSTERBLOCKADDITIONS',         0x35A1); //         [75][A1] -- Contain additional blocks to complete the main one. An EBML parser that has no knowledge of the Block structure could still see and use/skip these data.
            define('EBML_ID_CHANNELPOSITIONS',              0x347B); //         [7D][7B] -- Table of horizontal angles for each successive channel, see appendix.
            define('EBML_ID_OUTPUTSAMPLINGFREQUENCY',       0x38B5); //         [78][B5] -- Real output sampling frequency in Hz (used for SBR techniques).
            define('EBML_ID_TITLE',                         0x3BA9); //         [7B][A9] -- General name of the segment.
            define('EBML_ID_CHAPTERDISPLAY',                  0x00); //             [80] -- Contains all possible strings to use for the chapter display.
            define('EBML_ID_TRACKTYPE',                       0x03); //             [83] -- A set of track types coded on 8 bits (1: video, 2: audio, 3: complex, 0x10: logo, 0x11: subtitle, 0x12: buttons, 0x20: control).
            define('EBML_ID_CHAPSTRING',                      0x05); //             [85] -- Contains the string to use as the chapter atom.
            define('EBML_ID_CODECID',                         0x06); //             [86] -- An ID corresponding to the codec, see the codec page for more info.
            define('EBML_ID_FLAGDEFAULT',                     0x08); //             [88] -- Set if that track (audio, video or subs) SHOULD be used if no language found matches the user preference.
            define('EBML_ID_CHAPTERTRACKNUMBER',              0x09); //             [89] -- UID of the Track to apply this chapter too. In the absense of a control track, choosing this chapter will select the listed Tracks and deselect unlisted tracks. Absense of this element indicates that the Chapter should be applied to any currently used Tracks.
            define('EBML_ID_CLUSTERSLICES',                   0x0E); //             [8E] -- Contains slices description.
            define('EBML_ID_CHAPTERTRACK',                    0x0F); //             [8F] -- List of tracks on which the chapter applies. If this element is not present, all tracks apply
            define('EBML_ID_CHAPTERTIMESTART',                0x11); //             [91] -- Timecode of the start of Chapter (not scaled).
            define('EBML_ID_CHAPTERTIMEEND',                  0x12); //             [92] -- Timecode of the end of Chapter (timecode excluded, not scaled).
            define('EBML_ID_CUEREFTIME',                      0x16); //             [96] -- Timecode of the referenced Block.
            define('EBML_ID_CUEREFCLUSTER',                   0x17); //             [97] -- Position of the Cluster containing the referenced Block.
            define('EBML_ID_CHAPTERFLAGHIDDEN',               0x18); //             [98] -- If a chapter is hidden (1), it should not be available to the user interface (but still to Control Tracks).
            define('EBML_ID_FLAGINTERLACED',                  0x1A); //             [9A] -- Set if the video is interlaced.
            define('EBML_ID_CLUSTERBLOCKDURATION',            0x1B); //             [9B] -- The duration of the Block (based on TimecodeScale). This element is mandatory when DefaultDuration is set for the track. When not written and with no DefaultDuration, the value is assumed to be the difference between the timecode of this Block and the timecode of the next Block in "display" order (not coding order). This element can be useful at the end of a Track (as there is not other Block available), or when there is a break in a track like for subtitle tracks.
            define('EBML_ID_FLAGLACING',                      0x1C); //             [9C] -- Set if the track may contain blocks using lacing.
            define('EBML_ID_CHANNELS',                        0x1F); //             [9F] -- Numbers of channels in the track.
            define('EBML_ID_CLUSTERBLOCKGROUP',               0x20); //             [A0] -- Basic container of information containing a single Block or BlockVirtual, and information specific to that Block/VirtualBlock.
            define('EBML_ID_CLUSTERBLOCK',                    0x21); //             [A1] -- Block containing the actual data to be rendered and a timecode relative to the Cluster Timecode.
            define('EBML_ID_CLUSTERBLOCKVIRTUAL',             0x22); //             [A2] -- A Block with no data. It must be stored in the stream at the place the real Block should be in display order.
            define('EBML_ID_CLUSTERSIMPLEBLOCK',              0x23); //             [A3] -- Similar to Block but without all the extra information, mostly used to reduced overhead when no extra feature is needed.
            define('EBML_ID_CLUSTERCODECSTATE',               0x24); //             [A4] -- The new codec state to use. Data interpretation is private to the codec. This information should always be referenced by a seek entry.
            define('EBML_ID_CLUSTERBLOCKADDITIONAL',          0x25); //             [A5] -- Interpreted by the codec as it wishes (using the BlockAddID).
            define('EBML_ID_CLUSTERBLOCKMORE',                0x26); //             [A6] -- Contain the BlockAdditional and some parameters.
            define('EBML_ID_CLUSTERPOSITION',                 0x27); //             [A7] -- Position of the Cluster in the segment (0 in live broadcast streams). It might help to resynchronise offset on damaged streams.
            define('EBML_ID_CODECDECODEALL',                  0x2A); //             [AA] -- The codec can decode potentially damaged data.
            define('EBML_ID_CLUSTERPREVSIZE',                 0x2B); //             [AB] -- Size of the previous Cluster, in octets. Can be useful for backward playing.
            define('EBML_ID_TRACKENTRY',                      0x2E); //             [AE] -- Describes a track with all elements.
            define('EBML_ID_CLUSTERENCRYPTEDBLOCK',           0x2F); //             [AF] -- Similar to SimpleBlock but the data inside the Block are Transformed (encrypt and/or signed).
            define('EBML_ID_PIXELWIDTH',                      0x30); //             [B0] -- Width of the encoded video frames in pixels.
            define('EBML_ID_CUETIME',                         0x33); //             [B3] -- Absolute timecode according to the segment time base.
            define('EBML_ID_SAMPLINGFREQUENCY',               0x35); //             [B5] -- Sampling frequency in Hz.
            define('EBML_ID_CHAPTERATOM',                     0x36); //             [B6] -- Contains the atom information to use as the chapter atom (apply to all tracks).
            define('EBML_ID_CUETRACKPOSITIONS',               0x37); //             [B7] -- Contain positions for different tracks corresponding to the timecode.
            define('EBML_ID_FLAGENABLED',                     0x39); //             [B9] -- Set if the track is used.
            define('EBML_ID_PIXELHEIGHT',                     0x3A); //             [BA] -- Height of the encoded video frames in pixels.
            define('EBML_ID_CUEPOINT',                        0x3B); //             [BB] -- Contains all information relative to a seek point in the segment.
            define('EBML_ID_CRC32',                           0x3F); //             [BF] -- The CRC is computed on all the data of the Master element it's in, regardless of its position. It's recommended to put the CRC value at the beggining of the Master element for easier reading. All level 1 elements should include a CRC-32.
            define('EBML_ID_CLUSTERBLOCKADDITIONID',          0x4B); //             [CB] -- The ID of the BlockAdditional element (0 is the main Block).
            define('EBML_ID_CLUSTERLACENUMBER',               0x4C); //             [CC] -- The reverse number of the frame in the lace (0 is the last frame, 1 is the next to last, etc). While there are a few files in the wild with this element, it is no longer in use and has been deprecated. Being able to interpret this element is not required for playback.
            define('EBML_ID_CLUSTERFRAMENUMBER',              0x4D); //             [CD] -- The number of the frame to generate from this lace with this delay (allow you to generate many frames from the same Block/Frame).
            define('EBML_ID_CLUSTERDELAY',                    0x4E); //             [CE] -- The (scaled) delay to apply to the element.
            define('EBML_ID_CLUSTERDURATION',                 0x4F); //             [CF] -- The (scaled) duration to apply to the element.
            define('EBML_ID_TRACKNUMBER',                     0x57); //             [D7] -- The track number as used in the Block Header (using more than 127 tracks is not encouraged, though the design allows an unlimited number).
            define('EBML_ID_CUEREFERENCE',                    0x5B); //             [DB] -- The Clusters containing the required referenced Blocks.
            define('EBML_ID_VIDEO',                           0x60); //             [E0] -- Video settings.
            define('EBML_ID_AUDIO',                           0x61); //             [E1] -- Audio settings.
            define('EBML_ID_CLUSTERTIMESLICE',                0x68); //             [E8] -- Contains extra time information about the data contained in the Block. While there are a few files in the wild with this element, it is no longer in use and has been deprecated. Being able to interpret this element is not required for playback.
            define('EBML_ID_CUECODECSTATE',                   0x6A); //             [EA] -- The position of the Codec State corresponding to this Cue element. 0 means that the data is taken from the initial Track Entry.
            define('EBML_ID_CUEREFCODECSTATE',                0x6B); //             [EB] -- The position of the Codec State corresponding to this referenced element. 0 means that the data is taken from the initial Track Entry.
            define('EBML_ID_VOID',                            0x6C); //             [EC] -- Used to void damaged data, to avoid unexpected behaviors when using damaged data. The content is discarded. Also used to reserve space in a sub-element for later use.
            define('EBML_ID_CLUSTERTIMECODE',                 0x67); //             [E7] -- Absolute timecode of the cluster (based on TimecodeScale).
            define('EBML_ID_CLUSTERBLOCKADDID',               0x6E); //             [EE] -- An ID to identify the BlockAdditional level.
            define('EBML_ID_CUECLUSTERPOSITION',              0x71); //             [F1] -- The position of the Cluster containing the required Block.
            define('EBML_ID_CUETRACK',                        0x77); //             [F7] -- The track for which a position is given.
            define('EBML_ID_CLUSTERREFERENCEPRIORITY',        0x7A); //             [FA] -- This frame is referenced and has the specified cache priority. In cache only a frame of the same or higher priority can replace this frame. A value of 0 means the frame is not referenced.
            define('EBML_ID_CLUSTERREFERENCEBLOCK',           0x7B); //             [FB] -- Timecode of another frame used as a reference (ie: B or P frame). The timecode is relative to the block it's attached to.
            define('EBML_ID_CLUSTERREFERENCEVIRTUAL',         0x7D); //             [FD] -- Relative position of the data that should be in position of the virtual block.
        }
    }
}else{die;}