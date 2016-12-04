<?php

/**
 * Description of LoginController
 *
 * @author zxg
 */
class PassportController extends Com\Controller\My\Guest {

    public function loginAction() {
        if (IS_POST) {
            $username = $this->getRequest()->getPost('username');
            $password = $this->getRequest()->getPost('password');
            $userD = LibF::D('Kehu');
            $loginRes = $userD->login($username, $password);
            if ($loginRes['status'] != '200') {
                $this->error($loginRes['msg']);
            }
            $this->redirect('/my/home/index');
        }
    }
    
    public function RegisterAction() {
        if (IS_POST) {
            $param['username'] = $this->getRequest()->getPost('username');
            $param['password'] = $this->getRequest()->getPost('password');
            $param['confirm_password'] = $this->getRequest()->getPost('confirm_password');
            $param['mobile_phone'] = $this->getRequest()->getPost('mobile_phone');
            
            $param['region_1'] = $this->getRequest()->getPost('region_1');
            $param['region_2'] = $this->getRequest()->getPost('region_2');
            $param['region_3'] = $this->getRequest()->getPost('region_3');
            $param['region_4'] = $this->getRequest()->getPost('region_4');
            $param['address'] = $this->getRequest()->getPost('address');
            $param['shop_id'] = $this->getRequest()->getPost('shop_id');
            
            $kehuD = LibF::D('Kehu');
            $registerRes = $kehuD->register($param);
            if ($registerRes['status'] == 200) {
                $this->success('注册成功','/index/index');
            } else {
                $this->error($registerRes['msg']);
            }
        }
    }

    public function logoutAction() {
        Tools::session(NULL);
        Tools::cookie(NULL);
        $url = 'http://' . $_SERVER['HTTP_HOST'];
        $this->success('欢迎下次再来', $url);
    }

}
