<?
function smarty_modifier_sub_str($str, $length, $charset='utf-8', $append='...')
{
    $str = trim($str);
    $strlength = strlen($str);
    if ($length == 0 || $length >= $strlength) {
        return $str;
    } elseif ($length < 0) {
        $length = $strlength + $length;
        if ($length < 0) {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr')) {
        $newstr = mb_substr($str, 0, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        $newstr = iconv_substr($str, 0, $length, $charset);
    } else {
        $newstr = substr($str, 0, $length);
    }
    if ($append && $str != $newstr) {
        $newstr .= '...';
    }
    return $newstr;	
}
