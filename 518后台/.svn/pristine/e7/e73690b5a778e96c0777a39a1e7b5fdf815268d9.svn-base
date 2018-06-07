<?php
/**
 * 安智网产品管理平台 活动期控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：WWT
 * ----------------------------------------------------------------------------
*/
class ActivateAction extends CommonAction {
	
	//活动期列表管理
	function activate_list(){
	    import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
	    if(isset($_GET['status'])){
		    $condition['status'] = (int)$_GET['status'];
			$this->assign('status',(int)$_GET['status']);
		}else {
		    $condition['status'] = 1;
			$this->assign('status',1);
		}
	    $model = new Model();
		$count_total = $model ->table('sl_activate') ->where($condition) -> count();
		$page  = new Page($count_total, $limit, $param);
		$activate_list = $model -> table('sl_activate') ->where($condition) 
		                -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
		$this->assign('activate_list',$activate_list);
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign('page', $page->show());
	    $this -> display();
	}
	
	//活动期添加
	function activate_add(){
	    $model = new Model();
		$activate_ids = $model -> table('sl_activate') -> where('`status` != 3') 
		                ->field('id') -> order('id desc') -> limit(1) -> select();
		$this -> assign('activate_id', $activate_ids[0]['id']);
		$this -> assign('next_activate_id', $activate_ids[0]['id'] + 1);
	    $activate_history = $model -> table('sl_activate') -> where(' `status` = 1') 
		                -> field('id') -> select();
        $this -> assign('activate_history', $activate_history);
		$this -> assign('activate_history_count', count($activate_history));
	    $this -> display();
	}
	
	//活动期添加do
	function activate_add_do(){
	    if(isset($_POST)){
		    $data['id']                = (int)$_POST['id'];
		    $data['title']             = (string)$_POST['title'];
		    $data['activate_descript'] = (string)$_POST['activate_descript'];
			$data['activate_note']     = (string)$_POST['activate_note'];
			$data['status']            = 2;
			$data['publish_tm']        = time();
			$data['update_tm']         = time();
			$picture                   = $_FILES['a_image'];
			$activate_historys         = $_POST['activate_history'];
			$model = new Model();
			$where = array('id' => $data['id']);
			$count = $model -> table('sl_activate') -> where($where) -> count();
			//echo $model->getLastSql();
		    if($count > 0){
				$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
				$this -> error('活动期id不允许重复，请重新输入');
			} 
			if(empty($data['id'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('请输入活动期id');
			}
			if(empty($data['title'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('请输入活动期主题');
			}
			if(empty($data['activate_descript'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('请输入活动期简介');
			}
			if(mb_strlen($data['activate_descript'],'utf-8') > 300){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('活动期简介不超过300个字符');
			}
			if(empty($picture)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('请选择活动期图片');
			}
			if(isset($activate_historys)){
				array_pop($activate_historys);
				$old_count = count($activate_historys);
				$historys  = array_unique($activate_historys);
				$new_count = count($historys);
				if($new_count < $old_count){
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
					$this -> error('往期回顾里有重复的活动期id');
				}
				if( ($old_count + 1) != count($_FILES["ah_image"]["name"])){
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
					$this -> error('往期回顾里有缺少上传的的文件');
				}
			}
			//附件上传
			$path = date("Ym/d/");
			$config = array(
				'multi_config' => array(),
			);
			if(!empty($_FILES['a_image']['size'])){
				$config['multi_config']['a_image'] = array(
					'savepath' => UPLOAD_PATH . '/image/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 970,
					'img_p_height' => 275,
				);
			}
			$ah_sizes = $_FILES['ah_image']['size'];
			foreach($ah_sizes as $ah_size){
			    if(!empty($ah_size)){
					$config['multi_config']['ah_image'] = array(
						'savepath' => UPLOAD_PATH. '/image/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width' => 212,
						'img_p_height' => 110,
					);
			    }
			}
			$history_url = array();
			if (isset($config['multi_config'])) {
				$image_list = $this->_uploadapk(0, $config);
				foreach($image_list['image'] as $val) {
					if ($val['post_name'] == 'a_image') {
						$data['picture_url'] =  $val['url'];
					}
					if ($val['post_name'] == 'ah_image') {
						$history_url[]  =  array('url' => $val['url']);
					}
				}
			}
			$history_list = array_combine($activate_historys, $history_url);
			foreach($history_list as $key => $val){
			    $map['pic_url']    = $val['url'];
				$map['status']     = 1;
				$map['activate_id']= $data['id'];
				$map['history_id'] = $key;
				$map['rank']       = 0;
				$map['publish_tm'] = time();
				$model -> table('sl_activate_history')->add($map);
			}
			if($model->table('sl_activate')->add($data)){
			    //批量更新排序
			    $activate_history = $model -> table('sl_activate_history') 
				    -> where('`activate_id` = '.$data['id'].' AND `status` = 1 ') -> field('id,rank') -> order('rank asc ') -> select();
				//echo $model->getLastSql();
				foreach($activate_history as $key => $val){
				    $sql  = ' UPDATE sl_activate_history SET `rank` = '.($key + 1);
					$sql .= ' WHERE `id` ='.$val['id'].' AND `activate_id` = '.$data['id']; 
			        $model -> table('sl_activate_history') -> query($sql);
				}
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_list/status/2');
				$this -> writelog('添加了id为'.$id.'的活动期', 'sl_activate', $id,__ACTION__ ,"","add");
				$this -> success('添加成功');
			}else{
			    //echo $model->getLastSql();
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
				$this -> error('添加失败');
			}
		}
	}
	
	//活动期编辑
	function activate_edit(){
	    $id    = (int)$_GET['id'];
		$model = new Model();
		$where = array('id' => $id);
		$activate_info = $model -> table('sl_activate') ->where($where) -> select();
		$this -> assign('activate_info', $activate_info[0]);
		$activate_id = $model -> table('sl_activate') -> where('`status` != 3') 
		                ->field('id') -> order('id desc') -> limit(1) -> select();
		$this -> assign('activate_id', $activate_id[0]['id']);
		$map['status'] = array('eq',1) ;
		$map['id']     = array('lt',$id);
		$activate_history  = $model ->table('sl_activate') -> where($map) -> select();
		$this -> assign('activate_history', $activate_history);
		$this -> assign('activate_history_count', count($activate_history));
		$activate_history_list = $model ->table('sl_activate_history')->where('activate_id ='.$id.' and status = 1')->select();
		$this -> assign('activate_history_list',$activate_history_list);
	    $this -> display();
	}
	
	//活动期编辑do
	function activate_edit_do(){
	    if(isset($_POST)){
		    $old_id     = intval($_POST['old_id']);
			$new_id     = intval($_POST['new_id']);
		    $data['title']             = (string)$_POST['title'];
		    $data['activate_descript'] = (string)$_POST['activate_descript'];
			$data['activate_note']     = (string)$_POST['activate_note'];
			$data['update_tm']         = time();
			$picture                   = $_FILES['a_image'];
			$activate_historys         = $_POST['activate_history'];
			$model = new Model();
			if($new_id != $old_id){
			    $where = array('id' => $new_id);
			    $count = $model -> table('sl_activate') -> where($where) -> count();
				if($count > 0){
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
					$this -> error('活动期id不允许重复，请重新输入');
				} 
			}else{
			    $where = array('id' => $old_id);
			}
			if(empty($data['title'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('请输入活动主题');
			}
			if(empty($data['activate_descript'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('请输入活动简介');
			}
			if(mb_strlen($data['activate_descript'],'utf-8') > 300){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
			    $this -> error('活动期简介不超过300个字符');
			}
			if(isset($activate_historys)){
				array_pop($activate_historys);
				$old_count = count($activate_historys);
				$historys  = array_unique($activate_historys);
				$new_count = count($historys);
				if($new_count < $old_count){
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
					$this -> error('往期回顾里有重复的活动期id');
				}
				if( ($old_count + 1) != count($_FILES["ah_image"]["name"])){
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_add');
					$this -> error('往期回顾里有缺少上传的的文件');
				}
			}
			//附件上传
			$path = date("Ym/d/");
			$config = array(
				'multi_config' => array(),
			);
			if(!empty($_FILES['a_image']['size'])){
				$config['multi_config']['a_image'] = array(
					'savepath' => UPLOAD_PATH . '/image/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 970,
					'img_p_height' => 275,
				);
			}
			$ah_sizes = $_FILES['ah_image']['size'];
			foreach($ah_sizes as $ah_size){
			    if(!empty($ah_size)){
					$config['multi_config']['ah_image'] = array(
						'savepath' => UPLOAD_PATH. '/image/'. $path,
						'saveRule' => 'getmsec',
						'img_p_width' => 212,
						'img_p_height' => 110,
					);
			    }
			}
			$history_url = array();
			if (!empty($config['multi_config'])) {
				$image_list = $this->_uploadapk(0, $config);
				foreach($image_list['image'] as $val) {
					if ($val['post_name'] == 'a_image') {
						$data['picture_url'] =  $val['url'];
					}
					if ($val['post_name'] == 'ah_image') {
						$history_url[]  =  array('url' => $val['url']);
					}
				}
			}
			$model = new Model();
			$history_list = array_combine($activate_historys, $history_url);
			foreach($history_list as $key => $val){
			    $conf = array('`activate_id`' => $where['id'],'history_id' => $key ,'`status`' => 1);
			    $ah_count  = $model ->table('sl_activate_history') -> where($conf) -> count();
			    if($ah_count > 0){
				    $map['pic_url']    = $val['url'];
					$map['history_id'] = $key;
					$map['rank']       = $key;
					$map['publish_tm'] = time();
					$condition = array('`activate_id`' => $where['id'] ,'`history_id`' => $key , '`status`' => 1);
					$model -> table('sl_activate_history') -> where($condition) -> save($map);
					//echo $model->getLastSql();
				}else{
				    $map['pic_url']    = $val['url'];
					$map['status']     = 1;
					$map['activate_id']= $where['id'];
					$map['history_id'] = $key;
					$map['rank']       = $key;
					$map['publish_tm'] = time();
					$model -> table('sl_activate_history')->add($map);
					//echo $model->getLastSql();
				}
			}
			$log_result = $this->logcheck($where,'sl_activate',$data,$model);
			if($model -> table('sl_activate') -> where($where) -> save($data)){
			    //批量更新排序
			    $activate_history = $model -> table('sl_activate_history') 
				    -> where('`activate_id` = '.$where['id'].' AND `status` = 1 ') -> field('id,rank') -> order('rank asc ') -> select();
				foreach($activate_history as $key => $val){
				    $sql  = ' UPDATE sl_activate_history SET `rank` = '.($key + 1);
					$sql .= ' WHERE `id` ='.$val['id'].' AND `activate_id` = '.$where['id']; 
			        $model -> table('sl_activate_history') -> query($sql);
				}
			    $activate_info = $model -> table('sl_activate') -> where($where) ->field('status') -> limit(1) -> select();
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_list/status/'.$activate_info[0]['status']);
				$this -> writelog('编辑了id为'.$where['id'].'的活动期'.".{$log_result}", 'sl_activate', $where['id'],__ACTION__ ,"","edit");
				$this -> success('编辑成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_edit');
				$this -> error('编辑失败');
			}
		}
	}
	
	//活动期启停用
	function activate_able(){
	    $id     = (int)$_GET['id'];
		$where  = array( 'id' => $id );
		$status = (int)$_GET['status'];
		// status状态为 0删除 1启用 2待发布 3停用
		switch($status){
		    case 1 : $data['status'] = 3; break;
		    case 2 : $data['status'] = 1; break;
			case 3 : $data['status'] = 1; break;
		}
	    $model = new Model();
		if($model -> table('sl_activate') -> where($where) -> save($data)){
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_list/status/'.$status);
			$str=($status==1)?'停用':(($status==2)?'发布':'启用');
			$this -> writelog("{$str}了id为{$id}的活动期", 'sl_activate', $id,__ACTION__ ,"","edit");
			$this -> success('操作成功');
		}else{
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_list');
			$this -> error('操作失败');
		}
	}
	
	//活动期删除
	function activate_del(){
	    $id     = (int)$_GET['id'];
		$where  = array('id' => $id );
		$status = (int)$_GET['status'];
		if($status == 3){
		    $data['status'] = 0;
		}
	    $model = new Model();
		if($model -> table('sl_activate') -> where($where) -> save($data)){
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_list/status/'.$status);
			$this -> writelog('删除了id为'.$id.'的活动期', 'sl_activate', $id,__ACTION__ ,"","del");
			$this -> success('操作成功');
		}else{
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_list');
			$this -> error('操作失败');
		}
	}
	
	//查看活动期回顾
	function view_activate_history(){
	    import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
	    $activate_id = (int)$_GET['activate_id'];
		$model = new Model();
		$where['activate_id'] = $activate_id;
		$count_total = $model ->table('sl_activate_history') -> where($where) -> count();
		$page  = new Page($count_total, $limit, $param);
		$activate_history = $model -> table('sl_activate_history') -> where($where) -> order('rank asc') 
		                    -> limit($page -> firstRow . ',' . $page -> listRows)-> select();
		//echo $model->getLastSql();
		$this -> assign('activate_history', $activate_history);
		$this -> assign('count', $count_total);
		$this -> assign('activate_id', $activate_id);
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign('page', $page->show());
	    $this -> display();
	}
	
	//更新活动期回顾排序
	function activate_update_rank(){
	    $id    = (int)$_GET['id'];
		$rank  = (int)$_GET['rank'];
		$lr    = isset($_GET['lr']) ? (int)$_GET['lr'] : 10;
		$p     = isset($_GET['p'])  ? (int)$_GET['p']  : 1;
		$activate_id = (int)$_GET['activate_id'];
		$where  = ' `status` = 1 AND `activate_id` = '.$activate_id;
		$param  = $this->_updateRankInfo('sl_activate_history','rank',$id,$where,$rank,$lr,$p);
        $this -> writelog('更新了id为'.$id.',的活动期回顾的rank排序','sl_activate_history',$id,__ACTION__ ,"","edit");
		exit(json_encode($param));
	}
	
	
	//下期预告列表
	function next_forecast(){
	    import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
	    $model = new Model();
		$count_total = $model ->table('sl_activate_next') -> count();
		$page  = new Page($count_total, $limit, $param);
		$activate_next = $model -> table('sl_activate_next') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
		$this->assign('activate_next',$activate_next);
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign('page', $page->show());
	    $this -> display();
	}
	
	//下期预告添加
	function next_forecast_add(){
	    $model = new Model();
		$activate_list = $model -> table('sl_activate') ->where('`status` = 2') -> field('id') ->select();
		$this -> assign('activate_ids',$activate_list);
	    $this -> display();
	}
	
	//下期预告添加do
	function next_forecast_add_do(){
	    if(isset($_POST)){
		    $data['activate_id']       = intval($_POST['activate_id']);
		    $data['partner']           = trim($_POST['partner']);
			$data['title']             = trim($_POST['title']);
			$data['guest']             = trim($_POST['guest']);
			$data['descript']          = trim($_POST['descript']);
			$data['publish_tm']        = trim($_POST['publish_tm']);
			$data['address']           = trim($_POST['address']);
			$data['regist_method']     = trim($_POST['regist_method']);
			$data['join_method']       = trim($_POST['join_method']);
			$data['status']            = 2 ;
			if(empty($data['activate_id'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请选择活动期');
			}
			if(empty($data['partner'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入承办方或协办方');
			}
			if(empty($data['title'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入主题');
			}
			if(empty($data['guest'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入嘉宾');
			}
			if(empty($data['descript'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入简介');
			}
			if(empty($data['publish_tm'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入举办时间、地点、方式');
			}
			if(empty($data['address'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入举办地点');
			}
			if(empty($data['regist_method'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入报名方式');
			}
			if(empty($data['join_method'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入参会方式');
			}
			$model = new Model();
			$where = array('`activate_id`' => $data['activate_id'] ,'`status`' => 2);
			$count_total = $model->table('sl_activate_next')->where($where)->count();
			if($count_total > 0){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
				$this -> error('待发布活动期不允许重复');
			}
			if($id = $model->table('sl_activate_next')->add($data)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast');
				$this -> writelog('添加了id为'.$id.'的下期预告', 'sl_activate', $id,__ACTION__ ,"","add");
				$this -> success('添加成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
				$this -> error('添加失败');
			}
		}
	}
	
	//下期预告编辑
	function next_forecast_edit(){
	    $id = (int)$_GET['id'];
		$where = array( 'id' => $id);
		$model = new Model();
		$next_forecast = $model -> table('sl_activate_next') -> where($where) -> select();
		$this->assign('activate_info',$next_forecast[0]);
		$activate_list = $model -> table('sl_activate') ->where('`status` = 2') -> field('id') ->select();
		$this->assign('activate_ids',$activate_list);
	    $this -> display();
	}
	
	//下期预告编辑do
	function next_forecast_edit_do(){
	    if(isset($_POST)){
		    $id     = intval($_POST['id']);
		    $where  = array('id' => $id);
		    $data['activate_id']       = intval($_POST['activate_id']);
		    $data['partner']           = trim($_POST['partner']);
			$data['title']             = trim($_POST['title']);
			$data['guest']             = trim($_POST['guest']);
			$data['descript']          = trim($_POST['descript']);
			$data['publish_tm']        = trim($_POST['publish_tm']);
			$data['address']           = trim($_POST['address']);
			$data['regist_method']     = trim($_POST['regist_method']);
			$data['join_method']       = trim($_POST['join_method']);
			if(empty($data['activate_id'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_edit');
			    $this -> error('请选择活动期');
			}
			if(empty($data['partner'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_edit');
			    $this -> error('请输入承办方或协办方');
			}
			if(empty($data['title'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_edit');
			    $this -> error('请输入主题');
			}
			if(empty($data['guest'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_edit');
			    $this -> error('请输入嘉宾');
			}
			if(empty($data['descript'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_edit');
			    $this -> error('请输入简介');
			}
						if(empty($data['publish_tm'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入举办时间、地点、方式');
			}
			if(empty($data['address'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入举办地点');
			}
			if(empty($data['regist_method'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入报名方式');
			}
			if(empty($data['join_method'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast_add');
			    $this -> error('请输入参会方式');
			}
			$model = new Model();
			$log_result = $this->logcheck($where,'sl_activate_next',$data,$model);
			if($model -> table('sl_activate_next') -> where($where) -> save($data)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast');
				$this -> writelog('编辑了id为'.$id.'的下期预告'.".{$log_result}", 'sl_activate_next', $id,__ACTION__ ,"","edit");
				$this -> success('编辑成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/activate_edit');
				$this -> error('编辑失败');
			}
		}
	}
	
	//下期预告上下线
	function next_forecast_del(){
	    $id     = (int)$_GET['id'];
		$where  = array( 'id' => $id );
		$status = (int)$_GET['status'];
		switch($status){
		    case 1 : $data['status'] = 2; break;
		    case 2 : $data['status'] = 1; break;
		}
	    $model = new Model();
		if($model -> table('sl_activate_next') -> where($where) -> save($data)){
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast');
			$str=($data['status']==1)?'发布':'下线';
			$this -> writelog($str.'了id为'.$id.'的下载预告', 'sl_activate_next', $id,__ACTION__ ,"","edit");
			$this -> success('操作成功');
		}else{
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/next_forecast');
			$this -> error('操作失败');
		}
	}
	
	//文章管理列表
	function article_list(){
	    $model = new Model();
	    import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
		if(isset($_GET['status'])){
		    $where['`status`'] = (int)$_GET['status'];
			$this -> assign('status',(int)$_GET['status']);
		}else{
		    $where['`status`'] = 1 ;
			$this -> assign('status',1);
		}
		/*******************/
		$url_suffix = $this->get_url_suffix(array('activate_id','channel_id','title','p','lr'),true);
		$this->assign("url_suffix",$url_suffix);
		/*******************/
		if(isset($_GET['title'])){
		    $condition['`title`'] = array('like','%'.$_GET['title'].'%');
			$context_lists = $model -> table('sl_activate_context')->where($condition) ->field('id')  -> select();
			$context_info  = array();
			foreach($context_lists as $context_list){
			    $context_info[] = $context_list['id'];
			}
			$where['`context_id`'] = array('in',implode(',',$context_info)) ;
			$this -> assign('title',(string)$_GET['title']);
		}else{
		    $this -> assign('title','');
		}
		if(isset($_GET['channel_id'])){
		    $channel_id = (int)$_GET['channel_id'];
			if($channel_id < 0){
			    $where['`channel_id`'] = array('gt',0);
			}else {
			    $where['`channel_id`'] = $channel_id;
			}
			$this -> assign('channel_id',$channel_id);
		}else{
		    $this -> assign('channel_id',-1);
		}
		if(isset($_GET['activate_id'])){
		    $activate_id = (int)$_GET['activate_id'];
		    if($activate_id < 0){
			    $where['`activate_id`'] = array('gt',0);
			}else {
			    $where['`activate_id`'] = $activate_id;
			}
			$this -> assign('activate_id',$activate_id);
		}else{
		    $this -> assign('activate_id',-1);
		}
	    
		//活动期列表
		$activate_list = $model -> table('sl_activate') -> where('`status` != 3') ->field('id')  -> select();
		//echo $model->getLastSql();
		$this -> assign('activate_list',$activate_list);
		
		//频道列表
		$channel_list  = $model ->table('sl_activate_channel') ->where('`status` = 1') ->field('id,channel_name') -> select();
		//echo $model->getLastSql();
		$this -> assign('channel_list',$channel_list);
		
		
		$total =  $model -> table('sl_activate_map') -> where($where) -> group('activate_id,context_id') -> select();
		//echo $model->getLastSql();
	    $count_total = count($total);
		$page  = new Page($count_total, $limit, $param);	
		$field = 'activate_id,context_id';
		$map_list = $model -> table('sl_activate_map') -> where($where) -> group('activate_id,context_id')  -> field($field) -> order('context_id desc')
					    -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
		//echo $model->getLastSql();
 		
		for($i = 0; $i < count($map_list);$i++){
/* 			$huodong = '`id` = '.$map_list[$i]['activate_id'];
		    $activate_id = $model ->table('sl_activate') -> where($huodong) ->getField('id');
			$map_list[$i]['activate_id'] = $activate_id; */
			$activate_id = $map_list[$i]['activate_id'];
			$context_id = $map_list[$i]['context_id'];
			$cid_arr = $model -> table('sl_activate_map') -> where('activate_id = '.$activate_id.' and context_id = '.$context_id.' and status = 1')->field('channel_id') -> select(); 
			foreach($cid_arr as $cid){
			$qudao = '`status` = 1 AND `id` = '.$cid['channel_id'];
			$channel_name = $model -> table('sl_activate_channel') ->where($qudao)-> getField('channel_name');
			$map_list[$i]['channel_name'] .= $channel_name .",";
			}
			
			$wenzhang = '`status` = 1 AND `id` = '.$map_list[$i]['context_id'];
            $aricle_list = $model -> table('sl_activate_context') -> where($wenzhang) ->limit(1) -> select();
			$map_list[$i]['title']    = $aricle_list[0]['title'];
			$map_list[$i]['context']  = $aricle_list[0]['context'];
			$map_list[$i]['author']   = $aricle_list[0]['author'];
			$map_list[$i]['target']   = $aricle_list[0]['target'];
			//$map_list[$i]['picture_view']   = $aricle_list[0]['picture_view'];
			
			$tupian = '`status` = 1 AND  `activate_id` = '.$map_list[$i]['activate_id'].'   AND `context_id` = '.$map_list[$i]['context_id'];
			$aricle_pic_list = $model -> table('sl_activate_context_pic') -> where($tupian) -> select();	
			
			foreach($aricle_pic_list as $piclist_key=>$piclist_val){
			    $tupian_channel = 'id='.$piclist_val['channel_id'];
				$aricle_pic_list_name = $model -> table('sl_activate_channel') -> where($tupian_channel) -> select();
                 			
				if ($piclist_val['type'] == 2 && $piclist_val['pic_url']!=''){
					$map_list[$i]['pic_url'][$piclist_key][0]  = $piclist_val['pic_url'];
					$map_list[$i]['pic_url'][$piclist_key][1] = $aricle_pic_list_name[0]['channel_name'];
				}elseif($piclist_val['type'] == 3 && $piclist_val['pic_url']!=''){
				
					$map_list[$i]['video'][$piclist_key][0] = $piclist_val['pic_url'];
					$map_list[$i]['video'][$piclist_key][1] = $aricle_pic_list_name[0]['channel_name'];
				}
			}
		} 
	   // var_dump($map_list);
		$this -> assign('article_list',$map_list);
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign('page', $page->show());
	    $this -> display();
	}
	
	//文章添加
	function article_add(){
	    $model = new Model();
		$where['status'] = array('neq',3); 
		$activate_list = $model ->table('sl_activate') -> where($where) ->field('id') -> select();
		$this -> assign('activate_list',$activate_list);
		$channel_list = $model -> table('sl_activate_channel') -> where('`status` = 1') -> select();
		$this -> assign('channnel_list',$channel_list);
		$this -> display();
	}
	
	//文章添加do
	function article_add_do(){
	    if(isset($_POST)){
		    $data['title']        = (string)trim($_POST['title']);
			$data['context']      = (string)trim($_POST['editorValue']);
			$data['target']       = (string)trim($_POST['target']);
			$data['author']       = (string)trim($_POST['author']);
			$data['dispcript']    = (string)trim($_POST['dispcript']);
		//	$data['picture_view'] = (int)$_POST['picture_view'];
			$data['status']       = 1 ;
			$data['publish_tm']   = time();
			$data['update_tm']    = time();
			$activate_id          = (int)$_POST['activate_id'];
			$channel_ids          = $_POST['channel_id'];
			
		
			
			if(empty($data['title'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_add');
			    $this -> error('请输入文章标题');
			}
			if(empty($data['context'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_add');
			    $this -> error('请填写文章内容');
			}
			if(empty($activate_id)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_add');
			    $this -> error('请选择活动期');
			}
			if(empty($channel_ids)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_add');
			    $this -> error('请选择频道');
			}
			
			$model = new Model();
			if($id = $model -> table('sl_activate_context') -> add($data)){
			    //插入活动映射表
				foreach($channel_ids as $key => $channel_id){
				    $condition['activate_id'] = $activate_id;
					$condition['channel_id']  = $channel_id;
					$condition['status']      = 1;
					$count = $model -> table('sl_activate_map') -> where($condition) -> count();
					$condition['rank']        = $count + 1;
					$condition['context_id']  = $id;
					$model -> table('sl_activate_map') -> add($condition);
					//echo $model->getLastSql();
				}
				$file_count = 0;
				
				$channel_arr = $_POST['channel_arr'];
				foreach($_FILES['article_image']['size'] as $idx => $size){
					if($size > 0){ 
						$file_count++;
						$img_index[] = $channel_arr[$idx];
					}
				}
				
				
			 

				$is_image = false;
				if($file_count > 0){	
					$path = date("Ym/d/",time());
					$config = array(
						'multi_config' => array(),
					);
					$config['multi_config']['article_image'] = array(
						'savepath' => UPLOAD_PATH . '/image/' . $path,
						'saveRule' => 'getmsec',
						//'img_p_width'  => 288,
						//'img_p_height' => 296,
					);
					if (!empty($config['multi_config'])) {
						$image_list = $this->_uploadapk(0, $config);	 
					}
					 foreach($image_list['image'] as $idx => $info){
						$img_url_arr[$img_index[$idx]] =  $info['url'];
				    }
				}
				
				
				
				//var_dump($image_list,$img_url_arr);
         		foreach($channel_ids as $key => $channel_id){
				    $picture_view = 'picture_view'.$channel_id;
					$picture_view_type = $_POST[$picture_view]; 
					//插入文章图片表
					if($map['pic_url']) unset($map['pic_url']);

					if($picture_view_type == 1){//重点图片上传
					    if($img_url_arr[$channel_id]){
						  $map['pic_url'] = $img_url_arr[$channel_id];	
						  $is_image = true;
						}						  
						$map['descript']   = (string)$_POST['image_dispcript'.$channel_id];
						$map['type']       = 2 ;		
					
					}else if($picture_view_type == 2){//视频地址填写
						$article_video = $_POST['article_video'.$channel_id];
						if(!empty($article_video)){

							$map['descript']   = (string)$_POST['video_dispcript'.$channel_id];
							$map['pic_url']    = (string)$article_video;
							$map['type']       = 3 ;
							$is_video = true;
						}
						else{
							$is_video = false;
						
						}
						
					}
					$map['status']     = 1 ;
					$map['channel_id'] = $channel_id;
					$map['activate_id'] = $activate_id;
					$map['context_id'] = $id;
					$map['publish_tm'] = time();
					if($is_image || $is_video){
						$model -> table('sl_activate_context_pic') -> add($map);
					}
				}
		
				
				//匹配编辑器中上传图片
				preg_match_all('/<img.*src="(.*)"\\s*.*>/iU',$data['context'],$match);
				$image_urls = $match[1];
				if(!empty($image_urls)){
				    foreach($image_urls  as $image_url){
					    if(is_file(UPLOAD_PATH.$image_url)){
						    $map['pic_url'] = str_replace(IMGATT_HOST,'',$image_url);
							$map['type']    = 1;
							$map['status']  = 1 ;
							$map['context_id'] = $id;
							$map['publish_tm'] = time();
							$model -> table('sl_activate_context_pic') -> add($map);
						}
				    }
				}
			    $this  -> assign('jumpUrl', '/index.php/Sl/Activate/article_list');
				$this  -> writelog('添加了id为'.$id.'的文章', 'sl_activate_context', $id,__ACTION__ ,"","add");
				$this  -> success('添加成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_add');
				$this -> error('添加失败');
			}
		}
	}
	
	//文章编辑
	function article_edit(){
	    if(isset($_GET['id'])){
			$id          = (int)$_GET['id'];
			$activate_id = intval($_GET['activate_id']);
			$model = new Model();
			$map_channel_arr = $model -> table('sl_activate_map') -> where('activate_id = '.$activate_id.' and context_id ='.$id." and status = 1") -> field('channel_id') -> select();
			$channel_ids = array();
			foreach($map_channel_arr as $info){
				$channel_ids[]  = $info['channel_id'];			
			}
			
			$map['id']  = $id;
			$article_info = $model -> table('sl_activate_context') -> where($map) ->select();
			$this -> assign('article_info',$article_info[0]);
			$this -> assign('url_suffix',$_GET['url_suffix']);
			
			$where['status'] = array('neq',3); 
			$activate_list = $model ->table('sl_activate') -> where($where) ->field('id') -> select();
			$this -> assign('activate_list',$activate_list);
			$this -> assign('activate_id',$activate_id);
						
			$channel_list = $model -> table('sl_activate_channel') -> where('`status` = 1') 
						-> field('id,channel_name') -> select();
			foreach($channel_list as $idx => $info){
				if(in_array($info['id'],$channel_ids)){
					$channel_list[$idx]['checked'] = 1 ;
				}
				$map = array('`context_id`' => $id ,'`status`' => 1,'`activate_id`'=>$activate_id,'`channel_id`' => $info['id']);
				$context_pic_list = $model -> table('sl_activate_context_pic') -> where($map) -> limit(1) -> select();
				$channel_list[$idx]['pic'] = $context_pic_list[0];
				
			}
			$this -> assign('channel_list',$channel_list); 
			//$this -> assign('context_pic_info',$context_pic_list);
			
			
			$this -> display();
		}
	}
	
	//文章编辑do
	function article_edit_do(){
	    if(isset($_POST)){
		    $id     = (int)$_POST['id'];
		    $where  = array('id' => $id);
		    $data['title']        = (string)trim($_POST['title']);
			$data['context']      = (string)trim($_POST['editorValue']);
			$data['target']       = (string)trim($_POST['target']);
			$data['author']       = (string)trim($_POST['author']);
			$data['dispcript']    = (string)trim($_POST['dispcript']);
		//	$data['picture_view'] = (int)$_POST['picture_view'];
			$data['update_tm']    = time();
			$activate_id_old  = $_POST['activate_id_old'];
			$activate_id = (int)$_POST['activate_id'];
			$channel_ids  = $_POST['channel_id'];
			$article_info = $data;
			$url_suffix = $_POST['url_suffix'];
			if(empty($activate_id)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_edit/url_suffix/'.$url_suffix);
			    $this -> error('请选择活动期');
			}
			if(empty($channel_ids)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_edit/url_suffix/'.$url_suffix);
			    $this -> error('请选择频道');
			}
			if(empty($data['title'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_edit/url_suffix/'.$url_suffix);
			    $this -> error('请输入文章标题');
			}
			if(empty($data['context'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_edit/url_suffix/'.$url_suffix);
			    $this -> error('请填写文章内容');
			}
			$model = new Model();
			$log_result = $this->logcheck($where,'sl_activate_context',$data,$model);
			if($model -> table('sl_activate_context') -> where($where) -> save($data)){
			    //更新活动映射表
				$constraint = array('activate_id'=>$activate_id_old,'context_id'=>$id,'status'=>1);
				$map_channel_ids = $model -> table('sl_activate_map') -> where($constraint) -> field('channel_id')->select();
				$db_channel_id = array(); //数据库现有渠道
				foreach($map_channel_ids as $info){ 
					$db_channel_id[] = $info['channel_id'];
				}
				foreach($channel_ids as $key => $channel_id){ //表单提交的
					if(!in_array($channel_id,$db_channel_id)){
					 
						$map['activate_id'] = $activate_id_old;
				        $map['channel_id']  = $channel_id;
						$map['context_id']  = $id;
						$map['status']      = 1;
						$map['rank'] = 0;
					
						$model -> table('sl_activate_map')->add($map);
						
					   
					}
				   if($activate_id_old != $activate_id){
						$map2['activate_id'] = $activate_id_old;
						$map2['channel_id']  = $channel_id;
						$map2['context_id']  = $id;
						$map2['status']      = 1;
						$model -> table('sl_activate_map')->where($map2)-> save(array('activate_id'=>$activate_id));  
				    }
				}
			 
		     
				$map = array();
				$data = array();
				$map_channel_ids = $model -> table('sl_activate_map') -> where($constraint) -> field('channel_id')->select();
				$db_channel_id = array(); //数据库现有渠道
				foreach($map_channel_ids as $info){ 
					$db_channel_id[] = $info['channel_id'];
				}
				foreach($db_channel_id as $cid){
					if(!in_array($cid,$channel_ids)){
				        $map['channel_id']  = $cid;
						$map['context_id']  = $id;
						$model -> table('sl_activate_context_pic') -> where($map) -> save(array('status' => 0)); //删除频道图片
						$map['activate_id'] = $activate_id;
						$data['status']      = 0;
						$data['rank'] = 0;
				        $model -> table('sl_activate_map') ->where($map)-> save($data);
					}				
				}
				$where = array(
					'activate_id'=>$activate_id,
					'`status`'=>1,
					'`rank`' => 0,
				);
				$activate_map = $model -> table('sl_activate_map') -> where($where) -> order('id asc') ->select();
				$where = array();
				foreach($activate_map as $info){ //活动期下的频道下的文章进行排序
					$where['activate_id'] = $info['activate_id'];
					$where['channel_id'] = $info['channel_id'];
					$map['activate_id'] = $activate_id;
					$where['status'] = 1;
					$map_list = $model -> table('sl_activate_map') -> where($where) -> order('id asc') -> select();
					foreach($map_list as $idx => $info){
						$where = array();
						$where['id'] = $info['id'];
						$data = array(
							'rank' => $idx + 1,
						); 
						$model -> table('sl_activate_map') -> where($where) -> save($data);
					}
				}
				$where = array();
				$data = array();
				//更新文章图片表
				$data = $article_info;
				$conf = array();
				$file_count = 0;
				$img_index = array();
				$channel_arr = $_POST['channel_arr'];
				foreach($_FILES['article_image']['size'] as $idx => $size){
					if($size > 0){ 
					$file_count++;
					$img_index[] = $channel_arr[$idx];
					}
				}
				$is_image = false;
				if($file_count > 0){	
					$path = date("Ym/d/",time());
					$config = array(
						'multi_config' => array(),
					);
					$config['multi_config']['article_image'] = array(
						'savepath' => UPLOAD_PATH . '/image/' . $path,
						'saveRule' => 'getmsec',
						//'img_p_width'  => 288,
						//'img_p_height' => 296,
					);
					
					if (!empty($config['multi_config'])) {
						$image_list = $this->_uploadapk(0, $config);
					}
					
				}
				foreach($image_list['image'] as $idx => $info){
					$img_url_arr[$img_index[$idx]] =  $info['url'];
				}
				foreach($channel_ids as $key => $channel_id){
						$map = array();			 
						$picture_view = 'picture_view'.$channel_id;
						$picture_view_type = $_POST[$picture_view]; 
						if($map['pic_url']) unset($map['pic_url']);
						//插入文章图片表
					if($picture_view_type == 1){//重点图片上传
					    if($img_url_arr[$channel_id]){
						  $map['pic_url'] = $img_url_arr[$channel_id];	
						}						  
						$map['descript']   = (string)$_POST['image_dispcript'.$channel_id];
						$map['type']       = 2 ;		
					
					}elseif($picture_view_type == 2){//视频地址填写
					
						$article_video = $_POST['article_video'.$channel_id];
					
						if(!empty($article_video)){
							$map['descript']   =  trim($_POST['video_dispcript'.$channel_id]);
							$map['pic_url']    =  trim($article_video);
							$map['type']       = 3 ;
						}
						
					}
							
					$conf['context_id'] = $id;
					$conf['status']     = 1;
					$conf['channel_id'] = $channel_id;
					$conf['activate_id'] = $activate_id_old;
					$map['activate_id'] = $activate_id_old;
					$map['publish_tm']  = time();
					
					$pic_count = $model -> table('sl_activate_context_pic') ->where($conf) -> count();
					
					if($pic_count > 0){
					
						$model -> table('sl_activate_context_pic') -> where($conf) -> save($map);
					}else{
					
						if($picture_view_type == 2) $map['type'] = 3;
						if($picture_view_type == 1) $map['type'] = 2;
						$map['context_id'] = $id;
						$map['status']     = 1;
						$map['channel_id'] = $channel_id;	 
						$affect = $model -> table('sl_activate_context_pic') -> add($map);
						
				   } 
				  
				   
				  
				  if($activate_id_old != $activate_id){
					
					$model -> table('sl_activate_context_pic') -> where($conf) -> save( array('activate_id' => $activate_id));
				   }  
				  
				}
			
			
				//匹配编辑器中上传图片
				preg_match_all('/<img.*src="(.*)"\\s*.*>/iU',$data['context'],$match);
				$image_urls = $match[1];
				if(!empty($image_urls)){
				    foreach($image_urls as $image_url){
					    if(is_file(UPLOAD_PATH.$image_url)){
							$image_url = str_replace(IMGATT_HOST,'',$image_url);
							$weiyi = '`pic_url` = '.$image_url.' AND `context_id` = '.$id.' AND `status` = 1 AND `type` = 1';
							$pic_count = $model->table('sl_activate_context_pic')->where($weiyi)->count();
							if($pic_count > 0){
								/*$map['pic_url'] = $image_url;
								$map['type']    = 1;
								$map['publish_tm'] = time();
								$model -> table('sl_activate_context_pic') ->where('`context_id` = '.$id) -> save($map);
								//echo $model->getLastSql(); */
							} else {
								$map['pic_url'] = $image_url;
								$map['type']    = 1;
								$map['status']  = 1 ;
								$map['publish_tm'] = time();
								$map['context_id'] = $id;
								$model -> table('sl_activate_context_pic') -> add($map);
							}
						}
					}
				}
				
				
				
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list'.base64_decode($url_suffix));
				$this -> writelog('编辑了id为'.$id.'的文章'.".{$log_result}", 'sl_activate_context', $id,__ACTION__ ,"","edit");
				$this -> success('编辑成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_edit/url_suffix/'.$url_suffix);
				$this -> error('编辑失败');
			}
		}
	}
	
	//文章启停用
	function article_able(){
	    if(isset($_GET['id'])){
		    $id          = intval($_GET['id']);
			$activate_id = intval($_GET['activate_id']);
			$where  = array('context_id' => $id ,'activate_id' => $activate_id);
			$status = intval($_GET['status']);
			$url_suffix = $_GET['url_suffix'];
			// status状态为 0删除 1正常 2停用
			if($status == 1){
				$data['`status`'] = 2; 
				$this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list/status/2'.base64_decode($url_suffix));
			}
			if($status == 2){
				$data['`status`'] = 1; 
				$this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list/status/1'.base64_decode($url_suffix));
			}
			
			$model = new Model();
			if($model -> table('sl_activate_map') -> where($where) -> save($data)){
				$map['update_tm'] = time();
				$model -> table('sl_activate_context') -> where('`id` = '.$id) -> save($map);
				if($data['`status`']==2){
					$this -> writelog('停用了id为'.$id.'的文章', 'sl_activate_context', $id,__ACTION__ ,"","edit");
				}elseif ($data['`status`']==1) {
					$this -> writelog('启用了id为'.$id.'的文章', 'sl_activate_context', $id,__ACTION__ ,"","edit");
				}
				$this -> success('操作成功');
			}else{
				//echo $model->getLastSql();
				$this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list'.base64_decode($url_suffix));
				$this -> error('操作失败');
			}
		}
	}
	
	//文章删除
	function article_del(){
	    if(isset($_GET['id'])){
		    $id     = intval($_GET['id']);
			//$channel_id  = intval($_GET['channel_id']);
			$activate_id = intval($_GET['activate_id']);
			$where  = array('context_id' => $id ,'activate_id' => $activate_id);
			$status = intval($_GET['status']);
			if($status == 2){
				$data['`status`']    = 0; 
				$model = new Model();
				if($model -> table('sl_activate_map') -> where($where) -> save($data)){
					$map['update_tm'] = time();
					$model -> table('sl_activate_context') -> where('`id` = '.$id) -> save($map);
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list/status/2');
					$this -> writelog('删除了id为'.$id.'的文章', 'sl_activate_context', $id,__ACTION__ ,"","del");
					$this -> success('操作成功');
				}else{
					$this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list');
					$this -> error('操作失败');
				}
			}
		}else{
		    $this -> assign('jumpUrl', '/index.php/Sl/Activate/article_list');
			$this -> error('缺少必要参数');
		}
	}
	
	//查看频道文章列表
	function view_article_list(){
		import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
		$model = new Model();
		if(isset($_GET['channel_id'])){
		    $channel_id  = (int)$_GET['channel_id'];
			$where['channel_id'] = $channel_id;
			$this -> assign('channel_id',$channel_id);
		}
		if(isset($_GET['activate_id'])){
		    $activate_id = (int)$_GET['activate_id'];
			$where['activate_id'] = $activate_id;
			$this -> assign('new_activate_id',$activate_id);
		}else{
		    $activate_info = $model -> table('sl_activate') -> where('status != 3') 
			        -> field('id') -> limit(1) -> order('id desc')-> select();
			$activate_id = $activate_info[0]['id'];
			$where['activate_id'] = $activate_id;
			$this -> assign('new_activate_id',$activate_id);
		}
		
		//活动期列表
		$activate_list = $model -> table('sl_activate') -> where('status != 3') ->field('id')  -> select();
		//echo $model->getLastSql();
		$this -> assign('activate_list',$activate_list);
		
		//频道信息
		$map['id'] = $channel_id;
		$channel_info  = $model -> table('sl_activate_channel') -> where($map) ->field('channel_name')  -> select();
		//echo $model->getLastSql();
		$this -> assign('channel_info',$channel_info[0]);
		
		$where['status'] = 1;
		$count_total =  $model -> table('sl_activate_map') -> where($where) -> count();
		//echo $model->getLastSql();
	
		$page  = new Page($count_total, $limit, $param);	
		$field = ' id,activate_id,channel_id,context_id,`status`,rank';
		$map_list = $model -> table('sl_activate_map') -> where($where) -> field($field)
					    -> limit($page -> firstRow . ',' . $page -> listRows) -> order('rank asc') -> select();
		//echo $model->getLastSql();
		
		for($i = 0; $i < count($map_list);$i++){
			$huodong = '`id` = '.$map_list[$i]['activate_id'];
		    $activate_list = $model ->table('sl_activate') -> where($huodong) ->field('id') -> select();
			$map_list[$i]['activate_id'] = $activate_list[0]['id'];
			
			$qudao = '`status` = 1 AND `id` = '.$map_list[$i]['channel_id'];
			$channel_list = $model -> table('sl_activate_channel') ->where($qudao)-> field('channel_name') ->select();
			$map_list[$i]['channel_name'] = $channel_list[0]['channel_name'];
			
			$wenzhang = '`status` = 1 AND `id` = '.$map_list[$i]['context_id'];
            $aricle_list = $model -> table('sl_activate_context') -> where($wenzhang) -> order('id desc') -> select();
			$map_list[$i]['title']    = $aricle_list[0]['title'];
			$map_list[$i]['context']  = $aricle_list[0]['context'];
			
			$tupian = '`status` = 1 AND `context_id` = '.$map_list[$i]['context_id'];
			$aricle_pic_list = $model -> table('sl_activate_context_pic') -> where($tupian) -> select();		
			if($aricle_pic_list[0]['type'] == 2){
			    $map_list[$i]['pic_url']  = $aricle_pic_list[0]['pic_url'];
			    $map_list[$i]['descript'] = $aricle_pic_list[0]['descript'];
			}
		}
		$this -> assign('count',$count_total);
		$this -> assign('article_list',$map_list);
		$page -> setConfig('header', '篇记录');
		$page -> setConfig('first', '<<');
		$page -> setConfig('last', '>>');
		$this -> assign('page', $page->show());
		//$this -> display('view_article_list');

 		$html = $this->fetch();
		header('Cache-control: no-store');
		header('pragma:no-cache');
		exit($html); 
	}
	
	//更新文章排序
	function article_update_rank(){
	    $map_id    = (int)$_GET['id'];
		$rank      = (int)$_GET['rank'];
		$lr        = isset($_GET['lr']) ? (int)$_GET['lr'] : 10;
		$p         = isset($_GET['p'])  ? (int)$_GET['p']  : 1;
		$activate_id = (int)$_GET['activate_id'];
		$channel_id  = (int)$_GET['channel_id'];
		$where  = ' `status`=1 AND `activate_id`='.$activate_id.' AND `channel_id`='.$channel_id;
		$param  = $this->_updateRankInfo('sl_activate_map','rank',$map_id,$where,$rank,$lr,$p);
        $this -> writelog('更新了id为'.$map_id.'的文章的rank排序','sl_activate_map',$map_id,__ACTION__ ,"","edit");
		exit(json_encode($param));
	}
	
	//查看文章内容
	function view_article_info(){
	    if(isset($_GET['id'])){
		    $id    = intval($_GET['id']);
		    $model = new Model();
			$where = array('id' => $id);
			$article_info = $model ->table('sl_activate_context') -> where($where) ->field('context') -> limit(1) -> select();
			$this -> assign('article_info',$article_info[0]);
			$this -> display();
		}
	}
	
	//频道管理列表
	function channel_list(){
	    import('@.ORG.Page');
        $param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
		    $this->assign('lr',(int)$_GET['lr']);
		}else{
		    $this->assign('lr',$limit);
		}
		if(isset($_GET['p'])){
		    $this->assign('p',(int)$_GET['p']);
		}else{
		    $this->assign('p', 1);
		}
		if(isset($_GET['status'])){
		    $where['status'] = $_GET['status'];
			$this -> assign('status',(int)$_GET['status']);
		}else{
		    $where['status'] = 1;
			$this -> assign('status',1);
		}
	    $model = new Model();
		$count_total = $model ->table('sl_activate_channel') -> where($where) -> count();
		$page  = new Page($count_total, $limit, $param);
		$channel_list = $model -> table('sl_activate_channel') -> where($where) 
		            -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
		for($i = 0;$i < $count_total; $i++){
		    $muban = '`status` = 1 AND `id` = '.$channel_list[$i]['tmp_id'];
		    $tmp_list = $model -> table('sl_tmp_manage') -> where($muban) -> select();
		    $channel_list[$i]['tmp_dir']      = $tmp_list[0]['tmp_dir'];
			$channel_list[$i]['tmp_filename'] = $tmp_list[0]['tmp_filename'];
			$channel_list[$i]['tmp_pic']      = $tmp_list[0]['tmp_pic'];
			$condition['id'] = $channel_list[$i]['parent_id'];
			$parent_channel = $model -> table('sl_activate_channel') -> where($condition) 
				   -> field('channel_name,id') -> limit(1) -> select();
			if($parent_channel[0]['id'] == 0){
				$channel_list[$i]['parent_name']  = '根频道';
			}else{
				$channel_list[$i]['parent_name']  = $parent_channel[0]['channel_name'];
			}
		}
		$this->assign('channel_list',$channel_list);
		$page -> setConfig('header', '篇记录');
        $page -> setConfig('first', '<<');
        $page -> setConfig('last', '>>');
        $this -> assign('page', $page->show());
	    $this -> display();
	}
	
	//频道添加
	function channel_add(){
	    $model = new Model();
		$where = array('status' => 1);
		$tmp_list = $model -> table('sl_tmp_manage') -> where($where) -> field('id,tmp_name') -> select();
		$this -> assign('tmp_list',$tmp_list);
		$channel_category = D('Sl.Channel');
		$option = array(
			'label_id'   => 'parent_id',
			'label_name' => 'parent_id',
			'selected'   => null,
			'key'        => 1
		);
		$channel_list = $channel_category -> getCategory($option);
		$this -> assign('channnel_category',$channel_list);
	    $this -> display();
	}
	
	//频道添加do
	function channel_add_do(){
	    if(isset($_POST)){
		    $data['channel_name']      = (string)$_POST['channel_name'];
		    $data['parent_id']         = intval($_POST['parent_id']);
			$data['channel_descript']  = (string)$_POST['channel_descript'];
			$data['tmp_id']            = intval($_POST['tmp_id']);
			$data['note']              = (string)$_POST['note'];
			$data['status']            = 1 ;
			$data['publish_tm']        = time() ;
			$data['update_tm']         = time() ;
			if(empty($data['channel_name'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_add');
			    $this -> error('请输入频道名称');
			}
			if(empty($data['tmp_id'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_add');
			    $this -> error('请选择模板');
			}
			$model = new Model();
			if($id = $model -> table('sl_activate_channel') -> add($data)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_list');
				$this -> writelog('添加了id为'.$id.'的频道', 'sl_activate_channel', $id,__ACTION__ ,"","add");
				$this -> success('添加成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_add');
				$this -> error('添加失败');
			}
		}
	}
	
	//频道编辑
	function channel_edit(){
	    $id    = (int)$_GET['id'];
		$where = array('id' => $id);
		$model = new Model();
		$channel_list = $model -> table('sl_activate_channel') ->where($where) -> select();
		$this -> assign('channel_info',$channel_list[0]);
		$condition = array( 'status' => 1);
		$tmp_list  = $model -> table('sl_tmp_manage') -> where($condition) -> field('id,tmp_name') -> select();
		$this -> assign('tmp_list',$tmp_list);
		$channel_category = D('Sl.Channel');
		$option = array(
		    'label_id'   => 'parent_id',
			'label_name' => 'parent_id',
			'selected'   => $channel_list[0]['parent_id'],
			'key'        => 1
		);
		$channel_list = $channel_category -> getCategory($option);
		$this -> assign('channnel_category',$channel_list);
	    $this -> display();
	}
	
	//频道编辑do
	function channel_edit_do(){
	    if(isset($_POST)){
		    $id     = (int)$_POST['id'];
		    $where  = array('id' => $id);
		    $data['channel_name']      = (string)$_POST['channel_name'];
		    $data['parent_id']         = intval($_POST['parent_id']);
			$data['channel_descript']  = (string)$_POST['channel_descript'];
			$data['tmp_id']            = intval($_POST['tmp_id']);
			$data['note']              = (string)$_POST['note'];
			$data['update_tm']         = time() ;
			if(empty($data['channel_name'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_add');
			    $this -> error('请输入频道名称');
			}
			if(empty($data['tmp_id'])){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_edit');
			    $this -> error('请选择模板');
			}
			if($id == $data['parent_id']){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_edit');
			    $this -> error('频道本身不允许作为上级频道');
			}
			$model = new Model();
			$log_result = $this->logcheck($where,'sl_activate_channel',$data,$model);
			if($model -> table('sl_activate_channel') -> where($where) -> save($data)){
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_list');
				$this -> writelog("编辑了id为{$id}的频道.{$log_result}", 'sl_activate_channel', $id,__ACTION__ ,"","edit");
				$this -> success('编辑成功');
			}else{
			    $this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_edit');
				$this -> error('编辑失败');
			}
		}
	}
	
	//频道删除
	function channel_del(){
	    $id     = (int)$_GET['id'];
		$where  = array('id' => $id );
		$status = (int)$_GET['status'];
		if($status == 2){
		    $data['status'] = 0;
			$data['update_tm'] = time();
			$model = new Model();
			if($model -> table('sl_activate_channel') -> where($where) -> save($data)){
				$this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_list');
				$this -> writelog('删除id为'.$id.'的频道', 'sl_activate_channel', $id,__ACTION__ ,"","del");
				$this -> success('操作成功');
			}else{
				$this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_list');
				$this -> error('操作失败');
			}
		} 
	}
	
	//频道启停用
	function channel_able(){
	    $id     = (int)$_GET['id'];
		$where  = array('id' => $id );
		$status = (int)$_GET['status'];
		// status状态为 0删除 1正常 2停用
		switch($status){
		    case 1 : $data['status'] = 2; 
			         $data['update_tm'] = time(); 
			         break;
		    case 2 : $data['status'] = 1; 
			         $data['update_tm'] = time();
			         break;
		}
	    $model = new Model();
		if($model -> table('sl_activate_channel') -> where($where) -> save($data)){
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_list');
			if($data['status']==1){
				$this -> writelog('启用了id为'.$id.'的频道', 'sl_activate_channel', $id,__ACTION__ ,"","edit");
			}else if($data['status']==2){
				$this -> writelog('停用了id为'.$id.'的频道', 'sl_activate_channel', $id,__ACTION__ ,"","edit");
			}
			$this -> success('操作成功');
		}else{
			$this -> assign('jumpUrl', '/index.php/Sl/Activate/channel_list');
			$this -> error('操作失败');
		}
	}
	
	//显示沙龙频道信息
	function show_sl_channel(){
	    if (!empty($_POST['all'])) {
			unset($_COOKIE['c_keyword']);
		} elseif(isset($_POST['keyword'])) {
			$_COOKIE['c_keyword'] = $_POST['keyword'];
		}
		
		$type_list = array(
			'checobox' => 1,
			'radio' => 1,
		);
		
		if (isset($_GET['type']) && isset($type_list[$_GET['type']])) {
			$input_type = $_GET['type'];
		} else {
			$input_type = 'checkbox';
		}
		$this->assign('input_type', $input_type);
		
		
		$Model = new Model();
		$where = array();
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;
		$zh_map = array(
        	'source_type' => $source_type,
        	'source_value' => $_SESSION['admin']['admin_id'],
        	'target_type' => $target_type
        );
        $zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
		if(!empty($zh_res) && ($zh_res['filter_type'])==1){
			
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type = CHANNEL_FILTER_TYPE;
			$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}'";
			$res = $Model->query($sql);

			foreach ($res as $item) {
				$not_in_cid[] = $item['cid'];
			}
			 $where['cid'] = array('in', $not_in_cid);
		}
        
        if (!empty($_COOKIE['c_keyword'])) {
        	$this->assign('keyword', $_COOKIE['c_keyword']);
			$db = Db::getInstance();
			$keyword = $db->escape_string(trim($_COOKIE['c_keyword']));
        	$where['chname'] = array('like', '%'. $keyword. '%');
        }
		$where['status'] = 1;

        $channels = $Model->table('sj_channel')->where($where)->field('cid,chname,category_id')->select();
        $cids = explode('_', $_COOKIE['cids']);
        $cids = array_unique($cids);
        $in_cid = array();
        foreach ($cids as $cid){
        	if (!empty($cid)) $in_cid[] = $cid;
        }
        $channel_category = D('Sj.ChannelCategory');
		$category_list = array();
		
        $category_list = $channel_category->getCategory();
        if (empty($_POST['keyword']) || trim($_POST['keyword']) == '通用') {
			$category_list[0]['name'] = '未分类';
			$category_list[0]['result'][] = array(
				'cid' => 0,
				'chname' => '通用',
				'category_id' => 0,
			);
		}
        foreach ($channels as $k => $v) {
        	$channels[$k]['checked'] = in_array($v['cid'], $in_cid);
            $category_list[$v['category_id']]['result'][] = $channels[$k];
        }

        $this->assign('category_list', $category_list);
        $this->assign('channels', $channels);
		//$this->assign('selected_channel', $_SESSION['selected_channel']);
        $this->assign('callback', $_REQUEST['callback']);
		$this->assign('selected', $_REQUEST['selected']);
		$this->assign('ready', $_REQUEST['ready']);
        $this->display('show_channel');
	}
		//活动报名列表
	function apply_list(){
		$model = new Model();
		$activate_times_category = $model -> table('sl_activate') -> select();
		$type = escape_string($_GET['category']);
		$user_status = array(
			array(
				'value' => 0,
				'name' => '拒绝'
			),
			array(
				'value' => 1,
				'name' => '通过'
			),
			array(
				'value' => 2,
				'name' => '未审核'
			)
		);
		import("@.ORG.Page");
		$count = $model -> table('sl_activate_user') -> where($where) -> count();
		$param = http_build_query($_GET);
		$Page = new page($count,20,$param);
		
		if($type == 1){
			$where = "";
		}else if($type == 2){
			$where['_string'] = "status = 0";
		}
		$apply_list = $model -> table('sl_activate_user') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->select();
		$show = $Page -> show();
		$this -> assign("apply_list",$apply_list);
		$this -> assign("user_status",$user_status);
		$this -> assign("activate_times_category",$activate_times_category);
		$this -> assign("activate_status",$activate_status);
		$this -> assign("activate_times",$activate_times);
        $this->assign("page", $show);
		$this -> display("apply_list");
	}
	
	//导出列表
	function derive_list(){
		$model = new Model();
		
		$activate_times = escape_string($_GET['activate_times']);
		$activate_status = escape_string($_GET['activate_status']);

		$where = array();
		if($activate_times != 'all'){
			$where['activate_id'] = $activate_times ;
		}
		if($activate_status != 'all'){
		  $where['status'] = $activate_status;
		}
		$apply_list = $model -> table('sl_activate_user') -> where($where) -> select();

		$file_go = 'active_'.$activate_times; 

		if($apply_list){
			foreach($apply_list as $key => $val){
			$val['type_name'] = ($val['status'] == 0) ?   '拒绝' : ($val['status'] == 1 ?'通过':'未审核');
			$file_str .= $val['id'].','.$val['user_name'].','.$val['mobile'].','.$val['email'].','.$val['company'].','.$val['level'].','.$val['activate_id'].','.$val['type_name']."\n";
			}
			header( "Cache-Control: public" );
			header( "Pragma: public" );
			header("Content-type:application/vnd.ms-excel");
			header('Content-Disposition:attachment;filename='.$file_go.'.csv');
			header('Content-Type:APPLICATION/OCTET-STREAM');
			ob_start();
			$header_str =  iconv("UTF-8",'GBK',"ID,姓名,手机,邮箱,公司,职务,活动期,状态");

			$file_str_go=  iconv("UTF-8",'GBK',$file_str);
			echo $header_str."\n";
			echo $file_str_go;
			ob_end_flush();
		}else{
			$this -> error("搜索的数据不存在");
		}
	}
	
	//编辑报名用户
	function editor_apply(){
		$model = new Model();
		$user_id = escape_string($_GET['id']);
		$status = escape_string($_GET['status']);
		$editor = $model -> query("update sl_activate_user set status = $status where id = $user_id");
		if($status==1){
			$str='通过了';
		}else if($status==0){
			$str='拒绝了';
		}else{
			$str='撤销了';
		}
		$this->writelog("{$str}报名id:{$user_id}的用户",'sl_activate_user',$user_id,__ACTION__ ,"","edit");
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/apply_list');
		$this -> success("操作成功");
	}
	
	//评论管理
	function manage_comment(){
		$model = new Model();
		$times_category = $model -> table('sl_activate') -> select();
		$status_category = array(
			array(
				'value' => 1,
				'name' => '正常'
			),
			array(
				'value' => 2,
				'name' => '屏蔽'
			)
		);
		$where = array();
		$where['_string'] = "status <> 0"; 
		import('@.ORG.Page');
		$count = $model -> table('sl_activate_comment') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$comment_list = $model -> table('sl_activate_comment') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$show = $Page -> show();
		$this -> assign("page",$show);

		$this -> assign("times_category",$times_category);
		$this -> assign("status_category",$status_category);
		$this -> assign("comment_list",$comment_list);
		$this -> display("comment_list");
	}
	
	//搜索评论
	function search_comment(){
		$model = new Model();
		$user_name = $_GET['username'];
		$activate_times = $_GET['activate_times'];
		$activate_status = $_GET['activate_status'];
		$times_category = $model -> table('sl_activate') -> select();
		$status_category = array(
			array(
				'value' => 1,
				'name' => '正常'
			),
			array(
				'value' => 2,
				'name' => '屏蔽'
			)
		);
		$where['_string'] = 'status <> 0';
		
		if(!empty($user_name)){
			$where['username'] = array('like','%'.$user_name.'%');
		}
		if($activate_times !== 'all'){
			$where['activate_id'] = array('like','%'.$activate_times.'%');
		}
		if($activate_status !== 'all'){
			$where['_string'] = 'status = '.$activate_status;
		}
		import('@.ORG.Page');
		$count = $model -> table('sl_activate_comment') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$comment_list = $model -> table('sl_activate_comment') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$show = $Page -> show();
		$this -> assign("page",$show);
		$this -> assign("user_name",$user_name);
		$this -> assign("times_category",$times_category);
		$this -> assign("status_category",$status_category);
		$this -> assign("activate_times",$activate_times);
		$this -> assign("activate_status",$activate_status);
		$this -> assign("comment_list",$comment_list);
		$this -> display("comment_list");
	}
	
	//编辑评论
	function editor_comment(){
		$model = new Model();
		$id = escape_string($_GET['id']);
		$status = escape_string($_GET['status']);
		
		$model -> query("update sl_activate_comment set status = $status where id = $id");
		$this->generatelog(array('table'=>'sl_activate_comment','status'=>$status,'id'=>$id,'location'=>'评论'));
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/manage_comment');
		$this -> success("操作成功");
	}
	
	//模板管理
	function complate_manage(){
		$model = new Model();
		$category = $_GET['category'];
		if($category !== 2){
			$where['status'] = 1;
			$add_type = 1;
		}
		if($category == 2){
			$where['status'] = 2;
			$add_type = 2;
		}
		
		import('@.ORG.Page');
		$count = $model -> table('sl_tmp_manage') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$complate_list = $model -> table('sl_tmp_manage') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> select();

		$show = $Page -> show();
		$this -> assign("add_type",$add_type);
		$this -> assign("page",$show);
		$this -> assign("complate_list",$complate_list);
		$this -> display("complate_list");
		
	}
	
	//模板搜索
	function search_complate(){
		$model = new Model();
		$tmp_name = $_GET['tmp_name'];
		$tmp_use = $_GET['tmp_use'];
		$tmp_file = $_GET['tmp_file'];
		$where['_string'] = "status <> 0";
		if(!empty($tmp_name)){
			$where['tmp_name'] = array('like','%'.$tmp_name.'%');
		}
		if(!empty($tmp_use)){
			$where['tmp_dscript'] = array('like','%'.$tmp_use.'%');
		}
		if(!empty($tmp_file)){
			$where['tmp_filename'] = array('like','%'.$tmp_file.'%');
		}
		import('@.ORG.Page');
		$count = $model -> table('sl_tmp_manage') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$complate_list = $model -> table('sl_tmp_manage') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$show = $Page -> show();
		$this -> assign("page",$show);
		$this -> assign("complate_list",$complate_list);
		$this -> display("complate_list");
	}
	
	//编辑模板
	function editor_complate(){
		$model = new Model();
		$id = escape_string($_GET['id']);
		$status = escape_string($_GET['status']);
		$where['id'] = $id;
		$data['status'] = $status;
		$affect = $model -> table('sl_tmp_manage') -> where($where) -> save($data);

		if($affect){
			$this->generatelog(array('table'=>'sl_tmp_manage','status'=>$status,'id'=>$id,'location'=>'模板'));
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/complate_manage');
			$this -> success("操作成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	//添加模板
	function add_complate(){
		$model = new Model();
		$this -> display("add_complate");
	}
	function add_complate_do(){
		$model = new Model();
		$data['tmp_name'] = escape_string($_POST['tmp_name']);
		if(empty($data['tmp_name'])){
			$this -> error("请填写模板名称");
		}
		$data['tmp_dscript'] = escape_string($_POST['tmp_dscript']);
		if(empty($data['tmp_dscript'])){
			$this -> error("请填写模板用途");
		}
		$data['tmp_filename'] = escape_string($_POST['tmp_filename']);
		if(empty($data['tmp_filename'])){
			$this -> error("请填写模板目录");
		}
		$data['tmp_dir'] = escape_string($_POST['tmp_dir']);
		if(empty($data['tmp_dir'])){
			$this -> error("请填写模板文件");
		}
		$data['note'] = escape_string($_POST['note']);
		if(empty($data['note'])){
			$this -> error("请填写备注");
		}
		$data['status'] = 1;
		$data['tmp_pic'] = $_FILES['tmp_pic'];
		if(!empty($data['tmp_pic'])){
			$data['publish_tm'] = time();
			$path = date('Ym/d/', time());
			$file_path = $path.'/';
			$config = array(
				'multi_config' => array(
					'tmp_pic' => array(
						'savepath' => UPLOAD_PATH . '/image/' . $file_path,
						'saveRule' => 'time',
					)
				)
				
			);
			$result = $this -> _uploadapk(0,$config);		
			if($result){
				$data['tmp_pic'] = $result['image'][0]['url'];
				$affect = $model -> table('sl_tmp_manage') -> add($data);
				
				if($affect){
					$this->writelog('添加模板name:'.$data['tmp_name'],'sl_tmp_manage',$affect,__ACTION__ ,"",'add');
					$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/complate_manage');
					$this -> success("操作成功");
				}else{
					$this -> error("模板添加失败！");
				}
			}else{
				$this -> error("文件上传失败");
			}
		}else{
			$this -> error("模板图片为必须");
		}
	}
	
	//编辑模板详细信息
	function editor_complate_go(){
		$model = new Model();
		$id = $_GET['id'];
		$where['id'] = $id;
		$complate_change = $model -> table('sl_tmp_manage') -> where($where) -> select();
		$this -> assign("id",$id);
		$this -> assign("complate_change",$complate_change);
		$this -> display("editor_complate");
	}
	
	function editor_complate_do(){
		$model = new Model();
		$id = $_POST['id'];
		$tmp_name = $_POST['tmp_name'];
		$tmp_dscript = $_POST['tmp_dscript'];
		$tmp_filename = $_POST['tmp_filename'];
		$tmp_dir = $_POST['tmp_dir'];
		$note = $_POST['note'];	
		$tmp_pic = $_FILES['tmp_pic'];
		if(!empty($tmp_pic)){
			$path = date('Ym/d',time());
			$file_path = $path.'/';
			$config = array(
				'multi_config' => array(
					'tmp_pic' => array(
						'savepath' => UPLOAD_PATH . '/image/' . $file_path,
						'saveRule' => 'time',
					)
				)
			);
			$result = $this -> _uploadapk(0,$config);
			$tmp_url = $result['image'][0]['url'];
		}else{
			$this -> error("图片上传失败");
		}
		if($result){
				$data['tmp_name'] = $tmp_name;
				$data['tmp_dscript'] = $tmp_dscript;
				$data['tmp_dir'] = $tmp_dir;
				$data['tmp_filename'] = $tmp_filename;
				$data['tmp_pic'] = $tmp_url;
				$data['note'] = $note;
			
			$where_go['id'] = $id;
			$log_result = $this->logcheck($where_go,'sl_tmp_manage',$data,$model);
			$affect = $model -> table('sl_tmp_manage') -> where($where_go) -> save($data);

			if($affect){
				$this->writelog("编辑模板id:{$id}.{$log_result}",'sl_tmp_manage',$id,__ACTION__ ,"",'edit');
				$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/complate_manage');
				$this -> success("操作成功");
			}else{
				$this -> error("模板编辑失败！");
			}
		}
		
	}
	
	//宣传图列表管理
	function top_pic_list(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		$category = $_GET['category'];
		$activate_select = $_GET['activate_select'];
		if($activate_select != "no" && $activate_select != null){
			$where['activate_id'] = $activate_select;
		}else{
			$fromjs = 1;
		}
		if($category != 2){
			$where['status'] = 1;
			
		}else{
			$where['status'] = 2;
			
		}
	
		$all = $model -> table('sl_top_picture') -> where($where) -> count();

	
		for($i=1;$i<=$all;$i++){
			$all_rank[] = $i;
		}
		
		import('@.ORG.Page');
		$count = $model -> table('sl_top_picture') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		if($category != 2){
			$pic_list = $model -> table('sl_top_picture') -> where($where) -> order('rank') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		}
		if($category == 2){
			$pic_list = $model -> table('sl_top_picture') -> where($where) -> order('id') -> limit($Page->firstRow . ',' . $Page->listRows) -> select();
		}
		$show = $Page -> show();
		$this -> assign("page",$show);
		$this -> assign("all_rank",$all_rank);
		$this -> assign("activate_category",$activate_category);
		$this -> assign("category",$category);
		$this -> assign("fromjs",$fromjs);
		$this -> assign("activate_select",$activate_select);
		$this -> assign("pic_list",$pic_list);
		$this -> display("top_pic_list");
	}
	
	//搜索宣传列表activate_times,pic_name,pic_url
	function search_top_pic(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		$activate_times = $_GET['activate_times'];
		$pic_name = $_GET['pic_name'];
		$pic_url = $_GET['pic_url'];
		$fromjs = $_GET['fromjs'];
		if($activate_times != 'no'){
			$where['activate_id'] = array('like','%'.$activate_times.'%');
		}
		if(!empty($pic_name)){
			$where['pic_name'] = array('like','%'.$pic_name.'%');
		}
		if(!empty($pic_url)){
			$where['pic_url'] = array('like','%'.$pic_url.'%');
		}
		import('@.ORG.Page');
		$count = $model -> table('sl_top_picture') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$pic_list = $model -> table('sl_top_picture') -> where($where) -> order('rank') -> limit($Page -> firstRow .','. $Page -> listRows) -> select();
		
		$show = $Page -> show();
		
		$this -> assign("page",$show);
		$this -> assign("fromjs",$fromjs);
		$this -> assign("pic_url",$pic_url);
		$this -> assign("pic_name",$pic_name);
		$this -> assign("activate_times",$activate_times);
		$this -> assign("activate_category",$activate_category);
		$this -> assign("pic_list",$pic_list);
		$this -> display("top_pic_list");
	}
	function generatelog($data){
		if($data){
			if($data['table']!='sl_activate_comment'){
				$str=($data['status']==0)?'删除了':(($data['status']==1)?'启用了':'停用了');
			}else{
				$str=($data['status']==0)?'删除了':(($data['status']==1)?'恢复了':'屏蔽了');
			}
			$option=($data['status']==0)?'del':'edit';
			$this->writelog("{$str}{$data['location']}id:{$data['id']}",$data['table'],$data['id'],__ACTION__ ,"",$option);
		}
	}
	//编辑宣传图片
	function editor_top_pic(){
		$model = new Model();
		$id = $_GET['id'];
		$status = $_GET['status'];
		$where['id'] = $id;
		$data['status'] = $status;
		
		$affect = $model -> table('sl_top_picture') -> where($where) -> save($data);
		if($affect){
			$this->generatelog(array('table'=>'sl_top_picture','status'=>$status,'id'=>$id,'location'=>'宣传图'));
			if($status == 2){
				$where_go['id'] = $id;
				$rank_all = $model -> table('sl_top_picture') -> where($where_go) -> select();
				$rank = $rank_all[0]['rank'];
				$map['activate_id'] = $rank_all[0]['activate_id'];
				$map['status'] = 1;
				$map['rank'] = array('gt',$rank);
				$chang_rank = $model -> table('sl_top_picture') -> where($map) -> select();
				$data = array();
				foreach($chang_rank as $key => $val){
					$data['rank'] = $val['rank'] - 1;
					$chang_id = $val['id'];
					$where_change['id'] = $chang_id;
					$log_result = $this->logcheck($where_change,'sl_top_picture',$data,$model);
					$rank_option = $model -> table('sl_top_picture') -> where($where_change) -> save($data);
					$this->writelog("编辑宣传图片id:{$id}.{$log_result}",'sl_top_picture',$id,__ACTION__ ,"","edit");
				}
			}else if($status == 1){
				$where_go['id'] = $id;
				$rank_all = $model -> table('sl_top_picture') -> where($where_go) -> select();
				$map['stauts'] = 1;
				$map['activate_id'] = $rank_all[0]['activate_id'];
				$all = $model -> table('sl_top_picture') -> where($map) -> count();
				$data['rank'] = $all + 1;
				$log_result = $this->logcheck($where_go,'sl_top_picture',$data,$model);
				$rank_option = $model -> table('sl_top_picture') -> where($where_go) -> save($data);
				$this->writelog("编辑宣传图片id:{$id}.{$log_result}",'sl_top_picture',$id,__ACTION__ ,"","edit");
			}
		}
	
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/top_pic_list');
			$this -> success("操作成功");
	}
	
	//编辑宣传图片详细信息
	function editor_top_pic_go(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		$id = $_GET['id'];
	
		$where['id'] = $id;
		$top_pic = $model -> table('sl_top_picture') -> where($where) -> select();
		$this -> assign("id",$id);
		$this -> assign("top_pic",$top_pic);
		$this -> assign("activate_category",$activate_category);
		$this -> display("editor_top_pic");
	}

	function editor_top_pic_do(){
		$model = new Model();
		$id = $_POST['id'];

		$pic_name = $_POST['pic_name'];
		$activate_times = $_POST['activate_times'];
		$note = $_POST['note'];

		if($activate_times == 'no'){
			$pic_url = $_FILES['pic_url'];
			$pic_link =  $_POST['pic_link'];
			if($pic_url){
				$path = date('Ym/d',time());
				$file_path = $path.'/';
				$config = array(
					'multi_config' => array(
						'pic_url' => array(
							'savepath' => UPLOAD_PATH . '/image/' . $file_path,
							'saveRule' => 'time',
						),
					),
					'image_p_size' => 970*275
				);
				$result = $this -> _uploadapk(0,$config);
				$data['pic_url'] = $result['image'][0]['url'];
				
			}else{
				$this -> error("图片上传失败");
			}
			
			$data['pic_name'] = $pic_name;
			$data['activate_id'] = 0;
			$data['pic_link'] = $pic_link;
			$data['note'] = $note;
		
			$where['id'] = $id;
			$log_result = $this->logcheck($where,'sl_top_picture',$data,$model);
			if($result){
				$affect = $model -> table('sl_top_picture') -> where($where) -> save($data);
			}
		}else if($activate_times !== 'no'){
			$where_go['id'] = $activate_times;
			$mine_result = $model -> table('sl_activate') -> where($where_go) -> select();
			$pic_url = $mine_result[0]['picture_url'];
			$pic_link =  $mine_result[0]['pic_link'];
			$data['pic_name'] = $pic_name;
			$data['pic_link'] = $pic_link;
			$data['activate_id'] = $activate_times;
			$data['pic_url'] = $pic_url;
			$data['note'] = $note;
			$where_id['id'] = $id;
			$log_result = $this->logcheck($where_id,'sl_top_picture',$data,$model);
			$affect = $model -> table('sl_top_picture') -> where($where_id) -> save($data);
		}

		if($affect){
			$this->writelog("编辑宣传图片id:{$id}.{$log_result}",'sl_top_picture',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/top_pic_list');
			$this -> success("操作成功");
		}else{
			$this -> error("编辑失败");
		}	
	}
	
	//添加宣传图片
	function add_top_pic(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		
		$this -> assign("activate_category",$activate_category);
		$this -> display("add_top_pic");
	}
	
	function add_top_pic_do(){
		$model = new Model();
		$data['pic_name'] = $_POST['pic_name'];
		$data['activate_id'] = $_POST['activate_times'];
		$data['pic_link'] =  $_POST['pic_link'];
		$data['note'] = $_POST['note'];
		$data['publish_tm'] = time();
		$data['status'] = 1;
		
		$map['status'] = 1;
		$map['activate_id'] = $data['activate_id'];
		$all = $model -> table('sl_top_picture') -> where($map) -> count();
		$data['rank'] = $all + 1;
		if($data['pic_name'] == null){
			$this -> error("对不起，请输入名称");
		}
		
		if($data['activate_id'] == 'no'){
			$data['pic_url'] = $_FILES['pic_url'];
			if(!empty($data['pic_url'])){
				$path = date('Ym/d',time());
				$file_path = $path.'/';
				$config = array(
					'multi_config' => array(
						'pic_url' => array(
							'savepath' => UPLOAD_PATH . '/image/' . $file_path,
							'saveRule' => 'time',
						)
					),
					'image_p_size' => 970*275
				);
				$result = $this -> _uploadapk(0,$config);
				$data['pic_url'] = $result['image'][0]['url'];
			}else{
				$this -> error("图片上传失败");
			}
			
			if($result){
				$affect = $model -> table('sl_top_picture') -> add($data);
			}
		}else if($data['activate_id'] !== 'no'){
			$where_go['id'] = $data['activate_id'];
			$mine_result = $model -> table('sl_activate') -> where($where_go) -> select();
			$data['pic_url'] = $mine_result[0]['picture_url'];
			$affect = $model -> table('sl_top_picture') -> add($data);
		}
		
		if($affect){
			$this->writelog('添加宣传图片id:'.$affect,'sl_top_picture',$affect,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/top_pic_list/id/'.$id);
			$this -> success("操作成功");
		}else{
			$this -> error("编辑失败");
		}	
	}
	
	//编辑宣传图排序
	function top_rank_manage(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$where['id'] = $id;
		$rank_all = $model -> table('sl_top_picture') -> where($where) -> select();
		$bre_rank = $rank_all[0]['rank'];
		$activate_id = $rank_all[0]['activate_id'];
		
		if($bre_rank > $rank){
			$where_rank['_string'] = "rank >= $rank and rank <$bre_rank and status = 1";
			
			$my_rank = $model -> table('sl_top_picture') -> where($where_rank) -> select();

			$where_id['id'] = $id;
			$data['rank'] = $rank;
			$affect = $model -> table('sl_top_picture') -> where($where_id) -> save($data);
			if($affect){
				foreach($my_rank as $key => $val){
					$data_go['rank'] = $val['rank'] + 1;
					$where_go['id'] = $val['id'];
					$result = $model -> table('sl_top_picture') -> where($where_go) -> save($data_go);
				}
			}
		}else if($bre_rank < $rank){
			$where_rank['_string'] = "rank <= $rank and rank >$bre_rank and status =1";
			$my_rank = $model -> table('sl_top_picture') -> where($where_rank) -> select();
			$where_id['id'] = $id;
			$data['rank'] = $rank;
			$affect = $model -> table('sl_top_picture') -> where($where_id) -> save($data);
			if($affect){
				foreach($my_rank as $key => $val){
					$data_go['rank'] = $val['rank'] - 1;
					$where_go['id'] = $val['id'];
					$result = $model -> table('sl_top_picture') -> where($where_go) -> save($data_go);
				}
			}
		}	
		if($affect){
			$this->writelog('修改宣传图id:'.$id.',排序rank:'.$rank,'sl_top_picture',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/top_pic_list');
			$this -> success("操作成功");
		}else{
			$this -> error("修改排序失败");
		}
		//友情链接添加
	function friend_link_add(){
		$link = M('friend_link');
		$activate_category = $link -> table('sl_activate') -> select();
		$id = $_GET['id'];
		$this -> assign("activate_category",$activate_category);
		
		
		
	
	}
	function friend_link(){
		$this->display();
	}
	}
	
	//广告图列表
	function ad_list(){
		$model = new Model();
		$category = $_GET['category'];
		
		$activate_all = $model -> table('sl_activate') -> select();
		$activate_select = $_GET['activate_select'];

		if($activate_select != "no" && $activate_select != null){
			$where['activate_id'] = $activate_select;
		}else{
			$fromjs = 1;
		}
		if($category != 2){
			$where['status'] = 1;
		}
		if($category == 2){
			$where['status'] = 2;
		}
		$rank_all = $model -> table('sl_ad_pic') -> where($where) -> count();
		for($i=1;$i<=$rank_all;$i++){
			$rank_arr[] = $i; 
		}
		import('@.ORG.Page');
		$count = $model -> table('sl_ad_pic') -> where($where) -> count();

		$param = http_build_query($_GET['page']);	
		$Page = new page($count,20,$param);
		$show = $Page -> show();
		if($category != 2){
			$ad_list = $model -> table('sl_ad_pic') -> where($where) -> order('rank') -> limit($Page -> firstRow .','. $Page -> listRows) -> select(); 
		}else if($category == 2){
			$ad_list = $model -> table('sl_ad_pic') -> where($where) -> order('id') -> limit($Page -> firstRow .','. $Page -> listRows) -> select(); 
		}

		$this -> assign("activate_all",$activate_all);
		$this -> assign("page",$show);
		$this -> assign("rank_arr",$rank_arr);
		$this -> assign("category",$category);
		$this -> assign("activate_select",$activate_select);
		$this -> assign("fromjs",$fromjs);
		$this -> assign("ad_list",$ad_list);
		$this -> display("ad_list");
	}
	
	//搜索广告图
	function search_ad(){
		$model = new Model();
		$activate_all = $model -> table('sl_activate') -> select();
		$activate_times = $_GET['activate_times'];
		$ad_name = $_GET['ad_name'];
		$ad_url = $_GET['ad_url'];
		$fromjs = $_GET['fromjs'];
		$category = 3;
		if($activate_times != 'no'){
			$where['activate_id'] = array('like','%'.$activate_times.'%');
		}
		if(!empty($ad_name)){
			$where['ad_name'] = array('like','%'.$ad_name.'%');
		}
		if(!empty($ad_url)){
			$where['pic_url'] = array('like','%'.$ad_url.'%');
		}
		import('@.ORG.Page');
		$count = $model -> table('sl_ad_pic') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$ad_list = $model -> table('sl_ad_pic') -> where($where) -> limit($Page -> firstRow .','. $Page -> listRows) -> select();
		$show = $Page -> show();
		$this -> assign("fromjs",$fromjs);
		$this -> assign("page",$show);
		$this -> assign("category",$category);
		$this -> assign("activate_all",$activate_all);
		$this -> assign("ad_list",$ad_list);
		$this -> assign("ad_name",$ad_name);
		$this -> assign("ad_url",$ad_url);
		$this -> assign("activate_times",$activate_times);
		$this -> display("ad_list");
	}
	
	//编辑广告图
	function editor_ad(){
		$model = new Model();
		$id = $_GET['id'];
		$status = $_GET['status'];
		$where['id'] = $id;
		$data = array(
			'status' => $status
		);
		
		$affect = $model -> table('sl_ad_pic') -> where($where) -> save($data);

		if($affect){
			$this->generatelog(array('table'=>'sl_ad_pic','status'=>$status,'id'=>$id,'location'=>'广告图'));
			if($status == 2){
				$where_go['id'] = $id;
				$rank_all = $model -> table('sl_ad_pic') -> where($where_go) -> select();
				$rank = $rank_all[0]['rank'];
				$map['activate_id'] = $rank_all[0]['activate_id'];
				$map['status'] = 1;
				$map['rank'] = array('gt',$rank);
				$chang_rank = $model -> table('sl_ad_pic') -> where($map) -> select();
				$data = array();
				foreach($chang_rank as $key => $val){
					$data['rank'] = $val['rank'] - 1;
					$chang_id = $val['id'];
					$where_change['id'] = $chang_id;
					$log_result = $this->logcheck($where_change,'sl_ad_pic',$data,$model);
					$rank_option = $model -> table('sl_ad_pic') -> where($where_change) -> save($data);
					$this->writelog('编辑广告图片id:'.$id.".{$log_result}",'sl_ad_pic',$id,__ACTION__ ,"","edit");
				}
			}else if($status == 1){
				$where_to['id'] = $id;
				$rank_all = $model -> table('sl_ad_pic') -> where($where_to) -> select();
				$map['status'] = 1;
				$map['activate_id'] = $rank_all[0]['activate_id'];
				$all = $model -> table('sl_ad_pic') -> where($map) -> count();
				$data['rank'] = $all+1;
				$data['status'] = 1;
				$log_result = $this->logcheck($where_to,'sl_ad_pic',$data,$model);
				$rank_option = $model -> table('sl_ad_pic') -> where($where_to) -> save($data);
				$this->writelog('编辑广告图片id:'.$id.".{$log_result}",'sl_ad_pic',$id,__ACTION__ ,"","edit");
			}
		}
		
			
		$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/ad_list');
		$this -> success("操作成功");
		
	}
	
	//编辑广告图详细信息
	function editor_ad_go(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		$id = $_GET['id'];
	
		$where['id'] = $id;
		$ad_pic = $model -> table('sl_ad_pic') -> where($where) -> select();
		$this -> assign("id",$id);
		$this -> assign("ad_pic",$ad_pic);
		$this -> assign("activate_category",$activate_category);
		$this -> display("editor_ad_pic");
	}

	function editor_ad_do(){
		$model = new Model();
		$id = $_POST['id'];
		$ad_name = $_POST['ad_name'];
		$activate_times = $_POST['activate_times'];
		$pic_link =  $_POST['pic_link'];
		$new_link = $_POST['new_link'];


		$pic_url = $_FILES['pic_url'];
		if($pic_url){
			$path = date('Ym/d',time());
			$file_path = $path.'/';
			$config = array(
				'multi_config' => array(
					'pic_url' => array(
						'savepath' => UPLOAD_PATH . '/image/' . $file_path,
						'saveRule' => 'time',
					),
				'image_p_size' => 172*50
				)
			);
			$result = $this -> _uploadapk(0,$config);
			$data['pic_url'] = $result['image'][0]['url'];
			
		}else{
			$this -> error("图片上传失败");
		}
		
		$data['ad_name'] = $ad_name;
		$data['activate_id'] = ($activate_times!='no')?$activate_times:0;
		$data['pic_link'] = $pic_link;
		$data['new_link'] = $new_link;

		$where['id'] = $id;
		if($result){
			$log_result = $this->logcheck($where,'sl_ad_pic',$data,$model);
			$affect = $model -> table('sl_ad_pic') -> where($where) -> save($data);
		}
		if($affect){
			$this->writelog('编辑广告图片id:'.$id.".{$log_result}",'sl_ad_pic',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/ad_list');
			$this -> success("操作成功");
		}else{
			$this -> error("编辑失败");
		}	
	}
	
	//添加广告图
	function add_ad(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		
		$this -> assign("activate_category",$activate_category);
		$this -> display("add_ad_pic");
	}
	
	function add_ad_do(){
		$model = new Model();
		$data['ad_name'] = $_POST['ad_name'];
		$data['activate_id'] = $_POST['activate_times'];
		$data['pic_link'] =  $_POST['pic_link'];
		$data['new_link'] = $_POST['new_link'];
		$data['publish_tm'] = time();
		$data['status'] = 1;
		$map['status'] = 1;
		$map['activate_id'] = $data['activate_id'];
		$all = $model -> table('sl_ad_pic') -> where($map) -> count();
		$data['rank'] = $all + 1;
		
			$pic_url = $_FILES['pic_url'];
			if(!empty($pic_url)){
				$path = date('Ym/d',time());
				$file_path = $path.'/';
				$config = array(
					'multi_config' => array(
						'pic_url' => array(
							'savepath' => UPLOAD_PATH . '/image/' . $file_path,
							'saveRule' => 'time',
						)
					),
					'image_p_size' => 172*50
				);
				$result = $this -> _uploadapk(0,$config);
				$data['pic_url'] = $result['image'][0]['url'];
			}else{
				$this -> error("图片上传失败");
			}
			
			if($result){
				$affect = $model -> table('sl_ad_pic') -> add($data);
			}
		if($affect){
			$this->writelog('添加广告图片id:'.$affect,'sl_ad_pic',$affect,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/ad_list/id/'.$id);
			$this -> success("操作成功");
		}else{
			$this -> error("编辑失败");
		}	
	}
	
	//编辑广告图排序
	function ad_rank_manage(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$where['id'] = $id;
		$rank_all = $model -> table('sl_ad_pic') -> where($where) -> select();
		$bre_rank = $rank_all[0]['rank'];
		$activate_id = $rank_all[0]['activate_id'];
		if($bre_rank > $rank){
			$where_rank['_string'] = "rank >= $rank and rank <$bre_rank and activate_id = $activate_id and status = 1";
			$my_rank = $model -> table('sl_ad_pic') -> where($where_rank) -> select();
			$where_id['id'] = $id;
			$data['rank'] = $rank;
			$affect = $model -> table('sl_ad_pic') -> where($where_id) -> save($data);
			if($affect){
				foreach($my_rank as $key => $val){
					$data_go['rank'] = $val['rank'] + 1;
					$where_go['id'] = $val['id'];
					$result = $model -> table('sl_ad_pic') -> where($where_go) -> save($data_go);
				}
			}
		}else if($bre_rank < $rank){
			$where_rank['_string'] = "rank <= $rank and rank >$bre_rank and activate_id = $activate_id and status = 1";
			$my_rank = $model -> table('sl_ad_pic') -> where($where_rank) -> select();
			$where_id['id'] = $id;
			$data['rank'] = $rank;
			$affect = $model -> table('sl_ad_pic') -> where($where_id) -> save($data);
			if($affect){
				foreach($my_rank as $key => $val){
					$data_go['rank'] = $val['rank'] - 1;
					$where_go['id'] = $val['id'];
					$result = $model -> table('sl_ad_pic') -> where($where_go) -> save($data_go);
				}
			}
		}	
		if($result){
			$this->writelog('修改广告图id:'.$id.',排序rank:'.$rank,'sl_ad_pic',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/ad_list/category/1');
			$this -> success("操作成功");
		}else{
			$this -> error("修改排序失败");
		}
	}
	
 	//友情链接图列表
	function friend_link_list(){
		$model = new Model();
		$category = $_GET['category'];
		$activate_select = $_GET['activate_select'];
		$activate_category = $model -> table('sl_activate') -> select();
		
		if($activate_select != 'no' && $activate_select != null){
			$where['activate_id'] = $activate_select;
		}else{
			$fromjs = 1;
		}
		if($category != 2){
			$where['status'] = 1;
		}
		if($category == 2){
			$where['status'] = 2;
		}

		$rank_all = $model -> table('sl_friend_link') -> where($where) -> count();
		for($i=1;$i<=$rank_all;$i++){
			$rank_arr[] = $i; 
		}
		
		import('@.ORG.Page');
		$count = $model -> table('sl_friend_link') -> where($where) -> count();
		$param = http_build_query($_GET['page']);	
		$Page = new page($count,20,$param);
		$show = $Page -> show();
		if($category != 2){
			$friend_link_list = $model -> table('sl_friend_link') -> where($where) -> order('rank') -> limit($Page -> firstRow .','. $Page -> listRows) -> select(); 
		}else if($category == 2){
			$friend_link_list = $model -> table('sl_friend_link') -> where($where) -> order('id') -> limit($Page -> firstRow .','. $Page -> listRows) -> select(); 
		}
		$this -> assign("rank_arr",$rank_arr);
		$this -> assign("activate_select",$activate_select);
		$this -> assign("activate_category",$activate_category);
		$this -> assign("page",$show);
		$this -> assign("fromjs",$fromjs);
		$this -> assign("category",$category);
		$this -> assign("friend_link_list",$friend_link_list);
		$this -> display("friend_link_list");
	}
	
	//搜索友情链接图片
	function search_friend_link(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		$activate_times = $_GET['activate_times'];
		$link_name = $_GET['link_name'];
		$pic_link = $_GET['pic_link'];
		$fromjs = 1;
		if($activate_times != 'no'){
			$where['activate_id'] = array('like','%'.$activate_times.'%');
		}
		if(!empty($link_name)){
			$where['link_name'] = array('like','%'.$link_name.'%');
		}
		if(!empty($pic_link)){
			$where['pic_link'] = array('like','%'.$pic_link.'%');
		}
		import('@.ORG.Page');
		$count = $model -> table('sl_friend_link') -> where($where) -> count();
		$param = http_build_query($_GET['page']);
		$Page = new page($count,20,$param);
		$friend_link_list = $model -> table('sl_friend_link') -> where($where) -> limit($Page -> firstRow .','. $Page -> listRows) -> select();
		$show = $Page -> show();
		$this -> assign("page",$show);
		$this -> assign("activate_category",$activate_category);
		$this -> assign("friend_link_list",$friend_link_list);
		$this -> assign("link_name",$link_name);
		$this -> assign("category",$category);
		$this -> assign("fromjs",$fromjs);
		$this -> assign("pic_link",$pic_link);
		$this -> assign("activate_times",$activate_times);
		$this -> display("friend_link_list");
	}
	
	//编辑友情链接
	function editor_friend_link(){
		$model = new Model();
		$id = $_GET['id'];
		$status = $_GET['status'];
		$where['id'] = $id;
		$data['status'] = $status;
		
		$affect = $model -> table('sl_friend_link') -> where($where) -> save($data);
		
		if($affect){
			$this->generatelog(array('table'=>'sl_friend_link','status'=>$status,'id'=>$id,'location'=>'友情链接'));
			if($status == 2){
				$where_go['id'] = $id;
				$rank_all = $model -> table('sl_friend_link') -> where($where_go) -> select();		
				$rank = $rank_all[0]['rank'];
				$map['status'] = 1;
				$map['activate_id'] = $rank_all[0]['activate_id'];
				$map['rank'] = array('gt',$rank);
				$chang_rank = $model -> table('sl_friend_link') -> where($map) -> select();
				$data = array();
				foreach($chang_rank as $key => $val){
					$data['rank'] = $val['rank'] - 1;
					$chang_id = $val['id'];
					$where_change['id'] = $chang_id;
					$log_result = $this->logcheck($where_change,'sl_friend_link',$data,$model);
					$rank_option = $model -> table('sl_friend_link') -> where($where_change) -> save($data);
					$this->writelog('编辑友情链接id:'.$id.".{$log_result}",'sl_friend_link',$id,__ACTION__ ,"","edit");
				}
			}else if($status == 1){
				$where_go['id'] = $id;
				$rank_all = $model -> table('sl_friend_link') -> where($where_go) -> select();
				$map['status'] = 1;
				$map['activate_id'] = $rank_all[0]['activate_id'];
				$all = $model -> table('sl_friend_link') -> where($map) -> count();
				$data['rank'] = $all + 1;
				$log_result = $this->logcheck($where_go,'sl_friend_link',$data,$model);
				$rank_option = $model -> table('sl_friend_link') -> where($where_go) -> save($data);
				$this->writelog('编辑友情链接id:'.$id.".{$log_result}",'sl_friend_link',$id,__ACTION__ ,"","edit");
			}
		}
		
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/friend_link_list');
			$this -> success("操作成功");
	}
	
	//编辑友情链接详细信息
	function editor_friend_link_go(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		$id = $_GET['id'];
	
		$where['id'] = $id;
		$friend_link = $model -> table('sl_friend_link') -> where($where) -> select();
		$this -> assign("id",$id);
		$this -> assign("friend_link",$friend_link);
		$this -> assign("activate_category",$activate_category);
		$this -> display("editor_friend_link");
	}

	function editor_friend_link_do(){
		$model = new Model();
		$id = $_POST['id'];
		$link_name = $_POST['link_name'];
		$activate_times = $_POST['activate_times'];
		$pic_link =  $_POST['pic_link'];
		$new_link = $_POST['new_link'];


		$pic_url = $_FILES['pic_url'];
		if($pic_url){
			$path = date('Ym/d',time());
			$file_path = $path.'/';
			$config = array(
				'multi_config' => array(
					'pic_url' => array(
						'savepath' => UPLOAD_PATH . '/image/' . $file_path,
						'saveRule' => 'time',
					)
				),
				'image_p_size' => 88*30
			);
			$result = $this -> _uploadapk(0,$config);
			$data['pic_url'] = $result['image'][0]['url'];
			
		}else{
			$this -> error("图片上传失败");
		}
		
		$data['link_name'] = $link_name;
		$data['activate_id'] = ($activate_times!='no')?$activate_times:0;
		$data['pic_link'] = $pic_link;
		$data['new_link'] = $new_link;
		$where['id'] = $id;
		if($result){
			$log_result = $this->logcheck($where,'sl_friend_link',$data,$model);
			$affect = $model -> table('sl_friend_link') -> where($where) -> save($data);
		}
		if($affect){
			$this->writelog('编辑友情链接图片id:'.$id.".{$log_result}",'sl_friend_link',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/friend_link_list');
			$this -> success("操作成功");
		}else{
			$this -> error("编辑失败");
		}	
	} 
	
	//添加友情链接
	function add_friend_link(){
		$model = new Model();
		$activate_category = $model -> table('sl_activate') -> select();
		
		$this -> assign("activate_category",$activate_category);
		$this -> display("add_friend_link");
	}
	
	function add_friend_link_do(){
		$model = new Model();
		$link_name = $_POST['link_name'];
		$activate_times = $_POST['activate_times'];
		$new_link = $_POST['new_link'];
		$pic_link = $_POST['pic_link'];
		$pic_url = $_FILES['pic_url'];
		
		if($pic_url){
			$path = date('Ym/d',time());
			$file_path = $path.'/';
			$config = array(
				'multi_config' => array(
					'pic_url' => array(
						'savepath' => UPLOAD_PATH . '/image/' . $file_path,
						'saveRule' => 'time',
					)
				),
				'image_p_size' => 88*30
			);
			$result = $this -> _uploadapk(0,$config);
			$data['pic_url'] = $result['image'][0]['url'];
			
		}else{
			$this -> error("图片上传失败");
		}
		
		$data['link_name'] = $link_name;
		$data['activate_id'] = $activate_times;
		$data['pic_link'] = $pic_link;
		$data['new_link'] = $new_link;
		$data['status'] = 1;
		$where_rank['activate_id'] = $activate_times;
		$rank_all = $model ->  table('sl_friend_link') ->  where($where_rank) -> count();
		$data['rank'] = $rank_all + 1;
		if($result){
			$affect = $model -> table('sl_friend_link') -> where($where) -> add($data);
		}

		$id_all = $model -> table('sl_friend_link') -> count();
		$id = $id_all + 1;
		
		if($affect){
			$this->writelog('添加友情链接图片id:'.$id,'sl_friend_link',$id,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/friend_link_list');
			$this -> success("操作成功");
		}else{
			$this -> error("添加失败");
		}
		
	}
	
		//编辑广告图排序
	function friend_link_rank_manage(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$where['id'] = $id;
		$rank_all = $model -> table('sl_friend_link') -> where($where) -> select();
		$bre_rank = $rank_all[0]['rank'];
		$activate_id = $rank_all[0]['activate_id'];
		if($bre_rank > $rank){
			$where_rank['_string'] = "rank >= $rank and rank <$bre_rank and activate_id = $activate_id and status = 1";
			$my_rank = $model -> table('sl_friend_link') -> where($where_rank) -> select();
			$where_id['id'] = $id;
			$data['rank'] = $rank;
			$affect = $model -> table('sl_friend_link') -> where($where_id) -> save($data);
			if($affect){
				foreach($my_rank as $key => $val){
					$data_go['rank'] = $val['rank'] + 1;
					$where_go['id'] = $val['id'];
					$result = $model -> table('sl_friend_link') -> where($where_go) -> save($data_go);
				}
			}
		}else if($bre_rank < $rank){
			$where_rank['_string'] = "rank <= $rank and rank >$bre_rank and activate_id = $activate_id and status = 1";
			$my_rank = $model -> table('sl_friend_link') -> where($where_rank) -> select();
			$where_id['id'] = $id;
			$data['rank'] = $rank;
			$affect = $model -> table('sl_friend_link') -> where($where_id) -> save($data);
			if($affect){
				foreach($my_rank as $key => $val){
					$data_go['rank'] = $val['rank'] - 1;
					$where_go['id'] = $val['id'];
					$result = $model -> table('sl_friend_link') -> where($where_go) -> save($data_go);
				}
			}
		}	
		if($result){
			$this->writelog('修改友情鏈接图id:'.$id.',排序rank:'.$rank,'sl_friend_link',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/'.GROUP_NAME.'/Activate/friend_link_list/category/1');
			$this -> success("操作成功");
		}else{
			$this -> error("修改排序失败");
		}

	}
		
	function article_img_add(){
		//上传配置
		$time_path = date("Ym/d/",time());
		
		$config = array(
			"uploadPath" => "image/".$time_path , //保存路径
			"fileType" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" ) , //文件允许格式
			"fileSize" => 1000 //文件大小限制，单位KB
		);
		//原始文件名，表单名固定，不可配置
		$oriName = htmlspecialchars( $_POST[ 'fileName' ] , ENT_QUOTES );

		//上传图片框中的描述表单名称，
		$title = htmlspecialchars( $_POST[ 'pictitle' ] , ENT_QUOTES );

		//文件句柄
		$file = $_FILES[ "upfile" ];

		//文件上传状态,当成功时返回SUCCESS，其余值将直接返回对应字符窜并显示在图片预览框，同时可以在前端页面通过回调函数获取对应字符窜
		$state = "SUCCESS";

		//重命名后的文件名
		$fileName = "";

		//保存路径
		$path = $config[ 'uploadPath' ];

		if ( !file_exists( $path ) ) {
			mkdir( "$path" , 0777 ,true);
		}
		//格式验证
		$current_type = strtolower( strrchr( $file[ "name" ] , '.' ) );
		if ( !in_array( $current_type , $config[ 'fileType' ] ) || false == getimagesize( $file[ "tmp_name" ] ) ) {
			$state = "不允许的图片格式";
		}
		//大小验证
		$file_size = 1024 * $config[ 'fileSize' ];
		if ( $file[ "size" ] > $file_size ) {
			$state = "图片大小超出限制";
		}
		//保存图片
		if ( $state == "SUCCESS" ) {
			$tmp_file = $file[ "name" ];
			$fileName = $path . rand( 1 , 10000 ) . time() . strrchr( $tmp_file , '.' );
			$result = move_uploaded_file( $file[ "tmp_name" ] , UPLOAD_PATH.'/'.$fileName );
			if ( !$result ) {
				$state = "未知错误";
			}
		}
		//向浏览器返回数据json数据
		/**
		 * 返回数据格式
		 * {
		 *   'url'      :'a.jpg',   //保存后的文件路径
		 *   'title'    :'hello',   //文件描述，对图片来说在前端会添加到title属性上
		 *   'original' :'b.jpg',   //原始文件名
		 *   'state'    :'SUCCESS'  //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
		 * }
		 */
		echo "{'url':'" . $fileName . "','title':'" . $title . "','original':'" . $oriName . "','state':'" . $state . "'}";

	}
	function activate_preview(){
		$id = $_GET['id'];
		$this -> error('项目正在开发中....');
	}
	function next_forecast_preview(){
		$this -> error('项目正在开发中....');	
	}

	//安智年度评选页面_评选软件列表
	function AnzhiSoftList() {
		

		//软件分类
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"sort",
			//"selected"=>$cid[0]
		);
		$conf_list = $category->getCategory($array_config);

		//软件列表
		$m = M('year_soft');
		$year_list = $m -> table('sl_year_list') -> where(array('status' => 1)) -> order('year DESC') -> select();
		
		
		if($_GET['the_year']){
			$the_year = $_GET['the_year'];
		}else{
			$the_year = $year_list[0]['id'];
		}
		
		$add_channel_list = $m -> table('sl_year_channel') -> where(array('year_id' => $the_year,'status' => 1)) -> select();
		foreach($add_channel_list as $key => $val){
			$channel_name_result = $m -> table('sl_channel_list') -> where(array('id' => $val['channel_id'])) -> select();
			$val['channel'] = $channel_name_result[0]['channel'];
			$add_channel_list[$key] = $val;
		}
			
		
		
		//ajax改变二级下拉菜单
		if($_GET['from'] == 1){
			echo json_encode($add_channel_list);
			exit;
		}
		if($_GET['select_year']){
			$my_year = $_GET['select_year'];
		}else{
			$my_year = $year_list[0]['id'];
		}
		
		$channel_list = $m -> table('sl_year_channel') -> where(array('year_id' => $my_year,'status' => 1)) -> select();
	
		foreach($channel_list as $key => $val){
			$channel_name_results = $m -> table('sl_channel_list') -> where(array('id' => $val['channel_id'])) -> select();
			$val['channel'] = $channel_name_results[0]['channel'];
			$channel_list[$key] = $val;
		}
	
		if($_GET['_channel']){
			$_channel = $_GET['_channel'];
		}else{
			$_channel = $channel_list[0]['channel_id'];
		}
	
		$softlist = $m->where("channel='{$_channel}' and year = '{$my_year}'")->order("pos")->select();
		//查询包名对应信息
		if($_GET['from'] == 2){
			$package = $_GET['my_package'];
			$year = $_GET['year'];
			$channel = $_GET['channel'];
			$package_result = $m -> table('sj_soft') -> where(array('package' => $package,'hide' => 1,'status' => 1)) -> order('softid') -> select();
		
			if(!$package_result){
				echo json_encode('no');
				exit;
			}
			if($_GET['edit']){
				$id = $_GET['id'];
				$have_where['_string'] = "year = {$year} and channel = {$channel} and id != {$id} and package = '{$package}'";
				$have_package = $m -> table('sj_year_soft') -> where($have_where) -> select();
			}else{
				$have_package = $m -> table('sj_year_soft') -> where(array('year' => $year,'channel' => $channel,'package' => $package)) -> select();
			}

			if($have_package){
				echo json_encode('have');
				exit;
			}
			$my_package['softname'] = $package_result[0]['softname'];
			$pacakge_category_id = substr($package_result[0]['category_id'],1,strlen($package_result[0]['category_id'] - 1));
			$package_category = $m -> table('sj_category') -> where(array('category_id' => $pacakge_category_id)) -> select();
			$my_package['category_name'] = $package_category[0]['name'];
			echo json_encode($my_package);
			exit;
		}
		$soft_num = count($softlist);
		$id_arr = array();
		if($softlist) {
			foreach($softlist as $k=>$v) {
				$tmp = "<select rel='{$v['id']}' name='rank' class='extent_rank'>";
				for($i=1;$i<=$soft_num;$i++) {
					$select = $i==$v['pos'] ? ' selected' : '';
					$tmp .= "<option value='{$i}'{$select}>{$i}</option>";
				}
				$tmp .= "</select>";
				$softlist[$k]['pos_str'] = $tmp;
				$id_arr[$v['id']] = $v['id'];
			}
			
			foreach($softlist as $key => $val){
				$year_name = $m -> table('sl_year_list') -> where(array('id' => $val['year'])) -> find();
			
				$val['year_name'] = $year_name['year'];
				$softlist[$key] = $val;
			}
		}

		$this->assign('add_channel_list',$add_channel_list);
		$this->assign('my_year',$my_year);
		$this->assign('year_list',$year_list);
		$this->assign('channel_list',$channel_list);
		$this->assign('id_str',json_encode($id_arr));
		$this->assign('softlist',$softlist);
		$this->assign('conflist',$conf_list);
		$this->display();
	}

	//安智年度评选页面_评选软件列表_排序
	function AnzhiSoftList_sequence() {
	    if(isset($_GET)){
			$table       = 'sj_year_soft';
			$field       = 'pos';
			$where       = "channel = '{$_GET['_channel']}'";
			$extent_id   = (int)$_GET['id'];
			$target_rank = (int)$_GET['pos'];

			$where_rank = array(
				'channel' => $_GET['_channel'],
				'year' => $_GET['select_year']
			);

			//更新排序
		    $param = $this->_updateRankInfo($table,$field,$extent_id,$where_rank,$target_rank);
			exit(json_encode($param));
		}
	}
	//安智年度评选页面_评选软件列表_编辑
	function edit_AnzhiSoftList(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sj_year_soft') -> where(array('id' => $id)) -> select();
		$year_list = $model -> table('sl_year_list') -> where(array('status' => 1)) -> order('year DESC') -> select();
		$channel_list = $model -> table('sl_year_channel') -> where(array('year_id' => $result[0]['year'],'status' => 1)) -> select();
		foreach($channel_list as $key => $val){
			$channel_name_result = $model -> table('sl_channel_list') -> where(array('id' => $val['channel_id'])) -> select();
			$val['channel'] = $channel_name_result[0]['channel'];
			$channel_list[$key] = $val;
		}
	
		$this -> assign('result',$result[0]);
		$this -> assign('year_list',$year_list);
		$this -> assign('channel_list',$channel_list);
		$this -> display();
	}
	
	
	//安智年度评选页面_评选软件列表_添加
	function AnzhiSoftList_add() {
		if(!$_POST['name']) {
			$this -> error("软件名称不能为空，请填写！");
		} else if(!$_POST['name_show']) {
			$this -> error("显示的软件名称不能为空，请填写！");
		} else if(!$_POST['package']) {
			$this -> error("包名不能为空，请填写！");
		} else if(!$_POST['sort_name']) {
			$this -> error("请填写所属类别");
		}
		
		$m_soft = M('soft');
		$exists = $m_soft->where("package='{$_POST['package']}' AND status=1 AND hide=1 AND channel_id=''")->select();
		if(!$exists) {
			$this -> error("该包名软件不存在！");
		}

		$m = M('year_soft');
		$exists2 = $m->where("package='{$_POST['package']}' and channel='{$_POST['channel']}'")->select();
		if($exists2) {
			$this -> assign('jumpUrl','AnzhiSoftList');
			$this -> error("该包名软件在该频道已存在！");
		}

		$max = $m->where("channel='{$_POST['channel']}' and year = {$_POST['the_year']}")->field("MAX(pos) as max")->select();
		$max = $max[0]['max'] + 1;

		$fields = array(
			'name' => $_POST['name'],
			'name_show' => $_POST['name_show'],
			'package' => $_POST['package'],
			//'sort' => $_POST['sort'],
			'sort_name' => $_POST['sort_name'],
			'channel' => $_POST['channel'],
			'pos' => $max,
			'year' => $_POST['the_year']
		);
		$res=$m->add($fields);
		$this->writelog('评论软件列表新增id:'.$res,'sj_year_soft',$res,__ACTION__ ,"","add");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/AnzhiSoftList/_channel/'.$_POST['channel'].'/select_year/'.$_POST['the_year']);
		$this->success('恭喜您，软件添加成功！');
	}

	//安智年度评选页面_评选软件列表_编辑
	function AnzhiSoftList_edit() {
		$m_soft = M('soft');
		$exists = $m_soft->where("package='{$_POST['edit_package']}' AND status=1 AND hide=1")->select();
		if(!$exists) {
			$this -> error("该包名软件不存在！");
		}

		$m = M('year_soft');
		$exists2 = $m->where("package='{$_POST['edit_package']}' AND channel='{$_POST['eidt_channel']}' AND year = {$_POST['edit_year']} and id!='{$_POST['edit_id']}'")->select();
		if($exists2) {
			$this -> error("该包名软件在该频道已存在！");
		}
		$have_been = $m -> where(array('id' => $_POST['edit_id'])) -> select();
		if($have_been[0]['channel'] != $_POST['edit_channel'] || $have_been[0]['year'] != $_POST['edit_the_year']){
			$need_where['_string'] = "channel = {$have_been[0]['channel']} and year = {$have_been[0]['year']} and pos > {$have_been[0]['pos']}";
			$need_result = $m -> where($need_where) -> select();
			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_data['pos'] = $val['pos'] - 1;
				$update_result = $m -> where($update_where) -> save($update_data);
			}
			$the_count = $m -> where(array('channel' => $_POST['edit_channel'],'year' => $_POST['edit_the_year'])) -> count();
			$data_1=array('name'=>$_POST['edit_name'],'name_show'=>$_POST['edit_name_show'],'package'=>$_POST['edit_package'],'sort_name'=>$_POST['edit_sort_name'],'channel'=>$_POST['edit_channel'],'year' => $_POST['edit_the_year'],'pos' => $the_count + 1);
			$log_result = $this->logcheck(array('id'=>$_POST['edit_id']),'sj_year_soft',$data_1,$m);
			$m->where("id='{$_POST['edit_id']}'")->save($data_1);
		}else{
			$data_1=array('name'=>$_POST['edit_name'],'name_show'=>$_POST['edit_name_show'],'package'=>$_POST['edit_package'],'sort_name'=>$_POST['edit_sort_name'],'channel'=>$_POST['edit_channel'],'year' => $_POST['edit_the_year']);
			$log_result = $this->logcheck(array('id'=>$_POST['edit_id']),'sj_year_soft',$data_1,$m);
			$m->where("id='{$_POST['edit_id']}'")->save($data_1);
		}
		$this->writelog('编辑评选软件列表id:'.$_POST['edit_id'].".{$log_result}",'sj_year_soft',$_POST['edit_id'],__ACTION__ ,"","edit");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/AnzhiSoftList/_channel/'.$_POST['edit_channel'].'/select_year/'.$_POST['edit_the_year']);
		$this->success('恭喜您，软件编辑成功！');
	}

	//安智年度评选页面_评选软件列表_删除
	function AnzhiSoftList_del() {
		$model = new Model();
		$have_been = $model -> table('sj_year_soft') -> where(array('id' => $_GET['id'])) -> select();
		$need_where['_string'] = "channel = {$have_been[0]['channel']} and year = {$have_been[0]['year']} and pos > {$have_been[0]['pos']}";
		$need_result = $model -> table('sj_year_soft') -> where($need_where) -> select();
		foreach($need_result as $key => $val){
			$update_where['id'] = $val['id'];
			$update_data['pos'] = $val['pos'] - 1;
			$update_result = $model -> table('sj_year_soft') -> where($update_where) -> save($update_data);
		}
		$softs = $model->query("DELETE FROM sj_year_soft WHERE id='{$_GET['id']}'");
		$this->writelog("安智年度评选页面_评选软件列表_删除了id为{$_GET['id']}的软件",'sj_year_soft',$_GET['id'],__ACTION__ ,"","del");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/AnzhiSoftList/_channel/'.$_GET['_channel'].'/select_year/'.$have_been[0]['year']);
		$this->success('恭喜您，软件删除成功！');
	}

	//安智年度评选页面_现场照片
	function ScenePhoto() {
		$m = M('year_scenephoto_more');
		$year_list = $m -> table('sl_year_list') -> where(array('status' => 1)) -> order('year DESC') -> select();
		if($_GET['select_year']){
			$my_year = $_GET['select_year'];
		}else{
			$my_year = $year_list[0]['id'];
		}
		$more_url = $m->where("year={$my_year}")->select();

		$m = M('year_scenephoto');
		$_imglist = $m->where(array('year' => $my_year)) -> select();
		$imglist = array();
		if($_imglist) {
			foreach($_imglist as $k=>$v) {
				//$key = $v['year'].'_'.$v['id'];
				$imglist[$v['year_photo_id']] = $v;
			}
		}
		unset($_imglist);
	
		$this->assign('my_year',$my_year);	
		$this->assign('year_list',$year_list);
		$this->assign('more_url',$more_url[0]['url']);
		$this->assign('imglist',$imglist);
		$this->display();

	}

	//安智年度评选页面_现场照片_查看更多链接编辑
	function ScenePhoto_more() {
		$m = M('year_scenephoto_more');
		$my_year = $_POST['select_year'];
		$bool = $m->where("year={$my_year}")->select();
		if($bool) {		//编辑
			$m->where("year={$my_year}")->save(array('url'=>$_POST['more_url']));
		} else {		//添加
			$m->add(array('year'=>$my_year,'url'=>$_POST['more_url']));
		}

		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/ScenePhoto/select_year/'.$my_year);
		$this->success('恭喜您，链接地址编辑成功！');
	}

	//安智年度评选页面_现场照片_删除
	function ScenePhoto_del() {
		$m = M('year_scenephoto');
		
		if(!$_GET['id']) {
			$this->error("参数错误，请重试！");
		}
		
		$tmp = $m->where("year_photo_id='{$_GET['id']}'")->select();
		if(!$tmp) {
			$this->error('图片不存在，删除失败！');
		}
		$m->query("DELETE FROM sj_year_scenephoto WHERE year_photo_id='{$_GET['id']}'");
		$this->writelog('安智年度评选页面_现场照片_删除删除year_photo_id:'.$_GET['id'],'sj_year_scenephoto',"year_photo_id:{$_GET['id']}",__ACTION__ ,"","del");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/ScenePhoto/select_year/'.$tmp[0]['year']);
		$this->success('恭喜您，图片删除成功！');
	}

	//安智年度评选页面_现场照片_上传
	function ScenePhoto_upload() {
		$failed = FALSE;
		//判断是否有上传成功的文件
		$error = '';
		$year = $_POST['my_year'];
		if($_FILES['ufile']) {
			foreach($_FILES['ufile']['error'] as $k=>$v) {
				if($v==2) {		//图片尺寸大
					$error .= "图片{$k}的大小超过50KB；";
				} else if($v==4) {	//没上传图片
					unset($_FILES['ufile']['name'][$k]);
					unset($_FILES['ufile']['type'][$k]);
					unset($_FILES['ufile']['tmp_name'][$k]);
					unset($_FILES['ufile']['error'][$k]);
					unset($_FILES['ufile']['size'][$k]);
				} else if($v!=0) {	//其它错误
					$error .= "图片{$k}错误代码：{$v}；";
				}
			}
			
			if($error) {
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/ScenePhoto/select_year/'.$year);
				$this->error("$error");
			}
			if(!$_FILES['ufile']['error']) {
				$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/ScenePhoto/select_year/'.$year);
				$this->error("请选择要上传的图片！");
			}

			//遍历上传多张图片
			$m = M('year_scenephoto');

			$files = $_FILES;
			$files_arr = array_keys($files['ufile']['name']);
			foreach($files_arr as $v) {
				
				unset($_FILES);
				$_FILES['ufile']['name'][$v] = $files['ufile']['name'][$v];
				$_FILES['ufile']['type'][$v] = $files['ufile']['type'][$v];
				$_FILES['ufile']['tmp_name'][$v] = $files['ufile']['tmp_name'][$v];
				$_FILES['ufile']['error'][$v] = $files['ufile']['error'][$v];
				$_FILES['ufile']['size'][$v] = $files['ufile']['size'][$v];
				
				$path = date("Ym/d/s").$v;
				$config = array(
					'multi_config' => array(
						'ufile' => array(
							'savepath' => UPLOAD_PATH . '/img/' . $path,
							'saveRule' => 'time',
						)
					),
					//'img_p_size' => 230*230,  //图片常规压缩大小
					'img_p_width'=> 230,
					'img_p_height'=> 230,
				);
				$upload = $this->_uploadapk(0, $config);
				
				if($upload) {
					foreach($upload['image'] as $k=>$v) {
						$tmp = $m->where("year_photo_id='{$v['key']}' and year = {$year}")->select();
						if($tmp) {
							$m->where("year={$year} and year_photo_id = '{$v['key']}'")->save(array('photo'=>$v['url'],'year' => $year,'year_photo_id' => "{$v['key']}"));
							$this->writelog('现场照片编辑了year:'.$year."year_photo_id：{$v['key']}的照片,photo由{$v['photo']}改为{$v['url']}",'sj_year_scenephoto',"year_photo_id:'{$v['key']}',year:{$year}",__ACTION__ ,"","edit");
						} else {
							$res=$m->add(array('photo'=>$v['url'],'year' => $year,'year_photo_id' => "{$v['key']}"));
							$this->writelog('现场照片上传了id:'.$res,'sj_year_scenephoto',$res,__ACTION__ ,"","add");
						}
					}
				}
			}
			
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/ScenePhoto/select_year/'.$year);
			$this->success("恭喜您，照片上传成功！");
		} else {
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/ScenePhoto/select_year/'.$year);
			$this->error('上传参数错误，请重试！');
		}
	}

	//安智年度评选页面_合作媒体链接
	function MediaPartners() {
		$m = M('year_mediapartners');
		$year_list = $m -> table('sl_year_list') -> where(array('status' => 1)) -> order('year DESC') -> select();
		if($_GET['select_year']){
			$my_year = $_GET['select_year'];
		}else{
			$my_year = $year_list[0]['id'];
		}
		$medialist = $m-> where(array('year' => $my_year)) -> select();

		require_once(realpath(dirname(__FILE__).'/../../../../').'/GoPHP/config/config.inc.php');
		if($medialist) {
			foreach($medialist as $k=>$v) {
				//$medialist[$k]['pic'] = self::getImageHost($config).$v['pic'];
				$medialist[$k]['pic'] = IMGATT_HOST.$v['pic'];
				$year_result = $m -> table('sl_year_list') -> where(array('id' => $v['year'])) -> find();
				$medialist[$k]['year'] = $year_result['year'];
			}
		}
		
		
		
		$this->assign('my_year',$my_year);
		$this->assign('year_list',$year_list);
		$this->assign('medialist',$medialist);
		$this->display();
	}

	//获取图片host
	function getImageHost($config)
	{
		$cdn = $config['cdn'];
		$app_name =  'www';
		$conf = $cdn['img_host'][$app_name];
		if (is_array($conf)) {
			$k = array_rand($conf);
			$host = $conf[$k];
		} else {
			$host = $conf;
		}
		return $host;
	}

	//安智年度评选页面_合作媒体链接_添加
	function MediaPartners_add() {
		$m = M('year_mediapartners');
		if($_FILES['pic']['error']==2) {
			$this->error("链接图片大于15KB，请重选图片");
		} else if($_FILES['pic']['error']==4) {
			$this->error("请选择链接图片");
		} else if($_FILES['pic']['error']!=0) {
			$this->error("链接图片错误代码：{$_FILES['pic']['error']}");
		}
		$year = $_POST['the_year'];
		$name = $_POST['name'];
		$link = $_POST['link'];
		$have_been = $m -> where(array('name' => $name,'year' => $year)) -> select();
		$have_link_been = $m -> where(array('link' => $link,'year' => $year)) -> select();
		if($have_been){
			$this -> error("该名称在{$year}年已存在");
		}
		if($have_link_been){
			$this -> error("该链接在{$year}年已存在");
		}
		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array(
				'pic' => array(
					'savepath' => UPLOAD_PATH . '/img/' . $path,
					'saveRule' => 'time',
				)
			),
			//'img_p_size' => 180*70,  //图片常规压缩大小
			'img_p_width'=> 180,
			'img_p_height'=> 70,
		);
		$upload = $this->_uploadapk(0, $config);
		if($upload) {
			
			$ret=$m->add(array('name'=>$_POST['name'],'pic'=>$upload['image'][0]['url'],'link'=>$_POST['link'],'year' => $year));
			$this->writelog('合作媒体链接_编辑添加id:'.$ret,'sj_year_mediapartners',$ret,__ACTION__ ,"","add");
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/MediaPartners/select_year/'.$year);
			$this->success("恭喜您，合作媒体添加成功！");

		} else {
			$this->error("上传链接图片发生错误，请重试！");
		}
	}

	//安智年度评选页面_合作媒体链接_编辑
	function MediaPartners_edit() {
		$m = M('year_mediapartners');
		if($_FILES['pic']['error']==2) {
			$this->error("链接图片大于15KB，请重选图片");
		} else if($_FILES['pic']['error']==4) {
			$this->error("请选择链接图片");
		} else if($_FILES['pic']['error']!=0) {
			$this->error("链接图片错误代码：{$_FILES['pic']['error']}");
		}
		$year = $_POST['the_year'];
		$have_where['_string'] = "link = {$_POST['link']}  and year = {$year} and id != {$_GET['id']}";
		$have_been = $m -> where($have_where) -> select();
		if($have_been){
			$this -> error("该链接在{$year}年已存在");
		}
		$path = date("Ym/d/");
		$config = array(
			'multi_config' => array(
				'pic' => array(
					'savepath' => UPLOAD_PATH . '/img/' . $path,
					'saveRule' => 'time',
				)
			),
			'img_p_size' => 180*70,  //图片常规压缩大小
			//'img_p_width'=> 320, //图片常规压缩宽度
		);
		$upload = $this->_uploadapk(0, $config);
		if($upload) {
			$data=array('name'=>$_POST['name'],'pic'=>$upload['image'][0]['url'],'link'=>$_POST['link'],'year' => $year);
			$log_result = $this->logcheck(array('id'=>$_GET['id']),'sj_year_mediapartners',$data,$m);
			$m->where("id='{$_GET['id']}'")->save($data);
			$this->writelog('合作媒体链接_编辑编辑id:'.$_GET['id'].".{$log_result}",'sj_year_mediapartners',$_GET['id'],__ACTION__ ,"","edit");
			$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/MediaPartners/select_year/'.$year);
			$this->success("恭喜您，编辑合作媒体成功！");

		} else {
			$this->error("上传链接图片发生错误，请重试！");
		}
	}

	//安智年度评选页面_合作媒体链接_删除
	function MediaPartners_del() {
		if(!$_GET['id'] || !is_numeric($_GET['id'])) {
			$this->error("参数错误！");
		}
		
		$m = M('year_mediapartners');
		$have_been = $m -> where(array('id' => $_GET['id'])) -> select();
		$m->query("DELETE FROM sj_year_mediapartners WHERE id='{$_GET['id']}'");
		$this->writelog('合作媒体链接_删除删除了id:'.$_GET['id'],'sj_year_mediapartners',$_GET['id'],__ACTION__ ,"","del");
		$this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Activate/MediaPartners/select_year/'.$have_been[0]['year']);
		$this->success("恭喜您，删除合作媒体成功！");
	}
	
	function year_list(){
		$model = new Model();
		
		$result = $model -> table('sl_year_list') -> where(array('status' => 1)) -> order('year DESC') -> select();
		
		if($_GET['year']){
			$the_year = $_GET['year'];
		}else{
			$the_year = $result[0]['id'];
		}
		$my_result = $model -> table('sl_year_list') -> where(array('id' => $the_year,'status' => 1)) -> select();
		
		$channel_result = $model -> table('sl_year_channel') -> where(array('year_id' => $the_year,'status' => 1)) -> select();
		foreach($channel_result as $key => $val){
			$channel_name_result = $model -> table('sl_channel_list') -> where(array('id' => $val['channel_id'])) -> select();
			$val['channel'] = $channel_name_result[0]['channel'];
			$val['id'] = $channel_name_result[0]['id'];
			$channel_result[$key] = $val;
		}
		$this -> assign('channel_result',$channel_result);
		$this -> assign('the_year',$the_year);
		$this -> assign('year_list',$result);
		$this -> assign('my_result',$my_result);
		$this -> display();
	}
	
	function add_year(){
		$this -> display();
	}
	
	function add_year_do(){
		$model = new Model();
		$year = $_GET['year'];
		$data['year'] = $year;

		$data['status'] = 1;
		$result = $model -> table('sl_year_list') -> add($data);

		if($result){
			$this -> writelog("已添加年份{$year}",'sl_year_list',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sl/Activate/year_list/year/'.$result);
			$this -> success("添加成功");
		}
	}
	
	function del_year(){
		$model = new Model();
		$year = $_GET['year'];
		$data['status'] = 0;
		$result = $model -> table('sl_year_channel') -> where(array('year_id' => $year)) -> save($data);
		$year_result = $model -> table('sl_year_list') -> where(array('id' => $year)) -> save($data);
		if($result || $year_result){
			$this -> writelog("已删除年份id为{$year}",'sl_year_list',$year,__ACTION__ ,"","del");
			$this -> writelog("已删除年份id为{$year}",'sl_year_channel',"year_id:{$year}",__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sl/Activate/year_list');
			$this -> error("删除成功");
		}
	}
	
	function add_channel(){
		$model = new Model();
		$year = $_GET['year'];
		$year_result = $model -> table('sl_year_list') -> where(array('id' => $year)) -> select();

		$this -> assign('year_result',$year_result);
		$this -> display();
	}
	
	function add_channel_do(){
		$model = new Model();
		$year = $_POST['year'];
		$channel = $_POST['channel'];
		foreach($channel as $key => $val){
			if($val){
				$channels[$key] = $val;
			}
		}
		if(!$channels){
			$this -> error("请填写至少一个频道");
		}
		
		foreach($channel as $key => $val){
			if($val){
				$have_channel = $model -> table('sl_channel_list') -> where(array('channel' => $val,'status' => 1)) -> select();
				if($have_channel){
					$have_year_channel = $model -> table('sl_channel_list') -> where(array('year_id' => $year,'channel' => $have_channel[0]['id'])) -> select();
					if($have_year_channel){
						$this -> error("该年份已存在该频道{$val}");
					}
				}
				$my_data['channel'] = $val;
				$my_data['status'] = 1;
				$channel_result = $model -> table('sl_channel_list') -> add($my_data);
				if($channel_result){
					$this -> writelog("创建id为的{$channel_result}的频道",'sl_channel_list',$channel_result,__ACTION__ ,"","add");
				}
				$data['year_id'] = $year;
				$data['channel_id'] = $channel_result;
				$data['status'] = 1;
				$results = $model -> table('sl_year_channel') -> add($data);
				if($results){
					$this -> writelog("已添加年份为{$year}的频道{$val}",'sl_year_channel',$results,__ACTION__ ,"","add");
				}
				$result[] = $results;
			}
		}
		
		if($result){
			$this -> assign('jumpUrl','/index.php/Sl/Activate/year_list/year/'.$year);
			$this -> success("添加成功");
		}
	}
	
	function edit_channel(){
		$model = new Model();
		$id = $_GET['id'];
		$result = $model -> table('sl_channel_list') -> where(array('id' => $id)) -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_channel_do(){
		$model = new Model();
		$id = $_GET['id'];
		$channel = $_GET['channel'];
		$data['channel'] = $channel;
		$data['update_tm'] = time();
		$my_result = $model -> table('sl_channel_list') -> where(array('id' => $id)) -> select();
		$year_result = $model -> table('sl_year_channel') -> where(array('channel_id' => $id)) -> select();
		$have_where['_string'] = "channel = {$channel} and status = 1 and id != {$id}";
		$have_been = $model -> table('sl_channel_list') -> where($have_where) -> select();
		if($have_been){
			$have_year_channel = $model -> table('sl_year_channel') -> where(array('year_id' => $year_result[0]['year_id'],'channel_id' => $have_been[0]['id'])) -> select();
			if($have_year_channel){
				$this -> error("该年份已存在该频道");
			}
		}
		$log_result = $this -> logcheck(array('id' => $id),'sl_channel_list',$data,$model);
		$result = $model -> table('sl_channel_list') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑年份{$year_result[0]['year']}的频道,".$log_result,'sl_channel_list',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sl/Activate/year_list/year/'.$my_result[0]['year_id']);
			$this -> success("编辑成功");
		}
	}
	
	function del_channel(){
		$model = new Model();
		$id = $_GET['id'];
		$data['status'] = 0;
		$need_result = $model -> table('sl_year_channel') -> where(array('channel_id' => $id)) -> select();
		$result = $model -> table('sl_channel_list') -> where(array('id' => $id)) -> save($data);
		$year_channel_result = $model -> table('sl_year_channel') -> where(array('channel_id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已删除年份id为{$need_result[0]['year_id']}的频道id为{$need_result[0]['channel_id']}的频道",'sl_channel_list',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sl/Activate/year_list/year/'.$need_result[0]['year_id']);
			$this -> success("删除成功");
		}
	}
}
?>
