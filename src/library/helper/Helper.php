<?php


namespace BaiMuZe\library\helper;

use BaiMuZe\core\Controller;
use BaiMuZe\core\VirtualModel;
use BaiMuZe\library\AdminServer;
use think\App;
use think\Container;
use think\exception\HttpResponseException;
use think\facade\Db;
use think\Model;

/**
 * 控制器挂件
 * Class Helper
 */
abstract class Helper
{
    /**
     * 应用容器
     * @var App
     */
    public $app;

    /**
     * 控制器实例
     * @var Controller
     */
    public $class;

    /**
     * 当前请求方式
     * @var string
     */
    public $method;

    /**
     * 自定输出格式
     * @var string
     */
    public $output;

    /**
     * Helper constructor.
     * @param App $app
     * @param Controller $class
     */
    public function __construct(App $app, Controller $class)
    {
        $this->app = $app;
        $this->class = $class;
        // 计算指定输出格式
        $output = $app->request->request('output', 'default');
        $method = $app->request->method() ?: ($app->request->isCli() ? 'cli' : 'nil');
        $this->output = strtolower("{$method}.{$output}");
    }

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
     * 实例化数据库查询对象
     * @param Model|string $query
     * @author 白沐泽
     */
    public static function CreateQuery($query)
    {
        if (is_string($query)) {
            return static::CreateModel($query)->db();
        }
        if ($query instanceof Model) return $query->db();
        if ($query instanceof BaseHelper && !$query->getModel()) {
            $name = $query->getConfig('name') ?: '';
            if (is_string($name) && strlen($name) > 0) {
                $name = config("database.connections.{$name}") ? $name : '';
            }
            $query->model(static::buildModel($query->getName(), [], $name));
        }
        return $query;
    }

    /**
     * 实例化模型
     * @param  $name 模型名称
     * @param array $data 初始化
     * @param mixed $conn 指定连接
     * @return Model
     * @author 白沐泽
     */
    public static function CreateModel($name, $data = array(), string $conn = '')
    {
        if (strpos($name, '\\') !== false) {
            if (class_exists($name)) {
                $model = new $name($data);
                if ($model instanceof Model) return $model;
            }
            $name = basename(str_replace('\\', '/', $name));
        }
        return VirtualModel::mk($name, $data, $conn);
    }

    /**
     * 获取指定表的所欲字段信息
     * SELECT
     * ORDINAL_POSITION AS `序号`,
     * COLUMN_NAME AS `字段名称`,
     * COLUMN_COMENT AS `字段描述`,
     * COLUMN_TYPE AS `字段类型`,
     * IS_NULLABLE AS `允许为空`,
     * COLUMN_DEFAULT AS `默认值`
     * FROM
     * `COLUMNS`
     * WHERE
     * TABLE_SCHEMA = "yang_dev"
     * AND TABLE_NAME = "t_task"
     * ORDER BY
     * ORDINAL_POSITION;
     * @param string $table 数据表名称
     * @return bool
     */
    protected function selectField($table = '')
    {
        if (!(new AdminServer())->isLogin()) new HttpResponseException(json([
            'code' => 0, 'info' => BmzLang('busy')]));
        $sql = 'SHOW FULL COLUMNS FROM ' . $table;
        return Db::query($sql);
    }


    /**
     * 清空数据库，并且主键从新设为从1开始
     * @param string $table 数据表名称
     * @return int
     */
    protected function truncate($table)
    {
        if (!(new AdminServer())->isLogin()) new HttpResponseException(json([
            'code' => 0, 'info' => BmzLang('busy')]));
        $sql = trim('TRUNCATE TABLE ' . $table);
        return Db::query($sql);
    }
    /**
     *通过父级目录获取树形数组
     * @param  int      $id 顶级ID
     * @return string   $field 父级字段名称
     * @author 白沐泽
     */
    protected function tree($dbQuery,$columns = '*',$field='parent_id', string $template = '') {
        $query=self::CreateQuery($dbQuery);
        $list=$query->field($columns)->select()->toArray();
        $data=[];
        $items=[];
        if($list){
            foreach ($list as $row) {
                $items[$row['id']] = $row;
            }
            foreach ($items as $item) {
                isset($items[$item[$field]]) ? $items[$item[$field]]['children'][] = &$items[$item['id']] : $data[] = &$items[$item['id']];
            }
        }
        if (false !== $this->class->callback('_page_filter', $data,$data)) {
          $this->class->success('获取成功',$data);
        } else {
            return $data;
        }
    }
}