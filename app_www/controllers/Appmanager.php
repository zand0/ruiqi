<?php

/**
 * @门店经理(店长)
 */
class AppmanagerController extends \Com\Controller\Common {

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
     * 订单状态
     * @var orderStatus
     */
    protected $orderStatus;
    protected $bxorderStatus;
    protected $tporderStatus;
    protected $zjorderStatus;
    protected $czorderStatus;


    /**
     * 充值方式
     * @var paytype
     */
    protected $paytype;

    protected $pageSize;

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
        $this->orderStatus = array(1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单', -1 => '已关闭');
        $this->bxorderStatus = array(0 => '未派发', 1 => '已派发', 2 => '已处理');
        $this->tporderStatus = array(0 => '未派发', 1 => '配送中', 2 => '已完成');
        $this->zjorderStatus = array(0 => '未派发', 1 => '配送中', 2 => '已完成');
        $this->czorderStatus = array(0 => '失败', 1 => '成功');

        $this->paytype = array(1 => '现金', 2 => '支付宝', 3 => '微信');

        $this->pageSize = 15;
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
                $where['mobile_phone'] = $mobile;
                $where['shop_id'] = array('gt', 0);
                $parent_record = LibF::M('admin_user')->where($where)->find();
                if (empty($parent_record)) {
                    $this->app->respond(-1, '此账号不存在');
                } else if ($parent_record['password'] != md5(md5($password) . $parent_record['user_salt'])) {
                    $this->app->respond(-2, '密码错误');
                } else {
                    //判断当前验证吗是否正确
                    $token = 123456;
                    $this->app->respond(1, array('mobile' => $mobile, 'user_id' => $parent_record['user_id'], 'username' => $parent_record['username'], 'shop_id' => $parent_record['shop_id'], 'token' => $token));
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
                $parent_record = LibF::M('admin_user')->where(array('mobile_phone' => $mobile))->find();
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
                        $where['mobile_phone'] = $mobile;
                        $userData = LibF::M('admin_user')->where($where)->find();
                        $data['password'] = md5(md5($password) . $userData['user_salt']);
                        $status = LibF::M('admin_user')->where($where)->save($data);
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
                    $where['mobile_phone'] = $mobile;
                    $parent_record = LibF::M('admin_user')->where($where)->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['password'] != md5(md5($old_password) . $parent_record['user_salt'])) {
                        $this->app->respond(-4, '原始密码不正确');
                    } else {
                        $data['password'] = md5(md5($password) . $parent_record['user_salt']);
                        $status = LibF::M('admin_user')->where($where)->save($data);
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
                    $parent_record = LibF::M('verification_code')->where(array('mobile' => old_mobile, 'status' => 0))->find();
                    if ($parent_record === null) {
                        $this->app->respond(-1, '此账号不存在');
                    } else if ($parent_record['code'] != $captcha) {
                        $this->app->respond(-4, '验证码不正确');
                    } else {
                        $data['mobile'] = $mobile;
                        $where['mobile_phone'] = $old_mobile;
                        $status = LibF::M('admin_user')->where($where)->save($data);
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
                $where['mobile_phone'] = $mobile;
                $where['shop_id'] = array('gt', 0);
                $userData = LibF::M('admin_user')->where($where)->find();
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
     * app门店价格
     * 
     * @param $mobile
     * @param $token
     */
    public function pricelistAction() {

        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {

                $data = array();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * app经理入货价格
     * 
     * @param $mobile
     * @param $token
     */
    public function stockpriceAction() {
        if ($this->getRequest()->isPost()) {
            $mobile = $this->getRequest()->getPost('mobile');
            $beginTime = $this->getRequest()->getPost('begintime');
            $endTime = $this->getRequest()->getPost('endtime');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($mobile) && !empty($token)) {

                $data = array();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    
    /**
     * 补充接口一：订单接口
     * 
     * @param $shop_id
     * @param $type
     * @param $start_time
     * @param $end_time
     */
    public function orderlistAction() {
        if ($this->getRequest()->isPost()) {
            $date = date('Y-m-d');

            $shop_id = $this->getRequest()->getPost('shop_id');
            $status = $this->getRequest()->getPost('status');
            //$start_time = $this->getRequest()->getPost('time_start', $date);
            //$end_time = $this->getRequest()->getPost('time_end', $date);
            //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';

            $token = $this->getRequest()->getPost('token', 1234);
            $type = $this->getRequest()->getPost('type');   //1配送中 2已完成 3问题订单 4未派发

            $param['shop_id'] = $shop_id;
            $param['start_time'] = $start_time;
            $param['end_time'] = $end_time;
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            if (!empty($shop_id) && !empty($token)) {
                //备注 当前订单状态 1未派发 2配送中 4已完成
                $where = array('shop_id' => $shop_id, 'page' => $page, 'pagesize' => $this->pageSize);
                if ($type == 1) {
                    $where['status'] = 2;
                } else if ($type == 2) {
                    $where['status'] = 4;
                } else if ($type == 3) {
                    //问题订单
                    $where['status'] = 5;
                } else if ($type == 4) {
                    $where['status'] = 1;
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

                $returnData = array();
                if (!empty($data)) {
                    foreach ($data as $val) {
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

    public function ordertotalAction() {
        if ($this->getRequest()->isPost()) {
            $date = date('Y-m-d');

            $shop_id = $this->getRequest()->getPost('shop_id');
            //$start_time = $this->getRequest()->getPost('time_start', $date);
            //$end_time = $this->getRequest()->getPost('time_end', $date);
            //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';

            $token = $this->getRequest()->getPost('token', 1234);

            $param['shop_id'] = $shop_id;
            $param['start_time'] = $start_time;
            $param['end_time'] = $end_time;
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            if (!empty($shop_id) && !empty($token)) {
                //备注 当前订单状态 1未派发 2配送中 4已完成
                $where = array('shop_id' => $shop_id);

                //获取当前类型订单统计
                $newData = $returnData = array();
                $dataTotal = $this->getOrderStatus($param, '', 'status', '');
                if (!empty($dataTotal)) {
                    foreach ($dataTotal as &$value) {
                        $value['title'] = isset($this->orderStatus[$value['status']]) ? $this->orderStatus[$value['status']] : '';
                        $newData[$value['status']] = $value;
                    }
                    
                    foreach ($this->orderStatus as $k => $v) {
                        $value = !empty($newData[$k]) ? $newData[$k] : array();
                        $dv['title'] = $v;
                        $dv['total'] = isset($value['total']) ? $value['total'] : 0;
                        $dv['status'] = $k;
                        $returnData[] = $dv;
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
     * 补充接口二：获取当前门店送气工
     * 
     * @param $shop_id
     */
    public function shopshipperAction() {
        if ($this->getRequest()->isPost()) {

            $shop_id = $this->getRequest()->getPost('shop_id');

            $token = $this->getRequest()->getPost('token', 1234);
            $page = $this->getRequest()->getPost('page', 1);

            if (!empty($shop_id) && !empty($token)) {
                $where['shop_id'] = $shop_id;

                $pageStart = ($page - 1) * $this->pageSize;
                $returnData = LibF::M('shipper')->where($where)->order('shipper_id DESC')->limit($pageStart, $this->pageSize)->select();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口三：派发当前订单
     * 
     * @param $order_sn
     * @param $shop_id
     * @param $shipper_id
     * @param $shipper_name
     * @param $shipper_mobile
     * 
     */
    public function distributionAction() {
        
        if ($this->getRequest()->isPost()) {

            $order_sn = $this->getRequest()->getPost('order_sn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($order_sn) && !empty($shop_id) && !empty($shipper_id) && !empty($token)) {

                $shiper['shipper_id'] = $shipper_id;
                $shiper['shipper_name'] = !empty($shipper_name) ? $shipper_name : '';
                $shiper['shipper_mobile'] = !empty($shipper_mobile) ? $shipper_mobile : '';
                $shiper['status'] = 2;

                $where['order_sn'] = $order_sn;
                $where['status'] = 1;
                $status = LibF::M('order')->where($where)->save($shiper);
                if ($status) {
                    $platform = 'android'; // 接受此信息的系统
                    $msg_content = json_encode(array('n_builder_id' => $shiper['shipper_id'], 'n_title' => $order_sn, 'n_content' => '订单已经派发', 'n_extras' => array('fromorder' => $order_sn, 'fromer' => $shipper_id, 'fromer_name' => $shipper_name, 'fromer_icon' => '', 'image' => '', 'sound' => '', 'send_all' => 0)));
                    $smsSend = new SmsDataModel();
                    $smsSend->send(16, 3, $shiper['shipper_id'], 1, $msg_content, $platform);

                    $this->app->respond(1, '订单已派发' . $shiper['shipper_name']);
                } else {
                    $this->app->respond(-1, '订单派发失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口四：订单标注
     * 
     * @param $order_sn
     * @param $shop_id
     */
    public function editorderAction() {
        if ($this->getRequest()->isPost()) {

            $order_sn = $this->getRequest()->getPost('order_sn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $comment = $this->getRequest()->getPost('comment');
            $status = $this->getRequest()->getPost('status', 5); //5问题订单 -1 关闭订单

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($order_sn) && !empty($shop_id) && !empty($token)) {
                $updorder['status'] = $status;
                $updorder['comment'] = $comment;

                $where['order_sn'] = $order_sn;
                $where['shop_id'] = $shop_id;
                $where['status'] = 2;
                $status = LibF::M('order')->where($where)->save($updorder);
                if ($status) {
                    $this->app->respond(1, '订单已处理');
                } else {
                    $this->app->respond(-1, '订单处理失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 补充接口五：报修订单
     * 
     * @param $shop_id
     */
    public function bxorderAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $status = $this->getRequest()->getPost('status');
            //$start_time = $this->getRequest()->getPost('time_start', $date);
            //$end_time = $this->getRequest()->getPost('time_end', $date);
            //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';
            
            $page = $this->getRequest()->getPost('page', 1);
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($shop_id)) {
                $pagestart = ($page - 1) * $this->pageSize;
                
                $param['status'] = $status;
                $param['start_time'] = $start_time;
                $param['end_time'] = $end_time;
                if ($start_time && $end_time)
                    $where['rq_baoxiu.ctime'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');

                $shopObject = ShopModel::getShopArray();

                $repairModel = new RepairModel();
                $repairObect = $repairModel->getRepairArray();

                //$regionModel = new RegionModel();
                //$regionObject = $regionModel->getRegionObject();

                $where = array('rq_baoxiu.shop_id' => $shop_id);
                $filed = 'rq_baoxiu.*,rq_kehu.card_sn,rq_kehu.mobile_phone,rq_kehu.sheng,rq_kehu.shi,rq_kehu.qu,rq_kehu.cun,rq_kehu.address';
                $dataModel = new Model('baoxiu');
                $data = $dataModel->join('LEFT JOIN rq_kehu ON rq_baoxiu.kid = rq_kehu.kid')->field($filed)->where($where)->order('rq_baoxiu.ctime desc')->limit($pagestart, $this->pageSize)->select();
                if (!empty($data)) {
                    $param['shop_id'] = $shop_id;
                    
                    //获取订单统计数量
                    $dataTotal = $this->getBxOrderStatus($param, '', 'status', '');
                    if (!empty($dataTotal)) {
                        foreach ($dataTotal as &$value) {
                            $value['title'] = isset($this->bxorderStatus[$value['status']]) ? $this->bxorderStatus[$value['status']] : '';
                        }
                    }
                    $returnData['dataTotal'] = $dataTotal;

                    foreach ($data as &$value) {
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
                    $returnData['data'] = $data;

                    $this->app->respond(1, $returnData);
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
    
    /**
     * 补充接口六：报修订单派发
     * 
     * @param $shop_id
     * @param $bxorder_sn
     */
    public function bxpaifaAction() {
        if ($this->getRequest()->isPost()) {

            $bxorder_sn = $this->getRequest()->getPost('bxorder_sn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($bxorder_sn) && !empty($shop_id) && !empty($shipper_id) && !empty($token)) {

                $shiper['shipper_id'] = isset($shipperList[0]) ? $shipperList[0] : 0;
                $shiper['shipper_name'] = isset($shipperList[1]) ? $shipperList[1] : '';
                $shiper['shipper_mobile'] = isset($shipperList[2]) ? $shipperList[2] : '';
                $shiper['status'] = 1;

                $where['encode_id'] = $bxorder_sn;
                $where['status'] = 0;
                $status = LibF::M('baoxiu')->where($where)->save($shiper);
                if ($status) {
                    $platform = 'android'; // 接受此信息的系统
                    $msg_content = json_encode(array('n_builder_id' => $shiper['shipper_id'], 'n_title' => $bxorder_sn, 'n_content' => '报修订单已经派发', 'n_extras' => array('fromer' => $shiper['shipper_id'], 'fromer_name' => $shiper['shipper_name'], 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                    $smsSend = new SmsDataModel();
                    $smsSend->send(16, 3, $shiper['shipper_id'], 1, $msg_content, $platform);

                    $this->app->respond(1, '订单已派发' . $shiper['shipper_name']);
                } else {
                    $this->app->respond(-1, '订单派发失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口七: 退瓶(置换)订单
     * 
     * @param $shop_id
     * @param $start_time
     * @param $end_time
     */
    public function depositAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');
        $status = $this->getRequest()->getPost('status');

        //$start_time = $this->getRequest()->getPost('time_start', $date);
        //$end_time = $this->getRequest()->getPost('time_end', $date);
        //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';

        $type = $this->getRequest()->getPost('type', 1); //1退货 2置换
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shop_id) && !empty($token)) {
            $where['shop_id'] = $param['shop_id'] = $shop_id;
            $where['type'] = $param['type'] = $type;
            $param['start_time'] = $start_time;
            $param['end_time'] = $end_time;
            if ($start_time && $end_time)
                $where['time_created'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');
            if ($status)
                $where['status'] = $param['status'] = $status;

            $pageStart = ($page - 1) * $param['pagesize'];
            $data = LibF::M('deposit_list')->where($where)->limit($pageStart, $param['pagesize'])->order('id desc')->select();
            if (!empty($data)) {
                $dataTotal = $this->getTpOrderStatus($param, '', 'status', '');
                if (!empty($dataTotal)) {
                    foreach ($dataTotal as &$val) {
                        $val['title'] = isset($this->tporderStatus[$val['status']]) ? $this->tporderStatus[$val['status']] : '';
                    }
                }
                $returnData['dataTotal'] = $dataTotal;

                $shopModel = new ShopModel();
                $shopObject = $shopModel->getShopArray();

                //$regionModel = new RegionModel();
                //$regionObject = $regionModel->getRegionObject();
                foreach ($data as &$value) {
                    $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                    $value['type'] = $value['type'];
                    //$value['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                    //$value['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                    //$value['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                    //$value['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                    $value['time'] = date('Y-m-d', $value['time_created']);
                    $value['data'] = (!empty($value['bottle'])) ? json_decode($value['bottle'], true) : array();
                    $value['bottle_text'] = (!empty($value['bottle_text'])) ? json_decode($value['bottle_text'], true) : array();
                    $value['change_data'] = (!empty($value['change_data'])) ? json_decode($value['change_data'], true) : array();
                }
                $returnData['data'] = $data;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-2, '没有数据');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口八：退瓶（置换）派发
     * 
     * @param $shop_id
     * @param $shipper_id
     * @param $shipper_name
     * @param $shipper_mobile
     * 
     */
    public function tppaifAction() {
        if ($this->getRequest()->isPost()) {

            $tporder_sn = $this->getRequest()->getPost('tporder_sn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $shipper_name = $this->getRequest()->getPost('shipper_name');
            $shipper_mobile = $this->getRequest()->getPost('shipper_mobile');

            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($bxorder_sn) && !empty($shop_id) && !empty($shipper_id) && !empty($token)) {

                $shiper['shipper_id'] = isset($shipperList[0]) ? $shipperList[0] : 0;
                $shiper['shipper_name'] = isset($shipperList[1]) ? $shipperList[1] : '';
                $shiper['shipper_mobile'] = isset($shipperList[2]) ? $shipperList[2] : '';
                $shiper['status'] = 1;

                $where['depositsn'] = $tporder_sn;
                $where['status'] = 0;
                $status = LibF::M('deposit_list')->where($where)->save($shiper);
                if ($status) {
                    $platform = 'android'; // 接受此信息的系统
                    $msg_content = json_encode(array('n_builder_id' => $shiper['shipper_id'], 'n_title' => $bxorder_sn, 'n_content' => '退瓶订单已经派发', 'n_extras' => array('fromer' => $shiper['shipper_id'], 'fromer_name' => $shiper['shipper_name'], 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                    $smsSend = new SmsDataModel();
                    $smsSend->send(16, 3, $shiper['shipper_id'], 1, $msg_content, $platform);

                    $this->app->respond(1, '订单已派发' . $shiper['shipper_name']);
                } else {
                    $this->app->respond(-1, '订单派发失败');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口九：折旧订单
     * 
     * @param $shop_id
     * @param $start_time
     * @param $end_time
     */
    public function depreciationAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');
        $status = $this->getRequest()->getPost('status');

        //$start_time = $this->getRequest()->getPost('time_start', $date);
        //$end_time = $this->getRequest()->getPost('time_end', $date);
        //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shop_id) && !empty($token)) {
            $where['rq_order_depreciation.shop_id'] = $shop_id;
            if ($start_time && $end_time) {
                $where['rq_order_depreciation.time_created'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');
                $param['start_time'] = $start_time;
                $param['end_time'] = $end_time;
            }
            if ($status)
                $where['rq_order_depreciation.status'] = $param['status'] = $status;
            
            $pageStart = ($page - 1) * $this->pageSize;

            $orderModel = new Model('order_depreciation');
            $filed = " rq_order_depreciation.*,rq_kehu.user_name ";
            $leftJoin = " LEFT JOIN rq_kehu ON rq_order_depreciation.kid = rq_kehu.kid ";
            $data = $orderModel->join($leftJoin)->field($filed)->where($where)->order('rq_order_depreciation.id desc')->limit($pageStart, $this->pageSize)->select();
            if (!empty($data)) {
                $dataTotal = $this->getZjOrderStatus($param, '', 'status', '');
                if (!empty($dataTotal)) {
                    foreach ($dataTotal as &$val) {
                        $val['title'] = isset($this->zjorderStatus[$val['status']]) ? $this->zjorderStatus[$val['status']] : '';
                    }
                }
                $returnData['dataTotal'] = $dataTotal;

                //$shopModel = new ShopModel();
                //$shopObject = $shopModel->getShopArray();
                //$regionModel = new RegionModel();
                //$regionObject = $regionModel->getRegionObject();
                foreach ($data as &$value) {
                    //$value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                    //$value['sheng_name'] = isset($regionObject[$value['sheng']]) ? $regionObject[$value['sheng']]['region_name'] : '';
                    //$value['shi_name'] = isset($regionObject[$value['shi']]) ? $regionObject[$value['shi']]['region_name'] : '';
                    //$value['qu_name'] = isset($regionObject[$value['qu']]) ? $regionObject[$value['qu']]['region_name'] : '';
                    //$value['cun_name'] = isset($regionObject[$value['cun']]) ? $regionObject[$value['cun']]['region_name'] : '';
                    $value['time'] = date('Y-m-d', $value['time_created']);
                    $value['data'] = json_decode($value['bottle_data'], true);
                }
                $returnData['data'] = $data;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-2, '暂时没有数据');
            }
        }else{
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口十：充值订单
     * 
     * @param $shop_id
     * @param $start_time
     * @param $end_time
     */
    public function balanceorderAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');
        $status = $this->getRequest()->getPost('status');

        //$start_time = $this->getRequest()->getPost('time_start', $date);
        //$end_time = $this->getRequest()->getPost('time_end', $date);
        //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shop_id) && !empty($token)) {
            $where['rq_balance_list.type'] = 1;
            $where['rq_kehu.shop_id'] = $shop_id;
            if ($start_time && $end_time) {
                $where['rq_balance_list.time'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');
                $param['start_time'] = $start_time;
                $param['end_time'] = $end_time;
            }
            if ($status)
                $where['rq_balance_list.status'] = $param['status'] = $status;
            
            $pageStart = ($page - 1) * $this->pageSize;

            $orderModel = new Model('balance_list');
            $filed = " rq_balance_list.*,rq_kehu.user_name ";
            $leftJoin = " LEFT JOIN rq_kehu ON rq_balance_list.kid = rq_kehu.kid ";
            $data = $orderModel->join($leftJoin)->field($filed)->where($where)->order('rq_balance_list.id desc')->limit($pageStart, $this->pageSize)->select();
            if (!empty($data)) {
                $dataTotal = $this->getCzOrderStatus($param, '', 'status', '');
                if (!empty($dataTotal)) {
                    foreach ($dataTotal as &$val) {
                        $val['title'] = isset($this->czorderStatus[$val['status']]) ? $this->czorderStatus[$val['status']] : '';
                    }
                }
                $returnData['dataTotal'] = $dataTotal;

                foreach ($data as &$value) {
                    $value['time'] = !empty($value['time']) ? date('Y-m-d', $value['time']) : '';
                }
                $returnData['data'] = $data;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-2, '暂时没有数据');
            }
        }else{
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口十一：配送计划单据
     * 
     * @param $shop_id
     * @param $start_time
     * @param $end_time
     */
    public function filllistAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');

        //$start_time = $this->getRequest()->getPost('time_start', $date);
        //$end_time = $this->getRequest()->getPost('time_end', $date);
        //$end_time = !empty($end_time) ? $end_time . ' 23:59:59' : '';
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shop_id) && !empty($token)) {
            $where['shop_id'] = $shop_id;
            if ($start_time && $end_time) {
                $where['ctime'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');
                $param['start_time'] = $start_time;
                $param['end_time'] = $end_time;
            }

            $pageStart = ($page - 1) * $this->pageSize;

            $fillModel = new Model('filling');
            $filed = " id,filling_no,shop_id,ps_bottle,ps_product,hk_bottle,hk_product,ps_time,comment,status,hk_status,tb_status,ctime ";
            $data = $fillModel->field($filed)->where($where)->order('id desc')->limit($pageStart, $this->pageSize)->select();
            if (!empty($data)) {
                foreach ($data as &$value) {
                    $value['time'] = !empty($value['ctime']) ? date('m-d H:i', $value['ctime']) : '';
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-2, '暂时没有数据');
            }
        }else{
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 补充接口十二：配送计划单详情
     * 
     * @param $shop_id
     * @param $filling_no
     */
    public function filldetialAction() {
        $shop_id = $this->getRequest()->getPost('shop_id');
        $filling_no = $this->getRequest()->getPost('filling_no');
        $token = $this->getRequest()->getPost('token', 1234);

        $page = $this->getRequest()->getPost('page', 1);
        $param['pagesize'] = $this->pageSize;

        if (!empty($shop_id) && !empty($filling_no) && !empty($token)) {

            $file = "sum(num) as total,ftype,type,fid";
            $data = LibF::M('filling_detail')->field($file)->where(array('filling_no' => $filling_no))->group('ftype,type,fid')->order('ftype asc,type asc')->select();
            if (!empty($data)) {
                
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();

                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $commitObject = array();
                $fillingstocklogModel = new FillingModel();
                $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
                if (!empty($productObject)) {
                    foreach ($productObject as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                        $commitObject[$value['id']] = $value;
                    }
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-2, '暂时没有数据');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

//获取订单分类统计
    protected function getOrderStatus($param = array(), $filekey = '', $groupkey = '', $orderkey = '') {

        $where = array();
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        if (isset($param['shipper_id']) && !empty($param['shipper_id']))
            $where['shipper_id'] = $param['shipper_id'];
        //if (isset($param['status']) && !empty($param['status']))
           // $where['status'] = $param['status'];
        if (isset($param['start_time']) && isset($param['end_time']))
            $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

        $grouplist = $orderlist = "";
        $filelist = "*";
        if (!empty($groupkey)) {
            $grouplist = $groupkey;
            $orderlist = !empty($orderkey) ? $orderkey . " DESC " : " total DESC ";
            $filelist = !empty($filekey) ? $filekey : $groupkey . ", count(*) as total ";
        }

        $data = LibF::M('order')->field($filelist)->where($where)->group($grouplist)->order($orderlist)->select();
        return $data;
    }
    
//获取保修订单分类统计
    protected function getBxOrderStatus($param = array(), $filekey = '', $groupkey = '', $orderkey = '') {
        $where = array();
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        if (isset($param['status']) && !empty($param['status']))
            $where['status'] = $param['status'];
        if (isset($param['start_time']) && isset($param['end_time']))
            $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

        $grouplist = $orderlist = "";
        $filelist = "*";
        if (!empty($groupkey)) {
            $grouplist = $groupkey;
            $orderlist = !empty($orderkey) ? $orderkey . " DESC " : " total DESC ";
            $filelist = !empty($filekey) ? $filekey : $groupkey . ", count(*) as total ";
        }

        $data = LibF::M('baoxiu')->field($filelist)->where($where)->group($grouplist)->order($orderlist)->select();
        return $data;
    }

//获取退瓶订单分类统计
    protected function getTpOrderStatus($param = array(), $filekey = '', $groupkey = '', $orderkey = '') {
        $where = array();
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        if (isset($param['status']) && !empty($param['status']))
            $where['status'] = $param['status'];
        if (isset($param['start_time']) && isset($param['end_time']))
            $where['time_created'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

        $grouplist = $orderlist = "";
        $filelist = "*";
        if (!empty($groupkey)) {
            $grouplist = $groupkey;
            $orderlist = !empty($orderkey) ? $orderkey . " DESC " : " total DESC ";
            $filelist = !empty($filekey) ? $filekey : $groupkey . ", count(*) as total ";
        }

        $data = LibF::M('deposit_list')->field($filelist)->where($where)->group($grouplist)->order($orderlist)->select();
        return $data;
    }
    
//获取折旧订单分类统计
    protected function getZjOrderStatus($param = array(), $filekey = '', $groupkey = '', $orderkey = '') {
        $where = array();
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        if (isset($param['status']) && !empty($param['status']))
            $where['status'] = $param['status'];
        if (isset($param['start_time']) && isset($param['end_time']))
            $where['time_created'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

        $grouplist = $orderlist = "";
        $filelist = "*";
        if (!empty($groupkey)) {
            $grouplist = $groupkey;
            $orderlist = !empty($orderkey) ? $orderkey . " DESC " : " total DESC ";
            $filelist = !empty($filekey) ? $filekey : $groupkey . ", count(*) as total ";
        }

        $data = LibF::M('order_depreciation')->field($filelist)->where($where)->group($grouplist)->order($orderlist)->select();
        return $data;
    }
    
//获取充值订单分类统计
    protected function getCzOrderStatus($param = array(), $filekey = '', $groupkey = '', $orderkey = '') {
        $where = array();
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['rq.kehu.shop_id'] = $param['shop_id'];
        if (isset($param['status']) && !empty($param['status']))
            $where['rq_balance_list.status'] = $param['status'];
        if (isset($param['start_time']) && isset($param['end_time']))
            $where['rq_balance_list.time'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

        $orderModel = new Model('balance_list');
        $filed = " rq_balance_list.status,count(*) as total ";
        $leftJoin = " LEFT JOIN rq_kehu ON rq_balance_list.kid = rq_kehu.kid ";
        $data = $orderModel->join($leftJoin)->field($filed)->where($where)->group('rq_balance_list.status')->order('total desc')->select();
        return $data;
    }

    /** *
     * 备注：
     * 
     * 
     * //基于销售统计
     * 
     * 业绩排名：根据时间段查询，当前没给门店的销售统计列表，数据每隔一段时间统计一次数据，不是事实显示最新结果，创建一个新的数据表存储
     * 应收账款：根据时间按照月份，查询当前门店用户即将到期未收的账款
     * 新客户：  根据时间段查询统计，当前门店的新用户数量，当前用户的订单次数
     * 回流率:   根据时间段统计，当前钢瓶的流转情况，计算回流率
     * 销售统计：根据时间段查询统计，当前销售列表（钢瓶、废旧钢瓶、残液、配件），按照类型、数量、交易金额统计
     * 
     * //基于库存统计
     * 
     * 瓶库存：根据时间段统计当前库存数据，存储相关缓存数据
     * 配件库存：根据时间段统计当前配件库存数据，存储相关缓存数据
     * 充装统计：所有充装记录（充装气种类统计）
     * 进货统计：气站进货的统计记录（气种类）
     *
     */
}
