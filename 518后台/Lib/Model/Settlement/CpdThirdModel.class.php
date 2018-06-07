<?php
class CpdThirdModel extends AdvModel {
    //第三方合作下载
    protected $connect_id = 15;
    //第三方拇指玩渠道id
    private $third_channel_id = 'ef676a283762';
    public function __construct()
    {
        //parent::__construct();
        $myConnect1 = C('DB_CPD_THIRD');
        $this -> addConnect($myConnect1, $this->connect_id);
        $this -> switchConnect($this->connect_id);
    }

    function get_third_by_day(&$list){
        foreach($list as $k=>$v) {
            $where = $submit_day = array();
            if(!empty($this->third_channel_id)){
                $where['chl_cid'] = $this->third_channel_id;
            }
            $where['package'] = $k;
            foreach ($v as $m_k => $m_v) {
                $submit_day[] = $m_k;
            }
            $where['submit_day'] = array('in', $submit_day);
           // var_dump($where);
            $down = $this->table('sj_download_add')->where($where)->select();
            //var_dump($down);
            if($down){
                foreach($down as $d_k=>$d_v){
                    $list[$k][$d_v['submit_day']]['third_downloaded'] = $d_v['add_cnt'];
                   // $v[$d_v['submit_day']] =
                }

            }
        }
        //var_dump($list);
        return $list;
    }

    //可返回多个渠道（为新版准备）
    function get_third_more(&$list){
        $channel_arr = array();
        foreach($list as $k=>$v) {
            $where = $submit_day = array();
            $where['package'] = $k;
            foreach ($v as $m_k => $m_v) {
                $submit_day[] = $m_k;
            }
            $where['submit_day'] = array('in', $submit_day);
            // var_dump($where);
            $down = $this->table('sj_download_add')->where($where)->select();
            //echo $this->getLastSql();
           // var_dump($down);
            if($down){
                foreach($down as $d_k=>$d_v){
                    $channel_arr[] = $d_v['chl_cid'];
                    $list[$k][$d_v['submit_day']]['third_downloaded'][$d_v['chl_cid']] = $d_v['add_cnt'];
                    // $v[$d_v['submit_day']] =
                }

            }
        }
        array_unique($channel_arr);
        //var_dump($channel_arr);
        if($channel_arr){
            $channel_info = get_table_data(array('chl_cid'=>array('in',$channel_arr)),'sj_channel','chl_cid','chl_cid,chname');
        }
        //var_dump($channel_info);
        //print_r($list);
        return array($list,$channel_info);
    }

    //第三方所有下载统计
    function get_all_third_download($where){
        $down = $this->table('sj_download_add')->where($where)->select();
//        echo $this->getLastSql();
//        var_dump($down);
        $channel_arr = $list = array();
        if($down){
            foreach($down as $d_k=>$d_v){
                $channel_arr[] = $d_v['chl_cid'];
                if(!isset($list[$d_v['submit_day']][$d_v['chl_cid']])) $list[$d_v['submit_day']][$d_v['chl_cid']] = 0;
                $list[$d_v['submit_day']][$d_v['chl_cid']] += $d_v['add_cnt'];
            }

        }
        array_unique($channel_arr);
        if($channel_arr){
            $channel_info = get_table_data(array('chl_cid'=>array('in',$channel_arr)),'sj_channel','chl_cid','chl_cid,chname');
        }
//        var_dump($channel_info);
//        var_dump($list);
        return array($list,$channel_info);
    }

    //获取bi相关下载量
    function get_bi_download($where){
        if(is_array($where['package'])){
            $package = implode(',',$where['package']);
        }else{
            $package = $where['package'];
        }
        $params = array(
            'filter' => array(
                'THEDAY' => array(
                    'start' => $where['begintime'],
                    'end' => $where['endtime'],
                ),
                'PACKAGE_NAME' => $package
            ),
            'column' => array(
                'THEDAY' => 1,
                'PACKAGE_NAME' => 1,
                'WWW_NUM' => 1,
                'WAP_NUM' => 1,
                'COOPERATE_NUM' => 1,
                'CLIENT_NUM' => 1,
                'OTHER_NUM' => 1,
                'ZHIYOO_NUM' => 1,

            ),
            'order' => array(
                1 => 'THEDAY',
            ),
            'sort' => array(
                1 => 'asc',
            ),
            'compute' => array(
                1 => 'THEDAY'
            )
        );
        $params = http_build_query($params);
        $res = json_decode(getDataFromBi($params,'39'),true);
//        var_dump($res);
        $return = array();
        if($res['data']['list']){
            foreach($res['data']['list'] as $k=>$v){
                $key = strtotime($v['THEDAY']);
                $return[$key][$v['PACKAGE_NAME']]['market'] = intval($v['CLIENT_NUM']);
                $return[$key][$v['PACKAGE_NAME']]['wap'] = intval($v['WAP_NUM']);
                $return[$key][$v['PACKAGE_NAME']]['web'] = intval($v['WWW_NUM']);
                $return[$key][$v['PACKAGE_NAME']]['other'] = intval($v['OTHER_NUM']);
                $return[$key][$v['PACKAGE_NAME']]['coop'] = intval($v['COOPERATE_NUM']);
                $return[$key][$v['PACKAGE_NAME']]['ziyoo'] = intval($v['ZHIYOO_NUM']);
            }
        }else{
            return false;
        }
//        var_dump($return);
        return $return;
    }

    //商务下载量
    function get_bi_buss_download($where,&$res){
        if(is_array($where['package'])){
            $package = implode(',',$where['package']);
        }else{
            $package = $where['package'];
        }
        $params = array(
            'filter' => array(
                'PDATE' => array(
                    'start' => $where['begintime'],
                    'end' => $where['endtime'],
                ),
                'PACKAGE_NAME' => $package
            ),
            'column' => array(
                'PDATE' => 1,
                'PACKAGE_NAME' => 1,
                'DOWNLOADTIMESPV' => 1
            ),
            'order' => array(
                1 => 'PDATE',
            ),
            'sort' => array(
                1 => 'asc',
            ),
            'compute' => array(
                1 => 'total'
            )
        );
        $params = http_build_query($params);
        $result = json_decode(getDataFromBi($params,'50'),true);
        if($result['data']['list']){
            foreach($result['data']['list'] as $k=>$v){
                $key = strtotime($v['PDATE']);
                $res[$key][$v['PACKAGE_NAME']]['buss'] = intval($v['DOWNLOADTIMESPV']);
            }
        }else{
            return false;
        }
    }
}
