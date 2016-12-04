<?php

/**
 * 安全报告设置
 */
class ReportController extends \Com\Controller\Common {

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

        $where['status'] = 1;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        
        $reportModel = new FillingModel();
        $data = $reportModel->getDataList($param,'security_report',$where,'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {
        $reportModel = new ReportModel();
        if (IS_POST) {
            $app = new App();
            $data['reportsn'] = 'aqbg' . $app->build_order_no();
            $data['title'] = $this->getRequest()->getPost('title');
            $data['type'] = $this->getRequest()->getPost('type');
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['listorder'] = $this->getRequest()->getPost('listorder');

            $id = $this->getRequest()->getPost('id');
            $status = $reportModel->add($data, $id);

            $this->success('创建成功', '/report/index');
        }
    }

    public function editAction() {

        $reportModel = new ReportModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('security_report')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('report/add.phtml');
    }

    public function delAction() {
        $reportModel = new ReportModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $reportModel->delData($id);
        }
        $this->success('ok', '/report/index');
    }

}