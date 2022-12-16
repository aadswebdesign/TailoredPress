<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 20:32
 */
namespace TP_Core\Libs\DB;
use TP_Core\Libs\DB\Factory\_database_vars;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_15;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Actions\_action_01;
if(ABSPATH){
    class TP_Db {
        use _database_vars, _init_error, _filter_01, _methods_06, _methods_08;
        use _methods_12, _methods_15, _methods_16, _methods_17;
        use _action_01, _I10n_01, _I10n_02, _I10n_03,_I10n_04, _load_04;
        protected $_checking_collation;//todo added
        public function __construct( $db_user = '', $db_password = '', $db_name = '', $db_host = '' ) {
            $this->tp_version = TP_VERSION;
            $this->required_mysql_version = TP_REQUIRED_MYSQL_VERSION;
            if ( TP_DEBUG && TP_DEBUG_DISPLAY ) $this->show_errors();
            if ( function_exists( 'mysqli_connect' ) )
            $this->__use_mysqli = true;
            else{
                New TP_Error($this->__("As 'mysgl' support has stopped by PHP, this package supports 'mysqli' only!"));
                die;
            }
            $this->_db_user     = $db_user;
            $this->_db_password = $db_password;
            $this->_db_name     = $db_name;
            $this->_db_host     = $db_host;
            if (! defined( 'TP_SETUP_CONFIG' ) ) return;
            $this->db_connect();
        }//700
        /**
         * @description Sets $this->charset and $this->collate.
         */
        public function init_charset(): void{
            $charset = '';
            $collate = '';
            if ( function_exists( 'is_multisite' ) && is_multisite() ) {
                $charset = 'utf8';
                if ( defined( 'DB_COLLATE' ) && DB_COLLATE )
                    $collate = DB_COLLATE;
                else $collate = 'utf8_general_ci';
            } elseif ( defined( 'DB_COLLATE' ) ) $collate = DB_COLLATE;
            if ( defined( 'DB_CHARSET' ) )  $charset = DB_CHARSET;
            $charset_collate = $this->determine_charset( $charset, $collate );
            $this->charset = $charset_collate['charset'];
            $this->collate = $charset_collate['collate'];
        }//791
        /**
         * @description Determines the best charset and collation to use given a charset and collation.
         * @param $charset
         * @param $collate
         * @return array
         */
        public function determine_charset( $charset, $collate ): array{
            if ( ( $this->__use_mysqli && ! ( $this->_dbh instanceof \mysqli ) ) || empty( $this->_dbh ) )
                return compact( 'charset', 'collate' );
            if ( 'utf8' === $charset && $this->has_cap( 'utf8mb4' ) ) $charset = 'utf8mb4';
            if ( 'utf8mb4' === $charset && ! $this->has_cap( 'utf8mb4' ) ) {
                $charset = 'utf8';
                $collate = str_replace( 'utf8mb4_', 'utf8_', $collate );
            }
            if ( 'utf8mb4' === $charset ) {
                if ( ! $collate || 'utf8_general_ci' === $collate ) $collate = 'utf8mb4_unicode_ci';
                else $collate = str_replace( 'utf8_', 'utf8mb4_', $collate );
            }
            if ('utf8mb4_unicode_ci' === $collate && $this->has_cap( 'utf8mb4_520' ))
                $collate = 'utf8mb4_unicode_520_ci';
            return compact( 'charset', 'collate' );
        }//832
        /**
         * @description Sets the connection's character set.
         * @param $dbh
         * @param null $charset
         * @param null $collate
         * @throws \exception
         */
        public function set_charset( $dbh, $charset = null, $collate = null ): void{
            if ( ! isset( $charset ) ) $charset = $this->charset;
            if ( ! isset( $collate ) ) $collate = $this->collate;
            if (! empty( $charset ) && $this->has_cap( 'collation' )) {
                $set_charset_succeeded = true;
                if ( $this->__use_mysqli ) {
                    if ( function_exists( 'mysqli_set_charset' ) && $this->has_cap( 'set_charset' ) )
                        $set_charset_succeeded = mysqli_set_charset( $dbh, $charset );
                    if ( $set_charset_succeeded ) {
                        $query = $this->prepare( 'SET NAMES %s', $charset );
                        if ( ! empty( $collate ) )
                            $query .= $this->prepare( ' COLLATE %s', $collate );
                        mysqli_query( $dbh, $query );
                    }else throw new \exception('mysql is deprecated, use mysqli instead');
                }
            }
        }//872
        /**
         * @description Changes the current SQL mode, and ensures its TailoredPress compatibility.
         * @param array $modes
         * @throws \exception
         */
        public function set_sql_mode( $modes = [] ): void{
            if ( empty( $modes ) ) {
                if ( $this->__use_mysqli )  $res = mysqli_query( $this->_dbh, 'SELECT @@SESSION.sql_mode' );
                else $res = null;
                if ( empty( $res ) ) return;
                if ( $this->__use_mysqli ) {
                    $modes_array = mysqli_fetch_array( $res );
                    if ( empty( $modes_array[0] ) ) return;
                    $modes_str = $modes_array[0];
                } else $modes_str = null;
                if ( empty( $modes_str ) )  return;
                $modes = explode( ',', $modes_str );
            }
            $modes = array_change_key_case( $modes, CASE_UPPER );
            $incompatible_modes = (array) $this->_apply_filters( 'incompatible_sql_modes', $this->_incompatible_modes );
            foreach ( $modes as $i => $mode ) {
                if ( in_array( $mode, $incompatible_modes, true ) ) unset( $modes[ $i ] );
            }
            $modes_str = implode( ',', $modes );
            if ( $this->__use_mysqli )mysqli_query( $this->_dbh, "SET SESSION sql_mode='$modes_str'" );
            else  throw new \exception('mysql is deprecated, use mysqli instead');
        }//918
        /**
         * @description Sets the table prefix for the TailoredPress tables.
         * @param $prefix
         * @param bool $set_table_names
         * @return string|TP_Error
         */
        public function set_prefix( $prefix, $set_table_names = true ){
            if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
                return new TP_Error( 'invalid_db_prefix', 'Invalid database prefix' );
            $old_prefix = $this->_is_multisite() ? '' : $prefix;
            if ( isset( $this->base_prefix ) ) $old_prefix = $this->base_prefix;
            $this->base_prefix = $prefix;
            if ( $set_table_names ) {
                foreach ( $this->tables( 'global' ) as $table => $prefixed_table )
                    $this->$table = $prefixed_table;
                if (empty( $this->blogid ) && $this->_is_multisite())
                    return $old_prefix;
                $this->prefix = $this->get_blog_prefix();
                foreach ( $this->tables( 'blog' ) as $table => $prefixed_table )
                    $this->$table = $prefixed_table;
                foreach ( $this->tables( 'old' ) as $table => $prefixed_table )
                    $this->$table = $prefixed_table;
            }
            return $old_prefix;
        }//983
        /**
         * @description Sets blog ID.
         * @param $blog_id
         * @param int $network_id
         * @return mixed
         */
        public function set_blog_id( $blog_id, $network_id = 0 ){
            if ( ! empty( $network_id ) ) $this->site_id = $network_id;
            $old_blog_id  = $this->blog_id;
            $this->blog_id = $blog_id;
            $this->prefix = $this->get_blog_prefix();
            foreach ( $this->tables( 'blog' ) as $table => $prefixed_table )
                $this->$table = $prefixed_table;
            foreach ( $this->tables( 'old' ) as $table => $prefixed_table )
                $this->$table = $prefixed_table;
            return $old_blog_id;
        }//1028
        /**
         * @description Gets blog prefix.
         * @param null $blog_id
         * @return string
         */
        public function get_blog_prefix( $blog_id = null ): ?string{
            if ( $this->_is_multisite() ) {
                if ( null === $blog_id ) $blog_id = $this->blog_id;
                $blog_id = (int) $blog_id;
                if ( defined( 'MULTISITE' ) && ( 0 === $blog_id || 1 === $blog_id ) )
                    return $this->base_prefix;
                else return $this->base_prefix . $blog_id . '_';
            } else return $this->base_prefix;
        }//1057
        /**
         * @description Returns an array of TailoredPress tables.
         * @param string $scope
         * @param bool $prefix
         * @param int $blog_id
         * @return array
         */
        public function tables( $scope = 'all', $prefix = true, $blog_id = 0 ): array{
            switch ( $scope ) {
                case 'all':
                    $tables = array_merge( $this->global_tables, $this->tables );
                    if ( $this->_is_multisite() ) $tables = array_merge( $tables, $this->ms_global_tables );
                    break;
                case 'blog':
                    $tables = $this->tables;
                    break;
                case 'global':
                    $tables = $this->global_tables;
                    if ( $this->_is_multisite() ) $tables = array_merge( $tables, $this->ms_global_tables );
                    break;
                case 'ms_global':
                    $tables = $this->ms_global_tables;
                    break;
                default:
                    return [];
            }
            if ( $prefix ) {
                if ( ! $blog_id ) $blog_id = $this->blog_id;
                $blog_prefix   = $this->get_blog_prefix( $blog_id );
                $base_prefix   = $this->base_prefix;
                $global_tables = array_merge( $this->global_tables, $this->ms_global_tables );
                foreach ( $tables as $k => $table ) {
                    if ( in_array( $table, $global_tables, true ) )
                        $tables[ $table ] = $base_prefix . $table;
                    else $tables[ $table ] = $blog_prefix . $table;
                    unset( $tables[ $k ] );
                }
                if ( isset( $tables['users'] ) && defined( 'CUSTOM_USER_TABLE' ) )
                    $tables['users'] = CUSTOM_USER_TABLE;
                if ( isset( $tables['user_meta'] ) && defined( 'CUSTOM_USER_META_TABLE' ) )
                    $tables['user_meta'] = CUSTOM_USER_META_TABLE;
            }
            return $tables;
        }//1104
        /**
         * @description Selects a database using the current or provided database connection.
         * @param $db
         * @param null $dbh
         */
        public function select( $db, $dbh = null ): void{
            if ( is_null( $dbh ) ) $dbh = $this->_dbh;
            if ( $this->__use_mysqli ) $success = mysqli_select_db( $dbh, $db );
            else $success = null;
            if (is_null($success) ) {
                $this->ready = false;
                if ( ! $this->_did_action( 'template_redirect' ) ) {
                    $this->_tp_load_translations_early();
                    //todo make this in a way that it ends up in the console log (because that's the place where it belongs)
                    /* translators: %s: Database name. */
                    $message = "<h1>{$this->__( 'Can&#8217;t select database' )}</h1>";
                    $message .= "<p>";
                    $message .= sprintf(
                        $this->__( 'We were able to connect to the database server (which means your username and password is okay) but not able to select the %s database.' ),
                        '<code>' . htmlspecialchars( $db, ENT_QUOTES ) . '</code>'
                    );
                    $message .= "</p>\n";
                    $message .= "<ul>\n";
                    $message .= "<li>{$this->__( 'Are you sure it exists?' )}</li>\n";
                    $message .= "<li>";
                    $message .= sprintf(
                        $this->__( 'Does the user %1$s have permission to use the %2$s database?' ),
                        '<code>' . htmlspecialchars( $this->_db_user, ENT_QUOTES ) . '</code>',
                        '<code>' . htmlspecialchars( $db, ENT_QUOTES ) . '</code>'
                    );
                    $message .= "</li>\n<li>";
                    $message .= sprintf(
                        $this->__( 'On some systems the name of your database is prefixed with your username, so it would be like <code>username_%1$s</code>. Could that be the problem?' ),
                        htmlspecialchars( $db, ENT_QUOTES )
                    );
                    $message .= "</li>\n</ul>\n";
                    $message .= "<p>";
                    $message .= sprintf(
                        $this->__( 'If you don&#8217;t know how to set up a database you should <strong>contact your host</strong>. If all else fails you may find help at the todo.' ),'0.0.1');
                    $message .= "</p>\n";
                    $this->bail( $message, 'db_select_fail' );
                }
            }
        }//1170
        /**
         * @description Real escape, using mysqli_real_escape_string().
         * @param $string
         * @return string
         * @throws \exception
         */
        protected function _real_escape( $string ): string{
            if ( ! is_scalar( $string ) ) return '';
            $escaped = null;
            if ( $this->_dbh ){
                if ( $this->__use_mysqli ) {
                    $escaped = mysqli_real_escape_string( $this->_dbh, $string );
                }else throw new \exception('mysql is deprecated, use mysqli instead');
            }else {
                $class = get_class( $this );
                $this->_tp_load_translations_early();
                $this->_doing_it_wrong( $class, sprintf( $this->__( '%s must set a database connection for use with escaping.' ), $class ), '0.0.1' );
                $escaped = addslashes( $string );
            }
            return $this->add_placeholder_escape( $escaped );
        }//1253
        /**
         * @description Escapes data. Works on arrays.
         * @param $data
         * @return array|string
         */
        public function escape( $data ){
            if ( is_array( $data ) ) {
                foreach ( $data as $k => $v ) {
                    if ( is_array( $v ) ) $data[ $k ] = $this->escape( $v );
                    else $data[ $k ] = $this->_real_escape( $v );
                }
            }else $data = $this->_real_escape( $data );
            return $data;
        }//1287
        /**
         * @description Escapes content by reference for insertion into the database, for security.
         * @param $string
         */
        public function escape_by_ref( &$string ): void{
            if ( ! is_float( $string ) ) $string = $this->_real_escape( $string );
        }//1344
        /**
         * @description Prepares a SQL query for safe execution.
         * @param $query
         * @param array ...$args
         * @return null|string
         */
        public function prepare( $query, ...$args ): ?string{
            if ( is_null( $query )) return null;
            if ( strpos( $query, '%' ) === false ) {
                $this->_tp_load_translations_early();
                /* translators: %s: tpdb::prepare() */
                $this->_doing_it_wrong('tpdb::prepare',
                    sprintf($this->__( 'The query argument of %s must have a placeholder.' ),'tpdb::prepare()'),'0.0.1');
            }
            $passed_as_array = false;
            if ( isset( $args[0] ) && is_array( $args[0] ) && 1 === count( $args ) ) {
                $passed_as_array = true;
                $args            = $args[0];
            }
            foreach ( $args as $arg ) {
                if ( ! is_scalar( $arg ) && ! is_null( $arg ) ) {
                    $this->_tp_load_translations_early();
                    $this->_doing_it_wrong('tpdb::prepare',
                        sprintf( $this->__( 'Unsupported value type (%s).' ), gettype( $arg )),'0.0.1');
                }
            }
            $allowed_format = '(?:[1-9][0-9]*[$])?[-+0-9]*(?: |0|\'.)?[-+0-9]*(?:\.[0-9]+)?';
            $query = str_replace( "'%s'", '%s', $query ); // Strip any existing single quotes.
            //todo $query = str_replace( '"%s"', '%s', $query ); // Strip any existing double quotes.
            $query = preg_replace( '/(?<!%)%s/', "'%s'", $query ); // Quote the strings, avoiding escaped strings like %%s.
            $query = preg_replace( "/(?<!%)(%($allowed_format)?f)/", '%\\2F', $query ); // Force floats to be locale-unaware.
            $query = preg_replace( "/%(?:%|$|(?!($allowed_format)?[sdF]))/", '%%\\1', $query ); // Escape any unescaped percents.
            $placeholders = preg_match_all( "/(^|[^%]|(%%)+)%($allowed_format)?[sdF]/", $query, $matches );
            $args_count = count( $args );
            if ( $args_count !== $placeholders ) {
                if ( 1 === $placeholders && $passed_as_array ) {
                    // If the passed query only expected one argument, but the wrong number of arguments were sent as an array, bail.
                    $this->_tp_load_translations_early();
                    $this->_doing_it_wrong('tpdb::prepare',
                        $this->__( 'The query only expected one placeholder, but an array of multiple placeholders was sent.' ),
                        '0.0.1');
                    return null;
                }
                $this->_tp_load_translations_early();
                /* translators: 1: Number of placeholders, 2: Number of arguments passed. */
                $this->_doing_it_wrong('tpdb::prepare',
                    sprintf( $this->__( 'The query does not contain the correct number of placeholders (%1$d) for the number of arguments passed (%2$d).' ),
                        $placeholders, $args_count), '0.0.1' );
                if ( $args_count < $placeholders ) {
                    $max_numbered_placeholder = ! empty( $matches[3] ) ? max( array_map( 'intval', $matches[3] ) ) : 0;
                    if ( ! $max_numbered_placeholder || $args_count < $max_numbered_placeholder ) return '';
                }
            }
            array_walk( $args, array( $this, 'escape_by_ref' ) );
            $query = vsprintf( $query, $args );
            return $this->add_placeholder_escape( $query );
        }//1395
        /**
         * @description First half of escaping for `LIKE` special characters `%` and `_` before preparing for SQL.
         * @param $text
         * @return string
         */
        public function esc_like( $text ): string{
            return addcslashes( $text, '_%\\' );
        }//1538
        /**
         * @description Prints SQL/DB error.
         * @param string $str
         * @return bool
         */
        public function print_error( $str = '' ): bool
        {
            if ( ! $str ) {
                if ( $this->__use_mysqli ) $str = mysqli_error( $this->_dbh );
                else $str = '';
            }
            $this->__EZ_SQL_ERROR = ['query' => $this->last_query, 'error_str' => $str,];
            if ( $this->suppress_errors ) return false;
            $this->_tp_load_translations_early();
            $caller = $this->get_caller();
            if ( $caller ) $error_str = sprintf( $this->__( 'TailoredPress database error %1$s for query %2$s made by %3$s' ), $str, $this->last_query, $caller );
            /* translators: 1: Database error message, 2: SQL query. */
            else  $error_str = sprintf( $this->__( 'TailoredPress database error %1$s for query %2$s' ), $str, $this->last_query );

            /** @noinspection ForgottenDebugOutputInspection *///todo
            error_log( $error_str );
            if ( ! $this->show_errors ) return false;
            if ( $this->_is_multisite() ) {
                $msg = sprintf("%s [%s]\n%s\n",$this->__( 'TailoredPress database error:' ), $str, $this->last_query);
                if ( defined( 'ERROR_LOG_FILE' ) ) {
                    /** @noinspection ForgottenDebugOutputInspection */
                    error_log( $msg, 3, ERROR_LOG_FILE );
                }
                if ( defined( 'DIE_ON_DB_ERROR' ) ) $this->_tp_die( $msg );
            }else{
                $str   = htmlspecialchars( $str, ENT_QUOTES );
                $query = htmlspecialchars( $this->last_query, ENT_QUOTES );
                $error_class = 'tp-db-error';
                printf("<div id='error'><p class='{$error_class}'><strong>%s</strong>[%s]></p><code>%s</code></div>",
                    $this->__('TailoredPress database error:'), $str,$query);
            }
            return true; //todo needed or not?
        }//1552
        /**
         * @description Enables showing of database errors.
         * @param bool $show
         * @return mixed
         */
        public function show_errors( $show = true ){
            $errors            = $this->show_errors;
            $this->show_errors = $show;
            return $errors;
        }//1630
        /**
         * @description Disables showing of database errors.
         * @return mixed
         */
        public function hide_errors(){
            $show              = $this->show_errors;
            $this->show_errors = false;
            return $show;
        }//1647
        /**
         * @description Enables or disables suppressing of database errors.
         * @param bool $suppress
         * @return mixed
         */
        public function suppress_errors( $suppress = true ){
            $errors                = $this->suppress_errors;
            $this->suppress_errors = (bool) $suppress;
            return $errors;
        }//1665
        /**
         * @description Kills cached query results.
         * @throws \exception
         */
        public function flush(): void{
            $this->last_result   = [];
            $this->_col_info      = null;
            $this->last_query    = null;
            $this->rows_affected = 0;
            $this->num_rows      = 0;
            $this->last_error    = '';
            if ( $this->__use_mysqli && $this->_result instanceof \mysqli_result ) {
                mysqli_free_result( $this->_result );
                $this->_result = null;
                if ( empty( $this->_dbh ) || ! ( $this->_dbh instanceof \mysqli)) return;
                while ( mysqli_more_results( $this->_dbh ) ) mysqli_next_result( $this->_dbh );
            }//else throw new \exception('mysql is deprecated, use mysqli instead');
        }//1676
        /**
         * @description  Connects to mysqli by default.
         * @param bool $allow_bail
         * @return bool
         * @throws \exception
         */
        public function db_connect( $allow_bail = true): bool{
            //mysqli has been set to true by default
            $client_flags = defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0;
            if ( $this->__use_mysqli ) {
                mysqli_report( MYSQLI_REPORT_OFF );
                $this->_dbh = mysqli_init();
                $host    = $this->_db_host;
                $port    = null;
                $socket  = null;
                $is_ipv6 = false;
                $host_data = $this->parse_db_host( $this->_db_host );
                if ( $host_data ) @list( $host, $port, $socket, $is_ipv6 ) = $host_data;
                if ( $is_ipv6 && extension_loaded( 'mysqlnd')) $host = "[$host]";
                if ( TP_DEBUG ) mysqli_real_connect( $this->_dbh, $host, $this->_db_user, $this->_db_password, null, $port, $socket, $client_flags );
                else @mysqli_real_connect( $this->_dbh, $host, $this->_db_user, $this->_db_password, null, $port, $socket, $client_flags );
                if ( $this->_dbh->connect_errno ){
                    $this->_dbh = null;
                    throw new \exception('mysqli is misconfigured and failed to connect.');
                }
            }else throw new \exception('mysql is deprecated, use mysqli instead');
            if ( ! $this->_dbh && $allow_bail ) {
                $db_error_msg = 'Error establishing a database connection';
                if(class_exists('TP_Content\Themes\TP_Library\src\TP_ErrorManager\Modules\DB_Errors')) new TP_Error($db_error_msg);//todo
                $this->bail( $db_error_msg, 'db_connect_fail' );
                return false;
            }
            if ( $this->_dbh ) {
                if ( ! $this->__has_connected ) $this->init_charset();
                $this->__has_connected = true;
                $this->set_charset( $this->_dbh );
                $this->ready = true;
                $this->set_sql_mode();
                $this->select( $this->_db_name, $this->_dbh );
                return true;
            }
            return false;
        }//1713
        /**
         * @description Parses the DB_HOST setting to interpret it for mysqli_real_connect().
         * @param $host
         * @return array|bool
         */
        public function parse_db_host( $host ){
            $port    = null;
            $socket  = null;
            $is_ipv6 = false;
            $socket_pos = strpos( $host, ':/' );
            if ( false !== $socket_pos ) {
                $socket = substr( $host, $socket_pos + 1 );
                $host   = substr( $host, 0, $socket_pos );
            }
            if ( substr_count( $host, ':' ) > 1 ) {
                $pattern = '#^(?:\[)?(?P<host>[0-9a-fA-F:]+)(?:\]:(?P<port>[\d]+))?#';
                $is_ipv6 = true;
            } else $pattern = '#^(?P<host>[^:/]*)(?::(?P<port>[\d]+))?#'; // We seem to be dealing with an IPv4 address.
            $matches = array();
            $result  = preg_match( $pattern, $host, $matches );
            if ( 1 !== $result ) return false;
            $host = '';
            foreach ( array( 'host', 'port' ) as $component ) {
                if ( ! empty( $matches[ $component ])) $$component = $matches[ $component ];
            }
            return array( $host, $port, $socket, $is_ipv6 );
        }//1858
        /**
         * @description Checks that the connection to the database is still up. If not, try to reconnect.
         * @param bool $allow_bail
         * @return bool
         * @throws \exception
         */
        public function check_connection( $allow_bail = true ): bool{
            if ( $this->__use_mysqli ) {
                if ( ! empty( $this->_dbh ) && mysqli_ping( $this->_dbh ) ) return true;
            } else throw new \exception('mysql is deprecated, use mysqli instead');
            $error_reporting = false;
            if ( TP_DEBUG ) {
                $error_reporting = error_reporting();
                error_reporting( $error_reporting & ~E_WARNING );
            }
            for ( $tries = 1; $tries <= $this->_reconnect_retries; $tries++ ) {
                if ( $this->_reconnect_retries === $tries && TP_DEBUG ) error_reporting( $error_reporting );
                if ( $this->db_connect( false ) ) {
                    if ( $error_reporting ) error_reporting( $error_reporting );
                    return true;
                }
                sleep( 1 );
            }
            if ( $this->_did_action( 'template_redirect' ) ) return false;
            if ( ! $allow_bail ) return false;
            $this->_tp_load_translations_early();
            $message = "todo comes later";
            $this->bail( $message, 'db_connect_fail' );
            $this->_dead_db();
            return true; //todo ?
        }//1911
        /**
         * @description Performs a database query, using current database connection.
         * @param $query
         * @return int
         * @throws \exception
         */
        public function query( $query ): int{
            if ( ! $this->ready ) {
                $this->_check_current_query = true;
                return false;
            }
            $query = $this->_apply_filters( 'query', $query );
            if ( ! $query ) {
                $this->insert_id = 0;
                return false;
            }
            $this->flush();
            $this->func_call = "\$db->query(\"$query\")";
            if ( $this->_check_current_query && ! $this->_check_ascii( $query ) ) {
                $stripped_query = $this->_strip_invalid_text_from_query( $query );
                $this->flush();
                if ( $stripped_query !== $query ) {
                    $this->insert_id  = 0;
                    $this->last_query = $query;
                    $this->_tp_load_translations_early();
                    $this->last_error = $this->__( 'TailoredPress database error: Could not perform query because it contains invalid data.' );
                    return false;
                }
            }
            $this->_check_current_query = true;
            $this->last_query = $query;
            $this->__do_query( $query );
            $mysql_err_no = 0;
            if ( ! empty( $this->_dbh ) ) {
                if ( $this->__use_mysqli ) {
                    if ( $this->_dbh instanceof \mysqli ) $mysql_err_no = mysqli_errno( $this->_dbh );
                    else $mysql_err_no = 2006;
                } else throw new \exception('mysql is deprecated, use mysqli instead');
            }
            if ( empty( $this->_dbh ) || 2006 === $mysql_err_no ) {
                if ( $this->check_connection() ) {
                    $this->__do_query( $query );
                } else {
                    $this->insert_id = 0;
                    return false;
                }
            }
            if ( $this->__use_mysqli ) {
                if ( $this->_dbh instanceof \mysqli ) $this->last_error = mysqli_error( $this->_dbh );
                else $this->last_error = $this->__( 'Unable to retrieve the error message from MySQL' );

            }else throw new \exception('mysql is deprecated, use mysqli instead');
            if ( $this->last_error ) {
                if ( $this->insert_id && preg_match( '/^\s*(insert|replace)\s/i', $query ) ) $this->insert_id = 0;
                $this->print_error();
                return false;
            }
            if ( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ) $return_val = $this->_result;
            elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
                if ( $this->__use_mysqli ) $this->rows_affected = mysqli_affected_rows( $this->_dbh );
                if ( ( $this->__use_mysqli ) && preg_match( '/^\s*(insert|replace)\s/i', $query )) {
                    $this->insert_id = mysqli_insert_id( $this->_dbh );
                }
                $return_val = $this->rows_affected;
            }else {
                $num_rows = 0;
                if ( $this->__use_mysqli && $this->_result instanceof \mysqli_result ) {
                    while ( $row = mysqli_fetch_object( $this->_result ) ) {
                        $this->last_result[ $num_rows ] = $row;
                        $num_rows++;
                    }
                }
                // Log and return the number of rows selected.
                $this->num_rows = $num_rows;
                $return_val     = $num_rows;
            }
            return $return_val;
        }//2000
        /**
         * @description Logs query data.
         * @param $query
         * @param $query_time
         * @param $query_call_stack
         * @param $query_start
         * @param $query_data
         */
        public function log_query( $query, $query_time, $query_call_stack, $query_start, $query_data ): void{
            $query_data = $this->_apply_filters( 'log_query_custom_data', $query_data, $query, $query_time, $query_call_stack, $query_start );
            $this->queries[] = [$query,$query_time,$query_call_stack,$query_start,$query_data,];
        }//2190
        /**
         * @description Generates and returns a placeholder escape string for use in queries returned by ::prepare().
         * @return string
         */
        public function placeholder_escape(): string{
            static $placeholder;
            if ( ! $placeholder ) {
                $algorithm = function_exists( 'hash' ) ? 'sha256' : 'sha1';
                $salt = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : (string) mt_rand();
                $placeholder = '{' . hash_hmac( $algorithm, uniqid( $salt, true ), $salt ) . '}';
            }
            if ( false === $this->_has_filter( 'query', array( $this, 'remove_placeholder_escape' ) ) )
                $this->_add_filter( 'query', array( $this, 'remove_placeholder_escape' ), 0 );
            return $placeholder;
        }//2223
        /**
         * @description Removes the placeholder escape strings from a query.
         * @param $query
         * @return mixed
         */
        public function add_placeholder_escape( $query ){
            return str_replace( $this->placeholder_escape(), '%', $query );
        }//2254
        /**
         * @description
         * @param $query
         */
        private function __do_query( $query ): void{
            if ( defined( 'SAVE_QUERIES' ) && SAVE_QUERIES ) $this->timer_start();
            if ( ! empty( $this->_dbh ) && $this->__use_mysqli )
                $this->_result = mysqli_query( $this->_dbh, $query );
            else
                $this->_result = null;
            $this->num_queries++;
            if ( defined( 'SAVE_QUERIES' ) && SAVE_QUERIES ) {
                $this->log_query(
                    $query,
                    $this->timer_stop(),
                    $this->get_caller(),
                    $this->time_start,
                    []
                );
            }
        }//2156
        /**
         * @param $query
         * @return mixed
         */
        public function remove_placeholder_escape( $query ){
            return str_replace( $this->placeholder_escape(), '%', $query );
        }//2270
        /**
         * @description Inserts a row into the table.
         * @param $table
         * @param $data
         * @param null $format
         * @return bool
         */
        public function insert( $table, $data, $format = null ): bool{
            return $this->__insert_replace_helper( $table, $data, $format, 'INSERT' );
        }//2300
        /**
         * @description Replaces a row in the table.
         * @param $table
         * @param $data
         * @param null $format
         * @return bool
         */
        public function replace( $table, $data, $format = null ): bool{
            return $this->__insert_replace_helper( $table, $data, $format, 'REPLACE' );
        }//2330
        /**
         * @description Helper function for insert and replace.
         * @param $table
         * @param $data
         * @param null $format
         * @param string $type
         * @return bool
         */
        private function __insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ): bool{
            $this->insert_id = 0;
            if ( ! in_array( strtoupper( $type ), ['REPLACE', 'INSERT'], true ) )
                return false;
            $data = $this->_process_fields( $table, $data, $format );
            if ( false === $data ) return false;
            $formats = [];
            $values  = [];
            foreach ( $data as $value ) {
                if ( is_null( $value['value'] ) ) {
                    $formats[] = 'NULL';
                    continue;
                }
                $formats[] = $value['format'];
                $values[]  = $value['value'];
            }
            $fields  = '`' . implode( '`, `', array_keys( $data ) ) . '`';
            $formats = implode( ', ', $formats );
            $sql = "$type INTO `$table` ($fields) VALUES ($formats)";
            $this->_check_current_query = false;
            return $this->query( $this->prepare( $sql, $values ) );

        }//2359
        public function insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ): bool{
            return $this->__insert_replace_helper( $table, $data, $format, $type);
        }
        /**
         * @description Updates a row in the table.
         * @param $table
         * @param $data
         * @param $where
         * @param null $format
         * @param null $where_format
         * @return bool
         */
        public function update( $table, $data, $where, $format = null, $where_format = null ): bool{
            if ( ! is_array( $data ) || ! is_array( $where ) ) return false;
            $data = $this->_process_fields( $table, $data, $format );
            if ( false === $data ) return false;
            $where = $this->_process_fields( $table, $where, $where_format );
            if ( false === $where )  return false;
            $fields     = [];
            $conditions = [];
            $values     = [];
            foreach ( $data as $field => $value ) {
                if ( is_null( $value['value'] ) ) {
                    $fields[] = "`$field` = NULL";
                    continue;
                }
                $fields[] = "`$field` = " . $value['format'];
                $values[] = $value['value'];
            }
            foreach ( $where as $field => $value ) {
                if ( is_null( $value['value'] ) ) {
                    $conditions[] = "`$field` IS NULL";
                    continue;
                }
                $conditions[] = "`$field` = " . $value['format'];
                $values[]     = $value['value'];
            }
            $fields     = implode( ', ', $fields );
            $conditions = implode( ' AND ', $conditions );
            $sql = TP_UPDATE . " `$table` SET $fields WHERE $conditions";
            $this->_check_current_query = false;
            return $this->query( $this->prepare( $sql, $values ) );
        }//2427
        /**
         * @description Deletes a row in the table.
         * @param $table
         * @param $where
         * @param null $where_format
         * @return bool
         */
        public function delete( $table, $where, $where_format = null ): bool{
            if ( ! is_array( $where ) )  return false;
            $where = $this->_process_fields( $table, $where, $where_format );
            if ( false === $where )  return false;
            $conditions = [];
            $values     = [];
            foreach ( $where as $field => $value ) {
                if ( is_null( $value['value'] ) ) {
                    $conditions[] = "`$field` IS NULL";
                    continue;
                }
                $conditions[] = "`$field` = " . $value['format'];
                $values[]     = $value['value'];
            }
            $conditions = implode( ' AND ', $conditions );
            $sql = TP_DELETE . " FROM `$table` WHERE $conditions";
            $this->_check_current_query = false;
            return $this->query( $this->prepare( $sql, $values ) );
        }//2499
        /**
         * @description Processes arrays of field/value pairs and field formats.
         * @param $table
         * @param $data
         * @param $format
         * @return bool|mixed
         */
        protected function _process_fields( $table, $data, $format ){
            $data = $this->_process_field_formats( $data, $format );
            if ( false === $data ) return false;
            $data = $this->_process_field_charsets( $data, $format );
            if ( false === $data ) return false;
            $data = $this->_process_field_lengths( $data, $table );
            if ( false === $data ) return false;
            $converted_data = $this->_strip_invalid_text( $data );
            if ( $data !== $converted_data ) {
                $problem_fields = [];
                foreach ( (array)$data as $field => $value ) {
                    if ( $value !== $converted_data[ $field ] )  $problem_fields[] = $field;
                }
                $this->_tp_load_translations_early();
                if ( 1 === count( $problem_fields ) ) {
                    $this->last_error = sprintf(
                        $this->__('TailoredPress database error: Processing the value for the following field failed: %s. The supplied value may be too long or contains invalid data.'),
                        reset( $problem_fields )
                    );
                }else{
                    $this->last_error = sprintf(
                        $this->__('TailoredPress database error: Processing the values for the following fields failed: %s. The supplied values may be too long or contain invalid data.'),
                        implode( ', ', $problem_fields )
                    );
                }
                return false;
            }
            return $data;
        }//2546
        /**
         * @description Prepares arrays of value/format pairs as passed to wpdb CRUD methods.
         * @param $data
         * @param $format
         * @return mixed
         */
        protected function _process_field_formats( $data, $format ){
            $formats          = (array) $format;
            $original_formats = $formats;
            foreach ( $data as $field => $value ) {
                $value = ['value'  => $value,'format' => '%s',];
                if ( ! empty( $format ) ) {
                    $value['format'] = array_shift( $formats );
                    if ( ! $value['format'] ) $value['format'] = reset( $original_formats );
                } elseif ( isset( $this->field_types[ $field ] ) )
                    $value['format'] = $this->field_types[ $field ];
                $data[ $field ] = $value;
            }
            return $data;
        }//2605
        /**
         * @note might not be needed?
         * @description Adds field charsets to field/value/format arrays generated by wpdb::process_field_formats().
         * @param $data
         * @param $table
         * @return bool
         */
        protected function _process_field_charsets( $data, $table ): bool{
            foreach ( $data as $field => $value ) {
                if ( '%d' === $value['format'] || '%f' === $value['format'] )
                    $value['charset'] = false;
                else {
                    $value['charset'] = $this->get_col_charset( $table, $field );
                    if ( $this->_init_error( $value['charset'])) return false;
                }
                $data[ $field ] = $value;
            }
            return $data;
        }//2640
        /**
         * @description For string fields, records the maximum string length that field can safely save.
         * @param $data
         * @param $table
         * @return bool
         */
        protected function _process_field_lengths( $data, $table ): bool{
            foreach ( $data as $field => $value ) {
                if ( '%d' === $value['format'] || '%f' === $value['format'] ) $value['length'] = false;
                else{
                    $value['length'] = $this->get_col_length( $table, $field );
                    if ( $this->_init_error( $value['length'])) return false;
                }
                $data[ $field ] = $value;
            }
            return $data;
        }//2671
        /**
         * @description Retrieves one variable from the database.
         * @param null $query
         * @param int $x
         * @param int $y
         * @return null
         */
        public function get_var( $query = null, $x = 0, $y = 0 ){
            $this->func_call = "\$db->get_var(\"$query\", $x, $y)";
            if ( $query ) {
                if ( $this->_check_current_query && $this->_check_safe_collation( $query ) )
                    $this->_check_current_query = false;
                $this->query( $query );
            }
            if ( ! empty( $this->last_result[ $y ] ) )
                $values = array_values( get_object_vars( $this->last_result[ $y ] ) );
            return ( isset( $values[ $x ] ) && '' !== $values[ $x ] ) ? $values[ $x ] : null;
        }//2707
        /**
         * @description Retrieves one row from the database.
         * @param null $query
         * @param string $output
         * @param int $y
         * @return array|null
         */
        public function get_row( $query = null, $output = OBJECT, $y = 0 ): ?array{
            $this->func_call = "\$db->get_row(\"$query\",$output,$y)";
            $return = null;
            if ( $query ) {
                if ( $this->_check_current_query && $this->_check_safe_collation( $query ) )
                    $this->_check_current_query = false;
                $this->query( $query );
            } else return null;
            if ( ! isset( $this->last_result[ $y ])) return null;
            if ( OBJECT === $output )
                $return = $this->last_result[ $y ] ?: null;
            elseif ( ARRAY_A === $output )
                $return = $this->last_result[ $y ] ? get_object_vars( $this->last_result[ $y ] ) : null;
            elseif ( ARRAY_N === $output ) {
                $return = $this->last_result[ $y ] ? array_values( get_object_vars( $this->last_result[ $y ] ) ) : null;
            } else $this->print_error( ' $db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N' );
            return $return;
        }//2741
        /**
         * @description Retrieves one column from the database.
         * @param null $query
         * @param int $x
         * @return array
         */
        public function get_col( $query = null, $x = 0 ): array{
            if ( $query ) {
                if ( $this->_check_current_query && $this->_check_safe_collation( $query ) )
                    $this->_check_current_query = false;
                $this->query( $query );
            }
            $new_array = array();
            if ( $this->last_result ) {
                for ( $i = 0, $j = count( $this->last_result ); $i < $j; $i++ )
                    $new_array[ $i ] = $this->get_var( null, $x, $i );
            }
            return $new_array;
        }//2785
        /**
         * @description Retrieves an entire SQL result set from the database (i.e., many rows).
         * @param null $query
         * @param string $output
         * @return array|null
         */
        public function get_results( $query = null, $output = OBJECT ): ?array{
            $this->func_call = "\$db->get_results(\"$query\", $output)";
            if ( $query ) {
                if ( $this->_check_current_query && $this->_check_safe_collation($query ))
                    $this->_check_current_query = false;
                $this->query( $query );
            } else return null;
            $new_array = [];
            if ( OBJECT === $output ) return $this->last_result;
            elseif ( OBJECT_K === $output ) {
                if ( $this->last_result ) {
                    foreach ( $this->last_result as $row ) {
                        $var_by_ref = get_object_vars( $row );
                        $key        = array_shift( $var_by_ref );
                        if ( ! isset( $new_array[$key])) $new_array[ $key ] = $row;
                    }
                }
                return $new_array;
            } elseif ( ARRAY_A === $output || ARRAY_N === $output ) {
                if ( $this->last_result ) {
                    foreach ( (array) $this->last_result as $row ) {
                        if ( ARRAY_N === $output ) $new_array[] = array_values( get_object_vars( $row ) );
                        else $new_array[] = get_object_vars( $row );
                    }
                }
                return $new_array;
            } elseif ( strtoupper( $output ) === OBJECT ) return $this->last_result;
            return null;
        }//2821
        /**
         * @description Retrieves the character set for the given table.
         * @param $table
         * @return bool|mixed|string|TP_Error
         */
        protected function _get_table_charset( $table ){
            $table_key = strtolower( $table );
            $charset = $this->_apply_filters( 'pre_get_table_charset', null, $table );
            if ( null !== $charset ) return $charset;
            if ( isset( $this->_table_charset[ $table_key ] ) ) return $this->_table_charset[ $table_key ];
            $charsets = [];
            $columns  = [];
            $table_parts = explode( '.', $table );
            $table       = '`' . implode( '`.`', $table_parts ) . '`';
            $results     = $this->get_results( "SHOW FULL COLUMNS FROM $table" );
            if ( ! $results ) return new TP_Error('tpdb_get_table_charset_failure', $this->__('Could not retrieve table charset.'));
            foreach ( $results as $column ) $columns[ strtolower( $column->Field ) ] = $column;
            $this->_col_meta[ $table_key ] = $columns;
            foreach ( $columns as $column ) {
                if ( ! empty( $column->Collation ) ) {
                    @list( $charset ) = explode( '_', $column->Collation );
                    if ( 'utf8mb4' === $charset && ! $this->has_cap( 'utf8mb4' ) ) $charset = 'utf8';
                    $charsets[ strtolower( $charset ) ] = true;
                }
                @list( $type ) = explode( '(', $column->Type );
                if ( in_array( strtoupper( $type ), array( 'BINARY', 'VARBINARY', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB' ), true ) ) {
                    $this->_table_charset[ $table_key ] = 'binary';
                    return 'binary';
                }
            }
            if ( isset( $charsets['utf8mb3'] ) ) {
                $charsets['utf8'] = true;
                unset( $charsets['utf8mb3'] );
            }
            $count = count( $charsets );
            if ( 1 === $count )$charset = key( $charsets );
            elseif ( 0 === $count ) $charset = false;
            else{
                unset( $charsets['latin1'] );
                $count = count( $charsets );
                if ( 1 === $count ) $charset = key( $charsets );
                elseif ( 2 === $count && isset( $charsets['utf8'],$charsets['utf8mb4'])) $charset = 'utf8';
                else $charset = 'ascii';
            }
            $this->_table_charset[ $table_key ] = $charset;
            return $charset;
        }//2880
        /**
         * @description Checks if a string is ASCII.
         * @param $string
         * @return bool
         */
        protected function _check_ascii( $string ): bool{
            if ( function_exists( 'mb_check_encoding' ) ) {
                if(mb_check_encoding($string, 'ASCII')) return true;
            } elseif ( ! preg_match( '/[^\x00-\x7F]/', $string ) )
                return true;
            return false;
        }//3139
        /**
         * @description Checks if the query is accessing a collation considered safe on the current version of MySQL.
         * @param $query
         * @return bool
         */
        protected function _check_safe_collation( $query ): bool{
            if ( $this->_checking_collation ) return true;
            $query = ltrim( $query, "\r\n\t (" );
            if ( preg_match( '/^(?:SHOW|DESCRIBE|DESC|EXPLAIN|CREATE)\s/i', $query ) )
                return true;
            if ( $this->_check_ascii( $query ) ) return true;
            $table = $this->_get_table_from_query( $query );
            if ( ! $table ) return false;
            $this->_checking_collation = true;
            $collation                = $this->_get_table_charset( $table );
            $this->_checking_collation = false;
            if ( false === $collation || 'latin1' === $collation )
                return true;
            $table = strtolower( $table );
            if ( empty( $this->_col_meta[ $table ] ) ) return false;
            foreach ( $this->_col_meta[ $table ] as $col ) {
                if ( empty( $col->Collation ) ) continue;
                if ( ! in_array( $col->Collation, array( 'utf8_general_ci', 'utf8_bin', 'utf8mb4_general_ci', 'utf8mb4_bin' ), true ) )
                    return false;
            }
            return true;
        }//3159
        /**
         * might not be needed for msqli,
         * @description Retrieves the character set for the given column.
         * @param $table
         * @param $column
         * @return bool|mixed|string|TP_Error
         */
        public function get_col_charset( $table, $column ){
            $table_key  = strtolower( $table );
            $column_key = strtolower( $column );
            $charset = $this->_apply_filters( 'pre_get_col_charset', null, $table, $column );
            if ( null !== $charset ) return $charset;
            if ( empty( $this->is_mysql ) ) return false;
            if ( empty( $this->_table_charset[ $table_key ] ) ) {
                $table_charset = $this->_get_table_charset( $table );
                if ( $this->_init_error( $table_charset ) ) return $table_charset;
            }
            if ( empty( $this->_col_meta[ $table_key ] ) ) return $this->_table_charset[ $table_key ];
            if ( empty( $this->_col_meta[ $table_key ][ $column_key ])) return $this->_table_charset[ $table_key ];
            if ( empty( $this->_col_meta[ $table_key ][ $column_key ]->Collation ) ) return false;
            @list( $charset ) = explode( '_', $this->_col_meta[ $table_key ][ $column_key ]->Collation );
            return $charset;
        }//2984
        /**
         * might not be needed for msqli,
         * @description Retrieves the maximum string length allowed in a given column.
         * @param $table
         * @param $column
         * @return array|bool
         */
        public function get_col_length( $table, $column ){
            $table_key  = strtolower( $table );
            $column_key = strtolower( $column );
            if ( empty( $this->is_mysql ) ) return false;
            if ( empty( $this->_col_meta[ $table_key ] ) ) {
                $table_charset = $this->_get_table_charset( $table );
                if ( $this->_init_error( $table_charset ) ) return $table_charset;
            }
            if ( empty( $this->_col_meta[$table_key][$column_key ])) return false;
            $type_info = explode( '(', $this->_col_meta[ $table_key ][ $column_key ]->Type );
            $type = strtolower( $type_info[0] );
            if ( ! empty( $type_info[1] ) ) $length = trim( $type_info[1], ')' );
            else $length = false;
            switch ( $type ) {
                case 'char':
                case 'varchar':
                    return ['type' => 'char', 'length' => (int) $length,];
                case 'binary':
                case 'varbinary':
                    return ['type' => 'byte','length' => (int) $length,];
                case 'tinyblob':
                case 'tinytext':
                    return ['type' => 'byte','length' => 255,  ];// 2^8 - 1
                case 'blob':
                case 'text':
                    return ['type' => 'byte','length' => 65535,];// 2^16 - 1
                case 'mediumblob':
                case 'mediumtext':
                    return ['type' => 'byte','length' => 16777215,];// 2^24 - 1
                case 'longblob':
                case 'longtext':
                    return ['type' => 'byte','length' => 4294967295, ];// 2^32 - 1
                default:
                    return false;
            }
        }//3050
        /**
         * @description Strips any invalid characters from the string for a given table and column.
         * @param $table
         * @param $column
         * @param $value
         * @return array|bool|mixed|string|TP_Error
         */
        public function strip_invalid_text_for_column( $table, $column, $value ){
            if ( ! is_string( $value ) ) return $value;
            $charset = $this->get_col_charset( $table, $column );
            if ( ! $charset ) return $value;
            elseif ( $this->_init_error( $charset ) )
                return $charset;
            $data = [
                $column => [
                    'value'   => $value,
                    'charset' => $charset,
                    'length'  => $this->get_col_length( $table, $column ),
                ],
            ];
            $data = $this->_strip_invalid_text( $data );
            if ( $this->_init_error( $data ) ) return $data;
            return $data[ $column ]['value'];
        }//3431
        /**
         * @description Closes the current database connection.
         * @return bool|null
         */
        public function close_db_connection(): ?bool{
            if ( ! $this->_dbh ) return false;
            if ( $this->__use_mysqli )
                $closed = mysqli_close( $this->_dbh );
            else return null;
            if ( $closed ) {
                $this->_dbh           = null;
                $this->ready         = false;
                $this->__has_connected = false;
            }
            return $closed;
        }//3656
        /**
         * @description Strips any invalid characters based on value/charset pairs.
         * @param $data
         * @return TP_Error
         */
        protected function _strip_invalid_text( $data ): TP_Error{
            $db_check_string = false;
            foreach ( $data as &$value ) {
                $charset = $value['charset'];
                if ( is_array( $value['length'] ) ) {
                    $length                  = $value['length']['length'];
                    $truncate_by_byte_length = 'byte' === $value['length']['type'];
                } else {
                    $length = false;
                    $truncate_by_byte_length = false;
                }
                if ( false === $charset ) continue;
                if ( ! is_string( $value['value'])) continue;
                $needs_validation = true;
                if ('latin1' === $charset || ( ! isset( $value['ascii'] ) && $this->_check_ascii( $value['value']))) {
                    $truncate_by_byte_length = true;
                    $needs_validation        = false;
                }
                if ( $truncate_by_byte_length ) {
                    $this->_mb_string_binary_safe_encoding();
                    if ( false !== $length && strlen( $value['value'] ) > $length )
                        $value['value'] = substr( $value['value'], 0, $length );
                    $this->_reset_mb_string_encoding();
                    if (!$needs_validation ) continue;
                }
                if ( ( 'utf8' === $charset || 'utf8mb3' === $charset || 'utf8mb4' === $charset ) && function_exists( 'mb_strlen' ) ) {
                    /** @noinspection NotOptimalRegularExpressionsInspection *///todo
                    $regex = '/((?: [\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|[\xEE-\xEF][\x80-\xBF]{2}';
                    if ( 'utf8mb4' === $charset )
                        $regex .= '|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}';
                    $regex .= '){1,40})|./x';
                    $value['value'] = preg_replace( $regex, '$1', $value['value'] );
                    if ( false !== $length && mb_strlen( $value['value'], 'UTF-8' ) > $length )
                        $value['value'] = mb_substr( $value['value'], 0, $length, 'UTF-8' );
                    continue;
                }
                $value['db']     = true;
                $db_check_string = true;
            }
            unset( $value );
            if ( $db_check_string ) {
                $queries = array();
                foreach ( $data as $col => $value ) {
                    if ( ! empty( $value['db'] ) ) {
                        if ( isset( $value['length']['type'] ) && 'byte' === $value['length']['type'] )
                            $charset = 'binary';
                        else $charset = $value['charset'];
                        if ( $this->charset ) {
                            $connection_charset = $this->charset;
                        } elseif ( $this->__use_mysqli ) {
                            $connection_charset = mysqli_character_set_name( $this->_dbh );
                        }else $connection_charset = null;
                        if ( is_array( $value['length'] ) ) {
                            $length          = sprintf( '%.0f', $value['length']['length'] );
                            $queries[ $col ] = $this->prepare( "CONVERT( LEFT( CONVERT( %s USING $charset ), $length ) USING $connection_charset )", $value['value'] );
                        } elseif ( 'binary' !== $charset )
                            $queries[ $col ] = $this->prepare( "CONVERT( CONVERT( %s USING $charset ) USING $connection_charset )", $value['value'] );
                        unset( $data[ $col ]['db'] );
                    }
                }
                $sql = array();
                foreach ( $queries as $column => $query ) {
                    if ( ! $query ) continue;
                    $sql[] = $query . " AS x_$column";
                }
                $this->_check_current_query = false;
                $row  = $this->get_row( 'SELECT ' . implode( ', ', $sql ), ARRAY_A );
                if ( ! $row )
                    return new TP_Error( 'tpdb_strip_invalid_text_failure', $this->__( 'Could not strip invalid text.' ) );
                foreach ( array_keys( $data ) as $column ) {
                    if ( isset( $row[ "x_$column" ] ) ) $data[ $column ]['value'] = $row[ "x_$column" ];
                }
            }
            return $data;
        }//3220
        /**
         * @description Strips any invalid characters from the query.
         * @param $query
         * @return array|bool|mixed|string|TP_Error
         */
        protected function _strip_invalid_text_from_query( $query ){
            $trimmed_query = ltrim( $query, "\r\n\t (" );
            if ( preg_match( '/^(?:SHOW|DESCRIBE|DESC|EXPLAIN|CREATE)\s/i', $trimmed_query ) )
                return $query;
            $table = $this->_get_table_from_query( $query );
            if ( $table ) {
                $charset = $this->_get_table_charset( $table );
                if ( $this->_init_error( $charset ) ) return $charset;
                if ( 'binary' === $charset )  return $query;
            }
            else $charset = $this->charset;
            $data = ['value' => $query,'charset' => $charset, 'ascii'=> false,'length'=> false,];
            $data = $this->_strip_invalid_text( array( $data ) );
            if ($this->_init_error( $data ) ) return $data;
            return $data[0]['value'];
        }//3374
        /**
         * @description Strips any invalid characters from the string for a given table and column.
         * @param $table
         * @param $column
         * @param $value
         * @return array|void
         */
        protected function _strip_invalid_text_for_column( $table, $column, $value ){
            if ( ! is_string( $value ) ) return $value;
            $charset = $this->get_col_charset( $table, $column );
            if ( ! $charset ) return $value;
            elseif ( $this->_init_error( $charset ) ) return $charset;
            $data = [
                $column => [
                    'value'   => $value,
                    'charset' => $charset,
                    'length'  => $this->get_col_length( $table, $column ),
                ],
            ];
            $data = $this->_strip_invalid_text( $data );
            if ( $this->_init_error( $data ) ) return (array)$data;
            return $data[ $column ]['value'];
        }//3421
        /**
         * @description Finds the first table name referenced in a query.
         * @param $query
         * @return bool|mixed
         */
        protected function _get_table_from_query( $query ){
            $query = rtrim( $query, ';/-#' );
            $query = ltrim( $query, "\r\n\t (" );
            $query = preg_replace( '/\((?!.\s*select)[^(]*?\)/is', '()', $query );
            if ( preg_match(
                '/^\s*(?:'
                . 'SELECT.*?\s+FROM'
                . '|INSERT(?:\s+LOW_PRIORITY|\s+DELAYED|\s+HIGH_PRIORITY)?(?:\s+IGNORE)?(?:\s+INTO)?'
                . '|REPLACE(?:\s+LOW_PRIORITY|\s+DELAYED)?(?:\s+INTO)?'
                . '|UPDATE(?:\s+LOW_PRIORITY)?(?:\s+IGNORE)?'
                . '|DELETE(?:\s+LOW_PRIORITY|\s+QUICK|\s+IGNORE)*(?:.+?FROM)?'
                . ')\s+((?:[0-9a-zA-Z$_.`-]|[\xC2-\xDF][\x80-\xBF])+)/is',
                $query,
                $maybe
            ) ) {
                return str_replace( '`', '', $maybe[1] );
            }
            if ( preg_match( '/^\s*SHOW\s+(?:TABLE\s+STATUS|(?:FULL\s+)?TABLES).+WHERE\s+Name\s*=\s*("|\')((?:[0-9a-zA-Z$_.-]|[\xC2-\xDF][\x80-\xBF])+)\\1/is', $query, $maybe ) )
                return $maybe[2];
            if ( preg_match( '/^\s*SHOW\s+(?:TABLE\s+STATUS|(?:FULL\s+)?TABLES)\s+(?:WHERE\s+Name\s+)?LIKE\s*("|.\')((?:[\\\\0-9a-zA-Z$_.-]|[\xC2-\xDF][\x80-\xBF])+)%?\\1/is', $query, $maybe ) )
                return str_replace( '\\_', '_', $maybe[2] );
            if ( preg_match(
                '/^\s*(?:'
                . '(?:EXPLAIN\s+(?:EXTENDED\s+)?)?SELECT.*?\s+FROM'
                . '|DESCRIBE|DESC|EXPLAIN|HANDLER'
                . '|(?:LOCK|UNLOCK)\s+TABLE(?:S)?'
                . '|(?:RENAME|OPTIMIZE|BACKUP|RESTORE|CHECK|CHECKSUM|ANALYZE|REPAIR).*\s+TABLE'
                . '|TRUNCATE(?:\s+TABLE)?'
                . '|CREATE(?:\s+TEMPORARY)?\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?'
                . '|ALTER(?:\s+IGNORE)?\s+TABLE'
                . '|DROP\s+TABLE(?:\s+IF\s+EXISTS)?'
                . '|CREATE(?:\s+\w+)?\s+INDEX.*\s+ON'
                . '|DROP\s+INDEX.*\s+ON'
                . '|LOAD\s+DATA.*INFILE.*INTO\s+TABLE'
                . '|(?:GRANT|REVOKE).*ON\s+TABLE'
                . '|SHOW\s+(?:.*FROM|.*TABLE)'
                . ')\s+\(*\s*((?:[0-9a-zA-Z$_.`-]|[\xC2-\xDF][\x80-\xBF])+)\s*\)*/is',
                $query,
                $maybe
            ) ) {
                return str_replace( '`', '', $maybe[1] );
            }
            return false;
        }//3459
        /**
         * @description Loads the column metadata from the last query.
         * @throws \exception
         */
        protected function _load_col_info(): void{
            if ( $this->_col_info ) return;
            if ( $this->__use_mysqli ) {
                $num_fields = mysqli_num_fields( $this->_result );
                for ( $i = 0; $i < $num_fields; $i++ )
                    $this->_col_info[ $i ] = mysqli_fetch_field( $this->_result );
            } else  throw new \exception('mysql is deprecated, use mysqli instead');
        }//3531
        /**
         * @description Retrieves column metadata from the last query.
         * @param string $info_type
         * @param int $col_offset
         * @return array|null
         */
        public function get_col_info( $info_type = 'name', $col_offset = -1 ): ?array{
            $this->_load_col_info();
            $return = null;
            if ( $this->_col_info ) {
                if ( -1 === $col_offset ) {
                    $i         = 0;
                    $new_array = array();
                    foreach ( (array) $this->_col_info as $col ) {
                        $new_array[ $i ] = $col->{$info_type};
                        $i++;
                    }
                    $return = $new_array;
                } else  $return = $this->_col_info[ $col_offset ]->{$info_type};
            }
            return $return;

        }//3561
        /**
         * @description Starts the timer, for debugging purposes.
         * @return bool
         */
        public function timer_start(): bool{
            $this->time_start = microtime( true );
            return true;
        }//3586
        /**
         * @description Stops the debugging timer.
         * @return mixed
         */
        public function timer_stop(){
            return ( microtime( true ) - $this->time_start );
        }//3598
        /**
         * @description Wraps errors in a nice header and footer and dies.
         * @param $message
         * @param string $error_code
         * @return bool
         * @throws \exception
         */
        public function bail( $message, $error_code = '500' ): bool{
            if ( $this->show_errors ) {
                $error = '';
                if ( $this->__use_mysqli ) {
                    if ( $this->_dbh instanceof \mysqli ) {
                        $error = mysqli_error( $this->_dbh );
                    } elseif ( mysqli_connect_errno() ) {
                        $error = mysqli_connect_error();
                    }
                } else throw new \exception('mysql is deprecated, use mysqli instead');
                if ( $error ) $message = "<p><code>$error</code><span>$message</span></p>\n";
                $this->_tp_die( $message );
            } else {
                if ( class_exists( 'TP_Managers\Core_Manager\TP_Error', false))
                    $this->error = new TP_Error( $error_code, $message );
                else $this->error = $message;
                return false;
            }
            return true;
        }//3614
        /**
         * @description Determines whether MySQL database is at least the required minimum version.
         * @return bool|TP_Error
         */
        public function check_database_version(){
            $this->tp_version;
            if ( version_compare( $this->db_version(), $this->required_mysql_version, '<' ) )
                return new TP_Error( 'database_version', sprintf( $this->__( '<strong>Error</strong>: WordPress %1$s requires MySQL %2$s or higher' ), TP_VERSION, TP_REQUIRED_MYSQL_VERSION) );
            return true;
        }//3685
        /**
         * @description Retrieves the database character collate.
         * @return mixed
         */
        public function get_charset_collate(){
            $charset_collate = '';
            if ( ! empty( $this->charset)) $charset_collate = "DEFAULT CHARACTER SET $this->charset";
            if ( ! empty( $this->collate)) $charset_collate .= " COLLATE $this->collate";
            return $charset_collate;
        }//3718
        /**
         * todo,
         * @description Determines if a database supports a particular feature.
         * @param $db_cap
         * @return bool|mixed
         */
        public function has_cap( $db_cap ){
            $version = $this->db_version();
            switch ( strtolower( $db_cap ) ) {
                case 'collation':    // @since begin
                case 'group_concat': // @since begin
                case 'sub_queries':   // @since begin
                    return version_compare( $version, '4.1', '>=' );
                case 'set_charset':
                    return version_compare( $version, '5.0.7', '>=' );
                case 'utf8mb4':
                    if ( version_compare( $version, '5.5.3', '<' )) return false;
                    if ( $this->__use_mysqli ) $client_version = mysqli_get_client_info();
                    else return false;
                    if ( false !== strpos( $client_version, 'mysqlnd' ) ) {
                        $client_version = preg_replace( '/^\D+([\d.]+).*/', '$1', $client_version );
                        return version_compare( $client_version, '5.0.9', '>=' );
                    } else return version_compare( $client_version, '5.5.3', '>=' );
                case 'utf8mb4_520':
                    return version_compare( $version, '5.6', '>=' );
            }
            return false;
        }//3744
        /**
         * @description Retrieves a comma-separated list of the names of the functions that called wpdb.
         * @return mixed
         */
        public function get_caller(){
            return $this->_tp_debug_backtrace_summary( __CLASS__ );
        }//3788
        /**
         * @description Retrieves the database server version.
         * @return mixed
         */
        public function db_version(){
            return preg_replace( '/[^0-9.].*/', '', $this->db_server_info() );
        }//3799
        /**
         * @description Retrieves full database server information.
         * @return string
         */
        public function db_server_info(): string{
			if(!$this->_dbh){return false;}
            return mysqli_get_server_info( $this->_dbh );
        }//3810
    }
}else die;