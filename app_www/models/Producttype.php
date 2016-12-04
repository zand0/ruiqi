<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ProducttypeModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['typesn'] = $params['typesn'];
        $data['name'] = $params['name'];
        $data['comment'] = $params['comment'];
        $data['time_created'] = time();

        if ($id) {
            $status = LibF::M('products_type')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('products_type')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function productList($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $productsModel = LibF::M('products_type');
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

    public function getProductTypeArray($id = '') {

        $model = LibF::M('products_type');
        if ($id) {
            return $model->where('id=' . $id)->find();
        } else {
            $prducttypeObject = array();
            $data = $model->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $prducttypeObject[$value['id']] = $value;
                }
            }
            return $prducttypeObject;
        }
    }

}