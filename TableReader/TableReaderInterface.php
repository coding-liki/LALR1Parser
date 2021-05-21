<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\TableReader;


interface TableReaderInterface
{
    public function read(string $contents): array;
}