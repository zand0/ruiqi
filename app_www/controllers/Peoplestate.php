<?php

/**
 * 人员状态设置
 */
class PeoplestateController extends \Com\Controller\Common {

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

        $where = array('status' => 0);
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $peoplestateModelModel = new FillingModel();
        $data = $peoplestateModelModel->getDataList($param,'peoplestate',$where,'id desc');

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
    }

    public function addAction() {
        $peoplestateModelModel = new PeoplestateModel();
        if (IS_POST) {
            $app = new App();
            $data['peoplesn'] = 'ryxm' . $app->build_order_no();

            $data['peoplename'] = $this->getRequest()->getPost('peoplename');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $peoplestateModelModel->add($data, $id);

            $this->success('创建成功', '/peoplestate/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('peoplestate')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('peoplestate/add.phtml');
    }

    public function delAction() {
        $peoplestateModelModel = new PeoplestateModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $peoplestateModelModel->delData($id);
        }
        $this->success('ok', '/peoplestate/index');
    }

}