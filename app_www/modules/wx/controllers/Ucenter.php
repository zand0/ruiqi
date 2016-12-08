<?php

/**
 * “我的”相关功能控制器
 */
class UcenterController extends Com\Controller\My\Guest {
    /**
     * 渲染绑定页面
     */
    public function vbindAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
//         if(!Wxlogin::islogin())
//             Wxlogin::wlogin($code);
        $this->_view->display('ucenter\account_bind.phtml');
    }
    /**
     * 渲染优惠券页面
     */
    public function promotionAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('ucenter\coupon.phtml');  
    }
    /**
     * 渲染我的页面
     */
    public function myAction(){
        //header("Location: /wx/order/vorder");
        //var_dump(Tools::session('kid'));
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);

        $this->_view->display('ucenter\my.phtml');
    }
    /**
     * 
     *绑定手机号和openid接口
     */
    public function bindAction(){
        $post = $this->getRequest()->getQuery();
        $openid = isset($post['openid'])?$post['openid']:0;
        $phone = isset($post['phone'])?$post['phone']:0;
        $fromurl = isset($post['$fromurl'])?$post['$fromurl']:0;
        $vcode = isset($post['vcode'])?$post['vcode']:0;
        if(!isset($phone) || empty($phone)){
            return $this->ajaxReturn(0,'phone is empty','');
        }
        if(!isset($openid) || empty($openid)){
            return $this->ajaxReturn(0,'openid is empty','');
        }
        if(!isset($vcode) || empty($vcode)){
            return $this->ajaxReturn(0,'vcode is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //检查验证码
                /*if(!(new VerificationCodeModel)->checkCode($phone,$code)){
                    return $this->ajaxReturn(0,'验证码失效','');
                    //return $this->error('验证码失效');
                }*/
                //调用User的方法判断验证登录
                if($data = (new KehuwxModel)->bindOpenid($openid,$phone)){
                    if (!empty($fromurl)){
                        $this->redirect($fromurl);
                    }
                    return $this->ajaxReturn(1,'ok',$data);
                    //return $this->success('登录成功','/index.php');
                }else{
                    return $this->ajaxReturn(0,'获取失败','');
                    //return $this->error('登录失败');
                }
            } catch (Exception $e) {
                //throw $e;
                return $this->ajaxReturn(0,$e->getMessage(),'');
            }
        }  
    }
    /**
     * 获取优惠券接口
     */
    public function getpromotionAction(){
        
        $post = $this->getRequest()->getQuery();
        $kid = Tools::session('kid');
        if(empty($kid)){
            return $this->ajaxReturn(0,'kid is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //调用User的方法判断验证登录
                if($data = (new PromotionswxModel)->get($kid)){
                    return $this->ajaxReturn(1,'ok',$data);
                    //return $this->success('登录成功','/index.php');
                }else{
                    return $this->ajaxReturn(0,'获取失败','');
                    //return $this->error('登录失败');
                }
            } catch (Exception $e) {
                return $this->ajaxReturn(0,$e->getMessage(),'');
            }
        }
    }
    /**
     * 获取可用优惠券数量接口
     */
    public function promocountAction(){
        //$this->islogin();
        $res = LibF::M('promotions_user')->where([
            'kid'=>Tools::session('kid'),
            'status'=>0,
            'time_end'=>[['EQ',0],['GT',time()],'or']
        ])->count();
        return $this->ajaxReturn(1,'ok',['count'=>$res]);
    }
    /**
     * 添加优惠券接口
     */
    public function addpromotionAction(){
        $post = $this->getRequest()->getPost();
        $kid = $this->_session->get('kid');
        if(empty($kid)){
            return $this->ajaxReturn(0,'kid is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //调用User的方法判断验证登录
                if($data = (new PromotionswxModel)->add($post)){
                    return $this->ajaxReturn(1,'ok',$data);
                    //return $this->success('登录成功','/index.php');
                }else{
                    return $this->ajaxReturn(0,'添加失败','');
                    //return $this->error('登录失败');
                }
            } catch (Exception $e) {
                return $this->ajaxReturn(0,$e->getMessage(),'');
            }
        }
    }
    /**
     * 获取用户信息接口
     */
    public function getuserAction(){
        
        $res = LibF::M('kehu')->field('kid,user_name,mobile_phone,address')->where(['kid'=>Tools::session('kid')])->find();
        if(empty($res)){
            return $this->ajaxReturn(0,'用户信息获取失败','');
        }else{
            return $this->ajaxReturn(1,'ok',$res);
        }  
    }
    /**
     * 获取手机验证码
     */
    public function getcodeAction(){
        $post = $this->getRequest()->getQuery();
        $phone = isset($post['phone'])?$post['phone']:0;
        $isreg = isset($post['isreg'])?$post['isreg']:0;
        $type = isset($post['type'])?$post['type']:1;
        $token = isset($post['token'])?$post['token']:0;
        if(empty($phone)){
            return $this->ajaxReturn(0,'手机号不得为空','');
        }
        if(!isset($phone) || !$phone){
            return $this->ajaxReturn(0,'请填写手机号','');exit;
        }
        if(!is_numeric($phone)){
            return $this->ajaxReturn(0,'请填写正确的手机号','');
        }
        if(!preg_match("/^1[3|4|5|7|8]\d{9}$/", $phone)){
            return $this->ajaxReturn(0,'手机号不正确','');
        }
        $msg = $this->getRequest()->getPost('msg');
        $isver = isset($post['isver'])?$post['isver']:1;
//         if(isset($token) && $token && $token!=4493049){
//             return $this->show_json(array('state'=>0,'msg'=>'err','url'=>''));exit;
//         }
        $msgs = [
            1=>'欢迎你的关注,您的验证码是',
            //2=>'欢迎登录秒小空,您的验证码是',
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
            
            if(!$msg){
                $msg = $msgs[1];
            }
            if(!$isver){
                $verify='';
            }
            $data=$msg.$verify;
			
            try {
                $res = [];
                $uc = new VerificationCodeModel();
                //$uc->R_getSmsLimit($phone);
                //验证码接口
                //...
                //临时方案
                return $this->ajaxReturn(1,$verify,'');
                
                if($res['returnstatus']=='Success'){
                    if($uc->addCode(array('phone'=>$phone,'code'=>$verify))){
                        $this->ajaxReturn(1,$res['message'],'');
                        exit;
                    }
                }else{
                    $this->ajaxReturn(0,$res['message'],'');
                    exit;
                }
            } catch (Exception $e) {
                return $this->ajaxReturn(0,$e->getMessage(),'');
            }
            
        }
        return $this->ajaxReturn(0,'请输入手机号！','');
        exit;
    }
    
    /**
     * 更改用户收获地址
     */
    public function upaddrAction(){
        $post = $this->getRequest()->getPost();
        $addr = isset($post['addr'])?$post['addr']:0;
        $name = isset($post['name'])?$post['name']:0;
        $kid = Tools::session('kid');
        if(empty($addr)){
            return $this->ajaxReturn(0,'地址为空','');
        }
        if(empty($name)){
            return $this->ajaxReturn(0,'姓名为空','');
        }
        if(empty($kid)){
            return $this->ajaxReturn(0,'登陆超时','');
        }
        $khm = LibF::M('kehu');
        //$khm->where(['kid'=>$kid])->find()
        if($res=$khm->where(['kid'=>$kid])->find()){
            if(empty($res['address'])){
                $khm->where(['kid'=>$kid])->save(['address'=>$addr,'user_name'=>$name]);
            }
        }
        return $this->ajaxReturn(1,'ok','');
    }
}
