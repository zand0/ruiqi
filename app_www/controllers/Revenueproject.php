<?php

/**
 * 收入项目设置
 */
class RevenueprojectController extends \Com\Controller\Common {

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

        $revenueprojectModel = new FillingModel();
        $data = $revenueprojectModel->getDataList($param,'revenueproject',$where,'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {
        $revenueprojectModel = new RevenueprojectModel();
        if (IS_POST) {
            $app = new App();
            $data['revenuesn'] = 'srxm' . $app->build_order_no();

            $data['revenuename'] = $this->getRequest()->getPost('revenuename');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $revenueprojectModel->add($data, $id);

            $this->success('创建成功', '/revenueproject/index');
        }
    }

    public function editeAction() {

        $revenueprojectModel = new RevenueprojectModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('revenueproject')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('revenueproject/add.phtml');
    }

    public function delAction() {
        $revenueprojectModel = new RevenueprojectModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $revenueprojectModel->delData($id);
        }
        $this->success('ok', '/revenueproject/index');
    }

}
