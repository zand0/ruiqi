<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class BottletypeModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    public function add($params, $id=0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['bottlesn'] = $params['bottlesn'];
        $data['bottle_name'] = $params['bottle_name'];
        $data['bottle_newweight'] = $params['bottle_netweight'];
        $data['bottle_price'] = $params['bottle_price'];
        $data['suggested_price'] = $params['suggested_price'];
        $data['other_price'] = $params['other_price'];
        $data['price'] = $params['price'];
        $data['yearly_inspection'] = $params['yearly_inspection'];
        $data['depreciation'] = $params['depreciation'];
        $data['repair'] = $params['repair'];
        $data['bottle_comment'] = $params['bottle_comment'];

        if($id){
            $status = LibF::M('bottle_type')->where('id='.$id)->save($data);       
        }else{
            $status = LibF::M('bottle_type')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function edit() {
        
    }

    public function productList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $productsModel = LibF::M('bottle_type');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array('status' => 1);

        $offset = ($page - 1) * $pageSize;
        $count = $productsModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $productsModel->where($where)->limit($offset, $pageSize)->order('id desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function getBottleTypeArray($id=''){
        
        $model = LibF::M('bottle_type');
        if ($id) {
            return $model->where('id=' . $id)->find();
        } else {
            $bottelTypeObject = array();
            $data = $model->where(array('status' => 1))->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $bottelTypeObject[$value['id']] = $value;
                }
            }
            return $bottelTypeObject;
        }
    }
    
    public function getBottleDepositArray($id=''){
        
        $model = LibF::M('cylinder_deposit');
        if ($id) {
            return $model->where('id=' . $id)->find();
        } else {
            $bottelTypeObject = array();
            $data = $model->where(array('status'=>0))->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $bottelTypeObject[$value['bottle_id']] = $value;
                }
            }
            return $bottelTypeObject;
        }
    }
}
