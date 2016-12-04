<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SupplierModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($data, $id = 0) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $status = LibF::M('supplier')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('supplier')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function dataList($params = array()){
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $supplierModel = LibF::M('supplier');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        
        $supplier_no = isset($params['supplier_no']) ? $params['supplier_no'] : '';
        if(!empty($supplier_no))
            $where['supplier_no'] = array('like',$supplier_no.'%');
        
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
        $count = $supplierModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $supplierModel->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function getSupplierObject($param = array()){
        $where = array();
        if(isset($param['type']) && !empty($param['type']))
            $where['type'] = $param['type'];
        
        $returnData = array();
        $data = LibF::m('supplier')->where($where)->select();
        if(!empty($data)){
            foreach($data as $value){
                $returnData[$value['id']] = $value;
            }
        }
        return $returnData;
    }
}
