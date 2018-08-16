<?php namespace QR\Xngine;
/**
 * Author: lori@flashbay.com
 *
 **/
use QR\DAO\ExpressionNodes as NodeDAO;
use QR\Xngine\SyntaxOperator;
use QR\Xngine\Tokenizer;
use QR\Xngine\NodeFunction;
use Form\Util;
use QR\DebugProfiler;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
class ExpressionTreeNode extends SyntaxTreeNode
{
    const LEFT_CHILD  = 0x1;
    const RIGHT_CHILD = 0x2;
    static public function nodeEval(SyntaxTreeNode $node, $dryrun = false)
    {
        DebugProfiler :: start();
        $operation = $node->getOperation();
        $functionNO= $node->getNodeInfo('function');
        $runtimeContext = $node->getTree()
                               ->getRuntimeContext();
        $nodeTokenList = $node->tokenList;
        if($node->getType() === static :: TYPE_LEAF) {
            if($name = $node->getNodeInfo('name')) {
                if(!$dryrun && !$runtimeContext) {
                    throw new \Exception('Runtime context is NOT set');
                }
                $nodeTokenList = [[
                    'type'        => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_VARIABLE),
                    'value'       => $name,
                    'description' => $node->getNodeInfo('description'),
                    'fail_code'   => 0,
                    'fail_action' => 0,
                ]];
                if($dryrun) {
                    $node->value    = '';
                    $node->valueTxt = Util :: strVal($name);
                } else {
                    try {
                        $value = $runtimeContext->getVarManager()
                                                ->getValue($name);
                    } catch(\Exception $e) {
                        throw new \Exception(sprintf('%s on expression tree node: %d', $e->getMessage(), $node->getNodeInfo('id')));
                    }
                    $node->value     = $value;
                    $node->valueTxt  = sprintf('%s', Util :: strVal($node->value));
                    $node->valueTxtZ = sprintf('(%s = %s)', Util :: strVal($name), Util :: strVal($node->value));
                }
            } else {
                $node->value     = $node->getNodeInfo('value');
                $node->valueTxt  =
                $node->valueTxtZ = sprintf('%s', Util :: strVal($node->value));
                $nodeTokenList = [[
                    'type'        => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_CONSTANT),
                    'value'       => $node->getNodeInfo('value'),
                    'description' => $node->getNodeInfo('description'),
                    'fail_code'   => 0,
                    'fail_action' => 0,
                ]];
            }
        } else {
            $left  = $node->getLeft();
            $right = $node->getRight();
            if(!$left || !$right) {
                throw new \Exception(
                    sprintf('Expression tree node:[%d] corrupted(one of left/right is null).', $node->getNodeInfo('id'))
                );
            }
            $lValueInfo = $left->getValueInfo();
            $rValueInfo = $right->getValueInfo();
            $nodeToken  = [
                'type'        => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_OPERATOR),
                'value'       => SyntaxOperator :: getOperatorTxt($node->getNodeInfo('operator')),
                'description' => $node->getNodeInfo('description'),
                'fail_code'   => $node->getNodeInfo('fail_code'),
                'fail_action' => $node->getNodeInfo('fail_action'),
            ];
            $nodeTokenList = array_merge($lValueInfo[2], [$nodeToken], $rValueInfo[2]);
            $stringPattern = '%s %s %s';
            if(($parentNode = $node->getParent())) {
                $priorityCmp = SyntaxOperator :: priorityCmp($parentNode->getOperation(), $operation);
                if($priorityCmp > 0 || (!$priorityCmp && Util :: equal($node, $parentNode->getRight()))) {
                    $stringPattern = '(%s %s %s)';
                    array_unshift($nodeTokenList, [
                        'type' => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_BRACKET),
                        'value' => '(', 'description' => '', 'fail_code' => 0, 'fail_action' => 0,
                    ]);
                    array_push($nodeTokenList, [
                        'type' => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_BRACKET),
                        'value' => ')', 'description' => '', 'fail_code' => 0, 'fail_action' => 0,
                    ]);
                }
            }
            if($dryrun) {
                $node->value = '';
            } else {
                $node->value = SyntaxOperator :: doOperation($lValueInfo[0], $operation, $rValueInfo[0]);
            }
            $node->valueTxt  = sprintf($stringPattern,
                Util :: strVal($lValueInfo[1]),
                Util :: strVal(SyntaxOperator :: getOperatorTxt($operation)),
                Util :: strVal($rValueInfo[1])
            );
            $node->valueTxtZ = sprintf($stringPattern,
                Util :: strVal($lValueInfo[3]),
                Util :: strVal(SyntaxOperator :: getOperatorTxt($operation)),
                Util :: strVal($rValueInfo[3])
            );
        }
        $node->value0 = $node->value;
        if($functionNO) {
            if(!$dryrun) {
                $node->value = NodeFunction :: doFunc($functionNO, $node->value);
            } else {
                $node->value = '';
            }
            $noNeedBrackPair = $node->valueTxt && '(' == $node->valueTxt{0} && ')' == $node->valueTxt{strlen($node->valueTxt) - 1};
            $pattern = $noNeedBrackPair ? '%s%s' : '%s(%s)';
            $node->valueTxt  = sprintf($pattern, Util :: strVal(NodeFunction :: getFunctionTxt($functionNO)), $node->valueTxt);
            $node->valueTxtZ = sprintf($pattern, Util :: strVal(NodeFunction :: getFunctionTxt($functionNO)), $node->valueTxtZ);
            if(!$noNeedBrackPair) {
                array_unshift($nodeTokenList, [
                    'type' => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_BRACKET),
                    'value' => '(', 'description' => '', 'fail_code' => 0, 'fail_action' => 0,
                ]);
            }
            array_unshift($nodeTokenList, [
                'type' => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_FUNCTION),
                'value' => NodeFunction :: getFunctionTxt($functionNO),
            ]);
            if(!$noNeedBrackPair) {
                array_push($nodeTokenList, [
                    'type' => Tokenizer :: getTokenTypeName(Tokenizer :: TYPE_BRACKET),
                        'value' => ')', 'description' => '', 'fail_code' => 0, 'fail_action' => 0,
                ]);
            }
        }
        $node->tokenList = $nodeTokenList;
        DebugProfiler :: end();
    }
    /**
     * Callback when store each tree node
     *
     **/
    static public function nodeStore(ExpressionTreeNode $node)
    {
        if($parent = $node->getParent()) {
            $node->setNodeInfo('parent', $parent->getNodeInfo('id'));
        }
        $rootNode = $node->getTree()
                         ->getRoot();
        $nodeInfo = $node->getNodeInfo();
        $nodeDAO  = new NodeDAO($nodeInfo);
        $nodeDAO->root_id = $rootNode->getNodeInfo('id');
        $nodeDAO->store();
        $node->setNodeInfo('id', $nodeDAO->id);
    }

    static public function nodeLevelOrderEval(ExpressionTreeNode $node, &$traverseResult)
    {
        static $lastLevel, $lastIndex;
        $parent = $node->getParent();
        $currentLevel = -1;
        $parentKey = null;
        if(empty($parent)) {
            $traverseResult = [];
            $lastLevel      = 0;
            $currentLevel   = 0;
            $lastIndex      = -1;
        } else {
            $parentKey    = spl_object_hash($parent);
            $currentLevel = $traverseResult[$parentKey]['level'] + 1;
        }
        $operatorTxt = null;
        $valueTxt = Util :: strVal($node->value0);
        if($node->getType() === static :: TYPE_LEAF) {
            if($name = $node->getNodeInfo('name')) {
                $valueTxt = sprintf('%s = %s', $name, $valueTxt);
            }
        } else {
            $operatorTxt = SyntaxOperator :: getOperatorTxt($node->getOperation());
        }
        $functionTxt = '';
        if($functionNO = $node->getNodeInfo('function')) {
            $valueTxt = sprintf('%s = %s(%s)',
                Util :: strVal($node->value),
                $functionTxt = NodeFunction :: getFunctionTxt($functionNO),
                $valueTxt
            );
        }
        $key = spl_object_hash($node);
        if($lastLevel != $currentLevel) {
            $lastIndex = 0;
            $lastLevel = $currentLevel; 
        } else {
            ++$lastIndex;
        }
        $traverseResult[$key] = [
            'key'         => $key,
            'level'       => $currentLevel,
            'parentKey'   => $parentKey,
            'idxParent'   => $traverseResult[$parentKey]['index'],
            'value0'      => $node->value0,
            'value'       => $node->value,
            'valueTxt'    => $valueTxt,
            'function'    => $functionTxt,
            'operatorTxt' => $operatorTxt,
            'left'        => null,
            'right'       => null,
            'lrOrder'     => null,
            'index'       => $lastIndex,
        ];
        if(!empty($parentKey)) {
            $parent = &$traverseResult[$parentKey];
            if(null == $parent['left']) {
                $parent['left']    = $key;
                $parent['idxLeft'] = $lastIndex;
                $traverseResult[$key]['lrOrder'] = static :: LEFT_CHILD;
            } else {
                $parent['right']    = $key;
                $parent['idxRight'] = $lastIndex;
                $traverseResult[$key]['lrOrder'] = static :: RIGHT_CHILD;
            }
        }
        return $traverseResult;
    }
    /**
     * See class SyntaxOperator
     *
     **/
    public function getOperation($relationType = null)
    {
        $operation = 0;
        if(null === $relationType) {
            $operation = $this->getNodeInfo('operator');
        }
        return $operation;
    }

    public function getRelationType($operation = null)
    {
        if(null === $operation) {
            $operation = $this->getOperation();
        }
        return $operation;
    }

    public function equal(SyntaxTreeNode $node)
    {
        $typeA = $this->getType();
        $typeB = $node->getType();
        $operationA = $this->getOperation();
        $operationB = $node->getOperation();
        $functionA  = $this->getNodeInfo('function');
        $functionB  = $node->getNodeInfo('function');
        //
        $descriptionA = $this->getNodeInfo('description');
        $descriptionB = $node->getNodeInfo('description');
        $failCodeA    = $this->getNodeInfo('fail_code');
        $failCodeB    = $node->getNodeInfo('fail_code');
        $failActionA  = $this->getNodeInfo('fail_action');
        $failActionB  = $node->getNodeInfo('fail_action');
        if($typeA != $typeB
            || $functionA != $functionB
            || $descriptionA != $descriptionB
            || $failCodeA != $failCodeB
            || $failActionA != $failActionB) {
            return false;
        } else {
            if(static :: TYPE_INNER == $typeA) {
                return $operationA == $operationB;
            } else {
                $nameA = $this->getNodeInfo('name');
                $nameB = $node->getNodeInfo('name');
                if($nameA != $nameB) {
                    return false;
                } elseif(empty($nameA)) {
                    $valueA = $this->getNodeInfo('value');
                    $valueB = $node->getNodeInfo('value');
                    if(is_numeric($valueA) && is_numeric($valueB)) {
                        return floatval($valueA) == floatval($valueB);
                    } else {
                        return $valueA == $valueB;
                    }
                } else {
                    return true;
                }
            }
        }
    }
}
