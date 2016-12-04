<?php

/**
 * 商品种类管理
 */
class CommodityController extends \Com\Controller\Common {

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
        $where = array();
        if (!empty($param)) {
            if (!empty($param['commoditysn']))
                $where['commoditysn'] = $param['commoditysn'];
            if (!empty($param['name']))
                $where['name'] = $param['name'];
            if (!empty($param['type']))
                $where['type'] = $param['type'];
            if (!empty($param['norm_id']))
                $where['norm_id'] = $param['norm_id'];
            if (!empty($param['brands']))
                $where['brands'] = $param['brands'];
            if (!empty($param['manufacturer_id']))
                $where['manufacturer_id'] = $param['manufacturer_id'];

            unset($param['submit']);
        }
        
        $where['status'] = 0;
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
        $this->_view->assign('manufacturerData',$ManufacturerData);
        
        //当前商品类型
        $commdity_type = array(1=>'钢瓶',2=>'配件',3 =>'其它', 4=>'体验套餐', 5=>'优惠套餐');
        $this->_view->assign('commdity_type',$commdity_type);
    }

    public function addAction() {
        $commodityModel = new CommodityModel();
        if (IS_POST) {
            $app = new App();
            $data['commoditysn'] = 'sp' . $app->build_order_no();
            $data['name'] = $this->getRequest()->getPost('name');
            $data['num'] = $this->getRequest()->getPost('num');
            $data['price'] = $this->getRequest()->getPost('price');
            $data['money'] = $this->getRequest()->getPost('money');
            $data['deposit'] = $this->getRequest()->getPost('deposit');
            $data['norm_id'] = $this->getRequest()->getPost('norm_id'); //规格
            $data['manufacturer_id'] = $this->getRequest()->getPost('manufacturer_id'); //生产厂家id
            $data['brands'] = $this->getRequest()->getPost('brands'); //商品品牌
            $data['type'] = $this->getRequest()->getPost('type');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['time_created'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $commodityModel->add($data, $id);

            $this->success('创建成功', '/commodity/index');
        }

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData',$bottleTypeData);
        
        //获取当前厂家
        $manufacturerModel = new ManufacturerModel();
        $ManufacturerData = $manufacturerModel->getManufacturerArray();
        
        $this->_view->assign('manufacturerData',$ManufacturerData);
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('commodity')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        
        //获取当前钢瓶规格
        if ($data['type'] == 1 || $data['type'] == 4 || $data['type'] == 5) {
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            $this->_view->assign('bottleTypeData', $bottleTypeData);
        }else{
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();
            $this->_view->assign('porductTypeData', $productTypeData);
        }
        //获取当前厂家
        $manufacturerModel = new ManufacturerModel();
        $ManufacturerData = $manufacturerModel->getManufacturerArray();
        
        $this->_view->assign('manufacturerData',$ManufacturerData);
        
        $this->_view->assign('id', $id);
        $this->_view->display('commodity/add.phtml');
    }

    public function delAction() {
        $commodityModel = new CommodityModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $commodityModel->delData($id);
        }
        $this->success('ok', '/commodity/index');
    }
    
}