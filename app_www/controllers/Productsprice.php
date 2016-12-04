<?php

/**
 * 配件门店价格表
 */
class ProductspriceController extends \Com\Controller\Common {

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
        if ($this->shop_id > 0) {
            $param['shop_id'] = $this->shop_id;
        }

        $product_price = new ProductspriceModel();
        $data = $product_price->productList($param);

        $this->_view->assign($data);
        
        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }

    public function addAction() {

        //获取单据编号
        $products = new ProductsModel();
        $productObject = $products->getProductsArray();
        $this->_view->assign('productobject', $productObject);
        
        if (IS_POST) {
            $product_id = $this->getRequest()->getPost('product_id');
            $data['products_no'] = $productObject[$product_id]['products_no'];
            $data['products_name'] = $productObject[$product_id]['products_name'];
            $data['shop_id'] = $this->getRequest()->getPost('shop_id');
            $data['products_price'] = $this->getRequest()->getPost('products_price');
            $data['products_comment'] = $this->getRequest()->getPost('products_comment');

            $id = $this->getRequest()->getPost('id');
            
            $product_price = new ProductspriceModel();
            $returnData = $product_price->add($data,$id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/productsprice/index');
        }
        
        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $data = LibF::M('products_price')->where('id = ' . $id)->find();
        $this->_view->assign($data);
        $this->_view->assign('$id');
        
        //获取单据编号
        $products = new ProductsModel();
        $productObject = $products->getProductsArray();
        $this->_view->assign('productobject', $productObject);

        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        $this->_view->display('productsprice/add.phtml');
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }
    
    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $product_price = new ProductspriceModel();
        $returnData = $product_price->del($id);
        if (!$returnData['status'] == 200) {
            $this->error($returnData['msg']);
        }
        $this->success('ok', '/productsprice/index');
    }

}