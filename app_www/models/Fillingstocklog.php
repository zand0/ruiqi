<?php
/**
 * Description of Shop
 *
 * @author wjy
 */
class FillingstocklogModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    public function add($params) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['documentsn'] = $params['documentsn'];
        $data['goods_name'] = $params['goods_name'];
        $data['type'] = $params['type'];
        $data['goods_type'] = $params['goods_type'];
        $data['goods_channel'] = $params['goods_channel'];
        $data['goods_price'] = $params['goods_price'];
        $data['goods_time'] = $params['goods_time'];
        $data['goods_propety'] = $params['goods_propety'];
        $data['goods_comment'] = $params['goods_comment'];
        $data['gtype'] = $params['gtype'];
        $data['admin_user'] = $params['admin_user'];
        $data['time'] = date('Y-m-d');
        $data['ctime'] = time();

        if($params['id']){
            $status = LibF::M('filling_stock_log')->add($data);
        }else{
            $status = LibF::M('filling_stock_log')->where(array('id' => $params['id']))->save($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok');
    }
    
    public function add_shop($params) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['documentsn'] = $params['documentsn'];
        $data['goods_name'] = $params['goods_name'];
        $data['type'] = $params['type'];
        $data['goods_type'] = $params['goods_type'];
        $data['goods_channel'] = $params['goods_channel'];
        $data['goods_price'] = $params['goods_price'];
        $data['goods_time'] = $params['goods_time'];
        $data['goods_propety'] = $params['goods_propety'];
        $data['goods_comment'] = $params['goods_comment'];
        $data['gtype'] = $params['gtype'];
        $data['admin_user'] = $params['admin_user'];
        $data['shop_id'] = $params['shop_id'];
        $data['shop_name'] = $params['shop_name'];
        $data['time'] = date('Y-m-d');
        $data['ctime'] = time();

        if($params['id']){
            $status = LibF::M('filling_stock_log_shop')->add($data);
        }else{
            $status = LibF::M('filling_stock_log_shop')->where(array('id' => $params['id']))->save($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok');
    }
    

}