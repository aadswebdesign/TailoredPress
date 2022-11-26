<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-4-2022
 * Time: 15:06
 */
namespace TP_Core\Libs\SimplePie\Factory;
if(ABSPATH){
    trait _mm_vars{
        private $__char;
        private $__data;
        private $__default_options = array(
            'encode_entities' => false,
            'disable_html_ns' => false,
        );
        private $__eof;
        
		protected $_xpath;
        protected $_carry_on = true;
        protected $_current;
        protected $_doc;
        protected $_dom;
        protected $_encode = false;
        protected $_errors;
        protected $_events;
        protected $_frag;
        protected $_has_html_5 = false;
        protected $_implicit_namespaces;
        protected $_insert_mode = 0;
        protected $_mode;
        protected $_ns_roots = array(
            'html' => W3_XHTML,
            'svg' => W3_SVG,
            'math' => W3_MATHML,
        );
        protected $_ns_stack = [];
        protected $_non_boolean_attributes = array(
            array(
                'nodeNamespace' => 'http://www.w3.org/1999/xhtml',
                'attrName' => array('href',
                    'hreflang',
                    'http-equiv',
                    'icon',
                    'id',
                    'keytype',
                    'kind',
                    'label',
                    'lang',
                    'language',
                    'list',
                    'maxlength',
                    'media',
                    'method',
                    'name',
                    'placeholder',
                    'rel',
                    'rows',
                    'rowspan',
                    'sandbox',
                    'spellcheck',
                    'scope',
                    'seamless',
                    'shape',
                    'size',
                    'sizes',
                    'span',
                    'src',
                    'srcdoc',
                    'srclang',
                    'srcset',
                    'start',
                    'step',
                    'style',
                    'summary',
                    'tabindex',
                    'target',
                    'title',
                    'type',
                    'value',
                    'width',
                    'border',
                    'charset',
                    'cite',
                    'class',
                    'code',
                    'codebase',
                    'color',
                    'cols',
                    'colspan',
                    'content',
                    'coords',
                    'data',
                    'datetime',
                    'default',
                    'dir',
                    'dirname',
                    'enctype',
                    'for',
                    'form',
                    'formaction',
                    'headers',
                    'height',
                    'accept',
                    'accept-charset',
                    'accesskey',
                    'action',
                    'align',
                    'alt',
                    'bgcolor',
                ),
            ),
            array(
                'nodeNamespace' => 'http://www.w3.org/1999/xhtml',
                'xpath' => 'starts-with(local-name(), \'data-\')',
            ),
        );
        protected $_only_inline;
        protected $_options = [];
        protected $_out;
        protected $_output_mode;
        protected $_parent_current;
        protected $_processor;
        protected $_pushes = [];
        protected $_quirks = true;
        protected $_rules;
        protected $_scanner;
        protected $_stack = [];
        protected $_tok;
        protected $_text = '';
        protected $_text_mode = 0;
        protected $_traverser;
        protected $_until_tag;

        protected static $_local_ns = [
            'http://www.w3.org/1999/xhtml' => 'html',
            'http://www.w3.org/1998/Math/MathML' => 'math',
            'http://www.w3.org/2000/svg' => 'svg',
        ];

        public static $html5 = array(
            'a' => 1,
            'abbr' => 1,
            'address' => 65, // NORMAL | BLOCK_TAG
            'area' => 9, // NORMAL | VOID_TAG
            'article' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'aside' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'audio' => 1, // NORMAL
            'b' => 1,
            'base' => 9, // NORMAL | VOID_TAG
            'bdi' => 1,
            'bdo' => 1,
            'blockquote' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'body' => 1,
            'br' => 9, // NORMAL | VOID_TAG
            'button' => 1,
            'canvas' => 65, // NORMAL | BLOCK_TAG
            'caption' => 1,
            'cite' => 1,
            'code' => 1,
            'col' => 9, // NORMAL | VOID_TAG
            'colgroup' => 1,
            'command' => 9, // NORMAL | VOID_TAG
            // "data" => 1, // This is highly experimental and only part of the whatwg spec (not w3c). See https://developer.mozilla.org/en-US/docs/HTML/Element/data
            'datalist' => 1,
            'dd' => 65, // NORMAL | BLOCK_TAG
            'del' => 1,
            'details' => 17, // NORMAL | AUTOCLOSE_P,
            'dfn' => 1,
            'dialog' => 17, // NORMAL | AUTOCLOSE_P,
            'div' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'dl' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'dt' => 1,
            'em' => 1,
            'embed' => 9, // NORMAL | VOID_TAG
            'fieldset' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'figcaption' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'figure' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'footer' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'form' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'h1' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'h2' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'h3' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'h4' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'h5' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'h6' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'head' => 1,
            'header' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'hgroup' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'hr' => 73, // NORMAL | VOID_TAG
            'html' => 1,
            'i' => 1,
            'iframe' => 3, // NORMAL | TEXT_RAW
            'img' => 9, // NORMAL | VOID_TAG
            'input' => 9, // NORMAL | VOID_TAG
            'kbd' => 1,
            'ins' => 1,
            'keygen' => 9, // NORMAL | VOID_TAG
            'label' => 1,
            'legend' => 1,
            'li' => 1,
            'link' => 9, // NORMAL | VOID_TAG
            'map' => 1,
            'mark' => 1,
            'menu' => 17, // NORMAL | AUTOCLOSE_P,
            'meta' => 9, // NORMAL | VOID_TAG
            'meter' => 1,
            'nav' => 17, // NORMAL | AUTOCLOSE_P,
            'noscript' => 65, // NORMAL | BLOCK_TAG
            'object' => 1,
            'ol' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'optgroup' => 1,
            'option' => 1,
            'output' => 65, // NORMAL | BLOCK_TAG
            'p' => 209, // NORMAL | AUTOCLOSE_P | BLOCK_TAG | BLOCK_ONLY_INLINE
            'param' => 9, // NORMAL | VOID_TAG
            'pre' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'progress' => 1,
            'q' => 1,
            'rp' => 1,
            'rt' => 1,
            'ruby' => 1,
            's' => 1,
            'samp' => 1,
            'script' => 3, // NORMAL | TEXT_RAW
            'section' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'select' => 1,
            'small' => 1,
            'source' => 9, // NORMAL | VOID_TAG
            'span' => 1,
            'strong' => 1,
            'style' => 3, // NORMAL | TEXT_RAW
            'sub' => 1,
            'summary' => 17, // NORMAL | AUTOCLOSE_P,
            'sup' => 1,
            'table' => 65, // NORMAL | BLOCK_TAG
            'tbody' => 1,
            'td' => 1,
            'textarea' => 5, // NORMAL | TEXT_RCDATA
            'tfoot' => 65, // NORMAL | BLOCK_TAG
            'th' => 1,
            'thead' => 1,
            'time' => 1,
            'title' => 5, // NORMAL | TEXT_RCDATA
            'tr' => 1,
            'track' => 9, // NORMAL | VOID_TAG
            'u' => 1,
            'ul' => 81, // NORMAL | AUTOCLOSE_P | BLOCK_TAG
            'var' => 1,
            'video' => 65, // NORMAL | BLOCK_TAG
            'wbr' => 9, // NORMAL | VOID_TAG

            // Legacy?
            'basefont' => 8, // VOID_TAG
            'bgsound' => 8, // VOID_TAG
            'noframes' => 2, // RAW_TEXT
            'frame' => 9, // NORMAL | VOID_TAG
            'frameset' => 1,
            'center' => 16,
            'dir' => 16,
            'listing' => 16, // AUTOCLOSE_P
            'plaintext' => 48, // AUTOCLOSE_P | TEXT_PLAINTEXT
            'applet' => 0,
            'marquee' => 0,
            'isindex' => 8, // VOID_TAG
            'xmp' => 20, // AUTOCLOSE_P | VOID_TAG | RAW_TEXT
            'noembed' => 2, // RAW_TEXT
        );
        public static $mathml = array(
            'maction' => 1,
            'maligngroup' => 1,
            'malignmark' => 1,
            'math' => 1,
            'menclose' => 1,
            'merror' => 1,
            'mfenced' => 1,
            'mfrac' => 1,
            'mglyph' => 1,
            'mi' => 1,
            'mlabeledtr' => 1,
            'mlongdiv' => 1,
            'mmultiscripts' => 1,
            'mn' => 1,
            'mo' => 1,
            'mover' => 1,
            'mpadded' => 1,
            'mphantom' => 1,
            'mroot' => 1,
            'mrow' => 1,
            'ms' => 1,
            'mscarries' => 1,
            'mscarry' => 1,
            'msgroup' => 1,
            'msline' => 1,
            'mspace' => 1,
            'msqrt' => 1,
            'msrow' => 1,
            'mstack' => 1,
            'mstyle' => 1,
            'msub' => 1,
            'msup' => 1,
            'msubsup' => 1,
            'mtable' => 1,
            'mtd' => 1,
            'mtext' => 1,
            'mtr' => 1,
            'munder' => 1,
            'munderover' => 1,
        );
        public static $svg = array(
            'a' => 1,
            'altGlyph' => 1,
            'altGlyphDef' => 1,
            'altGlyphItem' => 1,
            'animate' => 1,
            'animateColor' => 1,
            'animateMotion' => 1,
            'animateTransform' => 1,
            'circle' => 1,
            'clipPath' => 1,
            'color-profile' => 1,
            'cursor' => 1,
            'defs' => 1,
            'desc' => 1,
            'ellipse' => 1,
            'feBlend' => 1,
            'feColorMatrix' => 1,
            'feComponentTransfer' => 1,
            'feComposite' => 1,
            'feConvolveMatrix' => 1,
            'feDiffuseLighting' => 1,
            'feDisplacementMap' => 1,
            'feDistantLight' => 1,
            'feFlood' => 1,
            'feFuncA' => 1,
            'feFuncB' => 1,
            'feFuncG' => 1,
            'feFuncR' => 1,
            'feGaussianBlur' => 1,
            'feImage' => 1,
            'feMerge' => 1,
            'feMergeNode' => 1,
            'feMorphology' => 1,
            'feOffset' => 1,
            'fePointLight' => 1,
            'feSpecularLighting' => 1,
            'feSpotLight' => 1,
            'feTile' => 1,
            'feTurbulence' => 1,
            'filter' => 1,
            'font' => 1,
            'font-face' => 1,
            'font-face-format' => 1,
            'font-face-name' => 1,
            'font-face-src' => 1,
            'font-face-uri' => 1,
            'foreignObject' => 1,
            'g' => 1,
            'glyph' => 1,
            'glyphRef' => 1,
            'hkern' => 1,
            'image' => 1,
            'line' => 1,
            'linearGradient' => 1,
            'marker' => 1,
            'mask' => 1,
            'metadata' => 1,
            'missing-glyph' => 1,
            'mpath' => 1,
            'path' => 1,
            'pattern' => 1,
            'polygon' => 1,
            'polyline' => 1,
            'radialGradient' => 1,
            'rect' => 1,
            'script' => 3, // NORMAL | RAW_TEXT
            'set' => 1,
            'stop' => 1,
            'style' => 3, // NORMAL | RAW_TEXT
            'svg' => 1,
            'switch' => 1,
            'symbol' => 1,
            'text' => 1,
            'textPath' => 1,
            'title' => 1,
            'tref' => 1,
            'tspan' => 1,
            'use' => 1,
            'view' => 1,
            'vkern' => 1,
        );
        public static $svgCaseSensitiveAttributeMap = array(
            'attributename' => 'attributeName',
            'attributetype' => 'attributeType',
            'basefrequency' => 'baseFrequency',
            'baseprofile' => 'baseProfile',
            'calcmode' => 'calcMode',
            'clippathunits' => 'clipPathUnits',
            'contentscripttype' => 'contentScriptType',
            'contentstyletype' => 'contentStyleType',
            'diffuseconstant' => 'diffuseConstant',
            'edgemode' => 'edgeMode',
            'externalresourcesrequired' => 'externalResourcesRequired',
            'filterres' => 'filterRes',
            'filterunits' => 'filterUnits',
            'glyphref' => 'glyphRef',
            'gradienttransform' => 'gradientTransform',
            'gradientunits' => 'gradientUnits',
            'kernelmatrix' => 'kernelMatrix',
            'kernelunitlength' => 'kernelUnitLength',
            'keypoints' => 'keyPoints',
            'keysplines' => 'keySplines',
            'keytimes' => 'keyTimes',
            'lengthadjust' => 'lengthAdjust',
            'limitingconeangle' => 'limitingConeAngle',
            'markerheight' => 'markerHeight',
            'markerunits' => 'markerUnits',
            'markerwidth' => 'markerWidth',
            'maskcontentunits' => 'maskContentUnits',
            'maskunits' => 'maskUnits',
            'numoctaves' => 'numOctaves',
            'pathlength' => 'pathLength',
            'patterncontentunits' => 'patternContentUnits',
            'patterntransform' => 'patternTransform',
            'patternunits' => 'patternUnits',
            'pointsatx' => 'pointsAtX',
            'pointsaty' => 'pointsAtY',
            'pointsatz' => 'pointsAtZ',
            'preservealpha' => 'preserveAlpha',
            'preserveaspectratio' => 'preserveAspectRatio',
            'primitiveunits' => 'primitiveUnits',
            'refx' => 'refX',
            'refy' => 'refY',
            'repeatcount' => 'repeatCount',
            'repeatdur' => 'repeatDur',
            'requiredextensions' => 'requiredExtensions',
            'requiredfeatures' => 'requiredFeatures',
            'specularconstant' => 'specularConstant',
            'specularexponent' => 'specularExponent',
            'spreadmethod' => 'spreadMethod',
            'startoffset' => 'startOffset',
            'stddeviation' => 'stdDeviation',
            'stitchtiles' => 'stitchTiles',
            'surfacescale' => 'surfaceScale',
            'systemlanguage' => 'systemLanguage',
            'tablevalues' => 'tableValues',
            'targetx' => 'targetX',
            'targety' => 'targetY',
            'textlength' => 'textLength',
            'viewbox' => 'viewBox',
            'viewtarget' => 'viewTarget',
            'xchannelselector' => 'xChannelSelector',
            'ychannelselector' => 'yChannelSelector',
            'zoomandpan' => 'zoomAndPan',
        );
        public static $svgCaseSensitiveElementMap = array(
            'altglyph' => 'altGlyph',
            'altglyphdef' => 'altGlyphDef',
            'altglyphitem' => 'altGlyphItem',
            'animatecolor' => 'animateColor',
            'animatemotion' => 'animateMotion',
            'animatetransform' => 'animateTransform',
            'clippath' => 'clipPath',
            'feblend' => 'feBlend',
            'fecolormatrix' => 'feColorMatrix',
            'fecomponenttransfer' => 'feComponentTransfer',
            'fecomposite' => 'feComposite',
            'feconvolvematrix' => 'feConvolveMatrix',
            'fediffuselighting' => 'feDiffuseLighting',
            'fedisplacementmap' => 'feDisplacementMap',
            'fedistantlight' => 'feDistantLight',
            'feflood' => 'feFlood',
            'fefunca' => 'feFuncA',
            'fefuncb' => 'feFuncB',
            'fefuncg' => 'feFuncG',
            'fefuncr' => 'feFuncR',
            'fegaussianblur' => 'feGaussianBlur',
            'feimage' => 'feImage',
            'femerge' => 'feMerge',
            'femergenode' => 'feMergeNode',
            'femorphology' => 'feMorphology',
            'feoffset' => 'feOffset',
            'fepointlight' => 'fePointLight',
            'fespecularlighting' => 'feSpecularLighting',
            'fespotlight' => 'feSpotLight',
            'fetile' => 'feTile',
            'feturbulence' => 'feTurbulence',
            'foreignobject' => 'foreignObject',
            'glyphref' => 'glyphRef',
            'lineargradient' => 'linearGradient',
            'radialgradient' => 'radialGradient',
            'textpath' => 'textPath',
        );
    }
}else die;