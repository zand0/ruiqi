<?php

/**
 * Description of Idauth
 *
 * @author zxg
 */

namespace Com\Controller\My;

class Idauth extends Base {

    protected $cuid = 0;
    protected $username = '';
    protected $mobilePhone = '';
    protected $shop_id = 0;
    protected $address = '';

    public function init() {
        parent::init();
        if (!$_SESSION['kehu_info']) {
            $this->redirect('/index/index');
        }
        $userId = $_SESSION['kehu_info']['kid'];
        $userRow = \LibF::M('kehu')->where(array('kid' => $userId))->find();
        if (!$userRow) {
            $this->redirect('/index/index');
        }
        $regionName = '';
        
        $userRow['region_name'] = $regionName;
        $this->username = $userRow['user_name'];
        $this->mobilePhone = $userRow['mobile_phone'];
        $this->cuid = $userRow['kid']; 
        $this->shop_id = $userRow['shop_id'];
        $this->address = $userRow['address'];
        $this->_view->assign('kehuInfo', $userRow);
    }

}
