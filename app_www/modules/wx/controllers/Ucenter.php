<?php

/**
 * 微信下订单
 */
class UcenterController extends Com\Controller\My\Guest {
    
    public function vbindAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
//         if(!Wxlogin::islogin())
//             Wxlogin::wlogin($code);
        $this->_view->display('ucenter\account_bind.phtml');
    }
    
    public function promotionAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('ucenter\coupon.phtml');  
    }
    public function myAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);

        $this->_view->display('ucenter\my.phtml');
    }
    /**
     * 
     *绑定接口
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
                if(!(new VerificationCodeModel)->checkCode($phone,$code)){
                    return $this->ajaxReturn(0,'验证码失效','');
                    //return $this->error('验证码失效');
                }
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
                return $this->ajaxReturn(0,$e->getMessage(),'');
            }
        }
        
    }
    /**
     * 获取优惠券
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
     * 添加优惠券
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
     * 获取用户信息
     */
    public function getuserAction(){
        
        $res = LibF::M('kehu')->where(['kid'=>Tools::session('kid')])->find();
        if(empty($res)){
            return $this->ajaxReturn(0,'用户信息获取失败','');
        }else{
            return $this->ajaxReturn(1,'ok',$res);
        }  
    }
}
