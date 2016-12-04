
<?php

class SyslogModel extends \Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('syslog');
    }

    public function add($param) {

        if(!empty($param)){
            $param['logip'] = '';
        }    

        $status = $this->_add($param);
        if (!empty($status)) {
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    public function getList($params = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $where = array();
        if (isset($params['logusername']) && !empty($params['logusername']))
            $where['logusername'] = array('like', $params['logusername'] . '%');

        if (isset($params['username']) && !empty($params['username']))
            $where['username'] = array('like', $params['username'] . '%');

        $offset = ($page - 1) * $pageSize;
        $count = $this->tableD->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }

        $rows = $this->tableD->where($where)->limit($offset, $pageSize)->order('logtime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    public function otherList($params = '') {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array();

        $offset = ($page - 1) * $pageSize;
        $count = $this->tableD->order('logtime desc,logstatus asc')->group('loguserid')->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }

        $rows = $this->tableD->order('logtime desc,logstatus asc')->group('loguserid')->where($where)->limit($offset, $pageSize)->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function getTotalData($params = '') {
        $returnData = array('data' => array());

        $data = LibF::M('syslog')->order('logtime desc')->group('loguserid')->select();
        $returnData['total'] = count($data);
        if (!empty($data)) {
            $returnData['data'] = $data;
            $returnData['zx'] = 0;
            $returnData['lx'] = 0;
            foreach ($data as $value) {
                if ($value['logstatus'] == 0) {
                    $returnData['zx'] += 1;
                } else {
                    $returnData['lx'] += 1;
                }
            }
        }
        return $returnData;
    }

}