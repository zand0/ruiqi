<?php

/**
 * 生产厂家设置
 */
class ManufacturerController extends \Com\Controller\Common {

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
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $manufacturerModel = new FillingModel();
        $data = $manufacturerModel->getDataList($param,'manufacturer',$where,'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {
        $manufacturerModel = new ManufacturerModel();
        if (IS_POST) {
            $app = new App();
            $data['manufacturersn'] = 'sccj' . $app->build_order_no();

            $data['name'] = $this->getRequest()->getPost('name');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['time_created'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $manufacturerModel->add($data, $id);

            $this->success('创建成功', '/manufacturer/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('manufacturer')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }

        $this->_view->assign('id', $id);
        $this->_view->display('manufacturer/add.phtml');
    }

    public function delAction() {
        $manufacturerModel = new ManufacturerModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $manufacturerModel->delData($id);
        }
        $this->success('ok', '/manufacturer/index');
    }

}