<?php

/**
 * 气站库存出入库
 */
class StockController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;
    protected $typeObject;

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
        
        //$this->typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶',10 => '新瓶');
        $this->typeObject = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶', 5 => '待检瓶', 6 =>'报废瓶');
    }

    //配件库存
    public function productAction() {

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array('gtype' => 2);
        if (!empty($param)) {
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['goods_id'])) {
                $where['goods_id'] = $param['goods_id'];
                $getParam[] = "goods_id=" . $param['goods_id'];
            }
            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['time'] = array(array('egt', $param['start_time']), array('elt', $param['end_time'] . " 23:59:59"), 'AND');

                $getParam[] = "start_time=" . $param['start_time'];
                $getParam[] = "end_time=" . $param['end_time'];
            }

            if (!empty($param['pagetype'])) {
                $getParam[] = "pagetype=".$param['pagetype'];
            }

            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        if ($param['pagetype'] == 'log') { //只显示出入口的记录
            $fillingstocklogModel = new FillingModel();
            $returndata = $fillingstocklogModel->getDataList($param, 'filling_stock_log', $where, 'ctime desc');
            $this->_view->assign($returndata);

            //配件类型
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();

            $productsObject = array();
            $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
            if (!empty($commdityData)) {
                foreach ($commdityData as &$value) {
                    $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                    $productsObject[$value['id']] = $value;
                }
            }
            $this->_view->assign('productsObject', $productsObject);
            $this->_view->assign('shopObject', ShopModel::getShopArray()); //获取门店
            
            $this->_view->display('stock/productlog.phtml');
        } else if ($param['pagetype'] == 'loglist') {  //显示另外一种模式
            $returnData = array();
            $list = '';
            $data = LibF::M('filling_stock_log')->field('goods_name,goods_type,sum(goods_num) as num,time')->where($where)->group('time,goods_type')->order(' goods_type asc,time DESC')->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $returnData[$value['time']][] = $value;
                }
                foreach ($returnData as $k => $val) {
                    $num = count($val);
                    foreach ($val as $ii => $v) {
                        $list .= '<tr>';
                        if ($ii == 0) {
                            $list .= '<td rowspan="' . $num . '">' . $k . '</td>';
                        }
                        $list .= '<td>' . $v['goods_name'] . '</td>
                                <td>' . $v['num'] . '</td>';
                        if ($ii == 0) {
                            $list .= '<td rowspan="' . $num . '"></td>';
                        }
                        $list .= '</tr>';
                    }
                }
            }
            $this->_view->assign('returnData', $returnData);
            $this->_view->assign('list', $list);
            $this->_view->display('stock/productlist.phtml');
        } else {
            //获取当前钢瓶类型库存数量
            $dataTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,fs_type_id,fs_name')->group('fs_type_id')->where(array('type' => 2))->select();
            $this->_view->assign('dataTotal', $dataTotal);
        }
    }

    //门店配件库存
    public function sproductAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where['gtype'] = 2;
        
        if (!empty($param)) {
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['goods_id'])) {
                $where['goods_id'] = $param['goods_id'];
                $getParam[] = "goods_id=" . $param['goods_id'];
            }
            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['time'] = array(array('egt', $param['start_time']), array('elt', $param['end_time']), 'AND');

                $getParam[] = "start_time=" . $param['start_time'];
                $getParam[] = "end_time=" . $param['end_time'];
            }
            
            if (!empty($param['pagetype'])) {
                $getParam[] = "pagetype=".$param['pagetype'];
            }
            
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $w['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id){
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
            }
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }
        
        //配件类型
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();

        $productsObject = array();
        $fillingstocklogModel = new FillingModel();
        $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($commdityData)) {
            foreach ($commdityData as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $productsObject[$value['id']] = $value;
            }
        }
        $this->_view->assign('productsObject', $productsObject);
        
        //获取当前配件库存数量
        $w['type'] = 2;
        $dataTotal = LibF::M('filling_stock_shop')->field('sum(fs_num) as total,fs_type_id,fs_name')->group('fs_type_id')->where($w)->select();
        $this->_view->assign('dataTotal', $dataTotal);
        
        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        if ($param['pagetype'] == 'log') { //只显示出入口的记录
            $returnData = array();
            $dataInfo = LibF::M('filling_stock_log_shop')->field('goods_name,goods_type,sum(goods_num) as num,time')->where($where)->group('time,goods_type')->order(' goods_type asc,time DESC')->select();
            $list = '';
            if (!empty($dataInfo)) {
                foreach ($dataInfo as $value) {
                    $returnData[$value['time']][] = $value;
                }
                foreach ($returnData as $k => $val) {
                    $num = count($val);
                    foreach ($val as $ii => $v) {
                        $list .= '<tr>';
                        if ($ii == 0) {
                            $list .= '<td rowspan="' . $num . '">' . $k . '</td>';
                        }
                        $list .= '<td>' . $v['goods_name'] . '</td>
                                <td>' . $v['num'] . '</td>';
                        if ($ii == 0) {
                            $list .= '<td rowspan="' . $num . '"></td>';
                        }
                        $list .= '</tr>';
                    }
                }
            }
            $this->_view->assign('returnData', $returnData);
            $this->_view->assign('list', $list);
            
            $this->_view->display('stock/sproductlog.phtml');
        } else {
            $fillingstocklogModel = new FillingModel();
            $data = $fillingstocklogModel->getDataList($param, 'filling_stock_log_shop', $where, 'id desc');
            $this->_view->assign($data);
        }
    }
    
    //送气工配件库存
    public function shipperproductAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array('gtype' => 2);
        
        if (!empty($param)) {
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['goods_id'])) {
                $where['goods_type'] = $param['goods_id'];
                $getParam[] = "goods_id=" . $param['goods_id'];
            }
            if (!empty($param['shipper_id'])) {
                $where['shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }
            if (!empty($param['start_time']) && !empty($param['end_time'])) {
                $where['time'] = array(array('egt', $param['start_time']), array('elt', $param['end_time']), 'AND');
                $getParam['start_time'] = "start_time=" . $param['start_time'];
                $getParam['end_time'] = "end_time=" . $param['end_time'];
            }
            
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $w['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }

            if (!empty($param['pagetype'])) {
                $getParam[] = "pagetype=" . $param['pagetype'];
            }

            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if (!empty($this->shop_id))
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);

        $w = array('type' => 2);
        if($this->shop_id){
            $this->_view->assign('is_show_shop',  $this->shop_id);
            $w['shop_id'] = $this->shop_id;
        }

        //配件类型
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());

        $productsObject = array();
        $fillingstocklogModel = new FillingModel();
        $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
        if (!empty($commdityData)) {
            foreach ($commdityData as &$value) {
                $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                $productsObject[$value['id']] = $value;
            }
        }
        $this->_view->assign('productsObject', $productsObject);

        $shipperObject = ShipperModel::getShipperArray('', $this->shop_id);
        $this->_view->assign('shipperObject', $shipperObject);

        if ($param['pagetype'] == 'log') { //只显示出入口的记录
            $data = $fillingstocklogModel->getDataList($param, 'filling_stock_log_shipper', $where, 'ctime desc');
            $this->_view->assign($data);

            $this->_view->display('stock/shipperproductlog.phtml');
        } else if ($param['pagetype'] == 'loglist') {
            $returnData = array();
            $data = LibF::M('filling_stock_log_shipper')->field('shipper_id,goods_name,goods_type,sum(goods_num) as num,time')->where($where)->group('time,goods_type')->order(' goods_type asc,time DESC')->select();
            $list = '';
            if (!empty($data)) {
                foreach ($data as $value) {
                    $returnData[$value['time']][] = $value;
                }
                foreach ($returnData as $k => $val) {
                    $num = count($val);
                    foreach ($val as $ii => $v) {
                        $list .= '<tr>';
                        if ($ii == 0) {
                            $list .= '<td rowspan="' . $num . '">' . $k . '</td>';
                        }
                        $list .= '<td>' . $shipperObject[$v['shipper_id']]['shipper_name'] . '</td>';
                        $list .= '<td>' . $v['goods_name'] . '</td>
                                <td>' . $v['num'] . '</td>';
                        if ($ii == 0) {
                            $list .= '<td rowspan="' . $num . '"></td>';
                        }
                        $list .= '</tr>';
                    }
                }
            }
            $this->_view->assign('returnData', $returnData);
            $this->_view->assign('list', $list);

            $this->_view->display('stock/shipperproductlist.phtml');
        } else {
            //获取当前钢瓶类型库存数量
            $dataTotal = LibF::M('filling_stock_shipper')->field('sum(fs_num) as total,fs_type_id,fs_name,shipper_id')->group('fs_type_id,shipper_id')->where($w)->select();
            $this->_view->assign('dataTotal', $dataTotal);
        }
    }

    /**
     * 气站钢瓶库存
     * 
     */
    public function flowAction() {
        //气站出入库（钢瓶|配件）

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array('gtype' => 1);
        if (!empty($param)) {
            if (!empty($param['type'])){
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['goods_type'])){
                $where['goods_propety'] = $param['goods_type'];
                $getParam[] = "goods_type=" . $param['goods_type'];
            }
            if (!empty($param['fs_type_id'])){
                $where['goods_type'] = $param['fs_type_id'];
                $getParam[] = "fs_type_id=" . $param['fs_type_id'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['end_time'] = "time_end=" . $param['time_end'];
            }
            
            if (!empty($param['pagetype'])) {
                $getParam[] = "pagetype=".$param['pagetype'];
            }
            
            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        
        /**
         * 获取当前钢瓶类型库存数量
         * @type 1钢瓶2配件
         * @fs_type_id 规格
         * @goods_type 类型
         */
        $dataTotal = LibF::M('filling_stock')->field('sum(fs_num) as total,fs_type_id,fs_name,goods_type')->group('fs_type_id,goods_type')->where(array('type' => 1))->select();
        $dataTotalAll = $dataTotalName = $dataTotalType = array();
        if (!empty($dataTotal)) {
            foreach ($dataTotal as $value) {
                $dataTotalAll[$value['fs_type_id']] += $value['total'];
                $dataTotalName[$value['fs_type_id']] = $value['fs_name'];

                $dataTotalType[$value['goods_type']][$value['fs_type_id']] = $value;
            }
        }
        $this->_view->assign('dataTotalAll', $dataTotalAll);
        $this->_view->assign('dataTotalName', $dataTotalName);
        $this->_view->assign('dataTotalType', $dataTotalType);
        
        $statusData = $this->typeObject;
        $this->_view->assign('statusData', $statusData);

        //获取类型
        $bottleType = new BottletypeModel();
        $bottleTypeObject = $bottleType->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());
        
        if ($param['pagetype'] == 'log') {
            $this->_view->display('stock/flowlog.phtml');
        } else {
            $fillingstocklogModel = new FillingModel();
            $data = $fillingstocklogModel->getDataList($param, 'filling_stock_log', $where, 'ctime desc');
            $this->_view->assign($data);

            $newData = array();
            if (!empty($data['ext']['list'])) {
                foreach ($data['ext']['list'] as $key => &$value) {
                    $newData[$value['documentsn']][] = $value;
                }
            }
            $this->_view->assign('newData', $newData);
        }
    }

    public function addflowAction() {
        //配件类型
        $productsModel = new ProductsModel();
        $productsObject = $productsModel->getProductsArray();
        $this->_view->assign('productsObject', $productsObject);

        //气站库存添加
        if (IS_POST) {

            $param['type'] = $this->getRequest()->getPost('btype', 1); //出入库
            $param['goods_comment'] = $this->getRequest()->getPost('comment');

            //获取订单号
            $app = new App();
            $orderSn = $app->build_order_no();
            $param['documentsn'] = 'gpkc' . $orderSn;
            $param['admin_user'] = $data['admin_user'] = $this->user_info['username'];
            $param['time'] = date('Y-m-d');
            $param['ctime'] = time();
            $param['gtype'] = $this->getRequest()->getPost('goods_type', 1); //1钢瓶w配件

            $bottle = array();
            if ($param['gtype'] == 1) {
                //获取类型
                $bottleType = new BottletypeModel();
                $bottleTypeObject = $bottleType->getBottleTypeArray();
                $this->_view->assign('bottleTypeObject', $bottleTypeObject);

                $dlist = $this->getRequest()->getPost('list');
                if (!empty($dlist)) {
                    $stockmethodModel = new StockmethodModel();
                    foreach ($dlist as $dVal) {
                        $v = explode('|', $dVal);
                        $param['goods_propety'] = $v[0];  //钢瓶类型
                        $param['goods_id'] = $param['goods_type'] = $v[1]; //钢瓶规格
                        $param['goods_name'] = $bottleTypeObject[$v[1]]['bottle_name'];
                        $param['goods_price'] = $bottleTypeObject[$v[1]]['bottle_price'];
                        $param['goods_num'] = $v[2];
                        $param['shop_id'] = !empty($v[3]) ? $v[3] : 0;

                        $status = $stockmethodModel->GasstationsStock($param, $param['gtype']);  //统一出入库（气站）
                    }
                }
            } else {
                $productsObject = array();
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $fillingstocklogModel = new FillingModel();
                $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                        $productsObject[$value['id']] = $value;
                    }
                }

                $list = $this->getRequest()->getPost('list');
                if (!empty($list)) {
                    $stockmethodModel = new StockmethodModel();
                    foreach ($list as $dVal) {
                        $v = explode('|', $dVal);

                        $param['goods_propety'] = $v[1];  //配件规格
                        $param['goods_id'] = $param['goods_type'] = $v[0]; //配件id
                        $param['goods_name'] = $productsObject[$v[0]]['name'] . '-' . $productsObject[$v[0]]['typename'];
                        $param['goods_price'] = $productsObject[$v[0]]['retail_price'];
                        $param['goods_num'] = $v[2];
                        $param['shop_id'] = !empty($v[3]) ? $v[3] : 0;

                        $status = $stockmethodModel->GasstationsStock($param, $param['gtype']);  //统一出入库（气站）
                    }
                }
            }

            $returnData['status'] = $status;
            echo json_encode($returnData);
        }
        exit;
    }

    /**
     * 门店钢瓶库存
     * 
     */
    public function sflowAction() {
        //气站出入库（钢瓶|配件）

        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array('gtype' => 1);
        if (!empty($param)) {

            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['goods_type'])) {
                $where['goods_propety'] = $param['goods_type'];
                $getParam[] = "goods_type=" . $param['goods_type'];
            }
            if (!empty($param['fs_type_id'])) {
                $where['goods_type'] = $param['fs_type_id'];
                $getParam[] = "fs_type_id=" . $param['fs_type_id'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');

                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['end_time'] = "time_end=" . $param['time_end'];
            }
            
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $w['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }

            if (!empty($param['pagetype'])) {
                $getParam[] = "pagetype=".$param['pagetype'];
            }
            
            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }else {
            if ($this->shop_id)
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
        }

        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        if ($this->shop_id) {
            $this->_view->assign('is_show_shop', $this->shop_id);
        }

        /**
         * 获取当前钢瓶类型库存数量
         */
        $w['type'] = 1;
        $dataTotal = LibF::M('filling_stock_shop')->field('sum(fs_num) as total,fs_type_id,fs_name,goods_type')->group('fs_type_id,goods_type')->where($w)->select();
        $dataTotalAll = $dataTotalName = $dataTotalType = array();
        if (!empty($dataTotal)) {
            foreach ($dataTotal as $value) {
                $dataTotalAll[$value['fs_type_id']] += $value['total'];
                $dataTotalName[$value['fs_type_id']] = $value['fs_name'];

                $dataTotalType[$value['goods_type']][$value['fs_type_id']] = $value;
            }
        }

        $this->_view->assign('dataTotalAll', $dataTotalAll);
        $this->_view->assign('dataTotalName', $dataTotalName);
        $this->_view->assign('dataTotalType', $dataTotalType);

        $statusData = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶', 4 => '待修瓶');
        $this->_view->assign('statusData', $statusData);
        //获取类型
        $bottleType = new BottletypeModel();
        $bottleTypeObject = $bottleType->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        //获取门店
        $this->_view->assign('shopObject', ShopModel::getShopArray());

        if ($param['pagetype'] == 'log') {
            $this->_view->display('stock/sflowlog.phtml');
        } else {
            $fillingstocklogModel = new FillingModel();
            $data = $fillingstocklogModel->getDataList($param, 'filling_stock_log_shop', $where, 'id DESC');
            $this->_view->assign($data);

            $newData = array();
            if (!empty($data['ext']['list'])) {
                foreach ($data['ext']['list'] as $key => &$value) {
                    $newData[$value['documentsn']][] = $value;
                }
            }
            $this->_view->assign('newData', $newData);
            $this->_view->assign('newCount', $data['ext']['count']);
        }
    }

    public function addsflowAction() {

        //获取类型
        $bottleType = new BottletypeModel();
        $bottleTypeObject = $bottleType->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        //门店
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);

        //气站库存添加
        if (IS_POST) {

            $param['type'] = $this->getRequest()->getPost('btype', 1); //出入库
            $param['goods_comment'] = $this->getRequest()->getPost('comment');

            //获取订单号
            $app = new App();
            $orderSn = $app->build_order_no();
            $param['documentsn'] = 'gpkc' . $orderSn;
            $param['admin_user'] = $this->user_info['username'];
            $param['time'] = date('Y-m-d');
            $param['ctime'] = time();
            $param['gtype'] = $this->getRequest()->getPost('goods_type', 1); //1钢瓶w配件
            $param['shop_id'] = $this->shop_id;
            $param['shop_name'] = $shopObject[$this->shop_id]['shop_name'];

            $bottle = array();
            if ($param['gtype'] == 1) {
                $dlist = $this->getRequest()->getPost('list');
                if (!empty($dlist)) {
                    $stockmethodModel = new StockmethodModel();
                    foreach ($dlist as $dVal) {
                        $v = explode('|', $dVal);
                        
                        $param['goods_propety'] = $v[0];  //钢瓶类型
                        $param['goods_id'] = $v[1]; //钢瓶规格
                        $param['goods_type'] = $param['goods_id'];
                        $param['goods_name'] = $bottleTypeObject[$v[1]]['bottle_name'];
                        $param['goods_price'] = $bottleTypeObject[$v[1]]['bottle_price'];
                        $param['goods_num'] = $v[2];
                        if ($v[0] == 1 || $v[0] == 3) {
                            $param['for_name'] = isset($v[3]) && !empty($v[3]) ? $v[3] : '送气工';
                            $status = $stockmethodModel->ShopstationsStock($param, $this->shop_id, 1);  //统一出入库（门店）
                            if($status){
                                $shipper_param['type'] = 2; //出库
                                $shipper_param['documentsn'] = $param['documentsn'];
                                $shipper_param['goods_propety'] = $param['goods_propety'];  //钢瓶规格
                                $shipper_param['goods_type'] = $param['goods_id']; //配件id
                                $shipper_param['goods_name'] = $param['goods_name'];
                                //$param['goods_price'] = $data['goods_price'];
                                $shipper_param['goods_num'] = $param['goods_num'];
                                $shipper_param['shop_id'] = $this->shop_id;
                                $shipper_param['shipper_id'] = $v[4];
                                $shipper_param['admin_user'] = $param['admin_user'];
                                $shipper_param['gtype'] = 1;
                                $shipper_param['time'] = $param['time'];
                                $shipper_param['ctime'] = $param['ctime'];
                                $stockmethodModel->ShipperstationsStock($shipper_param, $v[4], $this->shop_id, 0, 1); //送气工出库
                            }
                            
                        } else {
                            $param['for_name'] = '气站';
                            $status = $stockmethodModel->ShopstationsStock($param, $this->shop_id, $param['gtype']);  //统一出入库（门店）
                        }
                    }
                }
            } else {

                $productsObject = array();
                $productTypeModel = new ProducttypeModel();
                $productTypeData = $productTypeModel->getProductTypeArray();

                $fillingstocklogModel = new FillingModel();
                $commdityData = $fillingstocklogModel->getDataArr('commodity', array('type' => 2), 'id desc');
                if (!empty($commdityData)) {
                    foreach ($commdityData as &$value) {
                        $value['typename'] = isset($productTypeData[$value['norm_id']]) ? $productTypeData[$value['norm_id']]['name'] : '';
                        $productsObject[$value['id']] = $value;
                    }
                }

                $list = $this->getRequest()->getPost('list');
                if (!empty($list)) {
                    $stockmethodModel = new StockmethodModel();
                    foreach ($list as $dVal) {
                        $v = explode('|', $dVal);

                        $param['goods_propety'] = $v[1];  //配件规格
                        $param['goods_id'] = $param['goods_type'] = $v[0]; //配件id
                        $param['goods_name'] = $productsObject[$v[0]]['name'] . '-' . $productsObject[$v[0]]['typename'];
                        $param['goods_price'] = $productsObject[$v[0]]['retail_price'];
                        $param['goods_num'] = $v[2];
                        $param['shipper_id'] = !empty($v[3]) ? $v[3] : 0;
                        $param['for_name'] = (!empty($v[4])) ? $v[4] : '门店';

                        $status = $stockmethodModel->ShopstationsStock($param, $this->shop_id, 2);  //统一出入库（门店）
                    }
                }
            }

            $returnData['status'] = $status;
            echo json_encode($returnData);
        }
        exit;
    }

    /**
     * 送气工钢瓶库存
     */
    public function shipperflowAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);

        $where = array('gtype' => 1);
        if (!empty($param)) {

            if (!empty($param['type'])){
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }
            if (!empty($param['goods_type'])){
                $where['goods_propety'] = $param['goods_type'];
                $getParam[] = "goods_type=" . $param['goods_type'];
            }
            if (!empty($param['fs_type_id'])){
                $where['goods_type'] = $param['fs_type_id'];
                $getParam[] = "fs_type_id=" . $param['fs_type_id'];
            }
            if (!empty($param['shipper_id'])){
                $where['shipper_id'] = $param['shipper_id'];
                $getParam[] = "shipper_id=" . $param['shipper_id'];
            }
            if (!empty($param['time_start']) && !empty($param['time_end'])){
                $where['time'] = array(array('egt', $param['time_start']), array('elt', $param['time_end']), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }
            
            if ($this->shop_id) {
                $param['shop_id'] = $this->shop_id;
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
                $getParam[] = "shop_id=" . $this->shop_id;
            } else {
                if (!empty($param['shop_id'])) {
                    $where['shop_id'] = $w['shop_id'] = $param['shop_id'];
                    $getParam[] = "shop_id=" . $param['shop_id'];
                }
            }
            
            if (!empty($param['pagetype'])) {
                $getParam[] = "pagetype=".$param['pagetype'];
            }
            
            unset($param['submit']);
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));
        }else {
            if (!empty($this->shop_id))
                $where['shop_id'] = $w['shop_id'] = $this->shop_id;
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        
        $statusData = array(1 => '空瓶', 2 => '重瓶', 3 => '折旧瓶',4 => '待修瓶');
        $this->_view->assign('statusData', $statusData);

        //获取类型
        $bottleType = new BottletypeModel();
        $bottleTypeObject = $bottleType->getBottleTypeArray();
        $this->_view->assign('bottleTypeObject', $bottleTypeObject);

        //获取送气工
        $this->_view->assign('shipperObject', ShipperModel::getShipperArray('', $this->shop_id));
        
        //门店
        $shopObject = ShopModel::getShopArray();
        $this->_view->assign('shopObject', $shopObject);
        if ($param['pagetype'] == 'log') { //只显示出入口的记录
            $fillingstocklogModel = new FillingModel();
            $data = $fillingstocklogModel->getDataList($param, 'filling_stock_log_shipper', $where, 'id desc');
            $this->_view->assign($data);

            $newData = array();
            if (!empty($data['ext']['list'])) {
                foreach ($data['ext']['list'] as $key => &$value) {
                    $newData[$value['documentsn']][] = $value;
                }
            }
            $this->_view->assign('newData', $newData);
            $this->_view->assign('newCount', $data['ext']['count']);
            
            $this->_view->display('stock/shipperflowlog.phtml');
        } else if ($param['pagetype'] == 'loglist') {
            $this->_view->display('stock/shipperflowlist.phtml');
        } else {
            /**
             * 获取当前钢瓶类型库存数量
             */
            $w['type'] = 1;
            $dataTotal = LibF::M('filling_stock_shipper')->field('sum(fs_num) as total,fs_type_id,fs_name,goods_type,shipper_id')->group('fs_type_id,goods_type,shipper_id')->where($w)->select();
            $dataTotalAll = $dataTotalName = $dataTotalType = array();
            if (!empty($dataTotal)) {
                foreach ($dataTotal as $value) {
                    $dataTotalAll[$value['shipper_id']][$value['fs_type_id']] += $value['total'];
                    $dataTotalName[$value['shipper_id']][$value['fs_type_id']] = $value['fs_name'];

                    $dataTotalType[$value['goods_type']][$value['shipper_id']][$value['fs_type_id']] = $value;
                }
            }

            $this->_view->assign('dataTotalAll', $dataTotalAll);
            $this->_view->assign('dataTotalName', $dataTotalName);
            $this->_view->assign('dataTotalType', $dataTotalType);
        }

    }

}