<?php
class SignContinuityAction extends CommonAction{
	function sign_continuity_list()
	{
		$mid = (Int)$_GET['mid'];
		if( !$mid ) {
			$this->error('参数有误');
		}
		$where = array(
				'mid'	 =>	$mid,
		);
		$list = D('Sj.Sign')->table('qd_sign_continuity')->where($where)->order('id asc')->select();
		$this->assign('list', $list);
		$this->display();
	}
	
	//编辑   operation参数  ct编辑连续天数  base编辑基数随机数  st编辑状态
	function sign_continuity_operation()
	{
		$id			=	$_REQUEST['id'];
		$mid		=	$_REQUEST['mid'];//月份id
		$operation	=	trim($_REQUEST['operation']);
		$model		=	D('Sj.Sign');
		$where		=	array('id' => $id,'mid' => $mid);
		if( $_POST ) {
			if( $operation == 'ct' ) {
				$name	=	trim($_POST['name']);
				$count	=	(Int)$_POST['count'];
				if( !$name ) {
					$this->error('连续签到列表奖品说明不能为空');
				}
				if( $count > 28 ) {
					$this->error('连续签到天数配置最大28天');
				}
				$width = 96; $height = 72;
				$date	=	date("Ym/d/");
				if($_FILES['pic_path']['tmp_name']) {
					$pic_path = getimagesize($_FILES['pic_path']['tmp_name']);
					if($pic_path[0] != $width || $pic_path[1] != $height){
						$this->error("分辨率图标大小不符合条件");
					}
					if( !in_array($_FILES['pic_path']['type'], array('image/png','image/jpg','image/jpeg')) ) {
						$this->error("请添加图片格式为：jpg，png的弹窗图片");
					}
					$config['multi_config']['pic_path'] = array(
							'savepath'	 =>	UPLOAD_PATH. '/img/'. $date,
							'saveRule'	 =>	'getmsec',
							'img_p_size' =>	1024 * 200,
					);
				}
				if (!empty($config['multi_config'])) {
					$list = $this->_uploadapk(0, $config);
					foreach($list['image'] as $val) {
						$data[$val['post_name']] = $val['url'];
					}
				}
				$data['count']	=	$count;
				$data['name']	=	$name;
				if( !$count || !preg_match("/^[0-9]*[1-9][0-9]*$/", $count) ) {
					$this->error('天数填写错误，请重试!');
				}
				$where_lt = array(
					'mid'	=>	$mid,
					'id'	=>	array('lt', $id),
				);
				$where_gt = array(
					'mid'	=>	$mid,
					'id'	=>	array('gt', $id),
				);
				$data_lt = $model->table('qd_sign_continuity')->where($where_lt)->order('id desc')->find();
				$data_gt = $model->table('qd_sign_continuity')->where($where_gt)->order('id asc')->find();
				
				if( !empty($data_lt) && $data_lt['count'] >= $count ) {
					$this->error('不能小于或者等于上一级的天数');
				}
				if( !empty($data_gt) && $data_gt['count'] <= $count ) {
					$this->error('不能大于或者等于下一级的天数');
				}
				$result	=	$model->table('qd_sign_continuity')->where($where)->save($data);
				if( $result ) {
					$this -> writelog("已编辑id为{$id}的连续签到",'qd_sign_continuity',$id,__ACTION__ ,'','edit');
					$this->success('操作成功');
				}else {
					$this->error('操作失败');
				}
			}elseif( $operation == 'base' ) {
				$base_num		=	trim($_POST['base_num']);
				$random_start	=	trim($_POST['random_start']);
				$random_end		=	trim($_POST['random_end']);
				if( !preg_match("/^[0-9]*[1-9][0-9]*$/", $base_num) ){
					$this->error('请输入正整数');
				}
				if( $base_num < 0 || $base_num > 100000 ) {
					$this->error('只能填0到100000之间的任意数');
				}
				if( !preg_match('/^[0-9]$/', $random_start) ) {
					$this->error('随机数只能填写0到9的数');
				}
				if( !preg_match('/^[0-9]$/', $random_end) ) {
					$this->error('随机数只能填写0到9的数');
				}
				if($random_end <= $random_start) {
					$this->error('随机区间填写有误');
				}
				$where_lt = array(
						'mid'	=>	$mid,
						'id'	=>	array('lt', $id),
				);
				$where_gt = array(
						'mid'	=>	$mid,
						'id'	=>	array('gt', $id),
				);
				$data_lt = $model->table('qd_sign_continuity')->where($where_lt)->order('id desc')->find();
				$data_gt = $model->table('qd_sign_continuity')->where($where_gt)->order('id asc')->find();
				if( !empty($data_lt) && $data_lt['base_num'] < $base_num ) {
					$this->error('不能大于上一级的基数');
				}
				if( !empty($data_gt) && $data_gt['base_num'] > $base_num ) {
					$this->error('不能小于下一级的基数');
				}
				$data	=	array(
						'base_num'		=>	$base_num,
						'random_start'	=>	$random_start,
						'random_end'	=>	$random_end,	
				);
				$result	=	$model->table('qd_sign_continuity')->where($where)->save($data);
				if( $result ) {
					$this -> writelog("已编辑id为{$id}的连续签到",'qd_sign_continuity',$id,__ACTION__ ,'','edit');
					$this->success('操作成功');
				}else {
					$this->error('操作失败');
				}
			}else {
				$this->error('参数有误');
			}
		}else {
			if( $operation == 'st' ) {
				$status	=	(Int)$_GET['status'];
				$where	=	array('id' => $id);
				$data	=	array('status' => $status);
				$result	=	$model->table('qd_sign_continuity')->where($where)->save($data);
				if( $result ) {
					$this -> writelog("已编辑id为{$id}的连续签到",'qd_sign_continuity',$id,__ACTION__ ,'','edit');
					$this->success('操作成功');
				}else {
					$this->error('操作失败');
				}
			}
			$rows = $model->table('qd_sign_continuity')->where($where)->find();
			$this->assign('id', $id);
			$this->assign('mid', $mid);
			$this->assign('rows', $rows);
			$this->assign('operation', $operation);
			$this->display();
		}
	}
	
}