<?php

Class MobilewallpaperAction extends CommonAction{


	function mobile_classity_list()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		$count = $bbs_model -> table('bbs_mobile_classity') -> where(array('status'=>1)) -> count();
		import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_mobile_classity') -> where(array('status'=>1)) -> order('rank,create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
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
	function add_mobile_classity()
	{
	 if($_POST)
	   {
		 $bbs_model = D('Bbs_manage.Bbs_manage');
		 $name = $_POST['name'];
		 $rank = floatval($_POST['rank']);
		 $link_address = $_POST['link_address'];
		 if(mb_strlen($name,'utf8')>8||mb_strlen($name,'utf8')<1)
		 {
		   $this -> error("名称必须是1~8个字");
		 }
		 if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
		 {
		   $this -> error("地址必须1~255个字");
		 }
		 if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
		 {
		   $this -> error("排序必须是大于0的整数");
	     }
		    $data['classity_name']=$name;
			$data['rank']=$rank;
			$data['link_address']=$link_address;
			$data['create_tm']=time();
			$data['update_tm']=time();
			$data['status']=1;
			$result = $bbs_model -> table('bbs_mobile_classity') -> add($data);
			if($result)
			{
			  $this -> writelog("已添加id为{$result}的手机壁纸分类的数据", 'bbs_mobile_classity', $result,__ACTION__ ,"","add");
			  $this -> assign('jumpUrl',"/index.php/Bbs_manage/Mobilewallpaper/mobile_classity_list");
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
	function edit_mobile_classity()
	{
	 if($_POST)
	    {
			  $bbs_model = D('Bbs_manage.Bbs_manage');
			  $id = $_POST['id'];
			  $name = $_POST['name'];
			  $rank =floatval($_POST['rank']);
			  $link_address = $_POST['link_address'];
			  if(mb_strlen($name,'utf8')>8||mb_strlen($name,'utf8')<1)
			  {
			    $this -> error("名称必须是1~8个字");
			  }
			 if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
			  {
			    $this -> error("地址必须1~255个字");
			  }
			 if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
			  {
			    $this -> error("排序必须是大于0的整数");
			  }
			  $data['classity_name']=$name;
			  $data['rank']=$rank;
			  $data['link_address']=$link_address;
			  $data['update_tm']=time();
			  $data['status']=1;
			  $log_result = $this -> logcheck(array('id' => $id),'bbs_mobile_classity',$data,$bbs_model);
			  $result = $bbs_model -> table('bbs_mobile_classity') -> where(array('id' => $id)) -> save($data);
			  if($result)
				{
				 $this -> writelog("已修改id为{$id}的手机壁纸分类中的一条数据".$log_result, 'bbs_mobile_classity', $id,__ACTION__ ,"","edit");
				 $this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/mobile_classity_list");
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
		  $result = $bbs_model -> table('bbs_mobile_classity') -> where(array('id' => $id)) -> find();
		  $this -> assign('result',$result);
		  $this -> display();
		}
	}
	function delete_mobile_classity()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_mobile_classity') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的手机壁纸分类中的一条数据", 'bbs_mobile_classity', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/mobile_classity_list");
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
		$log_result = $this -> logcheck(array('id' => $id),'bbs_mobile_classity',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_mobile_classity') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的手机壁纸分类中的排序".$log_result, 'bbs_mobile_classity', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	function pic_content_list()
	{
	    $bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_address=M('pu_config');
		$result_pic=$pic_address->table('pu_config')->field('configcontent')->where(array('config_type'=>'BBS_PHONE_PICTURE'))->select();
		$article_title = $_POST['article_title'];
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if($article_title){
			$where_go .= " and article_title like '%{$article_title}%'";
		}
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm && $end_tm){
			$where_go .= " and create_tm >= ".strtotime($start_tm)." and create_tm <= ".strtotime($end_tm);
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $bbs_model -> table('bbs_pic_content') -> where($where) -> count();
		import("@.ORG.Page");
        $param = http_build_query($_GET);
        $Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_pic_content') -> where($where) -> order('rank,create_tm DESC')->limit($Page->firstRow . ',' . $Page->listRows) -> select();
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
		$this -> assign('result_pic',$result_pic);
        $this -> assign("page", $show);
		$this -> assign('result',$result);
		$this -> assign('article_title',$article_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> display();
	}
	function add_pic_content()
	{
	   if($_POST)
	   {
		 $bbs_model = D('Bbs_manage.Bbs_manage');
		 $name = $_POST['name'];
		 $rank = floatval($_POST['rank']);
		 $link_address = $_POST['link_address'];
		 $pic = $_FILES['pic'];
		 if(mb_strlen($name,'utf8')>15||mb_strlen($name,'utf8')<1)
		 {
		   $this -> error("文章标题必须是1~15个字");
		 }
		 if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
		 {
		   $this -> error("帖子地址必须1~255个字");
		 }
		 if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
		 {
		   $this -> error("排序必须是大于0的整数");
	     }
		 
		 if ($pic['size']) 
		 {  
			$halve_wd = getimagesize($pic['tmp_name']);
            $widhig_ha = $halve_wd[3];
            $wh_ha = explode(' ', $widhig_ha);
            $wh1_ha = $wh_ha[0];
            $widths_ha = explode('=', $wh1_ha);
            $width1_ha = substr($widths_ha[1], 0, -1);
            $width_go_ha = substr($width1_ha, 1);
            $hi1_ha = $wh_ha[1];
            $heights_ha = explode('=', $hi1_ha);
            $height1_ha = substr($heights_ha[1], 0, -1);
            $height_go_ha = substr($height1_ha, 1);
			if($rank == 1){
				if ($width_go_ha != 459 || $height_go_ha != 280) {
					$this->error("图标大小不符合条件");
				}
			}else{
				if ($width_go_ha != 147 || $height_go_ha != 130) {
					$this->error("图标大小不符合条件");
				}
			}
		
            if ($pic['type'] != 'image/png' && $pic['type'] != 'image/jpg' && $pic['type'] != 'image/jpeg') {
                $this->error("图标格式错误");
            }
		    $path = date('Ym/d');
			$config['multi_config']['pic'] = array(
				'savepath' => UPLOAD_PATH . '/img/' . $path,
				'saveRule' => 'getmsec',
				'img_p_width' => 360,
				'img_p_height' => 180,
			);
			$list = $this->_uploadapk(0, $config);
			$bbs_pic_url = $list['image'][0]['url'];
			$data['pic_url'] = $bbs_pic_url;
		 }
		 else
		 {
			$this -> error("请上传文章图片");
		 }
		    $data['article_title']=$name;
			$data['rank']=$rank;
			$data['link_address']=$link_address;
			$data['create_tm']=time();
			$data['update_tm']=time();
			$data['status']=1;
			$result = $bbs_model -> table('bbs_pic_content') -> add($data);
			if($result)
			{
				$this -> writelog("已添加id为{$result}的手机壁纸图片内容的数据", 'bbs_pic_content', $result,__ACTION__ ,"","add");
				$this -> assign('jumpUrl',"/index.php/Bbs_manage/Mobilewallpaper/pic_content_list");
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
	function edit_pic_content()
	{
	 if($_POST)
	    {
			  $bbs_model = D('Bbs_manage.Bbs_manage');
			  $id = $_POST['id'];
			  $name = $_POST['name'];
			  $rank =floatval($_POST['rank']);
			  $link_address = $_POST['link_address'];
			  $pic = $_FILES['pic'];
			  if(mb_strlen($name,'utf8')>15||mb_strlen($name,'utf8')<1)
				{
				 $this -> error("文章标题必须是1~15个字");
				}
			  if(mb_strlen($link_address,'utf8')>255||mb_strlen($link_address,'utf8')<1)
				{
				 $this -> error("帖子地址必须1~255个字");
				}
			  if($rank<=0||$rank!=floor($rank)||(string)floatval($_POST['rank'])!=$_POST['rank'])
				{
				 $this -> error("排序必须是大于0的整数");
				}
			  if ($pic['size']) 
				{  
				 $halve_wd = getimagesize($pic['tmp_name']);
				 $widhig_ha = $halve_wd[3];
				 $wh_ha = explode(' ', $widhig_ha);
				 $wh1_ha = $wh_ha[0];
				 $widths_ha = explode('=', $wh1_ha);
				 $width1_ha = substr($widths_ha[1], 0, -1);
				 $width_go_ha = substr($width1_ha, 1);
				 $hi1_ha = $wh_ha[1];
				 $heights_ha = explode('=', $hi1_ha);
				 $height1_ha = substr($heights_ha[1], 0, -1);
				 $height_go_ha = substr($height1_ha, 1);
				 if($rank == 1){
					 if ($width_go_ha != 459 || $height_go_ha != 280) {
							$this->error("图标大小不符合条件");
					 }
				 }else{
					 if ($width_go_ha != 147 || $height_go_ha != 130) {
						 $this->error("图标大小不符合条件");
					 }
				 }
				 if ($pic['type'] != 'image/png' && $pic['type'] != 'image/jpg' && $pic['type'] != 'image/jpeg') {
					$this->error("图标格式错误");
				 }
				 $path = date('Ym/d');
				 $config['multi_config']['pic'] = array(
					'savepath' => UPLOAD_PATH . '/img/' . $path,
					'saveRule' => 'getmsec',
					'img_p_width' => 360,
					'img_p_height' => 180,
				     );
				 $list = $this->_uploadapk(0, $config);
				 $bbs_pic_url = $list['image'][0]['url'];
				 $data['pic_url'] = $bbs_pic_url;
				}
			  $data['article_title']=$name;
			  $data['rank']=$rank;
			  $data['link_address']=$link_address;
			  $data['update_tm']=time();
			  $data['status']=1;
			  $log_result = $this -> logcheck(array('id' => $id),'bbs_pic_content',$data,$bbs_model);
			  $result = $bbs_model -> table('bbs_pic_content') -> where(array('id' => $id)) -> save($data);
			  if($result)
				{
				 $this -> writelog("已修改id为{$id}的手机壁纸图片内容的数据".$log_result, 'bbs_pic_content', $id,__ACTION__ ,"","edit");
				 $this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/pic_content_list");
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
		  $result = $bbs_model -> table('bbs_pic_content') -> where(array('id' => $id)) -> find();
		  $this -> assign('result',$result);
		  $this -> display();
		}
	}
	function delete_pic_content()
	{
	  $bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_pic_content') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的图片内容中的一条数据", 'bbs_pic_content', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/pic_content_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}	
	}
	
    function pic_change_rank()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_pic_content',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_pic_content') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的图片内容中的排序".$log_result, 'bbs_pic_content', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	function more_address_add()
	{
		if($_POST)
		{
		 $pic_address=M('pu_config');
		 $more_address=$_POST['more_address'];
		 if(mb_strlen($more_address,'utf8')>255||mb_strlen($more_address,'utf8')<1)
            {
			  $this -> error("跳转地址必须1~255个字");
		    }
		 $data['configcontent']=$more_address;
		 $data['uptime']=time();
		 $log_result = $this -> logcheck(array('conf_id' => 217),'pu_config',$data,$pic_address);
		 $result_pic=$pic_address->table('pu_config')->where(array('config_type'=>'BBS_PHONE_PICTURE'))->save($data);
		 if($result_pic)
		 {
		    $this -> writelog("已添加id为217的跳转地址".$log_result, 'pu_config','217',__ACTION__ ,"","add");
		    $this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/pic_content_list");
			$this -> success("添加成功");
		 }
		 else
		 {
		    $this -> success("添加失败");
		 }
		}
		else
		{
		 $this->display();
	    }	
	}
	function more_address_modify()
	{  
		if($_POST)
		{
		 $pic_address=M('pu_config');
		 $more_address=$_POST['more_address'];
		 if(mb_strlen($more_address,'utf8')>255||mb_strlen($more_address,'utf8')<1)
          {
			 $this -> error("跳转地址必须1~255个字");
          }
		  $data['configcontent']=$more_address;
		  $data['uptime']=time();
		  $log_result = $this -> logcheck(array('conf_id' => 217),'pu_config',$data,$pic_address);
		 $result_pic=$pic_address->table('pu_config')->where(array('config_type'=>'BBS_PHONE_PICTURE'))->save($data); 
		 if($result_pic)
		 {
		    $this -> writelog("已修改id为217的跳转地址".$log_result, 'pu_config','217',__ACTION__ ,"","edit");
		    $this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/pic_content_list");
			$this -> success("修改成功");
		 }
		 else
		 {
		    $this -> success("修改失败");
		 }
		}
		else
		{
		  $pic_address =M('pu_config');
		  $result_pic = $pic_address -> table('pu_config') ->field('configcontent')->where(array('config_type' => 'BBS_PHONE_PICTURE')) -> find();
		  $this -> assign('result_pic',$result_pic);
		  $this -> display();
	    }	
	}
	function delete_more_address()
	{
         $pic_address=M('pu_config');
		 $data['configcontent']="";
		 $data['uptime']=time();
		 $log_result = $this -> logcheck(array('conf_id' => 217),'pu_config',$data,$pic_address);
		 $result_pic=$pic_address->table('pu_config')->where(array('config_type'=>'BBS_PHONE_PICTURE'))->save($data); 
		if($result_pic)
		{   $this -> writelog("已删除id为217的跳转地址".$log_result, 'pu_config','217',__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Mobilewallpaper/pic_content_list");
			$this -> success("删除成功");
		}
		else
		{
			$this -> error("删除失败");
		}	
	}
}