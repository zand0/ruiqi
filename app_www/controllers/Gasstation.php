<?php

/**
 * 罐装站管理
 *
 * @author zxg
 */
class GasstationController extends \Com\Controller\Common {

    protected $adminId = 0;
    protected $userinfo;

    public function init() {
        parent::init();
        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        $this->adminId = $adminInfo['user_id'];
        $this->userinfo = $adminInfo;
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $getParam = $sWhere = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['region_1'])) {
                $sWhere['region_1'] = $param['region_1'];
                $getParam[] = "region_1=" . $param['region_1'];
            }
            if (!empty($param['region_2'])) {
                $sWhere['region_2'] = $param['region_2'];
                $getParam[] = "region_2=" . $param['region_2'];
            }
            if (!empty($param['region_3'])) {
                $sWhere['region_3'] = $param['region_3'];
                $getParam[] = "region_3=" . $param['region_3'];
            }
            if (!empty($param['region_4'])) {
                $sWhere['region_4'] = $param['region_4'];
                $getParam[] = "region_4=" . $param['region_4'];
            }
            if (!empty($param['gas_name'])) {
                $sWhere['gas_name'] = array('like', '%' . $param['gas_name'] . '%');
                $getParam[] = "gas_name=" . $param['gas_name'];
            }
            if (!empty($getParam)) {
                $this->_view->assign('getparamlist', implode('&', $getParam));
            }
        }
        $sWhere['status'] = 1;

        $CommonDataModel = new CommonDataModel();
        $areaData = $CommonDataModel->getQuarterData();

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'gas_station', $sWhere, 'gas_id desc');
        $this->_view->assign($data);
    }

    public function addAction() {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $data['gas_name'] = $postData['gas_name'];
            if (!empty($postData['admin_id'])) {
                $adminData = explode('|', $postData['admin_id']);
                $data['admin_id'] = $user_id = $adminData[0];
                $data['admin_name'] = $adminData[1];
                $data['mobile_phone'] = $adminData[2];
            }
            if (!empty($postData['tel_phone']))
                $data['tel_phone'] = $postData['tel_phone'];
            if (!empty($postData['region_1']))
                $data['region_1'] = $postData['region_1'];
            if (!empty($postData['region_2']))
                $data['region_2'] = $postData['region_2'];
            if (!empty($postData['region_3']))
                $data['region_3'] = $postData['region_3'];
            if (!empty($postData['region_4']))
                $data['region_4'] = $postData['region_4'];
            if (!empty($postData['address']))
                $data['address'] = $postData['address'];
            if (!empty($postData['coordinate']))
                $data['coordinate'] = $postData['coordinate'];
            if (!empty($postData['parent_gas_id']))
                $data['parent_gas_id'] = $postData['parent_gas_id'];
            $data['comment'] = $postData['comment'];
            $data['add_admin_id'] = $this->adminId;

            $gas_id = $postData['gas_id'];
            if ($gas_id) {
                $status = LibF::M('gas_station')->where(array('gas_id' => $gas_id))->save($data);
            } else {
                $app = new App();
                $qysn = $app->build_order_no();
                $data['gas_code'] = 'qz' . $qysn;
                $data['add_time'] = time();
                $status = LibF::M('gas_station')->add($data);
            }
            if ($status) {
                $this->success('更新成功', '/gasstation/index');
            } else {
                $this->success('更新失败', '/gasstation/index');
            }
        } else {
            $gas_id = $this->getRequest()->getQuery('id');
            if (!empty($gas_id)) {
                $data = LibF::M('gas_station')->where(array('gas_id' => $gas_id))->find();
                $this->_view->assign($data);
            }
        }

        //获取门店经理列表
        $CommonDataModel = new CommonDataModel();
        $userdata = $CommonDataModel->getAreaUser(6);
        $this->_view->assign('userdata', $userdata);
    }

}
