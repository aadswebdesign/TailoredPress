<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-4-2022
 * Time: 18:43
 */
namespace TP_Core\Libs\JSON;
if(ABSPATH){
    class TP_Theme_JSON_Schema extends JSON_Base {
        public const V1_TO_V2_RENAMED_PATHS = array(
            'border.customRadius' => 'border.radius', 'spacing.customMargin' => 'spacing.margin',
            'spacing.customPadding' => 'spacing.padding', 'typography.customLineHeight' => 'typography.lineHeight',
        );
        public static function migrate( $theme_json ) {
            if ( ! isset( $theme_json['version'] ) )
                $theme_json = ['version' => TP_Theme_JSON::LATEST_SCHEMA,];
            if ( 1 === $theme_json['version'] ) $theme_json = self::migrate_v1_to_v2( $theme_json );
            return $theme_json;
        }
        private static function migrate_v1_to_v2( $old ) {
            $new = $old;
            if ( isset( $old['settings'] ) )
                $new['settings'] = self::rename_paths( $old['settings'], self::V1_TO_V2_RENAMED_PATHS );
            $new['version'] = 2;
            return $new;
        }
        private static function rename_paths( $settings, $paths_to_rename ) {
            $new_settings = $settings;
            self::rename_settings( $new_settings, $paths_to_rename );
            if ( isset( $new_settings['blocks'] ) && is_array( $new_settings['blocks'] ) ) {
                foreach ( $new_settings['blocks'] as &$block_settings )
                    self::rename_settings( $block_settings, $paths_to_rename );
            }
            return $new_settings;
        }
        private static function rename_settings( &$settings, $paths_to_rename ): void{
            foreach ( $paths_to_rename as $original => $renamed ) {
                $original_path = explode( '.', $original );
                $renamed_path  = explode( '.', $renamed );
                $current_value = (new self)->_tp_array_get( $settings, $original_path, null );
                if ( null !== $current_value ) {
                    (new self)->_tp_array_set( $settings, $renamed_path, $current_value );
                    self::unset_setting_by_path( $settings, $original_path );
                }
            }
        }
        private static function unset_setting_by_path( &$settings, $path ): void {
            $tmp_settings = &$settings;
            $last_key     = array_pop( $path );
            foreach ( $path as $key ) $tmp_settings = &$tmp_settings[ $key ];
            unset( $tmp_settings[ $last_key ] );
        }
    }
}else die;