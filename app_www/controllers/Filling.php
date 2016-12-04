<?php

/**
 * 备注：门店的配送计划 等价 气站充装计划
 */
class FillingController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;
    protected $ftypeObject;
    protected $typeObject;
    protected $roles;
    
    protected $timekey;

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
            $this->roles = $adminInfo['roles'];
        }

        $this->modelD = LibF::D('Filling');

        $this->ftypeObject = array(0 => '重瓶入库', 1 => '配件入库', 2 => '退回空瓶', 3 => '退回废瓶');
        $this->typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶(普通瓶)', 4 => '待修瓶', 5 => '待检瓶', 6 => '报废瓶');

        $this->timekey = array(1 => '今日', 2 => '昨天', 3 => '本周', 4 => '上周', 5 => '本月', 6 => '上月', 7 => '本季度', 8 => '本年度');
    }

    /**
     * 门店配送计划单
     * 
     * @备注 门店配送计划 门店添加  filling  filling_detail
     */
    public function addAction() {

        if (IS_POST) {
            $shop_id = $this->shop_id ? $this->shop_id : $this->getRequest()->getPost('shop_id', 1);

            $ps_time = $this->getRequest()->getPost('ps_time', date('Y-m-d'));
            $ps_bottle_list = $this->getRequest()->getPost('ps_bottle_list');
            $ps_product_list = $this->getRequest()->getPost('ps_product_list');
            $hk_bottle_list = $this->getRequest()->getPost('hk_bottle_list');
            $hk_product_list = $this->getRequest()->getPost('hk_product_list');
            $comment = $this->getRequest()->getPost('comment');

            //获取订单号
            $time = time();
            $app = new App();
            $filling_no = 'psjh' . $app->build_order_no();
            $data = array(
                'filling_no' => $filling_no,
                'shop_id' => $shop_id,
                'ps_bottle' => (!empty($ps_bottle_list)) ? json_encode($ps_bottle_list) : '',
                'ps_product' => (!empty($ps_product_list)) ? json_encode($ps_product_list) : '',
                'hk_bottle' => (!empty($hk_bottle_list)) ? json_encode($hk_bottle_list) : '',
                'hk_product' => (!empty($hk_product_list)) ? json_encode($hk_product_list) : '',
                'ps_time' => $ps_time,
                'comment' => $comment,
                'ctime' => $time,
                'cuser' => $this->user_info['user_id']
            );
            $status = $this->modelD->add($data);
            if ($status) {
                $insertData = array();
                if (!empty($ps_bottle_list)) {
                    foreach ($ps_bottle_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['filling_no'] = $filling_no;
                        $data['ftype'] = 1;
                        $data['type'] = 0;
                        $data['fid'] = $v[0];
                        $data['num'] = $v[1];
                        $data['ctime'] = $time;
                        $insertData[] = $data;
                    }
                }
                if (!empty($ps_product_list)) {
                    foreach ($ps_product_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['filling_no'] = $filling_no;
                        $data['ftype'] = 2;
                        $data['type'] = $v[1];
                        $data['fid'] = $v[0];
                        $data['num'] = $v[2];
                        $data['ctime'] = $time;
                        $insertData[] = $data;
                    }
                }
                if (!empty($hk_bottle_list)) {
                    foreach ($hk_bottle_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['filling_no'] = $filling_no;
                        $data['ftype'] = 3;
                        $data['type'] = $v[0];
                        $data['fid'] = $v[1];
                        $data['num'] = $v[2];
                        $data['ctime'] = $time;
                        $insertData[] = $data;
                    }
                }
                if (!empty($hk_product_list)) {
                    foreach ($hk_product_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['filling_no'] = $filling_no;
                        $data['ftype'] = 4;
                        $data['type'] = $v[1];
                        $data['fid'] = $v[0];
                        $data['num'] = $v[2];
                        $data['ctime'] = $time;
                        $insertData[] = $data;
                    }
                }
                if (!empty($insertData)) {
                    LibF::M('filling_detail')->uinsertAll($insertData);
                }
            }
            $returnData = $status;
            echo json_encode($returnData);
        }
        exit();
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = '';
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;

                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }
            if (!empty($param['filling_no'])) {
                $where['filling_no'] = $param['filling_no'];
                $getParam[] = "filling_no=" . $param['filling_no'];
            }
            
            if (!empty($param['status'])){
                if ($param['status'] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $where['status'] = $status;
                $getParam['status'] = "status=" . $param['status'];
            }
            
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ps_time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end'] . ' 23:59:59'), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
                $where['shop_id'] = $this->shop_id;
            }
        }

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $commodityModel = new FillingModel();
        $data = $commodityModel->getDataList($param, 'Filling', $where, 'id desc');
        $this->_view->assign($data);

        $btypes = BottleModel::getBottleType();
        $this->_view->assign('btypes', $btypes);

        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        $this->_view->assign('shop_id', $param['shop_id']);

        $page = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $page);
    }

    /**
     * 门店配送计划详情
     * 
     */
    public function infoAction() {
        $sno = $this->getRequest()->getQuery('sno');
        if (empty($sno)) {
            $this->error('当前单据不存在!');
        }

        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();

        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commitObject = array();
        $fillingstocklogModel = new FillingModel();
        $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($productObject)) {
            foreach ($productObject as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $commitObject[$value['id']] = $value;
            }
        }

        $d = LibF::M('filling')->where(array('filling_no' => $sno))->find();
        $this->_view->assign($d);

        $file = "sum(num) as total,ftype,type,fid";
        $data = LibF::M('filling_detail')->field($file)->where(array('filling_no' => $sno))->group('ftype,type,fid')->order('ftype asc,type asc')->select();

        //备注ftype 1配送钢瓶 2瓶配件 3回库钢瓶 4回库配件
        // type 1空瓶 3重瓶  2废瓶  4普通屏
        // fid  产品 id
        $typeObject = $this->typeObject;

        $list = $list1 = '';
        $returnData = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                switch ($value['ftype']) {
                    case 1:
                        $list .= '<tr>
                                        <td>重瓶</td>
                                        <td>' . $bottleTypeObject[$value['fid']]['bottle_name'] . '</td>
                                        <td>' . $value['total'] . '</td>
                                </tr>';
                        break;
                    case 2:
                        $list .= '<tr>
                                        <td>' . $commitObject[$value['fid']]['name'] . '</td>
                                        <td>' . $commitObject[$value['fid']]['typename'] . '</td>
                                        <td>' . $value['total'] . '</td>
                                </tr>';
                        break;
                    case 3;
                        $list1 .= '<tr>
                                        <td>' . $typeObject[$value['type']] . '</td>
                                        <td>' . $bottleTypeObject[$value['fid']]['bottle_name'] . '</td>
                                        <td>' . $value['total'] . '</td>
                                </tr>';
                        break;
                    case 4:
                        $list1 .= '<tr>
                                        <td>' . $commitObject[$value['fid']]['name'] . '</td>
                                        <td>' . $commitObject[$value['fid']]['typename'] . '</td>
                                        <td>' . $value['total'] . '</td>
                                </tr>';
                        break;
                }
            }
        }
        $this->_view->assign('list', $list);
        $this->_view->assign('list1', $list1);
    }

    /**
     * 气站配送出库单
     * 
     * @备注 同时创建 门店入库确认单 和 门店回库确认单
     */
    public function deliveryAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = $shopWhere = array();
        if (!empty($param)) {
            if (!empty($param['delivery_no'])) {
                $where['delivery_no'] = $shopWhere['rq_delivery.delivery_no'] = $param['delivery_no'];
                $getParam['delivery_no'] = "delivery_no=" . $param['delivery_no'];
            }

            if (!empty($param['shop_id'])) {
                $getParam['shop_id'] = "shop_id=" . $param['shop_id'];
            }
            
            if (!empty($param['status'])){
                if ($param['status'] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $where['status'] = $shopWhere['rq_delivery.status'] = $status;
                $getParam['status'] = "status=" . $param['status'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ck_time'] = $shopWhere['rq_delivery.ck_time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        //判断是不是押运员
        $userModel = new Model('quarters');
        $leftJoin = "LEFT JOIN rq_auth_role ON rq_quarters.id = rq_auth_role.quarters_id ";
        $filed = "rq_auth_role.id,rq_quarters.quarters_id";
        $wheres = array('rq_auth_role.id' => $this->roles);
        $roleData = $userModel->join($leftJoin)->field($filed)->where($wheres)->find();
        if (!empty($roleData)) {
            if ($roleData['quarters_id'] == 2) {
                $where['guards_id'] = $shopWhere['rq_delivery.guards_id'] = $this->user_info['user_id'];
            }
        }

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        $dataDetial = array();
        if (!empty($param['shop_id'])) {
            $data['ext'] = array();

            $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;
            $pageStart = ($param['page'] - 1) * $pageSize;

            $shopWhere['rq_delivery_detail.shop_id'] = $param['shop_id'];
            
            $fillingModel = new Model('delivery');
            $field = 'rq_delivery.*,rq_delivery_detail.shop_id';
            $dataAll = $fillingModel->join('LEFT JOIN rq_delivery_detail ON rq_delivery.delivery_no = rq_delivery_detail.delivery_no')->field($field)->where($shopWhere)->group('rq_delivery.delivery_no,rq_delivery_detail.shop_id')->order('rq_delivery.ctime desc')->limit($pageStart, $pageSize)->select();
            $data['ext']['list'] = $dataAll;
            if (!empty($dataAll)) {
                foreach ($dataAll as $value) {
                    $val['shop_id'] = $value['shop_id'];
                    $val['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                    $dataDetial[$value['delivery_no']][] = $val;
                }
            }

            $datacount = $fillingModel->join('LEFT JOIN rq_delivery_detail ON rq_delivery.delivery_no = rq_delivery_detail.delivery_no')->field($field)->where($shopWhere)->group('rq_delivery.delivery_no,rq_delivery_detail.shop_id')->select();
            $data['ext']['count'] = count($datacount);
        } else {
            $deliveryModel = new FillingModel();
            $data = $deliveryModel->getDataList($param, 'delivery', $where, 'ctime desc');
            
            if (!empty($data['ext']['list'])) {
                $deliveryNoArr = array();
                foreach ($data['ext']['list'] as &$v) {
                    $v['bottle'] = json_decode($v['bottle'], true);
                    $v['products'] = json_decode($v['products'], true);

                    $deliveryNoArr[] = $v['delivery_no'];
                }

                $dWhere['delivery_no'] = array('in', $deliveryNoArr);
                $deliveryDetial = LibF::M('delivery_detail')->field('delivery_no,shop_id')->where($dWhere)->group('delivery_no,shop_id')->select();
                if (!empty($deliveryDetial)) {
                    foreach ($deliveryDetial as $value) {
                        $val['shop_id'] = $value['shop_id'];
                        $val['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                        $dataDetial[$value['delivery_no']][] = $val;
                    }
                }
            }
        }

        $this->_view->assign($data);
        $this->_view->assign('datainfo', $dataDetial);
    }

    public function adddeliveryAction() {

        if (IS_POST) {
            $license_plate = $this->getRequest()->getPost('license_plate');
            $guards = $this->getRequest()->getPost('guards');
            $bottle_list = $this->getRequest()->getPost('bottle_list');
            $product_list = $this->getRequest()->getPost('product_list');

            $gid = 0;
            $gname = '';
            if (!empty($guards)) {
                $gArr = explode('|', $guards);
                $gid = isset($gArr[0]) ? $gArr[0] : 0;
                $gname = isset($gArr[1]) ? $gArr[1] : '';
            }

            $time = time();
            //获取订单号
            $app = new App();
            $delivery_no = 'ckd' . $app->build_order_no();
            $data = array(
                'delivery_no' => $delivery_no,
                'bottle' => (!empty($bottle_list)) ? json_encode($bottle_list) : '',
                'products' => (!empty($product_list)) ? json_encode($product_list) : '',
                'license_plate' => $license_plate,
                'guards_id' => $gid,
                'guards' => $gname,
                'ck_time' => date('Y-m-d'),
                'admin_user' => $this->user_info['user_id'],
                'admin_user_name' => $this->user_info['username'],
                'ctime' => $time
            );
            $status = LibF::M('delivery')->add($data);

            if ($status) {
                $insertData = array();
                //$bottleObject = BottleModel::getBottleObject();
                //$productObject = ProductsModel::getProductsArray();

                $shopPriceObject = array();
                $shopWhere['type'] = array('in', array(1, 2));
                $shopData = LibF::M('commodity_shop')->where($shopWhere)->order('type ASC')->select();
                if (!empty($shopData)) {
                    foreach ($shopData as $shopValue) {
                        $shopPriceObject[$shopValue['shop_id']][$shopValue['norm_id']] = $shopValue;
                    }
                }

                //备注ftype 1配送钢瓶 2瓶配件
                $shopArr = $shopArrData = array();
                
                $rkbottle_list = $rkproduct_list = array();
                if (!empty($bottle_list)) {
                    foreach ($bottle_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['delivery_no'] = $delivery_no;
                        $data['ftype'] = 1;
                        $data['type'] = 0;
                        $data['shop_id'] = $v[0];
                        $data['fid'] = $v[1];
                        $data['num'] = $v[3];
                        $data['ctime'] = $time;

                        if (isset($shopArrData[$v[0]]) && !empty($shopArrData[$v[0]])) {
                            $shopDataInfo = $shopArrData[$v[0]];
                        } else {
                            $shopDataInfo = LibF::M('shop')->where(array('shop_id' => $v[0]))->find();
                            $shopArrData[$v[0]] = $shopDataInfo;
                        }
                        //获取当前门店价格
                        if (!empty($shopDataInfo)) {
                            if ($shopDataInfo['shop_type'] == 1) {
                                $data['money'] = $v[2] = (!empty($shopPriceObject[$v[0]][$v[1]]['direct_price'])) ? $shopPriceObject[$v[0]][$v[1]]['direct_price'] : $v[2]; //直营价格
                            } else {
                                $data['money'] = $v[2] = (!empty($shopPriceObject[$v[0]][$v[1]]['affiliate_price'])) ? $shopPriceObject[$v[0]][$v[1]]['affiliate_price'] : $v[2]; //加盟价格
                            }
                            $data['total'] = $v[3] * $data['money'];
                        }
                        $rkbottle_list[] = implode('|', $v);
                        $insertData[] = $data;
                        $shopArr[] = $v[0];
                    }
                }
                if (!empty($product_list)) {
                    foreach ($product_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['delivery_no'] = $delivery_no;
                        $data['ftype'] = 2;
                        $data['type'] = $v[2];
                        $data['shop_id'] = $v[0];
                        $data['fid'] = $v[1];
                        $data['num'] = $v[4];
                        $data['ctime'] = $time;
                        
                        if (isset($shopArrData[$v[0]]) && !empty($shopArrData[$v[0]])) {
                            $shopDataInfo = $shopArrData[$v[0]];
                        } else {
                            $shopDataInfo = LibF::M('shop')->where(array('shop_id' => $v[0]))->find();
                            $shopArrData[$v[0]] = $shopDataInfo;
                        }
                        //获取当前门店价格
                        if (!empty($shopDataInfo)) {
                            if ($shopDataInfo['shop_type'] == 1) {
                                $data['money'] = $v[3] = (!empty($shopPriceObject[$v[0]][$v[1]]['direct_price'])) ? $shopPriceObject[$v[0]][$v[1]]['direct_price'] : $v[3]; //直营价格
                            } else {
                                $data['money'] = $v[3] = (!empty($shopPriceObject[$v[0]][$v[1]]['affiliate_price'])) ? $shopPriceObject[$v[0]][$v[1]]['affiliate_price'] : $v[3]; //加盟价格
                            }
                            $data['total'] = $v[4] * $data['money'];
                        }

                        $rkproduct_list[] = implode('|', $v);
                        $insertData[] = $data;
                        $shopArr[] = $v[0];
                    }
                }
                if (!empty($insertData)) {
                    LibF::M('delivery_detail')->uinsertAll($insertData);

                    //同时创建门店的入库确认单  
                    $this->createConfirme($license_plate, $gname, $rkbottle_list, $rkproduct_list, $this->user_info['user_id'], $this->user_info['username'], $delivery_no);

                    //同时同步门店的回库确认单   //获取门店回库钢瓶数据
                    $shopList = !empty($shopArr) ? implode(',', array_unique($shopArr)) : '';
                    $hWhere['shop_id'] = array('in', $shopList);
                    $hWhere['hk_status'] = 0;
                    $hWhere['status'] = 1;
                    $hkArr = LibF::M('filling')->field('shop_id,hk_bottle,hk_product')->where($hWhere)->select();
                    if (!empty($hkArr)) {
                        if (!empty($hkArr)) {
                            $hkBottle = $hkproduct = array();
                            foreach ($hkArr as $hkVal) {
                                $hkData = !empty($hkVal['hk_bottle']) ? json_decode($hkVal['hk_bottle']) : array();
                                if (!empty($hkData)) {
                                    foreach ($hkData as $a) {
                                        $hkBottle[] = $hkVal['shop_id'] . '|' . $a;
                                    }
                                }
                                $hkpData = !empty($hkVal['hk_product']) ? json_decode($hkVal['hk_product']) : array();
                                if (!empty($hkpData)) {
                                    foreach ($hkpData as $p) {
                                        $hkproduct[] = $hkVal['shop_id'] . '|' . $p;
                                    }
                                }
                            }
                            $rData = $this->createConfirmBack($license_plate, $gid, $gname, $hkBottle, $hkproduct, $this->user_info['user_id'], $this->user_info['username'], $delivery_no);
                            if ($rData['status'] == 200) {
                                LibF::M('filling')->where($hWhere)->save(array('hk_status' => 1));
                            }
                        }
                    }
                }
            }
            $returnData['status'] = $status;
            echo json_encode($returnData);
            exit();
        }
    }

    public function editdeliveryAction() {
        if (IS_POST) {
            $license_plate = $this->getRequest()->getPost('license_plate');
            $guards = $this->getRequest()->getPost('guards');
            $bottle_list = $this->getRequest()->getPost('bottle_list');
            $product_list = $this->getRequest()->getPost('product_list');
            
            $delivery_no = $this->getRequest()->getPost('delivery_no');

            $gid = 0;
            $gname = '';
            if (!empty($guards)) {
                $gArr = explode('|', $guards);
                $gid = isset($gArr[0]) ? $gArr[0] : 0;
                $gname = isset($gArr[1]) ? $gArr[1] : '';
            }

            $time = time();
            $where['delivery_no'] = $delivery_no;
            $data = array(
                'delivery_no' => $delivery_no,
                'bottle' => (!empty($bottle_list)) ? json_encode($bottle_list) : '',
                'products' => (!empty($product_list)) ? json_encode($product_list) : '',
                'license_plate' => $license_plate,
                'guards_id' => $gid,
                'guards' => $gname,
                'ck_time' => date('Y-m-d'),
                'admin_user' => $this->user_info['user_id'],
                'admin_user_name' => $this->user_info['username']
            );
            $status = LibF::M('delivery')->where($where)->save($data);

            if ($status) {
                $insertData = array();
                //$bottleObject = BottleModel::getBottleObject();
                //$productObject = ProductsModel::getProductsArray();

                $shopPriceObject = array();
                $shopWhere['type'] = array('in', array(1, 2));
                $shopData = LibF::M('commodity_shop')->where($shopWhere)->order('type ASC')->select();
                if (!empty($shopData)) {
                    foreach ($shopData as $shopValue) {
                        $shopPriceObject[$shopValue['shop_id']][$shopValue['norm_id']] = $shopValue;
                    }
                }

                //备注ftype 1配送钢瓶 2瓶配件
                $shopArr = $shopArrData = array();
                
                $rkbottle_list = $rkproduct_list = array();
                if (!empty($bottle_list)) {
                    foreach ($bottle_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['delivery_no'] = $delivery_no;
                        $data['ftype'] = 1;
                        $data['type'] = 0;
                        $data['shop_id'] = $v[0];
                        $data['fid'] = $v[1];
                        $data['num'] = $v[3];
                        $data['ctime'] = $time;

                        if (isset($shopArrData[$v[0]]) && !empty($shopArrData[$v[0]])) {
                            $shopDataInfo = $shopArrData[$v[0]];
                        } else {
                            $shopDataInfo = LibF::M('shop')->where(array('shop_id' => $v[0]))->find();
                            $shopArrData[$v[0]] = $shopDataInfo;
                        }
                        //获取当前门店价格
                        if (!empty($shopDataInfo)) {
                            if ($shopDataInfo['shop_type'] == 1) {
                                $data['money'] = $v[2] = (!empty($shopPriceObject[$v[0]][$v[1]]['direct_price'])) ? $shopPriceObject[$v[0]][$v[1]]['direct_price'] : $v[2]; //直营价格
                            } else {
                                $data['money'] = $v[2] = (!empty($shopPriceObject[$v[0]][$v[1]]['affiliate_price'])) ? $shopPriceObject[$v[0]][$v[1]]['affiliate_price'] : $v[2]; //加盟价格
                            }
                            $data['total'] = $v[3] * $data['money'];
                        }
                        $rkbottle_list[] = implode('|', $v);
                        $insertData[] = $data;
                        $shopArr[] = $v[0];
                    }
                }
                if (!empty($product_list)) {
                    foreach ($product_list as $value) {
                        $data = array();
                        $v = explode('|', $value);
                        $data['delivery_no'] = $delivery_no;
                        $data['ftype'] = 2;
                        $data['type'] = $v[2];
                        $data['shop_id'] = $v[0];
                        $data['fid'] = $v[1];
                        $data['num'] = $v[4];
                        $data['ctime'] = $time;
                        
                        if (isset($shopArrData[$v[0]]) && !empty($shopArrData[$v[0]])) {
                            $shopDataInfo = $shopArrData[$v[0]];
                        } else {
                            $shopDataInfo = LibF::M('shop')->where(array('shop_id' => $v[0]))->find();
                            $shopArrData[$v[0]] = $shopDataInfo;
                        }
                        //获取当前门店价格
                        if (!empty($shopDataInfo)) {
                            if ($shopDataInfo['shop_type'] == 1) {
                                $data['money'] = $v[3] = (!empty($shopPriceObject[$v[0]][$v[1]]['direct_price'])) ? $shopPriceObject[$v[0]][$v[1]]['direct_price'] : $v[3]; //直营价格
                            } else {
                                $data['money'] = $v[3] = (!empty($shopPriceObject[$v[0]][$v[1]]['affiliate_price'])) ? $shopPriceObject[$v[0]][$v[1]]['affiliate_price'] : $v[3]; //加盟价格
                            }
                            $data['total'] = $v[4] * $data['money'];
                        }

                        $rkproduct_list[] = implode('|', $v);
                        $insertData[] = $data;
                        $shopArr[] = $v[0];
                    }
                }
                if (!empty($insertData)) {
                    $sta = LibF::M('delivery_detail')->where($where)->delete();
                    LibF::M('delivery_detail')->uinsertAll($insertData);
                }
            }
            $returnData['status'] = $status;
            echo json_encode($returnData);
            exit();
        }
    }
    
    /**
     * 气站出库单详情
     * 
     */
    public function deliveryinfoAction() {
        $sno = $this->getRequest()->getQuery('sno');
        if (empty($sno)) {
            $this->error('当前单据不存在!');
        }


        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();

        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commitObject = array();
        $fillingstocklogModel = new FillingModel();
        $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($productObject)) {
            foreach ($productObject as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $commitObject[$value['id']] = $value;
            }
        }

        $shopObject = ShopModel::getShopArray();

        $d = LibF::M('delivery')->where(array('delivery_no' => $sno))->find();
        $this->_view->assign($d);

        $file = "shop_id,ftype,fid,num,money,total";
        $data = LibF::M('delivery_detail')->field($file)->where(array('delivery_no' => $sno))->order('ftype asc')->select();

        $shopTotal = array();
        $dataTotal = LibF::M('delivery_detail')->field('sum(total) as price,shop_id')->where(array('delivery_no' => $sno))->group('shop_id')->order('ftype asc')->select();
        if (!empty($dataTotal)) {
            foreach ($dataTotal as $value) {
                $shopTotal[$value['shop_id']] = $value['price'];
            }
        }
        $this->_view->assign('shopTotal', $shopTotal);

        $returnData = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $value['price'] = 0;
                $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                if ($value['ftype'] == 1) {
                    $value['fname'] = $bottleTypeObject[$value['fid']]['bottle_name'];
                } else {
                    $value['name'] = $commitObject[$value['fid']]['name'];
                    $value['fname'] = $commitObject[$value['fid']]['typename'];
                }
                $returnData[$value['shop_id']][] = $value;
            }
        }
        $this->_view->assign('returnData', $returnData);
    }

    /**
     * 气站充装计划单
     * 
     */
    public function recordsAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (!empty($param['filling_no'])) {
                $where['filling_no'] = $param['filling_no'];
                $getParam[] = "filling_no=" . $param['filling_no'];
            }
            
            if (!empty($param['status'])){
                if ($param['status'] == 1) {
                    $status = 1;
                } else if ($param['status'] == 2) {
                    $status = 2;
                } else {
                    $status = 0;
                }
                $where['status'] = $status;
                $getParam['status'] = "status=" . $param['status'];
            }
            
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $fillingModel = new FillingModel();
        $data = $fillingModel->getDataList($param, 'filling_bottle_log', $where, 'id desc');
        if (!empty($data['ext']['list'])) {
            $btypes = BottleModel::getBottleType();
            foreach ($data['ext']['list'] as $key => &$value) {
                $list = '';
                $value['bottle'] = !empty($value['bottle']) ? json_decode($value['bottle'], true) : array();
                $value['num'] = count($value['bottle']);
                $value['list1'] = '';
                $value['list2'] = '';

                foreach ($value['bottle'] as $k => $v) {
                    if (!empty($v) && is_string($v)) {
                        $vList = explode('|', $v);
                        if ($k == 0) {
                            $value['list1'] = "<td>" . $btypes[$vList[0]] . "</td><td>" . $vList['1'] . "</td>";
                        } else {
                            $value['list2'] .= "<tr><td>" . $btypes[$vList[0]] . "</td><td>" . $vList['1'] . "</td></tr>";
                        }
                    } else {
                        if ($k == 0) {
                            $value['list1'] = "<td></td><td></td>";
                        } else {
                            $value['list2'] .= "<tr><td></td><td></td></tr>";
                        }
                    }
                }
            }
        }
        $this->_view->assign($data);
    }

    public function recordscountAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
            } else {
                $date = date('Y-m-d');
                $timeData = $this->timeInfo($param['type'], $date);
                $where['time'] = array(array('egt', $timeData['start_time']), array('elt', $timeData['end_time']), 'AND');
            }

            unset($param['submit']);
        } else {
            $where['time'] = array('egt', date('Y-m-d'));
            $param['type'] = 1;
        }
        $this->_view->assign('param',$param);
        
        $dataArray = array();
        $where['status'] = 2;
        $data = LibF::M('filling_bottle_log')->where($where)->select();
        if (!empty($data)) {
            $btypes = BottleModel::getBottleType();
            foreach ($data as $value) {
                $bottleArr = !empty($value['bottle']) ? json_decode($value['bottle'], true) : array();
                if (!empty($bottleArr)) {
                    foreach ($bottleArr as $k => $v) {
                        if (!empty($v) && is_string($v)) {
                            $vList = explode('|', $v);
                            $val['id'] = $vList[0];
                            $val['title'] = isset($btypes[$val['id']]) ? $btypes[$val['id']] : '';
                            $val['num'] = $vList[1];

                            if (isset($dataArray[$val['id']])) {
                                $dataArray[$val['id']]['num'] += $vList[1];
                            } else {
                                $dataArray[$val['id']]= $val;
                            }
                        }
                    }
                }
            }
        }
        $this->_view->assign('dataArray', $dataArray);

        $timeKey = $this->timekey;
        $this->_view->assign('timeKey', $timeKey);
    }

    public function addrecordsAction() {
        if (IS_POST) {
            $bottle_list = $this->getRequest()->getPost('bottle_list');
            $comment = $this->getRequest()->getPost('comment');

            //获取储罐编号
            $app = new App();
            $orderSn = $app->build_order_no();
            $data['filling_no'] = 'czjhd' . $orderSn;
            $data['time'] = date('Y-m-d');
            $data['ctime'] = time();

            $num = 0;
            $insertData = array();
            $btypes = BottleModel::getBottleType();
            if (!empty($bottle_list)) {
                foreach ($bottle_list as $value) {
                    $v = explode('|', $value);
                    $info['filling_no'] = $data['filling_no'];
                    $info['type'] = $v[0];
                    $info['num'] = $v[1];
                    $info['name'] = isset($btypes[$v[0]]) ? $btypes[$v[0]] : '';
                    $info['time'] = $data['time'];
                    $info['ctime'] = $data['ctime'];
                    $insertData[] = $info;

                    $num += $info['num'];
                }
            }

            $data['admin_id'] = $this->user_info['user_id'];
            $data['admin_user'] = $this->user_info['username'];
            $data['bottle'] = json_encode($bottle_list);
            $data['num'] = $num;
            $data['type'] = 0;

            $fillModel = new FillingbottlelogModel();
            $returnData = $fillModel->add($data, $id);
            if (!$returnData['status'] == 200) {
                $this->error($returnData['msg']);
            }

            if (!empty($insertData)) {
                LibF::M('filling_bottle_info')->uinsertAll($insertData);
            }

            echo json_encode($returnData);
            exit();
        }
    }

    public function pfrecordsAction() {
        if (IS_POST) {
            $fno = $this->getRequest()->getPost('fno');
            $comment = $this->getRequest()->getPost('comment');
            $cz_user_name = $this->getRequest()->getPost('cz_user_name');
            $returnData['status'] = 0;
            if (!empty($cz_user_name) && !empty($fno)) {
                $uArr = explode('|', $cz_user_name);

                $data['cz_user_id'] = isset($uArr[0]) ? $uArr[0] : 0;
                $data['cz_user_name'] = isset($uArr[1]) ? $uArr[1] : 0;
                $data['comment'] = $comment;
                $data['status'] = 1;
                $returnData['status'] = LibF::M('filling_bottle_log')->where(array('filling_no' => $fno))->save($data);
            }

            echo json_encode($returnData);
            exit();
        }
    }

    /**
     * 同步配送计划为充装计划
     * 
     */
    public function tbrecordsAction() {
        $where = " rq_filling.status = 1 AND rq_filling_detail.ftype = 1 AND rq_filling.tb_status = 0 ";
        $field = "rq_filling.filling_no,rq_filling.shop_id,rq_filling_detail.fid,rq_filling_detail.num";

        $fno = array();
        $filling = new Model('filling');
        $datainfo = $filling->join('LEFT JOIN rq_filling_detail ON rq_filling.filling_no = rq_filling_detail.filling_no')->field($field)->where($where)->select();
        if (!empty($datainfo)) {
            $bottleData = $insertData = array();
            $btypes = BottleModel::getBottleType();

            $now = date('Y-m-d');
            $time = time();

            $app = new App();
            $orderSn = $app->build_order_no();
            $data['filling_no'] = 'czjhd' . $orderSn;

            $bottleTypeArr = array();
            foreach ($datainfo as $value) {
                $fno[] = "'" . $value['filling_no'] . "'";
                if ($value['fid'] > 0 && $value['num'] > 0) {
                    if (isset($bottleTypeArr[$value['fid']])) {
                        $bottleTypeArr[$value['fid']] += $value['num'];
                    } else {
                        $bottleTypeArr[$value['fid']] = $value['num'];
                    }
                    $info['filling_no'] = $data['filling_no'];
                    $info['type'] = $value['fid'];
                    $info['num'] = $value['num'];
                    $info['name'] = $btypes[$value['fid']];
                    $info['time'] = !empty($value['time']) ? $value['time'] : $now;
                    $info['ctime'] = !empty($value['ctime']) ? $value['ctime'] : $time;
                    $insertData[] = $info;
                }
            }

            if (!empty($bottleTypeArr)) {
                foreach ($bottleTypeArr as $k => $v) {
                    $bottleData[] = $k . "|" . $v;
                }

                $data['admin_id'] = $this->user_info['user_id'];
                $data['admin_user'] = $this->user_info['username'];
                $data['time'] = $now;
                $data['type'] = 0;
                $data['num'] = 0;
                $data['bottle'] = json_encode($bottleData);
                $data['ctime'] = time();

                $fillModel = new FillingbottlelogModel();
                $returnData = $fillModel->add($data, $id);
                if (!$returnData['status'] == 200) {
                    $this->error($returnData['msg']);
                }

                if (!empty($insertData)) {
                    LibF::M('filling_bottle_info')->uinsertAll($insertData);

                    $fnolist = implode(',', $fno);
                    $wherelist = "filling_no IN (" . $fnolist . ")";
                    LibF::M('filling')->where($wherelist)->save(array('tb_status' => 1));
                }
            }
        } else {
            $returnData['status'] = -1;
        }
        echo json_encode($returnData);
        exit();
    }

    /**
     * 门店入库确认单
     * 
     */
    public function confirmeAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array();
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
            }
            
            if (!empty($param['shop_id'])) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }

            if (!empty($param['status'])){
                if ($param['status'] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $where['status'] = $status;
                $getParam['status'] = "status=" . $param['status'];
            }
            
            if (!empty($param['confirme_no'])) {
                $where['confirme_no'] = $param['confirme_no'];
                $getParam[] = "confirme_no=" . $param['confirme_no'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $where['shop_id'] = $this->shop_id;
                $param['shop_id'] = $this->shop_id;
            }
        }

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }
        
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $param['pageSize'] = $this->getRequest()->getQuery('pageSize', 15);

        $receiptModel = new FillingModel();
        $data = $receiptModel->getDataList($param, 'confirme', $where, 'ctime desc');
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    public function confirmeinfoAction() {
        $sno = $this->getRequest()->getQuery('sno');
        $shop_id = $this->getRequest()->getQuery('shop_id');
        if (empty($sno)) {
            $this->error('当前单据不存在!');
        }

        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();

        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $commitObject = array();
        $fillingstocklogModel = new FillingModel();
        $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($productObject)) {
            foreach ($productObject as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $commitObject[$value['id']] = $value;
            }
        }

        $shopObject = ShopModel::getShopArray();

        $where = array('confirme_no' => $sno, 'shop_id' => $shop_id);
        $d = LibF::M('confirme')->where($where)->find();
        $this->_view->assign($d);

        $file = "shop_id,ftype,type,num,typename,total";
        $data = LibF::M('confirme_detail')->field($file)->where($where)->order('ftype asc')->select();

        $totalMoney = 0;
        $shopTotal = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $totalMoney += $value['total'];
            }
        }
        $this->_view->assign('totalMoney', $totalMoney);
        $this->_view->assign('sno', $sno);

        $returnData = array();
        if (!empty($data)) {
            foreach ($data as &$value) {
                $value['price'] = 0;
                $value['shop_name'] = $shopObject[$value['shop_id']]['shop_name'];
                if ($value['ftype'] == 1) {
                    $value['fname'] = $bottleTypeObject[$value['type']]['bottle_name'];
                } else {
                    $value['name'] = $commitObject[$value['type']]['name'];
                    $value['fname'] = $commitObject[$value['type']]['typename'];
                }
            }
        }

        $this->_view->assign('returnData', $data);
        $this->_view->assign('count', count($data));
    }

    public function confirmebottleAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        $where = '';
        if (!empty($param)) {
            if (!empty($param['sn'])) {
                $where['sn'] = $param['sn'];
                $getParam[] = "sn=" . $param['sn'];
            }
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;

                $where['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else if (!empty($param['shop_id'])) {
                $where['shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }
            if (!empty($param['xinpian'])) {
                $where['xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }
            if (!empty($param['number'])) {
                $where['number'] = $param['number'];
                $getParam[] = "number=" . $param['number'];
            }

            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['pageSize'] = $this->getRequest()->getQuery('pageSize', 15);

        $receiptModel = new FillingModel();
        $data = $receiptModel->getDataList($param, 'store_inventory', $where, 'id desc');
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    /**
     * 获取当前充装记录显示
     * 
     */
    public function czlistAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['number'])) {
                $where['number'] = $param['number'];
                $getParam[] = "number=" . $param['number'];
            }
            if (!empty($param['xinpian'])) {
                $where['xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['czsj'] = array(array('egt', $param['time_start']), array('elt', $param['time_end'] . ' 23:59:59'), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $bottletbModel = new FillingModel();
        $bottletbData = $bottletbModel->getDataList($param, 'bottle_tb', $where, 'czsj desc');

        $btypes = BottleModel::getBottleType();
        $this->_view->assign('btypes', $btypes);

        $this->_view->assign($bottletbData);
        $this->_view->assign('param', $param);
    }

    /**
     * 获取档期钢瓶初始化记录
     * 
     */
    public function czbottleAction() {

        $where = array();
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['number'])) {
                $where['number'] = $param['number'];
                $getParam[] = "number=" . $param['number'];
            }
            if (!empty($param['xinpian'])) {
                $where['xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['ctime'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);

        $bottletbModel = new FillingModel();
        $bottletbData = $bottletbModel->getDataList($param, 'bottle_initialization', $where, 'ctime desc');
        $this->_view->assign($bottletbData);

        $supplierModel = new SupplierModel();
        $supplierData = $supplierModel->getSupplierObject(); //获取供应商
        $this->_view->assign('supplierData', $supplierData);

        $btypes = BottleModel::getBottleType();
        $this->_view->assign('btypes', $btypes);
    }

    /**
     * 配送回库确认单
     * 
     *
     */
    public function confirmbackAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = $shopWhere = array();
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
            }
            if (!empty($param['shop_id'])) {
                $where['shop_id'] = $shopWhere['rq_confirme_store_detail.shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }
            
            if (!empty($param['status'])){
                if ($param['status'] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $where['status'] = $shopWhere['rq_confirme_store.status'] = $status;
                $getParam['status'] = "status=" . $param['status'];
            }

            if (!empty($param['confirme_no'])) {
                $where['confirme_no'] = $shopWhere['rq_confirme_store.confirme_no'] = $param['confirme_no'];
                $getParam[] = "confirme_no=" . $param['confirme_no'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time'] = $shopWhere['rq_confirme_store.time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $where['shop_id'] = $this->shop_id;
                $param['shop_id'] = $this->shop_id;
            }
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['pageSize'] = $this->getRequest()->getQuery('pageSize', 15);
        $this->_view->assign('param', $param);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $dataDetial = array();
        if (!empty($param['shop_id'])) {
            $data['ext'] = array();

            $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;
            $pageStart = ($param['page'] - 1) * $pageSize;

            $shopWhere['rq_confirme_store_detail.shop_id'] = $param['shop_id'];

            $fillingModel = new Model('confirme_store');
            $field = 'rq_confirme_store.*,rq_confirme_store_detail.shop_id';
            $dataAll = $fillingModel->join('LEFT JOIN rq_confirme_store_detail ON rq_confirme_store.confirme_no = rq_confirme_store_detail.confirme_no')->field($field)->where($shopWhere)->group('rq_confirme_store.confirme_no,rq_confirme_store_detail.shop_id')->order('rq_confirme_store.id desc')->limit($pageStart, $pageSize)->select();
            $data['ext']['list'] = $dataAll;
            if (!empty($dataAll)) {
                foreach ($dataAll as $value) {
                    $val['shop_id'] = $value['shop_id'];
                    $val['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                    $dataDetial[$value['confirme_no']][] = $val;
                }
            }

            $datacount = $fillingModel->join('LEFT JOIN rq_confirme_store_detail ON rq_confirme_store.confirme_no = rq_confirme_store_detail.confirme_no')->field($field)->where($shopWhere)->group('rq_confirme_store.confirme_no,rq_confirme_store_detail.shop_id')->select();
            $data['ext']['count'] = count($datacount);
        } else {
            $receiptModel = new FillingModel();
            $data = $receiptModel->getDataList($param, 'confirme_store', $where, 'id desc');
            if (!empty($data['ext']['list'])) {
                $confirmeSnArr = array();
                foreach ($data['ext']['list'] as &$v) {
                    $confirmeSnArr[] = $v['confirme_no'];
                }

                $dWhere['confirme_no'] = array('in', $confirmeSnArr);
                $confirmeDetial = LibF::M('confirme_store_detail')->field('confirme_no,shop_id')->where($dWhere)->group('confirme_no,shop_id')->select();
                if (!empty($confirmeDetial)) {
                    foreach ($confirmeDetial as $value) {
                        $val['shop_id'] = $value['shop_id'];
                        $val['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                        $dataDetial[$value['confirme_no']][] = $val;
                    }
                }
            }
        }
        
        $this->_view->assign($data);
        $this->_view->assign('datainfo', $dataDetial);
    }

    public function addconfirmbackAction() {

        if (IS_POST) {
            $license_plate = $this->getRequest()->getPost('license_plate');
            $guards = $this->getRequest()->getPost('guards');
            $bottle_list = $this->getRequest()->getPost('bottle_list');

            $returnData = $this->createConfirmBack($license_plate, 0, $guards, $bottle_list, '', $this->user_info['user_id'], $this->user_info['username']);
            if ($returnData['status'] != 200) {
                $this->error($addRes['msg']);
            }
            echo json_encode($returnData);
            exit();
        }
    }
    
    public function editconfirmbackAction() {
        if (IS_POST){
            $license_plate = $this->getRequest()->getPost('license_plate');
            $guards = $this->getRequest()->getPost('guards');
            $bottle_list = $this->getRequest()->getPost('bottle_list');
            $confirm_no = $this->getRequest()->getPost('confirm_no');

            $returnData = $this->editConfirmBack($license_plate, 0, $guards, $bottle_list, '', $this->user_info['user_id'], $this->user_info['username'], $confirm_no);
            if ($returnData['status'] != 200) {
                $this->error($addRes['msg']);
            }
            echo json_encode($returnData);
            exit();
        }
    }

    /**
     * 气站回库确认单详情
     */
    public function confirmbackinfoAction() {

        $sno = $this->getRequest()->getQuery('sno');
        if (empty($sno)) {
            $this->error('当前单据不存在');
        }

        $list = '';
        $where['confirme_no'] = $sno;
        $data = LibF::M('confirme_store')->where($where)->find();
        $dataInfo = LibF::M('confirme_store_detail')->where($where)->order('ftype asc,type asc')->select();
        if (!empty($dataInfo)) {
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();

            $commitObject = array();
            $fillingstocklogModel = new FillingModel();
            $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
            if (!empty($productObject)) {
                foreach ($productObject as &$value) {
                    $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    $commitObject[$value['id']] = $value;
                }
            }

            $newData = array();
            foreach ($dataInfo as $value) {
                $newData[$value['ftype']][] = $value;
            }
            $typeObject = $this->typeObject;
            if (!empty($newData)) {
                $shopObject = ShopModel::getShopArray(); //获取门店
                foreach ($newData as $key => $val) {
                    $num = count($val) + 1;
                    if ($key == 0) {
                        //配件
                        $pName = '配件';
                    } else {
                        $pName = $typeObject[$key];
                    }

                    $list .= '<tr class="h60">
                                    <td rowspan="' . $num . '">' . $pName . '</td>
                                    <td>规格</td>
                                    <td>数量</td>
                                    <td>所属门店</td>
                                    <td colspan="2">合计</td>
                            </tr>';
                    foreach ($val as $v) {
                        if ($v['ftype'] == 0) {
                            $tName = isset($commitObject[$v['type']]) ? $commitObject[$v['type']]['name'] : '';
                            $tName .= isset($commitObject[$v['type']]) ? '|' . $commitObject[$v['type']]['typename'] : '';
                        } else {
                            $tName = $v['typename'];
                        }

                        $list .= '<tr>
                                <td>' . $tName . '</td>
                                <td>' . $v['num'] . '</td>
                                <td>' . $shopObject[$v['shop_id']]['shop_name'] . '</td>
                                <td colspan="2">' . $v['num'] . '</td>
                        </tr>';
                    }
                }
            }
        }

        $this->_view->assign('data', $data);
        $this->_view->assign('list', $list);
    }

    /**
     * 门店回库确认单钢瓶详情
     */
    public function confirmbackbottleAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $bottleTypeModel = new BottletypeModel();
        $bottleTypeObject = $bottleTypeModel->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        $where = '';
        if (!empty($param)) {
            if (!empty($param['sn'])) {
                $where['sn'] = $param['sn'];
                $getParam[] = "sn=" . $param['sn'];
            }
            if (!empty($param['xinpian'])) {
                $where['xinpian'] = $param['xinpian'];
                $getParam[] = "xinpian=" . $param['xinpian'];
            }
            if (!empty($param['number'])) {
                $where['number'] = $param['number'];
                $getParam[] = "number=" . $param['number'];
            }

            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['pageSize'] = $this->getRequest()->getQuery('pageSize', 15);

        $receiptModel = new FillingModel();
        $data = $receiptModel->getDataList($param, 'qz_inventory', $where, 'id desc');
        $this->_view->assign($data);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
    }

    /**
     * 回库确认单（押运员使用）
     */
    public function confirmguardsAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = $shopWhere = array();
        if (!empty($param)) {
            if (empty($param['shop_id']) && $this->shop_id) {
                $param['shop_id'] = $this->shop_id;
            }
            if (!empty($param['shop_id'])) {
                $where['shop_id'] = $shopWhere['rq_confirme_store_detail.shop_id'] = $param['shop_id'];
                $getParam[] = "shop_id=" . $param['shop_id'];
            }
            
            if (!empty($param['status'])){
                if ($param['status'] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $where['status'] = $shopWhere['rq_confirme_store.status'] = $status;
                $getParam['status'] = "status=" . $param['status'];
            }

            if (!empty($param['confirme_no'])) {
                $where['confirme_no'] = $shopWhere['rq_confirme_store.confirme_no'] = $param['confirme_no'];
                $getParam[] = "confirme_no=" . $param['confirme_no'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time'] = $shopWhere['rq_confirme_store.time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id) {
                $where['shop_id'] = $this->shop_id;
                $param['shop_id'] = $this->shop_id;
            }
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $param['pageSize'] = $this->getRequest()->getQuery('pageSize', 15);
        $this->_view->assign('param', $param);

        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        
        $dataDetial = array();
        if (!empty($param['shop_id'])) {
            $data['ext'] = array();

            $pageSize = isset($params['page_size']) ? $params['page_size'] : 15;
            $pageStart = ($param['page'] - 1) * $pageSize;

            $shopWhere['rq_confirme_store_detail.shop_id'] = $param['shop_id'];

            $fillingModel = new Model('confirme_store');
            $field = 'rq_confirme_store.*,rq_confirme_store_detail.shop_id';
            $dataAll = $fillingModel->join('LEFT JOIN rq_confirme_store_detail ON rq_confirme_store.confirme_no = rq_confirme_store_detail.confirme_no')->field($field)->where($shopWhere)->group('rq_confirme_store.confirme_no,rq_confirme_store_detail.shop_id')->order('rq_confirme_store.id desc')->limit($pageStart, $pageSize)->select();
            $data['ext']['list'] = $dataAll;
            if (!empty($dataAll)) {
                foreach ($dataAll as $value) {
                    $val['shop_id'] = $value['shop_id'];
                    $val['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                    $dataDetial[$value['confirme_no']][] = $val;
                }
            }

            $datacount = $fillingModel->join('LEFT JOIN rq_confirme_store_detail ON rq_confirme_store.confirme_no = rq_confirme_store_detail.confirme_no')->field($field)->where($shopWhere)->group('rq_confirme_store.confirme_no,rq_confirme_store_detail.shop_id')->select();
            $data['ext']['count'] = count($datacount);
        } else {
            $receiptModel = new FillingModel();
            $data = $receiptModel->getDataList($param, 'confirme_store', $where, 'id desc');
            if (!empty($data['ext']['list'])) {
                $confirmeSnArr = array();
                foreach ($data['ext']['list'] as &$v) {
                    $confirmeSnArr[] = $v['confirme_no'];
                }

                $dWhere['confirme_no'] = array('in', $confirmeSnArr);
                $confirmeDetial = LibF::M('confirme_store_detail')->field('confirme_no,shop_id')->where($dWhere)->group('confirme_no,shop_id')->select();
                if (!empty($confirmeDetial)) {
                    foreach ($confirmeDetial as $value) {
                        $val['shop_id'] = $value['shop_id'];
                        $val['shop_name'] = isset($shopObject[$value['shop_id']]) ? $shopObject[$value['shop_id']]['shop_name'] : '';
                        $dataDetial[$value['confirme_no']][] = $val;
                    }
                }
            }
        }
        
        $this->_view->assign($data);
        $this->_view->assign('datainfo', $dataDetial);
    }

    public function confirmguardsinfoAction() {
        $sno = $this->getRequest()->getQuery('sno');
        if (empty($sno)) {
            $this->error('当前单据不存在');
        }

        $list = '';
        $where['confirme_no'] = $sno;
        $data = LibF::M('confirme_store')->where($where)->find();
        $dataInfo = LibF::M('confirme_store_detail')->where($where)->order('ftype asc,type asc')->select();
        if (!empty($dataInfo)) {
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();

            $commitObject = array();
            $fillingstocklogModel = new FillingModel();
            $productObject = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
            if (!empty($productObject)) {
                foreach ($productObject as &$value) {
                    $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    $commitObject[$value['id']] = $value;
                }
            }

            $newData = $mdData = array();
            foreach ($dataInfo as $value) {
                $newData[$value['shop_id']][] = $value;
                $mdData[] = $value['shop_id'];
            }
            $typeObject = $this->typeObject;
            if (!empty($newData)) {
                $shopObject = ShopModel::getShopArray(); //获取门店

                $mdshenhe = array();
                $mdWHere['confirme_no'] = $sno;
                $mdWHere['shop_id'] = array('in', array_unique($mdData));
                $mdshData = LibF::M('confirme_store_detail_sp')->where($mdWHere)->select();
                if (!empty($mdshData)) {
                    foreach ($mdshData as $mVal) {
                        $mdshenhe[$mVal['shop_id']] = $mVal['status'];
                    }
                }
                foreach ($newData as $key => $val) {
                    $status = isset($mdshenhe[$key]) ? $mdshenhe[$key] : 0;

                    if ($status == 0) {
                        $shenlist = '<a href="javascript:;" class="yqsh" cno="' . $sno . '" shop_id="' . $key . '">未审核</a>';
                    } else {
                        $shenlist = '<a href="javascript:;">已审核</a>';
                    }

                    $num = count($val) + 1;
                    $pName = $shopObject[$key]['shop_name'];
                    $list .= '<tr class="h60">
                                    <td rowspan="' . $num . '">' . $pName . '</td>
                                    <td>类型</td>    
                                    <td>规格</td>
                                    <td>数量</td>
                                    <td colspan="3" rowspan="' . $num . '">' . $shenlist . '</td>
                            </tr>';
                    foreach ($val as $v) {
                        if ($v['ftype'] == 0) {
                            $lName = isset($commitObject[$v['type']]) ? $commitObject[$v['type']]['name'] : '';
                            $tName = isset($commitObject[$v['type']]) ? $commitObject[$v['type']]['typename'] : '';
                        } else {
                            $tName = $v['typename'];
                            $lName = $typeObject[$v['ftype']];
                        }

                        $list .= '<tr>
                                <td>' . $lName . '</td>
                                <td>' . $tName . '</td>
                                <td>' . $v['num'] . '</td>
                        </tr>';
                    }
                }
            }
        }

        $this->_view->assign('data', $data);
        $this->_view->assign('list', $list);
    }

    /**
     * 入库确认单创建方法
     * 
     * @param $license_plate 车牌号
     * @param $guards 押运员
     * @param $bottle_list 钢瓶数据 0门店 1钢瓶类型 2钢瓶规格 3数量
     * @param $user_id 
     * @param $user_name
     * @param $type 1钢瓶 2配件
     */
    protected function createConfirme($license_plate, $guards, $bottle_list, $product_list, $user_id, $user_name, $confirm_no = '') {
        if (empty($license_plate) || empty($guards) || empty($user_id) || empty($user_name))
            return false;

        $returnData = array();

        $data = array();
        if (empty($confirm_no)) {
            $app = new App();
            $data['confirme_no'] = 'qrd' . $app->build_order_no();
        } else {
            $data['confirme_no'] = $confirm_no;
        }
        $data['license_plate'] = $license_plate;
        $data['guards'] = $guards;
        $data['admin_id'] = $user_id;
        $data['admin_user'] = $user_name;
        $data['time'] = date('Y-m-d');
        $data['ctime'] = time();

        $shopShow = array();
        $insertData = array();

        if (!empty($bottle_list)) {
            $btypes = BottleModel::getBottleType();
            $shopArr = array();
            foreach ($bottle_list as $value) {
                $v = explode('|', $value);
                $info['confirme_no'] = $data['confirme_no'];
                $info['shop_id'] = $v[0];
                $info['ftype'] = 1;
                $info['type'] = $v[1];
                $info['typename'] = $btypes[$v[1]];
                $info['num'] = $v[3];
                $info['total'] = $v[2] * $v[3];
                $info['ctime'] = $data['ctime'];
                $insertData[] = $info;

                $shopArr[] = $v[0];
            }

            $shopData = array_unique($shopArr);
            $cArr = array();
            foreach ($shopData as $value) {
                $data['shop_id'] = $value;
                $cArr[] = $data;
                $shopShow[$data['shop_id']] = 1;
            }
        }
        if (!empty($product_list)) {
            $shopArr = array();

            $productObject = ProductsModel::getProductsArray();
            foreach ($product_list as $value) {
                $v = explode('|', $value);
                $info['confirme_no'] = $data['confirme_no'];
                $info['shop_id'] = $v[0];
                $info['ftype'] = 2;
                $info['type'] = $v[1];
                $info['typename'] = $productObject[$v[1]]['products_name'];
                $info['num'] = $v[4];
                $info['total'] = $v[3] * $v[4];
                $info['ctime'] = $data['ctime'];
                $insertData[] = $info;

                $shopArr[] = $v[0];
            }

            $shopData = array_unique($shopArr);
            foreach ($shopData as $value) {
                $data['shop_id'] = $value;
                if (!isset($shopShow[$value]))
                    $cArr[] = $data;
            }
        }

        if (!empty($cArr)) {
            $status = LibF::M('confirme')->uinsertAll($cArr);
            $returnData['status'] = 200;
            if (!empty($insertData)) {
                LibF::M('confirme_detail')->uinsertAll($insertData);
            }
        }

        return $returnData;
    }

    /**
     * 回库确认单创建方法
     * 
     * @param $license_plate 车牌号
     * @param $guards 押运员
     * @param $bottle_list 钢瓶数据 0门店 1钢瓶类型 2钢瓶规格 3数量
     * @param $user_id 
     * @param $user_name
     */
    protected function createConfirmBack($license_plate, $guards_id = 0, $guards, $bottle_list, $product_list, $user_id, $user_name, $confirm_no = '') {

        if (empty($license_plate) || empty($guards) || empty($user_id) || empty($user_name))
            return false;

        if (empty($bottle_list) && empty($product_list))
            return false;

        $returnData = array();
        $data = array();
        if (empty($confirm_no)) {
            $app = new App();
            $data['confirme_no'] = 'qrd' . $app->build_order_no();
        } else {
            $data['confirme_no'] = $confirm_no;
        }
        $data['license_plate'] = $license_plate;
        $data['guards_id'] = $guards_id;
        $data['guards'] = $guards;
        if (!empty($bottle_list))
            $data['bottle'] = json_encode($bottle_list);
        if (!empty($product_list))
            $data['products'] = json_encode($product_list);
        $data['admin_id'] = $user_id;
        $data['admin_user'] = $user_name;
        $data['time'] = date('Y-m-d');
        $data['ctime'] = time();
        $status = LibF::M('confirme_store')->add($data);

        $insertData = array();
        if ($status) {
            $shopArr = array();
            $returnData['status'] = 200;
            $btypes = BottleModel::getBottleType();
            if (!empty($bottle_list)) {
                foreach ($bottle_list as $value) {
                    $v = explode('|', $value);
                    $info['confirme_no'] = $data['confirme_no'];
                    $info['shop_id'] = $v[0];
                    $info['ftype'] = $v[1];
                    $info['type'] = $v[2];
                    $info['typename'] = $btypes[$v[2]];
                    $info['num'] = $v[3];
                    $info['ctime'] = $data['ctime'];
                    $insertData[] = $info;

                    $shopArr[] = $v[0];
                }
            }
            if (!empty($product_list)) {
                $productObject = ProductsModel::getProductsArray();
                foreach ($product_list as $pVal) {
                    $p = explode('|', $pVal);
                    $info['confirme_no'] = $data['confirme_no'];
                    $info['shop_id'] = $p[0];
                    $info['ftype'] = 0;
                    $info['type'] = $p[1];
                    $info['typename'] = $productObject[$p[1]]['products_name'];
                    $info['num'] = $p[3];
                    $info['ctime'] = $data['ctime'];
                    $insertData[] = $info;

                    $shopArr[] = $p[0];
                }
            }

            if (!empty($insertData)) {
                LibF::M('confirme_store_detail')->uinsertAll($insertData);
            }
        }
        return $returnData;
    }
    
    protected function editConfirmBack($license_plate, $guards_id = 0, $guards, $bottle_list, $product_list, $user_id, $user_name, $confirm_no = '') {
        if (empty($license_plate) || empty($guards) || empty($user_id) || empty($user_name) || empty($confirm_no))
            return false;

        if (empty($bottle_list) && empty($product_list))
            return false;
        
        $returnData = $data = $insertData = array();
        $where['confirme_no'] = $confirm_no;

        $data['license_plate'] = $license_plate;
        $data['guards_id'] = $guards_id;
        $data['guards'] = $guards;
        if (!empty($bottle_list))
            $data['bottle'] = json_encode($bottle_list);
        if (!empty($product_list))
            $data['products'] = json_encode($product_list);
        $data['admin_id'] = $user_id;
        $data['admin_user'] = $user_name;
        $status = LibF::M('confirme_store')->where($where)->save($data);
        if ($status) {
            $shopArr = array();
            $returnData['status'] = 200;
            $time = time();

            $btypes = BottleModel::getBottleType();
            if (!empty($bottle_list)) {
                foreach ($bottle_list as $value) {
                    $v = explode('|', $value);
                    $info['confirme_no'] = $confirm_no;
                    $info['shop_id'] = $v[0];
                    $info['ftype'] = $v[1];
                    $info['type'] = $v[2];
                    $info['typename'] = $btypes[$v[2]];
                    $info['num'] = $v[3];
                    $info['ctime'] = $time;
                    $insertData[] = $info;

                    $shopArr[] = $v[0];
                }
            }
            if (!empty($product_list)) {
                $productObject = ProductsModel::getProductsArray();
                foreach ($product_list as $pVal) {
                    $p = explode('|', $pVal);
                    $info['confirme_no'] = $confirm_no;
                    $info['shop_id'] = $p[0];
                    $info['ftype'] = 0;
                    $info['type'] = $p[1];
                    $info['typename'] = $productObject[$p[1]]['products_name'];
                    $info['num'] = $p[3];
                    $info['ctime'] = $time;
                    $insertData[] = $info;

                    $shopArr[] = $p[0];
                }
            }
            
            if (!empty($insertData)) {
                $status = LibF::M('confirme_store_detail')->where($where)->delete();
                LibF::M('confirme_store_detail')->uinsertAll($insertData);    
            }
        }
        return $returnData;
    }

    /**
     * 根据类型获取时间节点
     * 
     * @param $type
     */
    protected function timeInfo($search_type = 0, $date) {

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
            case 1:
            default :
                $param['start_time'] = $date;
                $param['end_time'] = $date . ' 23:59:59';
                break;
        }
        return $param;
    }

}
