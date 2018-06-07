<?php
function rsa_decrypt($key, $str)
{
    $content = file_get_contents($key);
    if (!$content)
        return null;
    $pri_key = openssl_pkey_get_public($content);
    if (!$pri_key)
        return null;
    $fail = false;
    $result = '';
    // RSA/None/PKCS1 245
    $chunk = 128;
	$str = base64_decode($str);
	$array = str_split($str, $chunk);
	foreach ($array as $once ) {
		$ret = openssl_public_decrypt($once, $out, $pri_key);
        if (!$ret) {
            $fail = true;
            break;
        }
		$result .= $out;
	}

    openssl_free_key($pri_key);
    return $fail ? null : $result;
}

function rsa_encrypt($key, $str) {
    $content = file_get_contents($key);
    if (!$content)
        return null;
    $pri_key = openssl_pkey_get_private($content);
    if (!$pri_key)
        return null;
    $fail = false;
    $result = '';
    // RSA/None/PKCS1 245
    $chunk = 117;
    for ($i = 0; $i < strlen($str); $i += $chunk) {
        $once = substr($str, $i, $chunk);
        $ret = openssl_private_encrypt($once, $out, $pri_key);
        if (!$ret) {
            $fail = true;
            break;
        }
        $result .= $out;
    }
    openssl_free_key($pri_key);
    return $fail ? null : base64_encode($result);
}
