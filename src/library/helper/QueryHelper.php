<?php


namespace BaiMuZe\library\helper;

use think\db\BaseQuery;
use think\db\Query;
use think\Model;

/**
 * 搜索条件处理器
 * Class QueryHelper
 * @package think\admin\helper
 * @see \think\db\Query
 * @mixin Query
 */
class QueryHelper extends Helper
{

    /**
     * 分页助手工具
     * @var PageHelper
     */
    protected $page;
    /**
     * 当前数据操作
     * @var Query
     */
    protected $query;
    /**
     * 初始化默认数据
     * @var array
     */
    protected $input;
    /**
     * 初始化显示的数据
     * @var string
     */
    protected $showFiled;

    /**
     * 获取当前Db操作对象
     * @return Query
     */
    public function db(): Query
    {
        return $this->query;
    }

    /**
     * 初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param string|array|null $input 输入数据
     * @param callable|null $callable 初始回调
     * @return $this
     * @throws \think\db\exception\DbException
     */
    public function init($dbQuery, $input = null, ?callable $callable = null): QueryHelper
    {
        $this->page = PageHelper::instance();
        $this->input = $this->getInputData($input);
        $this->query = self::CreateQuery($dbQuery);
        is_callable($callable) && call_user_func($callable, $this, $this->query);
        return $this;
    }

    /**
     * 获取输入数据
     * @param string|array|null $input
     * @return array
     */
    private function getInputData($input): array
    {
        if (is_array($input)) {
            return $input;
        } else {
            $input = $input ?: 'request';
            return $this->app->request->$input();
        }
    }

    /**
     * 设置where条件
     * @param $where
     * @param $value
     */
    public function where($fields, $value = '')
    {
        if (is_array($fields)) {
            $this->query->where($fields);
        } else {
            $this->query->where($fields, $value);
        }
        return $this;
    }
    /**
     * 设置 field 查询字段
     * @param string|array $fields 查询字段
     * @return $this
     */
    public function field($fields): QueryHelper
    {
        $this->showFiled = $fields;
        $this->query->field($fields);
        return $this;
    }

    /**
     * 设置 Like 查询条件
     * @param string|array $fields 查询字段
     * @param string $split 前后分割符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function like($fields, string $split = '', $input = null, string $alias = '#'): QueryHelper
    {
        $data = $this->getInputData($input ?: $this->input);
        foreach (is_array($fields) ? $fields : explode(',', $fields) as $field) {
            [$dk, $qk] = [$field, $field];
            if (stripos($field, $alias) !== false) {
                [$dk, $qk] = explode($alias, $field);
            }
            if (isset($data[$qk]) && $data[$qk] !== '') {
                $this->query->whereLike($dk, "%{$split}{$data[$qk]}{$split}%");
            }
        }
        return $this;
    }

    /**
     * 设置日期时间区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function dateBetween($fields, string $split = ' - ', $input = null, string $alias = '#'): QueryHelper
    {
        return $this->setBetweenWhere($fields, $split, $input, $alias, function ($value, $type) {
            if (preg_match('#^\d{4}(-\d\d){2}\s+\d\d(:\d\d){2}$#', $value)) return $value;
            else return $type === 'after' ? "{$value} 23:59:59" : "{$value} 00:00:00";
        });
    }

    /**
     * 设置时间戳区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function timeBetween($fields, string $split = ' - ', $input = null, string $alias = '#'): QueryHelper
    {
        return $this->setBetweenWhere($fields, $split, $input, $alias, function ($value, $type) {
            if (preg_match('#^\d{4}(-\d\d){2}\s+\d\d(:\d\d){2}$#', $value)) return strtotime($value);
            else return $type === 'after' ? strtotime("{$value} 23:59:59") : strtotime("{$value} 00:00:00");
        });
    }

    /**
     * 设置 IN 区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function in($fields, string $split = ',', $input = null, string $alias = '#'): QueryHelper
    {
        $data = $this->getInputData($input ?: $this->input);
        foreach (is_array($fields) ? $fields : explode(',', $fields) as $field) {
            [$dk, $qk] = [$field, $field];
            if (stripos($field, $alias) !== false) {
                [$dk, $qk] = explode($alias, $field);
            }
            if (isset($data[$qk]) && $data[$qk] !== '') {
                $this->query->whereIn($dk, explode($split, strval($data[$qk])));
            }
        }
        return $this;
    }

    /**
     * layui.table快速生成
     * @param ?callable $befor 表单前置操作
     * @param ?callable $after 表单后置操作
     * @param array $showFiled 表格显示的字段
     * @param string $template 视图模板文件
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function layTable(?callable $befor = null, ?callable $after = null, string $template = '')
    {
        if (in_array($this->output, ['get.json', 'get.layui.table'])) {
            if (is_callable($after)) {
                call_user_func($after, $this, $this->query);
            }
            $this->page->layTable($this->query, $template);
        } elseif ($befor) {
            if (method_exists((self::CreateQuery($this->query))->getModel(), 'getTitleSetting')) {
                $this->class->cols = (self::CreateQuery($this->query))->getModel()->getTitleSetting();
            }
            call_user_func($befor, $this, $this->query);
            $this->class->fetch($template);
        }
    }

    /**
     *通过父级目录获取树形数组
     * @param ?callable $befor 表单前置操作
     * @param ?callable $after 表单后置操作
     * @param int $id 顶级ID
     * @return string   $field 父级字段名称
     * @author 白沐泽
     */
    public function Baitree($columns = '*', $field = 'parent_id', ?callable $befor = null, ?callable $after = null, string $template = '')
    {
        if (in_array($this->output, ['get.json', 'get.bai.dtree'])) {
            if (is_callable($after)) {
                call_user_func($after, $this, $this->query);
            }
            $this->page->tree($this->query, $columns, $field, $template);
        } elseif ($befor) {
            call_user_func($befor, $this, $this->query);
            $this->class->fetch($template);
        }
    }
}