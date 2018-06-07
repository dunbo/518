<?php

//搜索热词编辑后台
class SearchkeywordsmanageAction extends CommonAction{


	public function searchkeywords_list(){
		$model = new Model();
		$where['_string'] = "end_tm >= ".time()." and status = 1";
		$result = $model -> table('sj_search_keywords') -> where($where) -> order('rank') -> select();
		$count = count($result);

		foreach($result as $key => $val){
			$val['num'] = $key + 1;
			$val['starts_tm'] = $val['start_tm'];
			if($val['start_tm']){
				$val['start_tm'] = date('Y-m-d',$val['start_tm']);
			}
			if($val['end_tm']){
				$val['end_tm'] = date('Y-m-d',$val['end_tm']);
			}
			$result[$key] = $val;
		}


		$this -> assign('result',$result);
		$this -> assign('count',$count);
		$this -> display();
	}
	
	public function add_searchkeywords_show(){
		$model = new Model();
		$where['_string'] = "end_tm >= ".time()." and status = 1";
		$been_result = $model -> table('sj_search_keywords') -> where($where) -> count();
		$count = $been_result + 1;
		$this -> assign('num',$count);
		$this -> display();
	}
	
	public function add_searchkeywords(){
		$model = new Model();
		$keywords = $_POST['keywords'];
		$package = $_POST['package'];
		$start_tm = strtotime(date('Y-m-d 00:00:00',strtotime($_POST['fromdate'])));
		$end_tm = strtotime(date('Y-m-d 23:59:59',strtotime($_POST['todate'])));
		if(!$_POST['fromdate'] || !$_POST['todate']){
			$this -> error("请填写开始时间和结束时间！");
		}
		if($start_tm > $end_tm){
			$this -> error("开始时间不能大于结束时间！");
		}
		if($package == '为空表示不关联'){
			$package = '';
		}
		if($this -> strlen_az($keywords) > 20 || $this -> strlen_az($keywords) < 1){
			$this -> error('请填写不超过10个汉字的搜索热词');
		}
		if(!preg_match("/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$keywords)){
			$this -> error("关键字只能为汉字，数字，英文");
		}
		$been_where['_string'] = "status = 1 and end_tm >= ".time()."";
		$been_result = $model -> table('sj_search_keywords') -> where($been_where) -> select();
		if(count($been_result) >= 60){
			$this -> error('搜索热词不可超过60个');
		}
		$soft_result = $model -> table('sj_soft') -> where(array('package' => $package,'hide' => 1)) -> select();
		if($package){
			if(!$soft_result && $package != '为空表示不关联'){
				$this -> error('关联包名不存在');
			}
		}
		
		$searchkey_have_where = "key_word = '{$keywords}' and start_tm <= {$end_tm} and end_tm >= {$start_tm} and status = 1";
		$searchkey_have_result = $model -> table('sj_search_keywords') -> where($searchkey_have_where) -> select();
		if($searchkey_have_result){
			$this -> error("当前排期内已存在该搜索热词！");
		}
		
		if($package){
			$package_have_where = "package = '{$package}' and start_tm <= {$end_tm} and end_tm >={$start_tm} and status = 1";
			$package_have_result = $model -> table('sj_search_keywords') -> where($package_have_where) -> select();

			if($package_have_result){
				$this -> error("当前排序内已存在该关联包名！");
			}
		}
		$data = array(
			'key_word' => $keywords,
			'package' => $package,
			'update_tm' => time(),
			'rank' => count($been_result)+1,
			'start_tm' => $start_tm,
			'end_tm' => $end_tm,
			'status' => 1,
		);
		$result = $model -> table('sj_search_keywords') -> add($data);
		if($result){
			$this -> writelog("搜索热词管理V4.5_已添加搜索热词{$keywords},关联包名为{$package}");
			$this->assign('jumpUrl', '/index.php/Sj/Searchkeywordsmanage/searchkeywords_list');
			$this->success('添加成功');
		}else{
			$this -> error('添加失败');
		}
	}
	
	function edit_searchkeywords_show(){
		$model = new Model();
		$where['_string'] = "end_tm >= ".time()." and status = 1";
		$result = $model -> table('sj_search_keywords') -> where($where) -> order('rank') -> select();
		$count = count($result);
		foreach($result as $key => $val){
			$val['start_tm'] = date('Y-m-d',$val['start_tm']);
			$val['end_tm'] = date('Y-m-d',$val['end_tm']);
			$result[$key] = $val;
		}
		$this -> assign("count",$count);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	function check_package(){
		$model = new Model();
		$package = $_GET['package'];
		if($package){
			$have_been = $model -> table('sj_soft') -> where(array('package' => $package,'status' => 1,'hide' => 1)) -> select();
			if(!$have_been){
				echo json_encode(1);
				return json_encode(1);
			}else{
				echo json_encode(2);
				return json_encode(2);
			}
		}else{
			echo json_encode(2);
			return json_encode(2);
		}
	}
	
	function update_searchkeywords(){
		$model = new Model();
		$package = $_POST['package'];
		$key_word = $_POST['key_word'];
		$fromdate = $_POST['fromdate'];
		$todate = $_POST['todate'];
		foreach($package as $key => $val){
			if($val == "为空表示不关联"){
				$val = "";
			}
			$package[$key] = $val;
		}
	
		$id = $_POST['id'];
		foreach($package as $key => $val){
			$been_result = $model -> table('sj_soft') -> where(array('package' => $val,'hide' => 1)) -> select();
			if($val && $val != "为空表示不关联"){
				if(!$been_result){
					$this -> error("关联包名'{$val}'不存在");
				}
			}
		}
		foreach($key_word as $key => $val){
			if($this -> strlen_az($val) > 20 || $this -> strlen_az($val) < 1){
				$this -> error('请填写不超过10个汉字的搜索热词');
			}
			if(!preg_match("/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u",$val)){
				$this -> error("搜索热词'{$val}'不可含有特殊字符");
			}
		}
		foreach($fromdate as $key => $val){
			if(!$val || !$todate[$key]){
				$this -> error("请填写开始时间和结束时间！");
			}
			$vals = strtotime(date('Y-m-d 00:00:00',strtotime($val)));
			$eval = strtotime(date('Y-m-d 23:59:59',strtotime($todate[$key])));
			$start_tm[$key] = $vals;
			$end_tm[$key] = $eval;
		}
		foreach($start_tm as $key => $val){
			if($val > $end_tm[$key]){
				$this -> error("开始时间不能大于结束时间！");
			}
		}
		$my_result = $model -> table('sj_search_keywords') -> where(array('status' => 1)) -> select();
		
		foreach($id as $key => $val){
		//检查排序是否冲突
			$searchkey_have_where = "key_word = '{$key_word[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >= {$start_tm[$key]} and end_tm >= ".time()." and status = 1 and id != {$val}";
			$searchkey_have_result = $model -> table('sj_search_keywords') -> where($searchkey_have_where) -> select();
			
			if($searchkey_have_result){
				if($package[$key]){
					$this -> error("当前排期内已存在该搜索热词{$key_word[$key]},包名为{$package[$key]}！");
				}else{
					$this -> error("当前排期内已存在该搜索热词{$key_word[$key]}");
				}
			}
			if($package[$key]){
				$package_have_where = "package = '{$package[$key]}' and start_tm <= {$end_tm[$key]} and end_tm >={$start_tm[$key]} and end_tm >=".time()." and status = 1 and id != {$val}";
				$package_have_result = $model -> table('sj_search_keywords') -> where($package_have_where) -> select();

				if($package_have_result){
					$this -> error("当前排序内已存在该关联包名{$package[$key]},搜索热词为{$key_word[$key]}！");
				}
			}
		
		//编辑出过期，排序改变
	
			if($end_tm[$key] < time()){
				$my_where['id'] = $val;
				$my_result = $model -> table('sj_search_keywords') -> where($my_where) -> select();
				$need_where['_string'] = "rank > {$my_result[0]['rank']} and status = 1 and end_tm >= ".time()."";
				$need_result = $model -> table('sj_search_keywords') -> where($need_where) -> select();
				foreach($need_result as $k => $v){
					$the_where['id'] = $v['id'];
					$the_data = array(
						'rank' => $v['rank'] - 1
					);
					$the_result = $model -> table('sj_search_keywords') -> where($the_where) -> save($the_data);
				}
			}

			$where['id'] = $val;
			$data = array(
				'key_word' => $key_word[$key],
				'package' => $package[$key],
				'start_tm' => $start_tm[$key],
				'end_tm' => $end_tm[$key],
				'update_tm' => time(),
			);
			$log_result = $this -> logcheck(array('id' => $val),'sj_search_keywords',$data,$model);
			$result = $model -> table('sj_search_keywords') -> where($where) -> save($data);
			if($result){
				$this -> writelog("搜索热词管理V4.5_已编辑搜索热词id为{$id}的关联包名为{$package}".$log_result);
			}
			$all_result[] = $result;
		}
		if($all_result){
			$this->assign('jumpUrl', '/index.php/Sj/Searchkeywordsmanage/searchkeywords_list');
			$this->success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
	}
	
	function stale_searchkeywords_show(){
		$model = new Model();
		$where['_string'] = "end_tm < ".time()." and status = 1";
		$count = $model -> table('sj_search_keywords') -> where($where) -> count();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$Page = new Page($count, 20, $param);
		$result = $model -> table('sj_search_keywords') -> where($where) -> limit($Page->firstRow . ',' . $Page->listRows) ->order('end_tm DESC') -> select();
		if(!$_GET['p']){
			$_GET['p'] = 1;
		}
		foreach($result as $key => $val){
			$val['num'] = $key + 1 + $_GET['lr']*($_GET['p'] - 1);
			$val['start_tm'] = date('Y-m-d',$val['start_tm']);
			$val['end_tm'] = date('Y-m-d',$val['end_tm']);
			$result[$key] = $val;
		}
		$Page->setConfig('header', '篇记录');
		$Page->setConfig('first', '<<');
		$Page->setConfig('last', '>>');
		$show = $Page->show();
		$this->assign("page", $show);
		$this -> assign("result",$result);
		$this -> display();
	}
	
	function delete_searchkeywords(){
		$model = new Model();
		$id = $_GET['id'];
		$data['status'] = 0;
		$where['id'] = $id;
		$my_result = $model -> table('sj_search_keywords') -> where($where) -> select();
		if($my_result[0]['end_tm'] >= time()){
			$need_where['_string'] = "rank > {$my_result[0]['rank']} and status = 1 and end_tm >= ".time()."";
			
			$need_result = $model -> table('sj_search_keywords') -> where($need_where) -> select();

			foreach($need_result as $key => $val){
				$update_where['id'] = $val['id'];
				$update_where['status'] = 1;
				$update_data = array(
					'rank' => $val['rank'] - 1
				);
				$update_result = $model -> table('sj_search_keywords') -> where($update_where) -> save($update_data);
			}
		}
		$result = $model -> table('sj_search_keywords') -> where($where) -> save($data);
		
		if($result){
			$this -> writelog("搜索热词管理V4.5_已删除id为{$id}的搜索热词");
			if($my_result[0]['end_tm'] >= time()){
				$this->assign('jumpUrl', '/index.php/Sj/Searchkeywordsmanage/searchkeywords_list');
			}else{
				$this->assign('jumpUrl', '/index.php/Sj/Searchkeywordsmanage/stale_searchkeywords_show');
			}
			$this->success('删除成功');
		}else{
			$this -> error('删除失败');
		}
	}
	
	function change_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$action = $_GET['action'];
		if($action == 'up'){
			$my_result = $model -> table('sj_search_keywords') -> where(array('id' => $id)) -> select();
			
			$update_data = array(
				'rank' => $my_result[0]['rank']
			);
			$need_result = $model -> table('sj_search_keywords') -> where(array('rank' => $my_result[0]['rank'] - 1,'status' => 1)) -> save($update_data);
			$rank = $my_result[0]['rank'] - 1;
			$data = array(
				'rank' => $rank
			);
			$result = $model -> table('sj_search_keywords') -> where(array('id' => $id)) -> save($data);
		}elseif($action == 'down'){
			$my_result = $model -> table('sj_search_keywords') -> where(array('id' => $id)) -> select();
			$update_data = array(
				'rank' => $my_result[0]['rank']
			);
			$need_result = $model -> table('sj_search_keywords') -> where(array('rank' => $my_result[0]['rank'] + 1,'status' => 1)) -> save($update_data);
			$rank = $my_result[0]['rank'] + 1;
			$data = array(
				'rank' => $rank
			);
			$result = $model -> table('sj_search_keywords') -> where(array('id' => $id)) -> save($data);
		
		}
	
		if($result){
			$this -> writelog("搜索热词管理V4.5_已编辑id为{$id}的搜索热词的排序为{$rank}");
			$this->assign('jumpUrl', '/index.php/Sj/Searchkeywordsmanage/searchkeywords_list');
			$this->success('编辑成功');
		}else{
			$this -> error('编辑失败');
		}
	}
	
	
	
	function strlen_az($string, $charset='utf-8')
	{
		$n = $count = 0;
		$length = strlen($string);
		if (strtolower($charset) == 'utf-8')
		{
			while ($n < $length)
			{
				$currentByte = ord($string[$n]);
				if ($currentByte == 9 || $currentByte == 10 || (32 <= $currentByte && $currentByte <= 126))
				{
					$n++;
					$count++;
				} elseif (194 <= $currentByte && $currentByte <= 223)
				{
					$n += 2;
					$count += 2;
				} elseif (224 <= $currentByte && $currentByte <= 239)
				{
					$n += 3;
					$count += 2;
				} elseif (240 <= $currentByte && $currentByte <= 247)
				{
					$n += 4;
					$count += 2;
				} elseif (248 <= $currentByte && $currentByte <= 251)
				{
					$n += 5;
					$count += 2;
				} elseif ($currentByte == 252 || $currentByte == 253)
				{
					$n += 6;
					$count += 2;
				} else
				{
					$n++;
					$count++;
				}
				if ($count >= $length)
				{break;
				}
			}
			return $count;
		} else {
			for ($i = 0; $i < $length; $i++)
			{
				if (ord($string[$i]) > 127) {
					$i++;
					$count++;
				}
				$count++;
			}
			return $count;
		}
	}




}