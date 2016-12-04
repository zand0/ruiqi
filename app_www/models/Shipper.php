<?php
class ShipperModel extends \Com\Model\Base {
    public function __construct() {
        $this->tableD = LibF::M('shipper');
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
            $where['shipper_id'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200', '操作成功');
        } else {
            $where['shipper_id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    /**
     * 获取指定送气工信息
     * 
     * @param $shipper_id
     */
    public static function getShipperArray($shipper_id = '',$shop_id = ''){
        
        if(!empty($shipper_id)){
            return LibF::M('shipper')->where('shipper_id='.$shipper_id)->find();
        }else{
            $shipperObject = array();
            $where = '';
            if(!empty($shop_id))
                $where = array('shop_id' => $shop_id);
            
            $data = LibF::M('shipper')->where($where)->select();
            if(!empty($data)){
                foreach($data as $value){
                    $shipperObject[$value['shipper_id']] = $value;
                }
            }
            return $shipperObject;
        }
    }
    
    public function getList($param = array()) {
        
        $param['is_del'] = 0;
        return $this->tableD->where($param)->order('shipper_id desc')->select();
    }

}