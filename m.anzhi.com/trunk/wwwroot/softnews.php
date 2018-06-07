<?php
require_once dirname(__FILE__) . '/init.php';
define('_APP_NAME', 'perfect');
define('_APP_SITE', 'm.anzhi.com');
define('_APP_SITE_PUBLIC', _APP_SITE.'/'._APP_NAME.'/public');
$tplObj->out['public_url'] = 'http://'._APP_SITE_PUBLIC;
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['url'] = 'http://'._APP_SITE;
$model = new GoModel();

if($_POST['act']=='get_maybe_like'){
    $res = get_maybe_like($_POST['package'],$_POST['sid']);
    echo $res;
    exit();
}

$id = $_GET['id'];
if (empty($id))
        exit;

$where = array(
    'id' => $id,
    'status' => 1,
	'passed' => 2,
);
$option = array(
    'where' => $where,
    'table' => 'sj_soft_content_explicit',
    'field' => '*',
);
$data = $model->findOne($option);
if (empty($data))
    exit;
$tplObj->out['id'] = $id;
$tplObj->out['start_tm'] = $data['start_tm'];
$tplObj->out['random'] = uniqid();
$tplObj->out['title'] = $data['title'];
$tplObj->out['sid'] = $_GET['sid'];

$img_url = getImageHost();
$video_cover = '/static/default_black.jpg';
$tplObj->out['video_cover'] = $video_cover;
if($data['template_select']==1 ||$data['template_select']==3){
    #查找顶部视频的相关信息
    $where = array('contentid'=>$data['id']);
    $option = array(
        'where' => $where,
        'table' => 'sj_soft_content_exp_video',
        // 'field' => '*',
    );
    $video_data = $model->findOne($option);
    if ($video_data){
        if($video_data['type']>=2 && !$video_data['coverpath']){
            $video_data['coverpath'] = $video_cover;
        }
        $tplObj->out['video_data'] = $video_data; 
    }
} 

if($data['template_select']==1){
    $az_style_content=json_decode($data['az_style_content'],true);
    if($id>=28&&$id<=169){
        foreach($az_style_content as $k=>$v){
            $az_style_content[$k]['article']=str_replace(array("\r\n","\r","\n\n","\n"), "</p><p>", $v['article']);
        }
    }
}elseif($data['template_select']==3){
    $az_style_content=add_quote_tag($data['az_style_content']);
}

$tplObj->out['az_style_content'] = $az_style_content;


$package = $data['package'];

$size = 10;

$tplObj->out['package'] = $package;
//获取softid
$where_cu = array(
    'package' => $package,
    'status' => 1,
    'hide' => 1,
);
$option_cu = array(
    'where' => $where_cu,
    'table' => 'sj_soft',
    'field' => 'package,softid',
);
$data_soft = $model->findOne($option_cu);

$note = get_table_data(array('package'=>$package),'sj_soft_note','package','package,in_short');
$tplObj->out['in_short'] = $note[$package]['in_short'];

$file_tb = get_table_data(array('softid'=>$data_soft['softid'],'package_status'=>1),'sj_soft_file','softid','iconurl_72,softid');
$tplObj->out['icon_url'] = $img_url.$file_tb[$data_soft['softid']]['iconurl_72'];

$server_name = load_config('m_website_domain');
$tplObj->out['share_url'] = $server_name.'/news/content_tf_'.$id.'.html?auto=1&share=1';

$tplObj->out['softid_cu'] = $data_soft['softid'];
$rand_visit = ($id+$data['create_tm'])%10;
if($rand_visit == 0) $rand_visit = 1;
$tplObj->out['img_url'] = $img_url;
$tplObj->out['release_tm'] = date(load_config('date_format'), $data['create_tm']);
$tplObj->out['visit_count'] = $data['visit_count'] + $rand_visit*10000;
$tplObj->out['template_select'] = $data['template_select'];

// 展示模版数据
$tplObj->display('soft_news_tpl.html');

function get_maybe_like($package,$sid){
    $param = array(
		'PACKAGE_NAME' => $package,
        'USE_BI' => true,
		'KEY' => 'MAYBE_LIKE',
		'VR' => 25
	);
    session_begin($sid);
	$res = gomarket_action('soft.GoGetSuggest',$param);
    file_put_contents('/tmp/softnews_like.log',date("Y-m-d H:i:s")."\r\n".$package."\r\n".json_encode($res)."\r\n",FILE_APPEND);
    $like_package = $softid = $softname = array();
    if($res){
        foreach($res['DATA_LIKE'] as $k=>$v){
            $like_package[] = $v[7];
            $softid[$v[7]] = $v[0];
            $softname[$v[7]] = $v[2];
        }
        file_put_contents('/tmp/softnews_like.log',json_encode($like_package)."\r\n",FILE_APPEND);
        $time = time();
        if(count($like_package)>0){
            $where = array(
                'package' => $like_package,
                'status' => 1,
                'passed' => 2,
                //'start_tm' => array('exp'," <= '{$time}'"),
                //'end_tm' => array('exp'," >= '{$time}'")
                
            );
            $content = get_table_data($where,'sj_soft_content_explicit','package','id,package,title,explicit_pic,create_tm','id asc');
            $return = array();
            $num = 0;
            foreach($like_package as $k=>$v){
                if($num >= 3) break; 
                if(isset($content[$v])){
                    $tmp_pic = json_decode($content[$v]['explicit_pic'],true);
                    $content[$v]['softid'] = $softid[$content[$v]['package']];
                    $content[$v]['softname'] = $softname[$content[$v]['package']];
                    $return[$k] = $content[$v];
                    $return[$k]['pic'] = $tmp_pic['pic0'];  
                    $num++;
                }               
            }
            $return = array_values($return);
            return json_encode($return);
        }
    }else{
        return '';
    }
}

function get_table_data($where,$table,$key,$field = '*',$order = ''){
	global $model;
	$option = array(
		'table' => $table,
		'where' => $where,
		'field' => $field,
	);
	if($order){
		$option['order'] = $order;
	}
	$list = $model->findAll($option);
	$return = array();
	foreach((array)$list as $k => $v){
		$return[$v[$key]] = $v;
	}
	unset($list);
	return $return;
}

#给引用段落加样式
function add_quote_tag($content){
    $content = preg_replace('/<blockquote\sstyle=(\'|")background-color:[^\'"]+\\1>(.*?)<\/blockquote>/', '<div class="quote"><blockquote>$2</blockquote></div>', $content);
    return $content;
}
