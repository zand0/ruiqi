<?php

/*
 * 获取基础模板数据 
 * 
 * @date 2016/03/12
 */

class AjaxdataController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;
    protected $typeObject;

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
        $this->typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
        //备注 1门店经理2押运员3超级管理员 4区域经理 5充装班长 6气站站长7 采购经理
        
    }
    
    //获取基础的钢瓶类型|配件类型
    public function datatypeAction() {
        $type = $this->getRequest()->getPost('type');
        $idsn = $this->getRequest()->getPost('idsn',0);
        $data = array();
        switch ($type) {
            case 'shop':
                $data['shop'] = ShopModel::getShopArray(); //获取门店
                $data['shipper'] = '';
                if ($idsn > 0) {
                    $shipperModel = new ShipperModel();
                    $data['shipper'] = $shipperModel->getShipperArray('', $idsn);
                }
                break;
            case 'bottle':
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                break;
            case 'product':
                $data['product'] = ProductsModel::productsArrlist(); //获取配件类型
                break;
            case 'cproduct':
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $fillingstocklogModel = new FillingModel();
                $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'is_app desc,type asc,id asc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    }
                }
                $data['product'] = $commdityData;
                break;
            case 'commdity_product':
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $fillingstocklogModel = new FillingModel();
                $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'is_app desc,type asc,id asc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    }
                }
                $data['product'] = $commdityData;
                $data['shop'] = ShopModel::getShopArray(); //获取门店
                break;
            case 'commdity_product_md':
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $fillingstocklogModel = new FillingModel();
                $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'is_app desc,type asc,id asc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    }
                }
                $data['product'] = $commdityData;
                $shop_id = $this->shop_id;
                if(!empty($shop_id)){
                    $shipperModel = new ShipperModel();
                    $data['shipper'] = $shipperModel->getShipperArray('', $shop_id);
                }
                break;
            case 'producttype':
                $producttypeModel = new ProducttypeModel();
                $data['producttype'] = $producttypeModel->getProductTypeArray();  //获取配件规格
                break;
            case 'all':
                $data['shop'] = ShopModel::getShopArray(); //获取门店
                
                $bottleObject = BottleModel::getBottleType(); //获取钢瓶类型
                $data['jbottle'] = $bottleObject;

                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();
                $data['jproduct'] = $productTypeData;

                $fillingstocklogModel = new FillingModel();
                
                $jcommdityData = array();
                $wherelist['status'] = 0;
                $wherelist['type'] = array('in', array(1, 2));
                $commdityData = $fillingstocklogModel->getDataArr('commodity', $wherelist, 'is_app desc,type asc,id asc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        if ($value['type'] == 2) {
                            $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                            $data['product'][] = $value;
                        } else {
                            $value['typename'] = isset($bottleObject[$value['norm_id']]) ? $value['name'] . '-' . $bottleObject[$value['norm_id']] : '';
                            $data['bottle'][] = $value;
                        }
                        $jcommdityData[$value['id']] = $value;
                    }
                }
                $data['jcommdity'] = $jcommdityData;

                $carModel = new CarModel();
                $data['car'] = $carModel->carObject();

                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(2);
                
                $dso = $this->getRequest()->getPost('dso');
                $data['cdata'] = array();
                if (!empty($dso)) {
                    $data['cdata'] = LibF::M('delivery_detail')->where(array('delivery_no' => $dso))->select();
                }

                break;
            case 'other':
                $data['shop'] = ShopModel::getShopArray(); //获取门店
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                $data['type'] = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                
                $carModel = new CarModel();
                $data['car'] = $carModel->carObject();

                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(2);
                break;
            case 'other_edit':
                
                $data['shop'] = ShopModel::getShopArray(); //获取门店
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                $data['type'] = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');

                $carModel = new CarModel();
                $data['car'] = $carModel->carObject();

                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(2);

                $cno = $this->getRequest()->getPost('cno');
                $data['cdata'] = array();
                if (!empty($cno)) {
                    $data['cdata'] = LibF::M('confirme_store_detail')->where(array('confirme_no' => $cno))->select();
                }

                break;
            case 'gas':
                $gasModel = new GasModel();
                $data['gas'] = $gasModel->getGasArray();    //获取燃气类型
                
                $supplierModel = new SupplierModel();
                $data['supplier'] = $supplierModel->getSupplierObject(); //获取供应商
                
                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(7);
                
                break;
            case 'bottle_plan':
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                
                $supplierModel = new SupplierModel();
                $data['supplier'] = $supplierModel->getSupplierObject(); //获取供应商
                
                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(7);
                break;
            case 'product_plan':
                $data['product'] = ProductsModel::productsArrlist(); //获取配件类型
                
                $supplierModel = new SupplierModel();
                $data['supplier'] = $supplierModel->getSupplierObject(); //获取供应商
                
                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(7);
                break;
            case 'gas_kucun':
                $gasModel = new GasModel();
                $data['gas'] = $gasModel->getGasArray();    //获取燃气类型
                
                $tankModel = new TankModel();
                $data['tank'] = $tankModel->getTankArray('',array('status' => 1));  //储罐表
                break;
            case 'bottle_kucun':
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                $data['shop'] = ShopModel::getShopArray(); //获取门店
                $data['type'] = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
                break;
            case 'md_kucun':
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                $data['type'] = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶');
                $shop_id = $this->shop_id;
                if(!empty($shop_id)){
                    $shipperModel = new ShipperModel();
                    $data['shipper'] = $shipperModel->getShipperArray('', $shop_id);
                }
                break;
            case 'shipper':
                $shop_id = $this->getRequest()->getPost('shop_id');
                if(!empty($shop_id)){
                    $shipperModel = new ShipperModel();
                    $data['shipper'] = $shipperModel->getShipperArray('', $shop_id);
                }
                break;
            case 'order':
                $commodityModel = new CommodityModel();
                $commodityData = $commodityModel->listData();
                if (!empty($commodityData)) {
                    //获取当前钢瓶规格
                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                    //获取配件规格
                    $productTypeModel = new ProducttypeModel();
                    $productTypeData = $productTypeModel->getProductTypeArray();
                    foreach ($commodityData as &$value) {
                        if($value['type'] == 1){
                            $value['typename'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                        }else{
                            $value['typename'] = $productTypeData[$value['norm_id']]['name'];
                        }
                    }
                }
                $data['shop'] = ShopModel::getShopArray(); //获取门店

                $data['commdity'] = $commodityData;
                $kehuType = array(1 => '居民用户', 2 => '商业用户');
                $data['kehu_type'] = $kehuType;
                $orderType = array(1 => '呼叫中心', 2 => '他人推荐', 3 => '门店拓展', 4 => '活动促销');
                $data['order_type'] = $orderType;
                $orderStatus = array(1 => '紧急订单', 2 => '押金开户', 3 => '上门安检');
                $data['order_status'] = $orderStatus;

                break;
            case 'car':
                $carModel = new CarModel();
                $data['car'] = $carModel->carObject();
                break;
            case 'czjh':
                $carModel = new CarModel();
                $data['car'] = $carModel->carObject();

                $cData = new CommonDataModel();
                $data['user'] = $cData->getAreaUser(5);
                break;
            default :
                $bStatus = array(1 => '空瓶', 3 => '折旧瓶',4 => '待修瓶');
                $data['bottle_status'] = $bStatus;
                $data['bottle'] = BottleModel::getBottleType(); //获取钢瓶类型
                
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();
                $fillingstocklogModel = new FillingModel();
                $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'is_app desc,type asc,id asc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    }
                }
                $data['product'] = $commdityData;
                break;
        }
        echo json_encode($data);
        exit();
    }
    
    //操作相关数据状态
    public function datastatusAction() {
        $type_sn = $this->getRequest()->getPost('type_sn');
        $type_name = $this->getRequest()->getPost('type_name');
        switch ($type_name){
            case 'filling':
                $data['status'] = 1;
                $status = LibF::M('filling')->where(array('filling_no' => $type_sn))->save($data);
                break;
        }
        $returnData['status'] = $status;
        echo json_encode($returnData);
        exit();
    }
    
    //气站确认出库单
    public function ckstockAction() {
        $dso = $this->getRequest()->getPost('dso');

        $returnData['status'] = 0;
        if (!empty($dso)) {
            //获取当前出库单详情
            $where['delivery_no'] = $dso;
            //$where['ftype'] = 1;
            $data = LibF::M('delivery_detail')->where($where)->select();
            if (!empty($data)) {
                $shopMoney = array();
                $ckData = $ckProduct = array();
                foreach ($data as $value) {
                    $val['kind_id'] = $value['fid'];
                    $val['num'] = $value['num'];
                    $val['shop_id'] = $value['shop_id'];
                    if ($value['ftype'] == 1) {
                        $ckData[] = $val;
                    } else {
                        $ckProduct[] = $val;
                    }
                    
                    $shopMoney[$val['shop_id']] += $value['total'];
                }

                $ckUData['comment'] = '';
                $ckUData['admin_user'] = $this->user_info['username'];
                $ckModel = new DataInventoryModel();
                //array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                if (!empty($ckData)) {
                    $status = $ckModel->systemck(0, $ckData, $ckUData, 2, 2);
                }
                if (!empty($ckProduct)) {
                    $status = $ckModel->ckProduct($shop_id, $ckProduct, $ckUData, 2, $dso);  //配件库存
                }
                LibF::M('delivery')->where($where)->save(array('status' => 1));
                    
                //结算门店金额
                if (!empty($shopMoney)) {
                    $time = time();
                    foreach ($shopMoney as $sk => $sv) {
                        $mW['shop_id'] = $sk;
                        $mD = LibF::M('shop')->where($mW)->find();
                        if (!empty($mD) && !empty($sk) && !empty($sv)) {
                            $total = floatval($mD['payment']) - floatval($sv);

                            $d['shop_id'] = $sk;
                            $d['time'] = $time;
                            $d['admin_id'] = $this->user_info['user_id'];
                            $d['admin_user'] = $this->user_info['username'];
                            $d['type'] = 2;
                            $d['money'] = $sv;
                            $d['balance'] = $total;
                            $d['delivery_no'] = $dso;
                            $d['time_created'] = $time;
                            $status = LibF::M('shop_prepayments')->add($d);
                            if ($status) {
                                LibF::M('shop')->where($mW)->setDec('payment', $sv);
                            }
                        }
                    }
                }
                
                $status = 1;
                $returnData['status'] = $status;
            }
        }
        echo json_encode($returnData);
        exit();
    }

    //门店确认入库单
    public function rkshopAction() {
        $cno = $this->getRequest()->getPost('cno');
        $shop_id = $this->getRequest()->getPost('shop_id');

        $returnData['status'] = 0;
        if(!empty($cno) && !empty($shop_id)){
            //获取当前出库单详情
            $where['confirme_no'] = $cno;
            $where['shop_id'] = $shop_id;
            //$where['ftype'] = 1;
            $data = LibF::M('confirme_detail')->where($where)->select();
            if (!empty($data)) {
                $ckData = $ckProduct = array();
                foreach ($data as $value) {
                    $val['kind_id'] = $value['type'];
                    $val['num'] = $value['num'];
                    $val['shop_id'] = $value['shop_id'];
                    if ($value['ftype'] == 1) {
                        $ckData[] = $val;
                    } else {
                        $ckProduct[] = $val;
                    }
                }
                $ckUData['comment'] = '';
                $ckUData['admin_user'] = $this->user_info['username'];

                $ckModel = new DataInventoryModel();
                if (!empty($ckData)) {
                    $status = $ckModel->shopck($shop_id, 0, $ckData, $ckUData, 1, 2);
                }
                if (!empty($ckProduct)) {
                    $status = $ckModel->shopckproduct($shop_id, 0, $ckProduct, $ckUData, 1, $cno);  //配件库存
                }
                if ($status) {
                    $wh = array('confirme_no' => $cno, 'shop_id' => $shop_id);
                    LibF::M('confirme')->where($wh)->save(array('status' => 1));
                }
                $returnData['status'] = $status;
            }
        }
        echo json_encode($returnData);
        exit();
    }
    
    //押运员回库确认
    public function yhkstockAction(){
        $cno = $this->getRequest()->getPost('cno');
        $shop_id = $this->getRequest()->getPost('shop_id');
        
        $returnData['status'] = 0;
        if (!empty($cno) && !empty($shop_id)) {
            //获取当前出库单详情
            $where['confirme_no'] = $cno;
            $where['shop_id'] = $shop_id;
            //$where['ftype'] = array('gt', 0);
            $data = LibF::M('confirme_store_detail')->where($where)->select();
            if (!empty($data)) {
                $ckData = $productData = array();
                foreach ($data as $value) {
                    if ($value['ftype'] == 0) {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['status_id'] = $value['ftype'];
                        $productData[] = $val;
                    } else {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['status_id'] = $value['ftype'];
                        $ckData[] = $val;
                    }
                }
                $ckUData['comment'] = '';
                $ckUData['admin_user'] = $this->user_info['username'];

                $ckModel = new DataInventoryModel();
                //备注当前押运员确认指定门店回库确认单数据 新增当前门店押运员确认数据，同时门店出库
                $iParam['confirme_no'] = $cno;
                $iParam['shop_id'] = $shop_id;
                $iParam['ctime'] = time();
                $iParam['status'] = 1;
                $iParam['admin_user'] = $this->user_info['username'];
                $status = LibF::M('confirme_store_detail_sp')->add($iParam);

                if (!empty($ckData) || !empty($productData)) {
                    if (!empty($ckData)) {
                        $status = $ckModel->shopck($shop_id, 0, $ckData, $ckUData, 2, 1);
                    }
                    if (!empty($productData)) {
                        $status = $ckModel->shopckproduct($shop_id, 0, $productData, $ckUData, 2, $cno);  //配件库存
                    }
                }
                LibF::M('confirme_store_detail')->where(array('confirme_no' => $cno, 'shop_id' => $shop_id))->save(array('status' => 1));
                $returnData['status'] = $status;
            }
        }
        echo json_encode($returnData);
        exit();
    }
    
    //气站确认回库单
    public function rkstockAction() {
        $cno = $this->getRequest()->getPost('cno');
        
        $returnData['status'] = 0;
        if(!empty($cno)){
            //获取当前出库单详情
            $where['confirme_no'] = $cno;
            //$where['ftype'] = array('gt', 0);
            $data = LibF::M('confirme_store_detail')->where($where)->select();
            if (!empty($data)) {
                $ckData = $productData = array();
                foreach ($data as $value) {
                    if ($value['ftype'] == 0) {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['status_id'] = $value['ftype'];
                        $productData[] = $val;
                    } else {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['status_id'] = $value['ftype'];
                        $ckData[] = $val;
                    }
                }
                
                $ckUData['comment'] = '';
                $ckUData['admin_user'] = $this->user_info['username'];

                $ckModel = new DataInventoryModel();
                //门店出库(正常逻辑是在什么情况下产生)
                //$status = $ckModel->shopck($shop_id, $shipper_id, $bottle, $kData, $type, $is_open);
                //气站入库 
                if (!empty($ckData)) {
                    $status = $ckModel->systemck(0, $ckData, $ckUData, 1, 1);
                }
                if (!empty($productData)) {
                    $status = $ckModel->ckProduct(0, $productData, $ckUData, 1, $cno);
                }
                $wh = array('confirme_no' => $cno);
                LibF::M('confirme_store')->where($wh)->save(array('status' => 1));
                $returnData['status'] = $status;
            }
        }
        echo json_encode($returnData);
        exit();
    }
    
    //申请审批
    public function shenpiAction() {
        $aid = $this->getRequest()->getPost('aid');
        $comment = $this->getRequest()->getPost('comment');
        $type = $this->getRequest()->getPost('type', 2);

        $returnData['status'] = 0;
        if (!empty($aid) && !empty($comment)) {
            //获取当前出库单详情
            $where['id'] = $aid;
            $data = LibF::M('approval')->where($where)->find();
            if (!empty($data)) {
                $d['approval_user'] = $this->user_info['user_id'];
                $d['approval_username'] = $this->user_info['username'];
                $d['approval_status'] = $type;
                $d['reason'] = $comment;
                $status = LibF::M('approval')->where($where)->save($d);
                $returnData['status'] = $status;

                if ($data['approval_genre'] == 1) {
                    $w['plan_no'] = $data['approval_documents'];
                    $u['status'] = $type;
                    $u['admin_user_id'] = $this->user_info['user_id'];
                    $u['admin_user_name'] = $this->user_info['username'];
                    LibF::M('gas_plan')->where($w)->save($u);
                } else if ($data['approval_genre'] == 2){
                    $w['documentsn'] = $data['approval_documents'];
                    $u['status'] = $type;
                    $u['effective_time'] = time();
                    $u['effective_user'] = $this->user_info['user_id'];
                    $u['effective_username'] = $this->user_info['username'];
                    LibF::M('bottle_purchase')->where($w)->save($u);
                } else if ($data['approval_genre'] == 3){
                    $w['documentsn'] = $data['approval_documents'];
                    $u['status'] = $type;
                    $u['effective_time'] = time();
                    $u['effective_user'] = $this->user_info['user_id'];
                    LibF::M('warehousing')->where($w)->save($u);
                }
            }
        }
        echo json_encode($returnData);
        exit();
    }

    //获取订单评论
    public function evaluateAction() {
        $ordersn = $this->getRequest()->getPost('ordersn');
        $returnData['status'] = 0;
        if (!empty($ordersn)) {
            //获取当前出库单详情
            $where['order_sn'] = $ordersn;
            $eModel = new FillingModel();
            $data = $eModel->getDataInfo('evaluation', $where);
            if (!empty($data)) {
                $returnData['status'] = 1;
                $returnData['data'] = $data;
            }
        }
        echo json_encode($returnData);
        exit();
    }
    
    //门店充值
    public function addpaymentAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');
        $money = $this->getRequest()->getPost('money');
        $type = $this->getRequest()->getPost('type', 1);

        $returnData['status'] = 0;
        
        $where['shop_id'] = $shop_id;
        $data = LibF::M('shop')->where($where)->find();
        if (!empty($data)) {
            if (!empty($shop_id) && !empty($money)) {
                $total = floatval($money) + floatval($data['payment']);

                $time = time();
                $d['shop_id'] = $shop_id;
                $d['time'] = $time;
                $d['admin_id'] = $this->user_info['user_id'];
                $d['admin_user'] = $this->user_info['username'];
                $d['type'] = $type;
                $d['money'] = $money;
                $d['balance'] = $total;
                $d['time_created'] = $time;
                $status = LibF::M('shop_prepayments')->add($d);
                if ($status) {
                    $swhere['shop_id'] = $shop_id;
                    LibF::M('shop')->where($swhere)->setInc('money', $money);
                }
                $returnData['status'] = $status;
            }
        }
        echo json_encode($returnData);
        exit();
    }

    //门店上缴
    public function uppaymentAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');
        $money = $this->getRequest()->getPost('money');

        $returnData['status'] = 0;
        
        $where['shop_id'] = $shop_id;
        $data = LibF::M('shop')->where($where)->find();
        if (!empty($data)) {
            if (!empty($shop_id) && !empty($money)) {
                $total = floatval($data['payment']) - floatval($money);

                $time = time();
                $d['shop_id'] = $shop_id;
                $d['time'] = $time;
                $d['admin_id'] = $this->user_info['user_id'];
                $d['admin_user'] = $this->user_info['username'];
                $d['type'] = 2;
                $d['money'] = $money;
                $d['balance'] = $total;
                $d['time_created'] = $time;
                $status = LibF::M('shop_prepayments')->add($d);
                if ($status) {
                    $swhere['shop_id'] = $shop_id;
                    LibF::M('shop')->where($swhere)->setDec('money', $money);
                }
                $returnData['status'] = $status;
            }
        }
        echo json_encode($returnData);
        exit();
    }
    
    //获取充装计划详情
    public function datafillAction() {
        $fno = $this->getRequest()->getPost('fno');

        $returnData['status'] = 0;
        if (!empty($fno)) {
            $where['filling_no'] = $fno;
            $fillingModel = new FillingModel();
            $data = $fillingModel->getDataArr('filling_bottle_log', $where, 'time desc');
            if (!empty($data)) {
                $btypes = BottleModel::getBottleType();
                foreach ($data as $key => &$value) {
                    $list = '';
                    $value['bottle'] = !empty($value['bottle']) ? json_decode($value['bottle'], true) : array();
                    $value['num'] = count($value['bottle']);
                    $value['list1'] = '';
                    $value['list2'] = '';

                    foreach ($value['bottle'] as $k => $v) {
                        if (!empty($v) && is_string($v)) {
                            $vList = explode('|', $v);
                            if ($k == 0) {
                                $value['list1'] = "<td>" . $btypes[$vList[0]] . "</td><td>" . $vList['1'] . "</td>";
                            } else {
                                $value['list2'] .= "<tr><td>" . $btypes[$vList[0]] . "</td><td>" . $vList['1'] . "</td></tr>";
                            }
                        } else {
                            if ($k == 0) {
                                $value['list1'] = "<td></td><td></td>";
                            } else {
                                $value['list2'] .= "<tr><td></td><td></td></tr>";
                            }
                        }
                    }
                }
                $returnData['message'] = $data;
            }
        }
        echo json_encode($returnData);
        exit();
    }

    //汇总送气工当前需要上缴款项
    public function shipperpaymentAction() {
        $shipper_id = $this->getRequest()->getPost('shipper_id');

        $returnData['status'] = 0;
        $date = date('Y-m-d');
        $time = strtotime($date);

        //$where['shipper_id'] = $shipper_id;
        $filed = "count(*) as total,sum(pay_money) as money,sum(deposit) as dmoney,sum(raffinat) as cmoney,sum(depreciation) as zmoney,shipper_id,shipper_name,shop_id";
        //$where['status'] = array('EGT', 4);
        //$where['otime'] = array('EGT', $time);

        $data = LibF::M('order')->field($filed)->where($where)->group('shipper_id')->select();
        if (!empty($data)) {
            $shipperVal = array();
            
            $now = time();
            foreach ($data as $value) {
                $val['shipper_id'] = $value['shipper_id'];
                $val['money'] = $value['money'];
                $val['shop_id'] = $value['shop_id'];
                $val['order_num'] = $value['total'];
                $val['deposit_total'] = $value['dmoney'];
                $val['zj_total'] = $value['zmoney'];
                $val['cy_total'] = $value['cmoney'];
                $val['time'] = $date;
                $val['time_created'] = $now;
                
                $shipperVal[] = $val;
            }
            if(!empty($shipperVal)){
                LibF::M('shipper_payment')->uinsertAll($shipperVal);
                $returnData['status'] = 1;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //充装数据入库（重瓶）
    public function qzrkbottleAction() {
        $cno = $this->getRequest()->getPost('cno');
        $returnData['status'] = 0;
        if(!empty($cno)){
            $where['filling_no'] = $cno;
            $where['status'] = 1;
            $data = LibF::M('filling_bottle_log')->where($where)->select();
            if (!empty($data)) {
                $ckData = array();
                $rkData = !empty($data['bottle']) ? json_encode($data['bottle']) : array();
                if (!empty($data)) {
                    foreach ($data as $value) {
                        $bottleArr = !empty($value['bottle']) ? json_decode($value['bottle']) : array();
                        if (!empty($bottleArr)) {
                            foreach ($bottleArr as $val) {
                                $rkList = explode('|', $val);
                                if (!empty($rkList)) {
                                    $v['kind_id'] = $rkList[0];
                                    $v['num'] = $rkList[1];
                                    $v['status_id'] = 2; //重瓶
                                    $ckData[] = $v;
                                }
                            }
                        }
                    }
                }

                $ckUData['comment'] = '';
                $ckUData['admin_user'] = $this->user_info['username'];

                $ckModel = new DataInventoryModel();
                //气站入库 
                $status = $ckModel->systemIn($ckData, $ckUData, 1, 2);
                if ($status) {
                    $ckModel->systemIn($ckData, $ckUData, 2, 1);
                    $wh = array('filling_no' => $cno);
                    LibF::M('filling_bottle_log')->where($wh)->save(array('status' => 2));
                }
                $returnData['status'] = $status['code'];
            }
        }
        echo json_encode($returnData);
        exit();
    }
    
    //判断当前区域商品是否定价
    public function isareapriceAction() {
        $type_id = $this->getRequest()->getPost('type_id');
        $commditylist = $this->getRequest()->getPost('commditylist');
        $istype = $this->getRequest()->getPost('istype');

        $returnData['code'] = 0;
        if ($type_id && !empty($commditylist)) {
            $cArr = explode('|', $commditylist);
            
            if ($istype == 'shop') {
                $where['shop_id'] = $type_id;
                $where['type'] = $cArr[3];
                $where['norm_id'] = $cArr[1];
                $arearPrice = LibF::M('commodity_shop')->where($where)->find();
            } else {
                $where['area_id'] = $type_id;
                $where['type'] = $cArr[3];
                $where['norm_id'] = $cArr[1];
                $arearPrice = LibF::M('commodity_area')->where($where)->find();
            }

            $returnData['code'] = 1;
            $returnData['data'] = !empty($arearPrice) ? $arearPrice : '';
        }
        echo json_encode($returnData);
        exit;
    }

    //绑定气罐盘库
    public function stocktakingAction() {
        $tankid = $this->getRequest()->getPost('tankid');
        $height = $this->getRequest()->getPost('height');

        $density = $this->getRequest()->getPost('density');
        $temperature = $this->getRequest()->getPost('temperature');
        if (!empty($tankid)) {
            $data = LibF::M('tank')->where(array('id'=>$tankid))->find();
            if(!empty($data)){
                $param['tankid'] = $tankid;
                $param['tank_name'] = $data['tank_name'];
                $param['height'] = $height;
                if($density)
                    $param['density'] = $density;
                if($temperature)
                    $param['temperature'] = $temperature;
                $param['total'] = $data['stock'];
                $param['admin_id'] = $this->user_info['user_id'];
                $param['admin_name'] = $this->user_info['username'];
                $param['time'] = date('Y-m-d');
                $param['time_created'] = time();

                $param['reserves'] = $this->getGasTotal($height);
                if(!empty($param['reserves']) && $density)
                    $param['volume'] = $param['reserves'] * $density;

                $status = LibF::M('tank_log')->add($param);
                if ($status) {
                    LibF::M('tank_gas')->where(array('tankid' => $tankid))->save(array('total' => $param['reserves'], 'volume' => $param['volume']));
                    $returnData['status'] = $status;
                }
            }
        }
        echo json_encode($returnData); exit;
    }

    //门店确认送气工缴费
    public function qrpayAction() {
        $pay_id = $this->getRequest()->getPost('pay_id');
        $shipper_id = $this->getRequest()->getPost('shipper_id');
        $shop_id = $this->getRequest()->getPost('shop_id');

        $returnData['code'] = 0;
        if ($pay_id && $shipper_id && $shop_id) {
            $data = LibF::M('shipper_payment')->where(array('id' => $pay_id))->find();
            if (!empty($data) && ($shipper_id == $data['shipper_id'])) {

                $where['shipper_id'] = $shipper_id;
                $shipperData = LibF::M('shipper')->where($where)->find();
                $returnData['code'] = LibF::M('shipper')->where($where)->setDec('money', $data['money']);

                $where['id'] = $pay_id;
                $udata['admin_user'] = $this->user_info['username'];
                $udata['status'] = 1;
                $udata['balance'] = $shipperData['money'] - $data['money'];
                LibF::M('shipper_payment')->where($where)->save($udata);

                $swhere['shop_id'] = $shop_id;
                LibF::M('shop')->where($swhere)->setInc('money', $data['money']);
            }
        }
        echo json_encode($returnData);
        exit;
    }
    
    //送气工上缴费用
    public function uppayAction() {
        $shipper_id = $this->getRequest()->getPost('shipper_id');
        $shop_id = $this->getRequest()->getPost('shop_id');
        $money = $this->getRequest()->getPost('money');

        $returnData['code'] = 0;
        if ($shop_id && $shipper_id && $money) {
            $where['shipper_id'] = $shipper_id;
            $shipperData = LibF::M('shipper')->where($where)->find();
            
            $insertData['shipper_id'] = $shipper_id;
            $insertData['money'] = $money;
            $insertData['shop_id'] = $shop_id;
            $insertData['admin_user'] = $this->user_info['username'];
            $insertData['status'] = 1;
            $insertData['balance'] = $shipperData['money'] - $money;
            $insertData['time'] = date('Y-m-d');      
            $insertData['time_created'] = time();

            $status = LibF::M('shipper_payment')->add($insertData);
            if ($status) {
                $returnData['code'] = LibF::M('shipper')->where($where)->setDec('money', $money);

                $swhere['shop_id'] = $shop_id;
                LibF::M('shop')->where($swhere)->setInc('money', $money);
            }
        }
        echo json_encode($returnData);
        exit;
    }
    
    //获取根据液位获取容量
    protected function getGasTotal($height = 0) {
        $h = $height; //液位高度
        $r = 1.2; //罐体半径
        $hi = 0.55; //圆头内高
        $l = 10.06; //圆柱体长度
        $v = 51.5003378098; //罐容量
        //$h=2.4;
        $vol = $v / 2 + 2 * $hi * PI() * ($h - $r) * (1 - (pow(($h - $r), 2) / (3 * pow($r, 3)))) + $l * (($h - $r) * sqrt(2 * $h * $r - $h * $h) + $r * $r * asin(($h - $r) / $r)); //计算公式
        //$vol = sprintf("%01.2f", $vol);
        return $vol;
    }

}
