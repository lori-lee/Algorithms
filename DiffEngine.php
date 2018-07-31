<?php namespace QR\Xngine;
/**
 *
 * @Author: lori@flashbay.com
 * 
 *
 **/
use QR\DebugProfiler;
/**
 *
 * @WARNING: NEVER change below codes unless you're clear what you are doing
 *
 **/
class DiffEngine
{
    static public function isalphabet($char)
    {
        return preg_match('#^[a-z.\-_]$#i', $char) > 0;
    }

    static public function split2Word($string, $encoding = 'UTF-8')
    {
        $array = [];
        $word  = '';
        $status= 0;
        for($i = 0, $len = mb_strlen($string, $encoding); $i < $len; ++$i) {
            $char = mb_substr($string, $i, 1, $encoding);
            switch($status) {
            case 0:
                if(static :: isalphabet($char)) {
                    $status = 1;
                    $word   = $char;
                } else {
                    $array[] = $char;
                    if('\\' == $char) {
                        $nextChar = mb_substr($string, $i + 1, 1, $encoding);
                        if(static :: isalphabet($nextChar)) {
                            ++$i;
                            $array[] = $nextChar;
                        }
                    }
                }
                break;
            case 1:
                if(static :: isalphabet($char)) {
                    $word .= $char;
                } else {
                    $status = 0;
                    $array[]= $word;
                    $array[]= $char;
                    $word   = '';
                }
                break;
            }
        }
        if($word) {
            $array[] = $word;
        }
        return $array;
    }

    static public function diff($stringA, $stringB, $spliter = null, $equalCmp = null, $encoding = 'UTF-8', $prefix = '<span style="color:red;font-weight:700;">', $suffix = '</span>')
    {
        DebugProfiler :: start();
        if(is_callable($spliter)) {
            $arrayA = call_user_func($spliter, $stringA, $encoding);
            $arrayB = call_user_func($spliter, $stringB, $encoding);
        } else {
            $arrayA = static :: split2Word($stringA, $encoding);
            $arrayB = static :: split2Word($stringB, $encoding);
        }
        $maxCommonLen  = []; 
        $commonCharPos = []; 
        for($i = count($arrayA), $j = count($arrayB); $i >= 0; --$i) {
            $key = $i . ',' . $j; 
            $maxCommonLen[$key] = 0;
            $commonCharPos[$key]= [[], []];
        }   
        for($i = count($arrayA), $j = count($arrayB); $j >= 0; --$j) {
            $key = $i . ',' . $j; 
            $maxCommonLen[$key] = 0;
            $commonCharPos[$key]= [[], []];
        }   
        for($i = count($arrayA) - 1; $i >= 0; --$i) {
            for($j = count($arrayB) - 1; $j >= 0; --$j) {
                $key  = $i . ',' . $j; 
                $key2 = ($i + 1) . ',' . $j; 
                $key3 = $i . ',' . ($j + 1); 
                if(is_callable($equalCmp)) {
                    $r = call_user_func($equalCmp, $arrayA[$i], $arrayB[$j]);
                } else {
                    $r = ($arrayA[$i] == $arrayB[$j]);
                }
                if($r) {
                    $key1 = ($i + 1) . ',' . ($j + 1); 
                    $maxCommonLen[$key] = 1 + $maxCommonLen[$key1];
                    $commonCharPos[$key]= [[$i, $j], [$i + 1, $j + 1]];
                } else {
                    if($maxCommonLen[$key2] >= $maxCommonLen[$key3]) {
                        $maxCommonLen[$key] = $maxCommonLen[$key2];
                        $commonCharPos[$key]= [[], [$i + 1, $j]];
                    } else {
                        $maxCommonLen[$key] = $maxCommonLen[$key3];
                        $commonCharPos[$key]= [[], [$i, $j + 1]];
                    }   
                }   
            }   
        }   
        $maxCommonStr = ''; 
        $key = '0,0';
        $indexA = []; 
        $indexB = []; 
        while(!empty($commonCharPos[$key])) {
            $t = $commonCharPos[$key];
            if(!empty($t0 = $t[0])) {
                $maxCommonStr .= $arrayA[$t0[0]];
                $indexA[] = $t0[0];
                $indexB[] = $t0[1];
            }   
            if(!empty($t1 = $t[1])) {
                $key = $t1[0] . ',' . $t1[1];
            } else {
                break;
            }   
        }   
        DebugProfiler :: end();
        return [
            $maxCommonLen['0,0'], $maxCommonStr,
            static :: mark($arrayA, $indexA, $prefix, $suffix),
            static :: mark($arrayB, $indexB, $prefix, $suffix),
        ];  
    }

    static public function mark($array, $index, $prefix = '<span style="color:red;">', $suffix = '</span>')
    {
        DebugProfiler :: start();
        $arrayStr = [];
        $status   = 0;
        for($i = $j = 0; $i < count($array); ++$i) {
            if(isset($index[$j])) {
                if($i < $index[$j]) {
                    if(!$status) {
                        $arrayStr[] = $prefix;
                        $status = 1;
                    }
                } else {
                    if($status) {
                        $arrayStr[] = $suffix;
                        $status = 0;
                    }
                    ++$j;
                }
            } else {
                if(!$status) {
                    $arrayStr[] = $prefix;
                    $status = 1;
                }
            }
            $arrayStr[] = $array[$i];
        }
        if($status) $arrayStr[] = $suffix;
        DebugProfiler :: end();
        return implode('', $arrayStr);
    }
}
