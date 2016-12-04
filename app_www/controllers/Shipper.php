<?php

/**
 * 送气工管理
 */
class ShipperController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    public function init() {
        parent::init();
        $this->modelD = LibF::D('Shipper');

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

    public function addAction() {
        if (IS_POST) {
            $param['shop_id'] = $this->getRequest()->getPost('shop_id');
            $param['shipper_name'] = $this->getRequest()->getPost('shipper_name');
            $param['mobile_phone'] = $this->getRequest()->getPost('mobile_phone');
            $param['shipper_type'] = $this->getRequest()->getPost('shipper_type',1);
            $param['password'] = md5($param['mobile_phone']);
            $param['is_del'] = $this->getRequest()->getPost('is_del');
            $id = intval($this->getRequest()->getPost('id'));

            $param['ctime'] = time();
            if (!empty($id)) {
                $where['mobile_phone'] = $param['mobile_phone'];
                $where['shipper_id'] = array('neq', $id);
                $data = LibF::M('shipper')->where($where)->find();
                if (empty($data)) {
                    $this->modelD->edite('edite', $id, $param, $roles);
                    $msg = '更新成功';
                } else {
                    $ms = '更新失败,手机号重复';
                }
            } else {
                $where['mobile_phone'] = $param['mobile_phone'];
                $data = LibF::M('shipper')->where($where)->find();
                if (empty($data)) {
                    $this->modelD->add($param, $roles);
                    $msg = '添加成功';
                } else {
                    $msg = '添加失败，手机号重复';
                }
            }
            $this->success($msg, '/shipper/index');
        }
        $this->_view->assign('shop_levels', LibF::C("shop.level")->toArray());

        //获取门店
        $this->_view->assign('shop_list', ShopModel::getShopArray());
        
        $this->_view->assign('shopid', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $data = $this->modelD->edite('', $id);

        $data['shop_levels'] = LibF::C("shop.level")->toArray();
        $data['shop_list'] = ShopModel::getShopArray(); //获取门店
        $this->_view->assign($data);

        $this->_view->assign('shopid', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
        $this->_view->display('shipper/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/shipper/index');
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }else{
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (isset($param['username']) && !empty($param['username'])){
                $where['shipper_name'] = $param['username'];
                $getParam[] = "username=" . $param['username'];
            }
            if (isset($param['mobile']) && !empty($param['mobile'])){
                $where['mobile_phone'] = $param['mobile'];
                $getParam[] = "mobile=" . $param['mobile'];
            }
            if (isset($param['shipper_type']) && !empty($param['shipper_type'])){
                $where['shipper_type'] = $param['shipper_type'];
                $getParam[] = "shipper_type=" . $param['shipper_type'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }
        $this->_view->assign('shop_id', $this->shop_id);
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);
        
        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'shipper', $where, 'shipper_id desc');
        $this->_view->assign($data);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }
    
    public function paymentAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['rq_shipper_payment.shop_id'] = $this->shop_id;
                $tWhere['shop_id'] = $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['rq_shipper_payment.shop_id'] = $param['shop_id'];
                    $tWhere['shop_id'] = $param['shop_id'];

                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }

            if (!empty($param['shipper_id'])) {
                $where['rq_shipper.shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
                $tWhere['shipper_id'] = $param['shipper_id'];
            }

            if (!empty($param['shipper_name'])) {
                $where['rq_shipper.shipper_name'] = $param['shipper_name'];
                $getParam[] = "shipper_name=" . $param['shipper_name'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $end_time = date('Y-m-d', strtotime("+1 day", strtotime($param['time_end'])));
                $where['rq_shipper_payment.time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($end_time)), 'AND');

                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['rq_shipper_payment.shop_id'] = $this->shop_id;
                $tWhere['shop_id'] = $this->shop_id;
            }

            if (!empty($param['shipper_id'])) {
                $where['rq_shipper.shipper_id'] = $param['shipper_id'];
                $tWhere['shipper_id'] = $param['shipper_id'];
            }
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);
        
        $files = "rq_shipper.shipper_id,rq_shipper.shipper_name,rq_shipper_payment.balance,rq_shipper_payment.id,rq_shipper_payment.money,rq_shipper_payment.shop_id,rq_shipper_payment.admin_user,rq_shipper_payment.time_created,rq_shipper_payment.status";
        $fillModel = new FillingModel();
        $data = $fillModel->getDataTableList($param, 'shipper_payment', 'rq_shipper_payment', 'rq_shipper', 'shipper_id', $where, 'rq_shipper_payment.id desc', $files);
        $this->_view->assign($data);

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $qkWhere['rq_order_arrears.type'] = 2;
        $qkWhere['rq_order_arrears.paytype'] = 4;
        if (!empty($param['shipper_id'])) {
            $qkWhere['rq_order_arrears.shipper_id'] = $param['shipper_id'];
        }
        if ($this->shop_id){
            $qkWhere['rq_kehu.shop_id'] = $this->shop_id;
        }
        $qmodel = new Model('order_arrears');
        $leftJoin = " LEFT JOIN rq_kehu ON rq_order_arrears.kid = rq_kehu.kid ";
        $qtotalData = $qmodel->join($leftJoin)->field("sum(rq_order_arrears.money) as total")->where($qkWhere)->select();

        $odWhere = $tWhere;
        $odWhere['order_paytype'] = 0;
        $odWhere['ispayment'] = 1;
        $odWhere['is_settlement'] = 1;
        $odWhere['status'] = 4;
        if($this->shop_id){
            $odWhere['shop_id'] = $this->shop_id;
            $tWhere['shop_id'] = $this->shop_id;
        }
        
        $totalData = LibF::M('order')->field("sum(pay_money) as total")->where($odWhere)->find();  //订单收费
        $srTotal = $totalData['total'] + $qtotalData['total'];
        $this->_view->assign('srTotal', $srTotal);

        $ztotalData = LibF::M('shipper_payment')->field("sum(money) as total")->where($tWhere)->find(); //送气工上缴
        $sjTotal = $ztotalData['total'];
        $this->_view->assign('sjTotal',$sjTotal);
        
        $dtotalData = LibF::M('deposit_list')->field("sum(money) as total")->where($tWhere)->find(); //押金支出
        $zcTotal = $dtotalData['total'] + $ztotalData['total'];
        $this->_view->assign('zcTotal',$zcTotal);
        $yeTotal = $srTotal - $zcTotal;
        $this->_view->assign('yeTotal',$yeTotal);
    }
    
    public function paymentlistAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }else{
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (isset($param['shipper_id']) && !empty($param['shipper_id'])){
                $where['shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
            if (isset($param['shipper_id']) && !empty($param['shipper_id'])){
                $where['shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }
        }
        $this->_view->assign('shop_id', $this->shop_id);
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'shipper_paylist', $where, 'id desc');
        $this->_view->assign($data);

        $typeObject = array(1 => '订单收费', 2 => '欠款收缴', 3 => '退瓶支出');
        $dataTotal = LibF::M('shipper_paylist')->field('type,sum(money) as total')->group('type')->select();
        $this->_view->assign('typeObject', $typeObject);
        $this->_view->assign('dataTotal', $dataTotal);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    public function hkshopAction(){
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }else{
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }
        $this->_view->assign('shop_id', $this->shop_id);

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'confirme_shipper', $where, 'id desc');
        if (!empty($data['ext']['list'])) {
            foreach ($data['ext']['list'] as $key => &$value) {
                $value['bottle_arr'] = !empty($value['bottle']) ? json_decode($value['bottle'], TRUE) : array();
                $value['bottle_num'] = count($value['bottle_arr']);

                $value['list1'] = $value['list2'] = '';
                if (!empty($value['bottle_arr'])) {
                    foreach ($value['bottle_arr'] as $k => $val) {
                        if ($k == 0) {
                            $value['list1'] = "<td>" . $val['type_name'] . '-' . $val['type_num'] . "</td>";
                        } else {
                            $value['list2'] .= "<tr><td>" . $val['type_name'] . '-' . $val['type_num'] . "</td></tr>";
                        }
                    }
                } else {
                    $value['list1'] = '<td></td>';
                }
            }
        }
        $this->_view->assign($data);
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $bottleStatus = array(0 => '配件', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $this->_view->assign('bottleStatus', $bottleStatus);
    }
    
    public function hkinfoAction() {
        $sn = $this->getRequest()->getQuery('sn');
        if (!empty($sn)) {
            $data = LibF::M('confirme_shipper')->where(array('confirme_no' => $sn))->find();
            if (!empty($data)) {
                $data['bottle_arr'] = !empty($data['bottle']) ? json_decode($data['bottle'], TRUE) : array();
                $data['bottle_num'] = count($data['bottle_arr']);

                $data['list1'] = $data['list2'] = '';
                foreach ($data['bottle_arr'] as $k => $val) {
                    if ($k == 0) {
                        $data['list1'] = "<td>" . $val['type_name'] . '--' . $val['type_num'] . "</td>";
                    } else {
                        $data['list2'] .= "<tr><td>" . $val['type_name'] . '--' . $val['type_num'] . "</td></tr>";
                    }
                }

                $data['bottle_info'] = !empty($data['bottle_data']) ? json_decode($data['bottle_data'], true) : array();
            }
            $this->_view->assign($data);
            $shopObject = ShopModel::getShopArray();
            $this->_view->assign('shopObject', $shopObject);
            
            $bottleStatus = array(0 => '配件', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
            $this->_view->assign('bottleStatus', $bottleStatus);
        } else {
            $this->error('当前订单号不存');
        }
    }
    
    public function ajaxhkbottleAction(){
        $sn = $this->getRequest()->getPost('sn');
        $bottle = $this->getRequest()->getPost('bottle');
        $bottle_data = $this->getRequest()->getPost('bottle_data');
        $shop_id = $this->getRequest()->getPost('shop_id');
        $shipper_id = $this->getRequest()->getPost('shipper_id');
        $type = $this->getRequest()->getPost('ftype'); //1空瓶2重瓶3折旧瓶4待修瓶

        $status = 0;
        $msg = '';
        if (!empty($sn) && !empty($shop_id) && !empty($shipper_id) && !empty($bottle)) {
            if (!empty($type)) {
                //规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //送气工出库
                $status = $this->shipperckData($shipper_id, $bottle, $shop_id, $this->user_info['username'], $type, $bottleTypeData);

                //门店库存增加
                $status = $this->shoprkData($shop_id, $bottle, $shipper_id, $this->user_info['username'], $type, $bottleTypeData);
                if ($type == 1 || $type == 2 || $type == 4) {
                    //获取当前钢瓶规格
                    //$bottleModel = new BottleModel();
                    //$bottleData = $bottleModel->bottleOData();
                    if (!empty($bottle_data)) {
                        $this->delShipperBottle($shipper_id, $bottle_data, $type);
                        $this->bottleStoreinfo($shop_id, $shipper_id, $bottle_data, $type, array(), $sn);
                    }
                }
            }else{

                $status = $this->shipperpckData($shipper_id, $bottle, $shop_id, $this->user_info['username']);
                $status = $this->shopprkData($shop_id, $bottle, $shipper_id, $this->user_info['username']);
            }

            $udate['status'] = 1;
            $udate['admin_id'] = $this->user_info['user_id'];
            $udate['admin_user'] = $this->user_info['username'];
            LibF::M('confirme_shipper')->where(array('confirme_no' =>$sn))->save($udate);
        } else {
            $msg = '当前参数缺失';
        }
        $returnData['status'] = $status;
        $returnData['msg'] = $msg;
        echo json_encode($returnData);
        exit();
    }
    
    //门店入库(钢瓶)
    protected function shoprkData($shop_id, $data, $shipper_id, $user_name = '', $type, $bottleTypeData) {
        if (empty($shop_id) || empty($data))
            return false;

        $shopObject = ShopModel::getShopArray();

        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'gpkc' . $orderSn;
        $param['type'] = 1; //出入库1入库 2出库
        $param['time'] = date('Y-m-d');
        $param['ctime'] = time();
        $param['gtype'] = 1; //1钢瓶2配件
        $param['shop_id'] = $shop_id;
        $param['shop_name'] = $shopObject[$shop_id]['shop_name'];
        if (!empty($shipper_id))
            $param['shipper_id'] = $shipper_id;
        $param['for_name'] = '送气工';
        if (!empty($user_name))
            $param['admin_user'] = $user_name;

        $status = 0;
        $hkDdata = json_decode($data, TRUE);
        if (!empty($hkDdata)) {
            $stockmethodModel = new StockmethodModel();
            foreach ($hkDdata as $key => $dVal) {
                $param['goods_propety'] = $type;  //钢瓶类型
                $param['goods_id'] = $param['goods_type'] = $dVal['type_id']; //钢瓶规格
                if (isset($bottleTypeData[$param['goods_id']])) {
                    $param['goods_name'] = $bottleTypeData[$param['goods_id']]['bottle_name'];
                    $param['goods_price'] = !empty($bottleTypeData[$param['goods_id']]['bottle_price']) ? $bottleTypeData[$param['goods_id']]['bottle_price'] : 0;
                    $param['goods_num'] = $dVal['type_num'];

                    $status = $stockmethodModel->ShopstationsStock($param, $shop_id, 1);  //统一出入库（门店）
                }
            }
        }
        return $status;
    }
    
    //门店入库（配件）
    protected function shopprkData($shop_id, $data, $shipper_id, $user_name = '') {
        if (empty($shop_id) || empty($data) || empty($shipper_id))
            return FALSE;

        $status = 0;
        $ckDdata = json_decode($data, TRUE);
        
        if (!empty($ckDdata)) {
            //获取订单号
            $app = new App();
            $orderSn = $app->build_order_no();
            $param['documentsn'] = 'gpkc' . $orderSn;
            $param['type'] = 1;
            $param['admin_user'] = $user_name;
            $param['time'] = date('Y-m-d');
            $param['ctime'] = time();
            $param['gtype'] = 2;
            
            $stockmethodModel = new StockmethodModel();
            foreach ($ckDdata as $dVal) {

                $param['goods_propety'] = $dVal['type_kind'];
                $param['goods_id'] = $param['goods_type'] = $dVal['type_id']; //配件id
                $param['goods_name'] = $dVal['type_name'];
                $param['goods_num'] = $dVal['type_num'];
                $param['shipper_id'] = $shipper_id;
                $param['shop_id'] = $shop_id;
                $param['for_name'] = '送气工';

                $status = $stockmethodModel->ShopstationsStock($param, $shop_id, 2);  //统一出入库（门店）
            }
        }
        return $status;
    }

    //钢瓶详细信息到门店
    protected function bottleStoreinfo($shop_id, $shipper_id, $bottle_data, $type, $bottleTypeData, $sn) {
        if (empty($shop_id) || empty($shipper_id) || empty($bottle_data) || empty($type))
            return false;

        $status = 0;
        $bottleInfo = json_decode($bottle_data, true);
        if (!empty($bottleInfo)) {
            $numberArr = array();
            $kData = array();

            $time = time();

            if (empty($bottleTypeData)) {
                $bArr = array();
                foreach ($bottleInfo as $value) {
                    //$bArr[] = "'" . $value['number'] . "'";
                    $bArr[] = str_replace(array("\r\n", "\r", "\n"), "", $value['number']);
                }

                $bottleModel = new BottleModel();
                $bottleTypeData = $bottleModel->bottleOData($bArr);
            }

            $bottlelogData = array();
            foreach ($bottleInfo as $value) {
                $numberArr[] = "'" . $value['number'] . "'";
                $val['sn'] = $sn;
                $val['number'] = $value['number'];
                $val['xinpian'] = $value['xinpian'];
                if (isset($bottleTypeData['number'][$val['number']]) && !empty($bottleTypeData['number'][$val['number']])) {
                    //$val['bar_code'] = $bottleTypeData['number'][$val['number']]['bar_code'];
                    $val['type'] = $bottleTypeData['number'][$value['number']]['type'];
                }
                $val['shop_id'] = $shop_id;
                if ($type == 1) {
                    $val['is_open'] = 0;
                    $val['is_use'] = 0;
                } else if ($type == 4) {
                    $val['is_open'] = 2;
                    $val['is_use'] = 1;
                } else {
                    $val['is_open'] = 1;
                    $val['is_use'] = 1;
                }
                $val['status'] = 1;
                $val['time_created'] = $time;
                $kData[] = $val;

                //添加空瓶日志
                $log['number'] = $val['number'];
                $log['xinpian'] = $val['xinpian'];
                if (isset($val['bar_code']) && !empty($val['bar_code']))
                    $log['bar_code'] = $val['bar_code'];
                $log['type'] = 2;
                $log['property'] = 1;
                $log['shop_id'] = $shop_id;
                $log['shop_time'] = $log['time_created'] = $time;
                $log['shipper_id'] = $shipper_id;
                $log['shipper_time'] = $time;
                $bottlelogData[] = $log;
            }
            
            if ($type == 2) {
                $where = " number IN (" . implode(',', $numberArr) . ") AND shop_id = " . $shop_id . " AND is_open = 1 AND status = 2 AND is_use = 0 ";
                $udate = array('status' => 1, 'is_use' => 1);
                $status = LibF::M('store_inventory')->where($where)->save($udate);

                $logWhere = " number IN (" . implode(',', $numberArr) . ") AND shop_id = " . $shop_id . " AND shipper_id = " . $shipper_id . " AND property = 0 ";
                $logudate = array('shipper_id' => 0, 'shipper_time' => 0);
                LibF::M('bottle_log')->where($logWhere)->save($logudate);
            } else {
                if (!empty($kData))
                    LibF::M('store_inventory')->uinsertAll($kData);

                if (!empty($bottlelogData))
                    LibF::M('bottle_log')->uinsertAll($bottlelogData);
                $status = 1;
            }
        }
        return $status;
    }

    //删除送气工的钢瓶信息
    protected function delShipperBottle($shipper_id, $bottleData = array(), $type = 1) {
        if (empty($shipper_id) || empty($bottleData))
            return false;

        $status = 0;
        $bottleArr = json_decode($bottleData, TRUE);
        if (!empty($bottleArr)) {

            $numberArr = array();
            foreach ($bottleArr as $value) {
                //$numberArr[] = "'" . $value['number'] . "'";
                $numberArr[] = str_replace(array("\r\n", "\r", "\n"), "", $value['number']);
            }
            if (!empty($numberArr)) {
                //$where = "number IN(" . implode(',', $numberArr) . ") AND shipper_id = " . $shipper_id;
                $where['number'] = array('in', $numberArr);
                $where['shipper_id'] = $shipper_id;
                $status = LibF::M('shipper_inventory')->where($where)->delete();
            }
        }
        return $status;
    }

    //送气工出库(钢瓶)
    protected function shipperckData($shipper_id, $data, $shop_id, $user_name = '', $type, $bottleTypeData) {

        if (empty($shipper_id) || empty($data))
            return false;

        $param['type'] = 2; //1入库 2出库

        $time = time();
        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'sqgck' . $orderSn; //获取订单号
        $param['time'] = date('Y-m-d');
        $param['ctime'] = $time;
        $param['admin_user'] = $user_name;

        $status = 0;
        $ckDdata = json_decode($data, TRUE);
        if (!empty($ckDdata)) {
            $stockmethodModel = new StockmethodModel();
            foreach ($ckDdata as $key => $dVal) {
                $param['goods_propety'] = $type;
                $param['goods_type'] = $dVal['type_id']; //钢瓶规格
                $param['goods_name'] = $bottleTypeData[$param['goods_type']]['bottle_name'];
                $param['goods_num'] = $dVal['type_num'];
                $param['goods_time'] = $time;
                $param['shipper_id'] = $shipper_id;
                $param['gtype'] = 1;
                if (!empty($shop_id))
                    $param['shop_id'] = $shop_id;
                if (!empty($user_id))
                    $param['user_id'] = $user_id;

                $status = $stockmethodModel->ShipperstationsStock($param, $shipper_id, $shop_id, $user_id, 1);  //统一出入库（门店）
            }
        }
        return $status;
    }

    //送气工出库(配件)
    protected function shipperpckData($shipper_id, $data, $shop_id, $user_name = '') {
        if (empty($shipper_id) || empty($data) || empty($shop_id))
            return FALSE;

        $param['type'] = 2; //1入库 2出库

        $time = time();
        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'sqgck' . $orderSn; //获取订单号
        $param['time'] = date('Y-m-d');
        $param['ctime'] = $time;
        if (!empty($user_name))
            $param['admin_user'] = $user_name;

        $status = 0;
        $ckDdata = json_decode($data, TRUE);

        $stockmethodModel = new StockmethodModel();
        foreach ($ckDdata as $dVal) {

            $param['goods_propety'] = $dVal['type_kind'];  //配件规格
            $param['goods_type'] = $dVal['type_id']; //0配件
            $param['goods_name'] = $dVal['type_name'];
            $param['goods_num'] = $dVal['type_num'];
            $param['shop_id'] = $shop_id;
            $param['shipper_id'] = $shipper_id;
            $param['gtype'] = 2;

            $status = $stockmethodModel->ShipperstationsStock($param, $shipper_id, $shop_id, 0, 2);  //统一出入库（气站）
        }
        return $status;
    }

}