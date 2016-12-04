<?php

/**
 * 额度管理
 */
class QuotaController extends \Com\Controller\Common {

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
        $quotaModel = new QuotaModel();
        $data = $quotaModel->quotaList();

        $this->_view->assign('data', $data);
    }

    public function addAction() {
        $quotaModel = new QuotaModel();
        if (IS_POST) {
            $data['type'] = $this->getRequest()->getPost('type');
            $data['price'] = $this->getRequest()->getPost('price');
            $data['comment'] = $this->getRequest()->getPost('comment');

            $id = $this->getRequest()->getPost('id');
            $status = $quotaModel->edit($data, $id);

            $this->success('创建成功', '/quota/index');
        }
        $typeObject = array(1 => '普通欠款用户', 2 => '门店经理', 3 => '区域经理');
        $this->_view->assign('typeobject', $typeObject);
    }

    public function editeAction() {
        $org_parent_id = 0;

        $quotaModel = new QuotaModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('quota')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $typeObject = array(1 => '普通欠款用户', 2 => '门店经理', 3 => '区域经理');
        $this->_view->assign('typeobject', $typeObject);
        
        $this->_view->display('quota/add.phtml');
    }

    public function delAction() {
        $quotaModel = new QuotaModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('quota')->where(array('id' => $id))->delete();
        }
        $this->success('ok', '/quota/index');
    }

}