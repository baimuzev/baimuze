<?php


namespace BaiMuZe\library;


use BaiMuZe\utility\Security;
use think\App;
use think\app\MultiApp;
use think\middleware\LoadLangPack;
use think\middleware\SessionInit;
use think\Request;
use think\Service;
use Closure;

/**
 * 系统模块服务
 * @author 白沐泽
 * @createdate 2022-11-22
 */
class AppServer extends Service
{
    /**
     * 静态应用实例
     * @var App
     */
    public static $sapp;

    /**
     * 启动服务
     */
    public function boot()
    {
//        // 静态应用赋值
//        static::$sapp = $this->app;
        // 服务初始化处理
        $this->app->event->listen('HttpRun', function (Request $request) {
            // 配置默认输入过滤
            $request->filter([function ($value) {
                return is_string($value) ? xss_safe($value) : $value;
            }]);
            // 注册多应用中间键
            $this->app->middleware->add(MultiApp::class);
            // 判断访问模式兼容处理
            if ($request->isCli()) {
                // 兼容 CLI 访问控制器
                if (empty($_SERVER['REQUEST_URI']) && isset($_SERVER['argv'][1])) {
                    $request->setPathinfo($_SERVER['argv'][1]);
                }
            } else {
                // 兼容 HTTP 调用 Console 后 URL 问题
                $request->setHost($request->host());
            }
        });
    }

    /**
     * 初始化服务
     */
    public function register()
    {
        // 动态加载应用初始化系统函数
        $this->app->lang->load(dirname(dirname(__FILE__)) . "/lang/zh-cn.php", 'zh-cn');
        $this->app->config->load(dirname(dirname(__FILE__)) . "/core/config.php", 'base');
        //绑定方法
        $this->app->bind('security',Security::class);
        // 终端 HTTP 访问时特殊处理
        if (!$this->app->request->isCli()) {
            // 如果是 YAR 接口或指定情况下，不需要初始化会话和语言包，否则有可能会报错
            $isYarRpc = stripos($this->app->request->header('user_agent', ''), 'PHP Yar RPC-');
            if ($isYarRpc === false && intval($this->app->request->get('not_init_session', 0)) < 1) {
                // 注册会话初始化中间键
                $this->app->middleware->add(SessionInit::class);
                // 注册语言包处理中间键
                $this->app->middleware->add(LoadLangPack::class);
            }
            // 注册访问处理中间键
            $this->app->middleware->add(function (Request $request, Closure $next) {
                $header = [];
                // 加载对应组件的语言包
                $langSet = $this->app->lang->getLangSet();
                if (file_exists($file = __DIR__ . "/lang/{$langSet}.php")) {
                    $this->app->lang->load($file, $langSet);
                }

                // HTTP.CORS 跨域规则配置
                if (($origin = $request->header('origin', '*')) !== '*') {
                    if (is_string($hosts = $this->app->config->get('app.cors_host', []))) $hosts = str2arr($hosts);
                    if ($this->app->config->get('app.cors_auto', 1) || in_array(parse_url(strtolower($origin), PHP_URL_HOST), $hosts)) {
                        $headers = $this->app->config->get('app.cors_headers', 'Api-Name,Api-Type,Api-Token,User-Form-Token,User-Token,Token');
                        $header['Access-Control-Allow-Origin'] = $origin;
                        $header['Access-Control-Allow-Methods'] = $this->app->config->get('app.cors_methods', 'GET,PUT,POST,PATCH,DELETE');
                        $header['Access-Control-Allow-Headers'] = "Authorization,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-Requested-With,{$headers}";
                        $header['Access-Control-Expose-Headers'] = $headers;
                        $header['Access-Control-Allow-Credentials'] = 'true';
                    }
                }
                // 访问模式及访问权限检查
                if ($request->isOptions()) {
                    return response()->code(204)->header($header);
                } else if ($request->url() == '/') {//后期添加未登录
//                    return redirect('/admin/index/login', 302);
//                    return json(['code'=>0,'info'=>BmzLang('no_login'),'url'=>'http://www.baidu.com/'])->header($header);
                }
                return $next($request)->header($header);
            }, 'route');
        }
    }
}