<?php

/**
 * 储罐管理
 */
class TankController extends \Com\Controller\Common {

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
        $this->app = new App();
    }
    
    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        $where['status'] = 1;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $tank = new FillingModel();
        $data = $tank->getDataList($param, 'tank', $where, 'id desc');

        $this->_view->assign($data);

        //获取燃气类型
        $gas = new GasModel();
        $gasObject = $gas->getGasArray();
        $this->_view->assign('gasObject', $gasObject);
    }

    public function addAction() {

        if (IS_POST) {
            $data['tanksn'] = $this->getRequest()->getPost('tanksn');
            $data['tank_name'] = $this->getRequest()->getPost('tank_name');
            $data['gas_id'] = $this->getRequest()->getPost('gas_id');
            $data['address'] = $this->getRequest()->getPost('address');
            $data['stock'] = $this->getRequest()->getPost('stock');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['work_time'] = $this->getRequest()->getPost('work_time');

            $id = $this->getRequest()->getPost('id');

            $tank = new TankModel();
            $returnData = $tank->add($data, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('ok', '/tank/index');
        }

        //获取储罐编号
        $app = new App();
        $orderSn = $app->build_order_no();
        $this->_view->assign('tanksn', 'cg' . $orderSn);
        
        //获取燃气类型
        $gas = new GasModel();
        $gasObject = $gas->getGasArray();
        $this->_view->assign('gasObject',$gasObject);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));

        $data = LibF::M('tank')->where('id = ' . $id)->find();
        $this->_view->assign($data);
        $this->_view->assign('id', $id);
        
        //获取燃气类型
        $gas = new GasModel();
        $gasObject = $gas->getGasArray();
        $this->_view->assign('gasObject',$gasObject);

        $this->_view->display('tank/add.phtml');
    }
    
    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        
        $data['status'] = -1;
        $status = LibF::M('tank')->where(array('id' => $id))->save($data);
        $this->success('操作成功', '/tank/index');
    }

    public function bottlefillingAction() {
        //充装记录同步
        
        $page = $this->getRequest()->getPost('page', 1);
        $pageSize = $this->getRequest()->getPost('pageSize', 100);

        if ($page == 1) {
            $fillData = LibF::M('bottle_tb_log')->where(array('type' => 0))->order('id desc')->limit(1)->find();
            if (!empty($fillData)) {
                $start_time = $fillData['end_time'];
                $end_time = date('Y-m-d H:i:s');
                //$page = $fillData['page'];
            }else{
                $start_time = $end_time = date('Y-m-d H:i:s');
            }
        } else {
            $fillData = LibF::M('bottle_tb_log')->where(array('type' => 0))->order('id desc')->limit(1)->find();
            if (!empty($fillData)) {
                $start_time = $fillData['start_time'];
                $end_time = $fillData['end_time'];
                //$page = $fillData['page'];
            }else{
                $start_time = $end_time = date('Y-m-d H:i:s');
            }
        }
        
        $url = 'http://115.28.176.222/open-api/fill/query/page';
        $post_data['pageSize'] = $pageSize;
        $post_data['currentPage'] = $page;
        $post_data['czkssjEnd'] = $end_time;
        $post_data['czkssjStart'] = $start_time;
        $post_data['token'] = '1301583aeb85';
        $post_data['tenantcode'] = '13015';
        
        $o = "";
        foreach ($post_data as $k => $v) {
            $o.= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        $data = array();
        $tb = new TbModel();
        $res = $tb->request_post($url, $post_data);
        
        $rData['code'] = 0;
        $rData['page'] = $page;
        if (!empty($res)) {
            $data = json_decode($res, true);
            if (isset($data['data']) && !empty($data['data'])) {
                $count = count($data['data']);
                if ($count < $pageSize) {
                    $page = 1;
                } else {
                    $page += 1;
                }

                $returnData = $tb->synchronizebottle($data); //充装数据同步
                if ($returnData['code'] == 1) {
                    $end_time = $returnData['end_time'];  //返回最后一条数据时间
                }

                $idata['type'] = 0;
                $idata['start_time'] = $start_time;
                $idata['end_time'] = $end_time;
                $idata['page'] = $page;
                $status = LibF::M('bottle_tb_log')->add($idata);
                
                $rData['page'] = $page;
                $rData['pageSize'] = $pageSize;
                $rData['code'] = $status;
            }
        }

        echo json_encode($rData);
        exit;
    }

    public function bottletbAction() {
        $page = $this->getRequest()->getPost('page', 1);
        $pageSize = $this->getRequest()->getPost('pageSize', 100);

        if ($page == 1) {
            $fillData = LibF::M('bottle_tb_log')->where(array('type' => 1))->order('id desc')->limit(1)->find();
            if (!empty($fillData)) {
                $start_time = $fillData['end_time'];
                $end_time = date('Y-m-d H:i:s');
                //$page = $fillData['page'];
            } else {
                $start_time = $end_time = date('Y-m-d H:i:s');
            }
        } else {
            $fillData = LibF::M('bottle_tb_log')->where(array('type' => 1))->order('id desc')->limit(1)->find();
            if (!empty($fillData)) {
                $start_time = $fillData['start_time'];
                $end_time = $fillData['end_time'];
                //$page = $fillData['page'];
            } else {
                $start_time = $end_time = date('Y-m-d H:i:s');
            }
        }

        $url = 'http://115.28.176.222/open-api/bottle/query/page';
        $post_data['pageSize'] = $pageSize;
        $post_data['currentPage'] = $page;
        $post_data['jdsjEnd'] = $end_time;
        $post_data['jdsjStart'] = $start_time;
        $post_data['token'] = '1301583aeb85';
        $post_data['tenantcode'] = '13015';

        $o = "";
        foreach ($post_data as $k => $v) {
            $o.= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        $tb = new TbModel();
        $res = $tb->request_post($url, $post_data);
        $rData['code'] = 0;
        $rData['page'] = $page;
        if (!empty($res)) {
            $data = json_decode($res, true);
            if (isset($data['data']) && !empty($data['data'])) {
                $count = count($data['data']);
                if ($count < $pageSize) {
                    $page = 1;
                } else {
                    $page += 1;
                }

                $returnData = $tb->initializationbottle($data);
                if ($returnData['code'] == 1) {
                    $etime = $returnData['end_time'];  //返回最后一条数据时间
                    $end_time = !empty($etime) ? date('Y-m-d H:i:s',$etime) : $end_time;
                }
                
                $idata['type'] = 1;
                $idata['start_time'] = $start_time;
                $idata['end_time'] = $end_time;
                $idata['page'] = $page;
                $status = LibF::M('bottle_tb_log')->add($idata);
                
                $rData['page'] = $page;
                $rData['pageSize'] = $pageSize;
                $rData['code'] = $status;
            }
        }

        echo json_encode($rData);
        exit;
    }

    public function bottlerefreshAction() {
        //定时刷新数据
        $type = $this->getRequest()->getQuery('type', 0);
        $data = LibF::M('bottle_tb_log')->where(array('type' => $type))->order('end_time DESC')->limit(1)->find();
        if (empty($data)) {
            $start_time = date('Y-m-d H:i:s');
        } else {
            $start_time = $data['end_time'];
        }

        $returnDate = array();
        $returnDate['code'] = 1;
        $returnDate['type'] = $type;
        $returnDate['time'] = $start_time;

        echo json_encode($returnDate); exit;
    }

    //获取同步日志
    public function synchronizelogAction() {
        
        $nowDate = date('Y-m');
        $this->_view->assign('nowDate',$nowDate);
        
        $nowTime = time();
        $daynum = date('t',$nowTime);
        
        $this->_view->assign('daynum', $daynum);
    }
    
    public function tbloglistAction() {
        
        $start_time = $this->getRequest()->getQuery('start_time');
        $end_time = $this->getRequest()->getQuery('end_time');

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $param['type'] = $this->getRequest()->getQuery('type');

        $where = '';
        if (!empty($start_time) && !empty($end_time)) {
            $where['end_time'] = array(array('egt', $start_time), array('elt', $end_time), 'AND');
        }
        if (!empty($param['type'])) {
            $where['type'] = $param['type'];
        }

        $tank = new FillingModel();
        $data = $tank->getDataList($param, 'bottle_tb_log', $where, 'id desc');
        print_r($data);exit;

        $this->_view->assign($data);
    }
}