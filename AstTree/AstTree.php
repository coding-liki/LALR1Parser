<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\AstTree;

class AstTree
{

    /** @var AstLeaf[] */
    private array $children = [];

    public function addChild(AstLeaf $leaf): self
    {
        $this->children[] = $leaf;
        return $this;
    }

    /**
     * @return AstLeaf[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}