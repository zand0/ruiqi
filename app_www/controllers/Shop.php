<?php

/**
 * 店铺管理
 *
 * @author zxg
 */
class shopController extends \Com\Controller\Common {

    protected $adminId = 0;
    protected $shop_id = 0;
    protected $shopLevel = 0;
    protected $shopLevelMap;
    protected $userinfo;

    public function init() {
        parent::init();
        $this->shopD = LibF::D('Shop');
        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        $this->shop_id = $adminInfo['shop_id'];
        $this->adminShopLevel = $adminInfo['shop_level'];
        $this->adminId = $adminInfo['user_id'];
        $this->userinfo = $adminInfo;

        $this->shopLevelMap = array(1 => '中心店', 2 => '卫星店', 3 => '社区店');
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $getParam = $sWhere = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['region_1'])) {
                $sWhere['region_1'] = $param['region_1'];
                $getParam[] = "region_1=" . $param['region_1'];
            }
            if (!empty($param['region_2'])) {
                $sWhere['region_2'] = $param['region_2'];
                $getParam[] = "region_2=" . $param['region_2'];
            }
            if (!empty($param['region_3'])) {
                $sWhere['region_3'] = $param['region_3'];
                $getParam[] = "region_3=" . $param['region_3'];
            }
            if (!empty($param['region_4'])) {
                $sWhere['region_4'] = $param['region_4'];
                $getParam[] = "region_4=" . $param['region_4'];
            }
            if (!empty($param['shop_type'])) {
                $sWhere['shop_type'] = $param['shop_type'];
                $getParam[] = "shop_type=" . $param['shop_type'];
            }

            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $sWhere['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $sWhere['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            if (!empty($getParam)) {
                $this->_view->assign('getparamlist', implode('&', $getParam));
            }
        } else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $sWhere['shop_id'] = $this->shop_id;
            } else {
                $sWhere['shop_id'] = array('gt', 1);
            }
        }

        $sWhere['status'] = 1;

        $CommonDataModel = new CommonDataModel();
        $areaData = $CommonDataModel->getQuarterData();
        
        $shopModel = new OrgModel();
        $shoplistData = $shopModel->shoplist($sWhere,$this->shopLevelMap,$areaData);
        $this->_view->assign('shoplistData', $shoplistData);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    public function addAction() {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $user_id = 0;
            $data['shop_name'] = $postData['shop_name'];
            if (!empty($postData['admin_id'])) {
                $adminData = explode('|', $postData['admin_id']);
                $data['admin_id'] = $user_id = $adminData[0];
                $data['admin_name'] = $adminData[1];
                $data['mobile_phone'] = $adminData[2];
            } else {
                $data['admin_id'] = $user_id = 0;
                $data['admin_name'] = '';
                $data['mobile_phone'] = '';
            }
            if (!empty($postData['tel_phone']))
                $data['tel_phone'] = $postData['tel_phone'];
            if (!empty($postData['level']))
                $data['level'] = $postData['level'];
            if (!empty($postData['area_id']))
                $data['area_id'] = $postData['area_id'];

            if(!empty($postData['shop_type']))
                $data['shop_type'] = $postData['shop_type'];
            
            if (!empty($postData['region_1']))
                $data['region_1'] = $postData['region_1'];
            if (!empty($postData['region_2']))
                $data['region_2'] = $postData['region_2'];
            if (!empty($postData['region_3']))
                $data['region_3'] = $postData['region_3'];
            if (!empty($postData['region_4']))
                $data['region_4'] = $postData['region_4'];
            if (!empty($postData['address']))
                $data['address'] = $postData['address'];
            if (!empty($postData['coordinate']))
                $data['coordinate'] = $postData['coordinate'];

            $data['parent_shop_id'] = $postData['parent_shop_id'];
            
            $data['comment'] = $postData['comment'];
            $data['add_admin_id'] = $this->adminId;
            $shop_id = $postData['shop_id'];
            if ($shop_id) {
                $status = LibF::M('shop')->where(array('shop_id' => $shop_id))->save($data);
                LibF::M('admin_user')->where(array('user_id' => $user_id))->save(array('shop_id' => $shop_id));
            } else {
                $app = new App();
                $qysn = $app->build_order_no();
                $data['shop_code'] = 'md' . $qysn;
                $data['add_time'] = time();
                $shop_id = LibF::M('shop')->add($data);

                if ($user_id)
                    LibF::M('admin_user')->where(array('user_id' => $user_id))->save(array('shop_id' => $shop_id));
            }
            if ($shop_id) {
                $this->success('更新成功', '/shop/index');
            } else {
                $this->success('更新失败', '/shop/index');
            }
            exit;
        }

        $this->_view->assign('shopLevelMap', $this->shopLevelMap);

        //获取门店
        $w['parent_shop_id'] = 0;
        $w['level'] = 1;
        $shopObject = ShopModel::getShopArray(0, $w);
        $this->_view->assign('shopObject', $shopObject);

        //获取当前区域
        $shop_id = $this->getRequest()->getQuery('shop_id');
        if (!empty($shop_id)) {
            $data = LibF::M('shop')->where(array('shop_id' => $shop_id))->find();
            $this->_view->assign($data);
        }

        //获取区域列表
        $CommonDataModel = new CommonDataModel();
        $areaData = $CommonDataModel->getQuarterData();
        $this->_view->assign('areaData', $areaData);

        //获取门店经理列表
        $CommonDataModel = new CommonDataModel();
        $userdata = $CommonDataModel->getAreaUser(1);
        $this->_view->assign('userdata', $userdata);
    }

    public function paymentAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        $where = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (empty($param['shop_id']) && $this->shop_id) {
                $where['shop_id'] = $this->shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }

            unset($param['submit']);
            if (!empty($getParam)) {
                $this->_view->assign('getparamlist', implode('&', $getParam));
            }
        } else {
            if ($this->shop_id)
                $where['shop_id'] = $this->shop_id;
        }

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        //预付款记录
        $commodityModel = new FillingModel();
        $payMentlist = $commodityModel->getDataList($param, 'shop_prepayments', $where, 'id desc');
        $this->_view->assign($payMentlist);

        $typeObject = array(1 => '预付款', 2 => '结算');
        $dataTotal = LibF::M('shop_prepayments')->field('type,sum(money) as total')->where($where)->group('type')->select();
        $this->_view->assign('typeObject', $typeObject);
        $this->_view->assign('dataTotal', $dataTotal);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    /**
     * 门店钢瓶借调
     */
    public function secondmentAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        $where = array();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam[] = "time_start=" . $param['time_start'];
                $getParam[] = "time_end=" . $param['time_end'];
            }
            if (!empty($param['shop_id'])) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }

            unset($param['submit']);
            if (!empty($getParam)) {
                $this->_view->assign('getparamlist', implode('&', $getParam));
            }
        }

        if ($this->shop_id) {
            
            $condition['shop_id'] = $this->shop_id;
            $condition['to_shop_id'] = $this->shop_id;
            $condition['_logic'] = 'OR';
            $where["_complex"] = $condition;

            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        //门店钢瓶借调
        $commodityModel = new FillingModel();
        $payMentlist = $commodityModel->getDataList($param, 'store_secondment', $where, 'id desc');
        $this->_view->assign($payMentlist);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    public function addsecondmentAction() {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $license_plate = $this->getRequest()->getPost('license_plate');
            $guards = $this->getRequest()->getPost('guards');
            $bottle_list = $this->getRequest()->getPost('bottle_list');
            $comment = $this->getRequest()->getPost('comment');
            $shop_id = $this->getRequest()->getPost('shop_id');

            $returnData = $this->createConfirmBack($license_plate, $guards, $bottle_list, $this->userinfo['user_id'], $this->userinfo['username'], $shop_id, $this->shop_id, $comment);
            if ($returnData['status'] != 200) {
                $this->error($addRes['msg']);
            }
            echo json_encode($returnData);
            exit();
        }
    }

    public function addbottleAction() {
        $sn = $this->getRequest()->getQuery('sn');
        if (empty($sn)) {
            $this->error('当前单据不存在!');
        }

        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();

        $list = $shop_name = '';
        $shopObject = ShopModel::getShopArray();

        $where['confirme_no'] = $sn;
        $where['shop_id'] = $this->shop_id;
        $where['status'] = 0;
        $data = LibF::M('store_secondment')->where($where)->find();
        if (!empty($data) && !empty($data['bottle'])) {
            $bottleArr = json_decode($data['bottle']);
            foreach ($bottleArr as $value) {
                $bArr = explode('|', $value);
                $list .= "<tr><td>" . $bottleTypeObject[$value['0']]['bottle_name'] . "</td><td>" . $bArr[1] . "</td></tr>";
            }
            $shop_name = isset($shopObject[$data['shop_id']]) ? $shopObject[$data['shop_id']]['shop_name'] : '';
        }else{
            $this->error('当前单据不存在!');
            exit;
        }
        $this->_view->assign('list', $list);
        $this->_view->assign('shop_name', $shop_name);
        $this->_view->assign('to_shop_id', $data['to_shop_id']);
        $this->_view->assign('sn',$sn);
    }

    public function ajaxbottleAction() {
        $number = $this->getRequest()->getPost('number');
        $sn = $this->getRequest()->getPost('sn');
        $to_shop_id = $this->getRequest()->getPost('to_shop_id');
        if (empty($number)) {
            $this->error('当前钢瓶信息不存在!');
        }
        if ($this->shop_id == $to_shop_id) {
            $this->error('当前借调门店一致!');
        }
        $bottle = explode(PHP_EOL, $number);

        //获取当前钢瓶规格
        $bottleModel = new BottleModel();
        $bottleData = $bottleModel->getStoreBottle($to_shop_id);

        //规格
        $bottleTypeModel = new BottletypeModel();
        $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

        //当前门店存在 送气工去门店申领重瓶
        $returnData = $typeData = $numberArr = array();
        foreach ($bottle as $value) {
            $data = array();
            $value = trim($value);
            $data['number'] = $value;
            $kind_id = isset($bottleData['number'][$value]) ? $bottleData['number'][$value]['type'] : 0; //规格
            if ($kind_id > 0) {
                if (!isset($typeData[$kind_id])) {
                    $typeData[$kind_id] = 1;
                } else {
                    $typeData[$kind_id] += 1;
                }
                $data['type_name'] = $bottleTypeData[$kind_id]['bottle_name'];
                $data['status'] = 1;
                $data['status_text'] = '正常';
                $numberArr[] = $value;
            } else {
                $data['type_name'] = '';
                $data['status'] = 0;
                $data['status_text'] = '当前钢瓶不在门店';
            }
            $returnData[] = $data;
        }
        $numberArr = array_unique($numberArr);
        $this->_view->assign('numberTotal',count($numberArr));
        $this->_view->assign('numberArr', json_encode($numberArr));
        $this->_view->assign('bottleTypeData', $bottleTypeData);
        $this->_view->assign('returnData', $returnData);
        $this->_view->assign('typeData', $typeData);
        $this->_view->assign('typeDataJson', json_encode($typeData));
        $this->_view->assign('sn', $sn);
        $this->_view->assign('to_shop_id', $to_shop_id);
        $this->_view->assign('shop_id', $this->shop_id);
    }
    
    public function roambottleAction() {
        $data = $this->getRequest()->getPost();
        
        $status = 0;
        if (!empty($data)) {
            $shop_id = $this->shop_id;
            if ($shop_id == $data['to_shop_id']) {
                $msg = '当前借调门店和送达门店一致';
            } else {

                //规格
                $bottleTypeModel = new BottletypeModel();
                $bottleTypeData = $bottleTypeModel->getBottleTypeArray();

                $numberArr = json_decode($data['number'], true);
                if (!empty($numberArr)) {
                    $numberArr = array_unique($numberArr);
                    
                    //判断当前是否已经录入信息
                    $isData = LibF::M('store_secondment')->where(array('shop_id' => $shop_id, 'confirme_no' => $data['sn'], 'status' => 1))->find();
                    if (!empty($isData)) {
                        $msg = '当前单据已经处理完成';
                    } else {

                        $dataTransModel = new DatatransferModel();
                        $tdata = $dataTransModel->storeInventory($numberArr, $shop_id, 1, $data['sn']);  //钢瓶录入
                        if ($tdata['status'] == 200) {
                            $uwhere['confirme_no'] = $data['sn'];
                            $udata['status'] = 1;
                            LibF::M('store_secondment')->where($uwhere)->save($udata);

                            $where['number'] = array('in', $numberArr);
                            $where['shop_id'] = $data['to_shop_id'];
                            $where['status'] = 1;
                            $where['is_use'] = 1;
                            $update['status'] = -1;
                            //LibF::M('store_inventory')->where($where)->delete(); //删除门店钢瓶
                            LibF::M('store_inventory')->where($where)->save($update);

                            $total = json_decode($data['total'], true);
                            $this->shopInstoreData($shop_id, $data['to_shop_id'], 1, $total, $bottleTypeData, $this->userinfo['username']);  //门店入库
                            $this->shopInstoreData($shop_id, $data['to_shop_id'], 2, $total, $bottleTypeData, $this->userinfo['username']);  //门店出库
                            
                            $logParam['confirme_no'] = $data['sn'];
                            $logParam['shop_id'] = $shop_id;
                            $logParam['to_shop_id'] = $data['to_shop_id'];
                            $logParam['bottle'] = json_encode($numberArr);
                            $logParam['admin_id'] = $this->userinfo['user_id'];
                            $logParam['admin_user'] = $this->userinfo['username'];
                            $logParam['ctime'] = time();
                            LibF::M('store_secondment_log')->add($logParam);
                            
                            $status = 1;
                        }
                    }
                }else{
                    $msg = '当前芯片号不存在';
                }
            }
        } else {
            $msg = '未提交参数';
        }
        $return['status'] = $status;
        $return['msg'] = $msg;
        echo json_encode($return);
        exit;
    }

    /**
     * ajax请求根据门店等级获取门店
     */
    public function getshopbylevelAction() {
        $level = $this->getRequest()->getQuery('level');
        $res = $this->shopD->getshopbywhere(array('level' => $level), 'shop_id,shop_name');
        echo json_encode($res);
        exit;
    }

    protected function createConfirmBack($license_plate = '', $guards = '', $bottle_list, $user_id, $user_name = '', $to_shop_id, $shop_id, $comment = '') {

        if (empty($bottle_list) || empty($user_id) || empty($shop_id))
            return false;

        $returnData = array();
        $app = new App();
        $data['confirme_no'] = 'jd' . $app->build_order_no();
        $data['shop_id'] = $shop_id;
        $data['to_shop_id'] = $to_shop_id;
        if (!empty($comment))
            $data['comment'] = $comment;
        $data['license_plate'] = $license_plate;
        $data['guards'] = $guards;
        $data['bottle'] = json_encode($bottle_list);
        $data['create_cuser'] = $user_id;
        if (!empty($user_name))
            $data['create_cusername'] = $user_name;

        $data['ctime'] = time();
        $status = LibF::M('store_secondment')->add($data);
        if ($status) {
            $returnData['status'] = 200;
        }
        return $returnData;
    }

    protected function shopInstoreData($shop_id, $to_shop_id, $type = 1, $data, $bottleTypeObject, $user_name = '') {
        if (empty($shop_id) || empty($to_shop_id) || empty($data))
            return false;

        $shopObject = ShopModel::getShopArray();

        $param['type'] = $type; //出入库1入库 2出库
        $app = new App();
        $orderSn = $app->build_order_no();
        $param['documentsn'] = 'gpkc' . $orderSn;
        if (!empty($user_name))
            $param['admin_user'] = $user_name;
        $param['time'] = date('Y-m-d');
        $param['ctime'] = time();
        $param['gtype'] = 1;
        if ($type == 1) {
            $param['shop_id'] = $shop_id;
            $param['shop_name'] = $shopObject[$shop_id]['shop_name'];
            $param['for_name'] = $shopObject[$to_shop_id]['shop_name'];
        } else {
            $param['shop_id'] = $to_shop_id;
            $param['shop_name'] = $shopObject[$to_shop_id]['shop_name'];
            $param['for_name'] = $shopObject[$shop_id]['shop_name'];
        }
        $status = 0;
        if (!empty($data)) {
            $stockmethodModel = new StockmethodModel();
            foreach ($data as $key => $dVal) {

                $param['goods_propety'] = 2;  //钢瓶类型
                $param['goods_id'] = $param['goods_type'] = $key; //钢瓶规格
                $param['goods_name'] = $bottleTypeObject[$param['goods_id']]['bottle_name'];
                $param['goods_price'] = $bottleTypeObject[$param['goods_id']]['bottle_price'];
                $param['goods_num'] = $dVal;
                if ($type == 1) {
                    $status = $stockmethodModel->ShopstationsStock($param, $shop_id, 1);  //统一出入库（门店）
                } else {
                    $status = $stockmethodModel->ShopstationsStock($param, $to_shop_id, 1);  //统一出入库（门店）
                }
            }
        }
        return $status;
    }

}
