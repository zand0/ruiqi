<?php 
/**
 * 钢瓶 配件进货单
 */

class InstockController extends \Com\Controller\Common{
    
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
    /**
     * 配件采购计划单
     */
    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (!empty($param['documentsn']))
                $where['documentsn'] = $param['documentsn'];
            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');

            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('param',$param);

        $products = new FillingModel();
        $data = $products->getDataList($param,'warehousing',$where,'time_created desc');
        
        $data['ext']['shop_list'] = ShopModel::getShopArray(); //获取门店
        $this->_view->assign($data['ext']);
        
        $this->_view->assign('page',$param['page']);
    }

    public function addAction() {

        $status = 0;
        if (IS_POST) {
            $admin_user_list = $this->getRequest()->getPost('admin_user_list');
            $list = $this->getRequest()->getPost('list');
            $comment = $this->getRequest()->getPost('comment');

            //获取单据编号
            $time = time();
            
            $app = new App();
            $documentsn = $app->build_order_no();
            $data['documentsn'] = 'pjcg' . $documentsn . $this->user_info['user_id'];
            $data['product'] = json_encode($list);
            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];
            $data['comment'] = $comment;
            $data['time_created'] = $time;
            $status = LibF::M('warehousing')->add($data);
            if($status){
                $insertData = array();
                $productObject = ProductsModel::getProductsArray(); //获取配件类型
                foreach ($list as $key => $value) {
                    $v = explode('|', $value);

                    $iVal['documentsn'] = $data['documentsn'];
                    $iVal['product_id'] = $v[0];
                    $iVal['product_name'] = $productObject[$v[0]]['products_name'];
                    $iVal['product_num'] = $v[2];
                    $iVal['product_price'] = $v[3];
                    $iVal['product_total'] = $v[2] * $v[3];
                    $iVal['time_created'] = $data['time_created'];
                    $insertData[] = $iVal;
                }
                if (!empty($insertData)){
                    LibF::M('warehousing_info')->uinsertAll($insertData);
                }
                
                $adminUserData = !empty($admin_user_list) ? explode('|', $admin_user_list) : array();

                $adata['approvalsn'] = 'sh' . $documentsn . $this->user_info['user_id'];
                $adata['title'] = '配件采购计划单';
                $adata['approval_user'] = isset($adminUserData[0]) ? $adminUserData[0] : 0;
                $adata['approval_username'] = isset($adminUserData[1]) ? $adminUserData[1] : '';
                $adata['comment'] = $comment;
                $adata['user'] = $this->user_info['user_id'];
                $adata['username'] = $this->user_info['username'];
                $adata['approval_documents'] = $data['documentsn'];
                $adata['approval_genre'] = 3;
                $adata['time_created'] = $time;

                LibF::M('approval')->add($adata);
            }
        }
        $returnData['status'] = $status;
        echo json_encode($returnData);
        exit();
    }
    
    public function infoAction() {
        $id = $this->getRequest()->getQuery('id');

        $returnData = array();
        if($id){
            $data = LibF::M('warehousing')->where(array('documentsn' => $id))->find();
            if ($data) {
                $dataArr = !empty($data['product']) ? json_decode($data['product']) : '';
                if (!empty($dataArr)) {
                    $productObject = ProductsModel::productsArrlist(); //获取配件类型

                    $supplierModel = new SupplierModel();
                    $supplerObject = $supplierModel->getSupplierObject(); //获取供应商
                    foreach ($dataArr as $value) {
                        $val = !empty($value) ? explode('|', $value) : '';
                        $v['product_name'] = $productObject[$val[0]]['products_name'];
                        $v['suppler_name'] = $supplerObject[$val[1]]['name'];
                        $v['num'] = $val[2];
                        $v['price'] = $val[3];
                        $returnData[] = $v;
                    }
                }
            }
        }
        $this->_view->assign('returnData', $returnData);
    }

    /**
     * 钢瓶采购计划单
     */
    public function purchaseAction() {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (!empty($param['documentsn']))
                $where['documentsn'] = $param['documentsn'];
            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');

            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('param',$param);

        $products = new FillingModel();
        $data = $products->getDataList($param, 'bottle_purchase', $where, 'time_created desc');
        $this->_view->assign($data['ext']);
    }

    public function purchaseinfoAction() {
        $id = $this->getRequest()->getQuery('id');

        $returnData = array();
        if($id){
            $data = LibF::M('bottle_purchase')->where(array('documentsn' => $id))->find();
            if ($data) {
                $dataArr = !empty($data['bottle']) ? json_decode($data['bottle']) : '';
                if (!empty($dataArr)) {
                    $bottleType = new BottletypeModel();
                    $bottleTypeObject = $bottleType->getBottleTypeArray();

                    $supplierModel = new SupplierModel();
                    $supplerObject = $supplierModel->getSupplierObject(); //获取供应商
                    foreach ($dataArr as $value) {
                        $val = !empty($value) ? explode('|', $value) : '';
                        $v['bottle_name'] = $bottleTypeObject[$val[0]]['bottle_name'];
                        $v['suppler_name'] = $supplerObject[$val[1]]['name'];
                        $v['num'] = $val[2];
                        $v['price'] = $val[3];
                        $returnData[] = $v;
                    }
                }
            }
        }
        $this->_view->assign('returnData', $returnData);
    }

    public function bottleaddAction() {

        if (IS_POST) {
            $admin_user_list = $this->getRequest()->getPost('admin_user_list');
            $list = $this->getRequest()->getPost('list');
            $comment = $this->getRequest()->getPost('comment');

            //获取单据编号
            $time = time();
            
            $app = new App();
            $documentsn = $app->build_order_no();

            $data['bottle'] = json_encode($list);
            $data['documentsn'] = 'gpcg' . $documentsn . $this->user_info['user_id'];
            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];
            $data['comment'] = $comment;
            $data['time_created'] = $time;
            $status = LibF::M('bottle_purchase')->add($data);
            if ($status) {
                $insertData = array();
                $bottleType = new BottletypeModel();
                $bottleTypeObject = $bottleType->getBottleTypeArray();
                foreach ($list as $key => $value) {
                    $v = explode('|', $value);

                    $iVal['documentsn'] = $data['documentsn'];
                    $iVal['bottle_id'] = $v[0];
                    $iVal['bottle_name'] = $bottleTypeObject[$v[0]]['bottle_name'];
                    $iVal['bottle_num'] = $v[2];
                    $iVal['bottle_price'] = $v[3];
                    $iVal['bottle_total'] = $v[2] * $v[3];
                    $iVal['time_created'] = $data['time_created'];
                    $insertData[] = $iVal;
                }
                if (!empty($insertData)){
                    LibF::M('bottle_purchase_info')->uinsertAll($insertData);
                }
                
                $adminUserData = !empty($admin_user_list) ? explode('|', $admin_user_list) : array();

                $adata['approvalsn'] = 'sh' . $documentsn . $this->user_info['user_id'];
                $adata['title'] = '钢瓶采购计划单';
                $adata['approval_user'] = isset($adminUserData[0]) ? $adminUserData[0] : 0;
                $adata['approval_username'] = isset($adminUserData[1]) ? $adminUserData[1] : '';
                $adata['comment'] = $comment;
                $adata['user'] = $this->user_info['user_id'];
                $adata['username'] = $this->user_info['username'];
                $adata['approval_documents'] = $data['documentsn'];
                $adata['approval_genre'] = 2;
                $adata['time_created'] = $time;

                LibF::M('approval')->add($adata);
            }
            $returnData['status'] = $status;
            echo json_encode($returnData);
        }
        exit();
    }

}
