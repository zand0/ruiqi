<?php

/**
 * @app通用
 */
class ApppoolController extends \Com\Controller\Common {

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
     * 模板类
     * @var 
     */
    protected $searchObject;
    
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

        $this->searchObject = array(1 => '今日', 2 => '昨天', 3 => '本周', 4 => '上周', 5 => '本月', 6 => '上月', 7 => '本季度', 8 => '本年度');
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
                $parent_record = LibF::M('admin_user')->where('mobile_phone = ' . $mobile . " AND status = 0 ")->find();
                if (empty($parent_record)) {
                    $this->app->respond(-1, '此账号不存在');
                } else if ($parent_record['password'] != md5(md5($password) . $parent_record['user_salt'])) {
                    $this->app->respond(-2, '密码错误');
                } else {
                    //判断当前验证吗是否正确
                    $token = 123456;
                    $data = array('mobile' => $mobile, 'user_id' => $parent_record['user_id'], 'username' => $parent_record['username'], 'token' => $token);
                    $data['role_id'] = $parent_record['roles'];
                    $data['shop_id'] = $parent_record['shop_id'];
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

    //接口1.1消息通知
    public function newslistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $page = $this->getRequest()->getPost('page', 1);

            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $appDataModel = new AppdatainfoModel();
            $data = $appDataModel->getNewList($param);
            if (!empty($data)) {
                foreach ($data as &$value) {
                    $value['time'] = date('Y-m-d', $value['time_created']);
                }
            }else{
                $data = array();
            }
            if (!empty($token)) {
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口1.2公文审批
    public function approvallistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $user_id = $this->getRequest()->getPost('user_id');

            $page = $this->getRequest()->getPost('page', 1);

            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            $param['user_id'] = $user_id;
            
            $appDataModel = new AppdatainfoModel();
            $data = $appDataModel->getApproveList($param);
            if (!empty($data)) {
                $statusObject = array(0 => '未审批', 1 => '已审批', 2 => '拒绝');
                foreach ($data as &$value) {
                    $value['time'] = date('Y-m-d', $value['time_created']);
                    $value['text'] = $statusObject[$value['approval_status']];
                    if ($value['approval_annex'])
                        $value['approval_annex'] = $this->url . $value['approval_annex'];
                }
            }else{
                $data = array();
            }
            if (!empty($token)) {
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口2.1销售额简报
    public function saleslistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            $search_type = $this->getRequest()->getPost('search_type', 1); //1 => '今日'2 => '昨天'3 => '本周'4 => '上周'5 => '本月'6 => '上月'7 => '本季度'8 => '本年度'
            $page = $this->getRequest()->getPost('page', 1);

            $data = array();
            $date = date("Y-m-d");
            
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            
            if (!empty($token)) {
                $timeData = new TimedataModel();
                $nowMonth = $timeData->month_firstday(0, true);

                $statisticModel = new StatisticsDataModel();
                $monthTotal = $statisticModel->salesTotal($nowMonth, 0, $shop_id, 4); //本月销售统计

                $nowWeek = $timeData->this_monday(0, true);
                $weekTotal = $statisticModel->salesTotal($nowWeek, 0, $shop_id, 4); //本周销售额统计

                $nowTime = strtotime($date);
                $dayTotal = $statisticModel->salesTotal($nowTime, 0, $shop_id, 4); //今天销售统计

                $total = 0;     //销售额
                $yjtotal = 0;   //押金统计
                switch ($search_type) {
                    case '2'://昨天
                        $ztdate = date("Y-m-d", strtotime("-1 day"));
                        $param['start_time'] = $ztdate;
                        $param['end_time'] = $ztdate . ' 23:59:59';

                        $salesData = $statisticModel->salesTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4);
                        $total = $salesData;
                        //$yjtotal = isset($salesData['yjtotal']) ? $salesData['yjtotal'] : 0;

                        break;
                    case '3'://本周
                        $bzdate = $timeData->this_monday(0, false);

                        $param['start_time'] = $bzdate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $total = $weekTotal;
                        //$yjtotal = isset($weekTotal['yjtotal']) ? $weekTotal['yjtotal'] : 0;
                        break;
                    case '4'://上周
                        $szdate = $timeData->last_monday(0, false);
                        $bzdate = $timeData->this_monday(0, false);

                        $param['start_time'] = $szdate;
                        $param['end_time'] = $bzdate;
                        
                        $salesData = $statisticModel->salesTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4);
                        $total = $salesData;
                        //$yjtotal = isset($salesData['yjtotal']) ? $salesData['yjtotal'] : 0;
                        break;
                    case 5: //本月

                        $bydate = $timeData->month_firstday(0, false);

                        $param['start_time'] = $bydate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $total = $monthTotal;
                        //$yjtotal = isset($monthTotal['yjtotal']) ? $monthTotal['yjtotal'] : 0;
                        break;
                    case 6: //上月
                        $sydate = $timeData->lastmonth_firstday(0, FALSE);
                        $bydate = $timeData->month_firstday(0, false);

                        $param['start_time'] = $sydate;
                        $param['end_time'] = $bydate;
                        
                        $salesData = $statisticModel->salesTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4);
                        $total = $salesData;
                        //$yjtotal = isset($salesData['yjtotal']) ? $salesData['yjtotal'] : 0;
                        break;
                    case 7://本季度
                        $bjdate = $timeData->season_firstday(0, FALSE);

                        $param['start_time'] = $bjdate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $salesData = $statisticModel->salesTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4);
                        $total = $salesData;
                        //$yjtotal = isset($salesData['yjtotal']) ? $salesData['yjtotal'] : 0;
                        break;
                    case 8://本年
                        $bndate = date('Y-01-01');

                        $param['start_time'] = $bndate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $salesData = $statisticModel->salesTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4);
                        $total = $salesData;
                        //$yjtotal = isset($salesData['yjtotal']) ? $salesData['yjtotal'] : 0;
                        break;
                    case 1:
                    default :
                        $param['start_time'] = $this->getRequest()->getPost('time_start', $date);
                        $param['end_time'] = $this->getRequest()->getPost('time_end', $date);
                        $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';

                        $salesData = $statisticModel->salesTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4);
                        $total = $salesData;
                        //$yjtotal = isset($salesData['yjtotal']) ? $salesData['yjtotal'] : 0;
                        break;
                }

                $statisticsModel = new StatisticsDataModel();
                $data = $statisticsModel->salesShopData(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, 4, $param); //获取门店用户销售额(默认当天)

                $returnData['dayTotal'] = $dayTotal;
                $returnData['weekTotal'] = $weekTotal;
                $returnData['monthTotal'] = $monthTotal;
                $returnData['total'] = $total;
                $returnData['yjtotal'] = $yjtotal;
                $returnData['data'] = !empty($data) ? $data : array();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口2.2 应收入简报
    public function reportlistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            $page = $this->getRequest()->getPost('page', 1);

            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            $param['shop_id'] = $shop_id;

            $shopObject = ShopModel::getShopArray();
            
            $data = array();
            $total = $yjtotal = 0;
            if (!empty($token)) {
                
                $statisticModel = new StatisticsDataModel();
                $reportData = $statisticModel->reportShopMoney($shop_id, $param);

                $title = $show = $showData = $reportNewData = array();
                if (!empty($reportData)) {
                    foreach ($reportData as &$value) {
                        $reportNewData[$value['shop_id']][$value['arrears_type']] = $value['money'];
                    }
                    if (!empty($reportNewData)) {
                        //$shopAdminUser = $statisticModel->getStoreAdminUser();
                        foreach ($reportNewData as $key => $val) {
                            if ($key > 0) {
                                $reportVal['shop_name'] = isset($shopObject[$key]) ? $shopObject[$key]['shop_name'] : '未分配门店';
                                $reportVal['user_name'] = isset($shopObject[$key]) ? $shopObject[$key]['admin_name'] : '';
                                $reportVal['total'] = (isset($val[1]) && !empty($val[1])) ? $val[1] : 0;
                                $total += $reportVal['total'];
                                $reportVal['yjtotal'] = (isset($val[2]) && !empty($val[2])) ? $val[2] : 0;
                                $yjtotal += $reportVal['yjtotal'];
                                $data[] = $reportVal;
                            }
                        }
                    }
                    
                }
                $returnData['data'] = $data;
                $returnData['total'] = $total;
                $returnData['yjtotal'] = $yjtotal;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口2.3 新开户简报
    public function newkehulistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            $search_type = $this->getRequest()->getPost('search_type', 1); //1 => '今日'2 => '昨天'3 => '本周'4 => '上周'5 => '本月'6 => '上月'7 => '本季度'8 => '本年度'
            $page = $this->getRequest()->getPost('page', 1);

            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $timeData = new TimedataModel();
            
            $date = date('Y-m-d');
            if (!empty($token)) {
                $status = 0; //当前欠款金额状态
                $total = 0;
                
                $nowMonth = $timeData->month_firstday(0, true);
                $statisticModel = new StatisticsDataModel();
                $monthTotal = $statisticModel->kehuTotal($nowMonth, 0, $shop_id); //本月新增用户统计

                $nowWeek = $timeData->this_monday(0, true);
                $weekTotal = $statisticModel->kehuTotal($nowWeek, 0, $shop_id); //本周新增用户统计

                $nowTime = strtotime($date);
                $dayTotal = $statisticModel->kehuTotal($nowTime, 0, $shop_id); //今天新增用户统计

                switch ($search_type) {
                    case '2'://昨天
                        $ztdate = date("Y-m-d", strtotime("-1 day"));
                        $param['start_time'] = $ztdate;
                        $param['end_time'] = $ztdate . ' 23:59:59';

                        $kehuTotal = $statisticModel->kehuTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id); //本周新增用户统计
                        $total = $kehuTotal;

                        break;
                    case '3'://本周
                        $bzdate = $timeData->this_monday(0, false);

                        $param['start_time'] = $bzdate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $total = $weekTotal;
                        break;
                    case '4'://上周
                        $szdate = $timeData->last_monday(0, false);
                        $bzdate = $timeData->this_monday(0, false);

                        $param['start_time'] = $szdate;
                        $param['end_time'] = $bzdate;
                        
                        $kehuTotal = $statisticModel->kehuTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id); //本周新增用户统计
                        $total = $kehuTotal;
                        break;
                    case 5: //本月
                        $bydate = $timeData->month_firstday(0, false);

                        $param['start_time'] = $bydate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $total = $monthTotal;
                        break;
                    case 6: //上月
                        $sydate = $timeData->lastmonth_firstday(0, FALSE);
                        $bydate = $timeData->month_firstday(0, false);

                        $param['start_time'] = $sydate;
                        $param['end_time'] = $bydate;
                        
                        $kehuTotal = $statisticModel->kehuTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id); //本周新增用户统计
                        $total = $kehuTotal;
                        break;
                    case 7://本季度
                        $bjdate = $timeData->season_firstday(0, FALSE);

                        $param['start_time'] = $bjdate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $kehuTotal = $statisticModel->kehuTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id); //本周新增用户统计
                        $total = $kehuTotal;
                        break;
                    case 8://本年
                        $bndate = date('Y-01-01');

                        $param['start_time'] = $bndate;
                        $param['end_time'] = $date . ' 23:59:59';
                        
                        $kehuTotal = $statisticModel->kehuTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id); //本周新增用户统计
                        $total = $kehuTotal;
                        break;
                    case 1:
                    default :
                        $param['start_time'] = $this->getRequest()->getPost('time_start', $date);
                        $param['end_time'] = $this->getRequest()->getPost('time_end', $date);
                        $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';
                        
                        $kehuTotal = $statisticModel->kehuTotal(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id); //本周新增用户统计
                        $total = $kehuTotal;
                        break;
                }

                $data = $statisticModel->kehuData(strtotime($param['start_time']), strtotime($param['end_time']), $shop_id, $param); //客户统计列表

                $returnData['dayTotal'] = $dayTotal;
                $returnData['monthTotal'] = $monthTotal;
                $returnData['weekTotal'] = $weekTotal;
                $returnData['data'] = !empty($data) ? $data : array();
                $returnData['total'] = $total;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口2.4 回流率简报
    public function quarterlistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);

            $data = array();
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('time_start');
            $param['end_time'] = $this->getRequest()->getPost('time_end');
            if (!empty($token)) {
                $returnData = array();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口2.5 库存量简报直接调用接口7库存
    //接口3订单统计
    public function orderlistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            $page = $this->getRequest()->getPost('page', 1);

            $data = array();
            
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time',$date);
            $param['end_time'] = $this->getRequest()->getPost('end_time',$date);
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            $param['shop_id'] = $shop_id;

            $appDataModel = new AppdatainfoModel();
            $nowData = $appDataModel->getNowOrder($param);
            $monthData = $appDataModel->getMonthOrder($param);

            $total = 0;
            $type = $this->getRequest()->getPost('type', 'all');
            if (!empty($token)) {
                $shopModel = new ShopModel();
                $shopObject = $shopModel->shopArrayData();
                switch ($type) {
                    case 'all':
                        //获取所有订单
                        $orderTotal = $appDataModel->orderTotal($param);

                        //获取所有门店订单数量
                        $data = $appDataModel->getOrderTotal($param);
                        if (!empty($data)) {
                            foreach ($data as &$value) {
                                $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                                $account = ($orderTotal>0) ? $value['total'] / $orderTotal : 0;
                                $value['accounting'] = sprintf("%.2f", $account * 100);
                            }
                        }
                        $total = $orderTotal;
                        break;
                    case 'done':
                        //获取已完成订单列表
                        $param['status'] = 4;
                        $returnData = $appDataModel->getorderList($param);
                        if (!empty($returnData)) {
                            $data = $returnData['data'];
                            foreach ($data as &$value) {
                                $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                            }
                            $total = $returnData['dataTotal'];
                        }
                        break;
                    case 'nodone':
                        //获取已完成订单列表
                        $param['status'] = array('in', '1, 2');
                        $returnData = $appDataModel->getorderList($param);
                        if (!empty($returnData)) {
                            $data = $returnData['data'];
                            foreach ($data as &$value) {
                                $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                            }
                            $total = $returnData['dataTotal'];
                        }
                        break;
                }

                $returnData = array();
                $returnData['nowdata'] = $nowData;
                $returnData['monthdata'] = $monthData;
                $returnData['data'] = !empty($data) ? $data : array();
                $returnData['total'] = $total;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口4.1 门店配送计划单
    public function shopfillingAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $param['shop_id'] = $this->getRequest()->getPost('shop_id', 0);
            $page = $this->getRequest()->getPost('page', 1);

            $data = array();
            
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('time_start');
            $param['end_time'] = $this->getRequest()->getPost('time_end');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            if (!empty($token)) {
                $where = array();
                if ($param['start_time'] && $param['end_time'])
                    $where['rq_filling.ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');
                if (isset($param['shop_id']) && !empty($param['shop_id']))
                    $where['rq_filling.shop_id'] = $param['shop_id'];

                $pageStart = ($page - 1) * $this->pageSize;

                $fillingModel = new Model('filling');
                $field = 'rq_filling.filling_no,rq_filling.shop_id,rq_filling.ps_time,rq_filling.status,rq_filling.comment,rq_shop.shop_name';
                $data = $fillingModel->join('LEFT JOIN rq_shop ON rq_filling.shop_id = rq_shop.shop_id')->field($field)->where($where)->order('rq_filling.id desc')->limit($pageStart,$this->pageSize)->select();

                $data = !empty($data) ? $data : array();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口4.2 门店配送计划详情
    public function fillingdetialAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $filling_no = $this->getRequest()->getPost('filling_no', '');
            if (!empty($token) && !empty($filling_no)) {

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

                //备注ftype 1配送钢瓶 2瓶配件 3回库钢瓶 4回库配件
                // type 1空瓶 3重瓶  2废瓶  4普通屏
                // fid  产品 id
                $typeObject = array(1 => '空瓶', 2 => '废瓶', 3 => '重瓶', 4 => '普通瓶');
                $ftypeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');

                $returnData = array('ps' => array(), 'hk' => array());
                $file = "sum(num) as total,ftype,type,fid";
                $data = LibF::M('filling_detail')->field($file)->where(array('filling_no' => $filling_no))->group('ftype,type,fid')->order('ftype asc,type asc')->select();
                if (!empty($data)) {
                    foreach ($data as $value) {
                        switch ($value['ftype']) {
                            case 1:
                                $val['title'] = '重瓶';
                                $val['type'] = $bottleTypeObject[$value['fid']]['bottle_name'];
                                $val['total'] = $value['total'];

                                $returnData['ps'][] = $val;
                                break;
                            case 2:
                                $val['title'] = $commitObject[$value['fid']]['name'];
                                $val['type'] = $commitObject[$value['fid']]['typename'];
                                $val['total'] = $value['total'];

                                $returnData['ps'][] = $val;
                                break;
                            case 3:
                                $val['title'] = $ftypeObject[$value['type']];
                                $val['type'] = $bottleTypeObject[$value['fid']]['bottle_name'];
                                $val['total'] = $value['total'];

                                $returnData['hk'][] = $val;
                                break;
                            case 4:
                                $val['title'] = $commitObject[$value['fid']]['name'];
                                $val['type'] = $commitObject[$value['fid']]['typename'];
                                $val['total'] = $value['total'];

                                $returnData['hk'][] = $val;
                                break;
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

    //接口4.3 门店确认单
    public function shopconfirmAction(){
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');

            $page = $this->getRequest()->getPost('page', 1);

            $data = array(); 
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('startTimes');
            $param['end_time'] = $this->getRequest()->getPost('endTimes');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            if (!empty($token)) {
                $mdshenhe = $where = array();
                //$mdWHere['confirme_no'] = $confirme_no;
                if(!empty($shop_id)){
                    $mdWHere['shop_id'] = $shop_id;
                    $where['shop_id'] = $shop_id;
                }
                $mdshData = LibF::M('confirme_store_detail_sp')->where($mdWHere)->select();
                if (!empty($mdshData)) {
                    foreach ($mdshData as $mVal) {
                        $mdshenhe[$mVal['shop_id']][$mVal['confirme_no']] = $mVal['status'];
                    }
                }

                if ($param['start_time'] && $param['end_time'])
                    $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('confirme')->field('confirme_no,money,license_plate,time,status')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['mstatus'] = isset($mdshenhe[$value['shop_id']][$value['confirme_no']]) ? $mdshenhe[$value['shop_id']][$value['confirme_no']] : 0;
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

    //接口4.4 门店确认单详情
    public function confirmdetialAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $confirme_no = $this->getRequest()->getPost('confirme_no', '');
            $shop_id = $this->getRequest()->getPost('shop_id');
            if (!empty($token) && !empty($confirme_no)) {
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

                $filed = "rq_confirme_detail.shop_id,rq_confirme_detail.ftype,rq_confirme_detail.type,rq_confirme_detail.num,rq_confirme_detail.total,rq_confirme_detail.total,rq_shop.shop_name";

                $where = array('rq_confirme_detail.confirme_no' => $confirme_no);
                if(!empty($shop_id))
                    $where['rq_confirme_detail.shop_id'] = $shop_id;
                $deliveryModel = new Model('confirme_detail');
                $data = $deliveryModel->join('LEFT JOIN rq_shop ON rq_confirme_detail.shop_id = rq_shop.shop_id')->field($filed)->where($where)->select();

                if (!empty($data)) {
                    foreach ($data as &$value) {
                        if ($value['ftype'] == 1) {
                            $value['typename'] = '燃气';
                            $value['fname'] = $bottleTypeObject[$value['type']]['bottle_name'];
                        } else {
                            $value['typename'] = $commitObject[$value['type']]['name'];
                            $value['fname'] = $commitObject[$value['type']]['typename'];
                        }
                    }
                }else{
                    $data = array();
                }

                //门店回库确认单
                $hfiled = " rq_confirme_store_detail.shop_id,rq_confirme_store_detail.ftype,rq_confirme_store_detail.type,rq_confirme_store_detail.num,rq_shop.shop_name,rq_confirme_store_detail.status ";

                $hWhere = array('rq_confirme_store_detail.confirme_no' => $confirme_no);
                if(!empty($shop_id))
                    $hWhere['rq_confirme_store_detail.shop_id'] = $shop_id;
                
                $hconfirmModel = new Model('confirme_store_detail');
                $hdata = $hconfirmModel->join('LEFT JOIN rq_shop ON rq_confirme_store_detail.shop_id = rq_shop.shop_id')->field($hfiled)->where($hWhere)->select();
                if (!empty($hdata)) {
                    $ftypeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                    foreach ($hdata as &$value) {
                        if ($value['ftype'] == 0) {
                            $value['typename'] = $commitObject[$value['type']]['name'];
                            $value['fname'] = $commitObject[$value['type']]['typename'];
                            $value['total'] = 0;
                        }else{
                            $value['typename'] = $ftypeObject[$value['ftype'] ];
                            $value['fname'] = $bottleTypeObject[$value['type']]['bottle_name'];
                            $value['total'] = 0;
                        }
                    }
                }
                $returnData['rk'] = $data;
                $returnData['ck'] = $hdata;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口4.5 气站出库单
    public function deliverylistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $page = $this->getRequest()->getPost('page', 1);
            $role_id = $this->getRequest()->getPost('role_id');
            $user_id = $this->getRequest()->getPost('user_id');

            $data = array();
            
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            $date = date("Y-m-d");
            
            $where = array();
            //判断是不是押运员
            if ($role_id) {
                $userModel = new Model('quarters');
                $leftJoin = "LEFT JOIN rq_auth_role ON rq_quarters.id = rq_auth_role.quarters_id ";
                $filed = "rq_auth_role.id,rq_quarters.quarters_id";
                $wheres = array('rq_auth_role.id' => $role_id);
                $roleData = $userModel->join($leftJoin)->field($filed)->where($wheres)->find();
                if (!empty($roleData)) {
                    if ($roleData['quarters_id'] == 2) {
                        $where['guards_id'] = $user_id;
                    }
                }
            }

            $param['start_time'] = $this->getRequest()->getPost('time_start');
            $param['end_time'] = $this->getRequest()->getPost('time_end');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            if (!empty($token)) {
                
                if ($param['start_time'] && $param['end_time'])
                    $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('delivery')->field('delivery_no,license_plate,guards,ck_time,status')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                $data = !empty($data) ? $data : array();
                
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口4.6 气站出库单详情
    public function deliveryinfoAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $delivery_no = $this->getRequest()->getPost('delivery_no', '');
            if (!empty($token) && !empty($delivery_no)) {
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

                $shopObject = ShopModel::getShopArray();
                $ftypeObject = array(0 => '重瓶', 1 => '配件');

                $file = "shop_id,ftype,fid,num,money,total";
                $data = LibF::M('delivery_detail')->field($file)->where(array('delivery_no' => $delivery_no))->order('ftype asc')->select();

                $jsonData = array();
                if (!empty($data)) {
                    $returnData = array();

                    foreach ($data as $value) {
                        $value['price'] = 0;
                        $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                        if ($value['ftype'] == 1) {
                            $value['fname'] = '燃气';
                            $value['typename'] = $bottleTypeObject[$value['fid']]['bottle_name'];
                        } else {
                            $value['fname'] = $commitObject[$value['fid']]['name'];
                            $value['typename'] = $commitObject[$value['fid']]['typename'];
                        }
                        $returnData[$value['shop_id']][] = $value;
                    }
                    if (!empty($returnData)) {
                        foreach ($returnData as $key => $value) {
                            $shopData = array();
                            $shopData['shop_id'] = $key;
                            $shopData['shop_name'] = $shopObject[$key]['shop_name'];
                            $shopData['data'] = $value;
                            $jsonData['ck'][] = $shopData;
                        }
                    }
                }else{
                    $data = array();
                }
                //获取回库数据
                $where['confirme_no'] = $delivery_no;
                $dataInfo = LibF::M('confirme_store_detail')->where($where)->order('ftype asc,type asc')->select();
                if (!empty($dataInfo)) {
                    $rData = $mdData = array();
                    $ftypeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
                    foreach ($dataInfo as &$value) {
                        if ($value['ftype'] == 0) {
                            $value['fname'] = $commitObject[$value['type']]['name'];
                            $value['typename'] = $commitObject[$value['type']]['typename'];
                        } else {
                            $value['fname'] = $ftypeObject[$value['ftype']];
                        }
                        $mdData[] = $value['shop_id'];
                        $rData[$value['shop_id']][] = $value;
                    }
                    if (!empty($rData)) {
                        $mdshenhe = array();
                        $mdWHere['confirme_no'] = $delivery_no;
                        $mdWHere['shop_id'] = array('in',  array_unique($mdData));
                        $mdshData = LibF::M('confirme_store_detail_sp')->where($mdWHere)->select();
                        if(!empty($mdshData)){
                            foreach ($mdshData as $mVal){
                                $mdshenhe[$mVal['shop_id']] = $mVal['status'];
                            }
                        }
                        
                        foreach ($rData as $k => $val) {
                            $shopData = array();
                            $shopData['shop_id'] = $k;
                            $shopData['status'] = isset($mdshenhe[$k]) ? $mdshenhe[$k] : 0;  //0未审核1押运员审核2气站管理员审核
                            $shopData['shop_name'] = $shopObject[$k]['shop_name'];
                            $shopData['data'] = $val;
                            $jsonData['hk'][] = $shopData;
                        }
                    }
                }

                $this->app->respond(1, $jsonData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口4.7 气站入库单（回库确认单）
    public function confirmbacklistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');

            $page = $this->getRequest()->getPost('page', 1);

            $data = array();

            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $date = date('Y-m-d');
            $param['start_time'] = $this->getRequest()->getPost('time_start');
            $param['end_time'] = $this->getRequest()->getPost('time_end');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            if (!empty($token)) {
                $where = array();
                
                $pageStart = ($page - 1)*$this->pageSize;
                if ($shop_id > 0) {
                    if ($param['start_time'] && $param['end_time'])
                        $where['rq_confirme_store_detail.ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                    $storeModel = new Model('confirme_store_detail');
                    $file = 'rq_confirme_store_detail.confirme_no,rq_confirme_store.license_plate,rq_confirme_store.guards,rq_confirme_store.time,rq_confirme_store_detail.status ';
                    $data = $storeModel->join("LEFT JOIN rq_confirme_store ON rq_confirme_store_detail.confirme_no = rq_confirme_store.confirme_no")->field($file)->where($where)->limit($pageStart,$this->pageSize)->select();
                } else {
                    if ($param['start_time'] && $param['end_time'])
                        $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                    $data = LibF::M('confirme_store')->field('confirme_no,license_plate,guards,time,status')->where($where)->order('time desc')->limit($pageStart,$this->pageSize)->select();
                }
                $data = !empty($data) ? $data : array();
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口4.8 气站入库单详情
    public function confirmbackdetialAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $confirme_no = $this->getRequest()->getPost('confirme_no', '');
            $shop_id = $this->getRequest()->getPost('shop_id');
            if (!empty($token) && !empty($confirme_no)) {
                $where['confirme_no'] = $confirme_no;
                if ($shop_id > 0)
                    $where['shop_id'] = $shop_id;

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
                $data = LibF::M('confirme_store_detail')->field('ftype,type,typename,sum(num) as total')->where($where)->group('ftype,type')->select();
                if (!empty($data)) {
                    $ftypeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                    foreach ($data as &$value) {
                        if ($value['ftype'] == 0) {
                            $value['name'] = isset($commitObject[$value['type']]) ? $commitObject[$value['type']]['name'] : '';
                            $value['typename'] = isset($commitObject[$value['type']]) ? $commitObject[$value['type']]['typename'] : '';
                        } else {
                            $value['name'] = $ftypeObject[$value['ftype']];
                        }
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

    //接口5订单客户统计
    public function kehulistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            $param['shop_id'] = $shop_id;

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time');
            $param['end_time'] = $this->getRequest()->getPost('end_time');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';

            $appDataModel = new AppdatainfoModel();
            $type = $this->getRequest()->getPost('type', 'all');

            $kehuTotal = $appDataModel->kehuTotal($param);  //客户总量
            $kehuTypeTotal = $appDataModel->kehuTypeTotal($param); //客户类型数量

            $totalData['total'] = $kehuTotal;
            $totalData['resident'] = isset($kehuTypeTotal[1]) ? $kehuTypeTotal[1] : 0;
            $totalData['business'] = isset($kehuTypeTotal[2]) ? $kehuTypeTotal[2] : 0;

            if (!empty($token)) {
                $shopModel = new ShopModel();
                $shopObject = $shopModel->shopArrayData();
                switch ($type) {
                    case 'all':
                        //获取所有门店用户数量 
                        $data = $appDataModel->getKehuTotal($param);
                        if (!empty($data)) {
                            foreach ($data as &$value) {
                                $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '未分配门店';
                                $account = $value['total'] / $kehuTotal;
                                $value['accounting'] = sprintf("%.2f", $account * 100);
                            }
                        }
                        break;
                    case 'other':
                        $param['ktype'] = $this->getRequest()->getPost('ktype', 1); //1商业2居民
                        $data = $appDataModel->getKehuList($param);
                        if (!empty($data)) {
                            foreach ($data as &$value) {
                                $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '未分配门店';
                                $value['type_name'] = ($value['paytype'] == 1) ? '月结' : '日结';
                                $value['card_sn'] = $value['card_sn'];
                                $value['ctime'] = ($value['ctime'] > 0) ? date('Y-m-d', $value['ctime']) : '';
                            }
                        }
                        break;
                }
                $returnData = array();
                $returnData['totalData'] = $totalData;
                $returnData['data'] = !empty($data) ? $data : array();
                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口6充装计划单
    public function fillinglistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $date = date("Y-m-d");
            $param = array();
            $param['start_time'] = $this->getRequest()->getPost('start_time');
            $param['end_time'] = $this->getRequest()->getPost('end_time');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';

            if (!empty($token)) {
                $appDataModel = new AppdatainfoModel();
                $data = $appDataModel->fiilingList($param);
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        if($value['status'] == 0){
                            $value['text'] = '未派发';
                        }else{
                            $value['text'] = '已派发';
                        }
                        $value['time'] = date('Y-m-d', $value['ctime']);
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

    //接口6.1充装详情
    public function fillingdetailAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $param['filling_no'] = $this->getRequest()->getPost('filling_no');

            if (!empty($token) && !empty($param['filling_no'])) {
                $appDataModel = new AppdatainfoModel();
                $data = $appDataModel->fillingDetial($param);
                $data = !empty($data) ? $data : array();
                
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口7库存
    public function inventorylistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $type = $this->getRequest()->getPost('type', 'gas'); //gas 燃气 bottle 钢瓶 product配件

            $returnData = array();
            $tank_gasTotal = LibF::M('tank_gas')->field('sum(volume) as num')->find();  //燃气总量
            $returnData['gasTotal'] = $tank_gasTotal['num'];
            $dataTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,type')->group('type')->select();
            $bottleTotal = $productTotal = 0; //钢瓶、配件总量
            if (!empty($dataTotal)) {
                foreach ($dataTotal as $value) {
                    if ($value['type'] == 1) {
                        $bottleTotal = $value['total'];
                    } else {
                        $productTotal = $value['total'];
                    }
                }
            }
            $returnData['bottleTotal'] = $bottleTotal;
            $returnData['productTotal'] = $productTotal;

            if (!empty($token)) {
                $ftypeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                switch ($type) {
                    case 'gas':
                        $tankData = LibF::M('tank_gas')->field('tank_name,total,volume')->select();
                        if (!empty($tankData)) {
                            foreach ($tankData as $value) {
                                $val['title'] = $value['tank_name'];
                                $val['total'] = $value['volume'];
                                $data[] = $val;
                            }
                        }
                        break;
                    case 'bottle':
                        $where['type'] = 1;
                        $dataTypeTotal = LibF::M('filling_stock')->field('fs_num as total,fs_type_id,fs_name,type,goods_type')->where($where)->select();
                        if (!empty($dataTypeTotal)) {
                            foreach ($dataTypeTotal as $value) {
                                $val['title'] = $ftypeObject[$value['goods_type']].'-'.$value['fs_name'];
                                $val['total'] = $value['total'];
                                $data[] = $val;
                            }
                        }
                        break;
                    case 'product':
                        $where['type'] = 2;
                        $dataTypeTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,fs_type_id,fs_name,type')->where($where)->group('fs_type_id,fs_name,type')->select();
                        if (!empty($dataTypeTotal)) {
                            foreach ($dataTypeTotal as $value) {
                                $val['title'] = $value['fs_name'];
                                $val['total'] = $value['total'];
                                $data[] = $val;
                            }
                        }
                        break;
                }
                $returnData['data'] = !empty($data) ? $data : array();
                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口8采购
    public function planlistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $date = date("Y-m-d");
            
            $param['start_time'] = $this->getRequest()->getPost('time_start');
            $param['end_time'] = $this->getRequest()->getPost('time_end');
            $type = $this->getRequest()->getPost('type', 'gas'); //gas 燃气 bottle钢瓶 product 配件

            $data = array();
            if (!empty($token) && $type) {
                $pageStart = ($page - 1) * $this->pageSize;
                switch ($type) {
                    case 'gas':
                        //获取燃气采购计划
                        $where = '';
                        if ($param['start_time'] && $param['end_time'])
                            $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'].' 23:59:59')), 'AND');

                        $gasPlan = LibF::M('gas_plan')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                        if (!empty($gasPlan)) {
                            foreach ($gasPlan as $value) {
                                $val['plan_no'] = $value['plan_no'];
                                $val['name'] = '燃气';
                                $val['time'] = date("Y-m-d", $value['ctime']);
                                $val['status'] = ($value['status'] == 0) ? '待审核' : '已审核';
                                $data[] = $val;
                            }
                        }
                        break;
                    case 'bottle':
                        //获取钢瓶采购计划
                        $where = '';
                        if ($param['start_time'] && $param['end_time'])
                            $where['time_created'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'].' 23:59:59')), 'AND');

                        $bottlePlan = LibF::M('bottle_purchase')->where($where)->order('id desc')->limit($pageStart, $this->pageSize)->select();
                        if (!empty($bottlePlan)) {
                            foreach ($bottlePlan as $value) {
                                $val['plan_no'] = $value['documentsn'];
                                $val['name'] = '钢瓶';
                                $val['time'] = date("Y-m-d", $value['time_created']);
                                $val['status'] = ($value['status'] == 0) ? '待审核' : '已审核';
                                $data[] = $val;
                            }
                        }

                        break;
                    case 'product':
                        //获取配件采购计划
                        $where = '';
                        if ($param['start_time'] && $param['end_time'])
                            $where['time_created'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'].' 23:59:59')), 'AND');

                        $productsPlan = LibF::M('warehousing')->where($where)->order('id desc')->limit($pageStart, $this->pageSize)->select();
                        if (!empty($productsPlan)) {
                            foreach ($productsPlan as $value) {
                                $val['plan_no'] = $value['documentsn'];
                                $val['name'] = '配件';
                                $val['time'] = date("Y-m-d", $value['time_created']);
                                $val['status'] = ($value['status'] == 0) ? '待审核' : '已审核';
                                $data[] = $val;
                            }
                        }
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

    //接口8.1采购详情
    public function plandetialAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $param['plan_no'] = $this->getRequest()->getPost('plan_no');
            $type = $this->getRequest()->getPost('type', 'gas'); //gas 燃气 bottle钢瓶 product 配件

            $returnData = array();
            $param['page'] = $this->getRequest()->getPost('page', 1);
            if (!empty($token) && $type) {
                switch ($type) {
                    case 'gas':
                        //获取燃气采购计划
                        if ($param['plan_no']) {
                            $data = LibF::M('gas_plan')->where(array('plan_no' => $param['plan_no']))->find();
                            if (!empty($data)) {
                                $dataArr = !empty($data['product']) ? json_decode($data['product']) : '';
                                if (!empty($dataArr)) {
                                    $gasModel = new GasModel();
                                    $gasObject = $gasModel->getGasArray();    //获取燃气类型

                                    foreach ($dataArr as $value) {
                                        if (is_string($value)) {
                                            $val = !empty($value) ? explode('|', $value) : '';
                                            $v['name'] = $gasObject[$val[0]]['gas_name'];
                                            $v['num'] = $val[2];
                                            $v['price'] = $val[3];
                                            $returnData[] = $v;
                                        }
                                    }
                                }
                            }
                        }

                        break;
                    case 'bottle':
                        //获取钢瓶采购计划
                        if ($param['plan_no']) {
                            $data = LibF::M('bottle_purchase')->where(array('documentsn' => $param['plan_no']))->find();
                            if ($data) {
                                $dataArr = !empty($data['bottle']) ? json_decode($data['bottle']) : '';
                                if (!empty($dataArr)) {
                                    $bottleType = new BottletypeModel();
                                    $bottleTypeObject = $bottleType->getBottleTypeArray();

                                    foreach ($dataArr as $value) {
                                        $val = !empty($value) ? explode('|', $value) : '';
                                        $v['name'] = $bottleTypeObject[$val[0]]['bottle_name'];
                                        $v['num'] = $val[2];
                                        $v['price'] = $val[3];
                                        $returnData[] = $v;
                                    }
                                }
                            }
                        }

                        break;
                    case 'product':
                        //获取配件采购计划
                        if ($param['plan_no']) {
                            $data = LibF::M('warehousing')->where(array('documentsn' => $param['plan_no']))->find();
                            if ($data) {
                                $dataArr = !empty($data['product']) ? json_decode($data['product']) : '';
                                if (!empty($dataArr)) {
                                    $productObject = ProductsModel::getProductsArray(); //获取配件类型

                                    foreach ($dataArr as $value) {
                                        $val = !empty($value) ? explode('|', $value) : '';
                                        $v['name'] = $productObject[$val[0]]['products_name'];
                                        $v['num'] = $val[2];
                                        $v['price'] = $val[3];
                                        $returnData[] = $v;
                                    }
                                }
                            }
                        }

                        break;
                }

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.1财务统计
    public function financetotalAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);

            if (!empty($token)) {
                if(!empty($shop_id)){
                    $where['shop_id'] = $dwhere['shop_id'] = $shop_id;
                    $rwhere['rq_kehu.shop_id'] = $shop_id;
                }
                
                $where['status'] = 4;
                $salesData = LibF::M('order')->field('sum(pay_money-deposit) as total,sum(deposit) as yjtotal')->where($where)->find();
                $data['salesTotal'] = ($salesData['total']) ? $salesData['total'] : 0; //销售额
                
                $dwhere['status'] = 0;
                $dwhere['deposit_type'] = 1;
                $depositData = LibF::M('deposit')->field('sum(money) as total')->where($dwhere)->find();
                $data['salesyjTotal'] = ($depositData['total']) ? $depositData['total'] : 0; //押金
                
                $rwhere['rq_order_arrears.type'] = 1;
                $rwhere['rq_order_arrears.is_return'] = 0;

                $reportModel = new Model('order_arrears');
                $filed = " sum(rq_order_arrears.money) as total ";
                $leftJoin = " LEFT JOIN rq_kehu ON rq_kehu.kid = rq_order_arrears.kid ";
                $reportData = $reportModel->join($leftJoin)->field($filed)->where($rwhere)->find();
                $data['reportTotal'] = ($reportData['total']) ? $reportData['total'] : 0;

                $data['spending'] = 0; //支出额

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.2 销售收入
    public function salesrevenueAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            if (!empty($token)) {
                //$w['ispayment'] = 1;
                $w['status'] = 4;
                if(!empty($shop_id)){
                    $where['rq_order.shop_id'] = $shop_id;
                    $w['shop_id'] = $shop_id;
                }
                
                $orderTotal = LibF::M('order')->field('sum(pay_money-deposit) as total')->where($w)->find();
                $returnData['total'] = $orderTotal['total'];

                $where['rq_order.ispayment'] = 1;
                $where['rq_order.status'] = 4;
                
                $pageStart = ($page - 1) * $this->pageSize;
                $orderModel = new Model('order');
                $field = 'rq_order.kid,rq_kehu.user_name,rq_order.shop_id,rq_shop.shop_name,count(rq_order.order_id) as num,sum(rq_order.pay_money-rq_order.deposit+rq_order.is_settlement_money) as total,sum(rq_order.deposit) as yjtotal';
                $orderData = $orderModel->join('LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid')->join('LEFT JOIN rq_shop ON rq_order.shop_id = rq_shop.shop_id')->field($field)->where($where)->order('rq_order.ctime desc')->group('rq_order.kid')->limit($pageStart,$this->pageSize)->select();
                $returnData['data'] = !empty($orderData) ? $orderData : array();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.3 应收款
    public function receivablesAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            if (!empty($token)) {
                $arrearType = array(1 => '液化气欠款', 2 => '押金欠款', 3 => '配件欠款');
                
                $where = array('rq_order_arrears.is_return' => 0, 'rq_order_arrears.type' => 1, 'rq_order_arrears.status' => 1);
                if (!empty($shop_id))
                    $where['rq_kehu.shop_id'] = $shop_id;

                $pageStart = ($page - 1) * $param['pagesize'];

                $oModel = new Model('order_arrears');
                $ofiled = " sum(rq_order_arrears.money) as total ";
                $leftJoin = " LEFT JOIN rq_kehu ON rq_order_arrears.kid = rq_kehu.kid ";
                $odata = $oModel->join($leftJoin)->field($ofiled)->where($where)->find();

                $orderModel = new Model('order_arrears');
                $filed = " rq_order_arrears.kid,rq_order_arrears.arrears_type,rq_order_arrears.money,rq_kehu.user_name,rq_kehu.shop_id ";
                $leftJoin = " LEFT JOIN rq_kehu ON rq_order_arrears.kid = rq_kehu.kid ";
                $data = $orderModel->join($leftJoin)->field($filed)->where($where)->order('rq_order_arrears.id desc')->limit($pageStart, $this->pageSize)->select();
                if (!empty($data)) {
                    $shopObject = ShopModel::getShopArray();
                    foreach ($data as &$value) {
                        $value['total'] = $value['money'];
                        $value['type_name'] = $arrearType[$value['arrears_type']];
                        $value['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '未分配门店';
                    }
                }
                $returnData['data'] = $data;
                $returnData['dataTotal'] = $odata['total'];

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.4 采购支出
    public function procurementAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            if (!empty($token)) {
                $dataTotal = LibF::M('procurement')->field('sum(money) as total')->find();
                $returnData['dataTotal'] = $dataTotal['total'];

                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('procurement')->field('procurement_no,type,goods_num,money')->order('time desc')->limit($pageStart,$this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['typename'] = ($value['type'] == 1) ? '燃气' : (($value == 2) ? '钢瓶' : '配件');
                    }
                }
                $returnData['data'] = !empty($data) ? $data : array();
                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.5 押金收入
    public function incomelistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            if (!empty($token)) {

                $where = $shopWhere = array();
                if(!empty($shop_id)){
                    $where['rq_deposit.shop_id'] = $shop_id;
                    $shopWhere['shop_id'] = $shop_id;
                }
                
                $where['rq_deposit.status'] = $shopWhere['status'] = 0;
                $where['rq_deposit.deposit_type'] = $shopWhere['deposit_type'] = 1;
                $dataTotal = LibF::M('deposit')->field('sum(money) as total')->where($shopWhere)->find();
                $returnData['dataTotal'] = $dataTotal['total'];
                
                $pageStart = ($page - 1)*$this->pageSize;
                $depositModel = new Model('deposit');
                $field = "rq_deposit.kid,rq_deposit.order_sn,rq_deposit.shop_id,rq_deposit.shop_name,rq_kehu.user_name,rq_deposit.money,rq_deposit.number,rq_deposit.time";
                $data = $depositModel->join("left join rq_kehu ON rq_deposit.kid = rq_kehu.kid")->field($field)->where($where)->order('rq_deposit.id desc')->limit($pageStart,$this->pageSize)->select();
                if (!empty($data)) {
                    $shopData = ShopModel::getShopArray(); //获取门店
                    foreach ($data as &$value) {
                        $value['shop_name'] = $shopData[$value['shop_id']]['shop_name'];
                        $value['typename'] = '';
                    }
                }
                $returnData['data'] = !empty($data) ? $data : array();
                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.6 预付款
    public function paymentlistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            if (!empty($token)) {
                if(!empty($shop_id))
                    $where['shop_id'] = $shop_id;
                
                $where['type'] = 1;
                $dataTotal = LibF::M('shop_prepayments')->field('sum(money) as total')->where($where)->find();
                $returnData['dataTotal'] = $dataTotal['total'];

                $pageStart = ($page - 1)*$this->pageSize;
                
                $where['money'] = array('gt', 0);
                $data = LibF::M('shop_prepayments')->field('shop_id,money')->where($where)->order('id desc')->limit($pageStart, $this->pageSize)->select();
                if(!empty($data)){
                    $shopObject = ShopModel::getShopArray();
                    foreach($data as &$value){
                        $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                        $value['payment'] = $value['money'];
                        $value['level'] = '';
                    }
                }
                $returnData['data'] = !empty($data) ? $data : array();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.7 其它支出
    public function oprocurementAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            if (!empty($token)) {
                $map['raffinat'] = array('gt', 0);
                $map['depreciation'] = array('gt', 0);
                $map['_logic'] = 'OR';
                $where['_complex'] = $map;
                $where['_logic'] = "AND";
                if(!empty($shop_id))
                    $where['shop_id'] = $w['shop_id'] = $shop_id;
                
                $where['status'] = $w['status'] = 4;
                $where['ispayment'] = $w['ispayment'] = 1;
                
                $dataTotal = LibF::M('order')->field('sum(raffinat) as total,sum(depreciation) as money,sum(residual_gas) as gas_total')->where($w)->find();
                $returnData['dataTotal'] = $dataTotal['total'] + $dataTotal['money'] + $dataTotal['gas_total'];

                $pageStart = ($page - 1)*$this->pageSize;
                $data = LibF::M('order')->field('order_sn,raffinat,depreciation,residual_gas,username,ctime')->where($where)->order('ctime desc')->limit($pageStart,$this->pageSize)->select();
                if(!empty($data)){
                    foreach($data as &$value){
                        $value['ctime'] = date('m-d H:i',$value['ctime']);
                    }
                }
                $returnData['data'] = !empty($data) ? $data : array();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口10.8 其它收入
    public function wholesalelistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            if (!empty($token)) {
                $dataTotal = LibF::M('other_wholesale')->field('sum(zc_price) as total')->find();
                $returnData['dataTotal'] = $dataTotal['total'];

                $typeObject = array(1 => '折现', 2 => '残液');
                
                $pageStart = ($page - 1)*$this->pageSize;
                $data = LibF::M('other_wholesale')->field('procurement_no,zc_object,zc_num,zc_price,time')->order('time desc')->limit($pageStart,$this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['typename'] = $typeObject[$value['zc_object']];
                    }
                }
                $returnData['data'] = !empty($data) ? $data : array();

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口 10.9 获取当前客户基础信息
    public function kehuinfoAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $kid = $this->getRequest()->getPost('kid');
            if (!empty($token) && $kid) {
                $kehuModel = new Model('kehu');

                $where['rq_kehu.kid'] = $kid;
                $field = " rq_kehu.kid,rq_kehu.user_name,rq_kehu.ktype,rq_kehu.mobile_phone,rq_kehu.last_buy,rq_kehu.buy_time,rq_kehu.pay_money,rq_kehu.balance,rq_kehu.ctime,rq_shop.shop_name, ";
                $field .= " rq_kehu.sheng,rq_kehu.shi,rq_kehu.qu,rq_kehu.cun,rq_kehu.address ";
                $data = $kehuModel->join('left join rq_shop on rq_shop.shop_id=rq_kehu.shop_id')->field($field)->where($where)->find();
                if (!empty($data)) {
                    $data['deposit'] = !empty($data['pay_money']) ? $data['pay_money'] : 0;  //押金
                    $data['no_money'] = !empty($data['nobuy_money']) ? $data['nobuy_money'] : 0; //欠款金额

                    $data['last_buy'] = ($data['last_buy'] > 0) ? date('Y-m-d H:i', $data['last_buy']) : '';
                    $data['ctime'] = ($data['ctime'] > 0) ? date('Y-m-d H:i', $data['ctime']) : '';

                    $regionModel = new RegionModel();
                    $regionData = $regionModel->getRegionObject();
                    $data['sheng_name'] = isset($regionData[$data['sheng']]) ? $regionData[$data['sheng']]['region_name'] : '';
                    $data['shi_name'] = isset($regionData[$data['shi']]) ? $regionData[$data['shi']]['region_name'] : '';
                    $data['qu_name'] = isset($regionData[$data['qu']]) ? $regionData[$data['qu']]['region_name'] : '';
                    $data['cun_name'] = isset($regionData[$data['cun']]) ? $regionData[$data['cun']]['region_name'] : '';
                }

                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口11.1 门店扫平入库
    //备注： 门店入库 1.气站配送入库 重瓶 2.送气工入库
    public function inshopAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');

            $card_id = $this->getRequest()->getPost('card_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');

            $admin_user = $this->getRequest()->getPost('user_name');
            $admin_id = $this->getRequest()->getPost('user_id');

            $type = $this->getRequest()->getPost('type_id', 1); //1重瓶0空瓶2配件
            $data = $this->getRequest()->getPost('data'); //数据串
            
            $confirm_no = $this->getRequest()->getPost('confirm_no');  //当前确认单号
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id)) {
                if ($type != 2 && !empty($data)) {
                    $data = json_decode($data, true);
                    $data = array_unique($data);
                    $status = $this->insertBottleShop($confirm_no, $shop_id, $data, 1, $type, $admin_user);
                } else {
                    $status = $this->insertProductShop($confirm_no, $shop_id, $admin_user);
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

    //接口11.2 门店扫平出库
    public function outshopAction() {
        if ($this->getRequest()->isPost()) {
            $shop_id = $this->getRequest()->getPost('shop_id');
            $car_id = $this->getRequest()->getPost('car_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $admin_user = $this->getRequest()->getPost('user_name');
            $admin_id = $this->getRequest()->getPost('user_id');
            $type = $this->getRequest()->getPost('type_id', 1); //1重瓶0空瓶2配件
            $data = $this->getRequest()->getPost('data'); //数据串
            
            $confirm_no = $this->getRequest()->getPost('confirm_no');  //当前确认单号
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($shop_id)) {
                $data = (!empty($data)) ? json_decode($data, true) : array();
                /*$dataTransModel = new DatatransferModel();
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
                        $data = $dataTransModel->storeOutInventory($data, $shop_id, $type,$confirm_no);
                    }
                }*/
                $status = $this->outbackShop($confirm_no, $shop_id, $data, $admin_user);
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

    //接口11.3 门店扫平获取钢瓶信息
    public function bottleinfoAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $param = array();
            $param['xinpian'] = $this->getRequest()->getPost('xinpian');
            if (!empty($token)) {
                $condition['xinpian'] = $param['xinpian'];
                $condition['number'] = $param['xinpian'];
                $condition['_logic'] = 'OR';
                $data = LibF::M('bottle')->where($condition)->find();
                if(!empty($data)){
                    $this->app->respond(1, $data);
                }else{
                    $this->app->respond(-2, '当前钢瓶号不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //接口12.1 投诉管理
    public function tosulistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $data = array();
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('startTimes');
            $param['end_time'] = $this->getRequest()->getPost('endTimes');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            if (!empty($token)) {
                $where = array();
                if ($param['start_time'] && $param['end_time'])
                    $where['rq_tousu.ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'AND');
                if (!empty($shop_id))
                    $where['rq_tousu.shop_id'] = $shop_id;

                $pageStart = ($page - 1)*$this->pageSize;
                $tosuModel = new Model('tousu');
                $filed = " rq_tousu.encode_id,rq_tousu.kname,rq_shop.shop_name,rq_tousu.comment,rq_tousu.treatment,rq_tousu.admin_user_name,rq_tousu.status,rq_tousu.ctime ";
                $data = $tosuModel->join('LEFT JOIN rq_shop ON rq_tousu.shop_id = rq_shop.shop_id')->field($filed)->where($where)->order('rq_tousu.ctime desc')->limit($pageStart,$this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['time'] = date("m-d H:i", $value['ctime']);
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

    //接口12.2 保修管理
    public function baoxiulistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id', 0);
            
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $data = array();
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('startTimes');
            $param['end_time'] = $this->getRequest()->getPost('endTimes');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            if (!empty($token)) {
                $where = array();
                if ($param['start_time'] && $param['end_time'])
                    $where['rq_baoxiu.ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'AND');

                if (!empty($shop_id))
                    $where['rq_baoxiu.shop_id'] = $shop_id;

                $pageStart = ($page - 1)*$this->pageSize;
                $baoxiuModel = new Model('baoxiu');
                $filed = " rq_baoxiu.encode_id,rq_baoxiu.kname,rq_shop.shop_name,rq_baoxiu.comment,rq_baoxiu.treatment,rq_baoxiu.admin_user_name,rq_baoxiu.status,rq_baoxiu.ctime ";
                $data = $baoxiuModel->join('LEFT JOIN rq_shop ON rq_baoxiu.shop_id = rq_shop.shop_id')->field($filed)->where($where)->order('rq_baoxiu.ctime desc')->limit($pageStart,$this->pageSize)->select();
                if (!empty($data)) {
                    foreach ($data as &$value) {
                        $value['time'] = date("m-d H:i", $value['ctime']);
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

    //补充接口 12.3 部门岗位对应的用户
    public function orguserlistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $messageData = array();
            if (!empty($token)) {
                $where = array();

                $messageData['total'] = LibF::M('admin_user')->where(array('status' => 0))->count();  //人员总数
                $messageData['qztotal'] = LibF::M('admin_user')->where(array('shop_id' => 0))->count(); //气站人员
                $messageData['mdtotal'] = $messageData['total'] - $messageData['qztotal'];

                $returnData = array();
                $orgModel = new Model('organization');
                $filed = " rq_organization.org_id,rq_organization.org_name,rq_quarters.id ";
                $data = $orgModel->join('LEFT JOIN rq_quarters ON rq_organization.org_id = rq_quarters.org_parent_id')->field($filed)->where($where)->select();
                if (!empty($data)) {
                    $orgObject = $orgData = array();
                    foreach ($data as $value) {
                        if (!empty($value['id']))
                            $orgData[$value['org_id']][] = $value['id'];

                        $orgObject[$value['org_id']] = $value['org_name'];
                    }
                    if (!empty($orgData)) {
                        foreach ($orgData as $key => $val) {
                            $cData = array();
                            //$quarterslist = implode(',', $val);
                            $cWhere['rq_quarters_user.quarters_id'] = array('in', array_unique($val));
                            $cWhere['rq_admin_user.user_id'] = array('gt', 0);
                            $cWhere['rq_admin_user.mobile_phone'] = array('neq', '');
                            $cData['org_id'] = $key;

                            $userModel = new Model('quarters_user');
                            $cData['total'] = $userModel->join('LEFT JOIN rq_admin_user ON rq_quarters_user.uid = rq_admin_user.user_id')->field('distinct rq_admin_user.user_id')->where($cWhere)->count();
                            $cData['title'] = isset($orgObject[$key]) ? $orgObject[$key] : '';
                            $returnData[] = $cData;
                        }
                    }
                }
                $messageData['data'] = $returnData;
                $this->app->respond(1, $messageData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.4 指定部门用户列表
    public function orguserAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $org_id = $this->getRequest()->getPost('org_id');
            
            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;

            $messageData = array();
            if (!empty($token) && $org_id) {

                $where['rq_quarters.org_parent_id'] = $org_id;
                $quartersModel = new Model('quarters');
                $filed = " rq_quarters.title,rq_quarters_user.uid ";
                
                $pageStart = ($page - 1)*$this->pageSize;
                $data = $quartersModel->join('LEFT JOIN rq_quarters_user ON rq_quarters.id = rq_quarters_user.quarters_id')->field($filed)->where($where)->select();
                if (!empty($data)) {
                    $userArr = $quartersObject = array();
                    foreach ($data as $value) {
                        $quartersObject[$value['uid']] = $value['title'];
                        $userArr[] = $value['uid'];
                    }
                    if (!empty($userArr)) {
                        $uWhere['user_id'] = array('in', array_unique($userArr));
                        $messageData = LibF::M('admin_user')->where($uWhere)->limit($pageStart, $this->pageSize)->select();
                        if (!empty($messageData)) {
                            foreach ($messageData as &$val) {
                                $val['title'] = isset($quartersObject[$val['user_id']]) ? $quartersObject[$val['user_id']] : '';
                            }
                        }
                        $messageData = !empty($messageData) ? $messageData : array();
                    }
                }

                $this->app->respond(1, $messageData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.5 根据当前订单号获取订单信息
    public function orderinfoAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $order_sn = $this->getRequest()->getPost('order_sn');

            if (!empty($token) && $order_sn) {
                $param['order_sn'] = $order_sn;

                $orderModel = new OrderModel();
                $orderData = $orderModel->getOrderInfo($param);

                $orderInfo = array();
                if (!empty($orderData)) {
                    //获取当前钢瓶规格
                    $bottleTypeModel = new BottletypeModel();
                    $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                    //获取配件规格
                    $productTypeModel = new ProducttypeModel();
                    $productTypeData = $productTypeModel->getProductTypeArray();

                    //获取订单类型
                    $typeManagerModel = new TypemanagerModel();
                    $orderTypeData = $typeManagerModel->getData(array(6, 8, 10));

                    $orderType = isset($orderTypeData[8]) ? $orderTypeData[8] : '';

                    //订单状态
                    $orderStatus = isset($orderTypeData[10]) ? $orderTypeData[10] : '';

                    //客户状态
                    $userType = isset($orderTypeData[6]) ? $orderTypeData[6] : '';

                    $orderData['kehuType'] = isset($userType[$orderData['kehu_type']]) ? $userType[$orderData['kehu_type']]['typemanagername'] : '';
                    $orderData['orderType'] = isset($orderType[$orderData['is_urgent']]) ? $orderType[$orderData['is_urgent']]['typemanagername'] : '';
                    $orderData['orderStatus'] = isset($orderStatus[$orderData['status']]) ? $orderStatus[$orderData['status']]['typemanagername'] : '';

                    $orderInfo = $orderModel->getUserOrderInfoList(array('ordersn' => $param['order_sn']));
                    if (!empty($orderInfo)) {
                        foreach ($orderInfo as $key => &$value) {
                            if ($value['goods_type'] == 1) {
                                $value['type_name'] = $bottleTypeData[$value['goods_kind']]['bottle_name'];
                            } else {
                                $value['type_name'] = $productTypeData[$value['goods_kind']]['name'];
                            }
                        }
                    }else{
                        $orderInfo = array();
                    }
                }
                $orderData['data'] = $orderInfo;

                $this->app->respond(1, $orderData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.6 门店确认单（确认保存门店库存）
    public function confirmeinfoAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $confirme_no = $this->getRequest()->getPost('confirme_no');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $username = $this->getRequest()->getPost('username');
            if (!empty($token) && !empty($confirme_no) && !empty($shop_id)) {
                $data = LibF::M('confirme_detail')->where(array('confirme_no' => $confirme_no, 'shop_id' => $shop_id))->select();
                if (!empty($data)) {
                    $bottleArr = $ckProduct = array();
                    foreach ($data as $value) {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        if ($value['ftype'] == 1) {
                            $bottleArr[] = $val;
                        } else {
                            $ckProduct[] = $val;
                        }
                    }
                    $ckUData['comment'] = '';
                    $ckUData['admin_user'] = $username;

                    $dataModel = new DataInventoryModel();
                    $status = $dataModel->shopck($shop_id, 0, $bottleArr, $ckUData, 1, 2);  //门店入库
                    if ($status){
                        $ckModel->shopckproduct($shop_id, 0, $ckProduct, $ckUData, 1, $confirme_no);  //配件库存
                        
                        LibF::M('confirme')->where(array('confirme_no' => $confirme_no, 'shop_id' => $shop_id))->save(array('status' => 1));
                    }
                }

                $this->app->respond(1, '确认完成');
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.7 根据钢瓶号获取是否是本气站钢瓶
    public function isbottleAction() {
        if ($this->getRequest()->isPost()) {
            $number = $this->getRequest()->getPost('number');
            $token = $this->getRequest()->getPost('token', 1234);

            if (!empty($token) && !empty($number)) {
                $condition['xinpian'] = $number;
                $condition['number'] = $number;
                $condition['_logic'] = 'OR';
                $data = LibF::M('bottle')->where($condition)->find();
                if (!empty($data)) {
                    $this->app->respond(1, '钢瓶存在');
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

    //补充接口 12.8 根据角色获取相应的功能
    public function ruleslistAction() {
        if ($this->getRequest()->isPost()) {
            $role = $this->getRequest()->getPost('roles');
            $token = $this->getRequest()->getPost('token', 1234);

            $rData = array();
            if (!empty($token) && !empty($role)) {
                $data = LibF::M('auth_role')->where(array('id' => $role))->find();
                if (!empty($data)) {
                    $rules = $data['app_rules'];

                    $returnData = array('frole' => array(), 'role' => array());
                    $where['id'] = array('in', $rules);
                    $rulesData = LibF::M('auth_rule_app')->where($where)->order('id asc,list_sort asc')->select();
                    if (!empty($rulesData)) {
                        $adminRuleAppModel = new AdminRuleAppModel();
                        $ruleObject = $adminRuleAppModel->getRuleObject();

                        $prole = array();
                        foreach ($rulesData as $value) {
                            if ($value['pid'] == 0) {
                                if (!isset($prole[$value['id']])) {
                                    $returnData['frole'][] = $value['list_sort'];
                                    $prole[$value['id']] = $value['id'];
                                }
                            } else {
                                $returnData['role'][$ruleObject[$value['pid']]][] = $value['list_sort'];
                                if (!isset($prole[$value['pid']])) {
                                    $returnData['frole'][] = $ruleObject[$value['pid']];
                                    $prole[$value['pid']] = $value['pid'];
                                }
                            }
                        }
                        if (!empty($returnData['frole'])) {
                            foreach ($returnData['frole'] as $value) {
                                $val['f'] = $value;
                                $val['s'] = isset($returnData['role'][$value]) ? $returnData['role'][$value] : array();
                                $rData[] = $val;
                            }
                        }
                    }
                    $this->app->respond(1, $rData);
                } else {
                    $this->app->respond(-3, '角色未分配功能');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.9 门店出库确认
    public function confirmehkAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $confirme_no = $this->getRequest()->getPost('confirme_no');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $username = $this->getRequest()->getPost('username');
            if (!empty($token) && !empty($confirme_no) && !empty($shop_id)) {
                
                //获取当前出库单详情
                $where['confirme_no'] = $confirme_no;
                $where['shop_id'] = $shop_id;
                //$where['ftype'] = 1;
                $data = LibF::M('confirme_store_detail')->where($where)->select();
                if (!empty($data)) {
                    $ckData = $productData = array();
                    foreach ($data as $value) {
                        if ($value['ftype'] == 0) {
                            $val['kind_id'] = $value['type'];
                            $val['num'] = $value['num'];
                            $val['shop_id'] = $value['shop_id'];
                            $val['status_id'] = $value['ftype'];
                            $productData[] = $val;
                        } else {
                            $val['kind_id'] = $value['type'];
                            $val['num'] = $value['num'];
                            $val['shop_id'] = $value['shop_id'];
                            $val['status_id'] = $value['ftype'];
                            $ckData[] = $val;
                        }
                    }
                    $ckUData['comment'] = '';
                    $ckUData['admin_user'] = $username;

                    $ckModel = new DataInventoryModel();
                    //备注当前押运员确认指定门店回库确认单数据 新增当前门店押运员确认数据，同时门店出库
                    $iParam['confirme_no'] = $confirme_no;
                    $iParam['shop_id'] = $shop_id;
                    $iParam['ctime'] = time();
                    $iParam['status'] = 1;
                    $iParam['admin_user'] = $username;
                    $status = LibF::M('confirme_store_detail_sp')->add($iParam);
                    
                    if (!empty($ckData)) {
                        $status = $ckModel->shopck($shop_id, 0, $ckData, $ckUData, 2, 1);
                        if ($status) {
                            $ckModel->shopckproduct($shop_id, 0, $productData, $ckUData, 2, $confirme_no);  //配件库存
                            LibF::M('confirme_store_detail')->where(array('confirme_no' => $confirme_no, 'shop_id' => $shop_id))->save(array('status' => 1));
                        }
                    } else {
                        LibF::M('confirme_store_detail')->where(array('confirme_no' => $confirme_no, 'shop_id' => $shop_id))->save(array('status' => 1));
                    }
                    $returnData['status'] = $status;
                }

                $this->app->respond(1, '确认完成');
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.10 气站回库确认单
    public function confirmerkAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $confirme_no = $this->getRequest()->getPost('confirme_no');
            $username = $this->getRequest()->getPost('username');
            //$shop_id = $this->getRequest()->getPost('shop_id');
            if (!empty($token) && !empty($confirme_no)) {
                $iw['confirme_no'] = $confirme_no;
                $iw['status'] = 0;
                $isData = LibF::M('confirme_store')->where($iw)->find();
                if (!empty($isData)) {
                    //$where['shop_id'] = $shop_id;
                    $where['confirme_no'] = $confirme_no;
                    //$where['ftype'] = array('gt', 0); //暂时不做配件
                    $data = LibF::M('confirme_store_detail')->where($where)->select();
                    if (!empty($data)) {
                        $ckData = $productData = array();
                        foreach ($data as $value) {
                            if ($value['ftype'] == 0) {
                                $val['kind_id'] = $value['type'];
                                $val['num'] = $value['num'];
                                $val['shop_id'] = $value['shop_id'];
                                $val['status_id'] = $value['ftype'];
                                $productData[] = $val;
                            } else {
                                $val['kind_id'] = $value['type'];
                                $val['num'] = $value['num'];
                                $val['shop_id'] = $value['shop_id'];
                                $val['status_id'] = $value['ftype'];
                                $ckData[] = $val;
                            }
                        }
                        $ckUData['comment'] = '';
                        $ckUData['admin_user'] = $username;
                        
                        //气站入库 
                        $ckModel = new DataInventoryModel();
                        $status = $ckModel->systemck(0, $ckData, $ckUData, 1, 1);
                        if ($status) {
                            $ckModel->ckProduct(0, $productData, $ckUData, 1, $confirme_no);
                            $wh = array('confirme_no' => $confirme_no);
                            LibF::M('confirme_store')->where($wh)->save(array('status' => 1));
                        }
                        $returnData['status'] = $status;
                    }
                    $this->app->respond(1, '确认完成');
                }else{
                    $this->app->respond(0, '已经入库');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.11 门店钢瓶库存
    public function shopkcAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');

            $rData = array();
            if (!empty($token) && $shop_id) {

                $where['shop_id'] = $shop_id;
                $where['fs_num'] = array('gt',0);
                $data = LibF::M('filling_stock_shop')->where($where)->select();
                
                if(!empty($data)){
                    /*$returnData = $r = array();
                    foreach ($data as $value) {
                        if (!isset($returnData[$value['goods_type']][$value['fs_type_id']])) {
                            $returnData[$value['goods_type']][$value['fs_type_id']] = $value['fs_num'];
                        } else {
                            $returnData[$value['goods_type']][$value['fs_type_id']] += $value['fs_num'];
                        }
                        $r[$value['fs_type_id']] = $value;
                    }*/
                    if(!empty($data)){
                        $ftypeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
                        foreach($data as $key => $value){
                            if($value['type'] == 1){
                                $typeName = $ftypeObject[$value['goods_type']];
                                $value['type_name'] = $typeName .'-'.$Value['fs_name'];
                            }else{
                                $value['type_name'] = $Value['fs_name'];
                            }
                            $rData[] = $value;
                        }
                    }
                }

                $this->app->respond(1, $rData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    //补充接口 12.12 公文审批
    public function shenpiAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            $user_id = $this->getRequest()->getPost('user_id');
            $user_name = $this->getRequest()->getPost('user_name');
            $comment = $this->getRequest()->getPost('reason');
            $type = $this->getRequest()->getPost('type', 1); //1审批2拒绝
            $id = $this->getRequest()->getPost('id');
            if (!empty($token) && !empty($id) && !empty($user_id)) {
                $where['id'] = $id;
                $data = LibF::M('approval')->where($where)->find();
                if (!empty($data)) {
                    $d['approval_user'] = $user_id;
                    if (!empty($user_name))
                        $d['approval_username'] = $user_name;
                    $d['approval_status'] = $type;
                    $d['reason'] = $comment;
                    $status = LibF::M('approval')->where($where)->save($d);
                    if ($status) {
                        if ($data['approval_genre'] == 1) {
                            $w['plan_no'] = $data['approval_documents'];
                            $u['status'] = $type;
                            $u['admin_user_id'] = $user_id;
                            $u['admin_user_name'] = $user_name;
                            LibF::M('gas_plan')->where($w)->save($u);
                        } else if ($data['approval_genre'] == 2) {
                            $w['documentsn'] = $data['approval_documents'];
                            $u['status'] = $type;
                            $u['effective_time'] = time();
                            $u['effective_user'] = $user_id;
                            $u['effective_username'] = $user_name;
                            LibF::M('bottle_purchase')->where($w)->save($u);
                        } else if ($data['approval_genre'] == 3) {
                            $w['documentsn'] = $data['approval_documents'];
                            $u['status'] = $type;
                            $u['effective_time'] = time();
                            $u['effective_user'] = $user_id;
                            LibF::M('warehousing')->where($w)->save($u);
                        }

                        $this->app->respond(1, '审批成功');
                    } else {
                        $this->app->respond(-3, '审批失败');
                    }
                } else {
                    $this->app->respond(-3, '当前审批单据不存在');
                }
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口 12.13 门店人员
    public function shopuserAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $rData = array();
            if (!empty($token) && $shop_id) {
                $where['shop_id'] = $shop_id;
                $where['status'] = 0;
                
                $pageStart = ($page - 1) * $this->pageSize;
                $rData = LibF::M('admin_user')->field('user_id,username,real_name,mobile_phone,add_time')->where($where)->limit($pageStart, $this->pageSize)->select();
                if (!empty($rData)) {
                    $quartersModel = new QuartersModel();
                    $quartersObject = $quartersModel->getQuartersData();

                    foreach ($rData as &$value) {
                        $quartersData = LibF::M('quarters_user')->where(array('uid' => $value['user_id']))->find();
                        $value['quarters_id'] = !empty($quartersData) ? $quartersData['quarters_id'] : 0;
                        $value['title'] = isset($quartersObject[$value['quarters_id']]) ? $quartersObject[$value['quarters_id']]['title'] : '';
                        $value['add_time'] = !empty($value['add_time']) ? date('Y-m-d') : '';
                    }
                }

                $this->app->respond(1, $rData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口 12.14 送气工（退回门店）回库单据审核
    public function backshopAction() {
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
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
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
                    $bottleModel = new BottleModel();
                    $bottleObject = $bottleModel->bottleOData();
                    foreach ($data as $key => &$value) {
                        $value['bottle'] = json_decode($value['bottle'], true);
                        $value['bottle_data'] = json_decode($value['bottle_data'], true);
                        if(!empty($value['bottle_data'])){
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
                $this->app->respond(1, $data);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口 12.15 送气工（退回门店） 回库单据审核确认
    public function confirmbockshopAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');
            $confirme_no = $this->getRequest()->getPost('confirme_no');
            if (!empty($confirme_no) && !empty($shop_id)) {
                $confirmData = LibF::M('confirme_shipper')->where(array('confirme_no' => $confirme_no, 'shop_id' => $shop_id))->select();
                if (!empty($confirmData)) {
                    //更新状态 更新门店库存
                    
                } else {
                    $this->app->respond(-3, '当前数据不存在');
                }
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口 12.16 送气工安全检测列表
    public function safelistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $shipper_id = $this->getRequest()->getPost('shipper_id');

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pageSize;
            
            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time');
            $param['end_time'] = $this->getRequest()->getPost('end_time');
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'].' 23:59:59' : '';
            $rData = array();
            if (!empty($token)) {
                if (!empty($shipper_id))
                    $where['shipper_id'] = $shipper_id;

                if ($param['start_time'] && $param['end_time'])
                    $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $pageStart = ($page - 1) * $this->pageSize;
                $data = LibF::M('security_report_user')->field('order_sn,kid,shipper_id,shipper_name,reportdetail,imagedetail,ctime,status')->where($where)->order('ctime desc')->limit($pageStart, $this->pageSize)->select();
                $rData = !empty($data) ? $data : array();
                $this->app->respond(1, $rData);
            } else {
                $this->app->respond(-3, '当前用户未登录');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    //补充接口 12.17 通用版本升级更新
    public function appversionAction() {
        //app更新类型 app userapp shipperapp
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            if (!empty($token)) {

                $file = "vsersion_address,version_number,version_time";
                $data = LibF::M('andrews_version')->where(array('version_status' => 1, 'version_type' => 'app'))->order('version_time desc')->limit(1)->find();
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

    //补充方法扫瓶子(门店重瓶入库、门店空瓶出库)
    protected function insertBottleShop($confirm_sn, $shop_id, $data, $type, $bottle_type = 0, $user_name = '') {
        //门店重瓶入库，气站进来(同步绑定当前门店入库确认单)
        if (empty($confirm_sn) || empty($shop_id) || empty($data))
            return false;
        
        //$bottle_type  0空瓶 1重瓶
        
        //type 1重瓶入库 2空瓶出库
        $status = 0;
        $dataTransModel = new DatatransferModel();
        if ($type == 1) {
            $tdata = $dataTransModel->storeInventory($data, $shop_id, 1, $confirm_sn);  //钢瓶录入
            if ($tdata['status'] == 200) {
                $rdata = LibF::M('confirme_detail')->where(array('confirme_no' => $confirm_sn, 'shop_id' => $shop_id))->select();
                if (!empty($rdata)) {
                    $bottleArr = $ckProduct = array();
                    foreach ($rdata as $value) {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        if ($value['ftype'] == 1) {
                            $bottleArr[] = $val;
                        } else {
                            $ckProduct[] = $val;
                        }
                    }
                    $ckUData['comment'] = '';
                    $ckUData['admin_user'] = $user_name;

                    $dataModel = new DataInventoryModel();
                    $status = $dataModel->shopck($shop_id, 0, $bottleArr, $ckUData, 1, 2);  //门店入库
                    if ($status) {
                        if (!empty($ckProduct)) {
                            $dataModel->shopckproduct($shop_id, 0, $ckProduct, $ckUData, 1, $confirm_sn);  //配件库存
                        }
                        $status = LibF::M('confirme')->where(array('confirme_no' => $confirm_sn, 'shop_id' => $shop_id))->save(array('status' => 1));
                    }
                }
            }
        } else {
            //空瓶出库
            $tdata = $dataTransModel->storeOutInventory($data, $shop_id, 0, $confirm_sn);
            if ($tdata['status'] == 200) {
                $status = 1;
                $where['confirme_no'] = $confirm_sn;
                LibF::M('confirme_store_detail')->where(array('confirme_no' => $confirm_sn, 'shop_id' => $shop_id))->save(array('status' => 1));
            }
        }

        return $status;
    }
    
    //补充接口配送回库确认单确认回库(押运员确认)
    protected function outbackShop($confirm_sn, $shop_id, $data, $user_name = '') {
        if (empty($confirm_sn) || empty($shop_id))
            return false;

        if(!empty($data)){
            $dataTransModel = new DatatransferModel();
            $tdata = $dataTransModel->storeOutInventory($data, $shop_id, 0, $confirm_sn);
        }
        
        if (!empty($confirm_sn) && !empty($shop_id)) {
            $where['confirme_no'] = $confirm_sn;
            $where['shop_id'] = $shop_id;
            
            $rdata = LibF::M('confirme_store_detail')->where($where)->select();
            if (!empty($rdata)) {
                $ckData = $productData = array();
                foreach ($rdata as $value) {
                    if ($value['ftype'] == 0) {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['status_id'] = $value['ftype'];
                        $productData[] = $val;
                    } else {
                        $val['kind_id'] = $value['type'];
                        $val['num'] = $value['num'];
                        $val['shop_id'] = $value['shop_id'];
                        $val['status_id'] = $value['ftype'];
                        $ckData[] = $val;
                    }
                }
                $ckUData['comment'] = '';
                $ckUData['admin_user'] = $username;

                $ckModel = new DataInventoryModel();
                //备注当前押运员确认指定门店回库确认单数据 新增当前门店押运员确认数据，同时门店出库
                $iParam['confirme_no'] = $confirm_sn;
                $iParam['shop_id'] = $shop_id;
                $iParam['ctime'] = time();
                $iParam['status'] = 1;
                $iParam['admin_user'] = $user_name;
                $status = LibF::M('confirme_store_detail_sp')->add($iParam);

                if (!empty($ckData)) {
                    $status = $ckModel->shopck($shop_id, 0, $ckData, $ckUData, 2, 1);
                } 
                if (!empty($productData)) {
                    $status = $ckModel->shopckproduct($shop_id, 0, $productData, $ckUData, 2, $confirm_sn);  //配件库存
                }
                LibF::M('confirme_store_detail')->where(array('confirme_no' => $cno, 'shop_id' => $shop_id))->save(array('status' => 1));
            }
        }
        return $status;
    }

    //补充方法门店单独入库配件
    protected function insertProductShop($confirm_sn, $shop_id, $user_name = '') {
        if (empty($confirm_sn) || empty($shop_id))
            return false;

        $status = 0;
        $rdata = LibF::M('confirme_detail')->where(array('confirme_no' => $confirm_sn, 'shop_id' => $shop_id))->select();
        if (!empty($rdata)) {
            $ckProduct = array();
            foreach ($rdata as $value) {
                $val['kind_id'] = $value['type'];
                $val['num'] = $value['num'];
                $val['shop_id'] = $value['shop_id'];
                if ($value['ftype'] == 2) {
                    $ckProduct[] = $val;
                }
            }
            $ckUData['comment'] = '';
            $ckUData['admin_user'] = $user_name;

            $dataModel = new DataInventoryModel();
            $dataModel->shopckproduct($shop_id, 0, $ckProduct, $ckUData, 1, $confirm_sn);  //配件库存
            $status = LibF::M('confirme')->where(array('confirme_no' => $confirm_sn, 'shop_id' => $shop_id))->save(array('status' => 1));
        }
        return $status;
    }

}