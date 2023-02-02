<?php


namespace BaiMuZe\utility;

/**
 * 常用的数据处理
 * @author 白沐泽
 */
class Data
{

    /**
     * 一维数组生成数据树
     * @param array $its 待处理数据
     * @param string $cid 自己的主键
     * @param string $pid 上级的主键
     * @param string $children 子数组名称
     * @return array
     */
    public static function arr2tree(array $its, string $cid = 'id', string $pid = 'pid', string $children = 'children'): array
    {
        [$tree, $its] = [[], array_column($its, null, $cid)];
        foreach ($its as $it) isset($its[$it[$pid]]) ? $its[$it[$pid]][$children][] = &$its[$it[$cid]] : $tree[] = &$its[$it[$cid]];
        return $tree;
    }

}