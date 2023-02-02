<?php


namespace BaiMuZe\library;

use BaiMuZe\model\Icon;

/**
 * 系统图标服务
 * @author 白沐泽
 */
class IconServer
{
    /**
     * 获取图标列表
     * @author 白沐泽
     */
    public static function list()
    {
        $list=AppServer::$sapp->cache->get('IconList',[]);
        if (count($list)>0)return $list;
        $list=Icon::mk()->select()->toArray();
        AppServer::$sapp->cache->set('IconList',$list);
        return $list;
    }

}