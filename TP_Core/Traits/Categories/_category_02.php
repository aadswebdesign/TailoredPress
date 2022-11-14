<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 11:06
 */
namespace TP_Core\Traits\Categories;
if(ABSPATH){
    trait _category_02{
        /**
         * @description Retrieves all post tags.
         * @param $tag
         * @param string $output
         * @param string $filter
         * @return mixed
         */
        protected function _get_tag( $tag, $output = OBJECT, $filter = 'raw' ) {
            return $this->_get_term( $tag, 'post_tag', $output, $filter );
        }//293
        /**
         * @description Retrieves a post tag by tag ID or tag object.
         * @param $id
         */
        protected function _clean_category_cache( $id ):void {
            $this->_clean_term_cache( $id, 'category' );
        }//339
        /**
         * @description Updates category structure to old pre-2.3 from new taxonomy structure.
         * @param $category
         */
        protected function _make_cat_compat( &$category ):void {
            if (is_object($category) && !$this->_init_error($category)) {
                $category->cat_ID = $category->term_id;
                $category->category_count = $category->count;
                $category->category_description = $category->description;
                $category->cat_name = $category->name;
                $category->category_nicename = $category->slug;
                $category->category_parent = $category->parent;
            } elseif (is_array($category) && isset($category['term_id'])) {
                $category['cat_ID'] = &$category['term_id'];
                $category['category_count'] = &$category['count'];
                $category['category_description'] = &$category['description'];
                $category['cat_name'] = &$category['name'];
                $category['category_nicename'] = &$category['slug'];
                $category['category_parent'] = &$category['parent'];
            }
        }//376
    }
}else die;