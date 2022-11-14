<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-4-2022
 * Time: 06:13
 */
namespace TP_Core\Traits\Block;
if(ABSPATH){
    trait _block_duotone_01{
        /**
         * @description Takes input from [0, n] and returns it as [0, 1].
         * @param $n
         * @param $max
         * @return float
         */
        protected function _tp_tinycolor_bound01( $n, $max ): float {
            if ( 1 === (float) $n && 'string' === is_string( $n ) && false !== strpos( $n, '.' ))
                $n = '100%';
            $n = min( $max, max( 0, (float) $n ) );
            if ( 'string' === is_string( $n ) && false !== strpos( $n, '%' ) )
                $n = (int) ( $n * $max ) / 100;
            if ( ( abs( $n - $max ) < 0.000001 ) ) return 1.0;
            return ( $n % $max ) / (float) $max;
        }//50
        /**
         * @description Direct port of tiny_color's boundAlpha function
         * @description . to maintain consistency with how tiny_color works.
         * @param $n
         * @return float|int
         */
        protected function _tp_tinycolor_bound_alpha( $n ){
            if ( is_numeric( $n ) ) {
                $n = (float) $n;
                if ($n >= 0 && $n <= 1) return $n;
            }
            return 1;
        }//83
        /**
         * @description Rounds and converts values of an RGB object.
         * @param $rgb_color
         * @return array
         */
        protected function _tp_tinycolor_rgb_to_rgb( $rgb_color ): array{
            return array(
                'r' => $this->_tp_tinycolor_bound01( $rgb_color['r'], 255 ) * 255,
                'g' => $this->_tp_tinycolor_bound01( $rgb_color['g'], 255 ) * 255,
                'b' => $this->_tp_tinycolor_bound01( $rgb_color['b'], 255 ) * 255,
            );
        }//107
        /**
         * @description Helper function for hsl to rgb conversion.
         * @param $p
         * @param $q
         * @param $t
         * @return mixed
         */
        protected function _tp_tinycolor_hue_to_rgb( $p, $q, $t ){
            if ( $t < 0 ) ++ $t;
            if ( $t > 1 ) -- $t;
            if ( $t < 1 / 6 ) return $p + ( $q - $p ) * 6 * $t;
            if ( $t < 1 / 2 ) return $q;
            if ( $t < 2 / 3 ) return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
            return $p;
        }//131
        /**
         * @description Converts an HSL object to an RGB object with converted and rounded values.
         * @param $hsl_color
         * @return array
         */
        protected function _tp_tinycolor_hsl_to_rgb( $hsl_color ): array{
            $h = $this->_tp_tinycolor_bound01( $hsl_color['h'], 360 );
            $s = $this->_tp_tinycolor_bound01( $hsl_color['s'], 100 );
            $l = $this->_tp_tinycolor_bound01( $hsl_color['l'], 100 );
            if ( 0 === $s ) {
                // Achromatic.
                $r = $l;
                $g = $l;
                $b = $l;
            } else {
                $q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
                $p = 2 * $l - $q;
                $r = $this->_tp_tinycolor_hue_to_rgb( $p, $q, $h + 1 / 3 );
                $g = $this->_tp_tinycolor_hue_to_rgb( $p, $q, $h );
                $b = $this->_tp_tinycolor_hue_to_rgb( $p, $q, $h - 1 / 3 );
            }
            return ['r' => $r * 255,'g' => $g * 255,'b' => $b * 255,];
        }//164
        /**
         * @description Parses hex, hsl, and rgb CSS strings using the same regex as TinyColor v1.4.2 used in the JavaScript.
         * @description . Only colors output from react-color are implemented.
         * @param $color_str
         * @return array|null
         */
        protected function _tp_tinycolor_string_to_rgb( $color_str ): ?array{
            $color_str = strtolower( trim( $color_str ) );
            $css_integer = '[-\\+]?\\d+%?';
            $css_number  = '[-\\+]?\\d*\\.\\d+%?';
            $css_unit = '(?:' . $css_number . ')|(?:' . $css_integer . ')';
            $permissive_match3 = '[\\s|\\(]+(' . $css_unit . ')[,|\\s]+(' . $css_unit . ')[,|\\s]+(' . $css_unit . ')\\s*\\)?';
            $permissive_match4 = '[\\s|\\(]+(' . $css_unit . ')[,|\\s]+(' . $css_unit . ')[,|\\s]+(' . $css_unit . ')[,|\\s]+(' . $css_unit . ')\\s*\\)?';
            $rgb_regexp = '/^rgb' . $permissive_match3 . '$/';
            if ( preg_match( $rgb_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_rgb_to_rgb(['r' => $match[1],'g' => $match[2],'b' => $match[3],]);
                $rgb['a'] = 1;
                return $rgb;
            }
            $rgba_regexp = '/^rgba' . $permissive_match4 . '$/';
            if ( preg_match( $rgba_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_rgb_to_rgb(['r' => $match[1],'g' => $match[2],'b' => $match[3],]);
                $rgb['a'] = $this->_tp_tinycolor_bound_alpha( $match[4] );
                return $rgb;
            }
            $hsl_regexp = '/^hsl' . $permissive_match3 . '$/';
            if ( preg_match( $hsl_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_hsl_to_rgb(['h' => $match[1],'s' => $match[2],'l' => $match[3],]);
                $rgb['a'] = 1;
                return $rgb;
            }
            $hsla_regexp = '/^hsla' . $permissive_match4 . '$/';
            if ( preg_match( $hsla_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_hsl_to_rgb(['h' => $match[1],'s' => $match[2],'l' => $match[3],]);
                $rgb['a'] = $this->_tp_tinycolor_bound_alpha( $match[4] );
                return $rgb;
            }
            $hex8_regexp = '/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/';
            if ( preg_match( $hex8_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_rgb_to_rgb(
                    ['r' => base_convert( $match[1], 16, 10 ),'g' => base_convert( $match[2], 16, 10 ),'b' => base_convert( $match[3], 16, 10 ),]
                );
                $rgb['a'] = $this->_tp_tinycolor_bound_alpha( base_convert( $match[4], 16, 10 ) / 255 );
                return $rgb;
            }
            $hex6_regexp = '/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/';
            if ( preg_match( $hex6_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_rgb_to_rgb(
                    ['r' => base_convert( $match[1], 16, 10 ),'g' => base_convert( $match[2], 16, 10 ),'b' => base_convert( $match[3], 16, 10 ),]
                );
                $rgb['a'] = 1;
                return $rgb;
            }
            $hex4_regexp = '/^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/';
            if ( preg_match( $hex4_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_rgb_to_rgb(
                    ['r' => base_convert( $match[1] . $match[1], 16, 10 ),'g' => base_convert( $match[2] . $match[2], 16, 10 ),'b' => base_convert( $match[3] . $match[3], 16, 10 ),]
                );
                $rgb['a'] = $this->_tp_tinycolor_bound_alpha( base_convert( $match[4] . $match[4], 16, 10 ) / 255 );
                return $rgb;
            }
            $hex3_regexp = '/^#?([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/';
            if ( preg_match( $hex3_regexp, $color_str, $match ) ) {
                $rgb = $this->_tp_tinycolor_rgb_to_rgb(
                    ['r' => base_convert( $match[1] . $match[1], 16, 10 ), 'g' => base_convert( $match[2] . $match[2], 16, 10 ),'b' => base_convert( $match[3] . $match[3], 16, 10 ),]
                );
                $rgb['a'] = 1;
                return $rgb;
            }
            if ( 'transparent' === $color_str ) return ['r' => 0,'g' => 0,'b' => 0,'a' => 0,];
            return null;
        }//206
        /**
         * @description Returns the prefixed id for the duo_tone filter for use as a CSS id.
         * @param $preset
         * @return string
         */
        protected function _tp_get_duotone_filter_id( $preset ): string{
            if ( ! isset( $preset['slug'] ) ) return '';
            return 'tp_duotone-' . $preset['slug'];
        }//364
        /**
         * @description Returns the CSS filter property url to reference the rendered SVG.
         * @param $preset
         * @return string
         */
        protected function _tp_get_duotone_filter_property( $preset ): string{
            $filter_id = $this->_tp_get_duotone_filter_id( $preset );
            return "url('#" . $filter_id . "')";
        }//381
        /**
         * @description Returns the duotone filter SVG string for the preset.
         * @param $preset
         * @return mixed|string
         */
        protected function _tp_get_duotone_filter_svg( $preset ){
            $filter_id = $this->_tp_get_duotone_filter_id( $preset );
            $duotone_values = [ 'r' => [],'g' => [],'b' => [],'a' => [],];
            if ( ! isset( $preset['colors'] ) || ! is_array( $preset['colors'] ) )
                $preset['colors'] = array();
            foreach ( $preset['colors'] as $color_str ) {
                $color = $this->_tp_tinycolor_string_to_rgb( $color_str );
                $duotone_values['r'][] = $color['r'] / 255;
                $duotone_values['g'][] = $color['g'] / 255;
                $duotone_values['b'][] = $color['b'] / 255;
                $duotone_values['a'][] = $color['a'];
            }
            ob_start();
            ?>
            <!--suppress HtmlUnknownTag, CheckEmptyScriptTag -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 0 0"
                width="0"
                height="0"
                focusable="false"
                style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;"
            >
                <defs>
                    <filter id="<?php echo $this->_esc_attr( $filter_id ); ?>">
                        <feColorMatrix color-interpolation-filters='sRGB' type='matrix'
                            values='.299 .587 .114 0 0.299 .587 .114 0 0.299 .587 .114 0 0.299 .587 .114 0 0' />
                        <feComponentTransfer color-interpolation-filters="sRGB" >
                            <feFuncR type="table" tableValues="<?php echo $this->_esc_attr( implode( ' ', $duotone_values['r'] ) ); ?>" />
                            <feFuncG type="table" tableValues="<?php echo $this->_esc_attr( implode( ' ', $duotone_values['g'] ) ); ?>" />
                            <feFuncB type="table" tableValues="<?php echo $this->_esc_attr( implode( ' ', $duotone_values['b'] ) ); ?>" />
                            <feFuncA type="table" tableValues="<?php echo $this->_esc_attr( implode( ' ', $duotone_values['a'] ) ); ?>" />
                        </feComponentTransfer>
                        <feComposite in2="SourceGraphic" operator="in" />
                    </filter>
                </defs>
            </svg>
            <?php
            $svg = ob_get_clean();
            if ( ! defined( 'TP_SCRIPT_DEBUG' ) || ! TP_SCRIPT_DEBUG ) {
                $svg = preg_replace( "/[\r\n\t ]+/", ' ', $svg );
                $svg = preg_replace( '/> </', '><', $svg );
                $svg = trim( $svg );
            }
            return $svg;
        }//395
        /**
         * @description Registers the style and colors block attributes for block types that support it.
         * @param $block_type
         */
        protected function _tp_register_duotone_support( $block_type ): void{
            $has_duotone_support = false;
            if ( property_exists( $block_type, 'supports' ) )
                $has_duotone_support = $this->_tp_array_get( $block_type->supports, array( 'color', '__experimentalDuotone' ), false );
            if ( $has_duotone_support ) {
                if ( ! $block_type->attributes ) $block_type->attributes = [];
                if ( ! array_key_exists( 'style', $block_type->attributes ) )
                    $block_type->attributes['style'] = ['type' => 'object',];
            }
        }//476
    }
}else die;