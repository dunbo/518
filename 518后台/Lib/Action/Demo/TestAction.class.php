<?php
/**
 * 安智网产品管理平台 公用控制器
 * ============================================================================
 * 版权所有 2009-2011 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * by:金山 2010.4.21
 * ----------------------------------------------------------------------------
 */
class TestAction extends Action {
	function index() 
	{
		$this->display();
	}
	
	function dump()
	{
		$model = M('soft');
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$where = array(
			'status' => 1,
			'hide' => 1
		);
		if (!isset($_GET['total'])) {
			$total = $model->where($where)->count();
		} else {
			$total = $_GET['total'];
		}
		
		if (!isset($_GET['fid'])) {
			$fid = uniqid();
		} else {
			$fid = $_GET['fid'];
		}

		import("@.ORG.Page");
		$Page = new Page($total, 5000);
		$result = $model->where($where)->field('softname,softid,package')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$fp = fopen('/tmp/export-'. $fid. '.csv', 'a');
		foreach ($result as $row) {
			fputcsv($fp, $row);
		}
		fclose($fp);
		
		$next_page = $page + 1;
		if ($page != $Page->totalPages) {
			//header("location: /index.php/Demo/Test/dump?page={$next_page}&total={$total}&fid={$fid}");
			//exit;
			$data = array(
				'type' => 'pager',
				'url' => "/index.php/Demo/Test/dump?page={$next_page}&total={$total}&fid={$fid}",
				'total_page' => $Page->totalPages
			);
		} else {
			$data = array(
				'type' => 'file',
				'url' => "/index.php/Demo/Test/getfile?fid={$fid}",
			);			
			//header("location: /index.php/Demo/Test/getfile?fid={$fid}");
			//exit;
		}
		exit(json_encode($data));
	}
	//文件下载
	function getfile()
	{
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="softinfo.csv"');
		header('Cache-Control: max-age=0');
		$fid = $_GET['fid'];
		$fp = fopen('/tmp/export-'. $fid. '.csv', 'r');
		$out_fp = fopen('php://output', 'a');
		while (!feof($fp)) {
			fputs($out_fp, fgets($fp));
		}
		fclose($fp);
		fclose($out_fp);
		exit;
	}
	//phpexcel demo
	function phpexcel(){
		$model = new Model();
		include('/data/www/wwwroot/newadmin.goapk.com/Lib/ORG/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		//设置属性
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
			->setLastModifiedBy("Maarten Balliauw")
			->setTitle("Office 2007 XLSX Test Document")
			->setSubject("Office 2007 XLSX Test Document")
			->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
			->setKeywords("office 2007 openxml php")
			->setCategory("Test result file");
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', '合并单元格')
				->setCellValue('B1', '当前总量')
				->setCellValue('C1', '本周新增');
		//合并单元格		
		$objPHPExcel->getActiveSheet(0)->mergeCells('A1:P1')
		//垂直水平居中 
		->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
		//设置行高度  
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);  
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);  
		$objPHPExcel->getActiveSheet()->setTitle($this->convertUTF8('开发者审核'));		
		$sheet1 = $objPHPExcel->createSheet();	
		$objPHPExcel->setActiveSheetIndex(1)
					->setCellValue('A1', '')
					->setCellValue('B1', '当前总量')
					->setCellValue('C1', '游戏总量')
					->setCellValue('D1', '应用总量');
		// 修改sheet名称
		$objPHPExcel->getActiveSheet(2)->setTitle($this->convertUTF8('APP审核'));	
		$excel_title = '数据统计'.date("Y-m-d",time());
		header ( 'Content-Type: application/vnd.ms-excel;charset=utf-8' );
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.xls"');
		} else if (preg_match("/Firefox/", $ua)) {  
			header('Content-Disposition: attachment; filename="' . $excel_title . '.xls"');
		} else {  
			header('Content-Disposition: attachment; filename="'.$excel_title.'.xls"');
		}	
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;		
	}
	//软件字符串编码格式
	public function convertUTF8($str){
		if(empty($str)) return '';
		if(mb_check_encoding($str,"utf-8") != true){
			return iconv("gbk","utf-8", $str);
		}else{
			return $str;
		}
	}
	//事务处理demo
	public function commit_test(){
		//$model = new Model();
		$active_model = D('sendNum.sendNum');
		//$model->startTrans(); //  在User模型中启动事务
		$active_model -> startTrans();
		// 进行相关的业务逻辑操作
		// $map = array( 'softname' => "tv.dan[m]a(ku.b?iliii" );
		// $ret = $model->table('sj_soft')->where('softid=4')->save($map); // 保存用户信息
		$map = array( 'active_name' => "commit" );
		$res = $active_model->table('sendnum_tmp')->where("id=1")->save($map);
		// if ($ret){	
			// echo '111';
			// $model->commit(); // 提交事务
		// }else{
			//$model->rollback(); 	// 事务回滚
			$ret = $active_model->rollback();
			var_dump($ret);
			echo '222';
		//}

	}
}
?>
