<?php

class Bootstrap extends Yaf\Bootstrap_Abstract {

    protected $config;

    public function _initConfig(Yaf\Dispatcher $dispatcher) {
        //echo 1234;
        $this->config = Yaf\Application::app()->getConfig()->toArray();
        $autoloadConfig = $this->config['autoload_config']; 
        if ($autoloadConfig) {
            $configDir = rtrim(APP_CONFIG, '\/');
            $configFiles = explode(',', $autoloadConfig);
            foreach ($configFiles AS $configFile) {
                $dotPos = strrpos($configFile, '.');
                $configName = substr($configFile,0,$dotPos);
                $configType = substr($configFile,$dotPos);
                $config = LibF::LC($configName,$configType);  
                if(!$config){
                    continue;
                }
                $this->config = array_merge($this->config, $config->toArray());
            }
        }
        $this->config = new Yaf\Config\Ini($this->config); $this->config->rewind();
        Yaf\Registry::set('config', $this->config);
    }

    public function _initConst() {
        // 定义当前请求的系统常量
        define('NOW_TIME', $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
        define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
        define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
        define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);
    }

    /**
     * [加载 命名空间 加载local library components文件]
     * @return [type] [description]
     */
    public function _initRegisterLocalNamespace() {
        $loader = Yaf\Loader::getInstance();
        $loader->registerLocalNamespace(
                array('com', 'Smarty')
        );
    }

    public function _initError(Yaf\Dispatcher $dispatcher) {
        if ($this->config->application->debug) {
            define('DEBUG_MODE', false);
            ini_set('display_errors', 'On');
        } else {
            define('DEBUG_MODE', false);
            ini_set('display_errors', 'Off');
        }
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher) {
        
        $routes = $this->config->routes;//var_dump($routes);exit('4567');
        if (!empty($routes)) {
            $router = $dispatcher->getRouter();
            $router->addConfig($routes);
        }
        
    }

    public function _initView(Yaf\Dispatcher $dispatcher) {
        //命令行下基本不需要使用smarty
        if (REQUEST_METHOD != 'CLI') {
            //var_dump($this->config->smarty);exit;
            $smarty = new Smarty_Adapter(null, $this->config->smarty);
            $smarty->registerFunction('function', 'truncate', array('Tools', 'truncate'));
            $publicUrl = LibF::C('site.static.url');
            $smarty->assign('_PUBLIC', $publicUrl);
            $dispatcher->setView($smarty);
        }
    }

}
