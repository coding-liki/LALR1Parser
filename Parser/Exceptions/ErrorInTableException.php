<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser\Exceptions;

use Exception;
use Throwable;

class ErrorInTableException extends Exception
{
    public function __construct(private string $tokenType, private string $tokenContent)
    {
        parent::__construct("Get Error from table while parsing");
    }

    /**
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * @return string
     */
    public function getTokenContent(): string
    {
        return $this->tokenContent;
    }
}