<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Inits;
if(ABSPATH){
    trait _init_formats{
        protected $_regex;
        protected $_tags;
        protected $_tp_cockney_replace;
        protected $_special_chars = [ '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', '’', '«', '»', '”', '“'];
        protected $_double_chars_out = ['OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th'];
        protected $_double_chars_inn = ["\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe" ];
        protected $_chars_in = "\x80\x83\x8a\x8e\x9a\x9e"
        . "\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
        . "\xc3\xc4\xc5\xc7\xc8\xc9\xca"
        . "\xcb\xcc\xcd\xce\xcf\xd1\xd2"
        . "\xd3\xd4\xd5\xd6\xd8\xd9\xda"
        . "\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
        . "\xe4\xe5\xe7\xe8\xe9\xea\xeb"
        . "\xec\xed\xee\xef\xf1\xf2\xf3"
        . "\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
        . "\xfc\xfd\xff";
        protected $_chars = [
            // Decompositions for Latin-1 Supplement.
            'ª' => 'a','º' => 'o','À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A',
            'Å' => 'A','Æ' => 'AE','Ç' => 'C','È' => 'E','É' => 'E','Ê' => 'E','Ë' => 'E',
            'Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I','Ð' => 'D','Ñ' => 'N','Ò' => 'O',
            'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ö' => 'O','Ù' => 'U','Ú' => 'U','Û' => 'U',
            'Ü' => 'U','Ý' => 'Y','Þ' => 'TH','ß' => 's','à' => 'a','á' => 'a','â' => 'a',
            'ã' => 'a','ä' => 'a','å' => 'a','æ' => 'ae','ç' => 'c','è' => 'e','é' => 'e',
            'ê' => 'e','ë' => 'e','ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ð' => 'd',
            'ñ' => 'n','ò' => 'o','ó' => 'o','ô' => 'o','õ' => 'o','ö' => 'o','ø' => 'o',
            'ù' => 'u','ú' => 'u','û' => 'u','ü' => 'u','ý' => 'y','þ' => 'th','ÿ' => 'y',
            'Ø' => 'O',
            // Decompositions for Latin Extended-A.
            'Ā' => 'A','ā' => 'a','Ă' => 'A','ă' => 'a','Ą' => 'A','ą' => 'a','Ć' => 'C',
            'ć' => 'c','Ĉ' => 'C','ĉ' => 'c','Ċ' => 'C','ċ' => 'c','Č' => 'C','č' => 'c',
            'Ď' => 'D','ď' => 'd','Đ' => 'D','đ' => 'd','Ē' => 'E','ē' => 'e','Ĕ' => 'E',
            'ĕ' => 'e','Ė' => 'E','ė' => 'e','Ę' => 'E','ę' => 'e','Ě' => 'E','ě' => 'e',
            'Ĝ' => 'G','ĝ' => 'g','Ğ' => 'G','ğ' => 'g','Ġ' => 'G','ġ' => 'g','Ģ' => 'G',
            'ģ' => 'g','Ĥ' => 'H','ĥ' => 'h','Ħ' => 'H','ħ' => 'h','Ĩ' => 'I','ĩ' => 'i',
            'Ī' => 'I','ī' => 'i','Ĭ' => 'I','ĭ' => 'i','Į' => 'I','į' => 'i','İ' => 'I',
            'ı' => 'i','Ĳ' => 'IJ','ĳ' => 'ij','Ĵ' => 'J','ĵ' => 'j','Ķ' => 'K','ķ' => 'k',
            'ĸ' => 'k','Ĺ' => 'L','ĺ' => 'l','Ļ' => 'L','ļ' => 'l','Ľ' => 'L','ľ' => 'l',
            'Ŀ' => 'L','ŀ' => 'l','Ł' => 'L','ł' => 'l','Ń' => 'N','ń' => 'n','Ņ' => 'N',
            'ņ' => 'n','Ň' => 'N','ň' => 'n','ŉ' => 'n','Ŋ' => 'N','ŋ' => 'n','Ō' => 'O',
            'ō' => 'o','Ŏ' => 'O','ŏ' => 'o','Ő' => 'O','ő' => 'o','Œ' => 'OE','œ' => 'oe',
            'Ŕ' => 'R','ŕ' => 'r','Ŗ' => 'R','ŗ' => 'r','Ř' => 'R','ř' => 'r','Ś' => 'S',
            'ś' => 's','Ŝ' => 'S','ŝ' => 's','Ş' => 'S','ş' => 's','Š' => 'S','š' => 's',
            'Ţ' => 'T','ţ' => 't','Ť' => 'T','ť' => 't','Ŧ' => 'T','ŧ' => 't','Ũ' => 'U',
            'ũ' => 'u','Ū' => 'U','ū' => 'u','Ŭ' => 'U','ŭ' => 'u','Ů' => 'U','ů' => 'u',
            'Ű' => 'U','ű' => 'u','Ų' => 'U','ų' => 'u','Ŵ' => 'W','ŵ' => 'w','Ŷ' => 'Y',
            'ŷ' => 'y','Ÿ' => 'Y','Ź' => 'Z','ź' => 'z','Ż' => 'Z','ż' => 'z','Ž' => 'Z',
            'ž' => 'z','ſ' => 's',
            'Ș' => 'S','ș' => 's','Ț' => 'T','ț' => 't',// Decompositions for Latin Extended-B.
            '€' => 'E',// Euro sign.
            '£' => '', // GBP (Pound) sign.
            'Ơ' => 'O','ơ' => 'o','Ư' => 'U','ư' => 'u',// Vowels with diacritic (Vietnamese). Unmarked.
            // Grave accent.
            'Ầ' => 'A','ầ' => 'a','Ằ' => 'A','ằ' => 'a','Ề' => 'E','ề' => 'e','Ồ' => 'O','ồ' => 'o',
            'Ờ' => 'O','ờ' => 'o','Ừ' => 'U','ừ' => 'u','Ỳ' => 'Y','ỳ' => 'y',
            // Hook.
            'Ả' => 'A','ả' => 'a','Ẩ' => 'A','ẩ' => 'a','Ẳ' => 'A','ẳ' => 'a','Ẻ' => 'E','ẻ' => 'e',
            'Ể' => 'E','ể' => 'e','Ỉ' => 'I','ỉ' => 'i','Ỏ' => 'O','ỏ' => 'o','Ổ' => 'O','ổ' => 'o',
            'Ở' => 'O','ở' => 'o','Ủ' => 'U','ủ' => 'u','Ử' => 'U','ử' => 'u','Ỷ' => 'Y','ỷ' => 'y',
            // Tilde.
            'Ẫ' => 'A','ẫ' => 'a','Ẵ' => 'A','ẵ' => 'a','Ẽ' => 'E','ẽ' => 'e','Ễ' => 'E','ễ' => 'e',
            'Ỗ' => 'O','ỗ' => 'o','Ỡ' => 'O','ỡ' => 'o','Ữ' => 'U','ữ' => 'u','Ỹ' => 'Y','ỹ' => 'y',
            // Acute accent.
            'Ấ' => 'A','ấ' => 'a','Ắ' => 'A','ắ' => 'a','Ế' => 'E','ế' => 'e','Ố' => 'O','ố' => 'o',
            'Ớ' => 'O','ớ' => 'o','Ứ' => 'U','ứ' => 'u',
            // Dot below.
            'Ạ' => 'A','ạ' => 'a','Ậ' => 'A','ậ' => 'a','Ặ' => 'A','ặ' => 'a','Ẹ' => 'E','ẹ' => 'e',
            'Ệ' => 'E','ệ' => 'e','Ị' => 'I','ị' => 'i','Ọ' => 'O','ọ' => 'o','Ộ' => 'O','ộ' => 'o',
            'Ợ' => 'O','ợ' => 'o','Ụ' => 'U','ụ' => 'u','Ự' => 'U','ự' => 'u','Ỵ' => 'Y','ỵ' => 'y',
            'ɑ' => 'a', // Vowels with diacritic (Chinese, Hanyu Pinyin).
            'ǖ' => 'u','Ǖ' => 'U',// Macron.
            'ǘ' => 'u','Ǘ' => 'U',// Acute accent.
            // Caron.
            'Ǎ' => 'A','ǎ' => 'a','Ǐ' => 'I','ǐ' => 'i','Ǒ' => 'O','ǒ' => 'o',
            'Ǔ' => 'U','ǔ' => 'u','Ǚ' => 'U','ǚ' => 'u',
            'Ǜ' => 'U','ǜ' => 'u', // Grave accent.
        ];
        protected $_translation;
        protected $_translation_preg;
        protected $_others = ['&lt;'=> '<','&#060;' => '<','&gt;'=>'>', '&#062;'=>'>','&amp;'=>'&','&#038;' => '&','&#x26;' => '&',];
        protected $_others_preg = ['/&#0*60;/'=>'&#060;','/&#0*62;/'=> '&#062;','/&#0*38;/'=> '&#038;','/&#x0*26;/i'=>'&#x26;',];
        protected $_single = ['&#039;' => '\'','&#x27;' => '\'', ];
        protected $_single_preg = ['/&#0*39;/'=>'&#039;','/&#x0*27;/i'=>'&#x27;',];
        protected $_double = ['&quot;' => '"','&#034;' => '"', '&#x22;' => '"',];
        protected $_double_preg = ['/&#0*34;/'   => '&#034;','/&#x0*22;/i' => '&#x22;',];
        protected $_characters_1 = [
            '%c2%ad', // Soft hyphens.
            '%c2%a1','%c2%bf',// &iexcl and &iquest.
            '%c2%ab','%c2%bb','%e2%80%b9','%e2%80%ba',// Angle quotes.
            '%e2%80%98','%e2%80%99','%e2%80%9c','%e2%80%9d','%e2%80%9a',
            '%e2%80%9b','%e2%80%9e','%e2%80%9f', // Curly quotes.
            '%e2%80%a2',// Bullet.
            '%c2%a9','%c2%ae','%c2%b0','%e2%80%a6','%e2%84%a2', // &copy, &reg, &deg, &hellip, and &trade.
            '%c2%b4','%cb%8a','%cc%81','%cd%81', // Acute accents.
            '%cc%80','%cc%84','%cc%8c', // Grave accent, macron, caron.
            // Non-visible characters that display without a width.
            '%e2%80%8b','%e2%80%8c','%e2%80%8d','%e2%80%8e','%e2%80%8f','%e2%80%aa',
            '%e2%80%ab','%e2%80%ac','%e2%80%ad','%e2%80%ae','%ef%bb%bf',
        ];
        protected $_characters_2 = [
            '%e2%80%80','%e2%80%81','%e2%80%82','%e2%80%83','%e2%80%84','%e2%80%85',
            '%e2%80%86','%e2%80%87','%e2%80%88','%e2%80%89','%e2%80%8a','%e2%80%a8',
            '%e2%80%a9','%e2%80%af',
        ];
        protected $_tp_html_transfer_win_to_uni =[
            '&#128;' => '&#8364;', // The Euro sign.
            '&#129;' => '',
            '&#130;' => '&#8218;', // These are Windows CP1252 specific characters.
            '&#131;' => '&#402;',  // They would look weird on non-Windows browsers.
            '&#132;' => '&#8222;','&#133;' => '&#8230;','&#134;' => '&#8224;','&#135;' => '&#8225;','&#136;' => '&#710;',
            '&#137;' => '&#8240;','&#138;' => '&#352;','&#139;' => '&#8249;','&#140;' => '&#338;','&#141;' => '',
            '&#142;' => '&#381;','&#143;' => '','&#144;' => '','&#145;' => '&#8216;','&#146;' => '&#8217;',
            '&#147;' => '&#8220;','&#148;' => '&#8221;','&#149;' => '&#8226;','&#150;' => '&#8211;','&#151;' => '&#8212;',
            '&#152;' => '&#732;','&#153;' => '&#8482;','&#154;' => '&#353;','&#155;' => '&#8250;','&#156;' => '&#339;',
            '&#157;' => '','&#158;' => '&#382;','&#159;' => '&#376;',
        ];
        protected $_chars_strings_to_numeric =[
            '&quot;'=> '&#34;','&amp;'=> '&#38;','&lt;' => '&#60;','&gt;' => '&#62;','|' => '&#124;',
            '&nbsp;' => '&#160;','&iexcl;' => '&#161;','&cent;' => '&#162;','&pound;' => '&#163;',
            '&curren;' => '&#164;','&yen;' => '&#165;','&brvbar;' => '&#166;','&brkbar;' => '&#166;',
            '&sect;' => '&#167;','&uml;' => '&#168;','&die;' => '&#168;','&copy;' => '&#169;','&ordf;' => '&#170;',
            '&laquo;' => '&#171;','&not;' => '&#172;','&shy;' => '&#173;','&reg;' => '&#174;','&macr;' => '&#175;',
            '&hibar;' => '&#175;', '&deg;' => '&#176;', '&plusmn;' => '&#177;', '&sup2;' => '&#178;', '&sup3;' => '&#179;',
            '&acute;' => '&#180;', '&micro;' => '&#181;', '&para;' => '&#182;', '&middot;' => '&#183;', '&cedil;' => '&#184;',
            '&sup1;' => '&#185;','&ordm;' => '&#186;','&raquo;' => '&#187;','&frac14;' => '&#188;','&frac12;' => '&#189;',
            '&frac34;' => '&#190;','&iquest;' => '&#191;','&Agrave;' => '&#192;','&Aacute;' => '&#193;','&Acirc;' => '&#194;',
            '&Atilde;' => '&#195;','&Auml;' => '&#196;','&Aring;' => '&#197;','&AElig;' => '&#198;','&Ccedil;' => '&#199;',
            '&Egrave;' => '&#200;','&Eacute;' => '&#201;','&Ecirc;' => '&#202;','&Euml;' => '&#203;','&Igrave;' => '&#204;',
            '&Iacute;' => '&#205;','&Icirc;' => '&#206;','&Iuml;' => '&#207;','&ETH;' => '&#208;','&Ntilde;' => '&#209;',
            '&Ograve;' => '&#210;','&Oacute;' => '&#211;','&Ocirc;' => '&#212;','&Otilde;' => '&#213;','&Ouml;' => '&#214;',
            '&times;' => '&#215;','&Oslash;' => '&#216;','&Ugrave;' => '&#217;', '&Uacute;' => '&#218;','&Ucirc;' => '&#219;',
            '&Uuml;' => '&#220;','&Yacute;' => '&#221;','&THORN;' => '&#222;','&szlig;' => '&#223;','&agrave;' => '&#224;',
            '&aacute;' => '&#225;','&acirc;' => '&#226;','&atilde;' => '&#227;','&auml;' => '&#228;','&aring;' => '&#229;',
            '&aelig;' => '&#230;','&ccedil;' => '&#231;','&egrave;' => '&#232;','&eacute;' => '&#233;','&ecirc;' => '&#234;',
            '&euml;' => '&#235;','&igrave;' => '&#236;','&iacute;' => '&#237;','&icirc;' => '&#238;','&iuml;' => '&#239;',
            '&eth;' => '&#240;','&ntilde;' => '&#241;','&ograve;'=> '&#242;','&oacute;'=> '&#243;','&ocirc;' => '&#244;',
            '&otilde;' => '&#245;','&ouml;' => '&#246;','&divide;' => '&#247;','&oslash;' => '&#248;','&ugrave;' => '&#249;',
            '&uacute;' => '&#250;','&ucirc;' => '&#251;','&uuml;' => '&#252;','&yacute;' => '&#253;','&thorn;' => '&#254;',
            '&yuml;' => '&#255;','&OElig;' => '&#338;','&oelig;' => '&#339;','&Scaron;' => '&#352;','&scaron;' => '&#353;',
            '&Yuml;' => '&#376;','&fnof;' => '&#402;','&circ;' => '&#710;','&tilde;' => '&#732;','&Alpha;' => '&#913;',
            '&Beta;' => '&#914;','&Gamma;' => '&#915;','&Delta;' => '&#916;','&Epsilon;' => '&#917;','&Zeta;' => '&#918;',
            '&Eta;' => '&#919;','&Theta;' => '&#920;','&Iota;' => '&#921;','&Kappa;' => '&#922;','&Lambda;' => '&#923;',
            '&Mu;' => '&#924;','&Nu;' => '&#925;','&Xi;' => '&#926;','&Omicron;' => '&#927;','&Pi;' => '&#928;',
            '&Rho;' => '&#929;','&Sigma;' => '&#931;','&Tau;' => '&#932;','&Upsilon;' => '&#933;','&Phi;' => '&#934;',
            '&Chi;' => '&#935;','&Psi;' => '&#936;','&Omega;' => '&#937;','&alpha;' => '&#945;','&beta;' => '&#946;',
            '&gamma;' => '&#947;','&delta;' => '&#948;','&epsilon;' => '&#949;','&zeta;' => '&#950;','&eta;' => '&#951;',
            '&theta;' => '&#952;','&iota;' => '&#953;','&kappa;' => '&#954;','&lambda;' => '&#955;','&mu;' => '&#956;',
            '&nu;' => '&#957;','&xi;' => '&#958;','&omicron;' => '&#959;','&pi;' => '&#960;','&rho;' => '&#961;',
            '&sigmaf;' => '&#962;','&sigma;' => '&#963;','&tau;' => '&#964;','&upsilon;' => '&#965;','&phi;' => '&#966;',
            '&chi;' => '&#967;','&psi;' => '&#968;','&omega;' => '&#969;','&thetasym;' => '&#977;','&upsih;' => '&#978;',
            '&piv;' => '&#982;','&ensp;' => '&#8194;','&emsp;' => '&#8195;','&thinsp;' => '&#8201;','&zwnj;' => '&#8204;',
            '&zwj;' => '&#8205;','&lrm;' => '&#8206;','&rlm;' => '&#8207;','&ndash;' => '&#8211;','&mdash;' => '&#8212;',
            '&lsquo;' => '&#8216;','&rsquo;' => '&#8217;','&sbquo;' => '&#8218;','&ldquo;' => '&#8220;','&rdquo;' => '&#8221;',
            '&bdquo;' => '&#8222;','&dagger;' => '&#8224;','&Dagger;' => '&#8225;','&bull;' => '&#8226;','&hellip;' => '&#8230;',
            '&permil;' => '&#8240;','&prime;' => '&#8242;','&Prime;' => '&#8243;','&lsaquo;' => '&#8249;','&rsaquo;' => '&#8250;',
            '&oline;' => '&#8254;','&frasl;' => '&#8260;','&euro;' => '&#8364;','&image;' => '&#8465;','&weierp;' => '&#8472;',
            '&real;' => '&#8476;','&trade;' => '&#8482;','&alefsym;' => '&#8501;','&crarr;' => '&#8629;','&lArr;' => '&#8656;',
            '&uArr;' => '&#8657;','&rArr;' => '&#8658;','&dArr;' => '&#8659;','&hArr;' => '&#8660;','&forall;' => '&#8704;',
            '&part;' => '&#8706;','&exist;' => '&#8707;','&empty;' => '&#8709;','&nabla;' => '&#8711;','&isin;' => '&#8712;',
            '&notin;' => '&#8713;','&ni;' => '&#8715;','&prod;' => '&#8719;','&sum;' => '&#8721;','&minus;' => '&#8722;',
            '&lowast;' => '&#8727;','&radic;' => '&#8730;','&prop;' => '&#8733;','&infin;' => '&#8734;','&ang;' => '&#8736;',
            '&and;' => '&#8743;','&or;' => '&#8744;','&cap;' => '&#8745;','&cup;' => '&#8746;','&int;' => '&#8747;',
            '&there4;' => '&#8756;','&sim;' => '&#8764;','&cong;' => '&#8773;','&asymp;' => '&#8776;','&ne;' => '&#8800;',
            '&equiv;' => '&#8801;','&le;' => '&#8804;','&ge;' => '&#8805;','&sub;' => '&#8834;','&sup;' => '&#8835;',
            '&nsub;' => '&#8836;','&sube;' => '&#8838;','&supe;' => '&#8839;','&oplus;' => '&#8853;','&otimes;' => '&#8855;',
            '&perp;' => '&#8869;','&sdot;' => '&#8901;','&lceil;' => '&#8968;','&rceil;' => '&#8969;','&lfloor;' => '&#8970;',
            '&rfloor;' => '&#8971;','&lang;' => '&#9001;','&rang;' => '&#9002;','&larr;' => '&#8592;','&uarr;' => '&#8593;',
            '&rarr;' => '&#8594;','&darr;' => '&#8595;','&harr;' => '&#8596;','&loz;'  => '&#9674;','&spades;' => '&#9824;',
            '&clubs;' => '&#9827;','&hearts;' => '&#9829;','&diams;' => '&#9830;',
        ];
        protected $_tp_smilies_search;
        protected $_tp_smilies_trans;
        protected $_rel_filters;
        protected $_links_add_base;
        protected $_links_add_target;
        //protected $_;
        //protected $_;


    }
}else{die;}