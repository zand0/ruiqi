<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OprocurementModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function dataList($params = array()) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $procurementModel = LibF::M('other_procurement');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();
        
        $procurement_no = isset($params['procurement_no']) ? $params['procurement_no'] : '';
        if (!empty($procurement_no))
            $where['procurement_no'] = array('like', $procurement_no . '%');

        $zc_object = isset($params['zc_object']) ? $params['zc_object'] : '';
        if ($zc_object)
            $where['zc_object'] = $zc_object;

        $zc_form = isset($params['zc_form']) ? $params['zc_form'] : '';
        if ($zc_form)
            $where['zc_form'] = $zc_form;

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
    
}
