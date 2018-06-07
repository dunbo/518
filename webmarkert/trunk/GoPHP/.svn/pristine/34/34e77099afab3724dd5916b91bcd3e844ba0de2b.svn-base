<?php
// 显示软件的大小，小于1M以K为单位；小于1G、大于1M以M为单位；大于1G以G为单位 保留2位小数
// 传字节$size
function size2size($size, $type=''){
	/*if (!is_numeric($size)){
		echo '不是有效的数字！';
	}*/
	if ($type==''){
		if ($size >= 1073741824){	// G
			$size = round($size / 1073741824, 2).'G';
		} elseif (($size >= 1048576) && ($size <= 1073741824)){	// M
			$size = round($size / 1048576, 2).'M';
		} else {	// K
			$size = round($size / 1024, 2).'K';
		}
	} else {
		switch ($type){
			case 'G':
				$size = round($size / 1073741824, 2).'G';
				break;
			case 'M':
				$size = round($size / 1048576, 2).'M';
				break;
			case 'K':
				$size = round($size / 1024, 2).'K';
				break;
		}
	}
	return $size;
}