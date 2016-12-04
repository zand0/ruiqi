<?php

/**
 * app基础类型数据
 */
class ApptypeController extends \Com\Controller\Common {

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
    
    protected $year;

    protected $url;

    /**
     * 初始化
     */
    public function init() {

        /*$url = $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
        if (strrpos($url, '/appuser/login') === false) {
            $token = $_GET('token');

            if (empty($token)) {
                $this->app->respond(-100, '验证失败！', true);
            }

            if (empty($this->userInfo)) {
                $this->app->respond(-101, '没有登录或已过期！', true);
            } else {
                
            }
        }*/
        $this->app = new App();
        $this->year = array(1 => '15年后', 2 => '12年后', 3 => '08年后', 4 => '报废瓶');
        
        $this->url = 'http://sr.ruiqi100.com';
    }
    
    /**
     * 获取商品列表
     * 
     * @param $shop_id
     * @param $token
     */
    public function bottletypeAction() {
        if ($this->getRequest()->isPost()) {
            $user_type = $this->getRequest()->getPost('user_type', 1); //1居民2商业3工业
            $shop_id = $this->getRequest()->getPost('shop_id', 0); //门店shop_id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                $returnData = array();

                //获取门店商品价格
                $area_id = 0;
                $shopPriceObject = $areaDepositObject = array();
                if (!empty($shop_id)) {
                    $shopObjectArr = LibF::M('shop')->where(array('shop_id' => $shop_id))->find();
                    $area_id = $shopObjectArr['area_id'];

                    $shopWhere['type'] = array('in', array(1, 2));
                    $shopWhere['shop_id'] = $shop_id;
                    $shopData = LibF::M('commodity_shop')->where($shopWhere)->order('type ASC')->select();
                    if (!empty($shopData)) {
                        foreach ($shopData as $shopValue) {
                            $shopPriceObject[$shopValue['norm_id']] = $shopValue;
                        }
                    }
                    if ($area_id) {
                        $depositWhere['area_id'] = $area_id;
                        $depositWhere['type'] = 1;
                        $depositArea = LibF::M('commodity_area')->where($depositWhere)->order('type ASC')->select();
                        if (!empty($depositArea)) {
                            foreach ($depositArea as $areaValue) {
                                $areaDepositObject[$areaValue['norm_id']] = $areaValue;
                            }
                        }
                    }
                }
                
                $where['type'] = array('in', array(1, 2));
                $where['is_app'] = 1;
                $where['status'] = 0;
                $data = LibF::M('commodity')->where($where)->order('type ASC')->select();
                if (!empty($data)) {
                    //获取当前钢瓶规格
                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();  //钢瓶规格
                    $depostTypeData = $bottleTypeModel->getBottleDepositArray(); //钢瓶规格对应押金
                    //获取配件规格
                    $productTypeModel = new ProducttypeModel();
                    $productTypeData = $productTypeModel->getProductTypeArray(); //配件规格
                    foreach ($data as $value) {
                        $val['id'] = $value['id'];
                        $val['name'] = $value['name'];
                        $val['type'] = $value['type'];  //1燃气2配件
                        $val['norm_id'] = $value['norm_id']; //规格id

                        $val['yj_price'] = 0; //押金
                        if ($val['type'] == 1) { //钢瓶
                            $val['typename'] = $bottleTypeData[$val['norm_id']]['bottle_name'];
                            if ($user_type == 2) {
                                $val['yj_price'] = (!empty($depostTypeData[$val['norm_id']])) ? $depostTypeData[$val['norm_id']]['suggested_price_business'] : $depostTypeData[$val['norm_id']]['suggested_price']; //押金
                                if ($area_id) {
                                    $val['yj_price'] = (isset($areaDepositObject[$val['norm_id']])) ? $areaDepositObject[$val['norm_id']]['deposit_price_business'] : $val['yj_price'];
                                }
                            } elseif($user_type == 3){
                            	$val['yj_price'] = (!empty($depostTypeData[$val['norm_id']])) ? $depostTypeData[$val['norm_id']]['suggested_price_industry'] : $depostTypeData[$val['norm_id']]['suggested_price_industry']; //押金
                                if ($area_id) {
                                    $val['yj_price'] = (isset($areaDepositObject[$val['norm_id']])) ? $areaDepositObject[$val['norm_id']]['suggested_price_industry'] : $val['yj_price'];
                                }
                            }else {
                                $val['yj_price'] = (!empty($depostTypeData[$val['norm_id']])) ? $depostTypeData[$val['norm_id']]['suggested_price'] : 0; //押金
                                if ($area_id) {
                                    $val['yj_price'] = (isset($areaDepositObject[$val['norm_id']])) ? $areaDepositObject[$val['norm_id']]['deposit_price'] : $val['yj_price'];
                                }
                            }
                        } else { //配件
                            $val['typename'] = $productTypeData[$val['norm_id']]['name'];
                            $val['yj_price'] = 0;  //押金
                        }
                        if ($user_type == 2) {
                            $val['price'] = $value['retail_price_business'] ? $value['retail_price_business'] : $value['retail_price'];  //零售价
                            if ($shop_id) {
                                $val['price'] = (isset($shopPriceObject[$val['norm_id']]) && $shopPriceObject[$val['norm_id']]['retail_price_business']) ? $shopPriceObject[$val['norm_id']]['retail_price_business'] : $val['price'];
                            }
                        } elseif($user_type == 3){
			    $val['price'] = $value['retail_price_industry'] ? $value['retail_price_industry'] : $value['retail_price'];  //零售价
                            if ($shop_id) {
                                $val['price'] = (isset($shopPriceObject[$val['norm_id']]) && $shopPriceObject[$val['norm_id']]['retail_price_industry']) ? $shopPriceObject[$val['norm_id']]['retail_price_industry'] : $val['price'];
                            }
                        } else {
                            $val['price'] = $value['retail_price'] ? $value['retail_price'] : 0;  //零售价
                            if ($shop_id) {
                                $val['price'] = (isset($shopPriceObject[$val['norm_id']]) && $shopPriceObject[$val['norm_id']]['retail_price']) ? $shopPriceObject[$val['norm_id']]['retail_price'] : $val['price'];
                            }
                        }
                        $returnData[] = $val;
                    }
                }
                
                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    public function producttypeAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                $returnData = array();
                $commodityModel = new CommodityModel();
                $data = LibF::M('commodity')->where(array('status' => 0,'type' =>2))->order('id DESC')->select();
                if (!empty($data)) {
                    //获取配件规格
                    $productTypeModel = new ProducttypeModel();
                    $productTypeData = $productTypeModel->getProductTypeArray();
                    foreach ($data as $value) {
                        $val['id'] = $value['id'];
                        $val['name'] = $value['name'];
                        $val['type'] = $value['type'];  //1燃气2配件
                        $val['norm_id'] = $value['norm_id']; //规格id
                        $val['typename'] = $productTypeData[$val['norm_id']]['name'];
                        $val['price'] = $value['retail_price'];  //零售价
                        $returnData[] = $val;
                    }
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    public function bottlelistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                //获取当前钢瓶规格
                $model = LibF::M('bottle_type')->where(array('status'=>1))->select();

                $this->app->respond(1, $model);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 获取配件基准表
     * 
     * @param $shop_id
     * @param $token
     */
    public function productAction(){
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shop_id) && !empty($token)) {

                $data = array();
                $productPrice = LibF::M('products_price')->where('shop_id='.$shop_id)->select();
                if(!empty($productPrice)){
                    foreach($productPrice as $value){
                        $val['products_no'] = $value['id'];
                        $val['products_name'] = $value['products_name'];
                        $val['products_price'] = $value['products_price'];
                        $data[] = $val;
                    }
                }
                
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取门店基准表
     * 
     * @param $shop_id
     * @param $token
     */
    public function shopAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {

                $data = array();
                $shop = LibF::M('shop')->where('level != 1')->select();
                if(!empty($shop)){
                    foreach($shop as $value){
                        $val['shop_id'] = $value['shop_id'];
                        $val['shop_name'] = $value['shop_name'];
                        $data[] = $val;
                    }
                }
                
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取报修的基准表
     * 
     * @param $shop_id
     * @param $token
     */
    public function repairAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id)) {

                $data = array();
                $repair = LibF::M('repair_type')->select();
                if(!empty($repair)){
                    foreach($repair as $value){
                        $val['id'] = $value['id'];
                        $val['title'] = $value['title'];
                        $data[] = $val;
                    }
                }
                
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取安检的基准表
     * 
     * @param $shop_id
     * @param $token
     */
    public function securityAction(){
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id)) {

                $data = array();
                $repair = LibF::M('security_type')->select();
                if(!empty($repair)){
                    foreach($repair as $value){
                        $val['id'] = $value['id'];
                        $val['title'] = $value['title'];
                        $data[] = $val;
                    }
                }
                
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取送气工基准表
     * 
     * @param $shop_id
     * @param $token
     */
    public function shipperAction(){
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id)) {

                $data = array();
                $repair = LibF::M('shipper')->where("shop_id=" . $shop_id . " AND is_del = 0")->select();
                if (!empty($repair)) {
                    foreach ($repair as $value) {
                        $val['shipper_id'] = $value['shipper_id'];
                        $val['shiper_name'] = $value['shipper_name'];
                        $data[] = $val;
                    }
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取车辆基准表
     * 
     * @param $car_id
     * @param $token
     */
    public function carAction(){
        
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {

                $data = array();
                $repair = LibF::M('car')->select();
                if (!empty($repair)) {
                    foreach ($repair as $value) {
                        $val['car_id'] = $value['car_id'];
                        $val['license_plate'] = $value['license_plate'];
                        $data[] = $val;
                    }
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
        
    }
    
    /**
     * 投诉类型
     */
    public function complainAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {

                $data = array();
                $tousu_type = LibF::M('tousu_type')->field('id,title')->select();

                $this->app->respond(1, $tousu_type);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取门店列表
     * 
     * 
     */
    public function shoplistAction() {
        $data = LibF::M('shop')->select();
        if ($data) {

            $this->app->respond(1, $data);
        } else {
            $this->app->respond(-1, '当前门店不存在');
        }
    }
    
    /**
     * 获取用户基础信息表
     * 
     */
    public function userinfoAction() {
        if ($this->getRequest()->isPost()) {
            $user_id = $this->getRequest()->getPost('user_id');
            $type = $this->getRequest()->getPost('type',1); //1用户2门店经理3区域经理4总经理5送气工
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($user_id)) {
                $data = array();
                switch ($type) {
                    case 1:
                        $data = LibF::M('kehu')->where(array('kid' => $user_id))->find();
                        //arrearage
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $data = LibF::M('admin_user')->where(array('user_id' => $user_id))->find();
                        $data['money'] = 0;
                        break;
                    case 5:
                        $data = LibF::M('shipper')->where(array('user_id' => $user_id))->find();
                        break;
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 获取安全报告
     */
    public function reportAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $type = $this->getRequest()->getPost('type', 1);  //1居民2商业3工业
            if (!empty($token)) {
                $where['status'] = 1;
                $where['type'] = $type;
                $data = LibF::M('security_report')->where($where)->order('listorder asc')->select();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app更新版本
     * 
     */
    public function versionAction() {

        if ($this->getRequest()->isPost()) {
            $type = $this->getRequest()->getPost('type'); //user|shipper|store|manager
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($type)) {

                $data['url'] = 'http://182.92.97.39/';
                $data['version_no'] = '0.0.1';

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取用户地址问题
     */
    public function useraddressAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $returnData = array();
            if (!empty($token)) {
                $where['status'] = array('neq', -1);
                $where['regionids'] = array('like', '%,10,140,%');
                $data = LibF::M('region')->where($where)->order('region_id asc')->select();
                foreach ($data as $key => $value) {
                    $val['code'] = $value['region_id'];
                    $val['pcode'] = $value['parent_id'];
                    $val['name'] = $value['region_name'];
                    $returnData[] = $val;
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    protected function getRegion($data){
        $returnData = array();
        if(!empty($data)){
            foreach($data as $key => $value){
                $val['code'] = $value['region_id'];
                $val['pcode'] = $value['parent_id'];
                $val['name'] = $value['region_name'];
                //$returnData[$value['parent_id']][] = $val;
            }
        }
        return $data;
    }
    
    /**
     * 前期用户系订单绑定当前的体验、优惠套餐直接下单
     */
    public function commditylistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {

                $where['type'] = array('in',array(4,5));  //4体验套餐 5优惠套餐
                $where['status'] = 0;
                $data = LibF::M('commodity')->where($where)->select();
                $returnData['ty'] = array();
                $returnData['yh'] = array();
                if(!empty($data)){
                    foreach($data as $value){
                        $val['id'] = $value['id'];
                        $val['name'] = $value['name'];
                        $val['norm_id'] = $value['norm_id'];
                        $val['num'] = $value['num'];
                        $val['price'] = $value['price'];
                        $val['money'] = $value['money'];
                        $val['deposit'] = $value['deposit'];
                        if($value['type'] == 4){
                            $returnData['ty'][] = $val;
                        }else {
                            $returnData['yh'][] = $val;
                        }
                    }
                }
                
                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口1.获取用户余瓶
    public function kehubottleAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($kid)) {
                $bottleData = array();
                $data = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($data) && !empty($data['bottle_data'])) {
                    $bottleData = json_decode($data['bottle_data']);
                }
                $returndata['ybottle'] = $bottleData;
                $this->app->respond(1, $returndata);
            } else {
                $this->app->respond(-3, '当前用户数据未提交');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口2.获取折旧瓶回收价格
    public function depreciationAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {

                $returndata['price'] = 0;
                $data = LibF::M('raffinate_price')->find();
                if (!empty($data)) {
                    $returndata['price'] = $data['price'];
                }

                $returndata['raffinate'] = array();
                $years = $this->year;
                $data = LibF::M('depreciation_price')->select();
                if (!empty($data)) {
                    foreach ($data as &$v) {
                        $v['year_name'] = $years[$v['year']];
                    }
                    $returndata['raffinate'] = $data;
                }
                $this->app->respond(1, $returndata);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口3 送气工补充方法获取优惠活动
    public function promotionsAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                $param['status'] = 1;
                $param['range_use'] = array('neq',2);  //不是送气工优惠

                $data = LibF::M('promotions')->where($param)->select();
                if (!empty($data)) {
                    if (!empty($data)) {
                        foreach ($data as &$value) {
                            if ($value['file_name']) {
                                $value['file_name'] = $this->url . $value['file_name'];
                            }
                            if ($value['type'] == 2) {
                                //抵扣商品
                                $annexArr = !empty($value['annex']) ? explode('|', $value['annex']) : '';
                                $pArr = array();
                                $pArr['good_id'] = $annexArr[0];
                                $pArr['good_name'] = $annexArr[2];
                                $pArr['good_kind'] = $annexArr[1];
                                $pArr['good_price'] = $annexArr[3];
                                $pArr['good_type'] = 2;
                                $pArr['good_num'] = 1;
                                $value['title'] .= $pArr['good_name'];
                                $value['data'][] = $pArr;
                            } else {
                                $value['annex'] = '';
                                $value['data'] = array();
                            }
                        }
                    }
                }
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口4 送气工自己优惠
    public function promotionshiperAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                $param['status'] = 1;
                $param['range_use'] = 2;  //送气工优惠

                $newData = array();
                $data = LibF::M('promotions')->where($param)->select();
                if (!empty($data)) {
                    foreach ($data as $value) {
                        $val['id'] = $value['id'];
                        $val['promotionsn'] = $value['promotionsn'];
                        $val['money'] = $value['money'];
                        $val['price'] = $value['price'];
                        $val['title'] = $value['title'];
                        $val['comment'] = $value['comment'];
                        $newData[] = $val;
                    }
                }
                $this->app->respond(1, $newData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口5 上门费
    public function doorfreeAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                $returndata['price'] = 0;
                $data = LibF::M('door_fee')->find();
                if (!empty($data)) {
                    $returndata['price'] = $data['price'];
                }
                $this->app->respond(1, $returndata);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口6 余气（残液）退气规则
    public function raffinateAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token)) {
                $data = LibF::M('depreciation_gas')->select();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

}