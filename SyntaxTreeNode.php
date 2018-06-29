<?php namespace QR\Xngine;
/**
 * Author: lori@flashbay.com
 *
 **/
use QR\Xngine\SyntaxTree;
use QR\Xngine\SyntaxOperator;

abstract class SyntaxTreeNode
{
    const TYPE_LEAF = 0x1;
    const TYPE_INNER= 0x2;
    //
    private $_tree;
    //
    private $_parent;
    private $_leftChild;
    private $_rightChild;
    //
    private $_dynamicValues;
    private $_nodeInfo;
    private $_name;
    private $_operator;
    private $_type;
    //
    public function __construct(array $nodeInfo = array())
    {
        $this->_tree = null;
        //
        $this->_parent    =
        $this->_leftChild =
        $this->_rightChild= null;
        $this->_dynamicValues = [];
        //
        $this->_nodeInfo = $nodeInfo;
        $this->_name     =
        $this->_operator = null;
        //
        $this->_type = static :: TYPE_LEAF;
    }

    public function getNodeInfo($key = null)
    {
        if(null === $key) {
            return $this->_nodeInfo;
        }
        if(isset($this->_nodeInfo[$key])) {
            return $this->_nodeInfo[$key];
        } else {
            return null;
        }
    }

    public function setNodeInfo($key, $value)
    {
        $this->_nodeInfo[$key] = $value;
        return $this;
    }

    public function setTree(SyntaxTree $tree)
    {
        $this->_tree = $tree;
        return $this;
    }

    public function getTree()
    {
        return $this->_tree;
    }

    public function setParent(SyntaxTreeNode $parent = null)
    {
        $this->_parent = $parent;
        return $this;
    }

    public function getParent()
    {
        return $this->_parent;
    }

    public function setLeft(SyntaxTreeNode $left = null)
    {
        if($this->_leftChild = $left) {
            $this->_type = static :: TYPE_INNER;
            $left->setParent($this);
        } elseif (null == $this->_rightChild) {
            $this->_type = static :: TYPE_LEAF;
        }
        return $this;
    }

    public function getLeft()
    {
        return $this->_leftChild;
    }

    public function setRight(SyntaxTreeNode $right = null)
    {
        if($this->_rightChild = $right) {
            $this->_type = static :: TYPE_INNER;
            $right->setParent($this);
        } elseif (null == $this->_leftChild) {
            $this->type = static :: TYPE_LEAF;
        }
        return $this;
    }

    public function getRight()
    {
        return $this->_rightChild;
    }

    public function setName($name)
    {
        if($name) {
            $this->_name = $name;
        }
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setOperator($operator)
    {
        if(!SyntaxOperator :: isOperatorValid($operator)) {
            throw new \Exception(sprintf('Invalid operator: %s!', $operator));
        }
        $this->_operator = $operator;
        return $this;
    }

    public function getOperator()
    {
        return $this->_operator;
    }

    public function __get($attrName)
    {
        return isset($this->_dynamicValues[$attrName])
                   ? $this->_dynamicValues[$attrName] : null;
    }

    public function __set($attrName, $value)
    {
        $this->_dynamicValues[$attrName] = $value;
    }
    /**
     * Get Value info
     *
     * @return [$value, $valueExpressionTxt,]
     *
     **/
    public function getValueInfo()
    {   
        return [$this->value, $this->valueTxt, $this->tokenList, $this->valueTxtZ,];
    }

    public function getAncestorPathIdSet()
    {
        $nodeId= $this->getNodeInfo('id');
        $idSet = [$nodeId => $nodeId,];
        $parent = $this->getParent();
        while($parent) {
            $nodeId  = $parent->getNodeInfo('id');
            if(isset($idSet[$nodeId])) {
                throw new \Exception(sprintf('Cycle found for node: %d & %d', end($idSet), $nodeId));
            }
            $idSet[$nodeId] = $nodeId;
            $parent = $parent->getParent();
        }
        return $idSet;
    }

    abstract public function getOperation($relationType);

    abstract public function getRelationType($opration);
    
    static public function nodeEval(SyntaxTreeNode $node, $dryrun = false)
    {
        throw new \Exception('Unimplemented method: nodeEval');
    }

    public function equal(SyntaxTreeNode $treeNode)
    {
        throw new \Exception('Unimplemented method: equal');
    }
}
