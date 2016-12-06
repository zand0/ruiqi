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
        $kid = Tools::session('kid');
        $res = LibF::M('evaluation')->where(['kehu_id'=>$kid,'order_sn'=>$osn])->find();
        if(!empty($res)){
            $res['type'] = substr($res['type'],1,strlen($res['type'])-2);
            $res['type']=explode(',',$res['type'])[2];
        }
        return $res;
    }
    
    public function setComment($post){
        $kid = Tools::session('kid');
        if(!isset($post['ordersn']) || empty($post['ordersn'])){
            throw new Exception("ordersn is empty");
        }
        if(empty($kid)){
            throw new Exception("kehuid is empty");
        }
        if(!isset($post['type']) || empty($post['type'])){
            throw new Exception("type is empty");
        }
        if(!isset($post['comment']) || empty($post['comment'])){
            throw new Exception("comment is empty");
        }
        $type = '['.implode(',',[0,0,$post['type']]).']';
        
        $data=[
            'order_sn'=>$post['ordersn'],
            'kehu_id'=>$kid,
            'type'=>$type,
            'comment'=>$post['comment'],
            'time_created'=>time()
        ];
        $model = LibF::M('evaluation');
        if($res=$model->where(['kehu_id'=>$data['kehu_id'],'order_sn'=>$data['order_sn']])->find()){
            throw new Exception("您已评论过此订单！");
        }
        return $model->add($data);
    }
    
}
