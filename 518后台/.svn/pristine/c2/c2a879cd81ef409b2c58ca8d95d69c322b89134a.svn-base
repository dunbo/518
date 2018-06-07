<?php

class FeedbackreportAction extends CommonAction{

	function feedback_list(){
		$model = new Model();
		$where = array('status' => 1);
		!isset($_GET['pid']) ? $_GET['pid'] =1 : $_GET['pid'];
		$this->check_where($where, 'pid');		
		$count = $model -> table('sj_feedback_question') -> where($where) -> count();
		$result = $model -> table('sj_feedback_question') -> where($where) -> order('rank') -> select();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$this -> assign('from',1);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);				
		$this -> display();
	}
	
	
	function add_feedback_show(){
		$model = new Model();
		$count = $model -> table('sj_feedback_question') -> where(array('status' => 1)) -> count();
		for($i=1;$i<=($count+1);$i++){
			$rank[] = $i;
		}
		$this -> assign('rank',$rank);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);				
		$this -> display();
	}

	function add_feedback_do(){
		$model = new Model();
		$question = trim($_POST['question']);
		if($this -> strlen_az($question) > 40 || !$question){
			$this -> error("反馈问题限定20字以内");
		}
		$have_been = $model -> table('sj_feedback_question') -> where(array('question' => $question,'status' => 1)) -> select();
		
		if($have_been){
			$this -> error("该反馈问题已存在");
		}
	
		$suggest = trim($_POST['suggest']);
		if($this -> strlen_az($suggest) > 100){
			$this -> error("提问建议限定50字以内");
		}
		$rank = $_POST['rank'];
		$where_rank['_string'] = "rank >= {$rank} and status = 1 and pid={$_POST['pid']}";
		$rank_result = $model -> table('sj_feedback_question') -> where($where_rank) -> select();
		foreach($rank_result as $key => $val){
			$change_rank = $model -> table('sj_feedback_question') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] + 1));
		}
		$data = array(
			'question' => $question,
			'suggest' => $suggest,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'pid' => $_POST['pid']
		);
		
		$result = $model -> table('sj_feedback_question') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的反馈问题",'sj_feedback_question',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Feedbackreport/feedback_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}


	function edit_feedback_show(){
		$model = new Model();
		$id = $_GET['id'];
		$count = $model -> table('sj_feedback_question') -> where(array('status' => 1)) -> count();
		$result = $model -> table('sj_feedback_question') -> where(array('id' => $id,'status' => 1)) -> select();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);				
		$this -> display();
	}

	function edit_feedback_do(){
		$model = new Model();
		$id = $_POST['id'];
		$question = trim($_POST['question']);
		if($this -> strlen_az($question) > 40 || !$question){
			$this -> error("反馈问题限定20字以内");
		}
		$where_have['_string'] = "question = '{$question}' and id != {$id} and status = 1";
		$have_been = $model -> table('sj_feedback_question') -> where($where_have) -> select();
		if($have_been){
			$this -> error("该反馈问题已存在");
		}
		$suggest = trim($_POST['suggest']);
		if($this -> strlen_az($suggest) > 100){
			$this -> error("提问建议限定50字以内");
		}
		$rank = $_POST['rank'];
		$been_result = $model -> table('sj_feedback_question') -> where(array('id' => $id)) -> select();
		$log_result = $this -> logcheck(array('id' => $id),'sj_feedback_question',array('question' => $question,'suggest' => $suggest,'rank' => $rank),$model);
		$rank_result = $this -> select_rank($been_result[0]['rank'],$rank,'sj_feedback_question',$id);
		$result = $model -> table('sj_feedback_question') -> where(array('id' => $id)) -> save(array('question' => $question,'suggest' => $suggest,'update_tm' => time()));
		if($result || $rank_result){
			$this -> writelog("已编辑id为{$id}的反馈问题".$log_result,'sj_feedback_question',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Feedbackreport/feedback_list');
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	
	function del_feedback(){
		$model = new Model();
		$id = $_GET['id'];
		$rank_result = $model -> table('sj_feedback_question') -> where(array('id' => $id)) -> select();
		$change_where['_string'] = "rank > {$rank_result[0]['rank']} and status = 1 and pid={$rank_result[0]['pid']}";
		$change_result = $model -> table('sj_feedback_question') -> where($change_where) -> select();
		foreach($change_result as $key => $val){
			$my_change = $model -> table('sj_feedback_question') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] - 1));
		}
		$result = $model -> table('sj_feedback_question') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的反馈问题",'sj_feedback_question',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Feedbackreport/feedback_list');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}
	
	function change_feedback_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$been_result = $model -> table('sj_feedback_question') -> where(array('id' => $id)) -> select();
		$rank_result = $this -> select_rank($been_result[0]['rank'],$rank,'sj_feedback_question',$id);
		if($rank_result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
		}
	
	}
	
	function report_list(){
		$model = new Model();
		$where = array('status' => 1);
		!isset($_GET['pid']) ? $_GET['pid'] =1 : $_GET['pid'];
		$this->check_where($where, 'pid');				
		$result = $model -> table('sj_report_question') -> where($where) -> order('rank') -> select();
		$count = $model -> table('sj_report_question') -> where($where) -> count();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$this -> assign('from',2);
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);				
		$this -> display();
	}
	
	function add_report_show(){
		$model = new Model();
		$count = $model -> table('sj_report_question') -> where(array('status' => 1)) -> count();
		for($i=1;$i<=($count+1);$i++){
			$rank[] = $i;
		}
		$this -> assign('rank',$rank);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);			
		$this -> display();
	}

	function add_report_do(){
		$model = new Model();
		$question = trim($_POST['question']);
		if($this -> strlen_az($question) > 40 || !$question){
			$this -> error("举报原因限定20字以内");
		}
		$have_been = $model -> table('sj_report_question') -> where(array('question' => $question,'status' => 1)) -> select();
		if($have_been){
			$this -> error("该举报问题已存在");
		}
		$suggest = trim($_POST['suggest']);
		if($this -> strlen_az($suggest) > 100){
			$this -> error("举报建议限定50字以内");
		}
		$rank = $_POST['rank'];
		$where_rank['_string'] = "rank >= {$rank} and status = 1 and pid={$_POST['pid']}";
		$rank_result = $model -> table('sj_report_question') -> where($where_rank) -> select();
		foreach($rank_result as $key => $val){
			$change_rank = $model -> table('sj_report_question') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] + 1));
		}
		$data = array(
			'question' => $question,
			'suggest' => $suggest,
			'rank' => $rank,
			'create_tm' => time(),
			'update_tm' => time(),
			'status' => 1,
			'pid' => $_POST['pid'],
		);
		
		$result = $model -> table('sj_report_question') -> add($data);
		if($result){
			$this -> writelog("已添加id为{$result}的举报问题",'sj_report_question',$result,__ACTION__ ,"","add");
			$this -> assign('jumpUrl','/index.php/Sj/Feedbackreport/report_list');
			$this -> success("添加成功");
		}else{
			$this -> error("添加失败");
		}
	}


	function edit_report_show(){
		$model = new Model();
		$id = $_GET['id'];
		$count = $model -> table('sj_report_question') -> where(array('status' => 1)) -> count();
		$result = $model -> table('sj_report_question') -> where(array('id' => $id,'status' => 1)) -> select();
		for($i=1;$i<=$count;$i++){
			$rank[] = $i;
		}
		$this -> assign('rank',$rank);
		$this -> assign('result',$result);
		$util = D("Sj.Util");
		$product = $util -> getProducts(array('pid'=>$_GET['pid']));
		$this->assign('product', $product);			
		$this -> display();
	}

	function edit_report_do(){
		$model = new Model();
		$id = $_POST['id'];
		$question = trim($_POST['question']);
		if($this -> strlen_az($question) > 40 || !$question){
			$this -> error("举报原因限定20字以内");
		}
		$where_have['_string'] = "question = '{$question}' and id != {$id} and status = 1";
		$have_been = $model -> table('sj_report_question') -> where($where_have) -> select();
		if($have_been){
			$this -> error("该举报问题已存在");
		}
		$suggest = trim($_POST['suggest']);
		if($this -> strlen_az($suggest) > 100){
			$this -> error("举报建议限定50字以内");
		}
		$rank = $_POST['rank'];
		$been_result = $model -> table('sj_report_question') -> where(array('id' => $id)) -> select();
		$log_result = $this -> logcheck(array('id' => $id),'sj_report_question',array('question' => $question,'suggest' => $suggest,'rank' => $rank),$model);
		$rank_result = $this -> select_rank($been_result[0]['rank'],$rank,'sj_report_question',$id);
		$result = $model -> table('sj_report_question') -> where(array('id' => $id)) -> save(array('question' => $question,'suggest' => $suggest,'update_tm' => time()));
		if($result || $rank_result){
			$this -> writelog("已编辑id为{$id}的反馈问题".$log_result,'sj_report_question',$id,__ACTION__ ,"","edit");
			$this -> assign('jumpUrl','/index.php/Sj/Feedbackreport/report_list');
			$this -> success("编辑成功");
		}else{
			$this -> error("编辑失败");
		}
	}

	
	function del_report(){
		$model = new Model();
		$id = $_GET['id'];
		$rank_result = $model -> table('sj_report_question') -> where(array('id' => $id)) -> select();
		$change_where['_string'] = "rank > {$rank_result[0]['rank']} and status = 1 and pid={$rank_result[0]['pid']}";
		$change_result = $model -> table('sj_report_question') -> where($change_where) -> select();
		foreach($change_result as $key => $val){
			$my_change = $model -> table('sj_report_question') -> where(array('id' => $val['id'])) -> save(array('rank' => $val['rank'] - 1));
		}
		$result = $model -> table('sj_report_question') -> where(array('id' => $id)) -> save(array('status' => 0));
		if($result){
			$this -> writelog("已删除id为{$id}的举报问题",'sj_report_question',$id,__ACTION__ ,"","del");
			$this -> assign('jumpUrl','/index.php/Sj/Feedbackreport/report_list');
			$this -> success("删除成功");
		}else{
			$this -> error("删除失败");
		}
	}

	
	function change_report_rank(){
		$model = new Model();
		$id = $_GET['id'];
		$rank = $_GET['rank'];
		$been_result = $model -> table('sj_report_question') -> where(array('id' => $id)) -> select();
		$rank_result = $this -> select_rank($been_result[0]['rank'],$rank,'sj_report_question',$id);
		if($rank_result){
			echo 1;
			return 1;
		}else{
			echo 2;
			return 2;
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