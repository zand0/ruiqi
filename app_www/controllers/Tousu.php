<?php

/**
 * 投诉类型设置
 */
class TousuController extends \Com\Controller\Common {

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
        $this->modelD = LibF::D('Tousu');
    }

    public function addAction() {
        if (IS_POST) {
            $id = intval($this->getRequest()->getPost('id'));

            $param = $_POST;
            $param['ctime'] = time();
            if (!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/tousu/index');
        }
        //获取门店
        $this->_view->assign('shop_list', ShopModel::getShopArray());

        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }

    public function editeAction() {
        $rData['status'] = 0;
        if (IS_POST) {
            $id = $this->getRequest()->getPost('tid');
            $data['treatment'] = $this->getRequest()->getPost('treatment');
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['status'] = 1;

            $returnData = $this->modelD->edite('edite', $id, $data);
            if ($returnData['status'] == 200) {
                $rData['status'] = 1;
            }
        }
        echo json_encode($rData);
        exit();
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/tousu/index');
    }

    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            }else{
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['kname'])){
                $where['kname'] = $param['kname'];
                $getParam[] = "kname=" . $param['kname'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'].' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }
        
        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $tousuModel = new FillingModel();
        $returnData = $tousuModel->getDataList($param, 'tousu', $where, 'id desc');
        $this->_view->assign($returnData);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    /**
     * 投诉类型
     */
    public function listAction() {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where['status'] = 0;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $tousuModel = new FillingModel();
        $data = $tousuModel->getDataList($param, 'tousu_type', $where, 'id desc');
        $this->_view->assign($data['ext']);
    }

    public function addtypeAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');

            if (!$id) {
                $app = new App();
                $data['toususn'] = 'tslx' . $app->build_order_no();
            }
            $data['title'] = $this->getRequest()->getPost('title');
            $data['comment'] = $this->getRequest()->getPost('comment');

            $tousuModel = new TousuModel();
            $returnData = $tousuModel->editeType($data, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/tousu/list');
        }
    }

    public function editetypeAction() {
        $id = $this->getRequest()->getQuery('id');

        if ($id) {
            $data = LibF::M('tousu_type')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);

        $this->_view->display('tousu/addtype.phtml');
    }

    public function deltypeAction() {
        $id = $this->getRequest()->getQuery('id');
        if ($id) {

            $data['status'] = 1;
            $status = LibF::M('tousu_type')->where('id=' . $id)->save($data);
            $this->success('ok', '/tousu/list');
        }
    }

}