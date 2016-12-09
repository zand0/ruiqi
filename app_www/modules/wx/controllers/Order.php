<?php

/**
 * 订气相关功能控制器
 */
class OrderController extends Com\Controller\My\Guest {

    
    /**
     * 渲染订单页面
     */
    public function vorderAction() {
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);
        
        $this->_view->display('order\order_online.phtml'); 
    }
    /**
     * 渲染支付页面
     */
    public function vpayAction(){
        $post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        /*if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);*/
        
        $this->_view->display('order\pay_money.phtml');
    }
    /**
     * 获取微信签名配置包接口
     */
    public function signAction(){
        try {
            $jssdk = new Jssdk("wxccf5868dd605affe", "d82a60977cdeaf8eb5f46a6a85e76779",$_GET['url']);
            $signPackage = $jssdk->GetSignPackage();
            //$callback = $_GET['callback'];
            return $this->ajaxReturn(1,'操作成功',$signPackage);
        } catch (Exception $e) {
            return $this->ajaxReturn(0,$e->getMessage(),$signPackage);
        }
        
    }
    /**
     * 提交订单接口
     */
    /*public function order(){
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
    }*/
    /**
     * 支付接口
     */
    public function payAction(){
        $post = $this->getRequest()->getQuery();
        $type = isset($post['type'])?$post['type']:0;
        $pid = isset($post['promoid'])?$post['promoid']:0;
        if(empty($pid)){
            $this->ajaxReturn(1,'pid is empty','');
        }
        if($type==0){
            //货到付款处理
            LibF::M('promotions_user')->where(['id'=>$pid])->save(['status'=>1]);
            return $this->ajaxReturn(1,'ok','');
        }
    }
    /**
     * 
     */
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

    /**
     * 提交订单接口
     */
    public function createorderAction() {
        /*$post = $this->getRequest()->getQuery();
        $code = isset($post['code'])?$post['code']:0;
        if(!Wxlogin::islogin())
            Wxlogin::wlogin($code);*/
        $kid = Tools::session('kid');
        if(empty($kid)){
            return $this->ajaxReturn(0,'请先登录','');
        }
        if(!LibF::M('kehu')->field('address')->where(['kid'=>$kid])->find()['address']){
            return $this->ajaxReturn(0,'无收货地址，请重新进入此页填写','');
        }
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
            $param['sendtime']=isset($post['sendtime'])&&!empty($post['sendtime'])?strtotime($post['sendtime']):0;
            
            $param['promotion_id'] = isset($post['promotion_id'])?$post['promotion_id']:0;
            $param["promotion_money"] = isset($post['promotion_money'])?$post['promotion_money']:0;
            if(empty($param['sendtime'])){
                return $this->ajaxReturn(0,'请选择配送时间','');
            }
            if($param['sendtime']<time()){
                return $this->ajaxReturn(0,'请选择将来的时间点','');
            }
            if($param['sendtime'] > (time()+3600*24*3) ){
                return $this->ajaxReturn(0,'请选择3天之内的时间点','');
            }
            if(mb_strlen($param['comment'],'utf8')>60){
                return $this->ajaxReturn(0,'备注不能超过60个字','');
            }
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
    /**
     * 获取商品接口
     */
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

    /**
     * 支付页获取商品列表
     */
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
        $res['sendtime_str'] = date('Y-m-d H:00:00',$res['sendtime']);
        return $this->ajaxReturn(1,'ok',$res);
    }
    /**
     * 获取单个商品
     * @param unknown $id
     */
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
