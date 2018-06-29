<?php namespace QR\Xngine;

/**
 *
 * Author: lori@flashbay.com
 *
 **/

use QR\Xngine\ExpressionTree as Tree;
use QR\Xngine\ExpressionTreeNode as Node;
use QR\Xngine\SyntaxOperator;
use QR\Xngine\NodeFunction;
use QR\Xngine\Tokenizer;
use QR\VariableManager;
use QR\DebugProfiler;
use Form\Util;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
class ExpressionParser
{
    static public function buildSyntaxTree($expressionOrTokens)
    {
        DebugProfiler :: start();
        $operandStack  = 
        $operatorStack = [];
        if(!is_array($expressionOrTokens)) {
            $expressionOrTokens = Tokenizer :: tokenize($expressionOrTokens);
        }
        if(!count($expressionOrTokens)) {
            DebugProfiler :: end();
            return null;
        }
        $tokenBracket  = Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_BRACKET);
        $tokenConstant = Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_CONSTANT);
        $tokenVariable = Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_VARIABLE);
        $tokenOperator = Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_OPERATOR);
        $tokenFunction = Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_FUNCTION);
        foreach($expressionOrTokens as &$token) {
            if($tokenOperator == $token['type']) {
                $token['NO'] = SyntaxOperator :: getOperatorNum($token['value']);
            }
        }
        unset($token);
        $expressionTree = new Tree();
        while($token = array_shift($expressionOrTokens)) {
            switch($token['type']) {
            case $tokenConstant:
            case $tokenVariable:
                $treeNode = static :: newTreeNodeFromToken($token);
                $treeNode->setTree($expressionTree);
                $operandStack[] = $treeNode;
                break;
            case $tokenOperator:
                while(($topOperatorNode = end($operatorStack)) instanceof Node
                   && SyntaxOperator :: priorityCmp($topOperatorNode->token['NO'], $token['NO']) >= 0) {
                    array_pop($operatorStack);
                    $rightOperandNode = array_pop($operandStack);
                    $leftOperandNode  = array_pop($operandStack);
                    if(!$leftOperandNode || !$rightOperandNode) {
                        throw new \Exception(sprintf('Invalid expression, missing operand(s) for operator: \'%s\'', $topOperatorNode->token['value']));
                    }
                    $topOperatorNode->setLeft($leftOperandNode)
                                    ->setRight($rightOperandNode);
                    $operandStack[]  = $topOperatorNode;
                    $topOperatorNode = end($operatorStack);
                }
                $treeNode = static :: newTreeNodeFromToken($token);
                $treeNode->setTree($expressionTree);
                $operatorStack[] = $treeNode;
                break;
            case $tokenFunction:
                $operatorStack[] = $token;
                break;
            case $tokenBracket:
                if(SyntaxOperator :: isLeftBracket($token['value'])) {
                    $operatorStack[] = $token;
                } elseif(SyntaxOperator :: isRightBracket($token['value'])) {
                    while(($operatorNode = array_pop($operatorStack)) instanceof Node) {
                        $rightOperandNode = array_pop($operandStack);
                        $leftOperandNode  = array_pop($operandStack);
                        if(!$leftOperandNode || !$rightOperandNode) {
                            throw new \Exception(sprintf('Invalid expression, missing operand(s) for operator: \'%s\'', $operatorNode->token['value']));
                        }
                        $operatorNode->setLeft($leftOperandNode)
                                     ->setRight($rightOperandNode);
                        $operandStack[] = $operatorNode;
                        $emptyBrackets  = false;
                    }
                    if(!$operatorNode || !SyntaxOperator :: isLeftBracket($operatorNode['value'])) {
                        throw new \Exception('Invalid expression, missing \'(\'');
                    }
                    if(!(($topOperatorNode = end($operatorStack)) instanceof Node)
                        && 'function' == $topOperatorNode['type']) {
                        $targetNode = end($operandStack);
                        if($targetNode) {
                            $targetNode->setNodeInfo('function', NodeFunction :: getFunctionNum($topOperatorNode['value']));
                            array_pop($operatorStack);
                        } else {
                            throw new \Exception(sprintf('Invalid expression, missing parameter for function: \'%s\'', $topOperatorNode['value']));
                        }
                    }
                } else {//Should never happen
                    throw new \Exception(sprintf('Invalid expression, invalid token: \'%s\'', $token['value']));
                }
                break;
            default:
                throw new \Exception(sprintf('Invalid expression, unsupported token type: \'%s\'', $token['type']));
            }
        }
        while(count($operatorStack)) {
            while(($operatorNode = array_pop($operatorStack)) instanceof Node) {
                $rightOperandNode = array_pop($operandStack);
                $leftOperandNode  = array_pop($operandStack);
                if(!$leftOperandNode || !$rightOperandNode) {
                    throw new \Exception(sprintf('Invalid expression, missing operand(s) for operator: \'%s\'', $operatorNode->token['value']));
                }
                $operatorNode->setLeft($leftOperandNode)
                             ->setRight($rightOperandNode);
                $operandStack[] = $operatorNode;
            }
            if($operatorNode) {
                if(SyntaxOperator :: isLeftBracket($operatorNode['value'])) {
                    throw new \Exception('Invalid expression, missing \')\'');
                } else {//Should never reach
                    throw new \Exception(sprintf('Invalid expression, unknown error, token: \'%s\'', Util :: strVal($operatorNode)));
                }
            }
        }
        if(1 !== count($operandStack)) {
            throw new \Exception('Invalid expression, missing operator(s)');
        }
        $expressionTree->setRoot(end($operandStack));
        DebugProfiler :: end();
        return $expressionTree;
    }

    static protected function newTreeNodeFromToken($token)
    {
        $treeNode = new Node();
        //Initialize DB fields
        $treeNode->setNodeInfo('parent', 0)
                 ->setNodeInfo('root_id', 0) 
                 ->setNodeInfo('operator', 0)
                 ->setNodeInfo('name', '')
                 ->setNodeInfo('description', '')
                 ->setNodeInfo('value', '')
                 ->setNodeInfo('function', 0)
                 ->setNodeInfo('fail_code', 0)
                 ->setNodeInfo('fail_action', 0);
        $treeNode->token = $token;
        $tokenType = Tokenizer :: getTokenType($token['type']);
        switch($tokenType) {
        case Tokenizer :: TYPE_CONSTANT:
            $treeNode->setNodeInfo('value', $token['value']); break;
        case Tokenizer :: TYPE_VARIABLE:
            if(!VariableManager :: isVarValid($token['value'])) {
                throw new \Exception(sprintf('Unknown variable: %s', $token['value']));
            }
            $treeNode->setNodeInfo('name', $token['value']); break;
        case Tokenizer :: TYPE_OPERATOR:
            $treeNode->setNodeInfo('operator', SyntaxOperator :: getOperatorNum($token['value']));
            $failCode = $failAction = 0;
            if(SyntaxOperator :: isLogicalOperator($token['value'])) {
                $failCode   = intval(empty($token['fail_code']) ? 0 : $token['fail_code']);
                $failAction = intval(empty($token['fail_action']) ? 0 : $token['fail_action']);
            }
            $treeNode->setNodeInfo('fail_code', $failCode)
                     ->setNodeInfo('fail_action', $failAction);
            break;
        default:
            throw new \Exception(sprintf('Invalid token: %s', Util :: strVal($token)));
        }
        if(isset($token['description'])) {
            $treeNode->setNodeInfo('description', $token['description']);
        }
        return $treeNode;
    }
}
