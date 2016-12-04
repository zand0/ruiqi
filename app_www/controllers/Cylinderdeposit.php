<?php

/**
 * 钢瓶押金设置
 */
class CylinderdepositController extends \Com\Controller\Common {

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
        
        $cylinderModel = new FillingModel();
        $data = $cylinderModel->getDataList($param,'cylinder_deposit',$where,'id desc');
        $this->_view->assign($data);

        //获取钢瓶类型
        $bottleTypeModel = new BottletypeModel();
        $bottleData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleData',$bottleData);
    }

    public function addAction() {
        $return['status'] = 0;
        
        $paramData = $this->getRequest()->getPost();
        if ($paramData) {
            $app = new App();
            $data['depositsn'] = 'byxm' . $app->build_order_no();

            $data['bottle_id'] = $paramData['bottle_id'];
            $data['suggested_price'] = $paramData['suggested_price'];
            $data['other_price'] = $paramData['other_price'];
            $data['price'] = $paramData['price'];
            $data['suggested_price_business'] = $paramData['suggested_price_business'];
            $data['suggested_price_industry'] = $paramData['suggested_price_industry'];

            $id = $paramData['id'];
            if ($id) {
                $status = LibF::M('cylinder_deposit')->where(array('id' => $id))->save($data);
                $this->success('押金更新成功', '/cylinderdeposit/index');
            } else if (!empty($data['bottle_id'])) {
                $deposit = LibF::M('cylinder_deposit')->where(array('bottle_id' => $data['bottle_id']))->find();
                if (empty($deposit)) {
                    $data['time_created'] = time();
                    $status = LibF::M('cylinder_deposit')->add($data);
                } else {
                    $status = LibF::M('cylinder_deposit')->where(array('id' => $deposit['id']))->save($data);
                }
                $this->success('押金更新成功', '/cylinderdeposit/index');
            } else {
                $this->error('押金更新失败');
            }
            exit;
        }
        
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('cylinder_deposit')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }

        $bottleTypeModel = new BottletypeModel();
        $bottleData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleData', $bottleData);
    }

    public function delAction() {
        $cylinderModel = new CylinderdepositModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $cylinderModel->delData($id);
        }
        $this->success('ok', '/cylinderdeposit/index');
    }

}