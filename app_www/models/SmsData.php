<?php


class SmsDataModel extends \Com\Model\Base {

    //private $_appkeys = '7462ca9efa4c752e81ae14bb';
    //private $_masterSecret = '91f1653d08528a83e2c7337e';
    
    private $_appkeys = '5b274e3154953780e663e870';
    private $_masterSecret = 'dc7a0cc240ecafd555b65479';
    
    /**
    * 短信发送
    */
    public function sendsms($username = '', $ordersn, $mobile, $message = '') {

        if (empty($ordersn) || empty($mobile))
            return FALSE;

        $url = 'http://api.app2e.com/smsBigSend.api.php';
        $post_data['pwd'] = md5('JhEKC9sS');
        $post_data['username'] = 'expaisi';
        $post_data['p'] = $mobile;
        //$post_data['msg'] = '【派思燃气】尊敬的用户（' . $username . '先生/女士）您好，您已成功下单，单号为（' . $ordersn . '），我们会尽快安排送气工上门服务，请耐心等待。';
        if ($message == 1) {
            $post_data['msg'] = '【升泰燃气】您已成功充值，充值金额为' . $ordersn . '，当前余额为' . $username . '，如有疑问请拨客服电话7575755';
        } else if ($message == 2) {
            $post_data['msg'] = '【升泰燃气】当前订单结算成功，当前订单金额为' . $ordersn . '，当前余额为' . $username . '，如有疑问请拨客服电话7575755';
        } else {
            $post_data['msg'] = '【升泰燃气】您已成功下单，单号为' . $ordersn . '，配送地址：' . $username . '，如有疑问请拨客服电话7575755';
        }
        $post_data['charSetStr'] = 'utf';

        $o = "";
        foreach ($post_data as $k => $v) {
            $o.= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        $tb = new TbModel();
        $res = $tb->request_post($url, $post_data);
        return $res;
    }

    /**
     * 信息推送
     * @param int $sendno 发送编号。由开发者自己维护，标识一次发送请求
     * @param int $receiver_type 接收者类型。1、指定的 IMEI。此时必须指定 appKeys。2、指定的 tag。3、指定的 alias。4、 对指定 appkey 的所有用户推送消息。* @param string $receiver_value 发送范围值，与 receiver_type相对应。 1、IMEI只支持一个 2、tag 支持多个，使用 "," 间隔。 3、alias 支持多个，使用 "," 间隔。 4、不需要填
     * @param int $msg_type 发送消息的类型：1、通知 2、自定义消息
     * @param string $msg_content 发送消息的内容。 与 msg_type 相对应的值
     * @param string $platform 目标用户终端手机的平台类型，如： android, ios 多个请使用逗号分隔
     */
    function send($sendno, $receiver_type = 1, $receiver_value = "", $msg_type = 1, $msg_content = "", $platform = 'android') {
        $url = 'http://api.jpush.cn:8800/sendmsg/v2/sendmsg';
        $param = '';
        $param .= '&sendno=' . $sendno;
        $appkeys = $this->_appkeys;
        $param .= '&app_key=' . $appkeys;
        $param .= '&receiver_type=' . $receiver_type;
        $param .= '&receiver_value=' . $receiver_value;
        $masterSecret = $this->_masterSecret;
        $verification_code = md5($sendno . $receiver_type . $receiver_value . $masterSecret);
        $param .= '&verification_code=' . $verification_code;
        $param .= '&msg_type=' . $msg_type;
        $param .= '&msg_content=' . $msg_content;
        $param .= '&platform=' . $platform;

        $tb = new TbModel();
        $res = $tb->request_post($url, $param);

        $res_arr = json_decode($res, true);

        return $res_arr;
    }

}
