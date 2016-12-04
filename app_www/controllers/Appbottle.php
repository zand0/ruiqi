<?php

/**
 * @app钢瓶管理系统
 */
class AppbottleController extends \Com\Controller\Common {

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
    
    protected $bottleType = array();
    protected $bottleStatus = array();
    protected $bottleStatusJson = array();

    /**
     * 调用对象
     * @var arr
     */
    protected $app;
    protected $year;
    protected $url;
    protected $pagesize;

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
        $this->year = array(1 => '15年后', 2 => '12年后', 3 => '08年后', 4 => '报废瓶');
        $this->pagesize = 20;

        $this->bottleType = array(0 => '普通瓶', 1 => '智能瓶');
        $this->bottleStatus = array(0 => '新瓶', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $this->bottleKind = array(0 => '未入库', 1 => '未投放', 2 => '已投放');
        $this->bottleStatusJson = array(
            0 => array('id' => 0, 'title' => '新瓶'), 1 => array('id' => 1, 'title' => '空瓶'), 2 => array('id' => 2, 'title' => '重瓶'), 3 => array('id' => 3, 'title' => '折旧瓶(普通瓶)'), 4 => array('id' => 4, 'title' => '待修瓶'),
            5 => array('id' => 5, 'title' => '待检瓶'), 6 => array('id' => 6, 'title' => '报废瓶')
        );

        $this->url = 'http://cztest.ruiqi100.com';
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

            if ($mobile) {
                //相关base64相关解码
                if ($password) {
                    $password = $this->app->getPassword($password);
                }

                $parent_record = $this->getUserInfo($mobile);
                if (empty($parent_record)) {
                    $this->app->respond(-1, '此账号不存在');
                } else if ($parent_record['password'] != md5(md5($password) . $parent_record['user_salt'])) {
                    $this->app->respond(-2, '密码错误');
                } else {
                    //判断当前验证吗是否正确
                    $token = 123456;
                    $this->app->respond(1, array('mobile' => $mobile, 'user_id' => $parent_record['user_id'], 'username' => $parent_record['username'], 'token' => $token));
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
                $parent_record = $this->getUserInfo($mobile);
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
                $userData = $this->getUserInfo($mobile);
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
     * 钢瓶初始化信息
     */
    public function createbottleAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            if ($token) {
                $admin_user = $this->getRequest()->getPost('admin_user');
                $bottleData = $this->getRequest()->getPost('bottle_data');  //json串
                if (!empty($admin_user) && !empty($bottleData)) {
                    $time = $start_time = time();
                    $returnData = array('errorbottle' => array(), 'bottle' => array(), 'updatebottle' => array());

                    $bottletransfer = array();
                    $bottleArr = json_decode($bottleData, true);
                    
                    $date = date('Y-m-d');
                    if (!empty($bottleArr['bottle_data'])) {
                        foreach ($bottleArr['bottle_data'] as $value) {
                            $id = preg_replace('/[\'\s| ]+/', '', $value['id']);
                            if ($id) {
                                $val['id'] = $id;
                            }
                            $val['bottletype'] = preg_replace('/[\'\s| ]+/', '', $value['bottletype']);      //钢瓶类型例如：气相瓶、液相瓶、双头瓶
                            $val['bottle_kind'] = preg_replace('/[\'\s| ]+/', '', $value['bottle_kind']);    //规格id
                            $val['bottlemodel'] = preg_replace('/[\'\s| ]+/', '', $value['bottlemodel']);   //钢瓶规格例如：5kg 10kg 
                            $val['bottleattr'] = $value['bottleattr'] ? $value['bottleattr'] : 1;                         //钢瓶属性 新、空、重、折旧
                            $val['bottlestate'] = 0;                        //钢瓶状态 使用中、待修、待检测、报废
                            $val['bottlegenre'] = preg_replace('/[\'\s| ]+/', '', $value['bottlegenre']); //0非智能瓶1智能
                            
                            if (!empty($value['weighttype'])) {
                                $val['weighttype'] = preg_replace('/[\'\s| ]+/', '', $value['weighttype']);      //重量类型
                            } else {
                                $val['weighttype'] = 0;
                            }
                            if (!empty($value['factory'])) {
                                $val['factory'] = preg_replace('/[\'\s| ]+/', '', $value['factory']);            //生产厂家
                            } else {
                                $val['factory'] = '';
                            }
                            if (!empty($value['productday'])) {
                                $val['productday'] = preg_replace('/[\'\s| ]+/', '', $value['productday']);      //出厂日期
                            } else {
                                $val['productday'] = $date;
                            }
                            if (!empty($value['lastcheckday'])) {
                                $val['lastcheckday'] = preg_replace('/[\'\s| ]+/', '', $value['lastcheckday']);  //上次安检时间
                            } else {
                                $val['lastcheckday'] = $date;
                            }
                            if (!empty($value['checkpartment'])) {
                                $val['checkpartment'] = preg_replace('/[\'\s| ]+/', '', $value['checkpartment']); //检测部门
                            } else {
                                $val['checkpartment'] = '';
                            }
                            if (!empty($value['yearnum'])) {
                                $val['yearnum'] = preg_replace('/[\'\s| ]+/', '', $value['yearnum']); //使用年限
                            } else {
                                $val['yearnum'] = 0;
                            }
                            if (!empty($value['productday']) && !empty($value['yearnum'])) {
                                $val['validityday'] = date('Y-m-d', strtotime($value['productday'] . "+" . $value['yearnum'] . " year"));    //有效期
                            } else {
                                $val['validityday'] = $date;
                            }

                            /*if (!empty($value['validityday'])) {
                                $val['retireday'] = $value['validityday']; //报废日期
                            } else {
                                $val['retireday'] = '';
                            }*/
                            if (!empty($value['detectioncycle']) && !empty($value['detectioncycle'])) {
                                $val['detectioncycle'] = preg_replace('/[\'\s| ]+/', '', $value['detectioncycle']); //检测周期
                            } else {
                                $val['detectioncycle'] = 0;
                            }
                            if (!empty($value['lastcheckday']) && !empty($value['detectioncycle'])) {
                                $val['nextcheckday'] = date('Y-m-d', strtotime($value['lastcheckday'] . "+" . $value['detectioncycle'] . " day"));  //下次检测时间
                            } else {
                                $val['nextcheckday'] = $date;
                            }

                            if (!empty($value['emptyweight'])) {
                                $val['emptyweight'] = preg_replace('/[\'\s| ]+/', '', $value['emptyweight']);        //钢瓶净重
                            } else {
                                $val['emptyweight'] = 0;
                            }
                            if (!empty($value['setfillweight'])) {
                                $val['setfillweight'] = preg_replace('/[\'\s| ]+/', '', $value['setfillweight']);    //钢瓶重装重量
                            } else {
                                $val['setfillweight'] = 0;
                            }
                            if (!empty($value['emptyweight']) && !empty($value['setfillweight'])) {
                                $val['settotalweight'] = $value['emptyweight'] + $value['setfillweight']; //钢瓶总重量
                            } else {
                                $val['settotalweight'] = 0;
                            }
                            if (!empty($value['factorymark'])) {
                                $val['factorymark'] = preg_replace('/[\'\s| ]+/', '', $value['factorymark']);        //出厂钢印号
                            } else {
                                $val['factorymark'] = '';
                            }

                            $val['enterprisemark'] = preg_replace('/[\'\s| ]+/', '', $value['enterprisemark']);  //钢印号
                            $val['chipid'] = preg_replace('/[\'\s| ]+/', '', $value['chipid']);                  //芯片号
                            if (!empty($value['valvetype'])) {
                                $val['valvetype'] = preg_replace('/[\'\s| ]+/', '', $value['valvetype']);            //阀门类型
                            } else {
                                $val['valvetype'] = '';
                            }
                            if (!empty($value['govcert'])) {
                                $val['govcert'] = preg_replace('/[\'\s| ]+/', '', $value['govcert']);                //政府使用证
                            } else {
                                $val['govcert'] = '';
                            }

                            $val['enterpriselable'] = preg_replace('/[\'\s| ]+/', '', $value['enterpriselable']); //企业标识
                            $val['infolable'] = preg_replace('/[\'\s| ]+/', '', $value['infolable']);            //信息标签
                            
                            if (!empty($value['onedimcode'])) {
                                $val['onedimcode'] = preg_replace('/[\'\s| ]+/', '', $value['onedimcode']);          //一维码
                            } else {
                                $val['onedimcode'] = '';
                            }
                            if (!empty($value['twodimcode'])) {
                                $val['twodimcode'] = preg_replace('/[\'\s| ]+/', '', $value['twodimcode']);          //二维码
                            } else {
                                $val['twodimcode'] = '';
                            }

                            $val['admin_user'] = $admin_user;
                            $val['createtime'] = $time;

                            if ($val['bottlegenre'] == 1) {
                                if ($id) {
                                    $where['chipid'] = $val['chipid'];
                                    $where['enterprisemark'] = $val['enterprisemark'];
                                    $where['_logic'] = 'OR';
                                    $map["_complex"] = $where;
                                    $map['id'] = array('neq', $id);         //编辑
                                    $bottleInfo = LibF::M('bottlebaseinfo')->where($map)->find();
                                    if (!empty($bottleInfo)) {
                                        $returnData['errorbottle'][] = $val;
                                    } else {
                                        $bottlegj['number'] = $val['enterprisemark'];
                                        $bottlegj['xinpian'] = $val['chipid'];
                                        $bottlegj['comment'] = '钢瓶信息更新';
                                        $bottlegj['time_created'] = $time;
                                        $bottletransfer[] = $bottlegj;

                                        $returnData['updatebottle'][] = $val;
                                    }
                                } else {
                                    $where['chipid'] = $val['chipid'];  //创建
                                    $where['enterprisemark'] = $val['enterprisemark'];
                                    $where['_logic'] = 'OR';

                                    $bottleInfo = LibF::M('bottlebaseinfo')->where($where)->find();
                                    if (!empty($bottleInfo)) {
                                        $returnData['errorbottle'][] = $val;
                                    } else {
                                        $bottlegj['number'] = $val['enterprisemark'];
                                        $bottlegj['xinpian'] = $val['chipid'];
                                        $bottlegj['comment'] = '钢瓶初始化';
                                        $bottlegj['time_created'] = $time;
                                        $bottletransfer[] = $bottlegj;

                                        $returnData['bottle'][] = $val;
                                    }
                                }
                            } else {
                                if ($id) {
                                    $where['id'] = array('neq', $id);         //编辑
                                    $where['enterprisemark'] = $val['enterprisemark'];

                                    $bottleInfo = LibF::M('bottlebaseinfo')->where($where)->find();
                                    if (!empty($bottleInfo)) {
                                        $returnData['errorbottle'][] = $val;
                                    } else {
                                        unset($val['chipid']);
                                        $bottlegj['number'] = $val['enterprisemark'];
                                        $bottlegj['comment'] = '钢瓶信息更新';
                                        $bottlegj['time_created'] = $time;
                                        $bottletransfer[] = $bottlegj;

                                        $returnData['updatebottle'][] = $val;
                                    }
                                } else {
                                    $where['enterprisemark'] = $val['enterprisemark'];
                                    $bottleInfo = LibF::M('bottlebaseinfo')->where($where)->find();
                                    if (!empty($bottleInfo)) {
                                        $returnData['errorbottle'][] = $val;
                                    } else {
                                        unset($val['chipid']);
                                        
                                        $bottlegj['number'] = $val['enterprisemark'];
                                        $bottlegj['comment'] = '钢瓶初始化';
                                        $bottlegj['time_created'] = $time;
                                        $bottletransfer[] = $bottlegj;

                                        $returnData['bottle'][] = $val;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($returnData['bottle']) && !empty($returnData['bottle'])) {
                        $status = LibF::M('bottlebaseinfo')->uinsertAll($returnData['bottle']);

                        $end_time = time();
                        $bottle_num = count($returnData['bottle']);
                        $bottlelog['bottletype'] = 0;
                        $bottlelog['bottlenum'] = $bottle_num;
                        $bottlelog['admin_user'] = $admin_user;
                        $bottlelog['start_time'] = $start_time;
                        $bottlelog['end_time'] = $end_time;
                        $bottlelog['createtime'] = $end_time;
                        LibF::M('bottleloglist')->add($bottlelog);

                        LibF::M('bottle_transfer_logs')->uinsertAll($bottletransfer);

                        $this->app->respond(1, $returnData);
                    } else if(isset($returnData['updatebottle']) && !empty($returnData['updatebottle'])){
                        foreach ($returnData['updatebottle'] as $uVal) {
                            $wherelist['id'] = $uVal['id'];
                            LibF::M('bottlebaseinfo')->where($wherelist)->save($uVal);
                        }

                        LibF::M('bottle_transfer_logs')->uinsertAll($bottletransfer);
                        $this->app->respond(1, $returnData);
                    } else {
                        $this->app->respond(1, $returnData);
                    }
                    
                } else {
                    $this->app->respond(-1, '当前未提交钢瓶初始化数据');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 根据条件获取钢瓶初始化记录
     */
    public function bottlelistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');
            $number = $this->getRequest()->getPost('number');
            $status = $this->getRequest()->getPost('status');

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pagesize;

            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time', $date);
            $param['end_time'] = $this->getRequest()->getPost('end_time', $date);
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';

            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                if ($start_time && $end_time)
                    $where['createtime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $where['admin_user'] = $admin_user;
                if (!empty($number)) {
                    $where['enterprisemark'] = array('like', '%' . $number . '%');
                }
                if (!empty($status)) {
                    $where['bottleattr'] = $status;
                }

                //分规格统计数据
                if ($page == 1) {
                    $returnTotal = LibF::M('bottlebaseinfo')->field('bottle_kind,count(*) as num')->where($where)->group('bottle_kind')->select();
                    if (!empty($returnTotal)) {
                        $bottleType = $this->getBottleType();
                        foreach ($returnTotal as &$value) {
                            $value['bottle_name'] = isset($bottleType[$value['bottle_kind']]) ? $bottleType[$value['bottle_kind']]['bottletype'] : '当前规格不存在';
                        }
                    }
                }

                $pageStart = ($param['page'] - 1) * $param['pagesize'];
                $data = LibF::M('bottlebaseinfo')->where($where)->order('createtime desc')->limit($pageStart, $param['pagesize'])->select();
                if (!empty($data)) {
                    $returnData['dataTotal'] = isset($returnTotal) ? $returnTotal : array();
                    foreach($data as &$value){
                        $value['createtime'] = !empty($value['createtime']) ? date('Y-m-d H:i',$value['createtime']) : '';
                    }
                    $returnData['data'] = $data;
                    
                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-1, '当前钢瓶不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 根据钢印号|芯片号获取钢瓶信息
     */
    public function bottleinfoAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $number = $this->getRequest()->getPost('number');

            $returnData = array();
            if ($token) {
                $where['chipid'] = $number;
                $where['enterprisemark'] = $number;
                $where['_logic'] = 'OR';
                $returnData = LibF::M('bottlebaseinfo')->where($where)->find();
                if (!empty($returnData)) {
                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-1, '当前钢瓶不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 根据一维码|二维码获取钢瓶信息
     */
    public function bottledetailsAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $code = $this->getRequest()->getPost('code');

            $returnData = array();
            if ($token) {
                $where['onedimcode'] = $number;
                $where['twodimcode'] = $number;
                $where['_logic'] = 'OR';
                $returnData = LibF::M('bottlebaseinfo')->where($where)->find();
                if (!empty($returnData)) {
                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-1, '当前钢瓶不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 获取钢瓶属性信息
     */
    public function bottleobjectAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);

            if ($token) {
                $factory = LibF::M('bottlefactory')->select();  //生产厂家
                $bottlekind = LibF::M('bottlekind')->select();  //钢瓶类型
                $bottletype = LibF::M('bottletype')->select();  //钢瓶规格
                $bottleweight = LibF::M('bottleweight')->select(); //钢瓶重量
                $bottleunit = LibF::M('bottledetectionunit')->select(); //钢瓶检测单位
                $returnData = array('factory' => $factory, 'bottlekind' => $bottlekind, 'bottletype' => $bottletype, 'bottleweight' => $bottleweight, 'bottleunit' => $bottleunit);
                $returnData['bottlestatus'] = $this->bottleStatusJson;

                $this->app->respond(1, $returnData);
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 充装工添加充装记录
     */
    public function fillbottleAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            if ($token) {
                $admin_user = $this->getRequest()->getPost('admin_user');
                $fille_bottle = $this->getRequest()->getPost('fill_bottle');  //json串
                if (!empty($admin_user) && !empty($fille_bottle)) {
                    $time = $start_time = time();
                    
                    $bottletransfer = array();
                    $returnData = array('errorfill' => array(), 'fill' => array());
                    $fille_arr = json_decode($fille_bottle, true);
                    if (isset($fille_arr['fill_bottle']) && !empty($fille_arr['fill_bottle'])) {
                        $numberArray = array();
                        foreach ($fille_arr['fill_bottle'] as $value) {
                            $val['bottletype'] = preg_replace('/[\'\s| ]+/', '', $value['bottletype']);    //规格id;
                            $val['bottle_kind'] = preg_replace('/[\'\s| ]+/', '', $value['bottle_kind']);    //规格id
                            $val['bottlemodel'] = preg_replace('/[\'\s| ]+/', '', $value['bottlemodel']);
                            $val['bottlegenre'] = preg_replace('/[\'\s| ]+/', '', $value['bottlegenre']); //0非智能瓶1智能
                            if (!empty($value['bottleattr']))
                                $val['bottleattr'] = preg_replace('/[\'\s| ]+/', '', $value['bottleattr']);

                            $val['bottlestate'] = 2;

                            $val['emptyweight'] = preg_replace('/[\'\s| ]+/', '', $value['emptyweight']);
                            $val['fillweight'] = preg_replace('/[\'\s| ]+/', '', $value['fillweight']);
                            if (!empty($value['emptyweight']) && !empty($value['fillweight']))
                                $val['totalweight'] = $value['emptyweight'] + $value['fillweight'];

                            $val['chipid'] = preg_replace('/[\'\s| ]+/', '', $value['chipid']);
                            $val['enterprisemark'] = preg_replace('/[\'\s| ]+/', '', $value['enterprisemark']);

                            $val['admin_user'] = $admin_user;
                            $val['createtime'] = $time;

                            $where['chipid'] = $val['chipid'];
                            $where['enterprisemark'] = $val['enterprisemark'];
                            $where['_logic'] = 'OR';
                            $bottleInfo = LibF::M('bottlebaseinfo')->where($where)->find();
                            if (!empty($bottleInfo)) {
                                $bottlegj['number'] = $val['enterprisemark'];
                                $bottlegj['xinpian'] = $val['chipid'];
                                $bottlegj['comment'] = '钢瓶充装成功重装重量' . $val['fillweight'];
                                $bottlegj['time_created'] = $time;
                                $bottletransfer[] = $bottlegj;

                                $numberArray[] = $bottlegj['number'];

                                $returnData['fill'][] = $val;
                            } else {
                                $returnData['errorfill'][] = $val;
                            }
                        }

                        if (isset($returnData['fill']) && !empty($returnData['fill'])) {
                            $status = LibF::M('bottlefill')->uinsertAll($returnData['fill']);

                            $end_time = time();
                            $bottle_num = count($returnData['fill']);
                            $bottlelog['bottletype'] = 1;
                            $bottlelog['bottlenum'] = $bottle_num;
                            $bottlelog['admin_user'] = $admin_user;
                            $bottlelog['start_time'] = $start_time;
                            $bottlelog['end_time'] = $end_time;
                            $bottlelog['createtime'] = $end_time;
                            LibF::M('bottleloglist')->add($bottlelog);

                            if (!empty($bottletransfer))
                                LibF::M('bottle_transfer_logs')->uinsertAll($bottletransfer);

                            if (!empty($numberArray)) {
                                $uWhere['enterprisemark'] = array('in', $numberArray);
                                LibF::M('bottlebaseinfo')->where($uWhere)->save(array('bottleattr' => 2));
                            }

                            $this->app->respond(1, $returnData);
                        } else {
                            $this->app->respond(1, $returnData);
                        }
                    } else {
                        $this->app->respond(-1, '提交钢瓶信息数据不正确');
                    }
                } else {
                    $this->app->respond(-1, '当前未提交钢瓶充装数据');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 充装工充装记录
     */
    public function fillbottlelistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pagesize;

            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time', $date);
            $param['end_time'] = $this->getRequest()->getPost('end_time', $date);
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';

            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                if ($start_time && $end_time)
                    $where['createtime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $where['admin_user'] = $admin_user;
                
                //分规格统计数据
                if ($page == 1) {
                    $returnTotal = LibF::M('bottlefill')->field('bottle_kind,count(*) as num,sum(fillweight) as total')->where($where)->group('bottle_kind')->select();
                    if (!empty($returnTotal)) {
                        $bottleType = $this->getBottleType();
                        foreach ($returnTotal as &$value) {
                            $value['bottle_name'] = isset($bottleType[$value['bottle_kind']]) ? $bottleType[$value['bottle_kind']]['bottletype'] : '当前规格不存在';
                        }
                    }
                }

                $pageStart = ($param['page'] - 1) * $param['pagesize'];
                $data = LibF::M('bottlefill')->where($where)->order('createtime desc')->limit($pageStart, $param['pagesize'])->select();
                if (!empty($data)) {
                    $returnData['dataTotal'] = isset($returnTotal) ? $returnTotal : array();
                    $returnData['data'] = $data;

                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-1, '当前充装数据不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 获取钢瓶管理操作日志
     */
    public function bottleloglistAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');
            $type = $this->getRequest()->getPost('type',1);

            $page = $this->getRequest()->getPost('page', 1);
            $param['page'] = $page;
            $param['pagesize'] = $this->pagesize;

            $date = date("Y-m-d");
            $param['start_time'] = $this->getRequest()->getPost('start_time', $date);
            $param['end_time'] = $this->getRequest()->getPost('end_time', $date);
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';

            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                if ($start_time && $end_time)
                    $where['createtime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'and');

                $where['admin_user'] = $admin_user;
                $where['bottletype'] = $type;

                $pageStart = ($param['page'] - 1) * $param['pagesize'];
                
                $returnData = LibF::M('bottleloglist')->where($where)->order('createtime desc')->limit($pageStart, $param['pagesize'])->select();
                if (!empty($returnData)) {
                    foreach ($returnData as $key => &$value) {
                        $value['start_time'] = date('Y-m-d H:i:s', $value['start_time']);
                        $value['end_time'] = date('Y-m-d H:i:s', $value['end_time']);
                    }
                    $this->app->respond(1, $returnData);
                } else {
                    if ($type == 1) {
                        $this->app->respond(-1, '当前充装数据不存在');
                    } else {
                        $this->app->respond(-1, '当前初始化数据不存在');
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
     * 获取当前钢瓶统计数量
     */
    public function bottlestockAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');

            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                $where['admin_user'] = $admin_user;

                $grouplist = 'bottlegenre,bottle_kind';
                $filed = 'bottlegenre,bottle_kind,count(*) as total';
                $returnData = LibF::M('bottlebaseinfo')->field($filed)->where($where)->group($grouplist)->order('bottlegenre desc')->select();
                if (!empty($returnData)) {
                    $bottleType = $this->getBottleType();
                    $bottleObject = $this->bottleType;

                    $dataArray = array('total' => 0,'zn_bottle' => NULL, 'fzn_bottle' => NULL);
                    foreach ($returnData as $key => &$value) {
                        $value['bottle_type'] = isset($bottleObject[$value['bottlegenre']]) ? $bottleObject[$value['bottlegenre']] : '当前钢瓶类型不存在';
                        $value['bottle_name'] = isset($bottleType[$value['bottle_kind']]) ? $bottleType[$value['bottle_kind']]['bottletype'] : '当前规格不存在';
                        if ($value['bottlegenre'] == 0) {
                            $dataArray['fzn_bottle']['name'] = $value['bottle_type'];
                            $dataArray['fzn_bottle']['total'] += $value['total'];
                            $dataArray['fzn_bottle']['data'][] = $value;
                        } else {
                            $dataArray['zn_bottle']['name'] = $value['bottle_type'];
                            $dataArray['zn_bottle']['total'] += $value['total'];
                            $dataArray['zn_bottle']['data'][] = $value;
                        }
                        $dataArray['total'] += $value['total'];
                    }
                    $this->app->respond(1, $dataArray);
                } else {
                    $this->app->respond(-1, '当前充装数据不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取钢瓶类型统计数据
     */
    public function bottlestateAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');
            $bottlegenre = $this->getRequest()->getPost('bottlegenre', 0);

            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                $where['admin_user'] = $admin_user;
                $where['bottlegenre'] = $bottlegenre;

                $grouplist = 'bottle_kind,bottleattr';
                $filed = 'bottleattr,bottle_kind,count(*) as total';
                $returnData = LibF::M('bottlebaseinfo')->field($filed)->where($where)->group($grouplist)->order('bottle_kind desc')->select();
                if (!empty($returnData)) {
                    $bottleType = $this->getBottleType();
                    $bottleStatus= $this->bottleStatus;
                    $bottleObject = $this->bottleType;
                    
                    $newData = array();
                    $dataArray = array('total' => 0,'data' => array());
                    foreach ($returnData as $key => &$value) {
                        $value['bottle_status'] = isset($bottleStatus[$value['bottleattr']]) ? $bottleStatus[$value['bottleattr']] : '当前钢瓶类型不存在';
                        $value['bottle_name'] = isset($bottleType[$value['bottle_kind']]) ? $bottleType[$value['bottle_kind']]['bottletype'] : '当前规格不存在';

                        if (isset($newData[$value['bottleattr']])) {
                            $newData[$value['bottleattr']]['ktotal'] += $value['total'];
                        } else {
                            $newData[$value['bottleattr']]['ktotal'] = $value['total'];
                        }
                        $newData[$value['bottleattr']]['bottleattr'] = $value['bottleattr'];
                        $newData[$value['bottleattr']]['kdata'][] = $value;

                        $dataArray['total'] += $value['total'];
                    }
                    if(!empty($newData)){
                        foreach ($newData as $kVal){
                            $dataArray['data'][] = $kVal;
                        }
                    }
                    $this->app->respond(1, $dataArray);
                } else {
                    $this->app->respond(-1, '当前充装数据不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取充装数据统计
     */
    public function bottlefillAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');
            $type = $this->getRequest()->getPost('type'); 

            $date = date('Y-m-d');
            
            $param = array();
            $timeData = new TimedataModel();
            switch ($type) {
                case 2://昨天
                    $ztdate = date("Y-m-d", strtotime("-1 day"));
                    $param['start_time'] = $ztdate;
                    $param['end_time'] = $ztdate . ' 23:59:59';
                    break;
                case 3://本周
                    $bzdate = $timeData->this_monday(0, false);

                    $param['start_time'] = $bzdate;
                    $param['end_time'] = $date . ' 23:59:59';
                    break;
                case 4://上周
                    $szdate = $timeData->last_monday(0, false);
                    $bzdate = $timeData->this_monday(0, false);

                    $param['start_time'] = $szdate;
                    $param['end_time'] = $bzdate;
                    break;
                case 5: //本月
                    $bydate = $timeData->month_firstday(0, false);

                    $param['start_time'] = $bydate;
                    $param['end_time'] = $date . ' 23:59:59';
                    break;
                case 6: //上月
                    $sydate = $timeData->lastmonth_firstday(0, FALSE);
                    $bydate = $timeData->month_firstday(0, false);

                    $param['start_time'] = $sydate;
                    $param['end_time'] = $bydate;
                    break;
                case 7://本季度
                    $bjdate = $timeData->season_firstday(0, FALSE);

                    $param['start_time'] = $bjdate;
                    $param['end_time'] = $date . ' 23:59:59';
                    break;
                case 8://本年
                    $bndate = date('Y-01-01');

                    $param['start_time'] = $bndate;
                    $param['end_time'] = $date . ' 23:59:59';
                    break;
                case 9: //上季度
                    $sjdate = $timeData->season_lastday(FALSE);

                    $param['start_time'] = $sjdate['firstday'];
                    $param['end_time'] = $sjdate['lastday'];
                    break;
                case 10: //上半年

                    $param['start_time'] = date('Y-01-01');
                    $param['end_time'] = date('Y-06-01');
                    break;
                case 11: //下半年

                    $param['start_time'] = date('Y-06-01');
                    $param['end_time'] = date('Y-12-31') . ' 23:59:59';
                    break;
                case 1:
                default :
                    $param['start_time'] = $this->getRequest()->getPost('time_start', $date);
                    $param['end_time'] = $this->getRequest()->getPost('time_end', $date);
                    $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';
                    break;
            }
            
            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                $where['admin_user'] = $admin_user;

                if (!empty($param['start_time']) && !empty($param['end_time'])) {
                    $where['createtime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'])), 'AND');
                } else {
                    if ($param['start_time'])
                        $where['createtime'] = array('egt', strtotime($param['start_time']));
                }

                $grouplist = 'bottlegenre,bottle_kind';
                $filed = 'bottlegenre,bottle_kind,count(*) as num,sum(fillweight) as total';
                $returnData = LibF::M('bottlefill')->field($filed)->where($where)->group($grouplist)->order('bottlegenre desc')->select();
                if (!empty($returnData)) {
                    
                    $bottleType = $this->getBottleType();
                    $bottleObject = $this->bottleType;

                    $dataArray = array('total' => 0, 'num' => 0, 'zn_bottle' => NULL, 'fzn_bottle' => NULL);
                    foreach ($returnData as $key => &$value) {
                        $value['bottle_type'] = isset($bottleObject[$value['bottlegenre']]) ? $bottleObject[$value['bottlegenre']] : '当前钢瓶类型不存在';
                        $value['bottle_name'] = isset($bottleType[$value['bottle_kind']]) ? $bottleType[$value['bottle_kind']]['bottletype'] : '当前规格不存在';
                        if ($value['bottlegenre'] == 0) {
                            $dataArray['fzn_bottle']['name'] = $value['bottle_type'];
                            $dataArray['fzn_bottle']['total'] += $value['total'];
                            $dataArray['fzn_bottle']['num'] += $value['num'];
                            $dataArray['fzn_bottle']['data'][] = $value;
                        } else {
                            $dataArray['zn_bottle']['name'] = $value['bottle_type'];
                            $dataArray['zn_bottle']['total'] += $value['total'];
                            $dataArray['zn_bottle']['num'] += $value['num'];
                            $dataArray['zn_bottle']['data'][] = $value;
                        }
                        $dataArray['total'] += $value['total'];
                        $dataArray['num'] += $value['num'];
                    }
                    $this->app->respond(1, $dataArray);
                } else {
                    $this->app->respond(-1, '当前充装数据不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }

    /**
     * 首页统计汇总
     */
    public function staticsdataAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $admin_user = $this->getRequest()->getPost('admin_user');

            $returnData = array();
            if ($token && !empty($admin_user)) {
                $where = array();
                $where['admin_user'] = $admin_user;

                $grouplist = 'bottle_kind';
                $filed = 'bottle_kind,count(*) as num';
                $returnData = LibF::M('bottlebaseinfo')->field($filed)->where($where)->group($grouplist)->order('num desc')->select();
                if (!empty($returnData)) {
                    
                    $bottleType = $this->getBottleType();

                    $dataArray = array('total' => 0, 'lt_total' => 0, 'kc_total' => 0, 'data' => NULL);
                    foreach ($returnData as $key => &$value) {
                        $value['bottle_name'] = isset($bottleType[$value['bottle_kind']]) ? $bottleType[$value['bottle_kind']]['bottletype'] : '当前规格不存在';
                        $dataArray['data'][] = $value;
                        $dataArray['total'] += $value['num']; //钢瓶总量
                    }
                    $dataArray['kc_total'] = $dataArray['total'];
                    $this->app->respond(1, $dataArray);
                } else {
                    $this->app->respond(-1, '当前充装数据不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 钢瓶轨迹
     */
    public function bottletransferAction() {
        if ($this->getRequest()->isPost()) {
            $token = $this->getRequest()->getPost('token', 1234);
            $number = $this->getRequest()->getPost('number');
            $page = $this->getRequest()->getPost('page', 1);

            $returnData = array();
            if ($token && !empty($number)) {
                $where['enterprisemark'] = $w['number'] = $number;
                $data = LibF::M('bottlebaseinfo')->where($where)->find();
                if (!empty($data)) {
                    $param['page'] = $page;
                    $param['pagesize'] = $this->pagesize;
                    
                    $pageStart = ($param['page'] - 1) * $param['pagesize'];
                    $returnData = LibF::M('bottle_transfer_logs')->where($w)->order('time_created desc')->limit($pageStart, $param['pagesize'])->select();
                    if (!empty($returnData)) {
                        foreach ($returnData as &$value) {
                            $value['time'] = date('Y-m-d H:i', $value['time_created']);
                        }
                    }

                    $this->app->respond(1, $returnData);
                } else {
                    $this->app->respond(-1, '当前钢瓶不存在');
                }
            } else {
                $this->app->respond(-3, '账号格式不正确');
            }
        } else {
            $this->app->respond(-4, '未提交');
        }
    }
    
    /**
     * 获取用户信息
     * 
     * @param $mobile
     */
    protected function getUserInfo($mobile = '', $where = array()) {
        if (!empty($mobile)) {
            $where['mobile_phone'] = $mobile;
        }
        $where['status'] = 0;

        $userInfo = LibF::M('admin_user')->where($where)->find();

        return $userInfo;
    }

    protected function getBottleType($where = array()) {

        $returnData = array();
        $data = LibF::M('bottletype')->where($where)->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $returnData[$value['id']] = $value;
            }
        }

        return $returnData;
    }

}