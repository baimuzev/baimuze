<?php


namespace BaiMuZe\utility;

use \Closure;

/**
 *数组处理助手
 * @Description  系统用到的所有操作数组的相关函数
 */
class Arr
{
    /**
     * 获取数组中的值，多层数组支持以‘.’分割
     *
     * @param array $array 要查询的数组
     * @param string $key 要查询的关键词
     * @param mixed $default 默认值,支持闭包函数
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {

        if (is_null($key)) {
            return $array;
        }

        if (!is_array($array)) {
            $json = self::is_json($array);
            if (false === $json) {
                return $array;
            } else {
                $array = $json;
            }
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default instanceof Closure ? $default() : $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * 判断Json
     * @encoding UTF-8
     * @param $str
     * @author Twinkly
     * @create 2021年11月25日
     * @update 2021年11月25日
     */
    public static function is_json($str)
    {
        $arr = json_decode($str, true);
        return json_last_error() == JSON_ERROR_NONE ? $arr : false;
    }

    /**
     * 通过‘.’分割给多维数组设定值，如果没有$key为空，则直接替换数组
     *
     * @param array $array //原始数组
     * @param string $key //键值
     * @param mixed $value //键值内容
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            //如果不存在该键值，则我们为该键创建一个空数组
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * 检测一个数组是否包含指定键值
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    public static function has($array, $key)
    {
        if (empty($array) || is_null($key) || !is_array($array)) {
            return false;
        }
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        return true;
    }

    /**
     * 使用一个回调函数构建一个新数组
     *
     * @param array $array 要重新构建的数组
     * @param \Closure $callback 回调函数
     * @return array
     */
    public static function build($array, Closure $callback)
    {
        $results = array();
        foreach ($array as $key => $value) {
            list($innerKey, $innerValue) = call_user_func($callback, $key, $value);
            $results[$innerKey] = $innerValue;
        }
        return $results;
    }

    /**
     * 把指定数组拆分为键和值两列数组
     *
     * @param array $array 要拆分的数组
     * @return array
     */
    public static function divide($array)
    {
        return array(array_keys($array), array_values($array));
    }

    /**
     * 使用分割符‘.’来把一个多位数组转换为单一数组
     *
     * @param array $array 待转换的数组
     * @param string $prepend 转换后的键值前缀
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return $results;
    }

    /**
     * 返回数组第一个元素，并通过闭包函数进行检测
     *
     * @param array $array 待操作的数组
     * @param \Closure $callback 检测函数,格式为function()
     * @param mixed $default 默认值,支持闭包函数
     * @return mixed
     */
    public static function first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }
        return $default instanceof Closure ? $default() : $default;
    }

    /**
     * 返回数组最后一个元素，并通过闭包函数进行检测
     *
     * @param array $array 待操作的数组
     * @param \Closure $callback 检测函数,格式为function()
     * @param mixed $default 默认值,支持闭包函数
     * @return mixed
     */
    public static function last($array, $callback, $default = null)
    {
        return static::first(array_reverse($array), $callback, $default);
    }

    /**
     * 从一个给定的数组中删除一个或多个数组项，可使用“.”分割。
     *
     * @param array $array 待操作的数组
     * @param array|string $keys 数组键值
     * @return void
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        foreach ((array)$keys as $key) {
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                }
            }
            unset($array[array_shift($parts)]);
            $array = &$original;
        }
    }

    /**
     * 获取数组指定的键值，并删除它，支持‘.’分割
     *
     * @param array $array 待操作的数组
     * @param string $key 键值
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }

    /**
     * 删除或替换数组指定值的项目
     *
     * @param string $search //待搜索的值
     * @param array $replace //待处理的数组
     * @param string $value //替换的值,如果为空，则我们认为是删除该键值
     * @return string
     */
    public static function replaceValue($search, array $replace, $value = '')
    {
        $key = array_search($search, $replace);
        if ($key !== false) {
            if (empty($value)) {
                unset($replace[$key]);
            } else {
                $replace[$key] = $value;
            }
            return $replace;
        } else {
            return $replace;
        }
    }


    /**
     * 使用一个闭包函数筛选一个数组
     *
     * @param array $array 待筛选的数组
     * @param \Closure $callback 闭包函数
     * @return array
     */
    public static function where($array, Closure $callback)
    {
        $filtered = array();
        foreach ((array)$array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }


    /**
     * 将多位数组变成单一数组
     *
     * @param array $array
     * @return array
     */
    public static function flatten($array)
    {
        $return = array();
        array_walk_recursive($array, function ($x) use (&$return) {
            $return[] = $x;
        });
        return $return;
    }

    /**
     * 将给定的值替换为数组中的顺序
     *
     * @param string $search
     * @param array $replace
     * @param string $subject
     * @return string
     */
    public static function replace($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
        }
        return $subject;
    }

    /**
     * 把二维数组生产一个一维数组
     *
     * @param array $data 原始数据
     * @param array $key 查找的子数据键值
     * @return array
     */
    public static function outputArray(array $data, $key = 'children', $depth = 0)
    {
        $items = [];
        if (!empty($data)) {
            foreach ($data AS $v) {
                $child = isset($v[$key]) ? $v[$key] : null;
                unset($v[$key]);
                $v['padding'] = $depth * 24 + 12;
                $v['depth'] = $depth;
                $v['child'] = empty($child) ? 0 : 1;
                $items[] = $v;
                if (!empty($child)) {
                    $items = array_merge($items, static::outputArray($child, $key, $depth + 1));
                }
            }
        }
        return $items;
    }

    /**
     * 把数组生产一个下拉
     *
     * @param array $data 原始数据
     * @param string $value 选中的值
     * @param int $group 是否生成group标签
     * @param int $depth 深度
     * @return string
     */
    public static function outputSelect(array $data, $value = '', $group = 0, $depth = 0)
    {
        $html = '';
        if (!empty($data)) {
            foreach ($data AS $v) {
                if (!empty($v['children']) && $group == 1) {
                    $html .= '<optgroup label="' . str_repeat('&emsp;', $depth) . $v['name'] . '"></optgroup>';
                } else {
                    $html .= '<option value="' . $v['id'] . '" ' . ($value == $v['id'] ? 'selected="selected"' : '') . '>' . $v['name'] . '</option>';
                }
                if (!empty($v['children'])) {
                    $html .= static::outputSelect($v['children'], $value, $group, $depth + 1);
                }

            }
        }
        return $html;
    }

    /**
     * 合并两个多维数组
     * @param array $arr 目标数组
     * @param array $replace 要并入的数组
     * @return type $srting
     */
    public static function merge($arr, $replace)
    {
        foreach ($replace as $key => $val) {
            if (!isset($arr[$key])) {
                $arr[$key] = $val;
            } else {
                if (is_array($val)) {
                    $arr[$key] = static::merge($arr[$key], $val);
                }
            }
        }
        return $arr;
    }


    /**
     * 多维数组转字符串
     * @param array $array
     * @return $srting
     */
    public static function toString($arr)
    {
        if (is_array($arr)) {
            $arr = Arr::dot($arr);
            $arrs = [];
            foreach ($arr as $key => $val) {
                $arrs[] = $key . '=' . $val;
            }
            return implode(',', $arrs);
        }
        return $arr;
    }

    /**
     * json转数组，支持多层嵌套
     * @encoding UTF-8
     * @author Twinkly
     * @create 2022年2月22日
     * @update 2022年2月22日
     */
    public static function jsonToArray($json)
    {
        if (is_string($json))
            $json = json_decode($json, true);
        $arr = array();
        foreach ($json as $k => $v) {
            if (is_object($v) || is_array($v))
                $arr[$k] = self::jsonToArray($v);
            else
                $arr[$k] = $v;
        }
        return $arr;
    }
}