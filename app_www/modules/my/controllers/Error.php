<?php

/**
 * @name ErrorController
 * @desc   错误控制器, 在发生未捕获的异常时刻被调用 
 */
class ErrorController extends \Com\Controller\Common {

	//从2.1开始, errorAction支持直接通过参数获取异常
	public function errorAction($exception) {
		//1. assign to view engine
		$this->_view->assign("message", $exception->getMessage());
		return true;
	}
}
