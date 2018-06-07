<?php
/**
 * 安智网产品管理平台 统计管理
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:高硕 2011.5.16
 * ----------------------------------------------------------------------------
*/
class StatisticalAction extends CommonAction {

	 //活跃用户统计
	//统计管理_活跃用户基础统计图
	function usersImage(){


		ini_set("memory_limit","-1");
		$time = $this -> gettimeArr();
		$Model = new Model('statics');
		$tlen = count($time);
		$info = array();
		for($m=0;$m<$tlen;$m++){
			$info=$Model->where('submit_day between "'.strtotime($time[$m]."00:00:00").'" and "'.strtotime($time[$m]."23:59:59").'"')->find();
			$data1[$m] = empty($info['activeuser_1d']) ? 0:$info['activeuser_1d'];
			$data2[$m] =  empty($info['activeuser_7d']) ? 0:$info['activeuser_7d'];
			$data3[$m] =  empty($info['activeuser_30d']) ? 0:$info['activeuser_30d'];
			$datamd = explode('-',$time[$m]);
			$xdate[$m] = $datamd[1] .'/'. $datamd[2];
		}


		include('jpgraph/jpgraph.php');
		include('jpgraph/jpgraph_line.php');

		$graph=new Graph(500,500);
		$graph->SetScale("intlin");
		$graph->xaxis->SetTickLabels($xdate);
		$graph->img->SetMargin(50,60,60,40);
		$graph->title->SetColor('red'); //设置字体颜色
		$graph->title->Set("活跃用户");
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD); // 设置标题中文字体

		$lineplot=new LinePlot($data1);
		$lineplot->mark->SetType(MARK_UTRIANGLE);
		$lineplot->value->Show();
		$lineplot->value->SetFormat('%d');
		$lineplot->SetLegend("1 day");

		$lineplot->SetColor("blue");
		$graph->Add($lineplot);

		$lineplot=new LinePlot($data2);
		$lineplot->mark->SetType(MARK_UTRIANGLE);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->SetLegend("7 day");
		$lineplot->SetColor("green");
		$graph->Add($lineplot);

		$lineplot=new LinePlot($data3);
		$lineplot->mark->SetType(MARK_UTRIANGLE);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->SetLegend("30 day");
		$lineplot->SetColor("gray");
		$graph->Add($lineplot);

		$graph->yaxis->title->Set("数量");
		$graph->yaxis->title->SetFont(FF_SIMSUN,FS_BOLD);//设置Y轴线条指示字体
		$graph->xaxis->title->Set("日期");
		$graph->xaxis->title->SetFont(FF_SIMSUN,FS_BOLD);//设置X轴线条指示字体
		$graph->Stroke();
	}
	//统计管理_活跃用户统计显示页
	public function users()
	{
		ini_set("memory_limit","-1");
		$time = $this -> gettimeArr('users');

		$Model = new Model('statics');
		$tlen = count($time);
		$info = array();
		$x_labels = array();//x轴的数据
		$data1 = array();//y轴1天的数据
		$data7 = array();//y轴7天的数据
		$data30 = array();//y轴30天的数据

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);
		$jscategories = array();

		$max_values = 0;
		for($m=0;$m<$tlen;$m++){
			$info=$Model->where('submit_day between "'.strtotime($time[$m]."00:00:00").'" and "'.strtotime($time[$m]."23:59:59").'"')->find();
			$data1[$m] = empty($info['activeuser_1d']) ? 0:(int)$info['activeuser_1d'];
			$data7[$m] =  empty($info['activeuser_7d']) ? 0:(int)$info['activeuser_7d'];
			$data30[$m] =  empty($info['activeuser_30d']) ? 0:(int)$info['activeuser_30d'];
			if($data30[$m]>$max_values) $max_values=$data30[$m];
			$datamd = explode('-',$time[$m]);
			$x_labels[$m] = $datamd[1] .'/'. $datamd[2];
			$jscategories[] = $datamd[1] .'/'. $datamd[2];
		}

		$jsdata = array(
			array(
				'name'=>'按1天内',
				'data'=>$data1
			),array(
				'name'=>'按7天内',
				'data'=>$data7
			),array(
				'name'=>'按30天内',
				'data'=>$data30
			)
		);

		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}


		$this -> assign('to_value',$time[count($time)-1]);
		$this->assign('from_value',$time[0]);

		$this->assign('fromdate',$time[0]);
		$this->assign('todate',$time[count($time)-1]);

		$this->assign('data1',implode(",",$data1));
		$this->assign('data7',implode(",",$data7));
		$this->assign('data30',implode(",",$data30));
		$this->assign('max_values',$max_values);
		$this->assign('x_labels',implode(",",$x_labels));
		$this->assign("r",rand());
		$w =  count($data1)*50;
		if($w < 750)
		{
			$w = 750;
		}
		$this->assign('w',$w);
		$this->writelog('查看了活跃用户统计信息','sj_statics','',__ACTION__ ,'','view');

		$this->assign("phpdata",json_encode($jsdata));
		$this->assign("phpcategories",json_encode($jscategories));
		$this->display('jsusers');
		//$this->display('users');
	}
//======================================================================================================
	 //统计管理_每天统计数据
	 function todaydownload($today) {

		//$model =new Model();
		$model = D("Sj.Slave");
		for($i=0,$c=24;$i<$c;$i++){
			$d[] = $i;
			$from = strlen($i) == 2 ? $i.":00:00":"0".$i.":00:00";
			$to = strlen($i) == 2 ? $i.":59:59":"0".$i.":59:59";
			$x_labels[] = $i;
			$jscategories[] = $i;
			$sql = 'select sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt) as co from sj_download_count where submit_day between '.strtotime($today.' '.$from).' and '.strtotime($today.' '.$to);
			//if($today == '2011-08-21') echo $sql."<br/>";
			$info = $model->query($sql);
			if(empty($info)){
				$t = 0;
			}else{
				$t = $info[0]['co'];
			}


			//$t = array('y'=>$t,'color'=>"rgb({$rgb1},{$rgb2},{$rgb3})");
			$name = $today;
			$data[] = (int)$t;
			$categories[] = $i;

		}
		$rgb1 = rand(0, 255);
		$rgb2 = rand(0, 255);
		$rgb3 = rand(0, 255);
		 $color =  "rgb({$rgb1},{$rgb2},{$rgb3})";
		$jsdata = array('name' => $name,'categories' => $categories,'data'=>$data,'color' => $color);
		//if($today == "2011-08-21") var_dump($jsdata);
		return $jsdata;
	}

	//统计管理_下载量统计显示页
	function download(){
		ini_set("memory_limit","-1");
		$Model = D("Sj.DownloadNew");
		$GModel = D("Sj.Slave");
		$inf=$Model->query("select (sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as total,
				(sum(mob_up_cnt) + sum(mob_dl_cnt)) as mobile, (sum(web_dl_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as other  from sj_download_count_day" );
//		$inf=$Model->query("select (sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as total,
//				(sum(mob_up_cnt) + sum(mob_dl_cnt)) as mobile, (sum(web_dl_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as other  from sj_download_count" );
		$sum_total = $inf[0]['total'];
		$sum_mobile = $inf[0]['mobile'];
		$sum_other = $inf[0]['other'];

		$inf=$Model->query("select (sum(game_dl_cnt)+sum(game_up_cnt)) as game from sj_game_download_count" );
		$sum_game = $inf[0]['game'];
		$sum_total += $inf[0]['game'];

		$inf=$Model->query("select (sum(hd_dl_cnt)+sum(hd_up_cnt)) as hd from sj_hd_download_count" );
		$sum_hd = $inf[0]['hd'];
		$sum_total += $inf[0]['hd'];


		$this->assign('sum_total',$sum_total);
		$this->assign('sum_mobile',$sum_mobile);
		$this->assign('sum_other',$sum_other);
		$this->assign('sum_game',$sum_game);
		$this->assign('sum_hd',$sum_hd);

		$time = $this -> gettimeArr('download');
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		$infodl=$Model->query("SELECT (sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as total,
				(sum(mob_up_cnt) + sum(mob_dl_cnt)) as mobile, (sum(web_dl_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as other FROM sj_download_count_day where submit_day between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."' ");
//		$infodl=$Model->query("SELECT (sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as total,
//				(sum(mob_up_cnt) + sum(mob_dl_cnt)) as mobile, (sum(web_dl_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as other FROM sj_download_count where submit_day between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."' ");
		$count_total = $infodl[0]['total'];
		$count_mobile = $infodl[0]['mobile'];
		$count_other = $infodl[0]['other'];

		$infodl=$Model->query("SELECT (sum(game_dl_cnt)+sum(game_up_cnt)) as game
				from sj_game_download_count where submit_day between " . strtotime($from_value."00:00:00") ." and " . strtotime($to_value."23:59:59"));
		$count_game = $infodl[0]['game'];
		$count_total += $count_game;

		$infodl=$Model->query("SELECT (sum(hd_dl_cnt)+sum(hd_up_cnt)) as hd
				from sj_hd_download_count where submit_day between " . strtotime($from_value."00:00:00") . " and " . strtotime($to_value."23:59:59"));
		$count_hd = $infodl[0]['hd'];
		$count_total += $count_hd;

		$this->assign('count_total',$count_total);
		$this->assign('count_mobile',$count_mobile);
		$this->assign('count_other',$count_other);
		$this->assign('count_game',$count_game);
		$this->assign('count_hd',$count_hd);

		//flash 数据
		$x_labels = array();//x轴的数据
		$values = array();//y轴的数据
		$total_max_values = $mobile_max_values = $mobile_dl_max_values = $mobile_up_max_values = $other_max_values = $game_max_values = $hd_max_values = 0;

		//jschart数据
		$result = array(
			'total' => array(),
			'mobile' => array(),
			'other' => array(),
		);

		$jscategories = array();

		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$day = $str[1].'/'.$str[2];
			$x_labels[] = $day;
			$jscategories[] = $day;
			$start_tm = strtotime($time[$i]."00:00:00");
			$end_tm = strtotime($time[$i]."00:00:00");
			$info = $Model->query('
				select (sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as total,
				(sum(mob_up_cnt) + sum(mob_dl_cnt)) as mobile, sum(mob_dl_cnt) as mobile_dl, sum(mob_up_cnt) as mobile_up, (sum(web_dl_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt)) as other
				from sj_download_count_day
				where submit_day between ' . $start_tm . ' and ' . $end_tm
			);
			if(!empty($info)){
				$t = $info[0];
				if (intval($t['total']) > $total_max_values) {
					$total_max_values = intval($t['total']);
				}
				if (intval($t['mobile']) > $mobile_max_values) {
					$mobile_max_values = intval($t['mobile']);
				}
				if (intval($t['mobile_dl']) > $mobile_dl_max_values) {
					$mobile_dl_max_values = intval($t['mobile_dl']);
				}
				if (intval($t['mobile_up']) > $mobile_up_max_values) {
					$mobile_up_max_values = intval($t['mobile_up']);
				}
				if (intval($t['other']) > $other_max_values) {
					$other_max_values = intval($t['other']);
				}
				$result['total'][] = array('y' => intval($t['total']), 'color' => 'colors[' . $i % 9 . ']');
				$result['mobile'][] = array('y' => intval($t['mobile']), 'color' => 'colors['. $i % 9 . ']');
				$result['mobile_dl'][] = array('y' => intval($t['mobile_dl']), 'color' => 'colors['. $i % 9 . ']','date'=> $start_tm);
				$result['mobile_up'][] = array('y' => intval($t['mobile_up']), 'color' => 'colors['. $i % 9 . ']','date'=> $start_tm);
				$result['other'][] = array('y' => intval($t['other']), 'color' => 'colors['. $i % 9 .']');
			}

			$info = $Model->query('
				select (sum(game_dl_cnt)+sum(game_up_cnt)) as game
				from sj_game_download_count
				where submit_day between ' . $start_tm . ' and ' . $end_tm
			);
			if(!empty($info)){
				$t = $info[0];
				if (intval($t['game']) > $game_max_values) {
					$game_max_values = intval($t['game']);
				}
				$result['game'][] = array('y' => intval($t['game']), 'color' => 'colors['. $i % 9 .']');
			}

			$info = $Model->query('
				select (sum(hd_dl_cnt)+sum(hd_up_cnt)) as hd
				from sj_hd_download_count
				where submit_day between ' . $start_tm . ' and ' . $end_tm
			);
			if(!empty($info)){
				$t = $info[0];
				if (intval($t['hd']) > $hd_max_values) {
					$hd_max_values = intval($t['hd']);
				}
				$result['hd'][] = array('y' => intval($t['hd']), 'color' => 'colors['. $i % 9 .']');
			}

		}

		$total_max_values = ceil($total_max_values * 1.5 / 50) * 50;
		if ($total_max_values < 50) {
			$total_max_values = 50;
		}
		$mobile_max_values = ceil($mobile_max_values * 1.5 / 50) * 50;
		if ($mobile_max_values < 50) {
			$mobile_max_values = 50;
		}
		$mobile_dl_max_values = ceil($mobile_dl_max_values * 1.5 / 50) * 50;
		if ($mobile_dl_max_values < 50) {
			$mobile_dl_max_values = 50;
		}
		$mobile_up_max_values = ceil($mobile_up_max_values * 1.5 / 50) * 50;
		if ($mobile_up_max_values < 50) {
			$mobile_up_max_values = 50;
		}
		$other_max_values = ceil($other_max_values * 1.5 / 50) * 50;
		if ($other_max_values < 50) {
			$other_max_values = 50;
		}


		$this->assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->assign("r",rand());

		$this->assign('x_labels',implode(",",$x_labels));

		$this->assign('total_max_values',$total_max_values);
		$this->assign('mobile_max_values',$mobile_max_values);
		$this->assign('mobile_dl_max_values',$mobile_dl_max_values);
		$this->assign('mobile_up_max_values',$mobile_up_max_values);
		$this->assign('other_max_values',$other_max_values);
		$this->assign('game_max_values',$game_max_values);
		$this->assign('hd_max_values',$hd_max_values);

		$this->assign("total_phpdata", str_replace('"', '', json_encode($result['total'])));
		$this->assign("mobile_phpdata", str_replace('"', '', json_encode($result['mobile'])));
		$this->assign("mobile_dl_phpdata", str_replace('"', '', json_encode($result['mobile_dl'])));
		$this->assign("mobile_up_phpdata", str_replace('"', '', json_encode($result['mobile_up'])));
		$this->assign("other_phpdata", str_replace('"', '', json_encode($result['other'])));
		$this->assign("game_phpdata", str_replace('"', '', json_encode($result['game'])));
		$this->assign("hd_phpdata", str_replace('"', '', json_encode($result['hd'])));
		$w = count($result['total'])*50;
		if($w < 800)
		{
			$w=800;
		}
		$this->assign('w',$w);
		$this->writelog('查看了下载量统计信息','pu_channel_first','',__ACTION__ ,'','view');
		$this->assign("phpcategories",json_encode($jscategories));
		$this->display('jsdownload');
		//$this->display('download');
	}

	//统计管理_下载量统计查看详细
	//Create By 黄文强 2012/10/29
	function download_detail()
	{
		date_default_timezone_set('Asia/Chongqing');
		$channel_list = array(
			//'2f85d65a9a0be41f9696e1e87f20aee0ca6b80d0' => 'a360',
			'c981a6e01887b89c942304613d3580366d489097' => 'qq',
			//'aada1945ac8cf34fc808892d2e53b172baca124c' => 'sina',
			'4f50b7ad55e51f2c9bf1242fd39e9cbef821409e' => 'uc',
			'32b86b304c9426f01d17dae491d9e6641bba7218' => 'baidu',
			'85ec11c52cfd7bfd3b54ff63cbf0d07e46640b5c' => 'wdj',
			//'eadaad39aae39f4106e28b4795ae55ff9d535127' => 'tencent',
			'ce78d7e887ac8583a003e005c0a9ea99f7c357db' => 'hezuosousuo',
			'877d6571e9d34313071385459fb00917788d9e13' => 'tencentass',
			'fd813da0e82fc130ce6a284793f02c2b57ca3073' => 'hisense',
			'231e062b072d7effe6ac1505b3b6ce63f65ea17e' => 'wap',
			'902ba004d1140689294d3f2492ec4a07c82b7450' => 'web',
		);
		$from_value = isset($_GET['fromdate']) ? $_GET['fromdate'] : date('Y-m-d', time() - 7 * 86400);
		$to_value = isset($_GET['todate']) ? $_GET['todate'] : date('Y-m-d', time() - 1 * 86400);
		$from_ts = strtotime($from_value);
		$to_ts = strtotime($to_value);
		$Model = D("Sj.DownloadNew");
		$time = $from_ts;
		for ($i = 0; $time < $to_ts; $i++)
		{
			$time = $from_ts + $i * 86400;
			$jscategories[] = date('m/d', $time);
			//echo $time,"\n";
			$inf=$Model->query(
				"SELECT chl,dl_count FROM `sj_download_count_detail` WHERE submit_day=$time;"
			);
			//if (empty($inf))
			//{
				foreach ($channel_list as $k)
				{
					$result[$k][$i] = array('y' => 0, 'colors' => 'colors[' . $i % 9 . ']');
				}
				$result['other'][$i] = 0;
				//continue;
			//}
			$result['other'][$i] = 0;
			foreach ($inf as $k)
			{
				if (isset($channel_list[$k['chl']]))
					$result[$channel_list[$k['chl']]][$i] = array('y' => intval($k['dl_count']), 'color' => 'colors[' . $i % 9 . ']');
				else
					$result['other'][$i] += $k['dl_count'];
			}
			$result['other'][$i] = array('y' => $result['other'][$i], 'color' => 'colors[' . $i % 9 . ']');
		}
		//var_dump($result);
		$w = count($result['web'])*50;
		if($w < 800)
		{
			$w=800;
		}
		$this->assign('w',$w);
		foreach ($channel_list as $k)
		{
			$this->assign("{$k}_phpdata", str_replace('"', '', json_encode($result[$k])));
		}
		$this->assign("other_phpdata", str_replace('"', '', json_encode($result['other'])));
		$this->assign("phpcategories",json_encode($jscategories));
		$this->assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->display('jsdownload_detail');
	}

	//统计管理_下载量统计基础统计图
	function downloadImage(){

		$time = $this -> gettimeArr();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$date[] = $str[1].'/'.$str[2];
		}

		//	$infodl=$Model->query("SELECT sum( cnt ) as count FROM sj_download_cnt where submit_day between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."' AND ACTION IN ( 2, 3 ) ");

		$model = D("Sj.DownloadNew");
		for($j=0,$c=count($date);$j<$c;$j++){
			//$info = $model->query("select cnt from download_cnt where submit_day='".$time[$j]."' and action in (2,3)");
			$info = $model->query('select sum(web_dl_cnt)+sum(mob_dl_cnt)+sum(mob_up_cnt)+sum(ptn_dl_cnt)+sum(wap_dl_cnt) as co from sj_download_count where submit_day between "'.strtotime($time[$j]."00:00:00").'" and "'.strtotime($time[$j]."23:59:59").'"');
			if(empty($info)){
				$data[$j] = 0;
			}else{
				$data[$j] = $info[0]['co'];
			}
		}

		include('jpgraph/jpgraph.php');
		include('jpgraph/jpgraph_bar.php');

		$graph = new Graph(500,500);
		$graph->SetScale("textlin");
		$graph->xaxis->SetTickLabels($date);
		$graph->SetShadow();
		$graph->img->SetMargin(50,60,60,40);
		$bplot = new BarPlot($data);
		$bplot->SetFillColor('orange');
		$bplot->value->Show();
		$bplot->value->SetFormat('%d');
		$bplot->SetValuePos('center');
		$bplot->SetWidth(0.5);
		$graph->Add($bplot);
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD); //设置字体，类型，大小
		$graph->title->SetColor('red'); //设置字体颜色
		$graph->title->Set("下载量");
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD); // 设置标题中文字体
		$graph->yaxis->title->Set("数量");
		$graph->yaxis->title->SetFont(FF_SIMSUN,FS_BOLD);//设置Y轴线条指示字体
		$graph->xaxis->title->Set("日期");
		$graph->xaxis->title->SetFont(FF_SIMSUN,FS_BOLD);//设置X轴线条指示字体
		$graph->Stroke();
	}
//===============================================================================================================
	//统计管理_期间激活量查询
	function active_count($from, $to) {
		$Model = new Model();
		$sql = 'SELECT count(mt) FROM `pu_channel_first` where submit_tm > "' .strtotime($from).'" AND submit_tm <= "'.strtotime($to).'" and pid=1 and status=1';
		$info = $Model->query($sql);
		return intval(array_shift($info[0]));
	}
	//统计管理_激活量基础统计图
	function activateqImage(){
		$time = $this -> gettimeArr();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$date[] = $str[1].'/'.$str[2];
		}
		$Model = new Model('pu_channel_first');
		for($m=0,$c=count($date);$m<$c;$m++){
			$times = $time[$m].' 23:59:59';
			$timee = $time[$m].' 00:00:00';
			//$info = $Model -> where("submit_tm <= '".strtotime($times)."' and submit_tm > '".strtotime($timee)."'")->select();
			$info=$Model->query("select mt from `pu_channel_first` where submit_tm <= '".strtotime($times)."' and submit_tm >='".strtotime($timee)."' and pid=1 and status=1");
			$slen = count($info);
			$data[$m] = $slen;
		}
		include('jpgraph/jpgraph.php');
		include('jpgraph/jpgraph_line.php');
		$graph=new Graph(500,500);
		$graph->SetScale("intlin");
		$graph->xaxis->SetTickLabels($date);
		$graph->img->SetMargin(50,40,60,40);
		$graph->title->SetColor('red'); //设置字体颜色
		$graph->title->Set("激活量");
		$graph->title->SetFont(FF_SIMSUN,FS_BOLD); // 设置标题中文字体
		$lineplot=new LinePlot($data);
		$lineplot->mark->SetType(MARK_UTRIANGLE);
		$lineplot->value->Show();
		$lineplot->value->SetFormat('%d');
		$lineplot->SetLegend("激活量");
		$graph->legend->SetFont(FF_SIMSUN,FS_BOLD); //设置线条指示字体
		$graph->yaxis->title->Set("数量");
		$graph->yaxis->title->SetFont(FF_SIMSUN,FS_BOLD);//设置Y轴线条指示字体
		$graph->xaxis->title->Set("日期");
		$graph->xaxis->title->SetFont(FF_SIMSUN,FS_BOLD);//设置X轴线条指示字体
		$lineplot->SetColor("blue");
		$graph->Add($lineplot);
		$graph->Stroke();
	}
	//统计管理_激活量统计显示页
	function activatequantity(){
//		if(!($_SESSION['admin']['admin_id']==9||$_SESSION['admin']['admin_id']==7||$_SESSION['admin']['admin_id']==39))
//			exit("系统维护中");
		$Model = new Model();
		$content = file_get_contents('/data/att/permanent_log/last_mt_count');
		$total = 0;
		$time = 0;
		if (!empty($content)) {
			list($total, $time) = explode(',', $content);
		}

		$inf = $Model->query('SELECT count(mt) as sum ,unix_timestamp(now()) as last_time FROM `pu_channel_first` where  pid=1 and status=1 and submit_tm>'.$time);
		$sum = $total + $inf[0]['sum'];
		file_put_contents('/data/att/permanent_log/last_mt_count', "{$sum},{$inf[0]['last_time']}");
		//$inf = $Model->query('SELECT count(mt),unix_timestamp(now()) as sum FROM `pu_channel_first` where  pid=1 and status=1');
		//$sum=$inf[0][sum];
		$this->assign('sum',$sum);

		$time = $this -> gettimeArr('activatequantity');
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		$count=$this->active_count($from_value, $to_value." 23:59:59");

		//flash 数据
		$x_labels = array();//x轴的数据
		$values = array();//y轴的数据
		$max_values = 0;

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);
		$jscategories = array();

		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];
			$times = $time[$i].' 23:59:59';
			$timee = $time[$i].' 00:00:00';
			//$info = $Model -> where("submit_tm <= '".strtotime($times)."' and submit_tm > '".strtotime($timee)."'")->select();
			$info=$Model->query("select count(*) as c from `pu_channel_first` where submit_tm <= '".strtotime($times)."' and submit_tm >='".strtotime($timee)."' and pid=1 and status=1");
			$slen =$info[0]['c'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;
		}
		$jsdata = array(
			array(
				'name'=>'激活量',
				'data'=>$values
			)
		);
		$w = count($values)*50;
		if($w < 750)
		{
			$w = 750;
		}
		$this->assign('w',$w);
		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}

		$this->assign("r",rand());

		$this->assign('x_labels',implode(",",$x_labels));
		$this->assign('values',implode(",",$values));
		$this->assign('max_values',$max_values);

		$this->assign('count',$count);

		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);

		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->writelog('查看了激活量统计信息','pu_channel_first','',__ACTION__ ,'','view');

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jsactivatequantity');
//		$this->display('activatequantity');
	}
//===============================================================================================================
	//统计管理_渠道用户量统计显示页
	function channel(){
//		if(!($_SESSION['admin']['admin_id']==9||$_SESSION['admin']['admin_id']==7||$_SESSION['admin']['admin_id']==39))
//			exit("系统维护中");

		import("@.ORG.Page");
		$Model = new Model();
		$channel_category = D('Sj.ChannelCategory');
		$category_list = $channel_category->getCategory();
			$in_cid_array = array();
			$in_cid = array();
			$not_in_string = '';
			$in_string = '';
			$on_where = 'a.cid = b.cid AND a.status=1 AND b.status=1 AND b.pid=1';
		$loop_where = '';
		if($_REQUEST['is_submit']){
			// XXX: 渠道检查形同虚设
			if(isset($_REQUEST['cid'])&&$_REQUEST['cid']){
				$cid_z = $_REQUEST['cid'];
				$cid_array=$cid_z;
				foreach($cid_array as $k){
					if($k!=0){
						$new_cid_array[]=$k;
					}
				}
				$cid_z=implode(",",$new_cid_array);
				$in_string = ' a.cid IN('.$cid_z.')';
				$where = " WHERE {$in_string} ";
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
			//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
				if(!empty($zh_res) && ($zh_res['filter_type'])==1){
					$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
				}else{
					$source_type = USER_FILTER_TYPE;
					$target_type = CHANNEL_FILTER_TYPE;
					$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
					$res = $Model->query($sql);
				}
				foreach ($res as $item) {
					$in_cid_array[] = $item['cid'];
				}
				$cid_m=implode(",",$in_cid_array);
				$not_in_string = empty($in_cid_array) ? '': ' a.cid IN('.$cid_m. ')';
				$where = empty($not_in_string) ? '' : " WHERE {$not_in_string} ";
			}
			if(empty($_REQUEST['fromdate'])||empty($_REQUEST['todate'])){
				$this->error("请正确选择时间区间！！！");
			}else{
				$from_time = strtotime( $_REQUEST['fromdate']);
				$to_time = strtotime($_REQUEST['todate']. ' 23:59:59');

				$from_value = date("Y-m-d",$from_time);
				$to_value = date("Y-m-d",$to_time);
				$on_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";
				$loop_where .= " AND submit_tm >={$from_time} AND submit_tm <{$to_time}";

				$this->assign('start',$from_value);
				$this->assign('end',$to_value);
			}
		}else{
			if(isset($_REQUEST['cid'])&&$_REQUEST['cid']){
			// XXX: 渠道检查形同虚设
			$cid_z = $_REQUEST['cid'];
			$cid_array=explode(",",$cid_z);
			$in_string = ' a.cid IN('.$cid_z.')';
			$where = " WHERE {$in_string} ";
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
				//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
						'source_type' => $source_type,
						'source_value' => $_SESSION['admin']['admin_id'],
						'target_type' => $target_type
					);
					$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
					if(!empty($zh_res) && ($zh_res['filter_type'])==1){
						$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
					}else{
						$source_type = USER_FILTER_TYPE;
						$target_type = CHANNEL_FILTER_TYPE;
						$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
						$res = $Model->query($sql);
					}
				foreach ($res as $item) {
					$in_cid_array[] = $item['cid'];
				}
				$cid_z_a=implode(",",$in_cid_array);
				$not_in_string = empty($in_cid_array) ? '': ' a.cid IN('.$cid_z_a. ')';
				$where = empty($not_in_string) ? '' : " WHERE {$not_in_string} ";
			}
			if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$from_time = strtotime( $_REQUEST['fromdate']);
			$to_time = strtotime($_REQUEST['todate']. ' 23:59:59');

			$from_value = date("Y-m-d",$from_time);
			$to_value = date("Y-m-d",$to_time);
			$on_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";
			$loop_where .= " AND submit_tm >={$from_time} AND submit_tm <{$to_time}";

			$this->assign('start',$from_value);
			$this->assign('end',$to_value);
			}else{
				$to_value=date("Y-m-d",time());
				$from_value=date("Y-m-d",strtotime("-1 week"));
				$biao=2;
				$from_time = strtotime("-1 week");
				$to_time = time();
				$this->assign('start','');
				$this->assign('end','');
			}
		}
		$sum_user_where = $where;
		$where .= empty($where) ? ' where a.cid IN("") and a.status=1 ' : ' and a.status=1 ';

		$sql = "SELECT count(DISTINCT a.cid) as counts FROM sj_channel a LEFT JOIN pu_channel_first_state b ON ({$on_where}) {$where}";
		$res = $Model->query($sql);
		$count = $res[0]['counts'];
		$param = http_build_query($_GET);
		//print_r($param);
		if($biao==2){
			$param=$param;
		}else{
			$param="fromdate=".$from_value."&todate=".$to_value."&cid=".$cid_z."&".$param;
		}
		$Page = new Page($count, 10, $param);
		$Page->callback = 'return get_params();';
		$page = $Page->show(); //$Page->firstRow.','.$Page->listRows

		$sql = "SELECT a.category_id, a.cid,sum(b.counts) as num, a.chname FROM sj_channel a LEFT JOIN pu_channel_first_state b ON ({$on_where}) {$where} group by a.cid order by sum(b.counts) desc LIMIT {$Page->firstRow}, {$Page->listRows}";
		$info=$Model->query($sql);
		$c_info = array();
		$category_result = array();
		foreach($info as $k => $v){
			$category_id = $v['category_id'];
			if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
			$category_result[$category_id]['result'][] = $v;

			$cid = $v['cid'];
			$c_info[$cid] = array();
			$sql = "SELECT cid,sum(counts) as num FROM `sj_channel_coefficient_state` where cid={$cid} AND status=1 {$loop_where}";
			$t=$Model->query($sql);
			$t = array_pop($t);
			$c_info[$cid]['num'] = $t['num'];
			$state_num += $t['num'];
			$where = " and `create_time` <= {$to_time}";
			$sql = "SELECT cid,coefficient FROM `sj_channel_coefficient` where cid={$v['cid']} {$where} order by id desc limit 1;";
			$t=$Model->query($sql);
			if ($t) {
				//$t = array_pop($t);
				$c_info[$cid]['coefficient'] = $t[0]['coefficient'];
			} else {
				$c_info[$cid]['coefficient'] = 100;
			}
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_COEFFICIENT_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}' AND filter_type = 2";
		$res = $Model->query($sql);
		if (empty($res)) {
			$this->assign('show_coefficient',false);
		} else {
			$this->assign('show_coefficient',true);
		}


		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_TOTAL_FILTER_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
		$res = $Model->query($sql);
		$sum = false;
		if (empty($res)) {
			$sql = "SELECT sum(b.counts) as sum  FROM sj_channel a LEFT JOIN pu_channel_first_state b ON ({$on_where}) {$sum_user_where}";
			$r=$Model->query($sql);
			$sum = $r[0]['sum'];
			$this->assign('showsum',true);
		} else {
			$this->assign('showsum',false);
		}
		$zh_cids=array();
		foreach($cid_array as $k=>$val){
			if($val!=0){
				$zh_cids[$k]['cid']=$val;
				$c_where['status']=1;
				$c_where['cid']=$val;
				$zh_chname=$Model->table("sj_channel")->where($c_where)->getfield("chname");
				$zh_cids[$k]['chname']=$zh_chname;
			}
		}

		$fromdate = $_REQUEST['fromdate'];
		$todate = $_REQUEST['todate'];

		$channel_arr = $_REQUEST['cid'];
		foreach($channel_arr as $key => $val){
			$channel_str_go .= $val.',';
		}
		$channel_str = substr($channel_str_go,0,-1);
		$this -> assign('channel',$channel_str);
		$this -> assign('start_tm',$fromdate);
		$this -> assign('end_tm',$todate);
		$this -> assign('num',$state_num);
		$this->assign('sum',$sum);
		$this->assign('info',$info);
		$this->assign("cid_array",$zh_cids);
		$this->assign("zh_cid_str",$cid_z);
		$this->assign('category_list',$category_result);
		$this->assign('c_info',$c_info);
		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->assign('page',$page);
		$this->writelog('查看了激活用户量统计信息','pu_channel_first_state','',__ACTION__ ,'','view');
		$this->display('channel');
	}

	//渠道用户量统计显示列表 -- 导出列表
	function derive_data(){
		$Model = new Model();
		$start = strtotime($_REQUEST['start_tm']);
		$start_to = date('Ymd 00:00:00',$start);
		$end = strtotime($_REQUEST['end_tm']);
		$end_to = date('Ymd 23:59:59',$end);
		$start_tm = strtotime($start_to);
		$end_tm = strtotime($end_to);
		$channel_arr = explode(',',$_REQUEST['channel_all']);
		if(empty($start_tm)){
			$end_tm = time();
			$start_tm = time() - 7*86400;
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;

		if($channel_arr[0] == ''){
			$ch_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$ch_res = $Model->table('sj_admin_filter')->where($ch_map)->find();

			if(!empty($ch_res) && ($ch_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}

			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
		}else{
			$in_cid_array = $channel_arr;
		}

		foreach($in_cid_array as $key => $val){
			$sql = "select sum(counts) from pu_channel_first_state where cid = $val and submit_tm >= $start_tm and submit_tm <= $end_tm and status=1";
			$counts_go = $Model -> query($sql);
			if($counts_go[0]['sum(counts)'] == null){
				$counts_go[0]['sum(counts)'] = 0;
			}
			$counts_total[$val] = $counts_go[0]['sum(counts)'];
		}
		asort($counts_total);
		$counts_total = array_reverse($counts_total,true);
		foreach($counts_total as $key => $val){
			$channel[] = $key;
		};

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$result_id = $Model -> table('sj_channel') -> where($where) -> select();
			$category_id = $result_id[0]['category_id'];
			$category_arr[] = $category_id;
		}

		foreach($category_arr as $key => $val){
			$where_go['category_id'] = $val;
			$where_go['status'] = 1;
			$result_category = $Model -> table('sj_channel_category') -> where($where_go) -> select();
			if($result_category[0]['name'] == null){
				$result_category[0]['name'] = '未知';
			}
			$category_name[] = $result_category[0]['name'];
		}

		foreach($category_name as $Key => $val){
			$category .= $val.',';
		}

		$errand = ($end_tm - $start_tm)/86400;

		for($i=0;$i<=$errand;$i++){
			$time[$i] = date('Y-m-d',$start_tm + $i*86400);
		}

		foreach($time as $t => $m){
			$start_go = strtotime($m.'00:00:00');
			$end_go = strtotime($m.'23:59:59');
			foreach($channel as $key => $val){
				$where_counts['_string'] = "cid = $val and status = 1 and pid = 1 and submit_tm >= $start_go and submit_tm <= $end_go";
				$result_counts = $Model -> table('pu_channel_first_state') -> where($where_counts) -> select();

				$result_counts_all[$key] = $result_counts;
			}

		$counts_all[$m] = $result_counts_all;

	}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				if($v == null){
					$v = 0;
				}
				$val[$k] = $v;
			}
			$counts_all[$key] = $val;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				$result['counts'] = $v[0]['counts'];
				if($v[0]['counts'] == ''){
					$result['counts'] = 0;
				}
				$all[$k] = $result['counts'];
			}
			$result_all[$key]['counts'] = $all;
		}

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$where['status'] = 1;
			$result = $Model -> table('sj_channel') -> where($where) -> select();
			$channel_go .= $result[0]['chname'].',';
		}

		foreach($result_all as $key => $val){
			foreach($val['counts'] as $v => $k){
				$the_result[$v]['counts'] = $k;
			}
			$all_result[$key] = $the_result;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$result_go[$k] = $v['counts'];
			}
			$all_result[$key] = $result_go;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$file_str .= $v.',';
			}
			$list[$key] = $file_str;
			$file_str="";
		}

		foreach($list as $key => $val){
			$file_arr[] = $key.','.$val;
		}

		foreach($file_arr as $key => $val){
			$file_str_go .= $val."\n";
		}
		foreach($counts_total as $key => $val){
			$total_to .= $val.',';
		}
		$total_go = '合计,'.$total_to;

		$file_go = 'Channel_User'.time();
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition:attachment;filename='.$file_go.'.csv');
		header('Content-Type:APPLICATION/OCTET-STREAM');
		ob_start();
		$header_str =  iconv("UTF-8",'GBK',"日期,".$channel_go."");
		$category_str = iconv("UTF-8",'GBK',"类别,".$category."");
		$file_str_go=  iconv("UTF-8",'GBK',$file_str_go);
		$total_str = iconv("UTF-8",'GBK',$total_go);
		echo $category_str."\n";
		echo $header_str."\n";
		echo $file_str_go;
		echo $total_str;
		ob_end_flush();
		exit;
	}
	//统计管理_渠道用户统计显示页
	function channelImage(){
		$time = $this -> gettimeArr();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);
		$jscategories = array();

		$Model = new Model();
		$max_values = 0;
		$values = array();
		$values2 = array();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$date[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];

			$info=$Model->query('select sum(counts) as count from pu_channel_first_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm   between "'.strtotime($time[$i]."00:00:00").'" and "'.strtotime($time[$i]."23:59:59").'"');
			$info1[]=$info;

			$slen =$info[0]['count'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;

			$info2=$Model->query('select sum(counts) as count from sj_channel_coefficient_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm = "'.strtotime($time[$i]."00:00:00").'"');
			$values2[] = (int)$info2[0]['count'];
		}

		$jsdata = array(
			array(
				'name'=>'激活量_未扣量',
				'data'=>$values
			),
			array(
				'name'=>'激活量_扣量',
				'data'=>$values2
			),
		);

		foreach($info1 as $k=>$v){
			foreach($v as $a=>$b){
			  foreach($b as $c=>$d){
				$arr[]=$d;
			  }
			}
		}

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jschannelImage');

//		include('jpgraph/jpgraph.php');
//		include('jpgraph/jpgraph_line.php');
//		$graph=new Graph(500,500);
//		$graph->SetScale("intlin");
//		$graph->xaxis->SetTickLabels($date);
//		$graph->img->SetMargin(50,60,60,40);
//		$graph->title->SetColor('red'); //设置字体颜色
//		$graph->title->Set("渠道用户");
//		$graph->title->SetFont(FF_SIMSUN,FS_BOLD); // 设置标题中文字体
//
//		$lineplot=new LinePlot($arr);
//		$lineplot->mark->SetType(MARK_UTRIANGLE);
//        $lineplot->value->Show();
//		$lineplot->SetLegend("用户量");
//		$graph->legend->SetFont(FF_SIMSUN,FS_BOLD); //设置线条指示字体
//		$lineplot->SetColor("blue");
//		$graph->Add($lineplot);
//		$graph->Stroke();
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

	//by 张辉  时间函数开始
		function zh_gettimeArr($end_time){

			$todate = date('Y-m-d',time()-86400);
			$to_end_time=strtotime($end_time);
			$tovaluetime = strtotime($todate);
			if($to_end_time < $tovaluetime){
				$tovaluetime=$to_end_time;
			}else{
				$tovaluetime=$tovaluetime;
			}
			for($i=7;$i>=0;$i--){
				$time[7-$i] = date('Y-m-d',$tovaluetime - $i * 86400);
			}
			return $time;
		}
	// by 张辉 时间函数结束
	function getWandoujiaStatic()
	{
		$now_date = date('Y-m-d');
		$start_date = isset($_POST['fromdate']) ? $_POST['fromdate'] : $now_date;
		$end_date = isset($_POST['todate']) ? $_POST['todate'] : $now_date;

		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		$prefix = (GO_SERVER_IP == "192.168.0.99") ? '119.57.50' : '192.168.1';
		$logs = array(
			"http://{$prefix}.114:81/www.goapk.com/",
			"http://{$prefix}.115:81/www.goapk.com/",
			"http://{$prefix}.84:81/www.goapk.com/",
		);
		for ($i=$start_time;$i<=$end_time;$i=$i+86400) {
			$day = date('Y-m-d', $i);

			for ($j=0;$j<24;$j++){
				$hour = sprintf('%02d', $j);
				$file = $day. '/parter_'. $hour. '.json';
				foreach($logs as $log) {
					$fp = fopen($log. $file, "r");
					if ($fp) {
						while (!feof($fp)) {
							$line = fgets($fp);
							if (strlen($line) == 0) continue;
							$json = json_decode($line, true);
							if (!array_key_exists('package', $json)||$json["action"]!="wandoujia") {
								continue;
							}
							$download_data[$day] += 1;
						}
						fclose($fp);
					}
				}
			}
		}

		$this->assign('download_data', $download_data);
		$this->assign('start_date', $start_date);
		$this->assign('end_date', $end_date);
		$this->display();
	}
	//统计管理_渠道下载量统计
	function channelDownloadStatic() {
		$mObj = new Model();
		$model=M();
		$ch_obj = M('channel');
		import("@.ORG.Page");
		//$time = $this -> gettimeArr('channelDownloadStatic');
		// $from_value = $_POST['fromdate'];
		//$to_value = $_POST['todate'];
		//获取渠道cid
		// $cid_array = $_REQUEST['cid'];
		$zh_where="";
		if($_REQUEST['is_submit']){
			if(empty($_REQUEST['fromdate'])||empty($_REQUEST['todate'])){
				$this->error("请选择时间");
			}else{
				$start_time=strtotime($_REQUEST['fromdate']." 00:00:00");
				$end_time=strtotime($_REQUEST['todate']." 23:59:59");
				$from_value=$_REQUEST['fromdate'];
				$to_value=$_REQUEST['todate'];
				/* if(($end_time-$start_time)>3600*24*184){
					$this -> error('选择区间超过六个月！！');
				} */
				$zh_where =" date >='".$start_time."' and date<= '".$end_time."' ";
			}
			if($_REQUEST['cid']){
				$cid_array=$_REQUEST['cid'];
				//print_r($cid_array);exit;
				foreach($cid_array as $k){
					if($k!=0){
						$new_cid_array[]=$k;
					}
				}
				//print_r($new_cid_array);exit;
				$cid=implode(",",$new_cid_array);
				$zh_where.="and mc.chl in (".$cid.") ";
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
				//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$zh_res = $model->table('sj_admin_filter')->where($zh_map)->find();
				if(!empty($zh_res) && ($zh_res['filter_type'])==1){
					$zh_where .=" ";
				}else{
					$source_type = USER_FILTER_TYPE;
					$target_type = CHANNEL_FILTER_TYPE;
					$cid_array_filter="select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
					$cid_array_filter_result=$model->query($cid_array_filter);
					foreach($cid_array_filter_result as $c=>$l)
					{
						$cid_str.=$l['cid'].",";
					}
					$cid_str_array=substr($cid_str,0,-1);
					$zh_where .= "and mc.chl in (".$cid_str_array.") ";

				}
			}
		}else{
			if($_REQUEST['fromdate'] && $_REQUEST['todate']){
					$start_time=strtotime($_REQUEST['fromdate']." 00:00:00");
					$end_time=strtotime($_REQUEST['todate']." 23:59:59");
					$from_value=$_REQUEST['fromdate'];
					$to_value=$_REQUEST['todate'];
					/* if(($end_time-$start_time)>3600*24*184){
						$this -> error('选择区间超过六个月！！');
					} */
					$zh_where.="date >='".$start_time."' and date<= '".$end_time."' ";
			}else{
				$start_time=$this->start_time();
				$now_data=date("Y-m-d",time()-86400);
				$end_time=strtotime($now_data." 23:59:59");
				$from_value=date("Y-m-d",$start_time);
				$to_value=date("Y-m-d",$end_time);
				$zh_where.="date >='".$start_time."' and date<= '".$end_time."' ";
				}
			if($_REQUEST['cid']){
				$cid=$_REQUEST['cid'];
				$cid_array=explode(",",$_REQUEST['cid']);
				$zh_where .=" and mc.chl in (".$_REQUEST['cid'].") ";
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
				//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$zh_res = $model->table('sj_admin_filter')->where($zh_map)->find();
				if(!empty($zh_res) && ($zh_res['filter_type'])==1){
					$zh_where .=" ";
				}else{
					$source_type = USER_FILTER_TYPE;
					$target_type = CHANNEL_FILTER_TYPE;
					$cid_array_filter="select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
					$cid_array_filter_result=$model->query($cid_array_filter);
					foreach($cid_array_filter_result as $c=>$l)
					{
						$cid_str.=$l['cid'].",";
					}
					$cid_str_array=substr($cid_str,0,-1);
					$zh_where .= " and mc.chl in (".$cid_str_array.") ";
					//$zh_where .= " and channel_id in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}') ";
				}
			}


		}
		$count_sql="select count(*) as num from (select mc.chl as chl from mobile_chl_soft_stat mc join
			sj_channel c on mc.chl = c.cid where ".$zh_where." group by mc.chl) as zhang";
		$search_count=$mObj->query($count_sql);
		$count=$search_count[0]['num'];
		$param = http_build_query($_GET);
		//print_r($param);
		$param="fromdate=".$from_value."&todate=".$to_value."&cid=".$cid."&".$param;
		$Page = new Page($count, 10, $param);
		$Page->callback = 'return get_params();';
		$limit=" limit ".$Page->firstRow . ',' . $Page->listRows;
		$result = $mObj -> query("
			select sum(apps) as appsum, sum(games) as gamesum,sum(activates) as actsum,c.chname as chname,mc.chl as chl from mobile_chl_soft_stat mc join
			sj_channel c on mc.chl = c.cid where ".$zh_where." group by mc.chl
		 ".$limit);
		foreach($result as $idx => $info){
			$result[$idx]['sum'] = $info['appsum'] + $info['gamesum'];
		}
		$zh_cids=array();
		foreach($cid_array as $k=>$val){
			if($val!=0){
				$zh_cids[$k]['cid']=$val;
				$c_where['status']=1;
				$c_where['cid']=$val;
				$zh_chname=$ch_obj->where($c_where)->getfield("chname");
				$zh_cids[$k]['chname']=$zh_chname;
			}
		}
		if ($_GET['p'])
			$this->assign('p', $_GET['p']);
		else
		$this->assign('p', '1');
		$show = $Page->show();
		$this->assign("page", $show);
		/* $result = $mObj -> query("
			select sum(apps) as appsum, sum(games) as gamesum,sum(activates) as actsum,c.chname as chname,mc.chl as chl from mobile_chl_soft_stat mc join
			sj_channel c on mc.chl = c.cid where ".$zh_where." group by mc.chl
		");
		foreach($result as $idx => $info){
			$result[$idx]['sum'] = $info['appsum'] + $info['gamesum'];
		} */
		$this -> assign('chname' ,$chname);
		$this -> assign('result' ,$result);
		$this -> assign('cid_array' ,$zh_cids);
		$this -> assign('zh_cid_str' ,$cid);
		$this -> assign('to_value',$to_value);
		$this -> assign('from_value',$from_value);
		$this -> display('channelDownloadStatic');
		//统计活跃量
	}

	/*
	 hide : 0历史 1正常 2新软件 3下架 4编辑软件 5更新软件 6驳回
	 status : 0 删除 , 1 正常
	*/
	function softStatic(){
	  ini_set("memory_limit","-1");
	  $model = M('soft');
				/*
	  $hide_array = array(0,1,2,3,4,5,6);
	  $categoryObj = M('category');
	  $cates = $categoryObj -> where("status = 1 and parentid <> 0") -> getField('category_id,name');
	  $result = array();
	  $total = array();
	  // XXX: 直接group by category,hide查询一次，为何要查(n_category * n_hide)次？
	  foreach($cates as $key => $cateName){
			$where = array('category_id' => ','.$key.',');
			$count = 0;
			foreach($hide_array as $h){
			$where['hide'] = $h;
			$where['status'] = 1;
			$result[$cateName][$h] = $model -> where($where) -> count();
			$count += $result[$cateName][$h];

			$total[$h] += $result[$cateName][$h];
			}
			$result[$cateName]['count'] = $count;
		}
				 */

			$sql ="SELECT COUNT(a.`softid`) as tp_count,b.name,b.category_id,a.hide FROM sj_soft a INNER JOIN sj_category b ON b.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2) AND b.status=1 AND b.parentid <> 0 WHERE  a.status = 1 and hide in (0,1,3) GROUP BY b.name,a.hide";
			$rs = $model->query($sql);
			$sql_tmp ="SELECT COUNT(a.`softid`) AS tp_count,b.name,b.category_id,a.`status`,a.`record_type` FROM sj_soft_tmp a INNER JOIN sj_category b ON b.category_id = SUBSTR(a.category_id,2,LENGTH(a.category_id) - 2) AND b.status = 1 AND b.parentid <> 0 WHERE a.`status` IN (2,3) AND record_type IN(1,2,3) GROUP BY b.name,a.`status`,a.`record_type`";
			$rs_tmp = $model->query($sql_tmp);
			$res_tmp = array();
			foreach($rs_tmp as $v)
			{
				if($v['status']==3)
				{
					$oldcount = $res_tmp[$v['name']][6]+$v['tp_count'];
					$res_tmp[$v['name']][6] = $oldcount;
				}else
				{
					if($v['record_type']==1){
						$res_tmp[$v['name']][2] = $v['tp_count'];
					}
					if($v['record_type']==2){
						$res_tmp[$v['name']][4] = $v['tp_count'];
					}
					if($v['record_type']==3){
						$res_tmp[$v['name']][5] = $v['tp_count'];
					}
				}
			}

			$res = array();
			foreach($rs as $v)
			{
				$res[$v['name']][$v['hide']] = $v['tp_count'];
			}

			foreach($res_tmp as $key=> $v)
			{
				foreach($v as $vkey=>$vv)
				{
					$res[$key][$vkey] = $vv;
				}
			}
			$total = array();
			foreach($res as $key =>$v)
			{
				for($i=0;$i<=6;$i++)
				{
					$total[$i] = $total[$i] + $v[$i];
					if($v[$i]==NULL){
						$res[$key][$i] =0;
					}
				}
				ksort($res[$key]);
				unset($res[$key][7]);
				unset($res[$key][1024]);
				unset($res[$key][12]);
				$res[$key]['count'] = $v[0]+$v[1]+$v[2]+$v[3]+$v[4]+$v[5]+$v[6];
			}
	 $this->writelog('查看了软件类别明细统计信息', 'sj_soft,sj_soft_tmp','',__ACTION__ ,'','view');
	 $this -> assign('result' , $res);
	 $this -> assign('total' ,$total);
	 $this -> display('softStatic');
	}
	/********************************************************
	手机top100统计
	*********************************************************/
	function sjtop()
	{
		ini_set('memory_limit', '1024M');
		set_time_limit(0);
		$model = D("Sj.Slave");
		$model = D("Sj.DownloadNew");
		$dmodel = D("Sj.DownloadNew");
		$category = M("category");
		$soft = M("soft");
		$time = time()-86400*7;
		$time1 = time();
		$ptime = date("Y-m-d",$time);
		$ptime1 = date("Y-m-d",$time1);
		$chekweb = 1;
		$cheksj = 1;
		$cheksjg = 1;
		$chekhz = 1;
		$chekwap = 1;
		$dir="/tmp/zh_statistical/";
		//$dir='';
		if(!file_exists($dir)){
				mkdir($dir,0755,TRUE);
			}
		//$dir="D:/gongju/APMServ5.2.6/www/htdocs/wwwroot/newadmin.goapk.com/yan/";
		$this->del_dir($dir);
		if($_POST)
		{
			$ptime = $_POST['date0'];
			$ptime1 = $_POST['date1'];
			$count = $_POST['count'];
			$zh_field="";
			$field="";
			if($_POST['web'] == 1)
			{
				$zh_field.=",SUM(web_dl_cnt) as web_dl_cnt";
				$field.= "web_dl_cnt+";
			}else{
				$chekweb = 0;
			}
			if($_POST['sj'] == 1)
			{
				$zh_field.=",SUM(mob_dl_cnt) as mob_dl_cnt";
				$field.= "mob_dl_cnt+";
			}else{
				$cheksj = 0;
			}
			if($_POST['sjg'] == 1)
			{
				$zh_field.=",SUM(mob_up_cnt) as mob_up_cnt";
				$field.= "mob_up_cnt+";
			}else{
				$cheksjg = 0;
			}
			if($_POST['hz'] == 1)
			{
				$zh_field.=",SUM(ptn_dl_cnt) as ptn_dl_cnt";
				$field.= "ptn_dl_cnt+";
			}else{
				$chekhz = 0;
			}
			if($_POST['wap'] == 1)
			{
				$zh_field.=",SUM(wap_dl_cnt) as wap_dl_cnt";
				$field.= "wap_dl_cnt+";
			}else{
				$chekwap = 0;
			}
			$field = substr($field,0,-1);
			if($_POST['categoryid']==1){
				$zh_pack_list=$this->get_pack_list($dir.$ptime.$ptime1.$chekweb.$cheksj.$cheksjg.$chekhz.$chekwap.".txt",$zh_field,$field,$ptime,$ptime1,$model);
				//print_r($zh_pack_list);exit;
				$sql_categoryid=$category->field("category_id")->where("parentid=1")->select();

				foreach($sql_categoryid as $k => $val){
					$categoryid_arry[]=$val['category_id'];
				}
				$categoryid=implode("|",$categoryid_arry);
				$categoryid=str_replace("|",",',',",$categoryid);
				$categoryid="',".$categoryid.",'";
				$where['category_id']=array('in',$categoryid);
				$packarray=$soft->field("package")->where($where)->select();
				foreach($packarray as $m => $n){
						$zh_pack[$n['package']]=$n['package'];
					}
					array_unique($zh_pack);
				$list=array();
				foreach($zh_pack_list as $k=>$v){
					if(isset($zh_pack[$k])){
						$list[] = $v;
					}
					if(count($list)>=$count) break;
				}
			}elseif($_POST['categoryid']==2){
				$zh_pack_list=$this->get_pack_list($dir.$ptime.$ptime1.$chekweb.$cheksj.$cheksjg.$chekhz.$chekwap.".txt",$zh_field,$field,$ptime,$ptime1,$model);

				$sql_categoryid=$category->field("category_id")->where("parentid=2")->select();

				foreach($sql_categoryid as $k => $val){
					$categoryid_arry[]=$val['category_id'];
				}
				$categoryid=implode("|",$categoryid_arry);
				$categoryid=str_replace("|",",',',",$categoryid);
				$categoryid="',".$categoryid.",'";
				$where['category_id']=array('in',$categoryid);
				$packarray=$soft->field("package")->where($where)->select();
				foreach($packarray as $m => $n){
						$zh_pack[$n['package']]=$n['package'];
					}
					array_unique($zh_pack);
				$list=array();
				foreach($zh_pack_list as $k=>$v){
					if(isset($zh_pack[$k])){
						$list[] = $v;
					}
					if(count($list)>=$count) break;
				}
			}elseif($_POST['categoryid']!=1&&$_POST['categoryid']!=2&&$_POST['categoryid']!=0){
				$zh_pack_list=$this->get_pack_list($dir.$ptime.$ptime1.$chekweb.$cheksj.$cheksjg.$chekhz.$chekwap.".txt",$zh_field,$field,$ptime,$ptime1,$model);
				$where['category_id']=",".$_POST['categoryid'].",";
				$packarray=$soft->field("package")->where($where)->select();
				foreach($packarray as $m => $n){
						$zh_pack[$n['package']]=$n['package'];
					}
					array_unique($zh_pack);
					//$count=count($zh_pack);
				$list=array();
				foreach($zh_pack_list as $k=>$v){
					if(isset($zh_pack[$k])){
						$list[] = $v;
					}
					if(count($list)>=$count) break;
				}
			}else{
				/*$sql = "SELECT package".$zh_field.",SUM(".$field.") AS s FROM sj_download_count WHERE submit_day >= UNIX_TIMESTAMP('".$ptime."') AND submit_day <=  UNIX_TIMESTAMP('".$ptime1."') GROUP BY package ORDER BY s DESC limit ".$count;*/
				$zh_pack_list=$this->get_pack_list($dir.$ptime.$ptime1.$chekweb.$cheksj.$cheksjg.$chekhz.$chekwap.".txt",$zh_field,$field,$ptime,$ptime1,$model);
				foreach($zh_pack_list as $k=>$v){
						$list[] = $v;

					if(count($list)>=$count) break;
				}
				//$list = $model->query($sql);
			}
		}else{
			$zh_field = ",SUM(web_dl_cnt) as web_dl_cnt,SUM(mob_dl_cnt) as mob_dl_cnt,SUM(mob_up_cnt) as mob_up_cnt,SUM(ptn_dl_cnt) as ptn_dl_cnt,SUM(wap_dl_cnt) as wap_dl_cnt";
			$field = "web_dl_cnt+mob_dl_cnt+mob_up_cnt+ptn_dl_cnt+wap_dl_cnt";
			$count = 100;
			$sql = "SELECT package".$zh_field.",SUM(".$field.") AS s FROM sj_download_count WHERE submit_day >= UNIX_TIMESTAMP('".$ptime."') AND submit_day <=  UNIX_TIMESTAMP('".$ptime1."') GROUP BY package ORDER BY s DESC limit ".$count;
			$list = $dmodel->query($sql);
		}
		$i = 1;
		foreach($list as $key => $value)
		{
			$list[$key]['num'] = $i++;

			$sql1 = "SELECT softname FROM `sj_soft` WHERE `package` = '".$value['package']."'  ORDER BY `softid`  DESC LIMIT 1 ";
			$list1 = $model -> query($sql1);
				$list[$key]['softname'] = $list1[0]['softname'];
		}

		$_SESSION['zh']['list']=$list;
		$_SESSION['zh']['ptime']=$ptime;
		$_SESSION['zh']['ptime1']=$ptime1;
		//分类功能
		$category_db = M('category');
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
		$this->assign('conflist',$conf_list);
		//分类功能结束
		$this->assign("post_categoryid", $_POST['categoryid']);
		$this -> assign("chekweb",$chekweb);
		$this -> assign("cheksj",$cheksj);
		$this -> assign("cheksjg",$cheksjg);
		$this -> assign("chekwap",$chekwap);
		$this -> assign("chekhz",$chekhz);
		$this -> assign("type",$type);
		$this -> assign("count",$count);
		$this -> assign("ptime",$ptime);
		$this -> assign("ptime1",$ptime1);
		$this -> assign("list",$list);
		$this -> display();
	}
	/*function sjtop_excel()
	{
		Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
		set_time_limit(0);
		$model = M();
		Header ( "Content-type:   application/octet-stream " );
			Header ( "Content-Disposition:   attachment;   filename= list.csv" );
			$field = "web_dl_cnt+mob_dl_cnt+mob_up_cnt+ptn_dl_cnt+wap_dl_cnt";
		if($_POST)
		{
			$ptime = $_POST['date0'];
			$ptime1 = $_POST['date1'];
			$dtime = date("Y-m-d",strtotime($_POST['date0']));
			$dtime1 = date("Y-m-d",strtotime($_POST['date1']));
			if($_POST['sj'] == 1)
			{
				$type = "1";
				$field = "mob_dl_cnt+mob_up_cnt";
			}
			if($_POST['fsj'] == 1)
			{
				$field = "web_dl_cnt+ptn_dl_cnt+wap_dl_cnt";
			}
			if(($_POST['sj'] == 1)&&($_POST['fsj'] == 1))
			{
				$type = "1";
				$field = "web_dl_cnt+mob_dl_cnt+mob_up_cnt+ptn_dl_cnt+wap_dl_cnt";
			}
		}
		$sql = "SELECT package , SUM(mob_up_cnt) as mob_up_cnt , SUM(".$field.") AS s FROM sj_download_count WHERE submit_day >= UNIX_TIMESTAMP('".$dtime."') AND submit_day <=  UNIX_TIMESTAMP('".$dtime1."') GROUP BY package ORDER BY s DESC LIMIT 100";
		$list = $model->query($sql);
		$i = 1;
		foreach($list as $key => $value)
		{
			$list[$key]['num'] = $i++;
			$sql1 = "SELECT softname FROM `sj_soft` WHERE `package` = '".$value['package']."'  ORDER BY `softid`  DESC LIMIT 1 ";
			$list1 = $model -> query($sql1);
				$list[$key]['softname'] = $list1[0]['softname'];
		}
		$str = "日期 , ".$dtime." , 至 , ".$dtime;
		echo iconv("utf-8",'gbk',$str)."\r\n";
		if($type == "1")
		{
			echo iconv("utf-8",'gbk'," 排名 , 应用名称 , 包名 , 下载量, 更新\r\n " );
		}else{
			echo iconv("utf-8",'gbk'," 排名 , 应用名称 , 包名 , 下载量\r\n " );
		}

		foreach($list as $key => $value)
		{
			if($type == "1"){
			echo iconv("utf-8",'gbk',$value['num']." , ".$value['softname']." , ".$value['package']." , ".$value['s'].",".$value['mob_up_cnt']."\r\n");
			}else{
			echo iconv("utf-8",'gbk',$value['num']." , ".$value['softname']." , ".$value['package']." , ".$value['s']."\r\n");
			}
		}

	}*/

	function Download_edit()
	{
		$category_db = M('category');

		if(!empty($_GET['id'])) {
				if(intval($_POST['detain_num'])<0 || intval($_POST['add_num'])<0)
				{
					$this->error('请填写正确的信息');
				}
				$category_db1 = M('soft');
				if($_POST['data_type'] == 2){
					$mark = "扣量";
					$data['total_downloaded_detain'] = intval($_POST['detain_num']);
				}else{
					$mark = "增量";
					$data['total_downloaded_add'] = intval($_POST['add_num']);
				}
				$data1 = $category_db1->where(array("softid" => trim($_GET['id'])))->save($data);
				/* $detain = M('sj_downloaded_detain');
				$package = $detain->where(array("package"=>$_POST['package']))->find();
				if($package != NULL) {
					$detain->where(array("package"=> $_POST['package']))->save(array('detain' => $_POST['num']));
				} else {
					$detain->data(array('package' => $_POST['package'],'detain'=>$_POST['num']))->add();
				} */
				if($data1 > 0) {
					$detain_add_num = ($_POST['data_type'] == 2 ) ? $_POST['detain_num'] : $_POST['add_num'];
					$this->writelog('扣增量包名为：'.$_POST['package'].$mark.'为：'.$detain_add_num,'sj_soft',$_GET['id'],__ACTION__ ,'','edit');
					$this->success("数据{$mark}保存成功！");
				}

		}
		$category = D('Sj.Category');
		$array_config=array(
			"categoryid"=>"categoryid",
			"selected"=>$_GET['categoryid']
		);
		$conf_list = $category->getCategory($array_config);
		$this->assign('conflist',$conf_list);
		$where = "status=1 and hide=1";
		$soft_db=M('soft');
		$soft_file_model = M('soft_file');
		import("@.ORG.Page");
		$this->order='last_refresh desc';
		if(!empty($_GET['softid'])) {
			$this->assign ("softid", $_GET['softid'] );
			$where.=' and softid="'.trim(escape_string($_GET['softid'])).'"';
		}
		if(!empty($_GET['softname'])) {
			$this->assign ("softname", $_GET['softname'] );
			$where.=' and softname like "%'.trim(escape_string($_GET['softname'])).'%"';
		}
		if(!empty($_GET['package'])) {
			$this->assign ("package", $_GET['package'] );
			$where.=' and package like "%'.trim(escape_string($_GET['package'])).'%"';
		}
		if(!empty($_GET['dev_name'])) {
			$this->assign ("dev_name", $_GET['dev_name'] );
			$where.=' and dev_name like "%'.trim(escape_string($_GET['dev_name'])).'%"';
		}
		if(!empty($_GET['softinfo'])) {
			$this->assign ("softinfo", $_GET['softinfo'] );
			$where.=' and intro like "%'.trim(escape_string($_GET['softinfo'])).'%"';
		}
		if(!empty($_GET['dev_id'])) {
			$this->assign ("dev_id", $_GET['dev_id'] );
			$where.=' and dev_id="'.trim(escape_string($_GET['dev_id'])).'"';
		}
		if(isset($_GET['only_search'])) {
			$this->assign ("only_search", $_GET['only_search'] );
			$s = trim($_GET['only_search']) == 'y' ? 1 : 0;
			$where.=' and only_search="'.$s.'"';
		}
		if(!empty($_GET['categoryid'])) {
			$this->assign ("categoryid", $_GET['categoryid'] );
			$where.=' and (SELECT find_in_set  ('.trim(escape_string($_GET['categoryid'])).',`category_id`)>0)';
			//$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
		}
		$this->assign ("operatorhide", '999');
		if($_GET['operatorhide']!='999' && $_GET['operatorhide']!='') {
			$this->assign ("operatorhide", $_GET['operatorhide'] );
			$where.=' and (SELECT find_in_set  ('.trim($_GET['operatorhide']).',`operatorhide`)>0)';
			//$this->map.=' and category_id like "%,'.preg_replace($_POST['categoryid']).',%"';
		}
		$count= $soft_db->where($where)->count();
		$Page=new Page($count,15, $param);
		$soft_list = $soft_db->field("`softid`,`softname`,`package`,`version`,`version_code`,`total_downloaded`,`total_downloaded_detain`,`total_downloaded_add`,`category_id`,from_unixtime(`upload_tm`) as `created_at`,from_unixtime(`last_refresh`) as `updated_at`")->order('last_refresh desc')->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
			$soft_list_mapped = array();

			foreach($soft_list as $key => $value) {
				foreach($category_list_child as $key1 => $value1) {
					if(in_array($value1['category_id'],explode(",",$value['category_id']))) {
						$soft_list[$key]['category_name'] .= ",".$value1['name'];
						break;
					}
				}
				$soft_list[$key]['category_name'] = strtr(substr($soft_list[$key]['category_name'],1),array(',',array(','=>'<br />')));
			}
			foreach ($soft_list as $v)
				$soft_list_mapped[$v['softid']] = $v;
			$softids = array_keys($soft_list_mapped);
			$sids = implode(',', $softids);
			$file_list = $soft_file_model->field('`softid`,`iconurl`')->where("`softid` in ({$sids})")->select();
			foreach ($file_list as $v) {
				if (isset($soft_list_mapped[$v['softid']]))
					$soft_list_mapped[$v['softid']]['iconurl'] = $v['iconurl'];
			}

			$this->assign('softlist',$soft_list_mapped) ;
			$Page->setConfig('header','篇记录') ;
		$Page->setConfig('first','<<') ;
		$Page->setConfig('last','>>') ;
		$show =$Page->show();
		$this->assign ("page", $show ) ;
		$this->display();
	}

	public function channel_verify_export() {
		$c = addslashes($_GET['c']);
		$f = strtotime($_GET['f']);
		$t = strtotime($_GET['t']);
		if (empty($c) || empty($f) || empty($t)) {
				$this->display();
				exit;
		}
		$this->assign('jumpUrl','/index.php/'.GROUP_NAME.'/Statistical/channel_verify_export');
		if ($f > $t) {
			$m = $f ^ $t;
			$f = $m ^ $f;
			$t = $m ^ $t;
		}
		$t += 86400;
		$t = strtotime(date('Y-m-d', $t));
		if ($t - $f > 365 * 86400) {
			$this->error("选择的时间间隔过长。");
		}
		$channel_db = M('channel');
		$test = $channel_db->where(array("chl"=>$c,"status"=>1))->select();
		if (empty($test)) {
			$this->error("找不到所需渠道号。");
		}
		$f = date('Y-m-d', $f);
		$t = date('Y-m-d', $t);
		$tmp = SU_ROOT. '/Runtime/Temp';
		$fn_csv = "${tmp}/${c}_${f}_${t}.csv";
		$fn_lock = "${fn_csv}.lock";
		if (is_file($fn_lock)) {
			$stat = lstat($fn_lock);
			$mtime = $stat['mtime'];
			$now = time();
			if ($now - $mtime < 1800) {
				$this->error("数据仍在生成中，请稍候再试。");
			}
		} else {
			if (!isset($_GET['d']) && is_file($fn_csv)) {
				$data = file_get_contents($fn_csv);
				$this->assign('data', $data);
				$this->display();
				exit;
			}
		}
		if (FALSE === file_put_contents($fn_lock, "")) {
			$this->error("目录没有写权限，请联系管理员。");
		}
		$this->writelog('查看了渠道数据统计信息', 'sj_channel','',__ACTION__ ,'','view');
		$generator = "/usr/local/php-5.2.14/bin/php /data/www/wwwroot/new-wwwroot/cron2/gen_channel_stats.php";
		$cmd = "nohup sh -c '${generator} ${f} ${t} ${c} > ${fn_csv}; rm -f ${fn_lock}' &";
		$ret = shell_exec($cmd);
		$this->success("正在生成数据，请稍候访问。");
	}

	//软件详细下载量统计
	//Edit by 黄文强 2012/09/04
	function soft_up_dl_stat(){
		//var_dump($_SESSION);
		$user_db = M("soft_download_user_config");
		//获取当前用户名
		$usernow = $_SESSION['admin']['admin_user_name'];
		//查询权限用户表
		$userflag = $user_db->where("user='$usernow' AND status='1'")->limit(1)->select();
		if (!$userflag[0]['user']){
			// 不在配置设置列表中
			$this -> display("soft_up_dl_stat");
			return ;
		}

		if ($userflag[0]['isgol']){
			/*if (array_key_exists('package', $where)){
				unset($where['package']);
			}*/
		} else {
			$softlist = '';
			$detail_db = M("soft_download_user_detail");
			$result = $detail_db -> query("SELECT package FROM sj_soft_download_user_detail WHERE user='$usernow' AND status='1'");
			if (empty($result)){
				$this -> display("soft_up_dl_stat");
				return ;
			} else {
				foreach ($result as $v)
					$softlist .= "'" . $v['package'] . "',";
				$softlist .= "''";
				//$where['package'] = array('IN', $softlist);
				$where['_string'] = "package in (".$softlist.")";
			}
		}
		$dlc_db = M("download_count");
		$dmodel = D("Sj.DownloadNew");
		$ss_db  = M("soft");
		//$ssf_db = M("soft_file");
		import("@.ORG.Page");
		$dlcwhere = "";
		if(!$_REQUEST['start'] && !$_REQUEST['end'])
		{
			$_REQUEST['end']   = date('m/d/Y', strtotime('now'));
			$_REQUEST['start'] = date('m/d/Y', strtotime('-7 days'));
		}
		//var_dump($_REQUEST);
		if($_REQUEST['start']){
			if (isset($_REQUEST['sm'])) $_REQUEST['start'] = str_replace('-','/',$_REQUEST['start']);
			$dlcwhere .= " submit_day >= ".strtotime($this ->format_date($_REQUEST['start']));//$dlcwhere['submit_day'] = array("egt",strtotime($this ->format_date($_POST['start'])));
		}
		if($_REQUEST['end']){
			if(isset($_REQUEST['sm'])) $_REQUEST['end'] = str_replace('-','/',$_REQUEST['end']);
			$dlcwhere .= " and submit_day <= ".strtotime($this ->format_date($_REQUEST['end']));
			$end_time = strtotime($this ->format_date($_REQUEST['end']));
			//$dlcwhere['submit_day'] = array("elt",strtotime($this ->format_date($_POST['end'])));
		}

		$request_url = '';
		setcookie('request_url',$_SERVER['REQUEST_URI'] ,time()+3600);

		if ($_REQUEST['softname']) $where['softname'] = array("like","%".trim($_REQUEST['softname'])."%");
		if ($_REQUEST['package'])  $where['package']  = array("eq",trim($_REQUEST['package']));//包名查找是等于 modify by shitingting
		//$where['hide'] = 1;
		$where['status'] = 1;
		if($_POST['search']||$_GET)
		{
			if($_REQUEST['start']&&$_REQUEST['end'])
			{
				$start_tm=strtotime($this ->format_date($_REQUEST['start']));
				$end_tm=strtotime($this ->format_date($_REQUEST['end']));
				if($start_tm>$end_tm)
				{
					$this->error("开始时间不能大于结束时间");
				}
			}
			if($_REQUEST['softname']||$_REQUEST['package'])
			{
				$count = $ss_db -> where($where) -> count('distinct package');
				$p = new page($count,50);
				$softlist = $ss_db -> where($where) ->limit($p -> firstRow.','.$p -> listRows) -> group('package') -> select();
			}
			else
			{
				$this->error("软件名称或者包名必须填写一个");
			}
			$dlc_static_info = array();
			foreach($softlist as $info){
				$where = "package = '".$info['package']."' and ".$dlcwhere;
				$result = $dmodel -> query('select package,sum(web_dl_cnt) web_dl_cnt,sum(mob_dl_cnt) mob_dl_cnt,sum(mob_up_cnt) mob_up_cnt,sum(ptn_dl_cnt) ptn_dl_cnt,sum(wap_dl_cnt) wap_dl_cnt,sum(zy_dl_cnt) zy_dl_cnt, submit_day from  sj_download_count where '.$where);
				$result = $result[0];
				$result['web_dl_cnt'] = $result['web_dl_cnt'] ? $result['web_dl_cnt'] : 0;
				$result['mob_dl_cnt'] = $result['mob_dl_cnt'] ? $result['mob_dl_cnt'] : 0;
				$result['mob_up_cnt'] = $result['mob_up_cnt'] ? $result['mob_up_cnt'] : 0;
				$result['ptn_dl_cnt'] = $result['ptn_dl_cnt'] ? $result['ptn_dl_cnt'] : 0;
				$result['wap_dl_cnt'] = $result['wap_dl_cnt'] ? $result['wap_dl_cnt'] : 0;
				$result['zy_dl_cnt'] = $result['zy_dl_cnt'] ? $result['zy_dl_cnt'] : 0;
				$result['package'] = $result['package'] ? $result['package'] : $info['package'];
				$result['softname'] = $info['softname'];
				$result['submit_day'] = $end_time;

				$r = $dmodel -> query('select package,sum(detain_cnt) detain_cnt,submit_day from  sj_download_detain where '.$where);
				$result['detain_cnt'] = intval($r[0]['detain_cnt']);
				$r = $dmodel -> query('select package,sum(add_cnt) add_cnt,submit_day from  sj_download_add where '.$where);
				$result['add_cnt'] = intval($r[0]['add_cnt']);

				$result['other_cnt'] = 0;
				$cnt_info  = $dmodel -> table('sj_game_download_count') -> where ($where) -> field('sum(game_dl_cnt+game_up_cnt) as c')->find();
				$result['other_cnt'] += intval($cnt_info['c']);

				$cnt_info  = $dmodel -> table('sj_hd_download_count') -> where ($where)-> field('sum(hd_dl_cnt+hd_up_cnt) as c') -> find();
				$result['other_cnt'] += intval($cnt_info['c']);

				$cnt_info  = $dmodel -> table('sj_lgtv_download_count') -> where ($where)-> field('sum(lgtv_dl_cnt+lgtv_up_cnt) as c') -> find();
				$result['other_cnt'] += intval($cnt_info['c']);

                $result['bidl_cnt'] = 0;
                $result['biup_cnt'] = 0;
                $d1 = date('Y-m-d', strtotime($_REQUEST['start']));
                $d2 = date('Y-m-d', strtotime($_REQUEST['end']));
                $bi_data = $this->get_bi_data($info['package'], $d1, $d2);
                if ($bi_data['data']['list']) {
                    foreach ($bi_data['data']['list'] as $val) {
                        $result['bidl_cnt'] += $val['DOWNLOADTIMESPV'];
                        $result['biup_cnt'] += $val['DOWNLOADTIMESUV']; 
                    }
                }                				

				$dlc_static_info[] = $result;
			}
			if($_REQUEST['start']) $p -> parameter = "start=".date("m-d-Y",strtotime($this ->format_date($_REQUEST['start'])));
			if($_REQUEST['end'])$p -> parameter .= "&end=".date("m-d-Y",strtotime($this ->format_date($_REQUEST['end'])));
			$p -> parameter .= "&sm=s";
			if($_REQUEST['softname']) $p -> parameter .= "&softname=".$_REQUEST['softname'];
			if($_REQUEST['package']) $p -> parameter .= "&package=".$_REQUEST['package'];
			$page = $p->show(); //$Page->firstRow.','.$Page->listRows
			$this -> assign('page',$page);
			$this -> assign('dlc_static_info',$dlc_static_info);
			$this -> assign('sign','search');
			$this -> assign('softname',$_REQUEST['softname']);
			$this -> assign('package',$_REQUEST['package']);
			$starttime = $_REQUEST['start'] ? strtotime($this ->format_date($_REQUEST['start'])) : time()-7*24*3600;
			$this -> assign('starttime',date("m/d/Y",$starttime));
			$endtime = $_REQUEST['end'] ? strtotime($this ->format_date($_REQUEST['end'])): time();
			
			$this -> assign('search_show','search');
			
			$this -> assign('endtime',date("m/d/Y",$endtime));
		}
		$this -> display("soft_up_dl_stat");
	}

	//软件下载量查看管理 Create by 黄文强 2012/09/04
	function soft_up_dl_admin()
	{
		$user_db = M("soft_download_user_config");
		$detail_db = M("soft_download_user_detail");
		$usersoftc = array();
		$usersoft = array();
		$result = $detail_db -> query("SELECT user, count(*) as softnum FROM sj_soft_download_user_detail WHERE status='1' GROUP BY user");
		foreach ($result as $v)
			$usersoftc[$v['user']] = $v['softnum'];
		$userlist = $user_db -> query('SELECT user,isgol FROM sj_soft_download_user_config WHERE status="1"');
		foreach ($userlist as $v)
			if (isset($usersoftc[$v['user']]))
				$usersoft[] = array(
					'user' => $v['user'],
					'softnum' => ($v['isgol'] == 1) ? '全部' : $usersoftc[$v['user']],
					'isgol' => $v['isgol']
				);
			else
				$usersoft[] = array(
					'user' => $v['user'],
					'softnum' => ($v['isgol'] == 1) ? '全部' : 0,
					'isgol' => $v['isgol']
				);
		$this -> assign('userlist', $usersoft);
		$this -> display("soft_up_dl_admin");
	}

	//软件下载量查看管理——编辑人员软件列表 Create by 黄文强 2012/09/05
	function soft_up_dl_edit()
	{
		$user_config = M('soft_download_user_config');
		$detail_db = M("soft_download_user_detail");
		$soft_db = M("soft");
		$user = $_REQUEST['user'];
		$res = $user_config->where("user='$user' AND status='1'")->field('isgol')->limit(1)->select();
		$list = array();
		if ($res[0]['isgol']){
			import("@.ORG.Page");
			$count      = $soft_db->where("hide='1' AND status='1' AND channel_id=''")->count();
			$Page       = new Page($count,50);
			$show       = $Page->show();
			$list = $soft_db->where("hide='1' AND status='1' AND channel_id=''")->field('softname, package')->order('softid DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('page',$show);
		} else {
			$softall = $detail_db -> query("SELECT package FROM sj_soft_download_user_detail WHERE user='$user' AND status='1'");
			foreach ($softall as $v)
			{
				$result = $soft_db -> query("SELECT softname FROM sj_soft WHERE package='" . $v['package'] . "' AND hide=1 AND status=1 AND channel_id='' ORDER BY softid DESC LIMIT 1");
				$list[] = array(
					'softname' => $result[0]['softname'],
					'package'  => $v['package']
				);
			}
		}
		$this -> assign('softlist', $list);
		$this -> assign('user', $user);
		$this -> assign('isgol', $res[0]['isgol']);
		$this -> display("soft_up_dl_edit");
	}

	//软件下载量查看管理——添加用户 Create by 黄文强 2012/09/06
	function soft_up_dl_add_user()
	{
		$user = $_REQUEST['user'];
		$user_db = M("soft_download_user_config");
		$detail_db = M("soft_download_user_detail");
		$admin_db = M("admin_users");
		//var_dump($user);

		$result = $user_db -> query("SELECT * FROM sj_soft_download_user_config WHERE user='$user' AND status='1'");
		if (!empty($result))
		{
			$ret['flag'] = false;
			$ret['info'] = 'dumplicate';
		}
		else
		{
			$result = $admin_db -> query("SELECT * FROM sj_admin_users WHERE admin_user_name='$user' AND admin_state=1");
			//var_dump($result);
			if (empty($result))
			{
				$ret['flag'] = false;
				$ret['info'] = 'unknown';
			}
			else
			{
				$time = time();
				$data = array(
					'user'      => $user,
					'status'    => '1',
					'submit_tm' => $time
				);
				$result = $user_db -> data($data) -> add();
				//var_dump($result);
				if ($result == false)
				{
					//echo mysql_error();
					$ret['flag'] = false;
					$ret['info'] = 'fault';
				}
				else
				{
					$ret['flag'] = true;
					$ret['info'] = 'success';
				}
			}
		}
		$ret = json_encode($ret);
		echo $ret;
	}

	//软件下载量查看管理———删除用户 Create by 黄文强 2012/09/06
	function soft_up_dl_del_user()
	{
		$user = $_REQUEST['user'];
		$user_db = M("soft_download_user_config");
		$detail_db = M("soft_download_user_detail");
		$result = $detail_db -> where("user='$user'") -> save(array('status' => '0'));
		if ($result == false && $result != 0)
		{
			$ret['flag'] = false;
		}
		else
		{
			$result = $user_db -> where("user='$user'") -> save(array('status' => '0'));
			if ($result == false && $result != 0)
			{
				$ret['flag'] = false;
			}
			else
			{
				$ret['flag'] = true;
			}
		}
		echo json_encode($ret);
	}

	//软件下载量查看管理——添加软件 Create by 黄文强 2012/09/05
	function soft_up_dl_add_soft()
	{
		$user_db = M("soft_download_user_config");
		$detail_db = M("soft_download_user_detail");
		$user = $_REQUEST['user'];
		$package = $_REQUEST['package'];
		$result = $detail_db -> query("SELECT * FROM sj_soft_download_user_detail WHERE user='$user' AND package='$package' AND status='1'");

		if ($_GET['flag'] == 'changeisgol'){
			$g = $_GET['lag'];
			$data['isgol'] = $g;
			if ($user_db->where("user='$user'")->save($data)){
				$ret['flag'] = true;
				echo json_encode($ret);
			}
			exit();
		}

		if (empty($result))
		{
			$data = array(
				'user'      => $user,
				'package'   => $package,
				'status'    => '1',
				'submit_tm' => time()
			);
			$result = $detail_db -> data($data) -> add();
			if ($result == false || $result == 0)
			{
				$ret['flag'] = false;
				$ret['info'] = 'fault';
			}
			else
			{
				$ret['flag'] = true;
			}
		}
		else
		{
			$ret['flag'] = false;
			if ($package){
				$ret['info'] = 'dumplicate';
			} else {
				$ret['info'] = 'nodumplicate';
			}
		}

		echo json_encode($ret);
	}

	//软件下载量查看管理——删除软件 Create by 黄文强 2012/09/06
	function soft_up_dl_del_soft()
	{
		$user_db = M("soft_download_user_config");
		$detail_db = M("soft_download_user_detail");
		$user = $_REQUEST['user'];
		$package = $_REQUEST['package'];
		//echo $user,",",$package;
		$result = $detail_db -> where("user='$user' AND package='$package'") -> save(array('status' => '0'));
		if ($result == false || $result == 0)
		{
			$ret['flag'] = false;
		}
		else
		{
			$ret['flag'] = true;
		}
		echo json_encode($ret);
	}

	//软件下载量查看管理——搜索软件 Create by 黄文强 2012/09/06
	function soft_up_dl_search_soft()
	{
		$soft_db = M('soft');
		$package = $_REQUEST['package'];
		$result = $soft_db -> query("SELECT softname FROM sj_soft WHERE package='$package' AND hide=1 AND status=1 AND channel_id='' ORDER BY softid DESC LIMIT 1");
		if (empty($result))
		{
			$ret['flag'] = false;
		}
		else
		{
			$ret['data'] = $result[0]['softname'];
			$ret['flag'] = true;
		}
		echo json_encode($ret);
	}

	function pub_stat_download()
	{
		return $this->soft_up_dl_stat_pkg();
	}

	function soft_up_dl_stat_pkg(){
		$package = $_GET['package'];
		$where = 'package =\''.$package.'\' and submit_day >='. $_GET['start'].' and submit_day <='. $_GET['end'];
		import("@.ORG.Page");
		$model = D("Sj.DownloadNew");
		$soft_model = new Model();
		$count = $model -> table('sj_download_count') -> where ($where) -> count();
		$p = new page($count,50);
		$pkg_cnt_list  = $model -> table('sj_download_count') -> where ($where) -> limit($p->firstRow.','.$p->listRows) -> select();
		$softname = $soft_model -> table('sj_soft') -> where('package = \''.$package.'\' and hide= 1 and status = 1') -> getField('softname');
		foreach($pkg_cnt_list as $info){
			$where = array(
				'package' => $package,
				'submit_day' => $info['submit_day'],
			);
			$detain_info  = $model -> table('sj_download_detain') -> where ($where) -> find();
			$info['softname'] =$softname;
			$info['detain_cnt'] = intval($detain_info['detain_cnt']);

			$where = array(
				'package' => $package,
				'submit_day' => $info['submit_day'],
			);
			$detain_info  = $model -> table('sj_download_add') -> where ($where) -> find();
			$info['softname'] =$softname;
			$info['add_cnt'] = intval($detain_info['add_cnt']);

			$where = array(
				'package' => $package,
				'submit_day' => $info['submit_day'],
			);
			$info['other_cnt'] = 0;

			$cnt_info  = $model -> table('sj_game_download_count') -> where ($where) -> field('sum(game_dl_cnt+game_up_cnt) as c')->find();
			$info['other_cnt'] += intval($cnt_info['c']);

			$cnt_info  = $model -> table('sj_hd_download_count') -> where ($where)-> field('sum(hd_dl_cnt+hd_up_cnt) as c') -> find();
			$info['other_cnt'] += intval($cnt_info['c']);

			$cnt_info  = $model -> table('sj_lgtv_download_count') -> where ($where)-> field('sum(lgtv_dl_cnt+lgtv_up_cnt) as c') -> find();
			$info['other_cnt'] += intval($cnt_info['c']);

            $info['bidl_cnt'] = 0;
            $info['biup_cnt'] = 0;
            $d = date('Y-m-d', $info['submit_day']);
            $bi_data = $this->get_bi_data($package, $d, $d);
            if ($bi_data['data']['list'][0]) {
                $info['bidl_cnt'] = $bi_data['data']['list'][0]['DOWNLOADTIMESPV'];
                $info['biup_cnt'] = $bi_data['data']['list'][0]['DOWNLOADTIMESUV'];
            }			

			$dlc_static_info[] = $info;
		}
		if ($_GET['return']) {
			$total = 0;
			foreach ($dlc_static_info as $key => $value) {
				$total += $value['web_dl_cnt'];
				$total += $value['mob_dl_cnt'];
				$total += $value['mob_up_cnt'];
				$total += $value['ptn_dl_cnt'];
				$total += $value['wap_dl_cnt'];
				$total += $value['detain_cnt'];
				$total += $value['add_cnt'];
				$total += $value['other_cnt'];
				$total += $value['zy_dl_cnt'];
			}
			echo $avg_num = "30天日均下载量：" . number_format(ceil($total/30));exit;
		}
		$this -> assign('search_show','search');
		$this -> assign('dlc_static_info',$dlc_static_info);
		$page = $p->show();
		$this -> assign('softname',$softname);
		$this -> assign('package',$package);
		$this -> assign('page',$page);
		$this -> assign('starttime',date("m/d/Y",$_GET['start']));
		$this -> assign('endtime',date("m/d/Y",$_GET['end']));
		if(isset($_COOKIE['request_url'])){
		  $this -> assign('refere_url',$_COOKIE['request_url']);
		}
		$this -> display('soft_up_dl_stat');
	}

	// 批量导入访问的页面节点
	function soft_up_dl_admin_import_soft() {
		$user = $_GET['user'];
		if (empty($user) && !$_GET['down_moban']) {
			$this->error('错误的请求');
		}
		$this->assign('user', $user);
		if ($_GET['down_moban']) {
			$this->down_moban();
		} else if ($_FILES) {
			$err = $_FILES["upload_file"]["error"];
			if ($err) {
				$this->ajaxReturn($err,"上传文件错误，错误码为{$err}！", -1);
			}
			$file_name = $_FILES['upload_file']['name'];
			$tmp_arr = explode(".", $file_name);
			$name_suffix = array_pop($tmp_arr);
			if (strtoupper($name_suffix) != "CSV") {
				$this->ajaxReturn("",'请上传CSV格式文件！', -2);
			}
			$tmp_name = $_FILES['upload_file']['tmp_name'];
			$content_arr = $this->import_file_to_array($tmp_name);
			if ($content_arr == -1) {
				$this->ajaxReturn("",'文件打开错误，请检查文件是否损坏！', -3);
			} else if (empty($content_arr)) {
				$this->ajaxReturn("",'文件数据内容不能为空！', -4);
			}
			// 返回检查结果的错误信息，如果记录的flag为1表示有错误
			$error_msg = $this->import_array_convert_and_check($content_arr, $user);
			$flag = true;
			foreach($error_msg as $key=>$value) {
				if ($value['flag'] == 1)
					$flag = false;
			}
			if (!$flag) {
				$this->ajaxReturn($error_msg,'您上传的CSV有如下问题，请修改后重新上传！', -5);
			}
			// 判断后台有没有人正在导入
			$lock_name = 'sj_soft_download_user_detail_importing';
			$import_lock = S($lock_name);
			if ($import_lock) {
				$this->ajaxReturn("",'后台有人正在导入，请稍后再尝试！', 1);
			}
			// 上锁，设置60秒内有效
			S($lock_name, 1, 60, 'File');
			// 返回导入结果，如果记录的flag为0表示添加失败
			$result_arr = $this->process_import_array($content_arr, $user);
			// 导入后解锁
			S($lock_name, NULL);
			$flag = true;
			foreach($result_arr as $key=>$value) {
				if ($value['flag'] == 0)
					$flag = false;
			}
			// save the import file for backups
			$save_dir = IMPORT_FILE_UPLOAD_PATH;
			$this->mkDirs($save_dir);
			$save_name = MODULE_NAME. '_' . ACTION_NAME  . '_' . time() . '_' . $_SESSION['admin']['admin_id'] . '.csv';
			$save_file_name = $save_dir . $save_name;
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $save_file_name);
			// 返回数据给页面
			if ($flag) {
				$this->ajaxReturn("",'导入成功！', 0);
			} else {
				$this->ajaxReturn($result_arr,'存在部分导入失败记录！', -6);
			}
		} else {
			$this->display();
		}
	}

	// 第一行标题列忽略，只保存之后的内容
	function import_file_to_array($file) {
		// $file = "/media/sf_D_DRIVE/shouye-gbk.csv";
		$handle = fopen($file, "r");
		if ($handle === false) {
			return -1;
		}
		$i = $j = 0;
		$content_arr = array();
		while (($line_arr = $this->mygetcsv($handle, 1000, ",")) != FALSE) {
			if ($i == 0) {
				// 读入标题列
				$title_arr = $line_arr;
			} else {
				// 读入每行内容
				$content_arr[$j] = $line_arr;
				$j++;
			}
			$i++;
		}
		fclose($handle);
		// 自动检测并转化编码
		foreach($content_arr as $key => $record) {
			foreach($record as $r_key => $r_value) {
				$content_arr[$key][$r_key] = $this->convert_encoding($r_value);
			}
		}
		return $content_arr;
	}

	function import_array_convert_and_check(&$content_arr, $user) {
		// 文件转换数据前的检查（是否可以转化成与页面数据格式一致）
		$error_msg1 = $this->handwriting_convert_and_check($content_arr);
		// 文件转换数据后的检查（区间是否有效、排期是否冲突等）
		$error_msg2 = $this->logic_check($content_arr, $user);
		// 将$error_msg2合并到$error_msg1里并返回$error_msg1
		foreach($error_msg2 as $key=>$value) {
			if (!array_key_exists($key, $error_msg1)) {
				$this->init_error_msg($error_msg1, $key);
			}
			$this->append_error_msg($error_msg1, $key, $value['flag'], $value['msg']);
		}
		return $error_msg1;
	}

	// 下载批量导入模版
	function down_moban() {
		$file_dir = C("ADLIST_PATH") . "xiazailiangchakan_import_moban.csv";
		if (file_exists($file_dir)) {
			$file = fopen($file_dir,"r");
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file_dir));
			Header("Content-Disposition: attachment; filename=" . urlencode('下载量查看管理批量导入模版') . ".csv");
			echo fread($file, filesize($file_dir));
			fclose($file);
			exit(0);
		} else {
			header("Content-type: text/html; charset=utf-8");
			echo "File not found!";
			exit;
		}
	}

	// 业务逻辑：将批量导入文件里所有数据添加进数据库，返回结果为每一行添加是否成功标志符
	function process_import_array($content_arr, $user) {
		$result_arr = array();
		$model = M('soft_download_user_detail');
		foreach($content_arr as $key => $record) {
			$map = array();
			// 设置默认值
			$map['status'] = '1';
			$map['submit_tm'] = time();
			$map['package'] = $record['package'];
			$map['user'] = $user;
			// 添加到表中
			if ($id = $model->add($map)) {
				$this->writelog("在下载量查看管理中用户{$user}下添加了软件[{$record['package']}]", 'sj_soft_download_user_detail',$id,__ACTION__ ,'','add');
				//$this->assign('jumpUrl', '/index.php/Sj/ExtentV1/list_soft/extent_id/'.$record['extent_id']);
				//$this->success('添加成功');
				$result_arr[$key]['flag'] = 1;
				$result_arr[$key]['msg'] = "添加成功";
			} else {
				//$this->error('添加失败');
				// 未知原因添加失败
				$result_arr[$key]['flag'] = 0;
				$result_arr[$key]['msg'] = "添加失败";
			}
		}
		return $result_arr;
	}

	// 只检查导入文件的手工填写内容，并将其数据格式转成与网页版的添加一致
	// 1，将每一行数组的key由数字转成对应数据库的列名，如0为extend_id，1为extent_name...
	// 2，将某些列的字符串转成数字，如是、否转化成1，0...
	function handwriting_convert_and_check(&$content_arr) {
		// 初始化错误数组
		$error_msg = array();
		foreach($content_arr as $key => $value) {
			$this->init_error_msg($error_msg, $key);
		}
		// 业务逻辑：将表里字段名称和模版里列的名称一一对应
		$correct_title_arr = array(
			'package'  =>   '包名',
		);
		// trim一下所有的数据
		foreach($content_arr as $key=>$record) {
			foreach($record as $r_key=>$r_record) {
				$content_arr[$key][$r_key] = trim($r_record);
			}
		}
		// 给$content_arr里的每一行记录的每一列下标由数字改成对应名称
		$new_content_arr = array();
		$new_key = array();
		// 将$correct_title_arr里的key值提取出来依次放在$new_key里
		foreach($correct_title_arr as $key => $value) {
			$new_key[] = $key;
		}
		foreach($content_arr as $key=>$record) {
			foreach($new_key as $new_key_key=>$new_key_value) {
				if (isset($record[$new_key_key])) {
					$new_content_arr[$key][$new_key_value] = $record[$new_key_key];
				}
			}
		}
		$content_arr = $new_content_arr;
		// 业务逻辑：检查列填写是否为预期文字，如果是则换成对应数据，如果不是则添加错误信息
		return $error_msg;
	}

	// 页面添加或编辑、批量导入共用的逻辑检查
	function logic_check($content_arr, $user) {
		// 初始化错误数组
		$error_msg = array();
		foreach($content_arr as $key => $value) {
			$this->init_error_msg($error_msg, $key);
		}
		$soft_model = M("soft");//软件大表
		$soft_download_user_detail_model = M("soft_download_user_detail");
		// 业务逻辑：以下为各项具体检查
		foreach($content_arr as $key=>$record) {
			// 检查包名是否在sj_soft表里
			if (isset($record['package']) && $record['package'] != "") {
				$where = array(
					'package' => $record['package'],
					'status' => '1',
				);
				$find = $soft_model->where($where)->find();
				if (!$find) {
					$this->append_error_msg($error_msg, $key, 1, "包名【{$record['package']}】不存在于市场软件库中;");
				}
			} else {
				$this->append_error_msg($error_msg, $key, 1, "包名不能为空;");
			}
		}

		// 业务逻辑：检查行与行之间的数据是否冲突
		foreach($content_arr as $key1=>$record1) {
			foreach($content_arr as $key2=>$record2) {
				// 比较过的不比较
				if ($key1 >= $key2)
					continue;
				// 如果包名不同，则不比较
				if ($record1['package'] == $record2['package']) {
					$k1 = $key1 + 1; $k2 = $key2 + 1;
					$this->append_error_msg($error_msg, $key1, 1, "包名与第{$k2}行有重叠;");
					$this->append_error_msg($error_msg, $key2, 1, "包名与第{$k1}行有重叠;");
				}
			}
		}

		// 业务逻辑：检查每一行数据是否与数据库的存储内容相冲突
		foreach($content_arr as $key=>$record) {
			$es_package = escape_string($record['package']);
			$sql = "SELECT * FROM sj_soft_download_user_detail WHERE `user`='{$user}' AND `package`='{$es_package}' AND status='1'";
			// 执行sql
			$db_records = $soft_model->query($sql);
			// 有冲突的后台记录
			foreach($db_records as $db_key=>$db_record) {
				$this->append_error_msg($error_msg, $key, 1, "{$db_record['package']}已存在");
			}
		}
		return $error_msg;
	}

	// 初始单条错误信息，初始化信息：flag为0，msg为空
	function init_error_msg(&$error_msg, $key) {
		if (!isset($error_msg))
			$error_msg = array();
		$error_msg[$key] = array('flag' => 0,'msg' => '');
	}

	// 添加错误信息
	function append_error_msg(&$error_msg, $key, $flag, $msg) {
		if (!isset($error_msg[$key])) {
			$this->init_error_msg($error_msg, $key);
		}
		$error_msg[$key]['flag'] |= $flag;
		$error_msg[$key]['msg'] .= $msg;
	}

	function format_date($strtime){
		$date = explode("/",$strtime);
		$time = $date[2].'-'.$date[0].'-'.$date[1];
		return $time;
	}

	function channel2mobilestatic(){
		$cid = $_GET['cid'];
		if($_GET['date']){
		$date_arr = explode('+',$_GET['date']);
		$from_value = $date_arr[0];
		$to_value = substr($date_arr[1],0,strpos($date_arr[1],'&'));
		$this -> assign('from_value',$from_value);
		$this -> assign('to_value',$to_value);
		$datewhere = " `date` between ".strtotime($from_value." 00:00:00")." and ".strtotime($to_value." 23:59:59")." ";
		}else{
		  $this -> error("操作有误！！");
		}
		$where = "`chl` = ".$cid." and ";
		$Model = new Model();
		$sql = "select * from mobile_chl_soft_stat where ".$where.$datewhere." order by date desc";
		$info = $Model -> query($sql);
		$channel_name = $Model -> Table('sj_channel') -> where(array("cid" => $cid)) -> getField("chname");
		foreach($info as $idx => $data){
			$dname = $Model -> Table('pu_device') -> where("did = ".$data['device'])-> getField('dname');
			$info[$idx]['dname'] = $dname;
		}
		$this -> assign('data_static',$info);
		$this -> assign('chname',$channel_name);
		$this -> display('channel2mobilestatic');
	}
	function verify_week(){
		import("@.ORG.Page");
		$connect1 = array(
			'dbms' => 'mysql',
			'username' => 'gostats',
			'password' => 'gostats',
			'hostname' => '118.26.203.30',  //'118.26.224.30',//192.168.1.30
			'hostport' => '3306',
			'database' => 'gostats',
			'DB_PREFIX' => 'sj_'
		);
		$key = M("channel_verify_imei","AdvModel");
		$key->addConnect($connect1,2);
		$key->switchConnect(2);
		if($_GET['date']){
			$day = $_GET['date'];
		}else{
			$day = date("Y-m-d",(time()-86400));
		}
		$sql = "SELECT chl FROM `sj_channel_verify_config` group by `chl`";
		$count = $key->query($sql);
		$i = count($count);
		$p = new page($i,50);
		$where = "";
		if($_GET['cid']){
			$zh_cid=escape_string($_GET['cid']);
			$sql = "select chl from `sj_channel_verify_config` where `cid` = '".$zh_cid."'";
			$chl = $key->query($sql);
			$where  = " a.chl = '".$chl[0]['chl']."'";
		}else{
			$where = "1";
		}
		$sql = "select a.cid as cid , b.verify_time as verify_time, count(b.id) as k , a.chlname as chlname , sum(b.login) as login , sum(b.install_status) as status ,sum(b.install) as install , a.chl as chl from `sj_channel_verify_config` as a  left join `sj_chl_verify_all_".$day."` as b ON a.chl = b.chl where ".$where."  group by  a.`chl` limit {$p->firstRow} , {$p->listRows}";
		$ret = $key->query($sql);
		$page = $p->show();
		foreach( $ret as $k => $value ){
			$ret[$k]['date'] = date('Y-m-d',$value['verify_time']);
			$sql = "select count(*) as num from `sj_channel_verify_imei_all` where `channel` = '".$value['chl']."'";
			$chlnum = $key->query($sql);
			$ret[$k]['num'] = $chlnum[0]['num'];
		}
		$sql = "SELECT cid , chlname from `sj_channel_verify_config` where `status` = 1";
		$chl = $key->query($sql);
		$this -> assign("chl",$chl);
		$this -> assign("day",$day);
		$this -> assign("ret",$ret);
		$this -> assign('page',$page);
		$this->display();
	}
	function add_channel(){
		if(!empty($_GET['soso'])){
			$chldb = M("channel");
			if(mb_strlen(trim($_GET['soso']),"utf-8") >= 2){
			$soso=escape_string(trim($_GET['soso']));
				$ret = $chldb->where("status = 1 and chname like '%".$soso."%'")->findAll();
				if(empty($ret)){
					$this->error("没有找到相关名称的渠道");
				}else{
					$this->assign("chlret",$ret);
				}
			}else{
				$this->error("请输入两个包括两个以上的汉字或英文");
			}
		}
		import("@.ORG.Page");
		$connect1 = array(
			'dbms' => 'mysql',
			'username' => 'gostats',
			'password' => 'gostats',
			'hostname' => '118.26.224.30',  //'118.26.224.30',//192.168.1.30
			'hostport' => '3306',
			'database' => 'gostats',
			'DB_PREFIX' => 'sj_'
		);
		$key = M("channel_verify_config","AdvModel");
		$key->addConnect($connect1,2);
		$key->switchConnect(2);
		if($_GET['del']){
			$chldb = M("channel");
			$zh_del=escape_string($_GET['del']);
			$sql = "UPDATE `sj_channel_verify_config` SET `status` = '0' WHERE `id` =".$zh_del."LIMIT 1";
			$key->query($sql);
		}
		if($_POST['cid']){
			$chldb = M("channel");
			$ret = $chldb->where( array("cid" => $_POST['cid']))->find();
			$sql = "INSERT INTO `sj_channel_verify_config` (`chl` ,`chlname` ,`status` ,`created_at` ,`updated_at`,`cid`,`type`) VALUES ('".$ret['chl']."', '".$ret['chname']."', '1', '".time()."', '0','".$ret['cid']."','0');";
			$key->query($sql);
			//shell_exec("nohup /usr/local/php-5.2.14/bin/php /data/www/wwwroot/new-wwwroot/cron2/channel_imei_insert.php ".$_POST['cid']);
			//$this->curl("http://192.168.1.30/get_daily_channel_verify_status.php?cid=".$_POST['cid']);
		}
		$sql = "SELECT count(*) as k FROM `sj_channel_verify_config` where `status` = 1";
		$count = $key->query($sql);
		$p = new page($count[0]['k'],50);
		$sql = "SELECT * FROM `sj_channel_verify_config` where `status` = 1 limit {$p->firstRow} , {$p->listRows}";
		$ret = $key->query($sql);
		$sql = "SELECT count(*) as num from `sj_channel_verify_config` where `status` = 1 and `type` = 0";
		$num = $key->query($sql);
		if(count($num[0])>0){
			$type = '1';
		}else{
			$type = '0';
		}
		$this->assign('type',$type);
		$this->assign('ret',$ret);
		$page = $p->show();
		$this->assign('page',$page);
		$this->display();
	}
	function list_all(){
		date_default_timezone_set("Etc/GMT-8");
		import("@.ORG.Page");
		if($_GET['cid']&&$_GET['data']){
			$connect1 = array(
				'dbms' => 'mysql',
				'username' => 'gostats',
				'password' => 'gostats',
				'hostname' => '118.26.224.30',  //'118.26.224.30',//192.168.1.30
				'hostport' => '3306',
				'database' => 'gostats',
				'DB_PREFIX' => 'sj_'
			);
			$key = M("channel_verify_config","AdvModel");
			$key->addConnect($connect1,2);
			$key->switchConnect(2);
			if($_GET['verify']){
				$where = " `verify_time` = '".escape_string($_GET['verify'])."' ";
			}else{
				$where = '1';
			}
			$sql = "SELECT * FROM `sj_channel_verify_config` WHERE `cid` = '".escape_string($_GET['cid'])."' LIMIT 1";
			$chl = $key -> query($sql);
			$sql = "select verify_time from `sj_chl_verify_all_".$_GET['data']."` WHERE ".$where." and `chl` = '".$chl['0']['chl']."'  group by `verify_time`";
			$ret1 = $key -> query($sql);
			$p = new page(count($ret1),20);
			foreach($ret1 as $k => $v){
				$ret1[$k]['day'] =  date("Y-m-d",$v['verify_time']);
			}
			$sql = "select count(id) as k ,verify_time, sum(login) as login , sum(install_status) as status ,sum(install) as install from `sj_chl_verify_all_".$_GET['data']."` where  ".$where." and `chl` = '".$chl['0']['chl']."' group by `verify_time` ORDER BY `verify_time` DESC limit {$p->firstRow} , {$p->listRows}";

			$ret = $key -> query($sql);
			foreach($ret as $k => $v){
				$ret[$k][time] = date("Y-m-d",$v['verify_time']);
			}
			$page = $p->show();
			$this->assign('data',$_GET['data']);
			$this->assign("page",$page);
			$this->assign('ret1',$ret1);
			$this->assign('chl',$chl);
			$this->assign('ret',$ret);
			$this->display();
		}
	}
	/*
	 * curl方法
	 * 访问地址  $url 	//不能为空
	 * 数据            $data    //如果为空 ，则为get方法，如果不为空，则为post方法
	 * 超时时间  $timeout //默认为5秒
	 */
	public function curl($url, $data=array(), $timeout=60)
	{
		$ch = curl_init();
		if (is_array($data) && $data)
		{
			$formdata = http_build_query($data);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	//渠道软件统计管理_下载量统计显示页 by 张辉
	function channel_download(){
		ini_set("memory_limit","-1");

		//$Model = new Model();
		$Model = D("Sj.Search");
		$soft_file_db = M('soft_file');
		$package=escape_string($_GET['package']);
		$start_time=escape_string($_GET['start_time']);
		$end_time=escape_string($_GET['end_time']);
		$fromdate=escape_string(strtotime($_GET['fromdate']));
		$todate=escape_string(strtotime($_GET['todate']));
		$channel_id=escape_string($_GET['channel_id']);
		//echo aaaaa.$channel_id;exit;
		if(empty($channel_id)||empty($package)||($fromdate < strtotime($start_time))||($todate > strtotime($end_time))){
			$this->error("所查时间区间不在指定软件渠道展示范围内！");
		}
		$time = $this -> gettimeArr("channel_download");
		//print_r($time);exit;
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		$time=implode(",", $time);
		$time=str_replace("-","",$time);
		$where="where package='".$package."' and timestamp between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."'";
		$zh_table=$this->back_bsql($time,$where);
		$inf=$Model->query("select sum(num) as total from (".$zh_table.") as zhang");
		//软件下载总量
		$sum_total = $inf[0]['total'];
		$cid_str = substr($channel_id,1,-1);
		$cname = $soft_file_db->table('sj_channel')->where("cid in ({$cid_str})")->findAll();
		$info_channel=array();
		foreach($cname as $k => $v){
				$where="where package='".$package."' and channel='".$v['chl']."' and timestamp between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."'";
				$zh_table=$this->back_bsql($time,$where);
				$channel_count=$Model->query("select sum(num) as channel_total from (".$zh_table.") as zhang");
			$info_channel[$k]['name']=$v['chname'];
			$info_channel[$k]['cid']=$v['cid'];
			$info_channel[$k]['channel_total']=$channel_count[0]['channel_total'];
			}
			//echo $start_time."</br>".$end_time;
		$this->assign('info_channel',$info_channel);
		$this->assign('channel_id',$channel_id);
		$this->assign('package',$package);
		$this->assign('start_time',$start_time);
		$this->assign('end_time',$end_time);
		$this->assign('from_value',$from_value);
		$this->assign('to_value',$to_value);
		$this->display('channel_down_list');
		//$this->display('download');
	}
	function channel_download_seven(){
		ini_set("memory_limit","-1");

		//$Model = new Model();
		$Model = D("Sj.Search");
		$soft_file_db = M('soft_file');
		$channel_id=escape_string($_GET['channel_id']);
		//echo $channel_id;
		$package=escape_string($_GET['package']);
		$start_time=escape_string($_GET['fromdate']);
		$end_time=escape_string($_GET['todate']);
		//echo $end_time;exit;
		if(empty($channel_id)||empty($package)||empty($start_time)||empty($end_time)){
			$this->error("所查渠道信息有误！");
		}
		$time = $this -> zh_gettimeArr($end_time);
		//print_r($time);exit;
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		$time=implode(",", $time);
		$time=str_replace("-","",$time);
		$where="where package='".$package."' and timestamp between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."'";
		$zh_table=$this->back_bsql($time,$where);
		//print_r($zh_table);exit;
		$inf=$Model->query("select sum(num) as total from (".$zh_table.") as zhang");
		//软件下载总量
		$sum_total = $inf[0]['total'];

		$cid_str = substr($channel_id,1,-1);
		$cname = $soft_file_db->table('sj_channel')->where("cid in ({$cid_str})")->findAll();
		$info_channel=array();
		foreach($cname as $k => $v){
				$where="where package='".$package."' and channel='".$v['chl']."' and timestamp between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."'";
				$zh_table=$this->back_bsql($time,$where);
				$channel_count=$Model->query("select sum(num) as channel_total from (".$zh_table.") as zhang");
			$info_channel[$k]['name']=$v['chname'];
			$info_channel[$k]['cid']=$v['cid'];
			$info_channel[$k]['channel_total']=$channel_count[0]['channel_total'];
			}
		//print_r($info_channel);exit;
		//echo aaaaa.$sum_total.bbbb;
		$this->assign('sum_total',$sum_total);
		$this->assign('info_channel',$info_channel);
		$this->assign('channel_id',$channel_id);
		$this->assign('package',$package);
		$this->assign('start_time',$start_time);
		$this->assign('end_time',$end_time);
		$this->assign('from_value',$from_value);
		$this->assign('to_value',$to_value);
		$this->display('channel_down_list');
		//$this->display('download');
	}
	function  channel_serch_key(){
		//$Model = new Model();
		$Model = D("Sj.Search");
		$soft_file_db = M('soft_file');
		import("@.ORG.Page");
		$start_tm=$_GET['start_time'];
		$stop_tm=$_GET['end_time'];
		$package=$_GET['package'];
		$serch_key=$_GET['serch_key'];
		$channel_id=escape_string($_GET['channel_id']);
		$time = $this -> gettimeArr("channel_serch_key");
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		$time=implode(",", $time);
		$time=str_replace("-","",$time);
		$cname = $soft_file_db->table('sj_channel')->where(array("cid"=>$serch_key))->getField('chl');
		$where="where package='".$package."' and channel='".$cname."' and timestamp between '". strtotime($from_value."00:00:00")."' and '".strtotime($to_value."23:59:59")."'";
		$zh_table=$this->back_bsql($time,$where);
		$sql="select count(distinct serch_key) as num from (".$zh_table.") as zhang";
		$count_array=$Model->query("select count(date) as num from (".$zh_table.") as zhang");
		$count=$count_array[0]['num'];
		//echo $count;exit;
		$getpage = intval($_GET['getpage']) > 0 ? $_GET['getpage'] : 10;
		//echo $start_tm."</br>".$stop_tm;exit;
		$Page=new Page($count,$getpage);
		$limit = " limit ".$Page->firstRow.','.$Page->listRows;
		$inf=$Model->query($zh_table.$limit);
		//软件下载总量
		$page = $Page->show();
		//print_r($page);exit;
		$this -> assign("page",$page);
		$this -> assign("getpage",$getpage);
		$this->assign('info_channel',$inf);
		$this->assign('channel_id',$channel_id);
		$this->assign('package',$package);
		$this->assign('start_time',$start_tm);
		$this->assign('end_time',$stop_tm);
		$this->assign('from_value',$from_value);
		$this->assign('to_value',$to_value);
		$this->display('channel_download');

	}
	//获取所有表
	function back_bsql($biao,$where){
			$biao=explode(",",$biao);
			$count=count($biao);
			for($i=0;$i<$count;$i++){
				$zh_table.="select sum(count) as num,{$biao[$i]} as date from sj_channel_daily_download_{$biao[$i]} ".$where." union ";
			}
			$zh_table = substr($zh_table,0,strlen($zh_table)-6);
			return $zh_table;
	}

	function  get_pack_list($file_txt,$zh_field,$field,$dtime,$dtime1,$model){
		if(!file_exists($file_txt)){
					//$a=filectime($file_txt);
					$sql = "SELECT package".$zh_field.",SUM(".$field.") AS s FROM sj_download_count WHERE submit_day >= UNIX_TIMESTAMP('".$dtime."') AND submit_day <=  UNIX_TIMESTAMP('".$dtime1."') GROUP BY package ORDER BY s DESC";
					$result = $model->query($sql);
					foreach($result as $k => $val){
						$zh_tong[$val['package']]=$val;
						$zh_tong[$val['package']]['biao']=$k;
					}
					$pack_list=json_encode($zh_tong);
					$file = fopen($file_txt,"w");
					fputs($file,$pack_list);
					fclose($file);
				}

					$pack_list=file_get_contents($file_txt);
					$zh_pack_list=json_decode($pack_list,true);

			return $zh_pack_list;
	}
	//删除过期文件
	public function del_dir($dir){	//删除目录
		if(!($mydir=@dir($dir))){
			return;
		}
		while($file=$mydir->read()){
			if(is_file($dir.$file)){
				$file_time=@filectime($dir.$file);	//读取文件的最后更新时间
				if(time()-$file_time>3600*24*7){
					@chmod($dir.$file, 0777);
					@unlink($dir.$file);
				}
			}
		}
		$mydir->close();
		@chmod($dir, 0777);
		//@rmdir($dir);
	}
	public function sjtop_csv(){
		ini_set('memory_limit','1024M');
		set_time_limit(0);
		$zh_list=$_SESSION['zh']['list'];
		$ptime=$_SESSION['zh']['ptime'];
		$ptime1=$_SESSION['zh']['ptime1'];
		//print_r($list);exit;
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="zh_sjtop.csv"');
		header('Cache-Control: max-age=0');
		$fp = fopen('php://output', 'a');
		$str = "日期 , ".$ptime." , 至 , ".$ptime1;
		echo iconv("utf-8",'gbk',$str)."\r\n";

		$head = array('序号', '应用名称','包名','web下载量','手机下载量','手机更新量','合作下载量','wap下载量','总下载量');
		foreach ($head as $i => $v) {
			// CSV的Excel支持GBK编码，一定要转换，否则乱码
			$head[$i] = iconv('utf-8', 'gbk', $v);
		}

		 //将数据通过fputcsv写到文件句柄
		fputcsv($fp, $head);

		foreach($zh_list as $idx => $info){
			$list=array();
			$list['id']=iconv('utf-8', 'gbk',$info['num'])."\t";
			$list['softname']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['softname']))."\t";
			$list['package']=iconv('utf-8', 'gbk',str_replace(array("\r\n", "\n", "\r","\t"),"",$info['package']))."\t";

			$list['web_dl_cnt']=iconv('utf-8', 'gbk',$info['web_dl_cnt'])."\t";
			$list['mob_dl_cnt']=iconv('utf-8', 'gbk',$info['mob_dl_cnt'])."\t";
			$list['mob_up_cnt']=iconv('utf-8', 'gbk',$info['mob_up_cnt'])."\t";
			$list['ptn_dl_cnt']=iconv('utf-8', 'gbk',$info['ptn_dl_cnt'])."\t";
			$list['wap_dl_cnt']=iconv('utf-8', 'gbk',$info['wap_dl_cnt'])."\t";

			$list['s']=iconv('utf-8', 'gbk',$info['s'])."\t";
			fputcsv($fp, $list);
		}

	}
	//渠道、类别统计by张辉
	function channel_daily_download(){
	//echo time();
		$model=M();
		$category = D('Sj.Category');
		//$download_zong = M('channel_daily_download');
		$download_zong = D("Sj.Download");
		$channel=M("channel");
		$category_m=M("category");
		import("@.ORG.Page");
		if(empty($_REQUEST['is_submit'])){
			$request_category=$_SESSION["zh_mcategory"];
		}else{
			$request_category=$_REQUEST['categoryid'][0];
			$_SESSION["zh_mcategory"]=$request_category;
		}
		$request_category_name=$category_m->where(array('status'=>1,'category_id'=>$request_category))->getfield("name");
		$array_config=array(
			"categoryid"=>"categoryid[]",
			"selected"=>$request_category,
			"key"=>1
		);
		$zh_where="where ";
		 if($_REQUEST['is_submit']){
			if(empty($_REQUEST['fromdate'])||empty($_REQUEST['todate'])){
				$this->error("请选择时间");
			}else{
				$start_time=strtotime($_REQUEST['fromdate']." 00:00:00");
				$end_time=strtotime($_REQUEST['todate']." 23:59:59");
				$from_value=$_REQUEST['fromdate'];
				$to_value=$_REQUEST['todate'];
				if(($end_time-$start_time)>3600*24*184){
					$this -> error('选择区间超过六个月！！');
				}
				$zh_where.="timestamp >='".$start_time."' and timestamp<= '".$end_time."'";
			}
			if($_REQUEST['cid']){
				$cid_array=$_REQUEST['cid'];
				//print_r($cid_array);exit;
				foreach($cid_array as $k){
					if($k!=0){
						$new_cid_array[]=$k;
					}
				}
				//print_r($new_cid_array);exit;
				$cid=implode(",",$new_cid_array);
				$zh_where.=" and channel_id in (".$cid.")";
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
				//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$zh_res = $download_zong->table('sj_admin_filter')->where($zh_map)->find();
				if(!empty($zh_res) && ($zh_res['filter_type'])==1){
					$zh_where .=" ";
				}else{
					$source_type = USER_FILTER_TYPE;
					$target_type = CHANNEL_FILTER_TYPE;
					$cid_array_filter="select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
					$cid_array_filter_result=$model->query($cid_array_filter);
					foreach($cid_array_filter_result as $c=>$l)
					{
						$cid_str.=$l['cid'].",";
					}
					$cid_str_array=substr($cid_str,0,-1);
					$zh_where .= " and channel_id in (".$cid_str_array.") ";
				}
			}
			if($_REQUEST['categoryid'] && ($_REQUEST['categoryid'][0]!=0)){
				if($_REQUEST['categoryid'][0]==1||$_REQUEST['categoryid'][0]==2){
					$pa_where['status']=1;
					$pa_where['parentid']=$_REQUEST['categoryid'][0];
					$parent_array=$category_m->where($pa_where)->field("category_id")->select();
					foreach($parent_array as $key=>$val){
						$parent_str.=$val['category_id'].",";
					}
					$parent_str=substr($parent_str,0,-1);
					$cate_where['status']=1;
					$cate_where['parentid']=array("in",$parent_str);
					$cate_array=$category_m->where($cate_where)->field("category_id")->select();
					foreach($cate_array as $m => $n){
						$cate_str.=$n['category_id'].",";
					}
					$categoryid=substr($cate_str,0,-1);
					$zh_where.=" and category_id in(".$categoryid.")";
				}else{
					$cate_where['status']=1;
					$cate_where['category_id']=$_REQUEST['categoryid'][0];
					$parent_id=$category_m->where($cate_where)->getfield("parentid");
					if($parent_id==1||$parent_id==2){
						$category_where['status']=1;
						$category_where['parentid']=$_REQUEST['categoryid'][0];
						$cate_array=$category_m->where($category_where)->field("category_id")->select();
						foreach($cate_array as $m => $n){
							$cate_str.=$n['category_id'].",";
						}
						$categoryid=substr($cate_str,0,-1);
						$zh_where.=" and category_id in(".$categoryid.")";
					}else{
						$categoryid=$_REQUEST['categoryid'][0];
						$zh_where.=" and category_id in (".$categoryid.")";
					}
				}
				/* $cate_where['status']=1;
				$cate_where['category_id']=$_REQUEST['categoryid'][0];
				$parent_id=$category_m->where($cate_where)->getfield("parentid");
				if($parent_id==0){
					$pa_where['status']=1;
					$pa_where['parentid']=$_REQUEST['categoryid'][0];
					$parent_array=$category_m->where($pa_where)->field("category_id")->select();
					print_r($parent_array);exit;
				}elseif($parent_id==1||$parent_id==2){

				}else{
					$categoryid=$_REQUEST['categoryid'][0];
					$zh_where.=" and category_id=".$categoryid;
				} */
			}
		}else{
			if($_REQUEST['fromdate'] && $_REQUEST['todate']){
					$start_time=strtotime($_REQUEST['fromdate']." 00:00:00");
					$end_time=strtotime($_REQUEST['todate']." 23:59:59");
					$from_value=$_REQUEST['fromdate'];
					$to_value=$_REQUEST['todate'];
					if(($end_time-$start_time)>3600*24*184){
						$this -> error('选择区间超过六个月！！');
					}
					$zh_where.="timestamp >='".$start_time."' and timestamp<= '".$end_time."'";
			}else{
				$start_time=$this->start_time();
				$now_data=date("Y-m-d",time()-86400);
				$end_time=strtotime($now_data." 23:59:59");
				$from_value=date("Y-m-d",$start_time);
				$to_value=date("Y-m-d",$end_time);
				$zh_where="where timestamp >='".$start_time."' and timestamp<= '".$end_time."'";
				}
			if($_REQUEST['cid']){
				$cid=$_REQUEST['cid'];
				$cid_array=explode(",",$_REQUEST['cid']);
				$zh_where .=" and channel_id in (".$_REQUEST['cid'].")";
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type= CHANNEL_SHOW_CONTROL;
				//$target_type = CHANNEL_FILTER_TYPE;
				$zh_map = array(
					'source_type' => $source_type,
					'source_value' => $_SESSION['admin']['admin_id'],
					'target_type' => $target_type
				);
				$zh_res = $download_zong->table('sj_admin_filter')->where($zh_map)->find();
				if(!empty($zh_res) && ($zh_res['filter_type'])==1){
					$zh_where .=" ";
				}else{
					$source_type = USER_FILTER_TYPE;
					$target_type = CHANNEL_FILTER_TYPE;
					$cid_array_filter="select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
					$cid_array_filter_result=$model->query($cid_array_filter);
					foreach($cid_array_filter_result as $c=>$l)
					{
						$cid_str.=$l['cid'].",";
					}
					$cid_str_array=substr($cid_str,0,-1);
					$zh_where .= " and channel_id in (".$cid_str_array.") ";
					//$zh_where .= " and channel_id in (select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}') ";
				}
			}
			if($_REQUEST['categoryid'] && ($_REQUEST['categoryid']!=0)){
				$categoryid=$_REQUEST['categoryid'];
				$zh_where.=" and category_id in (".$categoryid.")";
			}
		}
		$count_sql="select count(*) as num from (select channel_id,sum(count)as zong,category_id,timestamp,action from sj_channel_daily_download ".$zh_where." group by channel_id order by zong) as zhang";
		$search_count=$download_zong->query($count_sql);
		$count=$search_count[0]['num'];
		$param = http_build_query($_GET);
		//print_r($param);
		$param="fromdate=".$from_value."&todate=".$to_value."&cid=".$cid."&categoryid=".$categoryid."&".$param;
		$Page = new Page($count, 10, $param);
		$Page->callback = 'return get_params();';
		$limit=" limit ".$Page->firstRow . ',' . $Page->listRows;
		$sql="select channel_id,sum(count)as zong,category_id,timestamp,action from sj_channel_daily_download ".$zh_where." group by channel_id order by zong desc".$limit;
		$download_result=$download_zong->query($sql);
		foreach($download_result as $k=>$val){
			$ch_where['status']=1;
			$ch_where['cid']=$val['channel_id'];
			if($val['channel_id']==0){
				$chname="";
			}else{
				$chname=$channel->where($ch_where)->getfield("chname");
			}
			$download_result[$k]['chname']=$chname;
			$ca_where['status']=1;
			$ca_where['category_id']=$val['category_id'];
			$caname=$category_m->where($ca_where)->getfield("name");
			$download_result[$k]['caname']=$caname;
		}
		$zh_cids=array();
		foreach($cid_array as $k=>$val){
			if($val!=0){
				$zh_cids[$k]['cid']=$val;
				$c_where['status']=1;
				$c_where['cid']=$val;
				$zh_chname=$channel->where($c_where)->getfield("chname");
				$zh_cids[$k]['chname']=$zh_chname;
			}

		}
		$conf_list = $category->getCategory($array_config);
		if ($_GET['p'])
			$this->assign('p', $_GET['p']);
		else
		$this->assign('p', '1');
		$show = $Page->show();
		$this->assign("page", $show);
		$this->assign('from_value',$from_value);
		$this->assign('to_value',$to_value);
		$this->assign('conflist',$conf_list);
		$this->assign('categoryid',$categoryid);
		$this->assign('cid_array',$zh_cids);
		$this->assign('request_category',$request_category);
		$this->assign('request_category_name',$request_category_name);
		$this->assign('zh_cid_str',$cid);
		$this->assign('download_result',$download_result);
		$this->display();
	}
	function  start_time(){
		//$n=date("N",time());
		$one_time=time()-(7*86400);
		$One_date=date("Y-m-d",$one_time);
		$start_time=strtotime($One_date);
		return $start_time;

	}
	function channel_daily_download_one(){
		$cid = $_GET['cid'];
		if($_GET['date']){
		$date_arr = explode('+',$_GET['date']);
		$from_value = $date_arr[0];
		$to_value = $date_arr[1];
		$this -> assign('from_value',$from_value);
		$this -> assign('to_value',$to_value);
		$datewhere="timestamp >='".strtotime($from_value." 00:00:00")."' and timestamp<= '".strtotime($to_value." 23:59:59")."'";
		}else{
		  $this -> error("操作有误！！");
		}
		$where = "`channel_id` = ".$cid." and ";
		if($_GET['category_id']&&$_GET['category_id']!=0){
			$category_id = substr($_GET['category_id'],0,strpos($_GET['category_id'],'&'));
			$where.="category_id in(".$category_id.") and ";
		}
		$Model = new Model();
		$download_sum = D("Sj.Download");
		$sql = "select sum(count) as zong,category_id,channel_id,action,timestamp from sj_channel_daily_download where ".$where.$datewhere." group by timestamp,category_id order by timestamp desc";
		$info = $download_sum -> query($sql);
		foreach($info as $k=>$val){
			$channel_name = $Model -> Table('sj_channel') -> where(array("cid" => $val['channel_id'])) -> getField("chname");
			$category_name = $Model -> Table('sj_category') -> where(array("category_id" => $val['category_id'])) -> getField("name");
			$info[$k]['channel_name']=$channel_name;
			$info[$k]['category_name']=$category_name;
		}
		$channel_name = $Model -> Table('sj_channel') -> where(array("cid" => $cid)) -> getField("chname");
		$category_name = $Model -> Table('sj_category') -> where(array("category_id" => $_SESSION["zh_mcategory"])) -> getField("name");
		/* foreach($info as $idx => $data){
			$dname = $Model -> Table('pu_device') -> where("did = ".$data['device'])-> getField('dname');
			$info[$idx]['dname'] = $dname;
		} */
		$this -> assign('data_download_one',$info);
		$this -> assign('chname',$channel_name);
		$this -> assign('category_name',$category_name);
		$this -> display('channel_daily_download_one');
	}

	//top50激活量查看
	function activatequantity_top50(){
		$stateDB = D("Sj.GoStats");
		//$stateDB->getConnection();
		$info = $stateDB->query("select sum(`counts`) as sum from activation_state where pid=1 and status=1");
		$stat_count = $info[0]['sum'];
		$this->assign('sum',$stat_count);

		$time = $this -> gettimeArr('activatequantity_new');
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		$from_tms = strtotime($from_value);
		$to_tms = strtotime($to_value);
		//去掉易观sdk_fl渠道
		$sql = "select * from (select cid,sum(counts) as  c from activation_state where pid=1 and status=1 and submit_tm>={$from_tms} and submit_tm<={$to_tms} and cid<>3948 group by cid ) A order by A.c desc limit 50";
		$res = $stateDB->query($sql);
		$cids = array();
		foreach ($res as $row) {
			$cids[] = $row['cid'];
		}
		$cids_str = implode(',', $cids);

		$jscategories = array();
		$channel_result = array();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];
			$times = $time[$i].' 23:59:59';
			$timee = $time[$i].' 00:00:00';
			// activate_state全不全？
			$ts = strtotime($time[$i]);
			$sql = "select sum(`counts`) as sum from activation_state where pid=1 and status=1 and submit_tm=${ts}";
			$info = $stateDB->query($sql);
			$slen = $info[0]['sum'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;
			foreach ($cids as $cid) {
				!isset($channel_result[$cid]) && $channel_result[$cid] = array();
				$channel_result[$cid][$i] = 0;
			}
			
			//$channel_result[$time[$i]] = array();
			$sql = "select cid, sum(`counts`) as sum from activation_state where pid=1 and status=1 and submit_tm=${ts} and cid in ({$cids_str}) group by cid";
			$res = $stateDB->query($sql);
			foreach ($res as $row) {
				$channel_result[$row['cid']][$i] = intval($row['sum']);
			}
		}
		$Model = new Model();
		$sql = "select cid,chname from sj_channel where cid in ({$cids_str})";
		$res = $Model->query($sql);
		$chname_map = array();
		foreach ($res as $row) {
			$chname_map[$row['cid']] = $row['chname'];
		}
		$count = array_sum($values);

		$i = 0;
		$top50data = array();
		foreach ($cids as $cid) {
			$n = $i / 10;
			$i++;
			$top50data[$n][] = array(
				'name' => 'T'. $i. '-'. $chname_map[$cid],
				'data' => $channel_result[$cid]
			);
		}

		$this->assign('count',$count);

		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		
		$this->writelog('查看了top50激活量统计信息', 'activation_state','',__ACTION__ ,'','view');
		
		$this->assign("top50data", $top50data);

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jsactivatequantity_top50');
	}


	//统计管理_激活量统计显示页	  取新数据源
	function activatequantity_new(){
		$stateDB = D("Sj.GoStats");
		//$stateDB->getConnection();
		$info = $stateDB->query("select sum(`counts`) as sum from activation_state where pid=1 and status=1");
		$stat_count = $info[0]['sum'];
		$this->assign('sum',$stat_count);

		$time = $this -> gettimeArr('activatequantity_new');
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		$jscategories = array();
		$channel_result = array();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];
			$times = $time[$i].' 23:59:59';
			$timee = $time[$i].' 00:00:00';
			// activate_state全不全？
			$ts = strtotime($time[$i]);
			$sql = "select sum(`counts`) as sum from activation_state where pid=1 and status=1 and submit_tm=${ts}";
			$info = $stateDB->query($sql);
			$slen = $info[0]['sum'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;
		}
		$count = array_sum($values);
		//$activaDB -> closeconnect();
		$jsdata = array(
			array(
				'name'=>'激活量',
				'data'=>$values
			)
		);
		
		$w = count($values)*50;
		if($w < 750)
		{
			$w = 750;
		}
		$this->assign('w',$w);
		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}

		$this->assign("r",rand());

		$this->assign('x_labels',implode(",",$x_labels));
		$this->assign('values',implode(",",$values));
		$this->assign('max_values',$max_values);

		$this->assign('count',$count);

		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);

		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->writelog('查看了激活量统计信息', 'activation_state','',__ACTION__ ,'','view');

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jsactivatequantity_new');
//		$this->display('activatequantity');
	}

	//统计管理_游戏激活量统计显示页
	function activatequantity_game(){
		$stateDB = D("Sj.GoStats");
		//$stateDB->getConnection();
		$info = $stateDB->query("select sum(`counts`) as sum from activation_game_state where pid=5 and status=1");
		$stat_count = $info[0]['sum'];
		$this->assign('sum',$stat_count);

		$time = $this -> gettimeArr('activatequantity_game');
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		//$count=$this->active_count($from_value, $to_value." 23:59:59");

		//flash 数据
		$x_labels = array();//x轴的数据
		$values = array();//y轴的数据
		$max_values = 0;

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);

		$jscategories = array();

		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];
			$times = $time[$i].' 23:59:59';
			$timee = $time[$i].' 00:00:00';
			// activate_state全不全？
			$ts = strtotime($time[$i]);
			$sql = "select sum(`counts`) as sum from activation_game_state where pid=5 and status=1 and submit_tm=${ts}";
			$info = $stateDB->query($sql);
			$slen = $info[0]['sum'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;
		}
		$count = array_sum($values);
		//$activaDB -> closeconnect();
		$jsdata = array(
			array(
				'name'=>'激活量',
				'data'=>$values
			)
		);
		$w = count($values)*50;
		if($w < 750)
		{
			$w = 750;
		}
		$this->assign('w',$w);
		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}

		$this->assign("r",rand());

		$this->assign('x_labels',implode(",",$x_labels));
		$this->assign('values',implode(",",$values));
		$this->assign('max_values',$max_values);

		$this->assign('count',$count);

		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);

		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->writelog('查看了激活量统计信息', 'activation_game_state','',__ACTION__ ,'','view');

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jsactivatequantity_game');
//		$this->display('activatequantity');
	}

	//统计管理_HD激活量统计显示页
	function activatequantity_hd(){
		$stateDB = D("Sj.GoStats");
		//$stateDB->getConnection();
		$info = $stateDB->query("select sum(`counts`) as sum from activation_hd_state where pid=4 and status=1");
		$stat_count = $info[0]['sum'];
		$this->assign('sum',$stat_count);

		$time = $this -> gettimeArr('activatequantity_hd');
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		//$count=$this->active_count($from_value, $to_value." 23:59:59");

		//flash 数据
		$x_labels = array();//x轴的数据
		$values = array();//y轴的数据
		$max_values = 0;

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);

		$jscategories = array();

		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$x_labels[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];
			$times = $time[$i].' 23:59:59';
			$timee = $time[$i].' 00:00:00';
			// activate_state全不全？
			$ts = strtotime($time[$i]);
			$sql = "select sum(`counts`) as sum from activation_hd_state where pid=4 and status=1 and submit_tm=${ts}";
			$info = $stateDB->query($sql);
			$slen = $info[0]['sum'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;
		}
		$count = array_sum($values);
		//$activaDB -> closeconnect();
		$jsdata = array(
			array(
				'name'=>'激活量',
				'data'=>$values
			)
		);
		$w = count($values)*50;
		if($w < 750)
		{
			$w = 750;
		}
		$this->assign('w',$w);
		$max_values = ceil($max_values * 1.15 / 50) * 50;
		if ($max_values < 50) {
			$max_values = 50;
		}

		$this->assign("r",rand());

		$this->assign('x_labels',implode(",",$x_labels));
		$this->assign('values',implode(",",$values));
		$this->assign('max_values',$max_values);

		$this->assign('count',$count);

		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);

		$this->assign('fromdate',$from_value);
		$this->assign('todate',$to_value);
		$this->writelog('查看了HD激活量统计信息', 'activation_hd_state','',__ACTION__ ,'','view');

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jsactivatequantity_hd');
//		$this->display('activatequantity');
	}

	//设备下载刷量
	function device_download_brush(){
		$model = M('device_download_brush');
		$where = ' A.`status` = 1 ';
		if(isset($_GET['softname'])){
			$where .= ' AND B.`softname` like "%'.(string)$_GET['softname'].'%"';
			$this->assign("softname",(string)$_GET['softname']);
		}else{
			$this->assign("softname",'');
		}
		if(isset($_GET['package'])){
			$where .= ' AND A.`package` like "%'.(string)$_GET['package'].'%"';
			$this->assign("package",(string)$_GET['package']);
		}else{
			$this->assign("package",'');
		}
		if(isset($_GET['start_time'])){
			$start_time = (string)$_GET['start_time'];
			$where .= " AND A.`brush_time` >= '".strtotime($start_time." 00:00:00")."'";
			$this->assign("start_time",$start_time);
		}else{
			$where .= " AND A.`brush_time` >= '".strtotime(date("Y-m-d", strtotime("-7 days", time()))." 00:00:00")."'";
			$this->assign("start_time",date("Y-m-d", strtotime("-7 days", time())));
		}
		if(isset($_GET['end_time'])){
			$end_time  = (string)$_GET['end_time'];
			$where .= " AND A.`brush_time` <= '".strtotime($end_time." 23:59:59")."'";
			$this->assign("end_time",$end_time);
		}else{
			$where .= " AND A.`brush_time` <= '".strtotime(date('Y-m-d')." 23:59:59")."'";
			$this->assign("end_time",date('Y-m-d'));
		}
		$sql  = " SELECT COUNT(DISTINCT A.brush_id) as count FROM __TABLE__ AS A LEFT JOIN sj_soft AS B ";
		$sql .= " ON A.package = B.package WHERE ".$where." LIMIT 1";
		$count_total = $model->query($sql);
		//echo $model->getLastSql();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign("lr",(int)$_GET['lr']);
		}else{
			$this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
			$this->assign("p",(int)$_GET['p']);
		}else{
			$this->assign("p", 1);
		}
		$page = new Page($count_total[0]['count'], $limit, $param);
		$brush_list =  $model -> field('A.*,B.softname') -> table('sj_device_download_brush AS A')
						-> join('sj_soft AS B ON A.package = B.package')
						-> where($where)
						-> group('A.brush_time,A.package')
						-> order('A.brush_time ASC')
						-> limit($page->firstRow.','.$page->listRows)
						-> select();
		//echo $model->getLastSql();
		for($i = 0;$i < count($brush_list); $i++){
			$brush_list[$i]['percent'] = substr($brush_list[$i]['percent'] * 100,0,5) ."%";
		}
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());
		$this->assign('brush_list',$brush_list);
		$this->display();
	}

	//IP下载刷量
	function ip_download_brush(){
		$model = M('ip_download_brush');
		$where = ' A.`status` = 1 ';
		if(isset($_GET['softname'])){
			$where .= ' AND B.`softname` like "%'.(string)$_GET['softname'].'%"';
			$this->assign("softname",(string)$_GET['softname']);
		}else{
			$this->assign("softname",'');
		}
		if(isset($_GET['package'])){
			$where .= ' AND A.`package` like "%'.(string)$_GET['package'].'%"';
			$this->assign("package",(string)$_GET['package']);
		}else{
			$this->assign("package",'');
		}
		if(isset($_GET['start_time'])){
			$start_time = (string)$_GET['start_time'];
			$where .= " AND A.`brush_time` >= '".strtotime($start_time." 00:00:00")."'";
			$this->assign("start_time",$start_time);
		}else{
			$where .= " AND A.`brush_time` >= '".strtotime(date("Y-m-d", strtotime("-7 days", time()))." 00:00:00")."'";
			$this->assign("start_time",date("Y-m-d", strtotime("-7 days", time())));
		}
		if(isset($_GET['end_time'])){
			$end_time  = (string)$_GET['end_time'];
			$where .= " AND A.`brush_time` <= '".strtotime($end_time." 23:59:59")."'";
			$this->assign("end_time",$end_time);
		}else{
			$where .= " AND A.`brush_time` <= '".strtotime(date('Y-m-d')." 23:59:59")."'";
			$this->assign("end_time",date('Y-m-d'));
		}
		$sql  = " SELECT COUNT(DISTINCT A.brush_id) as count FROM __TABLE__ AS A LEFT JOIN sj_soft AS B ";
		$sql .= " ON A.package = B.package WHERE ".$where." LIMIT 1";
		$count_total = $model->query($sql);
		//echo $model->getLastSql();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign("lr",(int)$_GET['lr']);
		}else{
			$this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
			$this->assign("p",(int)$_GET['p']);
		}else{
			$this->assign("p", 1);
		}
		$page = new Page($count_total[0]['count'], $limit, $param);
		$brush_list =  $model -> field('A.*,B.softname') -> table('sj_ip_download_brush AS A')
						-> join('sj_soft AS B ON A.package = B.package')
						-> where($where)
						-> group('A.brush_time,A.package')
						-> order('A.brush_time ASC')
						-> limit($page->firstRow.','.$page->listRows)
						-> select();
		//echo $model->getLastSql();
		for($i = 0;$i < count($brush_list); $i++){
			$brush_list[$i]['percent'] = substr($brush_list[$i]['percent'] * 100,0,5) ."%";;
		}
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());
		$this->assign('brush_list',$brush_list);
		$this->display();
	}

	//manzhi下载刷量
	function manzhi_download_brush(){
		$model = M('manzhi_download_brush');
		$where = ' A.`status` = 1 ';
		if(isset($_GET['softname'])){
			$where .= ' AND B.`softname` like "%'.(string)$_GET['softname'].'%"';
			$this->assign("softname",(string)$_GET['softname']);
		}else{
			$this->assign("softname",'');
		}
		if(isset($_GET['package'])){
			$where .= ' AND A.`package` like "%'.(string)$_GET['package'].'%"';
			$this->assign("package",(string)$_GET['package']);
		}else{
			$this->assign("package",'');
		}
		if(isset($_GET['start_time'])){
			$start_time = (string)$_GET['start_time'];
			$where .= " AND A.`brush_time` >= '".strtotime($start_time." 00:00:00")."'";
			$this->assign("start_time",$start_time);
		}else{
			$where .= " AND A.`brush_time` >= '".strtotime(date("Y-m-d", strtotime("-7 days", time()))." 00:00:00")."'";
			$this->assign("start_time",date("Y-m-d", strtotime("-7 days", time())));
		}
		if(isset($_GET['end_time'])){
			$end_time  = (string)$_GET['end_time'];
			$where .= " AND A.`brush_time` <= '".strtotime($end_time." 23:59:59")."'";
			$this->assign("end_time",$end_time);
		}else{
			$where .= " AND A.`brush_time` <= '".strtotime(date('Y-m-d')." 23:59:59")."'";
			$this->assign("end_time",date('Y-m-d'));
		}
		$sql  = " SELECT COUNT(DISTINCT A.brush_id) as count FROM __TABLE__ AS A LEFT JOIN sj_soft AS B ";
		$sql .= " ON A.package = B.package WHERE ".$where." LIMIT 1";
		$count_total = $model->query($sql);
		//echo $model->getLastSql();
		import("@.ORG.Page");
		$param = http_build_query($_GET);
		$limit = 10;
		if(isset($_GET['lr'])){
			$this->assign("lr",(int)$_GET['lr']);
		}else{
			$this->assign("lr",$limit);
		}
		if(isset($_GET['p'])){
			$this->assign("p",(int)$_GET['p']);
		}else{
			$this->assign("p", 1);
		}
		$page = new Page($count_total[0]['count'], $limit, $param);
		$brush_list =  $model -> field('A.*,B.softname') -> table('sj_manzhi_download_brush AS A')
						-> join('sj_soft AS B ON A.package = B.package')
						-> where($where)
						-> group('A.brush_time,A.package')
						-> order('A.brush_time ASC')
						-> limit($page->firstRow.','.$page->listRows)
						-> select();
		//echo $model->getLastSql();
		for($i = 0;$i < count($brush_list); $i++){
			$brush_list[$i]['percent'] = substr($brush_list[$i]['percent'] * 100,0,5) ."%";
		}
		$page->setConfig('header', '篇记录');
		$page->setConfig('first', '<<');
		$page->setConfig('last', '>>');
		$this->assign("page", $page->show());
		$this->assign('brush_list',$brush_list);
		$this->display();
	}

	function channel2mobilestatic_new(){
		$cid = $_GET['cid'];
		if($_GET['date']){
		$date_arr = explode('+',$_GET['date']);
		$from_value = $date_arr[0];
		$to_value = substr($date_arr[1],0,strpos($date_arr[1],'&'));
		$this -> assign('from_value',$from_value);
		$this -> assign('to_value',$to_value);
		$datewhere = " `date` between ".strtotime($from_value." 00:00:00")." and ".strtotime($to_value." 23:59:59")." ";
		}else{
		  $this -> error("操作有误！！");
		}
		$where = "`chl` = ".$cid." and ";
		$Model = new Model();
		$activaDB  = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$sql = "select * from mobile_chl_soft_stat where ".$where.$datewhere." order by date desc";
		$info = $activaDB -> query($sql);
		$channel_name = $Model -> Table('sj_channel') -> where(array("cid" => $cid)) -> getField("chname");
		foreach($info as $idx => $data){
			$dname = $Model -> Table('pu_device') -> where("did = ".$data['device'])-> getField('dname');
			$info[$idx]['dname'] = $dname;
		}
		$this -> assign('data_static',$info);
		$this -> assign('chname',$channel_name);
		$this -> display('channel2mobilestatic');
	}

	//统计管理_渠道用户统计显示页
	function channelImage_new(){
		$time = $this -> gettimeArr();
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);
		$jscategories = array();

		$Model = new Model();
		$max_values = 0;
		$values = array();
		$values2 = array();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$date[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];

			$info=$activaDB ->query('select sum(counts) as count from activation_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and pid=1 and submit_tm   between "'.strtotime($time[$i]."00:00:00").'" and "'.strtotime($time[$i]."23:59:59").'"');
			$info1[]=$info;

			$slen =$info[0]['count'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;

			$info2=$activaDB ->query('select sum(counts) as count from activation_coefficient_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm = "'.strtotime($time[$i]."00:00:00").'"');
			$values2[] = (int)$info2[0]['count'];

			$info3=$activaDB ->query('select sum(counts) as count from activation_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and location=1 and pid=1 and submit_tm   between "'.strtotime($time[$i]."00:00:00").'" and "'.strtotime($time[$i]."23:59:59").'"');
			$values3[]=(int)$info3[0]['count'];

			$values4[]=(int)$slen - (int)$info3[0]['count'];
		}

		$jsdata = array(
			array(
				'name'=>'激活量_总量',
				'data'=>$values
			),
			array(
				'name'=>'激活量_扣量',
				'data'=>$values2
			),
			array(
				'name'=>'激活量_大陆',
				'data'=>$values3
			),
			array(
				'name'=>'激活量_海外',
				'data'=>$values4
			),
		);

		foreach($info1 as $k=>$v){
			foreach($v as $a=>$b){
			  foreach($b as $c=>$d){
				$arr[]=$d;
			  }
			}
		}
		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_COEFFICIENT_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}' AND filter_type = 2";
		$res = $Model->query($sql);
		if (empty($res)) {
			unset($jsdata[1]);
			shuffle($jsdata);
		}
		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jschannelImage');
	}

	//统计管理_游戏渠道用户统计显示页
	function channelImage_game(){
		$time = $this -> gettimeArr();
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		//jschart数据
		$jsdata = array(//线数据
//			array(
//				'name'=>'',//线名
//				'data'=>array(//数据
//					1,2,3
//				)
//			),
		);
		$jscategories = array();

		//$Model = new Model();
		$max_values = 0;
		$values = array();
		$values2 = array();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$date[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];

			$info=$activaDB ->query('select sum(counts) as count from activation_game_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm   between "'.strtotime($time[$i]."00:00:00").'" and "'.strtotime($time[$i]."23:59:59").'"');
			$info1[]=$info;

			$slen =$info[0]['count'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;

			$info2=$activaDB ->query('select sum(counts) as count from activation_game_coefficient_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm = "'.strtotime($time[$i]."00:00:00").'"');
			$values2[] = (int)$info2[0]['count'];
		}

		$jsdata = array(
			array(
				'name'=>'激活量_未扣量',
				'data'=>$values
			),
			array(
				'name'=>'激活量_扣量',
				'data'=>$values2
			),
		);

		foreach($info1 as $k=>$v){
			foreach($v as $a=>$b){
			  foreach($b as $c=>$d){
				$arr[]=$d;
			  }
			}
		}

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jschannelImage_game');
	}

	//统计管理_HD渠道用户统计显示页
	function channelImage_hd(){
		$time = $this -> gettimeArr();
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$from_value = $time[0];
		$to_value = $time[count($time)-1];

		//jschart数据
		$jsdata = array(//线数据
		);
		$jscategories = array();

		//$Model = new Model();
		$max_values = 0;
		$values = array();
		$values2 = array();
		for($i=0,$c=count($time);$i<$c;$i++){
			$d[] = $time[$i];
			$str = explode('-',$time[$i]);
			$date[] = $str[1].'/'.$str[2];
			$jscategories[] = $str[1].'/'.$str[2];

			$info=$activaDB ->query('select sum(counts) as count from activation_hd_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm   between "'.strtotime($time[$i]."00:00:00").'" and "'.strtotime($time[$i]."23:59:59").'"');
			$info1[]=$info;

			$slen =$info[0]['count'];
			if($slen>$max_values)
				$max_values = $slen;
			$values[] = (int)$slen;

			$info2=$activaDB ->query('select sum(counts) as count from activation_hd_coefficient_state where cid="'.escape_string($_GET['cid']).'"  and  status = 1 and submit_tm = "'.strtotime($time[$i]."00:00:00").'"');
			$values2[] = (int)$info2[0]['count'];
		}

		$jsdata = array(
			array(
				'name'=>'激活量_未扣量',
				'data'=>$values
			),
			array(
				'name'=>'激活量_扣量',
				'data'=>$values2
			),
		);

		foreach($info1 as $k=>$v){
			foreach($v as $a=>$b){
			  foreach($b as $c=>$d){
				$arr[]=$d;
			  }
			}
		}

		$this->assign("phpdata",json_encode($jsdata));

		$this->assign("phpcategories",json_encode($jscategories));

		$this->display('jschannelImage_hd');
	}

	//统计管理_渠道用户量统计显示页_new
	function channel_new(){
		import("@.ORG.Page");
		$Model = new Model();
		$channel_category = D('Sj.ChannelCategory');
		$activaDB = D('Sj.GoStats');
		$category_list = $channel_category->getCategory();
		$in_cid_array = array();
		$in_cid = array();
		$not_in_string = '';
		$in_string = '';
		//old
		$on_where = 'a.cid = b.cid AND a.status=1 AND b.status=1 AND b.pid=1';
		//new
		$b_where = 'b.status = 1 and b.pid = 1';
		if (!empty($_REQUEST['cid'])) {
			// XXX: 渠道检查形同虚设
			if(is_array($_REQUEST['cid'])){
				$zh_data['cid']=implode(",",$_REQUEST['cid']);
			}else{
				$zh_data['cid']=$_REQUEST['cid'];
				$_REQUEST['cid']=explode(",",$zh_data['cid']);
			}
			$this->assign("zh_cid_arra",$zh_data['cid']);
			$zh_cids=array();
			foreach($_REQUEST['cid'] as $m=>$n){
				if($n!=0){
					$zh_cids[$m]['cid']=$n;
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$Model->table("sj_channel")->where($c_where)->getfield("chname");
					$zh_cids[$m]['chname']=$zh_chname;
				}
			}
			$this->assign("channel_selected",$zh_cids);
			$not_in_string = 'a.cid IN('. implode(',',$_REQUEST['cid']).')';
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type= CHANNEL_SHOW_CONTROL;
			$zh_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
			if(!empty($zh_res) && ($zh_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}
			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
			$not_in_string = empty($in_cid_array) ? '': ' a.cid IN('. implode(',', $in_cid_array). ')';
		}
		$where = empty($not_in_string) ? '' : " WHERE {$not_in_string} ";
		$sum_user_where = $where;
		$loop_where = '';

		if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$from_time = strtotime( $_REQUEST['fromdate']);
			$to_time = strtotime($_REQUEST['todate']. ' 23:59:59');

			$from_value = date("Y-m-d",$from_time);
			$to_value = date("Y-m-d",$to_time);
			$zh_data['fromdate']=$from_value;
			$zh_data['todate']=$to_value;
			//new
			$b_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";

			$loop_where .= " AND submit_tm >={$from_time} AND submit_tm <{$to_time}";

			$this->assign('start',$from_value);
			$this->assign('end',$to_value);
			}else{
				$to_value=date("Y-m-d",time());
				$from_value=date("Y-m-d",strtotime("-1 week"));
				$from_time = strtotime("-1 week");
				$to_time = time();
				$this->assign('start','');
				$this->assign('end','');
			}

		$where .= empty($where) ? ' where a.cid IN("") and a.status=1 ' : ' and a.status=1 ';
		//new
		$sql_1 = "select cid from sj_channel a ".$where;
		$resinfo = $Model -> query($sql_1);
		$cid_1 = array();
		foreach($resinfo as $info){
			$cid_1[] = $info['cid'];
		}
		$cid_str = 'b.cid in ('.implode(',',$cid_1).')';
		$sql_2 = "select count(DISTINCT b.cid) as counts from activation_state b where ".$cid_str.' and '.$b_where;

		$res = $activaDB->query($sql_2);
		$count = $res[0]['counts'];
		$param = http_build_query($_GET);
		foreach($zh_data as $k=>$val){
			$param .= "&".$k."=".urlencode($val);
		}
		$Page = new Page($count, 500, $param);
		$Page->callback = 'return get_params();';
		$sql_1 = 'select b.cid, sum(b.counts) as num from activation_state b  where '.$cid_str.' and '.$b_where.' group by b.cid order by num desc limit '.$Page->firstRow.','.$Page->listRows;
		$info=$activaDB->query($sql_1);
		$c_info = array();
		$l_info = array();
		$category_result = array();
		foreach($info as $k => $v){
			$info_cid = $v['cid'];
			$info_sql = "select a.category_id,a.cid,a.chname,a.co_group from sj_channel a where a.cid = ".$info_cid." limit 1";
			$channel_info = $Model -> query($info_sql);
			$category_id = $channel_info[0]['category_id'];
			if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
			$v['category_id'] = $category_id;
			$v['chname'] = $channel_info[0]['chname'];
			$v['co_group'] = $channel_info[0]['co_group'];
			$category_result[$category_id]['result'][] = $v;

			$cid = $v['cid'];
			$c_info[$cid] = array();
			$sql = "SELECT cid,sum(counts) as num FROM `activation_coefficient_state` where cid={$cid} AND status=1 {$loop_where}";
			$t=$activaDB->query($sql);
			$t = array_pop($t);
			$c_info[$cid]['num'] = $t['num'];
			$state_num += $t['num'];
			$where = " and `create_time` <= {$to_time}";
			$sql = "SELECT cid,coefficient FROM `sj_channel_coefficient` where cid={$v['cid']} {$where} order by id desc limit 1;";
			$t=$Model->query($sql);
			if ($t) {
				//$t = array_pop($t);
				$c_info[$cid]['coefficient'] = $t[0]['coefficient'];
			} else {
				$c_info[$cid]['coefficient'] = 100;
			}
			$l_info[$cid] = array();
			$sql = "SELECT cid,sum(counts) as num FROM `activation_state` where cid={$cid} AND pid=1 AND location=1 AND status=1 {$loop_where}";
			$t=$activaDB->query($sql);
			$t = array_pop($t);
			$l_info[$cid]['num'] = $t['num'];
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_COEFFICIENT_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}' AND filter_type = 2";
		$res = $Model->query($sql);
		if (empty($res)) {
			$this->assign('show_coefficient',false);
		} else {
			$this->assign('show_coefficient',true);
		}


		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_TOTAL_FILTER_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
		$res = $Model->query($sql);
		$sum = false;
		if (empty($res)) {
			$sql = "select sum(b.counts) as sum from activation_state b where ".$cid_str.' and '.$b_where;
			$r=$activaDB->query($sql);
			$sum = $r[0]['sum'];
			$this->assign('showsum',true);
		} else {
			$this->assign('showsum',false);
		}

		$channel_arr = $_REQUEST['cid'];
		foreach($channel_arr as $key => $val){
			$channel_str_go .= $val.',';
		}
		$this->writelog('查看了渠道用户统计显示页信息', '','',__ACTION__ ,'','view');
		$channel_str = substr($channel_str_go,0,-1);
		$start_tm = $_REQUEST['fromdate'];
		$end_tm = $_REQUEST['todate'];
		$page = $Page->show();
		$this -> assign('channel_str',$channel_str);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('num',$state_num);
		$this->assign('sum',$sum);
		$this->assign('info',$info);
		$this->assign('category_list',$category_result);
		$this->assign('c_info',$c_info);
		$this->assign('l_info',$l_info);
		$this -> assign('to_value',$to_value);
		$this->assign('from_value',$from_value);
		$this->assign('page',$page);
		$this->assign('co_group',C('co_group'));
		$this->display('channel_new');
	}

	//统计管理_游戏渠道用户量统计显示页
	function channel_game(){
		import("@.ORG.Page");
		$Model = new Model();
		$channel_category = D('Sj.ChannelCategory');
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$category_list = $channel_category->getCategory();
		$in_cid_array = array();
		$in_cid = array();
		$not_in_string = '';
		$in_string = '';
		//old
		$on_where = 'a.cid = b.cid AND a.status=1 AND b.status=1 AND b.pid=5';
		//new
		$b_where = 'b.status = 1 and b.pid = 5';
		if (!empty($_REQUEST['cid'])) {
			// XXX: 渠道检查形同虚设
			if(is_array($_REQUEST['cid'])){
				$zh_data['cid']=implode(",",$_REQUEST['cid']);
			}else{
				$zh_data['cid']=$_REQUEST['cid'];
				$_REQUEST['cid']=explode(",",$zh_data['cid']);
			}
			$this->assign("zh_cid_arra",$zh_data['cid']);
			$zh_cids=array();
			foreach($_REQUEST['cid'] as $m=>$n){
				if($n!=0){
					$zh_cids[$m]['cid']=$n;
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$Model->table("sj_channel")->where($c_where)->getfield("chname");
					$zh_cids[$m]['chname']=$zh_chname;
				}
			}
			$this->assign("channel_selected",$zh_cids);
			$not_in_string = 'a.cid IN('. implode(',',$_REQUEST['cid']).')';
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type= CHANNEL_SHOW_CONTROL;
			$zh_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
			if(!empty($zh_res) && ($zh_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}
			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
			$not_in_string = empty($in_cid_array) ? '': ' a.cid IN('. implode(',', $in_cid_array). ')';
		}
		$where = empty($not_in_string) ? '' : " WHERE {$not_in_string} ";
		$sum_user_where = $where;
		$loop_where = '';

		if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$from_time = strtotime( $_REQUEST['fromdate']);
			$to_time = strtotime($_REQUEST['todate']. ' 23:59:59');

			$from_value = date("Y-m-d",$from_time);
			$to_value = date("Y-m-d",$to_time);
			$zh_data['fromdate']=$from_value;
			$zh_data['todate']=$to_value;
			$on_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";
			//new
			$b_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";

			$loop_where .= " AND submit_tm >={$from_time} AND submit_tm <{$to_time}";

			$this->assign('start',$from_value);
			$this->assign('end',$to_value);
		}else{
			$to_value=date("Y-m-d",time());
			$from_value=date("Y-m-d",strtotime("-1 week"));
			$from_time = strtotime("-1 week");
			$to_time = time();
			$this->assign('start','');
			$this->assign('end','');
		}

		$where .= empty($where) ? ' where a.cid IN("") and a.status=1 ' : ' and a.status=1 ';
		$sql_1 = "select cid from sj_channel a " . $where;
		$resinfo = $Model -> query($sql_1);
		$cid_1 = array();
		foreach($resinfo as $info){
			$cid_1[] = $info['cid'];
		}
		$cid_str = 'b.cid in ('.implode(',',$cid_1).')';
		$sql_2 = "select count(DISTINCT b.cid) as counts from activation_game_state b where " . $cid_str . ' and ' . $b_where;

		$res = $activaDB->query($sql_2);
		$count = $res[0]['counts'];
		$param = http_build_query($_GET);
		foreach($zh_data as $k=>$val){
			$param .= "&".$k."=".urlencode($val);
		}
		$Page = new Page($count, 500, $param);
		$Page->callback = 'return get_params();';
		$sql_1 = 'select b.cid, sum(b.counts) as num from activation_game_state b  where '.$cid_str.' and '.$b_where.' group by b.cid order by num desc limit '.$Page->firstRow.','.$Page->listRows;
		$info = $activaDB->query($sql_1);
		$c_info = array();
		$category_result = array();
		foreach ($info as $k => $v) {
			$info_cid = $v['cid'];
			$info_sql = "select a.category_id,a.cid,a.chname from sj_channel a where a.cid = ".$info_cid." limit 1";
			$channel_info = $Model -> query($info_sql);

			$category_id = $channel_info[0]['category_id'];
			if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
			$v['category_id'] = $category_id;
			$v['chname'] = $channel_info[0]['chname'];
			$category_result[$category_id]['result'][] = $v;

			$cid = $v['cid'];
			$c_info[$cid] = array();
			$sql = "SELECT cid,sum(counts) as num FROM `activation_game_coefficient_state` where cid={$cid} AND status=1 {$loop_where}";
			$t=$activaDB->query($sql);
			$t = array_pop($t);
			$c_info[$cid]['num'] = $t['num'];
			$state_num += $t['num'];
			$where = " and `create_time` <= {$to_time}";
			$sql = "SELECT cid,coefficient FROM `sj_channel_coefficient` where cid={$v['cid']} {$where} order by id desc limit 1;";
			$t=$Model->query($sql);
			if ($t) {
				//$t = array_pop($t);
				$c_info[$cid]['coefficient'] = $t[0]['coefficient'];
			} else {
				$c_info[$cid]['coefficient'] = 100;
			}
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_COEFFICIENT_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}' AND filter_type = 2";
		$res = $Model->query($sql);
		if (empty($res)) {
			$this->assign('show_coefficient',false);
		} else {
			$this->assign('show_coefficient',true);
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_TOTAL_FILTER_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
		$res = $Model->query($sql);
		$sum = false;
		if (empty($res)) {
		//old
			//$sql = "SELECT sum(b.counts) as sum  FROM sj_channel a LEFT JOIN pu_channel_first_state b ON ({$on_where}) {$sum_user_where}";
			$sql = "select sum(b.counts) as sum from activation_game_state b where ".$cid_str.' and '.$b_where;
			$r=$activaDB->query($sql);
			$sum = $r[0]['sum'];
			$this->assign('showsum',true);
		} else {
			$this->assign('showsum',false);
		}

		$channel_arr = $_REQUEST['cid'];
		foreach($channel_arr as $key => $val){
			$channel_str_go .= $val.',';
		}
		$channel_str = substr($channel_str_go, 0, -1);
		$start_tm = $_REQUEST['fromdate'];
		$end_tm = $_REQUEST['todate'];
		$page = $Page -> show();
		$this -> assign('channel_str',$channel_str);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('num',$state_num);
		$this -> assign('sum',$sum);
		$this -> assign('info',$info);
		$this -> assign('category_list',$category_result);
		$this -> assign('c_info',$c_info);
		$this -> assign('to_value',$to_value);
		$this -> assign('from_value',$from_value);
		$this -> assign('page',$page);
		$this -> writelog('查看了激活用户量统计信息', 'sj_admin_filter','',__ACTION__ ,'','view');
		$this -> display('channel_game');
	}

	//统计管理_HD渠道用户量统计显示页
	function channel_hd(){
		import("@.ORG.Page");
		$Model = new Model();
		$channel_category = D('Sj.ChannelCategory');
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$category_list = $channel_category->getCategory();
		$in_cid_array = array();
		$in_cid = array();
		$not_in_string = '';
		$in_string = '';
		//old
		$on_where = 'a.cid = b.cid AND a.status=1 AND b.status=1 AND b.pid=4';
		//new
		$b_where = 'b.status = 1 and b.pid = 4';
		if (!empty($_REQUEST['cid'])) {
			// XXX: 渠道检查形同虚设
			if(is_array($_REQUEST['cid'])){
				$zh_data['cid']=implode(",",$_REQUEST['cid']);
			}else{
				$zh_data['cid']=$_REQUEST['cid'];
				$_REQUEST['cid']=explode(",",$zh_data['cid']);
			}
			$this->assign("zh_cid_arra",$zh_data['cid']);
			$zh_cids=array();
			foreach($_REQUEST['cid'] as $m=>$n){
				if($n!=0){
					$zh_cids[$m]['cid']=$n;
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$Model->table("sj_channel")->where($c_where)->getfield("chname");
					$zh_cids[$m]['chname']=$zh_chname;
				}
			}
			$this->assign("channel_selected",$zh_cids);
			$not_in_string = 'a.cid IN('. implode(',',$_REQUEST['cid']).')';
		}else{
			$source_type = USER_FILTER_TYPE;
			$target_type= CHANNEL_SHOW_CONTROL;
			$zh_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$zh_res = $Model->table('sj_admin_filter')->where($zh_map)->find();
			if(!empty($zh_res) && ($zh_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}
			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
			$not_in_string = empty($in_cid_array) ? '': ' a.cid IN('. implode(',', $in_cid_array). ')';
		}
		$where = empty($not_in_string) ? '' : " WHERE {$not_in_string} ";
		$sum_user_where = $where;
		$loop_where = '';

		if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$from_time = strtotime( $_REQUEST['fromdate']);
			$to_time = strtotime($_REQUEST['todate']. ' 23:59:59');

			$from_value = date("Y-m-d",$from_time);
			$to_value = date("Y-m-d",$to_time);
			$zh_data['fromdate']=$from_value;
			$zh_data['todate']=$to_value;
			$on_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";
			//new
			$b_where .= " AND b.submit_tm >={$from_time} AND b.submit_tm <{$to_time}";

			$loop_where .= " AND submit_tm >={$from_time} AND submit_tm <{$to_time}";

			$this->assign('start',$from_value);
			$this->assign('end',$to_value);
		}else{
			$to_value=date("Y-m-d",time());
			$from_value=date("Y-m-d",strtotime("-1 week"));
			$from_time = strtotime("-1 week");
			$to_time = time();
			$this->assign('start','');
			$this->assign('end','');
		}

		$where .= empty($where) ? ' where a.cid IN("") and a.status=1 ' : ' and a.status=1 ';
		$sql_1 = "select cid from sj_channel a " . $where;
		$resinfo = $Model -> query($sql_1);
		$cid_1 = array();
		foreach($resinfo as $info){
			$cid_1[] = $info['cid'];
		}
		$cid_str = 'b.cid in ('.implode(',',$cid_1).')';
		$sql_2 = "select count(DISTINCT b.cid) as counts from activation_hd_state b where " . $cid_str . ' and ' . $b_where;

		$res = $activaDB->query($sql_2);
		$count = $res[0]['counts'];
		$param = http_build_query($_GET);
		foreach($zh_data as $k=>$val){
			$param .= "&".$k."=".urlencode($val);
		}
		$Page = new Page($count, 500, $param);
		$Page->callback = 'return get_params();';
		$sql_1 = 'select b.cid, sum(b.counts) as num from activation_hd_state b  where '.$cid_str.' and '.$b_where.' group by b.cid order by num desc limit '.$Page->firstRow.','.$Page->listRows;
		$info = $activaDB->query($sql_1);
		$c_info = array();
		$category_result = array();
		foreach ($info as $k => $v) {
			$info_cid = $v['cid'];
			$info_sql = "select a.category_id,a.cid,a.chname from sj_channel a where a.cid = ".$info_cid." limit 1";
			$channel_info = $Model -> query($info_sql);

			$category_id = $channel_info[0]['category_id'];
			if (!isset($category_result[$category_id])) $category_result[$category_id] = $category_list[$category_id];
			$v['category_id'] = $category_id;
			$v['chname'] = $channel_info[0]['chname'];
			$category_result[$category_id]['result'][] = $v;

			$cid = $v['cid'];
			$c_info[$cid] = array();
			$sql = "SELECT cid,sum(counts) as num FROM `activation_hd_coefficient_state` where cid={$cid} AND status=1 {$loop_where}";
			$t=$activaDB->query($sql);
			$t = array_pop($t);
			$c_info[$cid]['num'] = $t['num'];
			$state_num += $t['num'];
			$where = " and `create_time` <= {$to_time}";
			$sql = "SELECT cid,coefficient FROM `sj_channel_coefficient` where cid={$v['cid']} {$where} order by id desc limit 1;";
			$t=$Model->query($sql);
			if ($t) {
				//$t = array_pop($t);
				$c_info[$cid]['coefficient'] = $t[0]['coefficient'];
			} else {
				$c_info[$cid]['coefficient'] = 100;
			}
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_COEFFICIENT_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value='{$_SESSION['admin']['admin_id']}' AND target_type='{$target_type}' AND filter_type = 2";
		$res = $Model->query($sql);
		if (empty($res)) {
			$this->assign('show_coefficient',false);
		} else {
			$this->assign('show_coefficient',true);
		}

		$source_type = USER_FILTER_TYPE;
		$target_type = CHANNEL_TOTAL_FILTER_TYPE;
		$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
		$res = $Model->query($sql);
		$sum = false;
		if (empty($res)) {
		//old
			//$sql = "SELECT sum(b.counts) as sum  FROM sj_channel a LEFT JOIN pu_channel_first_state b ON ({$on_where}) {$sum_user_where}";
			$sql = "select sum(b.counts) as sum from activation_hd_state b where ".$cid_str.' and '.$b_where;
			$r=$activaDB->query($sql);
			$sum = $r[0]['sum'];
			$this->assign('showsum',true);
		} else {
			$this->assign('showsum',false);
		}

		$channel_arr = $_REQUEST['cid'];
		foreach($channel_arr as $key => $val){
			$channel_str_go .= $val.',';
		}
		$channel_str = substr($channel_str_go, 0, -1);
		$start_tm = $_REQUEST['fromdate'];
		$end_tm = $_REQUEST['todate'];
		$page = $Page -> show();
		$this -> assign('channel_str',$channel_str);
		$this -> assign('start_tm',$start_tm);
		$this -> assign('end_tm',$end_tm);
		$this -> assign('num',$state_num);
		$this -> assign('sum',$sum);
		$this -> assign('info',$info);
		$this -> assign('category_list',$category_result);
		$this -> assign('c_info',$c_info);
		$this -> assign('to_value',$to_value);
		$this -> assign('from_value',$from_value);
		$this -> assign('page',$page);
		$this -> writelog('查看了激活用户量统计信息', 'sj_admin_filter','',__ACTION__ ,'','view');
		$this -> display('channel_hd');
	}

	//统计管理_渠道用户量统计_new —— 导出列表
	function derive_data_new(){
		ini_set('memory_limit','1024M');
		set_time_limit (600);
		$Model = new Model();
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$start = strtotime($_REQUEST['start_tm']);
		$start_to = date('Ymd 00:00:00',$start);
		$end = strtotime($_REQUEST['end_tm']);
		$end_to = date('Ymd 23:59:59',$end);
		$start_tm = strtotime($start_to);
		$end_tm = strtotime($end_to);
		$channel_arr = explode(',',$_REQUEST['channel_all']);
		if(empty($start_tm)){
			$end_tm = time();
			$start_tm = time() - 7 * 86400;
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;

		if($channel_arr[0] == ''){
			$ch_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$ch_res = $Model->table('sj_admin_filter')->where($ch_map)->find();

			if(!empty($ch_res) && ($ch_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}

			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
		}else{
			$in_cid_array = $channel_arr;
		}

		foreach($in_cid_array as $key => $val){
			$sql = "select sum(counts) from activation_state where cid = $val and pid=1 and status=1 and submit_tm >= $start_tm and submit_tm <= $end_tm";
			$counts_go = $activaDB -> query($sql);

			if($counts_go[0]['sum(counts)'] == null){
				$counts_go[0]['sum(counts)'] = 0;
			}
			$counts_total[$val] = $counts_go[0]['sum(counts)'];
		}
		asort($counts_total);
		$counts_total = array_reverse($counts_total,true);
		foreach($counts_total as $key => $val){
			$channel[] = $key;
		};

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$result_id = $Model -> table('sj_channel') -> where($where) -> select();
			$category_id = $result_id[0]['category_id'];
			$category_arr[] = $category_id;
		}

		foreach($category_arr as $key => $val){
			$where_go['category_id'] = $val;
			$where_go['status'] = 1;
			$result_category = $Model -> table('sj_channel_category') -> where($where_go) -> select();
			if($result_category[0]['name'] == null){
				$result_category[0]['name'] = '未知';
			}
			$category_name[] = $result_category[0]['name'];
		}

		foreach($category_name as $Key => $val){
			$category .= $val.',';
		}

		$errand = ($end_tm - $start_tm)/86400;

		for($i=0;$i<=$errand;$i++){
			$time[$i] = date('Y-m-d',$start_tm + $i*86400);
		}

		foreach($time as $t => $m){
			$result_counts_all = array();
			$start_go = strtotime($m.'00:00:00');
			$end_go = strtotime($m.'23:59:59');
			foreach($channel as $key => $val){
				//$where_counts['_string'] = "cid = $val and status = 1 and pid = 1 and submit_tm >= $start_go and submit_tm <= $end_go";
				$sql_go = "select sum(counts) as counts from activation_state where cid=$val and status = 1 and pid = 1 and submit_tm = $start_go";
				//$result_counts = $Model -> table('activation_state') -> where($where_counts) -> select();
				$result_counts = $activaDB -> query($sql_go);
				$result_counts_all[$key] = $result_counts;
			}
			$counts_all[$m] = $result_counts_all;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				if($v == null){
					$v = 0;
				}
				$val[$k] = $v;
			}
			$counts_all[$key] = $val;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				$result['counts'] = $v[0]['counts'];
				if($v[0]['counts'] == ''){
					$result['counts'] = 0;
				}
				$all[$k] = $result['counts'];
			}
			$result_all[$key]['counts'] = $all;
		}
		$co_group_arr = C('co_group');
		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$where['status'] = 1;
			$result = $Model -> table('sj_channel') -> where($where) -> select();
			$co_group .= $co_group_arr[$result[0]['co_group']].',';
			$channel_go .= $result[0]['chname'].',';
		}

		foreach($result_all as $key => $val){
			foreach($val['counts'] as $v => $k){
				$the_result[$v]['counts'] = $k;
			}
			$all_result[$key] = $the_result;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$result_go[$k] = $v['counts'];
			}
			$all_result[$key] = $result_go;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$file_str .= $v.',';
			}
			$list[$key] = $file_str;
			$file_str="";
		}

		foreach($list as $key => $val){
			$file_arr[] = $key.','.$val;
		}

		foreach($file_arr as $key => $val){
			$file_str_go .= $val."\n";
		}
		foreach($counts_total as $key => $val){
			$total_to .= $val.',';
		}
		$total_go = '合计,'.$total_to;

		$file_go = 'Channel_User'.time();
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition:attachment;filename='.$file_go.'.csv');
		header('Content-Type:APPLICATION/OCTET-STREAM');
		ob_start();
		$header_str =  iconv("UTF-8",'GBK',"日期,".$channel_go."");
		$category_str = iconv("UTF-8",'GBK',"类型,".$category."");
		$co_group_str = iconv("UTF-8",'GBK',"分类,".$co_group."");
		$file_str_go=  iconv("UTF-8",'GBK',$file_str_go);
		$total_str = iconv("UTF-8",'GBK',$total_go);
		echo $co_group_str."\n";
		echo $category_str."\n";
		echo $header_str."\n";
		echo $file_str_go;
		echo $total_str;
		ob_end_flush();
		exit;
	}

	//统计管理_渠道用户量统计_new —— 导出扣量列表
	function derive_data_co_new(){
		set_time_limit(600);
		ini_set('memory_limit','1024M');
		$Model = new Model();
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$start = strtotime($_REQUEST['start_tm']);
		$start_to = date('Ymd 00:00:00',$start);
		$end = strtotime($_REQUEST['end_tm']);
		$end_to = date('Ymd 23:59:59',$end);
		$start_tm = strtotime($start_to);
		$end_tm = strtotime($end_to);
		$channel_arr = explode(',',$_REQUEST['channel_all']);
		if(empty($start_tm)){
			$end_tm = time();
			$start_tm = time() - 7*86400;
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;

		if($channel_arr[0] == ''){
			$ch_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$ch_res = $Model->table('sj_admin_filter')->where($ch_map)->find();

			if(!empty($ch_res) && ($ch_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}

			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
		}else{
			$in_cid_array = $channel_arr;
		}

		foreach($in_cid_array as $key => $val){
			$sql = "select sum(counts) from activation_coefficient_state where cid = $val and status=1 and submit_tm >= $start_tm and submit_tm <= $end_tm";
			$counts_go = $activaDB -> query($sql);

			if($counts_go[0]['sum(counts)'] == null){
				$counts_go[0]['sum(counts)'] = 0;
			}
			$counts_total[$val] = $counts_go[0]['sum(counts)'];
		}
		asort($counts_total);
		$counts_total = array_reverse($counts_total,true);
		foreach($counts_total as $key => $val){
			$channel[] = $key;
		};

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$result_id = $Model -> table('sj_channel') -> where($where) -> select();
			$category_id = $result_id[0]['category_id'];
			$category_arr[] = $category_id;
		}

		foreach($category_arr as $key => $val){
			$where_go['category_id'] = $val;
			$where_go['status'] = 1;
			$result_category = $Model -> table('sj_channel_category') -> where($where_go) ->field('name')-> find();
			if($result_category['name'] == null){
				$result_category['name'] = '未知';
			}
			$category_name[] = $result_category['name'];
		}

		foreach($category_name as $Key => $val){
			$category .= $val.',';
		}

		$errand = ($end_tm - $start_tm)/86400;

		for($i=0;$i<=$errand;$i++){
			$time[$i] = date('Y-m-d',$start_tm + $i*86400);
		}

		foreach($time as $t => $m){
			$start_go = strtotime($m.'00:00:00');
			$end_go = strtotime($m.'23:59:59');
			foreach($channel as $key => $val){
				//$where_counts['_string'] = "cid = $val and status = 1 and pid = 1 and submit_tm >= $start_go and submit_tm <= $end_go";
				$sql_go = "select * from activation_coefficient_state where cid = $val and status = 1 and submit_tm = $start_go";
				//$result_counts = $Model -> table('activation_state') -> where($where_counts) -> select();
				$result_counts = $activaDB -> query($sql_go);
				$result_counts_all[$key] = $result_counts;
			}
			$counts_all[$m] = $result_counts_all;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				if($v == null){
					$v = 0;
				}
				$val[$k] = $v;
			}
			$counts_all[$key] = $val;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				$result['counts'] = $v[0]['counts'];
				if($v[0]['counts'] == ''){
					$result['counts'] = 0;
				}
				$all[$k] = $result['counts'];
			}
			$result_all[$key]['counts'] = $all;
		}
		$co_group_arr = C('co_group');
		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$where['status'] = 1;
			$result = $Model -> table('sj_channel') -> where($where) -> select();
			$channel_go .= $result[0]['chname'].',';
			$co_group .= $co_group_arr[$result[0]['co_group']].',';
		}

		foreach($result_all as $key => $val){
			foreach($val['counts'] as $v => $k){
				$the_result[$v]['counts'] = $k;
			}
			$all_result[$key] = $the_result;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$result_go[$k] = $v['counts'];
			}
			$all_result[$key] = $result_go;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$file_str .= $v.',';
			}
			$list[$key] = $file_str;
			$file_str="";
		}

		foreach($list as $key => $val){
			$file_arr[] = $key.','.$val;
		}

		foreach($file_arr as $key => $val){
			$file_str_go .= $val."\n";
		}
		foreach($counts_total as $key => $val){
			$total_to .= $val.',';
		}
		$total_go = '合计,'.$total_to;

		$file_go = 'Channel_User'.time();
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition:attachment;filename='.$file_go.'.csv');
		header('Content-Type:APPLICATION/OCTET-STREAM');
		ob_start();
		$header_str =  iconv("UTF-8",'GBK',"日期,".$channel_go."");
		$category_str = iconv("UTF-8",'GBK',"类型,".$category."");
		$co_group_str = iconv("UTF-8",'GBK',"分类,".$co_group."");
		$file_str_go=  iconv("UTF-8",'GBK',$file_str_go);
		$total_str = iconv("UTF-8",'GBK',$total_go);
		echo $co_group_str."\n";
		echo $category_str."\n";
		echo $header_str."\n";
		echo $file_str_go;
		echo $total_str;
		ob_end_flush();
		exit;
	}

	//统计管理_游戏渠道用户量统计 —— 导出列表
	function derive_data_game(){
		$Model = new Model();
		$activaDB = D('Sj.GoStats');
		//$activaDB -> getConnection();
		$start = strtotime($_REQUEST['start_tm']);
		$start_to = date('Ymd 00:00:00',$start);
		$end = strtotime($_REQUEST['end_tm']);
		$end_to = date('Ymd 23:59:59',$end);
		$start_tm = strtotime($start_to);
		$end_tm = strtotime($end_to);
		$channel_arr = explode(',',$_REQUEST['channel_all']);
		if(empty($start_tm)){
			$end_tm = time();
			$start_tm = time() - 7*86400;
		}
		$source_type = USER_FILTER_TYPE;
		$target_type= CHANNEL_SHOW_CONTROL;
		//$target_type = CHANNEL_FILTER_TYPE;

		if($channel_arr[0] == ''){
			$ch_map = array(
				'source_type' => $source_type,
				'source_value' => $_SESSION['admin']['admin_id'],
				'target_type' => $target_type
			);
			$ch_res = $Model->table('sj_admin_filter')->where($ch_map)->find();

			if(!empty($ch_res) && ($ch_res['filter_type'])==1){
				$res = $Model->table("sj_channel")->where(array("status"=>1))->select();
			}else{
				$source_type = USER_FILTER_TYPE;
				$target_type = CHANNEL_FILTER_TYPE;
				$sql = "select target_value as cid from sj_admin_filter where source_type='{$source_type}' and source_value={$_SESSION['admin']['admin_id']} AND target_type='{$target_type}'";
				$res = $Model->query($sql);
			}

			foreach ($res as $item) {
				$in_cid_array[] = $item['cid'];
			}
		}else{
			$in_cid_array = $channel_arr;
		}

		foreach($in_cid_array as $key => $val){
			$sql = "select sum(counts) from activation_game_state where cid = $val and submit_tm >= $start_tm and submit_tm <= $end_tm and status=1";
			$counts_go = $activaDB -> query($sql);

			if($counts_go[0]['sum(counts)'] == null){
				$counts_go[0]['sum(counts)'] = 0;
			}
			$counts_total[$val] = $counts_go[0]['sum(counts)'];
		}
		asort($counts_total);
		$counts_total = array_reverse($counts_total,true);
		foreach($counts_total as $key => $val){
			$channel[] = $key;
		};

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$result_id = $Model -> table('sj_channel') -> where($where) -> select();
			$category_id = $result_id[0]['category_id'];
			$category_arr[] = $category_id;
		}

		foreach($category_arr as $key => $val){
			$where_go['category_id'] = $val;
			$where_go['status'] = 1;
			$result_category = $Model -> table('sj_channel_category') -> where($where_go) -> select();
			if($result_category[0]['name'] == null){
				$result_category[0]['name'] = '未知';
			}
			$category_name[] = $result_category[0]['name'];
		}

		foreach($category_name as $Key => $val){
			$category .= $val.',';
		}

		$errand = ($end_tm - $start_tm) / 86400;

		for($i=0;$i<=$errand;$i++){
			$time[$i] = date('Y-m-d', $start_tm + $i*86400);
		}

		foreach($time as $t => $m){
			$start_go = strtotime($m . '00:00:00');
			$end_go = strtotime($m . '23:59:59');
			foreach($channel as $key => $val){
				//$where_counts['_string'] = "cid = $val and status = 1 and pid = 1 and submit_tm >= $start_go and submit_tm <= $end_go";
				$sql_go = "select * from activation_game_state where cid = $val and status = 1 and pid = 5 and submit_tm >= $start_go and submit_tm <= $end_go";
				//$result_counts = $Model -> table('activation_state') -> where($where_counts) -> select();
				$result_counts = $activaDB -> query($sql_go);
				$result_counts_all[$key] = $result_counts;
			}

		$counts_all[$m] = $result_counts_all;

	}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				if($v == null){
					$v = 0;
				}
				$val[$k] = $v;
			}
			$counts_all[$key] = $val;
		}

		foreach($counts_all as $key => $val){
			foreach($val as $k => $v){
				$result['counts'] = $v[0]['counts'];
				if($v[0]['counts'] == ''){
					$result['counts'] = 0;
				}
				$all[$k] = $result['counts'];
			}
			$result_all[$key]['counts'] = $all;
		}

		foreach($channel as $key => $val){
			$where['cid'] = $val;
			$where['status'] = 1;
			$result = $Model -> table('sj_channel') -> where($where) -> select();
			$channel_go .= $result[0]['chname'].',';
		}

		foreach($result_all as $key => $val){
			foreach($val['counts'] as $v => $k){
				$the_result[$v]['counts'] = $k;
			}
			$all_result[$key] = $the_result;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$result_go[$k] = $v['counts'];
			}
			$all_result[$key] = $result_go;
		}

		foreach($all_result as $key => $val){
			foreach($val as $k => $v){
				$file_str .= $v.',';
			}
			$list[$key] = $file_str;
			$file_str="";
		}

		foreach($list as $key => $val){
			$file_arr[] = $key.','.$val;
		}

		foreach($file_arr as $key => $val){
			$file_str_go .= $val."\n";
		}
		foreach($counts_total as $key => $val){
			$total_to .= $val.',';
		}
		$total_go = '合计,'.$total_to;

		$file_go = 'Channel_User'.time();
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition:attachment;filename='.$file_go.'.csv');
		header('Content-Type:APPLICATION/OCTET-STREAM');
		ob_start();
		$header_str =  iconv("UTF-8",'GBK',"日期,".$channel_go."");
		$category_str = iconv("UTF-8",'GBK',"类别,".$category."");
		$file_str_go=  iconv("UTF-8",'GBK',$file_str_go);
		$total_str = iconv("UTF-8",'GBK',$total_go);
		echo $category_str."\n";
		echo $header_str."\n";
		echo $file_str_go;
		echo $total_str;
		ob_end_flush();
		exit;
	}

	//统计管理_渠道下载量统计_new
	function channelDownloadStatic_new() {
		$time = $this -> gettimeArr_hui('channelDownloadStatic');
		//print_r($time);exit;
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		//获取渠道cid
		// $chname = $_GET['chname'];
		$ch_obj = M('channel');
		$channel_info=array();
		import("@.ORG.Page");
		if (!empty($_REQUEST['cid'])) {
			if(is_array($_REQUEST['cid'])){
				$zh_data['cid']=implode(",",$_REQUEST['cid']);
			}else{
				$zh_data['cid']=$_REQUEST['cid'];
				$_REQUEST['cid']=explode(",",$zh_data['cid']);
			}
			$this->assign("zh_cid_arra",$zh_data['cid']);
			$zh_cids=array();
			foreach($_REQUEST['cid'] as $m=>$n){
				if($n!=0){
					$zh_cids[$m]['cid']=$n;
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$ch_obj->where($c_where)->getfield("chname");
					$zh_cids[$m]['chname']=$zh_chname;
				}

			}
			$this->assign("channel_selected",$zh_cids);
			$channel_info=$_REQUEST['cid'];
			$where = empty($channel_info) ? '': 'chl in ('.implode(',',$channel_info).') and ';
			//$not_in_string = 'a.cid IN('. implode(',',$_REQUEST['cid']).')';
			$channel_array=array();
			foreach($channel_info as $m=>$n){
				if($n!=0){
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$ch_obj->where($c_where)->getfield("chname");
					$channel_array[$n]=$zh_chname;
				}
			}
			$mObj = D('Sj.GoStats');
			//$mObj -> getConnection();
			//$where = "chl in (".implode(',',$channel_info).") and ";
			$date = "";
			foreach($time as $d){
				$date[] = strtotime($d);
			}
			$datewhere = "";
			if(count($date) > 0){
				$datewhere = " date between ".strtotime($from_value." 00:00:00")." and ".strtotime($to_value." 23:59:59")." ";
			}

			$count_sql="select count(*) as num from (select sum(apps) as appsum, sum(games) as gamesum,sum(activates) as actsum,chl from mobile_chl_soft_stat  where ".$where." ". $datewhere." group by chl) as zhang";
			$search_count=$mObj->query($count_sql);
			$count=$search_count[0]['num'];
			$param = http_build_query($_GET);
			foreach($zh_data as $k=>$val){
				$param .= "&".$k."=".urlencode($val);
			}
			$Page = new Page($count, 20, $param);
			$Page->callback = 'return get_params();';

			$result = $mObj -> query("
			select sum(apps) as appsum, sum(games) as gamesum,sum(activates) as actsum,chl from mobile_chl_soft_stat  where ".$where." ". $datewhere." group by chl limit ".$Page->firstRow.','.$Page->listRows);
			foreach($result as $idx => $info){
				$result[$idx]['sum'] = $info['appsum'] + $info['gamesum'];
				$result[$idx]['chname'] = $channel_array[$info['chl']];
			}
			$page = $Page->show();
			$this->assign('page',$page);
		}
		$this -> assign('chname' ,$chname);
		$this -> assign('result' ,$result);
		$this -> assign('to_value',$to_value);
		$this -> assign('from_value',$from_value);
		$this -> display('channelDownloadStatic_new');
		//统计活跃量
	}

	//统计管理_游戏渠道下载量统计
	function channelDownloadStatic_game() {
		$time = $this -> gettimeArr_hui('channelDownloadStatic');
		//print_r($time);exit;
		$from_value = $time[0];
		$to_value = $time[count($time)-1];
		//获取渠道cid
		// $chname = $_GET['chname'];
		$ch_obj = M('channel');
		$channel_info=array();
		import("@.ORG.Page");
		if (!empty($_REQUEST['cid'])) {
			if(is_array($_REQUEST['cid'])){
				$zh_data['cid']=implode(",",$_REQUEST['cid']);
			}else{
				$zh_data['cid']=$_REQUEST['cid'];
				$_REQUEST['cid']=explode(",",$zh_data['cid']);
			}
			$this->assign("zh_cid_arra",$zh_data['cid']);
			$zh_cids=array();
			foreach($_REQUEST['cid'] as $m=>$n){
				if($n!=0){
					$zh_cids[$m]['cid']=$n;
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$ch_obj->where($c_where)->getfield("chname");
					$zh_cids[$m]['chname']=$zh_chname;
				}
			}
			$this->assign("channel_selected",$zh_cids);
			$channel_info=$_REQUEST['cid'];
			$where = empty($channel_info) ? '': 'chl in ('.implode(',',$channel_info).') and ';
			//$not_in_string = 'a.cid IN('. implode(',',$_REQUEST['cid']).')';
			$channel_array=array();
			foreach($channel_info as $m=>$n){
				if($n!=0){
					$c_where['status']=1;
					$c_where['cid']=$n;
					$zh_chname=$ch_obj->where($c_where)->getfield("chname");
					$channel_array[$n]=$zh_chname;
				}
			}
			$mObj = D('Sj.GoStats');
			//$mObj -> getConnection();
			//$where = "chl in (".implode(',',$channel_info).") and ";
			$date = "";
			foreach($time as $d){
				$date[] = strtotime($d);
			}
			$datewhere = "";
			if(count($date) > 0){
				$datewhere = " date between ".strtotime($from_value." 00:00:00")." and ".strtotime($to_value." 23:59:59")." ";
			}

			$count_sql="select count(*) as num from (select sum(apps) as appsum, sum(games) as gamesum,sum(activates) as actsum,chl from mobile_chl_game_soft_stat  where ".$where." ". $datewhere." group by chl) as zhang";
			$search_count=$mObj->query($count_sql);
			$count=$search_count[0]['num'];
			$param = http_build_query($_GET);
			foreach($zh_data as $k=>$val){
				$param .= "&".$k."=".urlencode($val);
			}
			$Page = new Page($count, 20, $param);
			$Page->callback = 'return get_params();';



			$result = $mObj -> query("
			select sum(apps) as appsum, sum(games) as gamesum,sum(activates) as actsum,chl from mobile_chl_game_soft_stat  where ".$where." ". $datewhere." group by chl limit ".$Page->firstRow.','.$Page->listRows);
			foreach($result as $idx => $info){
				$result[$idx]['sum'] = $info['appsum'] + $info['gamesum'];
				$result[$idx]['chname'] = $channel_array[$info['chl']];
			}
			$page = $Page->show();
			$this->assign('page',$page);
		}
		$this -> assign('chname' ,$chname);
		$this -> assign('result' ,$result);
		$this -> assign('to_value',$to_value);
		$this -> assign('from_value',$from_value);
		$this -> display('channelDownloadStatic_game');
		//统计活跃量
	}

	//统计管理_时间段函数
	function gettimeArr_hui($url){
		$time = array();
		if (array_key_exists('fromdate', $_REQUEST)&&array_key_exists('todate', $_REQUEST)){
			$fromdate = $_REQUEST['fromdate'];
			$todate = $_REQUEST['todate'];
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
				$len = $chatime/86400;
				for($i=0;$i<=$len;$i++){
					$time[$i] = date('Y-m-d',$fromdatetime + $i * 86400);
				}
		}else{
			$todate = date('Y-m-d',time());
			$tovaluetime = strtotime($todate);
			for($i=7;$i>=0;$i--){
				$time[7-$i] = date('Y-m-d',$tovaluetime - $i * 86400);
			}
		}
		return $time;
	}
	//推广链接下载统计查看详细  庄超滨 2012.10.11
	function View_details(){
		$model = new Model();
		$id = $_GET['id'];
		$start_time = strtotime($_GET['fromdate']);
		$end_time = strtotime($_GET['todate']. ' 23:59:59');
		$start_date = date("Y-m-d",$start_time);
		$end_date = date("Y-m-d",$end_time);
		$where .= "time_log >={$start_time} and time_log<={$end_time}";
		$where .=" and puid={$id}";
		$popular_list = $model -> table('pu_puid_log') ->field('id,puid,downnum,time_log')->where($where) -> order('time_log desc')-> select();
		 //echo $model->getlastSql($popular_list);
		$populars = array();
		foreach ($popular_list as $info){
			$pu_name = $model -> table('pu_popularparter') -> where('id ='.$info[puid]) -> getField('pu_name');
			$time_key = date("Y-m-d",$info['time_log']);
			$populars[$time_key]['downnum'] +=$info['downnum'];
			$populars[$time_key]['time_log']= $time_key;
			$populars[$time_key]['pu_name']=$pu_name;
			$populars[$time_key]['puid'] = $info['puid'];
		}
		$this -> assign('activate_list',$populars);
		$this -> display();
	}
	//推广链接下载统计显示  庄超滨 2012.10.11
	  function extension(){
		$model = new Model();
		import("@.ORG.Page");
		if($_GET['fromdate']&&$_GET['todate']){
			if($_GET['pu_name']){
				$pu_name = $_GET['pu_name'];
				$where['pu_name'] = array("like","%".trim($_GET['pu_name'])."%");
			}
			if(empty($_GET['fromdate'])&&empty($_GET['todate'])){
				$this->error("请选择时间");
			}else{
				$start_time = strtotime( $_GET['fromdate']);
				$end_time = strtotime($_GET['todate']. ' 23:59:59');
				$start_date = date("Y-m-d",$start_time);
				$end_date = date("Y-m-d",$end_time);
				if($start_time > time()){
				$this->assign('jumpUrl','/index.php/extension/'.$url);
				$this->error('开始时间超出当前时间,请重新选择');
				}
				if($end_time < $start_time){
					$this->assign('jumpUrl','/index.php/extension/'.$url);
					$this->error('截止时间小于开始时间,请重新选择');
				}
				$where1 .= "time_log >={$start_time} and time_log<={$end_time}";
			}
		}else{
			$end_date=date("Y-m-d",time());
			$end_time=strtotime($end_date." 23:59:59");
			$start_date=date('Y-m-d', time()-24*3600*7);
			$start_time=strtotime($start_date." 00:00:00");
			$where1 .= "time_log >={$start_time} and time_log<={$end_time}";
		}
		$count = $model->table('pu_popularparter') -> where($where) -> count();
		$p = new page($count,50);
		$popul_list = $model -> table('pu_popularparter')  -> where($where) ->limit($p -> firstRow.','.$p -> listRows)-> field('id,pu_name') ->order('id desc')  -> select();
		foreach ($popul_list as $key=>$v){
			$activate_list1 = $model ->query("select id,puid,sum(downnum) as zong from pu_puid_log where puid={$v[id]} and {$where1} group by puid order by id desc");
			//echo $model->getlastSql($activate_list1);
			$popul_list[$key]['zong']=$activate_list1[0]['zong']?$activate_list1[0]['zong']:0;
		}
		$page = $p->show();
		$this -> assign('page',$page);
		//print_r($popul_list);
		$this -> assign('activate_list',$popul_list);
		$this -> assign('pu_name',$pu_name);
		$this -> assign('start_date',$start_date);
		$this -> assign('end_date',$end_date);
		$this ->display();

	}
	function pub_down_top10(){
		$model = D("Sj.DownloadNew");		
		$filed = $_GET['filed'];
		$where = array(
			'submit_day' => $_GET['date'],
		);
		$soft_list = $model->table('sj_download_count')->where($where)->field("package,{$filed}")->order("{$filed} desc")->limit(10)->select();
		$package = array();
		foreach($soft_list as $v){
			$package[] = $v['package'];
		}
		$where = array(
			'package'=>array('in',$package),
			'status'=> 1
		);
		$soft_info = get_table_data($where,"sj_soft","package","package,softname");		
		$this -> assign('soft_list',$soft_list);
		$this -> assign('soft_info',$soft_info);
		$this -> assign('filed',$filed);
		$this -> assign('date',date("Y-m-d",$_GET['date']));
		$this ->display('down_top10');
	}

	function get_bi_data($package, $start, $end)
	{
		$params = array(
			'filter' => array(
				'THEDAY' => array(
					'start' => $start,
					'end' => $end,
				),
				'NAME' => $package
			),
			'column' => array(
				'THEDAY' => 1,
				'NAME' => 1,
				'DOWNLOADTIMESPV' => 1,
                'DOWNLOADTIMESUV' => 1,
			),
			'order' => array(
				1 => 'THEDAY',
			),
			'sort' => array(
				1 => 'asc',
			),
		);
		$params = http_build_query($params);
		return json_decode(getDataFromBi($params), true);
	}

	//增量日志
	function soft_download_add_log(){
		$model = D("Sj.DownloadNew");
		$channel_id =  C('soft_dowload_add_channel');
		if($_POST){

			if(empty($_POST['submit_day'])){
				$this->error('日期不能为空');
			}
			if(empty($_POST['chl_cid'])){
				$this->error('未选择渠道');
			}
			$submit_day = strtotime($_POST['submit_day']);
			$res = $model->table('sj_download_add_log')->where(array('package'=>'.flag','submit_day'=>$submit_day,'chl_cid'=>$_POST['chl_cid']))->find();
			if($res){
				$this->error('该日期本渠道已经添加数据了');
			}

			if($_FILES['upload_file']){
				$filename=$_FILES['upload_file']['tmp_name'];
				$err = $_FILES["upload_file"]["error"];
				$file_name_csv=$_FILES['upload_file']['name'];
				if(empty($filename)){
					$this->error('未上传文件');
				}
				$tmp_arr = explode(".", $file_name_csv);
				$name_suffix = array_pop($tmp_arr);
				if (strtoupper($name_suffix) != "CSV")
				{
					$this->error('请上传csv文件');
				}
				$fp=fopen($filename,'r');
				$n = 1;
				$all_pack = array();
				$line = 0;
				while (!feof($fp))
				{
					$out[$n]=fgets($fp);
					$out[$n]=str_replace(array("\n","\r"),"",$out[$n]);//去掉换行符
					if($out[$n]){
						$data = explode(',',$out[$n]);
						if ($data['0'] == '包名') continue;
						if(empty($data[0])||empty($data[1])){
							$this->error('csv文件中存在空数据');
						}
						if($line!=0)
						$all_pack[] = array('package'=>$data[0],'num'=>$data[1]);
					}
					$line++;
				}
				if(count($all_pack)<0){
					$this->error('csv中无数据');
				}
				$all_pack[] = array('package'=> '.flag','num'=>0);
				foreach($all_pack as $k=>$v){
					$i_data = array(
						'package' => $v['package'],
							'submit_day' => $submit_day,
							'chl_cid' => $_POST['chl_cid'],
							'add_cnt' => $v['num'],
							'create_at' => time()
					);
					$res = $model->table('sj_download_add_log')->add($i_data);
					$this->writelog("CPD数据录入:增加了id为{$res}的渠道下载量", 'sj_download_add_log', $res,__ACTION__ ,'','add');
				}
				$this->success('添加成功');
			}
		}
		$this->assign('channel_id',$channel_id);
		$this->display();
	}

	//CPD投放软件下载量录入
	function import_soft_download(){
		$model = M('');
		$channel_id =  C('cpd_dowload_channel');
		if($_POST){
			$table = 'cpd_soft_download';
			if($_FILES['upload_file']){
				$filename=$_FILES['upload_file']['tmp_name'];
				$err = $_FILES["upload_file"]["error"];
				$file_name_csv=$_FILES['upload_file']['name'];
				if(empty($filename)){
					$this->error('未上传文件');
				}
				$tmp_arr = explode(".", $file_name_csv);
				$name_suffix = array_pop($tmp_arr);
				if (strtoupper($name_suffix) != "CSV")
				{
					$this->error('请上传csv文件');
				}
				$fp=fopen($filename,'r');
				$n = 1;
				$all_pack = array();
				$line = 0;
				$column_num = 6;
				while (!feof($fp))
				{

					$data = fgetcsv($fp);
					foreach ($data as $k => $v) {
						$data[$k] = str_replace(',', '', $v);
					}
					if ($line==0){
						$line ++;
						continue;
					}
					if(!$data) continue;
					if(empty($data[0])){
						$this->error('日期不能为空');
					}

					if (!preg_match("/^\d{4}(\-|\/|\.)\d{1,2}(\-|\/|\.)\d{1,2}$/", $data[0])) {
						$this->error('日期格式错误');
					}
					$data[0] = strtotime($data[0]);
					if(empty($data[1])){
						$this->error('包名不能为空');
					}
					if($data[2]<0){
						$this->error('下载量为正整数');
					}
					if($line!=0)
						$all_pack[] = array('submit_day'=>$data[0],'package'=>$data[1],'num'=>$data[2],'price'=>$data[3],'expend_sum'=>$data[4],'prestore'=>$data[5]);
					$line++;
				}
				if(count($all_pack)<0){
					$this->error('csv中无数据');
				}
				foreach($all_pack as $k=>$v){
					$where = array(
						'submit_day' => $v['submit_day'],
						'package' => $v['package']
					);

					$model->table($table)->where($where)->save(array('status'=>0));
					$i_data = array(
						'package' => $v['package'],
						'submit_day' => $v['submit_day'],
						'add_cnt' => $v['num'],
						'price' => $v['price']*100,
						'expend_sum' => $v['expend_sum']*100,
						'prestore' => $v['prestore']*100,
						'create_at' => time()
					);

					$res = $model->table($table)->add($i_data);
					$this->writelog("CPD软件下载量录入:增加了id为{$res}的渠道下载量", $table, $res,__ACTION__ ,'','add');
				}
				$this->success('添加成功');
			}
		}
		$this->assign('channel_id',$channel_id);
		$this->display();
	}

	//CPD投放数据录入
	function import_soft_adinfo(){
		if($_POST){
			$model = M('');

			if($_FILES['upload_file']){
				$filename=$_FILES['upload_file']['tmp_name'];
				$err = $_FILES["upload_file"]["error"];
				$file_name_csv=$_FILES['upload_file']['name'];
				if(empty($filename)){
					$this->error('未上传文件');
				}
				$tmp_arr = explode(".", $file_name_csv);
				$name_suffix = array_pop($tmp_arr);
				if (strtoupper($name_suffix) != "CSV")
				{
					$this->error('请上传csv文件');
				}
				$fp=fopen($filename,'r');
				$n = 1;
				$all_pack = array();
				$line = 0;
				$field_num = 6;
				$user_name = $package = array();

				while (!feof($fp))
				{
					$out[$n]=fgets($fp);
					$out[$n]=str_replace(array("\n","\r"),"",$out[$n]);//去掉换行符
					if($out[$n]){
						$data = explode(',',$out[$n]);
						if ($data['0'] == '包名') continue;
						for($i=0;$i<$field_num;$i++){
							if(empty($data[$i])){
								$this->error('csv文件中存在空数据');
							}
							if(mb_check_encoding($data[$i],"utf-8") != true){
								$data[$i] = iconv("gbk","utf-8", $data[$i]);
							}
						}

						if($line!=0){
							$all_pack[] = array('package'=>$data[0],'throw_status'=>$data[1],'area'=>$data[2],'time_frame'=>$data[3],'custom_level'=>$data[4],'username'=>$data[5]);
							$user_name[] = $data[5];
							$package[] = $data[0];
						}

					}
					$line++;
				}

				if(count($all_pack)<0){
					$this->error('csv中无数据');
				}
				$user_name_str = implode("','",$user_name);
				$users = $model->table('cpd_statistics_user')->where("name in('{$user_name_str}')")->field('id,name')->select();
				$has_users = $user_id = array();
				foreach($users as $k=>$v){
					$has_users[] = $v['name'];
					$user_id[$v['name']] = $v['id'];
				}
				$no_user = array_diff($user_name, $has_users);
				if($no_user){
					$msg = implode(',',$no_user);
					$this->error('不存在用户名:'.$msg);
				}
				$package_str = implode("','",$package);

				$package_info = $model->table('sj_soft')->where("package in('{$package_str}') and hide = 1 and status = 1")->field('softid,softname,package')->select();
				$has_soft = $soft = array();
				foreach($package_info as $k1=>$v1){
					$has_soft[] = $v1['package'];
					$soft[$v1['package']] = $v1['softname'];
				}
				$no_soft = array_diff($package, $has_soft);
				if($no_soft){
					$msg = implode(',',$no_soft);
					$this->error('不存在包名:'.$msg);
				}
				$time = time();
				foreach($all_pack as $k=>$v){
					$i_data = array(
						'package' => $v['package'],
//						'price' => $v['price'],
						'throw_status' => $v['throw_status'],
						'area' => $v['area'],
						'time_frame' => $v['time_frame'],
						'custom_level' => $v['custom_level'],
//						'prestore' => $v['prestore'],
						'add_tm' => $time,
						'user_id' => $user_id[$v['username']],
						'softname' => $soft[$v['package']]
					);
					$res = $model->table('cpd_soft_adinfo')->add($i_data);
					$this->writelog("CPD投放数据录入:增加了id为{$res}的投放数据", 'cpd_soft_adinfo', $res,__ACTION__ ,'','add');
				}
				$this->success('添加成功');
			}
			exit();
		}
		$this->display();
	}

}
?>
