<?php
/**
 * Description of RqAdminUser
 *
 * @author zxg
 */

class AdminUserModel extends Com\Model\Base {
    public function __construct() {
        $this->tableD = LibF::M('admin_user');
        $this->role = LibF::M('auth_role');
    }
    protected $errorStatusPrefix = '101';
    public function login($username, $password, $captcha = null) {
        $enableLoginCaptcha = LibF::C("site.enable_login_captcha");
        if ($enableLoginCaptcha && !$this->checkLoginCaptcha($captcha)) {
            //return $this->logicReturn('101','验证码错误！');
        }
        $condition = array('username' => $username);
        $userRow = $this->tableD->where($condition)->find();
        if (!$userRow) {
            return $this->logicReturn('103','当前用户不存在！');
        }
        if($userRow['status'] == '1') {
            return $this->logicReturn('104','您的账户被禁止登录');
        }
        if($userRow['status'] == '2') {
            return $this->logicReturn('105','您的账户不存在');
        }
        if (!$this->checkPwd($userRow['password'], $password, $userRow['user_salt'])) {
            return $this->logicReturn('106','用户名或密码错误！');
        }
        return $this->logicReturn('200','',$userRow);
    }

    public function showLoginCaptcha() {
        ob_end_clean();
        $Verify = new \Image\Verify();
        $Verify->fontSize = 14;
        $Verify->length = 4;
        $Verify->useNoise = false;
        $Verify->codeSet = '0123456789';
        $Verify->imageW = 95;
        $Verify->imageH = 41;
        $Verify->useImgBg = 1;
        $Verify->expire = 600;  
        $Verify->entry();
    }

    public function checkLoginCaptcha($captcha) {
        $verify = new \Image\Verify();
        return $verify->check($captcha);
    }

    public function encryptPwd($password, $salt) {
        return md5(md5($password) . $salt);
    }

    public function checkPwd($encryptPassword, $textPassword, $salt) {
        return $encryptPassword === $this->encryptPwd($textPassword, $salt);
    }
    
    public function add($param, $roles = array()) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            $data['uid'] = $insert_id;
            foreach ($roles as $role_id) {
                $data['role_id'] = $role_id;
                LibF::M('auth_role_user')->data($data)->add();
            }
            return $this->logicReturn('200','操作成功');
        }
        return $this->logicReturn('1','操作失败');
    }

    public function roleList() {
        $list_role = $this->role->where(array('status'=>1,'id'=>array('gt',1)))->select();
        if($list_role)
        foreach ($list_role as $val) {
            $roles[$val['id']] = $val['title'];
        }
        return $roles;
    }

    /**
     * 修改用户
     */
    public function edite($type = 'edite', $id, $data = array(), $roles = array()) {
        if ($type == 'edite') {
            $where['user_id'] = $id;
            !empty($roles) && $data['roles'] = implode(',', $roles);
            $this->_edite($id, $data, $where);
            $role_data['uid'] = $id;
            $roleUserD = LibF::M('auth_role_user');
            $roleUserD->where('uid='.$id)->delete();
            if(!empty($roles))
            foreach ($roles as $role_id) {
                $role_data['role_id'] = $role_id;
                $roleUserD->data($role_data)->add();
            }
            return $this->logicReturn('200','操作成功');
        } else {
            $where['user_id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList($where = array()){
        $_REQUEST ['_order'] = 'user_id';
        $_REQUEST ['_sort'] = 'desc';
        
        $where['status'] = array('neq', 2);
        return $this->_list($where);
    }
    
    /**
     * 获取门店对应的所有用户
     */
    public function getShopUserArray($shop_id = 0) {
        
        if(empty($shop_id))
            return FALSE;
        
        $data = LibF::M('admin_user')->where(array('shop_id' => $shop_id))->select();
        return $data;
    }

}
