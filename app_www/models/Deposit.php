<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DepositModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function depositList($params = array()) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';
        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';

        $status = isset($params['status']) ? $params['status'] : '';

        $kid = isset($params['kid']) ? $params['kid'] : '';

        $depositModel = LibF::M('deposit_list');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();
        if ($shop_id) {
            $where['shop_id'] = $shop_id;
        }
        if ($start_time && $end_time) {
            $where['time_created'] = array('egt', strtotime($start_time), 'AND');
            $where['time_created'] = array('elt', strtotime($end_time));
        }
        if ($status) {
            $where['status'] = $status;
        }
        if ($kid) {
            $where['kid'] = $kid;
        }

        $offset = ($page - 1) * $pageSize;
        $count = $depositModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $depositModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function editDeposit($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $params['ctime'] = time();

        if ($id) {
            $status = LibF::M('deposit_list')->where(array('id' => $id))->save($params);
        } else {
            $status = LibF::M('deposit_list')->add($params);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function shipperDeposit($shop_id = 0,$shipper_id = 0,$param = array()){
        $where = array();
        if($shop_id)
            $where['shop_id'] = $shop_id;
        if($shipper_id)
            $where['shipper_id'] = $shipper_id;
        if($param['status'])
            $where['status'] = $param['status'];

        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        
        $data = LibF::M('deposit_list')->where($where)->order('time_created desc')->limit($pageStart,$param['pagesize'])->select();
        
        return $data;
    }
    
}
