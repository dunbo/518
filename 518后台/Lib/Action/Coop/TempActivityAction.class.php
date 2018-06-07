<?php
class TempActivityAction extends CommonAction {
	
	public function info_list()
	{
		$model = D('Coop.Activity');
		$where = array('activity_type'=>'sd_developer');
		$count = $model->table('temp_activity_userinfo')->where($where)->count();
		import("@.ORG.Page");
		$param	=	http_build_query($_GET);
		$Page	=	new Page($count, 15, $param);
		$show	=	$Page->show();
		$list	=	$model->table('temp_activity_userinfo')->where($where)-> limit($Page->firstRow . ',' . $Page->listRows) ->order('create_tm DESC') -> select();
		$this->assign('page', $show);
		$this->assign('list', $list);
		$this->display();
	}
	
	public function export()
	{
		$model	=	D('Coop.Activity');
		$where	=	array('activity_type'=>'sd_developer');
		$list	=	$model->table('temp_activity_userinfo')->where($where)->order('create_tm DESC') -> select();
		$filename = date('Ymd').'.csv'; //设置文件名
		$str = '公司名称,产品名称,联系人,手机号,QQ,邮箱,充值金额,提交时间';
		$str = iconv('utf-8','gb2312', $str);
		$str = $str."\n";
		foreach ( $list as $val ) {
			$company_name	=	iconv('utf-8','gb2312', $val['company_name']);
			$product_name	=	iconv('utf-8','gb2312', $val['product_name']);
			$lxname			=	iconv('utf-8','gb2312', $val['lxname']);
			$phone			=	iconv('utf-8','gb2312', $val['phone']);
			$qq				=	iconv('utf-8','gb2312', $val['qq']);
			$email			=	iconv('utf-8','gb2312', $val['email']);
			$money			=	$val['money'];
			$create_tm		=	date('Y-m-d H:i:s', $val['create_tm']);
			$str	.=	$company_name.",".$product_name.",".$lxname.",".$phone.",".$qq.",".$email.",".$money.",".$create_tm."\n"; //用引文逗号分开
		}
		$this->export_csv($filename,$str);die; //导出
		
	}
	
	function export_csv($filename, $data)
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $data;
	}

}

