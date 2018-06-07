<?php
class AdFrameworkAction extends CommonAction {
    
    public function index() {
        $AdFrameworkModel = D("sendNum.AdFramework");
        $ContractModel = D("sendNum.Contract");
        
        // 所有广告频道
        $channels = $ContractModel->getFirstNames();
        
        // 搜索条件
        $where = array();
        $url_param = '';
        // 协议编号
        $search_framework_number = trim($_GET['search_framework_number']);
        if ($search_framework_number) {
            $where['ad_framework.framework_number'] = $search_framework_number;
            $url_param .= "&search_framework_number={$search_framework_number}";
            $this->assign('search_framework_number', $search_framework_number);
        }
        // 客户名称
        $search_client_name = trim($_GET['search_client_name']);
        if ($search_client_name) {
            $where['ad_client.client_name'] = $search_client_name;
            $url_param .= "&search_client_name={$search_client_name}";
            $this->assign('search_client_name', $search_client_name);
        }
        // 负责人
        $search_charge_name = trim($_GET['search_charge_name']);
        if ($search_charge_name) {
            $where['sj_admin_users.admin_user_name'] = $search_charge_name;
            $url_param .= "&search_charge_name={$search_charge_name}";
            $this->assign('search_charge_name', $search_charge_name);
        }
        // 购买频道
        $search_channel = trim($_GET['search_channel']);
        if ($search_channel) {
            $where['purchase_channels'] = array('like', "%,{$search_channel},%");
            $url_param .= "&search_channel={$search_channel}";
            $this->assign('search_channel', $search_channel);
        }
        // 合作日期
        $search_start_time = trim($_GET['search_start_time']);
        if (!$search_start_time) {
            // 搜索的开始时间默认为一年前当前月份
            $last_year = date("Y-m",strtotime("-1 year"));
            $search_start_time = $last_year;
        }
        $url_param .= "&search_start_time={$search_start_time}";
        $this->assign('search_start_time', $search_start_time);
        $where['ad_framework.start_time'] = array('egt', strtotime($search_start_time));
        $search_end_time = trim($_GET['search_end_time']);
        if (!$search_end_time) {
            $search_end_time = '2038-01';
        } else {
            $this->assign('search_end_time', $search_end_time);
        }
        $url_param .= "&search_end_time={$search_end_time}";
        $where['ad_framework.end_time'] = array('elt', strtotime($search_end_time));
        
        // 签订日期
        $search_sign_start = trim($_GET['search_sign_start']);
        if (!$search_sign_start) {
            // 搜索的开始时间默认为一年前当前月份
            $last_year_month = date("Y-m",strtotime("-1 year"));
            $search_sign_start = $last_year_month;
        }
        $url_param .= "&search_sign_start={$search_sign_start}";
        $this->assign('search_sign_start', $search_sign_start);
        $search_sign_end = trim($_GET['search_sign_end']);
        if (!$search_sign_end) {
            $search_sign_end = '2038-01';
        } else {
            $this->assign('search_sign_end', $search_sign_end);
        }
        $url_param .= "&search_sign_end={$search_sign_end}";
        $where['ad_framework.sign_time'] = array(array('egt', strtotime($search_sign_start)), array('elt', strtotime($search_sign_end)), 'and');
        
        $where['ad_framework.status'] = 1;
        // 查表
        import("@.ORG.Page");
        $count = $AdFrameworkModel->getAllFrameworkCount($where);
        $page = new Page($count, 10);
        $show = $page->show();//分页显示输出
        $list = $AdFrameworkModel->getFrameworkList($where, $page->firstRow, $page->listRows);
        
        $this->process_list($list);
        
        if ($_GET['down'] == 1) {
            $allList = $AdFrameworkModel->getFrameworkList($where);
            $this->exportFrameworkList($allList);
        }
        
        $this->assign('list', $list);
        $this->assign('url_param', $url_param);
        $this->assign('page', $show);
        $this->assign('channels', $channels);
        $this->display();
    }
    
    function process_list(&$list) {
        $AdFrameworkModel = D("sendNum.AdFramework");
        $ContractModel = D("sendNum.Contract");
        foreach ($list as $key => $record) {
            $client_id = $record['client_id'];
            $charge_id = $record['charge_id'];
            $purchase_channels = $record['purchase_channels'];
            $framework_id = $record['id'];
            $list[$key]['client_name'] = $AdFrameworkModel->getClientName($client_id);
            $list[$key]['charge_name'] = $AdFrameworkModel->getChargeName($charge_id);
            $list[$key]['purchase_channel_name'] = $AdFrameworkModel->getPurchaseChannelNames($purchase_channels);
            $list[$key]['contract_count'] = $ContractModel->getContractCountOfFramework($framework_id);
            // 已收保证金
            $list[$key]['total_input_account'] = $AdFrameworkModel->get_total_input_account($framework_id);
            $list[$key]['total_input_invoice'] = $AdFrameworkModel->get_total_input_invoice($framework_id);
            // 待抵扣
            $list[$key]['left_amount'] = $AdFrameworkModel->get_left_amount($framework_id);
        }
    }
    
    function exportFrameworkList($list) {
        $this->process_list($list);
        require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        // 设置数据
        $sheet->setCellValue('A1',"协议编号");
        $sheet->setCellValue('B1',"客户名称");
        $sheet->setCellValue('C1',"起始日期");
        $sheet->setCellValue('D1',"终止日期");
        $sheet->setCellValue('E1',"购买频道");
        $sheet->setCellValue('F1',"签订日期");
        $sheet->setCellValue('G1',"负责人");
        $sheet->setCellValue('H1',"合作金额");
        $sheet->setCellValue('I1',"预计保证金");
        $sheet->setCellValue('J1',"已收保证金");
        $sheet->setCellValue('K1',"待抵扣");
        $sheet->setCellValue('L1',"已开发票/票据");
        $sheet->setCellValue('M1',"合同数量");
        $sheet->setCellValue('N1',"备注");
        
        $row_map = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N');
        $key_map = array('framework_number', 'client_name', 'start_time', 'end_time', 'purchase_channels', 'sign_time', 'charge_name', 'cooperate_account', 'expected_deposit', 'total_input_account', 'left_amount', 'total_input_invoice', 'contract_count', 'remark');
        $key_map2 = array();
        foreach ($key_map as $key => $value) {
            $key_map2[$value] = $key;
        }
        foreach ($list as $i => $record) {
            $ii = $i + 2;
            foreach ($record as $key => $value) {
                if (!in_array($key, $key_map))
                    continue;
                $j = $key_map2[$key]+1;
                $col = $row_map[$j-1];
                $pos = "{$col}{$ii}";
                $sheet->setCellValue($pos, $value);
            }
        }
        
        // 设置样式
        $style = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FFFF0000'),
                ),
            ),
            'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
              'wrap'       => true
            )
        );
        $style_obj = new PHPExcel_Style();
        $style_obj->applyFromArray($style);
        $span_line = count($list) + 1;
        $span = "A1:L" . $span_line;
        $sheet->setSharedStyle($style_obj, $span);
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . urlencode('框架协议列表') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
    // 添加框架协议展示
    public function add_framework_show() {
        // 判断是否已上传一个文件
        $admin_id = $_SESSION['admin']['admin_id'];
		$my_time = microtime();
		$my_hash = md5($admin_id . $my_time);
        
        // 将所有客户列表返回给页面
        $AdFrameworkModel = D("sendNum.AdFramework");
        $client_list = $AdFrameworkModel->getAllClients();
        $client_data = array();
        foreach ($client_list as $client) {
            $client_data[] = array('value' => $client['id'], 'name' => $client['client_name'],);
        }
        $this->assign('client_data', json_encode($client_data));
        
        // 可购买的频道
        // 所有广告频道
        $ContractModel = D("sendNum.Contract");
        $channels = $ContractModel->getFirstNames();
        
        $this -> assign('my_hash', $my_hash);
        $this->assign('channels', $channels);
		$this -> display('add_framework');
    }
    
    public function add_framework_do() {
        $now = time();
        $AdFrameworkModel = D("sendNum.AdFramework");
        $map = array();
        // 客户
        $client_name = trim($_POST['client_name']);
        if (!$client_name) {
            $this->error("客户名称不能为空");
        }
        $client_id = $AdFrameworkModel->getClientId($client_name);
        if (!$client_id) {
            $this->error("客户不存在");
        }
        $map['client_id'] = $client_id;
        // 协议编号
        $framework_number = trim($_POST['framework_number']);
        if (!$framework_number) {
            $this->error("协议编号不能为空");
        }
        $framework_number_exists = $AdFrameworkModel->checkFrameworkNumberExists($framework_number);
        if ($framework_number_exists) {
            $this->error("协议编号已存在");
        }
        $map['framework_number'] = $framework_number;
        // 签订日期
        $sign_time = trim($_POST['sign_time']);
        if (!$sign_time) {
            $this->error("签订日期不能为空");
        }
        $sign_time = strtotime($sign_time);
        $map['sign_time'] = $sign_time;
        // 合作金额
        $cooperate_account = trim($_POST['cooperate_account']);
        if (!$cooperate_account) {
            $this->error("合作金额不能为空");
        }
        $map['cooperate_account'] = $cooperate_account;
        // 购买频道
        $purchase_channel = $_POST['purchase_channel'];
        $purchase_channel = array_filter($purchase_channel);
        $purchase_channel = array_unique($purchase_channel);
        if (empty($purchase_channel)) {
            $this->error("购买频道不能为空");
        }
        $purchase_channels = implode(',', $purchase_channel);
        $map['purchase_channels'] = ",{$purchase_channels},";
        // 起始日期
        $start_time = trim($_POST['start_time']);
        if (!$start_time) {
            $this->error("起始日期不能为空");
        }
        $start_time = strtotime($start_time);
        $map['start_time'] = $start_time;
        // 终止日期
        $end_time = trim($_POST['end_time']);
        if (!$end_time) {
            $this->error("结束日期不能为空");
        }
        $end_time = strtotime($end_time) + 86399;
        $map['end_time'] = $end_time;
        if ($start_time >= $end_time) {
            $this->error("起始日期不能大于终止日期");
        }
        // 负责人
        $charge_name = trim($_POST['charge_name']);
        if (!$charge_name) {
            $this->error("负责人不能为空");
        }
        $charge_id = $AdFrameworkModel->getChargeId($charge_name);
        if (!$charge_id) {
            $this->error("负责人不存在");
        }
        $map['charge_id'] = $charge_id;
        // 预计保证金
        $expected_deposit = trim($_POST['expected_deposit']);
        if (!$expected_deposit) {
            $this->error("预计保证金不能为空");
        }
        $map['expected_deposit'] = $expected_deposit;
        // 备注
        $remark = trim($_POST['remark']) ? trim($_POST['remark']) : "";
        $map['remark'] = $remark;
        $map['create_time'] = $now;
        $map['update_time'] = $now;
        $map['status'] = 1;
        // 附件
        $hash = $_POST['my_hash'];
        $model = M();
        $tmp_affix = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> find();
        if ($tmp_affix['file_name']) {
            // 文件存储路径
            $my_go = C('AD_FILE');
			$my_tmp_go = C('AD_TMP_FILE');
            // 创建目录
            $my_time = date('Ym/d');
            $tmp_dir = $my_go .'/'.$my_time;
            if(!is_dir($tmp_dir)){
                @mkdir($tmp_dir,0777,true);
            }
            $src_file = $my_tmp_go .'/'.$tmp_affix['file_name'];
            $dest_file = $tmp_dir.'/'.$tmp_affix['file_name'];
            if(!copy($src_file, $dest_file)){
                $this->error("文件上传失败！");
            }
            $map['affix_name'] = $tmp_affix['affix_name'];
            $map['affix_url'] = $dest_file;
        }
        $ret = $AdFrameworkModel->addFramework($map);
        if ($ret) {
            $this->assign('jumpUrl', "__URL__/index");
            $this->success("添加成功！");
        } else {
            $this->error("添加失败！");
        }
        
    }
    
    // 编辑框架协议展示
    public function edit_framework_show() {
        // 判断是否已上传一个文件
        $admin_id = $_SESSION['admin']['admin_id'];
		$my_time = microtime();
		$my_hash = md5($admin_id . $my_time);
        
        // 将所有客户列表返回给页面
        $AdFrameworkModel = D("sendNum.AdFramework");
        $client_list = $AdFrameworkModel->getAllClients();
        $client_data = array();
        foreach ($client_list as $client) {
            $client_data[] = array('value' => $client['id'], 'name' => $client['client_name'],);
        }
        $this->assign('client_data', json_encode($client_data));
        
        // 可购买的频道
        $ContractModel = D("sendNum.Contract");
        $channels = $ContractModel->getFirstNames();
        
        // 查找记录
        $id = $_GET['id'];
        $find = $AdFrameworkModel->getFramework($id);
        $find['client_name'] = $AdFrameworkModel->getClientName($find['client_id']);
        $find['charge_name'] = $AdFrameworkModel->getChargeName($find['charge_id']);
        
        // 购买频道拆分成数组返回给html
        $purchase_channels = $find['purchase_channels'];
        $tmp_arr = explode(',', $purchase_channels);
        $purchase_channel_arr = array();
        foreach ($tmp_arr as $value) {
            if (!$value)
                continue;
            $purchase_channel_arr[] = $value;
        }
        // 增加频道时tr的id从哪个值开始加起
        $tr_start = count($purchase_channel_arr);
        
        $this -> assign('my_hash', $my_hash);
        $this->assign('channels', $channels);
        $this->assign('record', $find);
        $this->assign('purchase_channel_arr', $purchase_channel_arr);
        $this->assign('tr_start', $tr_start);
		$this -> display('edit_framework');
    }
    
    public function edit_framework_do() {
        $now = time();
        $AdFrameworkModel = D("sendNum.AdFramework");
        $map = array();
        $id = $_POST['id'];
        // 客户
        $client_name = trim($_POST['client_name']);
        if (!$client_name) {
            $this->error("客户名称不能为空");
        }
        $client_id = $AdFrameworkModel->getClientId($client_name);
        if (!$client_id) {
            $this->error("客户不存在");
        }
        $map['client_id'] = $client_id;
        // 协议编号
        $framework_number = trim($_POST['framework_number']);
        if (!$framework_number) {
            $this->error("协议编号不能为空");
        }
        $framework_number_exists = $AdFrameworkModel->checkFrameworkNumberExists($framework_number, $id);
        if ($framework_number_exists) {
            $this->error("协议编号已存在");
        }
        $map['framework_number'] = $framework_number;
        // 签订日期
        $sign_time = trim($_POST['sign_time']);
        if (!$sign_time) {
            $this->error("签订日期不能为空");
        }
        $sign_time = strtotime($sign_time);
        $map['sign_time'] = $sign_time;
        // 合作金额
        $cooperate_account = trim($_POST['cooperate_account']);
        if (!$cooperate_account) {
            $this->error("合作金额不能为空");
        }
        $map['cooperate_account'] = $cooperate_account;
        // 购买频道
        $purchase_channel = $_POST['purchase_channel'];
        $purchase_channel = array_filter($purchase_channel);
        $purchase_channel = array_unique($purchase_channel);
        if (empty($purchase_channel)) {
            $this->error("购买频道不能为空");
        }
        $purchase_channels = implode(',', $purchase_channel);
        $map['purchase_channels'] = ",{$purchase_channels},";
        // 起始日期
        $start_time = trim($_POST['start_time']);
        if (!$start_time) {
            $this->error("起始日期不能为空");
        }
        $start_time = strtotime($start_time);
        $map['start_time'] = $start_time;
        // 终止日期
        $end_time = trim($_POST['end_time']);
        if (!$end_time) {
            $this->error("结束日期不能为空");
        }
        $end_time = strtotime($end_time) + 86399;
        $map['end_time'] = $end_time;
        if ($start_time >= $end_time) {
            $this->error("起始日期不能大于终止日期");
        }
        // 负责人
        $charge_name = trim($_POST['charge_name']);
        if (!$charge_name) {
            $this->error("负责人不能为空");
        }
        $charge_id = $AdFrameworkModel->getChargeId($charge_name);
        if (!$charge_id) {
            $this->error("负责人不存在");
        }
        $map['charge_id'] = $charge_id;
        // 预计保证金
        $expected_deposit = trim($_POST['expected_deposit']);
        if (!$expected_deposit) {
            $this->error("预计保证金不能为空");
        }
        $map['expected_deposit'] = $expected_deposit;
        // 备注
        $remark = trim($_POST['remark']) ? trim($_POST['remark']) : "";
        $map['remark'] = $remark;
        $map['create_time'] = $now;
        $map['update_time'] = $now;
        $map['status'] = 1;
        // 附件
        $hash = $_POST['my_hash'];
        $model = M();
        $tmp_affix = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> find();
        if ($tmp_affix['file_name']) {
            // 文件存储路径
            $my_go = C('AD_FILE');
			$my_tmp_go = C('AD_TMP_FILE');
            // 创建目录
            $my_time = date('Ym/d');
            $tmp_dir = $my_go .'/'.$my_time;
            if(!is_dir($tmp_dir)){
                @mkdir($tmp_dir,0777,true);
            }
            $src_file = $my_tmp_go .'/'.$tmp_affix['file_name'];
            $dest_file = $tmp_dir.'/'.$tmp_affix['file_name'];
            if(!copy($src_file, $dest_file)){
                $this->error("文件上传失败！");
            }
            $map['affix_name'] = $tmp_affix['affix_name'];
            $map['affix_url'] = $dest_file;
        }
        $ret = $AdFrameworkModel->updateFramework($id, $map);
        if ($ret || $ret === 0) {
            $this->assign('jumpUrl', "__URL__/index");
            $this->success("编辑成功！");
        } else {
            $this->error("编辑失败！");
        }
    }
    
    function delete_framework() {
        $AdFrameworkModel = D("sendNum.AdFramework");
        $id = $_GET['id'];
        $map = array(
            'status' => 0,
        );
        $ret = $AdFrameworkModel->updateFramework($id, $map);
        // TODO, 将框架协议下的所有合同也删除
        if ($ret || $ret === 0) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    function ajax_upload_file() {
		$model = new Model();
		$my_tmp_go = C('AD_TMP_FILE');
		$affix = $_FILES['affix'];
		$id = $_GET['id'];
		$affix_type_arr = array_reverse(explode('.',$affix['name']));
		$affix_type = $affix_type_arr[0];
		$hash = $_GET['hash'];
		$allow_type = array('doc','docx', 'xls', 'xlsx', 'pdf','jpg','png','zip','rar');
		if(!in_array($affix_type,$allow_type)){
			$ret = array('code' => 2,'msg' => '上传文件失败,文件格式错误');
			echo json_encode($ret);
			return json_encode($ret);
		}
		$tmp_filename = $affix['name'];
		$the_tmp_filename = time().'.'.$affix_type;
		$tmp_dir = $my_tmp_go .'/';
		if(!is_dir($tmp_dir)){
			@mkdir($tmp_dir,0777,true);
		}
		$have_been = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
		if (file_exists($tmp_dir . $tmp_filename) || $have_been) {
			$ret = array('code' => 4,'msg' => '上传文件失败,只能上传一个文件');
			echo json_encode($ret);
			return json_encode($ret);
		} else{
            if(move_uploaded_file($affix["tmp_name"], $tmp_dir . $the_tmp_filename)){
                $affix_data = array(
                    'affix_hash' => $hash,
                    'affix_name' => $tmp_filename,
                    'file_name' => $the_tmp_filename,
                    'create_tm' => time(),
                    'status' => 1
                );
                $tmp_affix_result = $model -> table('ad_affix_tmp') ->add($affix_data);

                if($tmp_affix_result){
                    $result = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
                    
                    $ret = array('code' => 1,'msg' => $result);
                    echo json_encode($ret);
                    return json_encode($ret);
                }
            }else{
                $ret = array('code' => 3,'msg' => '上传文件失败');
                echo json_encode($ret);
                return json_encode($ret);
            }
		}
	
	}
    
    function upload_file(){
		$model = new Model();
		$id = $_GET['id'];
		$from = $_GET['from'];
		if(!$from){
			$from = 1;
		}
		//下载临时文件
		if($from == 1){
			$result = $model -> table('ad_affix_tmp') -> where(array('id' => $id)) -> select();
			$tmp_file = C('AD_TMP_FILE');
			$file = $tmp_file.'/'.$result[0]['file_name'];
			$file_name = $result[0]['affix_name'];
		}elseif($from == 2){
			$result = $model -> table('ad_framework') -> where(array('id' => $id)) -> select();
			$file = $result[0]['affix_url'];
			$file_name = $result[0]['affix_name'];
		}
		if($file_name){
			if(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")){
				$file_names = urlencode($file_name);
			}else{
				$file_names = $file_name;
			}
		}
		if(!file_exists($file)){
			$this -> error("文件不存在");
		}else{
			$open_file = fopen($file,"r");
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: ".filesize($file));
			Header("Content-Disposition: attachment; filename=" . $file_names);
			// 输出文件内容
			echo fread($open_file,filesize($file));
			fclose($file);
			exit();
		}
	}
    
    function del_file(){
		$model = new Model();
		$id = $_GET['id'];
		$from = $_GET['from'];
		$hash = $_GET['hash'];
		$data = array(
			'status' => 0
		);
		$client_id = $_GET['client_id'];
	
		if($from == 1){
			$where['id'] = $id;
			$where['affix_hash'] = $hash;
			$update_result = $model -> table('ad_affix_tmp') -> where($where) -> save($data);
		}elseif($from == 2){
			$where_edit['agreement_id'] = $id;
			$where_edit['status'] = 1;
			$update_result = $model -> table('ad_affix_tmp') -> where($where_edit) -> save($data);
		}
		if($update_result){
			$tmp_result = $model -> table('ad_affix_tmp') -> where(array('affix_hash' => $hash,'status' => 1)) -> select();
			echo json_encode($result);
			return json_encode($result);
		}
	}
    
    
    // 录入保证金
    public function add_account() {
        if ($_POST) {
            $AdFrameworkModel = D("sendNum.AdFramework");
            $account_input = trim($_POST['account_input']);
            $account_input_time = trim($_POST['account_input_time']);
            $remark = trim($_POST['remark']);
            
            $framework_id = trim($_POST['framework_id']);
            
            if (!$account_input) {
                $this->error("请输入收款金额");
            }
            if (!$account_input_time) {
                $this->error("请输入收款日期");
            }
            
            if (!$framework_id) {
                $this->error("无法添加");
            }
            
            $data = array();
            $now = time();
            $data['account_input'] = $account_input;
            $data['account_input_time'] = strtotime($account_input_time);
            $data['framework_id'] = $framework_id;
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            if ($remark) {
                $data['remark'] = $remark;
            }
            $data['status'] = 1;
            
            $ret = $AdFrameworkModel->add_account($data);
            if ($ret) {
                $this->writelog("运营管理-广告结算-框架协议：录入id为{$ret}的保证金记录");
                $this->success("录入成功！");
            } else {
                $this->error("录入失败！");
            }
            
        } else {
            $AdFrameworkModel = D("sendNum.AdFramework");
            $id = $_GET['id'];
            
            $find = $AdFrameworkModel->getFramework($id);
            $find['client_name'] = $AdFrameworkModel->getClientName($find['client_id']);
            
            $this->assign('record', $find);
            $this->display();
        }
    }
    
    // 编辑保证金
    public function edit_account() {
        if ($_POST) {
            $AdFrameworkModel = D("sendNum.AdFramework");
            $id = trim($_POST['id']);
            $account_input = trim($_POST['account_input']);
            $account_input_time = trim($_POST['account_input_time']);
            $remark = trim($_POST['remark']);
            
            if (!$account_input) {
                $this->error("请输入收款金额");
            }
            if (!$account_input_time) {
                $this->error("请输入收款日期");
            }
            
            $data = array();
            $now = time();
            $data['account_input'] = $account_input;
            $data['account_input_time'] = strtotime($account_input_time);
            $data['update_time'] = $now;
            
            $data['remark'] = $remark ? $remark : '';
            
            $ret = $AdFrameworkModel->edit_account($id, $data);
            if ($ret || $ret === 0) {
                $this->writelog("运营管理-广告结算-框架协议：编辑了id为{$id}的保证金记录");
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
            
        } else {
            $AdFrameworkModel = D("sendNum.AdFramework");
            // 找到保证金记录所属framework_id
            $id = $_GET['id'];
            $record = $AdFrameworkModel->get_account_record($id);
            // 找到framework相关资料
            $framework_id = $record['framework_id'];
            $framework = $AdFrameworkModel->getFramework($framework_id);
            $record['framework_number'] = $framework['framework_number'];
            $record['client_name'] = $AdFrameworkModel->getClientName($framework['client_id']);
            
            $this->assign('record', $record);
            $this->display();
        }
    }
    
    // 删除保证金
    public function delete_account() {
        $id = $_GET['id'];
        $AdFrameworkModel = D("sendNum.AdFramework");
        $ret = $AdFrameworkModel->delete_account($id);
        if ($ret) {
            $this->writelog("运营管理-广告结算-框架协议：删除了id为{$id}的保证金记录");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    // 添加发票/票据
    public function add_invoice() {
        if ($_POST) {
            $AdFrameworkModel = D("sendNum.AdFramework");
            $data_arr = array();
            // 将post过来的数据大致归组
            foreach ($_POST as $key => $value) {
                $reg_arr = array(
                    'invoice_input' => '/^invoiceValue_(\d+)/',
                    'invoice_input_time' => '/^addTime_(\d+)/',
                    'invoice_number' => '/^invoiceNumber_(\d+)/',
                );
                foreach ($reg_arr as $reg_key => $reg_value) {
                    if (preg_match($reg_value, $key, $matches)) {
                        $data_key = $matches[1];
                        $data_arr[$data_key][$reg_key] = $value;
                        break;
                    }
                }
            }
            // 票据类型
            $invoice_type = $_POST['invoice_type'];
            if (!$invoice_type) {
                $this->error("请输入票据类型");
            }
            // 备注
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
            // 默认数据
            $framework_id = trim($_POST['framework_id']);
            $now = time();
            
            // 换成每行数据
            $map_arr = array();
            $i = 0;
            foreach ($data_arr as $data_value) {
                $invoice_input = trim($data_value['invoice_input']);
                $invoice_input_time = strtotime(trim($data_value['invoice_input_time']));
                $invoice_numbers = trim($data_value['invoice_number']);
                if ($invoice_input == '') {
                    $this->error('单张发票/票据金额不能为空');
                }
                if ($invoice_input_time == '') {
                    $this->error('开票日期不能为空');
                }
                if ($invoice_numbers == '') {
                    $this->error('发票/票据号不能为空');
                }
                $number_arr = explode("\r\n", $invoice_numbers);
                foreach ($number_arr as $number) {
                    if (!$number)
                        continue;
                    $map_arr[$i]['invoice_type'] = $invoice_type;
                    $map_arr[$i]['invoice_number'] = $number;
                    $map_arr[$i]['invoice_input'] = $invoice_input;
                    $map_arr[$i]['invoice_input_time'] = $invoice_input_time;
                    $map_arr[$i]['remark'] = $remark;
                    $map_arr[$i]['framework_id'] = $framework_id;
                    $map_arr[$i]['create_time'] = $now;
                    $map_arr[$i]['update_time'] = $now;
                    $map_arr[$i]['status'] = 1;
                    $i++;
                }
            }
            
            // 存储每一条数据
            $ret_arr = array();
            foreach ($map_arr as $map) {
                $ret = $AdFrameworkModel->add_invoice($map);
                if (!$ret) {
                    // 将此事务添加成功的数据删除
                    foreach ($ret_arr as $id) {
                        $AdFrameworkModel->delete_invoice($id);
                    }
                    $this->error('录入发票/票据失败！');
                } else {
                    $ret_arr[] = $ret;
                }
            }
            $ret = implode(',', $ret_arr);
            $this->writelog("运营管理-广告结算-框架协议：录入了id为{$ret}的发票/票据记录");
            $this->success('录入发票/票据成功！');
            
        } else {
            $AdFrameworkModel = D("sendNum.AdFramework");
            $id = $_GET['id'];
            
            $find = $AdFrameworkModel->getFramework($id);
            $find['client_name'] = $AdFrameworkModel->getClientName($find['client_id']);
            
            $this->assign('record', $find);
            $this->display();
        }
    }
    
    // 编辑发票/票据
    function edit_invoice() {
        if ($_POST) {
            $AdFrameworkModel = D("sendNum.AdFramework");
            $id = $_POST['id'];
            $invoice_input = floatval(trim($_POST['invoice_input']));
            $invoice_input_time = trim($_POST['invoice_input_time']);
            $remark = trim($_POST['remark']);
            
            if (!$invoice_input) {
                $this->error("请输入发票金额");
            }
            
            $data = array();
            $data['invoice_input'] = $invoice_input;
            $data['invoice_input_time'] = strtotime($invoice_input_time);
            $now = time();
            $data['update_time'] = $now;
            $data['remark'] = $remark ? $remark : '';
            $ret = $AdFrameworkModel->edit_invoice($id, $data);
            if ($ret) {
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else if ($_GET) {
            $AdFrameworkModel = D("sendNum.AdFramework");
            // 找到保证金记录所属framework_id
            $id = $_GET['id'];
            $record = $AdFrameworkModel->get_invoice_record($id);
            // 找到framework相关资料
            $framework_id = $record['framework_id'];
            $framework = $AdFrameworkModel->getFramework($framework_id);
            $record['framework_number'] = $framework['framework_number'];
            $record['client_name'] = $AdFrameworkModel->getClientName($framework['client_id']);
            
            $this->assign('record', $record);
            $this->display();
        }
    }
    
    // 删除发票/票据
    public function delete_invoice() {
        $id = $_GET['id'];
        $AdFrameworkModel = D("sendNum.AdFramework");
        $ret = $AdFrameworkModel->delete_invoice($id);
        if ($ret) {
            $this->writelog("运营管理-广告结算-框架协议：删除了id为{$id}的发票/票据记录");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    // 结算录入详情
    public function add_account_detail() {
        $framework_id = $_GET['id'];
        // 获得记录列表
        $AdFrameworkModel = D("sendNum.AdFramework");
        $list = $AdFrameworkModel->add_account_detail_list($framework_id);
        
        if ($_GET['down']) {
            $this->exportExcel_add_account_detail($list);
        } else {
            // 获得已付款总数
            $account_input_sum = $AdFrameworkModel->get_total_input_account($framework_id);
            // 等抵扣保证金
            $left_account = $AdFrameworkModel->get_left_account($framework_id);
            
            $this->assign('list', $list);
            $this->assign('account_input_sum', $account_input_sum);
            $this->assign('left_account', $left_account);
            // 返回合同号和月份，以便结算录入
            $this->assign('framework_id', $framework_id);
            $this->display('');
        }
    }
    
    
    // 发票/票据录入详情
    public function add_invoice_detail() {
        $framework_id = $_GET['framework_id'];
        // 获得记录列表
        $AdFrameworkModel = D("sendNum.AdFramework");
        $list = $AdFrameworkModel->add_invoice_detail_list($framework_id);
        
        if ($_GET['down']) {
            $this->exportExcel_add_invoice_detail($list);
        } else {
            // 获得已开发票总数
            $invoice_input_sum_1 = $AdFrameworkModel->get_total_input_invoice($framework_id, 1);
            // 已开票据总数
            $invoice_input_sum_2 = $AdFrameworkModel->get_total_input_invoice($framework_id, 2);
            // 待抵扣发票
            $left_invoice = $AdFrameworkModel->get_left_invoice($framework_id);
            $this->assign('list', $list);
            $this->assign('invoice_input_sum_1', $invoice_input_sum_1);
            $this->assign('invoice_input_sum_2', $invoice_input_sum_2);
            $this->assign('left_invoice', $left_invoice);
            $this->assign('framework_id', $framework_id);
            $this->display('');
        }
    }
    
    function exportExcel_add_account_detail($list) {
        require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        // 设置数据
        $sheet->setCellValue('A1',"序号");
        $sheet->setCellValue('B1',"录入时间");
        $sheet->setCellValue('C1',"最近一次编辑时间");
        $sheet->setCellValue('D1',"收款金额");
        $sheet->setCellValue('E1',"收款日期");
        $sheet->setCellValue('F1',"备注");
        
        $row_map = array('A', 'B', 'C', 'D', 'E', 'F');
        $key_map = array('list_number', 'create_time', 'update_time', 'account_input', 'account_input_time', 'remark');
        $key_map2 = array();
        foreach ($key_map as $key => $value) {
            $key_map2[$value] = $key;
        }
        foreach ($list as $i => $record) {
            $ii = $i + 2;
            foreach ($record as $key => $value) {
                if (strpos($key, '_time') !== false) {
                    if ($value) {
                        // 时间戳转成字符串
                        $value = date("Y-m-d H:i:s", $value);
                    }
                }
                if (!$value)
                    $value = "-";
                $j = $key_map2[$key]+1;
                $col = $row_map[$j-1];
                $pos = "{$col}{$ii}";
                $sheet->setCellValue($pos, $value);
            }
        }
        
        // 设置样式
        $style = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FFFF0000'),
                ),
            ),
            'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
              'wrap'       => true
            )
        );
        $style_obj = new PHPExcel_Style();
        $style_obj->applyFromArray($style);
        $span_line = count($list) + 1;
        $span = "A1:L" . $span_line;
        $sheet->setSharedStyle($style_obj, $span);
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . urlencode('结算录入详情') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
    // 导出
    private function exportExcel_add_invoice_detail($list) {
        require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        // 设置数据
        $sheet->setCellValue('A1',"序号");
        $sheet->setCellValue('B1',"录入时间");
        $sheet->setCellValue('C1',"最近一次编辑时间");
        $sheet->setCellValue('D1',"票据金额");
        $sheet->setCellValue('E1',"票据类型");
        $sheet->setCellValue('F1',"开票日期");
        $sheet->setCellValue('G1',"发票号");
        $sheet->setCellValue('H1',"备注");
        
        $row_map = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $key_map = array('list_number', 'create_time', 'update_time', 'invoice_input', 'invoice_type', 'invoice_input_time', 'invoice_number', 'remark');
        $key_map2 = array();
        foreach ($key_map as $key => $value) {
            $key_map2[$value] = $key;
        }
        foreach ($list as $i => $record) {
            $ii = $i + 2;
            foreach ($record as $key => $value) {
                if ($key == 'invoice_type') {
                    if ($value == 1) {
                        $value = '发票';
                    } else {
                        $value = '票据';
                    }
                }
                if (strpos($key, '_time') !== false) {
                    if ($value) {
                        // 时间戳转成字符串
                        $value = date("Y-m-d H:i:s", $value);
                    }
                }
                if (!$value)
                    $value = "-";
                $j = $key_map2[$key]+1;
                $col = $row_map[$j-1];
                $pos = "{$col}{$ii}";
                $sheet->setCellValue($pos, $value);
            }
        }
        
        // 设置样式
        $style = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FFFF0000'),
                ),
            ),
            'alignment' => array(
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
              'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
              'wrap'       => true
            )
        );
        $style_obj = new PHPExcel_Style();
        $style_obj->applyFromArray($style);
        $span_line = count($list) + 1;
        $span = "A1:L" . $span_line;
        $sheet->setSharedStyle($style_obj, $span);
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . urlencode('发票录入详情') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
}

?>