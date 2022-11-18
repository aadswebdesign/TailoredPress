<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:02
 */
namespace TP_Core\Traits\Pluggables;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\HTTP\TP_Http;
if(ABSPATH){
    trait _pluggable_03 {
        use _init_db;
        /**
         * todo should become fetch
         * @description Verifies the Ajax request to prevent processing requests external of the blog.
         * @param int $action
         * @param bool $query_arg
         * @param bool $die
         * @return mixed
         */
        protected function _check_async_referer( $action = -1, $query_arg = false, $die = true ){
            if ( -1 === $action )
                $this->_doing_it_wrong( __FUNCTION__, $this->__( 'You should specify an action to be verified by using the first parameter.' ), '0.0.1' );
            $nonce = '';
            if ( $query_arg && isset( $_REQUEST[ $query_arg ] ) ) $nonce = $_REQUEST[ $query_arg ];
            elseif ( isset( $_REQUEST['_fetch_nonce'] ) ) $nonce = $_REQUEST['_fetch_nonce'];
            elseif ( isset( $_REQUEST['_tp_nonce'] ) ) $nonce = $_REQUEST['_tp_nonce'];
            $result = $this->_tp_verify_nonce( $nonce, $action );
            $this->_do_action( 'check_fetch_referer', $action, $result );
            if ( $die && false === $result ) {
                if ( $this->_tp_doing_async() ) $this->_tp_die( -1, 403 );// todo should become doing_fetch
                else die( '-1' );
            }
            return $result;
        }//1237
        /**
         * @description Redirects to another page.
         * @param $location
         * @param int $status
         * @param string $x_redirect_by
         * @return bool
         */
        protected function _tp_redirect( $location, $status = 302, $x_redirect_by = 'TailoredPress' ):bool{
            $location = $this->_apply_filters( 'tp_redirect', $location, $status );
            $status = $this->_apply_filters( 'tp_redirect_status', $status, $location );
            if ( ! $location ) return false;
            if ( $status < 300 || 399 < $status )
                $this->_tp_die( $this->__( 'HTTP redirect status code must be a redirection code, 3xx.' ) );
            $location = $this->_tp_sanitize_redirect( $location );
            if ( ! $this->tp_is_IIS && 'cgi-fcgi' !== PHP_SAPI )
                $this->_status_header( $status ); // This causes problems on IIS and some FastCGI setups.
            $x_redirect_by = $this->_apply_filters( 'x_redirect_by', $x_redirect_by, $status, $location );
            if ( is_string( $x_redirect_by ) ) header( "X-Redirect-By: $x_redirect_by" );
            header( "Location: $location", true, $status );
            return true;
        }//1305
        /**
         * @description Sanitizes a URL for use in a redirect.
         * @param $location
         * @return mixed
         */
        protected function _tp_sanitize_redirect( $location ){
            $location = str_replace( ' ', '%20', $location );
            $regex    = '/((?:/';
            $regex .= '[\xC2-\xDF][\x80-\xBF]'; # double-byte sequences   110xxxxx 10xxxxxx
            $regex .= '|\xE0[\xA0-\xBF][\x80-\xBF]'; # triple-byte sequences   1110xxxx 10xxxxxx * 2
            $regex .= '|[\xE1-\xEC][\x80-\xBF]{2}';
            $regex .= '|\xED[\x80-\x9F][\x80-\xBF]';
            $regex .= '|[\xEE-\xEF][\x80-\xBF]{2}';
            $regex .= '|\xF0[\x90-\xBF][\x80-\xBF]{2}'; # four-byte sequences   11110xxx 10xxxxxx * 3
            $regex .= '|[\xF1-\xF3][\x80-\xBF]{3}';
            $regex .= '|\xF4[\x80-\x8F][\x80-\xBF]{2}';
            $regex .= '){1,40})/x'; # ...one or more times
            $location = preg_replace_callback( $regex, '_tp_sanitize_utf8_in_redirect', $location );
            $location = preg_replace( '|[^a-z0-9-~+_.?#=&;,/:%!*\[\]()@]|i', '', $location );
            $location = $this->_tp_kses_no_null( $location );
            $strip = array( '%0d', '%0a', '%0D', '%0A' );
            return $this->_deep_replace( $strip, $location );
        }//1373
        /**
         * @description URL encode UTF-8 characters in a URL.
         * @param $matches
         * @return string
         */
        protected function _tp_sanitize_utf8_in_redirect( $matches ):string{
            return urlencode( $matches[0] );
        }//1410
        /**
         * @description Performs a safe (local) redirect, using tp_redirect().
         * @param $location
         * @param int $status
         * @param string $x_redirect_by
         * @return bool
         */
        protected function _tp_safe_redirect( $location, $status = 302, $x_redirect_by = 'TailoredPress' ):bool{
            $location = $this->_tp_sanitize_redirect( $location );
            $location = $this->_tp_validate_redirect( $location, $this->_apply_filters( 'tp_safe_redirect_fallback', $this->_admin_url(), $status ) );
            return $this->_tp_redirect( $location, $status, $x_redirect_by );
        }//1448
        /**
         * @description Validates a URL for use in a redirect.
         * @param $location
         * @param string $default
         * @return mixed|string
         */
        protected function _tp_validate_redirect( $location, $default = '' ){
            $location = $this->_tp_sanitize_redirect( trim( $location, " \t\n\r\0\x08\x0B" ) );
            if (strpos($location, '//') === 0) $location = 'http:' . $location;
            $cut  = strpos( $location, '?' );
            $test = $cut ? substr( $location, 0, $cut ) : $location;
            $lp = parse_url( $test );
            // Give up if malformed URL.
            if ( false === $lp ) return $default;
            // Allow only 'http' and 'https' schemes. No 'data:', etc.
            if ( isset( $lp['scheme'] ) && ! ( 'http' === $lp['scheme'] || 'https' === $lp['scheme'] ) )
                return $default;
            if ( ! isset( $lp['host'] ) && ! empty( $lp['path'] ) && '/' !== $lp['path'][0] ) {
                $path = '';
                if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
                    $path = dirname( parse_url( 'http://placeholder' . $_SERVER['REQUEST_URI'], PHP_URL_PATH ) . '?' );
                    $path = $this->_tp_normalize_path( $path );
                }
                $location = '/' . ltrim( $path . '/', '/' ) . $location;
            }
            if ( ! isset( $lp['host'] ) && ( isset( $lp['scheme'] ) || isset( $lp['user'] ) || isset( $lp['pass'] ) || isset( $lp['port'] ) ) )
                return $default;
            foreach ( array( 'user', 'pass', 'host' ) as $component ) {
                if ( isset( $lp[ $component ] ) && strpbrk( $lp[ $component ], ':/?#@' ) ) return $default;
            }
            $tp_parse = parse_url( $this->_home_url() );
            $allowed_hosts = (array) $this->_apply_filters( 'allowed_redirect_hosts', array( $tp_parse['host'] ),$lp['host'] ?? '' );
            if ( isset( $lp['host'] ) && ( ! in_array( $lp['host'], $allowed_hosts, true ) && strtolower( $tp_parse['host'] ) !== $lp['host'] ) )
                $location = $default;
            return $location;
        }//1483
        /**
         * @description Notify an author (and/or others) of a comment/trackback/pingback on a post.
         * @param $comment_id
         * @return bool
         */
        protected function _tp_notify_post_author( $comment_id):bool{
            $comment = $this->_get_comment( $comment_id );
            if ( empty( $comment ) || empty( $comment->comment_post_ID ) ) return false;
            $post   = $this->_get_post( $comment->comment_post_ID );
            $author = $this->_get_user_data( $post->post_author );
            $emails = array();
            if ( $author ) $emails[] = $author->user_email;
            $emails = $this->_apply_filters( 'comment_notification_recipients', $emails, $comment->comment_ID );
            $emails = array_filter( $emails );
            if ( ! count( $emails ) ) return false;
            $emails = array_flip( $emails );
            $notify_author = $this->_apply_filters( 'comment_notification_notify_author', false, $comment->comment_ID );
            if ( $author && ! $notify_author && $comment->user_id === $post->post_author )
                unset( $emails[ $author->user_email ] );
            if ( $author && ! $notify_author && $this->_get_current_user_id() === $post->post_author )
                unset( $emails[ $author->user_email ] );
            if ( $author && ! $notify_author && ! $this->_user_can( $post->post_author, 'read_post', $post->ID ) )
                unset( $emails[ $author->user_email ] );
            if ( ! count( $emails ) )  return false;
            else  $emails = array_flip( $emails );
            $switched_locale = $this->_switch_to_locale( $this->_get_locale() );
            $comment_author_domain = '';
            if ( TP_Http::is_ip_address( $comment->comment_author_IP ) )
                $comment_author_domain = gethostbyaddr( $comment->comment_author_IP );
            $blogname        = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            $comment_content = $this->_tp_special_chars_decode( $comment->comment_content );
            switch ( $comment->comment_type ) {
                case 'trackback':
                    /* translators: %s: Post title. */
                    $notify_message = sprintf( $this->__( 'New trackback on your post "%s"' ), $post->post_title ) . "\r\n";
                    /* translators: 1: Trackback/pingback website name, 2: Website IP address, 3: Website hostname. */
                    $notify_message .= sprintf( $this->__( 'Website: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                    /* translators: %s: Trackback/pingback/comment author URL. */
                    $notify_message .= sprintf( $this->__( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                    /* translators: %s: Comment text. */
                    $notify_message .= sprintf( $this->__( 'Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
                    $notify_message .= $this->__( 'You can see all trackbacks on this post here:' ) . "\r\n";
                    /* translators: Trackback notification email subject. 1: Site title, 2: Post title. */
                    $subject = sprintf( $this->__( '[%1$s] Trackback: "%2$s"' ), $blogname, $post->post_title );
                    break;
                case 'pingback':
                    /* translators: %s: Post title. */
                    $notify_message = sprintf( $this->__( 'New pingback on your post "%s"' ), $post->post_title ) . "\r\n";
                    /* translators: 1: Trackback/pingback website name, 2: Website IP address, 3: Website hostname. */
                    $notify_message .= sprintf( $this->__( 'Website: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                    /* translators: %s: Trackback/pingback/comment author URL. */
                    $notify_message .= sprintf( $this->__( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                    /* translators: %s: Comment text. */
                    $notify_message .= sprintf( $this->__( 'Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
                    $notify_message .= $this->__( 'You can see all pingbacks on this post here:' ) . "\r\n";
                    /* translators: Pingback notification email subject. 1: Site title, 2: Post title. */
                    $subject = sprintf( $this->__( '[%1$s] Pingback: "%2$s"' ), $blogname, $post->post_title );
                    break;

                default: // Comments.
                    /* translators: %s: Post title. */
                    $notify_message = sprintf( $this->__( 'New comment on your post "%s"' ), $post->post_title ) . "\r\n";
                    /* translators: 1: Comment author's name, 2: Comment author's IP address, 3: Comment author's hostname. */
                    $notify_message .= sprintf( $this->__( 'Author: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                    /* translators: %s: Comment author email. */
                    $notify_message .= sprintf( $this->__( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
                    /* translators: %s: Trackback/pingback/comment author URL. */
                    $notify_message .= sprintf( $this->__( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                    if ( $comment->comment_parent && $this->_user_can( $post->post_author, 'edit_comment', $comment->comment_parent ) )
                        $notify_message .= sprintf( $this->__( 'In reply to: %s' ), $this->_admin_url( "comment.php?action=editcomment&c={$comment->comment_parent}#wpbody-content" ) ) . "\r\n";
                    $notify_message .= sprintf( $this->__( 'Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
                    $notify_message .= $this->__( 'You can see all comments on this post here:' ) . "\r\n";
                    $subject = sprintf( $this->__( '[%1$s] Comment: "%2$s"' ), $blogname, $post->post_title );
                    break;
            }
            $notify_message .= $this->_get_permalink( $comment->comment_post_ID ) . "#comments\r\n\r\n";
            $notify_message .= sprintf( $this->__( 'Permalink: %s' ), $this->_get_comment_link( $comment ) ) . "\r\n";
            if ( $this->_user_can( $post->post_author, 'edit_comment', $comment->comment_ID ) ) {
                if ( EMPTY_TRASH_DAYS )
                    $notify_message .= sprintf( $this->__( 'Trash it: %s' ), $this->_admin_url( "comment.php?action=trash&c={$comment->comment_ID}#tpbody-content" ) ) . "\r\n";
                else
                    $notify_message .= sprintf( $this->__( 'Delete it: %s' ), $this->_admin_url( "comment.php?action=delete&c={$comment->comment_ID}#tpbody-content" ) ) . "\r\n";
                $notify_message .= sprintf( $this->__( 'Spam it: %s' ), $this->_admin_url( "comment.php?action=spam&c={$comment->comment_ID}#tpbody-content" ) ) . "\r\n";
            }
            $tp_email = 'tailoredpress@' . preg_replace( '#^www\.#', '', $this->_tp_parse_url( $this->_network_home_url(), PHP_URL_HOST ) );
            if ( '' === $comment->comment_author ) {
                $from = "From: \"$blogname\" <$tp_email>";
                if ( '' !== $comment->comment_author_email )
                    $reply_to = "Reply-To: $comment->comment_author_email";
            } else {
                $from = "From: \"$comment->comment_author\" <$tp_email>";
                if ( '' !== $comment->comment_author_email )
                    $reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
            }
            $message_headers = "$from\n" . 'Content-Type: text/plain; charset="' . $this->_get_option( 'blog_charset' ) . "\"\n";
            if ( isset( $reply_to ) ) $message_headers .= $reply_to . "\n";
            $notify_message = $this->_apply_filters( 'comment_notification_text', $notify_message, $comment->comment_ID );
            $subject = $this->_apply_filters( 'comment_notification_subject', $subject, $comment->comment_ID );
            $message_headers = $this->_apply_filters( 'comment_notification_headers', $message_headers, $comment->comment_ID );
            foreach ( $emails as $email )
                $this->_tp_mail( $email, $this->_tp_special_chars_decode( $subject ), $notify_message, $message_headers );
            if ( $switched_locale ) $this->_restore_previous_locale();
            return true;
        }//1559
        //@description
        protected function _tp_notify_moderator( $comment_id ):bool{
            $this->tpdb = $this->_init_db();
            $maybe_notify = $this->_get_option( 'moderation_notify' );
            $maybe_notify = $this->_apply_filters( 'notify_moderator', $maybe_notify, $comment_id );
            if ( ! $maybe_notify ) {return true;}
            $comment = $this->_get_comment( $comment_id );
            $post    = $this->_get_post( $comment->comment_post_ID );
            $user    = $this->_get_user_data( $post->post_author );
            // Send to the administration and to the post author if the author can modify the comment.
            $emails = array( $this->_get_option( 'admin_email' ) );
            if ($user && !empty($user->user_email) && $this->_user_can($user->ID, 'edit_comment', $comment_id) && 0 !== strcasecmp($user->user_email, $this->_get_option('admin_email'))) {
                $emails[] = $user->user_email;
            }
            $switched_locale = $this->_switch_to_locale( $this->_get_locale() );
            $comment_author_domain = '';
            if ( TP_Http::is_ip_address( $comment->comment_author_IP ) ) {
                $comment_author_domain = gethostbyaddr( $comment->comment_author_IP );
            }
            $comments_waiting = $this->tpdb->get_var( TP_SELECT . " COUNT(*) FROM $this->tpdb->comments WHERE comment_approved = '0'" );
            // The blogname option is escaped with esc_html() on the way into the database in sanitize_option().
            // We want to reverse this for the plain text arena of emails.
            $blogname        = $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES );
            $comment_content = $this->_tp_special_chars_decode( $comment->comment_content );
            switch ( $comment->comment_type ) {
                case 'trackback':
                    /* translators: %s: Post title. */
                    $notify_message  = sprintf( $this->__( 'A new trackback on the post "%s" is waiting for your approval' ), $post->post_title ) . "\r\n";
                    $notify_message .= $this->_get_permalink( $comment->comment_post_ID ) . "\r\n\r\n";
                    /* translators: 1: Trackback/pingback website name, 2: Website IP address, 3: Website hostname. */
                    $notify_message .= sprintf( $this->__( 'Website: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                    /* translators: %s: Trackback/pingback/comment author URL. */
                    $notify_message .= sprintf( $this->__( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                    $notify_message .= $this->__( 'Trackback excerpt: ' ) . "\r\n" . $comment_content . "\r\n\r\n";
                    break;
                case 'pingback':
                    /* translators: %s: Post title. */
                    $notify_message  = sprintf( $this->__( 'A new pingback on the post "%s" is waiting for your approval' ), $post->post_title ) . "\r\n";
                    $notify_message .= $this->_get_permalink( $comment->comment_post_ID ) . "\r\n\r\n";
                    /* translators: 1: Trackback/pingback website name, 2: Website IP address, 3: Website hostname. */
                    $notify_message .= sprintf( $this->__( 'Website: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                    /* translators: %s: Trackback/pingback/comment author URL. */
                    $notify_message .= sprintf( $this->__( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                    $notify_message .= $this->__( 'Pingback excerpt: ' ) . "\r\n" . $comment_content . "\r\n\r\n";
                    break;
                default: // Comments.
                    /* translators: %s: Post title. */
                    $notify_message  = sprintf( $this->__( 'A new comment on the post "%s" is waiting for your approval' ), $post->post_title ) . "\r\n";
                    $notify_message .= $this->_get_permalink( $comment->comment_post_ID ) . "\r\n\r\n";
                    /* translators: 1: Comment author's name, 2: Comment author's IP address, 3: Comment author's hostname. */
                    $notify_message .= sprintf( $this->__( 'Author: %1$s (IP address: %2$s, %3$s)' ), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                    /* translators: %s: Comment author email. */
                    $notify_message .= sprintf( $this->__( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
                    /* translators: %s: Trackback/pingback/comment author URL. */
                    $notify_message .= sprintf( $this->__( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
                    if ( $comment->comment_parent ) {
                        /* translators: Comment moderation. %s: Parent comment edit URL. */
                        $notify_message .= sprintf( $this->__( 'In reply to: %s' ), $this->_admin_url( "comment.php?action=editcomment&c={$comment->comment_parent}#wpbody-content" ) ) . "\r\n";
                    }
                    /* translators: %s: Comment text. */
                    $notify_message .= sprintf( $this->__( 'Comment: %s' ), "\r\n" . $comment_content ) . "\r\n\r\n";
                    break;
            }
            $notify_message .= sprintf( $this->__( 'Approve it: %s' ), $this->_admin_url( "comment.php?action=approve&c={$comment_id}#wpbody-content" ) ) . "\r\n";
            if ( EMPTY_TRASH_DAYS ) {
                /* translators: Comment moderation. %s: Comment action URL. */
                $notify_message .= sprintf( $this->__( 'Trash it: %s' ), $this->_admin_url( "comment.php?action=trash&c={$comment_id}#wpbody-content" ) ) . "\r\n";
            } else {
                /* translators: Comment moderation. %s: Comment action URL. */
                $notify_message .= sprintf( $this->__( 'Delete it: %s' ), $this->_admin_url( "comment.php?action=delete&c={$comment_id}#wpbody-content" ) ) . "\r\n";
            }
            /* translators: Comment moderation. %s: Comment action URL. */
            $notify_message .= sprintf( $this->__( 'Spam it: %s' ), $this->_admin_url( "comment.php?action=spam&c={$comment_id}#wpbody-content" ) ) . "\r\n";
            $notify_message .= sprintf(
                /* translators: Comment moderation. %s: Number of comments awaiting approval. */
                    $this->_n(
                        'Currently %s comment is waiting for approval. Please visit the moderation panel:',
                        'Currently %s comments are waiting for approval. Please visit the moderation panel:',
                        $comments_waiting
                    ),
                    $this->_number_format_i18n( $comments_waiting )
                ) . "\r\n";
            $notify_message .= $this->_admin_url( 'edit-comments.php?comment_status=moderated#wpbody-content' ) . "\r\n";
            /* translators: Comment moderation notification email subject. 1: Site title, 2: Post title. */
            $subject         = sprintf( $this->__( '[%1$s] Please moderate: "%2$s"' ), $blogname, $post->post_title );
            $message_headers = '';
            $emails = $this->_apply_filters( 'comment_moderation_recipients', $emails, $comment_id );
            $notify_message = $this->_apply_filters( 'comment_moderation_text', $notify_message, $comment_id );
            $subject = $this->_apply_filters( 'comment_moderation_subject', $subject, $comment_id );
            $message_headers = $this->_apply_filters( 'comment_moderation_headers', $message_headers, $comment_id );
            foreach ( $emails as $email ) {
                $this->_tp_mail( $email, $this->_tp_special_chars_decode( $subject ), $notify_message, $message_headers );
            }
            if ( $switched_locale ) {$this->_restore_previous_locale(); }
            return true;
        }//1793
    }
}else die;