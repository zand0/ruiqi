<?php 
/**
 * 门店配件出库送气工领取
 */

class ShipmentController extends \Com\Controller\Common {

    public function indexAction() {

        $param['page'] = $this->getRequest()->getQuery('page');
        $param['shipper_id'] = array('neq', 0);

        $products = new ProductsModel();
        $data = $products->productList($param, 2);
        if (!empty($data['ext']['list'])) {
            foreach ($data['ext']['list'] as &$v) {
                $detail = LibF::M('warehousing_info')->where(array('documentsn' => $v['documentsn']))->select();
                foreach ($detail as $_v) {
                    $v['product'] .= $_v['product_name'] . ' -- ' . $_v['product_num'] . '<br>';
                }
            }
        }
        $data['ext']['shop_list'] = ShopModel::getShopArray(); //获取门店
        $data['ext']['shipper_list'] = ShipperModel::getShipperArray(); //送气工
        $this->_view->assign($data['ext']);
    }

    public function addAction() {

        if (IS_POST) {
            $data['documentsn'] = $this->getRequest()->getPost('documentsn');
            $data['time'] = $this->getRequest()->getPost('time');

            $data['shop_id'] = $this->getRequest()->getPost('shop_id');
            $data['shipper_id'] = $this->getRequest()->getPost('shipper_id', 0);
            $data['products_id'] = $this->getRequest()->getPost('products_id');
            $data['products_num'] = $this->getRequest()->getPost('products_num');
            $data['admin_user'] = $_SESSION['user_id'];
            $data['type'] = 2;
            $data['shop_level'] = 1;
            $products = new ProductsModel();
            $returnData = $products->add($data, 0);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/outstock/index');
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
    }

    public function approveAction() {
        $param['page'] = $this->getRequest()->getQuery('page');

        $products = new ProductsModel();
        $data = $products->productList($param, 2);

        $this->_view->assign($data['ext']);
    }

}
