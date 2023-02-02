<?php


namespace BaiMuZe\utility;

/**
 * 时间处理助手
 * @author 白沐泽
 */
class Date
{

    /**
     * 文本转换为时间戳
     *
     * @param string $str 要转换的日期
     * @return number
     */
    public static function span($str)
    {
        $time = intval(strtotime($str));
        return $time;
    }

    /**
     * 格式话时间
     *
     * @param number $time 要格式的日期时间戳
     * @param string $format 日期格式  j:月份中的第几天，没有前导零
     * d:月份中的第几天，有前导零的 2 位数字
     * D:星期中的第几天，文本表示，3 个字母 Mon 到 Sun
     * l:星期几，完整的文本格式 Sunday 到 Saturday
     * N:表示的星期中的第几天
     * w:星期中的第几天，数字表示    0（表示星期天）到 6（表示星期六）
     * z:年份中的第几天    0 到 365
     * @return string
     */
    public static function format($time = 0, $format = 'Y-m-d H:i:s')
    {
        if (!is_numeric($time)) {
            $format = $time;
            $time = 0;
        }
        if ($time == 0) {
            $time = time();
        }
        return date($format, $time);
    }

    /**
     * 检测是否为时间格式
     *
     * @param string $date 要检测的时间 如2018-11-02 11:22:11
     * @param string $format 日期格式
     * @return number
     */
    public static function check($date, $format = 'Y-m-d H:i:s')
    {
        return date($format, intval(strtotime($date))) == $date;
    }

    /**
     * 返回全时间段，格式为2018-12-01(6天前周六)
     *
     * @param $time   要格式的日期
     * @param $format 日期格式  j:月份中的第几天，没有前导零
     * d:月份中的第几天，有前导零的 2 位数字
     * D:星期中的第几天，文本表示，3 个字母 Mon 到 Sun
     * l:星期几，完整的文本格式 Sunday 到 Saturday
     * N:表示的星期中的第几天
     * w:星期中的第几天，数字表示    0（表示星期天）到 6（表示星期六）
     * z:年份中的第几天    0 到 365
     * @return string
     */
    public static function whole($time = 0, $format = 'Y-m-d H:i:s')
    {
        if (!is_numeric($time)) {
            $format = $time;
            $time = 0;
        }
        if ($time == 0) {
            $time = time();
        }
        $weeks = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
        $week = date('w', $time);
        return static::format($time, $format) . '<span class="ui-color-gray">(' . $weeks[$week] . ')</span>';
    }


    /**
     * 格式化时间到开始或者结束
     *
     * @param number $time
     * @param number $type_id 0：格式到00：:0:00 1：格式到23:59:59
     * @return int
     */
    public static function standard($time = 0, $type_id = 0)
    {
        if ($time == 0) {
            $time = app('time');
        } elseif ($time == 1) {
            $time = app('time');
            $type_id = 1;
        }

        $time_span = self::format($time, 'Y-m-d') . ($type_id == 0 ? ' 00:00:00' : ' 23:59:59');
        return static::span($time_span);
    }

    /**
     * 计算相隔时间后的日期
     *
     * 更新为如果当月没有改天数，则直接定位到月末
     * @param number $number
     * @param string $date
     * @param string $interval
     * @return int
     */
    public static function differ($number = 0, $date = false, $interval = 'days')
    {
        if (false === $date) {
            $date = time();
        }
        if ($interval == 'month') {
            $now = date('Y-m', $date);
            $time = date('H:i:s', $date);
            $next = strtotime('+' . $number . ' month', strtotime($now));
            $days = date('t', $next);
            if (date('d', $date) >= $days) {
                $next = date('Y-m-' . $days . ' ' . $time, $next);
                return strtotime($next);
            } else {
                return strtotime('+' . $number . ' month', $date);
            }
        } else {
            return strtotime('+' . $number . ' ' . $interval . '', $date);
        }

    }


    /**
     * 获取指定时间当月的起始时间
     *
     * @param number $time
     * @return array
     */
    public static function month($time = 0)
    {
        $time = $time == 0 ? time() : $time;
        $start_time = static::span(self::format($time, 'Y-m-01'));
        $data['start'] = static::standard($start_time);
        $count = date('t', $time);
        $end_time = static::span(self::format($time, 'Y-m-' . $count));
        $data['end'] = static::standard($end_time, 1);
        return $data;
    }

    /**
     * 获取指定时间当年的起始时间
     *
     * @param number $time
     * @return array
     */
    public static function year($time = 0)
    {
        $time = $time == 0 ? time() : $time;
        $start_time = static::span(self::format($time, 'Y-01-01'));
        $data['start'] = static::standard($start_time);
        $end_time = static::span(self::format($time, 'Y-12-31'));
        $data['end'] = static::standard($end_time, 1);
        return $data;
    }

    /**
     * 获取指定月份所有天的起始数组
     *
     * @param number $time
     * @return unknown
     */
    public static function getmonths($time = 0)
    {
        $time = $time == 0 ? app('time') : $time;

        $days = date('t', $time); //获取当前月份天数
        $start = strtotime(date('Y-m-01 00:00:00', $time));  //获取本月第一天时间戳

        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $data[] = $start + $i * 86400; //每隔一天赋值给数组
        }
        return $data;
    }


    /**
     * 获取指定时间当前周的起始时间
     *
     * @param number $time
     * @return unknown
     */
    public static function week($time = 0)
    {
        $time = $time == 0 ? app('time') : $time;
        $first = 1;

        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', $time);

        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $start_time = strtotime(' -' . ($w ? $w - $first : 6) . ' days', $time);
        $data['start'] = static::standard($start_time);

        //本周结束日期
        $end_time = strtotime(' +6 days', $start_time);
        $data['end'] = static::standard($end_time, 1);
        return $data;
    }

    /**
     * 获取本周所有日期
     */
    public static function getweeks($time = 0)
    {
        $time = $time == 0 ? app('time') : $time;
        //获取当前周几
        $week = date('w', $time);
        $date = [];
        for ($i = 1; $i <= 7; $i++) {
            $date[$i] = strtotime('+' . $i - $week . ' days', $time);
        }
        return $date;
    }


    /**
     * 时间日期转换
     * @param type $time
     * @return type
     */
    public static function tran($time)
    {
        if (empty($time)) {
            $time = time();
        }
        $rtime = date("m-d H:i", $time);
        $htime = date("H:i", $time);
        $time = time() - $time;
        if ($time < 60) {
            $str = '刚刚';
        } elseif ($time < 60 * 60) {
            $min = floor($time / 60);
            $str = $min . '分钟前';
        } elseif ($time < 60 * 60 * 24) {
            $h = floor($time / (60 * 60));
            $str = $h . '小时前 ' . $htime;
        } elseif ($time < 60 * 60 * 24 * 3) {
            $d = floor($time / (60 * 60 * 24));
            if ($d == 1)
                $str = '昨天 ' . $rtime;
            else
                $str = '前天 ' . $rtime;
        } else {
            $str = $rtime;
        }
        return $str;
    }
}