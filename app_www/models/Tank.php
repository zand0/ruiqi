<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class TankModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    public function add($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['tanksn'] = $params['tanksn'];
        $data['tank_name'] = $params['tank_name'];
        $data['gas_id'] = $params['gas_id'];
        $data['address'] = $params['address'];
        $data['stock'] = $params['stock'];
        $data['comment'] = $params['comment'];
        $data['work_time'] = $params['work_time'];

        if ($id) {
            $status = LibF::M('tank')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('tank')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function tankList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $id = isset($params['id']) ? $params['id'] : '';

        $tankModel = LibF::M('tank');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($id)
            $where = array('id' => $id);
        $where['status'] = 1;

        $offset = ($page - 1) * $pageSize;
        $count = $tankModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $tankModel->where($where)->limit($offset, $pageSize)->order('id desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function getTankArray($id = '', $where = array()) {

        $model = LibF::M('tank');
        if ($id) {
            return $model->where('id=' . $id)->find();
        } else {
            $tankObject = array();
            $data = $model->where($where)->select();
            if (!empty($data)) {
                if (empty($where)) {
                    foreach ($data as $value) {
                        $tankObject[$value['id']] = $value;
                    }
                } else {
                    foreach ($data as $value) {
                        $tankObject[$value['id']] = $value;
                    }
                }
            }
            return $tankObject;
        }
    }

}
