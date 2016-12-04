<?php

class GaslogModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('gas_log');
    }

    public function add($param, $roles = array()) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function uadd($param, $options) {
        $insert_id = LibF::M('gas_tj')->uinsert($param, true, $options);
        if ($insert_id !== false) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function utank($param, $options) {
        $insert_id = LibF::M('tank')->uinsert($param, true, $options);
        if ($insert_id !== false) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function ugastank($param,$options){
        $insert_id = LibF::M('tank_gas')->uinsert($param, true, $options);
        if ($insert_id !== false) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }
    
    public function edite($type = 'edite', $id, $data = array()) {
        if ($type == 'edite') {
            $where['id'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200', '操作成功');
        } else {
            $where['id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList($params = array()) {
        
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        $type = isset($params['type']) ? $params['type'] : '';
        if($type == 0){
            $where['type'] = $type;
        }else if($type == 1){
            $where['type'] = $type;
        }
        $gtype = isset($params['gtype']) ? $params['gtype'] : '';
        if(!empty($gtype))
            $where['gtype'] = $gtype;
        
        $license_plate = isset($params['license_plate']) ? $params['license_plate'] : '';
        if(!empty($license_plate))
            $where['license_plate'] = array('like',$license_plate.'%');
        
        $start_time = isset($params['start_time']) ? $params['start_time'] : '';
        $end_time = isset($params['end_time']) ? $params['end_time'] : '';
        if($start_time && $end_time){
            $where['ctime'] = array(array('egt', strtotime($start_time)),array('elt', strtotime($end_time)),'AND');
        }

        $offset = ($page - 1) * $pageSize;
        $count = $this->tableD->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $this->tableD->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

}