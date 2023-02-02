<?php

use BaiMuZe\library\AppServer;

if (!function_exists('BmzLang')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function BmzLang($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\facade\Lang::get($name, $vars, $lang);
    }
}
if (!function_exists('arrayHas')) {
    /**
     * 检测一个数组是否包含指定键值
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    function arrayHas($array, $key)
    {
       return \BaiMuZe\utility\Arr::has($array,$key);
    }
}
if (!function_exists('token')) {
    /**
     * 生成表单令牌
     * @param string $name 令牌名称
     * @param mixed $type 令牌生成方法
     * @return string
     */
    function token($name = '__token__', $type = 'md5')
    {
        $token = AppServer::$sapp->request->buildToken($name, $type);
        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}
if (!function_exists('xss_safe')) {
    /**
     * 文本内容XSS过滤
     * @param string $text
     * @return string
     */
    function xss_safe(string $text): string
    {
        // 将所有 onxxx= 中的字母 o 替换为符号 ο，注意它不是字母
        $rules = ['#<script.*?<\/script>#is' => '', '#(\s)on(\w+=\S)#i' => '$1οn$2'];
        return preg_replace(array_keys($rules), array_values($rules), trim($text));
    }
}
if (!function_exists('shortUrl')) {
    /**
     * 生成最短URL地址
     * @param string $url 路由地址
     * @param array $vars PATH 变量
     * @param boolean|string $suffix 后缀
     * @param boolean|string $domain 域名
     * @return string
     */
    function shortUrl(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        return \BaiMuZe\utility\Url::shortUrl($url, $vars, $suffix, $domain);
    }
}

if (!function_exists('arrayToJson')) {
    /**
     * 转换数组为JSON字符串
     * @access public
     * @param array $array 数组
     * @param integer $options json参数
     * @return string
     */
    function arrayToJson(array $array, $options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($array, $options);
    }
}
if (!function_exists('JsonToArray')) {
    /**
     * 转换JSON字符串为数组
     * @access public
     * @param array $json JSON字符串
     * @param integer $options json参数
     * @return string
     */
    function JsonToArray($json, $options = JSON_UNESCAPED_UNICODE)
    {
        return json_decode($json, $options);
    }
}
if (!function_exists('syconfig')) {
    /**
     * 获取系统配置
     * @param string $label 配置类型
     * @param string $key 下标
     * @param $default 默认值
     * @author 白沐泽
     */
    function syconfig($label, $key, $default = null)
    {
        return \BaiMuZe\library\ConfigServer::get($label, $key, $default = null);
    }
}
if (!function_exists('encode')) {

    /**
     * 加密函数
     * @param string $value 待解密的值
     * @param int $expiry 过期时间
     * @param int $key 加密码
     * @param int $base 是否使用base64_decode
     * @return string
     */
    function encode($value, $expiry = 0, $key = '', $base = 1)
    {
        return app('security')->encrypt($value, $expiry, $key, $base);
    }
}

if (!function_exists('decode')) {

    /**
     * 解密函数
     * @param string $value 待解密的值
     * @param int $key 加密码
     * @param int $base 是否使用base64_decode
     * @return string
     */
    function decode($value, $key = '', $base = 1)
    {
        return app('security')->decrypt($value, $key, $base);
    }
}
if (!function_exists('echoVideoStream')) {

    /**
     * 输出视频流
     * @encoding UTF-8
     * @param unknown $file 文件地址
     * @param unknown $mime 文件mime类型
     * @author Twinkly
     * @create 2021年7月10日
     * @update 2021年7月10日
     */
    function echoVideoStream($file, $mime)
    {

        header("Content-type: $mime");
        header("Accept-Ranges: bytes");
        $size = filesize($file);
        if (isset($_SERVER['HTTP_RANGE'])) {
            header("HTTP/1.1 206 Partial Content");
            list($name, $range) = explode("=", $_SERVER['HTTP_RANGE']);
            list($begin, $end) = explode("-", $range);
            if ($end == 0) {
                $end = $size - 1;
            }
        } else {
            $begin = 0;
            $end = $size - 1;
        }
        header("Content-Length: " . ($end - $begin + 1));
        header("Content-Disposition: filename=" . basename($file));
        header("Content-Range: bytes " . $begin . "-" . $end . "/" . $size);
        $fp = fopen($file, 'rb');
        fseek($fp, $begin);
        while (!feof($fp)) {
            $p = min(1024, $end - $begin + 1);
            echo fread($fp, $p);
        }
        fclose($fp);
        exit();

    }
}
if (!function_exists('analysis')) {

    /**
     * 通过附件hash值获取附件地址
     * @param string $hash hash值
     * @param $storage
     * @return string
     */
    function analysis($hash, $storage = 'local')
    {

        $base = request()->host();// 结尾不带‘/’
        if (empty($hash)) {
            return $base . '/assets/images/nopic.jpg';
        }
        if (strpos($hash, 'http') === 0) {
            return $hash;
        }
        // 如果是纯hash
        if (preg_match("/^[0-9a-z]{32}$/", $hash)) {
            if ($storage == 'local') {
                return shortUrl('@home/attachment/index/hash/' . $hash, array(), false, true);
            } else {

            }
        } else {

        }
    }
}


