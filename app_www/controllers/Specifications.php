<?php

/**
 * 配件种类设置
 */
class SpecificationsController extends \Com\Controller\Common {

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
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            unset($param['submit']);
            $this->_view->assign('getparamlist', implode('&', $getParam));
            $this->_view->assign('param', $param);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'products_specifications', $where, 'id desc');
        $this->_view->assign($data);
    }

    public function addAction() {
        $status = 0;

        $specification = new SpecificationsModel();
        if (IS_POST) {
            $data['name'] = $this->getRequest()->getPost('name');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['time_created'] = time();

            $id = $this->getRequest()->getPost('id');
            if ($id) {
                $app = new App();
                $data['specificationsn'] = 'pjxm' . $app->build_order_no();

                $status = LibF::M('products_specifications')->add($data);
            } else {
                $where['id'] = $id;
                $status = LibF::M('products_specifications')->where($where)->save($data);
            }
        }
        echo $status;
        exit;
    }

    public function ajaxediteAction() {

        $data = array();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('products_specifications')->where(array('id' => $id))->find();
        }
        echo json_encode($data);
        exit;
    }

    public function ajaxdelAction() {
        $status = 0;
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = LibF::M('products_specifications')->where(array('id' => $id))->delete();
        }
        echo $status;
        exit;
    }

}