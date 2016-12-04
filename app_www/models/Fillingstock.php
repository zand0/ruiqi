<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class FillingstockModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function fillstock_list($params) {
        $page = $params['page'];
        $pageSize = $params['page_size'];

        $fillingStockModel = LibF::M('filling_stock');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();
        if($params['type'])
            $where['type'] = $params['type'];
        if($params['fs_name'])
            $where['fs_name'] = array('like',$params['fs_name'].'%');
        
        $offset = ($page - 1) * $pageSize;
        $count = $fillingStockModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $fillingStockModel->where($where)->limit($offset, $pageSize)->order('ctime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function add($params) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['fs_name'] = $params['fs_name'];
        $data['fs_num'] = $params['fs_num'];
        $data['type'] = $params['type'];
        $data['fs_price'] = $params['fs_price'];
        $data['fs_type_id'] = $params['fs_type_id'];
        $data['admin_user'] = $params['admin_user'];
        $data['time'] = date('Y-m-d');
        $data['ctime'] = time();

        if($params['id']){
            $status = LibF::M('filling_stock')->add($data);
        }else{
            $status = LibF::M('filling_stock')->where(array('id' => $params['id']))->save($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok');
    }

}