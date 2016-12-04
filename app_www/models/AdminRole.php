<?php

/**
 * 角色管理 6.3
 * @date 2016/03/24
 */
class AdminRoleModel extends Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('auth_role');
    }

    public function add($param) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    /**
     * 修改用户
     */
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

    public function getList($where = array()) {
        return $this->tableD->where('status=1')->order('id desc')->select();
    }

}
