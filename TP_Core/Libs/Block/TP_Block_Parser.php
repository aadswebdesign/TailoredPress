<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 15:35
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
if(ABSPATH){
    class TP_Block_Parser{
        public $document;
        public $offset;
        public $output;
        public $stack;
        public $empty_attrs;
        public function parse( $document ): array{
            $this->document    = $document;
            $this->offset      = 0;
            $this->output      = [];
            $this->stack       = [];
            $this->empty_attrs = json_decode( '{}', true );
            //do {} while ( $this->proceed() );
            return $this->output;
        }
        public function proceed(): ?bool{
            @list( $token_type, $block_name, $attrs, $start_offset, $token_length ) = $this->next_token();
            $stack_depth = count( $this->stack );
            $leading_html_start = $start_offset > $this->offset ? $this->offset : null;
            switch ( $token_type ) {
                case 'no-more-tokens':
                    // if not in a block then flush output.
                    if ( 0 === $stack_depth ) {
                        $this->add_freeform();
                        return false;
                    }
                    if ( 1 === $stack_depth ) {
                        $this->add_block_from_stack();
                        return false;
                    }
                    while ( 0 < count( $this->stack ) ) $this->add_block_from_stack();
                    return false;
                case 'void-block':
                    if ( 0 === $stack_depth ) {
                        if ( isset( $leading_html_start ) ) {
                            $this->output[] = (array) $this->freeform(
                                substr(
                                    $this->document,
                                    $leading_html_start,
                                    $start_offset - $leading_html_start
                                )
                            );
                        }
                        $this->output[] = (array) new TP_Block_Parser_Block( $block_name, $attrs, [], '', [] );
                        $this->offset   = $start_offset + $token_length;
                        return true;
                    }
                    $this->add_inner_block(
                        new TP_Block_Parser_Block( $block_name, $attrs, [], '', [] ),
                        $start_offset,
                        $token_length
                    );
                    $this->offset = $start_offset + $token_length;
                    return true;
                case 'block-opener':
                    $this->stack = new TP_Block_Parser_Frame(
                        new TP_Block_Parser_Block( $block_name, $attrs, [], '', [] ),
                        $start_offset,
                        $token_length,
                        $start_offset + $token_length,
                        $leading_html_start
                    );
                    $this->offset = $start_offset + $token_length;
                    return true;
                case 'block-closer':
                    if ( 0 === $stack_depth ) {
                         $this->add_freeform();
                        return false;
                    }
                    if ( 1 === $stack_depth ) {
                        $this->add_block_from_stack( $start_offset );
                        $this->offset = $start_offset + $token_length;
                        return true;
                    }
                    $stack_top                        = array_pop( $this->stack );
                    $html                             = substr( $this->document, $stack_top->prev_offset, $start_offset - $stack_top->prev_offset );
                    $stack_top->block->innerHTML     .= $html;
                    $stack_top->block->innerContent[] = $html;
                    $stack_top->prev_offset           = $start_offset + $token_length;
                    $this->add_inner_block(
                        $stack_top->block,
                        $stack_top->token_start,
                        $stack_top->token_length,
                        $start_offset + $token_length
                    );
                    $this->offset = $start_offset + $token_length;
                    return true;
                default:
                    $this->add_freeform();
                    return false;
            }
        }
        public function next_token(): array{
            $matches = null;
            $has_match = preg_match(
                '/<!--\s+(?P<closer>\/)?wp:(?P<namespace>[a-z][a-z0-9_-]*\/)?(?P<name>[a-z][a-z0-9_-]*)\s+(?P<attrs>{(?:(?:[^}]+|}+(?=})|(?!}\s+\/?-->).)*+)?}\s+)?(?P<void>\/)?-->/s',
                $this->document,$matches, PREG_OFFSET_CAPTURE,$this->offset);
            if ( false === $has_match ) return array( 'no-more-tokens', null, null, null, null );
            if ( 0 === $has_match ) return array( 'no-more-tokens', null, null, null, null );
            @list( $match, $started_at ) = $matches[0];
            $length    = strlen( $match );
            $is_closer = isset( $matches['closer'] ) && -1 !== $matches['closer'][1];
            $is_void   = isset( $matches['void'] ) && -1 !== $matches['void'][1];
            $namespace = $matches['namespace'];
            $namespace = ( isset( $namespace ) && -1 !== $namespace[1] ) ? $namespace[0] : 'core/';
            $name      = $namespace . $matches['name'][0];
            $has_attrs = isset( $matches['attrs'] ) && -1 !== $matches['attrs'][1];
            $attrs = $has_attrs ? json_decode( $matches['attrs'][0], true ): $this->empty_attrs;
            if ( $is_closer && ( $is_void || $has_attrs ) ){}
            if ( $is_void ) return array( 'void-block', $name, $attrs, $started_at, $length );
            if ( $is_closer ) return array( 'block-closer', $name, null, $started_at, $length );
            return array( 'block-opener', $name, $attrs, $started_at, $length );
        }
        public function freeform( $innerHTML ): TP_Block_Parser_Block{
            return new TP_Block_Parser_Block( null, $this->empty_attrs, array(), $innerHTML, array( $innerHTML ) );
        }
        public function add_freeform( $length = null ): void{
            $length = $length ?: strlen( $this->document ) - $this->offset;
            if ( 0 === $length ) return;
            $this->output[] = (array) $this->freeform( substr( $this->document, $this->offset, $length ) );
        }
        public function add_inner_block( TP_Block_Parser_Block $block, $token_start, $token_length, $last_offset = null ): void{
            $parent                       = $this->stack[ count( $this->stack ) - 1 ];
            $parent->block->innerBlocks[] = (array) $block;
            $html                         = substr( $this->document, $parent->prev_offset, $token_start - $parent->prev_offset );

            if ( ! empty( $html ) ) {
                $parent->block->innerHTML     .= $html;
                $parent->block->innerContent[] = $html;
            }

            $parent->block->innerContent[] = null;
            $parent->prev_offset           = $last_offset ?: $token_start + $token_length;
        }
        public function add_block_from_stack( $end_offset = null ): void{
            $stack_top   = array_pop( $this->stack );
            $prev_offset = $stack_top->prev_offset;
            $html = isset( $end_offset ) ? substr( $this->document, $prev_offset, $end_offset - $prev_offset ) : substr( $this->document, $prev_offset );
            if ( ! empty( $html ) ) {
                $stack_top->block->innerHTML     .= $html;
                $stack_top->block->innerContent[] = $html;
            }
            if ( isset( $stack_top->leading_html_start ) )
                $this->output[] = (array) $this->freeform( substr( $this->document,$stack_top->leading_html_start,$stack_top->token_start - $stack_top->leading_html_start));
            $this->output[] = (array) $stack_top->block;
        }
    }
}else die;