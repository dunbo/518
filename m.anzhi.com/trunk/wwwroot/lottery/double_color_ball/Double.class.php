<?php
require_once 'Base.class.php';

class Double extends Base{

    public $jackpot;

    public $issue;

	function __construct(){
		parent::__construct();
		$jackpot = $this->get_issue();

		$this->jackpot=$jackpot['jackpot'];
		$this->issue=$jackpot['id'];
        $jackpot_show = floor($this->jackpot*0.4);
        if($jackpot_show<1){
            $jackpot_show=0;
        }
        $this->tplObj -> out['jackpot'] = $jackpot_show;
        $this->tplObj -> out['last_jackpot'] = floor($jackpot['last_jackpot']*0.5);
        $this->tplObj -> out['issue'] = $this->issue;
        $this->tplObj -> out['last_kai_number'] = json_decode(str_replace(array('[0',',0'),array('[',','),$jackpot['last_kai_number']),true);
	}

	//生成随机一组双色球号码
	public static function get_rand_number(){
		$red = range(1,33);
		$red_rand_key = array_rand($red,6);
		$number_arr = array();
		foreach ($red_rand_key as $key => $value) {
			array_push($number_arr,$red[$value]);
		}
		$blue = range(1,16);
		$blue_rand_key = array_rand($blue,1);
		array_push($number_arr,$blue[$blue_rand_key]);
		return $number_arr;
	}

	//期号
	public function get_issue(){
            $option = array(
                    'table' => 'double_color_issue',
                    'where' => array(
                            'status' => array('exp','in (1,2)'),
                    ),
                    'field'=>'*',
                    'order'=>'id desc',
                     'cache_time'=>60,
            );
            $jackpot = $this->model->findOne($option,'lottery/lottery');
            if($jackpot['status']==2)
            {
                $this->tplObj -> out['isover'] = 1;
                $this->redis->set('double_color_ball:isover',1,86400*365);
            }else{
                $this->tplObj -> out['isover'] = 2;
                $this->redis->set('double_color_ball:isover',2,86400*365);
            }
            return $jackpot;
	}


	//已结束期号及号码
	public function get_end_issues(){
            $option = array(
                    'table' => 'double_color_issue',
                    'where' => array(
                            'status' => 2,
                            'kai_number' => array('exp','!=""'),
                    ),
                    'field'=>'*',
                    'order'=>'id desc',
                    'cache_time'=>60,//todo
            );
            $jackpot = $this->model->findAll($option,'lottery/lottery');
            return empty($jackpot) ? false : $jackpot;
	}

	//获取个人购买未开奖号码
	public function get_number_byuid($puid){
            $option = array(
                    'table' => 'double_color_numbers',
                    'where' => array(
                            'issue' => $this->issue,
                            'is_award' => 2,
                            'paystatus' => 1,
                            'puid' => $puid,
                    ),
                    'order'=>'id desc',
                    'field'=>'*',
            );
            $result  = $this->model->findAll($option,'lottery/lottery');
            foreach ($result as $key => $value) {
            	foreach (json_decode($value['red_num'],true) as $k => $v) {
            		$str .= "<li>".$v."</li>";
            	}
            	$str = $str."<li>".$value['blue_num']."</li>";
            	$result[$key]['show_num'] =$str;
            	$str='';
            }
            return empty($result) ? false : $result;
	}

	//获取个人购买已开奖号码
	public function get_number_kai_byuid($issue,$puid,$number){
            $number= json_decode($number,true);
            $blue=array_pop($number);
            $option = array(
                    'table' => 'double_color_numbers',
                    'where' => array(
                            'is_award' => array('exp','!=2'),
                            'puid' => $puid,
                            'issue' => $issue,
                    ),
                    'order'=>'add_time desc',
                    'field'=>'*',
            );
            $result  = $this->model->findAll($option,'lottery/lottery');

            foreach ($result as $key => $value) {
            	foreach (json_decode($value['red_num'],true) as $k => $v) {
                    if(in_array($v, $number)){
                        $gray='gray';
                    }else{
                        $gray='';
                    }
            		$str .= "<li class='".$gray."'>".$v."</li>";
            	}

                if($blue==$value['blue_num']){
                    $str = $str."<li class='gray'>".$value['blue_num']."</li>";
                }else{
                	$str = $str."<li>".$value['blue_num']."</li>";
                }
            	$result[$key]['show_num'] =$str;
            	$str='';
            	if($value['is_award']==1){
            		$aw_str  = $aw_str.$value['prizelevel_name'].' × '.$value['buynumber'].'注'.'、';
                    $prize_str = $prize_str.$value['prizenum'].'元礼券 × 1张、';
            	}
            }
            if(!empty($aw_str)){
                $aw_str = mb_substr($aw_str,0,-1);
                $prize_str = mb_substr($prize_str,0,-1);
                $aw_str = '获奖：'.$aw_str.'<br>奖品：'.$prize_str;
            	$result['aw_str']=$aw_str;
            }
            return empty($result) ? false : $result;
	}

	//新增订单  事务 重复提交  并发  检查活动时间 todo
	public function add_order($uid,$puid,$orderarr,$buynumber){
        if(empty($uid)||empty($puid)||empty($orderarr)||empty($buynumber)){
            return false;
        }

        //是否已停止  redis  如果没有缓存则查库
        $isover = $this->redis->get('double_color_ball:isover');
        if($isover==1||empty($isover)){
            return false;
        }

		$data = array(
			'money' => $buynumber*20,
			'issue' => $this->issue,
			'add_time' => time(),
            'uid' => $uid,
			'puid' => $puid,
			'__user_table' => 'double_color_order'
		);
		$orderid = $this->model->insert($data,'lottery/lottery');
		//var_dump($orderid,$this->model->getsql());
		if($orderid>0){
            foreach ($orderarr as $key => $value) {
                $number = $value['number'];
                $blue = array_pop($number);
                $data = array(
                    'red_num' => json_encode($number),
                    'blue_num' => $blue,
                    'orderid' => $orderid,
                    'issue' => $this->issue,
                    'buynumber' => $value['buynumber'],
                    'add_time' => time(),
                    'puid' => $puid,
                    'uid' => $uid,
                    'username' => $_SESSION['USER_NAME'],
                    '__user_table' => 'double_color_numbers'
                );
                if($value['buynumber']==0){
                    continue;
                }
                $rs = $this->model->insert($data,'lottery/lottery');//如果没有全部写入则回滚
                if($rs==false){
                    //删掉此订单ID的相关信息 返回false
                    $where = array(
                        '__user_table' => 'double_color_order',
                        'orderid' => $orderid
                    );
                    $this->model->delete($where,'lottery/lottery');

                    $where = array(
                        '__user_table' => 'double_color_numbers',
                        'orderid' => $orderid
                    );
                    $this->model->delete($where,'lottery/lottery');

                    return false;
                }
            }

            return $orderid;
		}
        return false;
	}

    //支付成功 修改状态
    public function update_pay_status($orderid,$puid,$money){
        if(empty($orderid)||empty($puid)){
            return false;
        }

        //是否已停止  redis  如果没有缓存则查库
        $isover = $this->redis->get('double_color_ball:isover');
        if($isover==1||empty($isover)){
            return false;
        }
        

        $option = array(
                'table' => 'double_color_order',
                'where' => array(
                        'orderid' => $orderid,
                        'paystatus' => 1
                ),
                'field'=>'*',
        );
        $is_pay = $this->model->findOne($option,'lottery/lottery');
        //echo $this->model->getsql();
        if(!empty($is_pay)){
            return false;
        }

        $data = array(
            'paystatus' => 1,
            'pay_time' => time(),
            '__user_table' => 'double_color_order'
        );
        $where = array(
            'orderid' => $orderid
        );
        $rs_order = $this->model->update($where, $data,'lottery/lottery');

        if($rs_order!=false){
            $data = array(
                'paystatus' => 1,
                'pay_time' => time(),
                '__user_table' => 'double_color_numbers'
            );
            $where = array(
                'orderid' => $orderid,
                'puid' => $puid
            );
            $rs_order_info = $this->model->update($where, $data,'lottery/lottery');
            if($rs_order_info!=false){
                //奖池计算
            $data = array(
                'jackpot' => array('exp','`jackpot`+'.$money),
                '__user_table' => 'double_color_issue'
            );
            $where = array(
                'status' => 1,
                'id' => $this->issue
            );
            $rs_order_info = $this->model->update($where, $data,'lottery/lottery');
                return true;
            }else{
                $data = array(
                    'paystatus' => 0,
                    'pay_time' => time(),
                    '__user_table' => 'double_color_order'
                );
                $where = array(
                    'orderid' => $orderid,
                    'puid' => $puid
                );
                $this->model->update($where, $data,'lottery/lottery');//此条也失败 则记入日志
            }
        }
        return false;
    }

    //跑马灯数据
    public function get_lamp(){

            $option = array(
                    'table' => 'double_color_issue',
                    'order'=>'id desc',
                    'field'=>'id',
                    'limit'=>'2',
                    'cache_time'=>60*60,
            );
            $tmp  = $this->model->findAll($option,'lottery/lottery');

            $option = array(
                    'table' => 'double_color_numbers',
                    'where' => array(
                            'issue' => $tmp[1]['id'],
                            'is_award' => 1,
                            'status' => 1,
                    ),
                    'order'=>'rand()',
                    'field'=>'issue,username,prizelevel_name',
                    'limit'=>'20',
                    'cache_time'=>60*15,
            );
            $result  = $this->model->findAll($option,'lottery/lottery');

            return empty($result) ? false : $result;
    }
}
