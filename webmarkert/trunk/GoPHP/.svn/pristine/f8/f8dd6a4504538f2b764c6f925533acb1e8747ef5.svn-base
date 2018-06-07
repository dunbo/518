<?php
class GoDes 
{
	private $key;
	//只有CBC模式下需要iv，其他模式下iv会被忽略 
	//private $iv = 'B500290096000A007600E3005700C4003800A40018008500';
	private $iv = '12345678';
	
	public function __construct($key) {
		$this->key = $key;
	}
	 
	/**
	* 加密
	*/
	
	public function encrypt($value) {
		//先确定加密模式，此处以ECB为例
		$td = mcrypt_module_open ( MCRYPT_3DES,'', MCRYPT_MODE_ECB,'');
		//$iv = pack ( 'H16', $this->iv );
		$value = $this->PaddingPKCS7 ( $value ); //填充
		//$key = pack ( 'H48', $this->key );
		mcrypt_generic_init ( $td, $this->key, $this->iv);
		$ret = mcrypt_generic ( $td, $value );
		mcrypt_generic_deinit ( $td );
		mcrypt_module_close ( $td );
		return $ret;
	}
	
	public function getCodedEncrypt($value) {
		return base64_encode($this->encrypt($value));
	}
	
	/**
	* 解密
	*/
	public function decrypt($value) {
		$td = mcrypt_module_open ( MCRYPT_3DES, '', MCRYPT_MODE_ECB, '' );
		//$iv = pack ( 'H16', $this->iv );
		//$key = pack ( 'H48', $this->key );
		mcrypt_generic_init ( $td, $this->key,$this->iv );
		$ret = trim ( mdecrypt_generic ( $td, $value ) );
		$ret = $this->UnPaddingPKCS7 ( $ret );
		mcrypt_generic_deinit ( $td );
		mcrypt_module_close ( $td );
		return $ret;
	}
	
	public function getDecodedDecrypt($value) {
		return $this->decrypt(base64_decode($value));
	}
	 
	private function PaddingPKCS7($data) {
		$padlen = 8 - strlen( $data ) % 8 ;
		for($i = 0; $i < $padlen; $i ++)
			$data .= chr( $padlen );
		return $data;
	}
	 
	private function UnPaddingPKCS7($data) {
		$padlen = ord (substr($data, (strlen( $data )-1), 1 ) );
		if ($padlen > 8 )
			return $data;
			
		for($i = -1*($padlen-strlen($data)); $i < strlen ( $data ); $i ++) {
			if (ord ( substr ( $data, $i, 1 ) ) != $padlen)return false;
		}
		
		return substr ( $data, 0, -1*($padlen-strlen ( $data ) ) );
	}

}
