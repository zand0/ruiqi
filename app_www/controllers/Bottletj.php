<?php

/**
 * 钢瓶统计
 */
class BottletjController extends \Com\Controller\Common {

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
        $type = $this->getRequest()->getQuery('type');
        
        $staticsModel = new StatisticsModel();
        $data = $staticsModel->bottleStaticsAll();
        
        $bottleType = $returnData = array();
        if(!empty($data)){
            $bottleTypeModel = new BottletypeModel();
            $bottleType = $bottleTypeModel->getBottleTypeArray();
            
            //得到门店库存统计
            if ($type == 't') {
                $where = ' status IN (1,2) ';
                $tjData = LibF::M('bottle')->field("count(*) as num,status as type")->where($where)->group('status')->select();

                if (!empty($tjData)) {
                    foreach ($tjData as $value) {
                        $val[0] = ($value['type']==1) ? '空瓶' : '重瓶';
                        $val[1] = floatval($value['num']);
                        $returnData[] = $val;
                    }
                    $json_data = json_encode($returnData);
                }
            } else {
                $where = ' status != 9 ';
                $tjData = LibF::M('bottle')->field("count(*) as num,type")->where($where)->group('type')->select();
                if (!empty($tjData)) {
                    foreach ($tjData as $value) {
                        $val[0] = isset($bottleType[$value['type']]) ? $bottleType[$value['type']]['bottle_name'] : '';
                        $val[1] = floatval($value['num']);
                        $returnData[] = $val;
                    }
                    $json_data = json_encode($returnData);
                }
            }
        }
        $this->_view->assign('type',$type);
        $this->_view->assign('data',$data);
        $this->_view->assign('bottleType',$bottleType);
        $this->_view->assign('json_data',$json_data);
    }
    
    public function shopAction() {

        $shop_id = $this->getRequest()->getQuery('shop_id');

        $staticsModel = new StatisticsModel();
        $data = $staticsModel->bottleStaticsList($shop_id);

        $json_data = '';
        $shopObject = $returnData = array();
        if (!empty($data)) {
            $shopModel = new ShopModel();
            $shopObject = $shopModel->getShopArray();

            //得到门店库存统计
            $tjData = LibF::M('store_inventory')->field("count(*) as num,shop_id")->where("is_use=1")->group('shop_id')->select();
            if(!empty($tjData)){
                foreach($tjData as $value){
                    $val[0] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                    $val[1] = floatval($value['num']);
                    $returnData[] = $val;
                }
                $json_data = json_encode($returnData);
            }
        }

        $this->_view->assign('data', $data);
        $this->_view->assign('shopObject', $shopObject);
        $this->_view->assign('json_data',$json_data);
    }

}
