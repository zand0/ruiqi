<?php

/**
 * 车辆管理
 */
class CarController extends \Com\Controller\Common {

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

       // $param['page'] = $this->getRequest()->getQuery('page');
        
        $carModel = new CarModel();
        $data = $carModel->getList($param);
        $this->_view->assign('data',$data);
        
        $carType = array(0 => '其它', 1 => '小型', 2 => '中型', 3 => '大型');
        $typeStatus = array(1=>'正常',2 => '到期');
        $this->_view->assign('carType', $carType);
        $this->_view->assign('typeStatus',$typeStatus);
    }

    public function addAction() {

        if (IS_POST) {
            $data['license_plate'] = $this->getRequest()->getPost('license_plate');
            $data['car_type'] = $this->getRequest()->getPost('car_type');
            $data['brand_model'] = $this->getRequest()->getPost('brand_model');
            $data['site_name'] = $this->getRequest()->getPost('site_name');
            $data['driver_name'] = $this->getRequest()->getPost('driver_name');
            $data['escorte_name'] = $this->getRequest()->getPost('escorte_name');
            $data['exhaust_volume'] = $this->getRequest()->getPost('exhaust_volume');
            $data['fuel_consumption'] = $this->getRequest()->getPost('fuel_consumption');
            $data['load'] = $this->getRequest()->getPost('load');
            $data['driver_license'] = $this->getRequest()->getPost('driver_license');
            $data['inspection_status'] = $this->getRequest()->getPost('inspection_status');
            $ccrq = $this->getRequest()->getPost('ccrq');
            if(!empty($ccrq))
                $data['ccrq'] = $ccrq;
            
            $gmrq = $this->getRequest()->getPost('gmrq');
            if(!empty($gmrq))
                $data['gmrq'] = $gmrq;
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['status'] = 1;
            $car_id = $this->getRequest()->getPost('car_id');

            $carModel = new CarModel();
            if ($car_id) {
                $status = $carModel->edite($data, $car_id, array('car_id' => $car_id));
            } else {
                $status = $carModel->add($data);
            }
            $this->success('ok', '/car/index');
        }
        $carType = array(0 => '其它', 1 => '小型', 2 => '中型', 3 => '大型');
        $typeStatus = array(1=>'正常',2 => '到期');
        $this->_view->assign('carType', $carType);
        $this->_view->assign('typeStatus',$typeStatus);
    }

    public function editAction() {
        $car_id = intval($this->getRequest()->getQuery('car_id'));

        $data = LibF::M('car')->where('car_id = ' . $car_id)->find();
        $this->_view->assign($data);
        $this->_view->assign('car_id',$car_id);
        
        $carType = array(0 => '其它', 1 => '小型', 2 => '中型', 3 => '大型');
        $typeStatus = array(1=>'正常',2 => '到期');
        $this->_view->assign('carType', $carType);
        $this->_view->assign('typeStatus',$typeStatus);
        $this->_view->display('car/add.phtml');
    }

}
