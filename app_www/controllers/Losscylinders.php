<?php

/**
 * 钢瓶流失设置
 */
class LosscylindersController extends \Com\Controller\Common {

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
        if (!empty($param)) {
            if($param['type'])
                $where['type'] = $param['type'];

            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');

            unset($param['submit']);
        }
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $kehuModel = new FillingModel();
        $data = $kehuModel->getDataList($param, 'losscylinders', $where, 'id desc');
        $this->_view->assign($data);
        
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);
    }

    public function addAction() {
        $losscylindersModel = new LosscylindersModel();
        if (IS_POST) {
            $app = new App();
            $data['codingsn'] = 'gpds' . $app->build_order_no();

            $data['number'] = $this->getRequest()->getPost('number');
            $data['xinpian'] = $this->getRequest()->getPost('xinpian');
            $data['type'] = $this->getRequest()->getPost('type',1);
            $data['time'] = $this->getRequest()->getPost('time');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['time_created'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $losscylindersModel->add($data, $id);

            $this->success('创建成功', '/losscylinders/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('losscylinders')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('losscylinders/add.phtml');
    }

    public function delAction() {
        $losscylindersModel = new LosscylindersModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $losscylindersModel->delData($id);
        }
        $this->success('ok', '/losscylinders/index');
    }

}