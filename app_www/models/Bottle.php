<?php
class BottleModel extends Com\Model\Base {
    public function __construct() {
        $this->tableD = LibF::M('bottle');
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
            $where['gp_id'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200', '操作成功');
        } else {
            $where['id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public static function getBottleType($bottle_type = 1) {
        $where = array();
        if (!empty($bottle_type)) {
            $where['bottle_type'] = $bottle_type;
        }
        $where['status'] = 1;
        $res = LibF::M('bottle_type')->where($where)->select();
        $typeList = array();
        foreach ($res as $v) {
            $typeList[$v['id']] = $v['bottle_name'];
        }
        return $typeList;
    }
    
     public static function getBottleObject($bottle_type = 1) {
        $where = array();
        if (!empty($bottle_type)) {
            $where['bottle_type'] = $bottle_type;
        }
        $res = LibF::M('bottle_type')->where($where)->select();
        $typeList = array();
        foreach ($res as $v) {
            $typeList[$v['id']] = $v;
        }
        return $typeList;
    }

    public function getList($where){
        $where = array_filter($where);
        return $this->_list($where);
    }
    
    /**
     * 获取钢瓶相关信息数据
     */
    public function getBottleArray($xinpian = 0) {
        $model = LibF::M('bottle');
        if ($xinpian) {
            return $model->where('xinpian=' . $xinpian)->find();
        } else {
            $bottleObject = array();
            $data = $model->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $bottleObject[$value['xinpian']] = $value;
                }
            }
            return $bottleObject;
        }
    }
    
    public function bottleOData($bottle = array()) {
        $returnData = array();
        $model = LibF::M('bottle');
        $bottleObject = $bottleTypeObject = array();
        
        $where = array();
        if (!empty($bottle)) {
            //$bottleList = '"' . implode('","', $bottle) . '"';
            $where['number'] = array('in', $bottle);
            $where['xinpian'] = array('in', $bottle);
            $where['_logic'] = "OR";
        }
        $data = $model->where($where)->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $bottleObject[$value['xinpian']] = $value;
                $bottleTypeObject[$value['number']] = $value;
            }
            $returnData['xinpian'] = $bottleObject;
            $returnData['number'] = $bottleTypeObject;
        }
        return $returnData;
    }

    /**
     * 获取当前门店钢瓶
     */
    public function getStoreBottle($shop_id) {
        if (empty($shop_id))
            return false;

        $returnData = array();
        $data = LibF::M('store_inventory')->where(array('shop_id' => $shop_id, 'status' => 1, 'is_use' => 1))->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $bottleObject[$value['xinpian']] = $value;
                $bottleTypeObject[$value['number']] = $value;
            }
            $returnData['xinpian'] = $bottleObject;
            $returnData['number'] = $bottleTypeObject;
        }
        return $returnData;
    }

    /**
     * 获取当前钢瓶流转记录
     * 
     */
    public function getBottleLogList($param = array()) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $botleLogModel = LibF::M('bottle_log');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();

        $offset = ($page - 1) * $pageSize;
        $count = $botleLogModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, '添加成功', $data);
        }
        $rows = $botleLogModel->where($where)->limit($offset, $pageSize)->order('id desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, '添加成功', $data);
    }
    
    /**
     * 获取钢瓶流转记录
     * 
     */
    public function getTransferList($param = array()) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';
        
        $xinpian = isset($param['xinpian']) ? $param['xinpian'] : '';

        $botleLogModel = LibF::M('bottle_transfer_logs');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();
        if($xinpian)
            $where['xinpian'] = array('like',$xinpian.'%');
        
        $offset = ($page - 1) * $pageSize;
        $count = $botleLogModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, '添加成功', $data);
        }
        $rows = $botleLogModel->where($where)->limit($offset, $pageSize)->order('id desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, '添加成功', $data);
    }

}