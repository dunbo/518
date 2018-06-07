<?php

class Products2Action extends CommonAction {

    private $lists;
    private $hotfix_platform = 17;
    private $platform_map = array(
        15 => 1,
        17 => 0,
        18 => 11,
    );
    
    public function _initialize() {
        parent::_initialize();
        $this->assign("hotfix_platform", $this->hotfix_platform);
        $this->assign("platform_map", $this->platform_map);
    }
    //市场更新管理_市场查看列表
	function marketupdate2() {
        $model = D('Sj.Products2');
        
        // 根据get或post数据查询数据库并返回结果
        //$res = $model->getlist($_GET,$_POST);
        $res = $model->getlist($_REQUEST, 1);
        $list = $res['list'];
        
        // 查询sj_limit_rules限制规则
        //$limit_rules_model = M();
        // 对较长的字段进行处理：增加其对应短字段，以便在页面显示
        foreach($list as $k => $v) {
            $list[$k]['note_short'] = $this->chgtitle($v['note']);
            $list[$k]['remark_short'] = $this->chgtitle($v['remark']);
            $list[$k]['limit_rules_short'] = $this->chgtitle($list[$k]['limit_rules']);
            // 排除机型数字转名称
            $did_arr = explode(",", trim($list[$k]['exclude_did'], ','));
            $exclude_did_names = "";
            foreach($did_arr as $key => $did) {
                if ($key != 0) {
                    $exclude_did_names .= ",";
                }
                $dname_ret = $model->table('pu_device')->field('dname')->where(array('did'=>$did))->find();
                $exclude_did_names .= $dname_ret['dname'];
            }
            $list[$k]['exclude_did_names'] = $exclude_did_names;
        }
        // 将list里的渠道数字转换成名称
        // 获得整个渠道表的数字名称对应
        $Ch = M();
        $cid_chname = $Ch->table('sj_channel')->field('cid,chname')->select();
        $cid_cname_map = array();
        $cid_cname_map[0] = "通用";
        foreach($cid_chname as $value) {
            $cid_cname_map[$value['cid']] = $value['chname'];
        }
        // 开始数字转换成名称
        foreach($list as $k => $v) {
            $cid = $list[$k]['cid'];
            $list[$k]['cid_name'] = $cid_cname_map[$cid];
        }
        $this->assign("cid_cname_map", $cid_cname_map);
        
        // 获得平台列表
        $platform = '';
        if ($_REQUEST['platform']) {
            $platform = $_REQUEST['platform'];
        }
        $util = D('Sj.Util');
		$product_list = $util->getProducts($platform);
		$this->assign('product_list',$product_list);        
        
        //批量导出
        if (isset($_REQUEST['down'])) {
            // 得到选中的ids
            $ids = $_REQUEST['ids'];
            $ids_arr = explode(",", $ids);
        
            // 先将文件复制和生成到一个文件夹里
            $now = time();
            $folder = "/tmp/down_$now";
            
            // 限制规则
            //$model = M("");
            
            if(!file_exists($folder)) {
                mkdir($folder, 0777); 
                $allist_res = $model->getlist($_REQUEST, 1, 1);
                $allist = $allist_res['list'];
                foreach($allist as $key => $value) {
                    //$folder_1 = $folder."/".$key;
                    if (!in_array($value['id'], $ids_arr))
                        continue;
                    $folder_1 = $folder."/".$value['id'];
                    mkdir($folder_1);
                    $buf = "";
                    foreach($value as $k => $v) {
                        $attachment_host = "";
                        if ($k == "apkurl" || $k == "apkicon" || $k == "iconurl" || $k == "iconurl_96" || $k == "icon_original") {
                            if ($v) {
                                $attachment_host = ATTACHMENT_HOST;
                            }   
                        }
                        $buf .= $k . ":" . $attachment_host . $v . "\r\n";
                        if ($k == "apkurl") {
                            //将apk写到文件夹里
                            $apk_from = UPLOAD_PATH . $v;
                            $apk_name = array_pop(explode("/", $v));
                            //$apk_from = "D:/com.tencent.news.apk";
                            $buf_apk = file_get_contents($apk_from);
                            $apk_to = $folder_1 . "/". $apk_name;
                            $fp = fopen($apk_to, "w");
                            if ($fp) {
                                fwrite($fp, $buf_apk);
                                fclose($fp);
                            }
                        }
                    }
                    $txt = $folder_1 . "/". "info.txt";
                    $fp = fopen($txt, "w");
                    if ($fp) {
                        fwrite($fp, $buf);
                        fclose($fp);
                    }
                }
            }
            ini_set('memory_limit', '512M');
            import("@.ORG.PHPZip");
            $archive  = new PHPZip();
            $archive->ZipAndDownload("{$folder}");
            
        }
        
        $this->assign('list', $list);
        $this->assign('page', $res['page']);
        //$this->assign('sqlparam', $res['sqlparam']);
        
        // 将搜索条件返回
        $this->assign('version_code', $_REQUEST['version_code']);
        $this->assign('version_name', $_REQUEST['version_name']);
        $this->assign('cid', $_REQUEST['cid']);
        // 排除机型搜索
        $did_search_arr = array();
        foreach ($_REQUEST['did'] as $did) {
            $dname_ret = $model->table('pu_device')->field('dname')->where(array('did'=>$did))->find();
            $did_search_arr[$did] = $dname_ret['dname'];
        }
        $this->assign('did', $did_search_arr);
        $this->assign('remark', $_REQUEST['remark']);
        $this->assign('id', $_REQUEST['id']);
        $this->assign('force_update', $_REQUEST['force_update']);
        //$this->assign('platform', $_REQUEST['platform']);
        $this->assign('target_version_code', $_REQUEST['target_version_code']);
        $this->assign('type', $_REQUEST['type']);
        $this->assign('wifi_load', $_REQUEST['wifi_load']);
        
        $this->assign("apkurl",IMGATT_HOST);
        
        $this->display('marketupdate2');
    }
    
    //截取字符串
    function chgtitle($title){
        $length=9;
        $encoding='utf8';
        if(mb_strlen($title,$encoding)>$length){
            $title=mb_substr($title,0,$length,$encoding).'...';
        }
        return $title;
    }
    
    function show_remark() {
        if ($_GET) {
            //$model = D('Sj.Products2');
            //$record = $model->get_record_by_id($_GET['id']);
            //$this->assign('remark', $record['remark']);
            $model = D('Sj.Products2');
            $list = $model->get_record_by_id($_GET['id']);
            if ($_GET['type'] == 1)
                $remark = $list[0]['note'];
            else if ($_GET['type'] == 2)
                $remark = $list[0]['remark'];
            else
                $remark = $list[0]['limit_rules'];
            $this->assign('remark', $remark);
        }
        $this->display('show_remark');
    }

    function edit_limit_rules() {
        if ($_GET) {
            $this->assign('ready', $_GET['ready']);            
        }
        if ($_POST) {
            $ids_arr = explode(",", $_POST['ids']);
            $model = D('Sj.Products2');
            $last_refresh = time();
            foreach($ids_arr as $id) {
                $where = array();
                $where['id'] = $id;
                $data = array();
                $data['limit_rules'] = $_POST['rules'];
                $data['last_refresh'] = $last_refresh;
                $model->where($where)->save($data);
            }
            $this->writelog('市场更新管理-市场查看列表2-编辑限制规则-编辑了ids为['.$_POST['ids'].']，规则为['.$_POST['rules'].']','sj_market',$_POST['ids'],__ACTION__ ,"","edit");
            $this->success("编辑成功！");
        }
        $this->display('edit_limit_rules');
    }
    
    function search_rules() {
        if ($_POST) {
            $ids = $_POST['ids'];
            $ids_arr = explode(',', $ids);
            $rules_arr = array();
            $model = D('Sj.Products2');
            foreach($ids_arr as $id) {
                $map = array();
                $map['id'] = $id;
                $rule = $model->where($map)->field('limit_rules')->select();
                $arr = explode(',', $rule[0]['limit_rules']);
                foreach($arr as $value) {
                    if ($value)
                        $rules_arr[$value] = "";
                }
            }
            $rule_str = "";
            $i = 0;
            foreach($rules_arr as $key=>$value) {
                if ($i != 0)
                    $rule_str .= ',';
                $rule_str .= $key;
                $i++;
            }
            $this->ajaxReturn($rs,$rule_str, 0);
        }
    }
    
    function modify_in_use() {
        if ($_POST) {
            $ids = $_POST['ids'];
            $status = $_POST['status'];
            $ids_arr = explode(',', $ids);
            $model = D('Sj.Products2');
            $last_refresh = time();
            foreach($ids_arr as $id) {
                $model->modify_in_use($id, $status);
                $model->modify_last_refresh($id, $last_refresh);
            }
            if ($status == 0)
                $operation = "禁用";
            else
                $operation = "启用";
            $this->writelog('市场更新管理-市场查看列表2-更改状态-'. $operation . '了产品,ids：'.$ids,'sj_market',$ids,__ACTION__ ,"","edit");
            echo 1;exit(0);
        }
    }
    
    function add_market() {
        // 获得平台
        $util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts($platform));
        
        // 获得固件
        $Fo = M("pu_config");
		$firmware=$Fo->query('select * from pu_config  where status=1 and config_type="firmware" order by conf_id');
		//print_R($firmware);
		$len = count($firmware);
		for ($i=1;$i<=$len;$i++){
			if($i%3 == 0){
				$firmware[$i-1]['configcontent'] .= '<br />';
			}
		}
		$this->assign("firmware",$firmware);
        
        $this->display('add_market');
    }
    
    // 编辑_有效列表
    function edit_market() {
        $this->assign('edit_type', 1);
        $this->edit();
    }
    
    // 编辑_无效列表
    function edit_market_ineffective() {
        $this->assign('edit_type', 2);
        $this->edit();
    }
    
    private function edit() {
        // 获得平台
        $util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts($platform));
        
        // 获得固件
        $Fo = M("pu_config");
		$firmware=$Fo->query('select * from pu_config where status=1 and config_type="firmware"');
		$len = count($firmware);
		for ($i=1;$i<=$len;$i++){
			if($i%3 == 0){
				$firmware[$i-1]['configcontent'] .= '<br />';
			}
		}
		$this->assign("firmware",$firmware);
        
        $model = D('Sj.Products2');
        $list = $model->get_record_by_id($_GET['id']);
        $this->assign('list', $list[0]);
        
        $formware_arr = explode(',', $list[0]['firmware']);
        $this->assign("formware_arr", $formware_arr);
        
        //获得渠道名称
        $Ch = M();
        $cid = $list[0]['cid'];
        if ($cid == 0) {
            $cid_chname = array(array('cid' => '0', 'chname' => '通用'));
        } else {
            //$cid_chname = $Ch->query("select cid, chname from sj_channel where cid={$cid}");
            $map = array();
            $map['cid'] = array('EQ', $cid);
            $cid_chname = $Ch->table('sj_channel')->field('cid,chname')->where($map)->select();
        }
        if (!empty($cid_chname)) {
            $chname_map = array();
            foreach($cid_chname as $val) {
                $chname_map[$val['cid']] = $val['chname'];
            }
        
            $this->assign("cid", $cid);
            $this->assign("chname_map", $chname_map);
        }
        
        // 获得排除机型
        $Di = M();
        //$list[0]['exclude_did'] = trim($list[0]['exclude_did'], ',');
        //$did_in = '('.$list[0]['exclude_did'].')';
        //$did_dname = $Di->query("select did, dname from pu_device where did in {$did_in}");
        $map = array();
        $did_in = explode(",", trim($list[0]['exclude_did'], ','));
        $map['did'] = array('IN', $did_in);
        $did_dname = $Di->table('pu_device')->field('did,dname')->where($map)->select();
        if (!empty($did_dname)) {
            $dname_map = array();
            foreach($did_dname as $val) {
                $dname_map[$val['did']] = $val['dname'];
            }
            $did = explode(",", trim($list[0]['exclude_did'], ','));
            $this->assign("did", $did);
            $this->assign("dname_map", $dname_map);
        }
        
        $this->display('edit_market');
    }
    
    public function marketupdata_insert() {
        /*
        if ($this->isAjax()) {
            $this->check_apk_name();
            return;
        }
        */
        $model = D('Sj.Products2');
        $map['version_code'] = $_POST['version_code'];
		$map['version_name'] = $_POST['version_name'];
        $map['force_update'] = $_POST['force_update'];
		$map['min_firmware'] = $_POST['min_firmware'];
        if($_POST['cid']){
			$map['cid'] = $_POST['cid'];
		}else{
			$map['cid'] = 0;
		}
        $map['note'] = $_POST['note'];
        $map['remark'] = $_POST['remark'];
		$map['platform'] = $_POST['platform'];
        $map['type'] = $_POST['type'];
        $map['wifi_load'] = $_POST['wifi_load'];
		$map['priority_version_code'] = $_POST['priority_version_code'];
        
        if(empty($map['version_code']) || empty($map['version_name']) || empty($map['note'])) {
            $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，版本号、版本名称及更新描述必须填写');
        }
        if(strlen($$map['version_name'])>30) {
            $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，版本名称不得长于30个字符，10个汉字');
        }
        if(strlen($map['remark'])>3000) {
            $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，更新描述和备注不得长于3000个字符，500个汉字');
        }
        // 处理一下target_version_code
        $target_str = trim($_POST['target_version_code']);
        if ($target_str) {
            $target_tmp_arr = explode(',', $target_str);
            $target_arr = array();
            foreach ($target_tmp_arr as $value) {
                if ($value) {
                    $target_arr[] = $value;
                }
            }
            sort($target_arr);
            $target_str = ',' . implode(',', $target_arr) . ',';
        }
        $map['target_version_code'] = $target_str;
		$channel_where['_string'] = "cid = {$map['cid']} and status = 1";
		$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();
		$chl_cid = $channel_result[0]['chl_cid'];
		
		$file=$_FILES['apk']['tmp_name'];
		if($_POST['platform'] == 1 || $_POST['platform'] == 5 || $_POST['platform'] == 4|| $_POST['platform'] == 6)
		{
			//$need_dir = "assets/goapk.ini";
			$cmd = "unzip -o {$file} assets/goapk.ini -d /tmp/"; 
			$bb=shell_exec($cmd);
			$chcode = file_get_contents("/tmp/assets/goapk.ini");
			unlink("/tmp/assets/goapk.ini");
		}
		elseif($_POST['platform'] == 12)
		{
			//$need_dir = "assets/yz.ini";
			$cmd = "unzip -o {$file} assets/yz.ini -d /tmp/"; 
			$bb=shell_exec($cmd);
			$chcode = file_get_contents("/tmp/assets/yz.ini");
			unlink("/tmp/assets/yz.ini");
		}
        /*$aa = zip_open($_FILES['apk']['tmp_name']);
		while ($bb = zip_read($aa)) {
			$cc = zip_entry_name($bb);
			if($cc == $need_dir){
				if($ee = zip_entry_open($aa,$bb)){
					$dd = zip_entry_read($bb);
					zip_entry_close($bb);
				}			
			}
		}*/
		$common_channel = $this->getCommonChannel($_POST['platform']);
		//平台为安智市场、游戏客户端、lgtv、平板才进行chl_cid的code比对，其他平台的不需要
		if($_POST['platform']==1||$_POST['platform']==4||$_POST['platform']==5||$_POST['platform']==6)
		{
			if($_POST['cid'] == 0){
				if(!isset($common_channel[$chcode])){
					$this -> error("上传文件渠道编码跟所选择渠道的渠道编码不匹配");
				}
			}else{
				if($chl_cid != $chcode){
					$this -> error("上传文件渠道编码跟所选择渠道的渠道编码不匹配");
				}
			}
		}
        if ($_POST['platform'] == $this->hotfix_platform) {
            $map['real_version_code'] = $_POST['version_code'];
            $map['package'] = $_POST['package'];
        }
        if(!empty($_FILES['apk']['size'])) {
            // 判断上传的APK的md5在后台是否已存在
            $md5_file = md5_file($_FILES['apk']['tmp_name']);
            $where = array(
                'md5_file' => array('EQ', $md5_file),
                //'status' => array('EQ', 1),  //禁用之后也不能添加
            );
            $exists = $model->where($where)->find();
            if ($exists) {
                $this->error("APK已存在（后台存在相同md5值APK）");
            }
        	$path = date("Ym/d/");
            $apk_tmp_name = "tmp_" . time() . "_" . rand(10000000,99999999);
            $multi_config = array(
                'apk' => array(
                    'saveRule' => $apk_tmp_name
                 ),
            );
        	$config = array(
        		'savePath' => UPLOAD_PATH. '/apk/'. $path,
                'multi_config' => $multi_config,//先给个临时名
                'name_optimization' => 1,// 名字优化，1表示用package+time的命名方式
        	);
            if ($_POST['platform'] == $this->hotfix_platform) {
                $config = array(
                    'savePath' => UPLOAD_PATH. '/apk/'. $path,
                    'multi_config' => array(
                        'apk' => array(
                            'saveRule' => 'getmsec'
                        ),
                    ),//先给个临时名
                );                
            }
			
            $this->lists=$this->_uploadapk(false, $config);
            
        } else {
            $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，APK必须上传');
        }
        if ($_POST['platform'] == $this->hotfix_platform) {
            $map['iconurl'] = '/img/market/ic_app_default.png';
            $map['iconurl_96'] = '/img/market/ic_app_default.png';
            $map['apkurl'] = $this->lists[other][0]["dburl"];
            $map['md5_file'] = md5_file($this->lists[other][0]["url"]);
            $map['apksize'] = $this->lists[other][0]["size"];
        } else {
    		$map['iconurl'] = $this->lists[apk][0]["iconurl_db"];
            $map['iconurl_96'] = $this->lists[apk][0]["iconurl_db_96"];
    		$map['apkurl'] = $this->lists[apk][0]["apkurl_db"];
    		$map['md5_file'] = md5_file(UPLOAD_PATH.$this->lists[apk][0]["apkurl_db"]);
    		$map['apksize'] = $this->lists[apk][0]["size"];
    		$map['icon_original'] = $this->lists[apk][0]['apk_icon_db'];
            $map['real_version_code'] = $this->lists[apk][0]['versionCode'];
            $map['package'] = $this->lists[apk][0]['packagename'];
        }

        $map['status'] = 1;
		$map['submit_tm']=time();
		$map['last_refresh']=time();
        if ($_POST['firmware']) {
            sort($map['firmware']);
    		$map['firmware']=implode(',', $_POST['firmware']);
        }
        if ($_POST['did']) {
            $map['exclude_did'] = ',' . implode(',', $_POST['did']) . ',';
        } else {
            $map['exclude_did'] = "";
        }
        
        $idthumb = $model->add_data($map);
        if (!$idthumb) {//如果插入失败删除掉文件
            unlink(UPLOAD_PATH.'/'.$map['apkurl']);
            $this->error("数据保存失败！");
        }
		//渠道版本同步通知
		$ucenter = C('ucenter');
		$data_array = array(
			'data'=>array(
				'serviceId' =>$ucenter['client_serviceId'],
				'syncDataType' => 2,
				//'standAlone' => 1,
				'cid' => $_POST['cid'],
			),
		);	
		request_task(C('ucenter_api'),$data_array);		
		
        if ($_POST['type'] == 1 && $_POST['platform'] != $this->hotfix_platform) {
			$this->send_incremental($idthumb);
        } else {
            // 类型为初次时，不生成增量包
        }
		include_once SERVER_ROOT. '/tools/functions.php';
		// go_make_links(UPLOAD_PATH.$this->lists[apk][0]["apkurl_db"]);
		// go_make_links(UPLOAD_PATH.$this->lists[apk][0]["iconurl_db"]);
        
        $this->writelog('市场更新管理-市场查看列表2-上传了ID为['.$idthumb.']apkurl为['.$map['apkurl'].']的市场更新','sj_market',$idthumb,__ACTION__ ,"","add");
        $this->success("数据保存成功！");
        
    }
	
	private function send_incremental($idthumb) {
		$model = M();
		$new_row = $model->table('sj_market')->where(array('id' => $idthumb))->find();
		$market_update_info = $this->get_oid_new($new_row['real_version_code'], $new_row['cid'], $new_row['platform']);
		if (empty($market_update_info)) {
			return;
		}
		// 写增量表，并将状态置为0
		$high_oid_arr = $low_oid_arr = array();
		foreach ($market_update_info as $old_version_code => $id_arr) {
			foreach ($id_arr as $oid_id) {
				$old_row = $model->table('sj_market')->where(array('id' => $oid_id))->find();
				if ($old_version_code >= $new_row['priority_version_code']) {
					$high_oid_arr[] = $oid_id;
				} else {
					$low_oid_arr[] = $oid_id;
				}
				// 检查之前是否已有此增量记录，有则不插入
				$new_md5 = $new_row['md5_file'];
				$old_md5 = $old_row['md5_file'];
				
				$where = array(
					'new_md5' => $new_md5,
					'old_md5' => $old_md5,
				);
				$find = $model->table('sj_market_patch')->where($where)->find();
				if ($find) {
					continue;
				}
				$data = array(
					'new_market_id' => $idthumb,
					'old_market_id' => $oid_id,
					'new_md5' => $new_md5,
					'old_md5' => $old_md5,
					'new_version_code' => $new_row['real_version_code'],
					'old_version_code' => $old_version_code,
					'create_at' => time(),
					'update_at' => time(),
					'status' => 0,
				);
				$ret = $model->table('sj_market_patch')->add($data);
			}
		}
		$task_client = get_task_client();
		// 每次最多发50个oid
        $max_num = 50;
		$oid_count = count($high_oid_arr);
		for ($i = 0; $i < $oid_count; $i += $max_num) {
            $oid_arr = array_slice($high_oid_arr, $i, $max_num);
            $task_client->doHighBackground("market_incremental_update",json_encode(array('id'=>$idthumb,'oid'=>$oid_arr)));
        }
		$oid_count = count($low_oid_arr);
		for ($i = 0; $i < $oid_count; $i += $max_num) {
            $oid_arr = array_slice($low_oid_arr, $i, $max_num);
            $task_client->doBackground("market_incremental_update",json_encode(array('id'=>$idthumb,'oid'=>$oid_arr)));
        }
		// 如果有对应低版本，写日志，提示已发送数据给后台进程去生成增量更新包
		$oids = "";
		foreach($high_oid_arr as $oid) {
			$oids .= "{$oid},";
		}
		foreach($low_oid_arr as $oid) {
			$oids .= "{$oid},";
		}
		$log_str = "市场更新管理-市场查看列表2-新增：已发送数据到后台进程去生成增量更新包，高版本id：{$idthumb}，低版本ids：{$oids}";
		$this->writelog($log_str,'sj_market_patch',$ret,__ACTION__ ,"","add");
	}
	
	// 根据版本号、渠道号、平台，返回满足条件的低版本id数组，返回的二维数据key值为版本号
	private function get_oid_new($real_version_code, $cid, $platform) {
		$model = M();
		$oid_arr = array();
		
		$mini_version_code = $platform == 1 ? 6010 : ($platform == 5 ? 1100 : 0);
		if ($cid == 0) {
			// 先找cid=0且真实版本号小于当前真实版本号（同时大于4300）的记录
            // $ret1 = $model->table('sj_market')->field('id')->where("cid=0 and real_version_code<{$real_version_code}")->select();
            $map = array();
            $map['real_version_code'] = array(array('EGT', $mini_version_code), array('LT', $real_version_code), 'AND');
            $map['cid'] = array('EQ', 0);
            $map['platform'] = array('EQ', $platform);
            $ret1 = $model->table('sj_market')->where($map)->field('id,real_version_code')->select();
            // 再join表sj_channel找cid非0且非独立更新的记录
            $map = array();
            $map['real_version_code'] = array(array('EGT', $mini_version_code), array('LT', $real_version_code), 'AND');
            $map['sj_market.cid'] = array('NEQ', 0);
            $map['alone_update'] = array('EQ', 0);
            $map['sj_channel.platform'] = array('EQ', $platform);
            $ret2 = $model->table('sj_market')->join("left join sj_channel on sj_market.cid=sj_channel.cid")->field('sj_market.id, sj_market.real_version_code')
                ->where($map)->select();
            
            foreach($ret1 as $value) {
				$oid_real_version_code = $value['real_version_code'];
                $oid_arr[$oid_real_version_code][] = $value['id'];
            }
            foreach($ret2 as $value) {
				$oid_real_version_code = $value['real_version_code'];
                $oid_arr[$oid_real_version_code][] = $value['id'];
            }
		} else {
            // cid 不是通用的，则找渠道号一样的且真实版本号比当前记录的真实版本号小（同时大于4300）的记录
            //$ret = $model->query("select id from sj_market where real_version_code<{$real_version_code} and cid={$cid}");
            $map = array();
            $map['real_version_code'] = array(array('EGT', $mini_version_code), array('LT', $real_version_code), 'AND');
            $map['cid'] = array('EQ', $cid);
            $ret = $model->table('sj_market')->where($map)->field('id,real_version_code')->select();
            
            foreach($ret as $key => $value) {
				$oid_real_version_code = $value['real_version_code'];
                $oid_arr[$oid_real_version_code][] = $value['id'];
            }
        }
		// 对数组进行排序
		krsort($oid_arr, SORT_NUMERIC);
		
        return $oid_arr;
	}
    
    public function marketupdata_update() {
        $model = D('Sj.Products2');
        $map['version_code'] = $_POST['version_code'];
		$map['version_name'] = $_POST['version_name'];
		$map['force_update'] = $_POST['force_update'];
        $map['min_firmware'] = $_POST['min_firmware'];
        
        if($_POST['cid']){
			$map['cid'] = $_POST['cid'];
		}else{
			$map['cid'] = 0;
		}
        
        $map['note'] = $_POST['note'];
        $map['remark'] = $_POST['remark'];
		$map['platform'] = $_POST['platform'];
        $map['type'] = $_POST['type'];
        $map['wifi_load'] = $_POST['wifi_load'];
		$map['priority_version_code'] = $_POST['priority_version_code'];
        
		$channel_where['_string'] = "cid = {$map['cid']} and status = 1";
		$channel_result = $model -> table('sj_channel') -> where($channel_where) -> select();
		$chl_cid = $channel_result[0]['chl_cid'];
		
		$file=$_FILES['apk']['tmp_name'];
		if($_POST['platform'] == 1 || $_POST['platform'] == 5 || $_POST['platform'] == 4|| $_POST['platform'] == 6)
		{
			//$need_dir = "assets/goapk.ini";
			$cmd = "unzip -o {$file} assets/goapk.ini -d /tmp/"; 
			$bb=shell_exec($cmd);
			$chcode = file_get_contents("/tmp/assets/goapk.ini");
			unlink("/tmp/assets/goapk.ini");
		}
		elseif($_POST['platform'] == 12)
		{
			//$need_dir = "assets/yz.ini";
			$cmd = "unzip -o {$file} assets/yz.ini -d /tmp/"; 
			$bb=shell_exec($cmd);
			$chcode = file_get_contents("/tmp/assets/yz.ini");
			unlink("/tmp/assets/yz.ini");
		}

		//平台为安智市场、游戏客户端、lgtv、平板才进行chl_cid的code比对，其他平台的不需要
		if($_POST['platform']==1||$_POST['platform']==4||$_POST['platform']==5||$_POST['platform']==6)
		{
			$market_old=$model->table('sj_market')->where(array('id'=>$_POST['id']))->find();
			$common_channel = $this->getCommonChannel($_POST['platform']);
			if($_FIES['apk']['tmp_name'])
			{
				if($_POST['cid'] == 0){
					if(!isset($common_channel[$chcode])){
						$this -> error("上传文件渠道编码跟所选择渠道的渠道编码不匹配");
					}
				}else{
					if($chl_cid != $chcode){
						$this -> error("上传文件渠道编码跟所选择渠道的渠道编码不匹配");
					}
				}
			}
			else
			{
				if(trim($_POST['cid']))
				{
					$channel_result = $model -> table('sj_channel') -> where(array('cid'=>trim($_POST['cid']),'status'=>1)) -> find();
					$chl_cid = $channel_result['chl_cid'];
					$apk_url = UPLOAD_PATH.$market_old['apkurl'];
					$cmd = "unzip -o {$apk_url} assets/goapk.ini -d /tmp/"; //apk文件中的gopak.ini的路径
					$bb=shell_exec($cmd);
					$chcode_have = file_get_contents('/tmp/assets/goapk.ini');
					unlink("/tmp/assets/goapk.ini");
					if($chcode_have != $chl_cid)
					{
						$this -> error("修改的渠道编码跟已经上传文件渠道的渠道编码不匹配");
					}
				}
			}
		}
		
        if(empty($map['version_code']) || empty($map['version_name']) || empty($map['note'])) {
            if ($_POST['edit_type'] == 2)
                $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2_ineffective');
            else
                $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，版本号、版本名称及更新描述必须填写');
        }
        if(strlen($$map['version_name'])>30) {
            if ($_POST['edit_type'] == 2)
                $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2_ineffective');
            else
                $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，版本名称不得长于30个字符，10个汉字');
        }
        if(strlen($map['remark'])>3000) {
            if ($_POST['edit_type'] == 2)
                $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2_ineffective');
            else
                $this->assign('jumpUrl','/index.php/Sj/Products2/marketupdate2');
            $this->error('对不起，更新描述和备注不得长于3000个字符，500个汉字');
        }
        // 处理一下target_version_code
        $target_str = trim($_POST['target_version_code']);
        if ($target_str) {
            $target_tmp_arr = explode(',', $target_str);
            $target_arr = array();
            foreach ($target_tmp_arr as $value) {
                if ($value) {
                    $target_arr[] = $value;
                }
            }
            sort($target_arr);
            $target_str = ',' . implode(',', $target_arr) . ',';
        }
        $map['target_version_code'] = $target_str;        
        // 判断是否上传了新的apk
        $uploadNewAPK = false;
        if ($_POST['platform'] == $this->hotfix_platform) {
            $map['real_version_code'] = $_POST['version_code'];
            $map['package'] = $_POST['package'];
        }
        if(!empty($_FILES['apk']['size'])) {
            // 判断上传的APK的md5在后台是否已存在
            $md5_file = md5_file($_FILES['apk']['tmp_name']);
            $where = array(
                'md5_file' => array('EQ', $md5_file),
                //'status' => array('EQ', 1),
            );
            $exists = $model->where($where)->find();
            if ($exists) {
                $this->error("APK已存在（后台存在相同md5值APK）");
            }
            //APK上传
        	$path = date("Ym/d/");
            $apk_tmp_name = "tmp_" . time() . "_" . rand(10000000,99999999);
            $multi_config = array(
                'apk' => array(
                    'saveRule' => $apk_tmp_name
                 ),
            );
        	$config = array(
        		'savePath' => UPLOAD_PATH. '/apk/'. $path,
                'multi_config' => $multi_config,//先给个临时名
                'name_optimization' => 1,// 名字优化，1表示用package+time的命名方式
        	);

            if ($_POST['platform'] == $this->hotfix_platform) {
                $config = array(
                    'savePath' => UPLOAD_PATH. '/apk/'. $path,
                    'multi_config' => array(
                        'apk' => array(
                            'saveRule' => 'getmsec'
                        ),
                    ),//先给个临时名
                );                
            }
            $this->lists=$this->_uploadapk(false, $config);
            $uploadNewAPK = true;
            if ($_POST['platform'] == $this->hotfix_platform) {
                $map['iconurl'] = '/img/market/ic_app_default.png';
                $map['iconurl_96'] = '/img/market/ic_app_default.png';
                $map['apkurl'] = $this->lists[other][0]["dburl"];
                $map['md5_file'] = md5_file($this->lists[other][0]["url"]);
                $map['apksize'] = $this->lists[other][0]["size"];
            } else {
                $map['iconurl'] = $this->lists[apk][0]["iconurl_db"];
                $map['apkurl'] = $this->lists[apk][0]["apkurl_db"];
                $map['md5_file'] = md5_file(UPLOAD_PATH.$this->lists[apk][0]["apkurl_db"]);
                $map['apksize'] = $this->lists[apk][0]["size"];
                $map['icon_original'] = $this->lists[apk][0]['apk_icon_db'];
                $map['real_version_code'] = $this->lists[apk][0]['versionCode'];
                $map['package'] = $this->lists[apk][0]['packagename'];
                
            }

            
            include_once SERVER_ROOT. '/tools/functions.php';
            //go_make_links(UPLOAD_PATH.$this->lists[apk][0]["apkurl_db"]);
            //go_make_links(UPLOAD_PATH.$this->lists[apk][0]["iconurl_db"]);
        }
		// $map['submit_tm']=time();
		$map['last_refresh']=time();
        if ($_POST['firmware']) {
            sort($map['firmware']);
    		$map['firmware']=implode(',', $_POST['firmware']);
        }
        if ($_POST['did']) {
            $map['exclude_did'] = ',' . implode(',', $_POST['did']) . ',';
        } else {
            $map['exclude_did'] = "";
        }

        //$map['id'] = $_POST['id'];
        // 获得编辑信息，以方便写数据库
        $where_arr = array('id' => $_POST['id']);
        $table_name = 'sj_market';
        $column_arr = array();
        foreach($map as $key => $value) {
            $column_arr[$key] = $value;
        }
        $log_all_need = $this->logcheck($where_arr, $table_name, $column_arr, $model);
        $msg = "市场更新管理-市场查看列表2：编辑了id为{$_POST['id']}的记录：";
        $msg .= $log_all_need;
        // 准备写库
        $idthumb = $model->save_data($_POST['id'], $map);

        if ($idthumb === false) {
            //如果更新失败，删除掉文件
            unlink(UPLOAD_PATH.'/'.$map['apkurl']);
            $this->error("数据更新失败！");
        }
        if ($uploadNewAPK) {
            // 删除原有apk
            unlink(UPLOAD_PATH.'/'.$_POST['apkurl']);
            if ($_POST['type'] == 1 && $_POST['platform'] != $this->hotfix_platform) {
				$this->send_incremental($_POST['id']);
            } else {
                // 类型为初次时，不生成增量包
                $market_update_info = array();
            }
		}
        $this->writelog($msg,'sj_market',$_POST['id'],__ACTION__ ,"","edit");
        $this->success("数据更新成功！");
        
    }
    
    function check_apk_name() {
        if ($_POST) {
            $path = UPLOAD_PATH. '/apk/'. date("Ym/d/") . $_POST['apk_name'];
            if (file_exists($path)) {
                // 目录里已存在相同文件名的APK，不可以继续添加这个APK
                $this->ajaxReturn(NULL, "APK已存在", -1);
            } else {
                $this->ajaxReturn(NULL, "APK不存在", 0);
            }
        }
    }
    
    function import() {
        if ($_POST) {
            $tmp_name = $_FILES['upload']['tmp_name'];
            $tmp_houzhui = $_FILES['upload']['name'];
            $err = $_FILES["upload"]["error"];
            $tmp_arr = explode('.',$tmp_houzhui);
            $houzhui = array_pop($tmp_arr);
            if(strtoupper($houzhui)!='ZIP'){
                echo 2;exit(0);
            }
            $file_list = $this->check_zip($tmp_name);
            if (!$file_list) {
                echo 3;exit(0);
            }
            $ret = $this->process_zip($tmp_name, $file_list);
            if (!$ret) {
                echo 4;exit(0);
            }
            if (!empty($ret['ids_record'])) {
                $this->writelog('市场更新管理-市场查看列表2-导入了ids：['.implode(",",$ret['ids_record']).'].','sj_market',implode(",",$ret['ids_record']),__ACTION__ ,"","add");
            }
            $this->ajaxReturn($ret,'导入成功！', 1);
        }
        $this->display('import');
    }
    
    // 检查文件结构
    function check_zip($file) {
        $zip = zip_open($file);
        $file_list = array();
        if (!$zip)
            return false;
        $ret = true;
        while ($zip_entry = zip_read($zip)) {
            $zname = zip_entry_name($zip_entry);
            $zdir=dirname($zname);
            $file_count=substr_count($zname ,'/');
            if($file_count<=0 || $file_count>=3){
                $ret = false;
                zip_entry_close($zip_entry);
                break;
            }
            $zname_arr = explode("/",$zname);
            if ($file_count == 2 && $zname_arr[2]!="") {
                $tmp_arr = explode('.',$zname_arr[2]);
                $houzhui = array_pop($tmp_arr);
                if (array_key_exists($houzhui, $file_list[$zdir])) {
                    $ret = false;
                    zip_entry_close($zip_entry);
                    break;
                }
                $file_list[$zdir][$houzhui] = $zname_arr[2];
            }
            zip_entry_close($zip_entry);
        }
        if(!$ret) {
            zip_close($zip);
            return $ret;
        }
        foreach($file_list as $dir => $files) {
            if (count($files) != 2) {
                $ret = false;
                break;	
            }
            $houzhui_arr = array();
            foreach($files as $file) {
                $tmp_arr = explode('.',$file);
                $houzhui = array_pop($tmp_arr);
                $houzhui_arr[] = strtoupper($houzhui);
            }
            if (!(in_array("APK", $houzhui_arr) && in_array("TXT", $houzhui_arr))) {
                $ret = false;
                break;
            }
        }
        zip_close($zip);
        if (!$ret)
            return false;
        return $file_list;
    }
    
    function add_error_record($zdir, $msg, $apk_path) {
        $data_tmp = array();
        // 将目录名转码一下，因为用户上传的文件目录中有可能包含中文
        $data_tmp[0] = iconv("GBK", "UTF-8", $zdir);
        $data_tmp[1] = $msg;
        if ($apk_path)
            unlink($apk_path);
        return $data_tmp;
    }
    
    function process_zip($file, $file_list) 
	{
		$file_str = shell_exec("unzip -l {$file}|grep apk|awk '{print \$4}'");
		//显示apk文件所有路径
		$file_list = explode("\n", $file_str);
		
		$apk_list = array();
		foreach($file_list as $val) {
			if (preg_match('/apk$/', $val)) {
				$apk_list[] = $val;
			}
		}

		$subfix = uniqid();
		$dir ='/tmp/apk_tmp/'. $subfix; //存放路径
		//$dir = UPLOAD_PATH. '/apk/'. date("Ym/d");
		if (!is_dir($dir)) 
		{
			mkdir($dir, 0777, true);
		}
		$cmd = "unzip {$file} -d {$dir}"; //解压所有文件
		$aa=shell_exec($cmd);
		
        include_once SERVER_ROOT. '/tools/functions.php';
        $totalnum = $succnum = $failnum = 0;
        $info_saved = array();
        $failarr = array();
        $model = D('Sj.Products2');
        $faildata_tmp = array();
		
		// $ids_record数组存放导入成功的ids，以便写日志
        $ids_record = array();
        $ch_model = M();
		
		// 获得平台id信息并存在数组里
        $tmp_model = M();
        $list = $tmp_model->table('pu_product')->field("pid")->where("status=1")->select();
        $platform_id_arr = array();
        foreach($list as $record) {
            $platform_id_arr[] = $record['pid'];
        }
        // 获得固件id信息并存在数组里
        $list = $tmp_model->table('pu_config')->field("configname")->where("config_type='firmware' and status=1")->select();
        $firmware_id_arr = array();
        foreach($list as $record) {
            $firmware_id_arr[] = $record['configname'];
        }
		
		foreach($apk_list as $val) 
		{
			$cmd = "unzip -o {$dir}/{$val} assets/goapk.ini -d /tmp/"; //apk文件中的gopak.ini的路径
			$bb=shell_exec($cmd);
			$chcode = file_get_contents('/tmp/assets/goapk.ini');
			unlink("/tmp/assets/goapk.ini");
			//读取goapk文件的渠道编码
			
			
			$totalnum++;//计算总个数
			$apk_path1=$dir.'/'.$val; //apk的文件路径
			$file_size = filesize($apk_path1);
			
			$zdir=$apk_path1;
			// 检查文件是否存在
			/*$exists = file_exists($apk_path1);
			
			if ($exists) 
			{
				$failnum++;
				$failarr[] = $zdir;
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, "APK已存在！", NULL);
				continue;
			}*/

			// 获得apk信息
			$dir_apk = UPLOAD_PATH. '/apk/'. date("Ym/d/");
			if (!is_dir($dir_apk)) 
			{
				mkdir($dir_apk, 0777, true);
			}
			$info_tmp = get_apk_info($apk_path1);
			
			if (!$info_tmp) 
			{
				$failnum++;
				$failarr[] = $zdir;
				$faildata_tmp[] = $this->add_error_record($zdir, "获得APK信息失败，APK已损坏!", $apk_path1);
				continue;
			}
			// 得到apk的信息，重命名
		    $new_name = $dir_apk . $info_tmp['packagename'] . "_" . time() . "_" . rand(10000000,99999999) . ".apk";
			if (file_exists($new_name)) 
			{
				$failnum++;
				$failarr[] = $zdir;
				$faildata_tmp[] = $this->add_error_record($zdir, "APK重命名失败，存在相同命名的APK！", $apk_path1);
				continue;
			}
			if (rename($apk_path1, $new_name))
			{
				$apk_path1 = $new_name;
			}
			
			$zdir=$new_name;
			
			// 解压apk的icon
			list($msec, $sec) = explode(' ', microtime());
			$msec = substr($msec, 2);
			$package_name = $info_tmp['packagename'];
			$iconname = $package_name . '_'. $msec. '_0'. substr($info_tmp['icon'], strrpos($info_tmp['icon'], '.'));
			$iconname96 = $package_name . '_'. $msec. '_0'.'_96'.substr($info_tmp['icon_96'], strrpos($info_tmp['icon_96'], '.'));
			$iconpath = UPLOAD_PATH. '/icon/'. date("Ym/d/");
			if (!is_dir($iconpath))
				mkdir($iconpath, 0777, true);
				
			$iconurl = $iconpath. $iconname;
			$iconurl96 = $iconpath. $iconname96;
			
			$apk_icon_url = tempnam($iconpath, $package_name. "_");//创建临时文件
			$apk_icon_url .= substr($info_tmp['icon'], strrpos($info_tmp['icon'], '.'));
			$apk_icon_url_db = str_replace(UPLOAD_PATH, '', $apk_icon_url);
			
			$iconurl_db = str_replace(UPLOAD_PATH, '', $iconurl);
			$iconurl_db_96 = str_replace(UPLOAD_PATH, '', $iconurl96);
			
			copy($info_tmp['icon'], $iconurl);
			copy($info_tmp['icon_96'],$iconurl96);
			
			go_make_links($iconurl);
			go_make_links($iconurl96);
			
			# original icon
			copy($info_tmp['icon_original'], $apk_icon_url);
			go_make_links($apk_icon_url);
			
			if (!is_file($iconurl)) {
				$failnum++;
				$faildata_tmp[] = $this->add_error_record($zdir, "图标(${info_tmp['icon']} => ${iconurl})复制失败(1)。", $apk_path1);
				continue;
			   // echo "图标(${info_tmp['icon']} => ${iconurl})复制失败(1)。";
			   // exit;
			}
			if (!is_file($iconurl96)) {
				$failnum++;
				$faildata_tmp[] = $this->add_error_record($zdir, "图标(${info_tmp['icon_96']} => ${iconurl_96})复制失败(2)。", $apk_path1);
				continue;
			   // echo "图标(${info_tmp['icon_96']} => ${iconurl_96})复制失败(2)。";
			   // exit;
			}
			
			unlink($info_tmp['icon']);
			unlink($info_tmp['icon_original']);
			
			$info_tmp['icon'] = $iconurl;
			$info_tmp['iconurl_db'] = $iconurl_db;
			$info_tmp['iconurl_db_96'] = $iconurl_db_96;
			
			//splitfile($apk_path1, $apk_path2);
			go_make_links($apk_path1);

			
			//获得info信息
			$tmp_path = dirname($dir.'/'.$val);//info文件路径
			$info_path = $tmp_path. '/info.txt';
			$buf = file_get_contents($info_path);//读取文件info数据
			
			// 去除bom头
			$buf = $this->removeBom($buf);
			// 转换编码
			$buf = $this->convert_encoding($buf);
			$buf = $info_saved[$zdir]['apkurl'] . "\r\n" . $buf;
			//将\r\n换成\n,加入apk地址，写数据库
			$buf = str_replace("\r\n","\n",$buf);
			$buf_arr = explode("\n", $buf);
			$pattern_update = '/info:{([^{}]+)}/';
			preg_match($pattern_update, $buf, $matches_update);
			$pattern_remark = '/remark:{([^{}]+)}/';
			preg_match($pattern_remark, $buf, $matches_remark);
	

			//写库
			$map = array();
			$map['iconurl'] = $info_tmp["iconurl_db"];
			$map['iconurl_96'] = $info_tmp["iconurl_db_96"];
			$map['apkurl'] = str_replace(UPLOAD_PATH, '', $apk_path1);
		   
			$map['icon_original'] = str_replace(realpath(UPLOAD_PATH),'',$apk_icon_url_db);
			$map['real_version_code'] = $info_tmp['versionCode'];
		
			$map['md5_file'] = md5_file($apk_path1);
            $map['package'] = $package_name;
			// 检查apk的md5是否与后台记录有重复，如果有，则提示失败，并删除文件
			$where = array(
				'md5_file' => array('EQ', $map['md5_file']),
				//'status' => array('EQ', 1), //禁用之后不能添加
			);
			$exists = $model->where($where)->find();
			if ($exists) 
			{
				$failnum++;
				$failarr[] = $zdir;
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, "APK已存在（后台存在相同md5值APK）!", $apk_path1);
				continue;
			}
		   
			$map['apksize'] = $file_size;
			$map['platform'] = trim($buf_arr[1]);
			// 判断platform
			if ($map['platform'] === "" || !in_array($map['platform'], $platform_id_arr))
			{
				$failnum++;
				if ($map['platform'] === "")
					$tmp_msg = "平台id不能为空！";
				else
					$tmp_msg = "平台id: " . $map['platform'] . " 不存在！";
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, $tmp_msg, $apk_path1);
				continue;
			}

			$map['cid'] = trim($buf_arr[2]);
			// 导入的version_code统一设置为1，忽视txt里的设置值
			$map['version_code'] = 1;
			$map['version_name'] = trim($buf_arr[4]);
			//fix, read db
			if (trim($buf_arr[5]) == "all")
				$map['firmware'] = "3,4,5,6,7,8,9,10,11,12,13,14";
			else {
				$map['firmware'] = trim($buf_arr[5]);
				// 判断是否为空
				//if ($map['firmware'] === "") {
				//	$failnum++;
					// 将文件夹名记录在错误信息中
				//	$faildata_tmp[] = $this->add_error_record($zdir, "固件id不能为空！", $apk_path1);
				//	continue;
				//}
				// 判断固件输入是否正确
				$tmp_arr = explode(",", $map['firmware']);
				$tmp_arr = array_unique($tmp_arr);
				$wrong = false;
				foreach($tmp_arr as $id) {
					if (!in_array($id, $firmware_id_arr)) {
						$wrong = true;
						break;
					}
				}
				if ($wrong) {
					$failnum++;
					// 将文件夹名记录在错误信息中
					$faildata_tmp[] = $this->add_error_record($zdir, "固件id填写有误！", $apk_path1);
					continue;
				}
			}
			$map['exclude_did'] = trim($buf_arr[6]);
			if ($map['exclude_did']) {
				$tmp_arr = explode(",", $map['exclude_did']);
				$tmp_arr = array_unique($tmp_arr);
				$wrong = false;
				foreach($tmp_arr as $id) {
					// 查此排除机型id是否存在
					$find = $tmp_model->table('pu_device')->where(array('did'=>$id, 'status'=>1))->find();
					if (!$find) {
						$wrong = true;
						break;
					}
				}
				if ($wrong) {
					$failnum++;
					// 将文件夹名记录在错误信息中
					$faildata_tmp[] = $this->add_error_record($zdir, "排除机型id填写有误！", $apk_path1);
					continue;
				}
			}
			$map['force_update'] = trim($buf_arr[7]);
			if (empty($map['force_update']))
				$map['force_update'] = 0;
			if ($map['force_update'] != 0 && $map['force_update'] != 1) {
				$failnum++;
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, "强制更新填写有误，请填写0或1！", $apk_path1);
				continue;
			}
			// 处理一下target_version_code
			$target_str = trim($buf_arr[8]);
			if ($target_str) {
				// 检查填写是否正确
				$reg = "/^,(\d+\s*,)*\d+,$/";
				if (preg_match($reg, $target_str)) {
					$failnum++;
					// 将文件夹名记录在错误信息中
					$faildata_tmp[] = $this->add_error_record($zdir, "针对版本号只能填写数字与英文','分隔，并且开始和结尾处请加英文','！", $apk_path1);
					continue;
				}
				$target_tmp_arr = explode(',', $target_str);
				$target_arr = array();
				foreach ($target_tmp_arr as $value) {
					if ($value) {
						$target_arr[] = $value;
					}
				}
				sort($target_arr);
				$target_str = ',' . implode(',', $target_arr) . ',';
			}
			$map['target_version_code'] = $target_str;
			$map['type'] = trim($buf_arr[9]);
			if (empty($map['type']))
				$map['type'] = 1;
			if ($map['type'] != 1 && $map['type'] != 2) {
				$failnum++;
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, "类型填写有误，请填写1或2！", $apk_path1);
				continue;
			}
			$map['wifi_load'] = trim($buf_arr[10]);
			if (empty($map['wifi_load']))
				$map['wifi_load'] = 2;
			if ($map['wifi_load'] != 1 && $map['wifi_load'] != 2) {
				$failnum++;
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, "Wi-Fi下是否自动下载填写有误，请填写1或2！", $apk_path1);
				continue;
			}
			//$map['note'] = iconv('gbk','utf-8',$matches_update[1]);
			//$map['remark'] = iconv('gbk','utf-8',$matches_remark[1]);
			$map['note'] = $matches_update[1];
			if (empty($map['note'])) {
				$failnum++;
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, "更新描述不能为空！", $apk_path1);
				continue;
			}
			$map['remark'] = $matches_remark[1];
			$map['submit_tm']=time();
			$map['last_refresh']=time();
			$map['status']=1;
            if (preg_match('/min_firmware:\{([^\}]+)\}/', $buf, $mf_match)) {
                $map['min_firmware'] = $mf_match[1];
            } else {
                $map['min_firmware'] = 14;
            }
			
			// 判断platform与cid是否一致
			if ($map['cid'] != 0) {
				$where = array();
				$where['cid'] = array('EQ', $map['cid']);
				$ret = $ch_model->table('sj_channel')->where($where)->select();
				if (!$ret || $map['platform'] != $ret[0]['platform']) {
					$failnum++;
					if (!$ret)
						$tmp_msg = "渠道: " . $map['cid'] . " 不存在！";
					else
						$tmp_msg = "平台和渠道不匹配！";
					// 将文件夹名记录在错误信息中
					$faildata_tmp[] = $this->add_error_record($zdir, $tmp_msg, $apk_path1);
					continue;
				}
			}
			//平台为安智市场、游戏客户端、lgtv、平板才进行chl_cid的code比对，其他平台的不需要
			if($map['platform']==1||$map['platform']==4||$map['platform']==5||$map['platform']==6)
			{
				$common_channel = $this->getCommonChannel($map['platform']);
				if($map['cid']==0)
				{
					if(!isset($common_channel[$chcode]))
					{
						$failnum++;
						$tmp_msg = "上传文件apk渠道编码跟txt渠道". $map['cid'] ."的渠道编码不匹配";
						// 将文件夹名记录在错误信息中
						$faildata_tmp[] = $this->add_error_record($zdir, $tmp_msg, $apk_path1);
						continue;
					}
				}
				else
				{
					$where['cid'] = array('EQ', $map['cid']);
					$ret = $ch_model->table('sj_channel')->where($where)->find();
					$txt_chl_cid=$ret['chl_cid'];
					if($chcode != $txt_chl_cid)
					{
						$failnum++;
						$tmp_msg = "上传文件apk渠道编码跟txt渠道". $map['cid'] ."的渠道编码不匹配";
						// 将文件夹名记录在错误信息中
						$faildata_tmp[] = $this->add_error_record($zdir, $tmp_msg, $apk_path1);
						continue;
					}	
				}
			}

			$idthumb = $model->add_data($map);
			if (!$idthumb) {
				$failnum++;
				$tmp_msg= "添加失败, sql: " . $model->getlastsql();
				// 将文件夹名记录在错误信息中
				$faildata_tmp[] = $this->add_error_record($zdir, $tmp_msg, $apk_path1);
			} else {
				$succnum++;
				// 记录成功导入的id，以便写log
				$ids_record[$idthumb]['real_version_code'] = $map['real_version_code'];
				$ids_record[$idthumb]['cid'] = $map['cid'];
				$ids_record[$idthumb]['platform'] = $map['platform'];
			}
			
		}
        // 成功的记录调后台程序生成增量包
        foreach($ids_record as $idthumb => $record) {
			$this->send_incremental($idthumb);
        }
        $succ_ids_arr = array_keys($ids_record);
        // 失败记录写库
        $sj_market_importfail = M("market_importfail");
        $thistime = time();
        $data = array();
        //$data['desc'] = iconv('gbk','utf-8',serialize($faildata_tmp));
        $data['desc'] = serialize($faildata_tmp);
        $data['uncreatetime'] = $thistime;
        $sj_market_importfail->add($data);
        
        $ret = array(
            'totalnum' => $totalnum,
            'succnum' => $succnum,
            'failnum' => $failnum,
            'thistime' => $thistime,
            'ids_record' => $succ_ids_arr,
        );
        
        return $ret;
   	}
    
    /**
     *  导出失败名单
     */	
    function downfail() {
        $thistime = $_GET['thistime'];
        if($thistime) {
            $model = M("market_importfail");
            $desc = $model->field("`desc`")->where("uncreatetime='$thistime'")->find();
            $list = unserialize($desc['desc']);
            header("Content-type:application/vnd.ms-excel");
            header("content-Disposition:filename=fail.csv");

            $desc ="失败文件夹,失败原因\r\n";
            //$desc = iconv('utf-8','gbk',$desc);
            foreach($list as $v) {
                $reason = "\"" . $v[1] . "\"";
                $desc = $desc.$v[0].','.$reason."\r\n";
            }
            $desc = iconv('utf-8','gbk',$desc);
            echo $desc;
            exit(0);
        }
    }
    
    //市场更新管理_市场查看列表_无效列表
	function marketupdate2_ineffective() {
        $model = D('Sj.Products2');
        // 根据get或post数据查询数据库并返回结果
        //$res = $model->getlist($_GET,$_POST);
        $res = $model->getlist($_REQUEST, 2);
        
        // 查询sj_limit_rules限制规则
        //$limit_rules_model = M();
        
        $list = $res['list'];
        foreach($list as $k => $v) {
            $list[$k]['note_short'] = $this->chgtitle($v['note']);
            $list[$k]['remark_short'] = $this->chgtitle($v['remark']);
            $list[$k]['limit_rules_short'] = $this->chgtitle($list[$k]['limit_rules']);
            // 排除机型数字转名称
            $did_arr = explode(",", trim($list[$k]['exclude_did'], ','));
            $exclude_did_names = "";
            foreach($did_arr as $key => $did) {
                if ($key != 0) {
                    $exclude_did_names .= ",";
                }
                $dname_ret = $model->table('pu_device')->field('dname')->where(array('did'=>$did))->find();
                $exclude_did_names .= $dname_ret['dname'];
            }
            $list[$k]['exclude_did_names'] = $exclude_did_names;
        }
        
        // 将list里的渠道、排除机型数字转换成名称
        // 获得整个渠道表的数字名称对应
        $Ch = M();
        $cid_chname = $Ch->table('sj_channel')->field('cid,chname')->select();
        $cid_cname_map = array();
        $cid_cname_map[0] = "通用";
        foreach($cid_chname as $value) {
            $cid_cname_map[$value['cid']] = $value['chname'];
        }
        // 开始数字转换成名称
        foreach($list as $k => $v) {
            $cid = $list[$k]['cid'];
            $list[$k]['cid_name'] = $cid_cname_map[$cid];
        }
        $this->assign("cid_cname_map", $cid_cname_map);
        
        // 获得平台列表
        $platform = '';
        if ($_REQUEST['platform']) {
            $platform = $_REQUEST['platform'];
        }
        $util = D('Sj.Util');
		$this->assign('product_list',$util->getProducts($platform));
        
        $this->assign('list', $list);
        $this->assign('page', $res['page']);
        
        // 将搜索条件返回
        $this->assign('version_code', $_REQUEST['version_code']);
        $this->assign('version_name', $_REQUEST['version_name']);
        $this->assign('cid', $_REQUEST['cid']);
        // 排除机型搜索
        $did_search_arr = array();
        foreach ($_REQUEST['did'] as $did) {
            $dname_ret = $model->table('pu_device')->field('dname')->where(array('did'=>$did))->find();
            $did_search_arr[$did] = $dname_ret['dname'];
        }
        $this->assign('did', $did_search_arr);
        $this->assign('remark', $_REQUEST['remark']);
        $this->assign('id', $_REQUEST['id']);
        $this->assign('force_update', $_REQUEST['force_update']);
        //$this->assign('platform', $_REQUEST['platform']);
        $this->assign('target_version_code', $_REQUEST['target_version_code']);
        $this->assign('type', $_REQUEST['type']);
        $this->assign('wifi_load', $_REQUEST['wifi_load']);
        $this->assign("apkurl",IMGATT_HOST);
        
        $this->display('marketupdate2_ineffective');
    }
    
    // 自动识别编码，并转换到指定编码
    function convert_encoding($string,$encoding = 'UTF-8') {
        $is_utf8 =  preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]| [\xC2-\xDF][\x80-\xBF]|  \xE0[\xA0-\xBF][\x80-\xBF] | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}    |  \xED[\x80-\x9F][\x80-\xBF] |  \xF0[\x90-\xBF][\x80-\xBF]{2}  | [\xF1-\xF3][\x80-\xBF]{3}  |  \xF4[\x80-\x8F][\x80-\xBF]{2} )*$%xs', $string);           
        if ($is_utf8 && $encoding == 'utf8') {
            return $string;
        } elseif ($is_utf8) {
            return mb_convert_encoding($string, $encoding, "UTF-8");
        } else {
            return mb_convert_encoding($string, $encoding, 'gbk,gb2312,big5');
        }
    }
    
    // 去掉字符串里的bom头
    function removeBom($str) {
        $bom = array('efbbbf');//handle utf8's bom now
        $str = bin2hex($str);  
        foreach($bom as $v) {
            $str = preg_replace('/^'.$v.'/', '', $str);
        }
        $str = pack("H*",$str);
        return $str;
    }
	
	function getCommonChannel($platform) 
	{
		$common_channel = array();
		if($platform == 1){
			$common_channel = array(
				'94d546eb3' => 1,
				'6048e1e61499' => 1,
				'fd7332111189' => 1,
			);
		}elseif($platform == 5){
			$common_channel = array(
				'6048e1e61499' => 1,
				'c411bafe3266' => 1,
			);			
		}elseif($platform == 12){
			$common_channel = array(
				'5f3a89633267' => 1,
			);
		}
		return $common_channel;
	}
}

?>
