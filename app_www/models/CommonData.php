<?php
/** 
 * 通用方法
 * @author jinyue.wang
 */
class CommonDataModel extends Com\Model\Base {

    //获取相关职务的角色
    public function getAreaUser($quarters_id) {
        if (empty($quarters_id)) {
            return FALSE;
        }

        $userdata = array();
        $userModel = new Model('quarters');
        $leftJoin = "LEFT JOIN rq_auth_role ON rq_quarters.quarters_id = rq_auth_role.quarters_id ";
        $filed = "rq_auth_role.id";
        $where = array('rq_quarters.quarters_id' => $quarters_id);
        $roleData = $userModel->join($leftJoin)->field($filed)->where($where)->select();
        if (!empty($roleData)) {
            $role_list = '';
            $roleArr = array();
            foreach ($roleData as $rVal) {
                $roleArr[] = $rVal['id'];
            }
            $role_list = !empty($roleArr) ? implode(',', $roleArr) : '';
            if (!empty($role_list)) {
                $w['roles'] = array('in', $role_list);
                $userdata = LibF::M('admin_user')->field('user_id,username,mobile_phone,real_name')->where($w)->select();
            }
        }
        return $userdata;
    }

    //获取相关区域
    public function getQuarterData(){
        $quarterData = array();
        $data = LibF::M('area')->where(array('status' => 0))->select();
        if(!empty($data)){
            foreach($data as $value){
                $quarterData[$value['area_id']] = $value;
            }
        }
        return $quarterData;
    }
    
    //获取所有商品
    public function getCommodityData(){
        $commodityData = array();

        $where['type'] = array('in', array(1, 2));
        $data = LibF::M('commodity')->where($where)->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $commodityData[$value['id']] = $value;
            }
        }
        return $commodityData;
    }
}
