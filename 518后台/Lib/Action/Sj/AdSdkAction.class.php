<?php

/*
 * 通用广告sdk
 */

class AdSdkAction extends CommonAction {

    public function index() {
		$where = array('status' => 1);
		if(!isset($_GET['status'])){		
			$status = 2;
		}else{
			$status = $_GET['status'];
		}
		if($status == 1){
			$where['start_at'] = array('GT',time());
		}else if($status == 2){
			$where['start_at'] = array('ELT',time());
			$where['end_at'] = array('EGT',time());
		}else if($status == 3){
			$where['end_at'] = array('LT',time());
		}
		$this->assign('status',$status);
        $ad_type = C('ad_type');
        $ad_content_type = C('ad_content_type');
        $model = M('');
        $this->assign('ad_type', $ad_type);
        $total = $model->table('sdk_ad')->where($where)->count();
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
        import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $result = $model->table('sdk_ad')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('create_at desc')->select();
        foreach ($result as $key => $val) {
            $result[$key]['ad_type'] = $ad_type[$val['type']];
            $result[$key]['content_type'] = $ad_content_type[$val['content_type']];
			if(!empty($result[$key]['channel_id'])){
				$channel_name = $model->table('sj_channel')->where(array('cid'=>array('in',$result[$key]['channel_id'])))->field('chname')->select();
				foreach($channel_name as $k=>$v){
					if($k+1==count($channel_name)){
						$result[$key]['channel_name'] .= $v['chname'];
					}else{
						$result[$key]['channel_name'] .= $v['chname'].',';
					}
				}
			}
			if($val['priority'] ==0)
			{
				$result[$key]['priority_show'] ="";
			}
			else
			{
				$result[$key]['priority_show'] =$val['priority'];
			}
			
        }
        $this->assign('page', $Page->show());
        $this->assign('total', $total);
        $this->assign('result', $result);
        $this->display();
    }

    /*
     * 添加广告，推送
     * $_POST['push'] 1推送
     */
    public function add_ad() {
        $insert_data = array();
        if($_POST['push']!="1"){
            if (!isset($_POST['c_ad_type']) || empty($_POST['c_ad_type'])) {
                $this->error('请选择广告类型');
            } else {
                $insert_data['type'] = $_POST['c_ad_type'];
            }
        }
        
        if ($_POST['c_ad_type'] != 2) {
            if (!isset($_POST['content_type']) || empty($_POST['content_type'])) {
                $this->error('请选择广告数据类型');
            } else {
                $insert_data['content_type'] = $_POST['content_type'];
            }
        }
        if ($_POST['c_ad_type'] == 2 || $_POST['c_ad_type'] == 3|| $_POST['push'] ==1) {
            if (!isset($_POST['title']) || empty($_POST['title'])) {
                $this->error('请填写标题');
            } else {
                $insert_data['title'] = $_POST['title'];
            }
        }
		//覆盖人数
		if($_FILES['upload_file']['size'])
		{
			$filename=$_FILES['upload_file']['name'];
			if(!$filename&&!$_POST['csv_count'])
			{
				$insert_data['imei_file_path'] = "";
			}
			if($filename&&!$_POST['csv_count'])
			{
				$this->error("选择好的文件请点击上传才有效");
			}
			if($_POST['csv_count']&&$_POST['csv_url'])
			{
				$insert_data['imei_file_path'] = $_POST['csv_url'];
			}
			unset($_FILES['upload_file']);
		}
		//优先级
		if($_POST['push']!="1")
		{
			if($_POST['priority'])
			{
				$model = new model();
				$where_have = array(
					'start_at' => array('elt',strtotime($_POST['end_at'])),
					'end_at' => array('egt',strtotime($_POST['start_at'])),
					'priority' => $_POST['priority'],
					'status' =>1,
				);
				if($_POST['edit']) 
				{
					$id = $_POST['id'];
					$where_have['_string'] .= "id !={$id}";
				} 
				$ret = $model->table('sdk_ad')->where($where_have)->find(); 
				if($ret)
				{
					$this->error('同一排期内优先级重复，请重新填写');
				}
				else
				{
					$insert_data['priority'] = $_POST['priority'];
				}
			}
			else
			{
				$this->error('广告优先级必填');
			}
		}
        if ($_POST['c_ad_type'] == 5) {
            if (!isset($_POST['banner_type']) || empty($_POST['banner_type'])) {
                $this->error('请选择banner类型');
            } else {
                $insert_data['banner_type'] = $_POST['banner_type'];
            }
        }
        if ($_POST['c_ad_type'] == 3 || $_POST['c_ad_type'] == 4 || $_POST['c_ad_type'] == 6|| $_POST['push'] ==1) {
            $insert_data['description'] = $_POST['description'];
        }
        if ($_POST['c_ad_type'] == 1 || $_POST['c_ad_type'] == 4 || $_POST['c_ad_type'] == 5 || $_POST['push'] ==1) {
            if ($_FILES) {
                if($_POST['edit']!=1){
                    if ($_POST['c_ad_type'] == 1) {
                        if ($_FILES['landscape_img']['name'] == '') {
                            $this->error('请上传闪屏横屏图片');
                        }
                        $img_extension = pathinfo($_FILES['landscape_img']['name']);
                        if ($img_extension['extension'] != 'jpg') {
                            $this->error('闪屏横屏图片格式错误');
                        }
                        if ($_FILES['portrait_img']['name'] == '') {
                            $this->error('请上传闪屏竖屏图片');
                        }
                        $img_extension1 = pathinfo($_FILES['portrait_img']['name']);
                        if ($img_extension1['extension'] != 'jpg') {
                            $this->error('闪屏竖屏图片格式错误');
                        }
                    } else {
						if($_POST['push']!="1"){
							if ($_FILES['img']['name'] == '') {
								$this->error('请上传图片');
							}
						}
                        if ($_FILES['img']['name'] != '') {
							$img_extension = pathinfo($_FILES['img']['name']);
							if ($img_extension['extension'] != 'jpg') {
								$this->error('图片格式错误');
							}	
						}
                    }
                }
                
                $upload = true;
                foreach($_FILES as $k=>$v){
                    if($v['name']==''){
                        unset($_FILES[$k]);
                    }
                }

                //上传图片
                $data = $this->upload_file($_FILES);
                if ($_POST['c_ad_type'] == 1) {
                    if(isset($data['landscape_img'])){
                        $insert_data['landscape_img'] = $data['landscape_img'];
                    }
                    if(isset($data['portrait_img'])){
                        $insert_data['portrait_img'] = $data['portrait_img'];
                    }  
                } else {
                    if(isset($data['img'])){
                        $insert_data['img'] = $data['img'];
                    }
                }                    
            }
        }
        if($_POST['push']!="1"){
            if (!isset($_POST['close_flag']) || empty($_POST['close_flag'])) {
                $this->error('请选择关闭控制');
            } else {
                $insert_data['close_flag'] = $_POST['close_flag'];
            }            
        }
		//渠道
		$insert_data['channel_id'] = '';
		if(isset($_POST['cid'])){
			foreach($_POST['cid'] as $k=>$v){
				if($k+1==count($_POST['cid'])){
					$insert_data['channel_id'] .= $v;
				}else{
					$insert_data['channel_id'] .= $v.',';
				}
				
			}
		}
        //$insert_data['channel_id'] = $_POST['channel_id'];
        //$insert_data['channel_key'] = $_POST['channel_key'];
        if (!isset($_POST['pid']) || empty($_POST['pid'])) {
            $this->error('请填写平台');
        } else {
            $insert_data['pid'] = $_POST['pid'];
        }
        if (!isset($_POST['start_at']) || empty($_POST['start_at'])) {
            $this->error('请填写开始时间');
        } else {
            $insert_data['start_at'] = strtotime($_POST['start_at']);
        }
        if (!isset($_POST['end_at']) || empty($_POST['end_at'])) {
            $this->error('请填写结束时间');
        } else {
            $insert_data['end_at'] = strtotime($_POST['end_at']);
        }
        if (strtotime($_POST['start_at']) > strtotime($_POST['end_at'])) {
            $this->error('开始时间大于结束时间');
        }
        if ($_POST['c_ad_type'] != 2) {
            if ($_POST['content_type'] == 1) {
                if (!isset($_POST['url']) || empty($_POST['url'])) {
                    $this->error('请填写链接地址');
                } else {
                    $insert_data['url'] = $_POST['url'];
                }
                if (!isset($_POST['open_mode']) || empty($_POST['open_mode'])) {
                    $this->error('请选择链接打开方式');
                } else {
                    $insert_data['open_mode'] = $_POST['open_mode'];
                }
            } else {
                if (!isset($_POST['package']) || empty($_POST['package'])) {
                    $this->error('请填写包名');
                }else{
                    $insert_data['package'] = $_POST['package'];
                }
                if (!isset($_POST['download_flag']) || empty($_POST['download_flag'])) {
                    $this->error('请选择下载方式');
                } else {
                    $insert_data['download_flag'] = $_POST['download_flag'];
                }
				if (!isset($_POST['show_intall_pic']) || empty($_POST['show_intall_pic'])) {
                    $this->error('请选择下载完成是否弹出安装界面');
                } else {
                    $insert_data['show_intall_pic'] = $_POST['show_intall_pic'];
                }
            }
        }else{
                $pack_num = array_count_values($_POST['package']);
                foreach ($pack_num as $key => $val) {
                    if ($val > 1) {
                        $this->error('包名重复');
                    }
                }
                $rank_num = array_count_values($_POST['rank']);
                foreach ($rank_num as $r_k => $r_v) {
                    if ($r_v > 1) {
                        $this->error('排序值重复');
                    }
                }
                $package = array();
                foreach ($_POST['package'] as $k => $v) {
                    if ($v == '') {
                        $this->error('请填写包名');
                    }
                    if (isset($_POST['rank'][$k]) && !empty($_POST['rank'][$k]) && $_POST['rank'][$k] != '排序值') {
                        $package[$v] = $_POST['rank'][$k];
                    } else {
                        $this->error('请填写排序');
                    }
                }
        }
        if (!empty($_POST['edit'])) {
            $insert_data['id'] = $_POST['id'];
            $edit = 1;
        } else {
            $edit = 0;
        }
		$insert_data['app_type'] =$_POST['app_type'];
        if($_POST['push']!="1"){
			$insert_data['market_flag'] = $_POST['market_flag'];
			$insert_data['show_flag'] = $_POST['show_flag'];
            if ($insert_data['type'] == 2) {
                $this->update_data($insert_data, $package, $edit);
            } else {
                $this->update_data($insert_data, '', $edit);
            }
        }else{
            $this->update_push($insert_data,$edit);
        }
            }

    //编辑广告
    public function edit_ad() {
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $this->error('非法操作');
        }
        $model = M('');
        $res = $model->table('sdk_ad')->where(array('id' => $_POST['id'], 'status' => 1))->find();
        if(!empty($res['channel_id'])){
            $channel_name = $model->table('sj_channel')->where(array('cid'=>array('in',$res['channel_id'])))->field('chname')->select();
            foreach($channel_name as $k=>$v){
                if($k+1==count($channel_name)){
                    $res['channel_name'] .= $v['chname'];
                }else{
                    $res['channel_name'] .= $v['chname'].',';
                }
            }
            
        }
        
        if ($res['type'] == 2) {
            $pack = $model->table('sdk_ad_soft')->where(array('sdk_ad_id' => $_POST['id'], 'status' => 1))->field('package,rank')->select();
            if ($pack) {
                foreach ($pack as $key => $val) {
                    $res['package'][$key]['package'] = $val['package'];
                    $res['package'][$key]['rank'] = $val['rank'];
                }
            }
        }
        $res['ad_type'] = $res['type'];
        $res['start_at'] = date('Y-m-d H:i:s', $res['start_at']);
        $res['end_at'] = date('Y-m-d H:i:s', $res['end_at']);
		if($res['priority']==0)
		{
			$res['priority']="";
		}
        echo json_encode($res);
    }

    //删除广告
    public function del_ad() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $this->error('非法操作');
        }
        $model = M('');
        $res = $model->table('sdk_ad')->where(array('id' => $_GET['id'], 'status' => 1))->field('id,type')->find();
        if (!$res) {
            $this->error('非法操作');
        } else {
            $re = $model->table('sdk_ad')->where(array('id' => $_GET['id'], 'status' => 1))->save(array('status' => 0, 'update_at' => time()));
            if ($re && $res['type'] == 2) {
                $this->writelog('安智市场-手机:通用广告sdk-删除了id为'.$_GET['id'].'的广告','sdk_ad',"{$_GET['id']}",__ACTION__ ,"","del");
                $r = $model->table('sdk_ad_soft')->where(array('sdk_ad_id' => $_GET['id'], 'status' => 1))->save(array('status' => 0, 'update_at' => time()));
                if ($r) {
                    $this->writelog('安智市场-手机:通用广告sdk-删除了广告id为'.$_GET['id'].'的包名','sdk_ad_soft',"{$_GET['id']}",__ACTION__ ,"","del");
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
            } else if ($re && $res['type'] != 2) {
                $this->writelog('安智市场-手机:通用广告sdk-删除了id为'.$_GET['id'].'的广告','sdk_ad_soft',"{$_GET['id']}",__ACTION__ ,"","del");
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        }
    }

	//日志详情内容
	public function log_content($data,$res){
		if(isset($data['start_at'])) $data['start_at'] = date("Y-m-d H:i:s",$data['start_at']);
		if(isset($data['end_at'])) $data['end_at'] = date("Y-m-d H:i:s",$data['end_at']);
		$log_arr = array(
			'title'=>array('extra'=>'标题'),
			'description'=>array('extra'=>'描述'),
			'url'=>array('extra'=>'链接地址'),
			'open_mode'=>array('extra'=>'链接打开方式','type'=>array('1'=>'内置浏览器','2'=>'外置浏览器')),
			'type'=>array('extra'=>'广告类型','type'=>array('1'=>'闪屏广告','2'=>'弹窗广告1','3'=>'弹窗广告2','4'=>'弹窗广告3','5'=>'banner广告','6'=>'文字链接')),
			'content_type'=>array('extra'=>'广告数据类型','type'=>array('1'=>'网页','2'=>'软件')),
			'banner_type'=>array('extra'=>'banner类型','type'=>array('1'=>'1','2'=>'2','3'=>'3')),
			'close_flag'=>array('extra'=>'关闭控制字段说明','type'=>array('1'=>'关闭后不再展示','2'=>'关闭后下次启动展示')),
			'channel_id'=>array('extra'=>'渠道'),
			'pid'=>array('extra'=>'平台'),
			'start_at'=>array('extra'=>'开始时间'),
			'end_at'=>array('extra'=>'结束时间'),
			'download_flag'=>array('extra'=>'下载方式','type'=>array('1'=>'点击广告开始下载','2'=>'弹出广告自动下载','3'=>'后台静默下载完成后弹出广告','4'=>'下载安智市场','5'=>'使用安智市场下载软件')),
			'app_type'=>array('extra'=>'投放区域','type'=>array('0'=>'通用','1'=>'定制','2'=>'不限制')),
			'show_flag'=>array('extra'=>'启动次数展示'),
			'market_flag'=>array('extra'=>'安智市场安装状态','type'=>array('0'=>'未安装','1'=>'已安装','2'=>'不限制')),
			'imei_file_path' =>array('extra' =>'覆盖用户'),
			'priority' =>array('extra' =>'优先级'),
		);
		$log_str = '安智市场-手机:通用广告sdk-添加了广告id为【'.$res.'】的广告';
		foreach($data as $key=>$val){
			if(isset($log_arr[$key])){
				if(isset($log_arr[$key]['type'])){
					$log_str .= ",{$log_arr[$key]['extra']}为【{$log_arr[$key]['type'][$val]}】";
				}else{
					$log_str .= ",{$log_arr[$key]['extra']}为【{$val}】";
				}
				
			}
		}
		return $log_str;
	}
    //入库操作
    public function update_data($data, $package, $edit) {
        $model = M('');
		//查询渠道对应key
		if(isset($data['channel_id'])&&!empty($data['channel_id'])){
			$channel_key = $model->table('sj_channel')->where(array('cid'=>array('in',$data['channel_id'])))->field('chl_cid')->select();
			$data['channel_key'] = '';
			foreach($channel_key as $k=>$v){
				if($k+1==count($channel_key)){
					$data['channel_key'] .= $v['chl_cid'];
				}else{
					$data['channel_key'] .= $v['chl_cid'].',';
				}
			}
		}

        $insert_data = array();
        if ($edit) {
            //编辑
            $id = $data['id'];
            unset($data['id']);
            $data['update_at'] = time();
			$log = $this->logcheck(array('id'=>$id),'sdk_ad',$data,$model);
            $res = $model->table('sdk_ad')->where(array('id'=>$id))->save($data);
            if($res){
                $this->writelog('安智市场-手机:通用广告sdk-编辑了广告id为'.$id.'的广告<br>,'.$log,'sdk_ad',"{$id}",__ACTION__ ,"","edit");
                if ($data['type'] == 2) {
                    $model->table('sdk_ad_soft')->where(array('sdk_ad_id'=>$id,'status'=>1))->save(array('status'=>0,'update_at'=>time()));
                    foreach($package as $k=>$v){
                        $pack['sdk_ad_id'] = $id;
                        $pack['package'] = $k;
                        $pack['start_at'] = $data['start_at'];
                        $pack['end_at'] = $data['end_at'];
                        $pack['create_at'] = $pack['update_at'] = time();
                        $add_pack = $model->table('sdk_ad_soft')->add($pack);
                        $rank = $this->_updateRankInfo('sdk_ad_soft', 'rank', $add_pack, array('status' => 1, 'sdk_ad_id' => $id), (int) $v);
                    }
                    $this->success('更新成功');
                }else{
                    $this->success('更新成功');
                }
                
            }else{
                $this->error('更新失败');
            }
        } else {
            //添加
            $return = false;
            $data['create_at'] = time();
            $data['update_at'] = time();
            $data['status'] = 1;
            $res = $model->table('sdk_ad')->add($data);
            if ($res) {				
				$log_str = $this->log_content($data,$res);
                $this->writelog($log_str,'sdk_ad',"{$res}",__ACTION__ ,"","add");
                if ($data['type'] == 2) {
                    //广告数据类型是软件
                    foreach ($package as $key => $val) {
                        $has_pack = $model->table('sdk_ad_soft')->where(array('status' => 1, 'sdk_ad_id' => $res, 'package' => $key))->field('id')->find();
                        if (!$has_pack) {
                            $pack['sdk_ad_id'] = $res;
                            $pack['package'] = $key;
                            $pack['start_at'] = $data['start_at'];
                            $pack['end_at'] = $data['end_at'];
                            $pack['create_at'] = $pack['update_at'] = time();
                            $add_pack = $model->table('sdk_ad_soft')->add($pack);
                            $this->writelog('安智市场-手机:通用广告sdk-添加了包名为'.$key.'广告id为'.$res.'的包','sdk_ad',"{$add_pack}",__ACTION__ ,"","add");
                            $rank = $this->_updateRankInfo('sdk_ad_soft', 'rank', $add_pack, array('status' => 1, 'sdk_ad_id' => $res), (int) $val);
                            if (!$rank) {
                                $this->error('更新排序失败');
                            } else {
                                $return = true;
                            }
                        } else {
                            $save['start_at'] = $data['start_at'];
                            $save['end_at'] = $data['end_at'];
                            $save['update_at'] = time();
                            $result = $model->table('sdk_ad_soft')->where(array('id' => $has_pack['id'], 'status' => 1))->save($save);
                            if ($result) {
                                $this->writelog('安智市场-手机:通用广告sdk-更新了id为'.$has_pack['id'].'的包','sdk_ad',"{$has_pack['id']}",__ACTION__ ,"","add");
                                $rank = $this->_updateRankInfo('sdk_ad_soft', 'rank', $has_pack['id'], array('status' => 1, 'sdk_ad_id' => $res), (int) $val);
                                $return = true;
                            } else {
                                $this->error('更新失败');
                            }
                        }
                    }
                    if ($return) {
                        $this->success('更新成功');
                    }

                } else {
                    $this->success('更新成功');
                }
            } else {
                $this->error('添加失败，请重试');
            }
        }
    }

    //上传文件
    public function upload_file($file) {
        $files = $data = array();
        foreach ($file as $k => $v) {
            if ($v['error'] == 0) {
                $files[$k] = '@' . $v['tmp_name'];
            }
        }
        if ($files) {
            $files['static_data'] = C('ad_sdk_img');
            $files['do'] = 'save';
        }
        $upload_model = D("Dev.Uploadfile");
        $upload = $upload_model->_http_post($files);
        if ($upload['info']['http_code'] != 200) {
            $this->error("和图片服务器通讯失败，请重试！({$arr['errno']}:{$arr['error']})");
        }
        if ($upload['ret']) {
            foreach ($upload['ret'] as $key => $val) {
                if ($val == 'failed') {
                    if ($key == 'landscape_img') {
                        $this->error('闪屏横屏图片上传失败，请重试！');
                    } else if ($key == 'portrait_img') {
                        $this->error('闪屏竖屏图片上传失败，请重试！');
                    } else if ($key == 'img') {
                        $this->error('图片上传失败，请重试！');
                    }
                } else {
                    if ($key == 'landscape_img' || $key == 'portrait_img' || $key == 'img') {
                        $data[$key] = $val;
                    }
                }
            }
        }
        return $data;
    }

    //ajax填充添加页面
    public function choose_ad_type() {
        $ad_type = $_POST['ad_type'];
        $content_type = C('ad_content_type');
        $banner_type = C('banner_type');
        $download_flag = C('download_flag');
        $this->assign('download_flag', $download_flag);
        $this->assign('content_type', $content_type);
        $this->assign('banner_type', $banner_type);
        switch ($ad_type) {
            case 1:
                $this->display('ad_type1');
                break;
            case 2:
                $this->display('ad_type2');
                break;
            case 3:
                $this->display('ad_type3');
                break;
            case 4:
                $this->display('ad_type4');
                break;
            case 5:
                $this->display('ad_type5');
                break;
            case 6:
                $this->display('ad_type6');
                break;
        }
    }
    
    
    //通用广告sdk推送
    public function sdk_push(){
		$where = array('status' => 1);
		if(!isset($_GET['status'])){		
			$status = 2;
		}else{
			$status = $_GET['status'];
		}
		$this->assign('status',$status);
		if($status == 1){
			$where['start_at'] = array('GT',time());
		}else if($status == 2){
			$where['start_at'] = array('ELT',time());
			$where['end_at'] = array('EGT',time());
		}else if($status == 3){
			$where['end_at'] = array('LT',time());
		}
        $content_type = C('ad_content_type');
        $download_flag = C('download_flag');
        $ad_content_type = C('ad_content_type');
        $this->assign('download_flag', $download_flag);
        $this->assign('content_type', $content_type);
        $model = M('');

        $total = $model->table('sdk_push')->where($where)->count();
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
        import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 1;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $result = $model->table('sdk_push')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
        foreach ($result as $key => $val) {
            $result[$key]['content_type_name'] = $ad_content_type[$val['content_type']];
            $result[$key]['start_at'] = date('Y-m-d H:i:s',$result[$key]['start_at']);
            $result[$key]['end_at'] = date('Y-m-d H:i:s',$result[$key]['end_at']);
			if(!empty($result[$key]['channel_id'])){
				$channel_name = $model->table('sj_channel')->where(array('cid'=>array('in',$result[$key]['channel_id'])))->field('chname')->select();
				foreach($channel_name as $k=>$v){
					if($k+1==count($channel_name)){
						$result[$key]['channel_name'] .= $v['chname'];
					}else{
						$result[$key]['channel_name'] .= $v['chname'].',';
					}
				}
			}
        }
        $this->assign('page', $Page->show());
        $this->assign('total', $total);
        $this->assign('result', $result);
        $this->display();
    }
    
    //通用广告sdk推送入库
    public function update_push($data,$edit){
        $model = M('');
        //查询渠道对应key
        if(isset($data['channel_id'])&&!empty($data['channel_id'])){
                $channel_key = $model->table('sj_channel')->where(array('cid'=>array('in',$data['channel_id'])))->field('chl_cid')->select();
                $data['channel_key'] = '';
                foreach($channel_key as $k=>$v){
                        if($k+1==count($channel_key)){
                                $data['channel_key'] .= $v['chl_cid'];
                        }else{
                                $data['channel_key'] .= $v['chl_cid'].',';
                        }
                }
        }
        if ($edit) {
            $id = $data['id'];
            unset($data['id']);
            $data['update_at'] = time();
            $res = $model->table('sdk_push')->where(array('id'=>$id))->save($data);
            if ($res) {
                $this->writelog('安智市场-手机:通用广告sdk推送-编辑了广告sdk推送id为'.$id.'的推送广告','sdk_push',"{$id}",__ACTION__ ,"","edit");
                $this->success('更新成功');
            }else{
                $this->success('更新失败');
            }
        }else{
            $data['create_at'] = time();
            $data['update_at'] = time();
            $res = $model->table('sdk_push')->add($data);
            if ($res) {
                $this->writelog('安智市场-手机:通用广告sdk推送-添加了广告sdk推送id为'.$res.'的推送广告','sdk_push',"{$res}",__ACTION__ ,"","add");
                $this->success('更新成功');
            }else{
                $this->success('更新失败');
            }
        }
    }
	
	//删除通用广告sdk
	public function del_push(){
		if (!isset($_GET['id']) || empty($_GET['id'])) {
            $this->error('非法操作');
        }
        $model = M('');
		$res = $model->table('sdk_push')->where(array('id' => $_GET['id'], 'status' => 1))->save(array('status' => 0, 'update_at' => time()));
		if($res){
			$this->writelog('安智市场-手机:通用广告sdk推送-删除了推送广告id为'.$_GET['id'].'的推送广告','sdk_push',"{$_GET['id']}",__ACTION__ ,"","del");
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	//退出广告位管理__广告列表
	public function ad_exit_list(){
		$model = D('Sj.Adsdk');
		$where = array(
			'status' => 1
		);	
		$_GET['type'] ? $_GET['type'] : $_GET['type'] =2;
		$this -> assign('type',$_GET['type']);
		$this->check_where($where, 'ad_name', 'isset', 'like');
		$this->check_where($where, 'ad_type');
		list($list,$total,$page) = $model->get_ad_exit_list($where);
		$this->assign('list', $list);
		$this -> assign('page', $page->show());
		$this -> assign('total', $total);	
		$this -> assign('firmware',$model -> get_firmware());	
		$this -> display();
	}
	//退出广告位管理__添加广告
	public function add_exit_ad(){
		$model = D('Sj.Adsdk');
		if($_POST){
			$start = strtotime($_POST['begintime']);
			$end = strtotime($_POST['endtime']);
			$priority = $_POST['priority'];
			$where = array(
				'ad_end_tm' => array('egt',$start),
				'ad_start_tm' => array('elt',$end),
				'priority' => $priority,
				'status' => 1,				
			);
			if($_POST['id']){
				$where['id'] = array('exp',"!={$_POST['id']}");
			}
			$ret = $model->table('sj_exit_ad')->where($where)->find(); 	
			if($ret){
				$this->error("同一排期内优先级重复，请重新填写");
			}
            if($_POST['id']){
                $data = $_POST;
                $unset_arr = array('__hash__','URL_REFERER','user_type_old','begintime','endtime','suggests_soft','pkg_file_path','user_file_path');
                if($_POST['within_3_user'] == 2){
                    $data['select_user_type_ext'] = implode(',',$_POST['user_type_old']);
                }else if($_POST['within_3_user'] == 3){
                    $data['select_user_type_ext'] = implode(',',$_POST['user_type_vip']);
                }
                $data['ad_start_tm'] = strtotime($_POST['begintime']);
                $data['ad_end_tm'] = strtotime($_POST['endtime']);
                $data['pkg_path'] = $_POST['pkg_file_path'];
                $data['user_name_path'] = $_POST['user_file_path'];
                $data['package'] = trim($_POST['package_new']);
                foreach($unset_arr as $v){
                    unset($data[$v]);
                }
                $log = $this -> logcheck(array('id' =>$_POST['id']),'sj_exit_ad',$data,$model);
                //var_dump($log);
            }

			$res = $model -> post_add_exit();
            //exit();
			if($res){
				if($_POST['id']){
					$this->writelog("编辑了退出广告位id为{$_POST['id']}的广告位,{$log}",'sj_exit_ad',"{$_POST['id']}",__ACTION__ ,"","edit");
				}else{
					$this->writelog("添加了退出广告位id为{$res}的广告位",'sj_exit_ad',"{$res}",__ACTION__ ,"","add");
				}
				$this->assign("jumpUrl",$_POST['URL_REFERER']);
				$this->success("操作成功");
			}else{
				$this->error('提交失败');
			}
		}else{
			if($_GET['check_file'] == 'pkg'){
				$model -> get_pkg_code();
			}else if($_GET['check_file'] == 'user'){
				$model -> get_user_code();
			}else if($_GET['check_file'] == 'sdk_pkg'){
				$model -> get_sdk_pkg_code();
			}else if($_GET['check_file'] == 'ad_picture'){
				$model -> get_ad_picture_code();
			}else if($_GET['check_file'] == 'softname'){
				$model -> get_softname();
			}else if($_GET['check_file'] == 'priority_num'){
				$model -> get_priority_num();
			}

			$sdk_version_db = json_decode(file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/index.php/Interface/sdk_list'),true);
			$this -> assign('sdk_version_db',$sdk_version_db['list']);

			
			$this -> assign('URL_REFERER',$_SERVER['HTTP_REFERER']);	
			$this -> assign('firmware',$model -> get_firmware());	
			$this -> assign('memory_arr',$model -> get_memory());	
			$this -> display();
		}
	}
	//退出广告位管理__编辑
	public function ad_exit_edit(){
		$model = D('Sj.Adsdk');
		$this -> assign('id',$_GET['id']);	
		$list = $model->table('sj_exit_ad')->where("id={$_GET['id']}")->find();
		if($list['game_name_type'] == 2){
			if($list['game_name'] ==1){
				$list['game_name'] = '全部游戏';
			}else if($list['game_name'] ==2){	
				$list['game_name'] = '网络游戏';
			}else if($list['game_name'] ==3){	
				$list['game_name'] = '棋牌游戏';
			}else if($list['game_name'] ==4){	
				$list['game_name'] = '单机游戏';
			}
		}else if($list['game_name_type'] == 0){
			$_GET['package'] = $list['game_name'];
			$ret = $model -> get_softname(1);
			$list['game_name'] = $ret['softname'];
		}else if($list['game_name_type'] == 1){
			$ret = $model -> file_get_softname($list['game_name_path']);
			$list['game_name'] = $ret;
		}
		if($list['ignore_package_list']){
			//屏蔽游戏
			$where = array('package' => array('in',explode(',',$list['ignore_package_list'])));
			$soft_list = get_table_data($where,"sj_soft","package","package,softname",'softid asc');
			$this -> assign('soft_list',$soft_list);	
		}
		
		
		$sdk_version_db = json_decode(file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/index.php/Interface/sdk_list'),true);
				
		$this -> assign('sdk_version_db',$sdk_version_db['list']);
		
		$this -> assign('list',$list);	
		$this -> assign('firmware',$model -> get_firmware());	
		$this -> assign('memory_arr',$model -> get_memory());	
		
		$this -> assign('URL_REFERER',$_SERVER['HTTP_REFERER']);	
		$this->display('Sj:AdSdk:add_exit_ad'); 	
	}
	//退出广告位管理__启用、停用
	public function ad_exit_switch(){
		$model = D('Sj.Adsdk');
		$id = $_GET['id'];
		$map = array(
			'ad_switch' => $_GET['ad_switch'],
			'update_tm' => time(),
		);
		$ret = $model->table('sj_exit_ad')->where("id={$_GET['id']}")->save($map);	
		if($ret){
			if($_GET['ad_switch'] == 1){
				$str = "启用";
			}else{
				$str = "停用";
			}
			$this->writelog($str."了退出广告位id为{$id}的广告位",'sj_exit_ad',"{$id}",__ACTION__ ,"","edit");
			$referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "/index.php/Sj/AdSdk/ad_exit_list";
			$this->assign("jumpUrl",$referer);
			$this->success("操作成功");
		}else{
			$this->error('提交失败');
		}		
	}
	public function ad_exit_del(){
		$model = D('Sj.Adsdk');
		$id = $_GET['id'];
		$map = array(
			'status' => 0,
			'update_tm' => time(),
		);
		$ret = $model->table('sj_exit_ad')->where("id={$_GET['id']}")->save($map);	
		if($ret){
			$this->writelog("删除了退出广告位id为{$id}的广告位",'sj_exit_ad',"{$id}",__ACTION__ ,"","del");
			$referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "/index.php/Sj/AdSdk/ad_exit_list";
			$this->assign("jumpUrl",$referer);
			$this->success("操作成功");
		}else{
			$this->error('提交失败');
		}			
	}
	//查看范围
	public function pub_view_extents(){
		$model = D('Sj.Adsdk');
		$id = $_GET['id'];
		$list = $model->table('sj_exit_ad')->where("id={$_GET['id']}")->find();
		$firmware = $model -> get_firmware();
		$memory = $model -> get_memory();
		$str = '';
		if($firmware[$list['min_firmware']]['configcontent']) $str .= "系统：".$firmware[$list['min_firmware']]['configcontent']."以上<br/>";
		if($memory[$list['memory']]) $str .= "内存：".$memory[$list['memory']]."内存以上<br/>";
		
		if($list['resolution'] == 'm'){
			$str .= "分辨率：中分辨率<br/>";
		}else if($list['resolution'] == 'h'){
			$str .= "分辨率：高分辨率<br/>";
		}else if($list['resolution'] == 'xh'){
			$str .= "分辨率：超高分辨率<br/>";
		}
		if($list['game_name_type'] == 2){
			if($list['game_name'] ==1){
				$list['game_name'] = '全部游戏';
			}else if($list['game_name'] ==2){	
				$list['game_name'] = '网络游戏';
			}else if($list['game_name'] ==3){	
				$list['game_name'] = '棋牌游戏';
			}else if($list['game_name'] ==4){	
				$list['game_name'] = '单机游戏';
			}
		}else if($list['game_name_type'] == 0){
			$_GET['package'] = $list['game_name'];
			$ret = $model -> get_softname(1);
			$list['game_name'] = $ret['softname'];
		}else if($list['game_name_type'] == 1){
			$ret = $model -> file_get_softname($list['game_name_path']);
			$list['game_name'] = $ret;
		}	
		if($list['within_3_user'] == 2){
			$arr = array(
				2 => '2日未登录用户',
				7 => '1周未登录用户',
				14 => '2周未登录用户',
				21 => '3周未登录用户',
				30 => '1个月未登录用户',
			);
			$str .= "用户名：<br/>";
			foreach(explode(',',$list['select_user_type_ext']) as $v){
				$str .= "&nbsp;&nbsp;&nbsp;&nbsp;".$arr[$v]."<br/>";
			}
		}else if($list['within_3_user'] == 3){
			$arr = array(
				1 => 'VIP1',
				2 => 'VIP2',
				3 => 'VIP3',
				4 => 'VIP4',
				5 => 'VIP5',
			);
			$str .= "用户名：<br/>";
			foreach(explode(',',$list['select_user_type_ext']) as $v){
				$str .= "&nbsp;&nbsp;&nbsp;&nbsp;".$arr[$v]."<br/>";
			}			
		}	
		if($list['within_3_user'] >=2 && $list['ignore_package_list']){
			//屏蔽游戏
			$where = array('package' => array('in',explode(',',$list['ignore_package_list'])));
			$soft_list = get_table_data($where,"sj_soft","package","package,softname",'softid asc');
			$str .= "屏蔽游戏：<br/>";
			foreach($soft_list as $v){
				$str .= "&nbsp;&nbsp;&nbsp;&nbsp;".$v['softname']."<br/>";
			}
		}		
		//if($list['game_name']) $str .= "游戏名称：".$list['game_name'];
		echo "<b>发布范围：</b><br/>".$str;
	}
	//覆盖用户数
	public function pub_user_num(){
		$min_firmware = sprintf("%02d",$_POST['min_firmware']);//固件版本//生成2位数，不足前面补0 
		$resolution = $_POST['resolution'];//屏幕分辨率
		if(!S('user_num_'.$min_firmware.$_POST['memory'].$resolution)){
			$model = D('Sj.Adsdk');
			$memory = $model -> get_memory();
			$url = "http://172.16.1.218:8009/service/apilog/usersCount";
			$vals = array(
				'os_server' => $min_firmware,  
				'memory' => $_POST['memory'] ? $memory[$_POST['memory']]*1024*1024 : '',//内存字节
				'screen' => $resolution,
				'time' => time()*1000,//毫秒
			);
			$val = json_encode($vals);
			$list = httpGetInfo($url, "msg={$val}",'user_num.log');
			if($list){
				S('user_num_'.$min_firmware.$_POST['memory'].$resolution,$list,86400*5);
			}
		}else{
			$list = S('user_num_'.$min_firmware.$_POST['memory'].$resolution);
		}
		exit($list);
	}
	//计算csv中的覆盖个数 并返回
	function csv_count()
	{
		if($_FILES['upload_file'])
		{
			$filename=$_FILES['upload_file']['tmp_name'];
			$err = $_FILES["upload_file"]["error"];
			$file_name_csv=$_FILES['upload_file']['name'];
			$tmp_arr = explode(".", $file_name_csv);
			$name_suffix = array_pop($tmp_arr);
		}
		if(empty($filename))
		{
			$error1=-1;
			echo '{"error1":"'.$error1.'"}';
			return;
		}
		if (strtoupper($name_suffix) != "CSV") 
		{
			$error2=-2;
			echo '{"error2":"'.$error2.'"}';
			return;
		}
		$handle=fopen($filename,'r');
		$out = array (); 
		$n = 0; 
		while (!feof($handle)) 
		{
			$out[$n]=fgets($handle);
			$out[$n]=str_replace(array("\n","\r"),"",$out[$n]);//去掉换行符
			if(!empty($out[$n]))
			{
				$n++;
			}
		}
		if($_FILES['upload_file'])
		{
			if(trim($out[0])!="IMEI")
			{
				$error2=-3;
				echo '{"error2":"'.$error2.'"}';
				return;
			}
		}
		$len_result=$n-1;
		
		// save the import file for backups
		$path=date("/Ym/d/",time());
		$save_dir = C("MARKET_PUSH_CSV").$path;
		$this->mkDirs($save_dir);
		$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
		$save_file_name = $save_dir . $save_name;
		$db_save=$path.$save_name;
		if($_FILES['upload_file'])
		{
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
		}
		echo '{"out_count":"' . $len_result . '","csv_url":"' . $db_save . '"}';
	}
	function check_priority()
	{
		$start_at = strtotime($_GET['begintime']);
		$end_at = strtotime($_GET['endtime']);
		$priority = $_GET['priority'];
		$id = $_GET['id'];
		$model = new model();
		
		$where = array(
			'start_at' => array('elt',$end_at),
			'end_at' => array('egt',$start_at),
			'priority' => $priority,
			'status' =>1,
		);
		if($id)
		{
			$where['_string'] .= "id != {$id}";
		}
		$ret = $model->table('sdk_ad')->where($where)->find(); 
		if($ret){
			exit(json_encode(array('code'=>0)));
		}else{
			exit(json_encode(array('code'=>1)));
		}
	}
}