<?php

/**
 * Desc:   合同管理
 * @author yuanming<yuanming@anzhi.com>
 * @final  2015-08-16
 */
class ContractAction extends CommonAction {
    private $new_rate = '除短代、安智币、礼劵外，其余通道5%；安智币6%、礼劵6%、盛峰短信-联通40%、盛峰短信-电信53%、联动短信支付-移动卡45%、联通集团-联通30%';
    private  $old_rate = '支付宝支付3%、移动充值卡支付5%、联通一卡充支付5%、电信卡支付5%、银行卡在线支付1%、安智币6%、盛峰短代支付-电信53%、盛峰短代支付-联通40%+坏账、财付通2%、易宝支付5%、微信支付2%';
    private $fuzeren = array('丛野','周昆','唐春丽','刘吉');
    /**
     *  列表
     */
    function index() {

       	$model = M('');
		if($_GET['status']!= ''&&in_array($_GET['status'],array(0,1,2,3,4))){
			$where['a.status'] = $_GET['status'];
			$this->assign('status',$_GET['status']);
		}
		if(!empty($_GET['softname'])){
			$where['a.softname'] = array('like','%'.$_GET['softname'].'%');
			$this->assign('softname',$_GET['softname']);
		}
		if(!empty($_GET['game_type']) && $_GET['game_type'] >0){
			$where['a.game_type'] = $_GET['game_type'];
			$this->assign('game_type',$_GET['game_type']);
		}
		if(!empty($_GET['package'])){
		    $where['a.package'] = $_GET['package'];
		    $this->assign('package',$_GET['package']);
		}
		if(!empty($_GET['id'])){
            $id = array_filter(json_decode($_GET['id']));
            $where['a.id'] = array('in',$id);
        }
        $join_product = false;
        if(!empty($_GET['s_fuzeren'])){
            $where['b.fuzeren'] = array('like','%'.$_GET['s_fuzeren'].'%');
            $this->assign('s_fuzeren',$_GET['s_fuzeren']);
            $join_product = true;
        }
        if(!empty($_GET['s_company'])){
            $where['c.company'] = array('like','%'.$_GET['s_company'].'%');
            $this->assign('s_company',$_GET['s_company']);
            $join_product = true;
        }
        if(isset($_GET['c_status'])){
            $where['a.c_status'] = $_GET['c_status'];
            $this->assign('c_status',$_GET['c_status']);
        }
        if($join_product){
			$where['b.del'] = 0;
            $total = $model->table('yx_contract a')->join('yx_product b on a.package=b.package')->join('pu_developer as c on b.dev_id=c.dev_id')->where($where)->count();
        }else{
            $total = $model->table('yx_contract a')->where($where)->count();
        }

		//$total = count($channel_list);
		$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
        $param = http_build_query($_GET);
		 import('@.ORG.Page2');
        $Page = new Page($total, $limit, $param);
        $Page->rollPage = 10;
        $Page->setConfig('header', '篇记录');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        if($join_product){
            $list = $model->table('yx_contract a')->join('yx_product b on a.package=b.package')->join('pu_developer as c on b.dev_id=c.dev_id')->where($where)->field('a.*,b.fuzeren,c.company')->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
        }else{
            $list = $model->table('yx_contract a')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
        }
		$pack_arr = $r_company = array();
		foreach($list as $k=>$v){
			$pack_arr[] = $v['package'];
		}
		$company = $model->table('yx_product as a')->join('pu_developer as b on a.dev_id=b.dev_id')->join('pu_developer_settlement as c on a.dev_id=c.dev_id')->field('a.package,a.fuzeren,b.company,c.company_account,c.company_rate')->where(array('a.package'=>array('in',$pack_arr),'a.del'=>0,'b.status'=>0,'c.del'=>0))->select();
        echo $model->getLastSql();
		foreach($company as $k=>$v){
			$k = $v['package'];
			$r_company[$v['package']] = $v;
 		}
        if(!empty($_GET['id'])){
            $this->export_contract($list,$r_company);
        }
        $this->assign('lr',$_GET['lr']);
		$this->assign('param', $param);
		$this->assign('page', $Page->show());
		$this->assign('list',$list);
        $this->assign('new_rate',$this->new_rate);
        $this->assign('old_rate',$this->old_rate);
		$this->assign('r_company',$r_company);
        $this->assign('fuzeren',$this->fuzeren);
		$this->display();
    }

    function pub_edit_fuzeren(){
        $model = M('');
        if($_POST){
            $id = array_filter(json_decode($_POST['ids']));
            $package = $model->table('yx_contract')->where(array('id'=>array('in',$id)))->field('package')->select();
            $pack = array();
            foreach($package as $v){
                $pack[] = $v['package'];
            }
            $res = $model->table('yx_product')->where(array('package'=>array('in',$pack)))->save(array('fuzeren'=>$_POST['fuzeren']));
            if($res){
                $ids = implode(',',$id);
                $packs = implode(',',$pack);
                $this->writelog("修改了合同ID为{$ids}的负责人为{$_POST['fuzeren']}",'yx_product',$packs,'/index.php/Sendnum/Contract/edit_contract' ,'','edit');
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $this->display('edit_fuzeren');
    }

    function pub_edit_c_status(){
        $model = M('');
        if($_POST){
            $id = array_filter(json_decode($_POST['e_ids']));
            $res = $model->table('yx_contract')->where(array('id'=>array('in',$id)))->save(array('update_tm'=>time(),'c_status'=>$_POST['e_c_status']));
            if($res){
                $ids = implode(',',$id);
                $this->writelog("修改了合同ID为{$ids}的财务合同状态为{$_POST['e_c_status']}",'yx_contract',$ids,'/index.php/Sendnum/Contract/edit_contract' ,'','edit');
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }
    function export_contract($list,$r_company){
        $arr = array();
        foreach($list as $k=>$v){
            $arr[$k] = array(
                '0' =>$v['softname'],
                '1' => $r_company[$v['package']]['company'],
                '2' =>$r_company[$v['package']]['company_account']
            );
            if($r_company[$v['package']]['company_rate']==1||$r_company[$v['package']]['company_rate']==2){
                $arr[$k][3] = '专用发票';
            }else if($r_company[$v['package']]['company_rate']==3){
                $arr[$k][3] = '普通发票';
            }else{
                $arr[$k][3] = '';
            }
            if($r_company[$v['package']]['company_rate']==1){
                $arr[$k][4] = '6%';
            }else if($r_company[$v['package']]['company_rate']==2){
                $arr[$k][4] = '3%';
            }else if($r_company[$v['package']]['company_rate']==3){
                $arr[$k][4] = '';
            }else{
                $arr[$k][4] = '';
            }
            if($r_company[$v['package']]['company_rate']==1){
                $arr[$k][5] = '0%';
            }else if($r_company[$v['package']]['company_rate']==2){
                $arr[$k][5] = '3.72%';
            }else if($r_company[$v['package']]['company_rate']==3){
                $arr[$k][5] = '6.72%';
            }else{
                $arr[$k][5] = '';
            }
            $arr[$k][6] = '';

            if($v['game_type']==1||$v['game_type']==2){
                $arr[$k][7] = '先减后分';
            }elseif($v['game_type']==3){
                if($v['contract_rate']==1){
                    $arr[$k][7] = '先减后分';
                }elseif($v['contract_rate']==2){
                    $arr[$k][7] = '先分后减';
                }elseif($v['contract_rate']==3){
                    if($v['bill_method']==1){
                        $arr[$k][7] = '先分后减';
                    }elseif($v['bill_method']==2){
                        $arr[$k][7] = '先减后分';
                    }else{
                        $arr[$k][7] = '';
                    }
                }else{
                    $arr[$k][7] = '';
                }
            }else{
                $arr[$k][7] = '';
            }

            if($v['contract_rate']!=3){
                if($v['game_type']==1||$v['game_type']==2){
                    $arr[$k][8] = ' 50:50';
                }elseif($v['game_type']==3){
                    if($v['contract_rate']==1){
                        $arr[$k][8] = ' 80:20';
                    }elseif($v['contract_rate']==2){
                        $arr[$k][8] = ' 50:50';
                    }
                }else{
                    $arr[$k][8] = '';
                }
            }else{
                $arr[$k][8] = ' '.$v['proportion'];
            }

            if($v['contract_rate']==1){
                $arr[$k][9] = $this->old_rate;
            }elseif($v['contract_rate']==2){
                $arr[$k][9] = $this->new_rate;
            }elseif($v['contract_rate']==3){
                $arr[$k][9] = $v['rate'];
            }else{
                $arr[$k][9] = '';
            }

            $arr[$k][10] = date("Y-m-d",$v['start_tm']);
            $arr[$k][11] = date("Y-m-d",$v['end_tm']);
            $arr[$k][12] = $r_company[$v['package']]['fuzeren'];
            $arr[$k][13] = '2%';
            $arr[$k][14] = $v['contract_num'];
        }
        header('Content-type: application/csv');
        // //下载显示的名字
        $file_name = date("Y-m-d").'.csv';
        header('Content-Disposition: attachment; filename=合同_'.$file_name);
        $out = fopen('php://output', 'w');
        fwrite($out,chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out,array('游戏名称','公司名称','结算公司','发票类型','税点','安智后台应扣税点','税率变更时间','结算方式','分成比例','通道费率','起始日期','终止日期','负责人','合同误差率','合同行政编号'));
        foreach($arr as $v) {
            fputcsv($out,$v);
        }
        exit();
    }
    //改变状态
    function edit_status(){
        $model = M('');
        $type = $_GET['type'];
        $id = $_GET['id'];
        if(empty($type) || empty($id) ){
            $this->error('参数未知');
        }
        if($type == 4){
        	$data['remarks'] = htmlspecialchars($_GET['remarks']);
        	$msg_log = '游戏联运管理-合同管理：驳回了id为'.$id.'的游戏';
        }elseif($type == 1){            
            $contract_num = $this->makeContractNum($id);
            if($contract_num){
                $data['contract_num'] = $contract_num;
            }            
            $msg_log = '游戏联运管理-合同管理：通过了id为'.$id.'的游戏';
        }elseif($type == 3){
            $msg_log = '游戏联运管理-合同管理：忽略了id为'.$id.'的游戏';
        }elseif($type == 2){
            $msg_log = '游戏联运管理-合同管理：撤销了id为'.$id.'的游戏';
        }
        $where['id'] = $id ;
        $data['status'] = $type ;
		if($type==2){
			$data['status'] = 2;
//			$data['contract_num'] = '';
//			$data['contract_path'] = '';
		}
        $data['update_tm'] = time() ;
        if($type==1){
            $data['pass_tm'] = time();
        }else{
            $data['pass_tm'] = '';
        }
        $res = $model->table('yx_contract')->where($where)->save($data);
        if($res){            
            $this->writelog($msg_log,'yx_contract',$id,__ACTION__ ,'','edit');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }     
        	
    }
    
    //上传合同
    function upload_contract(){
        $model = M('');
        $id = $_POST['contract_id'];
        if(empty($id) ){
            $this->error('参数未知');
        }
        $_FILES['contract'];

        $path = UPLOAD_PATH . '/data3/' . '/agreement/' . date('Ym/d') . '/';
        list($msec, $sec) = explode(' ', microtime());
        $msec = substr($msec, 8);
        $this->mkDirs($path);
        
        $info = pathinfo($_FILES['contract']['name']);
        $type = $info['extension'];
        
        if($type != 'pdf'){
            $this->error('合同格式为pdf');
        }else{
            $contract_path = $path . $msec . '.' . $type;
        }
        
        if (move_uploaded_file($_FILES['contract']['tmp_name'], $contract_path)){
            $contract_path = str_replace(UPLOAD_PATH, '', $contract_path);            
        }else{
            $this->error('上传合同失败');
        }
        $where['id'] = $id ;
        $data['contract_path'] = $contract_path ;
        $data['update_tm'] = time() ;
		$data['status'] = 2;
        $res = $model->table('yx_contract')->where($where)->save($data);
        if($res){
            $this->writelog('游戏联运管理-合同管理：为id是'.$id.'的游戏上传了 合同','yx_contract',$id,__ACTION__ ,'','edit');
            $this->success('上传合同成功');
        }else{
            $this->error('更新失败');
        }
    } 
    
    //查看pdf
    function viewpdf(){
        $type = $_GET['type'];
		$model = M('');
		$res = $model->table('yx_contract')->where(array('id'=>$_GET['id']))->field('contract_path')->find();
    	if($type==1){
			$this->assign('path',$res['contract_path']);
    	    $this->display();
    	}
    }
    
    //生成合同号
    function makeContractNum($id){
        $model = M('');
        $res = $model->table('yx_contract')->field("contract_num")->where("id='$id'")->find();        
        if(!empty($res['contract_num'])){
        	return false;
        }else{
            $res = $model->table('yx_contract')->field("contract_num")->order('contract_num desc')->find();      
            $str_num = $res['contract_num'];
            
            $date_old = substr($str_num,2,8);
            $date_new = date('Ymd',time());
            if($date_old != $date_new){
                $c_str = 'YX'.$date_new.'001';
            }else{
                $c_num = substr($str_num,10,strlen($str_num));
                $c_num = intval($c_num);
                $c_num+=1;
                if($c_num >=100 ){
                    $c_str = 'YX'.$date_new.$c_num;
                }elseif($c_num >=10){
                    $c_str = 'YX'.$date_new.'0'.$c_num;
                }else{
                    $c_str = 'YX'.$date_new.'00'.$c_num;
                }            
            }
            return  $c_str; 
        }
        
    }
	
	//编辑合同
	function edit_contract(){
		$model = M('');		
		if(!$_POST){
			//编辑页
			$id = $_GET['id'];
			if(!empty($id)){
				$info = $model->table('yx_contract')->where(array('id'=>$id))->field('softname,package,start_tm,end_tm,contract_rate,game_type,proportion,rate,bill_method,contract_name')->find();
				if($info){
					$dev_id = $model->table('yx_product')->where(array('package'=>$info['package'],'del'=>0))->field('dev_id,fuzeren')->find();
					$settlement_info = $model->table('pu_developer_settlement a')->join('pu_developer b on a.dev_id=b.dev_id')->where(array('a.dev_id'=>$dev_id['dev_id'],'a.del'=>0))->field('a.*,b.company')->find();
                    $info['fuzeren'] = $dev_id['fuzeren'];
				}
			}
			$this->assign('id',$id);
			$this->assign('info',$info);
            $this->assign('fuzeren',$this->fuzeren);
            $this->assign('new_rate',$this->new_rate);
            $this->assign('old_rate',$this->old_rate);
			$this->assign('settlement_info',$settlement_info);
			$this->display();	
		}else{
			//编辑保存
			if($_POST['id']){
                $id=$_POST['id'];
				if(empty($_POST['softname'])) $this->error('产品名称不能为空');
				if(empty($_POST['start_tm'])) $this->error('合同日期不能为空');
                if(empty($_POST['end_tm'])) $this->error('合同日期不能为空');
				$data = array(
                    'softname'=>$_POST['softname'],
					'start_tm'=>strtotime($_POST['start_tm']),
					'end_tm'=>strtotime($_POST['end_tm']),
					'update_tm'=>time()
				);

                $contract_rate_old=$_POST['contract_rate_old'];
                if(isset($_POST['contract_rate'])){
                    $data['contract_rate']=$_POST['contract_rate'];
                    if($data['contract_rate'] == 3){
                        $data['rate'] = $_POST['rate'];
                        $data['bill_method'] = $_POST['bill_method'];
                        $data['proportion'] = $_POST['proportion'];
                    }else{
                        $data['rate'] = '';
                        $data['bill_method'] ='';
                        $data['proportion'] ='';
                    }
                }
                $old_info = $model->table('yx_contract')->where(array('id'=>$id))->find();
                if(!empty($old_info['contract_name'])&&empty($_POST['contract_name'])) $this->error('合同名称不能为空');
                if($_POST['contract_name']!= $old_info['contract_name']){
                    //修改同步到用户中心
                    if(relevance_softname($old_info['package'],array('contractAppName'=>$_POST['contract_name']))){
                        $data['contract_name'] = $_POST['contract_name'];
                    }else{
                        $this->error('修改合同名称同步到用户中心失败，请重试');
                    }
                }
				$res = $model->table('yx_contract')->where(array('id'=>$id))->save($data);
                if(!empty($_POST['fuzeren'])){
                    $model->table('yx_product')->where(array('package'=>$_POST['package'],'del'=>0))->save(array('fuzeren'=>$_POST['fuzeren']));
                }
				if($res){
                    $this->writelog('游戏联运管理-合同管理：'.$_SESSION['admin']['admin_user_name'].'('.$_SESSION['admin']['admin_id'].')编辑了id为'.$id.'的游戏合同,合同费率新值为'.$_POST['contract_rate'].',合同费率旧值为'.$contract_rate_old,'yx_contract',$id,__ACTION__ ,'','edit');
                    $this->assign("jumpUrl", "/index.php/Sendnum/Contract/index");
					$this->success('保存成功');
				}else{
					$this->error('保存失败');
				}
			}else{
				$this->error('保存失败');
			}
			
		}
		
	}
  
}

?>
