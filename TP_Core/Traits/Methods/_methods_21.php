<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _methods_21{
        // todo 3x from _methods_20 to be moved here
        protected function _tp_loading_dynamic_classes($namespace,$class_dir, ...$args):string{
            $located = '';
            foreach ( (array) $class_dir as $class ) {
                if ( ! $class ) continue;
                $class_name = $namespace.$class;
                if(class_exists($class_name)){
                    $located = new $class_name($args);
                }
            }
            return $located;
        }//added method
        /**
         * @param $class_name
         * @param $namespace
         * @param $class
         * @param array|null $class_args
         * @return mixed
         */
        protected function _tp_load_class($class_name,$namespace,$class, $class_args = null){
            $located = '';
            if(!isset($class_name,$namespace)){
                return new TP_Error($this->__('You have to provide a class_name and namespace, to have your classes dynamically loaded!'));
            }
            $class_name = $namespace.$class;
            if(class_exists($class_name)){ $located = new $class_name($class_args);}
            return $located;
        }//added method
        //@description
        protected function _tp_unserialize($serializedData, ...$classes){
            if (PHP_VERSION_ID >= 70000) {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $unserializedData = unserialize($serializedData, ['allowed_classes' => [$classes]]);
            } else {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $unserializedData = unserialize($serializedData, $classes=[]);
            }
            return $unserializedData;
        }//
        protected function _tp_str_contains($haystack, $needle):string{
            return (strpos($haystack, $needle) !== false);
        }//added php8
        protected function _tp_call_user_func_array($func, $args){
            $_func = $func ?: static function(){};
            return call_user_func_array($_func, $args);
        }//added
        //@description
        protected function _get_php_console_log($id, $log):string{
            $output  = "";
            if($id !== null && $log !== null){
                ob_start();
                ?>
                <script id='log_{$id}'>console.log('<?php echo $id ?> :','<?php echo $log ?>')</script>
                <?php
                $output .= ob_get_clean();
            }
            return $output;
        }//
        //@description
        protected function _php_console_log($id, $log):void{
            echo $this->_get_php_console_log($id, $log);
        }//
        protected function _tp_array_replace_recursive($data, $new_data):array{
            return array_replace_recursive($data, $new_data);
        }//added
        /**
         * @description  Gets a basename path. might not be needed as I use classes and methods
         * @param $file
         * @return mixed|string
         */
        protected function _tp_get_basename_path( $file ){
            $file = $this->_tp_normalize_path( $file );
            arsort( $this->tp_base_paths );
            foreach ( $this->tp_base_paths as $dir => $real_dir ) {
                if ( strpos( $file, $real_dir ) === 0 ) {
                    $file = $dir . substr( $file, strlen( $real_dir ) );
                }
            }
            $base_dir    = $this->_tp_normalize_path( TP_CONTENT_LIBS );
            $mu_base_dir = $this->_tp_normalize_path( TP_MU_CONTENT_LIBS );
            $file = preg_replace( '#^' . preg_quote( $base_dir, '#' ) . '/|^' . preg_quote( $mu_base_dir, '#' ) . '/#', '', $file );
            $file = trim( $file, '/' );
            return $file;
        }//710 from plugin.php
        /**
         * @param $hook
         * @param $callback
         * @param null $hook_suffix
         * @param null $args
         * @return mixed
         */
        public function add_asset($hook,$callback,$hook_suffix = null,$args = null){
            $hook_suffix = $hook_suffix ?: null;
            $hook_complete = null;
            if($hook_suffix !== ''){ $hook_complete = "$hook-{$hook_suffix}";}
            else{ $hook_complete = $hook;}
            if($callback === null){
                return false;
            }
            return  $this->_apply_filters($hook_complete,$callback,$args );
        }//added
        /**
         * $class_name
         * @param $class_name
         * @param array|null $class_args
         * @return bool|object
         */
        public function get_module_class($class_name,$class_args = null){
            if($class_name === null){
                new TP_Error('Please provide a Class Name, make sure your class is namespaced!');
                return false;
            }
            return $this->_tp_load_class($class_name,TP_NS_MODULES,$class_name,$class_args);
        }//added
    }
}else die;