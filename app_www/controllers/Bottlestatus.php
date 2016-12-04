<?php

/**
 * 钢瓶状态设置
 */
class BottlestatusController extends \Com\Controller\Common {

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

        $where['type'] = 11;
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
        $return['status'] = 0;
        $typemanagerModel = new TypemanagerModel();
        if (IS_POST) {
            $app = new App();
            $data['typemanagersn'] = 'gpzt' . $app->build_order_no();

            $data['type'] = 11;
            $data['typemanagername'] = $this->getRequest()->getPost('typemanagername');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['ctime'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $typemanagerModel->add($data, $id);
            if($status['status'] == 200){
                $return['status'] = 1;
            }

           // $this->success('创建成功', '/bottlestatus/index');
        }
        echo json_encode($return);
        exit;
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('typemanager')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('bottlestatus/add.phtml');
    }

    public function delAction() {
        $typemanagerModel = new TypemanagerModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $typemanagerModel->delData($id);
        }
        $this->success('ok', '/bottlestatus/index');
    }

}