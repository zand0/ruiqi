<?php

/**
 * 地区设置
 * @author  jinyue.wang
 */
class AreaController extends \Com\Controller\Common {

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
    }
    
    public function indexAction() {
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $where = array();
        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param,'area',$where,'area_id desc');
        $this->_view->assign($data);
    }

    public function addAction() {

        $paramData = $this->getRequest()->getPost();
        if (!empty($paramData)) {
            $app = new App();
            $qysn = $app->build_order_no();
            $data['area_code'] = 'qy' . $qysn;

            if (!empty($paramData['title']))
                $data['title'] = $paramData['title'];
            if (!empty($paramData['admin_id'])){
                $adminList = explode('|', $paramData['admin_id']);
                $data['admin_id'] = $adminList[0];
                $data['admin_name'] = $adminList[1];
                $data['mobile_phone'] = $adminList[2];
            }
            if (!empty($paramData['comment']))
                $data['comment'] = $paramData['comment'];

            $id = $paramData['area_id'];
            if ($id) {
                $status = LibF::M('area')->where(array('area_id' => $id))->save($data);
            } else {
                $data['time_created'] = time();
                $status = LibF::M('area')->add($data);
            }
            if($status){
                $this->success('更新成功', '/area/index');
            }else{
                $this->success('更新失败', '/area/index');
            }
        }

        $area_id = $this->getRequest()->getQuery('area_id');
        if(!empty($area_id)){
            $areaData = LibF::M('area')->where(array('area_id' => $area_id))->find();
            $this->_view->assign($areaData);
        }
        
        //获取区域经理列表
        $CommonDataModel = new CommonDataModel();
        $userdata = $CommonDataModel->getAreaUser(4);
        $this->_view->assign('userdata',$userdata);
    }
    
    public function listAction() {
        $area_id = $this->getRequest()->getQuery('area_id');

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $where = array('area_id' => $area_id);
        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param, 'shop', $where, 'shop_id desc');

        $this->_view->assign($data);
        
        //获取区域列表
        $CommonDataModel = new CommonDataModel();
        $areaData = $CommonDataModel->getQuarterData();
        $this->_view->assign('areaData',$areaData);
    }

}
