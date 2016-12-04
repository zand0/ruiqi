<?php

class ShipperproductModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('shipper_product');
    }

    public function add($param) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function edite($param, $id, $where) {
        $status = $this->_edite($id, $param, $where);
        if (!empty($status)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function getList($param = array()) {

        $param['status'] = 1;
        return $this->tableD->where($param)->order('id desc')->select();
    }

    public function dataList($params = array()) {
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $shipper_productModel = LibF::M('shipper_product');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();

        $offset = ($page - 1) * $pageSize;
        $count = $shipper_productModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $shipper_productModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

}