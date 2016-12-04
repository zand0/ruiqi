<?php

class GasModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('gas');
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

    /**
     * 获取燃气类型
     */
    public static function getGasArray($id = 0) {
        $model = LibF::M('gas');
        if ($id) {
            return $model->where('status=1 and id=' . $id)->find();
        } else {
            $bottleObject = array();
            $data = $model->where('status=0')->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $bottleObject[$value['gid']] = $value;
                }
            }
            return $bottleObject;
        }
    }

}