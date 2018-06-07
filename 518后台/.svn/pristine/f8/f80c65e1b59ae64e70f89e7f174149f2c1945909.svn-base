<?php

Class NewsrecommendAction extends CommonAction{

	function news_list(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$news_title = $_GET['news_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if(strtotime($start_tm) > strtotime($end_tm)){
			$this -> error("开始时间不能大于结束时间");
		}
		if($news_title){
			$where_go .= " and news_title like '%{$news_title}%'";
		}
		if($start_tm && $end_tm){
			$where_go .= " and create_tm >= ".strtotime($start_tm)." and create_tm <= ".strtotime($end_tm);
		}
		$where['_string'] = "status = 1".$where_go;
		$count = $bbs_model -> table('bbs_news_recommend') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $bbs_model -> table('bbs_news_recommend') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) -> order('rank,create_tm DESC') -> select();
		foreach($result as $key => $val){
			$about_result = $bbs_model -> table('bbs_news_about') -> where(array('parent_id' => $val['id'],'status' => 1)) -> select();
			$val['about_result'] = $about_result;
			$result[$key] = $val;
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
		$this -> assign('news_title',$news_title);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_news_show(){
		$this -> display();
	}
	
	function add_news_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$news_title = $_POST['news_title'];
		$news_link = $_POST['news_link'];
		$rank = $_POST['rank'];
		$about_name = $_POST['about_name'];
		$about_link = $_POST['about_link'];
		if(mb_strlen($news_title,'utf8') < 1 || mb_strlen($news_title,'utf8') > 40){
			$this -> error("请输入1-40字以内的文章名称");
		}
		if(mb_strlen($news_link,'utf8') < 1 || mb_strlen($news_link,'utf8') > 255){
			$this -> error("请输入1-255字以内的文章地址");
		}
		for($i=0;$i<count($about_name);$i++){
			$about_news[$i]['title'] = $about_name[$i];
			$about_news[$i]['link'] = $about_link[$i];
		}
		$data_news = array(
			'news_title' => $news_title,
			'news_link' => $news_link,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1
		);
		$result = $bbs_model -> table('bbs_news_recommend') -> add($data_news);
		if($result){
			foreach($about_news as $key => $val){
				if($val['title'] && $val['link']){
					$data_about = array(
						'news_title' => $val['title'],
						'news_link' => $val['link'],
						'parent_id' => $result,
						'create_tm' => time(),
						'update_tm' => time(),
						'status' => 1
					);
					$about_result = $bbs_model -> table('bbs_news_about') -> add($data_about);
				}
			}
			$this -> writelog("已添加id为{$result}的推荐新闻", 'bbs_news_about', $result,__ACTION__ ,"","add");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Newsrecommend/news_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_news_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$news_title = $_GET['news_title'];
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		$lr = $_GET['lr'];
		$p = $_GET['p'];
		$result = $bbs_model -> table('bbs_news_recommend') -> where(array('id' => $id)) -> find();
		$about_result = $bbs_model -> table('bbs_news_about') -> where(array('parent_id' => $result['id'],'status' => 1)) -> select();
		$all_about = $bbs_model -> table('bbs_news_about') -> order('id DESC') -> select();
		$this -> assign("result",$result);
		$this -> assign("all_about",$all_about[0]['id'] + 1);
		$this -> assign("about_result",$about_result);
		$this -> assign("about_count",count($about_result) - 1);
		$this -> assign("news_title",$news_title);
		$this -> assign("start_tm",$start_tm);
		$this -> assign("end_tm",$end_tm);
		$this -> assign("lr",$lr);
		$this -> assign("p",$p);
		$this -> display();
	}

	function edit_news_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_POST['id'];
		$news_titles = $_POST['news_titles'];
		if($news_titles){
			$where_go .= "/news_titles/{$news_titles}";
		}
		$start_tm = $_POST['start_tm'];
		$end_tm = $_POST['end_tm'];
		if($start_tm && $end_tm){
			$where_go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		$lr = $_POST['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_POST['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$news_title = $_POST['news_title'];
		if(mb_strlen($news_title,'utf8') < 1 || mb_strlen($news_title,'utf8') > 40){
			$this -> error("请输入1-40字以内的文章名称");
		}
		$rank = $_POST['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$news_link = $_POST['news_link'];
		if(mb_strlen($news_link,'utf8') < 1 || mb_strlen($news_link,'utf8') > 255){
			$this -> error("请输入1-255字以内的文章地址");
		}
		$about_name = $_POST['about_name'];
		$about_link = $_POST['about_link'];
		for($i=0;$i<count($about_name);$i++){
			$about_news[$i]['title'] = $about_name[$i];
			$about_news[$i]['link'] = $about_link[$i];
			if(($about_name[$i] && !$about_link[$i]) || (!$about_name[$i] && $about_link[$i])){
				$this -> error("关联文章标题与地址，若已填写一项则另一项必填");
			}
		}
		$about_id = $_POST['about_id'];
		$data['news_title'] = $news_title;
		$data['rank'] = $rank;
		$data['news_link'] = $news_link;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_news_recommend',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_news_recommend') -> where(array('id' => $id)) -> save($data);
	
		if($result){
			for($i=0;$i<count($about_id);$i++){
				$result_have = $bbs_model -> table('bbs_news_about') -> where(array('id' => $about_id[$i])) -> select();
				if($result_have){
					if($about_name[$i] && $about_link[$i]){
						$data_about = array(
							'news_title' => $about_name[$i],
							'news_link' => $about_link[$i],
							'update_tm' => time()
						);
						$about_result = $bbs_model -> table('bbs_news_about') -> where(array('id' => $about_id[$i],'status' => 1)) -> save($data_about);
					}
				}else{
					if($about_name[$i] && $about_link[$i]){
						$data_about = array(
							'parent_id' => $id,
							'news_title' => $about_name[$i],
							'news_link' => $about_link[$i],
							'update_tm' => time(),
							'create_tm' => time(),
							'status' => 1
						);
						$about_result = $bbs_model -> table('bbs_news_about') -> add($data_about);
					}
				}
			}
		}
		if($result){
			$this -> writelog("已编辑id为{$id}的推荐新闻".$log_result, 'bbs_news_about', $id,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Newsrecommend/news_list".$where_go);
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	function del_news(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$news_title = $_GET['news_title'];
		if($news_title){
			$where_go .= "/news_title/{$news_title}";
		}
		$start_tm = $_GET['start_tm'];
		$end_tm = $_GET['end_tm'];
		if($start_tm && $end_tm){
			$where_go .= "/start_tm/{$start_tm}/end_tm/{$end_tm}";
		}
		$lr = $_GET['lr'];
		if($lr){
			$where_go .= "/lr/{$lr}";
		}
		$p = $_GET['p'];
		if($p){
			$where_go .= "/p/{$p}";
		}
		$result = $bbs_model -> table('bbs_news_recommend') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$about_result = $bbs_model -> table('bbs_news_about') -> where(array('parent_id' => $id)) -> save(array('status' => 0));
			$this -> writelog("已删除id为{$id}的推荐新闻", 'bbs_news_about', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Newsrecommend/news_list".$where_go);
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function del_about_true(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_news_about') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	function change_rank(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data['rank'] = $rank;
		$data['update_tm'] = time();
		$log_result = $this -> logcheck(array('id' => $id),'bbs_news_recommend',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_news_recommend') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的推荐新闻".$log_result, 'bbs_news_recommend', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}
	
	



}