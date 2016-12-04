<?php

/**
 * 押金收入
 */
class DepositController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    public function init() {
        parent::init();
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
    
    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            }else{
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['username'])){
                $where['username'] = $param['username'];
                $getParam[] = "username=" . $param['username'];
            }
            if (!empty($param['mobile'])){
                $where['mobile'] = $param['mobile'];
                $getParam[] = "mobile=" . $param['mobile'];
            }
            if (isset($param['status'])){
                $where['status'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'].' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }
        
        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $jtWhere['ctime'] = array('egt', strtotime(date('Y-m-d')));
        $jtTotal = LibF::M('deposit_list')->field('count(*) as total')->where($jtWhere)->find();
        $this->_view->assign('jtTotal',$jtTotal);
        
        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'deposit_list', $where, 'id desc');
        $this->_view->assign($data);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        //订单状态
        $orderStatus = array(
            0 => '未派发', 1 => '配送中', 2 => '已完成'
        );
        //$orderStatus = isset($orderData[10]) ? $orderData[10] : '';
        $this->_view->assign('orderStatus', $orderStatus);

        //获取当前订单按照类型统计
        $dataTotal = LibF::M('deposit_list')->field('count(*) as total,status')->group('status')->order('time_created desc')->select();
        $this->_view->assign('dataTotal', $dataTotal);
    }

    public function addAction() {

        $shop_id = $this->shop_id;

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        //获取所有送气工
        $shiperModel = new ShipperModel();
        $data = $shiperModel->getShipperArray('', $shop_id);

        if (IS_POST) {
            $idata['shop_id'] = $this->getRequest()->getPost('shop_id');
            $idata['shipper_id'] = $this->getRequest()->getPost('shipper_id');
            $idata['shipper_name'] = $data[$idata['shipper_id']]['shipper_name'];
            $idata['shipper_mobile'] = $data[$idata['shipper_id']]['mobile_phone'];
            $idata['status'] = 1;

            $id = $this->getRequest()->getPost('id');

            $depositModel = new DepositModel();
            $returnData = $depositModel->editDeposit($idata, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/deposit/index');
        }
        
        $this->_view->assign('data', $data);
        $id = $this->getRequest()->getQuery('id');
        $this->_view->assign('id', $id);
    }
    
    public function infoAction() {
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $where['id'] = $id;
            $data = LibF::M('deposit_list')->where($where)->find();
            $this->_view->assign('data',$data);
            if (!empty($data)) {
                
                //获取当前钢瓶规格
                $bottleModel = new BottleModel();
                $bottleData = $bottleModel->bottleOData();

                $bottleTypeModel = new BottletypeModel();
                $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();
                
                $bottleInfo = array();
                $bottleText = (!empty($data['bottle_text'])) ? json_decode($data['bottle_text'],TRUE) : array();
                $count = count($bottleText);
                if($count >0){
                    foreach($bottleText as $value){
                        $kind_id = isset($bottleData['xinpian'][$value]) ? $bottleData['xinpian'][$value]['type'] : (isset($bottleData['number'][$value]) ? $bottleData['number'][$value]['type'] : 0); //规格
                        if($kind_id > 0){
                            $val['goods_name'] = $bottleTypeObject[$kind_id]['bottle_name'];
                            $val['goods_kind'] = $kind_id;
                            $val['goods_num'] = 1;
                            $val['xinpian'] = $value;
                            $bottleInfo[] = $val;
                        }
                    }
                }

                $this->_view->assign('bottleData',$bottleInfo);
                $this->_view->assign('count',$count);
            }else{
                $this->error('当前数据不存在');
            }
        }
        $shopObject = ShopModel::getShopArray($this->shop_id);
        $this->_view->assign('shopObject', $shopObject);
    }
    
    /**
     * 押金收入列表
     */
    public function incomeAction() { 
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = $w = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['order_sn'])) {
                $where['rq_deposit.order_sn'] = $param['order_sn'];
                $getParam[] = "order_sn=" . $param['order_sn'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['rq_deposit.time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (empty($param['shop_id']) && $this->shop_id) {
                $where['rq_deposit.shop_id'] = $this->shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['rq_deposit.shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }
            if (!empty($param['type'])) {
                $where['rq_deposit.type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['status'])) {
                $where['rq_deposit.status'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id){
                $where['rq_deposit.shop_id'] = $this->shop_id;
            }
        }
        
        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
            $w['shop_id'] = $this->shop_id;
        }
        $w['deposit_type'] = 1;

        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $where['rq_deposit.status'] = 0;
        //$where['rq_deposit.deposit_type'] = 1;
        
        $depositlistModel = new FillingModel();
        //$data = $depositlistModel->getDataList($param,'deposit',$where,'id desc');
        $data = $depositlistModel->getDataTableList($param, 'deposit', 'rq_deposit', 'rq_kehu', 'kid', $where, 'rq_deposit.id desc');

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        $this->_view->assign('param', $param);

        //统计相关数据
        $statusTj = LibF::M('deposit')->field('sum(price) as total,sum(number) as num,type')->where($w)->group('type')->order('total desc')->select();
        $totalMoney = 0;
        if(!empty($statusTj)){
            foreach($statusTj as $value){
                $totalMoney += $value['total'];
            }
        }
        $statusTjn = LibF::M('deposit')->field('sum(price) as total,count(*) as num')->where(array('deposit_type' => 2))->find();
        $this->_view->assign('totalqkMoney',$statusTjn);
        $totalMoney += $statusTjn['total'];
        
        $this->_view->assign('totalMoney', $totalMoney);
        $this->_view->assign('statusTj', $statusTj);
        $this->_view->assign('statusTotal', count($statusTj));

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData',$bottleTypeData);
    }

}