<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:02
 */
namespace TP_Core\Traits\Pluggables;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_mailer;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\PHP\Mailer\PHPMailer;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\PHP\Mailer\Mailer_Exception;
if(ABSPATH){
    trait _pluggable_01 {
        use _init_db;
        use _init_error;
        use _init_user;
        use _init_mailer;
        /**
         * @description Changes the current user by ID or name.
         * @param $id
         * @param string $name
         * @return TP_User
         */
        protected function _tp_set_current_user( $id, $name = '' ):TP_User{
            if ( isset( $this->tp_current_user )&& ( $this->tp_current_user instanceof TP_User )&& ( $id === $this->tp_current_user->ID )&&( null !== $id ))
                return $this->tp_current_user;
            $this->tp_current_user = new TP_User( $id, $name );
            $this->_setup_user_data( $this->tp_current_user->ID );
            $this->_do_action( 'set_current_user' );
            return $this->tp_current_user;
        }//27
        /**
         * @description Retrieve the current user object.
         * @return mixed
         */
        protected function _tp_get_user_current(){
            return $this->_tp_get_current_user();//todo
        }//71
        /**
         * @description Retrieve user info by user ID.
         * @param $user_id
         * @return bool|TP_User
         */
        protected function _get_user_data( $user_id ){
            return $this->_get_user_by( 'id', $user_id );
        }//83
        /**
         * @description Retrieve user info by a given field
         * @param $field
         * @param $value
         * @return bool|TP_User
         */
        protected function _get_user_by( $field, $value ){
            $userdata = TP_User::get_data_by( $field, $value );
            if ( ! $userdata ) return false;
            if ( $this->tp_current_user instanceof TP_User && $this->tp_current_user->ID === (int) $userdata->ID )
                return $this->tp_current_user;
            $user = new TP_User;
            $user->init( $userdata );
            return $user;
        }//102
        /**
         * @description Retrieve info for user lists to prevent multiple queries by get_userdata()
         * @param $user_ids
         */
        protected function _cache_users( $user_ids ):void{
            $this->tpdb = $this->_init_db();
            $clean = $this->_get_non_cached_ids( $user_ids, 'users' );
            if ( empty( $clean ) ) return;
            $list = implode( ',', $clean );
            $users = $this->tpdb->get_results( TP_SELECT ." * FROM " . $this->tpdb->users ." WHERE ID IN ($list)" );
            $ids = array();
            foreach ( $users as $user ) {
                $this->_update_user_caches( $user );
                $ids[] = $user->ID;
            }
            $this->_update_meta_cache( 'user', $ids );
        }//132
        /**
         * @description Sends an email, similar to PHP's mail function.
         * @param $to
         * @param $subject
         * @param $message
         * @param mixed $headers
         * @param \array[] ...$attachments
         * @return bool
         */
        protected function _tp_mail( $to, $subject, $message,$headers = '',array ...$attachments):?bool{
            $atts = $this->_apply_filters( 'tp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
            $pre_tp_mail = $this->_apply_filters( 'pre_wp_mail', null, $atts );
            if ( null !== $pre_tp_mail ) return $pre_tp_mail;
            if ( isset( $atts['to'] ) ) $to = $atts['to'];
            if ( ! is_array( $to ) ) $to = explode( ',', $to );
            if ( isset( $atts['subject'] ) ) $subject = $atts['subject'];
            if ( isset( $atts['message'] ) ) $message = $atts['message'];
            if ( isset( $atts['headers'] ) ) $headers = $atts['headers'];
            if ( isset( $atts['attachments'] ) ) $attachments = $atts['attachments'];
            if ( ! is_array( $attachments ) ) $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
            $phpmailer = $this->_init_mailer();
            if( $phpmailer instanceof PHPMailer ) $phpmailer::$validator = static function ( $email ) {
                return (bool) (new static)->_is_email( $email );
            };
            // Headers.
            $cc       = [];
            $bcc      = [];
            $reply_to = [];
            if ( empty( $headers ) ) $headers = [];
            else{
                if ( ! is_array((array)$headers ) ) $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
                else $tempheaders = $headers;
                $headers = [];
                // If it's actually got contents.
                if ( ! empty( $tempheaders ) ) {
                    // Iterate through the raw headers.
                    foreach ( (array) $tempheaders as $header ) {
                        if ( strpos( $header, ':' ) === false ) {
                            if ( false !== stripos( $header, 'boundary=' ) ) {
                                $parts    = preg_split( '/boundary=/i', trim( $header ) );
                                $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                            }
                            continue;
                        }
                        // Explode them out.
                        @list( $name, $content ) = explode( ':', trim( $header ), 2 );
                        // Cleanup crew.
                        $name    = trim( $name );
                        $content = trim( $content );
                        switch ( strtolower( $name ) ) {
                            // Mainly for legacy -- process a "From:" header if it's there.
                            case 'from':
                                $bracket_pos = strpos( $content, '<' );
                                if ( false !== $bracket_pos ) {
                                    // Text before the bracketed email is the "From" name.
                                    if ( $bracket_pos > 0 ) {
                                        $from_name = substr( $content, 0, $bracket_pos - 1 );
                                        $from_name = str_replace( '"', '', $from_name );
                                        $from_name = trim( $from_name );
                                    }
                                    $from_email = substr( $content, $bracket_pos + 1 );
                                    $from_email = str_replace( '>', '', $from_email );
                                    $from_email = trim( $from_email );
                                    // Avoid setting an empty $from_email.
                                } elseif ( '' !== trim( $content ) ) {
                                    $from_email = trim( $content );
                                }
                                break;
                            case 'content-type':
                                if ( strpos( $content, ';' ) !== false ) {
                                    @list( $type, $charset_content ) = explode( ';', $content );
                                    $content_type                   = trim( $type );
                                    if ( false !== stripos( $charset_content, 'charset=' ) )
                                        $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                    elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                        $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                        $charset  = '';
                                    }
                                    // Avoid setting an empty $content_type.
                                } elseif ( '' !== trim( $content ) ) $content_type = trim( $content );
                                break;
                            case 'cc':
                                $cc = array_merge($cc, explode( ',', $content ) );
                                break;
                            case 'bcc':
                                $bcc = array_merge($bcc, explode( ',', $content ) );
                                break;
                            case 'reply-to':
                                $reply_to = array_merge($reply_to, explode( ',', $content ) );
                                break;
                            default:
                                // Add it to our grand headers array.
                                $headers[ trim( $name ) ] = trim( $content );
                                break;
                        }
                    }
                }
            }
            $phpmailer->clearAllRecipients();
            $phpmailer->clearAttachments();
            $phpmailer->clearCustomHeaders();
            $phpmailer->clearReplyTos();
            if ( ! isset( $from_name ) ) $from_name = 'TailoredPress';
            if ( ! isset( $from_email ) ) {
                $sitename = $this->_tp_parse_url( $this->_network_home_url(), PHP_URL_HOST );
                if (strpos($sitename, 'www.') === 0) $sitename = substr( $sitename, 4 );
                $from_email = 'tailoredpress@' . $sitename;
            }
            $from_email = $this->_apply_filters( 'tp_mail_from', $from_email );
            $from_name = $this->_apply_filters( 'tp_mail_from_name', $from_name );
            try {
                $phpmailer->setFrom( $from_email, $from_name, false );
            } catch ( Mailer_Exception $e ) {
                $mail_error_data                             = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
                $mail_error_data['phpmailer_exception_code'] = $e->getCode();
                $this->_do_action( 'tp_mail_failed', new TP_Error( 'tp_mail_failed', $e->getMessage(), $mail_error_data ) );
                return false;
            }
            $phpmailer->Subject = $subject;
            $phpmailer->Body    = $message;
            $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );
            foreach ( $address_headers as $address_header => $addresses ) {
                if ( empty( $addresses ) ) continue;
                foreach ( (array) $addresses as $address ) {
                    try {
                        $recipient_name = '';
                        if (preg_match('/(.*)<(.+)>/', $address, $matches) && count($matches) === 3) {
                            @list($recipient_name,$address) = $matches;//todo lookup
                        }
                        switch ( $address_header ) {
                            case 'to':
                                $phpmailer->addAddress( $address, $recipient_name );
                                break;
                            case 'cc':
                                $phpmailer->addCC( $address, $recipient_name );
                                break;
                            case 'bcc':
                                $phpmailer->addBCC( $address, $recipient_name );
                                break;
                            case 'reply_to':
                                $phpmailer->addReplyTo( $address, $recipient_name );
                                break;
                        }
                    } catch ( Mailer_Exception $e ){continue;}
                }
            }
            $phpmailer->isMail();
            if ( ! isset( $content_type ) )  $content_type = 'text/plain';
            $content_type = $this->_apply_filters( 'tp_mail_content_type', $content_type );
            $phpmailer->ContentType = $content_type;
            if ( 'text/html' === $content_type ) {
                $phpmailer->isHTML( true );
            }
            // If we don't have a charset from the input headers.
            if ( ! isset( $charset ) ) $charset = $this->_get_bloginfo( 'charset' );
            $phpmailer->CharSet = $this->_apply_filters( 'tp_mail_charset', $charset );
            if ( ! empty( $headers ) ) {
                foreach ( (array) $headers as $name => $content ) {
                    if ( ! in_array( $name, array( 'MIME-Version', 'X-Mailer' ), true ) ) {
                        try {
                            $phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
                        } catch ( Mailer_Exception $e ) {
                            continue;
                        }
                    }
                }
                if ( ! empty( $boundary ) && false !== stripos( $content_type, 'multipart' )) {
                    $phpmailer->addCustomHeader( sprintf( 'Content-Type: %s; boundary="%s"', $content_type, $boundary ) );
                }
            }
            if ( ! empty( $attachments ) ) {
                foreach ( $attachments as $attachment ) {
                    try {
                        $phpmailer->addAttachment( $attachment );
                    } catch ( Mailer_Exception $e ) {
                        continue;
                    }
                }
            }
            $this->_do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );
            $mail_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
            try {
                $send = $phpmailer->send();
                $this->_do_action( 'tp_mail_succeeded', $mail_data );
                return $send;
            } catch ( Mailer_Exception $e ) {
                $mail_data['phpmailer_exception_code'] = $e->getCode();
                $this->_do_action( 'tp_mail_failed', new TP_Error( 'tp_mail_failed', $e->getMessage(), $mail_data ) );
                return false;
            }
        }//182
        /**
         * @description  Authenticate a user, confirming the login credentials are valid.
         * @param $username
         * @param $password
         * @return TP_Error
         */
        protected function _tp_authenticate( $username, $password ):TP_Error{
            $username = $this->_sanitize_user( $username );
            $password = trim( $password );
            $user = $this->_apply_filters( 'authenticate', null, $username, $password );
            if ( null === $user ) {
                // TODO: What should the error message be? (Or would these even happen?)
                // Only needed if all authentication handlers fail to return anything.
                $user = new TP_Error( 'authentication_failed', $this->__( '<strong>Error</strong>: Invalid username, email address or incorrect password.' ) );
            }
            $ignore_codes = array( 'empty_username', 'empty_password' );
            if ( $this->_init_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes, true ) ) {
                $error = $user;
                $this->_do_action( 'tp_login_failed', $username, $error );
            }
            return $user;
        }//590
    }
}else die;