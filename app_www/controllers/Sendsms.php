<?php

/**
 * 关于我们
 */
class AboutController extends \Com\Controller\Common {

    public function indexAction() {
        
    }

    public function appAction() {

    }

	public function applistAction() {
	}
	/**
	 *
	 * @param string $phone
	 * @param string $msg
	 * @param unknown $isreg 确定是否为注册操作
	 * @return number[]|string[]
	 */
	public function sendAction(){
	    $post = $this->_req->getQuery();
	    $phone = isset($post['phone'])?$post['phone']:0;
	    $isreg = isset($post['isreg'])?$post['isreg']:0;
	    $type = isset($post['type'])?$post['type']:1;
	    $token = isset($post['token'])?$post['token']:0;
	    //$token = $this->_req->getPost('ttoken');
	    if(!isset($phone) || !$phone){
	        return $this->show_json(array('state'=>0,'msg'=>'请填写手机号','url'=>''));exit;
	    }
	    if(!is_numeric($phone)){
	        return $this->show_json(array('state'=>0,'msg'=>'请填写正确的手机号','url'=>''));
	    }
	    if(!preg_match("/^1[3|4|5|7|8]\d{9}$/", $phone)){
	        return $this->show_json(array('state'=>0,'msg'=>'手机号不正确','url'=>''));
	    }
	    $msg = $this->_req->getPost('msg');
	    $isver = isset($post['isver'])?$post['isver']:1;
	    //         if(isset($token) && $token && $token!=4493049){
	    //             return $this->show_json(array('state'=>0,'msg'=>'err','url'=>''));exit;
	    //         }
	    $msgs = [
	        1=>'欢迎注册秒小空,您的验证码是',
	        2=>'欢迎登录秒小空,您的验证码是',
	        //2=>'',
	        //0=>'',
	    ];
	    if(isset($type) && $type && !$msg){
	        $msg = $msgs[$type];
	    }
	    if(!isset($msg)){
	        $msg='';
	    }
	    if($phone){
	        $verify=mt_rand(1000,9999);
	        //Session::set('verify',$verify);
	        $sms=new Sms;
	        if(!$msg){
	            $msg = $msgs[1];
	        }
	        if(!$isver){
	            $verify='';
	        }
	        $data=$msg.$verify;
	        if(pre_decode_js2php($token)!='0453.157400'){
	            //验证手机号是否注册
	            $uri = new UserReginfoModel();
	            if(!$uri->R_userByTel($phone) && !$isreg){
	                return $this->show_json(array('state'=>0,'msg'=>'此手机号尚未注册，请先注册','url'=>''));exit;
	            }
	            if($uri->R_userByTel($phone) && $isreg){
	                return $this->show_json(array('state'=>0,'msg'=>'你已注册，请登录！','url'=>''));exit;
	            }
	        }
	        try {
	            $uc = new UserCodeModel();
	            $uc->R_getSmsLimit($phone);
	
	            $res= $sms->SendData($phone,$data,$verify);
	            if($res['returnstatus']=='Success'){
	                if($uc->CU_code(array('phone'=>$phone,'code'=>$verify))){
	                    $this->show_json(array('state'=>1,'msg'=>$res['message'],'data'=>''));
	                    exit;
	                }
	            }else{
	                $this->show_json(array('state'=>0,'msg'=>$res['message'],'data'=>''));
	                exit;
	            }
	        } catch (Exception $e) {
	            return $this->show_json(array('state'=>0,'msg'=>$e->getMessage(),'url'=>''));
	        }
	
	    }
	    return $this->show_json(array('state'=>0, 'msg'=>'请输入手机号！'));
	    exit;
	}
}
