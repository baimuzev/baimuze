<?php


namespace BaiMuZe\model;


use BaiMuZe\core\Model;

/**
 * 后台管理员模型
 * @author 白沐泽
 * @createat 2022-12-06
 */
class Admin extends Model
{
    protected $table = 'bai_admin';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'created_time';
    protected $updateTime = 'update_time';

    /**
     * 表格设置
     * @author 白沐泽
     */
    public function getTitleSetting()
    {
        $titleSettings = [
            ['field' => 'avatar', 'title' => '头像', 'toolbar' => '#avatar'],
            ['field' => 'username', 'title' => '用户名'],
            ['field' => 'nickname', 'title' => '昵称'],
            ['field' => 'loginip', 'title' => '登录ip'],
            ['field' => 'logintime', 'title' => '登录时间'],
            ['field' => 'nickname', 'title' => '昵称'],
            ['field' => 'status', 'title' => '状态', 'toolbar' => '#status'],
            ['fixed' => 'right', 'title' => '操作', 'toolbar' => '#barDemo'],
        ];
        return arrayToJson($titleSettings);
    }

    /**
     * 重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword);
        $ret = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    // 密码加密
    protected function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }
}