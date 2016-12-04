<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class InventoryModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function storelist($params = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $type = isset($params['type']) ? $params['type'] : '';
        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';
        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';

        $storeModel = LibF::M('store_inventory');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($type)
            $where['type'] = $type;
        if ($shop_id)
            $where['shop_id'] = $shop_id;
        if ($start_time && $end_time) {
            $where['time_created'] = array('egt', strtotime($start_time), 'AND');
            $where['time_created'] = array('elt', strtotime($end_time));
        }
        if ($status)
            $where['status'] = $status;

        $offset = ($page - 1) * $pageSize;
        $count = $storeModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $storeModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function warehouselist($params = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $type = isset($params['type']) ? $params['type'] : '';
        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';
        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';

        $warehousingModel = LibF::M('warehousing');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($type)
            $where['type'] = $type;
        if ($shop_id)
            $where['shop_id'] = $shop_id;
        if ($start_time && $end_time) {
            $where['time_created'] = array('egt', strtotime($start_time), 'AND');
            $where['time_created'] = array('elt', strtotime($end_time));
        }

        $offset = ($page - 1) * $pageSize;
        $count = $warehousingModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $warehousingModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

}