<?php

/**
 * 配件规格
 */
class ProducttypeController extends \Com\Controller\Common {

    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where['status'] = 1;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $producttpyeModel = new FillingModel();
        $data = $producttpyeModel->getDataList($param,'products_type',$where,'id desc');
        $this->_view->assign($data);
    }

    public function addAction() {

        $return['status'] = 0;
        if (IS_POST) {
            $app = new App();
            $data['typesn'] = 'gpgg' . $app->build_order_no();
            $data['name'] = $this->getRequest()->getPost('name');
            $data['comment'] = $this->getRequest()->getPost('comment');

            $id = $this->getRequest()->getPost('id');

            $producttpyeModel = new ProducttypeModel();
            $returnData = $producttpyeModel->add($data, $id);
            if ($returnData['status'] == 200) {
                $this->success('创建成功', '/producttype/index');
            }else{
                $this->error('创建失败');
            }
        }
    }

    public function editAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $data = LibF::M('products_type')->where('id = ' . $id)->find();
        $this->_view->assign($data);

        $this->_view->assign('id', $id);
        $this->_view->display('producttype/add.phtml');
    }

    public function delAction() {
        $id = $this->getRequest()->getQuery('id');
        $status = LibF::M('products_type')->where(array('id' => $id))->save(array('status' => '-1'));
        if ($status) {
            $this->success('ok', '/producttype/index');
        } else {
            $this->error('删除失败');
        }
    }

}