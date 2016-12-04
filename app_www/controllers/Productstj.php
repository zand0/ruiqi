<?php 
/**
 * 配件统计
 */
class ProductstjController extends \Com\Controller\Common{
    public function init() {
        parent::init();
        $this->modelD = LibF::D('Productstj');
    }
    public function indexAction() {
        
        $data = $this->modelD->getList(array('products_type' =>1));
        $data['shop_levels'] = LibF::C("shop.level")->toArray();
        $data['shop_list'] = ShopModel::getShopArray();
        $this->_view->assign($data);
    }

}