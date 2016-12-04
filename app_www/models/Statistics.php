<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class StatisticsModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 门店销售额业绩排名统计
     * 
     * 备注：当前销售额统计只需要查询同步数据表，数据表每天凌晨更新同步数据
     * @param $shop_id
     * @param $type 1按照每月查询 2按照季度查询 3按照年查询
     * @param $show_type 1前十名 2后十名
     * @return array
     */
    public function sales($shop_id = 0, $type = 1, $show_type = 1, $num = 10) {

        $where = '';
        if ($type)
            $where = array('type' => $type);
        if($shop_id)
            $where['shop_id'] = $shop_id;
        
        $order = '';
        if ($type == 1) {
            $order = ' money DESC ';
        } else {
            $order = 'money ASC ';
        }

        $data = LibF::M('synchronize_sales')->where($where)->order($order)->limit($num)->select();

        return $data;
    }
    
    /**
     * 客户销售额统计
     * 
     * 备注:同步统计客户销售额度
     * 
     * @param $shop_id
     * @return array
     */
    public function userSales($shop_id = 0){
        $where = '';
        if($shop_id)
            $where['shop_id'] = $shop_id;

        $data = LibF::M('synchronize_usersales')->where($where)->order($order)->limit($num)->select();

        return $data;
    }

    /**
     * 当前店铺所有欠款和当前到期应收款
     * 
     * 备注：当前订单列表统计同步应收款，即将到期款
     * 
     * @param $shop_id
     * @return array
     */
    public function accounts($shop_id = 0) {
        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $data = LibF::M('synchronize_accounts')->where($where)->select();

        return $data;
    }

    /**
     * 新用户统计列表
     * 
     * 备注：当前新用户根据用户表统计用户数量
     * 
     * @param $shop_id
     * @return array
     */
    public function usercount($shop_id = 0){
        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $data = LibF::M('synchronize_usercount')->where($where)->select();

        return $data;
    }
    
    /**
     * 用户销售额统计
     * 
     * 备注：当前用户根据订单统计用户数据
     * 
     * @param $shop_id
     * @return array 
     */
    public function usersaleslist($shop_id = 0){
        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $data = LibF::M('synchronize_usersales')->where($where)->select();
        
        return $data;
    }
    
    /**
     * 瓶子类型销售额统计 按照门店统计
     * 
     * 备注：当前瓶子按照类型统计数据
     * 
     * @param $shop_id
     * @return array
     */
    public function bottlesales($shop_id = 0){
        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $data = LibF::M('synchronize_bottlesales')->where($where)->select();

        return $data;
    }
    
    /**
     * 瓶子类型销售统计 按照类型统计
     */
    public function bottlesalesType(){

        $data = LibF::M('synchronize_bottlesales')->field("sum(num) as num,sum(money) as money,type,type_name")->group("type")->select();

        return $data;
    }
    
    /**
     * 瓶子按照门店销售类型统计
     * 
     */
    public function bottlesalesShop(){
        $data = LibF::M('synchronize_bottlesales')->field("sum(num) as num,sum(money) as money,type,type_name,shop_id")->group("shop_id,type")->select();

        return $data;
    }
    
    /**
     * 统计燃气种类
     */
    public function gasStatics($shop_id = 0,$where = ''){
        if (!empty($shop_id))
            $where .=!empty($where) ? " AND rq_gas_tj.shop_id = " . $shop_id : " rq_gas_tj.shop_id = " . $shop_id;

        $order = new Model('gas_tj');
        $list = $order->join('LEFT JOIN rq_gas ON rq_gas_tj.gtype = rq_gas.gid')->field("rq_gas_tj.gnum,rq_gas.gas_name")->where($where)->select();

        return $list;
    }
    
    /**
     * 获取押金、使用费、上门费
     */
    public function depositList($shop_id = 0){
        $where = '';
        if ($shop_id)
            $where['shop_id'] = $shop_id;

        $returnData = array();
        $data = LibF::M('synchronize_deposit')->field("sum(money) as money,shop_id")->group("shop_id")->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $value['raffinate'] = 0;
                $value['shipment'] = 0;
                $returnData[$valu['shop_id']] = $value;
            }
        }

        //获取残液
        $sData = LibF::M('synchronize_raffinate')->field("sum(money) as money,shop_id")->group("shop_id")->select();
        if (!empty($sData)) {
            foreach ($sData as $sValue) {
                $returnData[$valu['shop_id']]['raffinate'] = $sValue['money'];
            }
        }

        //获取上门服务费
        $cData = LibF::M('synchronize_shipment')->field("sum(money) as money,shop_id")->group("shop_id")->select();
        if (!empty($cData)) {
            foreach ($sData as $sValue) {
                $returnData[$valu['shop_id']]['shipment'] = $sValue['money'];
            }
        }

        return $returnData;
    }
    
    /**
     * 钢瓶库存统计(门店)
     * 
     */
    public function bottleStaticsList($shop_id = 0) {
        
        $where = ' rq_store_inventory.is_use = 1 ';
        if (!empty($shop_id))
            $where = ' AND rq_store_inventory.shop_id = ' . $shop_id ." AND rq_bottle_price.shop_id = ".$shop_id;

        $order = new Model('store_inventory');
        $list = $order->join('LEFT JOIN rq_bottle_price ON rq_store_inventory.type = rq_bottle_price.bottle_id')->field("count(*) as num,rq_store_inventory.shop_id,rq_bottle_price.bottle_name,rq_store_inventory.is_open")->where($where)->group('rq_store_inventory.shop_id,rq_store_inventory.type,rq_store_inventory.is_open')->select();

        return $list;
    }

    /**
     * 钢瓶库存统计（气站）
     * 
     */
    public function bottleStaticsAll() {

        $where = " status IN(1,2) AND is_active = 1 AND is_used = 1 ";
        $list = LibF::M('bottle')->field("count(*) as num,type,status")->where($where)->group('type,status')->select();

        return $list;
    }

}