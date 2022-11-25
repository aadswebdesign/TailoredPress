<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-5-2022
 * Time: 14:31
 */
namespace TP_Core\Libs\RestApi;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Libs\HTTP\TP_HTTP_Response;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Response extends TP_HTTP_Response{
        use _methods_11;
        use _filter_01;
        protected $_links = [];
        protected $_matched_route = '';
        protected $_matched_handler;
        public function add_link(string $rel,string $href, array $attributes = [] ): void{
            if ( empty( $this->_links[ $rel ])) $this->_links[ $rel ] = [];
            if ( isset( $attributes['href'])) unset( $attributes['href'] );
            $this->_links[ $rel ][] = ['href' => $href,'attributes' => $attributes,];
        }//58
        public function remove_link( string $rel, string $href = null ): void{
            if ( ! isset( $this->_links[ $rel ] ) ) return;
            if ( $href ) $this->_links[ $rel ] = $this->_tp_list_filter( $this->_links[ $rel ], array( 'href' => $href ), 'NOT' );
            else $this->_links[ $rel ] = [];
            if ( ! $this->_links[ $rel ] ) unset($this->_links[$rel]);
        }//83
        public function add_links(array $links ): void{
            foreach ( $links as $rel => $set ) {
                if ( isset( $set['href'] ) ) $set = [$set];
                foreach ( $set as $attributes ) $this->add_link( $rel, $attributes['href'], $attributes );
            }
        }//111
        public function get_links(): array{
            return $this->_links;
        }//131
        public function link_header( $rel, $link, $other = array() ): void{
            $header = "<link {$link} rel='{$rel}'>";
            foreach ( $other as $key => $value ) {
                if ( 'title' === $key ) $value = "'{$value}'";
                $header .= " {$key}='{$value}'";
            }
            $this->header( 'Link', $header, false );
        }//150
        public function get_matched_route(): string{
            return $this->_matched_route;
        }//170
        public function set_matched_route( $route ): void{
            $this->_matched_route = $route;
        }//181
        public function get_matched_handler() {
            return $this->_matched_handler;
        }//192
        public function set_matched_handler( $handler ): void{
            $this->_matched_handler = $handler;
        }//203
        public function is_error(): bool{
            return $this->get_status() >= 400;
        }//214
        public function as_error() {
            if ( ! $this->is_error() ) return null;
            $error = new TP_Error;
            if ( is_array( $this->get_data() ) ) {
                $data = $this->get_data();
                $error->add( $data['code'], $data['message'], $data['data'] );
                if ( ! empty( $data['additional_errors'] ) ) {
                    foreach ( $data['additional_errors'] as $err )
                        $error->add( $err['code'], $err['message'], $err['data'] );
                }
            } else $error->add( $this->get_status(), '', array( 'status' => $this->get_status() ) );
            return $error;
        }
        public function get_curies() {
            $curies = [['name'=> 'tp','href'=> 'https://api.w.org/{rel}','templated' => true,]];
            $additional = $this->_apply_filters( 'rest_response_link_curies', array() );
            return array_merge( $curies, $additional );
        }
    }
}else die;