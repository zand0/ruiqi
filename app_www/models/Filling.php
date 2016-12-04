<?php

class FillingModel extends Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('filling');
    }

    public function add($param) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }
    
    public function uadd($param, $options) {
        $insert_id = LibF::M('products_tj')->uinsert($param, true, $options);
        if ($insert_id !== false) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function edite($type = 'edite', $id, $data = array(), $roles = array()) {
        if ($type == 'edite') {
            $where['id'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200', '操作成功');
        } else {
            $where['id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList($where) {
        $where = array_filter($where);
        $res = $this->_list($where);
        if (!empty($res['list'])) {
            foreach ($res['list'] as &$v) {
                $v['bottle'] = json_decode($v['bottle'], true);
                $v['products'] = json_decode($v['products'], true);
                $v['gbottle'] = json_decode($v['gbottle'], true);
                $v['fbottle'] = json_decode($v['fbottle'], true);
            }
        }
        return $res;
    }

    /**
     * 添加数据
     * 
     * @date 2016/04/20
     * @version 1.0
     * @abstract 所有数据更新|添加操作调用当前方法
     */
    public function editData($data, $where = array(), $table) {
        if (empty($data) || empty($table)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($where) {
            $status = LibF::M($table)->where($where)->save($data);
        } else {
            $status = LibF::M($table)->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }
    /**
     * 数据列表
     * 
     * @date 2016/04/20
     * @version 1.0
     * @abstract 所有数据列表调用方法
     */
    public function getDataList($params, $table, $where = array(), $order = '') {
        if (empty($table))
            return FALSE;

        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;

        $dataModel = LibF::M($table);
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        if (empty($order)) {
            $orderList = 'ctime desc';
        } else {
            $orderList = $order;
        }

        $offset = ($page - 1) * $pageSize;
        $count = $dataModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $dataModel->where($where)->limit($offset, $pageSize)->order($orderList)->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    //多表查询
    public function getDataTableList($params, $table, $table1, $table2, $zd, $where = array(), $order = '', $field = '', $ozd = '') {
        if (empty($table) || empty($table1) || empty($table2) || empty($zd))
            return FALSE;

        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $orderList = '';
        if (!empty($order)) {
            $orderList = $order;
        }
        
        $fillingModel = new Model($table);
        if (empty($field) && $table == 'deposit'){
            $field = 'rq_deposit.order_sn,rq_deposit.kid,rq_kehu.user_name,rq_deposit.type,rq_deposit.number,rq_deposit.receiptno,rq_deposit.money,rq_deposit.price,rq_deposit.shop_id,rq_deposit.time,rq_deposit.deposit_type,rq_deposit.time_created';
        }else{
            if(empty($field)){
                $field = '*';
            }
        }

        if (!empty($ozd)) {
            $leftWhere = " LEFT JOIN " . $table2 . " ON " . $table1 . "." . $zd . " = " . $table2 . "." . $ozd;
        } else {
            $leftWhere = " LEFT JOIN " . $table2 . " ON " . $table1 . "." . $zd . " = " . $table2 . "." . $zd;
        }

        $offset = ($page - 1) * $pageSize;
        $count = $fillingModel->join($leftWhere)->field($field)->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $fillingModel->join($leftWhere)->field($field)->where($where)->limit($offset, $pageSize)->order($orderList)->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    /**
     * 根据指定条件获取当前一条数据
     * 
     * @date 2016/04/20
     * @version 1.0
     * @abstract 获取当前条件指定数据
     */
    public function getDataInfo($table, $where = array()) {
        if (empty($table) || empty($where))
            return false;

        $data = LibF::M($table)->where($where)->find();
        return $data;
    }
    
    /**
     * 根据指定条件获取当前多条数据
     * 
     * @date 2016/04/20
     * @version 1.0
     * @abstract 获取当前条件指定数据
     */
    public function getDataArr($table, $where = array(), $order = '') {
        if (empty($table))
            return false;
        
        $data = LibF::M($table)->where($where)->order($order)->select();
        return $data;
    }

}