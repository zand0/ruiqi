<?php 
/**
 * 配件
 */

class ProductsController extends \Com\Controller\Common{
    
    public function indexAction() {
        
        $param['page'] = $this->getRequest()->getQuery('page');
        
        $fitting = new FittingModel();
        $data = $fitting->productList($param);
        $this->_view->assign($data);
    }
    
    public function addAction() {
        if (IS_POST) {
            $data['products_no'] = $this->getRequest()->getPost('products_no');
            $data['products_name'] = $this->getRequest()->getPost('products_name');
            $data['products_norm'] = $this->getRequest()->getPost('products_norm');
            $data['products_brand'] = $this->getRequest()->getPost('products_brand');
            $data['products_price'] = $this->getRequest()->getPost('products_price');
            $data['products_address'] = $this->getRequest()->getPost('products_address');
            $data['products_comment'] = $this->getRequest()->getPost('products_comment');
            
            $id = $this->getRequest()->getPost('id');
            
            $fitting = new FittingModel();
            $returnData = $fitting->add($data,$id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('添加成功', '/products/index');
        }

        //获取单据编号
        $app = new App();
        $productno = $app->build_order_no();
        $this->_view->assign('productno', 'Pj' . $productno);
        
        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
    }
    
    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $data = LibF::M('products')->where('id = ' . $id)->find();
        $this->_view->assign($data);
        $this->_view->assign('$id');
        $this->_view->display('products/add.phtml');
    }
}