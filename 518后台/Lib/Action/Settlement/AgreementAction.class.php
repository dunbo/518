<?php

/**
 * 安智网产品管理平台 广告结算控制器
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利。
 * 网站地址: http://www.goapk.com；
 * author：L&J
 * ----------------------------------------------------------------------------
 */
class AgreementAction extends CommonAction {

    /**
     * 框架协议列表
     */
    public function index() {
        //搜索条件
        $map["_string"] = "A.client_id = B.id";
        $map['A.status'] = array('eq', '1');
        //协议编号
        $agreement_code = trim($_GET['agreement_code']);
        if (isset($agreement_code) && $agreement_code != "") {
            $map['A.agreement_code'] = array('like', '%' . $agreement_code . '%');
            $this->assign('agreement_code', $agreement_code);
        }
        //客户名称
        $client_name = trim($_GET['client_name']);
        if (isset($client_name) && $client_name != "") {
            $map['B.client_name'] = array('like', '%' . $client_name . '%');
            $this->assign("client_name", $client_name);
        }
        //合作开始日期
        $co_date_start = trim($_GET['co_date_start']);
        if (isset($co_date_start) && $co_date_start != "") {
            $map['A.start_date'] = array('egt', $co_date_start);
            $this->assign("co_date_start", $co_date_start);
        }
        //合作结束日期
        $co_date_end = trim($_GET['co_date_end']);
        if (isset($co_date_end) && $co_date_end != "") {
            $map['A.end_date'] = array('elt', $co_date_end);
            $this->assign("co_date_end", $co_date_end);
        }
        //签订日期——小
        $sign_date_start = trim($_GET['sign_date_start']);
        $sign_date_end = trim($_GET['sign_date_end']);
        if (isset($sign_date_start) && $sign_date_start != "") {
            //签订日期——大
            if (isset($sign_date_end) && $sign_date_end != "") {
                $map['A.sign_date'] = array('between', $sign_date_start . "," . $sign_date_end);
                $this->assign("sign_date_end", $sign_date_end);
            } else {
                $map['A.sign_date'] = array('egt', $sign_date_start);
            }
            $this->assign("sign_date_start", $sign_date_start);
        } else {

            //签订日期——大
            if (isset($sign_date_end) && $sign_date_end != "") {
                $map['A.sign_date'] = array('elt', $sign_date_end);
                $this->assign("sign_date_end", $sign_date_end);
            }
        }

        //负责人
        $responsible = trim($_GET['responsible']);
        if (isset($responsible) && $responsible != "") {
            $map['A.responsible'] = array('like', '%' . $responsible . "%");
            $this->assign("responsible", $responsible);
        }
        //需要取出的列
        $fields = "A.id,
					   A.agreement_code,
					   B.client_name,
					   A.party_a,
					   A.party_b,
					   A.party_c,
					   A.start_date,
					   A.end_date,
					   A.sign_date,
					   A.responsible,
					   A.amount,
					   A.expect_deposit,
					   A.collection_cash,
					   A.deposit_balance,
					   A.deductible_cash,
					   A.contract_num,
					   A.remark
			";
        // $agreement_db = D("Settlement.Agreement");

        $agreement_db = new Model();
        //引入分页
        import("@.ORG.Page");
        $count = $agreement_db->table("settlement.ad_agreements A,settlement.ad_clients B")->field($fields)->where($map)->count();
        $Page = new Page($count, 50);
        $agreement_lists = $agreement_db->table("settlement.ad_agreements A,settlement.ad_clients B")->field($fields)->where($map)->order("A.sign_date DESC,CONVERT(B.client_name USING gbk) ASC")->limit($Page->firstRow . ',' . $Page->listRows)->select();

        /*
         * 根据agreement_id 取出发票以及票据，同时取出广告位
         */
        $Invoice_db = D("Settlement.AgreementInvoice");
        $Advert_db = D("Settlement.AgreementAdvert");
        foreach ($agreement_lists as $key => $a) {
            $agreement_lists[$key]['fapiao'] = $Invoice_db->where("invoice_type= 1 and agreement_id=" . $a['id'])->sum("invoice_cash");
            $agreement_lists[$key]['shouju'] = $Invoice_db->where("invoice_type= 2 and agreement_id=" . $a['id'])->sum("invoice_cash");
            $agreement_lists[$key]['chanels'] = $Advert_db->where("agreement_id = " . $a['id'])->field("advertising_name")->select();
        }
        if ($_GET['export'] == '1') {
            $this->export($agreement_lists,"协议列表_".date('Y-m-d-h-i').".csv");
        }
        $this->assign('agreement_lists', $agreement_lists);
        $this->assign('url_suffix', base64_encode($this->get_url_suffix(array('agreement_code', 'client_name', 'co_date_start', 'co_date_end', 'sign_date_start', 'sign_date_end', 'responsible', 'p', 'lr'))));
        $Page->setConfig('header', '条记录');
        $Page->setConfig('first', '<<');
        $Page->setConfig('last', '>>');
        $show = $Page->show();
        $this->assign("page", $show);
        $this->display();
    }

    /**
     * 导出csv文件
     */
    public function export($lists,$filename) {
        
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        $str = "";
        if (empty($lists)) {
            $str.=iconv('utf-8', 'gb2312', '没有任何节点信息');
        } else {
            echo iconv('utf-8', 'gb2312', '协议ID') . "," .
			iconv('utf-8', 'gb2312', '协议编号') . "," .
            iconv('utf-8', 'gb2312', '客户名称') . "," .
            iconv('utf-8', 'gb2312', '协议主体') . "," .
            iconv('utf-8', 'gb2312', '起始日期') . "," .
            iconv('utf-8', 'gb2312', '终止日期') . "," .
            iconv('utf-8', 'gb2312', '签订日期') . "," .
            iconv('utf-8', 'gb2312', '购买频道') . "," .
            iconv('utf-8', 'gb2312', '负责人') . "," .
            iconv('utf-8', 'gb2312', '合作金额') . "," .
            iconv('utf-8', 'gb2312', '预计保证金') . "," .
            iconv('utf-8', 'gb2312', '保证金余额') . "," .
            iconv('utf-8', 'gb2312', '待抵扣') . "," .
            iconv('utf-8', 'gb2312', '已开发票') . "," .
            iconv('utf-8', 'gb2312', '已开票据') . "," .
            iconv('utf-8', 'gb2312', '合同数量') . "," .
            iconv('utf-8', 'gb2312', '备注') .  "\r\n";
            foreach ($lists as $key => $val) {
				echo iconv('utf-8', 'gb2312', $val['id']) . ",";
                echo iconv('utf-8', 'gb2312', $val['agreement_code']) . ",";
                echo iconv('utf-8', 'gb2312', $val['client_name']) . ",";
                if (!empty($val['party_b'])) {
                    echo iconv('utf-8', 'gb2312', "乙：" . $val['party_b'] . "；");
                }
                if (!empty($val['party_c'])) {
                    echo iconv('utf-8', 'gb2312', "丙：" . $val['party_b']) . "";
                }
                if (empty($val['fapiao'])) {
                    $val['fapiao'] = 0;
                }
                if (empty($val['shouju'])) {
                    $val['shouju'] = 0;
                }
                echo ",";
                echo iconv('utf-8', 'gb2312', $val['start_date']) . ",";
                echo iconv('utf-8', 'gb2312', $val['end_date']) . ",";
                echo iconv('utf-8', 'gb2312', $val['sign_date']) . ',';
                foreach ($val['chanels'] as $c) {
                    echo iconv('utf-8', 'gb2312', $c['advertising_name'] . "；");
                }
                echo ",";
                echo iconv('utf-8', 'gb2312', $val['responsible']) . ",";
                echo iconv('utf-8', 'gb2312', $val['amount']) . ",";
                echo iconv('utf-8', 'gb2312', $val['expect_deposit']) . ",";
                echo iconv('utf-8', 'gb2312', $val['deposit_balance']) . ",";
                echo iconv('utf-8', 'gb2312', $val['deductible_cash']) . ",";
                echo iconv('utf-8', 'gb2312', $val['fapiao']) . ",";
                echo iconv('utf-8', 'gb2312', $val['shouju']) . ",";
                echo iconv('utf-8', 'gb2312', $val['contract_num']) . ",";
                echo iconv('utf-8', 'gb2312', $val['remark']) ."\r\n";
            }
        }

        exit;
    }

    /**
     * 添加框架协议——显示
     */
    public function add_agreement_show() {
        //取出所有的客户
        $client_db = D("Settlement.Client");
        
        //2014.12.12 jiwei
        //$client_lists = $client_db->where("status=1")->order("create_tm desc")->select();
        $client_lists = $client_db->where("status=1")->order("CONVERT(client_name USING gbk)")->select();
        //end
        
        //取出默认刊例id
        $RateCard_db = D("Settlement.RateCard");
        $rate_cards = $RateCard_db->where("status = 1 and is_disabled = 0 ")->order("create_tm desc")->select();
        //从广告为中取出频道
        $this->assign("client_lists", $client_lists);
        $this->assign('rate_cards', $rate_cards);
        $this->display();
    }

    /**
     * ajax通过刊例id获取广告位信息
     */
    public function ajax_get_advertising_info() {
        $rate_card_id = trim($_GET['rate_card_id']);
        if (!isset($rate_card_id)) {
            echo "<option value='-1'>未获取到刊例id</option>";
            exit();
        }
        $Advertising_db = D("Settlement.Advertising");
        $ads = $Advertising_db->where("rate_card_id=" . $rate_card_id)->order("create_tm desc")->select();
        if (empty($ads)) {
            echo "<option value='-1'>该刊例对应的广告位不存在！</option>";
            exit();
        }
        echo "<option value='-1'>选择广告位</option>";
        foreach ($ads as $ad) {
            echo "<option value='" . $ad['id'] . "'>" . $ad['advertising_name'] . "</option>";
        }
        exit();
    }

    /**
     * 添加框架协议——执行
     */
    public function add_agreement_do() {
        //检查数据来源
        if (!$this->isPost()) {
            $this->error("数据来源出错！");
        }
        //获取客户id
        $data['client_id'] = trim($_POST['client_id']);
        if ($data['client_id'] == "" || !isset($data['client_id']) || !$data['client_id']) {
            $this->error("未选择客户！");
        }
        //获取协议编号
        $data['agreement_code'] = trim($_POST['agreement_code']);
        if ($data['agreement_code'] == "" || !isset($data['agreement_code'])) {
            $this->error("协议编号不可为空！");
        }
        //检查协议编号是否存在
        $agreement_db = D("Settlement.Agreement");
        $map['agreement_code'] = $data['agreement_code'];
        $res = $agreement_db->where($map)->count();
        if ($res > 0) {
            //协议编号已存在
            $this->error("协议编号已经存在！");
        }
        //获取协议主体——乙方
        $data['party_b'] = trim($_POST['party_b']);
        if ($data['party_b'] == "" || !isset($data['party_b'])) {
            $this->error("协议主体（乙方）不可为空！");
        }
        //获取协议主体——丙方
        $data['party_c'] = trim($_POST['party_c']);
        $data['party_a'] = "安智";
        //获取签订日期
        $data['sign_date'] = trim($_POST['sign_date']);
        if ($data['sign_date'] == "" || !isset($data['sign_date'])) {
            $data['sign_date'] = date("Y-m-d", time());
        }
        //获取合作金额
        $data['amount'] = trim($_POST['amount']);
// 			if($data['amount'] == "" || !isset($data['amount']))
// 			{
// 				$this->error("合作金额不可为空！");
// 			}
        //获取起始日期
        $data['start_date'] = trim($_POST['start_date']);
        if ($data['start_date'] == "" || !isset($data['start_date'])) {
            $this->error("起始日期不可为空！");
        }
        //获取终止日期
        $data['end_date'] = trim($_POST['end_date']);
        if ($data['end_date'] == "" || !isset($data['end_date'])) {
            $this->error("终止日期不可为空！");
        }
        //获取终止日期
        $data['responsible'] = trim($_POST['responsible']);
        if ($data['responsible'] == "" || !isset($data['responsible'])) {
            $this->error("负责人不可为空！");
        }
        //获取保证金余额
        $data['deposit_balance'] = trim($_POST['deposit_balance']);
        if ($data['deposit_balance'] == "" || !isset($data['deposit_balance'])) {
            $this->error("保证金余额不可为空！");
        }
        //初始录入协议的时候待抵扣等于保证金余额
        $data['deductible_cash'] = $data['deposit_balance'];
        //获取预计保证金
        $data['expect_deposit'] = trim($_POST['expect_deposit']);
        //获取备注信息
        $data['remark'] = trim($_POST['remark']);
        $data['admin_id'] = $_SESSION['admin']['admin_id'];
        $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
        $data['update_tm'] = time();
        $data['create_tm'] = time();
        //获取附件数量
        $attachments_id = $_POST['attachment_id'];
        $attachment_count = count($attachments_id);
        $data['attachment_num'] = $attachment_count;
        //获取购买频道
        $chanels = $_POST['purchase_channel'];
        /*
        foreach ($chanels as $cls) {
            if ($cls == "-1" || !isset($cls)) {
                $this->error("未选择广告位！");
            }
        }*/
        //启动事务
        $agreement_db->startTrans();
        //准备保存数据
        $agreement_id = $agreement_db->add($data);
        if (!$agreement_id) {

            $this->error("框架协议添加失败！");
        }
        //将购买频道存储如数据表ad_agreement_adverts
        $Advert_db = D("Settlement.AgreementAdvert");

        $save['admin_id'] = $data['admin_id'];
        $save['admin_name'] = $data['admin_name'];
        $save['agreement_id'] = $agreement_id;
        $save['rate_card_id'] = trim($_POST['rate_card_id']);
        $save['client_id'] = $data['client_id'];
        $save['create_tm'] = time();
        $Advertisings_db = D("Settlement.Advertising");
        foreach ($chanels as $ad_id) {
            $advertising_name = $Advertisings_db->where("id=" . $ad_id)->getField("advertising_name");
            if (!isset($advertising_name) || $advertising_name == "") {
                //失败，回滚
                $agreement_db->rollback();
                $this->error("添加框架协议失败！");
            }
            $save['advertising_name'] = $advertising_name;
            $save['advertising_id'] = $ad_id;
            //保存
            if ($Advert_db->add($save) === false) {
                //失败，回滚
                $agreement_db->rollback();
                $this->error("添加框架协议失败！");
            }
        }
        //更新客户表，将相应客户的框架协议数量+1
        $client_db = D("Settlement.Client");
        $nwhere['id'] = array('eq', $data['client_id']);
        $client_judement = $client_lists = $client_db->where($nwhere)->setInc("agreement_num");
        if (!$client_judement) {
            $agreement_db->rollback();
            $this->error("添加框架协议失败！");
        }
        //更新附件表（ad_attachments），将框架协议id加进去
        if ($attachment_count > 0) {
            $attachment_db = D("Settlement.Attachment");
            foreach ($attachments_id as $id) {
                if (!$attachment_db->where("id= '" . $id . "' ")->setField('agreement_id', $agreement_id)) {
                    $agreement_db->rollback();
                    $this->error("添加框架协议失败！");
                }
            }
        }
        //事务提交
        $agreement_db->commit();
        //写日志
        $this->writelog("广告结算：添加了id为[" . $agreement_id . "]的框架协议", 'ad_agreements', $agreement_id,__ACTION__ ,'','add');
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Agreement/index' . base64_decode($_POST['url_suffix']));
        $this->success('添加框架协议议成功！');
    }

    /**
     * ajax检查协议编号是否唯一
     */
    public function ajax_check_agreement_code() {

        //数据来源错误
        if (!$this->isAjax() || !$this->isPost()) {
            echo "1";
            exit();
        }
        $code = trim($_POST['code']);
        $agreement_db = D("Settlement.Agreement");
        $map['agreement_code'] = $code;
        $res = $agreement_db->where($map)->count();
        if ($res > 0) {
            //协议编号已存在
            echo "2";
        } else {
            //协议编号不存在
            echo "0";
        }
        exit();
    }

    /**
     * ajax检查负责人是否存在
     */
    public function ajax_check_admin_name() {

        //数据来源错误
        if (!$this->isAjax() || !$this->isPost()) {
            echo "0";
            exit();
        }
        $name = trim($_POST['name']);
        $adminusers = M('admin_users');
        $map['admin_user_name'] = $name;
        $res = $adminusers->where($map)->count();
        if ($res > 0) {
            //负责人存在
            echo "1";
        } else {
            //负责人不存在
            echo "2";
        }
        exit();
    }

    /**
     * 编辑框架协议——显示
     */
    public function edit_agreement_show() {
        //取得id号
        $id = trim($_GET['id']);
        if (!isset($id) || $id == "") {
            $this->error("协议id不存在！");
        }
        //根据id取出数据
        $agreement_db = D("Settlement.Agreement");

        $list = $agreement_db->where("id=" . $id)->select();
        if (empty($list)) {
            $this->error("无此协议！");
        }
        //取出所有客户
        $client_db = D("Settlement.Client");
        $client_lists = $client_db->where("status=1")->order("create_tm desc")->select();
        /**
         * 取出最终频道广告的的流程
         * agreement_id -> advertising_id -> rate_card_id -> advertising_id + advertising_name
         */
        //根据协议id取出所有购买的频道id
        $Advert_db = D("Settlement.AgreementAdvert");
        $purchase_ad_ids = $Advert_db->where("agreement_id=" . $id)->field("advertising_id")->select();

        //用其中的一个广告id取出默认刊例id
        $Advertisings_db = D("Settlement.Advertising");
        $rate_card_id = $Advertisings_db->where("id=" . $purchase_ad_ids[0]['advertising_id'])->getField("rate_card_id");
        if (!isset($rate_card_id) || $rate_card_id == "") {
            //取出默认的刊例id
            $RateCard_db = D("Settlement.RateCard");
            $rate_card_id = $RateCard_db->where("is_defaulted = 1")->getField("id");
        }
        //根据刊例id取出该id下所有广告id以及广告名称
        $channels = $Advertisings_db->where("rate_card_id=" . $rate_card_id)->field("id,advertising_name")->select();

        //取出所有附件
        if ($list[0]['attachment_num'] > 0) {
            $attachment_db = D("Settlement.Attachment");
            $attachment_lists = $attachment_db->where("agreement_id=" . $list[0]['id'])->select();
        }
        //取出默认刊例id
        $RateCard_db = D("Settlement.RateCard");
        $rate_cards = $RateCard_db->where("status = 1 and is_disabled = 0 ")->order("create_tm desc")->select();
        $this->assign("client_lists", $client_lists);
        $this->assign("attachment_lists", $attachment_lists);
        $this->assign('channels', $channels);
        $this->assign('rate_cards', $rate_cards);
        $this->assign('rate_card_id', $rate_card_id);
        $this->assign('purchase_ad_ids', $purchase_ad_ids);
        $this->assign("list", $list[0]);
        $this->assign("url_suffix", trim($_GET['url_suffix']));
        $this->display();
    }

    /**
     * 编辑框架协议——执行
     */
    public function edit_agreement_do() {
        //只有来自post请求和拥有id和action=edit的才能进行操作
        $id = trim($_POST['id']);
        $action = trim($_POST['action']);
        if ($this->isPost() && isset($id) && $action == "edit") {

            //取得新上传附件数量
            $new_attachment_num = count($_POST['attachment_id']);
            //获取协议主体——乙方
            $data['party_b'] = trim($_POST['party_b']);
            if ($data['party_b'] == "" || !isset($data['party_b'])) {
                $this->error("协议主体（乙方）不可为空！");
            }
            //获取协议主体——丙方
            $data['party_c'] = trim($_POST['party_c']);
            //获取签订日期
            $data['sign_date'] = trim($_POST['sign_date']);
//                 if($data['sign_date'] == "" || !isset($data['sign_date']))
//                 {
//                     $this->error("签订日期不可为空！");
//                 }
            //获取合作金额
            $data['amount'] = trim($_POST['amount']);
//                 if($data['amount'] == "" || !isset($data['amount']))
//                 {
//                     $this->error("合作金额不可为空！");
//                 }
            //获取起始日期
            $data['start_date'] = trim($_POST['start_date']);
            if ($data['start_date'] == "" || !isset($data['start_date'])) {
                $this->error("起始日期不可为空！");
            }
            //获取终止日期
            $data['end_date'] = trim($_POST['end_date']);
            if ($data['end_date'] == "" || !isset($data['end_date'])) {
                $this->error("终止日期不可为空！");
            }
            //获取终止日期
            $data['responsible'] = trim($_POST['responsible']);
            if ($data['responsible'] == "" || !isset($data['responsible'])) {
                $this->error("负责人不可为空！");
            }
            //获取保证金余额
            $data['deposit_balance'] = trim($_POST['deposit_balance']);
            if ($data['deposit_balance'] == "" || !isset($data['deposit_balance'])) {
                $this->error("保证金余额不可为空！");
            }
            //判断购买频道是否为空
            $new_purchased_ads_ids = $_POST['purchase_channel'];
            if (empty($new_purchased_ads_ids)) {
                $this->error("必选选择购买频道");
            }
            //获取预计保证金
            $data['expect_deposit'] = trim($_POST['expect_deposit']);
            //获取备注信息
            $data['remark'] = trim($_POST['remark']);
            $data['update_tm'] = time();
            
            //准备更新数据
            $agreement_db = D("Settlement.Agreement");
            $old_attachment_num = $agreement_db->where("id=" . $id)->getField("attachment_num");
            $data['attachment_num'] = ($new_attachment_num + $old_attachment_num);
            
            // 2014.9.29 jiwei
            // 处理待抵扣金额变化
            $_agreement = $agreement_db->find($id);
            $data['deductible_cash'] = $_agreement['deductible_cash']-$_agreement['deposit_balance']+$data['deposit_balance'];
            // END
            
            $agreement_db->startTrans();
            $res_a = $agreement_db->where("id=" . $id)->save($data);
            if (!$res_a) {
                $agreement_db->rollback();
                $this->error("编辑框架协议失败！");
            }
            //根据agreement_id取出ad_agreement_adverts表中的rate_card_id，和新的rate_card_id对比
            $Advert_db = D("Settlement.AgreementAdvert");
            $old_rate_card_id = $Advert_db->where("agreement_id = " . $id)->getField("rate_card_id");
            $new_rate_card_id = $_POST['rate_card_id'];
            if ($new_rate_card_id == '-1') {
                $this->error("刊例id不存在！");
            }
            $Advertisings_db = D("Settlement.Advertising");
            if ($old_rate_card_id == $new_rate_card_id) {//刊例id不改变情况下的更新方法
                /*
                 * 更新框架协议的思路
                 * 首先根据协议id从表ad_agreement_adverts中取出之前所有的广告id，命名为：$old_purchased_ads_id
                 * 然后取出本次编辑的广告id，命名为：$new_purchased_ads_ids
                 * 获得$old_purchased_ads_id和$new_purchased_ads_ids的交集，命名为：$remain_ads_id。这部分是没有改变的广告id，不做任何更新
                 * 对$old_purchased_ads_id而言，凡不在$remain_ads_id里面的都删除；
                 * 对$new_purchased_ads_ids而言，凡不在$remain_ads_id里面的都新增进去。
                 */
                //取出上一次该协议的购买广告id

                $temp_ids = $Advert_db->where("agreement_id = '" . $id . "' ")->field("advertising_id")->select();
                foreach ($temp_ids as $i) {
                    $old_purchased_ads_id[] = $i['advertising_id'];
                }
                //取出$old_purchased_ads_id和$new_purchased_ads_ids的交集
                $remain_ads_id = array_intersect($old_purchased_ads_id, $new_purchased_ads_ids);

                //先执行删除
                $delete_map['agreement_id'] = array('eq', $id);
                if (!empty($remain_ads_id)) {
                    $delete_map['advertising_id'] = array('not in', $remain_ads_id);
                    $delete_affected_rows = $Advert_db->where($delete_map)->delete();
                    if ($delete_affected_rows === false) {
                        //操作失败，回滚
                        $agreement_db->rollback();
                        $this->error("编辑框架协议失败！");
                    }
                }

                //执行添加
                $new_save['admin_id'] = $_SESSION['admin']['admin_id'];
                $new_save['admin_name'] = $_SESSION['admin']['admin_user_name'];
                $new_save['agreement_id'] = $id;
                $new_save['create_tm'] = time();
                //取得客户id
                $new_save['client_id'] = $agreement_db->where("id=" . $id)->getField("client_id");

                foreach ($new_purchased_ads_ids as $new_ad_id) {
                    if (!in_array($new_ad_id, $remain_ads_id)) {
                        $new_save['advertising_id'] = $new_ad_id;
                        //根据new_ad_id取得advertising_name
                        $new_save['advertising_name'] = $Advertisings_db->where("id=" . $new_ad_id)->getField("advertising_name");
                        //准备保存
                        $tmp = $Advert_db->add($new_save);
                        if ($tmp === false) {
                            //操作失败，回滚
                            $agreement_db->rollback();
                            $this->error("编辑框架协议失败！");
                        }
                    }
                }
            } else {//刊例id改变了的情况下的更新方法
                /*
                 * 将之前数据表ad_agreement_adverts中的数据全删除，然后全新添加
                 */
                if ($Advert_db->where("agreement_id = " . $id)->delete() === false) {
                    //操作失败，回滚
                    $agreement_db->rollback();
                    $this->error("编辑框架协议失败！");
                }
                //重新添加
                $new_data['agreement_id'] = $id;
                $new_data['admin_id'] = $_SESSION['admin']['admin_id'];
                $new_data['rate_card_id'] = $new_rate_card_id;
                $new_data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                $new_data['client_id'] = $agreement_db->where('id=' . $id)->getField("client_id");
                $new_data['create_tm'] = time();
                foreach ($new_purchased_ads_ids as $ad_id) {
                    $advertising_name = $Advertisings_db->where("id=" . $ad_id)->getField("advertising_name");
                    if (!isset($advertising_name) || $advertising_name == "") {
                        //失败，回滚
                        $agreement_db->rollback();
                        $this->error("编辑框架协议失败！");
                    }
                    $new_data['advertising_name'] = $advertising_name;
                    $new_data['advertising_id'] = $ad_id;
                    //保存
                    if ($Advert_db->add($new_data) === false) {
                        //失败，回滚
                        $agreement_db->rollback();
                        $this->error("编辑框架协议失败！");
                    }
                }
            }
            if ($new_attachment_num > 0) {
                $attachment_db = D("Settlement.Attachment");

                foreach ($_POST['attachment_id'] as $aid) {
                    if (!$attachment_db->where("id=" . $aid)->setField("agreement_id", $id)) {
                        $agreement_db->rollback();
                        $this->error("编辑框架协议失败！");
                    }
                }
            }
            $agreement_db->commit();
            $this->writelog("广告结算：编辑id为[" . $id . "]的框架协议", 'ad_agreements', $id,__ACTION__ ,'','edit');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Agreement/index' . base64_decode($_POST['url_suffix']));
            $this->success('编辑框架协议议成功！');
        } else {
            $this->error("出错啦！");
        }
    }

    /**
     * 编辑备注_显示
     */
    public function edit_remark_show() {
        $id = trim($_GET['id']);
        if (!isset($id) || $id == "") {
            $this->error("协议id号不存在");
        }
        //根据id从数据库中取出数据
        $agreement_db = D("Settlement.Agreement");
        $list = $agreement_db->where("id=" . $id)->select();
        if (empty($list)) {
            $this->error("该条记录不存在！");
        }
        $this->assign("list", $list[0]);
        $this->assign("url_suffix", trim($_GET['url_suffix']));
        $this->display();
    }

    /**
     * 删除协议
     */
    public function delete_agreement() {
        $agreement_id = trim($_GET['id']);
        if (!isset($agreement_id) || $agreement_id == "") {
            $this->error("协议id号不存在！！");
        }
        $agreement_db = D("Settlement.Agreement");
        //取得客户id号，以便于将客户表中的框架数量减一
        $client_id = $agreement_db->where("id=" . $agreement_id)->getField("client_id");
        $agreement_db->startTrans();
        if (!$agreement_db->where("id=" . $agreement_id)->setField("status", '0')) {
            $agreement_db->rollback();
            $this->error("删除协议失败！");
        }
        //ad_clients 表中列“agreement_num”自减一
        $client_db = D("Settlement.Client");
        if (!$client_db->where("id=" . $client_id)->setDec("agreement_num")) {
            $agreement_db->rollback();
            $this->error("删除协议失败！");
        }
        $contract_db = D('Settlement.Contract');
        $save['status'] = 0;
        $contract_db->where("agreement_id=" . $agreement_id)->save($save);
        /*
         * 删除协议时假删除，故而显不处理附件
         * 需要删除附件的时候取消注释即可
         *
          //删除协议对应的附件
          $attachment_db = D("Settlement.Attachment");
          $attachments = $attachment_db -> where("agreement_id=".$agreement_id) -> select();
          if(!empty($attachments))
          {
          $file_root = "/data/att/518/settlement/";
          foreach ($attachments as $a)
          {
          //删除附件数据库中的记录
          if(!$attachment_db -> where("id=".$a['id']) -> delete())
          {
          $agreement_db -> rollback();
          $this->error("删除协议失败！");
          }
          unlink($file_root.$a['path'].$a['filename']);
          }
          }
         *
         */

        $agreement_db->commit();
        $this->writelog("广告结算：删除id为[" . $agreement_id . "]的框架协议", 'ad_agreements', $agreement_id,__ACTION__ ,'','del');
        $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Agreement/index' . base64_decode($_GET['url_suffix']));
        $this->success('框架协议删除成功！');
    }

    /**
     * 编辑备注_执行
     */
    public function edit_remark_do() {
        //获取id号
        $id = trim($_POST['id']);
        if (!isset($id) || $id == "") {
            $this->error("协议id不存在！");
        }
        //获取备注内容
        $remark = trim($_POST['remark']);
        //准备更新数据
        $agreement_db = D("Settlement.Agreement");
        $old_remark = $agreement_db->where("id=" . $id)->getField("remark");
        if ($remark == $old_remark) {
            $this->error("新备注与旧备注相同！");
        }
        if ($agreement_db->where("id=" . $id)->setField("remark", $remark)) {
            //更新成功！写入日记
            $this->writelog("广告结算：编辑了id为[" . $id . "]的框架协议备注，内容更新为[" . $remark . "]", 'ad_agreements', $id,__ACTION__ ,'','edit');
            $this->assign('jumpUrl', '/index.php/' . GROUP_NAME . '/Agreement/index' . base64_decode($_POST['url_suffix']));
            $this->success('备注编辑成功！！');
        } else {
            $this->error("更新备注失败！");
        }
    }



            /*导入数据*/
            public function import_data() 
            {
		import("@.ORG.UploadFile");
		$info = NULL;
		$upload = new UploadFile();
		$upload->maxSize = 3145728;
		$upload->allowExts = array('csv');
		$upload->savePath = '/tmp/';//'/data/att/518/settlement/';
		$upload->saveRule = 'time';
		
		if(!$upload->upload())
		{
			echo "<script>alert('".$upload->getErrorMsg()."');location.href='/index.php/Settlement/Agreement/index';</script>";
			exit;
		}
		else 
			$info = $upload->getUploadFileInfo();
		
		if(is_null($info))
		{
			echo "<script>alert('没有获得上传文件信息！');location.href='/index.php/Settlement/Agreement/index'</script>";
			exit;
		}

                // 处理数据 读取csv数据
                $handle = fopen($info[0]['savepath'].$info[0]['savename'], "r");
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

		$agreement_db = D("Settlement.Agreement");
                $Advertisings_db = D("Settlement.Advertising");
                $Advert_db = D("Settlement.AgreementAdvert");
                $client_db = D("Settlement.Client");
                
                
                $error_ids = '';
                $succ_ids = '';

                foreach($content_arr as $v){
                        $stop=0;
                        $ht_id = $xy_id = $v[1];

                        $rs_id = $agreement_db -> where("id=".$xy_id." and status=1") -> getField("id");
                        $rs_ht_code = $agreement_db -> where("agreement_code='".trim($v[2])."' and status=1") -> getField("id");

                        if(!empty($rs_id)||!empty($rs_ht_code)){
                                $error_ids=$error_ids.$xy_id.',';
                                continue;
                        }

                        $data['id'] = trim($v[1]); //协议ID

                        //获取客户id
                        $data['client_id'] = trim($v[0]);
                        if ($data['client_id'] == "" || !isset($data['client_id']) || !$data['client_id']) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }

                        //获取协议编号
                        $data['agreement_code'] = trim($v[2]);
                        if ($data['agreement_code'] == "" || !isset($data['agreement_code'])) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }

                       
                        //获取协议主体——乙方
                        $data['party_b'] = trim(iconv('gb2312','utf-8',$v[3]));
                        if ($data['party_b'] == "" || !isset($data['party_b'])) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }

                        //获取协议主体——丙方
                        $data['party_c'] = trim(iconv('gb2312','utf-8',$v[4]));
                        $data['party_a'] = "安智";

                        //获取签订日期
                        $data['sign_date'] = trim($v[5]);
                        if ($data['sign_date'] == "" || !isset($data['sign_date'])) {
                            $data['sign_date'] = date("Y-m-d", time());
                        }
                        //获取合作金额
                        $data['amount'] = trim($v[6]);

                        //$v[7] 刊例ID  todo
                        //$v[8] 频道ID  todo

                        //获取起始日期
                        $data['start_date'] = trim($v[9]);
                        if ($data['start_date'] == "" || !isset($data['start_date'])) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }
                        //获取终止日期
                        $data['end_date'] = trim($v[10]);
                        if ($data['end_date'] == "" || !isset($data['end_date'])) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }
                        //获取负责人
                        $data['responsible'] = trim(iconv('gb2312','utf-8',$v[11]));
                        if ($data['responsible'] == "" || !isset($data['responsible'])) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                        }

                        //获取保证金余额
                        $data['deposit_balance'] = trim($v[13]);
                        if ($data['deposit_balance'] == "" || !isset($data['deposit_balance'])) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            //$this->error("保证金余额不可为空！");
                        }
                        //初始录入协议的时候待抵扣等于保证金余额
                        $data['deductible_cash'] = $data['deposit_balance'];
                        //获取预计保证金
                        $data['expect_deposit'] = trim($v[12]);
                        //获取备注信息
                        $data['remark'] = trim(iconv('gb2312','utf-8',$v[14]));
                        $data['admin_id'] = $_SESSION['admin']['admin_id'];
                        $data['admin_name'] = $_SESSION['admin']['admin_user_name'];
                        $data['update_tm'] = time();
                        $data['create_tm'] = time();

                        //获取购买频道
                        $chanels = explode(";",$v[8]);
                        /*$chanels = $_POST['purchase_channel'];
                        foreach ($chanels as $cls) {
                            if(!empty($cls)){
                                if ($cls == "-1" || !isset($cls)) {
                                    $this->error("未选择广告位！");
                                }
                            }
                        }*/

                        //启动事务
                        $agreement_db->startTrans();
                        //准备保存数据
                        $agreement_id = $agreement_db->add($data);
                        if (!$agreement_id) {
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            //$this->error("框架协议添加失败！");
                        }
                        //将购买频道存储如数据表ad_agreement_adverts

                        $save['admin_id'] = $data['admin_id'];
                        $save['admin_name'] = $data['admin_name'];
                        $save['agreement_id'] = $agreement_id;
                        $save['rate_card_id'] = trim($v[7]);
                        $save['client_id'] = $v[0];
                        $save['create_tm'] = time();


                        if(!empty($chanels)){
                            foreach ($chanels as $ad_id) {
                                if(!empty($ad_id)){
                                    $advertising_name = $Advertisings_db->where("id=" . $ad_id." and rate_card_id=".$v[7])->getField("advertising_name");
                                    if(!$advertising_name){
                                        $stop = 1;
                                        break;
                                    }
                                    /*
                                    if (!isset($advertising_name) || $advertising_name == "") {
                                        //失败，回滚
                                        $agreement_db->rollback();
                                        $this->error("添加框架协议失败！");
                                    }*/
                                    $save['advertising_name'] = $advertising_name;
                                    $save['advertising_id'] = $ad_id;
                                    //保存
                                    if ($Advert_db->add($save) === false) {
                                        //失败，回滚
                                        //$agreement_db->rollback();
                                        //$this->error("添加框架协议失败！");
                                    }
                                }
                            }

                            if($stop==1){
                                $error_ids=$error_ids.$ht_id.',';
                                continue;
                            }
                        }

                        //更新客户表，将相应客户的框架协议数量+1
                        $nwhere['id'] = array('eq', $data['client_id']);
                        $client_judement = $client_lists = $client_db->where($nwhere)->setInc("agreement_num");
                        if (!$client_judement) {
                            $agreement_db->rollback();
                            $error_ids=$error_ids.$ht_id.',';
                            continue;
                            //$this->error("添加框架协议失败！");
                        }

                        //事务提交
                        $succ_ids = $succ_ids.$ht_id.',';
                        $agreement_db->commit();
                }

                $error_ids =substr($error_ids,0,-1);
                $succ_ids =substr($succ_ids,0,-1);
                if($error_ids!=''){
		    echo "<script>alert('ID为".$error_ids."的数据导入失败,请检查必要字段是否为空，或者协议ID协议编号有重复!或者频道ID不在该刊例ID下');location.href='/index.php/Settlement/Agreement/index'</script>";
                }else{
		    echo "<script>alert('导入成功');location.href='/index.php/Settlement/Agreement/index'</script>";
                }
                //写日志
                if($succ_ids!=''){

		    $this -> writelog("广告结算:批量导入了协议,ID为".$succ_ids);
                }
    }
}
