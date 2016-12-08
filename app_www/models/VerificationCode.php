<?php

/**
 * 通用的树型类，可以生成任何树型结构
 */
class VerificationCodeModel extends \Com\Model\Base {
    //检查验证码有效性
    public function checkCode($phone,$code){
        //return 1;
        //$uc = $this->_m;
        $m_vc = LibF::M('verification_code');
        if(!$m_vc->where(['mobile'=>$phone])->find()){
            throw new Exception('尚未获取验证码');
        }
        $ucr = $m_vc->where(['mobile'=>$phone,'code'=>$code])->find();
        //$ucr2 = $m_vc->where(['mobile'=>$phone])->find();
        $ustime = date('Y-m-d H:i:s',$ucr['time']);//$uc->where('UC_PHONE',$phone)->where('UC_CODE',$code)->value('UC_START_TIME');//exception(strtotime($ustime).'---'.(time()+10*60));
        if($ucr['status']!=1 && $ustime && time()<(strtotime($ustime)+30*60) ){
            //$this->_db->update($this->_table,array('UC_STATE'=>1,'UC_ERROR'=>0),array('UC_PHONE'=>$phone));
            $m_vc->where(['mobile'=>$phone])->save(['status'=>1]);
            return 1;
        }else{
            //$this->_db->update($this->_table,array('UC_ERROR'=>$ucr2['UC_ERROR']+1),array('UC_PHONE'=>$phone));
            return 0;
        }
    }
    //添加验证码
    public function addCode($post){
        $data = array(
            'mobile'=>$post['phone'],
            'status'=>0,
            'code'=>$post['code'],
            'time'=>date('Y-m-d H:i:s',time())
            //'UC_TYPE'=>0
        );
        $m_vc = LibF::M('verification_code');
        //exception(print_r($data));
        //$uc = $this->_m;
        //$this->_db->selectFirst($this->_table,array('UC_PHONE',$data['UC_PHONE']));
        if($r = $m_vc->where(['code'=>$post['code'],'mobile'=>$post['phone']])->find()){
            //此手机号存在便更新
            //print_r($data);
            return $m_vc->where(['id'=>$r['id']])->save($data);
        }else{
            //手机号不存在就添加
            return $m_vc->add($data);
        }
    
    }
    //检查验证码获取频率
    public function getSmsLimit($phone){
        $m_vc = LibF::M('verification_code');
        $time = $m_vc->where(['mobile'=>$phone])->find()['time'];
        //$time = $this->_db->queryFirst("select UC_START_TIME from k_user_code where UC_PHONE=:phone",[':phone'=>$phone])['UC_START_TIME'];
        if(strtotime($time) > (time()-60)){
            throw new Exception("请一分钟后再获取！");
        }
    }

}

?>