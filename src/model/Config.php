<?php


namespace BaiMuZe\model;

use BaiMuZe\core\Model;

/**
 * 系统配置模型
 * @author 白沐泽
 */
class Config extends Model
{
    protected $table = 'bmz_config';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $updateTime = 'updated_at';
}