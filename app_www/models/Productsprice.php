<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ProductspriceModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    public function add($params, $id=0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['products_no'] = $params['products_no'];
        $data['products_name'] = $params['products_name'];
        $data['products_price'] = $params['products_price'];

        $data['shop_id'] = $params['shop_id'];
        
        $data['products_comment'] = $params['products_comment'];

        if($id){
            $status = LibF::M('products_price')->where('id='.$id)->save($data);
        }else{
            $status = LibF::M('products_price')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function del($id = 0) {
        if (empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        $status = LibF::M('products_price')->where('id=' . $id)->delete();
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function productList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $products_no = isset($params['products_no']) ? $params['products_no'] : '';
        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';

        $productsModel = LibF::M('products_price');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = '';
        if ($products_no)
            $where = array('id' => $products_no);
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
    
    /**
     * 获取配件的相关信息
     * 
     * @param $products_id
     * @return array
     */
    public static function getProductsArray($products_id = 0, $shop_id) {

        $model = LibF::M('products_price');
        if ($products_id) {
            return $model->where('products_no=' . $products_id)->find();
        } else {
            $productsObject = array();
            $data = $model->select('shop_id = ' . $shop_id);
            if (!empty($data)) {
                foreach ($data as $value) {
                    $productsObject[$value['id']] = $value;
                }
            }
            return $productsObject;
        }
    }

}

