<?php

/*
*关键词分析
*/
include_once (dirname(realpath(__FILE__)).'/init.php');
$start_tm = microtime_float();
if(isset($_POST['my_referrer'])&&!empty($_POST['my_referrer'])){
	$referrer = $_POST['my_referrer'];
	if(strpos($referrer,'baidu.com/')){
		if(strpos($referrer,'youxuan.baidu.com/')){
			$keyword = query_decode_str($referrer);
		}else{
			$keyword = word_decode_str($referrer);
			if($keyword == ''){
				if(strpos($referrer,'src_')){
					$keyword = src_decode_str($referrer);
				}
				
			}	
		}
	}
	if($keyword != ''){
		if(substr($keyword,(strlen($keyword)-2))=="=="){
			$keyword = get_keyword_from_html($referrer);
		}	
	}
	$end_tm = microtime_float();
	$second = $end_tm - $start_tm;
	if(empty($keyword)||$second>0.5){
		$log = array(
			'referrer_url' =>$referrer,
			'time'=>date("Y-m-d H:i:s",time())
		);
		if($second>5) $log['use_time'] = $second;
		permanentlog("baidu_keyword_error.log",json_encode($log));
	}
	echo $keyword;
}

//word或wd或w为关键词
function word_decode_str($str){
	$keyword = '';
	$key_str = '';
	if(strpos($str,'word=')){
		$key_str = 'word=';
		$word_l = strstr($str,$key_str);
	}else if(strpos($str,'wd=')){
		$key_str = 'wd=';
		$word_l = strstr($str,$key_str);
	}else if(strpos($str,'w=')){
		if(preg_match('/w=[0-9]_[0-9]+_/',$str,$matches)){
			$key_str = $matches[0];
		}
		if(!empty($key_str)&&!empty($str)){
			$word_l = strstr($str,$key_str);
			$word_l = substr($word_l,0,strpos($word_l,'/'));
		}
	}
	if($key_str !=''){
		$key_len = strlen($key_str);
		if(strpos($word_l,'&')){
			$keyword = urldecode(substr($word_l,(strpos($word_l,$key_str)+$key_len),(strpos($word_l,'&')-$key_len)));
		}else{
			$keyword = urldecode(substr($word_l,(strpos($word_l,$key_str)+$key_len)));
		}
	}
	return $keyword;
}


//src为关键词
function src_decode_str($str){
	$word_l = strstr($str,'src_');
	$word_l = substr($word_l,0,strpos($word_l,'|'));
	$keyword = urldecode(substr($word_l,(strpos($word_l,'src_')+4)));
	return $keyword;
}

//query为关键词
function query_decode_str($str){
	if(strpos($str,'newyouxuan')){
		$word_l = urldecode(strstr($str,'query'));
		$word_l = substr($word_l,0,strpos($word_l,'&'));
		$keyword = urldecode(substr($word_l,(strpos($word_l,'query')+6)));
	}else{
		$word_l = strstr($str,'query');
		$keyword = urldecode(substr($word_l,(strpos($word_l,'query')+8)));
	}
	return mb_convert_encoding($keyword, "utf-8", "GBK");
}

//直接从页面获取关键字
function get_keyword_from_html($str){
	$content = file_get_contents($str);
	preg_match('/<textarea[^>]*id="kw2"[^>]*>(.*?)<\/textarea>/si',$content,$m);
	$keyword = mb_convert_encoding($m[1], "utf-8", "auto,GBK");
	return $keyword;
}
