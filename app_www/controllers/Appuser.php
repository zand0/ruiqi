<?php

/**
 * @app客户端
 */
class AppuserController extends \Com\Controller\Common {

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
    
    protected $pageSize;
    
    /**
     * 地址
     * @var $url
     */
    protected $url;

    /**
     * 初始化
     */
    public function init() {

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
        $this->pageSize = 15;
        
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
                $kehuModel = new KehuModel();
                $show_password = $kehuModel->encryptUserPwd($password);

                $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                if ($parent_record === null) {
                    $this->app->respond(-1, '此账号不存在');
                } else if ($parent_record['password'] !== $show_password) {
                    $this->app->respond(-2, '密码错误');
                } else {
                    //判断当前验证吗是否正确
                    $status = $parent_record['status'];
                    if ($status == 0) {
                        $token = 123456;

                        $time = ($parent_record['ctime']>0) ? date("Y-m-d", $parent_record['ctime']) : date('Y-m-d');
                        $data = array(
                            'mobile'    => $mobile,
                            'username'  => $parent_record['user_name'],
                            'kid'       => $parent_record['kid'],
                            'shop_id'   => $parent_record['shop_id'],
                            'user_type' => $parent_record['ktype'],
                            'paytype'   => $parent_record['paytype'],
                            'arrearage' => $parent_record['arrearage'],
                            'money'     => $parent_record['money'],
                            'point'     => $parent_record['point'],
                            'balance'   => $parent_record['balance'],
                            'bottle_data' => !empty($parent_record['bottle_data']) ? json_decode($parent_record['bottle_data']) : '',
                            'time'      => $time,
                            'token'     => $token);
                        $this->app->respond(1, $data);
                    } else {
                        $this->app->respond(-2, '当前账号没有被激活');
                    }
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 获取当前用户信息
     */
    public function managementAction() {
        if ($this->getRequest()->isPost()) {
            
            $kid = $this->getRequest()->getPost('kid');
            if ($kid) {
                $parent_record = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if ($parent_record === null) {
                    $this->app->respond(-1, '此账号不存在');
                } else {
                    $token = 123456;

                    $time = ($parent_record['ctime'] > 0) ? date("Y-m-d", $parent_record['ctime']) : date('Y-m-d');
                    $data = array(
                        'mobile' => $parent_record['mobile_phone'],
                        'username' => $parent_record['user_name'],
                        'kid' => $parent_record['kid'],
                        'shop_id' => $parent_record['shop_id'],
                        'user_type' => $parent_record['ktype'],
                        'paytype' => $parent_record['paytype'],
                        'arrearage' => $parent_record['arrearage'],
                        'money' => $parent_record['money'],
                        'point' => $parent_record['point'],
                        'balance' => $parent_record['balance'],
                        'time' => $time,
                        'token' => $token);
                    $this->app->respond(1, $data);
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
                $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
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
     * app用户注册(快速注册)
     * 
     * @param $mobile
     * @param $password
     * @param $code
     * @param confirm_password
     */
    public function registerAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $username = $this->getRequest()->getPost('username');
            $captcha = $this->getRequest()->getPost('captcha');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $password = $this->getRequest()->getPost('password');
            $confirm_password = $this->getRequest()->getPost('confirm_password');
            $user_type = $this->getRequest()->getPost('user_type', 1);

            //相关base64相关解码
            if (!empty($password) && !empty($captcha) && $password == $confirm_password) {
                $password = $this->app->getPassword($password);
                if ($mobile) {
                    $parent_record = LibF::M('verification_code')->where(array('mobile' => $mobile, 'code' => $captcha, 'status' => 0))->find();
                    if (!empty($parent_record)) {

                        $kehuModel = new KehuModel();
                        $show_password = $kehuModel->encryptUserPwd($password);

                        $app = new App();
                        $orderSn = $app->build_order_no();
                        $data['kehu_sn'] = 'kh'.$orderSn;
                        
                        $data['mobile_phone'] = $mobile;
                        $data['user_name'] = $username;
                        $data['mobile_list'] = $mobile;
                        $data['password'] = $show_password;
                        if($shop_id > 0)
                            $data['shop_id'] = $shop_id;
                        $data['ktype'] = $user_type;

                        $kehuData = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                        if (!empty($kehuData)) {
                            $this->app->respond(-2, '当前手机号已经存在');
                        } else {
                            $data['ctime'] = time();
                            $type = LibF::M('kehu')->add($data);
                            if ($type) {
                                $udata['status'] = 1;
                                $status = LibF::M('verification_code')->where(array('mobile' => $mobile))->save($udata);

                                $token = '123456';
                                $this->app->respond(1, array('mobile' => $mobile, 'kid' => $type, 'token' => $token));
                            } else {
                                $this->app->respond(-2, '注册失败');
                            }
                        }
                    } else {
                        $this->app->respond(-1, '验证码不存在');
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
     * 更新用户基础信息
     * 
     */
    public function updateinfoAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $shop_id = $this->getRequest()->getPost('shop_id'); //门店
            $paytype = $this->getRequest()->getPost('type', ''); //1个人用户2商铺用户
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {

                $param['shop_id'] = $shop_id;
                $param['ktype'] = $paytype;
                $status = LibF::M('kehu')->where(array('kid' => $kid))->save($param);
                if ($status) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '更新地址失败');
                }
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

                if ($type == 1) {//注册时候发验证码
                    $userData = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                    if (empty($userData)) {
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
                        $this->app->respond(-3, '此手机号已经被注册');
                    }
                } else { //密码重置发送验证码
                    $userData = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
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
                }
            } else {
                $this->app->respond(-5, '未提交参数');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app用户注册（添加详情）
     * 
     * @param $username
     * @param $kid
     */
    public function registerdetailsAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');
            $username = $this->getRequest()->getPost('username');
            $sheng = $this->getRequest()->getPost('sheng', 0); //省
            $shi = $this->getRequest()->getPost('shi', 0); //市
            $qu = $this->getRequest()->getPost('qu', 0); //区
            $cun = $this->getRequest()->getPost('cun',0); //村
            $address = $this->getRequest()->getPost('address', ''); //地址
            $receiver = $this->getRequest()->getPost('receiver');
            $receiver_tel = $this->getRequest()->getPost('receiver_tel');
            if (!empty($kid) && !empty($username)) {
                $data['user_name'] = $username;
                $data['sheng'] = $sheng;
                $data['shi'] = $shi;
                $data['qu'] = $qu;
                $data['cun'] = $cun;
                $data['address'] = $address;
                $status = LibF::M('kehu')->where(array('kid' => $kid))->save($data);
                if ($status) {

                    $param['kid'] = $kid;
                    $param['sheng'] = $sheng;
                    $param['shi'] = $shi;
                    $param['qu'] = $qu;
                    $param['cun'] = $cun;
                    $param['address'] = $address;
                    $param['receiver'] = $receiver;
                    $param['receiver_tel'] = $receiver_tel;
                    $param['isdefault'] = 1;
                    LibF::M('kehu_address')->add($param);

                    $this->app->respond(1, '注册成功');
                } else {
                    $this->app->respond(-3, '注册失败');
                }
            } else {
                $this->app->respond(-5, '未提交参数');
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
                        $kehuModel = new KehuModel();
                        $show_password = $kehuModel->encryptUserPwd($password);

                        $data['password'] = $show_password;
                        $status = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->save($data);
                        if ($status) {
                            $udata['status'] = 1;
                            $status = LibF::M('verification_code')->where(array('mobile' => $mobile))->save($udata);
                            $this->app->respond(1, '更新成功');
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

                $kehuModel = new KehuModel();
                $ypassword = $kehuModel->encryptUserPwd($old_password);
                if ($mobile && $token) {
                    $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['password'] != $ypassword) {
                        $this->app->respond(-4, '原始密码不正确');
                    } else {
                        $show_password = $kehuModel->encryptUserPwd($password);

                        $data['password'] = $show_password;
                        $status = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->save($data);
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
                        $status = LibF::M('kehu')->where(array('mobile_phone' => $old_mobile))->save($data);
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
     * 首页显示的当前最新订单信息
     * 
     * @param $mobile
     * @param $token
     */
    public function latestorderAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {

                $data = array();
                $orderModel = new OrderModel();
                $orderData = $orderModel->getOrderInfo(array('kid' => $kid));
                if (!empty($orderData)) {
                    $regionModel = new RegionModel();
                    $regionObject = $regionModel->getRegionObject();
                    
                    //备注 当前最新的订单信息
                    $orderInfoData = $orderModel->getOrderList(array('order_sn' => $orderData['order_sn']));
                    $type = array();
                    if (!empty($orderInfoData)) {
                        //获取当前钢瓶规格
                        $bottleTypeModel = new BottletypeModel();
                        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                        //获取配件规格
                        $productTypeModel = new ProducttypeModel();
                        $productTypeData = $productTypeModel->getProductTypeArray();
                        foreach ($orderInfoData as $value) {
                            $oInfo['title'] = $value['goods_name'];
                            $oInfo['goods_price'] = $value['goods_price'];
                            if ($value['goods_type'] == 1) {
                                $oInfo['goods_kind'] = $bottleTypeData[$value['goods_kind']]['bottle_name'];
                            } else {
                                $oInfo['goods_kind'] = $productTypeData[$value['goods_kind']]['name'];
                            }
                            $oInfo['num'] = $value['goods_num'];
                            $type[] = $oInfo;
                        }
                    }
                    $data = array(
                        'ordersn' => $orderData['order_sn'],
                        'status' => $orderData['status'],
                        'delivery' => date('Y-m-d', $orderData['good_time']),
                        'address' => $orderData['address'],
                        'username' => $orderData['username'],
                        'mobile'    => $orderData['mobile'],
                        'type' => $type,
                        'total' => $orderData['money'],
                        'pay_money' => $orderData['pay_money'],
                        'depreciation' => $orderData['is_old'], //是否有折旧 1有 0无
                        'workersname' => $orderData['shipper_name'],
                        'workersmobile' => !empty($orderData['shipper_mobile']) ? $orderData['shipper_mobile'] : '',
                        'shipment' => $orderData['shipment'], //运费
                        'raffinat' => $orderData['raffinat'], //残液
                        'deposit'  => $orderData['deposit'],//押金
                        'shop_id'  => $orderData['shop_id'],//门店id
                        'shop_name' => $orderData['shop_name'],//门店名称
                        'product_pice' => $orderData['product_pice'],//配件
                        'depreciation' => $orderData['depreciation'], //折旧
                        'sheng' => $orderData['sheng'],
                        'shi'   => $orderData['shi'],
                        'qu'    => $orderData['qu'],
                        'cun'   => $orderData['cun'],
                        'sheng_name' => isset($regionObject[$orderData['sheng']]) ? $regionObject[$orderData['sheng']]['region_name'] : '',
                        'shi_name' => isset($regionObject[$orderData['shi']]) ? $regionObject[$orderData['shi']]['region_name'] : '',
                        'qu_name' => isset($regionObject[$orderData['qu']]) ? $regionObject[$orderData['qu']]['region_name'] : '',
                        'cun_name' => isset($regionObject[$orderData['cun']]) ? $regionObject[$orderData['cun']]['region_name'] : ''
                    );
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
     * 首页订单统计
     * 
     * @param $mobile 
     * @param $token
     */
    public function orderstatisticsAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                //备注 需要根据需求展示
                $orderModel = new OrderModel();
                $total = $orderModel->getOrderTotal(array('kid' => $kid));
                $sum = $orderModel->getOrderSum(array('kid' => $kid));
                $ciel = ($sum > 0) ? ceil($sum / $total) : 0;

                $data = array(
                    0 => array(
                        'title' => '订气总次数',
                        'integral' => $total
                    ),
                    1 => array(
                        'title' => '订气总金额',
                        'integral' => $sum
                    ),
                    2 => array(
                        'title' => '平均每次金额',
                        'integral' => $ciel
                    ),
                    3 => array(
                        'title' => '用气天数',
                        'integral' => 0
                    )
                );

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 订单列表展示
     * 
     * @param $mobile 
     * @param $token
     */
    public function orderlistAction() {

        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');
            $begintime = $this->getRequest()->getPost('begintime');
            $endtime = $this->getRequest()->getPost('endtime');
            $type = $this->getRequest()->getPost('type');
            $status_type = $this->getRequest()->getPost('status');
            $token = $this->getRequest()->getPost('token', 1234);
            
            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                /**
                 * 备注:
                 * 只有收货的状态下 才会产生是否付款、是否评价、是否安全报告
                 * 所以 当status 状态是 4 的情况下 才会有其它状态
                 * 
                 * 基于订单安全等级先判定 如果 status 状态是 4 第一步判断是否存在安全报告如果有显示安全报告状态结束，如果没有判定是否有评价状态结束
                 */
                $status = array(
                    -1 => '已关闭', 1 => '未派发', 2 => '配送中', 3 => '', 4 => '已送达', 5 => '问题订单'
                );
                
                if ($type == 1) {
                    $status_type = 2;
                } else if ($type == 2) {
                    $status_type = 4;
                } else {
                    $where['status'] = array('neq', -1);
                }

                $where = array('kid' => $kid, 'start_time' => $begintime, 'end_time' => $endtime, 'status' => $status_type,'page' => $page,'pagesize' => $this->pageSize);
                
                //获取当前钢瓶规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                //获取配件规格
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();
                
                $orderModel = new OrderModel();
                $data = $orderModel->orderListApp($where,'',$bottleTypeData,$productTypeData);
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

    /**
     * 用户退押金列表
     * 
     * @param $mobile
     * @param $token
     */
    public function depositAction() {

        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid');
            $type = $this->getRequest()->getPost('type', 1); //1已完成0未完成
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                $status = array(
                    0 => '未派发', 1 => '未完成', 2 => '已完成'
                );

                $pageStar = ($page - 1) * $this->pageSize;

                $where['kid'] = $kid;
                $data = LibF::M('deposit_list')->where($where)->order('id desc')->limit($pageStar, $this->pageSize)->select();
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
                        $value['data'] = (!empty($value['bottle'])) ? json_decode($value['bottle'], true) : array();
                        $value['bottle_text'] = (!empty($value['bottle_text'])) ? json_decode($value['bottle_text'], true) : array();
                        $value['change_data'] = (!empty($value['change_data'])) ? json_decode($value['change_data'], true) : array();
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
     * 用户取消订单
     * 
     * @param $mobile
     * @param $token
     */
    public function cancelorderAction() {

        if ($this->getRequest()->isPost()) {
            $ordersn = $this->getRequest()->getPost('ordersn');
            $isgoods = $this->getRequest()->getPost('isgoods');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($ordersn) && !empty($token)) {
                $data = array('isgoods' => $isgoods);
                $status = LibF::M('order')->where(array('order_sn' => $ordersn))->save($data);
                if ($status) {
                    $this->app->respond(1, '更新成功');
                } else {
                    $this->app->respond(-1, '更新失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户创建订单
     * 
     * @param $kid
     * @param $mobile
     * @param $shop_id
     * @param $address
     * @param $money
     * 
     * @param $data需要的物品
     */
    public function createorderAction() {

        if ($this->getRequest()->isPost()) {

            $param['kid'] = $this->getRequest()->getPost('kid');
            $param['shop_id'] = $this->getRequest()->getPost('shop_id', 0);

            $param['urgent'] = $this->getRequest()->getPost('urgent', 0); //是否紧急    
            $param['isold'] = $this->getRequest()->getPost('isold', 0); //是否折旧
            $param['ismore'] = $this->getRequest()->getPost('ismore', 0); //是否余气
            $param['status'] = $this->getRequest()->getPost('status', 1); //6欠款申请订单
            $param['is_settlement'] = $this->getRequest()->getPost('is_settlement', 1); //1必须结算2可以欠款

            $param['address_id'] = $this->getRequest()->getPost('address_id',0); //地址id
            $param['sheng'] = $this->getRequest()->getPost('sheng');
            $param['shi'] = $this->getRequest()->getPost('shi');
            $param['qu'] = $this->getRequest()->getPost('qu');
            $param['cun'] = $this->getRequest()->getPost('cun');
            $param['address'] = $this->getRequest()->getPost('address','');  //配送地址
            
            $param['goodtime'] = $this->getRequest()->getPost('goodtime', date('Y-m-d')); //希望配送时间
            $param['goodtime'] = (!empty($param['goodtime'])) ? strtotime($param['goodtime']) : time();

            $param['shipment'] = $this->getRequest()->getPost('deliveryfee', 0); //送气费用
            $param['username'] = $this->getRequest()->getPost('name'); //收货人
            $param['mobile'] = $this->getRequest()->getPost('phone'); //收货人联系方式

            $param['is_discount'] = $this->getRequest()->getPost('is_discount', 0); //是否使用积分
            $param['discountmoney'] = $this->getRequest()->getPost('discountmonet'); //优惠价格
            
            $param['money'] = $this->getRequest()->getPost('money'); //订单金额
            $param['shouldmoney'] = $this->getRequest()->getPost('shouldmoney', 0); //应付款

            $param['is_point'] = $this->getRequest()->getPost('is_point',0); //是否使用积分
            
            $youhuijson = $this->getRequest()->getPost('youhuijson'); //优惠券id(余额券)
            if (!empty($youhuijson))
                $param['youhui_data'] = $youhuijson;
            $youhuimoney = $this->getRequest()->getPost('yh_money'); //优惠券金额(余额券)
            if (!empty($youhuimoney))
                $param['youhui_money'] = $youhuimoney;

            $param['order_tc_type'] = $this->getRequest()->getPost('tc_type',0); //套餐类型0正常订单4体验套餐5优惠套餐
            
            $data = $this->getRequest()->getPost('data'); //详情串
            if (!empty($data))
                $data = json_decode($data, true);

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($param['kid']) && !empty($data) && !empty($token)) {
                $dataTrans = new DatatransferModel();
                $returnData = $dataTrans->kehuOrder($param, $data);
                if ($returnData['status'] == 200) {
                    
                    if (!empty($youhuijson)) {  //优惠券
                        $yhData = json_decode($youhuijson, TRUE);
                        if (!empty($yhData)) {
                            $uData['status'] = 1;
                            $uWhere['id'] = array('in', $yhData);
                            LibF::M('promotions_user')->where($uWhere)->save($uData);
                        }
                    }

                    if ($param['is_point'] == 1) {  //是否有积分
                        $kehuData = LibF::M('kehu')->where(array('kid' => $param['kid']))->find();
                        if (!empty($kehuData)) {

                            $p['kid'] = $kehuData['kid'];
                            $p['time'] = time();
                            $p['type'] = 2;
                            $p['point'] = $this->getRequest()->getPost('point');
                            if ($p['point'] > 0) {
                                $p['balance'] = $kehuData['point'] - $p['point'];
                                $status = LibF::M('point_list')->add($p);
                                if ($status) {
                                    LibF::M('kehu')->where(array('kid' => $kehuData['kid']))->save(array('point' => $p['balance']));
                                }
                            }
                        }
                    }

                    //$smsdataModel = new SmsDataModel();
                    //$smsdataModel->sendsms($param['username'], $returnData['msg'], $param['mobile']);
                    $rData['order_sn'] = $returnData['msg'];
                    
                    $this->app->respond(1, $rData);
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
     * 用户投诉
     * 
     * @param $id
     * @param $kid
     * @param $kehu_name
     * @param $comment 
     * 
     */
    public function securityAction() {
        if ($this->getRequest()->isPost()) {
            $param['shop_id'] = $this->getRequest()->getPost('shop_id'); //门店
            $param['kehu_id'] = $this->getRequest()->getPost('kid'); //客户id
            $param['kehu_name'] = $this->getRequest()->getPost('kehu_name', ''); //客户名称
            $param['comment'] = $this->getRequest()->getPost('comment', ''); //备注

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($param) && !empty($token)) {

                $data['shop_id'] = $param['shop_id'];
                $data['kid'] = $param['kehu_id'];
                $data['kname'] = $param['kehu_name'];
                $data['comment'] = $param['comment'];
                $data['ctime'] = time();

                $app = new App();
                $orderSn = $app->build_order_no();
                $data['encode_id'] = 'ts' . $orderSn;
                $status = LibF::M('tousu')->add($data);
                if ($status) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '当前用户未登录');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    public function tousulistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $where['rq_tousu.kid'] = $kid;

                $tousuModel = new Model('tousu');
                $tousuData = $tousuModel->join('LEFT JOIN rq_shop ON rq_tousu.shop_id = rq_shop.shop_id')->field('rq_tousu.encode_id,rq_tousu.kname,rq_tousu.shop_id,rq_shop.shop_name,rq_tousu.comment,rq_tousu.treatment,rq_tousu.admin_user_name,rq_tousu.ctime')->where($where)->order('rq_tousu.ctime desc')->select();
                if ($tousuData) {
                    foreach ($tousuData as &$value) {
                        $value['time'] = date("Y-m-d", $value['ctime']);
                    }
                }else{
                    $tousuData = array();
                }

                $this->app->respond(1, $tousuData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户报修
     * 
     * @param $id
     * @param $kid
     * @param $kehu_name
     * @param $comment
     * 
     */
    public function repairAction() {
        if ($this->getRequest()->isPost()) {
            //$param['id'] = $this->getRequest()->getPost('id'); //安检id
            $param['shop_id'] = $this->getRequest()->getPost('shop_id'); //安检id
            $param['kid'] = $this->getRequest()->getPost('kid'); //客户id
            $param['kname'] = $this->getRequest()->getPost('kehu_name', ''); //客户名称
            $param['comment'] = $this->getRequest()->getPost('comment', ''); //安检备注

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($param) && !empty($token)) {
                $param['ctime'] = time();

                $app = new App();
                $orderSn = $app->build_order_no();
                $param['encode_id'] = 'bx' . $orderSn;
                $status = LibF::M('baoxiu')->add($param);
                if ($status) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '当前用户未登录');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    public function baoxiulistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $where['rq_baoxiu.kid'] = $kid;

                $baoxiuModel = new Model('baoxiu');
                $baoxiuData = $baoxiuModel->join('LEFT JOIN rq_shop ON rq_baoxiu.shop_id = rq_shop.shop_id')->field('rq_baoxiu.encode_id,rq_baoxiu.kname,rq_baoxiu.shop_id,rq_shop.shop_name,rq_baoxiu.comment,rq_baoxiu.treatment,rq_baoxiu.admin_user_name,rq_baoxiu.ctime')->where($where)->order('rq_baoxiu.ctime desc')->select();
                if (!empty($baoxiuData)) {
                    foreach ($baoxiuData as &$value) {
                        $value['time'] = date("Y-m-d", $value['ctime']);
                    }
                }else{
                    $baoxiuData = array();
                }
                $this->app->respond(1, $baoxiuData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 确认付款
     */
    public function confirmorderAction() {

        if ($this->getRequest()->isPost()) {
            $where['kid'] = $this->getRequest()->getPost('kid');
            $where['order_sn'] = $this->getRequest()->getPost('ordersn');
            $param['pay_money'] = $this->getRequest()->getPost('pay_money');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($param) && !empty($token)) {

                $param['is_balance'] = $this->getRequest()->getPost('is_balance');
                $param['balance'] = $this->getRequest()->getPost('balance');
                $param['order_type'] = $this->getRequest()->getPost('type');  //1余额 2支付宝 3微信

                $param['ispayment'] = 1;
                $status = LibF::M('order')->where($where)->save($param);
                if ($status) {
                    
                    if ($param['order_type'] == 1) {
                        $kehuData = LibF::M('kehu')->where(array('kid' => $where['kid']))->find();
                        //balance 账户余额  money 欠款金额y
                        //if 送气工确认是用户是欠款  
                        $app = new App();
                        $orderSn = $app->build_order_no();
                        $b['balance_sn'] = $orderSn;
                        $b['kid'] = $kehuData['kid'];
                        $b['time'] = $this->getRequest()->getPost('time', time());
                        $b['money'] = $param['balance'];
                        $b['balance'] = $kehuData['balance'] - $param['balance'];
                        $b['type'] = 2;
                        $status = LibF::M('balance_list')->add($b);
                        if ($status) {
                            LibF::M('kehu')->where(array('kid' => $where['kid']))->save(array('balance' => $b['balance']));
                        }
                    }
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '当前用户未登录');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 订单评价
     * 
     */
    public function evaluationAction() {
        if ($this->getRequest()->isPost()) {
            $where['order_sn'] = $this->getRequest()->getPost('ordersn');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($where['order_sn']) && !empty($token)) {

                $param['is_evaluation'] = 1;
                $status = LibF::M('order')->where($where)->save($param);

                $pj['order_sn'] = $where['order_sn'];
                $pj['kehu_id'] = $this->getRequest()->getPost('kid');
                $pj['type'] = $this->getRequest()->getPost('type');
                if (!empty($pj['type'])) {
                    $typeArr = json_decode($pj['type']);
                    $numtotal = 0;
                    foreach ($typeArr as $tVal) {
                        $numtotal += $tVal;
                    }
                    $ntotal = count($typeArr);
                    $pj['num'] = ($ntotal > 0) ? sprintf("%.1f", $numtotal / $ntotal) : 0;
                }

                $pj['comment'] = $this->getRequest()->getPost('comment');

                $status = LibF::M('evaluation')->add($pj);

                if ($status) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '当前用户未登录');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 评价内容
     */
    public function getevaluationAction() {
        if ($this->getRequest()->isPost()) {
            $order_sn = $this->getRequest()->getPost('order_sn');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($order_sn) && !empty($token)) {

                $where['order_sn'] = $order_sn;
                $data = LibF::M('evaluation')->field('num,comment')->where($where)->find();
                if(empty($data)){
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

    /**
     * 获取用户地址列表
     * 
     */
    public function addresslistAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $kid = $this->getRequest()->getPost('kid');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {

                $data = LibF::M('kehu_address')->where(array('kid' => $kid, 'status' => 1))->order('isdefault ASC')->select();
                if(!empty($data)){
                    $regionModel = new RegionModel();
                    $regionObject = $regionModel->getRegionObject();
                    foreach ($data as &$value){
                        $value['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                        $value['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                        $value['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                        $value['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                    }
                }else{
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

    /**
     * 用户增加新地址
     * 
     */
    public function addaddressAction() {
        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getPost('id'); //地址id
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $sheng = $this->getRequest()->getPost('sheng', ''); //省
            $shi = $this->getRequest()->getPost('shi', ''); //市
            $qu = $this->getRequest()->getPost('qu'); //区
            $cun = $this->getRequest()->getPost('cun'); //村
            $address = $this->getRequest()->getPost('address'); //地址
            $receiver = $this->getRequest()->getPost('receiver');
            $receiver_tel = $this->getRequest()->getPost('receiver_tel');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {

                $param['kid'] = $kid;
                $param['sheng'] = $sheng;
                $param['shi'] = $shi;
                $param['qu'] = $qu;
                $param['cun'] = $cun;
                $param['address'] = $address;
                $param['receiver'] = $receiver;
                $param['receiver_tel'] = $receiver_tel;
                if ($id > 0) {
                    $status = LibF::M('kehu_address')->where(array('id' => $id))->save($param);
                } else {
                    $status = LibF::M('kehu_address')->add($param);
                }
                if ($status) {
                    $this->app->respond(1, '创建成功');
                } else {
                    $this->app->respond(-3, '更新地址失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户删除地址
     * 
     */
    public function deladdressAction() {
        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getPost('id'); //地址id
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $status = 0;
                if ($id > 0) {
                    $param['status'] = -1;
                    $status = LibF::M('kehu_address')->where(array('id' => $id))->save($param);
                }
                if ($status) {
                    $this->app->respond(1, '删除成功');
                } else {
                    $this->app->respond(-3, '删除失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户设定默认地址
     * 
     */
    public function isdefaultaddressAction() {

        if ($this->getRequest()->isPost()) {
            $id = $this->getRequest()->getPost('id'); //地址id
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $status = 0;
                if ($id > 0) {
                    LibF::M('kehu_address')->where(array('kid' => $kid))->save(array('isdefault' => 2));

                    $param['isdefault'] = 1;
                    $status = LibF::M('kehu_address')->where(array('id' => $id))->save($param);
                }
                if ($status) {
                    $this->app->respond(1, '更新成功');
                } else {
                    $this->app->respond(-3, '更新失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户申请退押金
     * 
     */
    public function sqdepositAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');

            $kid = $this->getRequest()->getPost('kid');
            $username = $this->getRequest()->getPost('name');
            $mobile = $this->getRequest()->getPost('phone');

            $sheng = $this->getRequest()->getPost('sheng');
            $shi = $this->getRequest()->getPost('shi');
            $qu = $this->getRequest()->getPost('qu');
            $cun = $this->getRequest()->getPost('cun');
            $address = $this->getRequest()->getPost('address');

            $goodtime = $this->getRequest()->getPost('goodtime', date('Y-m-d H:i'));
            
            $comment = $this->getRequest()->getPost('comment',''); //备注

            $time = time();
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($kid) && !empty($token)) {
                $app = new App();
                $orderSn = $app->build_order_no();
                $orderSn = substr($orderSn, 0, -2);
                $orderData['depositsn'] = 'tp' . $orderSn . $kid;

                $orderData['kid'] = $kid;
                if (!empty($username))
                    $orderData['username'] = $username;
                if (!empty($mobile))
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

                if (!empty($comment))
                    $orderData['comment'] = $comment;

                $orderData['type'] = 1;
                $orderData['status'] = 0;
                $orderData['time'] = $goodtime;
                $orderData['time_created'] = $orderData['ctime'] = $time;
                $status = LibF::M('deposit_list')->add($orderData);
                if ($status) {
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
     * 获取当前用户钢瓶数
     */
    public function kehubottleAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
                $depostTypeData = $bottleTypeModel->getBottleDepositArray();
                
                $data = LibF::M('kehu_inventory')->where(array('kid' => $kid))->field('kid,type,count(*) as total')->group('type')->select();
                if(!empty($data)){
                    foreach ($data as &$value){
                        $value['bottle_name'] = $bottleTypeData[$value['type']]['bottle_name'];
                        $value['bottle_price'] = $depostTypeData[$value['type']]['suggested_price'];
                    }
                }else{
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

    /**
     * 天然气价格列表
     */
    public function pricelistAction() {
        if ($this->getRequest()->isPost()) {
            $user_type = $this->getRequest()->getPost('user_type',1);
            $priceData = array();

            $where['type'] = array('in', array(1, 2));
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
                    if ($val['type'] == 1) {
                        $val['typename'] = (isset($bottleTypeData[$val['norm_id']])) ? $bottleTypeData[$val['norm_id']]['bottle_name'] : '';
                        if ($user_type == 2) {
                            $val['yj_price'] = (!empty($depostTypeData[$val['norm_id']]['suggested_price_business'])) ? $depostTypeData[$val['norm_id']]['suggested_price_business'] : $depostTypeData[$val['norm_id']]['suggested_price'];
                        } elseif($user_type == 3){
                            $val['yj_price'] = (!empty($depostTypeData[$val['norm_id']]['suggested_price_industry'])) ? $depostTypeData[$val['norm_id']]['suggested_price_industry'] : $depostTypeData[$val['norm_id']]['suggested_price'];
                        }else {
                            $val['yj_price'] = (!empty($depostTypeData[$val['norm_id']]['suggested_price'])) ? $depostTypeData[$val['norm_id']]['suggested_price'] : 0;
                        }
                    } else {
                        $val['typename'] = (isset($productTypeData[$val['norm_id']])) ? $productTypeData[$val['norm_id']]['name'] : '';
                        $val['yj_price'] = 0;
                    }
                    if ($user_type == 2) {
                        $val['price'] = $value['retail_price_business'] ? $value['retail_price_business'] : $value['retail_price'];  //零售价
                    } elseif($user_type == 3){
                        $val['price'] = $value['retail_price_industry'] ? $value['retail_price_industry'] : $value['retail_price'];  //零售价
                    }else {
                        $val['price'] = $value['retail_price'] ? $value['retail_price'] : 0;  //零售价
                    }
                    $returnData[] = $val;
                }
            }

            $this->app->respond(1, $returnData);
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户积分列表
     */
    public function pointlistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($kid) && !empty($token)) {
                $pointData = LibF::M('point_list')->field('time,point,type,balance')->where(array('kid' => $kid))->order('time desc')->select();
                if (!empty($pointData)) {
                    foreach ($pointData as &$value) {
                        $value['time_list'] = date('Y-m-d', $value['time']);
                    }
                }else{
                    $pointData = array();
                }
                // $data = json_encode($pointData);

                $this->app->respond(1, $pointData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 用户积分增加
     */
    public function addpointAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {

                    $param['kid'] = $kehuData['kid'];
                    $param['time'] = $this->getRequest()->getPost('time', time());  //充值时间
                    $param['point'] = $this->getRequest()->getPost('point', 0);   //充值积分
                    $param['balance'] = $param['point'] + $kehuData['point']; //充值金额
                    $status = LibF::M('point_list')->add($param);
                    
                    if ($status) {
                        LibF::M('kehu')->where(array('kid' => $param['kid']))->save(array('point' => $param['balance']));
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

    /**
     * 用户余额列表
     */
    public function balanceAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                $pageStart = ($page - 1) * $this->pageSize;
                $balanceData = LibF::M('balance_list')->field('time,money,paytype,type,balance,balance_sn,status')->where(array('kid' => $kid, 'type' => 1,'status' =>1))->order('time desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($balanceData)) {
                    foreach ($balanceData as &$value) {
                        $value['time_list'] = date('Y-m-d', $value['time']);
                    }
                }else{
                    $balanceData = array();
                }
                //$data = json_encode($balanceData);

                $this->app->respond(1, $balanceData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 消费记录
     */
    public function consumeAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                $pageStart = ($page - 1) * $this->pageSize;
                $balanceData = LibF::M('balance_list')->field('time,money,paytype,balance,balance_sn,status')->where(array('kid' => $kid, 'type' => 2))->order('time desc')->limit($pageStart,$this->pageSize)->select();
                if (!empty($balanceData)) {
                    foreach ($balanceData as &$value) {
                        $value['time_list'] = date('Y-m-d', $value['time']);
                    }
                }else{
                    $balanceData = array();
                }
                //$data = json_encode($balanceData);

                $this->app->respond(1, $balanceData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 用户充值
     */
    public function addbalanceAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);
            $type = $this->getRequest()->getPost('type'); //2支付宝 3微信

            if (!empty($kid) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {

                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $param['balance_sn'] = 'cz' . $orderSn;

                    $param['kid'] = $kehuData['kid'];
                    $param['time'] = $this->getRequest()->getPost('time', time());  //充值时间
                    $param['paytype'] = $type;
                    $param['type'] = 1;
                    
                    if ($type == 3) { //微信充值
                        $money = $this->getRequest()->getPost('money', 100);   //充值金额
                        $param['money'] = $money / 100;
                        $param['status'] = 0; //默认未充值完成
                        $param['balance'] = $param['money'] + $kehuData['balance']; //充值金额

                        $status = LibF::M('balance_list')->add($param);
                        $this->balance($param['balance_sn'], $money, '用户充值');
                    } elseif ($type == 2) {
                        $money = $this->getRequest()->getPost('money', 0);   //充值金额
                        $param['money'] = $money;
                        $param['status'] = 0; //默认未充值完成
                        $param['balance'] = $param['money'] + $kehuData['balance']; //充值金额

                        $status = LibF::M('balance_list')->add($param);
                        $this->balance_zfb($param['balance_sn'], $money, '充值','用户充值');
                    } else {
                        $param['money'] = $this->getRequest()->getPost('money', 0);   //充值金额
                        $param['balance'] = $param['money'] + $kehuData['balance']; //充值金额
                        $status = LibF::M('balance_list')->add($param);

                        if ($status) {
                            LibF::M('kehu')->where(array('kid' => $param['kid']))->save(array('balance' => $param['balance']));
                            $this->app->respond(1, '充值成功');
                        } else {
                            $this->app->respond(-3, '充值失败');
                        }
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

    //用户欠款订单确认欠款
    public function addarrearsAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $order_sn = $this->getRequest()->getPost('order_sn');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($kid) && !empty($token) && $order_sn) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {

                    $param['order_sn'] = $order_sn;
                    $param['kid'] = $kehuData['kid'];
                    $param['time'] = $this->getRequest()->getPost('time', time());
                    $param['type'] = 1;
                    $param['money'] = $this->getRequest()->getPost('money', 0);
                    $param['balance'] = $param['money'] + $kehuData['money'];
                    $status = LibF::M('order_arrears')->add($param);
                    if ($status) {
                        LibF::M('kehu')->where(array('kid' => $param['kid']))->save(array('money' => $param['balance']));
                        LibF::M('order')->where(array('order_sn' => $order_sn))->save(array('is_settlement' => 2));
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

    //用户缴费
    public function addrepaymentAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);
            $type = $this->getRequest()->getPost('type');//1余额还款  2 支付宝 3微信

            if (!empty($kid) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {

                    $setArr = array();
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $order_sn = 'hk' . $orderSn;

                    $param['order_sn'] = $order_sn;
                    $param['kid'] = $kehuData['kid'];
                    $param['time'] = $this->getRequest()->getPost('time', time());
                    $param['money'] = $zmoney = $this->getRequest()->getPost('money', 100);
                    $param['type'] = 2;
                    $param['paytype'] = $type;
                    if ($type == 3) {
                        $money = $param['money'] / 100; //分为单位
                        $param['balance'] = $setArr['money'] = $kehuData['money'] - $money;
                        $param['money'] = $money;
                        $param['status'] = 0;
                        $status = LibF::M('order_arrears')->add($param);
                        $this->balance($order_sn, $zmoney, '用户还款', 2);
                    } elseif ($type == 2) {
                        $money = $param['money']; //分为单位
                        $param['balance'] = $setArr['money'] = $kehuData['money'] - $money;
                        $param['money'] = $money;
                        $param['status'] = 0;
                        $status = LibF::M('order_arrears')->add($param);
                        $this->balance_zfb($order_sn, $money, '缴费','用户缴费',2);
                    } else{
                        $param['status'] = 1;
                        $param['is_return'] = 1;
                        $param['balance'] = $setArr['money'] = $kehuData['money'] - $param['money'];
                        $status = LibF::M('order_arrears')->add($param);
                        if ($status) {
                            if ($type == 1) {
                                $b['balance_sn'] = $orderSn;
                                $b['kid'] = $kehuData['kid'];
                                $b['time'] = time();
                                $b['money'] = $param['money'];
                                $b['balance'] = $setArr['balance'] = $kehuData['balance'] - $param['money'];
                                $b['type'] = 2;
                                $b['paytype'] = $type;
                                LibF::M('balance_list')->add($b);
                            }
                            LibF::M('kehu')->where(array('kid' => $kehuData['kid']))->save($setArr);
                            $this->app->respond(1, '还款成功');
                        } else {
                            $this->app->respond(-3, '还款失败');
                        }
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

    //借款记录
    public function arrearslistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {
                    $where['type'] = 1;
                    $where['kid'] = $kid;

                    $pageStart = ($page - 1) * $this->pageSize;
                    $arrearsData = LibF::M('order_arrears')->field('order_sn,time,paytype,type,money,balance,status')->where($where)->order('time desc')->limit($pageStart, $this->pageSize)->select();
                    if (!empty($arrearsData)) {
                        foreach ($arrearsData as &$value) {
                            $value['time_list'] = date('Y-m-d', $value['time']);
                        }
                    }else{
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

    //还款记录
    public function repaymentlistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                $kehuData = LibF::M('kehu')->where(array('kid' => $kid))->find();
                if (!empty($kehuData)) {
                    $where['type'] = 2;
                    $where['kid'] = $kid;
                    $where['status'] = 1;
                    
                    $pageStart = ($page - 1) * $this->pageSize;
                    $arrearsData = LibF::M('order_arrears')->field('order_sn,time,paytype,type,money,balance,status')->where($where)->order('time desc')->limit($pageStart,$this->pageSize)->select();
                    if (!empty($arrearsData)) {
                        foreach ($arrearsData as &$value) {
                            $value['time_list'] = date('Y-m-d', $value['time']);
                        }
                    }else{
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

    //补充接口生成预付款订单(微信)
    public function payorderAction() {
        if ($this->getRequest()->isPost()) {
            $order_sn = $this->getRequest()->getPost('ordersn');
            $money = $this->getRequest()->getPost('money');
            $content = $this->getRequest()->getPost('content');

            if (!empty($order_sn) && !empty($money)) {
                
                /*$options = array(
                    'appid' => 'wx732dce3980379b83', //填写微信分配的公众开放账号ID
                    'mch_id' => '1333208901', //填写微信支付分配的商户号
                    'notify_url' => 'http://www.ruiqi100.com/appuser/backwx', //填写微信支付结果回调地址
                    'key' => 'ps7667613sr7667613rq902000043819'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
                );*/
                
                $options = array(
                    'appid' => 'wx40dca092205d606b', //填写微信分配的公众开放账号ID
                    'mch_id' => '1358732102', //填写微信支付分配的商户号
                    'notify_url' => $this->url.'/appuser/backwx', //填写微信支付结果回调地址
                    'key' => 'ac1gdc160wakkty301twcgy6601hlmt9'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
                );

                //统一下单方法
                $wechatAppPay = new WxpayModel($options);
                $params['body'] = $content;      //商品描述
                $params['out_trade_no'] = $order_sn; //自定义的订单号
                $params['total_fee'] = $money;     //订单金额 只能为整数 单位为分
                $params['trade_type'] = 'APP';     //交易类型 JSAPI | NATIVE | APP | WAP 

                $result = $wechatAppPay->unifiedOrder($params);
                if(!empty($result)){
                    $rData['appid'] = $result['appid'];
                    $rData['partnerid'] = $result['mch_id'];
                    $rData['prepayid'] = $result['prepay_id'];
                    $rData['noncestr'] = $result['nonce_str'];
                    $rData['timestamp'] = time();
                    $rData['package'] = 'Sign=WXPay';
                    $r = $wechatAppPay->MakeSign($rData);
                    $result['sign'] = $r;
                    $result['timestamp'] = $rData['timestamp'];
                    $result['package'] = $rData['package'];
                    
                    $this->app->respond(1, $result);
                }else{
                    $this->app->respond(-3, '生成预付订单失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        }else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口生成支付宝订单（支付宝）
    public function zfborderAction() {
        $alipay_config = $this->zfbConfig();

        //支付类型
        $payment_type = "1";
        //必填，不能修改

        //服务器异步通知页面路径
        $notify_url = $this->url . "/appuser/notifozfb";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = $this->url . "/appuser/returnzfb";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //必填
        //商户订单号
        $out_trade_no = $this->getRequest()->getPost('out_trade_no');
        //商户网站订单系统中唯一订单号，必填
        //订单名称
        $subject = $this->getRequest()->getPost('order_title');
        //必填
        //付款金额
        $total_fee = $this->getRequest()->getPost('money');
        //必填
        //订单描述

        $body = $this->getRequest()->getPost('order_comment');
        
        //商品展示地址
        $show_url = 'm.alipay.com';
        $service = 'mobile.securitypay.pay';
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => '"'.$service.'"',
            "partner" => '"'.trim($alipay_config['partner']).'"',
            "payment_type" => '"'.trim($payment_type).'"',
            "notify_url" => '"'.trim($notify_url).'"',
            "return_url" => '"'.trim($return_url).'"',
            "seller_id" => '"'.trim($alipay_config['seller_id']).'"',
            "out_trade_no" => '"'.trim($out_trade_no).'"',
            "subject" => '"'.trim($subject).'"',
            "total_fee" => '"'.trim($total_fee).'"',
            "body" => '"'.trim($body).'"',
            "show_url" => '"'.trim($show_url).'"',
            //"anti_phishing_key" => trim($anti_phishing_key),
            //"exter_invoke_ip" => trim($exter_invoke_ip),
            "_input_charset" => '"'.trim(strtolower($alipay_config['input_charset'])).'"'
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo json_encode($html_text);
        exit;
    }
    
    //补充接口查询生成订单
    public function cxpayorderAction() {
        if ($this->getRequest()->isPost()) {
            $order_sn = $this->getRequest()->getPost('ordersn');

            if (!empty($order_sn)) {
                
                $options = array(
                    'appid' => 'wx40dca092205d606b', //填写微信分配的公众开放账号ID
                    'mch_id' => '1358732102', //填写微信支付分配的商户号
                    'notify_url' => $this->url.'/appuser/backwx', //填写微信支付结果回调地址
                    'key' => 'ac1gdc160wakkty301twcgy6601hlmt9'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
                );

                //统一下单方法
                $wechatAppPay = new WxpayModel($options);
                $params['out_trade_no'] = $order_sn; //自定义的订单号
                //$params['trade_type'] = 'APP';     //交易类型 JSAPI | NATIVE | APP | WAP 

                $result = $wechatAppPay->orderQuery($params['out_trade_no']);
                
                $this->app->respond(1, $result);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        }else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充回掉函数更新订单状态
    public function backwxAction() {
        $options = array(
            'appid' => 'wx40dca092205d606b', //填写微信分配的公众开放账号ID
            'mch_id' => '1358732102', //填写微信支付分配的商户号
            'notify_url' => $this->url.'/appuser/backwx', //填写微信支付结果回调地址
            'key' => 'ac1gdc160wakkty301twcgy6601hlmt9'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
        );
        $wechatAppPay = new WxpayModel($options);
        $data = $wechatAppPay->getNotifyData();

        if (!empty($data)) {
            $iData['comment'] = json_encode($data);
            $iData['time_created'] = time();
            $status = LibF::M('wx_back')->add($iData);
            if ($status && ($data['return_code'] == 'SUCCESS')) {
                $ordersn = $data['out_trade_no'];
                $msg = $wechatAppPay->closeOrder($ordersn);

                $sdata['message'] = json_encode($msg);
                $sdata['status'] = 1;

                $orderData = LibF::M('order')->where(array('order_sn' => $ordersn))->find();
                if (!empty($orderData)) {
                    LibF::M('wx_back')->where(array('id' => $status))->save($sdata);
                    LibF::M('order')->where(array('order_sn' => $ordersn, 'status' => 4))->save(array('ispayment' => 1, 'order_paytype' => 1));
                }
                $returnData['return_code'] = $msg['return_code'];
                $returnData['return_msg'] = $msg['return_msg'];
                
                //$this->app->respond(1, '成功');
            } else {
                $returnData['return_code'] = $msg['return_code'];
                $returnData['return_msg'] = $msg['return_msg'];
                //$this->app->respond(-1, '失败');
            }
            
            $xml = '<xml>
                        <return_code><![CDATA[' . $returnData['return_code'] . ']]></return_code>
                        <return_msg><![CDATA[' . $returnData['return_msg'] . ']]></return_msg>
                    </xml>';
            echo $xml;
            exit;
        } else {
            $this->app->respond(-3, '当前用户未登录');
        }
    }

    //补充回调函数更新充值数据
    public function backczAction() {
        /*$options = array(
            'appid' => 'wx732dce3980379b83', //填写微信分配的公众开放账号ID
            'mch_id' => '1333208901', //填写微信支付分配的商户号
            'notify_url' => 'http://www.ruiqi100.com/appuser/backcz', //填写微信支付结果回调地址
            'key' => 'ps7667613sr7667613rq902000043819'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
        );*/
        
        $options = array(
            'appid' => 'wx40dca092205d606b', //填写微信分配的公众开放账号ID
            'mch_id' => '1358732102', //填写微信支付分配的商户号
            'notify_url' => $this->url.'/appuser/backcz', //填写微信支付结果回调地址
            'key' => 'ac1gdc160wakkty301twcgy6601hlmt9'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
        );
        $wechatAppPay = new WxpayModel($options);
        $data = $wechatAppPay->getNotifyData();
        
        if (!empty($data)) {
            $iData['comment'] = json_encode($data);
            $iData['type'] = 2;
            $iData['time_created'] = time();
            $status = LibF::M('wx_back')->add($iData);
            if ($status && ($data['return_code'] == 'SUCCESS')) {
                $ordersn = $data['out_trade_no'];
                $msg = $wechatAppPay->closeOrder($ordersn);

                $sdata['message'] = json_encode($msg);
                $sdata['status'] = 1;
                LibF::M('wx_back')->where(array('id' => $status))->save($sdata);

                //更新用户余额
                $d = LibF::M('balance_list')->where(array('balance_sn' => $ordersn))->find();
                if (!empty($d)) {
                    LibF::M('balance_list')->where(array('balance_sn' => $ordersn))->save(array('status' => 1));
                    LibF::M('kehu')->where(array('kid' => $d['kid']))->save(array('balance' => $d['balance']));
                }
                $returnData['return_code'] = $msg['return_code'];
                $returnData['return_msg'] = $msg['return_msg'];
                
                //$this->app->respond(1, '成功');
            } else {
                $returnData['return_code'] = $data['return_code'];
                $returnData['return_msg'] = $msg['return_msg'];
                //$this->app->respond(-1, '失败');
            }
            $xml = '<xml>
                        <return_code><![CDATA[' . $returnData['return_code'] . ']]></return_code>
                        <return_msg><![CDATA[' . $returnData['return_msg'] . ']]></return_msg>
                    </xml>';
            echo $xml;
            exit;
            //echo json_encode($returnData);
        } else {
            $this->app->respond(-3, '当前用户未登录');
        }
    }
    
    //补充回调函数更新还款数据
    public function backhkAction() {
        /*$options = array(
            'appid' => 'wx732dce3980379b83', //填写微信分配的公众开放账号ID
            'mch_id' => '1333208901', //填写微信支付分配的商户号
            'notify_url' => 'http://www.ruiqi100.com/appuser/backhk', //填写微信支付结果回调地址
            'key' => 'ps7667613sr7667613rq902000043819'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
        );*/
        
        $options = array(
            'appid' => 'wx40dca092205d606b', //填写微信分配的公众开放账号ID
            'mch_id' => '1358732102', //填写微信支付分配的商户号
            'notify_url' => $this->url.'/appuser/backhk', //填写微信支付结果回调地址
            'key' => 'ac1gdc160wakkty301twcgy6601hlmt9'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
        );
        $wechatAppPay = new WxpayModel($options);
        $data = $wechatAppPay->getNotifyData();

        if (!empty($data)) {
            $iData['comment'] = json_encode($data);
            $iData['type'] = 3;
            $iData['time_created'] = time();
            $status = LibF::M('wx_back')->add($iData);
            if ($status && ($data['return_code'] == 'SUCCESS')) {
                $ordersn = $data['out_trade_no'];
                $msg = $wechatAppPay->closeOrder($ordersn);

                $sdata['message'] = json_encode($msg);
                $sdata['status'] = 1;
                LibF::M('wx_back')->where(array('id' => $status))->save($sdata);

                //更新用户余额
                $d = LibF::M('order_arrears')->where(array('order_sn' => $ordersn))->find();
                if (!empty($d)) {
                    LibF::M('order_arrears')->where(array('order_sn' => $ordersn))->save(array('status' => 1,'is_return' => 1));
                    LibF::M('kehu')->where(array('kid' => $d['kid']))->save(array('money' => $d['balance']));
                }
                
                $returnData['return_code'] = $data['return_code'];
                $returnData['return_msg'] = $msg['return_msg'];
                
                //$this->app->respond(1, '成功');
            } else {
                $returnData['return_code'] = $data['return_code'];
                $returnData['return_msg'] = $msg['return_msg'];
                
                //$this->app->respond(-1, '失败');
            }
            
            $xml = '<xml>
                        <return_code><![CDATA[' . $returnData['return_code'] . ']]></return_code>
                        <return_msg><![CDATA[' . $returnData['return_msg'] . ']]></return_msg>
                    </xml>';
            echo $xml;
            exit;
        } else {
            $this->app->respond(-3, '当前用户未登录');
        }
    }
    
    //补充接口押金列表
    public function depositlistAction() {
        if ($this->getRequest()->isPost()) {
            $kid = $this->getRequest()->getPost('kid'); //用户id
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page',1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($kid) && !empty($token)) {
                $pageStart = ($page - 1) * $this->pageSize;
                $depositData = LibF::M('deposit')->field('order_sn,number,money,time_created')->where(array('kid' => $kid,'status' => 0))->order('time_created desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($depositData)) {
                    foreach ($depositData as &$value) {
                        $value['time_list'] = date('Y-m-d', $value['time_created']);
                    }
                }else{
                    $depositData = array();
                }
                //$data = json_encode($balanceData);

                $this->app->respond(1, $depositData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充方法支付宝回调函数(充值)
    public function notifyzfbAction(){
        $alipay_config = $this->zfbConfig();
        
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号
            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                $iData['comment'] = json_encode($_POST);
                $iData['type'] = 2;
                $iData['time_created'] = time();
                $status = LibF::M('zfb_back')->add($iData);
                if (!empty($status)) {
                    $ordersn = $out_trade_no;

                    //更新用户余额
                    $d = LibF::M('balance_list')->where(array('balance_sn' => $ordersn))->find();
                    if (!empty($d)) {
                        LibF::M('balance_list')->where(array('balance_sn' => $ordersn))->save(array('status' => 1));
                        LibF::M('kehu')->where(array('kid' => $d['kid']))->save(array('balance' => $d['balance']));
                    }
                    //$this->app->respond(1, '成功');
                } else {
                    //$this->app->respond(-1, '失败');
                }
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "SUCCESS";  //请不要修改或删除
        } else {
            if (!empty($_POST)) {
                $iData['comment'] = json_encode($_POST);
                $iData['type'] = 2;
                $iData['time_created'] = time();
                $status = LibF::M('zfb_back')->add($iData);
                if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                    $out_trade_no = $_POST['out_trade_no'];
                    $d = LibF::M('balance_list')->where(array('balance_sn' => $out_trade_no))->find();
                    if (!empty($d)) {
                        LibF::M('balance_list')->where(array('balance_sn' => $out_trade_no))->save(array('status' => 1));
                        LibF::M('kehu')->where(array('kid' => $d['kid']))->save(array('balance' => $d['balance']));
                    }
                    echo "SUCCESS";  //请不要修改或删除
                } else {
                    echo "FAIL";
                }
            } else {
                //$this->app->respond(-2, '失败');
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                echo "SUCCESS";  //请不要修改或删除
            }
        }
        exit;
    }
    
    //补充方法支付宝回调函数（欠款）
    public function notifqzfbAction(){
        $alipay_config = $this->zfbConfig();
        
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号
            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                $iData['comment'] = json_encode($_POST);
                $iData['type'] = 3;
                $iData['time_created'] = time();
                $status = LibF::M('zfb_back')->add($iData);
                if (!empty($status)) {
                    $ordersn = $out_trade_no;

                    //更新用户余额
                    $d = LibF::M('order_arrears')->where(array('order_sn' => $ordersn))->find();
                    if (!empty($d)) {
                        LibF::M('order_arrears')->where(array('order_sn' => $ordersn))->save(array('status' => 1));
                        LibF::M('kehu')->where(array('kid' => $d['kid']))->save(array('money' => $d['balance']));
                    }
                    //$this->app->respond(1, '成功');
                } else {
                    //$this->app->respond(-1, '失败');
                }
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "SUCCESS";  //请不要修改或删除
        } else {
            
            if (!empty($_POST)) {
                if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                    $out_trade_no = $_POST['out_trade_no'];

                    //更新用户余额
                    $d = LibF::M('order_arrears')->where(array('order_sn' => $out_trade_no))->find();
                    if (!empty($d)) {
                        LibF::M('order_arrears')->where(array('order_sn' => $out_trade_no))->save(array('status' => 1));
                        LibF::M('kehu')->where(array('kid' => $d['kid']))->save(array('money' => $d['balance']));
                    }

                    echo "SUCCESS";  //请不要修改或删除
                } else {
                    $iData['comment'] = json_encode($_POST);
                    $iData['type'] = 3;
                    $iData['time_created'] = time();
                    $status = LibF::M('zfb_back')->add($iData);

                    //$this->app->respond(-2, '失败');
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                    echo "FAIL";
                }
            } else {

                echo "SUCCESS";  //请不要修改或删除
            }
        }
        exit;
    }
    
    //补充方法支付宝回调函数 （订单结算）
    public function notifozfbAction() {
        $alipay_config = $this->zfbConfig();

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号
            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];

            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                $iData['comment'] = json_encode($_POST);
                $iData['type'] = 3;
                $iData['time_created'] = time();
                $status = LibF::M('zfb_back')->add($iData);
                if (!empty($status)) {
                    $ordersn = $out_trade_no;
                    LibF::M('order')->where(array('order_sn' => $ordersn, 'status' => 4))->save(array('ispayment' => 1,'order_paytype' => 1));
                    //$this->app->respond(1, '成功');
                } else {
                    //$this->app->respond(-1, '失败');
                }
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "SUCCESS";  //请不要修改或删除
        } else {

            if (!empty($_POST)) {
                if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                    $ordersn = $_POST['out_trade_no'];
                    LibF::M('order')->where(array('order_sn' => $ordersn, 'status' => 4))->save(array('ispayment' => 1, 'order_paytype' => 1));

                    echo "SUCCESS";  //请不要修改或删除
                } else {
                    $iData['comment'] = json_encode($_POST);
                    $iData['type'] = 3;
                    $iData['time_created'] = time();
                    $status = LibF::M('zfb_back')->add($iData);

                    //$this->app->respond(-2, '失败');
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                    echo "FAIL";
                }
            } else {
                echo "SUCCESS";  //请不要修改或删除
            }
        }
        exit;
    }
    
    //补充接口 版本升级更新
    public function appversionAction() {
        //app更新类型 app userapp shipperapp
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token)) {

                $file = "vsersion_address,version_number,version_time";
                $data = LibF::M('andrews_version')->where(array('version_status' => 1, 'version_type' => 'userapp'))->order('version_time desc')->limit(1)->find();
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
    
    //补充方法支付宝回掉函数
    public function returnzfb() {
        
    }
    
    //补充方法获取当前送气工所在位置坐标
    public function positionAction() {
        if ($this->getRequest()->isPost()) {
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shipper_id)) {
                $param['shipper_id'] = $shipper_id;

                $data = LibF::M('position')->where($param)->find();
                if ($data) {
                    $this->app->respond(1, $data);
                } else {
                    $this->app->respond(-3, '获取失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口获取当前用户优惠余额券
    public function promotionsAction() {
        if ($this->getRequest()->isPost()) {
            $user_id = $this->getRequest()->getPost('kid');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($user_id)) {
                $kehuModel = new KehuModel();
                $promotionsData = $kehuModel->getUserPromotionsData($user_id);
                if ($promotionsData) {
                    $this->app->respond(1, $promotionsData);
                } else {
                    $this->app->respond(-2, '没有相应余额券');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充方法充值生产订单（微信）
    protected function balance($order_sn, $money, $content, $type = 1) {

        $backUrl = $this->url . '/appuser/backcz';
        if ($type == 2) {
            $backUrl = $this->url . '/appuser/backhk';
        }
        if (!empty($order_sn) && !empty($money)) {
            $options = array(
                'appid' => 'wx40dca092205d606b', //填写微信分配的公众开放账号ID
                'mch_id' => '1358732102', //填写微信支付分配的商户号
                'notify_url' => $backUrl, //填写微信支付结果回调地址
                'key' => 'ac1gdc160wakkty301twcgy6601hlmt9'    //填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
            );

            //统一下单方法
            $wechatAppPay = new WxpayModel($options);
            $params['body'] = $content;      //商品描述
            $params['out_trade_no'] = $order_sn; //自定义的订单号
            $params['total_fee'] = $money;     //订单金额 只能为整数 单位为分
            $params['trade_type'] = 'APP';     //交易类型 JSAPI | NATIVE | APP | WAP 

            $result = $wechatAppPay->unifiedOrder($params);
            if (!empty($result)) {
                $rData['appid'] = $result['appid'];
                $rData['partnerid'] = $result['mch_id'];
                $rData['prepayid'] = $result['prepay_id'];
                $rData['noncestr'] = $result['nonce_str'];
                $rData['timestamp'] = time();
                $rData['package'] = 'Sign=WXPay';
                $r = $wechatAppPay->MakeSign($rData);
                $result['sign'] = $r;
                $result['timestamp'] = $rData['timestamp'];
                $result['package'] = $rData['package'];

                $this->app->respond(1, $result);
            } else {
                $this->app->respond(-3, '生成预付订单失败');
            }
        } else {
            $this->app->respond(-3, '订单不存在');
        }
    }

    //补充方法重置生产订单（支付宝）
    protected function balance_zfb($order_sn, $money, $title, $content, $type = 1) {
        $alipay_config = $this->zfbConfig();

        $payment_type = "1"; //支付类型

        if ($type == 2) {
            $notify_url = $this->url . "/appuser/notifqzfb";   //服务器异步通知页面路径
        } else {
            $notify_url = $this->url . "/appuser/notifyzfb";   //服务器异步通知页面路径
        }
        $return_url = $this->url . "/appuser/returnzfb";  //页面跳转同步通知页面路径

        $show_url = 'm.alipay.com'; //商品展示地址
        $service = 'mobile.securitypay.pay';

        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1
        $parameter = array(
            "service" => '"' . $service . '"',
            "partner" => '"' . trim($alipay_config['partner']) . '"',
            "payment_type" => '"' . trim($payment_type) . '"',
            "notify_url" => '"' . trim($notify_url) . '"',
            "return_url" => '"' . trim($return_url) . '"',
            "seller_id" => '"' . trim($alipay_config['seller_id']) . '"',
            "out_trade_no" => '"' . trim($order_sn) . '"',
            "subject" => '"' . trim($title) . '"',
            "total_fee" => '"' . trim($money) . '"',
            "body" => '"' . trim($content) . '"',
            "show_url" => '"' . trim($show_url) . '"',
            //"anti_phishing_key" => trim($anti_phishing_key),
            //"exter_invoke_ip" => trim($exter_invoke_ip),
            "_input_charset" => '"' . trim(strtolower($alipay_config['input_charset'])) . '"'
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo json_encode($html_text);
        exit;
    }

    //补充方法支付宝config
    protected function zfbConfig() {
        //合作身份者id，以2088开头的16位纯数字
        $alipay_config['partner'] = '2088221957053545';

        $alipay_config['seller_id'] = 'paisiranqi1@126.com';

        //商户的私钥（后缀是.pen）文件相对路径
        $alipay_config['private_key_path'] = getcwd() . '/key/rsa_private_key.pem';

        //支付宝公钥（后缀是.pen）文件相对路径
        $alipay_config['ali_public_key_path'] = getcwd() . '/key/alipay_public_key.pem';

        //签名方式 不需修改
        $alipay_config['sign_type'] = strtoupper('RSA');
        //$alipay_config['sign_type'] = strtoupper('MD5');
        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset'] = strtolower('utf-8');

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert'] = getcwd() . '/cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport'] = 'http';

        return $alipay_config;
    }

}