<?php namespace QR;
/**
 * Author: lori@flashbay.com
 *
 **/
use QR\VariableManager;


/**
 *         +-------------+    +-------------+                         +-------------+
 * null <- |        |    | -> |        |    |                      <- |        |    |       
 *         |previous|next| <- |previous|next| ->     .......          |previous|next| -> null
 *         +--------+----+    +--------+----+                         +--------+----+
 *
 **/
class RuntimeContext
{
    private $_varManager;
    private $_get;
    private $_post;
    private $_cookie;
    private $_request;
    private $_files;
    //
    public function __construct()
    {
        $this->_varManager= new VariableManager;
    }

    public function setWebParams($get = [], $post = [], $request = [], $cookie = [], $files = [])
    {
        $this->_get     = $get;
        $this->_post    = $post;
        $this->_request = $request;
        $this->_cookie  = $cookie;
        $this->_files   = $files;
        return $this;
    }

    public function getGet($key = null)
    {
        if($key !== null) {
            return isset($this->_get[$key]) ? $this->_get[$key] : null;
        }
        return $this->_get;
    }

    public function getPost($key = null)
    {
        if($key !== null) {
            return isset($this->_post[$key]) ? $this->_post[$key] : null;
        }
        return $this->_post;
    }

    public function getCookie($key = null)
    {
        if($key !== null) {
            return isset($this->_cookie[$key]) ? $this->_cookie[$key] : null;
        }
        return $this->_cookie;
    }

    public function getRequest($key = null)
    {
        if($key !== null) {
            return isset($this->_request[$key]) ? $this->_request[$key] : null;
        }
        return $this->_request;
    }

    public function getFiles($key = null)
    {
        if($key !== null) {
            return isset($this->_files[$key]) ? $this->_files[$key] : null;
        }
        return $this->_files;
    }

    public function setVarManager(VariableManager $varManager = null)
    {
        $this->_varManager = $varManager;
        return $this;
    }

    public function getVarManager()
    {
        return $this->_varManager;
    }
}
