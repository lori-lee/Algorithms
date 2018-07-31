<?php namespace QR\Xngine;
/**
 * @Author: Lori@flashbay.com
 * 
 * @WARNING NEVER change below codes unless you are clear what you are doing.
 * Reference RFC4180
 * https://tools.ietf.org/html/rfc4180
 *
 **/
use Form\Util;
use QR\DebugProfiler;
use QR\Xngine\Tokenizer;

class SimpleFileFormatParser
{
    static public function parse($input, $separator = ',', $encoding = 'UTF-8', $noBlank = true)
    {
        if('"' == $separator) {
            throw new \Exception('Separator should NEVER be double quote(")');
        }
        DebugProfiler :: start();
        $allRowCols = [];
        $wChars     = static :: split2Char($input, $encoding);
        $len        = count($wChars);
        list($status, $charCache, $doubleQuoted, $lastDoubleQuoteIndex) = [0, [], false, null];
        $separatorLen = mb_strlen($separator, $encoding);
        $row = 0;
        for($i = 0; $i < $len; ++$i) {
            $currentChar = $wChars[$i];
            $charCache[] = $currentChar;
            $cacheLen    = count($charCache);
            $txtChecking = '';
            if($cacheLen >= $separatorLen) {
                $txtChecking = implode('', array_slice($charCache, $cacheLen - $separatorLen, $separatorLen));
            }
            if(!isset($allRowCols[$row])) {
                $allRowCols[$row] = [];
            }
            if(!$status) {
                if($txtChecking == $separator) {
                    $allRowCols[$row][] = '';
                    list($charCache, $doubleQuoted, $lastDoubleQuoteIndex) = [[], false, null];
                } elseif('"' == $currentChar) {
                    list($status, $doubleQuoted) = [1, true];
                } elseif("\r" == $currentChar || "\n" == $currentChar) {
                    $allRowCols[$row++][] = '';
                    list($charCache, $doubleQuoted) = [[], false];
                    if("\r" == $currentChar && $i + 1 < $len && "\n" == $wChars[$i + 1]) {
                        ++$i;
                    }
                } else {
                    $status = 1;
                }
            } elseif(1 == $status) {
                if($txtChecking == $separator) {
                    if(!$doubleQuoted) {
                        $allRowCols[$row][] = implode('', array_slice($charCache, 0, $cacheLen - $separatorLen));
                        list($status, $charCache, $doubleQuoted, $lastDoubleQuoteIndex) = [0, [], false, null];
                    } else {
                    }
                } elseif('"' == $currentChar) {
                    $lastDoubleQuoteIndex = $cacheLen - 1;
                    if($doubleQuoted) {
                        if($i + 1 < $len && '"' == $wChars[$i + 1]) {
                            ++$i;
                        } else {
                            $status = 2;
                        }
                    }
                } elseif("\r" == $currentChar || "\n" == $currentChar) {
                    if(!$doubleQuoted) {
                        array_pop($charCache);
                        $allRowCols[$row++][] = implode('', $charCache);
                        $charCache = [];
                        if("\r" == $currentChar && $i + 1 < $len && "\n" == $wChars[$i + 1]) {
                            if(++$i + 1 < $len) {
                                list($status, $doubleQuoted) = [0, [], false];
                            }
                        }
                    }
                }
            } else {
                if($txtChecking == $separator) {
                    $allRowCols[$row][] = implode('', array_slice($charCache, 0, $lastDoubleQuoteIndex + 1));
                    list($status, $charCache, $doubleQuoted, $lastDoubleQuoteIndex) = [0, [], false, null];
                } elseif("\r" == $currentChar || "\n" == $currentChar) {
                    $allRowCols[$row++][] = implode('', array_slice($charCache, 0, $lastDoubleQuoteIndex + 1));
                    if("\r" == $currentChar && $i + 1 < $len && "\n" == $wChars[$i + 1]) {
                        ++$i;
                    }
                    list($status, $charCache, $doubleQuoted, $lastDoubleQuoteIndex) = [0, [], false, null];
                }
            }
        }
        if(!$status && $len || !empty($charCache)) {
            $allRowCols[$row][] = implode('', $charCache);
        }
        foreach($allRowCols as $row => &$rowCols) {
            foreach($rowCols as $col => &$colVal) {
                if(($len = mb_strlen($colVal, $encoding)) >= 2) {
                    $firstChar = mb_substr($colVal, 0, 1, $encoding);
                    $lastChar  = mb_substr($colVal, $len - 1, 1, $encoding);
                    if($firstChar == $lastChar && '"' == $firstChar) {
                        $colVal = mb_substr($colVal, 1, $len - 2, $encoding);
                    }
                    if($noBlank) {
                        $colVal = trim($colVal);
                    }
                }
            }
        }
        DebugProfiler :: end();
        return $allRowCols;
    }

    static public function pack(array $allRowsCols, $separator = ',', $newLine = "\r\n")
    {
        DebugProfiler :: start();
        $rowResult = [];
        $rowIndex  = 0;
        foreach($allRowsCols as $rowCols) {
            if(is_array($rowCols)) {
                if(!isset($rowResult[$rowIndex])) {
                    $rowResult[$rowIndex] = [];
                }
                foreach($rowCols as $colVal) {
                    $rowResult[$rowIndex][] = sprintf('"%s"', str_replace('"', '""', Util :: strVal($colVal)));
                }
                $rowResult[$rowIndex] = implode($separator, $rowResult[$rowIndex]);
                ++$rowIndex;
            } else {
                $rowResult[$rowIndex][] = sprintf('"%s"', str_replace('"', '""', Util :: strVal($rowCols)));
            }
        }
        DebugProfiler :: end();
        if(!is_array($rowResult[0])) {
            return implode($newLine, $rowResult);
        } else {
            return implode($separator, $rowResult[0]);
        }
    }

    static public function split2Char($input, $encoding = 'UTF-8')
    {
        $chars = [];
        if(!in_array(strtoupper($encoding), ['UTF-8', 'UTF8',])) {
            $input = mb_convert_encoding($input, 'UTF-8', $encoding);
        }
        $m = [0, 0, 0, 0, 3];
        for($i = 0, $len = strlen($input); $i < $len;) {
            $char = $input{$i};
            $ord  = ord($char);
            $n  = 0;
            $n += ($ord & 0x80) >> 7 << 1;
            $n += $m[($n & 0x2) << 1] & (-~(($ord & 0x30) >> 4) >> 1);
            $n += $m[($n & 0x4)] & (-~($ord & 0x0C) >> 2) >> 1;
            $n  = max(1, $n);
            $chars[] = substr($input, $i, $n);
            $i += $n;
        }
        return $chars;
    }

    static private function __addWord(array &$words, &$word)
    {
        if(!empty($word)) {
            $words[] = $word;
            $word    = '';
        }
    }

    static public function isCJKC($c, $encoding = 'UTF-8')
    {
        if(strlen($c) > 1) {
            $_c = static :: split2Char($c, $encoding);
            $c  = $_c[0];
            $l  = strlen($c);
            $ord= ord($c{0}) & ((1 << (~$l & 0x7)) - 1);
            for($i = 1; $i < $l; ++$i) {
                $ord = ($ord << 6) | (ord($c{$i}) & 0x3F);
            }
            return 0x4E00 <= $ord && $ord <= 0x9FFF;
        } else {
            return false;
        }
    }

    static public function isPunctuation($c)
    {
        if(strlen($c) == 1) {
            $dec = ord($c);
            return (0x21 <= $dec && $dec <= 0x2F)
                || (0x3A <= $dec && $dec <= 0x40)
                || (0x5B <= $dec && $dec <= 0x60)
                || (0x7B <= $dec && $dec <= 0x7E);
        }
        return false;
    }

    static public function split2Word($input, $encoding = 'UTF-8', $reserverBlank = true)
    {
        $words = [];
        $chars = static :: split2Char($input, $encoding);
        $prevWord = '';
        for($i = 0, $len = count($chars); $i < $len; ++$i) {
            $c = $chars{$i};
            if(Tokenizer :: isBlank($c)) {
                static :: __addWord($words, $prevWord);
                if($reserverBlank && $i && !Tokenizer :: isBlank($chars{$i - 1})) {
                    static :: __addWord($words, $c);
                }
            } elseif(static :: isCJKC($c) || static :: isPunctuation($c)) {
                static :: __addWord($words, $prevWord);
                static :: __addWord($words, $c);
            } else {
                $prevWord .= $c;
            }
        }
        static :: __addWord($words, $prevWord);
        return $words;
    }
}
