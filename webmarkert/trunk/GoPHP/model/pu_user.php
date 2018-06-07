<?php
/*
    用户model
    index格式: userid
    data_info格式: pu_user表中的字段
 */

class GoPu_userModel extends GoPu_model
{
	public $table = 'pu_user';
    public $userid = 0;
    public $index_name = 'userid';
    public $user_name = '';
    public $cache_timeout = 0;
    public $imei = '';
    public $last_time = 0;
	public $status = 0;
	public $last_ip = 0;
	

    public function __construct($index = '')
    {
        parent::__construct(__CLASS__, $index);
    }

    function data2property()
    {
        if ($this->data_info) {
            $this->userid = $this->data_info['userid'];
            $this->user_name = $this->data_info['user_name'];
            $this->imei = $this->data_info['imei'];
            $this->last_time = $this->data_info['last_time'];
			$this->status = $this -> data_info['status'];
			$this->last_ip = $this -> data_info['last_ip'];
        }
    }

    //通过user_name 取得model
    function get_model_by_user_name($user_name)
    {
        $option = array(
            'where' => array('user_name' => $user_name),
        );
        if ($data_info = $this->findOne($option)) {
            return parent::getInstance(__CLASS__, $data_info['userid'], $data_info);
        } else {
            return False;
        }
    }

    //通过ucenter的api 保存到ucenter中
    function save_data_info_to_ucapi($ucapi_data_info)
    {
        if (!$ucapi_data_info['user_name'] || !$ucapi_data_info['user_password']) {
            return False;
        }
/*         $discuz_api = load_config('uc_api')."/user.php?action=register";
        $postdata = array('username' => mb_convert_encoding($ucapi_data_info['user_name'], 'GBK', 'utf-8'), 'password' => $ucapi_data_info['user_password'], 'email' => $ucapi_data_info['email']); */
		
		$discuz_api = load_config('goapk_bbs_url')."/member.php?mod=register_gomarket";
        $postdata = array(
		'username' => base64_encode(mb_convert_encoding($ucapi_data_info['user_name'], 'GBK', 'utf-8')), 
		'password' => base64_encode($ucapi_data_info['user_password']),
		'email' => base64_encode($ucapi_data_info['email']),
		'clientip' => base64_encode($ucapi_data_info['clientip'])
		);
		if(isset($ucapi_data_info['refer_id'])) $postdata['refer_id'] = base64_encode($ucapi_data_info['refer_id']);
        $userinfo  = require_url($discuz_api, $postdata, 10);
        return explode(',',$userinfo);
    }

    //从ucenter的api中取得数据，并且转化为model
    function get_model_by_ucapi($ucapi_data_info)
    {
        if (!$ucapi_data_info['user_password']) {
        }
        $discuz_api = load_config('uc_api')."/user.php?action=login&v=1&source=1";
        $postdata = array('username' => base64_encode(mb_convert_encoding($ucapi_data_info['user_name'], 'GBK', 'utf-8')), 'password' => base64_encode($ucapi_data_info['user_password']),);
        $result     = require_url($discuz_api, $postdata); 
        $result = json_decode($result,true);
        $code = $result['code'];
        $userid = $result['uid'];
        if ($code > 0 || $code == -200) {
            $data_info = array(
                'userid' => $userid,
                'user_name' => $ucapi_data_info['user_name'],
                'user_password' => $ucapi_data_info['user_password'],
				'last_time' => time(),
                'renameurl' => $result['renameurl'],
                'code' => $result['code'],
				'last_ip' => ip2long($_SERVER['REMOTE_ADDR']),
				'status' => 1
             );
            return parent::getInstance(__CLASS__, $userid, $data_info);
        } else {
            $userid = $result['code'];
            return $userid;
        }
    }
	//bbs ucapi 检测用户是否存在
	function get_username_by_ucapi($username){
		$discuz_api = load_config('uc_api')."/user.php?action=checkname";
		$data_info = array('user_name' =>base64_encode(mb_convert_encoding($username, 'GBK', 'utf-8')));
		$user_info = require_url($discuz_api,$data_info);
		return $user_info;
	}
	//bbs ucapi 获取用户信息
	function get_userinfo_by_ucapi($username){
		$discuz_api = load_config('uc_api')."/user.php?action=checkname";
		$data_info = array('user_name' =>base64_encode(mb_convert_encoding($username, 'GBK', 'utf-8')));
		$user_info = require_url($discuz_api,$data_info);
		return $user_info;
	}	
	//bbs ucapi 修改密码
	function change_pwd_by_ucapi($username,$new_password,$uid,$auth) {
		$discuz_api = load_config('goapk_bbs_url')."/member.php?mod=getpasswd_dev2&do=chg";
		$username = base64_encode(mb_convert_encoding($username, 'GBK', 'utf-8'));
		$str = md5($username.$new_password.'%6a#w@!z&8dok./+)*jk');
		$data_info = array('username' => $username, 'new_password' => $new_password, 'uid'=>$uid, 'auth'=>$auth, 'str' => $str);
		$user_info = require_url($discuz_api,$data_info);
		return $user_info;
	}
	//bbs ucapi 获取通讯验证信息
	function get_auth_by_ucapi($uid) {
		$discuz_api = load_config('goapk_bbs_url')."/member.php?mod=getpasswd_dev2&do=auth";
		$str = md5($uid.'%sdfe%78h$3ed./+)*jk');
		$data_info = array('uid' => $uid, 'str' => $str);
		$user_info = require_url($discuz_api,$data_info);
		return $user_info;
	}
	//bbs ucapi 根据uid获取用户账号
	function get_user_by_ucapi($uid,$auth) {
		$discuz_api = load_config('goapk_bbs_url')."/member.php?mod=getpasswd_dev2&do=user";
		$str = md5($uid.$auth.'*7ild6r72,;9ydh5tsdf(4dow.8idw');
		$data_info = array('uid' => $uid, 'auth' => $auth, 'str' => $str);
		$user_info = require_url($discuz_api,$data_info);
		return $user_info;
	}
	//bbs ucapi 用户相关信息 (修改邮箱)
	/*$params = array(
		'new_mail' => mail,修改的值
	);
	return  -7修改失败  -8 用户受保护 >0 修改成功
	*/
	function change_userinfo_by_ucapi($uid,$username,$auth,$params=array()){
		if(empty($params)) return false;
		$discuz_api = load_config('goapk_bbs_url')."/member.php?mod=modify_userinfo_dev";
		$data_info = array();
		$str = md5($uid.$auth.'$%QIDNS*&');
		$username = base64_encode(mb_convert_encoding($username, 'GBK', 'utf-8'));
		$data_info = array('uid' => $uid,'username' => $username,'auth' => $auth,'str' => $str);
		foreach($params as $field => $info){			
				$data_info[$field] = $info;
		}
		$user_info = require_url($discuz_api,$data_info);
		return $user_info;	
	}	
    //密码加密
    function encrypt_password($user_password)
    {
        return md5($user_password);
    }

    //验证密码 
    function verify_password($user_password)
    {
        return $this->data_info['user_password'] && ($this->encrypt_password($user_password) === $this->data_info['user_password']);
    }

    //如果是新用户的话, 保存的时候user_password需要加密
    function save_field_filter()
    {
        //if ($this->is_new) {
            $this->data_info['user_password'] = $this->encrypt_password($this->data_info['user_password']);
        //}
        return $this->data_info;
    }
}
