<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\TableReader;

class CsvTableReader implements TableReaderInterface
{

    public function read(string $contents): array
    {
        $table = str_getcsv($contents, "\n"); //parse the rows
        if(empty($table[0]) ){
            return [];
        }
        $header = str_getcsv(array_shift($table), ",");

        foreach ($table as &$Row) {
            $Row = array_combine($header, str_getcsv($Row, ","));
        }

        return $table;
    }
}