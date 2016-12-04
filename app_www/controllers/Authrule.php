<?php

/**
 * 规则管理 6.1
 * @date 2016/03/24
 */
class AuthruleController extends \Com\Controller\Common {

    public $list_rule;
    public $list_rule_app;

    public function init() {
        parent::init();

        $this->modelD = LibF::D('AdminRule');
        $data = $this->modelD->getList();
        $this->list_rule = $data;

        $ruleAppModel = new AdminRuleAppModel();
        $appData = $ruleAppModel->getList();
        $this->list_rule_app = $appData;
    }

    public function addAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');
            $param['title'] = $this->getRequest()->getPost('title');
            $param['name'] = $this->getRequest()->getPost('name');
            $param['pid'] = $this->getRequest()->getPost('pid');
            $param['list_sort'] = $this->getRequest()->getPost('list_sort');
            $param['code_num'] = $this->getRequest()->getPost('code_num');
            $param['level'] = $this->getRequest()->getPost('level');
            if (!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/authrule/index');
        }

        $adminRule = new AdminRuleModel();
        $list = $adminRule->getRuleSelect($this->list_rule);
        $this->_view->assign('list', $list);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $data = $this->modelD->edite('', $id);
        $this->_view->assign($data);

        $adminRule = new AdminRuleModel();
        $list = $adminRule->getRuleSelect($this->list_rule, $data['pid']);
        $this->_view->assign('list', $list);
        $this->_view->display('authrule/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/authrule/index');
    }

    public function indexAction() {

        $adminRule = new AdminRuleModel();
        $list = $adminRule->getRuleList($this->list_rule);
        $this->_view->assign('list', $list);
    }
    
    /**
     * 通用app功能权限
     * 
     * 
     */
    public function appAction() {
        $adminRuleApp = new AdminRuleAppModel();
        $list = $adminRuleApp->getRuleList($this->list_rule_app);
        $this->_view->assign('list', $list);
    }

    public function addappAction() {

        $adminRuleApp = new AdminRuleAppModel();
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');
            $param['title'] = $this->getRequest()->getPost('title');
            $param['name'] = $this->getRequest()->getPost('name');
            $param['pid'] = $this->getRequest()->getPost('pid');
            $param['list_sort'] = $this->getRequest()->getPost('list_sort');
            $param['code_num'] = $this->getRequest()->getPost('code_num');
            $param['level'] = $this->getRequest()->getPost('level');
            if (!empty($id)) {
                $adminRuleApp->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $adminRuleApp->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/authrule/app');
        }

        $list = $adminRuleApp->getRuleSelect($this->list_rule_app);
        $this->_view->assign('list', $list);
    }

    public function appeditAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $adminRuleApp = new AdminRuleAppModel();
        $data = $adminRuleApp->edite('', $id);
        $this->_view->assign($data);

        $list = $adminRuleApp->getRuleSelect($this->list_rule_app, $data['pid']);
        $this->_view->assign('list', $list);
        $this->_view->display('authrule/addapp.phtml');
    }

    public function appdelAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $adminRuleApp = new AdminRuleAppModel();
        $adminRuleApp->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/authrule/app');
    }

}