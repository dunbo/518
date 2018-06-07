<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

class Page extends Think {
    // 起始行数
    public $firstRow	;
	
    public $callback = '';
    // 列表每页显示行数
    public $listRows	;
    // 页数跳转时要带的参数
    public $parameter ;
	// 输入跳转页数
    public $jumpPage ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页栏每页显示的页数
    protected $rollPage   ;
	// 分页显示定制
    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页  %upPage%  %first%  %prePage%  %linkPage% %nextPage% %end% %downPage%  %jumpPage% ');

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$parameter='',$jumpPage=1) {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
		$this->jumpPage  = $jumpPage;
        $this->rollPage  = C('PAGE_ROLLPAGE');
		if(!empty($listRows)){
           $this->listRows = ($listRows>20)?$listRows:20;
		   $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
		}
        $lr_setting=md5(__ACTION__).$_SESSION['admin']['admin_id'];
        if(!empty($_GET['lr'])){
		   $this->listRows = (int)$_GET['lr'];
           $_SESSION['admin'][$lr_setting]=$this->listRows;
		   $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
		}else if($_SESSION['admin'][$lr_setting]){
           $this->listRows = $_SESSION['admin'][$lr_setting];
           $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        }
		$this->coolPages  = ceil($this->totalPages/$this->rollPage);
        if(!empty($_GET['p'])){
		   $this->nowPage =  (int)$_GET['p'];
		}else{
		   $this->nowPage = 1;
		}
		if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
		if($this->listRows*($this->nowPage-1) < 0){
		   $this->firstRow = 0;
		}else{
		   $this->firstRow = $this->listRows*($this->nowPage-1);
		}
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 分页显示输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show() {
        
		if(0 == $this->totalRows) return '';
		
		if(0 > $this->nowPage) $this->nowPage = 1;
		
		$p = C('VAR_PAGE');
		
	    if(!empty($_GET['lr'])){
			$lr = (int)$_GET['lr'];
			if($lr > 1000 ){
			  echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
			  echo '<center><p>您输入的记录数大于1000条<p><br />';
			  echo '<a href="javascript:history.go(-1);">返回</a></center>';
			  exit;
			}
			if($lr< 10 ){
				 echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
				 echo '<center><p>您输入的记录数小于10条<p><br />';
				 echo '<a href="javascript:history.go(-1);">返回</a></center>';
				 exit;
			}
	    }else{
		    $lr = $this->listRows;
	    }
		
	    $this->totalPages = ceil($this->totalRows/$lr);     //总页数

        $nowCoolPage = ceil($this->nowPage/$this->rollPage);  //当前滚动页码
		
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
		
		$parse = parse_url($url);
        
		if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url = $parse['path'].'?'.http_build_query($params);
        }
		
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage= "&nbsp;&nbsp;"."<a href='".$url."&".$p."=$upRow&lr=".$lr."'>".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="&nbsp;&nbsp;"."<a href='".$url."&".$p."=$downRow&lr=".$lr."'>".$this->config['next']."</a>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
           // $prePage = "&nbsp;&nbsp;"."<a href='".$url."&".$p."=$preRow&lr=".$lr."' >上".$this->rollPage."页</a>";
			$prePage = "&nbsp;&nbsp;"."<a href='".$url."&".$p."=$preRow&lr=".$lr."' >...</a>";
            $theFirst = "&nbsp;&nbsp;"."<a href='".$url."&".$p."=1&lr=".$lr."' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "&nbsp;&nbsp;"."<a href='".$url."&".$p."=$nextRow&lr=".$lr."' >...</a>";
            $theEnd = "&nbsp;&nbsp;"."<a href='".$url."&".$p."=$theEndRow&lr=".$lr."' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
				   	$linkPage .= "&nbsp;<a href='".$url."&".$p."=$page&lr=".$lr."'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
		//输入验证函数
		$jumpjs = "<script type='text/javascript'>
					function onlyNum() { 
						if(!(event.keyCode==46)&&!(event.keyCode==8)&&!(event.keyCode==37)&&!(event.keyCode==39)) 
						  if(!((event.keyCode>=48&&event.keyCode<=57)||(event.keyCode>=96&&event.keyCode<=105))) 
							event.returnValue=false;
					    
					}
                </script>"; 	
				$onsubmit = '';
		if (!empty($this->callback)) {
			$onsubmit = " onsubmit='{$this->callback}'";
		}
		$jumpPage  =  $jumpjs."&nbsp;&nbsp;&nbsp;&nbsp;<div align='right' style='padding-top: 2px; border-bottom-width: 11px; padding-bottom: 3px; margin: -20px 15px 0px; border-top-width: 21px;'> <form name='form1' id='pageForm' method='get' {$onsubmit}>";
		$jumpPage .=  "每页显示&nbsp;&nbsp;<input id='listRows' name='lr' size='4' value='".$lr."' onkeydown='onlyNum();'  />";
		$jumpPage .=  "&nbsp;跳转至第&nbsp;<input id='jumpPage' name='p' onkeydown='onlyNum()' type='text' value=".$this->nowPage." size='4'  />&nbsp;页";
		$jumpPage .=  "&nbsp;&nbsp;&nbsp;<input type='submit' value='确定' alt='跳转' title='跳转' /></form></div>";
		$pageStr   =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%','%jumpPage%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd,$jumpPage),$this->config['theme']);
        $pageStr = str_replace('?&', '?', $pageStr);
        return $pageStr;
    }

}
?>