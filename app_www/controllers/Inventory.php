<?php

/**
 * 出入库
 */
class InventoryController extends \Com\Controller\Common {

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
    
    public function bottleAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['page_size'] = $this->getRequest()->getQuery('page_size', 20);
        $param['shop_id'] = $this->getRequest()->getPost('shop_id');
        $param['status'] = $this->getRequest()->getPost('status');
        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');
        $param['type'] = $this->getRequest()->getPost('type');

        if(empty($shop_id) && !empty($this->shop_id)){
            $param['shop_id'] = $this->shop_id;
        }
        
        $inventoryModel = new InventoryModel();
        $data = $inventoryModel->storelist($param);

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('page_size', $param['page_size']);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject',$bottleTypeObject);
        
        $this->_view->assign('param',$param);
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }

    public function warehouseAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['page_size'] = $this->getRequest()->getQuery('page_size', 20);
        $param['shop_id'] = $this->getRequest()->getPost('shop_id');
        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');
        $param['type'] = $this->getRequest()->getPost('type');

        if(empty($shop_id) && !empty($this->shop_id)){
            $param['shop_id'] = $this->shop_id;
        }
        
        $inventoryModel = new InventoryModel();
        $data = $inventoryModel->warehouselist($param);

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('page_size', $param['page_size']);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $shipperModel = new ShipperModel();
        $shipperObject = $shipperModel->getShipperArray();
        $this->_view->assign('shipperObject', $shipperObject);
        
        $this->_view->assign('param',$param);
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }
    
    /**
     * 库存简报
     */
    public function reportAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        if ($tempType == 'new') {
            $this->_view->display('inventory/report_new.phtml');
        }
        
        //获取总的燃气库存
        //$tankWhere['status'] = 1;
        $tankWhere = array();

        $tank_gasData = LibF::M('tank_gas')->field('tank_name,total,volume')->where($tankWhere)->select();
        $this->_view->assign('tank_gasData', $tank_gasData);
        $gasData = array();
        
        $tank_gasTotal = 0;
        if (!empty($tank_gasData)) {
            foreach ($tank_gasData as $value) {
                $val[0] = $value['tank_name'];
                $tNum = (float) $value['volume'];
                $val[1] = $tNum;
                $tank_gasTotal += $tNum;
                $gasData[] = $val;
            }
        }
        $this->_view->assign('gasData', json_encode($gasData));
        $this->_view->assign('tank_gasTotal', $tank_gasTotal);

        //获取钢瓶配件总量
        $dataTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,type')->group('type')->select();
        $bottleTotal = $productTotal = 0;
        if(!empty($dataTotal)){
            foreach($dataTotal as $value){
                if($value['type'] == 1){
                    $bottleTotal = $value['total'];
                }else{
                    $productTotal = $value['total'];
                }
            }
        }
        $this->_view->assign('bottleTotal',$bottleTotal);
        $this->_view->assign('productTotal',$productTotal);
        
        $dataTypeTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,fs_type_id,fs_name,type')->group('fs_type_id,type')->select();
        $bottleTypeData = $productTypeData = array();
        if (!empty($dataTypeTotal)) {
            foreach ($dataTypeTotal as $value) {
                if ($value['type'] == 1) {
                    $bottleTypeData[] = $value;
                } else {
                    $productTypeData[] = $value;
                }
            }
        }
        $this->_view->assign('bottleTypeData', $bottleTypeData);
        $this->_view->assign('productTypeData', $productTypeData);

        $bottleData = array();
        if(!empty($bottleTypeData)){
            foreach($bottleTypeData as $value){
               $val[0] = $value['fs_name'];
               $val[1] = (int)$value['total'];
               $bottleData[] = $val;
            }
        }
        $this->_view->assign('bottleData',  json_encode($bottleData));
        
        $productData = array();
        if (!empty($productTypeData)) {
            foreach ($productTypeData as $value) {
                $val[0] = $value['fs_name'];
                $val[1] = (int) $value['total'];
                $productData[] = $val;
            }
        }
        $this->_view->assign('productData',  json_encode($productData));
    }

}
