<?php 

Class NameWhiteAction extends CommonAction{
	
	function white_list(){
		//获取搜索条件
		$where_sql = array('status'=>1);
		$start = strtotime($_GET['start_tm']);
		$end = strtotime($_GET['end_tm']);
		$uid = $_GET['uid'];
		$nickname = $_GET['nickname'];
		//拼接搜索条件
		if($uid){
			$where_sql['uid'] = $uid;
		}
		if($nickname){
			$where_sql['_string'] = "nickname like '%{$nickname}%'";
		}
		if($start){
			$where_sql['addtime'][] = array('egt',$start);
		}
		if($end){
			$where_sql['addtime'][] = array('elt',$end);
		}
		
		//排序条件获取拼接 
		$filed = $_GET['filed'];
		$order = $_GET['order'];
		$orderby = 'uid asc';
		if($filed){
			$orderby = $filed.' '.$order;
		}
		if($order){
			if($order == 'desc'){
				$order = 'asc';
			}else{
				$order = 'desc';
			} 
		}else{
			$order = 'desc';
		}

		$model = D('Zhiyoo.Zhiyoo');

		//查询列表信息
		$list = $model -> table('zy_white_user') ->where($where_sql) ->order($orderby) ->select();
		
		$this->assign('order',$order);
		$this->assign('list',$list);
		$this -> display();
	}
	
	function add(){
		$this -> display();
	}

	function edit(){
		$id = $_GET['id'];
		$model = D('Zhiyoo.Zhiyoo');
		$result = $model ->table('zy_white_user') ->where(array('status'=>1,'id'=>$id)) ->find();

		$this->assign('result',$result);
		$this -> display();
	}
	
	function doedit(){

		$model = D('Zhiyoo.Zhiyoo');
		$action = $_GET['action'];
		if($action == 'edit'){
			$id = $_POST['id'];
			$data['uid'] = $_POST['uid'];
			$data['nickname'] = $_POST['nickname'];
			
			//判断UID是否存在
			$exituid = $model->table("zy_white_user")->where(array('uid'=>$_POST['uid'],'status'=>1))->find();
			if(!empty($exituid) && $id != $exituid['id']){
				$this->error("此UID已经存在！");
			}
			//判断昵称是否存在
			$exitname = $model->table("zy_white_user")->where(array('nickname'=>$_POST['nickname'],'status'=>1))->find();
			if(!empty($exitname) && $id != $exitname['id']){
				$this->error("此昵称已经存在！");
			}

			//图片保存路径
			$datedir = '/zhiyoo/';
			$savepath = UPLOAD_PATH.$datedir;
			if($_FILES['img1']['size']>0){
				$this->check_image($_FILES['img1']);
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 3000,
					'filesize' => 100000,
					'real_width' => 3000,
				);
				$imgpath = $this -> _upload($_FILES['img1'],$savepath,$config);
				$data['img_path1'] = $datedir.$imgpath;
			}
			if($_FILES['img2']['size']>0){
				$this->check_image($_FILES['img2']);
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 3000,
					'filesize' => 100000,
					'real_width' => 3000,
				);
				$imgpath = $this -> _upload($_FILES['img2'],$savepath,$config);
				$data['img_path2'] = $datedir.$imgpath;
			}
			if($_FILES['img3']['size']>0){
				$this->check_image($_FILES['img3']);
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 3000,
					'filesize' => 100000,
					'real_width' => 3000,
				);
				$imgpath = $this -> _upload($_FILES['img3'],$savepath,$config);
				$data['img_path3'] = $datedir.$imgpath;
			}

			$data['remake'] = $_POST['remake'];
			$data['upname'] = $_SESSION['admin']['admin_user_name'];
			$data['updatetime'] = time();
			$result = $model->table("zy_white_user")->where(array('id'=>$id))->save($data);

			$this -> writelog("智友内容管理-实名认证/白名单管理 已修改ID为{$id}的用户为{$uid}","zy_white_user",$id,__ACTION__ ,"","edit");
		}
		elseif($action == 'del'){
			$id  = $_GET['id'];
			$uid  = $_GET['uid'];
			$result = $model->table("zy_white_user")->where(array('id'=>$id))->save(array('status'=>0));
			$this -> writelog("智友内容管理-实名认证/白名单管理 已删除用户UID{$uid}","zy_white_user",$uid,__ACTION__ ,"","del");
		}
		elseif($action == 'add'){
			$data['uid'] = trim($_POST['uid'],'');
			$data['nickname'] = $_POST['nickname'];
			//判断UID是否存在
			$exituid = $model->table("zy_white_user")->where(array('uid'=>$_POST['uid'],'status'=>1))->find();
			if(!empty($exituid)){
				$this->error("此UID已经存在！");
			}
			//判断昵称是否存在
			$exitname = $model->table("zy_white_user")->where(array('nickname'=>$_POST['nickname'],'status'=>1))->find();
			if(!empty($exitname)){
				$this->error("此昵称已经存在！");
			}
			//图片保存路径
			$datedir = '/zhiyoo/';
			$savepath = UPLOAD_PATH.$datedir;
			if($_FILES['img1']['size']>0){
				$this->check_image($_FILES['img1']);
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 3000,
					'filesize' => 100000,
					'real_width' => 3000,
				);
				$imgpath = $this -> _upload($_FILES['img1'],$savepath,$config);
				$data['img_path1'] = $datedir.$imgpath;
			}
			if($_FILES['img2']['size']>0){
				$this->check_image($_FILES['img2']);
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 3000,
					'filesize' => 100000,
					'real_width' => 3000,
				);
				$imgpath = $this -> _upload($_FILES['img2'],$savepath,$config);
				$data['img_path2'] = $datedir.$imgpath;
			}
			if($_FILES['img3']['size']>0){
				$this->check_image($_FILES['img3']);
				$config = array(
					'tmp_file_dir' => '/tmp/',
					'width' => 3000,
					'filesize' => 100000,
					'real_width' => 3000,
				);
				$imgpath = $this -> _upload($_FILES['img3'],$savepath,$config);
				$data['img_path3'] = $datedir.$imgpath;
			}

			$data['remake'] = $_POST['remake'] ? $_POST['remake'] :'';
			$data['addtime'] = time();
			$data['updatetime'] = time();
			$data['upname'] = $_SESSION['admin']['admin_user_name'];
			$data['status'] = 1;
          
			$result = $model -> table('zy_white_user') -> data($data) -> add();

			$this -> writelog("智友内容管理-实名认证/白名单管理 已添加用户[{$_POST['nickname']}]","zy_white_user",$result,__ACTION__ ,"","add");
			
		}

		$this->assign("jumpUrl", "/index.php/Zhiyoo/NameWhite/white_list");
		if($result !== false){
			if($action != 'add')
			$this->success("修改成功！");
			else $this->success("添加成功！");
		}else $this->error("修改失败！");
	}

	function delimg(){
		$model = D('Zhiyoo.Zhiyoo');
        $id = intval($_GET['id']);
        //更新操作人员信息
        $save['updatetime'] = time();
        $save['upname'] = $_SESSION['admin']['admin_user_name'];
        if($_GET['path'] == 1) $save['img_path1'] = '';
        elseif($_GET['path'] == 2) $save['img_path2'] = '';
        elseif($_GET['path'] == 3) $save['img_path3'] = '';
        $result = $model->table('zy_white_user') ->where(array('id'=>$id))->save($save);
        if($result) {
            $this -> writelog("智友内容管理 已删除id为{$id}UID为{$uid}的图片","zy_white_user",$uid,__ACTION__ ,"","del");
            $this -> success('删除图片成功');
        }else{
            $this ->error('删除图片失败');
        }
	}

	protected function _upload($file,$savepath,$config){
		include_once dirname(realpath(__FILE__)).'/imagemagick.php';
		return  up_load_thumbimg($file,$savepath,$config);
	}

	public function check_image($file){
		$file_ext = strtolower(substr($file['type'], strrpos($file['type'], '/')+1));
		if(!in_array($file_ext,array('jpg','jpeg','png'))){
			$this->error('图片格式错误，只支持png、jpg、jpeg格式');
		}
	}

}