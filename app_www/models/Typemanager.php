<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class TypemanagerModel extends \Com\Model\Base {

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
            $status = LibF::M('typemanager')->where($where)->save($data);
        } else {
            $status = LibF::M('typemanager')->add($data);
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
        $status = LibF::M('typemanager')->where($where)->save($data);
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
        $status = LibF::M('typemanager')->where($where)->delete();
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
    public function getlist($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $typemanagerModel = LibF::M('typemanager');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where['status'] = 0;
        if (isset($param['type']))
            $where['type'] = $param['type'];

        $offset = ($page - 1) * $pageSize;
        $count = $typemanagerModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $typemanagerModel->where($where)->limit($offset, $pageSize)->order('id DESC')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function listData($param = array()) {
        $where['status'] = 0;
        if (isset($param['type']))
            $where['type'] = $param['type'];
        
        $data = LibF::M('typemanager')->where($where)->order('id DESC')->select();
        return $data;
    }

    public function getData($type = '') {
        $model = LibF::M('typemanager');
        if ($id) {
            return $model->where('id=' . $id)->find();
        } else {
            
            $where['status'] = 0;
            if (!empty($type)) {
                if (is_array($type)) {
                    $where['type'] = array('in', $type);
                } else {
                    $where['type'] = $type;
                }
            }

            $typemanagerObject = array();
            $data = $model->where($where)->select();
            if (!empty($data)) {
                if (is_array($type)) {
                    foreach ($data as $value) {
                        $typemanagerObject[$value['type']][$value['id']] = $value;
                    }
                } else {
                    foreach ($data as $value) {
                        $typemanagerObject[$value['id']] = $value;
                    }
                }
            }
            return $typemanagerObject;
        }
    }
    
}

