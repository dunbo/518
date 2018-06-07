<?php
//读文件
function getfilecontents($furl,$limit) {
    $time = time();
    if(!file_exists($furl)){
        return null;
    }else{
        if(($time-filemtime($furl))< $limit)
        {
            $html = file_get_contents($furl);
            return  $html;
        }else{
            unlink($furl);
            return null;
        }
    }
}
//开启缓存
function get_ob_start() {
    ob_start();
}
//写文件 并 读出文件
function putfilecontents($furl,$mod) {
    $html = ob_get_contents();
    ob_end_clean();
    $dir = dirname($furl);
    mk_dir($dir,$mod);
    $fp = fopen($furl, "w+");
	$lockfile = $furl.'.lock';
    if(!file_exists($lockfile)){
      file_put_contents($lockfile,"lock");
      fwrite($fp, $html);
      fclose($fp);
	  unlink($lockfile);
    }
    return $html;

}
//递归生出目录
function mk_dir($path, $mod = 0755, $recursive = false)
{
	static $loop = 0;
	$loop ++;
    if (file_exists($path) || @mkdir($path, $mod, $recursive) ) {
    	return true;
    }
	if ($loop>10) return false;
    return mkdir(dirname($path)) && mk_dir($path, $mod, $recursive);
}
?>