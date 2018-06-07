<?php 
class ColumnConfTagsModel extends AdvModel {
	 protected $tablePrefix = 'zy_';
	 var $connect_id = 32;
	 public function __construct(){
		parent::__construct();
		$co_Connect = C('DB_ZHIYOO');
		$this -> addConnect($co_Connect, $this->connect_id);
		$this -> switchConnect($this->connect_id);
	}
    
    public function selectall($cid){
        return $this->table('zy_column_conf_tags c')->where("c.cid=$cid")->join('zy_tags t on c.tagid=t.tagid')->order('c.status desc,c.rank asc')->field('c.*,t.tagname')->select();
    }
    
    public function refresh($cid){
        $platform = $this->table('zy_column_conf')->where("cid=$cid")->field('platform')->select();
        $platform = $platform[0]['platform'];
        if(empty($platform))return;
        $res = $this->table('zy_column_content as cc') ->join('left join zy_collect_ext as zce on cc.aid = zce.advid')->where("cc.cid =$cid and zce.platform=$platform")->group('cc.id')->field('cc.id')->select();
        if(empty($res)){//栏目中无内容，清空标签
            $res = $this->where("cid=$cid")->delete();
        }else{
            $colid = $tgid = $dtgid = array();
            foreach($res as $val){
                $colid[] = $val['id'];
            }
            $colid = implode(',',$colid);
            //选出所有内容标签ID
            $res = $this->table('zy_idtotagid')->where("id in ($colid)")->group('tagid')->field('tagid')->select();
            foreach($res as $val){
                $tgid[$val['tagid']] = $val['tagid'];
            }
            
            //选出栏目所有标签ID
            $res = $this->where("cid=$cid")->field('tagid')->select();
            foreach($res as $val){
                if(in_array($val['tagid'],$tgid)){
                    unset($tgid[$val['tagid']]);
                }else{//内容中已不存在此ID
                    $dtgid[] = $val['tagid'];
                }
            }
            if(!empty($dtgid)){
                $dtgid = implode(',',$dtgid);
                $res = $this->where("cid=$cid and tagid in ($dtgid)")->delete();
            }
            
            $rule = $this->getColumnRule($cid);
            if(!empty($tgid)){//剩下的为新ID
                foreach($tgid as $val){
                    if(in_array($val,$rule))$data = array('cid'=>$cid,'tagid'=>$val,'status'=>1);
                    else $data = array('cid'=>$cid,'tagid'=>$val);
                    $res = $this->add($data);
                }
            }
        }
    }
    
    public function getColumnRule($cid){
        $rule = $this->table('zy_column_conf')->where("cid=$cid")->field('rule')->select();
        $rule = explode('_',$rule[0]['rule']);
        return $rule;
    }

    #将线上符合标签规则的内容插入栏目
    #参数：zy_schedule表的id值
    public function insertColumnContent($aid){
        $res = $this->table('zy_schedule')->where("id={$aid}")->find();
        if(!$res) return false;
        $now = time();
        if($res['starttime'] > $now || $res['endtime'] < $now) return false;
        //查询内容的platform,过滤内容的平台和位置
        $where = array('advid'=>$aid,'position'=>1);
        $plat = $this->table('zy_collect_ext')->field('platform')->where($where)->find();
        if(empty($plat)) return false;
        //查询该平台的栏目
        $col = $this->table('zy_column_conf')->where("platform={$plat['platform']}")->select();
        $colid = array();
        foreach ($col as $key => $value) {
            $colid[] = $value['cid'];
        }
        if(empty($colid)) return false;

        //查询标签
        $tagids = $this->table('zy_idtotagid')->where("id={$res['colid']}")->select();
        foreach ($tagids as $key => $value) {
            $tagid_arr[] = $value['tagid'];
        }
        //查询这条内容符合标签规则的栏目
        foreach ($col as $conf) {
            $rule_info = explode('_',$conf['rule']);
            $inner = array_intersect($tagid_arr, $rule_info);
            if($conf['filter'] == 2){
                if(empty($inner)){
                    continue;
                }
                $need_insert_cid[] = $conf['cid'];
            }else{
                $cnt1 = count($tagid_arr);
                $cnt2 = count($inner);
                if($cnt1 != $cnt2){
                    continue;
                }
                $need_insert_cid[] = $conf['cid'];
            }
        }
        #查询所有已经存在的栏目
        $exist = $this->table('zy_column_content')->field('cid')->where(array('cid'=>array('in',$colid),'aid'=>$aid))->select();
        $exist_cid = array();
        foreach ($exist as $key => $value) {
            $exist_cid[] = $value['cid'];
            # code...
        }

        $insert_sql = '';
        foreach ($need_insert_cid as $key => $cid) {
            if(in_array($cid, $exist_cid)) continue;
            $insert_sql .= "('{$cid}','{$res['colid']}','{$res['id']}','{$res['tid']}','{$res['addschtime']}'),";
        }
        // var_dump($insert_sql);die;
        
        if($insert_sql){
            $insert_sql = substr($insert_sql, 0,-1);
            $insert_aids = implode(',', $insert_aid);
            $insert_sql = "INSERT INTO zy_column_content (`cid`,`id`,`aid`,`tid`,`addschtime`) VALUES {$insert_sql}";
            $this->query($insert_sql);
        }

        return true;



    }

    #为新栏目插入内容：更改栏目规则和新建栏目时调用
    public function insertNewColumnCon($cid){
        //查询和栏目规则符合的所有的内容
        $rule = $this->table('zy_column_conf')->field('rule, cid, filter, platform')->where("cid={$cid}")->find();

        $ruleinfo = explode('_',$rule['rule']);
        $extsql = '('.implode(',',$ruleinfo).')';
        $searchcnt = count($ruleinfo);
        
        $idarr = array();
        //筛选符合栏目标签规则的原始数据 
        if ($rule['filter']==2) {
            $queryid = $this->query("select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ");
        }else{
            $queryid = $this->query("select * from (select id,count(*) as cnt from zy_idtotagid where tagid in {$extsql} group by id ) as A  where A.cnt ={$searchcnt}");
        }
        foreach ($queryid as $key => $res) {
            $idarr[] = $res['id'];
        }
        if(!empty($idarr)){
            $extsql =  '('.implode(',',$idarr).')';
            $now = time();
            $result = $this->query("select zce.platform,zs.id,zs.tid,zs.addschtime,zs.colid from zy_schedule as zs left join zy_collect_ext as zce on zs.id=zce.advid where zs.starttime<={$now} and zs.endtime>={$now} and zs.status=0 and zce.platform={$rule['platform']} and zce.position = 1 and zs.colid in {$extsql}");
            foreach ($result as $key => $value) {
                $sql = "INSERT INTO zy_column_content (cid,id,aid,tid,addschtime) VALUES ({$rule['cid']},{$value['colid']},{$value['id']},{$value['tid']},{$value['addschtime']})";
                $this->query($sql);
            }
        }

    }
}