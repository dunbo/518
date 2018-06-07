<?php
/*
 *   评论处理
 */
include dirname(__FILE__).'/../init.php';
//include_once(dirname(__FILE__).'/../../tools/functions.php');

$model = new GoModel();

ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("comment_deal", "make_comment_deal");
while ($worker->work());

function getCommentData($id,$type){
	global $model;
	if($id&&$type){
		$option = array();
		$option['where']['status'] = 1;	
		if($type == 1){
			$option['where']['id'] = $id;
			$option['table'] = 'sj_soft_comment';
			$option['field'] = 'id,content';		
		}elseif($type == 2){
			$option['where']['pid'] = $id;
			$option['table'] = 'sj_post';
			$option['field'] = 'pid as id,contents as content';
		}
		$res = $model->findOne($option);
	}
	return $res;
}
	
function str_split_unicode($str,$l=1) {
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u' , $str, $result);
	$str_ch= implode('', $result[0]);
	$len_ch = mb_strlen($str_ch, "UTF-8");
    if ($l > 0) {
    	$ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        $len=count($ret);
        $arr = array_count_values($ret);

		arsort($arr);//按键值倒序
		print_r($arr);
		$n=current($arr);
    }
    if(!$len_ch){
    	$len_ch=0;
    }
    if(!$n){
    	$n=0;
    }
    return array($len_ch,$n);
}

function make_comment_deal($jobs){    

    global $model;
	$time = time();

    $jobs = $jobs->workload();
    $data = json_decode($jobs,true);
	$comment = getCommentData($data['id'],$data['type']);
	if($comment){
		list($word_num,$repeat_num)=str_split_unicode($comment['content']);
		$sql = 'insert into sj_soft_comment_extent (c_p_id,c_type,type,create_tm,word_num,repeat_num) values';
		$sql .= '('.$comment['id'].','.$data['type'].',1,'.$time.','.$word_num.','.$repeat_num.')';
		file_put_contents('/tmp/comment_deal.log',$sql.PHP_EOL,FILE_APPEND);
		$model->query($sql);
	}
}


