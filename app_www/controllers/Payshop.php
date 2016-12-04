<?php

/**
 * 送气工结算
 */
class PayshopController extends \Com\Controller\Common {

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
    //送气工结算余额
    public function indexAction() 
    {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (isset($param['username']) && !empty($param['username'])) {
                $where['shipper_name'] = $param['username'];
                $getParam[] = "username=" . $param['username'];
            }
            if (isset($param['mobile']) && !empty($param['mobile'])) {
                $where['mobile_phone'] = $param['mobile'];
                $getParam[] = "mobile=" . $param['mobile'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $gaslogModel = new FillingModel();
        $data = $gaslogModel->getDataList($param, 'shipper', $where, 'money desc');
        $this->_view->assign($data);
    }
    //送气工结算记录
    public function paylogAction() 
    {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        $page = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$page);
        $this->_view->assign('param', $param);
        if (IS_POST) {
            $time = isset($param['time'])?$param['time']:'';//送气工结款记录时间
            $this->_view->assign('time',$time);
            $status = isset($param['status'])?$param['status']:'';//送气工结款状态，是否审批
            $this->_view->assign('rstatus',$status);
        }
        $shop_id=$this->shop_id;
        $fillModel = new FillingModel();
        $data = $fillModel->payLog($shop_id,$time,$status,$page);
        $this->_view->assign($data);//上缴记录展示
    }
    //送气工结算账单详情
    public function detailAction() 
    {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        $page = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$page);
        $this->_view->assign('param', $param);
        $id = intval($this->getRequest()->getQuery('shipper_id'));//送气工id
        if ($id != 0) {
            Tools::session('detailID',$id);
        }
        if($id == 0){
            $session = Tools::session();
            $id = $session['detailID'];
        }
        

        $shName = LibF::M('shipper')->field('shipper_name')->where(array('shipper_id'=>$id))->find();//送气工名
        $this->_view->assign('shName',$shName);
        $qianData = LibF::M('order_arrears')->field("sum(money) as qmoney")->where(array('type' => 2, 'paytype' => 4,'shipper_id'=>$id))->find(); //收缴欠款金额
        $this->_view->assign('qianData',$qianData['qmoney']);
        $totalData = LibF::M('order')->field("sum(pay_money) as total")->where(array('shipper_id'=>$id,'order_paytype' => 0, 'ispayment' => 1, 'is_settlement' => 1, 'status' => 4))->find();  //订单收费
        $this->_view->assign('orderData',$totalData['total']);//订单款
        $dtotalData = LibF::M('deposit_list')->field("sum(money) as total")->where(array('shipper_id'=>$id))->find(); //押金支出
        if($dtotalData['total']==''){$dtotalData['total']=0;}
        $this->_view->assign('depositData',$dtotalData['total']);
        $overpay = LibF::M('shipper_payment')->field("sum(money) as money")->where(array('shipper_id'=>$id))->find();//已上缴额度
        $this->_view->assign('overpayData', $overpay['money']);


        $fillModel = new FillingModel();
        $payOrder = $fillModel->payOrder($id,$page);//账单详情列表
        $this->_view->assign($payOrder);



    }
    
}