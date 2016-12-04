<?php

/**
 * 微信订单管理
 */
class Order2Controller extends \Com\Controller\Common {
    
    /**
     * 
     * 查询列表
     */
    public function listAction(){
        $post = $this->getRequest()->getQuery();
        $kid = isset($post['kid'])?$post['kid']:0;
        if(empty($kid)){
            return $this->show_json(array('state'=>0,'msg'=>'kis is empty','url'=>''));
        }
        if(1 || $this->_req->isXmlHttpRequest()){
            
            try {
                //调用User的方法判断验证登录
                if($this->getOrderList($kid)){
                    return $this->ajaxReturn(1,'ok','');
                    //return $this->success('登录成功','/index.php');
                }else{
                    return $this->ajaxReturn(0,'获取失败','');
                    //return $this->error('登录失败');
                }
            } catch (Exception $e) {
                return $this->ajaxReturn(0,$e->getMessage(),'');
            } 
        }
        exit;
    }
    
    /**
     * 
     * 查询单个订单
     */
    public function oneAction(){
        exit;
    }
    
    private function getOrderList($kid){
        return $rows = LibF::M('order')->where("kid=%d",[$kid])->limit(0, 20)->order('order_id desc')->select();exit;
        
    }
    

}
