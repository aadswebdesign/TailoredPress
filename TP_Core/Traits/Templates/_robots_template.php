<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 20:44
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _robots_template {
        /**
         * @description Displays the robots meta tag as necessary.
         * @return bool|string
         */
        protected function _tp_get_robots(){
            $robots = $this->_apply_filters( 'tp_robots', [] );
            $robots_strings = array();
            foreach ((array) $robots as $directive => $value ) {
                if ( is_string( $value ) ) $robots_strings[] = "{$directive}:{$value}";
                elseif ( $value ) $robots_strings[] = $directive;
            }
            if ( empty( $robots_strings ) ) return false;
            return "<meta name='robots' content='{$this->_esc_attr( implode( ', ', $robots_strings ) )}' />\n";
        }//20 from robots-template
        protected function _tp_robots():void{
            echo $this->_tp_get_robots();
        }
        /**
         * @description Adds no index to the robots meta tag if required by the site configuration.
         * @param array $robots
         * @return array|void
         */
        protected function _tp_robots_no_index( array $robots ){
            if ( ! $this->_get_option( 'blog_public' ) )
                return $this->_tp_robots_no_robots( $robots );
            return $robots;
        }//70 from robots-template
        /**
         * @description Adds noindex to the robots meta tag for embeds.
         * @param array $robots
         * @return array
         */
        protected function _tp_robots_no_index_embed( array $robots ):array{
            if ( $this->_is_embed() ) return $this->_tp_robots_no_robots( $robots );
            return $robots;
        }//92 from robots-template
        /**
         * @description Adds no index to the robots meta tag if a search is being performed.
         * @param array $robots
         * @return array
         */
        protected function _tp_robots_no_index_search( array $robots ):array{
            if ( $this->_is_search() ) return $this->_tp_robots_no_robots( $robots );
            return $robots;
        }//118 from robots-template
        /**
         * @description Adds no_index to the robots meta tag.
         * @param array $robots
         * @return array
         */
        protected function _tp_robots_no_robots( array $robots ):array{
            $robots['noindex'] = true;
            if ( $this->_get_option( 'blog_public' ) )
                $robots['follow'] = true;
            else $robots['nofollow'] = true;
            return $robots;
        }//140 from robots-template
        /**
         * @description Adds no index and no archive to the robots meta tag.
         * @param array $robots
         * @return array
         */
        protected function _tp_get_robots_sensitive_page( array $robots ):array{
            $robots['noindex']   = true;
            $robots['noarchive'] = true;
            return $robots;
        }//167 from robots-template
        /**
         * @description Adds 'max-image-preview:large' to the robots meta tag.
         * @param array $robots
         * @return array
         */
        protected function _tp_robots_max_image_preview_large( array $robots ):array{
            if ( $this->_get_option( 'blog_public' ) )
                $robots['max-image-preview'] = 'large';
            return $robots;
        }//188 from robots-template
    }
}else die;