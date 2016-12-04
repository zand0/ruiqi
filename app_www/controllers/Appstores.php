<?php

/**
 * @name AppuserController
 * @author wjy
 * @desc app门店端
 * @date 2015-11-17  
 */
class AppstoresController extends \Com\Controller\Common {

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

        /*$url = $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
        if (strrpos($url, '/appuser/login') === false) {
            $token = $_GET('token');

            if (empty($token)) {
                $this->respond(-100, '验证失败！', true);
            }

            if (empty($this->userInfo)) {
                $this->respond(-101, '没有登录或已过期！', true);
            } else {
                
            }
        }*/

        $this->app = new App();
    }

    /**
     * app用户登录
     * 
     * @param $mobile
     * @param $password
     * @param $token
     * 
     * 备注： token 默认未做session 处理
     */
    public function loginAction() {
        
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $password = $this->getRequest()->getPost('password');
            $captcha = $this->getRequest()->getPost('captcha');

            //相关base64相关解码
            if ($password) {
                $password = $this->app->getPassword($password);
            }
            
            if ($mobile) {
                $parent_record = LibF::M('admin_user')->where('mobile_phone = '.$mobile." AND shop_level != 1 ")->find();
                if ($parent_record === null) {
                    $this->app->respond(-1, '此账号不存在');
                } else if ($parent_record['password'] != md5(md5($password).$parent_record['user_salt'])) {
                    $this->app->respond(-2, '密码错误');
                } else {
                    //判断当前验证吗是否正确
                    $token = 123456;
                    $this->app->respond(1, array('user_id' => $parent_record['user_id'],'mobile' => $mobile, 'username' => $parent_record['admin_name'], 'shop_id' => $parent_record['shop_id'],'money' =>0, 'token' => $token));
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app用户退出
     * 
     * @param $mobile
     * @param $token
     */
    public function logoutAction() {

        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $token = $this->getRequest()->getPost('token');

            if ($mobile) {
                $parent_record = LibF::M('admin_user')->where('mobile_phone = '.$mobile." AND shop_level != 1 ")->find();
                if ($parent_record === null) {
                    $this->app->respond(-1, '此账号不存在');
                } else {
                    $token = '';
                    $this->app->respond(1, array('mobile' => $mobile, 'token' => $token));
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app用户密码重置
     * 
     * @param $mobile
     * @param $password
     * @param $token
     */
    public function passresetAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $captcha = $this->getRequest()->getPost('captcha');
            $password = $this->getRequest()->getPost('password');
            $confirm_password = $this->getRequest()->getPost('confirm_password');

            //相关base64相关解码
            if (!empty($password) && $password == $confirm_password) {

                $password = $this->app->getPassword($password);
                if ($mobile) {
                    $parent_record = LibF::M('verification_code')->where(array('mobile' => $mobile, 'status' => 0))->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['code'] != $captcha) {
                        $this->app->respond(-2, '验证码错误');
                    } else {
                        $userData = LibF::M('admin_user')->where('mobile_phone=' . $mobile)->find();
                        $data['password'] = md5(md5($password) . $userData['user_salt']);
                        $status = LibF::M('admin_user')->where('mobile_phone=' . $mobile)->save($data);
                        if ($status) {
                            $udata['status'] = 1;
                            $status = LibF::M('verification_code')->where(array('mobile' => $mobile))->save($udata);
                            $this->app->respond(1, '更新成功');
                        } else {
                            $this->app->respond(-6, '更新失败');
                        }
                    }
                } else {
                    $this->app->respond(-3, '账号格式不正确');
                }
            } else {
                $this->app->respond(-5, '两次密码不一致');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app用户修改密码
     * 
     * @param $mobile
     * @param $password
     */
    public function changepasswordAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $password = $this->getRequest()->getPost('password');
            $old_password = $this->getRequest()->getPost('old_password');
            $confirm_password = $this->getRequest()->getPost('confirm_password');
            $token = $this->getRequest()->getPost('token', 1234);

            //相关base64相关解码
            if (!empty($password) && $password == $confirm_password) {

                $password = $this->app->getPassword($password);
                $old_password = $this->app->getPassword($old_password);
                if ($mobile && $token) {
                    $parent_record = LibF::M('admin_user')->where(array('mobile_phone' => $mobile))->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['password'] != md5(md5($old_password).$parent_record['user_salt'])) {
                        $this->app->respond(-4, '原始密码不正确');
                    } else {
                        $data['password'] = md5(md5($password).$parent_record['user_salt']);
                        $status = LibF::M('admin_user')->where('mobile_phone=' . $mobile)->save($data);
                        if ($status) {
                            $this->app->respond(1, '更新成功');
                        } else {
                            $this->app->respond(-2, '更新失败');
                        }
                    }
                } else {
                    $this->app->respond(-3, '当前用户未登录');
                }
            } else {
                $this->app->respond(-5, '两次密码不一致');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app用户更新自己绑定手机
     * 
     * @param $mobile
     * @param $token
     */
    public function bindmobileAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $captcha = $this->getRequest()->getPost('captcha');
            $old_mobile = $this->getRequest()->getPost('old_mobile');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($captcha)) {
                if ($token) {
                    $parent_record = LibF::M('verification_code')->where(array('mobile' => $old_mobile, 'status' => 0))->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['code'] != $captcha) {
                        $this->app->respond(-4, '验证码不正确');
                    } else {
                        $data['mobile_phone'] = $mobile;
                        $status = LibF::M('admin_user')->where('mobile_phone=' . $old_mobile)->save($data);
                        if ($status) {
                            $this->app->respond(1, '更新成功');
                        } else {
                            $this->app->respond(-2, '更新失败');
                        }
                    }
                } else {
                    $this->app->respond(-3, '当前用户未登录');
                }
            } else {
                $this->app->respond(-5, '数据不存在');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * app门店查询功能按照订单、送气工、瓶类型查询
     * 
     * @param $mobile
     * @param $token
     */
    public function statisticslistAction() {

        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $shop_id = $this->getRequest()->getPost('shop_id'); //新增
            $type = $this->getRequest()->getPost('type', 1); //1按订单2按照人工3按照瓶子种类
            $beginTime = $this->getRequest()->getPost('begintime');
            $endTime = $this->getRequest()->getPost('endtime');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {
                //备注 当前最新的订单信息
                switch ($type) {
                    case 1:

                        $orderModel = new OrderModel();
                        $returnData = $orderModel->orderWorkApp(array('shop_id' => $shop_id,'start_time'=>$beginTime,'end_time'=>$endTime));
                        $data = array();
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $data[] = $value;
                            }
                        }

                        break;
                    case 2:
                        
                        /*$dataModel = new DatatransferModel();
                        $returnData = $dataModel->shipperStatistics($shop_id);
                        if(!empty($returnData)){
                            foreach ($returnData as $value){
                                $val['workname'] = $value['shipper_name'];
                                $val['worksn'] = $value['shipper_id'];
                                $val['num'] = $value['num'];
                                $val['appraisal'] = 0;
                                
                                $data[] = $val;
                            }
                        }*/
                        $returnData = LibF::M('shipper')->where('shop_id='.$shop_id)->select();
                        if(!empty($returnData)){
                            foreach($returnData as $value){
                                $val['workname'] = $value['shipper_name'];
                                $val['worksn'] = $value['shipper_id'];
                                $val['num'] = $value['order_no'];
                                $val['appraisal'] = $value['commen'];
                                
                                $data[] = $val;
                            }
                        }
                        
                        break;
                    case 3:
                        //按照类型
                        $total = array();
                        $dataModel = new DatatransferModel();
                        $where = " rq_order_info.goods_type = 1 ";
                        $returnData = $dataModel->bottleTypeStatistics($shop_id,$where);
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $val['title'] = $value['goods_name'];
                                $val['num'] = $value['num'];
                                $val['money'] = $value['num']*$value['pay_money'];

                                $total[] = $val;
                            }
                        }
                        $data = array(
                            'data' => $total,
                            'total' => '',
                            'pay' => ''
                        );
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
     * app用户获取验证码
     * 
     * @param $mobile
     * @param $token
     */
    public function sendcodeAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $type = $this->getRequest()->getPost('type', 1);
            if (!empty($mobile)) {
                $userData = LibF::M('admin_user')->where('mobile_phone = '.$mobile." AND shop_level != 1 ")->find();
                if (!empty($userData)) {
                    $code = 1234; //验证码

                    $data['mobile'] = $mobile;
                    $data['code'] = $code;
                    $data['time'] = time();

                    $type = LibF::M('verification_code')->add($data);
                    if ($type) {
                        $this->app->respond(1, array('mobile' => $mobile, 'code' => $code));
                    } else {
                        $this->app->respond(-2, '存储失败');
                    }
                } else {
                    $this->app->respond(-3, '此手机号不存在');
                }
            } else {
                $this->app->respond(-5, '未提交参数');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 简报列表展示数据列表
     * 
     * @param $mobile
     * @param $token
     */
    public function briefingAction() {
        
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $type = $this->getRequest()->getPost('type', 1); //1进货2销售3库存4全部瓶6配件7人员5入库瓶子8进货配件
            $beginTime = $this->getRequest()->getPost('begintime');
            $endTime = $this->getRequest()->getPost('endtime');
            $token = $this->getRequest()->getPost('token', 1234);

            $data = array();
            if (!empty($mobile) && !empty($token)) {
                switch ($type) {
                    case 1:  //按照充装计划
                        $total = array();
                        $dataModel = new DatatransferModel();
                        $returnData = $dataModel->fillStatics($shop_id);
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $value['money'] = '';
                                $total[] = $value;
                            }
                        }
                        $data = array(
                            'data' => $total,
                            'total' => '',
                            'pay' => ''
                        );
                        break;
                    case 2: //按照订单
                        $total = array();
                        $dataModel = new DatatransferModel();
                        $returnData = $dataModel->bottleTypeStatistics($shop_id);
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $val['title'] = $value['goods_name'];
                                $val['num'] = $value['num'];
                                $val['money'] = $value['num'] * $value['pay_money'];

                                $total[] = $val;
                            }
                        }
                        $data = array(
                            'data' => $total,
                            'total' => '',
                            'pay' => ''
                        );
                        break;
                    case 3://调用库存列表
                        $bottleTypeModel = new BottlepriceModel();
                        $bottleType = $bottleTypeModel->getBottleTypeArray('', array('shop_id' => $shop_id));

                        $returnData = array();
                        $dataModel = new DatatransferModel();
                        $where = array('shop_id' => $shop_id, 'status' => 1);

                        $data = $dataModel->shopKuncun($shop_id, $where);
                        if (isset($data['data']) && !empty($data['data'])) {
                            foreach ($data['data'] as $key => $value) {
                                $val['title'] = isset($bottleType[$key]) ? $bottleType[$key]['bottle_name'] : '';
                                $val['num'] = $value;
                                $val['money'] = isset($bottleType[$key]) ? $bottleType[$key]['price'] : '';

                                $returnData[] = $val;
                            }
                        }

                        $data = array(
                            'data' => $returnData,
                            'total' => '',
                            'pay' => ''
                        );
                        break;
                    case 4:
                        $bottleTypeModel = new BottlepriceModel();
                        $bottleType = $bottleTypeModel->getBottleTypeArray('', array('shop_id' => $shop_id));

                        $returnData = array();
                        $dataModel = new DatatransferModel();
                        $where = array('shop_id' => $shop_id);
                        $where['status'] = array('neq',1);

                        $data = $dataModel->shopKuncun($shop_id, $where);
                        if (isset($data['data']) && !empty($data['data'])) {
                            foreach ($data['data'] as $key => $value) {
                                $val['title'] = isset($bottleType[$key]) ? $bottleType[$key]['bottle_name'] : '';
                                $val['num'] = $value;
                                $val['money'] = isset($bottleType[$key]) ? $bottleType[$key]['price'] : '';

                                $returnData[] = $val;
                            }
                        }

                        $data = array(
                            'data' => $returnData,
                            'total' => '',
                            'pay' => ''
                        );
                        break;
                    case 5://进货的瓶子
                        //$bottleTypeModel = new BottlepriceModel();
                        //$bottleType = $bottleTypeModel->getBottleTypeArray('', array('shop_id' => $shop_id));
                        $bottleTypeModel = new BottletypeModel();
                        $bottleType  = $bottleTypeModel->getBottleTypeArray();
                        
                        $returnData = array();
                        $dataModel = new DatatransferModel();
                        $where = array('shop_id' => $shop_id, 'is_open' => 1);
                        $where['status'] = array('eq', 1);

                        $data = $dataModel->shopKuncun($shop_id, $where);
                        if (isset($data['data']) && !empty($data['data'])) {
                            foreach ($data['data'] as $key => $value) {
                                $val['title'] = isset($bottleType[$key]) ? $bottleType[$key]['bottle_name'] : '';
                                $val['num'] = $value;
                                $val['money'] = isset($bottleType[$key]) ? $bottleType[$key]['price'] : '';

                                $returnData[] = $val;
                            }
                        }

                        $data = array(
                            'data' => $returnData,
                            'total' => '',
                            'pay' => ''
                        );
                        break;
                    case 6:

                        $total = array();

                        $dataModel = new DatatransferModel();
                        $where = 'rq_warehousing.type = 2 ';
                        $returnData = $dataModel->shopwarehousing($shop_id);
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $val['title'] = $value['product_name'];
                                $val['num'] = $value['product_num'];
                                $val['money'] = $value['product_price'];

                                $total[] = $val;
                            }
                        }

                        $data = array(
                            'data' => $total,
                            'total' => '',
                            'pay' => ''
                        );
                        break;
                    case 7:

                        /* $dataModel = new DatatransferModel();
                          $returnData = $dataModel->shipperStatistics($shop_id);
                          if(!empty($returnData)){
                          foreach ($returnData as $value){
                          $val['workname'] = $value['shipper_name'];
                          $val['worksn'] = $value['shipper_id'];
                          $val['num'] = $value['num'];
                          $val['appraisal'] = 0;

                          $data[] = $val;
                          }
                          } */
                        $returnData = LibF::M('shipper')->where('shop_id=' . $shop_id)->select();
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $val['workname'] = $value['shipper_name'];
                                $val['worksn'] = $value['shipper_id'];
                                $val['num'] = $value['order_no'];
                                $val['appraisal'] = $value['commen'];

                                $data[] = $val;
                            }
                        }
                        break;
                    case 8: //进货配件
                        $total = array();

                        $dataModel = new DatatransferModel();
                        $where = 'rq_warehousing.type = 1 ';
                        $returnData = $dataModel->shopwarehousing($shop_id);
                        if (!empty($returnData)) {
                            foreach ($returnData as $value) {
                                $val['title'] = $value['product_name'];
                                $val['num'] = $value['product_num'];
                                $val['money'] = $value['product_price'];

                                $total[] = $val;
                            }
                        }

                        $data = array(
                            'data' => $total,
                            'total' => '',
                            'pay' => ''
                        );
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
     * 欠款发货申请
     * 
     * @param $mobile
     * @param $token
     */
    public function arrearsAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shop_id) && !empty($token)) {
                $orderModel = new OrderModel();
                
                $where['shop_id'] = $shop_id;
                $where['status'] = 6;

                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();
                
                $data = $orderModel->orderListApp($where,'',$bottleTypeData,$productTypeData);
                $newData = array();
                if(!empty($data)){
                    foreach($data as $value){
                        $newData[] = $value;
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
    
    /**
     * 确认欠款发货
     */
    public function confirmarrearsAction() {
        if ($this->getRequest()->isPost()) {
            $order_id = $this->getRequest()->getPost('order_id');
            $comment = $this->getRequest()->getPost('comment');
            $type = $this->getRequest()->getPost('type',1);//1同意 2拒绝
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($order_id) && !empty($token)) {
                $where['order_id'] = $order_id;

                $param['comment'] = $comment;
                $param['status'] = $type;
                $status = LibF::M('order')->where($where)->save($param);

                if ($status) {
                    $this->app->respond(1, '审核成功');
                } else {
                    $this->app->respond(-3, '审核失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 额度申请列表
     * 
     */
    public function arrearslistAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shop_id) && !empty($token)) {
                
                $param['shop_id'] = $shop_id;
                $param['type'] = 2;
                
                $arearsModel = new ArrearsModel();
                $data = $arearsModel->listdata($param);
                if(!empty($data)){
                    foreach($data as &$value){
                        $value['time'] = (!empty($value['time_created'])) ? date("Y-m-d",$value['time_created']) : '';
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
     * 申请额度
     */
    public function createarrearsAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shop_name = $this->getRequest()->getPost('shop_name');
            //$bottle  = $this->getRequest()->getPost('bottle');
            //$product = $this->getRequest()->getPost('product');
            $money = $this->getRequest()->getPost('money');
            
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shop_id) && !empty($token) && !empty($money)) {
                $param['shop_id'] = $shop_id;
                $param['shop_name'] = $shop_name;
                $param['money'] = $money;
                $param['type'] = 2;
                $param['time_created'] = time();
                //$param['bottle'] = $bottle;
               // $param['products'] = $product;
                
                $arearsModel = new ArrearsModel();
                $data = $arearsModel->add($param);
                if ($data['status'] == 200) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '创建失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 门店库存 //显示到主页
     * 
     * @param $mobile
     * @param $token
     */
    public function inventoryAction() {

        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $beginTime = $this->getRequest()->getPost('begintime');
            $endTime = $this->getRequest()->getPost('endtime');
            $type = $this->getRequest()->getPost('type',1);
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {
                //$type = array(1 => '全部', 2 => '空瓶', 3 => '重瓶');
                $bottleTypeModel = new BottletypeModel();
                $bottleType = $bottleTypeModel->getBottleTypeArray();

                $returnData = array();
                $dataModel = new DatatransferModel();
                if ($type == 1) {
                    $where = array('shop_id' => $shop_id);
                } else {
                    if ($type == 2)
                        $where = array('shop_id' => $shop_id, 'is_open' => 0);
                    else
                        $where = array('shop_id' => $shop_id, 'is_open' => 1);
                }
                $where['status'] = 1;
                $data = $dataModel->shopKuncun($shop_id, $where);
                if (isset($data['data']) && !empty($data['data'])) {
                    foreach ($data['data'] as $key => $value) {
                        $val['title'] = isset($bottleType[$key]) ? $bottleType[$key]['bottle_name'] : '';
                        $val['num'] = $value;

                        $returnData[] = $val;
                    }
                }

                $pjdata = $dataModel->shopKuncunPj($shop_id, array('shop_id' => $shop_id, 'shop_level' => 1));
                if (!empty($pjdata)) {
                    foreach ($pjdata as $pjVal) {
                        $returnData[] = $pjVal;
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

    /**
     * 门店库存列表 //列表
     * 
     * @param $mobile
     * @param $token
     */
    public function inventorylistAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $type = $this->getRequest()->getPost('type', 1);//1全部2空瓶3重瓶
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {
                //$bottleTypeModel = new BottlepriceModel();
                //$bottleType = $bottleTypeModel->getBottleTypeArray('', array('shop_id' => $shop_id));

                $returnData = array();
                $dataModel = new DatatransferModel();
                if ($type == 1) {
                    $where = array('shop_id' => $shop_id);
                } else {
                    if ($type == 2)
                        $where = array('shop_id' => $shop_id, 'is_open' => 0);
                    else
                        $where = array('shop_id' => $shop_id, 'is_open' => 1);
                }
                $where['status'] = 1;
                $data = $dataModel->shopKuncun($shop_id, $where);
                if (isset($data['data']) && !empty($data['data'])) {
                    foreach ($data['data'] as $key => $value) {
                        $val['title'] = isset($bottleType[$key]) ? $bottleType[$key]['bottle_name'] : '';
                        $val['num'] = $value;
                        $val['unitprice'] = isset($bottleType[$key]) ? $bottleType[$key]['price'] : '';

                        $returnData['data'][] = $val;
                    }
                    $returnData['total'] = $data['total'];
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 门店库存配件 //
     * 
     */
    public function pjlistAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shop_id) && !empty($token)) {
                $data = LibF::M('products_tj')->field('sum(num) as total,products_no,products_name')->where(array('products_type' => 1, 'shop_id' => $shop_id))->group('products_no')->select();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 门店入库
     * 
     * 
     */
    public function inshopAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $card_id = $this->getRequest()->getPost('card_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $admin_user = $this->getRequest()->getPost('user_name');
            $admin_id = $this->getRequest()->getPost('user_id');
            $type = $this->getRequest()->getPost('type_id', 1); //1重瓶0空瓶2配件
            $data = $this->getRequest()->getPost('data'); //数据串
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id) && !empty($data)) {
                $data = json_decode($data, true);
                $dataTransModel = new DatatransferModel();
                if ($type == 2) {
                    //配件入库
                    $data = $dataTransModel->warehousing($data, $shop_id, $admin_id, $admin_user, 1);
                } else {
                    //钢瓶入库
                    if ($type == 1) {
                        //门店重瓶入库，气站进来
                        $data = $dataTransModel->storeInventory($data, $shop_id, $type);
                    } else {
                        //门店空瓶入库，送气工进来
                        //$data = $dataTransModel->shipperInventory($data, $shipper_id, $type);
                        $data = $dataTransModel->storeInventory($data, $shop_id, $type);
                    }
                }
                if ($data['status'] == 200) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '创建订单失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 门店出库
     * 
     * 
     */
    public function outshopAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $car_id = $this->getRequest()->getPost('car_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $admin_user = $this->getRequest()->getPost('user_name');
            $admin_id = $this->getRequest()->getPost('user_id');
            $type = $this->getRequest()->getPost('type_id', 1); //1重瓶0空瓶2配件
            $data = $this->getRequest()->getPost('data'); //数据串
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id) && !empty($data)) {
                $data = json_decode($data, true);
                $dataTransModel = new DatatransferModel();
                if ($type == 2) {
                    //配件出库
                    $data = $dataTransModel->warehousing($data, $shop_id, $admin_id, $admin_user, 0);
                } else {
                    //钢瓶出库
                    if ($type == 1) {
                        //门店重瓶出库 出给送气工
                        $data = $dataTransModel->shipperInventory($data, $shipper_id, $type);
                    } else {
                        //门店空瓶出库，出给气站
                        $data = $dataTransModel->storeOutInventory($data, $shop_id, $type);
                    }
                }

                if ($data['status'] == 200) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '创建订单失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
}