<?php 
Class AdvertiseAction extends CommonAction{
	private $bbsmodel;
	private $model;
	
	public function _initialize() {
        parent::_initialize();
		$this->bbsmodel = D('Zhiyoo.bbs');
		$this->model = D('Zhiyoo.Zhiyoo');
	}
	
	function active_list(){
		//获取版本
		$version = $_GET['version'] ? $_GET['version'] : 3700;
		if($version){
			$where['version'] = $version;
		}
		$result = $this -> model -> table('zy_advertise') -> where($where) -> order($typeid)->select();
		foreach($result as $key=>$value){
			if($version == 3700){
				if($value['typeid'] == 1) $result[$key]['explain'] .= '<p>广告展示概率：喜多龙'.$value['percent'].'% —— 飞扬'.$value['percent1'].'%</p>';
			}elseif($version == 3800){
				if($value['typeid'] == 1) $result[$key]['explain'] .= '<p>广告展示概率：SDK'.$value['percent'].'% —— 通用API'.$value['percent1'].'%</p>';
			}elseif($version == 3900){
				if($value['typeid'] == 1) $result[$key]['explain'] .= '<p>广告展示概率：SDK'.$value['percent'].'% —— 通用API'.$value['percent1'].'%</p>';
			}
			if($value['typeid'] == 2){
				$op = explode(',',$value['option']);
				if(in_array(1,$op)) $opt .= '智友、';
				if(in_array(2,$op)) $opt .= '论坛';
				$result[$key]['explain'] .= '<p>显示位置：'.$opt.'</p><p>显示概率：'.$value['percent'].'%</p>';
			}
			if($value['typeid'] == 3){
				$result[$key]['explain'] .= '<p>内容间隔数量：'.$value['spacenum'].'</p><p>广告显示数量：'.$value['shownum'].'</p>';
			} 
			if($value['typeid'] == 4){
				$result[$key]['explain'] .= '<p>显示概率：'.$value['percent'].'%</p>';
			}
			if($value['typeid'] == 5){
				$opt = $value['rewardtype'] == 1 ? '金币' : '智豆';
				$result[$key]['explain'] .= '<p>奖励类型：'.$opt.'</p><p>每次奖励数量：'.$value['rewardnum'].'</p>';
			}
		}

		$this -> assign('result',$result);
		$this -> assign('version',$version);
		$this -> display();
	}

	function edit(){
		//下载原生广告模板文件
		if($_GET['down'] == 'down'){
			header("Content-type:text/csv;charset=utf-8");
			header("Content-Disposition:attachment;filename = advertise.csv");
			header('Content-Transfer-Encoding: binary');
			header('Pragma:no-cache');
			$str = '"广告ID","广告尺寸"'."\r\n".'"10011","1280*394"'."\r\n".'"10012","1280*730"';
			$str = iconv("utf-8","gb2312",$str);
			echo $str;
			die;
		}
		$id = $_GET['id'];
		$typeid = $_GET['typeid'];
		if(!typeid) $this -> error('广告类型id不能为空');
		//如果为原生广告判断是否有广告id内容
		if($typeid == 3){
			$count = $this -> model -> table('zy_advertise_typeid3') -> where(array('advertiseid'=>$id)) -> count();
			$this -> assign('count',$count);
		}
		//获取版本和id
		if(!$id) $this -> error('id不能为空');
		if(!$_GET['version']) $this -> error('版本号不能为空');
		$where = array();
		$where['id'] = $_GET['id'];
		$where['version'] = $_GET['version'];
		$info = $this -> model -> table('zy_advertise') -> where($where)->find();
		if($info['typeid'] == 2 && $info['option'] != ''){
			$info['option'] = explode(',',$info['option']);
			if(count($info['option']) > 1){
				$info['opt1'] = 1;
				$info['opt2'] = 2;
			}else{
				$info['opt1'] = $info['option'][0]==1 ? 1 : '';
				$info['opt2'] = $info['option'][0]==2 ? 2 : '';
			}
			
		}
		$this -> assign('info',$info);
		$this -> display();
	}
	
	function do_edit(){
		$id = $_POST['id'];
		$typeid = $_POST['typeid'];
		$version = $_POST['version'];
		if($typeid == 1){//开屏广告
			if($version == 3700){
				$data['advid'] = trim($_POST['advid'], ' ');
				$data['advid1'] = trim($_POST['advid1'], ' ');
			}elseif($version == 3800){
				$data['advid2'] = trim($_POST['advid2'], ' ');
				$data['advid3'] = trim($_POST['advid3'], ' ');
			}elseif($version == 3900){
				$data['advid2'] = trim($_POST['advid2'], ' ');
				$data['advid3'] = trim($_POST['advid3'], ' ');
			}
			$data['percent'] = trim($_POST['percent'],' ');
			$data['percent1'] = trim($_POST['percent1'],' ');

		} elseif($typeid == 2){//插屏广告
			if($version == 3700) $data['advid'] = trim($_POST['advid'], ' ');
			if($version == 3800) $data['advid2'] = trim($_POST['advid2'], ' ');
			if($version == 3900) $data['advid2'] = trim($_POST['advid2'], ' ');
			$data['option'] = trim($_POST['option'], ' ');
			$data['percent'] = trim($_POST['percent'],' ');

		}elseif($typeid == 3){//原生广告
			$data['spacenum'] = trim($_POST['spacenum'], ' '); 
			$data['shownum'] = trim($_POST['shownum'],' ');
			if($_FILES['advid']['size']>0){
				$type = substr($_FILES['advid']['name'],strpos($_FILES['advid']['name'],'.'));
				if($type != '.csv'){
					$this -> error("请上传扩展名为.svc的文件");
				}
				$key = array('adverid','width','height');
				$list = @file($_FILES['advid']['tmp_name']);
				$k = 0;
				$datass = array();
				foreach ($list as $value) { //循环拼装文件数据，判断数据是否正确
					if(empty($value)) continue;
					$k++;
					if($k == 1) continue;
					$values = explode(',',$value);
					$size = explode('*',$values[1]);
					$values[1] = $size[0];
					$values[2] = $size[1];
					$datas = array_combine($key,$values);
					if($datas['adverid'] == '' || $datas['width'] == '' || $datas['height'] == ''){
						$this -> error("导入文件错误");
					}
					$datas['adverid'] = trim($datas['adverid'],' ');
					$datas['addtime'] = time();
					$datas['version'] = $version;
					$datas['advertiseid'] = $id;
					$datass[] = $datas;
				}
			}
			if(!empty($datass)){
				//清空原来数据
				// $where['version'] = $version;
				$where['advertiseid'] = $id;
				$res = $this -> model -> table('zy_advertise_typeid3') -> where($where) -> delete();
				$this -> writelog("智友内容管理-智友广告配置 已清空表数据","zy_advertise_typeid3",$res,__ACTION__ ,"","del");
				//添加新文件数据
				foreach ($datass as $value) {
					$result = $this -> model -> table('zy_advertise_typeid3') -> data($value) -> add();
					$this -> writelog("智友内容管理-智友广告配置 已添加广告id[{$datas['adverid']}]","zy_advertise_typeid3",$result,__ACTION__ ,"","add");
				}	
			}
			
		}elseif($typeid == 4){//banner广告
			if($version == 3700) $data['advid'] = trim($_POST['advid'], ' ');
			if($version == 3800) $data['advid2'] = trim($_POST['advid2'], ' ');
			if($version == 3900) $data['advid2'] = trim($_POST['advid2'], ' ');
			$data['percent'] = trim($_POST['percent'],' ');

		}elseif($typeid == 5){//奖励视频广告
			if($version == 3700) $data['advid'] = trim($_POST['advid'],' ');
			if($version == 3800) $data['advid2'] = trim($_POST['advid2'],' ');
			if($version == 3900) $data['advid2'] = trim($_POST['advid2'],' ');
			$data['rewardtype'] = trim($_POST['rewardtype'], ' '); 
			$data['rewardnum'] = trim($_POST['rewardnum'],' ');
		}

		$res = $this -> model -> table('zy_advertise') -> where(array('id'=>$id)) -> save($data);
		
		$this -> writelog("智友内容管理-智友广告配置 编辑id为{$data['id']}的广告类型","zy_advertise",$data['id'],__ACTION__ ,"",'edit');
		$this -> success("编辑成功");
		
	}

	function changestatus(){
		$typeid = $_GET['typeid'] ? $_GET['typeid'] : '';
		if(!$typeid){
			$this -> error('广告类型不存在');
		}
		$status = $_GET['status'] == 1 ? 0 : 1;
		$txt = $status == 0 ? '停用' : '开启';
		$id = $_GET['id'];
		if(!$id){
			$this -> error('id不能为空');
		}
		$data['status'] = $status;
		$data['id'] = $id;
		$res = $this -> model -> table('zy_advertise') -> save($data);
		if($res){
			$this -> writelog("智友内容管理-智友广告配置 已{$txt}id为{$id}的广告类型","zy_advertise",$id,__ACTION__ ,"","edit");
			$this -> success("{$txt}成功");
		}
	}

}	
	