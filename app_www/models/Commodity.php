<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class CommodityModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 创建创建相关数据
     * 
     * @param $data
     * @param $id
     */
    public function add($data, $id) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $where = array('id' => $id);
            $status = LibF::M('commodity')->where($where)->save($data);
        } else {
            $status = LibF::M('commodity')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 更新相关数据条件数据
     * 
     * @param $data
     * @param $id
     */
    public function editData($data, $id) {
        if (empty($data) || empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('commodity')->where($where)->save($data);
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 删除指定id数据
     */
    public function delData($id) {
        if (empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('commodity')->where($where)->delete();
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    public function listData($param = array()) {
        $data = LibF::M('commodity')->where(array('status' => 0, 'type' => 1))->order('id DESC')->select();
        return $data;
    }
    
    //如果有套餐获取当前指定套餐列表
    public function getTcObject() {
        $where['type'] = array('in', array(4, 5));  //4体验套餐 5优惠套餐
        $where['status'] = 0;
        $data = LibF::M('commodity')->where($where)->select();
        $returnData['ty'] = array();
        $returnData['yh'] = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $val['id'] = $value['id'];
                $val['name'] = $value['name'];
                $val['norm_id'] = $value['norm_id'];
                $val['num'] = $value['num'];
                $val['price'] = $value['price'];
                $val['money'] = $value['money'];
                $val['deposit'] = $value['deposit'];
                if ($value['type'] == 4) {
                    $returnData['ty'][] = $val;
                } else {
                    $returnData['yh'][] = $val;
                }
            }
        }
        return $returnData;
    }
    
    public function getCommodityObject($type = 1) {
        $where['type'] = $type;
        $where['status'] = 0;
        $data = LibF::M('commodity')->where($where)->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $val['id'] = $value['id'];
                $val['name'] = $value['name'];
                $val['norm_id'] = $value['norm_id'];
                $val['num'] = $value['num'];
                $val['retail_price'] = $value['retail_price'];  //零售价
                $val['direct_price'] = $value['direct_price']; //直营价
                $val['affiliate_price'] = $value['affiliate_price']; //加盟价
                $returnData[] = $val;
            }
        }
        return $returnData;
    }

}