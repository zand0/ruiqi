<?php

/**
 * 燃气库存
 */
class GastjController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    protected $approval_genre;


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
        $this->modelD = LibF::D('Gastj');

        $this->approval_genre = array(1 => '燃气采购计划单', 2 => '钢瓶采购计划单', 3 => '配件采购计划单');
    }
    
    /**
     * 燃气采购计划
     */
    public function planlistAction() {
        
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (!empty($param['plan_no']))
                $where['plan_no'] = $param['plan_no'];
            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');
            
            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        
        
        $gastjModel = new FillingModel();
        $data = $gastjModel->getDataList($param,'gas_plan',$where);
        if (!empty($data['ext']['list'])) {
            foreach ($data['ext']['list'] as $key => &$value) {
                $value['product'] = !empty($value['product']) ? json_decode($value['product'], true) : '';
            }
        }

        $this->_view->assign($data);
        $this->_view->assign('page',$param['page']);
    }

    public function addplanAction() {
        
        $status = 0;
        if (IS_POST) {
            $admin_user_list = $this->getRequest()->getPost('admin_user_list');
            $list = $this->getRequest()->getPost('list');
            $comment = $this->getRequest()->getPost('comment');

            $time = time();
            
            $app = new App();
            $order_no = $app->build_order_no();
            $plan_no = 'rqcg' . $order_no . $this->user_info['user_id'];
            $data = array(
                'plan_no' => $plan_no,
                'product' => json_encode($list),
                'comment' => $comment,
                'user_id' => $this->user_info['user_id'],
                'user_name' => $this->user_info['username'],
                'ctime' => $time
            );
            $status = LibF::M('gas_plan')->add($data);
            if ($status) {
                $adminUserData = !empty($admin_user_list) ? explode('|', $admin_user_list) : array();

                $adata['approvalsn'] = 'sh' . $order_no . $this->user_info['user_id'];
                $adata['title'] = '燃气采购计划单';
                $adata['approval_user'] = isset($adminUserData[0]) ? $adminUserData[0] : 0;
                $adata['approval_username'] = isset($adminUserData[1]) ? $adminUserData[1] : '';
                $adata['comment'] = $comment;
                $adata['user'] = $this->user_info['user_id'];
                $adata['username'] = $this->user_info['username'];
                $adata['approval_documents'] = $plan_no;
                $adata['approval_genre'] = 1;
                $adata['time_created'] = $time;

                LibF::M('approval')->add($adata);
            }
        }
        $returnData['status'] = $status;
        echo json_encode($returnData);
        exit();
    }
    
    public function planinfoAction() {

        $sno = $this->getRequest()->getQuery('sno');

        $returnData = array();
        if ($sno) {
            $data = LibF::M('gas_plan')->where(array('plan_no' => $sno))->find();
            $this->_view->assign($data);
            if (!empty($data)) {
                $dataArr = !empty($data['product']) ? json_decode($data['product']) : '';
                if (!empty($dataArr)) {
                    $gasModel = new GasModel();
                    $gasObject = $gasModel->getGasArray();    //获取燃气类型

                    $supplierModel = new SupplierModel();
                    $supplerObject = $supplierModel->getSupplierObject(); //获取供应商
                    foreach ($dataArr as $value) {
                        if (is_string($value)) {
                            $val = !empty($value) ? explode('|', $value) : '';
                            $v['gas_name'] = $gasObject[$val[0]]['gas_name'];
                            $v['suppler_name'] = $supplerObject[$val[1]]['name'];
                            $v['num'] = $val[2];
                            $v['price'] = $val[3];
                            $returnData[] = $v;
                        }
                    }
                }
            }
        }
        $this->_view->assign('returnData', $returnData);
    }

}