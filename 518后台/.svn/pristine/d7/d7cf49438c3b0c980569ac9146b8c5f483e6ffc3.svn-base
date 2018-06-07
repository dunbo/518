<?php
/**
 * 活动管理
 * Added by 庄超滨
 * 2018/03/17
 */
class DoublecolorAction extends CommonAction {

	/**
	 * 双色球平台
	 */
	public function index() {
		$model = D('Sj.Doublecolor');
		$where = array('status'=>1,'paystatus'=>1);
		$this->check_where($where,'issue');
		$this->check_where($where, 'uid', 'isset', 'like'); 
		$this->check_where($where, 'prizelevel'); 
		if($_GET['is_send'] == 2){
			$where['admin_id'] = array("exp",">0");
		}else{
			$this->check_where($where, 'is_send'); 
		}
		$this->check_range_where($where, 'startDate', 'endDate','add_time', true);
		
		//分页
		import("@.ORG.Page2");
		$count = $model->table('double_color_numbers')->where($where)->count();	
        $Page=new Page($count,10);
		$list = $model->table('double_color_numbers')-> where($where)->limit($Page->firstRow.','.$Page->listRows)->order("`issue` desc,`add_time` desc") -> select();
		$this -> assign("exp_sql",base64_encode($model->getlastsql()));
		$this -> assign("list",$list);
		$issue_arr = array();
		foreach($list as $k => $v){
			$issue_arr[$v['issue']] = $v['issue'];
		}
		$where = array(
			'id' => array('in',$issue_arr)
		);
		$kai_num =  $model->table('double_color_issue')-> where($where) ->field("id,kai_number,kai_time")-> select();
		$kai_num_new = array();
		foreach($kai_num as $key => $val){
			$kai_num_new[$val['id']] = $val;
		}
		unset($kai_num);
		$this -> assign("kai_num",$kai_num_new);
		//期号
		$this -> assign("issue_config",$model->get_issue_config());
		$this -> assign("prizelevel_config",$model->get_prizelevel_config());
		$this -> assign("res_azb",$model->get_res_azb());
        $Page->setConfig('header','篇记录');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');	
        $show =$Page->show();
        $this->assign ("page", $show );
		$this->assign('total', $count);	
		$this -> display();
	}
	
	/**
	 * 开奖
	 */
	public function open_award_num() {
		$model = D('Sj.Doublecolor');
		if($_POST){
			$issue = $_POST['issue'];
			$number = "[".$_POST['kai_number']."]";
			$where = array('id' => $issue);
			$map = array(
				'kai_number' => $number,
				'kai_time' => time(),
			);
			$ret = $model -> table('double_color_issue') ->where($where)->save($map);
			if($ret){
				$redis = new redis();
				$redis -> connect(C('LOTTERY_REDIS_HOST'),C('LOTTERY_REDIS_PORT'));
				$data = array(
					'issue' => $issue,
					'number' => $number
				);
				$data_json = json_encode($data);
				$cmd = "\$redis->rpush('double_color:kai', '{$data_json}');";
				eval($cmd);		
				$this->writelog("双色球添加了开奖号码：{$_POST['kai_number']},期号：{$issue}", 'double_color_issue',$issue,__ACTION__,'','add');
				$this -> success('操作成功');
			}else{
				$this -> error('操作失败');
			}
		}else{
			$where = array(
				'status' => 2,
				'kai_number' => ''
			);
			$info = $model -> table('double_color_issue') ->where($where)-> order('id desc')->find();
			$this->assign('issue', $info['id']);	
			if($info){
				$this->assign('kai_number',$model->get_winning_num($info['id']));	
			}
			$this -> display();
		}
	}
	//配置中奖
	public function con_user_award(){
		$model = D('Sj.Doublecolor');
		if($_POST){
			$purchase_num = explode(",",$_POST['purchase_num']);
			$blue_num = $purchase_num[6];
			unset($purchase_num[6]);			
			$data = array(
				'issue' => $_POST['issue'],
				'uid' => $_POST['uid_post'],
				'red_num' => json_encode($purchase_num),
				'blue_num' => $blue_num,
				'buynumber' => $_POST['buynumber'],
				'add_time' => strtotime($_POST['add_time']),
				'admin_id' => $_SESSION['admin']['admin_id'],
				'puid' => $_POST['puid'],
				'username' => $_POST['username'],
				'paystatus'=>1
			);
			$ret = $model -> table('double_color_numbers')->add($data);
			if($ret){	
				$this->writelog("双色球添加了配置中奖", 'double_color_numbers',$ret,__ACTION__,'','add');
				$this -> success('操作成功');
			}else{
				$this -> error('操作失败');
			}			
		}else{
			$where = array('kai_number' => '');
			$info = $model -> table('double_color_issue') ->where($where)-> order('id desc')->find();
			$this->assign('issue', $info['id']);	
			$this -> assign("prizelevel_config",$model->get_prizelevel_config());			
			$this -> display();
		}
	}
	//配置中奖-编辑
	public function con_user_award_save(){
		$model = D('Sj.Doublecolor');
		if($_POST){
			$where = array(
				'id' => $_POST['issue'],
				'kai_number'=> array('exp',"!=''")
			);
			$res = $model -> table('double_color_issue') ->where($where)->find();
			if($res){
				$this -> error('该期已开奖');
			}
			$where = array('id' => $_POST['id']);
			$purchase_num = explode(",",$_POST['purchase_num']);
			$blue_num = $purchase_num[6];
			unset($purchase_num[6]);
			$data = array(
				'issue' => $_POST['issue'],
				'uid' => $_POST['uid_post'],
				'red_num' => json_encode($purchase_num),
				'blue_num' => $blue_num,
				'buynumber' => $_POST['buynumber'],
				'add_time' => strtotime($_POST['add_time']),
				'update_time' => time(),								
				'admin_id' => $_SESSION['admin']['admin_id'],
				'puid' => $_POST['puid'],
				'username' => $_POST['username'],				
				'paystatus'=>1
			);
			$log = $this -> logcheck($where,'double_color_numbers',$data,$model);
			$ret = $model -> table('double_color_numbers') ->where($where)->save($data);
			if($ret){	
				$this->writelog("双色球：编辑了配置中奖：{$log}", 'double_color_numbers',$_POST['id'],__ACTION__,'','edit');
				$this -> success('操作成功');
			}else{
				$this -> error('操作失败');
			}			
		}else{
			$where = array('id' => $_GET['id']);
			$info = $model -> table('double_color_numbers') ->where($where)->find();
			$red_num = json_decode($info['red_num'],true);
			$info['purchase_num'] = implode(",",$red_num).",".$info['blue_num'];
			$info['add_time'] = date("Y-m-d H:i:s",$info['add_time']);
			$this->assign('info', $info);	
			$this -> assign("prizelevel_config",$model->get_prizelevel_config());			
			$this -> display();
		}
	}
	//添加安智币
	public function injection_azb(){
		$model = D('Sj.Doublecolor');
		if($_POST){
			$issue = $_POST['issue'];
			$azb = $_POST['azb'];
			$res_azb = $_POST['res_azb'];
			$where = array('id' => $issue);
			$map = array(
				'jackpot' => array("exp","`jackpot`+{$azb}"),
			);
			$ret = $model -> table('double_color_issue') ->where($where)->save($map);
			if($ret){	
				$this->writelog("双色球添加安智币:{$azb}个，期号{$issue}，添加前奖池：{$res_azb}", 'double_color_issue',$issue,__ACTION__,'','add');
				$this -> success('操作成功');
			}else{
				$this -> error('操作失败');
			}
		}else{
			$where = array(
				'status' => 1,
				'kai_number' => ''
			);
			$info = $model -> table('double_color_issue') ->where($where)-> order('id desc')->find();
			$this->assign('issue', $info['id']);	
			$this->assign('res_azb', $info['jackpot']);	
			$this -> display();
		}		
	}
	//删除配置中奖
	public function del_user_award(){
		$model = D('Sj.Doublecolor');
		$where = array('id' => $_GET['id']);		
		$data = array('status'=>0,'update_time'=>time());
		$ret = $model -> table('double_color_numbers')->where($where)->save($data);
		if($ret){	
			$this->writelog("双色球删除配置中奖", 'double_color_numbers',$_GET['id'],__ACTION__,'','del');
			$this -> success('操作成功');
		}else{
			$this -> error('操作失败');
		}			
	}
	//导出
	public function export_data(){
		$model = D('Sj.Doublecolor');
		$data = $model->get_exp();
		exit(json_encode($data));
	}
	//
	function pub_send_task(){
		if(!$_GET['issue']){ echo "no issue!";exit; }
		$task_client = get_task_client();
		$task_data = array();
		$task_data['issue'] = $_GET['issue'];
		$task_data['aid'] = $_GET['aid'];
		$task_client->doBackground('doublecolor_prize', json_encode($task_data));		
	}	
}
?>
