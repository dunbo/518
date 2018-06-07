<?php
/**
 * Desc:   世界杯活动model
 * @author Sun Tao<suntao@anzhi.com>
 *
 */
class WorldCupModel extends Model {


	public function __construct(){
		parent::__construct();
		$connection = C('DB_CO_ACTIVITY');
	}
    /**
     * 
     * 获取比赛列表
     * @param unknown_type $param
     */
    public function getCupMatch($param) {
    	foreach ($param as $k=>$v) {
    		$param[$k] = trim($v);
    	}
    	$where = array(
    		'status' => 1,
    	);
    	$sqlparam = '?';
    	if ($param['home_name_search']) {
    		$sqlparam = $sqlparam.'home_name_search='.$param['home_name_search'].'&';
            $where['home_name'] = array('like', '%'.$param['home_name_search'].'%');
    	}

    	if ($param['client_name_search']) {
    		$sqlparam = $sqlparam.'client_name_search='.$param['client_name_search'].'&';
            $where['client_name'] = array('like', '%'.$param['client_name_search'].'%');
    	}


        if($param['begintime_s'])
        {
            if($param['begintime_e'])
            {
                $where['begintime']  = array('between',''.strtotime($param['begintime_s']).','.strtotime($param['begintime_e']));
            }else
            {
                $where['begintime']  = array('between',''.strtotime($param['begintime_s']).',9999999999');
            }
        }

        if($param['begintime_e'])
        {
            if($param['begintime_s'])
            {
                $where['begintime']  = array('between',''.strtotime($param['begintime_s']).','.strtotime($param['begintime_e']));
            }else
            {
                $where['begintime']  = array('between','0,'.strtotime($param['begintime_e']));
            }
        }


    	if ($param['result_search']) {
    		$sqlparam = $sqlparam.'result='.$param['result_search'].'&';
            $where['result'] = $param['result_search']-1;
        }



        if($param['create_tm_s'])
        {
            if($param['create_tm_e'])
            {
                $where['create_tm']  = array('between',''.strtotime($param['create_tm_s']).','.strtotime($param['create_tm_e']));
            }else
            {
                $where['create_tm']  = array('between',''.strtotime($param['create_tm_s']).',9999999999');
            }
        }

        if($param['create_tm_e'])
        {
            if($param['create_tm_s'])
            {
                $where['create_tm']  = array('between',''.strtotime($param['create_tm_s']).','.strtotime($param['create_tm_e']));
            }else
            {
                $where['create_tm']  = array('between','0,'.strtotime($param['create_tm_e']));
            }
        }

    	
    	$count = $this->table('cup_match')->where($where)->count();
    	import("@.ORG.Page");
    	$page = new Page($count, 100);
    	//echo $this->getlastsql();
        $page->parameter = $sqlparam;
        $page->setConfig('header','条记录');
        $page->setConfig('first','首页');
        $page->setConfig('last','末页');
        $show =$page->show();
        $list = $this->table('cup_match')->where($where)->order('begintime desc')->select();
        
        return array(
        	'pageshow' => $show,
        	'list' => $list,
        	'sqlparam' => $sqlparam
        );
    }
    
    public function getCupInfo($id) {
    	$where = array(
    		'status' => 1,
    		'id' => $id
    	);
    	
    	$list = $this->table('cup_match')->where($where)->select();
    	return isset($list[0])?$list[0]:array();
    }
    
    public function modifyCupMatch($param) {
    	$time = time();
    	$data['update_tm'] = $time;
    	$data['home_name'] = $param['home_name'];
    	$data['home_pic'] = $param['home_pic'];
    	$data['client_name'] = $param['client_name'];
    	$data['client_pic'] = $param['client_pic'];
    	$data['begintime'] = strtotime($param['begintime']);
    	if ($param['id']) {
    		if (!$param['home_pic']) unset($data['home_pic']);
    		if (!$param['client_pic']) unset($data['client_pic']);
    		$r = $this->table('cup_match')->data($data)->where("id = {$param['id']} and status=1")->save();
    	} else {
    		$data['status'] = 1;
    		$data['create_tm'] = $time;
    		$r = $this->table('cup_match')->add($data);
    	}
    	
    	return $r;
    }
    
	public function setResult($result, $id) {
    	$time = time();
    	$data = array();
    	$data['update_tm'] = $time;
    	$data['result'] = $result;
    	$r = $this->table('cup_match')->data($data)->where(array('id' => $id, "status" => 1))->save();
    	
    	$guess_data = array(
    		'update_tm' => $time,
    		'guess_result' => 1,
    	);
    	$r = $this->table('cup_guess')->data($guess_data)->where(array('match_id' => $id, "guess_content" => $result))->save();
    	
    	$guess_data = array(
    		'update_tm' => $time,
    		'guess_result' => 2,
    	);
    	$r = $this->table('cup_guess')->data($guess_data)->where(array('match_id' => $id, "guess_content" => array('neq', $result)))->save();
    	
    	return 1;
    }
    
    public function del($id) {
    	$data = array('status'=>0);
    	$r = $this->table('cup_match')->data($data)->where(array('id' => $id, "status" => 1))->save();
    	return $r;
    }

    /**
     *  获取中奖人员列表
     *  @final       2013-06-15
     */	
    public function getAwardlist($get,$is_all = 0)
    {
        $where['guess_result']=1;
        $where['award_status']=1;
        if(isset($get['mobile']))
        {
            $where['mobile'] = array('eq', $get['mobile']);
        }

        if(isset($get['is_gua'])&&$get['is_gua']!=2)
        {
            $where['is_gua'] = array('eq', $get['is_gua']);
        }     

        if(isset($get['award_level'])&&$get['award_level']!=0)
        {
            $where['award_level'] = array('eq', $get['award_level']);
        }              

        if(isset($get['match_name']))
        {
            $where['_string'] ='home_name like "%'.$get['match_name'].'%" or client_name like "%'.$get['match_name'].'%"'; 
        }

        if(isset($get['award_btime']))
        {
            if(isset($get['award_etime']))
            {
                $where['award_time']  = array('between',''.strtotime($get['award_btime']).','.strtotime($get['award_etime']));
            }else
            {
                $where['award_time']  = array('between',''.strtotime($get['award_btime']).',9999999999');
            }
        }

        if(isset($get['award_etime']))
        {
            if(isset($get['award_btime']))
            {
                $where['award_time']  = array('between',''.strtotime($get['award_btime']).','.strtotime($get['award_etime']));
            }else
            {
                $where['award_time']  = array('between','0,'.strtotime($get['award_etime']));
            }
        }        

        if(isset($get['gua_btime']))
        {
            if(isset($get['gua_etime']))
            {
                $where['gua_time']  = array('between',''.strtotime($get['gua_btime']).','.strtotime($get['gua_etime']));
            }else
            {
                $where['gua_time']  = array('between',''.strtotime($get['gua_btime']).',9999999999');
            }
        }

        if(isset($get['gua_etime']))
        {
            if(isset($get['gua_btime']))
            {
                $where['gua_time']  = array('between',''.strtotime($get['gua_btime']).','.strtotime($get['gua_etime']));
            }else
            {
                $where['gua_time']  = array('between','0,'.strtotime($get['gua_etime']));
            }
        }


        if(isset($_GET['count']))
        {
            $count = $_GET['count'];
        }else
        {
            $count = $this->table('cup_guess a')->join('INNER JOIN cup_match b ON a.match_id=b.id')->where($where)->count();
        }
        import("@.ORG.Page");
        $page = new Page($count, 10);

        if($is_all==1)
        {
            $rs = $this->table('cup_guess a')->field("a.*,b.home_name,b.client_name")->join('INNER JOIN cup_match b ON a.match_id=b.id')->where($where)->order("award_time desc")->select();
        }else
        {
            $rs = $this->table('cup_guess a')->field("a.*,b.home_name,b.client_name")->join('INNER JOIN cup_match b ON a.match_id=b.id')->where($where)->limit($page->firstRow.','.$page->listRows)->order("award_time desc")->select();
        }
        
        //echo $this->getlastsql();
        $page->parameter ='count='.$count;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();    
        $res = array(
            'list'=>$rs,
            'page'=>$show,
        );
        return $res;
    }

    /**
     *  获取符合抽奖条件的人员
     *  @final       2013-06-15
     */	
    public function getobeAwardlist()
    {
        $where['guess_result']=1;
        $where['award_status']=0;
        $rs = $this->table('cup_guess')->field("id")->where($where)->select();
        return $rs;
    }

    /**
     *  抽奖方法
     *  @final       2013-06-15
     */	
    public function award($ret,$award_num1,$award_num2,$award_num3,$award_num4)
    {
        //变更所有参与的人 中奖状态为 已抽奖 award_status=2
        $all_str = '';
        foreach($ret as $v)
        {
            $all_str = $all_str.','.$v['id'];
        }
        $all_str = substr($all_str,1);
        $this->table('cup_guess')->where('id in ('.$all_str.')')->save(array('award_status'=>2));

        //开始抽奖计算
        $zong = $award_num1+$award_num2+$award_num3+$award_num4;
        $awardarr = array_rand($ret,$zong);

        $award1_str = '';
        if($award_num1!=0)
        {
            for($i=0;$i<$award_num1;$i++)
            {
                $key = ($zong==1)?$awardarr:array_pop($awardarr);
                $award1_str = $award1_str.','.$ret[$key]['id'];
            }
            $award1_str = substr($award1_str,1);
            $this->table('cup_guess')->where('id in ('.$award1_str.')')->save(array('award_status'=>1,'award_level'=>1,'award_time'=>time()));
        }

        $award2_str = '';
        if($award_num2!=0)
        {
            for($i=0;$i<$award_num2;$i++)
            {
                $key = ($zong==1)?$awardarr:array_pop($awardarr);
                $award2_str = $award2_str.','.$ret[$key]['id'];
            }
            $award2_str = substr($award2_str,1);
            $this->table('cup_guess')->where('id in ('.$award2_str.')')->save(array('award_status'=>1,'award_level'=>2,'award_time'=>time()));
        }

        $award3_str = '';
        if($award_num3!=0)
        {
            for($i=0;$i<$award_num3;$i++)
            {
                $key = ($zong==1)?$awardarr:array_pop($awardarr);
                $award3_str = $award3_str.','.$ret[$key]['id'];
            }
            $award3_str = substr($award3_str,1);
            $this->table('cup_guess')->where('id in ('.$award3_str.')')->save(array('award_status'=>1,'award_level'=>3,'award_time'=>time()));
        }

        $award4_str = '';
        if($award_num4!=0)
        {
            for($i=0;$i<$award_num4;$i++)
            {
                $key = ($zong==1)?$awardarr:array_pop($awardarr);
                $award4_str = $award4_str.','.$ret[$key]['id'];
            }
            $award4_str = substr($award4_str,1);
            $this->table('cup_guess')->where('id in ('.$award4_str.')')->save(array('award_status'=>1,'award_level'=>4,'award_time'=>time()));
        }
    }
}
?>
