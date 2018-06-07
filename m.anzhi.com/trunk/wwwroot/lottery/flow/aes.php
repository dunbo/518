<?php
class AES {

    var $key = "1234567812345678";

    public function __set($key, $value){
        $this->$key = $value;
    }

    public function __get($key) {
        return $this->$key;
    }

    public function encrypt($input) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = $this->pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = $this->base64url_encode($data);
        return $data;
    }

    private function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function strToHex($string)   
    {   
        $hex="";   
        for   ($i=0;$i<strlen($string);$i++)   
        $hex.=dechex(ord($string[$i]));   
        $hex=strtoupper($hex);   
        return   $hex;   
    }   
    function hexToStr($hex)   
    {   
        $string="";   
        for   ($i=0;$i<strlen($hex)-1;$i+=2)   
        $string.=chr(hexdec($hex[$i].$hex[$i+1]));   
        return   $string;   
    }

    public function decrypt($sStr) {
        $decrypted= mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128,
            //$sKey,
            $this->key,
            //base64_decode($sStr),
            $this->base64url_decode($sStr),
            //$sStr,
            MCRYPT_MODE_ECB
        );
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }
    /**
     *url 安全的base64编码 sunlonglong
     */
    function base64url_encode($data) { 
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 
    /**
     *url 安全的base64解码 sunlonglong
     */
    function base64url_decode($data) { 
      return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    }   
}