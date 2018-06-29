<?php namespace QR\Xngine;
/**
 * @Author: lori@flashbay.com
 *
 **/

use QR\Xngine\SyntaxOperator;
use Form\Util;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
class Tokenizer
{
    const TYPE_BRACKET  = 0x0;
    const TYPE_CONSTANT = 0x1;
    const TYPE_VARIABLE = 0x2;
    const TYPE_OPERATOR = 0x3;
    const TYPE_FUNCTION = 0x4;
    //
    static private $_tokenTypes = [
        'constant' => ['name' => 'constant', 'text' => '常量',],
        'variable' => ['name' => 'variable', 'text' => '变量',],
        'operator' => ['name' => 'operator', 'text' => '运算符',],
        'function' => ['name' => 'function', 'text' => '函数',],
    ];

    static public function getTokensTypes()
    {
        return static :: $_tokenTypes;
    }

    static public function isBlank($char)
    {
        return ' ' === $char || "\n" == $char || "\r" == $char || "\t" == $char || "\v" == $char;
    }

    static public function getTokenType($tokenName)
    {
        switch(strtolower($tokenName)) {
        case 'bracket':  return static :: TYPE_BRACKET;
        case 'constant': return static :: TYPE_CONSTANT;
        case 'variable': return static :: TYPE_VARIABLE;
        case 'operator': return static :: TYPE_OPERATOR;
        case 'function': return static :: TYPE_FUNCTION;
        }
        throw new \Exception(sprintf('Unknown token name: %s.', $tokenName));
    }

    static public function getTokenTypeName($tokenType)
    {
        if($tokenType < 0 || $tokenType > 0x4) {
            throw new \Exception(sprintf('Unknow Token Type: %d', $tokenType));
        }
        if(0 == $tokenType) {
            return 'bracket';
        } else {
            $tokenTypes = array_keys(static :: getTokensTypes());
            return $tokenTypes[$tokenType - 1];
        }
    }
    /**
     * Tokenize the expression given
     *
     * @param string $expressionStr
     *
     * @return array tokens
     *
     **/
    static public function tokenize($expressionStr)
    {
        $expressionStr = preg_replace('#\s{2,}#', ' ', trim(Util :: strVal($expressionStr)));
        $constantPattern   = '#^((0|[1-9][0-9]*)(\.[0-9]+)?|\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")#';
        $variablePattern   = '#^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*#';
        $functionPattern   = '#^([a-zA-Z]+[_0-9]*[a-zA-Z]*)\s*\(#';
        $operatiorPatterns = [];
        foreach(SyntaxOperator :: getOperatorTxtList() as $opTxt) {
            $operatiorPatterns[] = '#^' . trim(preg_quote(preg_replace(' {2,}', ' ', $opTxt), '#')) . '#';
        }
        rsort($operatiorPatterns);
        $tokens = [];
        $currentIndex = 0;
        $len = strlen($expressionStr);
        while($currentIndex < $len) {
            $subExpressionStr = substr($expressionStr, $currentIndex);
            $noMatch = false;
            if(' ' == $expressionStr{$currentIndex}) {
                ++$currentIndex;
            } elseif('(' == $expressionStr{$currentIndex} || ')' == $expressionStr{$currentIndex}) {
                $tokens[] = [
                    'type' => static :: getTokenTypeName(static :: TYPE_BRACKET),
                    'value'=> $expressionStr{$currentIndex++},
                ];
                
            } elseif(preg_match($constantPattern, $subExpressionStr, $m)) {
                $tokens[] = [
                    'type' => static :: getTokenTypeName(static :: TYPE_CONSTANT),
                    'value'=> trim($m[0], '\'"'),
                ];
                $currentIndex += strlen($m[0]);
            } elseif(preg_match($variablePattern, $subExpressionStr, $m)) {
                $tokens[] = [
                    'type' => static :: getTokenTypeName(static :: TYPE_VARIABLE),
                    'value'=> $m[0],
                ];
                $currentIndex += strlen($m[0]);
            } else {
                $noMatch = true;
                foreach($operatiorPatterns as $pattern) {
                    if(preg_match($pattern, $subExpressionStr, $m)) {
                        $noMatch = false;
                        $tokens[] = [
                            'type' => static :: getTokenTypeName(static :: TYPE_OPERATOR),
                            'value' => $m[0],
                        ];
                        $currentIndex += strlen($m[0]);
                        break;
                    }
                }
                if($noMatch) {
                    if(preg_match($functionPattern, $subExpressionStr, $m)) {
                        $tokens[] = [
                            'type' => static :: getTokenTypeName(static :: TYPE_FUNCTION),
                            'value'=> $m[1],
                        ];
                        $noMatch = false;
                    }
                    $currentIndex += strlen($m[0]) - 1;
                }
            }
            if($noMatch) {
                throw new \Exception(sprintf('Unrecognized expression %s at: %s',
                    $expressionStr, $subExpressionStr
                ));
            }
        }
        return $tokens;
    }
}
