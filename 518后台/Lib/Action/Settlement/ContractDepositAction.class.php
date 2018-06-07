<?php

/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：caihuan
 * 结算详情
 * ----------------------------------------------------------------------------
 */
class ContractDepositAction extends CommonAction {

    // 结算详情--收款
    public function receives_list() {
        //取得contract_id
        $contract_id = trim($_GET['contract_id']);

        $Receives_db = D("Settlement.ContractReceive");
        $Contract_db = D("Settlement.Contract");

        // 2014.9.17 jiwei
        // 获取合同数据
        $get_data = $Contract_db->where(array('id' => $contract_id))->select();
        //$last_cash = $get_data[0]['received_total'] - $get_data[0]['no_received_total'];
        $contract = $Contract_db->find($contract_id);
        // END


        $map['contract_id'] = array('eq', $contract_id);
        import("@.ORG.Page");
        $count = $Receives_db->where($map)->order("create_tm desc,collection_date desc,collection_cash desc")->count();
        $Page = new Page($count, 50);
        $lists = $Receives_db->where($map)->order("create_tm desc,collection_date desc,collection_cash desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if ($_GET['export'] == "1") {
            $this->export_deposit($lists,"合同列表_收款详情".date('Y-m-d-h-i').".csv", 'receives');
        }
        $this->assign("lists", $lists);
        $this->assign("contract_id", $contract_id);

        // 2014.9.17 jiwei
        // 处理合同数据
        // $this->assign("last_cash", $last_cash);
        $this->assign("get_data", $get_data);
        $this->assign("contract", $contract);
        // END

        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    // 录入收款
    public function add_receive_show() {
        if ($_POST) {
            $data['contract_id'] = trim($_POST['contract_id']);
            //$data['client_id'] = trim($_POST['client_id']);

            $data['collection_date'] = trim($_POST['collection_date']);
            $data['collection_cash'] = trim($_POST['collection_cash']);
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            if (!is_numeric($data['collection_cash'])) {
                $this->error("收款必须是数字！");
            }
            $data['admin_id'] = $_SESSION['admin']['admin_id'];
            $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
            $data['create_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);

            //准备写入数据
            $Receive_db = D("Settlement.ContractReceive");
            //开启事物
            $Receive_db->startTrans();
            $result = $Receive_db->add($data);
            if ($result) {
                // 2014.9.16 jiwei
                // 加入相关表的统计字段更新
                $m_contract = D('Settlement.Contract');
                $_contract = $m_contract->find($data['contract_id']);
                $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                        ->save(array('update_tm' => time(), 'received_total' => $_contract['received_total'] + $data['collection_cash']));
                if (!$m_contract_) {
                    $Receive_db->rollback();
                    $this->error("录入收款失败！");
                }
                // END
                $this->writelog("广告结算：合同-新录入了contract_id为[" . $data['contract_id'] . "]的收款。【广告结算】", 'sj_contract_receives',$data['contract_id'],__ACTION__ ,'','add');
                $Receive_db->commit();
                $this->success("录入收款成功！");
            } else {
                $Receive_db->rollback();
                $this->error("添加失败");
            }

            $this->redirect("receives_list");
        } else {
            $contract_id = trim($_GET['contract_id']);
            $this->assign("contract_id", $contract_id);
            $this->display();
        }
    }

    // 编辑收款
    public function edit_receive_show() {
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['collection_cash'] = trim($_POST['collection_cash']);
            if (!is_numeric($data['collection_cash'])) {
                $this->error("收款必须是数字！");
            }
            $data['collection_date'] = trim($_POST['collection_date']);
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法添加！");
                }
            }
            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();

            $Receive_db = D("Settlement.ContractReceive");
            //开启事物
            $Receive_db->startTrans();
            $receive_old_collection_cash1 = $Receive_db->where(array('id' => $data['id']))->select();
            $receive_old_collection_cash = $receive_old_collection_cash1[0]['collection_cash'];
            $m_data = $Receive_db->save($data);
            if ($m_data) {

                // 2014.9.16 jiwei
                // 加入相关表的统计字段更新
                $m_contract = D('Settlement.Contract');
                $_contract = $m_contract->find($data['contract_id']);
                $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                        ->save(array('update_tm' => time(), 'received_total' => $_contract['received_total'] - $receive_old_collection_cash + $data['collection_cash']));
                if (!$m_contract_) {
                    $Receive_db->rollback();
                    $this->error("编辑收款失败！");
                }
                // END

                $this->writelog("广告结算：合同-编辑了广告录入收款，id为[" . $data['id'] . "]，收款金额由[" . $receive_old_collection_cash . "] 变为[" . $data['collection_cash'] . "]", 'sj_contract_receives',$data['id'],__ACTION__ ,'','edit');
                $Receive_db->commit();
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/ContractDeposit/receives_list/contract_id/' . $data['contract_id']);
                $this->success("编辑成功！");
            } else {

                $Receive_db->rollback();
                $this->error('编辑失败');
            }
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                $this->error("id不存在");
            }
            $Receive_db = D("Settlement.ContractReceive");
            $data = $Receive_db->where(array('id' => $id))->select();
            $this->assign("data", $data[0]);
            $this->display();
        }
    }

    // 删除收款
    public function delete_receive_show() {
        //获取ID号
        $map['id'] = trim($_GET['id']);
        $map['contract_id'] = trim($_GET['contract_id']);
        $Receive_db = D("Settlement.ContractReceive");
        //启动事务
        $Receive_db->startTrans();

        // 2014.9.16 jiwei
        // 获取要删除收款的金额
        $_receive = $Receive_db->find($map['id']);
        // END

        if (!$Receive_db->where($map)->delete()) {
            //删除失败！
            $Receive_db->rollback();
            $this->error("删除失败！");
        }

        // 2014.9.16 jiwei
        // 加入相关表的统计字段更新
        $m_contract = D('Settlement.Contract');
        $_contract = $m_contract->find($map['contract_id']);
        $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                ->save(array('update_tm' => time(), 'received_total' => $_contract['received_total'] - $_receive['collection_cash']));
        if (!$m_contract_) {
            //删除出错，回滚
            $Receive_db->rollback();
            $this->error("删除出错！");
        }
        // END

        $this->writelog("广告结算：合同-删除了id为[" . $map['id'] . "]的收款记录。【广告结算】", 'sj_contract_receives',$map['id'],__ACTION__ ,'','del');
        $Receive_db->commit();
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/ContractDeposit/receives_list/contract_id/' . $map['contract_id']);
        $this->success("删除成功！");
    }

    // 发票详情--发票
    public function invoices_list() {
        //取得contract_id
        $contract_id = trim($_GET['contract_id']);
        //根据contract_id取出所有发票收据的流水
        $Invoice_db = D("Settlement.ContractInvoice");
        $Contract_db = D("Settlement.Contract");

        // 2014.9.17 jiwei
        // 获取合同数据
        $get_data = $Contract_db->where(array('id' => $contract_id))->select();
        $contract = $Contract_db->find($contract_id);
        // END

        $contract_code = $get_data[0]['contract_code'];
        $map['contract_id'] = array('eq', $contract_id);
        import("@.ORG.Page");
        $count = $Invoice_db->where($map)->order("create_tm desc,invoice_date desc,invoice_cash desc")->count();
        $Page = new Page($count, 50);
        $lists = $Invoice_db->where($map)->order("create_tm desc,invoice_date desc,invoice_cash desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        if ($_GET['export'] == "1") {
            $this->export_deposit($lists,"合同列表_发票详情".date('Y-m-d-h-i').".csv", 'invoices');
        }
        $this->assign("lists", $lists);
        $this->assign("contract_id", $contract_id);

        // 2014.9.17 jiwei
        // 处理合同数据
        $this->assign("get_data", $get_data);
        $this->assign("contract", $contract);
        // END

        $this->assign("contract_code", $contract_code);
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    // 添加发票
    public function add_invoice_show() {
        if ($_POST) {
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['invoice_date'] = trim($_POST['invoice_date']);
            $data['invoice_cash'] = trim($_POST['invoice_cash']);


            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            if (!is_numeric($data['invoice_cash'])) {
                $this->error("发票金额必须是数字！");
            }
            $code = trim($_POST['invoice_code']);
            $tmp = str_ireplace("\r\n", "", $code);
            if (!isset($tmp) || $tmp == "") {
                $this->error("发票或收据号没有录入！");
            }

            $data['admin_id'] = $_SESSION['admin']['admin_id'];
            $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
            $data['create_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);
            $split_code = split("\r\n", $code);
            $count = 0;

            //准备写入数据
            $Invoice_db = D("Settlement.ContractInvoice");
            //启动事务
            $Invoice_db->startTrans();

            foreach ($split_code as $key => $s) {
                if ($s != "" && isset($s) && !empty($s)) {
                    $data['invoice_code'] = $s;
                    if (!$Invoice_db->add($data)) {
                        //添加出错，回滚
                        $Invoice_db->rollback();
                        $this->error("添加失败");
                    } else {
                        // 2014.9.16 jiwei
                        // 加入相关表的统计字段更新
                        $m_contract = D('Settlement.Contract');
                        $_contract = $m_contract->find($data['contract_id']);
                        $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                                ->save(array('update_tm' => time(), 'invoiced_total' => $_contract['invoiced_total'] + $data['invoice_cash']));
                        if (!$m_contract_) {
                            //出错，回滚
                            $Invoice_db->rollback();
                            $this->error("添加出错！");
                        }
                        // END
                    }
                    $count ++;
                }
            }
            $this->writelog("广告结算：合同-新录入了contract_id为[" . $data['contract_id'] . "]的发票。【广告结算】", 'sj_contract_invoices',$data['contract_id'],__ACTION__ ,'','add');
            $Invoice_db->commit();
            $this->success("录入发票成功！");
        } else {
            $contract_id = $_GET['contract_id'];
            $this->assign('contract_id', $contract_id);
            $this->display();
        }
    }

    // 编辑发票
    function edit_invoice_show() {
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['invoice_cash'] = trim($_POST['invoice_cash']);
            if (!is_numeric($data['invoice_cash'])) {
                $this->error("收款必须是数字！");
            }
            $data['invoice_date'] = trim($_POST['invoice_date']);
            $data['invoice_code'] = trim($_POST['invoice_code']);

            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法添加！");
                }
            }
            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();

            $Invoice_db = D("Settlement.ContractInvoice");
            //启动事务
            $Invoice_db->startTrans();
            $invoice_old_collection_cash1 = $Invoice_db->where(array('id' => $data['id']))->select();
            $invoice_old_collection_cash = $invoice_old_collection_cash1[0]['invoice_cash'];
            $m_data = $Invoice_db->save($data);
            //录入成功。提交操作，写日志
            if ($m_data) {

                // 2014.9.16 jiwei
                // 加入相关表的统计字段更新
                $m_contract = D('Settlement.Contract');
                $_contract = $m_contract->find($data['contract_id']);
                $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                        ->save(array('update_tm' => time(), 'invoiced_total' => $_contract['invoiced_total'] - $invoice_old_collection_cash + $data['invoice_cash']));
                if (!$m_contract_) {
                    //失败，回滚
                    $Invoice_db->rollback();
                    $this->error("编辑出错！");
                }
                // END

                $this->writelog("广告结算：合同-编辑了保证金收款，id为[" . $data['id'] . "]，收款金额由[" . $invoice_old_collection_cash . "] 变为[" . $data['invoice_cash'] . "]", 'sj_contract_invoices',$data['id'],__ACTION__ ,'','edit');
                $Invoice_db->commit();
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/ContractDeposit/invoices_list/contract_id/' . $data['contract_id']);
                $this->success("编辑成功！");
            } else {
                $Invoice_db->rollback();
                $this->error('编辑失败');
            }
        } else {

            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                $this->error("id不存在");
            }
            $Invoice_db = D("Settlement.ContractInvoice");
            $data = $Invoice_db->where(array('id' => $id))->select();
            $this->assign("data", $data[0]);
            $this->display();
        }
    }

    // 删除发票
    public function delete_invoice_show() {
        //获取ID号
        $map['id'] = trim($_GET['id']);
        $map['contract_id'] = trim($_GET['contract_id']);
        $Invoice_db = D("Settlement.ContractInvoice");
        //启动事务
        $Invoice_db->startTrans();

        // 2014.9.16 jiwei
        // 获取要删除发票的金额
        $_invoice = $Invoice_db->find($map['id']);
        // END

        if (!$Invoice_db->where($map)->delete()) {
            //删除失败！
            $Invoice_db->rollback();
            $this->error("删除失败！");
        }

        // 2014.9.16 jiwei
        // 加入相关表的统计字段更新
        $m_contract = D('Settlement.Contract');
        $_contract = $m_contract->find($map['contract_id']);
        $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                ->save(array('update_tm' => time(), 'invoiced_total' => $_contract['invoiced_total'] - $_invoice['invoice_cash']));
        if (!$m_contract_) {
            //失败，回滚
            $Invoice_db->rollback();
            $this->error("删除出错！");
        }
        // END

        $this->writelog("广告结算：合同-删除了id为[" . $map['id'] . "]的发票记录。【广告结算】", 'sj_contract_invoices',$map['contract_id'],__ACTION__ ,'','del');
        $Invoice_db->commit();
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/ContractDeposit/invoices_list/contract_id/' . $map['contract_id']);
        $this->success("删除成功！");
    }

    // 结算详情--保证金抵扣
    public function deposits_list() {
        $contract_id = trim($_GET['contract_id']);
        $Receive_db = D("Settlement.ContractDeposit");
        $Contract_db = D("Settlement.Contract");

        // 2014.9.17 jiwei
        // 获取合同数据和框架协议数据
        $get_data = $Contract_db->where(array('id' => $contract_id))->select();
        $contract = $Contract_db->find($contract_id);
        $m_agreement = D('Settlement.Agreement');
        $agreement = $m_agreement->find($contract['agreement_id']);
        // END

        $contract_code = $get_data[0]['contract_code'];
        import("@.ORG.Page");
        $map['contract_id'] = array('eq', $contract_id);
        $count = $Receive_db->where($map)->count();
        $Page = new Page($count, 50);
        $lists = $Receive_db->where($map)->order("create_tm DESC,deduct_date DESC,deduct_cash DESC")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //导出报表
        if ($_GET['export'] == '1') {
            $this->export_deposit($lists,"合同列表_保证金详情".date('Y-m-d-h-i').".csv");
        }
        $this->assign("lists", $lists);
        $this->assign("contract_id", $contract_id);

        // 2014.9.17 jiwei
        // 获取合同数据和框架协议数据
        $this->assign("get_data", $get_data);
        $this->assign("contract", $contract);
        $this->assign("agreement", $agreement);
        // END

        $this->assign("contract_code", $contract_code);
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    // 保证金抵扣
    public function add_deposit_show() {
        if ($_POST) {
            //
            

            $data['contract_id'] = trim($_POST['contract_id']);
            $data['deduct_date'] = date("Y-m-d", time());           
            $data['deduct_cash'] = trim($_POST['deduct_cash']);
            $daidikoushoukuan = str_replace(',','',trim($_POST['daidikoushoukuan']));
            if (!is_numeric($data['deduct_cash'])) {
                $this->error("保证金额必须是数字！");
            }

            if ($data['deduct_cash'] > $daidikoushoukuan) {
                $this->error("保证金额要小于等于待抵扣保证金收款！");
            }
            foreach ($data as $d) {
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法录入！");
                }
            }
            $data['admin_id'] = $_SESSION['admin']['admin_id'];
            $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
            $data['create_tm'] = time();
            $data['update_tm'] = time();
            $data['remark'] = trim($_POST['remark']);

            //准备写入数据
            $Deposit_db = D("Settlement.ContractDeposit");
            //启动事务
            $Deposit_db->startTrans();
            $result = $Deposit_db->add($data);
            if ($result) {
                // 2014.9.16 jiwei
                // 加入相关表的统计字段更新
                $m_contract = D('Settlement.Contract');
                $_contract = $m_contract->find($data['contract_id']);
                $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                        ->save(array('update_tm' => time(), 'deposited_total' => $_contract['deposited_total'] + $data['deduct_cash']));
                if (!$m_contract_) {
                    //失败，回滚
                    $Deposit_db->rollback();
                    $this->error("添加出错！");
                }
                // END
                // 2014.9.17 jiwei
                // 处理框架协议保证金余额变化
                $m_agreement = D('Settlement.Agreement');
                $_agreement = $m_agreement->find($_contract['agreement_id']);
                $m_agreement_ = $m_agreement->where(array('id' => $_agreement['id']))
                        ->save(array('update_tm' => time(), 'deductible_cash' => $_agreement['deductible_cash'] - $data['deduct_cash']));
                if (!$m_agreement_) {
                    //失败，回滚
                    $Deposit_db->rollback();
                    $this->error("添加出错！");
                }
                // END

                $this->writelog("广告结算：合同-新录入了contract_id为[" . $data['contract_id'] . "]的保证金详情。【广告结算】", 'sj_contract_deposits',$data['contract_id'],__ACTION__ ,'','add');
                $Deposit_db->commit();
                $this->success("录入保证金详情成功！");
            } else {
                $Deposit_db->rollback();
                $this->error("添加失败");
            }

            $this->redirect("deposits_list");
        } else {
            $contract_id = $_GET['contract_id'];

            $Contract_db = D("Settlement.Contract");
            $contract = $Contract_db->find($contract_id);

            $agreement_db = D("Settlement.Agreement");
            $Invoice_db = D("Settlement.AgreementInvoice");
            $get_data1 = $Contract_db->where(array("id" => $contract_id))->select();
            $agreement_id = $get_data1[0]["agreement_id"];
            $get_data2 = $agreement_db->where(array('id' => $agreement_id, 'status' => 1))->select();
            $daidikoushoukuan = $get_data2[0]['deductible_cash'];

            $agreement_lists['fapiao'] = $Invoice_db->where("invoice_type= 1 and agreement_id=" . $agreement_id)->sum("invoice_cash");
            $agreement_lists['shouju'] = $Invoice_db->where("invoice_type= 2 and agreement_id=" . $agreement_id)->sum("invoice_cash");
            $agreement_lists1 = $agreement_lists['fapiao'] + $agreement_lists['shouju'];


            $this->assign('daidikoushoukuan', $daidikoushoukuan);
            $this->assign('agreement_lists1', $agreement_lists1);
            $this->assign('contract', $contract);

            $this->assign('contract_id', $contract_id);
            $this->display();
        }
    }

    // 编辑保证金抵扣
    public function edit_deposit_show() {
        if ($_POST) {
            $data['id'] = trim($_POST['id']);
            $data['contract_id'] = trim($_POST['contract_id']);
            $data['deduct_cash'] = trim($_POST['deduct_cash']);

            if (!is_numeric($data['deduct_cash'])) {
                $this->error("保证金额必须是数字！");
            }
            $daidikoushoukuan = str_replace(',','',trim($_POST['daidikoushoukuan']));
            if ($data['deduct_cash'] > $daidikoushoukuan) {
                $this->error("保证金额要小于等于待抵扣保证金发票金额 ！");
            }
            $data['deduct_date'] = date("Y-m-d", time());           
            foreach ($data as $d){
                if (!isset($d) || $d == "") {
                    $this->error("数据不全，无法添加！");
                }
            }
            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();

            $Deposit_db = D("Settlement.ContractDeposit");
            //启动事务
            $Deposit_db->startTrans();
            $deposit_old_collection_cash1 = $Deposit_db->where(array('id' => $data['id']))->select();
            $deposit_old_collection_cash = $deposit_old_collection_cash1[0]['deduct_cash']; //2014.9.17 jiwei 修复原程序的一处bug，之前没加[0]索引
            $m_data = $Deposit_db->save($data);
            //录入成功。提交操作，写日志
            if ($m_data) {

                // 2014.9.16 jiwei
                // 加入相关表的统计字段更新
                $m_contract = D('Settlement.Contract');
                $_contract = $m_contract->find($data['contract_id']);
                $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                        ->save(array('update_tm' => time(), 'deposited_total' => $_contract['deposited_total'] - $deposit_old_collection_cash + $data['deduct_cash']));
                if (!$m_contract_) {
                    //出错，回滚
                    $Deposit_db->rollback();
                    $this->error("编辑出错！");
                }
                // END
                // 2014.9.17 jiwei
                // 处理框架协议保证金余额变化
                $m_agreement = D('Settlement.Agreement');
                $_agreement = $m_agreement->find($_contract['agreement_id']);
                $m_agreement_ = $m_agreement->where(array('id' => $_agreement['id']))
                        ->save(array('update_tm' => time(), 'deductible_cash' => $_agreement['deductible_cash'] + $deposit_old_collection_cash - $data['deduct_cash']));
                if (!$m_agreement_) {
                    //出错，回滚
                    $Deposit_db->rollback();
                    $this->error("编辑出错！");
                }
                // END

                $this->writelog("广告结算：合同-编辑了广告录入保证金详情，id为[" . $data['id'] . "]，收款金额由[" . $deposit_old_collection_cash . "] 变为[" . $data['deduct_cash'] . "]", 'sj_contract_deposits',$data['id'],__ACTION__ ,'','edit');
                $Deposit_db->commit();
                $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/ContractDeposit/deposits_list/contract_id/' . $data['contract_id']);
                $this->success("编辑成功！");
            } else {
                $Deposit_db->rollback();
                $this->error('编辑失败');
            }
        } else {
            $id = trim($_GET['id']);
            if (!isset($id) || $id == "") {
                $this->error("id不存在");
            }
            $contract_id = trim($_GET['contract_id']);
            if (!isset($contract_id) || $contract_id == "") {
                $this->error("contract_id不存在");
            }

            //根据id取得client_id
            $Deposit_db = D("Settlement.ContractDeposit");
            $contract_db = D("Settlement.Contract");
            $agreement_db = D("Settlement.Agreement");
            $Invoice_db = D("Settlement.AgreementInvoice");
            $get_data1 = $contract_db->where(array("id" => $contract_id))->select();
            $agreement_id = $get_data1[0]["agreement_id"];
            $get_data2 = $agreement_db->where(array('id' => $agreement_id, 'status' => 1))->select();
            $daidikoushoukuan = $get_data2[0]['deductible_cash'];

            $agreement_lists['fapiao'] = $Invoice_db->where("invoice_type= 1 and agreement_id=" . $agreement_id)->sum("invoice_cash");
            $agreement_lists['shouju'] = $Invoice_db->where("invoice_type= 2 and agreement_id=" . $agreement_id)->sum("invoice_cash");
            $agreement_lists1 = $agreement_lists['fapiao'] + $agreement_lists['shouju'];


            $data = $Deposit_db->where(array('id' => $id))->select();
            $this->assign('contract_id', $contract_id);
            $this->assign('daidikoushoukuan', $daidikoushoukuan);
            $this->assign('agreement_lists1', $agreement_lists1);
            $this->assign("data", $data[0]);
            $this->display();
        }
    }

    //删除保证金抵扣
    public function delete_deposit_show() {
        //获取ID号
        $map['id'] = trim($_GET['id']);
        $map['contract_id'] = trim($_GET['contract_id']);
        $Deposit_db = D("Settlement.ContractDeposit");
        //启动事务
        $Deposit_db->startTrans();

        // 2014.9.16 jiwei
        // 获取要删除的保证金金额
        $_deposit = $Deposit_db->find($map['id']);
        // END

        if (!$Deposit_db->where($map)->delete()) {
            //删除失败！
            $Deposit_db->rollback();
            $this->error("删除失败！");
        }

        // 2014.9.16 jiwei
        // 加入相关表的统计字段更新
        $m_contract = D('Settlement.Contract');
        $_contract = $m_contract->find($map['contract_id']);
        $m_contract_ = $m_contract->where(array('id' => $_contract['id']))
                ->save(array('update_tm' => time(), 'deposited_total' => $_contract['deposited_total'] - $_deposit['deduct_cash']));
        if (!$m_contract_) {
            //删除出错，回滚
            $Deposit_db->rollback();
            $this->error("删除出错！");
        }
        // END
        // 2014.9.17 jiwei
        // 处理框架协议保证金余额变化
        $m_agreement = D('Settlement.Agreement');
        $_agreement = $m_agreement->find($_contract['agreement_id']);
        $m_agreement_ = $m_agreement->where(array('id' => $_agreement['id']))
                ->save(array('update_tm' => time(), 'deductible_cash' => $_agreement['deductible_cash'] + $_deposit['deduct_cash']));
        if (!$m_agreement_) {
            //删除出错，回滚
            $Deposit_db->rollback();
            $this->error("删除出错！");
        }
        // END


        $this->writelog("广告结算：合同-删除了id为[" . $map['id'] . "]的保证金抵扣记录。【广告结算】", 'sj_contract_deposits',$map['id'],__ACTION__ ,'','del');
        $Deposit_db->commit();
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/ContractDeposit/deposits_list/contract_id/' . $map['contract_id']);
        $this->success("删除成功！");
    }

    //导出
    public function export_deposit($lists, $filename,$category = "deposits") {
        
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何信息');
        } else {
            if ($category == "deposits") {
                $str = iconv('utf-8', 'gb2312', '序号') . ",".iconv('utf-8', 'gb2312', '保证金ID')."," . iconv('utf-8', 'gb2312', '录入时间') . "," . iconv('utf-8', 'gb2312', '最后一次编辑时间') . "," . iconv('utf-8', 'gb2312', '抵扣金额') . "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            } else if ($category == "invoices") {
                $str = iconv('utf-8', 'gb2312', '序号') . ",".iconv('utf-8', 'gb2312', '发票ID')."," . iconv('utf-8', 'gb2312', '录入时间') . "," . iconv('utf-8', 'gb2312', '最后一次编辑时间') . "," . iconv('utf-8', 'gb2312', '票据金额') . "," . iconv('utf-8', 'gb2312', '开票日期') . "," . iconv('utf-8', 'gb2312', '发票') . "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            } else {
                $str = iconv('utf-8', 'gb2312', '序号') . ",".iconv('utf-8', 'gb2312', '收款ID')."," . iconv('utf-8', 'gb2312', '录入时间') . "," . iconv('utf-8', 'gb2312', '最后一次编辑时间') . "," . iconv('utf-8', 'gb2312', '收款金额') . "," . iconv('utf-8', 'gb2312', '开票日期') . "," . iconv('utf-8', 'gb2312', '备注') . "\r\n";
            }
            foreach ($lists as $key => $val) {
                if ($category == "deposits") {
                    $str.= iconv('utf-8', 'gb2312', $key + 1) . ",".$val['id']."," . date("Y-m-d H:i:s", $val['create_tm']) . "," . date("Y-m-d H:i:s", $val['update_tm']) . "," . iconv('utf-8', 'gb2312', $val['deduct_cash']) . "," . iconv('utf-8', 'gb2312', $val['remark']) . "\r\n";
                } else if ($category == "invoices") {
                    $str.= iconv('utf-8', 'gb2312', $key + 1) . ",".$val['id']."," . date("Y-m-d H:i:s", $val['create_tm']) . "," . date("Y-m-d H:i:s", $val['update_tm']) . "," . iconv('utf-8', 'gb2312', $val['invoice_cash']) . "," . iconv('utf-8', 'gb2312', $val['invoice_date']) . "," . iconv('utf-8', 'gb2312', $val['invoice_code']) . "," . iconv('utf-8', 'gb2312', $val['remark']) . "\r\n";
                } else {
                    $str.= iconv('utf-8', 'gb2312', $key + 1) . ",".$val['id']."," . date("Y-m-d H:i:s", $val['create_tm']) . "," . date("Y-m-d H:i:s", $val['update_tm']) . "," . iconv('utf-8', 'gb2312', $val['collection_cash']) . "," . iconv('utf-8', 'gb2312', $val['collection_date']) . "," . iconv('utf-8', 'gb2312', $val['remark']) . "\r\n";
                }
            }
        }
        echo $str;
        exit;
    }

}
