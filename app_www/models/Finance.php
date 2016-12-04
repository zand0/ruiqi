<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FinanceModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    public function getList($params = '', $table = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';
        $status = isset($params['status']) ? $params['status'] : '';

        $listModel = LibF::M($table);
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($start_time && $end_time) {
            $where['ctime'] = array('egt', strtotime($start_time), 'AND');
            $where['ctime'] = array('elt', strtotime($end_time));
        }
        if ($status)
            $where['status'] = $status;

        $offset = ($page - 1) * $pageSize;
        $count = $listModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $listModel->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function datalist($params = '', $table = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $listModel = LibF::M('delivery_detail');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();
        if ($start_time && $end_time) {
            $where['ctime'] = array('egt', strtotime($start_time), 'AND');
            $where['ctime'] = array('elt', strtotime($end_time));
        }
        if ($status)
            $where['status'] = $status;

        $offset = ($page - 1) * $pageSize;
        $dataAll = $listModel->where($where)->group('delivery_no,shop_id')->select();
        $count = count($dataAll);
        
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $listModel->field('shop_id,delivery_no,sum(money) as price,ctime')->where($where)->group('delivery_no,shop_id')->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
}

