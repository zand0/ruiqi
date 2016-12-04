<?php

class AppdatainfoModel extends Com\Model\Base {

    /**
     * 获取消息信息列表
     * @date 2016/03/18
     */
    public function getNewList($param = array()) {
        $where = array();

        $pageStart = ($param['page'] - 1) * $param['pagesize'];

        $field = "message_id,message_title,message_content,admin_user,time_created";
        $data = LibF::M('notice')->field($field)->where($where)->order('time_created desc')->limit($pageStart,$param['pagesize'])->select();

        return $data;
    }
    
    /**
     * 获取公文审批列表
     * @date 2016/03/18
     */
    public function getApproveList($param = array()){
        $where = array();
        
        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        if (isset($param['user_id'])) {
            $where['approval_user'] = $param['user_id'];
        }

        $field = "id,approvalsn,title,username,approval_status,approval_annex,comment,reason,time_created";
        $data = LibF::M('approval')->field($field)->where($where)->order('time_created desc')->limit($pageStart,$param['pagesize'])->select();
        
        return $data;
    }

    /**
     * 订单总数
     * @date 2016/03/18
     */
    public function orderTotal($param = array()){
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) ? strtotime($param['end_time']) : '';

        $where = array();
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');

        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];

        if (isset($param['status']) && !empty($param['status'])) {
            $where['status'] = $param['status'];
        } else {
            $where['status'] = array(array('egt', 1), array('elt', 4), 'and');
            //$where['status'] = array('neq', 5);
            //$where['status'] = array('neq', -1);
        }
        $orderTotal = LibF::M('order')->where($where)->count();
        
        return $orderTotal;
    }
    
    /**
     * 获取订单总数(按门店)
     * @date 2016/03/18
     */
    public function getOrderTotal($param = array()) {
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = (isset($param['end_time']) && !empty($param['end_time'])) ? $param['end_time'] : '';
        
        if (!empty($param['end_time'])) {
            $end_time = strtotime("+1 day", strtotime($param['end_time']));
        }

        $pageStart = ($param['page'] - 1) * $param['pagesize'];

        $where = array();
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');

        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];

        if (isset($param['status']) && !empty($param['status'])){
            $where['status'] = $param['status'];
        }else{
            $where['status'] = array('neq',5);
            $where['status'] = array('neq', -1);
        }
        
        $field = "shop_id,shop_name,count(*) as total";
        $orderTotal = LibF::M('order')->field($field)->group('shop_id')->where($where)->order('total desc')->limit($pageStart,$param['pagesize'])->select();
        
        return $orderTotal;
    }

    /**
     * 获取已完成订单列表
     * @date 2016/03/18
     */
    public function getorderList($param = array()) {
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = (isset($param['end_time']) && !empty($param['end_time'])) ? $param['end_time'] : '';
        
        if (!empty($param['end_time'])) {
            $end_time = strtotime("+1 day", strtotime($param['end_time']));
        }

        $pageStart = ($param['page'] - 1) * $param['pagesize'];

        $where = array();
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');
        if (isset($param['status']) && !empty($param['status']))
            $where['status'] = $param['status'];

        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];

        $field = "order_sn,username,shop_id,shop_name,otime,should_time,is_urgent,username,kehu_type,mobile,address,shop_id,shop_name,shipper_name,shipper_mobile,is_evaluation,status,ctime";
        $data = LibF::M('order')->field($field)->where($where)->order('ctime desc')->limit($pageStart, $param['pagesize'])->select();
        if (!empty($data)) {
            foreach ($data as &$value) {
                $value['time'] = !empty($value['ctime']) ? date('m-d H:i', $value['ctime']) : '';
                $value['etime'] = !empty($value['should_time']) ? date('m-d H:i', $value['should_time']) : (($value['ctime']) ? date('m-d H:i',$value['ctime']) : '');
            }
        }

        $dataTotal = LibF::M('order')->where($where)->count();
        $returnData['data'] = $data;
        $returnData['dataTotal'] = $dataTotal;

        return $returnData;
    }

    /**
     * 获取当天订单
     * 
     * @date 2016/03/18
     */
    public function getNowOrder($param = array(),$date = '') {
        $time = strtotime(date('Y-m-d'));
        $where['ctime'] = array('egt', $time);
        
        if(isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        
        if (isset($param['status']) && !empty($param['status'])){
            $where['status'] = $param['status'];
        }else{
            //$where['status'] = array('neq',5);
            $where['status'] = array(array('egt', 1), array('elt', 4), 'and');
        }
        
        $orderNow = LibF::M('order')->where($where)->count();

        $ztTime = date("Y-m-d", strtotime("-1 day"));
        $zwhere['ctime'] = array(array('egt', $ztTime), array('elt', $time), 'and');
        $orderZt = LibF::M('order')->where($where)->count();

        $data['noworder'] = $orderNow;
        $data['yesterdayorder'] = $orderZt;
        if ($data['noworder'] >= $data['yesterdayorder']) {
            $val = $data['noworder'] - $data['yesterdayorder'];
            $data['nowtype'] = ($data['yesterdayorder'] > 0) ? '+' . sprintf("%.2f", ($val * 100) / $data['yesterdayorder']) : 0;
        } else {
            $val = $data['yesterdayorder'] - $data['noworder'];
            $data['nowtype'] = ($data['yesterdayorder'] > 0) ? '-' . sprintf("%.2f", ($val * 100) / $data['yesterdayorder']) : 0;
        }
        return $data;
    }

    /**
     * 获取当前月订单
     */
    public function getMonthOrder($param = array()) {

        $shop_id = isset($param['shop_id']) ? $param['shop_id'] : 0;
        
        $data = array();
        $timeData = new TimedataModel();
        $nowWeek = $timeData->month_firstday(0, true);
        
        $statisticModel = new StatisticsDataModel();
        $weekTotal = $data['monthorder'] = $statisticModel->orderTotal($nowWeek,0,$shop_id); //本月销售统计

        $lastWeek = $timeData->lastmonth_firstday(0, true);
        $lastWeekTotal = $data['lastorder'] = $statisticModel->orderTotal($lastWeek, $nowWeek,$shop_id); //上个月销售统计
        if ($weekTotal >= $lastWeekTotal) {
            $val = $weekTotal - $lastWeekTotal;
            $data['monthtype'] = ($lastWeekTotal > 0) ? '+' . sprintf("%.2f", ($val * 100) / $lastWeekTotal) : 0;
        } else {
            $val = $lastWeekTotal - $weekTotal;
            $data['monthtype'] = ($lastWeekTotal > 0) ? '-' . sprintf("%.2f", ($val * 100) / $lastWeekTotal) : 0;
        }
        return $data;
    }
    
    /**
     * 客户数量
     * @date 2016/03/18
     */
    public function kehuTotal($param = array()) {
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) ? strtotime($param['end_time']) : '';

        $where = array('status' => 0);
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');

        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];

        $kehuTotal = LibF::M('kehu')->where($where)->count();

        return $kehuTotal;
    }

    /**
     * 客户类型统计数量
     */
    public function kehuTypeTotal($param = array()) {

        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        
        $where['status'] = 0;
        
        $data = array();
        $kehuTotal = LibF::M('kehu')->field('ktype,count(*) as total')->where($where)->group('ktype')->select();
        if(!empty($kehuTotal)){
            foreach($kehuTotal as $value){
                $data[$value['ktype']] = $value['total'];
            }
        }

        return $data;
    }

    /**
     * 获取客户统计数量（按门店）
     */
    public function getKehuTotal($param = array()) {
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) ? strtotime($param['end_time']) : '';

        $where = array();
        $where['status'] = 0;
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');

        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        
        $field = "shop_id,count(*) as total";
        $orderTotal = LibF::M('kehu')->field($field)->group('shop_id')->where($where)->order('total desc')->limit($pageStart,$param['pagesize'])->select();
        return $orderTotal;
    }

    /**
     * 获取当前用户列表
     */
    public function getKehuList($param = array()){
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) ? strtotime($param['end_time']) : '';

        $where = array();
        if (isset($param['shop_id']) && !empty($param['shop_id']))
            $where['shop_id'] = $param['shop_id'];
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');
        if (isset($param['ktype']))
            $where['ktype'] = $param['ktype'];

        $pageStart = ($param['page'] - 1) * $param['pagesize'];
        
        $field = "kid,shop_id,user_name,paytype,card_sn,ctime";
        $orderTotal = LibF::M('kehu')->field($field)->where($where)->order('ctime desc')->limit($pageStart,$param['pagesize'])->select();

        return $orderTotal;
    }
    
    /**
     * 获取充装计划单
     */
    public function fiilingList($param = array()) {
        $start_time = isset($param['start_time']) ? strtotime($param['start_time']) : '';
        $end_time = isset($param['end_time']) ? strtotime($param['end_time']) : '';

        $where = array();
        if ($start_time && $end_time)
            $where['ctime'] = array(array('egt', $start_time), array('elt', $end_time), 'and');

        $pageStart = ($param['page'] - 1) * $param['pagesize'];

        $field = "filling_no,num,type,ctime,status";
        $fillingTotal = LibF::M('filling_bottle_log')->field($field)->where($where)->order('ctime desc')->limit($pageStart, $param['pagesize'])->select();

        return $fillingTotal;
    }
    
    /**
     * 获取充装计划详情
     */
    public function fillingDetial($param = array()){
        $filling_no = isset($param['filling_no']) ? $param['filling_no'] : '';
        $where['filling_no'] = $filling_no;
        
        $field = "name,num";
        $fillingDetial = LibF::M('filling_bottle_info')->field($field)->where($where)->select();
        return $fillingDetial;
    }

}
