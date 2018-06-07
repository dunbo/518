<?php
/*
** 与电信java相通的3des加密
*/

class Telecom3DES {
	var $crypt1;
	var $crypt2;
	var $crypt3;
	
	function __construct($key) {
		$this->key = $key;

		$key1 = substr($this->key, 0, 16);
		$key2 = substr($this->key, 16, 16);
		$key3 = substr($this->key, 32, 16);

		$this->crypt1 = new TelecomDES($key1);
		$this->crypt2 = new TelecomDES($key2);
		$this->crypt3 = new TelecomDES($key3);
	}
	
	function encrypt($str) {
		$mstr = $str;
		$mstr = $this->crypt1->encrypt($mstr);
		$mstr = $this->crypt2->encrypt($mstr);
		$mstr = $this->crypt3->encrypt($mstr);

		$mstr = unpack('H*', $mstr);
		$mstr = strtoupper($mstr[1]);
		return $mstr;
	}
	
	function decrypt($str) {
		$mstr = pack('H*', $str);
		$mstr = $this->crypt3->decrypt($mstr);
		$mstr = $this->crypt2->decrypt($mstr);
		$mstr = $this->crypt1->decrypt($mstr);

		return $mstr;
	}
}

class TelecomDES
{
    var $key;
    var $iv; //偏移量

    function __construct($key, $iv=0)
    {
		// key需要转换一下
		$key = $this->hex_key($key);
		$key = pack('H*', $key);
	
        $this->key = $key;
        if($iv == 0)
        {
            $this->iv = $key;
        }
        else 
        {
            $this->iv = $iv;
        }
    }
	
	// 将key做特殊处理，因为电信的密钥是任意的字符，但实际上只有十六进制字符才有效，所以将非十六进制的字符都换成0
	function hex_key($key) {
		for ($i = 0; $i < strlen($key); $i++) {
			if ($key[$i] >= '0' && $key[$i] <= '9') {
			} else if ($key[$i] >= 'A' && $key[$i] <= 'F') {
			} else {
				$key[$i] = '0';
			}
		}
		return $key;
	}
	
    //加密
    function encrypt($str)
    {       
        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );
        $str = $this->pkcs5Pad ( $str, $size );
        
        $data=mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
        //$data = bin2hex($data);
		//$data = base64_encode($data);
        return $data;
    }
    
    //解密
    function decrypt($str)
    {
        //$str = base64_decode ($str);
        //$strBin = $this->hex2bin( strtolower($str));
        $str = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv );
        $str = $this->pkcs5Unpad( $str );
        return $str;
    }

    function hex2bin($hexData)
    {
        $binData = "";
        for($i = 0; $i < strlen ( $hexData ); $i += 2)
        {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }

    function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    function pkcs5Unpad($text)
    {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }
}

