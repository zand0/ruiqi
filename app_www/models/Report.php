<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class ReportModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 创建安全报告
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
            $status = LibF::M('security_report')->where($where)->save($data);
        } else {
            $status = LibF::M('security_report')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 更新安全报告
     * 
     * @param $data
     * @param $id
     */
    public function editData($data, $id) {
        if (empty($data) || empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('security_report')->where($where)->save($data);
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 删除安全报告
     */
    public function delData($id) {
        if (empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('security_report')->where($where)->delete();
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 安全报告数据
     * 
     * @param $param
     */
    public function getlist($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $reportModel = LibF::M('security_report');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where['status'] = 1;

        $offset = ($page - 1) * $pageSize;
        $count = $reportModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $reportModel->where($where)->limit($offset, $pageSize)->order('listorder asc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function listData($param = array()) {
        $data = LibF::M('security_report')->where(array('status' => 1))->order('listorder asc')->select();
        return $data;
    }

}