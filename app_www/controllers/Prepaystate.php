<?php

/**
 * 预付款设置
 */
class PrepaystateController extends \Com\Controller\Common {

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

        $prepaystateModel = new FillingModel();
        $data = $prepaystateModel->getDataList($param,'prepaystate',$where,'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {
        $prepaystateModel = new PrepaystateModel();
        if (IS_POST) {
            $app = new App();
            $data['prepaysn'] = 'yfkxm' . $app->build_order_no();

            $data['revenuename'] = $this->getRequest()->getPost('revenuename');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $prepaystateModel->add($data, $id);

            $this->success('创建成功', '/prepaystate/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('prepaystate')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('prepaystate/add.phtml');
    }

    public function delAction() {
        $prepaystateModel = new PrepaystateModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $prepaystateModel->delData($id);
        }
        $this->success('ok', '/prepaystate/index');
    }

}