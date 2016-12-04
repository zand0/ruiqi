<?php
/**
 * 配件统计
 */
class ProductstjModel extends Com\Model\Base {
    public function __construct() {
        $this->tableD = LibF::M('products_tj');
    }
    public function add($param, $roles = array()) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200','操作成功');
        }
        return $this->logicReturn('1','操作失败');
    }
    public function edite($type = 'edite', $id, $data = array()) {
        if ($type == 'edite') {
            $where['id'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200','操作成功');
        } else {
            $where['id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList($where){
        $where = array_filter($where);
        return $this->_list($where);
    }
}