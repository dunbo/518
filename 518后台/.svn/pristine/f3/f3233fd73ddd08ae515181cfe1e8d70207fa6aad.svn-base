<?php
/**
 * 补签卡管理
 */
class SignMendAction extends CommonAction{
	function sign_mend_list()
	{
		$mid = (Int)$_GET['mid'];
		if( !$mid ) {
			$this->error('参数有误');
		}
		$where = array(
				'mid'	=>	$mid,
		);
		$list = D('Sj.Sign')->table('qd_sign_mend')->where($where)->order('id asc')->select();
		$list_price = $list_task = array();
		foreach ($list as $key => $val) {
			if( $val['type'] == 1 ) {
				$list_task[] = $val;//做任务领补签卡
			}else {
				$list_price[] = $val;//金币购买补签卡
			}
		}
		$this->assign('list_price', $list_price);
		$this->assign('list_task', $list_task);
		$this->display();
	}
	
	//编辑 operation参数  pri金币购买  tsk 做任务  st操作状态
	function sign_mend_edit()
	{
		$id  = $_REQUEST['id'];
		$mid = $_REQUEST['mid'];
		$operation = trim($_REQUEST['operation']);
		$model = D('Sj.Sign');
		$where = array('id' => $id);
		if( $_POST ) {
			$rows = $model->table('qd_sign_month')->where(array('id'=>$mid, 'status'=>2))->find();
			if( empty($rows) ) {
				$this->error('只能操作当前为待审核状态的签到月份');
			}
			if( $operation == 'pri' ) {
				$price = floatval($_POST['price']);
				$buy_num = (Int)($_POST['buy_num']);
				if( !$price ) {
					$this->error('金币价格不能为空');
				}
				if (!preg_match('/^[0-9]+(.[0-9]{1})?$/', $price)) {
					$this->error('金币价格只能为整数或小数一位');
				}
				if( !$buy_num || !preg_match("/^[0-9]*[1-9][0-9]*$/", $buy_num) ) {
					$this->error('购买次数填写错误，请重试');
				}
				if($buy_num > 10 ) {
					$this->error('上限10次');
				}
				$data = array(
					'price'		=>	$price,
					'buy_num'	=>	$buy_num,
				);
				$result	=	$model->table('qd_sign_mend')->where($where)->save($data);
				if( $result ) {
					$this -> writelog("已编辑id为{$id}的补签卡",'qd_sign_mend',$id,__ACTION__ ,'','edit');
					$this->success('操作成功');
				}else {
					$this->error('操作失败');
				}
			}elseif( $operation == 'tsk' ) {
				$task_num = (Int)($_POST['task_num']);
				if( !$task_num || !preg_match("/^[0-9]*[1-9][0-9]*$/", $task_num) ) {
					$this->error('任务次数填写错误，请重试');
				}
				if($task_num > 10 ) {
					$this->error('上限10次');
				}
				$data = array(
						'task_num'	=>	$task_num,
				);
				$result	=	$model->table('qd_sign_mend')->where($where)->save($data);
				if( $result ) {
					$this -> writelog("已编辑id为{$id}的补签卡",'qd_sign_mend',$id,__ACTION__ ,'','edit');
					$this->success('操作成功');
				}else {
					$this->error('操作失败');
				}
			}else {
				$this->error('操作失败');
			}
		}else {
			if( $operation == 'st' ) {
				$rows = $model->table('qd_sign_month')->where(array('id'=>$mid, 'status'=>2))->find();
				if( empty($rows) ) {
					$this->error('只能操作当前为待审核状态的签到月份');
				}
				$status	=	$_GET['status'];
				$data	=	array('status' => $status);
				$result	=	$model->table('qd_sign_mend')->where($where)->save($data);
				if( $result ) {
					$this -> writelog("已编辑id为{$id}的补签卡",'qd_sign_mend',$id,__ACTION__ ,'','edit');
					$this->success('操作成功');
				}else {
					$this->error('操作失败');
				}
			}
			$rows = $model->table('qd_sign_mend')->where($where)->find();
			$this->assign('id', $id);
			$this->assign('mid', $mid);
			$this->assign('operation', $operation);
			$this->assign('rows', $rows);
			$this->display();
		}
	}
}