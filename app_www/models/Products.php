<?php

/**
 * Description of products
 *
 * @author wky
 */
class ProductsModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($params,$id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        
        $data['documentsn'] = $params['documentsn'];
        $data['shop_id'] = $params['shop_id'];
        $data['shipper_id'] = isset($params['shipper_id'])?$params['shipper_id']:0;
        $data['time'] = strtotime($params['time']);
        $data['type'] = $params['type'];
        $data['admin_user'] = $params['admin_user'];
        $data['time_created'] = time();
        
        $shop_level = intval($params['shop_level']);
        if($id){
            $status = LibF::M('warehousing')->where('id='.$id)->save($data);
        }else{
            $products = self::getProductsArray();
            $status = LibF::M('warehousing')->add($data);
            if($status && !empty($params['products_id'])){
                foreach ($params['products_id'] as $key => $value){
                    $pData['documentsn'] = $params['documentsn'];
                    $pData['product_id'] = $value;
                    $pData['product_name'] = $products[$value]['products_name'];
                    $pData['product_num'] = $params['products_num'][$key];
                    $pData['time_created'] = time();
                    //存详细
                    LibF::M('warehousing_info')->add($pData);
                    //增减配件库存
                    $data = array(
                        'products_no'   => $value,
                        'products_name' => $products[$value]['products_name'],
                        'products_type' => 1,
                        'shop_level'    => $shop_level,
                        'shop_id'       => $data['shop_id'],
                        'num'           => $pData['product_num'],
                        'utime'         => time()
                    );
                    //type=1 入库
                    if($data['type'] == 1){
                        LibF::D('Filling')->uadd($data, '`num`=`num`+'.$pData['product_num']);
                    } else {
                        //出库
                        //========送气工领取，门店出库=========
                        if(isset($params['shipper_id']) && !empty($params['shipper_id'])) {
                            //LibF::D('filling')->uadd($data, '`num`=`num`-'.$pData['product_num']);
                            //门店出库
                            LibF::M('products_tj')->uquery('update rq_products_tj set `num`=`num`-'.$pData['product_num'].' where shop_level='.$shop_level.(intval($data['shop_id'])>0?' and shop_id='.$data['shop_id']:'').' and products_type=1 and products_no="'.$pData['product_id'].'"');
                        } else {
                            //=========门店入库=========
                            //气站出库
                            LibF::M('products_tj')->uquery('update rq_products_tj set `num`=`num`-'.$pData['product_num'].' where shop_level=1 and products_type=1 and products_no="'.$pData['product_id'].'"');
                            //门店入库
                            LibF::D('Filling')->uadd($data, '`num`=`num`+'.$pData['product_num']);
                        }
                    }
                }
            }
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, '添加成功', $status);
    }

    public function productList($params,$type = 1) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $documentsn = isset($params['documentsn']) ? $params['documentsn'] : '';
        $userid = isset($params['userid']) ? $params['userid'] : '';
        
        $shop_id = isset($params['shop_id']) ? $params['shop_id'] : '';

        $warehousingModel = LibF::M('warehousing');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where = array('type' => $type);
        if ($documentsn) {
            $where['documentsn'] = $documentsn;
        }
        if ($userid) {
            $where['admin_user'] = $userid;
        }
        if(isset($params['shipper_id']) && !empty($params['shipper_id'])) {
            $where['shipper_id'] = $params['shipper_id'];
        }
        
        if(isset($params['shop_id']) && !empty($params['shop_id'])){
            $where['shop_id'] = $params['shop_id'];
        }
        
        $offset = ($page - 1) * $pageSize;
        $count = $warehousingModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, '添加成功', $data);
        }
        $rows = $warehousingModel->where($where)->limit($offset, $pageSize)->order('time_created desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, '添加成功', $data);
    }
    
    public function getProductInfo($id = '') {
        $where = array();
        if($id)
            $where['documentsn'] = $id;
        
        $data = LibF::M('warehousing_info')->where($where)->select();
        return $data;
    }

    /**
     * 获取配件的相关信息
     * 
     * @param $products_id
     * @return array
     */
    public static function getProductsArray($products_id = 0) {

        $model = LibF::M('products');
        if ($products_id) {
            return $model->where('id=' . $products_id)->find();
        } else {
            $productsObject = array();
            $data = $model->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $productsObject[$value['id']] = $value;
                }
            }
            return $productsObject;
        }
    }
    
    public static function productsArrlist() {
        $dataType = array();
        $data = LibF::M('commodity')->where(array('type' => 2))->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $val['id'] = $value['id'];
                $val['products_no'] = $value['commoditysn'];
                $val['products_name'] = $value['name'];
                $val['products_norm'] = $value['norm_id'];
                $dataType[$val['id']] = $val;
            }
        }
        
        return $dataType;
    }

    public static function getProductsObject($products_sn = 0) {

        $model = LibF::M('products');
        if ($products_sn) {
            return $model->where(array('products_no' =>$products_sn))->find();
        } else {
            $productsObject = array();
            $data = $model->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $productsObject[$value['products_no']] = $value;
                }
            }
            return $productsObject;
        }
    }

    public function bottleInfo($id = '') {
        $where = array();
        if ($id)
            $where['documentsn'] = $id;

        $data = LibF::M('bottle_purchase_info')->where($where)->select();
        return $data;
    }

}