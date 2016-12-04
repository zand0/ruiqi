<?php

/**
 * 库存警戒线管理
 */
class BaselineController extends \Com\Controller\Common {

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
        $param['page'] = $this->getRequest()->getQuery('page',1);
        $this->_view->assign('page',$page);
        
        $param['type'] = $this->getRequest()->getPost('type');
        $param['goods_type'] = $this->getRequest()->getPost('goods_type');
        $param['baseline_sno'] = $this->getRequest()->getPost('baseline_sno');      
        
        $baselineModel = new BaselineModel();
        $data = $baselineModel->dataList($param);

        $this->_view->assign($data);

        $type = array(1 => '燃气供应商', 2 => '钢瓶供应商', 3 => '配件供应商');
        $this->_view->assign('type',$type);
        
        $this->_view->assign('param',$param);
    }
    
    public function gasAction() {
        $baselineModel = new BaselineModel();
        $data = $baselineModel->dataList(array('type' => 1));
        $this->_view->assign($data);
    }

    public function bottleAction() {
        $baselineModel = new BaselineModel();
        $data = $baselineModel->dataList(array('type' => 2));
        $this->_view->assign($data);
    }

    public function productAction() {
        $baselineModel = new BaselineModel();
        $data = $baselineModel->dataList(array('type' => 3));

        $this->_view->assign($data);
    }

    public function addAction() {
        
        $baselineModel = new BaselineModel();
        if (IS_POST) {
            
            $type = $this->getRequest()->getPost('type');

            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['num'] = $this->getRequest()->getPost('num');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            if (!empty($type)) {
                $data['type'] = $type;
                $goods_type = $this->getRequest()->getPost('goods_type');
                if(!empty($goods_type))
                    $data['goods_type'] = $goods_type;
                switch ($data['type']) {
                    case 1:
                        $dataModel = new GasModel();
                        $dataType = $dataModel->getGasArray();
                        if ($data['goods_type'])
                            $data['goods_name'] = isset($dataType[$data['goods_type']]) ? $dataType[$data['goods_type']]['gas_name'] : '';
                        break;
                    case 2:
                        $dataModel = new BottletypeModel();
                        $dataType = $dataModel->getBottleTypeArray();
                        if ($data['goods_type'])
                            $data['goods_name'] = isset($dataType[$data['goods_type']]) ? $dataType[$data['goods_type']]['bottle_name'] : '';
                        break;
                    case 3:
                        $dataModel = new ProductsModel();
                        $dataType = $dataModel->getProductsArray();
                        if ($data['goods_type'])
                            $data['goods_name'] = (isset($dataType[$data['goods_type']])) ? $dataType[$data['goods_type']]['products_name'] : '';
                        break;
                }
            }

            $id = $this->getRequest()->getPost('id');
            if (empty($id)) {
                $app = new App();
                $orderSn = $app->build_order_no();
                $data['baseline_sno'] = 'JBS' . $orderSn;
            }
            
            $status = $baselineModel->add($data, $id);

            if ($type == 1) {
                $this->success('成功', '/baseline/gas');
            } else if ($type == 2) {
                $this->success('成功', '/baseline/bottle');
            } else {
                $this->success('成功', '/baseline/product');
            }
        }
        $typeObject = array(1 => '燃气供应商', 2 => '钢瓶供应商', 3 => '配件供应商');
        $this->_view->assign('typeObject',$typeObject);
    }

    public function editAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('baseline')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        
        $typeObject = array(1 => '燃气供应商', 2 => '钢瓶供应商', 3 => '配件供应商');
        $this->_view->assign('typeObject',$typeObject);
        $this->_view->display('baseline/add.phtml');
    }

    public function delAction() {
        $baselineModel = new BaselineModel();
        
        $id = $this->getRequest()->getQuery('id');
        $type = $this->getRequest()->getQuery('type');
        if ($id) {
            $param['status'] = 0;
            $data = LibF::M('baseline')->where(array('id' => $id))->save($param);
        }
        if ($type == 1) {
            $this->success('成功', '/baseline/gas');
        } else if ($type == 2) {
            $this->success('成功', '/baseline/bottle');
        } else {
            $this->success('成功', '/baseline/product');
        }
    }

}