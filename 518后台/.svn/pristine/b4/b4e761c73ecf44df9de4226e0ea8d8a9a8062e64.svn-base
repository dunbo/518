<?php
#内容生产自动报警邮件
error_reporting(E_ERROR);
ini_set('display_errors', 'on');
include dirname(realpath(__FILE__)). '/../load_gophp.php';

$model = new GoModel();

$table = 'cont_level_stat';

$str .= "Hi,all:<br/>以下表格为今日安智市场生产的内容数量概述，请查看，谢谢！<br/>";

//今天起止时间
$t_starttime = strtotime(date('Y-m-d',time()));
$t_endtime = $t_starttime + 3600*24;
//昨天起止时间
$y_starttime = $t_starttime- 3600*24;
$y_endtime = $t_starttime;

$levellist = array(
    '1' => '资讯内容-A级内容',
    '2' => '专项评测-B 级内容',
    '3' => '体验攻略-C 级内容',
    '4' => '软件视频-D 级内容',
    '5' => '深度评论-E 级内容',
    '6' => '专题集合-F 级内容',
    );
$typelist = array(
    '1' => '单排',
    '2' => '单软件（视频）',
    '3' => '单软件列表单图',
    '4' => '单软件（3图）',
    '5' => '场景化卡片',
    );
$qualtylist = array(
    '1' => '商业化',  
    '2' => '非商业化',  
);

$result = cont_level($qualtylist,$typelist,$levellist,$table);

if(empty($result['timerow'])){
    $str .= '<span style="color:red;font-size:18px;"> 今天和昨天没有内容 </span></br>';
}else{
    $str .= '<table width="800" border="1" cellspacing="0" cellpadding="0"><tr style="background:#BFBFBF;"><td>日期</td><td>内容性质</td><td>展现形式</td><td>内容质量</td><td>资源库产出数量</td><td>昨日资源库产出数量</td><td>变化量</td></tr>';
}
foreach ($result['data'] as $firstkey => $firstvalue) {
    if($firstkey ==1 ){
        if(empty($result['quirerow1'])) continue;
        $str .= '<tr><td rowspan="'.$result['timerow'].'">'.date('Y-m-d',time()).'</td><td rowspan="'.$result['quirerow1'].'">'.$qualtylist[$firstkey].'</td>';
    }else{
        if(empty($result['quirerow2'])) continue;
        if(empty($result['quirerow1'])){
             $str .= '<tr><td rowspan="'.$result['timerow'].'">'.date('Y-m-d',time()).'</td><td rowspan="'.$result['quirerow2'].'">'.$qualtylist[$firstkey].'</td>';
        }else{   
            $str .= '<tr><td rowspan="'.$result['quirerow2'].'">'.$qualtylist[$firstkey].'</td>';
        }
    }
    $k=0;
    $levelcount = count($firstvalue);
    foreach ($firstvalue as $reskey=>$res) {
        $k++;
        $count = count($res);
        if($k == 1){
            $str .= '<td rowspan="'.$count.'">'.$typelist[$reskey].'</td>'; 
        }else{
            $str .= '<tr><td rowspan="'.$count.'">'.$typelist[$reskey].'</td>';
        }
        $j = 0;
        if(is_array($res)){
            foreach ($res as $key=>$value) {
                $j++;
                if($j == 1){
                    $str .= $value;
                }elseif($j == $count){
                    $str .= '<tr style="background:#FFE699;">'.$value;
                }else{
                    $str .= '<tr>'.$value;     
                }  
            }
        }
    }
}
$str .= '</table>';
$str .= '注：</br>(1)变化量=（今日数量-昨日数量）/昨日数量;';

//发送邮件
$email_cont = $str;
$email_arr = array( 
    // 'wuzheng'=>'wuzheng@anzhi.com' ,
    // 'yutong'=>'yutong@anzhi.com', 
    // 'zhangyongkui'=>'zhangyongkui@anzhi.com',
    // 'zhangzhicheng'=>'zhangzhicheng@anzhi.com',
    // 'zhangzhongguo'=>'zhangzhongguo@anzhi.com'
    'lipeng'=>'lipeng@anzhi.com',
	'cuixifeng'=>'cuixifeng@anzhi.com',
	'zhaoshuai'=>'zhaoshuai@anzhi.com',
	'caoliwei' => 'caoliwei@anzhi.com'
);

load_helper('password');
$subject = '安智内容生产每日提醒';
foreach($email_arr as $username => $email){ 
    __http_post_email(array('email' => $email, 'name' => $username, 'subject' => $subject, 'content' => $email_cont));  

}

/*=============================================================================================*/

function cont_level($qualtylist,$typelist,$levellist,$table){
    global $model,$t_starttime,$t_endtime,$y_starttime,$y_endtime;
    foreach($qualtylist as $qualitykey => $qualityvalue){ 
        $data = array();
        foreach ($typelist as $typekey => $typevalue) {
            $total_t = 0;
            $total_y = 0;
            $list = array();
            $k = 1;
            foreach ($levellist as $levelkey => $levelvalue) {
                $k++;
                if( $typekey == 5){ //场景化卡片
                    $result = $model -> query("select count(bgcard_id) as num from $table where modify_tm >=$t_starttime and modify_tm<$t_endtime and status=1 and bgcard_id>0 and cont_type=$typekey and cont_level=$levelkey and cont_quality=$qualitykey");
                    $result1 = $model -> query("select count(bgcard_id) as num from $table where modify_tm >=$y_starttime and modify_tm<$y_endtime and status=1 and bgcard_id>0 and cont_type=$typekey and cont_level=$levelkey and cont_quality=$qualitykey");
                }else{ //其他
                    $result = $model -> query("select count(cont_id) as num from $table where modify_tm >=$t_starttime and modify_tm<$t_endtime and status=1 and cont_id>0 and cont_type=$typekey and cont_level=$levelkey and cont_quality=$qualitykey");
                    $result1 = $model -> query("select count(cont_id) as num from $table where modify_tm >=$y_starttime and modify_tm<$y_endtime and status=1 and cont_id>0 and cont_type=$typekey and cont_level=$levelkey and cont_quality=$qualitykey");
                }
                $result = $model -> fetch($result);
                $levelt_num = $result['num'] ? $result['num'] : 0;
                
                $result1 = $model -> fetch($result1);
                $levely_num = $result1['num'] ? $result1['num'] : 0; 
                if($levely_num == 0){ $rate = 0;}else{ $rate = round(($levelt_num-$levely_num)/$levely_num,2); }
                //拼接数据
                if($levely_num != 0 || $levelt_num != 0){
                    $list[$levelkey] = '<td>'.$levellist[$levelkey].'</td><td>'.$levelt_num.'</td><td>'.$levely_num.'</td><td>'.$rate.'</td></tr>';
                    $date_rows++;
                    if($qualitykey == 1) $quirerow1++;
                    if($qualitykey == 2) $quirerow2++;
                }
                $total_t += $levelt_num;
                $total_y += $levely_num;
            }
            if($total_y == 0){ $total_rate = 0;}else{ $total_rate = round(($total_t-$total_y)/$total_y,2); }
            if($total_t != 0 || $total_y != 0){
                $list[$k] = '<td>总计</td><td>'.$total_t.'</td><td>'.$total_y.'</td><td>'.$total_rate.'</td></tr>';
                $date_rows++;
                if($qualitykey == 1) $quirerow1++;
                if($qualitykey == 2) $quirerow2++;
                $data[$typekey] = $list;
            }
        }
        $source['data'][$qualitykey] = $data;
        $source ['timerow'] = $date_rows;
        $source ['quirerow1'] = $quirerow1;
        $source ['quirerow2'] = $quirerow2;
    }
    return $source;
}

