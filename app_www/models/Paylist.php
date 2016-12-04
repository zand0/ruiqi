<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class PaylistModel extends \Com\Model\Base {

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
            $status = LibF::M('shop_paylist')->where($where)->save($data);
        } else {
            $status = LibF::M('shop_paylist')->add($data);
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
        $status = LibF::M('shop_paylist')->where($where)->save($data);
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
        $status = LibF::M('shop_paylist')->where($where)->delete();
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

        $paylistModel = LibF::M('shop_paylist');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $offset = ($page - 1) * $pageSize;
        $count = $paylistModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $paylistModel->where($where)->limit($offset, $pageSize)->order('id DESC')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

}

