<?php

/**
 * Description of LibF
 *
 * @author zxg
 */
class LibF {

    /**
     * 获取和设置配置参数 支持批量定义
     * @param string|array $name 配置变量
     * @param mixed $value 配置值
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function C($name, $default = null, $configFile=null, $ext = '.php') {
        if (!is_string($name) || !$name) {
            return null;
        }
        $name = explode('.', $name);
        $value = Yaf\Registry::get('config');
        if (!$name || !$value) {
            return $value ? $value : $default;
        }
        foreach ($name AS $k) {
            if (is_numeric($k)) {
                $value = $value->toArray();
            }
            if (!isset($value[$k])) {
                $value = $default;
                break;
            }
            $value = $value[$k];
        }
        return $value;
    }

    /**
     * 实例化一个没有模型文件的Model
     * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     * @return Model
     */
    public static function M($name = '', $tablePrefix = '', $connection = '') {
        static $_model = array();
        if (strpos($name, ':')) {
            list($class, $name) = explode(':', $name);
        } else {
            $class = 'Model';
        }
        $guid = (is_array($connection) ? implode('', $connection) : $connection) . $tablePrefix . $name . '_' . $class;
        if (!isset($_model[$guid]))
            $_model[$guid] = new $class($name, $tablePrefix, $connection);

        return $_model[$guid];
    }

    /**
     * 实例化模型类 格式 [资源://][模块/]模型
     * @param string $name 资源地址
     * @param string $layer 模型层名称
     * @return Model
     */
    public static function D($name = '') {
        static $_model = array();
        if (isset($_model[$name])) {
            return $_model[$name];
        }
        $class = $name . 'Model';
        if (class_exists($class)) {
            $model = new $class();
        } else {
            throw new Exception('D方法实例化没找到模型类' . $class);
        }
        $_model[$name] = $model;
        return $model;
    }

    /**
     * 抛出异常
     */
    public static function E($errorMsg, $code = null) {
        throw new Exception($errorMsg, $code);
    }

    /**
     * 加载配置文件
     * @param type $configFile
     * @param type $ext
     * @return type
     */
    public static function LC($configFile, $ext = '.php') {
        $config = null;
        if (!$configFile) {
            return $config;
        }
        // 单例加载
        if (Yaf\Registry::get('_config_stack_' . $configFile)) {
            return Yaf\Registry::get('_config_stack_' . $configFile);
        }
        $configDir = rtrim(APP_CONFIG, '\/');
        $configFilePath = $configDir . DIRECTORY_SEPARATOR . $configFile . $ext;
        if (!file_exists($configFilePath)) {
            LibF::E('load config [' . $configFile . '] not EXITS .');
        }
        if ($ext == '.php') {
            $config = include $configFilePath;
            $config = new Yaf\Config\Simple($config);
        } elseif ($ext == '.ini') {
            $config = new Yaf\Config\ini($configFilePath);
        } else {
            LibF::E('load config type [' . $ext . '] not support .');
        }
        Yaf\Registry::set('_config_stack_' . $configFile,$config);
        return $config;
    }

    /**
     * URL组装 支持不同URL模式
     * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
     * @param string|array $vars 传入的参数，支持数组和字符串
     * @param string $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean $domain 是否显示域名
     * @return string
     */
    public static function U($url = '', $vars = '') {
// 解析URL
        $info = parse_url($url);
        $url = !empty($info['path']) ? $info['path'] : LibF::C('action_name');
        if (isset($info['fragment'])) { // 解析锚点
            $anchor = $info['fragment'];
            if (false !== strpos($anchor, '?')) { // 解析参数
                list($anchor, $info['query']) = explode('?', $anchor, 2);
            }
            if (false !== strpos($anchor, '@')) { // 解析域名
                list($anchor, $host) = explode('@', $anchor, 2);
            }
        } elseif (false !== strpos($url, '@')) { // 解析域名
            list($url, $host) = explode('@', $info['path'], 2);
        }
        $domain = $_SERVER['HTTP_HOST'];


// 解析参数
        if (is_string($vars)) { // aaa=1&bbb=2 转换成数组
            parse_str($vars, $vars);
        } elseif (!is_array($vars)) {
            $vars = array();
        }
        if (isset($info['query'])) { // 解析地址里面参数 合并到vars
            parse_str($info['query'], $params);
            $vars = array_merge($params, $vars);
        }

// URL组装
        $depr = '/';
        $var = array();
        if ($url) {
            if (0 === strpos($url, '/')) {// 定义路由
                $route = true;
                $url = substr($url, 1);
                if ('/' != $depr) {
                    $url = str_replace('/', $depr, $url);
                }
            } else {
                $module_name = Yaf\Application::app()->getDispatcher()->getRequest()->getModuleName();
                $controller_name = Yaf\Application::app()->getDispatcher()->getRequest()->getControllerName();
                $action_name = Yaf\Application::app()->getDispatcher()->getRequest()->getActionName();
                if ('/' != $depr) { // 安全替换
                    $url = str_replace('/', $depr, $url);
                }
                // 解析模块、控制器和操作
                $url = trim($url, $depr);
                $path = explode($depr, $url);
                $var = array();
                $var[] = !empty($path) ? array_pop($path) : $action_name;
                $var[] = !empty($path) ? array_pop($path) : $controller_name;
                if ($module_name && LibF::C('site.mult_module')) {
                    $var[] = array_pop($path);
                }
                $url = implode($depr, array_reverse($var));
            }
        }

        if (!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val) {
                if ('' !== trim($val))
                    $url .= $depr . $var . $depr . urlencode($val);
            }
        }
        if (isset($anchor)) {
            $url .= '#' . $anchor;
        }
        if ($domain) {
            $url = 'http://' . $domain . $depr . $url;
        }
        return $url;
    }

}
