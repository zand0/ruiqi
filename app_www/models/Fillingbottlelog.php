<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FillingbottlelogModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($params, $id = 0) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['filling_no'] = $params['filling_no'];
        $data['num'] = $params['num'];
        $data['bottle'] = $params['bottle'];
        $data['admin_id'] = $params['admin_id'];
        $data['admin_user'] = $params['admin_user'];
        $data['type'] = $params['type'];
        $data['time'] = $params['time'];
        $data['ctime'] = time();

        if ($id) {
            $status = LibF::M('filling_bottle_log')->where('id=' . $id)->save($data);
        } else {
            $status = LibF::M('filling_bottle_log')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

}