<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _general_template_09 {
        use _init_rewrite,_init_assets,_init_queries;
        /**
         * @description Retrieves paginated links for archive post pages.
         * @param string|array $args
         * @return array|string
         */
        protected function _paginate_links(...$args){
            $tp_rewrite = $this->_init_rewrite();
            $tp_query = $this->_init_query();
            $page_num_link = html_entity_decode( $this->_get_page_num_link() );
            $url_parts    = explode( '?', $page_num_link );
            $total   = $tp_query->max_num_pages ?? 1;
            $current = $this->_get_query_var( 'paged' ) ? (int) $this->_get_query_var( 'paged' ) : 1;
            $page_num_link = $this->_trailingslashit( $url_parts[0] ) . '%_%';
            $format  = $tp_rewrite->using_index_permalinks() && ! strpos( $page_num_link, 'index.php' ) ? 'index.php/' : '';
            $format .= $tp_rewrite->using_permalinks() ? $this->_user_trailingslashit( $tp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';
            $defaults = ['base' => $page_num_link,'format' => $format, 'total' => $total,'current' => $current,
                'aria_current' => 'page','show_all' => false,'prev_next' => true,'prev_text' => $this->__( '&laquo; Previous' ),
                'next_text' => $this->__( 'Next &raquo;' ),'end_size' => 1,'mid_size' => 2,'type' => 'plain','add_args' => [],
                'add_fragment' => '','before_page_number' => '','after_page_number' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( ! is_array( $args['add_args'] ) ) $args['add_args'] = [];
            if ( isset( $url_parts[1] ) ) {
                $format = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
                $format_query = $format[1] ?? '';
                $this->_tp_parse_str( $format_query, $format_args );
                $this->_tp_parse_str( $url_parts[1], $url_query_args );
                foreach ( $format_args as $format_arg => $format_arg_value )
                    unset( $url_query_args[ $format_arg ] );
                $args['add_args'] = array_merge( $args['add_args'], $this->_url_encode_deep( $url_query_args ) );
            }
            $total = (int) $args['total'];
            if ( $total < 2 )  return null;
            $current  = (int) $args['current'];
            $end_size = (int) $args['end_size']; // Out of bounds? Make it the default.
            if ( $end_size < 1 ) $end_size = 1;
            $mid_size = (int) $args['mid_size'];
            if ( $mid_size < 0 ) $mid_size = 2;
            $add_args   = $args['add_args'];
            $r          = '';
            $page_links = [];
            $dots       = false;
            if ( $args['prev_next'] && $current && 1 < $current ) :
                $link = str_replace(array('%_%', '%#%'), array(2 === $current ? '' : $args['format'], $current - 1), $args['base']);
                if ( $add_args ) $link = $this->_add_query_arg( $add_args, $link );
                $link .= $args['add_fragment'];
                $page_links[] = sprintf(
                    "<a class='prev page-numbers' href='%s'>%s</a>",
                    $this->_esc_url( $this->_apply_filters( 'paginate_links', $link ) ),
                    $args['prev_text']
                );
            endif;
            for ( $n = 1; $n <= $total; $n++ ){
                if ( $n === $current ){
                    $page_links[] = sprintf(
                        '<span aria-current="%s" class="page-numbers current">%s</span>',
                        $this->_esc_attr( $args['aria_current'] ),
                        $args['before_page_number'] . $this->_number_format_i18n( $n ) . $args['after_page_number']
                    );
                    $dots = true;
                }else if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ){
                    $link = str_replace(array('%_%', '%#%'), array(1 === $n ? '' : $args['format'], $n), $args['base']);
                    if ( $add_args )
                        $link = $this->_add_query_arg( $add_args, $link );
                    $link .= $args['add_fragment'];
                    $page_links[] = sprintf(
                        '<a class="page-numbers" href="%s">%s</a>',
                        $this->_esc_url( $this->_apply_filters( 'paginate_links', $link ) ),
                        $args['before_page_number'] . $this->_number_format_i18n( $n ) . $args['after_page_number']
                    );
                    $dots = true;
                }elseif ( $dots && ! $args['show_all'] ){
                    $page_links[] = '<span class="page-numbers dots">' . $this->__( '&hellip;' ) . '</span>';
                    $dots = false;                    }
            }
            if ( $args['prev_next'] && $current && $current < $total ) :
                $link = str_replace(array('%_%', '%#%'), array($args['format'], $current + 1), $args['base']);
                if ( $add_args ) $link = $this->_add_query_arg( $add_args, $link );
                $link .= $args['add_fragment'];
                $page_links[] = sprintf(
                    '<a class="next page-numbers" href="%s">%s</a>',
                    /** This filter is documented in wp-includes/general-template.php */
                    $this->_esc_url( $this->_apply_filters( 'paginate_links', $link ) ),
                    $args['next_text']
                );
            endif;
            switch ( $args['type'] ) {
                case 'array':
                    return $page_links;
                case 'list':
                    $r .= "<ul class='page-numbers'>\n\t<li>";
                    $r .= implode( "</li>\n\t<li>", $page_links );
                    $r .= "</li>\n</ul>\n";
                    break;
                default:
                    $r = implode( "\n", $page_links );
                    break;
            }
            $r = $this->_apply_filters( 'paginate_links_output', $r, $args );
            return $r;
        }//4203 from general-template
        /**
         * @description Registers an admin color scheme css file.
         * @param $key
         * @param $name
         * @param $url
         * @param array $colors
         * @param array $icons
         */
        protected function _tp_admin_css_color( $key, $name, $url, $colors = [], $icons = []):void{
            if ( ! isset( $this->__tp_admin_css_colors ) )
                $this->__tp_admin_css_colors = [];
            $this->__tp_admin_css_colors[$key] = (object)['name' => $name,'url' => $url,'colors' => $colors,'icon_colors' => $icons,];
        }//4411 from general-template
        /**
         * @description Registers the default admin color schemes.
         */
        protected function _register_admin_color_schemes():void{

            $suffix  = $this->_is_rtl() ? '-rtl' : '';
            $suffix .= TP_SCRIPT_DEBUG ? '' : '.min';
            $this->_tp_admin_css_color(
                'fresh',
                $this->_x( 'Default', 'admin color scheme' ),
                false,
                array( '#1d2327', '#2c3338', '#2271b1', '#72aee6' ),
                array(
                    'base'    => '#a7aaad',
                    'focus'   => '#72aee6',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'light',
                $this->_x( 'Light', 'admin color scheme' ),
                $this->_admin_url( "css/colors/light/colors$suffix.css" ),
                array( '#e5e5e5', '#999', '#d64e07', '#04a4cc' ),
                array(
                    'base'    => '#999',
                    'focus'   => '#ccc',
                    'current' => '#ccc',
                )
            );
            $this->_tp_admin_css_color(
                'modern',
                $this->_x( 'Modern', 'admin color scheme' ),
                $this->_admin_url( "css/colors/modern/colors$suffix.css" ),
                array( '#1e1e1e', '#3858e9', '#33f078' ),
                array(
                    'base'    => '#f3f1f1',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'blue',
                $this->_x( 'Blue', 'admin color scheme' ),
                $this->_admin_url( "css/colors/blue/colors$suffix.css" ),
                array( '#096484', '#4796b3', '#52accc', '#74B6CE' ),
                array(
                    'base'    => '#e5f8ff',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'midnight',
                $this->_x( 'Midnight', 'admin color scheme' ),
                $this->_admin_url( "css/colors/midnight/colors$suffix.css" ),
                array( '#25282b', '#363b3f', '#69a8bb', '#e14d43' ),
                array(
                    'base'    => '#f1f2f3',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'sunrise',
                $this->_x( 'Sunrise', 'admin color scheme' ),
                $this->_admin_url( "css/colors/sunrise/colors$suffix.css" ),
                array( '#b43c38', '#cf4944', '#dd823b', '#ccaf0b' ),
                array(
                    'base'    => '#f3f1f1',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'ectoplasm',
                $this->_x( 'Ectoplasm', 'admin color scheme' ),
                $this->_admin_url( "css/colors/ectoplasm/colors$suffix.css" ),
                array( '#413256', '#523f6d', '#a3b745', '#d46f15' ),
                array(
                    'base'    => '#ece6f6',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'ocean',
                $this->_x( 'Ocean', 'admin color scheme' ),
                $this->_admin_url( "css/colors/ocean/colors$suffix.css" ),
                array( '#627c83', '#738e96', '#9ebaa0', '#aa9d88' ),
                array(
                    'base'    => '#f2fcff',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
            $this->_tp_admin_css_color(
                'coffee',
                $this->_x( 'Coffee', 'admin color scheme' ),
                $this->_admin_url( "css/colors/coffee/colors$suffix.css" ),
                array( '#46403c', '#59524c', '#c7a589', '#9ea476' ),
                array(
                    'base'    => '#f3f2f1',
                    'focus'   => '#fff',
                    'current' => '#fff',
                )
            );
        }//4436 from general-template //todo customizing
        /**
         * @description Displays the URL of a TailoredPress admin CSS file.
         * @param string $file
         * @return string
         */
        protected function _tp_admin_css_uri( $file = 'tp-admin' ):string{
            if ( defined( 'TP_INSTALLING' ) ) { $_file = "./$file.css";}
            else { $_file = $this->_admin_url( "$file.css" );}
            $_file = $this->_add_query_arg( 'version', $this->_get_bloginfo( 'version' ), $_file );
            return $this->_apply_filters( 'tp_admin_css_uri', $_file, $file );
        }//4560 from general-template
        /**
         * @description  Enqueues or directly prints a stylesheet link to the specified CSS file.
         * @param string $file
         * @return mixed
         */
        protected function _tp_get_admin_css( $file = 'tp-admin'){
            $handle = 0 === strpos( $file, 'css/' ) ? substr( $file, 4 ) : $file;
            $this->tp_styles = $this->_init_styles();
            static $get_style ='';
            if ( $this->tp_styles->query( $handle ) ) {
                if ($this->_did_action( 'tp_print_styles' ) ) {
                    $get_style = $this->tp_print_styles( $handle );
                } else { $this->tp_enqueue_style( $handle );}
                return;
            }
            $stylesheet_link = sprintf(
                "<link rel='stylesheet' href='%s' type='text/css' />\n",
                $this->_esc_url( $this->_tp_admin_css_uri( $file ) )
            );
            $get_style .= $this->_apply_filters( 'tp_admin_css', $stylesheet_link, $file );
            if(is_callable(self::class, '_is_rtl') && $this->_is_rtl()){
                $rtl_stylesheet_link = sprintf(
                    "<link rel='stylesheet' href='%s' type='text/css' />\n",
                    $this->_esc_url( $this->_tp_admin_css_uri( "$file-rtl" ) )
                );
                $get_style .= $rtl_stylesheet_link;
            }
            return $get_style;
        }//4599 todo testing
        protected function _tp_admin_css( $file = 'tp-admin'):void{
            echo $this->_tp_get_admin_css( $file);
        }//4599

        /**
         * @description Enqueues the default ThickBox js and css.
         */
        protected function _add_thick_box():void{
            $this->tp_enqueue_script( 'thickbox' );
            $this->tp_enqueue_style( 'thickbox' );
            if ($this->_is_network_admin() ) {
                $this->_add_action( 'admin_head', [$this,'get_thickbox_path_admin_subfolder'] );
            }//todo, this might be moved to admin index header?
        }//4652 from general-template
        /**
         * @description Outputs the HTML checked attribute.
         * @param $checked
         * @param bool $current
         * @return string
         */
        protected function _get_checked( $checked, $current = true):string{
            return $this->_get_checked_selected_helper( $checked, $current, 'checked' );
        }//4804 from general-template
        /**
         * @description Outputs the HTML selected attribute.
         * @param $selected
         * @param bool $current
         * @return string
         */
        protected function _get_selected( $selected, $current = true ):string{
            return $this->_get_checked_selected_helper( $selected, $current, 'selected' );
        }//4822 from general-template
        /**
         * @description Outputs the HTML disabled attribute.
         * @param $disabled
         * @param bool $current
         * @return string
         */
        protected function _get_disabled( $disabled, $current = true):string{
            return $this->_get_checked_selected_helper( $disabled, $current, 'disabled' );
        }//4840 from general-template
        /**
         * @description Outputs the HTML readonly attribute.
         * @param $readonly
         * @param bool $current
         * @return string
         */
        protected function _tp_get_readonly( $readonly, $current = true ):string{
            return $this->_get_checked_selected_helper( $readonly, $current, 'readonly' );
        }//4858 from general-template
        /**
         * @description Helper function for checked, selected, disabled and readonly.
         * @param $helper
         * @param $current
         * @param $type
         * @return string
         */
        protected function _get_checked_selected_helper( $helper, $current, $type ):string{
            if ( (string) $helper === (string) $current ) $result = " $type='$type'";
            else $result = '';
            return $result;
        }//4866 from general-template
    }
}else die;