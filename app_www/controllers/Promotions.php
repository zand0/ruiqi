<?php

/**
 * 优惠折扣活动
 */
class PromotionsController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;
    protected $typeObject;
    protected $rangeUse;

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
        $this->typeObject = array(1 => '优惠券', 2 => '折扣商品', 3 => '余额券');
        $this->rangeUse = array(1 => '用户', 2 => '送气工', 3 => '门店', 4 => '全部');
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $param = !empty($param) ? $param : $this->getRequest()->getQuery();
        $this->_view->assign('param', $param);
        if (!empty($param)) {
            $getParam = array();
            if (!empty($param['type'])) {
                $where['type'] = $param['type'];
                $getParam[] = "type=" . $param['type'];
            }

            if (!empty($param['time_start']) && !empty($param['time_end'])) {
                $where['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['time_end'] . ' 23:59:59')), 'AND');
                $getParam['time_start'] = "time_start=" . $param['time_start'];
                $getParam['time_end'] = "time_end=" . $param['time_end'];
            }

            unset($param['submit']);
            if (!empty($getParam))
                $this->_view->assign('getparamlist', implode('&', $getParam));
        }
        $param['page'] = $this->getRequest()->getQuery('page', 1);
        $this->_view->assign('page', $param['page']);
        $this->_view->assign('param', $param);

        $promotionsModel = new FillingModel();
        $returnData = $promotionsModel->getDataList($param, 'promotions', $where, 'id desc');
        if (!empty($returnData['ext']['list'])) {

            //获取所有配件
            $productTypeModel = new ProducttypeModel();
            $productTypeData = $productTypeModel->getProductTypeArray();
            foreach ($returnData['ext']['list'] as &$value) {
                $value['title'] = '';
                if ($value['type'] == 2) {
                    if (!empty($value['annex'])) {
                        $annexArr = explode('|', $value['annex']);
                        $value['title'] = $annexArr[2] . $productTypeData[$annexArr[1]]['name'];
                    }
                }
            }
        }
        $this->_view->assign($returnData);

        $this->_view->assign('typeObject', $this->typeObject);
    }

    public function addAction() {

        $data = $this->getRequest()->getPost();
        if (!empty($data)) {
            $app = new App();
            $orderSn = $app->build_order_no();
            $param['promotionsn'] = 'yh' . $orderSn;
            $param['title'] = ($data['type'] == 1) ? '优惠券' : (($data['type'] = 2) ? '折扣商品' : '余额券');
            $param['type'] = $data['type'];
            $param['price'] = $data['price']; //当前优惠使用条件
            $param['money'] = $data['money']; //当前价格
            if ($data['type'] == 2)
                $param['annex'] = $data['annex'];
            
            if ($data['range_use'])
                $param['range_use'] = $data['range_use'];
            
            if ($data['is_show'])
                $param['is_show'] = $data['is_show'];
            if ($data['is_weixin'])
                $param['is_weixin'] = $data['is_weixin'];
            if ($data['file_name'])
                $param['file_name'] = $data['file_name'];

            $param['comment'] = $data['comment'];
            $param['time_start'] = strtotime($data['time_start']);
            $param['time_end'] = strtotime($data['time_end']);
            $param['time_created'] = time();
            $id = $data['id'];

            $promotionsModel = new PromotionsModel();
            $status = $promotionsModel->add($param, $id);
            if ($status) {
                $this->success('更新成功', '/promotions/index');
            } else {
                $this->error('更新失败');
            }
        }

        $type = 1;
        $id = $this->getRequest()->getQuery('id');
        if ($id > 0) {
            $promotionsData = LibF::M('promotions')->where(array('id' => $id))->find();
            if (!empty($promotionsData)) {
                $promotionsData['time_start'] = ($promotionsData['time_start'] > 0) ? date("Y-m-d", $promotionsData['time_start']) : '';
                $promotionsData['time_end'] = ($promotionsData['time_end'] > 0) ? date('Y-m-d', $promotionsData['time_end']) : '';
                $promotionsData['product_id'] = 0;
                if (!empty($promotionsData['annex'])) {
                    $annexArr = explode('|', $promotionsData['annex']);
                    $promotionsData['product_id'] = $annexArr[0];
                }
                $type = $promotionsData['type'];
            }
            $this->_view->assign($promotionsData);
        }
        $this->_view->assign('type', $type);

        //获取所有配件
        $commdityModel = new CommodityModel();
        $commdityObject = $commdityModel->getCommodityObject(2);
        $this->_view->assign('commdityObject', $commdityObject);

        //获取配件类型
        $productTypeModel = new ProducttypeModel();
        $productTypeData = $productTypeModel->getProductTypeArray();
        $this->_view->assign('productTypeData', $productTypeData);

        $this->_view->assign('typeObject', $this->typeObject);
        $this->_view->assign('rangeUse', $this->rangeUse);
    }

    public function uploadAction() {
        $imageUploadModel = new ImageuploadModel();
        define('ROOT_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/webroot_www/statics/upload/file/');

        if (!empty($_FILES)) {
            $ext = pathinfo($_FILES['Filedata']['name']);
            $ext = strtolower($ext['extension']);
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = ROOT_PATH;
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
            $photo_name = time();

            $new_file_name = $photo_name . '.' . $ext;
            $targetFile = $targetPath . $new_file_name;
            move_uploaded_file($tempFile, $targetFile);

            if (!file_exists($targetFile)) {
                $ret['result_code'] = 0;
                $ret['result_des'] = '上传失败';
            } else {
                $img = '/statics/upload/file/' . $new_file_name;

                $ret['result_code'] = 1;
                $ret['result_des'] = $img;
                $ret['result_photo_name'] = $photo_name;
            }
        } else {
            $ret['result_code'] = 100;
            $ret['result_des'] = '没有选择文件';
        }
        exit(json_encode($ret));
        exit;
    }

}