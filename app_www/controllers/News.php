<?php

/**
 * 消息管理
 */
class NewsController extends \Com\Controller\Common {

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
            $userData['roles'] = $adminInfo['roles'];
            $this->user_info = $userData;
        }
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        
        $where = array();
        if (isset($param['role_id']) && isset($param['user_id'])) {
            $where['role'] = $param['role_id'];
            $where['admin_user_id'] = $param['user_id'];
            $where['_logic'] = 'OR';
        }
        if (isset($param['message_title']) && !empty($param['message_title'])) {
            $where['message_title'] = array('like', $param['message_title'] . "%");
            $getParam[] = "message_title=" . $param['message_title'];
        }

        if (!empty($param['time_start']) && !empty($param['time_end'])) {
            $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');
            $getParam[] = "time_start=" . $param['time_start'];
            $getParam[] = "time_end=" . $param['time_end'];
        }
        
        if (!empty($getParam))
            $this->_view->assign('getparamlist', implode('&', $getParam));
        
        $noticeModel = new FillingModel();
        $data = $noticeModel->getDataList($param, 'notice', $where, 'time_created desc');
        $this->_view->assign($data);

        $this->_view->assign('data', $data);
    }

    public function addAction() {
        $noticeModel = new NoticeModel();
        if (IS_POST) {
            $data['message_title'] = $this->getRequest()->getPost('message_title');
            $data['message_content'] = $this->getRequest()->getPost('message_content');
            $data['message_type'] = $this->getRequest()->getPost('message_type',0);
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];
            $data['receiving_object'] = $this->getRequest()->getPost('object_id');  //接收对象
            $data['department'] = $this->getRequest()->getPost('deparment_id');//接收部门（如果是气站，当前是部门，如果是门店，当前是门店）
            $data['receiving_user'] = $this->getRequest()->getPost('user_id'); //如果有用户则是当前用户，否则当前部门下所有用户
            //$data['send_range'] = $this->getRequest()->getPost('send_range');
            //$data['role'] = $this->getRequest()->getPost('role_id');
            $data['time_created'] = time();

            $id = $this->getRequest()->getPost('message_id');
            $status = $noticeModel->add($data, $id);

            $this->success('创建成功', '/news/index');
        }
        
        /*$orgModel = new OrgModel();
        $orgData = $orgModel->getOrgList();
        $this->_view->assign('orgData',$orgData);*/
        
        $roleModel = new OrgModel();
        $roleData = $roleModel->getRoleList();
        $this->_view->assign('roleData', $roleData);
    }

    public function editAction() {
        
        $noticeModel = new NoticeModel();
        $message_id = $this->getRequest()->getQuery('message_id');

        $messageData = $noticeModel->getNoticeList($message_id);
        $this->_view->assign($messageData);

        /*$orgModel = new OrgModel();
        $orgData = $orgModel->getOrgList();
        $this->_view->assign('orgData',$orgData);*/
        
        $roleModel = new OrgModel();
        $roleData = $roleModel->getRoleList();
        $this->_view->assign('roleData', $roleData);
        
        $this->_view->assign('message_id', $message_id);
        $this->_view->display('news/add.phtml');
    }
    
    public function infoAction() {
        $noticeModel = new NoticeModel();
        $message_id = $this->getRequest()->getQuery('message_id');

        $messageData = $noticeModel->getNoticeList($message_id);
        $this->_view->assign($messageData);

        $orgModel = new OrgModel();
        $orgData = $orgModel->getOrgList();
        $this->_view->assign('orgData',$orgData);
        
        $this->_view->assign('message_id', $message_id);
    }

    public function delAction(){
        $noticeModel = new NoticeModel();
        $message_id = $this->getRequest()->getQuery('message_id');
        
        if($message_id){
            $status = $noticeModel->delNotice($message_id);
        }
        $this->success('删除成功', '/news/index');
    }
    
    public function isshowAction() {
        $roles = $this->user_info['roles'];
        $user_id = $this->user_info['user_id'];

        $time = 0; //提醒多久的消息
        //$where['time_created'] = array('egt',$time);
        
        //获取要提醒的消息
        $where['role'] = $roles;
        $where['send_range'] = array('notlike', '%' . $user_id . '%');
        $data = LibF::M('notice')->where($where)->select();
        
        $returnData['total'] = count($data);
        $returnData['data'] = $data;
        
        echo json_encode($returnData);exit;
    }

    public function ajaxdataAction() {
        $dataVal = $this->getRequest()->getQuery('dataVal');
        $dVal = $this->getRequest()->getQuery('dVal');
        switch ($dataVal) {
            case 1:
                //判断当前部门下岗位
                $orgModel = new OrgModel();
                if (!empty($dVal)) {
                    $data = $orgModel->getOrgQuarters($dVal);
                } else {
                    //气站：获取气站对应的部门
                    $data = $orgModel->orgSelect();
                }

                break;
            case 2:
                //判断当前部门下的人员
                if (!empty($dVal)) {
                    $adminUserModel = new AdminUserModel();
                    $data = $adminUserModel->getShopUserArray($dVal);
                } else {
                    //门店
                    $shopModel = new ShopModel();
                    $data = $shopModel->shopArrayData();
                }

                break;
        }
        echo json_encode($data);    exit;
    }
}