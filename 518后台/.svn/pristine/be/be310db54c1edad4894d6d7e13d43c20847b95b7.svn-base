<?php 
Class ActiveConfAction extends CommonAction{
	private $bbsmodel;
	private $model;
	private $img_host_out = array(
            'http://img1.anzhi.com',
            'http://img2.anzhi.com',
            'http://img3.anzhi.com',
            'http://img4.anzhi.com',
            'http://img5.anzhi.com'
        );
	
	public function _initialize() {
        parent::_initialize();
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	
	function active_list(){
		$rank = $_GET['rank'] ? $_GET['rank']: 'asc';
		$order_url = $_GET['rank'] == 'desc' ? 'asc' : 'desc';
		$type = $_GET['type'] ? $_GET['type'] : 1;
		if(in_array($type,array(5,7))) $order_sql = !$_GET['rank']?  'status asc ,rank asc': "rank {$rank}";
		else $order_sql = !$_GET['rank']?  'rank asc': "rank {$rank}";
		
		if(in_array($type,array(8,11))) $order_sql = 'status asc,start_tm desc';
		$sub_query = '';
		if($type == 8){
			$sub_query .= $_GET['subject'] ? ' and subject like \'%'.$_GET['subject'].'%\' ' : '';
			if($_GET['starttime'] && $_GET['endtime']){
				$starttime = strtotime($_GET['starttime']);
				$endtime = strtotime($_GET['endtime']);
				$sub_query .= " and start_tm >= {$starttime} and end_tm <= {$endtime}";
			}elseif(empty($sub_query)){
				$sub_query = ' and end_tm >='.time();
			}
		}
		if($_GET['histype']){
			$sub_query = ' and end_tm <='.time();
		}
		
		$res = $this -> model -> table('zy_active_conf') -> where("type={$type} and status >0 ".$sub_query) -> order($order_sql)->select();
		$count = $this -> model -> table('zy_active_conf') -> where("type={$type} and status >0".$sub_query) -> count();
		$showadd = 1;
		if($type == 1 && $count >= 1) $showadd = 0; 
		if($type == 4 && $count >= 4) $showadd = 0; 
		if($type == 2 && $count >= 6) $showadd = 0; 
		if($type == 3 && $count >= 1) $showadd = 0; 
		if($type == 5 && $count >= 8) $showadd = 0; 
		if($type == 6 && $count >= 4) $showadd = 0; 
		if($type == 7 && $count >= 9) $showadd = 0; 
		if($type == 9 && $count >= 5) $showadd = 0; 
		if($type == 12 && $count >= 5) $showadd = 0; 
		if($type == 13 && $count >= 10) $showadd = 0; 
		if($type == 14 && $count >= 4) $showadd = 0; 
		if($type == 15 && $count >= 1) $showadd = 0; 
		if($type == 16 && $count >= 5) $showadd = 0; 
		if($type == 17 && $count >= 5) $showadd = 0; 
		if($_GET['edit_rank']){
			$this ->assign('editrank',$_GET['edit_rank']);
			$showadd = 0;
		}
        $hotforum = $this->model->query("SELECT id,class,b_fid,b_name,b_plat,name,icon,status FROM `zy_bbs_plate_area` WHERE class=1 AND status!='-1' ORDER BY status DESC , modifytime ASC ");
        foreach($hotforum as $f){
            $forum[$f['b_fid']] = $f['name'];
        }
        $jumpPosRes = $this -> model->table('zy_client_jump_position')->select();
        foreach($jumpPosRes as $val){
            $jumpPos[$val['operation_type']] = $val['name'];
        }
        
        
		foreach($res as $key=>$val){
			$jumptxt = '';
			if($val['operation_type'] == 1 && $val['cid']){
				$name = $this -> model->table('zy_column_conf')->field('name')->where("cid={$val['cid']} and status >=1 ")-> find();
				$jumptxt = !empty($name['name']) ? "栏目：".$name['name'] : '';
			}elseif($val['operation_type'] == 18){
				$name = $this -> bbsmodel->table('x15_forum_thread')->field('subject')->where(array('tid'=>$val['tid']))-> find();
				$jumptxt = "产品：".$name['subject'];
				
			}elseif(array_key_exists($val['operation_type'],$jumpPos)){
                //跳转页面选择设置页面时，列表页面链接栏显示空白
				// $name = $this -> model->table('zy_client_jump_position')->field('name')->where("operation_type={$val['operation_type']}")-> find();
				$jumptxt = "客户端：".$jumpPos[$val['operation_type']];
			}elseif($val['operation_type'] == 8){
				$name = $this -> bbsmodel->table('x15_forum_forum')->field('name')->where(array('fid'=>$val['fid']))-> find();
				$jumptxt = "版块：".$name['name'];
				
			}elseif($val['operation_type'] == 9){
				$name = $this -> bbsmodel->table('x15_forum_thread')->field('subject')->where(array('tid'=>$val['tid']))-> find();
				$jumptxt = "主题：".$name['subject'];
				
			}elseif($val['operation_type'] == 10){
				$jumptxt = "内部链接地址：".$val['url'];
				
			}elseif($val['operation_type'] == 11){
				$jumptxt = "外部链接地址：".$val['url'];
				
			}elseif($val['operation_type'] == 12){
				$name = $this -> model->table('zy_column_conf')->field('name')->where("cid={$val['pid']} and status >=1 ")-> find();
				$jumptxt = !empty($name['name']) ? "专题：".$name['name'] : '';
				
			}elseif($val['operation_type'] == 25){
                $jumptxt = "商城链接地址：".$val['url'];
            }elseif($val['operation_type'] == 26){
                $jumptxt = ' 板块-精华热帖：'.$forum[$val['fid']];
            }elseif($val['operation_type'] == 28){
            	$jumptxt = "H5链接地址：".$val['url'];
            }elseif($val['operation_type'] == 29){
            	$jumptxt = "互动广告链接地址：";
            	if(!empty($val['advurl'])){//对互动广告链接进行处理
					$ver_advurl = explode('}{',$val['advurl']);
					foreach ($ver_advurl as $advurl_value) {
						$version_url = explode('][',$advurl_value);
						if($version_url[1] == 3700 || $version_url[1] == ''){
							$jumptxt .= "版本v3.7：".$version_url[0];
						} 
						if($version_url[1] == 3800){
							$jumptxt .= " 版本v3.8：".$version_url[0];
						} 
					}
				}
            }
			$res[$key]['jumptxt'] = $jumptxt;
			if($type == 8){
				$push_info =  $this -> model -> table('zy_active_conf_push') -> where("aid={$val['id']}")-> find();
				$res[$key]['pushuser'] = $push_info['pushuser']?$push_info['pushuser']:'全部用户';
			}
		}
		
		$this -> assign('actlist',$res);
		$this -> assign('order_url',$order_url);
		$this -> assign('showadd',$showadd);
		$this -> assign('ltype',$type);
		if($type == 8) {
			$this -> assign('starttime',$_GET['starttime']);
			$this -> assign('endtime',$_GET['endtime']);
			$this -> assign('subject',$_GET['subject']);
			$this -> assign('histype',$_GET['histype']);
			$this -> display('pushlist');
		}else{
            $this -> display();
        }
	}
	
	function add(){
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$jump = $this -> model ->table('zy_client_jump_position') -> select();
		if($type == '16'){
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=3') ->select();
		}elseif($type == '17'){
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=4') ->select();
		}else{
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=2') ->select();
			$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform=2') ->select();
		}
        $hotforum = $this->model->query("SELECT id,class,b_fid,b_name,b_plat,name,icon,status FROM `zy_bbs_plate_area` WHERE class=1 AND status!='-1' ORDER BY status DESC , modifytime ASC ");
        
		$this -> assign('jump',$jump);
		$this -> assign('column',$column);
		$this -> assign('pref',$pref);
		$this -> assign('ltype',$type);
		$this -> assign('hotforum',$hotforum);
		$this -> display('edit');		
	}
	
	function edit(){
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$id = $_GET['id'];
		if(!id){
			$this -> error('id不能为空');
		}
        
        $jumpPosRes = $this -> model->table('zy_client_jump_position')->select();
        foreach($jumpPosRes as $val){
            $jumpPos[$val['operation_type']] = $val['name'];
        }
        
		$info = $this -> model -> table('zy_active_conf') -> where("id ={$id}")->find();
		if(!empty($info['advurl'])){//对互动广告链接进行处理
			$ver_advurl = explode('}{',$info['advurl']);
			foreach ($ver_advurl as $advurl_value) {
				$version_url = explode('][',$advurl_value);
				if($version_url[1] == 3700 || $version_url[1] == ''){
					$info['advurl_3700'] = $version_url[0];
				} 
				if($version_url[1] == 3800){
					$info['advurl_3800'] = $version_url[0];
				} 
			}
		}
		if($info['operation_type'] == 1 && $info['cid']){
			$info['cat'] = 1;
		}elseif(array_key_exists($info['operation_type'],$jumpPos)){
			$info['cat'] = 0;
		}else{
			$info['cat'] = $info['operation_type'];
		}
        $hotforum = $this->model->query("SELECT id,class,b_fid,b_name,b_plat,name,icon,status FROM `zy_bbs_plate_area` WHERE class=1 AND status!='-1' ORDER BY status DESC , modifytime ASC ");
		$jump = $this -> model ->table('zy_client_jump_position') -> select();
		if($type == '16'){
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=3') ->select();
		}elseif($type == '17'){
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=4') ->select();
		}else{
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=2') ->select();
			$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform =2') ->select();
		}
		
		if($info['fid']){
			$fname = $this -> bbsmodel -> table('x15_forum_forum')->field('name')-> where(array('fid'=>$info['fid']))->find();
		}
		if($info['tid']){
			$title = $this -> bbsmodel -> table('x15_forum_thread')->field('subject')-> where(array('tid'=>$info['tid']))->find();
		}
		$this -> assign('jump',$jump);
		$this -> assign('column',$column);
		$this -> assign('pref',$pref);
		$this -> assign('ltype',$type);
		$this -> assign('info',$info);
		$this -> assign('id',$id);
		$this -> assign('hotforum',$hotforum);
		$this -> assign('fname',$fname['name']);
		$this -> assign('title',$title['subject']);
		$this -> display();		
	}
	
	function changestatus(){
		$type = $_GET['type'] ? $_GET['type'] : 1;
		$status = $_GET['status'] == 1 ? 2 : 1;
		$txt = $status == 2 ? '停用' : '启用';
		$id = $_GET['id'];
		if(!$id){
			$this -> error('id不能为空');
		}
		$data['status'] = $status;
		$data['id'] = $id;
		$res = $this -> model -> table('zy_active_conf') -> save($data);
		if($res){
			$this -> writelog("智友内容管理-运营位配置 已{$txt}id为{$id}的运营位","zy_active_conf",$id,__ACTION__ ,"","edit");
			$this -> success("{$txt}成功");
		}
	}
	
	
	function add_submit(){
		$type = $_GET['type'];
		$id = $_GET['id'];
		$rank = trim($_POST['rank'],' ');
		$text = $id ? '编辑' :'添加' ;
		$optype = $_POST['cat'] > 0 ? $_POST['cat'] : $_POST['op_type'];
		// $displaytype = $_POST['display'];
		$subject = trim($_POST['subject']) ? trim($_POST['subject']) : '';
		$datedir = '/zhiyoo/';
		// $datedir = '/img/';
		$savepath = UPLOAD_PATH.$datedir;
		// $savepath = '/mnt/hgfs/goapk/newadmin.goapk.com/img/';
		if($type == '11'){
			if($id){
				$r = $this-> model -> table('zy_active_conf')->where(array('id'=>$id)) -> find(); 
				if($r['type'] == 11 && empty($r['pic_path']) && empty($_FILES['img']['size'])){
					$this -> error('闪屏运营为必须上传图片');					
				}
			}else{
				if(!$_FILES['img']['size']){
					$this -> error('闪屏运营为必须上传图片');
				}
			}
		}
		if($_FILES['img']['size']>0){
				//如果是搜索运营位，图片压缩到700 其他运营位图片压缩到200 推荐350
			if(in_array($type,array(3,5,9,10,12,11,13,15,16,17))){
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 700,
					'filesize' => 100000,
					'real_width' => 700,
				);
			}elseif($type == 2){
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 350,
					'filesize' => 100000,
					'real_width' => 350,
				);
			}else{
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 200,
					'filesize' => 100000,
					'real_width' => 200,
				);
			}
			$imgpath = $this -> _upload($_FILES['img'],$savepath,$config);
			$data['pic_path'] = $datedir.$imgpath;
		}
		if($_POST['cat'] ==='0' &&  empty($_POST['op_type'])){
			$this ->error("{$text}失败，请选择跳转的客户端的位置");
		}
		
		$data['type'] = $type;
		$data['operation_type'] = $optype;
		
		if(in_array($type,array(1,4,5,6,7,14))&&!$subject){
			$this ->error("{$text}失败，该运营位必须填写文案");
		}
		if($type == 1){
			$data['display_type'] = 2;
			if($id){  
				$img = $this -> model ->table('zy_active_conf') ->field('pic_path')-> where("id={$id}") -> find();
				if(!empty($img['pic_path'])) $data['display_type'] = 1;
			}
			if($data['pic_path']){
				$data['display_type'] = 1;
			}
		}elseif(in_array($type,array(2,3,9,12,13,15,16,17))){
			$data['display_type'] = 3;
		}elseif(in_array($type,array(4,6,14))){
			$data['display_type'] = 4;
		}elseif($type == 5){
			$data['display_type'] = 5;
		}elseif($type == 7){
			$data['display_type'] = 2;
		}
		
		$data['subject'] = trim($subject);
		$data['tid'] = $data['cid'] = $data['fid'] = $data['pid'] = 0;
		$data['url'] = '';
		if($optype == 1 && $_POST['cat'] == 1){
			if(empty($_POST['cid'])){
				$this ->error("{$text}失败，请选择栏目");
			}
			$data['cid'] = $_POST['cid'];
		}
		if($optype == 12){
			if(empty($_POST['pid'])){
				$this ->error("{$text}失败，请选择专题");
			}
			$data['pid'] = $_POST['pid'];
		}
		if($optype == 8 || $optype == 26){
			if(empty($_POST['fid'])){
				$this ->error("{$text}失败，请选择板块");
			}
			$data['fid'] = $_POST['fid'];
		}
		if($optype == 9){
			if(empty($_POST['tid'])){
				$this ->error("{$text}失败，请填写tid");
			}
			$data['tid'] = $_POST['tid'];
			//产品帖子判断
			/* $typeid = $this -> bbsmodel -> table('x15_forum_thread')->field('typeid')-> where(array('tid'=>$data['tid']))->find();
			$typename = $this -> bbsmodel -> table('x15_forum_threadclass')->field('name')-> where(array('typeid'=>$typeid['typeid']))->find();
			if($typename['name'] == '机型介绍') $data['operation_type'] = 18; */
		}
		if($optype == 10){
			if(empty($_POST['innerurl'])){
				$this ->error("{$text}失败，请填写内置跳转链接");
			}
			$data['url'] = trim($_POST['innerurl']);
		}
		if($optype == 11){
			if(empty($_POST['outerurl'])){
				$this ->error("{$text}失败，请填写内置跳转链接");
			}
			$data['url'] = trim($_POST['outerurl']);		
		}
        if($optype == 25){
			if(empty($_POST['shopurl'])){
				$this ->error("{$text}失败，请填写商城跳转链接");
			}
			$data['url'] = trim($_POST['shopurl']);		
		}
		if($optype == 28){
			if(empty($_POST['H5url'])){
				$this ->error("{$text}失败，请填写H5跳转链接");
			}
			$data['url'] = trim($_POST['H5url']);		
			$data['actionbar'] = $_POST['actionbar'];		
			$data['fullscreen'] = $_POST['fullscreen'];		
			$data['plugin'] = $_POST['plugin'];		
		}
		if($optype == 29){
			if(empty($_POST['advurl_3700']) && empty($_POST['advurl_3800'])){
				$this ->error("{$text}失败，请填写互动广告链接");
			}
			if(!empty($_POST['advurl_3700'])){
				$advurl_3700 = trim($_POST['advurl_3700']).'][3700';
				$data['advurl'] = $advurl_3700;
			} 
			if(!empty($_POST['advurl_3800'])){
				$advurl_3800 = trim($_POST['advurl_3800']).'][3800';
				if(!empty($data['advurl'])){
					$data['advurl'] .= '}{'.$advurl_3800;
				}else{
					$data['advurl'] .= $advurl_3800;
				} 
			} 
					
		}
		if($type == 3&&!$id){
			$rank = $this-> model -> table('zy_active_conf')->field('rank') ->where("type=3")-> order('rank desc')->find();			
			$data['rank'] = !$rank['rank'] ? 1 : $rank['rank']+1; 
		}
		if(in_array($type,array(5,6,7,13))&&!$id){
			$rank = $this-> model -> table('zy_active_conf')->where(array('type'=>$type,'status'=>array('gt',0)))->max('rank');			
			$data['rank'] = !$rank ? 1 : $rank+1; 
		}
		//精选或者市场的添加操作
		if(in_array($type,array(12,16,17))){
			//展示位置为空编辑添加排序设置
			if(empty($rank)){
				$maxrank = $this-> model -> table('zy_active_conf')->where(array('type'=>$type,'status'=>array('gt',0)))->max('rank');	
				$data['rank'] = !$maxrank ? 1 : $maxrank+1;
			}else{
				$exrank = $this-> model -> table('zy_active_conf')->where(array('type'=>$type,'status'=>array('gt',0)))->select();
				if($id){ //编辑
					foreach ($exrank as $item) {
						if($item['rank'] == $rank){
							if($item['id'] == $id){
								break;
							}else{
								$this ->error("{$text}失败，位置{$rank}已存在");
							}
						}else{
							$data['rank'] = $rank;	
						}
					}
				}else{ //添加
					foreach ($exrank as $item) {
						if($item['rank'] == $rank){
							$this ->error("{$text}失败，位置{$rank}已存在");
						}
					}
					$data['rank'] = $rank;
				}
			}	
		}
		
		if($id){
			$data['id'] = $id;
			$result = $this-> model -> table('zy_active_conf') -> save($data);	
		}else{
			$result = $this-> model -> table('zy_active_conf') -> add($data);	
		}
			
		if($result !== false){
			if($data['id']){
				$modtype = 'edit';
				$target_id = $id;
			}else{
				$modtype = 'add';
				$target_id = $result;
			}
			$this -> writelog("智友内容管理-运营位配置 {$text}id为{$target_id}的运营位","zy_active_conf",$target_id,__ACTION__ ,"",$modtype);
			if($type == 5){
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list_wrap/");
			} elseif($type == 7){
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list_buyer/");
			} elseif($type == 10){
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list_recommend/");
			} elseif($type == 11){
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list_splash_logo/");
			} elseif($type == 16 || $type == 17){
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list1/type/{$type}");
			}else {
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list/type/{$type}");
			}
			$this -> success("{$text}成功");
		}else{
			$this -> error("{$text}失败");
		}
	}
	
	public function forum_list(){
		$chkfid = $_GET['fid'] ? $_GET['fid']: 0;
		$grouplist = $this-> bbsmodel -> getForumList('group'); //论坛分区数据
		$forumlist = array();
		$subforumlist = array();
		foreach($grouplist as $index =>  $ginfo){
			$forums = $this ->bbsmodel -> getForumList('forum',$ginfo['fid']); //论坛板块数据
			if(!$forums){
				unset($grouplist[$index]);
				continue;
			}
			$forumlist[$ginfo['fid']] = $forums;
		}
		$this -> assign('chkfid',$chkfid);
		$this -> assign('grouplist',$grouplist);
		$this -> assign('forumlist',$forumlist);
		$this -> display();
	}
	
	function add_thread(){
		$this -> display();
	}
	
	function getthread(){
		$tid = $_GET['tid'];
		$subject = $this -> bbsmodel -> table('x15_forum_thread') -> field('subject') -> where("tid={$tid}") ->find();
		if($subject){			
			echo $subject['subject'];
		}else{
			echo 0;
		}
	}
	
	function editsubject(){
		$id = $_GET['id'];
		if(!id){
			$this ->error('修改失败');
		}
		$subject = $this -> model -> table('zy_active_conf') ->field('subject') ->where("id={$id}") ->find();
		$this ->assign('subject',$subject['subject']);
		$this ->assign('id',$id);
		$this -> display();
	}
	
	function editsubject_sub(){
		$id = $_GET['id'];
		$subject = $_POST['subject'];
		if(!id||!subject){
			$this ->error('修改失败');
		}
		$data['id'] = $id;
		$data['subject'] = $subject;
		
		$res = $this -> model -> table('zy_active_conf') ->save($data);
		if($res !== false){
			$this->writelog("智友内容管理-运营位配置 修改id为{$id}的文案","zy_active_conf",$id,__ACTION__ ,"","edit");
			$this->success('文案修改成功');
		}else{
			$this ->error('文案修改失败');
		}
		
		$this -> display();
		
		
	}
	
	function editrank(){
		$type = $_GET['type'];
		foreach($_POST['order'] as $id =>$rank){
			$this->model->table('zy_active_conf')->where('id='.$id)->save(array('rank'=>$rank));	
			$this -> writelog("智友内容管理-运营位配置 已编辑id为{$id}运营位的优先级为".$rank,"zy_active_conf",$id,__ACTION__ ,"","edit");
		}
		
		if($type == 5){
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list_wrap/");
		} else {
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list/type/{$type}");
		}
		$this -> success("编辑优先级成功");
		
		
	}
	
	function del(){
		$id = $_GET['id'];
		$type = $_GET['type'];
		if(empty($id)){
			$this->error('id为空，删除失败');
		}
		$data['id'] = $id;
		$data['status'] = 0;
		$res = $this -> model -> table('zy_active_conf') -> save($data);
		if($res){			
			$this -> writelog("智友内容管理-运营位配置 已删除id为{$id}的运营位","zy_active_conf",$id,__ACTION__ ,"","del");
			if($type == 7) $this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list_buyer/");
			else $this -> assign('jumpUrl',"/index.php/Zhiyoo/ActiveConf/active_list/type/$type");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function active_list_wrap(){
		$_GET['type'] = 5;
		$this->active_list();
	}
	
	function active_list_buyer(){
		$_GET['type'] = 7;
		$this->active_list();
	}
	
	function active_list_recommend(){
		$_GET['type'] = 10;
		$this->active_list();
	}
	
	function active_list_splash_logo(){
		$_GET['type'] = 11;
		$this->active_list();
	}
	
	function push_list(){
		$_GET['type'] = 8;
		$this->active_list();
	}
	function active_list1(){
		$_GET['type'] = empty($_GET['type']) ? 17 : $_GET['type'];
		$this->active_list();
	}

	function edittm(){
		$type = intval($_GET['type']);
		$id = intval($_GET['id']);
		$where['type'] = $type;
		$where['id'] = $id;
		$result = $this -> model -> table('zy_active_conf') -> where($where) -> find();
		$this -> assign('findone',$result);
		$this -> display();
	}
	
	function edittm_do(){
		$id = intval($_GET['id']);
		$type = intval($_GET['type']) ? $_GET['type'] : 11;
		$result = $this -> model -> table('zy_active_conf') -> where('id = '.$id) -> find();
		if($result){
			$starttime = strtotime($_POST['starttime']);
			$endtime = strtotime($_POST['endtime']);
			if($starttime >= $endtime){
				$this -> error('时间设置无效');
			}
			
 		}else{
			$this -> error('操作无效');
		}
		$data['start_tm'] =  $starttime;
		$data['end_tm'] =  $endtime;
		$where = array(
			'_string' => 'id<>'.$id.' and type = '.$type.' and start_tm <='.$data['end_tm'].' and end_tm >='.$data['start_tm']
		);
		$count = $this -> model -> table('zy_active_conf') -> where($where) -> count();
		echo $this -> model -> getLastsql();
		if($count){
			$this -> error('时间段已经被占用，请合理分配');
		}
		$result = $this -> model -> table('zy_active_conf') -> where('id = '.$id) -> save($data);
		$this -> success('修改成功');
		
	}
	function changestatus_wrap(){
		$status = $_GET['status'] == 1 ? 2 : 1;
		$txt = $status == 2 ? '停用' : '启用';
		$id = $_GET['id'];
		if(!$id){
			$this -> error('id不能为空');
		}
		
		if($status == 2){
			$count = $this -> model -> table('zy_active_conf') -> where('type=5 and status = 1')->count();
			if($count <= 1){
				$this -> error("最少保留一个内容且为开启状态");
			}
		}
		$data['id'] = $id;
		$data['status'] = $status;
		$res = $this -> model -> table('zy_active_conf') -> save($data);
		if($res){
			$this -> writelog("智友内容管理-运营位配置 已{$txt}id为{$id}的运营位","zy_active_conf",$id,__ACTION__ ,"","edit");
			$this -> success("{$txt}成功");
		}
	}
	
	function del_wrap(){
		$id = $_GET['id'];
		if(empty($id)){
			$this->error('id为空，删除失败');
		}
		$count = $this -> model -> table('zy_active_conf') -> where(array('type'=>5,'status'=>1, 'id'=>array('neq',$id)))->count();
		if($count < 1){//众测轮播图页面，添加多条数据后，其他数据全为禁用状态，留一条启用状态数据
			$this -> error("最少保留一个内容且为开启状态");
		}
		$data['id'] = $id;
		$data['status'] = 0;
		$res = $this -> model -> table('zy_active_conf') -> save($data);
		if($res){			
			$this -> writelog("智友内容管理-运营位配置 已删除id为{$id}的运营位","zy_active_conf",$id,__ACTION__ ,"","del");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	public function pushedit(){
		$id = $_GET['id'];		
		$canedittime = 1;
		if($id){
			$info = $this -> model -> table('zy_active_conf') -> where("id ={$id} and type= 8") -> find();
			$info_push = $this -> model -> table('zy_active_conf_push')-> where("aid ={$id} and type= 8") -> find();
			if(!$info){
				$this -> error('push 数据不存在');
			}
			if($info_push['pid']>0){
				$canedittime = 0;
			}
			if($info['operation_type'] == 1 && $info['cid']){
				$info['cat'] = 1;
			}elseif($info['operation_type']<=7||$info['operation_type']>=13){
				$info['cat'] = 0;
			}else{
				$info['cat'] = $info['operation_type'];
			}
			
			if($info['fid']){
				$fname = $this -> bbsmodel -> table('x15_forum_forum')->field('name')-> where(array('fid'=>$info['fid']))->find();
				$info['forumname'] = $fname['name'];
			}
			if($info['pid']){
				$columnname = $this -> model -> table('zy_column_conf')->field('name')-> where(array('cid'=>$info['pid']))->find();
				$info['columnname'] = $columnname['name'];
			}
			if($info['tid']){
				$title = $this -> bbsmodel -> table('x15_forum_thread')->field('subject')-> where(array('tid'=>$info['tid']))->find();
			}
			
			$img_host_out_count = count($this->img_host_out);
			$rand = mt_rand(0,$img_host_out_count-1);
			$json_arr = array();
			if($info['display_type'])$json_arr['DISPLAY_TYPE'] = $info['display_type'];
			if($info['subject'])$json_arr['TEXT'] =  $info['subject'];
			// if($info_push['subject_top'])$json_arr['TICKER_TEXT'] =  $info_push['subject_top'];
			if($info_push['subject_desc'])$json_arr['T2'] =  $info_push['subject_desc'];
			if($info['display_type'] == 7)$json_arr['T3'] =  $info_push['subject_btn'];
			if($info['pic_path'] && in_array($info['display_type'],array(6,7)))$json_arr['SMALL_ICON'] =  $this->img_host_out[$rand].$info['pic_path'];
			if($info['pic_path'] && in_array($info['display_type'],array(5,8)))$json_arr['BIG_ICON'] =  $this->img_host_out[$rand].$info['pic_path'];
			$json_arr['DATA'] =  $this->active_conf($info);
			$json_str = json_encode($json_arr);
			$json_str = $this->decodeUnicode($json_str);
			$json_str = htmlspecialchars($json_str);

			$this -> assign('title',$title['subject']);
			$step_tm = explode(',',$info_push['step_tm_area']);
			$info_push['step_tm'] = $step_tm;
			$this -> assign('info_push',$info_push);
			$this -> assign('aid',$id);
			$this -> assign('json_str',$json_str);
		}else{
			$this -> assign('add',1);
		}
		$jump = $this -> model ->table('zy_client_jump_position') -> select();
		$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=2') ->select();
		$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform =2') ->select();
		$this -> assign('canedittime',$canedittime);
		$this -> assign('jump',$jump);
		$this -> assign('column',$column);
		$this -> assign('pref',$pref);
		$this -> assign('info',$info);
		$this -> assign('fname',$fname['name']);
		$this -> display();		
	}
	
	public function pushadd(){
		$canedittime = 1;
		$jump = $this -> model ->table('zy_client_jump_position') -> select();
		$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=2') ->select();
		$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform =2') ->select();
		$this -> assign('canedittime',$canedittime);
		$this -> assign('jump',$jump);
		$this -> assign('column',$column);
		$this -> assign('pref',$pref);
		$this -> assign('add',1);
		$this -> display('pushedit');
	}
	
	public function dopushedit(){
		$optype = $_POST['cat'] > 0 ? $_POST['cat'] : $_POST['op_type'];
		$subject = trim($_POST['subject']) ? trim($_POST['subject']) : '';
		if(isset($_POST['starttime'])&&isset($_POST['endtime'])){
			$starttime = strtotime($_POST['starttime']);
			$endtime = strtotime($_POST['endtime']);
			if($starttime >= $endtime){
				$this -> error('时间设置无效');
			}
			$data['start_tm'] =  $starttime;
			$data['end_tm'] = $endtime;
		}

		if(!$_POST['aid'] && !$_POST['add']){
			$this -> error('编辑无效');
		}
		$datedir = '/zhiyoo/';
		$savepath = UPLOAD_PATH.$datedir;
		if($_FILES['img']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 2000,
				'filesize' => 100000,
				'real_width' => 2000,
			);
			$imgpath = $this -> _upload($_FILES['img'],$savepath,$config);
			$data['pic_path'] = $datedir.$imgpath;
		}
		if($_POST['cat'] ==='0' &&  empty($_POST['op_type'])){
			$this ->error("{$text}失败，请选择跳转的客户端的位置");
		}
		
		$data['type'] = 8;
		$data['operation_type'] = $optype;
		
		$data['display_type'] = intval($_POST['showclass']);
        if(!$data['display_type']) $this ->error("{$text}失败，请选择展示样式");
		$data['subject'] = $subject;
		$data['tid'] = $data['cid'] = $data['fid'] = $data['pid'] = 0;
		$data['url'] = '';
		if($optype == 1 && $_POST['cat'] == 1){
			if(empty($_POST['cid'])){
				$this ->error("{$text}失败，请选择栏目");
			}
			$data['cid'] = $_POST['cid'];
		}
		if($optype == 12){
			if(empty($_POST['pid'])){
				$this ->error("{$text}失败，请选择专题");
			}
			$data['pid'] = $_POST['pid'];
		}
		if($optype == 8){
			if(empty($_POST['fid'])){
				$this ->error("{$text}失败，请选择板块");
			}
			$data['fid'] = $_POST['fid'];
		}
		if($optype == 9){
			if(empty($_POST['tid'])){
				$this ->error("{$text}失败，请填写tid");
			}
			$data['tid'] = $_POST['tid'];
			/* $typeid = $this -> bbsmodel -> table('x15_forum_thread')->field('typeid')-> where(array('tid'=>$data['tid']))->find();
			$typename = $this -> bbsmodel -> table('x15_forum_threadclass')->field('name')-> where(array('typeid'=>$typeid['typeid']))->find();
			if($typename['name'] == '机型介绍') $data['operation_type'] = 18; */
		}
		if($optype == 10){
			if(empty($_POST['innerurl'])){
				$this ->error("{$text}失败，请填写内置跳转链接");
			}
			$data['url'] = $_POST['innerurl'];
		}
		if($optype == 11){
			if(empty($_POST['outerurl'])){
				$this ->error("{$text}失败，请填写内置跳转链接");
			}
			$data['url'] = $_POST['outerurl'];		
		}
		$data_push = array();
		// $data_push['subject_top'] = trim($_POST['subject_top']);
		$data_push['subject_desc'] = trim($_POST['subject_desc']);
		$data_push['subject_btn'] = trim($_POST['subject_btn']);
		$data_push['pushuser'] = trim(htmlspecialchars($_POST['pushuser']));
        if($data['display_type'] === 6){
            if(!$subject || !$data_push['subject_desc'] ){
                $this ->error("编辑失败，该运营位必须填写文案");
            }
        }elseif($data['display_type'] === 7){
            if(!$subject || !$data_push['subject_desc'] || !$data_push['subject_btn'] ){
                $this ->error("编辑失败，该运营位必须填写文案");
            }
            
        }
		
		$step_starttm = intval($_POST['step_starttm']);
		$step_endtm = intval($_POST['step_endtm']);
		if($step_starttm || $step_endtm){			
			if($step_starttm >= $step_endtm){
				$this -> error('开始时间必须小于结束时间');
			}
			if(isset($_POST['step_starttm']) && isset($_POST['step_endtm'])){
				$data_push['step_tm_area'] = "{$step_starttm},{$step_endtm}";
			}
		}
		$sub_query = '';
		if($_POST['aid']){
			$sub_query = 'id <> '.$_POST['aid'].' and ';
		}
		$cnt = $this-> model -> table('zy_active_conf')->where(array('_string' => $sub_query."type = 8 and start_tm <={$data['end_tm']} and end_tm >=".$data['start_tm'])) -> count();
		if($cnt){
			$this -> error('时间段已经被占用，请合理分配');
		}
		
			
			
		if(!$_POST['add']){
			/* //查看该条记录的时间是否有变更
			$pushtime = $this->model->table('zy_active_conf')->where('id='.$_POST['aid'])->find();
			$pushstep = $this->model->table('zy_active_conf_push')->where('aid='.$_POST['aid'])->find();
			$pushstep['step_tm_area'] = $pushstep['step_tm_area'] ? $pushstep['step_tm_area'] :'';
			$data_push['step_tm_area'] = $data_push['step_tm_area'] ? $data_push['step_tm_area'] :'';
			if($pushtime['start_tm'] != $data['start_tm'] || $pushtime['end_tm'] != $data['end_tm'] || $data_push['step_tm_area'] != $pushstep['step_tm_area']){
			}  */
			$data['update_tm'] = time();
			$result = $this-> model -> table('zy_active_conf')->where('id ='.$_POST['aid'].' and type=8') -> save($data);
			$result1 = $this-> model -> table('zy_active_conf_push')->where('aid ='.$_POST['aid'].' and type=8') -> save($data_push);
			$target_id = $_POST['aid'];
		}else{
			$data['type'] = $data_push['type'] = 8;
			$result = $this-> model -> table('zy_active_conf')->add($data);
			$data_push['aid'] = $result;
			$result1 = $this-> model -> table('zy_active_conf_push')->add($data_push);
			$target_id = $result;
		}
			
		if($result || $result1){
			if(!$_POST['add']){
				$this -> writelog("智友内容管理-运营位配置-PUSH 编辑type为8的运营位","zy_active_conf",$target_id,__ACTION__ ,"","edit");
				$this -> success("编辑成功");
			}else{
				$this -> writelog("智友内容管理-运营位配置-PUSH 添加type为8的运营位","zy_active_conf",$target_id,__ACTION__ ,"","add");
				$this -> success("添加成功");
			}
		}else{
				$this -> error("编辑失败");
		}
	}
	
	private function decodeUnicode($str)
	{
		return  preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
			create_function(
				'$matches',
				'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
			),
			$str);
	}
	
	private function active_conf($val){
		$arr['DATA'] = array();
		switch(intval($val['operation_type'])){
			case 0://表示无效操作
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 0,
				);
				break;
			case 1://表示跳转到智友精选页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 1,
					'ID' => $val['cid']
				);
				break;
			case 2://表示跳转到论坛页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 2
				);
				break;
			case 3://表示跳转到机型库首页
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 3
				);
				break;
			case 4://表示跳转到全部机型页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 4
				);
				break;
			case 5://表示跳转到个性选机页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 5
				);
				break;
			case 6://表示跳转到机型详情页面===============todo
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 6,
					'ID' => 1
				);
				break;
			case 7://表示跳转到我的页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 7
				);
				break;
			case 8://表示跳转到指定的版块页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 8,
					'ID'=> $val['fid'],
					'TITLE' => $val['forumname'], //TITLE:"版块的标题",
					'CID'=> 0 //"分类ID，如果没有或者没有对应的ID,默认第一个"
				);
				break;
			case 9://表示跳转到帖子详情页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 9,
					'URL' => "http://api.forum.anzhi.com/mobile_wap.php?mod=viewthread&tid={$val['tid']}" 
				);
				break;
			case 10://表示跳转到内部网页页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 10,
					'URL'=> $val['url'],
					'TITLE' => $val['subject']
				);
				break;
			case 11://表示跳转到外部网页页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 11,
					'URL'=> $val['url'],
				);
				break;
			case 12://表示专题列表
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 12,
					'TID'=> $val['pid'],
					'TITLE' => $val['columnname']
				);
				break;
			case 13://表示跳转到一级搜索页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 13
				);
				break;
			case 14://表示跳转到大众评测首页
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 14
				);
				break;
			case 15://表示跳转到我的众测页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 15
				);
				break;
			case 16://表示跳转到普通发帖页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 16
				);
				break;
			case 17://表示跳转到快速发帖页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 17
				);
				break;
			case 18://表示跳转到产品详情页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 18,
					'URL'=> "http://api.forum.anzhi.com/mobile_wap.php?mod=viewthread&tid={$val['tid']}" 
				);
				break;
			case 19://表示跳转到设置页面
				$arr['DATA'] = array(
					'OPERATION_TYPE'=> 19
				);
				break;
		}
		return $arr['DATA'];
	}
	
	protected function _upload($file,$savepath,$config){
		include dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
    
    public function delimg(){
        $id = intval($_GET['id']);
        $result = $this-> model -> table('zy_active_conf')->where(array('id'=>$id)) -> save(array('pic_path'=>''));	
		if($result){
			$this -> writelog("智友内容管理-运营位配置 删除运营位ID为{$id}的图片","zy_active_conf",$id,__ACTION__ ,"","del");
			echo json_encode(array('su'=>1));
		}else{
			echo json_encode(array('su'=>0));
		}
    }
}