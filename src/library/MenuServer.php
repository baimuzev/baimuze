<?php


namespace BaiMuZe\library;

use BaiMuZe\model\Menu;
use BaiMuZe\utility\Data;

/**
 * 菜单节点服务
 * @author 白沐泽
 * @createat 2022-12-08
 */
class MenuServer
{
    /**
     * 当前菜单列表
     * @author 白沐泽
     */
    protected $menuList = [];

    /**
     * 获取系统菜单树数据
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getTree(): array
    {
        $query = Menu::mk()->where(['status' => 0])->with(['getAbility']);
        $menus = $query->order('sort desc,id asc')->select()->toArray();
        return static::build(Data::arr2tree($menus, 'id', 'parent_id'));
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus 当前菜单列表
     * @return array
     * @throws \ReflectionException
     */
    private static function build(array $menus): array
    {
        foreach ($menus as $key=>&$menu){
            if (!empty($menu['children'])) {
                $menu['children'] = static::build($menu['children']);
            }
            if (!empty($menu['children'])) {
                $menu['getAbility']['url'] = '#';
            } elseif ($menu['getAbility']['url'] === '#') {
                unset($menus[$key]);
            } elseif (preg_match('/^(https?:)?(\/\/|\\\\)/i', $menu['getAbility']['url'])) {
//                if (!!$menu['getAbility']['node'] && !AdminService::check($menu['getAbility']['node'])) {
//                    unset($menus[$key]);
//                } elseif ($menu['params']) {
//                    $menu['url'] .= (strpos($menu['url'], '?') === false ? '?' : '&') . $menu['params'];
//                }
            }
//            elseif (!!$menu['getAbility']['node'] && !AdminService::check($menu['getAbility']['node'])) {
//                unset($menus[$key]);
//            }
            else {
                $node = join('/', array_slice(explode('/', $menu['getAbility']['url']), 0, 3));
                if ($menu['ability_id']!=0){
                    $menu['getAbility']['url'] = url($menu['getAbility']['url'])->build() . ($menu['params'] ? '?' . $menu['params'] : '');
                }
//                if (!AdminService::check($node)) unset($menus[$key]);
            }
        }
        return $menus;
    }
}