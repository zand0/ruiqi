<?php  
/**
 * 店铺管理
 *
 * @author zxg
 */
class shopsaleregionController extends \Com\Controller\Common{
    protected $adminId = 0;
    protected $adminShopId = 0;
    protected $adminShopLevel = 0;
    public function init() {
        parent::init();
        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        $this->adminShopId = $adminInfo['shop_id'];
        $this->adminShopLevel = $adminInfo['shop_level'];
        $this->adminId = $adminInfo['user_id'];
    }
    public function indexAction(){
        $shopD = LibF::D('Shop');
        $param['shop_id'] = $this->getRequest()->getQuery('shop_id');
        $param['page'] = $this->getRequest()->getQuery('page');
        $param['region_1'] = $this->getRequest()->getQuery('region_1');
        $param['region_2'] = $this->getRequest()->getQuery('region_2');
        $param['region_3'] = $this->getRequest()->getQuery('region_3');
        $param['region_4'] = $this->getRequest()->getQuery('region_4');
        $param['shop_id'] = $this->adminShopId; 
        $regionList = $shopD->getShopSaleRegionList($param);
        $viewData = $regionList['ext'];            
        $this->_view->assign($viewData);
        return true;
    }
    public function addAction(){
        $shopId = $this->adminShopId;
        if(IS_POST){
            $data['shop_id'] = $this->adminShopId;
            $data['region_1'] = $this->getRequest()->getPost('region_1');
            $data['region_2'] = $this->getRequest()->getPost('region_2');
            $data['region_3'] = $this->getRequest()->getPost('region_3');
            $data['region_4'] = $this->getRequest()->getPost('region_4');
            $data['ship_price'] = $this->getRequest()->getPost('ship_price');
            $data['add_admin_id'] = $this->adminId; 
            
            $shopD = LibF::D('Shop');
            $addRes = $shopD->addSaleRegion($data); 
            if($addRes['status'] != 200){
                $this->error($addRes['msg']);
            }
            $this->redirect("/shopsaleregion/index/");
        }  
        $this->_view->assign('adminShopId',$shopId); 
        return true;
    } 
    public function delAction(){
        $id = $this->getRequest()->getParam('id');
        $shopId = $this->adminShopId;
        $shopD = LibF::D('Shop');
        $shopD->delSaleRegion($shopId,$id);
        $this->redirect("/shopsaleregion/index");
    }
}
