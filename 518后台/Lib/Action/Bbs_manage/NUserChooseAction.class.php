<?php

Class NUserChooseAction extends CommonAction{
	
	private $max_news = 8;
	private $max_pic_text = 8;
    private $pic_width;
    private $pic_height;
    private $pic_max_MB;
    private $pic_max_size;
    
    public function __construct() {
        parent::__construct();
        $this->pic_width = 308;
        $this->pic_height = 166;
        $this->pic_max_MB = 2;
        $this->pic_max_size = $this->pic_max_MB*1024*1024;
    }

	function news_show_list()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_title = $_GET['pic_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$rank = $_GET['rank'];
		$ad_id = $_GET['ad_id'];
		$search=$_GET['search'];
		$schedule=$_GET['schedule'];
	
		
		//已过期、已上线、待上线状态
		if($_GET['my_time'])
		{
			$my_time = $_GET['my_time'];
		}
		else
		{
			$my_time = 2;
		}
		if($my_time == 1)
		{
			$where_g .= " and end_time <".time();
		}
		elseif($my_time == 2)
		{
			$where_g .= " and start_time <= ".time()." and end_time >= ".time();
		}
		elseif($my_time == 3)
		{
			$where_g .= " and start_time > ".time();
		}
		
		//搜索
		if(strtotime($start_tm) > strtotime($end_tm))
		{
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm && $end_tm)
		{
			$where_go .= " and start_time <= ".strtotime($end_tm)." and end_time >= ".strtotime($start_tm);
		}
		if($rank!="全部")
		{
			$where_go .= " and rank = {$rank}";
		}
		if($ad_id)
		{
			$where_go .= " and ad_id = {$ad_id}";
		}
		if($pic_title)
		{
			$where_go .= " and news_detail.recomment_title like '%{$pic_title}%'";
		}
		
		if($my_time)
		{
			if($my_time==2)
			{
				$model=M();
				$pu_result=$model->table('pu_config')->where("config_type='ZHIYOU_TIME'") ->find();
				$time=$pu_result['configcontent']*60;
				$where['_string'] = "news_detail.status = 1 and news_show.status = 1 and news_show.ad_id = news_detail.id".$where_g;
				$count = $bbs_model -> table('new_bbs_news_show news_show,new_bbs_news_detail news_detail') ->where($where) -> count();
				import("@.ORG.Page");
				$param = http_build_query($_GET);
				$Page = new Page($count, 20, $param);
				$result = $bbs_model -> field('news_show.*,news_detail.recomment_title ,news_detail.link_address') -> table('new_bbs_news_show news_show,new_bbs_news_detail news_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank asc') -> select();
			}
			else
			{
				if($schedule==1)
				{
					$where['_string'] = "news_detail.status = 1 and news_show.status = 1 and news_show.ad_id = news_detail.id and rank=".$rank.$where_g;
				}
				else
				{
					$where['_string'] = "news_detail.status = 1 and news_show.status = 1 and news_show.ad_id = news_detail.id".$where_g;
				}
				$count = $bbs_model -> table('new_bbs_news_show news_show,new_bbs_news_detail news_detail') -> where($where) -> count();
				import("@.ORG.Page");
				$param = http_build_query($_GET);
				$Page = new Page($count, 20, $param);
				if($my_time==3)
				{
					$result = $bbs_model -> field('news_show.*,news_detail.recomment_title ,news_detail.link_address') -> table('new_bbs_news_show news_show,new_bbs_news_detail news_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('start_time asc,end_time asc') -> select();
				}
				else
				{
					$result = $bbs_model -> field('news_show.*,news_detail.recomment_title ,news_detail.link_address') -> table('new_bbs_news_show news_show,new_bbs_news_detail news_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('start_time desc,end_time desc') -> select();
				}
			}
		}
		//区别搜索和三个状态
		if($search==1)
		{
			$my_time=0;
			$where['_string'] = "news_detail.status = 1 and news_show.status = 1 and news_show.ad_id = news_detail.id".$where_go;
			$count = $bbs_model -> table('new_bbs_news_show show,new_bbs_news_detail detail') -> where($where) -> count();
			import("@.ORG.Page");
			$param = http_build_query($_GET);
			$Page = new Page($count, 20, $param);
			$result = $bbs_model -> field('news_show.*,news_detail.recomment_title ,news_detail.link_address') -> table('new_bbs_news_show news_show,new_bbs_news_detail news_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('start_time DESC,end_time DESC,id asc') -> select();
		}
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
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('pic_title',$pic_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('rank',$rank);
		$this -> assign('ad_id',$ad_id);
		$this -> assign('result',$result);
		$this -> assign('time',$time);
		$this -> assign('num', $this->max_news);
		$this -> assign('my_time',$my_time);
		$this -> display();
	}

	function add_news_show()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$start_tm=strtotime($_POST['start_tm']);
			$end_tm=strtotime($_POST['end_tm']);
			if($_POST['ad_id'])
			{
				$result=$bbs_model->table('new_bbs_news_detail') -> where(array('id'=>$_POST['ad_id'],'status'=>1)) -> select();
				if($result)
				{
					$ad_id = $_POST['ad_id'];
				}
				else
				{
					$this -> error("广告ID不存在");
				}
			}
			else
			{
				$this -> error("请填写广告ID");
			}
			if($_POST['rank'])
			{
				$rank = $_POST['rank'];
				if($rank <= 0||$rank > $this->max_news)
				{
					$this -> error("排序值错误");
				}
			}
			else
			{
				$this -> error("请选择一个位置");
			}
			if($start_tm&&$end_tm)
			{
				if($start_tm > $end_tm)
				{
					$this -> error("开始时间不能大于结束时间");
				}
				$where['_string'] = " status =1 and start_time <= ".$end_tm." and end_time >=".$start_tm." and ((ad_id =".$ad_id.") or (rank =".$rank."))";
				$result = $bbs_model -> table('new_bbs_news_show') ->where($where) -> select();
				if($result)
				{
					//$this -> error("您添加的时间和广告ID为【{$result[0]['ad_id']}】的记录有冲突");
					$this -> error("排期冲突，请调整");
				}
				else
				{
					$data['start_time']=$start_tm;
					$data['end_time']=$end_tm;
					if($end_tm<time())
					{
						$my_time=1;
					}
					elseif($start_tm>time())
					{
						$my_time=3;
					}
					else
					{
						$my_time=2;
					}
				}
			}
			else
			{
				$this -> error("请填写开始时间和结束时间");
			}
			$data['rank'] = $rank;
			$data['ad_id'] =$ad_id;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;
			$result = $bbs_model -> table('new_bbs_news_show') -> add($data);
			if($result){
				$this -> writelog("已添加id为{$result}的网友精选新闻排期记录","new_bbs_news_show",$result,__ACTION__ ,"","add");
				$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_show_list/my_time/".$my_time);
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}
		else
		{
			$this -> assign('num', $this->max_news);
			$this -> display();
		}
	}
	
	function edit_news_show()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_POST['id'];
			$start_tm=strtotime($_POST['start_tm']);
			$end_tm=strtotime($_POST['end_tm']);
			$life=$_POST['life'];
			$my_time=$_POST['my_time'];
			
			if($my_time==0)
			{
				if($end_tm<time())
				{
					$my_time=1;
				}
				elseif($start_tm>time())
				{
					$my_time=3;
				}
				else
				{
					$my_time=2;
				}
			}
			$lr = $_POST['lr'];
			if($lr){
				$where_go .= "/lr/{$lr}";
			}
			$p = $_POST['p'];
			if($p){
				$where_go .= "/p/{$p}";
			}
			if($_POST['ad_id'])
			{
				$result=$bbs_model->table('new_bbs_news_detail') -> where(array('id'=>$_POST['ad_id'],'status'=>1)) -> select();
				if($result)
				{
					$ad_id = $_POST['ad_id'];
				}
				else
				{
					$this -> error("广告ID不存在");
				}
			}
			else
			{
				$this -> error("请填写广告ID");
			}
			if($_POST['rank'])
			{
				$rank = $_POST['rank'];
				if($rank <= 0||$rank >$this->max_news)
				{
					$this -> error("排序值错误");
				}
			}
			else
			{
				$this -> error("请选择一个位置");
			}
			if($start_tm&&$end_tm)
			{
				if($start_tm >$end_tm)
				{
					$this -> error("开始时间不能大于结束时间");
				}
				if($life==1)
				{
					if($start_tm<time())
					{
						$this -> error("复制上线的开始时间必须大于当前时间");
					}
				}
				$where['_string'] = " status =1 and start_time <= ".$end_tm." and end_time >=".$start_tm." and id !=".$id." and ((ad_id =".$ad_id.") or (rank =".$rank."))";
				$result = $bbs_model -> table('new_bbs_news_show') ->where($where) -> select();
				if($result)
				{
					//$this -> error("您编辑的时间和后台广告ID为【{$result[0]['ad_id']}】的记录有冲突");
					$this -> error("排期冲突，请调整");
				}
				else
				{
					$data['start_time'] = $start_tm;
					$data['end_time'] = $end_tm;
				}
			}
			else
			{
				$this -> error("请填写开始时间和结束时间");
			}
			$data['ad_id'] = $ad_id;
			$data['rank'] = $rank;
			$data['update_tm'] = time();
			if($life==1)
			{
				$data['create_tm'] = time();
				$data['status']= 1;
				$result = $bbs_model -> table('new_bbs_news_show') -> add($data);
				if($result){
					$this -> writelog("已复制上线id为{$result}的新闻","new_bbs_news_show",$result,__ACTION__ ,"","add");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_show_list/my_time/".$my_time);
					$this -> success("复制上线成功");
				}
				else
				{
					$this -> error("复制上线失败");
				}
			}
			else
			{
				$log_result = $this -> logcheck(array('id' => $id),'new_bbs_news_show',$data,$bbs_model);
				$result = $bbs_model -> table('new_bbs_news_show') -> where(array('id' => $id)) -> save($data);
				if($result){
					$this -> writelog("已编辑id为{$id}的轮播图".$log_result,"new_bbs_news_show",$id,__ACTION__ ,"","edit");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_show_list/my_time/".$my_time.$where_go);
					$this -> success("编辑成功");
				}
				else{
					$this -> error("编辑失败");
				}
			}
		}
		else
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_GET['id'];
			$lr = $_GET['lr'];
			$p = $_GET['p'];
			$result = $bbs_model -> table('new_bbs_news_show') -> where(array('id' => $id)) -> find();
			$result_detail = $bbs_model -> table('new_bbs_news_detail') -> where(array('id' => $result['ad_id'])) -> find();
			$this -> assign("result",$result);
			$this -> assign("re_detail",$result_detail);
			$this -> assign('num', $this->max_news);
			$this -> assign("lr",$lr);
			$this -> assign("p",$p);
			$this -> display();
		}
	}
	
	function del_news_show()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$lr = $_GET['lr'];
		$my_time=$_GET['my_time'];
		
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('new_bbs_news_show') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的轮播图","new_bbs_news_show",$id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_show_list/my_time/".$my_time.$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	function get_news_detail()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result=$bbs_model->table('new_bbs_news_detail') -> where(array('id'=>$id,'status'=>1)) -> select();
		if($result)
		{
			$data = array(
			'title' => $result[0]['recomment_title'],
			'link_address' => $result[0]['link_address'],
			//'pic_url' => IMGATT_HOST.$result[0]['pic_url'],
			);
			echo json_encode($data);
		}
		else
		{
			echo 2;
			return 2;
		}
	}
	function news_show_over()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$reasons=$_POST['reasons'];
			$id=$_POST['id'];
			if($reasons)
			{
				$data['note']=$reasons;
				$data['end_time']=time();
				$data['update_tm']=time();
				$log_result = $this -> logcheck(array('id' => $id),'new_bbs_news_show',$data,$bbs_model);
				$result = $bbs_model -> table('new_bbs_news_show') -> where(array('id' => $id)) -> save($data);
				if($result)
				{
					$this -> writelog("已强制结束id为{$id}的新闻".$log_result,"new_bbs_news_show",$id,__ACTION__ ,"","edit");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_show_list".$where_go);
					$this -> success("编辑成功");
				}
				else
				{
					$this -> error("编辑失败");
				}
			}
			else
			{
				$this -> error("结束理由不能为空");
			}
		}
		else
		{
			$this -> display();
		}
	}
	
	function news_detail_list()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_title = $_GET['pic_title'];
		$ad_id = $_GET['ad_id'];
		$create_time=$_GET['create_tm'];
		$end_time=$_GET['end_tm'];
		$create_tm = strtotime($_GET['create_tm']);
		$end_tm = strtotime($_GET['end_tm']);
		
		
		if($create_tm)
		{
			$where_go .= " and create_tm >= {$create_tm}";
		}
		if($end_tm)
		{
			$where_go .= " and create_tm <= {$end_tm}";
		}
		
		if($create_tm && $end_tm && $create_tm > $end_tm)
		{
			$this -> error("添加时间的开始时间不能大于添加时间的结束时间");
		}
		
		if($pic_title)
		{
			$where_go .= " and recomment_title like '%{$pic_title}%'";
		}
		if($ad_id)
		{
			$where_go .= " and id = {$ad_id}";
		}
		
		$where['_string'] = "status = 1 ".$where_go;
		$count = $bbs_model -> table('new_bbs_news_detail') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('new_bbs_news_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();
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
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('pic_title',$pic_title);
		$this -> assign('ad_id',$ad_id);
		$this -> assign('create_tm',$create_time);
		$this -> assign('end_tm',$end_time);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_news_detail()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$recomment_title = $_POST['recomment_title'];
			if($_POST['link_address'])
			{
				if (!$this->check_url($_POST['link_address'])) 
				{
					$this->error('请填写有效的链接地址');
				}
				else
				{
					$link_address = $_POST['link_address'];
				}
			}
			else
			{
				$this -> error("链接地址不能为空");
			}
			$data['recomment_title'] = $recomment_title;
			$data['link_address'] = $link_address;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;
			$result = $bbs_model -> table('new_bbs_news_detail') -> add($data);
			if($result){
				$this -> writelog("已添加id为{$result}的网友精选新闻素材详情","new_bbs_news_detail",$result,__ACTION__ ,"","add");
				$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_detail_list");
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}
		else
		{
			$this -> display();
		}
	}
	
	function edit_news_detail()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_POST['id'];
			
			$lr = $_POST['lr'];
			if($lr){
				$where_go .= "/lr/{$lr}";
			}
			$p = $_POST['p'];
			if($p){
				$where_go .= "/p/{$p}";
			}
			$recomment_title = $_POST['recomment_title'];
			if($_POST['link_address'])
			{
				if (!$this->check_url($_POST['link_address'])) 
				{
					$this->error('请填写有效的链接地址');
				}
				else
				{
					$link_address = $_POST['link_address'];
				}
			}
			else
			{
				$this -> error("请填写链接地址");
			}
			$data['recomment_title'] = $recomment_title;
			$data['link_address'] = $link_address;
			$data['update_tm'] = time();
			$log_result = $this -> logcheck(array('id' => $id),'new_bbs_news_detail',$data,$bbs_model);
			$result = $bbs_model -> table('new_bbs_news_detail') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑id为{$id}的网友精选新闻素材详情".$log_result,"new_bbs_news_detail",$id,__ACTION__ ,"","edit");
				$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_detail_list".$where_go);
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
		else
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_GET['id'];
			$lr = $_GET['lr'];
			$p = $_GET['p'];
			$result = $bbs_model -> table('new_bbs_news_detail') -> where(array('id' => $id)) -> find();
			$this -> assign("result",$result);
			$this -> assign("lr",$lr);
			$this -> assign("p",$p);
			$this -> display();
		}
	}
	
	function del_news_detail()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$lr = $_GET['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('new_bbs_news_detail') -> where(array('id' => $id)) -> save(array('status' => 0));
		$result_show = $bbs_model -> table('new_bbs_news_show') -> where(array('ad_id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的网友精选新闻素材详情","new_bbs_news_detail",$id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/news_detail_list".$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	//检测链接地址是否有效
	public function check_url($url) 
	{
        $reg = "/^((http:\/\/)|(https:\/\/))([\w\d-]+\.)+[\w-]+(\/[\x{4e00}-\x{9fa5}\d\w-.\/?%&=]*)?$/iu";
        if (!preg_match($reg, $url)) {
            return false;
        }
        return true;
    }
	//图文
	function pic_show_list()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_title = $_GET['pic_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$rank = $_GET['rank'];
		$ad_id = $_GET['ad_id'];
		$search=$_GET['search'];
		$schedule=$_GET['schedule'];
	
		
		//已过期、已上线、待上线状态
		if($_GET['my_time'])
		{
			$my_time = $_GET['my_time'];
		}
		else
		{
			$my_time = 2;
		}
		if($my_time == 1)
		{
			$where_g .= " and end_time <".time();
		}
		elseif($my_time == 2)
		{
			$where_g .= " and start_time <= ".time()." and end_time >= ".time();
		}
		elseif($my_time == 3)
		{
			$where_g .= " and start_time > ".time();
		}
		
		//搜索
		if(strtotime($start_tm) > strtotime($end_tm))
		{
			$this -> error("开始时间不能大于结束时间");
		}
		if($start_tm && $end_tm)
		{
			$where_go .= " and start_time <= ".strtotime($end_tm)." and end_time >= ".strtotime($start_tm);
		}
		if($rank!="全部")
		{	
			$where_go .= " and rank = {$rank}";
		}
		if($ad_id)
		{
			$where_go .= " and ad_id = {$ad_id}";
		}
		if($pic_title)
		{
			$where_go .= " and pic_detail.recomment_title like '%{$pic_title}%'";
		}
		
		if($my_time)
		{
			if($my_time==2)
			{
				$model=M();
				$pu_result=$model->table('pu_config')->where("config_type='ZHIYOU_TIME'") ->find();
				$time=$pu_result['configcontent']*60;
				$where['_string'] = "pic_detail.status = 1 and pic_show.status = 1 and pic_show.ad_id = pic_detail.id".$where_g;
				$count = $bbs_model -> table('new_bbs_pic_show pic_show,new_bbs_pic_detail pic_detail') ->where($where) -> count();
				import("@.ORG.Page");
				$param = http_build_query($_GET);
				$Page = new Page($count, 20, $param);
				$result = $bbs_model -> field('pic_show.*,pic_detail.recomment_title ,pic_detail.tid,pic_detail.pic_url') -> table('new_bbs_pic_show pic_show,new_bbs_pic_detail pic_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank asc') -> select();
			}
			else
			{
				if($schedule==1)
				{
					$where['_string'] = "pic_detail.status = 1 and pic_show.status = 1 and pic_show.ad_id = pic_detail.id and rank=".$rank.$where_g;
				}
				else
				{
					$where['_string'] = "pic_detail.status = 1 and pic_show.status = 1 and pic_show.ad_id = pic_detail.id".$where_g;
				}
				$count = $bbs_model -> table('new_bbs_pic_show pic_show,new_bbs_pic_detail pic_detail') -> where($where) -> count();
				import("@.ORG.Page");
				$param = http_build_query($_GET);
				$Page = new Page($count, 20, $param);
				if($my_time==3)
				{
					$result = $bbs_model -> field('pic_show.*,pic_detail.recomment_title ,pic_detail.tid,pic_detail.pic_url') -> table('new_bbs_pic_show pic_show,new_bbs_pic_detail pic_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('start_time asc,end_time asc') -> select();
				}
				else
				{
					$result = $bbs_model -> field('pic_show.*,pic_detail.recomment_title ,pic_detail.tid,pic_detail.pic_url') -> table('new_bbs_pic_show pic_show,new_bbs_pic_detail pic_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('start_time desc,end_time desc') -> select();
				}
			}
		}
		//区别搜索和三个状态
		if($search==1)
		{
			$my_time=0;
			$where['_string'] = "pic_detail.status = 1 and pic_show.status = 1 and pic_show.ad_id = pic_detail.id".$where_go;
			$count = $bbs_model -> table('new_bbs_pic_show show,new_bbs_pic_detail detail') -> where($where) -> count();
			import("@.ORG.Page");
			$param = http_build_query($_GET);
			$Page = new Page($count, 20, $param);
			$result = $bbs_model -> field('pic_show.*,pic_detail.recomment_title ,pic_detail.tid,pic_detail.pic_url') -> table('new_bbs_pic_show pic_show,new_bbs_pic_detail pic_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('start_time DESC,end_time DESC,id asc') -> select();
		}
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
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('pic_title',$pic_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('rank',$rank);
		$this -> assign('ad_id',$ad_id);
		$this -> assign('result',$result);
		$this -> assign('time',$time);
		$this -> assign('num', $this->max_pic_text);
		$this -> assign('my_time',$my_time);
		$this -> display();
	}

	function add_pic_show()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$start_tm=strtotime($_POST['start_tm']);
			$end_tm=strtotime($_POST['end_tm']);
			if($_POST['ad_id'])
			{
				$result=$bbs_model->table('new_bbs_pic_detail') -> where(array('id'=>$_POST['ad_id'],'status'=>1)) -> select();
				if($result)
				{
					$ad_id = $_POST['ad_id'];
				}
				else
				{
					$this -> error("广告ID不存在");
				}
			}
			else
			{
				$this -> error("请填写广告ID");
			}
			if($_POST['rank'])
			{
				$rank = $_POST['rank'];
				if($rank <= 0||$rank >$this->max_pic_text)
				{
					$this -> error("排序值错误");
				}
			}
			else
			{
				$this -> error("请选择一个位置");
			}
			if($start_tm&&$end_tm)
			{
				if($start_tm > $end_tm)
				{
					$this -> error("开始时间不能大于结束时间");
				}
				$where['_string'] = " status =1 and start_time <= ".$end_tm." and end_time >=".$start_tm." and ((ad_id =".$ad_id.") or (rank =".$rank."))";
				$result = $bbs_model -> table('new_bbs_pic_show') ->where($where) -> select();
				if($result)
				{
					//$this -> error("您添加的时间和广告ID为【{$result[0]['ad_id']}】的记录有冲突");
					$this -> error("排期冲突，请调整");
				}
				else
				{
					$data['start_time']=$start_tm;
					$data['end_time']=$end_tm;
					if($end_tm<time())
					{
						$my_time=1;
					}
					elseif($start_tm>time())
					{
						$my_time=3;
					}
					else
					{
						$my_time=2;
					}
				}
			}
			else
			{
				$this -> error("请填写开始时间和结束时间");
			}
			$data['rank'] = $rank;
			$data['ad_id'] =$ad_id;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;
			$result = $bbs_model -> table('new_bbs_pic_show') -> add($data);
			if($result){
				$this -> writelog("已添加id为{$result}的图文","new_bbs_pic_show",$result,__ACTION__ ,"","add");
				$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_show_list/my_time/".$my_time);
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}
		else
		{
			$this -> assign('num', $this->max_pic_text);
			$this -> display();
		}
	}
	
	function edit_pic_show()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_POST['id'];
			$start_tm=strtotime($_POST['start_tm']);
			$end_tm=strtotime($_POST['end_tm']);
			$life=$_POST['life'];
			$my_time=$_POST['my_time'];
			
			if($my_time==0)
			{
				if($end_tm<time())
				{
					$my_time=1;
				}
				elseif($start_tm>time())
				{
					$my_time=3;
				}
				else
				{
					$my_time=2;
				}
			}
			$lr = $_POST['lr'];
			if($lr){
				$where_go .= "/lr/{$lr}";
			}
			$p = $_POST['p'];
			if($p){
				$where_go .= "/p/{$p}";
			}
			if($_POST['ad_id'])
			{
				$result=$bbs_model->table('new_bbs_pic_detail') -> where(array('id'=>$_POST['ad_id'],'status'=>1)) -> select();
				if($result)
				{
					$ad_id = $_POST['ad_id'];
				}
				else
				{
					$this -> error("广告ID不存在");
				}
			}
			else
			{
				$this -> error("请填写广告ID");
			}
			if($_POST['rank'])
			{
				$rank = $_POST['rank'];
				if($rank <= 0||$rank >$this->max_pic_text)
				{
					$this -> error("排序值错误");
				}
			}
			else
			{
				$this -> error("请选择一个位置");
			}
			if($start_tm&&$end_tm)
			{
				if($start_tm >$end_tm)
				{
					$this -> error("开始时间不能大于结束时间");
				}
				if($life==1)
				{
					if($start_tm<time())
					{
						$this -> error("复制上线的开始时间必须大于当前时间");
					}
				}
				$where['_string'] = " status =1 and start_time <= ".$end_tm." and end_time >=".$start_tm." and id !=".$id." and ((ad_id =".$ad_id.") or (rank =".$rank."))";
				$result = $bbs_model -> table('new_bbs_pic_show') ->where($where) -> select();
				if($result)
				{
					//$this -> error("您编辑的时间和后台广告ID为【{$result[0]['ad_id']}】的记录有冲突");
					$this -> error("排期冲突，请调整");
				}
				else
				{
					$data['start_time'] = $start_tm;
					$data['end_time'] = $end_tm;
				}
			}
			else
			{
				$this -> error("请填写开始时间和结束时间");
			}
			$data['ad_id'] = $ad_id;
			$data['rank'] = $rank;
			$data['update_tm'] = time();
			if($life==1)
			{
				$data['create_tm'] = time();
				$data['status']= 1;
				$result = $bbs_model -> table('new_bbs_pic_show') -> add($data);
				if($result){
					$this -> writelog("已复制上线id为{$result}的图文","new_bbs_pic_show",$result,__ACTION__ ,"","add");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_show_list/my_time/".$my_time);
					$this -> success("复制上线成功");
				}
				else
				{
					$this -> error("复制上线失败");
				}
			}
			else
			{
				$log_result = $this -> logcheck(array('id' => $id),'new_bbs_pic_show',$data,$bbs_model);
				$result = $bbs_model -> table('new_bbs_pic_show') -> where(array('id' => $id)) -> save($data);
				if($result){
					$this -> writelog("已编辑id为{$id}的图文排期".$log_result,"new_bbs_pic_show",$id,__ACTION__ ,"","edit");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_show_list/my_time/".$my_time.$where_go);
					$this -> success("编辑成功");
				}
				else{
					$this -> error("编辑失败");
				}
			}
		}
		else
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_GET['id'];
			$lr = $_GET['lr'];
			$p = $_GET['p'];
			$result = $bbs_model -> table('new_bbs_pic_show') -> where(array('id' => $id)) -> find();
			$result_detail = $bbs_model -> table('new_bbs_pic_detail') -> where(array('id' => $result['ad_id'])) -> find();
			$this -> assign("result",$result);
			$this -> assign("re_detail",$result_detail);
			$this -> assign('num', $this->max_pic_text);
			$this -> assign("lr",$lr);
			$this -> assign("p",$p);
			$this -> display();
		}
	}
	
	function del_pic_show()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$lr = $_GET['lr'];
		$my_time=$_GET['my_time'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('new_bbs_pic_show') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的图文排期","new_bbs_pic_show",$id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_show_list/my_time/".$my_time.$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	function get_pic_detail()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result=$bbs_model->table('new_bbs_pic_detail') -> where(array('id'=>$id,'status'=>1)) -> select();
		if($result)
		{
			$data = array(
			'title' => $result[0]['recomment_title'],
			'tid' => $result[0]['tid'],
			'pic_url' => IMGATT_HOST.$result[0]['pic_url'],
			);
			echo json_encode($data);
		}
		else
		{
			echo 2;
			return 2;
		}
	}
	function pic_show_over()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$reasons=$_POST['reasons'];
			$id=$_POST['id'];
			if($reasons)
			{
				$data['note']=$reasons;
				$data['end_time']=time();
				$data['update_tm']=time();
				$log_result = $this -> logcheck(array('id' => $id),'new_bbs_pic_show',$data,$bbs_model);
				$result = $bbs_model -> table('new_bbs_pic_show') -> where(array('id' => $id)) -> save($data);
				if($result)
				{
					$this -> writelog("已强制结束id为{$id}的图文排期".$log_result,"new_bbs_pic_show",$id,__ACTION__ ,"","edit");
					$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_show_list".$where_go);
					$this -> success("编辑成功");
				}
				else
				{
					$this -> error("编辑失败");
				}
			}
			else
			{
				$this -> error("结束理由不能为空");
			}
		}
		else
		{
			$this -> display();
		}
	}
	
	function pic_detail_list()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$pic_title = $_GET['pic_title'];
		$ad_id = $_GET['ad_id'];
		$create_time=$_GET['create_tm'];
		$end_time=$_GET['end_tm'];
		$create_tm = strtotime($_GET['create_tm']);
		$end_tm = strtotime($_GET['end_tm']);
		
		
		if($create_tm)
		{
			$where_go .= " and create_tm >= {$create_tm}";
		}
		if($end_tm)
		{
			$where_go .= " and create_tm <= {$end_tm}";
		}
		
		if($create_tm && $end_tm && $create_tm > $end_tm)
		{
			$this -> error("添加时间的开始时间不能大于添加时间的结束时间");
		}
		
		if($pic_title)
		{
			$where_go .= " and recomment_title like '%{$pic_title}%'";
		}
		if($ad_id)
		{
			$where_go .= " and id = {$ad_id}";
		}
		
		$where['_string'] = "status = 1 ".$where_go;
		$count = $bbs_model -> table('new_bbs_pic_detail') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('new_bbs_pic_detail') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('create_tm DESC') -> select();
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
        $show = $Page->show();
		$this -> assign('lr',$lr);
		$this -> assign('p',$p);
        $this -> assign('page', $show);
		$this -> assign('pic_title',$pic_title);
		$this -> assign('ad_id',$ad_id);
		$this -> assign('create_tm',$create_time);
		$this -> assign('end_tm',$end_time);
		$this -> assign('result',$result);
		$this -> display();
	}

	function add_pic_detail()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$recomment_title = $_POST['recomment_title'];
			$my_pic = $_FILES['my_pic'];
		  
			if($_POST['tid'])
			{
				$tid_detail=$this->get_tid_detail($_POST['tid']);
				if (empty($tid_detail)) 
				{
					$this -> error("TID不存在");
				}
				else
				{
					$tid = $_POST['tid'];
				}
			}
			else
			{
				$this -> error("TID不能为空");
			}
			if ($my_pic['size']) 
			{
				//检查图片尺寸
				$img_info_arr = getimagesize($my_pic['tmp_name']);
				if (!$img_info_arr) 
				{
					$this->error("上传图片出错");
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != $this->pic_width || $height != $this->pic_height) 
				{
					$this->error("请添加尺寸为{$this->pic_width}*{$this->pic_height}的图片");
				}
				//检查图片大小
				if($my_pic['size']>$this->pic_max_size)
				{
					$this->error("请添加大小为{$this->pic_max_MB}MB以内的图片");
				}
				//检查图片格式
				if ($my_pic['type'] != 'image/png' && $my_pic['type'] != 'image/jpg' && $my_pic['type'] != 'image/jpeg'&& $my_pic['type'] != 'image/bmp') {
					$this->error("请添加图片格式为：jpg，bmp，jpeg，png的图片");
				}
				//上传图片
				$path = date("Ym/d/");
				$config['multi_config']['my_pic'] = array(
					'savepath' => UPLOAD_PATH . '/img/' . $path,
					'saveRule' => 'getmsec',
				);
				$list = $this->_uploadapk(0, $config);
				$bbs_pic_url = $list['image'][0]['url'];
				$data['pic_url'] = $bbs_pic_url;
			}
			else
			{
				$this -> error("请上传宣传图片");
			}
			$data['recomment_title'] = $recomment_title;
			$data['tid'] = $tid;
			$data['create_tm'] = time();
			$data['update_tm'] = time();
			$data['status'] = 1;
			$result = $bbs_model -> table('new_bbs_pic_detail') -> add($data);
			if($result){
				$this -> writelog("已添加id为{$result}的图文素材详情","new_bbs_pic_detail",$result,__ACTION__ ,"","add");
				$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_detail_list");
				$this -> success("添加成功");
			}else{
				$this -> error("添加失败");
			}
		}
		else
		{
			$this -> display();
		}
	}
	
	function edit_pic_detail()
	{
		if($_POST)
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_POST['id'];
			
			$lr = $_POST['lr'];
			if($lr){
				$where_go .= "/lr/{$lr}";
			}
			$p = $_POST['p'];
			if($p){
				$where_go .= "/p/{$p}";
			}
			$recomment_title = $_POST['recomment_title'];
			$my_pic = $_FILES['my_pic'];
			if($_POST['tid'])
			{
				$tid_detail=$this->get_tid_detail($_POST['tid']);
				if (empty($tid_detail)) 
				{
					$this -> error("TID不存在");
				}
				else
				{
					$tid = $_POST['tid'];
				}
			}
			else
			{
				$this -> error("TID不能为空");
			}
			if ($my_pic['size']) 
			{
				//检查图片尺寸
				$img_info_arr = getimagesize($my_pic['tmp_name']);
				if (!$img_info_arr) 
				{
					$this->error("上传图片出错");
				}
				$width = $img_info_arr[0];
				$height = $img_info_arr[1];
				if ($width != $this->pic_width || $height != $this->pic_height) 
				{
					$this->error("请添加尺寸为{$this->pic_width}*{$this->pic_height}的图片");
				}
				//检查图片大小
				if($my_pic['size']>$this->pic_max_size)
				{
					$this->error("请添加大小为{$this->pic_max_MB}MB以内的图片");
				}
				//检查图片格式
				if ($my_pic['type'] != 'image/png' && $my_pic['type'] != 'image/jpg' && $my_pic['type'] != 'image/jpeg'&& $my_pic['type'] != 'image/bmp') {
					$this->error("请添加图片格式为：jpg，bmp，jpeg，png的图片");
				}
				//上传图片
				$path = date("Ym/d/");
				$config['multi_config']['my_pic'] = array(
					'savepath' => UPLOAD_PATH . '/img/' . $path,
					'saveRule' => 'getmsec',
				);
				$list = $this->_uploadapk(0, $config);
				$bbs_pic_url = $list['image'][0]['url'];
				$data['pic_url'] = $bbs_pic_url;
			}
			$data['recomment_title'] = $recomment_title;
			$data['tid'] = $tid;
			$data['update_tm'] = time();
			$log_result = $this -> logcheck(array('id' => $id),'new_bbs_pic_detail',$data,$bbs_model);
			$result = $bbs_model -> table('new_bbs_pic_detail') -> where(array('id' => $id)) -> save($data);
			if($result){
				$this -> writelog("已编辑id为{$id}的图文素材详情".$log_result,"new_bbs_pic_detail",$id,__ACTION__ ,"","edit");
				$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_detail_list".$where_go);
				$this -> success("编辑成功");
			}else{
				$this -> error("编辑失败");
			}
		}
		else
		{
			$bbs_model = D('Bbs_manage.Bbs_manage');
			$id = $_GET['id'];
			$lr = $_GET['lr'];
			$p = $_GET['p'];
			$result = $bbs_model -> table('new_bbs_pic_detail') -> where(array('id' => $id)) -> find();
			$tid_detail= $this->get_tid_detail($result['tid']);
			$this -> assign("tid_detail",$tid_detail);
			$this -> assign("result",$result);
			$this -> assign("lr",$lr);
			$this -> assign("p",$p);
			$this -> display();
		}
	}
	
	function del_pic_detail()
	{
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$lr = $_GET['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('new_bbs_pic_detail') -> where(array('id' => $id)) -> save(array('status' => 0));
		$result_show = $bbs_model -> table('new_bbs_pic_show') -> where(array('ad_id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的图文素材详情","new_bbs_pic_detail",$id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/NUserChoose/pic_detail_list".$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	function curl_request($url, $post_param, $timeout = 60) 
	{
		if (!$url) 
		{
			return false;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$output = curl_exec($ch);
		curl_close($ch);
		if (!$output) {
			return $output;
		}
		$ret = json_decode($output, true);
		if (empty($ret) || !empty($ret['error'])) {
			// 出错
			return false;
		}
		return $ret;
	}
	function get_tid_detail($tid)
	{
		if($_GET['tid'])
		{
			$tid=$_GET['tid'];
		}
		$bbs_req_url = 'http://bbs.zhiyoo.com/api/getinfo.php';
		$now = time();
		$salt = 'EW22di9dREW9Q';
		$auth = md5($now.$salt);
		$post_data = array(
		'action' => 'getthread',   
		'tm' => $now,
		'auth' => $auth,
		'tid' => $tid
		);
		$output = $this->curl_request($bbs_req_url, $post_data);
		if($_GET['tid'])
		{
			if(empty($output))
			{
				echo 2;
			}
			else
			{
				$data = array(
				'title' => $output[0]['subject'],
				'author' => $output[0]['author'],
				'thread_url' => $output[0]['thread_url'],
				);
				echo json_encode($data);
			}
		}
		else
		{
			return $output;
		}
	}
}
