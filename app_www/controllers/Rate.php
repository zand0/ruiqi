<?php

/**
 * 回流率更新
 */
class RateController extends \Com\Controller\Common {

    public function indexAction() {

        $shop_id = $this->getRequest()->getQuery('shop_id');
        $type = $this->getRequest()->getQuery('type'); //1按季度2按照半年3按照年4按照月
        $beginTime = $this->getRequest()->getQuery('begin_time');
        $endTime = $this->getRequest()->getQuery('end_time');

        $dataferModel = new DatatransferModel();
        $data = $dataferModel->getEgrrateList($shop_id, $type, $beginTime, $endTime);
        
        $shopModel = new ShopModel();
        $shopArray = $shopModel->shopArrayData();

        $this->_view->assign('data', $data);
        $this->_view->assign('shopArray', $shopArray);
    }

}
