<?php namespace QR\Xngine;
/**
 * Author: lori@flashbay.com
 *
 * @Warning: NEVER change below codes unless your are clear what you are doing
 *
 **/
class NodeFunction
{
    const FUNC_NOP      = 0x0;//No operation
    const FUNC_NOT      = 0x1;//not
    const FUNC_CEIL     = 0x2;//ceiling
    const FUNC_FLOOR    = 0x3;//Floor
    const FUNC_INT      = 0x4;//intval
    const FUNC_DECIMAL  = 0x5;//decimal
    const FUNC_SIGN     = 0x6;//sign
    const FUNC_TOUPPER  = 0x7;//strtoupper
    const FUNC_TOLOWER  = 0x8;//strtolower
    const FUNC_FLOAT    = 0x9;//floatval
    const FUNC_IS_EMPTY = 0xA;//is_empty
    const FUNC_NOT_EMPTY= 0xB;//not null
    const FUNC_SUM      = 0xC;//sum sum([a1, a2, ..., an]) == a1 + a2 + ... + an
    const FUNC_MULTIPLY = 0xD;//multiply multiply([a1, a2, a3, ..., an]) == a1 * a2 * a3 * ...* an
    const FUNC_SQRT     = 0xE;//sqrt
    const FUNC_POWER2   = 0xF;//power2 power2(x) == x*x, power2([a1, a2, ..., an]) = [a1*a1, a2*a2, ..., an*an]
    const FUNC_ABS      = 0x10;//abs abs(x) = |x|, abs([a1, a2,...,an]) = [|a1|, |a2|, ..., |an|]
    const FUNC_LN       = 0x11;//ln
    const FUNC_LOG10    = 0x12;//log10
    const FUNC_FMIN     = 0x13;//fmin fmin(x) == x, fmin([a1, a2, ..., an]) = min(a1, a2, ..., an)
    const FUNC_FMAX     = 0x14;//fmax
    const FUNC_ANY_LOWER= 0x15;//anylower: contains any lowercase charactor ?
    const FUNC_ANY_UPPER= 0x16;//anyupper: contains any uppercase charactor ?
    const FUNC_COUNT    = 0x17;//count for array / strlen for string
    const FUNC_CAST_ARR = 0x18;//to_array, cast to an array, similar to (array)$something
    const FUNC_ARR_PACK = 0x19;//array_pack, array($something)
    const FUNC_FIRST_KEY= 0X1A;//first_key
    const FUNC_FIRST_VAL= 0X1B;//first_val
    const FUNC_LAST_KEY = 0x1C;//last_key
    const FUNC_LAST_VAL = 0x1D;//last_val
    const FUNC_ESCAPE_HTML_ENTITY_DECODE = 0x1E;//escape_html_entity_decode
    const FUNC_IS_NULL  = 0x1F;
    const FUNC_NOT_NULL = 0x20;
    const FUNC_UNIQUE = 0x21;//unique
    const FUNC_FILTER = 0x22;//filter
    //
    static private $_funcMap = [
        '', 'not', 'ceiling', 'floor', 'intval', 'decimal', 'sign',
        'strtoupper', 'strtolower', 'floatval', 'is_empty', 'not_empty',
        'sum', 'multiply', 'sqrt', 'power2', 'abs', 'ln', 'log10', 'fmin', 'fmax', 'anylower', 'anyupper',
        'count', 'to_array', 'array_pack',
        'first_key', 'first_val', 'last_key', 'last_val',
        'escape_html_entity_decode', 'is_null', 'not_null',
        'unique', 'filter',
    ];

    static public function getFunctionList()
    {
        return range(0, 0x22);
    }

    static public function isFunctionValid($function)
    {
        return 0 <= $function && $function <= 0x22;
    }

    static public function getFunctionTxt($function)
    {
        if(!static :: isFunctionValid($function)) {
            throw new \Exception(sprintf('Unsupported function: 0x%X', $function));
        }
        return static :: $_funcMap[intval($function)];
    }

    static public function getFunctionNum($functionTxt)
    {
        $functionTxt = preg_replace('#\s{2,}#', ' ', strtolower(trim($functionTxt)));
        if(false === ($index = array_search($functionTxt, static :: $_funcMap))) {
            throw new \Exception(sprintf('Unsupported function: %s', $functionTxt));
        }
        return $index;
    }

    static private function _doFunc($function, $value)
    {
        $r = [];
        foreach($value as $v) {
            $r[] = static :: doFunc($function, $v);
        }
        return $r;
    }

    static public function doFunc($function, $value)
    {
        if(!static :: isFunctionValid($function)) {
            throw new \Exception(sprintf('Unsupported function: 0x%X', $function));
        }
        switch($function) {
        case static :: FUNC_NOT:
            if(!is_array($value)) {
                $value = !$value;
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_CEIL:
            if(!is_array($value)) {
                $value = ceil($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_FLOOR:
            if(!is_array($value)) {
                $value = floor($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_INT:
            if(!is_array($value)) {
                $value = intval($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_DECIMAL:
            if(!is_array($value)) {
                $value = $value - intval($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_SIGN:
            if(!is_array($value)) {
                $value = $value >= 0 ? 1 : -1;
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_TOUPPER:
            if(!is_array($value)) {
                $value = strtoupper($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_TOLOWER:
            if(!is_array($value)) {
                $value = strtolower($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_FLOAT:
            if(!is_array($value)) {
                $value = floatval($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_IS_EMPTY:
            if(!is_array($value)) {
                $value = empty($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_NOT_EMPTY:
            $value = static :: doFunc(static :: FUNC_NOT, static :: doFunc(static :: FUNC_IS_EMPTY, $value));
            break;
        case static :: FUNC_SUM:
            if(!is_array($value)) {
                $value = floatval($value);
            } else {
                $value = array_sum(static :: _doFunc($function, $value));
            }
            break;
        case static :: FUNC_MULTIPLY:
            if(!is_array($value)) {
                $value = floatval($value);
            } else {
                $r = 1;
                foreach($value as $v) {
                    $r *= static :: doFunc($function, $v);
                }
                $value = $r;
            }
            break;
        case static :: FUNC_SQRT:
            if(!is_array($value)) {
                $value = sqrt(floatval($value));
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_POWER2:
            if(!is_array($value)) {
                $value = floatval($value) * floatval($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_ABS:
            if(!is_array($value)) {
                $value = abs($value);
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_LN:
            if(!is_array($value)) {
                $value = log(floatval($value));
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_LOG10:
            if(!is_array($value)) {
                $value = log10(floatval($value));
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_FMIN:
            if(!is_array($value)) {
                $value = floatval($value);
            } else {
                if(empty($value)) {
                    $value = 0;
                } else {
                    $min = static :: doFunc($function, current($value));
                    while($c = next($value)) {
                        $t = static :: doFunc($function, $c);
                        if($t < $min) $min = $t;
                    }
                    $value = $min;
                }
            }
            break;
        case static :: FUNC_FMAX:
            if(!is_array($value)) {
                $value = floatval($value);
            } else {
                if(empty($value)) {
                    $value = 0;
                } else {
                    reset($value);
                    $max = null;
                    foreach($value as $v) {
                        $t = static :: doFunc($function, $v);
                        if(null == $max) {
                            $max = $t;
                        } elseif($t > $max) {
                            $max = $t;
                        }
                    }
                    $value = $max;
                }
            }
            break;
        case static :: FUNC_ANY_LOWER:
            if(!is_array($value)) {
                $value = preg_match('#[a-z]#', $value) > 0;
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_ANY_UPPER:
            if(!is_array($value)) {
                $value = preg_match('#[A-Z]#', $value) > 0;
            } else {
                $value = static :: _doFunc($function, $value);
            }
            break;
        case static :: FUNC_COUNT:
            if(empty($value)) {
                $value = 0;
            } elseif(is_array($value)) {
                $value = count($value);
            } elseif(is_string($value)) {
                $value = strlen($value);
            } else {
                $value = 1;
            }
            break;
        case static :: FUNC_CAST_ARR:
            if(!is_array($value)) {
                $value = (array)$value;
            }
            break;
        case static :: FUNC_ARR_PACK:
            $value = array($value);
            break;
        case static :: FUNC_FIRST_VAL:
            if(is_array($value)) {
                reset($value);
                $value = current($value);
            } else {
                $value = null;
            }
            break;
        case static :: FUNC_FIRST_KEY:
            if(is_array($value)) {
                $value = current(array_keys($value));
            } else {
                $value = null;
            }
            break;
        case static :: FUNC_LAST_VAL:
            if(is_array($value)) {
                reset($value);
                $value = end($value);
            } else {
                $value = null;
            }
            break;
        case static :: FUNC_LAST_KEY:
            if(is_array($value)) {
                $value = end(array_keys($value));
            } else {
                $value = null;
            }
            break;
        case static :: FUNC_ESCAPE_HTML_ENTITY_DECODE:
            if(!is_array($value)) {
                $value = preg_replace('#%u([A-Fa-f0-9]{4})#', '&#x$1', $value);
                $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
            } else {
                $r = [];
                foreach($value as $v) {
                    $r[] = static :: doFunc(static :: FUNC_ESCAPE_HTML_ENTITY_DECODE, $v);
                }
                $value = $r;
            }
            break;
        case static :: FUNC_IS_NULL:
            if(!is_array($value)) {
                $value = is_null($value);
            } else {
                $r = [];
                foreach($value as $v) {
                    $r[] = static :: doFunc(static :: FUNC_IS_NULL, $v);
                }
                $value = $r;
            }
            break;
        case static :: FUNC_NOT_NULL:
            $value = static :: doFunc(static :: FUNC_NOT, static :: doFunc(static :: FUNC_IS_NULL, $value));
            break;
        case static :: FUNC_UNIQUE:
            if(is_array($value)) {
                $value = array_unique($value);//@NOTE: array_unique may cause PHP error when $value contains object
            }
            break;
        case static :: FUNC_FILTER:
            if(is_array($value)) {
                $r = [];
                foreach($value as $v) {
                    if(!empty($v)) {
                        $r[] = $v;
                    }
                }
                $value = $r;
            } else {
                $value = empty($value) ? null : $value;
            }
            break;
        }
        return $value;
    }
}
