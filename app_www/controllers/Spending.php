<?php

/**
 * 采购支出
 */
class SpendingController extends \Com\Controller\Common {

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
     * 气站采购支出
     */
    public function procurementAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['type'] = $this->getRequest()->getPost('type');
        $param['goods_type'] = $this->getRequest()->getPost('goods_type');
        $param['procurement_no'] = $this->getRequest()->getPost('procurement_no');
        $param['name'] = $this->getRequest()->getPost('name');
        $this->_view->assign('page', $page);

        $spendingModel = new SpendingModel();
        $data = $spendingModel->dataList($param);

        $this->_view->assign($data);

        $type = array(1 => '燃气', 2 => '钢瓶', 3 => '配件');
        $this->_view->assign('type', $type);
        $this->_view->assign('param', $param);
        
        $allData = $spendingModel->dataTotal();
        $this->_view->assign('allData',$allData);
        
        if ($tempType == 'new') {
            $this->_view->display('spending/procurement_new.phtml');
        }
    }

    /**
     * 创建采购支出
     */
    public function addprocurementAction() {

        if (IS_POST) {

            $app = new App();
            $orderSn = $app->build_order_no();
            $data['procurement_no'] = 'CG' . $orderSn;
            $data['type'] = $this->getRequest()->getPost('type');
            $data['goods_type'] = $this->getRequest()->getPost('goods_type');
            $data['goods_num'] = $this->getRequest()->getPost('goods_num');
            $data['goods_price'] = $this->getRequest()->getPost('goods_price');
            $data['money'] = $this->getRequest()->getPost('money');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];
            $data['name'] = $this->getRequest()->getPost('name');
            $data['time'] = date('Y-m-d');
            $data['ctime'] = time();
            if (!empty($data['type'])) {
                switch ($data['type']) {
                    case 1:
                        $dataModel = new GasModel();
                        $dataType = $dataModel->getGasArray();
                        $data['goods_name'] = $dataType[$data['goods_type']]['gas_name'];
                        break;
                    case 2:
                        $dataModel = new BottletypeModel();
                        $dataType = $dataModel->getBottleTypeArray();
                        $data['goods_name'] = $dataType[$data['goods_type']]['bottle_name'];
                        break;
                    case 3:
                        $dataModel = new ProductsModel();
                        $dataType = $dataModel->getProductsArray();
                        $data['goods_name'] = $dataType[$data['goods_type']]['products_name'];
                        break;
                }
            }
            $id = $this->getRequest()->getPost('id');
            $spendingModel = new SpendingModel();
            $status = $spendingModel->add($data, $id);
            
            $this->success('创建成功', '/spending/procurement');
        }
        $type = array(1 => '燃气', 2 => '钢瓶', 3 => '配件');
        $this->_view->assign('type', $type);
    }

    /**
     * 钢瓶检测支出
     */
    public function detectAction() {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            if (!empty($param['ds_no']))
                $where['ds_no'] = $param['ds_no'];

            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');

            unset($param['time_start']);
            unset($param['end_time']);
            unset($param['submit']);
        }
        $where['type'] = 1;
        
        $param['type'] = $w['type'] = 1;
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $spendingModel = new FillingModel();
        $data = $spendingModel->getDataList($param, 'detect_service', $where, 'id desc');
        $this->_view->assign($data);

        $list = '';
        if(!empty($data['ext']['list'])){
            foreach($data['ext']['list'] as $value){
                $dinfo = LibF::M('detect_service_info')->where(array('ds_no'=>$value['ds_no']))->select();
                $num = count($dinfo);
                foreach($dinfo as $k => $dVal){
                    if($k ==0){
                        $list .= '<tr><td rowspan="'.$num.'">'.$value['ds_no'].'</td><td rowspan="'.$num.'">'.$value['name'].'</td>';
                        $list .= '<td>'.$dVal['goods_name'].'</td><td>'.$dVal['goods_num'].'</td><td>'.$dVal['goods_price'].'</td>';
                        $list .= '<td rowspan="'.$num.'">'.$value['money'].'</td><td rowspan="'.$num.'">'.$value['attn'].'</td>';
                        $list .= '<td rowspan="'.$num.'">'.$value['time'].'</td></tr>';
                    }else{
                        $list .= '<tr>';
                        $list .= '<td>'.$dVal['goods_name'].'</td><td>'.$dVal['goods_num'].'</td><td>'.$dVal['goods_price'].'</td></tr>';
                    }
                }
            }
        }
        $this->_view->assign('list',$list);
        
        $spendingModel = new SpendingModel();
        $allData = $spendingModel->detect_serverTotal($w);  
        $this->_view->assign('allData',$allData);
    }
    
    /**
     * 创建检测支出
     */
    public function adddetectAction() {
        if (IS_POST) {
            
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['ds_no'] = 'JC' . $orderSn;
            $data['supplier_id'] = $this->getRequest()->getPost('supplier_id');
            $data['name'] = '';
            $data['money'] = 0;
            $data['time'] = $this->getRequest()->getPost('time');
            
            $ctime = time();
            $data['ctime'] = $ctime;

            $supplier_list = $this->getRequest()->getPost('supplier_list');
            $bottle = $ibottle = array();
            if (!empty($supplier_list)) {
                foreach ($supplier_list as $v) {
                    
                    $b = array();
                    $vlist = explode('|', $v);
                    $data['money'] += $vlist[3] * $vlist[4];
                    $data['name'] = $vlist[1];

                    $iVal = array();
                    $iVal['ds_no'] = $data['ds_no'];
                    $iVal['goods_type'] = $b[] = $vlist[2];
                    $iVal['goods_num'] = $b[] = $vlist[3];
                    $iVal['goods_name'] = $b['name'] = $vlist[5];
                    $b['yearly_inspection'] = $vlist[4];
                    $iVal['goods_price'] = $vlist[4];
                    $iVal['money'] = $vlist[3] * $vlist[4];
                    $iVal['time'] = $data['time'];
                    $iVal['ctime'] = $ctime;
                    $ibottle[] = $iVal;
                    $bottle[] = $b;
                }
            }
            
            $data['bottle'] = json_encode($bottle);
            $data['type'] = $this->getRequest()->getPost('type', 1);
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['attn'] = $this->user_info['username'];
            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];

            $id = $this->getRequest()->getPost('id');
            $spendingModel = new SpendingModel();
            $returnData = $spendingModel->add_detect_server($data, $id);
            if ($returnData['status'] == 200) {
                if (!empty($ibottle)) {
                    LibF::M('detect_service_info')->uinsertAll($ibottle);
                }
            }

            echo json_encode($returnData);
        }
        exit;
    }
    
    /**
     * 钢瓶维修支出
     */
    public function serviceAction() {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            if (!empty($param['ds_no']))
                $where['ds_no'] = $param['ds_no'];

            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');

            unset($param['time_start']);
            unset($param['end_time']);
            unset($param['submit']);
        }
        $where['type'] = 2;

        $w['type'] = 2;
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $spendingModel = new FillingModel();
        $data = $spendingModel->getDataList($param, 'detect_service', $where, 'id desc');
        $this->_view->assign($data);

        $list = '';
        if(!empty($data['ext']['list'])){
            foreach($data['ext']['list'] as $value){
                $dinfo = LibF::M('detect_service_info')->where(array('ds_no'=>$value['ds_no']))->select();
                $num = count($dinfo);
                foreach($dinfo as $k => $dVal){
                    if($k ==0){
                        $list .= '<tr><td rowspan="'.$num.'">'.$value['ds_no'].'</td><td rowspan="'.$num.'">'.$value['name'].'</td>';
                        $list .= '<td>'.$dVal['goods_name'].'</td><td>'.$dVal['goods_num'].'</td><td>'.$dVal['goods_price'].'</td>';
                        $list .= '<td rowspan="'.$num.'">'.$value['money'].'</td><td rowspan="'.$num.'">'.$value['attn'].'</td>';
                        $list .= '<td rowspan="'.$num.'">'.$value['time'].'</td></tr>';
                    }else{
                        $list .= '<tr>';
                        $list .= '<td>'.$dVal['goods_name'].'</td><td>'.$dVal['goods_num'].'</td><td>'.$dVal['goods_price'].'</td></tr>';
                    }
                }
            }
        }
        $this->_view->assign('list',$list);
        
        $spendingModel = new SpendingModel();
        $allData = $spendingModel->detect_serverTotal($w);  
        $this->_view->assign('allData',$allData);
    }
    
    /**
     * 创建维修支出
     */
    public function addserviceAction() {
        if (IS_POST) {
            
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['ds_no'] = 'JC' . $orderSn;
            $data['supplier_id'] = $this->getRequest()->getPost('supplier_id');
            $data['name'] = '';
            $data['money'] = 0;
            $data['time'] = $this->getRequest()->getPost('time');
            
            $ctime = time();
            $data['ctime'] = $ctime;

            $supplier_list = $this->getRequest()->getPost('supplier_list');
            $bottle = $ibottle = array();
            if (!empty($supplier_list)) {
                foreach ($supplier_list as $v) {
                    
                    $b = array();
                    $vlist = explode('|', $v);
                    $data['money'] += $vlist[3] * $vlist[4];
                    $data['name'] = $vlist[1];

                    $iVal = array();
                    $iVal['ds_no'] = $data['ds_no'];
                    $iVal['goods_type'] = $b[] = $vlist[2];
                    $iVal['goods_num'] = $b[] = $vlist[3];
                    $iVal['goods_name'] = $b['name'] = $vlist[5];
                    $b['yearly_inspection'] = $vlist[4];
                    $iVal['goods_price'] = $vlist[4];
                    $iVal['money'] = $vlist[3] * $vlist[4];
                    $iVal['time'] = $data['time'];
                    $iVal['ctime'] = $ctime;
                    $ibottle[] = $iVal;
                    $bottle[] = $b;
                }
            }
            
            $data['bottle'] = json_encode($bottle);
            $data['type'] = 2;
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['attn'] = $this->user_info['username'];
            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];

            $id = $this->getRequest()->getPost('id');
            $spendingModel = new SpendingModel();
            $returnData = $spendingModel->add_detect_server($data, $id);
            if ($returnData['status'] == 200) {
                if (!empty($ibottle)) {
                    LibF::M('detect_service_info')->uinsertAll($ibottle);
                }
            }

            echo json_encode($returnData);
        }
        exit;
    }
    
    /**
     * 其它支出
     */
    public function oprocurementAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id)
                $param['shop_id'] = $this->shop_id;
            
            if (!empty($param['order_sn'])){
                $where['order_sn'] = $param['order_sn'];
                $getParam[] = "order_sn=" . $param['order_sn'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'].' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else{
            if ($this->shop_id){
                $where['shop_id'] = $this->shop_id;
                $param['shop_id'] = $this->shop_id;
            }
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
            $w['shop_id'] = $where['shop_id'] = $this->shop_id;
        }
        
        $map['raffinat'] = array('gt', 0);
        $map['depreciation'] = array('gt', 0);
        $map['_logic'] = 'OR';
        $where['_complex'] = $map;
        $where['_logic'] = "AND";
        $where['status'] = $w['status'] = 4;
        $where['ispayment'] = $w['ispayment'] = 1;

        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param, 'order', $where, 'ctime desc');
        $this->_view->assign($data);
        
        $dataTotal = LibF::M('order')->field('sum(raffinat) as total,sum(depreciation) as money,sum(residual_gas) as gas_total')->where($w)->find();
        $this->_view->assign('dataTotal',$dataTotal);
        
        $this->_view->assign('page',$param['page']);
    }
    
    /**
     * 其它收入
     */
    public function wholesaleAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $condition['is_old'] = 1;
        $condition['raffinat'] = array('gt', 0);
        $condition['_logic'] = 'OR';
        $where['_complex'] = $condition;
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['order_sn'])) {
                $where['order_sn'] = $param['order_sn'];
                $getParam[] = "order_sn=" . $param['order_sn'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (empty($param['shop_id']) && $this->shop_id) {
                $where['shop_id'] = $this->shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist', implode('&', $getParam));
        } else {
            if ($this->shop_id) {
                $where['shop_id'] = $this->shop_id;
            }
        }
        
        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param,'order',$where,'order_id desc');
        $this->_view->assign($data);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $dataTotal = LibF::M('order')->field('sum(raffinat) as total,sum(raffinat_weight) as weight,sum(depreciation) as zjtotal')->where($where)->find();
        $this->_view->assign('dataTotal',$dataTotal);
    }
}
