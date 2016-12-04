<?php

/**
 * 应收账款
 */
class FinanceController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    public function init() {
        parent::init();
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
        }
    }
    
    /**
     * 应收账款
     */
    public function indexAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['rq_order_arrears.time'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (!empty($param['user_name'])) {
                $where['rq_kehu.user_name'] = $param['user_name'];
                $getParam[] = "user_name=" . $param['user_name'];
            }
            if ($param['shop_id']) {
                $where['rq_kehu.shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }else{
                if($this->shop_id){
                    $where['rq_kehu.shop_id'] = $this->shop_id;
                }
            }
            if (!empty($param['ktype'])) {
                $where['rq_kehu.ktype'] = $param['ktype'];
                $getParam[] = "ktype=" . $param['ktype'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        if ($this->shop_id) {
            $where['rq_kehu.shop_id'] = $this->shop_id;
            $this->_view->assign('is_show_shop', $this->shop_id);
            $w['shop_id'] = $this->shop_id;
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);
        
        $where['rq_order_arrears.type'] = 1;
        $where['rq_order_arrears.is_return'] = 0;

        $filed = 'rq_order_arrears.*,rq_kehu.ktype,rq_kehu.user_name,rq_kehu.shop_id,rq_kehu.mobile_phone,rq_kehu.ctime';

        $kehuModel = new FillingModel();
        $kehuData = $kehuModel->getDataTableList($param, 'order_arrears', 'rq_order_arrears', 'rq_kehu', 'kid', $where, 'rq_order_arrears.time desc', $filed);
        $this->_view->assign($kehuData);

        $reportModel = new Model('order_arrears');
        $filed = "rq_kehu.ktype,count(rq_kehu.kid) as num,sum(rq_order_arrears.money) as total ";
        $leftJoin = " LEFT JOIN rq_kehu ON rq_kehu.kid = rq_order_arrears.kid ";
        $tjData = $reportModel->join($leftJoin)->field($filed)->where($rwhere)->group('rq_kehu.ktype')->select();
        $this->_view->assign('tjData',$tjData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $typeManagerModel = new TypemanagerModel();
        $typeData = $typeManagerModel->getData(6);
        $this->_view->assign('kehuType', $typeData);
    }

    /**
     * 欠款记录
     */
    public function arearlistAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where['rq_order_arrears.type'] = $tWhere['type'] = 1;
        $where['rq_order_arrears.is_return'] = $tWhere['is_return'] = 0;
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['rq_order_arrears.time'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if ($param['shop_id']) {
                $where['rq_kehu.shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            } else {
                if ($this->shop_id) {
                    $where['rq_kehu.shop_id'] = $this->shop_id;
                }
            }
            if (!empty($param['ktype'])) {
                $where['rq_kehu.ktype'] = $param['ktype'];
                $getParam[] = "ktype=" . $param['ktype'];
            }
            if (!empty($param['contractno'])) {
                $where['rq_order_arrears.contractno'] = $param['contractno'];
                $getParam[] = "contractno=" . $param['contractno'];
            }
            if (!empty($param['arrears_type'])) {
                $where['rq_order_arrears.arrears_type'] = $param['arrears_type'];
                $getParam[] = "arrears_type=" . $param['arrears_type'];
            }
            
            if (!empty($param['kid'])) {
                $where['rq_order_arrears.kid'] = $param['kid'];
                $getParam[] = "kid=" . $param['kid'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        if ($this->shop_id) {
            $where['rq_kehu.shop_id'] = $this->shop_id;
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);
       
        $filed = 'rq_order_arrears.*,rq_kehu.ktype,rq_kehu.user_name,rq_kehu.shop_id';
        
        $kehuModel = new FillingModel();
        $kehuData = $kehuModel->getDataTableList($param, 'order_arrears', 'rq_order_arrears', 'rq_kehu', 'kid', $where, 'rq_order_arrears.time desc',$filed);
        $this->_view->assign($kehuData);

        $data = LibF::M('order_arrears')->field('arrears_type,sum(money) as total')->group('arrears_type')->where($tWhere)->select();
        $tMoney = 0;
        if (!empty($data)) {
            foreach ($data as $value) {
                $tMoney += $value['total'];
            }
        }
        $this->_view->assign('data', $data);
        $this->_view->assign('total', count($data));
        $this->_view->assign('tMoney', $tMoney);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $kehuType = array(1 => '居民用户', 2 => '商业用户');
        $this->_view->assign('kehuType', $kehuType);
        $arrearType = array(1 => '液化气欠款', 2 => '押金欠款', 3 => '配件欠款');
        $this->_view->assign('arrearType',$arrearType);
    }
    
    /**
     * 残液支出
     */
    public function raffinatAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['shop_id'] = $this->getRequest()->getPost('shop_id');
        $param['status'] = $this->getRequest()->getPost('status');
        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');

        $where = array();
        if (empty($param['shop_id']) && $this->shop_id) {
            $param['shop_id'] = $this->shop_id;
            $where['shop_id'] = $this->shop_id;
        }

        $param['raffinat'] = $where['raffinat'] = array('gt',0);
        $orderModel = new OrderModel();
        $data = $orderModel->orderlist($param);
        
        $this->_view->assign($data);
        $this->_view->assign('page',$param['page']);
        
        $shopObject = ShopModel::getShopArray();
        //获取门店
        $this->_view->assign('shopObject', $shopObject);
        $this->_view->assign('param',$param);
        
        //统计相关数据
        $statusTj = LibF::M('order')->field('sum(raffinat) as total,shop_id')->where($where)->group('shop_id')->order('total desc')->select();
        $this->_view->assign('statusTj',$statusTj);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }
    
    /**
     * 折旧支出
     */
    public function expenseAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['shop_id'] = $this->getRequest()->getPost('shop_id');
        $param['status'] = $this->getRequest()->getPost('status');
        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');

        $where = array();
        if (empty($param['shop_id']) && $this->shop_id) {
            $param['shop_id'] = $this->shop_id;
            $where['shop_id'] = $this->shop_id;
        }

        $param['depreciation'] = $where['depreciation'] = array('gt',0);
        $orderModel = new OrderModel();
        $data = $orderModel->orderlist($param);
        
        $this->_view->assign($data);
        $this->_view->assign('page',$param['page']);
        
        $shopObject = ShopModel::getShopArray();
        //获取门店
        $this->_view->assign('shopObject', $shopObject);
        $this->_view->assign('param',$param);
        
        //统计相关数据
        $statusTj = LibF::M('order')->field('sum(depreciation) as total,shop_id')->where($where)->group('shop_id')->order('total desc')->select();
        $this->_view->assign('statusTj',$statusTj);
        
        $this->_view->assign('shop_id', $this->shop_id);
    }
    
    /**
     * 废瓶
     */
    public function wasteAction(){
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');
        
        $financeModel = new FinanceModel();
        $data = $financeModel->getList($param, 'bottle_waste');
        if (!empty($data['ext']['list'])) {
            foreach ($data['ext']['list'] as $key => &$value) {
                $value['bottle'] = !empty($value['bottle']) ? json_decode($value['bottle'], true) : '';
            }
        }
        
        $this->_view->assign($data);
    }
    
    public function addwasteAction() {
        if (IS_POST) {
            $pdata['btype'] = $this->getRequest()->getPost('btype');
            $pdata['bnum'] = $this->getRequest()->getPost('bnum');

            $_btypes = BottleModel::getBottleType();
            $bottle = array_map(null, $pdata['btype'], $pdata['bnum']);
            foreach ($bottle as &$v) {
                $v['name'] = $_btypes[$v[0]];
            }
            $data['bottle'] = json_encode($bottle);
            $data['money'] = $this->getRequest()->getPost('money');
            $data['comment'] = $this->getRequest()->getPost('comment');
            
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['waste_no'] = "SR".$orderSn;
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['ctime'] = time();

            $status = LibF::M('bottle_waste')->add($data);
            
            $this->success('ok', '/finance/waste');
        }

        $data['btypes'] = BottleModel::getBottleType();
        $this->_view->assign($data);
    }
    
    /**
     * 残液
     */
    public function residualAction() {
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');
        
        $financeModel = new FinanceModel();
        $data = $financeModel->getList($param, 'bottle_residual');
        
        $this->_view->assign($data);
    }
    
    public function addresidualAction(){
        if (IS_POST) {
            $data['money'] = $this->getRequest()->getPost('money');
            $data['comment'] = $this->getRequest()->getPost('comment');
            
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['residual_no'] = "SR".$orderSn;
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['ctime'] = time();

            $status = LibF::M('bottle_residual')->add($data);
            
            $this->success('ok', '/finance/residual');
        }

        $data['btypes'] = BottleModel::getBottleType();
        $this->_view->assign($data);
    }
    
    /**
     * 钢瓶年检收费
     */
    public function yearlyinspectionAction() {
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $param['start_time'] = $this->getRequest()->getPost('start_time');
        $param['end_time'] = $this->getRequest()->getPost('end_time');
        
        $financeModel = new FinanceModel();
        $data = $financeModel->getList($param, 'year_inspection');
        if (!empty($data['ext']['list'])) {
            foreach ($data['ext']['list'] as $key => &$value) {
                $value['bottle'] = !empty($value['bottle']) ? json_decode($value['bottle'], true) : '';
            }
        }
        
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        //获取门店
        $this->_view->assign('shopObject', $shopObject);
    }

    public function addinspectionAction() {

        $shopObject = ShopModel::getShopArray();

        $bottleType = new BottletypeModel();
        $btypes = $bottleType->getBottleTypeArray();
        if (IS_POST) {
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['inspection_no'] = "NJ".$orderSn;
            
            $data['shop_id'] = $this->getRequest()->getPost('shop_id');
            $data['shop_type'] = isset($shopObject[$data['shop_id']]) ? $shopObject[$data['shop_id']]['level'] : '';
            $data['shop_name'] = isset($shopObject[$data['shop_id']]) ? $shopObject[$data['shop_id']]['shop_name'] : '';

            $data['comment'] = $this->getRequest()->getPost('comment');

            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];

            $pdata['btype'] = $this->getRequest()->getPost('btype');
            $pdata['bnum'] = $this->getRequest()->getPost('bnum');
            $bottle = array_map(null, $pdata['btype'], $pdata['bnum']);

            $money = 0;
            foreach ($bottle as &$v) {
                $v['name'] = $btypes[$v[0]]['bottle_name'];
                $money += $btypes[$v[0]]['yearly_inspection']*$v[1];
            }
            $data['bottle'] = json_encode($bottle);
            $data['money'] = $money;
            $data['ctime'] = time();
            
            $status = LibF::M('year_inspection')->add($data);
            $this->success('ok', '/finance/yearlyinspection');
        }

        //获取门店
        $this->_view->assign('shopObject', $shopObject);

        $data['btypes'] = $btypes;
        $this->_view->assign($data);
    }

    /**
     * 其它收入
     * 
     * 获取门店入库单数据
     */
    public function wholesaleAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['delivery_no'])) {
                $where['delivery_no'] = $param['delivery_no'];
                $getParam[] = "delivery_no=" . $param['delivery_no'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (empty($param['shop_id']) && $this->shop_id) {
                $where['shop_id'] = $this->shop_id;
            } else if ($param['shop_id']) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        } else {
            if ($this->shop_id)
                $where['shop_id'] = $this->shop_id;
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);

        $financeModel = new FillingModel();
        $data = $financeModel->getDataList($param,'delivery_detail',$where);
        $this->_view->assign($data);

        //统计类型
        $dataTotal = LibF::M('delivery_detail')->field('ftype,sum(num) as total,sum(money) as price')->group('ftype')->select();
        $this->_view->assign('dataTotal', $dataTotal);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    /**
     * 应收款简报
     */
    public function reportAction() {
        $shop_id = $this->getRequest()->getQuery('shop_id', $this->shop_id);

//当前门店应收款
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $statisticModel = new StatisticsDataModel();
        //$reportData = $statisticModel->reportShopData($shop_id);
        $reportData = $statisticModel->reportShopMoney($shop_id);

        $title = $show = $showData = $reportNewData = array();
        if (!empty($reportData)) {
            foreach ($reportData as &$value) {
                $reportNewData[$value['shop_id']][$value['arrears_type']] = $value['money'];
            }
            if (!empty($reportNewData)) {
                foreach ($reportNewData as $key => $val) {
                    $val['shop_name'] = isset($shopObject[$key]) ? $shopObject[$key]['shop_name'] : '未分配门店';

                    $title[] = $val['shop_name'];
                    $show[] = floatval($val[1]);
                    $showData[] = floatval($val[2]);
                }
            }
        }
        $this->_view->assign('reportData', $reportData);
        $this->_view->assign('reportNewData', $reportNewData);

        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));
        $this->_view->assign('showData', json_encode($showData));
    }

}