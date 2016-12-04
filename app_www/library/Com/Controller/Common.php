<?php

/**
 * Description of Common
 *
 * @author zxg
 */

namespace Com\Controller;

use Yaf\Application;

class Common extends \Yaf\Controller_Abstract {

    /**
     * 不走session验证的往这加
     * 
     * 键是controller, 全部action时，值设为空数组，否则数组中为action值
     */
    private $noSession = array(
        'index' => array('index', 'home', 'login', 'safecodeimg','deploy','userlogin'),
        'region' => array('regionlist'),
        'register' => array('index'),
        'authrule' => array(),
        'authrole' => array(),
        'appmanager' => array(),
        'appstores' => array(),
        'apptype' => array(),
        'appuser' => array(),
        'appworks' => array(),
        'about' => array(),
        'dynamic' => array(),
        'safety' => array(),
        'contact' => array(),
        'personal' => array(),
        'callcenter' => array(),
        'tank' => array(),
        'filling' => array(),
        'instock' => array('info', 'purchase', 'purchaseinfo', 'bottleadd'),
        'outstock' => array('info'),
        'car' => array('index', 'add', 'edit'),
        'syslog' => array('index'),
        'bottle' => array(),
        'org' => array('index', 'add', 'edit'),
        'organize' => array('list'),
        'news' => array('index', 'add', 'edit', 'del', 'info', 'isshow','ajaxdata'),
        'inventory' => array('bottle', 'warehouse','report'),
        'deposit' => array('index', 'add','income','info'),
        'tousu' => array('list', 'addtype', 'editetype', 'deltype'),
        'quota' => array('index', 'add', 'edite'),
        'security' => array(),
        'gastj' => array('planlist', 'addplan','list','historical','planinfo'),
        'supplier' => array('gas', 'bottle', 'product', 'add', 'edit', 'del','index','ajaxdata'),
        'baseline' => array('gas', 'bottle', 'product', 'add', 'edit', 'del','index'),
        'raffinate' => array(),
        'quarters' => array('index', 'add', 'edit', 'del'),
        'adminuser' => array(),
        'finance'   => array(),
        'shop'      => array(),
        'order'     => array(),
        'spending'  => array(),
        'stock'     => array(),
        'report'    => array('index','add','edit','del'),
        'resetpassword' => array('updatepass'),
        'payproject'    => array('index','add','edite','del'),
        'revenueproject' => array('index','add','edite','del'),
        'prepaystate'   => array('index','add','edite','del'),
        'rolestate'     => array('index','add','edite','del'),
        'quarterstate'  => array('index','add','edite','del'),
        'peoplestate'   => array('index','add','edite','del'),
        'applicationtype'   => array('index','add','edite','del'),
        'approvalstatus'    => array('index','add','edite','del'),
        'approval'  => array(),
        'storetype'   => array('index','add','edite','del'),
        'storerating'   => array('index','add','edite','del'),
        'storestatus'   => array('index','add','edite','del'),
        'customersource'   => array('index','add','edite','del'),
        'customertype'   => array('index','add','edite','del'),
        'customerstatus'   => array('index','add','edite','del'),
        'ordertype'   => array('index','add','edite','del'),
        'orderstatus'   => array('index','add','edite','del'),
        'orderevaluation'   => array('index','add','edite','del'),
        'specifications'    => array('index','add','edite','del'),
        'cylinderdeposit'   => array('index','add','edite','del'),
        'bottlestatus'      => array('index','add','edite','del'),
        'regionmanage'  => array(),
        'manufacturer'      => array('index','add','edite','del'),
        'commodity'     => array('index','add','edite','del','ajaxdata'),
        'custum'        => array(),
        'losscylinders' => array('index','add'),
        'sales' => array(),
        'ajaxdata'  => array(),
        'statistics' => array(),
        'producttype'  => array(),
        'commodityprice' => array(),
        'sms'   => array(),
        'neck'  => array(),
        'promotions' => array(),
        'area' => array(),
        'areaprice' => array(),
        'shopprice' => array(),
        'stocktaking' => array(),
        'payshop' => array(),
        'shipper' => array(),
        'orderwx' => array(),
        'map' => array(),
        'gasstation' => array(),
        'callcenternew' => array(),
        'test' => array()
    );

    public function init() {
        session_start();
        $cname = strtolower(Application::app()->getDispatcher()->getRequest()->getControllerName());
        $aname = strtolower(Application::app()->getDispatcher()->getRequest()->getActionName());
        $session = \Tools::session();
        if (!$this->checkNoSession($cname, $aname)) {
            if (empty($session) || !isset($session['user_id'], $session['userinfo']) || empty($session['userinfo'])) {
                //备注 这里需要 验证session 用户 和 后天管理者区分开
                \Tools::redirect('/index/index');
            } else if ($cname == 'index' && in_array($aname, array('logout', 'home'))) {
                //首页与退出
            } else if (!empty($session['roles']) && $this->getRequest()->isXmlHttpRequest() !== TRUE) {//不是ajax请求
                //权限验证
                if (array_search('/' . $cname . '/' . $aname, $session['roles']) === false && array_search($_SERVER['REQUEST_URI'], $session['roles']) === false) {
                    $this->error('无权操作此功能', '/index/home');
                }
            }
        }
        $roles = !empty($session['roles']) ? array_flip($session['roles']) : '';
        $this->_view->assign('auth_roles', $roles);
        
        $uRule = new \UruleModel();
        $user_auth = $uRule->_getRuleList();
        $this->_view->assign('user_auth',$user_auth);
        $this->_view->assign('dateWeek', array('1' => '星期一', '2' => '星期二', '3' => '星期三', '4' => '星期四', '5' => '星期五', '6' => '星期六', '7' => '星期日'));
    }

    /**
     * 是否跳过session true是 false否
     */
    private function checkNoSession($cname, $aname) {
        if (!empty($this->noSession) && isset($this->noSession[$cname])) {
            if (empty($this->noSession[$cname])) {
                return true;
            }
            return !in_array($aname, $this->noSession[$cname]) ? false : true;
        }
        return false;
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message = '', $jumpUrl = '', $ajax = false) {
        $this->dispatchJump($message, 0, $jumpUrl, $ajax);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message = '', $jumpUrl = '', $ajax = false) {
        $this->dispatchJump($message, 1, $jumpUrl, $ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($status, $msg, $data, $type = 'json') {
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode(array('status' => $status, 'msg' => $msg, 'data' => $data)));
            default :
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
    private function dispatchJump($message, $status = 1, $jumpUrl = '', $ajax = false) {
        if (true === $ajax || IS_AJAX) {// AJAX提交
            $data = is_array($ajax) ? $ajax : array();
            $data['info'] = $message;
            $data['status'] = $status;
            $data['url'] = $jumpUrl;
            $this->ajaxReturn($data);
        }
        if (is_int($ajax))
            $this->_view->assign('waitSecond', $ajax);
        if (!empty($jumpUrl))
            $this->_view->assign('jumpUrl', $jumpUrl);
        // 提示标题
        $this->_view->assign('msgTitle', $status ? '成功提示' : '失败提示');
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        //if($this->get('closeWin'))    $this->_view->assign('jumpUrl','javascript:window.close();');
        $this->_view->assign('status', $status);   // 状态
        //保证输出不受静态缓存影响
        //C('HTML_CACHE_ON',false);
        if ($status) { //发送成功信息
            $this->_view->assign('message', $message); // 提示信息
            // 成功操作后默认停留1秒
            $this->_view->assign('waitSecond', '3');
            // 默认操作成功自动返回操作前页面
            if (empty($jumpUrl))
                $this->_view->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
            $this->_view->display('common/success.phtml');
        }else {
            $this->_view->assign('error', $message); // 提示信息
            //发生错误时候默认停留3秒
            $this->_view->assign('waitSecond', '3');
            // 默认发生错误的话自动返回上页
            if (empty($jumpUrl))
                $this->_view->assign('jumpUrl', "javascript:history.back(-1);");
            $this->_view->display('common/error.phtml');
        }
        // 中止执行  避免出错后继续执行
        exit;
    }

}