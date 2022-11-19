<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Libs\Editor\TP_Editor;
//use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _general_template_08 {
        /**
         * @description Whether the user can access the visual editor.
         * @return mixed
         */
        protected function _user_can_rich_edit(){
            if ( ! isset( $this->__tp_rich_edit ) ) {
                $this->__tp_rich_edit = false;
                if ( 'true' === $this->_get_user_option( 'rich_editing' ) || ! $this->_is_user_logged_in() ) {
                    if ( $this->tp_is_safari )
                        $this->__tp_rich_edit = ! $this->_tp_is_mobile() || ( preg_match( '!AppleWebKit/(\d+)!', $_SERVER['HTTP_USER_AGENT'], $match ) && (int) $match[1] >= 534 );
                    elseif ( $this->tp_is_gecko || $this->tp_is_chrome || $this->tp_is_edge || ( $this->tp_is_opera && ! $this->_tp_is_mobile() ) )
                        $this->__tp_rich_edit = true;
                }
            }
            return $this->_apply_filters( 'user_can_rich_edit', $this->__tp_rich_edit );//todo
        }//3468 from general-template
        /**
         * @description Find out which editor should be displayed by default.
         * @return mixed
         */
        protected function _tp_default_editor(){
            $r = $this->_user_can_rich_edit() ? 'tinymce' : 'html'; // Defaults.
            if ( $this->_tp_get_current_user() ) {
                $ed = $this->_get_user_setting( 'editor', 'tinymce' );
                $r  = ( in_array( $ed, array( 'tinymce', 'html', 'test' ), true ) ) ? $ed : $r;
            }
            return $this->_apply_filters( 'tp_default_editor', $r );
        }//3505 from general-template
        /**
         * @description Renders an editor.
         * @param string $content
         * @param $editor_id
         * @param array ...$settings
         * @return string
         */
        protected function _tp_get_editor($content, $editor_id, ...$settings):string{
            return TP_Editor::get_editor($content,$editor_id,$settings);
        }//3543 from general-template
        protected function _tp_editor( $content, $editor_id, ...$settings):void{
            $this->_tp_get_editor( $content, $editor_id,$settings);
        }//3543 from general-template
        /**
         * @description Outputs the editor scripts, stylesheets, and default settings.
         * @return TP_Editor
         */
        protected function _tp_get_enqueue_editor():TP_Editor{
            return TP_Editor::enqueue_default_editor();
        }//3559 from general-template
        protected function _tp_enqueue_editor():void{
            $this->_tp_get_enqueue_editor();
        }//3559 from general-template
        /**
         * @description Enqueue assets needed by the code editor for the given settings.
         * @param array ...$args
         * @return bool|string
         */
        protected function _tp_get_enqueue_code_editor( ...$args ){
            if ( $this->_is_user_logged_in() && 'false' === $this->_tp_get_current_user()->syntax_highlighting ) {
                return false;}
            $settings = $this->_tp_get_code_editor_settings( $args );
            if ( empty( $settings ) || empty( $settings['codemirror'])){return false;}
            $this->tp_enqueue_script( 'code-editor' );
            $this->tp_enqueue_style( 'code-editor' );
            if ( isset( $settings['codemirror']['mode'] ) ) {
                $mode = $settings['codemirror']['mode'];
                if ( is_string( $mode ) ) { $mode = ['name' => $mode,];}
                if ( ! empty( $settings['codemirror']['lint'] ) ) {
                    switch ( $mode['name'] ) {
                        case 'css':
                        case 'text/css':
                        case 'text/x-scss':
                        case 'text/x-less':
                            $this->tp_enqueue_script( 'csslint' );
                            break;
                        case 'htmlmixed':
                        case 'text/html':
                        case 'php':
                        case 'application/x-httpd-php':
                        case 'text/x-php':
                            $this->tp_enqueue_script( 'htmlhint' );
                            $this->tp_enqueue_script( 'csslint' );
                            $this->tp_enqueue_script( 'jshint' );
                            if(!$this->_current_user_can('unfiltered_html')){ $this->tp_enqueue_script( 'htmlhint-kses' );}
                            break;
                        case 'javascript':
                        case 'application/ecmascript':
                        case 'application/json':
                        case 'application/javascript':
                        case 'application/ld+json':
                        case 'text/typescript':
                        case 'application/typescript':
                            $this->tp_enqueue_script( 'jshint' );
                            $this->tp_enqueue_script( 'jsonlint' );
                            break;
                    }
                }
            }
            $this->tp_add_inline_script( 'code-editor', sprintf( 'todo, %s );', $this->_tp_json_encode( $settings ) ) );
            $this->_do_action( 'tp_enqueue_code_editor', $settings );
            return $settings;
        }//3590 from general-template
        //todo @description Generate and return code editor settings.
        protected function _tp_get_code_editor_settings( ...$args ){
            $settings = array(
                'codemirror' => array(
                    'indentUnit'       => 4,
                    'indentWithTabs'   => true,
                    'inputStyle'       => 'contenteditable',
                    'lineNumbers'      => true,
                    'lineWrapping'     => true,
                    'styleActiveLine'  => true,
                    'continueComments' => true,
                    'extraKeys'        => array(
                        'Ctrl-Space' => 'autocomplete',
                        'Ctrl-/'     => 'toggleComment',
                        'Cmd-/'      => 'toggleComment',
                        'Alt-F'      => 'findPersistent',
                        'Ctrl-F'     => 'findPersistent',
                        'Cmd-F'      => 'findPersistent',
                    ),
                    'direction'        => 'ltr', // Code is shown in LTR even in RTL languages.
                    'gutters'          => array(),
                ),
                'csslint'    => array(
                    'errors'                    => true, // Parsing errors.
                    'box-model'                 => true,
                    'display-property-grouping' => true,
                    'duplicate-properties'      => true,
                    'known-properties'          => true,
                    'outline-none'              => true,
                ),
                'jshint'     => array(
                    // The following are copied from <https://github.com/WordPress/wordpress-develop/blob/4.8.1/.jshintrc>.
                    'boss'     => true,
                    'curly'    => true,
                    'eqeqeq'   => true,
                    'eqnull'   => true,
                    'es3'      => true,
                    'expr'     => true,
                    'immed'    => true,
                    'noarg'    => true,
                    'nonbsp'   => true,
                    'onevar'   => true,
                    'quotmark' => 'single',
                    'trailing' => true,
                    'undef'    => true,
                    'unused'   => true,
                    'browser'  => true,
                    'globals'  => array(
                        '_'        => false,
                        //'Backbone' => false, //todo don't want that
                        //'jQuery'   => false, //todo don't want that
                        'JSON'     => false,
                        'tp'       => false,
                    ),
                ),
                'htmlhint'   => array(
                    'tagname-lowercase'        => true,
                    'attr-lowercase'           => true,
                    'attr-value-double-quotes' => false,
                    'doctype-first'            => false,
                    'tag-pair'                 => true,
                    'spec-char-escape'         => true,
                    'id-unique'                => true,
                    'src-not-empty'            => true,
                    'attr-no-duplication'      => true,
                    'alt-require'              => true,
                    'space-tab-mixed-disabled' => 'tab',
                    'attr-unsafe-chars'        => true,
                ),
            );
            $type = '';
            if ( isset( $args['type'] ) ) {
                $type = $args['type'];
                if ( 'application/x-patch' === $type || 'text/x-patch' === $type ){ $type = 'text/x-diff';}
            } elseif ( isset( $args['file'] ) && false !== strpos( basename( $args['file'] ), '.' ) ) {
                $extension = strtolower( pathinfo( $args['file'], PATHINFO_EXTENSION ) );
                foreach ( $this->_tp_get_mime_types() as $exts => $mime ) {
                    if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
                        $type = $mime;
                        break;
                    }
                }
                if ( empty( $type ) ) {
                    switch ( $extension ) {
                        case 'conf':
                            $type = 'text/nginx';
                            break;
                        case 'css':
                            $type = 'text/css';
                            break;
                        case 'diff':
                        case 'patch':
                            $type = 'text/x-diff';
                            break;
                        case 'html':
                        case 'htm':
                            $type = 'text/html';
                            break;
                        case 'http':
                            $type = 'message/http';
                            break;
                        case 'js':
                            $type = 'text/javascript';
                            break;
                        case 'json':
                            $type = 'application/json';
                            break;
                        case 'jsx':
                            $type = 'text/jsx';
                            break;
                        case 'less':
                            $type = 'text/x-less';
                            break;
                        case 'md':
                            $type = 'text/x-gfm';
                            break;
                        case 'php':
                        case 'phtml':
                        //case 'php3':
                        //case 'php4':
                        //case 'php5':
                        case 'php7':
                        case 'phps':
                            $type = 'application/x-httpd-php';
                            break;
                        case 'scss':
                            $type = 'text/x-scss';
                            break;
                        case 'sass':
                            $type = 'text/x-sass';
                            break;
                        case 'sh':
                        case 'bash':
                            $type = 'text/x-sh';
                            break;
                        case 'sql':
                            $type = 'text/x-sql';
                            break;
                        case 'svg':
                            $type = 'application/svg+xml';
                            break;
                        case 'xml':
                            $type = 'text/xml';
                            break;
                        case 'yml':
                        case 'yaml':
                            $type = 'text/x-yaml';
                            break;
                        case 'txt':
                        default:
                            $type = 'text/plain';
                            break;
                    }
                }
            }

            if ( in_array( $type, array( 'text/css', 'text/x-scss', 'text/x-less', 'text/x-sass' ), true ) ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => $type,
                        'lint'              => false,
                        'autoCloseBrackets' => true,
                        'matchBrackets'     => true,
                    )
                );
            } elseif ( 'text/x-diff' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode' => 'diff',
                    )
                );
            } elseif ( 'text/html' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => 'htmlmixed',
                        'lint'              => true,
                        'autoCloseBrackets' => true,
                        'autoCloseTags'     => true,
                        'matchTags'         => array(
                            'bothTags' => true,
                        ),
                    )
                );

                if ( ! $this->_current_user_can( 'unfiltered_html' ) ) {
                    $settings['htmlhint']['kses'] = $this->_tp_kses_allowed_html( 'post' );
                }
            } elseif ( 'text/x-gfm' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'                => 'gfm',
                        'highlightFormatting' => true,
                    )
                );
            } elseif ( 'application/javascript' === $type || 'text/javascript' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => 'javascript',
                        'lint'              => true,
                        'autoCloseBrackets' => true,
                        'matchBrackets'     => true,
                    )
                );
            } elseif ( false !== strpos( $type, 'json' ) ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => array(
                            'name' => 'javascript',
                        ),
                        'lint'              => true,
                        'autoCloseBrackets' => true,
                        'matchBrackets'     => true,
                    )
                );
                if ( 'application/ld+json' === $type ) {
                    $settings['codemirror']['mode']['jsonld'] = true;
                } else {
                    $settings['codemirror']['mode']['json'] = true;
                }
            } elseif ( false !== strpos( $type, 'jsx' ) ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => 'jsx',
                        'autoCloseBrackets' => true,
                        'matchBrackets'     => true,
                    )
                );
            } elseif ( 'text/x-markdown' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'                => 'markdown',
                        'highlightFormatting' => true,
                    )
                );
            } elseif ( 'text/nginx' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode' => 'nginx',
                    )
                );
            } elseif ( 'application/x-httpd-php' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => 'php',
                        'autoCloseBrackets' => true,
                        'autoCloseTags'     => true,
                        'matchBrackets'     => true,
                        'matchTags'         => array(
                            'bothTags' => true,
                        ),
                    )
                );
            } elseif ( 'text/x-sql' === $type || 'text/x-mysql' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => 'sql',
                        'autoCloseBrackets' => true,
                        'matchBrackets'     => true,
                    )
                );
            } elseif ( false !== strpos( $type, 'xml' ) ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode'              => 'xml',
                        'autoCloseBrackets' => true,
                        'autoCloseTags'     => true,
                        'matchTags'         => array(
                            'bothTags' => true,
                        ),
                    )
                );
            } elseif ( 'text/x-yaml' === $type ) {
                $settings['codemirror'] = array_merge(
                    $settings['codemirror'],
                    array(
                        'mode' => 'yaml',
                    )
                );
            } else {
                $settings['codemirror']['mode'] = $type;
            }

            if ( ! empty( $settings['codemirror']['lint'] ) ) {
                $settings['codemirror']['gutters'][] = 'CodeMirror-lint-markers';
            }

            // Let settings supplied via args override any defaults.
            foreach ( $this->_tp_array_slice_assoc( $args, array( 'codemirror', 'csslint', 'jshint', 'htmlhint' ) ) as $key => $value ) {
                $settings[ $key ] = array_merge(
                    $settings[ $key ],
                    $value
                );
            }
            return $this->_apply_filters( 'tp_code_editor_settings', $settings, $args );

        }//3681 from general-template todo lots of edits
        /**
         * @description Retrieves the contents of the search TailoredPress query variable.
         * @param bool $escaped
         * @return mixed
         */
        protected function _get_search_query($escaped = true){
            $query = $this->_apply_filters( 'get_search_query', $this->_get_query_var( 's' ) );
            if ( $escaped ) $query = $this->_esc_attr( $query );
            return $query;
        }//4032 from general-template
        /**
         * @description Displays the contents of the search query variable.
         */
        public function the_search_query():void{
            echo $this->_esc_attr( $this->_apply_filters( 'the_search_query', $this->_get_search_query( false ) ) );
        }//4056 from general-template
        /**
         * @description Gets the language attributes for the 'html' tag.
         * @param string $doctype
         * @return mixed
         */
        protected function _get_language_attributes( $doctype = 'html' ){
            $attributes = [];
            if ( function_exists( 'is_rtl' ) && is_rtl() ) $attributes[] = 'dir="rtl"';
            $lang = $this->_get_bloginfo( 'language' );
            if ( $lang ) {
                if ('html'===$doctype || 'text/html' === $this->_get_option('html_type'))
                    $attributes[] = " lang='{$this->_esc_attr( $lang )}'";
            }
            $output_atts = implode( ' ', $attributes );
           return $this->_apply_filters( 'language_attributes', $output_atts, $doctype );
            //return ''; //todo

        }//4077 from general-template
        /**
         * @description  Displays the language attributes for the 'html' tag.
         * @param string $doctype
         */
        protected function _language_attributes( $doctype = 'html' ):void{
            echo $this->_get_language_attributes( $doctype );
        }//4120 from general-template
    }
}else die;