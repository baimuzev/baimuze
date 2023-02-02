<?php


namespace BaiMuZe\model;


use BaiMuZe\core\Model;

/**
 * 功能模型
 * @author 白沐泽
 */
class Ability extends Model
{
    protected $table = 'bmz_ability';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'created_at';

    /**
     * 表格设置
     * @author 白沐泽
     */
    public function getTitleSetting()
    {
        $titleSettings = [
            ['type' => 'checkbox'],
            ['field' => 'id', 'title' => 'ID', 'sort' => true],
            ['field' => 'title', 'title' => '标题'],
            ['field' => 'url', 'title' => '访问链接'],
            ['fixed' => 'right', 'title' => '操作', 'toolbar' => '#barDemo'],
        ];
        return arrayToJson($titleSettings);
    }
}