<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class DepositlistModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 创建创建相关数据
     * 
     * @param $data
     * @param $id
     */
    public function add($data, $id) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $where = array('id' => $id);
            $status = LibF::M('deposit')->where($where)->save($data);
        } else {
            $status = LibF::M('deposit')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 更新相关数据条件数据
     * 
     * @param $data
     * @param $id
     */
    public function editData($data, $id) {
        if (empty($data) || empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('deposit')->where($where)->save($data);
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 删除指定id数据
     */
    public function delData($id) {
        if (empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('deposit')->where($where)->delete();
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 项目支出类型列表
     * 
     * @param $param
     */
    public function getlist($param) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $depositModel = LibF::M('deposit');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        if(isset($param['order_sn']) && !empty($param['order_sn'])){
            $where['order_sn'] = array('like',$param['order_sn']."%");
        }
        if(isset($param['shop_id']) && !empty($param['shop_id'])){
            $where['shop_id'] = $param['shop_id'];
        }
        if(isset($param['type']) && !empty($param['type'])){
            $where['type'] = $param['type'];
        }
        if(!empty($param['time_start']) && !empty($param['time_end'])){
            $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
        }

        $offset = ($page - 1) * $pageSize;
        $count = $depositModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $depositModel->where($where)->limit($offset, $pageSize)->order('id DESC')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function listData($param = array()) {
        $data = LibF::M('deposit')->where(array('status' => 0))->order('id DESC')->select();
        return $data;
    }

}

