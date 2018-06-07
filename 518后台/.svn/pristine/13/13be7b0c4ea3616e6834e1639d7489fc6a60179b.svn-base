<?php

class AreaAction extends CommonAction {

    /**
     * 区间列表页
     */
    public function index()
    {

        //得到刊例ID
        $rate = isset($_GET['rate']) && empty($_GET['rate'])==FALSE ? trim($_GET['rate']) : NULL; //刊例ID

        //查询刊例列表
        $m_card = D('Settlement.RateCard');
        $ratelists = $m_card -> field('*')
                        -> where(array('status'=>array('eq',1), 'is_disabled'=>array('eq',0)))
                        -> order('is_defaulted DESC, id DESC')//录入时间倒叙
                        -> select();
        $this->assign('ratelists', $ratelists);

        //查询area
        $m_area = D('Settlement.Area');

        if($rate){
          $areas = $m_area -> where(array('rate_card_id'=>$rate))
                          -> order('sort ASC') 
                          ->select();
            $this->assign('areas',$areas);
        }
        
        //处理广告位显示
        $Model = new Model();
        $area_adv = array();
        foreach ($areas as $row) {
            //根据区间ID查询广告位名称和区间内广告信息
            $result = $Model -> table('settlement.ad_area_advertisings areaadv')
                                -> field('areaadv.*,adv.advertising_name')
                                -> join('settlement.ad_advertisings adv ON adv.id=areaadv.advertising_id')
                                -> where(array('areaadv.area_id'=>$row['id']))
                                -> order('sort ASC') //广告位顺序显示
                                -> select();

                foreach ($result as $value) {
                   $area_adv[$row['id']]['advname'][] = $value['advertising_name'];//得到区间内的广告位名称 （数组）
                   $area_adv[$row['id']]['softnum'] +=  $value['soft_num'];//同一个区间的广告位容纳软件数量（数字）
                }
                
                //区间内广告位去重显示（字符串）
        $area_adv[$row['id']]['advname'] =implode(';', array_unique($area_adv[$row['id']]['advname'])) ;

        }
       
        
        $this->assign('area_adv',$area_adv);
        $this->assign('rate',$rate);
        $this->display();


    }

    /**
     * 新建区间
     */
    public function add()
    {
        //得到刊例id
        $rate = isset($_GET['rate']) && empty($_GET['rate'])==FALSE ? trim($_GET['rate']) : NULL;
        //查询刊例下的广告位信息
        $m_adv = D('Settlement.Advertising');
        $advlists = $m_adv -> field('id,rate_card_id,advertising_name')
                           -> where(array('rate_card_id'=>$rate))
                           -> select();
        $this->assign('advlists', $advlists);
        $this->assign('rate',$rate);

        if(isset($_POST['submit'])){
            //获取提交数据
        $areaname = isset($_POST['areaname']) && empty($_POST['areaname'])==FALSE ? trim($_POST['areaname']): NULL;
        $areasort = isset($_POST['areasort']) && empty($_POST['areasort'])==FALSE ? $_POST['areasort']:10;
        $areasplit =isset($_POST['areasplit'])&& empty($_POST['areasplit'])==FALSE ?$_POST['areasplit']:0;
        $sort   =    isset($_POST['sort']) && empty($_POST['sort'])==FALSE ? $_POST['sort']: NULL;
        $adv_name   =    isset($_POST['adv_name']) && empty($_POST['adv_name'])==FALSE ? $_POST['adv_name']: NULL;
        $softnum   =    isset($_POST['softnum']) && empty($_POST['softnum'])==FALSE ? $_POST['softnum']: NULL;

        //判断是否有空白字段
        if(empty($areaname)||empty($areasort)){
                    $this->error('填写区间字段不完整');
                }
                //判断名称是否重复
        $che_areaname = D('Settlement.Area');
        $che_result = $che_areaname->where(array('rate_card_id'=>$rate,'area_name'=>$areaname))
                                   ->select();
        
        if($che_result){
             $this->error('区间名不可重复，请重新输入');
        }
        
        if(strstr($areasort,".")||$areasort<=0){
                $this->error('排序值需为正整数，请重新输入');
        }

        $che_sort = D('Settlement.Area')->where(array('rate_card_id'=>$rate))->select();
        foreach ($che_sort as  $value) {
            if($value['sort']==$areasort){
                $this->error('区间排序不可重复，请重新输入');
            }
            $area_ids[]=$value['id'];
        }
        if(!$sort&&!$adv_name&&!$softnum){
                    $this->error('没有填写任何广告位字段');
        }
        foreach ($sort as $sortkey => $sortvalue) {
               if(empty($sortvalue)){
                    $this->error('填写排序字段不完整');
                }
                if(strstr($sortvalue,".")||$sortvalue<=0){
                    $this->error('广告位排序字段为正整数');
                }
            $adv[$sortkey][sort] = $sortvalue;

        }
        foreach ($adv_name as $adv_namekey => $adv_namevalue) {
            $adv[$adv_namekey][adv_name] = $adv_namevalue;
        }
         foreach ($softnum as $softnumkey => $softnumvalue) {
               if(empty($softnumvalue)){
                    $this->error('填写软件数量字段不完整');
                }
            $adv[$softnumkey][softnum] = $softnumvalue;
        }
        if(count($sort)!==count(array_unique($sort))){
            $this->error('广告位排序字段不可重复');
        }

        //判断一个广告位是否在同一区间内
        $advid_con['rate_card_id']=array('eq',$rate);
        $advid_con['area_id']=array('in',$area_ids);
        $che_advid = D('Settlement.AreaAdvertising')->field('advertising_id')
                                                    ->where($advid_con)
                                                    ->select();
       
        foreach ($che_advid as $value) {
            $advids[] =  $value['advertising_id'];
        }
        
        foreach ($adv as  $value) {
            if(in_array($value['adv_name'],$advids)){
              $this->error('同一广告位只能存在在一个区间中');
            }
        }
                //area入库操作
        $areadata['admin_id'] = $_SESSION['admin']['admin_id'];
        $areadata['admin_name'] = $_SESSION['admin']['admin_user_name'];
        $areadata['rate_card_id'] = $rate;
        $areadata['area_name'] = $areaname;
        $areadata['sort'] = $areasort;
        $areadata['show_split'] = $areasplit;
        $areadata['update_tm'] = $areadata['create_tm'] = time();
        $m_area = D('Settlement.Area');
        $resultid = $m_area->add($areadata);
        
        
        //adv入库
        if($resultid>0){
            foreach ($adv as $value) {
                $advdata['admin_id'] = $_SESSION['admin']['admin_id'];
                $advdata['admin_name']=$_SESSION['admin']['admin_user_name'];
                $advdata['area_id'] = $resultid;
                $advdata['rate_card_id'] = $rate;
                $advdata['advertising_id']   = $value['adv_name'];                
                $advdata['soft_num']= $value['softnum'];
                $advdata['sort']    = $value['sort'];
                $advdata['update_tm'] = $advdata['create_tm'] = time();
                $m_area_adv = D('Settlement.AreaAdvertising');
                $result = $m_area_adv->add($advdata);
           }
        }
           
        $this->redirect("index",array('rate'=>$rate));
        }

        $this->display();
    }
    
    /**
     * 编辑区间
     */
    public function edit()
    {
    $areaid = isset($_GET['areaid']) && empty($_GET['areaid'])==FALSE ? trim($_GET['areaid']) : NULL;
    $rate = isset($_GET['rate']) && empty($_GET['rate'])==FALSE ? trim($_GET['rate']) : NULL; 
//得到刊例对应的广告位名称
        $m_adv = D('Settlement.Advertising');
        $advlists = $m_adv -> field('id,rate_card_id,advertising_name')
                           -> where(array('rate_card_id'=>$rate))
                           -> select();
        $this->assign('advlists', $advlists);
        $this->assign('rate',$rate);
        $this->assign('areaid',$areaid);



//读取区间及广告位信息    
        $Model=new Model();
        $results= $Model -> table('settlement.ad_area_advertisings areaadv')
                        -> field('areaadv.*,adv.advertising_name,area.area_name,area.sort AS areasort ,area.show_split')
                        -> join('settlement.ad_advertisings adv ON adv.id=areaadv.advertising_id')
                        -> join('settlement.ad_areas area ON area.id=areaadv.area_id')
                        -> where(array('areaadv.area_id'=>$areaid))
                        -> order('areaadv.sort ASC')
                        -> select();


        $this->assign('areaadvs',$results);
        

        //对更新的数据处理
        if(isset($_POST['submit']))
        {
            $areaid = isset($_POST['areaid']) && empty($_POST['areaid'])==FALSE ? intval($_POST['areaid']) : 0;
            $areaname = isset($_POST['areaname']) && empty($_POST['areaname'])==FALSE ? trim($_POST['areaname']) : NULL;
            $areasort = isset($_POST['areasort']) && empty($_POST['areasort'])==FALSE ?$_POST['areasort']:10;
            $areasplit = isset($_POST['areasplit']) && empty($_POST['areasplit'])==FALSE ?$_POST['areasplit']:0;
            $sort   =    isset($_POST['sort']) && empty($_POST['sort'])==FALSE ? $_POST['sort']: NULL;
            $adv_name   =    isset($_POST['adv_name']) && empty($_POST['adv_name'])==FALSE ? $_POST['adv_name']: NULL;
            $softnum   =    isset($_POST['softnum']) && empty($_POST['softnum'])==FALSE ? $_POST['softnum']: NULL;

        //判断是否有空白字段
        if(empty($areaname)||empty($areasort)){
                    $this->error('填写区间字段不完整');
                }
        if(!$sort&&!$adv_name&&!$softnum){
                    $this->error('没有填写任何广告位字段');
        }

                //判断名称是否重复
        $che_areaname = D('Settlement.Area');
        $condition['rate_card_id']=array('eq',$rate);
        $condition['area_name'] = array('eq',$areaname);
        $condition['id']=array('neq',$areaid);
        $che_result = $che_areaname->where($condition)
                                   ->select();
        

        if($che_result){
             $this->error('区间名不可重复，请重新输入');
        }

        if(strstr($areasort,".")||$areasort<=0){
                $this->error('排序值需为正整数，请重新输入');
        }

        $sortcon['rate_card_id']=array('eq',$rate);
        $sortcon['id'] = array('neq',$areaid);
        $che_sort = D('Settlement.Area')->where($sortcon)
                                        ->select();

        foreach ($che_sort as  $value) {
            if($value['sort']==$areasort){
                $this->error('区间排序不可重复，请重新输入');
            }
            $area_ids[]=$value['id'];
        }
        foreach ($sort as $sortkey => $sortvalue) {
               if(empty($sortvalue)){
                    $this->error('填写排序字段不完整');
                }
                if(strstr($sortvalue,".")||$sortvalue<=0){
                    $this->error('广告位排序字段为正整数');
                }
               $adv[$sortkey][sort] = $sortvalue;

        }
        foreach ($adv_name as $adv_namekey => $adv_namevalue) {
            $adv[$adv_namekey][adv_name] = $adv_namevalue;
        }
         foreach ($softnum as $softnumkey => $softnumvalue) {
               if(empty($softnumvalue)){
                    $this->error('填写软件数量字段不完整');
                }
            $adv[$softnumkey][softnum] = $softnumvalue;
        }

        //判断一个广告位是否在同一区间内
        $adv_con['rate_card_id']=array('eq',$rate);
        $adv_con['area_id'] = array('in',$area_ids);

        $che_advid = D('Settlement.AreaAdvertising')->field('advertising_id')
                                                    ->where($adv_con)
                                                    ->select();

        foreach ($che_advid as $value) {
            $advids[] =  $value['advertising_id'];
        }
        foreach ($adv as  $value) {
            if(in_array($value['adv_name'],$advids)){
              $this->error('同一广告位只能存在在一个区间中');
            }
        }
        
            //区间数据
            $areadata['id']         = $areaid;
            $areadata['area_name']  = $areaname;
            $areadata['sort']       = $areasort;
            $areadata['show_split'] = $areasplit;
            $areadata['update_tm']  = time();         
            D('Settlement.Area')->save($areadata);//area更新操作

            //清空area_id = $areaid的数据
            $del_condition['area_id'] = $areaid;
            D('Settlement.AreaAdvertising')->where($del_condition)->delete();
           
            //区间广告位数据
            foreach ($adv as $value) {
                $advdata['admin_id'] = $_SESSION['admin']['admin_id'];
                $advdata['admin_name']=$_SESSION['admin']['admin_user_name'];
                $advdata['area_id'] = $areaid;
                $advdata['rate_card_id'] = $rate;
                $advdata['advertising_id']   = $value['adv_name'];                
                $advdata['soft_num']= $value['softnum'];
                $advdata['sort']    = $value['sort'];
                $advdata['update_tm'] = $advdata['create_tm'] = time();
                $m_area_adv = D('Settlement.AreaAdvertising');
                $result = $m_area_adv->add($advdata);
           }
           
           $this->redirect("index",array('rate'=>$rate));
        }
        $this->display();
    }
    
    /**
     * 删除区间
     */
    public function delete()
    {
        //区间ID
    $areaid = isset($_GET['areaid']) && empty($_GET['areaid'])==FALSE ? $_GET['areaid'] : NULL;
    $rate = isset($_GET['rate']) && empty($_GET['rate'])==FALSE ? $_GET['rate'] : NULL; 
    $m_area = D('Settlement.Area');
    $result = $m_area->delete($areaid);
    $this->redirect('index',array('rate'=>$rate));

    }
    
    /**
     * 删除区间内的广告位
     */
    public function delete_advert()
    {
    	
    }

   /**
    * 检查区间名称是否存在
    */
    public function ajax_exist_name()
    {
        $areaname = isset($_GET['areaname']) && empty($_GET['areaname'])==FALSE ? $_GET['areaname'] : NULL;
        $rate = isset($_GET['rate']) && empty($_GET['rate'])==FALSE ? $_GET['rate'] : NULL;

        $che_areaname = D('Settlement.Area');
        $che_result = $che_areaname->where(array('rate_card_id'=>$rate,'area_name'=>$areaname))
                                   ->find();
        
        if($che_result){
             echo json_encode(array(
                'result_no' => 1,
                'result_msg' => '重复'
            ));
        }else{
             echo json_encode(array(
                'result_no' => -1,
                'result_msg' => '可以使用'
            ));
        }
        

          
    }

}
