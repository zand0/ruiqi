<?php

/**
 * 门店，送气工领瓶
 */
class NeckController extends \Com\Controller\Common {

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

    public function storeAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['xinpian'])) {
                $where['xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }
            if (!empty($param['number'])) {
                $where['number'] = $param['number'];
                $getParam[] = "number=" . $param['number'];
            }
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (isset($param['status']) && $param['status'] != '') {
                $where['is_open'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            
            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }
        //$where['status'] = 1;

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $commodityModel = new FillingModel();
        $data = $commodityModel->getDataList($param, 'store_inventory', $where, 'id desc');
        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        //$typeObject = array(0 => '新瓶', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $typeObject = array(0 => '空瓶', 1 => '重瓶', 2 => '待修瓶');
        $this->_view->assign('typeObject', $typeObject);

        $statusObject = array(1 => '在门店', 2 => '在送气工', 3 => '在客户');
        $this->_view->assign('statusObject',$statusObject);
    }

    public function shipperAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {

            $getParam = array();
            if (!empty($param['xinpian'])){
                $where['rq_shipper_inventory.xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=".$param['xinpian'];
            }
            if (!empty($param['number'])){
                $where['rq_shipper_inventory.number'] = $param['number'];
                $getParam[] = "number=".$param['number'];
            }
            if (!empty($param['type'])){
                $where['rq_shipper_inventory.type'] = $param['type'];
                $getParam[] = "type=".$param['type'];
            }
            if (!empty($param['shipper_id'])){
                $where['rq_shipper_inventory.shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=".$param['shipper_id'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['rq_shipper_inventory.time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        if($this->shop_id){
            $where['shop_id'] = $this->shop_id;
        }
        
        $commodityModel = new FillingModel();
        //$data = $commodityModel->getDataList($param, 'shipper_inventory', $where, 'id desc');
        $data = $commodityModel->getDataTableList($param, 'shipper_inventory', 'rq_shipper_inventory', 'rq_shipper', 'shipper_id', $where, 'rq_shipper_inventory.id desc');
        $this->_view->assign($data);
        
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $shipperModel = new ShipperModel();
        $shipperData = $shipperModel->getShipperArray('', $this->shop_id);
        $this->_view->assign('shipperData', $shipperData);

        //$typeObject = array(0 => '新瓶', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $typeObject = array(0 => '空瓶',1 => '重瓶', 2 => '待修瓶');
        $this->_view->assign('typeObject', $typeObject);
    }
    
    public function custumAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {

            $getParam = array();
            if (!empty($param['xinpian'])){
                $where['rq_kehu_inventory.xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=".$param['xinpian'];
            }
            if (!empty($param['number'])){
                $where['rq_kehu_inventory.number'] = $param['number'];
                $getParam[] = "number=".$param['number'];
            }
            if (!empty($param['type'])){
                $where['rq_kehu_inventory.type'] = $param['type'];
                $getParam[] = "type=".$param['type'];
            }
            if (!empty($param['kid'])){
                $where['rq_kehu.kid'] = $param['kid'];
                $getParam[] = "kid=".$param['kid'];
            }
            if ($this->shop_id) {
                $where['rq_kehu.shop_id'] = $this->shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['rq_kehu.shop_id'] = $param['shop_id'];
            }
            
            if (!empty($param['user_name'])) {
                $where['rq_kehu.user_name'] = array('like', $param['user_name'] . "%");
                $getParam[] = "user_name=" . $param['user_name'];
            }
            
            if (!empty($param['shipper_id'])){
                $where['rq_kehu_inventory.shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=".$param['shipper_id'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['rq_kehu_inventory.time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        if ($this->shop_id) {
            $where['rq_kehu.shop_id'] = $this->shop_id;
            $this->_view->assign('is_show_shop', $this->shop_id);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $commodityModel = new FillingModel();
        //$data = $commodityModel->getDataList($param, 'shipper_inventory', $where, 'id desc');
        $data = $commodityModel->getDataTableList($param, 'kehu_inventory', 'rq_kehu_inventory', 'rq_kehu', 'kid', $where, 'rq_kehu_inventory.id desc');
        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $shipperModel = new ShipperModel();
        $shipperData = $shipperModel->getShipperArray('', 0);
        $this->_view->assign('shipperData', $shipperData);
    }

}