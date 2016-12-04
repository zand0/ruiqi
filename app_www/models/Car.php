<?php

class CarModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('car');
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
        return $this->tableD->where($param)->order('car_id desc')->select();
    }

    public function carObject($car_id = 0) {
        if ($car_id > 0) {
            $data = $this->tableD->where($param)->find();
        } else {
            $result = $this->tableD->select();
            if (!empty($result)) {
                foreach ($result as $value) {
                    $data[$value['car_id']] = $value;
                }
            }
        }
        return $data;
    }

}