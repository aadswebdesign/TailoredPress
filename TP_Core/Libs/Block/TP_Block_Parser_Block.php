<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 15:34
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
if(ABSPATH){
    class TP_Block_Parser_Block{
        public $blockName;
        public $attrs;
        public $innerBlocks;
        public $innerHTML;
        public $innerContent;
        public function __construct( $name, $attrs, $innerBlocks, $innerHTML, $innerContent ) {
            $this->blockName    = $name;
            $this->attrs        = $attrs;
            $this->innerBlocks  = $innerBlocks;
            $this->innerHTML    = $innerHTML;
            $this->innerContent = $innerContent;
        }
    }
}else die;