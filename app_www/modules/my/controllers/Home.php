<?php
/**
 * Description of Home
 *
 * @author zxg
 */
class HomeController extends Com\Controller\My\Idauth {
    
    public $userInfo;
    public $fillModel;
    public function init() {
        parent::init();
        $fillModel = new FillingModel();
        $userRow = $fillModel->getDataInfo('kehu', array('kid' => $this->cuid));
        $this->userInfo = $userRow;
        $this->fillModel = $fillModel;
    }
    
    public function indexAction(){
        if(IS_POST){
            $kparam['user_name'] = $this->getRequest()->getPost('username');
            $kparam['mobile_phone'] = $this->getRequest()->getPost('mobile');
            $mobile_list = $this->getRequest()->getPost('mobile_list');
            $mobile_list[] = $kparam['mobile_phone'];
            $kparam['mobile_list'] = implode(',', $mobile_list);
            $shi = $this->getRequest()->getPost('region_1');
            $xian = $this->getRequest()->getPost('region_2');
            $qu = $this->getRequest()->getPost('region_3');
            $cun = $this->getRequest()->getPost('region_4');
            $kparam['sheng'] = $shi;
            $kparam['shi'] = $xian;
            $kparam['qu'] = $qu;
            $kparam['cun'] = $cun;
            $kparam['address'] = $this->getRequest()->getPost('address');

            $kWhere['kid'] = $this->cuid;
            $status = LibF::M('kehu')->where($kWhere)->save($kparam);

            $address_list = $this->getRequest()->getPost('address_list');
            if(!empty($address_list)){
                foreach ($address_list as $key => $value){
                    $kWhere['id'] = $key;
                    $aData = LibF::M('kehu_address')->where($kWhere)->find();
                    if(empty($aData)){
                        $iaVal['kid'] = $this->cuid;
                        $iaVal['sheng'] = $shi;
                        $iaVal['shi'] = $xian;
                        $iaVal['qu'] = $qu;
                        $iaVal['cun'] = $cun;
                        $iaVal['address'] = $value;
                        LibF::M('kehu_address')->add($iaVal);
                    }
                }
            }
            $this->redirect('/my/home/index');
        }

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            
            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        //当前用户基本信息
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);
        
        $regionData = array();
        $regionModel = new RegionModel();
        $regionData = $regionModel->getRegionObject();
        
        $kehuData = LibF::M('kehu')->where(array('kid' => $this->cuid))->find();
        if (!empty($kehuData)) {
            $kehuData['mobile_list'] = !empty($kehuData['mobile_list']) ? explode(',', $kehuData['mobile_list']) : array();
        }
        
        $this->_view->assign('kehuData', $kehuData);
        $this->_view->assign('sheng',10);
        $this->_view->assign('shi',140);
        $this->_view->assign('shi_name',$regionData[140]['region_name']);
        $this->_view->assign('xian',$kehuData['shi']);
        $this->_view->assign('xian_name',$regionData[$kehuData['shi']]['region_name']);
        $this->_view->assign('cun',$kehuData['qu']);
        $this->_view->assign('cun_name',$regionData[$kehuData['qu']]['region_name']);
        $this->_view->assign('qu',$kehuData['cun']);
        $this->_view->assign('qu_name',$regionData[$kehuData['cun']]['region_name']);
        
        $addressData = array();
        $where = array('kid' => $this->cuid, 'type' => 1);
        $order = 'time desc';
        $balanceData = $this->fillModel->getDataList($param, 'balance_list', $where, $order);
        if (!empty($balanceData['ext']['list'])) {
            foreach ($balanceData['ext']['list'] as &$value) {
                $value['time_list'] = date('Y-m-d H:i:s', $value['time']);
            }
            $addressData = LibF::M('kehu_address')->where(array('kid' => $this->cuid))->select();
        }
        
        $this->_view->assign('addressData',$addressData);
        $this->_view->assign($balanceData);
        $paytype = array(1 => '余额', 2 => '支付宝', 3 => '微信');
        $this->_view->assign('paytype',$paytype);
    }
    
    public function myorderAction() {
        
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            if(isset($param['order_sn']) && !empty($param['order_sn']))
                $where['order_sn'] = $param['order_sn'];
            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');
            
            unset($param['submit']);
        }
        $where['kid'] = $this->cuid;
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);
        
        //当前用户基本信息
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);

        $data = $this->fillModel->getDataList($param,'order',$where,'ctime desc');
        $this->_view->assign('data', $data);
    }

    public function backbottleAction() {
        if (IS_POST) {
            
            $param['comment'] = $this->getRequest()->getPost('comment');
            if (!empty($param['comment'])) {
                $time = time();
                $app = new App();
                $orderSn = $app->build_order_no();
                $param['depositsn'] = 'tp' . $orderSn;
                $param['kid'] = $this->cuid;
                $param['mobile'] = $this->mobilePhone;
                $param['username'] = $this->username;
                $param['shop_id'] = $this->shop_id;
                $param['time'] = date('Y-m-d');
                $param['address'] = $this->address;
                $param['time_created'] = $time;
                $status = LibF::M('deposit_list')->add($param);

                $this->redirect('/my/home/backbottle');
            } else {
                $this->error('请添加内容', '/my/home/backbottle');
            }
        }

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            
            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        //当前用户基本信息
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);

        $where = array('kid' => $this->cuid);
        $order = 'id desc';
        $data = $this->fillModel->getDataList($param, 'deposit_list', $where, $order);
        $this->_view->assign($data);

        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);
    }

    /**
     * 获取我的报修记录
     */
    public function baoxiuAction() {
        if (IS_POST) {
            $comment = $this->getRequest()->getPost('comment');
            $ctime = $this->getRequest()->getPost('ctime');
            $repairType = $this->getRequest()->getPost('repairType');
            if (!empty($repairType)) {
                $app = new App();
                $orderSn = $app->build_order_no();
                $addData['encode_id'] = 'bx' . $orderSn;
                $addData['shop_id'] = $this->shop_id;
                $addData['kid'] = $this->cuid;
                $addData['kname'] = $this->username;
                $addData['baoxiu_wt'] = !empty($repairType) ? json_encode($repairType) : '';
                $addData['comment'] = $comment;
                $addData['ctime'] = time();
                $status = LibF::M('baoxiu')->add($addData);
                $this->redirect('/my/home/baoxiu');
            }else{
                $this->error('请选择报修内容', '/my/home/baoxiu');
            }
        }
        
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            
            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        //当前用户基本信息
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);

        $where = array('kid' => $this->cuid);
        $order = 'id desc';
        $data = $this->fillModel->getDataList($param, 'baoxiu', $where, 'ctime desc');
        $this->_view->assign($data);
        
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);
        
        //报修类型
        $repair = new RepairModel();
        $repairData = $repair->getRepairArray();
        $this->_view->assign('repairData',$repairData);
    }

    /**
     * 获取我的投诉
     */
    public function tousuAction(){
        
        if (IS_POST) {
            $comment = $this->getRequest()->getPost('comment');
            $tousuType = $this->getRequest()->getPost('tousuType');
            if (!empty($tousuType)) {
                $app = new App();
                $orderSn = $app->build_order_no();
                $addData['encode_id'] = 'ts' . $orderSn;
                $addData['shop_id'] = $this->shop_id;
                $addData['kid'] = $this->cuid;
                $addData['kname'] = $this->username;
                $addData['tousu_wt'] = !empty($tousuType) ? json_encode($tousuType) : '';
                $addData['comment'] = $comment;
                $addData['ctime'] = time();

                $status = LibF::M('tousu')->add($addData);
                $this->redirect('/my/home/tousu');
            } else {
                $this->error('请选择投诉内容', '/my/home/tousu');
            }
        }

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            
            unset($param['submit']);
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        //当前用户基本信息
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);

        $where = array('kid' => $this->cuid);
        $order = 'id desc';
        $data = $this->fillModel->getDataList($param, 'tousu', $where, 'ctime desc');
        $this->_view->assign($data);
        
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);

        $repairModel = new RepairModel();
        $tousuData = $repairModel->getTousuArray();
        $this->_view->assign('tousuData', $tousuData);
    }
    
    /**
     * 我要订气
     */
    public function createorderAction() {
        //当前用户基本信息
        $userRow = $this->userInfo;
        $this->_view->assign('userRow', $userRow);
        
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            if(isset($param['order_sn']) && !empty($param['order_sn']))
                $where['order_sn'] = $param['order_sn'];
            if (!empty($param['time_start']) && !empty($param['time_end']))
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'])), 'AND');
            unset($param['submit']);
        }
        $where['kid'] = $this->cuid;
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $data = $this->fillModel->getDataList($param, 'order', $where, 'ctime desc');

        $this->_view->assign('data', $data);
        $this->_view->assign('param', $param);

        $commodityModel = new CommodityModel();
        $goodData = $commodityModel->listData();
        $this->_view->assign('goodData',$goodData);  //商品类型
        
        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottlTypeData', $bottleTypeData);

        //获取配件规格
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('productTypeData', $productTypeData);
        
        //获取当前套餐
        $tcData = $commodityModel->getTcObject();
        $this->_view->assign('tcData',$tcData);
    }

    /**
     * 创建订单
     */
    public function ajaxorderAction(){
        
        $returnData = array('code' => 0);
        
        $data['comment'] = $this->getRequest()->getPost('order_comment');
        $bottle = $this->getRequest()->getPost('order_bottle');
        $order_type = $this->getRequest()->getPost('order_type'); 
        
        $data['kid'] = $this->cuid;
        $data['shop_id'] = $this->shop_id;
        if(!empty($bottle) && !empty($data['kid'])){
            $orderInfo = array();
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['order_sn'] = 'Dd' . $orderSn;
            $data['mobile'] = $this->mobilePhone;
            $data['address'] = $this->address;
            $data['username'] = $this->username;
            $data['otime'] = $data['ctime'] = time();
            $shopData = LibF::M('shop')->where(array('shop_id' => $data['shop_id']))->find();
            if (!empty($shopData)) {
                $data['shop_name'] = $shopData['shop_name'];
                $data['shop_mobile'] = $shopData['shop_mobile'];
            }
            $data['ordertype'] = 1;

            $price = 0;
            $deposit = 0;
            $bottleArray = explode('*', $bottle);
            if (!empty($bottleArray)) {
                if ($order_type == 4 || $order_type == 5) {
                    $data['order_tc_type'] = $order_type;
                    //体验套餐、优惠套餐订单
                    foreach ($bottleArray as $bottleVal) {
                        if (!empty($bottleVal)) {

                            $bArray = explode('|', $bottleVal);
                            $value['order_sn'] = $data['order_sn'];
                            $value['goods_id'] = $bArray[0];
                            $value['goods_name'] = $bArray[2];
                            $value['goods_num'] = $bArray[5] * $bArray[7];
                            $value['goods_type'] = 1;
                            $value['goods_price'] = 0;
                            $value['goods_kind'] = $bArray[3];
                            $value['pay_money'] = $bArray[4] * $bArray[7];
                            $price += $value['pay_money'];
                            $deposit += $bArray[6] * $bArray[7];
                            $orderInfo[] = $value;
                        }
                    }
                } else {
                    foreach ($bottleArray as $bottleVal) {
                        if (!empty($bottleVal)) {

                            $bArray = explode('|', $bottleVal);
                            $value['order_sn'] = $data['order_sn'];
                            $value['goods_id'] = $bArray[0];
                            $value['goods_name'] = $bArray[2];
                            $value['goods_num'] = $bArray[5];
                            $value['goods_type'] = $bArray[1];
                            $value['goods_price'] = $bArray[4];
                            $value['goods_kind'] = $bArray[3];
                            $value['pay_money'] = $bArray[4] * $bArray[5];
                            $price += $value['pay_money'];
                            $orderInfo[] = $value;
                        }
                    }
                }
            }
            $data['money'] = $price;
            $data['deposit'] = $deposit;
            $status = LibF::M('order')->add($data);
            if ($status) {
                LibF::M('order_info')->uinsertAll($orderInfo);
                $returnData['code'] = $status;
            }
        }
        echo json_encode($returnData);
        exit;
    }

    /**
     * 修改密码
     */
    public function retsetpwdAction(){
        $kid = $this->cuid;
        
        $kehuM = LibF::M('kehu');
        $userRow = $kehuM->find($kid);
        $this->_view->assign('userRow', $userRow);
        
        if (IS_POST) {
            $password = $this->getRequest()->getPost('password');
            $old_password = $this->getRequest()->getPost('old_password');
            $confirm_password = $this->getRequest()->getPost('confirm_password');

            $msg = '';
            //相关base64相关解码
            if (!empty($password) && $password == $confirm_password) {

                $kehuModel = new KehuModel();
                $ypassword = $kehuModel->encryptUserPwd($old_password);
                $parent_record = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if ($parent_record === null) {
                    $msg = '此账号不存在';
                } else if ($parent_record['password'] != $ypassword) {
                    $msg = '原始密码不正确';
                } else {
                    $show_password = $kehuModel->encryptUserPwd($password);

                    $data['password'] = $show_password;
                    $status = LibF::M('kehu')->where(array('kid' => $kid))->save($data);
                    if ($status) {
                        $msg = '更新成功';
                    } else {
                        $msg = '更新失败';
                    }
                }
            } else {
                $msg = '两次密码不一致';
            }
            $this->success($msg, '/my/home/index');
        }
    }
}
