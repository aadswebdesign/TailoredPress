<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 04:24
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _post_template_04 {
        /**
         * @description  Determines whether currently in a page template.
         * @param array|string $template
         * @return bool
         */
        protected function _is_page_template( ...$template):bool{
            if ( ! $this->_is_singular()) return false;
            $page_template = $this->_get_page_template_slug( $this->_get_queried_object_id() );
            if ( empty( $template ) ) return (bool) $page_template;
            if ( $template === $page_template ) return true;
            if ( is_array( $template ) ) {
                if ( ( in_array( 'default', $template, true ) && ! $page_template ) || in_array( $page_template, $template, true ))
                   return true;
            }
            return ( 'default' === $template && ! $page_template );
        }//1763 from post-template
        /**
         * @description Get the specific template filename for a given post.
         * @param null $post
         * @return bool|string
         */
        protected function _get_page_template_slug( $post = null ){
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $template = $this->_get_post_meta( $post->ID, '_tp_page_template', true );
            if ( ! $template || 'default' === $template ) return '';
            return $template;
        }//1799 from post-template
        /**
         * @description Retrieve formatted date timestamp of a revision (linked to that revisions's page).
         * @param $revision
         * @param bool $link
         * @return bool|string
         */
        protected function _tp_post_revision_title( $revision, $link = true ){
            $revision = $this->_get_post( $revision );
            if ( ! $revision ) return $revision;
            if ( ! in_array( $revision->post_type, array( 'post', 'page', 'revision' ), true ) )
                return false;
            $datef = $this->_x( 'F j, Y @ H:i:s', 'revision date format' );
            $autosavef = $this->__( '%s [Autosave]' );
            $currentf = $this->__( '%s [Current Revision]' );
            $date      = $this->_date_i18n( $datef, strtotime( $revision->post_modified ) );
            $edit_link = $this->_get_edit_post_link( $revision->ID );
            if ($edit_link  && $link && $this->_current_user_can( 'edit_post', $revision->ID ))
                $date = "<a href='$edit_link'>$date</a>";
            if ( ! $this->_tp_is_post_revision( $revision ) )
                $date = sprintf( $currentf, $date );
            elseif ( $this->_tp_is_post_autosave( $revision ) )
                $date = sprintf( $autosavef, $date );
            return $date;
        }//1824 from post-template
        /**
         * @description Retrieve formatted date timestamp of a revision (linked to that revisions's page).
         * @param $revision
         * @param bool $link
         * @return bool
         */
        protected function _tp_post_revision_title_expanded( $revision, $link = true ):bool{
            $revision = $this->_get_post( $revision );
            if ( ! $revision ) return $revision;
            if ( ! in_array( $revision->post_type, array( 'post', 'page', 'revision' ), true ) )
                return false;
            $author = $this->_get_the_author_meta( 'display_name', $revision->post_author );
            $datef = $this->_x( 'F j, Y @ H:i:s', 'revision date format' );
            $gravatar = $this->_get_avatar( $revision->post_author, 24 );
            $date      = $this->_date_i18n( $datef, strtotime( $revision->post_modified ) );
            $edit_link = $this->_get_edit_post_link( $revision->ID );
            if ($edit_link && $link && $this->_current_user_can( 'edit_post', $revision->ID ))
                $date = "<a href='$edit_link'>$date</a>";
            $revision_date_author = sprintf($this->__( '%1$s %2$s, %3$s ago (%4$s)' ),$gravatar, $author,$this->_human_time_diff( strtotime( $revision->post_modified_gmt ) ),$date);
            $autosavef = $this->__( '%s [Autosave]' );
            $currentf = $this->__( '%s [Current Revision]' );
            if ( ! $this->_tp_is_post_revision( $revision ) )
                $revision_date_author = sprintf( $currentf, $revision_date_author );
            elseif ( $this->_tp_is_post_autosave( $revision ) )
                $revision_date_author = sprintf( $autosavef, $revision_date_author );
            return $this->_apply_filters( 'tp_post_revision_title_expanded', $revision_date_author, $revision, $link );
        }//1865 from post-template
        /**
         * @description Display a list of a post's revisions.
         * @param int $post_id
         * @param string $type
         * @return string
         */
        protected function _tp_get_list_post_revisions( $post_id = 0, $type = 'all' ):string{
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $revisions = $this->_tp_get_post_revisions( $post->ID );
            if ( ! $revisions ) return false;
            $rows = '';
            foreach ( $revisions as $revision ) {
                if ( ! $this->_current_user_can( 'read_post', $revision->ID ) ) continue;
                $is_autosave = $this->_tp_is_post_autosave( $revision );
                if ( ( 'revision' === $type && $is_autosave ) || ( 'autosave' === $type && ! $is_autosave ) )
                    continue;
                $rows .= "\t<li>{$this->_tp_post_revision_title_expanded( $revision )}</li>\n";
            }
            $output  = "<div class='hide-if-js'><p>{$this->__('JavaScript must be enabled to use this feature.')}</p></div>";
            $output .= "<ul class='post-revisions hide-if-no-js'>\n";
            $output .= $rows;
            $output .= "</ul>";
            return $output;
        }//1931 from post-template
        protected function _tp_list_post_revisions( $post_id = 0, $type = 'all' ):void{
            echo $this->_tp_get_list_post_revisions( $post_id, $type);
        }//1931
        /**
         * @description Retrieves the parent post object for the given post.
         * @param null $post
         * @return null
         */
        protected function _get_post_parent( $post = null ){
            $tp_post = $this->_get_post( $post );
            return ! empty( $tp_post->post_parent ) ? $this->_get_post( $tp_post->post_parent ) : null;
        }//1977 from post-template
        /**
         * @description Returns whether the given post has a parent post.
         * @param null $post
         * @return bool
         */
        protected function _has_post_parent( $post = null ):bool{
            return (bool) $this->_get_post_parent( $post );
        }//1990 from post-template
    }
}else die;