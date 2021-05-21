<?php
declare(strict_types=1);

namespace  CodingLiki\LALR1Parser\Parser;

use CodingLiki\GrammarParser\Token\Token;
use CodingLiki\LALR1Parser\AstTree\AstTree;

interface LALR1ParserInterface
{

    /**
     * @param Token[] $tokenChain
     * @return AstTree
     */
    public function parse(array $tokenChain): AstTree;
}