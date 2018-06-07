<?php 
	Class TopNavConfAction extends CommonAction{
		private $bbsmodel;
		private $model;
	
		public function _initialize() {
            parent::_initialize();
			$this->bbsmodel = D('Zhiyoo.bbs');
			$this->model = D('Zhiyoo.Zhiyoo');
		}
		
		function nav_list(){
			if($_GET['editrank']){
				$this -> assign('editrank',1);
			}
            $position = intval($_GET['position'] ) ? intval($_GET['position'] ) : 1;
			$res = $this -> model -> table('zy_test_topnav_conf') ->where('status>0 and position='.$position)-> order('rank asc')->select();
			foreach($res as $key => $val){
				if($val['type'] == 1 && $val['cid']){
					$name = $this -> model->table('zy_column_conf')->field('name')->where("cid={$val['cid']} and status >=1 ")-> find();
					$jumptxt = !empty($name['name']) ? "栏目：".$name['name'] : '';
				}elseif($val['type'] == 2 && $val['pid']){
					$name = $this -> model->table('zy_column_conf')->field('name')->where("cid={$val['pid']} and status >=1 ")-> find();
					$jumptxt = !empty($name['name']) ? "专题：".$name['name'] : '';
				}elseif($val['type'] == 3){
					$name = $this -> bbsmodel->table('x15_forum_forum')->field('name')->where("fid={$val['fid']}")-> find();
					$jumptxt = "版块：".$name['name'];
					
				}elseif($val['type'] == 4){
					$name = $this -> bbsmodel->table('x15_forum_thread')->field('subject')->where("tid={$val['tid']}")-> find();
					$jumptxt = "主题：".$name['subject'];
					
				}elseif($val['type'] == 5){
					$jumptxt = "内部链接地址：".$val['jumpurl'];
					
				}
				if($val['ref_fid']){
					$refname = $this -> bbsmodel->table('x15_forum_forum')->field('name')->where("fid={$val['ref_fid']}")-> find();
					$res[$key]['refname'] = strip_tags($refname['name']);
				}
				if($val['ref_typeid']){
					$ref_type = $this -> bbsmodel->table('x15_forum_threadclass')->field('name')->where("typeid={$val['ref_typeid']} and fid={$val['ref_fid']}")->find();
					$res[$key]['ref_type'] = strip_tags($ref_type['name']);
				}
				$res[$key]['jumptxt'] = $jumptxt;
				
				
				
			}
			// var_dump($res);
            $this -> assign("imghost", IMGATT_HOST);
			$this -> assign('navlist',$res);
			$this -> display();
		}
		
		function add(){
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform = 1') ->select();
			$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform = 1') ->select();
			$this -> assign('column',$column);
			$this -> assign('pref',$pref);
			$this -> display('add');		
		
		}
		
		function add_submit(){
			$id = $_GET['id'];
			$text = $id ? '编辑' :'添加' ;
			$optype = $_POST['cat'];
			$data['type'] = $optype;
			$subject = trim($_POST['subject']) ? trim($_POST['subject']) : '';
			$data['position'] = intval($_POST['position'])  ? intval($_POST['position']) : 1;
			
			if(!$subject){
				$this ->error("{$text}失败，导航栏必须填写显示名称");
			}
			
			$data['navname'] = $subject;
			$data['tid'] = $data['cid'] = $data['fid'] = $data['pid'] = 0;
			$data['jumpurl'] = '';
			if($optype == 1){
				if(empty($_POST['cid'])){
					$this ->error("{$text}失败，请选择栏目");	
				}		
				$data['poststatus'] = 0;
				// $data['istest'] = 0;
				$data['cid'] = $_POST['cid'];
			}

			if($optype ==  2){
				if(empty($_POST['pid'])){
					$this ->error("{$text}失败，请选择专题");	
				}
				$data['poststatus'] = 0;
				// $data['istest'] = 0;
				$data['pid'] = $_POST['pid'];
			}
			
			if($optype == 3){
				if(empty($_POST['fid'])){
					$this ->error("{$text}失败，请选择板块");	
				}
				$data['poststatus'] = 2;
				// $data['istest'] = 0;
				$data['fid'] = $_POST['fid'];
			}
			
			if($optype == 4 ){
				if(empty($_POST['tid'])){
					$this ->error("{$text}失败，请选择tid");	
				}
				// $data['istest'] = 0;
				$data['poststatus'] = 2;
				$data['tid'] = $_POST['tid'];
			}
			
			if($optype == 5){
				if(empty($_POST['innerurl'])){
					$this ->error("{$text}失败，请填写跳转链接");
				}
				// $data['istest'] = 0;
				$data['poststatus'] = 2;
				$data['jumpurl'] = $_POST['innerurl'];
			}
			
            if($_FILES['img']['size']>0){
                $config = array(
                    'tmp_file_dir' => '/tmp/',
                    'width' => 50,
                    'filesize' => 100000,
                    'real_width' => 50,
                );
                $datedir = '/navicon/';
                $savepath = UPLOAD_PATH.$datedir;
                $imgpath = $this -> _upload($_FILES['img'],$savepath,$config);
                $data['img'] = $datedir.$imgpath;
            }
            
			if($id){
				$data['id'] = $id;
				$result = $this-> model -> table('zy_test_topnav_conf') -> save($data);	
			
			}else{
				$result = $this-> model -> table('zy_test_topnav_conf') -> add($data);	
			}
			
		
				
			if($result){
				if($id){
					$target = $id;
					$type = 'edit';
				}else{
					$target = $result;
					$type = 'add';
				}
				$this -> writelog("智友内容管理-众测顶部导航 {$text}id为{$id}的运营位",'zy_test_topnav_conf',$target,__ACTION__,'',$type);
				$this -> assign('jumpUrl',"/index.php/Zhiyoo/TopNavConf/nav_list/".($data['position']>=2?'position/'.$data['position'].'/':''));
				$this -> success("{$text}成功");
				
			}else{
				
				$this -> error("失败");
			}
			
		}
		
		function edit(){
			$id = $_GET['id'];
			$res = $this->model->table('zy_test_topnav_conf')->where("id={$id}")->find();
			if($res['fid']){
				$fname = $this -> bbsmodel -> table('x15_forum_forum')->field('name')-> where("fid=	{$res['fid']}")->find();
			}
			if($res['tid']){
				$title = $this -> bbsmodel -> table('x15_forum_thread')->field('subject')-> where("tid={$res['tid']}")->find();
			}
			$column = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 0 and platform=1') ->select();
			$pref = $this ->model -> table('zy_column_conf') -> where('status=1 and type = 1 and platform=1') ->select();
            $this -> assign("imghost", IMGATT_HOST);
			$this -> assign('column',$column);
			$this -> assign('pref',$pref);
			$this -> assign('id',$id);
			$this -> assign('info',$res);
			$this -> assign('fname',$fname['name']);
			$this -> assign('title',$title['subject']);
			$this -> display('add');
			
		}
		
		function forum_list(){
			$chkfid = $_GET['fid'] ? $_GET['fid']: 0;
			$grouplist = $this-> bbsmodel -> getForumList('group'); //论坛分区数据
			$forumlist = array();
			$subforumlist = array();
			foreach($grouplist as $index =>  $ginfo){
				$forums = $this ->bbsmodel -> getForumList('forum',$ginfo['fid']); //论坛板块数据
				if(!$forums){
					unset($grouplist[$index]);
					continue;
				}
				$forumlist[$ginfo['fid']] = $forums;
			}
			if($_GET['ref']) $this ->assign('ref',1);
			if($_GET['id']) $this ->assign('id',$_GET['id']);
			$this -> assign('chkfid',$chkfid);
			$this -> assign('grouplist',$grouplist);
			$this -> assign('forumlist',$forumlist);
			$this -> display();
		}
		
		function add_thread(){
			$this -> display();
		}
		
		function getthread(){
			$tid = $_GET['tid'];
			$subject = $this -> bbsmodel -> table('x15_forum_thread') -> field('subject') -> where("tid={$tid}") ->find();
			if($subject){			
				echo $subject['subject'];
			}else{
				echo 0;
			}
		}
		
		function del(){
			$id = $_GET['id'];
			if(!$id){
				$this -> error("没有id值，删除失败");
			}
			$data['id'] = $id;
			$data['status'] = 0;
			$res = $this -> model ->table('zy_test_topnav_conf')->save($data);
			if($res){			
				$this -> writelog("智友内容管理-众测顶部导航 已删除id为{$id}的运营位",'zy_test_topnav_conf',$id,__ACTION__,'','del');
				// $this -> assign('jumpUrl',"/index.php/Zhiyoo/TopNavConf/nav_list/");
				$this -> success("删除成功");
			}else{
				$this -> error("删除失败");
			}
			
		}
		
		function changestatus(){
			$data['id'] = $_GET['id'];
			$txt = $_GET['status'] == 1 ? '停用' :'启用';
			$data['status'] = $_GET['status'] == 1 ? 2 : 1;
			$res = $this -> model -> table('zy_test_topnav_conf') -> save($data);
			if($res){			
				$this -> writelog("智友内容管理-众测顶部导航 已{$txt}id为{$_GET['id']}的运营位",'zy_test_topnav_conf',$_GET['id'],__ACTION__,'','edit');
				// $this -> assign('jumpUrl',"/index.php/Zhiyoo/TopNavConf/nav_list/");
				$this -> success("{$txt}成功");
			}else{
				$this -> error("{$txt}失败");
			}
		}
		
		function changepstatus(){
			$data['id'] = $_GET['id'];
			$txt = $_GET['pstatus'] == 1 ? '不允许发表' :'允许发表';
			$data['poststatus'] = $_GET['pstatus'] == 1 ? 0 : 1;
			$res = $this -> model -> table('zy_test_topnav_conf') -> save($data);
			if($res){			
				$this -> writelog("智友内容管理-众测顶部导航 id为{$_GET['id']}的运营位已{$txt}",'zy_test_topnav_conf',$_GET['id'],__ACTION__,'','edit');
				// $this -> assign('jumpUrl',"/index.php/Zhiyoo/TopNavConf/nav_list/");
				$this -> success("修改{$txt}成功");
			}else{
				$this -> error("修改{$txt}失败");
			}
			
			
			
		}
		
		function edit_rank(){
			foreach($_POST['rank'] as $id=>$rank){
				$data['id'] = $id;
				$data['rank'] = $rank;
				$res = $this -> model -> table('zy_test_topnav_conf') -> save($data);
				if($res){
					$this -> writelog("智友内容管理-众测顶部导航 以修改id为{$id}的运营位的优先级",'zy_test_topnav_conf',$id,__ACTION__,'','edit');
				}
			}
			$this -> assign('jumpUrl',"/index.php/Zhiyoo/TopNavConf/nav_list/".($_GET['position']>=2?'position/'.$_GET['position'].'/':''));
			$this -> success('编辑优先级成功');
		}
		
		function changetest(){
			$data['id'] = $_GET['id'];
			$txt = $_GET['istest'] == 1 ? '修改为不是众测栏目' :'修改为是众测栏目';
			if($_GET['istest'] == 0){
				$checktest = $this -> model -> table('zy_test_topnav_conf')->where('status>0 and istest=1')-> count();
				if($checktest){
					$this -> error('操作失败，已存在一个众测栏目');
					
				}
			}
			$data['istest'] = $_GET['istest'] == 1 ? 0 : 1;
			$res = $this -> model -> table('zy_test_topnav_conf') -> save($data);
			if($res){			
				$this -> writelog("智友内容管理-众测顶部导航 id为{$data['id']}的运营位已{$txt}",'zy_test_topnav_conf',$data['id'],__ACTION__,'','edit');
				// $this -> assign('jumpUrl',"/index.php/Zhiyoo/TopNavConf/nav_list/");
				$this -> success("{$txt}成功");
			}else{
				$this -> error("{$txt}失败");
			}
			
			
			
		}
		
		//配置晒机和摄影相对应的论坛版块和分类
		function changereforum(){
			$id = $_GET['id'];
			$data['id'] = $id;
			if($_GET['fid']){
				$data['ref_fid'] = $_GET['fid'];
				$res = $this ->model ->table('zy_test_topnav_conf') -> where("id={$id} ") ->find();
				if($res['ref_fid'] == $data['ref_fid']){
					echo 0;
				}else{
					//如果修改版块了，把对应的分类置为0
					$data['ref_typeid'] = 0;
					$res = $this -> model -> table("zy_test_topnav_conf") ->save($data);	
					$this -> writelog("智友内容管理-众测顶部导航 id为{$id}的运营位的对应版块被修改为{$data['ref_typeid']}",'zy_test_topnav_conf',$id,__ACTION__,'','edit');
					echo 1;
				}
			
			}
			if($_GET['typeid']){
				$data['ref_typeid'] = $_GET['typeid'];
				$res = $this -> model -> table("zy_test_topnav_conf") ->save($data);
				if($res){
					$this -> writelog("智友内容管理-众测顶部导航 id为{$id}的运营位的对应分类被修改为{$data['ref_typeid']}",'zy_test_topnav_conf',$id,__ACTION__,'','edit');
					echo 1;
				}else{
					echo 0;
				}
			}
		}
		
		function show_type(){
			$id = $_GET['id'];
			$res = $this -> model -> table("zy_test_topnav_conf")->field('ref_fid') -> where("id={$id}") -> find();
			if(empty($res['ref_fid'])){
				$this ->assign("tips","该栏目还没有选择对应的板块");
				
			}
			// $result = $this -> bbsmodel->table('x15_forum_threadclass')->where('fid='.$res['ref_fid'])->order('displayorder asc')->select();
			$result = $this -> bbsmodel->getForumtTypeByFid($res['ref_fid']);
			$realtype = array();
			foreach($result as $k => $val){
				$realtype[$k]['typeid'] = $k;
				$realtype[$k]['name'] = $val;
			}
			if($_GET['typeid']) $this->assign('typeid',$_GET['typeid']);
			$this ->assign('types',$realtype);
			$this ->assign('id',$id);
			$this->display();
		}
		
		function delimg(){
			$id = $_GET['id'];
			$res = $this -> model -> table("zy_test_topnav_conf") -> where("id={$id}") -> save(array('img'=>''));
            if($result !== false) {
                $this -> writelog("智友内容管理-众测顶部导航 已删除标签id为{$id}的图片","zy_test_topnav_conf",$id,__ACTION__ ,"","del");
                echo json_encode(array('su'=>1));
            }else{
                echo json_encode(array('su'=>0));
            }
		}
		
		
        protected function _upload($file,$savepath,$config){
            include dirname(realpath(__FILE__)).'/imagemagick.php';
            return  up_load_thumbimg($file,$savepath,$config);
        }
	}