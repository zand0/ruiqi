<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class OrderwxModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    //获取订单详情
    public function getOrder($id){
        $param['orderid']=$id;
        $res = $this->getOrderList($param);
        if(empty($res)){
            return null;
        }else{
            //获取商品详情
            $res['order_info']=$this->getUserOrderInfoList(['ordersn'=>$res['order_sn']]);
            //获取用户信息
            $res['user_info']=LibF::M('kehu')->where('kid=%d',[$res['kid']])->getField('kid,user_name,mobile_phone,address')[$res['kid']];
            $res['orderstatus']=$this->getOrderStatus($res);
            $res['otime_str'] = date('Y-m-d H:i',$res['otime']);
            $res['good_time_str'] = date('Y-m-d H:i',$res['good_time']);
            if(empty($res['shop_name'])){
                $res['shop_name'] = LibF::M('shop')->field('shop_name')->where(['shop_id'=>$res['shop_id']])->find()['shop_name'];
            }
            return $res;
        }
    }
    
    /**
     * 获取用户订单列表
     * @param string $param
     * @param string $order
     * @return unknown
     */
    public  function getOrderList($param = '',$order = ''){
        $ordersn = isset($param['ordersn']) ? $param['ordersn'] : '';
        $kid = isset($param['kid']) ? $param['kid'] : '';
        $shipperMobile = isset($param['shipper_mobile']) ? $param['shipper_mobile'] : '';
        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $start_time = isset($param['start_time']) && !empty($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) && !empty($param['end_time']) ? strtotime($param['end_time']) : '';
        $shop_id = isset($param['shop_id']) ? $param['shop_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';
        $orderid = isset($param['orderid']) ? $param['orderid'] : 0;
    
        $orderModel = LibF::M('order');
        if(!empty($orderid)){
            return $orderModel->where("order_id=%d",[$orderid])->find();
        }
        $where = array();
    
        if ($ordersn)
            $where['order_sn'] = $ordersn;
        if ($kid)
            $where['kid'] = $kid;
        if($shop_id)
            $where['shop_id'] = $shop_id;
        if ($shipperMobile)
            $where['shipper_mobile'] = $shipperMobile;
        if ($mobile)
            $where['mobile'] = $mobile;
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');
        if ($status)
            $where['status'] = $status;
        if(!isset($param['page'])){
            $pageStart=0;
        }else{
            $param['page']=$param['page']<1?1:$param['page'];
            $pageStart = ($param['page'] - 1) * $param['pagesize'];
        }
        if(!isset($param['pagesize'])){
            $param['pagesize']= 10;
        }
        $orderlist = (!empty($order)) ? $order : 'order_id desc';

        //$data = $orderModel->where($where)->order($orderlist)->limit($pageStart, $param['pagesize'])->select();
        $data = $orderModel->where(['kid'=>$kid])->order($orderlist)->limit($pageStart, $param['pagesize'])->select();
        foreach ($data as &$d){
            $d['order_info']=$this->getUserOrderInfoList(['ordersn'=>$d['order_sn']]);
            $d['orderstatus'] = $this->getOrderStatus($d);
            //$d['ctime'] = date('Y-m-d H:i:s',$d['ctime']);
        }
        unset($d);
        return $data;
    }
    public function getOrderStatus($d){
        switch ($d['status']){
            case 1:
                return '未派发';
                break;
            case 2:
                return '待收货';
                break;
            case 4:
                return '已完成';
                break;
            default:
                return '未知';
        }
        if($d['is_evaluation']==1){
            return '已评价';
        }
    }
    /**
     * 订单列表展示
     * 
     * @param $params
     */
    public function orderlist($params = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';
        $ordersn = isset($params['order_sn']) ? $params['order_sn'] : '';
        $is_urgent = isset($params['is_urgent']) ? $params['is_urgent'] : '';
        $shipper_name = isset($params['shipper_name']) ? $params['shipper_name'] : '';
        $username = isset($params['username']) ? $params['username'] : '';
        $mobile = isset($params['mobile']) ? $params['mobile'] : '';
        $kehu_type = isset($params['kehu_type']) ? $params['kehu_type'] : '';
        
        $start_time = isset($params['time_start']) ? $params['time_start'] : '';
        $end_time = isset($params['time_end']) ? $params['time_end'] : '';
        $status = isset($params['status']) ? $params['status'] : '';
        
        $address = isset($params['address']) ? $params['address'] : '';
        $kid = isset($params['kid']) ? $params['kid'] : '';
        
        $deposit = isset($params['deposit']) ? $params['deposit'] : '';
        $raffinat = isset($params['raffinat']) ? $params['raffinat'] : '';
        $depreciation = isset($params['depreciation']) ? $params['depreciation'] : '';

        $orderModel = LibF::M('order');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;
        if ($ordersn)
            $where['order_sn'] = array('like', $ordersn . '%');
        if ($is_urgent)
            $where['is_urgent'] = $is_urgent;
        if ($shipper_name)
            $where['shipper_name'] = array('like', $shipper_name . '%');
        if ($username)
            $where['username'] = array('like', $username . "%");
        if ($mobile)
            $where['mobile'] = array('like',$mobile.'%');
        if ($kehu_type)
            $where['kehu_type'] = $kehu_type;
        if ($status)
            $where['status'] = $status;
        if ($kid)
            $where['kid'] = $kid;
        
        if ($start_time && $end_time) {
            $where['ctime'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'AND');
        }
        if (!empty($deposit)) {
            $where['deposit'] = $deposit;
        }
        if (!empty($raffinat)) {
            $where['raffinat'] = $raffinat;
        }
        if (!empty($depreciation)) {
            $where['depreciation'] = $depreciation;
        }

        $offset = ($page - 1) * $pageSize;
        $count = $orderModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $orderModel->where($where)->limit($offset, $pageSize)->order('order_id desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    /**
     * 统计当前用户欠款列表 汇总相关数据列表
     * 
     * 
     */
    public function qk_orderlist($params = array(), $where) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;

        $orderModel = LibF::M('order');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $offset = ($page - 1) * $pageSize;
        $dataSelect = $orderModel->group('kid')->where($where)->select();

        $count = count($dataSelect);
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }

        $rows = $orderModel->field('*,count(*) as num,sum(pay_money-deposit+is_settlement_money) as total,sum(deposit) as yjtotal')->order('ctime desc')->group('kid')->where($where)->limit($offset, $pageSize)->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    /**
     * 获取当前App用户订单列表
     * 
     */
    public function getUserOrderList($param = '',$order = '') {

        $ordersn = isset($param['ordersn']) ? $param['ordersn'] : '';
        $kid = isset($param['kid']) ? $param['kid'] : '';

        $shipperMobile = isset($param['shipper_mobile']) ? $param['shipper_mobile'] : '';

        $mobile = isset($param['mobile']) ? $param['mobile'] : '';
        $start_time = isset($param['start_time']) && !empty($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) && !empty($param['end_time']) ? strtotime($param['end_time']) : '';
        
        $shop_id = isset($param['shop_id']) ? $param['shop_id'] : '';
        $status = isset($param['status']) ? $param['status'] : '';

        $orderModel = LibF::M('order');
        
        $where = array();
        if ($ordersn)
            $where = array('order_sn' => $ordersn);
        if ($kid)
            $where = array('kid' => $kid);
        
        if($shop_id)
            $where['shop_id'] = $shop_id;
        
        if ($shipperMobile)
            $where = array('shipper_mobile' => $shipperMobile);
        if ($mobile)
            $where = array('mobile' => $mobile);
        
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');
        if ($status)
            $where['status'] = $status;

        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        $orderlist = (!empty($order)) ? $order : 'order_id desc';

        $data = $orderModel->where($where)->order($orderlist)->limit($pageStart, $param['pagesize'])->select();
        return $data;
    }

    /**
     * 订单详情列表
     */
    public function getUserOrderInfoList($params = '') {
        $ordersn = isset($params['ordersn']) ? $params['ordersn'] : '';

        $orderModel = LibF::M('order_info');
        $where = '';
        if (!empty($ordersn)) {
            if (!is_array($ordersn))
                $where = array('order_sn' => $ordersn);
            else
                $where = "order_sn IN(" . implode(',', $ordersn) . ")";
        }

        $data = $orderModel->where($where)->order('id DESC')->select();
        foreach ($data as &$d){
            if(strstr($d['goods_name'],'-')){
                $arr = explode('-',$d['goods_name']);
                $d['type']=$arr[1];
                $d['name']=$arr[0];
            }else{
                $d['type']='';
                $d['name']=$d['goods_name'];
            }
            if(empty($d['type'])){
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
                
                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();
                
                $commdify = LibF::M('commodity')->where(array('id'=>$d['goods_id'],'status' => 0, 'type' => 1))->select();
                if (!empty($commdify)) {
                    foreach ($commdify as &$value) {
                        if ($value['type'] == 1) {
                            $value['cname'] = $bottleTypeData[$value['norm_id']]['bottle_name'];
                        } else {
                            $value['cname'] = $productTypeData[$value['norm_id']]['name'];
                        }
                    }
                    unset($value);
                    //return $commdify[0];
                    $d['type']=$commdify[0]['cname'];
                }else{
                    //return null;
                    $d['type']='';
                }
                //$d['type'] = LibF::M('bottle_type')->field('bottle_name')->where(['id'=>$d['goods_id']])->find()['bottle_name'];
            }
        }
        unset($d);
        return $data;
    }

    /**
     * 获取指定订单信息
     * 
     * @param $params
     */
    public function getOrderInfo($params) {
        if (empty($params))
            return FALSE;

        $data = LibF::M('order')->where($params)->order('order_id DESC')->find();
        return $data;
    }

    /**
     * 获取订单详情
     * 
     * @param $params
     */
    /*public function getOrderList($params) {
        if (empty($params))
            return FALSE;

        $data = LibF::M('order_info')->where($params)->select();
        return $data;
    }*/

    /**
     * 获取当前定气统计
     * 
     * @param $params
     */
    public function getOrderTotal($params) {

        $data = LibF::M('order')->where($params)->count();
        return $data;
    }

    /**
     * 获取档期定气总金额
     * 
     * @param $params
     */
    public function getOrderSum($params) {

        $data = LibF::M('order')->where($params)->sum('money');
        return $data;
    }

    /**
     * 订单创建
     * 
     * @param $params
     */
    public function add($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['order_sn'] = $params['order_sn'];  //订单号
        $data['otime'] = time(); //创建订单时间
        
        //客户信息
        if(isset($params['kid']) && !empty($params['kid']))
            $data['kid'] = $params['kid']; //订单用户ID
        
        $data['username'] = $params['username']; //订单用户姓名
        $data['mobile'] = $params['mobile']; //订单用户手机号

        $data['address'] = $params['address'];   //订单地址
        $data['comment'] = $params['comment'];   //订单备注

        if(isset($params['kehu_type']) && !empty($params['kehu_type']))
            $data['kehu_type'] = $params['kehu_type'];      //客户类型
        
        //配送门店信息
        if(isset($params['shop_id']) && !empty($params['shop_id']))
            $data['shop_id'] = $params['shop_id'];  //门店ID
        if(isset($params['shop_name']) && !empty($params['shop_name']))
            $data['shop_name'] = $params['shop_name'];  //门店名称
        if(isset($params['shop_mobile']) && !empty($params['shop_mobile']))
            $data['shop_mobile'] = $params['shop_mobile']; //门店手机号
        //$data['shop_type'] = $params['shop_type'];  //门店类型
        //配送送气工信息
        if($params['shipper_id'] && $params['shipper_mobile']){
            $data['shipper_id'] = $params['shipper_id']; //送气工ID
            //$data['shipper_name'] = $params['shipper_name']; //送气工名称
            $data['shipper_mobile'] = $params['shipper_mobile']; //送气工手机号
        }
        $data['ctime'] = $params['ctime'];

        if ($id) {
            $status = LibF::M('order')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('order')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 重组订单数据列表（App）
     * 
     * @param param 条件
     * @param order 排序条件
     * 
     * @app用户订单列表
     */
    public function orderListApp($param = '', $order = '', $bottleTypeData, $productTypeData, $shopObject = array()) {
        $data = $this->getUserOrderList($param, $order);
        $returnData = array();
        if (!empty($data)) {
            $orderSn = $qkorderSn = array();
            
            if (empty($shopObject)) {
                $shopModel = new ShopModel();
                $shopObject = $shopModel->getShopArray();
            }

            $regionModel = new RegionModel();
            $regionObject = $regionModel->getRegionObject();
            foreach ($data as $value) {
                $oVal['order_id'] = $value['order_id'];
                $oVal['ordersn'] = $value['order_sn'];
                $oVal['status'] = $value['status'];  //1未派发 2配送中 4已送达 5 已取消订单
                $oVal['mobile'] = $value['mobile'];

                $oVal['kid'] = $value['kid'];
                $oVal['username'] = $value['username'];
                $oVal['ktype'] = $value['kehu_type'];

                $oVal['shop_id'] = $value['shop_id'];
                $oVal['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : $value['shop_name'];

                $oVal['isreport'] = $value['is_safe']; //是否安全报告 1有 0没有
                $oVal['selfjson'] = $value['selfjson']; //有问题项安全报告
                $oVal['is_urgent'] = $value['is_urgent']; //是否紧急订单1是0否
                $oVal['depreciation'] = $value['is_old']; //是否有折旧 1有 0无
                $oVal['is_more'] = $value['is_more']; //是否有余气 1有0无
                $oVal['isevaluation'] = $value['is_evaluation']; //是否评价 0未评价 1已评价
                $oVal['is_discount'] = $value['is_discount']; //是否使用积分 0未 1有
                $oVal['point'] = $value['point']; //使用的积分额度
                $oVal['is_settlement'] = $value['is_settlement']; //是否欠款 1是 0 否
                $oVal['is_balance'] = $value['is_balance']; //是否使用余额 1是 0 否
                $oVal['balance'] = $value['balance']; //使用的余额

                $oVal['ispayment'] = $value['ispayment']; //是否付款 0未付款 1已付款
                $oVal['isgoods'] = $value['isgoods']; //是否收货 0未收货 1已收货
                
                $oVal['order_paytype'] = $value['order_paytype']; //付款方式0现金1网上支付

                $oVal['shipment'] = $value['shipment']; //运费
                $oVal['raffinat'] = $value['raffinat']; //残液
                $oVal['raffinat_weight'] = $value['raffinat_weight']; //残液重量
                $oVal['residual_gas'] = $value['residual_gas']; //余气
                $oVal['residual_gas_weight'] = $value['residual_gas_weight']; //余气重量
                $oVal['deposit'] = $value['deposit']; //押金
                $oVal['product_pice'] = $value['product_pice']; //配件
                $oVal['depreciation'] = $value['depreciation']; //折旧
                $oVal['order_tc_type'] = $value['order_tc_type']; //套餐类型0正常订单4体验套餐5优惠套餐

                $oVal['delivery'] = $oVal['time'] = date('Y-m-d', $value['good_time']);
                $oVal['ctime'] = $oVal['ctime'] = date('Y-m-d', $value['ctime']);
                $oVal['sheng'] = $value['sheng'];
                $oVal['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                $oVal['shi'] = $value['shi'];
                $oVal['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                $oVal['qu'] = $value['qu'];
                $oVal['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                $oVal['cun'] = $value['cun'];
                $oVal['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                $oVal['address'] = $value['address'];
                $oVal['total'] = $value['money'];
                $oVal['pay_money'] = $value['pay_money'];
                $oVal['yh_money'] = $value['is_yh_money'];//余额券价格
                $oVal['yhq_money'] = $value['discountmoney'];//优惠券价格
                $oVal['shipper_money'] = $value['shipper_money'];
                
                $oVal['workersid'] = $value['shipper_id'];
                $oVal['workersname'] = $value['shipper_name'];
                $oVal['workersmobile'] = !empty($value['shipper_mobile']) ? $value['shipper_mobile'] : '';
                $oVal['comment'] = $value['comment'];
                $oVal['bottle_data'] = !empty($value['bottle_data']) ? json_decode($value['bottle_data'], true) : array();
                $oVal['type'] = array();

                //获取当前订单欠费金额
                $oVal['is_settlement'] = $value['is_settlement'];
                $oVal['settlement_money'] = $value['settlement_money'];
                $oVal['settlement_deposit'] = $value['settlement_deposit'];
                $oVal['is_settlement_money'] = $value['is_settlement_money'];
                if ($value['is_settlement'] == 2) {
                    $qkorderSn[] = "'" . $value['order_sn'] . "'";
                }

                $oVal['zpbottle'] = !empty($value['zbottle']) ? json_decode($value['zbottle'], TRUE) : array();
                $oVal['kpbottle'] = !empty($value['kbottle']) ? json_decode($value['kbottle'], true) : array();

                $returnData[$oVal['ordersn']] = $oVal;
                $orderSn[] = "'" . $value['order_sn'] . "'";
            }
            $p['ordersn'] = $orderSn;
            $dataInfo = $this->getUserOrderInfoList($p);
            if (!empty($dataInfo)) {
                foreach ($dataInfo as $val) {
                    $iVal['goods_id'] = $val['goods_id'];
                    $iVal['title'] = $val['goods_name'];
                    if ($val['goods_type'] == 1) {
                        $iVal['goods_kind'] = $bottleTypeData[$val['goods_kind']]['bottle_name'];
                    } else {
                        $iVal['goods_kind'] = $productTypeData[$val['goods_kind']]['name'];
                    }
                    $iVal['norm_id'] = $val['goods_kind'];
                    $iVal['goods_price'] = $val['goods_price'];
                    $iVal['good_zhekou'] = $val['good_zhekou'];
                    $iVal['pay_money'] = $val['pay_money'];
                    $iVal['goods_type'] = $val['goods_type'];
                    $iVal['num'] = $val['goods_num'];
                    $iVal['goods_premium'] = $val['goods_premium'];
                    if (isset($returnData[$val['order_sn']]))
                        $returnData[$val['order_sn']]['type'][] = $iVal;
                }
            }

            if (!empty($qkorderSn)) {
                $qkDataInfo = $this->getUserDepositList($qkorderSn);
                if (!empty($qkDataInfo)) {
                    foreach ($qkDataInfo as $qkVal) {
                        $qkV['shipper_id'] = $qkVal['shipper_id'];
                        $qkV['type'] = $qkVal['arrears_type'];
                        $qkV['contractno'] = $qkVal['contractno'];
                        $qkV['money'] = $qkVal['money'];
                        $qkV['is_return'] = $qkVal['is_return'];
                        if (isset($returnData[$qkVal['order_sn']]))
                            $returnData[$qkVal['order_sn']]['arrears'][] = $qkV;
                    }
                }
            }
        }
        return $returnData;
    }

    /**
     * App送气工订单列表
     * 
     * 重组订单数据列表（App）
     * 
     */
    public function orderWorkApp($param = '') {
        $data = $this->getUserOrderList($param);
        $returnData = array();
        if (!empty($data)) {

            $orderSn = array();
            foreach ($data as $value) {
                $oVal['ordersn'] = $value['order_sn'];
                $oVal['type'] = $value['status'];
                $oVal['ispayment'] = 0; //是否付款 0未付款 1已付款
                $oVal['isgoods'] = 0; //是否收货 0未收货 1已收货
                $oVal['isevaluation'] = 0; //是否评价 0未评价 1已评价
                $oVal['isreport'] = $value['is_safe']; //是否安全报告 0有 1没有
                $oVal['delivery'] = $oVal['time'] = date('Y-m-d', $value['good_time']);
                $oVal['address'] = $value['address'];
                $oVal['total'] = $value['money'];
                $oVal['depreciation'] = $value['is_old']; //是否有折旧 1有 0无
                $oVal['workersname'] = $value['shipper_name'];
                $oVal['workersmobile'] = !empty($value['shipper_mobile']) ? $value['shipper_mobile'] : '';
                $oVal['data'] = array();

                $returnData[$oVal['ordersn']] = $oVal;
                $orderSn[] = "'" . $value['order_sn'] . "'";
            }
            $dataInfo = $this->getUserOrderInfoList($orderSn);
            if (!empty($dataInfo)) {
                foreach ($dataInfo as $val) {
                    $iVal['title'] = $val['goods_name'];
                    $iVal['num'] = $val['goods_num'];
                    if (isset($returnData[$val['order_sn']]))
                        $returnData[$val['order_sn']]['data'][] = $iVal;
                }
            }
        }
        return $returnData;
    }

    /**
     * App送气工订单列表
     * 
     * 重组订单数据列表（App）
     * 
     */
    public function noOrderMoneyApp($param = '') {
        $data = $this->getUserOrderList($param);
        $returnData = array();
        if (!empty($data)) {
            
            $orderSn = array();
            foreach ($data as $value) {
                $oVal['order_id'] = $value['order_id'];
                $oVal['oid'] = $value['order_sn'];
                $oVal['type'] = $value['status'];
                $oVal['kehu'] = $value['username'];
                $oVal['daynum'] = 0;
                $oVal['ispayment'] = 0; //是否付款 0未付款 1已付款
                $oVal['isgoods'] = 0; //是否收货 0未收货 1已收货
                $oVal['isevaluation'] = 0; //是否评价 0未评价 1已评价
                $oVal['isreport'] = $value['is_safe']; //是否安全报告 0有 1没有
                $oVal['delivery'] = $oVal['time'] = date('Y-m-d', $value['good_time']);
                $oVal['address'] = $value['address'];
                $oVal['money'] = $value['money'];
                $oVal['depreciation'] = $value['is_old']; //是否有折旧 1有 0无
                $oVal['workersname'] = $value['shipper_name'];
                $oVal['workersmobile'] = !empty($value['shipper_mobile']) ? $value['shipper_mobile'] : '';
                $oVal['comment'] = $value['comment'];
                $oVal['data'] = array();

                $returnData[$oVal['ordersn']] = $oVal;
                $orderSn[] = "'" . $value['order_sn'] . "'";
            }
            $dataInfo = $this->getUserOrderInfoList($orderSn);
            if (!empty($dataInfo)) {
                foreach ($dataInfo as $val) {
                    $iVal['title'] = $val['goods_name'];
                    $iVal['num'] = $val['goods_num'];
                    if (isset($returnData[$val['order_sn']]))
                        $returnData[$val['order_sn']]['data'][] = $iVal;
                }
            }
        }
        return $returnData;
    }

    /**
     * 欠款订单详情列表
     */
    public function getUserDepositList($params = '') {
        $ordersn = !empty($params) ? $params : array();

        $arrearsModel = LibF::M('order_arrears');
        $where['type'] = 1;
        if (!empty($ordersn)) {
            if (!is_array($ordersn))
                $where = array('order_sn' => $ordersn);
            else
                $where = "order_sn IN(" . implode(',', $ordersn) . ")";
        }

        $data = $arrearsModel->where($where)->order('id DESC')->select();
        return $data;
    }

}