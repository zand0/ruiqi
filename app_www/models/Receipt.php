<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class ReceiptModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    /**
     * 创建回库确认单据
     */
    public function addComfirmBack($params = array(),$id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        
        $data['confirme_no'] = $params['confirme_no'];
        $data['bottle'] = $params['bottle'];
        $data['license_plate'] = $params['license_plate'];
        $data['admin_id'] = $params['admin_id'];
        $data['admin_user'] = $params['admin_user'];
        $data['guards'] = $params['guards'];
        $data['time'] = $params['time'];
        $data['ctime'] = $params['ctime'];

        $dataDetail['datalist'] = $params['datalist'];
        $dataDetail['confirme_no'] = $data['confirme_no'];
        if ($id) {
            $status = LibF::M('confirme_store')->where(array('id' => $id))->save($data);
        } else {
            $status = LibF::M('confirme_store')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }

        //$this->addconfirmbackDetail($dataDetail);
        
        return $this->logicReturn(200, 'ok', $status);
    }
    
    /**
     * 创建回库确认单详情
     */
    public function addconfirmbackDetail($params = array()) {
        if (empty($params['datalist'])) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        $status = 0;
        
        $time = time();
        foreach ($params['datalist'] as $value) {
            $data['confirme_no'] = $params['confirme_no'];
            $data['ftype'] = $value['btype'];
            $data['type'] = $value[0];
            $data['typename'] = $value['name'];
            $data['num'] = $value[1];
            $data['ctime'] = $time;

            $status = LibF::M('confirme_store_detail')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }
    
    /**
     * 创建入库确认单据
     */
    public function addComfirm($params = array(),$id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        
        $data['confirme_no'] = $params['confirme_no'];
        $data['shop_id'] = $params['shop_id'];
        $data['bottle'] = $params['bottle'];
        $data['license_plate'] = $params['license_plate'];
        $data['admin_id'] = $params['admin_id'];
        $data['admin_user'] = $params['admin_user'];
        $data['guards'] = $params['guards'];
        $data['time'] = $params['time'];
        $data['ctime'] = $params['ctime'];

        $dataDetail['datalist'] = $params['datalist'];
        $dataDetail['confirme_no'] = $data['confirme_no'];
        if ($id) {
            $status = LibF::M('confirme')->where(array('id' => $id))->save($data);
        } else {
            $status = LibF::M('confirme')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }

        //$this->addconfirmDetail($dataDetail);
        
        return $this->logicReturn(200, 'ok', $status);
    }
    
    /**
     * 创建入库确认单详情
     */
    public function addconfirmDetail($params = array()) {
        if (empty($params['datalist'])) {
            return $this->logicReturn('0203', '请提交数据！');
        }
        $status = 0;
        
        $time = time();
        foreach ($params['datalist'] as $value) {
            $data['confirme_no'] = $params['confirme_no'];
            $data['ftype'] = $value['btype'];
            $data['type'] = $value[0];
            $data['typename'] = $value['name'];
            $data['num'] = $value[1];
            $data['ctime'] = $time;

            $status = LibF::M('confirme_detail')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

}