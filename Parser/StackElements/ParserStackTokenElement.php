<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser\StackElements;

use CodingLiki\GrammarParser\Token\Token;
use CodingLiki\LALR1Parser\Parser\ParserStackElement;

class ParserStackTokenElement extends ParserStackElement
{

    public function __construct(private Token $leaf)
    {
        parent::__construct(ParserStackElement::TYPE_TOKEN);
    }

    public function getContent(): Token
    {
        return $this->leaf;
    }
}