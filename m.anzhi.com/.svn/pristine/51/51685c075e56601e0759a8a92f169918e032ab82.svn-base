<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
session_begin();
$feature_id = $_GET['id'];
$version_code=$_GET['version_code'];
$share = $_GET['share'];
$sid = $_GET['sid'];
$opts = array(
	'table' => 'sj_feature',
	//'cache_time' => 3603,
	'where' => array(
		'feature_id' => $feature_id,
	),
);
$feature_main = $model->findOne($opts);
if($feature_main['feature_page_type']==1)//H5页面
{
	$url_param='?id='.$feature_id.'&version_code='.$version_code.'&from=1';
	if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
	{
		//$feature_url = 'http://118.26.203.23/lottery/feature_new.php';
		$feature_url = 'http://118.26.203.23/f_'.$feature_id.'.html';
	}
	elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
	{
		//$feature_url ='http://9.m.anzhi.com/lottery/feature_new.php';
		$feature_url = 'http://9.m.anzhi.com/f_'.$feature_id.'.html';
	}
	else 
	{
		//$feature_url ='http://fx.anzhi.com/lottery/feature_new.php';
		$feature_url = 'http://fx.anzhi.com/f_'.$feature_id.'.html';
	}
	//$pre_url = $feature_url.$url_param;
	$pre_url = $feature_url;
	$graphic_opts = array(
		'table' => 'sj_feature_graphic',
		//'cache_time' => 3603,
		'where' => array(
			'feature_id' => $feature_id,
			'status'=>1,
		),
	);
	$feature = $model->findAll($graphic_opts);
	
	//$graphic_image  = get_table_data($where,"sj_graphic_image","id","*");
	$graphic_image_where = array(
		'table' => 'sj_graphic_image',
		'where' => array(
			'from_id' => $feature_id,
		),
	);
	$graphic_image_list = $model->findAll($graphic_image_where);
	$graphic_image = array();
	foreach((array)$graphic_image_list as $ke => $va){
		$graphic_image[$va['id']] = $va;
	}
	//$graphic_image  = get_table_data($where,"sj_graphic_image","id","*");	
	//$this->assign('graphic_image', $graphic_image);		
	//$section = array('','一','二','三','四','五','六','七','八','九','十');
	$pattern = "#\[img\]([\d]+)\[/img\]#" ;
	//在这之前先遍历一遍feature 修改key值
	foreach($feature as $key =>$val)
	{
		$feature[$val['id']] = $val;
		unset($feature[$key]);
	}
	foreach($feature as $k => $v){
		//$feature[$k]['title'] = "图文第".$section[$v['rank']]."段";
		preg_match_all($pattern, $v['content'],$matches);
		$new_k = array();
		foreach($matches[1] as $vv){
			$new_k['[img]'.$vv.'[/img]'] = "<img src='".  getImageHost() . $graphic_image[$vv]['imgurl']."'/>";
		}
		$content = strtr($v['content'],$new_k);
		$feature[$k]['content'] =  $content;
		$is_header=$v['is_header'];
		$header_content_arr = json_decode($v['header_content'],true);
		//段落头图内容展示
		if($header_content_arr)
		{
			$header_count=count($header_content_arr)/2;
			$new_header_arr=array();
			for($i=1;$i<=$header_count;$i++)
			{
				$new_header_arr[$i]['img']=$header_content_arr['img'.$i];
				$new_header_arr[$i]['common_jump_id']=$header_content_arr['common_jump_id'.$i];
				$common_where = array(
					'table' => 'sj_common_jump',
					'where' => array(
						'id'=>$header_content_arr['common_jump_id'.$i],
						'status'=>1,
					),
				);
				$common_detail = $model->findOne($common_where);
				$new_header_arr[$i]['common_jump_detail']=$common_detail;
			}
			if($v['header_type']==1)
			{
				$feature[$k]['header_content_arr1'] =  $new_header_arr;
			}
			elseif($v['header_type']==2)
			{
				$feature[$k]['header_content_arr2'] =  $new_header_arr;
			}
			elseif($v['header_type']==3)
			{
				$feature[$k]['header_content_arr3'] =  $new_header_arr;
			}
			elseif($v['header_type']==4)
			{
				$feature[$k]['header_content_arr4'] =  $new_header_arr;
			}
		}
		//段落推荐图片展示
		$recommend_content_arr = json_decode($v['recommend_content'],true);
		if($recommend_content_arr)
		{
			$recommend_count=count($recommend_content_arr)/2;
			$new_recommend_arr=array();
			for($i=1;$i<=$recommend_count;$i++)
			{
				$new_recommend_arr[$i]['img']=$recommend_content_arr['recommend_img'.$i];
				$new_recommend_arr[$i]['common_jump_id']=$recommend_content_arr['recommend_common_jump_id'.$i];
				$recommend_common_where = array(
					'table' => 'sj_common_jump',
					'where' => array(
						'id'=>$recommend_content_arr['recommend_common_jump_id'.$i],
						'status'=>1,
					),
				);
				$recommend_common_detail = $model->findOne($recommend_common_where);
				$new_recommend_arr[$i]['common_jump_detail']=$recommend_common_detail;
			}
			if($v['recommend_type']==1)
			{
				$feature[$k]['recommend_content_arr1'] =  $new_recommend_arr;
			}
			elseif($v['recommend_type']==2)
			{
				$feature[$k]['recommend_content_arr2'] =  $new_recommend_arr;
			}
			elseif($v['recommend_type']==3)
			{
				$feature[$k]['recommend_content_arr3'] =  $new_recommend_arr;
			}
			elseif($v['recommend_type']==4)
			{
				$feature[$k]['recommend_content_arr4'] =  $new_recommend_arr;
			}
		}
		//段落标题展示
		$title_content_arr = json_decode($v['title_content'],true);
		if($title_content_arr)
		{
			$title_type=$v['title_type'];
			$new_title_content_arr=array();
			if($title_type==1)//文本
			{
				$feature[$k]['title_content'] =  $title_content_arr['title_content'];
				$feature[$k]['title_font_size'] =  $title_content_arr['font_size'];
				$feature[$k]['title_font_color'] =  $title_content_arr['font_color'];
				$feature[$k]['title_img'] ="";
			}
			else//图片
			{
				$feature[$k]['title_img'] =  $title_content_arr['img'];
				$feature[$k]['title_content']="";
			}
			$title_common_where = array(
				'table' => 'sj_common_jump',
				'where' => array(
					'id'=>$title_content_arr['common_jump_id'],
					'status'=>1,
				),
			);
			$title_common_detail = $model->findOne($title_common_where);
			$feature[$k]['title_common_jump_id'] =  $title_content_arr['common_jump_id'];
			$feature[$k]['title_common_jump_detail']=$title_common_detail;
		}
		$title_bg_content = $v['title_bg_content'];
		if($title_bg_content)
		{
			$feature[$k]['title_bg_content']=$title_bg_content;
		}
		//段落背景
		$bg_content = $v['bg_content'];
		if($bg_content)
		{
			$feature[$k]['bg_content']=$bg_content;
		}
		//段落标签
		$label_type=$v['label_type'];
		if($label_type==4)//文本
		{
			$label_content_arr = json_decode($v['label_content'],true);
			$feature[$k]['label_type_text'] =  $label_content_arr['label_type_text'];
			$feature[$k]['label_type_text_size'] =  $label_content_arr['label_type_text_size'];
			$feature[$k]['label_type_text_color'] =  $label_content_arr['label_type_text_color'];
			$feature[$k]['label_type_img']="";
			$feature[$k]['label_pre_words'] ="";
		}
		elseif($label_type==5)//图片
		{
			$feature[$k]['label_type_img'] = $v['label_content'];
			$feature[$k]['label_type_text'] ="";
			$feature[$k]['label_pre_words'] ="";
		}
		else
		{
			$feature[$k]['label_pre_words'] = $v['label_content'];
			$feature[$k]['label_type_text'] ="";
			$feature[$k]['label_type_img'] = "";
		}
		
		$label_one_download_content_arr = json_decode($v['label_one_download_content'],true);
		if($label_one_download_content_arr)
		{
			$is_one_download=$v['is_one_download'];
			if($is_one_download==1)//有
			{
				$feature[$k]['label_one_download_frame_color'] =  $label_one_download_content_arr['one_download_frame_color'];
				$feature[$k]['label_one_download_button_color'] =  $label_one_download_content_arr['one_download_button_color'];
				$feature[$k]['label_one_download_font_color'] =  $label_one_download_content_arr['one_download_font_color'];
			}
		}
		//段落软件
		//下载按钮
		$soft_download_content=$v['soft_download_content'];
		$soft_download_content_arr = json_decode($v['soft_download_content'],true);
		$feature[$k]['soft_download_frame_color'] =  $soft_download_content_arr['download_frame_color'];
		$feature[$k]['soft_download_button_color'] =  $soft_download_content_arr['download_button_color'];
		$feature[$k]['soft_download_font_color'] =  $soft_download_content_arr['download_font_color'];
		//软件标签内容配置
		$feature[$k]['soft_label_pre_words'] =  $v['soft_label_pre_words'];
		//软件推荐信息 
		$soft_recommend_info_content_arr = json_decode($v['soft_recommend_info_content'],true);
		$feature[$k]['soft_recommend_info'] =  $soft_recommend_info_content_arr['soft_recommend_info'];
		$feature[$k]['soft_recommend_info_color'] =  $soft_recommend_info_content_arr['soft_recommend_info_color'];
		//软件推荐人群 
		$soft_recommend_people_content_arr = json_decode($v['soft_recommend_people_content'],true);
		$feature[$k]['soft_recommend_people'] =  $soft_recommend_people_content_arr['soft_recommend_people'];
		$feature[$k]['soft_recommend_people_color'] =  $soft_recommend_people_content_arr['soft_recommend_people_color'];
		//软件标题 
		$soft_title_content_arr = json_decode($v['soft_title_content'],true);
		$feature[$k]['soft_title'] =  $soft_title_content_arr['soft_title'];
		$feature[$k]['soft_title_size'] =  $soft_title_content_arr['soft_title_size'];
		$feature[$k]['soft_title_color'] =  $soft_title_content_arr['soft_title_color'];
		//软件摘要 
		$soft_abstract_content_arr = json_decode($v['soft_abstract_content'],true);
		$feature[$k]['soft_abstract'] =  $soft_abstract_content_arr['soft_abstract'];
		$feature[$k]['soft_abstract_size'] =  $soft_abstract_content_arr['soft_abstract_size'];
		$feature[$k]['soft_abstract_color'] =  $soft_abstract_content_arr['soft_abstract_color'];
		//软件背景 特殊样式1 是三个背景 特殊样式2是一个个背景
		if($v['soft_type']==4)//特殊样式1 
		{
			$soft_all_bg_arr = json_decode($v['soft_all_bg'],true);
			$feature[$k]['soft1_bg_color'] =  $soft_all_bg_arr['soft1_bg_color'];
			$feature[$k]['soft2_bg_color'] =  $soft_all_bg_arr['soft2_bg_color'];
			$feature[$k]['soft3_bg_color'] =  $soft_all_bg_arr['soft3_bg_color'];
		}	
	}
	$tplObj -> out['imgurl'] = getImageHost();
	$tplObj->out['feature'] = $feature;
	$tplObj->out['feature_main'] = $feature_main;
	$tplObj -> out['feature_id'] = $feature_id;
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['version_code'] = $version_code;
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['pre_url'] = $pre_url;
	$tplObj -> out['share'] = $share;
	$tplObj -> out['from'] = $_GET['from'];
	$tplObj -> out['share_common_jump_id'] = $share_common_jump_id;
	
	//写日志
	if($feature_id)
	{
		if($_SESSION['USER_IMSI']){
			$imsi = $_SESSION['USER_IMSI'];
		}
		$log_data = array(
			'imsi' => $imsi,
			'feature_id'=>$feature_id,
			'device_id' => $_SESSION['DEVICEID'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'time' => time(),
			'key'  => "index",
		);
		permanentlog('new_feature_'.$feature_id.'.log', json_encode($log_data));
	}
	$tplObj->display("feature_new.html");
}
?>
