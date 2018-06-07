<?php

/**
 * Desc:   sdk分渠道管理
 */
class SdkChannelAction extends CommonAction {
	public $extend_game_positon = array('1'=>'热门','2'=>'吸金','3'=>'最新');

	//列表页
	public function index(){
		$model = M('');
		if(!empty($_GET['status'])){
			$where['status'] = $_GET['status'];
			$this->assign('status',$_GET['status']);
		}else{
			$where['status'] = array('egt','-1');
		}
		if(!empty($_GET['channel_name'])){
			$where['channel_name'] = array('like','%'.$_GET['channel_name'].'%');
			$this->assign('channel_name',$_GET['channel_name']);
		}
		if(!empty($_GET['id'])){
			$where['id'] = $_GET['id'];
			$this->assign('id',$_GET['id']);
		}
		if(!empty($_GET['channel_code'])){
			$where['channel_code'] = $_GET['channel_code'];
			$this->assign('channel_code',$_GET['channel_code']);
		}
		$total = $model->table('sdk_channel')->where($where)->count();
		//$total = count($channel_list);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 10;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
		
		
		if($_GET['import_out']==1){
			if(!empty($_GET['id_str'])){
				$id = substr($_GET['id_str'],0,-1);
				$where = array('id'=>array('in',$id));
			}
			$where['status'] = 1;
			$import_out_data = $model->table('sdk_channel')->where($where)->order('id desc')->select();
			$import_out_data = $this->get_num_by_channel($import_out_data);
			$this->import_out($import_out_data,1);

		}else{
			$channel_list = $model->table('sdk_channel')->join('sj_admin_users ON sdk_channel.creater = sj_admin_users.admin_user_id')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->field('sdk_channel.*,sj_admin_users.admin_user_name')->select();
			$channel_list = $this->get_num_by_channel($channel_list);
		}
		$this->assign('param', $param);
		$this->assign('page', $Page->show());
		$this->assign('channel_list',$channel_list);
		$this->display();
	}	
	
	
	//获取渠道下游戏数量
	public function get_num_by_channel($data){
		$model = M('');
		foreach($data as $k=>$v){
			if($v['channel_type']==1){
				$table = 'sdk_channel_game_nopack';
			}else{
				$table = 'sdk_channel_game';
			}
			$num = $model->table($table)->where(array('channel_id'=>$v['id']))->count();
			$v['num'] = $num;
			$data[$k] = $v;
		}
		
		return $data;
	}
	//添加渠道或编辑渠道名
	public function add_sdk_channel(){
	    $model = M('');
		if($_POST){			
			$channel_name = $_POST['chl_name'];
			if(empty($channel_name)){
				$this->error('渠道名称不能为空');
			}
			if($_POST['market_type'] == 2){
			    if(isset($_POST['cid'])&&$_POST['cid'] >0){
			        if(!empty($_POST['id'])){
			            $is_c_id = $model->table('sdk_channel')->where( array('market_c_id'=>$_POST['cid'],'id'=>array('exp',"!={$_POST['id']}")))->find();
			        }else{
			            $is_c_id = $model->table('sdk_channel')->where( array('market_c_id'=>$_POST['cid']))->find();
			        }
			        
			        if($is_c_id)  $this->error('关联的市场渠道已被关联');
			    }else{
			        $this->error('请关联市场渠道');
			    }		    
			}
				
			if(!empty($_POST['id'])){
				$where  = array('id'=>$_POST['id']);
				$data['channel_name'] = $channel_name ;
				$data['market_c_id'] = $_POST['cid'] ;
				$data['market_type'] = $_POST['market_type'] ;
				$data['market_c_name'] = $_POST['market_c_name'] ;				
				$http_sta = $model->table('sdk_channel')->where($where)->field('http_sta,channel_code')->find();
				if($http_sta['http_sta']=='2'){
					$this->error('请点击重新发送同步数据后再编辑');	
				}
				$save_name = $model->table('sdk_channel')->where($where)->save($data);
				if($save_name){
					$url = C('updateChannel');
					$http_data = array(
						'cpsName'=>$channel_name,
						'channelId'=>$http_sta['channel_code'],
						'id'=>$_POST['id'],
						'channelMode'=>$_POST['market_type']
					);
					$sdkchannel = D('sendNum.SdkChannel');
					$http_info = $sdkchannel->update_date_by_import($url,$http_data);	
					if(!$http_info){
						$model->table('sdk_channel')->where(array('id'=>$_POST['id']))->save(array('http_sta'=>'3'));	
					}else{
						$model->table('sdk_channel')->where(array('id'=>$_POST['id']))->save(array('http_sta'=>'1'));	
					}
					$this->writelog('游戏联运管理-网游分渠道管理：修改渠道id为'.$_POST['id'].'的渠道名称为'.$channel_name, 'sdk_channel',$_POST['id'],__ACTION__ ,'','edit');
					$this->assign("jumpUrl", "/index.php/Sendnum/SdkChannel/index");
					$this->success('编辑渠道成功');	
				}
			}else{
				$is_has_chl = $model->table('sdk_channel')->where(array('channel_name'=>$channel_name))->find();				
				if($is_has_chl){
					$this->error('该渠道已经存在');	
				}else{
					$code = md5(time() . mt_rand(0,1000));
					$data = array(
						'channel_name' => $_POST['chl_name'],
						'add_tm' => time(),
						'channel_code' => $code,
						'creater'=>$_SESSION['admin']['admin_id']
					);
					$data['market_c_id'] = $_POST['cid'] ;
					$data['market_type'] = $_POST['market_type'] ;
					$data['market_c_name'] = $_POST['market_c_name'] ;
					$data['channel_type'] = $_POST['channel_type'];
					$res = $model->table('sdk_channel')->add($data);
					if($res){						
						$model->table('sdk_channel')->where(array('id'=>$res))->save(array('http_sta'=>'1'));
						$this->writelog('游戏联运管理-网游分渠道管理：添加了渠道id为'.$res.'的渠道', 'sdk_channel',$res,__ACTION__ ,'','add');
						$this->success('添加渠道成功');
					}else{
						$this->error('添加渠道失败');
					}
				}
			}
		}
		
		if(isset($_GET['id'])&&!empty($_GET['id'])){
		    $data = $model->table('sdk_channel')->where(array('id'=>$_GET['id']))->find();
			$this->assign('id',$_GET['id']);
			$this->assign('channel_name',$data['channel_name']);
			$this->assign('cid',$data['market_c_id']);
			$this->assign('market_c_name',$data['market_c_name']);
			$this->assign('market_type',$data['market_type']);
			$this->assign('channel_type',$data['channel_type']);
		}
		$this->display();
	}
	
	
	
	
	//接口更新失败后重新发送请求
	public function re_http(){
		$type = $_GET['type'];
		$id = $_GET['id'];
		$model = M('');
		if($type == '1'){
			//渠道
			$table = 'sdk_channel';
		}else if($type == '2'){
			//渠道游戏
			$table = 'sdk_channel_game';
		}
		$where = array('id'=>$id);
		$res = $model->table($table)->where($where)->find();
		//var_dump($res);
		if($res['http_sta']=='2'){
			if($type == '2'){
				$url = C('addRelation');  
				$channelId = $model->table('sdk_channel')->where(array('id'=>$res['channel_id']))->field('channel_name,channel_code')->find();
				$app_key = $model->table('sj_sdk_info')->where(array('package'=>$res['package']))->field('app_id')->find();
				$data['channelId'] = $channelId['channel_code'];
				$data['cpsName'] = $channelId['channel_name']; //新版增加渠道名称2015.7.23
				//$data['APPKEY'] = $app_key['app_id'];  //新版增加appkey
				$data['appNameChannel'] = $res['channel_softname'];
				$data['version'] = $res['version_code'];
				$data['appName'] = $res['name'];
				$data['pkgName'] = $res['package'];
			}
		}else if($res['http_sta']=='3'){
			if($type == '1'){
				$url = C('updateChannel');
				$data['cpsName'] = $res['channel_name'];
				$data['channelId'] = $res['channel_code'];
			}else if($type == '2'){
				$url = C('updateRelation'); 
				$data['appNameChannel'] = $res['channel_softname'];
				$data['version'] = $res['version_code'];
			}
		}
		$data['id'] = $res['id'];
		$data['status'] = $res['status'];
		//var_dump($data);
		// var_dump($url);exit();
		$sdkchannel = D('sendNum.SdkChannel');
		$http_info = $sdkchannel->update_date_by_import($url,$data);
		if($http_info){
			$model->table($table)->where(array('id'=>$res['id']))->save(array('http_sta'=>'1'));
			$this->success("重新发送成功");			
		}else{
			$this->error("重新发送失败");
		}
	}
	//编辑状态
	public function edit_status(){
		if($_GET['is_batch']){
			$this->batch_edit_status();
			return;
		}
		$status = $_GET['status'];
		$channel_type = $_GET['channel_type'];
		$id = $_GET['id'];
		if($status&&$id){
			$model = M('');
			if($channel_type == 1){
				$table = 'sdk_channel_game_nopack';
			}else{
				if($_GET['type']==1){
					$table = 'sdk_channel';
				}else{
					$table = 'sdk_channel_game';
				}
				$http_sta = $model->table($table)->where(array('id'=>$id))->field('http_sta')->find();
				if($http_sta['http_sta']=='2'){
					$this->error('请点击重新发送同步数据后再编辑');
				}
			}

			$res = $model->table($table)->where(array('id'=>$id))->save(array('status'=>$status));
			if($res){
				if($status == '-1'&&$table=='sdk_channel'){
					$model->table('sdk_channel_game')->where(array('channel_id'=>$id))->save(array('status'=>-1));
				}
				if($channel_type != 1){
					if($_GET['type']==1){
						$url = C('updateChannel');
					}else{
						$url = C('updateRelation');
					}

					$http_data = array(
							'status'=>$status,
							'id'=>$id
					);
					$sdkchannel = D('sendNum.SdkChannel');
					if($table=='sdk_channel'){
						$http_info = $sdkchannel->update_date_by_import($url,$http_data);
						if(!$http_info){
							$model->table($table)->where(array('id'=>$id))->save(array('http_sta'=>'3'));
						}else{
							$model->table($table)->where(array('id'=>$id))->save(array('http_sta'=>'1'));
						}
					}
				}
				$sta = array('1'=>'有效','-1'=>'无效');
				$type = array('1'=>'渠道','2'=>'渠道游戏');
				$this->writelog('游戏联运管理-网游分渠道管理：将id为'.$id.'的'.$type[$_GET['type']].'状态置为'.$sta[$status],$table,$id,__ACTION__ ,'','edit');
				//$this->assign("jumpUrl", "/index.php/Sendnum/SdkChannel/index");
				$this->success('更改状态成功');		
			}
		}
	}
	//批量编辑状态
	function batch_edit_status(){
		$status = $_GET['status'];
		$channel_type = $_GET['channel_type'];
		$ids = $_GET['ids'];
		$ids=explode(',', $ids);
		if($status&&$ids){
			$model = M('');
			$table = 'sdk_channel_game';
			$http_stas = $model->table($table)->where(array('id'=>array('in',$ids)))->field('id,http_sta')->select();
			$id_qualified=array();
			$id_not_qualified=array();
			foreach($http_stas as $v){
				if($v['http_sta']==2){
					$id_not_qualified[]=$v['id'];
				}else{
					$id_qualified[]=$v['id'];
				}
			}
			$res = $model->table($table)->where(array('id'=>array('in',$id_qualified)))->save(array('status'=>$status));
			if($res){
				$sta = array('1'=>'有效','-1'=>'无效');
				$type = array('1'=>'渠道','2'=>'渠道游戏');
				$this->writelog('游戏联运管理-网游分渠道管理：将id为'.implode(',',$id_qualified).'的渠道游戏'.$type[$_GET['type']].'状态置为'.$sta[$status],$table,$id,__ACTION__ ,'','edit');
				if(count($id_not_qualified)){
					$str="id为".implode(',',$id_not_qualified)."请点击重新发送同步数据后再编辑,id为".implode(',',$id_not_qualified)."更改状态成功";
					// echo "<script>alert({$str});location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
					$this->error($str);	
				}else{
					$this->success('更改状态成功');	
				}
					
			}else{
				$str="id为".implode(',',$id_not_qualified)."请点击重新发送同步数据后再编辑";
				$this->error($str);	
			}
		}
	}
	//渠道下游戏详情
	public function channel_game_info(){
		if(isset($_GET['order'])){
			if($_GET['order']=='desc'){
				$order = 'add_tm desc';
				$_GET['order'] = 'asc';
			}else{
				$order = 'add_tm asc';
				$_GET['order'] = 'desc';
			}			
		}else{
			$order = 'id desc';
			$_GET['order'] = 'desc';
			$this->assign('o',1);
		}
		
		if(!empty($_GET['channel_name'])){
			$this->assign('channel_name',$_GET['channel_name']);
		}
		if(!empty($_GET['channel_id'])){
			$this->assign('channel_id',$_GET['channel_id']);
		}
		if(isset($_GET['channel_type'])){
			$this->assign('channel_type',$_GET['channel_type']);
		}

		if($_GET['channel_type']==1){
			$table = 'sdk_channel_game_nopack';
		}else{
			$table = 'sdk_channel_game';
		}
		$model = M('');
		$where = array('channel_id'=>$_GET['channel_id']);
		if(isset($_GET['channel_softname'])&&!empty($_GET['channel_softname'])){
			$where['channel_softname'] = array('like',"%{$_GET['channel_softname']}%");
			$this->assign('channel_softname',$_GET['channel_softname']);
		}
		if(isset($_GET['name'])&&!empty($_GET['name'])){
			$where['name'] = array('like',"%{$_GET['name']}%");
			$this->assign('name',$_GET['name']);
		}
		if(isset($_GET['package'])&&!empty($_GET['package'])){
			$where['package'] = $_GET['package'];
			$this->assign('package',$_GET['package']);
		}
		$total = $model->table($table)->where($where)->count();
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');	
		$softid = array();		
		if($_GET['import_out']==1){
			$where['status'] = 1;
			$import_data = $model->table($table)->where($where)->order('id desc')->select();
			foreach($import_data as $k=>$v){
				$softid[] = $v['softid'];
			}
			$channel_pack = get_table_data(array(
				'softid'=>array('in',$softid),
				'status'=>1,
				'channel_id'=>$_GET['channel_id']
			),'sdk_channel_game_sdk','package','package,sdk_status');		
			foreach($import_data as $k=>$v){
				$import_data[$k]['sdk_status'] = $channel_pack[$v['package']]['sdk_status'];
			}
			$this->import_out($import_data,2,$_GET['channel_name']);
			
		}else{
			$game_list = $model->table($table)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order($order)->select();
			foreach($game_list as $k=>$v){
				$softid[] = $v['softid'];
			}
			$channel_pack = get_table_data(array(
				'softid'=>array('in',$softid),
				'status'=>1,
				'channel_id'=>$_GET['channel_id']
			),'sdk_channel_game_sdk','package','id,package,sdk_status,need_test,sdk_send');
			foreach($game_list as $k=>$v){
				$game_list[$k]['sdk_status'] = $channel_pack[$v['package']]['sdk_status'];
				$game_list[$k]['need_test'] = $channel_pack[$v['package']]['need_test'];
				$game_list[$k]['sdk_id'] = $channel_pack[$v['package']]['id'];
				$game_list[$k]['sdk_send'] = $channel_pack[$v['package']]['sdk_send'];
				$game_list[$k]['fixed_url']="dl_game.php?package=".$v['package']."&gcid=".$v['channel_id'];
			}
		}
		if($_SERVER['SERVER_NAME']=='518test.anzhi.com'){
			$server_name="http://m.test.anzhi.com/";
		}else{
			$server_name="http://m.anzhi.com/";
		}
		$this->assign('param',$param);
		$this->assign('game_list',$game_list);
		$this->assign('count_games',count($game_list));
		$this->assign('p', $_GET['p']);
		$this->assign('lr', $_GET['lr']);
		$this->assign('page', $Page->show());
		$this->assign('server_name',$server_name);
		$this->assign('IMGATT_HOST',IMGATT_HOST);
		$this->display();
	}
	
	//关联游戏
	public function related_game(){
		$this->assign('channel_name',$_GET['channel_name']);
		$this->assign('channel_id',$_GET['channel_id']);
		$channel_type = isset($_GET['channel_type'])?$_GET['channel_type']:0;
		$this->assign('channel_type',$channel_type);
		if(isset($_GET['need_test'])&&$_GET['need_test']==1){
			$this->assign('need_test',$_GET['need_test']);
		}
		//为推广产品管理新增过来的
		if(isset($_GET['from']))  $this->assign('from',$_GET['from']);
		//运营位位置
		if(isset($_GET['position'])) $this->assign('position',$_GET['position']);
		//var_dump($_GET);
		$this->assign('p', $_GET['p']);
		$this->assign('lr', $_GET['lr']);
		$this->display();	
	}
	
	//关联游戏判断是否testin审核状态
	public function check_related_game(){
		$model = M('');
		if(!empty($_POST['game'])){
			$game_id = explode(',',substr($_POST['game'],0,-1));
			$where = array(
				'package'=>array('in',$game_id),
				'status'=>1
			);
			$testin_info = get_table_data($where,'sdk_channel_game_sdk','package','softid,softname,package,sdk_status');
			if(count($testin_info)>0){
				$reject_softname_arr = $pass_package_arr = array();
				$result['code'] = 0;
				foreach($testin_info as $k=>$v){
					//testin表中没有的软件加入要
					if($v['sdk_status']==3){
						//未通过的软件
						$reject_softname_arr[] = $v['softname']; 
						$result['code'] = 1;
					}else if($v['sdk_status']==2){
						//通过的软件
						$pass_package_arr[] = $v['package'];
					}
				}
				if($result['code']==1){
					$reject_softname = implode(',',$reject_softname_arr);
					$result['reject_softname'] = $reject_softname;
					$result['pass_package'] = '';
					foreach($pass_package_arr as $k=>$v){
						$result['pass_package'] .= $v.',';
					}
				}
			}else{
				$result['code'] = 0;
			}
			echo json_encode($result);
		}
	}

	//保存融合渠道游戏
	function  save_nopack_game($soft_info,$package){
		if(count($soft_info)==1) {
			$nopack_info = get_table_data(array('package' => array('in', $package), 'channel_id' => $_GET['channel_id']), 'sdk_channel_game_nopack', 'package', 'package,version_code_num,version_code');
			if($nopack_info){
				if($soft_info['package']==$nopack_info['package']&&$soft_info['version_code']==$nopack_info['version_code_num']){
					$this->error('已存在同版本的相同游戏');
				}
			}
		}
		$sdkchannel = D('sendNum.SdkChannel');
		$res = $sdkchannel->insert_sdk_game_nopack($soft_info,$_GET['channel_id']);
		if($res){
			$ids = implode(',',$package);
			$this->writelog('游戏联运管理-网游分渠道管理：关联了渠道id为'.$_GET['channel_id'].'的游戏,id为'.$ids,'sdk_channel_game_nopack',$ids,__ACTION__ ,'','add');
			$this->success('关联成功');
		}else{
			$this->error('无关联成功的游戏');
		}
		exit();
	}
	//保存渠道关联游戏
	//appNameChannel
	public function save_related_game(){
		$model = M('');
		if(!empty($_GET['game'])){
			$game_id = explode(',',substr($_GET['game'],0,-1));
			$where = array(
				'package'=>array('in',$game_id)
			);
			$game = $model->table('sj_soft_whitelist')->where($where)->field('softname,package')->select();
			$package = array();
			foreach($game as $key=>$val){
				$package[] = $val['package'];
			}
			$soft_info = get_table_data(array('package'=>array('in',$package),'hide'=>1,'status'=>1),'sj_soft','package','softid,softname,package,version,version_code');
			$channel_type = $_GET['channel_type'];
			if($channel_type == 1){
				$this->save_nopack_game($soft_info,$package);
			}
			if(count($soft_info)==1){
				$sdk_info = get_table_data(array('package'=>array('in',$package),'channel_id'=>$_GET['channel_id']),'sdk_channel_game','package','package,version_code_num,version_code');
				if($sdk_info){
					if($soft_info['package']==$sdk_info['package']&&$soft_info['version_code']==$sdk_info['version_code_num']){
						$this->error('已存在同版本的相同游戏');
					}
				}
				
			}											
			$sdkchannel = D('sendNum.SdkChannel');
			$need_test = isset($_GET['need_test'])?$_GET['need_test']:0;
			$sdkchannel->save_channel_game_sdk($soft_info,'','',array($_GET['channel_id']),$need_test);
			if($game){
				foreach($game as $k=>$v){
					
					$data = array(
						'softid'=>$soft_info[$v['package']]['softid'],
						'name'=>$v['softname'],				
						'channel_softname'=>$v['softname'],
						'package'=>$v['package'],
						'version_code_num'=>$soft_info[$v['package']]['version_code'],
						'version_code'=>$soft_info[$v['package']]['version'],
						'channel_id'=>$_GET['channel_id'],
						'add_tm'=>time(),
						'update_tm'=>time(),
						'creater'=>$_SESSION['admin']['admin_id']
					);
					// $sdk_status = $model->table('sdk_channel_game_sdk')->where(array('softid'=>$soft_info[$v['package']]['softid']))->field('sdk_status')->find();
					// if($sdk_status&&$sdk_status['sdk_status']==1){
						// $data['apk_status'] = 4;
					// }
					$res = $model->table('sdk_channel_game')->add($data);
					if($res){

						$channelId = $model->table('sdk_channel')->where(array('id'=>$_GET['channel_id']))->field('channel_name,channel_code')->find();
						$app_key = $model->table('sj_sdk_info')->where(array('package'=>$v['package']))->field('app_id')->find();
						$url = C('addRelation');

						$http_data = array(
							'status'=>1,
							'id'=>$res,
							'channelId'=>$channelId['channel_code'],
							'version'=>$soft_info[$v['package']]['version'],
							'appName'=>$v['softname'],
							'channelName'=>$channelId['channel_name'], //新版增加渠道名称 2015.7.23
							//'APPKEY'=>$app_key['app_id'], 新版新增appkey
							'appNameChannel' => $v['softname'],
							'pkgName'=>$v['package']
						);
						$sdkchannel = D('sendNum.SdkChannel');
						$http_info = $sdkchannel->update_date_by_import($url,$http_data);


						if(!$http_info){
							$model->table('sdk_channel_game')->where(array('id'=>$res))->save(array('http_sta'=>'2'));
						}else{
							$model->table('sdk_channel_game')->where(array('id'=>$res))->save(array('http_sta'=>'1'));
						}
						$this->writelog('游戏联运管理-网游分渠道管理：关联了渠道id为'.$_GET['channel_id'].'的游戏,id为'.$res.',包名为'.$v['package'].',软件名称为'.$v['softname'],'sdk_channel_game',$res,__ACTION__ ,'','edit');
					}
				}	
				$this->assign("jumpUrl", "/index.php/Sendnum/SdkChannel/channel_game_info/channel_id/{$_GET['channel_id']}/channel_name/{$_GET['channel_name']}/p/{$_GET['p']}/lr/{$_GET['lr']}");
				$this->success('关联成功');
			}else{
				$this->error('未找到要关联的游戏');
			}
		}else{
			$this->error('未选择要关联的游戏');
		}
	}

	//老流程搜索游戏更改成在推广列表中搜索
	public function search_game_new(){
		if(!empty($_POST['game_name'])||!empty($_POST['package'])){
			$sdkchannel = D('sendNum.SdkChannel');
			$where = "status = 1 and extend_sta = 2";
			if(!empty($_POST['game_name'])){
				$where .= ' and softname like "%'.$_POST['game_name'].'%"';
			}
			if(!empty($_POST['package'])){
				$where .= ' and package = "'.$_POST['package'].'"';
			}
			$game = $sdkchannel->get_extend_game($where,"softname,package");
			echo json_encode($game);
		}
	}

	//搜索在线网游
	public function search_game(){
		$model = M('');
		if(!empty($_POST['game_name'])||!empty($_POST['package'])){		
			$table = 'sj_soft_whitelist';
			$where = '(b.fen_lei in ("网游","单机","棋牌") or a.p_fenlei in ("网游","单机","棋牌")) and (a.del=0 or a.del IS NULL ) and b.status = 1';
			if(!empty($_POST['game_name'])){
				$where .= ' and b.softname like "%'.$_POST['game_name'].'%"';
			}
			if(!empty($_POST['package'])){
				$where .= ' and b.package = "'.$_POST['package'].'"';
			}
			$sql = 'select b.softname,b.package,b.fen_lei,a.p_fenlei from sj_soft_whitelist b left join yx_product a on b.package = a.package where '.$where;

			$game = $model->query($sql);
			foreach($game as $key=>$val){
				$this_online = $model->table('sj_soft')->where(array('package'=>$val['package'],'status'=>1,'hide'=>1))->field('softid,package')->order('softid desc')->find();
				if(!$this_online){
					unset($game[$key]);
				}else{
					$sdk_version = $model->table('sj_soft_tmp')->where(array('softid'=>$this_online['softid'],'sdk_version'=>array('exp',"!=''")))->field('sdk_version')->order('id desc')->find();
					if(!$sdk_version||empty($sdk_version['sdk_version'])){
						unset($game[$key]);
					}else{
						//	判断sdk版本是否为3.2以上
						$ver = explode('.',$sdk_version['sdk_version']);
						if($val['fen_lei']=='单机'||$val['p_fenlei']=='单机'){
							//单机3.7及以上
							$check_v = 7;
						}else{
							$check_v = 2;
						}
						if(empty($sdk_version['sdk_version'])||$ver[0]< 3||($ver[0]==3&&$ver[1] < $check_v)){
							unset($game[$key]);
						}
					}
				}
				//$game[$key] = $val;
			}
			$game = array_values($game);
			echo json_encode($game);
		}
	}
	//导出渠道或游戏
	public function import_out($data,$type,$title=''){
		include (dirname(__FILE__).'/../../ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
		->setLastModifiedBy("Maarten Balliauw")
		->setTitle("Office 2007 XLSX Test Document")
		->setSubject("Office 2007 XLSX Test Document")
		->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
		->setKeywords("office 2007 openxml php")
		->setCategory("Test result file");
		$date = date("Y-m-d H:i:s");
		if($type == 1){
			//渠道
			$title = '渠道列表';
		}
		$excel_title = $title.'_'.$date;
		if($type==1){
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', '渠道ID')
			->setCellValue('B1', '渠道名称')
			->setCellValue('C1', '游戏数量')
			->setCellValue('D1', '创建日期')
			->setCellValue('E1', '渠道编号');
		}else{
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', '游戏名称(安智)')
			->setCellValue('B1', '游戏名称(渠道)')
			->setCellValue('C1', '包名')
			->setCellValue('D1', '版本号/版本名')
			->setCellValue('E1', '渠道包')
			->setCellValue('F1', '创建时间')
			->setCellValue('G1', '状态');
		}
		
		//填充数据
		foreach($data as $key=>$val){
			$n = $key+2;
			if($type == 1){
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$n,$val['id'])
				->setCellValue('B'.$n,$val['channel_name'])
				->setCellValue('C'.$n,$val['num'])
				->setCellValue('D'.$n,date('Y-m-d H:i:s',$val['add_tm']))
				->setCellValue('E'.$n,$val['channel_code']);	
			}else{
				$pack_sta = $st = '';
				if(!empty($val['sdk_status'])){
					if($val['sdk_status']==2||$val['sdk_status']==4){
						$pack_sta='Testin_测试中';
					}else if($val['sdk_status']==3){
						$pack_sta='Testin_测试_未通过';
					}else if($val['sdk_status']==1){
						if($val['apk_status']==1){
							$pack_sta='生成中';
						}else if($val['apk_status']==2){
							$pack_sta='失败';
						}else if($val['apk_status']==3){
							$pack_sta='成功';
						}else if($val['apk_status']==4){
							$pack_sta='待打包';
						}
					}
				}
				
				if($val['status']=='1'){$st = '有效';}else{$st = '无效';}
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$n,$val['name'])
				->setCellValue('B'.$n,$val['channel_softname'])
				->setCellValue('C'.$n,$val['package'])
				->setCellValue('D'.$n,$val['version_code_num'].'/'.$val['version_code'])
				->setCellValue('E'.$n,$pack_sta)
				->setCellValue('F'.$n,date('Y-m-d H:i:s',$val['add_tm']))
				->setCellValue('G'.$n,$st);
			}
			
			//设置居中
			$objPHPExcel->getActiveSheet()->getStyle('A'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('C'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('D'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('E'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('F'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('G'.$n)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		for($i='A';$i<='G';$i++){
			//设置居中
			$objPHPExcel->getActiveSheet()->getStyle($i.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		//设置宽度
		
		if($type==1){
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
		}else{
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(35);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		}
		
		header ( 'Content-Type: application/vnd.ms-excel;charset=utf-8' );
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
		} else if (preg_match("/Firefox/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
		} else {  
			header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
		}	
		
		header('Cache-Control: max-age=0');
		
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
	
	//游戏签名管理
	public function sdk_sign(){
		$sdkchannel = D('sendNum.SdkChannel');
		$list = $sdkchannel->get_sdk_sign_list();
		$this->assign('soft_name',$_GET['soft_name']);
		$this->assign('package',$_GET['package']);
		$this->assign('list',$list[0]);
		$this->assign('page',$list[1]);
		$this->display();
	
	}

	//添加签名游戏
	public function sign_game_list(){
		if($_POST){
			$sdkchannel = D('sendNum.SdkChannel');
			$game = $sdkchannel->search_sign_game();
			echo json_encode($game);
			exit;
		}
		$this->display();
	}
	
	//保存签名游戏
	public function save_game_sign(){
		$model = M('');
		if(!empty($_GET['game'])){
			$game_id = explode(',',substr($_GET['game'],0,-1));
			$sign_where = array('package'=>array('in',$game_id),'status'=>1);
			$sign_game = $model->table('sdk_channel_game_sign')->where($sign_where)->field('package')->select();
			if($sign_game){
				foreach($sign_game as $k=>$v){
					if(in_array($v['package'],$game_id)){
						$key = array_search($v['package'],$game_id);
						unset($game_id[$key]);
					}
				}
			}
			
			$game = $model->table('sdk_channel_game')->where($sign_where)->group('package')->field('name,package')->select();
			if($game){
				foreach($game as $k=>$v){
					$data = array(
						'softname'=>$v['name'],
						'package'=>$v['package'],
						'add_tm'=>time()
					);
					$res = $model->table('sdk_channel_game_sign')->add($data);
					if($res){
						$this->writelog('手游渠道推广-游戏签名管理：添加了id为'.$res.'的游戏,包名为'.$v['package'].',软件名称为'.$v['name'],'sdk_channel_game_sign',$res,__ACTION__ ,'','add');
					}
				}
			}
			
			$this->success('添加成功');
		}else{
			$this->error('未选择要添加的游戏');
		}
	}
	
	//删除签名游戏
	public function del_sign_game(){
		$id = $_GET['id'];
		if(!empty($id)){
			$model = M('');
			$res = $model->table('sdk_channel_game_sign')->where('id = "'.$id.'"')->save(array('status'=>0,'up_tm'=>time()));
			if($res){
				$this->writelog('手游渠道推广-游戏签名管理：删除了id为'.$id.'的游戏','sdk_channel_game_sign',$id,__ACTION__ ,'','del');
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('请选择要删除的游戏');
		}
	}
	
	//编辑签名游戏
	public function edit_sign_game(){
		if($_POST){
			global $conf;
			$path = UPLOAD_PATH .C('sdk_game_sign_url').$_POST['id'].'/';
			list($msec, $sec) = explode(' ', microtime());
            $msec = substr($msec, 2);
            $this->mkDirs($path);
			if(!empty($_FILES['fileField']['name'])){
				$size = $_FILES['fileField']['size'];
				if($size > 2097152){ //2M
					$this->error('请上传小于2M的签名文件');	
				}
				$ytype = $_FILES['fileField']['name'];
				$info = pathinfo($ytype);
				$type = $info['extension']; //获取文件件扩展名
				if($type){
					$sign_path = $path . 'sign_' . $msec . '.' . $type;
					$sign_name = 'sign_' .$msec . '.' . $type;
				}else{
					$sign_path = $path . 'sign_' . $msec ;
					$sign_name = 'sign_' .$msec ;
				}
				if (move_uploaded_file($_FILES['fileField']['tmp_name'], $sign_path)){
					$cmd = C('sign_analyze').$_POST['sign_pwd']." -keystore ".$sign_path."  -v|grep Alias";
					$alias_name = shell_exec($cmd);
					if(preg_match('/Alias name:(.*)/',$alias_name,$matches)){
						$alias_name = trim($matches[1]);
					}
					 $test_path = str_replace(UPLOAD_PATH, '', $sign_path);
				}else{
					$this->error('上传签名失败');	
				};
			}
            
			// if(!empty($_FILES['file_pwd']['name'])){
				// $pwd_size = $_FILES['file_pwd']['size'];
				// if($pwd_size > 2097152){ //2M
					// $this->error('请上传小于2M的签名密码文件');	
				// }
				// $pwd_info = pathinfo($_FILES['file_pwd']['name']);
				// $pwd_type = $pwd_info['extension']; //获取文件件扩展名
				// if($pwd_type){
					// $sign_pwd_path = $path . 'signpwd_' . $msec . '.' . $pwd_type;
					// $sign_pwd_name = 'signpwd_' .$msec . '.' . $pwd_type;
				// }else{
					// $sign_pwd_path = $path . 'signpwd_' . $msec;
					// $sign_pwd_name = 'signpwd_' .$msec;
				// }
				
				// if (move_uploaded_file($_FILES['file_pwd']['tmp_name'], $sign_pwd_path)){
					 // $pwd_path = str_replace(UPLOAD_PATH, '', $sign_pwd_path);
				// }else{
					// $this->error('上传签名密码失败');	
				// };
			// }
			$model = M('');
			$data = array();
			if(isset($sign_name)){
				$data['sign_name'] = $sign_name;
				$data['sign_url'] = $test_path;
				$data['alias_name'] = $alias_name;
			}
			// if(isset($sign_pwd_name)){
				// $data['sign_pwd_name'] = $sign_pwd_name;
				// $data['sign_pwd'] = $pwd_path;
			// }
			if(empty($_POST['sign_pwd'])){
				$this->error('签名密码不能为空');
			}
			$data['sign_pwd'] = $_POST['sign_pwd'];
			$res = $model->table('sdk_channel_game_sign')->where('id = "'.$_POST['id'].'"')->save($data);
			if($res){
				$this->writelog('手游渠道推广-游戏签名管理：编辑了id为'.$_POST['id'].'的游戏','sdk_channel_game_sign',$_POST['id'],__ACTION__ ,'','edit');
				$this->success('编辑成功');
			}
		}else{
			$id = $_GET['id'];
			if(!empty($id)){
				$model = M('');
				$sign_info = $model->table('sdk_channel_game_sign')->where(array('id'=>$id))->field('sign_name,sign_pwd')->find();
				$this->assign('sign_info',$sign_info);
				$this->assign('id',$id);
				$this->display();
			}else{
				$this->error('请选择要编辑的游戏');
			}
		}
		
	}
	
	//渠道合作管理
	public function cooperation_channel(){
		$model = M('');
		if(isset($_GET['sdk_status'])){
			$sdk_status = $_GET['sdk_status'];
		}else{
			$sdk_status = 2;
		}
		$where = array(
			'a.sdk_status' =>$sdk_status,
			'a.status'=>1
		);
		
		if($_GET['sdk_status']==2||!$_GET['sdk_status']){
			$where['a.sdk_status'] = array('in',array('2','4'));
		}		
		if(!empty($_GET['softname'])){
			$this->assign('softname',$_GET['softname']);
			$where['a.softname'] = array('like','%'.$_GET['softname'].'%');
		}
		if(!empty($_GET['package'])){
			$this->assign('package',$_GET['package']);
			$where['a.package'] = $_GET['package'];
		}
		if(!empty($_GET['begintime'])){
			$this->assign('begintime',$_GET['begintime']);
			$where['a.create_tm'][] = array('GT',strtotime($_GET['begintime']));
		}
		if(!empty($_GET['endtime'])){
			$this->assign('endtime',$_GET['endtime']);
			$where['a.create_tm'][] = array('LT',strtotime($_GET['endtime']));
		}
		
		if(!empty($_GET['channel_name'])){
			$where['d.channel_name'] = array('like','%'.$_GET['channel_name'].'%');
			$this->assign('channel_name',$_GET['channel_name']);
			$total = $model->table('sdk_channel_game_sdk as a')->join('sdk_channel d ON d.id = a.channel_id')->where($where)->count();
		}else{
			$total = $model->table('sdk_channel_game_sdk as a')->where($where)->count();
		}
		
		//echo $model->getLastSql();
		// var_dump($total);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 10;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
		if(!empty($_GET['channel_name'])){
			$res = $model->table('sdk_channel_game_sdk a')->join('sdk_channel d ON d.id = a.channel_id')->join('sj_admin_users c ON a.reviewer = c.admin_user_id')->join('sdk_channel b on b.id = a.channel_id')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('a.rank asc,update_tm desc')->field('a.*,b.channel_name,c.admin_user_name')->select();
		}else{
			$res = $model->table('sdk_channel_game_sdk a')->join('sj_admin_users c ON a.reviewer = c.admin_user_id')->join('sdk_channel b on b.id = a.channel_id')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('a.rank asc,update_tm desc')->field('a.*,b.channel_name,c.admin_user_name')->select();
		}
		
		// echo $model->getLastSql();
		$this->assign('param',$param);
		$this->assign('res',$res);
		$this->assign('page',$Page->show());
		$this->assign('sdk_status',$sdk_status);
		$this->display();
	}
	
	/**
	 * +----------------------------------------------------------
	 * 修改排序值
	 * +----------------------------------------------------------
	 */
	function edit_rank(){
		$model = M('');		
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		if(isset($_GET['type'])&&$_GET['type']=='channel'){
			//渠道修改排序
			$table = 'sdk_channel';
			$log = "手游渠道推广：修改了id为{$id}的游戏排序值为{$rank}";
		}else{
			$table = 'sdk_channel_game_sdk';
			$log = "手游渠道推广：修改了id为{$id}的渠道优先级为{$rank}";
		}
		$result = $model -> table($table) -> where(array('id' => $id)) -> save(array('rank' => $rank));
		if($result){
			if(isset($_GET['type'])&&$_GET['type']=='channel') {
				update_testin_data(array('id' => $id, 'rank' => $rank, 'type' => 'set'));
			}
			$this -> writelog($log,$table,$id,__ACTION__ ,'','edit');
			echo 1;
		}
	}
	/* 点击重试
	*  @desc type(1重新打包 2重新testin测试 3通过testin测试 4批量通过testin测试)
	*/
	function re_apk_status(){
		$type = $_GET['type'];		
		if(empty($type)){
			$this->error('参数未知');
		}
		$model = M('');		
		$softid = $_GET['softid'];
		if(empty($softid)) $this->error('参数未知');
		if($type==1){
			$id = $_GET['id'];
			if(empty($id)) $this->error('参数未知');
			$res = $model->table('sdk_channel_game')->where('id = '.$id)->save(array('apk_status'=>3,'update_tm'=>time()));
			$this -> writelog("渠道游戏测试：重新提交软件id为{$id}的包", 'sdk_channel_game_sdk',$id,__ACTION__ ,'','edit');
			$msg = '重试成功';
			$error_msg = '重试失败';
		}else if($type==2){
			$res = $model->table('sdk_channel_game_sdk')->where('softid = '.$softid)->save(array('sdk_status'=>2,'sdk_send'=>0,'need_test'=>1,'update_tm'=>time()));
			$this -> writelog("渠道游戏测试：重新提交软件id为{$softid}的包", 'sdk_channel_game_sdk',$softid,__ACTION__ ,'','edit');
			$msg = '重试成功';
			$error_msg = '重试失败';
		}else if($type==3||$type==4){
			$where = 'id = '.$softid;
			if($type == 4){
				$id = substr($softid,0,-1);
				$where = array('id'=>array('in',$id));
			}else{
				$id = $softid;
			}


			update_testin_data(array('id'=>$id,'type'=>'del'));

			$res = $model->table('sdk_channel_game_sdk')->where($where)->save(array('sdk_status'=>1,'reviewer'=>$_SESSION['admin']['admin_id'],'review_tm'=>time(),'update_tm'=>time()));
			if($res){
				$game_info = $model->table('sdk_channel_game_sdk')->where($where)->field('id,softid,channel_id,package,version_code,rank,softname,game_type,sdk_version,version,record_type,url')->select();
				$package = array();
				//自增长包
				$task_client = get_task_client();
				foreach($game_info as $k=>$v){
					$package[] = $v['package'];
					//update_testin_data(array('id'=>$v['id'],'rank'=>$v['rank']));
					$task_data = array();
					$task_data['softid'] = $v['softid'];
					$task_data['package'] = $v['package'];
					$task_data['channel_id'] = $v['channel_id'];
					$task_data['version_code_num'] = $v['version_code'];

					if($v['channel_id']== C('general_channel_id')){
						$task_data['is_general_channel'] = 1;
					}else{
						$task_data['is_general_channel'] = 0;
					}
					$task_client->doBackground('incremental_update_channel_sdk', json_encode($task_data));
					$task_data1 = array(
							'softid'=>$v['softid'],
							'softname' => $v['softname'],
							'channel_id'=>$v['channel_id'],
							'package'=>$v['package'],
							'game_type'=>$v['game_type'],
							'sdk_version'=>$v['sdk_version'],
							'version'=>$v['version'],
							'version_code'=>$v['version_code'],
							'record_type'=>$v['record_type'],
							'url'=>$v['url']
					);
					$task_client->doBackground('update_other_channel_data', json_encode($task_data1));
					$game_where = array(
						'softid'=>$v['softid'],
						'channel_id'=>$v['channel_id'],
						'package'=>$v['package'],
						'version_code_num'=>$v['version_code'],
					);
					$model->table('sdk_channel_game')->where($game_where)->save(array('apk_status'=>3,'update_tm'=>time()));
					$this->writelog('手游渠道推广-合作渠道管理：通过了id为'.$v['id'].',softid为'.$v['softid'].',渠道id为：'.$v['channel_id'].',包名为'.$v['package'].',版本号为'.$v['version_code'].'的游戏','sdk_channel_game',$v['id'],__ACTION__ ,'','edit');
				}
				//通过后将推广产品设置成推广中
				$sdkchannel = D('sendNum.SdkChannel');
				$sdkchannel->update_extend_status($package);
			}
			
			$msg = '通过成功';
			$error_msg = '通过失败';
		}
		if($res){
			$this->success($msg);
		}else{
			$this->error($error_msg);
		}
	}
	
	//编辑游戏渠道名称
	function edit_channel_softname(){
		if($_POST){
			$model = M('');
			if(empty($_POST['c_softname'])){
				$this->error("游戏渠道名称不能为空");
			}
			$channel_type = $_POST['channel_type'];
			if($channel_type==1){
				$table = 'sdk_channel_game_nopack';
			}else{
				$table = 'sdk_channel_game';
				$http_sta = $model->table($table)->where($where)->field('http_sta')->find();
				if($http_sta['http_sta']=='2'){
					$this->error('请点击重新发送同步数据后再编辑');
				}
			}

			$res = $model->table($table)->where('id = '.$_POST['id'])->save(array('channel_softname'=>$_POST['c_softname'],'update_tm'=>time()));
			if($res){
				if($channel_type !=1){
					$url = C('updateRelation');
					$http_data = array(
							'appNameChannel'=>$_POST['c_softname'],
							'id'=>$_POST['id']
					);
					$sdkchannel = D('sendNum.SdkChannel');
					$http_info = $sdkchannel->update_date_by_import($url,$http_data);
					if(!$http_info){
						$model->table($table)->where(array('id'=>$id))->save(array('http_sta'=>'3'));
					}else{
						$model->table($table)->where(array('id'=>$id))->save(array('http_sta'=>'1'));
					}
				}
				$this->writelog('手游渠道推广-渠道游戏管理：修改了id为'.$_POST['id'].'的游戏的渠道游戏名称为'.$_POST['channel_softname'],$table,$_POST['id'],__ACTION__ ,'','edit');
				$this->success("编辑成功");
			}else{
				$this->error("编辑失败");
			}
		}
		$id = $_GET['id'];
		$channel_softname = $_GET['channel_softname'];
		$channel_type = isset($_GET['channel_type'])?$_GET['channel_type']:0;
		$this->assign('channel_type',$channel_type);
		$this->assign('id',$id);           
		$this->assign('channel_softname',$channel_softname);
		$this->display();
	}
	
	
	//获取渠道游戏历史版本
	function get_game_old_version(){
		$channel_id = $_GET['channel_id'];
		$package = $_GET['package'];
		$model = M('');
		if($_GET['from']=='extend'){
			$old_version = $model->table('sdk_channel_game_bak a')->join('sdk_channel_game_sdk b on a.package=b.package and a.channel_id=b.channel_id and a.version_code_num =b.version_code')->where(array('a.package'=>$package,'a.channel_id'=>$channel_id,'a.version_code_num'=>array('neq',$_GET['version_code_num'])))->field('a.version_code_num,a.version_code,a.url,b.sdk_version,b.review_tm')->select();
		}else{
			$old_version = $model->table('sdk_channel_game_bak')->where(array('package'=>$package,'channel_id'=>$channel_id,'version_code_num'=>array('neq',$_GET['version_code_num'])))->field('version_code_num,version_code,url')->select();
		}
		//echo $model->getLastSql();
		$this->assign('imghost',IMGATT_HOST);
		$this->assign('old_version',$old_version);
		if($_GET['from']=='extend'){
			$this->display("get_extend_old_version");
		}else{
			$this->display();
		}
	}
	
	//批量导入游戏
	function import_game(){
		//导入全部
		if(isset($_POST['all'])&&$_POST['all']==1&&!empty($_POST['channel_id'])){
			$this->related_all_game($_POST['channel_id'],$_POST['need_test'],$_POST['channel_type']);
		}else if(isset($_POST['all'])&&$_POST['all']==2&&!empty($_POST['channel_id'])){
			$this->import_by_csv();
		}
		if(isset($_GET['need_test'])&&$_GET['need_test']==1){
			$this->assign('need_test',$_GET['need_test']);
		}
		$this->assign('channel_type',$_GET['channel_type']);
		$this->assign('channel_id',$_GET['channel_id']);
		$this->assign('channel_name',$_GET['channel_name']);
		$this->display();
	}
	//导出失败明细
    public function out_fail_soft(){
            $allist = json_decode($_POST['fail_soft'],true);
            $this->down_csv($allist);
    }
    
    //下载csv
    function down_csv($data){
        header("Content-type:application/vnd.ms-excel");
		$title = "渠道名_导入失败软件_".date("Y-m-d");
        header("content-Disposition:filename={$title}.csv");
        $desc = "包名,错误信息\r\n";
        foreach ($data as $v) {
            $start_tm = date('Y/m/d',$v['start_tm']);
            $end_tm = date('Y/m/d',$v['end_tm']);
            $desc = $desc . $v['package'] . ',' . $v['error_msg'] . "\r";
        }
        $desc = iconv('utf-8', 'gbk', $desc);
        echo $desc;
        exit(0);
    }
	//csv导入游戏
	function import_by_csv(){
		$tmp_name = $_FILES['upload']['tmp_name'];
		$tmp_houzhui = $_FILES['upload']['name'];
		$tmp_arr = explode('.', $tmp_houzhui);
		$houzhui = array_pop($tmp_arr);
		if (strtoupper($houzhui) != 'CSV') {
			echo 2;
			exit(0);
		}
		$arr = $this->read_csv($tmp_name);

		if ($arr === false) {
			echo 2;
			exit(0);
		}
		$this -> writelog("渠道游戏管理:批量导入了渠道ID为{$_POST['channel_id']}的游戏", 'sdk_channel_game',$_POST['channel_id'],__ACTION__ ,'','add');
		$this->ajaxReturn($arr, '导入成功！', 1);
		
	}
	//读取excel数据
    function read_csv($file) {
        $arr = array();
        $title = array();
        $handel = fopen($file, "r");
        $i = 0;
        while (($num_arr = $this->mygetcsv($handel, 1000, ",")) !== FALSE) {
            //标题行不写入 
            if ($i != 0) {
                $arr[$i] = $num_arr;
            } else {
                $title[$i] = $num_arr;
            }
            $i++;
        }
        if (strlen($title[0][0]) != 4) {
            return false;
        }
        $rs = $this->import_add($arr);
        fclose($handel);
        return $rs;
    }
	
	function import_add($arr){
		foreach($arr as $k=>$v){
			$import_pack[] = $v[0];
		}
		$sdkchannel = D('sendNum.SdkChannel');
		$all_game = $sdkchannel->get_all_game($_POST['channel_id'],$import_pack);
		if($_POST['channel_type']==1){
			$import_info = $sdkchannel->insert_sdk_game_nopack($all_game['game'],$_POST['channel_id']);
			$result = array(
					'failnum'=>count($all_game['failarr']),
					'successnum'=>$import_info?$import_info:0,
					'failarr'=>  json_encode($all_game['failarr'])
			);
		}else{
			$import_info = $sdkchannel->batch_save_channel_game($all_game,$_POST['channel_id'],$_POST['need_test']);
			$result = array(
					'failnum'=>count($import_info['failarr']),
					'successnum'=>count($import_info['game']),
					'failarr'=>  json_encode($import_info['failarr'])
			);
		}


		return $result;
	}
	//导入全部符合条件的游戏
	function related_all_game($channel_id,$need_test,$channel_type=0){
		$sdkchannel = D('sendNum.SdkChannel');
		$all_game = $sdkchannel->get_all_game($channel_id,'',$channel_type);
		if(count($all_game['game'])>0){
			if($channel_type==1){
				//不打包渠道游戏
				$sdkchannel->insert_sdk_game_nopack($all_game['game'],$channel_id);
			}else{
				//发送到worker入库并同步到bi
				$task_client = get_task_client();
				foreach($all_game['game'] as $key=>$val){
					$task_data['package'] = $val['package'];
					$task_data['channel_id'] = $_POST['channel_id'];
					$task_data['need_test'] = $_POST['need_test'];
					$task_client->doBackground('channel_game', json_encode($task_data));
				}
			}
			if($channel_type==1){
				$table = 'sdk_channel_game_nopack';
			}else{
				$table = 'sdk_channel_game';
			}
			$this -> writelog("渠道游戏管理:批量导入了渠道ID为{$channel_id}的游戏", $table,$channel_id,__ACTION__ ,'','add');
			$this->success('关联中，前往列表页等候');
		}else{
			$this->error('没有符合条件的可以关联');
		}

	}
	//手动渠道推广-通用配置
	//1.1 通用配置
	function SdkConfigure(){
		$model = M('');
		//$where = array();
		$option = array(
			'table' => 'pu_config',
			'where' => array(
				'config_type' => array('exp',"='SdkConfigure_email' or `config_type` = 'SdkConfigure_reject_email' or `config_type` = 'SdkConfigure_email_time' or `config_type` = 'SdkConfigure_probability'"),
			),
			'field' => '*'
		);
		$SdkConfigure = $model -> findAll($option);
		//$SdkConfigure = $model->table('pu_config')->where(array('conf_id'=>array('in','267,268,269')))->select();
		//echo "<pre>";print_r($SdkConfigure);
		//邮件处理
		$SdkConfigure[0]['configcontent'] = explode(';',$SdkConfigure[0]['configcontent']);
		$SdkConfigure[3]['configcontent'] = explode(';',$SdkConfigure[3]['configcontent']);
		//print_r($SdkConfigure);
		$this->assign('SdkConfigure',$SdkConfigure);
		$this->display();
	}
	//1.2 邮箱添加
	function SdkConfigure_email(){
		if($_POST){
			$model = M('');
			if(isset($_POST['type'])&&$_POST['type']=='reject'){
				//testin测试未通过邮件发送人
				$where = array('config_type'=>'SdkConfigure_reject_email');
			}else{
				$where = array('config_type'=>'SdkConfigure_email');
			}
			$email = $model->table('pu_config')->where($where)->select();//查询数据库数据
			$emails = explode(';',$email[0]['configcontent']);//查出所有email
			if(in_array(trim($_POST['email']),$emails )){
				//已经存在
				echo 110;
				return 110;
			}else{
				//可以添加
				$emails = array_filter($emails);
				array_push($emails,trim($_POST['email']));
				$email = implode(';',$emails);
				$log = $this->logcheck($where, 'pu_config', array('configcontent'=>$email), $model);
				$res = $model->table('pu_config')->where($where)->save(array('configcontent'=>$email));
				if($res){
					$this -> writelog("通用配置：修改了配置,{$log}", 'sdk_channel_game',var_export($where,true),__ACTION__ ,'','edit');
					echo 1;
					return 1;
				}else{
					echo 2;
					return 2;					
				}
			}
			//$res = $model->table('pu_config')->add($data);$_POST['email']
			exit;
		}
		if(isset($_GET['type'])){
			$this->assign('type',$_GET['type']);
		}
		$this->display();
	}
	//1.3概率更改
	function edit_probability(){
		$model = M('');
		$log = $this->logcheck(array('config_type'=>'SdkConfigure_probability'), 'pu_config', array('configcontent'=>trim($_POST['probability'])), $model);
		$res = $model->table('pu_config')->where(array('config_type'=>'SdkConfigure_probability'))->save(array('configcontent'=>trim($_POST['probability'])));
		if($res){
			$this -> writelog("通用配置：修改了配置,{$log}", 'sdk_channel_game',var_export(array('config_type'=>'SdkConfigure_probability'),true),__ACTION__ ,'','edit');
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	//1.4邮件发送时间
	function edit_times(){
		$model = M('');
		$log = $this->logcheck(array('config_type'=>'SdkConfigure_email_time'), 'pu_config', array('configcontent'=>trim($_POST['email_time'])), $model);
		$res = $model->table('pu_config')->where(array('config_type'=>'SdkConfigure_email_time'))->save(array('configcontent'=>trim($_POST['email_time'])));
		if($res){
			$this -> writelog("通用配置：修改了配置,{$log}", 'pu_config',var_export(array('config_type'=>'SdkConfigure_email_time'),true),__ACTION__ ,'','edit');
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	//1.5邮件地址删除
	function del_email(){
		$model = M('');
		if(isset($_POST['type'])&&$_POST['type']=='reject'){
			$where = array('config_type'=>'SdkConfigure_reject_email');
			$type = 'reject';
		}else{
			$where = array('config_type'=>'SdkConfigure_email');
			$type = '';
		}
		$SdkConfigure = $model->table('pu_config')->where($where)->select();
		$emails = explode(';',$SdkConfigure[0]['configcontent']);
		foreach($emails as $k=>$v){
			if(trim($_POST['email']) == $v){
				unset($emails[$k]);
			}else{
				if($v){
					$emails_arr .=$v.'  <a href="javascript:;" onclick="del_email(\''.$v.'\','.$v.')"><img src="/Public/images/delete_icon.png"></a>&nbsp;&nbsp;';
				}
			}
		}
		//删除邮箱
		$email = implode(';',$emails);
		$log = $this->logcheck($where, 'pu_config',array('configcontent'=>$email), $model);
		$res = $model->table('pu_config')->where($where)->save(array('configcontent'=>$email));
		if($res){
			$this -> writelog("通用配置：修改了配置,{$log}", 'pu_config',var_export($where,true),__ACTION__ ,'','del');
			$result = array('id'=>1,'msg'=>'删除成功','arr'=>$emails_arr);
			echo json_encode($result);
			return json_encode($result);
		}else{
			$result = array('id'=>2,'msg'=>'删除失败');
			echo json_encode($result);
			return json_encode($result);
		}
	}

	//推广产品管理列表
	function extend_list(){
		$model = M('');
		$sdkchannel = D('sendNum.SdkChannel');
		$where = array('status'=>1);
		if(isset($_GET['softname'])){
			$where['softname'] = array("like","%{$_GET['softname']}%");
			$this->assign('softname', $_GET['softname']);
		}
		if(isset($_GET['package'])){
			$where['package'] = $_GET['package'];
			$this->assign('package', $_GET['package']);
		}
		if(isset($_GET['extend_sta'])){
			$where['extend_sta'] = $_GET['extend_sta'];
			$this->assign('extend_sta', $_GET['extend_sta']);
		}
		if(isset($_GET['sdk_status'])){
			$package = $sdkchannel->get_sdk_channel_game(array("sdk_status"=>$_GET['sdk_status']));
			$where['package'] = array("in",$package);
			$this->assign('sdk_status', $_GET['sdk_status']);
		}
		$total = $model->table('sdk_channel_extend')->where($where)->count();
		//$total = count($channel_list);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
		$param = http_build_query($_GET);
		import('@.ORG.Page2');
		$Page = new Page($total, $limit, $param);
		$Page->rollPage = 10;
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '首页');
		$Page->setConfig('last', '尾页');
		$extend_game = $sdkchannel->get_extend_game($where,"*",$Page->firstRow,$Page->listRows);
		$package = array();
		foreach($extend_game as $k=>$v){
			$package[] = $v['package'];
		}

		$game_info = $sdkchannel->get_newest_info($package);
		$pass_info = $sdkchannel->get_pass_info($package);
		$market_info = $sdkchannel->get_market_newest_info($package);
		$this->assign('param', $param);
		$this->assign('page', $Page->show());
		$this->assign('game_info',$game_info);
		$this->assign('pass_info',$pass_info);
		$this->assign('market_info',$market_info);
		$this->assign('extend_game',$extend_game);
		$this->display();
	}

	//添加推广产品
	function save_extend_game(){
		$sdkchannel = D('sendNum.SdkChannel');
		if(!empty($_GET['game'])){
			$game = explode(',',substr($_GET['game'],0,-1));
			$where = array(
					'package'=>array('in',$game)
			);
			$has_extend = $sdkchannel->get_extend_game($where);
			//剔除已存在的产品
			$msg = array();
			if($has_extend){
				foreach($has_extend as $k=>$v){
					if(in_array($v['package'],$game)){
						$this_key = array_keys($game,$v['package']);
						$msg[] = "{$v['package']}已存在";
						unset($game[$this_key[0]]);
					}
				}
			}
			//var_dump($game);
			$soft_info = get_table_data(array('package'=>array('in',$game)),'sj_soft','package','softid,softname,version,version_code,package');
			$this_online = array();
			foreach($soft_info as $k=>$v){
				$this_online['softid'][] = $v['softid'];
			}
			$sdk_version = get_table_data(array('softid'=>array('in',$this_online['softid']),'record_type'=>array('in',array('1','3'))),'sj_soft_tmp','package','package,sdk_version','id desc');
			foreach($soft_info as $k=>$v){
				$v['sdk_version'] = $sdk_version[$v['package']]['sdk_version'];
				$soft_info[$k] = $v;
			}
			$res = $sdkchannel->save_extend_game($soft_info);
			if(is_array($res)){
				foreach($res as $k=>$v){
					unset($soft_info[$v]);
				}
			}
			if(count($soft_info)>0){
				$game_info['game'] = $soft_info;
				$channel_id = C('general_channel_id');
				$pro_res = $sdkchannel->batch_save_channel_game($game_info,$channel_id,1,"extend");
				if($pro_res){
					$log_str = "手游渠道推广-推广产品管理：添加了包名为";
					$pack = '';
					foreach($pro_res['game'] as $k=>$v){
						$log_str .= $v['package'].",";
						$pack .= $v['package'].",";
					}
					$log_str .="的游戏";
					$this->writelog($log_str,'sdk_channel_game',$pack,__ACTION__ ,'','add');
					$this->success('添加成功');
				}
			}else{
				$error = "添加失败";
				if(count($msg)>0){
					$error .= ','.implode(',',$msg);
				}
				$this->error($error);
			}
		}else{
			$this->error("未选择任何产品");
		}
	}

	//取消推广
	function cancel_extend(){
		$id = $_GET['id'];
		if($id){
			$model = M('');
			$extend_sta=$_GET['type']?2:3;
			$res = $model->table('sdk_channel_extend')->where("id='{$id}'")->save(array("extend_sta"=>$extend_sta,"update_tm"=>time()));
			$str=$_GET['type']?'重新推广':'取消推广';
			$operate=$_GET['type']?'edit':'del';
			if($res){
				$this->writelog("手游渠道推广-推广产品管理：{$str}id为{$id}",'sdk_channel_extend',$id,__ACTION__ ,'',$operate);
				$this->success("{$str}成功");
			}else{
				$this->error("{$str}失败");
			}
		}else{
			$this->error("缺少参数");
		}
	}

	//运营位管理
	function extend_game_position(){
		$position = isset($_GET['position'])?$_GET['position']:1;
		$sdkchannel = D('sendNum.SdkChannel');
		$where = array('status'=>1,'position'=>$position);
		if($_GET['softname']){
			$where['softname'] = array('like',"%{$_GET['softname']}%");
			$this->assign('softname',$_GET['softname']);
		}
		if($_GET['package']){
			$where['package'] = $_GET['package'];
			$this->assign('package',$_GET['package']);
		}
		list($res,$extend_game,$pass_info,$file_info) = $sdkchannel->get_extend_game_position($where);
		$this->assign('res',$res);
		$this->assign('extend_game',$extend_game);
		$this->assign('pass_info',$pass_info);
		$this->assign('file_info',$file_info);
		$this->assign('position',$position);
		$this->display();
	}

	//添加运营位游戏
	function save_extend_position_game(){
		if(isset($_GET['game'])&&!empty($_GET['game'])){
			if(isset($_GET['position'])){
				$sdkchannel = D('sendNum.SdkChannel');
				$package = explode(',',substr($_GET['game'],0,-1));
				$where = array(
						'package'=>array('in',$package)
				);

				$res = $sdkchannel->save_extend_position_game($package,$_GET['position']);
				if(count($res)==0){
					$this->writelog("手游渠道推广-运营位管理：添加了包名为{$package}的游戏，运营位为{$this->extend_game_position[$_GET['position']]}",'sdk_channel_position',$_GET['game'],__ACTION__ ,'','add');
					$this->success("添加成功");
				}
			}else{
				$this->error("未选择运营位");
			}
		}
	}

	//编辑运营位排序
	function edit_extend_position_rank(){
		$id = $_GET['id'];
		if($id){
			if(!is_numeric($_GET['rank'])||$_GET['rank']<=0){
				echo json_encode(array("code"=>-1,"msg"=>'请输入正整数'));
			}else{
				$sdkchannel = D('sendNum.SdkChannel');
				$res = $sdkchannel->update_extend_game("id={$id}",array("rank"=>$_GET['rank']));
				if($res){
					$this->writelog("手游渠道推广-运营位管理:修改了id为{$id}的游戏排序值为{$_GET['rank']}",'sdk_channel_position',$id,__ACTION__ ,'','edit');
					echo json_encode(array("code"=>1,"msg"=>'修改成功'));
				}else{
					echo json_encode(array("code"=>-1,"msg"=>'修改失败'));
				}
			}
		}else{
			echo json_encode(array("code"=>-1,"msg"=>'未选择要修改排序的id'));
		}
	}

	//删除运营位游戏
	function del_game(){
		$id = $_GET['id'];
		if($id){
			$model  = M('');
			$res = $model->table('sdk_channel_position')->where("id={$id}")->save(array("status"=>0));
			if($res){
				$this->writelog("手游渠道推广-运营位管理:删除id为{$id}的游戏",'sdk_channel_position',$id,__ACTION__ ,'','del');
				$this->success("删除成功");
			}else{
				$this->error("删除失败");
			}
		}else{
			$this->error("缺少参数");
		}
	}
}
?>