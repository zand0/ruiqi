<?php

/**
 * 登陆日志管理
 */
class SyslogController extends \Com\Controller\Common {

    public function indexAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['pagesize'] = $this->getRequest()->getQuery('pagesize', 15);
        $param['type'] = $this->getRequest()->getQuery('type');
        
        $param['logusername'] = $this->getRequest()->getPost('logusername');
        $param['username'] = $this->getRequest()->getPost('username');
        //$param['job'] = $this->getRequest()->getPost('job'); //岗位

        $syslog = new SyslogModel();
        if ($param['type']) {
            $data = $syslog->otherList($param);
        } else {
            $data = $syslog->getList($param);
        }

        $dataTotal = $syslog->getTotalData();  //统计数据
        $this->_view->assign('dataTotal', $dataTotal);

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('pagesize', $param['pagesize']);
        $this->_view->assign('param',$param);
    }

}
