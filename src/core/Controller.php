<?php

declare (strict_types=1);
namespace BaiMuZe\core;

use BaiMuZe\BaseController;
use BaiMuZe\library\AdminServer;
use BaiMuZe\library\AuthServer;
use BaiMuZe\utility\Str;
use BaiMuZe\utility\Validator;
use think\App;
use think\exception\HttpResponseException;
use think\facade\Lang;
use think\facade\Validate;
use think\Request;
use stdClass;

/**
 * 控制器基类
 * @author 白沐泽
 * @create_at 2022-12-02
 */
class Controller extends stdClass
{
    /**
     * 应用容器
     * @var App
     */
    public $app;
    /**
     * 请求对象
     * @var Request
     */
    public $request;
    /**
     * 请求GET参数
     * @var array
     */
    public $get = [];
    /**
     * 请求GET参数
     * @var array
     */
    public $post = [];
    /**
     * 验证控制器
     * @author 白沐泽
     */
    public $Vali;
    /**
     * 用户规则控制器
     * @author 白沐泽
     */
    public $admin;
    /**
     * 表单CSRF验证状态
     * @var boolean
     */
    public $csrf_state = false;
    /**
     * 初始化控制器
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->app->bind('BaiMuZe\core\Controller',$this);
        $this->request = $app->request;//初始化request请求
        $controllername = Str::parseName($this->request->controller());
        if (in_array($this->request->action(), get_class_methods(__CLASS__))) {
            $this->error('访问方法不存在');
        }
        $this->get = $this->request->get();//初始化get数据
        $this->post = $this->request->post();//初始化post数据
        $this->Vali = Validator::instance();
        AuthServer::instance();
        $this->loadlang($controllername);
        $this->admin = new AdminServer();
        $this->initialize();
    }

    /**
     * 控制器初始化
     */
    public function initialize()
    {

    }

    /**
     * 加载对应的控制器语言
     * @param string $name
     * @author 白沐泽
     */
    protected function loadlang($name)
    {
        $name = Str::parseName($name);
        $name = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $name) ? $name : 'index';
        $lang = $this->app->lang->getLangSet();
        Lang::load($this->app->getBasePath() . $this->request->root() . '/lang/' . $lang . '/' . str_replace('.', '/', $name) . '.php');
    }

    /**
     * 数据回调处理机制
     * @param string $name 回调方法名称
     * @param mixed $one 回调引用参数1
     * @param mixed $two 回调引用参数2
     * @param mixed $thr 回调引用参数3
     * @return boolean
     */
    public function callback(string $name, &$one = [], &$two = [], &$thr = []): bool
    {
        if (is_callable($name)) return call_user_func($name, $this, $one, $two, $thr);
        foreach (["_{$this->app->request->action()}{$name}", $name] as $method) {
            if (method_exists($this, $method) && false === $this->$method($one, $two, $thr)) {
                return false;
            }
        }
        return true;
    }
    /**
     * 返回视图内容
     * @param string $tpl 模板名称
     * @param array $vars 模板变量
     * @param null|string $node 授权节点
     */
    public function fetch(string $tpl = '', array $vars = [], ?string $node = null): void
    {
        foreach ($this as $name => $value) $vars[$name] = $value;
        if ($this->csrf_state) {

        } else {
            throw new  HttpResponseException(view($tpl, $vars));
        }
    }

    /**
     * 模板变量赋值
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return $this
     */
    public function assign($name, $value = ''): Controller
    {
        if (is_string($name)) {
            $this->$name = $value;
        } elseif (is_array($name)) {
            foreach ($name as $k => $v) {
                if (is_string($k)) $this->$k = $v;
            }
        }
        return $this;
    }

    /**
     * 返回成功的操作
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function success($info, $data = '{-null-}', $code = 1): void
    {
        if ($data === '{-null-}') $data = new stdClass();
        throw new HttpResponseException(json([
            'code' => $code, 'info' => $info, 'data' => $data,
        ]));
    }

    /**
     * 返回失败的操作
     * @param mixed $info 消息内容
     * @param mixed $data 返回数据
     * @param mixed $code 返回代码
     */
    public function error($info, $data = '{-null-}', $code = 0): void
    {
        if ($data === '{-null-}') $data = new stdClass();
        throw new HttpResponseException(json([
            'code' => $code, 'info' => $info, 'data' => $data,
        ]));
    }

    /**
     * URL重定向
     * @param string $url 跳转链接
     * @param integer $code 跳转代码
     */
    public function redirect(string $url, int $code = 301): void
    {
        throw new HttpResponseException(redirect($url, $code));
    }

    /**
     * 刷新Token
     */
    protected function token()
    {
        $token = $this->request->param('__token__');
        //验证Token
        if (!Validate::check(['__token__' => $token], ['__token__' => 'require|token'])) {
            $this->error(BmzLang('token'), ['__token__' => $this->request->buildToken()]);
        }

        //刷新Token
        $this->request->buildToken();
    }
}