<?php


namespace BaiMuZe\library;




use think\Request;

class RouterServer
{
    /**
     * 当前请求对象类型
     * @type OBJECT
     */
    protected $request ;

    /**
     * 路由实例化
     * 检测路由并响应
     * @param Request $request
     * @author 白沐泽
     * @createdate 2022-11-22
     */
    public function dispatch(Request $request)
    {
        $this->request = $request;
        //获取当前的域名信息
        $domain=$request->domain();
        //注册当前的根目录
        AppServer::$sapp->bind('router.base',$request->baseUrl());

    }

}