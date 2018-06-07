<?php
//保留小数点后几位，不进位;
function figure_float($num,$precision=2){
    $res = bcadd($num,0,$precision);
    return $res;
}
/*
计算生成红包队列

$money 总金额
$num 总红包数
$min 最小金额
$coef 最大金额系数
$singlemax 单个最大金额
*/

function create_queue($money,$num,$min,$coef,$singlemax){
    if($money<0){
        return false;
    }
    if($num<4){
        return false;
    }
    if($min<0.01){
        return false;
    }
    if($min>$singlemax){
        return false;
    }
    //红包平均值，保留两位小数
    $average = figure_float($money/$num,2);
    if($average < $min) {
        return false;
    }
    //在平均值和最大系数值之间去最小的
	// $min_flag = min($average,2.8); 
    //系数必须大于等于1,小于$min_flag
    if($coef > 2.8 || $coef <1){
        return false;
    }
    if($average == $min){
        return array_fill(0,$num,$min);
    }
    $pack = array();
    $sum = 0;
    for($i=0;$i<$num;$i++){
        $max = figure_float(($money-$sum)/($num-$i),2);
        //金额随机上界限
        $maxrand = floor($max*$coef*100);
        //金额随机下界限
        $minrand = $min * 100;
        
        if($maxrand < $minrand) {
            break;
        }
        $rand = mt_rand($minrand,$maxrand)/100;
        //如果红包的随机金额大于单个红包最大额，红包金额等于单个红包最大额
        if($rand>$singlemax){
            $rand = $singlemax;
        }
        
        $sum = bcadd($sum,$rand,2);
        //如果红包金额大于了总金额 断掉
        if($sum > $money){
            $sum = bcsub($sum,$rand,2);
            break;
        }
        $pack[$i] = $rand;
    }
    $restnum = $num - count($pack);
    $resmon = bcsub($money,$sum,2);
    //如果生成红包总数小于需要发放的红包数，用最低金额补足剩余的红包数量
    if($restnum>0 && $resmon > 0){
        $maxnum = floor($resmon/$min);
        //当剩余的前小于红包
        $restnum = min($maxnum,$restnum);
        $newpack = array_fill(0,$restnum,$min);
        $pack = array_merge($pack,$newpack);
    }
    
    #整体做一次随机排序
    shuffle($pack);
    
   
    return $pack;
    
}

#生成redis红包队列
function create_redpacket_queue($packinfo,$packlist){
    $name = $packinfo['name'];
    $redis = new redis();
    $redis->connect('localhost','6379');
    $sqlheader = 'INSERT INTO sj_redpacket_bill(`packid`,`packtype`,`taskid`,`activeid`,`cashnum`)VALUES';
    $i=0;
    $sql='';
    foreach($packlist as $pack){
        $i++;
        $sql .= "({$packinfo['packid']},{$packinfo['packtype']},{$packinfo['taskid']},{$packinfo['activeid']},{$packinfo['cashnum']}),";
        $redis -> RPUSH($name,$pack);
        //每1000条插入一次流水表
        if($i%1000 == 0){
            $sql = substr($sql,0,-1);
            $model ->query($sqlheader.$sql);
            $sql='';
        }
    }
    if($sql){
        $sql = substr($sql,0,-1);
        $model ->query($sqlheader.$sql);        
    }
    
}



