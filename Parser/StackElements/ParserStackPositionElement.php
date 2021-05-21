<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser\StackElements;

use CodingLiki\LALR1Parser\Parser\ParserStackElement;

class ParserStackPositionElement extends ParserStackElement
{
    public function __construct(private int $position)
    {
        parent::__construct(ParserStackElement::TYPE_POSITION);
    }

    public function getContent(): int
    {
        return $this->position;
    }
}