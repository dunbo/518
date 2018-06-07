<?php
/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2015 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author： 
 *
 * ----------------------------------------------------------------------------
*/
class ClientMarkAction extends CommonAction {


	public function index()
	{
		$act = empty($_GET['act']) ? 'list' : $_GET['act'];
		//print_r($_SESSION);
		//exit($act);
		
		
		
		$busi = D("Settlement.ClientMark");
		switch($act){
			case 'search_package':
				$soft = $this->search_package(!empty($_GET['package']) ? $_GET['package'] : '');
				if($soft)
				{
					echo json_encode($soft);
				} else {
					echo json_encode(array('error' => array('code' => 2, 'msg' => '软件包不存在,请重新输入')));
				}
				exit;
				break;
			case 'edit_one':
				$this->edit_one();
				break;
			case 'edits':
				$this->edits();
			break;
			case 'export_data':
				$this->export_data();
				break;
			case 'get_log':
				echo json_encode($busi->getLogsByMid(intval($_GET['mid'])));
				exit;
				break;
			default:
				$p = !empty($_GET['p']) ? intval($_GET['p']) : 1; //页数
				$lr = !empty($_GET['lr']) ? intval($_GET['lr']) : 20; //每页记录数
				$order = !empty($_GET['order']) ? $_GET['order'] : null; //每页记录数
				$search = array();
				$search['cmark'] = empty($_GET['cmark']) ? null : trim($_GET['cmark']);
				$search['softname'] = empty($_GET['softname']) ? null : trim($_GET['softname']);
				$search['package'] = empty($_GET['package']) ? null : trim($_GET['package']);
				$search['bid'] = empty($_GET['bid']) ? null : intval($_GET['bid']);
				$search['cooperation'] = empty($_GET['cooperation']) ? null : trim($_GET['cooperation']);
				$search['stype'] = empty($_GET['stype']) ? null : intval($_GET['stype']);

				if($order == "up") {
					$order = "cmark ASC";
				} else if($order == "dn") {
					$order = "cmark DESC";
				}

				$search['order'] = $order;
				//print_r($search);
				$result = $busi->getList($search, $p, $lr);
				$item = $result['item'];
				$count = $result['count'];
				// 处理分页
				import("@.ORG.Page");
				$page = new Page($count, $lr);
				$page->setConfig('header','条记录');
				$page->setConfig('first','<<');
				$page->setConfig('last','>>');
				$this->assign('order', $order);
				$this->assign('page', $page->show());
				$this->assign('item', $item);
				$this->display();
				break;

				
		}
	}



	private function edit_one() {
	
		$busi = D("Settlement.ClientMark");

		$item = array();
		$bid = intval($_POST['mid']);
		//$item['lasttime'] = time();
		//$item['admin_id'] = $_SESSION['admin']['admin_id'];
		//$item['admin_name'] = $_SESSION['admin']['admin_user_name'];
		
		
		$data_map = array('utype','cooperation','stype','bid');
		if(!$bid) {
			$data_map = array_merge(array('cmark','package'),$data_map);
		} else {
			$data_map = array_merge(array('status'),$data_map);
		}
		
		foreach($data_map as $v){
			isset($_POST[$v]) && $item[$v] = trim(strip_tags($_POST[$v]));
		}

		if($bid){
			if(!isset($item['status'])){
				$log = $this->logcheck(array('mid'=>$bid), 'settlement.ad_client_mark', $item, $busi);
			}

			$busi->editById($bid,$item);
			if(isset($item['status'])){
				$this->writelog("广告结算-客户编号表：删除了mid为{$bid}的客户",'ad_client_mark',$bid,__ACTION__ ,"","del");
			}else{
				$this->writelog("广告结算-客户编号表：编辑了mid为{$bid}的客户,{$log}",'ad_client_mark',$bid,__ACTION__ ,"","edit");
			}

		} else {
			$mat_items = $busi->getItemByMark($item['cmark']);
			$soft = $this->search_package($item['package']);
			if(!$soft) {
				$error['code'] = 2;
				$error['msg'] = '软件包不存在，请重新输入';
				exit(json_encode(array('error' => $error)));
			}
			
			$item['softname'] = $soft['softname'];
			$item['softid'] = $soft['softid'];


			if(!empty($mat_items)) {
				$error['code'] = 1;
				$error['msg'] = '当前编号已使用，请重新输入';
				exit(json_encode(array('error' => $error)));
			}
			$bid = $busi->addItem($item);
			$this->writelog("广告结算-客户编号表：添加了mid为{$bid}的客户",'ad_client_mark',$bid,__ACTION__ ,"","add");
		}
		exit(json_encode(array('msg' => 'ok')));
	}


	private function edits() {
	
		$busi = D("Settlement.ClientMark");

		$item = array();
		$mids = array();
		
		foreach($_POST['mids'] as $k => $v) {
			$mids[] = intval($v);
		}
		
		$data_map = array();
		$data_map = array_merge(array('utype','cooperation','stype','bid','cmark','sname','iname','status'),$data_map);
		foreach($data_map as $v){
			isset($_POST[$v]) && $item[$v] = strip_tags($_POST[$v]);
		}
		
		foreach($mids as $v) {
			if(!isset($item['status']))
			$log = $this->logcheck(array('mid'=>$v), 'settlement.ad_client_mark', $item, $busi);
			$busi->editById($v,$item);
			if(isset($item['status'])){
				$this->writelog("广告结算-客户编号表：删除了mid为{$v}的客户",'ad_client_mark',$v,__ACTION__ ,"","del");
			}else{
				$this->writelog("广告结算-客户编号表：编辑了mid为{$v}的客户,{$log}",'ad_client_mark',$v,__ACTION__ ,"","edit");
			}


		}
		exit(json_encode(array('msg' => 'ok')));
	}

	/* 导出数据 */
	private function export_data() {
	
		header('Content-type: application/csv');
		//下载显示的名字
		$file_name = date("Y-m-d").'.csv';
		header('Content-Disposition: attachment; filename=客户编号表_"'.$file_name); 
		$out = fopen('php://output', 'w');
		fputcsv($out,$this->u2k(array('客户编号','软件名称','软件包名','必备类型','合作形式','软件类型','对应商务')));
		
		foreach(json_decode($_POST['data'],true) as $v) {
			fputcsv($out,$this->u2k($v));
		}


	}
	
	/*导入数据*/
	public function inport_data() 
	{
		//添加客户信息 添加商务信息
		//
		// 处理文件上传
		//
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/ClientMark/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/ClientMark/index'</script>";
			exit;
		}

		// 处理数据 读取csv数据
		$client_arr = $this->import_file_to_array($info[0]['savepath'].$info[0]['savename']);
		if ($client_arr == -1) 
		{
			echo "<script>alert('文件打开错误，请检查文件是否损坏！');location.href='/index.php/Settlement/ClientMark/index';</script>";
			exit;
		} else if (empty($client_arr)) {
			echo "<script>alert('文件数据内容不能为空！');location.href='/index.php/Settlement/ClientMark/index';</script>";
			exit;
		}
		
		//
		// 处理创建客户信息和商务信息
		//
		$busi = D("Settlement.ClientMark");
		$business = D("Settlement.Business");
		$error_msg ='';
		$error_arr=array();
		foreach($client_arr as $key => $val)
		{
			$mat_items = $busi->getItemByMark($val['cmark']);
			$soft = $this->search_package($val['package']);
			$error_msg1="";
			$error_msg2="";
			$error_msg3="";
			$error_msg4="";
			$line = $key+2; //包括头部
			if(!$soft) 
			{
				$error_msg1 .="软件包为{$val['package']}不存在;";
				//$error['code'] = 2;
				//$error['msg'] = '软件包不存在，请重新输入';
				//exit(json_encode(array('error' => $error)));
				$error_arr[$key]['soft'] = "软件包为{$val['package']}不存在";
			}
			else
			{
				$client_arr[$key]['softname'] = $soft['softname'];
				$client_arr[$key]['softid'] = $soft['softid'];
				$client_arr[$key]['stype'] = $soft['stype'];//软件分类
			}
			
			if(!empty($mat_items)) 
			{
				$error_msg2 .="编号{$val['cmark']}已使用;";
				//$error['code'] = 1;
				//$error['msg'] = '当前编号已使用，请重新输入';
				//exit(json_encode(array('error' => $error)));
				$error_arr[$key]['cmark'] = "编号{$val['cmark']}已使用";
			}
			//判断商务名称不存在就添加  存在则获取商务的bid
			if(!empty($val['business']))
			{
				//$business_result = $business->getBusListByNC($val['business'],$val['business_color']);
				$business_person = $business->getBusListByNC($val['business'],''); //查找商务名称是否存在
				$business_color = $business->getBusListByNC('',$val['business_color']); //查找这个颜色是否存在
				
				
				//商务名称必须对应一个颜色，数据库已有颜色 颜色可以不填写 否则必填
				if(empty($business_person)&&empty($val['business_color']))
				{
					$error_msg4 .="商务人员需对应一个颜色;";
					$error_arr[$key]['business_colors'] = "颜色不能为空";
				}
				else
				{
					//if(empty($business_result))
					if(empty($business_person)&&empty($business_color))
					{
						$busi_arr=array(
							'bname' => $val['business'],
							'status' => 1,
							'type' => 1, //商务类型都是1：个人
							'admin_id' => $_SESSION['admin']['admin_id'],
							'admin_name' => $_SESSION['admin']['admin_user_name'],
							'lasttime' => time(),
							'color' => $val['business_color'],
						);
						$bid = $business->addItem($busi_arr);
						$this->writelog("广告结算-商务配置：添加了mid为{$bid}的商务",'ad_business',$bid,__ACTION__ ,"","add");
					}
					elseif($business_person&&empty($business_color))
					{
						$bid = $business_person[0]['bid'];
						$busi_arr=array(
							'admin_id' => $_SESSION['admin']['admin_id'],
							'admin_name' => $_SESSION['admin']['admin_user_name'],
							'lasttime' => time(),
							'color' => $val['business_color'],
						);
						$save_color = $business->editById($bid,$busi_arr);
						$log = $this->logcheck(array('bid'=>$bid), 'settlement.ad_business', $busi_arr, $business);
						$this->writelog("广告结算-商务配置：编辑了mid为{$bid}的商务,{$log}",'ad_business',$bid,__ACTION__ ,"","edit");
					}
					elseif(empty($business_person)&&$business_color)
					{
						$error_msg3 .="颜色{$val['business_color']}已使用;";
						$error_arr[$key]['business_color'] = "颜色{$val['business_color']}已使用";
					}
					elseif($business_person&&$business_color)
					{
						if($business_person[0]['bid']!=$business_color[0]['bid'])
						{
							$error_msg3 .="颜色{$val['business_color']}已使用;";
							$error_arr[$key]['business_color'] = "颜色{$val['business_color']}已使用";
						}
						else
						{
							$bid = $business_person[0]['bid'];
						}
					}
				}
				
				if($bid)
				{
					$client_arr[$key]['bid'] = $bid;
				}
			}
			if($error_msg1||$error_msg2||$error_msg3||$error_msg4)
			{
				$error_msg .="第{$line}行，{$error_msg1} {$error_msg2} {$error_msg3} {$error_msg4}\r\n";
				$error_arr[$key]['line'] = $line;
			}
		}
		if($error_msg)
		{
			//echo $error_msg;
			$this->error($error_msg);
			exit;
			//$sql_path ="/home/error/error.txt";
			/*$ft=fopen($sql_path,"a+");
			fwrite($ft,$error_msg."\n");
			fclose($ft);*/
			/*echo "<script>alert(\"有错误信息，请看下载文件\");location.href='/index.php/Settlement/ClientMark/index'</script>";*/
	
			//echo "<script type='text/javascript'>setTimeout('show_errors(\"".$error_msg."\")',10);</script>";
			
			//header('Content-type: application/csv;charset=utf-8');
			//下载显示的名字
			/*$file_name = date("Y-m-d").'.csv';
			header('Content-Disposition: attachment; filename=客户编号_错误信息_"'.$file_name); 
			$out = fopen('php://output', 'w');
			fputcsv($out,$this->u2k(array('行数','软件信息','编号信息')));
			
			foreach($error_arr as $v) {
				fputcsv($out,$this->u2k($v));
			}
			exit;*/
		}
		else
		{
			//没有问题就添加
			foreach($client_arr as $k_p => $v_p)
			{
				$item['cmark'] = $v_p['cmark'];
				$item['package'] = $v_p['package'];
				$item['softname'] = $v_p['softname'];
				$item['softid'] = $v_p['softid'];
				$item['stype'] = $v_p['stype'];
				
				$item['utype'] = $v_p['utype'];
				$item['cooperation'] = $v_p['cooperation'];
				$item['bid'] = $v_p['bid']?$v_p['bid']:0;
				$item['status'] = 0; //0是正常 1是删除
				
				$client_id = $busi->addItem($item);
				if($client_id)
				{
					$this->writelog("广告结算-客户编号表：添加了mid为{$client_id}的客户",'ad_client_mark',$client_id,__ACTION__ ,"","add");
				}
				else
				{
					echo "<script>alert('创建数据错误！');location.href='/index.php/Settlement/ClientMark/index'</script>";
					exit;
				}
			}
			echo "<script>alert('添加成功！');location.href='/index.php/Settlement/ClientMark/index'</script>";
		}
	}
	
	//读取文件数据 
	// 第一行标题列忽略，只保存之后的内容
    function import_file_to_array($file) {
        $handle = fopen($file, "r");
        if ($handle === false) {
            return -1;
        }
        $i = $j = 0;
        $content_arr = array();
        while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
            if ($i == 0) {
                // 读入标题列
                $title_arr = $line_arr;
            } else {
                // 读入每行内容
                $content_arr[$j] = $line_arr;
                $j++;
            }
            $i++;
        }
        fclose($handle);
        // 自动检测并转化编码
        foreach($content_arr as $key => $record) {
            foreach($record as $r_key => $r_value) {
                $content_arr[$key][$r_key] = $this->convert_encoding($r_value);
				$content_arr[$key][$r_key] = trim($content_arr[$key][$r_key]);
            }
        }
		
		//转化为带键值的
		$content_title_arr = array(
            'cmark'     =>  '客户编号',
            'package' => '包名',
            //'softname' => '软件名称',
           // 'softid'   =>  '软件id',
		   //'stype'  =>   '软件类型',
            'utype'  =>   '必备类别',
            'cooperation'  => '合作形式',
			'business' => '商务人员',
			'business_color' => '商务人员对应的颜色',
            //'bid'  =>   '',
            //'status'  =>   '',
        );
		
		// 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
        $new_content_arr = array();
        $new_key = array();
        // 将$correct_title_arr里的key值提取出来依次放在$new_key里
        foreach($content_title_arr as $k_t => $v_t) {
            $new_key[] = $k_t;
        }
        foreach($content_arr as $k_c=>$v_c) {
            foreach($new_key as $new_key_key=>$new_key_value) {
                if (isset($v_c[$new_key_key])) {
                    $new_content_arr[$k_c][$new_key_value] = $v_c[$new_key_key];
                }
            }
        }
        $content_arr = $new_content_arr;
		
        return $content_arr;
    }
	
	
	private function u2k($obj) {
	
		if(!is_array($obj)) {
			return mb_convert_encoding($obj,'GBK','UTF-8');//之前是GB18030改为GBK
		}
		$item = array();
		foreach($obj as $k => $v){
			$item[$k] = $this->u2k($v);
		}
		return $item;
	}

	private function search_package($package)
	{
		$result = D('soft')->where(array('package' => $package, 'status'=>1))->find();
		$category_id = trim($result['category_id'],',');
		$types = $this->getTypes();
		$soft_type = $types[$category_id]['parentid'];
		
		$result['stype'] =$soft_type;
		/*if($soft_type==1)
		{
			$result['stype'] = "应用";
		}
		elseif($soft_type==2)
		{
			$result['stype'] = "游戏";
		}
		elseif($soft_type==3)
		{
			$result['stype'] = "电子书";
		}*/
		return $result;
		//return D('soft')->where(array('package' => $package, 'status'=>1))->find();
	}
	public function getTypes()
	{
		$model = M();
		$where=array(
			'status'=>1,
		);
		$types_arr = $model->table('sj_category')->where($where)->select();
		$types = array();
		foreach($types_arr as $k =>$v)
		{
			$types[$v['category_id']] = $v;
		}
		
		/*global $model;
		$option = array(
			'table' => 'sj_category',
			'index' => 'category_id',
			'where' => array(
				'status' => 1
			)
		);
		$tmp = array();
		$types = $model->findAll($option, load_config('cron/read_db'));*/
		
		$tmp = array();
		foreach($types as $key => $val){
			$parentid = $types[$key]['parentid'];
			
			if ($parentid == 1 || $parentid==2 || $parentid==3 || $parentid==0) continue;
			
			if (isset($types[$parentid])) {
				$types[$key]['parentid'] = $types[$parentid]['parentid'];
				$tmp[$parentid] = $parentid;
			}
		}
		foreach($tmp as $k) {
			unset($types[$k]);
		}
		return $types;
	}


}
