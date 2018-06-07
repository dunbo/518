<?php
define('SESSION_PSK', 'psk123');
function get_session_spec()
{
    $spec = array(
        'USER_IMEI' => 'STRING',
        'TIME_STAMP' => 'INT:32',
        'MODEL_CID' => 'INT:16',
        'VERSION_CODE' => 'INT:16',
        'MODEL_OID' => 'INT:16',
        'MODEL_DID' => 'INT:32',
        'ABI' => 'BIT:4',
        'FIRMWARE' => 'BIT:5',
        'USER_ID' => 'INT:32',
        'has_channel_soft' => 'BIT:1',
        'channel:has_rule' => 'BIT:1',
        'device:has_rule' => 'BIT:1',
    );
	return $spec;
}


function int2bin($val, $align) {
    $str = "";
    $n = 0;
    do {
        $chr = $val % 256;
        $str .= chr($chr);
        $val = $val >> 8;
        $n += 1;
    } while ($val != 0);
    for (; $n < $align; $n++)
        $str .= "\0";
    return $str;
}

function rc4_crypt($key, $str) {
    $td = mcrypt_module_open('arcfour', '' , 'stream', '');
    mcrypt_generic_init($td, $key, null);
    $encrypted = mcrypt_generic($td, $str);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $encrypted;
}

function le_read_n($str, $offset, $n) {
    $val = 0;
    for ($i = $n - 1; $i >= 0; $i--) {
        $val <<= 8;
        $x = ord($str[$offset + $i]);
        $val |= $x;
    }
    return $val;
}

function le_write_n(&$to, $toffset, $from, $foffset, $size) {
    $l = strlen($to);
    if ($l < $toffset + $size) {
        for ($i = $l; $i < $toffset + $size; $i++)
            $to .= "\0";
    }
    for ($i = 0; $i < $size; $i++) {
        $to[$toffset + $i] = chr(ord($from[$foffset + $i]));
    }
}

function le_get_bit($dst, $off) {
    $pos = intval($off / 8);
    $off = $off % 8;
    $byte = ord($dst[$pos]);
    $mask = 1 << $off;
    return ($byte & $mask) ? 1 : 0;
}

function le_set_bit(&$dst, $off, $bit) {
    $pos = intval($off / 8);
    $off = $off % 8;
    $byte = ord($dst[$pos]);
    $mask = 1 << $off;
    if ($bit)
        $byte |= $mask;
    else
        $byte &= (~$mask & 0xff);
    $dst[$pos] = chr($byte);
}

function le_bit_read($val, $val_off, $val_size) {
    $out = "";
    for ($i = 0; $i < $val_size; $i += 8) {
        $one = 0;
        for ($n = 0; $n < 8; $n++) {
            if ($i + $n >= $val_size)
                break;
            $bit = le_get_bit($val, $val_off + $i + $n);
            if ($bit)
                $one |= (1 << $n);
        }
        $out .= chr($one);
    }
    return $out;
}

function le_bit_write(&$dst, $dst_off, $val, $val_off, $val_size) {
    $dst_size = strlen($dst) * 8;
    if ($dst_size < ($dst_off + $val_size - $val_off)) {
        $padding = $dst_off + $val_size - $val_off - $dst_size;
        $padding_byte = intval($padding / 8);
        if ($padding % 8 != 0)
            $padding_byte += 1;
        while ($padding_byte > 0) {
            $dst .= "\0";
            $padding_byte -= 1;
        }
    }
    for ($i = 0; $i < $val_size; $i++) {
        $dst_pos = $dst_off + $i;
        $val_pos = $val_off + $i;
        $bit = le_get_bit($val, $val_pos);
        le_set_bit($dst, $dst_pos, $bit);
    }
    return $val_size;
}

function my_serialize($spec, $map) {
    $out = "";
    $off_bit = 0;
    foreach ($spec as $f => $type) {
        $v = $map[$f];
        if (strncmp($type, 'INT', 3) == 0) {
            $tmp = explode(':', $type);
            $bit_size = intval($tmp[1]);
            assert($bit_size > 0 && $bit_size % 8 == 0);
            $val = int2bin($v, $bit_size / 8);
        } else if (strncmp($type, 'BIT', 3) == 0) {
            $tmp = explode(':', $type);
            $bit_size = intval($tmp[1]);
            assert ($bit_size > 0 && $bit_size < 8);
            $v &= ((1 << ($bit_size + 1)) - 1);
            $val = int2bin($v, 1);
        } else {
            $l = strlen($v);
            assert ($l < 256);
            $bit_size = $l * 8;
            $l = int2bin($l, 1);
            $off_bit += le_bit_write($out, $off_bit, $l, 0, 8);
            $val = $v;
        }
        $off_bit += le_bit_write($out, $off_bit, $val, 0, $bit_size);
    }
    return $out;
}

function my_unserialize($spec, $str) {
    $map = array();
    $bit_off = 0;
    foreach ($spec as $f => $s) {
        if (strncmp($s, 'INT', 3) == 0) {
            $tmp = explode(':', $s);
            $bit_size = intval($tmp[1]);
            $val = le_bit_read($str, $bit_off, $bit_size);
            $map[$f] = le_read_n($val, 0, $bit_size / 8);
        } else if (strncmp($s, 'BIT', 3) == 0) {
            $tmp = explode(':', $s);
            $bit_size = intval($tmp[1]);
            $val = le_bit_read($str, $bit_off, $bit_size);
            $map[$f] = le_read_n($val, 0, 1);
        } else {
            $l = le_bit_read($str, $bit_off, 8);
            $bit_off += 8;
            $bit_size = ord($l) * 8;
            $val = le_bit_read($str, $bit_off, $bit_size);
            $map[$f] = $val;
        }
        $bit_off += $bit_size;
    }
    return $map;
}

function my_session_encode($session) {
    # 序列化
	$spec = get_session_spec();
    $str = my_serialize($spec, $session);
    # 计算密码
    $rand = int2bin(rand(0, 65535), 2);
    $middle = $rand. SESSION_PSK;
    $pwd = md5($middle);
    # 加密
    $out = $rand. rc4_crypt($pwd, $str);
    # 自定义的base64编码
	$out = my_base64_encode($out);
    return $out;
}

function my_session_decode($str) {
    # 解码
	$str = my_base64_decode($str);
    # 计算密码
    $rand = substr($str, 0, 2);
    $middle = $rand. SESSION_PSK;
    $pwd = md5($middle);
    # 解密
    $out = rc4_crypt($pwd, substr($str, 2));
    # 反序列化
	$spec = get_session_spec();
    $session = my_unserialize($spec, $out);
    return $session;
}

function my_base64_encode($out)
{
	$out = base64_encode($out);
	$out = str_replace('+', '-', $out);
	$out = str_replace('/', ',', $out);
	$n = substr_count($out, '=');
	switch($n) {
		case 0:
			$out = $out. '0';
		break;
		
		case 1:
			$out = str_replace('=', 1, $out);
		break;
		
		case 2:
			$out = str_replace('==', 2, $out);
		break;
	}
	return $out;
}

function my_base64_decode($str)
{
	$str = str_replace('-', '+', $str);
	$str = str_replace(',', '/', $str);
	$n = substr($str, -1);	
	switch($n) {
		case 0:
		break;
		
		case 1:
			$str = preg_replace('/1$/', '=', $str);
		break;
		
		case 2:
			$str = preg_replace('/2$/', '==', $str);
		break;
	}
	$str = base64_decode($str);
	return $str;
}
