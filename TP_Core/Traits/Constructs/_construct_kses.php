<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_kses{
        public
            $allowed_tags,
            $allowed_post_tags,
            $allowed_entity_names,
            $allowed_xml_entity_names;
        public $pass_allowed_html,$pass_allowed_protocols;
        protected function _construct_kses():void{
            if ( ! KSES_CUSTOM_TAGS ) {
                $this->allowed_tags = [
                    'a'          => ['href' => true,'title' => true,],
                    'abbr'       => ['title' => true,],
                    'acronym'    => ['title' => true,],
                    'b'          => [],
                    'blockquote' => ['cite' => true,],
                    'cite'       => [],
                    'code'       => [],
                    'del'        => ['datetime' => true,],
                    'em'         => [],
                    'i'          => [],
                    'q'          => ['cite' => true,],
                    's'          => [],
                    'strike'     => [],
                    'strong'     => [],
                ];
                $this->allowed_post_tags = [
                    'address'    => [],
                    'anchor'     => [
                        'href' => true,'rel' => true,'rev' => true,'name' => true,
                        'target' => true,'download' => ['valueless' => 'y',],
                    ],
                    'abbr'       => [],
                    'acronym'    => [],
                    'area'       => [
                        'alt' => true,'coords' => true,'href' => true,'nohref' => true,
                        'shape' => true,'target' => true,
                    ],
                    'article'    => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'aside'      => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'audio'      => [
                        'autoplay' => true,'controls' => true,'loop' => true,'muted' => true,
                        'preload' => true,'src' => true,
                    ],
                    'b' => [],
                    'blockquote' => ['cite' => true,'lang' => true,'xml:lang' => true,],
                    'br'         => [],
                    'button'     => ['disabled' => true,'name' => true,'type' => true,'value' => true,],
                    'caption'    => ['align' => true,],
                    'cite'       => ['dir' => true,'lang' => true,],
                    'code'       => [],
                    'col'        => [
                        'align' => true,'char' => true,'charoff' => true,'span' => true,
                        'dir' => true,'valign' => true,'width' => true,
                    ],
                    'colgroup'   => [
                        'align' => true,'char' => true,'charoff' => true,'span' => true,
                        'valign' => true,'width' => true,
                    ],
                    'del'        => ['datetime' => true,],
                    'dd'         => [],
                    'dfn'        => [],
                    'details'    => [
                        'align' => true,'dir' => true,'lang' => true,'open' => true,
                        'xml:lang' => true,
                    ],
                    'div'        => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'dl'         => [],
                    'dt'         => [],
                    'em'         => [],
                    'fieldset'   => [],
                    'figure'     => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'figcaption' => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'font'       => ['color' => true,'face' => true,'size' => true,],
                    'footer'     => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'h1'         => ['align' => true,],
                    'h2'         => ['align' => true,],
                    'h3'         => ['align' => true,],
                    'h4'         => ['align' => true,],
                    'h5'         => ['align' => true,],
                    'h6'         => ['align' => true,],
                    'header'     => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'hgroup'     => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'hr'         => ['align' => true,'noshade' => true,'size' => true,'width' => true,],
                    'i'          => [],
                    'img'        => [
                        'alt' => true,'align' => true,'border' => true,'height' => true,
                        'hspace' => true,'loading' => true,'longdesc' => true,'vspace' => true,
                        'src' => true,'usemap' => true,'width' => true,
                    ],
                    'ins'        => ['datetime' => true,'cite' => true,],
                    'kbd'        => [],
                    'label'      => ['for' => true,],
                    'legend'     => ['align' => true,],
                    'li'         => ['align' => true,'value' => true,],
                    'main'       => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'map'        => ['name' => true,],
                    'mark'       => [],
                    'menu'       => ['type' => true,],
                    'nav'        => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'object'     => [
                        'data' => ['required' => true,'value_callback' => '_tp_kses_allow_pdf_objects',],
                        'type' => ['required' => true,'values' => ['application/pdf'],],
                    ],
                    'p'          => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'pre'        => ['width' => true,],
                    'q'          => ['cite' => true,],
                    's'          => [],
                    'samp'       => [],
                    'span'       => ['dir' => true,'align' => true,'lang' => true,'xml:lang' => true,],
                    'section'    => ['align'=> true,'dir'=> true,'lang'=> true,'xml:lang' => true,],
                    'small'      => [],
                    'strike'     => [],
                    'strong'     => [],
                    'sub'        => [],
                    'summary'    => ['align' => true,'dir' => true,'lang' => true,'xml:lang' => true,],
                    'sup'        => [],
                    'table'      => [
                        'align' => true,'bgcolor' => true,'border' => true,'cellpadding' => true,
                        'cellspacing' => true,'dir' => true,'rules' => true,'summary' => true,
                        'width' => true,
                    ],
                    'tbody'      => ['align' => true,'char' => true,'charoff' => true,'valign' => true,],
                    'td'         => [
                        'abbr' => true,'align' => true,'axis' => true,'bgcolor' => true,
                        'char' => true,'charoff' => true,'colspan' => true,'dir' => true,
                        'headers' => true,'height' => true,'nowrap' => true,'rowspan' => true,
                        'scope' => true,'valign' => true,'width' => true,
                    ],
                    'textarea'   => [
                        'cols' => true,'rows' => true,'disabled' => true,'name' => true,
                        'readonly' => true,
                    ],
                    'tfoot'      => ['align' => true,'char' => true,'charoff' => true,'valign' => true,],
                    'th'         => [
                        'abbr' => true,'align' => true,'axis' => true,'bgcolor' => true,
                        'char' => true,'charoff' => true,'colspan' => true,'headers' => true,
                        'height' => true,'nowrap' => true,'rowspan' => true,'scope' => true,
                        'valign' => true,'width' => true,
                    ],
                    'thead'      => ['align' => true,'char' => true,'charoff' => true,'valign' => true,],
                    'title'      => [],
                    'tr'         => [
                        'align' => true,'bgcolor' => true,'char' => true,'charoff' => true,
                        'valign' => true,
                    ],
                    'track'      => [
                        'default' => true,'kind' => true,'label' => true,'src' => true,
                        'srclang' => true,
                    ],
                    'tt'         => [],
                    'u'          => [],
                    'ul'         => ['type' => true,],
                    'ol'         => ['start'=> true,'type'=> true,'reversed' => true,],
                    'var'        => [],
                    'video'      => [
                        'autoplay'=> true,'controls'=> true,'height' => true,'loop' => true,
                        'muted' => true,'playsinline' => true,'poster' => true,'preload' => true,
                        'src' => true,'width' => true,
                    ],
                ];
                $this->allowed_entity_names =[
                    'nbsp','iexcl','cent','pound','curren','yen','brvbar','sect','uml','copy',
                    'ordf','laquo','not','shy','reg','macr','deg','plusmn','acute','micro',
                    'para','middot','cedil','ordm','raquo','iquest','Agrave','Aacute','Acirc','Atilde',
                    'Auml','Aring','AElig','Ccedil','Egrave','Eacute','Ecirc','Euml','Igrave','Iacute',
                    'Icirc','Iuml','ETH','Ntilde','Ograve','Oacute','Ocirc','Otilde','Ouml','times',
                    'Oslash','Ugrave','Uacute','Ucirc','Uuml','Yacute','THORN','szlig','agrave','aacute',
                    'acirc','atilde','auml','aring','aelig','ccedil','egrave','eacute','ecirc','euml',
                    'igrave','iacute','icirc','iuml','eth','ntilde','ograve','oacute','ocirc','otilde',
                    'ouml','divide','oslash','ugrave','uacute','ucirc','uuml','yacute','thorn','yuml',
                    'quot','amp','lt','gt','apos','OElig','oelig','Scaron','scaron','Yuml',
                    'circ','tilde','ensp','emsp','thinsp','zwnj','zwj','lrm','rlm','ndash',
                    'mdash','lsquo','rsquo','sbquo','ldquo','rdquo','bdquo','dagger','Dagger','permil',
                    'lsaquo','rsaquo','euro','fnof','Alpha','Beta','Gamma','Delta','Epsilon','Zeta',
                    'Eta','Theta','Iota','Kappa','Lambda','Mu','Nu','Xi','Omicron','Pi',
                    'Rho','Sigma','Tau','Upsilon','Phi','Chi','Psi','Omega','alpha','beta',
                    'gamma','delta','epsilon','zeta','eta','theta','iota','kappa','lambda','mu',
                    'nu','xi','omicron','pi','rho','sigmaf','sigma','tau','upsilon','phi',
                    'chi','psi','omega','thetasym','upsih','piv','bull','hellip','prime','Prime',
                    'oline','frasl','weierp','image','real','trade','alefsym','larr','uarr','rarr',
                    'darr','harr','crarr','lArr','uArr','rArr','dArr','hArr','forall','part',
                    'exist','empty','nabla','isin','notin','ni','prod','sum','minus','lowast',
                    'radic','prop','infin','ang','and','or','cap','cup','int','sim',
                    'cong','asymp','ne','equiv','le','ge','sub','sup','nsub','sube',
                    'supe','oplus','otimes','perp','sdot','lceil','rceil','lfloor','rfloor','lang',
                    'rang','loz','spades','clubs','hearts','diams','sup1','sup2','sup3','frac14',
                    'frac12','frac34','there4',
                ];
                $this->allowed_xml_entity_names = ['amp','lt','gt','apos','quot',];
                $this->allowed_post_tags = array_map([$this,'_tp_add_global_attributes'], $this->allowed_post_tags);
            }else{
                $this->allowed_tags   = $this->_tp_kses_array_lc( $this->allowed_tags );
                $this->allowed_post_tags = $this->_tp_kses_array_lc( $this->allowed_post_tags );
            }
        }

        //protected $_;
        //protected $_;
        //protected $_;



    }
}else{die;}