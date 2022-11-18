<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:32
 */
namespace TP_Core\Traits\Query;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    trait _query_01 {
        use _init_queries;
        /**
         * @description Retrieves the value of a query variable in the WP_Query class.
         * @param $var
         * @param string $default
         * @return string
         */
        protected function _get_query_var($var, $default =''): string{
            return $this->_init_query()->get( $var, $default );
        }//26
        /**
         * @description Retrieves the currently queried object.
         */
        protected function _get_queried_object(){
            return $this->_init_query()->get_queried_object() ;
        }//41
        /**
         * @description Retrieves the ID of the currently queried object.
         * @return int
         */
        protected function _get_queried_object_id(): int{
            return $this->_init_query()->get_queried_object_id() ;
        }//58
        /**
         * @description Sets the value of a query variable in the TP_Query class.
         * @param $var
         * @param $value
         */
        protected function _set_query_var( $var, $value ): void{
            $this->_init_query()->set($var, $value);
        }//73
        /**
         * @description Sets up The Loop with query parameters.
         * @param $query
         * @return mixed
         */
        protected function _query_posts( $query ){
            $this->_tp_query = new TP_Query();
            return $this->_tp_query->query_main($query);
        }//96
        /**
         * @description Destroys the previous query and sets up a new query.
         */
        protected function _tp_reset_query(): void{
            $this->_tp_query = $this->getTpTheQuery();
            $this->_tp_reset_post_data();
        }//113
        /**
         * @description After looping through a separate query, this function restores
         * the $post global to the current post in the main query.
         */
        protected function _tp_reset_post_data(): void{
            $tp_query = $this->_init_query();
            if ( isset( $tp_query ) ) $tp_query->reset_postdata();
        }//126
        /**
         * @description Determines whether the query is for an existing archive page.
         * @return bool
         */
        protected function _is_archive(): bool{
            return $this->_init_query()->is_archive();
        }//160
        /**
         * @description Determines whether the query is for an existing post type archive page.
         * @param string $post_types
         * @return bool
         */
        protected function _is_post_type_archive($post_types = ''): bool{
            return $this->_init_query()->is_post_type_archive( $post_types );
        }//186
        /**
         * @description Determines whether the query is for an existing attachment page.
         * @param string $attachment
         * @return bool
         */
        protected function _is_attachment( $attachment = '' ): bool{
            return $this->_init_query()->is_attachment( $attachment );
        }//212
    }
}else die;