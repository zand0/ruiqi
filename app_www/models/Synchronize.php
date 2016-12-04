<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SynchronizeModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    /**
     * 充装瓶子 同步瓶子的详细信息 同步外部接口
     * 
     */
    public function synchronizebottleAction(){
        $data = array(); //获取的js串
        
        $returnData = array('code' =>0);
        if(!empty($data)){
            $addData['number'] = '';
            $addData['xinpian'] = '';
            $addData['bar_code'] = '';
            $addData['type'] = '';
            $addData['filling_no'] = '';
            $addData['total'] = '';
            $addData['time'] = time();
            
            $status = LibF::M('bottle_tb')->add();
            if($status){
                LibF::M('bottle')->where(array('xinpian' => $addData['xinpian']))->save(array('status'=>2));
                $returnData['code'] = 1;
            }else{
                $returnData['msg'] = '存储失败';
            }
        }
        return $status;
    }
    
    public function initializationbottleAction() {
        $data = array();
        
        $returnData = array('code' =>0);
        if(!empty($data)){
            $addData['number'] = '';
            $addData['xinpian'] = '';
            $addData['bar_code'] = '';
            $addData['type'] = '';
            $addData['production_date'] = time();
            $addData['chang_name'] = '';
            
            $status = LibF::M('bottle')->add();
            if($status){
                $returnData['code'] = 1;
            }else{
                $returnData['msg'] = '提交失败';
            }
        }
        return $status;
    }
    
    /**
     * 同步当前门店应收款库存表synchronize_accounts
     * 
     * 根据未付款统计数据基于时间统计当前条件暂时未加
     * 统计未付款的用户
     */
    public function tbAccounts(){
        
        $where = "status != 5";
        
        $status = 0;
        $data = LibF::M('order')->field("sum(money) as money,shop_id,shop_name,shop_type,shop_mobile")->where($where)->group("shop_id")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['all_money'] = $value['money'];
                $insertVal['money'] = $value['money'];
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = $value['shop_name'];
                $insertVal['shop_type'] = $value['shop_type'];
                $insertVal['shop_mobile'] = $value['shop_mobile'];
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_accounts')->add($insertVal);
                }
            }
        }
        return $status;
    }
    
    /**
     * 根据订单详情表统计瓶子类型统计数量synchronize_bottlesales
     * 
     * 
     */
    public function tbBottle(){
        $where = "goods_type = 1";
        
        $status = 0;
        $data = LibF::M('order_info')->field("sum(pay_money) as money,sum(goods_num) as num,shop_id,goods_id,goods_name")->where($where)->group("shop_id,goods_id")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['money'] = $value['money'];
                $insertVal['type'] = $value['goods_id'];
                $insertVal['type_name'] = $value['goods_name'];
                $insertVal['num'] = $value['num'];
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = '';
                $insertVal['shop_type'] = '';
                $insertVal['shop_mobile'] = '';
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_bottlesales')->add($insertVal);
                }
            }
        }
        return $status;
    }

    /**
     * 押金列表 同步
     * 
     */
    public function tbDeposit(){
        
    }
    
    /**
     * 残液列表 同步
     * 
     */
    public function tbRaffinate(){
        $where = "status != 1";
        
        $status = 0;
        $data = LibF::M('order')->field("sum(raffinat) as money,shop_id,shop_name,shop_type,shop_mobile")->where($where)->group("shop_id")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['money'] = !empty($value['money']) ? $value['money'] : 0;
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = $value['shop_name'];
                $insertVal['shop_type'] = $value['shop_type'];
                $insertVal['shop_mobile'] = $value['shop_mobile'];
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_raffinate')->add($insertVal);
                }
            }
        }
        return $status;
    }
    
    /**
     * 运费列表 同步
     * 
     */
    public function tbShipment(){
        $where = "status != 1";
        
        $status = 0;
        $data = LibF::M('order')->field("sum(shipment) as money,shop_id,shop_name,shop_type,shop_mobile")->where($where)->group("shop_id")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['money'] = !empty($value['money']) ? $value['money'] : 0;
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = $value['shop_name'];
                $insertVal['shop_type'] = $value['shop_type'];
                $insertVal['shop_mobile'] = $value['shop_mobile'];
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_shipment')->add($insertVal);
                }
            }
        }
        return $status;
    }
    
    /**
     * 同步门店销售额按季度按月按年
     * 
     * $type 1按月2按季度3按年
     */
    public function tbOrderlist($type = 1){
        $where = "status != 5";
        
        $status = 0;
        $data = LibF::M('order')->field("sum(money) as money,count(*) as num,shop_id,shop_name,shop_type,shop_mobile")->where($where)->group("shop_id")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['num'] = $value['num'];
                $insertVal['type'] = $type;
                $insertVal['money'] = $value['money'];
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = $value['shop_name'];
                $insertVal['shop_type'] = $value['shop_type'];
                $insertVal['shop_mobile'] = $value['shop_mobile'];
                $insertVal['shop_level'] = 1;
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_sales')->add($insertVal);
                }
            }
        }
        return $status;
    }
    
    /**
     * 门店新用户统计数量
     * 
     */
    public function tbUser(){
        $where = "";
        
        $status = 0;
        $data = LibF::M('kehu')->field("count(*) as num,shop_id")->where($where)->group("shop_id")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['num'] = $value['num'];
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = '';
                $insertVal['shop_type'] = '';
                $insertVal['shop_mobile'] = '';
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_usercount')->add($insertVal);
                }
            }
        }
        return $status;
    }
    
    /**
     * 门店同步客户销售额统计
     * 
     */
    public function tbUserSales(){
        $where = "";
        
        $status = 0;
        $data = LibF::M('order')->field("sum(money) as money,count(*)as num,shop_id,shop_name,shop_type,shop_mobile,kid,username,mobile")->where($where)->group("shop_id,kid")->select();
        if(!empty($data)){
            
            foreach($data as $value){
                $insertVal = array();
                $insertVal['num'] = $value['num'];
                $insertVal['money'] = $value['money'];
                $insertVal['shop_id'] = $value['shop_id'];
                $insertVal['shop_name'] = $value['shop_name'];
                $insertVal['shop_type'] = $value['shop_type'];
                $insertVal['shop_mobile'] = $value['shop_mobile'];
                $insertVal['kid'] = $value['kid'];
                $insertVal['mobile_phone'] = $value['mobile'];
                $insertVal['user_name'] = $value['username'];
                
                if(!empty($insertVal)){
                    LibF::M('synchronize_usersales')->add($insertVal);
                }
            }
        }
        return $status;
    }
}
