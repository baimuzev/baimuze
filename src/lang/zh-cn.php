<?php

/**
 * 核心语言变量
 */
return [
    /**
     * 系统基础方面
     */
//	'system'=>[
    'illegal_operation' => '非法操作',
    'not_found_view' => '没有发现模板文件：%s',
    'not_found_controller' => '没有找到控制器：%s',
    'not_found_module' => '没有找到模块：%s',
    'not_found' => '没有找到页面：%s',
    'not_found_api' => '未找到接口%s的配置信息',
    'unknown_method' => '调用了未知的方法：%s',
//	],

    /**
     * 数据操作方面
     */
//	'db'=>[
    'unable_connect' => '无法链接数据库，请检查您的配置信息',
    'not_connection' => '没有可用的数据库连接',
    'not_found_config' => '没有发现数据配置项目',
    'null_value' => '未提供待查询的值',
    'empty_delete_error'=>'删除条件不可为空',
    'delete_success'=>'删除成功',
    'delete_error'=>'删除失败',
//	],

    /**
     * 表单
     */
//	'form'=>[
    'token' => '表单令牌失效，请返回页面刷新重试',
    'validator' => '表单验证失败',
    'submission' => '禁止外部提交数据',
    'busy' => '系统繁忙，请重试',
    'form_success'=>'表单提交成功',
//	],

    /**
     * 注册
     */
//	'register'=>[
    'closed' => '系统关闭了注册',
    'wrong_promo_url' => '错误的推广链接，系统将以普通方式进行注册',
    'button' => '立即注册',
    'not_found_module' => '没有找到模块：%s',
    'not_found' => '没有找到页面：%s',
//	],

    /**
     * 登录
     */
//	'login'=>[
    'no_account' => '账号不存在',
    'no_login' => '请登录后操作',
    'status_account' => '账号已被冻结',
    'Please try again after 1 day' => '失败次数过多,请一天后再试',
    'Password is incorrect'=>'密码错误',
    'Login success'=>'登录成功',
    'Username or password is incorrect'=>'账号或密码错误',
//	],

    /**
     * 文件上传
     */
//	'upload'=>[
    'beyond_maximum' => '文件大小超出限制',
    'wrong_suffix' => '非法的文件格式',
    'wrong_mime_type' => '非法的mimeType类型',
    'directory_failed' => '目录创建失败',
    'write_failed' => '目录没有写权限',
    'merge_failure' => '文件合并失败',
    'failure_temporary ' => '创建临时文件失败',
    'file_not_exist' => '创建临时文件失败',
    'failure_copy' => '文件移动复制失败',
    'beyond_maxsize' => '文件尺寸超出了限制',
    'encode_failure' => '文件加密失败',
    'File_driver'=> '{:name}文件驱动未配置',
    'file_error'=>'文件上传异常，文件过大或未上传',
    'wrong_extension'=>'文件后缀异常',
//	],

    /**
     * 业务流程相关
     */
//	'flow'=>[
    'findnotinitiator' => '未找到业务发起人信息',
    'submission' => '提交申请',
    'statustext' => '等待%s处理',
    'createfailure' => '流程创建失败',
    'findnotobject' => '未发现指定业务对象',
    'hasbeen' => '该业务已经处理，请不要重复处理',
    'notauthority' => '你没有权利处理该项业务',
    'notnode' => '该业务流程还未设置任何节点',
//	]
];