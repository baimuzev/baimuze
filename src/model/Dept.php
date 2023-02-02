<?php


namespace BaiMuZe\model;

use BaiMuZe\core\Model;

/**
 *系统机构模型
 *@author 白沐泽
 */
class Dept extends Model
{
    protected $table = 'bmz_admin_dept';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}