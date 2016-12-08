<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class KehuwxModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }
    
    //绑定openid
    public function bindOpenid($openid,$phone){
        $mkehu = LibF::M('kehu');
        if($res=$mkehu->where(['openid'=>$openid])->find()){
            if($res['mobile_phone']==$phone){
                Wxlogin::login($res['kid']);
                return true;
            }else{
                $mkehu->where(['kid'=>$res['kid']])->save(['mobile_phone'=>$phone]);
                Wxlogin::login($res['kid']);
                return true;
            }
            
        }else{
            $data=[
                'user_name'=>$phone,
                'mobile_phone'=>$phone,
                'password'=>md5(substr($phone, -6)),
                'address'=>'',
                //'shop_name'=>'',
                'ctime'=>time()
            ];
            if($r=$mkehu->field('kid')->where(['mobile_phone'=>$phone])->find()){
                $mkehu->where(['kid'=>$r['kid']])->save(['openid'=>$openid]);
                $kid=$r['kid'];
            }else {
                $kid = $mkehu->add($data);
            }
            
            Wxlogin::login($kid);
            return true;
        }
        //return false;
    }
    /**
     * 客户列表
     * 
     * @param $data
     */
    public function kehulist($params) {
        $page = $params['page'];
        $pageSize = $params['page_size'];

        $orderModel = LibF::M('kehu');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $offset = ($page - 1) * $pageSize;
        $count = $orderModel->where($where)->count();
        
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $orderModel->where($where)->limit($offset, $pageSize)->order('otime desc')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    /**
     * 客户应收款
     * 
     * @param $data
     */
    public function kehuArr($params,$where) {
        $page = $params['page'];
        $pageSize = $params['page_size'];

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        
        $offset = ($page - 1) * $pageSize;

        $orderModel = new Model('order');
        $filed = " rq_order.kid,rq_order.kehu_type,rq_order.username,rq_kehu.shop_id,rq_kehu.ctime,rq_kehu.ktype,rq_kehu.money as total,count(rq_order.kid) as num ";
        $leftJoin = " LEFT JOIN rq_kehu ON rq_order.kid = rq_kehu.kid ";
        
        $data = $orderModel->join($leftJoin)->field($filed)->where($where)->group('rq_order.kid')->select();

        $count = count($data);
        
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $orderModel->join($leftJoin)->field($filed)->where($where)->group('rq_order.kid')->limit($offset, $pageSize)->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    /**
     * 获取当前指定条件的客户信息
     * 
     * @param $param
     */
    public function getKehuInfo($params, $is_yh = 'false') {
        if (empty($params))
            return FALSE;

        $data = LibF::M('kehu')->where($params)->find();
        if (!empty($data) && $is_yh) {
            $data['yhdata'] = array();
            $yhData = $this->getUserPromotionsData($data['kid']);
            if (!empty($yhData)) {
                $data['yhdata'] = $yhData;
            }
        }
        return $data;
    }
    
    /**
     * 获取当前用户优惠券
     */
    public function getUserPromotionsData($kehu_id){
        if (empty($kehu_id))
            return false;

        $yhlist = array();
        $yhData = LibF::M('promotions_user')->where(array('kid' => $kehu_id, 'status' => 0))->select();
        if (!empty($yhData)) {
            foreach ($yhData as $value) {
                $val['id'] = $value['id'];
                $val['title'] = $value['title'];  //优惠券名称
                $val['yhsn'] = $value['promotionsn']; //优惠券编码
                $val['type'] = $value['type']; //优惠类型1优惠券2余额券
                $val['good_type'] = $value['good_type']; //液化气类型
                $val['price'] = $value['price']; //价格
                $val['num'] = $value['num']; //数量
                $val['money'] = $value['money']; //条件
                $val['time_start'] = !empty($value['time_start']) ? date('Y-m-d', $value['time_start']) : '';
                $val['time_end'] = !empty($value['time_end']) ? date('Y-m-d', $value['time_end']) : '';
                $yhlist[] = $val;
            }
        }
        return $yhlist;
    }

    /**
     * 创建
     * 
     * @param $data
     */
    public function add($params) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        //配送门店信息
        $data['shop_id'] = $params['shop_id'];  //门店ID
        $data['shop_name'] = $params['shop_name'];  //门店名称
        $data['shop_mobile'] = $params['shop_mobile']; //门店手机号
        $shop_type = $params['shop_type'];  //门店类型

        $data['user_name'] = $params['user_name']; //用户名称
        $data['moble'] = $params['mobile']; //用户手机号
        $data['password'] = $this->encryptUserPwd($params['mobile']);//用户密码
        $data['address'] = $params['address']; //用户地址
        $data['comment'] = $params['comment']; //备注

        $status = LibF::M('kehu')->add($data);
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok');
    }
    
    /**
     * 当前客户登陆
     * 
     */
    /*public function login($username, $password) {
        $parent_record = LibF::M('kehu')->where(array('mobile_phone' => $mobile))->find();
        if ($parent_record === null) {
            return $this->logicReturn(103, '此账号不存在');
        } else if ($parent_record['password'] !== md5($password)) {
            return $this->logicReturn(106, '密码错误');
        }
        return $this->logicReturn('200', '', $parent_record);
    }*/
    
    /**
     * 用户是否登录
     * @return boolean
     */
    public function isLogin(){
        $data = $_SESSION['f_userinfo'];
        if($data){
            return true;
        }
        return false;
    }
    /**
     * 登录
     * @param type $username
     * @param type $password
     * @return type
     */
    public function login($username,$password){
        $userM = LibF::M('kehu');
        if(empty($username) || empty($password)){
            return $this->logicReturn('01','用户名或密码不能为空');
        }
        
        $userRow = $userM->where(array('mobile_phone'=>$username))->find();
        $encryptPassword = $this->encryptUserPwd($password);
        if(!$userRow || $userRow['password']!=$encryptPassword){
            return $this->logicReturn('03','用户名或密码错误');
        } 
        $kehuInfo = array('kid'=>$userRow['kid'],'mobile_phone'=>$userRow['mobile_phone'],'user_name'=>$userRow['user_name'],'photo'=>$userRow['photo'],'shop_id' => $userRow['shop_id'],'address' => $userRow['address']);
        Tools::session('kehu_info', $kehuInfo); 
        return $this->logicReturn('200',null,$userRow);
    }
    /**
     * 登录用户信息
     * @return type
     */
    public function getLoginUserInfo(){exit;
        return $data = $_SESSION['f_userinfo'];
    }
    
    public function register($param){
        $username = trim($param['username']);
        $password = trim($param['password']);
        $confirmPassword = trim($param['confirm_password']) ;
        $mobilePhone = trim($param['mobile_phone']);
        $address = trim($param['address']);
         
        $shop_id = $param['shop_id'];
               
        $userM = LibF::M('kehu');
        $regionM = LibF::M('region');
        $saleRegionM = LibF::M('shop_sale_region');
        
        $region_1 = $param['region_1'];
        $region_2 = $param['region_2'];
        $region_3 = $param['region_3'];
        $region_4 = $param['region_4'];
        
        if(strlen($username)<1){
            return $this->logicReturn('01','用户名不能为空！');
        }
        if(!Validate::isPasswd($password, 6)){
            return $this->logicReturn('01','密码错误！');
        }
        if($password != $confirmPassword){
            return $this->logicReturn('03','两次输入密码不一致！');
        }
        if(!Validate::isMobilePhone($mobilePhone)){
            return $this->logicReturn('05','手机号码格式错误！');
        }
        $isMobileExist = $userM->where(array('mobile_phone'=>$mobilePhone))->find();
        if($isMobileExist){
            return $this->logicReturn('07','手机号已存在！');
        }
        
        // 销售地区是否存在
        /*$saleRegionRow = $saleRegionM->where(array('region_1'=>$region_1,'region_2'=>$region_2,'region_3'=>$region_3,'region_4'=>$region_4))->find();*/
        if(!$shop_id){
            return $this->logicReturn('09', '请选择门店！');
        }
        
        // 地区是否可用
        /*$regionRow = $regionM->find($region_4);
        if(!$regionRow || $regionRow['disabled']!=='false'){
            return $this->logicReturn('11', '地区不存在！');
        }*/
        
        $data['user_name'] = $username;
        $data['mobile_phone'] = $mobilePhone;
        $data['password'] = $this->encryptUserPwd($password);
        $data['ctime'] = time();
        $data['source'] = 1;
        $data['status'] = 1;
        $data['shop_id'] = $shop_id;
        $data['sheng'] = $region_1;
        $data['shi'] = $region_2;
        $data['qu'] = $region_3;
        $data['address'] = $address;

        try {
            $userM->add($data);
            return $this->logicReturn('200');
        } catch (Exception $exc) {
            return $this->logicReturn('11','添加失败！');
        } 
    }
    public function encryptUserPwd($password){
        $userConfig = LibF::LC('user');
        $loginSalt = $userConfig['XSAdfv*^sdf)#'];
        return md5(md5($password).$loginSalt);
    }

}