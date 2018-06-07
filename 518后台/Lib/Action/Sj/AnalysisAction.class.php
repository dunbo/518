<?php
/**
 * 安智网产品管理平台 分析管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:陈志义 2011.5.16
 * ----------------------------------------------------------------------------
*/
class AnalysisAction extends CommonAction {
	//统计管理_下载量统计显示页
	function installAndUninstall(){
		ini_set("memory_limit","-1");

		$Model = new Model();

		$time = $this -> gettimeArr();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		$max_values = 0;

		//jschart数据
		$jsdata = array(//柱体数据
//			array(
//				'name'=>"",
//				'data'=>array()
//			)
		);
		$jscategories = array();//x轴

		$model =new Model();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$submit_day = date("Ymd",strtotime($time[$i]));
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];


			$info = $model->query("
				select * from static_install_uninstall where submit_day = $submit_day
			");
			if(empty($info)){
				$t = array("submit_day"=>$submit_day,"install"=>0,"uninstall"=>0);
			}else{
				$t = $info[0];
			}
			if($t['install']>$max_values)
				$max_values = $t['install'];

			$tmp1[] = (int)$t['install'];
			$tmp2[] = (int)$t['uninstall'];
		}
		$jsdata = array(
			array('name'=>'安装','data'=>$tmp1),
			array('name'=>'卸载','data'=>$tmp2)
		);

		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}

		if(count($jscategories)>10){
			$px = count($jscategories);
			$px = $px*65;
		}else {
			$px = 650;
		}

		$this->assign('px',$px."px");

		$this->assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->assign("r",rand());


		$this->assign("phpdata",json_encode($jsdata));
		$this->assign("phpcategories",json_encode($jscategories));
		$this->display();
		//$this->display('download');
	}
//===============================================================================================================
//用户行为分析
	function userbehavior(){
		$this->display();
	}
//==================================================================================================
	//统计管理_时间函数
	function days_away($date, $away=0) {
		$date_array = explode('-',$date);
		$dst_time  = mktime(1, 1, 1, $date_array[1], $date_array[2], $date_array[0]);
		$today = getdate($dst_time);
		$dt = getdate(mktime(1, 1, 1, $today['mon'], $today['mday']+$away, $today['year']));
		return $dt['year'].'-'.sprintf("%02d" ,$dt['mon']).'-'.sprintf("%02d",$dt['mday']);
	}
	//统计管理_时间段函数
	function gettimeArr($url){
		$time = array();
		if (array_key_exists('fromdate', $_GET)&&array_key_exists('todate', $_GET)){
			$fromdate = $_GET['fromdate'];
			$todate = $_GET['todate'];

			$fromdate = $this->days_away($fromdate, 0);
			$todate = $this->days_away($todate, 0);
			if($todate < $fromdate){
				$this->assign('jumpUrl','/index.php/Message/'.$url);
				$this->error('起始时间大于截止时间,请重新选择');
			}
			if($todate > date('Y-m-d',time())){
				$this->assign('jumpUrl','/index.php/Message/'.$url);
				$this->error('截止时间超出当前时间,请重新选择');
			}
			$fromdatetime = strtotime($fromdate);
			$tovaluetime = strtotime($todate);
			$chatime = $tovaluetime - $fromdatetime;
//			if ($chatime/86400 <7){
				$len = $chatime/86400;
				for($i=0;$i<=$len;$i++){
					$time[$i] = date('Y-m-d',$fromdatetime + $i * 86400);
				}
//			}else{
//				$lintime = ($tovaluetime - $fromdatetime)/7;
//				$len = 7;
//				for($i=$len;$i>=0;$i--){
//					$time[$i] = date('Y-m-d',$fromdatetime + $i * $lintime);
//				}
//			}
		}else{
			$todate = date('Y-m-d',time());
			$tovaluetime = strtotime($todate);
			for($i=7;$i>=0;$i--){
				$time[7-$i] = date('Y-m-d',$tovaluetime - $i * 86400);
			}
		}
		return $time;
	}
	//统计管理_单包下载统计表
	function pkgInstallStatic() {
		ini_set("memory_limit","-1");
		$Model = new Model();
		$time = $this -> gettimeArr();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		$package  = escape_string($_GET['package']);
		$jscategories = array();//x轴
		if(!empty($package)){
		$model =new Model();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];
			$submit_day = strtotime($time[$i]);
			$holdday = $submit_day + 60*60*24;
			$sql = "	select * from sj_download_count where package = '".$package."' and (submit_day  between  $submit_day  and $holdday )";
			$info = $model->query($sql);
			if(empty($info)){
				$t = array("submit_day"=>$submit_day,"install"=>0);
			}else{
				//web_dl_cnt 	mob_dl_cnt 	mob_up_cnt 	ptn_dl_cnt 	wap_dl_cnt
				$info[0]['install'] = $info[0]['web_dl_cnt'] + $info[0]['mob_dl_cnt'] + $info[0]['mob_up_cnt'] + $info[0]['ptn_dl_cnt'] + $info[0]['wap_dl_cnt'];
				$t = $info[0];
			}
			if($t['install']>$max_values)
				$max_values = $t['install'];
				$rgb1 = rand(0, 255);
				$rgb2 = rand(0, 255);
				$rgb3 = rand(0, 255);
				$name = $time[$i];
				$color =  "rgb({$rgb1},{$rgb2},{$rgb3})";
				$data = array((int)$info[0]['web_dl_cnt'] , (int)$info[0]['mob_dl_cnt'] , (int)$info[0]['mob_up_cnt'] , (int)$info[0]['ptn_dl_cnt'] , (int)$info[0]['wap_dl_cnt']);
				for($a=0;$a<5;$a++){
					$rgb1 = rand(0, 255);
					$rgb2 = rand(0, 255);
					$rgb3 = rand(0, 255);
					$color_arr[] = "rgb({$rgb1},{$rgb2},{$rgb3})";
				}
				 $categories = array('web市场下载量','手机下载量','手机更新量','合作方下载量','wap市场下载量');
					$tmp1[] = array(
						'y'=>(int)$t['install'],
						'color' =>$color,
						'drilldown' =>array(
							'name' => $name,
							'categories' => $categories,
							'data'   => $data,
							'color'  => $color_arr,
							 )
						);
		}
		$jsdata = $tmp1;

		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}

		if(count($jscategories)>10){
			$px = count($jscategories);
			$px = $px*65;
		} else {
			$px = 650;
		}
		$this->assign('x_labels',implode(",",$x_labels));
		$this->assign('max_values',$max_values);
		$this->assign('px',$px."px");
		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->assign('package' , $package);
		$this->assign("r",rand());
		$this->assign("phpdata",json_encode($jsdata));
		$this->assign("phpcategories",json_encode($jscategories));
		}
		$this->assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->display();
	}
}

?>
