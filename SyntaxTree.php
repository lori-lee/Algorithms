<?php namespace QR\Xngine;
/**
 * Author: lori@flashbay.com
 *
 **/
use QR\Xngine\SyntaxTreeNode as TreeNode;
use QR\RuntimeContext;
use QR\DebugProfiler;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
abstract class SyntaxTree
{
    protected $_root;
    //
    protected $_dynamicValues;
    //
    protected $_nodeFunc;
    //
    protected $_runtimeContxt;

    public function __construct(TreeNode $rootNode = null, RuntimeContext $runtimeContxt = null)
    {
        $this->_root = $rootNode;
        $this->_dynamicValues = [];
        //
        $this->_nodeFunc  = null;
        //
        $this->_runtimeContxt = $runtimeContxt;
    }
    /**
     * Set root node
     *
     **/
    public function setRoot(TreeNode $rootNode)
    {
        return $this->_root = $rootNode;
    }
    /**
     * Get root node
     *
     **/
    public function getRoot()
    {
        return $this->_root;
    }
    /**
     * Set Runtime Context
     *
     **/
    public function setRuntimeContext(RuntimeContext $context)
    {
        $this->_runtimeContxt = $context;
        return $this;
    }

    public function getRuntimeContext()
    {
        return $this->_runtimeContxt;
    }
    /**
     * Eval node when traverse
     *
     **/
    public function setNodeFunc(callable $func = null)
    {
        $this->_nodeFunc = $func;
        return $this;
    }

    public function preOrderTraverse(callable $callbackOnNode = null, $param = null)
    {
        if($this->_root) {
            $nodeStack   = [];
            $currentNode = $this->_root;
            do {
                if($left = $currentNode->getLeft()) {
                    $nodeStack[] = $currentNode;
                }
                if($callbackOnNode) {
                    $callbackOnNode($currentNode, $param);
                }
                if($left) {
                    $currentNode = $left;
                } elseif($right = $currentNode->getRight()) {
                    $currentNode = $right;
                } else {
                    while($currentNode && !($right = $currentNode->getRight())) {
                        $currentNode = array_pop($nodeStack);
                    }
                    if($currentNode) {
                        $currentNode = $right;
                    }
                }
            } while($currentNode);
        }
        return $this;
    }

    public function postOrderTraverse(callable $callbackOnNode = null, $param = null)
    {
        if($this->_root) {
            $nodeStack   = [$this->_root,];
            $currentNode = end($nodeStack);
            $currentNode->visited = 0;
            while(count($nodeStack)) {
                while(($left = $currentNode->getLeft()) && !$currentNode->visited) {
                    $nodeStack[] = $left;
                    $currentNode = $left;
                    $left->visited = 0;
                }
                if(!$currentNode->visited) {
                    $currentNode->visited++;
                }
                if(($right = $currentNode->getRight()) && 1 == $currentNode->visited) {
                    $currentNode = $right;
                    $nodeStack[] = $right;
                    $right->visited = 0;
                } else {
                    ++$currentNode->visited;
                    while(2 == $currentNode->visited) {
                        array_pop($nodeStack);
                        if($callbackOnNode) {
                            $callbackOnNode($currentNode, $param);
                        }
                        if(false === ($currentNode = end($nodeStack))) {
                            break;
                        }
                        ++$currentNode->visited;
                    }
                }
            }
        }
        return $this;
    }

    public function levelOrderTraverse(callable $callbackOnNode = null, &$param = null)
    {
        if($this->_root) {
            $queue = [$this->_root];
            while(!empty($queue)) {
                $currentNode = array_shift($queue);
                if(is_callable($callbackOnNode)) {
                    $callbackOnNode($currentNode, $param);
                }
                $left = $currentNode->getLeft();
                $right= $currentNode->getRight();
                if($left) {
                    $queue[] = $left;
                }
                if($right) {
                    $queue[] = $right;
                }
            }
        }
        return $this;
    }

    public function existCycle()
    {
        if(!$this->_root) {
            return false;
        }
        $nodeStack   = [$currentNode = $this->_root];
        $visitedFlag = [];
        while(count($nodeStack)) {
            $objectKey = spl_object_hash($currentNode);
            if(isset($visitedFlag[$objectKey])) {
                return true;
            } else {
                $visitedFlag[$objectKey] = 1;
            }
            if($left = $currentNode->getLeft()) {
                $nodeStack[] = ($currentNode = $left);
            } elseif($right = $currentNode->getRight()) {
                $nodeStack[] = ($currentNode = $right);
            } else {
                array_pop($nodeStack);
                while(($currentNode = array_pop($nodeStack))
                    && !($right = $currentNode->getRight())) {
                }
                if($right) {
                    $nodeStack[] = ($currentNode = $right);
                }
            }
        }
        return false;
    }

    public function getValueInfo($dryrun = false)
    {   
        $rootNode = $this->getRoot();
        $this->postOrderTraverse($this->_nodeFunc, $dryrun);
        return $rootNode->getValueInfo();
    }

    public function store()
    {
        $rootNode = $this->getRoot();
        $this->preOrderTraverse($this->_nodeFunc);
        return $rootNode->getNodeInfo('id');
    }

    static public function newTreeNode(array $nodeInfo)
    {
        throw new \Exception('Unimplemented method: newTreeNode()');
    }

    static public function getNodesByRootId($rootId)
    {
        throw new \Exception('Unimplemented method: getNodesByRootId()');
    }

    static public function buildTreeByRootId($rootId)
    {
        static $syntaxTreeSetCache = [];
        DebugProfiler :: start();
        if(!isset($syntaxTreeSetCache[$rootId])) {
            if(empty($allNodesSet = static :: getNodesByRootId($rootId))) {
                DebugProfiler :: end();
                return null;
            }
            $treeNodeMap = [];
            $treeNodeSet = [];
            $rootNode    = null;
            foreach($allNodesSet as $node) {
                if(!isset($treeNodeMap[$parentId = $node->parent])) {
                    $treeNodeMap[$parentId] = [];
                }
                $treeNodeMap[$parentId][]    = $node->id;
                if(($cnt = count($treeNodeMap[$parentId])) > 2) {
                    throw new \Exception(sprintf('Syntax tree: %d corrupted, %d children found for node: %d', $rootId, $cnt, $node->id));
                }
                if($parentId > 0) {
                    if($rootId != $node->root_id) {
                        throw new \Exception(sprintf('Actual root id: %d != %d given', $node->root_id, $rootId));
                    }
                }
            }
            foreach($treeNodeMap as $parentId => $lr) {
                if($parentId <= 0) {
                    if(count($lr) != 1 || $rootNode) {
                        throw new \Exception(sprintf('Syntax tree: %d corrupted, multi-root node found.', $rootId));
                    }
                    $rootNode              =
                    $treeNodeSet[end($lr)] = static :: newTreeNode($allNodesSet[end($lr)]->getValues());
                } else {
                    if(empty($treeNodeSet[$parentId])) {
                        $treeNode               =
                        $treeNodeSet[$parentId] = static :: newTreeNode($allNodesSet[$parentId]->getValues());
                    } else {
                        $treeNode = $treeNodeSet[$parentId];
                    }
                    $lid      = min($lr);
                    $leftNode = static :: newTreeNode($allNodesSet[$lid]->getValues());
                    $treeNode->setLeft($leftNode);
                    $treeNodeSet[$lid] = $leftNode;
                    if(2 == count($lr)) {
                        if(($rid = max($lr)) == $lid) {
                            throw new \Exception(sprintf('Syntax tree: %d corrupted, left/right children same for node: %d', $rootId, $parentId));
                        }
                        $rightNode= static :: newTreeNode($allNodesSet[$rid]->getValues());
                        $treeNode->setRight($rightNode);
                        $treeNodeSet[$rid] = $rightNode;
                    }
                }
            }
            $conditionTree = new static($rootNode);
            if($conditionTree->existCycle()) {
                throw new \Exception(sprintf('Syntax tree is corrupted(cycle found), please check tree: %d', $rootId));
            }
            foreach($treeNodeSet as $node) {
                $node->setTree($conditionTree);
            }
            $syntaxTreeSetCache[$rootId] = $conditionTree;
        }
        DebugProfiler :: end();
        return $syntaxTreeSetCache[$rootId];
    }

    static protected function equalTree(TreeNode $treeRootA = null, TreeNode $treeRootB = null)
    {
        if((empty($treeRootA) && !empty($treeRootB))
            || (!empty($treeRootA) && empty($treeRootB))) {
            return false;
        } elseif(empty($treeRootA) && empty($treeRootB)) {
            return true;
        } else {
            if($treeRootA->equal($treeRootB)) {
                return static :: equalTree($treeRootA->getLeft(), $treeRootB->getLeft())
                    && static :: equalTree($treeRootA->getRight(), $treeRootB->getRight());
            } else {
                return false;
            }
        }
    }

    public function equal(SyntaxTree $tree)
    {
        return static :: equalTree($this->getRoot(), $tree->getRoot());
    }
}
