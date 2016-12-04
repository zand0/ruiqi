<?php

/**
 * 燃气库存
 */
class GasinoutController extends \Com\Controller\Common {

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
        
        $this->modelD = LibF::D('Gaslog');
    }

    public function addAction() {
        $gasObject = GasModel::getGasArray();

        $tankModel = new TankModel();
        $tankObject = $tankModel->getTankArray('',array('status'=>1));
        
        $returnData = array();
        if (IS_POST) {
            $gas_type = $this->getRequest()->getpost('gas_type');
            $car_type = $this->getRequest()->getPost('car_type');
            $num = $this->getRequest()->getPost('num');
            $tank_type = $this->getRequest()->getPost('tank_type');
            $cz_user = $this->getRequest()->getPost('cz_user');
            //$ck_time = $this->getRequest()->getPost('ck_time',date('Y-m-d'));
            $ck_time = date("Y-m-d");
            $comment = $this->getRequest()->getPost('comment');
            $type = $this->getRequest()->getPost('type');

            $param['ctime'] = time();
            $data = array(
                'gtype' => $gas_type,
                'gnum' => $num,
                'ctime' => time()
            );
            $tank_id = $tank_type;
            $param['admin_user'] = $this->user_info['username'];
            $param['time'] = $ck_time;
            $param['type'] = $type;
            $param['gtype'] = $gas_type;
            $param['gnum'] = $num;
            $param['gprice'] = '';
            $param['tank_id'] = $tank_type;
            $param['license_plate'] = $car_type;
            $param['comment'] = $comment;

            $app = new App();
            $orderSn = $app->build_order_no();
            $snolist = 'rqkc' . $orderSn;

            $tgData = array(
                'tanksn' => $snolist,
                'tankid' => $tank_id,
                'tank_name' => $tankObject[$tank_id]['tank_name'],
                'gtype' => $gas_type,
                'gname' => $gasObject[$param['gtype']]['gas_name'],
                'volume' => $num,
                'time' => $ck_time,
                'time_created' => $param['ctime']
            );

            if ($type == 1) {
                //出库
                //$this->modelD->uadd($data, '`gnum`=`gnum`-' . $num . ',ctime=' . $param['ctime']);
                $this->modelD->utank(array('id' => $tank_id), '`total`=`total`-' . $num);

                LibF::M('tank_gas')->where(array('tankid' => $tank_id))->setDec('volume', $num);
            } else {
                //入库
                //$this->modelD->uadd($data, '`gnum`=`gnum`+' . $num . ',ctime=' . $param['ctime']);
                $this->modelD->utank(array('id' => $tank_id), '`total`=`total`+' . $num);
                $dataInfo = LibF::M('tank_gas')->where(array('tankid' => $tank_id))->find();
                if (!empty($dataInfo)) {
                    LibF::M('tank_gas')->where(array('tankid' => $tank_id))->setInc('volume', $num);
                } else {
                    LibF::M('tank_gas')->add($tgData);
                }
            }
            $param['gaslog_sno'] = $snolist;

            $returnData = $this->modelD->add($param);
            $msg = '添加成功';
        }
        echo json_encode($returnData);
        exit();
    }

    public function editeAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status'=>1));
        \Tools::redirect('/gasinout/index');
    }
    
    public function delAction() {
        $id = intval($this->getRequest()->getQuery('id'));
        $this->modelD->edite('edite', $id, array('status'=>2));
        $this->success('操作成功', '/gasinout/index');
    }

    public function indexAction() {
        
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (isset($param['type']) && ($param['type'] >=0)){
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (isset($param['gtype']) && !empty($param['gtype'])){
                $where['gtype'] = $param['gtype'];
                $getParam[] = "gtype=" . $param['gtype'];
            }
            if (isset($param['license_plate']) && !empty($param['license_plate'])){
                $where['license_plate'] = $param['license_plate'];
                $getParam[] = "license_plate=" . $param['license_plate'];
            }
            if (!empty($param['start_time']) && !empty($param['end_time'])){
                $where['ctime'] = array(array('egt', strtotime($param['start_time'])), array('elt', strtotime($param['end_time'].' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['start_time'];
                $getParam['time_end'] = "time_end=" . $param['end_time'];
            }
            unset($param['submit']);
            $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $gaslogModel = new FillingModel();
        $data = $gaslogModel->getDataList($param, 'gas_log', $where, 'ctime desc');
        $this->_view->assign($data);
        
        $gas_type = GasModel::getGasArray();
        $this->_view->assign('gas_type',$gas_type);

        $tankModel = new TankModel();
        $tankObject = $tankModel->getTankArray();
        $this->_view->assign('tankObject', $tankObject);
        
        $dataTotal = LibF::M('tank_gas')->order('gtype asc,tankid asc')->select();
        $this->_view->assign('dataTotal',$dataTotal);
        
        
        $tankModel = new TankModel();
        $tank_type = $tankModel->getTankArray();
        
        $returnData = array();
        $data = LibF::M('gas_log')->order('ctime desc,tank_id')->select();
        
        $list = '';
        if(!empty($data)){
            foreach ($data as &$value){
                $value['time'] = date('Y-m-d',$value['ctime']);
                $returnData[$value['time']][] = $value;
            }
            foreach($returnData as $k => $val){
                $num = count($val);

                foreach ($val as $ii => $v) {
                    $list .= '<tr>';
                    if ($ii == 0) {
                        $list .= '<td rowspan="' . $num . '">' . $k . '</td>';
                    }
                    $list .= '<td>' . $tank_type[$v['tank_id']]['tank_name'] . '</td>
                                <td>' . $gas_type[$v['gtype']]['gas_name'] . '</td>
                                <td>' . $v['gnum'] . '</td>';
                    if ($ii == 0) {
                        $list .= '<td rowspan="' . $num . '"></td>';
                    }
                    $list .= '</tr>';
                }
            }
        }
        $this->_view->assign('returnData',$returnData);
        $this->_view->assign('list',$list);
    }

}