<?php

/**
 * 微信下订单
 */
class OrderController extends Com\Controller\My\Guest {

    
    
    public function vorderAction() {
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('order\order_online.phtml'); 
    }
    public function vpayAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('order\pay_money.phtml');
    }
    
    public function signAction(){
        try {
            $jssdk = new Jssdk("wx70eaff95525d402a", "1b6e66de0e89e25e567a3c4b38ed5464",$_GET['url']);
            $signPackage = $jssdk->GetSignPackage();
            //$callback = $_GET['callback'];
            return $this->ajaxReturn(1,'操作成功',$signPackage);
        } catch (Exception $e) {
            return $this->ajaxReturn(0,$e->getMessage(),$signPackage);
        }
        
    }
    
    public function order(){
        Wxlogin::islogin();
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
    
    public function pay(){
        
    }
    public function testindexAction() {
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commdify = LibF::M('commodity')->where(array('status' => 0, 'type' => 1))->select();
        if (!empty($commdify)) {
            foreach ($commdify as &$value) {
                if ($value['type'] == 1) {
                    $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                } else {
                    $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                }
            }
        }
        $this->_view->assign('data', $commdify);
    }

    public function createorderAction() {
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $post = $this->getRequest()->getPost();
        $bottle_id = $this->getRequest()->getPost('bottle_id');
        $bottle_num = $this->getRequest()->getPost('bottle_num', 1);
        $comment = $this->getRequest()->getPost('comment', '');

        $returnData = array('code' => 0);
        $mobile = $this->getRequest()->getPost('mobile');
        $username = $this->getRequest()->getPost('username');
        if (!empty($mobile) && !empty($bottle_id)) {
            $bottleArr = array();
            foreach ($bottle_id as $key => $value) {
                if ($bottle_num[$key] > 0)
                    $bottleArr[] = $value . '|' . $bottle_num[$key];
            }
            $bottleList = implode('-', $bottleArr);
            if(empty($bottleList)){
                return $this->ajaxReturn(0,'请选择商品','');
            }
            $param['bottle_list'] = $bottleList;
            $param['mobile'] = $mobile;
            $param['comment'] = $comment;
            $param['num'] = 0;
            $param['sendtime']=isset($post['sendtime'])?strtotime($post['sendtime']):time();
            $param['promotion_id'] = isset($post['promotion_id'])?$post['promotion_id']:0;
            $param["promotion_money"] = isset($post['promotion_money'])?$post['promotion_money']:0;
            if (!empty($username)) {
                $param['username'] = $username;
            }
            $param['time_created'] = time();
            $status = LibF::M('order_snap')->add($param);
            if ($status) {
                $returnData = array('code' => 1, 'msg' => '下单成功,请您耐心等待，稍后送气工会送气上门','data'=>['id'=>$status]);
            } else {
                $returnData['msg'] = '下单失败';
            }
        } else {
            $returnData['msg'] = '未提交信息';
        }
        echo json_encode($returnData);
        exit;
    }

    public function appAction() {
        
    }
    
    public function getgoodsAction(){
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commdify = LibF::M('commodity')->where(array('status' => 0, 'type' => 1))->select();
        if (!empty($commdify)) {
            foreach ($commdify as &$value) {
                if ($value['type'] == 1) {
                    $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                } else {
                    $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                }
            }
            unset($value);
            return $this->ajaxReturn(1,'ok',$commdify);
        }else{
            return $this->ajaxReturn(0,'获取失败','');
        }
        
        //$this->_view->assign('data', $commdify);
    }

    public function getordertmpAction(){
        $post = $this->getRequest()->getQuery();
        $id = isset($post['id'])?$post['id']:0;
        if(empty($id)){
            return $this->ajaxReturn(0,'id is empty','');
        }
        $goods = [];
        $cmdf = LibF::M('commodity');
        $res = LibF::M('order_snap')->where(['id'=>$id])->find();
        foreach (explode('-',$res['bottle_list']) as $v){
            $arr = explode('|',$v);
            $gtype = $this->getgoods($arr[0]);
            $goodst['id']=$arr[0];
            $goodst['norm_id']=$arr[1];
            $goodst['price']=$arr[2];
            $goodst['num']=$arr[3];
            $goodst['name']=$gtype['name'];
            $goodst['type']=$gtype['cname'];
            $goods[]=$goodst;
            
        }
        $res['goods']=$goods;
        $res['sendtime_str'] = date('Y-m-d H:i:s',$res['sendtime']);
        return $this->ajaxReturn(1,'ok',$res);
    }
    
    private function getgoods($id){
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
    
        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
    
        $commdify = LibF::M('commodity')->where(array('id'=>$id,'status' => 0, 'type' => 1))->select();
        if (!empty($commdify)) {
            foreach ($commdify as &$value) {
                if ($value['type'] == 1) {
                    $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                } else {
                    $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                }
            }
            unset($value);
            return $commdify[0];
        }else{
            return null;
        }
    
        //$this->_view->assign('data', $commdify);
    }

}
