<?php
declare(strict_types=1);

namespace CodingLiki\LALR1Parser\Parser;

use CodingLiki\GrammarParser\Rule\Rule;
use CodingLiki\GrammarParser\Rule\RulePart;
use CodingLiki\GrammarParser\RulesHelper;
use CodingLiki\GrammarParser\Token\Token;
use CodingLiki\LALR1Parser\AstTree\AstLeaf;
use CodingLiki\LALR1Parser\AstTree\AstTree;
use CodingLiki\LALR1Parser\Parser\Exceptions\BadTokenException;
use CodingLiki\LALR1Parser\Parser\Exceptions\ErrorInTableException;
use CodingLiki\LALR1Parser\Parser\StackElements\ParserStackLeafElement;
use CodingLiki\LALR1Parser\Parser\StackElements\ParserStackPositionElement;
use CodingLiki\LALR1Parser\Parser\StackElements\ParserStackTokenElement;
use Error;

class LALR1Parser implements LALR1ParserInterface
{
    private ParserStack $stack;
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
        $this->stack = new ParserStack();
    }

    /**
     * @inheritDoc
     * @throws BadTokenException
     * @throws ErrorInTableException
     */
    public function parse(array $tokenChain): AstTree
    {
        $tree = new AstTree();

        if (empty($this->table) || empty($this->rules) || empty($tokenChain)) {
            return $tree;
        }

        $tokenChain[] = new Token('$', '');


        $this->stack->putElement(new ParserStackPositionElement(0));
        while ($token = array_shift($tokenChain)) {
            $position = $this->stack->getElement();
            $isPositionInStack = true;

            $key = $token->getType();
            if ($position instanceof ParserStackLeafElement) {
                $isPositionInStack = false;

                /** @var ParserStackLeafElement $element */
                $element = $this->stack->popElement();
                $position = $this->stack->getElement();
                $this->stack->putElement($element);
                $key = $element->getContent()->getName();
            }

            $whatToDo = $this->table[$position->getContent()][$key] ?? null;

            if($whatToDo === null){
                throw new BadTokenException($key);
            }
            switch ($whatToDo[0] ?? null) {
                case 's' :
                    $stageNumber = (int)substr($whatToDo, 1);
                    if ($isPositionInStack) {
                        $this->stack->putElement(new ParserStackTokenElement($token));
                    } else {
                        array_unshift($tokenChain, $token);
                    }
                    $this->stack->putElement(new ParserStackPositionElement($stageNumber));
                    break;
                case 'r':
                    if(!$isPositionInStack){
                        $this->stack->putElement(new ParserStackPositionElement(-1));
                    }
                    $ruleNumber = (int)substr($whatToDo, 1);
                    $rule = $this->rules[$ruleNumber];
                    $this->reduceByRule($rule);
                    array_unshift($tokenChain, $token);
                    break;
                case 'a':
                    break;
                case 'e':
                    throw new ErrorInTableException($token->getType(), $token->getValue());
            }
        }

        $this->stack->popElement();
        /** @var ParserStackLeafElement $leafElement */
        $leafElement = $this->stack->popElement();
        if($leafElement !== null){
            $tree->addChild($leafElement->getContent());
        }

        return $tree;
    }

    private function reduceByRule(Rule $rule): void
    {
        $leaf = new AstLeaf($rule->getName());
        $ruleParts = $rule->getParts();

        $reversedRuleParts = array_reverse($ruleParts);

        foreach ($reversedRuleParts as $part) {
            if ($part->getType() === RulePart::TYPE_NORMAL) {
                $this->stack->popElement();
                $element = $this->stack->popElement();

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
                    $positionElement = $this->stack->popElement();
                    $element = $this->stack->popElement();
                    if ($element instanceof ParserStackTokenElement && $element->getContent()->getType() === $part->getData()) {
                        $leaf->unshiftChildToken($element->getContent());
                    } elseif ($element instanceof ParserStackLeafElement && $element->getContent()->getName() === $part->getData()) {
                        $leaf->unshiftChildLeaf($element->getContent());
                    } elseif (!$positionElement instanceof ParserStackPositionElement) {
                        throw new Error("bad stackElement " . $positionElement->getType());
                    } else {
                        $finished = true;
                        $this->stack->putElement($element)->putElement($positionElement);
                    }
                }
            }
        }

        $this->stack->putElement(new ParserStackLeafElement($leaf));
    }
}