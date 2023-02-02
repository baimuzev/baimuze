<?php


namespace BaiMuZe\model;


use BaiMuZe\core\Model;

/**
 * 系统权限规则模型
 * @author 白沐泽
 */
class Auth extends Model
{
    protected $table = 'bmz_admin_auth';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'created_at';
    /**
     * 表格设置
     * @author 白沐泽
     */
    public  function getTitleSetting()
    {
        $titleSettings = [
            ['field' => 'title', 'title' => '规则名称'],
            ['field' => 'desc', 'title' => '规则描述'],
            ['field' => 'status', 'title' => '状态', 'toolbar' => '#status'],
            ['fixed' => 'right', 'title' => '操作', 'toolbar' => '#barDemo'],
        ];
        return arrayToJson($titleSettings);
    }
}