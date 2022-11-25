<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Edit_Site_Export_Controller extends TP_REST_Controller{
        public function __construct() {
            $this->_namespace = 'tp-block-editor/v1';
            $this->_rest_base = 'export';
        }
        public function register_routes():void {
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'export'],'permission_callback' => [$this, 'permissions_check'],],]
            );
        }
        public function permissions_check() {
            if ( ! $this->_current_user_can( 'edit_theme_options' ) )
                return new TP_Error('rest_cannot_export_templates',
                    $this->__( 'Sorry, you are not allowed to export templates and template parts.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }
        public function export() {
            $filename = $this->_tp_generate_block_templates_export_file();
            if ($filename instanceof TP_Error && $this->_init_error( $filename ) ) {
                $filename->add_data( ['status' => INTERNAL_SERVER_ERROR] );
                return $filename;
            }
            header( 'Content-Type: application/zip' );
            header( 'Content-Disposition: attachment; filename=edit-site-export.zip' );
            header( 'Content-Length: ' . filesize( $filename ) );
            flush();
            readfile( $filename );
            unlink( $filename );
            exit;
        }// Generate the export file.
    }
}else die;