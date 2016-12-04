<?php

/**
 * 后台用户
 */
class AdminuserController extends \Com\Controller\Common {

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

        $this->modelD = LibF::D('AdminUser');
    }

    public function addAction() {
        if (IS_POST) {
            $param['username'] = $this->getRequest()->getPost('username');
            $param['real_name'] = $this->getRequest()->getPost('real_name');
            $param['mobile_phone'] = $this->getRequest()->getPost('mobile_phone');
            $param['shop_id'] = $this->getRequest()->getPost('shop_id');
            $param['shop_level'] = $this->getRequest()->getPost('shop_level');
            $param['roles'] = $this->getRequest()->getPost('roles', '');
            $param['password'] = $this->getRequest()->getPost('password');
            $param['remark'] = $this->getRequest()->getPost('remark');
            $param['status'] = $this->getRequest()->getPost('status');
            $id = intval($this->getRequest()->getPost('id'));

            $isMobile = preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $param['mobile_phone']) ? true : false;
            if ($isMobile) {

                if (!empty($param['password'])) {
                    $param['user_salt'] = Tools::passwdGen();
                    $param['password'] = $this->modelD->encryptPwd($param['password'], $param['user_salt']);
                } else {
                    unset($param['password']);
                }
                $roles = array($param['roles']);
                if (!empty($id)) {
                    $where['mobile_phone'] = $param['mobile_phone'];
                    $where['user_id'] = array('neq', $id);
                    $data = LibF::M('admin_user')->where($where)->find();
                    if (empty($data)) {
                        $this->modelD->edite('edite', $id, $param, $roles);
                        $msg = '更新成功';
                    } else {
                        $msg = '更新失败，手机号重复';
                    }
                } else {
                    $where['mobile_phone'] = $param['mobile_phone'];
                    $data = LibF::M('admin_user')->where($where)->find();
                    if (empty($data)) {
                        $this->modelD->add($param, $roles);
                        $msg = '添加成功';
                    } else {
                        $msg = '更新失败，手机号重复';
                    }
                }
                $this->success($msg, '/adminuser/index');
            } else {
                if ($id) {
                    $url = '/adminuser/edite?id=' . $id;
                } else {
                    $url = '/adminuser/add';
                }
                $this->success('手机号格式错误', $url);
            }
        }

        $data['shop_list'] = ShopModel::getShopArray(); //获取门店
        $data['roleArr'] = $this->modelD->roleList();
        $data['shop_levels'] = LibF::C("shop.level")->toArray();
        $this->_view->assign($data);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $data = $this->modelD->edite('', $id);
        !empty($data['roles']) && $data['roles'] = array_fill_keys(explode(',', $data['roles']), 1);
        $data['roleArr'] = $this->modelD->roleList();
        $data['shop_list'] = ShopModel::getShopArray(); //获取门店
        $data['shop_levels'] = LibF::C("shop.level")->toArray();
        $this->_view->assign($data);
        $this->_view->display('adminuser/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/adminuser/index');
    }

    public function indexAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        
        $param = $getParam = $where = array();
        $mobile = $this->getRequest()->getPost('mobile_phone');
        $mobile = !empty($mobile) ? $mobile : $this->getRequest()->getQuery('mobile_phone');
        if ($mobile) {
            $param['mobile_phone'] = $mobile;
            $where['mobile_phone'] = $mobile;
            $getParam[] = "mobile_phone=" . $param['mobile_phone'];
        }
        $username = $this->getRequest()->getPost('username');
        $username = !empty($username) ? $username : $this->getRequest()->getQuery('username');
        if (!empty($username)) {
            $param['username'] = $username;
            $where['username'] = $username;
            $getParam[] = "username=" . $param['username'];
        }
        $real_name = $this->getRequest()->getPost('real_name');
        $real_name = !empty($real_name) ? $real_name : $this->getRequest()->getQuery('real_name');
        if (!empty($real_name)) {
            $param['real_name'] = $real_name;
            $where['real_name'] = $real_name;
            $getParam[] = "real_name=" . $param['real_name'];
        }
        if (!empty($this->shop_id)) {
            $param['shop_id'] = $this->shop_id;
            $where['shop_id'] = $this->shop_id;
        }
        
        $page = $this->getRequest()->getQuery('page',1);
        $param['page'] = $page;
        $this->_view->assign('page',$page);
        
        $this->_view->assign('param', $param);
        if(!empty($getParam))
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        
        $commodityModel = new FillingModel();
        $data = $commodityModel->getDataList($param, 'admin_user', $where, 'user_id desc');
        if (!empty($data['ext']['list'])) {
            foreach ($data['ext']['list'] as &$value) {
                $quartersData = LibF::M('quarters_user')->where(array('uid' => $value['user_id']))->find();
                $value['quarters_id'] = !empty($quartersData) ? $quartersData['quarters_id'] : 0;
            }
            $quartersModel = new QuartersModel();
            $data['ext']['quarters'] = $quartersModel->getQuartersData();
        }
        $this->_view->assign($data);
        $roleObject = $this->modelD->roleList();
        $this->_view->assign('roleObject', $roleObject);

        if ($tempType == 'new') {
            //得到当前部门数
            $organizationNum = LibF::M('organization')->count();
            $this->_view->assign('organizationNum',$organizationNum);
            
            //得到送气工人数
            $shipperNum = LibF::M('shipper')->where(array('status' => 1))->count();
            $this->_view->assign('shipperNum',$shipperNum);
            
            //分类人数
            $adminUserData = array();
            $userNum = LibF::M('admin_user')->field('status,count(*) as total')->group('status')->select();
            if(!empty($userNum)){
                foreach($userNum as $val){
                    $adminUserData[$val['status']] = $val['total'];
                }
            }
            $this->_view->assign('adminUserData',$adminUserData);
            
            $this->_view->display('adminuser/index_new.phtml');
        }
    }

    public function quartersAction() {
        if (IS_POST) {
            $data['uid'] = $this->getRequest()->getPost('user_id');
            $data['quarters_id'] = $this->getRequest()->getPost('quarters_id');

            $d = LibF::M('quarters_user')->where(array('uid' => $data['uid']))->find();
            if (empty($d)) {
                LibF::M('quarters_user')->add($data);
            } else {
                LibF::M('quarters_user')->where(array('uid' => $data['uid']))->save($data);
            }
            $this->success('ok', '/adminuser/index');
        }

        $user_id = $this->getRequest()->getQuery('user_id');
        $qdata = LibF::M('quarters_user')->where(array('uid' => $user_id))->find();
        $this->_view->assign('qdata', $qdata);

        $this->_view->assign('user_id', $user_id);

        $quartersModel = new QuartersModel();
        $quarterData = $quartersModel->getQuarter();
        $orgModel = new OrgModel();
        $quarterList = $orgModel->quartersSelect($quarterData, $data['quarters_parent_id']);
        $this->_view->assign('quarterList', $quarterList);
    }

}