<?php
class CpdPlatformModel extends AdvModel {
    protected $connect_id = 15;
    private $download_arr = array('2'=>'client_downloaded','3'=>'www_downloaded','10'=>'m_downloaded','4'=>'coop_downloaded','5'=>'other_downloaded');
    //其他站点下载type（51 安卓游戏(c.azyx.com) ，52 安智hd(hd.goapk.com)，53 lgtv(lgtv.anzhi.com) ）
    private $other_download_id = array('51','52','53');
    public function __construct()
    {
        //parent::__construct();
        $myConnect1 = C('DB_CPD_PLATFORM');
        $this -> addConnect($myConnect1, $this->connect_id);
        $this -> switchConnect($this->connect_id);
    }

    //获取每天下载统计
    function get_soft_platform_by_day(&$list){
       // var_dump($data);
        foreach($list as $k=>$v){
            $where = $pdate = array();
            $where['packagename'] = $k;
            foreach($v as $m_k=>$m_v){
                $tmp_m_k = date("Ymd",$m_k);
                $pdate[] = $tmp_m_k;

            }
            $where['pdate'] = array('in',$pdate);
            $platform = $this->table('fin_download_allplatform')->where($where)->field('pdate,type,packagename,SUM(downloadtimes) as sum')->group('pdate,type')->select();
//            echo $this->getLastSql();
//            var_dump($platform);
            if($platform){
                foreach($platform as $p_k=>$p_v){
                    $l_v[$this->download_arr[5]]  = 0;
                    foreach($v as $l_k=>$l_v){
                        if(strtotime($p_v['pdate'])==$l_k){
                            if(isset($this->download_arr[$p_v['type']])){
                                $l_v[$this->download_arr[$p_v['type']]] = $p_v['sum'];
                            }else{
                                //其他下载
                                if(in_array($p_v['type'],$this->other_download_id)){
                                    $l_v[$this->download_arr[5]] += $p_v['sum'];
                                }
                            }
                        }
                        $v[$l_k] = $l_v;
                    }
                }
            }

            $list[$k] = $v;
        }
       // var_dump($list);
        return $list;
    }

    //分小时下载统计
    function download_count_hour($list){
        $return_l = array();
        foreach($list as $k=>$v) {
            $where = $pdate = $softname = array();
            $where['packagename'] = $k;
            foreach ($v as $m_k => $m_v) {
                $tmp_m_k = date("Ymd", $m_k);
                $pdate[] = $tmp_m_k;
            }
            $where['pdate'] = array('in', $pdate);
            //var_dump($where);
            $platform = $this->table('fin_download_allplatform')->where($where)->order('pdate desc')->select();
            //var_dump($platform);
            foreach($platform as $k=>$v){
                //print_r($return_l[$v['pdate']][$v['packagename']]);
                $v['pdate'] = strtotime($v['pdate']);
                if(!isset($return_l[$v['pdate']][$v['packagename']][$v['phour']+1][$this->download_arr[5]]))
                $return_l[$v['pdate']][$v['packagename']][$v['phour']+1][$this->download_arr[5]]  = 0;
                //其他下载
                if(in_array($v['type'],$this->other_download_id)){
                    $return_l[$v['pdate']][$v['packagename']][$v['phour']+1][$this->download_arr[5]] += $v['downloadtimes'];
                }else{
                    $return_l[$v['pdate']][$v['packagename']][$v['phour']+1][$this->download_arr[$v['type']]] = $v['downloadtimes'];
                }
            }
        }

        return $return_l;
    }

    //获取下载完成，下载安装
    function get_soft_down_by_day(&$list){
        foreach($list as $k=>$v) {
            $where = $theday = array();
            $where['package'] = $k;
            foreach ($v as $m_k => $m_v) {
                $tmp_day = date('Ymd',$m_k);
                $theday[] = $tmp_day;
            }
            $where['theday'] = array('in', $theday);
            $soft_down = $this->table('market_soft_down')->where($where)->select();
            foreach($soft_down as $s_k=>$s_v){
                $s_v['theday'] = strtotime($s_v['theday']);
                $v[$s_v['theday']]['down_ok'] = $s_v['down_ok'];
                $v[$s_v['theday']]['down_ok_rate'] = $s_v['down_ok_rate'];
                $v[$s_v['theday']]['install_num'] = $s_v['install_num'];
                $v[$s_v['theday']]['install_num_rate'] = $s_v['install_num_rate'];
            }
           $list[$k] = $v;
        }
        return $list;
    }
}