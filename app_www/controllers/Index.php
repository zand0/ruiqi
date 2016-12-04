<?php

/**
 * @name IndexController
 * @author zxg
 * @desc   默认控制器
 * @see    http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends \Com\Controller\Common {

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/sample/index/index/index/name/chenzhidong 的时候, 你就会发现不同
     */
    public function _indexAction() {        
        $s = LibF::D('AdminUser');
        $s->checkLoginCaptcha();
        $sql = "select id from user where email='123@123.com'";
        $result = LibF::M('user')->select();
        $this->_view->assign('userList', $result);
        
        //$image = new \Think\Verify2();
        //$image->buildImageVerify(5,0,'png',95,41);exit;
        
        return false;
        if ($result) {
            var_dump($result);
        } 
        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板 
    }

    public function indexAction() {
        //var_dump(123);exit;
        //获取所有订单统计
        $orderData = LibF::M('order')->field('count(*) as total')->find();
        $orderTotal = !empty($orderData) ? $orderData['total'] : 0;
        $this->_view->assign('orderTotal', $orderTotal);
        $shipperData = LibF::M('shipper')->field('count(*) as total')->find();
        $shipperTotal = !empty($shipperData) ? $shipperData['total'] : 0;
        $this->_view->assign('shipperTotal', $shipperTotal);
        $bottleData = LibF::M('bottle')->field('count(*) as total')->find();
        $bottleTotal = !empty($bottleData) ? $bottleData['total'] : 0;
        $this->_view->assign('bottleTotal', $bottleTotal);
        
        $sWhere['shop_id'] = array('gt',1);
        $shopData = LibF::M('shop')->field('count(*) as total')->where($sWhere)->find();
        $shopTotal = !empty($shopData) ? $shopData['total'] : 0;
        $this->_view->assign('shopTotal', $shopTotal);
        $adminData = LibF::M('admin_user')->field('count(*) as total')->find();
        $userTotal = !empty($adminData['total']) ? $adminData['total'] : 0;
        $this->_view->assign('userTotal', $userTotal);
        
        //获取门店钢瓶
        $sWhere['type'] = 1;
        $shopBottle = LibF::M('filling_stock_shop')->field('shop_id,fs_type_id,fs_name,fs_num')->where($sWhere)->select();
        $this->_view->assign('shopBottle', $shopBottle);
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);//echo 456;exit;
    }
    
    public function userloginAction(){
        $data = $_SESSION['kehu_info'];
        if (!empty($data)) {
            $this->success('已经登录', '/index/index');
        }
    }
   
    public function homeAction() {
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        if ($tempType == 'new') {
            $this->_view->display('index/home_new.phtml');
        }
        
        //获取总的燃气库存
        $tankTotal = 0;
        $tank_gasData = LibF::M('tank_gas')->field('tank_name,total,volume')->select();
        $this->_view->assign('tank_gasData', $tank_gasData);
        $tankKey = $tankVal = array();
        if (!empty($tank_gasData)) {
            foreach ($tank_gasData as $value) {
                $tankKey[] = $value['tank_name'];
                $tNum = (float) $value['volume'];
                $tankVal[] = $tNum;
                $tankTotal += $tNum;
            }
        }
        $this->_view->assign('tankKey', json_encode($tankKey));
        $this->_view->assign('tankVal', json_encode($tankVal));
        $this->_view->assign('tank_gasTotal', $tankTotal);

        //获取钢瓶配件总量
        $dataTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,type')->group('type')->select();
        $bottleTotal = $productTotal = 0;
        if(!empty($dataTotal)){
            foreach($dataTotal as $value){
                if($value['type'] == 1){
                    $bottleTotal = $value['total'];
                }else{
                    $productTotal = $value['total'];
                }
            }
        }
        $this->_view->assign('bottleTotal',$bottleTotal);
        $this->_view->assign('productTotal',$productTotal);
        
        $dataTypeTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,fs_type_id,fs_name,type')->group('fs_type_id,fs_name,type')->select();
        $bottleTypeData = $productTypeData = array();
        if(!empty($dataTypeTotal)){
            foreach($dataTypeTotal as $value){
                if($value['type'] == 1){
                    $bottleTypeData[] = $value;
                }else{
                    $productTypeData[] = $value;
                }
            }
        }
        //钢瓶统计
        $bottleKey = $bottleVal = array();
        if(!empty($bottleTypeData)){
            foreach($bottleTypeData as $value){
               $bottleKey[] = $value['fs_name'];
               $bottleVal[] = (int)$value['total'];
            }
        }
        $this->_view->assign('bottleKey',  json_encode($bottleKey));
        $this->_view->assign('bottleVal',  json_encode($bottleVal));
        //配件统计
        $productKey = $productVal = array();
        if(!empty($productTypeData)){
            foreach($productTypeData as $value){
               $productKey[] = $value['fs_name'];
               $productVal[] = (int)$value['total'];
            }
        }
        $this->_view->assign('productKey',  json_encode($productKey));
        $this->_view->assign('productVal',  json_encode($productVal));
        
        //当前月销售额
        $timeData = new TimedataModel();
        $nowWeek = $timeData->month_firstday(0, true);
        $statisticModel = new StatisticsDataModel();
        $monthTotal = $statisticModel->salesTotal($nowWeek,0,0,4); //本月销售统计
        $this->_view->assign('monthTotal', $monthTotal);

        $shopObject = ShopModel::getShopArray();
        $shopWeekTotal = $statisticModel->salesShopData($nowWeek,0,0,4); //本月门店销售统计
        $salesArr = array();
        if(!empty($shopWeekTotal)){
            foreach($shopWeekTotal as $V){
                $val[0] = isset($shopObject[$V['shop_id']]) ? $shopObject[$V['shop_id']]['shop_name'] : '未分配门店';
                $val[1] = (int)$V['total'];
                $salesArr[] = $val;
            }
        }
        $this->_view->assign('salesArr',  json_encode($salesArr));
        
        //当月支出金额(燃气采购支出、钢瓶采购支出、配件采购支出、其它采购支出、退瓶支出)
        $typeObject = array(1 => '燃气', 2 => '钢瓶', 3 => '配件');
        $zcMoney = 0;
        $zcMoneyArr = array();
        $cgData = LibF::M('procurement')->field("sum(money) as total,type")->group('type')->select();
        if ($cgData) {
            foreach ($cgData as $cVal) {
                $v[0] = $typeObject[$cVal['type']];
                $v[1] = (int)$cVal['total'];
                $zcMoneyArr[] = $v;
                $zcMoney += $v[1];
            }
        }
        //其它支出
        $ocgData = LibF::M('other_procurement')->field("sum(zc_price) as total")->find();
        $oV[0] = '其它';
        $oV[1] = (int) $ocgData['total'];
        $zcMoneyArr[] = $oV;
        $zcMoney += $oV[1];
        
        //退瓶支出
        $tpData = LibF::M('deposit_list')->field("sum(money) as total")->find();
        $tVal[0] = '退瓶';
        $tVal[1] = (int) $tpData['total'];
        $zcMoneyArr[] = $tVal;
        $zcMoney += $tVal[1];
        
        $this->_view->assign('zcMoneyArr', json_encode($zcMoneyArr));
        $this->_view->assign('zcMoney', $zcMoney);
        
        //新开户统计
        $khwhere['ctime'] = array('egt', $nowWeek);
        $kehuData = LibF::M('kehu')->field('count(*) as total,shop_id')->where($khwhere)->group('shop_id')->select();
        $kehuTotal = 0;
        $kehuArr = array();
        if(!empty($kehuData)){
            foreach ($kehuData as $kVal){
                $kV[0] = isset($shopObject[$kVal['shop_id']]) ? $shopObject[$kVal['shop_id']]['shop_name'] : '未分配门店';
                $kV[1] = (int)$kVal['total'];
                $kehuArr[] = $kV;
                $kehuTotal += $kV[1];
            }
        }
        $this->_view->assign('kehuArr',  json_encode($kehuArr));
        $this->_view->assign('kehuTotal',$kehuTotal);
        
        //应收款
        $weekTotal = 0;
        $whereparam['start_time'] = $nowWeek;
        $whereparam['end_time'] = 0;
        $reportData = $statisticModel->reportShopMoney(0,$whereparam);
        if (!empty($reportData)) {
            foreach ($reportData as $rVal){
                $weekTotal += $rVal['money'];
                
                $sV[0] = isset($shopObject[$rVal['shop_id']]) ? $shopObject[$rVal['shop_id']]['shop_name'] : '未分配门店';
                $sV[1] = (int) $rVal['money'];
                $shopArr[] = $sV;
            }
        }
        $this->_view->assign('weekTotal', $weekTotal);
        $this->_view->assign('shopArr', json_encode($shopArr));
    }
    
    public function loginAction(){
        
        $data = $_SESSION['userinfo'];
        if (!empty($data)) {
            $this->success('已经登录', '/index/home');
        }

        if (IS_POST) {
            $username = $this->getRequest()->getPost('username');
            $password = $this->getRequest()->getPost('password');
            $captcha = $this->getRequest()->getPost('captcha');
            $adminUserD = LibF::D('AdminUser');
            $res = $adminUserD->login($username,$password,$captcha);
            if($res['status'] == '200') {
                $user = $res['ext'];
                Tools::session('user_id', $user['user_id']);
                Tools::session('real_name', $user['real_name']);
                $user_role = LibF::M('auth_role')->where('id in ('.$user['roles'].')')->select();
                   
                $roles = array();
                if($user_role)
                foreach ($user_role as $r){
                    $roles[]=$r['rules'];
                }
                $roles = array_unique(explode(',',implode(',', $roles)));
                
                $ruleArr = array();
                if(!empty($roles)) {
                    $rules = LibF::M('auth_rule')->where(array('id'=>array('in', $roles)))->select();
                    if(!empty($rules))
                    foreach ($rules as $v) {
                        $ruleArr[$v['id']] = $v['name'];
                    }
                }
                
                Tools::session('roles',$ruleArr);
                Tools::cookie('nickname',$user['nickname']);
                Tools::session('userinfo', $user);
                
                $sysData['logcontent'] = '登陆';
                $sysData['logtime'] = time();
                $sysData['loguserid'] = $user['user_id'];
                $sysData['logusername'] = $user['username'];
                $sysData['username'] = $user['real_name'];
                $sysData['logtype'] = 'web';
                $sysData['logstatus'] = 0;
                LibF::M('syslog')->add($sysData);
                
                $this->success('欢迎进入','/index/home');
            } else {
                $this->error($res['msg'],'/index/login');
            }
        }
    }
    
    public function logoutAction() {

        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        $sysData['logcontent'] = '退出';
        $sysData['logtime'] = time();
        $sysData['loguserid'] = $adminInfo['user_id'];
        $sysData['logusername'] = $adminInfo['username'];
        $sysData['logtype'] = 'web';
        $sysData['logstatus'] = 1;
        LibF::M('syslog')->add($sysData);

        Tools::session(NULL);
        Tools::cookie(NULL);
        $url = 'http://' . $_SERVER['HTTP_HOST'];

        $this->success('欢迎下次再来', $url);
    }

    public function safeCodeImgAction(){
        $adminUserD = LibF::D('AdminUser');
        $adminUserD->showLoginCaptcha();
        return false; 
    }
    
    public function deployAction() {
        $where['shop_id'] = array('gt',1);
        $shopData = LibF::M('shop')->where($where)->select();
        $this->_view->assign('shopData', $shopData);
        
        $jsonData = '';
        $returnData = array();
        $poingList = '116.298503,37.902271';
        if (!empty($shopData)) {
            foreach ($shopData as $key => $value) {
                if (!empty($value['coordinate'])) {
                    $val['title'] = $value['shop_name'];
                    $val['content'] = "<br>" . $value['address'] . "<br>";
                    $val['point'] = $value['coordinate'];

                    $list = '[' . $val['point'] . ', "' . $val['title'] . $val['content'] . '"]';

                    $returnData[] = $list;
                    if ($key == 0) {
                        $poingList = $val['point'];
                    }
                }
            }
        }

        $mapList = !empty($returnData) ? implode(',', $returnData) : '';
        $this->_view->assign('maplist', $mapList);
        $this->_view->assign('poingList', $poingList);
    }

}
