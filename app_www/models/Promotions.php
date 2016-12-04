<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class PromotionsModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($data, $id) {
        if (empty($data)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        if ($id) {
            $where = array('id' => $id);
            $status = LibF::M('promotions')->where($where)->save($data);
        } else {
            $status = LibF::M('promotions')->add($data);
        }
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
        $status = LibF::M('promotions')->where($where)->delete();
        if (!$status) {
            return $this->logicReturn('0206', '删除失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }
    
    public function getPromotionsObject() {
        $returnData = array();

        $where['status'] = 1;
        $data = LibF::M('promotions')->where($where)->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $returnData[$value['id']] = $value;
            }
        }
        return $returnData;
    }

}