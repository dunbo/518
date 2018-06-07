<?php
$time = time();
$value = md5($_SERVER['HTTP_USER_AGENT'] . 'goapk'. $time. $_SERVER['REMOTE_ADDR']).'|'.$time;
setcookie('CKISP', $value, $time + 3600);

$url = base64_decode($_GET['u']);
$file_arr = explode('|',$url);
$pos = count($file_arr) - 1;
$filename = $file_arr[$pos];
$filename = imageurl_parse($filename,'rc4_decode');
$file_arr[$pos] = $filename;
$url = implode('/',$file_arr);
$host = "http://".getImageHost().'/';
if($_SERVER['SERVER_ADDR'] == '118.26.203.23'){
    $host = 'http://'.$_SERVER['SERVER_ADDR'].'/';
}
$pos = strrpos($url,'/');
$filename = substr($url,$pos);
header('Content-Disposition: inline; filename='.$filename);
header('Content-Type: image/pjpeg');
header('Location:'.$host.$url);
function getImageHost(){
    $conf = array(
        'img1.anzhi.com',
        'img2.anzhi.com',
        'img3.anzhi.com',
        'img4.anzhi.com',
        'img5.anzhi.com',
    );
    if (is_array($conf)) {
        $k = array_rand($conf);
        $host = $conf[$k];
    } else {
        $host = $conf;
    }
    return $host;
}

function rc4($pass, $data)
{
    if (function_exists('rc4_crypt_native')) {
        return rc4_crypt_native($pass, $data);
    }
    $key[] ='';
    $box[] ='';
    $cipher='';
    $pass_length = strlen($pass);
    $data_length = strlen($data);
    for ($i = 0; $i < 256; $i++) {
        $key[$i] = ord($pass[$i % $pass_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $key[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $data_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $k = $box[(($box[$a] + $box[$j]) % 256)];
        $cipher.= chr(ord($data[$i]) ^ $k);
    }
    return $cipher;
}

function rc4_encode($data, $pass = '')
{
    if ($pass == '') $pass = '$(^7hgAn';
    $data_str = json_encode($data);
    return base64_encode(rc4($pass, $data_str));
}

function rc4_decode($data, $pass = '')
{
    if ($pass == '') $pass = '$(^7hgAn';
    $data_str = base64_decode($data);
    return json_decode(rc4($pass, $data_str), true);
}

function imageurl_parse($image_url,$func='rc4_encode'){
     $image_url = $func($image_url);
      return $image_url;
}
