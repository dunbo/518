<?php
/**
 * Desc:   标签model
 * @author Sun Tao<suntao@anzhi.com>
 * @final       2013-04-28
 *
 */
class TagsModel extends Model {

    protected $trueTableName = ' sj_tag';

    /**
     *  根据分类ID获取标签名
     *  @final       2013-05-12
     */	
    public function getTagsnamebycid($cid)
    {
        $cid = str_replace(',','',$cid);
        $rs = $this->table('sj_category')->field('tag_ids')->where("category_id= '$cid'")->find();
        $tag_ids = $rs['tag_ids'];
        $tag_ids = substr($tag_ids,1);
        $tag_ids = substr($tag_ids,0,-1);

        if(empty($tag_ids))
        {
            return -1;
        }

        $tag_ids = str_replace('j','',$tag_ids);
        $res = $this->field('tag_name')->where("tag_id in ($tag_ids)")->select();

        $tag_names_arr= array();
        foreach($res as $v)
        {
            $tag_names_arr[] = $v['tag_name'];
        }
        return $tag_names_arr;
    }

    
    /**
     *  根据标签名获取标签id  from sj_tag
     *  @final       2013-05-20
     */	
    public function getTagidbyname($tag_name)
    {
        $rs = $this->field('tag_id')->where("tag_name= '$tag_name' and status=1")->find();
        return $rs['tag_id'];
    }

    /**
     *  根据标签id获取标签名  from sj_tag
     *  @final       2013-05-21
     */	
    public function getTagnamebyid($tag_id)
    {
        $rs = $this->field('tag_name')->where("tag_id= '$tag_id' and status=1")->find();
        return $rs['tag_name'];
    }

    /**
     *  根据标签名like查询记录
     *  @final       2013-05-21
     */	
    public function getTaglistbylike($like_tag_name, $offset=0, $size=10)
    {
        $map['tag_name'] = array('like','%'.$like_tag_name.'%');
        $map['status'] = array('eq','1');
        $rs = $this->field('tag_id,tag_name')->where($map)->limit("$offset,$size")->select();
        return $rs;
    }
	 /**
     *  查询所有标签记录
     *  @final       2015-09-30  add by shitingting
     */	
    public function getTagslist()
    {
        $map['status'] = array('eq','1');
        $rs = $this->field('tag_id,tag_name')->where($map)->select();
        return $rs;
    }

    /**
     *  根据标签ID获取标签名，有荐
     *  @final       2013-05-07
     */	
    public function getTagsname($tags_id)
    {
        $tag_names_str = '';
        $tagarr = explode(',',$tags_id);
        $tagarr = array_filter($tagarr);
        foreach($tagarr as $v)
        {
            if(substr($v,0,1)=='j')
            {
                $v = str_replace('j','',$v);
                $houzhui = '(荐)';
            }else{
                $houzhui = '';
            }

            $rs = $this->field('tag_name')->where("tag_id= '$v' and status=1")->find();
            $tag_name = $rs['tag_name'];
            $tag_names_str = $tag_names_str.','.$tag_name.$houzhui;
        }
        return substr($tag_names_str,1);
    }

    /**
     *  检查传入的标签名称是否在传入的分类下有软件
     *  @final       2013-05-07
     */	
    public function is_has_soft($cid,$tagsarr)
    {
        $tagidstr = ',';
        foreach($tagsarr as $v)
        {
            if(strpos($v,'(荐)'))
            {
                $v = str_replace('(荐)','',$v);
                $prefix = 'j';
            }else{
                $prefix='';
            }

            $rs = $this->field('tag_id')->where("tag_name = '$v' and status=1")->find();
            $tag_id = $rs['tag_id'];
            if(!$rs['tag_id']) //不存在 返回
            {
                $resarr = array(
                    'code'=>'-1',
                    'value'=>$v,
                );
                return $resarr;
            }else{
                //$sql = "SELECT COUNT(softid) as num FROM sj_soft WHERE sj_soft.`category_id`=',$cid,' AND sj_soft.`status` =1 AND hide=1 AND package IN (SELECT package FROM sj_tag_package WHERE tag_id=$tag_id)";
                $sql ="SELECT softid FROM sj_soft a INNER JOIN sj_tag_package b ON a.package = b.package WHERE a.status = 1 AND hide = 1 AND b.tag_id = '$tag_id' AND a.category_id = ',$cid,' LIMIT 1";
                $res = $this->query($sql);
                if(!$res[0]['softid']){
                    $resarr = array(
                        'code'=>'-1',
                        'value'=>$v,
                    );
                    return $resarr;
                }
            }

            $tagidstr = $tagidstr.$prefix.$tag_id.',';
        }
        $resarr = array(
            'code'=>'1',
            'value'=>$tagidstr,
        );
        return $resarr;
    }

    /**
     *  更改NOTE表中的标签内容 
     *  @final       2013-05-06
     */	
    public function update_soft_note($package,$tagarr)
    {
        $tagstr = ',';
        foreach($tagarr as $v)
        {
            $rs = $this->field('tag_id')->where("tag_name = '$v' and status=1")->find();
            $tagstr = $tagstr.$rs['tag_id'].',';
        }
        $data['tag_ids'] = $tagstr;
        $data['tag_time'] = time();
        return $rs = $this->table('sj_soft_note')->where("package='$package'")->save($data);
    }


    /**
     *  获取无标签软件列表
     *  @final       2013-05-06
     */	
    public function getnotagsoftlist($get)
    {
        if(isset($get['order'])){
            if($get['order']==1){
                $order = 'total_downloaded desc';
            }else if($get['order']==2){
                $order = 'last_refresh desc';
            }
        }
        else
        {
            $order = 'total_downloaded desc';
        }

        //$where="package NOT IN (SELECT package FROM sj_tag_package) AND a.STATUS=1  AND a.hide=1";
        $where['a.status'] = array('eq',1);
        $where['a.hide'] = array('eq',1);
        //$where['b.status'] = array('neq',0);
        //$where['package']  = array('not in','SELECT package FROM sj_tag_package c inner join sj_tag d on c.tag_id = d.tag_id where d.status=1');
        $where['package']  = array('not in','SELECT package FROM sj_tag_package');

        if(isset($get['softname']))
        {
            $where['a.softname'] = array('like', '%'.$get['softname'].'%');
        }

        if(isset($get['package']))
        {
            //$where['a.package'] = array('like', '%'.$get['package'].'%');
            $where['a.package'] = array('eq', $get['package']);
        }

        if(isset($get['begintime']))
        {
            $where['a.last_refresh']  = array('between',''.strtotime($get['begintime']).','.strtotime($get['endtime']));
        }

        $cids = substr($get['cateid'],0,-1);
        if(strlen($cids)>0){
            $cids = "',".str_replace(",",",',',",$cids).",'";
            $where['a.category_id']  = array('in',$cids);
        }

        if(isset($_GET['count']))
        {
            $count = $_GET['count'];
        }else
        {
            $count = $this->table('sj_soft a')->field("softid")->join('INNER JOIN sj_category b ON b.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2)')->where($where)->count();
        }
        import("@.ORG.Page");
        $page = new Page($count, 10);

        $rs = $this->table('sj_soft a')->field("softid,softname,package,total_downloaded,a.last_refresh,b.name")->join('INNER JOIN sj_category b ON b.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2)')->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //echo $this->getlastsql();

        $page->parameter ='count='.$count;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();    
        $res = array(
            'list'=>$rs,
            'page'=>$show,
            'count'=>$count,
        );
        return $res;
    }

    /**
     *  根据package和标签名称 新增对照表的记录,如果没有该标签则自动新增
     *  @final       2013-05-05
     */	
    public function add_soft_tag($package,$add_tag_arr)
    {
        foreach($add_tag_arr as $v)
        {
            $rs = $this->field('tag_id')->where("tag_name = '$v' and status=1")->find();
            if($rs===NULL&&trim($v)!=''){
                //没有该标签，新增
                $data1['tag_name']=$v;
                $data1['addtime']=time();
                $data1['update_tm']=time();
                $insertid = $this->table('sj_tag')->add($data1);
                if($insertid>0)
                {
                    $newdata['package'] = $package;
                    $newdata['tag_id'] = $insertid;
                    $this->table('sj_tag_package')->add($newdata);
                }
            }else{
                //标签已存在，添加到对照表
                $data['package'] = $package;
                $data['tag_id'] = $rs['tag_id'];
                $this->table('sj_tag_package')->add($data);
            }
        }
        return true;
    }

    /**
     *  根据package和标签名称 删除对照表的记录
     *  @final       2013-05-05
     */	
    public function del_soft_tag($package,$deltags)
    {
        $rs = $this->field('GROUP_CONCAT(tag_id) as tag_ids')->where('tag_name in ('.$deltags.') and status=1')->find();
        $del_tagids = $rs['tag_ids'];
        $res = $this->table('sj_tag_package')->where('package = "'.$package.'" and tag_id in ('.$del_tagids.')')->delete();
        return $res;
    }


    /**
     *  根据SOFTID获取软件详情包括标签  supwatersql
     *  @final       2013-05-04
     */	
    public function getsoftinfo($softid)
    {
        $sql ="SELECT a.softid,a.softname,a.category_id,a.package,GROUP_CONCAT(c.tag_id ORDER BY c.tag_id ASC)as tag_ids,GROUP_CONCAT(c.tag_name order by c.tag_name ASC) AS tags FROM sj_soft a INNER JOIN  sj_tag_package b ON a.package = b.package INNER JOIN sj_tag c ON b.`tag_id`=c.`tag_id` WHERE a.status=1 AND a.hide=1 and c.status=1 AND a.`softid`='$softid'";
        $rs = $this->query($sql);
        return $rs[0];
    }

    /**
     *  获取有标签软件列表
     *  @final       2013-04-29
     */	
    public function getagsoftlist($get)
    {
        if(isset($get['order'])){
            if($get['order']==1){
                $order = 'total_downloaded desc';
            }else if($get['order']==2){
                $order = 'last_refresh desc';
            }
        }else{
            $order = 'total_downloaded desc';
        }

        $where['a.status'] = array('eq',1);
        $where['a.hide'] = array('eq',1);
        if(isset($get['softname']))
        {
            $where['a.softname'] = array('like', '%'.$get['softname'].'%');
        }

        if(isset($get['package']))
        {
            //$where['a.package'] = array('like', '%'.$get['package'].'%');
            $where['a.package'] = array('eq', $get['package']);
        }

        if(isset($get['tags']))
        {
            $tags = $get['tags'];
            $tag_id = $this->getTagidbyname($tags);
            $where['tag_id'] = array('eq',$tag_id);
        }

        if(isset($get['begintime']))
        {
            $where['a.last_refresh']  = array('between',''.strtotime($get['begintime']).','.strtotime($get['endtime']));
        }

        $cids = substr($get['cateid'],0,-1);
        if(strlen($cids)>0){
            $cids = "',".str_replace(",",",',',",$cids).",'";
            //$where['d.category_id']  = array('in',$cids);
            $where['a.category_id']  = array('in',$cids);
        }
        if(isset($_GET['count']))
        {
            $count = $_GET['count'];
        }else
        {
            $res = $this->table('sj_soft a')->field("count(DISTINCT(a.softid)) as tp_count")->join('INNER JOIN sj_tag_package b ON a.package = b.package')->join('INNER JOIN sj_category d ON d.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2)')->where($where)->find();
            $count = $res['tp_count'];
            //echo $this->getlastsql();
        }
        import("@.ORG.Page");
        $page = new Page($count, 10);

        $rs = $this->table('sj_soft a')->field("DISTINCT(a.softid),a.softname,a.package,total_downloaded,a.last_refresh,d.name")->join('INNER JOIN sj_tag_package b ON a.package = b.package')->join('INNER JOIN sj_category d ON d.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2)')->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //echo $this->getlastsql();


        $page->parameter ='count='.$count;
        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();    
        $res = array(
            'list'=>$rs,
            'page'=>$show,
            'count'=>$count,
        );
        return $res;
    }    

    /**
     *  获取有标签软件列表  supwater
     *  @final       2013-04-29
     */	
    /*
    public function getagsoftlist($get)
    {
        if(isset($get['order'])){
            if($get['order']==1){
                $order = 'total_downloaded desc';
            }else if($get['order']==2){
                $order = 'last_refresh desc';
            }
        }else{
            $order = 'total_downloaded desc';
        }

        $where['a.status'] = array('eq',1);
        $where['a.hide'] = array('eq',1);
        $where['c.status'] = array('eq',1);
        //$where['d.status'] = array('neq',0);
        if(isset($get['softname']))
        {
            $where['a.softname'] = array('like', '%'.$get['softname'].'%');
        }

        if(isset($get['package']))
        {
            $where['a.package'] = array('like', '%'.$get['package'].'%');
        }

        if(isset($get['tags']))
        {
            $tags = $get['tags'];
            $having = 'tags LIKE "%,'.$tags.',%"';
        }

        if(isset($get['begintime']))
        {
            $where['a.last_refresh']  = array('between',''.strtotime($get['begintime']).','.strtotime($get['endtime']));
        }

        $cids = substr($get['cateid'],0,-1);
        if(strlen($cids)>0){
            $where['d.category_id']  = array('in',$cids);
        }

        $res = $this->table('sj_soft a')->field("DISTINCT(a.softid),a.softname,a.package,total_downloaded,a.last_refresh,d.name,CONCAT(',',GROUP_CONCAT(c.tag_name),',') AS tags")->join('INNER JOIN sj_tag_package b ON a.package = b.package')->join('INNER JOIN sj_tag c ON b.`tag_id`=c.`tag_id`')->join('INNER JOIN sj_category d ON d.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2)')->where($where)->group('a.softname')->having($having)->select();
        $count = count($res);

        import("@.ORG.Page");
        $page = new Page($count, 10);

        $rs = $this->table('sj_soft a')->field("DISTINCT(a.softid),a.softname,a.package,total_downloaded,a.last_refresh,d.name,CONCAT(',',GROUP_CONCAT(c.tag_name order by c.tag_name ASC),',') AS tags")->join('INNER JOIN sj_tag_package b ON a.package = b.package')->join('INNER JOIN sj_tag c ON b.`tag_id`=c.`tag_id`')->join('INNER JOIN sj_category d ON d.category_id =SUBSTR(a.category_id,2,LENGTH(a.category_id)-2)')->where($where)->group('a.softname')->having($having)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //echo $this->getlastsql();

        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();    
        $res = array(
            'list'=>$rs,
            'page'=>$show,
            'count'=>$count,
        );
        return $res;
    }*/

    /**
     *  获取标签列表
     *  @final       2013-04-28
     */	
    /*
    public function getaglist($get)
    {
        if(isset($get['tag_name']))
        {
            $where['tag_name'] = array('like', '%'.$get['tag_name'].'%');
        }
        if(isset($get['bsoftnum']))
        {
            $having = "softnum>=".$get['bsoftnum']." and softnum<=".$get['esoftnum']."";
        }

        if(isset($get['bclick_num']))
        {
            $where['click_num']  = array('between',''.$get['bclick_num'].','.$get['eclick_num'].'');
        }

        if(isset($get['bdown_num']))
        {
            $where['down_num']  = array('between',''.$get['bdown_num'].','.$get['edown_num'].'');
        }

        $order = 'softnum desc';
        $where['a.status'] = array('eq',1);

        switch ($get['order'])
        {
            case 1:
                $order = 'softnum desc';
                break;
            case 2:
                $order = 'softnum asc';
                break;
            case 3:
                $order = 'click_num desc';
                break;
            case 4:
                $order = 'click_num asc';
                break;
            case 5:
                $order = 'down_num desc';
                break;
            case 6:
                $order = 'down_num asc';
                break;
        }
        import("@.ORG.Page");

        $rs = $this->table('sj_tag a')->field('a.tag_id,tag_name,click_num,down_num,count(c.package) as softnum')->join('left join sj_tag_package b on a.tag_id =b.tag_id left JOIN sj_soft c ON b.`package`=c.`package` and c.status=1 and c.hide=1')->where($where)->group('a.tag_id')->having($having)->select();
        $count = count($rs);
        $page = new Page($count, 10);

        $rs = $this->table('sj_tag a')->field('a.tag_id,tag_name,click_num,down_num,count(c.package) as softnum')->join('left join sj_tag_package b on a.tag_id =b.tag_id left JOIN sj_soft c ON b.`package`=c.`package` and c.status=1 and c.hide =1')->where($where)->group('a.tag_id')->having($having)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //echo $this->getlastsql();

        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $res = array(
            'list'=>$rs,
            'page'=>$show,
            'count'=>$count,
        );
        return $res;
    }*/    

    /**
     *  获取标签列表
     *  @final       2013-04-28
     */	
    public function getaglist($get)
    {
        if(isset($get['tag_name']))
        {
            $where['tag_name'] = array('like', '%'.$get['tag_name'].'%');
        }
        if(isset($get['bsoftnum']))
        {
            $where['soft_num']  = array('between',''.$get['bsoftnum'].','.$get['esoftnum'].'');
        }

        if(isset($get['bclick_num']))
        {
            $where['click_num']  = array('between',''.$get['bclick_num'].','.$get['eclick_num'].'');
        }

        if(isset($get['bdown_num']))
        {
            $where['down_num']  = array('between',''.$get['bdown_num'].','.$get['edown_num'].'');
        }

        $order = 'soft_num desc';
        $where['status'] = array('eq',1);

        switch ($get['order'])
        {
            case 1:
                $order = 'soft_num desc';
                break;
            case 2:
                $order = 'soft_num asc';
                break;
            case 3:
                $order = 'click_num desc';
                break;
            case 4:
                $order = 'click_num asc';
                break;
            case 5:
                $order = 'down_num desc';
                break;
            case 6:
                $order = 'down_num asc';
                break;
        }
        import("@.ORG.Page");

        $count = $this->field('tag_id,tag_name,soft_num,click_num,down_num')->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->count();

        $page = new Page($count, 10);

        $rs = $this->field('tag_id,tag_name,soft_num,click_num,down_num')->where($where)->limit($page->firstRow.','.$page->listRows)->order($order)->select();
        //echo $this->getlastsql();

        $page->setConfig('header','篇记录');
        $page->setConfig('first','<<');
        $page->setConfig('last','>>');
        $show =$page->show();
        $res = array(
            'list'=>$rs,
            'page'=>$show,
            'count'=>$count,
        );
        return $res;
    }


    /**
     *  添加单条新标签
     *  @final       2014-04-28
     */	
    public function addtag($tag_name)
    {
        $is_exist = $this->where("status=1 and tag_name='$tag_name'")->count();
        if($is_exist==1) //已存在
        {
            return -1;
        }else{
            $data['tag_name'] = $tag_name;
            $data['addtime'] = time();
            $rs = $this->add($data);
            return $rs;
        }
    }

    /**
     *  删除标签
     *  @final       2014-05-04
     */
    public function deltag($tag_id)
    {
        /*
        //暂时没有去note表把要删除的ID替换成空 操作会慢一些，以后看情况 可以添加
        $tmp_tag_id = "',".$tag_id.",'";
        $res = $this->table('sj_tag_package')->field('package')->where("tag_id= ".$tag_id)->select();
        foreach($res as $v){
            $package = $v['package'];
            $data_note['tag_ids'] = array('exp','REPLACE(tag_ids,'.$tmp_tag_id.',"")');
            $this->table('sj_soft_note')->where("package='".$package."'")->save($data_note);
        }
        */

        $this->table('sj_tag_package')->where("tag_id= ".$tag_id)->delete();
        $newdata['status'] = 0;
        $rs = $this->data($newdata)->where("tag_id= ".$tag_id)->save();
        return $rs;
    }

    /**
     *  保存标签
     *  @final       2014-04-28
     */	
    public function editag($tag_id,$tag_name)
    {
        $res= $this->field('tag_id')->where("status=1 and tag_name='$tag_name'")->find();
        if($res['tag_id']>0) //已存在 合并
        {
            return -1;
            /*
            $data['tag_id'] = $res['tag_id'];
            $rs = $this->table('sj_tag_package')->data($data)->where("tag_id= ".$tag_id)->save();
            if($rs!=false)
            {
                $newdata['status'] = 0;
                return $this->data($newdata)->where("tag_id= ".$tag_id)->save();
            }
            */
        }else{
            $data['tag_name'] = $tag_name;
            $rs = $this->data($data)->where("tag_id= ".$tag_id)->save();
            return $rs;
        }
    }

    /**
     *  根据分类ID获取该分类下包含软件最多的tag前10
     *  @final       2013-05-05
     */	
    /*
     * 暂时不用
    public function getSoftTop($cid)
    {
        $sql ="SELECT COUNT(DISTINCT(a.package)) AS num,a.tag_id,c.tag_name FROM sj_tag_package a INNER JOIN sj_soft b ON a.package = b.package 
 INNER JOIN sj_tag c ON a.`tag_id` = c.`tag_id` WHERE category_id='$cid'  GROUP BY tag_id ORDER BY num DESC LIMIT 10";
        $rs = $this->query($sql);
        foreach($rs as $v){
            $arr[]=$v['tag_name'];
        }
        return $arr;
    }
     */


    /**
     *  对某个包名添加标签，如果标签不存在则新增
     *  @final       2014-08-08
     */	
    public function add_package_tags($package,$tags)
    {
        //$tags = "图骥文学,地名文化,文学,阅读,文化";
        $tagarr = explode(',',$tags);

        foreach($tagarr as $v)
        {
			if(empty($v)) continue;
            $rs = $this->field('tag_id')->where("tag_name = '$v' and status=1")->find();
            if($rs===NULL){
                //没有该标签，新增
                $data1['tag_name']=$v;
                $data1['addtime']=time();
                $data1['update_tm']=time();
                $insertid = $this->table('sj_tag')->add($data1);
                if($insertid>0)
                {
                    $newdata['package'] = $package;
                    $newdata['tag_id'] = $insertid;
                    $this->table('sj_tag_package')->add($newdata);
                }
            }else{
                //查下对应的关系是否存在
                $res = $this->table('sj_tag_package')->field('tag_id')->where("package= '$package' and tag_id =".$rs['tag_id'])->find();
                if($res===NULL){
                    $data['package'] = $package;
                    $data['tag_id'] = $rs['tag_id'];
                    $this->table('sj_tag_package')->add($data);
                }
            }
        }

        //更新sj_soft_note
        $data_note = array();
        $ret = $this->table('sj_tag_package')->field('distinct(tag_id)')->where("package= '$package'")->select();

        $tagstr = ',';
        foreach($ret as $v)
        {
            $tagstr = $tagstr.$v['tag_id'].',';
        }
        $data_note['tag_ids'] = $tagstr;
        $data_note['tag_time'] = time();
        $this->table('sj_soft_note')->where("package='$package'")->save($data_note);

        return true;
    }
	//取热门标签数据
	function get_hot_tag($c_id){
		$where = array(
			'category_id' => $c_id,
			'status' => 1,
		);
		$ret = $this->table('sj_hot_tag')->field('id,tag_name,prefix')->where($where)->order('prefix asc')->select();
		if($_GET['from'] == 1){
			return $ret;
			exit;
		}
		$return = array();
		$ABCD = array('A','B','C','D');
		$EFGH = array('E','F','G','H');
		$IJKL = array('I','J','K','L');
		$MNOP = array('M','N','O','P');
		$QRST = array('Q','R','S','T');
		$UVWXYZ = array('U','V','W','X','Y','Z');
		foreach($ret as $k => $v){
			if(in_array($v['prefix'],$ABCD)){
				$return['A B C D'][] = $v;
			}else if(in_array($v['prefix'],$EFGH)){
				$return['E F G H'][] = $v;
			}else if(in_array($v['prefix'],$IJKL)){
				$return['I H K L'][] = $v;
			}else if(in_array($v['prefix'],$MNOP)){
				$return['M N O P'][] = $v;
			}else if(in_array($v['prefix'],$QRST)){
				$return['Q R S T'][] = $v;
			}else if(in_array($v['prefix'],$UVWXYZ)){
				$return['U V W X Y Z'][] = $v;
			}else{
				$return['0-9 其他'][] = $v;
			}
		}
		return $return;
	}
	//添加热门标签
	function add_hot_tag(){
		$where = array(
			'category_id' => $_POST['c_id'],
			'status' => 1,
			'tag_name' => trim($_POST['tag_name']),
		);
		$ret = $this->table('sj_hot_tag')->field('tag_name')->where($where)->find();
		if($ret) return(array('code'=>0,'msg'=>'标签已存在！'));
		//转拼音
		$pinyin = Pinyin($_POST['tag_name']);
		$prefix = strtoupper(substr($pinyin,0,1));
		$map = array(
			'category_id' => $_POST['c_id'],
			'tag_name' => $_POST['tag_name'],
			'prefix' => $prefix,
			'add_tm' => time(),
		);
		$res = $this->table('sj_hot_tag')->add($map);
		if($res){
			return(array('code'=>1,'msg'=>'添加成功！','id'=>$res));
		}else{
			return(array('code'=>0,'msg'=>'添加失败！'));
		}
	}
	//获取标签
	function get_tag($package,$from){
		if($from == 1){
			//线上列表编辑标签
			//自定义标签
			$res = $this -> table('sj_tag_history') -> where("package='{$package}' and status=1 and type=1")-> field('tag_name')->order('update_tm asc')->find();
			$ret = $this -> table('sj_tag_package a')->join('sj_tag b ON a.tag_id=b.tag_id') -> where("a.package='{$package}'")->field('a.package,a.tag_id,b.tag_name')->select();
			$hot_tag = array();
			foreach($ret as $v){
				$hot_tag[] = $v['tag_name'];
			}
			//分类标签
			$where = array(
				'tag_name' => array('in',$hot_tag),
				'status' => 1
			);
			$hot_list = get_table_data($where,"sj_hot_tag","tag_name","tag_name");
			$return = array();
			$return2 = array();
			$return3 = array();
			foreach($ret as $v){
				if($v['tag_name'] == $res['tag_name']){
					$return[1] = $v['tag_name'];
				}else if($hot_list[$v['tag_name']]){
					$return3[] = $v['tag_name'];
				}else{
					$return2[] = $v['tag_name'];
				}
				if($return2) $return[2] = implode(',',$return2);
				if($return3) $return[3] = implode(',',$return3);
			}
		}else{		
			$res = $this -> table('sj_tag_history') -> where("package='{$package}'")-> field('tag_name,type,status')->order('update_tm asc')->select();
			$return = array();
			foreach($res as $k => $v){
				if($v['type'] == 1){
					$return[1] = $v['tag_name'];
				}else if($v['type'] == 2 && $v['status']==1){
					$return[2] = $v['tag_name'];
				}else if($v['type'] == 3 && $v['status']==1){
					$return[3] = $v['tag_name'];
				}
			}
			unset($res);
		}
		return $return;
	}
	//更新自定义标签
	function save_tag_history($package,$tag,$status=0){
		$where = array(
			'package'=>$package,
			'type'=>1,
		);
		//查找到最后一条
		$list = $this -> table('sj_tag_history') -> where($where)->order('create_tm desc')->field('tag_name')->find();
		if($list){
			$where = array(
				'package'=>$package,
				'type'=>1,
				'tag_name'=>$list['tag_name'],
			);
			$map = array(
				'tag_name'=>$tag
			);
			if($status) $map['status'] = $status;
			return $this -> table('sj_tag_history') -> where($where)->save($map);
		}else if($tag && $package){
			//线上编辑软件如果自定义标签没有就新加一条
			$map = array(
				'tag_name'=>$tag,
				'package'=>$package,
				'create_tm' => time(),
				'type' => 1,
				'status'=>1
			);
			return $this -> table('sj_tag_history')->add($map);
		}else{
			//继续执行下面的逻辑
			return 1;
		}
	}
	//更新采集标签和分类标签
	function save_cj_hot_tag($pkg,$tag){
		foreach($tag as $k => $v){
			//if(empty($v)) continue; 
			$where = array(
				'package'=>$pkg,
				'type'=>$k,
			);
			$res = $this -> table('sj_tag_history') -> where($where)->field('id')->find();
			$map = array(
				'tag_name'=>$v,
			);
			if($res){
				$this -> table('sj_tag_history') -> where($where)->save($map);
			}else{
				$map['type'] = $k;
				$map['package'] = $pkg;
				$map['status'] = 1;
				$this -> table('sj_tag_history')->add($map);
			}
		}
	}
	//删除标签
	public function del_dev_tag($package){
        $res = $this->table('sj_tag_package')->where('package = "'.$package.'" ')->delete();
        return $res;
    }
}
?>
