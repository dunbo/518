<?php

Class NDateRemindAction extends CommonAction{
	 
	function date_remind()
	{
		$model=M();
		$time = $_GET['time'];
		
		$result = $model -> table('pu_config') -> where("config_type='ZHIYOU_TIME'") -> find();
		
		$this -> assign('time',$time);
		$this -> assign('result',$result);
		$this -> display();
	}

	function change_time()
	{
		$model=M();
		$time = $_GET['time'];
		
		if($time!="")
		{
			if(!preg_match('/^(0|([1-9]\d*))$/', $time))
			{
				echo 2;
				return 2;//输入不是正数字
			}
			else
			{
				$data['configcontent']=$time;
			}
		}
		else
		{
			$data['configcontent']=$time;
		}
		$data['uptime']=time();
		$result = $model -> table('pu_config') -> where("config_type='ZHIYOU_TIME'") -> save($data);
		if($result)
		{
			$this->writelog("智友门户距离广告结束的时间提醒修改为".$data['configcontent'],"pu_config","config_type:ZHIYOU_TIME",__ACTION__ ,"","edit");
			echo 1;
			return 1;//成功
		}
		else
		{
			echo 3;
			return 3;
		}	
	}
}
