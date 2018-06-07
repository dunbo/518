<?php

Class ModelsrankAction extends CommonAction{


	function modelsrank_list()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		import("@.ORG.Page");
		$count = $bbs_model->table('bbs_models_rank')->where(array('status' => 1))->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_models_rank') -> where(array('status' => 1)) -> order('rank,create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
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
		$this -> display();
	}
	function add_modelsrank()
	{
	 
	   if($_POST)
	   {
		 $bbs_model = D('Bbs_manage.Bbs_manage');
		 $models_name = $_POST['models_name'];
		 $rank = floatval($_POST['rank']);
		 $link_address = $_POST['link_address'];
		 if(mb_strlen($models_name,'utf8')>10||mb_strlen($models_name,'utf8')<1)
		 {
		   $this -> error("机型名称必须是1~10个字");
		 }
		 if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
		 {
		   $this -> error("机型地址必须1~255个字");
		 }
		 if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
		    {
			 $this -> error("排序必须是大于0的整数");
			}
			 $data = array(
					'models_name' => $models_name,
					'rank' => $rank,
					'link_address' => $link_address,
					'create_tm' => time(),
					'update_tm' => time(),
					'status' => 1,
				);
			 $result = $bbs_model -> table('bbs_models_rank') -> add($data);
			  if($result)
			  {
				 $this -> writelog("已添加id为{$result}的论坛机型排行管理数据", 'bbs_models_rank', $result,__ACTION__ ,"","add");
				 $this -> assign('jumpUrl',"/index.php/Bbs_manage/Modelsrank/modelsrank_list");
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
	function edit_modelsrank()
	{
        if($_POST)
	    {
			  $bbs_model = D('Bbs_manage.Bbs_manage');
			  $id = $_POST['id'];
			  $models_name = $_POST['models_name'];
			  $rank =floatval($_POST['rank']);
			  $link_address = $_POST['link_address'];
			   if(mb_strlen($models_name,'utf8')>10||mb_strlen($models_name,'utf8')<1)
				{
				   $this -> error("机型名称必须是1~10个字");
				}
				if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
				{
				   $this -> error("机型地址必须1~255个字");
				}
			  if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
			    {
				  $this -> error("排序必须是大于0的整数");
				}
					  $data = array(
						'models_name' => $models_name,
						'rank' => $rank,
						'link_address' => $link_address,
						'update_tm' => time()
					  );
					$log_result = $this -> logcheck(array('id' => $id),'bbs_models_rank',$data,$bbs_model);
					$result = $bbs_model -> table('bbs_models_rank') -> where(array('id' => $id)) -> save($data);
					if($result)
					{
						$this -> writelog("已编辑id为{$id}的论坛pc管理机型排行".$log_result, 'bbs_models_rank', $id,__ACTION__ ,"","edit");
						$this -> assign("jumpUrl","/index.php/Bbs_manage/Modelsrank/modelsrank_list");
						$this -> success("编辑成功");
					}else
					{
						$this -> error("编辑失败");
					}
		}
		else
		{
		  $bbs_model = D('Bbs_manage.Bbs_manage');
		  $id = $_GET['id'];
		  $result = $bbs_model -> table('bbs_models_rank') -> where(array('id' => $id)) -> find();
		  $this -> assign('result',$result);
		  $this -> display();
		}
	}
	function delete_modelsrank()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_models_rank') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的论坛pc管理机型排行", 'bbs_models_rank', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Modelsrank/modelsrank_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}	
	}
    function change_rank()
	{  
	    foreach($_POST['rank'] as $val)
	    { 
			$val1=floatval($val);
		    if($val1<=0 || $val1!=floor($val1)||(string)floatval($val)!=$val)
			{
			  $this -> error("排序必须是大于0的整数");
			}
		}
		  $rank_arr=$_POST['rank'];
		  $id_arr=$_POST['id'];
		  $a=array_combine($id_arr,$rank_arr);
		  $bbs_model = D('Bbs_manage.Bbs_manage');
		   foreach ($a as $k => $v)
		    {
			    
				$rank_new = $bbs_model -> table('bbs_models_rank') -> where(array('id'=>$k))->field("rank") -> select();
				if($v!=$rank_new[0]['rank'])
				{
				 break;
				}
				else
				{
				 $i+=1;
				 continue;
				}
			}
			if($i==count($a))
			{
			 $result=true;
			}
			if($result!=true)
			{
			  foreach($a as $k=>$v)
			    {
					$id=$k;
					$rank=$v;
					$old_rank = $bbs_model -> table('bbs_models_rank') -> where(array('id' => $id)) -> find();
					if($old_rank['rank']>$rank)
					{
					 $data['is_change']=1;
					}
					if($old_rank['rank']<$rank)
					{
					 $data['is_change']=2;
					}
					if($old_rank['rank']==$rank)
					{
					 $data['is_change']=0;
					}
					$data['rank']=$rank;
					$data['update_tm']=time();
					$log_result = $this -> logcheck(array('id' => $id),'bbs_models_rank',$data,$bbs_model);
					$result = $bbs_model -> table('bbs_models_rank') -> where(array('id' => $id)) -> save($data);	
		        }  
            }   			
		  if($result)
			{
			 $this -> writelog("已编辑id为{$id}的论坛pc管理机型排行".$log_result, 'bbs_models_rank', $id,__ACTION__ ,"","edit");
			 $this -> assign("jumpUrl","/index.php/Bbs_manage/Modelsrank/modelsrank_list");
			 $this -> success("编辑成功");
			}
		  else
			{
			 $this -> error("编辑失败");
			}
	}

    function hot_modelsrank_list()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		import("@.ORG.Page");
		$count = $bbs_model->table('bbs_hot_models_rank')->where(array('status' => 1))->count();
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_hot_models_rank') -> where(array('status' => 1)) -> order('rank,create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
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
		$this -> display();
	}
	function hot_add_modelsrank()
	{
	 
	   if($_POST)
	    {
		 $bbs_model = D('Bbs_manage.Bbs_manage');
		 $models_name = $_POST['models_name'];
		 $rank = floatval($_POST['rank']);
		 $link_address = $_POST['link_address'];
		    if(mb_strlen($models_name,'utf8')>10||mb_strlen($models_name,'utf8')<1)
		    {
			  $this -> error("机型名称必须是1~10个字");
			}
			if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
			{
			   $this -> error("机型地址必须1~255个字");
			}
		 if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
		    {
			 $this -> error("排序必须是大于0的整数");
			}
				 $data = array(
						'models_name' => $models_name,
						'rank' => $rank,
						'link_address' => $link_address,
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1,
					);
				 $result = $bbs_model -> table('bbs_hot_models_rank') -> add($data);
				  if($result)
				  {
					 $this -> writelog("已添加id为{$result}的论坛热门机型排行管理数据", 'bbs_hot_models_rank', $result,__ACTION__ ,"","add");
					 $this -> assign('jumpUrl',"/index.php/Bbs_manage/Modelsrank/hot_modelsrank_list");
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
	function hot_edit_modelsrank()
	{
        if($_POST)
		{
		  $bbs_model = D('Bbs_manage.Bbs_manage');
		  $id = $_POST['id'];
		  $models_name = $_POST['models_name'];
		  $rank =floatval($_POST['rank']);
		  $link_address = $_POST['link_address'];
		   if(mb_strlen($models_name,'utf8')>10||mb_strlen($models_name,'utf8')<1)
		    {
			  $this -> error("机型名称必须是1~10个字");
			}
			if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
			{
			   $this -> error("机型地址必须1~255个字");
			}
		  if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
		    {
			 $this -> error("排序必须是大于0的整数");
			}
				  $data = array(
					'models_name' => $models_name,
					'rank' => $rank,
					'link_address' => $link_address,
					'update_tm' => time()
				  );
				$log_result = $this -> logcheck(array('id' => $id),'bbs_hot_models_rank',$data,$bbs_model);
				$result = $bbs_model -> table('bbs_hot_models_rank') -> where(array('id' => $id)) -> save($data);
				if($result){
					$this -> writelog("已编辑id为{$id}的论坛pc管理热门机型排行".$log_result, 'bbs_hot_models_rank', $id,__ACTION__ ,"","edit");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/Modelsrank/hot_modelsrank_list");
					$this -> success("编辑成功");
				}else{
					$this -> error("编辑失败");
				}
		}
		else
		{
		  $bbs_model = D('Bbs_manage.Bbs_manage');
		  $id = $_GET['id'];
		  $result = $bbs_model -> table('bbs_hot_models_rank') -> where(array('id' => $id)) -> find();
		  $this -> assign('result',$result);
		  $this -> display();
		}
	}
	function hot_delete_modelsrank()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_hot_models_rank') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的论坛pc管理热门机型排行", 'bbs_hot_models_rank', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Modelsrank/hot_modelsrank_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}	
	}
    function hot_change_rank()
	{   
	    foreach($_POST['rank'] as $val)
		{
			$val1=floatval($val);
			if($val1<=0 || $val1!=floor($val1)||(string)floatval($val)!=$val)
			{
			  $this -> error("排序必须是大于0的整数");
			}
		}
	  $rank_arr=$_POST['rank'];
	  $id_arr=$_POST['id'];
	  $a=array_combine($id_arr,$rank_arr);
	  foreach($a as $k => $v)
	    {
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$rank_new = $bbs_model -> table('bbs_hot_models_rank') -> where(array('id'=>$k))->field("rank") -> select();
			if($v!=$rank_new[0]['rank'])
			{
			 break;
			}
			else
			{
			 $i+=1;
			 continue;
			}
		}
		if($i==count($a))
		{
		  $result=true;
		}
		if($result!=true)
		{
		    foreach($a as $k=>$v)
			{
				$id=$k;
				$rank=$v;
				$bbs_model = D('Bbs_manage.Bbs_manage');
				$old_rank = $bbs_model -> table('bbs_hot_models_rank') -> where(array('id' => $id)) -> find();
				if($old_rank['rank']>$rank)
				{
				 $data['is_change']=1;
				}
				if($old_rank['rank']<$rank)
				{
				 $data['is_change']=2;
				}
				if($old_rank['rank']==$rank)
				{
				 $data['is_change']=0;
				}
				$data['rank']=$rank;
				$data['update_tm']=time();
				 $log_result = $this -> logcheck(array('id' => $id),'bbs_hot_models_rank',$data,$bbs_model);
				 $result = $bbs_model -> table('bbs_hot_models_rank') -> where(array('id' => $id)) -> save($data);
            }				 
	    }
	  if($result)
		{
		 $this -> writelog("已编辑id为{$id}的论坛pc管理热门机型排行".$log_result, 'bbs_hot_models_rank', $id,__ACTION__ ,"","edit");
		 $this -> assign("jumpUrl","/index.php/Bbs_manage/Modelsrank/hot_modelsrank_list");
		 $this -> success("编辑成功");
		}
	   else
		{
		 $this -> error("编辑失败");
		}	
	}







}