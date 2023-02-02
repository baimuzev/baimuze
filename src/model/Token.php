<?php


namespace BaiMuZe\model;


use BaiMuZe\core\Model;

/**
 * 用户token管理
 * @author 白沐泽
 */
class Token extends Model
{
    protected $table = 'bmz_token';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'created_at';

    /**
     * 清除过期token
     * @author 白沐泽
     */
    public function clear()
    {
        $this->whereTime('expire_at', '<', time())->delete();
    }
}