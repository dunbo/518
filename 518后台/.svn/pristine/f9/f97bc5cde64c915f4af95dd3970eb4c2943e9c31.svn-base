<?php

Class SearchkeywordAction extends CommonAction{


	function searchkeyword_list(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$result = $bbs_model -> table('bbs_search_keyword') -> where(array('status' => 1)) -> order('rank,create_tm DESC') -> select();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function add_keyword_show(){
		$this -> display();
	}
	
	function add_keyword_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$keyword = $_GET['keyword'];
		if(mb_strlen($keyword,'utf8') > 10 || mb_strlen($keyword,'utf8') < 1){
			$this -> error("请输入1-10字以内的搜索热词");
		}
		$have_result = $bbs_model -> table('bbs_search_keyword') -> where(array('keyword' => $keyword,'status' => 1)) -> select();
		if($have_result){
			$this -> error("已存在该搜索关键词");
		}
		$rank = $_GET['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$data = array(
			'keyword' => $keyword,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
		);
		$result = $bbs_model -> table('bbs_search_keyword') -> add($data);

		if($result){
			$this -> writelog("已添加id为{$result}的论坛pc管理搜索关键词", 'bbs_search_keyword', $result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl',"/index.php/Bbs_manage/Searchkeyword/searchkeyword_list");
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}
	
	function edit_keyword_show(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_search_keyword') -> where(array('id' => $id)) -> find();
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function edit_keyword_do(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$keyword = $_GET['keyword'];
		if(mb_strlen($keyword,'utf8') > 10 || mb_strlen($keyword,'utf8') < 1){
			$this -> error("请输入1-10字以内的搜索热词");
		}
		$have_where['_string'] = "keyword = {$keyword} and status = 1 and id != {$id}";
		$have_result = $bbs_model -> table('bbs_search_keyword') -> where($have_where) -> select();
		if($have_result){
			$this -> error("已存在该搜索关键词");
		}
		$rank = $_GET['rank'];
		if($rank <= 0){
			$this -> error("排序值错误");
		}
		$data = array(
			'keyword' => $keyword,
			'rank' => $rank,
			'update_tm' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'bbs_search_keyword',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_search_keyword') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的论坛pc管理搜索关键词管理".$log_result, 'bbs_search_keyword', $id,__ACTION__ ,"","edit");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Searchkeyword/searchkeyword_list");
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}
	
	
	function del_keyword(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$result = $bbs_model -> table('bbs_search_keyword') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的论坛pc管理搜索关键词", 'bbs_search_keyword', $id,__ACTION__ ,"","del");
			$this -> assign("jumpUrl","/index.php/Bbs_manage/Searchkeyword/searchkeyword_list");
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function change_rank(){
		$bbs_model = D('Bbs_manage.Bbs_manage');
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$data = array(
			'rank' => $rank,
			'update_tm' => time()
		);
		$log_result = $this -> logcheck(array('id' => $id),'bbs_search_keyword',$data,$bbs_model);
		$result = $bbs_model -> table('bbs_search_keyword') -> where(array('id' => $id)) -> save($data);
		if($result){
			$this -> writelog("已编辑id为{$id}的搜索关键词".$log_result, 'bbs_search_keyword', $id,__ACTION__ ,"","edit");
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	}









}