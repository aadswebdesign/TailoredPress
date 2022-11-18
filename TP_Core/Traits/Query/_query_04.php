<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-5-2022
 * Time: 14:53
 */
namespace TP_Core\Traits\Query;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _query_04{
        use _init_queries;
        /**
         * @description Determines whether the query is for a specific time.
         * @return bool
         */
        protected function _is_time():bool{
            return $this->_init_query()->is_time();
        }//785
        /**
         * @description Determines whether the query is for a trackback endpoint call.
         * @return bool
         */
        protected function _is_trackback():bool{
            return $this->_init_query()->is_trackback();
        }//809
        /**
         * @description Determines whether the query is for an existing year archive.
         * @return bool
         */
        protected function _is_year():bool{
            return $this->_init_query()->is_year();
        }//833
        /**
         * @description Determines whether the query has resulted in a 404 (returns no results).
         * @return bool
         */
        protected function _is_404():bool{
            return $this->_init_query()->is_404();
        }//857
        /**
         * @description Is the query for an embedded post?
         * @return bool
         */
        protected function _is_embed():bool{
            return $this->_init_query()->is_embed();
        }//877
        /**
         * @description Determines whether the query is the main query.
         * @return bool
         */
        protected function _is_main_query():bool{
            return $this->_init_query()->is_main_query();
        }//901
        /**
         * @description Determines whether current TailoredPress query has posts to loop over.
         * @return bool
         */
        protected function _have_posts():bool{
            return $this->_init_query()->have_posts();
        }//940
        /**
         * @description Determines whether the caller is in the Loop.
         * @return bool
         */
        protected function _in_the_loop():bool{
            return $this->_init_query()->in_the_loop;
        }//958
        /**
         * @description Rewind the loop posts.
         */
        protected function _rewind_posts():bool{
            $this->_init_query()->rewind_posts();
        }//970
    }
}else die;