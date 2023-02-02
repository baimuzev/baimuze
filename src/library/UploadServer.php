<?php


namespace BaiMuZe\library;

use BaiMuZe\library\storage\LocalStorage;
use BaiMuZe\model\Attachment;
use BaiMuZe\utility\Arr;
use BaiMuZe\utility\Str;

/**
 * 文件上传服务
 * @author 白沐泽
 */
class UploadServer
{
    private $file;//当前文件
    private $request; //当前请求
    private $storage;//驱动
    private $mimeType;//上传文件类型
    private $extension;//文件后缀
    private $size;//文件大小
    private $originalName;//文件原始名字
    private $error;//错误信息
    private $chunk_status;//是否开启分片上传
    private $path;//上传路径
    private $hash;//hash值
    private $power;//权限
    private $maxheight;//图片最大宽度
    private $maxwidth;//图片最大宽度
    /**
     * 上传文件所属组image|doc|video|music|other
     *
     * @var string
     */
    private $type;

    /**
     * 初始化
     * @param $file 文件
     * @param $storage 驱动
     * @param $chunk 是否开启分片
     * @author 白沐泽
     */
    public function __construct(?array $file = array(), $storage = 'local', bool $chunk = false)
    {
        $this->request = app()->request;
        if (!empty($file)) {
            $this->file = $file;
        } else {
            $this->file = $file = $this->request->file(syconfig('storage', 'name'));
        }
        $this->storage = $this->request->post('storage', $storage);//若前段指定驱动,则以前端为主
        $this->chunk_status = $this->request->post('chunks', 0) ? true : $chunk;//若前段指定是否分片,则以前端为主
        $this->mimeType = $file->getOriginalMime();
        $this->size = $file->getSize();
        $this->originalName = $file->getOriginalName();
        $this->extension = $file->getOriginalExtension();
        $this->path = syconfig('storage', 'path');
        $this->hash = $this->request->post('hash') ?: $this->file->md5();
        $this->power = $this->request->post('power', 'public');
        $this->hash = md5($this->hash . '|' . $this->power);
        $this->maxwidth = $this->request->post('maxwidth');
        $this->maxheight = $this->request->post('maxheight');
    }

    /**
     * 文件校验
     * @author 白沐泽
     */
    public function fileCheck()
    {
        if (!$this->file->isValid()) {
            return $this->info(BmzLang('file_error'));
        }
        //检查文件后缀是否被恶意修改
        if (strtolower(pathinfo(parse_url($this->originalName, PHP_URL_PATH), PATHINFO_EXTENSION)) !== $this->extension) {
            return $this->info(BmzLang('wrong_extension'));
        }
        // 屏蔽禁止上传指定后缀的文件
        if (!in_array($this->extension, Str::str2arr(syconfig('storage', 'AllowExts')))) {
            return $this->info(BmzLang('wrong_suffix'));
        }
        //安全保护
        if (in_array($this->extension, ['sh', 'asp', 'bat', 'cmd', 'exe', 'php'])) {
            return $this->info(BmzLang('wrong_suffix'));
        }
        // 允许类型
        $allows = JsonToArray(syconfig('storage', 'mime'));

        if (Arr::has($allows, strtolower($this->extension))) {
            $mime = Arr::get($allows, strtolower($this->extension));
            if ($this->mimeType != 'application/octet-stream' && !in_array($this->mimeType, (array)$mime['mime'])) {
                $this->info(BmzLang('wrong_mime_type'));
            }
            $this->type = $mime['group'];
        } else {
            return $this->info(BmzLang('wrong_suffix'));
        }
        //转换为字节值
        $maxsize = JsonToArray(syconfig('storage', 'limit'))[$this->type]['maxsize'];
        //检查文件大小是否超出限制
        if ($this->size > $maxsize) {
            return $this->info(BmzLang('beyond_maximum'));
        }
        if ($this->type == 'image') {
            $allow = JsonToArray(syconfig('storage', 'limit'))[$this->type]['allow'];
            if (($this->maxwidth > 0 || $this->maxheight > 0) && in_array(strtolower($this->extension), $allow)) {
                $info = getimagesize($this->getPathname());
                if ($this->maxwidth > 0 && $info[0] > $this->maxwidth) {
                    return $this->info(BmzLang('beyond_maxsize'));
                }
                if ($this->maxheight > 0 && $info[1] > $this->maxheight) {
                    return $this->info(BmzLang('beyond_maxsize'));
                }
            }
        }
        return $this->info('success', array(), 1);
    }

    /**
     * 文件统一上传
     * @author 白沐泽
     */
    public function file()
    {
        if (!$this->fileCheck()) return false;
        $info = Storage::instance(strtolower($this->storage))->info($this->hash, $this->power);
        $data = ([
            'hash' => $this->hash,
            'size' => $this->size,
            'name' => $this->originalName,
            'power' => $this->power,
            'mime' => $this->mimeType,
            'extension' => $this->extension,
            'file' => $this->file,
            'groups' => $this->type
        ]);
        $file = Attachment::mk()->where('hash', $this->hash)->find();
        if (is_array($info) && isset($info['url']) && isset($info['key']) && $file) {
            $extr = ['id' => $file->id ?? 0, 'url' => $info['url'], 'key' => $info['key']];
            return $this->info('文件已上传', array_merge($data, $extr));
        } elseif ($file) {
            $file->save([
                'hash' => $this->hash,
                'size' => $this->size,
                'name' => $this->originalName,
                'power' => $this->power,
                'mime' => $this->mimeType,
                'extension' => $this->extension
            ]);
            $data['url']=LocalStorage::instance()->url($data['hash'], $data['power']);
            return $this->info('SUCCESS', $data, 1);
        } elseif ('local' == $this->storage) {
            $path = LocalStorage::instance()->upload($data);
            $path = (str_replace('/', '\\', $path));
            if ($path) {
                $data['path'] = str_replace(public_path(), '', $path);
                $data['storage']=$this->storage;
            }
            $url = LocalStorage::instance()->url($data['hash'], $data['power']);
            $data['url'] = $url;
        }
        Attachment::mk()->save($data);
        return $this->info('SUCCESS', $data, 1);
    }

    /**
     * 文件是否已上传
     * @author 白沐泽
     */
    public function haveUpload()
    {

    }

//    /**
//     * 单一文件上传
//     * @author 白沐泽
//     */
//    protected function upload()
//    {
//
//    }
//
//    /**
//     *分片上传
//     * @author 白沐泽
//     */
//    protected function uploadChunk()
//    {
//
//    }

    /**
     * 设置错误
     * @param $message 错误信息
     * @param $data 数据
     * @param $staus 状态码
     * @author 白沐泽
     */
    public function info(?string $message = '', array $data = array(), int $status = 0)
    {
        $path = !isset($data['path']) ? false : (str_replace('/', '\\', $data['path']));
        if ($path) {
            $path = str_replace(public_path(), '', $path);
        }
        return $this->error = [
            'status' => $status,                                                           //状态 1=成功 0=失败
            'info' => $message,                                                         //结果描述
            'path' => $path,//地址
            'original' => $this->originalName,                                             //原始名称
            'mime_type' => $this->mimeType,                                                //mime类型
            'size' => $this->size,                                                         //文件大小
            'name' => $this->hash,                                                         //生成的文件名称
            'type' => $this->mimeType,                                                         //类型
            'ext' => $this->extension,                                                           //扩展名
//            'encryption'=>$this->encryption,                                               //加密
//            'thumbnail'=>$this->thumbnail?$this->thumbnail:'',                                                               //缩略图
            'hash' => isset($data['hash']) ? $data['hash'] : '',
            'url' => isset($data['url']) ? $data['url'] : ''
        ];
    }

    /**
     * 读取错误
     * @author 白沐泽
     */
    public function getInfo()
    {
        return $this->error;
    }
}