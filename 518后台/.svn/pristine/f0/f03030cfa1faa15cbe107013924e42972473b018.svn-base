<?php
/*
 * 审核操作日报
 */
class AuditLogAction extends CommonAction {
    public function index(){
        $model = M('');
        if(!isset($_GET['start_tm'])||empty($_GET['start_tm'])){
            $where['audit_tm'] = strtotime(date('Y-m-d'));
        }else{
            $where['audit_tm'][] = array('exp','>= '.strtotime($_GET['start_tm']));
            $this->assign('start_tm',$_GET['start_tm']);
        }
        if(isset($_GET['end_tm'])&&!empty($_GET['end_tm'])){
            $where['audit_tm'][] = array('exp','<= '.strtotime($_GET['end_tm'].' 23:59:59'));
            $this->assign('end_tm',$_GET['end_tm']);
        }
        if(!isset($_GET['admin_name'])||empty($_GET['admin_name'])){
            $all_admin = $model->table('sj_audit_adminuser')->where(array('status'=>1))->field('admin_id,admin_name')->order('rank asc')->select();
            foreach($all_admin as $k=>$v){
                $admin_id[] = $v['admin_id'];
            }
        }
        $where['admin_id'] = array('in',$admin_id);
//        var_dump($all_admin);
        $where['status'] = 1;
        $where['extra'] = array('exp',' != "processed"');
        $all_info = $model->table('sj_audit_day_log')->where($where)->select();
        //排除所有操作都没有的管理员
        $show_admin = array();
        foreach($all_info as $k=>$v){
            if($show_admin[] = $v['admin_id']);
        }
        foreach($all_admin as $a_k=>$a_v){
            if(!in_array($a_v['admin_id'],$show_admin)){
                unset($all_admin[$a_k]);
            }
        }
        $res = $this->process_info($all_info);
        //导出统计数据
        if($_GET['import_out']==1){
			if($_GET['go']==1){
				$this->import_day_log($res,$all_admin);
			}else if($_GET['go']==2){
				$this->import_caiji_log($res,$all_admin);
			}else{
				$this->import_out($res,$all_admin,$_GET['start_tm'],$_GET['end_tm']);
			}
            
        }
        $this->assign('all_admin',$all_admin);
        $this->assign("res",$res);
        $this->display();
    }
    
    //添加用户
    public function add_user(){
        if($_POST){
            $admin_name = $_POST['admin_name'];
            if(!empty($admin_name)){
                $model = M('');
                $is_has_admin = $model->table('sj_audit_adminuser')->where(array('admin_name'=>$admin_name,'status'=>1))->find();
                if(!$is_has_admin){
                    $admin = $model->table('sj_admin_users')->where(array('admin_user_name' => $admin_name, 'admin_state' => 1))->field('admin_user_id')->find();
                    if (!$admin) {
                        $this->error('用户名不存在');
                    } else {
                        $data['admin_id'] = $admin['admin_user_id'];
                        $data['admin_name'] = $admin_name;
                        $num = $model->table('sj_audit_adminuser')->count();
                        $data['rank'] = $num + 1;
                        $res = $model->table('sj_audit_adminuser')->add($data);
                        if($res){
                            $this->writelog("审核操作日报：添加了id为{$res}的账号", 'sj_audit_adminuser', $res,__ACTION__ ,"","add");
                            $this->success('添加成功');
                        }else{
                            $this->error('添加失败');
                        }
                    }
                }else{
                    $this->error('用户名已存在');
                }
                
            }else{
                $this->error('请输入用户名');
            }
            
        }
        $this->display();
    }
    
    
    //编辑用户
    public function edit_user(){
        $model = M('');
        if(!empty($_GET['admin_id'])){
            $admin=$model->table('sj_audit_adminuser')->where(array('admin_id'=>$_GET['admin_id'],'status'=>1))->find();
            $this->assign('admin',$admin);
        }
         if(!isset($_GET['start_tm'])||empty($_GET['start_tm'])){
            $where['audit_tm'] = strtotime(date('Y-m-d'));
            $start_tm = strtotime(date('Y-m-d'));
        }else{
            $start_tm =  strtotime($_GET['start_tm']);
        }
        $this->assign('start_tm',$start_tm);
        if(isset($_GET['end_tm'])&&!empty($_GET['end_tm'])){           
            $end_tm = strtotime($_GET['end_tm'].' 23:59:59');
        }else{
            $end_tm = strtotime(date('Y-m-d').' 23:59:59');
        }
        $this->assign('end_tm',$end_tm);
        if(isset($_POST['rank'])&&!empty($_POST['rank'])){
            //修改排序值
            $num = $model->table('sj_audit_adminuser')->count();
            if($_POST['rank']>$num){
                $_POST['rank'] = $num;
            }
            $res = $this->_updateRankInfo('sj_audit_adminuser','rank',(int)$_POST['info_id'],array('status'=>1),(int)$_POST['rank']);
            if($res){
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
        }
        $this->display();
    }
    
    //删除用户时间段统计数据
    function del_data(){
        if(!empty($_GET['start_tm'])&&!empty($_GET['end_tm'])){
            $model=M('');
            $where = array('admin_id'=>$_GET['admin_id'],'status'=>1);
            $where['audit_tm'][] = array('exp','>= '.$_GET['start_tm']);
            $where['audit_tm'][] = array('exp','<= '.$_GET['end_tm']);
            $data['status']=0;
            $res =  $model->table('sj_audit_day_log')->where($where)->save($data);         
            if($res){
                $this->success('删除成功');
            }else{
                $this->success('删除失败');
            }
        }
    }
    //处理统计数据，返回所需格式
    public function process_info($data){
        $action_id_arr = $admin_info_arr = array();
        foreach($data as $k=>$v){
            if(($v['extra']==''||$v['extra']!=0)&&$v['extra']!='-1'){
                $action_id_arr[$v['action_id']]['num'] += $v['num'];
                $admin_info_arr[$v['admin_id']][$v['action_id']] += $v['num'];
            }else{              
                $action_id_arr[$v['extra']]['num'] += $v['num'];
                $admin_info_arr[$v['admin_id']][$v['extra']] += $v['num'];
            }
            
        }
        return array($action_id_arr,$admin_info_arr);
    }
    
    //导出统计数据
    public function import_out($data,$user,$start_tm,$end_tm){
        import('@.ORG.PHPExcel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $filePath = dirname(__FILE__).'/../../../Public/audit_log.xlsx';
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);
        if(empty($start_tm)){
            $start_tm = date('Y-m-d 00:00:00');
        }else{
            $start_tm = $start_tm.' 00:00:00';
        }
        if(empty($end_tm)){
            $end_tm = date('Y-m-d H:i:s');
        }else{
            $end_tm =$end_tm.' 23:59:59';
        }
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '审核操作日报_'.$start_tm.'——'.$end_tm);
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');

        //管理员及其统计数据
        $colIndex = 'E';
        foreach($user as $k=>$v){
            $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue($colIndex.'2', $v['admin_name'])
            ->setCellValue($colIndex.'3', empty($data[1][$v['admin_id']]['1128'])?'0':$data[1][$v['admin_id']]['1128'])
            ->setCellValue($colIndex.'4', empty($data[1][$v['admin_id']]['1132'])?'0':$data[1][$v['admin_id']]['1132'])
            ->setCellValue($colIndex.'5', empty($data[1][$v['admin_id']]['1129'])?'0':$data[1][$v['admin_id']]['1129'])
            ->setCellValue($colIndex.'6', empty($data[1][$v['admin_id']]['1134'])?'0':$data[1][$v['admin_id']]['1134'])
            ->setCellValue($colIndex.'7', $data[1][$v['admin_id']]['1130']+$data[1][$v['admin_id']]['1133']) 
            ->setCellValue($colIndex.'8', empty($data[1][$v['admin_id']]['new'])?'0':$data[1][$v['admin_id']]['new'])
            ->setCellValue($colIndex.'9', $data[1][$v['admin_id']]['1131']+$data[1][$v['admin_id']]['1141'])
            ->setCellValue($colIndex.'10', empty($data[1][$v['admin_id']]['cj_add'])?'0':$data[1][$v['admin_id']]['cj_add'])
            ->setCellValue($colIndex.'11', empty($data[1][$v['admin_id']]['add'])?'0':$data[1][$v['admin_id']]['add'])
            ->setCellValue($colIndex.'12', empty($data[1][$v['admin_id']]['cj_update'])?'0':$data[1][$v['admin_id']]['cj_update'])
            ->setCellValue($colIndex.'13', empty($data[1][$v['admin_id']]['update'])?'0':$data[1][$v['admin_id']]['update'])
            ->setCellValue($colIndex.'14', $data[1][$v['admin_id']]['0']+$data[1][$v['admin_id']]['2226'])
            ->setCellValue($colIndex.'15', $data[1][$v['admin_id']]['-1']+$data[1][$v['admin_id']]['2227'])
            ->setCellValue($colIndex.'16', empty($data[1][$v['admin_id']]['1082'])?'0':$data[1][$v['admin_id']]['1082'])
            ->setCellValue($colIndex.'17', empty($data[1][$v['admin_id']]['2206'])?'0':$data[1][$v['admin_id']]['2206'])
            ->setCellValue($colIndex.'18', $data[1][$v['admin_id']]['2193']+$data[1][$v['admin_id']]['2196'])
            ->setCellValue($colIndex.'19', $data[1][$v['admin_id']]['1109']+$data[1][$v['admin_id']]['2028']+$data[1][$v['admin_id']]['1108'])
            ->setCellValue($colIndex.'20', $data[1][$v['admin_id']]['1548']+$data[1][$v['admin_id']]['1162'])
            ->setCellValue($colIndex.'21', $data[1][$v['admin_id']]['1165']+$data[1][$v['admin_id']]['2030']+$data[1][$v['admin_id']]['2221']+$data[1][$v['admin_id']]['feedback']+$data[1][$v['admin_id']]['1878'])
            ->setCellValue($colIndex.'22', $data[1][$v['admin_id']]['report']+$data[1][$v['admin_id']]['reportprocessed'])
            ->setCellValue($colIndex.'23', $data[1][$v['admin_id']]['1103']+$data[1][$v['admin_id']]['1112'])
            ->setCellValue($colIndex.'24', $data[1][$v['admin_id']]['1555']+$data[1][$v['admin_id']]['1554'])
            ->setCellValue($colIndex.'25', empty($data[1][$v['admin_id']]['1579'])?'0':$data[1][$v['admin_id']]['1579'])
            ->setCellValue($colIndex.'26', empty($data[1][$v['admin_id']]['1586'])?'0':$data[1][$v['admin_id']]['1586']);
            $colIndex ++;
        }
        //填充统计数据
        $PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('C3', $data[0]['1128']['num']+$data[0]['1132']['num']) 
            ->setCellValue('D3', $data[0]['1128']['num'])
            ->setCellValue('D4', $data[0]['1132']['num'])
            ->setCellValue('C5', $data[0]['1129']['num']+$data[0]['1134']['num']+$data[0]['1130']['num']+$data[0]['1133']['num'])
            ->setCellValue('D5', $data[0]['1129']['num'])
            ->setCellValue('D6', $data[0]['1134']['num'])
            ->setCellValue('D7', $data[0]['1130']['num']+$data[0]['1133']['num'])
            ->setCellValue('C8', $data[0]['new']['num'])
            ->setCellValue('C9', $data[0]['1131']['num']+$data[0]['1141']['num'])
            ->setCellValue('C10', $data[0]['cj_add']['num']+$data[0]['add']['num'])
            ->setCellValue('D10', $data[0]['cj_add']['num'])
            ->setCellValue('D11', $data[0]['add']['num'])
            ->setCellValue('C12', $data[0]['cj_update']['num']+$data[0]['update']['num'])
            ->setCellValue('D12', $data[0]['cj_update']['num'])
            ->setCellValue('D13', $data[0]['update']['num'])
            ->setCellValue('C14', $data[0]['0']['num']+$data[0]['2226']['num']+$data[0]['-1']['num']+$data[0]['2227']['num'])
            ->setCellValue('D14', $data[0]['0']['num']+$data[0]['2226']['num'])
            ->setCellValue('D15', $data[0]['-1']['num']+$data[0]['2227']['num'])
            ->setCellValue('C16', $data[0]['1082']['num'])
            ->setCellValue('C17', $data[0]['2206']['num'])
            ->setCellValue('C18', $data[0]['2193']['num']+$data[0]['2196']['num'])
            ->setCellValue('C19', $data[0]['1109']['num']+$data[0]['2028']['num']+$data[0]['1108']['num']+$data[0]['1548']['num']+$data[0]['1162']['num']+$data[0]['1165']['num']+$data[0]['2030']['num']+$data[0]['feedback']['num']+$data[0]['report']['num']+$data[0]['reportprocessed']['num']+$data[0]['2221']['num']+$data[0]['1878']['num'])
            ->setCellValue('D19', $data[0]['1109']['num']+$data[0]['2028']['num']+$data[0]['1108']['num'])
            ->setCellValue('D20', $data[0]['1548']['num']+$data[0]['1162']['num'])
            ->setCellValue('D21', $data[0]['1165']['num']+$data[0]['2030']['num']+$data[0]['2221']['num']+$data[0]['feedback']['num']+$data[0]['1878']['num'])
            ->setCellValue('D22', $data[0]['report']['num']+$data[0]['reportprocessed']['num'])
            ->setCellValue('C23', $data[0]['1103']['num']+$data[0]['1112']['num']+$data[0]['1555']['num']+$data[0]['1554']['num'])
            ->setCellValue('D23', $data[0]['1103']['num']+$data[0]['1112']['num'])
            ->setCellValue('D24', $data[0]['1555']['num']+$data[0]['1554']['num'])
            ->setCellValue('C25', $data[0]['1579']['num']+$data[0]['1586']['num'])
            ->setCellValue('D25', $data[0]['1579']['num'])
            ->setCellValue('D26', $data[0]['1586']['num']);
        //生成新的excel     
        $excel_title = '审核操作日报'.date('Y-m-d');
        header ( 'Content-Type: application/vnd.ms-excel' );
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . $excel_title . '.csv"');
        } else {  
                header('Content-Disposition: attachment; filename="'.$excel_title.'.csv"');
        }	
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
	
	//导出日报表
	function import_day_log($data,$user){
		import('@.ORG.PHPExcel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $filePath = dirname(__FILE__).'/../../../Public/audit_day_log.xlsx';
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);
		$merge = 'C';
		 for($i=1;$i<=count($user);$i++){
			 $merge++;
		 }
		
		$PHPExcel->getActiveSheet()->mergeCells('A1:'.$merge.'1'); 
		$PHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '数据统计'.date('Y/m/d'));
		$PHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$PHPExcel->setActiveSheetIndex(0)
			->setCellValue('B3', $data[0]['1128']['num']+$data[0]['1132']['num'])
			->setCellValue('B4', $data[0][1129]['num']+$data[0][1134]['num']+$data[0][1130]['num']+$data[0][1133]['num'])
			->setCellValue('B5', $data[0][1131]['num']+$data[0][1141]['num'])
			->setCellValue('B6', $data[0]['0']['num']+$data[0]['2226']['num']+$data[0]['-1']['num']+$data[0]['2227']['num'])
            ->setCellValue('C3', round($data[0][1128]['num']/($data[0][1128]['num']+$data[0][1132]['num']),2))
			->setCellValue('C4', round($data[0][1129]['num']/($data[0][1129]['num']+$data[0][1134]['num']+$data[0][1130]['num']+$data[0][1133]['num']),2))
                                                     ->setCellValue('C6', round(($data[0]['0']['num']+$data[0]['2226']['num'])/($data[0]['0']['num']+$data[0]['2226']['num']+$data[0]['-1']['num']+$data[0]['2227']['num']),2));
                                    if(($data[0][1131]['num']+$data[0][1141]['num'])!=0){
                                            $PHPExcel->setActiveSheetIndex(0)
                                                            ->setCellValue('C5', 1);
                                    }else{
                                            $PHPExcel->setActiveSheetIndex(0)
                                                            ->setCellValue('C5', 0);
                                    }
                        
			
		//管理员及其统计数据
        $colIndex = 'D';
        foreach($user as $k=>$v){
            $PHPExcel->setActiveSheetIndex(0)
			->setCellValue($colIndex.'2', $v['admin_name'])
            ->setCellValue($colIndex.'3', $data[1][$v['admin_id']]['1128']+$data[1][$v['admin_id']]['1132'])
			->setCellValue($colIndex.'4', $data[1][$v['admin_id']]['1129']+$data[1][$v['admin_id']]['1134']+$data[1][$v['admin_id']]['1130']+$data[1][$v['admin_id']]['1133'])
			->setCellValue($colIndex.'5', $data[1][$v['admin_id']]['1131']+$data[1][$v['admin_id']]['1141'])
			->setCellValue($colIndex.'6', $data[1][$v['admin_id']]['0']+$data[1][$v['admin_id']]['2226']+$data[1][$v['admin_id']]['-1']+$data[1][$v['admin_id']]['2227']);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'3')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'3')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'4')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'4')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'5')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'5')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'6')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$PHPExcel->getActiveSheet()->getStyle($colIndex.'6')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
			$colIndex ++;
		}
		//生成新的excel     
        $excel_title = '日报表_'.date('Y-m-d');
        header ( 'Content-Type: application/vnd.ms-excel' );
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . $excel_title . '.csv"');
        } else {  
                header('Content-Disposition: attachment; filename="'.$excel_title.'.csv"');
        }	
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
	}
	
	//采集报表
	function import_caiji_log($res,$all_admin){
		import('@.ORG.PHPExcel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $filePath = dirname(__FILE__).'/../../../Public/audit_caiji_log.xlsx';
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);
		$PHPExcel->setActiveSheetIndex(0)
			->setCellValue('B2', $res[0]['cj_add']['num']+$res[0]['add']['num']+$res[0]['cj_update']['num']+$res[0]['update']['num'])
			->setCellValue('C2', round(($res[0]['cj_add']['num']+$res[0]['cj_update']['num'])/($res[0]['cj_add']['num']+$res[0]['add']['num']+$res[0]['cj_update']['num']+$res[0]['update']['num']),2))
			->setCellValue('B3', $res[0]['cj_add']['num']+$res[0]['add']['num'])
			->setCellValue('C3', round(($res[0]['cj_add']['num'])/($res[0]['cj_add']['num']+$res[0]['add']['num']),2))
			->setCellValue('B4', $res[0]['cj_update']['num']+$res[0]['update']['num'])
			->setCellValue('C4', round(($res[0]['cj_update']['num'])/($res[0]['cj_update']['num']+$res[0]['update']['num']),2));
		//生成新的excel     
        $excel_title = '采集报表_'.date('Y-m-d');
        header ( 'Content-Type: application/vnd.ms-excel' );
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . urlencode($excel_title) . '.csv"');
        } else if (preg_match("/Firefox/", $ua)) {  
                header('Content-Disposition: attachment; filename="' . $excel_title . '.csv"');
        } else {  
                header('Content-Disposition: attachment; filename="'.$excel_title.'.csv"');
        }	
        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
        $objWriter->save('php://output');
	}
	
}

