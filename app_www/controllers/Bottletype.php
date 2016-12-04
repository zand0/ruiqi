<?php 
/**
 * 钢瓶类型表
 */

class BottletypeController extends \Com\Controller\Common{
    
    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where['status'] = 1;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $bottleType = new FillingModel();
        $data = $bottleType->getDataList($param, 'bottle_type', $where, 'id desc');

        $this->_view->assign($data);
    }

    public function addAction() {

        $return['status'] = 0;
        if (IS_POST) {
            $app = new App();
            $data['bottlesn'] = 'gpbm' . $app->build_order_no();
            
            $data['bottle_name'] = $this->getRequest()->getPost('bottle_name');
            $data['bottle_netweight'] = $this->getRequest()->getPost('bottle_netweight',0);
            $data['bottle_comment'] = $this->getRequest()->getPost('bottle_comment');
            $data['bottle_price'] = $this->getRequest()->getPost('bottle_price',0); //直营店价格
            $data['suggested_price'] = $this->getRequest()->getPost('suggested_price',0); //零售价格
            $data['other_price'] = $this->getRequest()->getPost('other_price',0);//加盟店价格
            $data['price'] = $this->getRequest()->getPost('price',0);//钢瓶价格
            $data['repair'] = $this->getRequest()->getPost('repair',0); //维修费
            $data['depreciation'] = $this->getRequest()->getPost('depreciation',0);//折旧
            $data['yearly_inspection'] = $this->getRequest()->getPost('yearly_inspection',0);//年检

            $id = $this->getRequest()->getPost('id');

            $bottleType = new BottletypeModel();
            $returnData = $bottleType->add($data, $id);
            if ($returnData['status'] == 200) {
                $return['status'] = 1;
            }
            //$this->success('ok', '/bottletype/index');
        }
        echo json_encode($return);
        exit;
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $data = LibF::M('bottle_type')->where('id = ' . $id)->find();
        $this->_view->assign($data);
        $this->_view->assign('$id');
        $this->_view->display('bottletype/add.phtml');
    }
    
    public function delAction() {
        $id = $this->getRequest()->getQuery('id');
        $status = LibF::M('bottle_type')->where(array('id' => $id))->save(array('status' => '-1'));
        if($status){
            $this->success('ok', '/bottletype/index');
        }else{
            $this->error('删除失败');
        }
        
    }

}