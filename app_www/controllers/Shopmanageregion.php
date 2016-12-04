<?php  
/**
 * 店铺管理
 *
 * @author zxg
 */
class shopmanageregionController extends \Com\Controller\Common{
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
        $param['page'] = $this->getRequest()->getQuery('page');
        $param['shop_id'] = $this->getRequest()->getQuery('shop_id');
        $param['region_1'] = $this->getRequest()->getQuery('region_1');
        $param['region_2'] = $this->getRequest()->getQuery('region_2');
        $param['region_3'] = $this->getRequest()->getQuery('region_3');
        $param['region_4'] = $this->getRequest()->getQuery('region_4');
        $param['shop_id'] = $this->getRequest()->getParam('shop_id');
         
        $regionList = $shopD->getShopManageRegionList($param);
        $viewData = $regionList['ext'];            
        $this->_view->assign($viewData);
        $this->_view->assign('shopId',$param['shop_id']); 
        return true;
    }
    
    public function addAction(){
        if(IS_POST){ 
            $data['region_1'] = $this->getRequest()->getPost('region_1');
            $data['region_2'] = $this->getRequest()->getPost('region_2');
            $data['region_3'] = $this->getRequest()->getPost('region_3');
            $data['region_4'] = $this->getRequest()->getPost('region_4');
            $data['shop_id'] = $this->getRequest()->getPost('shop_id'); 
            $data['admin_id'] = $this->adminId;
            $data['admin_shop_level'] = $this->adminShopLevel;
            $shopD = LibF::D('Shop');
            $addRes = $shopD->addManageRegion($data);     
            if($addRes['status'] != 200){
                $this->error($addRes['msg']);
            }
            $this->redirect("/shopmanageregion/index/shop_id/{$data['shop_id']}");
        }
        $shopId = $this->getRequest()->getParam('shop_id');
        $this->_view->assign('shopId',$shopId); 
        $this->_view->assign('adminShopId',$this->adminShopId); 
    } 
    
    public function delAction(){
        $id = $this->getRequest()->getParam('id');
        $shopId = $this->getRequest()->getParam('shop_id');
        $shopD = LibF::D('Shop');
        $shopD->delManageRegion($shopId,$id);
        $this->redirect("/shopmanageregion/index/shop_id/{$shopId}");
    }
}
