<?php

/**
 * 密码重置
 */
class ResetpasswordController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    public function init() {
        parent::init();
        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        if (!empty($adminInfo)) {
            $this->shop_id = $adminInfo['shop_id'];
            $this->shop_level = $adminInfo['shop_level'];
            $userData['user_id'] = $adminInfo['user_id'];
            $userData['username'] = $adminInfo['username'];
            $userData['photo'] = $adminInfo['photo'];
            $userData['mobile_phone'] = $adminInfo['mobile_phone'];
            $this->user_info = $userData;
        }
    }
    
    public function indexAction() {
        //发送验证码
    }

    public function passwordAction() {
        //密码重置
    }
    
    public function updatepassAction() {
        //修改密码
        if (IS_POST) {
            $password = $this->getRequest()->getPost('password');
            $old_password = $this->getRequest()->getPost('old_password');
            $confirm_password = $this->getRequest()->getPost('confirm_password');

            $msg = '';
            //相关base64相关解码
            if (!empty($password) && $password == $confirm_password) {

                $parent_record = LibF::M('admin_user')->where(array('user_id' => $this->user_info['user_id']))->find();
                if ($parent_record === null) {
                    $msg = '此账号不存在';
                } else if ($parent_record['password'] != md5(md5($old_password) . $parent_record['user_salt'])) {
                    $msg = '原始密码不正确';
                } else {
                    $data['password'] = md5(md5($password) . $parent_record['user_salt']);
                    $status = LibF::M('admin_user')->where(array('user_id' => $this->user_info['user_id']))->save($data);
                    if ($status) {
                        $msg = '更新成功';
                    } else {
                        $msg = '更新失败';
                    }
                }
            } else {
                $msg = '两次密码不一致';
            }
            $this->success($msg, '/personal/index');
        }
        $this->_view->assign($this->user_info);
    }

}
