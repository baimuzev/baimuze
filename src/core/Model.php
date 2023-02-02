<?php


namespace BaiMuZe\core;

use BaiMuZe\library\helper\DeleteHelper;
use BaiMuZe\library\helper\FormHelper;
use BaiMuZe\library\helper\QueryHelper;
use BaiMuZe\library\helper\SaveHelper;
use think\Container;
use think\Model as thinkModel;

/**
 * 模型基类
 * @see \think\db\Query
 * @mixin \think\db\Query
 *
 * @method void onAdminSave(string $ids) 记录状态变更日志
 * @method void onAdminUpdate(string $ids) 记录更新数据日志
 * @method void onAdminInsert(string $ids) 记录新增数据日志
 * @method void onAdminDelete(string $ids) 记录删除数据日志
 *
 * @method QueryHelper Query($input = null, callable $callable = null) static 快捷查询逻辑器
 * @method bool|array Form(string $template = '', string $field = '', mixed $where = [], array $data = []) static 快捷表单逻辑器
 * @method bool|null bDelete(string $field = '', mixed $where = []) static 快捷删除逻辑器
 * @method bool bSave(array $data = [], string $field = '', mixed $where = []) static 快捷更新逻辑器
 * @author 白沐泽
 */
abstract class Model extends thinkModel
{
    /**
     * 日志类型
     * @var string
     */
    protected $oplogType;

    /**
     * 日志名称
     * @var string
     */
    protected $oplogName;
    /**
     * 日志过滤
     * @var callable
     */
    public static $oplogCall;
    /**
     * 创建模型实例
     * @return static
     */
    public static function mk($data = [])
    {
        return new static($data);
    }
    /**
     * 调用魔术方法
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return $this|false|mixed
     */
    public function __call($method, $args)
    {
        $oplogs = [
            'onAdminSave'   => "修改%s[%s]状态",
            'onAdminUpdate' => "更新%s[%s]记录",
            'onAdminInsert' => "增加%s[%s]成功",
            "onAdminDelete" => "删除%s[%s]成功",
        ];
        if (isset($oplogs[$method])) {
            if ($this->oplogType && $this->oplogName) {
                $changeIds = $args[0] ?? '';
                if (is_callable(static::$oplogCall)) {
                    $changeIds = call_user_func(static::$oplogCall, $method, $changeIds, $this);
                }
//                sysoplog($this->oplogType, lang($oplogs[$method], [lang($this->oplogName), $changeIds]));
            }
            return $this;
        } else {
            return parent::__call($method, $args);
        }
    }
    /**
     * 静态魔术方法
     * @author 白沐泽
     */
    public static function __callStatic($method, $args)
    {
        $helpers = [
            'Query' => [QueryHelper::class, 'init'],
            'Form'=>[FormHelper::class,'init'],
            'bDelete'=>[DeleteHelper::class,'init'],
            'bSave'=>[SaveHelper::class,'init']
        ];
        if (isset($helpers[$method])) {
            [$class, $method] = $helpers[$method];
            return Container::getInstance()->invokeClass($class)->$method(static::class, ...$args);
        } else {
            return parent::__callStatic($method, $args);
        }
    }

}