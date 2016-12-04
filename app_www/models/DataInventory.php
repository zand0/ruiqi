<?php

/**
 * 钢瓶业务流转
 * 
 * 第一步钢瓶充装同步到库存（气站库存)
 * 第二步气站根据门店重装计划配送门店，门店扫瓶入库（门店库存)，同时更新气站库存数据启用中，同时存储log日志
 * 第三步送气工扫瓶进入送气工自己库存（送气工），同时删除门店库存表数据，同时更新log日志
 * 第四步客户接收订单，删除送气工库存表数据，同时更新气站库存数据已送达，同时更新log日志
 * 第五步送气工接收空瓶置换，瓶基准表查询瓶状态，扫码入送气工自已库存，同时更新气站库存表为空瓶
 * 第六步门店接收空瓶置换，删除送气工库存，同时更新log日志
 * 
 * 
 * 备注: 1.气站重瓶出库 == 门店重瓶入库（当前门店确认入库单 汇总数插入同时气站出入库汇总数据出库）
 *       2.气站空瓶入库 == 门店空瓶出库 （ 当前门店空瓶出库 同时 更新气站重瓶入库）
 *       3.门店重瓶出库 == 送气工重瓶入库 （门店重瓶出库 同时送气工重瓶入库） 送气工领瓶功能
 *       4.门店空瓶入库 == 送气工空瓶出库
 * 、    5.送气工空瓶入库 == 用户退瓶（用户置换）
 * 
 *
 * @author wky
 */
class DataInventoryModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
     * 
     * 接口1  送气工出入库 （扫用户的空瓶，用户退瓶）
     * 
     * @param $shipper_id
     * @param $user_id type == 1,
     */
    public function shipperck($shop_id = 0, $shipper_id, $user_id = 0, $bottle, $type = 1, $btype = 1) {
        if (empty($shipper_id) || empty($bottle))
            return FALSE;


        //获取当前钢瓶规格
        $bottleModel = new BottleModel();
        $bottleData = $bottleModel->bottleOData($bottle);

        //规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        //当前门店存在 送气工去门店申领重瓶
        $data = array();
        foreach ($bottle as $value) {
            $value = str_replace(array("\r\n", "\r", "\n"), "", $value);
            $kind_id = isset($bottleData['xinpian'][$value]) ? $bottleData['xinpian'][$value]['type'] : (isset($bottleData['number'][$value]) ? $bottleData['number'][$value]['type'] : 0); //规格
            if ($kind_id > 0) {
                if (!isset($data[$kind_id])) {
                    $data[$kind_id] = 1;
                } else {
                    $data[$kind_id] += 1;
                }
            }
        }
        $returnData = array();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $vv = array();
                $vv['kind_id'] = $k;
                $vv['goods_name'] = isset($bottleTypeData[$k]) ? $bottleTypeData[$k]['bottle_name'] : '';
                $vv['num'] = $v;
                $returnData[] = $vv;
            }
        }
        $rData = array('data' => $returnData);
        $status = 0;
        $returnArr = array();
        switch ($type) {
            case 1:  //入库(空瓶，重瓶)
                $returnArr = $this->shipperInverntory($shop_id, $shipper_id, $user_id, $returnData, $btype, 1);
                break;
            case 2:  //出库（空瓶，重瓶）
                $returnArr = $this->shipperInverntory($shop_id, $shipper_id, $user_id, $returnData, $btype, 2);
                break;
        }
        $rData['status'] = 1;
        $rData['sn'] = $returnArr['sn'];
        $rData['bottleData'] = $bottleData;
        $rData['bottleTypeData'] = $bottleTypeData;
        return $rData;
    }

    /**
     * 折旧瓶 出入库
     */
    public function zjshipperck($shop_id = 0, $shipper_id, $user_id = 0, $bottle, $type = 1, $btype = 1) {
        if (empty($shipper_id) || empty($bottle))
            return FALSE;

        //规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        //当前门店存在 送气工去门店申领重瓶
        $data = array();
        foreach ($bottle as $value) {
            if (!isset($data[$value['good_id']])) {
                $data[$value['good_id']] = $value['good_num'];
            } else {
                $data[$value['good_id']] += $value['good_num'];
            }
        }
        $returnData = array();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $vv = array();
                $vv['kind_id'] = $k;
                $vv['goods_name'] = isset($bottleTypeData[$k]) ? $bottleTypeData[$k]['bottle_name'] : '';
                $vv['num'] = $v;
                $returnData[] = $vv;
            }
        }
        $rData = array('data' => $returnData);

        $status = 0;
        $returnArr = array();
        switch ($type) {
            case 1:  //入库(空瓶，重瓶)
                $returnArr = $this->shipperInverntory($shop_id, $shipper_id, $user_id, $returnData, $btype, 1);
                break;
            case 2:  //出库（空瓶，重瓶）
                $returnArr = $this->shipperInverntory($shop_id, $shipper_id, $user_id, $returnData, $btype, 2);
                break;
        }
        $rData['status'] = $returnArr['status'];
        $rData['sn'] = $returnArr['sn'];
        $rData['bottleData'] = $bottleData;
        return $rData;
    }

    /**
     * 操作送气工出入库  更新库存数据
     * 
     * @param $shop_id 门店 $shipper_id 送气工 $user_id 用户 
     * @param $bottle 出入库瓶子类型
     * @param $bottleType 瓶子类型array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
     * @param $type 1入库2出库
     */
    public function shipperInverntory($shop_id, $shipper_id, $user_id, $bottle, $bottle_type = 1, $type = 1) {
        if (empty($shipper_id) || empty($bottle))
            return FALSE;

        $status = 0;
        $data = $param = array();
        $param['type'] = $type;  //出入库类型

        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'gpkc' . $orderSn; //获取订单号
        $param['time'] = $data['time'] = date('Y-m-d');
        $param['ctime'] = $data['ctime'] = time();
        if (!empty($bottle)) {
            foreach ($bottle as $key => $dVal) {
                $param['goods_propety'] = $data['goods_type'] = $bottle_type;
                $param['goods_type'] = $data['fs_type_id'] = $dVal['kind_id'];
                $param['goods_name'] = $data['fs_name'] = $dVal['goods_name'];
                $param['goods_num'] = $data['fs_num'] = $dVal['num'];
                $param['shipper_id'] = $data['shipper_id'] = $shipper_id;
                $param['gtype'] = 1;
                if (!empty($shop_id))
                    $param['shop_id'] = $shop_id;
                if (!empty($user_id))
                    $param['user_id'] = $user_id;

                $status = LibF::M('filling_stock_log_shipper')->add($param);
                if ($status) {
                    $data['type'] = 1;
                    //判断当前库存产品是否存在
                    $where = array('type' => $data['type'], 'shipper_id' => $param['shipper_id'], 'fs_type_id' => $data['fs_type_id'], 'goods_type' => $param['goods_propety']);
                    $showStore = LibF::M('filling_stock_shipper')->where($where)->find();
                    if (empty($showStore)) {
                        LibF::M('filling_stock_shipper')->add($data);
                    } else {
                        if ($param['type'] == 1) {
                            LibF::M('filling_stock_shipper')->where($where)->setInc('fs_num', $data['fs_num']);
                        } else {
                            LibF::M('filling_stock_shipper')->where($where)->setDec('fs_num', $data['fs_num']);
                        }
                    }
                }
            }
        }
        $rData['status'] = $status;
        $rData['sn'] = $param['documentsn'];
        return $rData;
    }

    /**
     * 接口2 门店出入库 
     * 
     * @param $shop_id,$shipper_id
     * @param $bottle type == 1
     * 
     * @param $bottle = array(kind_id,num)
     * @parma $kData = array(comment,admin_user)
     * @param $is_open 0 空瓶 1重瓶
     */
    public function shopck($shop_id, $shipper_id = 0, $bottle, $kData, $type = 1, $btype = 1) {
        $status = 0;
        switch ($type) {
            case 1:  //入库(空瓶，重瓶)
                $status = $this->shopInverntory($shop_id, $shipper_id, $bottle, $kData, $btype, 1);
                break;
            case 2:  //出库（空瓶，重瓶）
                $status = $this->shopInverntory($shop_id, $shipper_id, $bottle, $kData, $btype, 2);
                break;
        }
        return $status;
    }

    //配件库存
    public function shopckproduct($shop_id, $shipper_id = 0, $product, $kData = array(), $type = 1, $cno = '') {
        $status = 0;

        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commitObject = array();
        $fillingstocklogModel = new FillingModel();
        $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($productObject)) {
            foreach ($productObject as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $commitObject[$value['id']] = $value;
            }
        }
        if (!empty($product)) {
            $stockmethodModel = new StockmethodModel();
            foreach ($product as $value) {
                $pjparam['type'] = $type;
                $pjparam['gtype'] = 2;
                $pjparam['documentsn'] = $cno;
                $pjparam['goods_propety'] = $commitObject[$value['kind_id']]['norm_id'];
                $pjparam['goods_type'] = $pjparam['goods_id'] = $value['kind_id']; //配件id
                $pjparam['goods_name'] = $commitObject[$value['kind_id']]['name'] . '-' . $commitObject[$value['kind_id']]['typename'];
                $pjparam['goods_price'] = 0;
                $pjparam['goods_num'] = $value['num'];
                $pjparam['shop_id'] = $shop_id;
                $pjparam['shipper_id'] = $shipper_id;
                $pjparam['admin_user'] = $kData['admin_user'];
                $pjparam['time'] = date('Y-m-d');
                $pjparam['ctime'] = time();
                $status = $stockmethodModel->ShopstationsStock($pjparam, $shop_id, 2);
            }
        }
        return $status;
    }

    /**
     * 门店出入库  库存数据
     * 
     * @param $shop_id 门店 $shipper_id 送气工
     * @param $bottle 出入库瓶子类型
     * @param $bottleType 瓶子类型array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
     * @param $type 1入库2出库
     */
    public function shopInverntory($shop_id, $shipper_id, $returnData, $kData, $bottleType = 1, $type = 1) {
        if (empty($shop_id) || empty($returnData))
            return FALSE;

        //规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        $param = $data = array();
        $param['type'] = $type;
        if (isset($kData['comment']) && !empty($kData['comment']))
            $param['goods_comment'] = $kData['comment'];
        if (isset($kData['admin_user']) && !empty($kData['admin_user']))
            $param['admin_user'] = $kData['admin_user'];

        $status = 0;

        //获取订单号
        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'gpkc' . $orderSn;

        $param['time'] = $data['time'] = date('Y-m-d');
        $param['ctime'] = $data['ctime'] = time();
        $param['shop_id'] = $data['shop_id'] = $shop_id;
        if (!empty($shipper_id))
            $param['shipper_id'] = $shipper_id;

        $data['type'] = 1;
        if (!empty($returnData)) {
            foreach ($returnData as $dVal) {
                $param['goods_propety'] = $data['goods_type'] = isset($dVal['status_id']) ? $dVal['status_id'] : $bottleType;
                $param['goods_id'] = $param['goods_type'] = $data['fs_type_id'] = $dVal['kind_id'];
                $param['goods_name'] = $data['fs_name'] = $bottleTypeData[$dVal['kind_id']]['bottle_name'];
                $param['goods_num'] = $data['fs_num'] = $dVal['num'];
                $param['shipper_id'] = $shipper_id;

                $status = LibF::M('filling_stock_log_shop')->add($param);

                if ($status) {
                    //判断当前库存产品是否存在
                    $where = array('fs_type_id' => $data['fs_type_id'], 'shop_id' => $data['shop_id'], 'goods_type' => $data['goods_type']);
                    $showStore = LibF::M('filling_stock_shop')->where($where)->find();
                    if (empty($showStore)) {
                        LibF::M('filling_stock_shop')->add($data);
                    } else {
                        if ($param['type'] == 1) {
                            LibF::M('filling_stock_shop')->where($where)->setInc('fs_num', $data['fs_num']);
                        } else {
                            LibF::M('filling_stock_shop')->where($where)->setDec('fs_num', $data['fs_num']);
                        }
                    }
                }
            }
        }
        return $status;
    }

    /**
     * 接口3 气站出入库
     * 
     * @param $shop_id,$shipper_id
     * @param $bottle type == 1
     * 
     * @param $bottle = array(kind_id,num)
     * @parma $kData = array(comment,admin_user)
     */
    public function systemck($shop_id = 0, $bottle, $kData, $type = 1, $btype = 1) {
        $status = 0;
        switch ($type) {
            case 1:  //空瓶入库
                $status = $this->systemInverntory($shop_id, $bottle, $kData, $btype, 1);
                break;
            case 2:  //重瓶出库
                $status = $this->systemInverntory($shop_id, $bottle, $kData, $btype, 2);
                break;
        }
        return $status;
    }

    /**
     * 气站出入库  库存数据
     * 
     * @param $shop_id 门店
     * @param $bottle 出入库瓶子类型
     * @param $bottleType 瓶子类型array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
     * @param $type 1入库2出库
     * 
     * {kind_id,num,shop_id}
     */
    public function systemInverntory($shop_id, $returnData, $kData, $bottleType = 1, $type = 1) {
        if (empty($returnData))
            return FALSE;

        //规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        $param = $data = array();
        $param['type'] = $type;
        if (isset($kData['comment']) && !empty($kData['comment']))
            $param['goods_comment'] = $kData['comment'];
        if (isset($kData['admin_user']) && !empty($kData['admin_user']))
            $param['admin_user'] = $kData['admin_user'];

        $status = 0;

        //获取订单号
        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'gpkc' . $orderSn;

        $param['time'] = $data['time'] = date('Y-m-d');
        $param['ctime'] = $data['ctime'] = time();
        if (!empty($shop_id))
            $param['shop_id'] = $shop_id;

        if (!empty($returnData)) {
            foreach ($returnData as $dVal) {
                if (isset($dVal['status_id'])) {
                    $param['goods_propety'] = $data['goods_type'] = $dVal['status_id'];  //钢瓶类型
                } else {
                    $param['goods_propety'] = $data['goods_type'] = $bottleType;  //钢瓶类型
                }

                $param['goods_id'] = $param['goods_type'] = $data['fs_type_id'] = $dVal['kind_id'];
                $param['goods_name'] = $data['fs_name'] = $bottleTypeData[$dVal['kind_id']]['bottle_name'];
                $param['goods_num'] = $data['fs_num'] = $dVal['num'];
                if (isset($dVal['shop_id']) && !empty($dVal['shop_id']))
                    $param['shop_id'] = $dVal['shop_id'];

                $status = LibF::M('filling_stock_log')->add($param);
                if ($status) {
                    //判断当前库存产品是否存在
                    $where = array('type' => 1, 'fs_type_id' => $data['fs_type_id'], 'goods_type' => $data['goods_type']);
                    $showStore = LibF::M('filling_stock')->where($where)->find();
                    if (empty($showStore)) {
                        $data['type'] = 1;
                        LibF::M('filling_stock')->add($data);
                    } else {
                        if ($param['type'] == 1) {
                            LibF::M('filling_stock')->where($where)->setInc('fs_num', $data['fs_num']);
                        } else {
                            LibF::M('filling_stock')->where($where)->setDec('fs_num', $data['fs_num']);
                        }
                    }
                }
            }
        }
        return $status;
    }

    //气站配件出入库
    public function ckProduct($shop_id, $product, $kData = array(), $type = 1, $cno = '') {
        $status = 0;

        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commitObject = array();
        $fillingstocklogModel = new FillingModel();
        $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($productObject)) {
            foreach ($productObject as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $commitObject[$value['id']] = $value;
            }
        }
        if (!empty($product)) {
            $stockmethodModel = new StockmethodModel();
            foreach ($product as $value) {
                $pjparam['type'] = $type;
                $pjparam['gtype'] = 2;
                $pjparam['documentsn'] = $cno;
                $pjparam['goods_propety'] = $commitObject[$value['kind_id']]['norm_id'];
                $pjparam['goods_type'] = $pjparam['goods_id'] = $value['kind_id']; //配件id
                $pjparam['goods_name'] = $commitObject[$value['kind_id']]['name'] . '-' . $commitObject[$value['kind_id']]['typename'];
                $pjparam['goods_price'] = 0;
                $pjparam['goods_num'] = $value['num'];
                $pjparam['shop_id'] = !empty($shop_id) ? $shop_id : $value['shop_id'];
                $pjparam['admin_user'] = $kData['admin_user'];
                $pjparam['time'] = date('Y-m-d');
                $pjparam['ctime'] = time();
                $status = $stockmethodModel->GasstationsStock($pjparam, 2);
            }
        }
        return $status;
    }

    /**
     * 气站充装计划入库
     * 
     * @param $bottle 钢瓶
     * @param $type 1 入库 2 出库
     * @param $btype 瓶子类型array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
     */
    public function systemIn($bottle, $kData, $type = 1, $btype = 1) {
        $status = 0;
        switch ($type) {
            case 1: //入库
                $status = $this->qzInverntory($bottle, $kData, $btype, $type);
                break;
            case 2: //出库
                $status = $this->qzInverntory($bottle, $kData, $btype, $type);
                break;
        }
        return $status;
    }

    /**
     * 气站出入库（指往外出 不是配送门店）
     * 
     * @param $bottle 钢瓶
     * @param $type 1 入库 2 出库
     * @param $btype
     */
    public function qzInverntory($bottle, $kData, $btype, $type) {
        $returnData['code'] = 0;
        if (!empty($bottle)) {
            //规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

            $param = $data = array();
            $param['type'] = $type;
            if (isset($kData['comment']) && !empty($kData['comment']))
                $param['goods_comment'] = $kData['comment'];
            if (isset($kData['admin_user']) && !empty($kData['admin_user']))
                $param['admin_user'] = $kData['admin_user'];

            $status = 0;

            //获取订单号
            $app = new App();
            $orderSn = $app->build_order_no();
            $param['documentsn'] = 'gpkc' . $orderSn;

            $param['time'] = $data['time'] = date('Y-m-d');
            $param['ctime'] = $data['ctime'] = time();

            if (!empty($bottle)) {
                foreach ($bottle as $dVal) {
                    /* if (isset($dVal['status_id'])) {
                      $param['goods_propety'] = $data['goods_type'] = $dVal['status_id'];  //钢瓶类型
                      } else {
                      $param['goods_propety'] = $data['goods_type'] = $btype;  //钢瓶类型
                      } */
                    $param['goods_propety'] = $data['goods_type'] = $btype;  //钢瓶类型

                    $param['goods_id'] = $param['goods_type'] = $data['fs_type_id'] = $dVal['kind_id'];
                    $param['goods_name'] = $data['fs_name'] = $bottleTypeData[$dVal['kind_id']]['bottle_name'];
                    $param['goods_num'] = $data['fs_num'] = $dVal['num'];

                    $status = LibF::M('filling_stock_log')->add($param);
                    if ($status) {
                        //判断当前库存产品是否存在
                        $where = array('type' => 1, 'fs_type_id' => $data['fs_type_id'], 'goods_type' => $data['goods_type']);
                        $showStore = LibF::M('filling_stock')->where($where)->find();
                        if (empty($showStore)) {
                            $data['type'] = 1;
                            LibF::M('filling_stock')->add($data);
                        } else {
                            if ($param['type'] == 1) {
                                LibF::M('filling_stock')->where($where)->setInc('fs_num', $data['fs_num']);
                            } else {
                                LibF::M('filling_stock')->where($where)->setDec('fs_num', $data['fs_num']);
                            }
                        }
                        $returnData['code'] = 1;
                    }
                }
            }
        }
        return $returnData;
    }

}
