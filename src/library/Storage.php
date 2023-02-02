<?php


namespace BaiMuZe\library;

use think\App;
use think\Container;

/**
 * 文件驱动引擎映射
 * Class Storage
 * @method array info($name, $safe = false, $attname = null) static 文件存储信息
 * @method array set($name, $file, $safe = false, $attname = null) static 储存文件
 * @method string url($name, $safe = false, $attname = null) static 获取文件链接
 * @method string get($name, $safe = false) static 读取文件内容
 * @method string path($name, $safe = false) static 文件存储路径
 * @method boolean del($name, $safe = false) static 删除存储文件
 * @method boolean has($name, $safe = false) static 检查是否存在
 * @method string upload() static 获取上传地址
 * @author 白沐泽
 */
abstract class Storage
{
    /**
     * 应用实例
     * @var App
     */
    protected $app;
    /**
     * 存储类型
     * @var string
     */
    protected $type;
    /**
     * 链接前缀
     * @var string
     */
    protected $prefix;
    /**
     * 链接类型
     * @var string
     */
    protected $link;
    /**
     * Storage constructor.
     * @param App $app
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __construct(App $app)
    {
        $this->app = $app;
//        $this->link = sysconf('storage.link_type');
        $this->initialize();
    }

    /**
     * 存储驱动初始化
     */
    abstract protected function initialize();

    /**
     * 设置文件驱动名称
     * @param null|string $name 驱动名称
     */

    public static function instance(?string $name = null)
    {
        $class = ucfirst(strtolower($name ?: syconfig('system', 'storage', 'Local')));
        if (class_exists($object = "BaiMuZe\\library\\storage\\{$class}Storage")) {
            return Container::getInstance()->make($object);
        } else {
            throw new Exception(BmzLang('File_driver', ['name' => $class]));
        }
    }

    /**
     * 获取文件基础名称
     * @param string $name 文件名称
     * @return string
     */
    protected function delSuffix(string $name): string
    {
        if (strpos($name, '?') !== false) {
            return strstr($name, '?', true);
        }
        if (stripos($name, '!') !== false) {
            return strstr($name, '!', true);
        }
        return $name;
    }
    /**
     * 获取下载链接后缀
     * @param null|string $attname 下载名称
     * @param null|string $filename 文件名称
     * @return string
     */
    protected function getSuffix(?string $attname = null, ?string $filename = null): string
    {
        $suffix = '';
        if (is_string($filename) && stripos($this->link, 'compress') !== false) {
            $compress = [
                'LocalStorage'  => '',
                'QiniuStorage'  => '?imageslim',
                'UpyunStorage'  => '!/format/webp',
                'TxcosStorage'  => '?imageMogr2/format/webp',
                'AliossStorage' => '?x-oss-process=image/format,webp',
            ];
            $class = basename(get_class($this));
            $extens = strtolower(pathinfo($this->delSuffix($filename), PATHINFO_EXTENSION));
            $suffix = in_array($extens, ['png', 'jpg', 'jpeg']) ? ($compress[$class] ?? '') : '';
        }
        if (is_string($attname) && strlen($attname) > 0 && stripos($this->link, 'full') !== false) {
            if ($this->type === 'upyun') {
                $suffix .= ($suffix ? '&' : '?') . '_upd=' . urlencode($attname);
            } else {
                $suffix .= ($suffix ? '&' : '?') . 'attname=' . urlencode($attname);
            }
        }
        return $suffix;
    }
}