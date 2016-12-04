<?php

class SalesController extends \Com\Controller\Common {

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
     * 门店
     * @var int
     */
    protected $shop_id;
    
    protected $orderStatus;
    protected $shoptype;
    protected $kehutype;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        if (!empty($adminInfo)) {
            $this->shop_id = $adminInfo['shop_id'];
            $userData['user_id'] = $adminInfo['user_id'];
            $userData['username'] = $adminInfo['username'];
            $userData['photo'] = $adminInfo['photo'];
            $userData['mobile_phone'] = $adminInfo['mobile_phone'];
            $this->user_info = $userData;
        }
        $this->app = new App();
        
        //订单状态
        $this->orderStatus = array(
            -1 => '已关闭', 1 => '未派发', 2 => '配送中', 4 => '已完成', 5 => '问题订单', 6 => '欠款申请订单'
        );

        //门店类型
        $this->shoptype = array(1 => '自营', 2 => '加盟');

        //客户类型
        $this->kehutype = array(1 => '居民', 2 => '商业', 3 => '工业');
    }

//销售额简报
    public function indexAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        $shop_id = $this->getRequest()->getQuery('shop_id',$this->shop_id);

//当天销售额度
        $jtData = strtotime(date('Y-m-d'));
        
        $statisticModel = new StatisticsDataModel();
        $jtTotal = $statisticModel->salesTotal($jtData,0,$shop_id,4); //本日销售统计
        $this->_view->assign('jtTotal',$jtTotal);
        
        $ztData = $jtData - (24 * 60 * 60);
        $ztTotal = $statisticModel->salesTotal($ztData,$jtData,$shop_id,4); //本日销售统计
        $this->_view->assign('ztTotal',$ztTotal);
        
        $shopDayTotal = $statisticModel->salesShopData($jtData,0,$shop_id,4); //本日门店销售统计
        $this->_view->assign('shopDayTotal',$shopDayTotal);
        
        $cz = $jtTotal - $ztTotal;
        $nowProportion = ($ztTotal > 0) ? sprintf("%.2f", $cz/ $ztTotal)*100 : 0;
        $this->_view->assign('nowProportion', $nowProportion); //当前比率

//当前周销售额
        $timeData = new TimedataModel();
        $nowDay = $timeData->this_monday(0, true);

        $nowTotal = $statisticModel->salesTotal($nowDay,0,$shop_id,4); //本周销售统计
        $this->_view->assign('nowTotal',$nowTotal);

        $shopNowTotal = $statisticModel->salesShopData($nowDay,0,$shop_id,4); //本周门店销售统计
        $this->_view->assign('shopNowTotal',$shopNowTotal);

        $lastDay = $timeData->last_monday(0, true);
        $lastTotal = $statisticModel->salesTotal($lastDay, $nowDay,$shop_id,4); //上一周销售统计

//当前月销售额
        $nowWeek = $timeData->month_firstday(0, true);
        $weekTotal = $statisticModel->salesTotal($nowWeek,0,$shop_id,4); //本月销售统计
        $this->_view->assign('weekTotal', $weekTotal);

        $shopWeekTotal = $statisticModel->salesShopData($nowWeek,0,$shop_id,4); //本月门店销售统计
        $this->_view->assign('shopWeekTotal',$shopWeekTotal);

        $lastWeek = $timeData->lastmonth_firstday(0, true);
        $lastWeekTotal = $statisticModel->salesTotal($lastWeek, $nowWeek,$shop_id,4); //上个月销售统计
        
        $cw = $weekTotal - $lastWeekTotal;
        $weekProportion = ($lastWeekTotal > 0) ? sprintf("%.2f",  $cw/ $lastWeekTotal)*100 : 0;
        $this->_view->assign('weekProportion', $weekProportion); //当前周比率

//当前季销售额
        $nowSeason = $timeData->season_firstday();
        $seasonTotal = $statisticModel->salesTotal($nowSeason,0,$shop_id,4); //当前季节销售统计
        $this->_view->assign('seasonTotal',$seasonTotal);
        
        $shopSeasonTotal = $statisticModel->salesShopData($nowSeason,0,$shop_id,4); //当前季节销售统计
        $this->_view->assign('shopSeasonTotal',$shopSeasonTotal);

        $lastSeason = $timeData->season_lastday(true);
        $lastSeasonTotal = $statisticModel->salesTotal($lastSeason['firstday'], $lastSeason['lastday'], $shop_id, 4); //上一个季节销售统计

        $cj = $seasonTotal - $lastSeasonTotal;
        $seasonProportion = ($lastSeasonTotal > 0) ? sprintf("%.2f", $cj / $lastSeasonTotal)*100 : 0;
        $this->_view->assign('seasonProportion', $seasonProportion); //当前季比率
        
//当前年度销售额
        $nowYear = strtotime(date('Y-01-01'));
        $shopYearTotal = $statisticModel->salesShopData($nowYear, 0, $shop_id, 4); //当前年度销售统计
        $this->_view->assign('shopYearTotal', $shopYearTotal);

//当前7天销售额（当前一周）
        $sevenDay = $timeData->sevenDay();
        $sevenTotal = $statisticModel->salesDayData($sevenDay[6],$shop_id,4);

        $title = $show = array();
        if (!empty($sevenTotal)) {
            foreach ($sevenTotal as $value) {
                $title[] = $value['timeday'];
                $show[] = ($value['total'] > 0) ? floatval($value['total']) : 0;
            }
        }

        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));
        
        if ($tempType == 'new') {
            if ($shop_id)
                $where['rq_order.shop_id'] = $shop_id;
            $where['rq_order.status'] = 4;
            $where['rq_order.ispayment'] = 1;
            $orderStaticModel = new Model('order');
            $filed = " rq_order.shop_id,rq_order_info.goods_kind,rq_order_info.goods_price,count(rq_order_info.goods_kind) as total,sum(rq_order.pay_money) as tmoney,sum(rq_order_info.pay_money) as bmoeny ";
            $leftJoin = " LEFT JOIN rq_order_info ON rq_order.order_sn = rq_order_info.order_sn ";

            $listTable = $listHeadTable = $listAllTable = '';
            $where['rq_order_info.goods_type'] = 1;
            $staticsBottleType = $orderStaticModel->join($leftJoin)->field($filed)->where($where)->group('rq_order.shop_id,rq_order_info.goods_kind')->order('tmoney desc')->select();
            if (!empty($staticsBottleType)) {
                $newStaticsData = $bottleStaticsData = array();
                $totalMoney = 0;
                foreach ($staticsBottleType as $sVal) {
                    $newStaticsData[$sVal['shop_id']][$sVal['goods_kind']] = $sVal;
                    $bottleStaticsData[$sVal['goods_kind']] += $sVal['tmoney'];
                    $totalMoney += $sVal['bmoeny'];
                }
                $shopObject = ShopModel::getShopArray();

                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
                if (!empty($newStaticsData)) {

                    $listTable .= "<thead>";
                    if (!empty($bottleTypeData)) {
                        $listTable .= "<tr>";
                        $listTable .= '<th class="borderRight">门店名称</th>';
                        foreach ($bottleTypeData as $bVal) {
                            $listTable .= '<th class="cof_color">' . $bVal['bottle_name'] . '</th>';
                            $bottleTypeMoney = isset($bottleStaticsData[$bVal['id']]) ? $bottleStaticsData[$bVal['id']] : 0;
                            $listHeadTable .= '<li><p>' . $bVal['bottle_name'] . '销售额</p><strong>￥' . $bottleTypeMoney . '</strong></li>';
                        }
                        $listTable .= '<th>总金额</th>';
                        $listTable .= "</tr>";
                    }
                    $listTable .= "</thead>";
                    $listTable .= "<tbody>";
                    foreach ($newStaticsData as $key => $nVal) {
                        $total = 0;
                        $listTable .= "<tr>";
                        $listTable .= '<td class="borderRight">' . $shopObject[$key]['shop_name'] . '</td>';
                        foreach ($bottleTypeData as $bVal) {
                            $bottle_price = isset($nVal[$bVal['id']]) ? '￥' . $nVal[$bVal['id']]["goods_price"] . ' X ' . $nVal[$bVal['id']]['total'] : '0';
                            $listTable .= '<td>' . $bottle_price . '瓶</td>';
                            $total += $nVal[$bVal['id']]['tmoney'];
                        }
                        $listTable .= '<td class="redColor"><strong>￥' . $total . '</strong></td>';
                        $listTable .= "</tr>";
                    }
                    $listTable .= "</tbody>";
                    
                    //汇总头部table
                    $listAllTable = '<div class="bgBtn"><p>全部销售额</p><strong>￥' . $totalMoney . '</strong></div>';
                    $listAllTable .= '<ul class="clearfix">' . $listHeadTable . '</ul>';
                }
            }
            $this->_view->assign('listTable', $listTable);
            $this->_view->assign('listAllTable',$listAllTable);
            
            //获取一段时间内的规格类型统计数据
            $orderBottleModel = new Model('order');
            $filed = " FROM_UNIXTIME(rq_order.ctime,'%Y-%m-%d') as time,rq_order_info.goods_kind,count(rq_order_info.goods_kind) as total ";
            $leftJoin = " LEFT JOIN rq_order_info ON rq_order.order_sn = rq_order_info.order_sn ";
            $orderBottleData = $orderBottleModel->join($leftJoin)->field($filed)->where($where)->group('time,rq_order_info.goods_kind')->order('time desc')->select();

            $titleShow = $dataShow = '';
            if (!empty($orderBottleData)) {
                $dataAll = $titleArr = $contentArr = array();
                foreach ($orderBottleData as $oVal) {
                    $titleArr[] = $oVal['time'];
                    $dataAll[$oVal['goods_kind']][$oVal['time']] = (int) $oVal['total'];
                }
                $timeUnique = array_unique($titleArr);
                $titleShow = json_encode($timeUnique);
                if (!empty($dataAll)) {
                    foreach ($dataAll as $dKey => $dVal) {
                        $val['name'] = $bottleTypeData[$dKey]['bottle_name'] . '销售额';
                        $val['data'] = array_values($dVal);
                        $contentArr[] = $val;
                    }
                    $dataShow = json_encode($contentArr);
                }
            }
            $this->_view->assign('titleShow', $titleShow);
            $this->_view->assign('dataShow', $dataShow);

            $this->_view->display('sales/index_new.phtml');
        }
    }
    
//新开户简报
    public function kehuAction() {
        $shop_id = $this->getRequest()->getQuery('shop_id',$this->shop_id);

//当天销售额度
        $jtData = strtotime(date('Y-m-d'));

        $statisticModel = new StatisticsDataModel();
        $jtTotal = $statisticModel->kehuTotal($jtData, 0, $shop_id); //今天
        $this->_view->assign('jtTotal', $jtTotal);

        $ztData = $jtData - (24 * 60 * 60);
        $ztTotal = $statisticModel->kehuTotal($ztData, $jtData, $shop_id); //本周
        $this->_view->assign('ztTotal', $ztTotal);

        $shopDayTotal = $statisticModel->kehuData($jtData, 0, $shop_id); //本周门店新增用户统计
        $this->_view->assign('shopDayTotal', $shopDayTotal);
        
        $cz = $jtTotal - $ztTotal;

        $nowProportion = ($ztTotal > 0) ? sprintf("%.2f", $cz / $ztTotal) * 100 : 0;
        $this->_view->assign('nowProportion', $nowProportion); //当前比率
//当前周销售额
        $timeData = new TimedataModel();
        $nowDay = $timeData->this_monday(0, true);

        $nowTotal = $statisticModel->kehuTotal($nowDay, 0, $shop_id); //本周新增用户统计
        $this->_view->assign('nowTotal', $nowTotal);

        $shopNowTotal = $statisticModel->kehuData($nowDay, 0, $shop_id); //本周门店新增用户统计
        $this->_view->assign('shopNowTotal', $shopNowTotal);

        $lastDay = $timeData->last_monday(0, true);
        $lastTotal = $statisticModel->kehuTotal($lastDay, $nowDay, $shop_id); //上一周新增用户统计
        //$nowProportion = ($lastTotal > 0) ? sprintf("%.2f", ($nowTotal - $lastTotal)/ $lastTotal) : 0;
        //$this->_view->assign('nowProportion', $nowProportion); //当前比率
//当前月销售额
        $nowWeek = $timeData->month_firstday(0, true);
        $weekTotal = $statisticModel->kehuTotal($nowWeek, 0, $shop_id); //本月新增用户统计
        $this->_view->assign('weekTotal', $weekTotal);

        $shopWeekTotal = $statisticModel->kehuData($nowWeek, 0, $shop_id); //本月门店新增用户统计
        $this->_view->assign('shopWeekTotal', $shopWeekTotal);

        $lastWeek = $timeData->lastmonth_firstday(0, true);
        $lastWeekTotal = $statisticModel->kehuTotal($lastWeek, $nowWeek, $shop_id); //上个月新增用户统计

        $weekProportion = ($lastWeekTotal > 0) ? sprintf("%.2f", ($weekTotal - $lastWeekTotal) / $lastWeekTotal) * 100 : 0;
        $this->_view->assign('weekProportion', $weekProportion); //当前周比率
//当前季销售额
        $nowSeason = $timeData->season_firstday();
        $seasonTotal = $statisticModel->kehuTotal($nowSeason, 0, $shop_id); //当前季节新增用户统计
        $this->_view->assign('seasonTotal', $seasonTotal);

        $shopSeasonTotal = $statisticModel->kehuData($nowSeason, 0, $shop_id); //本月门店新增用户统计
        $this->_view->assign('shopSeasonTotal', $shopSeasonTotal);

        $lastSeason = $timeData->season_lastday(true);
        $lastSeasonTotal = $statisticModel->kehuTotal($lastSeason['firstday'], $lastSeason['lastday'], $shop_id); //上一个季节新增用户统计

        $seasonProportion = ($lastSeasonTotal > 0) ? sprintf("%.2f", ($seasonTotal - $lastSeasonTotal) / $lastSeasonTotal) * 100 : 0;
        $this->_view->assign('seasonProportion', $seasonProportion); //当前季比率
        
//当前年度销售额
        $nowYear = strtotime(date('Y-01-01'));
        $shopYearTotal = $statisticModel->kehuData($nowYear, 0, $shop_id); //当前年度销售统计
        $this->_view->assign('shopYearTotal', $shopYearTotal);

//当前7天新增用户（当前一周）
        $sevenDay = $timeData->sevenDay(true);
        $sevenTotal = $statisticModel->kehuDayData($sevenDay[6]);
        $title = $show = array();
        if (!empty($sevenTotal)) {
            foreach ($sevenTotal as $value) {
                $title[] = $value['timeday'];
                $show[] = floatval($value['total']);
            }
        }
        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));
    }

//回流率简报(计算钢瓶一段时间内充装次数)
    public function quarterAction() {
        $shop_id = $this->getRequest()->getQuery('shop_id',$this->shop_id);
        
        //门店统计
        $shopObject = ShopModel::getShopArray();

        $statisticModel = new StatisticsDataModel();
        $rData = $statisticModel->getStoreInventory();
        if (!empty($rData)) {
            foreach ($rData as &$value) {
                $value['shop_name'] = (isset($shopObject[$value['shop_id']])) ? $shopObject[$value['shop_id']]['shop_name'] : '未分配门店';
                $title[] = $value['shop_name'];
                $show[] = floatval($value['total']);
            }
        }

        $this->_view->assign('title', json_encode($title));
        $this->_view->assign('show', json_encode($show));

        $this->_view->assign('rData', $rData);
        $this->_view->assign('shopObject', $shopObject);
    }
    
//月销售额统计
    public function gatherAction() {
        
        $kehuObject = array(1 => '居民用户', 2 => '商业用户');

        $shop_id = $this->getRequest()->getQuery('shop_id', $this->shop_id);
        $where['status'] = 4;
        if ($shop_id) {
            $where['shop_id'] = $shop_id;
        }

        $jmTotal = $syTotal = 0;
        $returnData = array();
        $filed = "FROM_UNIXTIME(ctime, '%Y-%m') as nowday,kehu_type,sum(pay_money) as total";
        $data = LibF::M('order')->field($filed)->where($where)->group('nowday,kehu_type')->order('nowday desc')->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $returnData[$value['nowday']][$value['kehu_type']] = $value['total'];
                if ($value['kehu_type'] == 1) {
                    $jmTotal += $value['total'];
                } else if ($value['kehu_type'] == 2) {
                    $syTotal += $value['total'];
                }
            }
        }
        $this->_view->assign('returnData', $returnData);
        $this->_view->assign('jmTotal', $jmTotal);
        $this->_view->assign('syTotal', $syTotal);
    }

//月销售额统计详情
    public function gatherinfoAction() {
        
        $date_time = $this->getRequest()->getQuery('datetime');
        if (!empty($date_time)) {
            $date_time .= "-01";

            $timeData = new TimedataModel();
            $date_end_time = $timeData->month_lastday(strtotime($date_time), false);

            $where['status'] = 4;
            $where['ctime'] = array(array('egt', strtotime($date_time)), array('elt', strtotime($date_end_time . ' 23:59:59')), 'AND');
            $filed = "shop_id,shop_name,kehu_type,sum(pay_money) as total";
            $data = LibF::M('order')->field($filed)->where($where)->group('shop_id,kehu_type')->order('shop_id asc')->select();

            $returnData = array();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $returnData[$value['shop_id']][$value['kehu_type']] = $value['total'];
                }
            }
            $this->_view->assign('returnData', $returnData);
            
            $shopObject = ShopModel::getShopArray();
            $this->_view->assign('shop_object',$shopObject);
        }
    }

//气站订单按照门店分类统计
    public function shoporderAction() {
        $shop_id = $this->getRequest()->getPost('shop_id', $this->shop_id);
        $search_type = $this->getRequest()->getPost('search_type', 1);
        $view_type = $this->getRequest()->getPost('view_type');  //是否是json传值

        $param = $this->getTime($search_type);
        $start_time = $this->getRequest()->getPost('start_time');
        $end_time = $this->getRequest()->getPost('end_time');
        $this->_view->assign('start_time', $start_time);
        $this->_view->assign('end_time', $end_time);

        $param['start_time'] = !empty($start_time) ? $start_time : $param['start_time'];
        $param['end_time'] = !empty($end_time) ? $end_time : $param['end_time'];
$shop_id = 2;
        if ($view_type == 'json') {
            $orderData = $this->getSalesOrder($param, $shop_id);
            $orderData['orderStatus'] = $this->orderStatus;
            $orderData['kehuType'] = $this->kehutype;
            echo json_encode($orderData, true);
            exit;
        } else {
            $param = array();
            $orderData = $this->getSalesOrder($param, $shop_id);
        }
        
        //备注当前门店登录只显示门店信息
        $this->_view->assign('orderData', $orderData);
        if ($shop_id) {
            $this->_view->assign('orderStatus', $this->orderStatus);
            $this->_view->assign('kehuType', $this->kehutype);
            $this->_view->display('sales/ordertype.phtml');
        } else {
            $this->_view->assign('orderStatus', $this->orderStatus);
            $this->_view->assign('kehuType', $this->kehutype);
        }
    }

//气站销售额按照门店分类统计
    public function shopsalesAction() {
        
        //备注当前门店登录只显示门店信息
        $this->_view->display('sales/salestype.phtml');
    }
    
//气站订单分类统计
    public function orderAction() {
        
    }
    
    //获取相关时间点
    protected function getTime($search_type) {
        $param = array();
        
        $date = date('Y-m-d');
        $timeData = new TimedataModel();
        switch ($search_type) {
            case '2'://昨天
                $ztdate = date("Y-m-d", strtotime("-1 day"));
                $param['start_time'] = $ztdate;
                $param['end_time'] = $ztdate . ' 23:59:59';

                break;
            case '3'://本周
                $bzdate = $timeData->this_monday(0, false);

                $param['start_time'] = $bzdate;
                $param['end_time'] = $date . ' 23:59:59';
                break;
            case '4'://上周
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
            case 1: //今天
            default :
                $param['start_time'] = $this->getRequest()->getPost('time_start', $date);
                $param['end_time'] = $this->getRequest()->getPost('time_end', $date);
                $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] . ' 23:59:59' : '';
                break;
        }

        return $param;
    }

    //获取门店订单统计数据
    protected function getSalesOrder($param, $shop_id) {
        $orderData = array('total' => 0, 'order_total' => array(), 'order_type' => array(), 'order_shop' => array());

        $start_time = !empty($param['start_time']) ? strtotime($param['start_time']) : 0;
        $end_time = !empty($param['end_time']) ? strtotime($param['end_time']) : 0;

        $statisticModel = new StatisticsDataModel();
        $data = $statisticModel->orderTypeTotal($start_time, $end_time, $shop_id, array());
        if ($shop_id) {
            $orderData = $this->shopOrderData($data);
        } else {
            $orderData = $this->newShopOrderData($data);
        }
        return $orderData;
    }
    
    //按照气站门店分类汇总订单数据
    protected function newShopOrderData($data = array()) {
        $orderData = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $orderData['total'] += $value['total'];

                if (isset($orderData['order_total'][$value['shop_type']])) {
                    $orderData['order_total'][$value['shop_type']] += $value['total'];
                } else {
                    $orderData['order_total'][$value['shop_type']] = $value['total'];
                }

                if (isset($orderData['order_type'][$value['status']])) {
                    $orderData['order_type'][$value['status']]['total'] += $value['total'];
                    if (isset($orderData['order_type'][$value['status']]['list'][$value['ktype']])) {
                        $orderData['order_type'][$value['status']]['list'][$value['ktype']] += $value['total'];
                    } else {
                        $orderData['order_type'][$value['status']]['list'][$value['ktype']] = $value['total'];
                    }
                } else {
                    $orderData['order_type'][$value['status']]['total'] = $value['total'];
                    $orderData['order_type'][$value['status']]['list'][$value['ktype']] = $value['total'];
                }

                if (isset($orderData['order_shop'][$value['shop_id']])) {
                    $orderData['order_shop'][$value['shop_id']]['total'] += $value['total'];
                    if (isset($orderData['order_shop'][$value['shop_id']]['list'][$value['ktype']])) {
                        if ($orderData['order_shop'][$value['shop_id']]['list'][$value['ktype']][$value['status']]) {
                            $orderData['order_shop'][$value['shop_id']]['list'][$value['ktype']][$value['status']] += $value['total'];
                        } else {
                            $orderData['order_shop'][$value['shop_id']]['list'][$value['ktype']][$value['status']] = $value['total'];
                        }
                    } else {
                        $orderData['order_shop'][$value['shop_id']]['list'][$value['ktype']][$value['status']] = $value['total'];
                    }
                } else {
                    $val['shop_name'] = $value['shop_name'];
                    $val['shop_type'] = $value['shop_type'];
                    $orderData['order_shop'][$value['shop_id']] = $val;
                    $orderData['order_shop'][$value['shop_id']]['total'] = $value['total'];
                    $orderData['order_shop'][$value['shop_id']]['list'][$value['ktype']][$value['status']] = $value['total'];
                }
            }
        }
        return $orderData;
    }
    
    //按照门店分类汇总订单数据
    protected function shopOrderData($data = array()) {
        $orderData = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $orderData['total'] += $value['total'];

                if (isset($orderData['order_total'][$value['ktype']])) {
                    $orderData['order_total'][$value['ktype']] += $value['total'];
                } else {
                    $orderData['order_total'][$value['ktype']] = $value['total'];
                }

                if (isset($orderData['order_type'][$value['status']])) {
                    $orderData['order_type'][$value['status']] += $value['total'];
                } else {
                    $orderData['order_type'][$value['status']] = $value['total'];
                }
            }
        }
        return $orderData;
    }

}