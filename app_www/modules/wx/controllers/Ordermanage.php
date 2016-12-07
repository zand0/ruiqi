<?php

/**
 * 微信下订单
 */
class OrdermanageController extends Com\Controller\My\Guest {
    
    public function indexAction(){
        
    }
    /**
     * 获取订单列表页面
     */
    public function listAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('ordermanage\order_management.phtml');
    }
    /**
     * 渲染订单详情页面
     */
    public function detailAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('ordermanage\order_detail.phtml');
    }
    /**
     * 渲染评论页面
     */
    public function commentAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('ordermanage\evaluate.phtml');
    }
    /**
     * 获取订单列表接口
     */
    public function getlistAction(){
        //$this->islogin();
        $post = $this->getRequest()->getQuery();
        $kid = Tools::session()['kid'];
        if(empty($kid)){
            return $this->ajaxReturn(0,'kid is empty','');
        }
        $post['kid']=$kid;
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
     * 查询单个订单借口
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
    
    
    /**
     * 获取评论接口
     */
    public function getcommentAction(){
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
    /**
     * 添加评论接口
     */
    public function addcommentAction(){
        $post = $this->getRequest()->getPost();
        $osn = isset($post['ordersn'])?$post['ordersn']:0;
        if(empty($osn)){
            return $this->ajaxReturn(0,'osn is empty','');
        }
        //$post['kid']=$this->_session->get('kid');
        if(1 || $this->_req->isXmlHttpRequest()){
            try {
                //调用User的方法判断验证登录
                if($data = (new EvaluationwxModel)->setComment($post)){
                    LibF::M('order')->where(['order_sn'=>$osn])->save(['is_evaluation'=>1]);
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
