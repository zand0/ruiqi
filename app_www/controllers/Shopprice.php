<?php

/**
 * 门店价格管理
 */
class ShoppriceController extends \Com\Controller\Common {

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
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where['status'] = 0;
        $where['type'] = array('in',array(1,2));
        if (!empty($param)) {
            if (!empty($param['type'])){
                $where['type'] = $param['type'];
            }
            
            unset($param['submit']);
        }
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $commodityModel = new FillingModel();
        $data = $commodityModel->getDataList($param,'commodity_shop',$where,'id desc');  
        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('porductTypeData', $productTypeData);

        $shopObject = ShopModel::getShopArray($this->shop_id);
        $this->_view->assign('shopObject', $shopObject);
    }

    public function addAction() {
        $paramData = $this->getRequest()->getPost();    
        if (!empty($paramData)) {
            $data['commoditysn'] = $paramData['commoditysn'];
            $data['type'] = $paramData['type'];
            $commditylist = $paramData['commditylist'];
            if (!empty($commditylist)) {
                $cArray = explode('|', $commditylist);
                $data['commoditysn'] = !empty($data['commoditysn']) ? $data['commoditysn'] : $cArray[2];
                $data['name'] = isset($cArray[0]) ? $cArray[0] : '';
                $data['norm_id'] = (isset($cArray[1]) && !empty($cArray[1])) ? $cArray[1] : 0;
                $data['type'] = !empty($data['type']) ? $data['type'] : $cArray[3];
            }
            $data['retail_price'] = $paramData['retail_price'];
            $data['retail_price_business'] = $paramData['retail_price_business'];
            $data['retail_price_industry'] = $paramData['retail_price_industry'];
            $data['direct_price'] = $paramData['direct_price'];
            $data['affiliate_price'] = $paramData['affiliate_price'];
            $data['shop_id'] = $paramData['shop_id'];

            $id = $paramData['id'];
            if ($id) {
                $status = LibF::M('commodity_shop')->where(array('id' => $id))->save($data);
                $this->success('价格更新成功', '/shopprice/index');
            } else if(!empty($data['shop_id'])) {
                $data['time_created'] = time();
                $status = LibF::M('commodity_shop')->add($data);
                $this->success('价格更新成功', '/shopprice/index');
            }else{
                $this->error('价格更新失败');
            }
            exit;
        }
        
        $type = 0;
        $sn = $this->getRequest()->getQuery('sn');
        $shop_id = $this->getRequest()->getQuery('shop_id');
        $norm_id = $this->getRequest()->getQuery('norm_id');
        $type = $this->getRequest()->getQuery('type');
        $this->_view->assign('now_shop_id',$shop_id);
        if (!empty($sn)) {
            $commdityModel = new Model('commodity');
            $filed = "rq_commodity.commoditysn,rq_commodity.name,rq_commodity.norm_id,rq_commodity.type,rq_commodity_shop.id,rq_commodity_shop.retail_price,rq_commodity_shop.retail_price_business";
            $filed .= ",rq_commodity_shop.retail_price_industry,rq_commodity_shop.direct_price,rq_commodity_shop.affiliate_price,rq_commodity_shop.shop_id,rq_commodity_shop.status,rq_commodity_shop.comment";

            $leftJoin = " LEFT JOIN rq_commodity_shop ON rq_commodity.commoditysn = rq_commodity_shop.commoditysn ";
            $where = array('rq_commodity.status' => 0);
            $where['rq_commodity_shop.commoditysn'] = trim($sn);
            if ($shop_id > 0) {
                $where['rq_commodity_shop.shop_id'] = $shop_id;
            }
            if ($norm_id > 0) {
                if ($type == 1) {
                    $where['rq_commodity.norm_id'] = $norm_id;
                } else {
                    $where['rq_commodity.id'] = $norm_id;
                }
            }
            $data = $commdityModel->join($leftJoin)->field($filed)->where($where)->find();
            $this->_view->assign($data);
            $type = $data['type'];
        }
        
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('porductTypeData', $productTypeData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $CommonDataModel = new CommonDataModel();
        $commodityData = $CommonDataModel->getCommodityData();
        $this->_view->assign('commodityData', $commodityData);
        
    }
}
