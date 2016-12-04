<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SpendingModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($data, $id = 0) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $status = LibF::M('procurement')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('procurement')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function dataList($params = array()){
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $procurementModel = LibF::M('procurement');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        
        $procurement_no = isset($params['procurement_no']) ? $params['procurement_no'] : '';
        if(!empty($procurement_no))
            $where['procurement_no'] = array('like',$procurement_no.'%');
        
        $name = isset($params['name']) ? $params['name'] : '';
        if(!empty($name))
            $where['name'] = array('like',$name.'%');
        
        $type = isset($params['type']) ? $params['type'] : '';
        if($type)
            $where['type'] = $type;
        
        $goods_type = isset($params['goods_type']) ? $params['goods_type'] : '';
        if($goods_type)
            $where['goods_type'] = $goods_type;

        $offset = ($page - 1) * $pageSize;
        $count = $procurementModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $procurementModel->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function dataTotal($params = array()) {
        
        $procurementModel = LibF::M('procurement');
        $data = $procurementModel->field("count(*) as num,sum(goods_num) as gnum,sum(money) as total,type,goods_type,goods_name")->group('type,goods_type')->select();
        return $data;
    }

    public function add_detect_server($data, $id = 0) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $status = LibF::M('detect_service')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('detect_service')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function detect_server_list($params = array()){
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $detectserviceModel = LibF::M('detect_service');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        
        $ds_no = isset($params['ds_no']) ? $params['ds_no'] : '';
        if(!empty($ds_no))
            $where['ds_no'] = array('like',$ds_no.'%');
        
        $name = isset($params['name']) ? $params['name'] : '';
        if(!empty($name))
            $where['name'] = array('like',$name.'%');
        
        $type = isset($params['type']) ? $params['type'] : '';
        if($type)
            $where['type'] = $type;

        $offset = ($page - 1) * $pageSize;
        $count = $detectserviceModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $detectserviceModel->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function detect_serverTotal($params = array()) {

        $where = '';
        if (isset($params['type']) && !empty($params['type']))
            $where .=!empty($where) ? " AND rq_detect_service.type = " . $params['type'] : " rq_detect_service.type = " . $params['type'];

        $dataModel = new Model('detect_service');
        $list = $dataModel->join('LEFT JOIN rq_detect_service_info ON rq_detect_service.ds_no = rq_detect_service_info.ds_no')->field("sum(rq_detect_service_info.money) as money,rq_detect_service_info.goods_price,sum(rq_detect_service_info.goods_num) as total,rq_detect_service_info.goods_name")->where($where)->group('rq_detect_service_info.goods_type')->select();
        
        return $list;
    }

}
