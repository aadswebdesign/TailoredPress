<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 15:46
 */
namespace TP_Core\Traits\Templates;
//use TP_Core\Libs\Post\TP_Post_Type;
//use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_error;

if(ABSPATH){
    trait _template_01 {
        use _init_error;
        protected function _get_query_template($class_name,$theme_name = null,$args = null):string{
            $theme_name = $theme_name ?? TP_NS_DEFAULT_THEME; //the default theme
            if(null === $class_name){
                $this->_init_error('A class name is needed for this method to work.');
                return false;
            }
            return $this->_tp_load_class($class_name,TP_NS_THEMES.$theme_name.TP_NS_THEME_TEMPLATE, $class_name.'_Template',$args);
        }//23
        protected function _get_index_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('Index',$theme_name,$args);
        }//118
        protected function _get_404_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('FourZeroFour',$theme_name,$args);
        }//134 uses __get_query_template
        protected function _get_archive_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);//todo Archive
        }//150 todo
        protected function _get_post_type_archive_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);//todo
        }//176
        protected function _get_author_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args); //todo Author
        }//214 todo
        protected function _get_category_template($theme_name = null,$args = null):string {
            return $this->_get_query_template('',$theme_name,$args); //todo Category
        }//254 todo
        protected function _get_tag_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args); //todo Tag
        }//318 todo
        protected function _get_taxonomy_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args); //todo Taxonomy
        }//346 todo
        protected function _get_date_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('Date',$theme_name,$args);
        }//379
        protected function _get_home_template($theme_name = null,$args = null):string{
            return $this->_get_query_template('',$theme_name,$args);//todo Home
        }//395 uses __get_query_template
    }
}else die;