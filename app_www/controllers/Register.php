<?php

/**
 * 注册
 */
class RegisterController extends \Com\Controller\Common {

    public function indexAction() {
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        return true;
    } 
}
