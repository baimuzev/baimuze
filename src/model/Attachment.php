<?php


namespace BaiMuZe\model;

use BaiMuZe\core\Model;
use think\model\concern\SoftDelete;

/**
 * 文件映射模型
 * @author 白沐泽
 */
class Attachment extends Model
{
    use SoftDelete;
    protected $table = 'bmz_attachment';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';
}