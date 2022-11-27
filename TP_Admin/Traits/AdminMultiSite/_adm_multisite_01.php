<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 19:04
 */
namespace TP_Admin\Traits\AdminMultiSite;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _adm_multisite_01{
        use _init_db;
        /**
         * @description Determine if uploaded file exceeds space quota.
         * @param $file
         * @return mixed
         */
        protected function _check_upload_size( $file ){
            if ( $this->_get_site_option( 'upload_space_check_disabled')){ return $file; }
            if ( $file['error'] > 0 ) {  return $file;}
            if ( defined( 'TP_IMPORTING' ) ) { return $file;}
            $space_left = $this->_get_upload_space_available();
            $file_size = filesize( $file['tmp_name'] );
            if ( $space_left < $file_size ) {
                /* translators: %s: Required disk space in kilobytes. */
                $file['error'] = sprintf( $this->__( 'Not enough space to upload. %s KB needed.' ), number_format( ( $file_size - $space_left ) / KB_IN_BYTES ) );
            }
            if ( $file_size > ( KB_IN_BYTES * $this->_get_site_option( 'fileupload_maxk', 1500 ) ) ) {
                /* translators: %s: Maximum allowed file size in kilobytes. */
                $file['error'] = sprintf( $this->__( 'This file is too big. Files must be less than %s KB in size.' ), $this->_get_site_option( 'fileupload_maxk', 1500 ) );
            }
            if ( $this->_upload_is_user_over_quota( false ) ) {
                $file['error'] = $this->__( 'You have used your space quota. Please delete files before uploading.' );
            }
            if ( $file['error'] > 0 && ! isset( $_POST['html-upload'] ) && ! $this->_tp_doing_async() ) {
                $this->_tp_die( $file['error'] . "<a href='javascript:history.go(-1)'>{$this->__( 'Back' )}</a>" );
            }
            return $file;
        }//18
        /**
         * @description Delete a site.
         * @param $blog_id
         * @param bool $drop
         */
        protected function _tp_mu_delete_blog( $blog_id, $drop = false ):void{
            $this->_init_db();
            $blog_id = (int) $blog_id;
            $switch = false;
            if ( $this->_get_current_blog_id() !== $blog_id ) {
                $switch = true;
                $this->_switch_to_blog( $blog_id );
            }
            $blog = $this->_get_site( $blog_id );
            $current_network = $this->_get_network();
            if ( $drop && ! $blog ) { $drop = false;}
            if ( $drop
                && ( 1 === $blog_id || $this->_is_main_site( $blog_id )
                    || ( $blog->path === $current_network->path && $blog->domain === $current_network->domain ) )
            ) { $drop = false;}
            $upload_path = trim( $this->_get_option( 'upload_path' ) );
            if ( $drop && empty( $upload_path ) && $this->_get_site_option( 'ms_files_rewriting' )) {
                $drop = false;
            }
            if ( $drop ) { $this->_tp_delete_site( $blog_id );}
            if ( $switch ) { $this->_restore_current_blog();}
        }//66
        /**
         * @description Delete a user from the network and remove from all sites.
         * @param $id
         * @return bool
         */
        protected function _tp_mu_delete_user( $id ):bool{
            $this->tpdb = $this->_init_db();
            if (!is_numeric( $id )){ return false;}
            $id   = (int) $id;
            $user = new TP_User( $id );
            if ( ! $user->exists() ) {return false;}
            $_super_admins = $this->_get_super_admins();
            if ( in_array( $user->user_login, $_super_admins, true ) ) { return false; }
            $this->_do_action( 'tp_mu_delete_user', $id, $user );
            $blogs = $this->_get_blogs_of_user( $id );
            if ( ! empty( $blogs ) ) {
                foreach ( $blogs as $blog ) {
                    $this->_switch_to_blog( $blog->userblog_id );
                    $this->_remove_user_from_blog( $id, $blog->userblog_id );
                    $post_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " ID FROM $this->tpdb->posts WHERE post_author = %d", $id ) );
                    foreach ($post_ids as $post_id ) { $this->_tp_delete_post( $post_id ); }
                    $link_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " link_id FROM $this->tpdb->links WHERE link_owner = %d", $id ) );
                    if ( $link_ids ) {
                        foreach ( $link_ids as $link_id ) { $this->_tp_delete_link( $link_id );}
                    }
                    $this->_restore_current_blog();
                }
            }
            $meta = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " umeta_id FROM $this->tpdb->usermeta WHERE user_id = %d", $id ) );
            foreach ( $meta as $mid ) { $this->_delete_metadata_by_mid( 'user', $mid );}
            $this->tpdb->delete( $this->tpdb->users, array( 'ID' => $id ) );
            $this->_clean_user_cache( $user );
            $this->_do_action( 'deleted_user', $id, null, $user );
            return true;
        }//144
        /**
         * @description Check whether a site has used its allotted upload space.
         * @param bool $echo
         * @return bool
         */
        protected function _upload_is_user_over_quota( $echo = true ):bool{
            if ( $this->_get_site_option( 'upload_space_check_disabled' ) ) {
                return false;
            }
            $space_allowed = $this->_get_space_allowed();
            if ( ! is_numeric( $space_allowed ) ) {
                $space_allowed = 10; // Default space allowed is 10 MB.
            }
            $space_used = $this->_get_space_used();
            if ( ( $space_allowed - $space_used ) < 0 ) {
                if ( $echo ) {
                    printf(
                        $this->__( 'Sorry, you have used your space allocation of %s. Please delete some files to upload more files.' ),
                        $this->_size_format( $space_allowed * MB_IN_BYTES )
                    );
                }
                return true;
            }
            return false;
        }//223
        /**
         * @description Displays the amount of disk space used by the current site. Not used in core.
         * @return string
         */
        protected function _get_display_space_usage():string{
            $space_allowed = $this->_get_space_allowed();
            $space_used    = $this->_get_space_used();
            $percent_used = ( $space_used / $space_allowed ) * 100;
            $space = $this->_size_format( $space_allowed * MB_IN_BYTES );
            $output  = "<strong>";
            $output .= sprintf($this->__('Used: %1$s%% of %2$s'), number_format( $percent_used ), $space );
            $output .= "</strong>";
            return $output;
        }//253
        /**
         * @description Get the remaining upload space for this site.
         * @param $size
         * @return mixed
         */
        protected function _fix_import_form_size( $size ){
            if ( $this->_upload_is_user_over_quota( false ) ) { return 0;}
            $available = $this->_get_upload_space_available();
            return min( $size, $available );
        }//278
        /**
         * @description Displays the site upload space quota setting form on the Edit Site Settings screen.
         * @param $id
         * @return string
         */
        protected function _get_upload_space_setting( $id ):string{
            $this->_switch_to_blog( $id );
            $quota = $this->_get_option( 'blog_upload_space' );
            $this->_restore_current_blog();
            if ( ! $quota ) { $quota = '';}
            $output  = "<li><dt><label for='blog_upload_space_number'>{$this->__('Site Upload Space Quota')}</label></dt>";
            $output .= "<dd><input name='option[blog_upload_space]' id='blog_upload_space_number' type='number' step='1' min='0' style='width: 100px;' aria-describedby='blog-upload-space-desc' value='$quota'/></dd>";
            $output .= "<dt><span id='blog_upload_space_desc'><span class='screen-reader-text'>{$this->__('Size in megabytes')}</span>{$this->__('MB (Leave blank for network default)')}</span></dt></li>";
            return $output;
        }//293
        /**
         * @description Cleans the user cache for a specific user.
         * @param $id
         * @return bool|int
         */
        protected function _refresh_user_details( $id ){
            $id = (int) $id;
            $user = $this->_get_user_data( $id );
            if ( ! $user ) { return false;}
            $this->_clean_user_cache( $user );
            return $id;
        }//321
        /**
         * @description Returns the language for a language code.
         * @param string $code
         * @return string
         */
        protected function _format_code_lang( $code = '' ):string{
            $code       = strtolower( substr( $code, 0, 2 ) );
            $lang_codes = ['aa' => 'Afar','ab' => 'Abkhazian','af' => 'Afrikaans','ak' => 'Akan','sq' => 'Albanian','am' => 'Amharic','ar' => 'Arabic','an' => 'Aragonese','hy' => 'Armenian','as' => 'Assamese','av' => 'Avaric',
                'ae' => 'Avestan','ay' => 'Aymara','az' => 'Azerbaijani','ba' => 'Bashkir','bm' => 'Bambara','eu' => 'Basque','be' => 'Belarusian','bn' => 'Bengali','bh' => 'Bihari','bi' => 'Bislama','bs' => 'Bosnian',
                'br' => 'Breton','bg' => 'Bulgarian','my' => 'Burmese','ca' => 'Catalan; Valencian','ch' => 'Chamorro','ce' => 'Chechen','zh' => 'Chinese','cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic',
                'cv' => 'Chuvash','kw' => 'Cornish','co' => 'Corsican','cr' => 'Cree','cs' => 'Czech','da' => 'Danish','dv' => 'Divehi; Dhivehi; Maldivian','nl' => 'Dutch; Flemish','dz' => 'Dzongkha','en' => 'English','eo' => 'Esperanto',
                'et' => 'Estonian','ee' => 'Ewe','fo' => 'Faroese','fj' => 'Fijjian','fi' => 'Finnish','fr' => 'French','fy' => 'Western Frisian','ff' => 'Fulah','ka' => 'Georgian','de' => 'German','gd' => 'Gaelic; Scottish Gaelic',
                'ga' => 'Irish','gl' => 'Galician','gv' => 'Manx','el' => 'Greek, Modern','gn' => 'Guarani','gu' => 'Gujarati','ht' => 'Haitian; Haitian Creole','ha' => 'Hausa','he' => 'Hebrew','hz' => 'Herero','hi' => 'Hindi',
                'ho' => 'Hiri Motu','hu' => 'Hungarian','ig' => 'Igbo','is' => 'Icelandic','io' => 'Ido','ii' => 'Sichuan Yi','iu' => 'Inuktitut','ie' => 'Interlingue','ia' => 'Interlingua (International Auxiliary Language Association)',
                'id' => 'Indonesian','ik' => 'Inupiaq','it' => 'Italian','jv' => 'Javanese','ja' => 'Japanese','kl' => 'Kalaallisut; Greenlandic','kn' => 'Kannada','ks' => 'Kashmiri','kr' => 'Kanuri','kk' => 'Kazakh','km' => 'Central Khmer',
                'ki' => 'Kikuyu; Gikuyu', 'rw' => 'Kinyarwanda','ky' => 'Kirghiz; Kyrgyz','kv' => 'Komi','kg' => 'Kongo','ko' => 'Korean','kj' => 'Kuanyama; Kwanyama','ku' => 'Kurdish','lo' => 'Lao','la' => 'Latin','lv' => 'Latvian',
                'li' => 'Limburgan; Limburger; Limburgish','ln' => 'Lingala', 'lt' => 'Lithuanian','lb' => 'Luxembourgish; Letzeburgesch','lu' => 'Luba-Katanga','lg' => 'Ganda','mk' => 'Macedonian', 'mh' => 'Marshallese','ml' => 'Malayalam',
                'mi' => 'Maori','mr' => 'Marathi','ms' => 'Malay','mg' => 'Malagasy','mt' => 'Maltese','mo' => 'Moldavian','mn' => 'Mongolian','na' => 'Nauru','nv' => 'Navajo; Navaho','nr' => 'Ndebele, South; South Ndebele',
                'nd' => 'Ndebele, North; North Ndebele','ng' => 'Ndonga','ne' => 'Nepali','nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian','nb' => 'Bokmål, Norwegian, Norwegian Bokmål','no' => 'Norwegian','ny' => 'Chichewa; Chewa; Nyanja',
                'oc' => 'Occitan, Provençal','oj' => 'Ojibwa','or' => 'Oriya','om' => 'Oromo','os' => 'Ossetian; Ossetic','pa' => 'Panjabi; Punjabi','fa' => 'Persian','pi' => 'Pali','pl' => 'Polish','pt' => 'Portuguese','ps' => 'Pushto',
                'qu' => 'Quechua','rm' => 'Romansh','ro' => 'Romanian','rn' => 'Rundi','ru' => 'Russian','sg' => 'Sango','sa' => 'Sanskrit','sr' => 'Serbian','hr' => 'Croatian','si' => 'Sinhala; Sinhalese','sk' => 'Slovak','sl' => 'Slovenian',
                'se' => 'Northern Sami','sm' => 'Samoan','sn' => 'Shona','sd' => 'Sindhi', 'so' => 'Somali','st' => 'Sotho, Southern','es' => 'Spanish; Castilian','sc' => 'Sardinian','ss' => 'Swati','su' => 'Sundanese','sw' => 'Swahili',
                'sv' => 'Swedish','ty' => 'Tahitian','ta' => 'Tamil','tt' => 'Tatar','te' => 'Telugu','tg' => 'Tajik','tl' => 'Tagalog','th' => 'Thai','bo' => 'Tibetan','ti' => 'Tigrinya','to' => 'Tonga (Tonga Islands)','tn' => 'Tswana',
                'ts' => 'Tsonga','tk' => 'Turkmen','tr' => 'Turkish','tw' => 'Twi','ug' => 'Uighur; Uyghur','uk' => 'Ukrainian','ur' => 'Urdu','uz' => 'Uzbek','ve' => 'Venda','vi' => 'Vietnamese',
                'vo' => 'Volapük','cy' => 'Welsh', 'wa' => 'Walloon','wo' => 'Wolof','xh' => 'Xhosa','yi' => 'Yiddish','yo' => 'Yoruba','za' => 'Zhuang; Chuang','zu' => 'Zulu',];
            $lang_codes = $this->_apply_filters( 'lang_codes', $lang_codes, $code );
            return strtr( $code, $lang_codes );
        }//343
        /**
         * @description Synchronizes category and post tag slugs when global terms are enabled.
         * @param $term
         * @param $taxonomy
         * @return mixed
         */
        protected function _sync_category_tag_slugs( $term, $taxonomy ){
            if (( 'category' === $taxonomy || 'post_tag' === $taxonomy ) && $this->_global_terms_enabled()) {
                if ( is_object( $term ) ) { $term->slug = $this->_sanitize_title( $term->name );}
                else { $term['slug'] = $this->_sanitize_title( $term['name'] );}
            }
            return $term;
        }//557
    }
}else die;