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
    trait _query_02 {
        use _init_queries;
        /**
         * @description Determines whether the query is for an existing author archive page.
         * @param string $author
         * @return bool
         */
        protected function _is_author($author=''): bool{
            return $this->_init_query()->is_author( $author );
        }//241
        /**
         * @description Determines whether the query is for an existing category archive page.
         * @param string $category
         * @return bool
         */
        protected function _is_category($category = ''): bool{
            return $this->_init_query()->is_category( $category );
        }//270
        /**
         * @description Determines whether the query is for an existing tag archive page.
         * @param string $tag
         * @return bool
         */
        protected function _is_tag($tag =''): bool{
            return $this->_init_query()->is_tag( $tag );
        }//299
        /**
         * @description Determines whether the query is for an existing custom taxonomy archive page.
         * @param string $taxonomy
         * @param string $term
         * @return bool
         */
        protected function _is_tax($taxonomy = '', $term = ''): bool{
            return $this->_init_query()->is_tax( $taxonomy, $term );
        }//336
        /**
         * @description Determines whether the query is for an existing date archive.
         * @return bool
         */
        protected function _is_date(): bool{
            return $this->_init_query()->is_date();
        }//360
        /**
         * @description Determines whether the query is for an existing day archive.
         * @return bool
         */
        protected function _is_day(): bool{
            return $this->_init_query()->is_day();
        }//386
        /**
         * @description Determines whether the query is for a feed.
         * @param string $feeds
         * @return bool
         */
        protected function _is_feed( $feeds = '' ): bool{
            return $this->_init_query()->is_feed( $feeds );
        }//412
        /**
         * @description Is the query for a comments feed?
         * @return bool
         */
        protected function _is_comment_feed(): bool{
            return $this->_init_query()->is_comment_feed();
        }//432
        /**
         * @description Determines whether the query is for the front page of the site.
         */
        protected function _is_front_page(): bool{
            $this->_init_query()->is_front_page();
        }//465
        /**
         * @description Determines whether the query is for the blog homepage.
         * @return bool
         */
        protected function _is_home(): bool{
            return $this->_init_query()->is_home();
        }//498
    }
}else die;