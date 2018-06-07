<?php

class SoftWhitelistAction extends CommonAction {
    /*
     * 软件白名单列表
     * @date 2013.8.9
     * */

    public function ShowWhitelist() {
        $act = empty($_GET['act']) ? '' : $_GET['act'];
        if($act=='export_data'){
            $this->export_data();
            return;
        }
		$model = new Model();
        $util = D("Sj.Util");
        import('@.ORG.Page');
        $cond = array();
        $where = $where2 = '';
        if (!empty($_GET['seach_val'])) {
            $show_type = $_GET['seach_type'];
            $seach_val = $_GET['seach_val'];
            if ($show_type == 1) {
				$where_s = array(
					'status'=>1,
					'hide'=>1,
					'softname'=> array('like',"%$seach_val%")
				);
				$subQuery = $model->table('sj_soft')->where($where_s)->field('package')->buildSql(); 
                $where = "(w.softname like '%{$seach_val}%' or w.package in ({$subQuery}) ) and  ";
            } elseif ($show_type == 2) {
                $seach_type = 'package';
                $where = "w.$seach_type = '{$seach_val}' and ";
            } elseif ($show_type == 3) {
				$where_u = array(
					's.hide'=>1,
					's.status'=>1,
					'u.status'=>0,
					'u.dev_name'=>$seach_val
				);
				$table_p = "sj_soft as s left join pu_developer u on u.dev_id=s.dev_id";
				$subQuery = $model->table($table_p)->where($where_u)->field('s.package')->buildSql(); 
                $where = "w.package in ({$subQuery}) and ";
            } elseif ($show_type == 4) {
                $seach_type = 'admin_name';
                $where = "w.admin_name like '%{$seach_val}%' and ";
            } elseif ($show_type == 5) {
				$where_u = array(
					's.hide'=>1,
					's.status'=>1,
					'u.status'=>0,
					'u.email'=>$seach_val
				);
				$table_p = "sj_soft as s left join pu_developer u on u.dev_id=s.dev_id";
				$subQuery = $model->table($table_p)->where($where_u)->field('s.package')->buildSql(); 				
                $where = "w.package in ({$subQuery}) and ";
            }
            $this->assign('seach_val', $seach_val);
            $this->assign('show_type', $show_type);
        }
        if (!empty($_GET['dev_type'])) {
            if ($_GET['dev_type'] == 2) {
				$type = 0;
            } elseif ($_GET['dev_type'] == 1) {
                $type = 1;
            }
			$where_u = array(
				'status'=>0,
				'type'=>$type
			);
			$subQuery = $model->table('pu_developer')->where($where_u)->field('dev_id')->buildSql();
			$where .= " w.dev_id in ({$subQuery}) and ";
            $this->assign('dev_type', $_GET['dev_type']);
        }
        if (isset($_GET['isshelves'])) {
            if ($_GET['isshelves'] == 0) {
                $where .= " w.is_time_shelves = 0 and ";
            } elseif ($_GET['isshelves'] == 1) {
                $where .= " w.is_time_shelves = 1 and ";
            }
            $this->assign('isshelves', $_GET['isshelves']);
        }
        if (isset($_GET['is_sdk'])) {
            if ($_GET['is_sdk'] == 0) {
                $where .= " w.is_sdk = 0 and ";
            } elseif ($_GET['is_sdk'] == 1) {
                $where .= " w.is_sdk = 1 and ";
            }
            $this->assign('is_sdk', $_GET['is_sdk']);
        }
        if (isset($_GET['is_online'])) {
            if ($_GET['is_online'] == 0) {
                $where .= " w.is_online = 0 and ";
            } elseif ($_GET['is_online'] == 1) {
                $where .= " w.is_online = 1 and ";
            }
            $this->assign('is_online', $_GET['is_online']);
        }

        //搜索条件（是否干预搜索结果）
        if (isset($_GET['is_intervres'])) {
            if ($_GET['is_intervres'] == 0) {
                $where .= " w.is_intervres = 0 and ";
            } elseif ($_GET['is_intervres'] == 1) {
                $where .= " w.is_intervres = 1 and ";
            }
            $this->assign('is_intervres', $_GET['is_intervres']);
        }
        //搜索条件（添加流程）
        if (isset($_GET['add_from'])) {
            if ($_GET['add_from'] == 1) {
                $where .= " w.add_from = 1 and ";
            } elseif ($_GET['add_from'] == 2) {
                $where .= " w.add_from = 2 and ";
            }
            $this->assign('add_from', $_GET['add_from']);
        }

        //游戏类型
         if (isset($_GET['game_type'])&&$_GET['game_type']!='') {
            $where .= "(b.p_fenlei like '%".$_GET['game_type']."%' or w.fen_lei like '%".$_GET['game_type']."%')and ";
            $this->assign('game_type', $_GET['game_type']);
        }
	//是否接入sdk
	if (isset($_GET['is_accept_sdk'])&&$_GET['is_accept_sdk']!='') {
            $where .= "w.is_accept_sdk = {$_GET['is_accept_sdk']} and ";
            $this->assign('is_accept_sdk', $_GET['is_accept_sdk']);
        }
	//是否接入账号
	if (isset($_GET['is_accept_account'])&&$_GET['is_accept_account']!='') {
            $where .= "w.is_accept_account = {$_GET['is_accept_account']} and ";
            $this->assign('is_accept_account', $_GET['is_accept_account']);
        }
     //游戏广告库
    if (isset($_GET['is_accept_ad'])&&$_GET['is_accept_ad']!='') {
            $where .= "w.is_accept_ad = {$_GET['is_accept_ad']} and ";
            $this->assign('is_accept_ad', $_GET['is_accept_ad']);
        }
	//是否接入支付
	if (isset($_GET['is_accept_pay'])&&$_GET['is_accept_pay']!='') {
            $where .= "w.is_accept_pay = {$_GET['is_accept_pay']} and ";
            $this->assign('is_accept_pay', $_GET['is_accept_pay']);
        }

        //是否新游列表
        if (isset($_GET['newgamelist'])&&$_GET['newgamelist']!='') {
            $where .= "w.newgamelist = {$_GET['newgamelist']} and ";
            $this->assign('newgamelist', $_GET['newgamelist']);
        }

        if(isset($_GET['status'])&&!empty($_GET['status'])){
            $where .= "w.status = {$_GET['status']}";
            $this->assign('status',$_GET['status']);
        }else{
            $where .= "w.status != 0 ";
        }
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $param = http_build_query($_REQUEST);
        $where .= ' and (b.del = 0 or b.del is null)'; //or b.del = 1 
        $where2 .= $where . '  GROUP BY w.package ORDER BY ';
        if (isset($_GET['is_intervres'])) {
            if ($_GET['is_intervres'] == 1) {
                $where2 .= " w.runindex desc";
            } else {
                $where2 .= "w.update_at  DESC";
            }
        } else {
            $where2 .= "w.update_at  DESC";
        }
        $table = 'sj_soft_whitelist w left join yx_product b on b.package=w.package';
        $all = $model->table('sj_soft_whitelist  w left join yx_product b on b.package=w.package')->where($where)->group('w.package')->field('id')->select();
		$total = count($all);
        $page = new Page($total, $limit);
        $piracyList = $model->table($table)->where($where2)->field('w.*,b.p_fenlei')->limit($page->firstRow . ',' . $page->listRows)->select();
		$pkg = array();
        foreach($piracyList as $key =>$val){
            if($val['is_sdk']==0){
//                unset($piracyList[$key]['p_fenlei']);
//                unset($piracyList[$key]['fen_lei']);
            }
			$pkg[] = $val['package'];
        }
		$where = array(
			's.package' => array('in',$pkg),
			's.hide' => 1,
			's.status' => 1,
		);
        $table3 = "sj_soft as s left join pu_developer u on u.dev_id=s.dev_id ";
        $soft_arr = get_table_data($where,$table3,"package","s.package,s.softname,u.type,u.dev_name,u.email");
        foreach($piracyList as $key =>$val){
            $piracyList[$key]['online_softname'] = $soft_arr[$val['package']]['softname'];
            $piracyList[$key]['type'] = $soft_arr[$val['package']]['type'];
            $piracyList[$key]['dev_name'] = $soft_arr[$val['package']]['dev_name'];
            $piracyList[$key]['email'] = $soft_arr[$val['package']]['email'];
        }
        $typelist = $util->getHomeExtentSoftTypeList();
        $this->assign('typelist',$typelist);
        $page->setConfig('header', '篇记录');
        $page->setConfig('first', '<<');
        $page->setConfig('last', '>>');
        $this->assign('page', $page->show());
        $this->assign('count', $total);
        $this->assign('piracyList', $piracyList);
        $this->assign('piracyList_two',json_encode($piracyList));
        $this->display('ShowWhitelist');
    }

    public function DeleteWhite() {
        $time = time();
        $flag = true;
        if (!isset($_GET['id'])) {
            //$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
            $this->error('ID不能为空');
        }
        $id = json_decode($_GET['id']);
        if (!$id) {
            //$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
            $this->error('ID格式错误');
        }
        $model = new Model();
        foreach ($id as $v) {
            $ret = $model->table('sj_soft_whitelist')->where("id = $v")->field('status,package,is_sdk')->select();
            if ($ret[0]['status'] != 0) {
                $res = $model->table('sj_soft_whitelist')->where("id = $v")->save(array('status' => 0, 'update_at' => $time));
                if (!$res)
                    $flag = false;
                else
                    if($ret[0]['is_sdk']==1){
                        update_soft_status (array('sdk_status'=>-1), $ret[0]['package']);
                    }
                    $this->writelog('删除了ID为' . $v . '运营白名单软件', 'sj_soft_whitelist',$v,__ACTION__ ,'','del');
            }
        }
        if ($flag == false) {
            //$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
            $this->error('删除失败');
        } else {
            //$this->assign('jumpUrl', '/index.php/Sj/SoftOfficial/confirm');
            $this->success('删除成功');
        }
    }

    public function GetSoftname() {
        $package = trim($_POST['package']);
        if (empty($package) || $package == '获取失败') {
            $result = array('success' => false, 'msg' => '请输入正确包名');
            echo json_encode($result);
            exit();
        }
        $model = new Model();
		$where = array('package'=>$package,'status'=>array('in',array(1,2)));
        $is_package = $model->table('sj_soft_whitelist')->where($where)->select();
        //$res = $model->table('sj_soft')->field('dev_id,softname,dev_name,package')->where("package = '{$package}' and claim_status=2 and status=1 and hide=1")->select();
        if ($is_package) {
            $result = array('success' => false, 'msg' => '该包名已被添加过');
            echo json_encode($result);
            exit();
        }
        //$res = $model->table('sj_soft')->field('dev_id,softname,dev_name,package')->where("package = '{$package}' and claim_status=2 and status=1 and hide=1 ")->find();
        $res = $model->table('sj_soft')->join('pu_developer ON sj_soft.dev_id = pu_developer.dev_id')->field('sj_soft.dev_id,sj_soft.softname,sj_soft.package,sj_soft.category_id,pu_developer.dev_name')->where("sj_soft.package = '{$package}'  and sj_soft.status=1 and sj_soft.hide=1 ")->find();
        if ($res) {
            $result = array(
                'success' => true,
                'msg' => '获取成功！',
                'softname' => $res['softname'],
                'dev_id' => $res['dev_id'],
                'dev_name' => $res['dev_name']
            );
            if (substr($res['category_id'], 1, -1) == C('newgame_id')) {
                $result['new_game'] = 1;
            }
            echo json_encode($result);
            exit();
        } else {
            $result = array('success' => false, 'msg' => '该包名不存在于已上架列表');
            echo json_encode($result);
            exit();
        }
    }

    public function EditWhitelist() {
        $model = new Model();
        $sub_type = trim($_POST['sub_type']);
        $res = false;
        //$show_str = '';
        $time = time();
        if ($sub_type == 'add') {
			// if (empty($_POST['softname'][0])){
			// 	$this->error('软件名称不能为空');
			// }
			// if (empty($_POST['package'][0])){
			// 	$this->error('包名不能为空');
			// }
			// if (empty($_POST['dev_id']) || empty($_POST['dev_name'])){
			// 	//$this->error('包名不存在已上架软件列表');
			//     //$this->error('添加失败');
			// }
            foreach ($_POST['package'] as $k => $v) {
                //if(empty($v)) continue;
                $softname = trim($_POST['softname'][$k]);

                $package = trim($_POST['package'][$k]);
                $dev_id = trim($_POST['dev_id'][$k]);
                $dev_name = trim($_POST['dev_name'][$k]);
                $is_shelves = trim($_POST['is_shelves'][$k]);
                $is_sdk = trim($_POST['is_sdk'][$k]);
                $fen_lei = empty($_POST['fen_lei'][$k])?'':trim($_POST['fen_lei'][$k]);
                $ignore = trim($_POST['ignore'][$k]);
                $status = $_POST['status'][$k];
                if(!$status){
                    $this->error('请选择是否是联运游戏');
                }
                if($is_sdk==1){
                    if($fen_lei==''){
                        $this->error('请选择游戏类型');
                    }
                }
                $online_soft = $model->table('sj_soft')->where("package='{$package}' and status=1 and hide = 1")->field('softid')->select();
                $is_online = $online_soft ? 1 : 0;
                $is_intervres = trim($_POST['is_intervres'][$k]);
                $runindex = intval(trim($_POST['runindex'][$k]));
                $runindex ? $runindex : $runindex = 0;
                if ($is_intervres == 1 && ($runindex < 0 || $runindex > 100)) {
                    $this->error('干预搜索结果时，软件运营指数不能为空且大于0小于等于100');
                }
                if (empty($softname)) {
                    $this->error('软件名称不能为空');
                }
                if (empty($package)) {
                    $this->error('包名不能为空');
                }
                if (empty($dev_id) || empty($dev_name)) {
                    //$this->error('包名不存在已上架软件列表');
                    //$this->error('添加失败');
                }
				$where = array('package'=>$package,'status'=>array('in',array(1,2)));
                $is_package = $model->table('sj_soft_whitelist')->where($where)->select();
				$where = array('softname'=>$softname,'status'=>array('in',array(1,2)));
                $is_softname = $model->table('sj_soft_whitelist')->where($where)->select();
                if ($is_package) {
                    $this->error('包名已存在该列表[ ' . $package . ']');
                }
                if ($is_softname) {
                    $this->error('软件名已存在该列表 [' . $softname . ']');
                }
                $admin_name = $model->table('yx_product')->where(array('package' => $package, 'type' => 3))->getField('fuzeren');
                $data = array(
                    'softname' => "{$softname}",
                    'dev_name' => "{$dev_name}",
                    'admin_name' => "{$admin_name}",
                    'package' => "{$package}",
                    'dev_id' => "{$dev_id}",
                    'create_at' => "{$time}",
                    'update_at' => "{$time}",
                    'is_time_shelves' => "{$is_shelves}",
                    'is_sdk' => "{$is_sdk}",
                    'is_online' => "{$is_online}",
                    'is_intervres' => "{$is_intervres}",
                    'runindex' => "{$runindex}",
                    'newgamelist' => $_POST['is_newgame'][$k],
                    'fen_lei'=>$fen_lei,
                    'ignore_sdk'=>$ignore,
                    'status' => $status
                );
                if($fen_lei=="单机(运营商)"){
                    $data['singletype'] = 1;
                }
                $a = '';
                if ($_POST['is_sdk'][$k] == 1) {
                    foreach ($_POST['pay'][$k] as $v) {
                        if ($a == '') {
                            $a = $v;
                        } else {
                            $a = $a | $v;
                        }
                    }
                }

                $data['sdk_fun'] = $a;
                $data['add_from'] = 2;
				if(isset($_POST['is_accept_sdk'])) $data['is_accept_sdk'] = $_POST['is_accept_sdk'][$k];
				if(isset($_POST['is_accept_account'])) $data['is_accept_account'] = $_POST['is_accept_account'][$k];
				if(isset($_POST['is_accept_pay'])) $data['is_accept_pay'] = $_POST['is_accept_pay'][$k];
				if($fen_lei=='网游'||$fen_lei=='棋牌'){ 
					$data['is_accept_sdk'] = 1;
					$data['is_accept_pay'] = 1;
				}
				if($fen_lei=='网游') $data['is_accept_account'] = 1;
                $res_add = $model->table('sj_soft_whitelist')->add($data);

                if($res_add){
                    if($is_sdk ==1){
                        update_soft_status(array('sdk_status'=>7), $package);
                    }
                }
                if ($pro) {
                    $da['p_fenlei'] = $fen_lei;
                    if ($fen_lei == '网游' || $fen_lei == '棋牌') {
                        $da['p_leixing'] = '';
                    }
                    $model->table('yx_product')->where(array('package' => $package))->save($da);
                }
                //$show_str.= '软件名：'.$softname.'&nbsp&nbsp包名：'.$package.'<br>';
            }
            
        } elseif ($sub_type == 'edit') {

            $softname = trim($_POST['softname'][0]);
            $package = trim($_POST['package'][0]);
            $edit_id = trim($_POST['edit_id']);
            $is_shelves = trim($_POST['is_shelves'][0]);
            $is_sdk = trim($_POST['is_sdk'][0]);
            $fen_lei = trim($_POST['fen_lei']);
            $is_intervres = trim($_POST['is_intervres'][0]);
            $ignore =  trim($_POST['ignore'][0]);
            $status = $_POST['status'][0];
            if ($is_intervres == 0) {
                $runindex = NULL;
            } else {
                $runindex = intval(trim($_POST['runindex']));
            }
            if($is_sdk[0]==1){
                if($fen_lei==''){
                     $this->error('请选择游戏类型');
                }
            }
            $runindex ? $runindex : $runindex = 0;
            if ($is_intervres == 1 && ($runindex < 0 || $runindex > 100)) {
                $this->error('干预搜索结果时，软件运营指数不能为空且大于0小于等于100');
            }
            if (empty($package)) {
                $this->error('请填写包名');
            }
            if (empty($softname)) {
                $this->error('请填写软件名');
            }
            if (empty($edit_id)) {
                //$this->error('无法获取开发者ID');
            }
            $data = array(
                'softname' => "{$softname}",
                'package' => "{$package}",
                'update_at' => "{$time}",
                'is_time_shelves' => "{$is_shelves}",
                'is_sdk' => "{$is_sdk}",
                'is_intervres' => "{$is_intervres}",
                'runindex' => $runindex,
                'newgamelist' => $_POST['is_newgame'][0],
                'fen_lei'=>$fen_lei,
                'ignore_sdk'=>$ignore
            );
            if($status) $data['status'] = $status;
            if($fen_lei=="单机(运营商)"){
                $data['singletype'] = 1;
            }else{
                $data['singletype'] = 0;
            }
			if(isset($_POST['is_accept_sdk'])) $data['is_accept_sdk'] = $_POST['is_accept_sdk'];
			if(isset($_POST['is_accept_account'])) $data['is_accept_account'] = $_POST['is_accept_account'];
			if(isset($_POST['is_accept_pay'])) $data['is_accept_pay'] = $_POST['is_accept_pay'];
            $a = '';
            if ($_POST['is_sdk'][0] == 1) {
                foreach ($_POST['pay'] as $v) {
                    if ($a == '') {
                        $a = $v;
                    } else {
                        $a = $a | $v;
                    }
                }
            }
            $data['sdk_fun'] = $a;
			$this->relevanceUcenter($edit_id,$data);
            $log_result = $this->logcheck(array('id'=>$edit_id),'sj_soft_whitelist',$data,$model);
            $res_edit = $model->table('sj_soft_whitelist')->where("id={$edit_id}")->save($data);
            $pro = $model->table('yx_product')->where(array('package'=>$package))->find();
            if($pro){
                $da['p_fenlei'] = $fen_lei;
                $model->table('yx_product')->where(array('package'=>$package))->save($da);
            }
            //$show_str.= '软件名：'.$softname.'&nbsp&nbsp包名：'.$package.'<br>';
        } else {
            $this->error('非法操作，请联系管理员1');
        }
        //echo $model->getLastSql(); exit;
        if ($res_add) {
            $this->writelog('添加了ID为' . $res_add . '运营白名单软件', 'sj_soft_whitelist',$res_add,__ACTION__ ,'','add');
            file_put_contents("/data/att/permanent_log/white_update.log", "添加了ID为".$res_add.'---'.print_r($data,true)."\n".date('Y-m-d H:i:s')."\n",FILE_APPEND);
            $this->success('添加成功！');
        } elseif ($res_edit) {
            $this->writelog('编辑了ID为' . $_POST['edit_id'] . '运营白名单软件'.$log_result, 'sj_soft_whitelist',$_POST['edit_id'],__ACTION__ ,'','edit');
            file_put_contents("/data/att/permanent_log/white_update.log", "编辑了ID为".$_POST['edit_id'].'---'.print_r($data,true)."\n".date('Y-m-d H:i:s')."\n",FILE_APPEND);
            $this->success('编辑成功！');
        } else {
            $this->error('非法操作，请联系管理员2');
        }
    }
    
	//将联运软件信息关联至用户中心
	private function relevanceUcenter($id,$data){
		$model = M('');
		$app_info = $model->table('sj_soft_whitelist')->where("id = '{$id}' and status = 1")->field('fen_lei,is_accept_account,is_accept_sdk,is_accept_pay')->find();
		$search_array = array('fen_lei','is_accept_sdk','is_accept_account');
		$search_array_info = array('游戏类型','是否接入SDK','是否接入账号');
		$relevance = false;
		$log_str = "游戏联运管理-运营白名单：ID为【{$id}】包名为【{$data['package']}】的";
        $log = false;
		foreach($search_array as $key=>$val){
			if($app_info[$val] != $data[$val]){
				if($data[$val]!=''){
					$log_str .= "【{$search_array_info[$key]}】由【{$app_info[$val]}】变为【{$data[$val]}】,";
                    $log = true;
				}
				$relevance = true;
			} 
		}
		if($log){
            $log_str = substr($log_str,0,-1);
            $this->writelog($log_str, 'sj_soft_whitelist',$id,__ACTION__ ,'','edit');
        }

		if($relevance){
			$relevance_data = array();
			$appkey = getAppKey($data['package']);
			$relevance_data['appKey'] = $appkey;
			if($data['fen_lei']=='单机'){
				$relevance_data['gameType'] = 0;
				if($data['is_accept_sdk']==1){
					$relevance_data['appType'] = 1;
				}else{
					$relevance_data['appType'] = 3;
				}
				if($data['is_accept_account']==1){
					$relevance_data['isJoinUcenter'] = 1;
				}else{
					$relevance_data['isJoinUcenter'] = 0;
				}
			}else if($data['fen_lei']=='网游'){
				$relevance_data['gameType'] = 1;
				$relevance_data['appType'] = 2;
				$relevance_data['isJoinUcenter'] = 1;
			}
			else if($data['fen_lei']=='棋牌'){
				$relevance_data['gameType'] = 2;
				$relevance_data['appType'] = 2;
				if($data['is_accept_account']==1){
					$relevance_data['isJoinUcenter'] = 1;
				}else{
					$relevance_data['isJoinUcenter'] = 0;
				}
			}
		}
		//var_dump($relevance_data);
		$res = json_decode(modifyAppNew($relevance_data),true);
		if($res['code']=='success'){
			return true;
		}else{
			return false;
		}
		
	}
    public function pub_help() {
        $tpl = 'help';
        $this->display($tpl);
    }

    public function feedback_authority() {
        $id_arr = explode(',', $_GET['id']);
        $model = new Model();
        $where = array(
            'id' => array('in', $id_arr),
            'status' => array('in',array(1,2))
        );
        $data = array(
            'feedback_type' => $_GET['feedback_type']
        );
        $ret = $model->table('sj_soft_whitelist')->where($where)->save($data);
        //echo $model->getlastsql();exit;
        if ($ret) {
            if ($_GET['feedback_type'] == 1) {
                $str = "开通";
            } else {
                $str = "关闭";
            }
            $this->writelog($str . "了id为" . $_GET['id'] . "的回复反馈权限", 'sj_soft_whitelist',$_GET['id'],__ACTION__ ,'','edit');
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }
	public function pub_open_url(){
		echo 'http://m.anzhi.com/download.php?package='.$_GET['package'];
	}
	public function edit_accept_ad_remark(){
         $model = new Model();

         if($_GET){
             $util = D("Sj.Util");
             $typelist = $util->getHomeExtentSoftTypeList();
            $res = $model ->table('sj_soft_whitelist')->where(array('package'=>trim($_GET['package']),'status'=>array('in',array(1,2))))->find();
             $this->assign('typelist',$typelist);
            $this->assign('res',$res);
            $this->display();
         }else{
            if($_POST){
                $where=array('package'=>trim($_POST['package']),'status'=>array('in',array(1,2)));
                $data=array(
                    'is_accept_ad' => $_POST['is_accept_ad'],
                    'ad_type' => $_POST['ad_type'],
                    'accept_ad_remark'=>trim($_POST['accept_ad_remark']),
                    'update_at'=>time()
                );
                if(!$_POST['is_accept_ad']){
                    if($this->check_ad_package($_POST['package'],time(),'',array('bs_new'=>1,'exclude_fea'=>1))) return;
                    $data['end_tm']=time();
                }else{
                   $is_promotion_time=trim($_POST['is_promotion_time']);
                   $start_tm=strtotime(trim($_POST['start_tm']));
                   $end_tm=strtotime(trim($_POST['end_tm']));
                   if($is_promotion_time==0){
                        $data['start_tm']=strtotime(date("Y-m-d",time()));
                        $data['end_tm']=strtotime(date("Y-m-d",time()))+16*86400-1;
                        if($this->check_ad_package($_POST['package'],$data['start_tm'],$data['end_tm'],2)) return;
                   }else if($is_promotion_time==1){
                        if($start_tm>$end_tm){
                            echo "结束日期不能早于开始日期";
                            return;
                        }
                        if(time()>$end_tm){
                            echo "结束日期不能早于当前时间";
                            return;
                        }
                        $data['start_tm']=$start_tm;
                        $data['end_tm']=$end_tm;
                        if($start_tm>time()) $data['is_accept_ad']=0;
                        if($this->check_ad_package($_POST['package'],$start_tm,$end_tm,2)) return;
                   }else if($is_promotion_time==2){
                        $data['start_tm']=time();
                        $data['end_tm']='';
                   }
                   $data['is_promotion_time']=$is_promotion_time;
                }
                $log_result = $this->logcheck($where,'sj_soft_whitelist',$data,$model);
                if(strpos($log_result,'结束时间')) $data['is_email'] = 0;
                $res = $model ->table('sj_soft_whitelist')->where($where)->save($data);
                if($res){
                    $this->writelog("游戏联运库,包名为{$_POST['package']}修改了广告库备注。{$log_result}",'sj_soft_whitelist',$_POST['package'],__ACTION__ ,"","edit");
                    echo 1;
                }else{
                    echo 2;
                }
            }else{
                echo 2;
            }
         }
    }
    public function check_ad_package($package,$start_tm,$end_tm,$bs,&$shield_error,$biaoshi){
        $AdSearch = D("Sj.AdSearch");
        $shield_error_new=$AdSearch->check_ad($package,$start_tm,$end_tm,'',$bs);
        if($shield_error_new){
            $shield_error.=$shield_error_new;
            if(!$biaoshi){
                 echo $shield_error_new;
            }
            return 1;
        }
    }
	//判断接入sdk状态是否能成是改成否
	public function check_change_sdk(){
		$package = $_POST['package'];
		$model = new Model();
		$where = array("status"=>2,"package"=>$package,'single_sdk'=>array('exp',array('2,3')));
		$res = $model ->table('sj_soft_tmp')->where($where)->find();
		if($res){
			echo '0';			
		}else{
			echo '1';
		}
	}
    //是否接入广告
    public function update_accept_ad(){
        $model = M('soft_whitelist');
        $id = $_GET['id'];
        $is_accept_ad = $_GET['is_accept_ad'];
        if($id && $is_accept_ad != ''){
            $data['is_accept_ad'] = $is_accept_ad;
            $data['update_at'] = time();
            $res = $model -> where(array('id'=>$id)) -> save($data);
            if($res){
                if($is_accept_ad==1){
                    $this->writelog("运营白名单：id为{$id}的成功接入广告");  
                }else if($is_accept_ad==0){
                    $this->writelog("运营白名单：id为{$id}的取消接入广告");  
                }
                echo 1;
                // $this->success('操作成功'); 
            }               
        }else{
            echo 2;
            // $this->error("参数错误");
        }
    }

    //批量导入游戏广告
    function edit_more_ad(){
        $model = M('');
        if($_FILES){
            $this->import_more_ad();
            exit();
        }
        $this->display();
    }

    function pub_update_ad_info(){
        $model = M('');
        $info = json_decode(base64_decode($_POST['info']),true);
        $id = explode(',',trim($_POST['id'],','));
        $id_str=$shield_error= '';
        $log = '批量修改游戏广告库:';
        //游戏联运库批量修改接入广告状态时,导出失败原因
        $file_dir="/tmp/shield_failure/";
        if(!is_dir($file_dir)){
            if(!mkdir(iconv("UTF-8", "GBK", $file_dir),0777,true)){
                jsmsg("创建目录失败", -1);
            }
        }
        $file_name="lianyunku.csv";
        $file_2=$file_dir.$file_name;
        if(file_exists($file_2)){
            unlink($file_2);
        }
        $out = fopen($file_2, 'w');
        fwrite($out,chr(0xEF).chr(0xBB).chr(0xBF)); 
        fputcsv($out,array('包名','位置','开始时间','结束时间','id','数据表名'));
        $import_count=0;
        foreach($info as $k=>$v){
            if(!in_array($v['id'], $id)){
                continue;
            }
            $data=array();
            if(!$_POST['is_accept_ad']){
                if($this->check_ad_package($v['package'],time(),'',array('bs_new'=>1,'exclude_fea'=>1,'whitelist_out_handle'=>$out),$shield_error,1)) continue;
                $data['end_tm']=time();
            }else{
               $is_promotion_time=trim($_POST['is_promotion_time']);
               $start_tm=strtotime(trim($_POST['start_tm']));
               $end_tm=strtotime(trim($_POST['end_tm']));
               if($is_promotion_time==0){
                    $data['start_tm']=strtotime(date("Y-m-d",time()));
                    $data['end_tm']=strtotime(date("Y-m-d",time()))+16*86400-1;
                    if($this->check_ad_package($v['package'],$data['start_tm'],$data['end_tm'],array('bs_new'=>2,'exclude_fea'=>1,'whitelist_out_handle'=>$out),$shield_error,1)) continue;
               }else if($is_promotion_time==1){
                    if($start_tm>$end_tm){
                        $shield_error.= "结束日期不能早于开始日期"."\n";
                        continue;
                    }
                    if(time()>$end_tm){
                        $shield_error.= "结束日期不能早于当前时间"."\n";
                        continue;
                    }
                    $data['start_tm']=$start_tm;
                    $data['end_tm']=$end_tm;
                    if($start_tm>time()){
                        $data['is_accept_ad']=0;
                    }
                    if($this->check_ad_package($v['package'],$data['start_tm'],$data['end_tm'],array('bs_new'=>2,'exclude_fea'=>1,'whitelist_out_handle'=>$out),$shield_error,1)) continue;
               }else if($is_promotion_time==2){
                    $data['start_tm']=time();
                    $data['end_tm']='';
               }
               $data['is_promotion_time']=$is_promotion_time;
            }
            $i_log = "id为{$v['id']}的";
            $data['is_accept_ad'] = $_POST['is_accept_ad'];
            if($v['beizhu']){
                $data['accept_ad_remark'] = $v['beizhu'];
            }
            if(count($data)>0){
                $data['update_at'] = time();
                $log_result = $this->logcheck(array('id'=>$v['id']),'sj_soft_whitelist',$data,$model);
                if(strpos($log_result,'结束时间')) $data['is_email'] = 0;
                $res = $model->table('sj_soft_whitelist')->where(array('id'=>$v['id']))->save($data);
                // echo $model->getLastSql();
                if($res){
                    $import_count++;
                    $id_str .= $v['id'].',';
                    $log .= "id为{$v['id']}".$log_result.'<br>';
                }
            }
        }
        if($log != '批量修改游戏广告库:'){
            $this->writelog($log, 'sj_soft_whitelist', $id_str,'/index.php/Dev/SoftWhitelist/edit_more_ad','','edit');
        }
        $count=count($id);
        if($out){
            fclose($out);
        }
        if($shield_error){
            $import_count_failure=$count-$import_count;
            $str="修改成功{$import_count}个,修改失败{$import_count_failure}个"."\n";
            echo json_encode(array('code'=>2,'msg'=>$str.$shield_error));
        }else{
            echo json_encode(array('code'=>1,'msg'=>"修改成功{$count}个"));return 1;
            echo 1;return 1;
        }
    }
    function pub_down_shield_csv() {
        header( 'Content-Type:text/html;charset=utf-8');  
        $file_dir = "/tmp/shield_failure/".$_GET['file_name'];
        if (file_exists($file_dir)) {
            $file = fopen($file_dir,"r");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($file_dir));
            Header("Content-Disposition: attachment; filename=" . "导入失败原因_".date('Y-m-d').'.csv');
            echo fread($file, filesize($file_dir));
            fclose($file);
            unlink($file_dir);
            exit(0);
        } else {
            echo "File not found!";
            exit;
        }

    }
    function import_more_ad(){
        $model = M('');
        list($package,$info,$repeat_pack) = $this->import_more_ad_do();
        if(count($package)>0){
            $whitelist_soft = get_table_data(array('package'=>array('in',$package),'status'=>array('in',array(1,2))),"sj_soft_whitelist",'package','id,package,softname,is_accept_ad,accept_ad_remark', '');
            // echo $model->getLastSql();
            $has_soft = array();
            if($whitelist_soft){
                foreach($whitelist_soft as $k=>$v){
                    $has_soft[] = $v['package'];
                }
                $fail_soft = array_filter(array_diff($package,$has_soft));

            }
            $soft_info = get_table_data(array('status'=>1,'hide'=>1,'package'=>array('in',$package)),"sj_soft",'package','softname,package','');
            // echo "<pre>";var_dump($info);
            foreach($whitelist_soft as $k=>$v){
                $v['beizhu'] = $info[$v['package']]['beizhu'];
                $v['o_softname'] = $soft_info[$v['package']]['softname'];
                $whitelist_soft[$k] = $v;
            }
            $num = count($whitelist_soft);
            $this->assign('num',$num);
            $this->assign('fail_soft',$fail_soft);
            // echo "<pre>";var_dump($whitelist_soft);die;
            $j_whitelist_soft = base64_encode(json_encode($whitelist_soft));
            $this->assign('j_whitelist_soft',$j_whitelist_soft);
            $this->assign('whitelist_soft',$whitelist_soft);
            $this->assign('repeat_pack',$repeat_pack);
        }

        $this->display('import_more_ad');
    }
    function import_more_ad_do(){
        $ad_file = $_FILES['ad_file'];
        if(!$ad_file['size']){
            $this->error('请上传文件');
        }
        $ext = pathinfo($ad_file['name']);
        if(strtolower($ext['extension'])!='csv'){
            $this->error('请上传csv格式文件');
        }
        $shili = fopen($ad_file['tmp_name'], "r");
        $package = $info = $repeat_pack = array();
        $str = '';
        while (!feof($shili)) {
            $shi = fgets($shili, 1024);
            $a = explode(',', $shi);
            if(count($a)>2){
                $this->error("激活码文件格式错误");
            }
            $str .= $shi . ",";
        }
        $str_arr = explode("\r\n", $str);
        foreach($str_arr as $key => $val){
            if(empty($val)||$val === ',,'){
                continue;
            }
            if($key==0){
                continue;
            }else{
                list($a,$pack,$beizhu) = explode(',',$val);
            }
            $beizhu = mb_convert_encoding($beizhu,"UTF-8","GBK");
            $package[] = trim($pack);
            if(isset($info[$pack])){
                $repeat_pack[] = $pack;
            }else{
                $info[trim($pack)]['beizhu'] = $beizhu;
            }

        }
        $repeat_pack = array_filter($repeat_pack);
        return array($package,$info,$repeat_pack);
    }

    /* 导出数据 */
    private function export_data() {
        $ids=json_decode($_POST['ids'],true);
        $datas=json_decode($_POST['data'],true);
        $data=array();
        if($ids){
            foreach($datas as $kk=>$vv){
                if(in_array($vv['id'], $ids)){
                    $data[]=$vv;
                }
            }
        }else{
            $data=$datas;
        }
        // echo "<pre>";var_dump($data);die;
        $data_new=$this->data_swith($data);
        header('Content-type: application/csv');
        // //下载显示的名字
        $file_name = date("Y-m-d").'.csv';
        
        header('Content-Disposition: attachment; filename=游戏库列表_"'.$file_name); 
        $out = fopen('php://output', 'w');
        fwrite($out,chr(0xEF).chr(0xBB).chr(0xBF)); 
        fputcsv($out,array('ID','包名','白名单软件名称','线上软件名称','游戏类型','联运','负责人','开发者','开发者类型','开发者邮箱','定时上架','是否已上线','添加流程','新游列表','是否干预搜索结果','接入广告','合作形式','接入广告开始时间','接入广告结束时间','接入广告备注','软件运营指数','更新时间'));
        foreach($data_new as $v) {
            fputcsv($out,$v);
        }
    }

    private function data_swith($data) {
        // echo "<pre>";var_dump($data);die;
        $util = D("Sj.Util");
        $typelist = $util->getHomeExtentSoftTypeList();
        $arr=array();
        foreach($data as $k=>$v){
            $arr[$k][0]=$v['id'];
            $arr[$k][1]=$v['package'];
            $arr[$k][2]=$v['softname'];
            $arr[$k][3]=$v['online_softname'];
            $str='';
            if($v['is_accept_sdk']==1){
                $str.='接SDK';
            }else{
                $str.='不接SDK';
            }
            if($v['is_accept_account']==1){
                if($str){
                    $str.=',接账号';
                }else{
                    $str.='接账号';
                }
            }else{
                if($str){
                    $str.=',不接账号';
                }else{
                    $str.='不接账号';
                }
            }
            if($v['is_accept_account']==1){
                if($str){
                    $str.=',接支付';
                }else{
                    $str.='接支付';
                }
            }else{
                if($str){
                    $str.=',不接支付';
                }else{
                    $str.='不接支付';
                }
            }
            $str_2=$v['p_fenlei']?$v['p_fenlei']:$v['fen_lei'];
            if($str_2){
                $arr[$k][4]=$str_2.','.($str?$str:'');
            }else{
                $arr[$k][4]=($str)?($str):'';
            }
            $arr[$k][5]=($v['is_sdk']==1)?'是':'否';
            $arr[$k][6]=$v['admin_name'];
            $arr[$k][7]=$v['dev_name'];
            $str='';
            if($v['type']==0){
                $str='公司';
            }else if($v['type']==1){
                $str='个人';
            }else{
                 $str='';
            }
            $arr[$k][8]=$str;
            $arr[$k][9]=$v['email'];
            $arr[$k][10]=($v['is_time_shelves']==0)?'否':'是';
            $arr[$k][11]=($v['is_online']==0)?'否':'是';
            $arr[$k][12]=($v['add_from']==1)?'自动':'人工';
            $arr[$k][13]=($v['newgamelist']==0)?'否':'是';
            $arr[$k][14]=($v['is_intervres']==0)?'否':'是';
            $arr[$k][15]=($v['is_accept_ad']==1)?'是':'否';
            if($v['ad_type']){
                $arr[$k][16]=$typelist[$v['ad_type']][0];
            }else{
                $arr[$k][16]='';
            }
            $arr[$k][17]=$v['start_tm']?date("Y-m-d H:i:s",$v['start_tm']):'' ;
            $arr[$k][18]=$v['end_tm']?date("Y-m-d H:i:s",$v['end_tm']):'' ;
            $arr[$k][19]=($v['accept_ad_remark'])?$v['accept_ad_remark']:'无';
            $arr[$k][20]=$v['runindex'];
            $arr[$k][21]=$v['update_at']?date("Y-m-d H:i:s",$v['update_at']):'';
        }
        return $arr;
    }

    function  pub_edit_rank(){
        $model = M('');
        if($_POST){
            $start_tm = $_POST['r_start_tm']?strtotime($_POST['r_start_tm']):0;
            $end_tm = $_POST['r_end_tm']?strtotime($_POST['r_end_tm']):0;
            if($start_tm>$end_tm){
                $this->error("开始时间不能大于结束时间");
            }
            $now = time();
            if($end_tm&&$end_tm<$now){
                $this->error("结束时间比当前时间小");
            }
            $s_data = array(
                'rank' => $_POST['rank'],
                'r_start_tm' => $start_tm,
                'r_end_tm' => $end_tm,
                'update_at' => $now
            );
            if($_POST['rank']!=0){
                $o_game = $model->table('sj_soft_whitelist')->where(array('newgamelist'=>1,'r_end_tm'=>array('exp'," > '{$now}'"),'id'=>array('exp'," != {$_POST['id']}"),'rank'=>$_POST['rank'],'status'=>1))->field('id,package,rank,r_start_tm,r_end_tm')->select();
                if($o_game){
                    $pack = array();
                    foreach($o_game as $key=>$val){
                        if(is_time_cross($val['r_start_tm'],$val['r_end_tm'],$start_tm, $end_tm)){
                            $pack[] = $val['package'];
                        }
                    }
                    if(count($pack)>0){
                        $pack_str = implode(',',$pack);
                        $msg = "该位置已被包名为{$pack_str}的游戏占用";
                        $this->error($msg);
                    }
                }
            }

            $s_res = $model->table('sj_soft_whitelist')->where(array('id'=>$_POST['id']))->save($s_data);
            if($s_res){
                $this->writelog("编辑了ID为{$_POST['id']}新游列表排序", 'sj_soft_whitelist',$_POST['id'],'/index.php/Dev/SoftWhitelist/EditWhitelist','','edit');
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }
        $id = $_GET['id'];
        $res = $model->table('sj_soft_whitelist')->where(array('id'=>$id))->field('id,rank,r_start_tm,r_end_tm')->find();
        $this->assign('res',$res);
        $this->assign('id',$id);
        $this->display("edit_rank");
    }

}
