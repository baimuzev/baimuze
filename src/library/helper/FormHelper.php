<?php


namespace BaiMuZe\library\helper;

use BaiMuZe\core\Application;
use think\db\BaseQuery;
use think\Model;

/**
 * 快捷表单
 * @author 白沐泽
 */
class FormHelper extends Helper
{
    /**
     * 逻辑器初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param string $template 视图模板名称
     * @param string $field 指定数据主键
     * @param mixed $where 限定更新条件
     * @param array $edata 表单扩展数据
     * @return void|array|boolean
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function init($dbQuery, string $template = '', string $field = '', $where = [], array $edata = [])
    {
        $query = static::CreateQuery($dbQuery);
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = $edata[$field] ?? input($field);
        if ($this->app->request->isGet()) {
            if ($value !== null) {
                $exist = $query->where([$field => $value])->where($where)->find();
                if ($exist instanceof Model) $exist = $exist->toArray();
                $edata = array_merge($edata, $exist ?: []);
            }
            if (false !== $this->class->callback('_form_filter', $edata)) {
                $this->class->fetch($template, ['vo' => $edata]);
            } else {
                return $edata;
            }
        }
        if ($this->app->request->isPost()) {
            $edata = array_merge($this->app->request->post(), $edata);
            if (false !== $this->class->callback('_form_filter', $edata, $where)) {
                $result = Application::save($query, $edata, $field, $where) !== false;
                if (false !== $this->class->callback('_form_result', $result, $edata)) {
                    if ($result !== false) {
                        $this->class->success(BmzLang('form_success'));
                    } else {
                        $this->class->error(BmzLang('busy'));
                    }
                }
                return $result;
            }
        }
    }
}