<?php


namespace BaiMuZe\library\helper;

use BaiMuZe\library\AdminServer;
use think\Container;
use think\db\BaseQuery;
use think\db\Query;
use think\exception\HttpResponseException;
use think\Model;

/**
 * 列表处理管理器
 * Class PageHelper
 */
class PageHelper extends Helper
{

    /**
     * 实例对象反射
     * @param array $args
     * @return static
     */
    public static function instance(...$args): Helper
    {
        return Container::getInstance()->invokeClass(static::class, $args);
    }
    /**
     * 输出 XSS 过滤处理
     * @param array $items
     */
    private static function xssFilter(array &$items)
    {
        foreach ($items as &$item) if (is_array($item)) {
            static::xssFilter($item);
        } elseif (is_string($item)) {
            $item = htmlspecialchars($item, ENT_QUOTES);
        }
    }
    /**
     * 查询对象数量统计
     * @param BaseQuery|Query $query
     * @param boolean|integer $total
     * @return integer|boolean|string
     * @throws \think\db\exception\DbException
     */
    private static function getCount($query, $total = false)
    {
        if ($total === true || is_numeric($total)) return $total;
        [$query, $options] = [clone $query, $query->getOptions()];
        if (isset($options['order'])) $query->removeOption('order');
        if (empty($options['union'])) return $query->count();
        $table = [$query->buildSql() => '_union_count_'];
        return $query->newQuery()->table($table)->count();
    }
    public function layTable($dbQuery, string $template = '')
    {
        if ($this->output === 'get.json') {
            $get = $this->app->request->get();
            $query = static::CreateQuery($dbQuery);
            // 根据参数排序
            if (isset($get['_field_']) && isset($get['_order_'])) {
                $dbQuery->order("{$get['_field_']} {$get['_order_']}");
            }
//            return PageHelper::instance()->init($query);
        }
        if ($this->output === 'get.layui.table') {//配合js快速生成表格
            $get = $this->app->request->get();
            $query = $this->autoSortQuery($dbQuery);
            // 根据参数排序
            if (isset($get['_field_']) && isset($get['_order_'])) {
                $query->order("{$get['_field_']} {$get['_order_']}");
            }
            // 数据分页处理
            if (empty($get['page']) || empty($get['limit'])) {
                $data = $query->select()->toArray();
                $result = ['msg' => '', 'code' => 0, 'count' => count($data), 'data' => $data];
            } else {
                $cfg = ['list_rows' => $get['limit'], 'query' => $get];
                $data = $query->paginate($cfg, static::getCount($query))->toArray();
                $result = ['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']];
            }
            static::xssFilter($result['data']);
            if (false !== $this->class->callback('_page_filter', $result['data'], $result)) {
                throw new HttpResponseException(json($result));
            } else {
                return $result;
            }
        }else {
            $this->class->fetch($template);
            return [];
        }
    }
    /**
     * 绑定排序并返回操作对象
     * @param Model|BaseQuery|string $dbQuery
     * @return Query
     * @throws \think\db\exception\DbException
     */
    public function autoSortQuery($dbQuery): Query
    {
        $query = static::CreateQuery($dbQuery);
        if ($this->app->request->isPost() && $this->app->request->post('action') === 'sort') {
            if (!(new AdminServer())->isLogin()) new HttpResponseException(json([
                'code' => 0, 'info' => BmzLang('busy')]));
            if (method_exists($query, 'getTableFields') && in_array('sort', $query->getTableFields())) {
                if ($this->app->request->has($pk = $query->getPk() ?: 'id', 'post')) {
                    $map = [$pk => $this->app->request->post($pk, 0)];
                    $data = ['sort' => intval($this->app->request->post('sort', 0))];
                    if ($query->newQuery()->where($map)->update($data) !== false) {
                        $this->class->success(lang('think_library_sort_success'), '');
                    }
                }
            }
            $this->class->error(lang('think_library_sort_error'));
        }
        return $query;
    }
}