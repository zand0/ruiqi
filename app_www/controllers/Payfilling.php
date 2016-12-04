<?php

/**
 * 送气工结算
 */
class PayfillingController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    public function init() {
        parent::init();
        $this->modelD = LibF::D('Shipper');

        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        if (!empty($adminInfo)) {
            $this->shop_id = $adminInfo['shop_id'];
            $this->shop_level = $adminInfo['shop_level'];
            $userData['user_id'] = $adminInfo['user_id'];
            $userData['username'] = $adminInfo['username'];
            $userData['photo'] = $adminInfo['photo'];
            $userData['mobile_phone'] = $adminInfo['mobile_phone'];
            $this->user_info = $userData;
        }
    }
    public function indexAction() 
    {
        $param = $this->getRequest()->getPost();
        $page = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$page);
        $this->_view->assign('param', $param);
        $shop_name = $param['shipper_name'];
        $shop_id=$this->shop_id;
        $fillModel = new FillingModel();
        $data = $fillModel->payfillingIndex($page);
        $this->_view->assign($data);//余额展示

        
    }
    public function paylogAction() 
    {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        $page = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$page);
        $this->_view->assign('param', $param);
        if (IS_POST) {
            $time = isset($param['time'])?$param['time']:'';//门店结算记录时间
            $this->_view->assign('time',$time);
            $status = isset($param['status'])?$param['status']:'';//门店结算审核状态
            $this->_view->assign('rstatus',$status);
        }
        $shop_id=$this->shop_id;
        $fillModel = new FillingModel();
        $data = $fillModel->payfillingLog($time,$status,$page);
        // print_r($data);
        $this->_view->assign($data);//上缴记录展示
    }
    public function detailAction() 
    {
        $id = intval($this->getRequest()->getQuery('shop_id'));
        if ($id != 0) {
            Tools::session('detailID',$id);
        }
        if($id == 0){
            $session = Tools::session();
            $id = $session['detailID'];
        }
        $shName = LibF::M('shop')->field('shop_name')->where(array('shop_id'=>$id))->find();
        $this->_view->assign('shName',$shName);
        $totalData = LibF::M('shipper_payment')->field("sum(money) as total")->where(array('shop_id'=>$id,'status' => 1))->find();  //门店收到上交款额度
        $this->_view->assign('totalData',$totalData['total']);
        $overpay = LibF::M('shop_paylist')->field("sum(money) as money")->where(array('shop_id'=>$id,'status'=>1))->find();//门店已上缴气站
        $this->_view->assign('overpayData', $overpay['money']);


        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        $page = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$page);
        $this->_view->assign('param', $param);

        $fillModel = new FillingModel();
        $data = $fillModel->payfillingOrder($id,$page);
        $this->_view->assign($data);//门店余额详情
    }
}