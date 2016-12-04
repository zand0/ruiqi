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
 * @author wky
 */
class DatatransferModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 气站库存同步数据，更新钢瓶基准表
     * 
     * @param $data
     * @return array
     */
    public function synchronizationStock($data) {

        if (empty($data)) {
            return $this->logicReturn('1', '操作失败');
        }

        $param['number'] = $data['number']; //钢印号
        $param['xinpian'] = $data['xinpian']; //芯片号
        $param['bar_code'] = $data['bar_code']; //条形码
        $param['type'] = $data['type']; //型号
        $param['status'] = 2;  //0未启用1空瓶2重瓶
        $param['production_date'] = ''; //出厂日期
        $param['check_date'] = ''; //送检日期
        $param['check_daqte_next'] = ''; //下次送检日期
        $param['ctime'] = time();
        $param['use_date'] = 0; //使用年限
        $param['chang_id'] = 0; //厂家id
        $param['chang_name'] = ''; //厂家名称
        $param['is_active'] = 1; //激活
        $param['is_used'] = 1; //是否使用

        $status = LibF::M('bottle')->add($param);
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok');
    }

    /**
     * 门店扫码同步门店库存数据信息(门店入)
     * 备注：扫重瓶入库（气站配送到门店） 扫空瓶入库（门店扫送气工送回空瓶）
     * 
     * @param $data
     * @param $type 1重瓶2空瓶
     * @return array
     */
    public function storeInventory($data, $shop_id = 0, $type = 1,$sn = '') {
        if (empty($data) || empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }

        $bottleModel = new BottleModel();
        $bottleObject = $bottleModel->bottleOData($data);
        $storeArr = $logArr = $bottleArr = array();
        foreach ($data as $value) {
            $value = strtoupper($value);
            if (isset($bottleObject['xinpian'][$value]) && !empty($bottleObject['xinpian'][$value])) {
                //存储门店库存
                if (!empty($sn))
                    $store['sn'] = $sn;

                $store['number'] = $log['number'] = $bottleObject['xinpian'][$value]['number'];
                $store['xinpian'] = $log['xinpian'] = $value;
                $store['bar_code'] = $log['bar_code'] = $bottleObject['xinpian'][$value]['number'];
                $store['type'] = $bottleObject['xinpian'][$value]['type'];
                $store['is_open'] = $type;
                $store['status'] = 1;
                $store['is_use'] = 1;
                $store['shop_id'] = $shop_id;
                $store['time_created'] = time();
                $storeArr[] = $store;

                //存储瓶子日志
                $log['type'] = $type;
                $log['property'] = 0;
                $log['shop_id'] = $shop_id;
                $log['shop_time'] = $log['time_created'] = time();
                $logArr[] = $log;

                //更新瓶子状态
                $bottleArr[] = "'" . $store['xinpian'] . "'";
            }elseif (isset($bottleObject['number'][$value]) && !empty($bottleObject['number'][$value])) {
                //存储门店库存
                if (!empty($sn))
                    $store['sn'] = $sn;

                $store['number'] = $log['number'] = $bottleObject['number'][$value]['number'];
                $store['xinpian'] = $log['xinpian'] = $bottleObject['number'][$value]['xinpian'];
                $store['bar_code'] = $log['bar_code'] = $bottleObject['number'][$value]['number'];
                $store['type'] = $bottleObject['number'][$value]['type'];
                $store['is_open'] = $type;
                $store['status'] = 1;
                $store['is_use'] = 1;
                $store['shop_id'] = $shop_id;
                $store['time_created'] = time();
                $storeArr[] = $store;

                //存储瓶子日志
                $log['type'] = $type;
                $log['property'] = 0;
                $log['shop_id'] = $shop_id;
                $log['shop_time'] = $log['time_created'] = time();
                $logArr[] = $log;

                //更新瓶子状态
                $bottleArr[] = "'" . $store['xinpian'] . "'";
            }
        }
        if (!empty($bottleArr)) {
            $status = LibF::M('store_inventory')->uinsertAll($storeArr);    //扫平入库
            $xinpianList = implode(',', $bottleArr);
            if ($type == 1) {
                //重瓶
                LibF::M('bottle_log')->uinsertAll($logArr); //门店第一次扫码 存储log
                //$udata['status'] = 9; //启用中
                //LibF::M('bottle')->where('xinpian IN (' . $xinpianList . ')')->save($udata);  //更新基准表
            } else {
                //空瓶
                LibF::M('shipper_inventory')->where('xinpian IN (' . $xinpianList . ')')->delete(); //门店扫空瓶，删除送气工库存
            }
            return $this->logicReturn(200, json_encode($storeArr));
        }else{
            return $this->logicReturn('0206', '添加失败!');
        }
    }

    /**
     * 送气工扫码同步送气工数据(送气工入)
     * 备注:送气工根据订单扫码取钢瓶扫重瓶（从门店扫绑定到送气工手中），扫空瓶（配送到用户，接收用户手中空瓶） 
     * 
     * @param $data
     * @param $type 1重瓶 2空瓶
     * @return array
     * 
     */
    public function shipperInventory($data, $shipper_id = 0, $type = 1, $bottleObject, $sn = '') {
        if (empty($data) || empty($shipper_id)) {
            return $this->logicReturn('1', '操作失败');
        }

        if (empty($bottleObject)) {
            $bottleModel = new BottleModel();
            $bottleObject = $bottleModel->bottleOData($data);
        }

        $shipperArr = $bottleArr = array();
        foreach ($data as $value) {
            //存储送气工库存
            if (isset($bottleObject['xinpian'][$value])) {
                $shipper['sn'] = $sn;
                $shipper['number'] = $bottleObject['xinpian'][$value]['number'];
                $shipper['xinpian'] = $value;
                $shipper['bar_code'] = $bottleObject['xinpian'][$value]['number'];
                $shipper['type'] = $bottleObject['xinpian'][$value]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = $type;
                $shipper['status'] = 1;
                $shipper['is_use'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;

                //更新瓶子状态
                $bottleArr[] = "'" . $shipper['xinpian'] . "'";
            }else if (isset($bottleObject['number'][$value])) {
                $shipper['sn'] = $sn;
                $shipper['number'] = $bottleObject['number'][$value]['number'];
                $shipper['xinpian'] = $bottleObject['number'][$value]['xinpian'];
                $shipper['bar_code'] = $bottleObject['number'][$value]['number'];
                $shipper['type'] = $bottleObject['number'][$value]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = $type;
                $shipper['status'] = 1;
                $shipper['is_use'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;
                
                //更新瓶子状态
                $bottleArr[] = "'" . $shipper['xinpian'] . "'";
            }
        }

        $status = LibF::M('shipper_inventory')->uinsertAll($shipperArr);
        if (!empty($bottleArr)) {
            $xinpianList = implode(',', $bottleArr);
            if ($type == 1) {
                $udata['shipper_id'] = $shipper_id;
                $udata['shipper_time'] = time();

                LibF::M('bottle_log')->where('xinpian IN (' . $xinpianList . ') AND property = 0')->save($udata); //送气工扫重瓶，更新瓶子日志
                LibF::M('store_inventory')->where('xinpian IN (' . $xinpianList . ') AND is_use = 1')->save(array('status' => 2, 'is_use' => 0)); //更新门店库存瓶子到送气工
                //LibF::M('store_inventory')->where('xinpian IN (' . $xinpianList . ')')->delete(); //更新门店库存瓶子到送气工
            } else {
                //空瓶更新基准表未空瓶
                $udata['status'] = 1;
                LibF::M('bottle')->where('xinpian IN (' . $xinpianList . ')')->save($udata); //送气工扫空瓶
            }
            return $this->logicReturn(200, 'ok');
        }
        return $this->logicReturn('0206', '添加失败!');
    }

    /**
     * 门店扫码同步门店库存数据信息(门店出)
     * 备注：扫空瓶出库（门店出库）
     * 
     * @param $data
     * @param $type 
     * @return array 
     */
    public function storeOutInventory($data, $shop_id = 0, $type = 0,$sn = '') {
        if (empty($data)) {
            return $this->logicReturn('1', '操作失败');
        }

        $bottleModel = new BottleModel();
        $bottleObject = $bottleModel->bottleOData($data);

        $storeArr = $logArr = $bottleArr = array();
        
        $time = time();
        foreach ($data as $value) {
            if (isset($bottleObject['xinpian'][$value])) {
                //存储门店库存
                if(!empty($sn))
                    $store['sn'] = $sn;
                
                $store['number'] = $log['number'] = $bottleObject['xinpian'][$value]['number'];
                $store['xinpian'] = $log['xinpian'] = $value;
                $store['bar_code'] = $log['bar_code'] = $bottleObject['xinpian'][$value]['number'];
                $store['type'] = $bottleObject['xinpian'][$value]['type'];
                $store['shop_id'] = $shop_id;
                $store['time_created'] = $time;
                $storeArr[] = $store;
                //存储瓶子日志
                /* $log['type'] = $type;
                  $log['property'] = 0;
                  $log['shop_id'] = $shop_id;
                  $log['shop_time'] = $log['time_created'] = time();
                  $logArr[] = $log; */

                //更新瓶子状态
                $bottleArr[] = "'" . $store['xinpian'] . "'";
            } else if (isset($bottleObject['number'][$value])){
                //存储门店库存
                if(!empty($sn))
                    $store['sn'] = $sn;
                
                $store['number'] = $log['number'] = $bottleObject['number'][$value]['number'];
                $store['xinpian'] = $log['xinpian'] = $bottleObject['number'][$value]['xinpian'];
                $store['bar_code'] = $log['bar_code'] = $bottleObject['number'][$value]['number'];
                $store['type'] = $bottleObject['number'][$value]['type'];
                $store['shop_id'] = $shop_id;
                $store['time_created'] = $time;
                $storeArr[] = $store;
                //存储瓶子日志
                /* $log['type'] = $type;
                  $log['property'] = 0;
                  $log['shop_id'] = $shop_id;
                  $log['shop_time'] = $log['time_created'] = time();
                  $logArr[] = $log; */

                //更新瓶子状态
                $bottleArr[] = "'" . $store['xinpian'] . "'";
            }
        }

        if (!empty($bottleArr)) {
            LibF::M('qz_inventory')->uinsertAll($storeArr);

            $xinpianList = implode(',', $bottleArr);
            //$wh = "xinpian in(" . $xinpianList . ") AND is_open = 0 ";
            $wh = "xinpian in(" . $xinpianList . ") ";
            LibF::M('store_inventory')->where($wh)->delete(); //门店扫空瓶出库，删除门店库存

            $wherelist = "xinpian in(" . $xinpianList . ")";
            LibF::M('shipper_inventory')->where($wherelist)->delete(); //门店扫空瓶，删除送气工库存

            $udata['status'] = 1;
            $status = LibF::M('bottle')->where($wherelist)->save($udata); //送气工扫空瓶
            return $this->logicReturn(200, 'ok');
        } else {
            return $this->logicReturn('0206', '添加失败!');
        }
    }

    /**
     * 门店配件出入库
     * 备注:门店配件入库
     * 
     * @param $data
     * @param $shop_id 门店
     * @type 0出库1入库
     */
    public function warehousing($data, $shop_id, $admin_id = '', $admin_user = '', $type) {
        if (empty($data) || empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }
        //获取订单号
        $app = new App();
        $orderSn = $app->build_order_no();

        $wVal = array();
        $wVal['documentsn'] = 'PJD' . $orderSn;
        $wVal['shop_id'] = $shop_id;
        $wVal['admin_id'] = $admin_id;
        $wVal['admin_user'] = $admin_user;
        $wVal['time'] = time();
        $wVal['type'] = $type;

        $status = LibF::M('warehousing')->add($wVal);
        if ($status && !empty($data)) {
            $wData = $tData = $pjData = array();
            foreach ($data as $value) {
                $shVal['documentsn'] = $tData['products_no'] = 'PJD' . $orderSn;
                $shVal['product_id'] = $value['id'];
                $shVal['product_type'] = 1;
                $shVal['product_name'] = $tData['products_name'] = $value['name'];
                $shVal['product_num'] = $tData['num'] = $value['num'];
                $shVal['product_price'] = $value['money'];
                $shVal['product_total'] = $value['price'];
                $shVal['time_created'] = $tData['utime'] = time();
                $wData[] = $shVal;
                
                $tData['shop_id'] = $shop_id;
                $tData['products_type'] = 1;
                $tData['shop_level'] = 1;
                $pjData[] = $tData;
            }
            if (!empty($wData)) {
                foreach ($wData as $sVal) {
                    LibF::M('warehousing_info')->add($sVal);
                }
            }
            if(!empty($pjData)){
                foreach($pjData as $tVal){
                    LibF::M('products_tj')->add($tVal);
                }
            }
            return $this->logicReturn(200, 'ok');
        } else {
            return $this->logicReturn('0206', '添加失败!');
        }
    }

    /**
     * 送气工送到用户确认订单
     * 备注：送气工订单确认1.基于订单2.送气工自己创建订单，如果订单存在确认订单扫空瓶，重瓶
     * 2.送气工自己为用户创建订单，如果是新用户，需要创建用户信息，如果是老用户直接创建老用户订单信息
     * 
     * @param $data 瓶子数据
     * @param $order_id 订单号
     * @param $shipper_id 送气工id
     * @param $userData 用户数据
     * @return array
     * 
     */
    public function kehuInventory($data, $shipper_id = 0, $kehu_id = 0,$bottleObject,$order_sn = '') {
        if (empty($data) || empty($shipper_id)) {
            return $this->logicReturn('1', '操作失败');
        }
        
        $time = time();

        //重瓶
        $zpData = isset($data['zp']) ? $data['zp'] : array();
        if (!empty($zpData)) {
            $bottleArr = $userBottle = $numberArr = array();
            
            if (empty($bottleObject)) {
                $bottleModel = new BottleModel();
                $bottleObject = $bottleModel->bottleOData($zpData);
            }

            foreach ($zpData as $zValue) {
                //更新瓶子状态
                $bVal['number'] = isset($bottleObject['xinpian'][$zValue]) ? $bottleObject['xinpian'][$zValue]['number'] : $bottleObject['number'][$zValue]['number'];
                $bVal['xinpian'] = isset($bottleObject['xinpian'][$zValue]) ? $bottleObject['xinpian'][$zValue]['xinpian'] : $bottleObject['number'][$zValue]['xinpian'];
                $bVal['bar_code'] = isset($bottleObject['xinpian'][$zValue]) ? $bottleObject['xinpian'][$zValue]['number'] : $bottleObject['number'][$zValue]['number'];
                $bVal['type'] = isset($bottleObject['xinpian'][$zValue]) ? $bottleObject['xinpian'][$zValue]['type'] : (isset($bottleObject['number'][$zValue]) ? $bottleObject['number'][$zValue]['type'] : 0);
                $bVal['shipper_id'] = $shipper_id;
                $bVal['kid'] = $kehu_id;
                $bVal['time_created'] = $time;
                $userBottle[] = $bVal;

                $bottleArr[] = "'" . $bVal['xinpian'] . "'";
                
                $numberArr[] = $bVal['number'];
            }
            if (!empty($bottleArr)) {
                $xinpianList = implode(',', $bottleArr);

                $udata['kehu_id'] = $kehu_id;
                $udata['property'] = 1;
                $udata['kehu_time'] = time();
                if (!empty($order_sn))
                    $udata['order_sn'] = $order_sn;


                $ubWhere['number'] = array('in', $numberArr);
                $ubWhere['property'] = 0;
                LibF::M('bottle_log')->where($ubWhere)->save($udata);

                $usWhere['number'] = array('in', $numberArr);
                LibF::M('shipper_inventory')->where($usWhere)->delete();

                $umWhere['number'] = array('in', $numberArr);
                $umWhere['is_use'] = 0;
                $umdate['status'] = 3;

                LibF::M('store_inventory')->where($umWhere)->save($umdate); //更新门店库存瓶子到用户
                //LibF::M('bottle_log')->where('xinpian IN (' . $xinpianList . ") AND property = 0")->save($udata);
                //LibF::M('shipper_inventory')->where('xinpian IN (' . $xinpianList . ')')->delete();
                //LibF::M('store_inventory')->where('xinpian IN (' . $xinpianList . ') AND is_use = 0')->save(array('status' => 3)); //更新门店库存瓶子到用户
                LibF::M('kehu_inventory')->uinsertAll($userBottle);

                //增加送气工出库操作
            }
        }

        //空瓶
        $kpData = isset($data['kp']) ? $data['kp'] : array();
        if (!empty($kpData)) {
            
            $bottleModel = new BottleModel();
            $bottleObject = $bottleModel->bottleOData($kpData);
            
            //插入空瓶
            $shipperArr = array();
            $userBottle = array();
            foreach ($kpData as $kValue) {
                $shipper['number'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['number'] : $bottleObject['number'][$kValue]['number'];
                $shipper['xinpian'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['xinpian'] : $bottleObject['number'][$kValue]['xinpian'];
                $shipper['bar_code'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['number'] : $bottleObject['number'][$kValue]['number'];
                $shipper['type'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['type'] : $bottleObject['number'][$kValue]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = 0;
                $shipper['is_use'] = 1;
                $shipper['status'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;

                $userBottle[] = $kValue;
            }
            LibF::M('shipper_inventory')->uinsertAll($shipperArr);
            if (!empty($userBottle)) {
                $where['kid'] = $kehu_id;
                $where['xinpian'] = array('in', $userBottle);
                LibF::M('kehu_inventory')->where($where)->delete();
            }
            //增加送气工入库操作
        }

        //待修瓶
        $fpData = isset($data['fp']) ? $data['fp'] : array();
        if (!empty($fpData)) {
            $shipperArr = array();
            $userBottle = array();
            
            $bottleModel = new BottleModel();
            $bottleObject = $bottleModel->bottleOData($fpData);
            
            foreach ($fpData as $kValue) {
                $shipper['number'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['number'] : $bottleObject['number'][$kValue]['number'];
                $shipper['xinpian'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['xinpian'] : $bottleObject['number'][$kValue]['xinpian'];
                $shipper['bar_code'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['number'] : $bottleObject['number'][$kValue]['number'];
                $shipper['type'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['type'] : $bottleObject['number'][$kValue]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = 2;
                $shipper['is_use'] = 1;
                $shipper['status'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;

                $userBottle[] = $kValue;
            }
            LibF::M('shipper_inventory')->uinsertAll($shipperArr);
            if (!empty($userBottle)) {
                $where['kid'] = $kehu_id;
                $where['xinpian'] = array('in', $userBottle);
                LibF::M('kehu_inventory')->where($where)->delete();
            }
        }

        //用户置换重瓶（不是待修瓶）
        $uzpData = isset($data['uzp']) ? $data['uzp'] : array();
        if (!empty($uzpData)) {
            $shipperArr = array();
            $userBottle = array();
            
            $bottleModel = new BottleModel();
            $bottleObject = $bottleModel->bottleOData($uzpData);
            
            foreach ($uzpData as $kValue) {
                $shipper['number'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['number'] : $bottleObject['number'][$kValue]['number'];
                $shipper['xinpian'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['xinpian'] : $bottleObject['number'][$kValue]['xinpian'];
                $shipper['bar_code'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['number'] : $bottleObject['number'][$kValue]['number'];
                $shipper['type'] = isset($bottleObject['xinpian'][$kValue]) ? $bottleObject['xinpian'][$kValue]['type'] : $bottleObject['number'][$kValue]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = 1;
                $shipper['is_use'] = 1;
                $shipper['status'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;

                $userBottle[] = $kValue;
            }
            LibF::M('shipper_inventory')->uinsertAll($shipperArr);
            if (!empty($userBottle)) {
                $where['kid'] = $kehu_id;
                $where['xinpian'] = array('in', $userBottle);
                LibF::M('kehu_inventory')->where($where)->delete();
            }
        }

        if (!empty($zpData) || !empty($kpData) || !empty($fpData)) {
            return $this->logicReturn(200, 'ok');
        } else {
            return $this->logicReturn('0206', '添加失败!');
        }
    }
    
    /**
     * 创建订单
     * 
     * @param $data
     * @param $datainfo
     */
    public function kehuOrder($data,$dataInfo,$pjData = array()){
        if (empty($data) || (empty($dataInfo) && empty($pjData))) {
            return $this->logicReturn('1', '操作失败');
        }
        
        //获取订单号
        $app = new App();
        $orderSn = $app->build_order_no();
        $orderSn = substr($orderSn, 0, -2);
        $orderData['order_sn'] = 'Dd' . $orderSn . $data['kid'];

        $orderData['kid'] = $data['kid'];
        $orderData['mobile'] = $data['mobile'];
        if(isset($data['sheng']) && !empty($data['sheng']))
            $orderData['sheng'] = $data['sheng'];
        if(isset($data['shi']) && !empty($data['shi']))
            $orderData['shi'] = $data['shi'];
        if(isset($data['qu']) && !empty($data['qu']))
            $orderData['qu'] = $data['qu'];
        if(isset($data['cun']) && !empty($data['cun']))
            $orderData['cun'] = $data['cun'];
        if(isset($data['address']) && !empty($data['address']))
            $orderData['address'] = $data['address'];
        
        if(isset($data['shop_id']))
            $orderData['shop_id'] = $data['shop_id'];
        
        if(isset($data['kehu_type']))
            $orderData['kehu_type'] = $data['kehu_type'];
        
        $orderData['money'] = $data['money']; //订单金额
        if (isset($data['pay_money']) && $data['pay_money'] > 0){
            $orderData['pay_money'] = $data['pay_money'];
        }
        
        if (isset($data['urgent']) && !empty($data['urgent']))
            $orderData['is_urgent'] = $data['urgent'];
        if (isset($data['is_old']) && !empty($data['isold']))
            $orderData['is_old'] = $data['isold'];
        if (isset($data['is_more']) && !empty($data['is_more']))
            $orderData['is_more'] = $data['ismore'];
        if (isset($data['goodtime']) && !empty($data['goodtime']))
            $orderData['good_time'] = $data['goodtime'];

        if (isset($data['is_settlement']))
            $orderData['is_settlement'] = $data['is_settlement'];

        $orderData['ctime'] = $orderData['otime'] = time();
        if (isset($data['username']))
            $orderData['username'] = $data['username'];

        if (isset($data['shipper_id']))
            $orderData['shipper_id'] = !empty($data['shipper_id']) ? $data['shipper_id'] : 0;

        if (isset($data['shipper_name']))
            $orderData['shipper_name'] = !empty($data['shipper_name']) ? $data['shipper_name'] : '';

        if (isset($data['shipper_mobile']))
            $orderData['shipper_mobile'] = !empty($data['shipper_mobile']) ? $data['shipper_mobile'] : '';

        if (isset($data['status']))
            $orderData['status'] = isset($data['status']) ? $data['status'] : 1;

        if (isset($data['shipment']))
            $orderData['shipment'] = isset($data['shipment']) ? $data['shipment'] : 0;

        if (isset($data['is_discount']))
            $orderData['is_discount'] = isset($data['is_discount']) ? $data['is_discount'] : 0;

        if (isset($data['discountment']))
            $orderData['discountmoney'] = isset($data['discountment']) ? $data['discountment'] : 0;

        if (isset($data['shouldmoney']))
            $orderData['shouldmoney'] = isset($data['shouldmoney']) ? $data['shouldmoney'] : 0;

        if (isset($data['order_tc_type']) && !empty($data['order_tc_type']))
            $orderData['order_tc_type'] = $data['order_tc_type'];

        if (isset($data['discountmonet']) && !empty($data['discountmonet']))   //订单优惠价格
            $orderData['discountmonet'] = $data['discountmonet'];

        if (isset($data['youhui_data']) && !empty($data['youhui_data'])) {  //用户是否使用优惠券
            $orderData['youhui_data'] = $data['youhui_data'];
            $orderData['is_yh_money'] = $data['youhui_money'];
            $orderData['is_youhui'] = 1;
        }

        if (isset($data['yhq_money']) && !empty($data['yhq_money'])) {
            $orderData['discountmoney'] = $data['yhq_money'];
        }

        if (isset($data['ispayment']) && !empty($data['ispayment'])) {
            $orderData['ispayment'] = $data['ispayment'];
        }

        $status = LibF::M('order')->add($orderData);
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
            exit;
        }

        $insertData = array();
        if (!empty($pjData)) {
            foreach ($pjData as &$Val) {
                $Val['order_sn'] = $orderData['order_sn'];
            }
            $insertData = $pjData;
        }
        if (!empty($dataInfo)) {
            foreach ($dataInfo as $value) {
                $val['shop_id'] = $orderData['shop_id'];
                $val['order_sn'] = $orderData['order_sn'];
                $val['goods_id'] = $value['good_id'];   //商品id
                $val['goods_name'] = $value['good_name']; //商品名称
                $val['goods_price'] = $value['good_price']; //商品价格
                $val['goods_num'] = $value['good_num'];  //商品数量
                $val['goods_type'] = isset($value['good_type']) ? $value['good_type'] : 1; //商品类型
                $val['goods_kind'] = $value['good_kind']; //商品规格
                $val['pay_money'] = $value['good_price'] * $value['good_num'];
                $val['good_zhekou'] = 0;
                $val['goods_premium'] = isset($value['goods_premium']) ? $value['goods_premium'] : $value['good_price'];
                $insertData[] = $val;
            }
        }
        if (!empty($insertData))
            LibF::M('order_info')->uinsertAll($insertData);

        //更新用户订单数量   
        LibF::M('kehu')->where(array('kid' => $orderData['kid']))->setInc('buy_time');
        LibF::M('shipper')->where(array('shipper_id' => $orderData['shipper_id']))->setInc('order_no');

        //$smsdataModel = new SmsDataModel();
        //$smsdataModel->sendsms($data['address'], $orderData['order_sn'], $orderData['mobile']);
        return $this->logicReturn(200, $orderData['order_sn']);
    }
    
    /**
     * 订单存储配件
     */
    public function kehuOrderProduct($order_sn, $product) {
        if (empty($order_sn) || empty($product))
            return FALSE;

        $insertData = array();
        foreach ($product as $value) {
            $val['order_sn'] = $order_sn;
            $val['goods_id'] = $value['good_id'];   //商品id
            $val['goods_name'] = $value['good_name']; //商品名称
            $val['goods_price'] = $value['good_price']; //商品价格
            $val['goods_num'] = $value['good_num'];  //商品数量
            $val['goods_type'] = $value['good_type']; //商品类型
            $val['goods_kind'] = $value['good_kind']; //商品规格
            $val['pay_money'] = $value['good_price'] * $value['good_num'];
            $insertData[] = $val;
        }
        $status = 0;
        if(!empty($insertData))
            $status = LibF::M('order_info')->uinsertAll($insertData);
        
        return $status;
    }

    /**
     * 门店库存(钢瓶)
     * 
     * @param $shop_id
     */
    public function shopKuncun($shop_id, $where = array()) {
        if (empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }
        
        $shopData = LibF::M('store_inventory')->where($where)->select();
        $returnData = $data = array();
        if (!empty($shopData)) {
            $total = 0;
            $typeArray = array();
            foreach ($shopData as $value) {
                if (!isset($returnData[$value['type']]))
                    $returnData[$value['type']] = 1;
                else
                    $returnData[$value['type']] += 1;

                $total += 1;
            }
            $data['data'] = $returnData;
            $data['total'] = $total;
        }
        return $data;
    }

    /**
     * 门店库存（配件）
     * 
     * @param $shop_id
     */
    public function shopKuncunPj($shop_id, $where = array()) {
        if (empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }

        $data = LibF::M('products_tj')->where($where)->select();
        $returnData = array();
        if(!empty($data)){
            foreach ($data as $value){
                $val['title'] = $value['products_name'];
                $val['num'] = $value['num'];
                $returnData[] = $val;
            }
        }
        
        return $returnData;
    }
    
    /**
     * 门店配件库存
     * 
     * @param $shop_id
     */
    public function shopwarehousing($shop_id, $where = '') {
        if (empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }

        if (!empty($shop_id)){
            $where .= "rq_warehousing.shop_id = " . $shop_id;
        }

        $warehousing = new Model('warehousing');
        $list = $warehousing->join('LEFT JOIN rq_warehousing_info ON rq_warehousing.documentsn = rq_warehousing_info.documentsn')->field("rq_warehousing_info.product_name,rq_warehousing_info.product_num,rq_warehousing_info.product_price,rq_warehousing_info.product_id")->where($where)->select();

        return $list;
    }

    /**
     * 送气工库存
     * 
     * @param $shipper_id
     */
    public function shipperKucun($shipper_id, $where = array()) {
        if (empty($shipper_id)) {
            return $this->logicReturn('1', '操作失败');
        }
        
        $shipperData = LibF::M('filling_stock_shipper')->where($where)->select();
        $returnData = $data = array();
        if (!empty($shipperData)) {
            $total = 0;
            $typeArray = array();
            foreach ($shipperData as $value) {
                if (!isset($returnData[$value['goods_type']][$value['fs_type_id']]))
                    $returnData[$value['goods_type']][$value['fs_type_id']] = $value['fs_num'];
                else
                    $returnData[$value['goods_type']][$value['fs_type_id']] += $value['fs_num'];

                $total += 1;
            }
            $data['data'] = $returnData;
            $data['total'] = $total;
        }
        return $data;
    }

    /**
     * 按照瓶子类型统计相关数据
     * 
     * @param $shop_id
     */
    public function bottleTypeStatistics($shop_id, $where = '') {

        if (!empty($shop_id))
            $where .=!empty($where) ? " AND rq_order.shop_id = " . $shop_id : " rq_order.shop_id = " . $shop_id;

        $order = new Model('order');
        $list = $order->join('LEFT JOIN rq_order_info ON rq_order.order_sn = rq_order_info.order_sn')->field("count(*) as num,rq_order_info.goods_id,rq_order_info.goods_name,rq_order_info.pay_money,rq_order_info.goods_type")->where($where)->group("rq_order_info.goods_id")->select();

        return $list;
    }

    /**
     * 按照送气工统计钢瓶
     * 
     * @param $shop_id
     */
    public function shipperStatistics($shop_id, $where = '') {

        if (!empty($shop_id))
            $where .=!empty($where) ? " AND shipper_id != 0 " : 'shipper_id != 0';

        $data = LibF::M('order')->field("count(*) as num,shipper_id,shipper_name")->where($where)->group("shipper_id")->select();
        return $data;
    }
    
    /**
     * 获取(气站)出入库的燃气列表
     * 
     * @param $type 0入库1出库
     * 
     */
    public function gasStatistics($type = 0) {
        
        $where['type'] = $type;

        $gas = new Model('gas_log');
        $list = $gas->join('LEFT JOIN rq_gas ON rq_gas_log.gtype = rq_gas.gid')->field("rq_gas_log.gnum,rq_gas_log.gprice,rq_gas_log.gtype,rq_gas.gas_name")->where($where)->select();

        return $list;
        
    }

    /**
     * 充装计划列表
     * 
     * @param $data
     * @param $shop_id
     */
    public function fillingList($shop_id) {
        if (empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }

        $fillingData = LibF::M('filling')->where('shop_id=' . $shop_id)->select();
        $returnData = array();
        if (!empty($fillingData)) {
            $shopModel = new ShopModel();
            $shopData = $shopModel->getShopArray($shop_id);
            foreach ($fillingData as $value) {
                $fVal['status'] = $value['status'];
                $fVal['time'] = date('Y-m-d', $value['ctime']);
                $fVal['type'] = $value['shop_level'];
                $fVal['address'] = $shopData['address'];
                $fVal['ordersn'] = $value['filling_no'];
                $fVal['store_name'] = $shopData['shop_name'];
                $fVal['store_manager'] = $shopData['admin_name'];
                $fVal['stores_phone'] = $shopData['mobile_phone'];
                $fVal['gp'] = !empty($value['bottle']) ? json_decode($value['bottle'],true) : array();
                $fVal['pj'] = !empty($value['products']) ? json_decode($value['products'],true) : array();
                
                $returnData[] = $fVal;
            }
        }
        return $returnData;
    }

    /**
     * 充装计划单详情
     * 
     * @param $data
     * @param $ordersn
     */
    public function fillingInfo($ordersn){
        if (empty($ordersn)) {
            return $this->logicReturn('1', '操作失败');
        }

        $fillingData = LibF::M('filling')->where("filling_no='" . $ordersn . "'")->find();
        $returnData = array();
        if (!empty($fillingData)) {
            $value = array();
            if(!empty($fillingData['bottle'])){
                $bottle = json_decode($fillingData['bottle'],true);
                if(!empty($bottle)){
                    foreach ($bottle as $key => $val){
                        $value['title'] = $val['name'];
                        $value['num'] = isset($val[1]) ? $val[1] : 0;
                        $returnData[] = $value;
                    }
                }
            }
            if(!empty($fillingData['products'])){
                $products = json_decode($fillingData['products'],true);
                if(!empty($products)){
                    foreach ($products as $key => $val){
                        $value['title'] = $val['name'];
                        $value['num'] = isset($val[1]) ? $val[1] : 0;
                        $returnData[] = $value;
                    }
                }
            }
        }
        return $returnData;
    }
    
    /**
     * 进货统计
     * 
     * 
     */
    public function fillStatics($shop_id){
        if (empty($shop_id)) {
            return $this->logicReturn('1', '操作失败');
        }

        $fillingData = LibF::M('filling')->where(array('shop_id' => $shop_id))->find();
        $returnData = array();
        if (!empty($fillingData)) {
            $value = array();
            if(!empty($fillingData['bottle'])){
                $bottle = json_decode($fillingData['bottle'],true);
                if(!empty($bottle)){
                    foreach ($bottle as $key => $val){
                        $value['title'] = $val['name'];
                        $value['num'] = isset($val[1]) ? $val[1] : 0;
                        $value['type'] = 1;
                        $returnData[] = $value;
                    }
                }
            }
            if(!empty($fillingData['products'])){
                $products = json_decode($fillingData['products'],true);
                if(!empty($products)){
                    foreach ($products as $key => $val){
                        $value['title'] = $val['name'];
                        $value['num'] = isset($val[1]) ? $val[1] : 0;
                        $value['type'] = 2;
                        $returnData[] = $value;
                    }
                }
            }
        }
        return $returnData;
    }
    
    /**
     * 门店回流率统计计算
     * 
     * 备注：回流率计算根据当前门店出入库表统计 store_inventory当前门店库存包含空瓶（送气工送来）、重瓶（气站送来）
     * if 当前送气工到门店领取重瓶当前门店重瓶状体status 更新为 2 意味当前送气工已经领取，同时送气工库存表shipper_inventory 更新瓶子数据
     * if 当前送气工将瓶子配送到客户 当前 门店库存store_inventory 状态 status 更新为 3 意味当前瓶子更新给用户，同时更新当前送气库存表 shipper_inventory 状态 2意味当前瓶子到用户手里
     * if 当前送气工从用户获取空瓶入库，这是更新当前送气工瓶子状态为空瓶，
     * if 当前送气工将瓶子更新给门店，这时更新门店瓶子为空瓶，同时更新当前送气工库存瓶子状态为不可用
     * if 当前门店把瓶子更新给气站，这是更新门店瓶子
     * 
     * 回流率 回来空瓶子/出去重瓶瓶子
     */
    public function getEgrrateList($shop_id = '', $beginTime = '', $endTime = '', $type = 0) {

        /**
         * 获取门店所有出去的瓶子
         * $sql = SELECT count(*) as total,shop_id FROM rq_store_inventory WHERE is_open = 1 AND is_use = 0 GROUP BY shop_id;
         * 
         * 获取门店所有回来瓶子
         * $sql = "SELECT count(*) as total,shop_id FROM rq_store_inventory WHERE is_open = 0 AND is_use = 1 GROUP BY shop_id;
         * 
         * 回流率计算 回来的瓶子/送出去的瓶子
         */
        $grouplist = '';
        $file = '';
        switch ($type) {
            case 0: //统计门店总数
                $grouplist = 'shop_id';
                $file = "count(*) as total,shop_id";
                break;
            case 1: //按照瓶子类型统计
                $grouplist = 'shop_id,type';
                $file = "count(*) as total,shop_id,type";
                break;
            case 2: //按照时间点统计
                $grouplist = 'shop_id';
                $file = "count(*) as total,shop_id";
                break;
        }

        //出去瓶子
        $returnData = array('out_data' => array(), 'in_data' => array());

        $where = " is_open = 1 AND is_use = 0 ";
        if ($beginTime && $endTime) {
            $where .= ' AND time_created >= ' . $beginTime . " AND time_created <= " . $endTime;
        }
        if ($shop_id) {
            $where .= " AND shop_id = " . $shop_id;
        }
        $outData = LibF::M('store_inventory')->field($file)->where($where)->group($grouplist)->select();
        //回来的瓶子
        $where = " is_open = 0 AND is_use = 1 ";
        if ($beginTime && $endTime) {
            $where .= ' AND time_created >= ' . $beginTime . " AND time_created <= " . $endTime;
        }
        if ($shop_id) {
            $where .= " AND shop_id = " . $shop_id;
        }
        $inData = LibF::M('store_inventory')->field($file)->where($where)->group($grouplist)->select();

        if (!empty($outData) || !empty($inData)) {
            switch ($type) {
                case 0:
                    if (!empty($outData)) {
                        foreach ($outData as $value) {
                            $returnData['out_data'][$value['shop_id']] = $value['total'];
                        }
                    }
                    if (!empty($inData)) {
                        foreach ($inData as $val) {
                            $returnData['in_data'][$val['shop_id']] = $val['total'];
                        }
                    }
                    break;
                case 1:
                    if (!empty($outData)) {
                        foreach ($outData as $value) {
                            $returnData['out_data'][$value['shop_id']] = $value['total'];
                        }
                    }
                    if (!empty($inData)) {
                        foreach ($inData as $val) {
                            $returnData['in_data'][$val['shop_id']] = $val['total'];
                        }
                    }
                    break;
                case 2:
                    if (!empty($outData)) {
                        foreach ($outData as $value) {
                            $returnData['out_data'][$value['shop_id']] = $value['total'];
                        }
                    }
                    if (!empty($inData)) {
                        foreach ($inData as $val) {
                            $returnData['in_data'][$val['shop_id']] = $val['total'];
                        }
                    }
                    break;
            }
        }
        return $returnData;
    }

    /**
     * 气站瓶子库存数量统计按照瓶子类型统计空、重瓶相关数量
     * 
     * 
     */
    public function getBottleTypeList($shop_id = '', $beginTime = '', $endTime = '', $type = 0) {

        $where = " is_active = 1 ";
        if ($type == 1) {
            $where .= ' AND status = 1 '; //空瓶
        } else if ($type == 2) {
            $where .= ' AND status = 2 '; //重瓶
        } else {
            $where .= ' AND status IN (1,2) ';
        }

        $file = 'count(*) as total,type,status';
        $grouplist = 'type,status';
        /**
         * sql 获取当前门店在使用的重瓶、空瓶的统计数量
         * SELECT count(*) as total,is_open,shop_id FROM rq_bottle WHERE is_active = 1 GROUP BY type,status
         */
        $returnData = array();
        $data = LibF::M('bottle')->field($file)->where($where)->group($grouplist)->select();
        if ($data) {
            foreach ($data as $value) {
                $returnData[$value['type']][$value['status']] = $value['total'];
            }
        }
        return $returnData;
    }
}
