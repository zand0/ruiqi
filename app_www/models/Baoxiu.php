<?php

class BaoxiuModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('baoxiu');
    }

    public function add($param, $roles = array()) {
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

    public function getList($params = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if(!empty($params['shop_id']))
            $where['shop_id'] = $params['shop_id'];
        if(!empty($params['kname']))
            $where['kname'] = array('like',$params['kname'].'%');
        if(!empty($params['time_start']) && !empty($params['time_end']))
            $where['ctime'] = array(array('egt', strtotime($params['time_start'])), array('elt', strtotime($params['time_end'])), 'AND');

        $offset = ($page - 1) * $pageSize;
        $count = $this->tableD->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $this->tableD->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

}