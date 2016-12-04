<?php

/**
 * 商品价格管理
 */
class CommoditypriceController extends \Com\Controller\Common {

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
        $data = $commodityModel->getDataList($param,'commodity',$where,'is_app desc,type asc,id asc');  

        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('porductTypeData', $productTypeData);

        //获取当前厂家
        $manufacturerModel = new ManufacturerModel();
        $ManufacturerData = $manufacturerModel->getManufacturerArray();

        $this->_view->assign('manufacturerData', $ManufacturerData);
    }

    public function addAction() {        
        $paramData = $this->getRequest()->getPost();
        if (!empty($paramData)) {
            $data['retail_price'] = $paramData['retail_price'];
            $data['retail_price_business'] = $paramData['retail_price_business'];
            $data['retail_price_industry'] = $paramData['retail_price_industry'];
            $data['direct_price'] = $paramData['direct_price'];
            $data['affiliate_price'] = $paramData['affiliate_price'];
            $data['is_app'] = $paramData['is_app'];

            $id = $paramData['id'];
            if ($id) {
                $status = LibF::M('commodity')->where(array('id' => $id))->save($data);
                $this->success('价格更新成功', '/commodityprice/index');
            } else {
                $this->error('价格更新失败');
            }
            exit;
        }

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取当前厂家
        $manufacturerModel = new ManufacturerModel();
        $ManufacturerData = $manufacturerModel->getManufacturerArray();

        $this->_view->assign('manufacturerData', $ManufacturerData);
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('commodity')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }

        //获取当前钢瓶规格
        if ($data['type'] == 1) {
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            $this->_view->assign('bottleTypeData', $bottleTypeData);
        } else {
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();
            $this->_view->assign('porductTypeData', $productTypeData);
        }
        //获取当前厂家
        $manufacturerModel = new ManufacturerModel();
        $ManufacturerData = $manufacturerModel->getManufacturerArray();

        $this->_view->assign('manufacturerData', $ManufacturerData);

        $this->_view->assign('id', $id);
        $this->_view->display('commodityprice/add.phtml');
    }

}