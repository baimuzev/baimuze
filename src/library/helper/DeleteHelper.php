<?php


namespace BaiMuZe\library\helper;

use BaiMuZe\utility\Str;
use think\db\BaseQuery;
use think\Model;

/**
 * 快捷删除
 * @author 白沐泽
 */
class DeleteHelper extends Helper
{
    /**
     * 逻辑器初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param string $field 操作数据主键
     * @param mixed $where 额外更新条件
     * @return bool|null
     * @throws \think\db\exception\DbException
     */
    public function init($dbQuery, string $field = '', $where = []): ?bool
    {
        $query = static::CreateQuery($dbQuery);
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = $this->app->request->post($field);
        // 查询限制处理
        if (!empty($where)) $query->where($where);
        if (!isset($where[$field]) && is_string($value)) {
            $query->whereIn($field, Str::str2arr($value));
        }
        // 前置回调处理
        if (false === $this->class->callback('_delete_filter', $query, $where)) {
            return null;
        }
        //获取模型中时间配置
        $WriteTimestamp = 'datetime';
        $model = $query->getModel();
        if (method_exists($model, 'getWriteTimestamp')) {
            $WriteTimestamp = $model->getWriteTimestamp();
        }
        // 阻止危险操作
        if (!$query->getOptions('where')) {
            $this->class->error(BmzLang('empty_delete_error'));
        }
        // 组装执行数据
        if (method_exists($query, 'getTableFields')) {
            $fields = $query->getTableFields();
            if (in_array('deleted', $fields)) $data['deleted'] = 1;
            if (in_array('is_deleted', $fields)) $data['is_deleted'] = 1;
            if (isset($data['deleted']) || isset($data['is_deleted'])) {
                if (in_array('deleted_at', $fields)) $data['deleted_at'] = $WriteTimestamp === 'datetime' ? date('Y-m-d H:i:s') : time();
                if (in_array('deleted_time', $fields)) $data['deleted_time'] = $WriteTimestamp === 'datetime' ? date('Y-m-d H:i:s') : time();
            }
        }
        // 执行删除操作
        if ($result = (empty($data) ? $query->delete() : $query->update($data)) !== false) {
            // 模型自定义事件回调
            if ($model instanceof \BaiMuZe\core\Model) {
                $model->onAdminDelete(strval($value));
            }
        }

        // 结果回调处理
        if (false === $this->class->callback('_delete_result', $result)) {
            return $result;
        }

        // 回复返回结果
        if ($result !== false) {
            $this->class->success(BmzLang('delete_success'), '');
        } else {
            $this->class->error(BmzLang('delete_error'));
        }
    }
}