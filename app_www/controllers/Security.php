<?php

/**
 * @name SecurityController
 * @author wjy
 * @desc 安检
 * @date 2015-11-17  
 */
class SecurityController extends \Com\Controller\Common {

    /**
     * 返回数据存储数组;
     * @var array
     */
    private $respondData = array();

    /**
     * 返回数据类型，默认'json'
     * @var string
     */
    protected $respondType = 'json';

    /**
     * token值
     * @var string 
     */
    protected $token;

    /**
     * 用户数据
     * @var arr
     */
    protected $userInfo = array();

    /**
     * 调用对象
     * @var arr
     */
    protected $app;
    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        /* $url = $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
          if (strrpos($url, '/appuser/login') === false) {
          $token = $_GET('token');

          if (empty($token)) {
          $this->app->respond(-100, '验证失败！', true);
          }

          if (empty($this->userInfo)) {
          $this->app->respond(-101, '没有登录或已过期！', true);
          } else {

          }
          } */
        //$this->app = new App();

        $session = Tools::session();
        $adminInfo = $session['userinfo'];

        if (!empty($adminInfo)) {
            $this->shop_id = $adminInfo['shop_id'];
            $this->shop_level = $adminInfo['shop_level'];
            $userData['user_id'] = $adminInfo['user_id'];
            $userData['username'] = $adminInfo['username'];
            $userData['photo'] = $adminInfo['photo'];
            $userData['mobile_phone'] = $adminInfo['mobile_phone'];
            $this->user_info = $userData;
        }
    }

    public function indexAction() {
        $param['page'] = $this->getRequest()->getQuery('page');

        $securityModel = new SecurityModel();
        $data = $securityModel->securityList($param);

        $this->_view->assign($data['ext']);
    }

    public function addAction() {

        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');

            if (!$id) {
                $app = new App();
                $data['securitysn'] = 'ajlx' . $app->build_order_no();
            }
            $data['title'] = $this->getRequest()->getPost('title');
            $data['comment'] = $this->getRequest()->getPost('comment');

            $securityModel = new SecurityModel();
            $returnData = $securityModel->add($data, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/security/index');
        }
    }

    public function editAction() {
        $id = $this->getRequest()->getQuery('id');
        if ($id > 0) {
            $secuirtyModel = new SecurityModel();
            $data = $secuirtyModel->getSecurityArray($id);

            $this->_view->assign($data);
            $this->_view->display('security/add.phtml');
        } else {
            $this->error('当前数据无效', '/security/index');
        }
    }

    public function listAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (isset($param['securitysn']) && !empty($param['securitysn'])) {
                $where['rq_security_report_user.securitysn'] = $param['securitysn'];
                $getParam[] = "securitysn=" . $param['securitysn'];
            }
            if (isset($param['shipper_id']) && !empty($param['shipper_id'])) {
                $where['rq_security_report_user.shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }
            if (!empty($param['time'])) {
                $where['rq_security_report_user.ctime'] = array(array('egt', strtotime($param['time'])), array('elt', strtotime($param['time'] . ' 23:59:59')), 'AND');
                $getParam['time'] = "time=" . $param['time'];
            }
            
            if (!empty($this->shop_id)){
                $where['rq_kehu.shop_id'] = $this->shop_id;
            }
            
            unset($param['submit']);
            $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $field = 'rq_security_report_user.id,rq_security_report_user.order_sn,rq_security_report_user.kid,rq_security_report_user.shipper_id,rq_security_report_user.shipper_name,rq_security_report_user.ctime,rq_security_report_user.securitysn,';
        $field.= 'rq_kehu.user_name,rq_kehu.ktype';

        $gaslogModel = new FillingModel();
        //$data = $gaslogModel->getDataList($param, 'security_report_user', $where, 'id desc');
        $data = $gaslogModel->getDataTableList($param, 'security_report_user', 'rq_security_report_user', 'rq_kehu', 'kid', $where, 'rq_security_report_user.id desc', $field);

        $this->_view->assign($data);
    }

    public function listshowAction() {
        $param = $this->getRequest()->getQuery();
        if (!empty($param)) {
            $param['page'] = $this->getRequest()->getQuery('page', 1);
            $this->_view->assign('page', $param['page']);

            $id = $param['id'];
            $getParam[] = "id=" . $param['id'];
            $securityData = LibF::M('security_report_user')->where(array('id' => $id))->find();
            if (!empty($securityData)) {
                $reportDetial = !empty($securityData['reportdetail']) ? json_decode($securityData['reportdetail'], TRUE) : array();
                $this->_view->assign('reportDetial', $reportDetial);
                $imagedetail = !empty($securityData['imagedetail']) ? json_decode($securityData['imagedetail'], TRUE) : array();
                $this->_view->assign('imagedetail', $imagedetail);
            }

            $ktype = $param['ktype'];
            $getParam[] = "ktype=" . $param['ktype'];
            unset($param['submit']);
            $this->_view->assign('getparamlist', implode('&', $getParam));

            $where['type'] = $ktype; //获取安检项目
            $where['status'] = 1;
            $gaslogModel = new FillingModel();
            $data = $gaslogModel->getDataList($param, 'security_report', $where,'id asc');
            $this->_view->assign($data);
        }
        
    }

    public function addsecurityAction() {
        $secutityModel = new SecurityModel();
        if (IS_POST) {
            $type = $this->getRequest()->getPost('type');
            $comment = $this->getRequest()->getPost('comment');

            $data['security_id'] = !empty($type) ? implode(',', $type) : '';
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['comment'] = $comment;
            $data['time_created'] = time();

            $returnData = $secutityModel->addSecurity($data, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/security/list');
        }

        $typeObject = $secutityModel->getSecurityArray();
        $this->_view->assign('typeObject', $typeObject);
    }

}
