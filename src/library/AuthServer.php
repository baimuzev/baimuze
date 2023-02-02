<?php


namespace BaiMuZe\library;

use app\admin\model\AbilityModel;
use BaiMuZe\core\Controller;
use BaiMuZe\model\Auth;
use BaiMuZe\utility\Str;
use BaiMuZe\utility\Validator;
use think\Container;
use think\exception\HttpResponseException;

/**
 *  权限服务层
 * @author 白沐泽
 */
class AuthServer
{
    protected $app;//当前app
    protected $ability;//功能库
    protected $url;//当前访问的url
    protected $rootUrl;//当前根目录
    protected $controller;//当前访问控制器
    protected $action;//当前访问方法
    protected $class;//当前控制器

    public function __construct(Controller $class)
    {
        $this->app = AppServer::$sapp;
        $this->url = $this->app->request->url();
        $this->rootUrl = $this->app->request->rootUrl();
        $this->controller = $this->app->request->controller();
        $this->action = $this->app->request->action();
        $this->class = $class;
        $this->initialize();
    }

    public static function instance()
    {
        return Container::getInstance()->invokeClass(static::class);
    }

    /**
     *  初始化权限服务
     * @author 白沐泽
     */
    public function initialize()
    {
        $this->CheckAuth();
    }

    /**
     *  校验权限
     * @param string $rootUrl
     * @param bool $login //强制登录
     * @author 白沐泽
     */
    protected function CheckAuth(string $rootUrl='', bool $login = false)
    {
        //获取访问的功能信息
        $rootUrl = $rootUrl ? $this->rootUrl : $this->rootUrl = $rootUrl;
        $admin = session('admin');
        $rootUrl = str_replace('/', '', $this->rootUrl);
        $path = Str::lower($rootUrl . '/' . $this->controller . '/' . $this->action);
        if (empty($admin) && $login == true)$this->class->error( shortUrl('admin/index/login'), 301);
        $ability = AbilityModel::mk()->where([
            'path' => $path
        ])->find();
        //不是null
        if (!is_null($ability)) {
            //是否需要密码
            if ($ability->is_password) {
                $password = $this->app->request->post('_password');
                if (!$password) $this->class->error('密码错误', array(), 5);
                if (!Validator::checkPassword($password, $admin['password'], $admin['salt'])) $this->class->error('密码错误', array(), 5);
            } else if ($ability->power != 'public') {
                $auth = explode(',', $admin['auth']);
                if (empty($auth)) $this->class->error('请重新登录', ['url' => shortUrl('admin/index/login')], 6);
                if (!in_array(1, $auth)) {
                    $authList = Auth::mk()->where('id', 'in', $auth)->column('node');
                    if (!in_array('*', $authList) && !in_array($ability->id, $authList)) $this->class->error('没有相关权限，请退出登录后再试', ['url' => shortUrl('admin/index/login')], 6);
                }
            }
        }
        return true;
    }

    /**
     *  权限密码校验
     * @author 白沐泽
     */
    protected function password()
    {

    }
}