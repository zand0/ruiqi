<?php
class CustumModel extends Com\Model\Base {
    public function __construct() {
        $this->tableD = LibF::M('kehu');
    }
    public function add($param, $roles = array()) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200','操作成功');
        }
        return $this->logicReturn('1','操作失败');
    }

    /**
     * 修改用户
     */
    public function edite($type = 'edite', $id, $data = array(), $roles = array()) {
        if ($type == 'edite') {
            $where['kid'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200','操作成功');
        } else {
            $where['kid'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList($params = array()){
        $where = array();
        if (!empty($params['mobile_phone']))
            $where['mobile_phone'] = array('like',$params['mobile_phone']);
        if (!empty($params['ktype']))
            $where['ktype'] = $params['ktype'];
        if (!empty($params['kehu_sn']))
            $where['kehu_sn'] = array('like',$params['kehu_sn']."%");
        if (!empty($params['user_name']))
            $where['user_name'] = array('like',$params['user_name']."%");
        if (!empty($params['shop_id']))
            $where['shop_id'] = $params['shop_id'];
        if (!empty($params['source']))
            $where['source'] = $params['source'];
        if (!empty($params['paytype']))
            $where['paytype'] = $params['paytype'];
        if (!empty($params['status']))
            $where['status'] = $params['status'];
        if (!empty($params['address']))
            $where['address'] = array('like',$params['address']."%");

        $where = array_filter($where);
        
        return $this->_list($where);
    }
}