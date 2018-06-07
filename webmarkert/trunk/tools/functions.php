<?php
define('OUT_SHELL', '/tmp/compress_shell.log');
$use_new_magick_path = true;
if ($use_new_magick_path && file_exists('/usr/local/ImageMagick/bin/convert')) {
	define('IMAGEMAFICK_PATH', '/usr/local/ImageMagick/bin/');	
} elseif (file_exists('/usr/local/bin/convert')) {
	define('IMAGEMAFICK_PATH', '/usr/local/bin/');
} elseif (file_exists('/usr/bin/convert')) {
	define('IMAGEMAFICK_PATH', '/usr/bin/');
}

// 加载广告扫描代码（新）
include_once (dirname(realpath(__FILE__)).'/AdSdkScan.php');
// 加载广告扫描代码（旧，弃用）
include_once (dirname(realpath(__FILE__)).'/AdSdkScan_old.php');

if (!function_exists('sendNotification')):
	function sendNotification($data)
	{
		$service_port = '192.168.1.118';
		$address = 20012;

		$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$result = @socket_connect($socket, $address, $service_port);
		if (!$result) {
			return false;
		}

		$put = json_encode($data). "\n";

		socket_write($socket, $put, strlen($put));
		socket_close($socket);
	}
endif;

if (!function_exists('get_apk_abi')):
	define('ABI_NONE', 0);
	define('ABI_ARMEABI', 1);
	define('ABI_ARMEABI_V7A', 2);
	define('ABI_X86', 4);
	define('ABI_MIPS', 8);

	function get_apk_abi($apk, $ret = '') {
		$known_abis = array(
			'armeabi' => ABI_ARMEABI,
			'armeabi-v7a' => ABI_ARMEABI_V7A,
			'x86' => ABI_X86,
			'mips' => ABI_MIPS,
		);
		if ($ret == '') {
		$cmd = "unzip -l $apk 2>/dev/null";
		$ret = shell_exec($cmd);
		}
		if (empty($ret))
			return ABI_NONE;
		if (preg_match_all('/lib\/(.+)\//m', $ret, $matches) == 0)
			return ABI_NONE;
		$abi = ABI_NONE;
		foreach ($matches[1] as $val) {
			if (isset($known_abis[$val]))
				$abi |= $known_abis[$val];
		}
		return $abi;
	}
endif;

//if (!function_exists('test_apk_for_addon')):
//endif;

//if (!function_exists('test_apk_for_addon_NEW')):
//endif;

if (!function_exists('go_file_size')):
	function go_file_size($path) {
		$fp = @fopen($path, "r");
		if (!$fp)
			return -1;
		$stat = fstat($fp);
		fclose($fp);
		return $stat['size'];
	}
endif;

if (!function_exists('go_shell_exec')):
	function go_shell_exec($cmd) {
		$ret = shell_exec("${cmd}; echo $?");
		if ($ret != 0)
			file_put_contents('/tmp/go_shell_exec.err', "${cmd}\n", FILE_APPEND);
		return intval($ret);
	}
endif;

if (!function_exists('go_strip_snapshot')):
	function go_strip_snapshot($in, $out, $max = 102400, $flip = false) {
		list($width, $height) = getimagesize($in);
		$rotate = false;
		if ($flip && $width > $height)
			$rotate = true;
		$filesize = go_file_size($in);
		if ($filesize < 0)
			return false;
		if (!$rotate && $filesize <= $max) {
			if ($in == $out)
				return true;
			return copy($in, $out);
		}
		$retry = 0;
		$tempname = false;
		while (!$tempname) {
			$tempname = tempnam('/tmp', 'snapshot_');
			$retry += 1;
			if ($retry > 99)
				return false;
		}
		$tempname .= '.jpg';
		# TODO: calculate how many bytes are reduced with quality going down
		$quality = 80;
		while ($filesize > $max) {
			$ret = go_shell_exec(IMAGEMAFICK_PATH. "convert -quality ${quality} ${in} ${tempname} 2>/dev/null");
			if ($ret != 0)
				return false;
			$quality -= 1;
			if ($quality < 1)
				return false;
			$filesize = go_file_size($tempname);
		}
		if ($rotate) {
			$input = go_file_size($tempname) > 0 ? $tempname : $in;
			$ret = go_shell_exec(IMAGEMAFICK_PATH. "convert -rotate 90 ${input} ${tempname} 2>/dev/null");
			if ($ret != 0)
				return false;
		}
		$ret = copy($tempname, $out);
		unlink($tempname);
        @unlink($tempname);
		return $ret;
	}
endif;

function go_apk_info($file, $ignore_icon = false)
{
	if (!is_file($file)){
		file_put_contents('/tmp/ym.log',print_r($file,true),FILE_APPEND);
		return false;
	}

	$result = array();
	$cmd = "unzip -l $file 2>/dev/null";
	$ret = shell_exec($cmd);
	$lines = explode("\n", $ret);
	$check_file = array(
		'AndroidManifest.xml' => 0,
		'classes.dex' => 0,
	);
	foreach ($lines as $val) {
		if(preg_match("/ AndroidManifest\.xml$/i",$val)) {
			$check_file['AndroidManifest.xml'] += 1;
			if ($check_file['AndroidManifest.xml'] > 1) {
				file_put_contents('/tmp/ym.log','--3--',FILE_APPEND);
				return false;
			}
		}

		if(preg_match("/ classes\.dex$/i",$val)) {
			$check_file['classes.dex'] += 1;
			if ($check_file['classes.dex'] > 1) {
				file_put_contents('/tmp/ym.log','--4--',FILE_APPEND);
				return false;
			}
		}
	}



	$result['abi'] = get_apk_abi($file, $ret);

	$bin_path = '/data/www/wwwroot/new-wwwroot/config/gnu';
	if (!is_file("{$bin_path}/aapt"))
		$bin_path = '/data/www/wwwroot/config/gnu';
	$info = shell_exec("{$bin_path}/aapt d badging \"{$file}\" 2>/dev/null");

	$lines = explode("\n", $info);

	$result['permission'] = array();

	$result['supports-screens'] = 0;
	$result['library'] = array();
	$result['library-optional'] = array();

	$result['sign'] = getSignFromApk($file);

	$max_dpi = 0;
	$dpi_icon = '';
	foreach($lines as $str){
		if (preg_match("/package: name='([^']*?)' versionCode='([^']*?)' versionName='([^']*?)'/", $str, $m)) {
			$result['packagename'] = $m[1];
			$result['versionCode'] = $m[2];
			$result['versionName'] = $m[3];
			if (empty($result['packagename']) || empty($result['versionCode'])) {
				file_put_contents('/tmp/ym.log','--5--',FILE_APPEND);
				return false;
			}
		}
		if (preg_match("/application:\ *?label='(.*?)'\ *?icon='([^']*?)'/", $str, $m)) {
			$result['label'] = $m[1];
			$app_icon = $m[2];
		}
		if (preg_match("/application-label-zh_CN:'(.*?)'/", $str, $m)) {
			$result['label_cn'] = $m[1];
		}
		if (preg_match("/application-label-zh:'(.*?)'/", $str, $m)) {
			$result['label_zh'] = $m[1];
		}
		if(preg_match("/application\-icon\-(\d+):'([^']*?)'/i",$str,$m)) {
			$dpi = $m[1];
			if ($dpi > $max_dpi) {
				$max_dpi = $dpi;
				$dpi_icon = $m[2];
			}
		}

		if (preg_match("/sdkVersion:'([^']*?)'/", $str, $m)) {
			$result['min_sdk_ver'] = $m[1];
		}

		if (preg_match("/maxSdkVersion:'([^']*?)'/", $str, $m)) {
			$result['max_sdk_ver'] = $m[1];
		}

		if (preg_match("/uses-permission:'([^']*?)'/", $str, $m)) {
			$result['permission'][] = $m[1];
		}

		if (stripos($str, "supports-screens:") !== false) {
			if (stripos($str, "small") !== false) {
				$result['supports-screens'] |= 1;
			}
			if (stripos($str, "normal") !== false) {
				$result['supports-screens'] |= 2;
			}
			if (stripos($str, "large") !== false) {
				$result['supports-screens'] |= 4;
			}
		}

		if (preg_match("/uses-library:'([^']*?)'/", $str, $m)) {
			$result['library'][] = $m[1];
		}

		if (preg_match("/uses-library-not-required:'([^']*?)'/", $str, $m)) {
			$result['library-optional'][] = $m[1];
		}
	}
	if (!empty($dpi_icon)) {
		$app_icon = $dpi_icon;
	}
	if ($ignore_icon == true) {
		return $result;
	}
	if (empty($result['packagename']) || empty($result['versionCode'])) {
		file_put_contents('/tmp/ym.log','--6--',FILE_APPEND);
		return false;
	}
	//兼容8.0图标
	if (preg_match('/\.xml/', $app_icon)) {
		$cmd = "{$bin_path}/aapt d xmltree '{$file}' {$app_icon} 2>/dev/null | grep foreground -A1|grep drawable|awk -F'@' '{print \$2}'";
		$app_icon = trim(shell_exec($cmd));

		$cmd = "{$bin_path}/aapt d --values resources '{$file}' 2>/dev/null | grep {$app_icon} -A1|grep '.png\"' |head -n 1 |awk -F'\"' '{print \$2}'";

		$resources = trim(shell_exec($cmd));
		if ($resources) {
			$app_icon = $resources;
		}
	}
	if (preg_match('/@[0-9a-fA-F]{8}/', $app_icon)) {
		$rid = strtolower(str_replace('@', '', $app_icon));
		$resources = shell_exec("{$bin_path}/aapt d resources \"{$file}\" 2>/dev/null");
		$lines = explode("\n", $resources);
		foreach($lines as $str) {
			if (stripos($str, $rid) !== false) {
				preg_match('/.*?:([^:]+):.*/', $str, $m);
				$app_icon = "res/{$m[1]}.png";
				break;
			}
		}
	}
    $tm = date('Ymd/His', time());
    $tmp_dir = "/tmp/apkinfo_tmp/{$tm}/{$result['packagename']}";
    if(!is_dir($tmp_dir)) {
        $oldumask = umask(0);
        mkdir($tmp_dir,0777,true);
        umask($oldumask); 
    }
    
    //$icon_fname = preg_replace('/.*\//', '', $app_icon);
    $icon_fname = basename($app_icon);
    $icon_fpath = "{$tmp_dir}/{$icon_fname}";

    $extract = go_shell_exec("unzip -jo -d {$tmp_dir} \"{$file}\" {$app_icon} 2>/dev/null");	
	if ($extract != 0){
		file_put_contents('/tmp/ym.log','--7--',FILE_APPEND);
		return false;
	}
		

	if (!preg_match('/\.png$/', $icon_fname)) {
		shell_exec("mv {$icon_fpath} {$icon_fpath}.png 2>/dev/null");
		$icon_fpath = $icon_fpath. '.png';
	}

	$icon_fpath_72 = str_replace('.png','_72.png',$icon_fpath);
	$icon_fpath_96	= str_replace('.png','_96.png',$icon_fpath);
	$icon_fpath_125 = str_replace('.png','_125.png',$icon_fpath);
	copy($icon_fpath, "${icon_fpath}.1");
	copy($icon_fpath, $icon_fpath_72);
	copy($icon_fpath,$icon_fpath_96);
	copy($icon_fpath,$icon_fpath_125);

	go_resize_icon($icon_fpath);									//48*48
	go_resize_icon($icon_fpath_72, 20480, 72, 72);	//72*72
	go_resize_icon($icon_fpath_96,30720,96,96);
	go_resize_icon($icon_fpath_125,40960,125,125);
	$result['icon'] = $icon_fpath;
	$result['icon_72'] = $icon_fpath_72;
	$result['icon_96']	= $icon_fpath_96;
	$result['icon_125'] = $icon_fpath_125;
	$result['icon_original'] = "${icon_fpath}.1";

	if (!empty($result['versionCode']) && !empty($result['versionName'])) {
		return $result;
	} else {
		file_put_contents('/tmp/ym.log','--8----',FILE_APPEND);
		return false;
	}
}

function resize_icon($fn, $max =  10240, $w = 48, $h = 48) {
	list($img_width, $img_height) = getimagesize($fn);

	# $w * $h, < 10KB
	# if > $w * $h, resize and draw over a transparent background
	# if < $w * $h, do nothing
	if ($img_width > $w || $img_height > $h) {
		$rate = $w / $img_width;
		if ($h / $img_height < $h / $img_width) {
			$rate = $h / $img_height;
	}
		$new_width = intval($img_width * $rate);
		$new_height = intval($img_height * $rate);
		$cmd = IMAGEMAFICK_PATH. "convert -resize {$new_width}x{$new_height} \"{$fn}\" \"{$fn}\" 2>/dev/null";
		shell_exec($cmd);
		if ($new_width != $w || $new_height != $h) {
			$cmd = IMAGEMAFICK_PATH. "convert -size ${w}x${h} xc:transparent -gravity center -draw \"image over 0,0,0,0 '${fn}'\" \"${fn}\" 2>/dev/null";
			shell_exec($cmd);
		}
	}
	$tmp = $fn. '.tmp';
	$filesize = go_file_size($tmp);
	$quality = 80;
	$ret = 0;
	copy($fn, $tmp);
	while ($filesize > $max) {
		$ret = go_shell_exec(IMAGEMAFICK_PATH. "convert -quality ${quality} ${fn} ${tmp} 2>/dev/null");
		if ($ret != 0)
			return false;
		$quality -= 1;
		if ($quality < 1)
			return false;
		$filesize = go_file_size($tmp);
	}
	$ret = copy($tmp, $fn);
	unlink($tmp);
	return $ret;
}

if (!function_exists("go_resize_icon")):
function go_resize_icon($fn, $max =  10240, $w = 48, $h = 48) {
	list($img_width, $img_height) = getimagesize($fn);
	# $w * $h, < 10KB
	# if > $w * $h, resize and draw over a transparent background
	# if < $w * $h, do nothing
	if ($img_width > $w || $img_height > $h) {
		# 首先按比例缩放
		$rate = $w / $img_width;
		if ($h / $img_height < $h / $img_width) {
			$rate = $h / $img_height;
		}
		$new_width = intval($img_width * $rate);
		$new_height = intval($img_height * $rate);
		$cmd = IMAGEMAFICK_PATH. "convert -resize {$w}x{$h}! \"{$fn}\" \"{$fn}\" 2>/dev/null";
		$ret = go_shell_exec($cmd);
		if ($ret != 0) {
			#echo __LINE__. "\n";
			return false;
		}
		list($img_width, $img_height) = getimagesize($fn);
	}
	# 然后放到${w}x${h}的画布上
	if ($img_width != $w || $img_height != $h) {
		//$cmd = IMAGEMAFICK_PATH. "convert -size ${w}x${h} xc:transparent -gravity center -draw \"image over 0,0,0,0 '${fn}'\" \"${fn}\" 2>/dev/null";
		$cmd = IMAGEMAFICK_PATH. "convert -resize {$w}x{$h}! \"{$fn}\" \"{$fn}\" 2>/dev/null";
		$ret = go_shell_exec($cmd);
		if ($ret != 0) {
			#echo __LINE__. "\n";
			   return false;
		}
	}
	$tmp = $fn. '.tmp';
	if (!copy($fn, $tmp)) {
		   #echo __LINE__. "\n";
		return false;
	}
	$filesize = go_file_size($fn);
	$quality = 80;
	while ($filesize > $max) {
		$ret = go_shell_exec(IMAGEMAFICK_PATH. "convert -quality ${quality} ${tmp} ${fn} 2>/dev/null");
		if ($ret != 0) {
			#echo __LINE__. "\n";
			unlink($tmp);
			return false;
		}
		$quality -= 1;
		if ($quality < 1) {
			#echo __LINE__. "\n";
			unlink($tmp);
			return false;
		}
		$filesize = go_file_size($fn);
	}
	unlink($tmp);
	return true;
}
endif;

if (!function_exists("go_make_links")):
	function go_make_links($file) {
		$dirname = dirname($file);
		$basename = basename($file);
		$uppername = strtoupper($basename);
		if ($uppername != $basename)
			shell_exec("cd ${dirname}; ln -s '${basename}' '${uppername}' 2>/dev/null");
		$lowername = strtolower($basename);
		if ($lowername != $basename)
			shell_exec("cd ${dirname}; ln -s '${basename}' '${lowername}' 2>/dev/null");
	}
endif;

function resize_image($in, $out, $w = 48, $h = 48) {
	list($img_width, $img_height) = getimagesize($in);

	$rate = 0;

	if ($w>0 && $img_width > $w) {
		$rate = $w / $img_width;
	}
	if ($rate == 0 && $h>0 && $img_height > $h) {
		$rate = $h / $img_height;
	}

	if ($rate > 0) {
		$new_width = intval($img_width * $rate);
		$new_height = intval($img_height * $rate);
		$cmd = IMAGEMAFICK_PATH. "convert -resize {$new_width}x{$new_height} \"{$in}\" \"{$out}\" 2>/dev/null";
		$ret = go_shell_exec($cmd);
	} else {
		$cmd = "cp {$in} {$out}";
		$ret = go_shell_exec($cmd);
	}
	return $ret == 0;
}

if (!function_exists('go_resize_image')):
	function go_resize_image($in, $out, $limit, $width = 0, $height = 0) {
		list($original_width, $original_height) = getimagesize($in);
		if (!$original_width || !$original_height)
			return false;
		$original_size = go_file_size($in);
		if ($original_size <= 0)
			return false;
		if ($limit >= $original_size && $width == 0 && $height == 0) {
			if ($in == $out)
				return true;
			return copy($in, $out);
		}
		$base = basename($in);
		$ext = '';
		$dot = strrpos($base, '.');
		if ($dot !== false) {
			$ext = substr($base, $dot + 1);
			$ext = strtolower($ext);
			$base = substr($base, 0, $dot - 1);
		}
		$temp = tempnam('/tmp', 'image_temp_');
		$extra = "";
		if ($width > 0 || $height > 0) {
			if ($width <= 0 || $height <= 0) {
				if ($height <= 0) {
					$new_width = $width;
					$rate = $width / $original_width;
					# 按2对齐
					$new_height = (~1) & (intval($rate * $original_height) + 1);
				}
				else {
					$new_height = $original_height;
					$rate = $original_height / $height;
					$new_width = (~1) & (intval($rate * $original_width) + 1);
				}
			} else {
				$new_width = $width;
				$new_height = $height;
			}
			$extra = "-resize '${new_width}x${new_height}!'";
		}
		# 调整尺寸
		if (strlen($extra) > 0) {
			$cmd = IMAGEMAFICK_PATH. "convert ${extra} \"${in}\" \"${temp}.${ext}\" 2>/dev/null";
			$rc = go_shell_exec($cmd);
		}
		else {
			$cmd = "copy \"${in}\" \"${temp}.${ext}\" 2>/dev/null";
			$rc = copy($in, "${temp}.${ext}") ? 0 : -1;
		}
		if ($rc != 0) {
			file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
			@unlink("${temp}.${ext}");
			@unlink($temp);
			return false;
		}
		# 尝试减少文件大小
		$kb = intval($limit / 1024);
		$cmd = IMAGEMAFICK_PATH. "convert -strip -define jpeg:extent:${kb}kb \"${temp}.${ext}\" \"${temp}.jpg\" 2>/dev/null";
		$rc = go_shell_exec($cmd);
		if ($rc != 0) {
			file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
			@unlink("${temp}.jpg");
			@unlink($temp);
			return false;
		}
		if (go_file_size("${temp}.jpg") <= $limit) {
			$ret = copy("${temp}.jpg", $out);
			@unlink("${temp}.jpg");
			@unlink($temp);
			return $ret;
		}
		# 降低质量
		$quality = 10;
		$out_size = $original_size;
		do {
			$cmd = IMAGEMAFICK_PATH. "convert -quality ${quality} \"${temp}.jpg\" \"${out}\" 2>/dev/null";
			$rc = go_shell_exec($cmd);
			if ($rc != 0) {
				file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
				break;
			}
			$quality -= 1;
			$out_size = go_file_size($out);
		} while (($quality > 0) && ($out_size >= 0) && ($out_size > $limit));
		file_put_contents('/tmp/strip.log', "q=${quality},${out_size}/${limit}\n", FILE_APPEND);
		@unlink("${temp}.jpg");
        @unlink($temp);
		return ($quality > 0) && ($out_size <= $limit);
	}
endif;
if (!function_exists('get_apkinfo_app2sd')):
function get_apkinfo_app2sd($file)
{
	if (!is_file($file))
		return false;
	$bin_path = '/data/www/wwwroot/new-wwwroot/config/gnu';
	if (!is_file("{$bin_path}/aapt"))
		$bin_path = '/data/www/wwwroot/config/gnu';
	$info = shell_exec("{$bin_path}/aapt d xmltree \"{$file}\" AndroidManifest.xml 2>/dev/null");
	$status = "";
	if(preg_match("/android:installLocation\([^'\(]*?\)=\(type[^'\(]*?\)[\s]*0x0/", $info)){
		$status = "android:installLocation=auto";
	}else if(preg_match("/android:installLocation\([^'\(]*?\)=\(type[^'\(]*?\)[\s]*0x2/", $info)){
		$status = "android:installLocation=preferExternal";
	}else if(preg_match("/android:installLocation\([^'\(]*?\)=\(type[^'\(]*?\)[\s]*0x1/",$info)){
		$status = "android:installLocation=internalOnly";
	}
	if (!empty($status)) {
		return $status;
	} else {
		return false;
	}
}
endif;

if (!function_exists('go_test_image')):
	function go_test_image($f) {
		$name = tempnam('/tmp', 'image_'). ".jpg";
		$ret = go_shell_exec(IMAGEMAFICK_PATH. "convert '${f}' '${name}' 2>/dev/null");
		@unlink($name);
		return ($ret == 0);
	}
endif;

function rc4($pass, $data)
{
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

function image_strip_size($in, $out, $limit, $width = 0, $height = 0) {
	list($original_width, $original_height) = getimagesize($in);
	if (!$original_width || !$original_height)
		return false;
	
	$original_size = file_size_no_cache($in);
	//不在要求的范围内不作处理
	if ($original_size <= 0)
		return false;
	if ($limit >= $original_size && $width == 0 && $height == 0) {
		if ($in == $out)
			return true;
		return copy($in, $out);
	}
	$base = basename($in);
	$ext = 'png';
	$dot = strrpos($base, '.');
	if ($dot !== false) {
		$ext = substr($base, $dot + 1);
		$ext = strtolower($ext);
		$base = substr($base, 0, $dot - 1);
	}
	$temp = tempnam('/tmp', 'image_temp_');

	$extra = "";
	if ($width > 0 || $height > 0) {
		if ($width <= 0 || $height <= 0) { //假如 提供了宽度 或 高度（任意一个） 就等比例处理的 缺失值
			if ($height <= 0) {
				$new_width = $width;
				$rate = $width / $original_width;
				# 按2对齐
				$new_height = (~1) & (intval($rate * $original_height) + 1);
			}
			else {
				$new_height = $original_height;
				$rate = $original_height / $height;
				$new_width = (~1) & (intval($rate * $original_width) + 1);
			}
		} else {
			$new_width = $width;
			$new_height = $height;
		}
		$extra = "-resize '${new_width}x${new_height}!'";  //重新处理图片的width  height
	}

	# 调整尺寸
	if (strlen($extra) > 0) {
		$cmd = IMAGEMAFICK_PATH. "convert ${extra} \"${in}\" \"${temp}.${ext}\" 2>/dev/null";
		//file_put_contents('/tmp/lipeng.log',date('Y-m-d H:i:s',time()).'-----'.$cmd."\n",FILE_APPEND);
		$rc = silent_shell_exec($cmd);
	}
	else {
		$cmd = "copy \"${in}\" \"${temp}.${ext}\"";
		$rc = copy($in, "${temp}.${ext}") ? 0 : -1;
	}


	if ($rc != 0) {
		file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
		@unlink("${temp}.${ext}");
		return false;
	}
	# 尝试减少文件大小
	$kb = intval($limit / 1024);
	$cmd = IMAGEMAFICK_PATH. "convert -strip -define jpeg:extent:${kb}kb \"${temp}.${ext}\" \"${temp}.${ext}.jpg\" 2>/dev/null";
	$rc = silent_shell_exec($cmd);
	if ($rc != 0) {
		file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
		@unlink("${temp}.${ext}");
        @unlink($temp);
		return false;
	}
	if (file_size_no_cache("${temp}.${ext}.jpg") <= $limit) {
		$ret = copy("${temp}.${ext}.jpg", $out);
		@unlink("${temp}.${ext}.jpg");
		@unlink("${temp}.${ext}");
        @unlink($temp);
		file_put_contents('/tmp/strip.log', sprintf("jpeg extent: %s for ${out}\n", $ret ? "success" : "failure"), FILE_APPEND);
		return $ret;
	}
	# 降低/提升质量
	$n = 1;
	$quality = 80;
	$direction = -1;
	$out_size = $original_size;
	$tmp_out = "${temp}.${ext}.c.jpg";
	$has_out = false;
	for (;;) {
		$cmd = IMAGEMAFICK_PATH. "convert -quality ${quality} \"${temp}.${ext}.jpg\" \"${tmp_out}\" 2>/dev/null";
		$rc = silent_shell_exec($cmd);
		if ($rc != 0) {
			file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
			break;
		}
		$out_size = file_size_no_cache($tmp_out);

		if ($n == 1) {
			if ($out_size > $limit) {
				$direction = -5;
			} else if ($out_size <= $limit) {
				$has_out = true;
				break;
			} else {
				$direction = 1;
			}
		} else {
			if ($direction > 0 && $out_size > $limit)
				break;
			if ($direction < 0 && $out_size < $limit) {
				$has_out = true;
				break;
			}
		}
		$n++;
		$quality += $direction;
		if ($quality >= 100 || $quality < 1)
			break;
	}
	if ($has_out) {
		copy($tmp_out, $out);
	}
	// if direction is minus, no need to recover to the last state
	if ($n > 1 && $rc == 0 && $direction > 0) {
		$quality -= $direction;
		$cmd = IMAGEMAFICK_PATH. "convert -quality ${quality} \"${temp}.${ext}.jpg\" \"${out}\" 2>/dev/null";
		$rc = silent_shell_exec($cmd);
		$out_size = file_size_no_cache($out);
	}
	#echo "q=${quality},${out_size}/${limit}\n";
	file_put_contents('/tmp/strip.log',"q=${quality},${out_size}/${limit}\n",FILE_APPEND);
	@unlink("${temp}.${ext}.jpg");
	@unlink("${temp}.${ext}");
	@unlink($tmp_out);
	@unlink($temp);
	return ($quality > 0) && ($quality <= 100) && ($out_size <= $limit);
}

function silent_shell_exec($cmd) {
	#echo "${cmd}\n";
	$ret = shell_exec("${cmd}". " >> ". OUT_SHELL. " 2>&1; echo $?");
	$ret_str = $ret === null ? "null" : trim($ret);
	file_put_contents(OUT_SHELL, "${ret_str}: ${cmd}\n", FILE_APPEND);
	if ($ret == null)
		return -1;
	return intval($ret);
}

function file_size_no_cache($file) {
	$fp = fopen($file, "r");
	if (!$fp)
		return -1;
	$stat = fstat($fp);
	fclose($fp);
	return isset($stat['size']) ? $stat['size'] : -1;
}

function image_size_flip($in,$out,$width,$height){
	list($oraginal_width,$oraginal_height) = getimagesize($in);
	if($oraginal_width > $oraginal_height && $width > $height)
	$rotate = false;
	else if($oraginal_width < $oraginal_height && $width < $height)
	$rotate = false;
	else if($oraginal_width == $oraginal_height)
	$rotate = false;
	else
	$rotate = true;

	if ($rotate) {
		$ret = go_shell_exec(IMAGEMAFICK_PATH. "convert -rotate 90 ${in} ${out} 2>/dev/null");
		//file_put_contents('/tmp/lipeng.log','status : '.$ret.' url :'.$in."\n");
		if ($ret != 0)
			return false;
	} else {
		if ($in != $out)
			return copy($in, $out);
	}
	return true;
}

function image_water_mark($in, $w, $out) {
	$temp = tempnam('/tmp', 'compress_temp_');
	$cmd = IMAGEMAFICK_PATH. "composite -gravity southeast \"${w}\" \"${in}\" \"${temp}.png\" 2>/dev/null";
	$rc = silent_shell_exec($cmd);
	if ($rc != 0) {
		file_put_contents('/tmp/strip.log', "`${cmd}' could not be done.\n", FILE_APPEND);
		@unlink("${temp}.png");
		@unlink($temp);
		return false;
	}
	$ret = copy("${temp}.png", $out);
	@unlink("${temp}.png");
    @unlink($temp);
	return $ret;
}


function getSignFromApk($file) {
	$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("pipe", "w") // stderr is a file to write to
			);
	$proc = proc_open('./apks '.$file. ' 2>/dev/null|grep META-INF', $descriptorspec, $pipes, realpath(dirname(realpath(__FILE__)).'/../config/gnu'));

	$string = stream_get_contents($pipes[1]);
	$status = proc_get_status($proc);
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	proc_close($proc);
	if ($status['exitcode'] >0 ) {
		return false;
	}


	$data = explode("\n", $string);
	$result = array();
	foreach($data as $line) {
		if (empty($line)) continue;
		list($path, $signature, $public_key) = explode("\t", $line);
		if (empty($path)) continue;

		$file = strtoupper(basename($path));
		$result[$file] = array($public_key, $signature);
	}
	ksort($result);
	$public_string = '';
	$private_string = '';
	foreach ($result as $v) {
		$public_string .= $v[0];
		$private_string .= $v[1];
	}
	return hashCode($public_string). ','. hashCode($private_string);
}

function hashCode($str) {
	$str = strtoupper($str);
	$hash = 0;
	$multiplier = 1;
	$_offset = 0;
	$_count = strlen($str);
	for ($i = $_offset + $_count - 1; $i >= $_offset; $i--) {
		$hash += ord($str[$i]) * $multiplier;
		$hash = $hash & 0xffffffff;
		$shifted = ($multiplier << 5) & 0xffffffff;
		$multiplier = $shifted - $multiplier;
	}
		if ($hash >= 2147483648) {
				$hash = $hash - 4294967296;
		}
	return $hash;
}

// 包名检测
function check_package($package) {
	include_once 'pkgname.php';
	static $reg = '/^([a-z_\$][a-z_\$0-9]*\.)*[a-z_\$][a-z_\$0-9]*$/i';
	$reg_key_words_prefix = '/^(' . implode ( '|', $key_words ) . ')\./';
	$reg_key_words_infix = '/\.(' . implode ( '|', $key_words ) . ')\./';
	$reg_key_words_surfix = '/\.(' . implode ( '|', $key_words ) . ')$/';

	if (! preg_match ( $reg, $package )) {
		return false;
	}
	return true;
	if (preg_match ( $reg_key_words_prefix, $package )) {
		return false;
	}

	if (preg_match ( $reg_key_words_infix, $package )) {
		return false;
	}

	if (preg_match ( $reg_key_words_surfix, $package )) {
		return false;
	}
	return true;
}

function imageurl_parse($image_url,$func='rc4_encode'){
    $image_url = $func($image_url);
    return $image_url;
}

function test_apk_for_feiwo_version($file,$package) {
    global $aapt;
    $cmd = "${aapt} d xmltree \"${file}\" AndroidManifest.xml 2>/dev/null";
    $content = shell_exec($cmd);
    $result = array();
    if (!$content) {
        return null;
    }

    $lines = explode("\n", $content);
    $count = count($lines);
    for($i = 0; $i < $count; $i++) {
        if (strstr($lines[$i], 'android:name')&&strstr($lines[$i],$package)&&strstr($lines[$i-1], 'meta-data')&&strstr($lines[$i+1], 'android:value')){
			$str_res = explode('"',$lines[$i+1]); 
			$result[] = $str_res[3];
		}
	}
    return $result;
}

// 读sdk插件支持的jar版本号，放在meta-data里
function go_sdk_plugin_jar_version_code($file) {
	$bin_path = '/data/www/wwwroot/new-wwwroot/config/gnu';
	if (!is_file("{$bin_path}/aapt"))
		$bin_path = '/data/www/wwwroot/config/gnu';
    $cmd = "{$bin_path}/aapt d xmltree \"${file}\" AndroidManifest.xml 2>/dev/null";
    $content = shell_exec($cmd);
    $result = array();
    if (!$content) {
        return null;
    }
	$jar_code = null;
	$lines = explode("\n", $content);
    $count = count($lines);
    for($i = 0; $i < $count; $i++) {
        if (strstr($lines[$i], 'android:name')&&strstr($lines[$i],'support_ver')&&strstr($lines[$i-1], 'meta-data')&&strstr($lines[$i+1], 'android:value')){
			// 匹配到了，取jar版本
			if (preg_match("/\(.+?\)=\(.+?\)0x(.+)$/", $lines[$i+1], $matches)) {
				$jar_code = hexdec($matches[1]);
				break;
			}
		}
	}
    return $jar_code;
}

function test_apk_for_feiwo_version_new($file) {    

    $path_rand = md5(rand(1, 100000) . microtime().uniqid());
    //$path_rand = 'ym_test';
    mkdir('/tmp/'.$path_rand,0777,true);    
    $cmd = "unzip -l $file 'assets/*_b' ";    
    $ret  = shell_exec($cmd);
    if(empty($ret)){
        return false;
    }else{
        $lines = explode("\n", $ret);
        foreach($lines as $val){
            if(strstr($val,'assets/') != false){
                $name = explode('assets/' ,$val);
                $zipname = $name[1];    
            }
        }

        if($zipname){
        	$cmd = "unzip -j \"{$file}\" \"assets/{$zipname}\"  -d \"/tmp/{$path_rand}\" ";
            $res = shell_exec($cmd);
            $file_new = "/tmp/{$path_rand}/{$zipname}";
            $cmd = "unzip -j \"{$file_new}\" \"version\"  -d \"/tmp/{$path_rand}\" ";
            shell_exec($cmd);
            $content = file_get_contents("/tmp/{$path_rand}/version");
            return $content;
        }else{
            return false;
        }
    }
}