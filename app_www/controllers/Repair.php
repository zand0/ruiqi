<?php

/**
 * 保修
 */
class RepairController extends \Com\Controller\Common {

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
        $this->modelD = LibF::D('Repair');

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

        $where['status'] = 0;
        if (!empty($param)) {
            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        
        $repairModel = new FillingModel();
        $data = $repairModel->getDataList($param,'repair_type',$where,'time_created desc');
        $this->_view->assign($data);
    }

    public function addAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id', 0);
            
            if (!$id) {
                $app = new App();
                $data['repairsn'] = 'bxlx' . $app->build_order_no();
            }
            $data['title'] = $this->getRequest()->getPost('title');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['time_created'] = time();
            
            $returnData = $this->modelD->add($data, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }
            $this->success('添加成功', '/repair/index');
        }
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $data = $this->modelD->edite('', $id);
        $this->_view->assign($data);
        $this->_view->display('repair/add.phtml');
    }
    
    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status'=>1));
        $this->success('操作成功', '/repair/index');
    }
}