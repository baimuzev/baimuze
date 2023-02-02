<?php


namespace BaiMuZe\library;

use BaiMuZe\model\Config;

/**
 * 系统配置服务
 * @author 白沐泽
 */
class ConfigServer
{
    /**
     * 获取配置列表
     * @param boolean $force 强制刷新
     * @return array
     * @author 白沐泽
     */
    public static function list(bool $force = false): array
    {
//        if (!$force && !empty(AppServer::$sapp->cache->get('systemConfig'))) {
//            return AppServer::$sapp->cache->get('systemConfig');
//        }
        $list = Config::mk()->select()->toArray();
        $list = static::build($list);
        AppServer::$sapp->cache->set('systemConfig', $list);
        return $list;
    }

    /**
     * 规划参数格式
     * @param array $list 配置数组
     * @return array
     * @author 白沐泽
     */
    public static function build(array $list): array
    {
        if (empty($list)) return [];
        $source = \config('base.config_source');

        $data = array();
        foreach ($source as $sk => $sv) {
            $data[$sk] = array();
        }
        foreach ($list as $k => $v) {
            if (isset($data[$v['source']])) $data[$v['source']][$v['label']] = $v;
        }
        return $data;
    }

    /**
     *  获取配置类型
     * @param string $label 配置类型
     * @param string $key 下标
     * @param $default 默认值
     * @author 白沐泽
     */
    public static function get(string $label, string $key=null, $default = null)
    {
        $config = static::list();
        if (isset($config[$label])&&$key===null)return$config[$label];
        if (isset($config[$label]) && isset($config[$label][$key])) return $config[$label][$key]['value'];
        return $default;
    }
}