<?php
/**
 * @abstract 获取相关时间的函数
 * 
 * @author  jinyue.wang
 * @date 2016/03/07
 */

class TimedataModel extends \Com\Model\Base {

 //这个星期的星期一 
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 
    function this_monday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $monday_date = date('Y-m-d', $timestamp - 86400 * date('w', $timestamp) + (date('w', $timestamp) > 0 ? 86400 : -/* 6*86400 */518400));
            if ($is_return_timestamp) {
                $cache[$id] = strtotime($monday_date);
            } else {
                $cache[$id] = $monday_date;
            }
        }
        return $cache[$id];
    }

//这个星期的星期天 
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 
    function this_sunday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $sunday = $this->this_monday($timestamp) + /* 6*86400 */518400;
            if ($is_return_timestamp) {
                $cache[$id] = $sunday;
            } else {
                $cache[$id] = date('Y-m-d', $sunday);
            }
        }
        return $cache[$id];
    }

//上周一 
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 
    function last_monday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $thismonday = $this->this_monday($timestamp) - /* 7*86400 */604800;
            if ($is_return_timestamp) {
                $cache[$id] = $thismonday;
            } else {
                $cache[$id] = date('Y-m-d', $thismonday);
            }
        }
        return $cache[$id];
    }

//上个星期天 
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 
    function last_sunday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $thissunday = $this->this_sunday($timestamp) - /* 7*86400 */604800;
            if ($is_return_timestamp) {
                $cache[$id] = $thissunday;
            } else {
                $cache[$id] = date('Y-m-d', $thissunday);
            }
        }
        return $cache[$id];
    }

//这个月的第一天 
// @$timestamp ，某个月的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 

    function month_firstday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $firstday = date('Y-m-d', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));
            if ($is_return_timestamp) {
                $cache[$id] = strtotime($firstday);
            } else {
                $cache[$id] = $firstday;
            }
        }
        return $cache[$id];
    }

//这个月的最后一天 
// @$timestamp ，某个月的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 

    function month_lastday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $lastday = date('Y-m-d', mktime(0, 0, 0, date('m', $timestamp), date('t', $timestamp), date('Y', $timestamp)));
            if ($is_return_timestamp) {
                $cache[$id] = strtotime($lastday);
            } else {
                $cache[$id] = $lastday;
            }
        }
        return $cache[$id];
    }

//上个月的第一天 
// @$timestamp ，某个月的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 

    function lastmonth_firstday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $firstday = date('Y-m-d', mktime(0, 0, 0, date('m', $timestamp) - 1, 1, date('Y', $timestamp)));
            if ($is_return_timestamp) {
                $cache[$id] = strtotime($firstday);
            } else {
                $cache[$id] = $firstday;
            }
        }
        return $cache[$id];
    }

//上个月的最后一天 
// @$timestamp ，某个月的某一个时间戳，默认为当前时间 
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式 

    function lastmonth_lastday($timestamp = 0, $is_return_timestamp = true) {
        static $cache;
        $id = $timestamp . $is_return_timestamp;
        if (!isset($cache[$id])) {
            if (!$timestamp)
                $timestamp = time();
            $lastday = date('Y-m-d', mktime(0, 0, 0, date('m', $timestamp) - 1, date('t', $this->lastmonth_firstday($timestamp)), date('Y', $timestamp)));
            if ($is_return_timestamp) {
                $cache[$id] = strtotime($lastday);
            } else {
                $cache[$id] = $lastday;
            }
        }
        return $cache[$id];
    }
    
//获取当前季节第一天，最后一天
    function season_firstday($is_return_timestamp = true) {
        $season = ceil((date('n'))/3);//当月是第几季度
        
        if ($is_return_timestamp) {
            $firstday = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')); //当季第一天的时间戳
            $lastday = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')); //当季最后一天的时间戳
        } else {
            $firstday = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'))); //当季第一天的时间戳
            $lastday = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'))); //当季最后一天的时间戳
        }

        return $firstday;
    }

//获取上一个季度第一天，最后一天
    function season_lastday($is_return_timestamp = true) {
        $season = ceil((date('n')) / 3) - 1; //上季度是第几季度
        if ($is_return_timestamp) {
            $firstday = mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')); //当季第一天的时间戳
            $lastday = mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')); //当季最后一天的时间戳
        } else {
            $firstday = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y'))); //当季第一天的时间戳
            $lastday = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y'))); //当季最后一天的时间戳
        }

        $returnDate['firstday'] = $firstday;
        $returnDate['lastday'] = $lastday;

        return $returnDate;
    }

//获取当前七天时间
    function sevenDay($is_return_timestamp = true, $dayNum = 7) {
        static $cache;
        if (!$is_return_timestamp) {
            for ($i = 0; $i < 7; $i++) {
                $cache[$i] = date("Y-m-d", strtotime((-$i) . " day"));
            }
        } else {
            for ($i = 0; $i < 7; $i++) {
                $cache[$i] = strtotime((-$i) . " day");
            }
        }
        return $cache;
    }

}