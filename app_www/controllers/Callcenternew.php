<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CallcenternewController extends \Com\Controller\Common {

    public function loginAction() {
        if (IS_POST) {
            $username = $this->getRequest()->getPost('username');
            $password = $this->getRequest()->getPost('password');

            $username = htmlspecialchars($username);
            $password = MD5($password);

            //包含数据库连接文件
            $conn = mysql_connect("123.57.159.30", "justcall", "jcdbup0932") or die("数据库链接错误" . mysql_error());
            mysql_select_db("justcall_db", $conn) or die("数据库访问错误" . mysql_error());
            mysql_query("set names gb2312");

            //检测用户名及密码是否正确
            $check_query = mysql_query("select user_no,device from org_user where user_name='$username' and password='$password' limit 1");
            if ($result = mysql_fetch_row($check_query)) {
                
            }

            //$this->_view->display('callcenternew/index');
            $this->success('ok', '/callcenternew/index');
        }
    }

    public function indexAction() {

        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        //报修类型
        $repair = new RepairModel();
        $repairData = $repair->getRepairArray();
        $this->_view->assign('repairData', $repairData);
    }

    //获取用户信息
    public function ajaxuinfoAction() {
        $mobile_phone = $this->getRequest()->getPost('mobile_phone');

        $returnData = array('code' => 0);
        if (!empty($mobile_phone)) {
            $condition['mobile_phone'] = $mobile_phone;
            $condition['mobile_list'] = array('like', '%' . $mobile_phone . '%');
            $condition['_logic'] = 'OR';
            $data = LibF::M('kehu')->where($condition)->find();
            if (!empty($data)) {
                $returnData['code'] = 1;
                $returnData['data'] = $data;

                //获取钢瓶类型，配件类型
                //$bottleType = LibF::M('bottle_price')->where(array('shop_id' =>$data['shop_id']))->select();
                //$pjType = LibF::M('products_price')->where(array('shop_id' => $data['shop_id']))->select();
                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $shopPriceObject = array();
                if ($data['shop_id'] > 0) {
                    //获取门店下送气工
                    $shipperModel = new ShipperModel();
                    $returnData['shipper'] = $shipperModel->getShipperArray('', $data['shop_id']);

                    $shopWhere['type'] = array('in', array(1, 2));
                    $shopWhere['shop_id'] = $data['shop_id'];
                    $shopData = LibF::M('commodity_shop')->where($shopWhere)->order('type ASC')->select();
                    if (!empty($shopData)) {
                        foreach ($shopData as $shopValue) {
                            $shopPriceObject[$shopValue['norm_id']] = $shopValue;
                        }
                    }
                }

                $commdify = LibF::M('commodity')->where(array('status' => 0, 'type' => 1))->select();
                if (!empty($commdify)) {
                    foreach ($commdify as &$value) {
                        if ($value['type'] == 1) {
                            $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                        } else {
                            $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                        }

                        if ($data['ktype'] == 2) {
                            $value['retail_price'] = $value['retail_price_business'] ? $value['retail_price_business'] : $value['retail_price'];  //零售价
                            if ($data['shop_id']) {
                                $value['retail_price'] = (isset($shopPriceObject[$value['norm_id']]) && $shopPriceObject[$value['norm_id']]['retail_price_business']) ? $shopPriceObject[$value['norm_id']]['retail_price_business'] : $value['retail_price'];
                            }
                        } else {
                            $value['retail_price'] = $value['retail_price'] ? $value['retail_price'] : 0;  //零售价
                            if ($data['shop_id']) {
                                $value['retail_price'] = (isset($shopPriceObject[$value['norm_id']]) && $shopPriceObject[$value['norm_id']]['retail_price']) ? $shopPriceObject[$value['norm_id']]['retail_price'] : $value['retail_price'];
                            }
                        }
                    }
                }
                $returnData['commdify'] = $commdify;

                //获取当前用户优惠券
                $kehuModle = new KehuModel();
                $yhData = $kehuModle->getUserPromotionsData($data['kid']);
                $returnData['promotions'] = $yhData;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //用户操作
    public function ajaxuserAction() {
        $data['user_name'] = $this->getRequest()->getPost('user_name');
        $data['mobile_phone'] = $this->getRequest()->getPost('mobile_phone');
        $data['shop_id'] = $this->getRequest()->getPost('shop_id');
        $data['ktype'] = $this->getRequest()->getPost('ktype');
        $data['paytype'] = $this->getRequest()->getPost('paytype');
        $data['address'] = $this->getRequest()->getPost('address');
        $data['comment'] = $this->getRequest()->getPost('comment');
        $data['comment'] = !empty($data['comment']) ? $data['comment'] : '';
        $data['gender'] = $this->getRequest()->getPost('sex_id', 0);
        $data['sheng'] = $this->getRequest()->getPost('sheng');
        $data['shi'] = $this->getRequest()->getPost('shi');
        $data['qu'] = $this->getRequest()->getPost('qu');
        $data['cun'] = $this->getRequest()->getPost('cun');

        $returnData = array('code' => 0);
        if (!empty($data)) {

            $kid = $this->getRequest()->getPost('kid');
            if ($kid > 0) {
                $data['utime'] = time();
                $status = LibF::M('kehu')->where(array('kid' => $kid))->save($data);
            } else {
                $status = LibF::M('kehu')->where(array('mobile_phone' => $data['mobile_phone']))->find($data);
                if ($status) {
                    $status = $status['kid'];
                } else {
                    $app = new App();
                    $orderSn = $app->build_order_no();

                    $kehuModel = new KehuModel();
                    $kpassword = $kehuModel->encryptUserPwd($data['mobile_phone']);

                    $data['kehu_sn'] = 'Kh' . $orderSn;
                    $data['password'] = $kpassword;
                    $data['ctime'] = time();
                    $status = LibF::M('kehu')->add($data);
                }
            }

            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

            //获取配件规格
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();

            $commdify = LibF::M('commodity')->where(array('status' => 0, 'type' => 1))->select();
            if (!empty($commdify)) {
                foreach ($commdify as &$value) {
                    if ($value['type'] == 1) {
                        $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                    } else {
                        $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                    }
                }
            }
            $returnData['commdify'] = $commdify;
            if ($data['shop_id'] > 0) {
                //获取门店下送气工
                $shipperModel = new ShipperModel();
                $returnData['shipper'] = $shipperModel->getShipperArray('', $data['shop_id']);
            }

            $returnData['code'] = $status;
        }
        echo json_encode($returnData);
        exit;
    }

    //创建订单
    public function ajaxorderAction() {
        $returnData = array('code' => 0);

        $data['comment'] = $this->getRequest()->getPost('order_comment');
        $bottle = $this->getRequest()->getPost('order_bottle');
        $order_type = $this->getRequest()->getPost('order_type');
        $data['kid'] = $this->getRequest()->getPost('order_kid');
        $shop_id = $this->getRequest()->getPost('order_shop_id');
        if ($shop_id > 0) {
            $data['shop_id'] = $shop_id;
            $shopData = LibF::M('shop')->where(array('shop_id' => $data['shop_id']))->find();
            if (!empty($shopData['shop_name']))
                $data['shop_name'] = $shopData['shop_name'];
            if (!empty($shopData['shop_mobile']))
                $data['shop_mobile'] = $shopData['shop_mobile'];
        }
        $order_shipper_id = $this->getRequest()->getPost('order_shipper_id');

        if (!empty($bottle) && !empty($data['kid'])) {
            $orderInfo = array();
            $price = 0;
            $deposit = 0;
            $bottleArray = explode('*', $bottle);
            if (!empty($bottleArray)) {
                foreach ($bottleArray as $bottleVal) {
                    if (!empty($bottleVal)) {
                        $bArray = explode('|', $bottleVal);
                        $value['goods_id'] = $bArray[0];
                        $value['goods_name'] = $bArray[5];
                        $value['goods_num'] = $bArray[4];
                        $value['goods_kind'] = $bArray[2];
                        $value['goods_type'] = $bArray[3];
                        $value['goods_price'] = $bArray[1];
                        $value['pay_money'] = $bArray[4] * $bArray[1];
                        $price += $value['pay_money'];
                        $orderInfo[] = $value;
                    }
                }
            }
            $userData = LibF::M('kehu')->where(array('kid' => $data['kid']))->find();
            if (!empty($userData)) {
                $data['mobile'] = $userData['mobile_phone'];
                if ($userData['sheng'])
                    $data['sheng'] = $userData['sheng'];
                if ($userData['shi'])
                    $data['shi'] = $userData['shi'];
                if ($userData['qu'])
                    $data['qu'] = $userData['qu'];
                if ($userData['cun'])
                    $data['cun'] = $userData['cun'];
                if (!empty($userData['address']))
                    $data['address'] = $userData['address'];
                if (!empty($userData['user_name']))
                    $data['username'] = $userData['user_name'];
                if (!empty($userData['ktype']))
                    $data['kehu_type'] = $userData['ktype'];
            }
            $app = new App();
            $orderSn = $app->build_order_no();
            $orderSn = substr($orderSn, 0, -2);
            $data['order_sn'] = 'Dd' . $orderSn . $data['kid'];
            $data['ctime'] = $data['otime'] = time();
            $data['ordertype'] = 4;
            $data['deposit'] = $deposit;

            //是否有优惠券
            $yhData = array();
            $order_promotions = $this->getRequest()->getPost('order_promotions');
            if (!empty($order_promotions)) {
                $yhmoney = 0;
                $promotinsArr = explode(',', $order_promotions);
                if (!empty($promotinsArr)) {
                    foreach ($promotinsArr as $pVal) {
                        $pArr = explode('|', $pVal);
                        if (isset($pArr[1]) && $pArr[1] > 0) {
                            $yhmoney += $pArr[1];
                            $yhData[] = $pArr[0];
                        }
                    }
                    $price -= $yhmoney;
                    $data['youhui_data'] = json_encode($yhData);
                    $data['is_youhui'] = 1;
                    $data['is_yh_money'] = $yhmoney;
                }
            }
            $data['money'] = $price;

            //判断当前是否选择送气工
            $send_shipper_id = 0;
            if (!empty($order_shipper_id)) {
                $shipperArr = explode('|', $order_shipper_id);
                if (isset($shipperArr[0]) && !empty($shipperArr[0])) {
                    $data['shipper_id'] = $send_shipper_id = $shipperArr[0];
                    $data['status'] = 2;
                }
                if (isset($shipperArr[1]) && !empty($shipperArr[1])) {
                    $data['shipper_name'] = $shipperArr[1];
                }
                if (isset($shipperArr[2]) && !empty($shipperArr[2])) {
                    $data['shipper_mobile'] = $shipperArr[2];
                }
            }
            $status = LibF::M('order')->add($data);
            if ($status) {
                if (!empty($send_shipper_id)) {
                    //$platform = 'android'; // 接受此信息的系统
                    //$msg_content = json_encode(array('n_builder_id' => $send_shipper_id, 'n_title' => $data['order_sn'], 'n_content' => '订单已经派发', 'n_extras' => array('fromer' => $data['shop_name'], 'fromer_name' => $data['shop_name'], 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                    //$smsSend = new SmsDataModel();
                    //$smsSend->send(16, 3, $send_shipper_id, 1, $msg_content, $platform);
                }

                //$smsdataModel = new SmsDataModel();
                //$smsdataModel->sendsms($data['address'], $data['order_sn'], $data['mobile']);
                foreach ($orderInfo as $val) {
                    $val['shop_id'] = $data['shop_id'];
                    $val['order_sn'] = $data['order_sn'];
                    LibF::M('order_info')->add($val);
                }
                $returnData['code'] = $status;

                //更新用户订单数量   
                LibF::M('kehu')->where(array('kid' => $data['kid']))->setInc('buy_time');

                //更新优惠券
                if (!empty($yhData)) {
                    $uData['status'] = 1;
                    $uWhere['id'] = array('in', $yhData);
                    LibF::M('promotions_user')->where($uWhere)->save($uData);
                }
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //查询订单
    public function ajaxxorderAction() {
        $cx_user_name = $this->getRequest()->getPost('cx_user_name');
        $cx_mobile_phone = $this->getRequest()->getPost('cx_mobile_phone');
        $cx_order_sn = $this->getRequest()->getPost('cx_order_sn');

        $returnData = array('code' => 0);
        if (!empty($cx_mobile_phone) || !empty($cx_user_name) || !empty($cx_order_sn)) {
            if (!empty($cx_order_sn)) {
                $where['rq_order.order_sn'] = $cx_order_sn;
            }
            if (!empty($cx_user_name)) {
                $where['rq_order.username'] = $cx_user_name;
            }
            if (!empty($cx_mobile_phone)) {
                $where['rq_order.mobile'] = $cx_mobile_phone;
            }
            //$data = LibF::M('order')->where($where)->limit(5)->order('order_id desc')->select();
            $dataModel = new Model('order');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid')->field('rq_order.*,rq_kehu.card_sn')->where($where)->order('rq_order.order_id desc')->limit(5)->select();
            if (!empty($data)) {
                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                //订单状态
                $orderStatus = array(
                    1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单'
                );
                $orderModel = new OrderModel();
                foreach ($data as $key => &$value) {
                    $p['ordersn'] = $value['order_sn'];
                    $dataInfo = $orderModel->getUserOrderInfoList($p);
                    $dataText = '';
                    if (!empty($dataInfo)) {
                        foreach ($dataInfo as $val) {
                            if ($val['goods_type'] == 1) {
                                $dataText .= $bottleTypeData[$val['goods_kind']]['bottle_name'];
                            } else {
                                $dataText .= $productTypeData[$val['goods_kind']]['name'];
                            }
                            $dataText .= ' ' . $val['goods_num'] . '个;';
                        }
                    }
                    $value['datatext'] = $dataText;
                    $value['statusMsg'] = $orderStatus[$value['status']];
                    $value['timeNow'] = date("Y-m-d H:i", $value['otime']);
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //查询订单（新）
    public function ajaxxordernAction() {
        $cx_user_name = $this->getRequest()->getPost('cx_user_name');
        $cx_mobile_phone = $this->getRequest()->getPost('cx_mobile_phone');
        $cx_order_sn = $this->getRequest()->getPost('cx_order_sn');
        $status = $this->getRequest()->getPost('status');
        if ($status != 'x' && $status != '')
            $where['rq_order.status'] = $status;
        $start = $this->getRequest()->getPost('start');
        $end = $this->getRequest()->getPost('end');
        $msg = 0;
        if ($start != '' && $end != '') {
            $msg = 1;
            $start = strtotime($start);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
            $where['rq_order.ctime'] = array(array('egt', $start), array('elt', $end), 'AND');
        }

        $returnData = array('code' => 0);
        if (!empty($cx_mobile_phone) || !empty($cx_user_name) || !empty($cx_order_sn) || $msg == 1) {
            if (!empty($cx_order_sn)) {
                $where['rq_order.order_sn'] = $cx_order_sn;
            }
            if (!empty($cx_user_name)) {
                $where['rq_order.username'] = $cx_user_name;
            }
            if (!empty($cx_mobile_phone)) {
                $where['rq_order.mobile'] = $cx_mobile_phone;
            }
            $dataModel = new Model('order');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid')->field('rq_order.*,rq_kehu.card_sn')->where($where)->order('rq_order.order_id desc')->select();
            if (!empty($data)) {
                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                //订单状态
                $orderStatus = array(
                    1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单'
                );
                $orderModel = new OrderModel();
                foreach ($data as $key => &$value) {
                    $p['ordersn'] = $value['order_sn'];
                    $dataInfo = $orderModel->getUserOrderInfoList($p);
                    $dataText = array();
                    $total = 0;
                    if (!empty($dataInfo)) {
                        $com = array();
                        foreach ($dataInfo as $val) {
                            if ($val['goods_type'] == 1) {
                                $com['name'] = $val['goods_name'];
                                $com['type'] = $bottleTypeData[$val['goods_kind']]['bottle_name'];
                                $com['num'] = $val['goods_num'];
                            } else {
                                $com['name'] = $val['goods_name'];
                                $com['type'] = empty($productTypeData[$val['goods_kind']]['name']) ? '通用' : $productTypeData[$val['goods_kind']]['name'];
                                $com['num'] = $val['goods_num'];
                            }
                            $total++;
                            $dataText[] = $com;
                        }
                    }
                    $yqStr = 0;
                    $yhidsData = array();
                    if (!empty($value['youhui_data'])) {
                        $yh_data = json_decode($value['youhui_data'], true);
                        $yhids = LibF::M('promotions_user')->select();
                        if (!empty($yhids)) {
                            foreach ($yhids as $v) {
                                if ($v['type'] == 2)
                                    $yhidsData[] = $v['id'];
                            }
                            if (!empty($yhidsData))
                                foreach ($yh_data as $vv) {
                                    if (in_array($vv, $yhidsData))
                                        $yqStr++;
                                }
                            if ($yqStr > 0)
                                $yqStr .= '张';
                        }
                    }
                    $value['yqStr'] = $yqStr;
                    $value['datatext'] = $dataText;
                    $value['total'] = $total;
                    $value['statusMsg'] = $orderStatus[$value['status']];
                    $value['timeNow'] = date("Y-m-d H:i", $value['otime']);
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //订单跟踪-- 取消订单
    public function ajaxcancelorderAction() {
        $order_id = $this->getRequest()->getPost('order_id');
        $youhui_data = $this->getRequest()->getPost('youhui_data');
        $returnData = array('code' => 0);
        $data = array();
        if (!empty($order_id)) {
            $data['order_id'] = $order_id;
            $data['status'] = 5;
            $status = LibF::M('order')->save($data);
            if ($status && $youhui_data != '') {
                $youhuiArr = explode(',', $youhui_data);
                if (!empty($youhuiArr))
                    foreach ($youhuiArr as $v) {
                        $yhData = array();
                        $yhData['status'] = 0;
                        $yhData['id'] = (int) $v;
                        $status = LibF::M('promotions_user')->save($yhData);
                    }
            }
            if ($status)
                $returnData['code'] = 1;
        }
        echo json_encode($returnData);
        exit;
    }

    //添加报修
    public function ajaxbaoxiuAction() {
        $repair = $this->getRequest()->getPost('repair');
        $repair_comment = $this->getRequest()->getPost('repair_comment');
        $kid = $this->getRequest()->getPost('kid');

        $send_shipper_id = 0;
        $repair_shipper_id = $this->getRequest()->getPost('repair_shipper_id');

        $returnData = array('code' => 0);
        if (!empty($repair_comment) && !empty($kid)) {
            $data = LibF::M('kehu')->where(array('kid' => $kid))->find();
            if (!empty($data)) {
                $app = new App();
                $orderSn = $app->build_order_no();
                $addData['encode_id'] = 'bx' . $orderSn;
                $addData['kid'] = $kid;
                $addData['baoxiu_wt'] = json_encode($repair);
                $addData['kname'] = $data['user_name'];
                $addData['shop_id'] = $data['shop_id'];
                $addData['comment'] = $repair_comment;
                $addData['ctime'] = time();

                $shipper_name = '';
                if (!empty($repair_shipper_id)) {
                    $shipperArr = explode('|', $repair_shipper_id);
                    if (isset($shipperArr[0]) && !empty($shipperArr[0])) {
                        $addData['shipper_id'] = $send_shipper_id = $shipperArr[0];
                        $addData['status'] = 1;
                    }
                    if (isset($shipperArr[1]) && !empty($shipperArr[1])) {
                        $addData['shipper_name'] = $shipper_name = $shipperArr[1];
                    }
                    if (isset($shipperArr[2]) && !empty($shipperArr[2])) {
                        $addData['shipper_mobile'] = $shipperArr[2];
                    }
                }

                $status = LibF::M('baoxiu')->add($addData);
                if ($status) {
                    $returnData['code'] = 1;

                    if ($send_shipper_id > 0) {
                        $platform = 'android'; // 接受此信息的系统
                        $msg_content = json_encode(array('n_builder_id' => $send_shipper_id, 'n_title' => $addData['encode_id'], 'n_content' => '报修订单已经派发', 'n_extras' => array('fromer' => $shipper_name, 'fromer_name' => $shipper_name, 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                        $smsSend = new SmsDataModel();
                        $smsSend->send(16, 3, $send_shipper_id, 1, $msg_content, $platform);
                    }
                }
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //查询报修
    public function ajaxxbaoxiuAction() {
        $cx_bx_mobile = $this->getRequest()->getPost('cx_user_mobile');
        $cx_bx_sn = $this->getRequest()->getPost('cx_bx_sn');

        $returnData = array('code' => 0);
        if (!empty($cx_bx_mobile) || !empty($cx_bx_sn)) {
            $where = array();
            if (!empty($cx_bx_sn)) {
                $where['rq_baoxiu.encode_id'] = $cx_bx_sn;
            }
            if (!empty($cx_bx_mobile)) {
                $where['rq_kehu.mobile_phone'] = $cx_bx_mobile;
            }

            $dataModel = new Model('baoxiu');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_baoxiu.kid = rq_kehu.kid')->field('rq_baoxiu.*,rq_kehu.card_sn,rq_kehu.mobile_phone')->where($where)->order('rq_baoxiu.ctime desc')->limit(5)->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                $repairModel = new RepairModel();
                $repairObect = $repairModel->getRepairArray();
                foreach ($data as &$value) {
                    $value['time'] = date("Y-m-d", $value['ctime']);
                    if ($value['status'] == 1) {
                        $value['text'] = '已处理';
                    } else {
                        $value['text'] = '未处理';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                    $baoxiuArr = !empty($value['baoxiu_wt']) ? json_decode($value['baoxiu_wt']) : '';
                    $baoxiuText = '';
                    if (!empty($baoxiuArr)) {
                        $bxList = array();
                        foreach ($baoxiuArr as $bVal) {
                            if (isset($repairObect[$bVal])) {
                                $bxList[] = $repairObect[$bVal]['title'];
                            }
                        }
                        $baoxiuText = implode(';', $bxList);
                    }
                    $value['baoxiu_text'] = $baoxiuText;
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //查询报修（新）
    public function ajaxxbaoxiunAction() {
        $cx_bx_mobile = $this->getRequest()->getPost('cx_user_mobile');
        $cx_bx_sn = $this->getRequest()->getPost('cx_bx_sn');
        $kname = $this->getRequest()->getPost('kname');
        $start = $this->getRequest()->getPost('start');
        $end = $this->getRequest()->getPost('end');
        $where = array();
        $msg = 0;
        if ($start != '' && $end != '') {
            $msg = 1;
            $start = strtotime($start);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
            $where['rq_baoxiu.ctime'] = array(array('egt', $start), array('elt', $end), 'AND');
        }
        $status = $this->getRequest()->getPost('status');
        if ($status != 'x' && $status != '')
            $where['rq_baoxiu.status'] = $status;

        $returnData = array('code' => 0);
        if (!empty($cx_bx_mobile) || !empty($cx_bx_sn) || !empty($kname) || $msg == 1) {

            if (!empty($cx_bx_sn)) {
                $where['rq_baoxiu.encode_id'] = $cx_bx_sn;
            }
            if (!empty($cx_bx_mobile)) {
                $where['rq_kehu.mobile_phone'] = $cx_bx_mobile;
            }
            if (!empty($kname)) {
                $where['rq_baoxiu.kname'] = $kname;
            }

            $dataModel = new Model('baoxiu');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_baoxiu.kid = rq_kehu.kid')->field('rq_baoxiu.*,rq_kehu.card_sn,rq_kehu.mobile_phone')->where($where)->order('rq_baoxiu.ctime desc')->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                $repairModel = new RepairModel();
                $repairObect = $repairModel->getRepairArray();
                foreach ($data as &$value) {
                    $value['time'] = date("Y-m-d", $value['ctime']);
                    $value['text'] = ($value['status'] == 0) ? '未派发' : (($value['status'] == 1) ? '未处理' : '已处理');
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                    $baoxiuArr = !empty($value['baoxiu_wt']) ? json_decode($value['baoxiu_wt']) : '';
                    $baoxiuText = '';
                    if (!empty($baoxiuArr)) {
                        $bxList = array();
                        foreach ($baoxiuArr as $bVal) {
                            if (isset($repairObect[$bVal])) {
                                $bxList[] = $repairObect[$bVal]['title'];
                            }
                        }
                        $baoxiuText = implode(';', $bxList);
                    }
                    $value['baoxiu_text'] = $baoxiuText;
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //添加安检
    public function ajaxrepairAction() {
        $repair = $this->getRequest()->getPost('repair');
        $repair_comment = $this->getRequest()->getPost('repair_comment');
        $kid = $this->getRequest()->getPost('kid');

        $returnData = array('code' => 0);
        if (!empty($repair_comment) && !empty($kid)) {
            $data = LibF::M('kehu')->where(array('kid' => $kid))->find();
            if (!empty($data)) {
                $addData['kehu_id'] = $kid;
                $addData['kehu_name'] = $data['user_name'];
                $addData['repair_idlist'] = !empty($repair) ? implode(',', $repair) : '';
                $addData['comment'] = $repair_comment;
                $addData['time_created'] = time();

                $status = LibF::M('repair')->add($addData);
                if ($status) {
                    $returnData['code'] = 1;
                }
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //添加投诉
    public function ajaxtousuAction() {
        $tousu = $this->getRequest()->getPost('tousu');
        $comment = $this->getRequest()->getPost('comment');
        $kid = $this->getRequest()->getPost('tousu_kid');

        $returnData = array('code' => 0);
        if (!empty($comment) && !empty($kid)) {
            $data = LibF::M('kehu')->where(array('kid' => $kid))->find();
            if (!empty($data)) {
                $app = new App();
                $orderSn = $app->build_order_no();
                $addData['encode_id'] = 'ts' . $orderSn;
                $addData['kid'] = $kid;
                $addData['kname'] = $data['user_name'];
                $addData['shop_id'] = $data['shop_id'];
                $addData['comment'] = $comment;
                $addData['tousu_wt'] = !empty($tousu) ? json_encode($tousu) : '';
                $addData['ctime'] = time();
                $status = LibF::M('tousu')->add($addData);
                if ($status) {
                    $returnData['code'] = 1;
                }
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //查询投诉
    public function ajaxxtousuAction() {
        $cx_ts_mobile = $this->getRequest()->getPost('cx_user_mobile');
        $cx_ts_sn = $this->getRequest()->getPost('cx_ts_sn');

        $returnData = array('code' => 0);
        if (!empty($cx_ts_mobile) || !empty($cx_ts_sn)) {
            if (!empty($cx_ts_sn)) {
                $where['rq_tousu.encode_id'] = $cx_ts_sn;
            }
            if (!empty($cx_ts_mobile)) {
                $where['rq_kehu.mobile_phone'] = $cx_ts_mobile;
            }
            $dataModel = new Model('tousu');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_tousu.kid = rq_kehu.kid')->field('rq_tousu.*,rq_kehu.card_sn,rq_kehu.mobile_phone')->where($where)->order('rq_tousu.ctime desc')->limit(5)->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                $tousuModel = new RepairModel();
                $tousuObect = $tousuModel->getTousuArray();
                foreach ($data as &$value) {
                    $value['time'] = date("Y-m-d", $value['ctime']);
                    if ($value['status'] == 1) {
                        $value['text'] = '已处理';
                    } else {
                        $value['text'] = '未处理';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                    $tousuArr = !empty($value['tousu_wt']) ? json_decode($value['tousu_wt']) : '';
                    $tousuText = '';
                    if (!empty($tousuArr)) {
                        $tsList = array();
                        foreach ($tousuArr as $tVal) {
                            if (isset($tousuObect[$tVal])) {
                                $tsList[] = $tousuObect[$tVal]['title'];
                            }
                        }
                        $tousuText = implode(';', $tsList);
                    }
                    $value['tousu_text'] = $tousuText;
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //查询投诉（新）
    public function ajaxxtousunAction() {
        $cx_ts_mobile = $this->getRequest()->getPost('cx_user_mobile');
        $cx_ts_sn = $this->getRequest()->getPost('cx_ts_sn');
        $kname = $this->getRequest()->getPost('kname');
        $start = $this->getRequest()->getPost('start');
        $end = $this->getRequest()->getPost('end');
        $where = array();
        $msg = 0;
        if ($start != '' && $end != '') {
            $msg = 1;
            $start = strtotime($start);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
            $where['rq_tousu.ctime'] = array(array('egt', $start), array('elt', $end), 'AND');
        }
        $status = $this->getRequest()->getPost('status');
        if ($status != 'x' && $status != '')
            $where['rq_tousu.status'] = $status;

        $returnData = array('code' => 0);
        if (!empty($cx_ts_mobile) || !empty($cx_ts_sn) || $msg == 1) {
            if (!empty($cx_ts_sn)) {
                $where['rq_tousu.encode_id'] = $cx_ts_sn;
            }
            if (!empty($cx_ts_mobile)) {
                $where['rq_kehu.mobile_phone'] = $cx_ts_mobile;
            }
            if (!empty($kname)) {
                $where['rq_tousu.kname'] = $kname;
            }
            $dataModel = new Model('tousu');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_tousu.kid = rq_kehu.kid')->field('rq_tousu.*,rq_kehu.card_sn,rq_kehu.mobile_phone')->where($where)->order('rq_tousu.ctime desc')->limit(5)->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                $tousuModel = new RepairModel();
                $tousuObect = $tousuModel->getTousuArray();
                foreach ($data as &$value) {
                    $value['time'] = date("Y-m-d", $value['ctime']);
                    if ($value['status'] == 1) {
                        $value['text'] = '已处理';
                    } else {
                        $value['text'] = '未处理';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                    $tousuArr = !empty($value['tousu_wt']) ? json_decode($value['tousu_wt']) : '';
                    $tousuText = '';
                    if (!empty($tousuArr)) {
                        $tsList = array();
                        foreach ($tousuArr as $tVal) {
                            if (isset($tousuObect[$tVal])) {
                                $tsList[] = $tousuObect[$tVal]['title'];
                            }
                        }
                        $tousuText = implode(';', $tsList);
                    }
                    $value['tousu_text'] = $tousuText;
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //申请腿瓶
    public function ajaxbackAction() {
        $comment = $this->getRequest()->getPost('comment');
        $kid = $this->getRequest()->getPost('back_kid');
        $back_shop_id = $this->getRequest()->getPost('back_shop_id');
        $back_shipper_id = $this->getRequest()->getPost('back_shipper_id');
        $returnData = array('code' => 0);
        if (!empty($comment) && !empty($kid)) {
            $data = LibF::M('kehu')->where(array('kid' => $kid))->find();
            if (!empty($data)) {
                $time = time();

                $app = new App();
                $orderSn = $app->build_order_no();
                $param['depositsn'] = 'tp' . $orderSn;
                $param['comment'] = $comment;
                $param['kid'] = $kid;
                $param['mobile'] = $data['mobile_phone'];
                if (!empty($data['user_name']))
                    $param['username'] = $data['user_name'];
                if (!empty($back_shop_id)) {
                    $param['shop_id'] = $back_shop_id;
                } else {
                    $param['shop_id'] = $data['shop_id'];
                }
                $param['time'] = date('Y-m-d');
                $param['address'] = $data['address'];
                $param['time_created'] = $param['ctime'] = $time;

                //判断当前是否选择送气工
                $send_shipper_id = 0;
                if (!empty($back_shipper_id)) {
                    $shipperArr = explode('|', $back_shipper_id);
                    if (isset($shipperArr[0]) && !empty($shipperArr[0])) {
                        $param['shipper_id'] = $send_shipper_id = $shipperArr[0];
                        $param['status'] = 1;
                    }
                    if (isset($shipperArr[1]) && !empty($shipperArr[1])) {
                        $param['shipper_name'] = $shipperArr[1];
                    }
                    if (isset($shipperArr[2]) && !empty($shipperArr[2])) {
                        $param['shipper_mobile'] = $shipperArr[2];
                    }
                }
                $status = LibF::M('deposit_list')->add($param);
                if ($status) {
                    if (!empty($send_shipper_id)) {
                        $platform = 'android'; // 接受此信息的系统
                        $msg_content = json_encode(array('n_builder_id' => $send_shipper_id, 'n_title' => $param['depositsn'], 'n_content' => '退瓶订单已经派发', 'n_extras' => array('fromer' => $param['mobile'], 'fromer_name' => $param['mobile'], 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                        $smsSend = new SmsDataModel();
                        $smsSend->send(16, 3, $send_shipper_id, 1, $msg_content, $platform);
                    }
                    $returnData['code'] = 1;
                }
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //获取退瓶记录
    public function ajaxxdepositAction() {
        $cx_user_name = $this->getRequest()->getPost('cx_user_name');
        $cx_mobile_phone = $this->getRequest()->getPost('cx_mobile_phone');
        $cx_deposit_sn = $this->getRequest()->getPost('cx_deposit_sn');

        $returnData = array('code' => 0);
        if (!empty($cx_mobile_phone) || !empty($cx_user_name) || !empty($cx_deposit_sn)) {
            if (!empty($cx_deposit_sn)) {
                $where['rq_deposit_list.depositsn'] = $cx_deposit_sn;
            }
            if (!empty($cx_user_name)) {
                $where['rq_deposit_list.username'] = $cx_user_name;
            }
            if (!empty($cx_mobile_phone)) {
                $where['rq_kehu.mobile_phone'] = $cx_mobile_phone;
            }
            $dataModel = new Model('deposit_list');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_deposit_list.kid = rq_kehu.kid')->field('rq_deposit_list.*,rq_kehu.card_sn,rq_kehu.mobile_phone')->where($where)->order('rq_deposit_list.id desc')->limit(5)->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                foreach ($data as &$value) {
                    if ($value['status'] == 0) {
                        $value['text'] = '未派发';
                    } elseif ($value['status'] == 1) {
                        $value['text'] = '配送中';
                    } else {
                        $value['text'] = '已完成';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //获取退瓶记录（新）
    public function ajaxxdepositnAction() {
        $cx_user_name = $this->getRequest()->getPost('cx_user_name');
        $cx_mobile_phone = $this->getRequest()->getPost('cx_mobile_phone');
        $cx_deposit_sn = $this->getRequest()->getPost('cx_deposit_sn');
        $start = $this->getRequest()->getPost('start');
        $end = $this->getRequest()->getPost('end');
        $msg = 0;
        if ($start != '' && $end != '') {
            $msg = 1;
            $start = strtotime($start);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
            $where['rq_deposit_list.time_created'] = array(array('egt', $start), array('elt', $end), 'AND');
        }
        $status = $this->getRequest()->getPost('status');
        if ($status != 'x' && $status != '')
            $where['rq_deposit_list.status'] = $status;

        $returnData = array('code' => 0);
        if (!empty($cx_mobile_phone) || !empty($cx_user_name) || !empty($cx_deposit_sn) || $msg == 1) {
            if (!empty($cx_deposit_sn)) {
                $where['rq_deposit_list.depositsn'] = $cx_deposit_sn;
            }
            if (!empty($cx_user_name)) {
                $where['rq_deposit_list.username'] = $cx_user_name;
            }
            if (!empty($cx_mobile_phone)) {
                $where['rq_kehu.mobile_phone'] = $cx_mobile_phone;
            }
            $dataModel = new Model('deposit_list');
            $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_deposit_list.kid = rq_kehu.kid')->field('rq_deposit_list.*,rq_kehu.card_sn,rq_kehu.mobile_phone')->where($where)->order('rq_deposit_list.id desc')->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                foreach ($data as &$value) {
                    if ($value['status'] == 0) {
                        $value['text'] = '未派发';
                    } elseif ($value['status'] == 1) {
                        $value['text'] = '配送中';
                    } else {
                        $value['text'] = '已完成';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //添加通话记录
    public function ajaxaddphoneAction() {
        $mobile = $this->getRequest()->getPost('mobile');
        $type = $this->getRequest()->getPost('type', 1);
        $comment = $this->getRequest()->getPost('comment', '');
        $username = $this->getRequest()->getPost('username', '');
        $returnData = array('code' => 0);
        if (!empty($mobile)) {
            $data = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
            if (!empty($data)) {
                $addData['kid'] = $data['kid'];
                $addData['kname'] = $data['user_name'];
                $addData['shop_id'] = $data['shop_id'];
                $addData['comment'] = $comment;
                $addData['ctime'] = time();
                //$addData['type'] = $type;
                $addData['mobile_phone'] = $mobile;
                if ($username)
                    $addData['operator'] = $username;
            }else {
                $addData['mobile_phone'] = $mobile;
                $addData['comment'] = $comment;
                $addData['ctime'] = time();
                //$addData['type'] = $type;
                if ($username)
                    $addData['operator'] = $username;
            }
            $status = LibF::M('phone_log')->add($addData);
            $returnData = array('code' => 1);
        }
        echo json_encode($returnData);
        exit;
    }

    //获取通话记录
    public function ajaxphonelistAction() {
        $mobile_phone = $this->getRequest()->getPost('mobile_phone');

        $returnData = array('code' => 0);
        if (!empty($mobile_phone)) {
            $data = LibF::M('phone_log')->where(array('mobile_phone' => $mobile_phone))->limit(5)->order('ctime desc')->select();
            if (!empty($data)) {
                foreach ($data as &$value) {
                    $value['time'] = date('Y/m/d', $value['ctime']);
                }
                $returnData['code'] = 1;
                $returnData['data'] = $data;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //获取投诉记录
    public function ajaxtousulistAction() {
        $kid = $this->getRequest()->getPost('kid');
        $sno = $this->getRequest()->getPost('sno');

        $returnData = array('code' => 0);
        if (!empty($kid)) {
            $data = LibF::M('tousu')->where(array('kid' => $kid))->limit(5)->order('ctime desc')->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                $tousuModel = new RepairModel();
                $tousuObect = $tousuModel->getTousuArray();
                foreach ($data as &$value) {
                    $value['time'] = date('Y/m/d', $value['ctime']);
                    if ($value['status'] == 1) {
                        $value['text'] = '已处理';
                    } else {
                        $value['text'] = '未处理';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                    $tousuArr = !empty($value['tousu_wt']) ? json_decode($value['tousu_wt']) : '';
                    $tousuText = '';
                    if (!empty($tousuArr)) {
                        $tsList = array();
                        foreach ($tousuArr as $tVal) {
                            if (isset($tousuObect[$tVal])) {
                                $tsList[] = $tousuObect[$tVal]['title'];
                            }
                        }
                        $tousuText = implode(';', $tsList);
                    }
                    $value['tousu_text'] = $tousuText;
                }
                $returnData['code'] = 1;
                $returnData['data'] = $data;
            }
        } else if (!empty($sno)) {
            $data = LibF::M('tousu')->where(array('encode_id' => $sno))->limit(1)->find();
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //获取报修
    public function ajaxbxlistAction() {
        $kid = $this->getRequest()->getPost('kid');
        $sno = $this->getRequest()->getPost('sno');

        $returnData = array('code' => 0);
        if (!empty($kid)) {
            $data = LibF::M('baoxiu')->where(array('kid' => $kid))->limit(5)->order('ctime desc')->select();
            if (!empty($data)) {
                $shopObject = ShopModel::getShopArray();
                $repairModel = new RepairModel();
                $repairObect = $repairModel->getRepairArray();
                foreach ($data as &$value) {
                    $value['time'] = date('Y/m/d', $value['ctime']);
                    if ($value['status'] == 2) {
                        $value['text'] = '已处理';
                    } elseif ($value['status'] == 1) {
                        $value['text'] = '已派发';
                    } else {
                        $value['text'] = '未处理';
                    }
                    $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                    $baoxiuArr = !empty($value['baoxiu_wt']) ? json_decode($value['baoxiu_wt']) : '';
                    $baoxiuText = '';
                    if (!empty($baoxiuArr)) {
                        $bxList = array();
                        foreach ($baoxiuArr as $bVal) {
                            if (isset($repairObect[$bVal])) {
                                $bxList[] = $repairObect[$bVal]['title'];
                            }
                        }
                        $baoxiuText = implode(';', $bxList);
                    }
                    $value['baoxiu_text'] = $baoxiuText;
                }
                $returnData['code'] = 1;
                $returnData['data'] = $data;
            }
        } else if (!empty($sno)) {
            $data = LibF::M('baoxiu')->where(array('encode_id' => $sno))->limit(1)->find();
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //微信订单
    public function ajaxwxlistAction() {

        $returnData = array('code' => 0);
        $data = LibF::M('order_snap')->where(array('status' => 0))->limit(4)->order('id desc')->select();
        if (!empty($data)) {
            $idArr = array();

            //获取当前钢瓶规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            foreach ($data as &$value) {
                $bottleData = $value['bottle_list'];
                $value['bottle_text'] = '';
                if (!empty($bottleData)) {
                    $blist = explode('-', $bottleData);
                    if (!empty($blist)) {
                        $dataText = '';
                        foreach ($blist as $val) {
                            if (!empty($val)) {
                                $bArr = explode('|', $val);
                                if (isset($bArr[1]) && !empty($bArr[1])) {
                                    $dataText .= $bottleTypeData[$bArr[1]]['bottle_name'];
                                    $dataText .= isset($bArr[3]) ? ' ' . $bArr[3] . '个;' : '';
                                }
                            }
                        }
                        $value['bottle_text'] = $dataText;
                    }
                }
                $value['time'] = date('Y-m-d H:i:s', $value['time_created']);

                $idArr[] = $value['id'];
            }

            //更新状态
            $condition['id'] = array('in', $idArr);
            if (!empty($condition))
                LibF::M('order_snap')->where($condition)->save(array('is_show' => 1));

            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //微信订单历史日志（新）
    public function ajaxwxordernAction() {
        $returnData = array('code' => 0);
        $cx_wx_phone = $this->getRequest()->getPost('cx_wx_phone');
        if (!empty($cx_wx_phone))
            $where['mobile'] = $cx_wx_phone;
        $start = $this->getRequest()->getPost('start');
        $end = $this->getRequest()->getPost('end');
        if ($start != '' && $end != '') {
            $start = strtotime($start);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
            $where['time_created'] = array(array('egt', $start), array('elt', $end), 'AND');
        }
        $where['status'] = 1;
        $data = LibF::M('order_snap')->where($where)->limit(4)->order('id desc')->select();
        if (!empty($data)) {
            //获取当前钢瓶规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            foreach ($data as &$value) {
                $bottleData = $value['bottle_list'];
                $value['bottle_text'] = '';
                if (!empty($bottleData)) {
                    $blist = explode('-', $bottleData);
                    if (!empty($blist)) {
                        $dataText = '';
                        foreach ($blist as $val) {
                            if (!empty($val)) {
                                $bArr = explode('|', $val);
                                if (isset($bArr[1]) && !empty($bArr[1])) {
                                    $dataText .= $bottleTypeData[$bArr[1]]['bottle_name'];
                                    $dataText .= isset($bArr[3]) ? ' ' . $bArr[3] . '个;' : '';
                                }
                            }
                        }
                        $value['bottle_text'] = $dataText;
                    }
                }
                $value['time'] = date('Y/m/d', $value['time_created']);
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //微信订单历史日志
    public function ajaxwxorderAction() {
        $returnData = array('code' => 0);
        $cx_wx_phone = $this->getRequest()->getPost('cx_wx_phone');
        if (!empty($cx_wx_phone))
            $where['mobile'] = $cx_wx_phone;
        $where['status'] = 1;
        $data = LibF::M('order_snap')->where($where)->limit(4)->order('id desc')->select();
        if (!empty($data)) {
            //获取当前钢瓶规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            foreach ($data as &$value) {
                $bottleData = $value['bottle_list'];
                $value['bottle_text'] = '';
                if (!empty($bottleData)) {
                    $blist = explode('-', $bottleData);
                    if (!empty($blist)) {
                        $dataText = '';
                        foreach ($blist as $val) {
                            if (!empty($val)) {
                                $bArr = explode('|', $val);
                                if (isset($bArr[1]) && !empty($bArr[1])) {
                                    $dataText .= $bottleTypeData[$bArr[1]]['bottle_name'];
                                    $dataText .= isset($bArr[3]) ? ' ' . $bArr[3] . '个;' : '';
                                }
                            }
                        }
                        $value['bottle_text'] = $dataText;
                    }
                }
                $value['time'] = date('Y/m/d', $value['time_created']);
            }
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //处理微信订单
    public function ajaxupdatewxAction() {
        $wx_id = $this->getRequest()->getPost('order_id');
        $username = $this->getRequest()->getPost('username');

        $returnData = array('code' => 0);
        if (!empty($wx_id)) {
            $where['id'] = $wx_id;
            $udata['status'] = 1;
            if (!empty($username))
                $udata['operator'] = $username;

            $status = LibF::M('order_snap')->where($where)->save($udata);
            if ($status) {
                $returnData['code'] = $status;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //删除微信订单
    public function ajaxdeletewxAction() {
        $wx_id = $this->getRequest()->getPost('order_id');
        $username = $this->getRequest()->getPost('username');

        $returnData = array('code' => 0);
        if (!empty($wx_id)) {
            $where['id'] = $wx_id;
            $udata['status'] = 2;
            if (!empty($username))
                $udata['operator'] = $username;

            $status = LibF::M('order_snap')->where($where)->save($udata);
            if ($status) {
                $returnData['code'] = $status;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //获取未处理的微信订单数量
    public function wxnumAction() {
        $returnData = array('total' => 0);

        $where['is_show'] = 0;
        $where['status'] = array('neq', 2);
        $data = LibF::M('order_snap')->field('count(*) as total')->where($where)->find();
        $returnData['total'] = $data['total'];
        echo json_encode($returnData);
        exit;
    }

    //获取送气工
    public function ajaxshipperAction() {
        $returnData = array('code' => 0);
        $shop_id = $this->getRequest()->getPost('shop_id');
        if (!empty($shop_id)) {
            $shipperModel = new ShipperModel();
            $data = $shipperModel->getShipperArray('', $shop_id);
            $returnData['code'] = 1;
            $returnData['data'] = $data;
        }
        echo json_encode($returnData);
        exit;
    }

    //获取用户信息(新)
    public function ajaxuninfoAction() {
        
        $mobile_phone = trim($this->getRequest()->getPost('mobile_phone'));
        $card_sn = trim($this->getRequest()->getPost('card_sn'));

        $returnData = array('code' => 0);
        if (!empty($mobile_phone) || !empty($card_sn)) {//至少其中一个不为空
            if (empty($card_sn)) {//卡号为空，手机号搜索
                $condition['mobile_phone'] = $mobile_phone;
                $condition['mobile_list'] = array('like', '%' . $mobile_phone . '%');
                $condition['_logic'] = 'OR';
            } elseif (empty($mobile_phone)) {//卡号搜索
                $condition['card_sn'] = $card_sn;
            } else {//卡号，手机同时搜索
                $mobile['mobile_phone'] = $mobile_phone;
                $mobile['mobile_list'] = array('like', '%' . $mobile_phone . '%');
                $mobile['_logic'] = 'OR';
                $condition["_complex"] = $mobile;
                $condition['card_sn'] = $card_sn;
            }
            $data = LibF::M('kehu')->where($condition)->find();
            if (!empty($data)) {
                $returnData['code'] = 1;
                $returnData['data'] = $data;
                $commdify = LibF::M('commodity')->where(array('status' => 0))->select();
                $returnData['commdify'] = $commdify; //商品列表
                if ($data['shop_id'] > 0) {
                    //获取门店下送气工
                    $shipperModel = new ShipperModel();
                    $returnData['shipper'] = $shipperModel->getShipperArray('', $data['shop_id']);
                }

                $address = LibF::M('kehu_address')->where(array('kid' => $data['kid'], 'status' => 1))->select();
                $returnData['address'] = $address; //送货地址列表
                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
                //获取当前用户优惠券、余额券
                $yhData = $arr = array();
                $yh = LibF::M('promotions_user')->where(array('kid' => $data['kid'], 'status' => 0))->select();
                if (!empty($yh)) {
                    foreach ($yh as $value) {
                        if ($value['type'] == 1) {
                            $yhData[1][] = $value; //优惠券
                        } else {//余气券
                            $yhData[2][$value['good_type']]['num'] += $value['num'];
                            $yhData[2][$value['good_type']]['good_type'] = $value['good_type'];
                            $yhData[2][$value['good_type']]['title'] = $bottleTypeData[$value['good_type']]['bottle_name'];
//                            $yhData[2][] = $arr[$value['good_type']];
                        }
                    }
                }

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();
                $zkData = LibF::M('promotions')->where(array('status' => 1, 'type' => 2))->select();
                if (!empty($zkData)) {
                    foreach ($zkData as $k=>&$value) {
                        $arr = explode('|', $value['annex']);
                        if(!empty($arr[2])){
                            $cname = empty($productTypeData[$arr[1]]['name']) ? '' : '|'.$productTypeData[$arr[1]]['name'];
                            $value['cname'] = $arr[2]  . $cname;
                        }else{
                            unset($zkData[$k]);
                        }
                    }
                }
                $returnData['zhekou'] = $zkData;
                $returnData['promotions'] = $yhData;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    //获取指定商品规格
    public function ajaxCommodityTypeAction() {
        $name = trim($this->getRequest()->getPost('name'));
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $nameArr = explode('|', $name);
        $where['type'] = $nameArr[1];
        $where['name'] = $nameArr[0];
        $ctype = LibF::M('commodity')->where($where)->select();
        if (!empty($ctype)) {
            foreach ($ctype as &$value) {
                if ($value['type'] == 1) {
                    $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                } else {
                    $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                }
            }
            $returnData['code'] = 1;
            $returnData['data'] = $ctype;
        } else {
            $returnData = array('code' => 0);
        }
        echo json_encode($returnData);
        exit;
    }

    //用户操作(新)
    public function ajaxusernAction() {
        $data['user_name'] = $this->getRequest()->getPost('user_name');
        $data['mobile_phone'] = $this->getRequest()->getPost('mobile_phone');
        $data['mobile_list'] = $this->getRequest()->getPost('mobile_list');
        $data['shop_id'] = $this->getRequest()->getPost('shop_id');
        $data['ktype'] = $this->getRequest()->getPost('ktype');
        $data['card_sn'] = $this->getRequest()->getPost('card_sn');
        $data['gender'] = $this->getRequest()->getPost('sex_id');
        $data['address'] = $this->getRequest()->getPost('address');
        $data['comment'] = $this->getRequest()->getPost('comment');
        $data['comment'] = !empty($data['comment']) ? $data['comment'] : '';
        $data['gender'] = $this->getRequest()->getPost('sex_id', 0);
        $data['sheng'] = $this->getRequest()->getPost('sheng');
        $data['shi'] = $this->getRequest()->getPost('shi');
        $data['qu'] = $this->getRequest()->getPost('qu');
        $data['cun'] = $this->getRequest()->getPost('cun');
        $data['status'] = $this->getRequest()->getPost('status');

        $returnData = array('code' => 0);
        if (!empty($data)) {

            $kid = $this->getRequest()->getPost('kid');
            if ($kid > 0) {//修改
                unset($data['mobile_phone']);
                if (!empty($data['mobile_list'])) {
                    $mobile['mobile_phone'] = $data['mobile_list'];
                    $mobile['mobile_list'] = array('like', '%' . $data['mobile_list'] . '%');
                    $mobile['_logic'] = 'OR';
                    $condition["_complex"] = $mobile;
                    $condition['kid'] = array('neq', $kid);
                    $mdata = LibF::M('kehu')->where($condition)->find();
                }
                if (!empty($mdata)) {//判断电话是否被注册
                    $returnData['msg'] = 'm_repeat';
                } else {//判断卡号是否被注册
                    if (!empty($data['card_sn'])) {
                        $card['card_sn'] = $data['card_sn'];
                        $card['kid'] = array('neq', $kid);
                        $cdata = LibF::M('kehu')->where($card)->find();
                        if (!empty($cdata))
                            $returnData['msg'] = 'c_repeat';
                    }
                }
                if (empty($returnData['msg'])) {//无卡号、手机号占用=>修改
                    $klist = LibF::M('kehu')->where(array('kid' => $kid))->find();

                    if (!empty($klist['mobile_list']))
                        $data['mobile_list'] = $klist['mobile_list'] . ',' . $data['mobile_list'];
                    $data['utime'] = time();
                    $status = LibF::M('kehu')->where(array('kid' => $kid))->save($data);
//                    $khnew = LibF::M('kehu')->where(array('kid' => $kid))->find();
                    $returnData['status'] = ($data['status']==$klist['status'])?$klist['status']:$data['status'];
                }
            } else {//创建新用户
                if (!empty($data['mobile_phone'])) {
                    $condition['mobile_phone'] = $data['mobile_phone'];
                    $condition['mobile_list'] = array('like', '%' . $data['mobile_phone'] . '%');
                    $condition['_logic'] = 'OR';
                    $status = LibF::M('kehu')->where($condition)->find();
                    if (!empty($status)) {//判断电话是否被注册
                        $returnData['msg'] = 'm_repeat';
                    } else {//判断卡号是否被注册
                        if (!empty($data['card_sn'])) {
                            $status = LibF::M('kehu')->where(array('card_sn' => $data['card_sn']))->find();
                            if (!empty($status))
                                $returnData['msg'] = 'c_repeat';
                        }
                    }
                    if (empty($returnData['msg'])) {//无卡号、手机号占用=>创建
                        $app = new App();
                        $orderSn = $app->build_order_no();

                        $kehuModel = new KehuModel();
                        $kpassword = $kehuModel->encryptUserPwd($data['mobile_phone']);

                        $data['kehu_sn'] = 'Kh' . $orderSn;
                        $data['password'] = $kpassword;
                        $data['ctime'] = time();
                        $status = LibF::M('kehu')->add($data);
                    }
                } else {
                    $returnData['msg'] = 'km_repeat';
                }
            }
            $commdify = LibF::M('commodity')->where(array('status' => 0))->select();
            $returnData['commdify'] = $commdify;
            if ($data['shop_id'] > 0) {
                //获取门店下送气工
                $shipperModel = new ShipperModel();
                $returnData['shipper'] = $shipperModel->getShipperArray('', $data['shop_id']);
            }

            //获取当前用户优惠券
            $kehuModle = new KehuModel();
            $yhData = $kehuModle->getUserPromotionsData($data['kid']);
            $returnData['promotions'] = $yhData;


            $returnData['code'] = $status;
        }
        echo json_encode($returnData);
        exit;
    }

    //获取用户收货地址(新)
    public function khaddressAction() {
        $kid = $this->getRequest()->getPost('kid');
        $where['kid'] = $kid;
        $where['status'] = 1;
        $kh = LibF::M('kehu_address')->where($where)->select();
        $region = LibF::M('region')->field('region_id,region_name')->select();
        $regions = array();
        foreach ($region as $value) {
            $regions[$value['region_id']] = $value['region_name'];
        }
        if (!empty($kh)) {
            foreach ($kh as &$v) {
                $v['sheng_v'] = $regions[$v['sheng']];
                $v['shi_v'] = $regions[$v['shi']];
                $v['qu_v'] = $regions[$v['qu']];
                $v['cun_v'] = $regions[$v['cun']];
            }
        }
        $returnData['address'] = $kh;
        $id = $this->getRequest()->getPost('id', 1); //getPost getQuery
        $regionD = LibF::D('Region');
        $data = $regionD->getSubRegionList($id, 'region_id,region_name');
        $returnData['region'] = $data;
        echo json_encode($returnData);
        exit;
    }

    //保存收货地址
    public function ajaxAddressAction() {
        $data = $this->getRequest()->getPost();
        if($data['isdefault'] == 1){//若当前地址默认
            LibF::M('kehu_address')->where(array('isdefault'=>1))->save(array('isdefault'=>2));//其他地址改为不默认
        }
        if (empty($data['id'])) {
            $status = LibF::M('kehu_address')->add($data);
        } else {
            $status = LibF::M('kehu_address')->save($data);
        }
        $returnData['status'] = $status;
        if ($status)
            $datas = LibF::M('kehu_address')->where(array('status' => 1, 'kid' => $data['kid']))->select();
        $returnData['datas'] = $datas;
        echo json_encode($returnData);
        exit;
    }

    //删除收货地址
    public function delAddressAction() {
        $data['id'] = $this->getRequest()->getPost('aid');
        $data['status'] = -1;
        $status = LibF::M('kehu_address')->save($data);
        $k = LibF::M('kehu_address')->where($data)->find();
        $datas = LibF::M('kehu_address')->where(array('status' => 1, 'kid' => $k['kid']))->select();
        $returnData['status'] = $status;
        $returnData['datas'] = $datas;
        echo json_encode($returnData);
        exit;
    }

    //创建订单（新）
    public function ajaxordernAction() {
        $returnData = array('code' => 0);
        $param = $this->getRequest()->getPost();

        $data['kid'] = $param['kid'];
        $userData = LibF::M('kehu')->where(array('kid' => $param['kid']))->find();
        if (!empty($userData)) {
            $data['mobile'] = $userData['mobile_phone'];
            $data['username'] = $userData['user_name'];
            $data['kehu_type'] = $userData['ktype'];
            $data['shop_id'] = $userData['shop_id'];
        }
        if ($data['shop_id'] > 0) {
            $shopData = LibF::M('shop')->where(array('shop_id' => $data['shop_id']))->find();
            if (!empty($shopData['shop_name']))
                $data['shop_name'] = $shopData['shop_name'];
            if (!empty($shopData['shop_mobile']))
                $data['shop_mobile'] = $shopData['shop_mobile'];
        }
        if (!empty($param['kaddress'])) {
            $addressArr = explode('|', $param['kaddress']);
            $data['sheng'] = $addressArr[1];
            $data['shi'] = $addressArr[2];
            $data['qu'] = $addressArr[3];
            $data['cun'] = $addressArr[4];
            $data['address'] = $addressArr[5];
            $data['address_id'] = $addressArr[0];
        }
        $app = new App();
        $orderSn = $app->build_order_no();
        $orderSn = substr($orderSn, 0, -2);
        $data['order_sn'] = 'Dd' . $orderSn . $param['kid'];
        $data['ctime'] = $data['otime'] = time();
        $data['ordertype'] = 4;
        $data['deposit'] = 0;
//        $data['pay_money'] = $param['pay_money'];

        $price = $yhMoney = $yqNum = 0;
        $orderInfo = $value = $yhData = $yqtypeData = array();
        if (!empty($param['order_list'])) {
            foreach ($param['order_list'] as $v) {
                $bArray = explode('|', $v);
                $value['goods_id'] = $bArray[3];
                $value['goods_name'] = $bArray[4];
                $value['goods_num'] = $bArray[5];
                $value['goods_kind'] = $bArray[1];
                $value['goods_type'] = $bArray[2];
                $value['goods_price'] = $bArray[0];
                $value['pay_money'] = $bArray[0]*$bArray[5];
                $price += $value['pay_money'];
                $orderInfo[] = $value;
                $yhMoney += $bArray[7];
                $yhArr = explode(',', $bArray[8]);
                foreach ($yhArr as $vv) {
                    if ($vv != 0)
                        $yhData[] = $vv;
                }
                if(!empty($bArray[9])){
                    $yqtypeData[$bArray[1]]['num'] += (int)$bArray[9]; //统计不同规格余气券数
                    $yqtypeData[$bArray[1]]['good_type'] = $bArray[1];
                }
            }
            if (!empty($yqtypeData)) {
                foreach ($yqtypeData as $v) {
                    $yqData = LibF::M('promotions_user')->where(array('good_type' => $v['good_type'], 'type' => 2,'status'=>0))->order('id')->limit($v['num'])->select();
                    if (!empty($yqData))
                        foreach ($yqData as $i) {
                            $yhData[] = $i['id']; //余气券id
                        }
                }
            }
        }
        if ($yhMoney > 0) {
            $data['is_youhui'] = 1;
            $data['is_yh_money'] = $yhMoney;

        }
        $data['youhui_data'] = json_encode($yhData);

            $data['money'] = $price;
//
        //判断当前是否选择送气工
        $send_shipper_id = 0;
        $order_shipper_id = $param['shipper_id'];
        if (!empty($order_shipper_id)) {
            $shipperArr = explode('|', $order_shipper_id);
            if (isset($shipperArr[0]) && !empty($shipperArr[0])) {
                $data['shipper_id'] = $send_shipper_id = $shipperArr[0];
                $data['status'] = 2;
            }
            if (isset($shipperArr[1]) && !empty($shipperArr[1])) {
                $data['shipper_name'] = $shipperArr[1];
            }
            if (isset($shipperArr[2]) && !empty($shipperArr[2])) {
                $data['shipper_mobile'] = $shipperArr[2];
            }
        }
        $status = LibF::M('order')->add($data);
        if ($status) {
            if (!empty($send_shipper_id)) {
                //$platform = 'android'; // 接受此信息的系统
                //$msg_content = json_encode(array('n_builder_id' => $send_shipper_id, 'n_title' => $data['order_sn'], 'n_content' => '订单已经派发', 'n_extras' => array('fromer' => $data['shop_name'], 'fromer_name' => $data['shop_name'], 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                //$smsSend = new SmsDataModel();
                //$smsSend->send(16, 3, $send_shipper_id, 1, $msg_content, $platform);
            }

            //$smsdataModel = new SmsDataModel();
            //$smsdataModel->sendsms($data['address'], $data['order_sn'], $data['mobile']);
            foreach ($orderInfo as $val) {
                $val['shop_id'] = $data['shop_id'];
                $val['order_sn'] = $data['order_sn'];
                LibF::M('order_info')->add($val);
            }
            $returnData['code'] = $status;
            //更新用户订单数量
            LibF::M('kehu')->where(array('kid' => $data['kid']))->setInc('buy_time');

            //更新优惠券
            if (!empty($yhData)) {
                foreach($yhData as $yhv){
                    $uData['status'] = 1;
                    $uData['id'] = $yhv;
                    LibF::M('promotions_user')->save($uData);
                }

            }
        }
        echo json_encode($returnData);
        exit;
    }
    //催单
    public function ajaxSendAction(){
        $id = $this->getRequest()->getPost('oid'); //客户电话
        $data = LibF::M('order')->where(array('order_id'=>$id))->find();
        $send_shipper_id = (!empty($data))?$data['shipper_id']:'';
        if (!empty($send_shipper_id)) {
            //$platform = 'android'; // 接受此信息的系统
            //$msg_content = json_encode(array('n_builder_id' => $send_shipper_id, 'n_title' => $data['order_sn'], 'n_content' => '订单已经派发', 'n_extras' => array('fromer' => $data['shop_name'], 'fromer_name' => $data['shop_name'], 'fromer_icon' => '', 'image' => '', 'sound' => '')));
            //$smsSend = new SmsDataModel();
            //$smsSend->send(16, 3, $send_shipper_id, 1, $msg_content, $platform);
        }

        //$smsdataModel = new SmsDataModel();
        //$smsdataModel->sendsms($data['address'], $data['order_sn'], $data['mobile']);
        $returnData['status'] = $send_shipper_id;
        echo json_encode($returnData);
        exit;
    }

    //获取 管理中心-业务类型搜索
    public function ajaxallorderAction() {
        $type_all = $this->getRequest()->getPost('type_all'); //订单业务类型1订气2报修3投诉4退瓶
        $phone = $this->getRequest()->getPost('phone'); //客户电话
        $start = $this->getRequest()->getPost('start');
        $end = $this->getRequest()->getPost('end');
        if ($start != '' && $end != '') {
            $start = strtotime($start);
            $end = strtotime(date('Y-m-d 23:59:59', strtotime($end)));
            $time = array(array('egt', $start), array('elt', $end), 'AND');
        }
        $kehu = LibF::M('kehu')->field('kid,mobile_phone,user_name')->select();
        $kehuData = $kehuD = array();
        if (!empty($kehu))
            foreach ($kehu as $v) {
                $kehuData[$v['mobile_phone']]['kid'] = $v['kid'];
                $kehuData[$v['mobile_phone']]['uname'] = $v['user_name'];
                $kehuD[$v['kid']]['uname'] = $v['user_name'];
                $kehuD[$v['kid']]['phone'] = $v['mobile_phone'];
            }
        $returnData = array('code' => 0);
        if (!empty($start) && !empty($end)) {
            if (!empty($phone)) {
                $where['kid'] = $kehuData[$phone]['kid'];
                $dwhere['kid'] = $where['kid'];
            }
            $where['ctime'] = $time;
            if ($type_all == 0 || $type_all == 1) {
                $order = LibF::M('order')->field('ctime as atime,order_sn as sn,kid,status,comment')->where($where)->select();
                $orderStatus = array(1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单');
                if (!empty($order))
                    foreach ($order as &$v) {
                        $v['status'] = $orderStatus[$v['status']];
                    }
            }

            if ($type_all == 0 || $type_all == 2) {
                $baoxiu = LibF::M('baoxiu')->field('ctime as atime,encode_id as sn,kid,status,comment')->where($where)->select();
                if (!empty($baoxiu))
                    foreach ($baoxiu as &$v) {
                        $v['status'] = ($v['status'] == 0) ? '未派发' : (($v['status'] == 1) ? '未处理' : '已处理');
                    }
            }

            if ($type_all == 0 || $type_all == 3) {
                $tousu = LibF::M('tousu')->field('ctime as atime,encode_id as sn,kid,status,comment')->where($where)->select();
                if (!empty($tousu))
                    foreach ($tousu as &$v) {
                        $v['status'] = ($v['status'] == 1) ? '已处理' : '未处理';
                    }
            }

            if ($type_all == 0 || $type_all == 4) {
                $dwhere['time_created'] = $time;
                $deposit = LibF::M('deposit_list')->field('time_created as atime,depositsn as sn,kid,status,comment')->where($dwhere)->select();
                if (!empty($deposit))
                    foreach ($deposit as &$value) {
                        if ($value['status'] == 0) {
                            $value['status'] = '未派发';
                        } elseif ($value['status'] == 1) {
                            $value['status'] = '配送中';
                        } else {
                            $value['status'] = '已完成';
                        }
                    }
            }
            $order = empty($order) ? array() : $order;
            $baoxiu = empty($baoxiu) ? array() : $baoxiu;
            $tousu = empty($tousu) ? array() : $tousu;
            $deposit = empty($deposit) ? array() : $deposit;
            $allArr = array_merge($order, $baoxiu, $tousu, $deposit);
            $allData = array();
            foreach ($allArr as $vv) {
                $vv['phone'] = $kehuD[$vv['kid']]['phone'];
                $vv['uname'] = $kehuD[$vv['kid']]['uname'];
                $vv['ztime'] = date('Y-m-d H:i:s', $vv['atime']);
                $allData[$vv['atime']] = $vv;
            }
            krsort($allData);
            $allDatas = array();
            foreach ($allData as $c) {
                $allDatas[] = $c;
            }
            $returnData['code'] = 1;
            $returnData['data'] = $allDatas;
        }
        echo json_encode($returnData);
        exit;
    }

    //获取 管理中心-订单统计
    public function ajaxtypetimeAction() {
        $type = $this->getRequest()->getPost('type'); //1今日2本周
        $timeData = new TimedataModel();
        if ($type == 1) {
            $start = strtotime(date('Y-m-d', time()));
        } else {
            $start = $timeData->this_monday();
        }
        $time = array(array('egt', $start), array('elt', time()), 'AND');
        $returnData[1] = LibF::M('order')->where(array('ctime' => $time))->count();
        $returnData[2] = LibF::M('baoxiu')->where(array('ctime' => $time))->count();
        $returnData[3] = LibF::M('tousu')->where(array('ctime' => $time))->count();
        $returnData[4] = LibF::M('deposit_list')->where(array('time_created' => $time))->count();
        echo json_encode($returnData);
        exit;
    }

    //获取下单记录
    public function ajaxxorderlistAction() {
        $kid = $this->getRequest()->getPost('kid');

        $returnData = array('code' => 0);
        if (!empty($kid)) {
            $data = LibF::M('order')->where(array('kid' => $kid))->limit(8)->order('ctime desc')->select();
            $orderStatus = array(
                1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单'
            );
            if (!empty($data)) {
                foreach ($data as &$value) {
                    $value['time'] = date('Y-m-d H:i:s', $value['ctime']);
                    $value['status'] = $orderStatus[$value['status']];
                }
                $returnData['code'] = 1;
                $returnData['data'] = $data;
            }
        }
        echo json_encode($returnData);
        exit;
    }

}