<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
if(ABSPATH){
    trait _media_04 {
        /**
         * @description Adds `loading` attribute to an `img` HTML tag.
         * @param $image
         * @param $context
         * @return mixed
         */
        protected function _tp_img_tag_add_loading_attr( $image, $context ){
            $value = $this->_tp_get_loading_attr_default( $context );
            if ( false === strpos( $image, " src='" ) || false === strpos( $image, " width='" ) || false === strpos( $image, " height='" ) )
                return $image;
            $value = $this->_apply_filters( 'tp_img_tag_add_loading_attr', $value, $image, $context );
            if ( $value ) {
                if ( ! in_array( $value, array( 'lazy', 'eager' ), true ) ) $value = 'lazy';
                return str_replace( '<img', "<img loading='{$this->_esc_attr( $value )}'", $image );
            }
            return $image;
        }//1878
        /**
         * @description Adds `width` and `height` attributes to an `img` HTML tag.
         * @param $image
         * @param $context
         * @param $attachment_id
         * @return mixed
         */
        protected function _tp_img_tag_add_width_and_height_attr( $image, $context, $attachment_id ){
            $image_src         = preg_match( "/src='([^']+)'/", $image, $match_src ) ? $match_src[1] : '';
            @list( $image_src ) = explode( '?', $image_src );
            if ( ! $image_src ) return $image;
            $add = $this->_apply_filters( 'tp_img_tag_add_width_and_height_attr', true, $image, $context, $attachment_id );
            if ( true === $add ) {
                $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
                $size_array = $this->_tp_image_src_get_dimensions( $image_src, $image_meta, $attachment_id );
                if ( $size_array ) {
                    $hw = trim( $this->_image_hwstring( $size_array[0], $size_array[1] ) );
                    return str_replace( '<img', "<img {$hw}", $image );
                }
            }
            return $image;
        }//1924
        /**
         * @description Adds `srcset` and `sizes` attributes to an existing `img` HTML tag.
         * @param $image
         * @param $context
         * @param $attachment_id
         * @return mixed
         */
        protected function _tp_img_tag_add_srcset_and_sizes_attr( $image, $context, $attachment_id ){
            $add = $this->_apply_filters( 'tp_img_tag_add_srcset_and_sizes_attr', true, $image, $context, $attachment_id );
            if ( true === $add ) {
                $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
                return $this->_tp_image_add_srcset_and_sizes( $image, $image_meta, $attachment_id );
            }
            return $image;
        }//1970
        /**
         * @description Adds `loading` attribute to an `iframe` HTML tag.
         * @param $iframe
         * @param $context
         * @return mixed
         */
        protected function _tp_iframe_tag_add_loading_attr( $iframe, $context ){
            if ( false !== strpos( $iframe, " data-secret='" ) ) return $iframe;
            $value = $this->_tp_get_loading_attr_default( $context );
            if ( false === strpos( $iframe, " src='" ) || false === strpos( $iframe, " width='" ) || false === strpos( $iframe, " height='" ) )
                return $iframe;
            $value = $this->_apply_filters( 'tp_iframe_tag_add_loading_attr', $value, $iframe, $context );
            if ( $value ) {
                if ( ! in_array( $value, array( 'lazy', 'eager' ), true ) ) $value = 'lazy';
                return str_replace( '<iframe', "<iframe loading='{$this->_esc_attr( $value )}'", $iframe );
            }
            return $iframe;
        }//2002
        /**
         * @description Adds a 'tp-post-image' class to post thumbnails. Internal use only.
         * @param $attr
         * @return mixed
         */
        protected function _tp_post_thumbnail_class_filter( $attr ){
            $attr['class'] .= ' tp-post-image';
            return $attr;
        }//2056
        /**
         * @description Adds '_tp_post_thumbnail_class_filter' callback to the 'tp_get_attachment_image_attributes'
         * @param $attr
         */
        protected function _tp_post_thumbnail_class_filter_add( $attr ):void{
            $this->_add_filter( 'tp_get_attachment_image_attributes',[$this,'__tp_post_thumbnail_class_filter'],$attr);
        }//2070
        /**
         * @description Removes the '_tp_post_thumbnail_class_filter' callback from the 'tp_get_attachment_image_attributes'
         * @param $attr
         */
        protected function _tp_post_thumbnail_class_filter_remove( $attr ):void{
            $this->_add_filter( 'tp_get_attachment_image_attributes',[$this,'__tp_post_thumbnail_class_filter'],$attr);
        }//2083
        /**
         * @description Builds the Caption shortcode output.
         * @param $attr
         * @param string $content
         * @return string
         */
        protected function _get_img_caption_shortcode( $attr, $content = '' ):string{
            if ( ! isset( $attr['caption'] ) ) {
                if ( preg_match( '#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches ) ) {
                    $content         = $matches[1];
                    $attr['caption'] = trim( $matches[2] );
                }
            } elseif ( strpos( $attr['caption'], '<' ) !== false )
                $attr['caption'] = $this->_tp_kses( $attr['caption'], 'post' );
            $output = $this->_apply_filters( 'img_caption_shortcode', '', $attr, $content );
            if ( ! empty( $output ) )
                return $output;
            $atts = $this->_shortcode_atts(
                ['id' => '','caption_id' => '','align' => 'alignnone','width' => '', 'caption' => '','class' => '',],
                $attr,
                'caption'
            );
            $atts['width'] = (int) $atts['width'];
            if ( $atts['width'] < 1 || empty( $atts['caption'] ) )
                return $content;
            $id          = '';
            $caption_id  = '';
            $describedby = '';
            if ( $atts['id'] ) {
                $atts['id'] = $this->_sanitize_html_class( $atts['id'] );
                $id         = "id='{$this->_esc_attr( $atts['id'] )}'";
            }
            if ( $atts['caption_id'] )
                $atts['caption_id'] = $this->_sanitize_html_class( $atts['caption_id'] );
            elseif ( $atts['id'] )
                $atts['caption_id'] = 'caption_' . str_replace( '-', '_', $atts['id'] );
            if ( $atts['caption_id'] ) {
                $caption_id  = 'id="' . $this->_esc_attr( $atts['caption_id'] ) . '" ';
                $describedby = "aria-describedby='{$this->_esc_attr( $atts['caption_id'] )}'";
            }
            $class = trim( 'tp-caption ' . $atts['align'] . ' ' . $atts['class'] );
            $html5 = 'html5';
            $width = $html5 ? $atts['width'] : ( 10 + $atts['width'] );
            $caption_width = $this->_apply_filters( 'img_caption_shortcode_width', $width, $atts, $content );
            $style = '';
            if ((int) $caption_width )
                $style = " style='width:{$caption_width}px;'";
            $html = sprintf(
                "<figure %s%s% class='%s'>%s%s</figure>",
                $id,
                $describedby,
                $style,
                $this->_esc_attr( $class ),
                $this->_do_shortcode( $content ),
                sprintf(
                    "<figure %s class='tp-caption-text'>%s</figure>",
                    $caption_id,
                    $atts['caption']
                )
            );
            return $html;
        }//2119
        protected function _img_caption_shortcode( $attr, $content = '' ):string{
            return $this->_get_img_caption_shortcode( $attr, $content);
        }//added
        /**
         * @description Builds the Gallery shortcode output.
         * @param $attr
         * @return string
         */
        protected function _get_gallery_shortcode( $attr ):string{
            $post = $this->_get_post();
            static $instance = 0;
            $instance++;
            if ( ! empty( $attr['ids'] ) ) {
                if ( empty( $attr['orderby'] ) ) $attr['orderby'] = 'post__in';
                $attr['include'] = $attr['ids'];
            }
            $output = $this->_apply_filters( 'post_gallery', '', $attr, $instance );
            if ( ! empty( $output ) ) return $output;
            $atts  = $this->_shortcode_atts(
                ['order' => 'ASC','orderby' => 'menu_order ID','id' => $post ? $post->ID : 0,
                    'itemtag' => 'figure','icontag' => 'div','captiontag' => 'figcaption','columns' => 3,
                    'size' => 'thumbnail','include' => '','exclude' => '','link' => '',],
                $attr,
                'gallery'
            );
            $id = (int) $atts['id'];
            if ( ! empty( $atts['include'] ) ) {
                $_attachments = $this->_get_posts(
                    ['include' => $atts['include'],'post_status' => 'inherit','post_type' => 'attachment',
                        'post_mime_type' => 'image','order' => $atts['order'],'orderby' => $atts['orderby'],]
                );
                $attachments = [];
                foreach ( $_attachments as $key => $val ) $attachments[ $val->ID ] = $_attachments[ $key ];
            }elseif ( ! empty( $atts['exclude'] ) ) {
                $attachments = $this->_get_children(
                    ['post_parent' => $id,'exclude' => $atts['exclude'],'post_status' => 'inherit','post_type' => 'attachment',
                        'post_mime_type' => 'image', 'order' => $atts['order'],'orderby' => $atts['orderby'],]
                );
            }
            else {
                $attachments = $this->_get_children(
                    ['post_parent' => $id,'post_status' => 'inherit','post_type' => 'attachment',
                        'post_mime_type' => 'image','order' => $atts['order'],'orderby' => $atts['orderby'],]
                );
            }
            if ( empty( $attachments ) ) return '';
            if ( $this->_is_feed() ) {
                $output = "\n";
                foreach ( $attachments as $att_id => $attachment ) {
                    if ( ! empty( $atts['link'] ) ) {
                        if ( 'none' === $atts['link'] )
                            $output .= $this->_tp_get_attachment_image( $att_id, $atts['size'], false, $attr );
                        else $output .= $this->_tp_get_attachment_link( $att_id, $atts['size'], false );
                    } else  $output .= $this->_tp_get_attachment_link( $att_id, $atts['size'], true );
                    $output .= "\n";
                }
                return $output;
            }
            $itemtag    = $this->_tag_escape( $atts['itemtag'] );
            $captiontag = $this->_tag_escape( $atts['captiontag'] );
            $icontag    = $this->_tag_escape( $atts['icontag'] );
            $valid_tags = $this->_tp_kses_allowed_html( 'post' );
            if ( ! isset( $valid_tags[ $itemtag ] ) ) $itemtag = 'dl';
            if ( ! isset( $valid_tags[ $captiontag ] ) ) $captiontag = 'dd';
            if ( ! isset( $valid_tags[ $icontag ] ) )  $icontag = 'dt';
            $columns   = (int) $atts['columns'];
            $itemwidth = $columns > 0 ? floor( 100 / $columns ) : 100;
            $selector = "gallery_{$instance}";
            /* todo as I don't want to make use of float shit, just some selectors to get the vars used*/
            $gallery_style = "<style>";
            $gallery_style .= "#{$selector}{margin: auto;}";
            $gallery_style .= "#{$selector} .gallery-item{margin-top: 10px;text-align: center;width: {$itemwidth}%;}";
            $gallery_style .= "#{$selector} img {border: 2px solid #cfcfcf;}";
            $gallery_style .= "#{$selector} .gallery-caption {margin-left: 0;}";
            $gallery_style .= "</style>";
            $size_class  = $this->_sanitize_html_class( is_array( $atts['size'] ) ? implode( 'x', $atts['size'] ) : $atts['size'] );
            $gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";
            $output = $this->_apply_filters( 'gallery_style', $gallery_style . $gallery_div );
            $i = 0;
            foreach ( $attachments as $id => $attachment ) {
                $attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';
                if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] )
                    $image_output = $this->_tp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
                elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] )
                    $image_output = $this->_tp_get_attachment_image( $id, $atts['size'], false, $attr );
                else $image_output = $this->_tp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
                $image_meta = $this->_tp_get_attachment_metadata( $id );
                $orientation = '';
                if ( isset( $image_meta['height'], $image_meta['width'] ) )
                    $orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
                $output .= "<{$itemtag} class='gallery-item'>";
                $output .= "<{$icontag} class='gallery-icon {$orientation}'>$image_output</{$icontag}>";
                if ( $captiontag && trim( $attachment->post_excerpt ) ) {
                    $output .= "<{$captiontag} class='tp-caption-text gallery-caption' id='{$selector}_{$id}'>";
                    $output .= $this->_tp_texturize( $attachment->post_excerpt );
                    $output .= "</{$captiontag}>";
                }
                $output .= "</{$itemtag}>";
                /** @noinspection OnlyWritesOnParameterInspection */
                $i++;
            }
            $output .= "</div>\n";
            return $output;
        }//2284
        public function gallery_shortcode( $attr ):string{
            return $this->_get_gallery_shortcode( $attr );
        }
        /**
         * @note seems to be for ie but that is past :)
         * @description Outputs the templates used by play-lists.
         * @return string
         */
        protected function _get_underscore_playlist_templates():string{
            $script = "<script type='text/html' id='tp_template_current_playlist_item' class='item'>";
            $script .= "<# if ( data.thumb && data.thumb.src ) { #>";
            $data_thumb = '{{ data.thumb.src }}';
            $script .= "<img src='{$data_thumb}' alt=''/>";
            $script .= "<# } #>";
            $script .= "<div class='tp-playlist caption'>";
            $script .= "<span class='tp-playlist item meta title'>";
            ob_start();
            printf( $this->_x( '&#8220;%s&#8221;', 'playlist item title' ), '{{ data.title }}' );
            $script .= ob_get_clean();
            $script .= "</span>";
            $script .= "<# if ( data.meta.album ) { #>";
            $data_album ='{{ data.meta.album }}';
            $script .= "<span class='tp-playlist item meta album'>{$data_album}</span>";
            $script .= "<# } #>";
            $script .= "<# if ( data.meta.artist ) { #>";
            $data_artist ='{{ data.meta.artist }}';
            $script .= "<span class='tp-playlist item meta artist'>{$data_artist}</span>";
            $script .= "<# } #>";
            $script .= "</div></script>";
            $script .= "<script type='text/html' id='tp_template_playlist_item' class='item'>";
            $script .= "<div class='tp-playlist item'>";
            $data_src = '{{ data.src }}';
            $data_index = '{{ data.index ? ( data.index + \'. \' ) : \'\' }}';
            $data_caption = '{{ data.caption }}';
            $script .= "<a class='tp-playlist caption' href='{$data_src}'>";
            $script .= $data_index;
            $script .= "<# if ( data.caption ) { #>";
            $script .= $data_caption;
            $script .= "<# } else { #>";
            $script .= "<span class='tp-playlist item title'>";
            ob_start();
                printf( $this->_x( '&#8220;%s&#8221;', 'playlist item title' ), '{{{ data.title }}}' );
            $script .= ob_get_clean();
            $script .= "</span>";
            $script .= "<# if ( data.artists && data.meta.artist ) { #>";
            $data_artist ='{{ data.meta.artist }}';
            $script .= "<span class='tp-playlist item artist'>{$data_artist}</span>";
            $script .= "<# } #><# } #>";
            $script .= "</a>";
            $script .= "<# if ( data.meta.length_formatted ) { #>";
            $data_formatted ='{{ data.meta.length_formatted }}';
            $script .= "<div class='tp-playlist item length'>{$data_formatted}</div>";
            $script .= "<# } #>";
            $script .= "</div></script>";
            return $script;
        }//2528
        public function tp_underscore_playlist_templates():void{
            echo $this->_get_underscore_playlist_templates();
        }
     }
}else die;