<?php

/*
 * 报修类型管理
 */

class RepairModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
        $this->tableD = LibF::M('repair_type');
    }

    public function add($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        if ($id) {
            $status = $this->tableD->where('id=' . $id)->save($params);
        } else {
            $status = $this->tableD->add($params);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, '操作成功', $status);
    }

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

    public function repairList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = ' status=0 ';

        $offset = ($page - 1) * $pageSize;
        $count = $this->tableD->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $this->tableD->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function getRepairArray($id = 0) {
        if ($id) {
            return $this->tableD->where('id=' . $id)->find();
        } else {
            $repairObject = array();
            $where['status'] = 0;
            $data = $this->tableD->where($where)->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $repairObject[$value['id']] = $value;
                }
            }
            return $repairObject;
        }
    }
    
    public function getTousuArray($id = 0) {
        if ($id){
            $data = LibF::M('tousu_type')->where(array('id' => $id,'status' => 0))->find();
        }else{
            $where['status'] = 0;
            $rdata = LibF::M('tousu_type')->where($where)->select();
            if(!empty($rdata)){
                foreach ($rdata as $value){
                    $data[$value['id']] = $value;
                }
            }
        }
        return $data;
    }

}
