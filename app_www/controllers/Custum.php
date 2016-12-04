<?php 
/**
 * 客户管理
 * 备注：客户管理分为总的客户管理、门店自己客户管理
 */

class CustumController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;
    protected $shopLevelMap;

    public function init() {
        parent::init();
        $this->modelD = LibF::D('Custum');

        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        if (!empty($adminInfo)) {
            $this->shop_id = $adminInfo['shop_id'];
            $this->shop_level = $adminInfo['shop_level'];
            $userData['user_id'] = $adminInfo['user_id'];
            $userData['username'] = $adminInfo['username'];
            $userData['photo'] = $adminInfo['photo'];
            $userData['mobile_phone'] = $adminInfo['mobile_phone'];
            $this->user_info = $userData;
            
            $this->shopLevelMap = array(2 => '中心店',3 =>'卫星店',4 => '社区店', 5=> '合伙人');
        }
    }

    public function addAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('kid');
            $param = $_POST;
            unset($param['kid']);
            if (isset($param['source']) && !empty($param['source'])) {
                $param['source'] = implode(',', $param['source']);
            }
            if (empty($param['sheng'])) {
                unset($param['sheng']);
            }
            if (empty($param['shi'])) {
                unset($param['shi']);
            }
            if (empty($param['qu'])) {
                unset($param['qu']);
            }
            if (empty($param['cun'])) {
                unset($param['cun']);
            }
            if (empty($param['comment'])) {
                unset($param['comment']);
            }

            if (!empty($id)) {
                
                $where['mobile_phone'] = $param['mobile_phone'];
                $where['card_sn'] = !empty($param['card_sn']) ? $param['card_sn'] : '';
                $where['_logic'] = 'OR';
                $map['_complex'] = $where;
                $map['_logic'] = "AND";
                $map['kid'] = array('neq', $id);
                $data = LibF::M('kehu')->where($map)->find();
                if (empty($data)) {
                    $param['utime'] = time();
                    $this->modelD->edite('edite', $id, $param);
                    $msg = '更新成功';
                } else {
                    $msg = '更新失败，手机号重复';
                }
            } else {
                $where['mobile_phone'] = $param['mobile_phone'];
                $where['card_sn'] = $param['card_sn'];
                $where['_logic'] = 'OR';
                $data = LibF::M('kehu')->where($where)->find();
                if (empty($data)) {
                    $param['ctime'] = time();
                    $password = $param['mobile_phone'];
                    $kehuModel = new KehuModel();
                    $show_password = $kehuModel->encryptUserPwd($password);
                    $param['password'] = $show_password;
                    if (isset($param['mobile_phone']) && isset($param['mobile_list'])) {
                        $param['mobile_list'] = $param['mobile_list'] . ',' . $param['mobile_phone'];
                    }
                    $app = new App();
                    $orderSn = $app->build_order_no();
                    $param['kehu_sn'] = 'kh' . $orderSn;
                    $this->modelD->add($param);
                    $msg = '添加成功';
                } else {
                    $msg = '添加失败，手机或者用户卡重复';
                }
            }
            $this->success($msg, '/custum/index');
        }
        $data = LibF::LC('shop')->toArray();
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shopLevelMap);
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('kid'));
        $data = $this->modelD->edite('', $id);
        !empty($data['rules']) && $data['rules'] = explode(',', $data['rules']);
        $data['list_rule'] = LibF::D('AdminRule')->getList();
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $this->_view->assign('is_shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shopLevelMap);
        $this->_view->display('custum/add.phtml');
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('kid'));
        
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/custum/index');
    }

    public function indexAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        $orderlist = '';
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['mobile_phone'])) {
                $condition['mobile_phone'] = $param['mobile_phone'];
                $condition['mobile_list'] = array('like','%'.$param['mobile_phone'].'%');
                $condition['_logic'] = 'OR';
                $where['_complex'] = $condition;
                $getParam[] = "mobile_phone=" . $param['mobile_phone'];
            }
            
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $param['shop_id'];
            } else {
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['ktype'])){
                $where['ktype'] = $param['ktype'];
                $getParam[] = "ktype=" . $param['ktype'];
            }
            if (!empty($param['kehu_sn'])){
                $where['kehu_sn'] = $param['kehu_sn'];
                $getParam[] = "kehu_sn=" . $param['kehu_sn'];
            }
            if (!empty($param['user_name'])){
                $where['user_name'] = $param['user_name'];
                $getParam[] = "user_name=" . $param['user_name'];
            }
            if (!empty($param['card_sn'])){
                $where['card_sn'] = $param['card_sn'];
                $getParam[] = "card_sn=" . $param['card_sn'];
            }
            
            if (!empty($param['paytype'])){
                $where['paytype'] = $param['paytype'];
                $getParam[] = "paytype=" . $param['paytype'];
            }
            if (!empty($param['source'])){
                $where['source'] = $param['source'];
                $getParam[] = "source=" . $param['source'];
            }
            if (!empty($param['status'])){
                $where['status'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }
            if (!empty($param['address'])){
                $where['address'] = array('like', $param['address'] . "%");
                $getParam[] = "address=" . $param['address'];
            }
            
            if (!empty($param['region_1'])) {
                $where['sheng'] = $param['region_1'];
                $getParam[] = "region_1=" . $param['region_1'];
            }
            if (!empty($param['region_2'])) {
                $where['shi'] = $param['region_2'];
                $getParam[] = "region_2=" . $param['region_2'];
            }
            if (!empty($param['region_3'])) {
                $where['qu'] = $param['region_3'];
                $getParam[] = "region_3=" . $param['region_3'];
            }
            if (!empty($param['region_4'])) {
                $where['cun'] = $param['region_4'];
                $getParam[] = "region_4=" . $param['region_4'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }

            if (isset($param['ordertype']) && !empty($param['ordertype'])) { //排序条件
                $desc = ($param['orderlist'] == 'up') ? 'desc' : 'asc';
                $orderlist = ($param['ordertype'] == 'khorder') ? 'ctime ' . $desc : (($param['ordertype'] == 'updatetime') ? 'utime ' . $desc : 'buy_time ' . $desc);

                $getParam[] = "ordertype=" . $param['ordertype'];
                $getParam[] = "orderlist=" . $param['orderlist'];
                $param['orderlist'] = !empty($param['orderlist']) ? '' : 'up';
            }
            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }

        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
        }
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $orderlist = !empty($orderlist) ? $orderlist : 'kid desc';

        $kehuModel = new FillingModel();
        $data = $kehuModel->getDataList($param, 'kehu', $where, $orderlist);
        $this->_view->assign($data);

        $where['status'] = 0;
        $totalData = LibF::M('kehu')->field('ktype,count(*) as num,sum(buy_time) as times')->where($where)->group('ktype')->order('times desc')->select();
        $this->_view->assign('totalData', $totalData);  //统计问题

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $typeManagerModel = new TypemanagerModel();
        $orderData = $typeManagerModel->getData(array(5, 6));

        $custum_source = isset($orderData['5']) ? $orderData['5'] : array(); //客户来源
        $kehuType = isset($orderData[6]) ? $orderData[6] : array();  //客户类型
        $kehuStatus = array(0 => '正常', 1 => '停用', 2 => '删除');
        $payType = array(1 => '现金', 2 => '网上支付', 4 => '月结'); //结算方式
        $kehuType = array(1=> '居民', 2 => '商业', 3 => '工业');

        $this->_view->assign('custum_source', $custum_source);
        $this->_view->assign('kehuType', $kehuType);
        $this->_view->assign('kehuStatus', $kehuStatus);
        $this->_view->assign('payType', $payType);
        
        if ($tempType == 'new') {
            $this->_view->display('custum/index_new.phtml');
        }
    }
    
    public function detialAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面

        $kid = $this->getRequest()->getQuery('kid');
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign($param);
        if (!empty($kid)) {
            $where = array('kid' => $kid);
            $kehuModel = new FillingModel();
            $data = $kehuModel->getDataInfo('kehu', $where);
            $this->_view->assign('data', $data);

            //$bottle_data = !empty($data['bottle_data']) ? json_decode($data['bottle_data'],true) : array();  //用户余额
            //当前用户优惠券余额
            $promotionsModel = new KehuModel();
            $bottle_data = $promotionsModel->getUserPromotionsData($kid, true);
            $this->_view->assign('bottle_data', $bottle_data);

            $getParam[] = "kid=" . $kid;
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));

            //得到当前用户订单
            $param['kid'] = $kid;
            $orderData = $kehuModel->getDataList($param, 'order', $where, 'order_id desc');
            $this->_view->assign($orderData);

            $shopObject = ShopModel::getShopArray();
            $this->_view->assign('shopObject', $shopObject);

            $typeManagerModel = new TypemanagerModel();
            $orderData = $typeManagerModel->getData(array(5, 6, 7));

            $custum_source = isset($orderData['5']) ? $orderData['5'] : array(); //客户来源
            $kehuType = isset($orderData[6]) ? $orderData[6] : array();  //客户类型
            //$kehuStatus = isset($orderData[7]) ? $orderData[7] : array(); //客户状态
            $kehuStatus = array(0 => '正常', 1 => '停用', 2 => '删除');
            $payType = array(1 => '现金', 2 => '网上支付', 4 => '月结'); //结算方式
            $orderFs = array(
                1 => '送气工下单',2=>'app下单',3=>'网站下单',4 => '呼叫中心下单'
            );

            $this->_view->assign('custum_source', $custum_source);
            $this->_view->assign('kehuType', $kehuType);
            $this->_view->assign('kehuStatus', $kehuStatus);
            $this->_view->assign('payType', $payType);
            $this->_view->assign('orderFs', $orderFs);
            //订单状态
            $orderStatus = array(
                1 => '未派发', 2 => '配送中', 4 => '已送达', 5 => '问题订单'
            );
            //$orderStatus = isset($orderData[10]) ? $orderData[10] : '';
            $this->_view->assign('orderStatus', $orderStatus);

            $regionModel = new RegionModel();
            $regionObject = $regionModel->getRegionObject();
            $this->_view->assign('regionObject', $regionObject);

            //获取当前钢瓶规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            $this->_view->assign('bottleTypeData', $bottleTypeData);
            
            if ($tempType == 'new') {
                $this->_view->display('custum/detial_new.phtml');
            }
        } else {
            $this->error('当前用户不存在');
        }
    }

    public function paymentAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $bWhere['rq_kehu.shop_id'] = $bW['rq_kehu.shop_id'] = $this->shop_id;
                $this->_view->assign('is_show_shop', $this->shop_id);
            } else {
                if (!empty($param['shop_id'])) {
                    $bWhere['rq_kehu.shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['type'])) {
                $bWhere['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['username'])) {
                $bWhere['rq_kehu.user_name'] = array('like', '%' . $param['username'] . '%');
                $getParam[] = "username=" . $param['username'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $bWhere['rq_balance_list.time'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $bWhere['rq_kehu.shop_id'] = $bW['rq_kehu.shop_id'] = $this->shop_id;
                $this->_view->assign('is_show_shop', $this->shop_id);
            }
        }
        $bWhere['rq_kehu.status'] = 0;
        $bWhere['rq_balance_list.status'] = 1;

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        
        //获取用户充值消费记录
        $field = " rq_balance_list.*,rq_kehu.kehu_sn,rq_kehu.user_name ";
        $fillModel = new FillingModel();
        $dataList = $fillModel->getDataTableList($param, 'balance_list', 'rq_balance_list', 'rq_kehu', 'kid', $bWhere, 'rq_balance_list.id desc', $field);
        $this->_view->assign('extlist', $dataList['ext']);

        //$shopObject = ShopModel::getShopArray();
        //$this->_view->assign('shopObject', $shopObject);

        $bmodel = $bbmodel = new Model('balance_list');
        $leftJoin = " LEFT JOIN rq_kehu ON rq_balance_list.kid = rq_kehu.kid ";
        $totalData = $bmodel->join($leftJoin)->field('rq_balance_list.type,sum(rq_balance_list.money) as total')->where($bW)->group('rq_balance_list.type')->order('id desc')->select();

        $czTotal = $xfTotal = $yeTotal = 0;
        if (!empty($totalData)) {
            foreach ($totalData as $tVal) {
                if ($tVal['type'] == 1) {
                    $czTotal += $tVal['total'];
                } else if ($tVal['type'] == 2) {
                    $xfTotal += $tVal['total'];
                }
            }
        }
        $this->_view->assign('czTotal', $czTotal);
        $this->_view->assign('xfTotal', $xfTotal);

        $czData = $bbmodel->join($leftJoin)->field('distinct kid')->where(array('type' => 1))->count();
        $this->_view->assign('czNum', $czData);

        $w['balance'] = array('gt', 0);
        $yeData = LibF::M('kehu')->field('sum(balance) as total')->where($w)->find();
        $yeTotal = isset($yeData['total']) ? $yeData['total'] : 0;
        $this->_view->assign('yeTotal', $yeTotal);
    }

}
