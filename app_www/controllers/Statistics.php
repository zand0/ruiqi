<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class StatisticsController extends \Com\Controller\Common {

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
    
    public function orderAction() {

        $shop_id = $this->getRequest()->getPost('shop_id',$this->shop_id);
        
//当天销售额度
        $jtData = strtotime(date('Y-m-d'));
        
        $statisticModel = new StatisticsDataModel();
        $jtTotal = $statisticModel->orderTotal($jtData,0,$shop_id); //本日销售统计
        $this->_view->assign('jtTotal',$jtTotal);
        
        $ztData = $jtData - (24 * 60 * 60);
        $ztTotal = $statisticModel->orderTotal($ztData,$jtTotal,$shop_id); //本日销售统计
        $this->_view->assign('ztTotal',$ztTotal);
        
        $shopDayTotal = $statisticModel->orderData($jtData,0,$shop_id); //本日门店订单统计
        $this->_view->assign('shopDayTotal',$shopDayTotal);
        
        $cz = $jtTotal - $ztTotal;
        $nowProportion = ($ztTotal > 0) ? sprintf("%.2f", $cz/ $ztTotal)*100 : 0;
        $this->_view->assign('nowProportion', $nowProportion); //当前比率
                
//当前周销售额
        $timeData = new TimedataModel();
        $nowDay = $timeData->this_monday(0, true);

        $nowTotal = $statisticModel->orderTotal($nowDay,0,$shop_id); //本周订单统计
        $this->_view->assign('nowTotal',$nowTotal);

        $shopNowTotal = $statisticModel->orderData($nowDay,0,$shop_id); //本周门店订单统计
        $this->_view->assign('shopNowTotal',$shopNowTotal);

        $lastDay = $timeData->last_monday(0, true);
        $lastTotal = $statisticModel->orderTotal($lastDay, $nowDay); //上一周订单统计
        
        //$nowProportion = ($lastTotal > 0) ? sprintf("%.2f", ($nowTotal - $lastTotal) / $lastTotal) : 0;
        //$this->_view->assign('nowProportion', $nowProportion); //当前比率

//当前月销售额
        $nowWeek = $timeData->month_firstday(0, true);
        $weekTotal = $statisticModel->orderTotal($nowWeek,0,$shop_id); //本月订单统计
        $this->_view->assign('weekTotal', $weekTotal);

        $shopWeekTotal = $statisticModel->orderData($nowWeek, 0, $shop_id); //本月门店订单统计
        $this->_view->assign('shopWeekTotal', $shopWeekTotal);

        $lastWeek = $timeData->lastmonth_firstday(0, true);
        $lastWeekTotal = $statisticModel->orderTotal($lastWeek, $nowWeek, $shop_id); //上个月订单统计

        $weekProportion = ($lastWeekTotal > 0) ? sprintf("%.2f", ($weekTotal - $lastWeekTotal)/ $lastWeekTotal)*100 : 0;
        $this->_view->assign('weekProportion', $weekProportion); //当前周比率

//当前季销售额
        $nowSeason = $timeData->season_firstday();
        $seasonTotal = $statisticModel->orderTotal($nowSeason,0,$shop_id); //当前季节订单统计
        $this->_view->assign('seasonTotal',$seasonTotal);
        
        $shopSeasonTotal = $statisticModel->orderData($nowSeason,0,$shop_id); //本月门店订单统计
        $this->_view->assign('shopSeasonTotal',$shopSeasonTotal);

        $lastSeason = $timeData->season_lastday(true);
        $lastSeasonTotal = $statisticModel->orderTotal($lastSeason['firstday'], $lastSeason['lastday'],$shop_id); //上一个季节订单统计
        
        $seasonProportion = ($lastSeasonTotal > 0) ? sprintf("%.2f", ($seasonTotal - $lastSeasonTotal) / $lastSeasonTotal)*100 : 0;
        $this->_view->assign('seasonProportion', $seasonProportion); //当前季比率
        
//当前年度销售额
        $nowYear = strtotime(date('Y-01-01'));
        $shopYearTotal = $statisticModel->orderData($nowYear, 0, $shop_id); //当前年度销售统计
        $this->_view->assign('shopYearTotal', $shopYearTotal);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject',$shopObject);
        
//当前7天销售额（当前一周）
        $sevenDay = $timeData->sevenDay();
        $data = $statisticModel->orderDayData($sevenDay[6],$shop_id);
        
        $title = $show = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $title[] = $value['timeday'];
                $show[] = floatval($value['total']);
            }
        }
        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));
    }

    /**
     * 当前订单按天统计
     */
    public function orderdayAction(){
        $where = array();
        $start_time = $this->getRequest()->getPost('start_time');
        $end_time = $this->getRequest()->getPost('end_time');
        if (!empty($start_time) && !empty($end_time)) {
            //$where = "ctime>='" . strtotime($start_time) . "' AND ctime <= '" . strtotime($end_time) . "'";   
            $where['ctime'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');
        }
        $shop_id = $this->getRequest()->getPost('shop_id');
        if ($shop_id) {
            //$where .=!empty($where) ? " AND shop_id = " . $shop_id : " shop_id = " . $shop_id;
            $where['shop_id'] = $shop_id;
        }else{
            if($this->shop_id){
                $where['shop_id'] = $this->shop_id;
            }
        }
        
        $type = $this->getRequest()->getQuery('type');
        if ($type == 'm') {
            $file = "count(*) as total,FROM_UNIXTIME(otime,'%Y/%m') as time";
        } else {
            $file = "count(*) as total,FROM_UNIXTIME(otime,'%Y/%m/%d') as time";
        }
        $grouplist = 'time';

        $data = LibF::M('order')->field($file)->where($where)->group($grouplist)->order('time desc')->select();
        $this->_view->assign('data', $data);

        $shopObject = ShopModel::getShopArray();
        $title = $show = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $title[] = $value['time'];
                $show['name'] = '每天订单量';
                $show['data'][] = floatval($value['total']);
            }
        }
        $newShow = array(0 => $show);
        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($newShow));
        
        $this->_view->assign('shop_id',$this->shop_id);
    }

    public function shipperorderAction(){
        
        //统计订单，按照门店统计门店订单数量 | 按照时间点统计
        
        $where = '';
        $start_time = $this->getRequest()->getPost('start_time');
        $end_time = $this->getRequest()->getPost('end_time');
        if (!empty($start_time) && !empty($end_time)) {
            //$where = "ctime>='" . strtotime($start_time) . "' AND ctime <= '" . strtotime($end_time) . "'";
            $where['ctime'] = array(array('egt', strtotime($start_time)), array('elt', strtotime($end_time)), 'and');
        }
        //$shop_id = $this->getRequest()->getPost('shop_id');
        $shipper_id = $this->getRequest()->getPost('shipper_id');
        if ($shipper_id) {
            //$where .=!empty($where) ? " AND shipper_id = " . $shipper_id : " shipper_id = " . $shipper_id;
            $where['shipper_id'] = $shipper_id;
        }
        
        if($this->shop_id){
            $where['shop_id'] = $this->shop_id;
        }
        
        $file = 'count(*) as total,shipper_id,shipper_name';
        $grouplist = 'shipper_id';

        $data = LibF::M('order')->field($file)->where($where)->group($grouplist)->order('shop_id DESC')->select();
        $this->_view->assign('data', $data);

        //$shopObject = ShopModel::getShopArray();
        $shipperModel = new ShipperModel();
        $shipperObject = $shipperModel->getShipperArray();
        
        $title = $show = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $title[] = isset($shipperObject[$value['shipper_id']]) ? $shipperObject[$value['shipper_id']]['shipper_name'] : '未知送气工';
                $show[] = floatval($value['total']);
            }
        }
        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));

        //获取门店
        $this->_view->assign('shipperObject', $shipperObject);
    }
    
    public function fillingAction() {
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'].' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }

        $fillingModel = new FillingModel();
        $data = $fillingModel->getDataList($param,'filling_bottle_log',$where,'id desc');
        $this->_view->assign('count',$data['ext']['count']);
        
        $returnData = $timeKey = array();
        
        $list = '';
        if (!empty($data['ext']['list'])) {
            $btypes = BottleModel::getBottleType();
            foreach ($data['ext']['list'] as $key => &$value) {
                $value['bottle'] = !empty($value['bottle']) ? json_decode($value['bottle'], true) : array();
                $num = count($value['bottle']);
                if (!isset($timeKey[$value['time']]))
                    $timeKey[$value['time']] = 0;
                foreach ($value['bottle'] as $k => $v) {
                    if (!empty($v) && is_string($v)) {
                        $vList = explode('|', $v);
                        if ($k == 0) {
                            $list .= '<tr>';
                            $list .= '<td rowspan="' . $num . '">' . $value['filling_no'] . '</td>';
                            $list .= '<td rowspan="' . $num . '">' . $value['time'] . '</td>';
                            $list .= "<td>" . $btypes[$vList[0]] . "</td><td>" . $vList['1'] . "</td>";
                            $list .= "</tr>";
                        } else {
                            $list .= "<tr><td>" . $btypes[$vList[0]] . "</td><td>" . $vList['1'] . "</td></tr>";
                        }
                        $timeKey[$value['time']] += (int) $vList['1'];
                    }
                }
            }
            krsort($timeKey);
            $key = array_keys($timeKey);
            $data = array_values($timeKey);
        }else{
            $list = "<tr><td colspan='4'>暂时没有数据</td></tr>";
        }
        $this->_view->assign($data);
        $this->_view->assign('list',$list);
        
        $this->_view->assign('key', json_encode($key));
        $this->_view->assign('data', json_encode($data));
    }
    
    public function fillingtypeAction() {
        //充装统计  按照气站瓶子类型统计
        
        $where = '';
        $start_time = $this->getRequest()->getPost('start_time');
        $end_time = $this->getRequest()->getPost('end_time');
        if (!empty($start_time) && !empty($end_time)) {
            //$where = "time>='" . $start_time . "' AND time <= '" . $end_time . "'";
            $where['time'] = array(array('egt', $start_time), array('elt', $end_time), 'and');
        }

        $file = 'sum(num) as total,type,name';
        $grouplist = 'type';

        $data = LibF::M('filling_bottle_info')->field($file)->where($where)->group($grouplist)->select();
        $this->_view->assign('data', $data);
        $returnData = array();
        $title = $show = array();
        if(!empty($data)){
            foreach($data as $value){
                $title[] = $value['name'];
                $show['name'] = '气站充装';
                $show['data'][] = floatval($value['total']);
            }
        }
        $newShow = array(0 => $show);
        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($newShow));
    }
    
    public function warehousingAction(){
        //库存配件统计
        $where = " products_type = 1 ";
        $start_time = $this->getRequest()->getPost('start_time');
        $end_time = $this->getRequest()->getPost('end_time');
        if (!empty($start_time) && !empty($end_time)) {
            $where .= " AND utime>='" . strtotime($start_time) . "' AND utime <= '" . strtotime($end_time) . "'";
        }
        $shop_id = $this->getRequest()->getPost('shop_id');
        if ($shop_id) {
            $where .= " AND shop_id = " . $shop_id;
        } else {
            if ($this->shop_id) {
                $where['shop_id'] = $this->shop_id;
            }
        }

        $file = "sum(num) as total,shop_id";
        $grouplist = "shop_id";

        $data = LibF::M('products_tj')->field($file)->where($where)->group($grouplist)->select();
        $this->_view->assign('data', $data);
        
        $shopObject = ShopModel::getShopArray();

        $returnData = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $val[0] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                $val[1] = floatval($value['total']);
                $returnData[] = $val;
            }
        }
        $this->_view->assign('json_data', json_encode($returnData));

        //获取门店
        $this->_view->assign('shopObject', $shopObject);
    }
    
    public function warehoustypeAction(){
        //库存配件统计
        //$where = " product_type = 1 ";
        $start_time = $this->getRequest()->getPost('start_time');
        $end_time = $this->getRequest()->getPost('end_time');
        /*if (!empty($start_time) && !empty($end_time)) {
            $where .= " AND utime>='" . strtotime($start_time) . "' AND utime <= '" . strtotime($end_time) . "'";
        }
        $shop_id = $this->getRequest()->getPost('shop_id');
        if ($shop_id) {
            $where .= " AND shop_id = " . $shop_id;
        }*/
        $where = "product_id>0";
        $file = "sum(product_num) as total,product_id";
        $grouplist = "product_id";

        $data = LibF::M('warehousing_info')->field($file)->where($where)->group($grouplist)->select();
        $this->_view->assign('data', $data);
        
        $shopObject = ShopModel::getShopArray();
        
        $pjModel = new ProductsModel();
        $pjObject = $pjModel->getProductsArray();

        $title = $show = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $title[] = isset($pjObject[$value['product_id']]) ? $pjObject[$value['product_id']]['products_name'] : '';
                $show[] = floatval($value['total']);
            }
        }
        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));

        //获取门店
        $this->_view->assign('shopObject', $shopObject);
        //配件
        $this->_view->assign('pjObject', $pjObject);
    }
    
    public function kehuAction(){
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        
        $shop_id = $this->getRequest()->getQuery('shop_id',$this->shop_id);
           
//当天销售额度
        $jtData = strtotime(date('Y-m-d'));
        
        $statisticModel = new StatisticsDataModel();
        $jtTotal = $statisticModel->kehuTotal($jtData, 0, $shop_id); //本日销售统计
        $this->_view->assign('jtTotal',$jtTotal);
        
        $ztData = $jtData - (24 * 60 * 60);
        $ztTotal = $statisticModel->kehuTotal($ztData, $jtTotal, $shop_id); //本日销售统计
        $this->_view->assign('ztTotal',$ztTotal);
        
        $shopDayTotal = $statisticModel->kehuData($jtData, 0, $shop_id); //本日门店用户统计
        $this->_view->assign('shopDayTotal', $shopDayTotal);
        
        $cz = $jtTotal - $ztTotal;
        $nowProportion = ($ztTotal > 0) ? sprintf("%.2f", $cz/ $ztTotal) * 100 : 0;
        $this->_view->assign('nowProportion', $nowProportion); //当前比率
        
//当前周销售额
        $timeData = new TimedataModel();
        $nowDay = $timeData->this_monday(0, true);

        $nowTotal = $statisticModel->kehuTotal($nowDay, 0, $shop_id); //本周用户统计
        $this->_view->assign('nowTotal', $nowTotal);

        $shopNowTotal = $statisticModel->kehuData($nowDay, 0, $shop_id); //本周门店用户统计
        $this->_view->assign('shopNowTotal', $shopNowTotal);

        $lastDay = $timeData->last_monday(0, true);
        $lastTotal = $statisticModel->kehuTotal($lastDay, $nowDay, $shop_id); //上一周用户统计

        //$nowProportion = ($lastTotal > 0) ? sprintf("%.2f", ($nowTotal - $lastTotal) / $lastTotal) : 0;
        //$this->_view->assign('nowProportion', $nowProportion); //当前比率
        
//当前月销售额
        $nowWeek = $timeData->month_firstday(0, true);
        $weekTotal = $statisticModel->kehuTotal($nowWeek, 0, $shop_id); //本月用户统计
        $this->_view->assign('weekTotal', $weekTotal);

        $shopWeekTotal = $statisticModel->kehuData($nowWeek, 0, $shop_id); //本月门店用户统计
        $this->_view->assign('shopWeekTotal', $shopWeekTotal);

        $lastWeek = $timeData->lastmonth_firstday(0, FALSE);
        $lastWeekTotal = $statisticModel->kehuTotal($lastWeek, $nowWeek, $shop_id); //上个月用户统计

        $weekProportion = ($lastWeekTotal > 0) ? sprintf("%.2f", ($weekTotal - $lastWeekTotal) / $lastWeekTotal) * 100 : 0;
        $this->_view->assign('weekProportion', $weekProportion); //当前周比率
        
//当前季销售额
        $nowSeason = $timeData->season_firstday();
        $seasonTotal = $statisticModel->kehuTotal($nowSeason, 0, $shop_id); //当前季节用户统计
        $this->_view->assign('seasonTotal', $seasonTotal);

        $shopSeasonTotal = $statisticModel->kehuData($nowSeason, 0, $shop_id); //本月门店用户统计
        $this->_view->assign('shopSeasonTotal', $shopSeasonTotal);

        $lastSeason = $timeData->season_lastday(true);
        $lastSeasonTotal = $statisticModel->kehuTotal($lastSeason['firstday'], $lastSeason['lastday'], $shop_id); //上一个季节用户统计

        $seasonProportion = ($lastSeasonTotal > 0) ? sprintf("%.2f", ($seasonTotal - $lastSeasonTotal) / $lastSeasonTotal) * 100 : 0;
        $this->_view->assign('seasonProportion', $seasonProportion); //当前季比率
   
//当前年度销售额
        $nowYear = strtotime(date('Y-01-01'));
        $shopYearTotal = $statisticModel->kehuData($nowYear, 0, $shop_id); //当前年度销售统计
        $this->_view->assign('shopYearTotal', $shopYearTotal);
        
//客户统计
        $where = '';
        if ($shop_id) {
            $where['shop_id'] = $shop_id;
        }
        $rData = LibF::M('kehu')->field('shop_id,count(*) as total')->where($where)->group('shop_id')->select();
        if (!empty($rData)) {
            $shopObject = ShopModel::getShopArray();
            foreach ($rData as &$value) {
                $value['shop_name'] = (isset($shopObject[$value['shop_id']])) ? $shopObject[$value['shop_id']]['shop_name'] : '未分配门店';
                $title[] = $value['shop_name'];
                $show[] = floatval($value['total']);
            }
        }

        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));
        if ($tempType == 'new') {
            $this->_view->display('statistics/kehu_new.phtml');
        }
    }
    
    public function shipperAction(){
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            }else{
                if(!empty($param['shop_id'])){
                    $where['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }else{
                    $where['shop_id'] = array('gt',1);
                }
            }
        }else{
            if($this->shop_id){
                $where['shop_id'] = $this->shop_id;
            }
        }
        $where['shipper_id'] = array('gt',1);
        $file = 'order_no as total,shipper_id,shipper_name';
        $grouplist = 'shipper_id';
        $data = LibF::M('shipper')->field($file)->where($where)->order('total DESC')->select();

        $categories = $series = '';
        if (!empty($data)) {
            $shipperName = array();
            $shipperNum = array();
            foreach ($data as $value) {
                $shipperName[] = (empty($value['shipper_name'])) ? '送气工' : $value['shipper_name'];
                $shipperNum[] = (int)$value['total'];
            }
            $categories = json_encode($shipperName);
            $series = json_encode($shipperNum);
        }
        $this->_view->assign('categories',$categories);
        $this->_view->assign('series',$series);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject',$shopObject);
        
        $this->_view->assign('page',$param['page']);
        
        if (!empty($param['shipper_name'])) {
            $where['shipper_name'] = $param['shipper_name'];
            $getParam[] = "shipper_name=" . $param['shipper_name'];
        }
        if (!empty($getParam))
            $this->_view->assign('getparamlist', implode('&', $getParam));
        
        $fillModel = new FillingModel();
        $shipperData = $fillModel->getDataList($param, 'shipper', $where, 'shop_id asc,order_no desc');
        $this->_view->assign($shipperData);
    }
}
