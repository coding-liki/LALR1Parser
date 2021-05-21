<?php
declare(strict_types=1);

namespace unit\Parser;


use Codeception\Test\Unit;
use CodingLiki\GrammarParser\GrammarRuleParser;
use CodingLiki\GrammarParser\Token\Token;
use CodingLiki\LALR1Parser\AstTree\AstLeaf;
use CodingLiki\LALR1Parser\AstTree\AstTree;
use CodingLiki\LALR1Parser\Parser\LALR1Parser;
use CodingLiki\LALR1Parser\TableReader\CsvTableReader;

class LALR1ParserTest extends Unit
{

    /**
     * @dataProvider parseProvider
     *
     * @param array $tokens
     * @param string $rules
     * @param string $tableString
     * @param AstTree $astTree
     */
    public function testParse(array $tokens, string $rules, string $tableString, AstTree $astTree): void
    {
        $rules = GrammarRuleParser::parse($rules);

        $reader = new CsvTableReader();

        $table = $reader->read($tableString);
        $parser = new LALR1Parser($table, $rules);
        self::assertEquals($astTree, $parser->parse($tokens));

    }

    public function parseProvider(): array
    {

        return [
            'all void' => [
                '$tokens' => [],
                '$rules' => "",
                '$table' => '',
                '$astTree' => new AstTree()
            ],
            'one rule no tokens' => [
                [],
                "test: a;",
                file_get_contents(__DIR__ . '/../../../grammar/test.grr.lrt'),
                '$astTree' => new AstTree()
            ],
            'one rule one token' => [
                [new Token('a', 'aaa')],
                "test: a;",
                file_get_contents(__DIR__ . '/../../../grammar/test.grr.lrt'),
                (new AstTree())->addChild((new AstLeaf('test'))->addChildToken(new Token('a', 'aaa')))
            ],
            'testTest' => [
                [
                    new Token('A', 'A1'),
                    new Token('A', 'A2'),
                    new Token('C', 'C1'),
                    new Token('D', 'D1'),
                    new Token('D', 'D2'),
                ],
                file_get_contents(__DIR__ . '/../../../grammar/testTest.grr'),
                file_get_contents(__DIR__ . '/../../../grammar/testTest.grr.lrt'),
                (new AstTree())
                    ->addChild(
                        (new AstLeaf('a'))
                            ->addChildLeaf(
                                (new AstLeaf('b'))
                                    ->addChildToken(new Token('A', 'A1'))
                                    ->addChildToken(new Token('A', 'A2'))
                                    ->addChildToken(new Token('C', 'C1'))
                            )
                            ->addChildToken( new Token('D', 'D1'))
                            ->addChildToken( new Token('D', 'D2'))
                    ),
            ],
            'calculator' => [
                [
                    new Token('INT_NUM', '123'),
                    new Token('PLUS', '+'),
                    new Token('FLOAT_NUM', '23.5'),
                ],
                file_get_contents(__DIR__ . '/../../../grammar/calculator_new.grr'),
                file_get_contents(__DIR__ . '/../../../grammar/calculator_new.grr.lrt'),
                (new AstTree())
                    ->addChild(
                        (new AstLeaf('expression'))
                            ->addChildLeaf(
                                (new AstLeaf('mulExpression'))
                                ->addChildLeaf(
                                    (new AstLeaf('atom'))
                                        ->addChildToken(new Token('INT_NUM', '123'))
                                )
                            )
                            ->addChildLeaf(
                                (new AstLeaf('plusMinusPart'))
                                    ->addChildToken(new Token('PLUS', '+'))
                                    ->addChildLeaf(
                                        (new AstLeaf('mulExpression'))
                                            ->addChildLeaf(
                                                (new AstLeaf('atom'))
                                                    ->addChildToken(new Token('FLOAT_NUM', '23.5'))
                                            )
                                    )
                            )
                    )
            ]
        ];
    }
}
