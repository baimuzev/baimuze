<?php


namespace BaiMuZe\utility;

/**
 * 字符串处理助手
 * @encoding UTF-8
 * @author 白沐泽
 * @Description  系统用到的所有操作字符串的相关函数
 */
class Str
{
    protected static $snakeCache = [];

    protected static $camelCache = [];

    protected static $studlyCache = [];

    /**
     * 确定是否已给定的字符串结束
     *
     * @param string $haystack 待检索的字符串
     * @param string|array $needles 要查找的字符串
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($haystack, -strlen($needle)))
                return true;
        }
        return false;
    }

    /**
     * 确定是否已给定的字符串开头
     *
     * @param string $haystack 待检索的字符串
     * @param string|array $needles 要查找的字符串
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }
        return false;
    }

    /**
     * 检查字符串中是否包含某些字符串
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {

        foreach ((array)$needles as $needle) {

            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 去除中文空格UTF8; windows下展示异常;过滤文件上传、新建文件等时的文件名
     * 文件名已存在含有该字符时，没有办法操作.
     *
     * @param string $str
     * @return string
     */
    public static function convert($str)
    {
        $char_empty = "\xc2\xa0";
        if (strpos($str, $char_empty) !== false) {
            $str = str_replace($char_empty, " ", $str);
        }
        return static::iconvTo($str);
    }

    /**
     * 编码转换
     *
     * @param string $str 待转换的字符串
     * @param string $from 原编码
     * @param string $to 转换后的编码
     * @return string
     */
    public static function iconvTo($str, $from = 'gb2312', $to = 'utf-8')
    {
        if (!function_exists('iconv')) {
            return $str;
        }
        //尝试用mb转换；android环境部分问题解决
        if (function_exists('mb_convert_encoding')) {
            $result = @mb_convert_encoding($str, $to, $from);
        } else {
            $result = @iconv($from, $to, $str);
        }
        if (strlen($result) == 0) {
            return $str;
        }
        return $result;
    }

    /**
     * 获取字符串的长度
     *
     * @param string $value
     * @return int
     */
    public static function length($value)
    {
        return mb_strlen($value);
    }

    /**
     * 判断一个对象是否为空
     * @param $string
     */
    public static function isEmpty($string)
    {
        if ($string == '' || $string == null || $string == '{}' || $string == 'undefined' || empty($string)) {
            return true;
        }
        return false;
    }

    /**
     * 确定给定的字符串是否匹配给定的模式。
     *
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern) . '\z';
        return (bool)preg_match('#^' . $pattern . '#', $value);
    }

    /**
     * 中文截取，支持gb2312,gbk,utf-8,big5
     *
     * @param string $str 要截取的字串
     * @param int $length 截取长度
     * @param int $start 截取起始位置
     * @param $suffix 是否加尾缀
     * @param string $charset utf-8|gb2312|gbk|big5 编码
     */
    public static function csubstr($str, $length, $start = 0, $suffix = true, $charset = 'utf-8')
    {
        $str = trim($str);
        if (function_exists('mb_substr')) {
            $slen = mb_strlen($str, $charset);
            if ($slen == 0) {
                return '';
            } elseif ($slen < $length) {
                return $str;
            } else {
                $slice = mb_substr($str, $start, $length, $charset);
            }
        } else {
            $re['utf-8'] = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
            $re['gb2312'] = '/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/';
            $re['gbk'] = '/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/';
            $re['big5'] = '/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/';
            preg_match_all($re[$charset], $str, $match);
            $slen = count($match[0]);
            if ($slen == 0) {
                return '';
            } elseif ($slen < $length) {
                return $str;
            } else {
                $slice = join('', array_slice($match[0], $start, $length));
            }
        }

        if ($suffix) {
            return $slice . "…";
        }
        return $slice;
    }

    /**
     * 将中文字符串按指定的长度截取到数组
     * @encoding UTF-8
     * @author Twinkly
     * @create 2022年5月11日
     * @update 2022年5月11日
     */
    public static function mb_str_split($str, $split_length = 1, $charset = "UTF-8")
    {
        if (func_num_args() == 1) {
            return preg_split('/(?<!^)(?!$)/u', $str);
        }
        if ($split_length < 1) return false;
        $len = mb_strlen($str, $charset);
        $arr = array();
        for ($i = 0; $i < $len; $i += $split_length) {
            $s = mb_substr($str, $i, $split_length, $charset);
            $arr[] = $s;
        }
        return $arr;
    }

    /**
     * 字符串转数组
     * @param string $text 待转内容
     * @param string $separ 分隔字符
     * @param null|array $allow 限定规则
     * @return array
     */
    public static function str2arr(string $text, string $separ = ',', ?array $allow = null): array
    {
        $items = [];
        foreach (explode($separ, trim($text, $separ)) as $item) {
            if ($item !== '' && (!is_array($allow) || in_array($item, $allow))) {
                $items[] = trim($item);
            }
        }
        return $items;
    }

    /**
     * 数组转字符串
     * @param array $data 待转数组
     * @param string $separ 分隔字符
     * @param null|array $allow 限定规则
     * @return string
     */
    public static function arr2str(array $data, string $separ = ',', ?array $allow = null): string
    {
        foreach ($data as $key => $item) {
            if ($item === '' || (is_array($allow) && !in_array($item, $allow))) {
                unset($data[$key]);
            }
        }
        return $separ . join($separ, $data) . $separ;
    }

    /**
     * 获取字符中的图片地址
     *
     * @param string $content 待查询的字符串
     * @param int $is_all 第几张图片，如果为all则返回所有的
     * @return string|array
     * '/<images.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.bmp]))[\'|\"].*?[\/]?>/'
     */
    public static function getImg($content, $order = 'all')
    {
        $pattern = '/<images.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/';
        preg_match_all($pattern, $content, $match);
        if (isset($match[1]) && !empty($match[1])) {
            if ($order === 'all') {
                return $match[1];
            }
            if (is_numeric($order) && isset($match[1][$order])) {
                return $match[1][$order];
            }
        }
        return [];
    }

    /**
     * 获取指定内容中所有的外部资源
     *
     * @param string $content
     * @return array
     */
    public static function getResources($content)
    {
        if (empty($content)) return false;
        $host = app('request')->root();
        $pattern = '/<images.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.bmp]))[\'|\"].*?[\/]?>/';
        $content = stripslashes($content);
        preg_match_all($pattern, $content, $match);
        return ['content' => $content, 'file' => $match[1]];
    }

    /**
     * 为指定的内容插入分页符
     *
     * @param string $body 内容
     * @param string $size 分页大小
     * @param string $tag 分页标记
     * @return    string
     */
    public static function page($body, $size, $tag)
    {
        if (strlen($body) < $size) {
            return $body;
        }
        $body = stripslashes($body);
        $bds = explode('<', $body);
        $npageBody = '';
        $istable = 0;
        $mybody = '';
        foreach ($bds as $i => $k) {
            if ($i == 0) {
                $npageBody .= $bds[$i];
                continue;
            }
            $bds[$i] = "<" . $bds[$i];
            if (strlen($bds[$i]) > 6) {
                $tname = substr($bds[$i], 1, 5);
                if (strtolower($tname) == 'table') {
                    $istable++;
                } else if (strtolower($tname) == '/tabl') {
                    $istable--;
                }
                if ($istable > 0) {
                    $npageBody .= $bds[$i];
                    continue;
                } else {
                    $npageBody .= $bds[$i];
                }
            } else {
                $npageBody .= $bds[$i];
            }
            if (strlen($npageBody) > $size) {
                $mybody .= $npageBody . $tag;
                $npageBody = '';
            }
        }
        if ($npageBody != '') {
            $mybody .= $npageBody;
        }
        return addslashes($mybody);
    }

    /**
     * 清理指定的html代码及空格换行符等
     *
     * @param string $str
     * @return string
     */
    public static function clearHtml($str)
    {
        if (empty($str)) {
            return $str;
        }
        $str = trim($str);
        // 把HTML实体标签转换为字符
        $str = htmlspecialchars_decode($str);
        $str = strip_tags($str, "");
        $str = preg_replace("/\t/", "", $str); //使用正则表达式匹配需要替换的内容，如：空格，换行，并将替换为空。
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/ /", "", $str);
        $str = preg_replace("/  /", "", $str);  //匹配html中的空格
        $str = preg_replace("/　/", "", $str);  //匹配html中的空格
        // 去除转义字符
        $str = str_replace(array(
            "&nbsp;",
            "&ensp;",
            "&emsp;",
            "&thinsp;",
            "&zwnj;",
            "&zwj;",
            "&ldquo;",
            "&rdquo;"
        ), "", $str);
        return trim($str);
    }

    /**
     * 过滤 字符串空格、全角空格、换行
     * @encoding UTF-8
     * @param unknown $str
     * @return mixed
     * @author Twinkly
     * @create 2021年6月22日
     * @update 2021年6月22日
     */
    public static function clearStr($str)
    {
        $search = array(" ", "　", "\n", "\r", "\t");
        $replace = array("", "", "", "", "");
        return str_replace($search, $replace, $str);
    }

    /**
     * 格式化字符
     *
     * @param float $str 待处理的文字
     * @param int $type 处理方式
     * @param int $last 结束位置，如果大于0，则默认type为起始位置
     * @return float
     */
    public static function format($str, $type = 1, $last = 0)
    {
        $str = (string)$str;
        switch ($type) {
            //隐藏手机号或这电话中间思维
            case 1:
                $is_tel = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i', $str); //固定电话
                if ($is_tel == 1) {
                    return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i', '$1****$2', $str);
                } else {
                    return preg_replace('/(1[356789]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $str);
                }
                break;
            //隐藏身份证号
            case 2:
                return strlen($str) == 15 ? substr_replace($str, "****", 8, 4) : (strlen($str) == 18 ? substr_replace($str, "****", 10, 4) : "身份证位数不正常！");
                break;
            //隐藏IP
            case 3:
                $reg = '/((?:\d+\.){3})\d+/';
                return preg_replace($reg, "\\1*", $str);
                break;
            //隐藏姓名
            case 4:
                $strlen = mb_strlen($str, 'utf-8');
                $firstStr = mb_substr($str, 0, 1, 'utf-8');
                $lastStr = mb_substr($str, -1, 1, 'utf-8');
                return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($str, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
                break;
            //隐藏客户号
            case 5:
                return substr_replace($str, "********", 8, 8);
                break;
            //隐藏邮箱
            case 6:
                $email = explode("@", $str);
                $prevfix = (strlen($email[0]) < 4) ? "" : substr($str, 0, 3); //邮箱前缀
                $count = 0;
                $str = preg_replace('/([\d\w+_-]{0,100})@/', '*****@', $str, -1, $count);
                return $prevfix . $str;
                break;
            //隐藏银行卡号
            case 7:
                return substr($str, 0, 4) . "************" . substr($str, -4);
                break;
            //隐藏公司名
            case 8:
                $strlen = mb_strlen($str, 'utf-8');
                $firstStr = mb_substr($str, 0, 2, 'utf-8');
                $lastStr = mb_substr($str, -2, 2, 'utf-8');
                return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($str, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
                break;
        }
    }

    /**
     * 把十六进制值转换为 ASCII 字符
     *
     * @param string $hex 16进制值
     * @return
     */
    public static function hexToAscii($hex = false)
    {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }

    /**
     * 格式化金额
     * @param float $str 待处理的金额
     * @return string
     */
    public static function formatmoney($str)
    {
        $str = (float)$str;
        if ($str < 10000) {
            $str = static::floating($str, 1, 2);
            $strs = explode('.', $str);
            return $strs[0] . '<em>.' . $strs[1] . '</em>';
        } elseif ($str < 100000000) {
            $str = bcdiv($str, 10000, 2);
            $strs = explode('.', $str);
            return $strs[0] . '<em>.' . $strs[1] . '万元</em>';
        } else {
            $str = bcdiv($str, 100000000, 2);
            $strs = explode('.', $str);
            return $strs[0] . '<em>.' . $strs[1] . '亿元</em>';
        }
    }

    /**
     * 根据身份证解析客户信息
     *
     * @param string $idcard 身份证号
     * @return
     */
    public static function parseIDcard($idcard)
    {
        if (empty($idcard)) return ['birthday' => 0, 'birth' => '', 'age' => '', 'constellation' => '', 'zodiac' => '', 'sex' => 0];

        //获取生日
        $birthday = substr($idcard, 6, 8);
        $year = substr($birthday, 0, 4);
        $month = substr($birthday, 4, 2);
        $day = substr($birthday, 6);

        //获取星座
        $constellation = '';
        if (($month == 1 && $day <= 21) || ($month == 2 && $day <= 19)) {
            $constellation = "水瓶座";
        } else if (($month == 2 && $day > 20) || ($month == 3 && $day <= 20)) {
            $constellation = "双鱼座";
        } else if (($month == 3 && $day > 20) || ($month == 4 && $day <= 20)) {
            $constellation = "白羊座";
        } else if (($month == 4 && $day > 20) || ($month == 5 && $day <= 21)) {
            $constellation = "金牛座";
        } else if (($month == 5 && $day > 21) || ($month == 6 && $day <= 21)) {
            $constellation = "双子座";
        } else if (($month == 6 && $day > 21) || ($month == 7 && $day <= 22)) {
            $constellation = "巨蟹座";
        } else if (($month == 7 && $day > 22) || ($month == 8 && $day <= 23)) {
            $constellation = "狮子座";
        } else if (($month == 8 && $day > 23) || ($month == 9 && $day <= 23)) {
            $constellation = "处女座";
        } else if (($month == 9 && $day > 23) || ($month == 10 && $day <= 23)) {
            $constellation = "天秤座";
        } else if (($month == 10 && $day > 23) || ($month == 11 && $day <= 22)) {
            $constellation = "天蝎座";
        } else if (($month == 11 && $day > 22) || ($month == 12 && $day <= 21)) {
            $constellation = "射手座";
        } else if (($month == 12 && $day > 21) || ($month == 1 && $day <= 20)) {
            $constellation = "魔羯座";
        }

        //获取属相
        $start = 1901;
        $x = ceil(($start - $year) % 12);
        $zodiac = '';
        if ($x == 1 || $x == -11) {
            $zodiac = "鼠";
        }
        if ($x == 0) {
            $zodiac = "牛";
        }
        if ($x == 11 || $x == -1) {
            $zodiac = "虎";
        }
        if ($x == 10 || $x == -2) {
            $zodiac = "兔";
        }
        if ($x == 9 || $x == -3) {
            $zodiac = "龙";
        }
        if ($x == 8 || $x == -4) {
            $zodiac = "蛇";
        }
        if ($x == 7 || $x == -5) {
            $zodiac = "马";
        }
        if ($x == 6 || $x == -6) {
            $zodiac = "羊";
        }
        if ($x == 5 || $x == -7) {
            $zodiac = "猴";
        }
        if ($x == 4 || $x == -8) {
            $zodiac = "鸡";
        }
        if ($x == 3 || $x == -9) {
            $zodiac = "狗";
        }
        if ($x == 2 || $x == -10) {
            $zodiac = "猪";
        }

        //获取性别
        $sexint = (int)substr($idcard, 16, 1);
        $sex = $sexint % 2 === 0 ? 2 : 1;  //1=男 2=女
        //生日时间戳
        $birth_date = strtotime($birthday);
        //获取年龄
        $today = time();
        $diff = floor(($today - $birth_date) / 86400 / 365);
        $age = strtotime($birthday . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;

        return ['birthday' => $birth_date, 'birth' => "$month-$day", 'age' => $age, 'constellation' => $constellation, 'zodiac' => $zodiac, 'sex' => $sex];
    }

    /**
     * 人民币金额数字转中文大写
     *
     * @param int $num 待转换金额
     * @param string $mode
     * @return string
     */
    public static function rmbtocapital($num, $mode = true)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        $num = round($num, 2);
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "数据太长，没有这么大的钱吧，检查下";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            $num = $num / 10;
            $num = ( int )$num;
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            $m = substr($c, $j, 6);
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }

        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        if (empty ($c)) {
            return "零元整";
        } else {
            return $c . "整";
        }
    }

    /**
     * 将100以内的整数转换成中文
     * @encoding UTF-8
     * @author Twinkly
     * @create 2021年8月4日
     * @update 2021年8月4日
     */
    public static function numToChinese($num)
    {
        $num_c = " 一二三四五六七八九";
        $num_d = "十";
        $num = intval($num);

        $str = '';
        if ($num > 100) {
            $str = '不支持100以上的数字';
        } else if ($num >= 10 && $num < 100) {
            $n1 = bcdiv($num, 10);
            $str .= substr($num_c, $n1, 1) . $num_d;
            $n2 = bcmod($num, 10);
            $str .= substr($num_c, $n2, 1);
        } else {
            $str = substr($num_c, $num, 1);
        }
        return trim($str);
    }

    /**
     * 生成默认头像 ay
     * @param $text
     * @return false|string
     */
    public static function createAvatar($text = "阿")
    {

        $randBg = [
            ['31', '38', '35'],
            ['199', '210', '212'],
            ['34', '162', '195'],
            ['27', '167', '132'],
            ['236', '43', '36'],
            ['222', '118', '34']
        ];
        $bg = $randBg[array_rand($randBg)]; //随机获取背景

        $image = imagecreate(200, 200);  //创建画布
        $color = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]); //为画布分配颜色
        imagefilledrectangle($image, 0, 0, 199, 199, $color); //填充颜色到背景

        $fontSize = 90; //字体大小
        $font_file = public_path('static/common/fonts') . "FZDeSHJW_506L.TTF"; //字体文件 * 修改成自己的字体路径

        $pos = ImageTTFBBox($fontSize, 0, $font_file, $text);// 计算字符的宽高 获得字体初始的8个相对位置
        // 居中公式 （画布宽 - 字体的宽度）/ 2 - 字体初始位置的偏移量
        $left_x = intval((200 - abs($pos[2] - $pos[0])) / 2 - abs($pos[0]));
        $left_y = intval((200 - abs($pos[5] - $pos[3])) / 2 + abs($pos[5]));

        $color2 = imagecolorallocate($image, 255, 255, 255);  //为字体分配颜色
        imagefttext($image, $fontSize, 0, $left_x, $left_y, $color2, $font_file, $text); //填充文案到画布里

        $fileName = 'Avatar_' . time() . '.png'; //文件名称,避免重复生成

        $localFilePath = public_path('static/tmp/avatar') . $fileName;//本地存储路径  * 修改成自己存放文件的路径

        imagepng($image, $localFilePath);//生成图像并保持本地
        if (file_exists($localFilePath)) {
            return '/static/tmp/avatar/' . $fileName;
        } else {
            return null;
        }
    }

    /**
     * 随机生成姓名
     * @param integer $sex 1 男 2 女 0 不限
     * @param string $x 固定姓
     * @param boolen $fx 是否加入复姓 true 是 false 否
     * @return array ['x' => '姓', 'm' => '名', 'xm' => '姓名']
     */

    public static function generate_name($sex = 0, $x = null, $fx = true)
    {

        // 单姓

        $xing_d = ['赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦', '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹', '喻', '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤', '花', '方', '任', '袁', '柳', '鲍', '史', '唐', '费', '薛', '雷', '贺', '倪', '汤', '滕', '殷', '罗', '毕', '郝', '安', '常', '傅', '卞', '齐', '元', '顾', '孟', '平', '黄', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '狄', '米', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝', '董', '梁', '杜', '阮', '蓝', '闵', '季', '贾', '路', '娄', '江', '童', '颜', '郭', '梅', '盛', '林', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万', '支', '柯', '管', '卢', '莫', '柯', '房', '裘', '缪', '解', '应', '宗', '丁', '宣', '邓', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '龚', '程', '嵇', '邢', '裴', '陆', '荣', '翁', '荀', '于', '惠', '甄', '曲', '封', '储', '仲', '伊', '宁', '仇', '甘', '武', '符', '刘', '景', '詹', '龙', '叶', '幸', '司', '黎', '溥', '印', '怀', '蒲', '邰', '从', '索', '赖', '卓', '屠', '池', '乔', '胥', '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申', '扶', '堵', '冉', '宰', '雍', '桑', '寿', '通', '燕', '浦', '尚', '农', '温', '别', '庄', '晏', '柴', '瞿', '阎', '连', '习', '容', '向', '古', '易', '廖', '庾', '终', '步', '都', '耿', '满', '弘', '匡', '国', '文', '寇', '广', '禄', '阙', '东', '欧', '利', '师', '巩', '聂', '关', '荆'];


        // 复姓

        $xing_f = ['司马', '上官', '欧阳', '夏侯', '诸葛', '闻人', '东方', '赫连', '皇甫', '尉迟', '公羊', '澹台', '公冶', '宗政', '濮阳', '淳于', '单于', '太叔', '申屠', '公孙', '仲孙', '轩辕', '令狐', '徐离', '宇文', '长孙', '慕容', '司徒', '司空'];


        // 男性名

        $ming_m = ['伟', '刚', '勇', '毅', '俊', '峰', '强', '军', '平', '保', '东', '文', '辉', '力', '明', '永', '健', '世', '广', '志', '义', '兴', '良', '海', '山', '仁', '波', '宁', '贵', '福', '生', '龙', '元', '全', '国', '胜', '学', '祥', '才', '发', '武', '新', '利', '清', '飞', '彬', '富', '顺', '信', '子', '杰', '涛', '昌', '成', '康', '星', '光', '天', '达', '安', '岩', '中', '茂', '进', '林', '有', '坚', '和', '彪', '博', '诚', '先', '敬', '震', '振', '壮', '会', '思', '群', '豪', '心', '邦', '承', '乐', '绍', '功', '松', '善', '厚', '庆', '磊', '民', '友', '裕', '河', '哲', '江', '超', '浩', '亮', '政', '谦', '亨', '奇', '固', '之', '轮', '翰', '朗', '伯', '宏', '言', '若', '鸣', '朋', '斌', '梁', '栋', '维', '启', '克', '伦', '翔', '旭', '鹏', '泽', '晨', '辰', '士', '以', '建', '家', '致', '树', '炎', '德', '行', '时', '泰', '盛', '雄', '琛', '钧', '冠', '策', '腾', '楠', '榕', '风', '航', '弘'];


        // 女性名

        $ming_f = ['秀', '娟', '英', '华', '慧', '巧', '美', '娜', '静', '淑', '惠', '珠', '翠', '雅', '芝', '玉', '萍', '红', '娥', '玲', '芬', '芳', '燕', '彩', '春', '菊', '兰', '凤', '洁', '梅', '琳', '素', '云', '莲', '真', '环', '雪', '荣', '爱', '妹', '霞', '香', '月', '莺', '媛', '艳', '瑞', '凡', '佳', '嘉', '琼', '勤', '珍', '贞', '莉', '桂', '娣', '叶', '璧', '璐', '娅', '琦', '晶', '妍', '茜', '秋', '珊', '莎', '锦', '黛', '青', '倩', '婷', '姣', '婉', '娴', '瑾', '颖', '露', '瑶', '怡', '婵', '雁', '蓓', '纨', '仪', '荷', '丹', '蓉', '眉', '君', '琴', '蕊', '薇', '菁', '梦', '岚', '苑', '婕', '馨', '瑗', '琰', '韵', '融', '园', '艺', '咏', '卿', '聪', '澜', '纯', '毓', '悦', '昭', '冰', '爽', '琬', '茗', '羽', '希', '欣', '飘', '育', '滢', '馥', '筠', '柔', '竹', '霭', '凝', '晓', '欢', '霄', '枫', '芸', '菲', '寒', '伊', '亚', '宜', '可', '姬', '舒', '影', '荔', '枝', '丽', '阳', '妮', '宝', '贝', '初', '程', '梵', '罡', '恒', '鸿', '桦', '骅', '剑', '娇', '纪', '宽', '苛', '灵', '玛', '媚', '琪', '晴', '容', '睿', '烁', '堂', '唯', '威', '韦', '雯', '苇', '萱', '阅', '彦', '宇', '雨', '洋', '忠', '宗', '曼', '紫', '逸', '贤', '蝶', '菡', '绿', '蓝', '儿', '翠', '烟'];


        // 获取姓

        if (!$x) {

            $xing = $fx ? array_merge($xing_d, $xing_f) : $xing_d;

            $x = $xing[mt_rand(0, count($xing) - 1)];

        }


        // 获取名

        switch ($sex) {

            case 1:
                $ming = $ming_m;
                break;

            case 2:
                $ming = $ming_f;
                break;

            default:
                $ming = array_merge($ming_m, $ming_f);
                break;

        }


        // 单字名

        $m = $ming[mt_rand(0, count($ming) - 1)];


        // 双字名

        mt_rand(0, 1) && $m .= $ming[mt_rand(0, count($ming) - 1)];


        return ['x' => $x, 'm' => $m, 'xm' => $x . $m];

    }

    /**
     * 字符串转小写
     *
     * @param string $value
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * 字符串转大写
     *
     * @param string $value
     * @return string
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * 获取指定长度的随机字母数字组合的字符串
     *
     * @param int $length
     * @param int $type
     * @param string $addChars
     * @return string
     */
    public static function random(int $length = 6, int $type = null, string $addChars = ''): string
    {
        $str = '';
        switch ($type) {
            case 0:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
                break;
            case 1:
                $chars = str_repeat('0123456789', 3);
                break;
            case 2:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
                break;
            case 3:
                $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
                break;
            case 4:
                $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书" . $addChars;
                break;
            default:
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
                break;
        }
        if ($length > 10) {
            $chars = $type == 1 ? str_repeat($chars, $length) : str_repeat($chars, 5);
        }
        if ($type != 4) {
            $chars = str_shuffle($chars);
            $str = substr($chars, 0, $length);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $str .= mb_substr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
            }
        }
        return $str;
    }

    /**
     * 驼峰转下划线
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * 下划线转驼峰(首字母小写)
     *
     * @param string $value
     * @return string
     */
    public static function camel(string $value): string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * 下划线转驼峰(首字母大写)
     *
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * 转为首字母大写的标题格式
     *
     * @param string $value
     * @return string
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 字符串命名风格转换
     * type 0 将 Java 风格转换为 C 的风格 1 将 C 风格转换为 Java 的风格
     * @access public
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @param bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}