<?php

/**
 *用户销售统计
 */
class NewuserController extends \Com\Controller\Common {

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
        $shop_id = $this->getRequest()->getQuery('shop_id');
        
        $staticsModel = new StatisticsModel();
        $data = $staticsModel->usersaleslist($shop_id);
        
        $this->_view->assign('data',$data);
    }
}