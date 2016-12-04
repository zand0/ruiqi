<?php

class GastjModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('gas_tj');
    }

    public function add($param, $roles = array()) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function edite($type = 'edite', $id, $data = array()) {
        if ($type == 'edite') {
            $where['gid'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200', '操作成功');
        } else {
            $where['gid'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList($where = array()) {
        return $this->_list($where);
    }

}
