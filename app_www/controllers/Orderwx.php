<?php 
/**
 * 微信订单
 */

class OrderwxController extends \Com\Controller\Common{
    
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
        
        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            
            if (!empty($param['mobile'])){
                $where['mobile'] = $param['mobile'];
                $getParam[] = "mobile=" . $param['mobile'];
            }
            if (!empty($param['operator'])){
                $where['operator'] = $param['operator'];
                $getParam[] = "operator=" . $param['operator'];
            }
            if (!empty($param['status'])){
                $where['status'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $end_time = date('Y-m-d', strtotime ("+1 day", strtotime($param['time_end'])));
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($end_time)), 'AND');
                
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }    
            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        $where['status'] = array('neq',2);
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $orderModel = new FillingModel();
        $data = $orderModel->getDataList($param,'order_snap',$where,'time_created desc');
        if(!empty($data['ext']['list'])){
            //获取当前钢瓶规格
            $bottleTypeModel = new BottletypeModel();
            $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
            foreach ($data['ext']['list'] as &$value) {
                $bottleData = $value['bottle_list'];
                $value['bottle_text'] = '';
                if (!empty($bottleData)) {
                    $blist = explode('-', $bottleData);
                    if (!empty($blist)) {
                        $dataText = '';
                        foreach ($blist as $val) {
                            if (!empty($val)) {
                                $bArr = explode('|', $val);
                                if (isset($bArr[1]) && !empty($bArr[1])) {
                                    $dataText .= $bottleTypeData[$bArr[1]]['bottle_name'];
                                    $dataText .= isset($bArr[3]) ? ' ' .'*'.$bArr[3] . '个; <br />' : '';
                                }
                            }
                        }
                        $value['bottle_text'] = $dataText;
                    }
                }
            }
        }
        $this->_view->assign($data);
        
        $twhere['time_created'] = array('egt', strtotime(date('Y-m-d')));
        $nowTj = LibF::M('order_snap')->where($twhere)->count();
        $this->_view->assign('nowTj', $nowTj);
        
        //订单状态
        $orderStatus = array(0 => '未处理', 1 => '已处理');
        $this->_view->assign('orderStatus', $orderStatus);

        //订单来源
        $ordersource = array(
            1001 => 1001, 1002 => 1002, 1003 => 1003, 1004 => 1004, 1005 => 1005, 1006 => 1006, 1007 => 1007, 1008 => 1008
        );
        $this->_view->assign('ordersource',$ordersource);
        
        if ($tempType == 'new') {
            $this->_view->display('orderwx/index_new.phtml');
        }
    }
    
    public function delorderAction() {
        $order_id = $this->getRequest()->getPost('order_id');

        $status = 0;
        if ($order_id) {
            $where['id'] = $order_id;
            $status = LibF::M('order_snap')->where($where)->save(array('status' => 2));
        }
        echo $status;
        exit;
    }
    
    public function balanceAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();

            if (!empty($param['shop_id'])) {
                $where['rq_kehu.shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }else {
                if($this->shop_id){
                    $where['rq_kehu.shop_id'] = $this->shop_id;
                    $getParam[] = "shop_id=" . $this->shop_id;
                    
                    $shop_id = $this->shop_id;
                }
            }

            if (!empty($param['kid'])) {
                $where['rq_balance_list.kid'] = $param['kid'];
                $getParam[] = "kid=" . $param['kid'];
            }
            if (!empty($param['balance_sn'])) {
                $where['rq_balance_list.balance_sn'] = $param['balance_sn'];
                $getParam[] = "balance_sn=" . $param['balance_sn'];
            }
            if (!empty($param['shipper_id'])) {
                $where['rq_balance_list.shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }
            if (!empty($param['paytype'])) {
                $where['rq_balance_list.paytype'] = $param['paytype'];
                $getParam[] = "paytype=" . $param['paytype'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['rq_balance_list.time'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        } else {
            if ($this->shop_id) {
                $where['rq_kehu.shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
                
                $shop_id = $this->shop_id;
            }
        }
        $where['rq_balance_list.type'] = 1;

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $fillModel = new FillingModel();
        $field = "rq_balance_list.*,rq_kehu.kehu_sn,rq_kehu.user_name,rq_kehu.shop_id";
        $data = $fillModel->getDataTableList($param, 'balance_list', 'rq_balance_list', 'rq_kehu', 'kid', $where, 'rq_balance_list.time desc', $field);
        $this->_view->assign($data);
        
        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $paytypeObject = array(1 => '现金', 2 => '支付宝', 3 => '微信');
        $this->_view->assign('paytypeObject', $paytypeObject);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $shipperModel = new ShipperModel();
        $shipperObject = $shipperModel->getShipperArray('', $shop_id);
        $this->_view->assign('shipperObject', $shipperObject);
        
        $twhere['type'] = 1;
        $allTj = LibF::M('balance_list')->field("count(*) as number,sum(money) as total")->where($twhere)->find();
        $this->_view->assign('allTj', $allTj);

        $twhere['time'] = array('egt', strtotime(date('Y-m-d')));
        $nowTj = LibF::M('balance_list')->field("count(*) as number,sum(money) as total")->where($twhere)->find();
        $this->_view->assign('nowTj', $nowTj);
    }

}
