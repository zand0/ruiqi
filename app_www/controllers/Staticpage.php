<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class StaticpageController extends \Com\Controller\Common {
    
    public function indexAction() {
        $data = array(
            'list'  => 'staticpage/list',
            'newcreateclient' => 'staticpage/newcreateclient',
            'storemanage'   => 'staticpage/storemanage',
            'xiaoshoujianbao' => 'staticpage/xiaoshoujianbao'
        );
        $this->_view->assign('data', $data);
        
        return true;
    }
    public function listAction() {
        return true;
    }
    public function newcreateclientAction() {
        return true;
    }
    public function storemanageAction() {
        return true;
    }
    public function xiaoshoujianbaoAction() {
        return true;
    }
}