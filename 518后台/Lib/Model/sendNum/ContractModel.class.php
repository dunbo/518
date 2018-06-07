<?php
/**
 * Desc:   广告结算-合同列表
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class ContractModel extends Model {
     protected $trueTableName = 'ad_tj_soft';

    /**
     *  获取刊例列表
     *  @final       2013-06-06
     */	
    public function getRatecard()
    {
        $rs= $this->field('id,rate_name')->table('ad_rate_card')->where('status=1')->select();
        return $rs;
    }

    /**
     *  根据刊例ID获取频道列表
     *  @final       2013-06-06
     */	
    public function getChannel($rateid)
    {
        $res= $this->field('configcontent')->table('pu_config')->where('config_type="AD_CHANNEL"')->find();
        $ret = json_decode($res['configcontent'],true);
        print_r($ret);
        exit;

        $rs= $this->field('distinct(grand_id)')->table('ad_rate')->where('status=1 and rate_id='.$rateid)->select();
        $newarr = array();
        foreach($rs as $key=>$v)
        {
            $id  =$v['grand_id'];
            $newarr[$key]['id']  = $v['grand_id'];
            $newarr[$key]['name']  = $ret[$id][0];
        }
        return $newarr;
    }
    
    public function getFirstNames() {
        $res= $this->field('configcontent')->table('pu_config')->where('config_type="AD_CHANNEL"')->find();
        $ret = json_decode($res['configcontent'],true);
        $arr = array();
        foreach ($ret as $key => $record) {
            $arr[$key] = $record['name'];
        }
        return $arr;
    }
    
    // 获得合同的框架id
    public function getFrameworkId($contract_id) {
        $rs = $this->field('framework_id')->table('ad_contract')->where("contract_id='{$contract_id}'")->find();
        if (!$rs['framework_id'])
            return 0;
        $framework_id = $rs['framework_id'];
        // TODO, 检查framework_id是否有效
        // $rs = $this->table('ad_frame_agreement')->where()->find();
        return $framework_id;
    }
    
    // 获得合同的框架码
    public function getFrameworkNumber($contract_id) {
        $rs = $this->field('framework_number')->table('ad_contract')->where("contract_id='{$contract_id}'")->find();
        if ($rs['framework_number'])
            return $rs['framework_number'];
        return 0;
    }
    
    // 根据framework_id获得其下所有有效合同
    public function getAllContractsOfFramework($framework_id) {
        // TODO，先判断framwork_id是否有效
        $where = array(
            'framework_id' => $framework_id,
        );
        $rs = $this->field("contract_id")->table("ad_contract")->where($where)->select();
        if (!$rs)
            return $rs;
        $contract_id_arr = array();
        foreach ($rs as $value) {
            $contract_id_arr[] = $value['contract_id'];
        }
        return $contract_id_arr;
    }
    
    // 根据framework_id获得其下所有有效合同数
    public function getContractCountOfFramework($framework_id) {
        $where = array(
            'framework_id' => $framework_id,
        );
        $rs = $this->field("contract_id")->table("ad_contract")->where($where)->count();
        return $rs;
    }

    /**
     *  根据频道ID获取广告位
     *  @final       2013-06-09
     */	
    public function getAdlist($grandid)
    {
        $rs= $this->field('parent_id')->table('ad_rate')->where('status=1 and grand_id='.$grandid)->select();
        $newarr = array();
        $tmpids = '';
        foreach($rs as $key=>$v)
        {
            $parent_id = $v['parent_id'];
            $tmparr = explode('_',$parent_id);
            $tmpids .= $tmparr[1].',';
        }
        $tmpids = '('.substr($tmpids,0,-1).')';
        $res = $this->field('extent_id,extent_name')->table('sj_category_extent')->where('extent_id in '.$tmpids)->select();
        return $res;
    }

    /**
     *  根据softid获取软件详情
     *  @final       2013-04-17
     *  1应用 2游戏 3电子书
     */	
    public function getFrametime($contract_id)
    {
        $rs= $this->field('framework_id')->table('ad_contract')->where("contract_id='$contract_id'")->find();
        $fid = $rs['framework_id'];
        if($fid==0){
            return false;
        }else
        {
            $res= $this->field('start_tm,end_tm')->table('ad_frame_agreement')->where("id=$fid")->find();
            return $res;
        }
    }

    /**
     *  根据softid获取软件详情
     *  @final       2013-04-17
     *  1应用 2游戏 3电子书
     */	
    public function getSoftinfo($package)
    {
        $rs= $this->field('softname,category_id')->table('sj_soft')->where("package='$package' and status=1 and hide=1")->order('version_code desc')->find();
        $softname = $rs['softname'];
        $cid = str_replace(',','',$rs['category_id']);
        $sql ="select parentid from sj_category where category_id =(select parentid from sj_category where category_id =$cid)";
        $res = $this->query($sql);
        $ret = array(
            'softname'=>$softname,
            'type'=>$res[0]['parentid'],
        );
        return $ret;
    }

    /**
     *  根据softid获取软件详情
     *  @final       2013-04-17
     */	
    public function getSoftbyid($softid)
    {
        $rs= $this->table('ad_soft')->where("id=$softid")->find();
        return $rs;
    }

    /**
     *  根据合同获取软件列表
     *  @final       2013-04-14
     */	
    public function getContract($contract_id)
    {
        $rs= $this->table('ad_contract')->where("contract_id=$contract_id")->find();
        return $rs;
    }

    /**
     *  根据合同获取软件列表
     *  @final       2013-04-10
     */	
    public function getSoftlist($contract_id)
    {
        $rs= $this->table('ad_soft')->where("contract_id={$contract_id} and status=1")->select();
        return $rs;
    }

    /**
     *  根据合同ID和月份获取软件
     *  @final       2013-04-14
     */	
    public function getSoftlistbymonth($contract_id,$month)
    {
        $rs= $this->table('ad_tj_soft')->where("contract_id={$contract_id} and month='{$month}' and status=1")->order('package desc')->select();
        return $rs;
    }
    
    // 根据频道和获取软件
    public function getSoftlistbychannelmonth($channel, $month) {
        $where = array(
            'channel' => $channel,
            'month' => $month,
            'status' => 1,
        );
        $rs = $this->table('ad_tj_soft')->where($where)->select();
        return $rs;
    }

    /**
     *  获取合同统计列表
     *  @final       2013-04-09
     */	
    public function getContractlist($get)
    {
        if(isset($get['framework_number']))
        {
            if($get['framework_number']=='空'){
                $where['b.framework_number'] = array('EQ','');
            }else{
                $where['b.framework_number'] = array('like', '%'.$get['framework_number'].'%');
            }
        }

        if(isset($get['contract_number']))
        {
            $where['b.contract_number'] = array('like', '%'.$get['contract_number'].'%');
        }

        if(isset($get['fuzeren']))
        {
            $where['b.fuzeren'] = array('like', '%'.$get['fuzeren'].'%');
        }

        if(isset($get['client_name']))
        {
            $where['b.client_name'] = array('like', '%'.$get['client_name'].'%');
        }

        if(isset($get['beginsigndate']))
        {
            $where['signday']  = array('between',''.$get['beginsigndate'].','.$get['endsigndate'].'');
        }

        if(isset($get['begindate']))
        {
            $where['month']  = array('between',''.$get['begindate'].','.$get['endate'].'');
        }

        import("@.ORG.Page");
        $rs = $this->table('ad_tj_soft a')->field('a.month,a.contract_id,b.client_name,b.contract_number,b.framework_number,b.signday,b.fuzeren,COUNT(distinct(package)) AS softnum,SUM(zprice) as zprice,SUM(yprice) as yprice')->join('INNER JOIN ad_contract b ON a.`contract_id`=b.`contract_id`')->where($where)->group('a.month,a.contract_id')->select();
        $page = new Page(count($rs), 10);
        $rs = $this->table('ad_tj_soft a')->field('a.month,a.contract_id,b.client_name,b.contract_number,b.framework_number,b.signday,b.fuzeren,COUNT(distinct(package)) AS softnum,SUM(zprice) as zprice,SUM(yprice) as yprice')->join('INNER JOIN ad_contract b ON a.`contract_id`=b.`contract_id`')->where($where)->order('month desc,client_name desc')->group('a.month,a.contract_id')->limit($page->firstRow.','.$page->listRows)->select();
        //echo $this->getlastsql();
        //$page->parameter = $sqlparam;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();    
        $res = array(
            'list'=>$rs,
            'page'=>$show,
            //'sqlparam'=>$sqlparam,
        );
        return $res;
    }



    /**
     *  添加单条新数据
     *  @final       2014-04-10
     */	
    public function savecontract($data)
    {
        unset($data['my_hash']);
        $data['addtime'] = time();
        $data['client_id'] = $this->getClientidbyname($data['client_name']);
        $ad_contract_model = $this->table('ad_contract');
        $rs = $ad_contract_model->add($data);
        return $rs;
    }

    /**
     *  保存合同
     *  @final       2014-04-14
     */	
    public function editcontract($post)
    {
        $htid = $post['htid'];
        unset($post['my_hash']);
        unset($post['htid']);
        /*
        $data = array();
        $data['softname'] = $post['softname'];
        $data['size'] = $post['size'];
        $data['nature'] = $post['nature'];
        $data['companyname'] = $post['companyname'];
        $data['jianjie'] = $post['jianjie'];
        $data['osuser'] = $admin_user_name;
        $data['hztype'] = $post['hztype'];
        $data['com_tj_tel'] = $post['com_tj_tel'];
        $data['beizhu'] = $post['beizhu'];
         */
        $rs = $this->table('ad_contract')->data($post)->where("contract_id= ".$htid)->save();
        return $rs;
    }

    /**
     *  根据softid删除软件表中的数据 ad_soft
     *  @final       2014-04-10
     */	
    public function delsoft($softid)
    {
        $rs = $this->deltjsoft($softid);
        if($rs >0)
        {
            $res = $this->table('ad_soft')->where('id='.$softid)->delete();
            return $res;
        }
    }

    /**
     *  根据softid删除统计软件表中的数据 ad_tj_soft
     *  @final       2014-04-10
     */	
    public function deltjsoft($softid)
    {
        return $this->where('softid='.$softid)->delete();
    }

    /**
     *  编辑单条软件
     *  @final       2014-04-10
     */	
    public function saveditsoft($data)
    {
        $softid= $data['softid'];
        unset($data['softid']);
        $res = $this->table('ad_soft')->data($data)->where("id = ".$softid)->save();
        if($res===1){
            $this->deltjsoft($softid);
            $this->addtjsoft($data,$softid);
        }
    }

    /**
     *  添加单条新软件
     *  @final       2014-04-10
     */	
    public function savesoft($data)
    {
        $data['addtime'] = time();
        $ad_soft = $this->table('ad_soft');
        $insertid = $ad_soft->add($data);
        if($insertid>0){
            $this->addtjsoft($data,$insertid);
        }
    }

    /**
     *  添加统计用的soft
     *  @final       2014-04-11
     */	
    public function addtjsoft($data,$softid)
    {
        $newdata = array();
        $daysarr = explode(',',$data['days']);
        foreach($daysarr as $v)
        {
            $zhou = date('w',strtotime($v));
            $dayy = date('d',strtotime($v));
            if($zhou==0||$zhou==6||$dayy<=5)
            {
                $zprice = $data['zhehoujiaprice'];
                $yprice = $data['jiaprice'];
                $is_ping = 0;
                $is_jia= 1;
            }else{
                $is_ping = 1;
                $is_jia= 0;
                $zprice = $data['zhehoupingprice'];
                $yprice = $data['pingprice'];
            }
            $newdata['month']=date('Y-m',strtotime($v));
            $newdata['package'] = $data['package'];
            $newdata['softname'] = $data['softname'];
            $newdata['day'] = $v;
            $newdata['ad_time'] = strtotime($v);
            $newdata['contract_id'] = $data['contract_id'];
            $newdata['channel'] = '首页推荐';//supwater
            $newdata['interval'] ='第一区间'; //supwater
            $newdata['extent_code'] = 'EX:16';//supwater
            $newdata['zprice'] = $zprice;
            $newdata['yprice'] = $yprice;
            $newdata['is_ping'] = $is_ping;
            $newdata['is_jia'] = $is_jia;
            $newdata['softid'] = $softid;
            $this->add($newdata);
        }
    }

    /**
     *  根据客户名称获取客户ID
     *  @final       2014-04-10
     */	
    public function getClientidbyname($name)
    {
        $rs= $this->table('ad_client')->field('id')->where("client_name='$name'")->find();
        return $rs['id'];
    }

    /**
     *  根据合同ID获取合同内容
     *  @final       2014-04-10
     */	
    public function getContractbyid($id)
    {
        $rs= $this->table('ad_contract')->field('contract_number,client_name')->where("contract_id='$id'")->find();
        return $rs;
    }
    
    // 根据合同id、月份，返回该合同这个月的广告总额
    public function get_total_price($contract_id, $month) {
         // 根据软件表统计一下总钱数
        $where = array(
            'contract_id' => $contract_id,
            'month' => $month,
            'status' => 1,
        );
        $find = $this->field('sum(zprice) as total_price')->where($where)->find();
        if ($find['total_price'])
            $total_price = $find['total_price'];
        else
            $total_price = 0;
        return $total_price;
    }
    
    
    /* 得到单个月的软件状态列表
    ** according_type为'contract_id'时表示按合同一个月，为'channel'时表示按频道一个月
    ** according_value为contract_id或channel的值
    ** schedule_type=0表示所有软件
    ** schedule_type=1表示已被排期软件
    ** schedule_type=2表示未被排期且未过期软件
    ** schedule_type=3表示未被排期但已过期软件
    */
    public function ad_detail_permonth($according_type, $according_value, $month, $schedule_type) {
        if ($schedule_type != 1 && $schedule_type != 2 && $schedule_type != 3) {
            $schedule_type = 0;
        }
        
        $where = array(
            $according_type => $according_value,
            'month' => $month,
            'status' => 1,
        );
        $soft_list = $this->table('ad_tj_soft')->where($where)->select();
        // 根据包名来将日期等数据归类
        $softs = array();
        foreach ($soft_list as $soft) {
            $package = $soft['package'];
            $extent_code = $soft['extent_code'];
            $softs[$package][$extent_code][] = $soft['ad_time'];
        }
        
        // 处理softs，方便在页面显示
        $tj_softs = array();
        $i = 0;
        foreach ($softs as $package => $extent_adtimes) {
            if ($schedule_type == 0) { // 不查找是否排期，只需把软件数组里的同一广告位的日期拼成一个字符串
                if (!isset($tj_softs[$i]['package'])) {
                    $tj_softs[$i]['package'] = $package;
                    // 根据包名查找sj_soft表里的软件名称
                    $find = $this->table('sj_soft')->where("package='{$package}' and status=1 and hide=1")->order('version_code desc')->find();
                    if ($find) {
                        $tj_softs[$i]['softname'] = $find['softname'];
                    }
                }
                $j = 0;
                foreach ($extent_adtimes as $extent_code => $adtimes) {
                    $tj_softs[$i]['extent_adtimes'][$j]['extent_code'] = $extent_code;
                    // 根据约定的区间代号，查找区间名
                    $tj_softs[$i]['extent_adtimes'][$j]['extent_name'] = $this->get_extent_name($extent_code);
                    // 把在该区间投入的日期组合起来
                    $all_days = '';
                    foreach ($adtimes as $ad_time) {
                        if ($all_days != '')
                            $all_days .= ', ';
                        $all_days .= date('Y-m-d', $ad_time);
                    }
                    $tj_softs[$i]['extent_adtimes'][$j]['all_days'] = $all_days;
                    $j++;
                }
            } else { // 查找排期，需检查每个广告位的每个日期该软件是否被排期
                $j = 0;
                foreach ($extent_adtimes as $extent_code => $adtimes) {
                    $all_days = '';
                    foreach ($adtimes as $ad_time) {
                        $ret = $this->get_schedule_type(array('package'=>$package,'extent_code'=>$extent_code,'ad_time'=>$ad_time));
                        if ($ret == $schedule_type) { // 找到该排期类型的数据
                            if (!isset($tj_softs[$i]['package'])) { // 软件包、软件名称查找并赋值
                                $tj_softs[$i]['package'] = $package;
                                // 根据包名查找sj_soft表里的软件名称
                                $find = $this->table('sj_soft')->where("package='{$package}' and status=1 and hide=1")->order('version_code desc')->find();
                                if ($find) {
                                    $tj_softs[$i]['softname'] = $find['softname'];
                                }
                            }
                            if (!isset($tj_softs[$i]['extent_adtimes'][$j]['extent_code'])) { // 投放区间名称查找并赋值
                                $tj_softs[$i]['extent_adtimes'][$j]['extent_code'] = $extent_code;
                                // 根据约定的区间代号，查找区间名
                                $tj_softs[$i]['extent_adtimes'][$j]['extent_name'] = $this->get_extent_name($extent_code);
                            }
                            if ($all_days != '') {
                                $all_days .= ', ';
                            }
                            $all_days .= date('Y-m-d', $ad_time);
                        }
                    }
                    if ($all_days) {
                        $tj_softs[$i]['extent_adtimes'][$j++]['all_days'] = $all_days;
                    }
                }
            }
            $i++;
            
        }
        return $tj_softs;
    }
    
    // 获得排期状态，1为已排期，2为未到期未排，3为已到期未排
    public function get_schedule_type($soft) {
        $ret = $this->is_scheduled($soft);
        if ($ret) {
            return 1;
        }
        $now = time();
        if ($soft['ad_time'] > $now) {
            return 2;
        }
        return 3;
    }
    
    // 判断包是否在指定区间和指定日期被投放
    public function is_scheduled($soft) {
        $extent_code = $soft['extent_code'];
        $tmp_arr = split(':', $extent_code);
        $package = $soft['package'];
        $ad_time = $soft['ad_time'];
        if (!$tmp_arr)
            return false;
        $prefix = $tmp_arr[0];
        $ret = false;
        // 判断包在具体广告位是否已排期的model，新增广告位的判断函数均在AdCheckIfScheduledModel里添加
        $model = D("sendNum.AdCheckIfScheduled");
        // 获得广告配置信息
        $find = $this->table('pu_config')->where(array('config_type'=>'AD_CHANNEL'))->find();
        if (!$find)
            return false;
        $ad_channel = $find['configcontent'];
        $ad_channel = json_decode($ad_channel, true);
        // 根据匹配上的前缀去执行AdCheckIfScheduled里具体的函数
        foreach ($ad_channel as $key => $ad_config) {
            if ($prefix != $key)
                continue;
            $func = "checkIfScheduled_{$key}";
            $ret = $model->$func($soft);
            break;
        }
        return $ret;
    }
    
    // 根据协定的区间代码找区间名称，以便展示
    private function get_extent_name($extent_code) {
        $tmp_arr = split(':', $extent_code);
        if (!$tmp_arr)
            return '';
        $prefix = $tmp_arr[0];
        // 具体广告位名称，新增广告位的生成名称函数均在AdCheckIfScheduledModel里添加
        $model = D("sendNum.AdCheckIfScheduled");
        // 获得广告配置信息
        $find = $this->table('pu_config')->where(array('config_type'=>'AD_CHANNEL'))->find();
        if (!$find)
            return '';
        $ad_channel = $find['configcontent'];
        $ad_channel = json_decode($ad_channel, true);
        // 根据匹配上的前缀去执行AdCheckIfScheduled里具体的函数
        $extent_name = '';
        foreach ($ad_channel as $key => $ad_config) {
            if ($prefix != $key)
                continue;
            $func = "getExtentName_{$key}";
            $first_name = $ad_config['name'];
            $extent_name = $model->$func($extent_code, $first_name);
            break;
        }
        return $extent_name;
    }

}
?>
