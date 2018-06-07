<?php
/*
模板类 guokai add 2011-7-21
*/
class GoTemplate
{
	var $tpl;
	var $display_exit = true;
	var $out	=	array();
    protected static $_instance = array();
	public static function getInstance($tpl_dir, $tpl_c_dir)
    {
        $key = $tpl_dir.$tpl_c_dir;
        if ( !isset(self::$_instance[$key]) ) {
            self::$_instance[$key] = new GoTemplate($tpl_dir, $tpl_c_dir);
        }
        return self::$_instance[$key];
    }
    function __construct($tpl_dir, $tpl_c_dir)
    {
        $this->tpl_dir = $tpl_dir;
        $this->tpl_c_dir = $tpl_c_dir; 
        $this->init_tpl();
    }
    
    function init_tpl()
  	{
		if (!$this->tpl) {
            require_once(dirname(__FILE__).'/smarty/Smarty.class.php');
			$this->tpl	    			= new Smarty;	
			$this->tpl->template_dir 	= $this->tpl_dir;
			$this->tpl->compile_dir 	= $this->tpl_c_dir;	
			$this->tpl->left_delimiter	=	'<!--{';
			$this->tpl->right_delimiter =  '}-->';
	        $this->tpl->registerPlugin("block", "url2static_url", "smarty_url2static_url","imgurl_trans");
	        $this->tpl->registerPlugin("function", "random_img_host", "smarty_random_img_host");
        }
	}

	function display($tpl)
	{
		$this->tpl->assign('out', $this->out);
		if (is_static_file()) {
            $str = $this->fetch($tpl);
            if ($file = url2static_file($_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'])) {
                file_put_contents($file, $str);
            }
            echo $str;
        } else {
            $this->tpl->display($tpl);
        }
		if($this -> display_exit){
			exit;
		}
	}
	
	function fetch($tpl)
 	{
        $this->tpl->assign('out', $this->out);
        return $this->tpl->fetch($tpl);
 	}
 	
 	function assign($key, $value)
 	{
 		$this->tpl->assign($key, $value);
 	}
}

function smarty_url2static_url($params, $content, $smarty, &$repeat) 
{
    if ( !($url = trim($content)) || !function_exists('url2static_url')) { return $url; }
    return url2static_url($url);  
}

function smarty_random_img_host()
{
	return getImageHost();	
}