<?php

/**
 * 燃气类型
 */
class GasController extends \Com\Controller\Common {

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $this->modelD = LibF::D('Gas');
    }

    public function addAction() {
        if (IS_POST) {
            $param['gas_name'] = $this->getRequest()->getPost('gas_name');
            $param['comment'] = $this->getRequest()->getPost('comment');
            $param['ctime'] = time();
            $id = intval($this->getRequest()->getPost('id'));
            if (!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $app = new App();
                $param['gassn'] = 'rqlx' . $app->build_order_no();
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/gas/index');
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

        $gasModel = new FillingModel();
        $data = $gasModel->getDataList($param, 'gas', $where, 'ctime desc');
        $this->_view->assign($data);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $data = $this->modelD->edite('', $id);
        $this->_view->assign($data);
        $this->_view->display('gas/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 1));
        $this->success('操作成功', '/gas/index');
    }

}
