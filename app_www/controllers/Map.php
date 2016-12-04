<?php

/**
 *
 */
class MapController extends \Com\Controller\Common {

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
     * 初始化
     */
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
        $this->app = new App();
    }

    public function indexAction() {

        $shop_id = $this->getRequest()->getQuery('shop_id');

        $where = '';
        if (!empty($shop_id))
            $where = " shop_id = " . $shop_id;

        $data = LibF::M('position')->where($where)->select();

        $returnData = array();
        $poingList = '';
        if (!empty($data)) {

            foreach ($data as $key => $value) {
                $mapArrData = json_decode($value['position'], true);
                $mapArr = isset($mapArrData[0]) ? $mapArrData[0] : array();
                $val[0] = '';
                if (!empty($mapArr)) {
                    $val[0] = isset($mapArr['lon']) ? floatval($mapArr['lon']) : '';
                    $val[0] .= ',';
                    $val[0] .= isset($mapArr['lat']) ? floatval($mapArr['lat']) : '';
                }
                $val[1] = $value['shipper_name']."&nbsp;电话：".$value['shipper_mobile'];

                $list = '[' . $val[0] . ', "' . $val[1] . '"]';
                if($key == 0)
                    $poingList = $val[0];

                $returnData[] = $list;
            }
        }
        //$mapList = json_encode($returnData);
        $mapList = !empty($returnData) ? implode(',', $returnData) : '';
        $this->_view->assign('maplist', $mapList);
        $this->_view->assign('poingList', $poingList);
    }
    
    public function shopAction() {
        $where['shop_id'] = array('gt',1);
        $shopData = LibF::M('shop')->where($where)->select();
        $this->_view->assign('shopData', $shopData);
        
        $jsonData = '';
        $returnData = array();
        $poingList = '116.298503,37.902271';

        foreach ($shopData as $key => $value) {
            if (!empty($value['coordinate'])) {
                $val['title'] = $value['shop_name'];
                $val['content'] = "<br>" . $value['address'] . "<br>";
                $val['point'] = $value['coordinate'];

                $list = '[' . $val['point'] . ', "' . $val['title'] . $val['content'] . '"]';

                $returnData[] = $list;
                if($key == 0){
                    $poingList = $val['point'];
                }
            }
        }
        
        $mapList = !empty($returnData) ? implode(',', $returnData) : '';
        $this->_view->assign('maplist', $mapList);
        $this->_view->assign('poingList', $poingList);
    }

    public function kehuAction() {
        $where['status'] = 0;
        $where['coordinate'] = array('neq', '');
        $kehuData = LibF::M('kehu')->where($where)->select();
        $this->_view->assign('kehuData', $kehuData);

        $jsonData = '';
        $returnData = $returnArr = array();

        $shopObject = ShopModel::getShopArray();
        foreach ($kehuData as $key => $value) {
            if (!empty($value['coordinate'])) {
                $zbData = explode(',', $value['coordinate']);
                $returnData[] = $zbData;

                $val['title'] = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $value['user_name']);
                $address = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', trim($value['address']));
                $val['content'] = "<br> 门店: " . $shopObject[$value['shop_id']]['shop_name'];
                $val['address'] = "<br> 地址: " . str_replace(array("\r\n", "\r", "\n"), "", $address) . "<br>";
                $val['point'] = $value['coordinate'];
                $list = '[' . $val['point'] . ', "' . $val['title'] . $val['content'] . $val['address'] . '"]';
                $returnArr[] = $list;
            }
        }

        $mapList = !empty($returnData) ? json_encode($returnData) : '';
        $this->_view->assign('mapList', $mapList);

        //$mapArr = !empty($returnArr) ? json_encode($returnArr) : '';
        $mapArr = !empty($returnArr) ? implode(',', $returnArr) : '';
        $this->_view->assign('mapArr', $mapArr);
    }
    
}