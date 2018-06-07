<?php
/**
 * 数据统计
 * date:2013-12-13
 *
*/
class DataCountAction extends CommonAction {
	public function data_count_list($from){
		$model = new Model();
		$now = strtotime(date('Y-m-d',time()));
		$this -> assign('currentdate',date('Y-m-d',time()));		
		//开发者审核
		//*****************本周数据***********************************/
		$dev_week_list = $model->table('sj_data_count_dev')->where("times ={$now}")->order('type asc')->select();
		//新增日期区间搜索
		if($_GET['start_tm'] && $_GET['end_tm']){
			$start_tm=strtotime($_GET['start_tm'])?strtotime($_GET['start_tm']):strtotime($start_time);
			$end_tm=strtotime($_GET['end_tm'])?strtotime($_GET['end_tm']):strtotime($end_time);
			$this -> assign('start_tm',$_GET['start_tm']);
			$this -> assign('end_tm',$_GET['end_tm']);
		}else{
			$start_tm=$now-86400;
			$end_tm=$now;
		}
		if($start_tm < $now){
			$start_tm = $start_tm + 86400;
		}

		if($end_tm < $now){
			$end_tm = $end_tm + 86400;
		}
		$dev_add_list_start = $model->table('sj_data_count_dev')->where("times ={$start_tm}")->select();
		$dev_add_list_end = $model->table('sj_data_count_dev')->where("times ={$end_tm}")->select();
		$dev_add_list=array();
		foreach($dev_add_list_end as $k=>$v){
			if($dev_add_list_start){
				foreach($dev_add_list_start as $vv){
					if($vv['type']==$v['type']){
						$dev_add_list[$k]['type']=$v['type'];
						$dev_add_list[$k]['add_data']=$v['add_data']-$vv['add_data'];
					}
				}
			}else{
				$dev_add_list[$k]['type']=$v['type'];
				$dev_add_list[$k]['add_data']=$v['add_data']-0;
			}
			
		}
		foreach($dev_week_list as $k => $v){
			if($dev_add_list){
				foreach($dev_add_list as $kk=>$vv){
					if($v['type']==$vv['type']){
						$dev_week_list[$k]['cycle_add']=$vv['add_data'];
					}
				}
			}else{
				$dev_week_list[$k]['cycle_add']=0;
			}
			
		}
		//var_dump($dev_week_list);
		$data_week_dev = array();
		foreach($dev_week_list as $k => $v){
			$data_week_dev[$v['type']] = $v;
		}

		 //echo "<pre>";var_dump($data_week_dev);die;
		//==============================================================
		//APP审核--前后台标识
		//本周数据
		$this_week_list = $model->table('sj_data_count_app')->where("times ={$now}")->select();
		$data_week = array();
		foreach($this_week_list as $k => $v){
			$data_week[$v['type']] = $v;
		}
		

		//开始日期数据
		$last_week_list = $model->table('sj_data_count_app')->where("times ={$end_tm}")->select();
		$last_data = array();
		foreach($last_week_list as $k => $v){
			$last_data[$v['type']] = $v;
		}
		//结束日期数据
		$last_last_week_list = $model->table('sj_data_count_app')->where("times ={$start_tm}")->select();
		$last_last_data = array();
		foreach($last_last_week_list as $k => $v){
			$last_last_data[$v['type']] = $v;
		}
		$data_list = array();
		foreach($data_week as $key => $val){
			//当前通过总量
			$data_list[$key]['counts']  = $val['game_week_add']+$val['app_week_add']+$val['book_week_add'];
			//当前游戏通过总量
			$data_list[$key]['game_total']  = $val['game_week_add'];
			//当前应用通过总量
			$data_list[$key]['app_total']  = $val['app_week_add'];
			//当前电子书通过总量
			$data_list[$key]['book_total']  = $val['book_week_add'];
			//本周游戏新增
			$data_list[$key]['game_week']  = $last_data[$key]['game_week_add']-$last_last_data[$key]['game_week_add'];
			//本周应用新增
			$data_list[$key]['app_week']  = $last_data[$key]['app_week_add']-$last_last_data[$key]['app_week_add'];
			//本周电子书新增
			$data_list[$key]['book_week']  = $last_data[$key]['book_week_add']-$last_last_data[$key]['book_week_add'];
			$last_add_week = '0';
			$last_add_week = ($last_data[$key]['game_week_add']-$last_last_data[$key]['game_week_add'])+($last_data[$key]['app_week_add']-$last_last_data[$key]['app_week_add'])+($last_data[$key]['book_week_add']-$last_last_data[$key]['book_week_add']);
			$data_list[$key]['last_add_week']  = $last_add_week;
			
		}

		// echo "<pre>";var_dump($data_list);die;
		$type = array(
			'1'=>"新增",
			'2'=>"升级",
			'3'=>"下架申请",
			'4'=>"软件采集",
			'5'=>"官方",
			'6'=>"安全",
			'7'=>"广告",
			'8'=>"屏蔽",
			'9'=>"山寨",
			'10'=>"不安全",
			'11'=>"首发",
			'12'=>"闪屏",
			'13'=>"汉化",
			'14'=>"推荐",
			'15'=>"新服",
			'16'=>"盗版",
		);
		$dev_type =  array(
			'1'=>"开发者",
			'2'=>"开发者屏蔽",
			'3'=>"邮箱提交",
			'4'=>"邮箱激活",
			'5'=>"手机提交",
			'6'=>"手机激活",
			'7'=>"公司",
			'8'=>"个人",
			'9'=>"大陆",
			'10'=>"港澳台及海外"
		);
		$this -> assign('type',$type);
		$this -> assign('dev_type',$dev_type);
		ksort($data_list);		
		ksort($data_week_dev);		
		$this -> assign('data_list',$data_list);
		$this -> assign('data_dev_list',$data_week_dev);
		if($from == 'exp'){
			return array($type,$data_list,$dev_type,$data_week_dev);
		}else{
			$this->display();
		}
	}
	public function convertUTF8($str){
		if(empty($str)) return '';
		if(mb_check_encoding($str,"utf-8") != true){
			return iconv("gbk","utf-8", $str);
		}else{
			return $str;
		}
	}	
	function datacount_export(){//导出Excel
		list($type,$data_list,$dev_type,$data_dev_list) = $this->data_count_list('exp');
		
		$xlsCell  = array(
			array('type',''),
			array('counts','当前总量'),
			array('game_total','游戏总量'),
			array('app_total','应用总量'),
			array('book_total','电子书总量'),
			array('last_add_week','所选周期总新增'),
			array('game_week','所选周期游戏新增'),
			array('app_week','所选周期应用新增'),
			array('book_week','所选周期电子书新增'),
		);
		$dev_xlscell = array(
			array('dev_type',''),
			array('add_data','当前总量'),
			array('cycle_add','所选周期新增'),
		);
		$xlsName = '数据统计'.date("Y-m-d",time());
		import('@.ORG.PHPExcel.PHPExcel');
		$objPHPExcel = new PHPExcel();
		$sheet1 = $objPHPExcel->createSheet();
		$sheet1 = $objPHPExcel->createSheet();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
		//开发者审核
		$dev_data = array();
		foreach($data_dev_list as $k => $v){
			$v['dev_type'] = $dev_type[$k];
			$dev_data[] = $v;
		}
		$dev_cellNum = count($dev_xlscell);
		for($i=0;$i<$dev_cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $dev_xlscell[$i][1]); 
        }  
        $dataNum = count($dev_data);		
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$dev_cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $dev_data[$i][$dev_xlscell[$j][0]]);
          }             
        } 		
		$objPHPExcel->getActiveSheet(0)->setTitle($this->convertUTF8('开发者审核'));
		//APP审核
		$data_app = array();
		foreach($data_list as $k => $v){
			if($k > 4) continue;
			$v['type'] = $type[$k];
			$data_app[] = $v;
		}
		$cellNum = count($xlsCell);
		for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(1)->setCellValue($cellName[$i].'1', $xlsCell[$i][1]); 
        }  
        $dataNum = count($data_app);		
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(1)->setCellValue($cellName[$j].($i+2), $data_app[$i][$xlsCell[$j][0]]);
          }             
        } 
		//前后台标识
		$data_top = array();
		foreach($data_list as $k => $v){
			if($k < 5) continue;
			$v['type'] = $type[$k];
			$data_top[] = $v;
		}
		$objPHPExcel->getActiveSheet(1)->setTitle($this->convertUTF8('APP审核'));
		for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(2)->setCellValue($cellName[$i].'1', $xlsCell[$i][1]); 
        }  
		$dataNum_top = count($data_top);
        for($i=0;$i<$dataNum_top;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(2)->setCellValue($cellName[$j].($i+2), $data_top[$i][$xlsCell[$j][0]]);
          }             
        } 
		$objPHPExcel->getActiveSheet(2)->setTitle($this->convertUTF8('前后台标识'));
		$xlsTitle = '数据统计'.date("Y-m-d",time());
		header ( 'Content-Type: application/vnd.ms-excel;charset=utf-8' );
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . urlencode($xlsTitle) . '.xls"');
		} else if (preg_match("/Firefox/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . $xlsTitle . '.xls"');
		} else {  
			header('Content-Disposition: attachment; filename="'.$xlsTitle.'.xls"');
		}	
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;	
		
	}
}
?>