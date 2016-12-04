<?php

/**
 * 短信发送
 */
class SmsController extends \Com\Controller\Common {
    
    
//单发短信
    public function singlesmsAction() {

        $url = 'http://api.app2e.com/smsBigSend.api.php';
        $post_data['pwd'] = md5('JhEKC9sS');
        $post_data['username'] = 'expaisi';
        $post_data['p'] = '15501281558';
        $post_data['msg'] = '【派思燃气】尊敬的用户（xxx先生士）您好，您已成功下单，单号为（XXXXXXXXXX），我们会尽快安排送气工上门服务，请耐心等待。';
	$post_data['charSetStr'] = 'utf';

        $o = "";
        foreach ($post_data as $k => $v) {
            $o.= "$k=" . urlencode($v) . "&";
        }
        $post_data = substr($o, 0, -1);

        $tb = new TbModel();
        $res = $tb->request_post($url, $post_data);
        print_r($res);exit;
    }

}
