<?php

/**
 * 岗位管理 6.4
 * @date 2016/03/24
 */
class QuartersController extends \Com\Controller\Common {

    public $roleType;

    public function init() {
        parent::init();
        $this->modelD = LibF::D('Quarters');

        $this->roleType = array(1 => '门店经理', 2 => '押运员', 3 => '超级管理员', 4 => '区域经理', 5 => '充装工', 6 => '气站站长', 7 => '采购经理', 8 => '业务员');
    }

    public function addAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');
            $param['title'] = $this->getRequest()->getPost('title');
            $param['org_parent_id'] = $this->getRequest()->getPost('org_parent_id');
            $param['quarters_parent_id'] = $this->getRequest()->getPost('quarters_parent_id');
            $param['quarters_id'] = $this->getRequest()->getPost('quarters_id');
            $param['status'] = $this->getRequest()->getPost('status');
            $param['comment'] = $this->getRequest()->getPost('comment');
            if (!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/quarters/index');
        }

        $orgModel = new OrgModel();
        $orglist = $orgModel->orgSelect();
        $this->_view->assign('orglist', $orglist);

        $quartersModel = new QuartersModel();
        $quarterData = $quartersModel->getQuarter();
        $quarterList = $orgModel->quartersSelect($quarterData);
        $this->_view->assign('quarterList', $quarterList);
        
        $typeObject = $this->roleType;
        $this->_view->assign('typeObject',$typeObject);
    }

    public function editAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $data = $this->modelD->edite('', $id);
        $this->_view->assign($data);

        $orgModel = new OrgModel();
        $orglist = $orgModel->orgSelect('', $data['org_parent_id']);
        $this->_view->assign('orglist', $orglist);

        $quartersModel = new QuartersModel();
        $quarterData = $quartersModel->getQuarter();
        $quarterList = $orgModel->quartersSelect($quarterData, $data['quarters_parent_id']);
        $this->_view->assign('quarterList', $quarterList);

        $typeObject = $this->roleType;
        $this->_view->assign('typeObject',$typeObject);
        
        $this->_view->display('quarters/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/quarters/index');
    }

    public function indexAction() {

        $orgModel = new OrgModel();
        $data = $orgModel->quartersList();
        
        $this->_view->assign('list',$data);
    }

}