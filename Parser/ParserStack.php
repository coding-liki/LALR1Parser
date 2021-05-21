<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser;

class ParserStack
{
    /** @var ParserStackElement[] */
    private array $stack = [];

    public function putElement(?ParserStackElement $element): self
    {
        if ($element !== null) {
            $this->stack[] = $element;
        }
        return $this;
    }


    public function popElement(): ?ParserStackElement
    {
        return array_pop($this->stack);
    }

    public function getElement(): ?ParserStackElement
    {
        return end($this->stack);
    }
}