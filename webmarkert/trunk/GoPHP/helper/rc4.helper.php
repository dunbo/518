<?php

if (!function_exists("rc4_crypt")):
function rc4_crypt($key, $msg) {
	if (function_exists('rc4_crypt_native')) {
		return rc4_crypt_native($key, $msg);
	}
    $td = mcrypt_module_open('arcfour', '' , 'stream', '');
    mcrypt_generic_init($td, $key, null);
    $encrypted = mcrypt_generic($td, $msg);
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
    return $encrypted;
}
endif;

