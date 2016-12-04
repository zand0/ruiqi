<?php

class Wxlogin
{
    private static $appid='wxedb065b0527bcf9c';
    private static $appsecret='365c80e20fd80b2e82ff90a6cb87f804';
    
    public static function wlogin($code){
        if(empty($code)){
            self::sw(self::getOpenid($code));
        }else{
            throw new Exception("code is empty");
        }
    }
    public static function islogin(){
        Tools::session('kid',170);
        return true;
        //var_dump(Tools::session('kid'));
        if(empty(Tools::session('kid')) || Tools::session('kid')!=Tools::cookie('kid')){
            self::logout();
            //执行登陆
            //self::sw(self::getOpenid());
            $return_url = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid='.self::$appid.'&redirect_uri='.$return_url.'&response_type=code&scope=snsapi_userinfo&state=5#wechat_redirect');
        }else{
            return true;
        }
    }
    
    public static function login($kid){
        Tools::session('kid',$kid);
        Tools::cookie('kid',kid,['expire'=>3600]);
    }
    
    public static function logout(){
        Tools::session('kid',null);
    }
    
    private static function getOpenid($code){
        if(empty($code)){
           throw new Exception("code is empty");
        }
        $appid=self::$appid;
        $appsecret=self::$appsecret;
        $url_a_t='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
        $res = Http::get($url_a_t);
        $res = json_decode($res,true);
        if(empty($res)){
            throw new Exception('openid获取失败');
        }
        //获取用户信息
        //$res = json_decode($res);
        $access_token = $res['access_token'];
        $openid = $res['openid'];
        if(empty($openid)){
            throw new Exception("wx:openid empty");
        }
        return $openid;
    } 
    
    private static  function sw($openid){
        if( $res = LibF::M('kehu')->where(['openid'=>$openid])->find() ){
            self::login($res['kid']);
        }else{
            $return_url = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            header('Location: /wx/ucenter/vbind?openid='.$openid.'&fromurl='.$return_url);
        }
    }
    
}

