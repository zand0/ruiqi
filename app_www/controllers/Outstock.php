<?php 
/**
 * 门店配件入库（气站出库）
 */

class OutstockController extends \Com\Controller\Common{
    
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
        
        $param['page'] = $this->getRequest()->getQuery('page');
        $param['shipper_id'] = 0;
        
        $param['shop_id'] = $this->shop_id;
        
        $products = new ProductsModel();
        $data = $products->productList($param,2);

        $data['ext']['shop_list'] = ShopModel::getShopArray();//获取门店
        $this->_view->assign($data['ext']);
    }
    
    public function addAction() {
        
        if (IS_POST) {
            $data['documentsn'] = $this->getRequest()->getPost('documentsn');
            $data['time'] = $this->getRequest()->getPost('time');

            $data['shop_id'] = $this->getRequest()->getPost('shop_id');
            
            $data['products_id'] = $this->getRequest()->getPost('products_id');
            $data['products_num'] = $this->getRequest()->getPost('products_num');
            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];
            $data['type'] = 2;
            $data['shop_level'] = 1;
            $products = new ProductsModel();
            $returnData = $products->add($data, 0);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('添加成功', '/outstock/index');
        }

        //获取单据编号
        $app = new App();
        $documentsn = $app->build_order_no();
        $this->_view->assign('documentsn', 'Dj' . $documentsn);

        //获取产品
        $productsObject = ProductsModel::getProductsArray();
        $this->_view->assign('productsObject', $productsObject);
        
        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }
    
    public function approveAction() {
        $param['page'] = $this->getRequest()->getQuery('page');
        
        $products = new ProductsModel();
        $data = $products->productList($param,2);
        
        $this->_view->assign($data['ext']);
    }
    
    public function infoAction() {
        $id = $this->getRequest()->getQuery('id');

        $products = new ProductsModel();
        $data = $products->getProductInfo($id);
        
        $this->_view->assign('data',$data);
    }
}