<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EvaluationwxModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function getComment($osn){
        return LibF::M('evaluation')->where(['order_sn'=>$osn])->find();
    }
    
    public function setComment($post){
        
        if(!isset($post['ordersn']) || empty($post['ordersn'])){
            throw new Exception("ordersn is empty");
        }
        if(!isset($post['kid']) || empty($post['kid'])){
            throw new Exception("kehuid is empty");
        }
        if(!isset($post['type']) || empty($post['type'])){
            throw new Exception("type is empty");
        }
        if(!isset($post['comment']) || empty($post['comment'])){
            throw new Exception("comment is empty");
        }
        $type = '['.implode(',',[0,0,1]).']';
        $data=[
            'order_sn'=>$post['ordersn'],
            'kehu_id'=>$post['kid'],
            'type'=>$type,
            'comment'=>$post['comment']
        ];
        LibF::M('evaluation')->add($data);
    }
    
}
