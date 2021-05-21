<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\AstTree;

use CodingLiki\GrammarParser\Token\Token;

class AstLeaf
{
    /** @var array<AstLeaf|Token> */
    private array $children = [];


    public function __construct(private string $name)
    {
    }

    public function addChildLeaf(AstLeaf $child): self
    {
        $this->children[] = $child;

        return $this;
    }


    public function addChildToken(Token $child): self
    {
        $this->children[] = $child;

        return $this;
    }

    public function unshiftChildLeaf(AstLeaf $child): self
    {
        array_unshift($this->children, $child);

        return $this;
    }


    public function unshiftChildToken(Token $child): self
    {
        array_unshift($this->children, $child);

        return $this;
    }

    /**
     * @return array<AstLeaf|Token>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Token[]|AstLeaf[] $children
     * @return AstLeaf
     */
    public function setChildren(array $children): AstLeaf
    {
        $this->children = $children;
        return $this;
    }
}