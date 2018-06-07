<?php
//安智市场内容合作管理
class ContentcooperationAction extends CommonAction{
	//渠道列表
	Public function channel_list(){
		$model = D('Cooperate.Contentcooperation');
		$where = array( 'del'=> 0 );
		if(!$_GET['site_id']) $_GET['site_id'] =1;
		$site_id_type=$model -> table('coop_site')->where(array('id'=>$_GET['site_id']))->find();
		$this -> assign('site_id_type',$site_id_type['type']);
		$this->check_where($where, 'site_id');
		list($list,$total,$Page,$count_arr) = $model->get_channel_list($where);
		// 内容类型,1:软件,2:活动,3:专题,4:页面,5:网页,6:礼包,7:攻略,
		// $type_name=array(
		// 	'2'=>'红包助手',
		// 	'4'=>'手机清理',
		// );
		$type_name_2=array(
			'4'=>'页面内容',
			'5'=>'网页内容',
			'2'=>'活动内容',
			'3'=>'专题内容',
		);
		foreach($list as $k=>$v){
			if($v['common_jump_id']){
				$common_jump_type=$model -> table('sj_common_jump')->where(array('id'=>$v['common_jump_id']))->find();
				$list[$k]['type_name']=$type_name_2[$common_jump_type['content_type']];
			}
		}
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$list);		
		$this -> assign('count_arr',$count_arr);		
		$where = array(
			'config_type' => 'coop_table_name',
			'status' => 1,
		);
		$config = $model -> table('pu_config') -> where($where)->field('configcontent')->find();
		$this -> assign('configcontent',$config['configcontent']);		
		$coop_site = $model->table("coop_site")->field('id,anzhi_name')->select(); 
		$this -> assign('coop_site',$coop_site);			
		$this -> display();
	}	
	//添加频道
	public function add_channel(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);				
			if(!$_POST['site_id']){
				$this->error("请城选择站点");
			}
			if($_POST['site_id']==1)
			{
				$type = $_POST['type'];
				if($type==2)
				{
					$where = array(
						'del' => 0,
						'site_id' => 1,
						'type' => 2,
					);
					$coop_result = $model->table('coop_channel')->where($where)->find();
					if($coop_result)
					{
						$this->error("该站点下已经有红包助手频道！");
					}
				}
				if($type==4)
				{
					$where = array(
						'del' => 0,
						'site_id' => 1,
						'type' => 4,
						'id' => array('neq',$_POST['id']),
					);
					$coop_result = $model->table('coop_channel')->where($where)->find();
					if($coop_result)
					{
						$this->error("该站点下已经有手机清理频道！");
					}
				}
			}
			if($_POST['type']==3){
				//推荐内容处理 合并
				$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				if($rcontent!==true)
				{
					$this -> error($rcontent);
				}
				// var_dump($map);
				$common_jump_id=$model-> table('sj_common_jump')->add($map);
				// echo $model->getLastSql();die;
				if(!$common_jump_id){
					$this -> error("添加失败");
				}
				$_POST['common_jump_id']=$common_jump_id;
			}else{
				$_POST['common_jump_id']='';
			}
			

			if(!(is_numeric($_POST['rank'])&&$_POST['rank']==(int)$_POST['rank']) || $_POST['rank'] <=0){
				$this->error("排序值请填写正整数");
			}	
			$strlen = mb_strlen($_POST['channel_name'],'utf-8');
			if($strlen <2 || $strlen >5){
				$this->error("频道名称：2~5个汉字");
			}
			
			if($_POST['status'] == 1  && $_POST['type'] != 3){	
				$this->error("启用：请先关联卡片");
			}			
			list($res,$log) = $model-> save_channel();		
			if($res){
				$this->writelog("内容合作管理--添加了id为{$res}【频道管理】",'coop_channel',$res,__ACTION__ ,'','add');
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}
		}else{
			$this -> assign('site_id',$_GET['site_id']);
			$site_id_type=$model -> table('coop_site')->where(array('id'=>$_GET['site_id']))->find();
			$this -> assign('site_id_type',$site_id_type['type']);
			$this -> assign('common_jump_id',0);
			if($site_id_type['type']==0 || $site_id_type['type']==3 || $site_id_type['type']==4 || $site_id_type['type']==5){
				$this -> assign('type',3);		
			}
			$this -> display();
		}

	}
	//编辑频道
	public function save_channel(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
			//
			if(!(is_numeric($_POST['rank'])&&$_POST['rank']==(int)$_POST['rank']) || $_POST['rank'] <=0){
				$this->error("排序值请填写正整数");
			}				
			if(!$_POST['site_id']){
				$this->error("请城选择站点");
			}
			if($_POST['type']==3){
				$list = $model -> get_channel_find($_POST['id']);
				//推荐内容处理 合并
				$rcontent=ContentTypeModel::saveRecommendContent($_POST,'',$map);
				if($rcontent!==true)
				{
					$this -> error($rcontent);
				}
				if($list['common_jump_id']){
					$common_jump_id=$model-> table('sj_common_jump')->where(array('id'=>$list['common_jump_id']))->save($map);
					$_POST['common_jump_id']=$list['common_jump_id'];
				}else{
					$common_jump_id=$model-> table('sj_common_jump')->add($map);
					// echo $model->getLastSql();die;
					if(!$common_jump_id){
						$this -> error("添加失败");
					}
					$_POST['common_jump_id']=$common_jump_id;
				}		
				
			}else{
				$_POST['common_jump_id']='';
			}
			

			$strlen = mb_strlen($_POST['channel_name'],'utf-8');
			if($strlen <2 || $strlen >5){
				$this->error("频道名称：2~5个汉字");
			}	
			if($_POST['status'] == 1 && $_POST['type'] != 3){	
				$where = array(
					'channle_id' => $_POST['id'],
					'status' => 1
				);
				list($list,$total,$Page) = $model -> get_card_list($where);
				if($total == 0){
					$this->error("启用：请先关联卡片");
				}
			}
			if($_POST['site_id']==1)
			{
				$type = $_POST['type'];
				if($type==2)
				{
					$where = array(
						'del' => 0,
						'site_id' => 1,
						'type' => 2,
						'id' => array('neq',$_POST['id']),
					);
					$coop_result = $model->table('coop_channel')->where($where)->find();
					if($coop_result)
					{
						$this->error("该站点下已经有红包助手频道！");
					}
				}
				$type = $_POST['type'];
				if($type==4)
				{
					$where = array(
						'del' => 0,
						'site_id' => 1,
						'type' => 4,
						'id' => array('neq',$_POST['id']),
					);
					$coop_result = $model->table('coop_channel')->where($where)->find();
					if($coop_result)
					{
						$this->error("该站点下已经有手机清理频道！");
					}
				}
			}
			list($res,$log) = $model-> save_channel($this);		
			if($res){
				$this->writelog("内容合作管理--编辑了id为{$_POST['id']}{$log}【频道管理】",'coop_channel',$_POST['id'],__ACTION__ ,'','edit');
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}		
		}else{
			$id = $_GET['id'];
			$list = $model -> get_channel_find($id);
			if($list['common_jump_id']){
				$find=$model -> table('sj_common_jump')->where(array('id'=>$list['common_jump_id']))->find();
				$this -> assign('common_jump_id',$find['id']);	
			}else{
				$this -> assign('common_jump_id',0);	
			}

			 // var_dump($list);die;
			// 给个默认值，要不然javascript那边可能会出错
            $find['general_page_type'] = 0;
            $find['page_name'] = "";
            // 获得活动名称，专题名称，页面名称
            $content_type = $find['content_type'];
            if ($content_type == 2) {
                // 活动名称
                $find['activity_name'] = ContentTypeModel::convertActivityId2ActivityName($find['activity_id']);
            } else if ($content_type == 3) {
                // 专题名称
                $find['feature_name'] = ContentTypeModel::convertFeatureId2FeatureName($find['feature_id']);
            } else if ($content_type == 4) {
                // 页面类型
                $find['general_page_type'] = ContentTypeModel::getGeneralPageType($find['page_type']);
                // 页面名称
                $find['page_name'] = ContentTypeModel::convertPageType2PageName($find['page_type']);
            }
            // var_dump($find);die;
			$this -> assign('list',$list);	
			$this -> assign('type',$list['type']);	
			$this -> assign('find',$find);	
			$chl_list = $model -> get_cid_arr($list['cid']);
			$this -> assign('chl_list',$chl_list);	
			$site_id_type=$model -> table('coop_site')->where(array('id'=>$_GET['site_id']))->find();
			$this -> assign('site_id_type',$site_id_type['type']);
			$this -> assign('site_id',$_GET['site_id']);

			$this -> display("add_channel");
		}
	}
	//启用、停用频道
	public function switch_channel(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
		$map = array(
			'update_time' => time()
		);
		if($_GET['status'] == 1){	
			$where = array(
				'channle_id' => $_GET['id'],
				'status' => 1
			);
			list($list,$total,$Page) = $model -> get_card_list($where);
			if(($_GET['type']==1 || $_GET['type']==2 || $_GET['type']==4) && $total == 0){
				$this->error("启用：请先关联卡片");
			}
			$map['status'] = 1;
			$log = "启用";
		}else{
			$where = array(
				'contact_id' => $_GET['id'],
				'status' => 1,
				'contact_type' => 1,
			);
			list($list,$total,$Page) = $model -> get_card_list($where);
			//var_dump($list);
			if(($_GET['type']==1 || $_GET['type']==2 || $_GET['type']==4) && $total > 0){
				$contact_site_id = array();
				foreach($list as $v){
					if($v['contact_site_id']) $contact_site_id[] = $v['contact_site_id'];
				}
				$where = array(
					'id' => array('in',$contact_site_id)
				);
				$coop_site = get_table_data($where,"coop_site","id","id,anzhi_name");	
				$error = "该频道有导向的卡片:\n";
				foreach($list as $v){
					$error .= "导向站点：".$coop_site[$v['contact_site_id']]['anzhi_name']."==>"."频道：".$_GET['channel_name']."==>"."卡片名称：".$v['card_name']."\n";
				}
				$error .= "不能停用";
				$this->error($error);
			}			
			$map['status'] = 0;
			$log = "停用";
		}
		$where = array(
			'id' => $_GET['id'],
			'del' => 0
		);
		$affect = $model->table("coop_channel") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--{$log}了id为{$_GET['id']}【频道管理】",'coop_channel',$_GET['id'],__ACTION__ ,'','edit');
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}	
	}
	//删除频道
	public function del_channel(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
		$where = array(
			'channle_id' => $_GET['id'],
			'status' => 1
		);
		list($list,$total,$Page) = $model -> get_card_list($where);
		if($total > 0){
			$this->error("该频道下有{$total}个关联的卡片，请移除卡片后才可删除频道");
		}		
		$where = array(
			'id' => $_GET['id'],
			'del' => 0
		);
		$map = array(
			'del' => 1,
			'update_time' => time()
		);
		$affect = $model->table("coop_channel") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--删除了id为{$_GET['id']}【频道管理】",'coop_channel',$_GET['id'],__ACTION__ ,'','del');
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}		
	}
	//主频道名称
	public function save_meun_name(){
		if(!$_GET['configcontent']){
			exit(json_encode(array('code'=>'0','msg'=>"请填写名称")));
		}
		$strlen = mb_strlen($_GET['configcontent'],'utf-8');
		if($strlen >2){
			exit(json_encode(array('code'=>'0','msg'=>"频道名称最多2个汉字")));
		}			
		$model = D('Cooperate.Contentcooperation');
		list($res,$log) = $model -> save_meun_name($this);
		if($res){
			$this->writelog("内容合作管理--编辑了config_type为coop_table_name内空{$log}【频道管理】",'pu_config',$res,__ACTION__ ,'','edit');
		}
		exit(json_encode(array('code'=>'1','msg'=>"操作成功")));
	}
	//投放条件
	public function pub_delivery_conditions(){
		$model = D('Cooperate.Contentcooperation');
		$id = $_GET['id'];
		if($_GET['show_splash_screen']){
			$list = $model->table("coop_splash_screen")->where(array('id'=>$id))->find(); 
		}else{
			$list = $model -> get_channel_find($id);
		}
		$chl_list = $model -> get_cid_arr($list['cid']);
		$this -> assign('chl_list',$chl_list);			
		$this -> display("delivery_conditions");
	}
	//卡片管理
	public function card_list(){
		$model = D('Cooperate.Contentcooperation');
		$where = array('del' => 0);		
		if(!$_GET['site_id']) $_GET['site_id'] =1;
		if(!$_GET['channle_id']) $_GET['channle_id'] =1;
		$this->check_where($where, 'site_id');
		$this->check_where($where, 'channle_id');
		list($list,$total,$Page) = $model -> get_card_list($where);
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$list);	
		$channel_list = $model ->  get_channel_arr();	
		$this -> assign('channel_list',$channel_list);	
		$card_type_conf = $model -> get_card_type_conf();	
		$this -> assign('card_type_conf',$card_type_conf);
		
		$coop_site = get_table_data('',"coop_site","id","id,anzhi_name");	
		$this -> assign('coop_site',$coop_site);	
		$site_tag = get_table_data('',"coop_site_tag","id","id,tag_anzhi_name");	
		$this -> assign('site_tag',$site_tag);			
		$this -> display();
	}
	//创建卡片
	public function add_card(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);			
			if(!$_POST['site_id'] || !$_POST['channle_id']){
				$this->error("请城选择站点或频道");
			}		
			if(!is_numeric($_POST['rank']) || $_POST['rank'] <=0){
				$this->error("排序值请填写整数");
			}				
			$strlen = mb_strlen($_POST['card_name'],'utf-8');
			if($strlen <2 || $strlen >20){
				$this->error("卡片名称：2~20个汉字");
			}	
			if(!$_POST['content_num'] || $_POST['content_num'] <0 || !is_numeric($_POST['content_num'])){
				$this->error("请填写内容行数,必须为整数");
			}
			//视频类只能录入1+3*X整数
			if(($_POST['card_type'] == 3 || $_POST['card_type'] == 4) && $_POST['content_num']%3 != 1){
				$this->error("视频类只能录入1+3*X整数");
			}	
			//如果是标签（导向页面站点和内容站点必须一至）	
			if($_POST['contact_type'] == 2 && $_POST['contact_site_id'] != $_POST['content_site_id']){
				$this->error("选择标签导向页面站点和内容站点必须一至");
			}
			if(!$_POST['content_tags']){
				$this->error("请选择关联标签");
			}
			list($res,$log) = $model ->  save_card();
			if($res){
				$this->writelog("内容合作管理--添加了id为{$res}【卡片管理】",'coop_card',$res,__ACTION__ ,'','add');
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}	
		}else{			
			//卡片类型
			$card_type_conf = $model -> get_card_type_conf();	
			$this -> assign('card_type_conf',$card_type_conf);					
			$this -> assign('site_id',$_GET['site_id']);			
			$this -> assign('channle_id',$_GET['channle_id']);	
			$zhiyoo_site_id=C('zhiyoo_site_id');
			if($_GET['site_id']==$zhiyoo_site_id){
				$where=array('id'=>$zhiyoo_site_id);
			}
			// else{
			// 	$where=array('id'=>array('neq',$zhiyoo_site_id));
			// }	
			$coop_site = $model->table("coop_site")->where($where)->field('id,anzhi_name')->select(); 
			$this -> assign('coop_site',$coop_site);			
			$this -> assign('zhiyoo_site_id',$zhiyoo_site_id);			
			$this -> display();
		}
	}
	//编辑卡片
	public function save_card(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);		
			if(!$_POST['site_id'] || !$_POST['channle_id']){
				$this->error("请城选择站点或频道");
			}	
			if(!is_numeric($_POST['rank']) || $_POST['rank'] <=0){
				$this->error("排序值请填写整数");
			}				
			$strlen = mb_strlen($_POST['card_name'],'utf-8');
			if($strlen <2 || $strlen >20){
				$this->error("卡片名称：2~20个汉字");
			}	
			if(!$_POST['content_num'] || $_POST['content_num'] <0 || !is_numeric($_POST['content_num'])){
				$this->error("请填写内容行数,必须为整数");
			}		
			//视频类只能录入1+3*X整数
			if($_POST['card_type'] == 3 && $_POST['content_num']%3 != 1){
				$this->error("视频类只能录入1+3*X整数");
			}				
			//如果是标签（导向页面站点和内容站点必须一至）	
			if($_POST['contact_type'] == 2 && $_POST['contact_site_id'] != $_POST['content_site_id']){
				$this->error("选择标签导向页面站点和内容站点必须一至");
			}		
			if(!$_POST['content_tags']){
				$this->error("请选择关联标签");
			}			
			list($res,$log) = $model -> save_card($this);
			if($res){
				$this->writelog("内容合作管理--编辑了id为{$_POST['id']}{$log}【卡片管理】",'coop_card',$_POST['id'],__ACTION__ ,'','edit');
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}			
		}else{			
			//卡片类型
			$card_type_conf = $model -> get_card_type_conf();	
			$this -> assign('card_type_conf',$card_type_conf);		
			$id = $_GET['id'];
			$list = $model -> get_card_find($id);
			$this -> assign('list',$list);	
			$this -> assign('tag_arr',$list['content_tags']);
			$zhiyoo_site_id=C('zhiyoo_site_id');
			if($_GET['site_id']==$zhiyoo_site_id){
				$where=array('id'=>$zhiyoo_site_id);
			}
			// else{
			// 	$where=array('id'=>array('neq',$zhiyoo_site_id));
			// }	
			$coop_site = $model->table("coop_site")->where($where)->field('id,anzhi_name')->select(); 
			$this -> assign('coop_site',$coop_site);	
			$this -> assign('site_id',$_GET['site_id']);			
			$this -> assign('channle_id',$_GET['channle_id']);	
			$this -> assign('zhiyoo_site_id',$zhiyoo_site_id);						
			$this -> display("add_card");
		}
	}
	//启用、停用卡片
	public function switch_card(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
		$map = array(
			'update_tm' => time()
		);
		if($_GET['status'] == 1){	
			$channle = $model -> get_channel_find($_GET['channle_id']);
			//if($channle['status'] == 0){
				//$this->error("该卡片管理频道处于未启用状态，前端将无法看见该卡片");
			//}				
			$map['status'] = 1;
			$log = "启用";
		}else{		
			$map['status'] = 0;
			$log = "停用";
		}
		$where = array(
			'id' => $_GET['id'],
		);
		$affect = $model->table("coop_card") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--{$log}了id为{$_GET['id']}【卡片管理】",'coop_card',$_GET['id'],__ACTION__ ,'','edit');
			if($_GET['status'] == 1 && $channle['status'] == 0){
				$this->success("该卡片管理频道处于未启用状态，前端将无法看见该卡片<br/>操作成功");
			}else{
				$this->success("操作成功");
			}
		}else{
			$this->error("操作失败");
		}			
	}
	//删除卡片
	public function del_card(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);		
		$where = array(
			'id' => $_GET['id'],
			'del' => 0
		);
		$map = array(
			'del' => 1,
			'status' => 0,
			'update_tm' => time()
		);
		$affect = $model->table("coop_card") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--删除了id为{$_GET['id']}【卡片管理】",'coop_card',$_GET['id'],__ACTION__ ,'','del');
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}		
	}
	//合作站点管理
	public function site_list(){
		$model = D('Cooperate.Contentcooperation');
		list($list,$total,$Page,$count_arr,$soft_arr,$chl_soft_arr) = $model -> get_site_list();
		foreach($list as $k=>$val)
		{
			if($val['brush_page']&2)
			{
				$list[$k]['brush_pages'].="首页,";
			}
			if($val['brush_page']&4)
			{
				$list[$k]['brush_pages'].="应用,";
			}
			if($val['brush_page']&8)
			{
				$list[$k]['brush_pages'].="游戏,";
			}
			if($val['brush_page']&16)
			{
				$list[$k]['brush_pages'].="管理,";
			}
			if($val['brush_page']&32)
			{
				$list[$k]['brush_pages'].="搜索,";
			}
			if($val['is_chain_down']==1)
			{
				$list[$k]['is_chain_down'] ="支持外链下载";
			}
			else
			{
				$list[$k]['is_chain_down'] ="屏蔽外链下载（且展示下载推广banner）";
			}
			if($val['show_frequency']==1)
			{
				$list[$k]['show_frequency'] ="开启 只展示1次";
			}
			else if($val['show_frequency']==2)
			{
				$list[$k]['show_frequency'] ="开启 只展示2次";
			}
			else if($val['show_frequency']==3)
			{
				$list[$k]['show_frequency'] ="开启 只展示3次";
			}
		}
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$list);	
		$this -> assign('count_arr',$count_arr);			
		$this -> assign('soft_arr',$soft_arr);		
		$this -> assign('chl_soft_arr',$chl_soft_arr);			
		$this -> display();
	}	
	//编辑合作站点
	public function save_site(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
			if($_POST['anzhi_name']){	
				$strlen = mb_strlen($_POST['anzhi_name'],'utf-8');
				if($strlen <2 || $strlen >20){
					$this->error("名称：2~20个字符");
				}		
			}		
			$strlen_tip = mb_strlen($_POST['down_tip'],'utf-8');
			if($strlen_tip <2 || $strlen_tip >40){
				$this->error("下载提示弹窗文案名称：2~40个字符");
			}		
				
			list($res,$log) = $model ->  save_site($this);
			if($res){
				$this->writelog("内容合作管理--编辑了id为{$_POST['id']}{$log}【合作站点管理】",'coop_site',$_POST['id'],__ACTION__ ,'','edit');
				$this->success("操作成功");
			}else{
				$this->error("操作失败");
			}			
		}else{
			$id = $_GET['id'];
			$list = $model -> get_site_find($id);
			$this -> assign('list',$list);				
			$this -> display("add_site");
		}
	}	
	//启用、停用站点
	public function switch_site(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
		$map = array(
			'update_time' => time()
		);
		if($_GET['status'] == 1){	
			$map['status'] = 1;
			$log = "启用";
		}else{
			$where = array(
				'site_id' => $_GET['id'],
				'status' => 1
			);
			list($list,$total,$Page) = $model -> get_card_list($where);
			if($total > 0){
				$this->error("停用：其关联的卡片处于启用状态，请先停用相关卡片");
			}			
			$map['status'] = 0;
			$log = "停用";
		}
		$where = array(
			'id' => $_GET['id'],
		);
		$affect = $model->table("coop_site") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--{$log}了id为{$_GET['id']}【合作站点管理】",'coop_site',$_GET['id'],__ACTION__ ,'','edit');
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}		
	}	
	//合作站点标签管理
	public function site_tag_list(){
		$model = D('Cooperate.Contentcooperation');
		if($_GET['site_id']){
			$where = array('site_id'=>$_GET['site_id']);
			$this -> assign('site_id',$_GET['site_id']);	
		}else{
			$zhiyoo_site_id=C('zhiyoo_site_id');		
			$where = array('site_id'=>$zhiyoo_site_id);
			$this -> assign('site_id',$zhiyoo_site_id);	
		}
		list($list,$total,$Page,$count_arr,$video_count_arr) = $model -> get_site_tag_list($where);
		$this -> assign('page', $Page->show());
		$coop_site = $model->table("coop_site")->field('id,anzhi_name')->select(); 
		$this -> assign('coop_site',$coop_site);	
		$this -> assign('total', $total);
		$this -> assign('list',$list);	
		$this -> assign('count_arr',$count_arr);
		$zhiyoo_site_id=C('zhiyoo_site_id');		
		$this -> assign('zhiyoo_site_id',$zhiyoo_site_id);	
		$this -> assign('video_count_arr',$video_count_arr);		
		$this -> display();
	}	
	//编辑合作标签站点
	public function save_site_tag(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			$zhiyoo_site_id=$_POST['zhiyoo_site_id'];
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);		
			$strlen = mb_strlen($_POST['tag_anzhi_name'],'utf-8');
			$tag_name_strlen = mb_strlen($_POST['tag_name'],'utf-8');
			if($strlen <2 || $strlen >22){
				$this->error("名称(安智)：2~22个字符");
			}
			if(!$zhiyoo_site_id){
				if(!is_numeric($_POST['rank']) || $_POST['rank'] <=0){
					$this->error("排序值请填写整数");
				}
			}else{
				$tag_name_strlen = mb_strlen($_POST['tag_name'],'utf-8');
				if($tag_name_strlen <2 || $tag_name_strlen >22){
					$this->error("名称(合作)：2~22个字符");
				}
				if(!$_POST['site_tag_id']){
					$this->error("合作站点标签id不能为空");
				}
				// if(!$_POST['filter_id']){
				// 	$this->error("过滤id不能为空");
				// }
				if($_POST['site_id']!=$zhiyoo_site_id){
					$this->error("合作站点必选");
				}
			}	
			if(!$zhiyoo_site_id){
				list($res,$log) = $model ->  save_site_tag($this);
				if($res){
					$this->writelog("内容合作管理--编辑了id为{$_POST['id']}{$log}【合作站点标签管理】",'coop_site_tag',$_POST['id'],__ACTION__ ,'','edit');
					$this->success("操作成功");
				}else{
					$this->error("操作失败");
				}	
			}else{
				list($res,$log) = $model ->  add_save_site_tag($this);
				
				if($res){
					if($_POST['id']){
						$this->writelog("内容合作管理--编辑了id为{$_POST['id']}{$log}【合作站点标签管理】",'coop_site_tag',$_POST['id'],__ACTION__ ,'','edit');
					}else{
						$this->writelog("内容合作管理--添加了id为{$log}【合作站点标签管理】",'coop_site_tag',$log,__ACTION__ ,'','add');
					}
					
					$this->success("操作成功");
				}else{
					$this->error("操作失败");
				}

			}	
					
		}else{
			$zhiyoo_site_id=$_GET['zhiyoo_site_id'];
			if($zhiyoo_site_id){
				$coop_site = $model->table("coop_site")->field('id,anzhi_name')->where(array('id'=>$zhiyoo_site_id))->find(); 
				$this -> assign('zhiyoo_site_id',$zhiyoo_site_id);	
				$this -> assign('anzhi_name',$coop_site['anzhi_name']);	
			}
			if($_GET['id']){
				$id = $_GET['id'];
				$list = $model -> get_site_tag_find($id);
				$site = $model -> get_site_find($list['site_id']);
				$this -> assign('type',$site['type']);					
				$this -> assign('list',$list);				
			}
				
			$this -> display("add_site_tag");
		}
	}	
	//启用、停用站点标签
	public function switch_site_tag(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
		$map = array(
			'update_time' => time()
		);
		if($_GET['status'] == 1){	
			$map['status'] = 1;
			$log = "启用";
		}else{
			$where = array(
				'content_tags' => array("like","%,".$_GET['id'].",%"),
				'status' => 1
			);
			list($list,$total,$Page) = $model -> get_card_list($where);
			if($total > 0){
				$this->error("停用：其关联的卡片处于启用状态，请先停用相关卡片");
			}	
			$where = array(
				'contact_id' => $_GET['id'],
				'contact_type' => 2,
				'status' =>1
			);
			list($list,$total,$Page) = $model -> get_card_list($where);		
			if($total > 0){	
				$contact_site_id = array();
				$channle_id = array();
				foreach($list as $v){
					if($v['contact_site_id']) $contact_site_id[] = $v['contact_site_id'];
					if($v['channle_id']) $channle_id[] = $v['channle_id'];
				}
				$where = array(
					'id' => array('in',$contact_site_id)
				);
				$coop_site = get_table_data($where,"coop_site","id","id,anzhi_name");	
				$where = array(
					'id' => array('in',$channle_id)
				);	
				$coop_channel = get_table_data($where,"coop_channel","id","id,channel_name");				
				$error = "该标签有导向的卡片:\n";
				foreach($list as $v){
					$error .= "导向站点：".$coop_site[$v['contact_site_id']]['anzhi_name']."==>"."频道：".$coop_channel[$v['channle_id']]['channel_name']."==>"."卡片：".$v['card_name']."\n";
				}
				$error .= "不能停用";
				$this->error($error);
			}			
			$map['status'] = 0;
			$log = "停用";
		}
		$where = array(
			'id' => $_GET['id'],
		);
		$affect = $model->table("coop_site_tag") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--{$log}了id为{$_GET['id']}【标签管理】",'coop_site_tag',$_GET['id'],__ACTION__ ,'','edit');
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}		
	}
	//更新渠道包
	public function save_chl_pkg(){
		if($_POST){
			$model = D('Cooperate.Contentcooperation');	
			$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
			$mark = $_POST['mark'];
			$ret = $model->get_pkg_info();
			if($ret['code'] == 1){
				$apkmodel = D("Dev.Apk");
				if($_SESSION['apk_update'.$mark]) {	//有上传升级包,编辑升级信息时可能没有新上传升级包
					$_SESSION['apk_info'.$mark] = array_merge($_SESSION['apk_update'.$mark],$_SESSION['apk_info'.$mark]);
				}
				unset($_SESSION['apk_update'.$mark]);
				$do_datadb =  'data_db_admin';
				$vals = array_merge($_SESSION['apk_info'.$mark], array('do'=>$do_datadb, 'admin_id'=>$_SESSION['admin']['admin_id']));
				$vals['update_type'] = 1;	//后台
				$vals['extra'] = 'update';
				$vals['from_type'] = 'chl_soft_update';
				$arr = $apkmodel -> _http_post($vals);
				$arr = json_decode($arr, true);		
				//copy截图
				$model->cp_soft_thumb($_SESSION['apk_info'.$mark]['update_from'],$arr['softid']);	
				//copy sj_icon表
				$model->cp_soft_icon($_SESSION['apk_info'.$mark]['update_from'],$arr['softid']);
				$log = "更新了渠道包:".$vals['package']."-softid:".$arr['softid']."-versionCode:".$vals['versionCode'];
				$this -> writelog($log, 'sj_soft',$arr['softid'],__ACTION__ ,'','save_chl_pkg');
				$this->success("渠道包更新成功");
			}else{
				$this->error($ret['msg']);
			}
		}else{
			$mark	=	uniqid();
			$this -> assign('mark',$mark);	
			$this -> assign('package',$_GET['package']);	
			$this -> assign('softid',$_GET['softid']);	
			$this -> assign('cid',$_GET['cid']);	
			$this -> display();
		}
	}
	public function pub_tag_list(){
		$model = D('Cooperate.Contentcooperation');		
		$where = array(
			'site_id' => $_GET['site_id'],
			'status' => 1,
		);	
		$site_tag = $model->table("coop_site_tag")->where($where)->field("id,tag_anzhi_name")->select(); 	
		exit(json_encode($site_tag));	
	}
	public function pub_channel_list(){
		$model = D('Cooperate.Contentcooperation');		
		$where = array(
			'site_id' => $_GET['site_id'],
			'del' => 0,
			'type' => array('in',array('1','2','4')),
		);	
		if($_GET['from'] == 1){
			//卡片创建编辑(导向页面)只取启用的频道
			$where['status'] = 1;
		}
		$channel = $model->table("coop_channel")->where($where)->field("id,channel_name")->select(); 	
		exit(json_encode($channel));	
	}	
	public function pub_get_site(){
		$model = D('Cooperate.Contentcooperation');		
		$where = array(
			'status' => 1,
			'type' => $_GET['type'],
		);	
		if($_GET['site_id']==C('zhiyoo_site_id')){
			$where['id']=$_GET['site_id'];
		}
		// else{
		// 	$where['id']=array('neq',C('zhiyoo_site_id'));
		// }
		$coop_site = $model->table("coop_site")->where($where)->field("id,anzhi_name")->select(); 	
		exit(json_encode($coop_site));			
	}

	//合作资讯管理
	public function audit_content(){
		$status = isset($_GET['status'])?$_GET['status']:1;
		$where = array('a.status' => 1);
		$where['a.az_status'] = $status;
		if(isset($_GET['title'])){
			$where['a.title'] = array('like',"%{$_GET['title']}%");
			$this->assign('title',$_GET['title']);
		}
		if(isset($_GET['website_name'])){
			$where['a.site_id'] = $_GET['website_name'];
			$this->assign('website_name',$_GET['website_name']);
		}
		if(isset($_GET['tag'])){
			$where['a.tag_id'] = $_GET['tag'];
			$this->assign('tag_id',$_GET['tag']);
		}
		if(isset($_GET['begintime'])){
			$where['a.push_tm'][] = array('egt',strtotime($_GET['begintime']));
			$this->assign('begintime',$_GET['begintime']);
		}
		if(isset($_GET['endtime'])){
			$where['a.push_tm'][] = array('elt',strtotime($_GET['endtime']));
			$this->assign('endtime',$_GET['endtime']);
		}
		$order = 'create_tm desc';
		if(isset($_GET['order_num'])){
			$order = "view_num {$_GET['order_num']}";
			$this->assign('order_num',$_GET['order_num']);
			unset($_GET['order_num']);
		}
		if(isset($_GET['order_create'])){
			$order = "create_tm {$_GET['order_create']}";
			$this->assign('order_create',$_GET['order_create']);
			unset($_GET['order_create']);
		}
		if(isset($_GET['order_push'])){
			$order = "push_tm {$_GET['order_push']}";
			$this->assign('order_push',$_GET['order_push']);
			unset($_GET['order_push']);
		}
		import('@.ORG.Page2');

		//获取标签
		$model = D('Cooperate.Contentcooperation');
		if(isset($_GET['website_name'])){
			$total = $model->table('coop_content a')->join('coop_site b on a.site_id = b.id')->where($where)->count();
		}else{
			$total = $model->table('coop_content a')->where($where)->count();
		}

		$Page = new Page($total,10);
		$tag_list = $model -> get_site_tag_list(array('status'=>1));
		$website_list = $model->table('coop_site')->where('status = 1')->field('id,website_name')->select();
		$content_list = $model->table('coop_content a')->join('coop_site b on a.site_id = b.id')->join('coop_site_tag c on a.tag_id= c.id')->where($where)->limit($Page->firstRow.','.$Page->listRows)->field('a.*,b.website_name,c.tag_name')->order($order)->select();

		//echo $model->getLastSql();
		//var_dump($content_list);
		$param = $_GET;
		$param = http_build_query($param);
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);
		$this->assign('tag_list',$tag_list[0]);
		$this->assign('website_list',$website_list);
		$this->assign('content_list',$content_list);
		$this->assign('img_host',IMGATT_HOST);
		$this -> assign('param',$param);
		$this->assign('status',$status);
		$this->display();
	}

	public function save_content(){
		$model = D('Cooperate.Contentcooperation');
		if($_GET){
			$info = $model->table('coop_content')->where(array("id"=>$_GET['id']))->find();
			//var_dump($info);
			$this->assign('id',$_GET['id']);
			$this->assign('content',$info);
			$this->display();
		}
		if($_POST){
//			var_dump($_POST);
//			var_dump($_FILES);exit();
			if(isset($_POST['is_more'])){
				$id = explode(',',$_POST['id']);
			}else{
				$id = $_POST['id'];
			}
			$data = array();
			if(!$id){
				$this->error('未选择要操作的对象');
			}
			$status = array('1'=>'通过','2'=>'撤销','3'=>'驳回');
			if(isset($_POST['status'])) $data['az_status'] = $_POST['status'];
			if(isset($_POST['az_titile'])) $data['az_titile'] = $_POST['az_titile'];
			if(!empty($_FILES['az_images']['name'])) {
				$data['az_images'] = $this->upload_img();
			}
			if($_POST['az_images_del']==1&&$_FILES['az_images']['name']=='') $data['az_images'] = '';
			$save = $model->table('coop_content')->where(array('id'=>array('in',$id)))->save($data);
			if($save){
				$msg = '';
				if(isset($_POST['status'])){
					$msg = "{$status[$_POST['status']]}id为{$_POST['id']}成功";
					$this->writelog("内容合作管理--合作资讯管理:{$msg}",'coop_content',$_POST['id'],__ACTION__ ,'','edit');
					exit(json_encode(array('code'=>1,'msg'=>'操作成功')));
				}else{
					$this->success('保存成功');
				}
			}else{
				if(isset($_POST['status'])){
					exit(json_encode(array('code'=>0,'msg'=>'操作失败')));
				}else{
					$this->error('保存失败');
				}
			}
		}
	}

	public function upload_img(){
			list($msec, $sec) = explode(' ', microtime());
			$msec = substr($msec, 2);
			$path = UPLOAD_PATH .'/data3/img/';
			if (!file_exists($path)) {
				mkdir($path, 0777,true);
			}
			$ext = strtolower(pathinfo($_FILES['az_images']['name'],PATHINFO_EXTENSION));
			if(!in_array($ext,array('jpg','jpeg','png'))){
				$this->error('图片格式为jpg,jpeg,png');
			}
			$img_info = getimagesize($_FILES['az_images']['tmp_name']);
			if(!$img_info){
				$this->error('请上传有效图片');
			}
			if($img_info[0]>700||$img_info[1]>700){
				$this->error('展示图片最大宽高为700像素');
			}

			$img_path =  $path.'img_'. $msec . '.' . $ext;
			if (move_uploaded_file($_FILES['az_images']['tmp_name'], $img_path)) {
				$az_images = str_replace(UPLOAD_PATH, '', $img_path);
			}
			return $az_images;
	}
	//合作站点标签管理
	public function audit_tag(){
		$model = D('Cooperate.Contentcooperation');
		$where = array('a.status'=>1);
		if(!$_GET['tag_id']){}else{
			$where['a.tag_id'] = $_GET['tag_id'];
			$this->assign('tag_id',$_GET['tag_id']);
			$this->assign('tag_name',$_GET['tag_name']);
		}
		if(!$_GET['status']){
			$status = 2;
		}else{
			$status = $_GET['status'];
			//unset($_GET['status']);
		}
		$this->assign('status',$status);

		if($status!=1){
			$where['a.data_status'] = $status;
		}

		if($_GET['s_title']){
			$where['b.title'] = array('exp'," like '%{$_GET['s_title']}%'");
			$this->assign('title',$_GET['s_title']);
		}
		if($_GET['begintime']){
			$start_tm = strtotime($_GET['begintime']);
			$where['a.start_tm'] = array('exp'," >= '{$start_tm}'");
			$this->assign('begintime',$_GET['begintime']);
		}
		if($_GET['endtime']){
			$end_tm = strtotime($_GET['endtime']);
			$where['a.end_tm'] = array('exp'," <= '{$end_tm}'");
			$this->assign('endtime',$_GET['endtime']);
		}
		$order = 'a.rank asc';
		if(isset($_GET['rank'])){
			$order = "a.rank {$_GET['rank']}";
			$this->assign('rank',$_GET['rank']);
			unset($_GET['rank']);
		}
		if(isset($_GET['order_num'])){
			$order = "b.view_num {$_GET['order_num']}";
			$this->assign('order_num',$_GET['order_num']);
			unset($_GET['order_num']);
		}
		$tag_list = $model -> get_site_tag_list(array('status'=>1));
		$where['b.az_status'] = 1;
		$info = $model->table('coop_content_order as a')->join('coop_site as c on a.site_id = c.id')->join('coop_content as b on a.content_id = b.id')->field('a.*,b.title,b.images_small,b.az_titile,b.az_images,b.from,b.view_num,c.website_name')->where($where)->order($order)->select();
		//echo $model->getLastSql();
		$param = $_GET;
		$param = http_build_query($param);
		//var_dump($param);
		$this->assign('param',$param);
		$this->assign('info',$info);

		$this->assign('tag_list',$tag_list[0]);
		$this->display();
	}

	//保存站点标签内容

	public function save_audit_tag(){
		$model = M('');
		if($_POST){
			if(!empty($_POST['id'])){
				$this->edit_audit_tag();
			}else{
				$this->add_audit_tag();
			}
		}
		if($_GET['id']){
			$content = $model->table('coop_content_order as a')->join('coop_site as c on a.site_id = c.id')->join('coop_site_tag as d on a.tag_id = d.id')->join('coop_content as b on a.content_id = b.id')->where("a.id = '{$_GET['id']}'")->field('a.*,b.title,b.images_small,b.az_titile,b.az_images,b.view_num,c.website_name,d.tag_name')->find();
			$this->assign('content',$content);
		}
		$this->display();
	}

	public function edit_audit_tag(){
		$model = M('');
		$has_info = $model->table('coop_content_order')->where(array('id'=>$_POST['id']))->find();
		$c_order_data = $this->p_content_order();
		$this->p_content($c_order_data);
		$need_edit = false;
		foreach($c_order_data as $k=>$v){
			if($v != $has_info[$k]){
				$need_edit = true;
			}
		}
		if($need_edit){
			$res = $model->table('coop_content_order')->where("id = '{$_POST['id']}'")->save($c_order_data);
			if($res){
				$this->success('保存成功');
			}else{
				$this->error('保存失败');
			}
		}else{
			$this->success('保存成功');
		}
	}

	public function p_content($c_order_data){
		$model = M('');
		$c_data = array();
		$c_data['az_titile'] = $_POST['az_titile'];
		if(!empty($_FILES['az_images']['name'])) {
			$c_data['az_images'] = $this->upload_img();
		}
		if($_POST['az_images_del']) $c_data['az_images'] = '';
		if($c_order_data['start_tm']<=time()&&$c_order_data['end_tm']>=time()){
			$c_data['open_type_new'] = $c_order_data['open_type'];
		}else{
			$c_data['open_type_new'] = 0;
		}
		$model->table('coop_content')->where(array('id'=>$_POST['content_id']))->save($c_data);

	}

	public function p_content_order(){
		$c_order_data = array();
		$c_order_data['rank'] = $_POST['rank'];
		$c_order_data['open_type'] = $_POST['open_type'];
		$c_order_data['start_tm'] = strtotime($_POST['start_tm']);
		$c_order_data['end_tm'] = strtotime($_POST['end_tm']);
		if(!$_POST['id']){
			$c_order_data['site_id'] = $_POST['site_id'];
			$c_order_data['tag_id'] = $_POST['s_tag_id'];
			$c_order_data['content_id'] = $_POST['content_id'];
			$c_order_data['push_tm'] = $_POST['push_tm'];
		}
		if($c_order_data['start_tm']>time()){
			$c_order_data['data_status'] = 1;
		}else if($c_order_data['start_tm']<=time()&&$c_order_data['end_tm']>=time()){
			$c_order_data['data_status'] = 2;
		}else if($c_order_data['end_tm']<time()){
			$c_order_data['data_status'] = 3;
		}
		return $c_order_data;
	}

	//添加合作站点标签内容
	public function add_audit_tag(){
		$model = M('');
		$has_info = $model->table('coop_content_order')->where(array('content_id'=>$_POST['content_id']))->find();
		if($has_info){
			$this->error('合作资讯已存在，请前往列表编辑');
		}
		$c_order_data = $this->p_content_order();
		$this->p_content($c_order_data);
		$res = $model->table('coop_content_order')->add($c_order_data);
		if($res){
			$this->writelog("内容合作管理-合作站点标签管理:添加了id为{$res}的资讯",'coop_content_order',$res,__ACTION__ ,'','add');
			$this->success("保存成功");
		}else{
			$this->error("保存失败");
		}

	}

	public function pub_chk_content(){
		$id = $_POST['id'];
		if(!$id||!is_numeric($id)){
			echo json_encode(array('code'=>0,'msg'=>'该资讯不存在'));
			exit();
		}
		$model = M('');
		$info = $model->table('coop_content a')->join('coop_site b on a.site_id = b.id')->join('coop_site_tag as c on a.tag_id = c.id')->where("a.id = '{$id}' and az_status = 1")->field('a.id,a.title,a.az_titile,a.az_images,a.push_tm,b.id as site_id,b.website_name,c.id as tag_id,c.tag_name')->find();
		if($info){
			echo json_encode(array('code'=>1,'info'=>$info));
		}else{
			echo json_encode(array('code'=>0,'msg'=>'该资讯无效'));
		}
		exit();

	}

	public function del_audit_tag(){
		$model = M('');
		$id = $_GET['id'];
		if(!$id){
			$this->error('未选择ID');
		}else{
			$res = $model->table('coop_content_order')->where("id = '{$id}'")->save(array('status'=>0));
			if($res){
				$this->writelog("内容合作管理-合作站点标签管理:删除了id为{$res}的站点标签内容",'coop_content_order',$res,__ACTION__ ,'','del');
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}
	}

	public function pub_edit_rank(){
		$model = M('');
		$id = $_POST['id'];
		if($id){
			$res = $model->table('coop_content_order')->where("id = '{$id}'")->save(array('rank'=>$_POST['rank']));
			if($res){
				echo json_encode(array('code'=>1,'msg'=>'保存成功'));
			}else{
				echo json_encode(array('code'=>0,'msg'=>'保存失败'));
			}
		}else{
			echo json_encode(array('code'=>0,'msg'=>'缺少参数'));
		}
		exit();
	}
	//渠道列表
	Public function coop_splash_screen_list(){
		$model = D('Cooperate.Contentcooperation');
		$where = array( 'del'=> 0 );
		$table = "coop_splash_screen";
		import('@.ORG.Page2');
		$total = $model->table($table)->where($where)->count();
		$Page = new Page($total,10);	
		$list = $model->table($table)->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
		$this -> assign('page', $Page->show());
		$this -> assign('total', $total);
		$this -> assign('list',$list);				
		$this -> display();
	}
	//启用、停用合作闪屏
	public function switch_coop_splash_screen(){
		$model = D('Cooperate.Contentcooperation');
		$this->assign('jumpUrl',$_SERVER['HTTP_REFERER']);	
		$map = array(
			'update_time' => time()
		);
		if($_GET['status'] == 1){	
			$map['status'] = 1;
			$log = "启用";
		}else{		
			$map['status'] = 0;
			$log = "停用";
		}
		$where = array(
			'id' => $_GET['id'],
			'del' => 0
		);
		$affect = $model->table("coop_splash_screen") -> where($where) -> save($map);		
		if($affect){
			$this->writelog("内容合作管理--{$log}了id为{$_GET['id']}【合作闪屏】",'coop_splash_screen',$_GET['id'],__ACTION__ ,'','edit');
			$this->success("操作成功");
		}else{
			$this->error("操作失败");
		}	
	}

	//编辑合作闪屏
	public function save_coop_splash_screen(){
		$model = D('Cooperate.Contentcooperation');
		if($_POST){
			if(!(is_numeric($_POST['show_probability'])&&$_POST['show_probability']==(int)$_POST['show_probability']) || $_POST['show_probability'] <0){
				$this->error("显示概率请填写整数");
			}
			if($_POST['show_probability']>100){
				$this->error("显示概率请填写100以内的整数");
			}	
			if($_POST['filter_area']>0 && !$_POST['area_value']){
				$this->error("请选择添加地区");
			}
			list($res,$log) = $model-> save_coop_splash_screen($this);		
			if($res){
				$this->writelog("内容合作管理--合作闪屏管理--编辑了id为{$_POST['id']}{$log}",'coop_splash_screen',$_POST['id'],__ACTION__ ,'','edit');
				$this ->success("编辑成功");
			}else{
				$this ->error("编辑失败");
			} 
		}else{
			$list = $model -> table('coop_splash_screen')->where(array('id'=>$_GET['id']))->find();
			$chl_list = $model -> get_cid_arr($list['cid']);
			$this -> assign('chl_list',$chl_list);	
			$this->assign('list',$list);
			$this->display(); 
		}		
	}
}


