<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser\Exceptions;

use Exception;
use Throwable;

class BadTokenException extends Exception
{
    public function __construct( private string $tokenType)
    {
        parent::__construct('Bad token type');
    }

    /**
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }
}