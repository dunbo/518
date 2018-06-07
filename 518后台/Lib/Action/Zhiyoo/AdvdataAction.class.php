<?php 

Class AdvdataAction extends CommonAction{
	function result_list(){
		$platform = $_GET['platform'] ? $_GET['platform'] : 0 ;
		$position = $_GET['position'] ? $_GET['position'] : 0 ;
		$order_sql = !$_GET['type']?  'ad.addtime desc': "{$_GET['type']} {$_GET['order']},ad.addtime desc";
		$th[$_GET['type']] = $_GET['order'] == 'asc' ? 'desc' : 'asc';
		$where_sql = array();
		$filterurl = '';
		$bbs_model = D('Zhiyoo.Zhiyoo');
			$where_sql['ad.status'] = 1;
			$start = strtotime($_GET['start_tm']);
			$end = strtotime($_GET['end_tm']);
			$title = $_GET['title'];
			$tid = $_GET['tid'];
			$platform = $_GET['platform'];
			$column = $_GET['column'];
			if($start) {
				$where_sql['ad.addtime'][] = array('egt',$start);	
				$filterurl .= '/start_tm/'.$_GET['start_tm'];
				
			}
			if($end) {
				$where_sql['ad.addtime'][] = array('elt',$end);	
				$filterurl .= '/end_tm/'.$_GET['end_tm'];
			}	
			if($title) {
				$where_sql['ad.ext_title'] = array('like',"%{$title}%");	
				$filterurl .= '/title/'.$title;
			}
			if($tid) {
				$where_sql['ad.advgid'] = $tid;	
				$filterurl .= '/tid/'.$tid;
			}	
			if($platform) {
				$where_sql['ad.platform'] = $platform;	
				$filterurl .= '/platform/'.$platform;
			}else{
				$where_sql['ad.platform'] = array('in','1,2,4');
			}
			if($position) {
				$where_sql['ad.position'] = $position;	
				$filterurl .= '/position/'.$position;
			}
			
		$postime = $_GET['postime'] ? $_GET['postime'] :'online';//var_dump($postime);
		if( $postime == 'waiton'){
			$where_sql['ad.starttime'] = array('exp','>='.time().' or ad.endtime = 0');	//待上线页面开始时间大于当前时间
		}
		elseif($postime == 'online'){
			$where_sql['ad.starttime'] = array('elt',time());	
			$where_sql['ad.endtime'] = array('egt',time());	
		}
		elseif($postime == 'outline'){
			$where_sql['ad.endtime'] = array(array('elt',time()),array('neq',0));
		}
		$postimeurl .= '/postime/'.$postime;
		
        $extpos = $bbs_model->extpos();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$count = $bbs_model -> table('zy_advdata ad') -> where($where_sql)->count();
		$prepage = isset($_GET['lr']) ? $_GET['lr'] : 20;
		$Page = new Page($count,$prepage , $param);
		$result = $bbs_model -> table("zy_advdata ad")->join('LEFT JOIN zy_schedule_platform p ON ad.platform=p.platform LEFT JOIN zy_schedule_position po ON ad.position=po.position')-> where($where_sql)->order($order_sql)-> limit("{$Page->firstRow},{$Page->listRows}")->select();
		//echo $bbs_model ->getlastsql();
		foreach($result as $key => $val){
			if($result[$key]['img_path1'])$result[$key]['img_path1'] = IMGATT_HOST.'/'.$val['img_path1'];
			if($result[$key]['img_path2'])$result[$key]['img_path2'] = IMGATT_HOST.'/'.$val['img_path2'];
		}//var_dump($result);
		
		//var_dump($Page);
		$grouplist = $bbs_model->gettaggroup();
		//保留标签功能
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
			$filterurl .= '/lr/'.$lr;
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
			$filterurl .= '/p/'.$p;
		}else{
			$p = 1;
		}
		//
		$platform = $bbs_model ->table("zy_schedule_platform")->where(array('status'=>1))->select();
		$position = $bbs_model ->table("zy_schedule_position")->select();
        
		$this -> assign('extpos',$extpos);
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('postime',$postime);
		$this -> assign('postimeurl',$postimeurl);
		$this -> assign('srcurl',$srcurl);
		$this -> assign('platform',$platform);
		$this -> assign('position',$position);
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);//var_dump($result);
		$this -> assign(array('result'=>$result,'th'=>$th,'filterurl'=>$filterurl));
		$this -> display();
	}
	
	function add(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$platform = $bbs_model ->table("zy_schedule_platform")->where(array('status'=>1))->select();
		$position = $bbs_model ->table("zy_schedule_position")->select();
		$this -> assign('position',$position);
		$this -> assign('platform',$platform);
		$this->display();
	}
	
	function doadd(){
		$_POST['endtime'] < $_POST['starttime'] && $this -> error("开始时间须小于结束时间");
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advdata_model = D('Zhiyoo.advdata');
		$advdata_gidmodel = D('Zhiyoo.advdata_gid');
		$pos_confmodel = D('Zhiyoo.pos_conf');
		$zy_schedulemodel = D('Zhiyoo.schedule');
		$data['ext_title'] = trim($_POST['ext_title']);
		$data['description'] = $_POST['description'];
		$url = trim($_POST['url']);
		$url = preg_match('/^http:\/\//i',$url) ? $url : 'http://'.$url ;
		$data['url'] = $url;
		$pp = $_POST['pp'];
		$data['starttime'] = $data1['starttime'] = strtotime($_POST['starttime']);
		$data['endtime'] = $data1['endtime'] = strtotime($_POST['endtime']);
		$data['addtime'] = $data1['addtime'] = time();
		$data1['source'] = 2;
		$data1['level'] = 999;
		
		$yearmonth = date("Ym");
		$day=date('d');
		$datedir = '/zhiyoo/';
		$savepath = UPLOAD_PATH.$datedir;

		if($_FILES['img1']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 200,
				'filesize' => 100000,
				'real_width' => 200,
			);
			$imgpath = $this->_upload($_FILES['img1'],$savepath,$config);
			$data['img_path1'] = $datedir.$imgpath;
		}
		
		if($_FILES['img2']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 800,
				'filesize' => 200000,
				'real_width' => 700,
			);
			$imgpath = $this->_upload($_FILES['img2'],$savepath,$config);	
			$data['img_path2'] = $datedir.$imgpath;			
		}
		
		if(is_array($pp)){
			foreach($pp as $val){//遍历添加平台位置
				$val2 = explode('_',$val);
				$data['platform'] = $data1['platform'] = $val2[0];
				$data['position'] = $data1['position'] = $val2[1];
				if(!isset($data['advgid']))$data['advgid'] = $advdata_gidmodel->table('zy_advdata_gid')->add(array('status'=>1));///确保内容不重复，生成广告组ID。
				$advdid = $zy_schedulemodel -> table('zy_schedule') -> data(array('status'=>-1)) -> add();
				$bbs_model-> table("zy_schedule")-> where(array('id'=>$advdid))->delete();
				$data['advdid'] = $advdid;
				$advdata_model->table('zy_advdata')->add($data);
				$data1['advid'] = $advdid;
				if($advdid !== false){
					$posid = $pos_confmodel->table('zy_pos_conf')->add($data1);
					$result = $bbs_model->table("zy_advdata")->where(array('advdid'=>$advdid))->save(array('posid'=>$posid));
				}
				
			}
		}else{
			$result=false;
		}
		if($result !== false){
			$this -> writelog("智友内容管理-广告素材管理-广告素材 已添加标题为【{$_POST['ext_title']}】的广告","zy_advdata",$advdid,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/Advdata/result_list/postime/".$_GET['postime']);
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advdid = $_GET['advdid'];
		$result = $bbs_model ->table("zy_advdata")->where(array('advdid'=>$advdid))->find();//取内容
		$result2 = $bbs_model->table("zy_advdata")->where(array('advgid'=>$result['advgid'],'status'=>1))->select();//取所有内容
		foreach($result2 as $val){
			$adv[] = $val;
			$checked[] = $val['platform'].'_'.$val['position'];
			if($advdid != $val['advdid'])
			$disable[] = $val['platform'].'_'.$val['position'];
		}
		$platform = $bbs_model ->table("zy_schedule_platform")->where(array('status'=>1))->select();
		$position = $bbs_model ->table("zy_schedule_position")->select();
		$this -> assign('position',$position);
		$this -> assign('platform',$platform);
		$this -> assign('checked',$checked);
		$this -> assign('disable',$disable);
		$this -> assign('result',$result);
		$this->display();
	}
	
	function doedit(){
		//$_POST['endtime'] < $_POST['starttime'] && $this -> error("开始时间须小于结束时间");
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advdata_model = D('Zhiyoo.advdata');
		$pos_confmodel = D('Zhiyoo.pos_conf');
		$zy_schedulemodel = D('Zhiyoo.schedule');
		
		$data['ext_title'] = $ext_title = trim($_POST['ext_title']);
		$data['description'] = $description = $_POST['description'];
		$url = trim($_POST['url']);
		$url = preg_match('/^http:\/\//i',$url) ? $url : 'http://'.$url ;
		$data['url'] = $url;
		$advdid = $_GET['advdid'];
		$pp = $_POST['pp'];
		//$starttime = strtotime($_POST['starttime']) ;
		//$posdata['starttime'] = $data['starttime'] = $starttime ? $starttime : 0;
		//$endtime = strtotime($_POST['endtime']);
		//$posdata['endtime'] = $data['endtime'] = $endtime ? $endtime : 0;
		$posdata['addtime'] = $data['addtime'] = $nowtime=time();
		$yearmonth = date("Ym");
		$day=date('d');
		$datedir = '/zhiyoo/';
		$savepath = UPLOAD_PATH.$datedir;

		if($_FILES['img1']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 200,
				'filesize' => 100000,
				'real_width' => 200,
			);
			$imgpath = $this->_upload($_FILES['img1'],$savepath,$config);
			$data['img_path1'] = $datedir.$imgpath;
		}
		
		if($_FILES['img2']['size']>0){
			$config = array(
				'tmp_file_dir' => '/tmp/',
				'width' => 800,
				'filesize' => 200000,
				'real_width' => 700,
			);
			$imgpath = $this->_upload($_FILES['img2'],$savepath,$config);	
			$data['img_path2'] = $datedir.$imgpath;			
		}
		
		$content = $bbs_model->table("zy_advdata")->where(array('advdid'=>$advdid))->field('platform,position,img_path1,img_path2,advgid,starttime,endtime')->find();//检查当前内容是否被取消
		$posdata['starttime'] = $data['starttime'] = $content['starttime'];
		$posdata['endtime'] = $data['endtime'] = $content['endtime'];
			$copp = $content['platform'].'_'.$content['position'];//var_dump($copp );exit;
			if(!isset($data['img_path1'])){
				$data['img_path1'] = $content['img_path1'] ? $content['img_path1'] : '';
			}
			if(!isset($data['img_path2'])){
				$data['img_path2'] = $content['img_path2'] ? $content['img_path2'] : '';
			}
			if(array_search($copp,$pp)===false || !isset($pp)){//内容被取消
				$bbs_model->table('zy_advdata')->where(array('advdid'=>$advdid))->save(array('deltime'=>$nowtime,'status'=>-1));
				$bbs_model->table('zy_pos_conf')->where(array('advdid'=>$advdid,'source'=>2))->save(array('status'=>-1));
				$this -> writelog("智友内容管理-广告素材管理-广告素材 已修改advdid为{$advdid}的广告 取消平台位置{$copp}","zy_advdata",$advdid,__ACTION__ ,"","edit");
			}
			$posdata['source'] = 2;
			$posdata['level'] = 999;
			$posdata['column'] = 0;
			if(is_array($pp)){
				foreach($pp as $val){//遍历添加平台位置
					$val2 = explode('_',$val);
					$data['platform'] = $posdata['platform'] = $val2[0];
					$data['position'] = $posdata['position'] = $val2[1];
					if($copp == $val){//更新当前内容信息
						$res = $advdata_model->table('zy_advdata')->where("advdid ={$advdid}")->save($data);	
					}
					else{
						$data['advgid'] = $content['advgid'] ;
						$aid = $zy_schedulemodel -> table('zy_schedule') -> data(array('status'=>-1)) -> add();
                        $bbs_model->table('zy_schedule')->where(array('id'=>$aid))->delete();
						$data['advdid'] = $aid;
						$advdata_model->table('zy_advdata')->add($data);
						$posdata['advid'] = $aid;
						if($aid !== false){
							$posid = $pos_confmodel->table('zy_pos_conf')->add($posdata);
                            $bbs_model->table('zy_advdata')->where(array('advdid'=>$aid))->save(array('posid'=>$posid));
							$this -> writelog("智友内容管理-广告素材管理-广告素材 已修改advdid为{$advdid}的广告 添加平台位置{$val}","zy_advdata",$advdid,__ACTION__ ,"","edit");
						}
					}
				
				}
			}
		$this -> writelog("智友内容管理-广告素材管理-广告素材 已修改advdid为{$advdid}的广告 标题为【{$ext_title}】URL为【{$url}】","zy_advdata",$advdid,__ACTION__ ,"","edit");
		if($_GET['from']=='care')$this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/result_list/postime/{$_POST['source']}");
		elseif($_GET['from']=='wrap')$this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/result_wrap/postime/{$_POST['source']}");
		elseif($_GET['from']=='dailyrecom')$this -> assign('jumpUrl',"/index.php/Zhiyoo/Care/dailyrecom/postime/{$_POST['source']}");
		else $this -> assign('jumpUrl',"/index.php/Zhiyoo/Advdata/result_list/postime/{$_POST['source']}");
		$this -> success("修改成功");
	}
	
	function del_list(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$ids = $_GET['posid'];
		$time = time();
		$result = $bbs_model->table('zy_advdata')->where(array('posid'=>array('in',$ids)))->save(array('deltime'=>$time,'status'=>-1));
		$result = $bbs_model->table('zy_pos_conf')->where(array('posid'=>array('in',$ids)))->save(array('status'=>-1));
		if($result!==false){
			//echo 1;
			$this -> writelog("智友内容管理-广告素材管理-广告素材 已删除posid为{$ids}的广告的内容记录","zy_advdata",$ids,__ACTION__ ,"","del");
			$this -> success("删除成功");
		}else{
			//echo 2;
			$this -> error("删除失败");
		}
	}
	
	function over(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$ids = $_GET['posid'];
		$endtime = time()-1;
		$result = $bbs_model->table('zy_advdata')->where(array('posid'=>array('in',$ids)))->save(array('endtime'=>$endtime));
		$result = $bbs_model->table('zy_pos_conf')->where(array('posid'=>array('in',$ids)))->save(array('endtime'=>$endtime));
		if($result !== false){
			$this -> writelog("智友内容管理-广告素材管理-广告素材 已结束posid为{$ids }的广告","zy_advdata",$ids,__ACTION__ ,"","edit");
			//$this -> assign('jumpUrl',"/index.php/Zhiyoo/Advdata/postime/".$_GET['postime']);
			$this -> success("结束成功");
		}else{
			$this -> error("结束失败");
		}
	}
	
	function reonline(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$posid = $_GET['posid'];
		if(is_numeric($posid)){
			$result = $bbs_model->table('zy_advdata')->where(array('posid'=>$posid,'status'=>1))->find();
			$platform = $result['platform'];
			$platform = $bbs_model->table('zy_schedule_platform')->where(array('platform'=>$platform))->find();//查询原平台
			$platform = $platform['platformname'];
			$position = $result['position'];
			$position = $bbs_model->table('zy_schedule_position')->where(array('position'=>$position))->find();//查询原位置
			$position = $position['positionname'];
		}
		else{
			$this -> error("id错误");
		}
		$result['starttime'] = $result['starttime'] ? date('Y-m-d H:i:s',$result['starttime']) : '';
		$result['endtime'] = $result['endtime'] ? date('Y-m-d H:i:s',$result['endtime']) : '';
		$this->assign('result',$result);
		$this->assign('platform',$platform);
		$this->assign('position',$position);
		//$this->assign('tittle',$result['ext_title']);
		$this->display();
	}
	
	function doreonline(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$posid = $_GET['posid'];
		!$_POST['starttime'] && $this -> error("时间不能为空");
		!$_POST['endtime'] && $this -> error("时间不能为空");
		$_POST['endtime'] < $_POST['starttime'] && $this -> error("开始时间须小于结束时间");
		$starttime = strtotime($_POST['starttime']);
		$endtime = strtotime($_POST['endtime']);//var_dump($advid);
		$time = time();
		$result = $bbs_model->table('zy_advdata')->where(array('posid'=>array('in',$posid)))->save(array('addtime'=>$time,'starttime'=>$starttime,'endtime'=>$endtime));
		$result = $bbs_model->table('zy_pos_conf')->where(array('posid'=>array('in',$posid)))->save(array('addtime'=>$time,'starttime'=>$starttime,'endtime'=>$endtime));
		if($result !== false){
			$this -> writelog("智友内容管理-广告素材管理-广告素材 已经编辑时间posid为{$posid}内容","zy_advdata",$posid,__ACTION__ ,"","edit");
			$this -> success("重新编辑时间成功");
		}
		else{
			$this -> error("重新编辑时间失败");
		}
	}
	
	function copy(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advdid = $_GET['advdid'];
        $result = $bbs_model ->table("zy_advdata")->where(array('advdid'=>$advdid))->find();//取内容
		$result['starttime'] = $result['starttime'] ? date('Y-m-d H:i:s',$result['starttime']) : '';
		$result['endtime'] = $result['endtime'] ? date('Y-m-d H:i:s',$result['endtime']) : '';
		$result2 = $bbs_model->table("zy_advdata")->where(array('advgid'=>$result['advgid'],'status'=>1))->select();//取所有内容
		foreach($result2 as $val){
			$adv[] = $val;
			$checked[] = $val['platform'].'_'.$val['position'];
		}
		$platform = $bbs_model ->table("zy_schedule_platform")->where(array('status'=>1))->select();
		$position = $bbs_model ->table("zy_schedule_position")->select();
		$this -> assign("platform", $platform);
		$this -> assign("position", $position);
		$this -> assign("checked", $checked);
		$this -> assign("result", $result);
		$this->display();
	}
	function docopy(){
		$bbs_model = D('Zhiyoo.Zhiyoo');
		$advdata_model = D('Zhiyoo.advdata');
		$pos_confmodel = D('Zhiyoo.pos_conf');
		$zy_schedulemodel = D('Zhiyoo.schedule');
		
		$advdid = $_GET['advdid'];
		/*$starttime = strtotime($_POST['starttime']) ;
		$posdata['starttime'] = $data['starttime'] = $starttime ? $starttime : 0;
		$endtime = strtotime($_POST['endtime']);
		$posdata['endtime'] = $data['endtime'] = $endtime ? $endtime : 0;*/
		$posdata['addtime'] = $data['addtime'] = $nowtime=time();
		$pp = is_array($_POST['pp']) ? $_POST['pp'] : $this->error('请选择位置');
		
		$result = $bbs_model ->table("zy_advdata")->where(array('advdid'=>$advdid))->find();//获取原始数据s
		$posdata['starttime'] = $data['starttime'] =  $result['starttime'] ;
		$posdata['endtime'] = $data['endtime'] =  $result['endtime'] ;
		$data['advgid'] = $result['advgid'] ;
		$yearmonth = date("Ym");
		$day=date('d');
		$savepath = UPLOAD_PATH.'/'.$yearmonth.'/'.$day.'/'.'';
		/*if($_FILES['img1']['size']>0||$_FILES['img2']['size']>0){
			$imginfo = $this->_upload($savepath);
		}
		if($imginfo['img1']['name']) $data['img_path1'] = $yearmonth.'/'.$day.'/'.'s_'.$imginfo['img1']['name'];
		if($imginfo['img2']['name']) $data['img_path2'] = $yearmonth.'/'.$day.'/'.'m_'.$imginfo['img2']['name'];
		*/
		if(!isset($data['img_path1'])){
			$data['img_path1'] = $result['img_path1'] ? $result['img_path1'] : '';
		}
		if(!isset($data['img_path2'])){
			$data['img_path2'] = $result['img_path2'] ? $result['img_path2'] : '';
		}
		$data['ext_title'] = $_POST['ext_title'] ? trim($_POST['ext_title']) : ($result['ext_title'] ? $result['ext_title'] : '');
		$data['url'] = $_POST['url'] ? trim($_POST['url']) : ($result['url'] ? $result['url'] : '');
		$posdata['source'] = 2;
		$posdata['level'] = 999;
		$posdata['column'] = 0;
		foreach($pp as $val){	
			$val2 = explode('_',$val);$log .= ','.$val;
			$posdata['platform'] = $data['platform'] = $val2[0];
			$posdata['position'] = $data['position'] = $val2[1];//var_dump($advid);exit;
			$aid = $zy_schedulemodel -> table('zy_schedule') -> data(array('status'=>-1)) -> add();
			$bbs_model->table('zy_schedule')->where(array('id'=>$aid))->delete();
			$data['advdid'] = $aid;
			$advdata_model->table('zy_advdata')-> data($data)->add();//插入一条新纪录,获取新 广告ID 
			$posdata['advid'] = $aid;
			$posid = $pos_confmodel->table('zy_pos_conf')->data($posdata)->add();//获取新 eid 
			$result = $bbs_model->table('zy_advdata')->where(array('advdid'=>$aid))->save(array('posid'=>$posid));//更新新内容关联eid
		}
		
		$this -> writelog("智友内容管理-广告素材管理-广告素材 已经复制id为{$advdid}的内容到{$log}","zy_advdata",$advdid,__ACTION__ ,"","edit");
		$this -> success("复制成功");
		
	}
	
	
	protected function _upload_tp($savepath) {
		import ( "@.ORG.UploadFile" );
		$upload = new UploadFile ( );
		//设置上传文件大小
		$upload->maxSize = 3292200;
		//设置上传文件类型
		$upload->allowExts = explode ( ',', 'jpg,gif,png,jpeg' );
		//设置附件上传目录
		$upload->savePath = $savepath;
		//设置需要生成缩略图，仅对图像文件有效
		$upload->thumb = true;
		// 设置引用图片类库包路径
		$upload->imageClassPath = '@.ORG.Image';
		//设置需要生成缩略图的文件后缀
		$upload->thumbPrefix = 'm_,s_'; //生产2张缩略图
		//设置缩略图最大宽度
		$upload->thumbMaxWidth = '570,320';
		//设置缩略图最大高度
		$upload->thumbMaxHeight = '390,190';
		//设置上传文件规则
		$upload->saveRule = uniqid;
		//删除原图
		$upload->thumbRemoveOrigin = true;
		if (! $upload->upload ()) {
			//捕获上传异常
			$this->error ( $upload->getErrorMsg () );
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo ();
			
		}
		foreach($uploadList as $val){
			if($val['post_name'] == 'img1') unlink($val['savepath'].'m_'.$val['savename'])  ;
			if($val['post_name'] == 'img2') unlink($val['savepath'].'s_'.$val['savename'])  ;
			$list[$val['post_name']]['name']=$val['savename'];
			
		}
		return $list;
	}

	protected function _upload($file,$savepath,$config){
		include_once dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}
}