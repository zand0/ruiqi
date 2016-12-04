<?php

/**
 * 气站盘库
 */
class StocktakingController extends \Com\Controller\Common {

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

    public function indexAction() {

        //获取当前燃气库存
        $data = LibF::M('tank_gas')->select();
        $this->_view->assign('data', $data);

        $returnTank = $returnTime = $returnValue = $firstValue = array();
        if (!empty($data)) {
            $tankArr = array();
            foreach ($data as $value) {
                $tankArr[] = $value['tankid'];
            }
            if (!empty($tankArr)) {
                $tanklogData = array();
                $tankWhere['tankid'] = array('in', $tankArr);
                $tankData = LibF::M('tank_log')->where($tankWhere)->order('time_created desc')->select();
                if (!empty($tankData)) {
                    foreach ($tankData as $val) {
                        $tanklogData[$val['tankid']][] = $val;
                    }
                    //获取最近七天的数据
                    if (!empty($tanklogData)) {
                        foreach ($tanklogData as $key => $tVal) {
                            $tArr = array_slice($tVal, 0, 7);
                            $returnTank[$key] = $tArr;
                            foreach ($tArr as $tK => $tV) {
                                if ($tK == 0) {
                                    $firstValue[$key] = $tV;
                                }
                                $returnTime[$key][] = date('m-d', $tV['time_created']);
                                $returnValue[$key][] = floatval($tV['volume']);
                            }
                        }
                    }
                }
            }
        }
        $this->_view->assign('tanklogData', $returnTank);
        $this->_view->assign('returnTime', $returnTime);
        $this->_view->assign('returnValue', $returnValue);
        $this->_view->assign('firstValue', $firstValue);
    }

    public function profitAction() {
        
    }

}
