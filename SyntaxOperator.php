<?php namespace QR\Xngine;
/**
 * Author: lori@flashbay.com
 *
 **/
use Form\Util;
use QR\Xngine\NodeFunction;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
class SyntaxOperator
{
    const TYPE_RB  = 0x0;//)
    //Group A
    const TYPE_OR  = 0x1;//||
    const TYPE_AND = 0x2;//&&
    //Group B
    const TYPE_LESS= 0x3;//<
    const TYPE_GT  = 0x4;//>
    const TYPE_EQ  = 0x5;//==
    const TYPE_LE  = 0x6;//<=
    const TYPE_GE  = 0x7;//>=
    const TYPE_NE  = 0x8;//!=
    const TYPE_CT  = 0x9;//contain
    const TYPE_NC  = 0xA;//not contain
    const TYPE_CTD = 0xB;//contained
    const TYPE_NCTD= 0xC;//not contained
    const TYPE_REG = 0xD;//regex match
    const TYPE_NREG= 0xE;//not regex match
    const TYPE_MIN = 0xF;//min min(x,y) --> x min y
    const TYPE_MAX = 0x10;//max max(x,y) --> x max y
    const TYPE_SPLIT_WITH  = 0x11;//preg_split
    const TYPE_REPLACE_WITH= 0x12;//preg_replace
    const TYPE_PREG_REMOVE = 0x13;//preg_remove
    const TYPE_ARR_MERGE   = 0x14;//array_merge
    const TYPE_SLICED_BY   = 0x15;//sliced_by
    const TYPE_RESERVE_LEFT= 0x16;//reserve_left
    const TYPE_VALUE_OF    = 0x17;//value_of
    //Group C
    const TYPE_PLUS = 0x21;//+
    const TYPE_MINUS= 0x22;//-
    //Group D
    const TYPE_MUL  = 0x23;//x
    const TYPE_DIV  = 0x24;///
    //
    const TYPE_LB   = 0x30;//(
    //
    static private $_symbolsMap = [
        1 => '||', '&&',//Group 0
        '<', '>', '==', '<=', '>=', '!=', 'contain', 'not-contain',//Group 1
        'contained', 'not-contained', 'regex-match', 'regex-not-match', 'min', 'max',//Group 1
        'preg-split-with', 'preg-replace-with', 'preg-remove', 'array-merge', 'sliced-by',//Group 1
        'reserve-left', 'value-of',
        0x21 => '+', '-',//Group 2
        '*', '/',//Group 3
    ];
    //
    static public function isLeftBracket($bracket)
    {
        return '(' === $bracket;
    }

    static public function isRightBracket($bracket)
    {
        return ')' === $bracket;
    }
    /**
     * Get allowed operator list
     *
     **/
    static public function getOperatorList()
    {
        return array_merge(range(1, 0x17), range(0x21, 0x24));
    }

    static public function getOperatorTxtList()
    {
        return static :: $_symbolsMap;
    }
    /**
     * Validation of operator
     *
     **/
    static public function isOperatorValid($operator)
    {
        $operator = intval($operator);
        return in_array($operator, static :: getOperatorList());
    }

    static public function isOperatorTxtValid($operatorTxt)
    {
        return in_array($operatorTxt, static :: $_symbolsMap);
    }
    /**
     * Get text representation of an operator
     *
     **/
    static public function getOperatorTxt($operator)
    {
        if(!static :: isOperatorValid($operator)) {
            throw new \Exception(sprintf('Unsupported operator: 0x%X', $operator));
        }
        return static :: $_symbolsMap[$operator];
    }
    /**
     * Reverse method of getOperatorTxt
     *
     **/
    static public function getOperatorNum($operatorTxt)
    {
        $operatorTxt = preg_replace('#\s{2,}#', ' ', strtolower(trim($operatorTxt)));
        if(false === ($index = array_search($operatorTxt, static :: $_symbolsMap))) {
            throw new \Exception(sprintf('Unsupported operator: %s', $operatorTxt));
        }
        return $index;
    }
    /**
     * Compare 2 operators priority
     *
     **/
    static public function priorityCmp($operatorA, $operatorB)
    {
        if(!static :: isOperatorValid($operatorA) || !static :: isOperatorValid($operatorB)) {
            throw new \Exception(sprintf('Operator: 0x%X / 0x%X not support', $operatorA, $operatorB));
        }
        if($operatorA == $operatorB) {
            return 0;
        } else {
            $groupA = static :: getOperatorGroup($operatorA);
            $groupB = static :: getOperatorGroup($operatorB);
            if($groupA != $groupB) {
                return $groupA < $groupB ? -1 : 1;
            } else {
                if(!$groupA) {
                    return $operatorA < $operatorB ? -1 : 1;
                } else {
                    return 0;
                }
            }
        }
    }
    /**
     * Determine group No of an operator
     *
     **/
    static protected function getOperatorGroup($operator)
    {
        if(!static :: isOperatorValid($operator)) {
            throw new \Exception(sprintf('Unsupported operator: 0x%X', $operator));
        }
        if($operator <= 0x2) {
            return 0;
        } elseif($operator <= 0x17) {
            return 1;
        } elseif($operator <= 0x22) {
            return 2;
        } else {
            return 3;
        }
    }

    static public function isLogicalOperator($operator)
    {
        if(!is_numeric($operator)) {
            $operator = static :: getOperatorNum($operator);
        }
        $groupNO = static :: getOperatorGroup($operator);
        return $groupNO <= 0x1;
    }

    static public function doOperation($valueA, $operator, $valueB)
    {
        if(!static :: isOperatorValid($operator)) {
            throw new \Exception(sprintf('Unsupported operator: 0x%X', $operator));
        }
        switch($operator) {
        case static :: TYPE_OR:
            $r = static :: doGenericOr($valueA, $valueB); break;
        case static :: TYPE_AND:
            $r = static :: doGenericAnd($valueA, $valueB); break;
        case static :: TYPE_LESS:
            $r = static :: doGenericLess($valueA, $valueB); break;
        case static :: TYPE_GT:
            $r = static :: doGenericGreater($valueA, $valueB); break;
        case static :: TYPE_EQ:
            $r = static :: doGenericEqual($valueA, $valueB); break;
        case static :: TYPE_LE:
            $r = static :: doGenericLessEqual($valueA, $valueB); break;
        case static :: TYPE_GE:
            $r = static :: doGenericGreaterEqual($valueA, $valueB); break;
        case static :: TYPE_NE:
            $r = static :: doGenericNonEqual($valueA, $valueB); break;
        case static :: TYPE_CT:
            $r = static :: doGenericContain($valueA, $valueB); break;
        case static :: TYPE_NC:
            $r = static :: doGenericNonContain($valueA, $valueB); break;
        case static :: TYPE_CTD:
            $r = static :: doGenericContained($valueB, $valueA); break;
        case static :: TYPE_NCTD:
            $r = static :: doGenericNonContained($valueB, $valueA); break;
        case static :: TYPE_REG:
            $r = static :: doGenericRegExMatch($valueA, $valueB); break;
        case static :: TYPE_NREG:
            $r = static :: doGenericNonRegExMatch($valueA, $valueB); break;
        case static :: TYPE_MIN:
            $r = static :: doGenericMin($valueA, $valueB); break;
        case static :: TYPE_MAX:
            $r = static :: doGenericMax($valueA, $valueB); break;
        case static :: TYPE_SPLIT_WITH:
            $r = static :: doGenericSplitWith($valueA, $valueB); break;
        case static :: TYPE_REPLACE_WITH:
            $r = static :: doGenericReplaceWith($valueA, $valueB); break;
        case static :: TYPE_PREG_REMOVE:
            $r = static :: doGenericPregRemove($valueA, $valueB); break;
        case static :: TYPE_ARR_MERGE:
            $r = static :: doGenericArrayMerge($valueA, $valueB); break;
        case static :: TYPE_SLICED_BY:
            $r = static :: doGenericSlicedBy($valueA, $valueB); break;
        case static :: TYPE_RESERVE_LEFT:
            $r = $valueA; break;//right part is discarded
        case static :: TYPE_VALUE_OF:
            $r = static :: doGenericValueOf($valueA, $valueB); break;
            break;
        case static :: TYPE_PLUS:
            $r = static :: doGenericAdd($valueA, $valueB); break;
        case static :: TYPE_MINUS:
            $r = floatval($valueA) - floatval($valueB); break;
        case static :: TYPE_MUL:
            $r = static :: doGenericMultiply($valueA, $valueB); break;
        case static :: TYPE_DIV:
            $r = static :: doGenericDiv($valueA, $valueB); break;
        }
        return $r;
    }
    /**
     * Extended || operation, supports array operand 
     *
     **/
    static public function doGenericOr($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal || $rightVal;
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericOr($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericOr($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Or, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericOr($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended && operation, supports array operand 
     *
     **/
    static public function doGenericAnd($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal && $rightVal;
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericAnd($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericAnd($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when And, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericAnd($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended < operation, supports array operand 
     *
     **/
    static public function doGenericLess($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal < $rightVal;
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericLess($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericLess($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Less, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericLess($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended > operation, supports array operand 
     *
     **/
    static public function doGenericGreater($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal > $rightVal;
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericGreater($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericGreater($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Greater, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericGreater($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended <= operation, supports array operand 
     *
     **/
    static public function doGenericLessEqual($leftVal, $rightVal)
    {
        return NodeFunction :: doFunc(
            NodeFunction :: FUNC_NOT, static :: doGenericGreater($leftVal, $rightVal)
        );
    }
    /**
     * Extended >= operation, supports array operand 
     *
     **/
    static public function doGenericGreaterEqual($leftVal, $rightVal)
    {
        return NodeFunction :: doFunc(
            NodeFunction :: FUNC_NOT, static :: doGenericLess($leftVal, $rightVal)
        );
    }
    /**
     * Extended == operation, supports array operand 
     *
     **/
    static public function doGenericEqual($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = Util :: equal($leftVal, $rightVal, false);//Non-strickly equal
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = Util :: strVal($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericEqual($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = Util :: strVal($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericEqual($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Equal, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericEqual($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended != operation, supports array operand 
     *
     **/
    static public function doGenericNonEqual($leftVal, $rightVal)
    {
        return NodeFunction :: doFunc(
            NodeFunction :: FUNC_NOT, static :: doGenericEqual($leftVal, $rightVal)
        );
    }
    /**
     * Extended contain operation, supports array operand 
     *
     **/
    static public function doGenericContain($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = Util :: contain($leftVal, $rightVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = Util :: strVal($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericContain($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = Util :: strVal($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericContain($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Contain, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericContain($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended Not contain operation, supports array operand 
     *
     **/
    static public function doGenericNonContain($leftVal, $rightVal)
    {
        return NodeFunction :: doFunc(
            NodeFunction :: FUNC_NOT, static :: doGenericContain($leftVal, $rightVal)
        );
    }
    /**
     * Extended Contained operation, supports array operand 
     *
     **/
    static public function doGenericContained($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = Util :: contain($rightVal, $leftVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = Util :: strVal($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericContained($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = Util :: strVal($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericContained($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Contained, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericContained($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended Not-contained operation, supports array operand 
     *
     **/
    static public function doGenericNonContained($leftVal, $rightVal)
    {
        return NodeFunction :: doFunc(
            NodeFunction :: FUNC_NOT, static :: doGenericContained($leftVal, $rightVal)
        );
    }
    /**
     * Extended regex match operation, supports array operand 
     *
     **/
    static public function doGenericRegExMatch($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            if('#' != $rightVal[0]) {
                $rightVal = '#' . str_replace('#', '\\#', $rightVal) . '#';
            }
            $r = (preg_match($rightVal, $leftVal) > 0);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = Util :: strVal($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericRegExMatch($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = Util :: strVal($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericRegExMatch($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when RegEx matching, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericRegExMatch($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended no-regex match operation, supports array operand 
     *
     **/
    static public function doGenericNonRegExMatch($leftVal, $rightVal)
    {
        return NodeFunction :: doFunc(
            NodeFunction :: FUNC_NOT, static :: doGenericRegExMatch($leftVal, $rightVal)
        );
    }
    /**
     * Extended min operation, supports array operand 
     *
     **/
    static public function doGenericMin($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = min($rightVal, $leftVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericMin($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericMin($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Min, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericMin($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended max operation, supports array operand 
     *
     **/
    static public function doGenericMax($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = max($rightVal, $leftVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericMax($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericMax($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Max, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericMax($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended preg_split_with, supports array operand 
     *
     **/
    static public function doGenericSplitWith($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            if('#' != $rightVal{0}) {
                $rightVal = '#' . $rightVal . '#';
            }
            $r = preg_split($rightVal, $leftVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            array_walk($r, function(&$v, $k) use($rightVal) {
                $v = static :: doGenericSplitWith($v, $rightVal);
            });
        } else {
            if(!(is_array($leftVal) && is_array($rightVal))) {
                throw new \Exception(sprintf('Unsupported operation: %s preg_split_with %s', gettype($leftVal), gettype($rightVal)));
            }
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Split, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericSplitWith($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended preg_replace_with operation, supports array operand 
     *
     **/
    static public function doGenericReplaceWith($leftVal, $rightVal)
    {
        if(!is_array($leftVal)) {
            if(!is_array($rightVal) || 2 != count($rightVal)) {
                throw new \Exception(sprintf('Failed when Replace, right operand %s must be an array with size 2. Format: [pattern, replacement]', Util :: strVal($rightVal)));
            }
            list($pattern, $replacement) = $rightVal;
            if(!is_string($pattern) || !is_string($replacement)) {
                throw new \Exception(sprintf('Invalid pattern / replacement : %s / %s, given, both must be string', Util :: strVal($pattern), Util :: strVal($replacement)));
            }
            if('#' != $pattern{0}) {
                $pattern = '#' . $pattern . '#';
            }
            $r = preg_replace($pattern, $replacement, $leftVal);
        } else {
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericReplaceWith($v, (2 != count($rightVal) || is_array($rightVal[0])) ? $rightVal[$k] : $rightVal);
            }
        }
        return $r;
    }

    static protected function addReplacement(&$pattern)
    {
        if(!is_array($pattern)) {
            $pattern = [$pattern, ''];
        } else {
            foreach($pattern as &$v) {
                static :: addReplacement($v);
            }
        }
        return $pattern;
    }
    /**
     * Extended preg_remove operation, supports array operand 
     *
     **/
    static public function doGenericPregRemove($leftVal, $rightVal)
    {
        static :: addReplacement($rightVal);
        return static :: doGenericReplaceWith($leftVal, $rightVal);
    }
    /**
     * Extended ArrayMerge operation, supports array operand 
     *
     **/
    static public function doGenericArrayMerge($leftVal, $rightVal)
    {
        $leftVal = (array)$leftVal;
        $rightVal= (array)$rightVal;
        return array_merge($leftVal, $rightVal);
    }
    /**
     * Slice an array or string
     *
     **/
    static public function doGenericSlicedBy($leftVal, $rightVal)
    {
        $start = $rightVal;
        $length= null;
        if(is_array($rightVal)) {
            if(empty($rightVal)) {
                throw new \Exception('Failed when calling Sliced-by, right operand is empty, start index / length must be specified');
            }
            $start = $rightVal[0];
            if(isset($rightVal[1])) {
                $length = $rightVal[1];
            }
        }
        $start = intval($start);
        if(!is_array($leftVal)) {
            if(is_null($leftVal) || is_bool($leftVal)
                || is_object($leftVal) || is_resource($leftVal)) {
                return false;
            }
            $leftVal = Util :: strVal($leftVal);
            return null === $length ? substr($leftVal, $start) : substr($leftVal, $start, intval($length));
        } else {
            return null === $length ? array_slice($leftVal, $start) : array_slice($leftVal, $start, intval($length));
        }
    }
    /**
     * Value of given index
     *
     **/
    static public function doGenericValueOf($leftVal, $rightVal)
    {
        if(is_array($leftVal)) {
            if(!is_array($rightVal)) {
                return isset($leftVal[$rightVal]) ? $leftVal[$rightVal] : null;
            } else {
                $r = [];
                foreach($rightVal as $v) {
                    $v = Util :: strVal($v);
                    $r[$v] = static :: doGenericValueOf($leftVal, $v);
                }
                return $r;
            }
        } elseif(is_string($leftVal)) {
            if(!is_array($rightVal)) {
                $rightVal = intval($rightVal);
                return mb_substr($leftVal, $rightVal, 1);
            } else {
                $r = [];
                foreach($rightVal as $v) {
                    $v = intval($v);
                    $r[$v] = static :: doGenericValueOf($leftVal, $v);
                }
                return $r;
            }
        } else {
            throw new \Exception(sprintf('Unsupported value-of for %s(%s) / %s(%s)', gettype($leftVal), Util :: strVal($leftVal), gettype($rightVal), Util :: strVal($rightVal)));
        }
        return $leftVal;
    }
    /**
     * Extended Adding operation, supports array operand 
     *
     **/
    static public function doGenericAdd($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = floatval($leftVal) + floatval($rightVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericAdd($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericAdd($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Adding, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericAdd($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended multiply operation, supports array operand 
     *
     **/
    static public function doGenericMultiply($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = floatval($leftVal) * floatval($rightVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericMultiply($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericMultiply($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Mutiply, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericMultiply($v, $rightVal[$k]);
            }
        }
        return $r;
    }
    /**
     * Extended divide operation, supports array operand 
     *
     **/
    static public function doGenericDiv($leftVal, $rightVal)
    {
        if(!is_array($leftVal) && !is_array($rightVal)) {
            $r = floatval($leftVal) / floatval($rightVal);
        } elseif(is_array($leftVal) && !is_array($rightVal)) {
            $r = $leftVal;
            $rightVal = floatval($rightVal);
            array_walk($r, function(&$v, $k) use ($rightVal) {
                $v = static :: doGenericDiv($v, $rightVal);
            });
        } elseif(!is_array($leftVal) && is_array($rightVal)) {
            $r = $rightVal;
            $leftVal = floatval($leftVal);
            array_walk($r, function(&$v, $k) use ($leftVal) {
                $v = static :: doGenericDiv($leftVal, $v);
            });
        } else {
            if(count($leftVal) != count($rightVal)) {
                throw new \Exception(
                    sprintf('Failed when Dividing, Left / Right operand must have the same size: %d/%d, %s/%s',
                    count($leftVal), count($rightVal), Util :: strVal($leftVal), Util :: strVal($rightVal)
                ));
            }
            $leftVal = array_values($leftVal);
            $rightVal= array_values($rightVal);
            $r = [];
            foreach($leftVal as $k => $v) {
                $r[$k] = static :: doGenericDiv($v, $rightVal[$k]);
            }
        }
        return $r;
    }
}
