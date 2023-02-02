<?php


namespace BaiMuZe\library;

use BaiMuZe\model\Admin;
use BaiMuZe\utility\Random;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;

/**
 * 后台权限管理类
 * @author 白沐泽
 * @createat 2022-12-06
 */
class AdminServer
{
    /**
     * 当前请求实例
     * @var Request
     */
    protected $request;
    protected $_error = '';
    protected $logined = false; //登录状态

    /**
     * 管理员登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param int $keeptime 有效时长
     * @return  boolean
     */
    public function login($username, $password, $keeptime = 0)
    {
        $admin = (new Admin())->where(['username' => $username])->find();
        if (!$admin) {
            $this->setError('no_account');
            return false;
        }
        if ($admin['status'] == 1) {
            $this->setError('status_account');
            return false;
        }
        if (config('base.admin.login_failure_retry') && $admin->loginfailure >= 10 && time() - $admin->update_time < 86400) {
            $this->setError('Please try again after 1 day');
            return false;
        }
        if ($admin->password != md5(md5($password) . $admin->salt)) {
            $admin->loginfailure++;
            $admin->save();
            $this->setError('Password is incorrect');
            return false;
        }
        $admin->loginfailure = 0;
        $admin->logintime = time();
        $admin->loginip = request()->ip();
        $admin->token = Random::uuid();
        $admin->save();
        Session::set("admin", $admin->toArray());
        $this->keeplogin($keeptime);
        return true;
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $admin = (new Admin())->find(intval($this->id));
        if ($admin) {
            $admin->token = '';
            $admin->save();
        }
        $this->logined = false; //重置登录状态
        Session::delete("admin");
        Cookie::delete("keeplogin");
        return true;
    }

    /**
     * 自动登录
     * @return boolean
     */
    public function autologin()
    {
        $keeplogin = Cookie::get('keeplogin');
        if (!$keeplogin) {
            return false;
        }
        list($id, $keeptime, $expiretime, $key) = explode('|', $keeplogin);
        if ($id && $keeptime && $expiretime && $key && $expiretime > time()) {
            $admin = (new Admin())->find($id);
            if (!$admin || !$admin->token) {
                return false;
            }
            //token有变更
            if ($key != md5(md5($id) . md5($keeptime) . md5($expiretime) . $admin->token . config('token.key'))) {
                return false;
            }
            $ip = request()->ip();
            //IP有变动
            if ($admin->loginip != $ip) {
                return false;
            }
            Session::set("admin", $admin->toArray());
            //刷新自动登录的时效
            $this->keeplogin($keeptime);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 刷新保持登录的Cookie
     *
     * @param int $keeptime
     * @return  boolean
     */
    protected function keeplogin($keeptime = 0)
    {
        if ($keeptime) {
            $expiretime = time() + $keeptime;
            $key = md5(md5($this->id) . md5($keeptime) . md5($expiretime) . $this->token . config('token.key'));
            $data = [$this->id, $keeptime, $expiretime, $key];
            Cookie::set('keeplogin', implode('|', $data), 86400 * 7);
            return true;
        }
        return false;
    }

    /**
     * 检测是否登录
     *
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }
        $admin = Session::get('admin');
        if (!$admin) {
            return false;
        }
        $my = (new Admin())->find($admin['id']);
        //判断是否同一时间同一账号只能在一个地方登录
        if (config('base.admin.login_unique')) {
            if (!$my || $my['token'] != $admin['token']) {
                $this->logined = false; //重置登录状态
                Session::delete("admin");
                Cookie::delete("keeplogin");
                return false;
            }
        }
        //判断管理员IP是否变动
        if (config('base.admin.loginip_check')) {
            if (!isset($admin['loginip']) || $admin['loginip'] != request()->ip()) {
                $this->logout();
                return false;
            }
        }
        //判断管理员密码是否变动
        if (!isset($admin['password']) || $my['password'] != $admin['password']) {
            $this->logout();
            return false;
        }
        $this->logined = true;
        return true;
    }

    /**
     * 获取当前登录
     * @author 白沐泽
     */
    public function AdminInfo()
    {
        if ($this->logined) {
            return true;
        }
        $admin = Session::get('admin');
        if (!$admin) {
            return false;
        }
        return $admin;
    }

    /**
     * 检测是不是超级管理员
     * @author 白沐泽
     */
    public function isSuper()
    {
        if ($this->logined) {
            $admin = Session::get('admin');
            if (!$admin) {
                return false;
            }
            if ($admin['id'] === 1) return true;
            return false;
        }
        return false;
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return AdminServer
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? BmzLang($this->_error) : '';
    }
}