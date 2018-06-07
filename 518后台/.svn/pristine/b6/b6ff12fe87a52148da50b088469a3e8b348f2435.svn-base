<?php
 
function up_load_thumbimg($file,$dir,$config){
	$attachimg = $file;

	$attachimg['file_ext'] = strtolower(substr($attachimg['name'], strrpos($attachimg['name'], '.')+1));
	if(in_array($attachimg['file_ext'],array('jpg','jpeg','png','gif'))){
		
		$gearman_cfg = $config;
		$def_width_limit = $gearman_cfg['width'];
		$def_filesize_limit = $gearman_cfg['filesize']; 
		$image_info = getimagesize($attachimg['tmp_name']);
		$attachimg['width']  = $image_info[0];
		$attachimg['height']  = $image_info[1];
		$isresized = 0;
		
		$source = $attachimg['tmp_name'];
		$filename = date('His').strtolower(random(16)).'.'.$attachimg['file_ext'];
		$date = date('Ym/d');
		
		$datedir = $dir.$date;
		
		if (!is_dir($datedir)) mkdir($datedir, 0777, true);
		
		$target = $datedir.'/'.$filename;
		
		if($attachimg['size'] > $def_filesize_limit || $attachimg['width'] > $def_width_limit){
			$params = array(
						'from' => $attachimg['tmp_name'], 
						'to' => $target, 
						'width' => $gearman_cfg['real_width'],
						'filesize' => $attachimg['size'],
						'file_type' => $attachimg['file_ext'],
						'src_w' => $attachimg['width'],
						'gearman_cfg' => $gearman_cfg,
					);
			if($attachimg['file_ext'] == 'gif'){
				list($width,$height,$isresized) = setimageGIF($params);
			}else{
				list($width,$height,$isresized) = thumb_by_imagemagick_1($params);
			}
		}
		if($isresized != 1) move_uploaded_file($file['tmp_name'],$target);
		$redirect_ext = ($isresized == 1 && ($attachimg['file_ext'] == 'png'||$attachimg['file_ext'] == 'gif')) ? '.jpg' : '';
		return $date.'/'.$filename.$redirect_ext;
		
	}
		
	
	
}

/* 
 * params @Í¼Æ¬ÐÅÏ¢ $param type array();
 * return array(width,height,ÊÇ·ñÑ¹Ëõ 0/1)
 */
function thumb_by_imagemagick_1($param)
{
		$gearman_cfg = $param['gearman_cfg'];
		
		$tmp_dir = $gearman_cfg['tmp_file_dir'];
		$cfg_width =  $gearman_cfg['width'];
		$cfg_filesize = $gearman_cfg['filesize'];
		$param['resize_w'] = 0;
		$param['resize_q'] = 0;
		if($param['src_w'] >= $cfg_width){
			$param['resize_w'] = 1;
		}elseif($param['filesize'] >= $cfg_filesize){
			$param['resize_q'] = 1;
		}
        $from = $param['from'];		
		if(!$param['resize_w'] && !$param['resize_q']){
			list($res_w,$res_h) = getimageWH($from);
			return array($res_w,$res_h,0);
		}
		
		
        $to = $param['to'];
        $width = $param['width']; //Ñ¹ËõºóµÄ¿í
        $height = $param['height']; //Ñ¹ËõºóµÄ¸ß
        $height = is_null($height) ? 0 : $height;
        $filesize = $param['filesize']; //Ô­Í¼´óÐ¡
        $resize_w = $param['resize_w']; //ÊÇ·ñ²Ã¿í
		
		
        try
        {
			$use_tmp_jpg = 0;
			$image = new Imagick();
			if($param['file_type'] == 'png'){
				$tmp_file = md5(microtime().uniqid().rand(1,10)).'.jpg';
				$tmp_dir_file = $tmp_dir.$tmp_file;
				$image -> readImage($from);
				$image -> writeImage($tmp_dir_file);
				$use_tmp_jpg = 1;
				$from = $tmp_dir_file;
				$to = $to.'.jpg';
			}
			$image -> readImage($from);
			if($resize_w){
				$image->thumbnailImage($width, $height);
			}
			$image->setImageFormat('JPEG');
			$image->setImageCompression(Imagick::COMPRESSION_JPEG);
			$a = $image->getImageCompressionQuality() * 0.70;
			if ($a == 0) {
				$a = 70;
			}
			if($use_tmp_jpg && file_exists($tmp_dir_file)) @unlink($tmp_dir_file);
			$image->setImageCompressionQuality($a);
			$image->stripImage();
			$image->writeImage($to);
			$image->destroy();
        }
        catch(exception $e)
        {
			return false;
        }
		if(file_exists($to)){
			list($width,$height) = getimageWH($to);
			return array($width,$height,1);
		}
        return false;
}

/* 
 * params $path Â·¾¶
 * return array(width,height)
 */
function getimageWH($path){
	$image = new Imagick($path);
	$height = $image -> getImageHeight();
	$width = $image -> getImageWidth();
	$image -> destroy();
	return array($width,$height);	
}


function setimageGIF($param){
	$path = $param['from'];
	$im = new Imagick($path);
	$resize_w = 0;
	$resize_q = 0;

	foreach ($im as $frame) {
		$config = $param['gearman_cfg'];
		$cfg_width =  $config['width'];
		$cfg_filesize = $config['filesize'];
        $width = $param['width'];
        $height = $param['height'];
        $height = is_null($height) ? 0 : $height;
		$to = $param['to'].'.jpg';
		if($param['src_w'] >= $cfg_width){
			$resize_w = 1;
		}elseif($param['filesize'] >= $cfg_filesize){
				$resize_q = 1;
		}
		
		$frame->setImageFormat('JPEG');
		$frame->setImageCompression(Imagick::COMPRESSION_JPEG);
		if($resize_w) $frame->thumbnailImage($width,$height);
		if($resize_q) $frame->setImageCompressionQuality(70);
		$frame->writeImage($to,true);
		break;
	}

	if(file_exists($to)){
		$imageinfo = @getimagesize($to);
		return array($imageinfo[0],$imageinfo[1],1);
	}
	return array($width,$height,0);
}

/**
* ²úÉúËæ»úÂë
* @param $length - Òª¶à³¤
* @param $numberic - Êý×Ö»¹ÊÇ×Ö·û´®
* @return ·µ»Ø×Ö·û´®
*/
function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

