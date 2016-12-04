<?php 
/**
 * 订单
 */
class OrderController extends \Com\Controller\Common {

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

    public function indexAction() {

        $tempType = $this->getRequest()->getQuery('temptype', 'now'); //now 当前页面 new 新页面

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['order_sn'])) {
                $where['order_sn'] = $param['order_sn'];
                $getParam[] = "order_sn=" . $param['order_sn'];
            }
            if (!empty($param['order_type'])) {
                $where['order_type'] = $param['order_type'];
                $getParam[] = "order_type=" . $param['order_type'];
            }
            if (!empty($param['shipper_id'])) {
                $where['shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }

            if (!empty($param['shipper_name'])) {
                $where['shipper_name'] = $param['shipper_name'];
                $getParam[] = "shipper_name=" . $param['shipper_name'];
            }
            if (!empty($param['username'])) {
                $where['username'] = $param['username'];
                $getParam[] = "username=" . $param['username'];
            }
            if (!empty($param['mobile'])) {
                $where['mobile'] = $param['mobile'];
                $getParam[] = "mobile=" . $param['mobile'];
            }
            
            if (!empty($param['kid'])) {
                $where['kid'] = $param['kid'];
                $getParam[] = "kid=" . $param['kid'];
            }

            if (!empty($param['kehu_type'])) {
                $where['kehu_type'] = $param['kehu_type'];
                $getParam[] = "kehu_type=" . $param['kehu_type'];
            }
            if (!empty($param['status'])) {
                $where['status'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }else{
                $where['status'] = array('gt', 0);
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                //$end_time = date('Y-m-d', strtotime("+1 day", strtotime($param['time_end'])));
                $end_time = $param['time_end'].' 23:59:59';
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($end_time)), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
            if (!empty($param['shipper_id'])) {
                $where['shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }
            $where['status'] = array('gt', 0);
        }

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param, 'order', $where, 'order_id desc');
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $typeManagerModel = new TypemanagerModel();
        $orderData = $typeManagerModel->getData(array(6, 8, 10));

        //获取订单类型
        $orderType = isset($orderData[8]) ? $orderData[8] : '';
        $this->_view->assign('orderType', $orderType);

        //订单状态
        $orderStatus = array(
            -1 => '已关闭', 1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单'
        );
        //$orderStatus = isset($orderData[10]) ? $orderData[10] : '';
        $this->_view->assign('orderStatus', $orderStatus);

        //订单来源
        $ordersource = array(
            1 => '送气工下单', 2 => 'app下单', 3 => '网站下单', 4 => '呼叫中心下单'
        );
        $this->_view->assign('ordersource', $ordersource);

        //客户类型
        $userType = isset($orderData[6]) ? $orderData[6] : '';
        $this->_view->assign('userType', $userType);

        //统计相关数据
        $statusTj = LibF::M('order')->field('count(*) as total,status')->where($where)->group('status')->order('ctime desc')->select();
        $this->_view->assign('statusTj', $statusTj);

        $where['otime'] = array('egt', strtotime(date('Y-m-d')));
        $nowTj = LibF::M('order')->where($where)->count();
        $this->_view->assign('nowTj', $nowTj);

        if ($tempType == 'new') {
            $this->_view->display('order/index_new.phtml');
        }
    }

    public function infoAction() {

        $tempType = $this->getRequest()->getQuery('temptype', 'now'); //now 当前页面 new 新页面

        $param['shop_id'] = $this->getRequest()->getQuery('shop_id');
        $param['order_sn'] = $this->getRequest()->getQuery('ordersn');

        $where['shop_id'] = $param['shop_id'];
        $where['order_sn'] = $param['order_sn'];
        $orderModel = new FillingModel();
        $orderData = $orderModel->getDataInfo('order', $where);
        $orderInfo = array();
        if (!empty($orderData)) {
            $orderInfo = $orderModel->getDataArr('order_info', array('order_sn' => $param['order_sn']), 'id desc');
        }

        $this->_view->assign('orderData', $orderData);
        $this->_view->assign('orderInfo', $orderInfo);
        $this->_view->assign('orderCount', count($orderInfo));
        $this->_view->assign('ordernum', count($orderData));

        $typeManagerModel = new TypemanagerModel();
        $orderData = $typeManagerModel->getData(array(6, 8, 10));

        //获取订单类型
        $orderType = isset($orderData[8]) ? $orderData[8] : '';
        $this->_view->assign('orderType', $orderType);

        //订单状态
        $orderStatus = array(
            1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单'
        );
        //$orderStatus = isset($orderData[10]) ? $orderData[10] : '';
        $this->_view->assign('orderStatus', $orderStatus);

        //订单来源
        $ordersource = array(
            1 => '送气工下单', 2 => 'app下单', 3 => '网站下单', 4 => '呼叫中心下单'
        );
        $this->_view->assign('ordersource', $ordersource);

        //客户类型
        $userType = isset($orderData[6]) ? $orderData[6] : '';
        $this->_view->assign('userType', $userType);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('porductTypeData', $productTypeData);

        //获取当前厂家
        $manufacturerModel = new ManufacturerModel();
        $ManufacturerData = $manufacturerModel->getManufacturerArray();
        $this->_view->assign('manufacturerData', $ManufacturerData);

        $shopObject = ShopModel::getShopArray($this->shop_id);
        $this->_view->assign('shopObject', $shopObject);

        if ($tempType == 'new') {
            $this->_view->display('order/info_new.phtml');
        }
    }

    public function qkinfoAction() {
        $param['shop_id'] = $this->getRequest()->getQuery('shop_id');
        $param['order_sn'] = $this->getRequest()->getQuery('ordersn');

        $where['order_sn'] = $param['order_sn'];
        $orderModel = new FillingModel();
        $orderData = $orderModel->getDataInfo('order', $where);
        if (!empty($orderData)) {
            $qkorderData = $orderModel->getDataArr('order_arrears', $where);
            $this->_view->assign('qkorderData', $qkorderData);
        }

        $this->_view->assign('orderData', $orderData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    public function addAction() {
        $rData['status'] = 0;
        if (IS_POST) {
            
            $data['username'] = $this->getRequest()->getPost('kehu_name');
            $data['mobile'] = $this->getRequest()->getPost('kehu_mobile');
            
            if (!empty($data['mobile'])) {
                $userData = LibF::M('kehu')->where(array('mobile_phone' => $data['mobile']))->find();
                if (!empty($userData)) {
                    $data['kid'] = $userData['kid'];

                    //获取订单号
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $orderSn = substr($orderSn, 0, -2);
                    $data['order_sn'] = 'Dd' . $orderSn . $data['kid'];
                    $data['address'] = $this->getRequest()->getPost('address');
                    $data['comment'] = $this->getRequest()->getPost('comment');
                    $data['kehu_type'] = $this->getRequest()->getPost('kehu_type');
                    $data['shop_id'] = $this->getRequest()->getPost('shop_id');
                    if (!empty($data['shop_id'])) {
                        $shopObject = ShopModel::getShopArray();
                        $data['shop_name'] = $shopObject[$data['shop_id']]['shop_name'];
                        $data['shop_mobile'] = $shopObject[$data['shop_id']]['shop_mobile'];
                    }
                    $orderInfo = array();
                    $commditylist = $this->getRequest()->getPost('commditylist');
                    if (!empty($commditylist)) {
                        $price = 0;
                        foreach ($commditylist as $bottleVal) {
                            if (!empty($bottleVal)) {

                                $bArray = explode('|', $bottleVal);
                                $value['order_sn'] = $data['order_sn'];
                                $value['goods_id'] = $bArray[0];
                                $value['goods_name'] = $bArray[2];
                                $value['goods_num'] = $bArray[5];
                                $value['goods_type'] = $bArray[1];
                                $value['goods_price'] = $bArray[4];
                                $value['goods_kind'] = $bArray[3];
                                $value['pay_money'] = $bArray[4] * $bArray[5];
                                $price += $value['pay_money'];
                                $orderInfo[] = $value;
                            }
                        }
                        $data['money'] = $price;
                    }
                    $data['ctime'] = $data['otime'] = time();

                    $order = new OrderModel();
                    $returnData = $order->add($data);
                    if ($returnData['status'] == 200) {
                        LibF::M('order_info')->uinsertAll($orderInfo);

                        $shipperlist = '';
                        $shipperData = LibF::M('shipper')->where(array('status' => 1))->select();
                        if(!empty($shipperData)){
                            $shipperArr = array();
                            foreach($shipperData as $sVal){
                                $shipperArr[] = $sVal['shipper_id'];
                            }
                            $shipperlist = implode(',', $shipperArr);
                        }

                        $platform = 'android'; // 接受此信息的系统
                        $msg_content = json_encode(array('n_title' => $data['order_sn'], 'n_content' => '订单已经派发', 'n_extras' => array('fromorder' => $data['order_sn'], 'fromer' => $data['username'], 'fromer_name' => $data['mobile'], 'fromer_icon' => '', 'image' => '', 'sound' => '','send_all' =>1)));
                        $smsSend = new SmsDataModel();
                        $returnData = $smsSend->send(16, 3, $shipperlist, 1, $msg_content, $platform);

                        $rData['status'] = 1;
                    }
                }
            }
        }
        echo json_encode($rData);
        exit();
    }

    public function addinfoAction() {
        $ordersn = $this->getRequest()->getQuery('orderid');
        $shop_id = $this->getRequest()->getQuery('shop_id');

        //获取类型
        $bottleType = new BottletypeModel();
        $bottleTypeObject = $bottleType->getBottleTypeArray('', $shop_id);
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        //配件类型
        $productsModel = new ProductspriceModel();
        $productsObject = $productsModel->getProductsArray();
        $this->_view->assign('productsObject', $productsObject);

        if (IS_POST) {
            //更新
            $ordersn = $this->getRequest()->getPost('ordersn');
            $bottle_id = $this->getRequest()->getPost('bottle_id');
            $bottle_num = $this->getRequest()->getPost('bottle_num');

            $product_id = $this->getRequest()->getPost('product_id');
            $product_num = $this->getRequest()->getPost('product_num');

            $orderInfo = array();
            if (!empty($ordersn)) {
                if (!empty($bottle_id)) {
                    foreach ($bottle_id as $key => $value) {
                        $val['shop_id'] = $shop_id;
                        $val['order_sn'] = $ordersn;
                        $val['goods_id'] = $value;
                        $val['goods_name'] = $bottleTypeObject[$value]['bottle_name'];
                        $val['goods_type'] = 1;
                        $val['pay_money'] = $bottleTypeObject[$value]['price'];
                        $val['shop_level'] = 1;
                        $val['goods_num'] = $bottle_num[$key];
                        $orderInfo[] = $val;
                    }
                }
                if (!empty($product_id)) {
                    foreach ($product_id as $k => $value) {
                        $val['shop_id'] = $shop_id;
                        $val['order_sn'] = $ordersn;
                        $val['goods_id'] = $value;
                        $val['goods_name'] = $productsObject[$value]['products_name'];
                        $val['goods_type'] = 2;
                        $val['pay_money'] = $productsObject[$value]['price'];
                        $val['shop_level'] = 1;
                        $val['goods_num'] = $product_num[$key];
                        $orderInfo[] = $val;
                    }
                }
                if (!empty($orderInfo)) {
                    foreach ($orderInfo as $aVal) {
                        LibF::M('order_info')->add($aVal);
                    }
                }
            }
            $this->success('ok', '/order/index');
        }

        $this->_view->assign('ordersn', $ordersn);
    }

    public function shipperlistAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');

        $shop = new ShopModel();
        $shipperData = $shop->getShopShipper($shop_id);

        $this->ajaxReturn(200, 'ok', $shipperData);
    }
    
    /**
     * 关闭订单
     */
    public function delorderAction() {
        $rData['status'] = 0;
        if (IS_POST) {
            $order_sn = $this->getRequest()->getPost('ordersn');
            $shop_id = $this->getRequest()->getPost('ordershop');

            if (!empty($order_sn)) {
                $udate['status'] = -1;

                $where['order_sn'] = $order_sn;
                $where['status'] = array('neq', 4);
                $rData['status'] = LibF::M('order')->where($where)->save($udate);
            }
        }
        echo json_encode($rData);
        exit();
    }

    /**
     * Distribution
     * 派发
     */
    public function distributionAction() {

        $rData['status'] = 0;
        if (IS_POST) {
            $shopObject = ShopModel::getShopArray();
            $order_sn = $this->getRequest()->getPost('order_sn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');

            if (!empty($shipper_id) && $shop_id && !empty($order_sn)) {
                $shop_name = $shopObject[$shop_id]['shop_name'];

                $shipperList = explode('|', $shipper_id);

                $shiper['shop_id'] = $shop_id;
                $shiper['shop_name'] = $shop_name;
                $shiper['shipper_id'] = isset($shipperList[0]) ? $shipperList[0] : 0;
                $shiper['shipper_name'] = isset($shipperList[1]) ? $shipperList[1] : '';
                $shiper['shipper_mobile'] = isset($shipperList[2]) ? $shipperList[2] : '';
                $shiper['status'] = 2;

                $rData['status'] = LibF::M('order')->where(array('order_sn' => $order_sn))->save($shiper);

                $platform = 'android'; // 接受此信息的系统
                $msg_content = json_encode(array('n_builder_id' => $shiper['shipper_id'], 'n_title' => $order_sn, 'n_content' => '订单已经派发', 'n_extras' => array('fromorder' => $order_sn, 'fromer' => $shop_name, 'fromer_name' => $shop_name, 'fromer_icon' => '', 'image' => '', 'sound' => '', 'send_all' => 0)));
                $smsSend = new SmsDataModel();
                $smsSend->send(16, 3, $shiper['shipper_id'], 1, $msg_content, $platform);
            }
        }
        echo json_encode($rData);
        exit();
    }

    /**
     * 销售收入
     */
    public function salesrevenueAction() {
        $tempType = $this->getRequest()->getQuery('temptype', 'now'); //now 当前页面 new 新页面

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();

        $where = $tjWhere = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = $tjWhere['rq_order.ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');

                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (!empty($param['kehu_type'])) {
                $where['kehu_type'] = $tjWhere['rq_order.kehu_type'] = $param['kehu_type'];
                $getParam[] = "kehu_type=" . $param['kehu_type'];
            }
            if (empty($param['shop_id']) && $this->shop_id) {
                $where['shop_id'] = $tjWhere['rq_order.shop_id'] = $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $tjWhere['rq_order.shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['username'])) {
                $where['username'] = $tjWhere['rq_order.shop_id'] = $param['username'];
                $getParam[] = "username=" . $param['username'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist', implode('&', $getParam));
        } else {
            if ($this->shop_id){
                $where['shop_id'] = $tjWhere['rq_order.shop_id'] = $this->shop_id;
            }
        }
        $where['status'] = $tjWhere['rq_order.status'] = 4;
        $where['ispayment'] = $tjWhere['rq_order.ispayment'] = 1;

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }
        $this->_view->assign('shop_id', $this->shop_id);
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        //获取门店
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        //获取客户类型
        $typeManagerModel = new TypemanagerModel();
        $kehuType = $typeManagerModel->getData(6);
        $this->_view->assign('kehuType', $kehuType);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData',$bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        
        if ($tempType == 'new') {
            $orderModel = new FillingModel();
            $data = $orderModel->getDataList($param, 'order', $where, 'order_id desc');
            $this->_view->assign($data);
            
            $staticsTotal = LibF::M('order')->field('sum(pay_money) as pay_money,sum(deposit) as deposit')->where($where)->find(); //统计订单销售额
            $this->_view->assign($staticsTotal);

            $orderStaticModel = new Model('order');
            $filed = " rq_order_info.goods_kind,sum(rq_order_info.pay_money) as total_money,count(rq_order_info.goods_kind) as total ";
            $leftJoin = " LEFT JOIN rq_order_info ON rq_order.order_sn = rq_order_info.order_sn ";

            $tjWhere['rq_order_info.goods_type'] = 1;
            $staticsBottleType = $orderStaticModel->join($leftJoin)->field($filed)->where($tjWhere)->group('rq_order_info.goods_kind')->select();
            
            $totalAllData = array('total' => 0,'total_num' => 0);
            if(!empty($staticsBottleType)){
                foreach($staticsBottleType as $tVal){
                    $totalAllData['total'] += $tVal['total_money'];
                    $totalAllData['total_num'] += $tVal['total'];
                }
            }
            $this->_view->assign('staticsBottleType',$staticsBottleType);
            $this->_view->assign('totalAllData',$totalAllData);
            
            $orderStatus = array(1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单');  //订单状态
            $this->_view->assign('orderStatus', $orderStatus);

            $ordersource = array(1 => '送气工下单', 2 => 'app下单', 3 => '网站下单', 4 => '呼叫中心下单'); //订单来源
            $this->_view->assign('ordersource', $ordersource);

            $this->_view->display('order/salesrevenue_new.phtml');
        } else {
            $orderModel = new OrderModel();
            $data = $orderModel->qk_orderlist($param, $where);  //按照用户统计
            $this->_view->assign($data);

            //统计相关数据
            $totalMoney = 0;
            
            //$orderStaticModel = new Model('order_info');
            //$filed = " rq_order_info.goods_type,rq_order_info.goods_kind,sum(rq_order_info.pay_money) as total,sum(rq_order_info.goods_num) as num ";
            //$leftJoin = " LEFT JOIN rq_order ON rq_order_info.order_sn = rq_order.order_sn ";

            //$kWhere['rq_order.status'] = 4;
            //$kWhere['rq_order_info.goods_type'] = 1;
            //$statusTj = $orderStaticModel->join($leftJoin)->field($filed)->where($kWhere)->group('rq_order_info.goods_kind')->order('rq_order_info.goods_kind asc,total desc')->select();
            
            $statusTj = LibF::M('order_info')->field('goods_id,goods_type,goods_name,goods_kind,sum(pay_money) as total,sum(goods_num) as num')->where(array('goods_type' => 1,'goods_kind'=>array('gt',0)))->group('goods_kind')->order('goods_kind asc,total desc')->select();
            if (!empty($statusTj)) {
                foreach ($statusTj as &$value) {
                    if ($value['goods_type'] == 1) {
                        //$value['goods_name'] = $value['goods_name'] . '-' . $bottleTypeData[$value['goods_kind']]['bottle_name'];
                        $value['goods_name'] = $bottleTypeData[$value['goods_kind']]['bottle_name'];
                    } else {
                       // $value['goods_name'] = $value['goods_name'] . '-' . $productTypeData[$value['goods_kind']]['name'];
                        $value['goods_name'] = $productTypeData[$value['goods_kind']]['name'];
                    }
                    $totalMoney += $value['total'];
                }
            }
            $this->_view->assign('totalMoney', $totalMoney);
            $this->_view->assign('statusTj', $statusTj);
            $this->_view->assign('statusTotal', count($statusTj));
        }
    }
    
    /**
     * 折旧订单展示
     */
    public function depreciationAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['shipper_name'])) {
                $where['shipper_name'] = $param['shipper_name'];
                $getParam[] = "shipper_name=" . $param['shipper_name'];
            }
            if (!empty($param['mobile'])) {
                $where['mobile'] = $param['mobile'];
                $getParam[] = "mobile=" . $param['mobile'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $end_time = date('Y-m-d', strtotime("+1 day", strtotime($param['time_end'])));
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($end_time)), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param, 'order_depreciation', $where, 'id desc');
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $where['time_created'] = array('egt', strtotime(date('Y-m-d')));
        $nowTj = LibF::M('order_depreciation')->where($where)->count();
        $this->_view->assign('nowTj', $nowTj);
    }
    
    /**
     * 根据条件导出订单相关数据
     */
    public function importorderAction() {
        $paramData = $this->getRequest()->getQuery();

        if (isset($paramData['import_type']) && ($paramData['import_type'] == 'order')) {
            $data = $this->getOrderList($paramData, $this->shop_id);
            if (!empty($data)) {
                
                $shopObject = ShopModel::getShopArray();
                $kehuObject = array(1=>'居民',2=>'商业',3=>'工业');
                $orderStatus = array(1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单');
                $ordersource = array(1 => '送气工下单', 2 => 'app下单', 3 => '网站下单', 4 => '呼叫中心下单');
                
                $objPHPExcel = new PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '订单数据');
                $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:W1');
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A2', '订单号')
                        ->setCellValue('B2', '用户姓名')
                        ->setCellValue('C2', '用户联系方式')
                        ->setCellValue('D2', '用户类型')
                        ->setCellValue('E2', '门店')
                        ->setCellValue('F2', '实际收款额')
                        ->setCellValue('G2', '订单价格')
                        ->setCellValue('H2', '详情信息')
                        ->setCellValue('I2', '数量')
                        ->setCellValue('J2', '押金')
                        ->setCellValue('K2', '是否欠款')
                        ->setCellValue('L2', '余气')
                        ->setCellValue('M2', '折旧')
                        ->setCellValue('N2', '残液')
                        ->setCellValue('O2', '用户价格优惠')
                        ->setCellValue('P2', '送气工价格优惠')
                        ->setCellValue('Q2', '结算方式')
                        ->setCellValue('R2', '重瓶信息')
                        ->setCellValue('S2', '空瓶信息')
                        ->setCellValue('T2', '订单来源')
                        ->setCellValue('U2', '订单时间')
                        ->setCellValue('V2', '订单状态')
                        ->setCellValue('W2', '送气工')
                        ->setCellValue('X2', '地址');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                $objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('A2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
                $objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
                $objPHPExcel->getActiveSheet()->getStyle('C2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('C2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getStyle('D2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('D2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getStyle('E2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('E2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
                $objPHPExcel->getActiveSheet()->getStyle('F2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('F2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getStyle('G2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('G2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(26);
                $objPHPExcel->getActiveSheet()->getStyle('H2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('H2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getStyle('I2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('I2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('J2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('J2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('K2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('K2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('L2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('L2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('M2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('M2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('N2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('N2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('O2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('O2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('P2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('P2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(16);
                $objPHPExcel->getActiveSheet()->getStyle('Q2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('Q2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('R2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('R2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getStyle('S2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('S2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(18);
                $objPHPExcel->getActiveSheet()->getStyle('T2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('T2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(40);
                $objPHPExcel->getActiveSheet()->getStyle('U2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('U2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(26);
                $objPHPExcel->getActiveSheet()->getStyle('V2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('V2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle('W2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('W2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(50);
                $objPHPExcel->getActiveSheet()->getStyle('X2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle('X2')->getFill()->getStartColor()->setARGB('FFAFEEEE');
                //@ini_set('memory_limit','60M');
                try {
                    $i = 3;
                    foreach ($data as $key => $orderVal) {
                        $orderInfoNum = count($orderVal['order_info']);
                        $value = $orderVal['order'];

                        $shop_name = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                        $kehu_type = isset($kehuObject[$value['kehu_type']]) ? $kehuObject[$value['kehu_type']] : '';
                        $order_status = isset($orderStatus[$value['status']]) ? $orderStatus[$value['status']] : '';
                        $order_qk = (($value['is_settlement'] == 2) && ($value['status'] == 4)) ? '欠款订单' : '正常结算';
                        $order_type = isset($ordersource[$value['order_type']]) ? $ordersource[$value['order_type']] : '';
                        $order_paytype = ($value['order_paytype'] == 0) ? '现金结算' : '网上结算';
                        $zbottle = !empty($value['zbottle']) ? implode('|', json_decode($value['zbottle'], TRUE)) : '';
                        $kbottle = !empty($value['kbottle']) ? implode('|', json_decode($value['kbottle'], TRUE)) : '';

                        $s = $i;
                        $time = date('Y-m-d H:i:s', $value['ctime']);
                        foreach ($orderVal['order_info'] as $val) {
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A' . $i, $value['order_sn'])
                                    ->setCellValue('B' . $i, $value['username'])
                                    ->setCellValue('C' . $i, $value['mobile'])
                                    ->setCellValue('D' . $i, $kehu_type)
                                    ->setCellValue('E' . $i, $shop_name)
                                    ->setCellValue('F' . $i, $value['pay_money'])
                                    ->setCellValue('G' . $i, $value['money'])
                                    ->setCellValue('H' . $i, $val['goods_name'])
                                    ->setCellValue('I' . $i, $val['goods_num'])
                                    ->setCellValue('J' . $i, $value['deposit'])
                                    ->setCellValue('K' . $i, $order_qk)
                                    ->setCellValue('L' . $i, $value['residual_gas'])
                                    ->setCellValue('M' . $i, $value['depreciation'])
                                    ->setCellValue('N' . $i, $value['raffinat'])
                                    ->setCellValue('O' . $i, $value['discountmoney'])
                                    ->setCellValue('P' . $i, $value['shipper_money'])
                                    ->setCellValue('Q' . $i, $order_paytype)
                                    ->setCellValue('R' . $i, $zbottle)
                                    ->setCellValue('S' . $i, $kbottle)
                                    ->setCellValue('T' . $i, $order_type)
                                    ->setCellValue('U' . $i, $time)
                                    ->setCellValue('V' . $i, $order_status)
                                    ->setCellValue('W' . $i, $value['shipper_name'])
                                    ->setCellValue('X' . $i, $value['address']);

                            $i++;
                        }
                        $e = $s + ($orderInfoNum - 1);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $s . ':A' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B' . $s . ':B' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('C' . $s . ':C' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('D' . $s . ':D' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('E' . $s . ':E' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('F' . $s . ':F' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G' . $s . ':G' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('J' . $s . ':J' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K' . $s . ':K' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L' . $s . ':L' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('M' . $s . ':M' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('N' . $s . ':N' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('O' . $s . ':O' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('P' . $s . ':P' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('Q' . $s . ':Q' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R' . $s . ':R' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('S' . $s . ':S' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T' . $s . ':T' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('U' . $s . ':U' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('V' . $s . ':V' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('W' . $s . ':W' . $e);
                        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('X' . $s . ':X' . $e);
                    }
                    $objPHPExcel->setActiveSheetIndex(0);
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }

                $file_nams = "订单数据.xls";
                if (preg_match("/MSIE/", $_SERVER["HTTP_USER_AGENT"])) {
                    $file_nams = urlencode($file_nams);
                    $file_nams = str_replace("+", "%20", $file_nams); // 替换空格
                }
                header('Content-Type: application/vnd.ms-excel;charset=utf-8');
                header("Content-Disposition: attachment;filename=" . $file_nams);
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
            }
        }

        $paramDataList = '';
        if (!empty($paramData)) {
            $pArr = array();
            foreach ($paramData as $pKey => $pVal) {
                $pArr[] = $pKey . "=" . $pVal;
            }
            $paramDataList = !empty($pArr) ? implode('&', $pArr) : '';
        }
        $this->_view->assign('paramData', $paramDataList);
    }

    protected function getOrderList($param = array(),$shop_id) {
        $where = array();
        if (!empty($param)) {
            if ($shop_id) {
                $where['rq_order.shop_id'] = $shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['rq_order.shop_id'] = $param['shop_id'];
            }
            if (!empty($param['order_sn'])) {
                $where['rq_order.order_sn'] = $param['order_sn'];
            }
            if (!empty($param['order_type'])) {
                $where['rq_order.order_type'] = $param['order_type'];
            }
            if (!empty($param['shipper_id'])) {
                $where['rq_order.shipper_id'] = $param['shipper_id'];
            }

            if (!empty($param['shipper_name'])) {
                $where['rq_order.shipper_name'] = $param['shipper_name'];
            }
            if (!empty($param['username'])) {
                $where['rq_order.username'] = $param['username'];
            }
            if (!empty($param['mobile'])) {
                $where['rq_order.mobile'] = $param['mobile'];
            }
            if (!empty($param['kid'])) {
                $where['rq_order.kid'] = $param['kid'];
            }

            if (!empty($param['kehu_type'])) {
                $where['rq_order.kehu_type'] = $param['kehu_type'];
            }
            if (!empty($param['status'])) {
                $where['rq_order.status'] = $param['status'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $end_time = $param['time_end'] . ' 23:59:59';
                $where['rq_order.ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($end_time)), 'AND');
            }
        } else {
            if (!empty($param['shop_id'])) {
                $where['rq_order.shop_id'] = $param['shop_id'];
            }
        }
        
        $data = array();
        
        $orderModel = new Model('order');
        $leftJoin = "LEFT JOIN rq_order_info ON rq_order.order_sn = rq_order_info.order_sn";
        $fileName = "rq_order.*,rq_order_info.goods_kind,rq_order_info.goods_type,rq_order_info.goods_name,rq_order_info.goods_num";
        $orderData = $orderModel->join($leftJoin)->field($fileName)->where($where)->order('rq_order.order_id desc')->select();
        if (!empty($orderData)) {
            //获取当前钢瓶规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

            //获取配件规格
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();
            foreach($orderData as &$value){
                $val['goods_kind'] = $value['goods_kind'];
                if ($value['goods_type'] == 1) {
                    $val['goods_name'] = '液化气-' . $bottleTypeData[$value['goods_kind']]['bottle_name'];
                } else {
                    $val['goods_name'] = $value['goods_name'] . '-' . $productTypeData[$value['goods_kind']]['name'];
                }
                $val['goods_num'] = $value['goods_num'];
                $data[$value['order_sn']]['order_info'][] = $val;
                $data[$value['order_sn']]['order'] = $value;
            }
        }
        return $data;
    }

}