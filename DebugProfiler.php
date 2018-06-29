<?php namespace QR;
/**
 * Author: lori@flashbay.com
 *
 **/
class DebugProfiler
{
    static protected $_debug = false;
    static protected $_totalTime;
    static protected $_profiles = [];
    /**
     * Enable debug profile
     *
     **/
    static public function open()
    {
        static :: $_debug     = true;
        static :: $_totalTime = 0;
        static :: $_profiles  = [];
    }
    /**
     * Disable debug profile
     *
     **/
    static public function close()
    {
        static :: $_debug =  false;
    }
    /**
     * Get all debug profiles
     *
     * @param boolean $sortByTimeDesc
     *        true: sort by cost time desc,
     *        false:sort by cost time asc
     *        null: original order of calling
     *
     **/
    static public function getProfiles($sortByTimeDesc = true)
    {
        $profiles = static :: $_profiles;
        foreach($profiles as &$profile) {
            uasort($profile['details'], function($a, $b) {
                return $a['cost'] - $b['cost'] < 0 ? 1 : -1;
            });
        }
        if(null !== $sortByTimeDesc) {
            if($sortByTimeDesc) {
                uasort($profiles, function($a, $b) {
                    if(!isset($a['cost'])) {
                        printf('%s lacks DebugProfiler :: end().<br>', $a['function']);
                    }
                    if(!isset($b['cost'])) {
                        printf('%s lacks DebugProfiler :: end().<br>', $b['function']);
                    }
                    return (isset($a['cost']) ? $a['cost'] : 0) - (isset($b['cost']) ? $b['cost'] : 0) < 0 ? 1 : -1;
                });
            } else {
                uasort($profiles, function($a, $b) {
                    if(!isset($a['cost'])) {
                        printf('%s lacks DebugProfiler :: end().<br>', $a['function']);
                    }
                    if(!isset($b['cost'])) {
                        printf('%s lacks DebugProfiler :: end().<br>', $b['function']);
                    }
                    return (isset($a['cost']) ? $a['cost'] : 0) - (isset($b['cost']) ? $b['cost'] : 0) < 0 ? -1 : 1;
                });
            }
        }
        return $profiles;
    }
    /**
     * Get total time cost of all profiles
     *
     **/
    static public function getTotalTime()
    {
        return static :: $_totalTime;
    }

    static protected function getProfileTarget()
    {
        $callStack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if(3 == count($callStack)) {
            $end = end($callStack);
            $key = '';
            if(!empty($end['class'])) {
                $key = $end['class'] . $end['type'];
            }
            $key .= $end['function'];
        } else {
            $end = end($callStack);
            $key = $end['file'];
        }
        return [
            'function' => $key,
            'line'     => $end['line'],
            'file'     => $end['file'],
        ];
    }
    /**
     * Start profile
     *
     **/
    static public function start()
    {
        if(static :: $_debug) {
            $profileTarget = static :: getProfileTarget();
            if(empty(static :: $_profiles[$key = $profileTarget['function']])) {
                static :: $_profiles[$key] = [
                    'function' => $key,
                    'details'  => [],
                ];
            }
            $detailKey = md5($profileTarget['file'] . $profileTarget['line']);
            static :: $_profiles[$key]['details'][$detailKey]['file'] = $profileTarget['file'];
            static :: $_profiles[$key]['details'][$detailKey]['line'] = $profileTarget['line'];
            static :: $_profiles[$key]['start'] = microtime(true);
        }
    }
    /**
     * End profile
     *
     **/
    static public function end()
    {
        if(static :: $_debug) {
            $end = microtime(true);
            $profileTarget = static :: getProfileTarget();
            if(!empty(static :: $_profiles[$key = $profileTarget['function']])
                && !empty(static :: $_profiles[$key]['start'])) {
                if(!isset(static :: $_profiles[$key]['cost'])) {
                    static :: $_profiles[$key]['cost'] = 0;
                    static :: $_profiles[$key]['count']= 0;
                }
                $cost = $end - static :: $_profiles[$key]['start'];
                static :: $_profiles[$key]['cost'] += $cost;
                static :: $_profiles[$key]['count']++;
                static :: $_totalTime += $cost;
                //
                $detailKey = md5($profileTarget['file'] . $profileTarget['line']);
                if(!isset(static :: $_profiles[$key]['details'][$detailKey]['count'])) {
                    static :: $_profiles[$key]['details'][$detailKey]['cost'] = 0;
                    static :: $_profiles[$key]['details'][$detailKey]['count']= 0;
                }
                static :: $_profiles[$key]['details'][$detailKey]['cost'] += $cost;
                static :: $_profiles[$key]['details'][$detailKey]['count']++;
                unset(static :: $_profiles[$key]['start']);
            }
        }
    }

    static public function dump()
    {
        if(static :: $_debug) {
            foreach(func_get_args() as $var) {
                var_dump('<pre>', $var, '</pre>');
            }
        }
    }
}
