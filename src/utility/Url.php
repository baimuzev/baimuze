<?php


namespace BaiMuZe\utility;


use BaiMuZe\library\AppServer;

class Url
{
    /**
     * 生成最短URL地址
     * @param string $url 路由地址
     * @param array $vars PATH 变量
     * @param boolean|string $suffix 后缀
     * @param boolean|string $domain 域名
     * @return string
     */
    public static function shortUrl(string $url = '', array $vars = [], $suffix = true, $domain = false):string
    {
        // 读取默认节点配置
        $app = AppServer::$sapp->config->get('route.default_app') ?: 'index';
        $ext = AppServer::$sapp->config->get('route.url_html_suffix') ?: 'php';
        $act = Str::lower(AppServer::$sapp->config->get('route.default_action') ?: 'index');
        $ctr = Str::snake(AppServer::$sapp->config->get('route.default_controller') ?: 'index');
        // 生成完整链接地址
        $pre = AppServer::$sapp->route->buildUrl('@')->suffix(false)->domain($domain)->build();
        $uri = AppServer::$sapp->route->buildUrl($url, $vars)->suffix($suffix)->domain($domain)->build();
        // 替换省略链接路径
        return preg_replace([
            "#^({$pre}){$app}/{$ctr}/{$act}(\.{$ext}|^\w|\?|$)?#i",
            "#^({$pre}[\w.]+)/{$ctr}/{$act}(\.{$ext}|^\w|\?|$)#i",
            "#^({$pre}[\w.]+)(/[\w.]+)/{$act}(\.{$ext}|^\w|\?|$)#i",
            "#/\.{$ext}$#i",
        ], ['$1$2', '$1$2', '$1$2$3', ''], $uri);
    }
    /**
     * 获取当前完整的请求地址
     * @return string
     */
    public function full()
    {
        return app('request')->domain() . app('request')->url();
    }
    /**
     * 生成一个完整的网址链接
     * @param  string      $path      如果不是完整的网址，则格式为 控制器/操作，如果跨域，则开头需要使用@模块/控制器/操作
     * @param  bool        $is_full   是否返回完整的连接以及解决跨域，
     * @return string
     */
    public function make($path='',$is_full=0) {
        if ($this->isValidUrl($path)) {
            return $path;
        }
        $is_allow=0; //是否跨域
        //如果以./开头，则定位到当前分组根目录
        if(substr($path,0,2)=='./'){
            $path='@'.app('router.group').substr($path,1);
        }
        //如果以@开头，则证明为跨域，跨域需要完整的模块/控制器/操作
        if(substr($path,0,1)=='@'){
            $path=substr($path,1);
            $is_allow=1;
        }else{
//            if(app('router.group')!==app('router.module')){
//                $path=app('router.module').'/'.$path;
//            }
        }
        $root=$is_allow==1?$this->full():app('request')->root();
        $base=app('request')->baseUrl();
        $path=ltrim($path,'/');
        if(empty($path) && $is_allow==1){
            return $is_full==1?$root:'/';
        }
        if(empty($url)){
            return $is_full==1?($root):($base);
        }else{
//            return $is_full==1?($root.'/'.$url):($base.'/'.$url);
        }
    }
    /**
     * 判断是否为一个有效的链接地址
     * @param  string  $path
     * @return bool
     */
    public function isValidUrl($path) {
        foreach (array('#', '//', 'mailto:', 'tel:') as $val) {
            if ($val != '' && strpos($path, $val) === 0)
                return true;
        }
        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }
}