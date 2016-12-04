<?php

/**
 * 微信下订单
 */
class OrdermanageController extends Com\Controller\My\Guest {
    
    public function indexAction(){
        
    }
    public function listAction(){
        /*$post = $this->getRequest()->getQuery();
        $kid = isset($post['kid'])?$post['kid']:0;
        if(empty($kid)){
            return $this->ajaxReturn(0,'kid is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //调用User的方法判断验证登录
                if($data = (new OrderwxModel)->getOrderList($post)){
                    return $this->ajaxReturn(1,'ok',$data);
                    //return $this->success('登录成功','/index.php');
                }else{
                    return $this->ajaxReturn(0,'获取失败','');
                    //return $this->error('登录失败');
                }
            } catch (Exception $e) {
                return $this->ajaxReturn(0,$e->getMessage(),'');
            }
        }***/
        $this->_view->display('ordermanage\order_management.phtml');
    }
    public function detailAction(){
        $this->_view->display('ordermanage\order_detail.phtml');
    }
    
    public function commentAction(){
        $this->_view->display('ordermanage\evaluate.phtml');
    }
    public function islogin(){
        //Tools::session('kid',170);
        if(empty(Tools::session('kid')) && Tools::session('kid')!=$_COOKIE['kid']){
            return $this->ajaxReturn(0,'登陆超时。。','');
        }
    }
    public function getlistAction(){
        $this->islogin();
        $post = $this->getRequest()->getQuery();
        $kid = Tools::session()['kid'];
        if(empty($kid)){
            return $this->ajaxReturn(0,'kid is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //调用User的方法判断验证登录
                if($data = (new OrderwxModel)->getOrderList($post)){
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
     *
     * 查询单个订单
     */
    public function oneAction(){
        $post = $this->getRequest()->getQuery();
        $id = isset($post['id'])?$post['id']:0;
        if(empty($id)){
            return $this->ajaxReturn(0,'id is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
        
            try {
                //调用User的方法判断验证登录
                if($data = (new OrderwxModel)->getOrder($id)){
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
    
    
    
    public function getcomment(){
        $post = $this->getRequest()->getQuery();
        $osn = isset($post['ordersn'])?$post['ordersn']:0;
        if(empty($osn)){
            return $this->ajaxReturn(0,'osn is empty','');
        }
        if(1 || $this->_req->isXmlHttpRequest()){
        
            try {
                //调用User的方法判断验证登录
                if($data = (new EvaluationwxModel)->getComment($osn)){
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
    
    public function addcomment(){
        $post = $this->getRequest()->getPost();
        $osn = isset($post['ordersn'])?$post['ordersn']:0;
        if(empty($osn)){
            return $this->ajaxReturn(0,'osn is empty','');
        }
        $post['kid']=$this->_session->get('kid');
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //调用User的方法判断验证登录
                if($data = (new EvaluationwxModel)->setComment($post)){
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
    

    
    
}
