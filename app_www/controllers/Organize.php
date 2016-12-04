<?php

/**
 * 组织机构
 */
class OrganizeController extends \Com\Controller\Common {

    public function indexAction() {

        $where = array();

        $shop = new Model('shop');
        $list = $shop->join('LEFT JOIN rq_admin_user ON rq_shop.mobile_phone = rq_admin_user.mobile_phone')->field("rq_shop.shop_id,rq_shop.shop_name,rq_shop.address,rq_admin_user.remark,rq_admin_user.mobile_phone,rq_admin_user.username,rq_admin_user.photo")->where($where)->select();

        $this->_view->assign('shop', $list);
    }

    public function detialAction() {
        $shop_id = $this->getRequest()->getQuery('shop_id', 0);
        $where = array('shop_id' => $shop_id);

        $list = LibF::M('shipper')->where($where)->select();
        $this->_view->assign('shipperData', $list);

        $shop = new Model('shop');
        $shopData = $shop->join('LEFT JOIN rq_admin_user ON rq_shop.mobile_phone = rq_admin_user.mobile_phone')->field("rq_shop.shop_id,rq_shop.shop_name,rq_shop.address,rq_admin_user.remark,rq_admin_user.mobile_phone,rq_admin_user.username,rq_admin_user.photo")->where(array('rq_shop.shop_id' => $shop_id))->find();
        $this->_view->assign('shopData', $shopData);

        //订单数量
        $orderTotal = LibF::M('order')->field('count(*) as total')->where($where)->group('shop_id')->find();
        $this->_view->assign('orderTotal', $orderTotal);

        //新用户数量
        $kehuTotal = LibF::M('kehu')->field('count(*) as total')->where($where)->find();
        $this->_view->assign('kehuTotal', $kehuTotal);

        //空瓶|重瓶数量
        $where['is_use'] = 1;
        $knum = $znum = 0;
        $bottleTotal = LibF::M('store_inventory')->field('count(*) as total,is_open')->where($where)->group('is_open')->select();
        if ($bottleTotal) {
            foreach ($bottleTotal as $value) {
                if ($value['is_open'] == 1) {
                    $znum = $value['total'];
                } else if ($value['is_open'] == 0) {
                    $knum = $value['total'];
                }
            }
        }
        $this->_view->assign('knum', $knum);
        $this->_view->assign('znum', $znum);

        //销售额统计
        $xsDay = LibF::M('order')->field('sum(money) as total,from_unixtime(ctime,"%Y/%m/%d") as time')->where(array('shop_id' => $shop_id))->group('time')->select();
        $dayTitle = $dayShow = array();
        if (!empty($xsDay)) {
            foreach ($xsDay as $xVal) {
                $dayTitle[] = $xVal['time'];
                $dayShow['name'] = '每天销售额';
                $dayShow['data'][] = floatval($xVal['total']);
            }
        }
        $this->_view->assign('daytitle', json_encode($dayTitle));
        $this->_view->assign('dayshow', json_encode(array(0 => $dayShow)));
    }

    public function listAction() {
        $tempType = $this->getRequest()->getQuery('temptype', 'now'); //now 当前页面 new 新页面
        if ($tempType == 'new') {
            $orgModel = new OrgModel();
            $list = $orgModel->getQuarter();
            $this->_view->assign('list', $list);
            
            //得到当前部门数
            $organizationNum = LibF::M('organization')->count();
            $this->_view->assign('organizationNum',$organizationNum);
            
            //得到送气工人数
            $shipperNum = LibF::M('shipper')->where(array('status' => 1))->count();
            $this->_view->assign('shipperNum',$shipperNum);
            
            //分类人数
            $adminUserData = array();
            $userNum = LibF::M('admin_user')->field('status,count(*) as total')->group('status')->select();
            if(!empty($userNum)){
                foreach($userNum as $val){
                    $adminUserData[$val['status']] = $val['total'];
                }
            }
            $this->_view->assign('adminUserData',$adminUserData);
            
            $returnData = $returnTotal = array();
            $orgModel = new Model('organization');
            $filed = " rq_organization.org_id,rq_organization.org_name,rq_quarters.id ";
            $data = $orgModel->join('LEFT JOIN rq_quarters ON rq_organization.org_id = rq_quarters.org_parent_id')->field($filed)->where($where)->select();
            if (!empty($data)) {
                $orgObject = $orgData = array();
                foreach ($data as $value) {
                    if (!empty($value['id']))
                        $orgData[$value['org_id']][] = $value['id'];

                    $orgObject[$value['org_id']] = $value['org_name'];
                }

                if (!empty($orgData)) {
                    foreach ($orgData as $key => $val) {
                        $cData = $tData = array();
                        //$quarterslist = implode(',', $val);
                        $cWhere['rq_quarters_user.quarters_id'] = array('in', array_unique($val));
                        $cWhere['rq_admin_user.user_id'] = array('gt', 0);
                        $cData['org_id'] = $key;

                        $cData['title'] = $tData[0] = isset($orgObject[$key]) ? $orgObject[$key] : '';
                        $userModel = new Model('quarters_user');
                        $cData['total'] = $tData[1] = (int) $userModel->join('LEFT JOIN rq_admin_user ON rq_quarters_user.uid = rq_admin_user.user_id')->field('distinct rq_admin_user.user_id')->where($cWhere)->count();

                        $returnData[] = $cData;
                        $returnTotal[] = $tData;
                    }
                }
            }

            $this->_view->assign('returnData', $returnData);
            $this->_view->assign('returnTotal', json_encode($returnTotal));

            $this->_view->display('organize/list_new.phtml');
        } else {

            $orgModel = new OrgModel();
            $list = $orgModel->getOrgation();
            $this->_view->assign('list', $list);
        }
    }

}
