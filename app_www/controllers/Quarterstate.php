<?php

/**
 * 岗位状态设置
 */
class QuarterstateController extends \Com\Controller\Common {

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

        $quarterstateModelModel = new FillingModel();
        $data = $quarterstateModelModel->getDataList($param,'quarterstate',$where,'id desc');

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
    }

    public function addAction() {
        $quarterstateModelModel = new QuarterstateModel();
        if (IS_POST) {
            $app = new App();
            $data['quartersn'] = 'gwxm' . $app->build_order_no();

            $data['quartersname'] = $this->getRequest()->getPost('quartersname');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $quarterstateModelModel->add($data, $id);

            $this->success('创建成功', '/quarterstate/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('quarterstate')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('quarterstate/add.phtml');
    }

    public function delAction() {
        $quarterstateModelModel = new QuarterstateModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $quarterstateModelModel->delData($id);
        }
        $this->success('ok', '/quarterstate/index');
    }

}