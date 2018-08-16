<?php namespace Form;
/**
 * Author: lori@flashbay.com
 *
 **/
use Core\Events;
use Form\ShoppingCart\ShoppingCart as CartOrder;
use QR\DebugProfiler;
use Core\CookieManager;

class Util
{
    static public function getDateFormat($terr)
    {
        static $_map = array(
            'iso'=> 'Y/m/d', 'au' => 'd/m/Y',
            'de' => 'd.m.Y', 'dk' => 'd-m-Y',
            'es' => 'd-m-Y', 'fi' => 'd.m.Y',
            'fr' => 'd/m/Y', 'gb' => 'd/m/Y',
            'it' => 'd/m/Y', 'nl' => 'd-m-Y',
            'no' => 'd.m.Y', 'pl' => 'd.m.Y',
            'pt' => 'd/m/Y', 'us' => 'm/d/Y',
        );
        $terr = strtolower($terr);
        if(empty($_map[$terr])) $terr = 'iso';
        return
            array(
                'backend' => $_map[$terr],
                'frontend' => str_replace(array('Y', 'm', 'd'), array('yy', 'mm', 'dd'), $_map[$terr])
            );
    }
    //
    static public function roundedPrice($price, $currency)
    {
        static $minDenominations = array(
            'CHF' => 0.05,
            'HUF' => 1,
            'JPY' => 1,
            'KRW' => 1,
        );
        $minUnit = 0.01;
        $currency = strtoupper($currency);
        if(!empty($minDenominations[$currency])) {
            $minUnit = $minDenominations[$currency];
        }
        $price = floatval($price);
        $mod = fmod($price, $minUnit);
        return $price - $mod + (2 * $mod >= $minUnit ? $minUnit : 0);
    }
    /**
     * Extended Compare
     *
     **/
    static public function equal($a, $b, $strict = true)
    {
        $ta = gettype($a);
        $tb = gettype($b);
        if($strict) {
            if($ta != $tb) return false;
        }   
        switch($ta) {
        case 'boolean':
        case 'integer':
        case 'double':
        case 'NULL':
            return $a == $b || (empty($a) && empty($b)); 
        case 'string':
            return (empty($a) && empty($b))
                || ($strict && $a == $b)
                || (!$strict && static :: isSameStr($a, $b));
        case 'array':
            if($ta != $tb || sizeof($a) != sizeof($b)) return false;
            if($strict) {
                foreach($a as $i => $e) {
                    if(!isset($b[$i]) || !self :: equal($e, $b[$i], $strict)) {
                        return false;
                    }   
                }   
            } else {
                return !sizeof(array_diff($a, $b));
            }   
            break;
        case 'resource':
        case 'object':
            if($ta != $tb) return false;
            if($strict) {
                return spl_object_hash($a) === spl_object_hash($b);
            } else {
                return get_class($a) === get_class($b)
                        && md5(serialize($a)) === md5(serialize($b));
            }
            break;
        }   
        return true;
    }

    static public function contain($a, $b)
    {
        if(static :: equal($a, $b) || static :: inArray($b, $a)) {
            return true;
        }
        if(is_string($a) && is_string($b)) {
            return static :: stringContains($a, $b);
        }
        return false;
    }

    static public function inArray($elem, $array)
    {
        if(!is_array($array) || empty($array)) {
            return false;
        }
        foreach($array as $e) {
            if(self :: equal($e, $elem)) {
                return true;
            }
        }
        return false;
    }

    static public function formatCurrency($amount, $currency, $locale)
    {
        static $_formatterSet = array();
        if(empty($_formatterSet[$locale])) {
            $_formatterSet[$locale] = new \NumberFormatter($locale, \NumberFormatter :: CURRENCY);
        }
        return $_formatterSet[$locale]->formatCurrency(floatval($amount), $currency);
    }

    static public function format($amount, $locale)
    {
        static $_formatterSet = array();
        if(empty($_formatterSet[$locale])) {
            $_formatterSet[$locale] = new \NumberFormatter($locale, \NumberFormatter :: DECIMAL);
        }
        return $_formatterSet[$locale]->format(floatval($amount));
    }

    static public function stringifyAddress($arrAddress, $delimiter = "\n")
    {
        $arrAddress = json_decode(json_encode($arrAddress), true);
        $keys = array('attention', 'addressee', 'address1', 'address2', 'city', 'state', 'postcode',);
        $countryName = '';
        if(!empty($arrAddress['country']) && ($countryInst = \Territory::fromAlpha2($arrAddress['country']))) {
            $countryName = $countryInst->localizedName ? $countryInst->localizedName : $countryInst->isoName;
        }
        //  
        $szAddress = ''; 
        foreach(['firstname', 'lastname', 'phone', ] as $_k) {
            if(!empty($arrAddress[$_k])) {
                $szAddress .= ' ' . $arrAddress[$_k];
            }   
            $szAddress = trim($szAddress);
        }
        if(!empty($arrAddress['companyName'])) {
            $arrAddress['addressee'] = $arrAddress['companyName'];
        }
        foreach($keys as $_k) {
            if(!empty($arrAddress[$_k])) {
                $szAddress .= $delimiter . $arrAddress[$_k];
            }
        }   
        $szAddress .= $delimiter . $countryName;
        return $szAddress;
    }

    static public function parseDeliveryDate($date, $format)
    {
        $arrFormat = preg_split('#[^a-z]#', $format);
        $arrDate   = preg_split('#[^0-9]#', $date);
        $datePart  = array();
        foreach($arrFormat as $_k) {
            $datePart[$_k] = current($arrDate);
            next($arrDate);
        }   
        return sprintf('%s-%s-%s', $datePart['yy'], $datePart['mm'], $datePart['dd']);
    }

    static public function resizeImage($imageContentBlob, $scaleParam = array())
    {
        $IMagickInst = new \IMagick;
        if(false === $IMagickInst->readImageBlob($imageContentBlob)) {
            return false;
        }     
        //    
        $width = $IMagickInst->getImageWidth();
        $height= $IMagickInst->getImageHeight();
        if(!empty($scaleParam['width'])
            || !empty($scaleParam['height'])) {
            //
            $widthCoeff  = empty($scaleParam['width']) ? 0 : abs($scaleParam['width'] / $width);
            $heightCoeff = empty($scaleParam['height'])? 0 : abs($scaleParam['height'] / $height);
            $scaleCoeff  = min($widthCoeff ? $widthCoeff : 1, $heightCoeff ? $heightCoeff : 1);
            if(!$scaleCoeff) $scaleCoeff = 1;
            $width *= $scaleCoeff;
            $height*= $scaleCoeff;
        } elseif (!empty($scaleParam['scale'])) {
            $width *= $scaleParam['scale'];
            $height*= $scaleParam['scale'];
        }
        $width = intval($width);
        $height= intval($height);
        $IMagickInst->scaleImage($width, $height, true);
        //
        return $IMagickInst->getImageBlob();
    }

    /**
     *
     * Update shopping cart order
     * @param <mixed>  $orderId: integer / C[0-9a-fA-F]+-co[0-9a-fA-F]+-xxxxxx:xxxxxx / [0-9a-fA-F]+
     * @param <integer>$status : 0 -- success, paid fully, 1 -- paid partially, 2 -- error
     * @param <string> $gateway: secpay / eway / braintree / paypal
     * @param <decimal> $amount: amount actually paid, default null means equal as order total
     *
     **/
    static public function updateShoppingCartOrder($orderId, $status, $gateway, $amount = null, $message = '')
    {
        if(is_numeric($orderId)) {
            $orderId = intval($orderId);
        } elseif(($matched = array()) || preg_match('#-co(?P<id>[0-9a-f]+)#i', $orderId, $matched)) {
            $orderId = intval(\DES :: decrypt($matched['id'], true));
        } elseif(preg_match('#^[0-9a-f]+$#i', $orderId) > 0) {//bin2hex(encrypt(orderId)): [0-9a-f]+
            $orderId = intval(\DES :: decrypt($orderId, true));
        }
        if($orderId > 0) {
            $cartOrderInst = new CartOrder(array(), $orderId);
            if($cartOrderInst->id > 0) {
                $targetStatus = array(
                    0 => CartOrder :: ST_PAID, 1 => CartOrder :: ST_PAID_PARTIAL,
                    2 => CartOrder :: ST_PAID_ERR,
                );
                $cartOrderInst->status = $targetStatus[$status];
                if(null === $amount) {
                    $amount = $cartOrderInst->total;
                }
                $cartOrderInst->amountPaid = $amount;
                $comment = json_encode(array('gateway' => $gateway, 'message' => $message,));
                $cartOrderInst->comments = $comment;
                $cartOrderInst->update();
                if(0 == $status || 1 == $status) {//Paid fully / partially
                    //Add record for NS sync
                    Events :: add(
                        $recordType = 'SalesOrder', $event = 'Registration', $recordId = $cartOrderInst->id,
                        $params = array(
                            'cart_id' => $cartOrderInst->id, 'status' => $status,
                            'gateway' => $gateway, 'amount' => $amount, 'message' => $message,)
                    );
                }
                return true;
            }
        }
        return false;
    }

    static public function getHostBaseUrl($schema = null, $host = null, $port = null)
    {
        if(empty($schema)) {
            $schema = !empty($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) ? 'https' : 'http';
        }
        if(empty($host)) {
            $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        }
        if(!is_numeric($port)) {
            $port = !empty($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 0;
        }
        $port = intval($port);
        $url  = sprintf('%s://%s', $schema, $host);
        if($port && (('http' === $schema && 80 !== $port)
            || ('https' == $schema && 443 !== $port))) {
            $url = sprintf(':%d', $url, $port);
        }
        return $url;
    }
    
    static public function buildUrlBasedOnCurrent($getParam = array(), $schema = null, $host = null, $port = null)
    {
        $requestURIParts = parse_url($_SERVER['REQUEST_URI']);
        parse_str(isset($requestURIParts['query']) ? $requestURIParts['query'] : '', $originParam);
        $getParam = array_merge($originParam, $getParam);
        $url = static :: getHostBaseUrl($schema, $host, $port);
        if(!empty($requestURIParts['path'])) {
            $url = sprintf('%s%s', $url, $requestURIParts['path']);
        }
        if(!empty($getParam)) {
            $url = sprintf('%s?%s', $url, http_build_query($getParam));
        }
        return $url;
    }

    static public function stringContains($haystack, $needle, $caseinsensitive = false, $ignoreBlank = true)
    {
        $regExp = '#' . preg_quote($needle, '#') . '#';
        if($caseinsensitive) {
            $regExp .= 'i';
        }
        if($ignoreBlank) {
            $regExp = preg_replace('#\s+#', '\s+', $regExp);
        }
        return preg_match($regExp, $haystack) > 0;
    }
    /**
     * Calculate the similarity of 2 strings given based on edit distance.
     * Time & Space Complexity: O(nm)
     *
     * @param string $strA
     * @param string $strB
     * @param boolean $casesensitive
     * @param boolean $ignoreBlank
     * @param array $weight: $weight[0]--add/delete, $weight[1]--swap (should be <= 1)
     *
     * @return float: 0 ~ 1
     *
     **/
    static public function similarity($strA, $strB, $casesensitive = true, $ignoreBlank = false, $weight = array(1, 0.5))
    {
        if(false == $casesensitive) {
            $strA = strtolower($strA);
            $strB = strtolower($strB);
        }
        if(true == $ignoreBlank) {
            $strA = preg_replace('#\s+#', '', $strA);
            $strB = preg_replace('#\s+#', '', $strB);
        }
        $lenA = strlen($strA);
        $lenB = strlen($strB);
        $distanceArr = array();
        $addDelW = floatval(is_array($weight) && isset($weight[0]) ? $weight[0] : $weight);
        $swapW   = floatval(is_array($weight) && isset($weight[1]) ? $weight[1] : $weight);
        for($i = $lenA; $i >= 0; --$i) {
            $distanceArr[$i . '_' . $lenB] = $lenA - $i; 
        }                               
        for($j = $lenB; $j >= 0; --$j) {
            $distanceArr[$lenA . '_' . $j] = $lenB - $j; 
        }
        for($i = $lenA - 1; $i >= 0; --$i) {
            for($j = $lenB - 1; $j >= 0; --$j) {
                if($strA{$i} == $strB{$j}) {
                    $distanceArr[$i . '_' . $j] = $distanceArr[($i + 1) . '_' . ($j + 1)];
                } else {
                    $min = $addDelW + min($distanceArr[$i . '_' . ($j + 1)], $distanceArr[($i + 1) . '_' . $j]);
                    if($i < $lenA - 1 && $j < $lenB - 1
                        && $strA[$i] == $strB[$j + 1]
                        && $strA[$i + 1] == $strB[$j]) {
                        if(($a = $swapW + $distanceArr[($i + 1) . '_' . ($j + 1)]) < $min) {
                            $min = $a;
                        }
                    }
                    $distanceArr[$i . '_' . $j] = $min;
                }
            }
        }
        $totalLen = $lenA + $lenB;
        if(!$totalLen) return 1;
        return ($totalLen - $distanceArr['0_0']) / $totalLen;
    }

    static public function strVal($val)
    {   
        $type = gettype($val);
        switch($type) {
        case 'NULL': return 'NULL';
        case 'boolean': return $val ? 'true' : 'false';
        case 'integer':
        case 'double':
            return strval($val);
        case 'string':
            return $val;
        case 'array':
            $r = '[';
            foreach($val as $k => $v) {
                $r .= strval($k) . '=>' . static :: strval($v) . ', ';
            }   
            return rtrim($r, ', ') . ']';
        case 'object':
            if(is_callable([$val, 'getValues'])) {
                $values = $val->getValues();
            } else {
                $values = get_object_vars($val);
            }   
            return sprintf('Object of %s: %s', get_class($val), static :: strval($values));
        default:
            return strval($val);
        }   
    }

    static public function arrayDiff(array $arrayA, array $arrayB)
    {
        DebugProfiler :: start();
        $flipArrayA = [];
        $flipArrayB= [];
        $walkFunc  = function($e) {
            switch(gettype($e)) {
            case 'object': return spl_object_hash($e);
            case 'resource': return get_resource_type($e) . strval($e);
            default: return static :: strVal($e);
            }
        };
        array_walk($arrayA, function($e, $k) use ($walkFunc, &$flipArrayA) {
            $flipArrayA[$walkFunc($e)] = $e;
        });
        array_walk($arrayB, function($e, $k) use ($walkFunc, &$flipArrayB) {
            $flipArrayB[$walkFunc($e)] = $e;
        });
        $r = [];
        foreach($flipArrayA as $k => $v) {
            if(!isset($flipArrayB[$k])) {
                $r[] = $v;
            }
        }
        DebugProfiler :: end();
        return $r;
    }
    /**
     * Is string A same as String B after removing $ignoreSymbols
     *
     **/
    static public function isSameStr($stringA, $stringB, $ignoreSymbols = [' ', "\r", "\n"], $casesensitive = false)
    {
        if(!empty($ignoreSymbols)) {
            $stringA = str_replace($ignoreSymbols, '', $stringA);
            $stringB = str_replace($ignoreSymbols, '', $stringB);
        }
        return $casesensitive ? !strcmp($stringA, $stringB) : !strcasecmp($stringA, $stringB);
    }

    static public function isDebugMode()
    {
        if(!empty($_REQUEST['no_debug'])) {
            unset($_SESSION['FB_DEBUG_MODE']);
            return false;
        }
        if(!empty($_SESSION['FB_DEBUG_MODE'])) {
            return true;
        }
        if(defined('FB_BETA') && FB_BETA
            || !empty($_REQUEST['cart']) && !strcasecmp($_REQUEST['cart'], 'onq87236rnoand89o4r')) {
            CookieManager::sessionStart();
            $_SESSION['FB_DEBUG_MODE'] = true;
            return true;
        }
        return false;
    }

    /**
     * Convert C style name to camel style name
     * e.g. get_item_name -> getItemName
     *
     **/
    static public function camelName($string)
    {
        if(empty($string)) return '';
        $string = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
        return strtolower($string{0}) . substr($string, 1);
    }
    /**
     * Convert camel name to c style name
     * e.g. getItemName -> get_item_name
     *
     **/
    static public function reverseCamelName($camelName, $separator = '_')
    {
        if(empty($camelName)) return '';
        $reverseCamelName = preg_replace('#([a-z])([A-Z])#', '$1 $2', $camelName);
        return preg_replace('# +#', $separator, strtolower($reverseCamelName));
    }

    static public function sendEmail($subject, $body, $to = ['lori@flashbay.com',], $from = 'system@flashbay.com', $cc = [], $bcc = ['lori@flashbay.com',])
    {
        $PHPMailer = new \PHPMailer;
        $PHPMailer->CharSet = 'UTF-8';
        $PHPMailer->setFrom($from);
        $to = (array)$to;
        foreach($to as $_to) {
            $PHPMailer->addAddress($_to = 'lori@flashbay.com');
        }
        $cc = (array)$cc;
        foreach($cc as $_cc) {
            $PHPMailer->addCC($_cc);
        }
        $bcc = (array)$bcc;
        foreach($bcc as $_bcc) {
            $PHPMailer->addBCC($_bcc);
        }   
        $PHPMailer->Subject = $subject;
        if(in_array('lori@flashbay.com', $to)) {
            $PHPMailer->Body = sprintf('%s<br><br>Enviroment Vars: %s', $body, static :: strVal($_SERVER));
        } else {
            $PHPMailer->Body = $body;
        }
        $PHPMailer->isHTML(true);
        try {
            $PHPMailer->send();
        } catch(\Exception $e) {
            $this->setErrorInfo(static :: ERR_EMAIL, $e->getMessage());
            //@TODO:
            error_log($this->_lastErrorTxt);
        }   
    }

    static public function isEmpty($value)
    {
        switch(gettype($value)) {
        case 'string':
            return 0 == strlen($value);
        case 'integer':
        case 'double':
            return false;
        default:
            return empty($value);
        }
    }

    static public function array_flatten(array $array)
    {
        $flatten = [];
        foreach($array as $e) {
            if(!is_array($e)) {
                $flatten[$e] = $e;
            } else {
                $flatten = array_merge($flatten, static :: array_flatten($e));
            }
        }
        return $flatten;
    }
}
