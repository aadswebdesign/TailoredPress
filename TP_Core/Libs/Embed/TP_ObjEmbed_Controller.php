<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 23:33
 */
namespace TP_Core\Libs\Embed;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_ObjEmbed_Controller extends Embed_Base {
        public function register_routes():void {
            $maxwidth = $this->_apply_filters( 'oembed_default_width', 600 );
            $this->_register_rest_route('obj_embed/1.0','/embed',[[
                'methods' => TP_GET,
                'callback' => [$this, 'get_item' ],
                'permission_callback' => '__return_true',
                'args' =>[
                    'url' =>[
                        'description' => $this->__( 'The URL of the resource for which to fetch obj_Embed data.' ),
                        'required'    => true,
                        'type'        => 'string',
                        'format'      => 'uri',
                    ],
                    'format'   => ['default' => 'json','sanitize_callback' => 'tp_obj_embed_ensure_format',],
                    'maxwidth' => ['default'=> $maxwidth, 'sanitize_callback' => 'absint',],
                ]
            ]]);
            $this->_register_rest_route('obj_embed/1.0','/proxy',[[
                'methods' => TP_GET,
                'callback' => [$this, 'get_proxy_item' ],
                'permission_callback' => [$this, 'get_proxy_item_permissions_check'],
                'args' => [
                    'url' => [
                        'description' => $this->__( 'The URL of the resource for which to fetch obj_Embed data.' ),
                        'required' => true,'type' => 'string','format' => 'uri',
                    ],
                    'format' => [
                        'description' => $this->__( 'The obj_Embed format to use.' ),
                        'type' => 'string','default' => 'json','enum' => ['json','xml',],
                    ],
                    'maxwidth'  => [
                        'description' => $this->__( 'The maximum width of the embed frame in pixels.' ),
                        'type' => 'integer', 'default' => $maxwidth,'sanitize_callback' => 'absint',
                    ],
                    'maxheight' => [
                        'description' => $this->__( 'The maximum height of the embed frame in pixels.' ),
                        'type' => 'integer','sanitize_callback' => 'absint',
                    ],
                    'discover'  => [
                        'description' => $this->__( 'Whether to perform an obj_Embed discovery request for unsanctioned providers.' ),
                        'type' => 'boolean','default' => true,
                    ],
                ],
            ]]);
        }//24
        public function get_item( $request ){
            $post_id = $this->_url_to_postid( $request['url'] );
            $post_id = $this->_apply_filters( 'obj_embed_request_post_id', $post_id, $request['url'] );
            $data = $this->_get_obj_embed_response_data( $post_id, $request['maxwidth'] );
            if ( ! $data )
                return new TP_Error( 'obj_embed_invalid_url', $this->_get_status_header_desc( 404 ), array( 'status' => 404 ) );
            return $data;
        }//118
        public function get_proxy_item_permissions_check(){
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new TP_Error( 'rest_forbidden', $this->__( 'Sorry, you are not allowed to make proxied oEmbed requests.' ), array( 'status' => $this->_rest_authorization_required_code() ) );
            return true;
        }//147
        public function get_proxy_item(TP_REST_Request $request ){
            $tp_embed = $this->_init_embed();
            $tp_scripts = $this->_init_scripts();
            $args = $request->get_params();
            unset( $args['_tp_nonce'] );
            $cache_key = 'obj_embed_' . md5( serialize( $args ) );
            $data      = $this->_get_transient( $cache_key );
            if ( ! empty( $data ) ) return $data;
            $url = $request['url'];
            unset( $args['url'] );
            if (isset( $args['maxwidth'])) $args['width'] = $args['maxwidth'];
            if (isset( $args['maxheight'])) $args['height'] = $args['maxheight'];
            $data = $this->_get_obj_embed_response_data_for_url( $url, $args );
            if ( $data ) return $data;
            $_obj_embed = $this->_tp_obj_embed_get_object();
            $obj_embed = null;
            if($_obj_embed instanceof TP_ObjEmbed){
                $obj_embed = $_obj_embed;
            }
            $data = $obj_embed->get_data( $url, $args );//todo 1
            if ( false === $data ) {
                $html = $tp_embed->get_embed_handler_html( $args, $url );
                if ( $html ) {
                    $enqueued_scripts = [];
                    foreach ( $tp_scripts->queue as $script )
                        $enqueued_scripts[] = $tp_scripts->registered[ $script ]->src;
                    return (object) ['provider_name' => $this->__( 'Embed Handler' ),
                        'html' => $html,'scripts' => $enqueued_scripts,];
                }
                return new TP_Error( 'obj_embed_invalid_url', $this->_get_status_header_desc( 404 ), array( 'status' => 404 ) );
            }
            $data->html = $this->_apply_filters( 'obj_embed_result', $obj_embed->data2html( (object) $data, $url ), $url, $args );
            $ttl = $this->_apply_filters( 'rest_obj_embed_ttl', DAY_IN_SECONDS, $url, $args );
            $this->_set_transient( $cache_key, $data, $ttl );
            return $data;
        }//167
    }
}else die;