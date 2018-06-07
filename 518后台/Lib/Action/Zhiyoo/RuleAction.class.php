<?php 

Class RuleAction extends CommonAction{
	
	function rule_list(){
		$model = D('Zhiyoo.Zhiyoo');
		$order = $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
		$result1 = $model -> query("select * from zy_bbs_rule where  `status`=1 order by `level` ASC, addtime DESC");
		$result2 = $model -> query("select * from zy_bbs_rule where  `status`=0 order by `level` ASC, addtime DESC");
		if($order == 'ASC'){
			foreach($result1 as $val){
				$result[] = $val;
			}
			foreach($result2 as $val){
				$result[] = $val;
			}
		}
		else{
			foreach($result2 as $val){
				$result[] = $val;
			}
			foreach($result1 as $val){
				$result[] = $val;
			}
		}
		$order = $_GET['order'] == 'DESC' ? 'ASC' : 'DESC';
		$this -> assign('order',$order);
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function rule_edit(){
		$action = $_GET['action'];
		$rid = $_GET['rid'];
		$model = D('Zhiyoo.Zhiyoo');
		if($action == 'edit'){
			$result = $model -> query("select * from zy_bbs_rule where rid='$rid' limit 1");//读取规则
			$this -> assign('result',$result[0]);
			$this -> display();
		}
		elseif($action == 'del'){
			$result = $model -> query("delete from zy_bbs_rule where rid='$rid'");
			$this -> writelog("智友内容管理-内容采集规则管理-采集规则管理 删除id为{$rid}的内容");
			$this->success("删除成功！");
		}
		elseif($action == 'status'){
			$status = $_GET['status'];
			$result = $model -> query("update zy_bbs_rule set status='$status' where rid='$rid'");
			$this -> writelog("智友内容管理-内容采集规则管理-采集规则管理 更改id为{$rid}的内容状态为".$status);
			$this->success("修改成功！");
		}
	}
	
	function rule_doedit(){
		$model = D('Zhiyoo.Zhiyoo');
		$rid = $_POST['rid'];
		if(!is_numeric($rid))$this->error('rid错误');
		$rulename = addslashes($_POST['rulename']);
		$descript = addslashes($_POST['descript']);
		$target = addslashes($_POST['target']);
		$numeric = addslashes($_POST['numeric']);
		$starttime = $_POST['starttime'];
		$endtime = $_POST['endtime'];
		if($starttime > $endtime){
			$this->assign("jumpUrl", "/index.php/Zhiyoo/Rule/rule_edit?rid={$rid}&action=edit");
			$this->error("开始时间必须小于结束时间！");
		}
		$function = addslashes($_POST['function']);
		$comment = addslashes($_POST['comment']);
		$result = $model -> query("update zy_bbs_rule set rulename='$rulename',`descript`='$descript',target='$target',`numeric`='$numeric',starttime='$starttime',endtime='$endtime',`comment`='$comment',`function`='$function' where rid='$rid'");
		$this->assign("jumpUrl", "/index.php/Zhiyoo/Rule/rule_list");
		$this -> writelog("智友内容管理-内容采集规则管理-采集规则管理 编辑id为{$rid}的内容");
		$this->success("修改成功！");
	}
	
	function rule_editlevel(){
		$model = D('Zhiyoo.Zhiyoo');
		$order = $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
		$result1 = $model -> query("select * from zy_bbs_rule where  `status`=1 order by `level` ASC, addtime DESC");
		$result2 = $model -> query("select * from zy_bbs_rule where  `status`=0 order by `level` ASC, addtime DESC");
		if($order == 'ASC'){
			foreach($result1 as $val){
				$result[] = $val;
			}
			foreach($result2 as $val){
				$result[] = $val;
			}
			//$result = $result2 + $result1;
		}
		else{
			foreach($result2 as $val){
				$result[] = $val;
			}
			foreach($result1 as $val){
				$result[] = $val;
			}
		}
		$this -> assign('result',$result);
		$this -> display();
	}
	
	function rule_doeditlevel(){
		$model = D('Zhiyoo.Zhiyoo');
		$rid = $_POST['rid'];
		$level = $_POST['level'];
		$i = 0;
		while($rid[$i])
		{
			$level[$i] = trim($level[$i]);$logl .= ','.$level[$i];
			$rid[$i] = trim($rid[$i]);$logr .= ','.$rid[$i];
			$model -> query("update zy_bbs_rule set level='{$level[$i]}' where rid='{$rid[$i]}'");
			$i++;
		}
		$this->assign("jumpUrl", "/index.php/Zhiyoo/Rule/rule_list");
		$this -> writelog("智友内容管理-内容采集规则管理-采集规则管理 编辑id为{$logr}的内容优先级".$logl);
		$this->success("修改成功！");
	}
	
	function rule_addnewrule(){
		$this -> display();
	}
	
	function rule_doaddnewrule(){
		$model = D('Zhiyoo.Zhiyoo');
		//$level = trim($_POST['level']);
		//if(!is_numeric($level))$this->error('level不是数字');
		$rulename = addslashes($_POST['rulename']);
		$descript = addslashes($_POST['descript']);
		$target = addslashes($_POST['target']);
		$numeric = addslashes($_POST['numeric']);
		$starttime = $_POST['starttime'];
		$endtime = $_POST['endtime'];
		if($starttime > $endtime){
			$this->error("开始时间必须小于结束时间！");
		}
		$function = addslashes($_POST['function']);
		$comment = addslashes($_POST['comment']);
		$level = $model -> query("select max(level) as `max` from zy_bbs_rule ");
		$level = $level[0]['max'] +1;
		$result = $model -> query("insert into zy_bbs_rule set rulename='$rulename',`descript`='$descript',target='$target',`numeric`='$numeric',starttime='$starttime',endtime='$endtime',`comment`='$comment',`function`='$function',`level`='$level' ");
		$this->assign("jumpUrl", "/index.php/Zhiyoo/Rule/rule_list");
		$this -> writelog("智友内容管理-内容采集规则管理-采集规则管理 添加规则为[{$rulename}]的内容");
		$this->error("添加成功！");
	}
}