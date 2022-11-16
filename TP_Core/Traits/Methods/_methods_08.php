<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Methods\Components\die_default_view;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _methods_08{
        use _init_queries;
        /**
         * @description Returns the real mime type of an image file.
         * @param $file
         * @return bool|string
         */
        protected function _tp_get_image_mime( $file ){
            try {
                if ( is_callable( 'exif_image_type' ) ) {
                    $image_type = exif_imagetype( $file );
                    $mime      = ( $image_type ) ? image_type_to_mime_type( $image_type ) : false;
                } elseif ( function_exists( 'getimagesize' ) ) {
                    if ( defined( 'TP_DEBUG' ) && TP_DEBUG && ! defined( 'TP_RUN_CORE_TESTS' ))
                        $image_size = getimagesize( $file );
                    else $image_size = @getimagesize( $file );
                    $mime = $image_size['mime'] ?? false;
                } else $mime = false;
                if ( false !== $mime )  return $mime;
                $handle = fopen( $file, 'rb' );
                if ( false === $handle ) return false;
                $magic = fread( $handle, 12 );
                if ( false === $magic ) return false;
                $magic = bin2hex( $magic );
                //1 RIFF. 2 WEBP.
                if (( 0 === strpos( $magic, '52494646' ) ) &&( 16 === strpos( $magic, '57454250' ) ))
                    $mime = 'image/webp';
                fclose( $handle );
            } catch ( \Exception $e ){
                $mime = false;
            }
            return $mime;
        }//3237
        /**
         * @description Retrieve list of mime types and file extensions.
         * @return mixed
         */
        protected function _tp_get_mime_types(){
            $mime_types = [
                // Image formats.
                'jpg|jpeg|jpe' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png', 'bmp' => 'image/bmp',
                'tiff|tif' => 'image/tiff', 'webp' => 'image/webp', 'ico' => 'image/x-icon', 'heic' => 'image/heic',
                // Video formats.  //last 2 Can also be audio.
                'asf|asx' => 'video/x-ms-asf','wmv' => 'video/x-ms-wmv', 'wmx' => 'video/x-ms-wmx', 'wm' => 'video/x-ms-wm', 'avi' => 'video/avi',
                'divx' => 'video/divx', 'flv' => 'video/x-flv', 'mov|qt' => 'video/quicktime', 'mpeg|mpg|mpe' => 'video/mpeg', 'mp4|m4v' => 'video/mp4',
                'ogv' => 'video/ogg', 'webm' => 'video/webm', 'mkv' => 'video/x-matroska', '3gp|3gpp' => 'video/3gpp', '3g2|3gp2' => 'video/3gpp2',
                // Text formats.
                'txt|asc|c|cc|h|srt' => 'text/plain', 'csv' => 'text/csv', 'tsv' => 'text/tab-separated-values', 'ics' => 'text/calendar',
                'rtx' => 'text/richtext', 'css' => 'text/css', 'htm|html' => 'text/html', 'vtt' => 'text/vtt', 'dfxp' => 'application/ttaf+xml',
                // Audio formats.
                'mp3|m4a|m4b' => 'audio/mpeg', 'aac'=> 'audio/aac', 'ra|ram'=> 'audio/x-realaudio', 'wav'=> 'audio/wav', 'ogg|oga'=> 'audio/ogg',
                'flac'=> 'audio/flac', 'mid|midi'=> 'audio/midi', 'wma'=> 'audio/x-ms-wma', 'wax'=> 'audio/x-ms-wax', 'mka'=> 'audio/x-matroska',
                // Misc application formats.
                'rtf' => 'application/rtf', 'js' => 'application/javascript', 'pdf' => 'application/pdf', 'swf' => 'application/x-shockwave-flash',
                'class' => 'application/java', 'tar' => 'application/x-tar', 'zip' => 'application/zip', 'gz|gzip' => 'application/x-gzip','rar' => 'application/rar',
                '7z' => 'application/x-7z-compressed', 'exe' => 'application/x-msdownload', 'psd' => 'application/octet-stream', 'xcf' => 'application/octet-stream',
                // MS Office formats.
                'doc' => 'application/msword','pot|pps|ppt' => 'application/vnd.ms-powerpoint','wri' => 'application/vnd.ms-write','xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
                'mdb' => 'application/vnd.ms-access','mpp' => 'application/vnd.ms-project','docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document','docm' => 'application/vnd.ms-word.document.macroEnabled.12',
                'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template','dotm' => 'application/vnd.ms-word.template.macroEnabled.12','xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
                'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12','xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template','xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
                'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12','pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation','pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
                'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow','ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12','potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template','potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
                'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12','sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide','sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12','onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
                'oxps' => 'application/oxps','xps' => 'application/vnd.ms-xpsdocument',
                // OpenOffice formats.
                'odt' => 'application/vnd.oasis.opendocument.text','odp' => 'application/vnd.oasis.opendocument.presentation','ods' => 'application/vnd.oasis.opendocument.spreadsheet',
                'odg' => 'application/vnd.oasis.opendocument.graphics','odc' => 'application/vnd.oasis.opendocument.chart','odb' => 'application/vnd.oasis.opendocument.database','odf' => 'application/vnd.oasis.opendocument.formula',
                // TailoredPerfect formats.
                'tp|tpd'=> 'application/wordperfect',
                // iWork formats.
                'key' => 'application/vnd.apple.keynote','numbers' => 'application/vnd.apple.numbers','pages' => 'application/vnd.apple.pages',
            ];
            return $this->_apply_filters('mime_types',$mime_types);
        }//3311
        /**
         * @description Retrieves the list of common file extensions and their types.
         * @return mixed
         */
        protected function _tp_get_ext_types(){
            $ext_types = [
                'image' =>['jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico', 'heic', 'webp'],
                'audio' =>['aac', 'ac3', 'aif', 'aiff', 'flac', 'm3a', 'm4a', 'm4b', 'mka', 'mp1', 'mp2', 'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma'],
                'video' =>['3g2', '3gp', '3gpp', 'asf', 'avi', 'divx', 'dv', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt', 'rm', 'vob', 'wmv'],
                'document' =>[ 'doc', 'docx', 'docm', 'dotm', 'odt', 'pages', 'pdf', 'xps', 'oxps', 'rtf', 'tp', 'wpd', 'psd', 'xcf'],
                'spreadsheet' =>['numbers', 'ods', 'xls', 'xlsx', 'xlsm', 'xlsb'],
                'interactive' =>['swf', 'key', 'ppt', 'pptx', 'pptm', 'pps', 'ppsx', 'ppsm', 'sldx', 'sldm', 'odp'],
                'text'=>['asc', 'csv', 'tsv', 'txt'],
                'archive' =>['bz2', 'cab', 'dmg', 'gz', 'rar', 'sea', 'sit', 'sqx', 'tar', 'tgz', 'zip', '7z'],
                'code'=> ['css', 'htm', 'html', 'php', 'js','ts','tsx'],//todo 1
                'xml' => ['xml','svg'],//todo 2
            ];
            return $this->_apply_filters('ext2type',$ext_types);
        }//3440
        /**
         * @description Retrieve list of allowed mime types and file extensions.
         * @param null $user
         * @return mixed
         */
        protected function _get_allowed_mime_types( $user = null ){
            $type = $this->_tp_get_mime_types();
            unset( $type['swf'], $type['exe'] );//todo erase at all
            if ( function_exists('__current_user_can') )
                $unfiltered = $user ? $this->_user_can( $user, 'unfiltered_html' ) : $this->_current_user_can( 'unfiltered_html' );
            if ( empty( $unfiltered ) ) unset( $type['htm|html'], $type['js'], $type['ts'], $type['tsx'] );//todo
            return $this->_apply_filters( 'upload_mimes', $type, $user );
        }
        /**
         * @description Display "Are You Sure" message to confirm the action being taken.
         * @param $action
         */
        protected function _tp_nonce_ays( $action ):void{
            $title = $this->__( 'Something went wrong.' );
            $response_code = FORBIDDEN;
            if ( 'log-out' === $action ) {
                $title = sprintf($this->__( 'You are attempting to log out of %s' ), $this->_get_bloginfo( 'name' ));
                $html        = $title;
                $html       .= '</p><p>';
                $redirect_to = $_REQUEST['redirect_to'] ?? '';
                $html       .= sprintf($this->__( "Do you really want to <a href='%s'>log out</a>?" ),$this->_tp_logout_url( $redirect_to ));
            } else {
                $html = $this->__( 'The link you followed has expired.' );
                if ( $this->_tp_get_referer() ) {
                    $html .= '</p><p>';
                    $html .= sprintf("<a href='%s'>%s</a>", $this->_esc_url( $this->_remove_query_arg( 'updated', $this->_tp_get_referer() ) ), $this->__( 'Please try again.' ));
                }
            }
            $this->_tp_die( $html, $title, $response_code );
        }//3509
        /**
         * @description Kills TailoredPress execution and displays HTML page with an error message.
         * @param string $message
         * @param string $title
         * @param array ...$args
         * @return string
         */
        protected function _tp_get_die( $message = '', $title = '', ...$args):string{//todo testing
            $output  = "";
            if ( is_int((int) $args ) )
                $args = ['response' => $args];
            elseif ( is_int((int) $title ) ) {
                $args  = ['response' => $title];
                $title = '';
            }
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $this->_tp_is_jsonp_request() )
                $output .= $function = $this->_apply_filters( 'tp_die_jsonp_handler', [$this,'_tp_jsonp_die_handler'] );
            elseif ( $this->_tp_is_json_request() )
                $output .= $function = $this->_apply_filters( 'tp_die_json_handler', [$this,'_tp_json_die_handler'] );//todo
            else $function = $this->_apply_filters( 'tp_die_handler',[$this,'_tp_die_default_handler'] );
            $output .= $function($message, $title, $args);
            return $output;
        }//3591
        protected function _tp_die( $message = '', $title = '', ...$args):void{
            echo $this->_tp_get_die( $message, $title,$args);
        }
        /**
         * @description Kills TailoredPress execution and displays HTML page with an error message.
         * @param $message
         * @param string $title
         * @param array ...$args
         */
        protected function _tp_die_default_handler( $message, $title = '', ...$args ):void{
            @list( $message, $title, $parsed_args ) = $this->_tp_die_process_input( $message, $title, $args );
            if ( is_string( $message ) ) {
                if ( ! empty( $parsed_args['additional_errors'] ) ) {
                    $message = array_merge(
                        [$message], $this->_tp_list_pluck( $parsed_args['additional_errors'], 'message' ));
                    $insert_li = implode( "</li>\n\t\t<li>", $message );
                    $message = "<ul>\n\t\t<li>$insert_li</li>\n\t</ul>";
                }
                ob_start();
                    sprintf("<div class='tp-die-message'>%s</div>", $message );
                $message = ob_get_clean();
            }
            if ( ! empty( $parsed_args['link_url'] ) && ! empty( $parsed_args['link_text'] ) ) {
                $link_url = $parsed_args['link_url'];
                if ( function_exists( 'esc_url' ) ) $link_url = esc_url( $link_url );
                $link_text = $parsed_args['link_text'];
                $message  .= "\n<p><a href='{$link_url}'>{$link_text}</a></p>";
            }
            if ( isset( $parsed_args['back_link'] ) && $parsed_args['back_link'] ) {
                $back_text = $this->__( '&laquo; Back' );
                $message  .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
            }
            $this->tp_view_args['title'] = $title;
            $this->tp_view_args['msg'] = $message;
            echo new die_default_view($this->tp_view_args,$parsed_args);
            if ( $parsed_args['exit'] ) die();
        }//3677
        /**
         * @description Kills TailoredPress execution and displays Async response with an error message.
         * @param $message
         * @param array ...$args
         */
        protected function _tp_async_die_handler( $message,  ...$args ):void{ //not used , $title = ''
            $args = $this->_tp_parse_args($args,['response' => 200]);
            @list( $message, $parsed_args ) = $this->_tp_die_process_input( $message, $args );
            if ( ! headers_sent() ) { $this->_nocache_headers();}
            if ( is_scalar( $message ) ) {$message = (string) $message;}
            else {$message = '0';}
            if ( $parsed_args['exit'] ) {die( $message );}
            echo $message;
        }//3879 todo
        /**
         * @description Kills TailoredPress execution and displays JSON response with an error message.
         * @param $message
         * @param string $title
         * @param array $args
         */
        protected function _tp_json_die_handler( $message, $title = '',  ...$args ):void{
            @list( $message, $title, $parsed_args ) = $this->_tp_die_process_input( $message, $title, $args );
            $data =['code'=> $parsed_args['code'],'message' => $message,'data' => ['status' => $parsed_args['response'],],
                'additional_errors' => $parsed_args['additional_errors'], 'title' => $title,];
            if ( ! headers_sent() ) {
                header( "Content-Type: application/json; charset={$parsed_args['charset']}" );
                if ( null !== $parsed_args['response'] ) $this->_status_header( $parsed_args['response'] );
                $this->_nocache_headers();
            }
            echo $this->_tp_json_encode( $data );
            if ( $parsed_args['exit'] ) die();
        }//3921
        /**
         * @description Kills TailoredPress execution and displays JSONP response with an error message.
         * @param $message
         * @param string $title
         * @param array $args
         */
        protected function _tp_jsonp_die_handler( $message, $title = '',  ...$args ):void{
            @list( $message, $title, $parsed_args ) = $this->_tp_die_process_input( $message, $title, $args );
            $data =['code'=> $parsed_args['code'],'message' => $message,'data' => ['status' => $parsed_args['response'],],
                'additional_errors' => $parsed_args['additional_errors'], 'title' => $title,];
            if ( ! headers_sent() ) {
                header( "Content-Type: application/javascript; charset={$parsed_args['charset']}" );
                header( 'X-Content-Type-Options: nosniff' );
                header( 'X-Robots-Tag: noindex' );
                if ( null !== $parsed_args['response'] ) $this->_status_header( $parsed_args['response'] );
                $this->_nocache_headers();
            }
            $result = $this->_tp_json_encode( $data );
            $jsonp_callback = $_GET['_jsonp'];
            echo "/**/{$jsonp_callback}({$result})";
            if ( $parsed_args['exit'] ) die();
        }//3959
    }
}else die;