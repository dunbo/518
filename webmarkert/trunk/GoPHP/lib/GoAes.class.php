<?php
class GoAes {
    /**
     *  加密
     *
     * @param $usData 需要加密的明文
     * @param $from   渠道标示
     * @param $secret secret
     * @param $iv     iv
     *
     * @return string
     */
    public static function encode($usData, $from, $secret, $iv) {
        if (!self::_validParams($from, $secret, $iv)) {
            return false;
        }
        list($key, $iv) = self::_init($from, $secret, $iv);
        $usData         = self::pad($usData);
        $enmcrypt       = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($enmcrypt, $key, $iv);
        // 加密
        $data = mcrypt_generic($enmcrypt, $usData);
        $data = urlencode(base64_encode($data));
        mcrypt_generic_deinit($enmcrypt);
        mcrypt_module_close($enmcrypt);
        return $data;
    }
    /**
     *  解密
     *
     * @param $usData 需要解密的密文
     * @param $from   渠道标示
     * @param $secret secret
     * @param $iv     iv
     *
     * @return string
     */
    public static function decode($usData, $from, $secret, $iv) {
        if (empty($usData) || !self::_validParams($from, $secret, $iv)) {
            return false;
        }
        list($key, $iv) = self::_init($from, $secret, $iv);
        $usData         = base64_decode(urldecode($usData));
        $enmcrypt       = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($enmcrypt, $key, $iv);
        $data = mdecrypt_generic($enmcrypt, $usData);
        mcrypt_generic_deinit($enmcrypt);
        mcrypt_module_close($enmcrypt);
        return self::unpad($data);
    }
    /**
     *  初始化加密参数
     *
     * @param $from
     * @param $secret
     * @param $iv
     *
     * @return 
     */
    private static function _init($from, $secret, $iv) {
        // 获取加密key
        $key = strtoupper(substr(md5($from . $secret), -16));
        // 如果iv长度不足16字节，进行补足
        $iv  = str_pad(substr($iv, 0, 16), 16, chr(0));
        return array($key, $iv);
    }
    /**
     *  验参
     *
     * @param $from
     * @param $secret
     * @param $iv
     *
     * @return 
     */
    private static function _validParams($from, $secret, $iv) {
        if (empty($from) || empty($secret) || empty($iv)) {
            return false;
        }
        return true;
    }
    /**
     *  pkcs 填充
     *
     * @param $text string
     *
     * @return string
     */
    public static function pad($text) {
        $blocksize = 16;
        $pad       = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    /**
     *  解pkcs
     *
     * @param $text string
     *
     * @return string
     */
    public static function unpad($text) {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }
}