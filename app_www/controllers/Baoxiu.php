<?php
/**
 * 报修管理
 */
class BaoxiuController extends \Com\Controller\Common{
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
        $this->modelD = LibF::D('Baoxiu');
    }

    public function addAction() {
        if(IS_POST) {
            $id = intval($this->getRequest()->getPost('id'));
            $param = $_POST;
            $param['ctime'] = time();
            if(!empty($id)) {
                $this->modelD->edite('edite', $id, $param);
                $msg = '更新成功';
            } else {
                $this->modelD->add($param);
                $msg = '添加成功';
            }
            $this->success($msg, '/baoxiu/index');
        }
        //获取门店
        $this->_view->assign('shop_list', ShopModel::getShopArray());
        
        $this->_view->assign('shop_id', $this->shop_id);
        $this->_view->assign('shop_level', $this->shop_level);
    }

    public function editeAction() {

        $rData['status'] = 0;
        if (IS_POST) {
            $id = $this->getRequest()->getPost('bid');
            $data['treatment'] = $this->getRequest()->getPost('treatment');
            $data['admin_user_id'] = $this->user_info['user_id'];
            $data['admin_user_name'] = $this->user_info['username'];
            $data['status'] = 2;

            $returnData = $this->modelD->edite('edite', $id, $data);
            if ($returnData['status'] == 200) {
                $rData['status'] = 1;
            }
        }
        echo json_encode($rData);
        exit();
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status'=>2));
        $this->success('操作成功', '/baoxiu/index');
    }

    public function indexAction() {
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
                }
            }
            if (!empty($param['kname'])){
                $where['kname'] = $param['kname'];
                $getParam[] = "kname=" . $param['kname'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'].' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
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
        $this->_view->assign('page',$param['page']);
        $this->_view->assign('param', $param);

        $tousuModel = new FillingModel();
        $returnData = $tousuModel->getDataList($param, 'baoxiu', $where, 'id desc');
        $this->_view->assign($returnData);
        
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }
    
    //派发
    public function distributionAction() {

        $rData['status'] = 0;
        if (IS_POST) {
            $shopObject = ShopModel::getShopArray();
            $baoxiusn = $this->getRequest()->getPost('baoxiusn');
            $shop_id = $this->getRequest()->getPost('shop_id');
            $shipper_id = $this->getRequest()->getPost('shipper_id');

            if (!empty($shipper_id) && $shop_id && !empty($baoxiusn)) {
                $shop_name = $shopObject[$shop_id]['shop_name'];

                $shipperList = explode('|', $shipper_id);
                $shiper['shipper_id'] = isset($shipperList[0]) ? $shipperList[0] : 0;
                $shiper['shipper_name'] = isset($shipperList[1]) ? $shipperList[1] : '';
                $shiper['shipper_mobile'] = isset($shipperList[2]) ? $shipperList[2] : '';
                $shiper['status'] = 1;

                $rData['status'] = LibF::M('baoxiu')->where(array('encode_id' => $baoxiusn))->save($shiper);
                
                $platform = 'android'; // 接受此信息的系统
                $msg_content = json_encode(array('n_builder_id' => $shiper['shipper_id'], 'n_title' => $baoxiusn, 'n_content' => '报修订单已经派发', 'n_extras' => array('fromer' => $shop_name, 'fromer_name' => $shop_name, 'fromer_icon' => '', 'image' => '', 'sound' => '')));
                $smsSend = new SmsDataModel();
                $smsSend->send(16, 3, $shiper['shipper_id'], 1, $msg_content, $platform);
            }
        }
        echo json_encode($rData);
        exit();
    }
}