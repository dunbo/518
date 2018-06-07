<?php
/*
 * 外投渠道跳转配置
 */
class OutsideJumpConfigAction extends CommonAction {
	private $special_package = 'cn.goapk.market';
	private $tb="outside_jump_config";
	private $tb_q="sj_outside_jump_config";

	function list_config()
	{
		$model = M($this->tb);
		$where = array(
			'status' => 1
		);
		$now = time();
		if($_POST['chname']){
			$chname=trim($_POST['chname']);
			if($chname=="通用"){
				$cid=0;
			}else{
				$channeldata=$model->table('sj_channel')->where(array('chname'=>$chname))->find();
				$cid=$channeldata['cid'];
			}
			

			if(isset($cid)){
				$where['cid']=array('like',"%,$cid,%");
			}
			$this->assign('chname', $chname);
		}
		if($_POST['start_tm']){
			$start_tm=strtotime($_POST['start_tm']);
			$where['add_tm']=array('egt',$start_tm);
			$this->assign('start_tm', $_POST['start_tm']);
		}
		if($_POST['end_tm']){
			$end_tm=strtotime($_POST['end_tm']);
			$where['add_tm']=array('elt',$end_tm);
			$this->assign('end_tm', $_POST['end_tm']);
		}
		if($_POST['start_tm'] && $_POST['end_tm']){
			$where['add_tm']=array('exp',">=$start_tm and add_tm<=$end_tm");
		}
		$count = $model->where($where)->count();
		import("@.ORG.Page");
		$p = new Page ( $count, 15 );
		$list = $model->limit($p->firstRow.','.$p->listRows)->where($where)->order('add_tm desc')->select();
		
		$page = $p->show();

		$content_id_arr=$cid_arr=$pkg_arr=$flexible_arr=array();
		foreach($list as $k=>$v){
			$v['content_id'] && $content_id_arr[]=$v['content_id'];
			$cid=trim($v['cid'],',');
			if(stripos($cid,",")){
				$cid_arr=array_merge($cid_arr,explode(',', $cid));
			}else{
				$cid_arr=array_merge($cid_arr,array($cid));
			}
			
			$v['flexible_id'] && $flexible_arr[]=$v['flexible_id'];
			$v['video_id'] && $flexible_arr[]=$v['video_id'];
			$v['package'] && $pkg_arr[]=$v['package'];
		}

		$content_id_arr && $common_jump_data=$model->table('sj_common_jump')->where(array('id'=>array('in',$content_id_arr)))->select();
		$channel_data=$model->table('sj_channel')->where(array('cid'=>array('in',array_unique($cid_arr))))->select();
		$pkg_data=$model->table('sj_soft')->where(array('package'=>array('in',$pkg_arr),'status'=>1,'hide'=>1))->select();
		$flexible_data=$model->table('sj_flexible_extent_soft')->where(array('id'=>array('in',$flexible_arr)))->select();

		
		$common_jump_data_new=array();
		foreach($common_jump_data as $k=>$v){
			$content_id=$v['id'];
			unset($v['id']);
			unset($v['create_at']);
			unset($v['update_at']);
			unset($v['status']);

			$common_jump_data_new[$content_id]=$v;
		}
		$channel_data_new=array();
		foreach($channel_data as $k=>$v){
			$channel_data_new[$v['cid']]=$v;
		}
		$pkg_data_new=array();
		foreach($pkg_data as $k=>$v){
			$pkg_data_new[$v['package']]=$v['softname'];
		}
		$flexible_data_new=array();
		foreach($flexible_data as $k=>$v){
			if($v['resource_type']==24){
				$flexible_data_new[$v['id']]=$v['title'];
			}else{
				if($v['title']){
					$flexible_data_new[$v['id']]=$v['title'];
				}else{
					$flexible_data_s=$model->table('sj_flexible_extent_soft')->where(array('id'=>$v['res_id']))->find();
					$flexible_data_new[$v['id']]=$flexible_data_s['title'];
				}
				
			}
			
		}
		$result = array();
		$fast_test="fast_test";
		$fast_url="m.dev.anzhi.com";
		foreach($list as $key=>$val) {
			$val['add_tm']=date('Y-m-d H:i:s',$val['add_tm']);
			$str_cid=trim($val['cid'],',');
			if(!stripos($str_cid,",")){
				$cid=array($str_cid);
			}else{
				$cid=explode(',', $str_cid);
			}
			
			$chname_str="";
			$str_link="";
			foreach($cid as $v){
				if($v==="0"){
					$chname_str.="通用"."<br>";
					if($val['type']==1){
						// http://fx.anzhi.com/fast.php?type=launch&id=6595&chl_cid=8f27dc5b4803
						$str_link.="http://{$fast_url}/{$fast_test}.php?type=outsidejump&id={$val['custom_id']}&chl_cid={$channel_data_new[$v]['chl_cid']}"."<br><br>";
					}else{
						$str_link.="http://{$fast_url}/{$fast_test}.php?type=launch&id={$val['content_id']}&chl_cid={$channel_data_new[$v]['chl_cid']}"."<br><br>";
					}
					
				}else{
					$chname_str.=$channel_data_new[$v]['chname']."<br>";
					if($val['type']==1){
						$str_link.="http://{$fast_url}/{$fast_test}.php?type=outsidejump&id={$val['custom_id']}&chl_cid={$channel_data_new[$v]['chl_cid']}"."<br><br>";
					}else{
						$str_link.="http://{$fast_url}/{$fast_test}.php?type=launch&id={$val['content_id']}&chl_cid={$channel_data_new[$v]['chl_cid']}"."<br><br>";
					}
					
				}
				
			}
			$val['chname_link']=$str_link;
			$val['chname_str']=$chname_str;
			$val['softname']=$pkg_data_new[$val['package']];
			$val['flexible_title']=$flexible_data_new[$val['flexible_id']];
			$val['video_title']=$flexible_data_new[$val['video_id']];
			if($val['type']==1){
				$result[] =$val;
			}else{
				$result[] = array_merge($val,$common_jump_data_new[$val['content_id']]);
			}
		}
		foreach($result as $k=>$val){
			if($val['content_type'] == 5){
				$result[$k]['content_type'] = "网页-".$val['website'];
			}else if($val['content_type'] == 6){
				$result[$k]['content_type'] = "礼包-".$val['gift_id'];
			}else if($val['content_type'] == 7){
				$result[$k]['content_type'] = "攻略-".$val['strategy_id'];
			}else if($val['content_type'] == 1){
				$pkg_data=$model->table('sj_soft')->where(array('package'=>$val['package'],'status'=>1,'hide'=>1))->find();
				$result[$k]['content_type'] = "软件-".$pkg_data['softname'];
			}else if($val['content_type'] == 9||$val['content_type'] == 4){
				$used_info = json_decode($val['parameter_field'],true);
				if($val['content_type'] == 4){
					$result[$k]['re_keyword'] = $used_info['re_keyword'];
				}else{
					$result[$k]['content_type'] = "应用内览-".$used_info['softname']."-".$used_info['title'];
				}

			}
		}
		
		$this->assign('list', $result);
		$this->assign('page', $page);
		$this->display();
	}


	function add_config()
	{


		$model = M($this->tb);

		if($_GET['bs']==1){
			echo $this->getinfo($_GET);
			return;
		}
		if (!empty($_POST)){

			$map = array();
			$map['status'] = 1;
			$map['add_tm'] = time();
            $map['admin_id'] = $_SESSION['admin']['admin_id'];

			isset($_POST['type']) && $map['type'] = $_POST['type'];
			isset($_POST['cid']) && $map['cid'] = ','.implode(',', $_POST['cid']).',';
			if($map['type']==2){
				$conetnt_map_1 = array();
				$rcontent_1 = ContentTypeModel::saveRecommendContent($_POST,$_POST['content_type'],$conetnt_map_1);
				if($rcontent_1==true)
				{
					$conetnt_map_1['create_at'] = time();
					$conetnt_map_1['update_at'] = time();
					$conetnt_id_1 = M('')->table('sj_common_jump')->add($conetnt_map_1);
					$map['content_id'] = $conetnt_id_1;
				}else {
					$this -> error($rcontent_1);
				}
			}else{
				isset($_POST['package_new']) && $map['package'] = $_POST['package_new'];
				isset($_POST['is_show_tip']) && $map['is_show_tip'] = $_POST['is_show_tip'];
				isset($_POST['rows_title']) && $map['rows_title'] = $_POST['rows_title'];
				isset($_POST['flexible_id']) && $map['flexible_id'] = $_POST['flexible_id'];
				isset($_POST['video_id']) && $map['video_id'] = $_POST['video_id'];
			}
			
			
			if ($id = $model->add($map)) {
				$map['id']=$id;
				if(!$map['content_id']){
					$this->add_outside_list($map);
				}
				$this->assign('jumpUrl', '/index.php/Channel_cooperation/OutsideJumpConfig/list_config');
				$this->writelog("软件外投管理-外投渠道跳转配置::添加了id为{$id}", $this->tb_q,$id,__ACTION__ ,"","add");

				$this->success('添加成功');
			} else {
				$this->error('添加失败');
			}
		} else {
			$this->display();
		}
	}

	function del_config()
	{
		$id = $_REQUEST['id'];
		$content_id = $_REQUEST['content_id'];
		$custom_id = $_REQUEST['custom_id'];
		$where = array(
			'id' => $id
		);
		$t=time();
		$map = array(
			'status' => 0,
			'update_tm'=>$t,
		);
		$model = M($this->tb);
		$re=$model->where($where)->save($map);
		if($re){
			if($content_id){
				$model->table('sj_common_jump')->where(array('id'=>$content_id))->save(array('status'=>0,'update_at'=>$t));
			}else{
				$model->table('sj_custom_list_name')->where(array('id'=>$custom_id))->save(array('status'=>0,'update_time'=>$t));
			}
			
			$this->writelog("软件外投管理-外投渠道跳转配置:删除了id为[$id]的外投配置", $this->tb_q, $id,__ACTION__ ,"","del");
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
		
	}
	function getinfo($data){
		$package=trim($data['package']);
		$model = M($this->tb);
		$pkg_data=$model->table('sj_soft')->where(array('package'=>$package,'status'=>1,'hide'=>1))->find();
		if(!$pkg_data){
			return '';
		}
		$arr=array();
		$arr['package']=$pkg_data['package'];
		$arr['softname']=$pkg_data['softname'];
		$arr['rows_title']="下载<".$pkg_data['softname'].">的用户还下载";
		$flexible_data=$model->table('sj_flexible_extent_soft')->where(array('package_643'=>$package,'status'=>1,'resource_type'=>24))->select();
		if($flexible_data){
			$fle_str="";
			$fle_str.="<SELECT name='flexible_id' id='flexible_id'>";
			foreach($flexible_data as  $k=>$v){
				$fle_str.="<option value='".$v['id']."'>".$v['title']."</option>";
			}
			$fle_str.="</SELECT>";
		}else{
			$fle_str="<SELECT name='flexible_id' id='flexible_id'><option value='' selected>无</option></SELECT>";
		}
		$arr['flexible_id_str']=$fle_str;
		$belong_page_type="external_special";
		$flexible_extent_data=$model->table('sj_flexible_extent')->where(array('belong_page_type'=>$belong_page_type,'status'=>1))->select();
		$extent_id_arr=array();
		foreach($flexible_extent_data as $v){
			$extent_id_arr[]=$v['extent_id'];
		}

		$flexible_extent_soft_data=$model->table('sj_flexible_extent_soft')->where(array('extent_id'=>array('in',$extent_id_arr),'status'=>1))->select();
		shuffle($flexible_extent_soft_data);
		if($flexible_extent_soft_data){
			$pkg_arr=array();
			foreach($flexible_extent_soft_data as  $k=>$v){
				$pkg_arr[]=$v['package_643'];
			}
			$pkg_data=$model->table('sj_soft')->where(array('package'=>array('in',$pkg_arr),'status'=>1,'hide'=>1))->field('package,softname')->select();
			$pkg_data_new=array();
			foreach($pkg_data as $v){
				$pkg_data_new[$v['package']]=$v['softname'];
			}
			$video_id_str="<SELECT name='video_id' id='video_id'>";
			foreach($flexible_extent_soft_data as  $k=>$v){
				if($v['title']){
					$parameter_field=json_decode($v['parameter_field'],true);
				    $video_id_str.="<option value='".$v['id']."'>".$pkg_data_new[$v['package_643']].'-'.$v['title']."</option>";
				}else{
					// $parameter_field=json_decode($v['parameter_field'],true);
					$flexible_data_new=$model->table('sj_flexible_extent_soft')->where(array('id'=>$v['res_id']))->find();
				    $video_id_str.="<option value='".$v['id']."'>".$pkg_data_new[$v['package_643']].'-'.$flexible_data_new['title']."</option>";
				}
				
			}
			$video_id_str.="</SELECT>";
		}else{
			$video_id_str="<SELECT name='video_id' id='video_id'><option value='' selected>无</option></SELECT>";
		}
		$arr['video_id_str']=$video_id_str;
		return json_encode($arr);
	}
	function add_outside_list($map){
		$model = M('');
		$map['channel_id']=27;
		//插入自定义列表
		$custom_id=$this->add_customlist($map);
		$map['custom_id']=$custom_id;
		if($map['custom_id']){
			if($map['video_id']){
				$arr=array(24,29,32);
			}else{
				$arr=array(24,32);
			}
			
			foreach($arr as $v){
				$map['extent_type']=$v;
				$this->add_flexible_extent($map);
			}
		}
		
		
	}
	function add_customlist($map){
		$model = M('');
		$data=array();
		$data['name']="列表".$map['id'];

		$data['channel_id'] = $map['channel_id'];
        $data['rank'] = '1';
        $now = time();
        $data['create_time'] = $now;
        $data['update_time'] = $now;			
		
		$data['data_type'] = $_POST['data_type'];
        $data['status'] = 1;
		$data['admin_id'] = $_SESSION['admin']['admin_id'];
		$ret = $model->table('sj_custom_list_name')->add($data);
		$model->table($this->tb_q)->where(array('id'=>$map['id']))->save(array('custom_id'=>$ret));
		return $ret;
	}
	function add_flexible_extent($map){
		$model = M('');
		$data=array();
		$data['extent_name']="区间".$map['custom_id']."_".$map['extent_type'];
		$data['type']=1;
		$data['oid']=0;
		$data['cid']='';
		if($map['extent_type']==24){
			$data['extent_size']=2;
			$data['rank']=1;
		}else if($map['extent_type']==29){
			$data['extent_size']=2;
			$data['rank']=3;
		}else if($map['extent_type']==32){
			$data['extent_size']=2;
			$data['rank']=5;
			$map['rows_title'] = trim($map['rows_title']);
			if(!empty($map['rows_title'])){
				$data['display_title'] = $map['rows_title'];
			}else{
				$soft = $model->table('sj_soft')->where(array('package'=>$map['package'],'status'=>1,'hide'=>1))->field('softid,softname')->order('softid desc')->find();
				$data['display_title'] = "下载<{$soft['softname']}>的用户还下载";
			}

		}
		
		$data['start_at']=0;
		$data['end_at']=0;
		$data['create_at']=time();
		$data['update_at']=time();
		$data['release_time']=time();
		$data['status']=1;
		$data['parent_id']=0;
		$data['belong_page_type']="customlist1_".$map['channel_id']."_".$map['custom_id'];
		$data['pid']=1;
		$data['extent_type']=$map['extent_type'];
		$data['admin_id']=$_SESSION['admin']['admin_id'];
		$ret = $model->table('sj_flexible_extent')->add($data);
		
		if($ret){
			$map['extent_id']=$ret;
			$this->add_flexible_extent_soft($map);
		}
		
	}
	function add_flexible_extent_soft($map){
		$model=M('');
		if($map['extent_type']==29){
			// $data['res_id']=$map['video_id'];
			// $data['parameter_field']='{"is_tag":"0","tag_title":""}';
			$flexible_data=$model->table('sj_flexible_extent_soft')->where(array('id'=>$map['video_id']))->find();
			$flexible_data['extent_id']=$map['extent_id'];
			$flexible_data['start_at']=time();
			$flexible_data['create_at']=time();
			$flexible_data['update_at']=time();
			$flexible_data['end_at']= strtotime("+10 year");
			unset($flexible_data['id']);
			$ret = $model->table('sj_flexible_extent_soft')->add($flexible_data);
			return;
		}
		$data=array();
		$data['extent_id']=$map['extent_id'];
		$data['prob']=100;
		$data['start_at']=time();
		$data['create_at']=time();
		$data['update_at']=time();
		$data['end_at']= strtotime("+10 year");
		$data['status']=1;
		$data['admin_id']=$_SESSION['admin']['admin_id'];
		$data['status']=1;
		if($map['extent_type']==24){
			$data['res_id']=$map['flexible_id'];
			if($map['is_show_tip']==1)
			$data['parameter_field']= json_encode(array('sem_title'=>'来自你的浏览记录'));
		}else if($map['extent_type']==32){
			$data['package']=$this->special_package;
			$data['parameter_field']='{"soft_rank":"1","search_type":"2"}';
		}
		if($map['extent_type']!=32){
			$flexible_data=$model->table('sj_flexible_extent_soft')->where(array('id'=>$data['res_id']))->find();

			$data['package_643']=$flexible_data['package_643'];
		}
		
		
		$data['version_type']=3;
		
		$ret = $model->table('sj_flexible_extent_soft')->add($data);

	}
}
