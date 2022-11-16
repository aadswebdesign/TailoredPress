<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_15{
        protected function _return_empty_array():array{return [];}//6673
        protected function _return_null():bool{return null;}//6686
        protected function _return_empty_string():string{return '';}//6701
        /**
         * @description Send a HTTP header to disable content type sniffing in browsers which support it.
         */
        protected function _send_no_sniff_header():void{
            header( 'X-Content-Type-Options: nosniff' );
        }//6706
        /**
         * @description Return a MySQL expression for selecting the week number based on the start_of_week option.
         * @param $column
         * @return string
         */
        protected function _tp_mysql_week( $column ):string{
            $start_of_week = (int) $this->_get_option( 'start_of_week' );
            switch ( $start_of_week ) {
                case 1:
                    return "WEEK( $column, 1 )";
                case 2: case 3: case 4: case 5: case 6:
                    return "WEEK( DATE_SUB( $column, INTERVAL $start_of_week DAY ), 0 )";
                case 0:
                default:
                    return "WEEK( $column, 0 )";
            }
        }//6726
        /**
         * @description Find hierarchy loops using a callback function that maps object IDs to parent IDs.
         * @param $callback
         * @param $start
         * @param $start_parent
         * @param array $callback_args
         * @return mixed
         */
        protected function _tp_find_hierarchy_loop( $callback, $start, $start_parent, $callback_args = [] ){
            $override = is_null( $start_parent ) ? array() : array( $start => $start_parent );
            $arbitrary_loop_member = $this->_tp_find_hierarchy_loop_tortoise_hare( $callback, $start, $override, $callback_args );
            if ( ! $arbitrary_loop_member ) return array();
           return $this->_tp_find_hierarchy_loop_tortoise_hare( $callback, $arbitrary_loop_member, $override, $callback_args, true );
        }//6756
        /**
         * @description Use the "The Tortoise and the Hare" algorithm to detect loops.
         * @param $callback
         * @param $start
         * @param array $override
         * @param array $callback_args
         * @param bool $_return_loop
         * @return array|bool
         */
        protected function _tp_find_hierarchy_loop_tortoise_hare( $callback, $start, $override = [], $callback_args = [], $_return_loop = false ){
            $tortoise = $start;
            $hare = $start;
            $this->tp_evanescent_hare = $start;
            $return = [];
            // Set evanescent_hare to one past hare.
            // Increment hare two steps.
            while ($tortoise &&( $this->tp_evanescent_hare = $override[$hare] ?? call_user_func_array($callback, array_merge(array($hare), $callback_args)))
                && ( $hare = $override[$this->tp_evanescent_hare] ?? call_user_func_array($callback, array_merge(array($this->tp_evanescent_hare), $callback_args)))){
                if ( $_return_loop ) {
                    $return[ $tortoise ]        = true;
                    $return[ $this->tp_evanescent_hare ] = true;
                    $return[ $hare ]            = true;
                }
                // Tortoise got lapped - must be a loop.
                if ( $tortoise === $this->tp_evanescent_hare || $tortoise === $hare ) return $_return_loop ? $return : $tortoise;
                // Increment tortoise by one step.
                $tortoise = $override[$tortoise] ?? call_user_func_array($callback, array_merge(array($tortoise), $callback_args));
            }
            return false;
        }//6787
        /**
         * @description Send a HTTP header to limit rendering of pages to same origin iframes.
         */
        protected function _send_frame_options_header():void{
            header( 'X-Frame-Options: SAMEORIGIN' );
        }//6820
        /**
         * @description Retrieve a list of protocols to allow in HTML attributes.
         * @return array
         */
        protected function _tp_allowed_protocols():array{
            static $protocols = [];
            if ( empty( $protocols ) )
                $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' );
            if ( ! $this->_did_action( 'tp_loaded' ) )
                $protocols = array_unique( (array) $this->_apply_filters( 'kses_allowed_protocols', $protocols ) );
            return $protocols;
        }//6849
        /**
         * @description Return a comma-separated string of functions that have been called to get
         * @description . to the current point in code.
         * @param null $ignore_class
         * @param int $skip_frames
         * @param bool $pretty
         * @return array|string
         */
        protected function _tp_debug_backtrace_summary( $ignore_class = null, $skip_frames = 0, $pretty = true ){
            static $truncate_paths;
            $trace       = debug_backtrace( false );
            $caller      = array();
            $check_class = ! is_null( $ignore_class );
            $skip_frames++; // Skip this function.
            if ( ! isset( $truncate_paths ) ) {
                $truncate_paths = array(
                    $this->_tp_normalize_path( TP_CONTENT_DIR ),
                    $this->_tp_normalize_path( ABSPATH ),
                );
            }
            foreach ( $trace as $call ) {
                if ( $skip_frames > 0 ) {
                    $skip_frames--;
                } elseif ( isset( $call['class'] ) ) {
                    if ( $check_class && $ignore_class === $call['class'] ) continue; // Filter out calls.
                    $caller[] = "{$call['class']}{$call['type']}{$call['function']}";
                } else if ( in_array( $call['function'], array( 'do_action', 'apply_filters', 'do_action_ref_array', 'apply_filters_ref_array' ), true ) )
                    $caller[] = "{$call['function']}('{$call['args'][0]}')";
                elseif ( in_array( $call['function'], array( 'include', 'include_once', 'require', 'require_once' ), true ) ) {
                    $filename = $call['args'][0] ?? '';
                    $caller[] = $call['function'] . "('" . str_replace( $truncate_paths, '', $this->_tp_normalize_path( $filename ) ) . "')";
                } else $caller[] = $call['function'];
            }
            if ( $pretty )  return implode( ', ', array_reverse( $caller ) );
            else  return $caller;
        }//6887
    }
}else die;