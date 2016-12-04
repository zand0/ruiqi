<?php

/**
 * 个人中心
 */
class PersonalController extends \Com\Controller\Common {

    public $kehu_info;
    public function init() {
        parent::init();
        
        $data = $_SESSION['kehu_info'];
        $this->kehu_info = $data;
    }
    
    public function ordergasAction() {
        $kid = $this->kehu_info['kid'];
    }
    
    public function createorderAction() {
        
    }
}
