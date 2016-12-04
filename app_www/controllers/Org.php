<?php

/**
 * 部门管理 6.2
 * @date 2016/03/24
 */
class OrgController extends \Com\Controller\Common {

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
        $orgModel = new OrgModel();
        $data = $orgModel->orglist();

        $this->_view->assign('data',$data);
    }

    public function addAction() {
        $orgModel = new OrgModel();
        if (IS_POST) {
            $data['org_name'] = $this->getRequest()->getPost('org_name');
            $data['org_parent_id'] = $this->getRequest()->getPost('org_parent_id');
            $data['org_content'] = $this->getRequest()->getPost('org_content');
            $data['listorder'] = $this->getRequest()->getPost('listorder');

            $id = $this->getRequest()->getPost('org_id');
            $status = $orgModel->add($data, $id);

            $this->success('创建成功', '/org/index');
        }
        
        $org_id = $this->getRequest()->getQuery('org_id');
        $this->_view->assign('org_parent_id',$org_id);
        
        $orgData = $orgModel->getOrgList();
        $this->_view->assign('orgData',$orgData);
        
    }

    public function editAction() {
        $org_parent_id = 0;
        
        $orgModel = new OrgModel();
        $org_id = $this->getRequest()->getQuery('org_id');
        if($org_id){
            $data = LibF::M('organization')->where(array('org_id' => $org_id))->find();
            $org_parent_id = $data['org_parent_id'];
            $this->_view->assign($data);
        }
        $orgData = $orgModel->getOrgList();
        $this->_view->assign('orgData',$orgData);
        
        $this->_view->assign('org_parent_id',$org_parent_id);
        $this->_view->assign('org_id',$org_id);
        $this->_view->display('org/add.phtml');
    }
    
    public function delAction() {
        $orgModel = new OrgModel();
        $org_id = $this->getRequest()->getQuery('org_id');
        if($org_id){
            $data = LibF::M('organization')->where(array('org_id' => $org_id))->delete();
        }
        $this->success('ok','/org/index');
    }

}