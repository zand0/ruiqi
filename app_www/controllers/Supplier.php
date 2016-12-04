<?php

/**
 * 供应商管理
 */
class SupplierController extends \Com\Controller\Common {

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

    public function gasAction() {
        $supplierModel = new SupplierModel();
        $data = $supplierModel->dataList(array('type' => 1));
        $this->_view->assign($data);
    }

    public function bottleAction() {
        $supplierModel = new SupplierModel();
        $data = $supplierModel->dataList(array('type' => 2));

        $this->_view->assign($data);
    }

    public function productAction() {
        $supplierModel = new SupplierModel();
        $data = $supplierModel->dataList(array('type' => 3));

        $this->_view->assign($data);
    }

    public function indexAction() {
        
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        $where['status'] = 1;
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (isset($param['goods_type']) && !empty($param['goods_type'])) {
                $where['goods_type'] = $param['goods_type'];
                $getParam[] = "goods_type=" . $param['goods_type'];
            }
            if (isset($param['supplier_no']) && !empty($param['supplier_no'])) {
                $where['supplier_no'] = $param['supplier_no'];
                $getParam[] = "supplier_no=" . $param['supplier_no'];
            }
            if (isset($param['name']) && !empty($param['name'])) {
                $where['name'] = array('like', $name . '%');
                $getParam[] = "name=" . $param['name'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $supplierModel = new FillingModel();
        $data = $supplierModel->getDataList($param, 'supplier', $where, 'ctime desc');
        $this->_view->assign($data);

        $type = array(1 => '燃气供应商', 2 => '钢瓶供应商', 3 => '配件供应商');
        $this->_view->assign('type',$type);
    }
    
    public function addAction() {
        
        $supplierModel = new SupplierModel();
        if (IS_POST) {
            $data['supplier_no'] = $this->getRequest()->getPost('supplier_no');
            $data['name'] = $this->getRequest()->getPost('name');
            $data['type'] = $this->getRequest()->getPost('type');
            $data['goods_type'] = $this->getRequest()->getPost('goods_type');
            $data['username'] = $this->getRequest()->getPost('username');
            $data['tel'] = $this->getRequest()->getPost('tel');
            $data['address'] = $this->getRequest()->getPost('address');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['type'] = $this->getRequest()->getPost('type');
            $data['admin_user'] = $this->user_info['user_id'];
            $data['admin_username'] = $this->user_info['username'];
            $data['status'] = 1;
            $data['ctime'] = time();
            if(!empty($data['type'])){
                switch ($data['type']){
                    case 1:
                        $dataModel = new GasModel();
                        $dataType = $dataModel->getGasArray();
                        $data['goods_name'] = $dataType[$data['goods_type']]['gas_name'];
                        break;
                    case 2:
                        $dataModel = new BottletypeModel();
                        $dataType = $dataModel->getBottleTypeArray();
                        $data['goods_name'] = $dataType[$data['goods_type']]['bottle_name'];
                        break;
                    case 3:
                        $dataModel = new ProductsModel();
                        $dataType = $dataModel->productsArrlist();
                        $data['goods_name'] = $dataType[$data['goods_type']]['products_name'];
                        break;
                }
            }
            $id = $this->getRequest()->getPost('id');
            $status = $supplierModel->add($data, $id);

            $this->success('创建成功', '/supplier/index');
        }
        
        $app = new App();
        $orderSn = $app->build_order_no();
        $this->_view->assign('supplier_no', 'GYS'.$orderSn);
        
        $type = array(1 => '燃气供应商', 2 => '钢瓶供应商', 3 => '配件供应商');
        $this->_view->assign('type',$type);
    }

    public function editAction() {

        $supplierModel = new SupplierModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('supplier')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        
        $type = array(1 => '燃气供应商', 2 => '钢瓶供应商', 3 => '配件供应商');
        $this->_view->assign('type',$type);
        
        $this->_view->assign('id', $id);
        $this->_view->display('supplier/add.phtml');
    }

    public function delAction() {
        $supplierModel = new SupplierModel();
        
        $code = 0;
        $returnCode = array();
        
        $id = $this->getRequest()->getPost('id');
        $type = $this->getRequest()->getPost('type');
        if ($id) {
            $param['status'] = 0;
            $code = LibF::M('supplier')->where(array('id' => $id))->save($param);

            $returnCode['code'] = $code;
            $url = '';
            if ($type == 1) {
                //$this->success('成功', '/supplier/gas');
                $url = '/supplier/gas';
            } else if ($data['type'] == 2) {
                //$this->success('成功', '/supplier/bottle');
                $url = '/supplier/bottle';
            } else {
                //$this->success('成功', '/supplier/product');
                $url = '/supplier/product';
            }
            $returnCode['url'] = $url;
        }
        echo json_encode($returnCode);
        exit;
        /*
        if ($type == 1) {
            $this->success('成功', '/supplier/gas');
        } else if ($data['type'] == 2) {
            $this->success('成功', '/supplier/bottle');
        } else {
            $this->success('成功', '/supplier/product');
        }*/
    }
    
    public function ajaxdataAction() {
        $dataVal = $this->getRequest()->getQuery('dataVal');
        
        $dataType = array();
        if(!empty($dataVal)){
            switch ($dataVal){
                case 1:
                    $dataModel = new GasModel();
                    $dataType = $dataModel->getGasArray();
                    break;
                case 2:
                    $dataModel = new BottletypeModel();
                    $dataType = $dataModel->getBottleTypeArray();
                    break;
                case 3:
                    $dataType = array();
                    $data = LibF::M('commodity')->where(array('type' => 2))->select();
                    if(!empty($data)){
                        foreach($data as $value){
                            $val['id'] = $value['id'];
                            $val['products_no'] = $value['commoditysn'];
                            $val['products_name'] = $value['name'];
                            $val['products_norm'] = $value['norm_id'];
                            $dataType[$val['id']] = $val;
                        }
                    }
                    break;
            }
        }
        echo json_encode($dataType); exit;
    }

}