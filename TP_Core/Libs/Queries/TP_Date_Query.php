<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-4-2022
 * Time: 16:01
 */
namespace TP_Core\Libs\Queries;
if(ABSPATH){
    class TP_Date_Query extends Query_Base{
        private $__parent_query;
        public $column = 'post_date';
        public $compare = '=';
        public $queries = [];
        public $relation = 'AND';
        public $time_keys = array( 'after', 'before', 'year', 'month', 'monthnum', 'week', 'w', 'dayofyear', 'day', 'dayofweek', 'dayofweek_iso', 'hour', 'minute', 'second' );
        /**
         * @description TP_Date_Query constructor.
         * @param $date_query
         * @param string $default_column
         */
        public function __construct( $date_query, $default_column = 'post_date' ){
            if ( empty( $date_query ) || ! is_array( $date_query ) ) return;
            if ( isset( $date_query['relation'] ) && 'OR' === strtoupper( $date_query['relation'] ) )
                $this->relation = 'OR';
            else  $this->relation = 'AND';
            if ( ! isset( $date_query[0] ) ) $date_query = array( $date_query );
            if ( ! empty( $date_query['column'] ) )
                $date_query['column'] = $this->_esc_sql( $date_query['column'] );
            else  $date_query['column'] = $this->_esc_sql( $default_column );
            $this->column = $this->validate_column( $this->column );
            $this->compare = $this->get_compare( $date_query );
            $this->queries = $this->sanitize_query( $date_query );
        }//147
        /**
         * @description Recursive-friendly query sanitizer.
         * @param $queries
         * @param null $parent_query
         * @return array
         */
        public function sanitize_query( $queries, $parent_query = null ): array{
            $cleaned_query = array();
            $defaults = array('column'   => 'post_date','compare'  => '=','relation' => 'AND',);
            foreach ( $queries as $q_key => $q_value )
                if ( is_numeric( $q_key ) && ! is_array( $q_value ) ) unset( $queries[ $q_key ] );
            foreach ( $defaults as $d_key => $d_value ) {
                if ( isset( $queries[ $d_key ] ) ) continue;
                if ( isset( $parent_query[ $d_key ] ) ) $queries[ $d_key ] = $parent_query[ $d_key ];
                else $queries[ $d_key ] = $d_value;
            }
            if ( $this->_is_first_order_clause( $queries ) ) $this->validate_date_values( $queries );
            foreach ( $queries as $key => $q ) {
                if ( ! is_array( $q ) || in_array( $key, $this->time_keys, true ) )
                    $cleaned_query[ $key ] = $q;
                else $cleaned_query[] = $this->sanitize_query( $q, $queries );
            }
            return $cleaned_query;
        }//188
        /**
         * @description Determine whether this is a first-order clause.
         * @param $query
         * @return bool
         */
        protected function _is_first_order_clause( $query ): bool{
            $time_keys = array_intersect( $this->time_keys, array_keys( $query ) );
            return ! empty( $time_keys );
        }//249
        /**
         * @description Determines and validates what comparison operator to use.
         * @param $query
         * @return string
         */
        public function get_compare( $query ): string{
            if ( ! empty( $query['compare'] ) && in_array( $query['compare'], array( '=', '!=', '>', '>=', '<', '<=', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ))
                return strtoupper( $query['compare'] );
            return $this->compare;
        }//259
        /**
         * @description Validates the given date_query values and triggers errors if something is not valid.
         * @param array $date_query
         * @return bool
         */
        public function validate_date_values( $date_query = array()): bool{
            if ( empty( $date_query ) ) return false;
            $valid = true;
            if ( array_key_exists( 'before', $date_query ) && is_array( $date_query['before'] ) )
                $valid = $this->validate_date_values( $date_query['before'] );
            if ( array_key_exists( 'after', $date_query ) && is_array( $date_query['after'] ) )
                $valid = $this->validate_date_values( $date_query['after'] );
            $min_max_checks = array();
            if ( array_key_exists( 'year', $date_query ) ) {
                if ( is_array( $date_query['year'] ) ) $_year = reset( $date_query['year'] );
                else $_year = $date_query['year'];
                $max_days_of_year = gmdate( 'z', mktime( 0, 0, 0, 12, 31, $_year ) ) + 1;
            } else $max_days_of_year = 366;
            $min_max_checks['dayofyear'] = array('min' => 1,'max' => $max_days_of_year,);
            $min_max_checks['dayofweek'] = array('min' => 1,'max' => 7,);
            $min_max_checks['dayofweek_iso'] = array('min' => 1,'max' => 7,);
            $min_max_checks['month'] = array('min' => 1,'max' => 12,);
            if ( isset( $_year ) ) $week_count = gmdate( 'W', mktime( 0, 0, 0, 12, 28, $_year ) );
            else $week_count = 53;
            $min_max_checks['week'] = array('min' => 1,'max' => $week_count,);
            $min_max_checks['day'] = array('min' => 1,'max' => 31,);
            $min_max_checks['hour'] = array('min' => 0,'max' => 23, );
            $min_max_checks['minute'] = array('min' => 0,'max' => 59,);
            $min_max_checks['second'] = array('min' => 0,'max' => 59,);
            foreach ( $min_max_checks as $key => $check ) {
                if ( ! array_key_exists( $key, $date_query ) ) continue;
                foreach ( (array) $date_query[ $key ] as $_value ) {
                    $is_between = $_value >= $check['min'] && $_value <= $check['max'];
                    if ( ! is_numeric( $_value ) || ! $is_between ) {
                        $error = sprintf(
                        /* translators: Date query invalid date message. 1: Invalid value, 2: Type of value, 3: Minimum valid value, 4: Maximum valid value. */
                            $this->__( 'Invalid value %1$s for %2$s. Expected value should be between %3$s and %4$s.' ),
                            '<code>' . $this->_esc_html( $_value ) . '</code>',
                            '<code>' . $this->_esc_html( $key ) . '</code>',
                            '<code>' . $this->_esc_html( $check['min'] ) . '</code>',
                            '<code>' . $this->_esc_html( $check['max'] ) . '</code>'
                        );
                        $this->_doing_it_wrong( __CLASS__, $error, '4.1.0' );
                        $valid = false;
                    }
                }
            }
            if ( ! $valid ) return $valid;
            $day_month_year_error_msg = '';
            $day_exists   = array_key_exists( 'day', $date_query ) && is_numeric( $date_query['day'] );
            $month_exists = array_key_exists( 'month', $date_query ) && is_numeric( $date_query['month'] );
            $year_exists  = array_key_exists( 'year', $date_query ) && is_numeric( $date_query['year'] );
            if ( $day_exists && $month_exists && $year_exists ) {
                // 1. Checking day, month, year combination.
                if ( ! $this->_tp_check_date( $date_query['month'], $date_query['day'], $date_query['year'], sprintf( '%s-%s-%s', $date_query['year'], $date_query['month'], $date_query['day'] ) ) ) {
                    $day_month_year_error_msg = sprintf(
                    /* translators: 1: Year, 2: Month, 3: Day of month. */
                        $this->__( 'The following values do not describe a valid date: year %1$s, month %2$s, day %3$s.' ),
                        '<code>' . $this->_esc_html( $date_query['year'] ) . '</code>',
                        '<code>' . $this->_esc_html( $date_query['month'] ) . '</code>',
                        '<code>' . $this->_esc_html( $date_query['day'] ) . '</code>'
                    );
                    $valid = false;
                }
            } elseif ( $day_exists && $month_exists ) {
                if ( ! $this->_tp_check_date( $date_query['month'], $date_query['day'], 2012, sprintf( '2012-%s-%s', $date_query['month'], $date_query['day'] ) ) ) {
                    $day_month_year_error_msg = sprintf(
                        $this->__( 'The following values do not describe a valid date: month %1$s, day %2$s.' ),
                        '<code>' . $this->_esc_html( $date_query['month'] ) . '</code>',
                        '<code>' . $this->_esc_html( $date_query['day'] ) . '</code>'
                    );
                    $valid = false;
                }
            }
            if ( ! empty( $day_month_year_error_msg ) ) $this->_doing_it_wrong( __CLASS__, $day_month_year_error_msg, '0.0.1' );
            return $valid;
        }//281
        /**
         * @description Validates a column name parameter.
         * @param $column
         * @return mixed
         */
        public function validate_column( $column ){
            $tpdb = $this->_init_db();
            $valid_columns = array(
                'post_date','post_date_gmt','post_modified','post_modified_gmt','comment_date',
                'comment_date_gmt','user_registered','registered','last_updated',
            );
            if ( false === strpos( $column, '.' ) ) {
                if ( ! in_array( $column, $this->_apply_filters( 'date_query_valid_columns', $valid_columns ), true ) )
                    $column = 'post_date';
                $known_columns = array(
                    $tpdb->posts    => array(
                        'post_date','post_date_gmt',
                        'post_modified','post_modified_gmt',
                    ),
                    $tpdb->comments => array(
                        'comment_date','comment_date_gmt',
                    ),
                    $tpdb->users    => array(
                        'user_registered',
                    ),
                    $tpdb->blogs    => array(
                        'registered','last_updated',
                    ),
                );
                foreach ( $known_columns as $table_name => $table_columns ) {
                    if ( in_array( $column, $table_columns, true ) ) {
                        $column = $table_name . '.' . $column;
                        break;
                    }
                }
            }
            return preg_replace( '/[^a-zA-Z0-9_$\.]/', '', $column );
        }//475
        /**
         * @description Generate WHERE clause to be appended to a main query.
         * @return mixed
         */
        public function get_sql(){
            $sql = $this->_get_sql_clauses();
            $where = $sql['where'];
            return $this->_apply_filters( 'get_date_sql', $where, $this );
        }//548
        /**
         * @description Generate SQL clauses to be appended to a main query.
         * @return array
         */
        protected function _get_sql_clauses(): array{
            $sql = $this->_get_sql_for_query( $this->queries );
            if ( ! empty( $sql['where'] ) )  $sql['where'] = ' AND ' . $sql['where'];
            return $sql;
        }//579
        /**
         * @description Generate SQL clauses for a single query array.
         * @param $query
         * @param int $depth
         * @return array
         */
        protected function _get_sql_for_query( $query, $depth = 0 ): array{
            $sql_chunks = array('join'  => array(),'where' => array(), );
            $sql = array('join'  => '','where' => '',);
            $indent = '';
            for ( $i = 0; $i < $depth; $i++ ) {
                $indent .= '  ';
            }
            foreach ( $query as $key => $clause ) {
                if ( 'relation' === $key )
                    $relation = $query['relation'];
                elseif ( is_array( $clause ) ) {
                    if ( $this->_is_first_order_clause( $clause ) ) {
                        $clause_sql = $this->_get_sql_for_clause( $clause, $query );
                        $where_count = count( $clause_sql['where'] );
                        if ( ! $where_count ) $sql_chunks['where'][] = '';
                        elseif ( 1 === $where_count ) $sql_chunks['where'][] = $clause_sql['where'][0];
                        else $sql_chunks['where'][] = '( ' . implode( ' AND ', $clause_sql['where'] ) . ' )';
                        $sql_chunks['join'] = array_merge( $sql_chunks['join'], $clause_sql['join'] );
                    } else {
                        $clause_sql = $this->_get_sql_for_query( $clause, $depth + 1 );
                        $sql_chunks['where'][] = $clause_sql['where'];
                        $sql_chunks['join'][]  = $clause_sql['join'];
                    }
                }
            }
            $sql_chunks['join']  = array_filter( $sql_chunks['join'] );
            $sql_chunks['where'] = array_filter( $sql_chunks['where'] );
            if ( empty( $relation ) ) $relation = 'AND';
            if ( ! empty( $sql_chunks['join'] ) ) $sql['join'] = implode( ' ', array_unique( $sql_chunks['join'] ) );
            if ( ! empty( $sql_chunks['where'] ) )
                $sql['where'] = '( ' . "\n  " . $indent . implode( ' ' . "\n  " . $indent . $relation . ' ' . "\n  " . $indent, $sql_chunks['where'] ) . "\n" . $indent . ')';
            return $sql;
        }//607
        /**
         * @description Turns a single date clause into pieces for a WHERE clause.
         * @param $query
         * @return array
         */
        protected function _get_sql_for_sub_query( $query ): array{
            return $this->_get_sql_for_clause( $query, '' );
        }//689
        /**
         * @description Turns a first-order date query into SQL for a WHERE clause.
         * @param $query
         * @param $parent_query
         * @return array
         */
        protected function _get_sql_for_clause( $query, $parent_query ): array{
            $tpdb = $this->_init_db();
            $this->__parent_query = $parent_query; //todo for now, have to sort this out
            $where_parts = [];
            $column = ( ! empty( $query['column'] ) ) ? $this->_esc_sql( $query['column'] ) : $this->column;
            $column = $this->validate_column( $column );
            $compare = $this->get_compare( $query );
            $inclusive = ! empty( $query['inclusive'] );
            $lt = '<';
            $gt = '>';
            if ( $inclusive ) {
                $lt .= '=';
                $gt .= '=';
            }
            // Range queries.
            if ( ! empty( $query['after'] ) )
                $where_parts[] = $tpdb->prepare( "$column $gt %s", $this->build_mysql_datetime( $query['after'], ! $inclusive ) );
            if ( ! empty( $query['before'] ) )
                $where_parts[] = $tpdb->prepare( "$column $lt %s", $this->build_mysql_datetime( $query['before'], $inclusive ) );
            $date_units = array(
                'YEAR'           => array( 'year' ),
                'MONTH'          => array( 'month', 'monthnum' ),
                '_tp_mysql_week' => array( 'week', 'w' ),
                'DAYOFYEAR'      => array( 'dayofyear' ),
                'DAYOFMONTH'     => array( 'day' ),
                'DAYOFWEEK'      => array( 'dayofweek' ),
                'WEEKDAY'        => array( 'dayofweek_iso' ),
            );
            // Check of the possible date units and add them to the query.
            foreach ( $date_units as $sql_part => $query_parts ) {
                foreach ( $query_parts as $query_part ) {
                    if ( isset( $query[ $query_part ] ) ) {
                        $value = $this->build_value( $compare, $query[ $query_part ] );
                        if ( $value ) {
                            switch ( $sql_part ) {
                                case '_tp_mysql_week':
                                    $where_parts[] = $this->_tp_mysql_week( $column ) . " $compare $value";
                                    break;
                                case 'WEEKDAY':
                                    $where_parts[] = "$sql_part( $column ) + 1 $compare $value";
                                    break;
                                default:
                                    $where_parts[] = "$sql_part( $column ) $compare $value";
                            }
                            break;
                        }
                    }
                }
            }
            if ( isset( $query['hour'] ) || isset( $query['minute'] ) || isset( $query['second'] ) ) {
                foreach ( array( 'hour', 'minute', 'second' ) as $unit) if (! isset( $query[ $unit ])) $query[ $unit ] = null;
                $time_query = $this->build_time_query( $column, $compare, $query['hour'], $query['minute'], $query['second'] );
                if ( $time_query )  $where_parts[] = $time_query;
            }
            return array('where' => $where_parts,'join'  => array(), );
        }//707
        /**
         * @description Builds and validates a value string based on the comparison operator.
         * @param $compare
         * @param $value
         * @return bool|int|string
         */
        public function build_value( $compare, $value ){
            if ( ! isset( $value ) ) return false;
            switch ( $compare ) {
                case 'IN':
                case 'NOT IN':
                    $value = (array) $value;
                    $value = array_filter( $value, 'is_numeric' );
                    if ( empty( $value ) ) return false;
                    return '(' . implode( ',', array_map( 'intval', $value ) ) . ')';
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    if ( ! is_array( $value ) || 2 !== count( $value ) )
                        $value = array( $value, $value );
                    else $value = array_values( $value );
                    foreach ( $value as $v ) if ( ! is_numeric( $v ) ) return false;
                    $value = array_map( 'intval', $value );
                    return $value[0] . ' AND ' . $value[1];
                default:
                    if ( ! is_numeric( $value ) ) return false;
                    return (int) $value;
            }
        }//805
        /**
         * @description Builds a MySQL format date/time based on some query parameters.
         * @param $datetime
         * @param bool $default_to_max
         * @return string
         */
        public function build_mysql_datetime( $datetime, $default_to_max = false ): string{
            if ( ! is_array( $datetime ) ) {
                if ( preg_match( '/^(\d{4})$/', $datetime, $matches ) ) {
                    // Y
                    $datetime = array('year' => (int) $matches[1], );
                } elseif ( preg_match( '/^(\d{4})\-(\d{2})$/', $datetime, $matches ) ) {
                    // Y-m
                    $datetime = array('year' => (int) $matches[1],'month' => (int) $matches[2], );
                } elseif ( preg_match( '/^(\d{4})\-(\d{2})\-(\d{2})$/', $datetime, $matches ) ) {
                    // Y-m-d
                    $datetime = array('year' => (int) $matches[1],'month' => (int) $matches[2],'day' => (int) $matches[3],);
                } elseif ( preg_match( '/^(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2})$/', $datetime, $matches ) ) {
                    // Y-m-d H:i
                    $datetime = array(
                        'year' => (int) $matches[1],'month' => (int) $matches[2],'day' => (int) $matches[3],
                        'hour' => (int) $matches[4],'minute' => (int) $matches[5],
                    );
                }
                if ( ! is_array( $datetime ) ) {
                    $tp_timezone = $this->_tp_timezone();
                    $dt = date_create( $datetime, $tp_timezone );
                    if ( false === $dt ) return gmdate( 'Y-m-d H:i:s', false );
                    return $dt->setTimezone( $tp_timezone )->format( 'Y-m-d H:i:s' );
                }
            }
            $datetime = array_map( 'absint', $datetime );
            if ( ! isset( $datetime['year'] ) ) $datetime['year'] = $this->_current_time( 'Y' );
            if ( ! isset( $datetime['month'] ) )  $datetime['month'] = ( $default_to_max ) ? 12 : 1;
            if ( ! isset( $datetime['day'] ) ) $datetime['day'] = ( $default_to_max ) ? (int) gmdate( 't', mktime( 0, 0, 0, $datetime['month'], 1, $datetime['year'] ) ) : 1;
            if ( ! isset( $datetime['hour'] ) ) $datetime['hour'] = ( $default_to_max ) ? 23 : 0;
            if ( ! isset( $datetime['minute'] ) )  $datetime['minute'] = ( $default_to_max ) ? 59 : 0;
            if ( ! isset( $datetime['second'] ) ) $datetime['second'] = ( $default_to_max ) ? 59 : 0;
            return sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $datetime['year'], $datetime['month'], $datetime['day'], $datetime['hour'], $datetime['minute'], $datetime['second'] );
        }//868
        /**
         * @description Builds a query string for comparing time values (hour, minute, second).
         * @param $column
         * @param $compare
         * @param null $hour
         * @param null $minute
         * @param null $second
         * @return bool|null|string
         */
        public function build_time_query( $column, $compare, $hour = null, $minute = null, $second = null ){
            $tpdb = $this->_init_db();
            if ( ! isset( $hour ) && ! isset( $minute ) && ! isset( $second ) ) return false;
            if ( in_array( $compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) ) {
                $return = [];
                $value = $this->build_value( $compare, $hour );
                if ( false !== $value ) $return[] = "HOUR( $column ) $compare $value";
                $value = $this->build_value( $compare, $minute );
                if ( false !== $value ) $return[] = "MINUTE( $column ) $compare $value";
                $value = $this->build_value( $compare, $second );
                if ( false !== $value ) $return[] = "SECOND( $column ) $compare $value";
                return implode( ' AND ', $return );
            }
            // Cases where just one unit is set.
            if ( isset( $hour ) && ! isset( $minute ) && ! isset( $second ) ) {
                $value = $this->build_value( $compare, $hour );
                if ( false !== $value ) return "HOUR( $column ) $compare $value";
            } elseif ( ! isset( $hour ) && isset( $minute ) && ! isset( $second ) ) {
                $value = $this->build_value( $compare, $minute );
                if ( false !== $value )  return "MINUTE( $column ) $compare $value";
            } elseif ( ! isset( $hour ) && ! isset( $minute ) && isset( $second ) ) {
                $value = $this->build_value( $compare, $second );
                if ( false !== $value ) return "SECOND( $column ) $compare $value";
            }
            if ( ! isset( $minute ) ) return false;
            $format = '';
            $time   = '';
            // Hour.
            if ( null !== $hour ) {
                $format .= '%H.';
                $time   .= sprintf( '%02d', $hour ) . '.';
            } else {
                $format .= '0.';
                $time   .= '0.';
            }
            // Minute.
            $format .= '%i';
            $time   .= sprintf( '%02d', $minute );
            if ( isset( $second ) ) {
                $format .= '%s';
                $time   .= sprintf( '%02d', $second );
            }
            return $tpdb->prepare( "DATE_FORMAT( $column, %s ) $compare %f", $format, $time );        }//967
    }
}else die;