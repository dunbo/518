<?php
class OTApackageAction extends CommonAction{

	function OTAlist(){
		$model = new Model();
		if($_GET['version_code']){
			$where_go .= " and (new_version_code = {$_GET['version_code']} or old_version_code = {$_GET['version_code']})";
		}

		if($_GET['version_name']){
			$where_version['_string'] = "version_name like '%{$_GET['version_name']}%'";
			$version_result = $model -> table('sj_market') -> where($where_version) -> select();
		
			foreach($version_result as $key => $val){
				$version_code_str_go .= $val['id'].',';
			}
	
			$version_code_str = substr($version_code_str_go,0,-1);
			$where_go .= " and (new_market_id in ({$version_code_str}) or old_market_id in ({$version_code_str})) ";
		}
		
		if($_GET['chname']){
			$where_channel['_string'] = "chname like '%{$_GET['chname']}%'";
			$channel_result = $model -> table('sj_channel') -> where($where_channel) -> select();
			foreach($channel_result as $key => $val){
				$cid_str_go .= $val['cid'].',';
			}
		
			$cid_str = substr($cid_str_go,0,-1);
			$where_cid['_string'] = "cid in ({$cid_str})";
			$cid_result = $model -> table('sj_market') -> where($where_cid) -> select();
			
			foreach($cid_result as $key => $val){
				$id_str_go .= $val['id'].',';
			}
			$id_str = substr($id_str_go,0,-1);
			
			$where_go .= " and (new_market_id in ({$id_str}) or old_market_id in ({$id_str}))";
		}
		
		if($_GET['platform']){
			$where_platform = "platform = {$_GET['platform']}";
			$platform_result = $model -> table('sj_market') -> where($where_platform) -> select();
			foreach($platform_result as $key => $val){
				$id_str_go .= $val['id'].',';
			}
			$id_str = substr($id_str_go,0,-1);
			$where_go .= " and (new_market_id in ({$id_str}) or old_market_id in ({$id_str}))";
		}
		import("@.ORG.Page");
		
		if($_GET['status'] == 1 || $_GET['status'] == ''){
			$status = 1;
			$count = $model -> table('sj_market_patch') -> where("status = 1".$where_go) -> count();
			$Page = new Page($count, 10);
			$result = $model -> table('sj_market_patch') -> where("status = 1".$where_go) -> limit($Page->firstRow.','.$Page->listRows) -> order('id DESC') -> select();
		}elseif($_GET['status'] == 0){
			$status = 0;
			$count = $model -> table('sj_market_patch') -> where("status = 0".$where_go) -> count();
			$Page = new Page($count, 10);
			$result = $model -> table('sj_market_patch') -> where("status = 0".$where_go) ->limit($Page->firstRow.','.$Page->listRows) -> order('id DESC') -> select();
		}
	
		foreach($result as $key => $val){
			$new_market_str_go .= $val['new_market_id'].',';
			$old_market_str_go .= $val['old_market_id'].',';
		}
		
		$new_market_str = substr($new_market_str_go,0,-1);
		$old_market_str = substr($old_market_str_go,0,-1);
		$where_market['_string'] = "id in ({$new_market_str})";
		$other_result = $model -> table('sj_market') -> where($where_market) -> select();
		$where_old_market['_string'] = "id in ({$old_market_str})";
		$other_old_result = $model -> table('sj_market') -> where($where_old_market) -> select();
		foreach($other_old_result as $key => $val){
			$other_old_results[$val['id']] = $val;
		}
		foreach($other_result as $key => $val){
			if($val['platform'] == 1){
				$val['platform_name'] = '安智市场';
			}elseif($val['platform'] == 2){
				$val['platform_name'] = '安智助手';
			}elseif($val['platform'] == 3){
				$val['platform_name'] = 'sd卡备份';
			}elseif($val['platform'] == 4){
				$val['platform_name'] = '平板';
			}elseif($val['platform'] == 5){
				$val['platform_name'] = '游戏客户端';
			}
			$channel_str_go .= $val['cid'].',';
			$other_results[$val['id']] = $val;
		}
		
		$channel_str = substr($channel_str_go,0,-1);
		$chname_where['_string'] = "cid in ({$channel_str})";
		$chname_result = $model -> table('sj_channel') -> where($chname_where) -> select();
		foreach($chname_result as $key => $val){
			$chname_results[$val['cid']] = $val;
		}
		foreach($result as $key => $val){
			$val['platform_name'] = $other_results[$val['new_market_id']]['platform_name'];
			$val['chname'] = $chname_results[$other_results[$val['new_market_id']]['cid']]['chname'];
			if(!$val['chname']){
				$val['chname'] = "通用";
			}
			$val['new_size'] = $other_results[$val['new_market_id']]['apksize'];
			$val['old_size'] = $other_old_results[$val['old_market_id']]['apksize'];
			$result[$key] = $val;
		}
		
		$util = D('Sj.Util');
		$product_list = $util->getProducts($platform);
		
		
		$Page -> setConfig('header', '篇记录');
		$Page -> setConfig('first', '<<');
		$Page -> setConfig('last', '>>');
		$show = $Page->show();
		$this->assign('page', $show);
	
		$this->assign('product_list',$product_list); 
		$this -> assign('version_code',$_GET['version_code']);
		$this -> assign('version_name',$_GET['version_name']);
		$this -> assign('chname',$_GET['chname']);
		$this -> assign('platform',$_GET['platform']);
		$this -> assign('status',$status);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function release_market(){
		$model = new Model();
		$id = $_GET['id'];
		$need_result = $model -> table('sj_market_patch') -> where(array('id' => $id)) -> select();
		$task_client = get_task_client();
		$r = $task_client->doBackground("market_incremental_update",json_encode(array('id'=>$need_result[0]['new_market_id'],'oid'=> array($need_result[0]['old_market_id']),'inc_type'=>'update')));
		$package_result = $model -> table('sj_market') -> where(array('id' => $need_result[0]['new_market_id'])) -> select();
		$the_model = M('market_patch');
		$data = array(
			'update_at' => time(),
			'status' => 1,
		);
		$where['id'] = $id;
		$result = $the_model -> where($where) -> save($data);

		if($result){
		    $this->__set('waitSecond',5);
			$this -> writelog('重新生成了ID为['.$need_result[0]['new_market_id'].']的OTA增量包','sj_market_patch',$id,__ACTION__ ,"","edit");
			$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/OTApackage/OTAlist');
			$this->success("生成成功，但更新包生成较慢，列表数据出现会比较慢，如果没有出现数据，稍后刷新下列表");
		}else{
			$this->error("生成失败");
		}
		
	}
    
    function manual_generate_otaPackage() {
        if ($_POST) {
            $model = D("Sj.Products2");
            $info = array();
            $info_status = 0;
            // 判断高版本id是否存在
            $where = array(
                'id' => array('EQ', $_POST['high_id']),
                'status' => array('EQ', 1),
            );
            $high_record = $model->where($where)->find();
            if (!$high_record) {
                $info[0] = "id【{$_POST['high_id']}】不存在";
                $info_status |= 1;
            }
            // low_ids_arr数组存放低版本ids，并去重
            $low_ids_arr = explode(",", $_POST['low_ids']);
            $low_ids_arr = array_unique($low_ids_arr);
            // low_record_arr数组存放低版本ids对应的数据库记录信息
            $low_record_arr = array();
            foreach($low_ids_arr as $id) {
                $where = array(
                    'id' => array('EQ', $id),
                    'status' => array('EQ', 1),
                );
                $low_record = $model->where($where)->find();
                if (!$low_record) {
                    if ($info[1] != "") {
                        $info[1] .= "、";
                    }
                    $info[1] .= "id【{$id}】";
                    $info_status |= 2;
                } else {
                    $low_record_arr[$id] = $low_record;
                }
            }
            if ($info[1] != "") {
                $info[1] .= "不存在";
            }
            if (!empty($info[0])) {
                $this->ajaxReturn($info, "", $info_status);
            }
            
            // 判断低版本平台是否和高版本平台相同
            $high_platform = $high_record['platform'];
            $high_cid = $high_record['cid'];
            $high_real_version_code = $high_record['real_version_code'];
            //$mini_version_code = $high_platform == 1 ? 4300 : ($high_platform == 5 ? 1100 : 4300);
            $mini_version_code = $high_platform == 1 ? 4300 : ($high_platform == 5 ? 1100 : 0);
            foreach($low_record_arr as $low_record) {
                if ($low_record['platform'] != $high_platform) {
                    $info[1] .= "【{$low_record['id']}】与高版本不在一个平台上，";
                    $info_status |= 2;
                    continue;
                }
                if ($low_record['real_version_code'] < $mini_version_code) {
                    $info[1] .= "【{$low_record['id']}】的版本号【{$low_record['real_version_code']}】不能低于【{$mini_version_code}】，";
                    $info_status |= 2;
                    continue;
                }
                if ($low_record['real_version_code'] >= $high_real_version_code) {
                    $info[1] .= "【{$low_record['id']}】的版本号【{$low_record['real_version_code']}】需小于高版本的版本号【{$high_real_version_code}】，";
                    $info_status |= 2;
                    continue;
                }
                if ($low_record['cid'] != $high_cid) {
                    if ($high_cid == 0) {
                        // left join表sj_channel，看当前渠道是不是非独立更新
                        $map = array();
                        $map['sj_market.id'] = array('EQ', $low_record['id']);
                        $ret = $model->table('sj_market')->join("left join sj_channel on sj_market.cid=sj_channel.cid")->field('alone_update')->where($map)->find();
                        if ($ret['alone_update'] != 0) {
                            $info[1] .= "【{$low_record['id']}】的渠道为独立更新渠道，";
                            $info_status |= 2;
                        }
                    } else {
                        $info[1] .= "【{$low_record['id']}】与高版本不在一个渠道上，";
                        $info_status |= 2;
                    }
                }
            }
            if (!empty($info)) {
                $this->ajaxReturn($info, "", $info_status);
            }
            // 查找以前是否生成过，如果生成过，删除之，并重新生成
            $patch_model = M('market_patch');
            foreach($low_ids_arr as $low_id) {
                $where = array(
                    'new_market_id' => array('EQ', $_POST['high_id']),
                    'old_market_id' => array('EQ', $low_id),
                    'status' => array('EQ', 1),
                );
                $exists = $patch_model->where($where)->find();
                if ($exists) {
                    $data = array();
                    $data['status'] = -1;
                    $patch_model->where($where)->save($data);
                    $this -> writelog("id为{$exists['id']}的人工生成OTA增量包被删除",'sj_market_patch',$exists['id'],__ACTION__ ,"","del");
                } else {
					$data = array();
                    $data['status'] = 0;
					$data['new_market_id'] = $_POST['high_id'];
					$data['new_md5'] = $high_record['md5_file'];
					$data['old_market_id'] = $low_id;
					$data['old_md5'] = $low_record_arr[$low_id]['md5_file'];
					$data['create_at'] = time();
					$id=$patch_model->table('sj_market_patch')->add($data);
					$this -> writelog("人工生成了id为{$id}的OTA增量包",'sj_market_patch',$id,__ACTION__ ,"","add");
				}
            }
            $task_client = get_task_client();
            $task_client->doBackground("market_incremental_update",json_encode(array('id'=>$_POST['high_id'],'oid'=>$low_ids_arr)));
            $this -> writelog("人工生成OTA增量包，其中高版本id为{$_POST['high_id']}，低版本id为{$_POST['low_ids']}");
            $this->ajaxReturn(NULL, "生成成功！", 0);
            
        }
        $this->display("manual_generate_otaPackage");
    }
	

}