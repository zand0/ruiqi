<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class QuotaModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    /**
     * 额度列表
     */
    public function quotaList($param = ''){
        $data = LibF::M('quota')->where($param)->select();
        return $data;
    }

    /**
     * 额度更新
     * 
     * @param $params
     */
    public function edit($params, $id) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        
        $typeObject = array(1 =>'普通欠款用户',2=>'门店经理',3=>'区域经理');
        
        $data['name'] = $typeObject[$params['type']];
        $data['status'] = $params['type'];
        $data['price'] = $params['price'];
        $data['comment'] = $params['comment'];

        if ($id) {
            $status = LibF::M('quota')->where('id=' . $id)->save($data);
        } else {
            $showData = LibF::M('quota')->where(array('status' => $data['status']))->find();
            if (empty($showData)) {
                $status = LibF::M('quota')->add($data);
            } else {
                $status = LibF::M('quota')->where(array('status' => $data['status']))->save($data);
            }
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }
}
