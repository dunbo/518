<?php
class AdAccountInputAction extends CommonAction {
    public function index_from_contract() {
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $where = array();
        $url_param = '';
        // 搜索条件
        $search_contract_number = trim($_GET['search_contract_number']);
        $search_client_name = trim($_GET['search_client_name']);
        $search_start_month = trim($_GET['search_start_month']);
        $search_end_month = trim($_GET['search_end_month']);
        
        if ($search_contract_number) {
            $where['contract_number'] = $search_contract_number;
            $url_param .= "&search_contract_number={$search_contract_number}";
            $this->assign('search_contract_number', $search_contract_number);
        }
        if ($search_client_name) {
            $where['client_name'] = $search_client_name;
            $url_param .= "&search_client_name={$search_client_name}";
            $this->assign('search_client_name', $search_client_name);
        }
        if (!$search_start_month) {
            // 搜索的开始时间默认为一年前当前月份
            $last_year_month = date("Y-m",strtotime("-1 year"));
            $search_start_month = $last_year_month;
            $url_param .= "&search_start_month={$last_year_month}";
        } else {
            $url_param .= "&search_start_month={$search_start_month}";
        }
        $this->assign('search_start_month', $search_start_month);
        if (!$search_end_month) {
            $search_end_month = '2038-01';
            $url_param .= "&search_end_month=2038-01";
        } else {
            $url_param .= "&search_end_month={$search_end_month}";
            $this->assign('search_end_month', $search_end_month);
        }
        $where['month'] = array(array('egt', $search_start_month), array('elt', $search_end_month), 'and');
        // status = 1
        $where['ad_tj_soft.status'] = 1;
        
        // 计算符合条件数据总条数
        $all_group_list = $AdAccountInputModel->getAllGroupListFromContract($where);
        $count = count($all_group_list);
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show();//分页显示输出
        // 生成列表
        $group_list = $AdAccountInputModel->getGroupListFromContract($where, $page->firstRow, $page->listRows);
        
        // 开始统计数据
        $group_list = $this->process_group_list_from_contract($group_list);
                    
        if ($_GET['down']) {
            $all_group_list = $this->process_group_list_from_contract($all_group_list);
            $this->exportExcel_index_from_contract($all_group_list);
        } else {
            $this->assign('url_param', $url_param);
            $this->assign('group_list', $group_list);
            $this->assign('page', $show);
            $this->display();
        }
    }
    
    // 统计数据
    private function process_group_list_from_contract($group_list) {
        foreach ($group_list as $group_id => $group_record) {
            $now = time();
            $contract_id = $group_record['contract_id'];
            $month = $group_record['month'];
            
            // 查找每份合同每个月的软件
            $group_list[$group_id]['expected_account'] = 0;
            
            $ContractModel = D("sendNum.Contract");
            $soft_list = $ContractModel->getSoftlistbymonth($contract_id, $month);
            // 合作软件包
            $softs = array();
            $schedule_arr = array();
            foreach ($soft_list as $soft) {
                $package = $soft['package'];
                $softs[$package][] = $soft['ad_time'];
                $group_list[$group_id]['expected_account'] += $soft['zprice'];
                // 根据包名，区间，时间去找是否已投放了软件
                $ret = $ContractModel->is_scheduled($soft);
                if ($ret)
                    $schedule_arr[$group_id]['scheduled'][$package] += 1;
                else if ($soft['ad_time'] <= $now)
                    $schedule_arr[$group_id]['unscheduled_past'][$package] += 1;
                else
                    $schedule_arr[$group_id]['unscheduled_feature'][$package] += 1;
            }
            
            // 合作软件数
            $soft_count = count($softs);
            $group_list[$group_id]['soft_count'] = $soft_count;
            
            foreach ($schedule_arr as $group_id => $record) {
                $group_list[$group_id]['scheduled'] = count($record['scheduled']);
                $group_list[$group_id]['unscheduled_past'] = count($record['unscheduled_past']);
                $group_list[$group_id]['unscheduled_feature'] = count($record['unscheduled_feature']);
            }
            
            // 查找已付款金额、已开发票金额
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $group_list[$group_id]['payed_account'] = $AdAccountInputModel->get_total_input_account($contract_id, $month);
            $group_list[$group_id]['payed_invoice'] = $AdAccountInputModel->get_total_input_invoice($contract_id, $month);
            
            $group_list[$group_id]['unpayed_account'] = $group_list[$group_id]['expected_account'] - $group_list[$group_id]['payed_account'];
            $group_list[$group_id]['unpayed_invoice'] = $group_list[$group_id]['expected_account'] - $group_list[$group_id]['payed_invoice'];
            
            // 如果有框架协议，则看还有多少保证金可以抵扣，这样页面中此记录才会有对应的保证金抵扣入口
            $AdFrameworkModel = D("sendNum.AdFramework");
            $framework_id = $group_record['framework_id'];
            if ($framework_id) {
                // 实际可抵扣
                $group_list[$group_id]['left_amount'] = $AdFrameworkModel->get_left_amount($framework_id);
            }
        }
        return $group_list;
    }
    
    // 导出
    private function exportExcel_index_from_contract($group_list) {
        require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        // 设置数据
        $range = "A1:A2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('A1',"月份");
        $range = "B1:B2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('B1',"客户名称");
        $range = "C1:C2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('C1',"合同编号");
        $range = "D1:D2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('D1',"合作软件");
        $range = "E1:G1";
        $sheet->mergeCells($range);
        $sheet->setCellValue('E1',"广告排期");
        $sheet->setCellValue('E2',"已排期");
        $sheet->setCellValue('F2',"未到期未排");
        $sheet->setCellValue('G2',"已到期未排");
        $range = "H1:I1";
        $sheet->mergeCells($range);
        $sheet->setCellValue('H1',"发票");
        $sheet->setCellValue('H2',"已开发票");
        $sheet->setCellValue('I2',"未开发票");
        $range = "J1:K1";
        $sheet->mergeCells($range);
        $sheet->setCellValue('J1',"付款");
        $sheet->setCellValue('J2',"已付款");
        $sheet->setCellValue('K2',"未付款");
        $range = "L1:L2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('L1',"折扣后总价");
        
        $row_map = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        $key_map = array('month', 'client_name', 'contract_number', 'soft_count', 'scheduled', 'unscheduled_feature', 'unscheduled_past', 'payed_invoice', 'unpayed_invoice', 'payed_account', 'unpayed_account', 'expected_account');
        $key_map2 = array();
        foreach ($key_map as $key => $value) {
            $key_map2[$value] = $key;
        }
        foreach ($group_list as $i => $record) {
            $ii = $i + 3;
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
        $span_line = count($group_list) + 2;
        $span = "A1:L" . $span_line;
        $sheet->setSharedStyle($style_obj, $span);
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . urlencode('结算录入表_按合同') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
    // 一个合同一个月的合作软件签订排期列表
    public function ad_detail_percontract() {
        // 根据contract_id和month来查找合作软件
        $contract_id = $_GET['contract_id'];
        $month = $_GET['month'];
        
        //$tj_softs = $this->ad_detail_percontract_permonth($contract_id, $month);
        $ContractModel = D("sendNum.Contract");
        $tj_softs = $ContractModel->ad_detail_permonth('contract_id', $contract_id, $month);
        
        $this->assign('softs', $tj_softs);
        $this->display('soft_ad_detail');
    }
    
    // 一个合同一个月的合作软件真实排期列表
    public function schedule_detail_percontract() {
        // 根据contract_id、month、schedule_type来查找详细排期
        $contract_id = $_GET['contract_id'];
        $month = $_GET['month'];
        $schedule_type = $_GET['schedule_type'];// 排期类型
        
        $ContractModel = D("sendNum.Contract");
        $tj_softs = $ContractModel->ad_detail_permonth('contract_id', $contract_id, $month, $schedule_type);
        
        $this->assign('softs', $tj_softs);
        $this->display('soft_ad_detail');
    }
    
    // 录入收款
    public function add_account() {
        if ($_POST) {
            $account_input = trim($_POST['account_input']);
            $account_input_time = trim($_POST['account_input_time']);
            $remark = trim($_POST['remark']);
            
            $contract_id = trim($_POST['contract_id']);
            $month = trim($_POST['month']);
            
            if (!$account_input) {
                $this->error("请输入收款金额");
            }
            
            if (!$contract_id && !$month) {
                $this->error("无法添加");
            }
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已付款总数
            $account_input_sum = $AdAccountInputModel->get_total_input_account($contract_id, $month);
            // 剩余的未支付/未开票的总数
            $account_input_rest = $expected_account - $account_input_sum;
            
            $data = array();
            $now = time();
            $data['contract_id'] = $contract_id;
            $data['month'] = $month;
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            if ($account_input) {
                $comp = bccomp($account_input, $account_input_rest, 2);
                if ($comp > 0) {
                    $this->error("输入金额不可超出剩余金额{$account_input_rest}");
                }
                if (!$account_input_time) {
                    $this->error("请输入收款时间");
                }
                $data['account_input'] = $account_input;
                $data['account_input_time'] = strtotime($account_input_time);
            }
            
            if ($remark) {
                $data['remark'] = $remark;
            }
            $data['status'] = 1;
            
            $ret = $AdAccountInputModel->add_account($data);
            if ($ret) {
                $this->writelog("运营管理-广告结算-结算录入：录入id为{$ret}的金额记录");
                $this->success("录入成功！");
            } else {
                $this->error("录入失败！");
            }
            
        } else {
            $contract_id = $_GET['contract_id'];
            $month = $_GET['month'];
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已付款总数
            $account_input_sum = $AdAccountInputModel->get_total_input_account($contract_id, $month);
            // 获得已开发票总数
            $invoice_input_sum = $AdAccountInputModel->get_total_input_invoice($contract_id, $month);
            // 将计算出来剩余的钱默认显示给用户
            $account_input_rest = $expected_account - $account_input_sum;
            $invoice_input_rest = $expected_account - $invoice_input_sum;
            $this->assign('account_input_rest', $account_input_rest);
            $this->assign('invoice_input_rest', $invoice_input_rest);
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->display();
        }
    }
    
    // 编辑结算录入
    public function edit_account() {
        if ($_POST) {
            $id = $_POST['id'];
            $account_input = floatval(trim($_POST['account_input']));
            $account_input_time = trim($_POST['account_input_time']);
            $remark = trim($_POST['remark']);
            
            $contract_id = trim($_POST['contract_id']);
            $month = trim($_POST['month']);
            
            if (!$account_input) {
                $this->error("请输入收款金额");
            }
            
            if (!$contract_id && !$month) {
                $this->error("无法添加");
            }
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已付款总数
            $account_input_sum = $AdAccountInputModel->get_total_input_account($contract_id, $month, $id);
            // 获得已开发票总数
            $invoice_input_sum = $AdAccountInputModel->get_total_input_invoice($contract_id, $month, $id);
            // 剩余的未支付/未开票的总数
            $account_input_rest = $expected_account - $account_input_sum;
            $invoice_input_rest = $expected_account - $invoice_input_sum;
            
            $data = array();
            $now = time();
            $data['contract_id'] = $contract_id;
            $data['month'] = $month;
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            if ($account_input) {
                $comp = bccomp($account_input, $account_input_rest, 2);
                if ($comp > 0) {
                    $this->error("输入金额不可超出剩余金额{$account_input_rest}");
                }
                if (!$account_input_time) {
                    $this->error("请输入收款时间");
                }
                $data['account_input'] = $account_input;
                $data['account_input_time'] = strtotime($account_input_time);
            }
            
            if ($remark) {
                $data['remark'] = $remark;
            }
            $data['status'] = 1;
            $ret = $AdAccountInputModel->edit_account($id, $data);
            if ($ret) {
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
            
        } else if ($_GET) {
            $id = $_GET['id'];
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $find = $AdAccountInputModel->get_account_record($id);
            $this->assign('record', $find);
            $this->display();
        }
    }
    
    public function delete_account() {
        $id = $_GET['id'];
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $ret = $AdAccountInputModel->delete_account($id);
        if ($ret) {
            $this->writelog("运营管理-广告结算-结算录入：删除了id为{$id}的金额记录");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    // 添加发票
    public function add_invoice() {
        if ($_POST) {
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
            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
            // 默认数据
            $contract_id = trim($_POST['contract_id']);
            $month = trim($_POST['month']);
            $now = time();
            
            // 换成每行数据
            $map_arr = array();
            $i = 0;
            foreach ($data_arr as $data_value) {
                $invoice_input = trim($data_value['invoice_input']);
                $invoice_input_time = strtotime(trim($data_value['invoice_input_time']));
                $invoice_numbers = trim($data_value['invoice_number']);
                if ($invoice_input == '') {
                    $this->error('单张发票金额不能为空');
                }
                if ($invoice_input_time == '') {
                    $this->error('开票日期不能为空');
                }
                if ($invoice_numbers == '') {
                    $this->error('发票号不能为空');
                }
                $number_arr = explode("\r\n", $invoice_numbers);
                foreach ($number_arr as $number) {
                    if (!$number)
                        continue;
                    $map_arr[$i]['invoice_number'] = $number;
                    $map_arr[$i]['invoice_input'] = $invoice_input;
                    $map_arr[$i]['invoice_input_time'] = $invoice_input_time;
                    $map_arr[$i]['remark'] = $remark;
                    $map_arr[$i]['contract_id'] = $contract_id;
                    $map_arr[$i]['month'] = $month;
                    $map_arr[$i]['create_time'] = $now;
                    $map_arr[$i]['update_time'] = $now;
                    $map_arr[$i]['status'] = 1;
                    $i++;
                }
            }
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已开发票总数
            $invoice_input_sum = $AdAccountInputModel->get_total_input_invoice($contract_id, $month);
            // 剩余的未开票的总数
            $invoice_input_rest = $expected_account - $invoice_input_sum;
            
            // 判断输入的总发票金额是否不大于未开票的总数
            $add_total = 0;
            foreach ($map_arr as $map) {
                $add_total += $map['invoice_input'];
            }
            $comp = bccomp($add_total, $invoice_input_rest, 2);
            if ($comp > 0) {
                $this->error("录入发票金额不能大于未开票数");
            }
            
            // 存储每一条数据
            $ret_arr = array();
            foreach ($map_arr as $map) {
                $ret = $AdAccountInputModel->add_invoice($map);
                if (!$ret) {
                    // 将此事务添加成功的数据删除
                    foreach ($ret_arr as $id) {
                        $AdAccountInputModel->delete_invoice($id);
                    }
                    $this->error('录入发票失败！');
                } else {
                    $ret_arr[] = $ret;
                }
            }
            $ret = implode(',', $ret_arr);
            $this->writelog("运营管理-广告结算-结算录入：录入了id为{$ret}的发票记录");
            $this->success('录入发票成功！');
            
        } else {
            $contract_id = $_GET['contract_id'];
            $month = $_GET['month'];
            
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->display();
        }
    }
    
    function edit_invoice() {
        if ($_POST) {
            $id = $_POST['id'];
            $invoice_input = floatval(trim($_POST['invoice_input']));
            $invoice_input_time = trim($_POST['invoice_input_time']);
            $remark = trim($_POST['remark']);
            // 记录的contract_id和month（用来读剩余可开发票数）的不可编辑，所以直接发送给页面再传回来，不用再读一次数据库
            $contract_id = trim($_POST['contract_id']);
            $month = trim($_POST['month']);
            
            if (!$invoice_input) {
                $this->error("请输入发票金额");
            }
            
            if (!$contract_id && !$month) {
                $this->error("无法添加");
            }
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已付款总数
            $account_input_sum = $AdAccountInputModel->get_total_input_account($contract_id, $month, $id);
            // 获得已开发票总数
            $invoice_input_sum = $AdAccountInputModel->get_total_input_invoice($contract_id, $month, $id);
            // 剩余的未支付/未开票的总数
            $account_input_rest = $expected_account - $account_input_sum;
            $invoice_input_rest = $expected_account - $invoice_input_sum;
            
            $data = array();
            $now = time();
            $data['update_time'] = $now;
            
            if ($invoice_input) {
                $comp = bccomp($invoice_input, $invoice_input_rest, 2);
                if ($comp > 0) {
                    $this->error("输入金额不可超出剩余金额{$invoice_input_rest}");
                }
                if (!$invoice_input_time) {
                    $this->error("请输入收款时间");
                }
                $data['invoice_input'] = $invoice_input;
                $data['invoice_input_time'] = strtotime($invoice_input_time);
            }
            
            if ($remark) {
                $data['remark'] = $remark;
            }
            $ret = $AdAccountInputModel->edit_invoice($id, $data);
            if ($ret) {
                $this->success("编辑成功！");
            } else {
                $this->error("编辑失败！");
            }
        } else if ($_GET) {
            $id = $_GET['id'];
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $find = $AdAccountInputModel->get_invoice_record($id);
            $this->assign('record', $find);
            $this->display();
        }
    }
    
    public function delete_invoice() {
        $id = $_GET['id'];
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $ret = $AdAccountInputModel->delete_invoice($id);
        if ($ret) {
            $this->writelog("运营管理-广告结算-结算录入：删除了id为{$id}的发票记录");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    // 保证金抵扣
    public function add_withhold() {
        if ($_POST) {
            $withhold_input = trim($_POST['withhold_input']);
            $remark = trim($_POST['remark']);
            
            $contract_id = trim($_POST['contract_id']);
            $month = trim($_POST['month']);
            
            if (!$withhold_input) {
                $this->error("请输入收款金额");
            }
            
            if (!$contract_id && !$month) {
                $this->error("无法添加");
            }
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $AdFrameworkModel = D("sendNum.AdFramework");
            // TODO, 获得所属框架的可抵扣保证金
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            $max_available_withhold = $AdFrameworkModel->get_left_amount($framework_id);
            
            $data = array();
            $now = time();
            $data['contract_id'] = $contract_id;
            $data['month'] = $month;
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            if ($withhold_input) {
                $comp = bccomp($withhold_input, $max_available_withhold, 2);
                if ($comp > 0) {
                    $this->error("输入金额不可超出剩余金额{$max_available_withhold}");
                }
                $data['withhold_input'] = $withhold_input;
            }
            
            if ($remark) {
                $data['remark'] = $remark;
            }
            $data['status'] = 1;
            
            $ret = $AdAccountInputModel->add_withhold($data);
            if ($ret) {
                $this->writelog("运营管理-广告结算-结算录入：添加了id为{$ret}的保证金抵扣记录");
                $this->success("保证金抵扣成功！");
            } else {
                $this->error("保证金抵扣失败！");
            }
            
        } else if ($_GET) {
            $contract_id = $_GET['contract_id'];
            $month = $_GET['month'];
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $AdFrameworkModel = D("sendNum.AdFramework");
            
            // TODO, 获得已交保证金总数
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            // 剩余可抵扣的保证金和发票
            $available_withhold = $AdFrameworkModel->get_left_account($framework_id);
            $available_withhold_invoice = $AdFrameworkModel->get_left_invoice($framework_id);
            // 实际可抵扣
            $max_available_withhold = $AdFrameworkModel->get_left_amount($framework_id);
            
            $this->assign('available_withhold', $available_withhold);
            $this->assign('available_withhold_invoice', $available_withhold_invoice);
            $this->assign('max_available_withhold', $max_available_withhold);
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->display();
        }
    }
    
    // 编辑保证金抵扣
    public function edit_withhold() {
        if ($_POST) {
            $id = $_POST['id'];
            $withhold_input = floatval(trim($_POST['withhold_input']));
            $remark = trim($_POST['remark']);
            
            $contract_id = trim($_POST['contract_id']);
            $month = trim($_POST['month']);
            
            if (!$withhold_input) {
                $this->error("请输入收款金额");
            }
            
            if (!$contract_id && !$month) {
                $this->error("无法添加");
            }
            
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $AdFrameworkModel = D("sendNum.AdFramework");
            
            // TODO, 获得所属框架的可抵扣保证金
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            $max_available_withhold = $AdFrameworkModel->get_left_amount($framework_id);
            // 最大可抵扣值需加上自身已抵扣数
            $find = $AdAccountInputModel->get_withhold_record($id);
            $max_available_withhold += $find['withhold_input'];
            
            $data = array();
            $now = time();
            $data['contract_id'] = $contract_id;
            $data['month'] = $month;
            $data['create_time'] = $now;
            $data['update_time'] = $now;
            
            if ($withhold_input) {
                $comp = bccomp($withhold_input, $max_available_withhold, 2);
                if ($comp > 0) {
                    $this->error("输入金额不可超出剩余金额{$max_available_withhold}");
                }
                $data['withhold_input'] = $withhold_input;
            }
            
            if ($remark) {
                $data['remark'] = $remark;
            }
            $ret = $AdAccountInputModel->edit_withhold($id, $data);
            if ($ret) {
                $this->success("保证金抵扣编辑成功！");
            } else {
                $this->error("保证金抵扣编辑失败！");
            }
            
        } else if ($_GET) {
            $ContractModel = D("sendNum.Contract");
            $AdAccountInputModel = D("sendNum.AdAccountInput");
            $AdFrameworkModel = D("sendNum.AdFramework");
            
            $id = $_GET['id'];
            $find = $AdAccountInputModel->get_withhold_record($id);
            
            $contract_id = $find['contract_id'];
            $month = $find['month'];
            
            // TODO, 获得已交保证金总数
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            $paid_withhold = $AdFrameworkModel->get_total_input_account($framework_id);
            $paid_withhold_invoice = $AdFrameworkModel->get_total_input_invoice($framework_id, 1);
            // 已用保证金抵扣数
            $used_withhold = $AdFrameworkModel->get_used_account($framework_id);
            // 剩余可抵扣的保证金和发票(加上当前记录已用的抵扣金额)
            $available_withhold = $paid_withhold - $used_withhold + $find['withhold_input'];
            $available_withhold_invoice = $paid_withhold_invoice - $used_withhold + $find['withhold_input'];
            
            $this->assign('record', $find);
            $this->assign('available_withhold', $available_withhold);
            $this->assign('available_withhold_invoice', $available_withhold_invoice);
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->display();
        }
    }
    
    public function delete_withhold() {
        $id = $_GET['id'];
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $ret = $AdAccountInputModel->delete_withhold($id);
        if ($ret) {
            $this->writelog("运营管理-广告结算-结算录入：删除了id为{$id}的保证金抵扣记录");
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
    
    // 结算录入详情
    public function add_account_detail() {
        $contract_id = $_GET['contract_id'];
        $month = $_GET['month'];
        // 获得记录列表
        $ContractModel = D("sendNum.Contract");
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $list = $AdAccountInputModel->add_account_detail_list($contract_id, $month);
        
        if ($_GET['down']) {
            $this->exportExcel_add_account_detail($list);
        } else {
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已付款总数
            $account_input_sum = $AdAccountInputModel->get_total_input_account($contract_id, $month);
            // 将计算出来剩余的钱默认显示给用户
            $account_input_rest = $expected_account - $account_input_sum;
            
            // 根据contract_id判断有没有框架协议（TODO, 封装成统一的方法，而且合同的框架协议如果被删除了的话，也应该是没有框架协议）
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            
            $this->assign('list', $list);
            $this->assign('account_input_sum', $account_input_sum);
            $this->assign('account_input_rest', $account_input_rest);
            // 返回合同号和月份，以便结算录入
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->assign('framework_id', $framework_id);
            $this->display('');
        }
    }
    
    // 发票录入详情
    public function add_invoice_detail() {
        $contract_id = $_GET['contract_id'];
        $month = $_GET['month'];
        // 获得记录列表
        $ContractModel = D("sendNum.Contract");
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $list = $AdAccountInputModel->add_invoice_detail_list($contract_id, $month);
        
        if ($_GET['down']) {
            $this->exportExcel_add_invoice_detail($list);
        } else {
            // 获得总钱数
            $expected_account = $ContractModel->get_total_price($contract_id, $month);
            // 获得已开发票总数
            $invoice_input_sum = $AdAccountInputModel->get_total_input_invoice($contract_id, $month);
            // 将计算出来剩余的钱默认显示给用户
            $invoice_input_rest = $expected_account - $invoice_input_sum;
            
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            
            $this->assign('list', $list);
            $this->assign('invoice_input_sum', $invoice_input_sum);
            $this->assign('invoice_input_rest', $invoice_input_rest);
            // 返回合同号和月份，以便结算录入
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->assign('framework_id', $framework_id);
            $this->display('');
        }
    }
    
    // 结算录入详情
    public function add_withhold_detail() {
        $contract_id = $_GET['contract_id'];
        $month = $_GET['month'];
        // 获得记录列表
        $ContractModel = D("sendNum.Contract");
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $AdFrameworkModel = D("sendNum.AdFramework");
        
        $list = $AdAccountInputModel->add_withhold_detail_list($contract_id, $month);
        
        if ($_GET['down']) {
            $this->exportExcel_add_withhold_detail($list);
        } else {
            // TODO, 获得已交保证金总数
            $framework_id = $ContractModel->getFrameworkId($contract_id);
            // 剩余可抵扣的保证金和发票
            $available_withhold = $AdFrameworkModel->get_left_account($framework_id);
            $available_withhold_invoice = $AdFrameworkModel->get_left_invoice($framework_id);
            
            // 该合同该月份已抵扣金额
            $current_used_withhold = $AdAccountInputModel->get_total_input_withhold($contract_id, $month);
            
            $this->assign('list', $list);
            $this->assign('current_used_withhold', $current_used_withhold);
            $this->assign('available_withhold', $available_withhold);
            $this->assign('available_withhold_invoice', $available_withhold_invoice);
            // 返回合同号和月份，以便结算录入
            $this->assign('contract_id', $contract_id);
            $this->assign('month', $month);
            $this->assign('framework_id', $framework_id);
            $this->display('');
        }
    }
    
    // 导出
    private function exportExcel_add_account_detail($list) {
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
        $sheet->setCellValue('E1',"开票日期");
        $sheet->setCellValue('F1',"发票号");
        $sheet->setCellValue('G1',"备注");
        
        $row_map = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
        $key_map = array('list_number', 'create_time', 'update_time', 'invoice_input', 'invoice_input_time', 'invoice_number', 'remark');
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
        header('Content-Disposition: attachment;filename="' . urlencode('发票录入详情') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
    // 导出
    private function exportExcel_add_withhold_detail($list) {
        require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        // 设置数据
        $sheet->setCellValue('A1',"序号");
        $sheet->setCellValue('B1',"录入时间");
        $sheet->setCellValue('C1',"最近一次编辑时间");
        $sheet->setCellValue('D1',"抵扣金额");
        $sheet->setCellValue('E1',"备注");
        
        $row_map = array('A', 'B', 'C', 'D', 'E');
        $key_map = array('list_number', 'create_time', 'update_time', 'withhold_input', 'remark');
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
        header('Content-Disposition: attachment;filename="' . urlencode('保证金抵扣详情') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
    // 检查收款金额是否合理
    public function check_account_input() {
        if ($_POST) {
            $id = 0;
            if ($_POST['id'])
                $id = $_POST['id'];
            $this->check_input_if_reasonable($_POST['contract_id'], $_POST['month'], 'account_input', $_POST['input'], $id);
        }
    }
    
    // 检查发票金额是否合理
    public function check_invoice_input() {
        if ($_POST) {
            $id = 0;
            if ($_POST['id'])
                $id = $_POST['id'];
            $this->check_input_if_reasonable($_POST['contract_id'], $_POST['month'], 'invoice_input', $_POST['input'], $id);
        }
    }
    
    // 检查金额/发票输入是否合理
    private function check_input_if_reasonable($contract_id, $month, $input_type, $input, $id=0) {
        $ContractModel = D("sendNum.Contract");
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        // 统计一下总钱数
        $sum_all = $ContractModel->get_total_price($contract_id, $month);
        if ($input_type == 'account_input')
            $sum_input = $AdAccountInputModel->get_total_input_account($contract_id, $month, $id);
        else
            $sum_input = $AdAccountInputModel->get_total_input_invoice($contract_id, $month, $id);
        // 最多只能填多少钱
        $max = $sum_all - $sum_input;
        // 判断所输入的金额是否超出最大可填值
        if ($input > $max) {
            if ($input_type == 'account_input')
                $hint = '收款金额';
            else
                $hint = '发票金额';
            $this->ajaxReturn(0, "{$hint}超出未付款金额", -1);
        } else {
            $this->ajaxReturn(0, "{$hint}合理", 0);
        }
    }
    
    public function check_withhold_input() {
        $contract_id = $_POST['contract_id'];
        $input = $_POST['input'];
        $id = $_POST['id'] ? $_POST['id'] : 0;
        $ret = $this->check_withhold_if_reasonable($contract_id, $input, $id);
        if ($ret != 0) {
            $this->ajaxReturn(0, "抵扣金额超出可用数", -1);
        } else {
            $this->ajaxReturn(0, "未超出", 0);
        }
    }
    
    private function check_withhold_if_reasonable($contract_id, $input, $id = 0) {
        // 根据contract_id找到该合同所属框架协议
        $ContractModel = D("sendNum.Contract");
        $framework_id = $ContractModel->getFrameworkId($contract_id);
        if (!$framework_id)
            return -2;
        
        // TODO, 获得已交保证金总数
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $AdFrameworkModel = D("sendNum.AdFramework");
        $left_amount = $AdFrameworkModel->get_left_amount($framework_id);
        // 如果有传id，则加上此id记录的抵扣值
        if ($id) {
            $find = $AdAccountInputModel->get_withhold_record($id);
            $left_amount += $find['withhold_input'];
        }
        
        return $input > $left_amount ? -1 : 0;
    }
    
    // 按频道
    public function index_from_channel() {
        $where = array();
        $url_param = '';
        // 搜索条件
        $search_channel = trim($_GET['search_channel']);
        $search_start_month = trim($_GET['search_start_month']);
        $search_end_month = trim($_GET['search_end_month']);
        
        if ($search_channel && $search_channel != '选择栏目') {
            $where['channel'] = $search_channel;
            $url_param .= "&search_channel={$search_channel}";
            $this->assign('search_channel', $search_channel);
        }
        
        if (!$search_start_month) {
            // 搜索的开始时间默认为一年前当前月份
            $last_year_month = date("Y-m",strtotime("-1 year"));
            $search_start_month = $last_year_month;
            $url_param .= "&search_start_month={$last_year_month}";
        } else {
            $url_param .= "&search_start_month={$search_start_month}";
        }
        $this->assign('search_start_month', $search_start_month);
        if (!$search_end_month) {
            $search_end_month = '2038-01';
            $url_param .= "&search_end_month=2038-01";
        } else {
            $url_param .= "&search_end_month={$search_end_month}";
            $this->assign('search_end_month', $search_end_month);
        }
        $where['month'] = array(array('egt', $search_start_month), array('elt', $search_end_month), 'and');
        // status = 1
        $where['status'] = 1;
        // 全量列表
        $AdAccountInputModel = D("sendNum.AdAccountInput");
        $all_group_list = $AdAccountInputModel->getAllGroupListFromChannel($where);
        $count = count($all_group_list);
        import("@.ORG.Page");
        $page = new Page($count, 10);
        $show = $page->show();//分页显示输出
        // 生成一页列表
        $group_list = $AdAccountInputModel->getGroupListFromChannel($where, $page->firstRow, $page->listRows);
        // 统计数量
        $group_list = $this->process_group_list_from_channel($group_list);
        // 统计合计
        $soft_count = 0;
        $scheduled = 0;
        $unscheduled_feature = 0;
        $unscheduled_past = 0;
        $expected_account = 0;
        foreach ($group_list as $key => $value) {
            $soft_count += $value['soft_count'];
            $scheduled += $value['scheduled'];
            $unscheduled_feature += $value['unscheduled_feature'];
            $unscheduled_past += $value['unscheduled_past'];
            $expected_account += $value['expected_account'];
        }
        
        // 获得栏目
        $ContractModel = D("sendNum.Contract");
        $channels = $ContractModel->getFirstNames();
        var_dump($channels);
        
        if ($_GET['down']) {
            // 对all_group_list进行处理
            $all_group_list = $this->process_group_list_from_channel($all_group_list);
            $this->exportExcel_index_from_channel($all_group_list);
        } else {
            $this->assign('channels', $channels);
            $this->assign('group_list', $group_list);
            $this->assign('soft_count', $soft_count);
            $this->assign('scheduled', $scheduled);
            $this->assign('unscheduled_feature', $unscheduled_feature);
            $this->assign('unscheduled_past', $unscheduled_past);
            $this->assign('expected_account', $expected_account);
            $this->assign('page', $show);
            $this->assign('url_param', $url_param);
            $this->display();
        }
    }
    
    private function process_group_list_from_channel($group_list) {
        foreach ($group_list as $group_id => $group_record) {
            $now = time();
            $channel = $group_record['channel'];
            $month = $group_record['month'];
            
            // 查找每份合同每个月的软件
            $group_list[$group_id]['expected_account'] = 0;
            
            $ContractModel = D("sendNum.Contract");
            $soft_list = $ContractModel->getSoftlistbychannelmonth($channel, $month);
            // 合作软件包
            $softs = array();
            $schedule_arr = array();
            foreach ($soft_list as $soft) {
                $package = $soft['package'];
                $softs[$package][] = $soft['ad_time'];
                $group_list[$group_id]['expected_account'] += $soft['zprice'];
                // 根据包名，区间，时间去找是否已投放了软件
                $ret = $ContractModel->is_scheduled($soft);
                if ($ret)
                    $schedule_arr[$group_id]['scheduled'][$package] += 1;
                else if ($soft['ad_time'] <= $now)
                    $schedule_arr[$group_id]['unscheduled_past'][$package] += 1;
                else
                    $schedule_arr[$group_id]['unscheduled_feature'][$package] += 1;
            }
            
            // 合作软件数
            $soft_count = count($softs);
            $group_list[$group_id]['soft_count'] = $soft_count;
            foreach ($schedule_arr as $group_id => $record) {
                $group_list[$group_id]['scheduled'] = count($record['scheduled']);
                $group_list[$group_id]['unscheduled_past'] = count($record['unscheduled_past']);
                $group_list[$group_id]['unscheduled_feature'] = count($record['unscheduled_feature']);
            }
        }
        return $group_list;
    }
    
    // 一个合同一个月的合作软件签订排期列表
    public function ad_detail_perchannel() {
        // 根据contract_id和month来查找合作软件
        $channel = $_GET['channel'];
        $month = $_GET['month'];
        
        $ContractModel = D("sendNum.Contract");
        $tj_softs = $ContractModel->ad_detail_permonth('channel', $channel, $month);
        
        $this->assign('softs', $tj_softs);
        $this->display('soft_ad_detail');
    }
    
    // 一个合同一个月的合作软件真实排期列表
    public function schedule_detail_perchannel() {
        // 根据contract_id、month、schedule_type来查找详细排期
        $channel = $_GET['channel'];
        $month = $_GET['month'];
        $schedule_type = $_GET['schedule_type'];// 排期类型
        
        $ContractModel = D("sendNum.Contract");
        $tj_softs = $ContractModel->ad_detail_permonth('channel', $channel, $month, $schedule_type);
        
        $this->assign('softs', $tj_softs);
        $this->display('soft_ad_detail');
    }
    
    // 导出
    private function exportExcel_index_from_channel($group_list) {
        require_once(dirname(__FILE__) . '/../../ORG/PHPExcel/PHPExcel.php');
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        // 设置数据
        $range = "A1:A2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('A1',"月份");
        $range = "B1:B2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('B1',"频道");
        $range = "C1:C2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('C1',"合作软件");
        $range = "D1:F1";
        $sheet->mergeCells($range);
        $sheet->setCellValue('D1',"广告排期");
        $sheet->setCellValue('D2',"已排期");
        $sheet->setCellValue('E2',"未到期未排");
        $sheet->setCellValue('F2',"已到期未排");
        $range = "G1:G2";
        $sheet->mergeCells($range);
        $sheet->setCellValue('G1',"折扣后总价");
        
        $row_map = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
        $key_map = array('month', 'channel', 'soft_count', 'scheduled', 'unscheduled_feature', 'unscheduled_past', 'expected_account');
        $key_map2 = array();
        foreach ($key_map as $key => $value) {
            $key_map2[$value] = $key;
        }
        foreach ($group_list as $i => $record) {
            $ii = $i + 3;
            foreach ($record as $key => $value) {
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
        $span_line = count($group_list) + 2;
        $span = "A1:G" . $span_line;
        $sheet->setSharedStyle($style_obj, $span);
        $sheet->getDefaultColumnDimension()->setWidth(15);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . urlencode('结算录入表_按频道') . '.xls');
        header('Cache-Control: max-age=0');
        $excelWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $excelWriter->save('php://output');
        exit;
    }
    
    
}
?>