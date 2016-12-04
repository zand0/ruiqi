<?php

/**
 * @abstract 获取相关统计数据函数
 * 
 * @author  jinyue.wang
 * @date 2016/03/07
 */
class StatisticsDataModel extends \Com\Model\Base {
    
//根据条件获取当前销售统计
    public function salesData() {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $orderModel = LibF::M('order');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';

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
    
//根据条件获取销售额总量
//@startTime 开始时间
//@endTime 结束时间
    public function salesTotal($startTime = 0, $endTime = 0, $shop_id = 0, $status = 0,$is_pay = 0) {

        if ($startTime > 0 && $endTime > 0) {
            $where['ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            $where['ctime'] = array('egt', $startTime);
        }
        //指定门店条件
        if ($shop_id) {
            $where['shop_id'] = $shop_id;
        }
        if ($status) {
            $where['status'] = $status;
        }
        if ($is_pay)
            $where['is_settlement'] = $is_pay;
        
        $data = LibF::M('order')->field('sum(pay_money-deposit+is_settlement_money) as total,sum(deposit - is_settlement_money) as yjtotal')->where($where)->find();
        $total = $data['total'] ? $data['total'] : 0;
        return $total;
    }

//根据条件获取天销售额
    public function salesDayData($startTime = 0,$shop_id = 0,$status=0) {
        $data = array();
        if ($startTime > 0) {
            $where['ctime'] = array('egt', $startTime);
            if ($shop_id)
                $where['shop_id'] = $shop_id;
            if ($status)
                $where['status'] = $status;

            $data = LibF::M('order')->field("sum(pay_money-deposit+is_settlement_money) as total,DATE_FORMAT(FROM_UNIXTIME(ctime),'%Y-%m-%d') as timeday")->where($where)->group('timeday')->select();
        }

        return $data;
    }
    
//根据条件获取门店应收款
    public function reportShopData($shop_id = 0,$param = array()) {
        $where['money'] = array('gt', 0);
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        $data = LibF::M('kehu')->field("sum(money) as total,shop_id")->where($where)->group('shop_id')->order('total desc')->limit($pageStart,$param['pagesize'])->select();

        return $data;
    }
    
//根据条件获取门店应收款
    public function reportShopMoney($shop_id = 0, $param = array()) {
        $orderAreasModel = new Model('order_arrears');
        $filed = " sum(rq_order_arrears.money) as money,rq_order_arrears.arrears_type,rq_kehu.shop_id ";
        $leftJoin = " LEFT JOIN rq_kehu ON rq_order_arrears.kid = rq_kehu.kid ";

        if ($shop_id) {
            $where['rq_kehu.shop_id'] = $shop_id;
        } else {
            $where['rq_kehu.shop_id'] = array('neq', 1);
        }

        if (isset($param['start_time']) && isset($param['end_time'])) {
            if ($param['start_time'] > 0 && $param['end_time'] > 0) {
                $where['rq_order_arrears.time'] = array(array('egt', $param['start_time']), array('elt', $param['end_time']), 'AND');
            } else {
                $where['rq_order_arrears.time'] = array('egt', $param['start_time']);
            }
        }

        $where['rq_order_arrears.is_return'] = 0;
        $where['rq_order_arrears.type'] = 1;
        $where['rq_order_arrears.status'] = 1;

        $data = $orderAreasModel->join($leftJoin)->field($filed)->where($where)->group('rq_kehu.shop_id,rq_order_arrears.arrears_type')->select();
        return $data;
    }

//根据条件获取门店销售额
    public function salesShopData($startTime = 0, $endTime = 0, $shop_id = 0, $status = 0, $param = array(), $is_pay = 0) {
        $data = array();
        if ($startTime > 0 && $endTime > 0) {
            $where['rq_order.ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            if ($startTime)
                $where['rq_order.ctime'] = array('egt', $startTime);
        }
        if ($shop_id) {
            $where['rq_order.shop_id'] = $shop_id;
        }
        if ($status) {
            $where['rq_order.status'] = $status;
        }
        if ($is_pay)
            $where['rq_order.is_settlement'] = $is_pay;

        $orderModel = new Model('order');
        $filed = " rq_order.shop_id,rq_shop.shop_name,rq_shop.admin_name,sum(rq_order.pay_money - rq_order.deposit + rq_order.is_settlement_money) as total,sum(rq_order.deposit - rq_order.is_settlement_money) as yjtotal";
        $leftJoin = " LEFT JOIN rq_shop ON rq_order.shop_id = rq_shop.shop_id ";
        if(!empty($param)){
            $pageStart = ($param['page'] - 1) * $param['pagesize'];
            $data = $orderModel->join($leftJoin)->field($filed)->where($where)->group('rq_order.shop_id')->order('total desc')->limit($pageStart,$param['pagesize'])->select();
        }else{
            $data = $orderModel->join($leftJoin)->field($filed)->where($where)->group('rq_order.shop_id')->order('total desc')->select();
        }
        //暂时增加统计数据
        if (!empty($data)) {
            
            //获取门店负责人
            //$shopAdminUser = $this->getStoreAdminUser();
            foreach ($data as &$value) {
                $total += $value['total'];
                $value['shop_name'] = !empty($value['shop_name']) ? $value['shop_name'] : '未分配门店';
                $value['user_name'] = !empty($value['admin_name']) ? $value['admin_name'] : '';
                //$value['user_name'] = isset($shopAdminUser[$value['shop_id']]) ? $shopAdminUser[$value['shop_id']]['real_name'] : '';
            }
        }
        return $data;
    }
    
//根据条件获取门店用户销售额
    public function salesShopUserData($startTime = 0, $endTime = 0, $shop_id = 0, $status = 0) {

        $where = array();
        if ($startTime > 0 && $endTime > 0) {
            $where['rq_order.ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            if ($startTime)
                $where['rq_order.ctime'] = array('egt', $startTime);
        }
        if ($shop_id) {
            $where['rq_order.shop_id'] = $shop_id;
        }
        if ($status) {
            $where['rq_order.status'] = $status;
        }

        $orderModel = new Model('order');
        $filed = " rq_order.shop_id,rq_shop.shop_name,rq_order.kid,rq_kehu.user_name,sum(rq_order.money) as total ";
        $leftJoin = " LEFT JOIN rq_shop ON rq_order.shop_id = rq_shop.shop_id ";
        $leftJoin2 = " LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid ";
        $data = $orderModel->join($leftJoin)->join($leftJoin2)->field($filed)->where($where)->group('rq_order.shop_id,rq_order.kid')->select();
        return $data;
    }

//获取当前用户统计
    public function kehuTotal($startTime = 0, $endTime = 0, $shop_id = 0) {
        $data = array();
        if ($startTime > 0 && $endTime > 0) {
            $where['ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            $where['ctime'] = array('egt', $startTime);
        }
        $where['status'] = 0;
        if ($shop_id)
            $where['shop_id'] = $shop_id;
        $data = LibF::M('kehu')->where($where)->count();
        return $data;
    }

//获取门店统计
    public function kehuData($startTime = 0, $endTime = 0, $shop_id = 0, $param = array()) {
        $data = array();
        if ($startTime > 0 && $endTime > 0) {
            $where['rq_kehu.ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            $where['rq_kehu.ctime'] = array('egt', $startTime);
        }
        
        $where['rq_kehu.status'] = 0;
        if ($shop_id)
            $where['rq_kehu.shop_id'] = $shop_id;

        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        
        $kehuModel = new Model('kehu');
        $filed = " rq_kehu.shop_id,rq_shop.shop_name,rq_shop.admin_name,count(rq_kehu.kid) as total ";
        $leftJoin = " LEFT JOIN rq_shop ON rq_kehu.shop_id = rq_shop.shop_id ";
        $data = $kehuModel->join($leftJoin)->field($filed)->where($where)->group('rq_kehu.shop_id')->order('total desc')->limit($pageStart,$param['pagesize'])->select();
        if(!empty($data)){
            
            //获取门店负责人
            //$shopAdminUser = $this->getStoreAdminUser();
            foreach ($data as &$value) {
                $total += $value['total'];
                
                $value['shop_name'] = !empty($value['shop_name']) ? $value['shop_name'] : '未分配门店';
                //$value['user_name'] = isset($shopAdminUser[$value['shop_id']]) ? $shopAdminUser[$value['shop_id']]['real_name'] : '';
                $value['user_name'] = $value['admin_name'];
            }
        }

        return $data;
    }

//根据条件获取天销售额
    public function kehuDayData($startTime = 0) {
        $data = array();
        if ($startTime > 0) {
            $where['ctime'] = array('egt', $startTime);
            $where['status'] = 0;
            $data = LibF::M('kehu')->field("count(*) as total,DATE_FORMAT(FROM_UNIXTIME(ctime),'%Y-%m-%d') as timeday")->where($where)->group('timeday')->select();
        }

        return $data;
    }
    
//根据条件获取订单统计数量
//@startTime 开始时间
//@endTime 结束时间
    public function orderTotal($startTime = 0, $endTime = 0, $shop_id = 0, $status = 0) {

        if ($startTime > 0 && $endTime > 0) {
            $where['ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            if($startTime >0)
                $where['ctime'] = array('egt', $startTime);
        }
        //指定门店条件
        if ($shop_id) {
            $where['shop_id'] = $shop_id;
        }
        //应收款
        if ($status) {
            $where['status'] = $status; //当状态等于3意味着是应收款 
        }
        $total = LibF::M('order')->where($where)->count();

        return $total;
    }
    
//补充接口获取不同类型订单数量
    public function orderTypeTotal($startTime = 0, $endTime = 0, $shop_id = 0, $type = array()){
        $where = array();
        if ($startTime > 0 && $endTime > 0) {
            $where['rq_order.ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            if ($startTime > 0)
                $where['rq_order.ctime'] = array('egt', $startTime);
        }
        $where['rq_order.status'] = array('egt', 0);
        
        //指定门店条件
        if ($shop_id) {
            $where['rq_order.shop_id'] = $shop_id;
            $where['rq_kehu.ktype'] = array('egt', 1);

            $orderModel = new Model('order');
            $field = ' rq_order.kid,rq_kehu.user_name,rq_kehu.ktype,rq_order.status,count(*) as total ';
            $grouplist = ' rq_kehu.ktype,rq_order.status ';
            $data = $orderModel->join('LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid')->field($field)->where($where)->group($grouplist)->order('rq_order.status desc')->select();
        } else {
            $where['rq_shop.shop_type'] = array('egt', 1);
            $where['rq_kehu.ktype'] = array('egt', 1);

            $orderModel = new Model('order');
            $field = 'rq_order.shop_id,rq_shop.shop_name,rq_shop.shop_type,rq_order.kid,rq_kehu.user_name,rq_kehu.ktype,rq_order.status,count(*) as total';
            $grouplist = 'rq_order.shop_id,rq_shop.shop_type,rq_kehu.ktype,rq_order.status';
            $data = $orderModel->join('LEFT JOIN rq_shop ON rq_order.shop_id = rq_shop.shop_id')->join('LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid')->field($field)->where($where)->group($grouplist)->order('rq_order.status desc')->select();
        }
        return $data;
    }
    
//获取门店订单统计
    public function orderData($startTime = 0, $endTime = 0, $shop_id = 0) {
        $data = array();
        if ($startTime > 0 && $endTime > 0) {
            $where['ctime'] = array(array('egt', $startTime), array('elt', $endTime), 'AND');
        } else {
            $where['ctime'] = array('egt', $startTime);
        }
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $data = LibF::M('order')->field('count(*) as total,shop_id')->where($where)->group('shop_id')->order('total desc')->select();
        return $data;
    }
    
    //根据条件获取门店应收款
    public function orderShopData($shop_id = 0) {
        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;
        $data = LibF::M('order')->field("count(*) as total,shop_id")->where($where)->group('shop_id')->select();

        return $data;
    }
    
    //根据条件获取天销售额
    public function orderDayData($startTime = 0, $shop_id = 0) {
        $data = array();
        if ($startTime > 0) {
            $where['ctime'] = array('egt', $startTime);
            if ($shop_id > 0)
                $where['shop_id'] = $shop_id;
            $data = LibF::M('order')->field("count(*) as total,DATE_FORMAT(FROM_UNIXTIME(ctime),'%Y-%m-%d') as timeday")->where($where)->group('timeday')->select();
        }

        return $data;
    }

//统计送气工订单数据    
    public function shipperOrderData($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $shipperModel = LibF::M('shipper');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';

        $offset = ($page - 1) * $pageSize;
        $count = $shipperModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $shipperModel->where($where)->limit($offset, $pageSize)->order('order_no desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    //获取门店钢瓶数量
    public function getStoreInventory(){
        $data = LibF::M('store_inventory')->field('count(*) as total,shop_id')->group('shop_id')->select();
        return $data;
    }
    
    //获取门店相关负责人
    public function getStoreAdminUser($where = '') {
        $shopManagerData = array();
        
        $shopManager = LibF::M('admin_user')->where($where)->select();
        if (!empty($shopManager)) {
            foreach ($shopManager as $val) {
                $shopManagerData[$val['shop_id']] = $val;
            }
        }
        return $shopManagerData;
    }

}
