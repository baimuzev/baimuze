<?php


namespace BaiMuZe\utility;

use BaiMuZe\library\Exception;

/**
 * 加密处理
 */
class Security
{
    /**
     * 加密密钥
     *
     * var string
     */
    protected $key;

    /**
     * 加密的初始话矢量
     *
     * var string
     */
    protected $iv;

    /**
     * 加密算法
     *
     * var string
     */
    protected $cipher;


    /**
     * 构建函数
     *
     * @param string $type
     * @return void
     */
    public function __construct()
    {
        $config = syconfig('app');
        $this->key = $config['key'];
        $this->cipher = $config['cipher'] ?: 'AES-128-CBC';
        $iv = empty($config['iv']) ? $this->key : $config['iv'];
        $this->iv = substr(md5($iv), -16);
    }

    /**
     * 加密给定的值
     *
     * @param string $value 待解密的值
     * @param int $expiry 过期时间
     * @param int $key 加密码
     * @param int $base 是否使用base64_decode
     * @return string
     */
    public function encrypt($value, $expiry = 0, $key = '', $base = 1)
    {
        if (empty($value)) {
            return '';
        }
        if (empty($key)) {
            $key = $this->key;
        }
        $value = openssl_encrypt(sprintf('%010d', $expiry ? $expiry + time() : 0) . serialize($value), $this->cipher, $key, 0, $this->iv);
        if ($value === false) {
            throw new Exception('Could not encrypt the data.');
        }
        return $base == 1 ? base64_encode($value) : $value;
    }

    /**
     * 解密给定的值
     *
     * @param string $value 待解密的值
     * @param int $key 加密码
     * @param int $base 是否使用base64_decode
     * @return string
     */
    public function decrypt($value, $key = '', $base = 1)
    {
        if (empty($value)) {
            return '';
        }
        if (empty($key)) {
            $key = $this->key;
        }
        $value = $base == 1 ? base64_decode($value) : $value;
        $result = openssl_decrypt($value, $this->cipher, $key, 0, $this->iv);
        if (substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) {
            return unserialize(substr($result, 10));
        }
        return false;

    }
}