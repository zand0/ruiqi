<?php

/**
 * 支出项目设置
 */
class PayprojectController extends \Com\Controller\Common {

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

        $payModel = new FillingModel();
        $data = $payModel->getDataList($param,'payproject',$where,'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {
        $payModel = new PayprojectModel();
        if (IS_POST) {
            $app = new App();
            $data['paysn'] = 'zcxm' . $app->build_order_no();

            $data['payname'] = $this->getRequest()->getPost('payname');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $payModel->add($data, $id);

            $this->success('创建成功', '/payproject/index');
        }
    }

    public function editeAction() {

        $payModel = new PayprojectModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('payproject')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('payproject/add.phtml');
    }

    public function delAction() {
        $payModel = new PayprojectModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $payModel->delData($id);
        }
        $this->success('ok', '/payproject/index');
    }

}