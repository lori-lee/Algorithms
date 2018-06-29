<?php namespace QR\Xngine;
/**
 * Author: lori@flashbay.com
 *
 **/
use QR\DAO\ExpressionNodes as NodeDAO;
use QR\Xngine\ExpressionTreeNode as TreeNode;
use QR\Xngine\SyntaxTree;

class ExpressionTree extends SyntaxTree
{
    public function __construct(TreeNode $rootNode = null)
    {
        parent :: __construct($rootNode);
    }
    
    static public function getNodesByRootId($rootId)
    {
        return NodeDAO :: getNodesByRootId($rootId);
    }

    static public function newTreeNode(array $nodeInfo)
    {
        return new TreeNode($nodeInfo);
    }

    public function getValueInfo($dryrun = false)
    {
        $this->setNodeFunc(['QR\\Xngine\\ExpressionTreeNode', 'nodeEval']); 
        return parent :: getValueInfo($dryrun);
    }

    public function store()
    {
        $this->setNodeFunc(['QR\\Xngine\\ExpressionTreeNode', 'nodeStore']); 
        return parent :: store();
    }
}
