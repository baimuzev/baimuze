<?php


namespace BaiMuZe\library;

use BaiMuZe\utility\DocParser;
use BaiMuZe\utility\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * @title 应用节点
 * @author 白沐泽
 */
class NodeServer
{
    /**
     * 驼峰转下划线规则
     * @param string $name
     * @return string
     */
    public static function nameTolower(string $name): string
    {
        $dots = [];
        foreach (explode('.', strtr($name, '/', '.')) as $dot) {
            $dots[] = trim(preg_replace("/[A-Z]/", "_\\0", $dot), '_');
        }
        return strtolower(join('.', $dots));
    }

    /**
     * 获取所有控制器入口
     * @param boolean $force
     * @param array $exclude 排除的目录
     * @return array
     * @throws ReflectionException
     */
    public static function getMethods(bool $force = false, $exclude = ['view', 'api']): array
    {
        static $data = [];
        if (empty($force)) {
            if (count($data) > 0) return $data;
            $data = AppServer::$sapp->cache->get('SystemAuthNode', []);
            if (count($data) > 0) return $data;
        } else {
            $data = [];
        }
        /*! 排除内置方法，禁止访问内置方法 */
        $ignores = get_class_methods('BaiMuZe\core\Controller');
        $ignores = array_merge($ignores, config('base.admin.ignores'));
        /*! 扫描所有代码控制器节点，更新节点缓存 */
        foreach (static::scanDirectory(AppServer::$sapp->getBasePath(), array(), 'php', $exclude) as $file) {
            $name = substr($file, strlen(strtr(AppServer::$sapp->getRootPath(), '\\', '/')) - 1);
            if (preg_match("|^([\w/]+)/(\w+)/controller/(.+)\.php$|i", $name, $matches)) {
                [, $namespace, $appname, $classname] = $matches;
                $classfull = strtr("{$namespace}/{$appname}/controller/{$classname}", '/', '\\');
                if (class_exists($classfull) && ($class = new ReflectionClass($classfull))) {
                    $prefix = strtolower(strtr("{$appname}/" . static::nameTolower($classname), '\\', '/'));
//                    $data[$prefix] = static::_parseComment($class->getDocComment() ?: '', $classname);
                    $data[$appname][$prefix] = static::_parseComment($class->getDocComment() ?: '', $classname);
                    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                        if (in_array($metname = $method->getName(), $ignores)) continue;
                        $data[$appname][$prefix]['children'][strtolower("{$prefix}/{$metname}")] = static::_parseComment($method->getDocComment() ?: '', $metname);
//                          $data[strtolower("{$prefix}/{$metname}")] = static::_parseComment($method->getDocComment() ?: '', $metname);
                    }
                }
            }
        }
//        if (function_exists('admin_node_filter')) admin_node_filter($data);
        AppServer::$sapp->cache->set('SystemAuthNode', $data);
        return $data;
    }

    /**
     * 解析硬节点属性
     * @param string $comment 备注内容
     * @param string $default 默认标题
     * @param boolean $param 是否获取参数
     * @return array
     */
    private static function _parseComment(string $comment, string $default = '', bool $param = false): array
    {
        $text = strtr($comment, "\n", ' ');
        $title = preg_replace('/^\/\*\s*\*\s*\*\s*(.*?)\s*\*.*?$/', '$1', $text);
        $paramList = [];
        if (in_array(substr($title, 0, 5), ['@auth', '@menu', '@logi'])) $title = $default;
        $data = [
            'title' => $title ?: $default,
            'isauth' => intval(preg_match('/@auth\s*true/i', $text)),
//            'ismenu' => intval(preg_match('/@menu\s*true/i', $text)),
            'islogin' => intval(preg_match('/@login\s*true/i', $text)),
            'ispassword' => intval(preg_match('/@password\s*true/i', $text)),
        ];
        if ($param) $paramList = (new DocParser())->parse($comment);
        if ($param && isset($paramList['param'])) $data['params'] = $paramList['param'];
        return $data;
    }

    /**
     * 获取所有PHP文件列表
     * @param string $path 扫描目录
     * @param array $data 额外数据
     * @param ?string $ext 文件后缀
     * @param ?array $exclude 排除的文件夹名称
     * @return array
     */
    public static function scanDirectory(string $path, array $data = [], ?string $ext = 'php', ?array $exclude = ['view']): array
    {
        if (file_exists($path)) if (is_file($path)) {
            $data[] = strtr($path, '\\', '/');
        } elseif (is_dir($path)) foreach (scandir($path) as $item) if ($item[0] !== '.') {
            $real = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . $item;
            if (is_readable($real) && is_dir($real) && (Str::contains($real, $exclude))===false) {
                $data = static::scanDirectory($real, $data, $ext, $exclude);
            } elseif (is_file($real) && (is_null($ext) || pathinfo($real, 4) === $ext)) {
                $data[] = strtr($real, '\\', '/');
            }
        }
        return $data;
    }
}