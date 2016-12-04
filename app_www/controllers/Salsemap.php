<?php 
/**
 * 销售统计
 */
class SalsemapController extends \Com\Controller\Common{
    
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
    
    //气瓶销售柱状图
    public function storebottleAction() {
        session_start();
        $where['goods_type'] = $this->getRequest()->getQuery('goods_type', 1);
        //$where['shop_level'] = isset($_SESSION['userinfo']['shop_level'])?$_SESSION['userinfo']['shop_level']:0;
        //$where['shop_id'] = isset($_SESSION['userinfo']['shop_id'])?$_SESSION['userinfo']['shop_id']:0;
        if (empty($where['shop_level'])) {
            unset($where['shop_level']);
        }
        if (empty($where['shop_id'])) {
            unset($where['shop_id']);
        }
        if(!isset($where['shop_id']) && $this->shop_id){
            $where['shop_id'] = $this->shop_id;
        }
        $model = LibF::M('order_info');
        $shop_tj = $model->field('shop_level,shop_id,sum(goods_num) as num,goods_name,goods_id')->group('shop_id,goods_type,goods_id')->where($where)->order('num desc')->select();

        $shop_list = ShopModel::getShopArray();

        $goods_name = array();
        $data = array('type' => '', 'shops' => '', 'shop_list' => $shop_list, 'shop_tj' => $shop_tj);
        if (!empty($shop_tj)) {
            $shop = $type = array();
            foreach ($shop_tj as $v) {
                $shop[$v['shop_id']] = $shop_list[$v['shop_id']]['shop_name'];
                $type[$v['goods_id']][$v['shop_id']] = isset($v['num']) ? $v['num'] : 0;
                $goods_name[$v['goods_id']] = $v['goods_name'];
            }

            $data['shops'] = '"' . implode('","', $shop) . '"';
            if (!empty($type)) {
                foreach ($type as $k => $v) {
                    $data['type'] .= '{name:"' . $goods_name[$k] . '",data:[' . implode(',', $v) . ']},';
                }
                $data['type'] = rtrim($data['type'], ',');
            }
        }
        $this->_view->assign($data);
    }

    //销售折线图
    public function storeproductAction(){
        $where['goods_type'] = $this->getRequest()->getQuery('goods_type', 1);
        $where['shop_level'] = $this->getRequest()->getQuery('shop_level', 0);
        $where['shop_id'] = $this->getRequest()->getQuery('shop_id', 0);
        if(empty($where['shop_level'])) {
            unset($where['shop_level']);
        }
        if(empty($where['shop_id'])) {
            unset($where['shop_id']);
        }
        
        if(!isset($where['shop_id']) && $this->shop_id){
            $where['shop_id'] = $this->shop_id;
        }
        
        $model = LibF::M('order_info');
        $shop_tj = $model->field('shop_level,shop_id,sum(goods_num) as num,goods_name,goods_id')->group('shop_id,goods_type,goods_id')->where($where)->select();
        $type = $shoptj = array();
        if (!empty($shop_tj)) {
            foreach ($shop_tj as $v) {
                if (!empty($v['goods_name'])) {
                    $shoptj[$v['goods_id']] = $v['num'];
                    $type[$v['goods_id']] = $v['goods_name'];
                }
            }
            ksort($type);
            ksort($shoptj);
        }
        
        $data = array('type'=>'"'.implode('","',$type).'"','shoptj'=>implode(',',$shoptj), 'shop_tj'=>$shop_tj);
        $this->_view->assign($data);
    }

}