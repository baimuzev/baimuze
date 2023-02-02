<?php


namespace BaiMuZe\utility;
/**
 * 文件处理助手
 * @author 白沐泽
 */
class File
{

    /**
     * 文件句柄集合
     *
     * @var array
     */
    protected static $fp = [];


    /**
     * 判断文件是否存在
     *
     * @param string $file
     * @return bool
     */
    public static function exists($file)
    {
        return file_exists($file);
    }

    /**
     * 获取一个文件内容
     *
     * @param string $path
     * @return string
     */
    public static function get($path)
    {
        if (static::exists($path)) {
            return file_get_contents($path);
        } else {
            return false;
        }
    }

    /**
     * 写入文件到内容
     *
     * @param string $path
     * @param string $contents
     * @return int
     */
    public static function put($path, $contents)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return file_put_contents($path, $contents);
    }

    /**
     * 追加内容到文件
     *
     * @param string $path
     * @param string $data
     * @return int
     */
    public static function append($path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * 通过给定的路径删除文件
     *
     * @param string|array $paths
     * @return bool
     */
    public static function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $success = true;
        foreach ($paths as $path) {
            if (!unlink($path)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * 移动文件到新位置，并返回新的文件路径地址
     *
     * @param string $path
     * @param string $target
     * @param int $type 0=不改变文件名称，直接移动到目标文件夹
     * @return bool
     */
    public static function move($path, $target, $type = 0)
    {
        //$dir=dirname($target);
        //echo $dir;
        //exit;
        if ($type == 0) {
            $name = basename($path);
        } else {
            $name = basename($target);
            $target = dirname($target);
        }
        if (!is_dir($target) && false === mkdir($target, 0777, true)) {
            return false;
        }
        $target .= '//' . $name;
        $target = str_replace('//', '/', $target);
        if (rename($path, $target)) {
            return $name;
        }
        return false;

    }

    /**
     * 获取文件扩展名
     *
     * @param string $path
     * @return string
     */
    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 获取文件类型
     *
     * @param string $path
     * @return string
     */
    public static function type($path)
    {
        return filetype($path);
    }

    /**
     * 获取文件大小
     *
     * @param string $path
     * @return int
     */
    public static function size($path)
    {
        return filesize($path);
    }

    /**
     * 获取文件最后修改时间
     *
     * @param string $path
     * @return int
     */
    public static function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * 获取文件创建时间
     *
     * @param string $path
     * @return int
     */
    public static function foundfied($path)
    {
        return filectime($path);
    }

    /**
     * 判断是否为目录
     *
     * @param string $directory
     * @return bool
     */
    public static function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * 判断是否为文件
     *
     * @param string $file
     * @return bool
     */
    public static function isFile($file)
    {
        return is_file($file);
    }

    /**
     * 创建一个目录
     *
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */
    public static function makeDirectory($path, $mode = 0755)
    {
        return is_dir($path) or self::makeDirectory(dirname($path), $mode) and mkdir($path, $mode);
    }

    /**
     * 遍历获取目录下的指定类型的文件
     *
     * @param string $path 文件夹地址
     * @param string $files 要获取的文件后缀名
     * @return array
     */
    public static function getFiles($path, $allowFiles = '', &$files = array())
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . iconv('gb2312', 'utf-8', $file);
                $dir = $path . $file;
                if (is_dir($dir)) {
                    static::getfiles($path2, $allowFiles, $files);
                } else {

                    if (empty($allowFiles)) {
                        $files[] = array(
                            'url' => substr($path2, strlen(path())),
                            'name' => iconv('gb2312', 'utf-8', $file),
                            'mtime' => filemtime($dir),
                            'ext' => pathinfo($path2, PATHINFO_EXTENSION),
                            'size' => filesize($dir),
                        );
                    } else {
                        if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                            $files[] = array(
                                'url' => substr($path2, strlen(path())),
                                'name' => iconv('gb2312', 'utf-8', $file),
                                'mtime' => filemtime($dir),
                                'ext' => pathinfo($path2, PATHINFO_EXTENSION),
                                'size' => filesize($dir),
                            );
                        }
                    }

                }
            }
        }
        return $files;
    }

    /**
     * 获取文件夹大小
     *
     * @param string $path 文件夹地址
     * @param string $allowFiles 要获取的文件后缀名
     * @return array
     */
    public static function getFilesSize($path, $allowFiles = '*', &$size = 0)
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    static::getfiles($path2, $allowFiles, $size);
                } else {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                        $size += filesize($path2);
                    }
                }
            }
        }
        return $size;
    }

    /**
     * 遍历获取目录下的文件夹
     *
     * @param string $path 文件夹地址
     * @param string $files
     * @return array
     */
    public static function getFolder($path, &$folder = array())
    {
        if (!is_dir($path)) return null;
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . $file)) {
                    $folder[] = $file;
                    static::getFolder($path . $file);
                }
            }
        }
        return $folder;
    }

    /**
     * 组建一个虚拟的上传文件类型
     *
     * @param array $data
     */
    public static function fixArray($url)
    {
        return ['file' => [
            'error' => 0,
            'name' => $url,
            'type' => 'image',
            'tmp_name' => $url,
            'size' => '',
        ]];
    }


    /**
     * 删除文件夹
     *
     * @param string $directory
     * @return bool
     */
    public static function rmDirectory($directory)
    {
        return rmdir($directory);
    }

    /**
     * 清理指定的文件夹
     *
     * @param string $dir 要清理的目录
     * @param int $type 0=清理所有 1=只清理文件
     */
    public static function clearDirectory($dir, $type = 0)
    {
        //先删除目录下的文件
        $dh = opendir($dir);
        while (false !== $file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
                $fullpath = $dir . '/' . $file;
                if (is_dir($fullpath)) {
                    static::ClearDirectory($fullpath, $type);
                    if ($type == 0) {
                        if (!rmdir($fullpath)) {
                            closedir($dh);
                            return false;
                        }
                    }
                } else {
                    if (!unlink($fullpath)) {
                        closedir($dh);
                        return false;
                    }
                }
            }
        }
        closedir($dh);
        return true;
    }


    /**
     * 判断路径是否可读
     *
     * @param string $path 路径
     */
    public static function isPathRead($path)
    {
        $result = intval(is_readable($path));
        if ($result) {
            return $result;
        }
        $mode = static::getChmod($path);
        if ($mode &&
            strlen($mode) == 18 &&
            substr($mode, -9, 1) == 'r') {// -rwx rwx rwx(0777)
            return true;
        }
        return false;
    }

    /**
     * 判断路径是否可写
     *
     * @param string $path 路径
     */
    public static function isPathWrite($path)
    {
        $result = intval(is_writeable($path));
        if ($result) {
            return $result;
        }
        $mode = static::getChmod($path);
        if ($mode && strlen($mode) == 18 && substr($mode, -8, 1) == 'w') {
            return true;
        }
        return false;
    }

    /**
     * 获取文件(夹)权限
     *
     * @param string $file 文件(夹)路径
     * @return string
     */
    public static function getChmod($file)
    {
        $mode = fileperms($file);
        $themode = ' ' . decoct($mode);
        $themode = substr($themode, -4);
        $owner = [];
        $group = [];
        $world = [];

        if ($mode & 0x1000) {
            $type = 'p'; // FIFO pipe
        } elseif ($mode & 0x2000) {
            $type = 'c'; // Character special
        } elseif ($mode & 0x4000) {
            $type = 'd'; // Directory
        } elseif ($mode & 0x6000) {
            $type = 'b'; // Block special
        } elseif ($mode & 0x8000) {
            $type = '-'; // Regular
        } elseif ($mode & 0xA000) {
            $type = 'l'; // Symbolic Link
        } elseif ($mode & 0xC000) {
            $type = 's'; // Socket
        } else {
            $type = 'u'; // UNKNOWN
        }
        // Determine les permissions par Groupe
        $owner['r'] = ($mode & 00400) ? 'r' : '-';
        $owner['w'] = ($mode & 00200) ? 'w' : '-';
        $owner['x'] = ($mode & 00100) ? 'x' : '-';
        $group['r'] = ($mode & 00040) ? 'r' : '-';
        $group['w'] = ($mode & 00020) ? 'w' : '-';
        $group['e'] = ($mode & 00010) ? 'x' : '-';
        $world['r'] = ($mode & 00004) ? 'r' : '-';
        $world['w'] = ($mode & 00002) ? 'w' : '-';
        $world['e'] = ($mode & 00001) ? 'x' : '-';

        // Adjuste pour SUID, SGID et sticky bit
        if ($mode & 0x800) $owner['e'] = ($owner['e'] == 'x') ? 's' : 'S';
        if ($mode & 0x400) $group['e'] = ($group['e'] == 'x') ? 's' : 'S';
        if ($mode & 0x200) $world['e'] = ($world['e'] == 'x') ? 't' : 'T';

        $mode = $type . $owner['r'] . $owner['w'] . $owner['x'] . ' ' .
            $group['r'] . $group['w'] . $group['e'] . ' ' .
            $world['r'] . $world['w'] . $world['e'];
        return $mode . '(' . $themode . ')';
    }


    /**
     * 修改文件、文件夹权限
     *
     * @param string $path 文件(夹)目录
     * @return string
     */
    public static function setChmod($path, $mode = 0777)
    {
        if (!file_exists($path)) {
            return false;
        }
        //如果是文件，则直接设置为文件权限
        if (is_file($path)) {
            return chmod($path, $mode);
        }
        if (!$dir = opendir($path)) {
            return false;
        }
        while (($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $full_path = $path . '/' . $file;
                static::setChmod($full_path, $mode);
                chmod($full_path, $mode);
            }
        }
        closedir($dir);
        return chmod($path, $mode);
    }

    /**
     * 加锁
     *
     * @param string $name 进程名称
     * @param int $model 模式
     * 阻塞模式(LOCK_EX):0
     * 多人访问时，当有第二个用户请求，会等待第一个用户请求释放锁，然后在获得文件锁后，程序才会继续运行下去
     * 非阻塞模式(LOCK_EX | LOCK_NB):1
     * 多人访问时，在取得锁的用户释放锁之前，其他的访问用户会返回系统繁忙
     * 非阻塞模式在windows下是无效的，必须使用linux、mac系统
     */
    public static function lock($name, $model = 0)
    {
        $path = path('storage') . '/temp/';
        //如果无法开启ea内存锁，则开启文件锁
        if (function_exists('eaccelerator_lock')) {
            return eaccelerator_lock($name);
        } else {
            //配置目录权限可写
            $fp = fopen($path . md5($name . config('app.safe.secretkey')) . '.lock', 'w+');
            if ($fp === false) {
                return false;
            }
            static::$fp[$name] = $fp;
            return $model == 0 ? flock($fp, LOCK_EX) : flock($fp, LOCK_EX | LOCK_NB);
        }
    }

    /**
     * 解锁
     *
     * @param string $name 进程名称
     */
    public static function unlock($name)
    {
        $path = path('storage') . '/temp/' . md5($name . config('app.safe.secretkey')) . '.lock';
        if (function_exists('eaccelerator_unlock')) {
            return eaccelerator_unlock($name);
        } else {
            if (isset(static::$fp[$name])) {
                $fp = static::$fp[$name];
                if ($fp !== false) {
                    flock($fp, LOCK_UN);
                    clearstatcache();
                }
                //进行关闭
                fclose($fp);
                //如果存在锁文件删除
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    /**
     * 加密文件
     *
     * @param string $file 要加密的文件
     * @param string $salt 加密盐值 1=默认
     * @return
     */
    public static function encode($file, $salt = 1)
    {
        $time = microtime(1);
        if ($salt === 1) {
            $salt = config('upload.salt');
        }
        try {
            //打开当前文件
            $fh = fopen($file, 'r');
            //打开文件为流
            $stream = fread($fh, filesize($file));
            $target = str_replace(strrchr($file, '.'), '', $file);
            file_put_contents($target, encode($salt . '@' . $stream, 0, $salt));
            //关闭句柄
            fclose($fh);
            unlink($file);
            return $target;
        } catch (Exception $e) {
            if (config('debug')) {
                throw $e;
            } else {
                unlink($file);
                return false;
            }
        }
    }

    /**
     * 解密文件
     *
     * @param string $file 要加密的文件
     * @param string $salt 加密盐值
     * @return
     */
    public static function decode($file, $salt = '')
    {
        $time = microtime(1);
        if (empty($salt)) {
            $salt = config('upload.salt');
        }
        $output = file_get_contents($file);
        $output = decode($output, $salt);
        return substr($output, strlen($salt . '@'));
    }

    /**
     * 获取文件base64
     *
     * @param string $content 文件内容
     * @param string $mime 文件mime类型
     * @return
     */
    public static function getbase64($content, $mime)
    {
        return 'data://' . $mime . ';base64,' . base64_encode($content);
    }

    /**
     * 清空并删除文件夹
     * @param $dirName
     * @param $oldtime 小于的时间
     * @param $newtime 大于的时间
     */
    public static function remove_dir($dirName, $oldtime = null, $newtime = null, $notme = false)
    {
        //先判断文件是否存在
        if (file_exists($dirName)) {
            if (!is_dir($dirName)) {//如果传入的参数不是目录，则为文件，应将其删除
                $mtime = filectime($dirName);
                if ($oldtime === null && $newtime === null) {
                    @unlink($dirName);
                } else {
                    if (isset($oldtime)) {
                        if ($mtime < $oldtime) {
                            @unlink($dirName);
                        }
                    }
                    if (isset($newtime)) {
                        if ($mtime > $newtime) {
                            @unlink($dirName);
                        }
                    }
                }
                return false;
            }
            //如果传入的参数是目录
            $handle = @opendir($dirName);
            while (($file = @readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $dir = $dirName . '/' . $file; //当前文件$dir为文件目录+文件
                    self::remove_dir($dir, $oldtime, $newtime);
                }
            }
            closedir($handle);
            if ($notme) {
                return true;
            } else {
                @rmdir($dirName);
                return true;
            }
        } else {
            return true;
        }
    }
}