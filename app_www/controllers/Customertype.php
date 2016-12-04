<?php

/**
 * 客户类型设置
 */
class CustomertypeController extends \Com\Controller\Common {

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

        $where['type'] = 6;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $typemanagerModel = new FillingModel();
        $data = $typemanagerModel->getDataList($param, 'typemanager', $where, 'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {
        $typemanagerModel = new TypemanagerModel();
        if (IS_POST) {
            $app = new App();
            $data['typemanagersn'] = 'lxxm' . $app->build_order_no();

            $data['type'] = 6;
            $data['typemanagername'] = $this->getRequest()->getPost('typemanagername');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $typemanagerModel->add($data, $id);

            $this->success('创建成功', '/customertype/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('typemanager')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('customertype/add.phtml');
    }

    public function delAction() {
        $typemanagerModel = new TypemanagerModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $typemanagerModel->delData($id);
        }
        $this->success('ok', '/customertype/index');
    }

}