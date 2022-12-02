<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:32
 */
namespace TP_Core\Traits\Query;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _query_03 {
        use _init_queries;
        /**
         * @description Determines whether the query is for the Privacy Policy page.
         * @return bool
         */
        protected function _is_privacy_policy():bool{
            return $this->_init_query()->is_privacy_policy();
        }//528
        /**
         * @description Determines whether the query is for an existing month archive.
         * @return bool
         */
        protected function _is_month():bool{
            return $this->_init_query()->is_month();
        }//552
        /**
         * @description Determines whether the query is for an existing single page.
         * @param string $page
         * @return bool
         */
        protected function _is_page($page = ''):bool{
            return $this->_init_query()->is_page( $page );
        }//583
        /**
         * @description Determines whether the query is for a paged result and not for the first page.
         * @return bool
         */
        protected function _is_paged($page = ''):bool{
            return $this->_init_query()->is_paged();
        }//607
        /**
         * @description Determines whether the query is for a post or page preview.
         * @return bool
         */
        protected function _is_preview():bool{
            return $this->_init_query()->is_preview();
        }//631
        /**
         * @description Is the query for the robots.txt file?
         * @return bool
         */
        protected function _is_robots():bool{
            return $this->_init_query()->is_robots();
        }//651
        /**
         * @description Is the query for the favicon.ico file?
         * @return bool
         */
        protected function _is_favicon():bool{
            return $this->_init_query()->is_favicon();
        }//671
        /**
         * @return bool
         * @description Determines whether the query is for a search.
         */
        protected function _is_search():bool{
            return $this->_init_query()->is_search();
        }//695
        /**
         * @description Determines whether the query is for an existing single post.
         * @param string $post
         * @return bool
         */
        protected function _is_single($post = ''):bool{
            return $this->_init_query()->is_single( $post );
        }//728
        /**
         * @description Determines whether the query is for an existing single post of any post type
         * @param string $post_types
         * @return bool
         */
        protected function _is_singular($post_types = ''):bool{
            return $this->_init_query()->is_singular( $post_types );
        }//761
     }
}else die;