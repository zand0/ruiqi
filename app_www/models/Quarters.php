<?php
/**
 * 岗位管理 6.4
 * @date 2016/03/24
 */
class QuartersModel extends Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('quarters');
    }

    public function add($param) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    /**
     * 修改
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

    public function getQuarter() {
        $data = LibF::M('quarters')->select();
        return $data;
    }

    public function getQuartersData($quarters_id = 0) {
        $data = array();
        if ($quarters_id) {
            $data = LibF::M('quarters')->where(array('id' => $quarters_id))->find();
        } else {
            $return = LibF::M('quarters')->select();
            if (!empty($return)) {
                foreach ($return as $value) {
                    $data[$value['id']] = $value;
                }
            }
        }
        return $data;
    }

}