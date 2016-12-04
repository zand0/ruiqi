<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BaselineModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($data, $id = 0) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $status = LibF::M('baseline')->where('id=' . $id)->save($data);
        } else {
            $where['goods_type'] = $data['goods_type'];
            $where['type'] = $data['type'];
            $newData = LibF::M('baseline')->where($where)->find();
            if (!empty($newData)) {
                $status = LibF::M('baseline')->where('id=' . $newData['id'])->save($data);
            } else {
                $status = LibF::M('baseline')->add($data);
            }
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function dataList($params){
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $baselineModel = LibF::M('baseline');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        $type = isset($params['type']) ? $params['type'] : '';
        
        $where = array('status' => 1);
        
        $baseline_sno = isset($params['baseline_sno']) ? $params['baseline_sno'] : '';
        if(!empty($baseline_sno))
            $where['baseline_sno'] = array('like',$baseline_sno.'%');
        
        $type = isset($params['type']) ? $params['type'] : '';
        if($type)
            $where['type'] = $type;

        $offset = ($page - 1) * $pageSize;
        $count = $baselineModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $baselineModel->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
}
