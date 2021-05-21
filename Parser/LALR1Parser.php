<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser;

use CodingLiki\GrammarParser\Rule\Rule;
use CodingLiki\GrammarParser\Rule\RulePart;
use CodingLiki\GrammarParser\RulesHelper;
use CodingLiki\GrammarParser\Token\Token;
use CodingLiki\LALR1Parser\AstTree\AstLeaf;
use CodingLiki\LALR1Parser\AstTree\AstTree;
use CodingLiki\LALR1Parser\Parser\StackElements\ParserStackLeafElement;
use CodingLiki\LALR1Parser\Parser\StackElements\ParserStackPositionElement;
use CodingLiki\LALR1Parser\Parser\StackElements\ParserStackTokenElement;
use Error;

class LALR1Parser implements LALR1ParserInterface
{
    /**
     * LALR1Parser constructor.
     * @param array $table
     * @param Rule[] $rules
     */
    public function __construct(private array $table, private array $rules)
    {
        if (!empty($this->rules)) {
            array_unshift($this->rules, RulesHelper::buildRootRule($this->rules));
        }
    }

    /**
     * @inheritDoc
     */
    public function parse(array $tokenChain): AstTree
    {
        $tree = new AstTree();

        if (empty($this->table) || empty($this->rules) || empty($tokenChain)) {
            return $tree;
        }

        $tokenChain[] = new Token('$', '');


        $stack = new ParserStack();
        $stack->putElement(new ParserStackPositionElement(0));
        while ($token = array_shift($tokenChain)) {
            $position = $stack->getElement();
            $isPositionInStack = true;

            $key = $token->getType();
            if ($position instanceof ParserStackLeafElement) {
                $isPositionInStack = false;

                /** @var ParserStackLeafElement $element */
                $element = $stack->popElement();
                $position = $stack->getElement();
                $stack->putElement($element);
                $key = $element->getContent()->getName();
            }

            $whatToDo = $this->table[$position->getContent()][$key];

            switch ($whatToDo[0]) {
                case 's' :
                    $stageNumber = (int)substr($whatToDo, 1);
                    if ($isPositionInStack) {
                        $stack->putElement(new ParserStackTokenElement($token));
                    } else {
                        array_unshift($tokenChain, $token);
                    }
                    $stack->putElement(new ParserStackPositionElement($stageNumber));
                    break;
                case 'r':
                    if(!$isPositionInStack){
                        $stack->putElement(new ParserStackPositionElement(-1));
                    }
                    $ruleNumber = (int)substr($whatToDo, 1);
                    $rule = $this->rules[$ruleNumber];
                    $this->reduceByRule($stack, $rule);
                    array_unshift($tokenChain, $token);
                    break;
                case 'a':
                    break;
                case 'e':
                    throw new Error("bad token " . $token->getType());
            }
        }

        $stack->popElement();
        /** @var ParserStackLeafElement $leafElement */
        $leafElement = $stack->popElement();
        if($leafElement !== null){
            $tree->addChild($leafElement->getContent());
        }

        return $tree;
    }

    private function reduceByRule(ParserStack $stack, Rule $rule): void
    {
        $leaf = new AstLeaf($rule->getName());
        $ruleParts = $rule->getParts();

        $reversedRuleParts = array_reverse($ruleParts);

        foreach ($reversedRuleParts as $part) {
            if ($part->getType() === RulePart::TYPE_NORMAL) {
                $stack->popElement();
                $element = $stack->popElement();

                if ($element instanceof ParserStackTokenElement && $element->getContent()->getType() === $part->getData()) {
                    $leaf->unshiftChildToken($element->getContent());
                } elseif ($element instanceof ParserStackLeafElement && $element->getContent()->getName() === $part->getData()) {
                    $leaf->unshiftChildLeaf($element->getContent());
                } else {
                    throw new Error("bad stackElement " . $element->getType());
                }

            } else {
                $finished = false;

                while (!$finished) {
                    $positionElement = $stack->popElement();
                    $element = $stack->popElement();
                    if ($element instanceof ParserStackTokenElement && $element->getContent()->getType() === $part->getData()) {
                        $leaf->unshiftChildToken($element->getContent());
                    } elseif ($element instanceof ParserStackLeafElement && $element->getContent()->getName() === $part->getData()) {
                        $leaf->unshiftChildLeaf($element->getContent());
                    } elseif (!$positionElement instanceof ParserStackPositionElement) {
                        throw new Error("bad stackElement " . $positionElement->getType());
                    } else {
                        $finished = true;
                        $stack->putElement($element)->putElement($positionElement);
                    }
                }
            }
        }

        //$leaf->getName();
        $stack->putElement(new ParserStackLeafElement($leaf));
    }
}