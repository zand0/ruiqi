<?php

/**
 * Description of region
 *
 * @author zxg
 */
class RegionController extends \Com\Controller\Common{
    public function init() {
        parent::init();
    }
    public function regionListAction(){
        $id = $this->getRequest()->getPost('id');//getPost getQuery
        $shopId = $this->getRequest()->getPost('shop_id');
        $optCat = $this->getRequest()->getPost('opt_cat');
        
        if($optCat=='list' || $optCat=='unallot_sale'){  // 地区列表
            $regionD = LibF::D('Region');
            $data = $regionD->getSubRegionList($id,'region_id,region_name');
            $this->ajaxReturn(200, 'ok', $data);
        }elseif($optCat=='unallot_manage'){ // 未分配管理区域
            $regionD = LibF::D('Shop');
            $data = $regionD->getUnallotSubManageRegion($id,$shopId);
            $this->ajaxReturn(200, 'ok', $data);
        }elseif($optCat=='manage'){ // 管理区域
            $shopD = LibF::D('Shop');
            $shopId = $_SESSION['userinfo']['shop_id'];
            $data = $shopD->getSubManageRegion($id,$shopId);
            $this->ajaxReturn(200, 'ok', $data);
        }elseif($optCat=='sale'){
            $shopD = LibF::D('Shop');
            $data = $shopD->getSubSaleRegion($id);
            $this->ajaxReturn(200, 'ok', $data);
        }
        return false;
    }
}
