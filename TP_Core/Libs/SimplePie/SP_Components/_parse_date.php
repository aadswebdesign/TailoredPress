<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-3-2022
 * Time: 20:46
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
if(ABSPATH){
    trait _parse_date {
        protected $_sp_day = [
            // English
            'mon' => 1,'monday' => 1,'tue' => 2,'tuesday' => 2,'wed' => 3,'wednesday' => 3,'thu' => 4,'thursday' => 4,'fri' => 5,'friday' => 5,'sat' => 6,'saturday' => 6,'sun' => 7,'sunday' => 7,
            // Dutch
            'maandag' => 1,'dinsdag' => 2,'woensdag' => 3,'donderdag' => 4,'vrijdag' => 5,'zaterdag' => 6,'zondag' => 7,
            // French
            'lundi' => 1,'mardi' => 2,'mercredi' => 3,'jeudi' => 4,'vendredi' => 5,'samedi' => 6,'dimanche' => 7,
            // German
            'montag' => 1,'mo' => 1,'dienstag' => 2,'di' => 2,'mittwoch' => 3,'mi' => 3,'donnerstag' => 4,'do' => 4,'freitag' => 5,'fr' => 5,'samstag' => 6,'sa' => 6,'sonnabend' => 6,'so' => 7,'sonntag' => 7,
            // Italian
            'lunedì' => 1,'martedì' => 2,'mercoledì' => 3,'giovedì' => 4,'venerdì' => 5,'sabato' => 6,'domenica' => 7,
            // Spanish
            'lunes' => 1,'martes' => 2,'miércoles' => 3,'jueves' => 4,'viernes' => 5,'sábado' => 6,'domingo' => 7,
            // Finnish
            'maanantai' => 1,'tiistai' => 2,'keskiviikko' => 3,'torstai' => 4,'perjantai' => 5,'lauantai' => 6,'sunnuntai' => 7,
            // Hungarian
            'hétfő' => 1,'kedd' => 2,'szerda' => 3,'csütörtok' => 4,'péntek' => 5,'szombat' => 6,'vasárnap' => 7,
            // Greek
            'Δευ' => 1,'Τρι' => 2,'Τετ' => 3,'Πεμ' => 4,'Παρ' => 5,'Σαβ' => 6,'Κυρ' => 7,
            // Russian
            'Пн.' => 1,'Вт.' => 2,'Ср.' => 3,'Чт.' => 4,'Пт.' => 5,'Сб.' => 6,'Вс.' => 7,
        ];
        protected $_sp_day_pcre;
        protected $_sp_month = [
            'english' =>[
                'jan' => 1,'january' => 1,'feb' => 2,'february' => 2,'mar' => 3,'march' => 3,'apr' => 4,'april' => 4,'may' => 5,'jun' => 6,'june' => 6,
                'jul' => 7,'july' => 7,'aug' => 8,'august' => 8,'sep' => 9,'september' => 9,'oct' => 10,'october' => 10,'nov' => 11,'november' => 11,'dec' => 12,'december' => 12,
            ],
            'dutch' =>[
                'januari' => 1,'februari' => 2,'maart' => 3,'april' => 4,'mei' => 5,'juni' => 6,
                'juli' => 7,'augustus' => 8,'september' => 9,'oktober' => 10,'november' => 11,'december' => 12,
            ],
            'french' =>[
                'janvier' => 1,'février' => 2,'mars' => 3,'avril' => 4,'mai' => 5,'juin' => 6,
                'juillet' => 7,'août' => 8,'septembre' => 9,'octobre' => 10,'novembre' => 11,'décembre' => 12,
            ],
            'german' =>[
                'januar' => 1,'jan' => 1,'februar' => 2,'feb' => 2,'märz' => 3,'mär' => 3,'april' => 4,'apr_f' => 4,'mai_f' => 5, 'juni_f' => 6,'jun_f' => 6,
                'juli' => 7,'jul' => 7,'august' => 8,'aug' => 8,'september' => 9,'sep' => 9,'oktober_f' => 10,'okt' => 10,'november' => 11,'nov' => 11,'dezember' => 12,'dez' => 12,
            ],
            'italian' =>[
                'gennaio' => 1,'febbraio' => 2,'marzo' => 3,'aprile' => 4,'maggio' => 5,'giugno' => 6,
                'luglio' => 7,'agosto' => 8,'settembre' => 9,'ottobre' => 10,'novembre' => 11,'dicembre' => 12,
            ],
            'spanish' =>[
                'enero' => 1,'febrero' => 2,'marzo' => 3,'abril' => 4,'mayo' => 5,'junio' => 6,
                'julio' => 7,'agosto' => 8,'septiembre' => 9,'setiembre' => 9,'octubre' => 10,'noviembre' => 11,'diciembre' => 12,
            ],
            'finnish' =>[
                'tammikuu' => 1,'helmikuu' => 2,'maaliskuu' => 3,'huhtikuu' => 4,'toukokuu' => 5,'kesäkuu' => 6,
                'heinäkuu' => 7,'elokuu' => 8,'suuskuu' => 9,'lokakuu' => 10,'marras' => 11,'joulukuu' => 12,
            ],
            'hungarian' =>[
                'január' => 1,'február' => 2,'március' => 3,'április' => 4,'május' => 5,'június' => 6,
                'július' => 7,'augusztus' => 8,'szeptember' => 9,'október' => 10,'november' => 11,'december' => 12,
            ],
            'greek' =>[
                'Ιαν' => 1,'Φεβ' => 2,'Μάώ' => 3,'Μαώ' => 3,'Απρ' => 4,'Μάι' => 5,'Μαϊ' => 5,'Μαι' => 5,'Ιούν' => 6,'Ιον' => 6,
                'Ιούλ' => 7,'Ιολ' => 7,'Αύγ' => 8,'Αυγ' => 8,'Σεπ' => 9,'Οκτ' => 10,'Νοέ' => 11,'Δεκ' => 12,
            ],
            'russian' =>[
                'Янв' => 1,'января' => 1,'Фев' => 2,'февраля' => 2,'Мар' => 3,'марта' => 3,'Апр' => 4,'апреля' => 4,'Май' => 5,'мая' => 5,'Июн' => 6,'июня' => 6,
                'Июл' => 7,'июля' => 7,'Авг' => 8,'августа' => 8,'Сен' => 9,'сентября' => 9,'Окт' => 10,'октября' => 10,'Ноя' => 11,'ноября' => 11,'Дек' => 12,'декабря' => 12,
            ],
        ];
        protected $_sp_month_pcre;
        protected $_sp_timezone =[
            'ACDT' => 37800,'ACIT' => 28800,'ACST' => 34200,'ACT' => -18000,'ACWDT' => 35100,
            'ACWST' => 31500,'AEDT' => 39600,'AEST' => 36000,'AFT' => 16200,'AKDT' => -28800,
            'AKST' => -32400,'AMDT' => 18000,'AMT' => -14400,'ANAST' => 46800,'ANAT' => 43200,
            'ART' => -10800,'AZOST' => -3600,'AZST' => 18000,'AZT' => 14400,'BIOT' => 21600,
            'BIT' => -43200,'BOT' => -14400,'BRST' => -7200,'BRT' => -10800,'BST' => 3600,
            'BTT' => 21600,'CAST' => 18000,'CAT' => 7200,'CCT' => 23400,'CDT' => -18000,
            'CEDT' => 7200,'CEST' => 7200,'CET' => 3600,'CGST' => -7200,'CGT' => -10800,
            'CHADT' => 49500,'CHAST' => 45900,'CIST' => -28800,'CKT' => -36000,'CLDT' => -10800,
            'CLST' => -14400,'COT' => -18000,'CST' => -21600,'CVT' => -3600,'CXT' => 25200,
            'DAVT' => 25200,'DTAT' => 36000,'EADT' => -18000,'EAST' => -21600,'EAT' => 10800,
            'ECT' => -18000,'EDT' => -14400,'EEST' => 10800,'EET' => 7200,'EGT' => -3600,
            'EKST' => 21600,'EST' => -18000,'FJT' => 43200,'FKDT' => -10800,'FKST' => -14400,
            'FNT' => -7200,'GALT' => -21600,'GEDT' => 14400,'GEST' => 10800,'GFT' => -10800,
            'GILT' => 43200,'GIT' => -32400,'GST' => [14400,-7200],'GYT' => -14400,'HAA' => -10800,
            'HAC' => -18000,'HADT' => -32400,'HAE' => -14400,'HAP' => -25200,'HAR' => -21600,
            'HAST' => -36000,'HAT' => -9000,'HAY' => -28800,'HKST' => 28800,'HMT' => 18000,
            'HNA' => -14400,'HNC' => -21600,'HNE' => -18000,'HNP' => -28800,'HNR' => -25200,
            'HNT' => -12600,'HNY' => -32400,'IRDT' => 16200,'IRKST' => 32400,'IRKT' => 28800,
            'IRST' => 12600,'JFDT' => -10800,'JFST' => -14400,'JST' => 32400,'KGST' => 21600,
            'KGT' => 18000,'KOST' => 39600,'KOVST' => 28800,'KOVT' => 25200,'KRAST' => 28800,
            'KRAT' => 25200,'KST' => 32400,'LHDT' => 39600,'LHST' => 37800,'LINT' => 50400,
            'LKT' => 21600,'MAGST' => 43200,'MAGT' => 39600,'MAWT' => 21600,'MDT' => -21600,
            'MESZ' => 7200,'MEZ' => 3600,'MHT' => 43200,'MIT' => -34200,'MNST' => 32400,
            'MSDT' => 14400,'MSST' => 10800,'MST' => -25200,'MUT' => 14400,'MVT' => 18000,
            'MYT' => 28800,'NCT' => 39600,'NDT' => -9000,'NFT' => 41400,'NMIT' => 36000,
            'NOVST' => 25200,'NOVT' => 21600,'NPT' => 20700,'NRT' => 43200,'NST' => -12600,
            'NUT' => -39600,'NZDT' => 46800,'NZST' => 43200,'OMSST' => 25200,'OMST' => 21600,
            'PDT' => -25200,'PET' => -18000,'PETST' => 46800,'PETT' => 43200,'PGT' => 36000,
            'PHOT' => 46800,'PHT' => 28800,'PKT' => 18000,'PMDT' => -7200,'PMST' => -10800,
            'PONT' => 39600,'PST' => -28800,'PWT' => 32400,'PYST' => -10800,'PYT' => -14400,
            'RET' => 14400,'ROTT' => -10800,'SAMST' => 18000,'SAMT' => 14400,'SAST' => 7200,
            'SBT' => 39600,'SCDT' => 46800,'SCST' => 43200,'SCT' => 14400,'SEST' => 3600,
            'SGT' => 28800,'SIT' => 28800,'SRT' => -10800,'SST' => -39600,'SYST' => 10800,
            'SYT' => 7200,'TFT' => 18000,'THAT' => -36000,'TJT' => 18000,'TKT' => -36000,
            'TMT' => 18000,'TOT' => 46800,'TPT' => 32400,'TRUT' => 36000,'TVT' => 43200,
            'TWT' => 28800,'UYST' => -7200,'UYT' => -10800,'UZT' => 18000,'VET' => -14400,
            'VLAST' => 39600,'VLAT' => 36000,'VOST' => 21600,'VUT' => 39600,'WAST' => 7200,
            'WAT' => 3600,'WDT' => 32400,'WEST' => 3600,'WFT' => 43200,'WIB' => 25200,
            'WIT' => 32400,'WITA' => 28800,'WKST' => 18000,'WST' => 28800,'YAKST' => 36000,
            'YAKT' => 32400,'YAPT' => 36000,'YEKST' => 21600,'YEKT' => 18000,
        ];
    }
}else die;

