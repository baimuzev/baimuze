<?php


namespace BaiMuZe\model;

use BaiMuZe\core\Model;

/**
 * 储存管理模型
 * @author 白沐泽
 */
class Storage extends Model
{
    protected $table = 'bmz_storage';
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
            ['field' => 'name', 'title' => '存储方式'],
            ['field' => 'label', 'title' => '调用标签'],
            ['field' => 'status', 'title' => '状态', 'toolbar' => '#status'],
            ['fixed' => 'right', 'title' => '操作', 'toolbar' => '#barDemo'],
        ];
        return arrayToJson($titleSettings);
    }
}