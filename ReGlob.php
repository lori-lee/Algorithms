<?php namespace QR\Xngine;
/**
 *
 * @Author: lori@flashbay.com
 *
 * Below is a reduced version of glob
 * Only ? and * are used for wildcard
 *
 * @WARNING: NEVER change below codes until you are clear what you are doing.
 *
 **/
use QR\DebugProfiler;
use QR\Xngine\SimpleFileFormatParser;

class ReGlob
{
    static public function match($pattern, $haystack, $ignoreCase = false, $encoding = 'UTF-8')
    {
        DebugProfiler :: start();
        $pattern = preg_replace('#\*{2,}#', '*', $pattern);
        if($ignoreCase) {
            $pattern = strtolower($pattern);
            $haystack= strtolower($haystack);
        }
        $patternChars = SimpleFileFormatParser :: split2Char($pattern, $encoding);
        $haystackChars= SimpleFileFormatParser :: split2Char($haystack, $encoding);
        $matched = false;
        $la = count($patternChars);
        $lb = count($haystackChars);
        if(!$la || !$lb) {
            $matched = $la ? (1 == $la && '*' == $patternChars[0]) : !$lb;
        } else {
            $flag = [$la . ',' . $lb => true,];
            for($j = $lb - 1; $j >= 0; --$j) {
                $flag[$la . ',' . $j] = false;
            }
            for($i = $la - 1; $i >= 0; --$i) {
                $flag[$i . ',' . $lb] = false;
            }
            $flag[($la - 1) . ',' . $lb] = ('*' == $patternChars[$la - 1]);
            for($i = $la - 1; $i >= 0; --$i) {
                for($j = $lb - 1; $j >= 0; --$j) {
                    $k = $i . ',' . $j;
                    if('*' == $patternChars[$i]) {
                        $flag[$k] = $flag[($i + 1) . ',' . $j] || $flag[$i . ',' . ($j + 1)];
                    } elseif('?' == $patternChars[$i] || $patternChars[$i] == $haystackChars[$j]) {
                        $flag[$k] = $flag[($i + 1) . ',' . ($j + 1)];
                    } else {
                        $flag[$k] = false;
                    }
                }
            }
            $matched = $flag['0,0'];
        }
        DebugProfiler :: end();
        return $matched;
    }
}
