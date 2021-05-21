<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser;

abstract class ParserStackElement
{
    public const TYPE_POSITION = 0;
    public const TYPE_TOKEN = 1;
    public const TYPE_LEAF = 2;

    public function __construct(private int $type)
    {
    }

    /** @return mixed */
    abstract public function getContent(): mixed;

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}