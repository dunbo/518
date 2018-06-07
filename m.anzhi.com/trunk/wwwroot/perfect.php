<?php
define('APP_NAME', 'wap');
include_once dirname(__FILE__).'/../newgomarket.goapk.com/init.php';
include_once dirname(__FILE__).'/functions.php';


define('_APP_ROOT', dirname(realpath(__FILE__)));
define('_APP_NAME', 'perfect');
define('_APP_SITE', 'm.anzhi.com');
define('_APP_SITE_PUBLIC', _APP_SITE.'/'._APP_NAME.'/public');
define('_APP_PAGESIZE', 20);

define('TEMPLATE_DIR', _APP_ROOT.'/'._APP_NAME.'/app/views');
define('TEMPLATE_C_DIR', _APP_ROOT.'/'._APP_NAME.'/app/cache/compile');


$tpl = GoTemplate::getInstance(TEMPLATE_DIR, TEMPLATE_C_DIR);
$tpl->display_exit = false;
$tpl->tpl->registerPlugin("block", "imgurltrans", "imgurl_trans");
$tpl->tpl->registerPlugin("block", "formatFileSize", "formatFileSize");
//$tpl->tpl->registerPlugin("block", "url2static_url", "url2static_url");

$tpl->out['public_url'] = 'http://'._APP_SITE_PUBLIC;
$tpl->out['image_url'] = $image_url = getImageHost();

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : null;
$method = isset($_GET['method']) && empty($_GET['method'])==false ? $_GET['method'] : 'index';

$model = new GoModel();

if(in_array($method, array('index', 'comment', 'write_log')))
{
	//
	// 处理安智精选网页信息的查询
	//		
	$option = array(
			'table' => 'sj_perfect_soft',
			'where' => array(
					'status' => 1,
					'id' => $id,
			),
			'field' => 'id,softid,package,softname,subject,comment_num,history_num,banner_path',
	);
	$perfect = $model->findOne($option);
	if(!$perfect)
		return;
	
	//
	//通过包名获得软件详细信息
	//
	$soft = gomarket_action('soft.GoGetSoftDetailPackage', array(
			'PACKAGE_NAME' => $perfect['package'],
			'VR' => 3,
			'EXTRA_OPTION_FIELD' => array(
					'A.category_id','A.category_name','A.hide','A.status','A.update_content', 'A.iconurl_72', 'min_firmware', 'max_firmware'
			),
	));
	
	$tpl->out['soft'] = $soft;
	$tpl->out['perfect'] = $perfect;
}

switch($method)
{
	case 'write_log':
		load_helper('utiltool');
		
		//from：0-未知；1-通过第三方浏览器；2-通过5.3以下版本客户端
		$from = isset($_GET['from']) && in_array($_GET['from'], array(1,2)) ? $_GET['from'] : 0;
		
		/* 2014.12.8 jiwei 按BI部门提供格式记录
		$log = array(	'ip' => $_SERVER['REMOTE_ADDR'],
						'user_agent' => $_SERVER['HTTP_USER_AGENT'],
						'perfect_soft_id' => $perfect['id'],
						'soft_id' => $soft['ID'],
						'soft_package' => $perfect['package'],
						'soft_version' => $soft['SOFT_VERSION'],
						'soft_version_code' => $soft['SOFT_VERSION_CODE'],
						'from' => $from,
						'download_tm' => time());
		*/
		$log = array(	'id'=>$soft['ID'],
						'package_name'=>$perfect['package'],
						'type'=>1,
						'ACTIVITY_ID'=>-9999,
						'IP'=>$_SERVER['REMOTE_ADDR'],
						'perfect_soft_id'=>$perfect['id']);
		
		$wlog = array('k'=>'REQ_DOWNLOAD', 'r'=>$log, 't'=>time());
		
		permanentlog('anzhi_perfect_soft_download.log', json_encode($wlog));
		break;
		
	case 'comment':
		$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 0;
		$offset = $page * _APP_PAGESIZE;
		$comments = gomarket_action('comment.GoGetCommentList',array("ID" => $soft['ID'], 'GET_COUNT' => false, "LIST_INDEX_START" => $offset, "LIST_INDEX_SIZE" => _APP_PAGESIZE, 'VR' => 1));
		
		//
		// 判断是否还有下一页
		//
		$has_more = false;
		if(is_array($comments['DATA']))
		{
			if(_APP_PAGESIZE == count($comments['DATA']))
			{
				$next_comment = gomarket_action('comment.GoGetCommentList',array("ID" => $soft['ID'], 'GET_COUNT' => false, "LIST_INDEX_START" => $offset+_APP_PAGESIZE, "LIST_INDEX_SIZE" => 1, 'VR' => 1));
				if(is_array($next_comment['DATA']))
				{
					if(count($next_comment['DATA'])>0)
						$has_more = true;
				}
			}
		}
		$comments['has_more'] = $has_more;
		
		
		if($_GET['ajax']==1)
		{
			echo json_encode($comments);
		}
		else 
		{
			$tpl->out['perfect'] = $perfect;
			$tpl->out['comments'] = $comments;
			$tpl->display('comment.html');
		}
		break;
		
	case 'history':
		$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 0;
		$offset = $page * _APP_PAGESIZE;
		
		$history = gomarket_action('perfect.GoGetPerfect', array("LIST_INDEX_START" => $offset, "LIST_INDEX_SIZE" => _APP_PAGESIZE));
		
		//
		// 判断是否还有下一页
		//
		$has_more = false;
		if(is_array($history['DATA']))
		{
			if(_APP_PAGESIZE == count($history['DATA']))
			{
				$next_history = gomarket_action('perfect.GoGetPerfect', array("LIST_INDEX_START" => $offset+_APP_PAGESIZE, "LIST_INDEX_SIZE" => 1));
				if(is_array($next_history['DATA']))
				{
					if(count($next_history['DATA'])>0)
						$has_more = true;
				}
			}
		}
		$history['has_more'] = $has_more;
		
		
		if($_GET['ajax']==1)
		{
			foreach($history['DATA'] as $key=>$val)
			{
				$val[5] = imgurl_trans('', $val[5]);
				$val[2] = date(load_config('date_format'), $val[2]);
				$val[101] = "perfect.php?id={$val[0]}";
				$val[102] = url2static_url($val[101]);
				
				$history['DATA'][$key] = $val;
			}
			echo json_encode($history);
		}
		else
		{
			$tpl->out['perfect'] = $perfect;
			$tpl->out['history'] = $history;
			$tpl->display('history.html');
		}
		break;
	
	case 'index':
	default:
		//
		// 查询内容，此处修改要走缓存
		//
		$option = array(
				'table' => 'sj_perfect_soft_content',
				'where' => array(
						'perfect_soft_id' => $id,
				),
				'field' => 'content',
		);
		$content = $model->findOne($option);
		$content = str_replace('http://118.26.224.18/cmcc', $image_url, $content['content']);
		
		$content = str_replace('&lt;', '<', $content);
		$content = str_replace('&gt;', '>', $content);
		$content = str_replace('&amp;', '&', $content);
		$content = str_replace('&quot;', '"', $content);
	
		// val.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&amp;/g, '&');
		$path_preg="/<embed.+?src=\"<!--{ANZHI_IMAGE_HOST}-->(.*?)\".+?imgurl=\"(.*?)\".+?\/>/u";
		
		if(strpos($_SERVER['SERVER_ADDR'],'192.168.0')!==FALSE||$_SERVER['SERVER_ADDR']=='127.0.0.1'||$_SERVER['SERVER_ADDR']=='124.243.198.97'){
			$new_path_preg='<video id="my-video" class="video-js vjs-big-play-centered" controls="" width="320px" height="240px" style="margin:0 auto;" preload="${3}" data-setup="{}" poster="${2}" ><source src="http://m.test.anzhi.com/cmcc${1}" type="video/mp4"></source></video>';
		}else{
			$new_path_preg='<video id="my-video" class="video-js vjs-big-play-centered" controls="" width="320px" height="240px" style="margin:0 auto;" preload="${3}" data-setup="{}" poster="${2}" ><source src="http://v.cdn.anzhi.com${1}" type="video/mp4"></source></video>';
		}
		
		$content=preg_replace($path_preg, $new_path_preg, $content);
		$content = str_replace('<!--{ANZHI_IMAGE_HOST}-->', $image_url, $content);
		// var_dump($content);die;
		$path_preg="/<iframe.+?src=\"(.*?)\".+?>/u";
		$new_path_preg='<iframe frameborder="0" width="100%" height="100%"  src="${1}" allowfullscreen="">';
		$content=preg_replace($path_preg, $new_path_preg, $content);
		if(strstr($content,'</video>')){
			$tpl->out['show_video'] = 1;
		}

		$tpl->out['content'] = $content;
		
		//
		// 处理评论内容的查询
		//
		if($perfect['comment_num']>0)
		{
			$comments = gomarket_action('comment.GoGetCommentList',array("ID" => $soft['ID'], 'GET_COUNT' => false, "LIST_INDEX_START" => 0, "LIST_INDEX_SIZE" => $perfect['comment_num'], 'VR' => 1));
			$tpl->out['comments'] = $comments['DATA'];
		}
		
		//
		// 通过接口查询往期内容
		//
		if($perfect['history_num']>0)
		{
			$history = gomarket_action('perfect.GoGetPerfect', array("ID"=>$perfect['id'], "LIST_INDEX_START" => 0, "LIST_INDEX_SIZE" => $perfect['history_num']));
			$tpl->out['history'] = $history['DATA'];
		}
		
		//
		// 查询往期总数,此处要修改为缓存
		//
		$perfect_count = 0;
		$option = array(
				'table' => 'sj_perfect_soft_content',
				'field' => 'count(*) AS count',
		);
		$count_result = $model->findOne($option);
		$perfect_count = $count_result['count'];
		$tpl->out['perfect_count'] = $perfect_count;
		
		$tpl->display('index.html');
}