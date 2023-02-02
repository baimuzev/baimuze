<?php


namespace BaiMuZe\model;


use BaiMuZe\core\Model;
use think\model\concern\SoftDelete;

class Menu extends Model
{
    use SoftDelete;
    protected $table = 'bmz_menu';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    public function getAbility()
    {
        return $this->hasOne(Ability::class, 'id', 'ability_id');
    }
    /**
     * 时间戳字段
     * @author 白沐泽
     */
    public function getWriteTimestamp(){
        return $this->autoWriteTimestamp;
    }

}