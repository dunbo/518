<?php
class SdkChannelModel extends Model {
	private  $general_channel_id;
	function __construct()
	{
		$this->general_channel_id =  C('general_channel_id');
	}

	//搜索能添加签名的游戏
	function search_sign_game(){
		$model = new Model();
		if(!empty($_POST['game_name'])||!empty($_POST['package'])){		
			$table = 'sdk_channel_game';
			$where = 'status = 1';
			if(!empty($_POST['game_name'])){
				$where .= ' and name like "%'.$_POST['game_name'].'%"';
			}
			if(!empty($_POST['package'])){
				$where .= ' and package = "'.$_POST['package'].'"';
			}
			$game = $model->table($table)->where($where)->group('package')->field('id,package,name')->select();
			$game = array_values($game);
			return $game;
		}
	}
	
	//签名游戏列表
	function get_sdk_sign_list(){
		$model = new Model();
		$table = 'sdk_channel_game_sign';
		$where = array('status'=>1);
		if(isset($_GET['soft_name'])&&!empty($_GET['soft_name'])) $where['softname'] = array('like',"%{$_GET['soft_name']}%");
		if(isset($_GET['package'])&&!empty($_GET['package'])) $where['package'] = array('like',"%{$_GET['package']}%");
		$total = $model->table($table)->where($where)->count();
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
		$list = $model ->table($table)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		return array($list,$Page->show());
	}

	/*
	 * 合作渠道管理数据处理
	*@desc
	* $soft 软件信息
	* $type(1为升级审核通过时插入数据)
	* $channel_id 渠道id
	* $need_test(是否需要测试，默认需测试)
	*/
	function save_channel_game_sdk($soft,$type,$record_type,$channel_id,$need_test=1){
		$model = M('');
		$soft_id = $soft_id_pack = $has_soft = $package = array();
		if($type == 1){
		//版本升级审核
			foreach($soft as $k=>$v){
				if($record_type == 3){
					//518后台升级
					$soft_version = $model->table('sj_soft')->where(array('package'=>$v['package'],'status'=>1,'hide'=>1))->field('softid,version_code,version,softname')->order('softid desc')->find();
					$v['softid'] = $soft_version['softid'];
				}else{
					$soft_version = $model->table('sj_soft')->where(array('softid'=>$v['softid']))->field('version_code,version,softname')->find();
				}				
				$v['version_code'] = $soft_version['version_code'];
				$v['version'] = $soft_version['version'];
				$v['softname'] = $soft_version['softname'];
				$soft[$k] = $v;
				$update_pack = $model->table('sdk_channel_game_sdk')->where(array('package'=>$v['package']))->field('softid,sdk_status,version_code')->find();
				if(!$update_pack||$update_pack['version_code']==$v['version_code']) unset($soft[$k]);
			}
		}
		if(count($soft)>0){
			foreach($soft as $k=>$v){
				//升级通过审核将之前所有版本数据置为历史并插入新版本对应渠道游戏
				if($type == 1){
					$tmp = $this->chk_update_need_tmp($v['package'],$v['version_code']);
					if($tmp){
						$this->update_need_tmp($v['package'],$v['version_code'],$v['softid']);
					}else{
						$oldversion_data = $model->table('sdk_channel_game')->where(array('package'=>$v['package'],'channel_id'=>$this->general_channel_id))->select();
						$sql = $model->getLastSql();
						$this->write_error_log('sdkchannel.log',$this->general_channel_id."\n".$sql."\n\n");
						if($oldversion_data){
							//$process_general_channel = false;
							if(!isset($channel_id)) $channel_id = array();
							foreach($oldversion_data as $key=>$val){
								//将历史成功数据插入bak表
								if($val['apk_status']==3){
									$this->insert_old_version($val);
								}

									$channel_id[] = $val['channel_id'];
									$this->write_error_log('sdkchannel.log',"test".$val['channel_id']."\n\n");
									//更新新版本数据
									$update_new_version = $model->table('sdk_channel_game')->where(array('id'=>$val['id']))->save(array('softid'=>$v['softid'],'name'=>$v['softname'],'channel_softname'=>$v['softname'],'version_code_num'=>$v['version_code'],'version_code'=>$v['version'],'url'=>'','filesize'=>'','md5_file'=>'','apk_status'=>'0','update_tm'=>time()));
									//同步数据到bi									
									if($update_new_version){										
										$url = C('updateRelation');
										$channel_code = $this->get_channel_code($val['channel_id']);
										$http_data = array(
											'version'=>$v['version'],
											'channelId'=>$channel_code,
											'appName'=>$v['softname'],
											'appNameChannel' => $v['softname'],
											'pkgName'=>$v['package']
										);
									$http_info = $this->update_date_by_import($url,$http_data);
									if(!$http_info){
										$model->table('sdk_channel_game')->where(array('id'=>$val['id']))->save(array('http_sta'=>'3'));
									}
								}
//							}
							}
						}
					}
				}
				if(!$tmp){
//					if($process_general_channel){
//						//渠道包无效时插入通用渠道包
//						$this->process_general_channel($v);
//					}
					//判断同包名同版本是否已存在与合作渠道审核表，如已在不做处理，不再添加
					$channel_id = array_unique($channel_id);
					$where = array(
							'package'=>$v['package'],
							'version_code'=>$v['version_code'],
							'status'=>1,
							'channel_id'=>array('in',$channel_id)
					);
					$sdk_soft = $model->table('sdk_channel_game_sdk')->where($where)->field('softid,sdk_status,channel_id')->select();
					$this->write_error_log('sdkchannel.log',$model->getLastSql());
					if($sdk_soft){
						foreach($sdk_soft as $sdk_k=>$sdk_v){
							if(in_array($sdk_v['channel_id'],$channel_id)){
								$key = array_search($sdk_v['channel_id'],$channel_id);
								unset($channel_id[$key]);
							}
						}
					}
					//准备要插入的id
					$soft_id_pack[$v['package']] = $v['softid'];
					$soft_id[] = $v['softid'];
					$package[] = $v['package'];
				}
			}
			if(count($channel_id)>0&&!$tmp){
				$soft_apk = get_table_data(array('softid'=>array('in',$soft_id),'package_status'=>1),'sj_soft_file','apk_name','apk_name,url');

				$soft_info = get_table_data(array('softid'=>array('in',$soft_id)),'sj_soft','package','softname,version,version_code,package,update_content');
				$soft_tmp = get_table_data(array('softid'=>array('in',$soft_id),'record_type'=>array('in',array('1','3'))),'sj_soft_tmp','package','package,sdk_version');
				$p_fenlei = get_table_data(array('package'=>array('in',$package),'status'=>1),'sj_soft_whitelist','package','softname,package,fen_lei as p_fenlei');	
				foreach($package as $k=>$v){
					if(!$p_fenlei[$v]||empty($p_fenlei[$v]['p_fenlei'])){
						$yx_feilei = $model->table('yx_product')->where(array('package'=>$v,'del'=>0))->field('p_fenlei')->find();
						$p_fenlei[$v]['p_fenlei'] = $yx_feilei['p_fenlei'];
					}
					foreach($channel_id as $channel_k=>$channel_v){
						$rank = $model->table('sdk_channel_game_sdk')->field('max(rank) as rank')->find();
						if($soft_tmp[$v]['sdk_version']==''){
							//后台升级需获取sdk_version
							$sdk_ver = check_sdk_ver(' /data/att/m.goapk.com' .$soft_apk[$v]['url']);
							$soft_tmp[$v]['sdk_version'] = implode(',', $sdk_ver);
						}
						$insert_data = array(
							'softid'=>$soft_id_pack[$v],
							'softname'=>$p_fenlei[$v]['softname'],
							'package'=>$v,
							'sdk_version'=>$soft_tmp[$v]['sdk_version'],
							'game_type'=>$p_fenlei[$v]['p_fenlei'],
							'version'=>$soft_info[$v]['version'],
							'version_code'=>$soft_info[$v]['version_code'],
							'url'=>$soft_apk[$v]['url'],
							'sdk_status'=>4,
							'channel_id' => $channel_v,
							'update_tm'=>time(),
							'create_tm'=>time(),
							'rank'=>$rank['rank']+1
						);
						if($type==1){
							$insert_data['need_test'] = 1;
						}else{
							$insert_data['need_test'] = $need_test;
						}
						
						if(isset($record_type)&&!empty($record_type)){
							$insert_data['record_type'] = $record_type;
						}else{
							if($type==1){
								$insert_data['record_type'] = 3;
							}else{
								$insert_data['record_type'] = 1;
							}
						}
						$res = $model->table('sdk_channel_game_sdk')->add($insert_data);
						$this->write_error_log('sdkchannel.log',$model->getLastSql());
						if(!$res){
							$sql = $model->getLastSql();
							$log = "关联渠道游戏到testin审核表时失败\n" . $sql ."\n\n";
							$this->write_error_log('sdkchannel.log',$log);
						}
					}
					
				}
			}
			
		}
		
	}

	/*
	 * @desc 有渠道无效时处理通用渠道
	 * $soft 要处理的游戏
	 */
	function process_general_channel($soft){
		$general_channel = $model->table('save_channel_game')->where(array('channel_id'=>$this->general_channel_id,'package'=>$soft['package'],'version_code_num'=>$soft['version_code']))->find();
		if(!$general_channel){
			$insert_general = array(
					'softid'=>$soft['softid'],
					'name'  =>$soft['softname'],
					'channel_softname'=>$soft['softname'],
					'package'=>$soft['package'],
					'version_code_num'=>$soft['version_code'],
					'version_code'=>$soft['version'],
					'channel_id'=>$this->general_channel_id,
					'add_tm'=>time(),
					'update_tm'=>time()
			);
			$insert_res = $model->table('sdk_channel_game')->add($insert_general);
				if($insert_res){
				//同步通用渠道游戏到bi
//				$channelId = $model->table('sdk_channel')->where(array('id'=>$this->general_channel_id))->field('channel_name,channel_code')->find();
//				$app_key = $model->table('sj_sdk_info')->where(array('package'=>$soft['package']))->field('app_id')->find();
//				$url = C('addRelation');
//				$http_data = array(
//						'status'=>1,
//						'id'=>$insert_res,
//						'channelId'=>$channelId['channel_code'],
//						'channelName' => $channelId['channel_name'], //新版增加渠道名称2015.7.23
//					//'APPKEY' => $app_key['app_id'];  //新版增加appkey
//						'version'=>$soft['version'],
//						'appName'=>$soft['softname'],
//						'pkgName'=>$soft['package']
//				);
//				$http_info = $this->update_date_by_import($url,$http_data);
//				if(!$http_info){
//					$model->table('sdk_channel_game')->where(array('id'=>$insert_res))->save(array('http_sta'=>'2'));
//				}
			}else{
				$sql = $model->getLastSql();
				$log = "插入通用渠道游戏失败" . $sql ."\n\n";
				$this->write_error_log('sdkchannel.log',$log);
			}
		}
	}
	function write_error_log($filename,$msg){
		$now = time();
		$path = "/data/att/permanent_log/admin_data_log/".date("Y-m-d", $now);
		if(!file_exists($path)){
			mkdir($path, 0755, true);
		}	
		$path_log = $path."/".$filename;
		$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
		file_put_contents($path_log, $msg, FILE_APPEND);
	}

	//接口
	public function update_date_by_import($url,$data){		
		import("@.ORG.GoDes");
        $host = C('sdk_host');
		$privatekey = C('sdk_3des_key');
		$des = new GoDes($privatekey);
		$list = array();
		$list['list'][] = $data;
		$list['pid'] = 0;
		//var_dump($data);
		$temp_data = $des->encrypt(json_encode($list));
        $i_data = base64_encode($temp_data);
		$vals['data'] = $i_data;
		$vals['appKey'] = C('sdk_app_key');
		//var_dump($host.$url);
		//var_dump($vals);
		// exit();
		$log = date("Y-m-d H:i:s")."\n" . print_r($list, true) . "\n" . print_r($url, true) . "\n\n";
		permanentlog('sdk_channel_http.log', $log);
		$res = httpGetInfo($host.$url, $vals,'sdk_channel_http.log');
		//var_dump($res);
		$last = json_decode($res,true);
		//var_dump($last);exit;
		if($last['res']['statusCode']!='200'){
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * +----------------------------------------------------------
	 * 获取渠道下符合关联条件的所有游戏
	 * channel_id 渠道id  import_pack 导入包名
	 * +----------------------------------------------------------
	 */
	function get_all_game($channel_id,$import_pack,$channel_type = 0){
		if(!empty($channel_id)){
			$model = M('');
			$failarr =  array();
			//新版数据源来源于推广产品
			$where1 = array('status'=>1,'extend_sta'=>2);
			if($import_pack&&count($import_pack)>0){
				$where1['package'] = array('in',$import_pack);
			}

			$extend_game = $this->get_extend_game($where1,'package');
			$can_package = array();
			foreach($extend_game as $k=>$v){
				$can_package[] = $v['package'];
			}

			if($import_pack&&count($import_pack)>0){
				foreach($import_pack as $k=>$v){
					if(!in_array($v,$can_package)){
						$failarr[$v]['package'] = $v;
						$failarr[$v]['error_msg'] = "不在推广产品中或不处于推广中状态";
					}
				}
			}

			$where['package'] = array('in',$can_package);
			if(count($can_package)==0){
				$result = array(
						'failarr' => $failarr
				);
				return $result;
			}
			$where = '(b.fen_lei = "网游" or a.p_fenlei ="网游") and (a.del=0 or a.del IS NULL ) and b.status = 1';
			$pack_str="";
			foreach($can_package as $k=>$v){
				$pack_str .="'{$v}',";
			}
			$pack_str = substr($pack_str,0,-1);
			$where .=" and b.package in ({$pack_str})";
			$sql = 'select b.softname,b.package from sj_soft_whitelist b left join yx_product a on b.package = a.package where '.$where;
			$game = $model->query($sql);
//			$game = $model->table('sj_soft_whitelist')->where($where)->field('package')->select();
			foreach($game as $k=>$v){
				$this_key = array_keys($can_package,$v['package']);
				unset($can_package[$this_key[0]]);
			}
			foreach($can_package as $k=>$v){
				$failarr[$v]['package'] = $v;
				$failarr[$v]['error_msg'] = "不在白名单或不是网游";
			}
			if(count($game)==0){
				$result = array(
						'failarr' => $failarr
				);
				return $result;
			}
			$package = array();
			foreach($game as $key=>$val){
				$this_online = $model->table('sj_soft')->where(array('package'=>$val['package'],'status'=>1,'hide'=>1))->field('softid,softname,package,version,version_code')->order('softid desc')->find();

				$game[$key]['softid'] = $this_online['softid'];
				$game[$key]['softname'] = $this_online['softname'];
				$game[$key]['version'] = $this_online['version'];
				$game[$key]['version_code'] = $this_online['version_code'];
				if(!$this_online){
					$failarr[$val['package']]['package'] = $val['package'];
					$failarr[$val['package']]['error_msg'] = "当前不是上架状态";
					unset($game[$key]);
				}else{				
					$sdk_version = $model->table('sj_soft_tmp')->where(array('softid'=>$this_online['softid']))->field('sdk_version')->order('softid desc')->find();
					if(!$sdk_version||empty($sdk_version['sdk_version'])){
						$failarr[$val['package']]['package'] = $val['package'];
						$failarr[$val['package']]['error_msg'] = "sdk版本不是3.2以上";
						unset($game[$key]);
					}else{
						//	判断sdk版本是否为3.2以上
						$ver = explode('.',$sdk_version['sdk_version']);
						if(empty($sdk_version['sdk_version'])||$ver[0]< 3||($ver[0]==3&&$ver[1] < 2)){
							$failarr[$val['package']]['package'] = $val['package'];
							$failarr[$val['package']]['error_msg'] = "sdk版本不是3.2以上";
							unset($game[$key]);
						}else{
							$game[$key]['sdk_version'] = $sdk_version['sdk_version'];
							$package[] = $val['package'];
						}
					}
				}	
			}
			//剔除同渠道同一款同版本的游戏
			if($channel_type==1){
				$table = 'sdk_channel_game_nopack';
			}else{
				$table = 'sdk_channel_game';
			}
			$sdk_info = get_table_data(array('package'=>array('in',$package),'channel_id'=>$channel_id),$table,'package','package,version_code_num,version_code');
			$game = array_values($game);
			foreach($game as $key=>$val){
				if($sdk_info[$val['package']]&&$sdk_info[$val['package']]['version_code_num']==$val['version_code']){
					$failarr[$val['package']]['package'] = $val['package'];
					$failarr[$val['package']]['error_msg'] = "该渠道下已存在相同版本";
					unset($game[$key]);
				}
			}
			array_unique($game);
			$result = array(
				'failarr' => $failarr,
				'game'	=> $game
			);

			return $result;	
		}
	}
	
	/**
	 * +----------------------------------------------------------
	 * 批量关联游戏入库
	 * game_info 游戏信息
	 * channel_id 渠道id
	 * need_test 是否需要测试
	 * +----------------------------------------------------------
	 */
	function batch_save_channel_game($game_info,$channel_id,$need_test,$from=""){
		
		if(count($game_info['game'])>0&&!empty($channel_id)){
			$model = M('');
			$sql = "insert into sdk_channel_game (`softid`,`name`,`channel_softname`,`package`,`version_code_num`,`version_code`,`channel_id`,`add_tm`,`update_tm`,`creater`) values ";
			$time = time();
			$creater = $_SESSION['admin']['admin_id'];
			$game_info['game'] = array_values($game_info['game']);
			foreach($game_info['game'] as $key=>$val){
				$n_sql = "('{$val['softid']}','{$val['softname']}','{$val['softname']}','{$val['package']}','{$val['version_code']}','{$val['version']}','{$channel_id}','{$time}','{$time}','{$creater}')";
				if(count($game_info['game']) == $key+1){
					$last_sql .= $n_sql;
				}else{
					$last_sql .= $n_sql .',';
				}
			}
			$r_sql = $sql.$last_sql;
			$insert_game = $model->execute($r_sql);
			if($insert_game){
				$insert_game_sdk = $this->batch_save_channel_game_sdk($game_info['game'],$channel_id,$need_test,$from);
				if($insert_game_sdk[0]){
					$this->relate_game_to_bi($insert_game_sdk[1],$insert_game_sdk[2],$channel_id);
					return $game_info;
				}
			}else{
				$sql = $model->getLastSql();
				$log = "批量关联游戏入库:插入sdk_channel_game失败" . $sql ."\n\n";
				if($from=="extend") $log .="(来源于推广产品添加)\n\n";
				$this->write_error_log('sdkchannel.log',$log);
			}
			
		}else{
			return $game_info;
		}
	}
	
	/**
	 * +----------------------------------------------------------
	 * 批量关联游戏入库(sdk_channel_game_sdk)
	 * game_info 游戏信息
	 * channel_id 渠道id
	 * need_test 是否需要测试
	 * +----------------------------------------------------------
	 */
	function batch_save_channel_game_sdk($game_info,$channel_id,$need_test=0,$from=""){
		$model = M('');
		$package = $r_pack = $soft_id = array();
		
		foreach($game_info as $k=>$v){
			//剔除sdk_channel_game_sdk中同渠道同版本的游戏
			$has_info = $model->table('sdk_channel_game_sdk')->where(array('package'=>$v['package'],'softid'=>$v['softid'],'channel_id'=>$channel_id,'version_code'=>$v['version_code']))->field('id')->select();
			if(!$has_info){
				$package[$v['package']]['package'] = $v['package'];
				$package[$v['package']]['softid'] = $v['softid'];
				$package[$v['package']]['softname'] = $v['softname'];
				$package[$v['package']]['version'] = $v['version'];
				$package[$v['package']]['version_code'] = $v['version_code'];
				$package[$v['package']]['sdk_version'] = $v['sdk_version'];
				$r_pack[] = $v['package'];
				$soft_id[] = $v['softid'];
			}
		}
		$soft_apk = get_table_data(array('softid'=>array('in',$soft_id),'package_status'=>1),'sj_soft_file','apk_name','apk_name,url');

		$game_rank = $model->table('sdk_channel_game_sdk')->field('max(rank) as rank')->find();
		$rank = $game_rank['rank']+1;
		$sql = "insert into sdk_channel_game_sdk (`softid`,`softname`,`package`,`sdk_version`,`game_type`,`version`,`version_code`,`url`,`sdk_status`,`channel_id`,`update_tm`,`create_tm`,`rank`,`need_test`,`record_type`) values ";
		$time = time();
		$package = array_values($package);
		foreach($package as $key=>$val){
			$n_sql = "('{$val['softid']}','{$val['softname']}','{$val['package']}','{$val['sdk_version']}','网游','{$val['version']}','{$val['version_code']}','{$soft_apk[$val['package']]['url']}','4','{$channel_id}','{$time}','{$time}','{$rank}','{$need_test}','1')";
			if(count($package) == $key+1){
				$last_sql .= $n_sql;
			}else{
				$last_sql .= $n_sql .',';
			}
			$rank++;
		}
		$r_sql = $sql.$last_sql;
		$insert_game_sdk = $model->execute($r_sql);
		if(!$insert_game_sdk){
			$sql = $model->getLastSql();
			$log = "批量关联游戏入库:插入sdk_channel_game_sdk失败" . $sql ."\n\n";
			if($from=="extend") $log .="(来源于推广产品添加)\n\n";
			$this->write_error_log('sdkchannel.log',$log);
		}
		return array($insert_game_sdk,$r_pack,$package);
	}
	/**
	 * +----------------------------------------------------------
	 * 关联游戏入库同步到bi
	 * r_pack 需同步到bi的包名
	 * package 包名信息
	 * channel_id 渠道id
	 * +----------------------------------------------------------
	 */
	function relate_game_to_bi($r_pack,$package,$channel_id){
		$model = M('');
		$channelId = $model->table('sdk_channel')->where(array('id'=>$channel_id))->field('channel_name,channel_code')->find();
		$app_key = get_table_data(array('package'=>array('in',$r_pack)),'sj_sdk_info','package','package,app_id');
		foreach($package as $key=>$val){
			$id = $model->table('sdk_channel_game')->where(array('package'=>$val['package'],'softid'=>$val['softid'],'channel_id'=>$channel_id,'version_code_num'=>$val['version_code']))->field('id')->find();
			$package[$key]['id'] = $id['id'];
		}
		
		$url = C('addRelation');
		foreach($package as $key=>$val){
			$http_data = array(
				'status'=>1,
				'id'=>$val['id'],
				'channelId'=>$channelId['channel_code'],
				'version'=>$val['version'],
				'appName'=>$val['softname'],
				'channelName'=>$channelId['channel_name'], //新版增加渠道名称 2015.7.23
				//'APPKEY'=>$app_key['app_id'], 新版新增appkey
				'appNameChannel' => $val['softname'],
				'pkgName'=>$val['package']
			);
			$sdkchannel = D('sendNum.SdkChannel');
			$http_info = $this->update_date_by_import($url,$http_data);
			if(!$http_info){
				$model->table('sdk_channel_game')->where(array('id'=>$val['id']))->save(array('http_sta'=>'2'));	
			}else{
				$model->table('sdk_channel_game')->where(array('id'=>$val['id']))->save(array('http_sta'=>'1'));	
			}
		}
		
	}

	//获取通用渠道产品最新状态信息
	function get_newest_info($package){
		$game_info = get_table_data(array('package'=>array('in',$package),'channel_id'=>$this->general_channel_id),'sdk_channel_game','package','softid,version_code,version_code_num,package');
//		$max_id = $this->get_max_id(array('package'=>array('in',$package),'channel_id'=>$this->general_channel_id),'sdk_channel_game_sdk');
		$game_sdk_info =  get_table_data(array('package'=>array('in',$package),'channel_id'=>$this->general_channel_id),'sdk_channel_game_sdk','package','package,sdk_status,id,record_type');
		foreach($game_info as $k=>$v){
			$v['sdk_status'] = $game_sdk_info[$v['package']]['sdk_status'];
			$v['id'] = $game_sdk_info[$v['package']]['id'];
			$v['record_type'] = $game_sdk_info[$v['package']]['record_typesave_channel_game_sdk'];
			$game_info[$k] = $v;
		}
		//var_dump($game_info);
		return $game_info;
	}

	//获取通用渠道产品最新通过产品信息
	function  get_pass_info($package){
//		$max_id = $this->get_max_id(array('package'=>array('in',$package),'channel_id'=>$this->general_channel_id,'sdk_status'=>1),'sdk_channel_game_sdk');
		$pass_info =  get_table_data(array('package'=>array('in',$package),'channel_id'=>$this->general_channel_id,'sdk_status'=>1),'sdk_channel_game_sdk','package','channel_id,package,version,version_code,game_type,sdk_version,review_tm');
		return $pass_info;
	}

	//获取市场最新版本状态
	function get_market_newest_info($package){
//		$max_id = $this->get_max_id(array('package'=>array('in',$package)),'sj_soft_tmp');
		$market_info = get_table_data(array('package'=>array('in',$package)),'sj_soft_tmp','package','package,sdk_status,status');
		return $market_info;
	}

	//获取分组最大id
	function get_max_id($where,$table,$group="package"){
		$model = M('');
		$id = array();
		$max_id = $model->table($table)->where($where)->field('max(id) as id')->group('package')->select();
		foreach($max_id as $k=>$v){
			$id[] = $v['id'];
		}
		return $id;
	}
	//获取推广产品
	function get_extend_game($where,$field="*",$limit_from="",$limit_to=""){
		$model = M('');
		if(!empty($limit_to)){
			$extend_game = $model->table('sdk_channel_extend')->where($where)->field($field)->limit($limit_from . ',' . $limit_to)->order('add_tm desc')->findAll();
		}else{
			$extend_game = $model->table('sdk_channel_extend')->where($where)->field($field)->findAll();
		}

//		echo $model->getLastSql();
		return $extend_game;
	}

	//保存推广产品
	function save_extend_game($data){
		$model = M('');
		$error_pack = array();
		foreach($data as $k=>$v){
			if(!$v['package']) continue;
			$insert_data = array(
				'softname'=>$v['softname'],
				'package'=>$v['package'],
				'add_tm	'=>time()
			);
			$res = $model->table('sdk_channel_extend')->add($insert_data);
			if(!$res) $error_pack[] = $v['package'];
		}
		if(count($error_pack)>0){
			return $error_pack;
		}else{
			return true;
		}
	}

	//通过testin测试改变推广状态
	function update_extend_status($package){
		//更新推广状态
		$model = M('');
		$up_res = $model->table('sdk_channel_extend')->where(array('package'=>array('in',$package)))->save(array("extend_sta"=>2));
		if($up_res){
			return true;
		}
	}

	//根据testin测试状态获取软件
	function get_sdk_channel_game($where,$field="package",$group="package"){
		$model = M('');
		$where['channel_id'] = $this->general_channel_id;
		$res = $model->table('sdk_channel_game_sdk')->where($where)->field($field)->group($group)->select();
		$package = array();
		foreach($res as $k=>$v){
			$package[] = $v['package'];
		}
		return $package;
	}

	/*
	 *添加运营位游戏
	 *  positon(运营位位置)
	 */
	function save_extend_position_game($package,$position){
		$model = M('');
		$soft_info = get_table_data(array('package'=>array('in',$package),'hide'=>1,'status'=>1),'sj_soft','package','softid,softname,package');
		$product_info = get_table_data(array('package'=>array('in',$package),'del'=>0),'yx_product','package','package,p_leixing');
		foreach($package as $k=>$v){
			$rank =$model->table('sdk_channel_position')->where("status = 1 and position = {$position} and add_by = 1")->field('max(rank) as rank')->find();
			$has_info = $model->table('sdk_channel_position')->where("status = 1 and position = {$position} and package = '{$v}'")->find();
			if($has_info){
				$res = $model->table('sdk_channel_position')->where("id = {$has_info['id']}")->save(array('add_tm'=>time(),'rank'=> (int)$rank['rank']+1,'add_by'=>1));
			}else{
				$data = array(
						'softname'		=> $soft_info[$v]['softname'],
						'package'			=> $soft_info[$v]['package'],
						'p_leixing'			=> $product_info[$v]['p_leixing'],
						'add_tm'			=> time(),
						'add_by'			=> 1,
						'rank'					=> (int)$rank['rank']+1,
						'position'			=> $position
				);
				$res = $model->table('sdk_channel_position')->add($data);
			}

			$fail_arr = array();
			if(!$res){
				$fail_arr[] = $v;
				continue;
			}
		}
		return $fail_arr;
	}

	//获取运营位游戏
	function  get_extend_game_position($where){
		$model = M('');
		$where['add_by'] = 1;
		$res =$model->table('sdk_channel_position')->where($where)->order("rank asc")->select();
		//echo $this->sdkchannel_model->getLastSql();
		if($res){
			$package = array();
			foreach($res as $k=>$v){
				$package[] = $v['package'];
			}
			//获取列表上架时间（推广产品添加时间）
			$extend_game = get_table_data(array('package'=>array('in',$package),'status'=>1),'sdk_channel_extend','package','package,add_tm');

			//最新通过测试的版本
			$pass_info = $this->get_pass_info($package);

			//游戏大小
			$file_info = get_table_data(array('package'=>array('in',$package),'channel_id'=>$this->general_channel_id),'sdk_channel_game','package','package,filesize');
			foreach($file_info as $k=>$v){
				$v['filesize'] = byte_format($v['filesize']);
				$file_info[$k] = $v;
			}

			return array($res,$extend_game,$pass_info,$file_info);
		}else{
			return false;
		}
	}

	//更新运营位产品
	function update_extend_game($where,$data){
		$model = M('');
		$res = $model->table('sdk_channel_position')->where($where)->save($data);
		return $res;
	}

	//判断是否需要渠道更新
	function is_need_channel_update($package,$version_code){
		$model = M('');
		$res = $model->table('sdk_channel_game')->where(array('package'=>$package,'status'=>1,'version_code_num'=>array('exp'," < {$version_code}")))->find();
		$this->write_error_log('sdkchannel_tmp.log',$model->getLastSql());
		return $res;
	}

	//SDK测试通过处理sdk_channel_game_tmp数据
	function save_sdk_channel_game_tmp($data){
		$model = M('');
		if(isset($data['tmp_id'])&&isset($data['file_tmp_id'])){
			$has_info = $model->table('sdk_channel_game_tmp')->where(array('tmp_id'=>$data['tmp_id'],'file_tmp_id'=>$data['file_tmp_id'],'status'=>1))->find();
			$this->write_error_log('sdkchannel_tmp.log',$model->getLastSql());
			if(!$has_info){
				$where = array('package'=>$data['package'],'version_code'=>$data['version_code'],'status'=>1);
				$save_status = $model->table('sdk_channel_game_tmp')->where($where)->save(array('status'=>-1,'update_tm'=>time()));
				$data['channel_id'] = 27;
				$data['update_tm'] = $data['add_tm'] = time();
				$data['sdk_status'] = 4;
				$res = $model->table('sdk_channel_game_tmp')->add($data);

				if(!$res){
					$this->write_error_log('sdkchannel_tmp.log',$model->getLastSql());
				}
			}
		}
	}

	//升级审核通过判断是否处理sdk_channel_game_tmp到正式表中
	function chk_update_need_tmp($package,$version_code){
		$this->write_error_log('sdkchannel_tmp.log',"chk_update_need_tmp:step1:{$package}--{$version_code}");
		$model = M('');
		//如有此版本的包的通用渠道非审核状态即处理，审核状态不需处理
		$has_info = $model->table('sdk_channel_game_tmp')->where(array('package'=>$package,'status'=>1,'version_code'=>$version_code,'channel_id'=>$this->general_channel_id,'sdk_status'=>array('in',array('1','3'))))->find();
		$this->write_error_log('sdkchannel_tmp.log',"chk_update_need_tmp:step2:".$model->getLastSql());
		return $has_info;
	}

	function update_need_tmp($package,$version_code,$softid){
		$this->write_error_log('sdkchannel_tmp.log',"update_need_tmp:step1:{$package}--{$version_code}--{$softid}");
		$model = M('');
		$all_apk = $model->table('sdk_channel_game_tmp')->where(array('package'=>$package,'status'=>1,'version_code'=>$version_code,'apk_status'=>3,'sdk_status'=>array('in',array('1','3'))))->select();
		$channel_id = $id_arr = array();
		if($all_apk){
			foreach($all_apk as $k=>$v){
				$channel_id[] = $v['channel_id'];

			}
			$old_version = $model->table('sdk_channel_game')->where(array('package'=>$package,'status'=>1,'channel_id'=>array('in',$channel_id)))->select();
			if($old_version){
				foreach($old_version as $key=>$val){
					$id_arr[$val['channel_id']] = $val['id'];
					$this->insert_old_version($val);
				}
				foreach($all_apk as $a_k=>$a_v){
					$save_data = array(
						'softid'=>$softid,
						'name'=>$a_v['softname'],
						'channel_softname'=>$a_v['softname'],
						'version_code_num'=>$a_v['version_code'],
						'version_code'=>$a_v['version'],
						'url'=>$a_v['apk_url'],
						'filesize'=>$a_v['filesize'],
						'md5_file'=>$a_v['md5_file'],
						'sign'=>$a_v['sign'],
						'apk_status'=>3,
						'update_tm'=>time()
					);
					$res = $model->table('sdk_channel_game')->where(array('package'=>$a_v['package'],'channel_id'=>$a_v['channel_id']))->save($save_data);
					if(!$res){
						$this->write_error_log('sdkchannel_tmp.log','update_need_tmp:step3:'.$model->getLastSql());
					}else{
						$a_v['softid'] = $softid;
						$this->incremental_update_to_worker($a_v);
						$url = C('updateRelation');
						$channel_code = $this->get_channel_code($a_v['channel_id']);
						$http_data = array(
							'version'=>$a_v['version'],
							'channelId'=>$channel_code,
							'appName'=>$a_v['softname'],
							'appNameChannel' => $a_v['softname'],
							'pkgName'=>$a_v['package']
						);
						$http_info = $this->update_date_by_import($url,$http_data);
						if(!$http_info){
							$model->table('sdk_channel_game')->where(array('id'=>$id_arr[$a_v['channel_id']]))->save(array('http_sta'=>'3'));
						}
					}
					$sdk = $model->table('sdk_channel_game_sdk')->where(array('package'=>$a_v['package'],'status'=>1,'version_code'=>$a_v['version_code'],'channel_id'=>$a_v['channel_id'],'softid'=>$softid))->find();
					if(!$sdk){
						$insert_data = array(
								'softid'=>$softid,
								'softname'=>$a_v['softname'],
								'package'=>$a_v['package'],
								'game_type'=>'网游',
								'sdk_version'=>$a_v['sdk_version'],
								'version'=>$a_v['version'],
								'version_code'=>$a_v['version_code'],
								'record_type'=>3,
								'url_apk'=>$a_v['apk_url'],
								'url'=>$a_v['url'],
								'sdk_send'=>$a_v['sdk_send'],
								'sdk_status'=>$a_v['sdk_status'],
								'status'=>1,
								'test_report'=>$a_v['test_report'],
								'channel_id'=>$a_v['channel_id'],
								'review_tm'=>$a_v['review_tm'],
								'update_tm'=>time(),
								'create_tm'=>time(),
								'need_test'=>$a_v['channel_id']==$this->general_channel_id?1:0,
								'sign'=>$a_v['sign'],
								'reviewer'=>3000 //新逻辑设定
						);
						$new_res = $model->table('sdk_channel_game_sdk')->add($insert_data);
						if(!$new_res){
							$this->write_error_log('sdkchannel_tmp.log','update_need_tmp:step4:'.$model->getLastSql());
						}
					}

				}
			}

		}

	}

	function insert_old_version($val){
		$model = M('');
		$old_data = array(
				'softid'=>$val['softid'],
				'name'=>$val['name'],
				'channel_softname'=>$val['channel_softname'],
				'package'=>$val['package'],
				'version_code_num'=>$val['version_code_num'],
				'version_code'=>$val['version_code'],
				'channel_id'=>$val['channel_id'],
				'url'=>$val['url'],
				'filesize'=>$val['filesize'],
				'md5_file'=>$val['md5_file'],
				'apk_status'=>$val['apk_status'],
				'add_tm'=>$val['add_tm'],
				'update_tm'=>time(),
				'status'=>$val['status'],
				'http_sta'=>$val['http_sta']
		);
		$old_res = $model->table('sdk_channel_game_bak')->add($old_data);
		if(!$old_res){
			$sql = $model->getLastSql();
			$this->write_error_log('sdkchannel_tmp.log','update_need_tmp:step4:'.$sql."\n\n");
		}
	}

	function incremental_update_to_worker($data){
		$task_client = get_task_client();
		$task_data = array();
		$task_data['softid'] = $data['softid'];
		$task_data['package'] = $data['package'];
		$task_data['channel_id'] = $data['channel_id'];
		$task_data['version_code_num'] = $data['version_code'];
		$task_data['is_general_channel'] = ($data['channel_id']==$this->general_channel_id)?1:0;
		$task_client->doBackground('incremental_update_channel_sdk', json_encode($task_data));
	}

	function insert_sdk_game_nopack($game,$channel_id){
		if(!$game) return false;
			$model = M('');
			$sql = "insert into sdk_channel_game_nopack (`softid`,`name`,`channel_softname`,`package`,`version_code_num`,`version_code`,`channel_id`,`add_tm`,`update_tm`,`creater`) values ";
			$time = time();
			$creater = $_SESSION['admin']['admin_id'];
			$game = array_values($game);
			$last_sql = '';
			foreach($game as $key=>$val){
				$n_sql = "('{$val['softid']}','{$val['softname']}','{$val['softname']}','{$val['package']}','{$val['version_code']}','{$val['version']}','{$channel_id}','{$time}','{$time}','{$creater}')";
				if(count($game) == $key+1){
					$last_sql .= $n_sql;
				}else{
					$last_sql .= $n_sql .',';
				}
			}
			$r_sql = $sql.$last_sql;
			$res = $model->execute($r_sql);
			return $res;
	}
	function get_channel_code($cid){
		$model = M('');
		$channel = $model->table('sdk_channel')->where(array('id'=>$cid))->field('channel_code')->find();
		return $channel['channel_code'];
	}
}

