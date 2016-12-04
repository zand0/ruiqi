<?php

/**
 * 1.气站出入库 2.门店出入库 3.送气工出入库
 * 4.门店钢瓶详情 5送气工钢瓶详情 6用户钢瓶详情
 *
 * @author jinyue.wang 
 * @date 2016/05/12
 * @version 0.1.0
 */
class StockmethodModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    //方法一：气站出入库相关库存的增减
    public function GasstationsStock($param,$type = 1) {
        /**
         * data[fs_name:类型名称，fs_num:类型数量，fs_price:产品单价,fs_type_id：产品规格id,goods_type：产品类型,shop_id:出库对象]
         * $type 1 钢瓶 2配件
         */
        if (empty($param))
            return false;

        //判断当前库存产品是否存在
        $data['goods_type'] = $param['goods_propety'];
        $data['fs_type_id'] = $param['goods_type'];
        $data['fs_name'] = $param['goods_name'];
        $data['fs_price'] = $param['goods_price'];
        $data['fs_num'] = $param['goods_num'];
        $data['time'] = $param['time'];
        $data['ctime'] = $param['ctime'];
        $data['type'] = $type;
        $data['admin_user'] = $param['admin_user'];

        $where = array('type' => $data['type'], 'fs_type_id' => $data['fs_type_id'], 'goods_type' => $data['goods_type']);
        $showStore = LibF::M('filling_stock')->where($where)->find();

        if (empty($showStore)) {
            if (($param['type'] == 1)) {
                $status = LibF::M('filling_stock_log')->add($param);
                if ($status)
                    LibF::M('filling_stock')->add($data);
            }else{
                $status = 0;
            }
        } else {
            $status = LibF::M('filling_stock_log')->add($param);
            if ($status) {
                if ($param['type'] == 1) {//入库
                    LibF::M('filling_stock')->where($where)->setInc('fs_num', $data['fs_num']);
                } else {//出库
                    LibF::M('filling_stock')->where($where)->setDec('fs_num', $data['fs_num']);
                    //同时增加门店库存 text
                }
            }
        }
        return $status;
    }
    
    //方法二：门店相关出入库的增减
    public function ShopstationsStock($param, $shop_id,$type = 1) {
        /**
         * data[fs_name:类型名称，fs_num:类型数量，fs_price:产品单价,fs_type_id：产品规格id,goods_type：产品类型,shop_id:出库对象]
         * $type 1 钢瓶 2配件
         */
        if (empty($param))
            return false;

        $data['goods_type'] = $param['goods_propety']; //类型
        $data['fs_type_id'] = $param['goods_id']; //规格
        $data['fs_name'] = $param['goods_name'];
        $data['fs_price'] = !empty($param['goods_price']) ? $param['goods_price'] : 0;
        $data['fs_num'] = $param['goods_num'];
        $data['time'] = $param['time'];
        $data['ctime'] = $param['ctime'];
        $data['shop_id'] = $shop_id;
        if (!empty($param['shop_name']))
            $data['shop_name'] = $param['shop_name'];

        $data['type'] = $type; //1钢瓶2配件
        if (!empty($param['admin_user']))
            $data['admin_user'] = $param['admin_user'];

        //判断当前库存产品是否存在
        $where = array('type' => $data['type'], 'fs_type_id' => $data['fs_type_id'], 'goods_type' => $data['goods_type'], 'shop_id' => $shop_id);
        $showStore = LibF::M('filling_stock_shop')->where($where)->find();
        if (empty($showStore)) {
            if (($param['type'] == 1)) {
                $status = LibF::M('filling_stock_log_shop')->add($param);
                if ($status) {
                    LibF::M('filling_stock_shop')->add($data);
                }
            }else {
                $status = 0;
            }
        } else {
            $status = LibF::M('filling_stock_log_shop')->add($param);
            if ($status) {
                if ($param['type'] == 1) {
                    LibF::M('filling_stock_shop')->where($where)->setInc('fs_num', $data['fs_num']);
                } else {
                    LibF::M('filling_stock_shop')->where($where)->setDec('fs_num', $data['fs_num']);
                }
            }
        }
        return $status;
    }

    //方法三: 送气工相关出入库的增减
    public function ShipperstationsStock($param, $shipper_id, $shop_id = 0, $user_id = 0, $type = 1) {
        /**
         * data[fs_name:类型名称，fs_num:类型数量，fs_price:产品单价,fs_type_id：产品规格id,goods_type：产品类型,shop_id:出库对象]
         * $type 1 钢瓶 2配件
         */
        if (empty($param))
            return false;
        
        $data['goods_type'] = $param['goods_propety']; //类型
        $data['fs_type_id'] = isset($param['goods_id']) ? $param['goods_id'] : $param['goods_type']; //规格
        $data['fs_name'] = $param['goods_name'];
        $data['fs_num'] = $param['goods_num'];
        $data['time'] = $param['time'];
        $data['ctime'] = $param['ctime'];
        $data['shipper_id'] = $shipper_id;
        $data['type'] = $type;
        if (isset($param['admin_user']) && !empty($param['admin_user']))
            $data['admin_user'] = $param['admin_user'];
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $data['shop_id'] = $param['shop_id'];
        //if (isset($param['gtype']) && !empty($param['gtype']))
            //$data['gtype'] = $param['gtype'];

        //判断当前库存产品是否存在
        $where = array('type' => $data['type'], 'fs_type_id' => $data['fs_type_id'], 'goods_type' => $data['goods_type'], 'shipper_id' => $shipper_id);
        $showStore = LibF::M('filling_stock_shipper')->where($where)->find();
        if (empty($showStore)) {
            if (($param['type'] == 1)) {
                $status = LibF::M('filling_stock_log_shipper')->add($param);
                if ($status)
                    LibF::M('filling_stock_shipper')->add($data);
            }else {
                //没有库存无法出库
                $status = 1;
            }
        } else {
            $status = LibF::M('filling_stock_log_shipper')->add($param);
            if ($status) {
                if ($param['type'] == 1) {
                    LibF::M('filling_stock_shipper')->where($where)->setInc('fs_num', $data['fs_num']);
                } else {
                    LibF::M('filling_stock_shipper')->where($where)->setDec('fs_num', $data['fs_num']);
                }
            }
        }
        return $status;
    }

}
