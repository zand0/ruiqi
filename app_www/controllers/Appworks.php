<?php

/**
 * @app送气工端
 */
class AppworksController extends \Com\Controller\Common {

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
    protected $pageSize;
    
    protected $financial;

    /**
     * 地址
     * @var $url
     */
    protected $url;
    
    protected $bottleStatus;


    /**
     * 初始化
     */
    public function init() {

        /* $url = $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
          if (strrpos($url, '/appuser/login') === false) {
          $token = $_GET('token');

          if (empty($token)) {
          $this->respond(-100, '验证失败！', true);
          }

          if (empty($this->userInfo)) {
          $this->respond(-101, '没有登录或已过期！', true);
          } else {

          }
          } */

        $this->app = new App();
        $this->pageSize = 15;
        
        $financialData = array(1 => '订单收入',2=>'押金收入',3=>'折旧支出',4=>'残液支出', 5 =>'余气支出',6 => '押金支出');
        $this->financial = $financialData;
        
        $this->bottleStatus = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
        
        $this->url = "http://cztest.ruiqi100.com";
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
                $parent_record = LibF::M('shipper')->where(array('mobile_phone' => $mobile))->find();
                if ($parent_record === null) {
                    $this->app->respond(-1, '此账号不存在');
                } else if ($parent_record['password'] !== md5($password)) {
                    $this->app->respond(-2, '密码错误');
                } else {
                    //判断当前验证吗是否正确
                    $shopObject = ShopModel::getShopArray();
                    $shopName = isset($shopObject[$parent_record['shop_id']]) ? $shopObject[$parent_record['shop_id']]['shop_name'] : '';
                    $token = 123456;

                    $ctime = ($parent_record['ctime'] > 0) ? date('Y-m-d', $parent_record['ctime']) : '';
                    $returnData = array('shipper_id' => $parent_record['shipper_id'], 'mobile' => $mobile, 'username' => $parent_record['shipper_name'], 'shop_id' => $parent_record['shop_id'], 'shop_name' => $shopName, 'shiper_id' => $parent_record['shipper_id'], 'role' => $parent_record['role'], 'money' => $parent_record['money'], 'ctime' => $ctime, 'token' => $token);
                    $this->app->respond(1, $returnData);
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
                $parent_record = LibF::M('shipper')->where(array('mobile_phone' => $mobile))->find();
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
            $captcha = $this->getRequest()->getPost('captcha', 1234);
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
                        $data['password'] = md5($password);
                        $status = LibF::M('shipper')->where(array('mobile_phone' => $mobile))->save($data);
                        if ($status) {
                            $udata['status'] = 1;
                            LibF::M('verification_code')->where(array('mobile' => $mobile))->save($udata);
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
                    $parent_record = LibF::M('shipper')->where(array('mobile_phone' => $mobile))->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['password'] != md5($old_password)) {
                        $this->app->respond(-4, '原始密码不正确');
                    } else {
                        $data['password'] = md5($password);
                        $status = LibF::M('shipper')->where('mobile_phone=' . $mobile)->save($data);
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
                        $data['mobile'] = $mobile;
                        $status = LibF::M('shipper')->where('mobile_phone = ' . $old_mobile)->save($data);
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
                $userData = LibF::M('shipper')->where(array('mobile_phone' => $mobile))->find();
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

    //1.1 app送气工订单列表
    public function orderlistAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $token = $this->getRequest()->getPost('token', 1234);
            $type = $this->getRequest()->getPost('type');   //1未完成订单2已完成订单3问题订单-1 已关闭

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            if (!empty($mobile) && !empty($token)) {
                //备注 当前订单状态 1未派发 2配送中 4已完成
                $where = array('shipper_mobile' => $mobile, 'page' => $page, 'pagesize' => $this->pageSize);
                if ($type == 1) {
                    $where['status'] = 2;
                } else if ($type == 2) {
                    $where['status'] = 4;
                } else if ($type == 3) {
                    //问题订单
                    $where['status'] = 5;
                } else {
                    $where['status'] = array('gt', 0);
                }

                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $orderModel = new OrderModel();
                $data = $orderModel->orderListApp($where, '', $bottleTypeData, $productTypeData);
                $newData = array();
                if (!empty($data)) {
                    foreach ($data as $value) {
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

    //1.2 app送气工退瓶订单
    public function depositAction() {
        if ($this->getRequest()->isPost()) {
            //备注 status 0 未派发 1配送中 2已完成

            $shipper_id = $this->getRequest()->getPost('shipper_id'); //送气工id
            $shop_id = $this->getRequest()->getPost('shop_id'); //门店id
            $type = $this->getRequest()->getPost('type');

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token) && !empty($shipper_id)) {
                if ($type == 1) {
                    $param['status'] = 1;
                } else if ($type == 2) {
                    $param['status'] = 2;
                }
                $depositModel = new DepositModel();
                $data = $depositModel->shipperDeposit($shop_id, $shipper_id, $param);
                if (!empty($data)) {
                    $orderStatus = array(
                        0 => '未派发', 1 => '配送中', 2 => '已完成'
                    );

                    //获取当前钢瓶规格
                    $bottleModel = new BottleModel();
                    $bottleData = $bottleModel->bottleOData();

                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                    foreach ($data as &$value) {
                        $bottleArr = !empty($value['bottle_text']) ? json_decode($value['bottle_text'], TRUE) : '';
                        $value['datainfo'] = array();
                        if (!empty($bottleArr)) {
                            foreach ($bottleArr as $val) {
                                $kind_id = isset($bottleData['xinpian'][$val]) ? $bottleData['xinpian'][$val]['type'] : (isset($bottleData['number'][$val]) ? $bottleData['number'][$val]['type'] : ''); //规格
                                $v['typename'] = isset($bottleTypeData[$kind_id]) ? $bottleTypeData[$kind_id]['bottle_name'] : '';
                                $v['xinpian'] = $val;
                                $v['number'] = isset($bottleData['xinpian'][$val]) ? $bottleData['xinpian'][$val]['number'] : (isset($bottleData['number'][$val]) ? $bottleData['number'][$val]['number'] : ''); //规格
                                $value['datainfo'][] = $v;
                            }
                        }
                        $value['status_name'] = $orderStatus[$value['status']];
                    }
                } else {
                    $data = array();
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //1.3 app退瓶订单结算*********
    public function confirmdepositAction() {
        if ($this->getRequest()->isPost()) {
            $depositsn = $this->getRequest()->getPost('depositsn'); //订单号
            $receiptno = $this->getRequest()->getPost('receiptno'); //收据号
            $bottle = $this->getRequest()->getPost('bottle'); //扫码钢瓶吗
            $bottle_data = $this->getRequest()->getPost('bottle_data'); //data 数据串
            $money = $this->getRequest()->getPost('money');
            $kid = $this->getRequest()->getPost('kid');

            $cyweight = $this->getRequest()->getPost('canye_weight', 0); //余气重量
            $cyMoney = $this->getRequest()->getPost('canye_money', 0);   //余气金额

            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($depositsn) && !empty($token) && !empty($bottle)) {
                /*if (!empty($receiptno)) {
                    $param['receiptno'] = $receiptno;
                    LibF::M('deposit')->where(array('receiptno' => $receiptno))->save(array('status' => 1));
                }*/
                $time = time();

                $param['bottle_text'] = $bottle;
                $param['money'] = $money;
                if ($cyweight)
                    $param['shouldweight'] = $cyweight;
                if ($cyMoney)
                    $param['shouldmoney'] = $cyMoney;
                $param['status'] = 2;
                $param['ctime'] = $time;
                $where['depositsn'] = $depositsn;
                $status = LibF::M('deposit_list')->where($where)->save($param);
                if ($status) {
                    $moneyStatus = LibF::M('shipper')->where(array('shipper_id' => $shipper_id))->setDec('money', $param['money']); //zt：收款后送气工余额减少
                    //删除用户对应的瓶子
                    if (!empty($bottle)) {
                        $bJson = json_decode($bottle, true);
                        if (!empty($bJson)) {
                            $bArr = array();

                            //获取当前钢瓶规格
                            $bottleModel = new BottleModel();
                            $bottleData = $bottleModel->bottleOData();
                            foreach ($bJson as $bJVal) {
                                if (isset($bottleData['xinpian'][$bJVal])) {
                                    $bArr[] = $bJVal;
                                } else {
                                    $bArr[] = $bottleData['number'][$bJVal]['xinpian'];
                                }
                            }
                            if (!empty($bArr) && $kid) {
                                $condition['xinpian'] = array('in', $bArr);
                                $condition['number'] = array('in', $bArr);
                                $condition['_logic'] = 'OR';
                                $map['_complex'] = $condition;
                                $map['kid'] = $kid;
                                LibF::M('kehu_inventory')->where($map)->delete();
                            }
                            $dataModel = new DataInventoryModel();
                            $rData = $dataModel->shipperck($shop_id, $shipper_id, $kid, $bArr, 1, 1);  //送气工空瓶入库

                            $this->shipperDepositAdd($shipper_id, $bJson, $kid, $bottleData);
                        }
                    }

                    if ($param['money']) {
                        $payInsert['paylist_no'] = $depositsn;
                        $payInsert['shipper_id'] = $shipper_id;
                        $payInsert['shipper_name'] = $shipper_name;
                        $payInsert['mobile_phone'] = $shipper_mobile;
                        $payInsert['money'] = $param['money'];
                        $payInsert['shop_id'] = $shop_id;
                        $payInsert['time'] = date('Y-m-d');
                        $payInsert['time_created'] = $time;
                        
                        $depositMoney = $canyeMoney = $payInsert;
                        if ($cyMoney > 0) {
                            $depositMoney['is_statistics'] = 1;
                            $depositMoney['type'] = 3;
                            $depositMoney['status'] = 2;
                            $depositArr[] = $depositMoney;
                            $canyeMoney['is_statistics'] = 0;
                            $canyeMoney['type'] = 6;
                            $canyeMoney['status'] = 2;
                            $depositArr[] = $canyeMoney;
                            LibF::M('shipper_paylist')->uinsertAll($depositArr);
                        } else {
                            $payInsert['is_statistics'] = 1;
                            $payInsert['type'] = 3;
                            $payInsert['status'] = 2;
                            LibF::M('shipper_paylist')->add($payInsert);
                        }
                    }

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

    //2.1 app创建订单
    public function createorderAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $username = $this->getRequest()->getPost('username');

            $sheng = $this->getRequest()->getPost('sheng');
            $shi = $this->getRequest()->getPost('shi');
            $qu = $this->getRequest()->getPost('qu');
            $cun = $this->getRequest()->getPost('cun');
            $address = $this->getRequest()->getPost('address');

            $card_sn = $this->getRequest()->getPost('card_sn');
            $user_type = $this->getRequest()->getPost('user_type', 1); //1居民2商业

            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');

            $shop_id = $this->getRequest()->getPost('shop_id');

            $tc_type = $this->getRequest()->getPost('tc_type', 0); //套餐类型0正常订单4体验套餐5优惠套餐
            $youhuijson = $this->getRequest()->getPost('youhuijson'); //优惠券id(余额券)

            $status = $this->getRequest()->getPost('status', 0);
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {
                $time = time();
                if ($status == 0) {
                    $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                    if (!empty($parent_record)) {
                        $this->app->respond(-1, '当前手机号已被注册');
                    } else {
                        $app = new App();
                        $orderSn = $app->build_order_no();
                        $data['kehu_sn'] = 'kh' . $orderSn;
                        $data['mobile_phone'] = $mobile;
                        $data['mobile_list'] = $mobile;
                        $data['user_name'] = $username;
                        $data['ktype'] = $user_type;
                        if (!empty($card_sn))
                            $data['card_sn'] = $card_sn;
                        $kehuModel = new KehuModel();
                        $show_password = $kehuModel->encryptUserPwd($mobile);
                        $data['password'] = $show_password;
                        if (!empty($sheng))
                            $data['sheng'] = $sheng;
                        if (!empty($shi))
                            $data['shi'] = $shi;
                        if (!empty($qu))
                            $data['qu'] = $qu;
                        if (!empty($cun))
                            $data['cun'] = $cun;
                        if (!empty($address))
                            $data['address'] = $address;
                        $data['shop_id'] = $shop_id;
                        $data['ctime'] = $time;
                        $type = LibF::M('kehu')->add($data);
                        if ($type) {
                            $uparam['kid'] = $type;
                            if (!empty($sheng))
                                $uparam['sheng'] = $sheng;
                            if (!empty($shi))
                                $uparam['shi'] = $shi;
                            if (!empty($qu))
                                $uparam['qu'] = $qu;
                            if (!empty($cun))
                                $uparam['cun'] = $cun;
                            if (!empty($address))
                                $uparam['address'] = $address;
                            $uparam['receiver'] = $username;
                            $uparam['receiver_tel'] = $mobile;
                            $uparam['isdefault'] = 1;
                            LibF::M('kehu_address')->add($uparam);

                            //添加订单接收数据
                            $data['kid'] = $type;
                            $data['username'] = $username;
                            $data['mobile'] = $mobile;
                            $data['sheng'] = $sheng;
                            $data['shi'] = $shi;
                            $data['qu'] = $qu;
                            $data['cun'] = $cun;
                            $data['address'] = $address;
                            $data['shop_id'] = $shop_id;
                            $data['money'] = $this->getRequest()->getPost('money', 0);
                            $data['urgent'] = $this->getRequest()->getPost('urgent', 0); //是否紧急
                            $data['goodtime'] = $this->getRequest()->getPost('goodtime'); //希望配送时间
                            if (!empty($data['goodtime']))
                                $data['goodtime'] = strtotime($data['goodtime']);
                            $data['isold'] = $this->getRequest()->getPost('isold', 0); //是否折旧
                            $data['ismore'] = $this->getRequest()->getPost('ismore', 0); //是否余气
                            $data['shipper_id'] = $shipper_id;
                            $data['shipper_name'] = $shipper_name;
                            $data['shipper_mobile'] = $shipper_mobile;
                            $data['status'] = 2;
                            $data['kehu_type'] = $user_type;
                            $data['ctime'] = $time;
                            $data['order_tc_type'] = $tc_type;
                            $data['youhui_data'] = $youhuijson;

                            $dataInfo = $this->getRequest()->getPost('data'); //详情串
                            if (!empty($dataInfo))
                                $dataInfo = json_decode($dataInfo, true);

                            $dataTrans = new DatatransferModel();
                            $returnData = $dataTrans->kehuOrder($data, $dataInfo);
                            if ($returnData['status'] == 200) {
                                $rData = array(
                                    'ordersn' => $returnData['msg'],
                                    'kid' => $type
                                );
                                $this->app->respond(1, $rData);
                            } else {
                                $this->app->respond(-3, '创建订单失败');
                            }
                        } else {
                            $this->app->respond(-2, '注册失败');
                        }
                    }
                } else {
                    $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                    if (!empty($parent_record)) {
                        //添加订单接收数据
                        $param['kid'] = $parent_record['kid'];
                        $param['mobile'] = $parent_record['mobile_phone'];
                        $param['username'] = $username;
                        //$param['address_id'] = $this->getRequest()->getPost('address_id', 0);
                        $param['sheng'] = $sheng;
                        $param['shi'] = $shi;
                        $param['qu'] = $qu;
                        $param['cun'] = $cun;
                        $param['address'] = $parent_record['address'];
                        $param['shop_id'] = $shop_id;
                        $param['money'] = $this->getRequest()->getPost('money', 0);
                        $param['urgent'] = $this->getRequest()->getPost('urgent', 0); //是否紧急
                        $param['goodtime'] = $this->getRequest()->getPost('goodtime'); //希望配送时间
                        if (!empty($param['goodtime']))
                            $param['goodtime'] = strtotime($param['goodtime']);
                        $param['isold'] = $this->getRequest()->getPost('isold', 0); //是否折旧
                        $param['ismore'] = $this->getRequest()->getPost('ismore', 0); //是否余气
                        $param['shipper_id'] = $shipper_id;
                        $param['shipper_name'] = $shipper_name;
                        $param['shipper_mobile'] = $shipper_mobile;
                        $param['order_tc_type'] = $tc_type;
                        $param['status'] = 2;
                        $param['kehu_type'] = $parent_record['ktype'];
                        $param['ctime'] = $time;
                        $param['youhui_data'] = $youhuijson;

                        $data = $this->getRequest()->getPost('data'); //详情串
                        if (!empty($data))
                            $data = json_decode($data, true);

                        $dataTrans = new DatatransferModel();
                        $returnData = $dataTrans->kehuOrder($param, $data);
                        if ($returnData['status'] == 200) {
                            if (!empty($youhuijson)) {
                                $yhData = json_decode($youhuijson, TRUE);
                                $uData['status'] = 1;
                                $uWhere['id'] = array('in', $yhData);
                                LibF::M('promotions_user')->where($uWhere)->save($uData);
                            }

                            $rData = array(
                                'ordersn' => $returnData['msg'],
                                'kid' => $parent_record['kid']
                            );
                            $this->app->respond(1, $rData);
                        } else {
                            $this->app->respond(-3, '创建订单失败');
                        }
                    } else {
                        $this->app->respond(-1, '当前手机号未被注册');
                    }
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //2.1.1 app创建新用户
    public function createkehuAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $username = $this->getRequest()->getPost('username');
            $sheng = $this->getRequest()->getPost('sheng');
            $shi = $this->getRequest()->getPost('shi');
            $qu = $this->getRequest()->getPost('qu');
            $cun = $this->getRequest()->getPost('cun');
            $address = $this->getRequest()->getPost('address');
            $card_sn = $this->getRequest()->getPost('card_sn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $user_type = $this->getRequest()->getPost('user_type', 1); //1居民2商业
            $recommended = $this->getRequest()->getPost('recommended'); //推荐人

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($mobile) && !empty($token)) {
                $time = time();
                $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                if (!empty($parent_record)) {
                    $this->app->respond(-1, '当前手机号已被注册');
                } else {
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $data['kehu_sn'] = 'kh' . $orderSn;
                    $data['mobile_phone'] = $mobile;
                    $data['mobile_list'] = $mobile;
                    $data['user_name'] = $username;
                    $data['ktype'] = $user_type;
                    $data['card_sn'] = $card_sn;
                    $kehuModel = new KehuModel();
                    $show_password = $kehuModel->encryptUserPwd($mobile);
                    $data['password'] = $show_password;
                    if (!empty($sheng))
                        $data['sheng'] = $sheng;
                    if (!empty($shi))
                        $data['shi'] = $shi;
                    if (!empty($qu))
                        $data['qu'] = $qu;
                    if (!empty($cun))
                        $data['cun'] = $cun;
                    if (!empty($address))
                        $data['address'] = $address;
                    $data['shop_id'] = $shop_id;
                    if (!empty($recommended))
                        $data['recommended'] = $recommended;
                    $data['ctime'] = $time;
                    $status = LibF::M('kehu')->add($data);
                    if ($status) {
                        $uparam['kid'] = $status;
                        if (!empty($sheng))
                            $uparam['sheng'] = $sheng;
                        if (!empty($shi))
                            $uparam['shi'] = $shi;
                        if (!empty($qu))
                            $uparam['qu'] = $qu;
                        if (!empty($cun))
                            $uparam['cun'] = $cun;
                        if (!empty($address))
                            $uparam['address'] = $address;
                        $uparam['receiver'] = $username;
                        $uparam['receiver_tel'] = $mobile;
                        $uparam['isdefault'] = 1;
                        LibF::M('kehu_address')->add($uparam);

                        $rData = array(
                            'kehusn' => $data['kehu_sn'],
                            'kid' => $status
                        );
                        $this->app->respond(1, $rData);
                    } else {
                        $this->app->respond(-2, '创建失败');
                    }
                }
            } else {
                $this->app->respond(-3, '参数提交不完全');
            }
        } else {
            $this->app->respond(-3, '未提交参数');
        }
    }

    //2.1.2 app创建订单
    public function createkhorderAction() {
        $mobile = $this->getRequest()->getPost('mobile');  //当前用户手机号

        $sheng = $this->getRequest()->getPost('sheng');  //地址
        $shi = $this->getRequest()->getPost('shi');
        $qu = $this->getRequest()->getPost('qu');
        $cun = $this->getRequest()->getPost('cun');
        $address = $this->getRequest()->getPost('address');

        $card_sn = $this->getRequest()->getPost('card_sn'); //用户卡号
        $user_type = $this->getRequest()->getPost('user_type', 1); //1居民2商业

        $shipper_id = $this->getRequest()->getPost('shipper_id'); //送气工
        $shipper_name = $this->getRequest()->getPost('shipper_name');
        $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');

        $shop_id = $this->getRequest()->getPost('shop_id'); //门店

        $money = $this->getRequest()->getPost('money', 0);  //订单金额

        $urgent = $this->getRequest()->getPost('urgent', 0); //是否紧急
        $goodtime = $this->getRequest()->getPost('goodtime'); //希望配送时间

        $isold = $this->getRequest()->getPost('isold', 0); //是否折旧
        $ismore = $this->getRequest()->getPost('ismore', 0); //是否余气
        $tc_type = $this->getRequest()->getPost('tc_type', 0); //套餐类型0正常订单4体验套餐5优惠套餐

        $dataInfo = $this->getRequest()->getPost('data'); //详情串

        $youhuijson = $this->getRequest()->getPost('youhuijson'); //优惠券id(余额券)
        $youhuimoney = $this->getRequest()->getPost('yh_money'); //优惠券金额(余额券)

        $discountmoney = $this->getRequest()->getPost('count_money'); //优惠价格(优惠券价格)

        $productArr = $this->getRequest()->getPost('peijian'); //配件抵扣
        $product_pice = $this->getRequest()->getPost('peijian_money', 0); //配件抵扣金额

        $token = $this->getRequest()->getPost('token', 1234);
        $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
        if (!empty($parent_record)) {
            $time = time();
            //添加订单接收数据
            $data['kid'] = $parent_record['kid'];
            $data['username'] = $parent_record['user_name'];
            $data['mobile'] = $mobile;
            if (!empty($sheng))
                $data['sheng'] = $sheng;
            if (!empty($shi))
                $data['shi'] = $shi;
            if (!empty($qu))
                $data['qu'] = $qu;
            if (!empty($cun))
                $data['cun'] = $cun;
            if (!empty($address))
                $data['address'] = $address;

            $data['shop_id'] = $shop_id;
            $data['money'] = $money ? $money : 0;
            $data['urgent'] = $urgent ? $urgent : 0; //是否紧急
            $data['goodtime'] = $goodtime ? $goodtime : $time; //希望配送时间
            $data['isold'] = $isold ? $isold : 0; //是否折旧
            $data['ismore'] = $ismore ? $ismore : 0; //是否余气
            $data['shipper_id'] = $shipper_id;
            $data['shipper_name'] = $shipper_name;
            $data['shipper_mobile'] = $shipper_mobile;
            $data['status'] = 2;
            $data['ctime'] = $time;
            $data['order_tc_type'] = $tc_type;
            $data['youhui_data'] = $youhuijson;
            $data['youhui_money'] = $youhuimoney;
            $data['yhq_money'] = $discountmoney;
            $data['product_pice'] = $product_pice;
            $data['kehu_type'] = $user_type;

            if (!empty($dataInfo))
                $dataInfo = json_decode($dataInfo, true);

            //抵扣商品
            $pjArr = array();
            if (!empty($productArr)) {
                $pArr = json_decode($productArr, TRUE);
                foreach ($pArr as $dVal) {
                    //存储配件
                    if (isset($dVal['is_zhekou']) && !empty($dVal['is_zhekou'])) {
                        $pdArr['shop_id'] = $shop_id;
                        $pdArr['order_sn'] = '';
                        $pdArr['goods_id'] = $dVal['good_id'];
                        $pdArr['goods_name'] = $dVal['good_name'];
                        $pdArr['goods_price'] = $dVal['good_price'];
                        $pdArr['goods_num'] = $dVal['good_num'];
                        $pdArr['goods_type'] = 2;
                        $pdArr['goods_kind'] = $dVal['good_kind'];
                        $pdArr['pay_money'] = isset($dVal['zk_money']) ? $dVal['zk_money'] : $dVal['good_price'];
                        $pdArr['good_zhekou'] = $dVal['is_zhekou'];
                        $pdArr['goods_premium'] = $pdArr['pay_money'];
                        $pjArr[] = $pdArr;
                    }
                }
            }

            $dataTrans = new DatatransferModel();
            $returnData = $dataTrans->kehuOrder($data, $dataInfo, $pjArr);
            if ($returnData['status'] == 200) {
                if (!empty($youhuijson)) {
                    $yhData = json_decode($youhuijson, TRUE);
                    if (!empty($yhData)) {
                        $uData['status'] = 1;
                        $uWhere['id'] = array('in', $yhData);
                        LibF::M('promotions_user')->where($uWhere)->save($uData);
                    }
                }

                $rData = array(
                    'ordersn' => $returnData['msg'],
                    'kid' => $data['kid']
                );
                $this->app->respond(1, $rData);
            } else {
                $this->app->respond(-3, '创建订单失败');
            }
        } else {
            $this->app->respond(-2, '当前用户不存在');
        }
    }

    //2.1.3 app提交安全报告
    public function safereportAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //送气工id 
            $shipper_name = $this->getRequest()->getPost('shipper_name'); //送气工姓名
            $kid = $this->getRequest()->getPost('kid'); //客户id 
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($kid)) {
                $data['shipper_id'] = $shipper_id;
                $data['kid'] = $kid;
                if (!empty($shipper_name))
                    $data['shipper_name'] = $shipper_name;

                define('ROOT_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/webroot_www/statics/upload/safe/' . date('Y-m-d') . '/');
                $order_sn = $this->getRequest()->getPost('order_sn'); //订单号
                $num = $this->getRequest()->getPost('num'); //照片数

                $time = time();
                $reportdetail = $this->getRequest()->getPost('selfjson'); //安检详情 
                $imageArr = array();
                if ($num > 0) {
                    for ($i = 1; $i <= $num; $i++) {
                        if (!empty($_FILES['file' . $i]['name'])) {
                            $ext = pathinfo($_FILES['file' . $i]['name']);
                            $ext = strtolower($ext['extension']);
                            $tempFile = $_FILES['file' . $i]['tmp_name'];
                            $targetPath = ROOT_PATH;
                            if (!is_dir($targetPath)) {
                                mkdir($targetPath, 0777, true);
                            }
                            $photo_name = $order_sn . $time . $i;

                            $new_file_name = $photo_name . '.' . $ext;
                            $targetFile = $targetPath . $new_file_name;
                            move_uploaded_file($tempFile, $targetFile);

                            $imageArr[] = date('Y-m-d') . '/' . $new_file_name;
                        }
                    }
                }
                if (!empty($imageArr) && !empty($order_sn)) {
                    $ilist = json_encode($imageArr);
                    LibF::M('order')->where(array('order_sn' => $order_sn))->save(array('safe_image' => $ilist));

                    $data['order_sn'] = $order_sn;
                    $data['imagedetail'] = $ilist;
                }

                $data['reportdetail'] = $reportdetail;
                $data['ctime'] = $time;
                $app = new App();
                $orderSn = $app->build_order_no();
                $data['securitysn'] = 'aj' . $orderSn;
                $status = LibF::M('security_report_user')->add($data);
                if ($status) {
                    LibF::M('kehu')->where(array('kid' => $kid))->save(array('safe_time' => $time, 'safe_detial' => $reportdetail,'safe_user' => $shipper_name));
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-4, '创建失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录' . $shipper_id);
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //2.2 app订单详情
    public function orderinfoAction() {
        if ($this->getRequest()->isPost()) {
            $ordersn = $this->getRequest()->getPost('ordersn');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($ordersn) && !empty($token)) {
                $data = array();
                $orderModel = new OrderModel();
                $orderData = $orderModel->getOrderInfo(array('order_sn' => $ordersn));
                if (!empty($orderData)) {
                    $kehuData = LibF::M('kehu')->where(array('kid' => $orderData['kid']))->find();
                    
                    $zpbottle = !empty($orderData['zbottle']) ? json_decode($orderData['zbottle'], TRUE) : array();
                    $kpbottle = !empty($orderData['kbottle']) ? json_decode($orderData['kbottle'], true) : array();
                    
                    //备注 当前最新的订单信息
                    $orderInfoData = $orderModel->getOrderList(array('order_sn' => $orderData['order_sn']));
                    $type = array();
                    if (!empty($orderInfoData)) {
                        $bottleTypeModel = new BottletypeModel();
                        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                        //获取配件规格
                        $productTypeModel = new ProducttypeModel();
                        $productTypeData = $productTypeModel->getProductTypeArray();
                        foreach ($orderInfoData as $value) {
                            $oInfo['goods_id'] = $value['goods_id'];
                            $oInfo['title'] = $value['goods_name'];
                            $oInfo['num'] = $value['goods_num'];
                            $oInfo['money'] = ($value['goods_premium'] > 0) ? $value['goods_premium'] : $value['goods_price'];
                            $oInfo['type'] = $value['goods_type'];
                            $oInfo['kind'] = $value['goods_kind'];
                            $oInfo['kind_name'] = isset($bottleTypeData[$value['goods_kind']]) ? $bottleTypeData[$value['goods_kind']]['bottle_name'] : '';
                            if ($value['goods_type'] == 1) {
                                $oInfo['kind_name'] = $bottleTypeData[$value['goods_kind']]['bottle_name'];
                            } else {
                                $oInfo['kind_name'] = $productTypeData[$value['goods_kind']]['name'];
                            }
                            $type[] = $oInfo;
                        }
                    }
                    $data = array(
                        'ordersn' => $orderData['order_sn'],
                        'status' => $orderData['status'],
                        'delivery' => date('Y-m-d', $orderData['good_time']),
                        'address' => $orderData['address'],
                        'mobile' => $orderData['mobile'],
                        'username' => $orderData['username'],
                        'type' => $type,
                        'ktype' => $orderData['kehu_type'],
                        'total' => $orderData['money'],
                        'yunfei' => 0,
                        'depreciation' => $orderData['is_old'], //是否有折旧 1有 0无
                        'workersname' => $orderData['shipper_name'],
                        'workersmobile' => !empty($orderData['shipper_mobile']) ? $orderData['shipper_mobile'] : '',
                        'is_settlement' => $orderData['is_settlement'],
                        'settlement_money' => $orderData['settlement_money'],
                        'settlement_deposit' => $orderData['settlement_deposit'],
                        'is_safe' => $orderData['is_safe'],
                        'time' => ($value['status'] > 3) ? date('Y-m-d', $orderData['should_time']) : $orderData['ctime'],
                        'yh_money' => $orderData['is_yh_money'], //余额券价格
                        'yhq_money' => $orderData['discountmoney'], //优惠券价格
                        'shipper_money' => $orderData['shipper_money'],
                        'comment'   => $orderData['comment'],
                        'zpbottle' => $zpbottle,
                        'kpbottle' => $kpbottle,
                        'balance'  => $kehuData['balance']
                    );
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-2, '当前订单不存在');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //2.3 app订单确认收款
    public function confirmorderAction() {
        if ($this->getRequest()->isPost()) {
            $time = time();
            $date = date('Y-m-d');

            $where['order_sn'] = $this->getRequest()->getPost('ordersn');
            $kid = $this->getRequest()->getPost('kid');
            $param['pay_money'] = $this->getRequest()->getPost('pay_money');  //实收款
            
            $yj_money = $this->getRequest()->getPost('deposit', 0);  //押金额
            $yjdata = $this->getRequest()->getPost('yjdata',''); //押金data
            $param['deposit'] = $yj_money; //押金实际收款
            
            $qkMoney = array();
            $is_pay = $this->getRequest()->getPost('is_pay', 0); //0不欠款 1欠款订单
            if ($is_pay == 1) {
                $deposit_contractno = $this->getRequest()->getPost('deposit_contractno', '');  //合同编号
                $qkMoney['deposit_contractno'] = $deposit_contractno;
                $deposit_money = $this->getRequest()->getPost('deposit_money', 0);  //押金欠款
                $qkMoney['deposit_money'] = $deposit_money;
                $yj_money -= $deposit_money;

                $bottle_contractno = $this->getRequest()->getPost('bottle_contractno', '');  //合同编号
                $bottle_money = $this->getRequest()->getPost('bottle_money', 0); //气价格欠款
                $qkMoney['bottle_money'] = $bottle_money;
                $qkMoney['bottle_contractno'] = $bottle_contractno;
                //$product_contractno = $this->getRequest()->getPost('product_contractno');  //合同编号
                //$product_money = $this->getRequest()->getPost('product_money', 0); //配件欠款
                //$qkMoney['product_money'] = $product_money;
                //$qkMoney['product_contractno'] = $product_contractno;
                $param['is_settlement'] = 2;
                if ($bottle_money) {
                    $param['settlement_money'] = $bottle_money;
                    //$param['settlement_money'] += $product_money;
                }
                if ($deposit_money) {
                    $param['settlement_deposit'] = $deposit_money;
                }
                $param['is_settlement_money'] = $deposit_money + $bottle_money;
                //$param['is_settlement_money'] += $product_money;
            }

            $is_cash = $this->getRequest()->getPost('is_cash', 0); //0现金支付  1网上支付 11微信 12支付宝  2客户余额支付
            $is_cash_money = $this->getRequest()->getPost('is_cash_money', 0); //客户账户余额
            if ($is_cash > 0) {
                $param['order_paytype'] = $is_cash;
            }
            $param['ispayment'] = 1;
            $param['status'] = 4;   //订单已结算
            $param['should_time'] = $time;

            $comment = $this->getRequest()->getPost('comment'); //订单备注
            if (!empty($comment)) {
                $param['comment'] = $comment;
            }

            $shipper_money = $this->getRequest()->getPost('shipper_money', 0);  //送气工优惠
            $param['shipper_money'] = $shipper_money;

            $param['raffinat'] = $this->getRequest()->getPost('raffinat', 0); //残液金额
            $param['raffinat_weight'] = $this->getRequest()->getPost('raffinat_weight', 0); //残液重量

            $zjdata = $this->getRequest()->getPost('zheijiu_ping'); //折旧
            $param['depreciation'] = $this->getRequest()->getPost('depreciation', 0); //折旧金额

            $data = $this->getRequest()->getPost('data'); //重瓶
            $nodata = $this->getRequest()->getPost('nodata',''); //空瓶(空瓶串)
            
            $param['is_more'] = $this->getRequest()->getPost('is_more',0); //是否余气
            $nodataJson = $this->getRequest()->getPost('nodatajson'); //空瓶详情

            $orderInfoJson = $this->getRequest()->getPost('order_info','');  //商品详情
            
            $productArr = $this->getRequest()->getPost('peijian'); //配件
            $bottleData = $this->getRequest()->getPost('bottle_data'); //余额data串
            if (!empty($bottleData)) {
                $param['bottle_data'] = $bottleData;
            }

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($kid) && !empty($token) && !empty($where['order_sn'])) {
                $where['status'] = 2;
                $orderData = LibF::M('order')->where($where)->find();
                if (!empty($orderData)) {
                    if($param['is_more'] == 1){  //有余气
                        $nobottle = !empty($nodataJson) ? json_decode($nodataJson,true): '';
                        if(!empty($nobottle)){
                            $param['residual_gas'] = 0;
                            $param['residual_gas_weight'] = 0;
                            foreach($nobottle as $nval){
                                $param['residual_gas'] += $nval['price'];
                                $param['residual_gas_weight'] += $nval['weight'];
                            }
                        }
                        $param['bottle_ndata'] = $nodataJson;
                    }
                    
                    if (!empty($data)) {
                        $param['zbottle'] = $data; //扫码钢瓶重瓶
                        $pData['zp'] = json_decode($data, true); //重瓶
                    }
                    if (!empty($nodata)) {
                        $param['kbottle'] = $nodata; ////扫码钢瓶空瓶
                        $pData['kp'] = json_decode($nodata, true); //空瓶
                    }
                    if (!empty($zjdata)) {
                        $pData['zj'] = json_decode($zjdata, true);  //折旧
                    }
                    if (!empty($yjdata)) {
                        $param['bottle_deposit'] = $yjdata; //押金
                    }
                    $status = LibF::M('order')->where($where)->save($param);
                    if ($status) {
                        if ($is_cash == 2) {
                            //用户余额
                            if ($param['pay_money'] > 0) {
                                LibF::M('kehu')->where(array('kid' => $orderData['kid']))->setDec('balance', $param['pay_money']); //zt：客户余额减少
                                $b['balance_sn'] = $orderData['order_sn'];
                                $b['kid'] = $orderData['kid'];
                                $b['time'] = $time;
                                $b['money'] = $param['pay_money'];
                                $b['balance'] = $is_cash_money - $param['pay_money'];
                                $b['type'] = 2;
                                $b['paytype'] = 1;
                                $b['shipper_id'] = $orderData['shipper_id'];
                                LibF::M('balance_list')->add($b);

                                if (!empty($orderData['mobile'])) {
                                    //发送充值成功短信
                                    //$smsdataModel = new SmsDataModel();
                                    //$smsdataModel->sendsms($b['balance'], $b['money'], $orderData['mobile'], 2);
                                }
                            }
                        }
                        if (($is_pay == 1) && !empty($qkMoney)) {
                            $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                            $this->userqk($kid, $qkMoney, $where['order_sn'], $kehuData); //生成欠款订单
                        }
                        if ($yj_money > 0) {
                            $this->createDeposit($where['order_sn'], $kid, $orderData['shop_id'], $yj_money, $yjdata); //押金收入
                        }

                        if (!empty($orderInfoJson)) {
                            $this->shipperOrderInfo($orderData['order_sn'], $orderInfoJson);  //更新商品价格
                        }

                        $orderzjData = array();
                        //array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                        if (!empty($pData['zj'])) {
                            //送气工折旧瓶生成订单
                            $orderzjData['order_sn'] = $where['order_sn'];
                            $orderzjData['kid'] = $kid;
                            $orderzjData['mobile'] = $orderData['mobile'];
                            if (!empty($orderData['sheng']))
                                $orderzjData['sheng'] = $orderData['sheng'];
                            if (!empty($orderData['shi']))
                                $orderzjData['shi'] = $orderData['shi'];
                            if (!empty($orderData['qu']))
                                $orderzjData['qu'] = $orderData['qu'];
                            if (!empty($orderData['cun']))
                                $orderzjData['cun'] = $orderData['cun'];
                            if (!empty($orderData['address']))
                                $orderzjData['address'] = $orderData['address'];

                            $orderzjData['bottle_data'] = $zjdata;
                            $orderzjData['shipper_id'] = $orderData['shipper_id'];
                            $orderzjData['shipper_mobile'] = $orderData['shipper_mobile'];
                            $orderzjData['shipper_name'] = $orderData['shipper_name'];
                            $orderzjData['status'] = 1;
                            $orderzjData['time_created'] = $time;
                            if ($param['raffinat'] > 0 || $param['depreciation'] > 0)
                                $orderzjData['money'] = $param['raffinat'] + $param['depreciation'];
                            if ($param['raffinat_weight'] > 0)
                                $orderzjData['weight'] = $param['raffinat_weight'];
                            if (!empty($comment))
                                $orderzjData['comment'] = $comment;
                        }

                        $rData = $this->shipperIncome($orderData['shipper_id'], $orderData['shop_id'], $kid, $pData, $orderzjData, $orderData['order_sn']);  //送气工库存变更
                        //有配件更新库存配件数
                        if (!empty($productArr)) {
                            $pArr = json_decode($productArr, TRUE);

                            $app = new App();
                            $orderSn = $app->build_order_no();
                            $pjkc = 'pjkc' . $orderSn;
                            $stockmethodModel = new StockmethodModel();

                            $pjArr = array();
                            foreach ($pArr as $dVal) {

                                $pjparam['type'] = 2;
                                $pjparam['documentsn'] = $pjkc;
                                $pjparam['goods_propety'] = $dVal['good_kind'];  //配件规格
                                $pjparam['goods_type'] = $dVal['good_id']; //配件id
                                $pjparam['goods_name'] = $dVal['good_name'];
                                //$param['goods_price'] = $data['goods_price'];
                                $pjparam['goods_num'] = $dVal['good_num'];
                                $pjparam['shop_id'] = $orderData['shop_id'];
                                $pjparam['shipper_id'] = $orderData['shipper_id'];
                                $pjparam['gtype'] = 2;
                                //$pjparam['admin_user'] = '';
                                $pjparam['time'] = $date;
                                $pjparam['ctime'] = $time;

                                $status = $stockmethodModel->ShipperstationsStock($pjparam, $orderData['shipper_id'], $orderData['shop_id'], 0, 2);
                            }
                        }

                        //更新当前用户余额
                        if (!empty($bottleData)) {
                            //优惠套餐才会有
                            $kehuData['bottle_data'] = $bottleData;
                            LibF::M('kehu')->where(array('kid' => $kid))->save($kehuData);

                            //转存成优惠券
                            $yhData = json_decode($bottleData, TRUE);
                            if (!empty($yhData)) {
                                $yhq_info = $insert_yhq = array();
                                $yhqsn = date("YmdHis");
                                foreach ($yhData as $yhkey => $yhVal) {
                                    $typeName = isset($rData['bottleTypeData']) ? $rData['bottleTypeData'][$yhVal['good_kind']]['bottle_name'] : '';
                                    if ($yhVal['good_num'] > 0) {
                                        for ($i = 1; $i <= $yhVal['good_num']; $i++) {
                                            $yhq_info['kid'] = $kid;
                                            $yhq_info['promotionsn'] = $yhqsn . $yhkey . $kid;
                                            $yhq_info['title'] = $yhVal['good_name'] . $typeName;
                                            $yhq_info['type'] = 2; //余额券
                                            $yhq_info['good_type'] = $yhVal['good_kind'];
                                            $yhq_info['price'] = $yhVal['good_price'];
                                            $yhq_info['money'] = $yhVal['good_price'];
                                            $yhq_info['num'] = 1;
                                            $yhq_info['time_created'] = $time;
                                            $insert_yhq[] = $yhq_info;
                                        }
                                    }
                                }
                                LibF::M('promotions_user')->uinsertAll($insert_yhq);
                            }
                        }

                        //送气工收入
                        if ($param['pay_money'] > 0) {
                            $param['date'] = $date;
                            $this->shipperPayListAdd($orderData['shipper_id'], $orderData['shipper_name'], $orderData['shipper_mobile'], $orderData['shop_id'], $orderData['order_sn'], $is_cash, $param);
                        }

                        $this->app->respond(1, '结算成功');
                    } else {
                        $this->app->respond(-3, '结算失败');
                    }
                } else {
                    $this->app->respond(-3, '当前订单不存在');
                }
            } else {
                $this->app->respond(-3, '当前提交参数缺失');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //2.4 app订单修改问题订单
    public function editorderAction() {
        if ($this->getRequest()->isPost()) {
            $where['order_sn'] = $this->getRequest()->getPost('ordersn');
            $param['username'] = $this->getRequest()->getPost('username');
            $param['mobile'] = $this->getRequest()->getPost('mobile');
            $param['sheng'] = $this->getRequest()->getPost('sheng');
            $param['shi'] = $this->getRequest()->getPost('shi');
            $param['qu'] = $this->getRequest()->getPost('qu');
            $param['cun'] = $this->getRequest()->getPost('cun');
            $param['address'] = $this->getRequest()->getPost('address');
            $param['status'] = $this->getRequest()->getPost('status');
            $param['comment'] = $this->getRequest()->getPost('comment');
            $param['card_sn'] = $this->getRequest()->getPost('card_sn');
            $param['kid'] = $this->getRequest()->getPost('kid');
            if (!empty($where['order_sn'])) {
                $uData = array();
                if (isset($param['mobile']) && !empty($param['mobile']))
                    $uData['mobile'] = $param['mobile'];
                if (isset($param['username']) && !empty($param['username']))
                    $uData['username'] = $param['username'];
                if (isset($param['sheng']) && !empty($param['sheng']))
                    $uData['sheng'] = $param['sheng'];
                if (isset($param['shi']) && !empty($param['shi']))
                    $uData['shi'] = $param['shi'];
                if (isset($param['qu']) && !empty($param['qu']))
                    $uData['qu'] = $param['qu'];
                if (isset($param['cun']) && !empty($param['cun']))
                    $uData['cun'] = $param['cun'];
                if (isset($param['address']) && !empty($param['address']))
                    $uData['address'] = $param['address'];
                if (isset($param['status']) && !empty($param['status']))
                    $uData['status'] = $param['status'];
                if (isset($param['comment']) && !empty($param['comment']))
                    $uData['comment'] = $param['comment'];

                $where['status'] = array('neq', 4);
                $status = 0;
                if (!empty($uData))
                    $status = LibF::M('order')->where($where)->save($uData);

                if (!empty($param['kid']) && !empty($param['card_sn'])) {
                    $kwhere['kid'] = $param['kid'];
                    $kData['card_sn'] = $param['card_sn'];
                    LibF::M('kehu')->where($kwhere)->save($kData);
                }

                if ($status) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '创建订单失败');
                }
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //2.4.5 app修改用户基础信息
    public function editkehuAction() {
        if ($this->getRequest()->isPost()) {
            $param['username'] = $this->getRequest()->getPost('username'); //用户名称
            $param['mobile'] = $this->getRequest()->getPost('mobile'); //手机号
            $param['sheng'] = $this->getRequest()->getPost('sheng');
            $param['shi'] = $this->getRequest()->getPost('shi');
            $param['qu'] = $this->getRequest()->getPost('qu');
            $param['cun'] = $this->getRequest()->getPost('cun');
            $param['address'] = $this->getRequest()->getPost('address');
            $param['card_sn'] = $this->getRequest()->getPost('card_sn'); //用户卡号
            $param['kid'] = $this->getRequest()->getPost('kid');  //用户id 必填
            if (!empty($param['kid'])) {
                if (!empty($param['mobile'])) {
                    $mobileData = LibF::M('kehu')->where(array('mobile_phone' => $param['mobile'],'kid' =>array('neq',$param['kid'])))->find();
                    if (!empty($mobileData)) {
                        $this->app->respond(-2, '当前手机号已存在');
                        exit;
                    }
                }
                $kwhere['kid'] = $param['kid'];
                $kehuData = LibF::M('kehu')->where($kwhere)->find();

                $uData = array();
                if (isset($param['mobile']) && !empty($param['mobile'])) {
                    $uData['mobile_phone'] = $param['mobile'];
                    $uData['mobile_list'] = $kehuData['mobile_list'] . ',' . $param['mobile_phone'];
                }
                if (isset($param['username']) && !empty($param['username']))
                    $uData['user_name'] = $param['username'];
                if (isset($param['sheng']) && !empty($param['sheng']))
                    $uData['sheng'] = $param['sheng'];
                if (isset($param['shi']) && !empty($param['shi']))
                    $uData['shi'] = $param['shi'];
                if (isset($param['qu']) && !empty($param['qu']))
                    $uData['qu'] = $param['qu'];
                if (isset($param['cun']) && !empty($param['cun']))
                    $uData['cun'] = $param['cun'];
                if (isset($param['address']) && !empty($param['address']))
                    $uData['address'] = $param['address'];

                if (isset($param['card_sn']) && !empty($param['card_sn']))
                    $uData['card_sn'] = $param['card_sn'];

                $status = LibF::M('kehu')->where($kwhere)->save($uData);

                if ($status) {
                    $this->app->respond(1, '更新成功');
                } else {
                    $this->app->respond(-3, '更新失败');
                }
            }else{
                $this->app->respond(-2, '当前用户不存在');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //3.1 app送气工领瓶
    public function inshipperAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');  //门店
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //送气工
            $username = $this->getRequest()->getPost('username'); //送气工名称
            $type = $this->getRequest()->getPost('type_id', 1);

            $data = $this->getRequest()->getPost('data'); //数据串
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shipper_id) && !empty($shop_id) && !empty($data)) {
                $data = json_decode($data, true);

                //array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                $dataModel = new DataInventoryModel();
                $rData = $dataModel->shipperck($shop_id, $shipper_id, 0, $data, 1, 2);  //送气工重瓶入库
                if ($rData['status']) {
                    $kData['comment'] = '送气工领瓶';
                    $kData['admin_user'] = $username;
                    $dataModel->shopck($shop_id, $shipper_id, $rData['data'], $kData, 2, 2); //门店重瓶出库
                    //领瓶详情
                    $dModel = new DatatransferModel();
                    $dModel->shipperInventory($data, $shipper_id, 1, $rData['bottleData']);

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

    //3.2 app送气工领取配件
    public function inproductAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $product_no = $this->getRequest()->getPost('product_no');
            $id = $this->getRequest()->getPost('id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token) && !empty($shop_id) && !empty($shipper_id) && !empty($product_no)) {

                $where['id'] = $id;
                $where['shop_id'] = $shop_id;
                $where['shipper_id'] = $shipper_id;
                $where['documentsn'] = $product_no;
                $where['type'] = 2;
                $data = LibF::M('filling_stock_log_shop')->where($where)->find();
                if (!empty($data)) {
                    $stockmethodModel = new StockmethodModel();

                    $param['type'] = 1;
                    $param['documentsn'] = $data['documentsn'];
                    $param['goods_propety'] = $data['goods_propety'];  //配件规格
                    $param['goods_type'] = $data['goods_id']; //配件id
                    $param['goods_name'] = $data['goods_name'];
                    //$param['goods_price'] = $data['goods_price'];
                    $param['goods_num'] = $data['goods_num'];
                    $param['shop_id'] = $data['shop_id'];
                    $param['shipper_id'] = $data['shipper_id'];
                    $param['admin_user'] = $data['admin_user'];
                    $param['gtype'] = 2;
                    $param['time'] = $data['time'];
                    $param['ctime'] = time();
                    $status = $stockmethodModel->ShipperstationsStock($param, $shipper_id, $shop_id, 0, 2);
                    if ($status) {
                        LibF::M('filling_stock_log_shop')->where($where)->save(array('status' => 1));
                        $this->app->respond(1, '创建成功');
                    } else {
                        $this->app->respond(-1, '创建失败');
                    }
                } else {
                    $this->app->respond(-1, '当前单据不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //3.2 app送气工领配件列表
    public function shipperproductAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token) && !empty($shipper_id)) {
                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $where['shop_id'] = $shop_id;
                $where['shipper_id'] = $shipper_id;
                $where['gtype'] = 2;
                $where['type'] = 2;
                //$where['status'] = 0;

                $returnData = array();
                $pageStrar = ($page - 1) * $param['pagesize'];
                $data = LibF::M('filling_stock_log_shop')->where($where)->limit($pageStrar, $this->pageSize)->order('id desc')->select();
                if (!empty($data)) {
                    foreach ($data as $value) {
                        $val['id'] = $value['id'];
                        $val['product_no'] = $value['documentsn'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['shipper_id'] = $value['shipper_id'];
                        $val['status'] = $value['status'];
                        $val['time'] = $value['time'];

                        $val['good_name'] = $value['goods_name'];
                        $val['good_num'] = $value['goods_num'];
                        $val['good_kind'] = $value['goods_propety'];
                        $val['good_id'] = $value['goods_id'];
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

    //4.1 app送气工库存
    public function kucunlistAction() {

        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');

            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shipper_id) && !empty($token)) {
                //$type = array(1 => '全部', 2 => '空瓶', 3 => '重瓶');
                $bottleTypeModel = new BottletypeModel();
                $bottleType = $bottleTypeModel->getBottleTypeArray();

                $returnData = array('data' => array(), 'total' => 0);
                $dataModel = new DatatransferModel();

                $where['shipper_id'] = $shipper_id;
                $where['type'] = 1;
                $data = $dataModel->shipperKucun($shipper_id, $where);
                if (isset($data['data']) && !empty($data['data'])) {
                    $typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
                    foreach ($data['data'] as $key => $value) {
                        $typeName = $typeObject[$key];
                        foreach ($value as $k => $val) {
                            $v = array();
                            $v['typeid'] = $key;
                            $v['is_open'] = $k;
                            $v['title'] = isset($bottleType[$k]) ? $bottleType[$k]['bottle_name'] : '';
                            $v['typename'] = $typeName;
                            $v['num'] = $val;
                            $returnData['data'][] = $v;
                        }
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

    //4.2 app送气工钢瓶出库
    public function cklistAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $type = $this->getRequest()->getPost('type', 1); //1钢瓶2配件
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token) && !empty($shipper_id)) {

                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $where['shop_id'] = $shop_id;
                $where['shipper_id'] = $shipper_id;
                $where['type'] = 2;
                $where['gtype'] = $type;

                $returnData = array('bottle' => array(), 'product' => array());
                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('filling_stock_log_shipper')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    $typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
                    foreach ($data as &$value) {
                        $value['time'] = date('Y-m-d H:i', $value['ctime']);
                        if ($value['gtype'] == 1) {
                            $typeName = $typeObject[$value['goods_propety']];
                            $value['typename'] = $typeName;
                            $returnData['bottle'][] = $value;
                        } elseif ($value['gtype'] == 2) {
                            $value['typename'] = '配件';
                            $returnData['product'][] = $value;
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

    //4.2 app送气钢瓶入库
    public function rklistAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $type = $this->getRequest()->getPost('type', 1); //1钢瓶2配件
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token) && !empty($shipper_id) && !empty($shop_id)) {

                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $where['shop_id'] = $shop_id;
                $where['shipper_id'] = $shipper_id;
                $where['type'] = 1;
                $where['gtype'] = $type;

                $typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');

                $returnData = array('bottle' => array(), 'product' => array());
                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('filling_stock_log_shipper')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as $value) {
                        $value['time'] = date('Y-m-d H:i', $value['ctime']);
                        if ($value['gtype'] == 1) {
                            $value['typename'] = $typeObject[$value['goods_propety']];
                            $returnData['bottle'][] = $value;
                        } elseif ($value['gtype'] == 2) {
                            $value['typename'] = '配件';
                            $returnData['product'][] = $value;
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

    //4.3 app送气工配件库存
    public function kcproductAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($token)) {
                $returnData = array();
                $where['shipper_id'] = $shipper_id;
                $where['type'] = 2;
                $data = LibF::M('filling_stock_shipper')->where($where)->select();
                if (!empty($data)) {
                    foreach ($data as $value) {
                        $val['id'] = '';
                        $val['shipper_id'] = $shipper_id;
                        $val['goods_id'] = $value['goods_type'];
                        $val['goods_kind'] = $value['fs_type_id'];
                        $val['goods_num'] = $value['fs_num'];
                        $val['goods_type'] = $value['goods_type'];
                        $val['name'] = $value['fs_name'];
                        $val['type'] = $value['type'];
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

    //4.4 app送气工欠款用户
    public function nopaylistAction() {

        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($token)) {
                $username = $this->getRequest()->getPost('username');

                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $orderModel = new Model('order');
                $filed = " rq_order.kid,rq_order.kehu_type,rq_order.username,rq_kehu.money as total ";
                $leftJoin = " LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid ";
                $where = array('rq_order.is_settlement' => 2, 'rq_order.shipper_id' => $shipper_id, 'rq_order.status' => 4);
                if (!empty($username))
                    $where['rq_order.username'] = $username;

                $pageStart = ($page - 1) * $this->pageSize;
                $data = $orderModel->join($leftJoin)->field($filed)->where($where)->group('rq_order.kid')->order('total desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    $kType = array(1 => '居民用户', 2 => '商业用户' ,3 => '工业用户');
                    foreach ($data as &$value) {
                        $value['type_name'] = isset($kType[$value['kehu_type']]) ? $kType[$value['kehu_type']] : '';
                    }
                } else {
                    $data = array();
                }

                //$data = LibF::M('order')->field('kid,kehu_type,username,sum(money) as total')->where(array('is_settlement' => 2, 'shipper_id' => $shipper_id, 'status' => 4))->group('kid')->select();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //4.5 app客户欠款记录
    public function noorderlistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($kid) && !empty($token)) {

                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                //获取当前用户欠款金额
                $mdata = LibF::M('kehu')->field('money,kid,user_name')->where(array('kid' => $kid))->find();
                $returndata['username'] = $mdata['user_name'];
                $returndata['money'] = $mdata['money'];

                $pageStart = ($page - 1) * $this->pageSize;

                $data = LibF::M('order_arrears')->field('order_sn,money,contractno,arrears_type,time,status')->where(array('type' => 1, 'kid' => $kid))->order('id desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['time_list'] = date('Y-m-d', $value['time']);
                        $value['username'] = $returndata['username'];
                        $value['typename'] = ($value['arrears_type'] == 1) ? '商品欠款' : (($value['arrears_type'] == 2) ? '押金欠款' : '其它欠款');
                    }
                } else {
                    $data = array();
                }
                $returndata['data'] = $data;
                $this->app->respond(1, $returndata);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //4.5.1 app客户欠款记录（收缴）
    public function userqkorderAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($kid) && !empty($token)) {

                $data = LibF::M('order_arrears')->field('order_sn,money,contractno,arrears_type,time,status')->where(array('type' => 1, 'kid' => $kid, 'is_return' => 0))->order('id desc')->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['time_list'] = date('Y-m-d', $value['time']);
                        $value['username'] = $returndata['username'];
                        $value['typename'] = ($value['arrears_type'] == 1) ? '商品欠款' : (($value['arrears_type'] == 2) ? '押金欠款' : '其它欠款');
                    }
                } else {
                    $data = array();
                }
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //4.6 还款记录
    public function repaymentlistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {
                    $where['type'] = 2;
                    $where['kid'] = $kid;

                    $pageStart = ($page - 1) * $this->pageSize;
                    $arrearsData = LibF::M('order_arrears')->field('order_sn,time,paytype,type,money,balance')->where($where)->order('time desc')->limit($pageStart, $this->pageSize)->select();
                    if (!empty($arrearsData)) {
                        foreach ($arrearsData as &$value) {
                            $value['time_list'] = date('Y-m-d', $value['time']);
                        }
                    } else {
                        $arrearsData = array();
                    }
                    $this->app->respond(1, $arrearsData);
                } else {
                    $this->app->respond(-3, '当前用户不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //4.7 收缴欠款
    public function addrepaymentAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //送气工
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');
            $token = $this->getRequest()->getPost('token', 1234);
            
            $type = $this->getRequest()->getPost('type', 1); //1 余额还款  2 支付宝 3 微信 4 现金
            $money = $this->getRequest()->getPost('money', 0);
            $time = time();

            $contractno = $this->getRequest()->getPost('contractno'); //合同编号
            if (!empty($kid) && !empty($shipper_id) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {

                    $setArr = array();
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $order_sn = 'hk' . $orderSn;

                    $param['order_sn'] = $order_sn;
                    $param['kid'] = $kehuData['kid'];
                    $param['time'] = $time;
                    $param['money'] = $money;
                    $param['type'] = 2;
                    $param['paytype'] = $type;
                    $param['balance'] = $setArr['money'] = $kehuData['money'] - $param['money'];
                    $param['is_return'] = 1;
                    $param['shipper_id'] = $shipper_id;

                    $status = LibF::M('order_arrears')->add($param);
                    if ($status) {
                        //money 欠款  balance 余额
                        if ($type == 1) {
                            $b['kid'] = $kehuData['kid'];
                            $b['time'] = time();
                            $b['money'] = $param['money'];
                            $b['balance'] = $setArr['balance'] = $kehuData['balance'] - $param['money'];
                            $b['type'] = 2;
                            $b['paytype'] = $type;
                            $status = LibF::M('balance_list')->add($b);
                        }
                        if (!empty($contractno)) {
                            $dataJson = json_decode($contractno, TRUE);
                            if (!empty($dataJson)) {
                                $htArr = array();
                                foreach ($dataJson as $dVal) {
                                    $htArr[] = $dVal['contractno'];
                                    //如果有押金产生押金
                                    if ($dVal['arrears_type'] == 2) {
                                        $this->addDeposit($kehuData['kid'], $dVal['money'], '', $dVal['contractno'], $kehuData['shop_id']);
                                    }
                                }
                                $uWhere['type'] = 1;
                                $uWhere['contractno'] = array('in', $htArr);
                                LibF::M('order_arrears')->where($uWhere)->save(array('is_return' => 1));
                            }
                        }

                        LibF::M('kehu')->where(array('kid' => $kehuData['kid']))->save($setArr);
                        if ($param['money'] > 0) {
                            LibF::M('shipper')->where(array('shipper_id' => $shipper_id))->setInc('money', $param['money']); //zt：收款后送气工余额增加	
                            
                            $payInsert['paylist_no'] = $param['order_sn'];
                            $payInsert['shipper_id'] = $shipper_id;
                            $payInsert['shipper_name'] = $shipper_name;
                            $payInsert['mobile_phone'] = $shipper_mobile;
                            $payInsert['money'] = $param['money'];
                            $payInsert['shop_id'] = $kehuData['shop_id'];
                            $payInsert['time'] = date('Y-m-d', $param['time']);
                            $payInsert['time_created'] = $param['time'];
                            $payInsert['type'] = 2;
                            $payInsert['is_statistics'] = 1;
                            $payInsert['status'] = 1;
                            LibF::M('shipper_paylist')->add($payInsert);
                        }

                        $this->app->respond(1, '还款成功');
                    } else {
                        $this->app->respond(-3, '还款失败');
                    }
                } else {
                    $this->app->respond(-3, '当前用户不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.1 送气工财务订单收入
    public function financialincomeAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $page = $this->getRequest()->getPost('page', 1);
            $token = $this->getRequest()->getPost('token', 1234);

            $typeObject = array(1 => '订单收入', 2 => '收缴欠款', 4 => '押金收入' , 9 => '上门费',10 => '替用户充值');
            //$date = date('Y-m-d');
            //$start_time = $this->getRequest()->getPost('time_start', strtotime($date));
            //$end_time = $this->getRequest()->getPost('end_time', strtotime($date . ' 23:59:59'));
            $start_time = $end_time = 0;
            if (!empty($shipper_id) && !empty($token)) {

                $where['shipper_id'] = $shipper_id;
                $shipperArr = LibF::M('shipper')->where($where)->find();
                
                $where['type'] = array('in', array(1, 2, 4, 9,10));
                $where['is_statistics'] = 1;

                $pageStrar = ($page - 1) * $this->pageSize;
                $data = LibF::M('shipper_paylist')->where($where)->order('time_created desc')->limit($pageStrar, $this->pageSize)->select();
                $totalData = array();
                if(!empty($data)){
                    foreach($data as &$value){
                        $val['paylist_no'] = $value['paylist_no'];
                        $val['shipper_id'] = $value['shipper_id'];
                        $val['money'] = $value['money'];
                        $val['type'] = $value['type'];
                        $val['type_name'] = $typeObject[$value['type']];
                        $val['time'] = date('m-d H:i',$value['time_created']);
                        $totalData[] = $val;
                    }
                }
                
                $returnData['stotal'] = $returnData['ztotal'] = 0;
                $tWhere['shipper_id'] = $shipper_id;
                $tWhere['is_statistics'] = 1;
                $dataTotal = LibF::M('shipper_paylist')->field('status,sum(money) as total')->where($tWhere)->group('status')->select();
                if (!empty($dataTotal)) {
                    foreach ($dataTotal as $value) {
                        if ($value['status'] == 1) {
                            $returnData['stotal'] = $value['total'];
                        } else if ($value['status'] == 2) {
                            $returnData['ztotal'] = $value['total'];
                        }
                    }
                }
                $returnData['ytotal'] = $shipperArr['money'];
                //$returnData['ytotal'] = $returnData['stotal'] - $returnData['ztotal'];
                $returnData['datalist'] = $totalData;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //测试接口暂时不适用*********
    public function orderpaymentAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            //$date = date('Y-m-d');
            //$start_time = $this->getRequest()->getPost('time_start', strtotime($date));
            //$end_time = $this->getRequest()->getPost('end_time', strtotime($date . ' 23:59:59'));
            $start_time = $end_time = 0;

            if (!empty($shipper_id) && !empty($token)) {
                $arrearsWhere['shipper_id'] = $shipper_id;
                $arrearsWhere['type'] = 2;
                $arrearsWhere['paytype'] = 4;
                //$arrearsWhere['time'] = array(array('egt', $start_time), array('elt', $end_time), 'AND');
                $qtotalData = LibF::M('order_arrears')->field("sum(money) as total")->where($arrearsWhere)->find(); //收缴欠款金额
                $orderWhere['shipper_id'] = $shipper_id;
                $orderWhere['order_paytype'] = 0;
                $orderWhere['ispayment'] = 1;
                //$orderWhere['is_settlement'] = 1;
                $orderWhere['status'] = 4;
                //$orderWhere['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'AND');
                $totalData = LibF::M('order')->field("sum(pay_money) as total")->where($orderWhere)->find();  //订单收费
                $paymentWhere['shipper_id'] = $shipper_id;
                $paymentWhere['time_created'] = array(array('egt', $start_time), array('elt', $end_time), 'AND');
                $ztotalData = LibF::M('shipper_payment')->field("sum(money) as total")->where($paymentWhere)->find(); //送气工上缴
                $depositWhere['shipper_id'] = $shipper_id;
                //$depositWhere['time_created'] = array(array('egt', $start_time), array('elt', $end_time), 'AND');
                $dtotalData = LibF::M('deposit_list')->field("sum(money) as total")->where($depositWhere)->find(); //押金支出

                $returnData['stotal'] = $totalData['total'] + $qtotalData['total']; //总收入额（订单+收缴欠款）
                $returnData['ztotal'] = $dtotalData['total'] + $ztotalData['total']; //支出总额（上缴 + 退押金）
                $returnData['ytotal'] = $returnData['stotal'] - $returnData['ztotal'];  //余额
                $orderlist = LibF::M('order')->field('order_sn,pay_money,ctime')->where(array('shipper_id' => $shipper_id, 'order_paytype' => 0, 'ispayment' => 1, 'status' => 4))->order('ctime desc')->select();  //收入列表
                if (!empty($orderlist)) {
                    foreach ($orderlist as &$value) {
                        $value['ctime'] = ($value['ctime'] > 0) ? date('Y-m-d', $value['ctime']) : '';
                    }
                } else {
                    $orderlist = array();
                }
                $returnData['data'] = $orderlist;

                $where['rq_order_arrears.shipper_id'] = $shipper_id;
                $where['rq_order_arrears.type'] = 2;
                $where['rq_order_arrears.paytype'] = 1;

                $arrearsModel = new Model('order_arrears');
                $arrearsData = $arrearsModel->join('LEFT JOIN rq_kehu ON rq_order_arrears.kid = rq_kehu.kid')->field('rq_order_arrears.money,rq_kehu.user_name,rq_order_arrears.time')->where($where)->order('rq_order_arrears.time desc')->select();
                $arrearsData = !empty($arrearsData) ? $arrearsData : array();
                $returnData['qdata'] = $arrearsData;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.2 送气工财务订单支出
    public function financialexpensesAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $page = $this->getRequest()->getPost('page', 1);
            $token = $this->getRequest()->getPost('token', 1234);

            $typeObject = array(3 => '退瓶支出', 5 => '折旧支出', 6 => '折旧支出', 7 => '余气支出', 8 => '押金支出');
            //$date = date('Y-m-d');
            //$start_time = $this->getRequest()->getPost('time_start', strtotime($date));
            //$end_time = $this->getRequest()->getPost('end_time', strtotime($date . ' 23:59:59'));
            $start_time = $end_time = 0;
            if (!empty($shipper_id) && !empty($token)) {

                $where['shipper_id'] = $shipper_id;
                $shipperArr = LibF::M('shipper')->where($where)->find();

                $where['type'] = array('in', array(3, 5, 6, 7, 8));
                $where['is_statistics'] = 1;

                $pageStrar = ($page - 1) * $this->pageSize;
                $data = LibF::M('shipper_paylist')->where($where)->order('time_created desc')->limit($pageStrar, $this->pageSize)->select();
                $totalData = array();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $val['paylist_no'] = $value['paylist_no'];
                        $val['shipper_id'] = $value['shipper_id'];
                        $val['money'] = $value['money'];
                        $val['type'] = $value['type'];
                        $val['type_name'] = $typeObject[$value['type']];
                        $val['time'] = date('m-d H:i', $value['time_created']);
                        $totalData[] = $val;
                    }
                }
                $returnData['datalist'] = $totalData;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.2.2 送气工上缴支出记录
    public function shipperpaymentAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shipper_id) && !empty($token)) {
                $where['shipper_id'] = $shipper_id;
                $shipperArr = LibF::M('shipper')->where($where)->find();

                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('shipper_payment')->where(array('shipper_id' => $shipper_id))->order('time_created desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    $shopObject = ShopModel::getShopArray();
                    foreach ($data as &$value) {
                        $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                        $value['time'] = ($value['time_created'] > 0) ? date('Y-m-d', $value['time_created']) : 0;
                    }
                }

                $returnData['ytotal'] = $shipperArr['money'];
                $returnData['datalist'] = $data;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.3 送气工还款
    public function shipperforpayAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $shop_id = $this->getRequest()->getPost('shop_id');
            $money = $this->getRequest()->getPost('money');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shipper_id) && !empty($token)) {
                $param['shipper_id'] = $shipper_id;
                $param['shop_id'] = $shop_id;
                $param['money'] = $money;
                $param['time_created'] = time();

                $status = LibF::M('shipper_payment')->add($param);
                if ($status) {
                    $this->app->respond(1, '更新成功');
                } else {
                    $this->app->respond(-4, '更新失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.4 送气工收缴欠款记录
    public function shipperarrearsAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shipper_id) && !empty($token)) {
                $page = $this->getRequest()->getPost('page', 1);
                $param['page'] = $page;
                $param['pagesize'] = $this->pageSize;

                $file = " rq_order_arrears.*,rq_kehu.user_name ";
                $oModel = new Model('order_arrears');
                $where['rq_order_arrears.shipper_id'] = $shipper_id;
                //$where['rq_order_arrears.type'] = 1;

                $pageStart = ($page - 1) * $this->pageSize;
                $returnData = $oModel->join('LEFT JOIN rq_kehu ON rq_order_arrears.kid = rq_kehu.kid')->field($file)->where($where)->order('rq_order_arrears.time desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($returnData)) {
                    foreach ($returnData as &$value) {
                        $value['time'] = date('Y-m-d', $value['time']);
                    }
                } else {
                    $returnData = array();
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.5 送气工退瓶支出接口
    public function depositorderAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            $rData = array();
            if (!empty($shipper_id) && !empty($token)) {
                $returnData = LibF::M('deposit_list')->where(array('shipper_id' => $shipper_id, 'status' => 2))->order('id desc')->select();
                if (!empty($returnData)) {
                    foreach ($returnData as &$value) {
                        $val['depositsn'] = $value['depositsn'];
                        $val['money'] = $value['money'];
                        $val['time'] = date('Y-m-d', $value['ctime']);
                        $rData[] = $val;
                    }
                } else {
                    $returnData = array();
                }

                $this->app->respond(1, $rData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app送气工未付款订单详情
     * 
     * @param $mobile
     * @param $token
     * @param $order
     */
    public function noorderinfoAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $orderID = $this->getRequest()->getPost('orderid');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {
                if ($orderID) {
                    $data = array();
                    $orderModel = new OrderModel();
                    $orderData = $orderModel->getOrderInfo(array('order_sn' => $orderID));
                    if (!empty($orderData)) {
                        //备注 当前最新的订单信息
                        $orderInfoData = $orderModel->getOrderList(array('order_sn' => $orderData['order_sn']));
                        $type = array();
                        if (!empty($orderInfoData)) {
                            foreach ($orderInfoData as $value) {
                                $oInfo['title'] = $value['goods_name'];
                                $oInfo['num'] = $value['goods_num'];
                                $oInfo['money'] = $value['pay_money'];
                                $type[] = $oInfo;
                            }
                        }
                        $data = array(
                            'ordersn' => $orderData['order_sn'],
                            'status' => $orderData['status'],
                            'delivery' => date('Y-m-d', $orderData['good_time']),
                            'address' => $orderData['address'],
                            'type' => $type,
                            'total' => $orderData['money'],
                            'yunfei' => 0,
                            'depreciation' => $orderData['is_old'], //是否有折旧 1有 0无
                            'workersname' => $orderData['shipper_name'],
                            'workersmobile' => !empty($orderData['shipper_mobile']) ? $orderData['shipper_mobile'] : ''
                        );
                    }

                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-2, '当前订单不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.6 送气工基础信息
    public function shipperinfoAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($token)) {
                $data = array();
                if (!empty($data)) {
                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-2, '当前用户不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //5.7 安全报告
    public function safeimageAction() {
        define('ROOT_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/webroot_www/statics/upload/safe/');
        $order_sn = $this->getRequest()->getPost('order_sn');
        $num = $this->getRequest()->getPost('num');
        if (!empty($order_sn)) {
            $imageArr = array();
            for ($i = 1; $i <= $num; $i++) {
                if (!empty($_FILES['file' . $i]['name'])) {
                    $ext = pathinfo($_FILES['file' . $i]['name']);
                    $ext = strtolower($ext['extension']);
                    $tempFile = $_FILES['file' . $i]['tmp_name'];
                    $targetPath = ROOT_PATH;
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0777, true);
                    }
                    $photo_name = $order_sn . $i;

                    $new_file_name = $photo_name . '.' . $ext;
                    $targetFile = $targetPath . $new_file_name;
                    move_uploaded_file($tempFile, $targetFile);

                    $imageArr[] = $new_file_name;
                }
            }
            if (!empty($imageArr)) {
                $ilist = json_encode($imageArr);
                LibF::M('order')->where(array('order_sn' => $order_sn))->save(array('safe_image' => $ilist));
                $this->app->respond(1, '上传成功');
            } else {
                $this->app->respond(-4, '上传失败');
            }
        } else {
            $this->app->respond(-4, '当前订单号不存在');
        }
    }

    //5.8 送气工扫码列表
    public function bottlelistAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($token)) {
                $where['shipper_id'] = $shipper_id;
                $data = LibF::M('shipper_inventory')->where($where)->select();
                if (!empty($data)) {
                    $returnData = array();

                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
                    foreach ($data as $value) {
                        $val['number'] = $value['number'];
                        $val['xinpian'] = $value['xinpian'];
                        $val['type'] = $value['type'];
                        $val['is_open'] = $value['is_open'];
                        $val['type_name'] = $bottleTypeData[$value['type']]['bottle_name'];
                        $returnData[] = $val;
                    }
                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-2, '当前送气工没有库存');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 获取用户信息
    public function userinfoAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $kid = $this->getRequest()->getPost('kid');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($mobile) || !empty($kid)) {

                if ($kid && $mobile) {
                    $condition['kid'] = $kid;
                    $condition['mobile_phone'] = $mobile;
                    $condition['_logic'] = 'OR';
                } else if ($mobile) {
                    $condition['mobile_phone'] = $mobile;
                } else {
                    $condition['kid'] = $kid;
                }

                $kehuModel = new KehuModel();
                $data = $kehuModel->getKehuInfo($condition, true);
                if (!empty($data)) {
                    $regionModel = new RegionModel();
                    $regionData = $regionModel->getRegionObject();
                    $data['sheng_name'] = isset($regionData[$data['sheng']]) ? $regionData[$data['sheng']]['region_name'] : '';
                    $data['shi_name'] = isset($regionData[$data['shi']]) ? $regionData[$data['shi']]['region_name'] : '';
                    $data['qu_name'] = isset($regionData[$data['qu']]) ? $regionData[$data['qu']]['region_name'] : '';
                    $data['cun_name'] = isset($regionData[$data['cun']]) ? $regionData[$data['cun']]['region_name'] : '';
                    $data['safe_date'] = !empty($data['safe_time']) ? date('Y-m-d H:i',$data['safe_time']) : '';
                    
                    $data['orderlist'] = '';
                    $safe_detial = json_decode($data['safe_detial']);
                    if (!empty($safe_detial)) {
                        $where['id'] = array('in', $safe_detial);
                        $reportList = LibF::M('security_report')->field('comment,id,listorder')->order('listorder')->where($where)->select(); //查询上次安检项
                        $orders = array();
                        foreach ($reportList as $key => $value) {
                            $orders[] = $value['listorder'];
                        }
                        $data['orderlist'] = implode(',', $orders);
                    }

                    $data['bottle_data'] = !empty($data['bottle_data']) ? json_decode($data['bottle_data']) : '';
                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-2, '当前用户不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 获取当前送气工位置
    public function shipperpositionAction() {

        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');
            $position = $this->getRequest()->getPost('position');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shipper_id)) {
                $param['shop_id'] = $shop_id;
                $param['shipper_id'] = $shipper_id;
                $param['shipper_name'] = $shipper_name;
                $param['shipper_mobile'] = $shipper_mobile;
                $param['position'] = $position;

                $data = LibF::M('position')->where(array('shipper_id' => $shipper_id))->find();
                if (!empty($data)) {
                    $status = LibF::M('position')->where(array('shipper_id' => $shipper_id))->save($param);
                } else {
                    $status = LibF::M('position')->add($param);
                }
                if ($status) {
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

    //补充接口 判断当前钢瓶是否存在
    public function isbottleAction() {
        if ($this->getRequest()->isPost()) {
            $xinpian = $this->getRequest()->getPost('xinpian');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $is_open = $this->getRequest()->getPost('is_open');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($xinpian)) {
                $condition['xinpian'] = $xinpian;
                $condition['number'] = $xinpian;
                $condition['_logic'] = 'OR';

                if ($is_open == 1) {
                    $map["_complex"] = $condition;
                    $map['shop_id'] = $shop_id;
                    $map['status'] = 1;
                    $map['is_use'] = 1;
                    $data = LibF::M('store_inventory')->where($map)->find();
                } else {
                    $data = LibF::M('bottle')->where($condition)->find();
                }
                if (!empty($data)) {
                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
                    $data['type_name'] = isset($bottleTypeData[$data['type']]) ? $bottleTypeData[$data['type']]['bottle_name'] : '';

                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-3, '钢瓶不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 送气工库存回门店（退货）
    public function backshopAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');

            //array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
            $bottle = $this->getRequest()->getPost('bottle'); //重瓶
            $bottle_data = $this->getRequest()->getPost('bottle_data');

            $kbottle = $this->getRequest()->getPost('kbottle'); //空瓶
            $kbottle_data = $this->getRequest()->getPost('kbottle_data');

            $qbottle = $this->getRequest()->getPost('qbottle');  //折旧瓶
            //$qbottle_data = $this->getRequest()->getPost('qbottle_data');

            $xbottle = $this->getRequest()->getPost('xbottle'); //待修瓶
            $xbottle_data = $this->getRequest()->getPost('xbottle_data'); //待修瓶
            
            $pbottle = $this->getRequest()->getPost('pbottle'); //配件

            $comment = $this->getRequest()->getPost('comment');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($shop_id) && !empty($token)) {
                $shipperInsert = $shipperDetitalInsert = array();

                $app = new App();
                $orderSn = $app->build_order_no();
                $hkshipper = 'tp' . $orderSn;

                $time = date('Y-m-d');
                $ctime = time();
                if (!empty($bottle) && !empty($bottle_data)) {
                    $insertData['confirme_no'] = $hkshipper;
                    $insertData['shop_id'] = $shop_id;
                    $insertData['shipper_id'] = $shipper_id;
                    if (!empty($shipper_name))
                        $insertData['shipper_name'] = $shipper_name;
                    $insertData['ftype'] = 2; //重瓶
                    $insertData['bottle'] = $bottle;
                    $insertData['bottle_data'] = $bottle_data;
                    if (!empty($comment))
                        $insertData['comment'] = $comment;
                    $insertData['time'] = $time;
                    $insertData['ctime'] = $ctime;
                    $shipperInsert[] = $insertData;

                    $bottleArr = json_decode($bottle, true);
                    if (!empty($bottleArr)) {
                        foreach ($bottleArr as $value) {
                            $val['confirme_no'] = $hkshipper;
                            $val['shop_id'] = $shop_id;
                            $val['shipper_id'] = $shipper_id;
                            $val['ftype'] = 2;
                            $val['type'] = $value['type_id'];
                            $val['typename'] = $value['type_name'];
                            $val['num'] = $value['type_num'];
                            $val['ctime'] = time();
                            $shipperDetitalInsert[] = $val;
                        }
                    }
                }

                if (!empty($kbottle) && !empty($kbottle_data)) {
                    $insertData['confirme_no'] = $hkshipper;
                    $insertData['shop_id'] = $shop_id;
                    $insertData['shipper_id'] = $shipper_id;
                    if (!empty($shipper_name))
                        $insertData['shipper_name'] = $shipper_name;
                    $insertData['ftype'] = 1; //空瓶
                    $insertData['bottle'] = $kbottle;
                    $insertData['bottle_data'] = $kbottle_data;
                    if (!empty($comment))
                        $insertData['comment'] = $comment;
                    $insertData['time'] = $time;
                    $insertData['ctime'] = $ctime;
                    $shipperInsert[] = $insertData;

                    $bottleArr = json_decode($kbottle, true);
                    if (!empty($bottleArr)) {
                        foreach ($bottleArr as $value) {
                            $val['confirme_no'] = $hkshipper;
                            $val['shop_id'] = $shop_id;
                            $val['shipper_id'] = $shipper_id;
                            $val['ftype'] = 1;
                            $val['type'] = $value['type_id'];
                            $val['typename'] = $value['type_name'];
                            $val['num'] = $value['type_num'];
                            $val['ctime'] = time();
                            $shipperDetitalInsert[] = $val;
                        }
                    }
                }

                if (!empty($xbottle) && !empty($xbottle_data)) {
                    $insertData['confirme_no'] = $hkshipper;
                    $insertData['shop_id'] = $shop_id;
                    $insertData['shipper_id'] = $shipper_id;
                    if (!empty($shipper_name))
                        $insertData['shipper_name'] = $shipper_name;
                    $insertData['ftype'] = 4; //待修
                    $insertData['bottle'] = $xbottle;
                    $insertData['bottle_data'] = $xbottle_data;
                    if (!empty($comment))
                        $insertData['comment'] = $comment;
                    $insertData['time'] = $time;
                    $insertData['ctime'] = $ctime;
                    $shipperInsert[] = $insertData;

                    $bottleArr = json_decode($kbottle, true);
                    if (!empty($bottleArr)) {
                        foreach ($bottleArr as $value) {
                            $val['confirme_no'] = $hkshipper;
                            $val['shop_id'] = $shop_id;
                            $val['shipper_id'] = $shipper_id;
                            $val['ftype'] = 1;
                            $val['type'] = $value['type_id'];
                            $val['typename'] = $value['type_name'];
                            $val['num'] = $value['type_num'];
                            $val['ctime'] = time();
                            $shipperDetitalInsert[] = $val;
                        }
                    }
                }
                
                if (!empty($qbottle)) {
                    $insertData['confirme_no'] = $hkshipper;
                    $insertData['shop_id'] = $shop_id;
                    $insertData['shipper_id'] = $shipper_id;
                    if (!empty($shipper_name))
                        $insertData['shipper_name'] = $shipper_name;
                    $insertData['ftype'] = 3; //折旧瓶
                    $insertData['bottle'] = $qbottle;
                    if (!empty($comment))
                        $insertData['comment'] = $comment;
                    $insertData['time'] = $time;
                    $insertData['ctime'] = $ctime;
                    $shipperInsert[] = $insertData;

                    $bottleArr = json_decode($qbottle, true);
                    if (!empty($bottleArr)) {
                        foreach ($bottleArr as $value) {
                            $val['confirme_no'] = $hkshipper;
                            $val['shop_id'] = $shop_id;
                            $val['shipper_id'] = $shipper_id;
                            $val['ftype'] = 3;
                            $val['type'] = $value['type_id'];
                            $val['typename'] = $value['type_name'];
                            $val['num'] = $value['type_num'];
                            $val['ctime'] = time();
                            $shipperDetitalInsert[] = $val;
                        }
                    }
                }

                if (!empty($pbottle)) {
                    $insertData['confirme_no'] = $hkshipper;
                    $insertData['shop_id'] = $shop_id;
                    $insertData['shipper_id'] = $shipper_id;
                    if (!empty($shipper_name))
                        $insertData['shipper_name'] = $shipper_name;
                    $insertData['ftype'] = 0; //配件
                    $insertData['bottle'] = $pbottle;
                    if (!empty($comment))
                        $insertData['comment'] = $comment;
                    $insertData['time'] = $time;
                    $insertData['ctime'] = $ctime;
                    $shipperInsert[] = $insertData;

                    $bottleArr = json_decode($pbottle, true);
                    if (!empty($bottleArr)) {
                        foreach ($bottleArr as $value) {
                            $val['confirme_no'] = $hkshipper;
                            $val['shop_id'] = $shop_id;
                            $val['shipper_id'] = $shipper_id;
                            $val['ftype'] = 0;
                            $val['type'] = $value['type_id'];
                            $val['typename'] = $value['type_name'];
                            $val['num'] = $value['type_num'];
                            $val['ctime'] = time();
                            $shipperDetitalInsert[] = $val;
                        }
                    }
                }

                if (!empty($shipperInsert)) {
                    $status = LibF::M('confirme_shipper')->insertAlldata($shipperInsert);
                    if (!empty($shipperDetitalInsert)) {
                        LibF::M('confirme_shipper_detail')->insertAlldata($shipperDetitalInsert);
                    }
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-2, '当前参数提交不正确');
                }
            } else {
                $this->app->respond(-3, '当前参数提交不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.14 送气工（退回门店）回库单据审核
    public function backshipperAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time');
            $param['end_time'] = $this->getRequest()->getPost('end_time');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';
            $rData = array();
            if (!empty($token)) {
                if (!empty($shop_id))
                    $where['shop_id'] = $shop_id;
                if (!empty($shipper_id))
                    $where['shipper_id'] = $shipper_id;

                if ($param['start_time'] && $param['end_time'])
                    $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('confirme_shipper')->field('confirme_no,shop_id,shipper_id,shipper_name,ftype,bottle,bottle_data,time,status')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    //获取当前钢瓶规格
                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                    //获取钢瓶类型
                    $numArr = array();
                    foreach ($data as $bVal) {
                        if ($bVal['ftype'] > 0) {
                            $bDataArr = json_decode($bVal['bottle_data'], true);
                            if (!empty($bDataArr)) {
                                foreach ($bDataArr as $bV) {
                                    $numArr[] = $bV['number'];
                                }
                            }
                        }
                    }

                    if (!empty($numArr)) {
                        $bottleModel = new BottleModel();
                        $bottleObject = $bottleModel->bottleOData($numArr);
                    }
                    foreach ($data as $key => &$value) {
                        $value['bottle'] = json_decode($value['bottle'], true);
                        $value['bottle_data'] = json_decode($value['bottle_data'], true);
                        if ($value['ftype'] == 0) {
                            if (!empty($value['bottle'])) {
                                foreach ($value['bottle'] as $val) {
                                    $value['bottle_data'][] = $val;
                                }
                            }
                        } else {
                            if (!empty($value['bottle_data'])) {
                                foreach ($value['bottle_data'] as &$val) {
                                    $val['type_id'] = 0;
                                    if (isset($bottleObject['number'][$val['number']])) {
                                        $val['type_id'] = $bottleObject['number'][$val['number']]['type'];
                                        $val['type_name'] = $bottleTypeData[$val['type_id']]['bottle_name'];
                                    }
                                }
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

    //补充接口 送气工退瓶
    public function backorderAction() {

        if ($this->getRequest()->isPost()) {
            
            $kid = $this->getRequest()->getPost('kid');
            $mobile = $this->getRequest()->getPost('mobile');
            $username = $this->getRequest()->getPost('username');
            $back_shop_id = $this->getRequest()->getPost('shop_id');
            
            $type = $this->getRequest()->getPost('type',1); //1退货 2置换
            
            $back_shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');
            
            $sheng = $this->getRequest()->getPost('sheng');
            $shi = $this->getRequest()->getPost('shi');
            $qu = $this->getRequest()->getPost('qu');
            $cun = $this->getRequest()->getPost('cun');
            $address = $this->getRequest()->getPost('address');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($kid) && !empty($back_shop_id) && !empty($back_shipper_id)) {
                $time = time();

                $app = new App();
                $orderSn = $app->build_order_no();
                $orderSn = substr($orderSn, 0, -2);
                $param['depositsn'] = 'tp' . $orderSn . $kid;

                $param['type'] = $type;
                $param['kid'] = $kid;
                $param['mobile'] = $mobile;
                $param['shop_id'] = $back_shop_id;
                if (!empty($username))
                    $param['username'] = $username;

                $param['status'] = 1;
                $param['shipper_id'] = $back_shipper_id;
                $param['shipper_name'] = $shipper_name;
                $param['shipper_mobile'] = $shipper_mobile;

                $param['time'] = date('Y-m-d');
                if (!empty($sheng))
                    $param['sheng'] = $sheng;
                if (!empty($shi))
                    $param['shi'] = $shi;
                if (!empty($qu))
                    $param['qu'] = $qu;
                if (!empty($cun))
                    $param['cun'] = $cun;
                if (!empty($address))
                    $param['address'] = $address;

                $param['time_created'] = $time;
                $status = LibF::M('deposit_list')->add($param);
                if (!empty($status)) {
                    $this->app->respond(1, $param['depositsn']);
                } else {
                    $this->app->respond(-3, '退瓶订单数据');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 送气工根据当前编号获取相关信息
    public function getreceiptAction() {
        if ($this->getRequest()->isPost()) {

            $receiptno = $this->getRequest()->getPost('receiptno');
            $type = $this->getRequest()->getPost('type', 1); //1欠款 2押金
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($receiptno)) {

                $returnData = array();
                if ($type == 1) {
                    $where['contractno'] = $receiptno;
                    $where['is_return'] = 0;
                    $data = LibF::M('order_arrears')->where($where)->find();
                    if (!empty($data)) {
                        $returnData['type'] = $data['arrears_type']; //1燃气2押金3配件
                        $returnData['money'] = $data['money'];
                    }
                } else if ($type == 2) {
                    $where['receiptno'] = $receiptno;
                    $where['status'] = 0;
                    $data = LibF::M('deposit')->where($where)->find();
                    if (!empty($data)) {
                        $returnData['type'] = 1;
                        $returnData['money'] = $data['money'];
                    }
                }

                if (!empty($returnData)) {
                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-2, '当前单据不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 获取用户最近一次安检信息
    public function reportuserinfoAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($mobile)) {
                $data = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                if (!empty($data)) {
                    if (!empty($data['safe_detial'])) {
                        $safe_detial = json_decode($data['safe_detial']);
                        if (!empty($safe_detial)) {
                            $where['id'] = array('in', $safe_detial);
                            $reportList = LibF::M('security_report')->field('comment,id,listorder')->order('listorder')->where($where)->select(); //查询上次安检项
                            $orders = array();
                            foreach ($reportList as $key => $value) {
                                $orders[] = $value['listorder'];
                            }
                            $data['orderlist'] = implode(',', $orders);
                            $data['time'] = date('Y-m-d', $data['safe_time']);
                            $data['report'] = $reportList;
                        }
                    } else {
                        $data['orderlist'] = '';
                        $data['time'] = '';
                        $data['report'] = '';
                    }
                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-2, '当前用户不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 获取当前送气工报修订单
    public function baoxiulistAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $page = $this->getRequest()->getPost('page', 1);
            $pagesize = $this->getRequest()->getPost('pagesize', 15);
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id)) {
                $pagestart = ($page - 1) * $pagesize;
                
                $shopObject = ShopModel::getShopArray();
                
                $repairModel = new RepairModel();
                $repairObect = $repairModel->getRepairArray();
                
                $regionModel = new RegionModel();
                $regionObject = $regionModel->getRegionObject();
                
                $where = array('rq_baoxiu.shipper_id' => $shipper_id);
                $filed = 'rq_baoxiu.*,rq_kehu.card_sn,rq_kehu.mobile_phone,rq_kehu.sheng,rq_kehu.shi,rq_kehu.qu,rq_kehu.cun,rq_kehu.address';
                $dataModel = new Model('baoxiu');
                $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_baoxiu.kid = rq_kehu.kid')->field($filed)->where($where)->order('rq_baoxiu.ctime desc')->limit($pagestart, $pagesize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['address'] = strip_tags($value['address']);
                        $value['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                        $value['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                        $value['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                        $value['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                        $value['time'] = date("Y-m-d", $value['ctime']);
                        $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                        $baoxiuArr = !empty($value['baoxiu_wt']) ? json_decode($value['baoxiu_wt']) : '';
                        $baoxiuText = '';
                        if (!empty($baoxiuArr)) {
                            $bxList = array();
                            foreach ($baoxiuArr as $bVal) {
                                if (isset($repairObect[$bVal])) {
                                    $bxList[] = $repairObect[$bVal]['title'];
                                }
                            }
                            $baoxiuText = implode(',', $bxList);
                        }
                        $value['baoxiu_text'] = $baoxiuText;
                    }
                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-2, '当前数据不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 获取当前送气工处理报修订单
    public function treatebaoxiuAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $comment = $this->getRequest()->getPost('comment');
            $id = $this->getRequest()->getPost('id');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shipper_id) && !empty($comment) && !empty($id)) {
                $where['id'] = $id;
                $udata['treatment'] = $comment;
                $udata['admin_user_id'] = $shipper_id;
                $udata['admin_user_name'] = $shipper_name;
                $udata['status'] = 2;
                $status = LibF::M('baoxiu')->where($where)->save($udata);
                if ($status) {
                    $this->app->respond(1, '更新成功');
                } else {
                    $this->app->respond(-2, '更新失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口 送气工单独收取折旧瓶
     * 
     * zhejiu_data{
     *      type_id     //规格id
     *      type_name //规格名称
     *      year     //年限id
     *      money    //对应金额
     * }
     */
    public function createdepreciationAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');
            
            $shop_id = $this->getRequest()->getPost('shop_id');
            
            $kid = $this->getRequest()->getPost('kid');
            $mobile = $this->getRequest()->getPost('mobile');
            
            $sheng = $this->getRequest()->getPost('sheng');
            $shi = $this->getRequest()->getPost('shi');
            $qu = $this->getRequest()->getPost('qu');
            $cun = $this->getRequest()->getPost('cun');
            $address = $this->getRequest()->getPost('address');
            
            $bottle_data = $this->getRequest()->getPost('zhejiu_data');  //data 串
            $money = $this->getRequest()->getPost('money');
            $weight = $this->getRequest()->getPost('weight');
            
            $order_sn = $this->getRequest()->getPost('order_sn'); //如果跟当前订单走，如果单独收重新创建
            
            $time = time();
            $comment = $this->getRequest()->getPost('comment');
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($bottle_data) && !empty($shipper_id) && !empty($kid)) {

                if (!empty($order_sn)) {
                    $orderData['order_sn'] = $order_sn;
                } else {
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $orderSn = substr($orderSn, 0, -2);
                    $orderData['order_sn'] = 'Dd' . $orderSn . $kid;
                }
                $orderData['kid'] = $kid;
                $orderData['mobile'] = $mobile;
                if (!empty($sheng))
                    $orderData['sheng'] = $sheng;
                if (!empty($shi))
                    $orderData['shi'] = $shi;
                if (!empty($qu))
                    $orderData['qu'] = $qu;
                if (!empty($cun))
                    $orderData['cun'] = $cun;
                if (!empty($address))
                    $orderData['address'] = $address;
                if (!empty($shop_id))
                    $orderData['shop_id'] = $shop_id;

                $orderData['bottle_data'] = $bottle_data;
                $orderData['shipper_id'] = $shipper_id;
                $orderData['shipper_mobile'] = $shipper_mobile;
                $orderData['shipper_name'] = $shipper_name;
                if ($money > 0)
                    $orderData['money'] = $money;
                if ($weight > 0)
                    $orderData['weight'] = $weight;
                if (!empty($comment))
                    $orderData['comment'] = $comment;

                $orderData['status'] = 1;
                $orderData['time_created'] = $time;
                $status = LibF::M('order_depreciation')->add($orderData);
                if ($status) {
                    $zhejiuData = json_decode($bottle_data,TRUE);
                    
                    $dataModel = new DataInventoryModel();
                    $dataModel->zjshipperck($orderData['shop_id'], $orderData['shipper_id'], 0, $zhejiuData, 1, 3);  //送气工折旧瓶入库

                    LibF::M('shipper')->where(array('shipper_id' => $orderData['shipper_id']))->setDec('money', $money); //zt：收款后送气工余额减少
                    if ($money > 0) {
                        $payInsert['paylist_no'] = $orderData['order_sn'];
                        $payInsert['shipper_id'] = $shipper_id;
                        $payInsert['shipper_name'] = $shipper_name;
                        $payInsert['mobile_phone'] = $shipper_mobile;
                        $payInsert['money'] = $money;
                        $payInsert['shop_id'] = $orderData['shop_id'];
                        $payInsert['time'] = date('Y-m-d');
                        $payInsert['time_created'] = $time;
                        $payInsert['is_statistics'] = 1;
                        $payInsert['type'] = 5;
                        $payInsert['status'] = 2;
                        LibF::M('shipper_paylist')->add($payInsert);
                    }

                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-2, '创建失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取当前折旧订单列表
     */
    public function depreciationlistAction() {
        $shipper_id = $this->getRequest()->getPost('shipper_id');
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shipper_id) && !empty($token)) {
            $where['rq_order_depreciation.shipper_id'] = $shipper_id;
            $pageStart = ($page - 1) * $param['pagesize'];

            $orderModel = new Model('order_depreciation');
            $filed = " rq_order_depreciation.*,rq_kehu.user_name ";
            $leftJoin = " LEFT JOIN rq_kehu ON rq_order_depreciation.kid = rq_kehu.kid ";
            $data = $orderModel->join($leftJoin)->field($filed)->where($where)->order('rq_order_depreciation.id desc')->limit($pageStart, $this->pageSize)->select();
            if (!empty($data)) {
                $shopModel = new ShopModel();
                $shopObject = $shopModel->getShopArray();

                $regionModel = new RegionModel();
                $regionObject = $regionModel->getRegionObject();
                foreach ($data as &$value) {
                    $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';

                    $value['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                    $value['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                    $value['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                    $value['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                    $value['time'] = date('Y-m-d', $value['time_created']);
                    $value['data'] = json_decode($value['bottle_data'], true);
                }
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-2, '更新失败');
            }
        }else{
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 补充接口 送气工退换钢瓶
     * 
     * bottle_data{
     *      number   //钢印号
     *      xinpian  //芯片号 
     *      type    //钢瓶类型1空2重3折旧4待修
     *      weight  //余气重量
     *      price   //余气金额
     *      deposit //押金
     * }
     * 
     * change_data  钢瓶号
     */
    public function createchangebottleAction() {
        if ($this->getRequest()->isPost()) {
            $depositsn = $this->getRequest()->getPost('depositsn'); //单据号

            $pData = array();
            $bottle_data = $this->getRequest()->getPost('bottle_data');  //data 串
            $bottle_text = $this->getRequest()->getPost('bottle_text'); //data串详情

            $change_data = '';
            $type = $this->getRequest()->getPost('type', 1); //1退货2置换
            $bottle_type = $this->getRequest()->getPost('bottle_type',1); //1待修瓶 2 重瓶
            if ($type == 2) {
                $change_data = $this->getRequest()->getPost('change_data');  //置换瓶的data串
                if (!empty($change_data)) {
                    $cData = json_decode($change_data, true);
                    $zData = array();
                    foreach ($cData as $cVal) {
                        $zData[] = $cVal['number'];
                    }
                    $pData['zp'] = json_encode($zData);
                }
                $pData['fp'] = $bottle_text;
            } else {
                $pData['kp'] = $bottle_text;
            }

            $time = time();
            $money = $this->getRequest()->getPost('money', 0);
            $weight = $this->getRequest()->getPost('weight', 0); //余气重量
            $weightmoney = $this->getRequest()->getPost('yq_money', 0); //余气金额
            $deposit = $this->getRequest()->getPost('deposit', 0); //押金

            $doormoney = $this->getRequest()->getPost('doormoney', 0); //上门费
            $comment = $this->getRequest()->getPost('comment', ''); //备注
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($bottle_data) && !empty($depositsn) && !empty($token)) {

                $orderData['bottle'] = $bottle_data;
                $orderData['bottle_text'] = $bottle_text;
                if (!empty($change_data))
                    $orderData['change_data'] = $change_data;

                if (!empty($comment))
                    $orderData['comment'] = $comment;

                if ($money > 0)
                    $orderData['money'] = $money;
                if ($weight > 0)
                    $orderData['shouldweight'] = $weight;
                if ($weightmoney > 0)
                    $orderData['shouldmoney'] = $weightmoney;
                if ($deposit > 0)
                    $orderData['deposit'] = $deposit;
                if ($doormoney > 0)
                    $orderData['doormoney'] = $doormoney;

                $orderData['type'] = $type;
                $orderData['status'] = 2;
                $orderData['time'] = date('Y-m-d');
                $orderData['ctime'] = $time;
                
                $where['depositsn'] = $depositsn;
                $depositData = LibF::M('deposit_list')->where($where)->find();
                if (!empty($depositData)) {
                    $status = LibF::M('deposit_list')->where($where)->save($orderData);
                    if ($status) {
                        //钢瓶库存更新
                        $this->shipperIncomeInfo($depositData['shipper_id'], $depositData['shop_id'], $depositData['kid'], $pData, $bottle_type);
                        if (isset($orderData['money']) && $orderData['money'] > 0) {
                            LibF::M('shipper')->where(array('shipper_id' => $depositData['shipper_id']))->setDec('money', $orderData['money']); //zt：收款后送气工余额减少

                            $payInsert['paylist_no'] = $depositsn;
                            $payInsert['shipper_id'] = $depositData['shipper_id'];
                            $payInsert['shipper_name'] = $depositData['shipper_name'];
                            $payInsert['mobile_phone'] = $depositData['shipper_mobile'];
                            $payInsert['shop_id'] = $depositData['shop_id'];
                            $payInsert['time'] = date('Y-m-d');
                            $payInsert['time_created'] = $time;
                            $depositFiance = $payFiance = $payInsert;
                            if ($weightmoney > 0) {

                                $depositFiance['money'] = $orderData['money'];
                                $depositFiance['is_statistics'] = 1;
                                $depositFiance['type'] = 3;
                                $depositFiance['status'] = 2;
                                $changeArr[] = $depositFiance;
                                $payFiance['money'] = $deposit;
                                $payFiance['is_statistics'] = 0;
                                $payFiance['type'] = 8;
                                $payFiance['status'] = 2;
                                $changeArr[] = $payFiance;
                                LibF::M('shipper_paylist')->uinsertAll($changeArr);
                            } else {
                                $payInsert['money'] = $orderData['money'];
                                $payInsert['is_statistics'] = 1;
                                $payInsert['type'] = 8;
                                $payInsert['status'] = 2;
                                LibF::M('shipper_paylist')->add($payInsert);
                            }
                        }
                        
                        if (($type == 1) && ($doormoney > 0)) {
                            $payInsert['money'] = $doormoney;
                            $payInsert['is_statistics'] = 1;
                            $payInsert['type'] = 9;
                            $payInsert['status'] = 2;
                            LibF::M('shipper_paylist')->add($payInsert);
                        }

                        $this->app->respond(1, '创建成功');
                    } else {
                        $this->app->respond(-2, '创建失败');
                    }
                } else {
                    $this->app->respond(-2, '当前订单不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 送气工退换钢瓶列表
     * 
     */
    public function changeorderlistAction() {
        $shipper_id = $this->getRequest()->getPost('shipper_id');
        $type = $this->getRequest()->getPost('type',1); //1退货 2置换
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shipper_id) && !empty($token)) {
            $where['shipper_id'] = $shipper_id;
            $where['type'] = $type;
            $pageStart = ($page - 1) * $param['pagesize'];

            $data = LibF::M('deposit_list')->where($where)->limit($pageStart, $param['pagesize'])->order('id desc')->select();
            if (!empty($data)) {
                $shopModel = new ShopModel();
                $shopObject = $shopModel->getShopArray();

                $regionModel = new RegionModel();
                $regionObject = $regionModel->getRegionObject();
                foreach ($data as &$value) {
                    $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';

                    $value['type'] = $value['type'];
                    $value['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                    $value['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                    $value['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                    $value['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                    $value['time'] = date('Y-m-d', $value['time_created']);
                    $value['data'] = (!empty($value['bottle'])) ? json_decode($value['bottle'], true) : array();
                    $value['bottle_text'] = (!empty($value['bottle_text'])) ? json_decode($value['bottle_text'], true) : array();
                    $value['change_data'] = (!empty($value['change_data'])) ? json_decode($value['change_data'], true) : array();
                }
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-2, '没有数据');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 送气工结算预支付订单
     */
    public function paymentorderAction() {
        if ($this->getRequest()->isPost()) {
            $time = time();
            $date = date('Y-m-d');

            $where['order_sn'] = $this->getRequest()->getPost('ordersn');
            $kid = $this->getRequest()->getPost('kid');
            $param['pay_money'] = $this->getRequest()->getPost('pay_money');  //实收款
            $param['shipper_money'] = $this->getRequest()->getPost('shipper_money'); //送气工优惠
            
            $productArr = $this->getRequest()->getPost('peijian'); //配件抵扣
            $product_pice = $this->getRequest()->getPost('peijian_money', 0); //配件抵扣金额
            
            $is_cash = $this->getRequest()->getPost('is_cash', 0); //0现金支付  1网上支付
            if ($is_cash == 0) {
                $param['ispayment'] = 1;
            } else {
                $param['order_paytype'] = 1;
            }
            $param['status'] = 4;   //订单已结算
            $param['order_tc_type'] = 7;  //预支付订单
            $param['should_time'] = $time;

            $comment = $this->getRequest()->getPost('comment'); //订单备注
            if (!empty($comment)) {
                $param['comment'] = $comment;
            }
            $bottleData = $this->getRequest()->getPost('bottle_data'); //余额data串
            if (!empty($bottleData)) {
                $param['bottle_data'] = $bottleData;
            }
            $where['status'] = 2;
            $orderData = LibF::M('order')->where($where)->find();
            if (!empty($orderData)) {
                $status = LibF::M('order')->where($where)->save($param);
                if ($status && !empty($bottleData)) {
                    //抵扣商品
                    $pjArr = array();
                    if (!empty($productArr)) {
                        $pArr = json_decode($productArr, TRUE);
                        foreach ($pArr as $dVal) {
                            //存储配件
                            if (isset($dVal['is_zhekou']) && !empty($dVal['is_zhekou'])) {
                                $pdArr['shop_id'] = $orderData['shop_id'];
                                $pdArr['order_sn'] = $orderData['order_sn'];
                                $pdArr['goods_id'] = $dVal['good_id'];
                                $pdArr['goods_name'] = $dVal['good_name'];
                                $pdArr['goods_price'] = $dVal['good_price'];
                                $pdArr['goods_num'] = $dVal['good_num'];
                                $pdArr['goods_type'] = 2;
                                $pdArr['goods_kind'] = $dVal['good_kind'];
                                $pdArr['pay_money'] = isset($dVal['zk_money']) ? $dVal['zk_money'] : $dVal['good_price'];
                                $pdArr['good_zhekou'] = $dVal['is_zhekou'];
                                $pjArr[] = $pdArr;
                            }
                        }
                        LibF::M('order_info')->uinsertAll($pjArr);
                    }
                    
                    //更新当前用户余额
                    $kehuData['bottle_data'] = $bottleData;
                    LibF::M('kehu')->where(array('kid' => $kid))->save($kehuData);

                    //规格
                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                    //转存成优惠券
                    $yhData = json_decode($bottleData, TRUE);
                    if (!empty($yhData)) {
                        $yhq_info = $insert_yhq = array();
                        $yhqsn = date("YmdHis");
                        foreach ($yhData as $yhkey => $yhVal) {
                            $typeName = isset($bottleTypeData[$yhVal['good_kind']]) ? $bottleTypeData[$yhVal['good_kind']]['bottle_name'] : '';
                            if ($yhVal['good_num'] > 0) {
                                for ($i = 1; $i <= $yhVal['good_num']; $i++) {
                                    $yhq_info['kid'] = $kid;
                                    $yhq_info['promotionsn'] = $yhqsn . $yhkey . $kid;
                                    $yhq_info['title'] = $yhVal['good_name'] . $typeName;
                                    $yhq_info['type'] = 2;
                                    $yhq_info['good_type'] = $yhVal['good_kind'];
                                    $yhq_info['price'] = $yhVal['good_price'];
                                    $yhq_info['money'] = $yhVal['good_price'];
                                    $yhq_info['num'] = 1;
                                    $insert_yhq[] = $yhq_info;
                                }
                            }
                        }
                        LibF::M('promotions_user')->uinsertAll($insert_yhq);
                    }
                    //送气工收入
                    if ($param['pay_money'] > 0) {
                        $payInsert['paylist_no'] = $where['order_sn'];
                        $payInsert['shipper_id'] = $orderData['shipper_id'];
                        if (!empty($orderData['shipper_name']))
                            $payInsert['shipper_name'] = $orderData['shipper_name'];
                        if (!empty($orderData['shipper_mobile']))
                            $payInsert['mobile_phone'] = $orderData['shipper_mobile'];
                        $payInsert['money'] = $param['pay_money'];
                        $payInsert['shop_id'] = $orderData['shop_id'];
                        $payInsert['time'] = $date;
                        $payInsert['time_created'] = $param['should_time'];

                        $payInsert['type'] = 1;
                        $payInsert['is_statistics'] = 1;
                        LibF::M('shipper_paylist')->add($payInsert);
                    }

                    $this->app->respond(1, '结算成功');
                }else {
                    $this->app->respond(-1, '结算失败');
                }
            } else {
                $this->app->respond(-2, '当前订单不存在');
            }
        } else {
            $this->app->respond(-3, '未提交参数');
        }
    }

    /**
     * 送气工抢单
     */
    public function shippergraboneAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $ordersn = $this->getRequest()->getPost('ordersn');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($ordersn) && !empty($shipper_id)) {
                $where['order_sn'] = $ordersn;
                $where['status'] = 1;
                $orderData = LibF::M('order')->where($where)->find();
                if (!empty($orderData)) {
                    $udata['shipper_id'] = $shipper_id;
                    $udata['shipper_mobile'] = $shipper_mobile;
                    $udata['shipper_name'] = $shipper_name;
                    $udata['status'] = 2;
                    $status = LibF::M('order')->where($where)->save($udata);
                    if ($status) {
                        $this->app->respond(1, '订单已接收，请准时配送');
                    } else {
                        $this->app->respond(-2, '抢单失败');
                    }
                } else {
                    $this->app->respond(-2, '订单已失效');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 抢单池
     */
    public function graboneAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($shipper_id) && !empty($token)) {
                //备注 当前订单状态 1未派发 2配送中 4已完成
                $where['status'] = 1;
                $data = LibF::M('order')->field('kid,order_sn,sheng,shi,qu,cun,address,money,kehu_type,ctime,username')->where($where)->order('ctime desc')->select(); //统计订单销售额

                $newData = array();
                if (!empty($data)) {
                    $regionModel = new RegionModel();
                    $regionObject = $regionModel->getRegionObject();
                    foreach ($data as $value) {
                        $val['kid'] = $value['kid'];
                        $val['order_sn'] = $value['order_sn'];
                        $val['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                        $val['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                        $val['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                        $val['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                        $val['address'] = $value['address'];
                        $val['time'] = date('Y-m-d',$value['ctime']);
                        $val['username'] = $value['username'];
                        $val['money'] = $value['money'];
                        $val['kh_name'] = ($value['kehu_type'] == 1) ? '居民用户' : '商业用户';
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
    
    //补充接口 版本升级更新
    public function appversionAction() {
        //app更新类型 app userapp shipperapp
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token)) {

                $file = "vsersion_address,version_number,version_time";
                $data = LibF::M('andrews_version')->where(array('version_status' => 1, 'version_type' => 'shipperapp'))->order('version_time desc')->limit(1)->find();
                if (!empty($data)) {
                    $data['version_address'] = $this->url . '/statics/upload/' . $data['version_address'];
                    $data['version_time'] = !empty($value['version_time']) ? date('Y-m-d H:i', $data['version_time']) : 0;
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 送气工替用户充值
    public function addbalanceAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $money = $this->getRequest()->getPost('money');   //充值金额
            $mobile = $this->getRequest()->getPost('mobile');  //用户手机号
            $shipper_id = $this->getRequest()->getPost('shipper_id'); //送气工id
            $shipper_name = $this->getRequest()->getPost('shipper_name'); //送气工姓名
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile'); //送气工手机号

            $productArr = $this->getRequest()->getPost('peijian'); //配件抵扣
            $product_pice = $this->getRequest()->getPost('peijian_money', 0); //配件抵扣金额
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($shipper_id) && !empty($money) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {

                    $date = date('Y-m-d');
                    $time = time();
                    
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $param['balance_sn'] = 'cz' . $orderSn . $kehuData['kid'];
                    $param['kid'] = $kehuData['kid'];
                    $param['paytype'] = 1;
                    $param['type'] = 1;
                    $param['money'] = $money;
                    $param['balance'] = $money + $kehuData['balance']; //充值金额
                    $param['time'] = $time;  //充值时间
                    $param['shipper_id'] = $shipper_id;
                    $status = LibF::M('balance_list')->add($param);

                    if ($status) {
                        LibF::M('kehu')->where(array('kid' => $param['kid']))->save(array('balance' => $param['balance']));

                        $send_mobile = !empty($mobile) ? $mobile : $kehuData['mobile'];
                        if (!empty($send_mobile)) {
                            //发送充值成功短信
                            //$smsdataModel = new SmsDataModel();
                            //$smsdataModel->sendsms($param['balance'], $param['money'], $send_mobile, 1);
                        }
                        
                        if ($money > 0) {
                            $payInsert['paylist_no'] = $param['balance_sn'];
                            $payInsert['shipper_id'] = $shipper_id;
                            if (!empty($shipper_name))
                                $payInsert['shipper_name'] = $shipper_name;
                            if (!empty($shipper_mobile))
                                $payInsert['mobile_phone'] = $shipper_mobile;
                            $payInsert['money'] = $money;
                            $payInsert['shop_id'] = $kehuData['shop_id'];
                            $payInsert['time'] = $date;
                            $payInsert['time_created'] = $time;
                            $payInsert['type'] = 10;
                            $payInsert['is_statistics'] = 1;
                            $payInsert['status'] = 1;

                            LibF::M('shipper')->where(array('shipper_id' => $shipper_id))->setInc('money', $money); //zt：收款后送气工余额增加
                            LibF::M('shipper_paylist')->add($payInsert);
                        }

                        //如果有配件生成订单
                        if (!empty($productArr)) {
                            //添加订单接收数据
                            $data['kid'] = $kehuData['kid'];
                            $data['username'] = $kehuData['user_name'];
                            $data['mobile'] = $mobile;
                            if (!empty($kehuData['sheng']))
                                $data['sheng'] = $kehuData['sheng'];
                            if (!empty($kehuData['shi']))
                                $data['shi'] = $kehuData['shi'];
                            if (!empty($kehuData['qu']))
                                $data['qu'] = $kehuData['qu'];
                            if (!empty($kehuData['cun']))
                                $data['cun'] = $kehuData['cun'];
                            if (!empty($kehuData['address']))
                                $data['address'] = $kehuData['address'];

                            $data['shop_id'] = $kehuData['shop_id'];
                            $data['shipper_id'] = $shipper_id;
                            if (!empty($shipper_name))
                                $data['shipper_name'] = $shipper_name;
                            if (!empty($shipper_mobile))
                                $data['shipper_mobile'] = $shipper_mobile;
                            $data['status'] = 4;
                            $data['ispayment'] = 1;
                            $data['ctime'] = $time;
                            $data['kehu_type'] = $kehuData['ktype'];
                            $data['money'] = 0;
                            
                            $pjkc = 'pjkc' . $orderSn;
                            //抵扣商品
                            $stockmethodModel = new StockmethodModel();
                            $pArr = json_decode($productArr, TRUE);
                            foreach ($pArr as $dVal) {
                                //存储配件
                                if (isset($dVal['is_zhekou']) && !empty($dVal['is_zhekou'])) {
                                    $pjparam['type'] = 2;
                                    $pjparam['documentsn'] = $pjkc;
                                    $pjparam['shipper_id'] = $shipper_id;
                                    $pjparam['time'] = $date;
                                    $pjparam['ctime'] = $time;
                                    $pdArr['shop_id'] = $pjparam['shop_id'] = $data['shop_id'];
                                    $pdArr['order_sn'] = '';
                                    $pdArr['goods_id'] = $pjparam['goods_type'] = $dVal['good_id'];
                                    $pdArr['goods_name'] = $pjparam['goods_name'] = $dVal['good_name'];
                                    $pdArr['goods_price'] = $dVal['good_price'];
                                    $pdArr['goods_num'] = $pjparam['goods_num'] = $dVal['good_num'];
                                    $pdArr['goods_type'] = $pjparam['gtype'] = 2;
                                    $pdArr['goods_kind'] = $pjparam['goods_propety'] = $dVal['good_kind'];
                                    $pdArr['pay_money'] = isset($dVal['zk_money']) ? $dVal['zk_money'] : $dVal['good_price'];
                                    $pdArr['good_zhekou'] = $dVal['is_zhekou'];
                                    $pdArr['goods_premium'] = $pdArr['pay_money'];
                                    $data['money'] += $pdArr['pay_money'];
                                    $pjArr[] = $pdArr;
                                    
                                    $status = $stockmethodModel->ShipperstationsStock($pjparam, $shipper_id, $pdArr['shop_id'], 0, 2);
                                }
                            }
                            $data['pay_money'] = $data['money'];
                            
                            $dataTrans = new DatatransferModel();
                            $returnData = $dataTrans->kehuOrder($data, '', $pjArr);
                        }
                        $this->app->respond(1, '充值成功');
                    } else {
                        $this->app->respond(-3, '充值失败');
                    }
                } else {
                    $this->app->respond(-3, '当前用户不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充方法 确认欠款订单
    protected function userqk($kid, $data, $order_sn, $kehuData = array()) {
        if (empty($kid) || empty($order_sn))
            return FALSE;

        $status = 0;
        //$kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
        if (!empty($kehuData)) {

            $param['order_sn'] = $order_sn;
            $param['kid'] = $kehuData['kid'];
            $param['time'] = time();
            $param['type'] = 1;

            $arreasData = array();
            $money = 0;
            if (isset($data['deposit_money']) && $data['deposit_money'] > 0) {
                $money += $data['deposit_money'];
                $param['money'] = $data['deposit_money'];
                $param['balance'] = $param['money'] + $kehuData['money'];
                $param['arrears_type'] = 2;
                if (isset($data['deposit_contractno']) && !empty($data['deposit_contractno']))
                    $param['contractno'] = $data['deposit_contractno'];

                $arreasData[] = $param;
            }
            if (isset($data['bottle_money']) && $data['bottle_money'] > 0) {
                $money += $data['bottle_money'];
                $param['money'] = $data['bottle_money'];
                $param['balance'] = $param['money'] + $kehuData['money'];
                $param['arrears_type'] = 1;
                if (isset($data['bottle_contractno']) && !empty($data['bottle_contractno']))
                    $param['contractno'] = $data['bottle_contractno'];

                $arreasData[] = $param;
            }
            if (isset($data['product_money']) && $data['product_money'] > 0) {
                $money += $data['product_money'];
                $param['money'] = $data['product_money'];
                $param['balance'] = $param['money'] + $kehuData['money'];
                $param['arrears_type'] = 3;
                if (isset($data['product_contractno']) && !empty($data['product_contractno']))
                    $param['contractno'] = $data['product_contractno'];

                $arreasData[] = $param;
            }
            $status = LibF::M('order_arrears')->uinsertAll($arreasData);
            LibF::M('kehu')->where(array('kid' => $param['kid']))->save(array('money' => $money + $kehuData['money']));
        }
        return $status;
    }

    //补充方法 新用户创建产生押金
    protected function createDeposit($order_sn, $kid, $shop_id, $money = 0, $deposit_data = array(), $receiptno = '') {
        if (empty($order_sn) || empty($kid) || empty($shop_id))
            return false;

        $depositVal = array();
        $depositArray = json_decode($deposit_data, true);

        $status = 0;
        if (!empty($depositArray)) {
            $time = time();
            $date = date('Y-m-d');
            foreach ($depositArray as $value) {
                $val['order_sn'] = $order_sn;
                $val['kid'] = $kid;
                $val['type'] = $value['type'];
                $val['number'] = $value['num'];
                if (!empty($receiptno))
                    $val['receiptno'] = $receiptno;
                $val['money'] = (isset($value['money']) && !empty($value['money'])) ? $value['money'] : 0;
                $val['price'] = (isset($value['price']) && !empty($value['price'])) ? $value['price'] : 0;
                $val['shop_id'] = $shop_id;
                $val['time'] = $date;
                $val['time_created'] = $time;

                $depositVal[] = $val;
            }
            LibF::M('deposit')->uinsertAll($depositVal);

            $incomeVal['order_sn'] = $order_sn;
            $incomeVal['kid'] = $kid;
            $incomeVal['money'] = $money;
            $incomeVal['shop_id'] = $shop_id;
            $incomeVal['time_created'] = $time;
            $status = LibF::M('deposit_income')->add($incomeVal);
        }

        return $status;
    }

    //补充方法 收欠款补录押金
    protected function addDeposit($kid, $money, $receiptno = '', $contractno, $shop_id = 0) {
        if (empty($kid) || empty($contractno) || empty($money))
            return false;

        $contractnoData = LibF::M('order_arrears')->where(array('contractno' => $contractno, 'type' => 1, 'is_return' => 0))->find(); //根据合同号获取相关信息

        $status = 0;
        $iData = array();
        if (!empty($contractnoData)) {
            $param = array();
            $param['order_sn'] = $contractnoData['order_sn'];
            $param['kid'] = $kid;

            if (!empty($receiptno))
                $param['receiptno'] = $receiptno; //押金条
            if (!empty($shop_id))
                $param['shop_id'] = $shop_id;

            $param['deposit_type'] = 2;
            $param['money'] = $money;
            $param['price'] = $money;
            $param['time'] = date('Y-m-d');
            $param['time_created'] = time();
            $status = LibF::M('deposit')->add($param);
        }

        return $status;
    }

    //补充方法 送气工领瓶记录
    protected function shipperBottleIn($shipper_id, $shop_id, $bottle, $bottleNum, $type = 1) {
        if (empty($shipper_id) || empty($shop_id) || empty($bottle))
            return FALSE;

        //获取订单号
        $app = new App();
        $orderSn = $app->build_order_no();

        $data['bottle_no'] = 'lp' . $orderSn;
        $data['shop_id'] = $shop_id;
        $data['shipper_id'] = $shipper_id;
        $data['time'] = date('Y-m-d');
        $data['status'] = 1;
        $data['time_created'] = time();
        $status = LibF::M('shipper_bottle')->add($data);
        if ($status) {
            $iVal = array();
            foreach ($bottle as $key => $value) {
                $val['bottle_no'] = $data['bottle_no'];
                $val['goods_kind'] = $value['type'];
                $val['goods_type'] = $type;
                $val['goods_num'] = $bottleNum[$key];
                $iVal[] = $val;
            }
            if (!empty($iVal))
                LibF::M('shipper_bottle_detial')->uinsertAll($iVal);
        }
        return $status;
    }

    //补充方法 送气工领瓶详情
    protected function shipperInventory($shipperArr, $bottleArr, $shipper_id = 0, $type = 1) {
        if (empty($shipperArr) || empty($bottleArr) || empty($shipper_id)) {
            return false;
        }

        $status = LibF::M('shipper_inventory')->uinsertAll($shipperArr);
        if (!empty($bottleArr)) {
            $xinpianList = implode(',', $bottleArr);
            if ($type == 1) {
                $udata['shipper_id'] = $shipper_id;
                $udata['shipper_time'] = time();

                $where['xinpian'] = $w['xinpian'] = array('in', $bottleArr);
                $where['property'] = 0;
                LibF::M('bottle_log')->where($where)->save($udata); //送气工扫重瓶，更新瓶子日志
                LibF::M('store_inventory')->where($w)->delete(); //更新门店库存瓶子到送气工
            } else {
                //空瓶更新基准表未空瓶
                $udata['status'] = 1;
                LibF::M('bottle')->where('xinpian IN (' . $xinpianList . ')')->save($udata); //送气工扫空瓶
            }
        }
        return $status;
    }

    //补充方法 送气工退空瓶入库
    protected function shipperDepositAdd($shipper_id, $bottle, $user_id, $bottleObject) {
        if (empty($shipper_id) || empty($bottle) || empty($user_id) || empty($bottleObject)) {
            return false;
        }

        $shipperArr = $bottleArr = array();
        foreach ($bottle as $value) {

            //存储送气工库存
            if (isset($bottleObject['xinpian'][$value])) {
                $shipper['number'] = $bottleObject['xinpian'][$value]['number'];
                $shipper['xinpian'] = $value;
                $shipper['bar_code'] = $bottleObject['xinpian'][$value]['number'];
                $shipper['type'] = $bottleObject['xinpian'][$value]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = 0;
                $shipper['status'] = 1;
                $shipper['is_use'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;
            } else if (isset($bottleObject['number'][$value])) {
                $shipper['number'] = $value;
                $shipper['xinpian'] = $bottleObject['number'][$value]['xinpian'];
                $shipper['bar_code'] = $bottleObject['number'][$value]['number'];
                $shipper['type'] = $bottleObject['number'][$value]['type'];
                $shipper['shipper_id'] = $shipper_id;
                $shipper['is_open'] = 0;
                $shipper['status'] = 1;
                $shipper['is_use'] = 1;
                $shipper['time_created'] = time();
                $shipperArr[] = $shipper;
            }
        }

        $status = 0;
        if (!empty($shipperArr))
            $status = LibF::M('shipper_inventory')->uinsertAll($shipperArr);
        return $status;
    }
    
    //补充方法 送气工空重瓶库存更新
    protected function shipperIncome($shipper_id, $shop_id, $kid, $pData, $orderzjData, $order_sn = '') {
        if (empty($pData['zp']) || empty($shipper_id) || empty($shop_id) || empty($kid))
            return FALSE;
        
        $dataModel = new DataInventoryModel();
        $rData = $dataModel->shipperck($shop_id, $shipper_id, 0, $pData['zp'], 2, 2);  //送气工重瓶出库
        if (!empty($pData['kp'])) {
            $dataModel->shipperck($shop_id, $shipper_id, 0, $pData['kp'], 1, 1);  //送气工空瓶入库
            //删除空瓶
            $condition['xinpian'] = array('in', $pData['kp']);
            $condition['number'] = array('in', $pData['kp']);
            $condition['_logic'] = 'OR';
            if (!empty($condition))
                LibF::M('kehu_inventory')->where($condition)->delete();
        }

        if (!empty($pData['zj'])) {
            $dataModel->zjshipperck($shop_id, $shipper_id, 0, $pData['zj'], 1, 3);  //送气工折旧瓶入库
            if (!empty($orderzjData)) {
                $orderzjData['shop_id'] = $shop_id;
                LibF::M('order_depreciation')->add($orderzjData);
            }
        }

        $bottleDataArr = isset($rData['bottleData']) ? $rData['bottleData'] : array();

        $dataTransModel = new DatatransferModel();
        $dataTransModel->kehuInventory($pData, $shipper_id, $kid, $bottleDataArr, $order_sn);  //钢瓶详情记录 is_open 0空瓶 1重瓶 2待修瓶
        return $rData;
    }
    
    //补充方法 送气工空重待修库存更新$bottle_type 1 转成待修瓶 2 转成正常瓶
    protected function shipperIncomeInfo($shipper_id, $shop_id, $kid, $pData, $bottle_type = 1) {
        if (empty($shipper_id) || empty($shop_id) || empty($kid))
            return FALSE;

        $kpData = (isset($pData['kp']) && !empty($pData['kp'])) ? json_decode($pData['kp'], true) : ''; //空瓶
        $bottleData['kp'] = $kpData;
        
        $bottleData['zp'] = (isset($pData['zp']) && !empty($pData['zp'])) ? json_decode($pData['zp'], true) : ''; //重瓶
        
        $rData = array();
        $dataModel = new DataInventoryModel();
        if (!empty($bottleData['kp'])) {
            $rData = $dataModel->shipperck($shop_id, $shipper_id, 0, $bottleData['kp'], 1, 1);  //送气工空瓶入库
            //删除空瓶
            $condition['xinpian'] = array('in', $bottleData['kp']);
            $condition['number'] = array('in', $bottleData['kp']);
            $condition['_logic'] = 'OR';
            if (!empty($condition))
                LibF::M('kehu_inventory')->where($condition)->delete();
        }
        
        if (!empty($pData['fp'])) {
            if ($bottle_type == 1) {
                $bottleData['fp'] = (isset($pData['fp']) && !empty($pData['fp'])) ? json_decode($pData['fp'], true) : ''; //待修瓶
                $dataModel->shipperck($shop_id, $shipper_id, 0, $bottleData['fp'], 1, 4);  //送气工待修瓶入库
            } else {
                $bottleData['uzp'] = (isset($pData['fp']) && !empty($pData['fp'])) ? json_decode($pData['fp'], true) : ''; //用户手中置换重瓶
                $dataModel->shipperck($shop_id, $shipper_id, 0, $bottleData['uzp'], 1, 2);  //送气工重瓶入库
            }
            if (!empty($bottleData['zp'])) {
                $rData = $dataModel->shipperck($shop_id, $shipper_id, 0, $bottleData['zp'], 2, 2);  //送气工重瓶出库
            }
        }

        if (!empty($rData['bottleData'])) {
            $dataTransModel = new DatatransferModel();
            $dataTransModel->kehuInventory($bottleData, $shipper_id, $kid, $rData['bottleData']);  //钢瓶详情记录
        }
        return $rData;
    }
    
    //补充方法 送气工相关收入
    protected function shipperPayListAdd($shipper_id, $shipper_name = '', $shipper_mobile = '', $shop_id, $order_sn, $is_cash, $param = array()) {

        if (empty($shipper_id) || empty($shop_id) || empty($order_sn))
            return FALSE;

        $status = LibF::M('shipper')->where(array('shipper_id' => $shipper_id))->setInc('order_no'); //更新送气工订单数量
        if (($is_cash == 0) && ($param['pay_money'] > 0)) {
            LibF::M('shipper')->where(array('shipper_id' => $shipper_id))->setInc('money', $param['pay_money']); //zt：收款后送气工余额增加   
        }

        $financeData = array();
        $payInsert['paylist_no'] = $order_sn;
        $payInsert['shipper_id'] = $shipper_id;
        if (!empty($shipper_name))
            $payInsert['shipper_name'] = $shipper_name;
        if (!empty($shipper_mobile))
            $payInsert['mobile_phone'] = $shipper_mobile;
        
        $payInsert['money'] = $param['pay_money'];
        $payInsert['shop_id'] = $shop_id;
        $payInsert['time'] = $param['date'];
        $payInsert['time_created'] = $param['should_time'];
        $orderFinance = $depositFinance = $depreciationFinance = $raffinatFinance = $payInsert;
        if ($param['deposit'] > 0 || $param['raffinat'] || $param['depreciation']) {
            $orderFinance['type'] = 1;
            $orderFinance['is_statistics'] = 1;
            $orderFinance['status'] = 1;
            $financeData[] = $orderFinance;
            if ($param['deposit']) {
                $depositFinance['type'] = 4;
                $depositFinance['is_statistics'] = 0;
                $depositFinance['status'] = 1;
                $depositFinance['money'] = $param['deposit'];
                $financeData[] = $depositFinance;
            }
            if ($param['depreciation']) {
                $depreciationFinance['type'] = 5;
                $depreciationFinance['is_statistics'] = 0;
                $depreciationFinance['status'] = 2;
                $depreciationFinance['money'] = $param['depreciation'];
                $financeData[] = $depreciationFinance;
            }
            if ($param['raffinat']) {
                $raffinatFinance['type'] = 6;
                $raffinatFinance['is_statistics'] = 0;
                $raffinatFinance['status'] = 2;
                $depreciationFinance['money'] = $param['raffinat'];
                $financeData[] = $raffinatFinance;
            }
            LibF::M('shipper_paylist')->uinsertAll($financeData);
        } else {
            $payInsert['type'] = 1;
            $payInsert['is_statistics'] = 1;
            LibF::M('shipper_paylist')->add($payInsert);
        }
        
        return $status;
    }

    //补充方法 订单更新价格
    protected function shipperOrderInfo($order_sn, $order_info) {
        if (empty($order_sn) || empty($order_info))
            return FALSE;

        $status = 0;
        $orderData = json_decode($order_info, true);
        if (!empty($orderData)) {
            $status = 1;

            $where['order_sn'] = $w['order_sn'] = $order_sn;
            $where['goods_type'] = 1;
            foreach ($orderData as $value) {
                $where['goods_kind'] = $value['type'];
                $val['goods_premium'] = $value['price'];
                $val['pay_money'] = $value['num'] * $value['price'];
                LibF::M('order_info')->where($where)->save($val);
            }

            $orderInfo = LibF::M('order_info')->where($w)->select();
            $money = 0;
            if (!empty($orderInfo)) {
                foreach ($orderInfo as $oVal) {
                    $money += $oVal['pay_money'];
                }
                $uVal['money'] = $money;
                $status = LibF::M('order')->where($w)->save($uVal);
            }
        }
        return $status;
    }

}