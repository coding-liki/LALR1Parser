<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser\StackElements;

use CodingLiki\GrammarParser\Token\Token;
use CodingLiki\LALR1Parser\AstTree\AstLeaf;
use CodingLiki\LALR1Parser\Parser\ParserStackElement;

class ParserStackLeafElement extends ParserStackElement
{

    public function __construct(private AstLeaf $leaf)
    {
        parent::__construct(ParserStackElement::TYPE_LEAF);
    }
    
    public function getContent(): AstLeaf
    {
        return $this->leaf;
    }
}