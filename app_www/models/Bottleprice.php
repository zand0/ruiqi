<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class BottlepriceModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    public function add($params, $id=0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['bottle_name'] = $params['bottle_name'];
        $data['bottle_id'] = $params['bottle_id'];

        $data['shop_id'] = $params['shop_id'];

        $data['price'] = $params['price'];
        $data['deposit'] = $params['deposit'];
        $data['bottle_comment'] = $params['bottle_comment'];

        if($id){
            $status = LibF::M('bottle_price')->where('id='.$id)->save($data);
            
        }else{
            $status = LibF::M('bottle_price')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function productList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';

        $productsModel = LibF::M('bottle_price');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($shop_id)
            $where = array('shop_id' => $shop_id);

        $offset = ($page - 1) * $pageSize;
        $count = $productsModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $productsModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function getBottleTypeArray($id = '', $where = array()) {

        $model = LibF::M('bottle_price');
        if ($id) {
            return $model->where('id=' . $id)->find();
        } else {
            $bottelPriceObject = array();
            $data = $model->where($where)->select();
            if (!empty($data)) {
                if (empty($where)) {
                    foreach ($data as $value) {
                        $bottelPriceObject[$value['id']] = $value;
                    }
                }else{
                    foreach ($data as $value) {
                        $bottelPriceObject[$value['bottle_id']] = $value;
                    }
                }
            }
            return $bottelPriceObject;
        }
    }

}
