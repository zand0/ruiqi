<?php

/**
 * 区域价格管理
 */
class AreapriceController extends \Com\Controller\Common {

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
        $data = $commodityModel->getDataList($param,'commodity_area',$where,'id desc');  
        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('porductTypeData', $productTypeData);

        $CommonDataModel = new CommonDataModel();
        $areaData = $CommonDataModel->getQuarterData();
        $this->_view->assign('areaData',$areaData);
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
            $data['deposit_price'] = $paramData['deposit_price'];
            $data['deposit_price_business'] = $paramData['deposit_price_business'];
            $data['deposit_price_industry'] = $paramData['deposit_price_industry'];
            $data['area_id'] = $paramData['area_id'];

            $id = $paramData['id'];
            if ($id) {
                $status = LibF::M('commodity_area')->where(array('id' => $id))->save($data);
                $this->success('价格更新成功', '/areaprice/index');
            } else if(!empty($data['area_id'])) {
                $data['time_created'] = time();
                $status = LibF::M('commodity_area')->add($data);
                $this->success('价格更新成功', '/areaprice/index');
            }else{
                $this->error('价格更新失败');
            }
            exit;
        }
        
        $type = 0;
        $sn = $this->getRequest()->getQuery('sn');
        $area_id = $this->getRequest()->getQuery('area_id');
        if (!empty($sn)) {
            $commdityModel = new Model('commodity');
            $filed = "rq_commodity.commoditysn,rq_commodity.name,rq_commodity.norm_id,rq_commodity.type,rq_commodity_area.id,rq_commodity_area.retail_price,rq_commodity_area.retail_price_business";
            $filed .= ",rq_commodity_area.direct_price,rq_commodity_area.affiliate_price,rq_commodity_area.area_id,rq_commodity_area.status,rq_commodity_area.comment";
            $filed .= ",rq_commodity_area.deposit_price,rq_commodity_area.deposit_price_business ";
            
            $leftJoin = " LEFT JOIN rq_commodity_area ON rq_commodity.commoditysn = rq_commodity_area.commoditysn ";
            $where = array('rq_commodity.status' => 0);
            $where['rq_commodity.commoditysn'] = $sn;
            if($area_id > 0)
                $where['rq_commodity_area.area_id'] = $area_id;
            $data = $commdityModel->join($leftJoin)->field($filed)->where($where)->find();
            $this->_view->assign($data);
        }
        
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('porductTypeData', $productTypeData);

        //获取区域
        $CommonDataModel = new CommonDataModel();
        $areaData = $CommonDataModel->getQuarterData();
        $this->_view->assign('areaData', $areaData);

        $commodityData = $CommonDataModel->getCommodityData();
        $this->_view->assign('commodityData', $commodityData);
    }

}
