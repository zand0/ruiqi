<?php

class UruleModel extends Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('auth_rule');
    }

    public function _getRuleList() {
        $data = $this->tableD->order('pid asc,list_sort asc')->select();

        $returnData = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                if ($value['pid'] == 0) {
                    $returnData[$value['id']] = array();
                } else {
                    if (isset($returnData[$value['pid']])) {
                        $returnData[$value['pid']][] = $value;
                    }
                }
            }
        }
        return $returnData;
    }

}
