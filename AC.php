<?php
class TrieNode
{
    const TYPE_TERM = 0x0;
    const TYPE_INNER= 0x1;

    public $parent;
    public $failChain;
    public $children;

    public $value;

    public $type;

    public $wordList = array ();

    public function __construct ($parent = null, $value = null, $type = self :: TYPE_INNER, $failChain = null, $children = array (), $wordList = array ())
    {
        $this->parent = $parent;
        $this->failChain = $failChain;

        $this->value     = $value;
        $this->type      = $type;

        $this->children  = $children;
        $this->wordList  = $wordList;
    }

    public function addRefWord ($word)
    {
        if (!in_array ($word, $this->wordList)) {
            array_push ($this->wordList, $word);
        }
        return $this;
    }
}

class AC
{
    private $_trieRoot;

    private $_threaded;

    public function __construct ()
    {
        $this->_threaded = false;

        $this->_trieRoot = new TrieNode;

        $this->_trieRoot->parent   = null;
        $this->_trieRoot->failChain= null;

        $this->_trieRoot->children= array ();
        $this->_trieRoot->value   = null;
        $this->_trieRoot->type    = TrieNode :: TYPE_INNER;
    }

    protected function _getLetter ($word, $index = 0)
    {
        return mb_substr ($word, $index, 1);
    }

    protected function _enQueue (&$queue, $node)
    {
        array_push ($queue, $node);
        return $queue;
    }

    protected function _deQueue (&$queue)
    {
        return array_shift ($queue);
    }

    public function addWord ($word)
    {
        if (!empty ($word)) {
            $currentNode = $this->_trieRoot;
            $index       = 0;
            while ($letter = $this -> _getLetter ($word, $index++)) {
                if (!isset ($currentNode->children[$letter])) {
                    $currentNode->children[$letter] = new TrieNode ($currentNode, $letter);
                }
                $currentNode = $currentNode->children[$letter];
            }
            $currentNode->type = TrieNode :: TYPE_TERM;
            $currentNode -> addRefWord ($word);
        }
        $this->_threaded = false;
    }

    protected function _threadNodes ()
    {
        $currentNode = $this->_trieRoot;
        $currentNode->failChain = $currentNode;
        $nodeQueue   = $currentNode->children;
        while ($QNode = $this -> _deQueue ($nodeQueue)) {
            $parent = $QNode->parent;
            while ($parent->parent && !isset ($parent->failChain->children[$QNode->value])) {
                $parent = $parent->failChain;
            }
            if (!$parent->parent) {
                $QNode->failChain = $parent;
            } else {
                $QNode->failChain = $parent->failChain->children[$QNode->value];
            }
            if (TrieNode :: TYPE_TERM == $QNode->failChain->type) {
                $QNode->type = TrieNode :: TYPE_TERM;
                foreach ($QNode->failChain->wordList as $_word) { 
                    $QNode->addRefWord ($_word);
                }
            }

            foreach ($QNode->children as $_child) {
                $this -> _enQueue ($nodeQueue, $_child);
            }
        }
        $this->_threaded = true;
    }

    public function addWords (array $words)
    {
        foreach ($words as $_word) {
            $this -> addWord ($_word);
        }
        $this -> _threadNodes ();
    }

    public function match ($haystack)
    {
        if (!$this->_threaded) {
            throw new \RuntimeException ('Please build trie tree by calling method: addWords ($words) first.');
        }
        $index = 0;
        $wordsMatched = array ();
        $currentNode = $this->_trieRoot;
        while ($letter = $this -> _getLetter ($haystack, $index++)) {
            if (isset ($currentNode->children[$letter])) {
                $currentNode = $currentNode->children[$letter];
            } else {
                while ($currentNode->parent && !isset ($currentNode->children[$letter])) {
                    $currentNode = $currentNode->failChain;
                }
                $currentNode = $currentNode->failChain;
            }
            if (TrieNode :: TYPE_TERM == $currentNode->type) {
                $wordsMatched[] = array ($index - 1 => $currentNode->wordList);
            }
        }
        return $wordsMatched;
    }
}

$acInst = new AC;
$words = array ('first', 'second', 'cond', 'firstly', 'st', '关键字', '多关键字匹配', '匹配', '精确匹配');
$haystack = 'firstly speaking, there will not be a firstly second secondary device for such purpose.开始测试多关键字匹配是否是精确匹配。结束了';

$acInst -> addWords ($words);
$r = $acInst -> match ($haystack);
var_dump ($r);
