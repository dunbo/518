<?php

Class GameapplyAction extends CommonAction{


	function gameapply_list()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		$special_title = $_POST['special_title'];
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if($special_title){
			$where_go .= " and special_title like '%{$special_title}%'";
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm && $end_tm){
			$where_go .= " and create_tm >= ".strtotime($start_tm)." and create_tm <= ".strtotime($end_tm);
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $bbs_model -> table('bbs_game_apply') -> where($where) -> count();
		import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_game_apply') -> where($where) -> order('rank,create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$show = $Page->show();
		if($_GET['lr']){
			$lr = $_GET['lr'];
		}else{
			$lr = 20;
		}
		if($_GET['p']){
			$p = $_GET['p'];
		}else{
			$p = 1;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('`first', '<<');
		$Page->setConfig('last', '>>');
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> assign('special_title',$special_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> display();
	}
	function add_gameapply()
	{
	 if($_POST)
	   {
		 $bbs_model = D('Bbs_manage.Bbs_manage');
		 $special_name = $_POST['special_name'];
		 $rank = floatval($_POST['rank']);
		 $link_address = $_POST['link_address'];
		 $special_pic = $_FILES['special_pic'];
		 if(mb_strlen($special_name,'utf8')>12||mb_strlen($special_name,'utf8')<1)
		 {
		   $this -> error("专题名称必须是1~12个字");
		 }
		 if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
		 {
		   $this -> error("帖子地址必须1~255个字");
		 }
		 if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
		 {
		   $this -> error("排序必须是大于0的整数");
	     }
		 
		 if ($special_pic['size']) 
		 {  
		    $path = date('Ym/d');
			$config['multi_config']['special_pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
				'img_p_width' => 225,
				'img_p_height' => 125,
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['pic_url'] = $bbs_pic_url;
		 }
		 else
		 {
			$this -> error("请上传专题图片");
		 }
		    $data['special_title']=$special_name;
			$data['rank']=$rank;
			$data['link_address']=$link_address;
			$data['create_tm']=time();
			$data['update_tm']=time();
			$data['status']=1;
			 $result = $bbs_model -> table('bbs_game_apply') -> add($data);
			  if($result)
			  {
				 $this -> writelog("已添加id为{$result}的专题配置的数据", 'bbs_game_apply',$result,__ACTION__ ,'','add');
				 $this -> assign('jumpUrl',"/index.php/Bbs_manage/Gameapply/gameapply_list");
				 $this -> success("添加成功");
			  }
			   else
			   {
				 $this -> error("添加失败");
			   }
	   }
	   else
	   {
	      $this -> display();	
	   }
	}
	function edit_gameapply()
	{
	 if($_POST)
	    {
			  $bbs_model = D('Bbs_manage.Bbs_manage');
			  $id = $_POST['id'];
			  $special_name = $_POST['special_name'];
			  $rank =floatval($_POST['rank']);
			  $link_address = $_POST['link_address'];
			  $special_pic = $_FILES['special_pic'];
			  if(mb_strlen($special_name,'utf8')>12||mb_strlen($special_name,'utf8')<1)
				{
				 $this -> error("专题名称必须是1~12个字");
				}
			  if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
				{
				 $this -> error("帖子地址必须1~255个字");
				}
			  if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
				{
				 $this -> error("排序必须是大于0的整数");
				}
			  if ($special_pic['size']) 
				{  
				 $path = date('Ym/d');
				 $config['multi_config']['special_pic'] = array(
					'savepath' => UPLOAD_PATH . '/img/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 225,
					'img_p_height' => 125,
				     );
				 $list = $this->_uploadapk(0, $config);
				 $bbs_pic_url = $list['image'][0]['url'];
				 $data['pic_url'] = $bbs_pic_url;
				}
			  $data['special_title']=$special_name;
			  $data['rank']=$rank;
			  $data['link_address']=$link_address;
			  $data['update_tm']=time();
			  $data['status']=1;
			  $log_result = $this -> logcheck(array('id' => $id),'bbs_game_apply',$data,$bbs_model);
			  $result = $bbs_model -> table('bbs_game_apply') -> where(array('id' => $id)) -> save($data);
			  if($result)
				{
				 $this -> writelog("已修改id为{$id}的专题配置数据".$log_result, 'bbs_game_apply', $id,__ACTION__ ,'','edit');
				 $this -> assign("jumpUrl","/index.php/Bbs_manage/Gameapply/gameapply_list");
				 $this -> success("编辑成功");
				}
				else
				{
				  $this -> error("编辑失败");
				}
		}
		else
		{
		  $bbs_model = D('Bbs_manage.Bbs_manage');
		  $id = $_GET['id'];
		  $result = $bbs_model -> table('bbs_game_apply') -> where(array('id' => $id)) -> find();
		  $this -> assign('result',$result);
		  $this -> display();
		}
	}
	function delete_gameapply()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_game_apply') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的专题配置中的一条数据", 'bbs_game_apply', $id,__ACTION__ ,'','del');
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Gameapply/gameapply_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}	
	}
	function change_rank()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_game_apply',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_game_apply') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的专题".$log_result, 'bbs_game_apply', $id,__ACTION__ ,'','edit');
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}

}