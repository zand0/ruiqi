<?php

/**
 * 折旧残液管理
 */
class RaffinateController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;
    protected $year;

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
        $this->year = array(1 => '15年后', 2 => '12年后', 3 => '08年后', 4 => '报废瓶');
    }

    /**
     * 残液价格
     */
    public function indexAction() {
        $data = LibF::M('raffinate_price')->find();
        $this->_view->assign($data);
    }

    public function addAction() {
        $data = LibF::M('raffinate_price')->find();
        
        $price = $this->getRequest()->getPost('price');
        if ($price) {
            $param['price'] = $price;
            if (!empty($data)) {
                $where['id'] = $data['id'];
                $status = LibF::M('raffinate_price')->where($where)->save($param);
            } else {
                $param['time_created'] = time();
                $status = LibF::M('raffinate_price')->add($param);
            }
            if ($status) {
                $this->success('创建成功', '/raffinate/index');
            } else {
                $this->error('创建失败');
            }
        }
        $this->_view->assign($data);
    }

    /**
     * 折旧瓶回收价格表
     */
    public function depreciationAction() {
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        $years = $this->year;
        
        $priceData = array();
        $data = LibF::M('depreciation_price')->order('bottle_type asc')->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $priceData[$value['bottle_type']][$value['year']] = $value['price'];
            }
        }
        $this->_view->assign('years', $years);
        $this->_view->assign('priceData', $priceData);
    }

    public function createAction() {
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        $data = $this->getRequest()->getPost();
        if (!empty($data) && !empty($data['year']) && !empty($data['bottle_type'])) {
            $data['year'] = $where['year'] = $data['year'];
            $data['bottle_type'] = $where['bottle_type'] = $data['bottle_type'];
            $data['price'] = $data['price'];
            $data['bottle_name'] = $bottleTypeData[$data['bottle_type']]['bottle_name'];
            $data['time_created'] = time();

            $returnData = LibF::M('depreciation_price')->where($where)->find();
            if (empty($returnData)) {
                $status = LibF::M('depreciation_price')->add($data);
            } else {
                $status = LibF::M('depreciation_price')->where($where)->save($data);
            }
            if ($status) {
                $this->success('创建成功', '/raffinate/depreciation');
            } else {
                $this->error('创建失败');
            }
        }
        $this->_view->assign('bottleTypeData', $bottleTypeData);
        $this->_view->assign('years', $this->year);
    }
    
    /**
     * 回收折旧瓶
     */
    public function stockAction() {
        
    }
    
    /**
     * 退瓶上门费
     */
    public function doorfreeAction() {
        $data = LibF::M('door_fee')->find();
        $this->_view->assign($data);
    }

    public function adddoorAction() {
        $data = LibF::M('door_fee')->find();
        
        $price = $this->getRequest()->getPost('price');
        if ($price) {
            $param['price'] = $price;
            if (!empty($data)) {
                $where['id'] = $data['id'];
                $status = LibF::M('door_fee')->where($where)->save($param);
            } else {
                $param['time_created'] = time();
                $status = LibF::M('door_fee')->add($param);
            }
            if ($status) {
                $this->success('创建成功', '/raffinate/doorfree');
            } else {
                $this->error('创建失败');
            }
        }
        $this->_view->assign($data);
    }

    /**
     * 残液（余气）回收规范
     */
    public function gasAction() {
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);
        
        $returnData = array();
        $data = LibF::M('depreciation_gas')->order('bottle_type asc')->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $returnData[$value['bottle_type']] = $value;
            }
        }
        $this->_view->assign('returnData',$returnData);
    }

    public function addgasAction() {
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        $data = $this->getRequest()->getPost();
        if (!empty($data) && !empty($data['weight']) && !empty($data['bottle_type'])) {
            $data['weight'] = $data['weight'];
            $data['down_weight'] = $data['down_weight'];
            $data['net_weight'] = $data['net_weight'];
            $data['bottle_type'] = $where['bottle_type'] = $data['bottle_type'];
            $data['bottle_name'] = $bottleTypeData[$data['bottle_type']]['bottle_name'];
            $data['time_created'] = time();

            $returnData = LibF::M('depreciation_gas')->where($where)->find();
            if (empty($returnData)) {
                $status = LibF::M('depreciation_gas')->add($data);
            } else {
                $status = LibF::M('depreciation_gas')->where($where)->save($data);
            }
            if ($status) {
                $this->success('创建成功', '/raffinate/gas');
            } else {
                $this->error('创建失败');
            }
        }
        $this->_view->assign('bottleTypeData', $bottleTypeData);
    }

}
