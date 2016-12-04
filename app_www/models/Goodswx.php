<?php

/**
 * Description of Shop
 *
 * @author wjy
 */
class PromotionswxModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function add($post) {
        if( !isset($post['promotionsn']) || empty($post['promotionsn']) ){
            throw new Exception("promotions is empty");
        }
        if( !isset($post['title']) || empty($post['title']) ){
            throw new Exception("title is empty");
        }
        if( !isset($post['type']) || empty($post['type']) ){
            throw new Exception("type is empty");
        }
        if( !isset($post['good_type']) || empty($post['good_type']) ){
            throw new Exception("good_type is empty");
        }
        if( !isset($post['money']) || empty($post['money']) ){
            throw new Exception("money is empty");
        }
        $data = [
            'promotionsn'=>$post['promotionsn'],
            'kid'=>$post['kid'],
            'title'=>$post['title'],
            'type'=>$post['type'],
            'good_type'=>$post['good_type'],
            'price'=>$post['price'],
            'num'=>isset($post['num'])?$post['num']:1,
            'money'=>$post['money'],
            'status'=>0,
            'time_start'=>0,
            'time_end'=>0,
            'time_created'=>time()
        ];
        $status = LibF::M('promotions_user')->add($data);
        return $status;
    }
    
    public function get($kid){
        $data = LibF::M('promotions_user')->where('kid=%d',[$kid])->select();
        return $data;
    }
    
    /**
     * 删除指定id数据
     */
    public function del($id) {
        if (empty($id)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $where = array('id' => $id);
        $status = LibF::M('promotions')->where($where)->delete();
        if (!$status) {
            return $this->logicReturn('0206', '删除失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }
    
    /*public function getPromotionsObject() {
        $returnData = array();

        $where['status'] = 1;
        $data = LibF::M('promotions')->where($where)->select();
        if (!empty($data)) {
            foreach ($data as $value) {
                $returnData[$value['id']] = $value;
            }
        }
        return $returnData;
    }*/

}