<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class NoticeModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function getlist($params = array()) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $noticeModel = LibF::M('notice');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        if (isset($params['role_id']) && isset($params['user_id'])) {
            $where['role'] = $params['role_id'];
            $where['admin_user_id'] = $params['user_id'];
            $where['_logic'] = 'OR';
        }
        if (isset($params['message_title']) && !empty($params['message_title'])) {
            $where['message_title'] = array('like', $params['message_title'] . "%");
        }

        if (!empty($params['time_start']) && !empty($params['time_end'])) {
            $where['time_created'] = array(array('egt', strtotime($params['time_start'])), array('elt', strtotime($params['time_end'])), 'AND');
        }

        $offset = ($page - 1) * $pageSize;
        $count = $noticeModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $noticeModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    /**
     * 消息创建
     * 
     * @param $params
     * @param $id
     */
    public function add($params, $id) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['message_title'] = $params['message_title'];
        $data['message_content'] = $params['message_content'];
        $data['message_type'] = $params['message_type'];
        $data['admin_user_id'] = $params['admin_user_id'];
        $data['admin_user'] = $params['admin_user'];
        if (!empty($params['receiving_object']))
            $data['receiving_object'] = $params['receiving_object'];
        if(!empty($params['receiving_user']))
            $data['receiving_user'] = $params['receiving_user'];
        if (!empty($params['department']))
            $data['department'] = $params['department'];
        if (!empty($params['send_range']))
            $data['send_range'] = $params['send_range'];
        if (!empty($params['role']))
            $data['role'] = $params['role'];
        $data['time_created'] = time();

        if ($id) {
            $status = LibF::M('notice')->where('message_id=' . $id)->save($data);
        } else {
            $status = LibF::M('notice')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 获取消息
     * 
     * @param $message_id
     */
    public function getNoticeList($message_id = 0) {
        $where = array();
        if ($message_id) {
            $where['message_id'] = $message_id;
            $data = LibF::M('notice')->where($where)->find();
        } else {
            $data = LibF::M('notice')->where($where)->select();
        }
        return $data;
    }

    public function delNotice($message_id = 0) {
        $status = 0;
        if($message_id){
            $status = LibF::M('notice')->where(array('message_id' => $message_id))->delete();
        }
        return $status;
    }

}