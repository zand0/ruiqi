<?php

/*
 * 欠款发货申请
 */

class ArrearsModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
        $this->tableD = LibF::M('arrears');
    }

    public function listdata($param = array()){
        $where = array();
        if(isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        
        if(isset($param['type']) && !empty($param['type']))
            $where['type'] = $param['type'];
        
        $data = LibF::M('arrears')->where($where)->order('time_created desc')->select();
        
        return $data;
    }
    
    public function add($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        if ($id) {
            $status = $this->tableD->where('id=' . $id)->save($params);
        } else {
            $status = $this->tableD->add($params);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, '操作成功', $status);
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

    public function arrearsList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        //$where = ' status=0 ';

        $offset = ($page - 1) * $pageSize;
        $count = $this->tableD->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $this->tableD->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function addmanager($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        if ($id) {
            $status = LibF::M('manager_arrears')->where('id=' . $id)->save($params);
        } else {
            $status = LibF::M('manager_arrears')->add($params);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, '操作成功', $status);
    }
    
    public function listmanager($param = array()) {
        $where = array();

        if (isset($param['status']) && !empty($param['status']))
            $where['status'] = $param['status'];

        $data = LibF::M('manager_arrears')->where($where)->order('time_created desc')->select();

        return $data;
    }

}