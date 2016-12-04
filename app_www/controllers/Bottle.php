<?php 
/**
 * 气瓶管理
 */

class BottleController extends \Com\Controller\Common {

    public function init() {
        parent::init();
        $this->modelD = LibF::D('Bottle');
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

    public function addAction() {
        if (IS_POST) {
            $id = $this->getRequest()->getPost('id');

            $param = $_POST;
            unset($param['id']);
            isset($param['production_date']) && $param['production_date'] = strtotime($param['production_date']);
            isset($param['check_date']) && $param['check_date'] = strtotime($param['check_date']);
            isset($param['check_date_next']) && $param['production_date'] = strtotime($param['check_date_next']);

            if (!empty($id)) {
                $condition['xinpian'] = $param['xinpian'];
                $condition['number'] = $param['number'];
                $condition['_logic'] = 'OR';
                $data = LibF::M('bottle')->where($condition)->find();
                if (empty($data)) {
                    $this->modelD->edite('edite', $id, $param);
                }
                $msg = '更新成功';
            } else {
                $number = $xinpian = $bar_code = array();
                if (isset($param['number']) && !empty($param['number'])) {
                    $number = explode(PHP_EOL, $param['number']);
                    unset($param['number']);
                }
                if (isset($param['xinpian']) && !empty($param['xinpian'])) {
                    $xinpian = explode(PHP_EOL, $param['xinpian']);
                    unset($param['xinpian']);
                }
                if (isset($param['bar_code']) && !empty($param['bar_code'])) {
                    $bar_code = explode(PHP_EOL, $param['bar_code']);
                    unset($param['bar_code']);
                }
                foreach ($number as $key => $value) {
                    $param['number'] = trim($value);
                    $param['xinpian'] = isset($xinpian[$key]) ? trim($xinpian[$key]) : '';
                    $param['bar_code'] = isset($bar_code[$key]) ? trim($bar_code[$key]) : '';

                    $condition['xinpian'] = $param['xinpian'];
                    $condition['number'] = $param['number'];
                    $condition['_logic'] = 'OR';
                    $data = LibF::M('bottle')->where($condition)->find();
                    if (empty($data)) {
                        $this->modelD->add($param);
                    }
                }
                $msg = '添加成功';
            }
            $this->success($msg, '/bottle/index');
        }
        $data = LibF::LC('bottle')->toArray();
        $data['btypes'] = BottleModel::getBottleType();
        $this->_view->assign($data);

        $typeObject = array(0 => '新瓶', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $this->_view->assign('typeObject', $typeObject);
    }

    public function editeAction() {
        $xinpian = $this->getRequest()->getQuery('xinpian');
        if (!empty($xinpian)) {
            $fillMode = new FillingModel();
            $data = $fillMode->getDataInfo('bottle', array('xinpian' => $xinpian));

            $data['btypes'] = BottleModel::getBottleType();
            $this->_view->assign($data);
        }

        $typeObject = array(0 => '新瓶', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $this->_view->assign('typeObject', $typeObject);
        $this->_view->display('bottle/add.phtml');
        exit;
    }

    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status' => 2));
        $this->success('操作成功', '/bottle/index');
    }

    public function indexAction() {

        $tempType = $this->getRequest()->getQuery('temptype','now'); //now 当前页面 new 新页面
        
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        $where = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['number'])){
                $where['number'] = $param['number'];
                $getParam[] = "number=" . $param['number'];
            }
            if (!empty($param['xinpian'])){
                $where['xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }
            if (!empty($param['type'])){
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['status'])){
                $where['status'] = $param['status'];
                $getParam[] = "status=" . $param['status'];
            }
            if (!empty($param['bar_code'])){
                $where['bar_code'] = $param['bar_code'];
                $getParam[] = "bar_code=" . $param['bar_code'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'].' 23:59:59')), 'AND');
                
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        //$where['is_active'] = 1;

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $commodityModel = new FillingModel();
        $data = $commodityModel->getDataList($param, 'bottle', $where, 'gp_id desc');
        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        $typeObject = array(0 => '新瓶', 1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');
        $this->_view->assign('typeObject', $typeObject);
        if ($tempType == 'new') {
            $colorObjet = array(0 => '#2cb6f4', 1 => '#03dbb6', 2 => '#ff863b', 3 => '#cd957c');
            $this->_view->assign('colorObject',$colorObjet);

            //统计当前钢瓶数量
            $bottleShow = $bottleNumShow = array();
            $botttObject = LibF::M('bottle')->field('type,status,count(*) as total')->group('type,status')->select();
            if (!empty($botttObject)) {
                foreach ($botttObject as &$val) {
                    $bVal['type'] = $val['type'];
                    $bVal['bottle_name'] = $bottleTypeData[$val['type']]['bottle_name'];
                    $bVal['status'] = $val['status'];
                    $bVal['status_text'] = $typeObject[$val['status']];
                    $bVal['num'] = $val['total'];
                    $bVal['proportion'] = sprintf("%01.2f", $val['total'] / $data['ext']['count']) * 100;
                    $bottleShow[$bVal['type']][] = $bVal;
                    if (!isset($bottleNumShow[$val['status']])) {
                        $bottleNumShow[$val['status']] = $val['total'];
                    } else {
                        $bottleNumShow[$val['status']] += $val['total'];
                    }
                }
            }
            
            $this->_view->assign('bottleShow',$bottleShow);
            $this->_view->assign('bottleNumShow',$bottleNumShow);
            $this->_view->display('bottle/index_new.phtml');
        }
    }

    public function logAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        
        $where = '';
        if (!empty($param)) {
            $getParam = array();
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['rq_bottle_log.shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['rq_bottle_log.shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($param['shipper_id'])) {
                $where['rq_bottle_log.shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=".$param['shipper_id'];
            }
            if (!empty($param['number'])) {
                $where['rq_bottle_log.number'] = $param['number'];
                $getParam[] = "number=".$param['numbers'];
            }
            if (isset($param['xinpian']) && !empty($param['xinpian'])) {
                $where['rq_bottle_log.xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }

            if (isset($param['order_sn']) && !empty($param['order_sn'])) {
                $where['rq_bottle_log.order_sn'] = $param['order_sn'];
                $getParam[] = "order_sn=" . $param['order_sn'];
            }

            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        } else {
            if ($this->shop_id) {
                $where['rq_bottle_log.shop_id'] = $this->shop_id;
            } 
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $fillModel = new FillingModel();
        $field = "rq_bottle_log.*,rq_kehu.kehu_sn,rq_kehu.user_name";
        $data = $fillModel->getDataTableList($param, 'bottle_log', 'rq_bottle_log', 'rq_kehu', 'kehu_id', $where, 'rq_bottle_log.kehu_time desc,rq_bottle_log.id desc', $field, 'kid');
        $this->_view->assign($data);

        //获取当前钢瓶规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeData', $bottleTypeData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $shipperModel = new ShipperModel();
        $shipperData = $shipperModel->getShipperArray('', $this->shop_id);
        $this->_view->assign('shipperData', $shipperData);
    }

    public function transferAction() {

        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);
        $where = array();
        if (!empty($param)) {
            if (!empty($param['xinpian']))
                $where['xinpian'] = $param['xinpian'];
            if (!empty($param['type']))
                $where['type'] = $param['type'];

            unset($param['submit']);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        
        $fillModel = new FillingModel();
        $data = $fillModel->getDataList($param, 'bottle_transfer_logs', $where, 'id desc');
        $this->_view->assign($data);
    }
    
    /**
     * 钢瓶日志（外部查询）
     */
    public function bottlelogAction() {
        
    }
        

}
