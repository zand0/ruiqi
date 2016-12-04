<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FittingModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($params, $id=0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        $data['products_no'] = $params['products_no'];
        $data['products_name'] = $params['products_name'];
        $data['products_norm'] = $params['products_norm'];
        $data['products_brand'] = $params['products_brand'];
        $data['products_price'] = $params['products_price'];
        $data['products_address'] = $params['products_address'];
        $data['products_comment'] = $params['products_comment'];
        if($id){
            $status = LibF::M('products')->where('id='.$id)->save($data);
        }else{
            $status = LibF::M('products')->add($data);
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

        $products_no = isset($params['products_no']) ? $params['products_no'] : '';
        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';

        $productsModel = LibF::M('products');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($products_no)
            $where = array('products_no' => $products_no);
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

}