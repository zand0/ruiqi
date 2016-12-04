<?php

/**
 * Description of Base
 *
 * @author zxg
 */
namespace Com\Controller\My;
class Base extends \Yaf\Controller_Abstract{
    
    public function init() {
        //设置Controller的模板位置为模块目录下的views文件夹
        $this->setViewpath(APP_PATH . '/modules/' . $this->getModuleName() . '/views');
        $views = $this->initView();
        session_start(); 
    }
    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($status,$msg,$data,$type='json') {
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode(array('status'=>$status,'msg'=>$msg,'data'=>$data))); 
            default     :
                exit($data);
        }
    }
    
    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->_view->assign('waitSecond',$ajax);
        if(!empty($jumpUrl)) $this->_view->assign('jumpUrl',$jumpUrl);
        // 提示标题
        $this->_view->assign('msgTitle',$status? '成功提示' : '失败提示');
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        //if($this->get('closeWin'))    $this->_view->assign('jumpUrl','javascript:window.close();');
        $this->_view->assign('status',$status);   // 状态
        //保证输出不受静态缓存影响
        //C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            $this->_view->assign('message',$message);// 提示信息
            // 成功操作后默认停留1秒
            $this->_view->assign('waitSecond','3');
            // 默认操作成功自动返回操作前页面
            if(empty($jumpUrl)) $this->_view->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->_view->display('common/success.phtml');
        }else{
            $this->_view->assign('error',$message);// 提示信息
            //发生错误时候默认停留3秒
            $this->_view->assign('waitSecond','3');
            // 默认发生错误的话自动返回上页
            if(empty($jumpUrl)) $this->_view->assign('jumpUrl',"javascript:history.back(-1);");
            $this->_view->display('common/error.phtml');
        }
        // 中止执行  避免出错后继续执行
        exit ;
    }

}
