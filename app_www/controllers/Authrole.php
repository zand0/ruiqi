<?php

/**
 * 角色管理 6.3
 * @date 2016/03/24
 */
class AuthroleController extends \Com\Controller\Common {

    protected $modelD;
    public $roleType;

    public function init() {
        parent::init();
        $this->modelD = LibF::D('AdminRole');
        
        $this->roleType = array(1 => '门店经理', 2 => '押运员', 3 => '超级管理员', 4 => '区域经理', 5 => '充装工', 6 => '气站站长', 7 => '采购经理', 8 => '业务员');
    }

    public function addAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');
            $param['title'] = $this->getRequest()->getPost('title');
            $param['org_parent_id'] = $this->getRequest()->getPost('org_parent_id');
            $param['status'] = $this->getRequest()->getPost('status');
            $param['quarters_id'] = $this->getRequest()->getPost('quarters_id');

            $rules = $this->getRequest()->getPost('rules');
            $param['rules'] = !empty($rules) ? implode(',', $rules) : '';
            if (!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/authrole/index');
        }
        
        $ruleModle = new FillingModel();
        $dataRule = $ruleModle->getDataArr('auth_rule', '', 'id asc');

        $adminRule = new AdminRuleModel();
        $list = $adminRule->getRuleRole($dataRule, '');
        $this->_view->assign('list_rule', $list);

        $orgModel = new OrgModel();
        $orglist = $orgModel->orgSelect('', 0);
        $this->_view->assign('orglist', $orglist);
        
        //$quartersData = $ruleModle->getDataArr('quarters',array('is_show' => 1));
        $this->_view->assign('quartersData', $this->roleType);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $where['id'] = $id;
        $ruleModel = new FillingModel();
        $data = $ruleModel->getDataInfo('auth_role', $where);
        !empty($data['rules']) && $data['rules'] = explode(',', $data['rules']);
        $this->_view->assign($data);

        $dataRule = $ruleModel->getDataArr('auth_rule', '', 'id asc');
        $adminRule = new AdminRuleModel();
        $list = $adminRule->getRuleRole($dataRule, $data['rules']);
        $this->_view->assign('list_rule', $list);

        $orgModel = new OrgModel();
        $orglist = $orgModel->orgSelect('', $data['org_parent_id']);
        $this->_view->assign('orglist', $orglist);
        
        //$quartersData = $ruleModel->getDataArr('quarters','');
        $this->_view->assign('quartersData', $this->roleType);

        $this->_view->display('authrole/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/authrole/index');
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();

        $where['status'] = 1;
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param, 'auth_role', $where, 'id desc');
        $this->_view->assign($data);
    }

    public function appAction() {
        $data['list'] = $this->modelD->getList();
        $this->_view->assign($data);
    }

    public function appeditAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');

            $rules = $this->getRequest()->getPost('rules');
            $param['app_rules'] = !empty($rules) ? implode(',', $rules) : '';
            if (!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/authrole/app');
        }

        $data = $this->modelD->edite('', $id);
        !empty($data['app_rules']) && $data['app_rules'] = explode(',', $data['app_rules']);

        $this->_view->assign($data);

        $ruleAppModel = new AdminRuleAppModel();
        $appData = $ruleAppModel->getList();

        $adminRule = new AdminRuleModel();
        $list = $adminRule->getRuleRole($appData, $data['app_rules']);
        $this->_view->assign('list_rule', $list);
    }

}
