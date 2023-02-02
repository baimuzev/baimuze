<?php


namespace BaiMuZe\utility;

use BaiMuZe\library\AppServer;
use think\Container;

/**
 *  验证辅助类（待完善）
 * @author 白沐泽
 * @createdate 2022-11-22
 */
class Validator
{
    /**
     * 快捷验证方法
     * @author 白沐泽
     */
    protected $rule = [
        'require' => 'validateRequire',
        'nickname' => 'validateNickname',
        'chinese' => 'validateChinese',
        'boolean' => 'validateBoolean',
        'array' => 'validateArray',
        'alpha' => 'validateAlpha',
        'AlphaNum' => 'validateAlphaNum',
        'AlphaDash' => 'validateAlphaDash',
        'mobile' => 'validateMobile',
        'email' => 'validateEmail',
        'regex' => 'validateRegex',
        'same' => 'validateSame',
        'number' => 'validateNumeric',
        'ZeroNumeric' => 'validateZeroNumeric',
        'integer' => 'validateInteger',
        'idcard' => 'validateIdCard',
        'isUrl' => 'isUrl',
        'truename' => 'isTrueName',
        'wechat' => 'isWechat',
        'WechatOpenId' => 'isWechatOpenId'
    ];

    /**
     *
     */
    public static function instance()
    {
        return Container::getInstance()->invokeClass(static::class);
    }

    /**
     * 快捷输入并验证（ 支持 规则 # 别名 ）
     * @param array $rules 验证规则（ 验证信息数组 ）
     * @param string|array $input 输入内容 ( post. 或 get. )
     * @param callable|null $callable 异常处理操作
     * @return array
     *
     * age.require => message // 最大值限定
     * age.between:1,120 => message // 范围限定
     * name.require => message // 必填内容
     * name.default => 100 // 获取并设置默认值
     * region.value => value // 固定字段数值内容
     * @author 白沐泽
     */
    public function init(array $rules, $input = '', ?callable $callable = null)
    {
        if (is_string($input)) {
            $type = trim($input, '.') ?: 'request';
            $input = AppServer::$sapp->request->$type();
        }
        [$data, $rule, $info] = [[], [], []];
        foreach ($rules as $name => $message) if (is_numeric($name)) {
            [$name, $alias] = explode('#', $message . '#');
            $data[$name] = $input[($alias ?: $name)] ?? null;
        } elseif (strpos($name, '.') === false) {
            $data[$name] = $message;
        } elseif (preg_match('|^(.*?)\.(.*?)#(.*?)#?$|', $name . '#', $matches)) {
            [, $_key, $_rule, $alias] = $matches;
            if (in_array($_rule, ['value', 'default'])) {
                if ($_rule === 'value') $data[$_key] = $message;
                elseif ($_rule === 'default') $data[$_key] = $input[($alias ?: $_key)] ?? $message;
            } else {
                $info[explode(':', $name)[0]] = $message;
                $data[$_key] = $data[$_key] ?? ($input[($alias ?: $_key)] ?? null);
                $rule[$_key] = isset($rule[$_key]) ? ($rule[$_key] . '|' . $_rule) : $_rule;
            }
        }
        if ($this->check($rule, $data)) {
            return $data;
        } elseif (is_callable($callable)) {
            return call_user_func($callable, $message, $data);
        } else {
            return [];
        }
    }

    /**
     * 批量校验规则
     * @author 白沐泽
     */
    protected function check($rules, $datas)
    {
        foreach ($rules as $name => $rule) {
            if (isset($rule, $this->rule)) {
                if (!call_user_func(array($this, $this->rule[$rule]), $datas[$name])) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 验证是不是为空
     * @param  $value
     */
    public function validateRequire($value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        }
        return true;
    }

    /**
     * 检查用户名是否符合规定 (两个字符以上，只能有中文，字母，数字，下划线的)
     *
     * @param string $username 要检查的用户名
     * @param mixed $value
     * @return  bool
     */
    public function validateNickname($username, $value)
    {
        $strlen = strlen($username);
        if (!preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $value)) {
            return false;
        } elseif (20 < $strlen || $strlen < 2) {
            return false;
        }
        return true;
    }

    /**
     * 检查是否为汉字
     *
     * @param mixed $value
     * @return  bool
     */
    public function validateChinese($value)
    {
        if (!preg_match("/^[\x7f-\xff]+$/", $value)) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否为布尔值
     *
     * @param mixed $value
     * @return bool
     */
    public function validateBoolean($value)
    {
        $acceptable = array(true, false, 0, 1, '0', '1');
        return in_array($value, $acceptable, true);
    }

    /**
     * 验证是否是一个数组
     *
     * @param mixed $value
     * @return bool
     */
    public function validateArray($value)
    {
        return is_array($value);
    }

    /**
     * 验证是否只包含字母
     *
     * @param mixed $value
     * @return bool
     */
    public function validateAlpha($value)
    {
        return preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * 验证属性只包含字母数字字符。
     * @param mixed $value
     * @return bool
     */
    public function validateAlphaNum($value)
    {
        return preg_match('/^[\pL\pM\pN]+$/u', $value);
    }

    /**
     * 验证属性只包含字母数字字符和下划线，破折号。
     *
     * @param mixed $value
     * @return bool
     */
    public function validateAlphaDash($value)
    {
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value);
    }

    /**
     * 验证手机号是否正确
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public static function validateMobile($value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        return preg_match('#^1[3,4,5,6,7,8,9]{1}\d{9}$#', $value) ? true : false;
    }

    /**
     * 验证邮箱是否正确
     * @param mixed $value
     * @return bool
     */
    public function validateEmail($value)
    {
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (preg_match($pattern, $value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证是否符合正则表达式
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateRegex($value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'regex');
        return preg_match($parameters[0], $value);
    }

    /**
     * 验证属性是有效日期。
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateDate($value)
    {
        if ($value instanceof DateTime) {
            return true;
        }
        if (strtotime($value) === false) {
            return false;
        }
        $date = date_parse($value);
        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * 验证两个属性是否相同
     *
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateSame($value, $parameters)
    {
        $other = Arr::get($this->data, $parameters[0]);
        return (isset($other) && $value == $other);
    }

    /**
     * 验证是否为数字
     *
     * @param mixed $value
     * @return bool
     */
    public function validateNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * 验证是否为数字,并且大于0
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateZeroNumeric($value)
    {
        if (is_numeric($value)) {
            return $value > 0;
        } else {
            return false;
        }
    }

    /**
     * 验证是否为整数
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateInteger($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * 验证身份证是否正确,暂时只支持18位的身份证信息
     *
     * @param mixed $value
     * @return bool
     */
    public static function validateIdCard($value)
    {

        // 只能是18位
        if (strlen($value) != 18) {
            return false;
        }
        // 取出本体码
        $idcard_base = substr($value, 0, 17);
        // 取出校验码
        $verify_code = substr($value, 17, 1);
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // 校验码对应值
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        // 根据前17位计算校验码
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($idcard_base, $i, 1) * $factor[$i];
        }
        // 取模
        $mod = $total % 11;
        // 比较校验码
        if ($verify_code == $verify_code_list[$mod]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证网址是否正确
     *
     * @param string $str
     * @return bool
     */
    public static function isUrl($url)
    {
        if (empty($url)) {
            return false;
        }
        return preg_match('/^(http|https):\/\/.?$/i', $url);
    }

    /**
     * 检查姓名是否符合规定(2-10中文 或者 2-20英文)
     *
     * @param string $truename 要检查的姓名
     * @return  true or false
     */
    public static function isTrueName($truename)
    {

        return preg_match("/^[\x{4e00}-\x{9fa5}]{2,4}$|^[a-zA-Z\s]*[a-zA-Z\s]{2,20}$/isu", $truename);
    }

    /**
     * 检查微信号是否符合规定
     *
     * @param string $wechat 要检查的微信号
     * @return  true or false
     */
    public static function isWechat($wechat)
    {

        return preg_match("/^[_a-zA-Z0-9]{5,19}+$/isu", $wechat);
    }

    /**
     * 检查微信openid是否符合规定
     *
     * @param string $wechat 要检查的微信号
     * @return  true or false
     */
    public static function isWechatOpenId($openid)
    {

        return preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[\da-zA-Z-_]{28}$/", $openid);
    }

    /**
     * 检验密码是否一致
     * @param $password 未加密的密码
     * @param $checkPassword 加密后的密码
     * @param $salt 加密值
     * @return true or false
     * @author 白沐泽
     */
    public static function checkPassword($password, $checkPassword, $salt)
    {
        if (md5(md5($password) . $salt) != $checkPassword) {
            return false;
        }
        return true;
    }

    /**
     * 校验密码强度
     * @param $value 检验的密码
     * $score>=1 && $score<=3 弱
     * $score>=4 && $score<=6 中等
     * $score>=7 && $score<=8 强
     * $score>=9 && $score<=10 极好
     * @return int
     * @author 白沐泽
     */
    public static function passwordPower($value)
    {
        $score = 0;
        if (preg_match("/[0-9]+/", $value)) {
            $score++;
        }
        if (preg_match("/[0-9]{3,}/", $value)) {
            $score++;
        }
        if (preg_match("/[a-z]+/", $value)) {
            $score++;
        }
        if (preg_match("/[a-z]{3,}/", $value)) {
            $score++;
        }
        if (preg_match("/[A-Z]+/", $value)) {
            $score++;
        }
        if (preg_match("/[A-Z]{3,}/", $value)) {
            $score++;
        }
        if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/", $value)) {
            $score += 2;
        }
        if (preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]{3,}/", $value)) {
            $score++;
        }
        if (strlen($value) >= 10) {
            $score++;
        }
        return $score;
    }
}