<?php


namespace BaiMuZe\library\helper;

use BaiMuZe\utility\Str;
use think\db\BaseQuery;
use think\Model;

/**
 * 数据更新管理器
 * Class SaveHelper
 */
class SaveHelper extends Helper
{
    /**
     * 逻辑器初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param array $edata 表单扩展数据
     * @param string $field 数据对象主键
     * @param mixed $where 额外更新条件
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function init($dbQuery, array $edata = [], string $field = '', $where = []): bool
    {
        $query = static::CreateQuery($dbQuery);
        $field = $field ?: ($query->getPk() ?: 'id');
        $edata = $edata ?: $this->app->request->post();
        $value = $this->app->request->post($field);
        // 主键限制处理
        if (!isset($where[$field]) && !is_null($value)) {
            $query->whereIn($field, Str::str2arr(strval($value)));
            if (isset($edata)) unset($edata[$field]);
        }

        // 前置回调处理
        if (false === $this->class->callback('_save_filter', $query, $edata)) {
            return false;
        }

        // 检查原始数据
        $result = $query->master()->where($where)->update($edata) !== false;

        // 模型自定义事件回调
        $model = $query->getModel();
        if ($result && $model instanceof \BaiMuZe\core\Model) {
            $model->onAdminSave(strval($value));
        }

        // 结果回调处理
        if (false === $this->class->callback('_save_result', $result, $model)) {
            return $result;
        }

        // 回复前端结果
        if ($result !== false) {
            $this->class->success(BmzLang('form_success'), '');
        } else {
            $this->class->error(BmzLang('busy'));
        }
    }
}